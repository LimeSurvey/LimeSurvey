<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
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
require_once("./admin/config.php");

$sid=returnglobal('sid');

//Check that there is a SID
if (!isset($sid))
	{
	//You must have an SID to use this
	include "index.php";
    exit;
	}

$esquery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$sid";
$esresult = mysql_query($esquery);
while ($esrow = mysql_fetch_array($esresult))
	{
	$surveyname = $esrow['short_title'];
	$surveydescription = $esrow['description'];
	$surveyadmin = $esrow['admin'];
	$surveyadminemail = $esrow['adminemail'];
	$surveytemplate = $esrow['template'];
	$surveylanguage = $esrow['language'];
	$surveyemailregister = $esrow['email_register'];
	}
if (!$surveyadminemail) {$surveyadminemail=$siteadminemail; $surveyadmin=$siteadminname;}

//Get the language file
$langdir="$publicdir/lang";
$langfilename="$langdir/$surveylanguage.lang.php";
//Use the default language file if the $thissurvey['language'] file doesn't exist
if (!is_file($langfilename)) {$langfilename="$langdir/$defaultlang.lang.php";}
require_once($langfilename);

//Check that the email is a valid style address
if (!validate_email(returnglobal('register_email'))) 
	{
	$register_errormsg=_RG_INVALIDEMAIL;
	include "index.php";
    exit;
	}

//Check if this email already exists in token database
$query = "SELECT email FROM {$dbprefix}tokens_$sid\n"
	   . "WHERE email = '".returnglobal('register_email')."'";
$result = mysql_query($query) or die ($query."<br />".mysql_error());
if (mysql_num_rows($result) > 0)
	{
	$register_errormsg=_RG_USEDEMAIL;
	include "index.php";
	exit;
	}

if (phpversion() < "4.2.0")
	{
	srand((double)microtime()*1000000);
	}
$insert = "NO";
while ($insert != "OK")
	{
	if (isset($THISOS) && $THISOS == "solaris")
		{
		$nt1=mysql_query("SELECT RAND()");
		while ($row=mysql_fetch_row($nt1)) {$newtoken="R".(int)(sprintf("%09s", $row[0]*100000000));}
		}
	else
		{
		$newtoken = "R".sprintf("%09s", rand(1, 1000000000));
		}
	$ntquery = "SELECT * FROM {$dbprefix}tokens_$sid WHERE token='$newtoken'";
	$ntresult = mysql_query($ntquery);
	if (!mysql_num_rows($ntresult)) {$insert = "OK";}
	}

//Insert new entry into tokens db
$query = "INSERT INTO {$dbprefix}tokens_$sid\n"
	   . "(`firstname`, `lastname`, `email`, `token`, `attribute_1`, `attribute_2`)\n"
	   . "VALUES ('".mysql_escape_string(returnglobal('register_firstname'))."',\n"
	   . "'".mysql_escape_string(returnglobal('register_lastname'))."',\n"
	   . "'".mysql_escape_string(returnglobal('register_email'))."',\n"
	   . "'$newtoken',\n"
	   . "'".mysql_escape_string(returnglobal('register_attribute1'))."',\n"
	   . "'".mysql_escape_string(returnglobal('register_attribute2'))."')";
$result = mysql_query($query) or die ($query."<br />".mysql_error());
$tid=mysql_insert_id();

//$message=_RG_EMAILINVITATION;
$message=$surveyemailregister;
$message=str_replace("{ADMINNAME}", $surveyadmin, $message);
$message=str_replace("{ADMINEMAIL}", $surveyadminemail, $message);
$message=str_replace("{SURVEYNAME}", $surveyname, $message);
$message=str_replace("{SURVEYDESCRIPTION}", $surveydescription, $message);
$message=str_replace("{SURVEYURL}", "$publicurl/index.php?sid=$sid&token=$newtoken", $message);
$message=str_replace("{FIRSTNAME}", returnglobal('register_firstname'), $message);
$message=str_replace("{LASTNAME}", returnglobal('register_lastname'), $message);
$message=str_replace("{ATTRIBUTE_1}", returnglobal('register_attribute1'), $message);
$message=str_replace("{ATTRIBUTE_2}", returnglobal('register_attribute2'), $message);
$message=str_replace("\n", "\r\n", $message);

$headers = "From: $surveyadmin <$surveyadminemail>\r\n"
		 . "X-Mailer: $sitename Emailer (phpsurveyor.sourceforge.net)\r\n";

$subject=_RG_EMAILSUBJECT;
$subject=str_replace("{ADMINNAME}", $surveyadmin, $subject);
$subject=str_replace("{ADMINEMAIL}", $surveyadminemail, $subject);
$subject=str_replace("{SURVEYNAME}", $surveyname, $subject);
$subject=str_replace("{SURVEYDESCRIPTION}", $surveydescription, $subject);

if (mail(returnglobal('register_email'), $subject, $message, $headers))
	{
	$query = "UPDATE {$dbprefix}tokens_$sid\n"
			."SET sent='Y' WHERE tid=$tid";
	$result=mysql_query($query) or die ("$query<br />".mysql_error());
	$html="<center>"._RG_REGISTRATIONCOMPLETE;
	$html=str_replace("{ADMINNAME}", $surveyadmin, $html);
	$html=str_replace("{ADMINEMAIL}", $surveyadminemail, $html);
	$html=str_replace("{SURVEYNAME}", $surveyname, $html);
	$html .= "<br /><br />\n<input $btstyle type='submit' onclick='javascript: self.close()' value='"._CLOSEWIN."'></center>\n";
	}

//PRINT COMPLETED PAGE
if (!$publicdir) {$publicdir=".";}
if (!$surveytemplate) {$thistpl="$publicdir/templates/default";} else {$thistpl="$publicdir/templates/$surveytemplate";}
if (!is_dir($thistpl)) {$thistpl="$publicdir/templates/default";}

sendcacheheaders();
echo "<html>\n";

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
	
function templatereplace1($line)
	{
	global $surveyname, $surveydescription, $surveytemplate, $sid; 
	global $publicurl, $templatedir, $token;
	
	if ($surveytemplate) {$templateurl="$publicurl/templates/$surveytemplate/";}
	else {$templateurl="$publicurl/templates/default/";}

	$line=str_replace("{SURVEYNAME}", $surveyname, $line);
	$line=str_replace("{SURVEYDESCRIPTION}", $surveydescription, $line);
	$line=str_replace("{TOKEN}", $token, $line);
	$line=str_replace("{SID}", $sid, $line);
	$line=str_replace("{TEMPLATEURL}", $templateurl, $line);
	$line=str_replace("{PERCENTCOMPLETE}", "", $line);
	return $line;
	}
?>
