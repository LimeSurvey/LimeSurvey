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
	echo "<CENTER><B><FONT COLOR='RED'>ERROR: Surveyor database does not exist</FONT></B><BR><BR>";
	echo "It appears that your surveyor script has not yet been set up properly.<BR>";
	echo "The first step is to create a MYSQL database name with your chosen default name of $databasename<BR>";
	echo "<BR><INPUT TYPE='SUBMIT' VALUE='Create $databasename' onClick='location.href=\"createdb.php?dbname=$databasename\"'>";
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
	echo "<CENTER><B>Activating survey ID $sid</B><BR><BR>";
	include("activate.php");
	exit;
	}
if ($action == "deactivate")
	{
	echo "<CENTER><B>De-activating survey ID $sid</B><BR><BR>";
	include("deactivate.php");
	exit;
	}

if ($action == "importsurvey")
	{
	echo "<CENTER><B>Importing Survey</B><BR><BR>\n";
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

$cellstyle="STYLE='border-color: #000080; border-width: 1; border-style: solid'";
echo "<TABLE WIDTH='100%' BORDER='0' CELLPADDING='0' CELLSPACING='0' STYLE='border-color: #000080; border-width: 5; border-style: solid'>\n";
echo "<TR><TD WIDTH='25%' VALIGN='TOP' ALIGN='CENTER' BGCOLOR='SILVER' $cellstyle>";
echo "<FONT SIZE='2'>$adminmenu</TD>\n";

echo "<TD WIDTH='75%' VALIGN='TOP' $cellstyle>";
if ($action == "newsurvey")
	{
	echo "$newsurvey</TD></TR>\n";
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
echo "</TD></TR>\n";

echo $htmlfooter;
?>