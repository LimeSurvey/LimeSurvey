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
 * $Id$
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
class tokens extends Survey_Common_Controller {

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
		$surveyid = sanitize_int($surveyid);

		$clang = $this->limesurvey_lang;
		if(!bHasSurveyPermission($surveyid,'tokens','read'))
		{
			show_error("no permissions"); // TODO Replace
		}

		self::_js_admin_includes(base_url()."scripts/admin/tokens.js");

		$this->load->helper("surveytranslator");

		$dateformatdetails=getDateFormatData($this->session->userdata('dateformat'));
		$thissurvey=getSurveyInfo($surveyid);

		if ($thissurvey===false)
		{
			show_error($clang->gT("The survey you selected does not exist")); // TODO Replace
		}

        $surveyprivate = $thissurvey['anonymized'];
        $data['surveyprivate'] = $surveyprivate;
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
     * tokens::bounceprocessing()
     *
     * @return void
     */
    function bounceprocessing($surveyid)
    {
    	$surveyid = sanitize_int($surveyid);

        $clang = $this->limesurvey_lang;
        $this->load->helper('globalsettings');

        $thissurvey=getSurveyInfo($surveyid);
        if ($thissurvey['bounceprocessing']!='N' && !($thissurvey['bounceprocessing']=='G' && getGlobalSetting('bounceaccounttype')=='off') && bHasSurveyPermission($surveyid, 'tokens','update'))
    	{
    		$bouncetotal=0;
    		$checktotal=0;
    		if($thissurvey['bounceprocessing']=='G')
    		{
    			$accounttype=getGlobalSetting('bounceaccounttype');
    			$hostname=getGlobalSetting('bounceaccounthost');
    			$username=getGlobalSetting('bounceaccountuser');
    			$pass=getGlobalSetting('bounceaccountpass');
    			$hostencryption=getGlobalSetting('bounceencryption');

            }
    		else
    		{
    			$accounttype=$thissurvey['bounceaccounttype'];
    			$hostname=$thissurvey['bounceaccounthost'];
    			$username=$thissurvey['bounceaccountuser'];
    			$pass=$thissurvey['bounceaccountpass'];
    			$hostencryption=$thissurvey['bounceaccountencryption'];

    		}
            @list($hostname,$port) = split(':', $hostname);
                if(empty($port))
                {
                  if($accounttype=="IMAP")
                    {
                      switch($hostencryption)
                      {
                          case "Off":
                            $hostname = $hostname.":143";
                            break;
                          case "SSL":
                            $hostname = $hostname.":993";
                            break;
                          case "TLS":
                            $hostname = $hostname.":993";
                            break;
                      }
                    }
                  else
                    {
                       switch($hostencryption)
                      {
                          case "Off":
                            $hostname = $hostname.":110";
                            break;
                          case "SSL":
                            $hostname = $hostname.":995";
                            break;
                          case "TLS":
                            $hostname = $hostname.":995";
                            break;
                      }
                    }
                }
    		$flags="";
            switch($accounttype)
    		{
    			case "IMAP":
    			$flags.="/imap";
    			break;
    			case "POP":
    			$flags.="/pop3";
    			break;
    		}
    		switch($hostencryption) // novalidate-cert to have personal CA , maybe option.
    		{
    			case "SSL":
    			$flags.="/ssl/novalidate-cert";
    			break;
    			case "TLS":
    			$flags.="/tls/novalidate-cert";
    			break;
    		}
    		if($mbox=imap_open('{'.$hostname.$flags.'}INBOX',$username,$pass))
    		{
                imap_errors();
    			$count=imap_num_msg($mbox);
    			$lasthinfo=imap_headerinfo($mbox,$count);
    			$datelcu = strtotime($lasthinfo->date);
    			$datelastbounce= $datelcu;
    			$lastbounce = $thissurvey['bouncetime'];
    			while($datelcu > $lastbounce)
    			{
    				@$header = explode("\r\n", imap_body($mbox,$count,FT_PEEK)); // Don't put read
    				foreach ($header as $item)
    				{
    					if (preg_match('/^X-surveyid/',$item))
    					{
    						$surveyidBounce=explode(": ",$item);
    					}
    					if (preg_match('/^X-tokenid/',$item))
    					{
    						$tokenBounce=explode(": ",$item);
    						if($surveyid == $surveyidBounce[1])
    						{
    							$bouncequery = "UPDATE ".db_table_name("tokens_$surveyid")." SET emailstatus='bounced' WHERE token='$tokenBounce[1]';";
    							$data = array(
                                        'emailstatus'=> 'bounced'

                                );
                                $condn = array('token' => $tokenBounce[1]);
                                $this->load->model('tokens_dynamic_model');


                                $anish= $this->tokens_dynamic_model->updateRecords($surveyid,$data,$condn); //$connect->Execute($bouncequery);)

    							$readbounce=imap_body($mbox,$count); // Put read
    							if (isset($thissurvey['bounceremove']) && $thissurvey['bounceremove']) // TODO Y or just true, and a imap_delete
    							{
    								$deletebounce=imap_delete($mbox,$count); // Put delete
    							}
    							$bouncetotal++;
    						}
    					}
    				}
    				$count--;
    				@$lasthinfo=imap_headerinfo($mbox,$count);
    				@$datelc=$lasthinfo->date;
    				$datelcu = strtotime($datelc);
    				$checktotal++;
    			    @imap_close($mbox);
                }
    			$entertimestamp = "update ".db_table_name("surveys")." set bouncetime='$datelastbounce' where sid='$surveyid'";
                $data = array('bouncetime' => $datelastbounce);
                $condn = array('sid' => $surveyid);
                $this->load->model('surveys_model');

    			$executetimestamp = $this->surveys_model->updateSurvey($data,$condn); //'$connect->Execute($entertimestamp);)
    			if($bouncetotal>0)
    			{
    				echo sprintf($clang->gT("%s messages were scanned out of which %s were marked as bounce by the system."), $checktotal,$bouncetotal);
    			}
    			else
    			{
    				echo sprintf($clang->gT("%s messages were scanned, none were marked as bounce by the system."),$checktotal);
    			}
    		}
    		else
    		{
    			echo $clang->gT("Please check your settings");
    		}
    	}
    	else
    	{
    		echo $clang->gT("We are sorry but you don't have permissions to do this.");
    	}
    	exit(0); // if bounceprocessing : javascript : no more todo

    }

	/**
	 * Browse Tokens
	 */
	function browse($surveyid,$limit=50,$start=0,$order=false,$searchstring=false)
	{
		$surveyid = sanitize_int($surveyid);
		$limit = (int) $limit;
		$start = (int) $start;

		$clang=$this->limesurvey_lang;
		$this->load->model("tokens_dynamic_model");
		$tkcount=$this->tokens_dynamic_model->totalTokens($surveyid);

		self::_js_admin_includes(base_url()."scripts/admin/tokens.js");

		//if (!isset($limit)) {$limit=(int)returnglobal('limit');}
		//if ($limit==0) $limit=50;
		//if (!isset($start)) {$start=(int)returnglobal('start');}
		//if (!isset($limit)) {$limit = 100;}
    	//if (!isset($start)) {$start = 0;}	    if ($limit > $tkcount) {$limit=$tkcount;}
	    $next=$start+$limit;
	    $last=$start-$limit;
	    $end=$tkcount-$limit;
	    if ($end < 0) {$end=0;}
	    if ($last <0) {$last=0;}
	    if ($next >= $tkcount) {$next=$tkcount-$limit;}
	    if ($end < 0) {$end=0;}
	    $baselanguage = GetBaseLanguageFromSurveyID($surveyid);
		$data['next']=$next;
	    $data['last']=$last;
	    $data['end']=$end;
		if(!$order) $order=$this->input->post("order");
		$order=preg_replace('/[^_ a-z0-9-]/i', '',$order);
		if ($order=="") {$order = "tid";}
		if($this->input->post("limit")) $limit = $this->input->post("limit");
		if($this->input->post("start")) $start = $this->input->post("start");
		//if (!isset($order)) {$order=preg_replace('/[^_ a-z0-9-]/i', '', returnglobal('order'));}
		//if (!isset($limit)) {$limit=(int)returnglobal('limit');}

    	//ALLOW SELECTION OF NUMBER OF RECORDS SHOWN		$thissurvey=getSurveyInfo($surveyid);

		if(!$searchstring) $searchstring=$this->input->post("searchstring");
		/*$bquery = "SELECT * FROM ".db_table_name("tokens_$surveyid");
		if ($searchstring)
		{
	        $sSearch=db_quote($searchstring);
		    $bquery .= " WHERE firstname LIKE '%{$sSearch}%' "
		    . "OR lastname LIKE '%{$sSearch}%' "
		    . "OR email LIKE '%{$sSearch}%' "
		    . "OR emailstatus LIKE '%{$sSearch}%' "
		    . "OR token LIKE '%{$sSearch}%'";
		}
		if (!isset($order) || !$order) {$bquery .= " ORDER BY tid";}
		else {$bquery .= " ORDER BY $order"; }

		$bresult = db_select_limit_assoc($bquery, $limit, $start) or safe_die ($clang->gT("Error").": $bquery<br />".$connect->ErrorMsg());*/
		if($searchstring)
		{
			$idata = array("firstname"=>$searchstring,
							"lastname"=>$searchstring,
							"email"=>$searchstring,
							"emailstatus"=>$searchstring,
							"token"=>$searchstring);
		}
		else
		{
			$idata = false;
		}

		$data['bresult'] = $this->tokens_dynamic_model->getAllRecords($surveyid,false,$limit,$start,$order,$idata);
		$data['clang']=$clang;
		$data['thissurvey']=getSurveyInfo($surveyid);
		$data['searchstring']=$searchstring;
		$data['imageurl'] = $this->config->item('imageurl');
		$data['surveyid']=$surveyid;
		$data['bgc']="";
		$data['limit']=$limit;
		$data['start']=$start;
		$data['order']=$order;
		$data['surveyprivate'] = $data['thissurvey']['anonymized'];

		self::_getAdminHeader();
		$this->load->view("admin/token/tokenbar",$data);
		$this->load->view("admin/token/browse",$data);
		self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

	}

	/**
	 * Add new token form
	 */
	function addnew($surveyid)
	{
		$surveyid = sanitize_int($surveyid);

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
	 * Edit Tokens
	 */
	function edit($surveyid,$tokenid)
	{
		$surveyid = sanitize_int($surveyid);
		$tokenid = (int) $tokenid;
		if(!bHasSurveyPermission($surveyid, 'tokens','update'))
		{
			show_error("no permissions"); // TODO Replace
		}

		if ($this->input->post("subaction"))
		{
			$clang=$this->limesurvey_lang;
			$this->load->model("tokens_dynamic_model");
			$_POST=$this->input->post();

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
		    $data = array();
		    $data[] = $_POST['firstname'];
		    $data[] = $_POST['lastname'];
		    $data[] = sanitize_email($_POST['email']);
		    $data[] = $_POST['emailstatus'];
		    $santitizedtoken=sanitize_token($_POST['token']);
		    $data[] = $santitizedtoken;
		    $data[] = sanitize_languagecode($_POST['language']);
		    $data[] = $_POST['sent'];
		    $data[] = $_POST['completed'];
		    $data[] = $_POST['usesleft'];
		    //    $db->DBTimeStamp("$year-$month-$day $hr:$min:$secs");
		    $data[] = $_POST['validfrom'];
		    $data[] = $_POST['validuntil'];
		    $data[] = $_POST['remindersent'];
		    $data[] = intval($_POST['remindercount']);

		    //$udresult = $connect->Execute("Select * from ".db_table_name("tokens_$surveyid")." where tid<>{$tokenid} and token<>'' and token='{$santitizedtoken}'") or safe_die ("Update record {$tokenid} failed:<br />\n$udquery<br />\n".$connect->ErrorMsg());
			$udresult = $this->tokens_dynamic_model->getAllRecords($surveyid,array("tid !="=>$tokenid, "token !="=>"", "token"=>$santitizedtoken));
		    if ($udresult->num_rows()==0)
		    {
		        //$udresult = $connect->Execute("Select * from ".db_table_name("tokens_$surveyid")." where tid={$tokenid} and email='".sanitize_email($_POST['email'])."'") or safe_die ("Update record {$tokenid} failed:<br />\n$udquery<br />\n".$connect->ErrorMsg());


		        // Using adodb Execute with blinding method so auto-dbquote is done
		        $udquery = "UPDATE ".$this->db->dbprefix("tokens_$surveyid")." SET firstname=?, "
		        . "lastname=?, email=?, emailstatus=?, "
		        . "token=?, language=?, sent=?, completed=?, usesleft=?, validfrom=?, validuntil=?, remindersent=?, remindercount=?";
		        $attrfieldnames=GetAttributeFieldnames($surveyid);
		        foreach ($attrfieldnames as $attr_name)
		        {
		            $udquery.= ", $attr_name=?";
		            $data[].=$_POST[$attr_name];
		        }

		        $udquery .= " WHERE tid={$tokenid}";
				//$this->load->helper("database");
		        //$udresult = db_execute_assoc($udquery);
				$this->db->query($udquery,$data);

				$clang=$this->limesurvey_lang;
				$data['clang']=$this->limesurvey_lang;
				$data['thissurvey']=getSurveyInfo($surveyid);
				$data['imageurl'] = $this->config->item('imageurl');
				$data['surveyid']=$surveyid;
				self::_getAdminHeader();
				$this->load->view("admin/token/tokenbar",$data);
				self::_showMessageBox($clang->gT("Success"),
				$clang->gT("The token entry was successfully updated.")."<br /><br />\n"
		        ."\t\t<input type='button' value='".$clang->gT("Display tokens")."' onclick=\"window.open('".site_url("admin/tokens/browse/$surveyid/")."', '_top')\" />\n");
				self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

		    }
		    else
		    {
				$clang=$this->limesurvey_lang;
				$data['clang']=$this->limesurvey_lang;
				$data['thissurvey']=getSurveyInfo($surveyid);
				$data['imageurl'] = $this->config->item('imageurl');
				$data['surveyid']=$surveyid;
				self::_getAdminHeader();
				$this->load->view("admin/token/tokenbar",$data);
				self::_showMessageBox($clang->gT("Failed"),
						$clang->gT("There is already an entry with that exact token in the table. The same token cannot be used in multiple entries.")."<br /><br />\n"
		        ."\t\t<input type='button' value='".$clang->gT("Show this token entry")."' onclick=\"window.open('".site_url("admin/tokens/edit/$surveyid/$tokenid")."', '_top')\" />\n");
				self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
		    }
		}
		else
		{
			self::_handletokenform($surveyid,"edit",$tokenid);
		}

	}

	/**
	 * Delete tokens
	 */
	function delete($surveyid, $tokenid=null,$limit=50,$start=0,$order=false,$searchstring=false)
	{
		$surveyid = sanitize_int($surveyid);
		$tokenid = (int) $tokenid;
		$limit = (int) $limit;
		$start = (int) $start;

        if(bHasSurveyPermission($surveyid, 'tokens','delete')) {
			$clang=$this->limesurvey_lang;
			$this->load->model("tokens_dynamic_model");
			$this->session->set_userdata('metaHeader', "<meta http-equiv=\"refresh\" content=\"1;URL=".site_url("/admin/tokens/browse/$surveyid")."\" />");

			if($this->input->post("tokenids")) {
			    $tokenidsarray=explode("|", substr($this->input->post("tokenids"), 1)); //Make the tokenids string into an array, and exclude the first character
                                    $data = array('token_id' => $tokenidsarray);
				    foreach($tokenidsarray as $tokenitem) {
				        if($tokenitem != "") $tokenids[]=sanitize_int($tokenitem);
				    }
                            }
                    if(isset($tokenids) && count($tokenids)>0) {
		        if(implode(", ", $tokenids) != "") {
                		$this->tokens_dynamic_model->deleteTokens($surveyid,$tokenids);
		            $tokenoutput = $clang->gT("Marked tokens have been deleted.");
		        } else {
		            $tokenoutput = $clang->gT("No tokens were selected for deletion");
		        }
		    } elseif (isset($tokenid)) {
                        $data = array('token_id' => $tokenid);
                        $this->tokens_dynamic_model->deleteToken($surveyid,$tokenid);
                        $tokenoutput = $clang->gT("Token has been deleted.");
		    }
                        $data['survey_id']=$surveyid; // This is for lime_survey_links delete
                        $this->tokens_dynamic_model->deleteParticipantLinks($data); // This is for lime_survey_links delete
			$data['clang']=$this->limesurvey_lang;
			$data['thissurvey']=getSurveyInfo($surveyid);
			$data['imageurl'] = $this->config->item('imageurl');
			$data['surveyid']=$surveyid;
			self::_getAdminHeader($this->session->userdata('metaHeader'));
			$this->load->view("admin/token/tokenbar",$data);
			self::_showMessageBox($clang->gT("Delete"),
					$tokenoutput . "</strong><font size='1'><i>".$clang->gT("Reloading Screen. Please wait.")."</i></font></p>");
			self::_loadEndScripts();
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
		}
	}

	/**
	 * Add dummy tokens form
	 */
	function adddummys($surveyid)
	{
		$surveyid = sanitize_int($surveyid);
		$clang=$this->limesurvey_lang;
		if(!bHasSurveyPermission($surveyid, 'tokens','create'))
		{
			show_error("no permissions"); // TODO Replace
		}

		if ($this->input->post("subaction"))
		{
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

		    $santitizedtoken='';

            $tokenoutput = '';
		    $tokenoutput .= "\t<div class='header ui-widget-header'>".$clang->gT("Add dummy tokens")."</div>\n"
		    ."\t<div class='messagebox ui-corner-all'>\n";
		    $data = array('firstname' => $_POST['firstname'],
			'lastname' => $_POST['lastname'],
			'email' => sanitize_email($_POST['email']),
			'emailstatus' => 'OK',
			'token' => $santitizedtoken,
			'language' => sanitize_languagecode($_POST['language']),
		        'sent' => 'N',
			'remindersent' => 'N',
			'completed' => 'N',
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
		    $amount = sanitize_int($_POST['amount']);
		    $tokenlength = sanitize_int($_POST['tokenlen']);

		    for ($i=0; $i<$amount;$i++){
		    	$dataToInsert = $data;
		        $dataToInsert['firstname'] = str_replace('{TOKEN_COUNTER}',"$i",$dataToInsert['firstname']);
		        $dataToInsert['lastname'] = str_replace('{TOKEN_COUNTER}',"$i",$dataToInsert['lastname']);
		        $dataToInsert['email'] = str_replace('{TOKEN_COUNTER}',"$i",$dataToInsert['email']);

		        $isvalidtoken = false;
		        while ($isvalidtoken == false)
		        {
		            $newtoken = sRandomChars($tokenlength);
		            if (!isset($existingtokens[$newtoken])) {
		                $isvalidtoken = true;
		                $existingtokens[$newtoken]=null;
		            }
		        }
		        $dataToInsert['token'] = $newtoken;
		        //$tblInsert=db_table_name('tokens_'.$surveyid);
		        //$inresult = $connect->AutoExecute($tblInsert, $dataToInsert, 'INSERT') or safe_die ("Add new record failed:<br />\n$inquery<br />\n".$connect->ErrorMsg());
				$inresult = $this->tokens_dynamic_model->insertTokens($surveyid,$dataToInsert);
		    }

			self::_getAdminHeader();
			self::_showMessageBox($clang->gT("Success"),
					$clang->gT("New dummy tokens were added.")."<br /><br />\n<input type='button' value='"
					.$clang->gT("Display tokens")."' onclick=\"window.open('".site_url("admin/tokens/browse/$surveyid")."', '_top')\" />\n");
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

		} else {
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
	}

	/**
	 * Handle managetokenattributes action
	 */
	function managetokenattributes($surveyid)
	{
		$surveyid = sanitize_int($surveyid);
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
	 * Update token attributes
	 */
	function updatetokenattributes($surveyid)
	{
		$surveyid = sanitize_int($surveyid);
		if (bHasSurveyPermission($surveyid, 'tokens', 'update'))
		{
			$_POST=$this->input->post();
		    $number2add=sanitize_int($_POST['addnumber'],1,100);
		    // find out the existing token attribute fieldnames

		    $tokenfieldnames = array_values($this->db->list_fields("tokens_$surveyid"));
		    $tokenattributefieldnames=array_filter($tokenfieldnames,'filterforattributes');
		    $i=1;
		    for ($b=0;$b<$number2add;$b++)
		    {
		        while (in_array('attribute_'.$i,$tokenattributefieldnames)!==false) {
		            $i++;
		        }
		        $tokenattributefieldnames[]='attribute_'.$i;
		        $fields['attribute_'.$i]=array('type' => 'VARCHAR','constraint' => '255');
		    }
		    //$dict = NewDataDictionary($connect);
		    //$sqlarray = $dict->ChangeTableSQL("{$dbprefix}tokens_$surveyid", $fields);
		    //$execresult=$dict->ExecuteSQLArray($sqlarray, false);
		    $this->load->dbforge();
			$this->dbforge->add_column("tokens_$surveyid", $fields);

			$clang=$this->limesurvey_lang;
			$data['clang']=$this->limesurvey_lang;
			$data['thissurvey']=getSurveyInfo($surveyid);
			$data['imageurl'] = $this->config->item('imageurl');
			$data['surveyid']=$surveyid;
			self::_getAdminHeader();
			$this->load->view("admin/token/tokenbar",$data);
			self::_showMessageBox(sprintf($clang->gT("%s field(s) were successfully added."),$number2add),
			"<br /><input type='button' value='".$clang->gT("Back to attribute field management.")."' onclick=\"window.open('".site_url("admin/tokens/managetokenattributes/$surveyid")."', '_top')\" />");
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
		}
	}

	/**
	 * updatetokenattributedescriptions action
	 */
	function updatetokenattributedescriptions($surveyid)
	{
		$surveyid = sanitize_int($surveyid);
		if (bHasSurveyPermission($surveyid, 'tokens', 'update'))
		{
		    // find out the existing token attribute fieldnames
		    $tokenattributefieldnames=GetAttributeFieldNames($surveyid);
		    $fieldcontents='';
			$_POST=$this->input->post();
		    foreach ($tokenattributefieldnames as $fieldname)
		    {
		        $fieldcontents.=$fieldname.'='.strip_tags($_POST['description_'.$fieldname])."\n";
		    }
		    //$updatequery = "update ".db_table_name('surveys').' set attributedescriptions='.db_quoteall($fieldcontents,true)." where sid=$surveyid";
		    //$execresult=db_execute_assoc($updatequery);
			$this->load->model("surveys_model");
			$this->surveys_model->updateSurvey(array("attributedescriptions"=>$fieldcontents),array("sid"=>$surveyid));
			$clang=$this->limesurvey_lang;
			$data['clang']=$this->limesurvey_lang;
			$data['thissurvey']=getSurveyInfo($surveyid);
			$data['imageurl'] = $this->config->item('imageurl');
			$data['surveyid']=$surveyid;
			self::_getAdminHeader();
			$this->load->view("admin/token/tokenbar",$data);
			self::_showMessageBox($clang->gT("Token attribute descriptions were successfully updated."),
					"<br /><input type='button' value='".$clang->gT("Back to attribute field management.")."' onclick=\"window.open('".site_url("admin/tokens/managetokenattributes/$surveyid")."', '_top')\" />");
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
		}
	}

	/**
	 * Handle email action
	 */
	function email($surveyid,$tokenids=null)
	{
		global $maildebug, $maildebugbody;
		$surveyid = sanitize_int($surveyid);
		$clang=$this->limesurvey_lang;
		if(!bHasSurveyPermission($surveyid, 'tokens', 'update'))
		{
			show_error("no permissions"); // TODO Replace
		}

		if(isset($tokenids) && $tokenids=="tids") {
			$tokenids=$this->input->post("tokenids");
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
    		$_POST=$this->input->post();
			$tokenid=$this->input->post("tokenid");
			$tokenids=$this->input->post("tokenids");
			$maxemails=$this->config->item("maxemails");

			$data['tokenid']=$tokenid;
			$data['tokenids']=$tokenids;

	        if (isset($_POST['bypassbademails']) && $_POST['bypassbademails'] == 'Y')
	        {
	            $SQLemailstatuscondition = " AND emailstatus = 'OK'";
	        }
	        else
	        {
	            $SQLemailstatuscondition = " AND emailstatus <> 'OptOut'";
	        }

	        //$ctfieldcount = $ctresult->FieldCount();
			$ctresult=$this->tokens_dynamic_model->ctquery($surveyid,$SQLemailstatuscondition,$tokenid,$tokenids);
			$ctcount = $ctresult->num_rows();

	        $emresult = $this->tokens_dynamic_model->emquery($surveyid,$SQLemailstatuscondition,$maxemails,$tokenid,$tokenids);
	        $emcount = $emresult->num_rows();

	        $surveylangs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        $baselanguage = GetBaseLanguageFromSurveyID($surveyid);
	        array_unshift($surveylangs,$baselanguage);

			$this->load->config("email");
	        foreach ($surveylangs as $language)
	        {
	            $_POST['message_'.$language]=auto_unescape($_POST['message_'.$language]);
	            $_POST['subject_'.$language]=auto_unescape($_POST['subject_'.$language]);
	            if ($ishtml) $_POST['message_'.$language] = html_entity_decode($_POST['message_'.$language], ENT_QUOTES, $this->config->item("emailcharset"));
	        }

	        $attributes=GetTokenFieldsAndNames($surveyid);
			$tokenoutput="";

	        if ($emcount > 0)
	        {
	            foreach ($emresult->result_array() as $emrow)
	            {
	                unset($fieldsarray);
                    $to=array();
                    $aEmailaddresses=explode(';',$emrow['email']);
                    foreach($aEmailaddresses as $sEmailaddress)
                    {
                        $to[]=$emrow['firstname']." ".$emrow['lastname']." <{$sEmailaddress}>";
                    }
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

					$publicurl=site_url();
					$modrewrite=$this->config->item("modrewrite");
	                if ($ishtml === false)
	                {
	                    $fieldsarray["{OPTOUTURL}"]="$publicurl/optout/local/".trim($emrow['language'])."/$surveyid/{$emrow['token']}";
                            $fieldsarray["{OPTINURL}"]="$publicurl/optin/local/".trim($emrow['language'])."/$surveyid/{$emrow['token']}";

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

	                    $fieldsarray["{OPTOUTURL}"]="<a href='$publicurl/optout/local/".trim($emrow['language'])."/$surveyid/{$emrow['token']}'>".htmlspecialchars("$publicurl/optout/local/".trim($emrow['language'])."/$surveyid/{$emrow['token']}")."</a>";
                            $fieldsarray["{OPTINURL}"]="<a href='$publicurl/optin/local/".trim($emrow['language'])."/$surveyid/{$emrow['token']}'>".htmlspecialchars("$publicurl/optin/local".trim($emrow['language'])."/$surveyid/{$emrow['token']}")."</a>";
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
	                elseif (SendEmailMessage($modmessage, $modsubject, $to , $from, $this->config->item("sitename"), $ishtml, getBounceEmail($surveyid),null,$customheaders))
	                {
	                    // Put date into sent
	                    $today = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $this->config->item("timeadjust"));
	                    $udequery = "UPDATE ".$this->db->dbprefix("tokens_{$surveyid}")."\n"
	                    ."SET sent='$today' WHERE tid={$emrow['tid']}";
	                    //
	                    $uderesult = db_execute_assoc($udequery);
	                    $tokenoutput .= $clang->gT("Invitation sent to:")." {$emrow['firstname']} {$emrow['lastname']} ($to)<br />\n";
	                    if ($this->config->item("emailsmtpdebug")==2)
	                    {
	                        $tokenoutput .=$maildebug;
	                    }
	                }
	                else
	                {
	                    $tokenoutput .= '<li>'.ReplaceFields($clang->gT("Email to {FIRSTNAME} {LASTNAME} ({EMAIL}) failed. Error Message:")." ".$maildebug."<br />", $fieldsarray).'</li>';
	                    /*if ($debug>0)
	                    {
	                        $tokenoutput .= "<pre>Subject : $modsubject<br /><br />".htmlspecialchars($maildebugbody)."</pre>";
	                    }*/
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
				$data['clang']=$this->limesurvey_lang;
				$data['thissurvey']=getSurveyInfo($surveyid);
				$data['imageurl'] = $this->config->item('imageurl');
				$data['surveyid']=$surveyid;
				$data['tokenoutput']=$tokenoutput;
				self::_getAdminHeader();
				$this->load->view("admin/token/tokenbar",$data);
				$this->load->view("admin/token/emailpost",$data);
				self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
	        }
	        else
	        {
				$data['clang']=$this->limesurvey_lang;
				$data['thissurvey']=getSurveyInfo($surveyid);
				$data['imageurl'] = $this->config->item('imageurl');
				$data['surveyid']=$surveyid;
				self::_getAdminHeader();
				$this->load->view("admin/token/tokenbar",$data);
				self::_showMessageBox($clang->gT("Warning"),
						$clang->gT("There were no eligible emails to send. This will be because none satisfied the criteria of:")
	            ."<br/>&nbsp;<ul><li>".$clang->gT("having a valid email address")."</li>"
	            ."<li>".$clang->gT("not having been sent an invitation already")."</li>"
	            ."<li>".$clang->gT("having already completed the survey")."</li>"
	            ."<li>".$clang->gT("having a token")."</li></ul>");
				self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
	        }
	    }
	}

	/**
	 * Remind Action
	 */
	function remind($surveyid)
	{
		$surveyid = sanitize_int($surveyid);
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
	    	//Views don't work properly when sending emails: The user will only receive feedback after the script is executed.
	    	$tokenoutput="";
			$_POST=$this->input->post();
			$this->load->helper("database");
	        //$tokenoutput .= $clang->gT("Sending Reminders")

	        $surveylangs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        $baselanguage = GetBaseLanguageFromSurveyID($surveyid);
	        array_unshift($surveylangs,$baselanguage);

	        foreach ($surveylangs as $language)
	        {
	            $_POST['message_'.$language]=auto_unescape($_POST['message_'.$language]);
	            $_POST['subject_'.$language]=auto_unescape($_POST['subject_'.$language]);

	        }

	        if (isset($starttokenid)) {$tokenoutput .= " (".$clang->gT("From token ID").":&nbsp;{$starttokenid})";}
	        if (isset($tokenid)) {$tokenoutput .= " (".$clang->gT("Sending to token ID").":&nbsp;{$tokenid})";}
	        if (isset($tokenids)) {$tokenoutput .= " (".$clang->gT("Sending to token IDs").":&nbsp;".implode("|", $tokenids).")";}

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

	        $ctquery = "SELECT * FROM ".$this->db->dbprefix("tokens_{$surveyid}")." WHERE (completed ='N' or completed ='') AND sent<>'' AND sent<>'N' AND token <>'' AND email <> '' $SQLemailstatuscondition $SQLremindercountcondition $SQLreminderdelaycondition";

	        if (isset($starttokenid)) {$ctquery .= " AND tid > '{$starttokenid}'";}
	        if (isset($tokenid) && $tokenid) {$ctquery .= " AND tid = '{$tokenid}'";}
	        if (isset($tokenids)) {$ctquery .= " AND tid IN (".implode(", ", $tokenids).")";}
	        $tokenoutput .= "<!-- ctquery: $ctquery -->\n";
	        $ctresult = db_execute_assoc($ctquery) or safe_die ("Database error!<br />\n" . $connect->ErrorMsg());
	        $ctcount = $ctresult->num_rows();
	        //$ctfieldcount = $ctresult->FieldCount();
	        $emquery = "SELECT * FROM ".$this->db->dbprefix("tokens_{$surveyid}")." WHERE (completed = 'N' or completed = '') AND sent <> 'N' and sent <>'' AND token <>'' AND EMAIL <>'' $SQLemailstatuscondition $SQLremindercountcondition $SQLreminderdelaycondition";

	        if (isset($starttokenid)) {$emquery .= " AND tid > '{$starttokenid}'";}
	        if (isset($tokenid) && $tokenid) {$emquery .= " AND tid = '{$tokenid}'";}
	        if (isset($tokenids)) {$emquery .= " AND tid IN (".implode(", ", $tokenids).")";}
	        $emquery .= " ORDER BY tid ";
	        $emresult = db_select_limit_assoc($emquery, $this->config->item("maxemails")) or safe_die ("Couldn't do query.<br />$emquery<br />".$connect->ErrorMsg());
	        $emcount = $emresult->num_rows();


	        $attributes=GetTokenFieldsAndNames($surveyid);
	        if ($emcount > 0)
	        {
	            $tokenoutput .= "<table width='450' align='center' >\n"
	            ."\t<tr>\n"
	            ."<td><font size='1'>\n";
	            while ($emrow = $emresult->FetchRow())
	            {
	                unset($fieldsarray);
                    $to=array();
                    $aEmailaddresses=explode(';',$emrow['email']);
                    foreach($aEmailaddresses as $sEmailaddress)
                    {
                        $to[]=$emrow['firstname']." ".$emrow['lastname']." <{$sEmailaddress}>";
                    }
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
	                    $fieldsarray["{OPTOUTURL}"]="$publicurl/optout/local/".trim($emrow['language'])."/$surveyid/{$emrow['token']}";
                            $fieldsarray["{OPTINURL}"]="$publicurl/optin/local/".trim($emrow['language'])."/$surveyid/{$emrow['token']}";
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
	                    $fieldsarray["{OPTOUTURL}"]="<a href='$publicurl/optout/local/".trim($emrow['language'])."/$surveyid/{$emrow['token']}'>".htmlspecialchars("$publicurl/optout/local/".trim($emrow['language'])."/$surveyid/{$emrow['token']}")."</a>";
                            $fieldsarray["{OPTINURL}"]="<a href='$publicurl/optin/local/".trim($emrow['language'])."/$surveyid/{$emrow['token']}'>".htmlspecialchars("$publicurl/optin/local/".trim($emrow['language'])."/$surveyid/{$emrow['token']}")."</a>";
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
                    $tokenoutput .= "<p><input type='button' name='addtocpdb' id='addtocpdb' value='addtocpdb'/>";
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
		echo $tokenoutput;
		}
	}

	/**
	 * Export Dialog
	 */
	function exportdialog($surveyid)
	{
		$surveyid = sanitize_int($surveyid);
		if (bHasSurveyPermission($surveyid, 'tokens','export') )//EXPORT FEATURE SUBMITTED BY PIETERJAN HEYSE
		{
			$this->load->helper("database");
			if ($this->input->post('submit'))
		    {
		    	$this->load->helper("export");
				tokens_export($surveyid);
			}
		    $langquery = "SELECT language FROM ".$this->db->dbprefix("tokens_$surveyid")." group by language";
		    $langresult = db_execute_assoc($langquery);
			$data['resultr'] = $langresult->row_array();

			$data['clang']=$this->limesurvey_lang;
			$thissurvey=getSurveyInfo($surveyid);
			$data['thissurvey']=$thissurvey;
			$data['imageurl'] = $this->config->item('imageurl');
			$data['surveyid']=$surveyid;


			self::_getAdminHeader();
			$this->load->view("admin/token/tokenbar",$data);
			$this->load->view("admin/token/exportdialog",$data);
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
		}
	}

	/**
	 * Generate Tokens
	 */
	function tokenify($surveyid)
	{
		$surveyid = sanitize_int($surveyid);
		$clang = $this->limesurvey_lang;
		$data['clang']=$this->limesurvey_lang;
		$data['thissurvey']=getSurveyInfo($surveyid);
		$data['imageurl'] = $this->config->item('imageurl');
		$data['surveyid']=$surveyid;

		if (bHasSurveyPermission($surveyid, 'tokens', 'update'))
		{
		    //$tokenoutput .= "<div class='header ui-widget-header'>".$clang->gT("Create tokens")."</div>\n";
		    if (!$this->input->post('ok'))
		    {

				self::_getAdminHeader();
				$this->load->view("admin/token/tokenbar",$data);
				self::_showMessageBox($clang->gT("Create tokens"),
						$clang->gT("Clicking yes will generate tokens for all those in this token list that have not been issued one. Is this OK?")."<br /><br />\n"
		        ."<input type='submit' value='"
		        //		.$clang->gT("Yes")."' onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=tokenify&amp;ok=Y', '_top')\" />\n"
		        .$clang->gT("Yes")."' onclick=\"".get2post(site_url("admin/tokens/tokenify/$surveyid")."?action=tokens&amp;sid=$surveyid&amp;subaction=tokenify&amp;ok=Y")."\" />\n"
		        ."<input type='submit' value='"
		        .$clang->gT("No")."' onclick=\"window.open('".site_url("admin/tokens/index/$surveyid")."', '_top')\" />\n"
		        ."<br />\n");
				self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));

		    }
		    else
		    {
		        //get token length from survey settings
		        //$tlquery = "SELECT tokenlength FROM ".db_table_name("surveys")." WHERE sid=$surveyid";
		        //$tlresult = db_execute_assoc($tlquery);
				$this->load->model("surveys_model");
				$tlresult = $this->surveys_model->getSomeRecords(array("tokenlength"),array("sid"=>$surveyid));
		        $tlrow = $tlresult->row_array();
		        $tokenlength = $tlrow['tokenlength'];

		        //if tokenlength is not set or there are other problems use the default value (15)
		        if(!isset($tokenlength) || $tokenlength == '')
		        {
		            $tokenlength = 15;
		        }

		        // select all existing tokens
		        //$ntquery = "SELECT token FROM ".db_table_name("tokens_$surveyid")." group by token";
				$this->load->model("tokens_dynamic_model");
				$ntresult = $this->tokens_dynamic_model->getSomeRecords(array("token"),$surveyid,FALSE,"token");
		        //$ntresult = db_execute_assoc($ntquery);
		        foreach ($ntresult->result_array() as $tkrow)
		        {
		            $existingtokens[$tkrow['token']]=null;
		        }
		        $newtokencount = 0;
		        //$tkquery = "SELECT tid FROM ".db_table_name("tokens_$surveyid")." WHERE token IS NULL OR token=''";
		        //$tkresult = db_execute_assoc($tkquery) or safe_die ("Mucked up!<br />$tkquery<br />".$connect->ErrorMsg());
				$tkresult = $this->tokens_dynamic_model->selectEmptyTokens($surveyid);
		        foreach ($tkresult->result_array() as $tkrow)
		        {
		            $isvalidtoken = false;
		            while ($isvalidtoken == false)
		            {
		                $newtoken = sRandomChars($tokenlength);
		                if (!isset($existingtokens[$newtoken])) {
		                    $isvalidtoken = true;
		                    $existingtokens[$newtoken]=null;
		                }
		            }
		            //$itquery = "UPDATE ".db_table_name("tokens_$surveyid")." SET token='$newtoken' WHERE tid={$tkrow['tid']}";
		            //$itresult = $connect->Execute($itquery);
					$itresult = $this->tokens_dynamic_model->updateToken($surveyid,$tkrow['tid'],$newtoken);
		            $newtokencount++;
		        }
		        $message=str_replace("{TOKENCOUNT}", $newtokencount, $clang->gT("{TOKENCOUNT} tokens have been created"));
		        //$tokenoutput .= "<div class='successheader'>$message</div>\n";
				self::_getAdminHeader();
				$this->load->view("admin/token/tokenbar",$data);
				self::_showMessageBox($clang->gT("Create tokens"),$message);
				self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
		    }
		}
	}

	/**
	 * Remove Token Database
	 */
	function kill($surveyid)
	{
		$surveyid = sanitize_int($surveyid);
		$clang = $this->limesurvey_lang;
		$data['clang']=$this->limesurvey_lang;
		$data['thissurvey']=getSurveyInfo($surveyid);
		$data['imageurl'] = $this->config->item('imageurl');
		$data['surveyid']=$surveyid;

		if (bHasSurveyPermission($surveyid, 'surveyactivation', 'update'))
		{
			$_POST = $this->input->post();
		    $date = date('YmdHis');
		    //$tokenoutput .= "<div class='header ui-widget-header'>".$clang->gT("Delete Tokens Table")."</div>\n"
		    //."<div class='messagebox ui-corner-all'>\n";
		    // ToDo: Just delete it if there is no token in the table
		    if (!isset($_POST['ok']) || !$_POST['ok'])
		    {
				self::_getAdminHeader();
				$this->load->view("admin/token/tokenbar",$data);
				self::_showMessageBox($clang->gT("Delete Tokens Table"),$clang->gT("If you delete this table tokens will no longer be required to access this survey.")."<br />".$clang->gT("A backup of this table will be made if you proceed. Your system administrator will be able to access this table.")."<br />\n"
		        ."( \"old_tokens_{$surveyid}_$date\" )<br /><br />\n"
		        ."<input type='submit' value='"
		        .$clang->gT("Delete Tokens")."' onclick=\"".get2post(site_url("admin/tokens/kill/$surveyid")."?action=tokens&amp;sid=$surveyid&amp;subaction=kill&amp;ok=surething")."\" />\n"
		        ."<input type='submit' value='"
		        .$clang->gT("Cancel")."' onclick=\"window.open('".site_url("admin/tokens/index/$surveyid")."', '_top')\" />\n");
				self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
		    }
		    elseif (isset($_POST['ok']) && $_POST['ok'] == "surething")
		    {
		        $oldtable = "tokens_$surveyid";
		        $newtable = "old_tokens_{$surveyid}_$date";
		        //$deactivatequery = db_rename_table( db_table_name_nq($oldtable), db_table_name_nq($newtable));
		        $this->load->dbforge();
				$this->dbforge->rename_table($this->db->dbprefix($oldtable) , $this->db->dbprefix($newtable));

				//CodeIgniter should handle this correctly
		        /*if ($databasetype=='postgres')
		        {
		            // If you deactivate a postgres table you have to rename the according sequence too and alter the id field to point to the changed sequence
		            $oldTableJur = db_table_name_nq($oldtable);
		            $deactivatequery = db_rename_table(db_table_name_nq($oldtable),db_table_name_nq($newtable).'_tid_seq');
		            $deactivateresult = $connect->Execute($deactivatequery) or die ("oldtable : ".$oldtable. " / oldtableJur : ". $oldTableJur . " / ".htmlspecialchars($deactivatequery)." / Could not rename the old sequence for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
		            $setsequence="ALTER TABLE ".db_table_name_nq($newtable)."_tid_seq ALTER COLUMN tid SET DEFAULT nextval('".db_table_name_nq($newtable)."_tid_seq'::regclass);";
		            $deactivateresult = $connect->Execute($setsequence) or die (htmlspecialchars($setsequence)." Could not alter the field tid to point to the new sequence name for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
		            $setidx="ALTER INDEX ".db_table_name_nq($oldtable)."_idx RENAME TO ".db_table_name_nq($newtable)."_idx;";
		            $deactivateresult = $connect->Execute($setidx) or die (htmlspecialchars($setidx)." Could not alter the index for this token table. The database reported the following error:<br />".htmlspecialchars($connect->ErrorMsg())."<br /><br />Survey was not deactivated either.<br /><br /><a href='$scriptname?sid={$_GET['sid']}'>".$clang->gT("Main Admin Screen")."</a>");
		        } else {
		            $deactivateresult = $connect->Execute($deactivatequery) or die ("Couldn't deactivate because:<br />\n".htmlspecialchars($connect->ErrorMsg())." - Query: ".htmlspecialchars($deactivatequery)." <br /><br />\n<a href='$scriptname?sid=$surveyid'>Admin</a>\n");
		        }*/

				self::_getAdminHeader();
				$this->load->view("admin/token/tokenbar",$data);
				self::_showMessageBox($clang->gT("Delete Tokens Table"),'<br />'.$clang->gT("The tokens table has now been removed and tokens are no longer required to access this survey.")."<br /> ".$clang->gT("A backup of this table has been made and can be accessed by your system administrator.")."<br />\n"
		        ."(\"old_tokens_{$surveyid}_$date\")"."<br /><br />\n"
		        ."<input type='submit' value='"
		        .$clang->gT("Main Admin Screen")."' onclick=\"window.open('".base_url("admin/")."', '_top')\" />");
				self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
		    }
		}
	}

	function bouncesettings($surveyid)
	{
		$surveyid = sanitize_int($surveyid);
		$clang = $this->limesurvey_lang;
		$data['clang']=$this->limesurvey_lang;
		$data['thissurvey'] = $data['settings']=getSurveyInfo($surveyid);
		$data['imageurl'] = $this->config->item('imageurl');
		$data['surveyid']=$surveyid;

		if($this->input->post())
		{
			$_POST = $this->input->post();
			@$fieldvalue = array("bounceprocessing"=>$_POST['bounceprocessing'],
			"bounce_email"=>$_POST['bounce_email'],
			);

			if(@$_POST['bounceprocessing']=='L')
			{
				$fieldvalue['bounceaccountencryption']=$_POST['bounceaccountencryption'];
				$fieldvalue['bounceaccountuser']=$_POST['bounceaccountuser'];
				$fieldvalue['bounceaccountpass']=$_POST['bounceaccountpass'];
				$fieldvalue['bounceaccounttype']=$_POST['bounceaccounttype'];
				$fieldvalue['bounceaccounthost']=$_POST['bounceaccounthost'];
			}

			$where = "sid = $surveyid";
			$this->load->helper("database");
			db_execute_assoc($this->db->update_string('surveys', $fieldvalue, $where));
			//$connect->AutoExecute("{$dbprefix}surveys", $fieldvalue, 2,"sid=$surveyid",get_magic_quotes_gpc());
			self::_getAdminHeader();
			$this->load->view("admin/token/tokenbar",$data);
		    self::_showMessageBox($clang->gT("Bounce settings"),$clang->gT("Bounce settings have been saved."),"successheader");
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
		}
		else
		{
			self::_getAdminHeader();
			$this->load->view("admin/token/tokenbar",$data);
			$this->load->view("admin/token/bounce",$data);
			self::_getAdminFooter("http://docs.limesurvey.org", $this->limesurvey_lang->gT("LimeSurvey online manual"));
		}
	}

	/**
	 * Handle token form for addnew/edit actions
	 */
	function _handletokenform($surveyid,$subaction,$tokenid="")
	{
		$clang=$this->limesurvey_lang;
		$this->load->model("tokens_dynamic_model");
		$tkcount=$this->tokens_dynamic_model->totalTokens($surveyid);
		$this->load->helper("surveytranslator");

		if ($subaction == "edit")
	    {
	        $edquery = "SELECT * FROM ".$this->db->dbprefix("tokens_$surveyid")." WHERE tid={$tokenid}";
			$this->load->helper("database");
	        $edresult = db_execute_assoc($edquery);
	        //$edfieldcount = $edresult->FieldCount();
	        $edrow=$edresult->row_array();
	        //Create variables with the same names as the database column names and fill in the value
	        foreach ($edrow as $Key=>$Value) {$data['tokendata'][$Key] = $Value;}
			$data['tokenid']=$tokenid;

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
                                'participant_id' => array('type' => 'VARCHAR', 'constraint' => 50),
	                        'firstname' => array('type' => 'VARCHAR', 'constraint' => 40),
	                        'lastname' => array('type' => 'VARCHAR', 'constraint' => 40),
	                        'email' => array('type' => 'TEXT'),
	                        'emailstatus' => array('type' => 'TEXT'),
	                        'token' => array('type' => 'VARCHAR', 'constraint' => 36),
	                        'language' => array('type' => 'VARCHAR', 'constraint' => 25),
	                        'blacklisted' => array('type' => 'CHAR', 'constraint' => 1),
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
	        $this->load->dbforge();
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
				   $oldlist[]=reset($rows);
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
