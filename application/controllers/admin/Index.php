<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends html {

	function __construct()
	{
		parent::__construct();
	}

	function index($surveyid='')
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
		elseif (count(getsurveylist(true))==0) 
		{
			$data['clang']=$this->limesurvey_lang;
			$this->load->view("admin/Super/firststeps",$data);
		}
        
        if ($surveyid != '' && $this->session->userdata('USER_RIGHT_CREATE_SURVEY'))
        {
            echo self::_display('insertsurvey',$surveyid);
            
        }
        
        	
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
	}
    
}