<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* LimeSurvey (tm)
* Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*
*/

/**
* questiongroup
*
* @package LimeSurvey
* @author
* @copyright 2011
* @version $Id: questiongroup.php 11328 2011-11-04 20:46:49Z tmswhite $
* @access public
*/
class questiongroup extends Survey_Common_Action {

    /**
     * Eoutes to the current sub-question
     *
     * @access public
     * @param string $sa
     * @return void
     */
	public function run($sa)
	{
		if ($sa == 'add')
			$this->route('add', array('surveyid'));
		elseif ($sa == 'insert')
			$this->route('insert', array('surveyid'));
		elseif ($sa == 'edit')
			$this->route('edit', array('surveyid', 'gid'));
		elseif ($sa == 'update')
			$this->route('update', array('gid'));
		elseif ($sa == 'import')
			$this->route('import', array());
		elseif ($sa == 'organize')
			$this->route('organize', array('surveyid'));
		elseif ($sa == 'delete')
			$this->route('delete', array());
	}

    /**
    * questiongroup::import()
    * Function responsible to import a question group.
    * @return
    */
    function import()
    {
        $action = $_POST['action'];
        $surveyid = $_POST['sid'];
        $clang = $this->controller->lang;
        $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
        Yii::app()->setConfig("css_admin_includes", $css_admin_includes);
        $this->controller->_getAdminHeader();
        $this->controller->_showadminmenu($surveyid);
        $this->_surveybar($surveyid,NULL);
        $this->_surveysummary($surveyid,"importgroup");

        if ($action == 'importgroup')
        {
            $importgroup = "<div class='header ui-widget-header'>".$clang->gT("Import question group")."</div>\n";
            $importgroup .= "<div class='messagebox ui-corner-all'>\n";

            $sFullFilepath = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR . $_FILES['the_file']['name'];
            $aPathInfo = pathinfo($sFullFilepath);
            $sExtension = $aPathInfo['extension'];

            if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $sFullFilepath))
            {
                $fatalerror = sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$this->config->item('tempdir'));
            }

            // validate that we have a SID
            if (!returnglobal('sid'))
            {
                $fatalerror .= $clang->gT("No SID (Survey) has been provided. Cannot import question.");
            }
            /**else
            {
            $surveyid=returnglobal('sid');
            }*/

            if (isset($fatalerror))
            {
                $importgroup .= "<div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
                $importgroup .= $fatalerror."<br /><br />\n";
                $importgroup .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('" . Yii::app()->baseUrl . "', '_top')\" /><br /><br />\n";
                $importgroup .= "</div>\n";
                @unlink($sFullFilepath);
                show_error($importgroup);
                return;
            }

            Yii::app()->loadHelper('admin/import');

            // IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY
            $importgroup .= "<div class='successheader'>".$clang->gT("Success")."</div>&nbsp;<br />\n"
            .$clang->gT("File upload succeeded.")."<br /><br />\n"
            .$clang->gT("Reading file..")."<br /><br />\n";
            if (strtolower($sExtension)=='csv')
            {
                $aImportResults=CSVImportGroup($sFullFilepath, $surveyid);
            }
            elseif (strtolower($sExtension)=='lsg')
            {
                $aImportResults=XMLImportGroup($sFullFilepath, $surveyid);
            }
            else die('Unknown file extension');
            FixLanguageConsistency($surveyid);

            if (isset($aImportResults['fatalerror']))
            {
                $importgroup .= "<div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
                $importgroup .= $aImportResults['fatalerror']."<br /><br />\n";
                $importgroup .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('" . Yii::app()->baseUrl . "', '_top')\" />\n";
                $importgroup .=  "</div>\n";
                unlink($sFullFilepath);
                $this->controller->_showMessageBox('fatal error', $importgroup);
                return;
            }

            $importgroup .= "<div class='successheader'>".$clang->gT("Success")."</div><br />\n"
            ."<strong><u>".$clang->gT("Question group import summary")."</u></strong><br />\n"
            ."<ul style=\"text-align:left;\">\n"
            ."\t<li>".$clang->gT("Groups").": ".$aImportResults['groups']."</li>\n"
            ."\t<li>".$clang->gT("Questions").": ".$aImportResults['questions']."</li>\n"
            ."\t<li>".$clang->gT("Subquestions").": ".$aImportResults['subquestions']."</li>\n"
            ."\t<li>".$clang->gT("Answers").": ".$aImportResults['answers']."</li>\n"
            ."\t<li>".$clang->gT("Conditions").": ".$aImportResults['conditions']."</li>\n";
            if (strtolower($sExtension)=='csv')  {
                $importgroup.="\t<li>".$clang->gT("Label sets").": ".$aImportResults['labelsets']." (".$aImportResults['labels'].")</li>\n";
            }
            $importgroup.="\t<li>".$clang->gT("Question attributes:").$aImportResults['question_attributes']."</li>"
            ."</ul>\n";

            $importgroup .= "<strong>".$clang->gT("Question group import is complete.")."</strong><br />&nbsp;\n";
            $importgroup .= "<input type='submit' value='".$clang->gT("Go to question group")."' onclick=\"window.open('".$this->controller->createUrl('admin/survey/view/'.$surveyid.'/'.$aImportResults['newgid'])."', '_top')\" />\n";
            $importgroup .= "</div><br />\n";

            unlink($sFullFilepath);

            $data['display'] = $importgroup;
            $this->getController()->render('/survey_view',$data);
            // TMSW Conditions->Relevance:  call LEM->ConvertConditionsToRelevance() after import



        }
        $this->getController()->_loadEndScripts();

        $this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
    }


    /**
    * questiongroup::add()
    * Load add new question grup screen.
    * @return
    */
    function add($surveyid)
    {
        $surveyid = sanitize_int($surveyid);

        if (bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            $action = "addgroup";//$this->input->post('action');
            $clang = $this->controller->lang;

            $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
            Yii::app()->setConfig("css_admin_includes", $css_admin_includes);
            $this->getController()->_getAdminHeader();
            $this->getController()->_showadminmenu($surveyid);
            $this->_surveybar($surveyid);
            $this->_surveysummary($surveyid, "addgroup");
            if ($action == "addgroup")
            {
                Yii::app()->loadHelper('admin/htmleditor');
                Yii::app()->loadHelper('surveytranslator');
                $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                $baselang = GetBaseLanguageFromSurveyID($surveyid);
                $grplangs[] = $baselang;
                $grplangs = array_reverse($grplangs);

                $data['clang'] = $clang;
                $data['surveyid'] = $surveyid;
                $data['action'] = $action;
                $data['grplangs'] = $grplangs;
            	$data['baselang'] = $baselang;
                $this->getController()->render('/admin/survey/QuestionGroups/addGroup_view',$data);
            }

            $this->getController()->_loadEndScripts();

            $this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
        }
    }


    /**
    * Insert the new group to the database
    *
    * @param mixed $surveyid
    */
    function insert($surveyid)
    {
        if (bHasSurveyPermission($surveyid, 'surveycontent','create'))
        {
            Yii::app()->loadHelper('surveytranslator');

            $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            $grplangs[] = $baselang;
            $errorstring = '';
            foreach ($grplangs as $grouplang)
            {
                if (empty($_POST['group_name_'.$grouplang])) { $errorstring.= GetLanguageNameFromCode($grouplang,false)."\\n";}
            }
            if ($errorstring!='')
            {
                $databaseoutput = "<script type=\"text/javascript\">\n<!--\n alert(\"".$this->controller->lang->gT("Group could not be added.\\n\\nIt is missing the group name for the following languages","js").":\\n".$errorstring."\")\n //-->\n</script>\n";
            	$this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$surveyid));
            }

            else
            {
                $first=true;
                foreach ($grplangs as $grouplang)
                {
                    //Clean XSS
                    $group_name = $_POST['group_name_'.$grouplang];
                    $group_description = $_POST['description_'.$grouplang];

                    $group_name = html_entity_decode($group_name, ENT_QUOTES, "UTF-8");
                    $group_description = html_entity_decode($group_description, ENT_QUOTES, "UTF-8");

                    // Fix bug with FCKEditor saving strange BR types
                    $group_name=fix_FCKeditor_text($group_name);
                    $group_description=fix_FCKeditor_text($group_description);


                    if ($first)
                    {
                        $data = array (
	                        'sid' => $surveyid,
	                        'group_name' => $group_name,
	                        'description' => $group_description,
	                        'group_order' => getMaxgrouporder($surveyid),
	                        'language' => $grouplang,
	                        'randomization_group' => $_POST['randomization_group'],
                        );

                    	$group = new Groups;
                    	foreach ($data as $k => $v)
                    		$group->$k = $v;
                    	$result = $group->save();
                        $groupid = Yii::app()->db->getLastInsertID();
                        $first=false;

                    }
                    else{
                        //db_switchIDInsert('groups',true);
                        $data = array (
	                        'gid' => $groupid,
	                        'sid' => $surveyid,
	                        'group_name' => $group_name,
	                        'description' => $group_description,
	                        'group_order' => getMaxgrouporder($surveyid),
	                        'language' => $grouplang,
	                        'randomization_group' => $_POST['randomization_group']
                        );

						$group = new Groups;
                    	foreach ($data as $k => $v)
                    		$group->$k = $v;
                    	$group->save();

                        //db_switchIDInsert('groups',false);
                    }
                    if (!$result)
                    {
                        $databaseoutput .= $this->controller->lang->gT("Error: The database reported an error while executing INSERT query in addgroup action in database.php:")."<br />\n";

                        $databaseoutput .= "</body>\n</html>";
                        //exit;
                    }
                }
                // This line sets the newly inserted group as the new group
                if (isset($groupid)){$gid=$groupid;}
                Yii::app()->session['flashmessage'] = Yii::app()->lang->gT("New question group was saved.");

            }
            $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$surveyid.'/gid/'.$gid));
        }
    }


    /**
    * Action to delete a question group.
    */
    function delete()
    {
        $surveyId = sanitize_int($_GET['surveyid']);
        if (bHasSurveyPermission($surveyId, 'surveycontent', 'delete')) {
            $groupId = sanitize_int($_GET['gid']);
            $clang = $this->getController()->lang;

            if (isset($_GET['sa']) && $_GET['sa'] == 'delete') {
                $iGroupsDeleted = Groups::deleteWithDependency($groupId, $surveyId);

                if ($iGroupsDeleted !== 1) {
                    fixSortOrderGroups($surveyId);
                    Yii::app()->user->setFlash('flashmessage', $clang->gT('The question group was deleted.'));
                }
                else
                {
                    Yii::app()->user->setFlash('flashmessage', $clang->gT('Group could not be deleted'));
                }
                $this->getController()->redirect($this->getController()->createUrl('admin/survey/view/' . $surveyId));
            }
        }
    }

    /**
    * questiongroup::edit()
    * Load editing of a question group screen.
    * @return
    */
    function edit($surveyid, $gid)
    {
        $surveyid = sanitize_int($surveyid);
        $gid = sanitize_int($gid);

        if (bHasSurveyPermission($surveyid,'surveycontent','read'))
        {

            $action = "editgroup";//$this->input->post('action');
            $clang = $this->controller->lang;

            $css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
            Yii::app()->setConfig("css_admin_includes", $css_admin_includes);
            $this->controller->_getAdminHeader($surveyid);
            $this->controller->_showadminmenu($surveyid, $gid);
            $this->_surveybar($surveyid,$gid);

            if ($action == "editgroup")
            {
                Yii::app()->loadHelper('admin/htmleditor');
                Yii::app()->loadHelper('surveytranslator');

                $aAdditionalLanguages = GetAdditionalLanguagesFromSurveyID($surveyid);
                $aBaseLanguage = GetBaseLanguageFromSurveyID($surveyid);

                $aLanguages = array_merge(array($aBaseLanguage),$aAdditionalLanguages);

                $grplangs = array_flip($aLanguages);
                // Check out the intgrity of the language versions of this group
                $egquery = "SELECT * FROM {{groups}} WHERE sid=$surveyid AND gid=$gid";
                $egresult = Yii::app()->db->createCommand($egquery)->query();
                foreach ($egresult->readAll() as $esrow)
                {
                    if(!in_array($esrow['language'], $aLanguages)) // Language Exists, BUT ITS NOT ON THE SURVEY ANYMORE.
                    {
                        $egquery = "DELETE FROM {{groups}} WHERE sid='{$surveyid}' AND gid='{$gid}' AND language='".$esrow['language']."'";
                        $egresultD = Yii::app()->db->createCommand($egquery)->execute();
                    } else {
                        $grplangs[$esrow['language']] = 'exists';
                    }
                    if ($esrow['language'] == $aBaseLanguage) $basesettings = $esrow;
                }

                // Create groups in missing languages
                while (list($key,$value) = each($grplangs))
                {
                    if ($value != 'exists')
                    {
                        $basesettings['language']=$key;
                    	$group = new Groups;
                    	foreach ($basesettings as $k => $v)
                    		$group->$k = $v;
                    	$group->save();
                    }
                }
                $first=true;
                foreach ($aLanguages as $sLanguage)
                {
                    $oResult=Groups::model()->findByAttributes(array('sid'=>$surveyid,'gid'=>$gid,'language'=>$sLanguage));
                    $data['aGroupData'][$sLanguage]=$oResult->attributes;
                    $aTabTitles[$sLanguage] = getLanguageNameFromCode($sLanguage,false);
                    if($first){
                        $aTabTitles[$sLanguage].= ' ('.$clang->gT("Base language").')';
                        $first=false;
                    }

                }
                $data['action'] = "editgroup";
                $data['clang'] = $clang;
                $data['surveyid'] = $surveyid;
                $data['gid'] = $gid;
                $data['tabtitles']=$aTabTitles;
                $data['aBaseLanguage']=$aBaseLanguage;


                $this->getController()->render('/admin/survey/QuestionGroups/editGroup_view',$data);
            }
        }
        $this->controller->_loadEndScripts();

        $this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->controller->lang->gT("LimeSurvey online manual"));
    }

    function update($gid)
    {
        $gid = (int) $gid;

    	$group = Groups::model()->findByAttributes(array('gid' => $gid));
    	$surveyid = $group->sid;

        if (bHasSurveyPermission($surveyid, 'surveycontent','update'))
        {
            Yii::app()->loadHelper('surveytranslator');

            $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);

            array_push($grplangs, $baselang);

            foreach ($grplangs as $grplang)
            {
                if (isset($grplang) && $grplang != "")
                {
                    $group_name = $_POST['group_name_'.$grplang];
                    $group_description = $_POST['description_' . $grplang];

                    $group_name = html_entity_decode($group_name, ENT_QUOTES, "UTF-8");
                    $group_description = html_entity_decode($group_description, ENT_QUOTES, "UTF-8");

                    // Fix bug with FCKEditor saving strange BR types
                    $group_name=fix_FCKeditor_text($group_name);
                    $group_description=fix_FCKeditor_text($group_description);

                    $data = array(
	                    'group_name' => $group_name,
	                    'description' => $group_description,
	                    'randomization_group' => $_POST['randomization_group'],
                    );
                    $condition = array (
	                    'gid' => $gid,
	                    'sid' => $surveyid,
	                    'language' => $grplang
                    );
                    $group = Groups::model()->findByAttributes($condition);
                	foreach ($data as $k => $v)
                		$group->$k = $v;
                	$ugresult = $group->save();
                    if ($ugresult)
                    {
                        $groupsummary = getgrouplist($gid,$surveyid);
                    }
                    else
                    {
                        $databaseoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Group could not be updated","js")."\")\n //-->\n</script>\n";

                    }
                }
            }
            Yii::app()->session['flashmessage'] = Yii::app()->lang->gT("Question group successfully saved.");
            $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$surveyid.'/gid/'.$gid));
        }
    }

    /**
    * questiongroup::organize()
    * Load ordering of question group screen.
    * @return
    */
    function organize($iSurveyID)
    {
        $iSurveyID = (int) $iSurveyID;

        if (!empty($_POST['orgdata']) && bHasSurveyPermission($iSurveyID,'surveycontent','update'))
        {
            $AOrgData = array();
            parse_str($_POST['orgdata'], $AOrgData);
            $grouporder = 0;
            foreach($AOrgData['list'] as $ID=>$parent)
            {
                if ($parent=='root' && $ID[0]=='g'){
                    Groups::model()->update(array('group_order'=>$grouporder), 'gid='.(int)substr($ID,1));
                    $grouporder++;
                }
                elseif ($ID[0]=='q')
                {
                    if (!isset($questionorder[(int)substr($parent,1)])) $questionorder[(int)substr($parent,1)]=0;
					Questions::model()->updateAll(array('question_order' => $questionorder[(int)substr($parent,1)], 'gid' => (int)substr($parent,1)), 'qid=' . (int) substr($ID, 1));
					Questions::model()->updateAll(array('gid'=>(int)substr($parent,1)), 'parent_qid=' . (int) substr($ID, 1));
                    $questionorder[(int)substr($parent,1)]++;
                }
            }
            Yii::app()->session['flashmessage'] = Yii::app()->lang->gT("The new question group/question order was successfully saved.");
            $this->getController()->redirect($this->getController()->createUrl('admin/survey/sa/view/surveyid/'.$iSurveyID));
        }

        // Prepare data for the view
        $sBaseLanguage = GetBaseLanguageFromSurveyID($iSurveyID);

        LimeExpressionManager::StartProcessingPage(false,true,false);

        $aGrouplist = Groups::model()->getGroups($iSurveyID);
        $initializedReplacementFields = false;

        foreach($aGrouplist as $iGID => $aGroup)
        {
            LimeExpressionManager::StartProcessingGroup($aGroup['gid'],false,$iSurveyID);
            if (!$initializedReplacementFields) {
                templatereplace("{SITENAME}");  // Hack to ensure the EM sets values of LimeReplacementFields
                $initializedReplacementFields = true;
            }

            $oQuestionData=Questions::model()->getQuestions($iSurveyID,$aGroup['gid'],$sBaseLanguage);

			$qs = array();
            $junk=array();

			foreach ($oQuestionData->readAll() as $q) {
                $relevance = (trim($q['relevance'])=='') ? 1 : $q['relevance'];
                $question = '[{' . $relevance . '}] ' . $q['question'];
                LimeExpressionManager::ProcessString($question,$q['qid'],$junk,false,1,1);
                $q['question'] = LimeExpressionManager::GetLastPrettyPrintExpression();
                $q['gid'] = $aGroup['gid'];
                $qs[] = $q;
            }
            $aGrouplist[$iGID]['questions']=$qs;
        }
        LimeExpressionManager::FinishProcessingPage();

        $aViewData['aGroupsAndQuestions'] = $aGrouplist;
        $aViewData['clang'] = Yii::app()->lang;
        $aViewData['surveyid'] = $iSurveyID;

		$js_admin_includes   = Yii::app()->getConfig("js_admin_includes");
        $js_admin_includes[] = Yii::app()->getConfig('generalscripts').'jquery/jquery.ui.nestedSortable.js';
        $js_admin_includes[] = Yii::app()->getConfig('generalscripts').'admin/organize.js';
        Yii::app()->setConfig("js_admin_includes", $js_admin_includes);

		$css_admin_includes   = Yii::app()->getConfig("css_admin_includes");
		$css_admin_includes[] = Yii::app()->getConfig('styleurl')."admin/default/superfish.css";
        Yii::app()->setConfig("css_admin_includes", $css_admin_includes);

        $this->controller->_getAdminHeader();
        $this->controller->_showadminmenu($iSurveyID);
        $this->_surveybar($iSurveyID);

        $this->getController()->render('/admin/survey/organizeGroupsAndQuestions_view',$aViewData);

		$this->controller->_loadEndScripts();
        $this->controller->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));

    }


}
