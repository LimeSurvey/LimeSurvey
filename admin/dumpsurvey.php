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

require_once("config.php");

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

//echo $htmlheader;
if (!$surveyid)
	{
	echo $htmlheader
		."<br />\n"
		."<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"
		._EXPORTSURVEY."</b></td></tr>\n"
		."\t<tr><td align='center'>\n"
		."$setfont<br /><b><font color='red'>"
		._ERROR."</font></b><br />\n"
		._ES_NOSID."<br />\n"
		."<br /><input type='submit' $btstyle value='"
		._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\">\n"
		."\t</td></tr>\n"
		."</table>\n"
		."</body></html>\n";
	exit;
	}

$dumphead = "# SURVEYOR SURVEY DUMP\n"
		  . "#\n# This is a dumped survey from the PHPSurveyor Script\n"
		  . "# http://phpsurveyor.sourceforge.net/\n";

function BuildOutput($Query)
	{
	global $dbprefix;
	$QueryResult = mysql_query($Query) or die ("ERROR: $QueryResult<br />".mysql_error());
	preg_match('/FROM (\w+)( |,)/i', $Query, $MatchResults);
	$TableName = $MatchResults[1];
	if ($dbprefix)
		{
		$TableName = substr($TableName, strlen($dbprefix), strlen($TableName));
		}
	$Output = "\n# NEW TABLE\n# " . strtoupper($TableName) . " TABLE\n#\n";
	while ($Row = mysql_fetch_assoc($QueryResult))
		{
		$ColumnNames = "";
		$ColumnValues = "";
		foreach ($Row as $Key=>$Value)
			{
			$ColumnNames .= "`" . $Key . "`, "; //Add all the column names together
			if (_PHPVERSION >= "4.3.0")
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
		
		
		$Output .= "INSERT INTO $TableName ($ColumnNames) VALUES ($ColumnValues);\n";
		}
	return $Output;
	}

//1: Surveys table
$squery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid";
$sdump = BuildOutput($squery);

//2: Groups Table
$gquery = "SELECT * FROM {$dbprefix}groups WHERE sid=$surveyid";
$gdump = BuildOutput($gquery);

//3: Questions Table
$qquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid";
$qdump = BuildOutput($qquery);

//4: Answers table
$aquery = "SELECT {$dbprefix}answers.* FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}questions.sid=$surveyid";
$adump = BuildOutput($aquery);

//5: Conditions table
$cquery = "SELECT {$dbprefix}conditions.* FROM {$dbprefix}conditions, {$dbprefix}questions WHERE {$dbprefix}conditions.qid={$dbprefix}questions.qid AND {$dbprefix}questions.sid=$surveyid";
$cdump = BuildOutput($cquery);

//6: Label Sets
$lsquery = "SELECT DISTINCT {$dbprefix}labelsets.lid, label_name FROM {$dbprefix}labelsets, {$dbprefix}questions WHERE {$dbprefix}labelsets.lid={$dbprefix}questions.lid AND type='F' AND sid=$surveyid";
$lsdump = BuildOutput($lsquery);

//7: Labels
$lquery = "SELECT DISTINCT {$dbprefix}labels.lid, {$dbprefix}labels.code, {$dbprefix}labels.title, {$dbprefix}labels.sortorder FROM {$dbprefix}labels, {$dbprefix}questions WHERE {$dbprefix}labels.lid={$dbprefix}questions.lid AND type in ('F', 'W', 'H', 'Z') AND sid=$surveyid";
$ldump = BuildOutput($lquery);

//8: Question Attributes
$query = "SELECT {$dbprefix}question_attributes.* FROM {$dbprefix}question_attributes, {$dbprefix}questions WHERE {$dbprefix}question_attributes.qid={$dbprefix}questions.qid AND {$dbprefix}questions.sid=$surveyid";
$qadump = BuildOutput($query);

//9: Assessments
$query = "SELECT {$dbprefix}assessments.* FROM {$dbprefix}assessments WHERE {$dbprefix}assessments.sid=$surveyid";
$asdump = BuildOutput($query);

$fn = "survey_$surveyid.sql";

header("Content-Type: application/download");
header("Content-Disposition: attachment; filename=$fn");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0

echo "#<pre>\n"
	.$dumphead, $sdump, $gdump, $qdump, $adump, $cdump, $lsdump, $ldump, $qadump, $asdump
	."#</pre>\n";
?>