<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends Admin_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function index()
	{
		
		global $loginsummary;
        $clang = $this->limesurvey_lang;
        self::_getAdminHeader();
		self::_showadminmenu();
		
        if ($this->session->userdata('just_logged_in'))
        {
            self::_showMessageBox($clang->gT("Logged in"), $this->session->userdata('loginsummary'));
            $this->session->unset_userdata('just_logged_in');
            $this->session->unset_userdata('loginsummary');
        }
		if (count(getsurveylist(true))==0) 
		{
			$data['clang']=$this->limesurvey_lang;
			$this->load->view("admin/Super/firststeps",$data);
		}
        
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
	}
    
}