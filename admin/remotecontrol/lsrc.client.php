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
* $Id:$
* 
*/
class lsrcClient {

	//Configuration...
	/**change this to the installation path, where you want to try the public functions.*/
	public $limeUrl = 'http://localhost/limesource/limesurvey_dev'; 

	/** this have to be an admin account for full public functionality*/
	public $user = 'admin'; 

	/** password to the account*/
	public $pass = 'password'; 

	/** 
	 * normally you do not have to change following. 
	 * But sometimes you maybe want to change this to an static wsdl file like i.e. '/admin/remotecontrol/lsrc.wsdl'.*/
	public $wsdl = '/admin/remotecontrol/lsrc.server.php?wsdl'; 
	
	public $path2wsdl = ''; //will get concatinated later on

	private $sid; //just the initial value

	public $soapClient; //the soapClient object
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

		ini_set("allow_url_fopen", 1);
		$file = fopen($this->path2wsdl,"r");
		if(class_exists(SoapClient) && $file!=FALSE)
		{
			try
			{
				$this->soapClient = new SoapClient($this->path2wsdl, array('soap_version' => SOAP_1_1,
            										'trace' => 1));   
				return 1;
			}
			catch (SoapFault $fault)
			{
				return 0;
			}
		}
		else
		{
			return 0;
		}
	}

	public function soapCheck ()
	{
		$soapCheck ='<div style="color:white;background-color:black;border: 1px solid green;">';
		if(class_exists(SoapClient))
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
		//these are just outputs for testing
		return $sReturn;
	}
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
		//these are just outputs for testing
		return $sReturn;
	}
	public function importMatrix ($title, $question, $help, $items, $module = "Matrix5", $mandatory )
	{
		try
		{
			$sReturn = $this->soapClient->sImportMatrix($this->user, $this->pass, $this->sid, $title, $question, $help, $items, $module, $mandatory);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		//these are just outputs for testing
		return $sReturn;
	}
	public function importFreetext ($title, $question, $help, $module = "Freitext", $mandatory )
	{
		try
		{
			$sReturn = $this->soapClient->sImportFreetext($this->user, $this->pass, $this->sid, $title, $question, $module, $mandatory);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		//these are just outputs for testing
		return $sReturn;
	}
	public function importQuestion ($module, $mandatory)
	{	
		try
		{
			$sReturn = $this->soapClient->sImportQuestion($this->user, $this->pass, $this->sid, $module, $mandatory);
		}
		catch (SoapFault $fault)
		{
			throw new SoapFault($fault->faultcode, $fault->faultstring);
		}
		//these are just outputs for testing
		$sOutput .= "<br/><br/><b>Return</b>: ". $sReturn;
	}
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
		//these are just outputs for testing
		return $sReturn;
	}
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
		//these are just outputs for testing
		return $sReturn;
	}
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
		//these are just outputs for testing
		return $sReturn;
	}
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