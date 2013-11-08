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
        $surveyid = $_POST['sid'];
        $clang = $this->getController()->lang;

        if ($action == 'importgroup')
        {
            $importgroup = "\n";
            $importgroup .= "\n";

            $sFullFilepath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . randomChars(20);
            $aPathInfo = pathinfo($_FILES['the_file']['name']);
            $sExtension = $aPathInfo['extension'];

            if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath))
            {
                $fatalerror = sprintf($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."), $this->config->item('tempdir'));
            }

            // validate that we have a SID
            if (!returnGlobal('sid'))
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
            LimeExpressionManager::SetDirtyFlag(); // so refreshes syntax highlighting
            fixLanguageConsistency($surveyid);

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
            //$aData['display']['menu_bars']['surveysummary'] = 'importgroup';

            $this->_renderWrappedTemplate('survey/QuestionGroups', 'import_view', $aData);
            // TMSW Condition->Relevance:  call LEM->ConvertConditionsToRelevance() after import
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

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'read'))
        {
            $clang = $this->getController()->lang;

            Yii::app()->session['FileManagerContext'] = "create:group:{$surveyid}";

            Yii::app()->loadHelper('admin/htmleditor');
            Yii::app()->loadHelper('surveytranslator');
            $grplangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
            $baselang = Survey::model()->findByPk($surveyid)->language;
            $grplangs[] = $baselang;
            $grplangs = array_reverse($grplangs);
            App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('adminscripts') . 'questiongroup.js');

            $aData['display']['menu_bars']['surveysummary'] = 'addgroup';
            $aData['surveyid'] = $surveyid;
            $aData['action'] = $aData['display']['menu_bars']['gid_action'] = 'addgroup';
            $aData['grplangs'] = $grplangs;
            $aData['baselang'] = $baselang;

            $this->_renderWrappedTemplate('survey/QuestionGroups', 'addGroup_view', $aData);
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

            $grplangs = Survey::model()->findByPk($surveyid)->additionalLanguages;
            $baselang = Survey::model()->findByPk($surveyid)->language;

            $grplangs[] = $baselang;
            $errorstring = '';
            foreach ($grplangs as $grouplang)
                if (empty($_POST['group_name_' . $grouplang]))
                    $errorstring.= getLanguageNameFromCode($grouplang, false) . "\\n";

            if ($errorstring != '')
                $this->getController()->redirect(array('admin/survey/sa/view/surveyid/' . $surveyid));

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
                    $group_name = fixCKeditorText($group_name);
                    $group_description = fixCKeditorText($group_description);


                    if ($first)
                    {
                        $aData = array(
                            'sid' => $surveyid,
                            'group_name' => $group_name,
                            'description' => $group_description,
                            'group_order' => getMaxGroupOrder($surveyid),
                            'language' => $grouplang,
                            'randomization_group' => $_POST['randomization_group'],
                            'grelevance' => $_POST['grelevance'],
                        );

                        $group = new QuestionGroup;
                        foreach ($aData as $k => $v)
                            $group->$k = $v;
                        $group->save();
                        $groupid = $group->gid;
                        $first = false;
                    }
                    else
                    {
                        switchMSSQLIdentityInsert('groups',true);
                        $aData = array(
                            'gid' => $groupid,
                            'sid' => $surveyid,
                            'group_name' => $group_name,
                            'description' => $group_description,
                            'group_order' => getMaxGroupOrder($surveyid),
                            'language' => $grouplang,
                            'randomization_group' => $_POST['randomization_group']
                        );

                        $group = new QuestionGroup;
                        foreach ($aData as $k => $v)
                            $group->$k = $v;
                        $group->save();
                        switchMSSQLIdentityInsert('groups',false);
                    }
                }
                // This line sets the newly inserted group as the new group
                if (isset($groupid))
                    $gid = $groupid;
                Yii::app()->session['flashmessage'] = Yii::app()->lang->gT("New question group was saved.");
            }
            $this->getController()->redirect(array('admin/survey/sa/view/surveyid/' . $surveyid . '/gid/' . $gid));
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
            $clang = $this->getController()->lang;

            $iGroupsDeleted = QuestionGroup::deleteWithDependency($iGroupId, $iSurveyId);

            if ($iGroupsDeleted > 0)
            {
                fixSortOrderGroups($iSurveyId);
                Yii::app()->setFlashMessage($clang->gT('The question group was deleted.'));
            }
            else
                Yii::app()->setFlashMessage($clang->gT('Group could not be deleted'),'error');
            LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyId);
            $this->getController()->redirect(array('admin/survey/sa/view/surveyid/' . $iSurveyId));
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

        if (Permission::model()->hasSurveyPermission($surveyid, 'surveycontent', 'read'))
        {
            Yii::app()->session['FileManagerContext'] = "edit:group:{$surveyid}";

            Yii::app()->loadHelper('admin/htmleditor');
            Yii::app()->loadHelper('surveytranslator');

            $aAdditionalLanguages = Survey::model()->findByPk($surveyid)->additionalLanguages;
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
                $oResult = QuestionGroup::model()->findByAttributes(array('sid' => $surveyid, 'gid' => $gid, 'language' => $sLanguage));
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

            $this->_renderWrappedTemplate('survey/QuestionGroups', 'editGroup_view', $aData);
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

            Yii::app()->session['flashmessage'] = Yii::app()->lang->gT("Question group successfully saved.");
            $this->getController()->redirect(array('admin/survey/sa/view/surveyid/' . $surveyid . '/gid/' . $gid));
        }
    }

    /**
     * Renders template(s) wrapped in header and footer
     *
     * @param string $sAction Current action, the folder to fetch views from
     * @param string|array $aViewUrls View url(s)
     * @param array $aData Data to be passed on. Optional.
     */
    protected function _renderWrappedTemplate($sAction = 'survey/QuestionGroups', $aViewUrls = array(), $aData = array())
    {
        App()->getClientScript()->registerPackage('jquery-superfish');
        parent::_renderWrappedTemplate($sAction, $aViewUrls, $aData);
    }
}