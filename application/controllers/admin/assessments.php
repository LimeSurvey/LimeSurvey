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
 *	$Id$
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
        $action = Yii::app()->request->getPost('action');

        $languages = Survey::model()->findByPk($iSurveyID)->additionalLanguages;
        $surveyLanguage = Survey::model()->findByPk($iSurveyID)->language;

        Yii::app()->session['FileManagerContext'] = "edit:assessments:{$iSurveyID}";

        array_unshift($languages, $surveyLanguage); // makes an array with ALL the languages supported by the survey -> $assessmentlangs

        Yii::app()->setConfig("baselang", $surveyLanguage);
        Yii::app()->setConfig("assessmentlangs", $languages);

        if ($action == "assessmentadd")
            $this->_add($iSurveyID);
        if ($action == "assessmentupdate")
            $this->_update($iSurveyID);
        if ($action == "assessmentdelete")
             $this->_delete($iSurveyID, $_POST['id']);

        if (hasSurveyPermission($iSurveyID, 'assessments', 'read')) {
            $clang = $this->getController()->lang;

            if ($iSurveyID == '') {
                show_error($clang->gT("No SID Provided"));
                die();
            }

            $this->_showAssessments($iSurveyID, $action, $surveyLanguage, $clang);
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
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . 'assessments.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.tablesorter.min.js');
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('adminstyleurl')."superfish.css");

        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

    private function _showAssessments($iSurveyID, $action, $surveyLanguage, Limesurvey_lang $clang)
    {
        $assessments = Assessment::model()->findAllByAttributes(array('sid' => $iSurveyID));
        $aData = $this->_collectGroupData($iSurveyID);
        $headings = array($clang->gT("Scope"), $clang->gT("Question group"), $clang->gT("Minimum"), $clang->gT("Maximum"));
        $aData['actiontitle'] = $clang->gT("Add");
        $aData['actionvalue'] = "assessmentadd";
        $aData['editId'] = '';

        if ($action == "assessmentedit" && hasSurveyPermission($iSurveyID, 'assessments', 'update')) {
            $aData = $this->_collectEditData($surveyLanguage, $aData, $clang);
        }

        $surveyinfo = getSurveyInfo($iSurveyID);
        $aData['clang'] = $clang;
        $aData['surveyinfo'] = $surveyinfo;
        $aData['imageurl'] = Yii::app()->getConfig('adminimageurl');
        $aData['surveyid'] = $iSurveyID;
        $aData['headings'] = $headings;
        $aData['assessments'] = $assessments;
        $aData['assessmentlangs'] = Yii::app()->getConfig("assessmentlangs");
        $aData['baselang'] = $surveyLanguage;
        $aData['action'] = $action;
        $aData['gid'] = empty($_POST['gid']) ? '' : sanitize_int($_POST['gid']);

        Yii::app()->loadHelper('admin/htmleditor');
        if ($surveyinfo['assessments']!='Y')
            $urls['message'] = array('title' => $clang->gT("Assessments mode not activated"), 'message' => sprintf($clang->gT("Assessment mode for this survey is not activated. You can activate it in the %s survey settings %s (tab 'Notification & data management')."),'<a href="'.$this->getController()->createUrl('admin/survey/sa/editsurveysettings/surveyid/'.$iSurveyID).'">','</a>'), 'class'=> 'warningheader');
        $urls['assessments_view'][]= $aData;
        $this->_renderWrappedTemplate('', $urls, $aData);
    }

    private function _collectGroupData($iSurveyID)
    {
        $aData = array();
        $groups = Groups::model()->findAllByAttributes(array('sid' => $iSurveyID));
        foreach ($groups as $group) {
            $groupId = $group->attributes['gid'];
            $groupName = $group->attributes['group_name'];
            $aData['groups'][$groupId] = $groupName;
        }
        return $aData;
    }

    private function _collectEditData($surveyLanguage, array $aData, Limesurvey_lang $clang)
    {
        $assessments = Assessment::model()->findAllByAttributes(array('id' => sanitize_int($_POST['id']), 'language' => $surveyLanguage));

        foreach ($assessments as $assessment) {
            $editData = $assessment->attributes;
        }
        $aData['actiontitle'] = $clang->gT("Edit");
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
        if (hasSurveyPermission($iSurveyID, 'assessments', 'create')) {
            $first = true;
            $assessmentId = -1;
            $languages = Yii::app()->getConfig("assessmentlangs");
            foreach ($languages as $language)
            {
                $aData = $this->_getAssessmentPostData($iSurveyID, $language);

                if ($first == false) {
                    $aData['id'] = $assessmentId;
                }
                $assessment = Assessment::model()->insertRecords($aData);
                if ($first == true) {
                    $first = false;
                    $assessmentId = $assessment->id;
                }
            }
        }
    }

    /**
     * Updates an assessment. Receives input from POST
     */
    private function _update($iSurveyID)
    {
        if (hasSurveyPermission($iSurveyID, 'assessments', 'update') && isset($_POST['id'])) {

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
        if (hasSurveyPermission($iSurveyID, 'assessments', 'delete')) {
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
