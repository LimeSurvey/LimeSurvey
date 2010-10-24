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
//  - Questions
//  - Answers
//  - Question attributes
//  - Default values

//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}
include_once("login_check.php");
require_once("export_data_functions.php");      
if(!bHasSurveyPermission($surveyid,'surveycontent','export')) safe_die("You are not allowed to export questions.");
 

$qid = returnglobal('qid');

if (!$qid)
{
    safe_die("No QID has been provided. Cannot dump question.");
}

$fn = "limesurvey_question_$qid.lsq";      
$xml = getXMLWriter();  

if($action=='exportstructureLsrcCsvQuestion')
{
    include_once($homedir.'/remotecontrol/lsrc.config.php');
    //Select title as Filename and save
    $questionTitleSql = "SELECT title
                     FROM {$dbprefix}questions 
                     WHERE qid=$qid AND sid=$surveyid AND gid=$gid ";
    $questionTitle = $connect->GetOne($questionTitleSql);
    $xml->openURI('remotecontrol/'.$queDir.substr($questionTitle,0,20).".lsq");     
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
$xml->writeElement('LimeSurveyDocType','Question');    
$xml->writeElement('DBVersion',$dbversionnumber);    
$xml->startElement('languages');
$lquery = "SELECT language
           FROM {$dbprefix}questions 
           WHERE qid=$qid or parent_qid=$qid group by language";
$lresult=db_execute_assoc($lquery);           
while ($row=$lresult->FetchRow())   
{
    $xml->writeElement('language',$row['language']);    
}
$xml->endElement();
getXMLStructure($xml,$qid);
$xml->endElement(); // close columns
$xml->endDocument();
exit;

function getXMLStructure($xml,$qid)
{
    global $dbprefix;
    // Questions table
    $qquery = "SELECT *
               FROM {$dbprefix}questions 
               WHERE qid=$qid and parent_qid=0 order by language, scale_id, question_order";
    BuildXMLFromQuery($xml,$qquery);

    // Questions table - Subquestions
    $qquery = "SELECT *
               FROM {$dbprefix}questions 
               WHERE parent_qid=$qid order by language, scale_id, question_order";
    BuildXMLFromQuery($xml,$qquery,'subquestions');
    
    
    // Answers table
    $aquery = "SELECT *
               FROM {$dbprefix}answers 
               WHERE qid = $qid order by language, scale_id, sortorder";
    BuildXMLFromQuery($xml,$aquery);

    // Question attributes
    $query = "SELECT *
              FROM {$dbprefix}question_attributes 
              WHERE qid=$qid order by qid, attribute";
    BuildXMLFromQuery($xml,$query);

    // Default values
    $query = "SELECT *
              FROM {$dbprefix}defaultvalues 
              WHERE qid=$qid  order by language, scale_id";
    BuildXMLFromQuery($xml,$query);              

}

