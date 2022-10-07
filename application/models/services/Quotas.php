<?php

namespace LimeSurvey\Models\Services;

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
    public function getQuotaStructure(){
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
     * Get Quota Answers
     *
     * todo: rewrite this function, use switch instead of if-elseif...(done!) and create OOPs for questiontypes
     * @param integer $iQuestionId
     * @param integer $iQuotaId
     * @return array
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
        switch ($aQuestionType){
            case \Question::QT_M_MULTIPLE_CHOICE:
                $aResults = \Question::model()
                    ->with('questionl10ns', array('language' => $sBaseLang))
                    ->findAllByAttributes(array('parent_qid' => $iQuestionId));
                foreach ($aResults as $oDbAnsList) {
                    $tmparrayans = array('Title' => $aQuestion['title'],
                        'Display' => substr($oDbAnsList->questionl10ns[$sBaseLang]->question, 0, 40),
                        'code' => $oDbAnsList->title);
                    $aAnswerList[$oDbAnsList->title] = $tmparrayans;
                }
                break;
            case  \Question::QT_G_GENDER:
                $aAnswerList = array(
                    'M' => array('Title' => $aQuestion['title'], 'Display' => gT("Male"), 'code' => 'M'),
                    'F' => array('Title' => $aQuestion['title'], 'Display' => gT("Female"), 'code' => 'F'));
                break;
            case  \Question::QT_L_LIST:
            case  \Question::QT_O_LIST_WITH_COMMENT:
            case  \Question::QT_EXCLAMATION_LIST_DROPDOWN:
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
                            'Display' => substr($aDbAnsList->questionl10ns[$sBaseLang]->question, 0, 40) . ' [' . $x . ']',
                            'code' => $aDbAnsList['title']);
                        $aAnswerList[$aDbAnsList['title'] . "-" . $x] = $tmparrayans;
                    }
                }
                break;
            case  \Question::QT_B_ARRAY_10_CHOICE_QUESTIONS:
                $aAnsResults = \Question::model()
                    ->with('questionl10ns', array('language' => $sBaseLang))
                    ->findAllByAttributes(array('parent_qid' => $iQuestionId));

                foreach ($aAnsResults as $aDbAnsList) {
                    for ($x = 1; $x < 11; $x++) {
                        $tmparrayans = array('Title' => $aQuestion['title'],
                            'Display' => substr($aDbAnsList->questionl10ns[$sBaseLang]->question, 0, 40) . ' [' . $x . ']',
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

        if (!empty($aAnswerList)){
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


    public function saveNewQuota($Quotaparams){
        $oQuota = new \Quota();
        $oQuota->sid = $this->survey->sid;

        $oQuota->attributes = $_POST['Quota'];

        $savingOk = $oQuota->save();
        if ($savingOk) {
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
                //self::redirectToIndex($surveyid);
                $this->redirect($this->createUrl("quotas/index/surveyid/$surveyid"));
            } else {
                //todo also delete already created QuotaLanguageSettings

                // if any of the parts fail to save we delete the quota and try again
                $oQuota->delete();
            }
        }

        return $savingOk;
    }
}
