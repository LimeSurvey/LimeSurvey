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
	echo "<center>$setfont<br /><b>You must have a Survey ID number to export.</b></center>\n";
	exit;
	}

$dumphead = "# SURVEYOR SURVEY DUMP\n";
$dumphead .= "#\n# This is a dumped survey from the PHPSurveyor Script\n";
$dumphead .= "# http://phpsurveyor.sourceforge.net/\n#\n\n";

function BuildOutput($Query)
	{
	$QueryResult = mysql_query($Query);
	preg_match('/FROM (\w+)( |,)/i', $Query, $MatchResults);
	$TableName = $MatchResults[1];
	$Output = "\n#\n# " . strtoupper($TableName) . " TABLE\n#\n";
	while ($Row = mysql_fetch_assoc($QueryResult))
		{
		$ColumnNames = "";
		$ColumnValues = "";
		foreach ($Row as $Key=>$Value)
			{
			$ColumnNames .= "`" . $Key . "`, "; //Add all the column names together
			$ColumnValues .= "'" . addcslashes(str_replace("\r\n", "<br />", $Value), "',\",") . "', ";
			}
		$ColumnNames = substr($ColumnNames, 0, -2); //strip off last comma space
		$ColumnValues = substr($ColumnValues, 0, -2); //strip off last comma space
		
		$Output .= "INSERT INTO $TableName ($ColumnNames) VALUES\n ($ColumnValues);\n";
		}
	return $Output;
	}

//1: Surveys table
$squery = "SELECT * FROM surveys WHERE sid=$sid";
$sdump = BuildOutput($squery);

//2: Groups Table
$gquery = "SELECT * FROM groups WHERE sid=$sid";
$gdump = BuildOutput($gquery);

//3: Questions Table
$qquery = "SELECT * FROM questions WHERE sid=$sid";
$qdump = BuildOutput($qquery);

//4: Answers table
$aquery = "SELECT answers.* FROM answers, questions WHERE answers.qid=questions.qid AND questions.sid=$sid";
$adump = BuildOutput($aquery);

$fn = "survey_$sid.sql";

//header("Content-Type: application/msword"); //EXPORT INTO MSWORD
header("Content-Disposition: attachment; filename=$fn");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
echo "#<pre>\n";
echo $dumphead, $sdump, $gdump, $qdump, $adump;
echo "#</pre>\n";

?>