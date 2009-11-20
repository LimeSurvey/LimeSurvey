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
*/

// Security Checked: POST, GET, SESSION, REQUEST, returnglobal, DB 

require_once(dirname(__FILE__).'/classes/core/startup.php');    // Since this file can be directly run
require_once(dirname(__FILE__).'/config-defaults.php');
require_once(dirname(__FILE__).'/common.php');
require_once($rootdir.'/classes/core/language.php');

$surveyid=returnglobal('sid');
$postlang=returnglobal('lang');

//Check that there is a SID
if (!isset($surveyid))
{
	//You must have an SID to use this
	include "index.php";
	exit;
}
session_start();

// Get passed language from form, so that we dont loose this!
if (!isset($postlang) || $postlang == "")
{
	$baselang = GetBaseLanguageFromSurveyID($surveyid);
	$clang = new limesurvey_lang($baselang);
} else {
	$clang = new limesurvey_lang($postlang);
	$baselang = $postlang;
}

$thissurvey=getSurveyInfo($surveyid,$baselang);

$register_errormsg = "";

// Check the security question's answer
if (function_exists("ImageCreate") && captcha_enabled('registrationscreen',$thissurvey['usecaptcha']) )
{
    if (!isset($_POST['loadsecurity']) || 
		!isset($_SESSION['secanswer']) ||
		$_POST['loadsecurity'] != $_SESSION['secanswer'])
    {
	    $register_errormsg .= $clang->gT("The answer to the security question is incorrect.")."<br />\n";
    }
}

//Check that the email is a valid style address
if (!validate_email(returnglobal('register_email')))
{
	$register_errormsg .= $clang->gT("The email you used is not valid. Please try again.");
}

if ($register_errormsg != "")
{
		include "index.php";
		exit;
}

//Check if this email already exists in token database
$query = "SELECT email FROM {$dbprefix}tokens_$surveyid\n"
. "WHERE email = ".db_quoteall(sanitize_email(returnglobal('register_email')));
$result = $connect->Execute($query) or safe_die ($query."<br />".$connect->ErrorMsg());   //Checked
if (($result->RecordCount()) > 0)
{
	$register_errormsg=$clang->gT("The email you used has already been registered.");
	include "index.php";
	exit;
}

$mayinsert = false;
while ($mayinsert != true)
{
	$newtoken = randomkey(15);
	$ntquery = "SELECT * FROM {$dbprefix}tokens_$surveyid WHERE token='$newtoken'";
	$ntresult = $connect->Execute($ntquery); //Checked
	if (!$ntresult->RecordCount()) {$mayinsert = true;}
}

$postfirstname=sanitize_xss_string(strip_tags(returnglobal('register_firstname')));   
$postlastname=sanitize_xss_string(strip_tags(returnglobal('register_lastname')));   
/*$postattribute1=sanitize_xss_string(strip_tags(returnglobal('register_attribute1')));   
$postattribute2=sanitize_xss_string(strip_tags(returnglobal('register_attribute2')));   */

//Insert new entry into tokens db
$query = "INSERT INTO {$dbprefix}tokens_$surveyid\n"
. "(firstname, lastname, email, emailstatus, token)\n"
. "VALUES (?, ?, ?, ?, ?)";
$result = $connect->Execute($query, array($postfirstname, 
                                          $postlastname,
                                          returnglobal('register_email'), 
                                          'OK', 
                                          $newtoken)
                                          //                             $postattribute1,   $postattribute2)
) or safe_die ($query."<br />".$connect->ErrorMsg());  //Checked - According to adodb docs the bound variables are quoted automatically
$tid=$connect->Insert_ID("{$dbprefix}tokens_$surveyid","tid");


$fieldsarray["{ADMINNAME}"]=$thissurvey['adminname'];
$fieldsarray["{ADMINEMAIL}"]=$thissurvey['adminemail'];
$fieldsarray["{SURVEYNAME}"]=$thissurvey['name'];
$fieldsarray["{SURVEYDESCRIPTION}"]=$thissurvey['description'];
$fieldsarray["{FIRSTNAME}"]=$postfirstname;
$fieldsarray["{LASTNAME}"]=$postlastname;
$fieldsarray["{EXPIRY}"]=$thissurvey["expiry"];
$fieldsarray["{EXPIRY-DMY}"]=date("d-m-Y",strtotime($thissurvey["expiry"]));
$fieldsarray["{EXPIRY-MDY}"]=date("m-d-Y",strtotime($thissurvey["expiry"]));

$message=$thissurvey['email_register'];
$subject=$thissurvey['email_register_subj'];


$from = "{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";

if (getEmailFormat($surveyid) == 'html')
{
	$useHtmlEmail = true;
	$fieldsarray["{SURVEYURL}"]="<a href='$publicurl/index.php?lang=".$baselang."&sid=$surveyid&token=$newtoken'>".htmlspecialchars("$publicurl/index.php?lang=".$baselang."&sid=$surveyid&token=$newtoken")."</a>";
}
else
{
	$useHtmlEmail = false;
	$fieldsarray["{SURVEYURL}"]="$publicurl/index.php?lang=".$baselang."&sid=$surveyid&token=$newtoken";
}

$message=Replacefields($message, $fieldsarray);
$subject=Replacefields($subject, $fieldsarray);

$html=""; //Set variable

if (SendEmailMessage($message, $subject, returnglobal('register_email'), $from, $sitename,$useHtmlEmail,getBounceEmail($surveyid)))
{
	// TLR change to put date into sent
	//	$query = "UPDATE {$dbprefix}tokens_$surveyid\n"
	//			."SET sent='Y' WHERE tid=$tid";
	$today = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust);
	$query = "UPDATE {$dbprefix}tokens_$surveyid\n"
	."SET sent='$today' WHERE tid=$tid";
	$result=$connect->Execute($query) or safe_die ("$query<br />".$connect->ErrorMsg());     //Checked
	$html="<center>".$clang->gT("Thank you for registering to participate in this survey.")."<br /><br />\n".$clang->gT("An email has been sent to the address you provided with access details for this survey. Please follow the link in that email to proceed.")."<br /><br />\n".$clang->gT("Survey Administrator")." {ADMINNAME} ({ADMINEMAIL})";
	$html=Replacefields($html, $fieldsarray);
	$html .= "<br /><br />\n<input type='submit' onclick='javascript: self.close()' value='".$clang->gT("Close this Window")."'></center>\n";
}
else
{
	$html="Email Error";
}

//PRINT COMPLETED PAGE
if (!$publicdir) {$publicdir=".";}
if (!$thissurvey['template']) {$thistpl="$publicdir/templates/default";} else {$thistpl="$publicdir/templates/{$thissurvey['template']}";}
if (!is_dir($thistpl)) {$thistpl="$publicdir/templates/default";}

sendcacheheaders();
doHeader();

foreach(file("$thistpl/startpage.pstpl") as $op)
{
	echo templatereplace1($op);
}
foreach(file("$thistpl/survey.pstpl") as $op)
{
	echo "\t".templatereplace1($op);
}
echo $html;
foreach(file("$thistpl/endpage.pstpl") as $op)
{
	echo templatereplace1($op);
}
doFooter();

function templatereplace1($line)
{
	global $thissurvey, $surveyid;
	global $publicurl, $templatedir, $token;

	if ($thissurvey['template']) {$templateurl="$publicurl/templates/{$thissurvey['template']}/";}
	else {$templateurl="$publicurl/templates/default/";}

	$line=str_replace("{SURVEYNAME}", $thissurvey['name'], $line);
	$line=str_replace("{SURVEYDESCRIPTION}", $thissurvey['description'], $line);
	$line=str_replace("{TOKEN}", $token, $line);
	$line=str_replace("{SID}", $surveyid, $line);
	$line=str_replace("{TEMPLATEURL}", $templateurl, $line);
	$line=str_replace("{PERCENTCOMPLETE}", "", $line);
	$line=str_replace("{LANGUAGECHANGER}", "", $line);
	return $line;
}

?>
