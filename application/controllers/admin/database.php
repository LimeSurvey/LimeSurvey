<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
* LimeSurvey
* Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

use \LimeSurvey\Helpers\questionHelper;

/**
* Database
*
* @package LimeSurvey
* @author
* @copyright 2011
* @access public
*/
class database extends Survey_Common_Action
{
    /**
     * @var integer Group id
     */
    private $iQuestionGroupID;

    /**
     * @var integer Question id
     */
    private $iQuestionID;

    /**
     * @var integer Survey id
     */
    private $iSurveyID;


    private $updateableFields = [
                'owner_id' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'admin' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'faxto' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'format' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'expires' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'startdate' => ['type'=> 'default', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'template' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'assessments' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'anonymized' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'savetimings' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'datestamp' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'ipaddr' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'refurl' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'publicgraphs' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'usecookie' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'allowregister' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'allowsave' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'navigationdelay' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'printanswers' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'publicstatistics' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'autoredirect' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'showxquestions' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'showgroupinfo' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'showqnumcode' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'shownoanswer' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'showwelcome' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'showsurveypolicynotice' => ['type'=> '', 'default' => 0, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'allowprev' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'questionindex' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'nokeyboard' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'showprogress' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'listpublic' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'htmlemail' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'sendconfirmation' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'tokenanswerspersistence' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'alloweditaftercompletion' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'emailresponseto' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'emailnotificationto' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'googleanalyticsapikeysetting' => ['type'=> 'default', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'googleanalyticsapikey' => ['type'=> 'default', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'googleanalyticsstyle' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'tokenlength' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'adminemail' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'bounce_email' => ['type'=> '', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'gsid' => ['type'=> '', 'default' => 1, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'usecaptcha_surveyaccess' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'usecaptcha_registration' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
                'usecaptcha_saveandload' => ['type'=> 'yesno', 'default' => false, 'dbname'=>false, 'active'=>true, 'required'=>[]],
            ];
        private $updatedFields = [];

    /**
     * @var object LSYii_Validators
     * @todo : use model (and validate if we do it in model rules)
     */
    private $oFixCKeditor;

    /**
     * Database::index()
     *
     * @return
     */
    public function index()
    {
        $sAction = Yii::app()->request->getPost('action');
        $this->iSurveyID = (isset($_POST['sid'])) ? (int) $_POST['sid'] : (int) returnGlobal('sid');

        $this->iQuestionGroupID = (int) returnGlobal('gid');
        $this->iQuestionID = (int) returnGlobal('qid');

        $this->oFixCKeditor = new LSYii_Validators;
        $this->oFixCKeditor->fixCKeditor = true;
        $this->oFixCKeditor->xssfilter = false;

        if ($sAction == "updatedefaultvalues" && Permission::model()->hasSurveyPermission($this->iSurveyID, 'surveycontent', 'update')) {
            $this->actionUpdateDefaultValues($this->iSurveyID);
        }
        if ($sAction == "updateansweroptions" && Permission::model()->hasSurveyPermission($this->iSurveyID, 'surveycontent', 'update')) {
            $this->actionUpdateAnswerOptions($this->iSurveyID);
        }
        if ($sAction == "updatesubquestions" && Permission::model()->hasSurveyPermission($this->iSurveyID, 'surveycontent', 'update')) {
            $this->actionSubQuestions($this->iSurveyID);
        }
        if (in_array($sAction, array('insertquestion', 'copyquestion')) && Permission::model()->hasSurveyPermission($this->iSurveyID, 'surveycontent', 'create')) {
            $this->actionInsertCopyQuestion($this->iSurveyID);
        }
        if ($sAction == "updatequestion" && Permission::model()->hasSurveyPermission($this->iSurveyID, 'surveycontent', 'update')) {
            $this->actionUpdateQuestion($this->iSurveyID);
        }
        if (($sAction == "updatesurveylocalesettings") && (Permission::model()->hasSurveyPermission($this->iSurveyID, 'surveylocale', 'update') || Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update'))) {
            $this->actionUpdateSurveyLocaleSettings($this->iSurveyID);
        }
        if (($sAction == "updatesurveylocalesettings_generalsettings") && (Permission::model()->hasSurveyPermission($this->iSurveyID, 'surveylocale', 'update') || Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update'))) {
            $this->actionUpdateSurveyLocaleSettingsGeneralSettings($this->iSurveyID);
        }

        Yii::app()->setFlashMessage(gT("Unknown action or no permission."), 'error');
        $this->getController()->redirect(Yii::app()->request->urlReferrer);
    }

    /**
     * This is a convenience function to update/delete answer default values. If the given
     * $defaultvalue is empty then the entry is removed from table defaultvalues
     *
     * @param integer $qid   Question ID
     * @param integer $scale_id  Scale ID
     * @param string $specialtype  Special type (i.e. for  'Other')
     * @param string $language     Language (defaults are language specific)
     * @param mixed $defaultvalue    The default value itself
     */
    public function _updateDefaultValues($qid, $sqid, $scale_id, $specialtype, $language, $defaultvalue)
    {
        $arDefaultValue = DefaultValue::model()
            ->find(
                'specialtype = :specialtype AND qid = :qid AND sqid = :sqid AND scale_id = :scale_id',
                array(
                ':specialtype' => $specialtype,
                ':qid' => $qid,
                ':sqid' => $sqid,
                ':scale_id' => $scale_id,
                )
        );
        $dvid = !empty($arDefaultValue->dvid) ? $arDefaultValue->dvid : null;

        if ($defaultvalue == '') {
            // Remove the default value if it is empty
            if ($dvid !== null){
                DefaultValueL10n::model()->deleteAllByAttributes(array('dvid'=>$dvid, 'language' => $language ));
                $iRowCount = DefaultValueL10n::model()->countByAttributes(array('dvid' => $dvid));
                if ($iRowCount == 0){
                    DefaultValue::model()->deleteByPk($dvid);
                }
            }
        } else {
            if (is_null($dvid)) {
                $data = array('qid'=>$qid, 'sqid'=>$sqid, 'scale_id'=>$scale_id, 'specialtype'=>$specialtype);
                $oDefaultvalue = new DefaultValue();
                $oDefaultvalue->attributes = $data;
                $oDefaultvalue->specialtype = $specialtype;
                $oDefaultvalue->save();
                if (!empty($oDefaultvalue->dvid)){
                    $dataL10n = array('dvid'=>$oDefaultvalue->dvid, 'language'=>$language, 'defaultvalue'=>$defaultvalue);
                    $oDefaultvalueL10n = new DefaultValueL10n();
                    $oDefaultvalueL10n->attributes = $dataL10n;
                    $oDefaultvalueL10n->save();
                }   
            } else {
                if ($dvid !== null){
                    $arDefaultValue->with('defaultvaluel10ns');
                    $idL10n = !empty($arDefaultValue->defaultvaluel10ns) && array_key_exists($language, $arDefaultValue->defaultvaluel10ns) ? $arDefaultValue->defaultvaluel10ns[$language]->id : null;
                    if ($idL10n !== null){
                        DefaultValueL10n::model()->updateAll(array('defaultvalue'=>$defaultvalue), 'dvid = ' . $dvid . ' AND language = \'' . $language . '\'');
                    } else {
                        $dataL10n = array('dvid'=>$dvid, 'language'=>$language, 'defaultvalue'=>$defaultvalue);
                        $oDefaultvalueL10n = new DefaultValueL10n();
                        $oDefaultvalueL10n->attributes = $dataL10n;
                        $oDefaultvalueL10n->save();
                    }
                }
            }
        }
        $surveyid = $this->iSurveyID;
        updateFieldArray();
    }

    /**
     * action to do when update default value
     * @param integer $iSurveyID
     * @return void (redirect)
     */
    private function actionUpdateDefaultValues($iSurveyID)
    {
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $aSurveyLanguages = $oSurvey->allLanguages;
        $sBaseLanguage = $oSurvey->language;

        Question::model()->updateAll(array('same_default'=> Yii::app()->request->getPost('samedefault') ? 1 : 0), 'sid=:sid ANd qid=:qid', array(':sid'=>$iSurveyID, ':qid'=>$this->iQuestionID));

        $arQuestion = Question::model()->findByAttributes(array('qid'=>$this->iQuestionID));
        $sQuestionType = $arQuestion['type'];

        $aQuestionTypeList = Question::typeList();
        if ($aQuestionTypeList[$sQuestionType]['answerscales'] > 0 && $aQuestionTypeList[$sQuestionType]['subquestions'] == 0) {
            for ($iScaleID = 0; $iScaleID < $aQuestionTypeList[$sQuestionType]['answerscales']; $iScaleID++) {
                foreach ($aSurveyLanguages as $sLanguage) {
                    if (!is_null(Yii::app()->request->getPost('defaultanswerscale_'.$iScaleID.'_'.$sLanguage))) {
                        $this->_updateDefaultValues($this->iQuestionID, 0, $iScaleID, '', $sLanguage, Yii::app()->request->getPost('defaultanswerscale_'.$iScaleID.'_'.$sLanguage));
                    }
                    if (!is_null(Yii::app()->request->getPost('other_'.$iScaleID.'_'.$sLanguage))) {
                        $this->_updateDefaultValues($this->iQuestionID, 0, $iScaleID, 'other', $sLanguage, Yii::app()->request->getPost('other_'.$iScaleID.'_'.$sLanguage));
                    }
                }
            }
        }
        if ($aQuestionTypeList[$sQuestionType]['subquestions'] > 0) {
            foreach ($aSurveyLanguages as $sLanguage) {
                $arQuestions = Question::model()->with('questionl10ns', array('condition' => 'language = ' . $sLanguage))->findAllByAttributes(array('sid'=>$iSurveyID, 'gid'=>$this->iQuestionGroupID, 'parent_qid'=>$this->iQuestionID, 'scale_id'=>0));

                for ($iScaleID = 0; $iScaleID < $aQuestionTypeList[$sQuestionType]['subquestions']; $iScaleID++) {
                    foreach ($arQuestions as $aSubquestionrow) {
                        if (!is_null(Yii::app()->request->getPost('defaultanswerscale_'.$iScaleID.'_'.$sLanguage.'_'.$aSubquestionrow['qid']))) {
                            $this->_updateDefaultValues($this->iQuestionID, $aSubquestionrow['qid'], $iScaleID, '', $sLanguage, Yii::app()->request->getPost('defaultanswerscale_'.$iScaleID.'_'.$sLanguage.'_'.$aSubquestionrow['qid']));
                        }
                    }
                }
            }
        }
        if ($aQuestionTypeList[$sQuestionType]['answerscales'] == 0 && $aQuestionTypeList[$sQuestionType]['subquestions'] == 0) {
            foreach ($aSurveyLanguages as $sLanguage) {
                // Qick and dirty insert for yes/no defaul value
                // write the the selectbox option, or if "EM" is slected, this value to table
                if ($sQuestionType == 'Y') {
                    /// value for all langs
                    if (Yii::app()->request->getPost('samedefault') == 1) {
                        $sLanguage = $aSurveyLanguages[0]; // turn
                    }

                    if (Yii::app()->request->getPost('defaultanswerscale_0_'.$sLanguage) == 'EM') {
// Case EM, write expression to database
                        $this->_updateDefaultValues($this->iQuestionID, 0, 0, '', $sLanguage, Yii::app()->request->getPost('defaultanswerscale_0_'.$sLanguage.'_EM'));
                    } else {
                        // Case "other", write list value to database
                        $this->_updateDefaultValues($this->iQuestionID, 0, 0, '', $sLanguage, Yii::app()->request->getPost('defaultanswerscale_0_'.$sLanguage));
                    }
                    ///// end yes/no
                } else {
                    if (!is_null(Yii::app()->request->getPost('defaultanswerscale_0_'.$sLanguage.'_0'))) {
                        $this->_updateDefaultValues($this->iQuestionID, 0, 0, '', $sLanguage, Yii::app()->request->getPost('defaultanswerscale_0_'.$sLanguage.'_0'));
                    }
                }
            }
        }
        Yii::app()->session['flashmessage'] = gT("Default value settings were successfully saved.");
        //This is SUPER important! Recalculating the ExpressionScript Engine state!
        LimeExpressionManager::SetDirtyFlag();

        if (Yii::app()->request->getPost('close-after-save') === 'true') {
            $this->getController()->redirect(array('admin/questions/sa/view/surveyid/'.$iSurveyID.'/gid/'.$this->iQuestionGroupID.'/qid/'.$this->iQuestionID));
        }
        $this->getController()->redirect(array('admin/questions/sa/editdefaultvalues/surveyid/'.$iSurveyID.'/gid/'.$this->iQuestionGroupID.'/qid/'.$this->iQuestionID));
    }

    /**
     * action to do when update answers options
     * @param integer $iSurveyID
     * @return void (redirect)
     */
    private function actionUpdateAnswerOptions($iSurveyID)
    {
        Yii::app()->loadHelper('database');
        $survey = Survey::model()->findByPk($iSurveyID);
        $arQuestion = Question::model()->findByAttributes(array('qid'=>$this->iQuestionID));
        $sQuestionType = $arQuestion['type']; // Checked)
        $aQuestionTypeList = Question::typeList();
        $iScaleCount = $aQuestionTypeList[$sQuestionType]['answerscales'];
        /* for already activated survey and rank question type : fix the maxDbAnswer before deleting answers */
        /* @todo : add it to upgrage DB system, and see for the lsa */
        if ($sQuestionType == Question::QT_R_RANKING_STYLE && $survey->isActive) {
            QuestionAttribute::model()->find(
                "qid = :qid AND attribute = 'max_subquestions'",
                array(':qid' => $this->iQuestionID)
            );

            $answerCount = Answer::model()->countByAttributes(array('qid' => $this->iQuestionID, 'language'=>$survey->language));
            $oQuestionAttribute = new QuestionAttribute();
            $oQuestionAttribute->qid = $this->iQuestionID;
            $oQuestionAttribute->attribute = 'max_subquestions';
            $oQuestionAttribute->value = $answerCount;
            $oQuestionAttribute->save();
        }

        //First delete all answers
        $oldAnswers = Answer::model()->findAllByAttributes(array('qid'=>$this->iQuestionID));
        foreach ($oldAnswers as $oAnswer) {
            AnswerL10n::model()->deleteAllByAttributes(array('aid'=>$oAnswer->aid));
            $oAnswer->delete();
        }
        LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyID);
        for ($iScaleID = 0; $iScaleID < $iScaleCount; $iScaleID++) {
            $iMaxCount = (int) Yii::app()->request->getPost('answercount_'.$iScaleID);
            for ($iSortOrderID = 1; $iSortOrderID < $iMaxCount; $iSortOrderID++) {
                $sCode = (string) sanitize_paranoid_string(Yii::app()->request->getPost('code_'.$iSortOrderID.'_'.$iScaleID));
                $iAssessmentValue = (int) Yii::app()->request->getPost('assessment_'.$iSortOrderID.'_'.$iScaleID);

                    // Now we insert the answer
                $oAnswer = new Answer;
                $oAnswer->code              = $sCode;
                $oAnswer->qid               = $this->iQuestionID;
                $oAnswer->sortorder         = $iSortOrderID;
                $oAnswer->assessment_value  = $iAssessmentValue;
                $oAnswer->scale_id          = $iScaleID;
                $bAnswerSave = true;
                if (!$oAnswer->save()) {
                    $sErrors = '<br/>';
                    foreach ($oAnswer->getErrors() as $sError) {
                        $sErrors .= $sError[0].'<br/>';
                    }

                    // Let's give a new to code to the answer to save it, so user entries are not lost
                    $bAnswerSave = false;

                    while (!$bAnswerSave) {
                        $oAnswer->code = strval(rand(11111, 99999)); // If the random code already exist (very low probablilty), answer will not be save and a new code will be generated
                        if ($oAnswer->save()) {
                            $bAnswerSave = true;
                        }
                    }

                    Yii::app()->setFlashMessage(gT("Failed to update answer: ").$sCode.$sErrors, 'error');
                }
                if($bAnswerSave) {
                    foreach ($survey->allLanguages as $sLanguage) {
                        $sAnswerText = Yii::app()->request->getPost('answer_'.$sLanguage.'_'.$iSortOrderID.'_'.$iScaleID);
                        // Fix bug with FCKEditor saving strange BR types
                        $sAnswerText = $this->oFixCKeditor->fixCKeditor($sAnswerText);

                        // Now we insert the answers by language
                        $oAnswerL10n = new AnswerL10n;
                        $oAnswerL10n->aid = $oAnswer->aid;
                        $oAnswerL10n->answer            = $sAnswerText;
                        $oAnswerL10n->language          = $sLanguage;
                        $oAnswerL10n->save();
                    }
                    // Updating code (oldcode!==null) => update condition with the new code
                    $sOldCode = Yii::app()->request->getPost('oldcode_'.$iSortOrderID.'_'.$iScaleID);
                    if (isset($sOldCode) && $sCode !== $sOldCode) {
                        Condition::model()->updateAll(array('value'=>$sCode), 'cqid=:cqid AND value=:value', array(':cqid'=>$this->iQuestionID, ':value'=>$sOldCode));
                    }
                }

            }  // for ($sortorderid=0;$sortorderid<$maxcount;$sortorderid++)
        }

        //This is SUPER important! Recalculating the ExpressionScript Engine state!
        LimeExpressionManager::SetDirtyFlag();
        LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyID);
        $this->_resetEM();

        if (!Yii::app()->request->getPost('bFullPOST')) {
            Yii::app()->setFlashMessage(gT("Not all answer options were saved. This usually happens due to server limitations ( PHP setting max_input_vars) - please contact your system administrator."), 'error');
        } else {
            Yii::app()->setFlashMessage(gT("Answer options were successfully saved."));
        }
        if (Yii::app()->request->getPost('close-after-save') === 'true') {
            $this->getController()->redirect(array('admin/questions/sa/view/surveyid/'.$iSurveyID.'/gid/'.$this->iQuestionGroupID.'/qid/'.$this->iQuestionID));
        }
        $this->getController()->redirect(array('/admin/questions/sa/answeroptions/surveyid/'.$iSurveyID.'/gid/'.$this->iQuestionGroupID.'/qid/'.$this->iQuestionID));
    }

    /**
     * action to do when update subquestions
     * @param integer $iSurveyID
     * @return void
     * @throws CHttpException
     */
    private function actionSubQuestions($iSurveyID)
    {
        Yii::app()->loadHelper('database');
        $oSurvey = Survey::model()->findByPk($iSurveyID);

        $aSurveyLanguages = $oSurvey->allLanguages;
        $sBaseLanguage = $oSurvey->language;

        $arQuestion = Question::model()->findByAttributes(array('qid'=>$this->iQuestionID));
        $sQuestionType = $arQuestion['type']; // Checked
        $aQuestionTypeList = Question::typeList();
        $iScaleCount = $aQuestionTypeList[$sQuestionType]['subquestions'];
        // First delete any deleted ids
        $aDeletedQIDs = explode(' ', trim(Yii::app()->request->getPost('deletedqids')));

        LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyID);
        $aDeletedQIDs = array_unique($aDeletedQIDs, SORT_NUMERIC);
        foreach ($aDeletedQIDs as $iDeletedQID) {
            $iDeletedQID = (int) $iDeletedQID;
            if ($iDeletedQID > 0) {
                // don't remove undefined
                $iInsertCount = Question::model()->deleteAllByAttributes(array('qid'=>$iDeletedQID));
                $iInsertCount += QuestionL10n::model()->deleteAllByAttributes(array('qid'=>$iDeletedQID));
                if (!$iInsertCount) {
                    Yii::app()->setFlashMessage(gT("Failed to delete subquestion"), 'error');
                }
            }
        }

        //Determine ids by evaluating the hidden field
        $aRows = array();
        $aCodes = array();
        $aOldCodes = array();
        $aRelevance = array();
        foreach ($_POST as $sPOSTKey=>$sPOSTValue) {
            $sPOSTKey = explode('_', $sPOSTKey);
            if ($sPOSTKey[0] == 'answer') {
                $aRows[$sPOSTKey[3]][$sPOSTKey[1]][$sPOSTKey[2]] = $sPOSTValue;
            }
            if ($sPOSTKey[0] == 'code') {
                $aCodes[$sPOSTKey[2]][] = $sPOSTValue;
            }
            if ($sPOSTKey[0] == 'oldcode') {
                $aOldCodes[$sPOSTKey[2]][] = $sPOSTValue;
            }
            if ($sPOSTKey[0] == 'relevance') {
                $aRelevance[$sPOSTKey[2]][] = $sPOSTValue;
            }
        }
        $aInsertQID = array();

        // Give existing subquestions a temporary random title (code) to avoid title duplication on update
        $aoSubquestions = Question::model()->findAllByAttributes(['parent_qid'=>$this->iQuestionID]);
        foreach ($aoSubquestions as $oSubQuestion) {
            $bAnswerSave = false;
            while (!$bAnswerSave) {
                $oSubQuestion->title = strval(rand(11111, 99999)); // If the random code already exist (very low probablilty), answer will not be save and a new code will be generated
                if ($oSubQuestion->save()) {
                    $bAnswerSave = true;
                }
            }
        }

        for ($iScaleID = 0; $iScaleID < $iScaleCount; $iScaleID++) {
            foreach ($aSurveyLanguages as $sLanguage) {
                $iPosition = 0;
                foreach ($aRows[$iScaleID][$sLanguage] as $iSubQuestionID=>$subquestionvalue) {
                    if (substr($iSubQuestionID, 0, 3) != 'new') {
                        //update record
                        $oSubQuestion = Question::model()->findByPk($iSubQuestionID);
                        $oSubQuestionL10n = QuestionL10n::model()->findByAttributes(array('qid'=>$iSubQuestionID, 'language'=>$sLanguage));
                        if (!is_object($oSubQuestion)) {
                            throw new CHttpException(502, "could not find subquestion $iSubQuestionID !");
                        }
                        $oSubQuestion->question_order = $iPosition + 1;
                        $oSubQuestion->title = $aCodes[$iScaleID][$iPosition];
                        $oSubQuestionL10n->question = $subquestionvalue;
                        $oSubQuestion->scale_id = $iScaleID;
                        $oSubQuestion->relevance = isset($aRelevance[$iScaleID][$iPosition]) ? $aRelevance[$iScaleID][$iPosition] : "";
                    } else {
                        // new record
                        if (!isset($aInsertQID[$iScaleID][$iPosition])) {
                            //new record: first (default) language
                            $oSubQuestion = new Question;
                            $oSubQuestionL10n = new QuestionL10n;
                            $oSubQuestion->sid = $iSurveyID;
                            $oSubQuestion->gid = $this->iQuestionGroupID;
                            $oSubQuestion->question_order = $iPosition + 1;
                            $oSubQuestion->title = $aCodes[$iScaleID][$iPosition];
                            $oSubQuestionL10n->question = $subquestionvalue;
                            $oSubQuestion->parent_qid = $this->iQuestionID;
                            $oSubQuestionL10n->language = $sLanguage;
                            $oSubQuestion->scale_id = $iScaleID;
                            $oSubQuestion->relevance = isset($aRelevance[$iScaleID][$iPosition]) ? $aRelevance[$iScaleID][$iPosition] : "";
                        } else {
                            //new record: additional language
                            $oSubQuestionL10n = QuestionL10n::model()->find("qid=:qid AND language=:language", array(":qid"=>$aInsertQID[$iScaleID][$iPosition], ':language'=>$sLanguage));
                            if (!$oSubQuestionL10n) {
                                $oSubQuestionL10n = new QuestionL10n;
                            }
                            $oSubQuestionL10n->qid = $aInsertQID[$iScaleID][$iPosition];
                            $oSubQuestionL10n->question = $subquestionvalue;
                            $oSubQuestionL10n->language = $sLanguage;
                        }
                    }
                    if (isset($oSubQuestion)) {
                        $bSubQuestionResult = $oSubQuestion->save();
                        $aInsertQID[$iScaleID][$iPosition] = $oSubQuestion->qid;
                        unset($oSubQuestion);
                    } else {
                        $bSubQuestionResult = true;
                    }
                    if (isset($oSubQuestionL10n)) {
                        if (empty($oSubQuestionL10n->qid)) {
                            $oSubQuestionL10n->qid = $aInsertQID[$iScaleID][$iPosition];
                        }
                        $bSubQuestionResult = $oSubQuestionL10n->save();
                        unset($oSubQuestionL10n);
                    }

                    if ($bSubQuestionResult) {
                        if ($bSubQuestionResult !== true && substr($iSubQuestionID, 0, 3) != 'new' && isset($aOldCodes[$iScaleID][$iPosition]) && $aCodes[$iScaleID][$iPosition] !== $aOldCodes[$iScaleID][$iPosition]) {
                            Condition::model()->updateAll(array('cfieldname'=>'+'.$iSurveyID.'X'.$this->iQuestionGroupID.'X'.$this->iQuestionID.$aCodes[$iScaleID][$iPosition], 'value'=>$aCodes[$iScaleID][$iPosition]), 'cqid=:cqid AND cfieldname=:cfieldname AND value=:value', array(':cqid'=>$this->iQuestionID, ':cfieldname'=>$iSurveyID.'X'.$this->iQuestionGroupID.'X'.$this->iQuestionID, ':value'=>$aOldCodes[$iScaleID][$iPosition]));
                        }
                    } else {
                        $aErrors = $oSubQuestion->getErrors();
                        if (count($aErrors)) {
                            foreach ($aErrors as $sAttribute=>$aStringErrors) {
                                foreach ($aStringErrors as $sStringErrors) {
                                    Yii::app()->setFlashMessage(sprintf(gT("Error on %s for subquestion %s: %s"), $sAttribute, $aCodes[$iScaleID][$iPosition], $sStringErrors), 'error');
                                }
                            }

                            // Let's give a new to code to the answer to save it, so user entries are not lost
                            $bAnswerSave = false;

                            while (!$bAnswerSave) {
                                $oSubQuestion->title = strval(rand(11111, 99999)); // If the random code already exist (very low probablilty), answer will not be save and a new code will be generated
                                if ($oSubQuestion->save()) {
                                    $sError = '<strong>'.sprintf(gT('A code has been updated to %s.'), $oSubQuestion->title).'</strong><br/>';
                                    Yii::app()->setFlashMessage($sError, 'error');
                                    $bAnswerSave = true;
                                }
                            }
                        } else {
                            Yii::app()->setFlashMessage(sprintf(gT("Subquestions %s could not be updated."), $aCodes[$iScaleID][$iPosition]), 'error');
                        }
                    }
                    $iPosition++;
                }
            }
        }

        //This is SUPER important! Recalculating the ExpressionScript Engine state!
        LimeExpressionManager::SetDirtyFlag();
        LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyID);
        $this->_resetEM();

        if (!isset($aErrors) || !count($aErrors)) {
            if (!Yii::app()->request->getPost('bFullPOST')) {
                Yii::app()->session['flashmessage'] = gT("Not all subquestions were saved. This usually happens due to server limitations ( PHP setting max_input_vars) - please contact your system administrator.");
            } else {
                Yii::app()->session['flashmessage'] = gT("Subquestions were successfully saved.");
            }
        }

        if (Yii::app()->request->getPost('close-after-save') === 'true') {
            $this->getController()->redirect(array('/admin/questions/sa/view/surveyid/'.$iSurveyID.'/gid/'.$this->iQuestionGroupID.'/qid/'.$this->iQuestionID));
        }

        $this->getController()->redirect(array('/admin/questions/sa/subquestions/surveyid/'.$iSurveyID.'/gid/'.$this->iQuestionGroupID.'/qid/'.$this->iQuestionID));
    }
    /**
     * action to do when update question
     * @param integer $iSurveyID
     * @return void (redirect)
     * @todo Deprecated, replaced by questionedit.php
     */
    private function actionUpdateQuestion($iSurveyID)
    {
        $oSurvey = Survey::model()->findByPk($iSurveyID);

        LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyID);

        $cqr = Question::model()->findByAttributes(array('qid'=>$this->iQuestionID));
        $oldtype = $cqr['type'];
        $oldgid = $cqr['gid'];

        $survey = Survey::model()->findByPk($iSurveyID);
        // If the survey is activate the question type may not be changed
        if ($survey->isActive) {
            $sQuestionType = $oldtype;
        } else {
            $sQuestionType = Yii::app()->request->getPost('type');
        }


        /* Check if we need to save QuestionAttribute testing advancedquestionsettings , see mantis #14563 */
        if(Yii::app()->request->getPost('advancedquestionsettingsLoaded',true)) {
            // Remove invalid question attributes on saving
            $criteria = new CDbCriteria;
            $criteria->compare('qid', $this->iQuestionID);
            $validAttributes = QuestionAttribute::getQuestionAttributesSettings($sQuestionType);
            // If the question has a custom template, we first check if it provides custom attributes
            $oAttributeValues = QuestionAttribute::model()->find("qid=:qid and attribute='question_template'", array('qid'=>$cqr->qid));
            if (is_object($oAttributeValues) && $oAttributeValues->value) {
                $aAttributeValues['question_template'] = $oAttributeValues->value;
            } else {
                $aAttributeValues['question_template'] = 'core';
            }
            $validAttributes = Question::getQuestionTemplateAttributes($validAttributes, $aAttributeValues, $cqr);
            foreach ($validAttributes as  $validAttribute) {
                $criteria->compare('attribute', '<>'.$validAttribute['name']);
            }
            QuestionAttribute::model()->deleteAll($criteria);
            $aLanguages = array_merge(array(Survey::model()->findByPk($iSurveyID)->language), Survey::model()->findByPk($iSurveyID)->additionalLanguages);
            foreach ($validAttributes as $validAttribute) {
                /* Readonly attribute : disable save */
                if( $validAttribute['readonly'] || ( $validAttribute['readonly_when_active'] && Survey::model()->findByPk($iSurveyID)->getIsActive() ) ) {
                    continue;
                }
                if ($validAttribute['i18n']) {
                    /* Delete invalid language : not needed but cleaner */
                    $langCriteria = new CDbCriteria;
                    $langCriteria->compare('qid', $this->iQuestionID);
                    $langCriteria->compare('attribute', $validAttribute['name']);
                    $langCriteria->addNotInCondition('language', $aLanguages);
                    QuestionAttribute::model()->deleteAll($langCriteria);
                    /* delete IS NULL too*/
                    QuestionAttribute::model()->deleteAll('attribute=:attribute AND qid=:qid AND language IS NULL', array(':attribute'=>$validAttribute['name'], ':qid'=>$this->iQuestionID));
                    foreach ($aLanguages as $sLanguage) {
                        // TODO sanitise XSS
                        $value = Yii::app()->request->getPost($validAttribute['name'].'_'.$sLanguage);
                        $iInsertCount = QuestionAttribute::model()->countByAttributes(array('attribute'=>$validAttribute['name'], 'qid'=>$this->iQuestionID, 'language'=>$sLanguage));
                        if ($iInsertCount > 0) {
                            if ($value != '') {
                                QuestionAttribute::model()->updateAll(array('value'=>$value), 'attribute=:attribute AND qid=:qid AND language=:language', array(':attribute'=>$validAttribute['name'], ':qid'=>$this->iQuestionID, ':language'=>$sLanguage));
                            } else {
                                QuestionAttribute::model()->deleteAll('attribute=:attribute AND qid=:qid AND language=:language', array(':attribute'=>$validAttribute['name'], ':qid'=>$this->iQuestionID, ':language'=>$sLanguage));
                            }
                        } elseif ($value != '') {
                            $attribute = new QuestionAttribute;
                            $attribute->qid = $this->iQuestionID;
                            $attribute->value = $value;
                            $attribute->attribute = $validAttribute['name'];
                            $attribute->language = $sLanguage;
                            $attribute->save();
                        }
                    }
                } else {
                    $default = isset($validAttribute['default']) ? $validAttribute['default'] : '';
                    $value = Yii::app()->request->getPost($validAttribute['name'], $default);
                    if ($validAttribute['name'] == "slider_layout") {
                        tracevar("delete $value");
                    }
                    /* we must have only one element, and this element must be null, then reset always (see #11980)*/
                    /* We can update, but : this happen only for admin and not a lot, then : delete + add */
                    QuestionAttribute::model()->deleteAll('attribute=:attribute AND qid=:qid', array(':attribute'=>$validAttribute['name'], ':qid'=>$this->iQuestionID));
                    if ($value != $default) {
                        if ($validAttribute['name'] == "slider_layout") {
                            tracevar("save $value");
                        }
                        $attribute = new QuestionAttribute;
                        $attribute->qid = $this->iQuestionID;
                        $attribute->value = $value;
                        $attribute->attribute = $validAttribute['name'];
                        $attribute->save();
                    }
                }
            }
        }

        $aQuestionTypeList = Question::typeList();
        // These are the questions types that have no answers and therefore we delete the answer in that case
        $iAnswerScales = $aQuestionTypeList[$sQuestionType]['answerscales'];
        $iSubquestionScales = $aQuestionTypeList[$sQuestionType]['subquestions'];

        /* Set the new question attribute with post value to fix it after */
        $fixedQuestionAttributes = array(
            'preg' => Yii::app()->request->getPost('preg',''),
            'other' => Yii::app()->request->getPost('other'),
            'mandatory' => Yii::app()->request->getPost('mandatory'),
            'relevance' => Yii::app()->request->getPost('relevance',''),
        );

        if ($sQuestionType == "!" || $sQuestionType == "L" || $sQuestionType == "M" || $sQuestionType == "P" ||
        $sQuestionType == "F" || $sQuestionType == "H" ||
        $sQuestionType == "X" || $sQuestionType == "") {
            $fixedQuestionAttributes['preg'] = '';
        }

        // For Bootstrap Version using BAD YiiWheels switch only if needed
        // Alt solution : filter_var($fixedQuestionAttributes['mandatory'], FILTER_VALIDATE_BOOLEAN); then on is true and off is false
        if(!in_array($fixedQuestionAttributes['other'],array('Y','N'))) {
            $fixedQuestionAttributes['other'] = boolval($fixedQuestionAttributes['other']) ? 'Y' : 'N';
        }

        // These are the questions types that have no mandatory property - so zap it accordingly
        if ($sQuestionType == "X" || $sQuestionType == "|") {
            $fixedQuestionAttributes['mandatory'] = 'N';
        }

        if ($oldtype != $sQuestionType) {
            // TMSW Condition->Relevance:  Do similar check via EM, but do allow such a change since will be easier to modify relevance
            //Make sure there are no conditions based on this question, since we are changing the type
            $ccresult = Condition::model()->findAllByAttributes(array('cqid'=>$this->iQuestionID));
            $cccount = count($ccresult);
            foreach ($ccresult as $ccr) {
                $qidarray[] = $ccr['qid'];
            }
        }
        if (isset($cccount) && $cccount) {
            Yii::app()->setFlashMessage(gT("Question could not be updated. There are conditions for other questions that rely on the answers to this question and changing the type will cause problems. You must delete these conditions  before you can change the type of this question."), 'error');
        } else {
            if (!empty($this->iQuestionGroupID)) {
                $aSurveyLanguages = Survey::model()->findByPk($iSurveyID)->getAllLanguages();
                $oQuestion = Question::model()->findByPk($this->iQuestionID);
                $oQuestion->type = $sQuestionType; 
                $oQuestion->title = Yii::app()->request->getPost('title');
                $oQuestion->preg = $fixedQuestionAttributes['preg'];
                $oQuestion->gid = $this->iQuestionGroupID;
                $oQuestion->other = Yii::app()->request->getPost('other');
                $oQuestion->mandatory = Yii::app()->request->getPost('mandatory');
                $oQuestion->encrypted = Yii::app()->request->getPost('encrypted');
                $oQuestion->relevance = Yii::app()->request->getPost('relevance');  
                // Update question module
                if (Yii::app()->request->getPost('module_name') != '') {
                    // The question module is not empty. So it's an external question module.
                    $oQuestion->modulename = Yii::app()->request->getPost('module_name');
                } else {
                    // If it was a module before, we must
                    $oQuestion->modulename = '';
                }
                if ($oldgid != $this->iQuestionGroupID) {
                    if (getGroupOrder($oldgid) > getGroupOrder($this->iQuestionGroupID)) {
                        // TMSW Condition->Relevance:  What is needed here?

                        // Moving question to a 'upper' group
                        // insert question at the end of the destination group
                        // this prevent breaking conditions if the target qid is in the dest group
                        $insertorder = getMaxQuestionOrder($this->iQuestionGroupID) + 1;
                        $oQuestion->question_order = $insertorder;
                    } else {
                        // Moving question to a 'lower' group
                        // insert question at the beginning of the destination group
                        Question::model()->updateQuestionOrder($this->iQuestionGroupID, $iSurveyID); // makes 1 spare room for new question at top of dest group
                        $oQuestion->question_order = 0;
                    }
                }
                
                $uqresult = $oQuestion->save();              
                foreach ($aSurveyLanguages as $qlang) {
                    if (isset($qlang) && $qlang != "") {
                        $sQuestionText = Yii::app()->request->getPost('question_'.$qlang, '');
                        $sQuestionHelp = Yii::app()->request->getPost('help_'.$qlang, '');
                        $sQuestionText = $this->oFixCKeditor->fixCKeditor($sQuestionText);
                        $sQuestionHelp = $this->oFixCKeditor->fixCKeditor($sQuestionHelp);
                        $udata = array(
                            'question' => $sQuestionText,
                            'help' => $sQuestionHelp,
                            'language' => $qlang
                        );

                        $oQuestionL10n = QuestionL10n::model()->findByAttributes(array("qid"=>$this->iQuestionID, 'language'=>$qlang));

                        foreach ($udata as $k => $v) {
                            $oQuestionL10n->$k = $v;
                        }

                        $uqresult = $oQuestionL10n->save(); //($uqquery); // or safeDie ("Error Update Question: ".$uqquery."<br />");  // Checked)
                        if (!$uqresult) {
                            $bOnError = true;
                            $aErrors = $oQuestion->getErrors();
                            if (count($aErrors)) {
                                foreach ($aErrors as $sAttribute=>$aStringErrors) {
                                    foreach ($aStringErrors as $sStringErrors) {
                                        Yii::app()->setFlashMessage(sprintf(gT("Question could not be updated with error on %s: %s"), $sAttribute, $sStringErrors), 'error');
                                    }
                                }
                            } else {
                                Yii::app()->setFlashMessage(gT("Question could not be updated."), 'error');
                            }
                        }
                    }
                }


                // Update the group ID on subquestions, too
                if ($oldgid != $this->iQuestionGroupID) {
                    Question::model()->updateAll(array('gid'=>$this->iQuestionGroupID), 'parent_qid=:qid and parent_qid>0', array(':qid'=>$this->iQuestionID));
                    // if the group has changed then fix the sortorder of old and new group
                    Question::model()->updateQuestionOrder($oldgid, $iSurveyID);
                    Question::model()->updateQuestionOrder($this->iQuestionGroupID, $iSurveyID);
                    // If some questions have conditions set on this question's answers
                    // then change the cfieldname accordingly
                    Condition::model()->updateCFieldName($iSurveyID, $this->iQuestionID, $oldgid, $this->iQuestionGroupID);
                }
                // Update subquestions
                if ($oldtype != $sQuestionType) {
                    Question::model()->updateAll(array('type'=>$sQuestionType), 'parent_qid=:qid', array(':qid'=>$this->iQuestionID));
                }

                // Update subquestions if question module
                if (Yii::app()->request->getPost('module_name') != '') {
                    // The question module is not empty. So it's an external question module.
                    Question::model()->updateAll(array('modulename'=>Yii::app()->request->getPost('module_name')), 'parent_qid=:qid', array(':qid'=>$this->iQuestionID));
                } else {
                    // If it was a module before, we must
                    Question::model()->updateAll(array('modulename'=>''), 'parent_qid=:qid', array(':qid'=>$this->iQuestionID));
                }

                Answer::model()->deleteAllByAttributes(array('qid' => $this->iQuestionID), 'scale_id >= :scale_id', array(':scale_id' => $iAnswerScales));

                // Remove old subquestion scales
                Question::model()->deleteAllByAttributes(array('parent_qid' => $this->iQuestionID), 'scale_id >= :scale_id', array(':scale_id' => $iSubquestionScales));
                if (!isset($bOnError) || !$bOnError) {
                    // This really a quick hack and need a better system
                    Yii::app()->setFlashMessage(gT("Question was successfully saved."));
                }
            } else {
                Yii::app()->setFlashMessage(gT("Question could not be updated"), 'error');
            }
        }
        //This is SUPER important! Recalculating the ExpressionScript Engine state!
        LimeExpressionManager::SetDirtyFlag();
        LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyID);
        $this->_resetEM();

        $closeAfterSave = Yii::app()->request->getPost('close-after-save') === 'true';

        if ($closeAfterSave) {
            // Redirect to summary
            $this->getController()->redirect(array('admin/questions/sa/view/surveyid/'.$iSurveyID.'/gid/'.$this->iQuestionGroupID.'/qid/'.$this->iQuestionID));
        } else {
            // Redirect to edit
            $this->getController()->redirect(array('questionEditor/view/surveyid/'.$iSurveyID.'/gid/'.$this->iQuestionGroupID.'/qid/'.$this->iQuestionID));
            // This works too: $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }
    }

    /**
     * action to do when update survey seetings + survey language
     * @param integer $iSurveyID
     * @return void (redirect)
     */
    private function actionUpdateSurveyLocaleSettings($iSurveyID)
    {
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $languagelist = $oSurvey->additionalLanguages;
        $languagelist[] = $oSurvey->language;

        Yii::app()->loadHelper('database');

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveylocale', 'update')) {
            foreach ($languagelist as $langname) {
                if ($langname) {
                    $data = array();
                    $sURLDescription = Yii::app()->request->getPost('urldescrip_'.$langname, null);
                    $sURL = Yii::app()->request->getPost('url_'.$langname, null);
                    $short_title = Yii::app()->request->getPost('short_title_'.$langname, null);
                    $description = Yii::app()->request->getPost('description_'.$langname, null);
                    $welcome = Yii::app()->request->getPost('welcome_'.$langname, null);
                    $endtext = Yii::app()->request->getPost('endtext_'.$langname, null);
                    $datasec = Yii::app()->request->getPost('datasec_'.$langname, null);
                    $datasecerror = Yii::app()->request->getPost('datasecerror_'.$langname, null);
                    $dataseclabel = Yii::app()->request->getPost('dataseclabel_'.$langname, null);
                    $dateformat = Yii::app()->request->getPost('dateformat_'.$langname, null);
                    $numberformat = Yii::app()->request->getPost('numberformat_'.$langname, null);

                    if ($short_title !== null) {
                        // Fix bug with FCKEditor saving strange BR types
                        $short_title = $this->oFixCKeditor->fixCKeditor($short_title);
                        $data['surveyls_title'] = $short_title;
                    }
                    if ($description !== null) {
                        // Fix bug with FCKEditor saving strange BR types
                        $description = $this->oFixCKeditor->fixCKeditor($description);
                        $data['surveyls_description'] = $description;
                    }
                    if ($welcome !== null) {
                        // Fix bug with FCKEditor saving strange BR types
                        $welcome = $this->oFixCKeditor->fixCKeditor($welcome);
                        $data['surveyls_welcometext'] = $welcome;
                    }
                    if ($endtext !== null) {
                        // Fix bug with FCKEditor saving strange BR types
                        $endtext = $this->oFixCKeditor->fixCKeditor($endtext);
                        $data['surveyls_endtext'] = $endtext;
                    }
                    if ($datasec !== null) {
                        // Fix bug with FCKEditor saving strange BR types
                        $datasec = $this->oFixCKeditor->fixCKeditor($datasec);
                        $data['surveyls_policy_notice'] = $datasec;
                    }
                    if ($datasecerror !== null) {
                        // Fix bug with FCKEditor saving strange BR types
                        $datasecerror = $this->oFixCKeditor->fixCKeditor($datasecerror);
                        $data['surveyls_policy_error'] = $datasecerror;
                    }
                    if ($dataseclabel !== null) {
                        $data['surveyls_policy_notice_label'] = $dataseclabel;
                    }
                    if ($sURL !== null) {
                        $data['surveyls_url'] = htmlspecialchars($sURL);
                    }
                    if ($sURLDescription !== null) {
                        $data['surveyls_urldescription'] = htmlspecialchars($sURLDescription);
                    }
                    if ($dateformat !== null) {
                        $data['surveyls_dateformat'] = $dateformat;
                    }
                    if ($numberformat !== null) {
                        $data['surveyls_numberformat'] = $numberformat;
                    }

                    if (count($data) > 0) {
                        $oSurveyLanguageSetting = SurveyLanguageSetting::model()->findByPk(array('surveyls_survey_id'=>$iSurveyID, 'surveyls_language'=>$langname));
                        $oSurveyLanguageSetting->setAttributes($data);
                        $oSurveyLanguageSetting->save(); // save the change to database
                    }
                }
            }
        }
        ////////////////////////////////////////////////////////////////////////////////////
        // General settings (copy / paste from surveyadmin::update)
        if (Permission::model()->hasSurveyPermission($iSurveyID, 'surveysettings', 'update')) {
            // Preload survey
            $oSurvey = Survey::model()->findByPk($iSurveyID);
            $aOldAttributes = $oSurvey->attributes;
            // Save plugin settings : actually leave it before saving core : we are sure core settings is saved in LS way.
            $pluginSettings = App()->request->getPost('plugin', array());
            foreach ($pluginSettings as $plugin => $settings) {
                $settingsEvent = new PluginEvent('newSurveySettings');
                $settingsEvent->set('settings', $settings);
                $settingsEvent->set('survey', $iSurveyID);
                App()->getPluginManager()->dispatchEvent($settingsEvent, $plugin);
            }

            /* Start to fix some param before save (TODO : use models directly ?) */
            /* Date management */
            Yii::app()->loadHelper('surveytranslator');
            $formatdata = getDateFormatData(Yii::app()->session['dateformat']);
            Yii::app()->loadLibrary('Date_Time_Converter');

            $unfilteredStartdate = App()->request->getPost('startdate', null);
            $startdate = $this->_filterEmptyFields($oSurvey, 'startdate');
            if ($unfilteredStartdate === null) {
                // Not submitted.
            } elseif (trim($unfilteredStartdate) == "") {
                $oSurvey->startdate = "";
            } else {
                Yii::app()->loadLibrary('Date_Time_Converter');
                $datetimeobj = new date_time_converter($startdate, $formatdata['phpdate'].' H:i');
                $startdate = $datetimeobj->convert("Y-m-d H:i:s");
                $oSurvey->startdate = $startdate;
            }

            $unfilteredExpires = App()->request->getPost('expires', null);
            $expires = $this->_filterEmptyFields($oSurvey, 'expires');
            if ($unfilteredExpires === null) {
                // Not submitted.
            } elseif (trim($unfilteredExpires) == "") {
                // Must not convert if empty.
                $oSurvey->expires = "";
            } else {
                $datetimeobj = new date_time_converter($expires, $formatdata['phpdate'].' H:i');
                $expires = $datetimeobj->convert("Y-m-d H:i:s");
                $oSurvey->expires = $expires;
            }

            $oSurvey->assessments = $this->_filterEmptyFields($oSurvey, 'assessments');

            if ($oSurvey->active != 'Y') {
                $oSurvey->anonymized = $this->_filterEmptyFields($oSurvey, 'anonymized');
                $oSurvey->savetimings = $this->_filterEmptyFields($oSurvey, 'savetimings');
                $oSurvey->datestamp = $this->_filterEmptyFields($oSurvey, 'datestamp');
                $oSurvey->ipaddr = $this->_filterEmptyFields($oSurvey, 'ipaddr');
                //save the new setting ipanonymize
                $oSurvey->ipanonymize = $this->_filterEmptyFields($oSurvey, 'ipanonymize');
                //todo: here we have to change the ip-addresses already saved ?!?
                $oSurvey->refurl = $this->_filterEmptyFields($oSurvey, 'refurl');
            }

            $oSurvey->publicgraphs = $this->_filterEmptyFields($oSurvey, 'publicgraphs');
            $oSurvey->usecookie = $this->_filterEmptyFields($oSurvey, 'usecookie');
            $oSurvey->allowregister = $this->_filterEmptyFields($oSurvey, 'allowregister');
            $oSurvey->allowsave = $this->_filterEmptyFields($oSurvey, 'allowsave');
            $oSurvey->navigationdelay = (int) $this->_filterEmptyFields($oSurvey, 'navigationdelay');
            $oSurvey->printanswers = $this->_filterEmptyFields($oSurvey, 'printanswers');
            $oSurvey->publicstatistics = $this->_filterEmptyFields($oSurvey, 'publicstatistics');
            $oSurvey->autoredirect = $this->_filterEmptyFields($oSurvey, 'autoredirect');

            // save into the database only if global settings are off
            //if (getGlobalSetting('showxquestions') === 'choose'){
                $oSurvey->showxquestions = $this->_filterEmptyFields($oSurvey, 'showxquestions');
            //}
            //if (getGlobalSetting('showgroupinfo') === 'choose'){
                $oSurvey->showgroupinfo = $this->_filterEmptyFields($oSurvey, 'showgroupinfo');
            //}
            //if (getGlobalSetting('showqnumcode') === 'choose'){
                $oSurvey->showqnumcode = $this->_filterEmptyFields($oSurvey, 'showqnumcode');
            //}
            //if (getGlobalSetting('shownoanswer') == 2){  // Don't do exact comparison because the value could be from global settings table (string) or from config (integer)
                $oSurvey->shownoanswer = $this->_filterEmptyFields($oSurvey, 'shownoanswer');
            //}
            $oSurvey->showwelcome = $this->_filterEmptyFields($oSurvey, 'showwelcome');
            $oSurvey->showsurveypolicynotice = $this->_filterEmptyFields($oSurvey, 'showsurveypolicynotice');
            $oSurvey->allowprev = $this->_filterEmptyFields($oSurvey, 'allowprev');
            $oSurvey->questionindex = (int) $this->_filterEmptyFields($oSurvey, 'questionindex');
            $oSurvey->nokeyboard = $this->_filterEmptyFields($oSurvey, 'nokeyboard');
            $oSurvey->showprogress = $this->_filterEmptyFields($oSurvey, 'showprogress');
            $oSurvey->listpublic = $this->_filterEmptyFields($oSurvey, 'listpublic');
            $oSurvey->htmlemail = $this->_filterEmptyFields($oSurvey, 'htmlemail');
            $oSurvey->sendconfirmation = $this->_filterEmptyFields($oSurvey, 'sendconfirmation');
            $oSurvey->tokenanswerspersistence = $this->_filterEmptyFields($oSurvey, 'tokenanswerspersistence');
            $oSurvey->alloweditaftercompletion = $this->_filterEmptyFields($oSurvey, 'alloweditaftercompletion');
            $oSurvey->usecaptcha = Survey::saveTranscribeCaptchaOptions($oSurvey);
            $oSurvey->emailresponseto = $this->_filterEmptyFields($oSurvey, 'emailresponseto');
            $oSurvey->emailnotificationto = $this->_filterEmptyFields($oSurvey, 'emailnotificationto');
            $googleanalyticsapikeysetting = $this->_filterEmptyFields($oSurvey, 'googleanalyticsapikeysetting');
            $oSurvey->googleanalyticsapikeysetting = $googleanalyticsapikeysetting;

            if ($googleanalyticsapikeysetting == "Y") {
                $oSurvey->googleanalyticsapikey = $this->_filterEmptyFields($oSurvey, 'googleanalyticsapikey');
            } elseif ($googleanalyticsapikeysetting == "G") {
                $oSurvey->googleanalyticsapikey = "9999useGlobal9999";
            } elseif ($googleanalyticsapikeysetting == "N") {
                $oSurvey->googleanalyticsapikey = "";
            }

            $oSurvey->googleanalyticsstyle = $this->_filterEmptyFields($oSurvey, 'googleanalyticsstyle');

            $tokenlength = $this->_filterEmptyFields($oSurvey, 'tokenlength');
            $oSurvey->tokenlength = (int) ((($tokenlength < 5 || $tokenlength > 36) && $tokenlength != -1) ? 15 : $tokenlength);

            $event = new PluginEvent('beforeSurveySettingsSave');
            $event->set('modifiedSurvey', $oSurvey);
            App()->getPluginManager()->dispatchEvent($event);
            $aAfterApplyAttributes = $oSurvey->attributes;

            if ($oSurvey->save()) {
                Yii::app()->setFlashMessage(gT("Survey settings were successfully saved."));
            } else {
                Yii::app()->setFlashMessage(Chtml::errorSummary($oSurvey,Chtml::tag("p",array('class'=>'strong'),gT("Survey could not be updated, please fix the following error:"))), "error");
            }
        }
        $oSurvey->refresh();

        // Url params in json
        if (Yii::app()->request->getPost('allurlparams',false)!==false) {
            $aURLParams = json_decode(Yii::app()->request->getPost('allurlparams'), true);
            SurveyURLParameter::model()->deleteAllByAttributes(array('sid'=>$iSurveyID));
            foreach ($aURLParams as $aURLParam) {
                $aURLParam['parameter'] = trim($aURLParam['parameter']);
                if ($aURLParam['parameter'] == '' || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $aURLParam['parameter']) || $aURLParam['parameter'] == 'sid' || $aURLParam['parameter'] == 'newtest' || $aURLParam['parameter'] == 'token' || $aURLParam['parameter'] == 'lang') {
                    continue; // this parameter name seems to be invalid - just ignore it
                }
                $aURLParam['targetqid']  = $aURLParam['qid'];
                $aURLParam['targetsqid'] = $aURLParam['sqid'];
                unset($aURLParam['actionBtn']);
                unset($aURLParam['title']);
                unset($aURLParam['id']);
                unset($aURLParam['qid']);
                unset($aURLParam['targetQuestionText']);
                unset($aURLParam['sqid']);
                if ($aURLParam['targetqid'] == '') {
                    $aURLParam['targetqid'] = null;
                }
                if ($aURLParam['targetsqid'] == '') {
                    $aURLParam['targetsqid'] = null;
                }
                $aURLParam['sid'] = $iSurveyID;

                $param = new SurveyURLParameter;
                foreach ($aURLParam as $k => $v) {
                                    $param->$k = $v;
                }
                $param->save();
            }
        }
        //This is SUPER important! Recalculating the ExpressionScript Engine state!
        LimeExpressionManager::SetDirtyFlag();
        $this->_resetEM();

        if (Yii::app()->request->getPost('responsejson', 0) == 1) {

            $updatedFields = $this->updatedFields;
            $this->updatedFields = [];
            return Yii::app()->getController()->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => true,
                        'updated'=> $updatedFields,
                        'DEBUG' => ['POST'=>$_POST,
                                    'reloaded'=>$oSurvey->attributes,
                                    'aURLParams' => isset($aURLParams) ? $aURLParams : '',
                                    'initial'=>isset($aOldAttributes) ? $aOldAttributes : '',
                                    'afterApply'=>isset($aAfterApplyAttributes) ? $aAfterApplyAttributes : '']
                    ],
                ),
                false,
                false
            );
        } else {
            ////////////////////////////////////////
            if (Yii::app()->request->getPost('close-after-save') === 'true') {
                $this->getController()->redirect(array('admin/survey/sa/view/surveyid/'.$iSurveyID));
            }

            $referrer = Yii::app()->request->urlReferrer;
            if ($referrer) {
                $this->getController()->redirect(array($referrer));
            } else {
                $this->getController()->redirect(array('/admin/survey/sa/rendersidemenulink/subaction/generalsettings/surveyid/'.$iSurveyID));
            }
        }

    }

    /**
     * Action for the page "General settings".
     * @param int $iSurveyID
     * @return void
     */
    protected function actionUpdateSurveyLocaleSettingsGeneralSettings($iSurveyID)
    {
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $request = Yii::app()->request;
        $oSurvey->additional_languages = implode(' ', Yii::app()->request->getPost('additional_languages', array()));
        $oSurvey->admin = $request->getPost('admin');
        $oSurvey->adminemail = $request->getPost('adminemail');
        $oSurvey->bounce_email = $request->getPost('bounce_email');
        $oSurvey->faxto = $request->getPost('faxto');
        $oSurvey->gsid = $request->getPost('gsid');
        $oSurvey->format = $request->getPost('format');

        // For the new template system we have to check that the changed template is also applied.
        $current_template = $oSurvey->template;
        $new_template = $request->getPost('template');
        if ($current_template != '' && $current_template !== $new_template) {
            $currentConfiguration = TemplateConfiguration::getInstance(
                $current_template,
                $oSurvey->gsid,
                $oSurvey->sid
            );

        }
        $oSurvey->template = $new_template;

        // Only owner and superadmins may change the survey owner
        if ($oSurvey->owner_id == Yii::app()->session['loginID']
            || Permission::model()->hasGlobalPermission('superadmin', 'read')) {
            $oSurvey->owner_id = $request->getPost('owner_id');
        }

        /* Delete removed language cleanLanguagesFromSurvey do it already why redo it (cleanLanguagesFromSurvey must be moved to model) ?*/
        $aAvailableLanguage = $oSurvey->getAllLanguages();
        $oCriteria = new CDbCriteria;
        $oCriteria->compare('surveyls_survey_id', $iSurveyID);
        $oCriteria->addNotInCondition('surveyls_language', $aAvailableLanguage);
        SurveyLanguageSetting::model()->deleteAll($oCriteria);

        /* Add new language fixLanguageConsistency do it ?*/
        foreach ($oSurvey->additionalLanguages as $sLang) {
            if ($sLang) {
                $oLanguageSettings = SurveyLanguageSetting::model()->find(
                    'surveyls_survey_id=:surveyid AND surveyls_language=:langname',
                    array(':surveyid'=>$iSurveyID, ':langname'=>$sLang)
                );
                if (!$oLanguageSettings) {
                    $oLanguageSettings = new SurveyLanguageSetting;
                    $languagedetails = getLanguageDetails($sLang);
                    $oLanguageSettings->surveyls_survey_id = $iSurveyID;
                    $oLanguageSettings->surveyls_language = $sLang;
                    $oLanguageSettings->surveyls_title = ''; // Not in default model ?
                    $oLanguageSettings->surveyls_dateformat = $languagedetails['dateformat'];
                    if (!$oLanguageSettings->save()) {
                        Yii::app()->setFlashMessage(gT("Survey language could not be created."), "error");
                        tracevar($oLanguageSettings->getErrors());
                    }
                }
            }
        }
        /* Language fix : remove and add question/group */
        cleanLanguagesFromSurvey($iSurveyID, implode(" ", $oSurvey->additionalLanguages));
        fixLanguageConsistency($iSurveyID, implode(" ", $oSurvey->additionalLanguages));

        //This is SUPER important! Recalculating the ExpressionScript Engine state!
        LimeExpressionManager::SetDirtyFlag();
        $this->_resetEM();

        // This will force the generation of the entry for survey group
        TemplateConfiguration::checkAndcreateSurveyConfig($iSurveyID);

        if ($oSurvey->save()) {
            Yii::app()->setFlashMessage(gT("Survey settings were successfully saved."));
        } else {
            Yii::app()->setFlashMessage(Chtml::errorSummary($oSurvey,Chtml::tag("p",array('class'=>'strong'),gT("Survey could not be updated, please fix the following error:"))), "error");
        }
        Yii::app()->end();
    }

    /**
     * @param Survey $oSurvey
     * @param string $fieldArrayName
     * @param mixed $newValue
     * @return mixed
     */
    private function _filterEmptyFields($oSurvey, $fieldArrayName, $newValue = null)
    {
        $aSurvey = $oSurvey->attributes;

        if ($newValue === null) {
            $newValue = App()->request->getPost($fieldArrayName, null);
        }

        if ($newValue === null) {
            $newValue = isset($aSurvey[$fieldArrayName]) ? $aSurvey[$fieldArrayName] : $oSurvey->{$fieldArrayName};
        } else {
            $this->updatedFields[] = $fieldArrayName;
        }

        $newValue = trim($newValue);

        $options = $this->updateableFields[$fieldArrayName];

        switch ($options['type']) {
            case 'yesno':
            if ($newValue != 'Y' && $newValue != 'N' && $newValue != 'I') {
                $newValue = (int) $newValue;
                $newValue = ($newValue === 1) ? 'Y' : 'N';
            }
            break;
            case 'Int':
                $newValue = (int) $newValue;
            break;
        }

        return $newValue;
    }

    /**
     * action to do when adding a new question (insert or copy)
     * @param integer $iSurveyID
     * @return void (redirect)
     */
    private function actionInsertCopyQuestion($iSurveyID)
    {
        /** @var Survey $survey */
        $survey = Survey::model()->findByPk($iSurveyID);
        $sBaseLanguage = $survey->language;

        // Abort if survey is active
        if ($survey->active !== 'N') {
            Yii::app()->setFlashMessage(gT("You can't insert a new question when the survey is active."), 'error');
            $this->getController()->redirect(array("/admin/survey/sa/view/surveyid/".$survey->sid), "refresh");
        }

        if (strlen(Yii::app()->request->getPost('title')) < 1) {
            /* Already done in model : must control if return a good system or not here : BUT difficult to submit an empty string here */
            Yii::app()->setFlashMessage(gT("The question could not be added. You must enter at least a question code."), 'error');
        } else {

            if (Yii::app()->request->getPost('questionposition', "") != "") {
                $iQuestionOrder = intval(Yii::app()->request->getPost('questionposition'));
                //Need to renumber all questions on or after this
                $sQuery = "UPDATE {{questions}} SET question_order=question_order+1 WHERE gid=:gid AND question_order >= :order";
                Yii::app()->db->createCommand($sQuery)->bindValues(array(':gid'=>$this->iQuestionGroupID, ':order'=>$iQuestionOrder))->query();
            } else {
                $iQuestionOrder = getMaxQuestionOrder($this->iQuestionGroupID);
                $iQuestionOrder++;
            }
            $sQuestionText = Yii::app()->request->getPost('question_'.$sBaseLanguage, '');
            $sQuestionHelp = Yii::app()->request->getPost('help_'.$sBaseLanguage, '');
            // Fix bug with FCKEditor saving strange BR types : in rules ?
            $sQuestionText = $this->oFixCKeditor->fixCKeditor($sQuestionText);
            $sQuestionHelp = $this->oFixCKeditor->fixCKeditor($sQuestionHelp);

            $this->iQuestionID = 0;

            $oQuestion = new Question;
            $oQuestion->sid = $iSurveyID;
            $oQuestion->gid = $this->iQuestionGroupID;
            $oQuestion->type = Yii::app()->request->getPost('type');
            $oQuestion->title = Yii::app()->request->getPost('title');
            $oQuestion->preg = Yii::app()->request->getPost('preg');
            $oQuestion->other = Yii::app()->request->getPost('other');

            // For Bootstrap Version usin YiiWheels switch :
            $oQuestion->mandatory = Yii::app()->request->getPost('mandatory');
            $oQuestion->encrypted = Yii::app()->request->getPost('encrypted');
            $oQuestion->other = Yii::app()->request->getPost('other');

            $oQuestion->relevance = Yii::app()->request->getPost('relevance');
            $oQuestion->question_order = (int) $iQuestionOrder;
            $oQuestion->save();
            if ($oQuestion) {
                $this->iQuestionID = $oQuestion->qid;
            }
            $aErrors = $oQuestion->getErrors();
            if (count($aErrors)) {
                foreach ($aErrors as $sAttribute=>$aStringErrors) {
                    foreach ($aStringErrors as $sStringErrors) {
                                            Yii::app()->setFlashMessage(sprintf(gT("Question could not be created with error on %s: %s"), $sAttribute, $sStringErrors), 'error');
                    }
                }
            }

            $oQuestionLS = new QuestionL10n;
            $oQuestionLS->language = $sBaseLanguage;
            $oQuestionLS->question = $sQuestionText;
            $oQuestionLS->help = $sQuestionHelp;
            $oQuestionLS->qid = $oQuestion->qid;
            $oQuestionLS->save();
            
            $aErrors = $oQuestionLS->getErrors();
            if (count($aErrors)) {
                foreach ($aErrors as $sAttribute=>$aStringErrors) {
                    foreach ($aStringErrors as $sStringErrors) {
                                            Yii::app()->setFlashMessage(sprintf(gT("Question could not be created with error on %s: %s"), $sAttribute, $sStringErrors), 'error');
                    }
                }
            }
            // Add other languages
            if ($this->iQuestionID) {
                $addlangs = $survey->additionalLanguages;
                foreach ($addlangs as $alang) {
                    if ($alang != "") {
                        $oQuestionLS = new QuestionL10n;
                        $oQuestionLS->qid = $this->iQuestionID;
                        $oQuestionLS->question = Yii::app()->request->getPost('question_'.$alang);
                        $oQuestionLS->help = Yii::app()->request->getPost('help_'.$alang);
                        $oQuestionLS->language = $alang;
                        $oQuestionLS->save();
                        $aErrors = $oQuestionLS->getErrors();
                        if (count($aErrors)) {
                            foreach ($aErrors as $sAttribute=>$aStringErrors) {
                                foreach ($aStringErrors as $sStringErrors) {
                                                                    Yii::app()->setFlashMessage(sprintf(gT("Question in language %s could not be created with error on %s: %s"), $alang, $sAttribute, $sStringErrors), 'error');
                                }
                            }
                        }
                    }
                }
            }


            if (!$this->iQuestionID) {
                Yii::app()->setFlashMessage(gT("Question could not be created."), 'error');

            } else {
                if (Yii::app()->request->getPost('action') == 'copyquestion') {
                    /** @var Question $oOldQuestion */
                    $oldQID = returnGlobal('oldqid');

                    if (returnGlobal('copysubquestions') == 1) {
                        if ($oldQID) {
                            $oOldQuestion = Question::model()->findByPk( array('qid' => $oldQID));

                            // subquestions
                            $aOldNewSubquestions = array(); // track old and new sqid's
                            $oOldSubQuestions = Question::model()->with('questionl10ns')->findAllByAttributes(array("parent_qid"=>$oldQID), array('order'=>'question_order'));
                            foreach ($oOldSubQuestions as $sSubquestionIndex => $subquestion) {
                                $aInsertData = $subquestion->attributes;
                                unset($aInsertData['qid']);
                                $aInsertData['parent_qid'] = $this->iQuestionID;
                                if (Question::model()->insertRecords($aInsertData)){
                                    $iNewSubquestionId = Yii::app()->db->getLastInsertID();
                                    $aOldNewSubquestions[$subquestion->qid] = $iNewSubquestionId;

                                    if (isset($subquestion->questionl10ns)){
                                        foreach($subquestion->questionl10ns as $language => $questionl10ns){
                                            $oQuestionLS = new QuestionL10n;
                                            $oQuestionLS->language = $language;
                                            $oQuestionLS->question = $questionl10ns->question;
                                            $oQuestionLS->help = $questionl10ns->help;
                                            $oQuestionLS->qid = $iNewSubquestionId;
                                            $oQuestionLS->save();
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (returnGlobal('copyanswers') == 1) {
                        $oOldAnswers = Answer::model()->with('answerl10ns')->findAllByAttributes(array("qid"=>$oldQID));
                        foreach ($oOldAnswers as $answer) {
                            $newAnswer = new Answer();
                            $newAnswer->attributes = $answer->attributes;
                            $newAnswer->qid = $this->iQuestionID;
                            if ($newAnswer->save()) {
                                $iNewAnswerId = Yii::app()->db->getLastInsertID();                                 
                                if (isset($answer->answerl10ns)){
                                    foreach($answer->answerl10ns as $language => $answerL10ns){
                                        $oAnswerLS = new AnswerL10n;
                                        $oAnswerLS->language = $language;
                                        $oAnswerLS->answer = $answerL10ns->answer;
                                        $oAnswerLS->aid = $iNewAnswerId;
                                        $oAnswerLS->save();
                                    }
                                }
                            }
                        }
                    }
                    if (returnGlobal('copydefaultanswers') == 1) {
                        $oOldDefaultValues = DefaultValue::model()->with('defaultvaluel10ns')->findAll("qid=:qid", array("qid"=>returnGlobal('oldqid')));
                        foreach ($oOldDefaultValues as $defaultvalue) {
                            $newDefaultValue = new DefaultValue();
                            $newDefaultValue->qid = $this->iQuestionID;
                            $newDefaultValue->sqid = array_key_exists($defaultvalue['sqid'], $aOldNewSubquestions) ? $aOldNewSubquestions[$defaultvalue['sqid']] : 0;
                            $newDefaultValue->scale_id = $defaultvalue['scale_id'];
                            $newDefaultValue->specialtype = $defaultvalue['specialtype'];
                            if ($newDefaultValue->save()) {
                                $iNewDefaultValueId = Yii::app()->db->getLastInsertID();                                 
                                if (isset($defaultvalue->defaultvaluel10ns)){
                                    foreach($defaultvalue->defaultvaluel10ns as $language => $defaultValueL10ns){
                                        $oDefaultValueLS = new DefaultValueL10n;
                                        $oDefaultValueLS->dvid = $iNewDefaultValueId;
                                        $oDefaultValueLS->language = $language;
                                        $oDefaultValueLS->defaultvalue = $defaultValueL10ns->defaultvalue;
                                        $oDefaultValueLS->save();
                                    }
                                }
                            }
                        }
                    }

                    /**
                     * Copy attribute
                     */
                    if (returnGlobal('copyattributes') == 1) {
                        $oOldAttributes = QuestionAttribute::model()->findAll("qid=:qid", array("qid"=>returnGlobal('oldqid')));
                        foreach ($oOldAttributes as $oOldAttribute) {
                            $attribute = new QuestionAttribute;
                            $attribute->qid = $this->iQuestionID;
                            $attribute->value = $oOldAttribute->value;
                            $attribute->attribute = $oOldAttribute->attribute;
                            $attribute->language = $oOldAttribute->language;
                            $attribute->save();
                        }
                    }
                } else {
                    $validAttributes = QuestionAttribute::getQuestionAttributesSettings(Yii::app()->request->getPost('type'));
                    $aAttributeValues = [];
                    // If the question has a custom template, we first check if it provides custom attributes
                    $aAttributeValues['question_template'] = 'core';
                    // @todo Bug: Where does $cqr come from?
                    if (isset($cqr)) {
                        $oAttributeValues = QuestionAttribute::model()->find("qid=:qid and attribute='question_template'", array('qid'=>$cqr->qid));
                        if (is_object($oAttributeValues && $oAttributeValues->value)) {
                            $aAttributeValues['question_template'] = $oAttributeValues->value;
                        }
                    } else {
                        $cqr = null;
                    }

                    $validAttributes = Question::getQuestionTemplateAttributes($validAttributes, $aAttributeValues, $cqr);

                    $aLanguages = array_merge(array(Survey::model()->findByPk($iSurveyID)->language), Survey::model()->findByPk($iSurveyID)->additionalLanguages);
                    /* Start to fix some param before save (TODO : use models directly ?) */
                    /* Date management */
                    Yii::app()->loadHelper('surveytranslator');


                    foreach ($validAttributes as $validAttribute) {
                        if ($validAttribute['i18n']) {
                            foreach ($aLanguages as $sLanguage) {
                                $value = Yii::app()->request->getPost($validAttribute['name'].'_'.$sLanguage);
                                $iInsertCount = QuestionAttribute::model()->findAllByAttributes(array('attribute'=>$validAttribute['name'], 'qid'=>$this->iQuestionID, 'language'=>$sLanguage));
                                if (count($iInsertCount) > 0) {
                                    if ($value != '') {
                                        QuestionAttribute::model()->updateAll(array('value'=>$value), 'attribute=:attribute AND qid=:qid AND language=:language', array(':attribute'=>$validAttribute['name'], ':qid'=>$this->iQuestionID, ':language'=>$sLanguage));
                                    } else {
                                        QuestionAttribute::model()->deleteAll('attribute=:attribute AND qid=:qid AND language=:language', array(':attribute'=>$validAttribute['name'], ':qid'=>$this->iQuestionID, ':language'=>$sLanguage));
                                    }
                                } elseif ($value != '') {
                                    $attribute = new QuestionAttribute;
                                    $attribute->qid = $this->iQuestionID;
                                    $attribute->value = $value;
                                    $attribute->attribute = $validAttribute['name'];
                                    $attribute->language = $sLanguage;
                                    $attribute->save();
                                }
                            }
                        } else {
                            $value = Yii::app()->request->getPost($validAttribute['name']);

                            if ($validAttribute['name'] == 'multiflexible_step' && trim($value) != '') {
                                $value = floatval($value);
                                if ($value == 0) {
                                    $value = 1;
                                }
                            };

                            $iInsertCount = QuestionAttribute::model()->findAllByAttributes(array('attribute'=>$validAttribute['name'], 'qid'=>$this->iQuestionID));
                            if (count($iInsertCount) > 0) {
                                if ($value != $validAttribute['default'] && trim($value) != "") {
                                    QuestionAttribute::model()->updateAll(array('value'=>$value), 'attribute=:attribute AND qid=:qid', array(':attribute'=>$validAttribute['name'], ':qid'=>$this->iQuestionID));
                                } else {
                                    QuestionAttribute::model()->deleteAll('attribute=:attribute AND qid=:qid', array(':attribute'=>$validAttribute['name'], ':qid'=>$this->iQuestionID));
                                }
                            } elseif ($value != $validAttribute['default'] && trim($value) != "") {
                                $attribute = new QuestionAttribute;
                                $attribute->qid = $this->iQuestionID;
                                $attribute->value = $value;
                                $attribute->attribute = $validAttribute['name'];
                                $attribute->save();
                            }
                        }
                    }

                }

                LimeExpressionManager::SetDirtyFlag();
                LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyID);
                $this->_resetEM();
                Question::model()->updateQuestionOrder($this->iQuestionGroupID, $iSurveyID);
                Yii::app()->session['flashmessage'] = gT("Question was successfully added.");

            }

        }
        //This is SUPER important! Recalculating the ExpressionScript Engine state!
        LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting
        $redirectLink = $this->getController()->createUrl('admin/questions/sa/view/', array('surveyid' => $iSurveyID, 'gid' => $this->iQuestionGroupID, 'qid' => $this->iQuestionID));
        if (Yii::app()->request->getPost('saveandnew', '') != '') {
            $redirectLink = $this->getController()->createUrl('admin/questions/sa/newquestion/', array('surveyid' => $iSurveyID, 'gid' => $this->iQuestionGroupID));
        }

        $this->getController()->redirect($redirectLink);
    }

    private function _resetEM()
    {
        $oSurvey = Survey::model()->findByPk($this->iSurveyID);
        $oEM =& LimeExpressionManager::singleton();
        LimeExpressionManager::SetDirtyFlag(); // UpgradeConditionsToRelevance SetDirtyFlag too
        LimeExpressionManager::UpgradeConditionsToRelevance($this->iSurveyID);
        LimeExpressionManager::SetPreviewMode('database');// Deactivate _UpdateValuesInDatabase
        LimeExpressionManager::StartSurvey($oSurvey->sid,'survey',$oSurvey->attributes,true);
        LimeExpressionManager::StartProcessingPage(true,true);
        $aGrouplist = QuestionGroup::model()->findAllByAttributes(['sid' => $this->iSurveyID]);
        foreach ($aGrouplist as $iGID => $aGroup) {
            LimeExpressionManager::StartProcessingGroup($aGroup['gid'], $oSurvey->anonymized != 'Y', $this->iSurveyID);
            LimeExpressionManager::FinishProcessingGroup();
        }
        LimeExpressionManager::FinishProcessingPage();

        // Flush emcache when changes are made to the survey.
        EmCacheHelper::init(['sid' => $this->iSurveyID, 'active' => 'Y']);
        EmCacheHelper::flush();
    }
}
