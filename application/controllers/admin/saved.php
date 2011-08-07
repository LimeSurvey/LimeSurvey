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
 * $Id: saved.php 10576 2011-07-23 12:56:38Z ssachdeva $
 */
 
 class saved extends SurveyCommonController {
    
    function __construct()
	{
		parent::__construct();
	}
    
    function view($surveyid)
    {
        self::_js_admin_includes(base_url().'scripts/jquery/jquery.tablesorter.min.js');
        self::_js_admin_includes(base_url().'scripts/admin/saved.js');
        self::_getAdminHeader();
        
        if(bHasSurveyPermission($surveyid,'responses','read')) 
        {
            $clang = $this->limesurvey_lang;
            $thissurvey=getSurveyInfo($surveyid);
            
            $savedsurveyoutput = "<div class='menubar'>\n"
            . "<div class='menubar-title ui-widget-header'><span style='font-weight:bold;'>\n";
            $savedsurveyoutput .= $clang->gT("Saved Responses")."</span> ".$thissurvey['name']." (ID: $surveyid)</div>\n"
            . "<div class='menubar-main'>\n"
            . "<div class='menubar-left'>\n";
            
            $savedsurveyoutput .= self::_savedmenubar($surveyid);
            
            $savedsurveyoutput .= "</div></div></div>\n";
            
            $savedsurveyoutput .= "<div class='header ui-widget-header'>".$clang->gT("Saved Responses:") . " ". getSavedCount($surveyid)."</div><p>";
            
            $data['display'] = $savedsurveyoutput;
            $this->load->view('survey_view',$data);
            self::_showSavedList($surveyid);
        }
        
        self::_loadEndScripts();
                
                
	   self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
        
        
    }
    
    function delete()
    {
        $surveyid=$this->input->post('sid');
        $srid=$this->input->post('srid');
        $scid=$this->input->post('scid');
        $subaction=$this->input->post('subaction');
        $surveytable = $this->db->dbprefix."survey_".$surveyid;
        
        if ($subaction == "delete" && $surveyid && $scid)
        {
            $query = "DELETE FROM ".$this->db->dbprefix."saved_control
        			  WHERE scid=$scid
        			  AND sid=$surveyid
        			  ";
            $this->load->helper('database');
            if ($result = db_execute_assosc($query))
            {
                //If we were succesful deleting the saved_control entry,
                //then delete the rest
                $query = "DELETE FROM {$surveytable} WHERE id={$srid}";
                $result = db_execute_assosc($query) or die("Couldn't delete");
        
            }
            else
            {
                show_error("Couldn't delete<br />$query<br />");
            }
        }
        redirect("admin/saved/view/".$surveyid,'refresh');
    }
    
    function _showSavedList($surveyid)
    {
        //global $dbprefix, $connect, $clang, $savedsurveyoutput, $scriptname, $imageurl, $surrows;
        $this->load->helper('database');
        
        $query = "SELECT scid, srid, identifier, ip, saved_date, email, access_code\n"
        ."FROM ".$this->db->dbprefix."saved_control\n"
        ."WHERE sid=$surveyid\n"
        ."ORDER BY saved_date desc";
        $result = db_execute_assoc($query) or safe_die ("Couldn't summarise saved entries<br />$query<br />");
        if ($result->num_rows() > 0)
        {
            
            $data['result'] = $result;
            $data['clang'] = $clang;
            $data['surveyid'] = $surveyid;
            
            $this->load->view('admin/Saved/savedlist_view',$data);
        }
    }
    
    //				[<a href='saved.php?sid=$surveyid&amp;action=remind&amp;scid=".$row['scid']."'>".$clang->gT("Remind")."</a>]
    //               c_schmitz: Since its without function at the moment i removed it from the above lines
    
    function _savedmenubar($surveyid)
    {
        //global $surveyid, $scriptname, $imageurl, $clang;
        //BROWSE MENU BAR
        $clang = $this->limesurvey_lang;
        if (!isset($surveyoptions)) {$surveyoptions="";}
        $surveyoptions .= "<a href='".site_url('admin/survey/view/'.$surveyid)."' title='".$clang->gTview("Return to survey administration")."' >" .
    			"<img name='Administration' src='".$this->config->item('imageurl')."/home.png' alt='".$clang->gT("Return to survey administration")."' align='left'></a>\n";
        /*	. "\t\t\t<img src='$imageurl/blank.gif' alt='' width='11' border='0' hspace='0' align='left'>\n"
         . "\t\t\t<img src='$imageurl/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
         . "\t\t\t<a href='$scriptname?action=saved&amp;sid=$surveyid' " .
         "title='".$clang->gTview("Show summary information")."'>" .
         "<img name='SurveySummary' src='$imageurl/summary.png' alt='".$clang->gT("Show summary information")."' align='left'></a>\n"
         . "\t\t\t<a href='$scriptname?action=saved&amp;sid=$surveyid&amp;subaction=all' title='".$clang->gTview("Display Responses")."'>"
         . "<img name='ViewAll' src='$imageurl/document.png' alt='".$clang->gT("Display Responses")."' align='left'></a>\n"
         //. "\t\t\t<input type='image' name='ViewLast' src='$imageurl/viewlast.png' title='"
         //. $clang->gT("Display Last 50 Responses")."'  align='left'  onclick=\"window.open('saved.php?sid=$surveyid&action=all&limit=50&order=desc', '_top')\">\n"
         . "\t\t\t<img src='$imageurl/seperator.gif' border='0' hspace='0' align='left' alt=''>\n";*/
        return $surveyoptions;
    }
 
 
 }