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
 * $Id: survey.php 10433 2011-07-06 14:18:45Z dionet $
 *
 */

class survey extends LSCI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	public function _remap($method, $params = array())
	{
		array_unshift($params, $method);
	    return call_user_func_array(array($this, "action"), $params);
	}

	function action()
	{
		global $surveyid, $thistpl, $totalquestions;
    	global $thissurvey, $thisstep;
    	global $clienttoken, $tokensexist, $token;

		//Replace $_GET:
		$arg_list = func_get_args();
		if($arg_list[0]==__CLASS__) array_shift($arg_list);
		if(count($arg_list)%2 == 0) {
		    for ($i = 0; $i < count($arg_list); $i+=2) {
		        //Sanitize input from URL with returnglobal
				$param[$arg_list[$i]] = returnglobal($arg_list[$i], $arg_list[$i+1]);
		    }
		}

		@ini_set('session.gc_maxlifetime', $this->config->item('sess_expiration'));

		//Load helpers, libraries and config vars
		$this->load->helper("database");
		$this->load->helper("frontend");
		$this->load->helper("surveytranslator");

		$relativeurl = $this->config->item("relativeurl");
		$defaultlang = $this->config->item("defaultlang");
		$siteadminname = $this->config->item("siteadminname");
		$siteadminemail = $this->config->item("siteadminemail");
		$sitename = $this->config->item("sitename");
		$standardtemplaterootdir = $this->config->item("standardtemplaterootdir");
		$dbprefix = $this->db->dbprefix;

		$this->load->library("Dtexts");

		$_POST=$this->input->post();
		//$_SESSION=$this->session->userdata;

		$surveyid = isset($param['sid']) ? $param['sid'] : returnglobal('sid');
		$loadname= isset($param['loadname']) ? $param['loadname'] : returnglobal('loadname');
		$loadpass= isset($param['loadpass']) ? $param['loadpass'] : returnglobal('loadpass');
		$scid= isset($param['scid']) ? $param['scid'] : returnglobal('scid');
		$thisstep= isset($param['thisstep']) ? $param['thisstep'] : returnglobal('thisstep');
		$move = isset($param['move']) ? sanitize_paranoid_string($param['move']) : sanitize_paranoid_string(returnglobal('move'));
		$clienttoken= isset($param['token']) ? sanitize_token($param['token']) : sanitize_token(returnglobal('token'));

		if(!isset($param['action']))
			$param['action'] = isset($_POST['action']) ? $_POST['action'] : null;
		if(!isset($param['newtest']))
			$param['newtest'] = isset($_POST['newtest']) ? $_POST['newtest'] : null;
		if(!isset($param['gid']))
			$param['gid'] = isset($_POST['gid']) ? $_POST['gid'] : null;

		if (!isset($thisstep))
		{
		    $thisstep = "";
		}

		//This next line ensures that the $surveyid value is never anything but a number.
		$surveyid=sanitize_int($surveyid);

        // Compute the Session name
		// Session name is based:
		// * on this specific limesurvey installation (Value SessionName in DB)
		// * on the surveyid (from Get or Post param). If no surveyid is given we are on the public surveys portal

		$sSessionname=getGlobalSetting('SessionName');
		if ($sSessionname!='')
		{
			if ($surveyid)
			{
				$sSessionname = $sSessionname.'-runtime-'.$surveyid;
			}
			else
			{
				$sSessionname = $sSessionname.'-runtime-publicportal';
			}
		}
		else
		{
			$sSessionname = "LimeSurveyRuntime-$surveyid";
		}

        // Establish / Switch to survey session
        // Import data from current session (if available) to survey
        // session if the survey session has no data.

        $__SESSION = array(); // session data copy store
        $oSess = new LS_PHP_Session();
        if ($oSess->changeTo($sSessionname))
        {
            // Needed to call session_start() below.
            $__SESSION =& $_SESSION; // reference current session data.
            unset($_SESSION);
            $_SESSION = array();
        }
        else
        {
            session_name($sSessionname);
        }
        unset($oSess);

        session_set_cookie_params(0,$relativeurl);
        if (empty($_SESSION)) // the $_SESSION variable can be empty if register_globals is on
        {
            @session_start();
            if (empty($_SESSION)) // if this session is new, import old session
            {
                $_SESSION = $__SESSION;
                unset($__SESSION);
            }
            $this->session->bind_userdata();
        }

		// First check if survey is active
		// if not: copy some vars from the admin session
		// to a new user session

		if ($surveyid)
		{
		    $issurveyactive=false;
		    $aRow=db_execute_assoc("SELECT * FROM ".$this->db->dbprefix('surveys')." WHERE sid=$surveyid")->row_array();
		    if (isset($aRow['active']))
		    {
		        $surveyexists=true;
		        if($aRow['active']=='Y')
		        {
		            $issurveyactive=true;
		        }
		    }
		    else
		    {
		        $surveyexists=false;
		    }
		}

		if ($clienttoken != '' && isset($_SESSION['token']) &&
		$clienttoken != $_SESSION['token'])
		{
		    $baselang = GetBaseLanguageFromSurveyID($surveyid);
		    $this->load->library('Limesurvey_lang',array("langcode"=>$baselang));
			$clang = $this->limesurvey_lang;
		    // Let's first regenerate a session id
		    killSession();
		    // Let's redirect the client to the same URL after having reseted the session
		    //header("Location: $rooturl/index.php?" .$_SERVER['QUERY_STRING']);
		    sendcacheheaders();
		    doHeader();

            $redata = compact(array_keys(get_defined_vars()));
			echo templatereplace(file_get_contents("$standardtemplaterootdir/default/startpage.pstpl"),array(),$redata,'survey[159]');
		    echo "\t<div id='wrapper'>\n"
		    ."\t<p id='tokenmessage'>\n"
		    ."\t<span class='error'>".$clang->gT("Token mismatch")."</span><br /><br />\n"
		    ."\t".$clang->gT("The token you provided doesn't match the one in your session.")."<br /><br />\n"
		    ."\t".$clang->gT("Please wait to begin with a new session.")."<br /><br />\n"
		    ."\t</p>\n"
		    ."\t</div>\n";

			echo templatereplace(file_get_contents("$standardtemplaterootdir/default/endpage.pstpl"),array(),$redata,'survey[168]');
		    doFooter();
		    exit;
		}

		if (isset($_SESSION['finished']) && $_SESSION['finished'] === true)
		{
		    $baselang = GetBaseLanguageFromSurveyID($surveyid);
		    $this->load->library('Limesurvey_lang',array("langcode"=>$baselang));
			$clang = $this->limesurvey_lang;
		    // Let's first regenerate a session id
		    killSession();
		    // Let's redirect the client to the same URL after having reseted the session
		    //header("Location: " .$this->config->site_url()."/".$this->uri->uri_string());
		    sendcacheheaders();
		    doHeader();

            $redata = compact(array_keys(get_defined_vars()));
			echo templatereplace(file_get_contents("$standardtemplaterootdir/default/startpage.pstpl"),array(),$redata,'survey[186]');
		    echo "\t<div id='wrapper'>\n"
		    ."\t<p id='tokenmessage'>\n"
		    ."\t<span class='error'>".$clang->gT("Previous session is set to be finished.")."</span><br /><br />\n"
		    ."\t".$clang->gT("Your browser reports that it was used previously to answer this survey. We are resetting the session so that you can start from the beginning.")."<br /><br />\n"
		    ."\t".$clang->gT("Please wait to begin with a new session.")."<br /><br />\n"
		    ."\t</p>\n"
		    ."\t</div>\n";

			echo templatereplace(file_get_contents("$standardtemplaterootdir/default/endpage.pstpl"),array(),$redata,'survey[195]');
		    doFooter();
		    exit;
		}
		$previewgrp = false;
		if (isset($param['action']) && ($param['action'] == 'previewgroup')){
			$rightquery="SELECT uid FROM {$dbprefix}survey_permissions WHERE sid=".$this->db->escape($surveyid)." AND uid = ".$this->db->escape($this->session->userdata('loginID')).' group by uid';
			$rightresult = db_execute_assoc($rightquery);
			if ($rightresult->num_rows() > 0 || $this->session->userdata('USER_RIGHT_SUPERADMIN') == 1)
			{
				$previewgrp = true;
			}
			else
			{
				$baselang = GetBaseLanguageFromSurveyID($surveyid);
		    	$this->load->library('Limesurvey_lang',array("langcode"=>$baselang));
				show_error("\t<span class='error'>".$this->limesurvey_lang->gT("ERROR")."</span><br /><br />\n"
		        ."\t".$this->limesurvey_lang->gT("We are sorry but you don't have permissions to do this.")."<br /><br />\n");
			}
		}

		if (($surveyid &&
		$issurveyactive===false && $surveyexists &&
		isset ($surveyPreview_require_Auth) &&
		$surveyPreview_require_Auth == true) &&  $previewgrp == false)
		{
		    // admin session and permission have not already been imported
		    // for this particular survey
		    if ( !isset($_SESSION['USER_RIGHT_PREVIEW']) ||
		    $_SESSION['USER_RIGHT_PREVIEW'] != $surveyid)
		    {
		        // Store initial session name
		        $initial_session_name=session_name();

		        // One way (not implemented here) would be to start the
		        // user session from a duplicate of the admin session
		        // - destroy the new session
		        // - load admin session (with correct session name)
		        // - close admin session
		        // - change used session name to default
		        // - open new session (takes admin session id)
		        // - regenerate brand new session id for this session

		        // The solution implemented here is to copy some
		        // fields from the admin session to the new session
		        // - first destroy the new (empty) user session
		        // - then open admin session
		        // - record interresting values from the admin session
		        // - duplicate admin session under another name and Id
		        // - destroy the duplicated admin session
		        // - start a brand new user session
		        // - copy interresting values in this user session

		        @session_destroy();	// make it silent because for
		        // some strange reasons it fails sometimes
		        // which is not a problem
		        // but if it throws an error then future
		        // session functions won't work because
		        // headers are already sent.
		        if (isset($stg_SessionName) && $stg_SessionName)
		        {
		            @session_name($stg_SessionName);
		        }
		        else
		        {
		            session_name("LimeSurveyAdmin");
		        }
		        session_start(); // Loads Admin Session

		        $previewright=false;
		        $savesessionvars=Array();
		        if (isset($_SESSION['loginID']))
		        {
		            $rightquery="SELECT uid FROM {$dbprefix}survey_permissions WHERE sid=".$this->db->escape($surveyid)." AND uid = ".$this->db->escape($_SESSION['loginID'].' group by uid');
		            $rightresult = db_execute_assoc($rightquery);      //Checked

		            // Currently it is enough to be listed in the survey
		            // user operator list to get preview access
		            if ($rightresult->num_rows() > 0 || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
		            {
		                $previewright=true;
		                $savesessionvars["USER_RIGHT_PREVIEW"]=$surveyid;
		                $savesessionvars["loginID"]=$_SESSION['loginID'];
		                $savesessionvars["user"]=$_SESSION['user'];
		            }
		        }

		        // change session name and id
		        // then delete this new session
		        // ==> the original admin session remains valid
		        // ==> it is possible to start a new session
		        session_name($initial_session_name);
		        if ($sessionhandler=='db')
		        {
		            adodb_session_regenerate_id();
		        }
		        elseif (session_regenerate_id() === false)
		        {
		            safe_die("Error Regenerating Session Id");
		        }
		        @session_destroy();

		        // start new session
		        @session_start();
		        // regenerate id so that the header geenrated by previous
		        // regenerate_id is overwritten
		        // needed after clearall
		        if ($sessionhandler=='db')
		        {
		            adodb_session_regenerate_id();
		        }
		        elseif (session_regenerate_id() === false)
		        {
		            safe_die("Error Regenerating Session Id");
		        }

		        if ( $previewright === true)
		        {
		            foreach ($savesessionvars as $sesskey => $sessval)
		            {
		                $_SESSION[$sesskey]=$sessval;
		            }
		        }
		    }
		    else
		    { // already authorized
		        $previewright = true;
		    }

		    if ($previewright === false)
		    {
		        // print an error message
		        if (isset($_REQUEST['rootdir']))
		        {
		            safe_die('You cannot start this script directly');
		        }
		        //require_once(dirname(__FILE__).'/classes/core/language.php');
		        $baselang = GetBaseLanguageFromSurveyID($surveyid);
		        $this->load->library('Limesurvey_lang',array("langcode"=>$baselang));
				$clang = $this->limesurvey_lang;
		        //A nice exit
		        sendcacheheaders();
		        doHeader();

                $redata = compact(array_keys(get_defined_vars()));
				echo templatereplace(file_get_contents("$standardtemplaterootdir/default/startpage.pstpl"),array(),$redata,'survey[340]');
		        echo "\t<div id='wrapper'>\n"
		        ."\t<p id='tokenmessage'>\n"
		        ."\t<span class='error'>".$clang->gT("ERROR")."</span><br /><br />\n"
		        ."\t".$clang->gT("We are sorry but you don't have permissions to do this.")."<br /><br />\n"
		        ."\t".sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$siteadminname,encodeEmail($siteadminemail))."<br /><br />\n"
		        ."\t</p>\n"
		        ."\t</div>\n";

				echo templatereplace(file_get_contents("$standardtemplaterootdir/default/endpage.pstpl"),array(),$redata,'survey[349]');
		        doFooter();
		        exit;
		    }
		}
		if (isset($_SESSION['srid']))
		{
		    $saved_id = $_SESSION['srid'];
		}

		if (!isset($_SESSION['s_lang'])  && (isset($move)) )
		// geez ... a session time out! RUN!
		{
		    if (isset($param['rootdir']))
		    {
		        safe_die('You cannot start this script directly');
		    }
		    //require_once(dirname(__FILE__).'/classes/core/language.php');
		    $baselang = GetBaseLanguageFromSurveyID($surveyid);
		    $this->load->library('Limesurvey_lang',array("langcode"=>$baselang));
			$clang = $this->limesurvey_lang;
		    //A nice exit
		    sendcacheheaders();
		    doHeader();

            $redata = compact(array_keys(get_defined_vars()));
			echo templatereplace(file_get_contents("$standardtemplaterootdir/default/startpage.pstpl"),array(),$redata,'survey[375]');
		    echo "\t<div id='wrapper'>\n"
		    ."\t<p id='tokenmessage'>\n"
		    ."\t<span class='error'>".$clang->gT("ERROR")."</span><br /><br />\n"
		    ."\t".$clang->gT("We are sorry but your session has expired.")."<br /><br />\n"
		    ."\t".$clang->gT("Either you have been inactive for too long, you have cookies disabled for your browser, or there were problems with your connection.")."<br /><br />\n"
		    ."\t".sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$siteadminname,$siteadminemail)."<br /><br />\n"
		    ."\t</p>\n"
		    ."\t</div>\n";

			echo templatereplace(file_get_contents("$standardtemplaterootdir/default/endpage.pstpl"),array(),$redata,'survey[385]');
		    doFooter();
		    exit;
		};

		// Set the language of the survey, either from POST, GET parameter of session var
		if (isset($_POST['lang']) && $_POST['lang']!='')  // this one comes from the language question
		{
		    $templang = sanitize_languagecode($_POST['lang']);
		    $clang = SetSurveyLanguage( $surveyid, $templang);
		    UpdateSessionGroupList($surveyid, $templang);  // to refresh the language strings in the group list session variable

		    UpdateFieldArray();        // to refresh question titles and question text
		}
		else
		if (isset($param['lang']) && $surveyid)
		{
		    $templang = sanitize_languagecode($param['lang']);
		    $clang = SetSurveyLanguage( $surveyid, $templang);
		    UpdateSessionGroupList($surveyid, $templang);  // to refresh the language strings in the group list session variable
		    UpdateFieldArray();        // to refresh question titles and question text
		}

		if (isset($_SESSION['s_lang']))
		{
		    $clang = SetSurveyLanguage( $surveyid, $_SESSION['s_lang']);
		}
		elseif (isset($surveyid) && $surveyid)
		{
		    $baselang = GetBaseLanguageFromSurveyID($surveyid);
		    $clang = SetSurveyLanguage( $surveyid, $baselang);
		}
		else
		{
		    $baselang=$defaultlang;
		}

		if (isset($param['embedded_inc']))
		{
		    safe_die('You cannot start this script directly');
		}


		//CHECK FOR REQUIRED INFORMATION (sid)
		if (!$surveyid)
		{
		    if(isset($param['lang']))
		    {
		        $baselang = sanitize_languagecode($param['lang']);
		    }
		    elseif (!isset($baselang))
		    {
		        $baselang=$defaultlang;
		    }
			$this->load->library('Limesurvey_lang',array("langcode"=>$baselang));
			$clang = $this->limesurvey_lang;
		    if(!isset($defaulttemplate))
		    {
		        $defaulttemplate="default";
		    }
		    $languagechanger = makelanguagechanger();
		    //Find out if there are any publicly available surveys
		    $query = "SELECT a.sid, b.surveyls_title, a.publicstatistics
			          FROM ".$this->db->dbprefix('surveys')." AS a
					  INNER JOIN ".$this->db->dbprefix('surveys_languagesettings')." AS b
					  ON ( surveyls_survey_id = a.sid AND surveyls_language = a.language )
					  WHERE surveyls_survey_id=a.sid
					  AND surveyls_language=a.language
		              AND surveyls_language='$baselang'
					  AND a.active='Y'
					  AND a.listpublic='Y'
					  AND ((a.expires >= '".date("Y-m-d H:i")."') OR (a.expires is null))
		              AND ((a.startdate <= '".date("Y-m-d H:i")."') OR (a.startdate is null))
					  ORDER BY surveyls_title";
		    $result = db_execute_assoc($query,false,true) or die("Could not connect to database. If you try to install LimeSurvey please refer to the <a href='http://docs.limesurvey.org'>installation docs</a> and/or contact the system administrator of this webpage."); //Checked
		    $list=array();
		    if($result->num_rows() > 0)
		    {
		        foreach($result->result_array() as $rows)
		        {
		            $link = "<li><a href='".site_url($rows['sid']);
		            if (isset($param['lang']))
		            {
		                $link .= "/lang-".sanitize_languagecode($param['lang']);
		            }
		            $link .= "'  class='surveytitle'>".$rows['surveyls_title']."</a>\n";
		            if ($rows['publicstatistics'] == 'Y') $link .= "<a href='".site_url("statistics_user/".$rows['sid'])."'>(".$clang->gT('View statistics').")</a>";
		            $link .= "</li>\n";
		            $list[]=$link;
		        }
		    }
		    if(count($list) < 1)
		    {
		        $list[]="<li class='surveytitle'>".$clang->gT("No available surveys")."</li>";
		    }
		    $surveylist=array(
		            "nosid"=>$clang->gT("You have not provided a survey identification number"),
		            "contact"=>sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$siteadminname,encodeEmail($siteadminemail)),
		            "listheading"=>$clang->gT("The following surveys are available:"),
		            "list"=>implode("\n",$list),
		    );

		    $thissurvey['name']=$sitename;
		    $thissurvey['templatedir']=$defaulttemplate;

		    $data['thissurvey'] = $thissurvey;
	        //$data['privacy'] = $privacy;
	        $data['surveylist'] = $surveylist;
	        $data['surveyid'] = $surveyid;
	        $data['templatedir'] = sGetTemplatePath($defaulttemplate);
	        $data['templateurl'] = sGetTemplateURL($defaulttemplate)."/";
	        $data['templatename'] = $defaulttemplate;
			$data['sitename'] = $sitename;
			$data['languagechanger'] = $languagechanger;

		    //A nice exit
		    sendcacheheaders();
		    doHeader();
			echo templatereplace(file_get_contents(sGetTemplatePath($defaulttemplate)."/startpage.pstpl"),array(),$data,'survey[503]');

			echo templatereplace(file_get_contents(sGetTemplatePath($defaulttemplate)."/surveylist.pstpl"),array(),$data,'survey[505]');

			echo templatereplace(file_get_contents(sGetTemplatePath($defaulttemplate)."/endpage.pstpl"),array(),$data,'survey[507]');
		    doFooter();
		    exit;
		}

		// Get token
		if (!isset($token))
		{
		    $token=$clienttoken;
		}

		//GET BASIC INFORMATION ABOUT THIS SURVEY
		$totalBoilerplatequestions =0;
		$thissurvey=getSurveyInfo($surveyid, $_SESSION['s_lang']);

		if (isset($param['newtest']) && $param['newtest'] == "Y")
		{
            //Removes any existing timer cookies so timers will start again
		    setcookie ("limesurvey_timers", "", time() - 3600);
		}



		//SEE IF SURVEY USES TOKENS AND GROUP TOKENS
		$i = 0; //$tokensexist = 0;
		if ($surveyexists == 1 && tableExists('tokens_'.$thissurvey['sid']))
		{
		    $tokensexist = 1;

		}
		else
		{
		    $tokensexist = 0;
		    unset($_POST['token']);
		    unset($param['token']);
		    unset($token);
		    unset($clienttoken);
		}




		//SET THE TEMPLATE DIRECTORY
		if (!$thissurvey['templatedir'])
		{
		    $thistpl=sGetTemplatePath($defaulttemplate);
		}
		else
		{
		    $thistpl=sGetTemplatePath($thissurvey['templatedir']);
		}


		$timeadjust = $this->config->item("timeadjust");
		//MAKE SURE SURVEY HASN'T EXPIRED
		if ($thissurvey['expiry']!='' and date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)>$thissurvey['expiry'] && $thissurvey['active']!='N')
		{

		    sendcacheheaders();
		    doHeader();

            $redata = compact(array_keys(get_defined_vars()));
			echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata,'survey[569]');
		    echo "\t<div id='wrapper'>\n"
		    ."\t<p id='tokenmessage'>\n"
		    ."\t".$clang->gT("This survey is no longer available.")."<br /><br />\n"
		    ."\t".sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail']).".<br /><br />\n"
			."\t</p>\n"
		    ."\t</div>\n";

			echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata,'survey[577]');
		    doFooter();
		    exit;
		}

		//MAKE SURE SURVEY IS ALREADY VALID
		if ($thissurvey['startdate']!='' and  date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)<$thissurvey['startdate'] && $thissurvey['active']!='N')
		{
		    sendcacheheaders();
		    doHeader();

            $redata = compact(array_keys(get_defined_vars()));
		    echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata,'survey[589]');
		    echo "\t<div id='wrapper'>\n"
		    ."\t<p id='tokenmessage'>\n"
		    ."\t".$clang->gT("This survey is not yet started.")."<br /><br />\n"
		    ."\t".sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail']).".<br /><br />\n"
		    ."\t</p>\n"
		    ."\t</div>\n";

		    echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata,'survey[597]');
		    doFooter();
		    exit;
		}

		//CHECK FOR PREVIOUSLY COMPLETED COOKIE
		//If cookies are being used, and this survey has been completed, a cookie called "PHPSID[sid]STATUS" will exist (ie: SID6STATUS) and will have a value of "COMPLETE"
		$cookiename="PHPSID".returnglobal('sid')."STATUS";
		if (isset($_COOKIE[$cookiename]) && $_COOKIE[$cookiename] == "COMPLETE" && $thissurvey['usecookie'] == "Y" && $tokensexist != 1 && (!isset($param['newtest']) || $param['newtest'] != "Y"))
		{
		    sendcacheheaders();
		    doHeader();

            $redata = compact(array_keys(get_defined_vars()));
			echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata,'survey[611]');
		    echo "\t<div id='wrapper'>\n"
		    ."\t<p id='tokenmessage'>\n"
		    ."\t<span class='error'>".$clang->gT("Error")."</span><br /><br />\n"
		    ."\t".$clang->gT("You have already completed this survey.")."<br /><br />\n"
		    ."\t".sprintf($clang->gT("Please contact %s ( %s ) for further assistance."),$thissurvey['adminname'],$thissurvey['adminemail'])."\n"
		    ."\t</p>\n"
		    ."\t</div>\n";

			echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata,'survey[620]');
		    doFooter();
		    exit;
		}


		//CHECK IF SURVEY ID DETAILS HAVE CHANGED
		if (isset($_SESSION['oldsid']))
		{
		    $oldsid=$_SESSION['oldsid'];
		}

		if (!isset($oldsid))
		{
		    $_SESSION['oldsid'] = $surveyid;
		}

		if (isset($oldsid) && $oldsid && $oldsid != $surveyid)
		{
		    $savesessionvars=Array();
		    if (isset($_SESSION['USER_RIGHT_PREVIEW']))
		    {
		        $savesessionvars["USER_RIGHT_PREVIEW"]=$surveyid;
		        $savesessionvars["loginID"]=$_SESSION['loginID'];
		        $savesessionvars["user"]=$_SESSION['user'];
		    }
		    session_unset();
		    $_SESSION['oldsid']=$surveyid;
		    foreach ($savesessionvars as $sesskey => $sessval)
		    {
		        $_SESSION[$sesskey]=$sessval;
		    }
		}



		if (isset($_GET['loadall']) && $_GET['loadall'] == "reload")
		{
		    if (returnglobal('loadname') && returnglobal('loadpass'))
		    {
		        $_POST['loadall']="reload";
		    }
		}

		//LOAD SAVED SURVEY
		if (isset($_POST['loadall']) && $_POST['loadall'] == "reload")
		{
		    $errormsg="";
		    // if (loadname is not set) or if ((loadname is set) and (loadname is NULL))
		    if (!isset($loadname) || (isset($loadname) && ($loadname == null)))
		    {
		        $errormsg .= $clang->gT("You did not provide a name")."<br />\n";
		    }
		    // if (loadpass is not set) or if ((loadpass is set) and (loadpass is NULL))
		    if (!isset($loadpass) || (isset($loadpass) && ($loadpass == null)))
		    {
		        $errormsg .= $clang->gT("You did not provide a password")."<br />\n";
		    }

		    // if security question answer is incorrect
		    // Not called if scid is set in GET params (when using email save/reload reminder URL)
		    if (function_exists("ImageCreate") && captcha_enabled('saveandloadscreen',$thissurvey['usecaptcha']))
		    {
		        if ( (!isset($_POST['loadsecurity']) ||
		        !isset($_SESSION['secanswer']) ||
		        $_POST['loadsecurity'] != $_SESSION['secanswer']) &&
		        !isset($_GET['scid']))
		        {
		            $errormsg .= $clang->gT("The answer to the security question is incorrect.")."<br />\n";
		        }
		    }

		    // Load session before loading the values from the saved data
		    if (isset($_GET['loadall']))
		    {
		        buildsurveysession();
		    }

		    $_SESSION['holdname']=$loadname; //Session variable used to load answers every page.
		    $_SESSION['holdpass']=$loadpass; //Session variable used to load answers every page.

		    if ($errormsg == "") loadanswers();
		    $move = "movenext";

		    if ($errormsg)
		    {
		        $_POST['loadall'] = $clang->gT("Load Unfinished Survey");
		    }
		}
		//Allow loading of saved survey
		if (isset($_POST['loadall']) && $_POST['loadall'] == $clang->gT("Load Unfinished Survey"))
		{
			$vars = compact(array_keys(get_defined_vars()));
		    $this->load->library("load_answers");
			$this->load_answers->run($vars);
		}


		//Check if TOKEN is used for EVERY PAGE
		//This function fixes a bug where users able to submit two surveys/votes
		//by checking that the token has not been used at each page displayed.
		// bypass only this check at first page (Step=0) because
		// this check is done in buildsurveysession and error message
		// could be more interresting there (takes into accound captcha if used)
		if ($tokensexist == 1 && isset($token) && $token &&
			isset($_SESSION['step']) && $_SESSION['step']>0 && db_tables_exist($dbprefix.'tokens_'.$surveyid))
		{
			//check if tokens actually haven't been already used
			$areTokensUsed = usedTokens(trim(strip_tags(returnglobal('token'))),$surveyid);
			// check if token actually does exist
			// check also if it is allowed to change survey after completion
			if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
		    	$tkquery = "SELECT * FROM ".$this->db->dbprefix('tokens_'.$surveyid)." WHERE token=".$this->db->escape($token)." ";
			} else {
		    	$tkquery = "SELECT * FROM ".$this->db->dbprefix('tokens_'.$surveyid)." WHERE token=".$this->db->escape($token)." AND (completed = 'N' or completed='')";
		    }
		    $tkresult = db_execute_assoc($tkquery); //Checked
		    $tokendata = $tkresult->row_array();
		    if ($tkresult->num_rows()==0 || $areTokensUsed)
		    {
		        sendcacheheaders();
		        doHeader();
		        //TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

                $redata = compact(array_keys(get_defined_vars()));
		        echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata,'survey[745]');
		        echo templatereplace(file_get_contents("$thistpl/survey.pstpl"),array(),$redata,'survey[746]');
		        echo "\t<div id='wrapper'>\n"
		        ."\t<p id='tokenmessage'>\n"
		        ."\t".$clang->gT("This is a controlled survey. You need a valid token to participate.")."<br /><br />\n"
		        ."\t".$clang->gT("The token you have provided is either not valid, or has already been used.")."\n"
		        ."\t".sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname']
		        ." (<a href='mailto:{$thissurvey['adminemail']}'>"
		        ."{$thissurvey['adminemail']}</a>)")."\n"
		        ."\t</p>\n"
		        ."\t</div>\n";

		        echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata,'survey[757]');
			    killSession();
		        doFooter();
		        exit;
		}
		}
		if ($tokensexist == 1 && isset($token) && $token && db_tables_exist($dbprefix.'tokens_'.$surveyid)) //check if token is in a valid time frame
		{
			// check also if it is allowed to change survey after completion
			if ($thissurvey['alloweditaftercompletion'] == 'Y' ) {
		        $tkquery = "SELECT * FROM ".$this->db->dbprefix('tokens_'.$surveyid)." WHERE token=".$this->db->escape($token)." ";
		    } else {
		        $tkquery = "SELECT * FROM ".$this->db->dbprefix('tokens_'.$surveyid)." WHERE token=".$this->db->escape($token)." AND (completed = 'N' or completed='')";
		    }
		    $tkresult = db_execute_assoc($tkquery); //Checked
		    $tokendata = $tkresult->row_array();
		    if (isset($tokendata['validfrom']) && (trim($tokendata['validfrom'])!='' && $tokendata['validfrom']>date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)) ||
		    isset($tokendata['validuntil']) && (trim($tokendata['validuntil'])!='' && $tokendata['validuntil']<date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)))
		    {
		        sendcacheheaders();
		        doHeader();
		        //TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT

                $redata = compact(array_keys(get_defined_vars()));
		        echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata,'survey[781]');
		        echo templatereplace(file_get_contents("$thistpl/survey.pstpl"),array(),$redata,'survey[782]');
		        echo "\t<div id='wrapper'>\n"
		        ."\t<p id='tokenmessage'>\n"
		        ."\t".$clang->gT("We are sorry but you are not allowed to enter this survey.")."<br /><br />\n"
		        ."\t".$clang->gT("Your token seems to be valid but can be used only during a certain time period.")."<br />\n"
		        ."\t".sprintf($clang->gT("For further information please contact %s"), $thissurvey['adminname']
		        ." (<a href='mailto:{$thissurvey['adminemail']}'>"
		        ."{$thissurvey['adminemail']}</a>)")."\n"
		        ."\t</p>\n"
		        ."\t</div>\n";

		        echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata,'survey[793]');
		        doFooter();
			    killSession();
		        exit;
		    }
		}



		//Clear session and remove the incomplete response if requested.
		if (isset($move) && $move == "clearall")
		{
		    $s_lang = $_SESSION['s_lang'];
		    if (isset($_SESSION['srid']))
		    {
		        // find out if there are any fuqt questions - checked
		        $fieldmap = createFieldMap($surveyid);
		        foreach ($fieldmap as $field)
		        {
		            if ($field['type'] == "|" && !strpos($field['fieldname'], "_filecount"))
		            {
		                if (!isset($qid)) { $qid = array(); }
		                $qid[] = $field['fieldname'];
		            }
		        }

		        // if yes, extract the response json to those questions
		        if (isset($qid))
		        {
		            $query = "SELECT * FROM ".$this->db->dbprefix("survey_".$surveyid)." WHERE id=".$_SESSION['srid'];
		            $result = db_execute_assoc($query);
		            foreach($result->result_array() as $row)
		            {
		                foreach ($qid as $question)
		                {
		                    $json = $row[$question];
		                    if ($json == "" || $json == NULL)
		                        continue;

		                    // decode them
		                    $phparray = json_decode($json);

		                    foreach ($phparray as $metadata)
		                    {
		                        $target = $this->config->item("uploaddir")."/surveys/".$surveyid."/files/";
		                        // delete those files
		                        unlink($target.$metadata->filename);
		                    }
		                }
		            }
		        }
		        // done deleting uploaded files


		        // delete the response but only if not already completed
		        db_execute_assoc('DELETE FROM '.$this->db->dbprefix('survey_'.$surveyid).' WHERE id='.$_SESSION['srid']." AND submitdate IS NULL");

		        // also delete a record from saved_control when there is one
		        db_execute_assoc('DELETE FROM '.$this->db->dbprefix('saved_control'). ' WHERE srid='.$_SESSION['srid'].' AND sid='.$surveyid);
		    }
		    session_unset();
		    session_destroy();
		    setcookie(session_name(),"EXPIRED",time()-120);
		    sendcacheheaders();
		    if (isset($_GET['redirect']))
		    {
		        session_write_close();
		        header("Location: {$_GET['redirect']}");
		    }
		    doHeader();

            $redata = compact(array_keys(get_defined_vars()));
            echo templatereplace(file_get_contents("$thistpl/startpage.pstpl"),array(),$redata,'survey[864]');
		    echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n"
		    ."\t<script type='text/javascript'>\n"
		    ."\t<!--\n"
		    ."function checkconditions(value, name, type)\n"
		    ."\t{\n"
		    ."\t}\n"
		    ."\t//-->\n"
		    ."\t</script>\n\n";

		    //Present the clear all page using clearall.pstpl template
		    echo templatereplace(file_get_contents("$thistpl/clearall.pstpl"),array(),$redata,'survey[876]');

		    echo templatereplace(file_get_contents("$thistpl/endpage.pstpl"),array(),$redata,'survey[878]');
		    doFooter();
		    exit;
		}

		if (isset($param['newtest']) && $param['newtest'] == "Y")
		{
		    $savesessionvars=Array();
		    if (isset($_SESSION['USER_RIGHT_PREVIEW']))
		    {
		        $savesessionvars["USER_RIGHT_PREVIEW"]=$surveyid;
		        $savesessionvars["loginID"]=$_SESSION['loginID'];
		        $savesessionvars["user"]=$_SESSION['user'];
		    }
		    session_unset();
		    $_SESSION['oldsid']=$surveyid;
		    foreach ($savesessionvars as $sesskey => $sessval)
		    {
		        $_SESSION[$sesskey]=$sessval;
		    }
		    //DELETE COOKIE (allow to use multiple times)
		    setcookie($cookiename, "INCOMPLETE", time()-120);
		    //echo "Reset Cookie!";
		}

		//Check to see if a refering URL has been captured.
		GetReferringUrl();
		// Let's do this only if
		//  - a saved answer record hasn't been loaded through the saved feature
		//  - the survey is not anonymous
		//  - the survey is active
		//  - a token information has been provided
		//  - the survey is setup to allow token-response-persistence

		if ($thissurvey['tokenanswerspersistence'] == 'Y' && !isset($_SESSION['srid']) && $thissurvey['anonymized'] == "N" && $thissurvey['active'] == "Y" && isset($token) && $token !='')
		{

		    // load previous answers if any (dataentry with nosubmit)
		    $srquery="SELECT id FROM {$thissurvey['tablename']}"
		    . " WHERE {$thissurvey['tablename']}.token='".$this->db->escape($token)."' order by id desc";

            $result = db_select_limit_assoc($srquery,1);
            if ($result->num_rows()>0)
            {
                $row=reset($result->result_array());
                if($row['submitdate']=='' || ($row['submitdate']!='' && $thissurvey['alloweditaftercompletion'] == 'Y'))
                    $_SESSION['srid'] = $row['id'];
		    }
		    buildsurveysession();
		    loadanswers();
		}

		// SAVE POSTED ANSWERS TO DATABASE IF MOVE (NEXT,PREV,LAST, or SUBMIT) or RETURNING FROM SAVE FORM
		if (isset($move) || isset($_POST['saveprompt']))
		{
			$args = compact(array_keys(get_defined_vars()));
			//save.php
		    $this->load->library("Save");
			$this->save->run($args);

		    // RELOAD THE ANSWERS INCASE SOMEONE ELSE CHANGED THEM
		    if ($thissurvey['active'] == "Y" &&
		            ( $thissurvey['allowsave'] == "Y" || $thissurvey['tokenanswerspersistence'] == "Y") )
		    {
		        loadanswers();
		    }
		}

		if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'previewgroup')){
		        $thissurvey['format'] = 'G';
		        buildsurveysession();
		}

		sendcacheheaders();

		//Send local variables to the appropriate survey type
		$args = compact(array_keys(get_defined_vars()));

		//CALL APPROPRIATE SCRIPT
		switch ($thissurvey['format'])
		{
		    case "A": //All in one
		        //require_once("survey.php");
		        $this->load->library("Survey_format");
				$this->survey_format->run($args);
		        break;
		    case "S": //One at a time
		        //require_once("question.php");
		        $this->load->library("Question_format");
				$this->question_format->run($args);
		        break;
		    case "G": //Group at a time
		        $this->load->library("Group_format");
				$this->group_format->run($args);
		        break;
		    default:
		        //require_once("question.php");
		        $this->load->library("Question_format");
				$this->question_format->run($args);
		}

		if (isset($_POST['saveall']) || isset($flashmessage))
		{
		    echo "<script language='JavaScript'> $(document).ready( function() {alert('".$clang->gT("Your responses were successfully saved.","js")."');}) </script>";
		}

	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */