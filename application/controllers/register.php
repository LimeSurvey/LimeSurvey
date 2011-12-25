<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *	$Id: Admin_Controller.php 11256 2011-10-25 13:52:18Z c_schmitz $
 */

 /**
  * register
  *
  * @package LimeSurvey
  * @copyright 2011
  * @version $Id$
  * @access public
  */
 class register extends CAction {

	/**
	 * register::__construct()
	 * Constructor
	 * @return
	 */
	 public function run()
    {
    	$actions = array_keys($_GET);
    	$_GET['method'] = $action = (!empty($actions[0])) ? $actions[0] : '';
    	Yii::app()->loadHelper('database');
    	Yii::app()->loadHelper('replacements');
    	//Yii::app();
    	//$this->getController();
    	
    	if(!empty($action))
    	{
    		$this->$action($_GET[$action]);
    	}
    	else
    	{
    		$this->index();
    	}
    }
    function ajaxregisterform($surveyid)
    {
        $redata = compact(array_keys(get_defined_vars()));
        $thistpl = Yii::app()->getConfig("standardtemplaterootdir").'/default';
        $surveyid = sanitize_int($surveyid);
        $squery = "SELECT a.expires, a.startdate
                      FROM {{surveys}} AS a
                      WHERE a.sid = $surveyid "; 
                     
                                            
        $sresult = db_execute_assoc($squery) or show_error("Couldn't execute $squery");
        
            
        $row = $sresult->read();
        
        $data['sid'] = $surveyid;
        $data['startdate'] = $row['startdate'];
        $data['enddate'] = $row['expires'];
        
        $baselang = GetBaseLanguageFromSurveyID($surveyid);
        Yii::import('application.libraries.Limesurvey_lang');
			$clang = new Limesurvey_lang(array('langcode' => $baselang));
        echo templatereplace(file_get_contents("$thistpl/register.pstpl"),array(),$redata,'register.php',false,NULL,$data);
        
    }

    /**
     * register::index()
     * Process register form data and take appropriate action
     * @return
     */
    function index()
    {
    	
        $surveyid = CHttpRequest::getPost('sid');
        $postlang = CHttpRequest::getPost('lang');
        if (!$surveyid)
        {
            Yii::app()->request->redirect(Yii::app()->baseUrl);
        }

        $usquery = "SELECT stg_value FROM {{settings_global}} where stg_name='SessionName'";
        $usresult = db_execute_assoc($usquery,'',true);          //Checked
        if ($usresult->count() > 0)
        {
            $usrow = $usresult->read();
            $stg_SessionName=$usrow['stg_value'];
            CHttpSession::setSessionName("$stg_SessionName-runtime-$surveyid");
        }
        else
        {
            CHttpSession::setSessionName("LimeSurveyRuntime-$surveyid");
        }

		CHttpSession::setCookieParams(array(0, Yii::app()->getConfig('relativeurl').'/'));
       
        // Get passed language from form, so that we dont loose this!
        if (!isset($postlang) || $postlang == "" || !$postlang )
        {
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            //$this->load->library('Limesurvey_lang',array($baselang));
            Yii::import('application.libraries.Limesurvey_lang');
			$clang = new Limesurvey_lang(array('langcode' => $baselang));
            //$clang = $this->limesurvey_lang;
        } else {
            //$this->load->library('Limesurvey_lang',array($postlang));
            //$clang = $this->limesurvey_lang;
            Yii::import('application.libraries.Limesurvey_lang');
			$clang = new Limesurvey_lang(array('langcode' => $postlang));
            $baselang = $postlang;
        }

        $thissurvey=getSurveyInfo($surveyid,$baselang);

        $register_errormsg = "";
        // Check the security question's answer
    if (function_exists("ImageCreate") && captcha_enabled('registrationscreen',$thissurvey['usecaptcha']) )
        {
            if (!isset($_POST['loadsecurity']) ||
            !isset(Yii::app()->session['secanswer']) ||
            CHttpRequest::getPost('loadsecurity') != Yii::app()->session['secanswer'])
            {
                $register_errormsg .= $clang->gT("The answer to the security question is incorrect.")."<br />\n";
            }
        }

        //Check that the email is a valid style address
        if (!validate_email(CHttpRequest::getPost('register_email')))
        {
            $register_errormsg .= $clang->gT("The email you used is not valid. Please try again.");
        }
        if ($register_errormsg != "")
        {
            Yii::app()->request->redirect($surveyid);
        }
        
        //$dbprefix = $this->db->dbprefix;
        //Check if this email already exists in token database
        $query = "SELECT email FROM {{tokens_$surveyid}}\n"
        . "WHERE email = '".sanitize_email(CHttpRequest::getPost('register_email'))."'";
        $result = db_execute_assoc($query) or show_error("Unable to execute this query : \n <br/>".$query."<br />");   //Checked)
        if (($result->count()) > 0)
        {
            $register_errormsg=$clang->gT("The email you used has already been registered.");
            Yii::app()->request->redirect($surveyid);
            //include "index.php";
            //exit;
        }

        $mayinsert = false;

    	// Get the survey settings for token length
    	//$this->load->model("surveys_model");
    	$tlresult = Survey::model()->getSomeRecords(array("tokenlength"),array("sid"=>$surveyid));
    	if (isset($tlresult[0])) {
    		$tlrow = $tlresult[0];
    	}
    	else
    	{
    		$tlrow = $tlresult;
    	}
    	$tokenlength = $tlrow['tokenlength'];
    	//if tokenlength is not set or there are other problems use the default value (15)
    	if(!isset($tokenlength) || $tokenlength == '')
    	{
    		$tokenlength = 15;
    	}

        while ($mayinsert != true)
        {
            $newtoken = sRandomChars($tokenlength);
            $ntquery = "SELECT * FROM {{tokens_$surveyid}} WHERE token='$newtoken'";
            $ntresult = db_execute_assoc($ntquery); //Checked
            if (!$ntresult->count()) {$mayinsert = true;}
        }

        $postfirstname=sanitize_xss_string(strip_tags(CHttpRequest::getPost('register_firstname')));
        $postlastname=sanitize_xss_string(strip_tags(CHttpRequest::getPost('register_lastname')));
        $starttime = sanitize_xss_string(CHttpRequest::getPost('startdate'));
        $endtime = sanitize_xss_string(CHttpRequest::getPost('enddate'));        
        /*$postattribute1=sanitize_xss_string(strip_tags(returnglobal('register_attribute1')));
         $postattribute2=sanitize_xss_string(strip_tags(returnglobal('register_attribute2')));   */

        //Insert new entry into tokens db
        $query = "INSERT INTO {{tokens_$surveyid}}\n"
        . "(firstname, lastname, email, emailstatus, token"; 
        
        if ($starttime && $endtime)
        $query .= ", validfrom, validuntil"; 
        
        $query .=")\n"      
        . "VALUES ('$postfirstname', '$postlastname', '".CHttpRequest::getPost('register_email')."', 'OK', '$newtoken'";
        
        if ($starttime && $endtime)
        $query .= ",$starttime,$endtime";
        
        $query .=")";
        $result = db_execute_assoc($query);
        /**
        $result = $connect->Execute($query, array($postfirstname,
        $postlastname,
        returnglobal('register_email'),
                                                  'OK',
        $newtoken)

        //                             $postattribute1,   $postattribute2)
        ) or safe_die ($query."<br />".$connect->ErrorMsg());  //Checked - According to adodb docs the bound variables are quoted automatically
        */
        $tid = Yii::app()->db->getLastInsertID();; //$connect->Insert_ID("{$dbprefix}tokens_$surveyid","tid");


        $fieldsarray["{ADMINNAME}"]=$thissurvey['adminname'];
        $fieldsarray["{ADMINEMAIL}"]=$thissurvey['adminemail'];
        $fieldsarray["{SURVEYNAME}"]=$thissurvey['name'];
        $fieldsarray["{SURVEYDESCRIPTION}"]=$thissurvey['description'];
        $fieldsarray["{FIRSTNAME}"]=$postfirstname;
        $fieldsarray["{LASTNAME}"]=$postlastname;
        $fieldsarray["{EXPIRY}"]=$thissurvey["expiry"];

        $message=$thissurvey['email_register'];
        $subject=$thissurvey['email_register_subj'];


        $from = "{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";

        if (getEmailFormat($surveyid) == 'html')
        {
            $useHtmlEmail = true;
            $surveylink = $this->getController()->createUrl(''.$surveyid.'/lang-'.$baselang.'/tk-'.$newtoken);
            $optoutlink = $this->getController()->createUrl('optout/local/'.$surveyid.'/'.$baselang.'/'.$newtoken);
            $optinlink = $this->getController()->createUrl('optin/local/'.$surveyid.'/'.$baselang.'/'.$newtoken);
            $fieldsarray["{SURVEYURL}"]="<a href='$surveylink'>".$surveylink."</a>";
            $fieldsarray["{OPTOUTURL}"]="<a href='$optoutlink'>".$optoutlink."</a>";
            $fieldsarray["{OPTINURL}"]="<a href='$optinlink'>".$optinlink."</a>";
        }
        else
        {
            $useHtmlEmail = false;
            $fieldsarray["{SURVEYURL}"]= $this->getController()->createUrl(''.$surveyid.'/lang-'.$baselang.'/tk-'.$newtoken);
            $fieldsarray["{OPTOUTURL}"]= $this->getController()->createUrl('optout/local/'.$surveyid.'/'.$baselang.'/'.$newtoken);
            $fieldsarray["{OPTINURL}"]= $this->getController()->createUrl('optin/local/'.$surveyid.'/'.$baselang.'/'.$newtoken);
        }

        $message=ReplaceFields($message, $fieldsarray);
        $subject=ReplaceFields($subject, $fieldsarray);

        $html=""; //Set variable
					$sitename =  Yii::app()->getConfig('sitename');

        if (SendEmailMessage($message, $subject, CHttpRequest::getPost('register_email'), $from, $sitename,$useHtmlEmail,getBounceEmail($surveyid)))
        {
            // TLR change to put date into sent
            //	$query = "UPDATE {$dbprefix}tokens_$surveyid\n"
            //			."SET sent='Y' WHERE tid=$tid";
            $today = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust);
            $query = "UPDATE {{tokens_$surveyid}}\n"
            ."SET sent='$today' WHERE tid=$tid";
            $result=db_execute_assoc($query) or show_error("Unable to execute this query : $query<br />");     //Checked
            $html="<center>".$clang->gT("Thank you for registering to participate in this survey.")."<br /><br />\n".$clang->gT("An email has been sent to the address you provided with access details for this survey. Please follow the link in that email to proceed.")."<br /><br />\n".$clang->gT("Survey administrator")." {ADMINNAME} ({ADMINEMAIL})";
            $html=ReplaceFields($html, $fieldsarray);
            $html .= "<br /><br /></center>\n";
        }
        else
        {
            $html="Email Error";
        }

        //PRINT COMPLETED PAGE
        if (!$thissurvey['template'])
        {
            $thistpl=sGetTemplatePath(validate_templatedir('default'));
        }
        else
        {
            $thistpl=sGetTemplatePath(validate_templatedir($thissurvey['template']));
        }

        sendcacheheaders();
        doHeader();
		Yii::app()->lang = $clang;
        foreach(file("$thistpl/startpage.pstpl") as $op)
        {
            echo templatereplace($op);
        }
        foreach(file("$thistpl/survey.pstpl") as $op)
        {
            echo "\t".templatereplace($op);
        }
        echo $html;
        foreach(file("$thistpl/endpage.pstpl") as $op)
        {
            echo templatereplace($op);
        }
        doFooter();
    }

 }
