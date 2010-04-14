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
 * $Id: export_structure_csv.php 8592 2010-04-14 12:23:25Z machaven $
 */



// DUMP THE RELATED DATA FOR A SINGLE SURVEY INTO AN XML FILE FOR IMPORTING LATER ON OR ON ANOTHER SURVEY SETUP
// DUMP ALL DATA FOR RELATED SID FROM THE FOLLOWING TABLES:
// Answers
// Assessments
// Conditions
// Default values
// Groups
// Questions
// Question attributes
// Quota
// Quota Members
// Surveys
// Surveys language settings

include_once("login_check.php");
require_once ("export_data_functions.php");

if (!isset($surveyid))
{
    $surveyid=returnglobal('sid');
}


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

function getXMLStructure($xmlwriter, $exclude=array())
{
    global $dbprefix, $surveyid;

    $sdump = "";

    if ((!empty($exclude) && $exclude['answers'] !== true) || empty($exclude))
    {
        //Answers table
        $aquery = "SELECT {$dbprefix}answers.*
           FROM {$dbprefix}answers, {$dbprefix}questions 
		   WHERE {$dbprefix}answers.language={$dbprefix}questions.language 
		   AND {$dbprefix}answers.qid={$dbprefix}questions.qid 
		   AND {$dbprefix}questions.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$aquery);
    }

    // Assessments
    $query = "SELECT {$dbprefix}assessments.*
          FROM {$dbprefix}assessments 
          WHERE {$dbprefix}assessments.sid=$surveyid";
    BuildXMLFromQuery($xmlwriter,$query);
    
    if ((!empty($exclude) && $exclude['conditions'] !== true) || empty($exclude))
    {
        //Conditions table
        $cquery = "SELECT DISTINCT {$dbprefix}conditions.*
           FROM {$dbprefix}conditions, {$dbprefix}questions 
		   WHERE {$dbprefix}conditions.qid={$dbprefix}questions.qid 
		   AND {$dbprefix}questions.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$cquery);
    }

    //Default values
    $query = "SELECT {$dbprefix}defaultvalues.*
          FROM {$dbprefix}defaultvalues 
          WHERE qid in (select qid from {$dbprefix}questions where sid=$surveyid group by qid)";
    BuildXMLFromQuery($xmlwriter,$query);
    
    // Groups 
    $gquery = "SELECT *
           FROM {$dbprefix}groups 
           WHERE sid=$surveyid 
           ORDER BY gid";
    BuildXMLFromQuery($xmlwriter,$gquery);
    
    //Questions    
    $qquery = "SELECT *
           FROM {$dbprefix}questions 
           WHERE sid=$surveyid 
           ORDER BY qid";
    BuildXMLFromQuery($xmlwriter,$qquery);
    
    //Question attributes
    $query = "SELECT {$dbprefix}question_attributes.qaid, {$dbprefix}question_attributes.qid, {$dbprefix}question_attributes.attribute,  {$dbprefix}question_attributes.value
          FROM {$dbprefix}question_attributes 
		  WHERE {$dbprefix}question_attributes.qid in (select qid from {$dbprefix}questions where sid=$surveyid group by qid)";
    BuildXMLFromQuery($xmlwriter,$query);

    if ((!empty($exclude) && $exclude['quotas'] !== true) || empty($exclude))
    {
        //Quota
        $query = "SELECT {$dbprefix}quota.*
          FROM {$dbprefix}quota 
		  WHERE {$dbprefix}quota.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$query);

        //1Quota members
        $query = "SELECT {$dbprefix}quota_members.*
          FROM {$dbprefix}quota_members 
		  WHERE {$dbprefix}quota_members.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$query);

        //Quota languagesettings
        $query = "SELECT {$dbprefix}quota_languagesettings.*
          FROM {$dbprefix}quota_languagesettings, {$dbprefix}quota
		  WHERE {$dbprefix}quota.id = {$dbprefix}quota_languagesettings.quotals_quota_id
		  AND {$dbprefix}quota.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$query);
    }
    
    // Surveys 
    $squery = "SELECT *
           FROM {$dbprefix}surveys 
           WHERE sid=$surveyid";
    BuildXMLFromQuery($xmlwriter,$squery);

    // Survey language settings 
    $slsquery = "SELECT *
             FROM {$dbprefix}surveys_languagesettings 
             WHERE surveyls_survey_id=$surveyid";
    BuildXMLFromQuery($xmlwriter,$slsquery);
    
}

if (!isset($copyfunction))
{
    $fn = "limesurvey_survey_$surveyid.lss";      
    header("Content-Type: text/html/force-download");
    header("Content-Disposition: attachment; filename=$fn");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: cache");                          // HTTP/1.0
    $xml =new XMLWriter();
    $xml->openURI('php://output');
    $xml->setIndent(true);
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('document');
    $xml->writeElement('LimeSurveyDocType','Survey');    
    $xml->writeElement('DBVersion',$dbversionnumber);    
    
    getXMLStructure($xml);
    $xml->endElement(); // close columns
    $xml->endDocument();
}

exit;
?>
