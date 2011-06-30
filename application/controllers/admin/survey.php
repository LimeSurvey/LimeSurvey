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
 * $Id$
 */
 
 class survey extends SurveyCommonController {
    
    function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Shows survey details
	 */
	function view($surveyid)
	{
	    if(bHasSurveyPermission($surveyid,'survey','read'))
	    {
            $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
			$this->config->set_item("css_admin_includes", $css_admin_includes);
		
			self::_getAdminHeader();
			self::_showadminmenu();
			self::_surveybar($surveyid);
			self::_surveysummary($surveyid);
            self::_loadEndScripts();
            
            
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
		}
	}
    
    
    function listsurveys()
    {
        $clang = $this->limesurvey_lang;
        $this->load->helper('database');
        $this->load->helper('surveytranslator');
        
        //self::_js_admin_includes(base_url().'scripts/admin/surveysettings.js');
        
        self::_js_admin_includes(base_url().'scripts/jquery/jquery.tablesorter.min.js');
        self::_js_admin_includes(base_url().'scripts/admin/listsurvey.js');
        
        self::_getAdminHeader();
        self::_showadminmenu();
        
        
        $query = " SELECT a.*, c.*, u.users_name FROM ".$this->db->dbprefix."surveys as a "
        ." INNER JOIN ".$this->db->dbprefix."surveys_languagesettings as c ON ( surveyls_survey_id = a.sid AND surveyls_language = a.language ) AND surveyls_survey_id=a.sid AND surveyls_language=a.language "
        ." INNER JOIN ".$this->db->dbprefix."users as u ON (u.uid=a.owner_id) ";
    
        if ($this->session->userdata('USER_RIGHT_SUPERADMIN') != 1)
        {
            $query .= "WHERE a.sid in (select sid from ".$this->db->dbprefix."survey_permissions WHERE uid=".$this->session->userdata('loginID')." AND permission='survey' AND read_p=1) ";
        }
    
        $query .= " ORDER BY surveyls_title";
        $this->load->helper('database');
        $result = db_execute_assoc($query); // or safe_die($connect->ErrorMsg()); //Checked
    
        if($result->num_rows() > 0) {
            
           /**$listsurveys= "<br /><table class='listsurveys'><thead>
                      <tr>
                        <th colspan='7'>&nbsp;</th>
                        <th colspan='3'>".$clang->gT("Responses")."</th>
                        <th colspan='2'>&nbsp;</th>
                      </tr>
    				  <tr>
    				    <th>".$clang->gT("Status")."</th>
                        <th>".$clang->gT("SID")."</th>
    				    <th>".$clang->gT("Survey")."</th>
    				    <th>".$clang->gT("Date created")."</th>
    				    <th>".$clang->gT("Owner") ."</th>
    				    <th>".$clang->gT("Access")."</th>
    				    <th>".$clang->gT("Anonymized responses")."</th>
    				    <th>".$clang->gT("Full")."</th>
                        <th>".$clang->gT("Partial")."</th>
                        <th>".$clang->gT("Total")."</th>
                        <th>".$clang->gT("Tokens available")."</th>
                        <th>".$clang->gT("Response rate")."</th>
    				  </tr></thead>
    				  <tfoot><tr class='header ui-widget-header'>
    		<td colspan=\"12\">&nbsp;</td>".
    		"</tr></tfoot>
    		<tbody>"; */
            $gbc = "evenrow";
            $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
            $listsurveys = "";
            $first_time = true;
            foreach ($result->result_array() as $rows)
            {
                
                if($rows['anonymized']=="Y")
                {
                    $privacy=$clang->gT("Yes") ;
                }
                else
                {
                    $privacy =$clang->gT("No") ;   
                }
    
    
                if (tableExists('tokens_'.$rows['sid']))
                {
                    $visibility = $clang->gT("Closed");
                }
                else
                {
                    $visibility = $clang->gT("Open");
                }
                
                if($rows['active']=="Y")
                {
                    
                    if ($rows['expires']!='' && $rows['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d", $this->config->item('timeadjust')))
                    {
                        $status=$clang->gT("Expired") ;
                    }
                    elseif ($rows['startdate']!='' && $rows['startdate'] > date_shift(date("Y-m-d H:i:s"), "Y-m-d", $this->config->item('timeadjust')))
                    {
                        $status=$clang->gT("Not yet active") ;
                    }
                    else {
                        $status=$clang->gT("Active") ;
                    }
                    
                    // Complete Survey Responses - added by DLR
                    $gnquery = "SELECT COUNT(id) AS countofid FROM ".$this->db->dbprefix."survey_".$rows['sid']." WHERE submitdate IS NULL";
                    $gnresult = db_execute_assoc($gnquery); //Checked)
                    
                    foreach ($gnresult->result_array() as $gnrow)
                    {
                        $partial_responses=$gnrow['countofid'];
                    }
                    $gnquery = "SELECT COUNT(id) AS countofid FROM ".$this->db->dbprefix."survey_".$rows['sid'];
                    $gnresult = db_execute_assoc($gnquery); //Checked
                    foreach ($gnresult->result_array() as $gnrow)
                    {
                        $responses=$gnrow['countofid'];
                    } 
    
                }
                else $status = $clang->gT("Inactive") ;
                
                if ($first_time) // can't use same instance of Date_Time_Converter library every time!
                {
                    $this->load->library('Date_Time_Converter',array($rows['datecreated'], "Y-m-d H:i:s"));
                    $datetimeobj = $this->date_time_converter ; // new Date_Time_Converter($rows['datecreated'] , "Y-m-d H:i:s");
                    $first_time = false;
                    
                }
                else
                {
                    // no need to load the library again, just make a new instance!
                    $datetimeobj = new Date_Time_Converter(array($rows['datecreated'], "Y-m-d H:i:s"));
                    
                }
                
                
                $datecreated=$datetimeobj->convert($dateformatdetails['phpdate']);

                if (in_array($rows['owner_id'],getuserlist('onlyuidarray')))
                {
                    $ownername=$rows['users_name'] ;
                }
                else
                {
                    $ownername="---";
                }
    
                $questionsCount = 0;
                $questionsCountQuery = "SELECT * FROM ".$this->db->dbprefix."questions WHERE sid={$rows['sid']} AND language='".$rows['language']."'"; //Getting a count of questions for this survey
                $questionsCountResult = db_execute_assoc($questionsCountQuery); //($connect->Execute($questionsCountQuery); //Checked)
                $questionsCount = $questionsCountResult->num_rows();
    
                $listsurveys.="<tr>";
                
                if ($rows['active']=="Y")
                {
                    if ($rows['expires']!='' && $rows['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d", $this->config->item('timeadjust')))
                    {
                        $listsurveys .= "<td><img src='".$this->config->item('imageurl')."/expired.png' "
                        . "alt='".$clang->gT("This survey is active but expired.")."' /></td>";
                    }
                    else
                    {
                        if (bHasSurveyPermission($rows['sid'],'surveyactivation','update'))
                        {
                            $listsurveys .= "<td><a href=\"#\" onclick=\"window.open('".$this->config->item('scriptname')."?action=deactivate&amp;sid={$rows['sid']}', '_top')\""
                            . " title=\"".$clang->gTview("This survey is active - click here to deactivate this survey.")."\" >"
                            . "<img src='".$this->config->item('imageurl')."/active.png' alt='".$clang->gT("This survey is active - click here to deactivate this survey.")."' /></a></td>\n";
                        } else
                        {
                            $listsurveys .= "<td><img src='".$this->config->item('imageurl')."/active.png' "
                            . "alt='".$clang->gT("This survey is currently active.")."' /></td>\n";
                        }
                    }
                } else {
                    if ( $questionsCount > 0 && bHasSurveyPermission($rows['sid'],'surveyactivation','update') )
                    {
                        $listsurveys .= "<td><a href=\"#\" onclick=\"window.open('".$this->config->item('scriptname')."?action=activate&amp;sid={$rows['sid']}', '_top')\""
                        . " title=\"".$clang->gTview("This survey is currently not active - click here to activate this survey.")."\" >"
                        . "<img src='".$this->config->item('imageurl')."/inactive.png' title='' alt='".$clang->gT("This survey is currently not active - click here to activate this survey.")."' /></a></td>\n" ;
                    } else
                    {
                        $listsurveys .= "<td><img src='".$this->config->item('imageurl')."/inactive.png'"
                        . " title='".$clang->gT("This survey is currently not active.")."' alt='".$clang->gT("This survey is currently not active.")."' />"
                        . "</td>\n";
                    }
                } 
                $link = site_url("admin/survey/view/".$rows['sid']);
                $listsurveys.="<td align='center'><a href='".$link."'>{$rows['sid']}</a></td>";
                $listsurveys.="<td align='left'><a href='".$link."'>{$rows['surveyls_title']}</a></td>".
    					    "<td>".$datecreated."</td>".
    					    "<td>".$ownername." (<a href='#' class='ownername_edit' id='ownername_edit_{$rows['sid']}'>Edit</a>)</td>".
    					    "<td>".$visibility."</td>" .
    					    "<td>".$privacy."</td>"; 
    
                if ($rows['active']=="Y")
                {
                    $complete = $responses - $partial_responses;
                    $listsurveys .= "<td>".$complete."</td>";
                    $listsurveys .= "<td>".$partial_responses."</td>";
                    $listsurveys .= "<td>".$responses."</td>";
                }else{
                    $listsurveys .= "<td>&nbsp;</td>";
                    $listsurveys .= "<td>&nbsp;</td>";
                    $listsurveys .= "<td>&nbsp;</td>";
                } 
    
                if ($rows['active']=="Y" && tableExists("tokens_".$rows['sid']))
    		    {
    		    	//get the number of tokens for each survey
    		    	$tokencountquery = "SELECT COUNT(tid) AS countoftid FROM ".$this->db->dbprefix."tokens_".$rows['sid'];
                                $tokencountresult = db_execute_assoc($tokencountquery); //Checked)
                                foreach ($tokencountresult->result_array() as $tokenrow)
                                {
                                    $tokencount = $tokenrow['countoftid'];
                                }
    
    		    	//get the number of COMLETED tokens for each survey
    		    	$tokencompletedquery = "SELECT COUNT(tid) AS countoftid FROM ".$this->db->dbprefix."tokens_".$rows['sid']." WHERE completed!='N'";
                                $tokencompletedresult = db_execute_assoc($tokencompletedquery); //Checked
                                foreach ($tokencompletedresult->result_array() as $tokencompletedrow)
                                {
                                    $tokencompleted = $tokencompletedrow['countoftid'];
                                }
    
                                //calculate percentage
    
                                //prevent division by zero problems
                                if($tokencompleted != 0 && $tokencount != 0)
                                {
                                $tokenpercentage = round(($tokencompleted / $tokencount) * 100, 1);
                                }
                                else
                                {
                                $tokenpercentage = 0;
                                }
    
                                $listsurveys .= "<td>".$tokencount."</td>";
                                $listsurveys .= "<td>".$tokenpercentage."%</td>";
    		    }
    		    else
    		    {
    				$listsurveys .= "<td>&nbsp;</td>";
    				$listsurveys .= "<td>&nbsp;</td>";
    		    }
    
    		    $listsurveys .= "</tr>" ;
            }
    
    		$listsurveys.="</tbody>";
    		$listsurveys.="</table><br />" ;
            $data['clang'] = $clang;
            $this->load->view('admin/Survey/listSurveys_view',$data);
            
            
        }
        else 
        {
            $listsurveys ="<p><strong> ".$clang->gT("No Surveys available - please create one.")." </strong><br /><br />" ;
            //$this->load->view('survey_view',$displaydata);
        }
        
        $displaydata['display'] = $listsurveys;
        //$data['display'] = $editsurvey;
        $this->load->view('survey_view',$displaydata);
        self::_loadEndScripts();
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
        
        
    }
    
    function delete()
    {
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
        self::_getAdminHeader();
        self::_showadminmenu();
        self::_surveybar($this->input->post('sid')); 
        $data['surveyid'] = $surveyid = $this->input->post('sid');      
        if(bHasSurveyPermission($surveyid,'survey','delete'))
        {
            $data['deleteok'] = $deleteok = $this->input->post('deleteok');
            $data['clang'] = $this->limesurvey_lang;
            
            $data['link'] = site_url("admin/survey/delete");
            if (!(!isset($deleteok) || !$deleteok))
            {
                $this->load->dbforge();
                if (tableExists("survey_$surveyid"))  //delete the survey_$surveyid table
                {
                    //$dsquery = $dict->DropTableSQL("{$dbprefix}survey_$surveyid");
                    //$dict->ExecuteSQLArray($sqlarray); $dict->ExecuteSQLArray($dsquery)
                    $dsresult = $this->dbforge->drop_table('survey_'.$surveyid) or safe_die ("Couldn't drop table survey_".$surveyid." in survey.php");
                }
            
            	if (tableExists("survey_{$surveyid}_timings"))  //delete the survey_$surveyid_timings table
                {    	
                    //$dsquery = $dict->DropTableSQL("{$dbprefix}survey_{$surveyid}_timings");
                    //$dict->ExecuteSQLArray($sqlarraytimings);
                    $dsresult = $this->dbforge->drop_table('survey_'.$surveyid.'_timings') or safe_die ("Couldn't drop table survey_".$surveyid."_timings in survey.php");
                }
            
                if (tableExists("tokens_$surveyid")) //delete the tokens_$surveyid table
                {
                    //$dsquery = $dict->DropTableSQL("{$dbprefix}tokens_$surveyid");
                    $dsresult = $this->dbforge->drop_table('tokens_'.$surveyid) or safe_die ("Couldn't drop table token_".$surveyid." in survey.php");
                }
                
                $dsquery = "SELECT qid FROM ".$this->db->dbprefix."questions WHERE sid=$surveyid";
                $dsresult = db_execute_assoc($dsquery) or safe_die ("Couldn't find matching survey to delete<br />$dsquery<br />");
                while ($dsrow = $dsresult->result_array())
                {
                    //$asdel = "DELETE FROM {$dbprefix}answers WHERE qid={$dsrow['qid']}";
                    //$asres = $connect->Execute($asdel);
                    $this->db->delete('answers', array('qid' => $dsrow['qid'])); 
                    $this->db->delete('conditions', array('qid' => $dsrow['qid']));
                    $this->db->delete('question_attributes', array('qid' => $dsrow['qid']));
                    /**
                    $cddel = "DELETE FROM {$dbprefix}conditions WHERE qid={$dsrow['qid']}";
                    $cdres = $connect->Execute($cddel) or safe_die ("Delete conditions failed<br />$cddel<br />".$connect->ErrorMsg());
                    $qadel = "DELETE FROM {$dbprefix}question_attributes WHERE qid={$dsrow['qid']}";
                    $qares = $connect->Execute($qadel); */
                }
            
                //$qdel = "DELETE FROM {$dbprefix}questions WHERE sid=$surveyid";
                //$qres = $connect->Execute($qdel);
                $this->db->delete('questions', array('sid' => $surveyid));
                
                //$scdel = "DELETE FROM {$dbprefix}assessments WHERE sid=$surveyid";
                //$scres = $connect->Execute($scdel);
                $this->db->delete('assessments', array('sid' => $surveyid));
                
                //$gdel = "DELETE FROM {$dbprefix}groups WHERE sid=$surveyid";
                //$gres = $connect->Execute($gdel);
                $this->db->delete('groups', array('sid' => $surveyid));
                
                //$slsdel = "DELETE FROM {$dbprefix}surveys_languagesettings WHERE surveyls_survey_id=$surveyid";
                //$slsres = $connect->Execute($slsdel);
                $this->db->delete('surveys_languagesettings', array('surveyls_survey_id' => $surveyid));
                
                //$srdel = "DELETE FROM {$dbprefix}survey_permissions WHERE sid=$surveyid";
                //$srres = $connect->Execute($srdel);
                $this->db->delete('survey_permissions', array('sid' => $surveyid));
                
                //$srdel = "DELETE FROM {$dbprefix}saved_control WHERE sid=$surveyid";
                //$srres = $connect->Execute($srdel);
                $this->db->delete('saved_control', array('sid' => $surveyid));
                
                //$sdel = "DELETE FROM {$dbprefix}surveys WHERE sid=$surveyid";
                //$sres = $connect->Execute($sdel);
                $this->db->delete('surveys', array('sid' => $surveyid));
                
                $sdel = "DELETE ".$this->db->dbprefix."quota_languagesettings FROM ".$this->db->dbprefix."quota_languagesettings, ".$this->db->dbprefix."quota WHERE ".$this->db->dbprefix."quota_languagesettings.quotals_quota_id=".$this->db->dbprefix."quota.id and sid=$surveyid";
                $sres = db_execute_assoc($sdel);
                //$sres = $connect->Execute($sdel);
                //$this->db->delete('assessments', array('sid' => $surveyid));
                
                //$sdel = "DELETE FROM {$dbprefix}quota WHERE sid=$surveyid";
                //$sres = $connect->Execute($sdel);
                $this->db->delete('quota', array('sid' => $surveyid));
                
                //$sdel = "DELETE FROM {$dbprefix}quota_members WHERE sid=$surveyid;";
                //$sres = $connect->Execute($sdel);
                $this->db->delete('quota_members', array('sid' => $surveyid));
            }
            $this->load->view('admin/Survey/deleteSurvey_view',$data);
        }
        else { 
            //include('access_denied.php');
            $finaldata['display'] = access_denied("editsurvey",$surveyid);
            $this->load->view('survey_view',$finaldata);
        }
        self::_loadEndScripts();
        
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    }
    
    function editlocalsettings($surveyid)
    {
        $clang = $this->limesurvey_lang;
        
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
        self::_js_admin_includes(base_url().'scripts/admin/surveysettings.js');
        self::_getAdminHeader();
  		self::_showadminmenu();
        self::_surveybar($surveyid);
        if(bHasSurveyPermission($surveyid,'surveylocale','read'))
        {
    
            $grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            array_unshift($grplangs,$baselang);
    
            $editsurvey = PrepareEditorScript();
    
    
            $editsurvey .="<div class='header ui-widget-header'>".$clang->gT("Edit survey text elements")."</div>\n";
            $editsurvey .= "<form id='addnewsurvey' class='form30' name='addnewsurvey' action='".site_url("admin/database/index/updatesurveylocalesettings")."' method='post'>\n"
            . '<div id="tabs">';
            $i = 0;
            foreach ($grplangs as $grouplang)
            {
                // this one is created to get the right default texts fo each language
                $this->load->library('Limesurvey_lang',array($grouplang));
                $this->load->helper('database');
                $this->load->helper('surveytranslator');
                $bplang = $this->limesurvey_lang;//new limesurvey_lang($grouplang);
                $esquery = "SELECT * FROM ".$this->db->dbprefix."surveys_languagesettings WHERE surveyls_survey_id=$surveyid and surveyls_language='$grouplang'";
                $esresult = db_execute_assoc($esquery); //Checked
                $esrow = $esresult->row_array();
    
                $tab_title[$i] = getLanguageNameFromCode($esrow['surveyls_language'],false);
    
                if ($esrow['surveyls_language']==GetBaseLanguageFromSurveyID($surveyid))
                    $tab_title[$i]  .= '('.$clang->gT("Base Language").')';
    
                $esrow = array_map('htmlspecialchars', $esrow);
                $data['clang'] = $clang;
                $data['esrow'] = $esrow;
                $data['surveyid'] = $surveyid;
                $data['action'] = "editsurveylocalesettings";
                
                $tab_content[$i] = $this->load->view('admin/Survey/editLocalSettings_view',$data,true);
                
                /**
                $tab_content[$i] = "<ul>\n"
                . "<li><label for=''>".$clang->gT("Survey title").":</label>\n"
                . "<input type='text' size='80' name='short_title_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_title']}\" /></li>\n"
                . "<li><label for=''>".$clang->gT("Description:")."</label>\n"
                . "<textarea cols='80' rows='15' name='description_".$esrow['surveyls_language']."'>{$esrow['surveyls_description']}</textarea>\n"
                . getEditor("survey-desc","description_".$esrow['surveyls_language'], "[".$clang->gT("Description:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action)
                . "</li>\n"
                . "<li><label for=''>".$clang->gT("Welcome message:")."</label>\n"
                . "<textarea cols='80' rows='15' name='welcome_".$esrow['surveyls_language']."'>{$esrow['surveyls_welcometext']}</textarea>\n"
                . getEditor("survey-welc","welcome_".$esrow['surveyls_language'], "[".$clang->gT("Welcome:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action)
                . "</li>\n"
                . "<li><label for=''>".$clang->gT("End message:")."</label>\n"
                . "<textarea cols='80' rows='15' name='endtext_".$esrow['surveyls_language']."'>{$esrow['surveyls_endtext']}</textarea>\n"
                . getEditor("survey-endtext","endtext_".$esrow['surveyls_language'], "[".$clang->gT("End message:", "js")."](".$esrow['surveyls_language'].")",$surveyid,'','',$action)
                . "</li>\n"
                . "<li><label for=''>".$clang->gT("End URL:")."</label>\n"
                . "<input type='text' size='80' name='url_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_url']}\" />\n"
                . "</li>"
                . "<li><label for=''>".$clang->gT("URL description:")."</label>\n"
                . "<input type='text' size='80' name='urldescrip_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_urldescription']}\" />\n"
                . "</li>"
                . "<li><label for=''>".$clang->gT("Date format:")."</label>\n"
                . "<select size='1' name='dateformat_".$esrow['surveyls_language']."'>\n";
                foreach (getDateFormatData() as $index=>$dateformatdata)
                {
                    $tab_content[$i].= "<option value='{$index}'";
                    if ($esrow['surveyls_dateformat']==$index) {
                       $tab_content[$i].=" selected='selected'";
                    }
                    $tab_content[$i].= ">".$dateformatdata['dateformat'].'</option>';
                }
                $tab_content[$i].= "</select></li>"
                . "<li><label for=''>".$clang->gT("Decimal Point Format:")."</label>\n";
                $tab_content[$i].="<select size='1' name='numberformat_".$esrow['surveyls_language']."'>\n";
                foreach (getRadixPointData() as $index=>$radixptdata)
                {
                    $tab_content[$i].= "<option value='{$index}'";
                    if ($esrow['surveyls_numberformat']==$index) {
                       $tab_content[$i].=" selected='selected'";
                    }
                    $tab_content[$i].= ">".$radixptdata['desc'].'</option>';
                }
                $tab_content[$i].= "</select></li></ul>"; */
                $i++;
            }
    
            $editsurvey .= "<ul>";
            foreach($tab_title as $i=>$eachtitle){
                $editsurvey .= "<li style='clear:none'><a href='#edittxtele$i'>$eachtitle</a></li>";
            }
            $editsurvey .= "</ul>";
            foreach ($tab_content as $i=>$eachcontent){
                $editsurvey .= "<div id='edittxtele$i'>$eachcontent</div>";
            }
            $editsurvey .= "</div>";
            if(bHasSurveyPermission($surveyid,'surveylocale','update'))
            {
                $editsurvey .= "<p><input type='submit' class='standardbtn' value='".$clang->gT("Save")."' />\n"
                . "<input type='hidden' name='action' value='updatesurveylocalesettings' />\n"
                . "<input type='hidden' name='sid' value=\"{$surveyid}\" />\n"
                . "<input type='hidden' name='language' value=\"{$esrow['surveyls_language']}\" />\n"
                . "</p>\n"
                . "</form>\n";
            }
            
            
            
            
            
            
            //echo $editsurvey;
            $finaldata['display'] = $editsurvey;
            $this->load->view('survey_view',$finaldata);
            //self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
    
        }
        else
        {
            //include("access_denied.php");
            $finaldata['display'] = access_denied("editsurvey",$surveyid);
            $this->load->view('survey_view',$finaldata);
            
        }
        self::_loadEndScripts();
            
        
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

    }
    
    function copy()
    {
        
        if ( $this->input->post('copysurveytranslinksfields') == "on"  || $this->input->post('translinksfields') == "on")
        {
            $sTransLinks = true;    
        }
        
        //$importsurvey = "<div class='header ui-widget-header'>".$clang->gT("Copy survey")."</div>\n";
        $copyfunction= true;
        //$importsurvey .= "<div class='messagebox ui-corner-all'>\n";
        $importerror=false; // Put a var for continue
        $surveyid = sanitize_int($this->input->post('copysurveylist'));

        $exclude = array();
        if (get_magic_quotes_gpc()) {$sNewSurveyName = stripslashes($this->input->post('copysurveyname'));}
        else{
            $sNewSurveyName=$this->input->post('copysurveyname');
        }
    
        /*require_once("../classes/inputfilter/class.inputfilter_clean.php");
        $myFilter = new InputFilter('','',1,1,1);
        if ($filterxsshtml)
        {
            $sNewSurveyName = $myFilter->process($sNewSurveyName);
        } else {
            $sNewSurveyName = html_entity_decode($sNewSurveyName, ENT_QUOTES, "UTF-8");
        } */
        if ($this->input->post('copysurveyexcludequotas') == "on")
        {
            $exclude['quotas'] = true;
        }
        if ($this->input->post('copysurveyexcludeanswers') == "on")
        {
            $exclude['answers'] = true;
        }
        if ($this->input->post('copysurveyresetconditions') == "on")
        {
            $exclude['conditions'] = true;
        }
        
        //include("export_structure_xml.php");
        $this->load->helper('admin/export_structure_xml');

        if (!isset($surveyid))
        {
            $surveyid=returnglobal('sid');
        }
        
        $clang = $this->limesurvey_lang;
        if (!$surveyid)
        {
            
            self::_getAdminHeader();
			self::_showadminmenu();
            $data['clang'] = $clang;
            $this->load->view('admin/Survey/exportSurveyError_view',$data);
            
            exit;
        }
        
        if (!isset($copyfunction))
        {
            $fn = "limesurvey_survey_$surveyid.lss";      
            header("Content-Type: text/xml");
            header("Content-Disposition: attachment; filename=$fn");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");                          // HTTP/1.0
            echo getXMLData('',$surveyid);
            exit;
        }
        
        $copysurveydata = getXMLData($exclude,$surveyid);
        // $this->load->helper('import');
        //require_once('import_functions.php');
        if (!$importerror)
        {
            $aImportResults=XMLImportSurvey('',$copysurveydata,$sNewSurveyName);
        }
        else
        {
            $importerror=true;
        }
        
        /**
        if (isset($aImportResults['error']) && $aImportResults['error']!=false)
        {
            $importsurvey .= "<div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
            $importsurvey .= $aImportResults['error']."<br /><br />\n";
            $importsurvey .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";
            $importerror = true;
        }
        
        if (!$importerror)
        {
            $importsurvey .= "<br />\n<div class='successheader'>".$clang->gT("Success")."</div><br /><br />\n";
            
            $importsurvey .= "<strong>".$clang->gT("Survey import summary")."</strong><br />\n";        
            
            
            $importsurvey .= "<ul style=\"text-align:left;\">\n\t<li>".$clang->gT("Surveys").": {$aImportResults['surveys']}</li>\n";
            $importsurvey .= "\t<li>".$clang->gT("Languages").": {$aImportResults['languages']}</li>\n";
            $importsurvey .= "\t<li>".$clang->gT("Question groups").": {$aImportResults['groups']}</li>\n";
            $importsurvey .= "\t<li>".$clang->gT("Questions").": {$aImportResults['questions']}</li>\n";
            $importsurvey .= "\t<li>".$clang->gT("Answers").": {$aImportResults['answers']}</li>\n";
            if (isset($aImportResults['subquestions']))
            {
                $importsurvey .= "\t<li>".$clang->gT("Subquestions").": {$aImportResults['subquestions']}</li>\n";     
            }
            if (isset($aImportResults['defaultvalues']))
            {
                $importsurvey .= "\t<li>".$clang->gT("Default answers").": {$aImportResults['defaultvalues']}</li>\n";     
            }
            if (isset($aImportResults['conditions']))
            {
                $importsurvey .= "\t<li>".$clang->gT("Conditions").": {$aImportResults['conditions']}</li>\n";     
            }
            if (isset($aImportResults['labelsets']))
            {
                $importsurvey .= "\t<li>".$clang->gT("Label sets").": {$aImportResults['labelsets']}</li>\n";
            }
            if (isset($aImportResults['deniedcountls']) && $aImportResults['deniedcountls']>0)
            {
                $importsurvey .= "\t<li>".$clang->gT("Not imported label sets").": {$aImportResults['deniedcountls']} ".$clang->gT("(Label sets were not imported since you do not have the permission to create new label sets.)")."</li>\n";
            }
            $importsurvey .= "\t<li>".$clang->gT("Question attributes").": {$aImportResults['question_attributes']}</li>\n";
            $importsurvey .= "\t<li>".$clang->gT("Assessments").": {$aImportResults['assessments']}</li>\n";
            $importsurvey .= "\t<li>".$clang->gT("Quotas").": {$aImportResults['quota']} ({$aImportResults['quotamembers']} ".$clang->gT("quota members")." ".$clang->gT("and")." {$aImportResults['quotals']} ".$clang->gT("quota language settings").")</li>\n</ul><br />\n";
            
            if (count($aImportResults['importwarnings'])>0) 
            {
                $importsurvey .= "<div class='warningheader'>".$clang->gT("Warnings").":</div><ul style=\"text-align:left;\">";
                foreach ($aImportResults['importwarnings'] as $warning)
                {
                    $importsurvey .='<li>'.$warning.'</li>';
                }
                $importsurvey .= "</ul><br />\n";
            }
            
            $importsurvey .= "<strong>".$clang->gT("Copy of survey is completed.")."</strong><br />\n"
                . "<a href='$scriptname?sid={$aImportResults['newsid']}'>".$clang->gT("Go to survey")."</a><br />\n"; 
              
        
            
            
        }
            // end of traitment an close message box
            $importsurvey .= "</div><br />\n"; */
            
            $data['clang'] = $clang;
            $data['aImportResults'] = $aImportResults;
            $data['importerror'] = $importerror;
            self::_getAdminHeader();
			self::_showadminmenu();
			self::_surveybar($surveyid);
			//self::_surveysummary($surveyid);
			$this->load->view('admin/Survey/copySurvey_view',$data);
            self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
        
    }
    
    function index($action,$surveyid=null)
    {
        //global $surveyid;
        
        self::_js_admin_includes(base_url().'scripts/admin/surveysettings.js');
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
        $this->config->set_item("css_admin_includes", $css_admin_includes);
        self::_getAdminHeader();
		self::_showadminmenu();
        if (!is_null($surveyid))
        self::_surveybar($surveyid);
        
        if(!bHasSurveyPermission($surveyid,'surveysettings','read') && !bHasGlobalPermission('USER_RIGHT_CREATE_SURVEY'))
        {
            //include("access_denied.php");
        }
        $this->load->helper('surveytranslator');
        $clang = $this->limesurvey_lang;
        
        $esrow = array();
        $editsurvey = '';
        if ($action == "newsurvey") {
            $esrow = self::_fetchSurveyInfo('newsurvey');
            $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
        
            $editsurvey = PrepareEditorScript();
            $editsurvey .= "";
            $editsurvey .="<script type=\"text/javascript\">
                        standardtemplaterooturl='".$this->config->item('standardtemplaterooturl')."';
                        templaterooturl='".$this->config->item('usertemplaterooturl')."'; \n";
            $editsurvey .= "</script>\n";

        // header
        $editsurvey .= "<div class='header ui-widget-header'>" . $clang->gT("Create, import, or copy survey") . "</div>\n";
        } elseif ($action == "editsurveysettings") {
            $esrow = self::_fetchSurveyInfo('editsurvey',$surveyid);
            // header
            $editsurvey .= "<div class='header ui-widget-header'>".$clang->gT("Edit survey settings")."</div>\n";
        }
        if ($action == "newsurvey") {
            $editsurvey .= self::_generalTabNewSurvey();
        } elseif ($action == "editsurveysettings") {
            $editsurvey .= self::_generalTabEditSurvey($surveyid,$esrow);
        }
        
        $editsurvey .= self::_tabPresentationNavigation($esrow);
        $editsurvey .= self::_tabPublicationAccess($esrow);
        $editsurvey .= self::_tabNotificationDataManagement($esrow);
        $editsurvey .= self::_tabTokens($esrow);
        
        if ($action == "newsurvey") {
            $editsurvey .= "<input type='hidden' id='surveysettingsaction' name='action' value='insertsurvey' />\n";
            //$this->session->set_userdata(array('action' => 'insertsurvey'));
        } elseif ($action == "editsurveysettings") {
            $editsurvey .= "<input type='hidden' id='surveysettingsaction' name='action' value='updatesurveysettings' />\n"
            . "<input type='hidden' name='sid' value=\"{$esrow['sid']}\" />\n"
            . "<input type='hidden' name='languageids' id='languageids' value=\"{$esrow['additional_languages']}\" />\n"
            . "<input type='hidden' name='language' value=\"{$esrow['language']}\" />\n";
        }
        $editsurvey .= "</form>";
        if ($action == "newsurvey") {
            $editsurvey .= self::_tabImport();
            $editsurvey .= self::_tabCopy();
        } elseif ($action == "editsurveysettings") {
            $editsurvey .= self::_tabResourceManagement($surveyid);
        }
        
        
        // End TAB pane
        $editsurvey .= "</div>\n";
        
        
        if ($action == "newsurvey") {
            $cond = "if (isEmpty(document.getElementById('surveyls_title'), '" . $clang->gT("Error: You have to enter a title for this survey.", 'js') . "'))";
            $editsurvey .= "<p><button onclick=\"$cond {document.getElementById('addnewsurvey').submit();}\" class='standardbtn' >" . $clang->gT("Save") . "</button></p>\n";
        } elseif ($action == "editsurveysettings") {
            $cond = "if (UpdateLanguageIDs(mylangs,'" . $clang->gT("All questions, answers, etc for removed languages will be lost. Are you sure?", "js") . "'))";
            if (bHasSurveyPermission($surveyid,'surveysettings','update'))
            {
                $editsurvey .= "<p><button onclick=\"$cond {document.getElementById('addnewsurvey').submit();}\" class='standardbtn' >" . $clang->gT("Save") . "</button></p>\n";
            }
            if (bHasSurveyPermission($surveyid,'surveylocale','read'))
            {
                $editsurvey .= "<p><button onclick=\"$cond {document.getElementById('surveysettingsaction').value = 'updatesurveysettingsandeditlocalesettings'; document.getElementById('addnewsurvey').submit();}\" class='standardbtn' >" . $clang->gT("Save & edit survey text elements") . " >></button></p>\n";
            }
        }
       	
        //echo $editsurvey;
        $data['display'] = $editsurvey;
        $this->load->view('survey_view',$data);
        self::_loadEndScripts();
        self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
        
    }
    
    
    function _fetchSurveyInfo($action,$surveyid=null)
    {
        if ($action == 'newsurvey')
        {
            $esrow = array();
            $esrow['active']                   = 'N';
            $esrow['allowjumps']               = 'N';   
            $esrow['format']                   = 'G'; //Group-by-group mode
            $esrow['template']                 = $this->config->item('defaulttemplate');
            $esrow['allowsave']                = 'Y';
            $esrow['allowprev']                = 'N';
            $esrow['nokeyboard']               = 'N';   
            $esrow['printanswers']             = 'N';
            $esrow['publicstatistics']         = 'N';
            $esrow['publicgraphs']             = 'N';
            $esrow['public']                   = 'Y';
            $esrow['autoredirect']             = 'N';
            $esrow['tokenlength']              = 15;
            $esrow['allowregister']            = 'N';
            $esrow['usecookie']                = 'N';
            $esrow['usecaptcha']               = 'D';
            $esrow['htmlemail']                = 'Y';
            $esrow['emailnotificationto']      = '';
            $esrow['anonymized']               = 'N';
            $esrow['datestamp']                = 'N';
            $esrow['ipaddr']                   = 'N';
            $esrow['refurl']                   = 'N';
            $esrow['tokenanswerspersistence']  = 'N';
            $esrow['alloweditaftercompletion'] = 'N';
            $esrow['assesments']               = 'N';
            $esrow['startdate']                = '';
            $esrow['savetimings']              = 'N';
            $esrow['expires']                  = '';
            $esrow['showqnumcode']             = 'X';
            $esrow['showwelcome']              = 'Y';
            $esrow['emailresponseto']          = '';
            $esrow['assessments']              = 'N';
        } elseif ($action == 'editsurvey') {
            $condition = array('sid' => $surveyid);
            $this->load->model('surveys_model');
            //$esquery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid";
            $esresult = $this->surveys_model->getAllRecords($condition); //($esquery); //Checked)
            if ($esrow = $esresult->row_array()) {
                $esrow = array_map('htmlspecialchars', $esrow);
            }
            
        }
        
        return $esrow;
    }
    
    function _generalTabNewSurvey()
    {
        global $siteadminname,$siteadminemail;
        $clang = $this->limesurvey_lang;
        
        /** $editsurvey = "<div id='tabs'><ul>
        <li><a href='#general'>".$clang->gT("General")."</a></li>
        <li><a href='#presentation'>".$clang->gT("Presentation & navigation")."</a></li>
        <li><a href='#publication'>".$clang->gT("Publication & access control")."</a></li>
        <li><a href='#notification'>".$clang->gT("Notification & data management")."</a></li>
        <li><a href='#tokens'>".$clang->gT("Tokens")."</a></li>
        <li><a href='#import'>".$clang->gT("Import")."</a></li>
        <li><a href='#copy'>".$clang->gT("Copy")."</a></li>
        </ul>
        \n";
        $editsurvey .= "<form class='form30' name='addnewsurvey' id='addnewsurvey' action='../database/index/insertsurvey' method='post' onsubmit=\"alert('hi');return isEmpty(document.getElementById('surveyls_title'), '" . $clang->gT("Error: You have to enter a title for this survey.", 'js') . "');\" >\n";

        // General & Contact TAB
        $editsurvey .= "<div id='general'>\n";
        
        // Survey Language
        $editsurvey .= "<ul><li><label for='language' title='" . $clang->gT("This is the base language of your survey and it can't be changed later. You can add more languages after you have created the survey.") . "'><span class='annotationasterisk'>*</span>" . $clang->gT("Base language:") . "</label>\n"
                            . "<select id='language' name='language'>\n";
        
        foreach (getLanguageData () as $langkey2 => $langname) {
            $editsurvey .= "<option value='" . $langkey2 . "'";
            if ($this->config->item('defaultlang') == $langkey2) {
                $editsurvey .= " selected='selected'";
            }
            $editsurvey .= ">" . $langname['description'] . "</option>\n";
        }
        $editsurvey .= "</select>\n";
        */    
        $condition = array('users_name' => $this->session->userdata('user'));
        $fieldstoselect = array('full_name', 'email');
        $this->load->model('users_model');
        //Use the current user details for the default administrator name and email for this survey
        //$query = "SELECT full_name, email FROM " . db_table_name('users') . " WHERE users_name = " . db_quoteall($_SESSION['user']);
        $result = $this->users_model->getSomeRecords($fieldstoselect,$condition); //($query) or safe_die($connect->ErrorMsg());)
        $owner = $result->row_array();
        //Degrade gracefully to $siteadmin details if anything is missing.
        if (empty($owner['full_name']))
            $owner['full_name'] = $siteadminname;
        if (empty($owner['email']))
            $owner['email'] = $siteadminemail;
        //Bounce setting by default to global if it set globally
        $this->load->helper('globalsettings');
        if (getGlobalSetting('bounceaccounttype')!='off'){
            $owner['bounce_email']         = getGlobalSetting('siteadminbounce');
        } else {
            $owner['bounce_email']        = $owner['email'];
        }
        
        /**
        $editsurvey .= "<span class='annotation'> " . $clang->gT("*This setting cannot be changed later!") . "</span></li>\n";
        $action = "newsurvey";
        $editsurvey .= ""
                    . "<li><label for='surveyls_title'><span class='annotationasterisk'>*</span>" . $clang->gT("Title") . ":</label>\n"
                    . "<input type='text' size='82' maxlength='200' id='surveyls_title' name='surveyls_title' /> <span class='annotation'>" . $clang->gT("*Required") . "</span></li>\n"
                    . "<li><label for='description'>" . $clang->gT("Description:") . "</label>\n"
                    . "<textarea cols='80' rows='10' id='description' name='description'></textarea>"
                    . getEditor("survey-desc", "description", "[" . $clang->gT("Description:", "js") . "]", '', '', '', $action)
                    . "</li>\n"
                    . "<li><label for='welcome'>" . $clang->gT("Welcome message:") . "</label>\n"
                    . "<textarea cols='80' rows='10' id='welcome' name='welcome'></textarea>"
                    . getEditor("survey-welc", "welcome", "[" . $clang->gT("Welcome message:", "js") . "]", '', '', '', $action)
                    . "</li>\n"
                    . "<li><label for='endtext'>" . $clang->gT("End message:") . "</label>\n"
                    . "<textarea cols='80' id='endtext' rows='10' name='endtext'></textarea>"
                    . getEditor("survey-endtext", "endtext", "[" . $clang->gT("End message:", "js") . "]", '', '', '', $action)
                    . "</li>\n";
        
            // End URL
            $editsurvey .= "<li><label for='url'>" . $clang->gT("End URL:") . "</label>\n"
                    . "<input type='text' size='50' id='url' name='url' value='http://";
            $editsurvey .= "' /></li>\n";

            // URL description
            $editsurvey.= "<li><label for='urldescrip'>" . $clang->gT("URL description:") . "</label>\n"
                    . "<input type='text' maxlength='255' size='50' id='urldescrip' name='urldescrip' value='";
            $editsurvey .= "' /></li>\n"

                    //Default date format
                    . "<li><label for='dateformat'>" . $clang->gT("Date format:") . "</label>\n"
                    . "<select size='1' id='dateformat' name='dateformat'>\n";
            foreach (getDateFormatData () as $index => $dateformatdata) {
                $editsurvey.= "<option value='{$index}'";
                $editsurvey.= ">" . $dateformatdata['dateformat'] . '</option>';
            }
            $editsurvey.= "</select></li>"

                    . "<li><label for='admin'>" . $clang->gT("Administrator:") . "</label>\n"
                    . "<input type='text' size='50' id='admin' name='admin' value='" . $owner['full_name'] . "' /></li>\n"
                    . "<li><label for='adminemail'>" . $clang->gT("Admin Email:") . "</label>\n"
                    . "<input type='text' size='50' id='adminemail' name='adminemail' value='" . $owner['email'] . "' /></li>\n"
                    . "<li><label for='bounce_email'>" . $clang->gT("Bounce Email:") . "</label>\n"
                    . "<input type='text' size='50' id='bounce_email' name='bounce_email' value='" . $owner['bounce_email'] . "' /></li>\n"
                    . "<li><label for='faxto'>" . $clang->gT("Fax to:") . "</label>\n"
                    . "<input type='text' size='50' id='faxto' name='faxto' /></li>\n";

            $editsurvey.= "</ul>";


            // End General TAB
            $editsurvey .= "</div>\n";
            return $editsurvey;
            */
            $data['action'] = "newsurvey";
            $data['clang'] = $clang;
            $data['owner'] = $owner;
            return $this->load->view('admin/survey/superview/superGeneralNewSurvey_view',$data, true);
    }
        
    function _generalTabEditSurvey($surveyid,$esrow)
    {
        global $siteadminname,$siteadminemail;
        $clang = $this->limesurvey_lang;
        /**    
        $editsurvey = "<div id='tabs'><ul>
        <li><a href='#general'>".$clang->gT("General")."</a></li>
        <li><a href='#presentation'>".$clang->gT("Presentation & navigation")."</a></li>
        <li><a href='#publication'>".$clang->gT("Publication & access control")."</a></li>
        <li><a href='#notification'>".$clang->gT("Notification & data management")."</a></li>
        <li><a href='#tokens'>".$clang->gT("Tokens")."</a></li>
        <li><a href='#resources'>".$clang->gT("Resources")."</a></li>
        </ul>
        \n";
        $editsurvey .= "<form class='form30' name='addnewsurvey' id='addnewsurvey' action='../database/index/insertsurvey' method='post' onsubmit=\"alert('hi');return isEmpty(document.getElementById('surveyls_title'), '" . $clang->gT("Error: You have to enter a title for this survey.", 'js') . "');\" >\n";

        // General & Contact TAB
        $editsurvey .= "<div id='general'>\n";

        // Base language
        $editsurvey .= "<ul><li><label>" . $clang->gT("Base language:") . "</label>\n"
        .GetLanguageNameFromCode($esrow['language'])
        . "</li>\n"

        // Additional languages listbox
        . "<li><label for='additional_languages'>".$clang->gT("Additional Languages").":</label>\n"
        . "<table><tr><td align='left'><select style='min-width:220px;' size='5' id='additional_languages' name='additional_languages'>";
        $jsX=0;
        $jsRemLang ="<script type=\"text/javascript\">
                    var mylangs = new Array();
                    standardtemplaterooturl='".$this->config->item('standardtemplaterooturl')."';
                    templaterooturl='".$this->config->item('usertemplaterooturl')."'; \n";

        foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname) {
            if ($langname && $langname != $esrow['language']) { // base languag must not be shown here
            $jsRemLang .="mylangs[$jsX] = \"$langname\"\n";
            $editsurvey .= "<option id='".$langname."' value='".$langname."'";
            $editsurvey .= ">".getLanguageNameFromCode($langname,false)."</option>\n";
            $jsX++;
            }
        }
        $jsRemLang .= "</script>\n";
        $editsurvey .= $jsRemLang;
        //  Add/Remove Buttons
        $editsurvey .= "</select></td>"
        . "<td align='left'><input type=\"button\" value=\"<< ".$clang->gT("Add")."\" onclick=\"DoAdd()\" id=\"AddBtn\" /><br /> <input type=\"button\" value=\"".$clang->gT("Remove")." >>\" onclick=\"DoRemove(0,'')\" id=\"RemoveBtn\"  /></td>\n"

        // Available languages listbox
        . "<td align='left'><select size='5' style='min-width:220px;' id='available_languages' name='available_languages'>";
        $tempLang=GetAdditionalLanguagesFromSurveyID($surveyid);
        foreach (getLanguageData () as $langkey2 => $langname) {
            if ($langkey2 != $esrow['language'] && in_array($langkey2, $tempLang) == false) {  // base languag must not be shown here
                $editsurvey .= "<option id='".$langkey2."' value='".$langkey2."'";
                $editsurvey .= ">".$langname['description']."</option>\n";
            }
        }
        $editsurvey .= "</select></td>"
        . " </tr></table></li>\n";
        // Administrator...
        $editsurvey .= ""
        . "<li><label for='admin'>".$clang->gT("Administrator:")."</label>\n"
        . "<input type='text' size='50' id='admin' name='admin' value=\"{$esrow['admin']}\" /></li>\n"
        . "<li><label for='adminemail'>".$clang->gT("Admin Email:")."</label>\n"
        . "<input type='text' size='50' id='adminemail' name='adminemail' value=\"{$esrow['adminemail']}\" /></li>\n"
        . "<li><label for='bounce_email'>".$clang->gT("Bounce Email:")."</label>\n"
        . "<input type='text' size='50' id='bounce_email' name='bounce_email' value=\"{$esrow['bounce_email']}\" /></li>\n"
        . "<li><label for='faxto'>".$clang->gT("Fax to:")."</label>\n"
        . "<input type='text' size='50' id='faxto' name='faxto' value=\"{$esrow['faxto']}\" /></li></ul>\n";

        // End General TAB
        $editsurvey .= "</div>\n";
        */
        $data['action'] = "editsurveysettings";
        $data['clang'] = $clang;
        $data['esrow'] = $esrow;
        $data['surveyid'] = $surveyid;
        return $this->load->view('admin/survey/superview/superGeneralEditSurvey_view',$data, true); 
        
    }
    
    function _tabPresentationNavigation($esrow)
    {
        $clang = $this->limesurvey_lang;
        global $showXquestions,$showgroupinfo,$showqnumcode;
        
        /**
        // Presentation and navigation TAB
        $editsurvey = "<div id='presentation'><ul>\n";

        //Format
        $editsurvey .= "<li><label for='format'>".$clang->gT("Format:")."</label>\n"
        . "<select id='format' name='format'>\n"
        . "<option value='S'";
        if ($esrow['format'] == "S" || !$esrow['format']) {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Question by Question")."</option>\n"
            . "<option value='G'";
        if ($esrow['format'] == "G") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Group by Group")."</option>\n"
            . "<option value='A'";
        if ($esrow['format'] == "A") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("All in one")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //TEMPLATES
            $editsurvey .= "<li><label for='template'>".$clang->gT("Template:")."</label>\n"
            . "<select id='template' name='template'>\n";
        foreach (array_keys(gettemplatelist()) as $tname) {

            if ($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $this->session->userdata('USER_RIGHT_MANAGE_TEMPLATE') == 1 || hasTemplateManageRights($this->session->userdata("loginID"), $tname) == 1) {
                    $editsurvey .= "<option value='$tname'";
                if ($esrow['template'] && htmlspecialchars($tname) == $esrow['template']) {
                    $editsurvey .= " selected='selected'";
                } elseif (!$esrow['template'] && $tname == "default") {
                    $editsurvey .= " selected='selected'";
                }
                    $editsurvey .= ">$tname</option>\n";
                }
            }
            $editsurvey .= "</select>\n"
            . "</li>\n";

            $editsurvey .= "<li><label for='preview'>".$clang->gT("Template Preview:")."</label>\n"
            . "<img alt='".$clang->gT("Template preview image")."' id='preview' src='".sGetTemplateURL($esrow['template'])."/preview.png' />\n"
            . "</li>\n" ;

        //SHOW WELCOMESCRN
        $editsurvey .= "<li><label for='showwelcome'>" . $clang->gT("Show welcome screen?") . "</label>\n"
                . "<select id='showwelcome' name='showwelcome'>\n"
                . "<option value='Y'";
        if (!$esrow['showwelcome'] || $esrow['showwelcome'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">" . $clang->gT("Yes") . "</option>\n"
                . "<option value='N'";
        if ($esrow['showwelcome'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">" . $clang->gT("No") . "</option>\n"
                . "</select></li>\n";

        //Navigation Delay
        if (!isset($esrow['navigationdelay'])) 
        {
            $esrow['navigationdelay']=0;
        }
        $editsurvey .= "<li><label for='navigationdelay'>".$clang->gT("Navigation delay (seconds):")."</label>\n"
        . "<input type='text' value=\"{$esrow['navigationdelay']}\" name='navigationdelay' id='navigationdelay' size='12' maxlength='2' onkeypress=\"return goodchars(event,'0123456789')\" />\n"
        . "</li>\n";

        //Show Prev Button
        $editsurvey .= "<li><label for='allowprev'>".$clang->gT("Show [<< Prev] button")."</label>\n"
        . "<select id='allowprev' name='allowprev'>\n"
        . "<option value='Y'";
        if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

        //Show Question Index
        $editsurvey .= "<li><label for='allowjumps'>".$clang->gT("Show question index / allow jumping")."</label>\n"
                . "<select id='allowjumps' name='allowjumps'>\n"
                . "<option value='Y'";
        if (!isset($esrow['allowjumps']) || !$esrow['allowjumps'] || $esrow['allowjumps'] == "Y") {$editsurvey .= " selected='selected'";}
        $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
        if (isset($esrow['allowjumps']) && $esrow['allowjumps'] == "N") {$editsurvey .= " selected='selected'";}
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select></li>\n";

        //No Keyboard
        $editsurvey .= "<li><label for='nokeyboard'>".$clang->gT("Keyboard-less operation")."</label>\n"
                . "<select id='nokeyboard' name='nokeyboard'>\n"
                . "<option value='Y'";
        if (!isset($esrow['nokeyboard']) || !$esrow['nokeyboard'] || $esrow['nokeyboard'] == "Y") {$editsurvey .= " selected='selected'";}
        $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
        if (isset($esrow['nokeyboard']) && $esrow['nokeyboard'] == "N") {$editsurvey .= " selected='selected'";}
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select></li>\n";

	//Show Progress
	$editsurvey .= "<li><label for='showprogress'>".$clang->gT("Show progress bar")."</label>\n"
                . "<select id='showprogress' name='showprogress'>\n"
                . "<option value='Y'";
	if (!isset($esrow['showprogress']) || !$esrow['showprogress'] || $esrow['showprogress'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
	$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
	if (isset($esrow['showprogress']) && $esrow['showprogress'] == "N") {
            $editsurvey .= " selected='selected'";
        }
	$editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select></li>\n";

            //Result printing
            $editsurvey .= "<li><label for='printanswers'>".$clang->gT("Participants may print answers?")."</label>\n"
            . "<select id='printanswers' name='printanswers'>\n"
            . "<option value='Y'";
        if (!isset($esrow['printanswers']) || !$esrow['printanswers'] || $esrow['printanswers'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['printanswers']) && $esrow['printanswers'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //Public statistics
            $editsurvey .= "<li><label for='publicstatistics'>".$clang->gT("Public statistics?")."</label>\n"
            . "<select id='publicstatistics' name='publicstatistics'>\n"
            . "<option value='Y'";
        if (!isset($esrow['publicstatistics']) || !$esrow['publicstatistics'] || $esrow['publicstatistics'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['publicstatistics']) && $esrow['publicstatistics'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            //Public statistics
            $editsurvey .= "<li><label for='publicgraphs'>".$clang->gT("Show graphs in public statistics?")."</label>\n"
            . "<select id='publicgraphs' name='publicgraphs'>\n"
            . "<option value='Y'";
        if (!isset($esrow['publicgraphs']) || !$esrow['publicgraphs'] || $esrow['publicgraphs'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['publicgraphs']) && $esrow['publicgraphs'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";


            // End URL block
            $editsurvey .= "<li><label for='autoredirect'>".$clang->gT("Automatically load URL when survey complete?")."</label>\n"
            . "<select id='autoredirect' name='autoredirect'>";
            $editsurvey .= "<option value='Y'";
        if (isset($esrow['autoredirect']) && $esrow['autoredirect'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n";
            $editsurvey .= "<option value='N'";
        if (!isset($esrow['autoredirect']) || $esrow['autoredirect'] != "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>";

            // Show {THEREAREXQUESTIONS} block
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_showXquestions\">".$clang->gT('Show "There are X questions in this survey"')."</label>\n\t\t".'<input type="hidden" name="showXquestions" id="" value="';
	    $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_showXquestions" id="dis_showXquestions" disabled="disabled" value="';
	    $show_dis_post = "\" size=\"70\" />\n\t</li>\n";
        switch ($showXquestions) {
		case 'show':
		    $editsurvey .= $show_dis_pre.'Y'.$show_dis_mid.$clang->gT('Yes (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'hide':
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('No (Forced by the system administrator)').$show_dis_post;
		    break;
	    	case 'choose':
		default:
		    $sel_showxq = array( 'Y' => '' , 'N' => '' );
                if (isset($esrow['showXquestions'])) {
		    	$set_showxq = $esrow['showXquestions'];
			$sel_showxq[$set_showxq] = ' selected="selected"';
		    }
                if (empty($sel_showxq['Y']) && empty($sel_showxq['N'])) {
		    	$sel_showxq['Y'] = ' selected="selected"';
		    };
		    $editsurvey .= "\n\t<li>\n\t\t<label for=\"showXquestions\">".$clang->gT('Show "There are X questions in this survey"')."</label>\n\t\t"
		    . "<select id=\"showXquestions\" name=\"showXquestions\">\n\t\t\t"
		    . '<option value="Y"'.$sel_showxq['Y'].'>'.$clang->gT('Yes')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showxq['N'].'>'.$clang->gT('No')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    unset($sel_showxq,$set_showxq);
		    break;
	    };

            // Show {GROUPNAME} and/or {GROUPDESCRIPTION} block
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_showgroupinfo\">".$clang->gT('Show group name and/or group description')."</label>\n\t\t".'<input type="hidden" name="showgroupinfo" id="showgroupinfo" value="';
            $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_showgroupinfo" id="dis_showgroupinfo" disabled="disabled" value="';
        switch ($showgroupinfo) {
		case 'both':
		    $editsurvey .= $show_dis_pre.'B'.$show_dis_mid.$clang->gT('Show both (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'name':
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('Show group name only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'description':
		    $editsurvey .= $show_dis_pre.'D'.$show_dis_mid.$clang->gT('Show group description only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'none':
		    $editsurvey .= $show_dis_pre.'X'.$show_dis_mid.$clang->gT('Hide both (Forced by the system administrator)').$show_dis_post;
		    break;
	    	case 'choose':
		default:
		    $sel_showgri = array( 'B' => '' , 'D' => '' , 'N' => '' , 'X' => '' );
                if (isset($esrow['showgroupinfo'])) {
		    	$set_showgri = $esrow['showgroupinfo'];
                $sel_showgri[$set_showgri] = ' selected="selected"';
		    }
                if (empty($sel_showgri['B']) && empty($sel_showgri['D']) && empty($sel_showgri['N']) && empty($sel_showgri['X'])) {
		    	$sel_showgri['C'] = ' selected="selected"';
		    };
		    $editsurvey .= "\n\t<li>\n\t\t<label for=\"showgroupinfo\">".$clang->gT('Show group name and/or group description')."</label>\n\t\t"
		    . "<select id=\"showgroupinfo\" name=\"showgroupinfo\">\n\t\t\t"
		    . '<option value="B"'.$sel_showgri['B'].'>'.$clang->gT('Show both')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showgri['N'].'>'.$clang->gT('Show group name only')."</option>\n\t\t\t"
		    . '<option value="D"'.$sel_showgri['D'].'>'.$clang->gT('Show group description only')."</option>\n\t\t\t"
		    . '<option value="X"'.$sel_showgri['X'].'>'.$clang->gT('Hide both')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    unset($sel_showgri,$set_showgri);
		    break;
	    };

            // Show {QUESTION_CODE} and/or {QUESTION_NUMBER} block
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_showqnumcode\">".$clang->gT('Show question number and/or code')."</label>\n\t\t".'<input type="hidden" name="showqnumcode" id="showqnumcode" value="';
            $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_showqnumcode" id="dis_showqnumcode" disabled="disabled" value="';
        switch ($showqnumcode) {
		case 'none':
		    $editsurvey .= $show_dis_pre.'X'.$show_dis_mid.$clang->gT('Hide both (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'number':
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('Show question number only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'code':
		    $editsurvey .= $show_dis_pre.'C'.$show_dis_mid.$clang->gT('Show question code only (Forced by the system administrator)').$show_dis_post;
		    break;
		case 'both':
		    $editsurvey .= $show_dis_pre.'B'.$show_dis_mid.$clang->gT('Show both (Forced by the system administrator)').$show_dis_post;
		    break;
	    	case 'choose':
		default:
		    $sel_showqnc = array( 'B' => '' , 'C' => '' , 'N' => '' , 'X' => '' );
                if (isset($esrow['showqnumcode'])) {
		    	$set_showqnc = $esrow['showqnumcode'];
			$sel_showqnc[$set_showqnc] = ' selected="selected"';
		    }
                if (empty($sel_showqnc['B']) && empty($sel_showqnc['C']) && empty($sel_showqnc['N']) && empty($sel_showqnc['X'])) {
		    	$sel_showqnc['X'] = ' selected="selected"';
		    };
		    $editsurvey .= "\n\t<li>\n\t\t<label for=\"showqnumcode\">".$clang->gT('Show question number and/or code')."</label>\n\t\t"
		    . "<select id=\"showqnumcode\" name=\"showqnumcode\">\n\t\t\t"
		    . '<option value="B"'.$sel_showqnc['B'].'>'.$clang->gT('Show both')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showqnc['N'].'>'.$clang->gT('Show question number only')."</option>\n\t\t\t"
		    . '<option value="C"'.$sel_showqnc['C'].'>'.$clang->gT('Show question code only')."</option>\n\t\t\t"
		    . '<option value="X"'.$sel_showqnc['X'].'>'.$clang->gT('Hide both')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    unset($sel_showqnc,$set_showqnc);
		    break;
	    };

            // Show "No Answer" block
	    $shownoanswer = isset($shownoanswer)?$shownoanswer:'Y';
	    $show_dis_pre = "\n\t<li>\n\t\t<label for=\"dis_shownoanswer\">".$clang->gT('Show "No answer"')."</label>\n\t\t".'<input type="hidden" name="shownoanswer" id="shownoanswer" value="';
            $show_dis_mid = "\" />\n\t\t".'<input type="text" name="dis_shownoanswer" id="dis_shownoanswer" disabled="disabled" value="';
        switch ($shownoanswer) {
	    	case 0:
		    $editsurvey .= $show_dis_pre.'N'.$show_dis_mid.$clang->gT('Off (Forced by the system administrator)').$show_dis_post;
		    break;
	        case 2:
		    $sel_showno = array( 'Y' => '' , 'N' => '' );
                if (isset($esrow['shownoanswer'])) {
		    	$set_showno = $esrow['shownoanswer'];
			$sel_showno[$set_showno] = ' selected="selected"';
		    };
                if (empty($sel_showno)) {
		    	$sel_showno['Y'] = ' selected="selected"';
		    };
	    	    $editsurvey .= "\n\t<li>\n\t\t<label for=\"shownoanswer\">".$clang->gT('Show "No answer"')."</label>\n\t\t"
		    . "<select id=\"shownoanswer\" name=\"shownoanswer\">\n\t\t\t"
		    . '<option value="Y"'.$sel_showno['Y'].'>'.$clang->gT('Yes')."</option>\n\t\t\t"
		    . '<option value="N"'.$sel_showno['N'].'>'.$clang->gT('No')."</option>\n\t\t"
		    . "</select>\n\t</li>\n";
		    break;
		default:
		    $editsurvey .= $show_dis_pre.'Y'.$show_dis_mid.$clang->gT('On (Forced by the system administrator)').$show_dis_post;
		    break;
	    };

            // End Presention and navigation TAB
            $editsurvey .= "</ul></div>\n";
            
        */    
        if (!isset($esrow['navigationdelay'])) 
        {
            $esrow['navigationdelay']=0;
        }
        
        $this->load->helper('globalsettings');
        
        $shownoanswer = getGlobalSetting('shownoanswer')?getGlobalSetting('shownoanswer'):'Y';
        
        $data['clang'] = $clang;
        $data['esrow'] = $esrow;
        //$data['surveyid'] = $surveyid;
        $data['shownoanswer'] = $shownoanswer;
        $data['showXquestions'] = $showXquestions;
        $data['showgroupinfo'] = $showgroupinfo;
        $data['showqnumcode'] = $showqnumcode;
        return $this->load->view('admin/survey/superview/superPresentation_view',$data, true); 
        
    }
    
    function _tabPublicationAccess($esrow)
    {
        $clang = $this->limesurvey_lang;
        /**
        $editsurvey = "<div id='publication'><ul>\n";

             //Public Surveys
            $editsurvey .= "<li><label for='public'>".$clang->gT("List survey publicly:")."</label>\n"
            . "<select id='public' name='public'>\n"
            . "<option value='Y'";
        if (!isset($esrow['listpublic']) || !$esrow['listpublic'] || $esrow['listpublic'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if (isset($esrow['listpublic']) && $esrow['listpublic'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            // Self registration
            $editsurvey .= "<li><label for='allowregister'>".$clang->gT("Allow public registration?")."</label>\n"
            . "<select id='allowregister' name='allowregister'>\n"
            . "<option value='Y'";
        if ($esrow['allowregister'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['allowregister'] != "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";
            */
            // Start date
            $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
            $startdate='';
        if (trim($esrow['startdate']) != '') {
                $items = array($esrow['startdate'] , "Y-m-d H:i:s");
                $this->load->library('Date_Time_Converter',$items);
                $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($esrow['startdate'] , "Y-m-d H:i:s");
                $startdate=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
            }
            /**
            $editsurvey .= "<li><label for='startdate'>".$clang->gT("Start date/time:")."</label>\n"
            . "<input type='text' class='popupdatetime' id='startdate' size='20' name='startdate' value=\"{$startdate}\" /></li>\n";
            */
            // Expiration date
            $expires='';
        if (trim($esrow['expires']) != '') {
                $items = array($esrow['expires'] , "Y-m-d H:i:s");
                $this->load->library('Date_Time_Converter',$items);
                $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($esrow['expires'] , "Y-m-d H:i:s");
                $expires=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
            }
            /**
            $editsurvey .="<li><label for='expires'>".$clang->gT("Expiry date/time:")."</label>\n"
            . "<input type='text' class='popupdatetime' id='expires' size='20' name='expires' value=\"{$expires}\" /></li>\n";

            //COOKIES
            $editsurvey .= "<li><label for=''>".$clang->gT("Set cookie to prevent repeated participation?")."</label>\n"
            . "<select name='usecookie'>\n"
            . "<option value='Y'";
        if ($esrow['usecookie'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['usecookie'] != "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select>\n"
            . "</li>\n";

            // Use Captcha
            $editsurvey .= "<li><label for=''>".$clang->gT("Use CAPTCHA for").":</label>\n"
            . "<select name='usecaptcha'>\n"
            . "<option value='A'";
        if ($esrow['usecaptcha'] == "A") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='B'";
        if ($esrow['usecaptcha'] == "B") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ---------</option>\n"
            . "<option value='C'";
        if ($esrow['usecaptcha'] == "C") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("Survey Access")." / ------------ / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='D'";
        if ($esrow['usecaptcha'] == "D") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">------------- / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='X'";

        if ($esrow['usecaptcha'] == "X") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("Survey Access")." / ------------ / ---------</option>\n"
            . "<option value='R'";
        if ($esrow['usecaptcha'] == "R") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">------------- / ".$clang->gT("Registration")." / ---------</option>\n"
            . "<option value='S'";
        if ($esrow['usecaptcha'] == "S") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">------------- / ------------ / ".$clang->gT("Save & Load")."</option>\n"
            . "<option value='N'";
        if ($esrow['usecaptcha'] == "N") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">------------- / ------------ / ---------</option>\n"
            . "</select>\n</li>\n";

            // Email format
            $editsurvey .= "<li><label for=''>".$clang->gT("Use HTML format for token emails?")."</label>\n"
            . "<select name='htmlemail' onchange=\"alert('".$clang->gT("If you switch email mode, you'll have to review your email templates to fit the new format","js")."');\">\n"
            . "<option value='Y'";
        if ($esrow['htmlemail'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['htmlemail'] == "N") {
            $editsurvey .= " selected='selected'";
        }

            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

            // End Publication and access control TAB
            $editsurvey .= "</ul></div>\n";
        return $editsurvey;
        
        */
        $data['clang'] = $clang;
        $data['esrow'] = $esrow;
        //$data['surveyid'] = $surveyid;
        $data['startdate'] = $startdate;
        $data['expires'] = $expires;
        //$data['showgroupinfo'] = $showgroupinfo;
        //$data['showqnumcode'] = $showqnumcode;
        return $this->load->view('admin/survey/superview/superPublication_view',$data, true); 
        
    }
    
    function _tabNotificationDataManagement($esrow)
    {
        $clang = $this->limesurvey_lang;
        
        /**
        // Notification and Data management TAB
            $editsurvey = "<div id='notification'><ul>\n";


            //NOTIFICATION
            $editsurvey .= "<li><label for='emailnotificationto'>".$clang->gT("Send basic admin notification email to:")."</label>\n"
            . "<input size='70' type='text' value=\"{$esrow['emailnotificationto']}\" id='emailnotificationto' name='emailnotificationto' />\n"
            . "</li>\n";

            //EMAIL SURVEY RESPONSES TO
            $editsurvey .= "<li><label for='emailresponseto'>".$clang->gT("Send detailed admin notification email to:")."</label>\n"
            . "<input size='70' type='text' value=\"{$esrow['emailresponseto']}\" id='emailresponseto' name='emailresponseto' />\n"
            . "</li>\n";

            //ANONYMOUS
            $editsurvey .= "<li><label for=''>".$clang->gT("Anonymized responses?")."\n";
            // warning message if anonymous + tokens used
            $editsurvey .= "\n"
            . "<script type=\"text/javascript\"><!-- \n"
            . "function alertPrivacy()\n"
            . "{\n"
            . "if (document.getElementById('tokenanswerspersistence').value == 'Y')\n"
            . "{\n"
            . "alert('".$clang->gT("You can't use Anonymized responses when Token-based answers persistence is enabled.","js")."');\n"
            . "document.getElementById('anonymized').value = 'N';\n"
            . "}\n"
            . "else if (document.getElementById('anonymized').value == 'Y')\n"
            . "{\n"
            . "alert('".$clang->gT("Warning").": ".$clang->gT("If you turn on the -Anonymized responses- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.","js")."');\n"
            . "}\n"
            . "}"
            . "//--></script></label>\n";

        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['anonymized'] == "N") {
                $editsurvey .= " " . $clang->gT("This survey is NOT anonymous.");
            } else {
                $editsurvey .= $clang->gT("Answers to this survey are anonymized.");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='anonymized' value=\"{$esrow['anonymized']}\" />\n";
        } else {
                $editsurvey .= "<select id='anonymized' name='anonymized' onchange='alertPrivacy();'>\n"
                . "<option value='Y'";
            if ($esrow['anonymized'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['anonymized'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";

            // date stamp
            $editsurvey .= "<li><label for='datestamp'>".$clang->gT("Date Stamp?")."</label>\n";
        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['datestamp'] != "Y") {
                $editsurvey .= " " . $clang->gT("Responses will not be date stamped.");
            } else {
                $editsurvey .= $clang->gT("Responses will be date stamped.");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='datestamp' value=\"{$esrow['datestamp']}\" />\n";
        } else {
                $editsurvey .= "<select id='datestamp' name='datestamp' onchange='alertPrivacy();'>\n"
                . "<option value='Y'";
            if ($esrow['datestamp'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['datestamp'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";

            // Ip Addr
            $editsurvey .= "<li><label for=''>".$clang->gT("Save IP Address?")."</label>\n";

        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['ipaddr'] != "Y") {
                $editsurvey .= " " . $clang->gT("Responses will not have the IP address logged.");
            } else {
                $editsurvey .= $clang->gT("Responses will have the IP address logged");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='ipaddr' value='".$esrow['ipaddr']."' />\n";
        } else {
                $editsurvey .= "<select name='ipaddr'>\n"
                . "<option value='Y'";
            if ($esrow['ipaddr'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['ipaddr'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }

            $editsurvey .= "</li>\n";

            // begin REF URL Block
            $editsurvey .= "<li><label for=''>".$clang->gT("Save referrer URL?")."</label>\n";

        if ($esrow['active'] == "Y") {
                $editsurvey .= "\n";
            if ($esrow['refurl'] != "Y") {
                $editsurvey .= " " . $clang->gT("Responses will not have their referring URL logged.");
            } else {
                $editsurvey .= $clang->gT("Responses will have their referring URL logged.");
            }
                $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
                . "</font>\n";
                $editsurvey .= "<input type='hidden' name='refurl' value='".$esrow['refurl']."' />\n";
        } else {
                $editsurvey .= "<select name='refurl'>\n"
                . "<option value='Y'";
            if ($esrow['refurl'] == "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
                . "<option value='N'";
            if ($esrow['refurl'] != "Y") {
                $editsurvey .= " selected='selected'";
            }
                $editsurvey .= ">".$clang->gT("No")."</option>\n"
                . "</select>\n";
            }
            $editsurvey .= "</li>\n";
            // BENBUN - END REF URL Block

            // Enable assessments
            $editsurvey .= "<li><label for=''>".$clang->gT("Enable assessment mode?")."</label>\n"
            . "<select id='assessments' name='assessments'>\n"
            . "<option value='Y'";
        if ($esrow['assessments'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['assessments'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

            // Allow editing answers after completion 
            $editsurvey .= "<li><label for=''>".$clang->gT("Allow editing answers after completion?")."</label>\n"
            . "<select id='alloweditaftercompletion' name='alloweditaftercompletion' onchange=\"javascript: if (document.getElementById('private').value == 'Y') {alert('".$clang->gT("This option can't be set if Anonymous answers are used","js")."'); this.value='N';}\">\n"
            . "<option value='Y'";
            if ($esrow['alloweditaftercompletion'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if ($esrow['alloweditaftercompletion'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></li>\n";

        // Save timings
        $editsurvey .= "<li><label for='savetimings'>".$clang->gT("Save timings?")."</label>\n";
        if ($esrow['active']=="Y")
        {
            $editsurvey .= "\n";
            if ($esrow['savetimings'] != "Y") {$editsurvey .= " ".$clang->gT("Timings will not be saved.");}
            else {$editsurvey .= $clang->gT("Timings will be saved.");}
            $editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
            . "</font>\n";
            $editsurvey .= "<input type='hidden' name='savetimings' value='".$esrow['savetimings']."' />\n";
		}
		else
        {
			$editsurvey .= "<select id='savetimings' name='savetimings'>\n"
			. "<option value='Y'";
			if (!isset($esrow['savetimings']) || !$esrow['savetimings'] || $esrow['savetimings'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "<option value='N'";
			if (isset($esrow['savetimings']) && $esrow['savetimings'] == "N") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "</select>\n"
			. "</li>\n";
		}
        //ALLOW SAVES
        $editsurvey .= "<li><label for='allowsave'>".$clang->gT("Participant may save and resume later?")."</label>\n"
        . "<select id='allowsave' name='allowsave'>\n"
        . "<option value='Y'";
        if (!$esrow['allowsave'] || $esrow['allowsave'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
        if ($esrow['allowsave'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></li>\n";


        // End Notification and Data management TAB
        $editsurvey .= "</ul></div>\n";
        return $editsurvey; */
        $data['clang'] = $clang;
        $data['esrow'] = $esrow;
        
        //$data['showqnumcode'] = $showqnumcode;
        return $this->load->view('admin/survey/superview/superNotification_view',$data, true); 
        
    }
    
    function _tabTokens($esrow)
    {
        $clang = $this->limesurvey_lang;
        // Tokens TAB
        /**
        $editsurvey = "<div id='tokens'><ul>\n";
        // Token answers persistence
        $editsurvey .= "<li><label for=''>".$clang->gT("Enable token-based response persistence?")."</label>\n"
        . "<select id='tokenanswerspersistence' name='tokenanswerspersistence' onchange=\"javascript: if (document.getElementById('anonymized').value == 'Y') {alert('".$clang->gT("This option can't be set if the `Anonymized responses` option is active.","js")."'); this.value='N';}\">\n"
        . "<option value='Y'";
        if ($esrow['tokenanswerspersistence'] == "Y") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
        . "<option value='N'";
        if ($esrow['tokenanswerspersistence'] == "N") {
            $editsurvey .= " selected='selected'";
        }
        $editsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></li>\n";

        //Set token length
        $editsurvey .= "<li><label for='tokenlength'>".$clang->gT("Set token length to:")."</label>\n"
        . "<input type='text' value=\"{$esrow['tokenlength']}\" name='tokenlength' id='tokenlength' size='12' maxlength='2' onkeypress=\"return goodchars(event,'0123456789')\" />\n"
        . "</li>\n";

        // End Tokens TAB
        $editsurvey .= "</ul></div>\n";
        return $editsurvey; */
        $data['clang'] = $clang;
        $data['esrow'] = $esrow;
        
        //$data['showqnumcode'] = $showqnumcode;
        return $this->load->view('admin/survey/superview/superTokens_view',$data, true); 
    }
    
    function _tabImport()
    {
        $clang = $this->limesurvey_lang;
        /**
        // Import TAB
        $editsurvey = "<div id='import'>\n";

        // Import survey
        $editsurvey .= "<form enctype='multipart/form-data' class='form30' id='importsurvey' name='importsurvey' action='../database/index/insertsurvey' method='post' onsubmit='return validatefilename(this,\"" . $clang->gT('Please select a file to import!', 'js') . "\");'>\n"
                    . "<ul>\n"
                    . "<li><label for='the_file'>" . $clang->gT("Select survey structure file (*.lss, *.csv):") . "</label>\n"
                    . "<input id='the_file' name=\"the_file\" type=\"file\" size=\"50\" /></li>\n"
                    . "<li><label for='translinksfields'>" . $clang->gT("Convert resource links and INSERTANS fields?") . "</label>\n"
                    . "<input id='translinksfields' name=\"translinksfields\" type=\"checkbox\" checked='checked'/></li></ul>\n"
                    . "<p><input type='submit' value='" . $clang->gT("Import survey") . "' />\n"
                    . "<input type='hidden' name='action' value='importsurvey' /></p></form>\n";

        // End Import TAB
        $editsurvey .= "</div>\n";
        return $editsurvey; */
        $data['clang'] = $clang;
        //$data['esrow'] = $esrow;
        
        //$data['showqnumcode'] = $showqnumcode;
        return $this->load->view('admin/survey/superview/superImport_view',$data, true); 
    }
    
    function _tabCopy()
    {
        $clang = $this->limesurvey_lang;
        /**
        // Copy survey TAB
        $editsurvey = "<div id='copy'>\n";
        
        // Copy survey
        $editsurvey .= "<form class='form30' action='../database/index/insertsurvey' id='copysurveyform' method='post'>\n"
                    . "<ul>\n"
                    . "<li><label for='copysurveylist'><span class='annotationasterisk'>*</span>" . $clang->gT("Select survey to copy:") . "</label>\n"
                    . "<select id='copysurveylist' name='copysurveylist'>\n"
                    . getsurveylist(false, true) . "</select> <span class='annotation'>" . $clang->gT("*Required") . "</span></li>\n"
                    . "<li><label for='copysurveyname'><span class='annotationasterisk'>*</span>" . $clang->gT("New survey title:") . "</label>\n"
                    . "<input type='text' id='copysurveyname' size='82' maxlength='200' name='copysurveyname' value='' />"
                    . "<span class='annotation'>" . $clang->gT("*Required") . "</span></li>\n"
                    . "<li><label for='copysurveytranslinksfields'>" . $clang->gT("Convert resource links and INSERTANS fields?") . "</label>\n"
                    . "<input id='copysurveytranslinksfields' name=\"copysurveytranslinksfields\" type=\"checkbox\" checked='checked'/></li>\n"
                    . "<li><label for='copysurveyexcludequotas'>" . $clang->gT("Exclude quotas?") . "</label>\n"
                    . "<input id='copysurveyexcludequotas' name=\"copysurveyexcludequotas\" type=\"checkbox\" /></li>\n"
                    . "<li><label for='copysurveyexcludeanswers'>" . $clang->gT("Exclude answers?") . "</label>\n"
                    . "<input id='copysurveyexcludeanswers' name=\"copysurveyexcludeanswers\" type=\"checkbox\" /></li>\n"
                    . "<li><label for='copysurveyresetconditions'>" . $clang->gT("Reset conditions?") . "</label>\n"
                    . "<input id='copysurveyresetconditions' name=\"copysurveyresetconditions\" type=\"checkbox\" /></li></ul>\n"
                    . "<p><input type='submit' value='" . $clang->gT("Copy survey") . "' />\n"
                    . "<input type='hidden' name='action' value='copysurvey' /></p></form>\n";

        // End Copy survey TAB
        $editsurvey .= "</div>\n";
               
        return $editsurvey;
        */
        
        $data['clang'] = $clang;
        //$data['esrow'] = $esrow;
        
        //$data['showqnumcode'] = $showqnumcode;
        return $this->load->view('admin/survey/superview/superCopy_view',$data, true); 
    }
    
    
    function _tabResourceManagement($surveyid)
    {
        $clang = $this->limesurvey_lang;
        global $sCKEditorURL;
        // TAB Uploaded Resources Management
        $ZIPimportAction = " onclick='if (validatefilename(this.form,\"".$clang->gT('Please select a file to import!','js')."\")) {this.form.submit();}'";
        if (!function_exists("zip_open")) {
            $ZIPimportAction = " onclick='alert(\"".$clang->gT("zip library not supported by PHP, Import ZIP Disabled","js")."\");'";
        }

        $disabledIfNoResources = '';
        if (hasResources($surveyid, 'survey') === false) {
            $disabledIfNoResources = " disabled='disabled'";
        }
        /**
        // functionality not ported 
        $editsurvey = "<div id='resources'>\n"
            . "<form enctype='multipart/form-data'  class='form30' id='importsurveyresources' name='importsurveyresources' action='../database/index/insertsurvey' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
            . "<input type='hidden' name='sid' value='$surveyid' />\n"
            . "<input type='hidden' name='action' value='importsurveyresources' />\n"
            . "<ul>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' onclick='window.open(\"$sCKEditorURL/editor/filemanager/browser/default/browser.html?Connector=../../connectors/php/connector.php\", \"_blank\")' value=\"".$clang->gT("Browse Uploaded Resources")."\" $disabledIfNoResources /></li>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' onclick='window.open(\"$scriptname?action=exportsurvresources&amp;sid={$surveyid}\", \"_blank\")' value=\"".$clang->gT("Export Resources As ZIP Archive")."\" $disabledIfNoResources /></li>\n"
            . "<li><label for='the_file'>".$clang->gT("Select ZIP File:")."</label>\n"
            . "<input id='the_file' name='the_file' type='file' size='50' /></li>\n"
            . "<li><label>&nbsp;</label>\n"
            . "<input type='button' value='".$clang->gT("Import Resources ZIP Archive")."' $ZIPimportAction /></li>\n"
            . "</ul></form>\n";

        // End TAB Uploaded Resources Management
        $editsurvey .= "</div>\n";
        return $editsurvey; */
        $data['clang'] = $clang;
        //$data['esrow'] = $esrow;
        $data['ZIPimportAction'] = $ZIPimportAction;
        $data['disabledIfNoResources'] = $disabledIfNoResources;
        $dqata['sCKEditorURL'] = $sCKEditorURL;
        //$data['showqnumcode'] = $showqnumcode;
        return $this->load->view('admin/survey/superview/superResourceManagement_view',$data, true); 
        
    }
    
 }