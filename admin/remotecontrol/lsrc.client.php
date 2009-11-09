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
* 
*/
class lsrcClient {

	//Configuration...
	/**change this to the installation path, where you want to try the functions.*/
	public $limeUrl = 'http://localhost/limesource/limesurvey'; 

	/** this have to be an admin account for full functionality*/
	public $user = 'admin'; 

	/** password to the account*/
	public $pass = 'password'; 

	/** 
	 * normally you do not have to change following. 
	 * But sometimes you maybe want to change this to an static wsdl file like i.e. '/admin/remotecontrol/lsrc.wsdl'.*/
	public $wsdl = '/admin/remotecontrol/lsrc.wsdl'; 
	
	public $path2wsdl = ''; //will get concatinated from $limeUrl and $wsdl on prepare

	private $sid; //just the initial value

	private $soapClient; //the soapClient object
	/**
	 * use this to initiate the SoapClient Class and prepare the use of it
	 * you have to set the surveyId here, if you work on a particular survey
	 * @param $sid the surveyid
	 * @return 1 for "client initiated", 0 for "something went wrong"
	 */
	public function prepare($sid = 0)
	{
		$this->path2wsdl = $this->limeUrl.$this->wsdl;
//		$this->user = $user;
//		$this->pass = $pass;
		$this->sid = $sid;

		//print_r(get_declared_classes());
		
		ini_set("allow_url_fopen", 1);
		$file = fopen($this->path2wsdl,"r");
		if(class_exists('SoapClient') && $file!=FALSE)
		{
			try
			{
				$this->soapClient = new SoapClient($this->path2wsdl, array('soap_version' => SOAP_1_1,
            										'trace' => 1));   
				return 1;
			}
			catch (SoapFault $fault)
			{
				throw new SoapFault($fault->faultcode, $fault->faultstring);
			}
		}
		else
		{
			return 0;
		}
	}
	/**
	 * Used in the testclient to show if the SOAP class is available, libXML is there in the minimum version and if the wsdl can be reached
	 * @return HTML String
	 */
	public function soapCheck ()
	{
		$soapCheck ='<div style="color:white;background-color:black;border: 1px solid green;">';
		if(class_exists('SoapClient'))
		{
			$soapCheck .= "<div style='float:left;background:green;color:white;padding:5px;margin-right:5px;'>
						SOAP Erweiterung existiert</div> ";
		}
		else
		{
			$soapCheck .= "<div style='float:left;background:red;color:white;padding:5px;margin-right:5px;'>
						SOAP Erweiterung fehlt!</div> ";
		}
		if(LIBXML_VERSION>=20540)
		{
			$soapCheck .= " <div style='float:left;background:green;color:white;padding:5px;margin-right:5px;'>
						libXML version '".LIBXML_DOTTED_VERSION."' OK</div>";
		}
		else
		{
			$soapCheck .= " <div style='float:left;background:red;color:white;padding:5px;margin-right:5px;'>
						libXML version '".(LIBXML_DOTTED_VERSION )."' nicht OK</div>";
		}
		// Check ob eine wsdl Datei gefunden werden kann
		//$wsdlfile = ;

		ini_set("allow_url_fopen", 1);
		if (!fopen($this->path2wsdl,"r"))
		{
			$soapCheck .= "<div style='float:left;background:red;color:white;padding:5px;'>
						Der Pfad zur WSDL Datei ist nicht korrekt oder die WSDL Datei fehlt!</div>";
		}
		else
		{
			$soapCheck .= "<div style='float:left;background:green;color:white;padding:5px;'>
						WSDL Datei konnte gefunden werden</div>";
		}
		$soapCheck .= "<div style='clear:both;background-color:black;'>
					<form action='".$_SERVER['PHP_SELF']."' method='post'>
					<input type='text' name='wsdl' size='97' value='".$this->path2wsdl."' disabled='disabled' />
					<input type='submit' name='validate' value='neu checken!' />
					</form>
					</div></div>";
		return $soapCheck;
	}
	/**
	 * Used in the testclient to show the Serverfunctions, as given in the wsdl.
	 * @return HTML String
	 */
	public function getServerFunctions()
	{
		$return = "<b>Client object public functions:</b> <font style='font-size:x-small'>(As given in the wsdl file. Functions could be disabled or else on the serverside. There is no guarantee the public functions really have these Params nor that the public functions exist on the serverside.)</font><br/>";
		if (!(!fopen($this->path2wsdl,"r")))
		{
			$funcs = $this->soapClient->__getFunctions();
	
			foreach($funcs as $func)
			{
				$return .= '<p><font style="font-family:tahoma, arial;font-size:small;" >';
				$return .= $func;
				$return .= '</font></p>';
			}
		}
		return $return;
	}
	/**
	 * Used by testclient to show the Request and Response. This is valuable information for debugging.
	 * @return HTML String
	 */
	public function getResponse()
	{
		$sOutput .="<br/><br/>Dumping <b>request headers</b>:<br/><pre>"
		.$this->soapClient->__getLastRequestHeaders()."</pre>";
	
		$sOutput .="Dumping <b>request</b>:<code>".htmlentities($this->soapClient->__getLastRequest())."</code>";
	
		$sOutput .="<br/><br/><br/> Dumping <b>response headers</b>:<br/><pre>"
		.$this->soapClient->__getLastResponseHeaders()."</pre>";
		$sOutput .="<br/>Dumping <b>response</b>:<br/><code>".htmlentities($this->soapClient->__getLastResponse())."</code>";
		return $sOutput;
		
	}
	/**
	 * Used to get the available module names for other functions like importQuestion, importGroup, createSurvey and so on
	 * @param $mode - can be 'core'(surveys), 'mod'(groups) or 'que'(questions)
	 * @return modules csv
	 */
	public function getAvailableModules ($mode = 'mod')
	{
		try
		{	
			return $this->soapClient->sAvailableModules($this->user, $this->pass, $mode);
		}
		catch (SoapFault $fault)
		{	
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
	}
	/**
	 * Sends a statistic of a particular survey, to the given E-Mail adress
	 * @param $email - E-Mail adress of the reciever
	 * @param $type - 'pdf', 'xls' or 'html' is possible... the Format in which the statistic is delivered. pdf and xls will be an attachment
	 * @param $graph - '0' or '1' ... have only an effect on pdf statistics. If '1' graphs will be embedded in the pdf, '0' will embed no images in the pdf
	 * @return 'PDF send', 'HTML send, 'XLS send' or throws a SoapFault
	 */
	public function sendStatistics ( $email, $type = 'pdf', $graph)
	{
		try
		{
			$sReturn = $this->soapClient->fSendStatistic($this->user, $this->pass, $this->sid, $email, $type, $graph);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		return $sReturn;
	}
	/**
	 * returns the fieldmap of a particular survey
	 * @return fieldmap as csv
	 */
	public function getFieldmap()
	{
				
		try
		{
			$sReturn = $this->soapClient->sGetFieldmap($this->user, $this->pass, $this->sid);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		return $sReturn;
	}
	/**
	 * sends invitation, reminder or custom Mails to participants in the token list of a particular survey 
	 * @param $type - custom, remind, invite
	 * @param $maxmails - set the maximum amount of mails to be send in one go. repeat until all mails are send
	 * @param $subject - set the subject for custom mails
	 * @param $message - set the message for custom mails
	 * @return String ('No Mails to send', 'XX Mails send', 'XX Mails send, XX Mails left to send')
	 */
	public function sendMail( $type, $maxmails, $subject, $message)
	{
		
		try
		{
			$sReturn = $this->soapClient->sSendEmail($this->user, $this->pass, $this->sid, $type, $maxmails, $subject, $message);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		
		return $sReturn;
	}
	/**
	 * deletes a particular survey
	 * @return String 'Survey XX deleted' or a SoapFault
	 */
	public function deleteSurvey()
	{
		try
		{
			$sReturn = $this->soapClient->sDeleteSurvey($this->user, $this->pass, $this->sid);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		
		return $sReturn;
	}
	/**
	 * imports a Matrix 5scale question. Normally there is a "Matrix5.csv" in directory 'que' which is used for this. But you can use an own exported Matrix 5 question with any other name of course 
	 * @param $title - Question Code
	 * @param $question - The Question text
	 * @param $help - Help Text for this question
	 * @param $items - Items to rate on the 5scale, Comma seperated
	 * @param $module - optional Parameter, if not given, he tries with Matrix5, which should be OK
	 * @param $mandatory - optional paramter. If not given, the question will not be mandatory. Use 'Y' to make the question mandatory
	 * @return String 'OK' or throws a SoapFault
	 */
	public function importMatrix ($title, $question, $help, $items, $module = "Matrix5", $mandatory='N' )
	{
		try
		{
			$sReturn = $this->soapClient->sImportMatrix($this->user, $this->pass, $this->sid, $title, $question, $help, $items, $module, $mandatory);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		
		return $sReturn;
	}
	/**
	 * imports a Freetext Question. Normally there is a "Freitext.csv" in the directory 'que' which is used for this.
	 * @param $title - Question Code
	 * @param $question - The Question text
	 * @param $help - Help Text for this question
	 * @param $module - optional Parameter, if not given, he tries with 'Freitext', which should be OK
	 * @param $mandatory - optional paramter. If not given, the question will not be mandatory. Use 'Y' to make the question mandatory
	 * @return String "OK" or throws SoapFault
	 */
	public function importFreetext ($title, $question, $help, $module = "Freitext", $mandatory='N' )
	{
		try
		{
			$sReturn = $this->soapClient->sImportFreetext($this->user, $this->pass, $this->sid, $title, $question, $module, $mandatory);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		
		return $sReturn;
	}
	/**
	 * Imports a Question in questions directory. Use getAvailableModules('que') to get all available question.csv's to import.
	 * @param $module - name of the question file, without the filesuffix
	 * @param $mandatory - optional paramter. If not given, the question will not be mandatory. Use 'Y' to make the question mandatory
	 * @return String "OK" or throws SoapFault
	 */
	public function importQuestion ($module, $mandatory='N')
	{	
		try
		{
			$sReturn = $this->soapClient->sImportQuestion($this->user, $this->pass, $this->sid, $module, $mandatory);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		
		$sOutput .= "<br/><br/><b>Return</b>: ". $sReturn;
	}
	/**
	 * Imports a Group in groups directory. Use getAvailableModules('mod') to get all available group.csv's to import.
	 * @param $module - name of the group file, without the filesuffix
	 * @param $name - name of the Group 
	 * @param $description - description text for the group
	 * @return String "Import OK" or throws SoapFault
	 */
	public function importGroup ($module, $name, $description)
	{
		try
		{
			$sReturn = $this->soapClient->sImportGroup($this->user, $this->pass, $this->sid, $module, $name, $description);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		
		return $sReturn;
	}
	/**
	 * activates a survey
	 * @param $start - optional, set a startdate 'YYYY-MM-DD'
	 * @param $end - optional, set an enddate 'YYYY-MM-DD'
	 * @return surveyid of the activated survey or throws a SoapFault
	 */
	public function activateSurvey($start = "1980-01-01", $end = "1980-01-01")
	{
		try
		{
			$sReturn = $this->soapClient->sActivateSurvey($this->user, $this->pass, $this->sid, $start, $end);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		
		return $sReturn;
	}
	/**
	 * potential bad function. You can crash your whole database with this. It is not enabled by default in the server. 
	 * You can change ALL the db tables in Limesurvey with this. Only enable and use it, if you know 100% what you are doing
	 * @param $table - dbtable to change (without prefix)
	 * @param $key - field to change
	 * @param $value - value to set
	 * @param $where - the where clause
	 * @param $mode - insert or update
	 * @return unknown_type
	 */
	public function changeSurvey($table, $key, $value, $where, $mode)
	{ 
		try
		{
			$sReturn = $this->soapClient->sChangeSurvey($this->user, $this->pass, $table, $key, $value, $where, $mode);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		
		return $sReturn;
	}
	/**
	 * Function to import a survey into the database and change some Values. 
	 * @param $title
	 * @param $description
	 * @param $welcome
	 * @param $endtext
	 * @param $email
	 * @param $name
	 * @param $url
	 * @param $urldesc
	 * @param $module
	 * @param $autord - optional
	 * @return unknown_type
	 */
	public function createSurvey($title, $description, $welcome, $endtext, $email, $name, $url, $urldesc, $module, $autord='N')
	{
		try
		{
			$sReturn = $this->soapClient->sCreateSurvey($this->user, $this->pass, $this->sid, $title, $description, $welcome, $endtext, $email, $name, $url, $urldesc, $module, $autord);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		return $sReturn;
	}
	/**
	 * add Tokens to the token table of a particular survey, or create a new one, if it does not exist
	 * @param $tokencsv
	 * @return unknown_type
	 */
	public function insertToken($tokencsv)
	{
		try
		{
			$sReturn = $this->soapClient->sInsertToken($this->user, $this->pass, $this->sid, $tokencsv );
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		return $sReturn;
	}
	/**
	 * Function to insert Participant data while auto creating token if non is supported...
	 * @param $participantData - (FIRSTNAME;LASTNAME;EMAIL;LANG;TOKEN;VALIDFROM;VALIDTO;attrib1,attrib2,attrib3,attrib4,attrib5::)
	 * @return unknown_type
	 */
	public function insertParticipants($participantData)
	{
		try
		{
			$sReturn = $this->soapClient->sInsertParticipants($this->user, $this->pass, $this->sid, $participantData);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		return $sReturn;
	}
	/**
	 * function to return unused Tokens as String, seperated by commas, to get the people who did not complete the Survey
	 * @return String unused Tokes as csv
	 */
	public function tokenReturn ()
	{
		try
		{
			$sReturn = $this->soapClient->sTokenReturn($this->user, $this->pass, $this->sid);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		return $sReturn;
	}

}