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
$sid = $_GET['sid']; if (!$sid) {$sid=$_POST['sid'];}
$gid = $_GET['gid']; if (!$gid) {$gid=$_POST['gid'];}
$qid = $_GET['qid']; if (!$qid) {$qid=$_POST['qid'];}
$code = $_GET['code']; if (!$code) {$code=$_POST['code'];}
$action = $_GET['action']; if (!$action) {$action=$_POST['action'];}
$ok = $_GET['ok']; if (!$ok) {$ok = $_POST['ok'];}
$user = $_GET['user']; if (!$user) {$user = $_POST['user'];}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
//Send ("Expires: " & Format$(Date - 30, "ddd, d mmm yyyy") & " " & Format$(Time, "hh:mm:ss") & " GMT ") 

include("config.php");
echo $htmlheader;

// CHECK IF FIRST USE!
if (!mysql_selectdb ($databasename, $connect))
	{
	echo "<center><b><font color='red'>ERROR: Surveyor database does not exist</font></b><br /><br />\n";
	echo "It appears that your surveyor script has not yet been set up properly.<br />\n";
	echo "The first step is to create a MYSQL database name with your chosen default name of $databasename<br />\n";
	echo "<br /><input type='submit' value='Create $databasename' onClick='location.href=\"createdb.php?dbname=$databasename\"' /></center>\n";
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
	echo "<center><b>Activating survey ID $sid</b></center><br />\n";
	include("activate.php");
	exit;
	}
if ($action == "deactivate")
	{
	echo "<center><b>De-activating survey ID $sid</b></center><br /><br />\n";
	include("deactivate.php");
	exit;
	}

if ($action == "importsurvey")
	{
	echo "<center><b>Importing Survey</b></center><br /><br />\n";
	include("importsurvey.php");
	exit;
	}

//CHECK THAT SURVEYS MARKED AS ACTIVE ACTUALLY HAVE MATCHING TABLES
checkactivations();


//VARIOUS DATABASE OPTIONS/ACTIONS PERFORMED HERE
if ($action == "delsurvey" || $action == "delgroup" || $action=="delquestion" || $action == "delanswer" || $action == "insertnewsurvey" || $action == "copynewquestion" || $action == "insertnewgroup" || $action == "insertnewquestion" || $action == "insertnewanswer" || $action == "updatesurvey" || $action=="updategroup" || $action=="updatequestion" || $action == "updateanswer")
	{
	include("database.php");
	}


// WE DRAW THE PRETTY SCREEN HERE

include("html.php"); 

$cellstyle = "style='border: 1px solid #000080'";
echo "<table width='100%' border='0' cellpadding='0' cellspacing='0' style='border: 5px solid #000080'>\n";
echo "\t<tr>\n";
echo "\t\t<td width='25%' valign='top' align='center' bgcolor='silver' $cellstyle>\n";
echo "\t\t\t<font size='2'>\n";
echo "$adminmenu\n";
echo "\t\t</td>\n";

echo "\t\t<td width='75%' valign='top' $cellstyle>\n";
if ($action == "newsurvey")
	{
	echo "$newsurvey\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo $htmlfooter;
	exit;
	}

echo "$surveysummary";
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
if ($editanswer) {echo "$editanswer";}
echo "\t\t</td>\n";
echo "\t</tr>\n";
echo "</table>\n";

echo $htmlfooter;
?>