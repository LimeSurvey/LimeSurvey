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

$qid = returnglobal('qid');

include_once("login_check.php");

//echo $htmlheader;
if (!$qid)
{
    echo $htmlheader;
    echo "<br />\n";
    echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
    echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>".$clang->gT("Export Question")."</strong></td></tr>\n";
    echo "\t<tr bgcolor='#CCCCCC'><td align='center'>\n";
    echo "<br /><strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n".$clang->gT("No QID has been provided. Cannot dump question.")."<br />\n";
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
    $QueryResult = db_execute_assoc($Query) or safe_die ("ERROR: $QueryResult<br />".$connect->ErrorMsg());
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
$qquery = "SELECT *
           FROM {$dbprefix}questions 
		   WHERE qid=$qid";
$qdump = BuildCSVFromQuery($qquery);

//2: Answers table
$aquery = "SELECT {$dbprefix}answers.*
           FROM {$dbprefix}answers 
		   WHERE {$dbprefix}answers.qid = $qid";
$adump = BuildCSVFromQuery($aquery);

//3: Labelsets Table
//$lsquery = "SELECT DISTINCT {$dbprefix}labelsets.* FROM {$dbprefix}labelsets, {$dbprefix}questions WHERE {$dbprefix}labelsets.lid={$dbprefix}questions.lid AND type='F' AND qid=$qid";
$lsquery = "SELECT DISTINCT {$dbprefix}labelsets.*
            FROM {$dbprefix}labelsets, {$dbprefix}questions 
			WHERE {$dbprefix}labelsets.lid={$dbprefix}questions.lid 
			AND type in ('F', 'W', 'H', 'Z', '1', ':', ';') 
			AND qid=$qid";
$lsdump = BuildCSVFromQuery($lsquery);

//4: Labels Table
$lquery = "SELECT DISTINCT {$dbprefix}labels.*
		   FROM {$dbprefix}labels, {$dbprefix}questions 
		   WHERE {$dbprefix}labels.lid={$dbprefix}questions.lid 
		   AND type in ('F', 'W', 'H', 'Z', '1', ':', ';') 
		   AND qid=$qid";
$ldump = BuildCSVFromQuery($lquery);

//4: Labelsets1 Table
//This exists specifically to deal with dual-scale questions (or any future question that may have 2 labelsets)
$lsquery = "SELECT DISTINCT {$dbprefix}labelsets.* FROM {$dbprefix}labelsets, {$dbprefix}questions WHERE {$dbprefix}labelsets.lid={$dbprefix}questions.lid1 AND type in ('1') AND qid=$qid";
$ls1dump = BuildCSVFromQuery($lsquery);
$ls1=explode("\n", trim($ls1dump));

if(count($ls1)>3) {
    //If there is actually some data here, then add just the data (not the headers) into
    // $ls1dump - which will be outputted directly after $lsdump
    $ls1dump=$ls1[4];
    $ls1dump .= "\n";
} else {
    //If there is no data then make it an empty string.
    $ls1dump = "";
}
//4a: Labels1 Table
// See explanation for Labelsets1 Table!! These are the actual labels
$lquery = "SELECT DISTINCT {$dbprefix}labels.* FROM {$dbprefix}labels, {$dbprefix}questions WHERE {$dbprefix}labels.lid={$dbprefix}questions.lid1 AND type in ('1') AND qid=$qid";
$l1dump = BuildCSVFromQuery($lquery);
$ld1=explode("\n", trim($l1dump));

if(count($ld1)>3) {
    //If there is actually some data here, then add just the data (not the headers) into
    // $l1dump - which will be outputted directly after $ldump
    $l1dump=array();
    foreach($ld1 as $key=>$ld) {
        //Put every line, other than the first three into this string
        if($key > 3) {
            $l1dump[]=$ld;
        }
    }
    $l1dump = implode("\n", $l1dump);
    $l1dump .= "\n";
} else {
    //If there is no data then make it an empty string.
    $l1dump = "";
}


//5: Question Attributes
$query = "SELECT {$dbprefix}question_attributes.*
	      FROM {$dbprefix}question_attributes 
		  WHERE {$dbprefix}question_attributes.qid=$qid";
$qadump = BuildCSVFromQuery($query);
$fn = "limesurvey_question_$qid.csv";

if($action=='exportstructureLsrcCsvQuestion')
{
    include_once($homedir.'/remotecontrol/lsrc.config.php');
    $lsrcString = $dumphead. $qdump. $adump. $lsdump. $ls1dump. $ldump. $l1dump. $qadump;
    //Select title as Filename and save
    $questionTitleSql = "SELECT title
		             FROM {$dbprefix}questions 
					 WHERE qid=$qid AND sid=$surveyid AND gid=$gid ";
    $questionTitleRs = db_execute_assoc($questionTitleSql);
    $questionTitle = $questionTitleRs->FetchRow();
    file_put_contents("remotecontrol/".$queDir.substr($questionTitle['title'],0,20).".csv",$lsrcString);
}
else
{
    header("Content-Type: application/download");
    header("Content-Disposition: attachment; filename=$fn");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: cache");                          // HTTP/1.0
    echo $dumphead, $qdump, $adump, $lsdump, $ls1dump, $ldump, $l1dump, $qadump;
    exit;
}
?>
