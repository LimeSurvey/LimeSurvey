<?php

namespace LimeSurvey\Models\Services;

use CHtml;
use LimeSurvey\PluginManager\PluginEvent;
use LimeSurvey\PluginManager\PluginEventContent;
use QuestionAttribute;
use Quota;
use Response;
use Survey;

/**
 * @todo Possible remove this warning
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Quotas
{
    /** @var \Survey the survey */
    private $survey;


    /**
     * Quicktranslation constructor.
     *
     * @param \Survey $survey the survey object
     *
     */
    public function __construct(\Survey $survey)
    {
        $this->survey = $survey;
    }


    /**
     * In the array are
     * qoutaId[
     *   aQuotaItems = [
     *      oQuestion,
     *      answerTitle,
     *      quotaMember
     *      valid ???
     *   ]
     * ]
     *
     * @return array
     */
    public function getQuotaStructure()
    {
        $totalquotas = 0;
        $totalcompleted = 0;
        if (!empty($this->survey->quotas)) {
            $aQuotaItems = array();
            //loop through all quotas
            foreach ($this->survey->quotas as $oQuota) {
                $totalquotas += $oQuota->qlimit;
                $totalcompleted += $oQuota->completeCount;

                //loop through all quotaMembers
                foreach ($oQuota->quotaMembers as $oQuotaMember) {
                    $aQuestionAnswers = self::getQuotaAnswers($oQuotaMember['qid'], $oQuota['id']);
                    if ($oQuotaMember->question->type == '*') {
                        $answerText = $oQuotaMember->code;
                    } else {
                        $answerText = isset($aQuestionAnswers[$oQuotaMember['code']]) ? flattenText($aQuestionAnswers[$oQuotaMember['code']]['Display']) : null;
                    }

                    $aQuotaItems[$oQuota['id']][] = array(
                        'oQuestion' => \Question::model()
                            ->with('questionl10ns', array('language' => $this->survey->language))
                            ->findByPk(array('qid' => $oQuotaMember['qid'])),
                        'answer_title' => $answerText,
                        'oQuotaMember' => $oQuotaMember,
                        'valid' => isset($answerText),
                    );
                }
            }
            $aData['aQuotaItems'] = $aQuotaItems;

            // take the last quota as base for bulk edits
            $aData['oQuota'] = $oQuota;
            $aData['aQuotaLanguageSettings'] = array();
            foreach ($oQuota->languagesettings as $languagesetting) {
                $aData['aQuotaLanguageSettings'][$languagesetting->quotals_language] = $languagesetting;
            }
        }
        $aData['totalquotas'] = $totalquotas;
        $aData['totalcompleted'] = $totalcompleted;

        return $aData;
    }

    /**
     * Returns an answerlist for a specific question type and marks already used answers as such.
     *
     * @todo Refactor and remove phpmd warning
     * @param integer $iQuestionId
     * @param integer $iQuotaId
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getQuotaAnswers(int $iQuestionId, int $iQuotaId)
    {
        $iQuestionId = sanitize_int($iQuestionId);
        $iQuotaId    = sanitize_int($iQuotaId);
        $sBaseLang = $this->survey->language;

        $aQuestion = \Question::model()
            ->with('questionl10ns', array('language' => $sBaseLang))
            ->findByPk(array('qid' => $iQuestionId));
        $aQuestionType = $aQuestion['type'];
        $aAnswerList = [];
        switch ($aQuestionType) {
            case \Question::QT_M_MULTIPLE_CHOICE:
                $aResults = \Question::model()
                    ->with('questionl10ns', array('language' => $sBaseLang))
                    ->findAllByAttributes(array('parent_qid' => $iQuestionId));
                foreach ($aResults as $oDbAnsList) {
                    $tmparrayans = array('Title' => $aQuestion['title'],
                        'Display' => substr((string) $oDbAnsList->questionl10ns[$sBaseLang]->question, 0, 40),
                        'code' => $oDbAnsList->title);
                    $aAnswerList[$oDbAnsList->title] = $tmparrayans;
                }
                break;
            case \Question::QT_G_GENDER:
                $aAnswerList = array(
                    'M' => array('Title' => $aQuestion['title'], 'Display' => gT("Male"), 'code' => 'M'),
                    'F' => array('Title' => $aQuestion['title'], 'Display' => gT("Female"), 'code' => 'F'));
                break;
            case \Question::QT_L_LIST:
            case \Question::QT_O_LIST_WITH_COMMENT:
            case \Question::QT_EXCLAMATION_LIST_DROPDOWN:
                $aAnsResults = \Answer::model()
                ->with('answerl10ns', array('language' => $sBaseLang))
                ->findAllByAttributes(array('qid' => $iQuestionId));

                foreach ($aAnsResults as $aDbAnsList) {
                    $aAnswerList[$aDbAnsList['code']] = array('Title' => $aQuestion['title'],
                    'Display' => $aDbAnsList->answerl10ns[$sBaseLang]->answer,
                    'code' => $aDbAnsList['code']);
                }
                break;
            case \Question::QT_A_ARRAY_5_POINT:
                $aAnsResults = \Question::model()
                    ->with('questionl10ns', array('language' => $sBaseLang))
                    ->findAllByAttributes(array('parent_qid' => $iQuestionId));

                foreach ($aAnsResults as $aDbAnsList) {
                    for ($x = 1; $x < 6; $x++) {
                        $tmparrayans = array('Title' => $aQuestion['title'],
                            'Display' => substr((string) $aDbAnsList->questionl10ns[$sBaseLang]->question, 0, 40) . ' [' . $x . ']',
                            'code' => $aDbAnsList['title']);
                        $aAnswerList[$aDbAnsList['title'] . "-" . $x] = $tmparrayans;
                    }
                }
                break;
            case \Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:
                $aAnsResults = \Question::model()
                    ->with('questionl10ns', array('language' => $sBaseLang))
                    ->findAllByAttributes(array('parent_qid' => $iQuestionId));

                foreach ($aAnsResults as $aDbAnsList) {
                    for ($x = 1; $x < 11; $x++) {
                        $tmparrayans = array('Title' => $aQuestion['title'],
                            'Display' => substr((string) $aDbAnsList->questionl10ns[$sBaseLang]->question, 0, 40) . ' [' . $x . ']',
                            'code' => $aDbAnsList['title']);
                        $aAnswerList[$aDbAnsList['title'] . "-" . $x] = $tmparrayans;
                    }
                }
                break;
            case \Question::QT_Y_YES_NO_RADIO:
                $aAnswerList = array(
                    'Y' => array('Title' => $aQuestion['title'], 'Display' => gT("Yes"), 'code' => 'Y'),
                    'N' => array('Title' => $aQuestion['title'], 'Display' => gT("No"), 'code' => 'N'));
                break;
            case \Question::QT_I_LANGUAGE:
                $slangs = $this->survey->allLanguages;

                foreach ($slangs as $key => $value) {
                    $tmparrayans = array('Title' => $aQuestion['title'],
                        'Display' => getLanguageNameFromCode($value, false), $value);
                    $aAnswerList[$value] = $tmparrayans;
                }
                break;
        }

        if (!empty($aAnswerList)) {
            // Now we mark answers already used in this quota as such
            $aExistsingAnswers = \QuotaMember::model()->findAllByAttributes(array('sid' => $this->survey->sid,
                'qid' => $iQuestionId, 'quota_id' => $iQuotaId));
            foreach ($aExistsingAnswers as $aAnswerRow) {
                if (array_key_exists($aAnswerRow['code'], $aAnswerList)) {
                    $aAnswerList[$aAnswerRow['code']]['rowexists'] = '1';
                }
            }
        }

        return  $aAnswerList;
    }

    /**
     * Saves the new quota and it's language settings.
     *
     * @param $quotaParams array the quota attributes
     * @return Quota the new quota with added QuotaLanguageSettings, or Quota with errors
     * @throws \CDbException
     */
    public function saveNewQuota(array $quotaParams): Quota
    {
        $oQuota = new Quota();
        $oQuota->sid = $this->survey->sid;
        /* new quota : remove pk */
        unset($quotaParams['id']);
        $oQuota->attributes = $quotaParams;
        if ($oQuota->save()) {
            $postQuotaLanguageSettings = (array) App()->getRequest()->getPost('QuotaLanguageSetting');
            foreach ($postQuotaLanguageSettings as $language => $settingAttributes) {
                $oQuotaLanguageSetting = new \QuotaLanguageSetting();
                $oQuotaLanguageSetting->attributes = $settingAttributes;
                $oQuotaLanguageSetting->quotals_quota_id = $oQuota->primaryKey;
                $oQuotaLanguageSetting->quotals_language = $language;

                //Clean XSS - Automatically provided by CI
                $oQuotaLanguageSetting->quotals_message = html_entity_decode($oQuotaLanguageSetting->quotals_message, ENT_QUOTES, "UTF-8");
                // Fix bug with FCKEditor saving strange BR types
                $oQuotaLanguageSetting->quotals_message = fixCKeditorText($oQuotaLanguageSetting->quotals_message);

                $oQuotaLanguageSetting->save(false);
                if (!$oQuotaLanguageSetting->validate()) {
                    $oQuota->addErrors($oQuotaLanguageSetting->getErrors());
                }
            }
            //delete quota and language settings for this qouta if errors
            if ($oQuota->getErrors()) {
                //delete quotalanguagesettings if any for this qouta
                foreach ($oQuota->languagesettings as $languageSetting) {
                    $languageSetting->delete();
                }
                $oQuota->delete();
            }
        }

        return $oQuota;
    }

    /**
     *
     * @param Quota $oQuota
     * @param array $quotaParams
     * @return bool|mixed
     */
    public function editQuota($oQuota, array $quotaParams)
    {
        $oQuota->attributes = $quotaParams;
        if ($oQuota->save()) {
            foreach ($_POST['QuotaLanguageSetting'] as $language => $settingAttributes) {
                $oQuotaLanguageSetting = $oQuota->languagesettings[$language];
                $oQuotaLanguageSetting->attributes = $settingAttributes;

                //Clean XSS - Automatically provided by CI
                $oQuotaLanguageSetting->quotals_message = html_entity_decode($oQuotaLanguageSetting->quotals_message, ENT_QUOTES, "UTF-8");
                // Fix bug with FCKEditor saving strange BR types
                $oQuotaLanguageSetting->quotals_message = fixCKeditorText($oQuotaLanguageSetting->quotals_message);

                if (!$oQuotaLanguageSetting->save()) {
                    $oQuota->addErrors($oQuotaLanguageSetting->getErrors());
                }
            }
        }

        return $oQuota;
    }

    /**
     * Retunr
     *
     * @param Quota $oQuota
     * @param $language
     * @return \QuotaLanguageSetting
     */
    public function newQuotaLanguageSetting(Quota $oQuota, $language)
    {
        $oQuotaLanguageSetting = new \QuotaLanguageSetting();
        $oQuotaLanguageSetting->quotals_name = $oQuota->name;
        $oQuotaLanguageSetting->quotals_quota_id = $oQuota->primaryKey;
        $oQuotaLanguageSetting->quotals_language = $language;
        $oQuotaLanguageSetting->quotals_url = $this->survey->languagesettings[$language]->surveyls_url;
        $siteLanguage = \Yii::app()->language;
        // Switch language temporarily to get the default text in right language
        \Yii::app()->language = $language;
        $oQuotaLanguageSetting->quotals_message = gT("Sorry your responses have exceeded a quota on this survey.");
        \Yii::app()->language = $siteLanguage;

        return $oQuotaLanguageSetting;
    }

    /**
     * Checks if all possible answers are already selected.
     *
     * @param \Question $oQuestion
     * @param array $aQuestionAnswers  array list with possible question answers
     *                                  and already used answers (see getQuotaAnswer)
     * @return bool true if all possible answers are alreday selected, false otherwise
     */
    public function allAnswersSelected(\Question $oQuestion, array $aQuestionAnswers)
    {
        $cntQuestionAnswer = 0;
        foreach ($aQuestionAnswers as $aQACheck) {
            if (isset($aQACheck['rowexists'])) {
                $cntQuestionAnswer++;
            }
        }

        return ($oQuestion->type != "*" && count($aQuestionAnswers) == $cntQuestionAnswer);
    }

    /**
     *
     * @param integer[] $aQuotaIds
     * @param string $action
     * @param null|array $languageSettings
     * @return null|array errors or null if no errors
     * @throws \CDbException
     */
    public function multipleItemsAction($aQuotaIds, $action, $languageSettings = [])
    {
        $errors = null;
        foreach ($aQuotaIds as $iQuotaId) {
            /** @var Quota $oQuota */
            $oQuota = Quota::model()->findByPk($iQuotaId);
            if (empty($oQuota) || $oQuota->sid != $this->survey->sid) {
                $errors [] = gT("Invalid quota ID");
            }
            switch ($action) {
                case 'activate':
                case 'deactivate':
                    $oQuota->active = ($action == 'activate' ? 1 : 0);
                    if (!$oQuota->save()) {
                        $errors[] = $oQuota->errors;
                    }
                    break;
                case 'delete':
                    $oQuota->delete();
                    \QuotaLanguageSetting::model()->deleteAllByAttributes(array('quotals_quota_id' => $iQuotaId));
                    \QuotaMember::model()->deleteAllByAttributes(array('quota_id' => $iQuotaId));
                    break;
                case 'changeLanguageSettings':
                    if (!empty($languageSettings)) {
                        $oQuotaLanguageSettings = $oQuota->languagesettings;
                        foreach ($_POST['QuotaLanguageSetting'] as $language => $aQuotaLanguageSettingAttributes) {
                            $oQuotaLanguageSetting = $oQuota->languagesettings[$language];
                            $oQuotaLanguageSetting->attributes = $aQuotaLanguageSettingAttributes;
                            if (!$oQuotaLanguageSetting->save()) {
                                // save errors
                                $oQuotaLanguageSettings[$language] = $oQuotaLanguageSetting;
                                $errors[] = $oQuotaLanguageSetting->errors;
                            }
                        }
                    }
                    break;
                default:
                    $errors [] = gT('No valid action');
            }
        }

        return $errors;
    }

    /**
     * Checks for a specific action if current user has the permission for it.
     *
     * @param $action
     * @return bool true if user has permission for action, false otherwise
     */
    public function checkActionPermissions($action)
    {
        switch ($action) {
            case 'activate':
            case 'deactivate':
            case 'changeLanguageSettings':
                $permissionOk = \Permission::model()->hasSurveyPermission($this->survey->sid, 'quotas', 'update');
                break;
            case 'delete':
                $permissionOk = \Permission::model()->hasSurveyPermission(
                    $this->survey->sid,
                    'quotas',
                    'delete'
                );
                break;
            default:
                $permissionOk = false;
        }
        return $permissionOk;
    }

    /**
     * checkCompletedQuota() returns matched quotas information for the current response
     * @param int $surveyid Survey ID
     * @param array $updatedValues The fields to be updated in the current request
     * @param bool $return Set to true to return information or false to execute the quota and display a message
     * @return array|void Nested array, Quotas->Members->Fields, includes quota information matched in session
     * @throws \CException
     * @throws \CHttpException
     * @throws \Throwable
     * @throws \WrongTemplateVersionException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function checkCompletedQuota(int $surveyid, array $updatedValues = [], bool $return = false)
    {
        /* Check if session is set */
        if (!isset(App()->session['survey_' . $surveyid]['srid'])) {
            return;
        }
        /* Check if Response is already submitted : only when "do" the quota: allow to send information about quota */
        $oResponse = Response::model($surveyid)->findByPk(App()->session['survey_' . $surveyid]['srid']);
        if (!$return && $oResponse && !is_null($oResponse->submitdate)) {
            return;
        }
        // EM call 2 times quotas with 3 lines of php code, then use static.
        static $aMatchedQuotas;
        if (is_null($aMatchedQuotas)) {
            $aMatchedQuotas = [];
            /** @var Quota[] $aQuotas */
            $aQuotas = Quota::model()
                ->with('languagesettings', 'quotaMembers.question')
                ->findAllByAttributes(['sid' => $surveyid]);
            if (empty($aQuotas)) {
                return $aMatchedQuotas;
            }

            // OK, we have some quota, then find if this $_SESSION have some set
            // foreach ($aQuotasInfos as $aQuotaInfo)
            foreach ($aQuotas as $oQuota) {
                // if(!$aQuotaInfo['active']) {
                if (!$oQuota->active) {
                    continue;
                }
                // if(count($aQuotaInfo['members'])===0) {
                if (count($oQuota->quotaMembers) === 0) {
                    continue;
                }
                $iMatchedAnswers = 0;
                $bPostedField = false;

                ////Create filtering
                // Array of field with quota array value
                $aQuotaFields = [];
                // Array of fieldnames with relevance value : EM fill $_SESSION with default value even is irrelevant (em_manager_helper line 6548)
                $aQuotaRelevantFieldnames = [];
                // To count number of hidden questions
                $aQuotaQid = [];
                //Fill the necessary filter arrays
                foreach ($oQuota->quotaMembers as $oQuotaMember) {
                    $aQuotaMember = $oQuotaMember->getMemberInfo();
                    $aQuotaFields[$aQuotaMember['fieldname']][] = $aQuotaMember['value'];
                    $aQuotaRelevantFieldnames[$aQuotaMember['fieldname']] = isset($_SESSION['survey_' . $surveyid]['relevanceStatus'][$aQuotaMember['qid']]) && $_SESSION['survey_' . $surveyid]['relevanceStatus'][$aQuotaMember['qid']];
                    $aQuotaQid[] = $aQuotaMember['qid'];
                }
                $aQuotaQid = array_unique($aQuotaQid);

                // Filter
                // For each field : test if actual responses is in quota (and is relevant)
                foreach ($aQuotaFields as $sFieldName => $aValues) {
                    $bInQuota = isset($_SESSION['survey_' . $surveyid][$sFieldName])
                        && in_array($_SESSION['survey_' . $surveyid][$sFieldName], $aValues);
                    if ($bInQuota && $aQuotaRelevantFieldnames[$sFieldName]) {
                        $iMatchedAnswers++;
                    }
                    if (!is_null(App()->request->getPost($sFieldName))) {
                        // Need only one posted value
                        $bPostedField = true;
                        $aPostedQuotaFields[$sFieldName] = App()->getRequest()->getPost($sFieldName);
                    }
                }

                ################ QUOTA ACTIONS START ################
                $quotaAction = (int)$oQuota->action;
                $actionBasedTriggerCondition = false;

                if ($quotaAction === Quota::TERMINATE_VISIBLE_QUOTA_QUESTIONS) {
                    // if all quota members are hidden questions, the quota will be triggered as well
                    // if we have quota members that are hidden mixed with visible ones, they will never be triggered.
                    $actionBasedTriggerCondition = (int)QuestionAttribute::model()
                            ->countByAttributes([
                                'qid'       => $aQuotaQid,
                                'attribute' => 'hidden',
                                'value'     => '1',
                            ]) === count($aQuotaQid);
                }
                if ($quotaAction === Quota::TERMINATE_VISIBLE_AND_HIDDEN_QUOTA_QUESTIONS) {
                    $actionBasedTriggerCondition = !empty(array_intersect_key($updatedValues, $aQuotaRelevantFieldnames));
                }
                if ($quotaAction === Quota::TERMINATE_ALL_PAGES) {
                    $actionBasedTriggerCondition = true;
                }
                if ($quotaAction === Quota::SOFT_TERMINATE_VISIBLE_QUOTA_QUESTIONS) {
                    $actionBasedTriggerCondition = false;
                }
                ################ QUOTA ACTIONS END ################

                // condition to count quota
                // check if all answers match the quota AND check if the question was answered on this page OR if all questions are hidden
                if (
                    $iMatchedAnswers === count($aQuotaFields)
                    && ($bPostedField || $actionBasedTriggerCondition)
                ) {
                    if ($oQuota->qlimit === 0) {
                        // Always add the quota if qlimit is 0
                        $aMatchedQuotas[] = $oQuota->getViewArray();
                    } else {
                        $iCompleted = $oQuota->completeCount;
                        if (!is_null($iCompleted) && ($iCompleted >= $oQuota->qlimit)) {
                            // This remove invalid quota and not completed
                            $aMatchedQuotas[] = $oQuota->getViewArray();
                        }
                    }
                }
            }
        }
        if ($return) {
            return $aMatchedQuotas;
        }
        if (empty($aMatchedQuotas)) {
            return;
        }
        // Now we have all the information we need about the quotas and their status.
        // We need to construct the page and do all needed action
        $aSurveyInfo = getSurveyInfo($surveyid, $_SESSION['survey_' . $surveyid]['s_lang']);
        $sClientToken = $_SESSION['survey_' . $surveyid]['token'] ?? "";
        // $redata for templatereplace
        $aDataReplacement = [
            'thissurvey'  => $aSurveyInfo,
            'clienttoken' => $sClientToken,
            'token'       => $sClientToken,
        ];
        // We take only the first matched quota, no need for each
        $aMatchedQuota = $aMatchedQuotas[0];
        // If a token is used then mark the token as completed, do it before event : this allow plugin to update token information
        $event = new PluginEvent('afterSurveyQuota');
        $event->set('surveyId', $surveyid);
        $event->set('responseId', $_SESSION['survey_' . $surveyid]['srid']); // We always have a responseId
        $event->set('aMatchedQuotas', $aMatchedQuotas); // Give all the matched quota : the first is the active
        App()->getPluginManager()->dispatchEvent($event);
        $blocks = [];
        foreach ($event->getAllContent() as $blockData) {
            /* @var $blockData PluginEventContent */
            $blocks[] = CHtml::tag(
                'div',
                ['id' => $blockData->getCssId(), 'class' => $blockData->getCssClass()],
                $blockData->getContent()
            );
        }
        // Allow plugin to update message, url, url description and action
        $sMessage = $event->get('message', $aMatchedQuota['quotals_message']);
        $sUrl = $event->get('url', $aMatchedQuota['quotals_url']);
        $sUrlDescription = $event->get('urldescrip', $aMatchedQuota['quotals_urldescrip']);
        $sAction = (int) $event->get('action', $aMatchedQuota['action']);
        // close the survey only when the action is a terminate type or when confirmquota is called as move action
        /**
         * @todo: 2026-01-05: Is confirmquota action still used?
         *        The "confirmquota" button was commented out on commit 9e678fb (Ticket #14652)
         */
        $closeSurvey = ($sAction !== Quota::SOFT_TERMINATE_VISIBLE_QUOTA_QUESTIONS || App()->getRequest()->getPost('move') === 'confirmquota');
        $sAutoloadUrl = $event->get('autoloadurl', $aMatchedQuota['autoload_url']);
        // If action is Terminate, stamp the quota id in the response and marks tokens as completed
        if (
            in_array($sAction, [
                Quota::TERMINATE_VISIBLE_QUOTA_QUESTIONS,
                Quota::TERMINATE_VISIBLE_AND_HIDDEN_QUOTA_QUESTIONS,
                Quota::TERMINATE_ALL_PAGES
            ], true)
        ) {
            // Update the response's "quota_exit" attribute with the ID of the matched quota
            self::updateResponseQuotaExit($oResponse, $aMatchedQuota['id']);

            if ($sClientToken) {
                submittokens(true);
            }
        }
        // Construct the default message
        $sMessage = templatereplace(
            $sMessage,
            [],
            $aDataReplacement,
            null,
            null,
            null,
            null,
            true
        );
        $sUrl = passthruReplace($sUrl, $aSurveyInfo);
        $sUrl = templatereplace(
            $sUrl,
            [],
            $aDataReplacement,
            null,
            null,
            null,
            null,
            true
        );
        $sUrlDescription = templatereplace(
            $sUrlDescription,
            [],
            $aDataReplacement,
            null,
            null,
            null,
            null,
            true
        );

        // Datas for twig view
        $thissurvey['sid'] = $surveyid;
        $thissurvey['aQuotas'] = [];
        $thissurvey['aQuotas']['sMessage'] = $sMessage;
        $thissurvey['aQuotas']['bShowNavigator'] = !$closeSurvey;
        $thissurvey['aQuotas']['sClientToken'] = $sClientToken;
        $thissurvey['aQuotas']['sQuotaStep'] = 'returnfromquota';
        $thissurvey['aQuotas']['aPostedQuotaFields'] = $aPostedQuotaFields ?? '';
        $thissurvey['aQuotas']['sPluginBlocks'] = implode("\n", $blocks);
        $thissurvey['aQuotas']['sUrlDescription'] = $sUrlDescription;
        $thissurvey['aQuotas']['sUrl'] = $sUrl;
        $thissurvey['active'] = 'Y';
        $thissurvey['aQuotas']['hiddeninputs'] = '<input type="hidden" name="sid"      value="' . $surveyid . '" />
                                              <input type="hidden" name="token"    value="' . $thissurvey['aQuotas']['sClientToken'] . '" />
                                              <input type="hidden" name="thisstep" value="' . ($_SESSION['survey_' . $surveyid]['step'] ?? 0) . '" />';
        if (!empty($thissurvey['aQuotas']['aPostedQuotaFields'])) {
            foreach ($thissurvey['aQuotas']['aPostedQuotaFields'] as $field => $post) {
                $thissurvey['aQuotas']['hiddeninputs'] .= '<input type="hidden" name="' . $field . '"   value="' . $post . '" />';
            }
        }

        //field,post in aSurveyInfo.aQuotas.aPostedQuotaFields %}
        if ($closeSurvey) {
            killSurveySession($surveyid);

            if ((int) $sAutoloadUrl === 1 && $sUrl !== "") {
                /* Same than end url of survey */
                $headToSurveyUrl = htmlspecialchars_decode($sUrl);
                header("Location: " . $headToSurveyUrl);
            }
        }
        $thissurvey['include_content'] = 'quotas';
        App()->twigRenderer->renderTemplateFromFile(
            "layout_global.twig",
            ['oSurvey' => Survey::model()->findByPk($surveyid), 'aSurveyInfo' => $thissurvey],
            false
        );
    }

    public static function updateResponseQuotaExit(Response $response, ?int $quotaId): void
    {
        $response->quota_exit = $quotaId;
        $response->save(false, ['quota_exit']);
    }
}
