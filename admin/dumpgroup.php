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
if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}
include_once("login_check.php");
require_once("export_data_functions.php");      
if(!bHasRight($surveyid,'export')) safe_die("You are not allowed to export question groups."); 

$gid = returnglobal('gid');
$surveyid = returnglobal('sid');

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

$fn = "limesurvey_group_$gid.lsg";
$xml = new XMLWriter();    



if($action=='exportstructureLsrcCsvGroup')
{
    include_once($homedir.'/remotecontrol/lsrc.config.php');
    //Select group_name as Filename and save
    $groupTitleSql = "SELECT group_name
                     FROM {$dbprefix}groups 
                     WHERE sid=$surveyid AND gid=$gid ";
    $groupTitle = $connect->GetOne($groupTitleSql);
    $xml->openURI('remotecontrol/'.$queDir.substr($groupTitle,0,20).".lsq");     
}
else
{
    header("Content-Type: text/html/force-download");
    header("Content-Disposition: attachment; filename=$fn");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: cache");                          // HTTP/1.0

    $xml->openURI('php://output');
}

$xml->setIndent(true);
$xml->startDocument('1.0', 'UTF-8');
$xml->startElement('document');
$xml->writeElement('LimeSurveyDocType','Group');    
$xml->writeElement('DBVersion',$dbversionnumber);
$xml->startElement('languages');
$lquery = "SELECT language
           FROM {$dbprefix}groups 
           WHERE gid=$gid group by language";
$lresult=db_execute_assoc($lquery);           
while ($row=$lresult->FetchRow())   
{
    $xml->writeElement('language',$row['language']);    
}
$xml->endElement();
getXMLStructure($xml,$gid);
$xml->endElement(); // close columns
$xml->endDocument();
exit;


function getXMLStructure($xml,$gid)
{
    global $dbprefix; 
    // Groups 
    $gquery = "SELECT *
               FROM {$dbprefix}groups 
               WHERE gid=$gid";
    BuildXMLFromQuery($xml,$gquery);                

    // Questions table
    $qquery = "SELECT *
               FROM {$dbprefix}questions 
               WHERE gid=$gid and parent_qid=0 order by question_order, language, scale_id";
    BuildXMLFromQuery($xml,$qquery);

    // Questions table - Subquestions
    $qquery = "SELECT *
               FROM {$dbprefix}questions 
               WHERE gid=$gid and parent_qid>0 order by question_order, language, scale_id";
    BuildXMLFromQuery($xml,$qquery,'subquestions');    
    
    //Answers 
    $aquery = "SELECT DISTINCT {$dbprefix}answers.*
               FROM {$dbprefix}answers, {$dbprefix}questions 
               WHERE ({$dbprefix}answers.qid={$dbprefix}questions.qid) 
               AND ({$dbprefix}questions.gid=$gid)";
    BuildXMLFromQuery($xml,$aquery);

    //Conditions - THIS CAN ONLY EXPORT CONDITIONS THAT RELATE TO THE SAME GROUP
    $cquery = "SELECT DISTINCT c.*
               FROM {$dbprefix}conditions c, {$dbprefix}questions q, {$dbprefix}questions b 
               WHERE (c.cqid=q.qid) 
               AND (c.qid=b.qid) 
               AND (q.gid=$gid) 
               AND (b.gid=$gid)";
    BuildXMLFromQuery($xml,$cquery,'conditions');

    //Question attributes
    $query = "SELECT DISTINCT qa.*
              FROM {$dbprefix}question_attributes qa, {$dbprefix}questions q 
              WHERE (qa.qid=q.qid) 
              AND (q.gid=$gid)";
    BuildXMLFromQuery($xml,$query,'question_attributes');
    
    // Default values
    $query = "SELECT DISTINCT dv.*
              FROM {$dbprefix}defaultvalues dv, {$dbprefix}questions q  
              WHERE dv.qid=q.qid 
              AND q.gid=$gid order by dv.language, dv.scale_id";
    BuildXMLFromQuery($xml,$query,'defaultvalues');                 
}
