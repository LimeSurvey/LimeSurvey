<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA
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
require_once("config.php");

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($gid)) {$gid=returnglobal('gid');}
if (!isset($qid)) {$qid=returnglobal('qid');}
if (!isset($code)) {$code=returnglobal('code');}
if (!isset($action)) {$action=returnglobal('action');}
if (!isset($ok)) {$ok=returnglobal('ok');}
if (!isset($user)) {$user=returnglobal('user');}
if (!isset($pass)) {$pass=returnglobal('pass');}

sendcacheheaders();

echo $htmlheader;

echo "<script type='text/javascript'>\n"
	."<!--\n"
	."\tfunction showhelp(action)\n"
	."\t\t{\n"
	."\t\tvar name='help';\n"
	."\t\tif (action == \"hide\")\n"
	."\t\t\t{\n"
	."\t\t\tdocument.getElementById(name).style.display='none';\n"
	."\t\t\t}\n"
	."\t\telse if (action == \"show\")\n"
	."\t\t\t{\n"
	."\t\t\tdocument.getElementById(name).style.display='';\n"
	."\t\t\t}\n"
	."\t\t}\n"
	."-->\n"
	."</script>\n";

// CHECK IF FIRST USE!
if (!mysql_selectdb ($databasename, $connect))
	{
	echo "<br />\n"
		."<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._SETUP."</strong></td></tr>\n"
		."\t<tr height='22' bgcolor='#CCCCCC'><td align='center'>$setfont\n"
		."<strong><font color='red'>"
		._ERROR."<br />\n"
		._ST_NODB1."</font></strong><br /><br />\n"
		._ST_NODB2."<br />\n"
		._ST_NODB3."<br /><br />\n"
		._ST_NODB4." $databasename<br />\n"
		."<br /><input $btstyle type='submit' value='"
		._ST_CREATEDB."' onClick='location.href=\"createdb.php?dbname=$databasename\"' /></center>\n"
		."</td></tr></table>\n"
		."</body>\n</html>\n";
	exit;
	}
else
	{
	//OPEN DATABASE CONNECTION
	$db = mysql_selectdb($databasename, $connect);
	//DB EXISTS, CHECK FOR APPROPRIATE TABLES
	checkfortables();
	}


if ($action == "activate")
	{
	include("activate.php");
	exit;
	}
if ($action == "deactivate")
	{
	include("deactivate.php");
	exit;
	}

if ($action == "importsurvey")
	{
	include("importsurvey.php");
	exit;
	}
if ($action == "importgroup")
	{
	include("importgroup.php");
	exit;
	}
if ($action == "importquestion")
	{
	include("importquestion.php");
	exit;
	}

//CHECK THAT SURVEYS MARKED AS ACTIVE ACTUALLY HAVE MATCHING TABLES
checkactivations();


//VARIOUS DATABASE OPTIONS/ACTIONS PERFORMED HERE
if ($action == "delsurvey" || $action == "delgroup" || $action == "delgroupall" || 
	$action=="delquestion" || $action=="delquestionall" || $action == "insertnewsurvey" || 
	$action == "copynewquestion" || $action == "insertnewgroup" || 
	$action == "insertnewquestion" || $action == "updatesurvey" || $action=="updategroup" || 
	$action=="updatequestion" || $action == "modanswer" || $action == "renumberquestions" ||
	$action == "delattribute" || $action == "addattribute" || $action == "editattribute")
	{
	include("database.php");
	}


// WE DRAW THE PRETTY SCREEN HERE

include("html.php"); 

//$cellstyle = "style='border: 1px inset #000080'";
echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n"
	."\t<tr>\n"
	."\t\t<td valign='top' align='center' bgcolor='#BBBBBB'>\n";
	//."\t\t\t<font size='2'>\n";

echo showadminmenu();

if ($action == "newsurvey")
	{
	echo "$newsurvey\n"
		."\t\t</td>\n";
	helpscreen();
	echo "\t</tr>\n"
		."</table>\n"
		.htmlfooter("$langdir/instructions.html", "Using PHPSurveyors Admin Script");
	exit;
	}

if (isset($surveysummary)) {echo $surveysummary;}
if (isset($cssummary)) {echo $cssummary;}
if (isset($usersummary)) {echo $usersummary;}
if (isset($addsummary)) {echo $addsummary;}
if (isset($editsurvey)) {echo $editsurvey;}
if (isset($newgroup)) {echo $newgroup;}
if (isset($groupsummary)) {echo $groupsummary;}
if (isset($editgroup)) {echo $editgroup;}
if (isset($newquestion)) {echo $newquestion;}
if (isset($questionsummary)) {echo $questionsummary;}
if (isset($editquestion)) {echo $editquestion;}
if (isset($newanswer)) {echo $newanswer;}
if (isset($answersummary)) {echo $answersummary;}
if (isset($vasummary)) {echo $vasummary;}
if (isset($editanswer)) {echo $editanswer;}
echo "\t\t</td>\n";

helpscreen();

echo "\t</tr>\n";
echo "</table>\n";

echo htmlfooter("$langdir/instructions.html", "Using PHPSurveyors Admin Script");

function helpscreen()
	{
	global $homeurl, $langdir, $setfont, $imagefiles;
	global $surveyid, $gid, $qid, $action;
	echo "\t\t<td id='help' width='200' valign='top' style='display: none' bgcolor='#CCCCCC'>\n"
		."\t\t\t<table width='100%'><tr><td>"
		."<table width='100%' align='center' cellspacing='0'>\n"
		."\t\t\t\t<tr>\n"
		."\t\t\t\t\t<td bgcolor='#555555' height='8'>\n"
		."\t\t\t\t\t\t$setfont<font color='white' size='1'><strong>"
		._HELP."</strong>\n"
		."\t\t\t\t\t</font></font></td>\n"
		."\t\t\t\t</tr>\n"
		."\t\t\t\t<tr>\n"
		."\t\t\t\t\t<td align='center' bgcolor='#AAAAAA' style='border-style: solid; border-width: 1; border-color: #555555'>\n"
		."\t\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' hspace='0' border='0' align='left'>\n"
		."\t\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' name='CloseHelp' align='right' onClick=\"showhelp('hide')\">\n"
		."\t\t\t\t\t</td>\n"
		."\t\t\t\t</tr>\n"
		."\t\t\t\t<tr>\n"
		."\t\t\t\t\t<td bgcolor='silver' height='100%' style='border-style: solid; border-width: 1; border-color: #333333'>\n";
	//determine which help document to show
	if (!$surveyid && $action != "editusers")
		{
		$helpdoc = "$langdir/admin.html";
		}
	elseif (!$surveyid && $action=="editusers")
		{
		$helpdoc = "$langdir/users.html";
		}
	elseif ($surveyid && !$gid)
		{
		$helpdoc = "$langdir/survey.html";
		}
	elseif ($surveyid && $gid && !$qid)
		{
		$helpdoc = "$langdir/group.html";
		}
	//elseif ($surveyid && $gid && $qid && !$_GET['viewanswer'] && !$_POST['viewanswer'])
	elseif ($surveyid && $gid && $qid && !returnglobal('viewanswer'))
		{
		$helpdoc = "$langdir/question.html";
		}
	elseif ($surveyid && $gid && $qid && (returnglobal('viewanswer')))
		{
		$helpdoc = "$langdir/answer.html";
		}
	echo "\t\t\t\t\t\t<iframe width='200' height='400' src='$helpdoc' marginwidth='2' marginheight='2'>\n"
		."\t\t\t\t\t\t</iframe>\n"
		."\t\t\t\t\t</td>"
		."\t\t\t\t</tr>\n"
		."\t\t\t</table></td></tr></table>\n"
		."\t\t</td>\n";
	}
?>