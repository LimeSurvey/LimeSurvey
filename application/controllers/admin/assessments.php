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
* Assessments Controller
 *
 * This controller performs assessments actions
 *
 * @package        LimeSurvey
 * @subpackage    Backend
 */
class Assessments extends Survey_Common_Action
{

    /**
     * Routes to the correct sub-action
     *
     * @access public
     * @param int $iSurveyID
     * @return void
     */
    public function index($iSurveyID)
    {
        $iSurveyID = sanitize_int($iSurveyID);
        $sAction = Yii::app()->request->getParam('action');
        if (Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'read')) {
            $languages = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
            $surveyLanguage = Survey::model()->findByPk($iSurveyID)->language;

            Yii::app()->session['FileManagerContext'] = "edit:assessments:{$iSurveyID}";

            array_unshift($languages, $surveyLanguage); // makes an array with ALL the languages supported by the survey -> $assessmentlangs

            Yii::app()->setConfig("baselang", $surveyLanguage);
            Yii::app()->setConfig("assessmentlangs", $languages);

            if ($sAction == "assessmentadd") {
                            $this->_add($iSurveyID);
            }

            if ($sAction == "assessmentupdate") {
                            $this->_update($iSurveyID);
            }

            if ($sAction == "assessmentopenedit") {
                            $this->_edit($iSurveyID);
            }

            if ($sAction == "assessmentdelete") {
                            $this->_delete($iSurveyID, $_POST['id']);
            }

            if ($sAction == "asessementactivate") {
                            $this->_activateAsessement($iSurveyID);
            }


            $this->_showAssessments($iSurveyID, $sAction);
        } else {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."), 'error');
            $this->getController()->redirect(array("admin/"));
        }
    }


    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param array|string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'assessments', $aViewUrls = array(), $aData = array())
    {
        $aData['sidemenu']['state'] = false;
        $iSurveyID = $aData['surveyid'];
        $survey = Survey::model()->findByPk($iSurveyID);
        $aData['surveybar']['closebutton']['url'] = 'admin/survey/sa/view/surveyid/'.$iSurveyID; // Close button
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";
        $aData['surveybar']['savebutton']['form'] = true;
        $aData['surveybar']['saveandclosebutton']['form'] = true;
        $aData['gid'] = null;
        App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'assessments.js', LSYii_ClientScript::POS_BEGIN);
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

    private function _prepareDataArray(&$aData, $collectEdit = false)
    {
        $iSurveyID = $aData['surveyid'];
        
        $aHeadings = array(gT("Scope"), gT("Question group"), gT("Minimum"), gT("Maximum"));
        $aData['headings'] = $aHeadings;
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $oAssessments = Assessment::model();
        $oAssessments->sid = $iSurveyID;
        $this->_collectGroupData($iSurveyID, $aData);
        
        $aData['model'] = $oAssessments;
        $aData['pageSizeAsessements'] = Yii::app()->user->getState('pageSizeAsessements', Yii::app()->params['defaultPageSize']);
        $aData['actiontitle'] = gT("Add");
        $aData['actionvalue'] = "assessmentadd";
        $aData['editId'] = '';

        if ($collectEdit === true) {
            $aData = $this->_collectEditData($aData);
        }

        $aData['imageurl'] = Yii::app()->getConfig('adminimageurl');
        $aData['assessments'] = $oAssessments;
        $aData['assessmentlangs'] = Yii::app()->getConfig("assessmentlangs");
        $aData['baselang'] = $oSurvey->language;
        $aData['subaction'] = gT("Assessments");
        $aData['gid'] = App()->request->getPost('gid', '');
        return $aData;
    }

    public function _edit($surveyid)
    {
        $iAsessementId = App()->request->getParam('id');
        $oAssessments = Assessment::model()->findAll("id=:id", [':id' => $iAsessementId]);
        if ($oAssessments !== null && Permission::model()->hasSurveyPermission($surveyid, 'assessments', 'update')) {
            $aData = [];
            $aData['editData'] = $oAssessments[0]->attributes;
            foreach ($oAssessments as $oAssessment) {
                $aData['models'][] = $oAssessment;
                $aData['editData']['name_'.$oAssessment->language] = $oAssessment->name;
                $aData['editData']['assessmentmessage_'.$oAssessment->language] = $oAssessment->message;
            }
            $action = 'assessmentedit';
            $aData['action'] = $action;

            Yii::app()->getController()->renderPartial('/admin/super/_renderJson', ['data' => $aData]);
        }     
    }

    private function _showAssessments($iSurveyID, $action)
    {
        $oSurvey = Survey::model()->findByPk($iSurveyID);

        $aData = [];
        $aData['surveyid'] = $iSurveyID;
        $aData['action'] = $action;
        
        Yii::app()->loadHelper('admin/htmleditor');

        $this->_prepareDataArray($aData);

        $aData['asessementNotActivated'] = false;
        if ($oSurvey->assessments != 'Y') {
            $aData['asessementNotActivated'] = array(
                'title' => gT("Assessments mode not activated"), 
                'message' => gT("Assessment mode for this survey is not activated.").'<br/>'
                    . gt("If you want to activate it click here:").'<br/>'
                    . '<a type="submit" class="btn btn-primary" href="'
                    . App()->getController()->createUrl('admin/assessments', ['action'=> 'asessementactivate', 'surveyid'=> $iSurveyID])
                    .'">'.gT('Activate assessements').'</a>', 
                'class'=> 'warningheader col-sm-12 col-md-6 col-md-offset-3');
        }
        $urls = [];
        $urls['assessments']['assessments_view'][] = $aData;
        
        $this->_renderWrappedTemplate('', 'assessments/assessments_view', $aData);
    }

    private function _activateAsessement($iSurveyID)
    {
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $oSurvey->assessments = "Y";
        $oSurvey->save();
        return ['success' => true];
    }

    private function _collectGroupData($iSurveyID, &$aData = array())
    {
        //$aData = array();
        $groups = QuestionGroup::model()->findAllByAttributes(array('sid' => $iSurveyID));
        foreach ($groups as $group) {
            $groupId = $group->attributes['gid'];
            $groupName = $group->attributes['group_name'];
            $aData['groups'][$groupId] = $groupName;
        }
        return $aData;
    }

    private function _collectEditData(array $aData)
    {
        $oAssessment = Assessment::model()->find("id=:id", array(':id' => App()->request->getParam('id')));
        if (!$oAssessment) {
                    throw new CHttpException(500);
        }
        // 404 ?

        $editData = $oAssessment->attributes;
        $aData['actiontitle'] = gT("Edit");
        $aData['actionvalue'] = "assessmentupdate";
        $aData['editId'] = $editData['id'];
        $aData['editdata'] = $editData;
        return $aData;
    }

    /**
     * Inserts an assessment to the database. Receives input from POST
     */
    private function _add($iSurveyID)
    {
        if (Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'create')) {
            $bFirst = true;
            $iAssessmentID = -1;
            $aLanguages = Yii::app()->getConfig("assessmentlangs");
            foreach ($aLanguages as $sLanguage) {
                $aData = $this->_getAssessmentPostData($iSurveyID, $sLanguage);

                if ($bFirst === false) {
                    $aData['id'] = $iAssessmentID;
                }
                $assessment = Assessment::model()->insertRecords($aData);
                if ($bFirst === true) {
                    $bFirst = false;
                    $iAssessmentID = $assessment->id;
                }
            }
        }
        App()->getController()->refresh();
    }

    /**
     * Updates an assessment. Receives input from POST
     */
    private function _update($iSurveyID)
    {
        if (Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'update') && App()->request->getPost('id', null) != null) {

            $aid = App()->request->getPost('id', null);
            $languages = Yii::app()->getConfig("assessmentlangs");
            foreach ($languages as $language) {
                $aData = $this->_getAssessmentPostData($iSurveyID, $language);
                Assessment::model()->updateAssessment($aid, $iSurveyID, $language, $aData);
            }
        }
    }

    /**
     * Deletes an assessment.
     */
    private function _delete($iSurveyID, $assessmentId)
    {
        if (Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'delete')) {
            Assessment::model()->deleteAllByAttributes(array('id' => $assessmentId, 'sid' => $iSurveyID));
        }
    }

    private function _getAssessmentPostData($iSurveyID, $language)
    {
        if (!isset($_POST['gid'])) {
                    $_POST['gid'] = 0;
        }

        return array(
            'sid' => $iSurveyID,
            'scope' => sanitize_paranoid_string(App()->request->getPost('scope')),
            'gid' => App()->request->getPost('gid'),
            'minimum' => (int) App()->request->getPost('minimum', 0),
            'maximum' => (int) App()->request->getPost('maximum', 0),
            'name' => App()->request->getPost('name_'.$language),
            'language' => $language,
            'message' => App()->request->getPost('assessmentmessage_'.$language)
        );
    }
}
