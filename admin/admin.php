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
include("config.php");

if (!isset($sid)) {$sid=returnglobal('sid');}
if (!isset($gid)) {$gid=returnglobal('gid');}
if (!isset($qid)) {$qid=returnglobal('qid');}
if (!isset($code)) {$code=returnglobal('code');}
if (!isset($action)) {$action=returnglobal('action');}
if (!isset($ok)) {$ok=returnglobal('ok');}
if (!isset($user)) {$user=returnglobal('user');}
if (!isset($pass)) {$pass=returnglobal('pass');}

sendcacheheaders();

echo $htmlheader;

echo "<script type='text/javascript'>\n";
echo "<!--\n";
echo "\tfunction showhelp(action)\n";
echo "\t\t{\n";
echo "\t\tvar name='help';\n";
echo "\t\tif (action == \"hide\")\n";
echo "\t\t\t{\n";
echo "\t\t\tdocument.getElementById(name).style.display='none';\n";
echo "\t\t\t}\n";
echo "\t\telse if (action == \"show\")\n";
echo "\t\t\t{\n";
echo "\t\t\tdocument.getElementById(name).style.display='';\n";
echo "\t\t\t}\n";
echo "\t\t}\n";
echo "-->\n";
echo "</script>\n";

// CHECK IF FIRST USE!
if (!mysql_selectdb ($databasename, $connect))
	{
	echo "<br />\n";
	echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._SETUP."</b> <font color='silver'>{$s1row['short_title']}</td></tr>\n";
	echo "\t<tr height='22' bgcolor='#CCCCCC'><td align='center'>$setfont\n";
	echo "<b><font color='red'>"._ERROR."<br />\n";
	echo _ST_NODB1."</font></b><br /><br />\n";
	echo _ST_NODB2."<br />\n";
	echo _ST_NODB3."<br /><br />\n";
	echo _ST_NODB4." $databasename<br />\n";
	echo "<br /><input $btstyle type='submit' value='"._ST_CREATEDB."' onClick='location.href=\"createdb.php?dbname=$databasename\"' /></center>\n";
	echo "</td></tr></table>\n";
	echo "</body>\n</html>\n";
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
if ($action == "delsurvey" || $action == "delgroup" || $action=="delquestion" || $action == "insertnewsurvey" || $action == "copynewquestion" || $action == "insertnewgroup" || $action == "insertnewquestion" || $action == "updatesurvey" || $action=="updategroup" || $action=="updatequestion" || $action == "modanswer")
	{
	include("database.php");
	}


// WE DRAW THE PRETTY SCREEN HERE

include("html.php"); 

//$cellstyle = "style='border: 1px inset #000080'";
echo "<table width='100%' border='0' cellpadding='0' cellspacing='0' >\n";
echo "\t<tr>\n";
echo "\t\t<td valign='top' align='center' bgcolor='#BBBBBB'>\n";
echo "\t\t\t<font size='2'>\n";
echo "$adminmenu\n";

if ($action == "newsurvey")
	{
	echo "$newsurvey\n";
	echo "\t\t</td>\n";
	helpscreen();
	echo "\t</tr>\n";
	echo htmlfooter("instructions.html", "Using PHPSurveyors Admin Script");
	exit;
	}

echo "$surveysummary";
if ($cssummary) {echo $cssummary;}
if ($usersummary) {echo $usersummary;}
if ($addsummary) {echo $addsummary;}
if ($editsurvey) {echo "$editsurvey";}
if ($newgroup) {echo "$newgroup";}
if ($groupsummary) {echo "$groupsummary";}
if ($editgroup) {echo "$editgroup";}
if ($newquestion) {echo "$newquestion";}
if ($questionsummary) {echo "$questionsummary";}
if ($editquestion) {echo "$editquestion";}
if ($newanswer) {echo "$newanswer";}
if ($answersummary) {echo "$answersummary";}
if ($vasummary) {echo "$vasummary";}
if ($editanswer) {echo "$editanswer";}
echo "\t\t</td>\n";

helpscreen();

echo "\t</tr>\n";
echo "</table>\n";

echo htmlfooter("instructions.html", "Using PHPSurveyors Admin Script");

function helpscreen()
	{
	global $homeurl, $langdir;
	global $sid, $gid, $qid, $action;
	echo "\t\t<td id='help' width='150' valign='top' style='display: none' bgcolor='#CCCCCC'>\n";
	echo "\t\t\t<table width='100%'><tr><td><table width='100%' height='100%' align='center' cellspacing='0'>\n";
	echo "\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t<td bgcolor='#555555' height='8'>\n";
	echo "\t\t\t\t\t\t<font color='white' size='1'><b>"._HELP."\n";
	echo "\t\t\t\t\t</td>\n";
	echo "\t\t\t\t</tr>\n";
	echo "\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t<td align='center' bgcolor='#AAAAAA' style='border-style: solid; border-width: 1; border-color: #555555'>\n";
	echo "\t\t\t\t\t\t<img src='./images/blank.gif' width='20' hspace='0' border='0' align='left'>\n";
	echo "\t\t\t\t\t\t<input type='image' src='./images/close.gif' align='right' border='0' hspace='0' onClick=\"showhelp('hide')\">\n";
	echo "\t\t\t\t\t</td>\n";
	echo "\t\t\t\t</tr>\n";
	echo "\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t<td bgcolor='silver' height='100%' style='border-style: solid; border-width: 1; border-color: #333333'>\n";
	//determine which help document to show
	if (!$sid && $action != "editusers")
		{
		$helpdoc = "$langdir/admin.html";
		}
	elseif (!$sid && $action=="editusers")
		{
		$helpdoc = "$langdir/users.html";
		}
	elseif ($sid && !$gid)
		{
		$helpdoc = "$langdir/survey.html";
		}
	elseif ($sid && $gid && !$qid)
		{
		$helpdoc = "$langdir/group.html";
		}
	elseif ($sid && $gid && $qid && !$_GET['viewanswer'] && !$_POST['viewanswer'])
		{
		$helpdoc = "$langdir/question.html";
		}
	elseif ($sid && $gid && $qid && ($_GET['viewanswer'] || $_POST['viewanswer']))
		{
		$helpdoc = "$langdir/answer.html";
		}
	echo "\t\t\t\t\t\t<iframe width='150' height='400' src='$helpdoc' marginwidth='2' marginheight='2'>\n";
	echo "\t\t\t\t\t\t</iframe>\n";
	echo "\t\t\t\t\t</td>";
	echo "\t\t\t\t</tr>\n";
	echo "\t\t\t</table></td></tr></table>\n";
	echo "\t\t</td>\n";
	}
?>