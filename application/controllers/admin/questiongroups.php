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
 * questiongroup
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
 * @access public
 */
class questiongroups extends Survey_Common_Action
{

    /**
     * questiongroup::import()
     * Function responsible to import a question group.
     *
     * @access public
     * @return void
     */
    public function import()
    {
        $action = $_POST['action'];
        $iSurveyID = $surveyid = $aData['surveyid'] = (int) $_POST['sid'];
        $survey = Survey::model()->findByPk($iSurveyID);

        if (!Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'import')) {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(array('admin/survey/sa/listquestiongroups/surveyid/'.$surveyid));
        }

        if ($action == 'importgroup') {
            $importgroup = "\n";
            $importgroup .= "\n";

            $sFullFilepath = Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.randomChars(20);
            $aPathInfo = pathinfo($_FILES['the_file']['name']);
            $sExtension = $aPathInfo['extension'];

            if ($_FILES['the_file']['error'] == 1 || $_FILES['the_file']['error'] == 2) {
                $fatalerror = sprintf(gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."), getMaximumFileUploadSize() / 1024 / 1024).'<br>';
            } elseif (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath)) {
                $fatalerror = gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder.");
            }

            // validate that we have a SID
            if (!returnGlobal('sid')) {
                $fatalerror .= gT("No SID (Survey) has been provided. Cannot import question.");
            }

            if (isset($fatalerror)) {
                @unlink($sFullFilepath);
                Yii::app()->user->setFlash('error', $fatalerror);
                $this->getController()->redirect(array('admin/questiongroups/sa/importview/surveyid/'.$surveyid));
            }

            Yii::app()->loadHelper('admin/import');

            // IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY
            if (strtolower($sExtension) == 'lsg') {
                $aImportResults = XMLImportGroup($sFullFilepath, $iSurveyID, (Yii::app()->request->getPost('translinksfields') == '1'));
            } else {
                Yii::app()->user->setFlash('error', gT("Unknown file extension"));
                $this->getController()->redirect(array('admin/questiongroups/sa/importview/surveyid/'.$surveyid));
            }
            LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting
            fixLanguageConsistency($iSurveyID);

            if (isset($aImportResults['fatalerror'])) {
                unlink($sFullFilepath);
                Yii::app()->user->setFlash('error', $aImportResults['fatalerror']);
                $this->getController()->redirect(array('admin/questiongroups/sa/importview/surveyid/'.$surveyid));
            }

            unlink($sFullFilepath);

            $aData['display'] = $importgroup;
            $aData['surveyid'] = $iSurveyID;
            $aData['aImportResults'] = $aImportResults;
            $aData['sExtension'] = $sExtension;
            //$aData['display']['menu_bars']['surveysummary'] = 'importgroup';
            $aData['sidemenu']['state'] = false;

            $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";

            $this->_renderWrappedTemplate('survey/QuestionGroups', 'import_view', $aData);
        }
    }

    /**
     * Import a question group
     *
     */
    public function importView($surveyid)
    {
        $iSurveyID = $surveyid = sanitize_int($surveyid);
        $survey = Survey::model()->findByPk($iSurveyID);

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'import')) {
            $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'addgroup';
            $aData['display']['menu_bars']['surveysummary'] = 'addgroup';
            $aData['sidemenu']['state'] = false;
            $aData['sidemenu']['questiongroups'] = true;

            $aData['surveybar']['closebutton']['url'] = 'admin/survey/sa/listquestiongroups/surveyid/'.$surveyid; // Close button
            $aData['surveybar']['savebutton']['form'] = true;
            $aData['surveybar']['savebutton']['text'] = gt('Import');
            $aData['surveyid'] = $surveyid;
            $aData['topBar']['sid'] = $iSurveyID;
            $aData['topBar']['showSaveButton'] = true;

            $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";

            $this->_renderWrappedTemplate('survey/QuestionGroups', 'importGroup_view', $aData);
        } else {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(array('admin/survey/sa/listquestiongroups/surveyid/'.$surveyid));
        }
    }

    /**
     * questiongroup::add()
     * Load add new question group screen.
     * @return
     */
    public function add($surveyid)
    {
        $this->view($surveyid, null);
        return;
        /////
        $iSurveyID = $surveyid = sanitize_int($surveyid);
        $survey = Survey::model()->findByPk($iSurveyID);
        $aViewUrls = $aData = array();

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create')) {
            Yii::app()->session['FileManagerContext'] = "create:group:{$surveyid}";

            Yii::app()->loadHelper('admin/htmleditor');
            Yii::app()->loadHelper('surveytranslator');
            App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts').'questiongroup.js');

            $aData['display']['menu_bars']['surveysummary'] = 'addgroup';
            $aData['surveyid'] = $surveyid;
            $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'addgroup';
            $aData['grplangs'] = $survey->allLanguages;
            $aData['baselang'] = $survey->language;
            ;

            $aData['sidemenu']['state'] = false;
            $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";
            $aData['subaction'] = gT('Add question group');
            $aData['surveybar']['importquestiongroup'] = true;
            $aData['surveybar']['closebutton']['url'] = 'admin/survey/sa/listquestiongroups/surveyid/'.$surveyid; // Close button
            $aData['surveybar']['savebutton']['form'] = true;
            $aData['topBar']['sid'] = $iSurveyID;
            $aData['topBar']['showSaveButton'] = true;
            $aData['surveybar']['saveandclosebutton']['form'] = true;
            $this->_renderWrappedTemplate('survey/QuestionGroups', 'addGroup_view', $aData);
        } else {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }
    }

    /**
     * Insert the new group to the database
     *
     * @access public
     * @param int $surveyid
     * @return void
     */
    public function insert($surveyid)
    {
        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create')) {
            Yii::app()->loadHelper('surveytranslator');

            $oGroup = new QuestionGroup;
            $oGroup->sid = $surveyid;
            $oGroup->group_order = getMaxGroupOrder($surveyid);
            ;
            $oGroup->randomization_group = Yii::app()->request->getPost('randomization_group');
            $oGroup->grelevance = Yii::app()->request->getPost('grelevance');
            if ($oGroup->save()) {
                $newGroupID = $oGroup->gid;
            } else {
                Yii::app()->setFlashMessage(CHtml::errorSummary($oGroup), 'error');
                $this->getController()->redirect(array("admin/questiongroups/sa/add/surveyid/$surveyid"));
            }
            $sSurveyLanguages = Survey::model()->findByPk($surveyid)->getAllLanguages();
            foreach ($sSurveyLanguages as $sLanguage) {
                $oGroupLS = new QuestionGroupL10n;
                $oGroupLS->gid = $newGroupID;
                $oGroupLS->group_name = Yii::app()->request->getPost('group_name_'.$sLanguage, "");
                $oGroupLS->description = Yii::app()->request->getPost('description_'.$sLanguage, "");
                $oGroupLS->language = $sLanguage;
                $oGroupLS->save();
            }
            Yii::app()->setFlashMessage(gT("New question group was saved."));
            Yii::app()->setFlashMessage(sprintf(gT('You can now %sadd a question%s in this group.'), '<a href="'.Yii::app()->createUrl("admin/questions/sa/newquestion/surveyid/$surveyid/gid/$newGroupID").'">', '</a>'), 'info');
            if (Yii::app()->request->getPost('close-after-save') === 'true') {
                $this->getController()->redirect(array("admin/questiongroups/sa/view/surveyid/$surveyid/gid/$newGroupID"));
            } elseif (Yii::app()->request->getPost('saveandnew', '') !== '') {
                $this->getController()->redirect(array("admin/questiongroups/sa/add/surveyid/$surveyid"));
            } elseif (Yii::app()->request->getPost('saveandnewquestion', '') !== '') {
                $this->getController()->redirect(array("admin/questions/sa/newquestion/", 'surveyid' => $surveyid, 'gid' => $newGroupID));
            } else {
                // After save, go to edit
                $this->getController()->redirect(array("admin/questiongroups/sa/edit/surveyid/$surveyid/gid/$newGroupID"));
            }
        } else {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }
    }

    /**
     * Action to delete a question group.
     *
     * @access public
     * @return void
     */
    public function delete($iSurveyId=null, $iGroupId=null)
    {
        if (is_null($iGroupId)) {
            $iGroupId = Yii::app()->getRequest()->getPost('gid');
        }
        $oQuestionGroup = QuestionGroup::model()->find("gid = :gid", array(":gid"=>$iGroupId));
        if (empty($oQuestionGroup)) {
            throw new CHttpException(401, gT("Invalid question id"));
        }
        /* Test the surveyid from question, not from submitted value */
        $iSurveyId = $oQuestionGroup->sid;
        if (!Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'delete')) {
            throw new CHttpException(403, gT("You are not authorized to delete questions."));
        }
        if (!Yii::app()->getRequest()->isPostRequest) {
            throw new CHttpException(405, gT("Invalid action"));
        }

        LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyId);

        $iGroupId = sanitize_int($iGroupId);
        $iGroupsDeleted = QuestionGroup::deleteWithDependency($iGroupId, $iSurveyId);

        if ($iGroupsDeleted > 0) {
            QuestionGroup::model()->updateGroupOrder($iSurveyId);
            Yii::app()->setFlashMessage(gT('The question group was deleted.'));
        } else {
            Yii::app()->setFlashMessage(gT('Group could not be deleted'), 'error');
        }

        LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyId);
        $this->getController()->redirect(array('admin/survey/sa/listquestiongroups/surveyid/'.$iSurveyId));
    }

    public function view($surveyid, $gid)
    {
        $aData = array();
        $aData['surveyid'] = $iSurveyID = $surveyid;
        $survey = Survey::model()->findByPk($iSurveyID);
        $aData['gid'] = $gid;
        $baselang = $survey->language;
        if($gid!==null) {
            $condarray = getGroupDepsForConditions($surveyid, "all", $gid, "by-targgid");
        }
        $aData['condarray'] = $condarray ?? [];

        Yii::app()->getClientScript()->registerPackage('ace');
        Yii::app()->getClientScript()->registerPackage('questiongroupeditor');

        $oQuestionGroup = $this->_getQuestionGroupObject($gid);
        $grow           = $oQuestionGroup->attributes;

        $grow = array_map('flattenText', $grow);

        $aData['oQuestionGroup'] = $oQuestionGroup;
        $aData['surveyid'] = $surveyid;
        $aData['gid'] = $gid;
        $aData['grow'] = $grow;

        $aData['sidemenu']['questiongroups'] = true;
        $aData['sidemenu']['group_name'] = $oQuestionGroup->questionGroupL10ns[$baselang]->group_name ?? '';
        $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$iSurveyID.")";
        $aData['questiongroupbar']['buttons']['view'] = true;

        $aData['questiongroupbar']['buttonspreview'] = true;
        $aData['questiongroupbar']['savebutton']['form'] = true;
        $aData['questiongroupbar']['saveandclosebutton']['form'] = true;
        if (sanitize_paranoid_string(App()->request->getParam('sa') == 'add')) {
            $aData['questiongroupbar']['importbutton'] = true;
        }

        ///////////
        // sidemenu
        $aData['sidemenu']['state'] = true;
        $aData['sidemenu']['explorer']['state'] = true;
        $aData['sidemenu']['explorer']['gid'] = (isset($gid)) ? $gid : false;
        $aData['sidemenu']['explorer']['qid'] = false;

        $aData['jsData'] = [
            'surveyid' => $iSurveyID,
            'gid' => $gid,
            'startInEditView' => SettingsUser::getUserSettingValue('noViewMode', App()->user->id) == '1',
            'connectorBaseUrl' => $this->getController()->createUrl('admin/questiongroups', ['sid' => $iSurveyID, 'sa' => '']),
            'openQuestionUrl' => $this->getController()->createUrl('admin/questioneditor/sa/view/', ['surveyid'=>$iSurveyID, 'gid'=>$gid, 'qid' => '']),
            'createQuestionUrl' => $this->getController()->createUrl("admin/questioneditor/sa/view/" ,["surveyid" =>  $surveyid, 'gid' => $gid]),
            'i10N' => [
                'Question group' => gT('Question group'),
                'Group overview' => gT('Group overview'),
                'Question list' => gT('Question list'),
                'Create new question group' => gT('Create new question group'),
                'Question group overview' => gT('Question group overview'),
                'Question group editor' => gT('Question group editor'),
                'General Settings' => gT("General Settings"),
                'Group summary' => gT('Group summary'),
                'Random Group' => gT('Random Group'),
                'Title' => gT('Title'),
                'Relevance' => gT('Relevance'),
                'Description' => gT('Description'),
                'Quick actions' => gT('Quick actions'),
                'Question list' => gT('Question list'),
                'Subquestions' => gT('Subquestions'),
                'Answeroptions' => gT('Answer options'),
                'Question type' => gT('Question type'),
                'Default answer' => gT('Default answer'),
                'Create question' => gT('Create question'),
                'Order' => gT('Order'),
                'Question code' => gT('Question code'),
                'Question' => gT('Question'),
                'QuestionType' => gT('QuestionType'),
                'Mandatory' => gT('Mandatory'),
                'Encrypted' => gT('Encrypted'),
                'Actions' => gT('Actions'),
               ]
        ];

        $this->_renderWrappedTemplate('survey/QuestionGroups', 'group_view', $aData);
    }

    public function loadQuestionGroup($surveyid, $iQuestionGroupId=null)
    {
        $oQuestionGroup = QuestionGroup::model()->findByPk($iQuestionGroupId);
        $oSurvey = Survey::model()->findByPk($surveyid);

        $aLanguages = [];
        $aAllLanguages = getLanguageData(false, Yii::app()->session['adminlang']);
        $aSurveyLanguages = $oSurvey->getAllLanguages();

        array_walk($aSurveyLanguages, function ($lngString) use (&$aLanguages, $aAllLanguages) {
            $aLanguages[$lngString] = $aAllLanguages[$lngString]['description'];
        });

        if ($oQuestionGroup == null) {
            $oQuestionGroup = new QuestionGroup();
            $oQuestionGroup->sid = $oSurvey->sid;
            $i10N = [];
            array_walk($aSurveyLanguages, function ($sLanguage) use (&$i10N) {
                $i10N[$sLanguage] = [
                    'language' => $sLanguage,
                    'group_name' => '',
                    'group_name_expression' => '',
                    'description' => '',
                    'description_expression' => '',
                ];
            });
        } else {
            $i10N = [];
            foreach ($oQuestionGroup->questionGroupL10ns as $lng => $oQuestionGroupi10n) {
                $i10N[$lng] = $oQuestionGroupi10n->attributes;

                templatereplace($oQuestionGroupi10n->group_name, array(), $aReplacementData, 'Unspecified', false, null);
                $i10N[$lng]['group_name_expression'] = viewHelper::stripTagsEM(
                    LimeExpressionManager::GetLastPrettyPrintExpression()
                );
                
                templatereplace($oQuestionGroupi10n->description, array(), $aReplacementData, 'Unspecified', false, null);
                $i10N[$lng]['description_expression'] = viewHelper::stripTagsEM(
                    LimeExpressionManager::GetLastPrettyPrintExpression()
                );
            }
        }

        $aPermissions = [
            "read" => Permission::model()->hasSurveyPermission($oSurvey->sid, 'survey', 'read'),
            "update" => Permission::model()->hasSurveyPermission($oSurvey->sid, 'survey', 'update'),
            "editorpreset" => Yii::app()->session['htmleditormode'],
        ];

        $aQuestionGroup = $oQuestionGroup->attributes;
        LimeExpressionManager::ProcessString('{' . $aQuestionGroup['grelevance'] . '}');
        $aQuestionGroup['grelevance_expression'] = viewHelper::stripTagsEM(
            LimeExpressionManager::GetLastPrettyPrintExpression()
        );
        $this->renderJSON([
            'questionGroup' => $aQuestionGroup,
            'permissions' => $aPermissions, 
            'questonGroupI10N' => $i10N,
            'languages' => $aLanguages
        ]);
    }
    public function getQuestionsForGroup($surveyid, $iQuestionGroupId)
    {
        $iQuestionGroupId = (int) $iQuestionGroupId;
        $oQuestionGroup = QuestionGroup::model()->findByPk($iQuestionGroupId);
        if ($oQuestionGroup == null) {
            $this->renderJSON([]);
            return;
        }
        $aQuestions = [];
        $aAllQuestions = $oQuestionGroup->questions;
        array_walk($aAllQuestions, function ($oQuestion) use (&$aQuestions) {
            $aQuestions[$oQuestion->qid] = array_merge($oQuestion->attributes, $oQuestion->questionL10ns);
        });

        $this->renderJSON($aQuestions);
    }
    public function saveQuestionGroupData($sid)
    {
        $questionGroup = App()->request->getPost('questionGroup', []);
        $questionGroupI10N = App()->request->getPost('questionGroupI10N', []);
        $iSurveyId = (int) $sid;

        $oQuestionGroup = QuestionGroup::model()->findByPk($questionGroup['gid']);
        if ($oQuestionGroup == null) {
            $oQuestionGroup = $this->_newQuestionGroup($questionGroup);
        } else {
            $oQuestionGroup = $this->_editQuestionGroup($oQuestionGroup, $questionGroup);
        }
        //$this->_applyI10N($oQuestionGroup, $oQuestionGroupI10N);

        $success = $this->_applyI10N($oQuestionGroup, $questionGroupI10N);

        $this->renderJSON([
            'success' => $success,
            'message' => gT('Question group successfully stored'),
            'questionGroupId' => $oQuestionGroup->gid,
            'redirect' => $this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$iSurveyId),
            'transfer' => [$questionGroup, $questionGroupI10N],
        ]);
        Yii::app()->close();
    }
    /**
     * questiongroup::edit()
     * Load editing of a question group screen.
     *
     * @access public
     * @param int $surveyid
     * @param int $gid
     * @return void
     */
    public function edit($surveyid, $gid)
    {
        $surveyid = $iSurveyID = sanitize_int($surveyid);
        $survey = Survey::model()->findByPk($surveyid);
        $gid = sanitize_int($gid);
        $aViewUrls = $aData = array();

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            Yii::app()->session['FileManagerContext'] = "edit:group:{$surveyid}";

            Yii::app()->loadHelper('admin/htmleditor');
            Yii::app()->loadHelper('surveytranslator');

            // TODO: This is not an array, but a string "en"
            $aBaseLanguage = $survey->language;

            $aLanguages = $survey->allLanguages;

            $grplangs = array_flip($aLanguages);

            // Check out the intgrity of the language versions of this group
            $egresult = QuestionGroupL10n::model()->findAllByAttributes(array('gid' => $gid));
            foreach ($egresult as $esrow) {
                $esrow = $esrow->attributes;

                // Language Exists, BUT ITS NOT ON THE SURVEY ANYMORE
                if (!in_array($esrow['language'], $aLanguages)) {
                    QuestionGroupL10n::model()->deleteAllByAttributes(array('gid' => $gid, 'language' => $esrow['language']));
                } else {
                    $grplangs[$esrow['language']] = 'exists';
                }

                if ($esrow['language'] == $aBaseLanguage) {
                    $basesettings = $esrow;
                }
            }

            // Create groups in missing languages
            foreach ($grplangs as $key => $value) {
                if ($value != 'exists') {
                    $basesettings['language'] = $key;
                    $groupLS = new QuestionGroupL10n;
                    foreach ($basesettings as $k => $v) {
                        $group->$k = $v;
                    }
                    $groupLS->save();
                }
            }
            $first = true;
            $oQuestionGroup = QuestionGroup::model()->findByAttributes(array('gid' => $gid));
            foreach ($aLanguages as $sLanguage) {
                $oResult = QuestionGroupL10n::model()->findByAttributes(array('gid' => $gid, 'language' => $sLanguage));
                $aData['aGroupData'][$sLanguage] = array_merge($oResult->attributes, $oQuestionGroup->attributes);
                $aTabTitles[$sLanguage] = getLanguageNameFromCode($sLanguage, false);
                if ($first) {
                    $aTabTitles[$sLanguage] .= ' ('.gT("Base language").')';
                    $first = false;
                }
            }
            $aData['oQuestionGroup'] = $oQuestionGroup;
            $aData['sidemenu']['questiongroups'] = true;
            $aData['questiongroupbar']['buttonspreview'] = true;
            $aData['questiongroupbar']['savebutton']['form'] = true;
            $aData['questiongroupbar']['saveandclosebutton']['form'] = true;
            $aData['questiongroupbar']['closebutton']['url'] = 'admin/questiongroups/sa/view/surveyid/'.$surveyid.'/gid/'.$gid; // Close button

            $aData['topBar']['sid'] = $iSurveyID;
            $aData['topBar']['gid'] = $gid;
            $aData['topBar']['showSaveButton'] = true;
            $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'editgroup';
            $aData['subaction'] = gT('Edit group');
            $aData['surveyid'] = $surveyid;
            $aData['gid'] = $gid;
            $aData['tabtitles'] = $aTabTitles;
            $aData['aBaseLanguage'] = $aBaseLanguage;

            $aData['title_bar']['title'] = $survey->currentLanguageSettings->surveyls_title.":".$iSurveyID.")";

            ///////////
            // sidemenu
            $aData['sidemenu']['state'] = false;
            $aData['sidemenu']['explorer']['state'] = true;
            $aData['sidemenu']['explorer']['gid'] = (isset($gid)) ? $gid : false;
            $aData['sidemenu']['explorer']['qid'] = false;

            $this->_renderWrappedTemplate('survey/QuestionGroups', 'editGroup_view', $aData);
        } else {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }
    }

    /**
     * Reorder the questiongroups based on the new order in the adminsidepanel
     *
     * @param integer $surveyid
     * @return void
     */
    public function updateOrder($surveyid)
    {
        $oSurvey = Survey::model()->findByPk($surveyid);
        $success = true;
        if (!$oSurvey->isActive) {
            $grouparray = Yii::app()->request->getPost('grouparray', []);
            if (!empty($grouparray)) {
                foreach ($grouparray as $aQuestiongroup) {

                    //first set up the ordering for questiongroups
                    $oQuestiongroups = QuestionGroup::model()->findAll("gid=:gid AND sid=:sid", [':gid'=> $aQuestiongroup['gid'], ':sid'=> $surveyid]);
                    array_map(function ($oQuestiongroup) use ($aQuestiongroup, $success) {
                        $oQuestiongroup->group_order = $aQuestiongroup['group_order'];
                        $success = $success && $oQuestiongroup->save();
                    }, $oQuestiongroups);

                    $aQuestiongroup['questions'] = isset($aQuestiongroup['questions']) ? $aQuestiongroup['questions'] : [];

                    foreach ($aQuestiongroup['questions'] as $aQuestion) {
                        $oQuestions = Question::model()->findAll("qid=:qid AND sid=:sid", [':qid'=> $aQuestion['qid'], ':sid'=> $surveyid]);
                        array_map(function ($oQuestion) use ($aQuestion, $success) {
                            $oQuestion->question_order = $aQuestion['question_order'];
                            $oQuestion->gid = $aQuestion['gid'];
                            $success = $success && $oQuestion->save(true);
                        }, $oQuestions);
                    }
                }
            }

            QuestionGroup::model()->cleanOrder($surveyid);

            return Yii::app()->getController()->renderPartial(
                '/admin/super/_renderJson',
                array(
                    'data' => [
                        'success' => $success,
                        'DEBUG' => ['POST'=>$_POST, 'grouparray' => $grouparray]
                    ],
                ),
                false,
                false
            );
        }
        return Yii::app()->getController()->renderPartial(
            '/admin/super/_renderJson',
            array(
                'data' => [
                    'success' => false,
                    'message' => gT("You can't reorder in an active survey"),
                    'DEBUG' => ['POST'=>$_POST, 'grouparray' => $grouparray]
                ],
            ),
            false,
            false
        );
    }

    /**
     * Reorder the questiongroups based on the new order in the adminsidepanel
     *
     * @param integer $surveyid
     * @return void
     */
    public function updateOrderWithQuestions($surveyid)
    {
        $grouparray = Yii::app()->request->getPost('grouparray', []);
        foreach ($grouparray as $aQuestiongroup) {
            $oQuestiongroups = QuestionGroup::model()->findAll("gid=:gid AND sid=:sid", [':gid'=> $aQuestiongroup['gid'], ':sid'=> $surveyid]);
            array_map(function ($oQuestiongroup) use ($aQuestiongroup) {
                $oQuestiongroup->group_order = $aQuestiongroup['group_order'];
                $oQuestiongroup->save();
            }, $oQuestiongroups);
        }
    }

    /**
     * Provides an interface for updating a group
     *
     * @access public
     * @param int $gid
     * @return void
     */
    public function update($gid)
    {
        $gid = (int) $gid;
        $group = QuestionGroup::model()->findByAttributes(array('gid' => $gid));
        $surveyid = $group->sid;
        $survey = Survey::model()->findByPk($surveyid);

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update')) {
            Yii::app()->loadHelper('surveytranslator');

            foreach ($survey->allLanguages as $grplang) {
                if (isset($grplang) && $grplang != "") {
                    $group_name = $_POST['group_name_'.$grplang];
                    $group_description = $_POST['description_'.$grplang];

                    $group_name = html_entity_decode($group_name, ENT_QUOTES, "UTF-8");
                    $group_description = html_entity_decode($group_description, ENT_QUOTES, "UTF-8");

                    // Fix bug with FCKEditor saving strange BR types
                    $group_name = fixCKeditorText($group_name);
                    $group_description = fixCKeditorText($group_description);

                    $aData = array(
                        'randomization_group' => $_POST['randomization_group'],
                        'grelevance' => $_POST['grelevance'],
                    );
                    $group = QuestionGroup::model()->findByPk($gid);
                    foreach ($aData as $k => $v) {
                        $group->$k = $v;
                    }
                    $ugresult = $group->save();

                    $aData = array(
                        'group_name' => $group_name,
                        'description' => $group_description,
                    );
                    $condition = array(
                        'language' => $grplang,
                        'gid' => $gid,
                    );
                    $oGroupLS = QuestionGroupL10n::model()->findByAttributes($condition);
                    foreach ($aData as $k => $v) {
                        $oGroupLS->$k = $v;
                    }
                    $ugresult2 = $oGroupLS->save();
                }
            }

            Yii::app()->setFlashMessage(gT("Question group successfully saved."));

            if (Yii::app()->request->getPost('close-after-save') === 'true') {
                $this->getController()->redirect(array('admin/questiongroups/sa/view/surveyid/'.$surveyid.'/gid/'.$gid));
            }

            $this->getController()->redirect(array('admin/questiongroups/sa/edit/surveyid/'.$surveyid.'/gid/'.$gid));
        } else {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }
    }

    public function getQuestionGroupTopBar($gid) {
        $oQuestionGroup = QuestionGroup::model()->findByPk($gid);
        $sumcount  = safecount($oQuestionGroup->questions);
        $activated = $oQuestionGroup->survey->active;
        $languagelist = $oQuestionGroup->survey->allLanguages;
        $ownsSaveButton = true;
        $ownsSaveAndCloseButton = true;
  
        return Yii::app()->getController()->renderPartial(
          '/admin/survey/topbar/question_group_topbar',
          array(
            'oSurvey' => $oQuestionGroup->survey,
            'sid'     => $oQuestionGroup->sid,
            'gid'     => $oQuestionGroup->gid,
            'sumcount4' => $sumcount,
            'languagelist' => $languagelist,
            'activated' => $activated,
            'ownsSaveButton'         => $ownsSaveButton,
            'ownsSaveAndCloseButton' => $ownsSaveAndCloseButton,
          ),
          false,
          false
        );
      }
  

    private function _getQuestionGroupObject($iQuestionGroupId=null)
    {
        $iSurveyId = Yii::app()->request->getParam('sid') ?? Yii::app()->request->getParam('surveyid');
        $oQuestionGroup =  QuestionGroup::model()->findByPk($iQuestionGroupId);
        if ($oQuestionGroup == null) {
            $oQuestionGroup = new QuestionGroup();
            $oQuestionGroup->sid = $iSurveyId;
        }

        return $oQuestionGroup;
    }
    /**
         * Method to store and filter questionData for a new question
         */
    private function _newQuestionGroup($aQuestionGroupData = null)
    {
        $iSurveyId = Yii::app()->request->getParam('sid') ?? Yii::app()->request->getParam('surveyid');
        $oSurvey = Survey::model()->findByPk($iSurveyId);

        $aQuestionGroupData = array_merge([
                'sid' => $iSurveyId,
        ], $aQuestionGroupData);
        unset($aQuestionGroupData['gid']);

        $oQuestionGroup = new QuestionGroup();
        $oQuestionGroup->setAttributes($aQuestionGroupData, false);
        if ($oQuestionGroup == null) {
            throw new CException("Object creation failed, input array malformed or invalid");
        }

        $saved = $oQuestionGroup->save();
        if ($saved == false) {
            throw new CException("Object creation failed, couldn't save.\n ERRORS:".print_r($oQuestionGroup->getErrors(), true));
        }

        $i10N = [];
        foreach ($oSurvey->allLanguages as $sLanguage) {
            $i10N[$sLanguage] = new QuestionGroupL10n();
            $i10N[$sLanguage]->setAttributes([
                'gid' => $oQuestionGroup->gid,
                'language' => $sLanguage,
                'group_name' => '',
                'description' => '',
            ], false);
            $i10N[$sLanguage]->save();
        }

        return $oQuestionGroup;
    }

    /**
     * Method to store and filter questionGroupData for editing a questionGroup
     */
    private function _editQuestionGroup(&$oQuestionGroup, $aQuestionGroupData)
    {
        $aOldQuestionGroupData = $oQuestionGroup->attributes;
        $oQuestionGroup->setAttributes($aQuestionGroupData, false);
        if ($oQuestionGroup == null) {
            throw new CException("Object update failed, input array malformed or invalid");
        }

        $saved = $oQuestionGroup->save();
        if ($saved == false) {
            throw new CException("Object update failed, couldn't save. ERRORS:".print_r($oQuestionGroup->getErrors(), true));
        }
        return $oQuestionGroup;
    }

    private function _applyI10N(&$oQuestionGroup, $dataSet)
    {
        $storeValid = true;

        foreach ($dataSet as $sLanguage => $aI10NBlock) {
            $i10N = QuestionGroupL10n::model()->findByAttributes(['gid' => $oQuestionGroup->gid,'language' => $sLanguage]);
            $i10N->setAttributes([
                'group_name' => $aI10NBlock['group_name'],
                'description' => $aI10NBlock['description'],
            ], false);
            $storeValid = $storeValid && $i10N->save();
        }

        return $storeValid;
    }

    /**
     * Method to render an array as a json document
     *
     * @param array $aData
     * @return void
     */
    protected function renderJSON($aData)
    {
        if (Yii::app()->getConfig('debug') > 0) {
            $aData['debug'] = [$_POST, $_GET];
        }

        echo Yii::app()->getController()->renderPartial('/admin/super/_renderJson', ['data' => $aData], true, false);
        return;
    }
    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'survey/QuestionGroups', $aViewUrls = array(), $aData = array(), $sRenderFile = false)
    {
        $aData['topBar']['type'] = 'group';
        $aData['topBar']['showSaveButton'] = true;
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData, $sRenderFile);
    }
}
