<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* $Id: assessments.php 11259 2011-10-25 17:06:26Z c_schmitz $
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
     * @param int $surveyId
     * @return void
     */
    public function run($surveyId)
    {
        $surveyId = sanitize_int($surveyId);
        $action = !empty($_POST['action']) ? $_POST['action'] : '';

        $languages = GetAdditionalLanguagesFromSurveyID($surveyId);
        $surveyLanguage = GetBaseLanguageFromSurveyID($surveyId);

        array_unshift($languages, $surveyLanguage); // makes an array with ALL the languages supported by the survey -> $assessmentlangs

        Yii::app()->setConfig("baselang", $surveyLanguage);
        Yii::app()->setConfig("assessmentlangs", $languages);

        if ($action == "assessmentadd")
            $this->_add($surveyId);
        if ($action == "assessmentupdate")
            $this->_update($surveyId);
        if ($action == "assessmentdelete")
             $this->_delete($surveyId, $_POST['id']);

        if (bHasSurveyPermission($surveyId, 'assessments', 'read')) {
            $clang = $this->getController()->lang;

            if ($surveyId == '') {
                show_error($clang->gT("No SID Provided"));
                die();
            }

            $this->_showAssessments($surveyId, $action, $surveyLanguage, $clang);
        }

    }

    private function _showAssessments($surveyId, $action, $surveyLanguage, Limesurvey_lang $clang)
    {
        $assessments = Assessment::model()->findAllByAttributes(array('sid' => $surveyId));
        $data = $this->_collectGroupData($surveyId);
        $headings = array($clang->gT("Scope"), $clang->gT("Question group"), $clang->gT("Minimum"), $clang->gT("Maximum"));
        $data['actiontitle'] = $clang->gT("Add");
        $data['actionvalue'] = "assessmentadd";
        $data['editId'] = '';

        if ($action == "assessmentedit" && bHasSurveyPermission($surveyId, 'assessments', 'update')) {
            $data = $this->_collectEditData($surveyLanguage, $data, $clang);
        }

        $surveyinfo = getSurveyInfo($surveyId);
        $data['clang'] = $clang;
        $data['surveyinfo'] = $surveyinfo;
        $data['imageurl'] = Yii::app()->getConfig('imageurl');
        $data['surveyid'] = $surveyId;
        $data['headings'] = $headings;
        $data['assessments'] = $assessments;
        $data['assessmentlangs'] = Yii::app()->getConfig("assessmentlangs");
        $data['baselang'] = $surveyLanguage;
        $data['action'] = $action;
        $data['gid'] = empty($_POST['gid']) ? '' : sanitize_int($_POST['gid']);

        Yii::app()->loadHelper('admin/htmleditor');
        $this->_renderHeaderAndFooter("/admin/assessments_view", $data);
    }

    private function _collectGroupData($surveyId)
    {
        $groups = Groups::model()->findAllByAttributes(array('sid' => $surveyId));
        foreach ($groups as $group) {
            $groupId = $group->attributes['gid'];
            $groupName = $group->attributes['group_name'];
            $data['groups'][$groupId] = $groupName;
        }
        return $data;
    }

    private function _collectEditData($surveyLanguage, array $data, Limesurvey_lang $clang)
    {
        $assessments = Assessment::model()->findAllByAttributes(array('id' => sanitize_int($_POST['id']), 'language' => $surveyLanguage));

        foreach ($assessments as $assessment) {
            $editData = $assessment->attributes;
        }
        $data['actiontitle'] = $clang->gT("Edit");
        $data['actionvalue'] = "assessmentupdate";
        $data['editId'] = $editData['id'];
        $data['editdata'] = $editData;
        return $data;
    }

    /**
     * Inserts an assessment to the database. Receives input from POST
     */
    private function _add($surveyId)
    {
        if (bHasSurveyPermission($surveyId, 'assessments', 'create')) {
            $first = true;
            $assessmentId = -1;
            $languages = Yii::app()->getConfig("assessmentlangs");
            foreach ($languages as $language)
            {
                $data = $this->_getAssessmentPostData($surveyId, $language);

                if ($first == false) {
                    $data['id'] = $assessmentId;
                }
                $assessment = Assessment::insertRecords($data);
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
    private function _update($surveyid)
    {
        if (bHasSurveyPermission($surveyid, 'assessments', 'update') && isset($_POST['id'])) {

            $aid = sanitize_int($_POST['id']);
            $languages = Yii::app()->getConfig("assessmentlangs");
            foreach ($languages as $language)
            {
                $data = $this->_getAssessmentPostData($surveyid, $language);
                Assessment::updateAssessment($aid, $language, $data);
            }
        }
    }

    /**
     * Deletes an assessment.
     */
    private function _delete($surveyid, $assessmentId)
    {
        if (bHasSurveyPermission($surveyid, 'assessments', 'delete')) {
            Assessment::model()->deleteAllByAttributes(array('id' => $assessmentId));
        }
    }

    /**
     * Renders the views for the index
     * @return void
     */
    private function _renderHeaderAndFooter($path, array $data)
    {
        $this->getController()->_js_admin_includes(Yii::app()->getConfig("adminscripts") . 'assessments.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig("generalscripts") . 'jquery/jquery.tablesorter.min.js');
        $this->getController()->_getAdminHeader();
        $this->getController()->render($path, $data);
        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
    }

    private function _getAssessmentPostData($surveyid, $language)
    {
        if (!isset($_POST['gid']))
            $_POST['gid'] = 0;

        if (Yii::app()->getConfig('filterxsshtml')) {
            $_POST['name_' . $language] = htmlspecialchars($_POST['name_' . $language]);
            $_POST['assessmentmessage_' . $language] = htmlspecialchars($_POST['assessmentmessage_' . $language]);
        }

        return array(
            'sid' => $surveyid,
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
