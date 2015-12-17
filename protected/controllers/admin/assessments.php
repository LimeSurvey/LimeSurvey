<?php
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
use ls\models\Assessment;
use ls\models\QuestionGroup;
use ls\models\Survey;

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
        $iSurveyID = \ls\helpers\Sanitize::int($iSurveyID);
        $sAction = Yii::app()->request->getParam('action');

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

        if (App()->user->checkAccess('assessments', ['crud' => 'read', 'entity' => 'survey', 'entity_id' => $iSurveyID])) {
            if ($iSurveyID == '') {
                show_error(gT("No SID Provided"));
                die();
            }

            $this->_showAssessments($iSurveyID, $sAction, $surveyLanguage);
        }

    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'assessments', $aViewUrls = [], $aData = [])
    {
        App()->getClientScript()->registerScriptFile(App()->publicUrl . '/scripts/admin/' . 'assessments.js');
        App()->getClientScript()->registerPackage('jquery-tablesorter');
        

        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }

    private function _showAssessments($iSurveyID, $action)
    {
        $oAssessments = Assessment::model()->findAllByAttributes(['sid' => $iSurveyID]);
        $aData = $this->_collectGroupData($iSurveyID);
        $aHeadings = [gT("Scope"), gT("Question group"), gT("Minimum"), gT("Maximum")];
        $aData['actiontitle'] = gT("Add");
        $aData['actionvalue'] = "assessmentadd";
        $aData['editId'] = '';

        if ($action == "assessmentedit" && App()->user->checkAccess('assessments', ['crud' => 'update', 'entity' => 'survey', 'entity_id' => $iSurveyID])) {
            $aData = $this->_collectEditData($aData);
        }

        $surveyinfo = getSurveyInfo($iSurveyID);
        $aData['surveyinfo'] = $surveyinfo;
        $aData['imageurl'] = Yii::app()->getConfig('adminimageurl');
        $aData['surveyid'] = $iSurveyID;
        $aData['headings'] = $aHeadings;
        $aData['assessments'] = $oAssessments;
        $aData['assessmentlangs'] = Yii::app()->getConfig("assessmentlangs");
        $aData['baselang'] = $surveyinfo['language'];
        $aData['action'] = $action;
        $aData['gid'] = empty($_POST['gid']) ? '' : \ls\helpers\Sanitize::int($_POST['gid']);

        Yii::app()->loadHelper('admin/htmleditor');
        if ($surveyinfo['assessments']!='Y')
            $urls['message'] = ['title' => gT("Assessments mode not activated"), 'message' => sprintf(gT("Assessment mode for this survey is not activated. You can activate it in the %s survey settings %s (tab 'Notification & data management')."),'<a href="'.$this->getController()->createUrl('admin/survey/sa/editsurveysettings/surveyid/'.$iSurveyID).'">','</a>'), 'class'=> 'warningheader'];
        $urls['assessments_view'][]= $aData;
        $this->_renderWrappedTemplate('', $urls, $aData);
    }

    private function _collectGroupData($iSurveyID)
    {
        $aData = [];
        $groups = QuestionGroup::model()->findAllByAttributes(['sid' => $iSurveyID]);
        foreach ($groups as $group) {
            $groupId = $group->attributes['gid'];
            $groupName = $group->attributes['group_name'];
            $aData['groups'][$groupId] = $groupName;
        }
        return $aData;
    }

    private function _collectEditData(array $aData)
    {
        $oAssessment = Assessment::model()->find("id=:id", [':id' => App()->request->getParam('id')]);
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
        if (App()->user->checkAccess('assessments', ['crud' => 'create', 'entity' => 'survey', 'entity_id' => $iSurveyID])) {
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
        if (App()->user->checkAccess('assessments', ['crud' => 'update', 'entity' => 'survey', 'entity_id' => $iSurveyID]) && isset($_POST['id'])) {

            $aid = \ls\helpers\Sanitize::int($_POST['id']);
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
        if (App()->user->checkAccess('assessments', ['crud' => 'delete', 'entity' => 'survey', 'entity_id' => $iSurveyID])) {
            Assessment::model()->deleteAllByAttributes(['id' => $assessmentId, 'sid' => $iSurveyID]);
        }
    }

    private function _getAssessmentPostData($iSurveyID, $language)
    {
        if (!isset($_POST['gid']))
            $_POST['gid'] = 0;

        return [
            'sid' => $iSurveyID,
            'scope' => \ls\helpers\Sanitize::paranoid_string($_POST['scope']),
            'gid' => \ls\helpers\Sanitize::int($_POST['gid']),
            'minimum' => intval($_POST['minimum']),
            'maximum' => intval($_POST['maximum']),
            'name' => $_POST['name_' . $language],
            'language' => $language,
            'message' => $_POST['assessmentmessage_' . $language]
        ];
    }
}
