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
 
 class questiongroup extends SurveyCommonController {
    
    function __construct()
	{
		parent::__construct();
	}
    
    
    function add($surveyid)
    {
        if(bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            $action = "addgroup";//$this->input->post('action');
            $clang = $this->limesurvey_lang;
            
            $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
            $this->config->set_item("css_admin_includes", $css_admin_includes);
            self::_getAdminHeader();
            self::_showadminmenu();
            self::_surveybar($surveyid); 
            
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
                $this->load->view('admin/Survey/QuestionGroups/addGroup_view',$data);
                
                /**
                $newgroupoutput = PrepareEditorScript();
                $newgroupoutput .= "<div class='header ui-widget-header'>".$clang->gT("Add question group")."</div>\n";
                 $newgroupoutput .= "<div id='tabs'>\n<ul>\n";
            	 foreach ($grplangs as $grouplang)
                 {
                    $newgroupoutput .= '<li><a href="#'.$grouplang.'">'.GetLanguageNameFromCode($grouplang,false);
                    if ($grouplang==$baselang) {$newgroupoutput .= '('.$clang->gT("Base language").')';}
                    $newgroupoutput .= "</a></li>\n";
    		     }
            		if (bHasSurveyPermission($surveyid,'surveycontent','import'))
                    {
                        $newgroupoutput .= '<li><a href="#import">'.$clang->gT("Import question group")."</a></li>\n";
                		
                	}
            		$newgroupoutput .= "</ul>";
            
                //    $newgroupoutput .="<table width='100%' border='0'  class='tab-page'>\n\t<tr><td>\n"
                $newgroupoutput .="\n";
                $newgroupoutput .= "<form action='$scriptname' class='form30' id='newquestiongroup' name='newquestiongroup' method='post' onsubmit=\"if (1==0 ";
            
                foreach ($grplangs as $grouplang)
                {
                    $newgroupoutput .= "|| document.getElementById('group_name_$grouplang').value.length==0 ";
                }
                $newgroupoutput .=" ) {alert ('".$clang->gT("Error: You have to enter a group title for each language.",'js')."'); return false;}\" >";
            
                foreach ($grplangs as $grouplang)
                {
                    $newgroupoutput .= '<div id="'.$grouplang.'">';
                    $newgroupoutput .= "<ul>"
                    . "<li>"
                    . "<label for='group_name_$grouplang'>".$clang->gT("Title").":</label>\n"
                    . "<input type='text' size='80' maxlength='100' name='group_name_$grouplang' id='group_name_$grouplang' /><font color='red' face='verdana' size='1'> ".$clang->gT("Required")."</font></li>\n"
                    . "\t<li><label for='description_$grouplang'>".$clang->gT("Description:")."</label>\n"
                    . "<textarea cols='80' rows='8' id='description_$grouplang' name='description_$grouplang'></textarea>"
                    . getEditor("group-desc","description_".$grouplang, "[".$clang->gT("Description:", "js")."](".$grouplang.")",$surveyid,'','',$action)
                    . "</li>\n"
                    . "</ul>"
                    . "\t<p><input type='submit' value='".$clang->gT("Save question group")."' />\n"
                    . "</div>\n";
                }
            
                $newgroupoutput.= "<input type='hidden' name='action' value='insertquestiongroup' />\n"
                . "<input type='hidden' name='sid' value='$surveyid' />\n"
                . "</form>\n";
            
            
                // Import TAB
                if (bHasSurveyPermission($surveyid,'surveycontent','import'))
                {
                    $newgroupoutput .= '<div id="import">'."\n"
                    . "<form enctype='multipart/form-data' class='form30' id='importgroup' name='importgroup' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
                    . "<ul>\n"
                    . "<li>\n"
                    . "<label for='the_file'>".$clang->gT("Select question group file (*.lsg/*.csv):")."</label>\n"
                    . "<input id='the_file' name=\"the_file\" type=\"file\" size=\"35\" /></li>\n"
                    . "<li><label for='translinksfields'>".$clang->gT("Convert resource links?")."</label>\n"
                    . "<input id='translinksfields' name=\"translinksfields\" type=\"checkbox\" checked=\"checked\"/></li></ul>\n"
                    . "\t<p><input type='submit' value='".$clang->gT("Import question group")."' />\n"
                    . "\t<input type='hidden' name='action' value='importgroup' />\n"
                    . "\t<input type='hidden' name='sid' value='$surveyid' />\n"
                    . "\t</form>\n";
                    // End Import TABS
                    $newgroupoutput.= "</div>";
                }
            	 
            
            
                // End of TABS
                 $newgroupoutput.= "</div>"; */
            	
            
            }
            
            self::_loadEndScripts();
            
            self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
            }
        
        
    }
    
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
    
    function edit($surveyid,$gid)
    {
        
        if(bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            
            $action = "editgroup";//$this->input->post('action');
            $clang = $this->limesurvey_lang;
            
            $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
            $this->config->set_item("css_admin_includes", $css_admin_includes);
            self::_getAdminHeader();
            self::_showadminmenu();
            self::_surveybar($surveyid); 
            
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
                $tab_content[0] = $this->load->view('admin/Survey/QuestionGroups/editGroup_view',$data,true);/**"<div class='settingrow'><span class='settingcaption'><label for='group_name_{$esrow['language']}'>".$clang->gT("Title").":</label></span>\n")
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
                    
                    $tab_content[$i] = $this->load->view('admin/Survey/QuestionGroups/editGroup_view',$data,true); /**"<div class='settingrow'><span class='settingcaption'><label for='group_name_{$esrow['language']}'>".$clang->gT("Title").":</label></span>\n"
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
    
    
    
    
    
 }