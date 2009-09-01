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



// DUMP THE RELATED DATA FOR A SINGLE SURVEY INTO A SQL FILE FOR IMPORTING LATER ON OR ON ANOTHER SURVEY SETUP
// DUMP ALL DATA WITH RELATED SID FROM THE FOLLOWING TABLES
// 1. Surveys
// 2. Surveys Language Table
// 3. Groups
// 4. Questions
// 5. Answers
// 6. Conditions 
// 7. Label Sets
// 8. Labels
// 9. Question Attributes
// 10. Assessments
// 11. Quota
// 12. Quota Members

include_once("login_check.php");

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}


if (!$surveyid)
{
	echo $htmlheader
	."<br />\n"
	."<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
	."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Export Survey")."</strong></td></tr>\n"
	."\t<tr><td align='center'>\n"
	."<br /><strong><font color='red'>"
	.$clang->gT("Error")."</font></strong><br />\n"
	.$clang->gT("No SID has been provided. Cannot dump survey")."<br />\n"
	."<br /><input type='submit' value='"
	.$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n"
	."\t</td></tr>\n"
	."</table>\n"
	."</body></html>\n";
	exit;
}

$dumphead = "# LimeSurvey Survey Dump\n"
        . "# DBVersion $dbversionnumber\n"
        . "# This is a dumped survey from the LimeSurvey Script\n"
        . "# http://www.limesurvey.org/\n"
        . "# Do not change this header!\n";

//1: Surveys table
$squery = "SELECT * 
           FROM {$dbprefix}surveys 
		   WHERE sid=$surveyid";
$sdump = BuildCSVFromQuery($squery);

//2: Surveys Languagsettings table
$slsquery = "SELECT * 
             FROM {$dbprefix}surveys_languagesettings 
			 WHERE surveyls_survey_id=$surveyid";
$slsdump = BuildCSVFromQuery($slsquery);

//3: Groups Table
$gquery = "SELECT * 
           FROM {$dbprefix}groups 
		   WHERE sid=$surveyid 
		   ORDER BY gid";
$gdump = BuildCSVFromQuery($gquery);

//4: Questions Table
$qquery = "SELECT * 
           FROM {$dbprefix}questions 
		   WHERE sid=$surveyid 
		   ORDER BY qid";
$qdump = BuildCSVFromQuery($qquery);

//5: Answers table
$aquery = "SELECT {$dbprefix}answers.* 
           FROM {$dbprefix}answers, {$dbprefix}questions 
		   WHERE {$dbprefix}answers.language={$dbprefix}questions.language 
		   AND {$dbprefix}answers.qid={$dbprefix}questions.qid 
		   AND {$dbprefix}questions.sid=$surveyid";
$adump = BuildCSVFromQuery($aquery);

//6: Conditions table
$cquery = "SELECT DISTINCT {$dbprefix}conditions.* 
           FROM {$dbprefix}conditions, {$dbprefix}questions 
		   WHERE {$dbprefix}conditions.qid={$dbprefix}questions.qid 
		   AND {$dbprefix}questions.sid=$surveyid";
$cdump = BuildCSVFromQuery($cquery);

//7: Label Sets
$lsquery = "SELECT DISTINCT {$dbprefix}labelsets.lid, label_name, {$dbprefix}labelsets.languages 
            FROM {$dbprefix}labelsets, {$dbprefix}questions 
			WHERE ({$dbprefix}labelsets.lid={$dbprefix}questions.lid or {$dbprefix}labelsets.lid={$dbprefix}questions.lid1) 
			AND type IN ('F', 'H', 'W', 'Z', '1', ':', ';') 
			AND sid=$surveyid";
$lsdump = BuildCSVFromQuery($lsquery);

//8: Labels
$lquery = "SELECT {$dbprefix}labels.lid, {$dbprefix}labels.code, {$dbprefix}labels.title, {$dbprefix}labels.sortorder,{$dbprefix}labels.language,{$dbprefix}labels.assessment_value
           FROM {$dbprefix}labels, {$dbprefix}questions 
		   WHERE ({$dbprefix}labels.lid={$dbprefix}questions.lid or {$dbprefix}labels.lid={$dbprefix}questions.lid1) 
		   AND type in ('F', 'W', 'H', 'Z', '1', ':', ';') 
		   AND sid=$surveyid 
		   GROUP BY {$dbprefix}labels.lid, {$dbprefix}labels.code, {$dbprefix}labels.title, {$dbprefix}labels.sortorder,{$dbprefix}labels.language,{$dbprefix}labels.assessment_value";
$ldump = BuildCSVFromQuery($lquery);

//9: Question Attributes
$query = "SELECT {$dbprefix}question_attributes.qaid, {$dbprefix}question_attributes.qid, {$dbprefix}question_attributes.attribute,  {$dbprefix}question_attributes.value
          FROM {$dbprefix}question_attributes 
		  WHERE {$dbprefix}question_attributes.qid in (select qid from {$dbprefix}questions where sid=$surveyid group by qid)";
$qadump = BuildCSVFromQuery($query);

//10: Assessments;
$query = "SELECT {$dbprefix}assessments.* 
          FROM {$dbprefix}assessments 
		  WHERE {$dbprefix}assessments.sid=$surveyid";
$asdump = BuildCSVFromQuery($query);

//11: Quota;
$query = "SELECT {$dbprefix}quota.* 
          FROM {$dbprefix}quota 
		  WHERE {$dbprefix}quota.sid=$surveyid";
$quotadump = BuildCSVFromQuery($query);

//12: Quota Members;
$query = "SELECT {$dbprefix}quota_members.* 
          FROM {$dbprefix}quota_members 
		  WHERE {$dbprefix}quota_members.sid=$surveyid";
$quotamemdump = BuildCSVFromQuery($query);

//13: Quota languagesettings
$query = "SELECT {$dbprefix}quota_languagesettings.*
          FROM {$dbprefix}quota_languagesettings, {$dbprefix}quota
		  WHERE {$dbprefix}quota.id = {$dbprefix}quota_languagesettings.quotals_quota_id
		  AND {$dbprefix}quota.sid=$surveyid";
$quotalsdump = BuildCSVFromQuery($query);

$fn = "limesurvey_survey_$surveyid.csv";

header("Content-Type: application/download");
header("Content-Disposition: attachment; filename=$fn");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: cache");                          // HTTP/1.0

echo $dumphead, $sdump, $gdump, $qdump, $adump, $cdump, $lsdump, $ldump, $qadump, $asdump, $slsdump, $quotadump, $quotamemdump, $quotalsdump."\n";
exit;
?>
