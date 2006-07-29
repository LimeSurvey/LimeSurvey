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
	# Public License as published by the Free Software 			#
	# Foundation; either version 2 of the License, or (at your 	#
	# option) any later version.								#
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
//THESE WILL BEMOVED INTO THE LANGUAGE FILE ONCE COMPLETED
require_once(dirname(__FILE__).'/config.php');

$surveyid=returnglobal('sid');

//This next line is for security reasons. It ensures that the $surveyid value is never anything but a number.
if (_PHPVERSION >= '4.2.0') {settype($surveyid, "int");} else {settype($surveyid, "integer");} 

//Check that there is a SID
if (!isset($surveyid))
	{
	//You must have an SID to use this
	include "index.php";
    exit;
	}

$thissurvey=getSurveyInfo($surveyid);
GetLanguageFromSurveyID($surveyid);

//Check that the email is a valid style address
if (!validate_email(returnglobal('register_email'))) 
	{
	$register_errormsg=_("The email you used is not valid. Please try again.");
	include "index.php";
    exit;
	}

//Check if this email already exists in token database
$query = "SELECT email FROM {$dbprefix}tokens_$surveyid\n"
	   . "WHERE email = '".returnglobal('register_email')."'";
$result = $connect->Execute($query) or die ($query."<br />".htmlspecialchars($connect->ErrorMsg()));
if ($result->RecordCount()) > 0)
	{
	$register_errormsg=_("The email you used has already been registered.");
	include "index.php";
	exit;
	}

if (_PHPVERSION < "4.2.0")
	{
	srand((double)microtime()*1000000);
	}
$insert = "NO";
while ($insert != "OK")
	{
	if (isset($THISOS) && $THISOS == "solaris")
		{
		$nt1=db_execute_num("SELECT RAND()");
		while ($row=$nt1->FetchRow())) {$newtoken="R".(int)(sprintf("%09s", $row[0]*100000000));}
		}
	else
		{
		$newtoken = "R".sprintf("%09s", rand(1, 1000000000));
		}
	$ntquery = "SELECT * FROM {$dbprefix}tokens_$surveyid WHERE token='$newtoken'";
	$ntresult = $connect->Execute($ntquery);
	if (!$ntresult->RecordCount()) {$insert = "OK";}
	}

//Insert new entry into tokens db
$query = "INSERT INTO {$dbprefix}tokens_$surveyid\n"
	   . "(`firstname`, `lastname`, `email`, `token`, `attribute_1`, `attribute_2`)\n"
	   . "VALUES (?, ?, ?, ?, ?, ?)";
$result = $connect->Execute($query, returnglobal('register_firstname'), returnglobal('register_lastname'),
		returnglobal('register_email'), $newtoken, 
		returnglobal('register_attribute1'), returnglobal('register_attribute2')
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
	$html="<center>"._("Thank you for registering to participate in this survey.<br /><br />\nAn email has been sent to the address you provided with access details for this survey. Please follow the link in that email to proceed.<br /><br />\nSurvey Administrator {ADMINNAME} ({ADMINEMAIL})");
	$html=Replacefields($html, $fieldsarray);
	$html .= "<br /><br />\n<input $btstyle type='submit' onclick='javascript: self.close()' value='"._("Close this Window")."'></center>\n";
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
?>
