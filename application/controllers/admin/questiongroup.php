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
 *	$Id$
 */

/**
 * questiongroup
 *
 * @package LimeSurvey
 * @author
 * @copyright 2011
 * @version $Id$
 * @access public
 */
class questiongroup extends Survey_Common_Action
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
        $surveyid = $_POST['sid'];
        $clang = $this->getController()->lang;

        if ($action == 'importgroup')
        {
            $importgroup = "\n";
            $importgroup .= "\n";

            $sFullFilepath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $_FILES['the_file']['name'];
            $aPathInfo = pathinfo($sFullFilepath);
            $sExtension = $aPathInfo['extension'];

            if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath))
            {
                $fatalerror = sprintf($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."), $this->config->item('tempdir'));
            }

            // validate that we have a SID
            if (!returnglobal('sid'))
                $fatalerror .= $clang->gT("No SID (Survey) has been provided. Cannot import question.");

            if (isset($fatalerror))
            {
                @unlink($sFullFilepath);
                $this->getController()->error($fatalerror);
            }

            Yii::app()->loadHelper('admin/import');

            // IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY
            if (strtolower($sExtension) == 'csv')
                $aImportResults = CSVImportGroup($sFullFilepath, $surveyid);
            elseif (strtolower($sExtension) == 'lsg')
                $aImportResults = XMLImportGroup($sFullFilepath, $surveyid);
            else
                $this->getController()->error('Unknown file extension');
            FixLanguageConsistency($surveyid);

            if (isset($aImportResults['fatalerror']))
            {
                unlink($sFullFilepath);
                $this->getController()->error($aImportResults['fatalerror']);
            }

            unlink($sFullFilepath);

            $aData['display'] = $importgroup;
            $aData['surveyid'] = $surveyid;
            $aData['aImportResults'] = $aImportResults;
            $aData['sExtension'] = $sExtension;
            $aData['display']['menu_bars']['surveysummary'] = 'importgroup';

            $this->_renderWrappedTemplate('QuestionGroups/import_view', $aData);
            // TMSW Conditions->Relevance:  call LEM->ConvertConditionsToRelevance() after import
        }
    }

    /**
     * questiongroup::add()
     * Load add new question group screen.
     * @return
     */
    function add($surveyid)
    {
        $surveyid = sanitize_int($surveyid);
        $aViewUrls = $aData = array();

        if (bHasSurveyPermission($surveyid, 'surveycontent', 'read'))
        {
            $clang = $this->getController()->lang;

            $_SESSION['FileManagerContext'] = "create:group:{$surveyid}";

            Yii::app()->loadHelper('admin/htmleditor');
            Yii::app()->loadHelper('surveytranslator');
            $grplangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
            $baselang = Survey::model()->findByPk($surveyid)->language;
            $grplangs[] = $baselang;
            $grplangs = array_reverse($grplangs);

            $aData['display']['menu_bars']['surveysummary'] = 'addgroup';
            $aData['surveyid'] = $surveyid;
            $aData['action'] = $action;
            $aData['grplangs'] = $grplangs;
            $aData['baselang'] = $baselang;
            $aViewUrls = 'QuestionGroups/addGroup_view';

            $this->_renderWrappedTemplate($aViewUrls, $aData);
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
        if (bHasSurveyPermission($surveyid, 'surveycontent', 'create'))
        {
            Yii::app()->loadHelper('surveytranslator');

            $grplangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
            $baselang = Survey::model()->findByPk($surveyid)->language;

            $grplangs[] = $baselang;
            $errorstring = '';
            foreach ($grplangs as $grouplang)
                if (empty($_POST['group_name_' . $grouplang]))
                    $errorstring.= GetLanguageNameFromCode($grouplang, false) . "\\n";

            if ($errorstring != '')
                $this->getController()->redirect($this->getController()->createUrl('admin/survey/view/surveyid/' . $surveyid));

            else
            {
                $first = true;
                foreach ($grplangs as $grouplang)
                {
                    //Clean XSS
                    $group_name = $_POST['group_name_' . $grouplang];
                    $group_description = $_POST['description_' . $grouplang];

                    $group_name = html_entity_decode($group_name, ENT_QUOTES, "UTF-8");
                    $group_description = html_entity_decode($group_description, ENT_QUOTES, "UTF-8");

                    // Fix bug with FCKEditor saving strange BR types
                    $group_name = fix_FCKeditor_text($group_name);
                    $group_description = fix_FCKeditor_text($group_description);


                    if ($first)
                    {
                        $aData = array(
                            'sid' => $surveyid,
                            'group_name' => $group_name,
                            'description' => $group_description,
                            'group_order' => getMaxgrouporder($surveyid),
                            'language' => $grouplang,
                            'randomization_group' => $_POST['randomization_group'],
                        );

                        $group = new Groups;
                        foreach ($aData as $k => $v)
                            $group->$k = $v;
                        $group->save();
                        $groupid = Yii::app()->db->getLastInsertID();
                        $first = false;
                    }
                    else
                    {
                        //db_switchIDInsert('groups',true);
                        $aData = array(
                            'gid' => $groupid,
                            'sid' => $surveyid,
                            'group_name' => $group_name,
                            'description' => $group_description,
                            'group_order' => getMaxgrouporder($surveyid),
                            'language' => $grouplang,
                            'randomization_group' => $_POST['randomization_group']
                        );

                        $group = new Groups;
                        foreach ($aData as $k => $v)
                            $group->$k = $v;
                        $group->save();
                    }
                }
                // This line sets the newly inserted group as the new group
                if (isset($groupid))
                    $gid = $groupid;
                Yii::app()->session['flashmessage'] = Yii::app()->lang->gT("New question group was saved.");
            }
            $this->getController()->redirect($this->getController()->createUrl('admin/survey/view/surveyid/' . $surveyid . '/gid/' . $gid));
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

        if (bHasSurveyPermission($iSurveyId, 'surveycontent', 'delete'))
        {
            LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyId);

            $iGroupId = sanitize_int($iGroupId);
            $clang = $this->getController()->lang;

            $iGroupsDeleted = Groups::deleteWithDependency($iGroupId, $iSurveyId);

            if ($iGroupsDeleted !== 1)
            {
                fixSortOrderGroups($iSurveyId);
                Yii::app()->user->setFlash('flashmessage', $clang->gT('The question group was deleted.'));
            }
            else
                Yii::app()->user->setFlash('flashmessage', $clang->gT('Group could not be deleted'));

            $this->getController()->redirect($this->getController()->createUrl('admin/survey/view/surveyid/' . $iSurveyId));

            LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyId);
        }
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
        $clang = $this->getController()->lang;
        $surveyid = sanitize_int($surveyid);
        $gid = sanitize_int($gid);
        $aViewUrls = $aData = array();

        if (bHasSurveyPermission($surveyid, 'surveycontent', 'read'))
        {
            $_SESSION['FileManagerContext'] = "edit:group:{$surveyid}";

            Yii::app()->loadHelper('admin/htmleditor');
            Yii::app()->loadHelper('surveytranslator');

            $aAdditionalLanguages = Survey::model()->findByPk($surveyid)->additionalLanguages;
            $aBaseLanguage = Survey::model()->findByPk($surveyid)->language;

            $aLanguages = array_merge(array($aBaseLanguage), $aAdditionalLanguages);

            $grplangs = array_flip($aLanguages);

            // Check out the intgrity of the language versions of this group
            $egresult = Groups::model()->findAllByAttributes(array('sid' => $surveyid, 'gid' => $gid));
            foreach ($egresult as $esrow)
            {
                $esrow = $esrow->attributes;

                // Language Exists, BUT ITS NOT ON THE SURVEY ANYMORE
                if (!in_array($esrow['language'], $aLanguages))
                {
                    Groups::model()->deleteAllByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'language' => $esrow['language']));
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
                    $group = new Groups;
                    foreach ($basesettings as $k => $v)
                        $group->$k = $v;
                    $group->save();
                }
            }
            $first = true;
            foreach ($aLanguages as $sLanguage)
            {
                $oResult = Groups::model()->findByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'language' => $sLanguage));
                $aData['aGroupData'][$sLanguage] = $oResult->attributes;
                $aTabTitles[$sLanguage] = getLanguageNameFromCode($sLanguage, false);
                if ($first)
                {
                    $aTabTitles[$sLanguage].= ' (' . $clang->gT("Base language") . ')';
                    $first = false;
                }
            }

            $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'editgroup';
            $aData['surveyid'] = $surveyid;
            $aData['gid'] = $gid;
            $aData['tabtitles'] = $aTabTitles;
            $aData['aBaseLanguage'] = $aBaseLanguage;

            $this->_renderWrappedTemplate('QuestionGroups/editGroup_view', $aData);
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

        $group = Groups::model()->findByAttributes(array('gid' => $gid));
        $surveyid = $group->sid;

        if (bHasSurveyPermission($surveyid, 'surveycontent', 'update'))
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
                    $group_name = fix_FCKeditor_text($group_name);
                    $group_description = fix_FCKeditor_text($group_description);

                    $aData = array(
                        'group_name' => $group_name,
                        'description' => $group_description,
                        'randomization_group' => $_POST['randomization_group'],
                    );
                    $condition = array(
                        'gid' => $gid,
                        'sid' => $surveyid,
                        'language' => $grplang
                    );
                    $group = Groups::model()->findByAttributes($condition);
                    foreach ($aData as $k => $v)
                        $group->$k = $v;
                    $ugresult = $group->save();
                    if ($ugresult)
                    {
                        $groupsummary = getgrouplist($gid, $surveyid);
                    }
                }
            }

            Yii::app()->session['flashmessage'] = Yii::app()->lang->gT("Question group successfully saved.");
            $this->getController()->redirect($this->getController()->createUrl('admin/survey/view/surveyid/' . $surveyid . '/gid/' . $gid));
        }
    }

    /**
     * questiongroup::organize()
     * Load ordering of question group screen.
     * @return
     */
    public function organize($iSurveyId)
    {
        $iSurveyId = (int)$iSurveyId;

        if (!empty($_POST['orgdata']) && bHasSurveyPermission($iSurveyId, 'surveycontent', 'update'))
        {
            $this->_reorderGroup($iSurveyId);
        }
        else
        {
            $this->_showReorderForm($iSurveyId);
        }
    }

    private function _showReorderForm($iSurveyId)
    {
        // Prepare data for the view
        $sBaseLanguage = Survey::model()->findByPk($iSurveyId)->language;

        LimeExpressionManager::StartProcessingPage(true, Yii::app()->baseUrl);

        $aGrouplist = Groups::model()->getGroups($iSurveyId);
        $initializedReplacementFields = false;

        foreach ($aGrouplist as $iGID => $aGroup)
        {
            LimeExpressionManager::StartProcessingGroup($aGroup['gid'], false, $iSurveyId);
            if (!$initializedReplacementFields) {
                templatereplace("{SITENAME}"); // Hack to ensure the EM sets values of LimeReplacementFields
                $initializedReplacementFields = true;
            }

            $oQuestionData = Questions::model()->getQuestions($iSurveyId, $aGroup['gid'], $sBaseLanguage);

            $qs = array();
            $junk = array();

            foreach ($oQuestionData->readAll() as $q)
            {
                $relevance = ($q['relevance'] == '') ? 1 : $q['relevance'];
                $question = '[{' . $relevance . '}] ' . $q['question'];
                LimeExpressionManager::ProcessString($question, $q['qid']);
                $q['question'] = LimeExpressionManager::GetLastPrettyPrintExpression();
                $q['gid'] = $aGroup['gid'];
                $qs[] = $q;
            }
            $aGrouplist[$iGID]['questions'] = $qs;
            LimeExpressionManager::FinishProcessingGroup();
        }
        LimeExpressionManager::FinishProcessingPage();

        $aData['aGroupsAndQuestions'] = $aGrouplist;
        $aData['surveyid'] = $iSurveyId;

        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.ui.nestedSortable.js');
        $this->getController()->_js_admin_includes(Yii::app()->getConfig('generalscripts') . 'admin/organize.js');

        $this->_renderWrappedTemplate('organizeGroupsAndQuestions_view', $aData);
    }

    private function _reorderGroup($iSurveyId)
    {
        $AOrgData = array();
        parse_str($_POST['orgdata'], $AOrgData);
        $grouporder = 0;
        foreach ($AOrgData['list'] as $ID => $parent)
        {
            if ($parent == 'root' && $ID[0] == 'g') {
                Groups::model()->updateAll(array('group_order' => $grouporder), array('gid' => (int)substr($ID, 1)));
                $grouporder++;
            }
            elseif ($ID[0] == 'q')
            {
                if (!isset($questionorder[(int)substr($parent, 1)]))
                    $questionorder[(int)substr($parent, 1)] = 0;

                Questions::model()->updateAll(array('question_order' => $questionorder[(int)substr($parent, 1)], 'gid' => (int)substr($parent, 1)), array('qid' => (int)substr($ID, 1)));

                Questions::model()->updateAll(array('gid' => (int)substr($parent, 1)), array('parent_qid' => (int)substr($ID, 1)));

                $questionorder[(int)substr($parent, 1)]++;
            }
        }
        LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting
        Yii::app()->session['flashmessage'] = Yii::app()->lang->gT("The new question group/question order was successfully saved.");
        $this->getController()->redirect($this->getController()->createUrl('admin/survey/view/surveyid/' . $iSurveyId));
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($aViewUrls = array(), $aData = array())
    {
        $this->getController()->_css_admin_includes(Yii::app()->getConfig('styleurl') . "admin/default/superfish.css");
        parent::_renderWrappedTemplate('survey', $aViewUrls, $aData);
    }
}