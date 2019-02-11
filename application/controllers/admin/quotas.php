<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/**
 * Quotas Controller
 *
 * This controller performs quota actions
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class quotas extends Survey_Common_Action
{

    public function __construct($controller, $id)
    {
        parent::__construct($controller, $id);

        // Load helpers
        Yii::app()->loadHelper('surveytranslator');
        // Sanitize/get globals/variables
        $_POST['quotamax'] = sanitize_int(Yii::app()->request->getPost('quotamax'));

        if (empty($_POST['autoload_url'])) {
            $_POST['autoload_url'] = 0;
        }

        if (empty($_POST['quota_limit']) || !is_numeric(Yii::app()->request->getPost('quota_limit')) || Yii::app()->request->getPost('quota_limit') < 0) {
            $_POST['quota_limit'] = 0;
        }
    }

    private function _getData($iSurveyId)
    {
        // Set the variables in an array
        $aData['iSurveyId'] = $aData['surveyid'] = $iSurveyId;
        $aData['sBaseLang'] = Survey::model()->findByPk($iSurveyId)->language;
        $aData['aLangs'] = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
        array_unshift($aData['aLangs'], $aData['sBaseLang']);

        $aData['action'] = $action = Yii::app()->request->getParam('action');
        if (!isset($action)) {
                    $aData['action'] = 'quotas';
        }

        return $aData;
    }

    /**
     * @param string $sPermission
     */
    private function _checkPermissions($iSurveyId, $sPermission)
    {
        if (!empty($sPermission) && !(Permission::model()->hasSurveyPermission($iSurveyId, 'quotas', $sPermission))) {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->_redirectToIndex($iSurveyId);
        }
    }

    private function _redirectToIndex($iSurveyId)
    {
        if (Permission::model()->hasSurveyPermission($iSurveyId, 'quotas', 'read')) {
            $this->getController()->redirect($this->getController()->createUrl("/admin/quotas/sa/index/surveyid/$iSurveyId"));
        } else {
            Yii::app()->session['flashmessage'] = gT('Access denied!');
            $this->getController()->redirect($this->getController()->createUrl("admin/survey/sa/view/surveyid/$iSurveyId"));
        }
    }

    public function massiveAction()
    {

        $action = Yii::app()->request->getQuery('action');
        $allowedActions = array('activate', 'deactivate', 'delete', 'changeLanguageSettings');
        if (isset($_POST) && in_array($action, $allowedActions)) {
            $sItems = Yii::app()->request->getPost('sItems');
            $aQuotaIds = json_decode($sItems);
            $errors = array();
            foreach ($aQuotaIds as $iQuotaId) {
                /** @var Quota $oQuota */
                $oQuota = Quota::model()->findByPk($iQuotaId);
                if (in_array($action, array('activate', 'deactivate'))) {
                    $oQuota->active = ($action == 'activate' ? 1 : 0);
                    $oQuota->save();
                } elseif ($action == 'delete') {
                    $oQuota->delete();
                } elseif ($action == 'changeLanguageSettings' && !empty($_POST['QuotaLanguageSetting'])) {
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
                    // render form again to display errorSummary
                    if (!empty($errors)) {
                        $this->getController()->renderPartial('/admin/quotas/viewquotas_massive_langsettings_form',
                            array(
                                'oQuota'=>$oQuota,
                                'aQuotaLanguageSettings'=>$oQuotaLanguageSettings,
                            ));
                        return;
                    }
                }
            }
            if (empty($errors)) {
                eT("OK!");
            }
        }
    }

    public function index($iSurveyId, $quickreport = false)
    {

        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'read');
        $aData = $this->_getData($iSurveyId);
        $aViewUrls = array();

        if ($quickreport == false) {
            $aViewUrls[] = 'viewquotas_view';
        }

        $aData['surveyid'] = $iSurveyID = $surveyid = sanitize_int($iSurveyId);

        $aData['sidemenu']['state'] = false;

        /** @var Survey $oSurvey */
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";
        $aData['subaction'] = gT("Survey quotas");

        //$aData['surveybar']['active_survey_properties'] = 'quotas';
        $aData['surveybar']['buttons']['view'] = true;
        $aData['surveybar']['active_survey_properties']['img'] = 'quota';
        $aData['surveybar']['active_survey_properties']['txt'] = gT("Quotas");
        $aData['surveybar']['closebutton']['url'] = 'admin/survey/sa/view/surveyid/'.$iSurveyID; // Close button
        $aData['surveybar']['closebutton']['forbidden'][] = 'quotas';

        $totalquotas = 0;
        $totalcompleted = 0;
        $csvoutput = array();

        // Set number of page
        if (Yii::app()->getRequest()->getQuery('pageSize')) {
            Yii::app()->user->setState('pageSize', (int) Yii::app()->getRequest()->getQuery('pageSize'));
        }
        $aData['iGridPageSize'] = Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']);
        $aData['oDataProvider'] = new CArrayDataProvider($oSurvey->quotas, array(
            'pagination' => array(
                'pageSize' => $aData['iGridPageSize'],
                'pageVar' => 'page'
            ),
        ));


        //if there are quotas let's proceed
        $aViewUrls['output'] = '';
        if (!empty($oSurvey->quotas)) {
            $aData['output'] = '';
            $aQuotaItems = array();

            //loop through all quotas
            foreach ($oSurvey->quotas as $oQuota) {
                $totalquotas += $oQuota->qlimit;
                $completed = 0;
                $completed = $oQuota->completeCount;
                $totalcompleted = $totalcompleted + $completed;
                $csvoutput[] = $oQuota->name.",".$oQuota->qlimit.",".$completed.",".($oQuota->qlimit - $completed)."\r\n";

                if ($quickreport != false) {
                    continue;
                }

                // Edit URL
                $aData['aEditUrls'][$oQuota->primaryKey] = App()->createUrl("admin/quotas/sa/editquota/surveyid/".$iSurveyId, array(
                    'sid' => $iSurveyId,
                    'action' => 'quotas',
                    'quota_id' => $oQuota->primaryKey,
                    'subaction' => 'quota_editquota'

                ));

                // Delete URL
                $aData['aDeleteUrls'][$oQuota->primaryKey] = App()->createUrl("admin/quotas/sa/delquota/surveyid/".$iSurveyId, array(
                    'sid' => $iSurveyId,
                    'action' => 'quotas',
                    'quota_id' => $oQuota->primaryKey,
                    'subaction' => 'quota_delquota'
                ));

                //loop through all sub-parts
                foreach ($oQuota->quotaMembers as $oQuotaMember) {
                    $aQuestionAnswers = self::getQuotaAnswers($oQuotaMember['qid'], $iSurveyId, $oQuota['id']);
                    if ($oQuotaMember->question->type == '*') {
                        $answerText = $oQuotaMember->code;
                    } else {
                        $answerText = isset($aQuestionAnswers[$oQuotaMember['code']]) ? flattenText($aQuestionAnswers[$oQuotaMember['code']]['Display']) : null;
                    }

                    $aQuotaItems[$oQuota['id']][] = array(
                        'oQuestion' => Question::model()->findByPk(array('qid' => $oQuotaMember['qid'], 'language' => $oSurvey->language)),
                        'answer_title' => $answerText,
                        'oQuotaMember'=>$oQuotaMember,
                        'valid'=>isset($answerText),
                    );
                }

            }
            $aData['totalquotas'] = $totalquotas;
            $aData['totalcompleted'] = $totalcompleted;
            $aData['aQuotaItems'] = $aQuotaItems;

            // take the last quota as base for bulk edits
            $aData['oQuota'] = $oQuota;
            $aData['aQuotaLanguageSettings'] = array();
            foreach ($oQuota->languagesettings as $languagesetting) {
                $aData['aQuotaLanguageSettings'][$languagesetting->quotals_language] = $languagesetting;
            }
        } else {
            // No quotas have been set for this survey
            //$aViewUrls[] = 'viewquotasempty_view';
            $aData['output'] = $this->getController()->renderPartial('/admin/quotas/viewquotasempty_view', $aData, true);
        }

        $aData['totalquotas'] = $totalquotas;
        $aData['totalcompleted'] = $totalcompleted;

        if ($quickreport == false) {
            $this->_renderWrappedTemplate('quotas', $aViewUrls, $aData);
        } else {
            /* Export a quickly done csv file */
            header("Content-Disposition: attachment; filename=quotas-survey".$iSurveyId.".csv");
            header("Content-type: text/comma-separated-values; charset=UTF-8");
            header("Pragma: public");
            echo gT("Quota name").",".gT("Limit").",".gT("Completed").",".gT("Remaining")."\r\n";
            foreach ($csvoutput as $line) {
                echo $line;
            }
            App()->end();
        }
    }


    public function insertquotaanswer($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'update');

        $oQuotaMembers = new QuotaMember('create'); // Trigger the 'create' rules
        $oQuotaMembers->sid = $iSurveyId;
        $oQuotaMembers->qid = Yii::app()->request->getPost('quota_qid');
        $oQuotaMembers->quota_id = Yii::app()->request->getPost('quota_id');
        $oQuotaMembers->code = Yii::app()->request->getPost('quota_anscode');
        if ($oQuotaMembers->save()) {
            if (!empty($_POST['createanother'])) {
                $_POST['action'] = "quotas";
                $_POST['subaction'] = "new_answer";
                $sSubAction = "new_answer";
                self::new_answer($iSurveyId, $sSubAction);
            } else {
                self::_redirectToIndex($iSurveyId);
            }
        } else {
            // Save was not successful, redirect back
            $_POST['action'] = "quotas";
            $_POST['subaction'] = "new_answer";
            $sSubAction = "new_answer_two";
            self::new_answer($iSurveyId, $sSubAction);
        }
    }

    public function delans($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'update');

        QuotaMember::model()->deleteAllByAttributes(array(
            'id' => Yii::app()->request->getPost('quota_member_id'),
            'qid' => Yii::app()->request->getPost('quota_qid'),
            'code' => Yii::app()->request->getPost('quota_anscode'),
        ));

        self::_redirectToIndex($iSurveyId);
    }

    public function delquota($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'delete');

        $quotaId = Yii::app()->request->getQuery('quota_id');

        Quota::model()->deleteByPk($quotaId);
        QuotaLanguageSetting::model()->deleteAllByAttributes(array('quotals_quota_id' => $quotaId));
        QuotaMember::model()->deleteAllByAttributes(array('quota_id' => $quotaId));

        Yii::app()->user->setFlash('success', sprintf(gT("Quota with ID %s was deleted"), $quotaId));

        self::_redirectToIndex($iSurveyId);
    }

    function editquota($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'update');
        $aData = $this->_getData($iSurveyId);
        $aViewUrls = array();
        $quotaId = Yii::app()->request->getQuery('quota_id');

        /* @var Quota $oQuota */
        $oQuota = Quota::model()->findByPk($quotaId);

        if (isset($_POST['Quota'])) {
            $oQuota->attributes = $_POST['Quota'];
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
                if (!$oQuota->getErrors()) {
                    Yii::app()->user->setFlash('success', gT("Quota saved"));
                    self::_redirectToIndex($iSurveyId);
                }
            }
        }


        $aData['oQuota'] = $oQuota;
        $aData['aQuotaLanguageSettings'] = array();
        foreach ($oQuota->languagesettings as $languagesetting) {
            $aData['aQuotaLanguageSettings'][$languagesetting->quotals_language] = $languagesetting;
        }

        $aViewUrls[] = 'editquota_view';

        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyId.")";

        //$aData['surveybar']['active_survey_properties'] = 'quotas';
        $aData['surveybar']['closebutton']['url'] = 'admin/quotas/sa/index/surveyid/'.$iSurveyId; // Close button
        $aData['surveybar']['savebutton']['form'] = 'frmeditgroup';


        $this->_renderWrappedTemplate('quotas', $aViewUrls, $aData);
    }

    /**
     * Add new answer to quota
     */
    public function new_answer($iSurveyId, $sSubAction = 'new_answer')
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $oSurvey = Survey::model()->findByPk($iSurveyId);

        $this->_checkPermissions($iSurveyId, 'update');
        $aData = $this->_getData($iSurveyId);
        $aViewUrls = array();
        $quota = Quota::model()->findByPk(Yii::app()->request->getPost('quota_id'));
        $aData['oQuota'] = $quota;

        if (($sSubAction == "new_answer" || ($sSubAction == "new_answer_two" && !isset($_POST['quota_qid']))) && Permission::model()->hasSurveyPermission($iSurveyId, 'quotas', 'create')) {

            $result = $oSurvey->quotableQuestions;
            if (empty($result)) {
                $aViewUrls[] = 'newanswererror_view';
            } else {
                $aViewUrls[] = 'newanswer_view';
            }
        }

        if ($sSubAction == "new_answer_two" && isset($_POST['quota_qid']) && Permission::model()->hasSurveyPermission($iSurveyId, 'quotas', 'create')) {
            $oQuestion = Question::model()->findByPk(array('qid' => Yii::app()->request->getPost('quota_qid'), 'language' => $oSurvey->language));

            $aQuestionAnswers = self::getQuotaAnswers(Yii::app()->request->getPost('quota_qid'), $iSurveyId, Yii::app()->request->getPost('quota_id'));
            $x = 0;

            foreach ($aQuestionAnswers as $aQACheck) {
                if (isset($aQACheck['rowexists'])) {
                                    $x++;
                }
            }

            reset($aQuestionAnswers);
            $aData['oQuestion'] = $oQuestion;
            $aData['question_answers'] = $aQuestionAnswers;
            $aData['x'] = $x;
            $aViewUrls[] = 'newanswertwo_view';
        }

        $aData['sidemenu']['state'] = false;
        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyId.")";
        $aData['surveybar']['closebutton']['url'] = 'admin/quotas/sa/index/surveyid/'.$iSurveyId; // Close button
        $aData['surveybar']['closebutton']['forbidden'][] = 'new_answer';

        $this->_renderWrappedTemplate('quotas', $aViewUrls, $aData);
    }

    public function newquota($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $oSurvey = Survey::model()->findByPk($iSurveyId);
        $this->_checkPermissions($iSurveyId, 'create');
        $aData = $this->_getData($iSurveyId);

        $aData['thissurvey'] = getSurveyInfo($iSurveyId);
        $aData['langs'] = $aData['aLangs'];
        $aData['baselang'] = $aData['sBaseLang'];

        $aData['sidemenu']['state'] = false;

        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyId.")";
        $aData['surveybar']['savebutton']['form'] = 'frmeditgroup';
        $aData['surveybar']['closebutton']['url'] = 'admin/quotas/sa/index/surveyid/'.$iSurveyId; // Close button

        $oQuota = new Quota();
        $oQuota->sid = $oSurvey->primaryKey;


        if (isset($_POST['Quota'])) {
            $oQuota->attributes = $_POST['Quota'];
            if ($oQuota->save()) {
                foreach ($_POST['QuotaLanguageSetting'] as $language => $settingAttributes) {
                    $oQuotaLanguageSetting = new QuotaLanguageSetting();
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
                if (!$oQuota->getErrors()) {
                    Yii::app()->user->setFlash('success', gT("New quota saved"));
                    self::_redirectToIndex($iSurveyId);
                } else {
                    // if any of the parts fail to save we delete the quota and and try again
                    $oQuota->delete();
                }
            }
        }

        $aData['oQuota'] = $oQuota;
        $aData['oSurvey'] = $oSurvey;
        // create QuotaLanguageSettings
        foreach ($oSurvey->getAllLanguages() as $language) {
            $oQuotaLanguageSetting = new QuotaLanguageSetting();
            $oQuotaLanguageSetting->quotals_name = $oQuota->name;
            $oQuotaLanguageSetting->quotals_quota_id = $oQuota->primaryKey;
            $oQuotaLanguageSetting->quotals_language = $language;
            $oQuotaLanguageSetting->quotals_url = $oSurvey->languagesettings[$language]->surveyls_url;
            $siteLanguage = Yii::app()->language;
            // Switch language temporarily to get the default text in right language
            Yii::app()->language = $language;
            $oQuotaLanguageSetting->quotals_message = gT("Sorry your responses have exceeded a quota on this survey.");
            Yii::app()->language = $siteLanguage;
            $aData['aQuotaLanguageSettings'][$language] = $oQuotaLanguageSetting;
        }

        $this->_renderWrappedTemplate('quotas', 'newquota_view', $aData);
    }

    /**
     *
     * @param integer $iQuestionId
     * @param integer $iSurveyId
     * @param integer $iQuotaId
     * @return array
     */
    public function getQuotaAnswers($iQuestionId, $iSurveyId, $iQuotaId)
    {
        $iQuestionId = sanitize_int($iQuestionId);
        $iSurveyId   = sanitize_int($iSurveyId);
        $iQuotaId    = sanitize_int($iQuotaId);
        $aData       = $this->_getData($iSurveyId);
        $sBaseLang   = $aData['sBaseLang'];
        $this->_checkPermissions($iSurveyId, 'read');


        $aQuestion = Question::model()->findByPk(array('qid' => $iQuestionId, 'language' => $sBaseLang));
        $aQuestionType = $aQuestion['type'];

        if ($aQuestionType == 'M') {
            $aResults = Question::model()->findAllByAttributes(array('parent_qid' => $iQuestionId));
            $aAnswerList = array();

            foreach ($aResults as $aDbAnsList) {
                $tmparrayans = array('Title' => $aQuestion['title'], 'Display' => substr($aDbAnsList['question'], 0, 40), 'code' => $aDbAnsList['title']);
                $aAnswerList[$aDbAnsList['title']] = $tmparrayans;
            }
        } elseif ($aQuestionType == 'G') {
            $aAnswerList = array(
                'M' => array('Title' => $aQuestion['title'], 'Display' => gT("Male"), 'code' => 'M'),
                'F' => array('Title' => $aQuestion['title'], 'Display' => gT("Female"), 'code' => 'F'));
        } elseif ($aQuestionType == 'L' || $aQuestionType == 'O' || $aQuestionType == '!') {

            $aAnsResults = Answer::model()->findAllByAttributes(array('qid' => $iQuestionId, 'language' => $sBaseLang));

            $aAnswerList = array();

            foreach ($aAnsResults as $aDbAnsList) {
                $aAnswerList[$aDbAnsList['code']] = array('Title' => $aQuestion['title'], 'Display' => $aDbAnsList['answer'], 'code' => $aDbAnsList['code']);
            }

        } elseif ($aQuestionType == 'A') {
            $aAnsResults = Question::model()->findAllByAttributes(array('parent_qid' => $iQuestionId));

            $aAnswerList = array();

            foreach ($aAnsResults as $aDbAnsList) {
                for ($x = 1; $x < 6; $x++) {
                    $tmparrayans = array('Title' => $aQuestion['title'], 'Display' => substr($aDbAnsList['question'], 0, 40).' ['.$x.']', 'code' => $aDbAnsList['title']);
                    $aAnswerList[$aDbAnsList['title']."-".$x] = $tmparrayans;
                }
            }
        } elseif ($aQuestionType == 'B') {
            $aAnsResults = Answer::model()->findAllByAttributes(array('qid' => $iQuestionId, 'language' => $sBaseLang));

            $aAnswerList = array();

            foreach ($aAnsResults as $aDbAnsList) {
                for ($x = 1; $x < 11; $x++) {
                    $tmparrayans = array('Title' => $aQuestion['title'], 'Display' => substr($aDbAnsList['answer'], 0, 40).' ['.$x.']', 'code' => $aDbAnsList['code']);
                    $aAnswerList[$aDbAnsList['code']."-".$x] = $tmparrayans;
                }
            }
        } elseif ($aQuestionType == 'Y') {
            $aAnswerList = array(
                'Y' => array('Title' => $aQuestion['title'], 'Display' => gT("Yes"), 'code' => 'Y'),
                'N' => array('Title' => $aQuestion['title'], 'Display' => gT("No"), 'code' => 'N'));
        } elseif ($aQuestionType == 'I') {
            $slangs = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
            array_unshift($slangs, $sBaseLang);

            foreach($slangs as $key => $value) {
                $tmparrayans = array('Title' => $aQuestion['title'], 'Display' => getLanguageNameFromCode($value, false), $value);
                $aAnswerList[$value] = $tmparrayans;
            }
        }

        if (empty($aAnswerList)) {
            return array();
        } else {
            // Now we mark answers already used in this quota as such
            $aExistsingAnswers = QuotaMember::model()->findAllByAttributes(array('sid' => $iSurveyId, 'qid' => $iQuestionId, 'quota_id' => $iQuotaId));
            foreach ($aExistsingAnswers as $aAnswerRow) {
                if (array_key_exists($aAnswerRow['code'], $aAnswerList)) {
                    $aAnswerList[$aAnswerRow['code']]['rowexists'] = '1';
                }
            }
            return $aAnswerList;
        }
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'quotas', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'quotas.js');
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

}
