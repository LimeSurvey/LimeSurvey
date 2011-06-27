<?php
/*
 * LimeSurvey
 * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
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
 * Survey Common Controller
 *
 * This controller contains common functions for survey related views.
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 * @author		LimeSurvey
 */
 class SurveyCommonController extends AdminController {
 
 	/**
	 * Constructor
	 */
    function __construct()
	{
		parent::__construct();
	}

    /**
	 * Shows admin menu for surveys
	 * @param int Survey id
	 */
    function _surveybar($surveyid)
    {
    	//$this->load->helper('surveytranslator');
    	$clang = $this->limesurvey_lang;
		echo $this->config->item('gid');
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        $condition = array('sid' => $surveyid, 'language' => $baselang);
        $this->load->model('surveys_model');
        //$sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
        $sumresult1 = $this->surveys_model->getDataOnSurvey($surveyid); //$sumquery1, 1) ; //Checked
        if ($sumresult1->num_rows()==0){die('Invalid survey id');} //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->row_array();
        $surveyinfo = array_map('FlattenText', $surveyinfo);
        //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
        $activated = $surveyinfo['active'];

        $js_admin_includes = $this->config->item("js_admin_includes");
        $js_admin_includes[]=$this->config->item('generalscripts').'jquery/jquery.coookie.js';
        $js_admin_includes[]=$this->config->item('generalscripts').'jquery/superfish.js';
        $js_admin_includes[]=$this->config->item('generalscripts').'jquery/hoverIntent.js';
        $js_admin_includes[]=$this->config->item('adminscripts').'surveytoolbar.js';
        $css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
		$this->config->set_item("css_admin_includes", $css_admin_includes);
        $this->config->set_item("js_admin_includes", $js_admin_includes); 
        
		//Parse data to send to view
		$data['clang']=$clang;
		$data['surveyinfo']=$surveyinfo;
		$data['surveyid']=$surveyid;
		
		// ACTIVATE SURVEY BUTTON
		$data['activated'] = ($activated=="Y") ? true : false;
		$data['imageurl'] = $this->config->item('imageurl');
		
        $condition = array('sid' => $surveyid, 'parent_qid' => 0, 'language' => $baselang);
        $this->load->model('questions_model');      
        //$sumquery3 =  "SELECT * FROM ".db_table_name('questions')." WHERE sid={$surveyid} AND parent_qid=0 AND language='".$baselang."'"; //Getting a count of questions for this survey
        $sumresult3 = $this->questions_model->getAllRecords($condition); //$connect->Execute($sumquery3); //Checked
        $sumcount3 = $sumresult3->num_rows();
		
		$data['canactivate'] = $sumcount3>0 && bHasSurveyPermission($surveyid,'surveyactivation','update');
		$data['candeactivate'] = bHasSurveyPermission($surveyid,'surveyactivation','update');
		$data['expired'] = $surveyinfo['expires']!='' && ($surveyinfo['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $this->config->item('timeadjust')));
		$data['notstarted'] = ($surveyinfo['startdate']!='') && ($surveyinfo['startdate'] > date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $this->config->item('timeadjust')));
		
        // Start of suckerfish menu
        // TEST BUTTON
  		if ($activated == "N")
        {
            $data['icontext']=$clang->gT("Test This Survey");
            $data['icontext2']=$clang->gTview("Test This Survey");
        } else
        {
            $data['icontext']=$clang->gT("Execute This Survey");
            $data['icontext2']=$clang->gTview("Execute This Survey");
        }
		
        $data['baselang'] = GetBaseLanguageFromSurveyID($surveyid);
 		$data['onelanguage'] = (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0);
		
		$tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
		$data['additionallanguages'] = $tmp_survlangs;
        $tmp_survlangs[] = $data['baselang'];
		rsort($tmp_survlangs);
		$data['languagelist'] = $tmp_survlangs;
		
		$data['hasadditionallanguages']=(count($data['additionallanguages']) > 0);

        // EDIT SURVEY TEXT ELEMENTS BUTTON
        $data['surveylocale']=bHasSurveyPermission($surveyid,'surveylocale','read');
        // EDIT SURVEY SETTINGS BUTTON
        $data['surveysettings']=bHasSurveyPermission($surveyid,'surveysettings','read');
        // Survey permission item
        $data['surveysecurity']=($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1 || $surveyinfo['owner_id'] == $this->session->userdata('loginID'));
         // CHANGE QUESTION GROUP ORDER BUTTON
        $data['surveycontent']=bHasSurveyPermission($surveyid,'surveycontent','read');
		$data['groupsum']=(getGroupSum($surveyid,$surveyinfo['language'])>1);
        // SET SURVEY QUOTAS BUTTON
        $data['quotas'] = bHasSurveyPermission($surveyid,'quotas','read');
        // Assessment menu item
        $data['assessments'] = bHasSurveyPermission($surveyid,'assessments','read');
        // EDIT SURVEY TEXT ELEMENTS BUTTON
        // End if survey properties

        // Tools menu item     
        // Delete survey item
        $data['surveydelete'] = bHasSurveyPermission($surveyid,'survey','delete');
        // Translate survey item
        $data['surveytranslate'] = bHasSurveyPermission($surveyid,'translations','read');
        // RESET SURVEY LOGIC BUTTON
        //$sumquery6 = "SELECT count(*) FROM ".db_table_name('conditions')." as c, ".db_table_name('questions')." as q WHERE c.qid = q.qid AND q.sid=$surveyid"; //Getting a count of conditions for this survey
        $this->load->model('conditions_model');
        $query = $this->conditions_model->getCountOfConditions($surveyid);
        $sumcount6 = $query->row_array(); //$connect->GetOne($sumquery6); //Checked
        $data['surveycontent'] = bHasSurveyPermission($surveyid,'surveycontent','update');
		$data['conditionscount'] = ($sumcount6 > 0);
        // Eport menu item
        $data['surveyexport']=bHasSurveyPermission($surveyid,'surveycontent','export');
        // PRINTABLE VERSION OF SURVEY BUTTON
        // SHOW PRINTABLE AND SCANNABLE VERSION OF SURVEY BUTTON

        //browse responses menu item
        $data['respstatsread']=bHasSurveyPermission($surveyid,'responses','read') || bHasSurveyPermission($surveyid,'statistics','read');
        // Data entry screen menu item
        $data['responsescreate']=bHasSurveyPermission($surveyid,'responses','create');
        $data['responsesread'] = bHasSurveyPermission($surveyid,'responses','read');
        // TOKEN MANAGEMENT BUTTON
		$data['tokenmanagement'] = bHasSurveyPermission($surveyid,'surveysettings','update') || bHasSurveyPermission($surveyid,'tokens','read');
        
        $data['gid'] = $gid = $this->config->item('gid');
        
        if (bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            $data['permission']= true;
        }
        else
        {
            $data['gid'] = $gid =null;
            $qid=null;
        } 
        
        if (getgrouplistlang($gid, $baselang))
        {
            $data['groups']= getgrouplistlang($gid, $baselang);
        }
        else
        {
            $data['groups']= "<option>".$clang->gT("None")."</option>";
        }
        
        $data['GidPrev'] = $GidPrev = getGidPrevious($surveyid, $gid);
        
        $data['GidNext'] = $GidNext = getGidNext($surveyid, $gid);
        $data['activated'] = $activated;
        
        $this->load->view("admin/survey/surveybar",$data);
		
        
        /**
         ////////////////////////////////////////////////////////////////////////
        // QUESTION GROUP TOOLBAR
        ////////////////////////////////////////////////////////////////////////

        $surveysummary.= "<div class='menubar-right'>\n";
        if (bHasSurveyPermission($surveyid,'surveycontent','read'))
        {
            $surveysummary .= "<span class=\"boxcaption\">".$clang->gT("Question groups").":</span>"
            . "<select name='groupselect' onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";

            if (getgrouplistlang($gid, $baselang))
            {
                $surveysummary .= getgrouplistlang($gid, $baselang);
            }
            else
            {
                $surveysummary .= "<option>".$clang->gT("None")."</option>\n";
            }
            $surveysummary .= "</select>\n";
        }
        else
        {
            $gid=null;
            $qid=null;
        }

        // QUICK NAVIGATION TO PREVIOUS AND NEXT QUESTION GROUP
        // TODO: Fix functionality to previous and next question group buttons (Andrie)
        $GidPrev = getGidPrevious($surveyid, $gid);
        $surveysummary .= "<span class='arrow-wrapper'>";
        if ($GidPrev != "")
        {
          $link = site_url("admin/index/index/$surveyid/$GidPrev");
          $surveysummary .= ""
            . "<a href='{$link}'>"
            . "<img src='".$this->config->item('imageurl')."/previous_20.png' title='' alt='".$clang->gT("Previous question group")."' "
            ."name='questiongroupprevious' ".$clang->gT("Previous question group")."/> </a>";
        }
        else
        {
          $surveysummary .= ""
            . "<img src='".$this->config->item('imageurl')."/previous_disabled_20.png' title='' alt='".$clang->gT("No previous question group")."' "
            ."name='noquestiongroupprevious' />";
        }

        $GidNext = getGidNext($surveyid, $gid);
        if ($GidNext != "")
        {
          $link = site_url("admin/index/index/$surveyid/$GidNext");
          $surveysummary .= ""
            . "<a href='{$link}'>"
            . "<img src='".$this->config->item('imageurl')."/next_20.png' title='' alt='".$clang->gT("Next question group")."' "
            ."name='questiongroupnext' /> </a>";
        }
        else
        {
          $surveysummary .= ""
            . "<img src='".$this->config->item('imageurl')."/next_disabled_20.png' title='' alt='".$clang->gT("No next question group")."' "
            ."name='noquestiongroupnext' />";
        }
		$surveysummary .= "</span>";


        // ADD NEW GROUP TO SURVEY BUTTON

        if(bHasSurveyPermission($surveyid,'surveycontent','create'))
        {
            if ($activated == "Y")
            {
                $surveysummary .= "<a href='#'>"
                ."<img src='".$this->config->item('imageurl')."/add_disabled.png' title='' alt='".$clang->gT("Disabled").' - '.$clang->gT("This survey is currently active.")."' " .
                " name='AddNewGroup' /></a>\n";
            }
            else
            {
                $link = site_url("admin/addgroup/index/$surveyid");
                $surveysummary .= "<a href=\"#\" onclick=\"window.open('$link', '_top')\""
                . " title=\"".$clang->gTview("Add new group to survey")."\">"
                . "<img src='".$this->config->item('imageurl')."/add.png' alt='".$clang->gT("Add new group to survey")."' name='AddNewGroup' /></a>\n";
            }
        }
        $surveysummary .= "<img src='".$this->config->item('imageurl')."/seperator.gif' alt='' />\n"
        . "<img src='".$this->config->item('imageurl')."/blank.gif' width='15' alt='' />"
        . "<input type='image' src='".$this->config->item('imageurl')."/minus.gif' title='". $clang->gT("Hide details of this Survey")."' "
        . "alt='". $clang->gT("Hide details of this Survey")."' name='MinimiseSurveyWindow' "
        . "onclick='document.getElementById(\"surveydetails\").style.display=\"none\";' />\n";

        $surveysummary .= "<input type='image' src='".$this->config->item('imageurl')."/plus.gif' title='". $clang->gT("Show details of this survey")."' "
        . "alt='". $clang->gT("Show details of this survey")."' name='MaximiseSurveyWindow' "
        . "onclick='document.getElementById(\"surveydetails\").style.display=\"\";' />\n";

        if (!$gid)
        {
            $link = site_url("admin/index/index");
            $surveysummary .= "<input type='image' src='".$this->config->item('imageurl')."/close.gif' title='". $clang->gT("Close this survey")."' "
            . "alt='".$clang->gT("Close this survey")."' name='CloseSurveyWindow' "
            . "onclick=\"window.open('$link', '_top')\" />\n";
        }
        else
        {
            $surveysummary .= "<img src='".$this->config->item('imageurl')."/blank.gif' width='18' alt='' />\n";
        }

        $surveysummary .= "</div>\n"
        . "</div>\n"
        . "</div>\n";
        */
        
        
    }
    
	/**
	 * Show survey summary
	 * @param int Survey id
	 */
    function _surveysummary($surveyid)
    {
        $clang = $this->limesurvey_lang;
		
		$baselang = GetBaseLanguageFromSurveyID($surveyid);
        $condition = array('sid' => $surveyid, 'language' => $baselang);
        $this->load->model('surveys_model');
        //$sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
        $sumresult1 = $this->surveys_model->getDataOnSurvey($surveyid); //$sumquery1, 1) ; //Checked
        if ($sumresult1->num_rows()==0){die('Invalid survey id');} //  if surveyid is invalid then die to prevent errors at a later time
        $surveyinfo = $sumresult1->row_array();
        $surveyinfo = array_map('FlattenText', $surveyinfo);
        //$surveyinfo = array_map('htmlspecialchars', $surveyinfo);
        $activated = $surveyinfo['active'];
		
		$condition = array('sid' => $surveyid, 'parent_qid' => 0, 'language' => $baselang);
        $this->load->model('questions_model');      
        //$sumquery3 =  "SELECT * FROM ".db_table_name('questions')." WHERE sid={$surveyid} AND parent_qid=0 AND language='".$baselang."'"; //Getting a count of questions for this survey
        $sumresult3 = $this->questions_model->getAllRecords($condition); //$connect->Execute($sumquery3); //Checked
        $sumcount3 = $sumresult3->num_rows();
        
		$condition = array('sid' => $surveyid, 'language' => $baselang);
		$this->load->model('groups_model');
		//$sumquery2 = "SELECT * FROM ".db_table_name('groups')." WHERE sid={$surveyid} AND language='".$baselang."'"; //Getting a count of groups for this survey
		$sumresult2 = $this->groups_model->getAllRecords($condition); //$connect->Execute($sumquery2); //Checked
		$sumcount2 = $sumresult2->num_rows();
        
        //SURVEY SUMMARY

        $aAdditionalLanguages = GetAdditionalLanguagesFromSurveyID($surveyid);
        $surveysummary2 = "";
        if ($surveyinfo['anonymized'] != "N") {$surveysummary2 .= $clang->gT("Answers to this survey are anonymized.")."<br />\n";}
        else {$surveysummary2 .= $clang->gT("This survey is NOT anonymous.")."<br />\n";}
        if ($surveyinfo['format'] == "S") {$surveysummary2 .= $clang->gT("It is presented question by question.")."<br />\n";}
        elseif ($surveyinfo['format'] == "G") {$surveysummary2 .= $clang->gT("It is presented group by group.")."<br />\n";}
        else {$surveysummary2 .= $clang->gT("It is presented on one single page.")."<br />\n";}
        if ($surveyinfo['allowjumps'] == "Y")
        {
          if ($surveyinfo['format'] == 'A') {$surveysummary2 .= $clang->gT("No question index will be shown with this format.")."<br />\n";}
          else {$surveysummary2 .= $clang->gT("A question index will be shown; participants will be able to jump between viewed questions.")."<br />\n";}
        }
        if ($surveyinfo['datestamp'] == "Y") {$surveysummary2 .= $clang->gT("Responses will be date stamped.")."<br />\n";}
        if ($surveyinfo['ipaddr'] == "Y") {$surveysummary2 .= $clang->gT("IP Addresses will be logged")."<br />\n";}
        if ($surveyinfo['refurl'] == "Y") {$surveysummary2 .= $clang->gT("Referrer URL will be saved.")."<br />\n";}
        if ($surveyinfo['usecookie'] == "Y") {$surveysummary2 .= $clang->gT("It uses cookies for access control.")."<br />\n";}
        if ($surveyinfo['allowregister'] == "Y") {$surveysummary2 .= $clang->gT("If tokens are used, the public may register for this survey")."<br />\n";}
        if ($surveyinfo['allowsave'] == "Y" && $surveyinfo['tokenanswerspersistence'] == 'N') {$surveysummary2 .= $clang->gT("Participants can save partially finished surveys")."<br />\n";}
        if ($surveyinfo['emailnotificationto'] != '') 
        {
            $surveysummary2 .= $clang->gT("Basic email notification is sent to:")." {$surveyinfo['emailnotificationto']}<br />\n";
        }
        if ($surveyinfo['emailresponseto'] != '') 
        {
            $surveysummary2 .= $clang->gT("Detailed email notification with response data is sent to:")." {$surveyinfo['emailresponseto']}<br />\n";
        }

        if(bHasSurveyPermission($surveyid,'surveycontent','update'))
        {
            $surveysummary2 .= $clang->gT("Regenerate question codes:")
            . " [<a href='#' "
            . "onclick=\"if (confirm('".$clang->gT("Are you sure you want regenerate the question codes?","js")."')) {".get2post(base_url()."?action=renumberquestions&amp;sid=$surveyid&amp;style=straight")."}\" "
            . ">".$clang->gT("Straight")."</a>] "
            . " [<a href='#' "
            . "onclick=\"if (confirm('".$clang->gT("Are you sure you want regenerate the question codes?","js")."')) {".get2post(base_url()."?action=renumberquestions&amp;sid=$surveyid&amp;style=bygroup")."}\" "
            . ">".$clang->gT("By Group")."</a>]";
            $surveysummary2 .= "</td></tr>\n";
        }

		//SURVEY URL PART
        /** front end functionality required
        if ( $modrewrite ) {
            $tmp_url = $GLOBALS['publicurl'] . '/' . $surveyinfo['sid'];
            $surveysummary .= "<td align='left'> <a href='$tmp_url/lang-".$surveyinfo['language']."' target='_blank'>$tmp_url/lang-".$surveyinfo['language']."</a>";
            foreach ($aAdditionalLanguages as $langname)
            {
                $surveysummary .= "&nbsp;<a href='$tmp_url/lang-$langname' target='_blank'><img title='".$clang->gT("Survey URL for language:")." ".getLanguageNameFromCode($langname,false)."' alt='".getLanguageNameFromCode($langname,false)." ".$clang->gT("Flag")."' src='../images/flags/$langname.png' /></a>";
            }
        } else {
            $tmp_url = $GLOBALS['publicurl'] . '/index.php?sid=' . $surveyinfo['sid'];
            $surveysummary .= "<td align='left'> <a href='$tmp_url&amp;lang=".$surveyinfo['language']."' target='_blank'>$tmp_url&amp;lang=".$surveyinfo['language']."</a>";
            foreach ($aAdditionalLanguages as $langname)
            {
                $surveysummary .= "&nbsp;<a href='$tmp_url&amp;lang=$langname' target='_blank'><img title='".$clang->gT("Survey URL for language:")." ".getLanguageNameFromCode($langname,false)."' alt='".getLanguageNameFromCode($langname,false)." ".$clang->gT("Flag")."' src='../images/flags/$langname.png' /></a>";
            }
        }
        */
        
        $dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
        if (trim($surveyinfo['startdate'])!= '')
        {
            $constructoritems = array($surveyinfo['startdate'] , "Y-m-d H:i:s");
            $this->load->library('Date_Time_Converter',$items);
            $datetimeobj = $this->date_time_converter; //new Date_Time_Converter($surveyinfo['startdate'] , "Y-m-d H:i:s");
            $data['startdate']=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
        }
        else
        {
            $data['startdate']="-";
        }

        if (trim($surveyinfo['expires'])!= '')
        {
            $constructoritems = array($surveyinfo['expires'] , "Y-m-d H:i:s");
            $this->load->library('Date_Time_Converter',$items);
            $datetimeobj = $this->date_time_converter; 
            //$datetimeobj = new Date_Time_Converter($surveyinfo['expires'] , "Y-m-d H:i:s");
            $data['expdate']=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');
        }
        else
        {
            $data['expdate']="-";
        }

        if (!$surveyinfo['language']) {$data['language']=getLanguageNameFromCode($currentadminlang,false);} else {$data['language']=getLanguageNameFromCode($surveyinfo['language'],false);}

        // get the rowspan of the Additionnal languages row
        // is at least 1 even if no additionnal language is present
        $additionnalLanguagesCount = count($aAdditionalLanguages);
        $first=true;
		$data['additionnalLanguages']="";
        if ($additionnalLanguagesCount == 0)
        {
                    $data['additionnalLanguages'] .= "<td align='left'>-</td>\n";
        }
        else
        {
            foreach ($aAdditionalLanguages as $langname)
            {
                if ($langname)
                {
                    if (!$first) {$data['additionnalLanguages'].= "<tr><td>&nbsp;</td>";}
                    $first=false;
                    $data['additionnalLanguages'] .= "<td align='left'>".getLanguageNameFromCode($langname,false)."</td></tr>\n";
                }
            }
        }
        if ($first) $data['additionnalLanguages'] .= "</tr>";

        if ($surveyinfo['surveyls_urldescription']==""){$surveyinfo['surveyls_urldescription']=htmlspecialchars($surveyinfo['surveyls_url']);}
                                          
        if ($surveyinfo['surveyls_url']!="") 
        {
            $data['endurl'] = " <a target='_blank' href=\"".htmlspecialchars($surveyinfo['surveyls_url'])."\" title=\"".htmlspecialchars($surveyinfo['surveyls_url'])."\">{$surveyinfo['surveyls_urldescription']}</a>";
        }
        else
        {
            $data['endurl'] ="-";
        }

		$data['sumcount3']=$sumcount3;
		$data['sumcount2']=$sumcount2;

        if ($activated == "N")
        {
            $data['activatedlang'] = $clang->gT("No");
        }
        else
        {
            $data['activatedlang'] = $clang->gT("Yes");
        }

		$data['activated']=$activated;
        if ($activated == "Y")
        {
            $data['surveydb']=$this->db->dbprefix."survey_".$surveyid;
        }
 		$data['warnings']="";
        if ($activated == "N" && $sumcount3 == 0)
        {
            $data['warnings']= $clang->gT("Survey cannot be activated yet.")."<br />\n";
            if ($sumcount2 == 0 && bHasSurveyPermission($surveyid,'surveycontent','create'))
            {
                $data['warnings'] .= "<span class='statusentryhighlight'>[".$clang->gT("You need to add question groups")."]</span><br />";
            }
            if ($sumcount3 == 0 && bHasSurveyPermission($surveyid,'surveycontent','create'))
            {
               $data['warnings'] .= "<span class='statusentryhighlight'>[".$clang->gT("You need to add questions")."]</span><br />";
            }
        }
        $data['hints']=$surveysummary2;

        //return (array('column'=>array($columns_used,$hard_limit) , 'size' => array($length, $size_limit) ));
        /*
        $tableusage = get_dbtableusage($surveyid);
        if ($tableusage != false){

            if ($tableusage['dbtype']=='mysql'){
                $column_usage = round($tableusage['column'][0]/$tableusage['column'][1] * 100,2);
                $size_usage =  round($tableusage['size'][0]/$tableusage['size'][1] * 100,2);


                $surveysummary .="<tr><td align='right' valign='top'><strong>{$clang->gT("Table Column Usage")}: </strong></td><td><div class='progressbar' style='width:20%; height:15px;' name='{$column_usage}'></div> </td></tr>";
                $surveysummary .="<tr><td align='right' valign='top'><strong>{$clang->gT("Table Size Usage")}: </strong></td><td><div class='progressbar' style='width:20%; height:15px;' name='{$size_usage}'></div></td></tr>";
            }
            elseif (($arrCols['dbtype'] == 'mssqlnative')||($arrCols['dbtype'] == 'postgres')||($arrCols['dbtype'] == 'odbtp')||($arrCols['dbtype'] == 'mssql_n')){
                $column_usage = round($tableusage['column'][0]/$tableusage['column'][1] * 100,2);
                $surveysummary .="<tr><td align='right' valign='top'><strong>{$clang->gT("Table Column Usage")}: </strong></td><td><strong>{$column_usage}%</strong><div class='progressbar' style='width:20%; height:15px;' name='{$column_usage}'></div> </td></tr>";
            }
            
        }
        */
		$this->load->view("admin/survey/surveysummary",$data);
    }
}
