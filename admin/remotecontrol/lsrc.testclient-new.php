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

//we don't like caching while testing, so we disable it
//ini_set("soap.wsdl_cache_enabled", 0);
//
//// errors are ok, but warnings have nothing to say (in this case, sometimes warnings are useful!)
//ini_set("error_reporting", "E_ALL & ~E_WARNING");

class testclient {

	//Configuration...
	/**change this to the installation path, where you want to try the functions.*/
	public $limeUrl = 'http://localhost/limesurvey'; 

	/** this have to be an admin account for full functionality*/
	public $user = 'admin'; 

	/** password to the account*/
	public $pass = 'password'; 

	/** normally you do not have to change this. But sometimes you want to change this to an static wsdl file like i.e. '/admin/remotecontrol/lsrc.wsdl'.*/
	public $wsdl = '/admin/remotecontrol/lsrc.server.php?wsdl'; 
	
	public $path2wsdl = ''; //will get concatinated later on

	public $sid = 0; //just the initial value

	public $soapClient; //the soapClient object

	function prepare($sid = 0)
	{
		$this->path2wsdl = $this->limeUrl.$this->wsdl;
//		$this->user = $user;
//		$this->pass = $pass;
		$this->sid = $sid;

		ini_set("allow_url_fopen", 1);
		$file = fopen($this->path2wsdl,"r");
		if(class_exists(SoapClient) && $file!=FALSE)
		{
			$this->soapClient = new SoapClient($this->path2wsdl, array('soap_version' => SOAP_1_1,
            'trace' => 1));   
		}
	}

	function soapCheck ()
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
	function getServerFunctions()
	{
		$return = "<b>Client object functions:</b> <font style='font-size:x-small'>(As given in the wsdl file. Functions could be disabled or else on the serverside. There is no guarantee the functions really have these Params nor that the functions exist on the serverside.)</font><br/>";
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
	function getResponse()
	{
		$sOutput .="<br/><br/>Dumping <b>request headers</b>:<br/><pre>"
		.$this->soapClient->__getLastRequestHeaders()."</pre>";
	
		$sOutput .="Dumping <b>request</b>:<code>".htmlentities($this->soapClient->__getLastRequest())."</code>";
	
		$sOutput .="<br/><br/><br/> Dumping <b>response headers</b>:<br/><pre>"
		.$this->soapClient->__getLastResponseHeaders()."</pre>";
		$sOutput .="<br/>Dumping <b>response</b>:<br/><code>".htmlentities($this->soapClient->__getLastResponse())."</code>";
		return $sOutput;
		
	}
	function getAvailableModules ($mode = 'mod')
	{
		try
		{	return $this->soapClient->sAvailableModules($this->user, $this->pass, $mode);}
		catch (SoapFault $fault)
		{	return "<br/><br/><b>SOAP Error: ".$fault->faultcode." : ".$fault->faultstring."</b>";}
	}
	function sendStatistics ( $email, $type = 'pdf', $graph)
	{

		try
		{
			$sReturn = $this->soapClient->fSendStatistic($this->user, $this->pass, $this->sid, $email, $type, $graph);
		}
		catch (SoapFault $fault)
		{
			$sOutput .= " <br/><br/><b>SOAP Error: ".$fault->faultcode." : ".$fault->faultstring."</b>";
		}
		//these are just outputs for testing
		$sOutput .= "<br/><br/><b>Return</b>: ". $sReturn;
		return $sOutput;
	}
	function getFieldmap()
	{

	}
	function sendMail( $type, $maxmails, $subject, $message)
	{

	}
	function deleteSurvey()
	{

	}
	function importMatrix ($title, $question, $help, $items, $module, $mandatory )
	{

	}
	function importFreetext ($title, $question, $help, $module, $mandatory )
	{

	}
	function importQuestion ($module, $mandatory)
	{

	}
	function importGroup ($module, $name, $description)
	{

	}
	function activateSurvey($start = '1980-01-01', $end = '1980-01-01')
	{

	}
	function changeSurvey($table, $key, $value, $where, $mode)
	{

	}
	function createSurvey($title, $description, $welcome, $endtext, $email, $name, $url, $urldesc, $module, $autord='N')
	{
		try
		{
			$sReturn = $this->soapClient->sCreateSurvey($this->user, $this->pass, $this->sid, $title, $description, $welcome, $endtext, $email, $name, $url, $urldesc, $module, $autord);
		}
		catch (SoapFault $fault)
		{
			$sOutput .= " <br/><br/><b>SOAP Error: ".$fault->faultcode." : ".$fault->faultstring."</b>";
		}
		//these are just outputs for testing
		$sOutput .= "<br/><br/><b>Return</b>: ". $sReturn;
		
		return $sOutput;
	}
	function insertToken($tokencsv)
	{

	}
	function insertParticipants($participantData)
	{

	}
	function tokenReturn ()
	{

	}

}

/**
 * initiate the testclient object
 */
$testclient = new testclient();

/**
 * prepare the testclient class (initiate the soapClient, set variables, set sid if given/needed)
 */
if(isset($_POST['sid']))
	$testclient->prepare($_POST['sid']);
else
	$testclient->prepare();

/**
 * set the path2wsdl to what ever is set by the user
 */
//if(isset($_POST['wsdl']))
//	$testclient->path2wsdl = $_POST['wsdl'];
	
$sReturn = '';
// Calling the desired function //XXX
reset($_POST);
while(list($key, $value) = each($_POST))
{
	switch ($key)
	{
		case "sendStatistic":
			
			$sReturn = $testclient->sendStatistics($_REQUEST['type'],$_REQUEST['email'], $_REQUEST['graph']);
		
		break;
		case "createSurvey":
			
			$sVbes = $_REQUEST['sdes'];
			$sVtit = $_REQUEST['stit'];
			$sVwel = $_REQUEST['sVwel'];
			$sVend = $_REQUEST['sVend'];
			$sMail = $_REQUEST['sEmail'];
			$sName = $_REQUEST['sName'];
			$sUrl = $_REQUEST['sUrl'];
			$sUbes = $_REQUEST['sUdes'];
			$sVtyp = $_REQUEST['core'];

			$sReturn = $testclient->createSurvey($sVtit , $sVbes, $sVwel, $sVend, $sMail, $sName, $sUrl, $sUbes, $sVtyp);
				//$sReturn = $client->sCreateSurvey($user, $pass, $iVid, $sVtit , $sVbes, $sVwel, $sVend, $sMail, $sName, $sUrl, $sUbes, $sVtyp);
		break;
		case "delsurvey":
		
			$sReturn = $testclient->deleteSurvey();
			
		break;
		
	}
}

##
#####################################################################

/**
 * begin with output (some css first)
 */
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>LimeSurvey RC Testclient</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type='text/css'>
<!--
body {
	background-color: black;
	color: white;
}

div {
	background-color: white;
	color: black;
	padding: 0.2em;
}

a:link {
	color: darkgreen;
	fontweight: bold;
}

a:visited {
	color: darkgreen;
	fontweight: bold;
}

a:hover {
	color: green;
	fontweight: bold;
}

p {
	border-top: solid 1px white;
	margin-bottom: 0.5em;
	margin-top: 0.2em;
}
-->
</style>
</head>
<body>
<?php
echo $testclient->soapCheck();
/**
 * if there is an return, output request and response for debugging
 */
if(isset($sReturn) && $sReturn!='')
{
	echo $testclient->getResponse();
}

// we dump the client object functions (which are functions of the server  defined in the wsdl)
if(!isset($sReturn) || $sReturn=='')
{
	echo $testclient->getServerFunctions();
}

if(isset($sOutput))
{

	echo '<div style="color:white;background-color:black;border: 1px solid white;">';
	echo '<h3>testing output:</h3>';
	if(isset($iVid))
	{
		echo "<a href='{$testclient->limeUrl}/index.php?sid=".$testclient->sid."&amp;lang=de&amp;newtest=Y' target='_blank'>test Survey</a>";
	}
	echo $sOutput;
	echo '</div>';
}
?>
<div style='float: left; margin-bottom: 5px; margin-right: 5px;'>
<h3>sCreateSurvey function</h3>
<form action='<?php echo $_SERVER['PHP_SELF'] ?>' method='post'><b><font
	color='red'>* </font>VeranstaltungsTyp:</b> <br />
<select name='core' size='1'>
<?php
$aCores = explode(",", $testclient->getAvailableModules('core'));
for($n=0;$n<count($aCores);++$n)
{echo "<option value='".$aCores[$n]."'>".$aCores[$n]."</option>";}
?>
</select> <? //print_r($cores);?> <br />
<b><font color='red'>* </font>VeranstaltungsID / SurveyID (have to be
Integer):</b> <br />
<input type='text' name='sid' size='8' maxlength='11' /> <br />
<b><font color='red'>* </font>Veranstaltungs Titel / Survey Title (used
as part of the welcome message, if welcome Message is left blank):</b> <br />
<input type='text' name='stit' size='30' maxlength='150' /> <br />
<b><font color='red'>* </font>VeranstaltungsBeschreibung /
SurveyDescription (used as part of the welcome message, if welcome
Message is left blank):</b> <br />
<input type='text' name='sdes' size='30' maxlength='150' /> <br />
<b>Willkommenstext / Welcome Message:</b> <br />
<textarea name='sVwel' cols='50' rows='3'></textarea> <br />
<b>Endtext / endtext:</b> <br />
<textarea name='sVend' cols='50' rows='3'></textarea> <br />
<b>Admin Name:</b> <br />
<input type='text' name='sName' size='30' maxlength='150' /> <br />
<b>Admin Email:</b> <br />
<input type='text' name='sEmail' size='30' maxlength='150' /> <br />
<b>End Url:</b> <br />
<input type='text' name='sUrl' size='30' maxlength='150' /> <br />
<b>Url Description:</b> <br />
<input type='text' name='sUdes' size='30' maxlength='150' /> <br />
<?php echo "<input type='hidden' name='wsdl' size='97' value='".$wsdl."' />" ?>
<input type='submit' name='createSurvey' value='Create Survey!' /></form>
</div>


<div style='float: left; margin-bottom: 5px; margin-right: 5px;'>
<h3>sActivateSurvey function</h3>
<form action='<?php echo $_SERVER['PHP_SELF'] ?>' method='post'><b><font
	color='red'>* </font>VeranstaltungsID / SurveyID:</b> <br />
<input type='text' name='sid' size='5' maxlength='5'
	value='<?php echo $iVid ?>' /> <br />
<b>Startdate (YYYY-MM-DD):</b> <br />
<input type='text' name='start' size='30' maxlength='150' /> <br />
<b>Enddate (YYYY-MM-DD):</b> <br />
<input type='text' name='end' size='30' maxlength='150' /> <br />
<?php echo "<input type='hidden' name='wsdl' size='97' value='".$wsdl."' />" ?>
<input type='submit' name='activate' value='Start Survey!' /></form>
</div>

<div style='float: left; margin-bottom: 5px; margin-right: 5px'>
<h3>sDeleteSurvey function</h3>
(attention: no safetyquestion is asked!)<br />
<form action='<?php echo $_SERVER['PHP_SELF'] ?>' method='post'><b><font
	color='red'>* </font>VeranstaltungsID / SurveyID:</b> <br />
<input type='text' name='sid' size='5' maxlength='5'
	value='<?php echo $iVid ?>' /> <br />
<?php echo "<input type='hidden' name='wsdl' size='97' value='".$wsdl."' />" ?>
<input type='submit' name='delsurvey' value='Delete Survey!' /></form>
</div>

<div style='float: left; margin-bottom: 5px; margin-right: 5px;'>
<h3>sImportGroup function</h3>
<form action='<?php echo $_SERVER['PHP_SELF'] ?>' method='post'><b><font
	color='red'>* </font>VeranstaltungsID / SurveyID (have to be Integer):</b>
<br />
<input type='text' name='sid' size='5' maxlength='5'
	value='<?php echo $iVid ?>' /> <br />
<b><font color='red'>* </font>Question group to add (INF,BIO,BWL is
there for testing):</b> <br />
<select name='mod' size='1'>
<?php
$aMods = explode(",", $testclient->getAvailableModules('mod'));
for($n=0;$n<count($aMods);++$n)
{echo "<option value='".$aMods[$n]."'>".$aMods[$n]."</option>";}
?>
</select> <? //print_r($mods);?> <br />
<b>Name of the group:</b><br />
<input type='text' name='groupName' size='30' maxlength='150' /> <br />
<b>groupDescription:</b><br />
<input type='text' name='groupDescription' size='30' maxlength='255' />
<br />
<?php echo "<input type='hidden' name='wsdl' size='97' value='".$wsdl."' />" ?>
<input type='submit' name='impGroup' value='add group to survey!' /></form>
</div>

<div style='float: left; margin-bottom: 5px; margin-right: 5px;'>
<h3>sImportFreetext</h3>
<form action='<?php echo $_SERVER['PHP_SELF'] ?>' method='post'><b><font
	color='red'>* </font>VeranstaltungsID / SurveyID (have to be Integer):</b>
<br />
<input type='text' name='sid' size='5' maxlength='5'
	value='<?php echo $iVid ?>' /> <br />
<input type='checkbox' name='mandatory' value='Y' /> Mandatory <br />
<b><font color='red'>* </font>Question Title :</b> <br />
<input type='text' name='title' size='30' maxlength='150' /> <br />
<b><font color='red'>* </font>Question:</b> <br />
<textarea name='quest' cols='50' rows='3'></textarea> <br />
<b>Helptext:</b> <br />
<textarea name='help' cols='50' rows='3'></textarea> <br />
<?php echo "<input type='hidden' name='wsdl' size='97' value='".$wsdl."' />" ?>
<input type='submit' name='impFree' value='Create Question!' /></form>
</div>

<div style='float: left; margin-bottom: 5px; margin-right: 5px;'>
<h3>sImportQuestion</h3>
<form action='<?php echo $_SERVER['PHP_SELF'] ?>' method='post'><b><font
	color='red'>* </font>VeranstaltungsID / SurveyID (have to be Integer):</b>
<br />
<input type='text' name='sid' size='5' maxlength='5'
	value='<?php echo $iVid ?>' /> <br />
<input type='checkbox' name='mandatory' value='Y' /> Mandatory <br />
<b><font color='red'>* </font>Question csv to import:</b> <br />
<select name='mod' size='1'>
<?php
$aQues = explode(",", $testclient->getAvailableModules('que'));
for($n=0;$n<count($aQues);++$n)
{echo "<option value='".$aQues[$n]."'>".$aQues[$n]."</option>";}
?>
</select> <br />
<?php echo "<input type='hidden' name='wsdl' size='97' value='".$wsdl."' />" ?>
<input type='submit' name='impFree' value='Create Question!' /></form>
</div>


<div style='float: left; margin-bottom: 5px; margin-right: 5px;'>
<h3>sImportMatrix</h3>
<form action='<?php echo $_SERVER['PHP_SELF'] ?>' method='post'><b><font
	color='red'>* </font>VeranstaltungsID / SurveyID (have to be Integer):</b>
<br />
<input type='text' name='sid' size='5' maxlength='5'
	value='<?php echo $iVid ?>' /> <br />
<input type='checkbox' name='mandatory' value='Y' /> Mandatory <br />
<b><font color='red'>* </font>Question :</b> <br />
<textarea name='quest' cols='50' rows='3'></textarea> <br />
<?php
for($n=1;$n<10;++$n)
{
	echo "<b>Item {$n} :</b> <br />",
		 "<input type='text' name='item{$n}' size='50' maxlength='150'/>",
		 "<br />";
}
?> <b>Helptext:</b> <br />
<textarea name='help' cols='50' rows='3'></textarea> <br />
<?php echo "<input type='hidden' name='wsdl' size='97' value='".$wsdl."' />" ?>
<input type='submit' name='impMatrix' value='Create Question!' /></form>
</div>


<div style='float: left; margin-bottom: 5px; margin-right: 5px;'>
<h3>sChangeSurvey function</h3>
( this is not part of the lsrc, it just shows the power of it, <br />
it has to be activated in server.php on line ~60 )
<form action='<?php echo $_SERVER['PHP_SELF'] ?>' method='post'><input
	type="radio" name="mode" value="0" checked='checked' /> update <input
	type="radio" name="mode" value="1" /> insert<br />
<b>Table to change</b> <br />
<input type='text' name='table' size='30' maxlength='150' /> <br />
<b>Key</b><br />
<input type='text' name='key' size='30' maxlength='150' value='' /> <br />
<b>Value</b> <br />
<input type='text' name='value' size='30' maxlength='150' value='' /> <br />
<b>where Condition</b> <br />
<input type='text' name='whereKey' size='30' maxlength='150' /> <br />
<!-- <b>where Value</b> <br />
<input type='text' name='whereValue' size='30' maxlength='150'/>
<?php echo "<input type='hidden' name='wsdl' size='97' value='".$wsdl."' />" ?>
<br />--> <input type='submit' name='change' value='Change Survey!' /></form>

</div>

<?php
echo "<div style='float:left;margin-bottom:5px;margin-left:5px;'>";
echo "<h3>sInsertToken function</h3>";
echo "<p>Makes the Survey closed.<br/> Means: It's only available to people who have an unused token</p>";
echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>";
echo "<b><font color='red'>* </font>VeranstaltungsID / SurveyID (have to be Integer):</b> <br />";
echo "<input type='text' name='sid' size='5' maxlength='5' value='".$iVid."'/>";
echo "<br />";
echo "<b>Tokens seperated by comma (,) :</b> <br />";
echo "<input type='text' name='token' size='50' maxlength='110'/>";
echo "<br />";
echo "<input type='submit' name='tokens' value='Insert Token!'/>";
echo "<input type='hidden' name='wsdl' size='97' value='".$wsdl."' />";
echo "</form>";
echo "</div>";


echo "<div style='float:left; margin-bottom:5px;margin-left:5px;'>";
echo "<h3>sTokenReturn function</h3>";
echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>";
echo "<b><font color='red'>* </font>VeranstaltungsID / SurveyID (have to be Integer):</b> <br />";
echo "<input type='text' name='sid' value='".$iVid."' maxlength='5'/><br />";
echo "<input type='submit' name='tokRet' value='Check for unused Tokens!'/>";
echo "<input type='hidden' name='wsdl' size='97' value='".$wsdl."' />";
echo "</form></div>";

echo "<div style='float:left;margin-bottom:5px;margin-left:5px;'>";
echo "<h3>sInsertParticipants function</h3>";
echo "<p>Makes the Survey closed. Means: It's only available to people who have an unused token</p>";
echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>";
echo "<b><font color='red'>* </font>VeranstaltungsID / SurveyID (have to be Integer):</b> <br />";
echo "<input type='text' name='sid' size='5' maxlength='5' value='".$iVid."'/>";
echo "<br />";
echo "<b><font color='red'>* </font>Data in this Format [params in square brackets are optional]:<br/> \"FIRSTNAME;LASTNAME;EMAIL;LANG[;TOKEN;VALIDFROM;VALIDUNTIL;attrib1,attrib2,attrib3,attrib4,attrib5]
<br/>::FIRSTNAME;LASTNAME;EMAIL;LANG[;TOKEN;VALIDFROM;VALIDUNTIL;attrib1,attrib2,attrib3,attrib4,attrib5]\" and so on :</b> <br />";
echo "<textarea name='sParticipantData' cols='50' rows='3'>";
echo "</textarea> ";
echo "<br />";
echo "<input type='hidden' name='wsdl' size='97' value='".$wsdl."' />";
echo "<input type='submit' name='insPar' value='Insert Personal Data!'/>";
echo "</form>";
echo "</div>";

echo "<div style='float:left;margin-bottom:5px;margin-left:5px;'>";
echo "<h3>sSendEmail function</h3>";
echo "<p>Sends an Email to users of a specific survey. Invite, Remind and custom emails are possible</p>";
echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>";
echo "<b><font color='red'>* </font>SurveyID (have to be Integer):</b> <br />";
echo "<input type='text' name='sid' size='5' maxlength='5' value='".$iVid."'/>";
echo "<br />";
echo "<font color='red'>* </font><b> Email Type:</b><br/>";
echo "<input type='radio' name='type' value='invite' checked='checked' /> invite";
echo "<input type='radio' name='type' value='remind' /> remind";
echo "<input type='radio' name='type' value='custom' /> custom<br/>";
echo "<b>Maxemails (have to be Integer):</b> <br />";
echo "<input type='text' name='maxemails' size='5' maxlength='5' value=''/>";
echo "<br />";
//echo "<b><font color='red'>* </font>Data in this Format [params in square brackets are optional]:<br/> \"FIRSTNAME;LASTNAME;EMAIL[;[ATTRIB1];[ATTRIB2]]::FIRSTNAME;LASTNAME;EMAIL[;[ATTRIB1];[ATTRIB2]]\" and so on :</b> <br />";
echo "<b>Subject for custom cails</b> <br />";
echo "<input type='text' name='subject' size='50' maxlength='255' value=''/><br/>";
echo "<b>Mailtext for custom cails</b> <br />";
echo "<textarea name='mailText' cols='50' rows='3'>";
echo "</textarea> ";
echo "<br />";
echo "<input type='hidden' name='wsdl' size='97' value='".$wsdl."' />";
echo "<input type='submit' name='sendMail' value='Send Email to participants'/>";
echo "</form>";
echo "</div>";

echo "<div style='float:left; margin-bottom:5px;margin-left:5px;'>";
echo "<h3>sGetFieldmap function</h3>";
echo "<p>Gets you the fieldmap from a survey as csv</p>";
echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>";
echo "<b><font color='red'>* </font>VeranstaltungsID / SurveyID (have to be Integer):</b> <br />";
echo "<input type='text' name='sid' value='".$iVid."' maxlength='5'/><br />";
echo "<input type='submit' name='getField' value='Get me the Fieldmap as CSV!'/>";
echo "<input type='hidden' name='wsdl' size='97' value='".$wsdl."' />";
echo "</form></div>";

echo "<div style='float:left; margin-bottom:5px;margin-left:5px;'>";
echo "<h3>fSendStatistic function</h3>";
echo "<p>Gets statistic from a survey and sends it to an E-Mail recipient</p>";
echo "<form action='".$_SERVER['PHP_SELF']."' method='post'>";
echo "<b><font color='red'>* </font>VeranstaltungsID / SurveyID (have to be Integer):</b> <br />";
echo "<input type='text' name='sid' value='".$iVid."' maxlength='5'/><br />";
echo "<b><font color='red'>* </font>E-Mail Adress:</b> <br />";
echo "<input type='text' name='email' value='' maxlength='50' size='50'/><br />";
echo "<input type='checkbox' name='graph' value='1' />Include graphs (only with pdf generation) <br />";
echo "<input type='radio' name='type' value='pdf' checked='checked' />PDF attachement";
echo "<input type='radio' name='type' value='xls' />Excel attachement";
echo "<input type='radio' name='type' value='html' />HTML Mail<br/>";

echo "<input type='submit' name='sendStatistic' value='Send a Statistic'/>";
echo "<input type='hidden' name='wsdl' size='97' value='".$wsdl."' />";
echo "</form></div>";
//phpinfo();

?>

</body>
</html>
