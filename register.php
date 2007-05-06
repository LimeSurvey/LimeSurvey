<?php
/*
#############################################################
# >>> PHPSurveyor  							     			#
#############################################################
# > Author:  Jason Cleeland									#
# > E-mail:  jason@cleeland.org								#
# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
# >          CARLTON SOUTH 3053, AUSTRALIA					#
# > Date: 	 20 February 2003								#
#															#
# This set of scripts allows you to develop, publish and	#
# perform data-entry on surveys.							#
#############################################################
#															#
#	Copyright (C) 2003  Jason Cleeland						#
#															#
# This program is free software; you can redistribute 		#
# it and/or modify it under the terms of the GNU General 	#
# Public License Version 2 as published by the Free         #
# Software Foundation.										#
#															#
#															#
# This program is distributed in the hope that it will be 	#
# useful, but WITHOUT ANY WARRANTY; without even the 		#
# implied warranty of MERCHANTABILITY or FITNESS FOR A 		#
# PARTICULAR PURPOSE.  See the GNU General Public License 	#
# for more details.											#
#															#
# You should have received a copy of the GNU General 		#
# Public License along with this program; if not, write to 	#
# the Free Software Foundation, Inc., 59 Temple Place - 	#
# Suite 330, Boston, MA  02111-1307, USA.					#
#############################################################
*/
require_once(dirname(__FILE__).'/config.php');
require_once($rootdir.'/classes/core/language.php');

$surveyid=returnglobal('sid');

//This next line is for security reasons. It ensures that the $surveyid value is never anything but a number.
settype($surveyid, "int");

//Check that there is a SID
if (!isset($surveyid))
{
	//You must have an SID to use this
	include "index.php";
	exit;
}
session_start();
$thissurvey=getSurveyInfo($surveyid);

// Get passed language from form, so that we dont loose this!
if (!isset($_POST['lang']) || $_POST['lang'] == "")
{
	$baselang = GetBaseLanguageFromSurveyID($surveyid);
	$clang = new phpsurveyor_lang($baselang);
} else {
	$clang = new phpsurveyor_lang($_POST['lang']);
}


$register_errormsg = "";

// Check the security question's answer
if (!isset($_POST['loadsecurity']) || $_POST['loadsecurity'] != $_SESSION['secanswer'])
{
	$register_errormsg .= $clang->gT("The answer to the security question is incorrect")."<br />\n";
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
. "WHERE email = '".returnglobal('register_email')."'";
$result = $connect->Execute($query) or die ($query."<br />".htmlspecialchars($connect->ErrorMsg()));
if (($result->RecordCount()) > 0)
{
	$register_errormsg=$clang->gT("The email you used has already been registered.");
	include "index.php";
	exit;
}

$insert = "NO";
while ($insert != "OK")
{
	$newtoken = randomkey(10);
	$ntquery = "SELECT * FROM {$dbprefix}tokens_$surveyid WHERE token='$newtoken'";
	$ntresult = $connect->Execute($ntquery);
	if (!$ntresult->RecordCount()) {$insert = "OK";}
}

//Insert new entry into tokens db
$query = "INSERT INTO {$dbprefix}tokens_$surveyid\n"
. "(`firstname`, `lastname`, `email`, `token`, `attribute_1`, `attribute_2`)\n"
. "VALUES (?, ?, ?, ?, ?, ?)";
$result = $connect->Execute($query, array(returnglobal('register_firstname'), returnglobal('register_lastname'),
returnglobal('register_email'), $newtoken,
returnglobal('register_attribute1'), returnglobal('register_attribute2'))
) or die ($query."<br />".htmlspecialchars($connect->ErrorMsg()));
$tid=$connect->Insert_ID();


$fieldsarray["{ADMINNAME}"]=$thissurvey['adminname'];
$fieldsarray["{ADMINEMAIL}"]=$thissurvey['adminemail'];
$fieldsarray["{SURVEYNAME}"]=$thissurvey['name'];
$fieldsarray["{SURVEYDESCRIPTION}"]=$thissurvey['description'];
$fieldsarray["{SURVEYURL}"]="$publicurl/index.php?sid=$surveyid&token=$newtoken";
$fieldsarray["{FIRSTNAME}"]=returnglobal('register_firstname');
$fieldsarray["{LASTNAME}"]=returnglobal('register_lastname');
$fieldsarray["{ATTRIBUTE_1}"]=returnglobal('register_attribute1');
$fieldsarray["{ATTRIBUTE_2}"]=returnglobal('register_attribute2');

$message=$thissurvey['email_register'];
$subject=$thissurvey['email_register_subj'];

$message=Replacefields($message, $fieldsarray);
$subject=Replacefields($subject, $fieldsarray);

$from = "{$thissurvey['adminname']} <{$thissurvey['adminemail']}>";

$html=""; //Set variable

if (MailtextMessage($message, $subject, returnglobal('register_email'), $from, $sitename))
{
	// TLR change to put date into sent
	//	$query = "UPDATE {$dbprefix}tokens_$surveyid\n"
	//			."SET sent='Y' WHERE tid=$tid";
	$today = date("Y-m-d Hi");
	$query = "UPDATE {$dbprefix}tokens_$surveyid\n"
	."SET sent='$today' WHERE tid=$tid";
	$result=$connect->Execute($query) or die ("$query<br />".htmlspecialchars($connect->ErrorMsg()));
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
	return $line;
}

function randomkey($length)
{
	$pattern = "1234567890";
	for($i=0;$i<$length;$i++)
	{
		if(isset($key))
		$key .= $pattern{rand(0,9)};
		else
		$key = $pattern{rand(0,9)};
	}
	return $key;
}

?>