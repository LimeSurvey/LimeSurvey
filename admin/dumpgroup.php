<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
* 
* $Id$
*/



// DUMP THE RELATED DATA FOR A SINGLE QUESTION INTO A SQL FILE FOR IMPORTING LATER ON OR
// ON ANOTHER SURVEY SETUP DUMP ALL DATA WITH RELATED QID FROM THE FOLLOWING TABLES
// 1. questions
// 2. answers

include_once("login_check.php");

$gid = returnglobal('gid');
$surveyid = returnglobal('sid');

//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}


//echo $htmlheader;
if (!$gid)
{
	echo $htmlheader;
	echo "<br />\n";
	echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>".$clang->gT("Export Question")."</strong></td></tr>\n";
	echo "\t<tr bgcolor='#CCCCCC'><td align='center'>$setfont\n";
	echo "$setfont<br /><strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n"._EQ_NOGID."<br />\n";
	echo "<br /><input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n";
	echo "\t</td></tr>\n";
	echo "</table>\n";
	echo "</body></html>\n";
	exit;
}

$fn = "limesurvey_group_$gid.csv";

$dumphead = "# LimeSurvey Group Dump\n"
        . "# DBVersion $dbversionnumber\n"
        . "# This is a dumped group from the LimeSurvey Script\n"
        . "# http://www.limesurvey.org/\n"
        . "# Do not change this header!\n";



//1: Groups Table
$gquery = "SELECT * 
           FROM {$dbprefix}groups 
		   WHERE gid=$gid";
$gdump = BuildCSVFromQuery($gquery);

//2: Questions Table
$qquery = "SELECT * 
           FROM {$dbprefix}questions 
		   WHERE gid=$gid";
$qdump = BuildCSVFromQuery($qquery);

//3: Answers table
$aquery = "SELECT DISTINCT {$dbprefix}answers.* 
           FROM {$dbprefix}answers, {$dbprefix}questions 
		   WHERE ({$dbprefix}answers.qid={$dbprefix}questions.qid) 
		   AND ({$dbprefix}questions.gid=$gid)";
$adump = BuildCSVFromQuery($aquery);

//4: Conditions table - THIS CAN ONLY EXPORT CONDITIONS THAT RELATE TO THE SAME GROUP
$cquery = "SELECT DISTINCT {$dbprefix}conditions.* 
           FROM {$dbprefix}conditions, {$dbprefix}questions, {$dbprefix}questions b 
		   WHERE ({$dbprefix}conditions.cqid={$dbprefix}questions.qid) 
		   AND ({$dbprefix}conditions.qid=b.qid) 
		   AND ({$dbprefix}questions.gid=$gid) 
		   AND (b.gid=$gid)";
$cdump = BuildCSVFromQuery($cquery);

//5: Question Attributes
$query = "SELECT {$dbprefix}question_attributes.* 
	   	  FROM {$dbprefix}question_attributes, {$dbprefix}questions 
		  WHERE ({$dbprefix}question_attributes.qid={$dbprefix}questions.qid) 
		  AND ({$dbprefix}questions.gid=$gid)";
$qadump = BuildCSVFromQuery($query);

if($action=='exportstructureLsrcCsvGroup')
{
	include_once($homedir.'/remotecontrol/lsrc.config.php');
	$lsrcString = $dumphead. $gdump. $qdump. $adump. $cdump. $qadump;
	//Select title as Filename and save
	$groupTitleSql = "SELECT group_name 
		             FROM {$dbprefix}groups 
					 WHERE sid=$surveyid AND gid=$gid ";
	$groupTitleRs = db_execute_assoc($groupTitleSql);   
	$groupTitle = $groupTitleRs->FetchRow();
	file_put_contents("remotecontrol/".$modDir.substr($groupTitle['group_name'],0,20).".csv",$lsrcString);
}
else
{
// HTTP/1.0
	header("Content-Type: application/download");
	header("Content-Disposition: attachment; filename=$fn");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // always modified
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Pragma: cache");
	
echo $dumphead, $gdump, $qdump, $adump, $cdump, $qadump;
exit;
}

?>
