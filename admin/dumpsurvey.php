<?php
/*
#############################################################
# >>> PHPSurveyor  										#
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


// DUMP THE RELATED DATA FOR A SINGLE SURVEY INTO A SQL FILE FOR IMPORTING LATER ON OR ON ANOTHER SURVEY SETUP
// DUMP ALL DATA WITH RELATED SID FROM THE FOLLOWING TABLES
// 1. Surveys
// 2. Groups
// 3. Questions
// 4. Answers
// 5. Conditions 
// 6. Label Sets
// 7. Labels
// 8. Question Attributes
// 9. Assessments

require_once(dirname(__FILE__).'/../config.php');

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

include_once("login_check.php");


if (!$surveyid)
{
	echo $htmlheader
	."<br />\n"
	."<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
	."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	.$clang->gT("Export Survey")."</strong></td></tr>\n"
	."\t<tr><td align='center'>\n"
	."$setfont<br /><strong><font color='red'>"
	.$clang->gT("Error")."</font></strong><br />\n"
	.$clang->gT("No SID has been provided. Cannot dump survey")."<br />\n"
	."<br /><input type='submit' value='"
	.$clang->gT("Main Admin Screen")."' onClick=\"window.open('$scriptname', '_top')\">\n"
	."\t</td></tr>\n"
	."</table>\n"
	."</body></html>\n";
	exit;
}

$dumphead = "# PHPSurveyor Survey Dump\n"
        . "# DBVersion $dbversionnumber\n"
        . "# This is a dumped survey from the PHPSurveyor Script\n"
        . "# http://www.phpsurveyor.org/\n"
        . "# Do not change this header!\n";

//1: Surveys table
$squery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid";
$sdump = BuildCSVFromQuery($squery);

//2: Surveys Languagsettings table
$slsquery = "SELECT * FROM {$dbprefix}surveys_languagesettings WHERE surveyls_survey_id=$surveyid";
$slsdump = BuildCSVFromQuery($slsquery);

//3: Groups Table
$gquery = "SELECT * FROM {$dbprefix}groups WHERE sid=$surveyid";
$gdump = BuildCSVFromQuery($gquery);

//4: Questions Table
$qquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid";
$qdump = BuildCSVFromQuery($qquery);

//5: Answers table
$aquery = "SELECT {$dbprefix}answers.* FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}questions.sid=$surveyid";
$adump = BuildCSVFromQuery($aquery);

//6: Conditions table
$cquery = "SELECT {$dbprefix}conditions.* FROM {$dbprefix}conditions, {$dbprefix}questions WHERE {$dbprefix}conditions.qid={$dbprefix}questions.qid AND {$dbprefix}questions.sid=$surveyid";
$cdump = BuildCSVFromQuery($cquery);

//7: Label Sets
$lsquery = "SELECT DISTINCT {$dbprefix}labelsets.lid, label_name, {$dbprefix}labelsets.languages FROM {$dbprefix}labelsets, {$dbprefix}questions WHERE {$dbprefix}labelsets.lid={$dbprefix}questions.lid AND type IN ('F', 'H', 'W', 'Z') AND sid=$surveyid";
$lsdump = BuildCSVFromQuery($lsquery);

//8: Labels
$lquery = "SELECT DISTINCT {$dbprefix}labels.lid, {$dbprefix}labels.code, {$dbprefix}labels.title, {$dbprefix}labels.sortorder,{$dbprefix}labels.language FROM {$dbprefix}labels, {$dbprefix}questions WHERE {$dbprefix}labels.lid={$dbprefix}questions.lid AND type in ('F', 'W', 'H', 'Z') AND sid=$surveyid";
$ldump = BuildCSVFromQuery($lquery);

//9: Question Attributes
$query = "SELECT {$dbprefix}question_attributes.* FROM {$dbprefix}question_attributes, {$dbprefix}questions WHERE {$dbprefix}question_attributes.qid={$dbprefix}questions.qid AND {$dbprefix}questions.sid=$surveyid";
$qadump = BuildCSVFromQuery($query);

//10: Assessments;
$query = "SELECT {$dbprefix}assessments.* FROM {$dbprefix}assessments WHERE {$dbprefix}assessments.sid=$surveyid";
$asdump = BuildCSVFromQuery($query);

$fn = "phpsurveyor_survey_$surveyid.csv";

header("Content-Type: application/download");
header("Content-Disposition: attachment; filename=$fn");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
Header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache");                          // HTTP/1.0

echo $dumphead, $sdump, $gdump, $qdump, $adump, $cdump, $lsdump, $ldump, $qadump, $asdump, $slsdump."\n";
?>
