<?php
/*
#############################################################
# >>> LimeSurvey  										#
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
# Public License Version 2 as published by the Free         #
# Software Foundation.										#
#															#
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

//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}


$qid = $_GET['qid'];

require_once(dirname(__FILE__).'/../config.php');
include_once("login_check.php");

//echo $htmlheader;
if (!$qid)
{
	echo $htmlheader;
	echo "<br />\n";
	echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>".$clang->gT("Export Question")."</strong></td></tr>\n";
	echo "\t<tr bgcolor='#CCCCCC'><td align='center'>$setfont\n";
	echo "$setfont<br /><strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n".$clang->gT("No QID has been provided. Cannot dump question.")."<br />\n";
	echo "<br /><input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n";
	echo "\t</td></tr>\n";
	echo "</table>\n";
	echo "</body></html>\n";
	exit;
}

$dumphead = "# LimeSurvey Question Dump\n"
          . "# DBVersion $dbversionnumber\n"
          . "# This is a dumped question from the LimeSurvey Script\n"
          . "# http://www.limesurvey.org/\n"
          . "# Do not change this header!\n";

function BuildOutput($Query)
{
	global $dbprefix, $connect;
	$QueryResult = db_execute_assoc($Query) or die ("ERROR: $QueryResult<br />".htmlspecialchars($connect->ErrorMsg()));
	preg_match('/FROM (\w+)( |,)/i', $Query, $MatchResults);
	$TableName = $MatchResults[1];;
	if ($dbprefix)
	{
		$TableName = substr($TableName, strlen($dbprefix), strlen($TableName));
	}
	$Output = "\n#\n# " . strtoupper($TableName) . " TABLE\n#\n";
	$HeaderDone = false;	$ColumnNames = "";
	while ($Row = $QueryResult->FetchRow())
	{

       if (!$HeaderDone)
       {
    		foreach ($Row as $Key=>$Value)
    		{
    			$ColumnNames .= CSVEscape($Key).","; //Add all the column names together
    		}
			$ColumnNames = substr($ColumnNames, 0, -1); //strip off last comma space
     		$Output .= "$ColumnNames\n";
    		$HeaderDone=true;
       }
		$ColumnValues = "";
		foreach ($Row as $Key=>$Value)
		{
			$ColumnValues .= CSVEscape(str_replace("\r\n", "\n", $Value)) . ",";
		}
		$ColumnValues = substr($ColumnValues, 0, -1); //strip off last comma space
		$Output .= "$ColumnValues\n";
	}
	return $Output;
}


//1: Questions Table
$qquery = "SELECT * FROM {$dbprefix}questions WHERE qid=$qid";
$qdump = BuildCSVFromQuery($qquery);

//2: Answers table
$aquery = "SELECT {$dbprefix}answers.* FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}questions.qid=$qid";
$adump = BuildCSVFromQuery($aquery);

//3: Labelsets Table
//$lsquery = "SELECT DISTINCT {$dbprefix}labelsets.* FROM {$dbprefix}labelsets, {$dbprefix}questions WHERE {$dbprefix}labelsets.lid={$dbprefix}questions.lid AND type='F' AND qid=$qid";
$lsquery = "SELECT DISTINCT {$dbprefix}labelsets.* FROM {$dbprefix}labelsets, {$dbprefix}questions WHERE {$dbprefix}labelsets.lid={$dbprefix}questions.lid AND type in ('F', 'H', 'Z', 'W') AND qid=$qid";
$lsdump = BuildCSVFromQuery($lsquery);

//4: Labels Table
$lquery = "SELECT DISTINCT {$dbprefix}labels.* FROM {$dbprefix}labels, {$dbprefix}questions WHERE {$dbprefix}labels.lid={$dbprefix}questions.lid AND type in ('F', 'H', 'Z', 'W') AND qid=$qid";
$ldump = BuildCSVFromQuery($lquery);

//5: Question Attributes
$query = "SELECT {$dbprefix}question_attributes.* FROM {$dbprefix}question_attributes WHERE {$dbprefix}question_attributes.qid=$qid";
$qadump = BuildCSVFromQuery($query);
$fn = "limesurvey_question_$qid.csv";

header("Content-Type: application/download");
header("Content-Disposition: attachment; filename=$fn");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
Header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache");                          // HTTP/1.0
echo $dumphead, $qdump, $adump, $lsdump, $ldump, $qadump;

?>
