<?php

if (!defined('BASEPATH')) {
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
* question
*
* @package LimeSurvey
* @author
* @copyright 2011
* @access public
*/
class questions extends Survey_Common_Action
{

    public function view($surveyid, $gid, $qid)
    {
        $aData = array();

        // Init general variables
        $aData['surveyid'] = $iSurveyID = (int) $surveyid;
        $aData['gid'] = $gid;
        $aData['qid'] = $qid;
        $survey = Survey::model()->findByPk($iSurveyID);
        $baselang = $survey->language;

        //Show Question Details
        //Count answer-options for this question
        $qrr = Answer::model()->findAllByAttributes(array('qid' => $qid, 'language' => $baselang));

        $aData['qct'] = count($qrr);

        //Count subquestions for this question
        $sqrq = Question::model()->findAllByAttributes(array('parent_qid' => $qid, 'language' => $baselang));
        $aData['sqct'] = count($sqrq);

        $qrrow = Question::model()->findByAttributes(array('qid' => $qid, 'gid' => $gid, 'sid' => $iSurveyID, 'language' => $baselang));
        if (is_null($qrrow)) {
            return;
        }

        // Check if other questions in the Survey are dependent upon this question
        $condarray = getQuestDepsForConditions($iSurveyID, "all", "all", $qid, "by-targqid", "outsidegroup");

        if (is_null($survey)) {
            Yii::app()->session['flashmessage'] = gT("Invalid survey ID");
            $this->getController()->redirect(array("admin/index"));
        } //  if surveyid is invalid then die to prevent errors at a later time

        $aData['activated'] = $survey->active;

        $oQuestion = $qrrow;
        $aData['oQuestion'] = $oQuestion;
        $qrrow = $qrrow->attributes;
        $aData['languagelist'] = $survey->allLanguages;
        $aData['qtypes'] = getQuestionTypeList('', 'array');

        $qshowstyle = "";


        $aData['qshowstyle'] = $qshowstyle;
        $aData['surveyid'] = $iSurveyID;
        $aData['qid'] = $qid;
        $aData['gid'] = $gid;
        $aData['qrrow'] = $qrrow;
        $aData['baselang'] = $baselang;
        $aAttributesWithValues = Question::model()->getAdvancedSettingsWithValues($qid, $qrrow['type'], $iSurveyID, $baselang);
        $DisplayArray = array();

        foreach ($aAttributesWithValues as $aAttribute) {
            if (($aAttribute['i18n'] == false && isset($aAttribute['value']) && $aAttribute['value'] != $aAttribute['default'])
                || ($aAttribute['i18n'] == true && isset($aAttribute['value'][$baselang]) && $aAttribute['value'][$baselang] != $aAttribute['default'])) {
                if ($aAttribute['inputtype'] == 'singleselect') {
                    if (isset($aAttribute['options'][$aAttribute['value']])) {
                        $aAttribute['value'] = $aAttribute['options'][$aAttribute['value']];
                    }
                }
                $DisplayArray[] = $aAttribute;
            }
        }
        $aData['advancedsettings'] = $DisplayArray;
        $aData['condarray'] = $condarray;
        $aData['sImageURL'] = Yii::app()->getConfig('adminimageurl');
        $aData['iIconSize'] = Yii::app()->getConfig('adminthemeiconsize');

        $this->getController()->renderPartial('/admin/survey/Question/questionbar_view', $aData, true);
        $aData['display']['menu_bars']['gid_action'] = 'viewquestion';
        $aData['questionbar']['buttons']['view'] = true;

        ///////////
        // sidemenu
        $aData['sidemenu']['state'] = true;
        $aData['sidemenu']['explorer']['state'] = true;
        $aData['sidemenu']['explorer']['gid'] = (isset($gid)) ? $gid : false;
        $aData['sidemenu']['explorer']['qid'] = (isset($qid)) ? $qid : false;

        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";

        // Last question visited : By user (only one by user)
        $setting_entry = 'last_question_'.Yii::app()->user->getId();
        SettingGlobal::setSetting($setting_entry, $qid);

        // we need to set the sid for this question
        $setting_entry = 'last_question_sid_'.Yii::app()->user->getId();
        SettingGlobal::setSetting($setting_entry, $iSurveyID);

        // we need to set the gid for this question
        $setting_entry = 'last_question_gid_'.Yii::app()->user->getId();
        SettingGlobal::setSetting($setting_entry, $gid);

        // Last question for this survey (only one by survey, many by user)
        $setting_entry = 'last_question_'.Yii::app()->user->getId().'_'.$iSurveyID;
        SettingGlobal::setSetting($setting_entry, $qid);

        // we need to set the gid for this question
        $setting_entry = 'last_question_'.Yii::app()->user->getId().'_'.$iSurveyID.'_gid';
        SettingGlobal::setSetting($setting_entry, $gid);

        $aData['surveyIsActive'] = $survey->active !== 'N';

        $this->_renderWrappedTemplate('survey/Question', 'question_view', $aData);
    }

    /**
     * Display import view
     */
    public function importView($groupid = null, $surveyid)
    {
        $iSurveyID = (int) $surveyid;
        if (!Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'import')) {
            Yii::app()->session['flashmessage'] = gT("We are sorry but you don't have permissions to do this.");
            $this->getController()->redirect(array('admin/survey/sa/listquestions/surveyid/'.$iSurveyID));
        }
        $survey = Survey::model()->findByPk($iSurveyID);
        $aData = [];
        $aData['sidemenu']['state'] = false;
        $aData['sidemenu']['questiongroups'] = true;
        $aData['surveybar']['closebutton']['url'] = '/admin/survey/sa/listquestiongroups/surveyid/'.$iSurveyID; // Close button
        $aData['surveybar']['savebutton']['form'] = true;
        $aData['surveybar']['savebutton']['text'] = gt('Import');
        $aData['surveyid'] = $iSurveyID;
        $aData['groupid'] = $groupid;
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";
        $this->_renderWrappedTemplate('survey/Question', 'importQuestion_view', $aData);
    }

    /**
     * Function responsible to import a question.
     *
     * @access public
     * @return void
     */
    public function import()
    {
        $action = returnGlobal('action');
        $iSurveyID = (int) returnGlobal('sid');
        $oSurvey = Survey::model()->findByPk($iSurveyID);

        $gid = returnGlobal('gid');
        $aViewUrls = array();

        $aData = [];
        $aData['display']['menu_bars']['surveysummary'] = 'viewquestion';
        $aData['display']['menu_bars']['gid_action'] = 'viewgroup';

        if ($action == 'importquestion') {
            $sFullFilepath = Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.randomChars(20);
            $sExtension = pathinfo($_FILES['the_file']['name'], PATHINFO_EXTENSION);
            $fatalerror = '';

            if ($_FILES['the_file']['error'] == 1 || $_FILES['the_file']['error'] == 2) {
                $fatalerror = sprintf(gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."), getMaximumFileUploadSize() / 1024 / 1024).'<br>';
            } elseif (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath)) {
                $fatalerror = gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder.").'<br>';
            }

            // validate that we have a SID and GID
            if (!$iSurveyID) {
                            $fatalerror .= gT("No SID (Survey) has been provided. Cannot import question.");
            }

            if (!$gid) {
                            $fatalerror .= gT("No GID (Group) has been provided. Cannot import question");
            }

            if ($fatalerror != '') {
                unlink($sFullFilepath);
                $message = '<p>'.$fatalerror.'</p>
                <a class="btn btn-default btn-lg"
                href="'.$this->getController()->createUrl('admin/survey/sa/listquestions/surveyid/').'/'.$iSurveyID.'">'
                .gT("Return to question list").'</a></p>';
                $this->_renderWrappedTemplate('super', 'messagebox', array('title'=>gT('Error'), 'message'=>$message));
                die();
            }

            // IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY
            Yii::app()->loadHelper('admin/import');

            $aImportResults = array();
            if (strtolower($sExtension) == 'lsq') {
                $aImportResults = XMLImportQuestion($sFullFilepath, $iSurveyID, $gid, array('autorename'=>Yii::app()->request->getPost('autorename') == '1' ?true:false));
            } else {
                $this->getController()->error(gT('Unknown file extension'));
            }

            fixLanguageConsistency($iSurveyID);

            if (isset($aImportResults['fatalerror'])) {
                //echo htmlentities($aImportResults['fatalerror']); die();
                $message = $aImportResults['fatalerror'];
                $message .= '<p>
                <a class="btn btn-default btn-lg"
                href="'.$this->getController()->createUrl('admin/survey/sa/listquestions/surveyid/').'/'.$iSurveyID.'">'
                .gT("Return to question list").'</a></p>';
                $this->_renderWrappedTemplate('super', 'messagebox', array('title'=>gT('Error'), 'message'=>$message));
                App()->end();
            }

            unlink($sFullFilepath);

            $aData['aImportResults'] = $aImportResults;
            $aData['surveyid'] = $iSurveyID;
            $aData['gid'] = $gid;
            $aData['sExtension'] = $sExtension;
            $aViewUrls[] = 'import_view';
        }

        /////
        $aData['sidemenu']['state'] = false;
        $aData['surveyid'] = $iSurveyID;
        $aData['title_bar']['title'] = $oSurvey->defaultlanguage->surveyls_title." (".gT("ID").":".$iSurveyID.")";

        $this->_renderWrappedTemplate('survey/Question', $aViewUrls, $aData);
    }

    /**
     * Load edit default values of a question screen
     *
     * @access public
     * @param int $surveyid
     * @param int $gid
     * @param int $qid
     * @return void
     */
    public function editdefaultvalues($surveyid, $gid, $qid)
    {
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }
        $iSurveyID = (int) $surveyid;
        $survey = Survey::model()->findByPk($iSurveyID);

        $gid = (int) $gid;
        $qid = (int) $qid;


        Yii::app()->loadHelper('surveytranslator');


        $oQuestion = Question::model()->findByAttributes(array(
            'qid' => $qid,
            'gid' => $gid,
            'language' => $survey->language
        ));

        $questionrow = $oQuestion->attributes;

        $qtproperties = getQuestionTypeList('', 'array');

        $langopts = array();
        foreach ($survey->allLanguages as $language) {
            $langopts[$language] = array();
            $langopts[$language][$questionrow['type']] = array();

            // If there are answerscales
            if ($qtproperties[$questionrow['type']]['answerscales'] > 0) {
                for ($scale_id = 0; $scale_id < $qtproperties[$questionrow['type']]['answerscales']; $scale_id++) {
                    $langopts[$language][$questionrow['type']][$scale_id] = array();

                    $defaultvalue = DefaultValue::model()->findByAttributes(array(
                    'specialtype' => '',
                    'qid' => $qid,
                    'scale_id' => $scale_id,
                    'language' => $language
                    ));

                    $defaultvalue = $defaultvalue != null ? $defaultvalue->defaultvalue : null;

                    $langopts[$language][$questionrow['type']][$scale_id]['defaultvalue'] = $defaultvalue;

                    $answerresult = Answer::model()->findAllByAttributes(array(
                    'qid' => $qid,
                    'language' => $language
                    ), array('order' => 'sortorder'));
                    $langopts[$language][$questionrow['type']][$scale_id]['answers'] = $answerresult;

                    if ($questionrow['other'] == 'Y') {
                        $defaultvalue = DefaultValue::model()->findByAttributes(array(
                        'specialtype' => 'other',
                        'qid' => $qid,
                        'scale_id' => $scale_id,
                        'language' => $language
                        ));

                        $defaultvalue = $defaultvalue != null ? $defaultvalue->defaultvalue : null;
                        $langopts[$language][$questionrow['type']]['Ydefaultvalue'] = $defaultvalue;
                    }
                }
            }

            // If there are subquestions and no answerscales
            if ($qtproperties[$questionrow['type']]['answerscales'] == 0 &&
            $qtproperties[$questionrow['type']]['subquestions'] > 0) {
                for ($scale_id = 0; $scale_id < $qtproperties[$questionrow['type']]['subquestions']; $scale_id++) {
                    $langopts[$language][$questionrow['type']][$scale_id] = array();

                    $sqresult = Question::model()->findAllByAttributes(array(
                    'sid' => $iSurveyID,
                    'gid' => $gid,
                    'parent_qid' => $qid,
                    'language' => $language,
                    'scale_id' => 0
                    ), array('order' => 'question_order'));

                    $langopts[$language][$questionrow['type']][$scale_id]['sqresult'] = array();

                    $options = array();
                    if ($questionrow['type'] == 'M' || $questionrow['type'] == 'P') {
                                            $options = array('' => gT('<No default value>'), 'Y' => gT('Checked'));
                    }

                    foreach ($sqresult as $aSubquestion) {
                        $defaultvalue = DefaultValue::model()->findByAttributes(array(
                        'specialtype' => '',
                        'qid' => $qid,
                        'sqid' => $aSubquestion['qid'],
                        'scale_id' => $scale_id,
                        'language' => $language
                        ));
                        $defaultvalue = $defaultvalue != null ? $defaultvalue->defaultvalue : null;

                        $aSubquestion = $aSubquestion->attributes;
                        $aSubquestion['defaultvalue'] = $defaultvalue;
                        $aSubquestion['options'] = $options;

                        $langopts[$language][$questionrow['type']][$scale_id]['sqresult'][] = $aSubquestion;
                    }
                }
            }
            if ($qtproperties[$questionrow['type']]['answerscales'] == 0 &&
            $qtproperties[$questionrow['type']]['subquestions'] == 0) {
                $defaultvalue = DefaultValue::model()->findByAttributes(array(
                'specialtype' => '',
                'qid' => $qid,
                'scale_id' => 0,
                'language' => $language
                ));
                $langopts[$language][$questionrow['type']][0] = $defaultvalue != null ? $defaultvalue->defaultvalue : null;
            }

        }

        $aData = array(
            'oQuestion' => $oQuestion,
            'qid' => $qid,
            'surveyid' => $iSurveyID,
            'langopts' => $langopts,
            'questionrow' => $questionrow,
            'gid' => $gid,
            'qtproperties' => $qtproperties,
        );


        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";
        $aData['questiongroupbar']['savebutton']['form'] = 'frmeditgroup';
        $aData['questiongroupbar']['closebutton']['url'] = 'admin/questions/sa/view/surveyid/'.$iSurveyID.'/gid/'.$gid.'/qid/'.$qid; // Close button

        $aData['questiongroupbar']['saveandclosebutton']['form'] = 'frmeditgroup';
        $aData['display']['menu_bars']['surveysummary'] = 'editdefaultvalues';
        $aData['display']['menu_bars']['qid_action'] = 'editdefaultvalues';


        ///////////
        // sidemenu
        $aData['sidemenu']['state'] = false;
        $aData['sidemenu']['explorer']['state'] = true;
        $aData['sidemenu']['explorer']['gid'] = (isset($gid)) ? $gid : false;
        $aData['sidemenu']['explorer']['qid'] = (isset($qid)) ? $qid : false;
        $aData['hasUpdatePermission'] =
            Permission::model()->hasSurveyPermission($iSurveyID, 'surveycontent', 'update') ?
            '' :
            'disabled="disabled" readonly="readonly"';

        $this->_renderWrappedTemplate('survey/Question', 'editdefaultvalues_view', $aData);
    }

    /**
     * Load complete editing of answer options screen.
     *
     * @access public
     * @param int $surveyid
     * @param int $gid
     * @param int $qid
     */
    public function answeroptions($surveyid, $gid, $qid)
    {
        // Abort if user lacks permission to update survey content
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }

        $surveyid = sanitize_int($surveyid);
        $survey = Survey::model()->findByPk($surveyid);

        $qid = sanitize_int($qid);
        $gid = sanitize_int($gid);
        App()->getClientScript()->registerScriptFile(
            App()->getConfig('adminscripts').'answers.js',
            LSYii_ClientScript::POS_BEGIN
        );
        App()->getClientScript()->registerPackage('jquery-selectboxes');

        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$surveyid.")";
        $aData['questiongroupbar']['savebutton']['form'] = true;
        $aData['questiongroupbar']['saveandclosebutton']['form'] = 'frmeditgroup';
        $aData['questiongroupbar']['closebutton']['url'] = 'admin/questions/sa/view/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid; // Close button

        $aData['surveyid'] = $surveyid;
        $aData['gid']      = $gid;
        $aData['qid']      = $qid;
        Yii::app()->session['FileManagerContext'] = "edit:answer:{$surveyid}";
        $aViewUrls = $this->_editansweroptions($surveyid, $gid, $qid);

        ///////////
        // sidemenu
        $aData['sidemenu']['state'] = false;
        $aData['sidemenu']['explorer']['state'] = true;
        $aData['sidemenu']['explorer']['gid'] = (isset($gid)) ? $gid : false;
        $aData['sidemenu']['explorer']['qid'] = (isset($qid)) ? $qid : false;


        $this->_renderWrappedTemplate('survey/Question', $aViewUrls, $aData);
    }

    /**
     * Load editing of answer options specific screen only.
     *
     * @access public
     * @param int $surveyid
     * @param int $gid
     * @param int $qid
     * @return array
     */
    public function _editansweroptions($surveyid, $gid, $qid)
    {
        Yii::app()->loadHelper('database');
        $iSurveyID = (int) $surveyid;
        $qid = (int) $qid;
        $gid = (int) $gid;

        // Get languages select on survey.
        $anslangs = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
        $baselang = Survey::model()->findByPk($iSurveyID)->language;

        $oQuestion = $qrow = Question::model()->findByAttributes(array('qid' => $qid, 'language' => $baselang));
        $qtype = $qrow['type'];

        $qtypes = getQuestionTypeList('', 'array');

        $scalecount = $qtypes[$qtype]['answerscales'];

        // Check if there is at least one answer
        for ($i = 0; $i < $scalecount; $i++) {
            $ans = new CDbCriteria;
            $ans->addCondition("qid=$qid")->addCondition("scale_id=$i")->addCondition("language='$baselang'");
            $qresult = Answer::model()->count($ans);

            if ((int) $qresult == 0) {
                $oAnswer = new Answer;
                $oAnswer->qid = $qid;
                $oAnswer->code = 'A1';
                $oAnswer->answer = "";
                $oAnswer->language = $baselang;
                $oAnswer->sortorder = 0;
                $oAnswer->scale_id = $i;
                $oAnswer->assessment_value = 0;
                $oAnswer->save();
            }
        }


        // Check that there are answers for every language supported by the survey
        for ($i = 0; $i < $scalecount; $i++) {
            foreach ($anslangs as $language) {
                $ans = new CDbCriteria;
                $ans->addCondition("qid=$qid")->addCondition("scale_id=$i")->addCondition("language='$language'");
                $iAnswerCount = Answer::model()->count($ans);

                // Means that no record for the language exists in the answers table
                if (empty($iAnswerCount)) {
                    foreach (Answer::model()->findAllByAttributes(array(
                    'qid' => $qid,
                    'scale_id' => $i,
                    'language' => $baselang
                    )) as $answer) {
                        $oAnswer = new Answer;
                    }
                    $oAnswer->qid = $answer->qid;
                    $oAnswer->code = $answer->code;
                    $oAnswer->answer = $answer->answer;
                    $oAnswer->language = $language;
                    $oAnswer->sortorder = $answer->sortorder;
                    $oAnswer->scale_id = $i;
                    $oAnswer->assessment_value = (isset($answer->assessment_value) ? $answer->assessment_value : 0);
                    $oAnswer->save();
                }
            }
        }

        // Makes an array with ALL the languages supported by the survey -> $anslangs
        array_unshift($anslangs, $baselang);

        // Delete the answers in languages not supported by the survey
        $criteria = new CDbCriteria;
        $criteria->addColumnCondition(array('qid' => $qid));
        $criteria->addNotInCondition('language', $anslangs);
        //$languageresult = Answer::model()->deleteAll($criteria);

        if (!isset($_POST['ansaction'])) {
            // Check if any nulls exist. If they do, redo the sortorders
            $ans = new CDbCriteria;
            $ans->addCondition("qid=$qid")->addCondition("scale_id=$i")->addCondition("language='$baselang'");
            $cacount = Answer::model()->count($ans);
            if (!empty($cacount)) {
                            Answer::model()->updateSortOrder($qid, Survey::model()->findByPk($iSurveyID)->language);
            }
        }

        Yii::app()->loadHelper('admin/htmleditor');

        $row = Answer::model()->findByAttributes(array(
        'qid' => $qid,
        'language' => Survey::model()->findByPk($iSurveyID)->language
        ), array('order' => 'sortorder desc'));

        if (!is_null($row)) {
                    $maxsortorder = $row->sortorder + 1;
        } else {
                    $maxsortorder = 1;
        }

        $aData['oQuestion'] = $oQuestion;
        $aData['surveyid'] = $iSurveyID;
        $aData['gid'] = $gid;
        $aData['qid'] = $qid;
        $aData['anslangs'] = $anslangs;
        $aData['scalecount'] = $scalecount;

        // The following line decides if the assessment input fields are visible or not
        $sumresult1 = Survey::model()->with(array('languagesettings'=>array('condition'=>'surveyls_language=language')))->together()->findByAttributes(array('sid' => $iSurveyID));
        if (is_null($sumresult1)) {
                    $this->getController()->error('Invalid survey ID');
        }

        // $surveyinfo = $sumresult1->attributes;
        // $surveyinfo = array_merge($surveyinfo, $sumresult1->defaultlanguage->attributes);
        // $surveyinfo = array_map('flattenText', $surveyinfo);
        $assessmentvisible = ($sumresult1->assessments == 'Y' && $qtypes[$qtype]['assessable'] == 1);
        $aData['assessmentvisible'] = $assessmentvisible;

        $aData['activated'] = $sumresult1->active;

        $results = array();
        foreach ($anslangs as $anslang) {
            for ($scale_id = 0; $scale_id < $scalecount; $scale_id++) {
                $criteria = new CDbCriteria;
                $criteria->condition = 'qid = :qid AND language = :language AND scale_id = :scale_id';
                $criteria->order = 'sortorder, code ASC';
                $criteria->params = array(':qid' => $qid, ':language' => $anslang, ':scale_id' => $scale_id);
                $results[$anslang][$scale_id] = Answer::model()->findAll($criteria);
                //$aData['results'][$anslang][$scale_id] = Answer::model()->findAll($criteria);
                foreach ($results[$anslang][$scale_id] as $row) {
                    $row->code      = htmlspecialchars($row->code);
                    $row->answer    = htmlspecialchars($row->answer);
                }
                $aData['tableId'][$anslang][$scale_id] = 'answers_'.$anslang.'_'.$scale_id;
            }

        }

        $aData['results'] = $results;
        $aData['viewType'] = 'answerOptions';
        $aData['formId'] = 'editanswersform';
        $aData['formName'] = 'editanswersform';
        $aData['pageTitle'] = gT('Edit answer options');

        $aViewUrls['_subQuestionsAndAnwsersJsVariables'][] = $aData;
        $aViewUrls['answerOptions_view'][] = $aData;

        return $aViewUrls;
    }

    /**
     * Load complete subquestions screen.
     *
     * @access public
     * @param int $surveyid
     * @param int $gid
     * @param int $qid
     * @return void
     */
    public function subquestions($surveyid, $gid, $qid)
    {
        $aData = [];
        $aData['surveyid'] = $surveyid = (int) $surveyid;
        $aData['gid'] = $gid = (int) $gid;
        $aData['qid'] = $qid = (int) $qid;

        // Abort if user lacks permission to update survey content
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }

        $survey = Survey::model()->findByPk($surveyid);

        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'subquestions.js', LSYii_ClientScript::POS_BEGIN);
        App()->getClientScript()->registerPackage('jquery-blockUI');
        App()->getClientScript()->registerPackage('jquery-selectboxes');
        Yii::app()->session['FileManagerContext'] = "edit:answer:{$surveyid}";

        $aData['display']['menu_bars']['surveysummary'] = 'viewgroup';
        $aData['display']['menu_bars']['gid_action'] = 'addquestion';
        $aData['display']['menu_bars']['qid_action'] = 'editsubquestions';
        $aViewUrls = $this->_editsubquestion($surveyid, $gid, $qid);

        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$surveyid.")";
        $aData['questiongroupbar']['savebutton']['form'] = 'frmeditgroup';
        $aData['questiongroupbar']['saveandclosebutton']['form'] = 'frmeditgroup';
        $aData['questiongroupbar']['closebutton']['url'] = 'admin/questions/sa/view/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid; // Close button

        ///////////
        // sidemenu
        $aData['sidemenu']['state'] = false;
        $aData['sidemenu']['explorer']['state'] = true;
        $aData['sidemenu']['explorer']['gid'] = (isset($gid)) ? $gid : false;
        $aData['sidemenu']['explorer']['qid'] = (isset($qid)) ? $qid : false;


        $this->_renderWrappedTemplate('survey/Question', $aViewUrls, $aData);
    }

    /**
     * Load only subquestion specific screen only.
     *
     * @access public
     * @param int $surveyid
     * @param int $gid
     * @param int $qid
     * @return array
     */
    public function _editsubquestion($surveyid, $gid, $qid)
    {
        // Abort if user lacks permission to update survey content
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }

        $surveyid = (int) $surveyid;
        $oSurvey = Survey::model()->findByPk($surveyid);
        $qid = sanitize_int($qid);
        $gid = sanitize_int($gid);

        // Get languages select on survey.
        $anslangs = $oSurvey->additionalLanguages;

        $oQuestion = Question::model()->findByPk(array('qid' => $qid, 'language' => $oSurvey->language));
        $aParentQuestion = $oQuestion->attributes;

        $sQuestiontype = $aParentQuestion['type'];
        $aQuestiontypeInfo = getQuestionTypeList($sQuestiontype, 'array');
        $iScaleCount = $aQuestiontypeInfo[$sQuestiontype]['subquestions'];

        for ($iScale = 0; $iScale < $iScaleCount; $iScale++) {
            $subquestiondata = Question::model()->findAllByAttributes(array(
            'parent_qid' => $qid,
            'language' => $oSurvey->language,
            'scale_id' => $iScale
            ));

            if (empty($subquestiondata)) {
                $data = array(
                    'sid' => $surveyid,
                    'gid' => $gid,
                    'parent_qid' => $qid,
                    'title' => 'SQ001',
                    'question' => '',
                    'question_order' => 1,
                    'language' => $oSurvey->language,
                    'relevance' => '1',
                    'scale_id' => $iScale,
                );
                $subQuestion = new Question();
                $subQuestion->attributes = $data;
                $subQuestion->save();

                $subquestiondata = Question::model()->findAllByAttributes(array(
                    'parent_qid' => $qid,
                    'language' => $oSurvey->language,
                    'scale_id' => $iScale
                ));
            }

            // Check that there are subquestions for every language supported by the survey
            foreach ($anslangs as $language) {
                foreach ($subquestiondata as $row) {
                    $qrow = Question::model()->count('
                    parent_qid = :qid AND
                    language = :language AND
                    qid = '.$row->qid.' AND
                    scale_id = :iScale',
                    array(
                    ':qid' => $qid,
                    ':language' => $language,
                    ':iScale' => $iScale
                    ));

                    // Means that no record for the language exists in the questions table
                    if (empty($qrow)) {
                        switchMSSQLIdentityInsert('questions', true);
                        $question = new Question;
                        $question->qid = $row->qid;
                        $question->sid = $surveyid;
                        $question->gid = $row->gid;
                        $question->parent_qid = $qid;
                        $question->title = $row->title;
                        $question->question = $row->question;
                        $question->question_order = $row->question_order;
                        $question->language = $language;
                        $question->scale_id = $iScale;
                        $question->relevance = $row->relevance;
                        $question->save();
                        switchMSSQLIdentityInsert('questions', false);
                    }
                }
            }
        }

        array_unshift($anslangs, $oSurvey->language);
        /* Fix subquestions */
        $oQuestion->fixSubQuestions();

        // Check sort order for subquestions
        $qresult = Question::model()->findByAttributes(array('qid' => $qid, 'language' => $oSurvey->language));
        if (!is_null($qresult)) {
                    $qtype = $qresult->type;
        }

        if (!empty($_POST['ansaction'])) {
            // Check if any nulls exist. If they do, redo the sortorders
            $cacount = Question::model()->count(array(
            'parent_qid' => $qid,
            'question_order' => null,
            'language' => $oSurvey->language
            ));

            if ($cacount) {
                            Answer::model()->updateSortOrder($qid, Survey::model()->findByPk($surveyid)->language);
            }
        }

        Yii::app()->loadHelper('admin/htmleditor');

        // Print Key Control JavaScript
        $result = Question::model()->findAllBYAttributes(array(
        'parent_qid' => $qid,
        'language' => Survey::model()->findByPk($surveyid)->language
        ), array('order' => 'question_order desc'));

        $aData['anscount'] = count($result);
        $row = $result[0]->attributes;
        $aData['row'] = $row;
        $maxsortorder = $row['question_order'] + 1;

        /**
         * The following line decides if the assessment input fields are visible or not
         * for some question types the assessment values is set in the label set instead of the answers
         */
        $qtypes = getQuestionTypeList('', 'array');
        Yii::app()->loadHelper('surveytranslator');

        $aData['scalecount'] = $scalecount = $qtypes[$qtype]['subquestions'];

        $sumresult1 = Survey::model()->with(array('languagesettings'=>array('condition'=>'surveyls_language=language')))->together()->findByAttributes(array('sid' => $surveyid));
        if ($sumresult1 == null) {
                    $this->getController()->error('Invalid survey ID');
        }

        // $surveyinfo = $sumresult1->attributes;
        // $surveyinfo = array_merge($surveyinfo, $sumresult1->defaultlanguage->attributes);
        // $surveyinfo = array_map('flattenText', $surveyinfo);

        $aData['activated']       = $sumresult1->active;
        $aData['surveyid']        = $surveyid;
        $aData['gid']             = $gid;
        $aData['qid']             = $qid;
        $aData['aParentQuestion'] = $aParentQuestion;
        $aData['anslangs']        = $anslangs;
        $aData['maxsortorder']    = $maxsortorder;
        $aData['oQuestion']       = $oQuestion;

        foreach ($anslangs as $anslang) {
            for ($scale_id = 0; $scale_id < $scalecount; $scale_id++) {
                $criteria = new CDbCriteria;
                $criteria->condition = 'parent_qid = :pqid AND language = :language AND scale_id = :scale_id';
                $criteria->order = 'question_order, title ASC';
                $criteria->params = array(':pqid' => $qid, ':language' => $anslang, ':scale_id' => $scale_id);
                $results[$anslang][$scale_id] = Question::model()->findAll($criteria);

                foreach ($results[$anslang][$scale_id] as $row) {
                    $row->title     = htmlspecialchars($row->title);
                    $row->question  = htmlspecialchars($row->question);
                    $row->relevance = htmlspecialchars($row->relevance);
                }
                $aData['tableId'][$anslang][$scale_id] = 'answers_'.$anslang.'_'.$scale_id;
            }
        }

        $aData['results'] = $results;
        $aData['pageTitle'] = gT('Edit subquestions');
        $aData['viewType'] = 'subQuestions';
        $aData['alternate'] = false;

        $aData['formId'] = 'editsubquestionsform';
        $aData['formName'] = 'editsubquestionsform';

        $aViewUrls['_subQuestionsAndAnwsersJsVariables'][] = $aData;
        $aViewUrls['answerOptions_view'][] = $aData;

        return $aViewUrls;
    }



    public function getSubquestionRowForAllLanguages($surveyid, $gid, $codes, $scale_id, $type, $languages, $position, $assessmentvisible = '')
    {
        $languages = explode(';', json_decode($languages));
        $html      = array();
        $first     = true;
        $qid = 'new'.rand(0, 99999);
        foreach ($languages as $language) {
            $html[$language] = $this->getSubquestionRow($surveyid, $gid, $qid, $codes, $language, $first, $scale_id, $type, $position, $assessmentvisible);
            $first = false;
        }
        header('Content-Type: application/json');
        echo json_encode($html);
    }

    /**
     * AJAX Method to QuickAdd multiple Rows AJAX-based
     * @todo Permission
     */
    public function getSubquestionRowQuickAdd($surveyid, $gid)
    {
        $qid               = '{{quid_placeholder}}';
        $request           = Yii::app()->request;
        $codes             = $request->getPost('codes');
        $language          = $request->getPost('language');
        $first             = $request->getPost('first');
        $scale_id          = $request->getPost('scale_id');
        $type              = $request->getPost('type');
        $position          = $request->getPost('position');
        $assessmentvisible = $request->getPost('assessmentvisible');
        echo $this->getSubquestionRow($surveyid, $gid, $qid, $codes, $language, $first, $scale_id, $type, $position, $assessmentvisible);
    }

    /**
     * This function should be called via ajax request
     * It returns a EMPTY subquestion row HTML for a given ....
     * @param string $qid
     */

    public function getSubquestionRow($surveyid, $gid, $qid, $codes, $language, $first, $scale_id, $type, $position, $assessmentvisible = '')
    {
        // index.php/admin/questions/sa/getSubquestionRow/position/1/scale_id/1/surveyid/691948/gid/76/qid/1611/language/en/first/true
        $stringCodes = json_decode($codes,true); // All the codes of the displayed subquestions

        // TODO: calcul correct value
        $oldCode = false;

        //Capture "true" and "false" as strings
        if (is_string($first)) {
            $first = ($first == "false" ? false : true);
        }
        // We get the numerical part of each code and we store them in Arrays
        // One array is to store the pure numerical values (so we can search in it for the greates value, and increment it)
        // Another array is to store the string values (so we keep all the prefixed "0")
        $numCodes = array();
        foreach ($stringCodes as $key => $stringCode) {
            // This will loop into the code, from the last character to the first letter
            $numericSuffix = ''; $n = 1; $numeric = true;
            while ($numeric === true && $n <= strlen($stringCode)) {
                $currentCharacter = (string) substr($stringCode, -$n, 1); // get the current character

                if (ctype_digit($currentCharacter)) {
                // check if it's numerical
                    $numericSuffix = $currentCharacter.$numericSuffix; // store it in a string
                    $n = $n + 1;
                } else {
                    $numeric = false; // At first non numeric character found, the loop is stoped
                }
            }
            $numCodesWithZero[$key] = (string) $numericSuffix; // In string type, we can have   : "0001"
            $numCodes[$key]         = (int) $numericSuffix; // In int type, we can only have : "1"
        }

        // Let's get the greatest code
        $greatestNumCode          = max($numCodes); // greatest code
        $key                      = array_keys($numCodes, max($numCodes)); // its key (same key in all tables)
        $greatesNumCodeWithZeros  = (isset($numCodesWithZero)) ? $numCodesWithZero[$key[0]] : ''; // its value with prefixed 0 (like : 001)
        $stringCodeOfGreatestCode = $stringCodes[$key[0]]; // its original submited  string (like: SQ001)

        // We get the string part of it: it's the original string code, without the greates code with its 0 :
        // like  substr ("SQ001", (strlen(SQ001)) - strlen(001) ) ==> "SQ"
        $stringPartOfNewCode    = (string) substr($stringCodeOfGreatestCode, 0, (strlen($stringCodeOfGreatestCode) - strlen($greatesNumCodeWithZeros)));

        // We increment by one the greatest code
        $numericalPartOfNewCode = $newPosition = $greatestNumCode + 1;

        // We get the list of 0 : (using $numericalPartOfNewCode will remove the excedent 0 ; SQ009 will be followed by SQ010 )
        $listOfZero = (string) substr($greatesNumCodeWithZeros, 0, (strlen($greatesNumCodeWithZeros) - strlen($numericalPartOfNewCode)));

        // When no more zero are available we want to be sure that the last 9 unit will not left
        // (like in SQ01 => SQ99 ; should become SQ100, not SQ9100)
        $listOfZero = ($listOfZero == "9") ? '' : $listOfZero;

        // We finaly build the new code
        $code = $stringPartOfNewCode.$listOfZero.$numericalPartOfNewCode;

        $activated = false; // You can't add ne subquestion when survey is active
        Yii::app()->loadHelper('admin/htmleditor'); // Prepare the editor helper for the view

        if ($type == 'subquestion') {
            $view = '_subquestion';
            $aData = array(
                'position'  => $position,
                'scale_id'  => $scale_id,
                'activated' => $activated,
                'first'     => $first,
                'surveyid'  => $surveyid,
                'gid'       => $gid,
                'qid'       => $qid,
                'language'  => $language,
                'title'     => $code,
                'question'  => '',
                'relevance' => '1',
                'oldCode'   => $oldCode,
            );
        } else {
            $view = '_answer_option';
            $aData = array(
                'assessmentvisible' => $assessmentvisible == "false" ? false : true,
                'assessment_value'  => '',
                'answer'            => '',
                'sortorder'         => $newPosition,
                'position'          => $newPosition,
                'scale_id'          => $scale_id,
                'activated'         => $activated,
                'first'             => $first,
                'surveyid'          => $surveyid,
                'gid'               => $gid,
                'qid'               => $qid,
                'language'          => $language,
                'title'             => $code,
                'question'          => '',
                'relevance'         => '1',
                'oldCode'           => $oldCode,
            );
        }

        $html = '<!-- Inserted Row -->';
        $html .= App()->twigRenderer->renderAnswerOptions('/admin/survey/Question/subquestionsAndAnswers/'.$view, $aData);
        $html .= '<!-- end of Inserted Row -->';
        return $html;
    }


    /**
     * Add a new question
     * @param $surveyid int the sid
     * @return string html
     */
    public function newquestion($surveyid)
    {
        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }

        Yii::app()->loadHelper('admin/htmleditor');
        $aData = [];
        $surveyid = $iSurveyID = $aData['surveyid'] = sanitize_int($surveyid);
        App()->getClientScript()->registerPackage('qTip2');
        $survey = Survey::model()->findByPk($iSurveyID);

        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";
        $aData['subaction'] = gT('Add a new question');
        $aData['surveybar']['importquestion'] = true;
        $aData['surveybar']['savebutton']['form'] = 'frmeditgroup';
        $aData['surveybar']['saveandclosebutton']['form'] = 'frmeditgroup';
        $aData['surveybar']['closebutton']['url'] = '/admin/survey/sa/listquestions/surveyid/'.$iSurveyID; // Close button

        $this->abortIfSurveyIsActive($survey);

        Yii::app()->session['FileManagerContext'] = "create:question:{$surveyid}";

        $baselang = Survey::model()->findByPk($surveyid)->language;

        $qtypelist = getQuestionTypeList('', 'array');

        $aData['ajaxDatas']['qTypeOutput'] = json_encode($qtypelist);

        $eqrow = [];
        $eqrow['language'] = $baselang;
        $eqrow['title'] = '';
        $eqrow['question'] = '';
        $eqrow['help'] = '';
        $eqrow['lid'] = 0;
        $eqrow['lid1'] = 0;
        $eqrow['gid'] = null;
        $eqrow['other'] = 'N';
        $eqrow['mandatory'] = 'N';
        $eqrow['preg'] = '';
        $eqrow['relevance'] = 1;
        $eqrow['group_name'] = '';
        $eqrow['modulename'] = '';
        $eqrow['conditions_number'] = false;
        $eqrow['type'] = 'T';
        
        if (isset($_GET['gid'])) {
            $eqrow['gid'] = $_GET['gid'];
        }
        $aData['eqrow'] = $eqrow;
        $aData['groupid'] = $eqrow['gid'];
        $qid = null;
        $aData['aQuestionTemplateAttributes'] = Question::model()->getAdvancedSettingsWithValues($qid, $eqrow['type'], $surveyid)['question_template'];
        $aData['aQuestionTemplateList'] = \QuestionTemplate::getQuestionTemplateList($eqrow['type']);

        $sumresult1 = Survey::model()->findByPk($surveyid);
        if (is_null($sumresult1)) {
            $this->getController()->error('Invalid Survey ID');
        }

        // $surveyinfo = $sumresult1->attributes;
        // $surveyinfo = array_map('flattenText', $surveyinfo);
        $aData['activated'] = $sumresult1->active;

        // Prepare selector Class for javascript function
        if (Yii::app()->session['questionselectormode'] !== 'default') {
            $selectormodeclass = Yii::app()->session['questionselectormode'];
        } else {
            $selectormodeclass = getGlobalSetting('defaultquestionselectormode');
        }

        $aData['accordionDatas']['selectormodeclass'] = $selectormodeclass;
        $aData['selectormodeclass'] = $selectormodeclass;
        $aData['ajaxDatas']['selectormodeclass'] = $selectormodeclass;


        $aData['accordionDatas']['eqrow'] = $eqrow;
        $aData['ajaxDatas']['sValidateUrl'] = $this->getController()->createUrl('admin/questions', array('sa' => 'ajaxValidate', 'surveyid'=>$surveyid));
        $aData['addlanguages'] = Survey::model()->findByPk($surveyid)->additionalLanguages;

        // Get the questions for this group, for position
        // NB: gid won't be set if user clicks quick-button Add question
        if (isset($_GET['gid'])) {
            $oQuestionGroup = QuestionGroup::model()->find('gid=:gid', array(':gid'=>$_GET['gid']));
        } else {
            $aData['oqresult'] = array();
            $oQuestionGroup = QuestionGroup::model()->find(array('condition'=>'sid=:sid', 'params'=> array(':sid'=>$surveyid), 'order'=>'group_order'));
        }
        $aData['oQuestionGroup'] = $oQuestionGroup;
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'questions.js', LSYii_ClientScript::POS_BEGIN);

        $aData['adding'] = true;
        $aData['copying'] = false;

        $aData['aqresult'] = '';
        $aData['action'] = 'newquestion';

        ///////////
        // sidemenu
        ///////////
        // sidemenu
        $aData['sidemenu']['state'] = false;
        $aData['sidemenu']['explorer']['state'] = true;

        $aViewUrls = [];
        $aViewUrls['editQuestion_view'][] = $aData;
        App()->getClientScript()->registerScript("EditQuestionView_question_jsviews_".$surveyid.$eqrow['gid'].'new', "OtherSelection('".$eqrow['type']."');", LSYii_ClientScript::POS_POSTSCRIPT);            
        


        $this->_renderWrappedTemplate('survey/Question', $aViewUrls, $aData);
    }

    /**
     * Load edit/new question screen depending on $action.
     *
     * @access public
     * @param string $sa subaction
     * @param int $surveyid
     * @param int $gid
     * @param int $qid
     * @return void
     */
    public function index($sa, $surveyid, $gid, $qid = null)
    {
        App()->getClientScript()->registerPackage('qTip2');
        $action = $sa;
        $surveyid = $iSurveyID = sanitize_int($surveyid);
        $oSurvey = Survey::model()->findByPk($surveyid);

        $gid = sanitize_int($gid);
        if (isset($qid)) {
                    $qid = sanitize_int($qid);
        }


        $aViewUrls = array();

        $oQuestionGroup = QuestionGroup::model()->find('gid=:gid', array(':gid'=>$gid));
        $aData = [];
        $aData['oQuestionGroup'] = $oQuestionGroup;
        $aData['surveyid'] = $surveyid;
        $aData['gid'] = $gid;
        $aData['qid'] = $qid;
        $aData['display']['menu_bars']['surveysummary'] = 'viewgroup';
        $aData['display']['menu_bars']['gid_action'] = 'addquestion';

        $aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";
        $aData['questiongroupbar']['savebutton']['form'] = 'frmeditgroup';
        $aData['questiongroupbar']['saveandclosebutton']['form'] = 'frmeditgroup';
        $aData['questiongroupbar']['closebutton']['url'] = 'admin/questions/sa/view/surveyid/'.$surveyid.'/gid/'.$gid.'/qid/'.$qid; // Close button

        Yii::app()->session['FileManagerContext'] = "create:question:{$surveyid}";

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'read')) {
            Yii::app()->loadHelper('admin/htmleditor');
            Yii::app()->loadHelper('surveytranslator');

            // if (isset($_POST['sortorder']))
            //     $postsortorder = sanitize_int($_POST['sortorder']);

            $aData['adding'] = $adding = $action == 'newquestion';
            $aData['copying'] = $copying = $action == 'copyquestion';
            $aData['editing'] = $editing = $action == 'editquestion';
            $aData['subaction'] = $copying ? gT('Copy question') : ($editing ? gT('Edit question') : gT('Add a new question'));

            $questlangs = $oSurvey->additionalLanguages;
            $baselang = $oSurvey->language;
            $questlangs[] = $baselang;
            $questlangs = array_flip($questlangs);

            // Prepare selector Mode TODO: with and without image
            if (!$adding) {
                // Abort if user lacks update permission
                if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
                    Yii::app()->user->setFlash('error', gT("Access denied"));
                    $this->getController()->redirect(Yii::app()->request->urlReferrer);
                }

                Yii::app()->session['FileManagerContext'] = "edit:question:{$surveyid}";
                $aData['display']['menu_bars']['qid_action'] = 'editquestion';

                $oQuestion = Question::model()->find('qid=:qid', array(':qid'=>$qid));
                $aData['oQuestion'] = $oQuestion;

                $basesettings = [];
                $egresult = Question::model()->findAllByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'qid' => $qid));
                foreach ($egresult as $esrow) {
                    if (!array_key_exists($esrow->language, $questlangs)) {
                    // Language Exists, BUT ITS NOT ON THE SURVEY ANYMORE.
                        $esrow->delete();
                    } else {
                        $questlangs[$esrow->language] = 99;
                    }

                    if ($esrow->language == $baselang) {
                        $esrow = $esrow->attributes;
                        $basesettings = array(
                        'question_order' => $esrow['question_order'],
                        'other' => $esrow['other'],
                        'mandatory' => $esrow['mandatory'],
                        'type' => $esrow['type'],
                        'title' => $esrow['title'],
                        'preg' => $esrow['preg'],
                        'question' => $esrow['question'],
                        'help' => $esrow['help']
                        );
                    }
                }

                if (!$egresult) {
                    $this->getController()->error('Invalid question id');
                }

                foreach ($questlangs as $key=>$value) {
                    if ($value != 99) {
                        $arQuestion = new Question;
                        $arQuestion->qid = $qid;
                        $arQuestion->sid = $surveyid;
                        $arQuestion->gid = $gid;
                        $arQuestion->type = $basesettings['type'];
                        $arQuestion->title = $basesettings['title'];
                        $arQuestion->question = $basesettings['question'];
                        $arQuestion->preg = $basesettings['preg'];
                        $arQuestion->help = $basesettings['help'];
                        $arQuestion->other = $basesettings['other'];
                        $arQuestion->mandatory = $basesettings['mandatory'];
                        $arQuestion->question_order = $basesettings['question_order'];
                        $arQuestion->language = $key;
                        $arQuestion->insert();
                    }
                }

                $eqresult = Question::model()->with('groups')->together()->findByAttributes(array(
                    'sid' => $surveyid,
                    'gid' => $gid,
                    'qid' => $qid,
                    'language' => $baselang
                ));
            } else {
                // This is needed to properly color-code content if it contains replacements
                LimeExpressionManager::StartProcessingPage(false, true); // so can click on syntax highlighting to edit questions
            }
            $qtypelist = getQuestionTypeList('', 'array');
            $aData['qTypeOutput'] = json_encode($qtypelist);

            $eqrow = [];
            if (!$adding) {
                if (is_object($eqresult->groups)) {
                                    $eqrow = array_merge($eqresult->attributes, $eqresult->groups->attributes);
                } else {
                                    $eqrow = $eqresult->attributes;
                }

                // Todo: handler in case that record is not found
                if ($copying) {
                                    $eqrow['title'] = '';
                }
            } else {
                $eqrow['language'] = $baselang;
                $eqrow['title'] = '';
                $eqrow['question'] = '';
                $eqrow['help'] = '';
                $eqrow['type'] = 'T';
                $eqrow['lid'] = 0;
                $eqrow['lid1'] = 0;
                $eqrow['gid'] = $gid;
                $eqrow['other'] = 'N';
                $eqrow['mandatory'] = 'N';
                $eqrow['preg'] = '';
                $eqrow['relevance'] = 1;
                $eqrow['group_name'] = '';
            }
            $eqrow['conditions_number'] = Condition::Model()->count("qid=:qid", array('qid' => $qid));


            $aData['eqrow'] = $eqrow;
            $aData['surveyid'] = $surveyid;
            $aData['gid'] = $gid;
            $questionTemplateAttributes = Question::model()->getAdvancedSettingsWithValues($qid, $eqrow['type'], $surveyid);
            if (!empty($questionTemplateAttributes['question_template'])){
                $aData['aQuestionTemplateAttributes'] = $questionTemplateAttributes['question_template'];
            } else {
                $aData['aQuestionTemplateAttributes']['value'] = 'core';
            }

            $aData['aQuestionTemplateList'] = \QuestionTemplate::getQuestionTemplateList($eqrow['type']);

            if (!$adding) {
                $criteria = new CDbCriteria;
                $criteria->addColumnCondition(array('sid' => $surveyid, 'gid' => $gid, 'qid' => $qid));
                $criteria->params[':lang'] = $baselang;
                $criteria->addCondition('language != :lang');
                $aqresult = Question::model()->findAll($criteria);
                $aData['aqresult'] = $aqresult;
            }

            $aData['action'] = $action;

            $arSurveyInfo = Survey::model()->findByPk($surveyid);
            if (is_null($arSurveyInfo)) {
                $this->getController()->error('Invalid Survey ID');
            }

            $aData['activated'] = $arSurveyInfo->active;
            
            // Prepare selector Class for javascript function
            if (Yii::app()->session['questionselectormode'] !== 'default') {
                $selectormodeclass = Yii::app()->session['questionselectormode'];
            } else {
                $selectormodeclass = getGlobalSetting('defaultquestionselectormode');
            }

            $aData['selectormodeclass'] = $selectormodeclass;
            $aData['ajaxDatas']['selectormodeclass'] = $selectormodeclass;

            /**
             * Since is moved via ajax call only : it's not needed, when we have time : readd it for no-js solution
             */
            //~ if (!$adding)
                //~ $qattributes = \LimeSurvey\Helpers\questionHelper::getQuestionAttributesSettings(($aqresult->type); //(or Question::getAdvancedSettingsWithValues )
            //~ else
                //~ $qattributes = array();

            if ($adding) {
                // Get the questions for this group
                $baselang = $arSurveyInfo->language;
                $oqresult = Question::model()->findAllByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'language' => $baselang, 'parent_qid'=> 0), array('order' => 'question_order'));
                $aData['oqresult'] = $oqresult;
            }
            App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'questions.js', LSYii_ClientScript::POS_BEGIN);

            $aData['sValidateUrl'] = ($adding || $copying) ? $this->getController()->createUrl('admin/questions', array('sa' => 'ajaxValidate', 'surveyid'=>$surveyid)) : $this->getController()->createUrl('admin/questions', array('sa' => 'ajaxValidate', 'surveyid'=>$surveyid, 'qid'=>$qid));
            $aData['ajaxDatas']['sValidateUrl'] = $aData['sValidateUrl'];
            $aData['ajaxDatas']['qTypeOutput'] = $aData['qTypeOutput'];
            
            $aData['addlanguages'] = Survey::model()->findByPk($surveyid)->additionalLanguages;

            $aViewUrls['editQuestion_view'][] = $aData;
            App()->getClientScript()->registerScript("EditQuestionView_question_jsviews_".$surveyid.$gid.$qid, "OtherSelection('".$eqrow['type']."');", LSYii_ClientScript::POS_POSTSCRIPT);            
        } else {
                    include('accessDenied.php');
        }

        
        $aData['ajaxDatas']['qTypeOutput'] = $aData['qTypeOutput'];

        ///////////
        // sidemenu
        $aData['sidemenu']['state'] = false;
        $aData['sidemenu']['explorer']['state'] = true;
        $aData['sidemenu']['explorer']['gid'] = (isset($gid)) ? $gid : false;
        $aData['sidemenu']['explorer']['qid'] = (isset($qid)) ? $qid : false;


        $this->_renderWrappedTemplate('survey/Question', $aViewUrls, $aData);
    }


    /**
     * Delete multiple questions.
     * Called by ajax from question list.
     * Permission check is done by questions::delete()
     * @return HTML
     */
    public function deleteMultiple()
    {
        $aQidsAndLang = json_decode(Yii::app()->request->getPost('sItems'));
        $aResults     = array();

        foreach ($aQidsAndLang as $sQidAndLang) {
            $aQidAndLang = explode(',', $sQidAndLang);
            $iQid        = $aQidAndLang[0];
            $sLanguage   = $aQidAndLang[1];

            $oQuestion   = Question::model()->find('qid=:qid and language=:language', array(":qid"=>$iQid, ":language"=>$sLanguage));

            if (is_object($oQuestion)) {
                $aResults[$iQid]['title'] = viewHelper::flatEllipsizeText($oQuestion->question, true, 0);
                $result = $this->delete($oQuestion->sid, $iQid, true);
                $aResults[$iQid]['result'] = $result['status'];
            }
        }

        Yii::app()->getController()->renderPartial('ext.admin.survey.ListSurveysWidget.views.massive_actions._action_results', array('aResults'=>$aResults, 'successLabel'=>gT('Deleted')));
    }

    /**
     * Function responsible for deleting a question.
     *
     * @access public
     * @param int $surveyid
     * @param int $qid
     * @return array
     */
    public function delete($surveyid=null, $qid=null, $ajax = false, $gid = 0)
    {
        if(is_null($qid)) {
            $qid = Yii::app()->getRequest()->getPost('qid');
        }
        $oQuestion = Question::model()->find("qid = :qid",array(":qid"=>$qid));
        if(empty($oQuestion)) {
            throw new CHttpException(404, gT("Invalid question id"));
        }
        /* Test the surveyid from question, not from submitted value */
        $surveyid = $oQuestion->sid;
        if(!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'delete')) {
            throw new CHttpException(403, gT("You are not authorized to delete questions."));
        }
        if(!Yii::app()->getRequest()->isPostRequest) {
            throw new CHttpException(405, gT("Invalid action"));
        }

        $rqid = $qid; // Used for last qid seen
        $gid_search = sanitize_int($gid); // gid from search filter
        if ($gid_search == 0){
            $gid_search = null;
        }

        LimeExpressionManager::RevertUpgradeConditionsToRelevance(null, $qid);

        // Check if any other questions have conditions which rely on this question. Don't delete if there are.
        // TMSW Condition->Relevance:  Allow such deletes - can warn about missing relevance separately.
        $ccresult = Condition::model()->findAllByAttributes(array('cqid' => $qid));
        $cccount = count($ccresult);

        // There are conditions dependent on this question
        if ($cccount) {
            $sMessage = gT("Question could not be deleted. There are conditions for other questions that rely on this question. You cannot delete this question until those conditions are removed.");
            if (!$ajax) {
                Yii::app()->setFlashMessage($sMessage, 'error');
                $this->getController()->redirect(array('admin/survey/sa/listquestions/surveyid/'.$surveyid));
            } else {
                return array('status'=>false, 'message'=>$sMessage);
            }
        } else {
            Question::model()->deleteAllById($qid);
            Question::model()->updateQuestionOrder($oQuestion->gid);
        }

        $sMessage = gT("Question was successfully deleted.");

        // remove question from lastVisited
        $oCriteria = new CDbCriteria();
        $oCriteria->compare('stg_name', 'last_question_%', true, 'AND', false);
        $oCriteria->compare('stg_value', $rqid, false, 'AND');
        SettingGlobal::model()->deleteAll($oCriteria);
        $redirectUrl = array('admin/survey/sa/listquestions/', 'surveyid' => $surveyid, 'gid' => $gid_search);
        if (!$ajax) {
            Yii::app()->session['flashmessage'] = $sMessage;
            $this->getController()->redirect($redirectUrl);
        } else {
            return array('status'=>true, 'message'=>$sMessage);
        }
    }


    /// TODO: refactore multiple function to call the model, and then push all the common stuff to a model function for a dry code

    /**
     * Change the question group/order position of multiple questions
     *
     */
    public function setMultipleQuestionGroup()
    {
        $aQidsAndLang   = json_decode(Yii::app()->request->getPost('sItems')); // List of question ids to update
        $iGid           = Yii::app()->request->getPost('group_gid'); // New Group ID  (can be same group for a simple position change)
        $iQuestionOrder = Yii::app()->request->getPost('questionposition'); // Wanted position

        $oQuestionGroup = QuestionGroup::model()->find('gid=:gid', array(':gid'=>$iGid)); // The New Group object
        $oSurvey        = $oQuestionGroup->survey; // The Survey associated with this group

        if (Permission::model()->hasSurveyPermission($oSurvey->sid, 'surveycontent', 'update')) {
            if ($oSurvey->active == 'N') {
// If survey is active it should not be possible to update
                if ($iQuestionOrder == "") {
// If asked "at the endd"
                    $iQuestionOrder = (getMaxQuestionOrder($oQuestionGroup->gid, $oSurvey->sid));

                    // We get the last question order, so we want the number just after it
                    // Unless it's 0
                    if ($iQuestionOrder > 0) {
                        $iQuestionOrder++;
                    }

                }

                // Now, we push each question to the new question group
                // And update positions
                foreach ($aQidsAndLang as $sQidAndLang) {
                    // Question basic infos
                    $aQidAndLang = explode(',', $sQidAndLang);
                    $iQid        = $aQidAndLang[0];

                    $oQuestion = Question::model()->findByAttributes(array('qid' => $iQid)); // Question object
                    $oldGid    = $oQuestion->gid; // The current GID of the question
                    $oldOrder  = $oQuestion->question_order; // Its current order

                    // First, we update all the positions of the questions in the current group of the question
                    // If they were after the question, we must decrease by one their position
                    $sQuery = "UPDATE {{questions}} SET question_order=question_order-1 WHERE gid=:gid AND question_order >= :order";
                    Yii::app()->db->createCommand($sQuery)->bindValues(array(':gid'=>$oldGid, ':order'=>$oldOrder))->query();

                    // Then, we must update all the position of the question in the new group of the question
                    // If they will be after the question, we must increase their position
                    $sQuery = "UPDATE {{questions}} SET question_order=question_order+1 WHERE gid=:gid AND question_order >= :order";
                    Yii::app()->db->createCommand($sQuery)->bindValues(array(':gid'=>$oQuestionGroup->gid, ':order'=>$iQuestionOrder))->query();

                    // Then we move all the questions with the request QID (same question in different langagues) to the new group, with the righ postion
                    Question::model()->updateAll(array('question_order' => $iQuestionOrder, 'gid' => $oQuestionGroup->gid), 'qid=:qid', array(':qid' => $iQid));
                    // Then we update its subquestions
                    Question::model()->updateAll(array('gid' => $oQuestionGroup->gid), 'parent_qid=:parent_qid', array(':parent_qid' => $iQid));

                    $iQuestionOrder++;
                }
            }
        }
    }


    public function setMultipleMandatory()
    {
        $aQidsAndLang   = json_decode($_POST['sItems']); // List of question ids to update
        $iSid           = Yii::app()->request->getPost('sid');
        $bMandatory     = (Yii::app()->request->getPost('mandatory') === 'true') ? 'Y' : 'N';

        if (Permission::model()->hasSurveyPermission($iSid, 'surveycontent', 'update')) {
            $oSurvey          = Survey::model()->findByPk($iSid);
            $aSurveyLanguages = $oSurvey->additionalLanguages;
            $sBaseLanguage    = $oSurvey->language;

            array_push($aSurveyLanguages, $sBaseLanguage);

            foreach ($aQidsAndLang as $sQidAndLang) {
                $aQidAndLang = explode(',', $sQidAndLang);
                $iQid        = $aQidAndLang[0];

                foreach ($aSurveyLanguages as $sAdditionalLanguage) {
                    $oQuestion = Question::model()->findByPk(array("qid"=>$iQid, 'language'=>$sAdditionalLanguage));

                    // These are the questions types that have no mandatory property - so zap it accordingly
                    if ($oQuestion->type != "X" && $oQuestion->type != "|") {
                        $oQuestion->mandatory = $bMandatory;
                        $oQuestion->save();
                    }
                }
            }
        }
    }

    public function setMultipleOther()
    {
        $aQidsAndLang   = json_decode($_POST['sItems']); // List of question ids to update
        $iSid           = $_POST['sid'];
        $bOther = (Yii::app()->request->getPost('other') === 'true') ? 'Y' : 'N';

        if (Permission::model()->hasSurveyPermission($iSid, 'surveycontent', 'update')) {
            $oSurvey          = Survey::model()->findByPk($iSid);
            $aSurveyLanguages = $oSurvey->additionalLanguages;
            $sBaseLanguage    = $oSurvey->language;

            array_push($aSurveyLanguages, $sBaseLanguage);

            foreach ($aQidsAndLang as $sQidAndLang) {
                $aQidAndLang = explode(',', $sQidAndLang);
                $iQid        = $aQidAndLang[0];

                foreach ($aSurveyLanguages as $sAdditionalLanguage) {
                    $oQuestion = Question::model()->findByPk(array("qid"=>$iQid, 'language'=>$sAdditionalLanguage));

                    // These are the questions types that have the other option therefore we set everything else to 'No Other'
                    if (($oQuestion->type == "L") || ($oQuestion->type == "!") || ($oQuestion->type == "P") || ($oQuestion->type == "M")) {
                        $oQuestion->other = $bOther;
                        $oQuestion->save();
                    }

                }
            }
        }
    }


    /**
     * Set attributes for multiple questions
     */
    public function setMultipleAttributes()
    {
        $aQidsAndLang        = json_decode($_POST['sItems']); // List of question ids to update
        $iSid                = Yii::app()->request->getPost('sid'); // The survey (for permission check)
        $aAttributesToUpdate = json_decode($_POST['aAttributesToUpdate']); // The list of attributes to updates
        // TODO: this should be get from the question model
        $aValidQuestionTypes = str_split($_POST['aValidQuestionTypes']); // The valid question types for thoses attributes

        // Calling th model
        QuestionAttribute::model()->setMultiple($iSid, $aQidsAndLang, $aAttributesToUpdate, $aValidQuestionTypes);
    }



    public function ajaxReloadPositionWidget($gid, $classes = '')
    {
        $oQuestionGroup = QuestionGroup::model()->find('gid=:gid', array(':gid'=>$gid));
        if (is_a($oQuestionGroup, 'QuestionGroup') && Permission::model()->hasSurveyPermission($oQuestionGroup->sid, 'surveycontent', 'read')) {
            $aOptions = array(
                        'display'           => 'form_group',
                        'oQuestionGroup'    => $oQuestionGroup,

            );

            // TODO: Better solution: Hard-code allowed CSS classes.
            if ($classes != '' && $this->isValidCSSClass($classes)) {
                $aOptions['classes'] = $classes;
            }

            return App()->getController()->widget('ext.admin.survey.question.PositionWidget.PositionWidget', $aOptions);
        }
    }

    // private function getQuestionAttribute($type, $qid=0){
    //
    // }

    /**
     * This function prepares the data for the advanced question attributes view
     *
     * @access public
     * @return void
     */
    public function ajaxquestionattributes()
    {

        $surveyid           = (int) Yii::app()->request->getParam('sid', 0);
        $qid                = (int) Yii::app()->request->getParam('qid', 0);
        $type               = Yii::app()->request->getParam('question_type');
        $sQuestionTemplate  = Yii::app()->request->getParam('question_template', '');
        $sOldQuestionTemplate  = Yii::app()->request->getParam('old_question_template', '');
        $oSurvey = Survey::model()->findByPk($surveyid);
        $questionTypeList = QuestionTemplate::getTypeToFolder();

        if ($oSurvey === null) {
            App()->end();
        }

        $aLanguages = array_merge(
            array(Survey::model()->findByPk($surveyid)->language),
            Survey::model()->findByPk($surveyid)->additionalLanguages
            );
        $aAttributesWithValues = Question::model()->getAdvancedSettingsWithValues($qid, $type, $surveyid);

        // get all attributes from old custom question theme and then unset them, only attributes from selected question theme should be visible  
        if (!empty($sOldQuestionTemplate) && $sOldQuestionTemplate !== 'core'){
            // get old custom question theme attributes
            $aOldQuestionThemeAttributes = \LimeSurvey\Helpers\questionHelper::getQuestionThemeAttributeValues($sOldQuestionTemplate, $questionTypeList[$type]);
            if (!empty($aOldQuestionThemeAttributes)){ 
                foreach ($aOldQuestionThemeAttributes as $key => $value) {
                    unset($aAttributesWithValues[$value['name']]);
                }
            }
        }
        // INSERTING CUSTOM ATTRIBUTES FROM CORE QUESTION THEME XML FILE
        if (!empty($sQuestionTemplate) && $sQuestionTemplate !== 'core') {
                $themeAttributes = \LimeSurvey\Helpers\questionHelper::getQuestionThemeAttributeValues($sQuestionTemplate, $questionTypeList[$type]);
                $aAttributesWithValues = array_merge($aAttributesWithValues,$themeAttributes); // theme can update core/plugin attribute
        }
        uasort($aAttributesWithValues, 'categorySort');
        unset($aAttributesWithValues['question_template']);

        $aAttributesPrepared = array();
        foreach ($aAttributesWithValues as $aAttribute) {
            //Set default for a new question
            if(empty($aAttribute['value']) && $qid===0) {
                //Empty strings are arrays because of some reason no one understands.
                $aAttribute['value'] = is_array($aAttribute['default']) ? '' : $aAttribute['default'];
            }

            // SET QUESTION TEMPLATE FORM ATTRIBUTES WHEN $sQuestionTemplate VARIABLE IS SET
            if (!empty($sQuestionTemplate) && isset($aAttribute['name']) && $aAttribute['name'] == 'question_template') {
                $aAttribute['value'] = $sQuestionTemplate;
                $aAttributesPrepared[] = $aAttribute;
            } elseif (isset($aAttribute['i18n']) && $aAttribute['i18n'] == false) {
                $aAttributesPrepared[] = $aAttribute;
            } else {
                foreach ($aLanguages as $sLanguage) {
                    $aAttributeModified = $aAttribute;
                    $aAttributeModified['name'] = $aAttributeModified['name'].'_'.$sLanguage;
                    $aAttributeModified['language'] = $sLanguage;
                    if (isset($aAttributeModified[$sLanguage]['value'])) {
                        $aAttributeModified['value'] = $aAttributeModified[$sLanguage]['value'];
                    } else {
                        $aAttributeModified['value'] = $aAttributeModified['default'];
                    }
                    $aAttributesPrepared[] = $aAttributeModified;
                }
            }
        }

        $aData = [];
        $aData['bIsActive'] = ($oSurvey->active == 'Y');
        $aData['attributedata'] = $aAttributesPrepared;
        $aData['aQuestionTemplates'] = \QuestionTemplate::getQuestionTemplateList($type);

        $this->getController()->renderPartial('/admin/survey/Question/advanced_settings_view', $aData);
    }

    /**
     * This function prepares the data for label set details
     *
     * @access public
     * @return void
     */
    public function ajaxlabelsetdetails($lid)
    {

        $oLabelSet = LabelSet::model()->find('lid=:lid', array(':lid' => $lid)); //$connect->GetArray($query);

        $aResult = [];
        $aLanguages = [];

        if($oLabelSet !== null) {
            $aUsedLanguages = explode(' ', $oLabelSet->languages);
        
            foreach ($aUsedLanguages as $sLanguage) {
                $aResult[$sLanguage] = array_map(
                    function($attribute) { return \viewHelper::flatten($attribute); },
                    $oLabelSet->attributes
                ); 
                foreach ($oLabelSet->labels as $oLabel) {
                    if($oLabel->language === $sLanguage) {
                        $aLabels = $oLabel->attributes;
                        array_walk(
                            $aLabels,
                            function(&$attribute, $key) {
                                $attribute = $key == 'title' ? $attribute : \viewHelper::flatten($attribute); // Don't flatten the label title because it is OK to include HTML
                            }
                        );
                        $aResult[$sLanguage]['labels'][] = $aLabels;
                    }
                };
                $aLanguages[$sLanguage] = getLanguageNameFromCode($sLanguage,false);
            };
        }
        
        $resultdata = ['results' => $aResult, 'languages' => $aLanguages];

        return Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson',
            array(
                'data' => [
                    'success' => count($aResult) > 0,
                    'results' => $aResult, 
                    'languages' => $aLanguages
                ],
            ),
            false,
            false
        );
    }

    /**
     * This function prepares the data for labelset
     *
     * @access public
     * @return void
     */
    public function ajaxlabelsetpicker($sid, $match=0)
    {
        $survey = Survey::model()->findByPk($sid);

        $criteria = new CDbCriteria;
        $language = null;
        if ($match === 1) {
            $criteria->addCondition('languages LIKE :language');
            $criteria->params = [':language' => '%'.$language.'%'];
        }
        
        $resultdata = LabelSet::model()->findAll($criteria);
        // $resultdata = [];
        // create languagespecific array
        $aResults = [];
        foreach ($resultdata as $oResult) {
            $aResults[] = array_map(
                function($attribute) { return \viewHelper::flatten($attribute); },
                $oResult->attributes
            ); 
        }
        
        return Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson',
            array(
                'data' => [
                    'success' => count($aResults) > 0,
                    'labelsets'=> $aResults
                ],
            ),
            false,
            false
        );
    }

    public function ajaxchecklabel()
    {
        $iLabelID = (int) Yii::app()->request->getParam('lid');
        $aNewLanguages = Yii::app()->request->getParam('languages');
        $bCheckAssessments = Yii::app()->request->getParam('bCheckAssessments', 0);
        $arLabelSet = LabelSet::model()->find('lid=:lid', array(':lid' => $iLabelID));
        $iLabelsWithAssessmentValues = Label::model()->count('lid=:lid AND assessment_value<>0', array(':lid' => $iLabelID));
        $aLabelSetLanguages = explode(' ', $arLabelSet->languages);
        $aErrorMessages = array();
        if ($bCheckAssessments && $iLabelsWithAssessmentValues) {
            $aErrorMessages[] = gT('The existing label set has assessment values assigned.').'<strong>'.gT('If you replace the label set the existing asssessment values will be lost.').'</strong>';
        }
        if (count(array_diff($aLabelSetLanguages, $aNewLanguages))) {
            $aErrorMessages[] = gT('The existing label set has different/more languages.').'<strong>'.gT('If you replace the label set these translations will be lost.').'</strong>';
        }
        if (count($aErrorMessages)) {
            foreach ($aErrorMessages as $sErrorMessage) {
                echo  $sErrorMessage.'<br>';
            }
            eT('Do you really want to continue?');
        } else {
            eT('You are about to replace an existing label set with the current answer options.');
            echo '<br>';
            eT('Continue?');
        }
    }

    /**
     * function ajaxValidate
     */
    public function ajaxValidate($surveyid, $qid = false)
    {

        // Stupid hack since Bootstrap switch is a checkbox and 'other' used to be radio button
        // TODO: Longterm, change 'other' to boolean; change the model rules
        $_POST['other'] = (Yii::app()->request->getPost('other') == '1') ? 'Y' : 'N';

        $iSurveyId = (int) $surveyid;
        $iQid = $qid;
        $oSurvey = Survey::model()->findByPk($surveyid);
        if (empty($oSurvey)) {
            Yii::app()->end(); // Or throw error 500
        }
        // Validate only on default language
        if (!$iQid) {
            $oQuestion = new Question('insert');
            $oQuestion->sid = $iSurveyId;
            $oQuestion->language = $oSurvey->language;
        } else {
            $oQuestion = Question::model()->find('qid=:qid and language=:language', array(":qid"=>$iQid, ":language"=>$oSurvey->language));
            if (!$oQuestion) {
                    throw new Exception('Invalid question id.');
            }
        }
        $oQuestion->title = App()->request->getParam('title');
        $oQuestion->other = App()->request->getParam('other');
        $oQuestion->validate();

        header('Content-type: application/json');
        echo CJSON::encode($oQuestion->getErrors());
        Yii::app()->end();
    }
     /**
     * Todo : update whole view to use CActiveForm
     */
#    protected function performAjaxValidation($model)
#    {
#        if(trueYii::app()->request->getPost('ajax')=='user-form')
#        {
#            echo CActiveForm::validate($model);
#            Yii::app()->end();
#        }
#    }    

    /**
     * @param string $question_type
     * @return string JSON data
     */
    public function ajaxGetQuestionTemplateList()
    {
        $type = Yii::app()->request->getParam('type');
        $questionTemplateList = \QuestionTemplate::getQuestionTemplateList($type);
        header('Content-type: application/json');
        echo CJSON::encode($questionTemplateList);
        Yii::app()->end();
    }

    /**
     * Returns true if $class is a valid CSS class (alphanumeric + '-' and '_')
     *
     * @param string $class
     * @return boolean
     */
    protected function isValidCSSClass($class)
    {
        $class = str_replace(['-', '_'], '', $class);
        return ctype_alnum($class);
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'survey/Question', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }

    /**
     * Show error and redirect back if survey is active
     *
     * @param Survey $survey
     * @return void
     */
    protected function abortIfSurveyIsActive($survey)
    {
        if ($survey->active !== 'N') {
            Yii::app()->user->setFlash('error', gT("You can't add questions while the survey is active."));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }
    }
}
