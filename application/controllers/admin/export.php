<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 * $Id: assessments.php 10433 2011-07-06 14:18:45Z dionet $
 * 
 */

/**
 * Export Controller
 *
 * This controller performs export actions
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class export extends SurveyCommonController {

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->helper("export");
		$this->load->helper("database");
	}
	
	function survey($surveyid)
	{
		if(bHasSurveyPermission($surveyid,'surveycontent','export')) {
			if($this->input->post("action"))
			{
				self::_surveyexport($this->input->post("action"), $surveyid);
				return;
			}
			$css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
	   		$this->config->set_item("css_admin_includes", $css_admin_includes);
			self::_getAdminHeader();
	    	self::_showadminmenu($surveyid);
	    	self::_surveybar($surveyid);
	    	$this->load->view("admin/Export/survey_view");
	        self::_loadEndScripts();
	    	self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
		}
	}
	
	function _surveyexport($action, $surveyid)
	{
		if($action == "exportstructurexml")
		{
			    $fn = "limesurvey_survey_$surveyid.lss";      
			    header("Content-Type: text/xml");
			    header("Content-Disposition: attachment; filename=$fn");
			    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
			    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			    header("Pragma: public");                          // HTTP/1.0
			    echo survey_getXMLData($surveyid);
			    exit;
		}
		elseif($action == "exportstructurequexml")
		{
			if (isset($surveyprintlang) && !empty($surveyprintlang))
				$quexmllang = $surveyprintlang;
			else
				$quexmllang=GetBaseLanguageFromSurveyID($surveyid);
			if (!(isset($noheader) && $noheader == true))
			{
				$fn = "survey_{$surveyid}_{$quexmllang}.xml";
				header("Content-Type: text/xml");
				header("Content-Disposition: attachment; filename=$fn");
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header("Pragma: public");                          // HTTP/1.0
			
				echo quexml_export($surveyid, $quexmllang);	
				exit;
			}
			elseif($action == "exportstructureLsrcCsv")
			{
				lsrccsv_export($surveyid);
			}
		}
	}
	
	function group($surveyid, $gid)
	{
		if($this->config->item("export4lsrc") === true && bHasSurveyPermission($surveyid,'survey','export')) {
			if($this->input->post("action"))
			{
				group_export($this->input->post("action"), $surveyid, $gid);
				return;
			}
			$css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
	   		$this->config->set_item("css_admin_includes", $css_admin_includes);
			self::_getAdminHeader();
	    	self::_showadminmenu($surveyid);
	    	self::_surveybar($surveyid,$gid);
	    	self::_questiongroupbar($surveyid,$gid,null,"exportstructureGroup");
	    	$this->load->view("admin/Export/group_view", array("surveyid" => $surveyid, "gid" => $gid));
	        self::_loadEndScripts();
	    	self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
		}
		else
		{
			group_export("exportstructurecsvGroup", $surveyid, $gid);
			return;
		}
	}
	
	function question($surveyid, $gid, $qid)
	{
		if($this->config->item("export4lsrc") === true && bHasSurveyPermission($surveyid,'survey','export')) {
			if($this->input->post("action"))
			{
				question_export($this->input->post("action"), $surveyid, $gid, $qid);
				return;
			}
			$css_admin_includes[] = $this->config->item('styleurl')."admin/default/superfish.css";
	   		$this->config->set_item("css_admin_includes", $css_admin_includes);
			self::_getAdminHeader();
	    	self::_showadminmenu($surveyid);
	    	self::_surveybar($surveyid,$gid);
	    	self::_questiongroupbar($surveyid,$gid,$qid,"exportstructureGroup");
	    	$this->load->view("admin/Export/question_view", array("surveyid" => $surveyid, "gid" => $gid, "qid" =>$qid));
	        self::_loadEndScripts();
	    	self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
		}
		else
		{
			question_export("exportstructurecsvQuestion", $surveyid, $gid, $qid);
			return;
		}
	}

}