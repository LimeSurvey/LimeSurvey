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
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
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
     * @param int $iSurveyId
     * @return void
     */
    public function index($iSurveyId)
    {
        $iSurveyId = sanitize_int($iSurveyId);
        $action = Yii::app()->request->getPost('action');

        $languages = Survey::model()->findByPk($iSurveyId)->additionalLanguages;
        $surveyLanguage = Survey::model()->findByPk($iSurveyId)->language;

        $_SESSION['FileManagerContext'] = "edit:assessments:{$iSurveyId}";

        array_unshift($languages, $surveyLanguage); // makes an array with ALL the languages supported by the survey -> $assessmentlangs

        Yii::app()->setConfig("baselang", $surveyLanguage);
        Yii::app()->setConfig("assessmentlangs", $languages);

        if ($action == "assessmentadd")
            $this->_add($iSurveyId);
        if ($action == "assessmentupdate")
            $this->_update($iSurveyId);
        if ($action == "assessmentdelete")
             $this->_delete($iSurveyId, $_POST['id']);

        if (bHasSurveyPermission($iSurveyId, 'assessments', 'read')) {
            $clang = $this->getController()->lang;

            if ($iSurveyId == '') {
                show_error($clang->gT("No SID Provided"));
                die();
            }

            $this->_showAssessments($iSurveyId, $action, $surveyLanguage, $clang);
        }

    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($aViewUrls = array(), $aData = array())
    {
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('adminscripts') . 'assessments.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.tablesorter.min.js');

        $aData['display']['menu_bars'] = false;

        parent::_renderWrappedTemplate('', $aViewUrls, $aData);
    }

    private function _showAssessments($iSurveyId, $action, $surveyLanguage, Limesurvey_lang $clang)
    {
        $assessments = Assessment::model()->findAllByAttributes(array('sid' => $iSurveyId));
        $aData = $this->_collectGroupData($iSurveyId);
        $headings = array($clang->gT("Scope"), $clang->gT("Question group"), $clang->gT("Minimum"), $clang->gT("Maximum"));
        $aData['actiontitle'] = $clang->gT("Add");
        $aData['actionvalue'] = "assessmentadd";
        $aData['editId'] = '';

        if ($action == "assessmentedit" && bHasSurveyPermission($iSurveyId, 'assessments', 'update')) {
            $aData = $this->_collectEditData($surveyLanguage, $aData, $clang);
        }

        $surveyinfo = getSurveyInfo($iSurveyId);
        $aData['clang'] = $clang;
        $aData['surveyinfo'] = $surveyinfo;
        $aData['imageurl'] = Yii::app()->getConfig('imageurl');
        $aData['surveyid'] = $iSurveyId;
        $aData['headings'] = $headings;
        $aData['assessments'] = $assessments;
        $aData['assessmentlangs'] = Yii::app()->getConfig("assessmentlangs");
        $aData['baselang'] = $surveyLanguage;
        $aData['action'] = $action;
        $aData['gid'] = empty($_POST['gid']) ? '' : sanitize_int($_POST['gid']);

        Yii::app()->loadHelper('admin/htmleditor');
        $this->_renderWrappedTemplate("assessments_view", $aData);
    }

    private function _collectGroupData($iSurveyId)
    {
        $groups = Groups::model()->findAllByAttributes(array('sid' => $iSurveyId));
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
    private function _add($iSurveyId)
    {
        if (bHasSurveyPermission($iSurveyId, 'assessments', 'create')) {
            $first = true;
            $assessmentId = -1;
            $languages = Yii::app()->getConfig("assessmentlangs");
            foreach ($languages as $language)
            {
                $aData = $this->_getAssessmentPostData($iSurveyId, $language);

                if ($first == false) {
                    $aData['id'] = $assessmentId;
                }
                $assessment = Assessment::insertRecords($aData);
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
    private function _update($iSurveyId)
    {
        if (bHasSurveyPermission($iSurveyId, 'assessments', 'update') && isset($_POST['id'])) {

            $aid = sanitize_int($_POST['id']);
            $languages = Yii::app()->getConfig("assessmentlangs");
            foreach ($languages as $language)
            {
                $aData = $this->_getAssessmentPostData($iSurveyId, $language);
                Assessment::updateAssessment($aid, $language, $aData);
            }
        }
    }

    /**
     * Deletes an assessment.
     */
    private function _delete($iSurveyId, $assessmentId)
    {
        if (bHasSurveyPermission($iSurveyId, 'assessments', 'delete')) {
            Assessment::model()->deleteAllByAttributes(array('id' => $assessmentId));
        }
    }

    private function _getAssessmentPostData($iSurveyId, $language)
    {
        if (!isset($_POST['gid']))
            $_POST['gid'] = 0;

        if (Yii::app()->getConfig('filterxsshtml')) {
            $_POST['name_' . $language] = htmlspecialchars($_POST['name_' . $language]);
            $_POST['assessmentmessage_' . $language] = htmlspecialchars($_POST['assessmentmessage_' . $language]);
        }

        return array(
            'sid' => $iSurveyId,
            'scope' => sanitize_paranoid_string($_POST['scope']),
            'gid' => sanitize_int($_POST['gid']),
            'minimum' => sanitize_paranoid_string($_POST['minimum']),
            'maximum' => sanitize_paranoid_string($_POST['maximum']),
            'name' => $_POST['name_' . $language],
            'language' => $language,
            'message' => $_POST['assessmentmessage_' . $language]
        );
    }
}
