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

// include the lsrc.config.php
include_once("lsrc.config.php");

// functions helping lsrcService to do some things with Limesurvey (import, activation, checks)
include("lsrc.helper.php");

/**
 * if ?wsdl is set, generate wsdl with correct uri and send it back to whoever requesting
 */
if(isset($_GET['wsdl']))
{
	if($lsrcOverSSL)
		$http = "https://";
	else
		$http = "http://";
		
	$wsdlString = file_get_contents("lsrc_orig.wsdl");
	$wsdlString = str_replace("{lsrclocation}",$http.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'],$wsdlString);
	file_put_contents("lsrc.wsdl",$wsdlString);
	header('Content-type: text/wsdl');
	header('Content-Disposition: attachment; filename=lsrc.wsdl');
}
//we initiate a SoapServer Objekt
if($useCert && $sslCert!=''){
	
	// I don't know for sure how this works... no developerdocumentation on this.
	$context["ssl"]["local_cert"] = $sslCert;
	$context["ssl"]["verify_peer"] = FALSE;
	$context["ssl"]["allow_self_signed"] = TRUE;
	$context["ssl"]["cafile"] = "D://xampp//htdocs//apache//conf//keys//cacert.pem";
	
  	$stream_context = stream_context_create($context);
	
  	$server = new SoapServer($wsdl, array('soap_version' => SOAP_1_1, 
	 			'stream_context' => $stream_context,
	 			'local_cert' => $sslCert));
}
else{
	$server = new SoapServer($wsdl, array('soap_version' => SOAP_1_1));
}

//adds the functions to the SoapServer Object, 
//the sChangeSurvey and sDeleteSurvey function should be commented out for productive Use
//$server->addFunction("sChangeSurvey");
$server->addFunction("sDeleteSurvey");
$server->addFunction("sActivateSurvey");
$server->addFunction("sCreateSurvey");
$server->addFunction("sInsertToken");
$server->addFunction("sTokenReturn");
$server->addFunction("sInsertParticipants");
$server->addFunction("sImportGroup");
$server->addFunction("sAvailableModules");
$server->addFunction("sImportQuestion");
$server->addFunction("sImportMatrix");
// handle the soap request!
$server->handle();
 
/**
 *	Function to change tables in Limesurvey Database, this is too sensitive for productive use, but useful for development and testing
 *
*/
function sChangeSurvey($sUser, $sPass, $table, $key, $value, $where, $mode='0') //XXX
{
	include("lsrc.config.php");
	$lsrcHelper = new lsrcHelper();
	// check for appropriate rights
	if(!$lsrcHelper->checkUser($sUser, $sPass))
	{
		throw new SoapFault("Authentication: ", "User or password wrong");
		exit;
	}
	//check for Surveyowner. Only owners and superadmins can change and activate surveys
	if(!$_SESSION['USER_RIGHT_SUPERADMIN']=='1')
	{
		throw new SoapFault("Authentication: ", "You have no right to change Databasetables");
		exit;
	}
	
	return $lsrcHelper->changeTable($table, $key, $value, $where, $mode);
}

/**
 *	Function to activate a survey in the database and change some Values (starttime, endtime. Required parameters are:
 *	$iVid= Survey ID
 *	$dStart = datetime
 *	$dE = datetime
 *	$sUser = have to be an existing admin or superadmin in limesurvey
 *	$sPass = password have to be the right one for the existing user in limesurvey
*/
function sActivateSurvey($sUser, $sPass, $iVid, $dStart, $dEnd)
{
	include("lsrc.config.php");
	$lsrcHelper = new lsrcHelper();
	$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", START OK "); 
	// check for apropriate rights
	if(!$lsrcHelper->checkUser($sUser, $sPass))
	{
		throw new SoapFault("Authentication: ", "User or password wrong");
		exit;
	}
	
	// Check if all mandatory parameters are present, else abort...
	if(!is_int($iVid) || $iVid==0)
	{
		throw new SoapFault("Server: ", "Mandatory Parameters missing");
		exit;
	}
	
	// Check if the survey to create already exists. If not, abort with Fault.
	if(!$lsrcHelper->surveyExists($iVid))
	{
		throw new SoapFault("Database: ", "Survey you want to activate does not exists");
		exit;
	}
	
	//check for Surveyowner. Only owners and superadmins can change and activate surveys
	if($lsrcHelper->getSurveyOwner($iVid)!=$_SESSION['loginID'] && !$_SESSION['USER_RIGHT_SUPERADMIN']=='1')
	{
		throw new SoapFault("Authentication: ", "You have no right to change Surveys from other people");
		exit;
	}
	
	if(!$lsrcHelper->activateSurvey($iVid))
	{
		throw new SoapFault("Server: ", "Activation went wrong somehow");
		exit;
	}
	
	if($dStart!='')
	{
		$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", CHANGE start ");
		$lsrcHelper->changeTable('surveys','usestartdate','Y','sid='.$iVid);
		$lsrcHelper->changeTable('surveys','startdate',$dStart,'sid='.$iVid);
	}
	if($dEnd!='')
	{
		$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", CHANGE end ");
		$lsrcHelper->changeTable('surveys','useexpiry','Y','sid='.$iVid);
		$lsrcHelper->changeTable('surveys','expires',$dEnd,'sid='.$iVid);
	}
	
	$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", aktivierung OK ");
	return $iVid;
}

/**
 *	Function to import a survey into the database and change some Values. Required parameters are:
 *	$iVid  = Survey ID
 *	$sVtit = Survey Titel
 *	$sVbes = Surveydescription
 *  $sVwel = Welcometext
 *	$sUser = have to be an existing accounts with appropriate rights in limesurvey
 *	$sPass = password have to be the right one for the existing user in limesurvey
 *  $sVtyp = Veranstaltungstyp (Vorlesung, Seminar, Uebung, Exkursion)
*/
function sCreateSurvey($sUser, $sPass, $iVid, $sVtit , $sVbes, $sVwel, $sMail, $sName, $sUrl, $sUbes, $sVtyp ) //XXX
{
	include("lsrc.config.php");
	$lsrcHelper = new lsrcHelper();
	$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", START OK ");
	
	
	if($sVwel=='')
	{//if no welcometext is given, set this one
		$sVwel	= "Herzlich Willkommen zur Evaluation von \"".$sVtit."\"";
	}
	
	if(!$lsrcHelper->checkUser($sUser, $sPass))
	{// check for appropriate rights
		throw new SoapFault("Authentication: ", "User or password wrong");
		exit;
	}
	
	if((!is_int($iVid) || $iVid==0) || $sVtit=='' || $sVbes=='')
	{// Check if mandatory parameters are empty, if so-> abort
		throw new SoapFault("Server: ", "Mandatory Parameters missing");
		exit;
	}
	
	if($lsrcHelper->surveyExists($iVid))
	{// Check if the survey to create already exists. If so, abort with Fault.
		throw new SoapFault("Database: ", "Survey already exists");
		exit;
	}
	$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.",vor import OK ");
	
	
	if($lsrcHelper->importSurvey($iVid, $sVtit , $sVbes, $sVwel, $sUbes, $sVtyp))
	{// if import of survey went ok it returns true, else nothing
		$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", nach import OK ");
		
		//get the optional data into db
		if($sMail!='')
		{
			$lsrcHelper->changeTable("surveys", "adminemail", $sMail, "sid='$iVid'");
			$lsrcHelper->changeTable("surveys", "bounce_email", $sMail, "sid='$iVid'");
		}
		if($sName!='')
			$lsrcHelper->changeTable("surveys", "admin", $sName, "sid='$iVid'");
		if($sUrl!='')
			$lsrcHelper->changeTable("surveys", "url", $sUrl, "sid='$iVid'");
			
		$lsrcHelper->changeTable("surveys", "datecreated", date("Y-m-d"), "sid='$iVid'");
		
		return $iVid;
	}
	else
	{
		throw new SoapFault("Server: ", "Import went wrong somehow");
		exit;
	}
	
}//end of function sCreateSurvey

/**
 *	Function to insert Tokens to an existing Survey, makes it "closed"
 *	$iVid = Survey ID
 *	$sToken = String of tokens in this pattern: token1,token2,token3,token4,token5 and so on...
 *	$sUser = have to be an existing admin or superadmin in Limesurvey
 *	$sPass = password have to be the right one for the existing user in limesurvey
 */
function sInsertToken($sUser, $sPass, $iVid, $sToken) //XXX
{
	global $connect ;
	global $dbprefix ;
	//$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	include("lsrc.config.php");
	$lsrcHelper = new lsrcHelper();
	$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", START OK ");

	// check for apropriate rights
	if(!$lsrcHelper->checkUser($sUser, $sPass))
	{
		throw new SoapFault("Authentication: ", "User or password wrong");
		exit;
	}
	
	// check if there is a $iVid, else abort
	if(!isset($iVid) || $iVid=='' || $iVid==0 )
	{
		throw new SoapFault("Server: ", "No SurveyId given");
		exit;
	}
	
	// check if the Survey we want to populate with tokens already exists, else -> Fault
	if(!$lsrcHelper->surveyExists($iVid))
	{
		throw new SoapFault("Database: ", "Survey does not exists");
		exit;
	}
	$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", ckecks ueberstanden ");
	
	// check if the Token table already exists, if not, create it...
	if(!db_tables_exist("{$dbprefix}tokens_".$iVid))
	{
		$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", TOken Table existiert nicht ");
		$createtokentable=
		"tid int I NOTNULL AUTO PRIMARY,\n "
		. "firstname C(40) ,\n "
		. "lastname C(40) ,\n ";
        //MSSQL needs special treatment because of some strangeness in ADODB
        if ($databasetype=='odbc_mssql')
		{
			$createtokentable.= "email C(320) ,\n "
			."emailstatus C(300) DEFAULT 'OK',\n ";
		}
        else
		{
			$createtokentable.= "email X(320) ,\n "
			."emailstatus X(300) DEFAULT 'OK',\n ";
		}
        
		$createtokentable.= "token C(36) ,\n "
		. "language C(25) ,\n "
		. "sent C(17) DEFAULT 'N',\n "
		. "remindersent C(17) DEFAULT 'N',\n "
		. "remindercount int I DEFAULT 0,\n "
		. "completed C(17) DEFAULT 'N',\n "
		. "attribute_1 C(100) ,\n"
		. "attribute_2 C(100) ,\n"
		. "mpid I ";
		
		$tabname = "{$dbprefix}tokens_{$iVid}"; # not using db_table_name as it quotes the table name (as does CreateTableSQL)
        $taboptarray = array('mysql' => 'ENGINE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci');
		$dict = NewDataDictionary($connect);
		$sqlarray = $dict->CreateTableSQL($tabname, $createtokentable, $taboptarray);
		$execresult=$dict->ExecuteSQLArray($sqlarray, false);
		
		$createtokentableindex = $dict->CreateIndexSQL("{$tabname}_idx", $tabname, array('token'));
		$dict->ExecuteSQLArray($createtokentableindex, false);
	}
	$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", Token tabelle sollte erstellt sein ");
	#################
	
	//if the lsrcSeperator is not set, set the default seperator, a comma...
	if($sLsrcSeparator=='')
	{
		$sLsrcSeparator = ",";
	}
	
	// prepare to fill the table lime_tokens_*
	// this is sensitiv, if the Seperator is not the defined one, almost everything could happen, BE AWARE OF YOUR SEPERATOR!...
	$asTokens = explode($sLsrcSeparator, $sToken);
	$givenTokenCount = count($asTokens);
	// write the tokens in the token_table
	$insertedTokenCount=0;
	
	foreach($asTokens as $value)
	{
		if($value!='')
		{
			$sInsertToken = "INSERT INTO {$dbprefix}tokens_".$iVid." 
				(token,language) VALUES ('".$value."' , '".$_SESSION['lang']."'); ";
			if(!$connect->Execute($sInsertToken))
			{
				throw new SoapFault("Server: ", "Token could not be inserted");
				exit;
			}
			else
			{
				++$insertedTokenCount;
			}			
		}
	}
	return "".$givenTokenCount." Token given, ".$insertedTokenCount." Token inserted.";
	
} //end of function sInsertToken  

/**
 *	Function to insert Participants data while auto creating tokens for everyone...
 *	$iVid = Survey ID
 *	$sParticipantData = String of datasets in this pattern: FIRSTNAME;LASTNAME;EMAIL[;[ATTRIB1];[ATTRIB2]]::FIRSTNAME... and so on...
 *	$sUser = have to be an existing admin or superadmin in Limesurvey
 *	$sPass = password have to be the right one for the existing user in limesurvey
 */
function sInsertParticipants($sUser, $sPass, $iVid, $sParticipantData) //XXX
{
	global $connect ;
	global $dbprefix ;
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	include("lsrc.config.php");
	$lsrcHelper = new lsrcHelper();
	// check for appropriate rights
	if(!$lsrcHelper->checkUser($sUser, $sPass))
	{
		throw new SoapFault("Authentication: ", "User or password wrong");
		exit;
	}
	
	// check if there is a $iVid, else abort
	if(!isset($iVid) || $iVid=='' || $iVid==0 )
	{
		throw new SoapFault("Server: ", "No SurveyId given");
		exit;
	}
	
	// check if the Survey we want to populate with data and tokens already exists, else -> Fault
	if(!$lsrcHelper->surveyExists($iVid))
	{
		throw new SoapFault("Database: ", "Survey does not exists");
		exit;
	}

	// check if the Token table already exists, if not, create it...
	if(!db_tables_exist("{$dbprefix}tokens_".$iVid))
	{
		$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", Token Table existiert nicht ");
		$createtokentable=
		"tid int I NOTNULL AUTO PRIMARY,\n "
		. "firstname C(40) ,\n "
		. "lastname C(40) ,\n ";
        //MSSQL needs special treatment because of some strangeness in ADODB
        if ($databasetype=='odbc_mssql')
		{
			$createtokentable.= "email C(320) ,\n "
			."emailstatus C(300) DEFAULT 'OK',\n ";
		}
        else
		{
			$createtokentable.= "email X(320) ,\n "
			."emailstatus X(300) DEFAULT 'OK',\n ";
		}
        
		$createtokentable.= "token C(36) ,\n "
		. "language C(25) ,\n "
		. "sent C(17) DEFAULT 'N',\n "
		. "remindersent C(17) DEFAULT 'N',\n "
		. "remindercount int I DEFAULT 0,\n "
		. "completed C(17) DEFAULT 'N',\n "
		. "attribute_1 C(100) ,\n"
		. "attribute_2 C(100) ,\n"
		. "mpid I ";
		
		$tabname = "{$dbprefix}tokens_{$iVid}"; # not using db_table_name as it quotes the table name (as does CreateTableSQL)
        $taboptarray = array('mysql' => 'ENGINE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci');
		$dict = NewDataDictionary($connect);
		$sqlarray = $dict->CreateTableSQL($tabname, $createtokentable, $taboptarray);
		$execresult=$dict->ExecuteSQLArray($sqlarray, false);
		
		$createtokentableindex = $dict->CreateIndexSQL("{$tabname}_idx", $tabname, array('token'));
		$dict->ExecuteSQLArray($createtokentableindex, false);
	}
	$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", Token tabelle sollte erstellt sein ");
	
	//set the Seperators to default if nothing is set in the lsrc.config.php
	if(!isset($sDatasetSeperator) || $sDatasetSeperator=='')
	{$sDatasetSeperator = "::";}
	if(!isset($sDatafieldSeperator) || $sDatafieldSeperator=='')
	{$sDatafieldSeperator = ";";}
	
	// prepare to fill the table lime_tokens_*
	// this is sensitiv, if the Seperator is not the defined one, almost everything could happen, BE AWARE OF YOUR SEPERATOR!...
	$asDataset = explode($sDatasetSeperator, $sParticipantData);
	// write the tokens to the token_table
	$iCountParticipants =  count($asDataset);
	$iInsertedParticipants=0;
	foreach($asDataset as $sData)
	{
		if($sData!='')
		{
			$asDatafield = explode($sDatafieldSeperator, $sData);
			$checkCnt=1;
			while($checkCnt>0)
			{
				$value = randomkey(5); //change randomkey value for different tokenlength (up to 36 chars max.)
				$cQuery= "select token from ".$dbprefix."tokens_".$iVid." where token = '".$value."'; ";
				$result = db_execute_assoc($cQuery);
				$checkCnt = $result->RecordCount();
			}
			$iDataLength = count($asDatafield);
			for($n=0;$n>=$iDataLength;++$n)
			{
				if($asDatafield[$n]==''){$asDatafield[$n]=null;}
			}
			$sInsertParti = "INSERT INTO ".$dbprefix."tokens_".$iVid 
					."(firstname,lastname,email,emailstatus,token,"
					."language,sent,completed,attribute_1,attribute_2,mpid)"
					."VALUES ('".utf8_decode($asDatafield[0])."' , 
					'".utf8_decode($asDatafield[1])."' , '".$asDatafield[2]."' , NULL , '".$value."',
					'".$_SESSION['lang']."', 'N', 'N', '".utf8_decode($asDatafield[3])."' , '".utf8_decode($asDatafield[4])."' , NULL); ";
				
			$connect->Execute($sInsertParti);	
			++$iInsertedParticipants;
		}
			
	}

	return "".$iCountParticipants."Datasets given, ".$iInsertedParticipants." rows inserted. ";
	//return  $sParticipantData;
} //end of function sInsertParticipants

/**
 * function to return unused Tokens as String, seperated by commas, to get the people who did not complete the Survey
 */ 
function sTokenReturn($sUser, $sPass, $iVid) //XXX
{
	global $connect ;
	global $dbprefix ;
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	include("lsrc.config.php");
	$lsrcHelper = new lsrcHelper();
	$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", START OK ");
	
	// check for appropriate rights
	if(!$lsrcHelper->checkUser($sUser, $sPass))
	{
		throw new SoapFault("Authentication: ", "User or password wrong");
		exit;
	}
	
	// check if there is a $iVid, else abort
	if(!isset($iVid) || $iVid=='' || $iVid==0 )
	{
		throw new SoapFault("Server: ", "No SurveyId given");
		exit;
	}
	
	// check if the Survey exists, else -> Fault
	if(!$lsrcHelper->surveyExists($iVid))
	{
		throw new SoapFault("Database: ", "Survey does not exists");
		exit;
	}
		
	// check if the token table exists, else throw fault message
	if(db_tables_exist($dbprefix."tokens_".$iVid))
	{		
		// select all the tokens that did not complete the Survey
		$query2select_token = "SELECT token from {$dbprefix}tokens_".$iVid." WHERE completed = 'N'; ";		
		$rs = db_execute_assoc($query2select_token);
		if($rs->RecordCount()<1)
		{
			throw new SoapFault("Database: ", "No unused Tokens found");
			exit;
		}
		
		$n=0;
		while($row = $rs->FetchRow())
		{
			if($n == 0)
			{
				$sReturn = $row['token'];
			}
			else
			{
				$sReturn .= ",".$row['token'];
			} 
			$n++;
		}  
		// return Response: array([iVid],[return]) on the client side, you get this as an Array resp. list
		// the keys in the array, containing the values, are named as defined in the wsdl under the response Message, in this case: array(iVid =>$iVid, return=>$sReturn)

		return $sReturn;
		exit;	
	}
	else
	{
		throw new SoapFault("Database: ", "Token table for this Survey does not exists");
		exit;
	}		
}//end of function sTokenReturn  

/**
 * function to import an exported group of questions into a survey
 *
 * @param unknown_type $iVid
 * @param unknown_type $sMod
 */
function sImportGroup($sUser, $sPass, $iVid, $sMod)//XXX
{
	include("lsrc.config.php");
	$lsrcHelper = new lsrcHelper();
	$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", START OK ");
	
	if(!$lsrcHelper->checkUser($sUser, $sPass))
	{
		throw new SoapFault("Authentication: ", "User or Password wrong");
		exit;
	}
	
	//check for Surveyowner
	if($lsrcHelper->getSurveyOwner($iVid)!=$_SESSION['loginID'] && !$_SESSION['USER_RIGHT_SUPERADMIN']=='1')
	{
		throw new SoapFault("Authentication: ", "You have no right to change Surveys from other people");
		exit;
	}
	
	if(!is_file($modDir."mod_".$sMod.".csv"))
	{
		throw new SoapFault("Server: ", "Survey Module $sMod does not exist");
		exit;
	}
	$checkImport = $lsrcHelper->importGroup($iVid,"mod_".$sMod);
	if(is_array($checkImport))
	{
		return "Import OK";
	}
	else
	{
		throw new SoapFault("Server: ", $checkImport);
		exit;
	}
	
}

/**
 * function to import a questiongroup with one freetext question and change it 
 *
 * @param String $sUser
 * @param String $sPass
 * @param Int $iVid
 * @param String $sMod
 * @param String $qTitle
 * @param String $qText
 * @param String $qHelp
 * @return String
 */
function sImportQuestion($sUser, $sPass, $iVid, $sMod, $qTitle, $qText, $qHelp, $mandatory='N')
{
	include("lsrc.config.php");
	$lsrcHelper = new lsrcHelper();
	$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", START OK ");
	$lastId = $lsrcHelper->importGroup($iVid,$sMod);
	
	// check for appropriate rights
	if(!$lsrcHelper->checkUser($sUser, $sPass))
	{
		throw new SoapFault("Authentication: ", "User or password wrong");
		exit;
	}
	
	//check for Surveyowner
	if($lsrcHelper->getSurveyOwner($iVid)!=$_SESSION['loginID'] && !$_SESSION['USER_RIGHT_SUPERADMIN']=='1')
	{
		throw new SoapFault("Authentication: ", "You have no right to change Surveys from other people");
		exit;
	}
	
	if(is_array($lastId))
	{
		$lsrcHelper->changeTable("questions", "title", $qTitle, "qid='".$lastId['qid']."'");
		$lsrcHelper->changeTable("questions", "question", $qText, "qid='".$lastId['qid']."'");
		$lsrcHelper->changeTable("questions", "help", $qHelp, "qid='".$lastId['qid']."'");
		$lsrcHelper->changeTable("questions", "mandatory", $mandatory, "qid='".$lastId['qid']."'");
		return "OK";
	}
	else
	{
		throw new SoapFault("Server: ", $lastId);
		exit;
	}
}

/**
 * function to import a five scale Matrix question and set 1 to n items
 *
 * @param unknown_type $sUser
 * @param unknown_type $sPass
 * @param unknown_type $iVid
 * @param unknown_type $sMod
 * @param unknown_type $qTitle
 * @param unknown_type $qHelp
 * @param unknown_type $sItems comma seperated values
 * @return unknown
 */
function sImportMatrix($sUser, $sPass, $iVid, $sMod, $qText, $qHelp, $sItems, $mandatory='N' )
{
	global $connect ;
	global $dbprefix ;
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	include("lsrc.config.php");
	
	$lsrcHelper = new lsrcHelper();
	$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.", START OK ");
	
	// check for appropriate rights
	if(!$lsrcHelper->checkUser($sUser, $sPass))
	{
		throw new SoapFault("Authentication: ", "User or password wrong");
		exit;
	}
	//check for surveyOwner
	if($lsrcHelper->getSurveyOwner($iVid)!=$_SESSION['loginID'] && !$_SESSION['USER_RIGHT_SUPERADMIN']=='1')
	{
		throw new SoapFault("Authentication: ", "You have no right to change Surveys from other people");
		exit;
	}
	
	$lastId = $lsrcHelper->importGroup($iVid,$sMod);
	if(is_array($lastId))
	{
		$lsrcHelper->changeTable("questions", "question", $qText, "qid='".$lastId['qid']."'");
		$lsrcHelper->changeTable("questions", "help", $qHelp, "qid='".$lastId['qid']."'");
		if($mandatory==''){$mandatory='N';}
		$lsrcHelper->changeTable("questions", "mandatory", $mandatory, "qid='".$lastId['qid']."'");
		
		$aItems = explode(",", $sItems);
		$n=0;
		foreach($aItems as $item)
		{
			++$n;
			$lsrcHelper->changeTable("answers", "qid,code,answer,default_value,sortorder,language", "'".$lastId['qid']."', '$n','$item','N','$n','".$_SESSION['lang']."' " , "", 1);
		}
		return "OK";
	}
	else
	{
		throw new SoapFault("Server: ", $lastId);
		exit;
	}
	
	
}

/**
 * function to collect all available Modules and send them comma seperated to the client
 *
 * @param String $sUser
 * @param String $sPass
 * String $mode ("mod" or "core")
 * @return commma seperated list of available Modules (groups)
 */
function sAvailableModules($sUser, $sPass, $mode='mod')
{
	include("lsrc.config.php");
	$lsrcHelper = new lsrcHelper();
	$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.",mode=$mode START OK ");
	
	if(!$lsrcHelper->checkUser($sUser, $sPass))
	{
		throw new SoapFault("Authentication: ", "User or password wrong");
		exit;
	}
	if($mode=='mod')
	{
		$mDir = opendir($modDir);
		$n=0;
		while(false !== ($file = readdir($mDir))) 
		{
			if($file!='.' && $file!='..' && substr($file,0,4)=="mod_" && substr($file,-4,4)==".csv")
			{
				$file = basename ($file, ".csv");
				$file = str_replace("mod_", "", $file);
				
				if($n == 0)
				{
					$return = $file;
					$n=1;
				}
				else
				{
					$return .= ",".$file;
				} 
			}
		}  
		return $return;	
	}
	
	if($mode=='core')
	{
		$cDir = opendir($coreDir);
		$n=0;
		while(false !== ($file = readdir($cDir))) 
		{
			if($file!='.' && $file!='..' && substr($file,-4,4)==".csv")
			{
				$file = basename ($file, ".csv");
				//$file = str_replace("mod_", "", $file);
				if($n == 0)
				{
					$return = $file;
					$n=1;
					
				}
				else
				{
					$return .= ",".$file;
				} 
			}
		}  
		return $return;	
	}
}

/**
 * function to delete a survey
 *
 * @param unknown_type $sUser
 * @param unknown_type $sPass
 * @param unknown_type $iVid
 */
function sDeleteSurvey($sUser, $sPass, $iVid)
{
	include("lsrc.config.php");
	$lsrcHelper = new lsrcHelper();
	$lsrcHelper->debugLsrc("wir sind in ".__FUNCTION__." Line ".__LINE__.",mode=$mode START OK ");
	
	if(!$lsrcHelper->checkUser($sUser, $sPass))
	{
		throw new SoapFault("Authentication: ", "User or password wrong");
		exit;
	}
	
	if($lsrcHelper->getSurveyOwner($iVid)!=$_SESSION['loginID'] && !$_SESSION['USER_RIGHT_SUPERADMIN']=='1')
	{
		throw new SoapFault("Authentication: ", "You have no right to delete Surveys from other people");
		exit;
	}
	// check if the Survey exists, else -> Fault
	if(!$lsrcHelper->surveyExists($iVid))
	{
		throw new SoapFault("Database: ", "Survey $iVid does not exists");
		exit;
	}
	
	if($lsrcHelper->deleteSurvey($iVid))
	{
		return "Survey $iVid deleted";
	}
	else
	{
		throw new SoapFault("Server: ", "Survey $iVid was not deleted");
		exit;
	}
	
	
}

?>