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


session_start();

include("./admin/config.php");

//DEFAULT SETTINGS FOR TEMPLATES
if (!$publicdir) {$publicdir=".";}
$tpldir="$publicdir/templates";

//CHECK FOR REQUIRED INFORMATION (sid)
if (!$_GET['sid'] && !$_POST['sid'])
	{
	//A nice crashout
	sendcacheheaders();
	echo "<html>\n";
	$output=file("$tpldir/default/startpage.pstpl");
	foreach($output as $op)
		{
		echo templatereplace($op);
		}
	echo "\t\t<center><br />\n";
	echo "\t\t\t<font color='RED'><b>ERROR</b></font><br />\n";
	echo "\t\t\tYou have not provided a survey identification number.<br /><br />\n";
	echo "\t\t\tPlease contact <i>$siteadminname</i> at <i>$siteadminemail</i> for further assistance.<br /><br />\n";
	$output=file("$tpldir/default/endpage.pstpl");
	foreach($output as $op)
		{
		echo templatereplace($op);
		}
	echo "</html>\n";
	exit;
	}



//GET BASIC INFORMATION ABOUT THIS SURVEY
if (!isset($sid)) {$sid=returnglobal('sid');}
if (!isset($token)) {$token=returnglobal('token');}
$query="SELECT * FROM {$dbprefix}surveys WHERE sid=$sid";
$result=mysql_query($query) or die ("Couldn't access surveys<br />$query<br />".mysql_error());
$surveyexists=mysql_num_rows($result);
while ($row=mysql_fetch_array($result))
	{
	$surveyname=$row['short_title'];
	$surveydescription=$row['description'];
	$surveywelcome=$row['welcome'];
	$templatedir=$row['template'];
	$surveyadminname=$row['admin'];
	$surveyadminemail=$row['adminemail'];
	$surveyactive = $row['active'];
	$surveyexpiry = $row['expires'];
	$surveyprivate = $row['private'];
	$surveytable = "{$dbprefix}survey_{$row['sid']}";
	$surveyurl = $row['url'];
	$surveyurldescrip = $row['urldescrip'];
	$surveyformat = $row['format'];
	$surveylanguage = $row['language'];
	$surveydatestamp = $row['datestamp'];
	$surveyusecookie = $row['usecookie'];
	}

//SEE IF SURVEY USES TOKENS
$i = 0; $tokensexist = 0;
$tresult = @mysql_list_tables($databasename) or die ("Error getting tokens<br />".mysql_error());
while($tbl = @mysql_tablename($tresult, $i++))
	{
	if ($tbl == "{$dbprefix}tokens_$sid") {$tokensexist = 1;}
	}

//SET THE TEMPLATE DIRECTORY
if (!$templatedir) {$thistpl=$tpldir."/default";} else {$thistpl=$tpldir."/$templatedir";}
if (!is_dir($thistpl)) {$thistpl=$tpldir."/default";}

//REQUIRE THE LANGUAGE FILE
$langdir="$publicdir/lang";
$langfilename="$langdir/$surveylanguage.lang.php";

if (!is_file($langfilename)) {$langfilename="$langdir/$defaultlang.lang.php";}
require($langfilename);

//CHECK FOR PREVIOUSLY COMPLETED COOKIE
//If cookies are being used, and this survey has been completed, a cookie called "PHPSID[sid]STATUS" will exist (ie: SID6STATUS) and will have a value of "COMPLETE"
$cookiename="PHPSID".returnglobal('sid')."STATUS";
if ($_COOKIE[$cookiename] == "COMPLETE" && $surveyusecookie == "Y" && $tokensexist != 1)
	{
	sendcacheheaders();
	echo "<html>\n";
	$output=file("$tpldir/default/startpage.pstpl");
	foreach($output as $op)
		{
		echo templatereplace($op);
		}
	echo "\t\t<center><br />\n";
	echo "\t\t\t<font color='RED'><b>"._ERROR."</b></font><br />\n";
	echo "\t\t\t"._SURVEYCOMPLETE."<br /><br />\n";
	echo "\t\t\t"._CONTACT1." <i>$siteadminname</i> (<i>$siteadminemail</i>) "._CONTACT2."<br /><br />\n";
	$output=file("$tpldir/default/endpage.pstpl");
	foreach($output as $op)
		{
		echo templatereplace($op);
		}
	echo "</html>\n";
	exit;
	}

//CHECK IF SURVEY ID DETAILS HAVE CHANGED
if (!isset($oldsid)) {$oldsid=returnglobal('oldsid');}
if ($oldsid && $oldsid != $sid) //Must be an 'oldsid' and this must not match up with current sid
	{ // SURVEY HAS CHANGED
	foreach ($_SESSION as $SES)
		{
		session_unset();
		}
	//Add here an option to save old results when this capability is added.
	//exit;
	}
if (!isset($oldsid))
	{
	$_SESSION['oldsid'] = $sid;
	}


//CLEAR SESSION IF REQUESTED
if ($_GET['move'] == "clearall")
	{
	sendcacheheaders();
	echo "<html>\n";
	foreach(file("$thistpl/startpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n";
	echo "\t<script type='text/javascript'>\n";
	echo "\t<!--\n";
	echo "\t\tfunction checkconditions(value, name, type)\n";
	echo "\t\t\t{\n";
	echo "\t\t\t}\n";
	echo "\t//-->\n";
	echo "\t</script>\n\n";

	echo "\t<br />\n";
	echo "\t<table align='center' cellpadding='30'><tr><td align='center' bgcolor='white'>\n";
	echo "\t\t<font face='arial' size='2'>\n";
	echo "\t\t<b><font color='red'>"._ANSCLEAR."</b></font><br /><br />";
	echo "<a href='$PHP_SELF?sid=$sid'>"._RESTART."</a><br />\n";
	echo "\t\t<a href='javascript: window.close()'>"._CLOSEWIN."</a>\n";
	echo "\t\t</font>\n";
	echo "\t</td></tr>\n";
	echo "\t</table>\n\t<br />\n";
	foreach(file("$thistpl/endpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	echo "</html>\n";
	session_unset();
	session_destroy();
	exit;	
	}

if ($_GET['newtest'] == "Y")
	{
	foreach ($_SESSION as $SES)
		{
		session_unset();
		}
	}

sendcacheheaders();
//CALL APPROPRIATE SCRIPT
switch ($surveyformat)
	{
	case "A": //All in one
		include("survey.php");
		break;
	case "S": //One at a time
		include("question.php");
		break;
	case "G": //Group at a time
		include("group.php");
		break;		
	default:
		include("question.php");
	}

function templatereplace($line)
	{
	global $surveyname, $surveydescription;
	global $surveywelcome, $percentcomplete;
	global $groupname, $groupdescription, $question;
	global $questioncode, $answer, $navigator;
	global $help, $totalquestions;
	global $completed, $surveyurl, $surveyurldescrip;
	global $notanswered, $privacy, $sid;
	global $publicurl, $templatedir;
	
	if ($templatedir) {$templateurl="$publicurl/templates/$templatedir/";}
	else {$templateurl="$publicurl/templates/default/";}

	$clearall = "\t\t\t\t\t<div class='clearall'><a href='{$_SERVER['PHP_SELF']}?sid=$sid&move=clearall' onClick='return confirm(\""._CONFIRMCLEAR."\")'>["._EXITCLEAR."]</a></div>\n";
	
	
	if (ereg("^<body", $line))
		{
		if (!ereg("^checkconditions()", $line) && ($_SESSION['step'] || $_SESSION['step'] > 0) && ($_POST['move'] != " last " || ($_POST['move'] == " "._LAST." " && $notanswered)) && ($_POST['move'] != " "._SUBMIT." " || ($_POST['move']== " "._SUBMIT." " && $notanswered)))
			{
			$line=str_replace("<body", "<body onload=\"checkconditions()\"", $line);
			}
		}
	
	$line=str_replace("{SURVEYNAME}", $surveyname, $line);
	$line=str_replace("{SURVEYDESCRIPTION}", $surveydescription, $line);
	$line=str_replace("{WELCOME}", $surveywelcome, $line);
	$line=str_replace("{PERCENTCOMPLETE}", $percentcomplete, $line);
	$line=str_replace("{GROUPNAME}", $groupname, $line);
	$line=str_replace("{GROUPDESCRIPTION}", $groupdescription, $line);
	$line=str_replace("{QUESTION}", $question, $line);
	$line=str_replace("{QUESTION_CODE}", $questioncode, $line);
	$line=str_replace("{ANSWER}", $answer, $line);
	$line=str_replace("{NUMBEROFQUESTIONS}", $totalquestions, $line);
	if ($help) 
		{$line=str_replace("{QUESTIONHELP}", "<img src='help.gif' align='left'>".$help, $line);}
	else
		{$line=str_replace("{QUESTIONHELP}", $help, $line);}
	$line=str_replace("{NAVIGATOR}", $navigator, $line);
	$submitbutton="<input class='submit' type='submit' value=' "._SUBMIT." ' name='move'>";
	$line=str_replace("{SUBMITBUTTON}", $submitbutton, $line);
	$line=str_replace("{COMPLETED}", $completed, $line);
	if (!$surveyurldescrip) {$linkreplace="<a href='$surveyurl'>$surveyurl</a>";}
	else {$linkreplace="<a href='$surveyurl'>$surveyurldescrip</a>";}
	$line=str_replace("{URL}", $linkreplace, $line);
	$line=str_replace("{PRIVACY}", $privacy, $line);
	$line=str_replace("{CLEARALL}", $clearall, $line);
	$line=str_replace("{TEMPLATEURL}", $templateurl, $line);
	return $line;
	}

function makegraph($thisstep, $total)
	{
	global $thistpl, $templatedir, $publicurl;
	$chart=$thistpl."/chart.jpg";
	if (!is_file($chart)) {$shchart="chart.jpg";}
	else {$shchart = "$publicurl/templates/$templatedir/chart.jpg";}
	$graph = "<table class='graph' width='100' align='center' cellpadding='2'><tr><td>\n";
	$graph .= "<table width='180' align='center' cellpadding='0' cellspacing='0' border='0' class='innergraph'>\n";
	$graph .= "<tr><td align='right' width='40'>0%</td>\n";
	$size=intval(($thisstep-1)/$total*100);
	$graph .= "<td width='100' align='left'><img src='$shchart' height='12' width='$size' align='left' alt='$size% "._COMPLETE."'></td>\n";
	$graph .= "<td align='left' width='40'>100%</td></tr>\n";
	$graph .= "</table>\n";
	$graph .= "</td></tr>\n</table>\n";
	return $graph;
	}
?>