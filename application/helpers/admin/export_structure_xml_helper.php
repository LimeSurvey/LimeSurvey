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
 * $Id: export_structure_xml.php 10193 2011-06-05 12:20:37Z c_schmitz $
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
/**
include_once("login_check.php");
require_once ("export_data_functions.php");

*/

$CI =& get_instance(); 
$CI->load->helper('admin/export_data');
/**
if (!isset($surveyid))
{
    $surveyid=returnglobal('sid');
}

$clang = $CI->limesurvey_lang;
if (!$surveyid)
{
    
     * echo $htmlheader
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
} */

function getXMLStructure($xmlwriter, $exclude=array(),$surveyid)
{
    //global $surveyid;

    $sdump = "";
    $CI =& get_instance(); 
    if ((!isset($exclude) && $exclude['answers'] !== true) || empty($exclude))
    {
        //Answers table 

        $aquery = "SELECT ".$CI->db->dbprefix."answers.*
           FROM ".$CI->db->dbprefix."answers, ".$CI->db->dbprefix."questions 
		   WHERE ".$CI->db->dbprefix."answers.language=".$CI->db->dbprefix."questions.language 
		   AND ".$CI->db->dbprefix."answers.qid=".$CI->db->dbprefix."questions.qid 
		   AND ".$CI->db->dbprefix."questions.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$aquery);
    }

    // Assessments
    $query = "SELECT ".$CI->db->dbprefix."assessments.*
          FROM ".$CI->db->dbprefix."assessments 
          WHERE ".$CI->db->dbprefix."assessments.sid=$surveyid";
    BuildXMLFromQuery($xmlwriter,$query);
    
    if ((!isset($exclude) && $exclude['conditions'] !== true) || empty($exclude))
    {
        //Conditions table
        $cquery = "SELECT DISTINCT ".$CI->db->dbprefix."conditions.*
           FROM ".$CI->db->dbprefix."conditions, ".$CI->db->dbprefix."questions 
		   WHERE ".$CI->db->dbprefix."conditions.qid=".$CI->db->dbprefix."questions.qid 
		   AND ".$CI->db->dbprefix."questions.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$cquery);
    }

    //Default values
    $query = "SELECT ".$CI->db->dbprefix."defaultvalues.*
          FROM ".$CI->db->dbprefix."defaultvalues JOIN ".$CI->db->dbprefix."questions ON ".$CI->db->dbprefix."questions.qid = ".$CI->db->dbprefix."defaultvalues.qid AND ".$CI->db->dbprefix."questions.sid=$surveyid AND ".$CI->db->dbprefix."questions.language=".$CI->db->dbprefix."defaultvalues.language "; 
    
    BuildXMLFromQuery($xmlwriter,$query);
    
    // Groups 
    $gquery = "SELECT *
           FROM ".$CI->db->dbprefix."groups 
           WHERE sid=$surveyid 
           ORDER BY gid";
    BuildXMLFromQuery($xmlwriter,$gquery);
    
    //Questions    
    $qquery = "SELECT *
           FROM ".$CI->db->dbprefix."questions 
           WHERE sid=$surveyid and parent_qid=0 
           ORDER BY qid";
    BuildXMLFromQuery($xmlwriter,$qquery);

    //Subquestions    
    $qquery = "SELECT *
           FROM ".$CI->db->dbprefix."questions 
           WHERE sid=$surveyid and parent_qid>0
           ORDER BY qid";
    BuildXMLFromQuery($xmlwriter,$qquery,'subquestions');

    //Question attributes
    $sBaseLanguage=GetBaseLanguageFromSurveyID($surveyid);
    if ($CI->db->dbdriver == 'odbc_mssql' || $CI->db->dbdriver == 'odbtp' || $CI->db->dbdriver == 'mssql_n' || $CI->db->dbdriver =='mssqlnative')
    {
        $query="SELECT qa.qid, qa.attribute, cast(qa.value as varchar(4000)) as value 
          FROM ".$CI->db->dbprefix."question_attributes qa JOIN ".$CI->db->dbprefix."questions  q ON q.qid = qa.qid AND q.sid={$surveyid} 
          where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute,  cast(qa.value as varchar(4000))";
    }
    else {
        $query="SELECT qa.qid, qa.attribute, qa.value
          FROM ".$CI->db->dbprefix."question_attributes qa JOIN ".$CI->db->dbprefix."questions  q ON q.qid = qa.qid AND q.sid={$surveyid} 
          where q.language='{$sBaseLanguage}' group by qa.qid, qa.attribute, qa.value";
    }
    
    BuildXMLFromQuery($xmlwriter,$query,'question_attributes');

    if ((!isset($exclude) && $exclude['quotas'] !== true) || empty($exclude))
    {
        //Quota
        $query = "SELECT ".$CI->db->dbprefix."quota.*
          FROM ".$CI->db->dbprefix."quota 
		  WHERE ".$CI->db->dbprefix."quota.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$query);

        //1Quota members
        $query = "SELECT ".$CI->db->dbprefix."quota_members.*
          FROM ".$CI->db->dbprefix."quota_members 
		  WHERE ".$CI->db->dbprefix."quota_members.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$query);

        //Quota languagesettings
        $query = "SELECT ".$CI->db->dbprefix."quota_languagesettings.*
          FROM ".$CI->db->dbprefix."quota_languagesettings, ".$CI->db->dbprefix."quota
		  WHERE ".$CI->db->dbprefix."quota.id = ".$CI->db->dbprefix."quota_languagesettings.quotals_quota_id
		  AND ".$CI->db->dbprefix."quota.sid=$surveyid";
        BuildXMLFromQuery($xmlwriter,$query);
    }
    
    // Surveys 
    $squery = "SELECT *
           FROM ".$CI->db->dbprefix."surveys 
           WHERE sid=$surveyid";
    //Exclude some fields from the export
    BuildXMLFromQuery($xmlwriter,$squery,'',array('owner_id','active','datecreated'));

    // Survey language settings 
    $slsquery = "SELECT *
             FROM ".$CI->db->dbprefix."surveys_languagesettings 
             WHERE surveyls_survey_id=$surveyid";
    BuildXMLFromQuery($xmlwriter,$slsquery);
    
}

function getXMLData($exclude = array(),$surveyid)
{
    global $dbversionnumber;
    $xml = getXMLWriter();
    $xml->openMemory();
    $xml->setIndent(true);
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement('document');
    $xml->writeElement('LimeSurveyDocType','Survey');    
    $xml->writeElement('DBVersion',$dbversionnumber);
    $xml->startElement('languages');
    $surveylanguages=GetAdditionalLanguagesFromSurveyID($surveyid);
    $surveylanguages[]=GetBaseLanguageFromSurveyID($surveyid);
    foreach ($surveylanguages as $surveylanguage)   
    {
        $xml->writeElement('language',$surveylanguage);    
    }
    $xml->endElement();    
    getXMLStructure($xml,$exclude,$surveyid);
    $xml->endElement(); // close columns
    $xml->endDocument();
    return $xml->outputMemory(true);
}
/**
if (!isset($copyfunction))
{
    $fn = "limesurvey_survey_$surveyid.lss";      
    header("Content-Type: text/xml");
    header("Content-Disposition: attachment; filename=$fn");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Pragma: public");                          // HTTP/1.0
    echo getXMLData();
    exit;
} */
?>
