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


// DUMP THE RELATED DATA FOR A SINGLE SURVEY INTO A SQL FILE FOR IMPORTING LATER ON OR ON ANOTHER SURVEY SETUP
// DUMP ALL DATA WITH RELATED SID FROM THE FOLLOWING TABLES
// 1. surveys
// 2. groups
// 3. questions
// 4. answers

include ("config.php");

//echo $htmlheader;
if (!$sid)
	{
	echo "<CENTER>$setfont<BR><B>You must have a Survey ID number to export.";
	exit;
	}


$dumphead="# SURVEYOR SURVEY DUMP\n";
$dumphead .= "#\n# This is a dumped survey from the Surveyor Script\n";
$dumphead .= "# Written by Jason Cleeland\n#\n\n";


//1: Surveys table
$squery="SELECT * FROM surveys WHERE sid=$sid";
$sresult=mysql_query($squery);
$sfields=mysql_num_fields($sresult);
$sdump = "\n# NEW TABLE\n# SURVEY TABLE\n#\n";
while ($srow=mysql_fetch_row($sresult))
	{
	$sdump.="INSERT INTO surveys VALUES (";
	for ($i=0; $i<$sfields; $i++)
		{
		$sdump .= "'".addcslashes(str_replace("\r\n", "<BR>", $srow[$i]), "',\",")."'";
		if ($i!=$sfields-1) {$sdump .= ", ";}
		}
	$sdump .= ");\n";
	}
//echo str_replace("\n", "<BR>", $sdump);
//echo "<BR>";

//2: Groups Table
$gquery="SELECT * FROM groups WHERE sid=$sid";
$gresult=mysql_query($gquery);
$gfields=mysql_num_fields($gresult);
$gdump="\n# NEW TABLE\n# GROUP TABLE\n#\n";
while ($grow=mysql_fetch_row($gresult))
	{
	$gdump.="INSERT INTO groups VALUES (";
	for ($i=0; $i<$gfields; $i++)
		{
		$gdump .= "'".addcslashes(str_replace("\r\n", "<BR>", $grow[$i]), "',\",")."'";
		if ($i!=$gfields-1) {$gdump .= ", ";}
		}
	$gdump .= ");\n";
	}
//echo str_replace("\n", "<BR>", $gdump);
//echo "<BR>";

//3: Questions Table
$qquery="SELECT * FROM questions WHERE sid=$sid";
$qresult=mysql_query($qquery);
$qfields=mysql_num_fields($qresult);
$qdump = "\n# NEW TABLE\n# QUESTIONS TABLE\n#\n";
while ($qrow=mysql_fetch_row($qresult))
	{
	$qdump.="INSERT INTO questions VALUES (";
	for ($i=0; $i<$qfields; $i++)
		{
		$qdump .= "'".addcslashes(str_replace("\r\n","<BR>",$qrow[$i]), "',\",")."'";
		if ($i != $qfields-1) {$qdump .= ", ";}
		}
	$qdump .= ");\n";
	}
//echo str_replace("\n", "<BR>", $qdump);
//echo "<BR>";

//4: Answers table
$aquery="SELECT answers.* FROM answers, questions WHERE answers.qid=questions.qid AND questions.sid=$sid";
$aresult=mysql_query($aquery);
$afields=mysql_num_fields($aresult);
$adump = "\n# NEW TABLE\n# ANSWERS TABLE\n#\n";
while ($arow=mysql_fetch_row($aresult))
	{
	$adump.="INSERT INTO answers VALUES (";
	for ($i=0; $i<$afields; $i++)
		{
		$adump .= "'".addcslashes(str_replace("\r\n","<BR>",$arow[$i]), "',\",")."'";
		if ($i != $afields-1) {$adump .= ", ";}
		}
	$adump .= ");\n";
	}
//echo str_replace("\n", "<BR>", $adump);
$fn="survey_$sid.sql";
//header("Content-Type: application/msword"); //EXPORT INTO MSWORD
header("Content-Disposition: attachment; filename=$fn");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
echo $dumphead, $sdump, $gdump, $qdump, $adump;

?>