<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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

        if (Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'read'))
        {
            $languages = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
            $surveyLanguage = Survey::model()->findByPk($iSurveyID)->language;

            Yii::app()->session['FileManagerContext'] = "edit:assessments:{$iSurveyID}";

            array_unshift($languages, $surveyLanguage); // makes an array with ALL the languages supported by the survey -> $assessmentlangs

            Yii::app()->setConfig("baselang", $surveyLanguage);
            Yii::app()->setConfig("assessmentlangs", $languages);

            if ($sAction == "assessmentadd")
                $this->_add($iSurveyID);

            if ($sAction == "assessmentupdate")
                $this->_update($iSurveyID);

            if ($sAction == "assessmentdelete")
                $this->_delete($iSurveyID, $_POST['id']);


            $this->_showAssessments($iSurveyID, $sAction, $surveyLanguage);
        }
        else
        {
            Yii::app()->setFlashMessage(gT("You do not have permission to access this page."),'error');
            $this->getController()->redirect(array("admin/"));
        }
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'assessments', $aViewUrls = array(), $aData = array())
    {
        $aData['sidemenu']['state'] = false;
        $iSurveyID=$aData['surveyid'];
        $surveyinfo = Survey::model()->findByPk($iSurveyID)->surveyinfo;
        $aData['surveybar']['closebutton']['url'] = 'admin/survey/sa/view/surveyid/'.$iSurveyID; // Close button
        $aData['title_bar']['title'] = $surveyinfo['surveyls_title']."(".gT("ID").":".$iSurveyID.")";
        $aData['surveybar']['savebutton']['form'] = true;
        $aData['surveybar']['saveandclosebutton']['form'] = true;
        $aData['gid']=null;
        $this->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'assessments.js');
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

    private function _showAssessments($iSurveyID, $action)
    {
        $oCriteria = new CDbCriteria(array('order' => 'id ASC'));
        $oAssessments = Assessment::model()->findAllByAttributes(array('sid' => $iSurveyID), $oCriteria);
        $aData = $this->_collectGroupData($iSurveyID);
        $aHeadings = array(gT("Scope"), gT("Question group"), gT("Minimum"), gT("Maximum"));
        $aData['actiontitle'] = gT("Add");
        $aData['actionvalue'] = "assessmentadd";
        $aData['editId'] = '';

        if ($action == "assessmentedit" && Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'update')) {
            $aData = $this->_collectEditData($aData);
        }
        $oSurvey = Survey::model()->findByPk($iSurveyID);
        $surveyinfo = getSurveyInfo($iSurveyID);
        $aData['surveyinfo'] = $surveyinfo;
        $aData['imageurl'] = Yii::app()->getConfig('adminimageurl');
        $aData['surveyid'] = $iSurveyID;
        $aData['headings'] = $aHeadings;
        $aData['assessments'] = $oAssessments;
        $aData['assessmentlangs'] = Yii::app()->getConfig("assessmentlangs");
        $aData['baselang'] = $surveyinfo['language'];
        $aData['action'] = $action;
        $aData['gid'] = empty($_POST['gid']) ? '' : sanitize_int($_POST['gid']);

        Yii::app()->loadHelper('admin/htmleditor');

        $urls['output'] = '<div class="side-body ' . getSideBodyClass(false) . '">';
        $urls['output'] .= App()->getController()->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'active'=>gT("Assessments")), true, false);
        $urls['output'] .= '<h3>'.gT("Assessments").'</h3>';

        if ($surveyinfo['assessments']!='Y')
        {

            $urls['message'] = array('title' => gT("Assessments mode not activated"), 'message' => sprintf(gT("Assessment mode for this survey is not activated. You can activate it in the %s survey settings %s (tab 'Notification & data management')."),'<a href="'.$this->getController()->createUrl('admin/survey/sa/editlocalsettings/surveyid/'.$iSurveyID).'">','</a>'), 'class'=> 'warningheader');
        }
        $urls['assessments_view'][]= $aData;
        $this->_renderWrappedTemplate('', $urls, $aData);
    }

    private function _collectGroupData($iSurveyID)
    {
        $aData = array();
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
        $oAssessment = Assessment::model()->find("id=:id",array(':id' => App()->request->getParam('id')));
        if(!$oAssessment)
            throw new CHttpException(500);// 404 ?

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
            foreach ($aLanguages as $sLanguage)
            {
                $aData = $this->_getAssessmentPostData($iSurveyID, $sLanguage);

                if ($bFirst == false) {
                    $aData['id'] = $iAssessmentID;
                }
                $assessment = Assessment::model()->insertRecords($aData);
                if ($bFirst == true) {
                    $bFirst = false;
                    $iAssessmentID = $assessment->id;
                }
            }
        }
    }

    /**
     * Updates an assessment. Receives input from POST
     */
    private function _update($iSurveyID)
    {
        if (Permission::model()->hasSurveyPermission($iSurveyID, 'assessments', 'update') && isset($_POST['id'])) {

            $aid = sanitize_int($_POST['id']);
            $languages = Yii::app()->getConfig("assessmentlangs");
            foreach ($languages as $language)
            {
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
        if (!isset($_POST['gid']))
            $_POST['gid'] = 0;

        return array(
            'sid' => $iSurveyID,
            'scope' => sanitize_paranoid_string($_POST['scope']),
            'gid' => sanitize_int($_POST['gid']),
            'minimum' => intval($_POST['minimum']),
            'maximum' => intval($_POST['maximum']),
            'name' => $_POST['name_' . $language],
            'language' => $language,
            'message' => $_POST['assessmentmessage_' . $language]
        );
    }
}
