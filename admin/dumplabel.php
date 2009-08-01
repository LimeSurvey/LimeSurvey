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

//Ensure script is not run directly, avoid path disclosure
include_once("login_check.php");
$lid=returnglobal('lid');
if (!$lid)
{
	echo "<br />\n";
	echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>".$clang->gT("Export Label Set")."</strong></td></tr>\n";
	echo "\t<tr bgcolor='#CCCCCC'><td align='center'>\n";
	echo "<br /><strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n".$clang->gT("No LID has been provided. Cannot dump label set.")."<br />\n";
	echo "<br /><input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n";
	echo "\t</td></tr>\n";
	echo "</table>\n";
	echo "</body></html>\n";
	exit;
}

$dumphead = "# LimeSurvey Label Set Dump\n"
. "# DBVersion $dbversionnumber\n"
. "# This is a dumped label set from the LimeSurvey Script\n"
. "# http://www.limesurvey.org/\n"
. "# Do not change this header!\n";

//1: Questions Table
$qquery = "SELECT * FROM {$dbprefix}labelsets WHERE lid=$lid";
$qdump = BuildCSVFromQuery($qquery);

//2: Answers table
$aquery = "SELECT lid, code, title, sortorder, language, assessment_value FROM {$dbprefix}labels WHERE lid=$lid";
$adump = BuildCSVFromQuery($aquery);

$fn = "limesurvey_labelset_$lid.csv";


header("Content-Type: application/download");
header("Content-Disposition: attachment; filename=$fn");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: cache");                          // HTTP/1.0

echo $dumphead, $qdump, $adump;
exit;

?>
