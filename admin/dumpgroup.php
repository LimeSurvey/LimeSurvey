<?php
/*
#############################################################
# >>> PHPSurveyor  											#
#############################################################
# > Author:  Jason Cleeland									#
# > E-mail:  jason@cleeland.org								#
# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
# >          CARLTON SOUTH 3053, AUSTRALIA					#
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

require_once(dirname(__FILE__).'/../config.php');
$gid = returnglobal('gid');
$surveyid = returnglobal('sid');

//Ensure script is not run directly, avoid path disclosure
if (empty($gid)) {die ("Cannot run this script directly");}

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

$fn = "phpsurveyor_group_$gid.csv";

$dumphead = "# PHPSurveyor Group Dump\n"
        . "# DBVersion $dbversionnumber\n"
        . "# This is a dumped group from the PHPSurveyor Script\n"
        . "# http://www.phpsurveyor.org/\n"
        . "# Do not change this header!\n";


header("Content-Type: application/download");
header("Content-Disposition: attachment; filename=$fn");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // always modified
Header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: no-cache");

//0: Groups Table
$gquery = "SELECT * FROM {$dbprefix}groups WHERE gid=$gid";
$gdump = BuildCSVFromQuery($gquery);

//1: Questions Table
$qquery = "SELECT * FROM {$dbprefix}questions WHERE gid=$gid";
$qdump = BuildCSVFromQuery($qquery);

//2: Answers table
$aquery = "SELECT {$dbprefix}answers.* FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}questions.gid=$gid";
$adump = BuildCSVFromQuery($aquery);

//3: Conditions table - THIS CAN ONLY EXPORT CONDITIONS THAT RELATE TO THE SAME GROUP
$cquery = "SELECT {$dbprefix}conditions.* FROM {$dbprefix}conditions, {$dbprefix}questions, {$dbprefix}questions b WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid AND {$dbprefix}conditions.qid=b.qid AND {$dbprefix}questions.sid=$surveyid AND {$dbprefix}questions.gid=$gid AND b.gid=$gid";
$cdump = BuildCSVFromQuery($cquery);

//4: Labelsets Table
$lsquery = "SELECT DISTINCT {$dbprefix}labelsets.lid, label_name FROM {$dbprefix}labelsets, {$dbprefix}questions WHERE {$dbprefix}labelsets.lid={$dbprefix}questions.lid AND type in ('F', 'H', 'W', 'Z') AND gid=$gid";
$lsdump = BuildCSVFromQuery($lsquery);

//5: Labels Table
$lquery = "SELECT DISTINCT {$dbprefix}labels.lid, {$dbprefix}labels.code, {$dbprefix}labels.title, {$dbprefix}labels.sortorder FROM {$dbprefix}labels, {$dbprefix}questions WHERE {$dbprefix}labels.lid={$dbprefix}questions.lid AND type='F' AND gid=$gid";
$ldump = BuildCSVFromQuery($lquery);

//8: Question Attributes
$query = "SELECT {$dbprefix}question_attributes.* FROM {$dbprefix}question_attributes, {$dbprefix}questions WHERE {$dbprefix}question_attributes.qid={$dbprefix}questions.qid AND {$dbprefix}questions.sid=$surveyid AND {$dbprefix}questions.gid=$gid";
$qadump = BuildCSVFromQuery($query);
// HTTP/1.0
echo $dumphead, $gdump, $qdump, $adump, $cdump, $lsdump, $ldump, $qadump;

?>
