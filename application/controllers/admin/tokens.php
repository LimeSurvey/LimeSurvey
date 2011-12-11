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
 * $Id: tokens.php 11305 2011-11-01 15:00:14Z c_schmitz $
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
class tokens extends Survey_Common_Action
{
	private $yii;
	private $controller;
	
	public function run($sa)
	{
		$this->yii = Yii::app();
		$this->controller = $this->getController();
		$sa = (!$sa) ? "" : $sa;
		
		if ($sa == 'index')
			$this->route('index', array('surveyid'));
		elseif ($sa == 'addnew')
			$this->route('addnew', array('surveyid'));
		elseif ($sa == 'browse')
			$this->route('browse', array('surveyid', 'limit', 'start', 'order', 'searchstring'));
		elseif ($sa == 'remind')
			$this->route('remind', array('surveyid'));
		elseif ($sa == 'email')
			$this->route('email', array('surveyid'));
		elseif ($sa == 'bounceprocessing')
			$this->route('bounceprocessing', array('surveyid'));
		elseif ($sa == 'bouncesettings')
			$this->route('bouncesettings', array('surveyid'));
		elseif ($sa == 'exportdialog')
			$this->route('exportdialog', array('surveyid'));
		elseif ($sa == 'import')
			$this->route('import', array('surveyid'));
		elseif ($sa == 'importldap')
			$this->route('importldap', array('surveyid'));
	        elseif ($sa == 'kill')
	                $this->route('kill', array('surveyid'));
		elseif ($sa == 'adddummys')
			$this->route('adddummys', array('surveyid', 'subaction'));
		elseif ($sa == 'tokenify')
			$this->route('tokenify', array('surveyid'));
		elseif ($sa == 'managetokenattributes')
			$this->route('managetokenattributes', array('surveyid'));
		elseif ($sa == 'updatetokenattributes')
			$this->route('updatetokenattributes', array('surveyid'));
		elseif ($sa == 'updatetokenattributedescriptions')
			$this->route('updatetokenattributedescriptions', array('surveyid'));
	}

	/**
	 * Show token index page, handle token database
	 */
	function index($surveyid)
	{
		$surveyid = sanitize_int($surveyid);

		$clang = $this->getController()->lang;
		if(!bHasSurveyPermission($surveyid,'tokens','read'))
		{
			show_error("no permissions"); // TODO Replace
		}

		$this->getController()->_js_admin_includes(Yii::app()->baseUrl."/scripts/admin/tokens.js");

		Yii::app()->loadHelper("surveytranslator");

		$dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
		$thissurvey = getSurveyInfo($surveyid);

		if ($thissurvey===false)
		{
			show_error($clang->gT("The survey you selected does not exist")); // TODO Replace
		}

        $data['surveyprivate'] = $thissurvey['anonymized'];
		// CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
		$tokenexists=Yii::app()->db->schema->getTable('{{tokens_'.$surveyid.'}}');
		if (!$tokenexists) //If no tokens table exists
		{
			self::_newtokentable($surveyid);
		}
		else
		{
			$data['clang'] = $clang;
			$data['thissurvey'] = $thissurvey;
			$data['imageurl'] = Yii::app()->getConfig('imageurl');
			$data['surveyid'] = $surveyid;

			Tokens_dynamic::sid($surveyid);
			$data['queries']= Tokens_dynamic::model()->summary();

			$this->getController()->_getAdminHeader();
			$this->getController()->render("/admin/token/tokenbar",$data);
			$this->getController()->render("/admin/token/tokensummary",$data);
			$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
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

        $clang = $this->controller->lang;

    	Tokens_dynamic::sid($surveyid);

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

    		if($mbox=@imap_open('{'.$hostname.$flags.'}INBOX',$username,$pass))
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
    							$bouncequery = "UPDATE {{tokens_$surveyid}} SET emailstatus='bounced' WHERE token='$tokenBounce[1]';";
    							$data = array(
                                        'emailstatus'=> 'bounced'

                                );
                                $condn = array('token' => $tokenBounce[1]);

    							$record = Tokens_dynamic::model()->findByAttributes($condn);
								foreach ($data as $k => $v)
									$record->$k = $v;
    							$record->save();

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
    			$entertimestamp = "update {{surveys}} set bouncetime='$datelastbounce' where sid='$surveyid'";
                $data = array('bouncetime' => $datelastbounce);
                $condn = array('sid' => $surveyid);
                $survey = Survey::model()->findByAttributes($condn);
    			$survey->bouncetime = $datelistbounce;
    			$executetimestamp = $survey->save();

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
	function browse($surveyid, $limit=50, $start=0, $order=false, $searchstring=false)
	{
		Yii::app()->loadHelper('surveytranslator');
		Yii::import('application.libraries.Date_Time_Converter', true);
		$dateformatdetails=getDateFormatData(Yii::app()->session['dateformat']);

		$surveyid = sanitize_int($surveyid);
		$limit = (int) $limit;
		$start = (int) $start;

		$clang=$this->controller->lang;
		Tokens_dynamic::sid($surveyid);

		$tkcount = count(Tokens_dynamic::model()->findAll());

		$this->getController()->_js_admin_includes(Yii::app()->baseUrl."/scripts/admin/tokens.js");

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
		if(!$order) $order=!empty($_POST['order']) ? $_POST['order'] : '';
		$order=preg_replace('/[^_ a-z0-9-]/i', '',$order);
		if ($order=="") {$order = "tid";}
		if(!empty($_POST['limit'])) $limit = $_POST['limit'];
		if(!empty($_POST['start'])) $start = $_POST['start'];
		//if (!isset($order)) {$order=preg_replace('/[^_ a-z0-9-]/i', '', returnglobal('order'));}
		//if (!isset($limit)) {$limit=(int)returnglobal('limit');}

    	//ALLOW SELECTION OF NUMBER OF RECORDS SHOWN		$thissurvey=getSurveyInfo($surveyid);

		if(!$searchstring) $searchstring=!empty($_POST['searchstring']) ? $_POST['searchstring'] : '';
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
			$iquery = array();
			foreach ($idata as $k => $v)
				$iquery[] = $k . ' LIKE "' . $searchstring . '%"';
			$iquery = '(' . implode(' OR ', $iquery) . ')';
		}
		else
		{
			$iquery = '';
		}

		$tokens = Tokens_dynamic::model()->findAll(array('condition' => $iquery, 'limit' => $limit, 'offset' => $start, 'order' => $order));
		$data['bresult'] = array();
		foreach ($tokens as $token)
			$data['bresult'][] = $token->attributes;

		$data['clang']=$clang;
		$data['thissurvey']=getSurveyInfo($surveyid);
		$data['searchstring']=$searchstring;
		$data['imageurl'] = Yii::app()->getConfig('imageurl');
		$data['surveyid']=$surveyid;
		$data['bgc']="";
		$data['limit']=$limit;
		$data['start']=$start;
		$data['order']=$order;
		$data['surveyprivate'] = $data['thissurvey']['anonymized'];
		$data['dateformatdetails'] = $dateformatdetails;

		$this->getController()->_getAdminHeader();
		$this->getController()->render("/admin/token/tokenbar",$data);
		$this->getController()->render("/admin/token/browse",$data);
		$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));

	}

	/**
	 * Add new token form
	 */
	function addnew($surveyid)
	{
		$surveyid = sanitize_int($surveyid);
		Yii::app()->loadHelper("surveytranslator");

		/*if (($subaction == "edit" &&  bHasSurveyPermission($surveyid, 'tokens','update')) ||
    	($subaction == "addnew" && bHasSurveyPermission($surveyid, 'tokens','create')))*/
		$dateformatdetails=getDateFormatData(Yii::app()->session['dateformat']);

		if(!bHasSurveyPermission($surveyid, 'tokens','create'))
		{
			show_error("no permissions"); // TODO Replace
		}

		Tokens_dynamic::sid($surveyid);

		if (!empty($_POST['subaction']))
		{
			$clang=$this->controller->lang;

			Yii::import('application.libraries.Date_Time_Converter');
		    //Fix up dates and match to database format
		    if (trim($_POST['validfrom'])=='') {
		        $_POST['validfrom']=null;
		    }
		    else
		    {
		        $datetimeobj = new Date_Time_Converter(array(trim($_POST['validfrom']), $dateformatdetails['phpdate'].' H:i'));
		        $_POST['validfrom'] =$datetimeobj->convert('Y-m-d H:i:s');
		    }
		    if (trim($_POST['validuntil'])=='') {$_POST['validuntil']=null;}
		    else
		    {
		        $datetimeobj = new Date_Time_Converter(array(trim($_POST['validuntil']), $dateformatdetails['phpdate'].' H:i'));
		        $_POST['validuntil'] =$datetimeobj->convert('Y-m-d H:i:s');
		    }

		    $santitizedtoken=sanitize_token($_POST['token']);

		    $data = array(
		    	'firstname' => $_POST['firstname'],
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
				'validuntil' => $_POST['validuntil'],
			);

		    // add attributes
		    $attrfieldnames=GetAttributeFieldnames($surveyid);
		    foreach ($attrfieldnames as $attr_name)
		    {
		        $data[$attr_name]=$_POST[$attr_name];
		    }
		    //$tblInsert=db_table_name('tokens_'.$surveyid);
		    //$udresult = $connect->Execute("Select * from ".db_table_name("tokens_$surveyid")." where  token<>'' and token='{$santitizedtoken}'");
			$udresult = Tokens_dynamic::model()->findAll("token <> '' and token = '$santitizedtoken'");
		    if (count($udresult) == 0)//RecordCount()==0)
		    {
		        // AutoExecute
		        //$inresult = $connect->AutoExecute($tblInsert, $data, 'INSERT') or safe_die ("Add new record failed:<br />\n$inquery<br />\n".$connect->ErrorMsg());
				$token = new Tokens_dynamic;
		    	foreach ($data as $k => $v)
		    		$token->$k = $v;
				$inresult = $token->save();
				$data['success']=true;
			}
		    else
		    {
	        	$data['success']=false;
			}

			$data['clang']=$clang;
			$thissurvey = getSurveyInfo($surveyid);
			$data['thissurvey']=$thissurvey;
			$data['imageurl'] = Yii::app()->getConfig('imageurl');
			$data['surveyid']=$surveyid;


			$this->getController()->_getAdminHeader();
			$this->getController()->render("/admin/token/tokenbar",$data);
			$this->getController()->render("/admin/token/addtokenpost",$data);
			$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
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
                		$this->tokens_dynamic_model->deleteRecords($surveyid,$tokenids);
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
	function adddummys($surveyid, $subaction = '')
	{
		$surveyid = sanitize_int($surveyid);
		$clang = $this->getController()->lang;
		if(!bHasSurveyPermission($surveyid, 'tokens','create'))
		{
			die("No permissions."); // TODO Replace
		}

		$this->getController()->loadHelper("surveytranslator");

		if (!empty($subaction) && $subaction == 'add')
		{
			$this->getController()->loadLibrary('Date_Time_Converter');
			$dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);

		    //Fix up dates and match to database format
		    if (trim($_POST['validfrom'])=='') {
		        $_POST['validfrom'] = null;
		    } else {
				$datetimeobj = new Date_Time_Converter(array(trim($_POST['validfrom']), $dateformatdetails['phpdate'].' H:i'));
		        $_POST['validfrom'] = $datetimeobj->convert('Y-m-d H:i:s');
		    }
		    if (trim($_POST['validuntil'])=='') {$_POST['validuntil']=null;}
		    else
		    {
				$datetimeobj = new Date_Time_Converter(array(trim($_POST['validuntil']), $dateformatdetails['phpdate'].' H:i'));
		        $_POST['validuntil'] = $datetimeobj->convert('Y-m-d H:i:s');
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
		    $attrfieldnames = GetAttributeFieldnames($surveyid);
		    foreach ($attrfieldnames as $attr_name)
		    {
		        $data[$attr_name] = $_POST[$attr_name];
		    }
		    //$tblInsert=db_table_name('tokens_'.$surveyid);
		    $amount = sanitize_int($_POST['amount']);
		    $tokenlength = sanitize_int($_POST['tokenlen']);

		    for ($i=0; $i<$amount;$i++){
		    	$dataToInsert = $data;
		        $dataToInsert['firstname'] = str_replace('{TOKEN_COUNTER}', $i, $dataToInsert['firstname']);
		        $dataToInsert['lastname'] = str_replace('{TOKEN_COUNTER}', $i, $dataToInsert['lastname']);
		        $dataToInsert['email'] = str_replace('{TOKEN_COUNTER}', $i, $dataToInsert['email']);

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
				Tokens_dynamic::insertToken($surveyid, $dataToInsert);
		    }

			$this->getController()->_getAdminHeader();
			$this->getController()->_showMessageBox($clang->gT("Success"),
					$clang->gT("New dummy tokens were added.")."<br /><br />\n<input type='button' value='"
					.$clang->gT("Display tokens")."' onclick=\"window.open('".$this->getController()->createUrl("admin/tokens/sa/browse/surveyid/$surveyid")."', '_top')\" />\n");
			$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));

		} else {
			$tkcount = Tokens_dynamic::totalRecords($surveyid);
			$tokenlength = Yii::app()->db->createCommand()->select('tokenlength')->from('{{surveys}}')->where('sid='.$surveyid)->query()->readColumn(0);

		    if(empty($tokenlength)) {
		        $tokenlength = 15;
		    }

			$data['clang'] = $clang;
			$thissurvey = getSurveyInfo($surveyid);
			$data['thissurvey'] = $thissurvey;
			$data['imageurl'] = Yii::app()->getConfig('imageurl');
			$data['surveyid'] = $surveyid;
			$data['tokenlength'] = $tokenlength;
			$data['dateformatdetails'] = getDateFormatData(Yii::app()->session['dateformat']);

			$this->getController()->_getAdminHeader();
			$this->getController()->render("/admin/token/tokenbar",$data);
			$this->getController()->render("/admin/token/dummytokenform",$data);
			$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
		}
	}

	/**
	 * Handle managetokenattributes action
	 */
	function managetokenattributes($surveyid)
	{
		$surveyid = sanitize_int($surveyid);
		$clang=$this->controller->lang;
		if(!bHasSurveyPermission($surveyid, 'tokens', 'update'))
		{
			safe_die("no permissions"); // TODO Replace
		}

		//$this->load->model("tokens_dynamic_model");
		Tokens_dynamic::sid($surveyid);
		$tkcount=Tokens_dynamic::model()->totalRecords($surveyid);
		$this->yii->loadHelper("surveytranslator");

		//$this->load->model("surveys_model");
		$query = Tokens_dynamic::model()->findAll(array('limit' => 1));
		$examplerow = $query;

		$tokenfields=GetTokenFieldsAndNames($surveyid,true);
    	$nrofattributes=0;

		$data['clang']=$clang;
		$thissurvey=getSurveyInfo($surveyid);
		$data['thissurvey']=$thissurvey;
		$data['imageurl'] = $this->yii->getConfig('imageurl');
		$data['surveyid']=$surveyid;
		$data['tokenfields']=$tokenfields;
		$data['nrofattributes']=$nrofattributes;
		$data['examplerow']=$examplerow;

		$this->controller->_getAdminHeader();
		$this->controller->render("/admin/token/tokenbar",$data);
		$this->controller->render("/admin/token/managetokenattributes",$data);
		$this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->controller->lang->gT("LimeSurvey online manual"));
	}

	/**
	 * Update token attributes
	 */
	function updatetokenattributes($surveyid)
	{
		$surveyid = sanitize_int($surveyid);
		if (bHasSurveyPermission($surveyid, 'tokens', 'update'))
		{
		    $number2add=sanitize_int($_POST['addnumber'],1,100);
		    // find out the existing token attribute fieldnames
			$this->yii->loadHelper('database');
			$dbprefix = $this->yii->db->tablePrefix;
			$SQL = "SELECT TABLE_NAME, COLUMN_NAME
				FROM information_schema.columns
				WHERE TABLE_NAME = '{$dbprefix}tokens_$surveyid'";	
				
                //$realfieldnames = array_values($connect->MetaColumnNames($surveytable, true));
                //$realfieldnames = array_values($this->db->list_fields($surveytable));
			$realfieldnames_query = db_execute_assoc($SQL);
			$realfieldnames_result = $realfieldnames_query->readAll();
			
			$tokenfieldnames = array();
			foreach ($realfieldnames_result as $tokenfieldname)
			{
				$tokenfieldnames[] .= $tokenfieldname['COLUMN_NAME'];
			}
		    $tokenattributefieldnames=array_filter($tokenfieldnames,'filterforattributes');
		    $i=1;
		    for ($b=0;$b<$number2add;$b++)
		    {
		        while (in_array('attribute_'.$i,$tokenattributefieldnames)!==false) {
		            $i++;
		        }
		        $tokenattributefieldnames[]='attribute_'.$i;
				db_execute_assoc($this->yii->db->getSchema()->addColumn("{$dbprefix}tokens_$surveyid", 'attribute_'.$i, 'VARCHAR(255)'));
		        $fields['attribute_'.$i]=array('type' => 'VARCHAR','constraint' => '255');
		    }
		    //$dict = NewDataDictionary($connect);
		    //$sqlarray = $dict->ChangeTableSQL("{$dbprefix}tokens_$surveyid", $fields);
		    //$execresult=$dict->ExecuteSQLArray($sqlarray, false);
		    //$this->load->dbforge();
			//var_dump($tokenattributefieldnames);
			//var_dump(Yii::app()->db->getSchema()->addColumn("tokens_$surveyid", $tokenattributefieldnames, 'VARCHAR 255'));
			//return;
			//$this->dbforge->add_column("tokens_$surveyid", $fields);

			$clang=$this->controller->lang;
			$data['clang']=$this->controller->lang;
			$data['thissurvey']=getSurveyInfo($surveyid);
			$data['imageurl'] = $this->yii->getConfig('imageurl');
			$data['surveyid']=$surveyid;
			$this->controller->_getAdminHeader();
			$this->controller->render("/admin/token/tokenbar",$data);
			$this->controller->_showMessageBox(sprintf($clang->gT("%s field(s) were successfully added."),$number2add),
			"<br /><input type='button' value='".$clang->gT("Back to attribute field management.")."' onclick=\"window.open('".$this->yii->homeUrl.("/admin/tokens/sa/managetokenattributes/surveyid/$surveyid")."', '_top')\" />");
			$this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->controller->lang->gT("LimeSurvey online manual"));
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
		    foreach ($tokenattributefieldnames as $fieldname)
		    {
		        $fieldcontents.=$fieldname.'='.strip_tags($_POST['description_'.$fieldname])."\n";
		    }
		    //$updatequery = "update ".db_table_name('surveys').' set attributedescriptions='.db_quoteall($fieldcontents,true)." where sid=$surveyid";
		    //$execresult=db_execute_assoc($updatequery);
			//$this->load->model("surveys_model");
			//$this->surveys_model->updateSurvey(array("attributedescriptions"=>$fieldcontents),array("sid"=>$surveyid));
			Survey::model()->updateSurvey(array("attributedescriptions"=>$fieldcontents),"sid=$surveyid");
			$clang=$this->controller->lang;
			$data['clang']=$this->controller->lang;
			$data['thissurvey']=getSurveyInfo($surveyid);
			$data['imageurl'] = $this->yii->getConfig('imageurl');
			$data['surveyid']=$surveyid;
			$this->controller->_getAdminHeader();
			$this->controller->render("/admin/token/tokenbar",$data);
			$this->controller->_showMessageBox($clang->gT("Token attribute descriptions were successfully updated."),
					"<br /><input type='button' value='".$clang->gT("Back to attribute field management.")."' onclick=\"window.open('".$this->yii->homeUrl.("/admin/tokens/sa/managetokenattributes/surveyid/$surveyid")."', '_top')\" />");
			$this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->controller->lang->gT("LimeSurvey online manual"));
		}
	}

	/**
	 * Handle email action
	 */
	function email($surveyid=null,$tokenids=null)
	{
		if (!empty($_POST['sid']))
        {
            $surveyid = (int)$_POST['sid'];
        }

		$surveyid = sanitize_int($surveyid);

		$clang = $this->getController()->lang;
		if(!bHasSurveyPermission($surveyid, 'tokens', 'update'))
		{
			safe_die("no permissions"); // TODO Replace
		}

		if(isset($tokenids) && $tokenids=="tids") {
			$_POST("tokenids");
		    $tokenidsarray=explode("|", substr($tokenids, 1)); //Make the tokenids string into an array, and exclude the first character
		    unset($tokenids);
		    foreach($tokenidsarray as $tokenitem) {
		        if($tokenitem != "") $tokenids[]=sanitize_int($tokenitem);
		    }
		}
		Tokens_dynamic::sid($surveyid);
		$tkcount=Tokens_dynamic::model()->totalRecords($surveyid);
		Yii::app()->loadHelper('surveytranslator');

		$examplerow = array();
		$row = Tokens_dynamic::model()->find($surveyid);
		if ($row)
		$examplerow = $row->attributes;

		$tokenfields=GetTokenFieldsAndNames($surveyid,true);
    	$nrofattributes=0;

		$data['clang']=$clang;
		$thissurvey=getSurveyInfo($surveyid);
		$data['thissurvey']=$thissurvey;
		$data['imageurl'] = Yii::app()->getConfig('imageurl');
		$data['surveyid']=$surveyid;
		$data['tokenfields']=$tokenfields;
		$data['nrofattributes']=$nrofattributes;
		$data['examplerow']=$examplerow;
		$publicurl = Yii::app()->baseUrl;
		$modrewrite = Yii::app()->getConfig("modrewrite");
		$timeadjust = Yii::app()->getConfig("timeadjust");

		Yii::app()->loadHelper('/admin/htmleditor');
		Yii::app()->loadHelper('/replacements');

		if (getEmailFormat($surveyid) == 'html')
	    {
	        $ishtml=true;
	    }
	    else
	    {
	        $ishtml=false;
	    }
		$data['ishtml']=$ishtml;

	    if (@!$_POST['ok'])
	    {
			$this->getController()->_getAdminHeader();
			$this->getController()->render("/admin/token/tokenbar",$data);
			$this->getController()->render("/admin/token/email",$data);
			$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
	    }
    	else
    	{
			@$tokenid=$_POST["tokenid"];
			@$tokenids=$_POST["tokenids"];
			@$maxemails=$_POST["maxemails"];

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
			$ctresult=Tokens_dynamic::model()->ctquery($surveyid,$SQLemailstatuscondition,$tokenid,$tokenids);
			$ctcount = $ctresult->count();

	        $emresult = Tokens_dynamic::model()->emquery($surveyid,$SQLemailstatuscondition,$maxemails,$tokenid,$tokenids);
	        $emcount = $emresult->count();

	        $surveylangs = GetAdditionalLanguagesFromSurveyID($surveyid);
	        $baselanguage = GetBaseLanguageFromSurveyID($surveyid);
	        array_unshift($surveylangs,$baselanguage);

			Yii::app()->getConfig("email");
	        foreach ($surveylangs as $language)
	        {
	            $_POST['message_'.$language]=auto_unescape($_POST['message_'.$language]);
	            $_POST['subject_'.$language]=auto_unescape($_POST['subject_'.$language]);
	            if ($ishtml) $_POST['message_'.$language] = html_entity_decode($_POST['message_'.$language], ENT_QUOTES, Yii::app()->getConfig("emailcharset"));
	        }

	        $attributes=GetTokenFieldsAndNames($surveyid);
			$tokenoutput="";

	        if ($emcount > 0)
	        {
	            foreach ($emresult->readAll() as $emrow)
	            {
	                unset($fieldsarray);
                    $to=array();
                    $aEmailaddresses=explode(';',$emrow['email']);
                    foreach($aEmailaddresses as $sEmailaddress)
                    {
                        $to[]=($emrow['firstname']." ".$emrow['lastname']." <{$sEmailaddress}>");
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
			global $maildebug;
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
	                elseif (SendEmailMessage($modmessage, $modsubject, $to , $from, Yii::app()->getConfig("sitename"), $ishtml, getBounceEmail($surveyid),null,$customheaders))
	                {
	                    // Put date into sent
	                    $today = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", Yii::app()->getConfig("timeadjust"));
	                    $udequery = "UPDATE {{tokens_{$surveyid}}}\n"
	                    ."SET sent='$today' WHERE tid={$emrow['tid']}";
	                    //
	                    $uderesult = db_execute_assoc($udequery);
	                    $tokenoutput .= $clang->gT("Invitation sent to:")." {$emrow['firstname']} {$emrow['lastname']} ($to)<br />\n";
	                    if (Yii::app()->getConfig("emailsmtpdebug")==2)
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
				$data['clang']=$this->getController()->lang;
				$data['thissurvey']=getSurveyInfo($surveyid);
				$data['imageurl'] = Yii::app()->getConfig('imageurl');
				$data['surveyid']=$surveyid;
				$data['tokenoutput']=$tokenoutput;
				$this->getController()->_getAdminHeader();
				$this->getController()->render("/admin/token/tokenbar",$data);
				$this->getController()->render("/admin/token/emailpost",$data);
				$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
	        }
	        else
	        {
				$data['clang']=$this->getController()->lang;
				$data['thissurvey']=getSurveyInfo($surveyid);
				$data['imageurl'] = Yii::app()->getConfig('imageurl');
				$data['surveyid']=$surveyid;
				$this->getController()->_getAdminHeader();
				$this->getController()->render("/admin/token/tokenbar",$data);
				$this->getController()->_showMessageBox($clang->gT("Warning"),
						$clang->gT("There were no eligible emails to send. This will be because none satisfied the criteria of:")
	            ."<br/>&nbsp;<ul><li>".$clang->gT("having a valid email address")."</li>"
	            ."<li>".$clang->gT("not having been sent an invitation already")."</li>"
	            ."<li>".$clang->gT("having already completed the survey")."</li>"
	            ."<li>".$clang->gT("having a token")."</li></ul>");
				$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->getController()->lang->gT("LimeSurvey online manual"));
	        }
	    }
	}

	/**
	 * Remind Action
	 */
	function remind($surveyid)
	{
		if (!empty($_POST['sid']))
        {
            $surveyid = (int)$_POST['sid'];
        }

		if(isset($tokenids) && $tokenids=="tids") {
			$_POST("tokenids");
		    $tokenidsarray=explode("|", substr($tokenids, 1)); //Make the tokenids string into an array, and exclude the first character
		    unset($tokenids);
		    foreach($tokenidsarray as $tokenitem) {
		        if($tokenitem != "") $tokenids[]=sanitize_int($tokenitem);
		    }
		}

		$surveyid = sanitize_int($surveyid);

		@$tokenid=$_POST["tid"];
		@$tokenids=$_POST["tokenids"];
		@$maxemails=$_POST["maxemails"];

		$clang = $this->getController()->lang;
		if(!bHasSurveyPermission($surveyid, 'tokens', 'update'))
		{
			safe_die("no permissions");
		}

		Tokens_dynamic::sid($surveyid);

		$tkcount=count(Tokens_dynamic::model()->findAll());

		Yii::app()->loadHelper("surveytranslator");

		$query = Tokens_dynamic::model()->find();
		$examplerow = is_null($query) ? array() : $query->attributes;

		$tokenfields=GetTokenFieldsAndNames($surveyid,true);
    	$nrofattributes=0;


		$data['clang']=$clang;
		$thissurvey=getSurveyInfo($surveyid);
		$data['thissurvey']=$thissurvey;
		$data['imageurl'] = Yii::app()->getConfig('imageurl');
		$data['surveyid']=$surveyid;
		$data['tokenfields']=$tokenfields;
		$data['nrofattributes']=$nrofattributes;
		$data['examplerow']=$examplerow;
		$data['surveyid'] = $surveyid;
		Yii::app()->loadHelper("admin/htmleditor");
		Yii::app()->loadHelper('replacements');

		$publicurl = Yii::app()->baseUrl;
		$modrewrite = Yii::app()->getConfig("modrewrite");
		$timeadjust = Yii::app()->getConfig("timeadjust");
		$emailcharset = Yii::app()->getConfig("emailcharset");


		if (getEmailFormat($surveyid) == 'html')
	    {
	        $ishtml=true;
	    }
	    else
	    {
	        $ishtml=false;
	    }
		$data['ishtml']=$ishtml;

	    if (empty($_POST['ok']))
	    {
		    $this->getController()->_getAdminHeader();
			$this->getController()->render("/admin/token/tokenbar",$data);
			$this->getController()->render("/admin/token/remind",$data);
		 	$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $this->controller->lang->gT("LimeSurvey online manual"));

	    }
	    else
	    {
	    	//Views don't work properly when sending emails: The user will only receive feedback after the script is executed.
	    	$tokenoutput="";
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

	        $ctquery = "SELECT * FROM {{tokens_$surveyid}} WHERE (completed ='N' or completed ='') AND sent<>'' AND sent<>'N' AND token <>'' AND email <> '' $SQLemailstatuscondition $SQLremindercountcondition $SQLreminderdelaycondition";

	        if (isset($starttokenid)) {$ctquery .= " AND tid > '{$starttokenid}'";}
	        if (isset($tokenid) && $tokenid) {$ctquery .= " AND tid = '{$tokenid}'";}
	        if (isset($tokenids)) {$ctquery .= " AND tid IN (".implode(", ", $tokenids).")";}
	        $tokenoutput .= "<!-- ctquery: $ctquery -->\n";
	        $ctresult = Yii::app()->db->createCommand($ctquery)->query();
	        $ctcount = $ctresult->getRowCount();
	        //$ctfieldcount = $ctresult->FieldCount();
	        $emquery = "SELECT * FROM {{tokens_$surveyid}} WHERE (completed = 'N' or completed = '') AND sent <> 'N' and sent <>'' AND token <>'' AND EMAIL <>'' $SQLemailstatuscondition $SQLremindercountcondition $SQLreminderdelaycondition";

	        if (isset($starttokenid)) {$emquery .= " AND tid > '{$starttokenid}'";}
	        if (isset($tokenid) && $tokenid) {$emquery .= " AND tid = '{$tokenid}'";}
	        if (isset($tokenids)) {$emquery .= " AND tid IN (".implode(", ", $tokenids).")";}
	        $emquery .= " ORDER BY tid ";
	        $emresult = Yii::app()->db->createCommand($emquery)->limit(Yii::app()->getConfig("maxemails"))->query();
	        $emcount = $emresult->getRowCount();

	        $attributes=GetTokenFieldsAndNames($surveyid);
	        if ($emcount > 0)
	        {
	            $tokenoutput .= "<table width='450' align='center' >\n"
	            ."\t<tr>\n"
	            ."<td><font size='1'>\n";
	            while ($emrow = $emresult->read())
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
	                elseif (SendEmailMessage($sendmessage, $msgsubject, $to, $from, Yii::app()->getConfig('sitename'),$ishtml,getBounceEmail($surveyid),null,$customheaders))
	                {

	                    // Put date into remindersent
	                    $today = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust);
	                    $udequery = "UPDATE {{tokens_$surveyid}}\n"
	                    ."SET remindersent='$today',remindercount = remindercount+1  WHERE tid={$emrow['tid']}";
	                    //
	                    $uderesult = Yii::app()->db->createCommand($udequery)->execute();
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
	    	$this->controller->_getAdminHeader();
	    	$this->controller->render('/admin/token/tokenbar', $data);
			echo $tokenoutput;
	    	$this->controller->_getAdminFooter("http://docs.limesurvey.org", $this->controller->lang->gT("LimeSurvey online manual"));
	    }
	}

	/**
	 * Export Dialog
	 */
	function exportdialog($surveyid)
	{
		$clang = $this->controller->lang;
		$surveyid = sanitize_int($surveyid);
		if (bHasSurveyPermission($surveyid, 'tokens','export') )//EXPORT FEATURE SUBMITTED BY PIETERJAN HEYSE
		{
			if (!empty($_POST['submit']))
		    {
		    	Yii::app()->loadHelper("export");
				tokens_export($surveyid);
			}
		    $langquery = "SELECT language FROM {{tokens_$surveyid}} group by language";
		    $langresult = Yii::app()->db->createCommand($langquery)->query();
			$data['resultr'] = $langresult->read();

			$data['clang']=$this->controller->lang;
			$thissurvey=getSurveyInfo($surveyid);
			$data['thissurvey']=$thissurvey;
			$data['imageurl'] = Yii::app()->getConfig('imageurl');
			$data['surveyid']=$surveyid;


			$this->controller->_getAdminHeader();
			$this->controller->render("/admin/token/tokenbar",$data);
			$this->controller->render("/admin/token/exportdialog",$data);
			$this->controller->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
		}
	}

	/**
	 * Performs a ldap import
	 *
	 * @access public
	 * @param int $surveyid
	 * @return void
	 */
	public function importldap($surveyid)
	{
		$surveyid = (int) $surveyid;
		$clang = $this->controller->lang;

		Yii::app()->loadConfig('ldap');
		Yii::app()->loadHelper('ldap');

		$tokenoutput = '';
		if (!bHasSurveyPermission($surveyid, 'tokens', 'create'))
			show_error('access denied');

		if (empty($_POST['submit']))
		{
			$tokenoutput .= "\t<div class='header ui-widget-header'>".$clang->gT("Upload LDAP entries")."</div>\n";
			$tokenoutput .= self::formldap(null, $surveyid);
			$tokenoutput .= "<div class='messagebox ui-corner-all'>\n"
				."\t<div class='header ui-widget-header'>".$clang->gT("Note:")."</div><br />\n"
				.$clang->gT("LDAP queries are defined by the administrator in the config-ldap.php file")."\n"
				."</div>\n";
		}
		else
		{
			$ldap_queries = Yii::app()->getConfig('ldap_queries');
			$ldap_server = Yii::app()->getConfig('ldap_server');

			$duplicatelist=array();
			$invalidemaillist=array();
			$tokenoutput .= "\t<tr><td colspan='2' height='4'><strong>"
				.$clang->gT("Uploading LDAP Query")."</strong></td></tr>\n"
				."\t<tr><td align='center'>\n";
			$ldapq=$_POST['ldapQueries']; // the ldap query id

			$ldap_server_id=$ldap_queries[$ldapq]['ldapServerId'];
			$ldapserver=$ldap_server[$ldap_server_id]['server'];
			$ldapport=$ldap_server[$ldap_server_id]['port'];
			if (isset($ldap_server[$ldap_server_id]['encoding']) &&
				$ldap_server[$ldap_server_id]['encoding'] != 'utf-8' &&
				$ldap_server[$ldap_server_id]['encoding'] != 'UTF-8')
			{
				$ldapencoding=$ldap_server[$ldap_server_id]['encoding'];
			}
			else
			{
				$ldapencoding='';
			}

			// define $attrlist: list of attributes to read from users' entries
			$attrparams = array('firstname_attr','lastname_attr',
			'email_attr','token_attr', 'language');

			$aTokenAttr=GetAttributeFieldNames($surveyid);
			foreach ($aTokenAttr as $thisattrfieldname)
			{
				$attridx=substr($thisattrfieldname,10); // the 'attribute_' prefix is 10 chars long
				$attrparams[] = "attr".$attridx;
			}

			foreach ($attrparams as $id => $attr) {
				if (array_key_exists($attr,$ldap_queries[$ldapq]) &&
				$ldap_queries[$ldapq][$attr] != '') {
					$attrlist[]=$ldap_queries[$ldapq][$attr];
				}
			}

			// Open connection to server
			$ds = ldap_getCnx($ldap_server_id);

			if ($ds) {
				// bind to server
				$resbind=ldap_bindCnx($ds, $ldap_server_id);

				if ($resbind) {
					$ResArray=array();
					$resultnum=ldap_doTokenSearch($ds, $ldapq, $ResArray, $surveyid);
					$xz = 0; // imported token count
					$xv = 0; // meet minim requirement count
					$xy = 0; // check for duplicates
					$duplicatecount = 0; // duplicate tokens skipped count
					$invalidemailcount = 0;

					if ($resultnum >= 1) {
						foreach ($ResArray as $responseGroupId => $responseGroup) {
							for($j = 0;$j < $responseGroup['count']; $j++) {
								// first let's initialize everything to ''
								$myfirstname='';
								$mylastname='';
								$myemail='';
								$mylanguage='';
								$mytoken='';
								$myattrArray=array();

								// The first 3 attrs MUST exist in the ldap answer
								// ==> send PHP notice msg to apache logs otherwise
								$meetminirequirements=true;
								if (isset($responseGroup[$j][$ldap_queries[$ldapq]['firstname_attr']]) &&
									isset($responseGroup[$j][$ldap_queries[$ldapq]['lastname_attr']])
									)
								{
									// minimum requirement for ldap
									// * at least a firstanme
									// * at least a lastname
									// * if filterblankemail is set (default): at least an email address
									$myfirstname = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['firstname_attr']]);
									$mylastname = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['lastname_attr']]);
									if (isset($responseGroup[$j][$ldap_queries[$ldapq]['email_attr']]))
									{
										$myemail = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['email_attr']]);
										$myemail= sanitize_email($myemail);
										++$xv;
									}
									elseif ($filterblankemail !==true)
									{
										$myemail = '';
										++$xv;
									}
									else
									{
										$meetminirequirements=false;
									}
								}
								else
								{
									$meetminirequirements=false;
								}

								// The following attrs are optionnal
								if ( isset($responseGroup[$j][$ldap_queries[$ldapq]['token_attr']]) ) $mytoken = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['token_attr']]);

								foreach ($aTokenAttr as $thisattrfieldname)
								{
									$attridx=substr($thisattrfieldname,10); // the 'attribute_' prefix is 10 chars long
									if ( isset($ldap_queries[$ldapq]['attr'.$attridx]) &&
										isset($responseGroup[$j][$ldap_queries[$ldapq]['attr'.$attridx]]) ) $myattrArray[$attridx] = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['attr'.$attridx]]);
								}

								if ( isset($responseGroup[$j][$ldap_queries[$ldapq]['language']]) ) $mylanguage = ldap_readattr($responseGroup[$j][$ldap_queries[$ldapq]['language']]);

								// In case Ldap Server encoding isn't UTF-8, let's translate
								// the strings to UTF-8
								if ($ldapencoding != '')
								{
									$myfirstname = @mb_convert_encoding($myfirstname,"UTF-8",$ldapencoding);
									$mylastname = @mb_convert_encoding($mylastname,"UTF-8",$ldapencoding);
									foreach ($aTokenAttr as $thisattrfieldname)
									{
										$attridx=substr($thisattrfieldname,10); // the 'attribute_' prefix is 10 chars long
										@mb_convert_encoding($myattrArray[$attridx],"UTF-8",$ldapencoding);
									}

								}

								// Now check for duplicates or bad formatted email addresses
								$dupfound=false;
								$invalidemail=false;
								if ($filterduplicatetoken)
								{
									$dupquery = "SELECT firstname, lastname from {{tokens_$surveyid}} where email=".db_quoteall($myemail)." and firstname=".db_quoteall($myfirstname)." and lastname=".db_quoteall($mylastname);
									$dupresult = Yii::app()->db->createCommand($dupquery)->query();
									if ( $dupresult->getRowCount() > 0)
									{
										$dupfound = true;
										$duplicatelist[]=$myfirstname." ".$mylastname." (".$myemail.")";
										$xy++;

									}
								}
								if ($filterblankemail && $myemail=='')
								{
									$invalidemail=true;
									$invalidemaillist[]=$myfirstname." ".$mylastname." ( )";
								}
								elseif ($myemail!='' && !validate_email($myemail))
								{
									$invalidemail=true;
									$invalidemaillist[]=$myfirstname." ".$mylastname." (".$myemail.")";
								}

								if ($invalidemail)
								{
									++$invalidemailcount;
								}
								elseif ($dupfound)
								{
									++$duplicatecount;
								}
								elseif ($meetminirequirements===true)
								{
									// No issue, let's import
									$iq = "INSERT INTO {{tokens_$surveyid}} \n"
									. "(firstname, lastname, email, emailstatus, token, language";

									foreach ($aTokenAttr as $thisattrfieldname)
									{
										$attridx=substr($thisattrfieldname,10); // the 'attribute_' prefix is 10 chars long
										if (!empty($myattrArray[$attridx])) {$iq .= ", $thisattrfieldname";}
									}
									$iq .=") \n"
									. "VALUES (".db_quoteall($myfirstname).", ".db_quoteall($mylastname).", ".db_quoteall($myemail).", 'OK', ".db_quoteall($mytoken).", ".db_quoteall($mylanguage)."";

									foreach ($aTokenAttr as $thisattrfieldname)
									{
										$attridx=substr($thisattrfieldname,10); // the 'attribute_' prefix is 10 chars long
										if (!empty($myattrArray[$attridx])) {$iq .= ", ".db_quoteall($myattrArray[$attridx]).""; }// dbquote_all encloses str with quotes
									}
									$iq .= ")";
									$ir = Yii::app()->db->createCommand($iq)->execute();
									if (!$ir) $duplicatecount++;
									$xz++;
									// or safe_die ("Couldn't insert line<br />\n$buffer<br />\n".htmlspecialchars($connect->ErrorMsg())."<pre style='text-align: left'>$iq</pre>\n");
								}
							} // End for each entry
						} // End foreach responseGroup
					} // End of if resnum >= 1

					if ($xz != 0)
					{
						$tokenoutput .= "<span class='successtitle'>".$clang->gT("Success")."</span><br /><br />\n";
					}
					else
					{
						$tokenoutput .= "<font color='red'>".$clang->gT("Failed")."</font><br /><br />\n";
					}
					$message = "$resultnum ".$clang->gT("Results from LDAP Query").".<br />\n";
					$message .= "$xv ".$clang->gT("Records met minumum requirements").".<br />\n";
					$message .= "$xz ".$clang->gT("Records imported").".<br />\n";
					$message .= "$xy ".$clang->gT("Duplicate records removed");
					$message .= " [<a href='#' onclick='$(\"#duplicateslist\").toggle();'>".$clang->gT("List")."</a>]";
					$message .= "<div class='badtokenlist' id='duplicateslist' style='display: none;'>";
					foreach($duplicatelist as $data) {
						$message .= "<li>$data</li>\n";
					}
					$message .= "</div>";
					$message .= "<br />\n";
					$message .= sprintf($clang->gT("%s records with invalid email address removed"),$invalidemailcount);
					$message .= " [<a href='#' onclick='$(\"#invalidemaillist\").toggle();'>".$clang->gT("List")."</a>]";
					$message .= "<div class='badtokenlist' id='invalidemaillist' style='display: none;'>";
					foreach($invalidemaillist as $data) {
						$message .= "<li>$data</li>\n";
					}
					$message .= "</div>";
					$message .= "<br />\n";
					$tokenoutput .= "<i>$message</i><br />\n";
				}
				else {
					$errormessage="<strong><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("Can't bind to the LDAP directory")."</strong>\n";
					$tokenoutput .= self::formldap($errormessage, $surveyid);
				}
				@ldap_close($ds);
			}
			else {
				$errormessage="<strong><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("Can't connect to the LDAP directory")."</strong>\n";
				$tokenoutput .= self::formldap($errormessage, $surveyid);
			}
		}

		$this->controller->_getAdminHeader();
		$this->controller->render('/admin/token/tokenbar', array('thissurvey' => getSurveyInfo($surveyid), 'imageurl' => Yii::app()->getConfig('imageurl'), 'clang' => $clang, 'surveyid' => $surveyid));
		echo $tokenoutput;
		$this->controller->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
	}

	/**
	 * Ldap submission form
	 */
	function formldap($error=null, $surveyid)
	{
		$ldap_queries = Yii::app()->getConfig('ldap_queries');
		$clang = $this->controller->lang;

		$tokenoutput = '';
		if ($error) {$tokenoutput .= $error . "<br /><br />\n";}

		if (!function_exists('ldap_connect'))
		{
			$tokenoutput .= '<p>';
			$tokenoutput .= $clang->gT('Sorry, but the LDAP module is missing in your PHP configuration.');
			$tokenoutput .= '<br />';
		}

		elseif (! isset($ldap_queries) || ! is_array($ldap_queries) || count($ldap_queries) == 0) {
			$tokenoutput .= '<br />';
			$tokenoutput .= $clang->gT('LDAP is disabled or no LDAP query defined.');
			$tokenoutput .= '<br /><br /><br />';
		}
		else {
			$tokenoutput .= "<form method='post' action='" . $this->controller->createUrl("admin/tokens/sa/importldap/surveyid/$surveyid") . "' method='post'>";
			$tokenoutput .= '<p>';
			$tokenoutput .= $clang->gT("Select the LDAP query you want to run:")."<br />";
			$tokenoutput .= "<select name='ldapQueries' style='length=35'><br />";
			foreach ($ldap_queries as $q_number => $q) {
				$tokenoutput .= " <option value=".$q_number.">".$q['name']."</option>";
			}
			$tokenoutput .= "</select><br />";
			$tokenoutput .= '</p>';
			$tokenoutput .= "<p><label for='filterblankemail'>".$clang->gT("Filter blank email addresses:")."</label><input type='checkbox' name='filterblankemail' checked='checked' /></p>\n"
			. "<p><label for='filterduplicatetoken'>".$clang->gT("Filter duplicate records:")."</label><input type='checkbox' name='filterduplicatetoken' checked='checked' /></p>\n";
			$tokenoutput .= "<input type='hidden' name='sid' value='$surveyid' />";
			$tokenoutput .= "<input type='hidden' name='subaction' value='uploadldap' />";
			$tokenoutput .= "<p><input type='submit' name='submit' /></p>";
			$tokenoutput .= '</form></font>';
		}

		return $tokenoutput;
	}

	/**
	 * import from csv
	 */
	function import($surveyid)
	{
		$surveyid = (int) $surveyid;

		if (!bHasSurveyPermission($surveyid, 'tokens', 'create'))
			show_error('access denied');

		$this->controller->_js_admin_includes('scripts/tokens.js');

		$clang = $this->controller->lang;

		$encodingsarray = array(
			"armscii8"=>$clang->gT("ARMSCII-8 Armenian")
			,"ascii"=>$clang->gT("US ASCII")
			,"auto"=>$clang->gT("Automatic")
			,"big5"=>$clang->gT("Big5 Traditional Chinese")
			,"binary"=>$clang->gT("Binary pseudo charset")
			,"cp1250"=>$clang->gT("Windows Central European")
			,"cp1251"=>$clang->gT("Windows Cyrillic")
			,"cp1256"=>$clang->gT("Windows Arabic")
			,"cp1257"=>$clang->gT("Windows Baltic")
			,"cp850"=>$clang->gT("DOS West European")
			,"cp852"=>$clang->gT("DOS Central European")
			,"cp866"=>$clang->gT("DOS Russian")
			,"cp932"=>$clang->gT("SJIS for Windows Japanese")
			,"dec8"=>$clang->gT("DEC West European")
			,"eucjpms"=>$clang->gT("UJIS for Windows Japanese")
			,"euckr"=>$clang->gT("EUC-KR Korean")
			,"gb2312"=>$clang->gT("GB2312 Simplified Chinese")
			,"gbk"=>$clang->gT("GBK Simplified Chinese")
			,"geostd8"=>$clang->gT("GEOSTD8 Georgian")
			,"greek"=>$clang->gT("ISO 8859-7 Greek")
			,"hebrew"=>$clang->gT("ISO 8859-8 Hebrew")
			,"hp8"=>$clang->gT("HP West European")
			,"keybcs2"=>$clang->gT("DOS Kamenicky Czech-Slovak")
			,"koi8r"=>$clang->gT("KOI8-R Relcom Russian")
			,"koi8u"=>$clang->gT("KOI8-U Ukrainian")
			,"latin1"=>$clang->gT("cp1252 West European")
			,"latin2"=>$clang->gT("ISO 8859-2 Central European")
			,"latin5"=>$clang->gT("ISO 8859-9 Turkish")
			,"latin7"=>$clang->gT("ISO 8859-13 Baltic")
			,"macce"=>$clang->gT("Mac Central European")
			,"macroman"=>$clang->gT("Mac West European")
			,"sjis"=>$clang->gT("Shift-JIS Japanese")
			,"swe7"=>$clang->gT("7bit Swedish")
			,"tis620"=>$clang->gT("TIS620 Thai")
			,"ucs2"=>$clang->gT("UCS-2 Unicode")
			,"ujis"=>$clang->gT("EUC-JP Japanese")
			,"utf8"=>$clang->gT("UTF-8 Unicode"));

		$tokenoutput = '';

		if (!empty($_POST['submit']))
		{
			if (isset($_POST['csvcharset']) && $_POST['csvcharset'])  //sanitize charset - if encoding is not found sanitize to 'auto'
			{
				$uploadcharset=$_POST['csvcharset'];
				if (!array_key_exists($uploadcharset,$encodingsarray)) {$uploadcharset='auto';}
				$filterduplicatetoken=(isset($_POST['filterduplicatetoken']) && $_POST['filterduplicatetoken']=='on');
				$filterblankemail=(isset($_POST['filterblankemail']) && $_POST['filterblankemail']=='on');
			}
			$attrfieldnames=GetAttributeFieldnames($surveyid);
			$duplicatelist=array();
			$invalidemaillist=array();
			$invalidformatlist=array();
			$tokenoutput .= "\t<div class='header ui-widget-header'>".$clang->gT("Token file upload")."</div>\n"
			 ."\t<div class='messagebox ui-corner-all'>\n";

			$the_path = Yii::app()->getConfig('tempdir');

			$the_file_name = $_FILES['the_file']['name'];
			$the_file = $_FILES['the_file']['tmp_name'];
			$the_full_file_path = $the_path."/".$the_file_name;

			if (!@move_uploaded_file($the_file, $the_full_file_path))
			{
				$errormessage="<div class='warningheader'>".$clang->gT("Error")."</div><p>".$clang->gT("Upload file not found. Check your permissions and path ({$the_full_file_path}) for the upload directory")."</p>\n";
				$tokenoutput .= self::form_upload_csv(null, $encodingsarray, $clang, $surveyid);
			}
			else
			{
				$tokenoutput .= "<div class='successheader'>".$clang->gT("Uploaded CSV file successfully")."</div><br />\n";
				$xz = 0; $recordcount = 0; $xv = 0;
				// This allows to read file with MAC line endings too
				@ini_set('auto_detect_line_endings', true);
				// open it and trim the ednings
				$tokenlistarray = file($the_full_file_path);
				$baselanguage=GetBaseLanguageFromSurveyID($surveyid);
				if (!isset($tokenlistarray))
				{
					$tokenoutput .= "<div class='warningheader'>".$clang->gT("Failed to open the uploaded file!")."</div><br />\n";
				}
				if (!isset($_POST['filterduplicatefields']) || (isset($_POST['filterduplicatefields']) && count($_POST['filterduplicatefields'])==0))
				{
					$filterduplicatefields=array('firstname','lastname','email');
				} else {
					$filterduplicatefields=$_POST['filterduplicatefields'];
				}
				$separator = returnglobal('separator');
				foreach ($tokenlistarray as $buffer)
				{
					$buffer=@mb_convert_encoding($buffer,"UTF-8",$uploadcharset);
					$firstname = ""; $lastname = ""; $email = ""; $emailstatus="OK"; $token = ""; $language=""; $attribute1=""; $attribute2=""; //Clear out values from the last path, in case the next line is missing a value
					if ($recordcount==0)
					{
						// Pick apart the first line
						$buffer=removeBOM($buffer);
						$allowedfieldnames=array('firstname','lastname','email','emailstatus','token','language', 'validfrom', 'validuntil', 'usesleft');
						$allowedfieldnames=array_merge($attrfieldnames,$allowedfieldnames);

						switch ($separator) {
							case 'comma':
								$separator = ',';
								break;
							case 'semicolon':
								$separator = ';';
								break;
							default:
								$comma = substr_count($buffer,',');
								$semicolon = substr_count($buffer,';');
								if ($semicolon>$comma) $separator = ';'; else $separator = ',';
						}
						$firstline = convertCSVRowToArray($buffer,$separator,'"');
						$firstline=array_map('trim',$firstline);
						$ignoredcolumns=array();
						//now check the first line for invalid fields
						foreach ($firstline as $index=>$fieldname)
						{
							$firstline[$index] = preg_replace("/(.*) <[^,]*>$/","$1",$fieldname);
							$fieldname = $firstline[$index];
							if (!in_array($fieldname,$allowedfieldnames))
							{
								$ignoredcolumns[]=$fieldname;
							}
						}
						if (!in_array('firstname',$firstline) || !in_array('lastname',$firstline) || !in_array('email',$firstline))
						{
							$tokenoutput .= "<div class='warningheader'>".$clang->gT("Error: Your uploaded file is missing one or more of the mandatory columns: 'firstname', 'lastname' or 'email'")."</div><br />";
							$recordcount=count($tokenlistarray);
							break;
						}

					}
					else
					{

						$line = convertCSVRowToArray($buffer,$separator,'"');

						if (count($firstline)!=count($line))
						{
							$invalidformatlist[]=$recordcount;
							$recordcount++;
							continue;
						}
						$writearray=array_combine($firstline,$line);

						//kick out ignored columns
						foreach ($ignoredcolumns  as $column)
						{
							unset($writearray[$column]);
						}
						$dupfound=false;
						$invalidemail=false;

						if ($filterduplicatetoken!=false)
						{
							$dupquery = "SELECT tid from {{tokens_$surveyid}} where 1=1";
							foreach($filterduplicatefields as $field)
							{
								if (isset($writearray[$field])) {
									$dupquery.=' and '.db_quote_id($field).' = '.db_quoteall($writearray[$field]);
								}
							}
							$dupresult = Yii::app()->db->createCommand($dupquery)->query();
							if ( $dupresult->getRowCount() > 0)
							{
								$dupfound = true;
								$duplicatelist[]=$writearray['firstname']." ".$writearray['lastname']." (".$writearray['email'].")";
							}
						}


						$writearray['email'] = trim($writearray['email']);

						//treat blank emails
						if ($filterblankemail && $writearray['email']=='')
						{
							$invalidemail=true;
							$invalidemaillist[]=$line[0]." ".$line[1]." ( )";
						}
						if  ($writearray['email']!='')
						{
							$aEmailAddresses=explode(';',$writearray['email']);
							foreach ($aEmailAddresses as $sEmailaddress)
							{
								if (!validate_email($sEmailaddress))
								{
									$invalidemail=true;
									$invalidemaillist[]=$line[0]." ".$line[1]." (".$line[2].")";
								}
							}
						}

						if (!isset($writearray['token'])) {
							$writearray['token'] = '';
						} else {
							$writearray['token'] = sanitize_token($writearray['token']);
						}

						if (!$dupfound && !$invalidemail)
						{
							if (!isset($writearray['emailstatus']) || $writearray['emailstatus']=='') $writearray['emailstatus'] = "OK";
							if (!isset($writearray['language']) || $writearray['language'] == "") $writearray['language'] = $baselanguage;
							if (isset($writearray['validfrom']) && trim($writearray['validfrom']=='')){ unset($writearray['validfrom']);}
							if (isset($writearray['validuntil']) && trim($writearray['validuntil']=='')){ unset($writearray['validuntil']);}

							// sanitize it before writing into table
							$sanitizedArray = array_map('db_quoteall',array_values($writearray));

							$iq = "INSERT INTO {{tokens_$surveyid}} \n"
							. "(".implode(',',array_keys($writearray)).") \n"
							. "VALUES (".implode(",",$sanitizedArray).")";
							$ir = Yii::app()->db->createCommand($iq)->execute();

							if (!$ir)
							{
								$duplicatelist[]=$writearray['firstname']." ".$writearray['lastname']." (".$writearray['email'].")";
							} else {
								$xz++;
							}
						}
						$xv++;
					}
					$recordcount++;
				}
				$recordcount = $recordcount-1;
				if ($xz != 0)
				{
					$tokenoutput .= "<div class='successheader'>".$clang->gT("Successfully created token entries")."</div><br />\n";
				} else {
					$tokenoutput .= "<div class='warningheader'>".$clang->gT("Failed to create token entries")."</div>\n";
				}
				$message = '<ul><li>'.sprintf($clang->gT("%s records in CSV"),$recordcount)."</li>\n";
				$message .= '<li>'.sprintf($clang->gT("%s records met minumum requirements"),$xv)."</li>\n";
				$message .= '<li>'.sprintf($clang->gT("%s records imported"),$xz)."</li></ul>\n";


				if (count($duplicatelist)>0 || count($invalidformatlist)>0 || count($invalidemaillist)>0)
				{

					$message .="<div class='warningheader'>".$clang->gT('Warnings')."</div><ul>";
					if (count($duplicatelist)>0)
					{
						$message .= '<li>'.sprintf($clang->gT("%s duplicate records removed"),count($duplicatelist));
						$message .= " [<a href='#' onclick='$(\"#duplicateslist\").toggle();'>".$clang->gT("List")."</a>]";
						$message .= "<div class='badtokenlist' id='duplicateslist' style='display: none;'><ul>";
						foreach($duplicatelist as $data) {
							$message .= "<li>$data</li>\n";
						}
						$message .= "</ul></div>";
						$message .= "</li>\n";
					}

					if (count($invalidformatlist)>0)
					{
						$message .= '<li>'.sprintf($clang->gT("%s lines had a mismatching number of fields."),count($invalidformatlist));
						$message .= " [<a href='#' onclick='$(\"#invalidformatlist\").toggle();'>".$clang->gT("List")."</a>]";
						$message .= "<div class='badtokenlist' id='invalidformatlist' style='display: none;'><ul>";
						foreach($invalidformatlist as $data) {
							$message .= "<li>Line $data</li>\n";
						}
					}

					if (count($invalidemaillist)>0)
					{
						$message .= '<li>'.sprintf($clang->gT("%s records with invalid email address removed"),count($invalidemaillist));
						$message .= " [<a href='#' onclick='$(\"#invalidemaillist\").toggle();'>".$clang->gT("List")."</a>]";
						$message .= "<div class='badtokenlist' id='invalidemaillist' style='display: none;'><ul>";
						foreach($invalidemaillist as $data) {
							$message .= "<li>$data</li>\n";
						}
					}
					$message .= "</ul>";
				}
				$message .= "</div>";

				$tokenoutput .= "$message<br />\n";
				unlink($the_full_file_path);
			}
			$tokenoutput .= "</div>\n";
		}
		else
		{
			$tokenoutput = self::form_upload_csv(null, $encodingsarray, $clang, $surveyid);
			$tokenoutput .= "<div class='messagebox ui-corner-all'>\n"
			."<div class='header ui-widget-header'>".$clang->gT("CSV input format")."</div>\n"
			."<p>".$clang->gT("File should be a standard CSV (comma delimited) file with optional double quotes around values (default for OpenOffice and Excel). The first line must contain the field names. The fields can be in any order.").'</p><span style="font-weight:bold;">'.$clang->gT("Mandatory fields:")."</span> firstname,lastname,email<br />"
			.'<span style="font-weight:bold;">'.$clang->gT('Optional fields:')."</span> emailstatus, token, language, validfrom, validuntil, attribute_1, attribute_2, attribute_3, usesleft, ... ."
			."</div>\n";
		}

		$this->controller->_getAdminHeader();
		$this->controller->render('/admin/token/tokenbar', array('thissurvey' => getSurveyInfo($surveyid), 'imageurl' => Yii::app()->getConfig('imageurl'), 'clang' => $clang, 'surveyid' => $surveyid));
		echo $tokenoutput;
		$this->controller->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
	}

	function form_upload_csv($error = null, $encodingsarray = array(), $clang = null, $surveyid)
	{
		$tokenoutput = '';
		if ($error) {$tokenoutput .= $error . "<br /><br />\n";}
		asort($encodingsarray);
		$charsetsout='';
		foreach  ($encodingsarray as $charset=>$title)
		{
			$charsetsout.="<option value='$charset' ";
			if ($charset=='auto') {$charsetsout.=" selected ='selected'";}
			$charsetsout.=">$title ($charset)</option>";
		}
		$separator = returnglobal('separator');
		if (empty($separator) || $separator == 'auto') $selected = " selected = 'selected'"; else $selected = '';
		$separatorout = "<option value='auto'$selected>".$clang->gT("(Autodetect)")."</option>";
		if ($separator == 'comma') $selected = " selected = 'selected'"; else $selected = '';
		$separatorout .= "<option value='comma'$selected>".$clang->gT("Comma")."</option>";
		if ($separator == 'semicolon') $selected = " selected = 'selected'"; else $selected = '';
		$separatorout .= "<option value='semicolon'$selected>".$clang->gT("Semicolon")."</option>";
		$tokenoutput .= "<form id='tokenimport' enctype='multipart/form-data' action='" . $this->controller->createUrl("admin/tokens/sa/import/surveyid/$surveyid") . "' method='post'><ul>\n"
			. "<li><label for='the_file'>".$clang->gT("Choose the CSV file to upload:")."</label><input type='file' id='the_file' name='the_file' size='35' /></li>\n"
			. "<li><label for='csvcharset'>".$clang->gT("Character set of the file:")."</label><select id='csvcharset' name='csvcharset' size='1'>$charsetsout</select></li>\n"
			. "<li><label for='separator'>".$clang->gT("Separator used:")."</label><select id='separator' name='separator' size='1'>"
			. $separatorout
			. "</select></li>\n"
			. "<li><label for='filterblankemail'>".$clang->gT("Filter blank email addresses:")."</label><input type='checkbox' id='filterblankemail' name='filterblankemail' checked='checked' /></li>\n"
			. "<li><label for='filterduplicatetoken'>".$clang->gT("Filter duplicate records:")."</label><input type='checkbox' id='filterduplicatetoken' name='filterduplicatetoken' checked='checked' /></li>"
			. "<li id='lifilterduplicatefields'><label for='filterduplicatefields[]'>".$clang->gT("Duplicates are determined by:")."</label>"
			. "<select id='filterduplicatefields[]' name='filterduplicatefields[]' multiple='multiple' size='5'>"
			. "<option selected='selected'>firstname</option>"
			. "<option selected='selected'>lastname</option>"
			. "<option selected='selected'>email</option>"
			. "<option>token</option>"
			. "<option>language</option>";
		$aTokenAttr=GetAttributeFieldNames($surveyid);
		foreach ($aTokenAttr as $thisattrfieldname)
		{
			$tokenoutput.="<option>$thisattrfieldname</option>";
		}

		$tokenoutput .= "</select> "
			. "</li></ul>\n"
			. "<p><input class='submit' type='submit' name='submit' value='".$clang->gT("Upload")."' />\n"
			. "<input type='hidden' name='subaction' value='upload' />\n"
			. "<input type='hidden' name='sid' value='$surveyid' />\n"
			. "</p></form>\n\n";
		return $tokenoutput;
	}
	/**
	 * Generate tokens
	 */
	function tokenify($surveyid)
	{
		   function _post($d) {
    		if (isset($_POST[$d])) {
    			return $_POST[$d];
    		}else{
    			return FALSE;
    		}
    	}
		$surveyid = sanitize_int($surveyid);
		$clang = Yii::app()->lang;
		$data['clang']=Yii::app()->lang;
		$data['thissurvey']=getSurveyInfo($surveyid);
		$data['imageurl'] = Yii::app()->getConfig('imageurl');
		$data['surveyid']=$surveyid;
		Tokens_dynamic::sid($surveyid);

		if (bHasSurveyPermission($surveyid, 'tokens', 'update'))
		{
		    if (!_post('ok'))
		    {

				$this->getController()->_getAdminHeader();
				$this->getController()->render("/admin/token/tokenbar",$data);
				$this->getController()->_showMessageBox($clang->gT("Create tokens"),
						$clang->gT("Clicking yes will generate tokens for all those in this token list that have not been issued one. Is this OK?")."<br /><br />\n"
		        ."<input type='submit' value='"
		        .$clang->gT("Yes")."' onclick=\"".get2post($this->getController()->createUrl("admin/tokens/sa/tokenify/surveyid/$surveyid")."?action=tokens&amp;sid=$surveyid&amp;subaction=tokenify&amp;ok=Y")."\" />\n"
		        ."<input type='submit' value='"
		        .$clang->gT("No")."' onclick=\"window.open('".$this->getController()->createUrl("admin/tokens/sa/index/surveyid/$surveyid")."', '_top')\" />\n"
		        ."<br />\n");
				$this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));

		    }
		    else
		    {
		        //get token length from survey settings
                $newtokencount=Tokens_dynamic::model()->createTokens($surveyid);
		        $message=str_replace("{TOKENCOUNT}", $newtokencount, $clang->gT("{TOKENCOUNT} tokens have been created"));
				$this->getController()->_getAdminHeader();
				$this->getController()->render("/admin/token/tokenbar",$data);
				$this->getController()->_showMessageBox($clang->gT("Create tokens"),$message);
				$this->getController()->_getAdminFooter("http://docs.limesurvey.org", Yii::app()->lang->gT("LimeSurvey online manual"));
		    }
		}
	}

	/**
	 * Remove Token Database
	 */
	function kill($surveyid)
	{
		$surveyid = sanitize_int($surveyid);
		$clang = $this->getController()->lang; 
		$data['clang']=$this->getController()->lang;
		$data['thissurvey']=getSurveyInfo($surveyid);

		$data['imageurl'] = Yii::app()->getConfig("imageurl");
		$data['surveyid']=$surveyid;

		if (bHasSurveyPermission($surveyid, 'surveyactivation', 'update'))
		{
		    $date = date('YmdHis');
		    //$tokenoutput .= "<div class='header ui-widget-header'>".$clang->gT("Delete Tokens Table")."</div>\n"
		    //."<div class='messagebox ui-corner-all'>\n";
		    // ToDo: Just delete it if there is no token in the table
		    if (!isset($_POST['ok']) || !$_POST['ok'])
		    {
                        $this->getController()->_getAdminHeader();
			$this->getController()->render("/admin/token/tokenbar",$data);   
			$this->getController()->_showMessageBox($clang->gT("Delete Tokens Table"),$clang->gT("If you delete this table tokens will no longer be required to access this survey.")."<br />".$clang->gT("A backup of this table will be made if you proceed. Your system administrator will be able to access this table.")."<br />\n"
		        ."( \"old_tokens_{$surveyid}_$date\" )<br /><br />\n"
		        ."<input type='submit' value='"
		        .$clang->gT("Delete Tokens")."' onclick=\"".get2post($this->getController()->createUrl("admin/tokens/sa/kill/surveyid/$surveyid")."?action=tokens&amp;sid=$surveyid&amp;subaction=kill&amp;ok=surething")."\" />\n"
		        ."<input type='submit' value='"
		        .$clang->gT("Cancel")."' onclick=\"window.open('".$this->getController()->createUrl("admin/tokens/sa/index/surveyid/$surveyid")."', '_top')\" />\n");
				
			$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual")); 
		    }
		    elseif (isset($_POST['ok']) && $_POST['ok'] == "surething")
		    {
		        $sDBPrefix = Yii::app()->db->tablePrefix;
		        $oldtable = "tokens_$surveyid";
 		        $newtable = "old_tokens_{$surveyid}_$date";
				
			Yii::app()->db->createCommand()->renameTable($sDBPrefix.$oldtable, $sDBPrefix.$newtable);
				
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

				$this->getController()->_getAdminHeader();
		
				$this->getController()->render("/admin/token/tokenbar",$data);  
				$this->getController()->_showMessageBox($clang->gT("Delete Tokens Table"),'<br />'.$clang->gT("The tokens table has now been removed and tokens are no longer required to access this survey.")."<br /> ".$clang->gT("A backup of this table has been made and can be accessed by your system administrator.")."<br />\n"
		        ."(\"old_tokens_{$surveyid}_$date\")"."<br /><br />\n"
		        ."<input type='submit' value='"
		        .$clang->gT("Main Admin Screen")."' onclick=\"window.open('".Yii::app()->createURL("admin/")."', '_top')\" />");

				$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
		    }  
		}
	}

	function bouncesettings($surveyid)
	{
		$surveyid = sanitize_int($surveyid);
		$clang = $this->controller->lang;
		$data['clang']=$clang;
		$data['thissurvey'] = $data['settings']=getSurveyInfo($surveyid);
		$data['imageurl'] = Yii::app()->getConfig('imageurl');
		$data['surveyid']=$surveyid;

		if(!empty($_POST))
		{
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

			$survey = Survey::model()->findByAttributes(array('sid' => $surveyid));
			foreach ($fieldvalue as $k => $v)
				$survey->$k = $v;
			$survey->save();

			//$connect->AutoExecute("{$dbprefix}surveys", $fieldvalue, 2,"sid=$surveyid",get_magic_quotes_gpc());
			$this->controller->_getAdminHeader();
			$this->controller->render("/admin/token/tokenbar",$data);
		    $this->controller->_showMessageBox($clang->gT("Bounce settings"),$clang->gT("Bounce settings have been saved."),"successheader");
			$this->controller->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
		}
		else
		{
			$this->controller->_getAdminHeader();
			$this->controller->render("/admin/token/tokenbar",$data);
			$this->controller->render("/admin/token/bounce",$data);
			$this->controller->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
		}
	}

	/**
	 * Handle token form for addnew/edit actions
	 */
	function _handletokenform($surveyid,$subaction,$tokenid="")
	{
		$clang=$this->controller->lang;

		Tokens_dynamic::sid($surveyid);

		$tkcount = count(Tokens_dynamic::model()->findAll());

		Yii::app()->loadHelper("surveytranslator");

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
		$data['imageurl'] = Yii::app()->getConfig('imageurl');
		$data['surveyid']=$surveyid;
		$data['subaction']=$subaction;
		$data['dateformatdetails']=getDateFormatData(Yii::app()->session['dateformat']);

		$this->getController()->_getAdminHeader();
		$this->getController()->render("/admin/token/tokenbar",$data);
		$this->getController()->render("/admin/token/tokenform",$data);
		$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));

	}

	/**
	 * Show dialogs and create a new tokens table
	 */
	function _newtokentable($surveyid)
	{
		$clang=$this->getController()->lang;
		if(!empty($_POST['createtable']) && $_POST['createtable']=="Y" && bHasSurveyPermission($surveyid, 'surveyactivation','update'))
     	{
			$fields = array(
							'tid' => 'int(11) not null auto_increment primary key',
                            'participant_id' => 'VARCHAR(50)',
	                        'firstname' => 'VARCHAR(40)',
	                        'lastname' => 'VARCHAR(40)',
	                        'email' => 'text',
	                        'emailstatus' => 'text',
	                        'token' => 'VARCHAR(35)',
	                        'language' => 'VARCHAR(25)',
	                        'blacklisted' => 'CHAR(17)',
                            'sent' => 'VARCHAR(17) DEFAULT "N"',
	                        'remindersent' => 'VARCHAR(17) DEFAULT "N"',
	                        'remindercount' => 'INT(11) DEFAULT 0',
	                        'completed' => 'VARCHAR(17) DEFAULT "N"',
	                        'usesleft' => 'INT(11) DEFAULT 1',
	                        'validfrom' => 'DATETIME',
	                        'validuntil' => 'DATETIME',
	                        'mpid' => 'INT(11)'
	                );
			$comm = Yii::app()->db->createCommand();
			$comm->createTable('{{tokens_' . $surveyid . '}}', $fields);

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

			$this->getController()->_getAdminHeader();
			$this->getController()->_showMessageBox($clang->gT("Token control"),
					$clang->gT("A token table has been created for this survey.")." (\"". Yii::app()->db->tablePrefix ."tokens_$surveyid\")<br /><br />\n"
		    		."<input type='submit' value='"
		    		.$clang->gT("Continue")."' onclick=\"window.open('".$this->getController()->createUrl("admin/tokens/sa/index/surveyid/$surveyid")."', '_top')\" />\n");
			$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));
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
	    elseif (returnglobal('restoretable') == "Y" && !empty($_POST['oldtable']) && bHasSurveyPermission($surveyid, 'surveyactivation','update'))
	    {
	        //$query = db_rename_table($this->input->post("oldtable") , $this->db->dbprefix("tokens_$surveyid"));
	        //$result=$connect->Execute($query) or safe_die("Failed Rename!<br />".$query."<br />".$connect->ErrorMsg());
			Yii::app()->db->createCommand()->renameTable('{{' . $_POST['oldtable'] . '}}' , "{{tokens_$surveyid}}");

			$this->getController()->_getAdminHeader();
			$this->getController()->_showMessageBox($clang->gT("Import old tokens"),
					$clang->gT("A token table has been created for this survey and the old tokens were imported.")." (\"".Yii::app()->db->tablePrefix . "tokens_$surveyid" . "\")<br /><br />\n"
		    		."<input type='submit' value='"
		    		.$clang->gT("Continue")."' onclick=\"window.open('".$this->getController()->createUrl("admin/tokens/index/surveyid/$surveyid")."', '_top')\" />\n");
			$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));

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
			Yii::import('application.helpers.database_helper', true);
			$result = Yii::app()->db->createCommand(db_select_tables_like('{{old_tokens_' . $surveyid . '_%}}'))->query();
	        $tcount=$result->getRowCount();
	        if ($tcount > 0)
	        {
				foreach ($result->readAll() as $rows)
				{
				   $oldlist[]=reset($rows);
				}
				$data['oldlist'] = $oldlist;
	        }

	       	$data['clang']=$clang;
			$thissurvey=getSurveyInfo($surveyid);
			$data['thissurvey']=$thissurvey;
			$data['imageurl'] = Yii::app()->getConfig('imageurl');
			$data['surveyid']=$surveyid;
			$data['tcount']=$tcount;
			$data['databasetype']=Yii::app()->db->getDriverName();

			$this->getController()->_getAdminHeader();
			$this->getController()->render("/admin/token/tokenwarning",$data);
			$this->getController()->_getAdminFooter("http://docs.limesurvey.org", $clang->gT("LimeSurvey online manual"));

	        return;
	    }
	}
}
