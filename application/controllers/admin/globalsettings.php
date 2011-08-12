<?php
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
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

/**
 * GlobalSettings Controller
 *
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class GlobalSettings extends AdminController {

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	function index()
	{
		if($this->session->userdata('USER_RIGHT_CONFIGURATOR') == 1)
        {
			if($this->input->post("action"))
			{
				self::_savesettings();	
			}
			self::_display();
		}
        else
        {
           //include("access_denied.php");
        }
	}
	
	function showphpinfo()
	{
		if($this->session->userdata('USER_RIGHT_CONFIGURATOR') == 1)
		{
			phpinfo();
		}
	}
	
	function _display()
	{

		$clang = $this->limesurvey_lang;
		
		self::_js_admin_includes(base_url()."scripts/admin/globalsettings.js");
			
		$data['title']="hi";
		$data['message']="message";
		$data['checksettings'] = self::_checksettings();
	    $data['thisupdatecheckperiod']=getGlobalSetting('updatecheckperiod');
		$data['updatelastcheck'] = $this->config->item("updatelastcheck");
		$data['updateavailable'] = $this->config->item("updateavailable");
		$data['updateinfo'] = $this->config->item("updateinfo");
						
		self::_getAdminHeader();
		self::_showadminmenu();
		$this->load->view('admin/globalsettings', $data);
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

	}
		
	function _savesettings()
	{
	    $clang = $this->limesurvey_lang;
		$action = $this->input->post("action");
	    if ($action == "globalsettingssave")
	    {
	        if($this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
	        {
	        	$maxemails = $this->input->post('maxemails');
	            if (sanitize_int($this->input->post('maxemails'))<1)
	            {
	                $maxemails=1;
	            }
	            setGlobalSetting('sitename',strip_tags($this->input->post('sitename')));
	            setGlobalSetting('updatecheckperiod',(int)($this->input->post('updatecheckperiod')));
	            setGlobalSetting('addTitleToLinks',sanitize_paranoid_string($this->input->post('addTitleToLinks')));
	            setGlobalSetting('defaultlang',sanitize_languagecode($this->input->post('defaultlang')));
	            setGlobalSetting('defaulthtmleditormode',sanitize_paranoid_string($this->input->post('defaulthtmleditormode')));
	            setGlobalSetting('defaulttemplate',sanitize_paranoid_string($this->input->post('defaulttemplate')));
	            setGlobalSetting('emailmethod',strip_tags($this->input->post('emailmethod')));
	            setGlobalSetting('emailsmtphost',strip_tags(returnglobal('emailsmtphost')));
	            if (returnglobal('emailsmtppassword')!='somepassword')
	            {
	                setGlobalSetting('emailsmtppassword',strip_tags(returnglobal('emailsmtppassword')));
	            }
	            setGlobalSetting('bounceaccounthost',strip_tags(returnglobal('bounceaccounthost')));
	      	    setGlobalSetting('bounceaccounttype',strip_tags(returnglobal('bounceaccounttype')));
	            setGlobalSetting('bounceencryption',strip_tags(returnglobal('bounceencryption')));
	            setGlobalSetting('bounceaccountuser',strip_tags(returnglobal('bounceaccountuser')));
	       
		    if (returnglobal('bounceaccountpass')!='enteredpassword')
	            {
	                setGlobalSetting('bounceaccountpass',strip_tags(returnglobal('bounceaccountpass')));
	            }
	            setGlobalSetting('emailsmtpssl',sanitize_paranoid_string(returnglobal('emailsmtpssl')));
	            setGlobalSetting('emailsmtpdebug',sanitize_int(returnglobal('emailsmtpdebug')));
	            setGlobalSetting('emailsmtpuser',strip_tags(returnglobal('emailsmtpuser')));
	            setGlobalSetting('filterxsshtml',strip_tags($this->input->post('filterxsshtml')));
	            setGlobalSetting('siteadminbounce',strip_tags($this->input->post('siteadminbounce')));
	            setGlobalSetting('siteadminemail',strip_tags($this->input->post('siteadminemail')));
	            setGlobalSetting('siteadminname',strip_tags($this->input->post('siteadminname')));
	            setGlobalSetting('shownoanswer',sanitize_int($this->input->post('shownoanswer')));
	            setGlobalSetting('showXquestions',($this->input->post('showXquestions')));
	            setGlobalSetting('showgroupinfo',($this->input->post('showgroupinfo')));
	            setGlobalSetting('showqnumcode',($this->input->post('showqnumcode')));
	            $repeatheadingstemp=(int)($this->input->post('repeatheadings'));
	            if ($repeatheadingstemp==0)  $repeatheadingstemp=25;
	            setGlobalSetting('repeatheadings',$repeatheadingstemp);
	
	            setGlobalSetting('maxemails',sanitize_int($maxemails));
	            $sessionlifetimetemp=(int)($this->input->post('sessionlifetime'));
	            if ($sessionlifetimetemp==0)  $sessionlifetimetemp=3600;
	            setGlobalSetting('sessionlifetime',$sessionlifetimetemp);
	            setGlobalSetting('ipInfoDbAPIKey',$this->input->post('ipInfoDbAPIKey'));
	            setGlobalSetting('googleMapsAPIKey',$this->input->post('googleMapsAPIKey'));
	            setGlobalSetting('force_ssl',$this->input->post('force_ssl'));
	            setGlobalSetting('surveyPreview_require_Auth',strip_tags($this->input->post('surveyPreview_require_Auth')));
	            $savetime=trim(strip_tags((float) $this->input->post('timeadjust')).' hours'); //makes sure it is a number, at least 0
	            if ((substr($savetime,0,1)!='-') && (substr($savetime,0,1)!='+')) { $savetime = '+'.$savetime;}
	            setGlobalSetting('timeadjust',$savetime);
	            setGlobalSetting('usepdfexport',strip_tags($this->input->post('usepdfexport')));
	            setGlobalSetting('usercontrolSameGroupPolicy',strip_tags($this->input->post('usercontrolSameGroupPolicy')));
	
	            $this->session->set_userdata('flashmessage',$clang->gT("Global settings were saved."));
	        }
	    }
	}

	function _checksettings()
	{
	    global $databasename;
		$clang = $this->limesurvey_lang;
	    //GET NUMBER OF SURVEYS
	    $this->load->model(("surveys_model"));
	    $this->load->model(("users_model"));
		
		$databasename = $this->db->database;
		//var_dump($databasename);	
			
	    //$query = "SELECT count(sid) FROM ".db_table_name('surveys');
		$query = $this->surveys_model->getSomeRecords(array("count(sid)"));
	    //$surveycount=$connect->GetOne($query);   //Checked
	    $surveycount=$query->row_array();
		$surveycount=$surveycount['count(sid)'];
		//var_dump($surveycount);
	    //$query = "SELECT count(sid) FROM ".db_table_name('surveys')." WHERE active='Y'";
		$query = $this->surveys_model->getSomeRecords(array("count(sid)"),array("active"=>"Y"));
	    //$activesurveycount=$connect->GetOne($query);  //Checked
	    $activesurveycount=$query->row_array();
		$activesurveycount=$activesurveycount['count(sid)'];
		//var_dump($activesurveycount);
	    //$query = "SELECT count(users_name) FROM ".db_table_name('users');
	    $query = $this->users_model->getSomeRecords(array("count(users_name)"));
	    //$usercount = $connect->GetOne($query);   //Checked
	    $usercount=$query->row_array();
		$usercount=$usercount['count(users_name)'];
		//var_dump($usercount);
			
	    if ($activesurveycount==false) $activesurveycount=0;
	    if ($surveycount==false) $surveycount=0;
	
	    $tablelist = $this->db->list_tables();
	    foreach ($tablelist as $table)
	    {
	        if (strpos($table,$this->db->dbprefix("old_tokens_"))!==false)
	        {
	            $oldtokenlist[]=$table;
	        }
	        elseif (strpos($table,$this->db->dbprefix("tokens_"))!==false)
	        {
	            $tokenlist[]=$table;
	        }
	        elseif (strpos($table,$this->db->dbprefix("old_survey_"))!==false)
	        {
	            $oldresultslist[]=$table;
	        }
	    }
		
	    if(isset($oldresultslist) && is_array($oldresultslist))
	    {$deactivatedsurveys=count($oldresultslist);} else {$deactivatedsurveys=0;}
	    if(isset($oldtokenlist) && is_array($oldtokenlist))
	    {$deactivatedtokens=count($oldtokenlist);} else {$deactivatedtokens=0;}
	    if(isset($tokenlist) && is_array($tokenlist))
	    {$activetokens=count($tokenlist);} else {$activetokens=0;}
	    $cssummary = "<div class='header ui-widget-header'>".$clang->gT("System overview")."</div>\n";
	    // Database name & default language
	    $cssummary .= "<br /><table class='statisticssummary'><tr>\n"
	    . "<th width='50%' align='right'>".$clang->gT("Database name").":</th><td>$databasename</td>\n"
	    . "</tr>\n";
	    // Other infos
	    $cssummary .=  "<tr>\n"
	    . "<th align='right'>".$clang->gT("Users").":</th><td>$usercount</td>\n"
	    . "</tr>\n"
	    . "<tr>\n"
	    . "<th align='right'>".$clang->gT("Surveys").":</th><td>$surveycount</td>\n"
	    . "</tr>\n"
	    . "<tr>\n"                                               
	    . "<th align='right'>".$clang->gT("Active surveys").":</th><td>$activesurveycount</td>\n"
	    . "</tr>\n"
	    . "<tr>\n"
	    . "<th align='right'>".$clang->gT("Deactivated result tables").":</th><td>$deactivatedsurveys</td>\n"
	    . "</tr>\n"
	    . "<tr>\n"
	    . "<th align='right'>".$clang->gT("Active token tables").":</th><td>$activetokens</td>\n"
	    . "</tr>\n"
	    . "<tr>\n"
	    . "<th align='right'>".$clang->gT("Deactivated token tables").":</th><td>$deactivatedtokens</td>\n"
	    . "</tr>\n"
	    . "</table>\n";
	
	    if ($this->session->userdata('USER_RIGHT_CONFIGURATOR') == 1)
	    {
	        $cssummary .= "<p><input type='button' onclick='window.open(\"".site_url("admin/globalsettings/showphpinfo")."\")' value='".$clang->gT("Show PHPInfo")."' />";
	    }
	    return $cssummary;
	}
}