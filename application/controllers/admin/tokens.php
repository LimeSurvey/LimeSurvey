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
 * $Id:  $
 * 
 */

/**
 * Tokens Controller
 *
 * This controller performs token actions
 *
 * @package		LimeSurvey
 * @subpackage	Backend
 */
class tokens extends SurveyCommonController {
    
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Show token index page, handle token database
	 */
	function index($surveyid)
	{
		$clang = $this->limesurvey_lang;
		if(!bHasSurveyPermission($surveyid,'tokens','read'))    
		{
			show_error("no permissions"); // TODO Replace
		}
	
		//if ($enableLdap)
		//{
		//    require_once(dirname(__FILE__).'/../config-ldap.php');
		//}
		/*if (!isset($order)) {$order=preg_replace('/[^_ a-z0-9-]/i', '', returnglobal('order'));}
		if (!isset($limit)) {$limit=(int)returnglobal('limit');}
		if ($limit==0) $limit=50;
		if (!isset($start)) {$start=(int)returnglobal('start');}
		if (!isset($searchstring)) {$searchstring=returnglobal('searchstring');}
		if (!isset($tokenid)) {$tokenid=returnglobal('tid');}
		if (!isset($tokenids)) {$tokenids=returnglobal('tids');}
		if (!isset($gtokenid)) {$gtokenid=returnglobal('gtid');}
		if (!isset($gtokenids)) {$gtokenids=returnglobal('gtids');}
		if (!isset($starttokenid)) {$starttokenid=sanitize_int(returnglobal('last_tid'));}*/
		
		//include_once("login_check.php");
		//include_once("database.php");
		
		$js_admin_includes[]='scripts/tokens.js';
		self::_js_admin_includes(base_url()."scripts/admin/tokens.js");
		
		$this->load->helper("surveytranslator");
		
		$dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
		$thissurvey=getSurveyInfo($surveyid);
		
		if ($thissurvey===false)
		{
			show_error($clang->gT("The survey you selected does not exist")); // TODO Replace
		}
		
        $surveyprivate = $thissurvey['anonymized'];
        		
		// CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
		$tokenexists=tableExists('tokens_'.$surveyid);
		if (!$tokenexists) //If no tokens table exists
		{
			self::_newtokentable($surveyid);
		}
		else
		{
			$data['clang']=$clang;
			$data['thissurvey']=$thissurvey;
			$data['imageurl'] = $this->config->item('imageurl');
			$data['surveyid']=$surveyid;
	
			$this->load->model("tokens_dynamic_model");
			$data['queries']=$this->tokens_dynamic_model->tokensSummary($surveyid);
			
			self::_getAdminHeader();
			$this->load->view("admin/token/tokenbar",$data);
			$this->load->view("admin/token/tokensummary",$data);
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));	
		}
	}

	/**
	 * Browse Tokens
	 */
	function browse($surveyid)
	{
		$clang=$this->limesurvey_lang;
		$this->load->model("tokens_dynamic_model");
		$tkcount=$this->tokens_dynamic_model->totalTokens($surveyid);
		
		if (!isset($limit)) {$limit=(int)returnglobal('limit');}
		if ($limit==0) $limit=50;
		if (!isset($start)) {$start=(int)returnglobal('start');}
		if (!isset($limit)) {$limit = 100;}
    	if (!isset($start)) {$start = 0;}	    if ($limit > $tkcount) {$limit=$tkcount;}
	    $next=$start+$limit;
	    $last=$start-$limit;
	    $end=$tkcount-$limit;
	    if ($end < 0) {$end=0;}
	    if ($last <0) {$last=0;}
	    if ($next >= $tkcount) {$next=$tkcount-$limit;}
	    if ($end < 0) {$end=0;}
	    $baselanguage = GetBaseLanguageFromSurveyID($surveyid);
		
		
    	//ALLOW SELECTION OF NUMBER OF RECORDS SHOWN		$thissurvey=getSurveyInfo($surveyid);

		$data['clang']=$clang;
		$data['thissurvey']=$thissurvey;
		$data['searchstring']="";
		$data['imageurl'] = $this->config->item('imageurl');
		$data['surveyid']=$surveyid;

		self::_getAdminHeader();
		$this->load->view("admin/token/tokenbar",$data);
		$this->load->view("admin/token/browse",$data);
		//self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
	
	}

	/**
	 * Add new token form
	 */
	function addnew($surveyid)
	{
		/*if (($subaction == "edit" &&  bHasSurveyPermission($surveyid, 'tokens','update')) || 
    	($subaction == "addnew" && bHasSurveyPermission($surveyid, 'tokens','create')))*/
    	
		if(!bHasSurveyPermission($surveyid, 'tokens','create'))    
		{
			show_error("no permissions"); // TODO Replace
		}
		
		if ($this->input->post("subaction"))
		{
			$clang=$this->limesurvey_lang;
			$this->load->model("tokens_dynamic_model");
			$_POST=$this->input->post();
	
		    //Fix up dates and match to database format
		    if (trim($_POST['validfrom'])=='') {
		        $_POST['validfrom']=null;
		    }
		    else
		    {
		        $datetimeobj = new Date_Time_Converter(trim($_POST['validfrom']), $dateformatdetails['phpdate'].' H:i');
		        $_POST['validfrom'] =$datetimeobj->convert('Y-m-d H:i:s');
		    }
		    if (trim($_POST['validuntil'])=='') {$_POST['validuntil']=null;}
		    else
		    {
		        $datetimeobj = new Date_Time_Converter(trim($_POST['validuntil']), $dateformatdetails['phpdate'].' H:i');
		        $_POST['validuntil'] =$datetimeobj->convert('Y-m-d H:i:s');
		    }
		
		    $santitizedtoken=sanitize_token($_POST['token']);

		    $data = array('firstname' => $_POST['firstname'],
			'lastname' => $_POST['lastname'],
			'email' => sanitize_email($_POST['email']),
			'emailstatus' => $_POST['emailstatus'],
			'token' => $santitizedtoken,
			'language' => sanitize_languagecode($_POST['language']),
		    'sent' => $_POST['sent'],
			'remindersent' => $_POST['remindersent'],
			'completed' => $_POST['completed'],
			'usesleft' => $_POST['usesleft'],
			'validfrom' => $_POST['validfrom'],
			'validuntil' => $_POST['validuntil']);
		    // add attributes
		    $attrfieldnames=GetAttributeFieldnames($surveyid);
		    foreach ($attrfieldnames as $attr_name)
		    {
		        $data[$attr_name]=$_POST[$attr_name];
		    }
		    //$tblInsert=db_table_name('tokens_'.$surveyid);
		    //$udresult = $connect->Execute("Select * from ".db_table_name("tokens_$surveyid")." where  token<>'' and token='{$santitizedtoken}'");
			$udresult = $this->tokens_dynamic_model->getAllRecords($surveyid,array("token !="=>"", "token"=>$santitizedtoken));
		    if ($udresult->num_rows()==0)//RecordCount()==0)
		    {
		        // AutoExecute
		        //$inresult = $connect->AutoExecute($tblInsert, $data, 'INSERT') or safe_die ("Add new record failed:<br />\n$inquery<br />\n".$connect->ErrorMsg());
				$inresult = $this->tokens_dynamic_model->insertTokens($surveyid,$data);
				$data['success']=true;
			}
		    else
		    {
	        	$data['success']=false;
			}
	    
			$data['clang']=$clang;
			$thissurvey=getSurveyInfo($surveyid);
			$data['thissurvey']=$thissurvey;
			$data['imageurl'] = $this->config->item('imageurl');
			$data['surveyid']=$surveyid;
	
					
			self::_getAdminHeader();
			$this->load->view("admin/token/tokenbar",$data);
			$this->load->view("admin/token/addtokenpost",$data);
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));	
		}
		else
		{
			self::_handletokenform($surveyid,"addnew");
		}
	}

	/**
	 * Add dummy tokens form
	 */
	function adddummys($surveyid)
	{
		$clang=$this->limesurvey_lang;
		if(!bHasSurveyPermission($surveyid, 'tokens','create'))    
		{
			show_error("no permissions"); // TODO Replace
		}

		$this->load->model("tokens_dynamic_model");
		$tkcount=$this->tokens_dynamic_model->totalTokens($surveyid);
		$this->load->helper("surveytranslator");
	    //get token length from survey settings
		$this->load->model("surveys_model");
		$query = $this->surveys_model->getSomeRecords(array("tokenlength"),array("sid"=>$surveyid));
		$row = $query->row_array();
		$tokenlength = $row['tokenlength'];
		
	    //if tokenlength is not set or there are other problems use the default value (15)
	    if(!isset($tokenlength) || $tokenlength == '')
	    {
	        $tokenlength = 15;
	    }
	    
		$data['clang']=$clang;
		$thissurvey=getSurveyInfo($surveyid);
		$data['thissurvey']=$thissurvey;
		$data['imageurl'] = $this->config->item('imageurl');
		$data['surveyid']=$surveyid;
		$data['tokenlength']=$tokenlength;
		$data['dateformatdetails']=getDateFormatData($this->session->userdata('dateformat'));
				
		self::_getAdminHeader();
		$this->load->view("admin/token/tokenbar",$data);
		$this->load->view("admin/token/dummytokenform",$data);
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
			
	}

	/**
	 * Handle managetokenattributes action
	 */
	function managetokenattributes($surveyid)
	{
		$clang=$this->limesurvey_lang;
		if(!bHasSurveyPermission($surveyid, 'tokens', 'update'))   
		{
			show_error("no permissions"); // TODO Replace
		}

		$this->load->model("tokens_dynamic_model");
		$tkcount=$this->tokens_dynamic_model->totalTokens($surveyid);
		$this->load->helper("surveytranslator");
	
		$this->load->model("surveys_model");
		$query = $this->tokens_dynamic_model->getAllRecords($surveyid,FALSE,1);
		$examplerow = $query->row_array();

		$tokenfields=GetTokenFieldsAndNames($surveyid,true);
    	$nrofattributes=0;
		
		$data['clang']=$clang;
		$thissurvey=getSurveyInfo($surveyid);
		$data['thissurvey']=$thissurvey;
		$data['imageurl'] = $this->config->item('imageurl');
		$data['surveyid']=$surveyid;
		$data['tokenfields']=$tokenfields;
		$data['nrofattributes']=$nrofattributes;
		$data['examplerow']=$examplerow;
				
		self::_getAdminHeader();
		$this->load->view("admin/token/tokenbar",$data);
		$this->load->view("admin/token/managetokenattributes",$data);
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));	
	}

	/**
	 * Handle email action
	 */
	function email($surveyid,$tokenids=null)
	{
		$clang=$this->limesurvey_lang;
		if(!bHasSurveyPermission($surveyid, 'tokens', 'update'))   
		{
			show_error("no permissions"); // TODO Replace
		}
		
		if(isset($tokenids)) {
		    $tokenidsarray=explode("|", substr($tokenids, 1)); //Make the tokenids string into an array, and exclude the first character
		    unset($tokenids);
		    foreach($tokenidsarray as $tokenitem) {
		        if($tokenitem != "") $tokenids[]=sanitize_int($tokenitem);
		    }
		}

		$this->load->model("tokens_dynamic_model");
		$tkcount=$this->tokens_dynamic_model->totalTokens($surveyid);
		$this->load->helper("surveytranslator");
	
		$this->load->model("surveys_model");
		$query = $this->tokens_dynamic_model->getAllRecords($surveyid,FALSE,1);
		$examplerow = $query->row_array();

		$tokenfields=GetTokenFieldsAndNames($surveyid,true);
    	$nrofattributes=0;
		
		$data['clang']=$clang;
		$thissurvey=getSurveyInfo($surveyid);
		$data['thissurvey']=$thissurvey;
		$data['imageurl'] = $this->config->item('imageurl');
		$data['surveyid']=$surveyid;
		$data['tokenfields']=$tokenfields;
		$data['nrofattributes']=$nrofattributes;
		$data['examplerow']=$examplerow;

		$this->load->helper("admin/htmleditor_helper");
		
		if (getEmailFormat($surveyid) == 'html')
	    {
	        $ishtml=true;
	    }
	    else
	    {
	        $ishtml=false;
	    }
		$data['ishtml']=$ishtml;
		
	    if (!$this->input->post('ok'))
	    {
			self::_getAdminHeader();
			$this->load->view("admin/token/tokenbar",$data);
			$this->load->view("admin/token/email",$data);
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));	       
	    }
    	else
    	{
	        /*$tokenoutput .= "<div class='messagebox ui-corner-all'>\n"
	        ."\t<div class='header ui-widget-header'>\n";
	        $tokenoutput .= $clang->gT("Sending invitations...");
	        $tokenoutput .= "\n\t</div>\n";
	        if (isset($tokenid)) {$tokenoutput .= " (".$clang->gT("Sending to Token ID").":&nbsp;{$tokenid})";}
	        if (isset($tokenids)) {$tokenoutput .= " (".$clang->gT("Sending to Token IDs").":&nbsp;".implode(", ", $tokenids).")";}
	        $tokenoutput .= "<br />\n";
	
	        if (isset($_POST['bypassbademails']) && $_POST['bypassbademails'] == 'Y')
	        {
	            $SQLemailstatuscondition = " AND emailstatus = 'OK'";
	        }
	        else
	        {
	            $SQLemailstatuscondition = " AND emailstatus <> 'OptOut'";
	        }
	
	        $ctquery = "SELECT * FROM ".db_table_name("tokens_{$surveyid}")." WHERE ((completed ='N') or (completed='')) AND ((sent ='N') or (sent='')) AND token !='' AND email != '' $SQLemailstatuscondition";
	
	        if (isset($tokenid)) {$ctquery .= " AND tid='{$tokenid}'";}
	        if (isset($tokenids)) {$ctquery .= " AND tid IN ('".implode("', '", $tokenids)."')";}
	        $tokenoutput .= "<!-- ctquery: $ctquery -->\n";
	        $ctresult = $connect->Execute($ctquery) or safe_die("Database error!<br />\n" . $connect->ErrorMsg());
	        $ctcount = $ctresult->RecordCount();
	        $ctfieldcount = $ctresult->FieldCount();
	
	        $emquery = "SELECT * FROM ".db_table_name("tokens_{$surveyid}")." WHERE ((completed ='N') or (completed='')) AND ((sent ='N') or (sent='')) AND token !='' AND email != '' $SQLemailstatuscondition";
	
	        if (isset($tokenid)) {$emquery .= " and tid='{$tokenid}'";}
	        if (isset($tokenids)) {$emquery .= " AND tid IN ('".implode("', '", $tokenids)."')";}
	        $tokenoutput .= "\n\n<!-- emquery: $emquery -->\n\n";
	        $emresult = db_select_limit_assoc($emquery,$maxemails) or safe_die ("Couldn't do query.<br />\n$emquery<br />\n".$connect->ErrorMsg());
	        $emcount = $emresult->RecordCount();
	
	        $surveylangs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        $baselanguage = GetBaseLanguageFromSurveyID($surveyid);
	        array_unshift($surveylangs,$baselanguage);
	
	        foreach ($surveylangs as $language)
	        {
	            $_POST['message_'.$language]=auto_unescape($_POST['message_'.$language]);
	            $_POST['subject_'.$language]=auto_unescape($_POST['subject_'.$language]);
	            if ($ishtml) $_POST['message_'.$language] = html_entity_decode($_POST['message_'.$language], ENT_QUOTES, $emailcharset);
	        }
	
	
	        $attributes=GetTokenFieldsAndNames($surveyid);
	        if ($emcount > 0)
	        {
	        	$tokenoutput .= "<ul>\n";
	            while ($emrow = $emresult->FetchRow())
	            {
	                unset($fieldsarray);
	                $to = $emrow['email'];
	                $fieldsarray["{EMAIL}"]=$emrow['email'];
	                $fieldsarray["{FIRSTNAME}"]=$emrow['firstname'];
	                $fieldsarray["{LASTNAME}"]=$emrow['lastname'];
	                $fieldsarray["{TOKEN}"]=$emrow['token'];
	                $fieldsarray["{LANGUAGE}"]=$emrow['language'];
	
	                foreach ($attributes as $attributefield=>$attributedescription)
	                {
	                    $fieldsarray['{'.strtoupper($attributefield).'}']=$emrow[$attributefield];
	                }
	
	                $emrow['language']=trim($emrow['language']);
	                if ($emrow['language']=='') {$emrow['language']=$baselanguage;} //if language is not given use default
	                $found = array_search($emrow['language'], $surveylangs);
	                if ($found==false) {$emrow['language']=$baselanguage;}
	
	                $from = $_POST['from_'.$emrow['language']];
	
	
	                if ($ishtml === false)
	                {
	                    $fieldsarray["{OPTOUTURL}"]="$publicurl/optout.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}";
	
	                    if ( $modrewrite )
	                    {
	                        $fieldsarray["{SURVEYURL}"]="$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}";
	                    }
	                    else
	                    {
	                        $fieldsarray["{SURVEYURL}"]="$publicurl/index.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}";
	                    }
	                }
	                else
	                {
	                    $fieldsarray["{OPTOUTURL}"]="<a href='$publicurl/optout.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}'>".htmlspecialchars("$publicurl/optout.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}")."</a>";
	                    if ( $modrewrite )
	                    {
	                        $fieldsarray["{SURVEYURL}"]="<a href='$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}'>".htmlspecialchars("$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}")."</a>";
	                        $fieldsarray["@@SURVEYURL@@"]="$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}";
	                    }
	                    else
	                    {
	                        $fieldsarray["{SURVEYURL}"]="<a href='$publicurl/index.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}'>".htmlspecialchars("$publicurl/index.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}")."</a>";
	                        $fieldsarray["@@SURVEYURL@@"]="$publicurl/index.php?lang=".trim($emrow['language'])."&amp;sid=$surveyid&amp;token={$emrow['token']}";
	                    }
	                }
			$customheaders = array( '1' => "X-surveyid: ".$surveyid,
						'2' => "X-tokenid: ".$fieldsarray["{TOKEN}"]);
	
	        $modsubject=Replacefields($_POST['subject_'.$emrow['language']], $fieldsarray);
	                $modmessage=Replacefields($_POST['message_'.$emrow['language']], $fieldsarray);
			
	                if (trim($emrow['validfrom'])!='' && convertDateTimeFormat($emrow['validfrom'],'Y-m-d H:i:s','U')*1>date('U')*1)
	                {
	                    $tokenoutput .= $emrow['tid'] ." ".ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) delayed: Token is not yet valid.")."<br />", $fieldsarray);
	                }
	                elseif (trim($emrow['validuntil'])!='' && convertDateTimeFormat($emrow['validuntil'],'Y-m-d H:i:s','U')*1<date('U')*1)
	                {
	                    $tokenoutput .= $emrow['tid'] ." ".ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) skipped: Token is not valid anymore.")."<br />", $fieldsarray);
	                }
	                elseif (SendEmailMessage($modmessage, $modsubject, $to , $from, $sitename, $ishtml, getBounceEmail($surveyid),null,$customheaders))
	                {
	                    // Put date into sent
	                    $today = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust);
	                    $udequery = "UPDATE ".db_table_name("tokens_{$surveyid}")."\n"
	                    ."SET sent='$today' WHERE tid={$emrow['tid']}";
	                    //
	                    $uderesult = $connect->Execute($udequery) or safe_die ("Could not update tokens<br />$udequery<br />".$connect->ErrorMsg());
	                    $tokenoutput .= $clang->gT("Invitation sent to:")." {$emrow['firstname']} {$emrow['lastname']} ($to)<br />\n";
	                    if ($emailsmtpdebug==2)
	                    {
	                        $tokenoutput .=$maildebug;
	                    }
	                }
	                else
	                {
	                    $tokenoutput .= '<li>'.ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) failed. Error Message:")." ".$maildebug."<br />", $fieldsarray).'</li>';
	                    if ($debug>0)
	                    {
	                        $tokenoutput .= "<pre>Subject : $modsubject<br /><br />".htmlspecialchars($maildebugbody)."</pre>";
	                    }
	                }
	            }
	            if ($ctcount > $emcount)
	            {
	                $i = 0;
	                if (isset($tokenids))
	                {
	                while($i < $maxemails)
	                { array_shift($tokenids); $i++; }
	                $tids = '|'.implode('|',$tokenids);
	                }
	                $lefttosend = $ctcount-$maxemails;
	                $tokenoutput .= "</ul>\n"
	                ."<div class='warningheader'>".$clang->gT("Warning")."</div><br />\n"
	                ."<form method='post' action='$scriptname?action=tokens&amp;sid=$surveyid'>"
	                .$clang->gT("There are more emails pending than can be sent in one batch. Continue sending emails by clicking below.")."<br /><br />\n";
	                $tokenoutput .= str_replace("{EMAILCOUNT}", "$lefttosend", $clang->gT("There are {EMAILCOUNT} emails still to be sent."));
	                $tokenoutput .= "<br /><br />\n";
	                $tokenoutput .= "<input type='submit' value='".$clang->gT("Continue")."' />\n"
	                ."<input type='hidden' name='ok' value=\"absolutely\" />\n"
	                ."<input type='hidden' name='subaction' value=\"email\" />\n"
	                ."<input type='hidden' name='action' value=\"tokens\" />\n"
	                ."<input type='hidden' name='bypassbademails' value=\"".$_POST['bypassbademails']."\" />\n"
	                ."<input type='hidden' name='sid' value=\"{$surveyid}\" />\n";
	                if (isset($tokenids)) 
	                {
	                    $tokenoutput .= "<input type='hidden' name='tids' value=\"{$tids}\" />\n";        
	                }
	                foreach ($surveylangs as $language)
	                {
	                    $message = html_escape($_POST['message_'.$language]);
	                    $subject = html_escape($_POST['subject_'.$language]);
	                    $tokenoutput .="<input type='hidden' name='from_$language' value=\"".$_POST['from_'.$language]."\" />\n"
	                    ."<input type='hidden' name='subject_$language' value=\"".$_POST['subject_'.$language]."\" />\n"
	                    ."<input type='hidden' name='message_$language' value=\"$message\" />\n";
	                }
	                $tokenoutput .="</form>\n";
	            }
	        }
	        else
	        {
	            $tokenoutput .= "<div class='warningheader'>".$clang->gT("Warning")."</div>\n".$clang->gT("There were no eligible emails to send. This will be because none satisfied the criteria of:")
	            ."<br/>&nbsp;<ul><li>".$clang->gT("having a valid email address")."</li>"
	            ."<li>".$clang->gT("not having been sent an invitation already")."</li>"
	            ."<li>".$clang->gT("having already completed the survey")."</li>"
	            ."<li>".$clang->gT("having a token")."</li></ul>";
	        }*/
	    }
	}

	function remind($surveyid) 
	{
		$clang=$this->limesurvey_lang;
		if(!bHasSurveyPermission($surveyid, 'tokens', 'update'))   
		{
			show_error("no permissions"); // TODO Replace
		}

		$this->load->model("tokens_dynamic_model");
		$tkcount=$this->tokens_dynamic_model->totalTokens($surveyid);
		$this->load->helper("surveytranslator");
	
		$this->load->model("surveys_model");
		$query = $this->tokens_dynamic_model->getAllRecords($surveyid,FALSE,1);
		$examplerow = $query->row_array();

		$tokenfields=GetTokenFieldsAndNames($surveyid,true);
    	$nrofattributes=0;
		
	    
		$data['clang']=$clang;
		$thissurvey=getSurveyInfo($surveyid);
		$data['thissurvey']=$thissurvey;
		$data['imageurl'] = $this->config->item('imageurl');
		$data['surveyid']=$surveyid;
		$data['tokenfields']=$tokenfields;
		$data['nrofattributes']=$nrofattributes;
		$data['examplerow']=$examplerow;
				
		$this->load->helper("admin/htmleditor_helper");
		
		if (getEmailFormat($surveyid) == 'html')
	    {
	        $ishtml=true;
	    }
	    else
	    {
	        $ishtml=false;
	    }
		$data['ishtml']=$ishtml;
		
	    if (!$this->input->post('ok'))
	    {
		    self::_getAdminHeader();
			$this->load->view("admin/token/tokenbar",$data);
			$this->load->view("admin/token/remind",$data);
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

	    }
	    else
	    {
	
	        /*$tokenoutput .= "<div class='messagebox ui-corner-all'>\n"
	        . "<div class='header ui-widget-header'>";
	        $tokenoutput .= $clang->gT("Sending Reminders")
	        ."</div><br />\n";
	
	        $surveylangs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        $baselanguage = GetBaseLanguageFromSurveyID($surveyid);
	        array_unshift($surveylangs,$baselanguage);
	
	        foreach ($surveylangs as $language)
	        {
	            $_POST['message_'.$language]=auto_unescape($_POST['message_'.$language]);
	            $_POST['subject_'.$language]=auto_unescape($_POST['subject_'.$language]);
	
	        }
	
	        if (isset($starttokenid)) {$tokenoutput .= " (".$clang->gT("From Token ID").":&nbsp;{$starttokenid})";}
	        if (isset($tokenid)) {$tokenoutput .= " (".$clang->gT("Sending to Token ID").":&nbsp;{$tokenid})";}
	        if (isset($tokenids)) {$tokenoutput .= " (".$clang->gT("Sending to Token IDs").":&nbsp;".implode("|", $tokenids).")";}
	
	        if (isset($_POST['bypassbademails']) && $_POST['bypassbademails'] == 'Y')
	        {
	            $SQLemailstatuscondition = " AND emailstatus = 'OK'";
	        }
	        else
	        {
	            $SQLemailstatuscondition = "";
	        }
	
	        if (isset($_POST['maxremindercount']) &&
	        $_POST['maxremindercount'] != '' &&
	        intval($_POST['maxremindercount']) != 0)
	        {
	            $SQLremindercountcondition = " AND remindercount < ".intval($_POST['maxremindercount']);
	        }
	        else
	        {
	            $SQLremindercountcondition = "";
	        }
	
	        if (isset($_POST['minreminderdelay']) &&
	        $_POST['minreminderdelay'] != '' &&
	        intval($_POST['minreminderdelay']) != 0)
	        {
	            // $_POST['minreminderdelay'] in days (86400 seconds per day)
	            $compareddate = date_shift(
	            date("Y-m-d H:i:s",time() - 86400 * intval($_POST['minreminderdelay'])),
						"Y-m-d H:i",
	            $timeadjust);
	            $SQLreminderdelaycondition = " AND ( "
	            . " (remindersent = 'N' AND sent < '".$compareddate."') "
	            . " OR "
	            . " (remindersent < '".$compareddate."'))";
	        }
	        else
	        {
	            $SQLreminderdelaycondition = "";
	        }
	
	        $ctquery = "SELECT * FROM ".db_table_name("tokens_{$surveyid}")." WHERE (completed ='N' or completed ='') AND sent<>'' AND sent<>'N' AND token <>'' AND email <> '' $SQLemailstatuscondition $SQLremindercountcondition $SQLreminderdelaycondition";
	
	        if (isset($starttokenid)) {$ctquery .= " AND tid > '{$starttokenid}'";}
	        if (isset($tokenid) && $tokenid) {$ctquery .= " AND tid = '{$tokenid}'";}
	        if (isset($tokenids)) {$ctquery .= " AND tid IN (".implode(", ", $tokenids).")";}
	        $tokenoutput .= "<!-- ctquery: $ctquery -->\n";
	        $ctresult = $connect->Execute($ctquery) or safe_die ("Database error!<br />\n" . $connect->ErrorMsg());
	        $ctcount = $ctresult->RecordCount();
	        $ctfieldcount = $ctresult->FieldCount();
	        $emquery = "SELECT * FROM ".db_table_name("tokens_{$surveyid}")." WHERE (completed = 'N' or completed = '') AND sent <> 'N' and sent <>'' AND token <>'' AND EMAIL <>'' $SQLemailstatuscondition $SQLremindercountcondition $SQLreminderdelaycondition";
	
	        if (isset($starttokenid)) {$emquery .= " AND tid > '{$starttokenid}'";}
	        if (isset($tokenid) && $tokenid) {$emquery .= " AND tid = '{$tokenid}'";}
	        if (isset($tokenids)) {$emquery .= " AND tid IN (".implode(", ", $tokenids).")";}
	        $emquery .= " ORDER BY tid ";
	        $emresult = db_select_limit_assoc($emquery, $maxemails) or safe_die ("Couldn't do query.<br />$emquery<br />".$connect->ErrorMsg());
	        $emcount = $emresult->RecordCount();
	
	
	        $attributes=GetTokenFieldsAndNames($surveyid);
	        if ($emcount > 0)
	        {
	            $tokenoutput .= "<table width='450' align='center' >\n"
	            ."\t<tr>\n"
	            ."<td><font size='1'>\n";
	            while ($emrow = $emresult->FetchRow())
	            {
	                unset($fieldsarray);
	                $to = $emrow['email'];
	                $fieldsarray["{EMAIL}"]=$emrow['email'];
	                $fieldsarray["{FIRSTNAME}"]=$emrow['firstname'];
	                $fieldsarray["{LASTNAME}"]=$emrow['lastname'];
	                $fieldsarray["{TOKEN}"]=$emrow['token'];
	                $fieldsarray["{LANGUAGE}"]=$emrow['language'];
	
	                foreach ($attributes as $attributefield=>$attributedescription)
	                {
	                    $fieldsarray['{'.strtoupper($attributefield).'}']=$emrow[$attributefield];
	                }
	
	                $emrow['language']=trim($emrow['language']);
	                if ($emrow['language']=='') {$emrow['language']=$baselanguage;} //if language is not give use default
	                $found = array_search($emrow['language'], $surveylangs);
	                if ($found==false) {$emrow['language']=$baselanguage;}
	
	                $from = $_POST['from_'.$emrow['language']];
	
	                if (getEmailFormat($surveyid) == 'html')
	                {
	                    $ishtml=true;
	                }
	                else
	                {
	                    $ishtml=false;
	                }
	
	                if ($ishtml == false)
	                {
	                    $fieldsarray["{OPTOUTURL}"]="$publicurl/optout.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}";
	                    if ( $modrewrite )
	                    {
	                        $fieldsarray["{SURVEYURL}"]="$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}";
	                    }
	                    else
	                    {
	                        $fieldsarray["{SURVEYURL}"]="$publicurl/index.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}";
	                    }
	                }
	                else
	                {
	                    $fieldsarray["{OPTOUTURL}"]="<a href='$publicurl/optout.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}'>".htmlspecialchars("$publicurl/optout.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}")."</a>";
	                    if ( $modrewrite )
	                    {
	                        $fieldsarray["{SURVEYURL}"]="<a href='$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}'>".htmlspecialchars("$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}")."</a>";
	                        $fieldsarray["@@SURVEYURL@@"]="$publicurl/$surveyid/lang-".trim($emrow['language'])."/tk-{$emrow['token']}";
	                    }
	                    else
	                    {
	                        $fieldsarray["{SURVEYURL}"]="<a href='$publicurl/index.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}'>".htmlspecialchars("$publicurl/index.php?lang=".trim($emrow['language'])."&sid=$surveyid&token={$emrow['token']}")."</a>";
	                        $fieldsarray["@@SURVEYURL@@"]="$publicurl/index.php?lang=".trim($emrow['language'])."&amp;sid=$surveyid&amp;token={$emrow['token']}";
	                        $_POST['message_'.$emrow['language']] = html_entity_decode($_POST['message_'.$emrow['language']], ENT_QUOTES, $emailcharset);
	                    }
	                }
	
	                $msgsubject=Replacefields($_POST['subject_'.$emrow['language']], $fieldsarray);
	                $sendmessage=Replacefields($_POST['message_'.$emrow['language']], $fieldsarray);
	$customheaders = array( '1' => "X-surveyid: ".$surveyid,
						'2' => "X-tokenid: ".$tokenid);
	
	                if (trim($emrow['validfrom'])!='' && convertDateTimeFormat($emrow['validfrom'],'Y-m-d H:i:s','U')*1>date('U')*1)
	                {
	                    $tokenoutput .= $emrow['tid'] ." ".ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) delayed: Token is not yet valid.")."<br />", $fieldsarray);
	                }
	                elseif (trim($emrow['validuntil'])!='' && convertDateTimeFormat($emrow['validuntil'],'Y-m-d H:i:s','U')*1<date('U')*1)
	                {
	                    $tokenoutput .= $emrow['tid'] ." ".ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) skipped: Token is not valid anymore.")."<br />", $fieldsarray);
	                }
	                elseif (SendEmailMessage($sendmessage, $msgsubject, $to, $from, $sitename,$ishtml,getBounceEmail($surveyid),null,$customheaders))
	                {
	
	                    // Put date into remindersent
	                    $today = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust);
	                    $udequery = "UPDATE ".db_table_name("tokens_{$surveyid}")."\n"
	                    ."SET remindersent='$today',remindercount = remindercount+1  WHERE tid={$emrow['tid']}";
	                    //
	                    $uderesult = $connect->Execute($udequery) or safe_die ("Could not update tokens<br />$udequery<br />".$connect->ErrorMsg());
	                    //orig: $tokenoutput .= "({$emrow['tid']})[".$clang->gT("Reminder sent to:")." {$emrow['firstname']} {$emrow['lastname']}]<br />\n";
	                    $tokenoutput .= "({$emrow['tid']}) [".$clang->gT("Reminder sent to:")." {$emrow['firstname']} {$emrow['lastname']} ($to)]<br />\n";
	                }
	                else
	                {
	                    $tokenoutput .= $emrow['tid'] ." ".ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) failed. Error Message:")." ".$maildebug."<br />", $fieldsarray);
	                    if ($debug>0)
	                    {
	                        $tokenoutput .= "<pre>Subject : $msgsubject<br /><br />".htmlspecialchars($maildebugbody)."<br /></pre>";
	                    }
	
	                }
	                $lasttid = $emrow['tid'];
	            }
	            if ($ctcount > $emcount)
	            {
	                $lefttosend = $ctcount-$maxemails;
	                $tokenoutput .= "</td>\n"
	                ."\t</tr>\n"
	                ."\t<tr><form method='post' action='$scriptname?action=tokens&amp;sid=$surveyid'>"
	                ."<td align='center'>\n"
	                ."<strong>".$clang->gT("Warning")."</strong><br /><br />\n"
	                .$clang->gT("There are more emails pending than can be sent in one batch. Continue sending emails by clicking below.")."<br /><br />\n"
	                .str_replace("{EMAILCOUNT}", $lefttosend, $clang->gT("There are {EMAILCOUNT} emails still to be sent."))
	                ."<br />\n"
	                ."<input type='submit' value='".$clang->gT("Continue")."' />\n"
	                ."</td>\n"
	                ."\t<input type='hidden' name='ok' value=\"absolutely\" />\n"
	                ."\t<input type='hidden' name='subaction' value=\"remind\" />\n"
	                ."\t<input type='hidden' name='action' value=\"tokens\" />\n"
	                ."\t<input type='hidden' name='bypassbademails' value=\"".$_POST['bypassbademails']."\" />\n"
	                ."\t<input type='hidden' name='sid' value=\"{$surveyid}\" />\n";
	                //Include values for constraints minreminderdelay and maxremindercount if they exist
	                if (isset($_POST['minreminderdelay']) &&
	                $_POST['minreminderdelay'] != '' &&
	                intval($_POST['minreminderdelay']) != 0)
	                {
	                    $tokenoutput .= "\t<input type='hidden' name='minreminderdelay' value=\"".$_POST['minreminderdelay']."\" />\n";
	                }
	                if (isset($_POST['maxremindercount']) &&
	                $_POST['maxremindercount'] != '' &&
	                intval($_POST['maxremindercount']) != 0)
	                {
	                    $tokenoutput .= "\t<input type='hidden' name='maxremindercount' value=\"".$_POST['maxremindercount']."\" />\n";
	                }
	                //
	                foreach ($surveylangs as $language)
	                {
	                    $message = html_escape($_POST['message_'.$language]);
	                    $tokenoutput .="<input type='hidden' name='from_$language' value=\"".$_POST['from_'.$language]."\" />\n"
	                    ."<input type='hidden' name='subject_$language' value=\"".$_POST['subject_'.$language]."\" />\n"
	                    ."<input type='hidden' name='message_$language' value=\"$message\" />\n";
	                }
	                $tokenoutput.="\t<input type='hidden' name='last_tid' value=\"$lasttid\" />\n"
	                ."\t</form>\n";
	            }
	            $tokenoutput .= "\t</tr>\n"
	            ."</table>\n";
	        }
	        else
	        {
	            $tokenoutput .= "<div class='warningheader'>".$clang->gT("Warning")."</div>\n"
	            .$clang->gT("There were no eligible emails to send. This will be because none satisfied the criteria of:")."\n"
	            ."<br/>&nbsp;<ul><li>".$clang->gT("having a valid email address")."</li>"
	            ."<li>".$clang->gT("not having been sent an invitation already")."</li>"
	            ."<li>".$clang->gT("but not having already completed the survey")."</li>"
	            ."</ul><br />\n";
	        }
	        //$tokenoutput .= "</div>\n";
		*/
		}
	}


	function _handletokenform($surveyid,$subaction,$tokenid="")
	{
		$clang=$this->limesurvey_lang;
		$this->load->model("tokens_dynamic_model");
		$tkcount=$this->tokens_dynamic_model->totalTokens($surveyid);
		$this->load->helper("surveytranslator");
		
		if ($subaction == "edit")
	    {
	        /*$edquery = "SELECT * FROM ".db_table_name("tokens_$surveyid")." WHERE tid={$tokenid}";
	        $edresult = db_execute_assoc($edquery);
	        $edfieldcount = $edresult->FieldCount();
	        while($edrow = $edresult->FetchRow())
	        {
	            //Create variables with the same names as the database column names and fill in the value
	            foreach ($edrow as $Key=>$Value) {$$Key = $Value;}
	        }*/
	    }
	    /*if ($subaction != "edit")
	    {
	        $edquery = "SELECT * FROM ".db_table_name("tokens_$surveyid");
	        $edresult = db_select_limit_assoc($edquery, 1);
	        $edfieldcount = $edresult->FieldCount();
	    }*/
	   
		$data['clang']=$clang;
		$thissurvey=getSurveyInfo($surveyid);
		$data['thissurvey']=$thissurvey;
		$data['imageurl'] = $this->config->item('imageurl');
		$data['surveyid']=$surveyid;
		$data['subaction']=$subaction;
		$data['dateformatdetails']=getDateFormatData($this->session->userdata('dateformat'));

		self::_getAdminHeader();
		$this->load->view("admin/token/tokenbar",$data);
		$this->load->view("admin/token/tokenform",$data);
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
	
	}

	/**
	 * Show dialogs and create a new tokens table
	 */
	function _newtokentable($surveyid)
	{
		$clang=$this->limesurvey_lang;
		if($this->input->post("createtable")=="Y" && bHasSurveyPermission($surveyid, 'surveyactivation','update'))
     	{
			$this->load->dbforge();
			$this->dbforge->add_field("tid int(11) NOT NULL AUTO_INCREMENT");
			$fields = array(
	                        'firstname' => array('type' => 'VARCHAR', 'constraint' => 40),
	                        'lastname' => array('type' => 'VARCHAR', 'constraint' => 40),
	                        'email' => array('type' => 'TEXT'),
	                        'emailstatus' => array('type' => 'TEXT'),
	                        'token' => array('type' => 'VARCHAR', 'constraint' => 36),
	                        'language' => array('type' => 'VARCHAR', 'constraint' => 25),
	                        'sent' => array('type' => 'VARCHAR', 'constraint' => 17, 'default' => 'N'),
	                        'remindersent' => array('type' => 'VARCHAR', 'constraint' => 17, 'default' => 'N'),
	                        'remindercount' => array('type' => 'INT', 'constraint' => 11, 'default' => 0),
	                        'completed' => array('type' => 'VARCHAR', 'constraint' => 17, 'default' => 'N'),
	                        'usesleft' => array('type' => 'INT', 'constraint' => 11, 'default' => 1),
	                        'validfrom' => array('type' => 'DATETIME'),
	                        'validuntil' => array('type' => 'DATETIME'),
	                        'mpid' => array('type' => 'INT', 'constraint' => 11)
	                );
			$this->dbforge->add_field($fields);
	
			//$tabname = "{$dbprefix}tokens_{$surveyid}"; # not using db_table_name as it quotes the table name (as does CreateTableSQL)
			/*$taboptarray = array('mysql' => 'ENGINE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci',
	                             'mysqli' => 'ENGINE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci');
			$dict = NewDataDictionary($connect);
			$sqlarray = $dict->CreateTableSQL($tabname, $createtokentable, $taboptarray);
			$execresult=$dict->ExecuteSQLArray($sqlarray, false);
			
			   $createtokentableindex = $dict->CreateIndexSQL("{$tabname}_idx", $tabname, array('token'));
			    $dict->ExecuteSQLArray($createtokentableindex, false) or safe_die ("Failed to create token table index<br />$createtokentableindex<br /><br />".$connect->ErrorMsg());
			    if ($connect->databaseType == 'mysql' || $connect->databaseType == 'mysqli')
			    {
			        $query = 'CREATE INDEX idx_'.$tabname.'_efl ON '.$tabname.' ( email(120), firstname, lastname )';
			        $result=$connect->Execute($query) or safe_die("Failed Rename!<br />".$query."<br />".$connect->ErrorMsg());
			    }*/
		
			$this->dbforge->add_key('tid', TRUE);
			$this->dbforge->add_key("token");
			//$this->dbforge->add_key(array('email (120)', 'firstname', 'lastname'));
			$this->dbforge->create_table("tokens_{$surveyid}");

			self::_getAdminHeader();
			self::_showMessageBox($clang->gT("Token control"),
					$clang->gT("A token table has been created for this survey.")." (\"".$this->db->dbprefix("tokens_$surveyid")."\")<br /><br />\n"
		    		."<input type='submit' value='"
		    		.$clang->gT("Continue")."' onclick=\"window.open('".site_url("admin/tokens/index/$surveyid")."', '_top')\" />\n");
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
			/*if ($execresult==0 || $execresult==1)
			{
			    $tokenoutput .= "\t</div><div class='messagebox ui-corner-all'>\n"
			    ."<font size='1'><strong><center>".$clang->gT("Token table could not be created.")."</center></strong></font>\n"
			    .$clang->gT("Error").": \n<font color='red'>" . $connect->ErrorMsg() . "</font>\n"
			    ."<pre>".htmlspecialchars(implode(" ",$sqlarray))."</pre>\n"
			    ."<br />"
			    ."<input type='submit' value='"
			    .$clang->gT("Main admin screen")."' onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" />\n"
			    ."</div>\n"
			    ."</div>\n";
	
			} else {
	
	
	
			    $tokenoutput .= "\t</div><p>\n"
			    .$clang->gT("A token table has been created for this survey.")." (\"".$dbprefix."tokens_$surveyid\")<br /><br />\n"
			    ."<input type='submit' value='"
			    .$clang->gT("Continue")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" />\n";
			}*/
			return;
	    }
	    elseif (returnglobal('restoretable') == "Y" && $this->input->post("oldtable") && bHasSurveyPermission($surveyid, 'surveyactivation','update'))
	    {
	        //$query = db_rename_table($this->input->post("oldtable") , $this->db->dbprefix("tokens_$surveyid"));
	        //$result=$connect->Execute($query) or safe_die("Failed Rename!<br />".$query."<br />".$connect->ErrorMsg());
			$this->dbforge->rename_table($this->input->post("oldtable") , $this->db->dbprefix("tokens_$surveyid"));

			self::_getAdminHeader();
			self::_showMessageBox($clang->gT("Import old tokens"),
					$clang->gT("A token table has been created for this survey and the old tokens were imported.")." (\"".$this->db->dbprefix("tokens_$surveyid")."\")<br /><br />\n"
		    		."<input type='submit' value='"
		    		.$clang->gT("Continue")."' onclick=\"window.open('".site_url("admin/tokens/index/$surveyid")."', '_top')\" />\n");
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
			
			/*$tokenoutput .= "\t</div><div class='messagebox ui-corner-all'>\n"
	        ."<div class='header ui-widget-header'>".$clang->gT("Import old tokens")."</div>"
	        ."<br />".$clang->gT("A token table has been created for this survey and the old tokens were imported.")." (\"".$dbprefix."tokens_$surveyid\")<br /><br />\n"
	        ."<input type='submit' value='"
	        .$clang->gT("Continue")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" />\n"
	        ."</div>\n";*/
	        return;
	    }
	    else
	    {
	        $this->load->model("tokens_dynamic_model");
			$result=$this->tokens_dynamic_model->getOldTableList($surveyid);
	        $tcount=$result->num_rows();
	        if ($tcount > 0)
	        {
				foreach ($result->result_array() as $rows)
				{
				   $oldlist[]=$rows[0];
				}
			$data['oldlist'] = $oldlist;
	        }

	       	$data['clang']=$clang;
			$thissurvey=getSurveyInfo($surveyid);
			$data['thissurvey']=$thissurvey;
			$data['imageurl'] = $this->config->item('imageurl');
			$data['surveyid']=$surveyid;
			$data['tcount']=$tcount;
			$this->load->config("database");
			$data['databasetype']=$this->config->item("dbdriver");
	
			self::_getAdminHeader();
			$this->load->view("admin/token/tokenwarning",$data);
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
			
	        return;
	    }
	}
}