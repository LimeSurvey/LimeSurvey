<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
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
    function import()
    {
        $action = $_POST['action'];
        $iSurveyID = $surveyid =  $aData['surveyid'] = (int)$_POST['sid'];

        if (!Permission::model()->hasSurveyPermission($surveyid,'surveycontent','import'))
        {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(array('admin/survey/sa/listquestiongroups/surveyid/' . $surveyid));
        }

        if ($action == 'importgroup')
        {
            $importgroup = "\n";
            $importgroup .= "\n";

            $sFullFilepath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(20);
            $aPathInfo = pathinfo($_FILES['the_file']['name']);
            $sExtension = $aPathInfo['extension'];

            if ($_FILES['the_file']['error']==1 || $_FILES['the_file']['error']==2)
            {
                $fatalerror=sprintf(gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."), getMaximumFileUploadSize()/1024/1024).'<br>';
            }

            elseif(!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath))
            {
                $fatalerror = gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder.");
            }

            // validate that we have a SID
            if (!returnGlobal('sid'))
                $fatalerror .= gT("No SID (Survey) has been provided. Cannot import question.");

            if (isset($fatalerror))
            {
                @unlink($sFullFilepath);
                Yii::app()->user->setFlash('error', $fatalerror);
                $this->getController()->redirect(array('admin/questiongroups/sa/importview/surveyid/' . $surveyid));
            }

            Yii::app()->loadHelper('admin/import');

            // IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY
            if (strtolower($sExtension) == 'lsg')
            {
                $aImportResults = XMLImportGroup($sFullFilepath, $iSurveyID);
            }
            else
            {
                Yii::app()->user->setFlash('error', gT("Unknown file extension"));
                $this->getController()->redirect(array('admin/questiongroups/sa/importview/surveyid/' . $surveyid));
            }
            LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting
            fixLanguageConsistency($iSurveyID);

            if (isset($aImportResults['fatalerror']))
            {
                unlink($sFullFilepath);
                Yii::app()->user->setFlash('error', $aImportResults['fatalerror']);
                $this->getController()->redirect(array('admin/questiongroups/sa/importview/surveyid/' . $surveyid));
            }

            unlink($sFullFilepath);

            $aData['display'] = $importgroup;
            $aData['surveyid'] = $iSurveyID;
            $aData['aImportResults'] = $aImportResults;
            $aData['sExtension'] = $sExtension;
            //$aData['display']['menu_bars']['surveysummary'] = 'importgroup';
            $aData['sidemenu']['state'] = false;

            $surveyinfo = Survey::model()->findByPk($iSurveyID)->surveyinfo;
            $aData['title_bar']['title'] = $surveyinfo['surveyls_title']."(".gT("ID").":".$iSurveyID.")";

            $this->_renderWrappedTemplate('survey/QuestionGroups', 'import_view', $aData);
        }
    }

    /**
     * Import a question group
     *
     */
    function importView($surveyid)
    {
        $iSurveyID = $surveyid = sanitize_int($surveyid);
        if (Permission::model()->hasSurveyPermission($surveyid,'surveycontent','import'))
        {

            $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'addgroup';
            $aData['display']['menu_bars']['surveysummary'] = 'addgroup';
            $aData['sidemenu']['state'] = false;
            $aData['sidemenu']['questiongroups'] = true;

            $aData['surveybar']['closebutton']['url'] = 'admin/survey/sa/listquestiongroups/surveyid/'.$surveyid;  // Close button
            $aData['surveybar']['savebutton']['form'] = true;
            $aData['surveybar']['savebutton']['text'] = gt('Import');
            $aData['surveyid'] = $surveyid;


            $surveyinfo = Survey::model()->findByPk($iSurveyID)->surveyinfo;
            $aData['title_bar']['title'] = $surveyinfo['surveyls_title']."(".gT("ID").":".$iSurveyID.")";

            $this->_renderWrappedTemplate('survey/QuestionGroups', 'importGroup_view', $aData);
        }
        else
        {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(array('admin/survey/sa/listquestiongroups/surveyid/' . $surveyid));
        }
    }

    /**
     * questiongroup::add()
     * Load add new question group screen.
     * @return
     */
    function add($surveyid)
    {
        /////
        $iSurveyID = $surveyid = sanitize_int($surveyid);
        $aViewUrls = $aData = array();

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create'))
        {
            Yii::app()->session['FileManagerContext'] = "create:group:{$surveyid}";

            Yii::app()->loadHelper('admin/htmleditor');
            Yii::app()->loadHelper('surveytranslator');
            $grplangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
            $baselang = Survey::model()->findByPk($surveyid)->language;
            $grplangs[] = $baselang;
            $grplangs = array_reverse($grplangs);
            $this->registerScriptFile( 'ADMIN_SCRIPT_PATH', 'questiongroup.js');

            $aData['display']['menu_bars']['surveysummary'] = 'addgroup';
            $aData['surveyid'] = $surveyid;
            $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'addgroup';
            $aData['grplangs'] = $grplangs;
            $aData['baselang'] = $baselang;

            $aData['sidemenu']['state'] = false;
            $surveyinfo = Survey::model()->findByPk($iSurveyID)->surveyinfo;
            $aData['title_bar']['title'] = $surveyinfo['surveyls_title']."(".gT("ID").":".$iSurveyID.")";
            $aData['surveybar']['importquestiongroup'] = true;
            $aData['surveybar']['closebutton']['url'] = 'admin/survey/sa/listquestiongroups/surveyid/'.$surveyid;  // Close button
            $aData['surveybar']['savebutton']['form'] = true;
            $aData['surveybar']['saveandclosebutton']['form'] = true;
            $this->_renderWrappedTemplate('survey/QuestionGroups', 'addGroup_view', $aData);
        }
        else
        {
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
        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'create'))
        {
            Yii::app()->loadHelper('surveytranslator');

            $sSurveyLanguages = Survey::model()->findByPk($surveyid)->getAllLanguages();
            foreach ($sSurveyLanguages as $sLanguage)
            {
                $oGroup=new QuestionGroup;
                $oGroup->sid=$surveyid;
                if(isset($newGroupID)){
                    $oGroup->gid=$newGroupID;
                }
                $oGroup->group_name = Yii::app()->request->getPost('group_name_' . $sLanguage,"");
                $oGroup->description = Yii::app()->request->getPost('description_' . $sLanguage,"");
                if(!isset($newGroupOrder)){
                    $newGroupOrder=getMaxGroupOrder($surveyid);
                }
                $oGroup->group_order=$newGroupOrder;
                $oGroup->language =$sLanguage;
                $oGroup->randomization_group =Yii::app()->request->getPost('randomization_group');
                $oGroup->grelevance =Yii::app()->request->getPost('grelevance');
                if($oGroup->save()){
                    if(!isset($newGroupID)){
                        $newGroupID=$oGroup->gid;
                    }
                }else{
                    Yii::app()->setFlashMessage(CHtml::errorSummary($oGroup),'error');
                }
            }
            if(!isset($newGroupID)){
                // Error, redirect back.
                Yii::app()->setFlashMessage(gT("Question group was not saved."), 'error');
                $this->getController()->redirect(array("admin/questiongroups/sa/add/surveyid/$surveyid"));
            }

            Yii::app()->setFlashMessage(gT("New question group was saved."));
            Yii::app()->setFlashMessage(sprintf(gT('You can now %sadd a question%s in this group.'),'<a href="'.Yii::app()->createUrl("admin/questions/sa/newquestion/surveyid/$surveyid/gid/$newGroupID").'">','</a>'),'info');
            if(Yii::app()->request->getPost('close-after-save') === 'true')
            {
                $this->getController()->redirect(array("admin/questiongroups/sa/view/surveyid/$surveyid/gid/$newGroupID"));
            }
            else
            {
                // After save, go to edit
                $this->getController()->redirect(array("admin/questiongroups/sa/edit/surveyid/$surveyid/gid/$newGroupID"));
            }

        }
        else
        {
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
    public function delete($iSurveyId, $iGroupId)
    {
        $iSurveyId = sanitize_int($iSurveyId);

        if (Permission::model()->hasSurveyPermission($iSurveyId, 'surveycontent', 'delete'))
        {
            LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyId);

            $iGroupId = sanitize_int($iGroupId);
            $iGroupsDeleted = QuestionGroup::deleteWithDependency($iGroupId, $iSurveyId);

            if ($iGroupsDeleted > 0)
            {
                fixSortOrderGroups($iSurveyId);
                Yii::app()->setFlashMessage(gT('The question group was deleted.'));
            }
            else
                Yii::app()->setFlashMessage(gT('Group could not be deleted'),'error');
            LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyId);
            $this->getController()->redirect(array('admin/survey/sa/listquestiongroups/surveyid/' . $iSurveyId ));
        }
        else
        {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }
    }

    public function view($surveyid, $gid)
    {
        $aData = array();
        $aData['surveyid'] = $iSurveyID = $surveyid;
        $aData['gid'] = $gid;
        $baselang = Survey::model()->findByPk($surveyid)->language;
        $condarray = getGroupDepsForConditions($surveyid, "all", $gid, "by-targgid");
        $aData['condarray'] = $condarray;

        $oQuestionGroup = QuestionGroup::model()->findByPk(array('gid' => $gid, 'language' => $baselang));
        $grow           = $oQuestionGroup->attributes;

        $grow = array_map('flattenText', $grow);

        $aData['oQuestionGroup'] = $oQuestionGroup;
        $aData['surveyid'] = $surveyid;
        $aData['gid'] = $gid;
        $aData['grow'] = $grow;

        $aData['sidemenu']['questiongroups'] = true;
        $aData['sidemenu']['group_name'] = $grow['group_name'];
        $surveyinfo = Survey::model()->findByPk($iSurveyID)->surveyinfo;
        $aData['title_bar']['title'] = $surveyinfo['surveyls_title']."(".gT("ID").":".$iSurveyID.")";
        $aData['surveyIsActive'] = $surveyinfo['active']=='Y';
        $aData['questiongroupbar']['buttons']['view'] = true;

        ///////////
        // sidemenu
        $aData['sidemenu']['state'] = true;
        $aData['sidemenu']['explorer']['state'] = true;
        $aData['sidemenu']['explorer']['gid'] = (isset($gid))?$gid:false;
        $aData['sidemenu']['explorer']['qid'] = false;

        $this->_renderWrappedTemplate('survey/QuestionGroups', 'group_view', $aData);
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
        $gid = sanitize_int($gid);
        $aViewUrls = $aData = array();

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update'))
        {
            Yii::app()->session['FileManagerContext'] = "edit:group:{$surveyid}";

            Yii::app()->loadHelper('admin/htmleditor');
            Yii::app()->loadHelper('surveytranslator');

            $aAdditionalLanguages = Survey::model()->findByPk($surveyid)->additionalLanguages;
            // TODO: This is not an array, but a string "en"
            $aBaseLanguage = Survey::model()->findByPk($surveyid)->language;

            $aLanguages = array_merge(array($aBaseLanguage), $aAdditionalLanguages);

            $grplangs = array_flip($aLanguages);

            // Check out the intgrity of the language versions of this group
            $egresult = QuestionGroup::model()->findAllByAttributes(array('sid' => $surveyid, 'gid' => $gid));
            foreach ($egresult as $esrow)
            {
                $esrow = $esrow->attributes;

                // Language Exists, BUT ITS NOT ON THE SURVEY ANYMORE
                if (!in_array($esrow['language'], $aLanguages))
                {
                    QuestionGroup::model()->deleteAllByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'language' => $esrow['language']));
                }
                else
                {
                    $grplangs[$esrow['language']] = 'exists';
                }

                if ($esrow['language'] == $aBaseLanguage)
                    $basesettings = $esrow;
            }

            // Create groups in missing languages
            while (list($key, $value) = each($grplangs))
            {
                if ($value != 'exists')
                {
                    $basesettings['language'] = $key;
                    $group = new QuestionGroup;
                    foreach ($basesettings as $k => $v)
                        $group->$k = $v;
                    switchMSSQLIdentityInsert('groups', true);
                    $group->save();
                    switchMSSQLIdentityInsert('groups', false);
                }
            }
            $first = true;
            foreach ($aLanguages as $sLanguage)
            {
                $oResult = $oQuestionGroup = QuestionGroup::model()->findByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'language' => $sLanguage));
                $aData['aGroupData'][$sLanguage] = $oResult->attributes;
                $aTabTitles[$sLanguage] = getLanguageNameFromCode($sLanguage, false);
                if ($first)
                {
                    $aTabTitles[$sLanguage].= ' (' . gT("Base language") . ')';
                    $first = false;
                }
            }

            $aData['oQuestionGroup'] = $oQuestionGroup;
            $aData['sidemenu']['questiongroups'] = true;
            $aData['questiongroupbar']['buttonspreview'] = true;
            $aData['questiongroupbar']['savebutton']['form'] = true;
            $aData['questiongroupbar']['saveandclosebutton']['form'] = true;
            $aData['questiongroupbar']['closebutton']['url'] = 'admin/questiongroups/sa/view/surveyid/'.$surveyid.'/gid/'.$gid;  // Close button

            $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'editgroup';
            $aData['surveyid'] = $surveyid;
            $aData['gid'] = $gid;
            $aData['tabtitles'] = $aTabTitles;
            $aData['aBaseLanguage'] = $aBaseLanguage;

            $surveyinfo = Survey::model()->findByPk($iSurveyID)->surveyinfo;
            $aData['title_bar']['title'] = $surveyinfo['surveyls_title']."(".gT("ID").":".$iSurveyID.")";

            ///////////
            // sidemenu
            $aData['sidemenu']['state'] = false;
            $aData['sidemenu']['explorer']['state'] = true;
            $aData['sidemenu']['explorer']['gid'] = (isset($gid))?$gid:false;
            $aData['sidemenu']['explorer']['qid'] = false;

            $this->_renderWrappedTemplate('survey/QuestionGroups', 'editGroup_view', $aData);
        }
        else
        {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
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

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'update'))
        {
            Yii::app()->loadHelper('surveytranslator');

            $grplangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
            $baselang = Survey::model()->findByPk($surveyid)->language;

            array_push($grplangs, $baselang);

            foreach ($grplangs as $grplang)
            {
                if (isset($grplang) && $grplang != "")
                {
                    $group_name = $_POST['group_name_' . $grplang];
                    $group_description = $_POST['description_' . $grplang];

                    $group_name = html_entity_decode($group_name, ENT_QUOTES, "UTF-8");
                    $group_description = html_entity_decode($group_description, ENT_QUOTES, "UTF-8");

                    // Fix bug with FCKEditor saving strange BR types
                    $group_name = fixCKeditorText($group_name);
                    $group_description = fixCKeditorText($group_description);

                    $aData = array(
                        'group_name' => $group_name,
                        'description' => $group_description,
                        'randomization_group' => $_POST['randomization_group'],
                        'grelevance' => $_POST['grelevance'],
                    );
                    $condition = array(
                        'gid' => $gid,
                        'sid' => $surveyid,
                        'language' => $grplang
                    );
                    $group = QuestionGroup::model()->findByAttributes($condition);
                    foreach ($aData as $k => $v)
                        $group->$k = $v;
                    $ugresult = $group->save();
                    if ($ugresult)
                    {
                        $groupsummary = getGroupList($gid, $surveyid);
                    }
                }
            }

            Yii::app()->setFlashMessage(gT("Question group successfully saved."));

            if(Yii::app()->request->getPost('close-after-save') === 'true')
                $this->getController()->redirect(array('admin/questiongroups/sa/view/surveyid/' . $surveyid . '/gid/' . $gid));

            $this->getController()->redirect(array('admin/questiongroups/sa/edit/surveyid/' . $surveyid . '/gid/' . $gid));
        }
        else
        {
            Yii::app()->user->setFlash('error', gT("Access denied"));
            $this->getController()->redirect(Yii::app()->request->urlReferrer);
        }
    }


    /**
     * Generate the json array for question explorer tree.
     * Fancy Tree is waiting for a unindexed array (no count)
     * So we must build an array without index ($jDatas) and export it as Json
     * For readability, we first build an indexed array ($aDatas)
     *
     * @see: http://php.net/manual/en/function.json-encode.php#example-4325  (Non-associative array output as array)
     * @see: https://github.com/mar10/fancytree/wiki/TutorialLoadData#pass-a-javascript-array
     *
     * @param int $surveyid
     * @param string $language
     * @return string (json array)
     */
    public function getGroupExplorerDatas($surveyid, $language)
    {
        $iSurveyID = (int) $surveyid;
        $aGroups   = QuestionGroup::model()->getGroupExplorerDatas($iSurveyID, $language);   // Get an array of Groups and questions
        $aDatas    = array();                                                               // The indexed array

        // Two task :
        // Clean the datas (ellipsize etc)
        foreach($aGroups as $aGroup)
        {
            $aGroupArray = array();

            $aGroupArray["key"]    = $aGroup->gid;                           // The key is used by fancy tree to build the node id.
            $aGroupArray["gid"]    = $aGroup->gid;
            $aGroupArray["title"]  = $aGroup->sanitized_group_name;          //shortening the name is a css thing now          
            $aGroupArray["folder"] = true;                                   // Means it's a node with children
            $aGroupArray["href"] = Yii::app()->createUrl('admin/questiongroups/sa/view/', array('surveyid' => $iSurveyID, 'gid' => $aGroup->gid));                                   // Means it's a node with children
            $aGroupArray['extraClasses']   = 'lsi-tree-group-item';
            $aGroupArray['buttonlinks'] = array(
                "add" => array(
                    'title'  => gT('Add a question to this group'),
                    'url'    => Yii::app()->createUrl('admin/questions/sa/newquestion/', array('surveyid' => $iSurveyID, 'gid' => $aGroup->gid)),
                    'icon'   => 'fa fa-plus-circle',
                    'toggle' => 'tooltip',
                    'cssclasses' => 'btn btn-xs btn-success',

                ),
                "delete" => array(
                    'title'    => gT('Delete this Group'),
                    'url'   => Yii::app()->createUrl('admin/questiongroups/sa/delete/', array('surveyid' => $iSurveyID, 'gid' => $aGroup->gid)),
                    'icon'  => 'fa fa-trash-o',
                    'toggle' => 'modal',
                    'target' => '#confirmation-modal',
                    'cssclasses' => 'btn btn-xs btn-danger deleteNode'
                ),
                "edit" => array(
                    'title'    => gT('Edit this group'),
                    'url'   => Yii::app()->createUrl('admin/questiongroups/sa/edit/', array('surveyid' => $iSurveyID, 'gid' => $aGroup->gid)),
                    'icon'  => 'fa fa-edit',
                    'toggle' => 'tooltip',
                    'cssclasses' => 'btn btn-xs btn-default',
                ),
            );

            foreach ($aGroup['aQuestions'] as $oQuestion)
            {
                $aDatasQuestions = array();                                                 // The indexed array that will contain questions
                $aDatasQuestions["key"]      = $oQuestion->qid;
                $aDatasQuestions["gid"]      = $aGroup->gid;
                $aDatasQuestions["title"]    = "[".$oQuestion->sanitized_title . ']&nbsp;' . $oQuestion->sanitized_question;
                $aDatasQuestions['href']     = Yii::app()->createUrl('admin/questions/sa/view/', array('surveyid' => $surveyid, 'gid' => $aGroup->gid, 'qid' => $oQuestion->qid));
                $aDatasQuestions['toggle']   = 'tooltip';
                $aDatasQuestions['placement']   = 'bottom';
                $aDatasQuestions['extraClasses']   = 'lsi-tree-question-item';
                $aDatasQuestions['buttonlinks'] = array(
                    "delete" => array(
                    'title'    => gT('Delete this Question'),
                    'url'   => Yii::app()->createUrl('admin/questions/sa/delete/', array('surveyid' => $iSurveyID, 'qid' => $oQuestion->qid)),
                    'icon'  => 'fa fa-trash-o',
                    'toggle' => 'modal',
                    'target' => '#confirmation-modal',
                    'cssclasses' => 'btn btn-xs btn-danger deleteNode',
                ),
                "edit" => array(
                    'title'    => gT('Edit this group'),
                    'url'   => Yii::app()->createUrl('admin/questions/sa/editquestion/', array('surveyid' => $iSurveyID, 'qid' => $oQuestion->qid)),
                    'icon'  => 'fa fa-edit',
                    'toggle' => 'tooltip',
                    'cssclasses' => 'btn btn-xs btn-default',
                ),
                );

                $aGroupArray["children"][] = $aDatasQuestions;             // Doing that, we push the questions in the children array, as an unindexed array (no count)
            }
            // Doing that, we push the Group as an unindexed array to jDatas, !IMPORTANT! don't index the jDatas array
            $jDatas[] = $aGroupArray;          
        }

        echo json_encode($jDatas);
    }

    function getQuestionDetailData($surveyid, $language, $gid=null, $qid=null){
        $iSurveyID = (int) $surveyid;
        if($qid === null){
            $jDetailsArray = $this->collectQuestionGroupDetail($surveyid, $language, $gid);
        } else {
            $jDetailsArray = $this->collectQuestionDetail($surveyid, $language, $qid);
        }

        echo json_encode($jDetailsArray);
        Yii::app()->end();
    }
    private function collectQuestionGroupDetail($surveyid, $language, $gid){

        $oQuestionGroup = QuestionGroup::model()->findByPk(array('gid' => $gid, 'language' => $language));
        $jDetailsArray = array(print_r($oQuestionGroup,true));
        $jDetailContent = "<div class='container-center'>
            <dl>
            <dt>".gT('Description')."</dt>
            <dd class='text-right'>&nbsp;".$oQuestionGroup->getGroupDescription($gid,$language)."</dd>
            
            <dt>".gT('Questions')."</dt>
            <dd class='text-right'>&nbsp;".$oQuestionGroup->questionsInGroup."</dd>

            <dt>".gT('Randomization Group')."</dt>
            <dd class='text-right'>&nbsp;".$oQuestionGroup->randomization_group."</dd>

            <dt>".gT('Relevance')."</dt>
            <dd class='text-right'>&nbsp;".LimeExpressionManager::UnitTestConvertConditionsToRelevance($surveyid,$oQuestionGroup->gid)."</dd>

        </dl>";

        $jDetailsArray = array(
            'success' => true,
            'title' => "<p style='white-space: pre-wrap; word-wrap:break-word;'>".$oQuestionGroup->sanitized_group_name."</p>",
            'content' => $jDetailContent
        );
        return $jDetailsArray;
    }
    private function collectQuestionDetail($surveyid, $language, $qid){

        $oQuestion = Question::model()->findByPk(array('qid' => $qid, 'language' => $language));
        LimeExpressionManager::ProcessString("{" . $oQuestion->relevance . "}", $qid);
        $jDetailContent = "<div class='container-center'>
            <dl>
            <dt>".gT('Code')."</dt>
            <dd class='text-right'>&nbsp;".$oQuestion->sanitized_title."</dd>
            
            <dt>".gT('Question type')."</dt>
            <dd class='text-right'>&nbsp;".$oQuestion->typedesc."</dd>

            <dt>".gT('Mandatory')."</dt>
            <dd class='text-right'>&nbsp;".$oQuestion->mandatoryIcon."</dd>

            <dt>".gT('Other')."</dt>
            <dd class='text-right'>&nbsp;".$oQuestion->otherIcon."</dd>

            <dt>".gT('Relevance equation')."</dt>
            <dd class='text-right'>&nbsp;".LimeExpressionManager::GetLastPrettyPrintExpression()."</dd>
        </dl>";

        $jDetailsArray = array(
            'success' => true,
            'title' => "<p style='white-space: pre-wrap; word-wrap:break-word;'>".$oQuestion->sanitized_question."</p>",
            'content' => $jDetailContent
        );
        return $jDetailsArray;
    }
    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'survey/QuestionGroups', $aViewUrls = array(), $aData = array())
    {
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}
