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
  * @version $Id$
  * @access public
  */
 class questiongroup extends Survey_Common_Controller {

    /**
     * questiongroup::__construct()
     * Constructor
     * @return
     */
    function __construct()
	{
		parent::__construct();
	}

    /**
     * questiongroup::import()
     * Function responsible to import a question group.
     * @return
     */
    function import()
    {
        $action = $this->input->post('action');
        $surveyid = $this->input->post('sid');
        $clang = $this->limesurvey_lang;
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
        self::_getAdminHeader();
        self::_showadminmenu();
        self::_surveybar($surveyid,NULL);
        self::_surveysummary($surveyid,"importgroup");
        if ($action == 'importgroup')
        {
            $importgroup = "<div class='header ui-widget-header'>".$clang->gT("Import question group")."</div>\n";
            $importgroup .= "<div class='messagebox ui-corner-all'>\n";

            $sFullFilepath = $this->config->item('tempdir') . DIRECTORY_SEPARATOR . $_FILES['the_file']['name'];
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
                $importgroup .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br /><br />\n";
                $importgroup .= "</div>\n";
                @unlink($sFullFilepath);
                show_error($importgroup);
                return;
            }
            $this->load->helper('admin/import');
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
                    $importgroup .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";
                    $importgroup .=  "</div>\n";
                    unlink($sFullFilepath);
                    show_error($importgroup);
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
            $importgroup .= "<input type='submit' value='".$clang->gT("Go to question group")."' onclick=\"window.open('".site_url('admin/survey/view/'.$surveyid.'/'.$aImportResults['newgid'])."', '_top')\" />\n";
            $importgroup .= "</div><br />\n";

            unlink($sFullFilepath);

            $data['display'] = $importgroup;
            $this->load->view('survey_view',$data);


        }
        self::_loadEndScripts();

        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }


    /**
     * questiongroup::add()
     * Load add new question grup screen.
     * @return
     */
    function add($surveyid)
    {
    	$surveyid = sanitize_int($surveyid);

        if(bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            $action = "addgroup";//$this->input->post('action');
            $clang = $this->limesurvey_lang;

            $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
            $this->config->set_item("css_admin_includes", $css_admin_includes);
            self::_getAdminHeader();
            self::_showadminmenu();
            self::_surveybar($surveyid);
            self::_surveysummary($surveyid,"addgroup");
            if ($action == "addgroup")
            {
                $this->load->helper('admin/htmleditor');
                $this->load->helper('surveytranslator');
                $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                $baselang = GetBaseLanguageFromSurveyID($surveyid);
                $grplangs[] = $baselang;
                $grplangs = array_reverse($grplangs);

                $data['clang'] = $clang;
                $data['surveyid'] = $surveyid;
                $data['action'] = $action;
                $data['grplangs'] = $grplangs;
                $this->load->view('admin/survey/QuestionGroups/addGroup_view',$data);
            }

            self::_loadEndScripts();

            self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
            }


    }

    /**
     * questiongroup::delete()
     * Function responsible for deleting a question group.
     * @return
     */
    function delete()
    {
        $action = $this->input->post("action");
        $surveyid = $this->input->post("sid");
        $gid = $this->input->post("gid");
        $clang = $this->limesurvey_lang;
        if ($action == "delgroup" && bHasSurveyPermission($surveyid, 'surveycontent','delete'))
        {
            $this->load->helper('database');
            if (!isset($gid)) $gid=returnglobal('gid');
            $query = "SELECT qid FROM ".$this->db->dbprefix."groups g, ".$this->db->dbprefix."questions q WHERE g.gid=q.gid AND g.gid=$gid AND q.parent_qid=0 group by qid";
            if ($result = db_execute_assoc($query)) // Checked
            {
                foreach ($result->result_array() as $row)
                {
                    db_execute_assoc("DELETE FROM ".$this->db->dbprefix."conditions WHERE qid={$row['qid']}");    // Checked
                    db_execute_assoc("DELETE FROM ".$this->db->dbprefix."question_attributes WHERE qid={$row['qid']}"); // Checked
                    db_execute_assoc("DELETE FROM ".$this->db->dbprefix."answers WHERE qid={$row['qid']}"); // Checked
                    db_execute_assoc("DELETE FROM ".$this->db->dbprefix."questions WHERE qid={$row['qid']} or parent_qid={$row['qid']}"); // Checked
                    db_execute_assoc("DELETE FROM ".$this->db->dbprefix."defaultvalues WHERE qid={$row['qid']}"); // Checked
                    db_execute_assoc("DELETE FROM ".$this->db->dbprefix."quota_members WHERE qid={$qid}");
                }
            }
            $query = "DELETE FROM ".$this->db->dbprefix."assessments WHERE sid=$surveyid AND gid=$gid";
            $result = db_execute_assoc($query) ; //or safe_die($connect->ErrorMsg());  // Checked

            $query = "DELETE FROM ".$this->db->dbprefix."groups WHERE sid=$surveyid AND gid=$gid";
            $result = db_execute_assoc($query); // or safe_die($connect->ErrorMsg());  // Checked
            if ($result)
            {
                $gid = "";
                $groupselect = getgrouplist($gid,$surveyid);
                fixSortOrderGroups($surveyid);
                $this->session->set_userdata('flashmessage', $clang->gT("The question group was deleted."));
            }
            else
            {
                $databaseoutput = "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Group could not be deleted","js")."\")\n //-->\n</script>\n";
            }

            if ($databaseoutput != '')
            {
                echo $databaseoutput;
            }
            else
            {
                redirect(site_url('admin/survey/view/'.$surveyid));
            }
        }
    }

    /**
     * questiongroup::edit()
     * Load editing of a question group screen.
     * @return
     */
    function edit($surveyid,$gid)
    {

    	$surveyid = sanitize_int($surveyid);
		$gid = sanitize_int($gid);
		
        if(bHasSurveyPermission($surveyid,'surveycontent','read'))
        {

            $action = "editgroup";//$this->input->post('action');
            $clang = $this->limesurvey_lang;

            $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
            $this->config->set_item("css_admin_includes", $css_admin_includes);
            self::_getAdminHeader();
            self::_showadminmenu();
            self::_surveybar($surveyid,$gid);

            if ($action == "editgroup")
            {

                $this->load->helper('admin/htmleditor');
                $this->load->helper('surveytranslator');
                $this->load->helper('database');

                $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                $baselang = GetBaseLanguageFromSurveyID($surveyid);

                $grplangs[] = $baselang;
                $grplangs = array_flip($grplangs);

                $egquery = "SELECT * FROM ".$this->db->dbprefix."groups WHERE sid=$surveyid AND gid=$gid";
                $egresult = db_execute_assoc($egquery);
                foreach ($egresult->result_array() as $esrow)
                {
                    if(!array_key_exists($esrow['language'], $grplangs)) // Language Exists, BUT ITS NOT ON THE SURVEY ANYMORE.
                    {
                        $egquery = "DELETE FROM ".$this->db->dbprefix."groups WHERE sid='{$surveyid}' AND gid='{$gid}' AND language='".$esrow['language']."'";
                        $egresultD = db_execute_assoc($egquery);
                    } else {
                        $grplangs[$esrow['language']] = 99;
                    }
                    if ($esrow['language'] == $baselang) $basesettings = array('group_name' => $esrow['group_name'],'description' => $esrow['description'],'group_order' => $esrow['group_order']);

                }

                while (list($key,$value) = each($grplangs))
                {
                    if ($value != 99)
                    {
                        $egquery = "INSERT INTO ".$this->db->dbprefix."groups (gid, sid, group_name, description,group_order,language) VALUES ('{$gid}', '{$surveyid}', '{$basesettings['group_name']}', '{$basesettings['description']}','{$basesettings['group_order']}', '{$key}')";
                        $egresult = db_execute_assoc($egquery);
                    }
                }

                $egquery = "SELECT * FROM ".$this->db->dbprefix."groups WHERE sid=$surveyid AND gid=$gid AND language='$baselang'";
                $egresult = db_execute_assoc($egquery);
                $editgroup = PrepareEditorScript();
                $esrow = $egresult->row_array();
                $tab_title[0] = getLanguageNameFromCode($esrow['language'],false). '('.$clang->gT("Base language").')';
                $esrow = array_map('htmlspecialchars', $esrow);
                $data['action'] = "editgroup";
                $data['clang'] = $clang;
                $data['surveyid'] = $surveyid;
                $data['gid'] = $gid;
                $data['esrow'] = $esrow;
                $data['i'] = 0;
                $tab_content[0] = $this->load->view('admin/survey/QuestionGroups/editGroup_view',$data,true);/**"<div class='settingrow'><span class='settingcaption'><label for='group_name_{$esrow['language']}'>".$clang->gT("Title").":</label></span>\n")
                    . "<span class='settingentry'><input type='text' maxlength='100' size='80' name='group_name_{$esrow['language']}' id='group_name_{$esrow['language']}' value=\"{$esrow['group_name']}\" />\n"
                    . "\t</span></div>\n"
                    . "<div class='settingrow'><span class='settingcaption'><label for='description_{$esrow['language']}'>".$clang->gT("Description:")."</label>\n"
                    . "</span><span class='settingentry'><textarea cols='70' rows='8' id='description_{$esrow['language']}' name='description_{$esrow['language']}'>{$esrow['description']}</textarea>\n"
                    . getEditor("group-desc","description_".$esrow['language'], "[".$clang->gT("Description:", "js")."](".$esrow['language'].")",$surveyid,$gid,'',$action)
                    . "\t</span></div><div style='clear:both'></div>"; */
                $egquery = "SELECT * FROM ".$this->db->dbprefix."groups WHERE sid=$surveyid AND gid=$gid AND language!='$baselang'";
                $egresult = db_execute_assoc($egquery);
                $i = 1;
                foreach ($egresult->result_array() as $esrow)
                {
                    $tab_title[$i] = getLanguageNameFromCode($esrow['language'],false);
                    $esrow = array_map('htmlspecialchars', $esrow);
                    $data['action'] = "editgroup";
                    $data['clang'] = $clang;
                    $data['surveyid'] = $surveyid;
                    $data['gid'] = $gid;
                    $data['esrow'] = $esrow;

                    $tab_content[$i] = $this->load->view('admin/survey/QuestionGroups/editGroup_view',$data,true); /**"<div class='settingrow'><span class='settingcaption'><label for='group_name_{$esrow['language']}'>".$clang->gT("Title").":</label></span>\n"
                        . "<span class='settingentry'><input type='text' maxlength='100' size='80' name='group_name_{$esrow['language']}' id='group_name_{$esrow['language']}' value=\"{$esrow['group_name']}\" />\n"
                        . "\t</span></div>\n"
                        . "<div class='settingrow'><span class='settingcaption'><label for='description_{$esrow['language']}'>".$clang->gT("Description:")."</label>\n"
                        . "</span><span class='settingentry'><textarea cols='70' rows='8' id='description_{$esrow['language']}' name='description_{$esrow['language']}'>{$esrow['description']}</textarea>\n"
                        . getEditor("group-desc","description_".$esrow['language'], "[".$clang->gT("Description:", "js")."](".$esrow['language'].")",$surveyid,$gid,'',$action)
                        . "\t</span></div><div style='clear:both'></div>"; */
                    $i++;
                }

                $editgroup .= "<div class='header ui-widget-header'>".$clang->gT("Edit Group")."</div>\n"
                . "<form name='frmeditgroup' id='frmeditgroup' action='".site_url("admin/database/index")."' class='form30' method='post'>\n<div id='tabs'><ul>\n";


                foreach ($tab_title as $i=>$eachtitle){
                    $editgroup .= "\t<li style='clear:none'><a href='#editgrp$i'>$eachtitle</a></li>\n";

                }
                $editgroup.="</ul>\n";

                foreach ($tab_content as $i=>$eachcontent){
                    $editgroup .= "\n<div id='editgrp$i'>$eachcontent</div>";
                }

                $editgroup .= "</div>\n\t<p><input type='submit' class='standardbtn' value='".$clang->gT("Update Group")."' />\n"
                . "\t<input type='hidden' name='action' value='updategroup' />\n"
                . "\t<input type='hidden' name='sid' value=\"{$surveyid}\" />\n"
                . "\t<input type='hidden' name='gid' value='{$gid}' />\n"
                . "\t<input type='hidden' name='language' value=\"{$esrow['language']}\" />\n"
                . "\t</p>\n"
                . "</form>\n";
            }

            //echo $editsurvey;
            $finaldata['display'] = $editgroup;
            $this->load->view('survey_view',$finaldata);



        }
        self::_loadEndScripts();

        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }

    /**
     * questiongroup::order()
     * Load ordering of question group screen.
     * @return
     */
    function order($surveyid)
    {

    	$surveyid = sanitize_int($surveyid);

        $clang = $this->limesurvey_lang;
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
        self::_getAdminHeader();
        self::_showadminmenu();
        self::_surveybar($surveyid);

        if(bHasSurveyPermission($surveyid,'surveycontent','update'))
        {
            $_POST = $this->input->post();
            $this->load->helper('database');

            // Check if one of the up/down buttons have been clicked
            if ($this->input->post('groupordermethod') && $this->input->post('sortorder'))
            {

                $postsortorder=sanitize_int($this->input->post('sortorder'));
                switch($this->input->post('groupordermethod'))
                {
                    // Pressing the Up button
                    case 'up':
                        $newsortorder=$postsortorder-1;
                        $oldsortorder=$postsortorder;
                        $cdquery = "UPDATE ".$this->db->dbprefix."groups SET group_order=-1 WHERE sid=$surveyid AND group_order=$newsortorder";
                        $cdresult=db_execute_assoc($cdquery); // or safe_die($connect->ErrorMsg()); //Checked)
                        $cdquery = "UPDATE ".$this->db->dbprefix."groups SET group_order=$newsortorder WHERE sid=$surveyid AND group_order=$oldsortorder";
                        $cdresult=db_execute_assoc($cdquery); // or safe_die($connect->ErrorMsg()); //Checked
                        $cdquery = "UPDATE ".$this->db->dbprefix."groups SET group_order='$oldsortorder' WHERE sid=$surveyid AND group_order=-1";
                        $cdresult=db_execute_assoc($cdquery); // or safe_die($connect->ErrorMsg()); //Checked
                        break;

                        // Pressing the Down button
                    case 'down':
                        $newsortorder=$postsortorder+1;
                        $oldsortorder=$postsortorder;
                        $cdquery = "UPDATE ".$this->db->dbprefix."groups SET group_order=-1 WHERE sid=$surveyid AND group_order=$newsortorder";
                        $cdresult=db_execute_assoc($cdquery); // or safe_die($connect->ErrorMsg());//Checked
                        $cdquery = "UPDATE ".$this->db->dbprefix."groups SET group_order='$newsortorder' WHERE sid=$surveyid AND group_order=$oldsortorder";
                        $cdresult=db_execute_assoc($cdquery); // or safe_die($connect->ErrorMsg());//Checked
                        $cdquery = "UPDATE ".$this->db->dbprefix."groups SET group_order=$oldsortorder WHERE sid=$surveyid AND group_order=-1";
                        $cdresult=db_execute_assoc($cdquery); // or safe_die($connect->ErrorMsg());//Checked
                        break;
                }
            }
            // Move the question to specific position
            if ((!empty($_POST['groupmovefrom']) || (isset($_POST['groupmovefrom']) && $_POST['groupmovefrom'] == '0')) && (!empty($_POST['groupmoveto']) || (isset($_POST['groupmoveto']) && $_POST['groupmoveto'] == '0')))
            {

                $newpos=(int)$_POST['groupmoveto'];
                $oldpos=(int)$_POST['groupmovefrom'];
                if($newpos > $oldpos)
                {
                    //Move the group we're changing out of the way
                    $cdquery = "UPDATE ".$this->db->dbprefix."groups SET group_order=-1 WHERE sid=$surveyid AND group_order=$oldpos";
                    $cdresult=db_execute_assoc($cdquery); // or safe_die($cdquery."<br />".$connect->ErrorMsg());
                    //Move all question_orders that are less than the newpos down one
                    $cdquery = "UPDATE ".$this->db->dbprefix."groups SET group_order=group_order-1 WHERE sid=$surveyid AND group_order > $oldpos and group_order<=$newpos";
                    $cdresult=db_execute_assoc($cdquery); //or safe_die($connect->ErrorMsg());
                    //Renumber the question we're changing
                    $cdquery = "UPDATE ".$this->db->dbprefix."groups SET group_order=$newpos WHERE sid=$surveyid AND group_order=-1";
                    $cdresult=db_execute_assoc($cdquery); // or safe_die($connect->ErrorMsg());
                }

                if(($newpos+1) < $oldpos)
                {

                    //echo "Newpos $newpos, Oldpos $oldpos";
                    //Move the question we're changing out of the way
                    $cdquery = "UPDATE ".$this->db->dbprefix."groups SET group_order=-1 WHERE sid=$surveyid AND group_order=$oldpos";
                    $cdresult=db_execute_assoc($cdquery); // or safe_die($connect->ErrorMsg());
                    //Move all question_orders that are later than the newpos up one
                    $cdquery = "UPDATE ".$this->db->dbprefix."groups SET group_order=group_order+1 WHERE sid=$surveyid AND group_order > $newpos AND group_order <= $oldpos";
                    $cdresult=db_execute_assoc($cdquery); // or safe_die($connect->ErrorMsg());
                    //Renumber the question we're changing
                    $cdquery = "UPDATE ".$this->db->dbprefix."groups SET group_order=".($newpos+1)." WHERE sid=$surveyid AND group_order=-1";
                    $cdresult=db_execute_assoc($cdquery); // or safe_die($connect->ErrorMsg());
                }
            }

            $ordergroups = "<div class='header ui-widget-header'>".$clang->gT("Change Group Order")."</div><br />\n";

            // Get groups dependencies regarding conditions
            // => Get an array of groups containing questions with conditions outside the group
            // $groupdepsarray[dependent-gid][target-gid]['conditions'][qid-having-conditions]=Array(cids...)
            $groupdepsarray = GetGroupDepsForConditions($surveyid);
            $nicegroupdeps=array();

            if (!is_null($groupdepsarray))
            {

                $ordergroups .= "<ul class='movableList'><li class='movableNode'><strong><font color='orange'>".$clang->gT("Warning").":</font> ".$clang->gT("Current survey has questions with conditions outside their own group")."</strong><br /><br /><i>".$clang->gT("Re-ordering groups is restricted to ensure that questions on which conditions are based aren't reordered after questions having the conditions set")."</i></strong><br /><br/>".$clang->gT("The following groups are concerned").":<ul>\n";
                foreach ($groupdepsarray as $depgid => $depgrouprow)
                {
                    foreach($depgrouprow as $targgid => $targrow)
                    {
                        $ordergroups .= "<li>".sprintf($clang->gT("Group %s depends on group %s, see the marked conditions on:"), "<a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$depgid."')\">".$targrow['depgpname']."</a>", "<a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$targgid."')\">".$targrow['targetgpname']."</a> ");
                        $nicegroupdeps[$depgid]=$targgid;
                        foreach($targrow['conditions'] as $depqid => $depqrow)
                        {
                            $listcid=implode("-",$depqrow);
                            $ordergroups .= " <a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$depgid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."','_top')\"> [".$clang->gT("QID").": ".$depqid."]</a>";
                        }
                        $ordergroups .= "</li>\n";
                    }
                }
                $ordergroups .= "</ul></li></ul>";
            }

            $ordergroups .= "<form method='post' action=''><ul class='movableList'>";
            //Get the groups from this survey
            $s_lang = GetBaseLanguageFromSurveyID($surveyid);
            $ogquery = "SELECT * FROM ".$this->db->dbprefix."groups WHERE sid='{$surveyid}' AND language='{$s_lang}' order by group_order,group_name" ;
            $ogresult = db_execute_assoc($ogquery) or safe_die($connect->ErrorMsg());//Checked

            $ogarray = array(); //$ogresult->GetArray();
            foreach ($ogresult->result_array() as $row)
            {
                $ogarray[] = $row;
            }
            //FIX BROKEN ORDER
            //Check if all group_order numbers are consecutive
            $consecutive=true;
            $lastnumber=-1;
            foreach($ogarray as $group)
            {
                if(($group['group_order']-1) != $lastnumber)
                {
                    $consecutive=false;
                }
                $lastnumber=$group['group_order'];
            }
            //Fix bad ordering
            if((isset($ogarray[0]['group_order']) && $ogarray[0]['group_order'] > 0) || !$consecutive)
            {
                $i=0;
                foreach($ogarray as $group)
                {
                    $fixorderq = "UPDATE ".$this->db->dbprefix."groups SET group_order=$i WHERE sid=$surveyid AND group_order = ".$group['group_order'];
                    $foresult = db_execute_assoc($fixorderq) or safe_die($connect->ErrorMsg());
                    $ogarray[$i]['group_order']=$i;
                    $i++;
                }
            }

            //END FIX BROKEN ORDER
            $miniogarray=$ogarray;
            $groupcount = count($ogarray);
            for($i=0; $i < $groupcount ; $i++)
            {
                $downdisabled = "";
                $updisabled = "";
                if ( !is_null($groupdepsarray) && $i < $groupcount-1 &&
                array_key_exists($ogarray[$i+1]['gid'],$groupdepsarray) &&
                array_key_exists($ogarray[$i]['gid'],$groupdepsarray[$ogarray[$i+1]['gid']]) )
                {
                    $downdisabled = "disabled=\"true\" class=\"disabledUpDnBtn\"";
                }
                if ( !is_null($groupdepsarray) && $i !=0  &&
                array_key_exists($ogarray[$i]['gid'],$groupdepsarray) &&
                array_key_exists($ogarray[$i-1]['gid'],$groupdepsarray[$ogarray[$i]['gid']]) )
                {
                    $updisabled = "disabled=\"true\" class=\"disabledUpDnBtn\"";
                }

                $ordergroups.="<li class='movableNode' id='gid".$ogarray[$i]['gid']."'>\n" ;

                // DROP DOWN LIST //
                //Move to location
                //$ordergroups.="<li class='movableNode'>\n" ;
                $ordergroups.="\t<select style='float:right; margin-left: 5px; width:20em;";
                $ordergroups.="' name='groupmovetomethod$i' onchange=\"this.form.groupmovefrom.value='".$ogarray[$i]['group_order']."';this.form.groupmoveto.value=this.value;submit()\">\n";
                $ordergroups.="<option value=''>".$clang->gT("Place after..")."</option>\n";
                //Display the "position at beginning" item
                if(empty($groupdepsarray) || (!is_null($groupdepsarray)  && $i != 0 &&
                !array_key_exists($ogarray[$i]['gid'], $groupdepsarray)))
                {
                    $ordergroups.="<option value='-1'>".$clang->gT("At beginning")."</option>\n";
                }
                //Find out if there are any dependencies
                $max_start_order=0;
                if ( !is_null($groupdepsarray) && $i!=0 &&
                array_key_exists($ogarray[$i]['gid'], $groupdepsarray)) //This should find out if there are any dependencies
                {
                    foreach($groupdepsarray[$ogarray[$i]['gid']] as $key=>$val)
                    {
                        //qet the question_order value for each of the dependencies
                        foreach($miniogarray as $mo)
                        {
                            if($mo['gid'] == $key && $mo['group_order'] > $max_start_order) //If there is a matching condition, and the question order for that condition is higher than the one already set:
                            {
                                $max_start_order = $mo['group_order']; //Set the maximum question condition to this
                            }
                        }
                    }
                }
                //Find out if any groups use this as a dependency
                $max_end_order=$groupcount+1; //By default, stop the list at the last group

                if ( !is_null($nicegroupdeps))
                {
                    //to find which question has a dependence on this one
                    //then stop the list, so you can't move this group past the dependent one
                    foreach($nicegroupdeps as $gdarray)
                    {
                        if ($ogarray[$i]['gid'] == $gdarray)
                        {
                            //This group should never get higher than here ($gdarray)!
                            //Get the group_order for $gdarray;
                            $goquery="SELECT group_order FROM ".$this->db->dbprefix."groups WHERE gid = $gdarray";
                            $goresult=db_execute_assoc($goquery) or safe_die($connect->ErrorMsg());
                            $gorow = $goresult->FetchRow();
                            $max_end_order=$gorow['group_order'];
                        }
                    }
                }
                $minipos=$miniogarray[0]['group_order']; //Start at the very first group_order
                foreach($miniogarray as $mo)
                {
                    if($minipos >= $max_start_order && $minipos < $max_end_order && $i!=$mo['group_order'] && $i-1!=$mo['group_order'])
                    {
                        $ordergroups.="<option value='".$mo['group_order']."'>".$mo['group_name']."</option>\n";
                    }
                    $minipos++;
                }
                $ordergroups.="</select>\n";

                // BUTTONS //
                $ordergroups.= "<input style='float:right;";

                if ($i == 0){$ordergroups.="visibility:hidden;";}
                $ordergroups.="' type='image' src='".$this->config->item('imageurl')."/up.png' name='btnup_$i' onclick=\"$('#sortorder').val('{$ogarray[$i]['group_order']}');$('#groupordermethod').val('up')\" ".$updisabled."/>\n";

                if ($i < $groupcount-1)
                {
                    // Fill the hidden field 'sortorder' so we know what field is moved down
                    $ordergroups.= "<input type='image' src='".$this->config->item('imageurl')."/down.png' style='float:right;' name='btndown_$i' onclick=\"$('#sortorder').val('{$ogarray[$i]['group_order']}');$('#groupordermethod').val('down')\" ".$downdisabled."/>\n";
                }
                $ordergroups.=$ogarray[$i]['group_name']."</li>\n" ;

            }

            $ordergroups.="</ul>\n"
            . "<input type='hidden' name='groupmovefrom' />\n"
            . "<input type='hidden' id='groupordermethod' name='groupordermethod' />\n"
            . "<input type='hidden' name='groupmoveto' />\n"
            . "<input type='hidden' id='sortorder' name='sortorder' />"
            . "<input type='hidden' name='action' value='ordergroups' />"
            . "</form>" ;
            $ordergroups .="<br />" ;
        }
        else
        {
            $ordergroups = access_denied('ordergroups',$surveyid);

            //include("access_denied.php");
        }

        $finaldata['display'] = $ordergroups;
        $this->load->view('survey_view',$finaldata);
        self::_loadEndScripts();

        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }





 }