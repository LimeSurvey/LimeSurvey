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


// DUMP THE RELATED DATA FOR A SINGLE QUESTION INTO A SQL FILE FOR IMPORTING LATER ON OR 
// ON ANOTHER SURVEY SETUP DUMP ALL DATA WITH RELATED QID FROM THE FOLLOWING TABLES
// 1. questions
// 2. answers

$sid = $_GET['sid'];

include ("config.php");

//echo $htmlheader;
if (!$qid)
	{
	echo "<center>$setfont<br /><b>You must have a Question ID number to export.</b></center>\n";
	exit;
	}
$dumphead = "# SURVEYOR QUESTION DUMP\n";
$dumphead .= "#\n# This is a dumped question from the PHPSurveyor Script\n";
$dumphead .= "# http://phpsurveyor.sourceforge.net/\n";

function BuildOutput($Query)
	{
	$QueryResult = mysql_query($Query);
	preg_match('/FROM (\w+)( |,)/i', $Query, $MatchResults);
	$TableName = $MatchResults[1];
	$Output = "\n# NEW TABLE\n# " . strtoupper($TableName) . " TABLE\n#\n";
	while ($Row = mysql_fetch_assoc($QueryResult))
		{
		$ColumnNames = "";
		$ColumnValues = "";
		foreach ($Row as $Key=>$Value)
			{
			$ColumnNames .= "`" . $Key . "`, "; //Add all the column names together
			if (phpversion() >= "4.3.0")
				{
				$ColumnValues .= "'" . mysql_real_escape_string(str_replace("\r\n", "\n", $Value)) . "', ";
				}
			else
				{
				$ColumnValues .= "'" . mysql_escape_string(str_replace("\r\n", "\n", $Value)) . "', ";
				}
			}
		$ColumnNames = substr($ColumnNames, 0, -2); //strip off last comma space
		$ColumnValues = substr($ColumnValues, 0, -2); //strip off last comma space
		
		$Output .= "INSERT INTO $TableName ($ColumnNames) VALUES ($ColumnValues)\n";
		}
	return $Output;
	}

//1: Questions Table
$qquery = "SELECT * FROM questions WHERE qid=$qid";
$qdump = BuildOutput($qquery);

//2: Answers table
$aquery = "SELECT answers.* FROM answers, questions WHERE answers.qid=questions.qid AND questions.qid=$qid";
$adump = BuildOutput($aquery);

$fn = "question_$qid.sql";

//header("Content-Type: application/msword"); //EXPORT INTO MSWORD
header("Content-Disposition: attachment; filename=$fn");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
echo "#<pre>\n";
echo $dumphead, $qdump, $adump;
echo "#</pre>\n";

?>