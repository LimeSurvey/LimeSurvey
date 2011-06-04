<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends AdminController {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		
		self::_getAdminHeader();
		self::_showadminmenu();
		
		if(count(getsurveylist(true))==0) 
		{
			$data['clang']=$this->limesurvey_lang;
			$this->load->view("admin/firststeps",$data);
		}
		
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
	}
}