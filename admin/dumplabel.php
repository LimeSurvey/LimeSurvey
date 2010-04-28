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
require_once("export_data_functions.php");      
$lids=returnglobal('lids');
$lid=returnglobal('lid');
if (!$lid && !$lids) die('No LID has been provided. Cannot dump label set.');

if ($lid)
{
  $lids=array($lid);
}
$lids=array_map('sanitize_int',$lids);
                        
$fn = "limesurvey_labelset_".implode('_',$lids).".lsl";
$xml = new XMLWriter();             

header("Content-Type: text/html/force-download");
header("Content-Disposition: attachment; filename=$fn");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Pragma: cache");                          // HTTP/1.0

$xml->openURI('php://output');

$xml->setIndent(true);
$xml->startDocument('1.0', 'UTF-8');
$xml->startElement('document');
$xml->writeElement('LimeSurveyDocType','Label');    
$xml->writeElement('DBVersion',$dbversionnumber);
getXMLStructure($xml,$lids);
$xml->endElement(); // close columns
$xml->endDocument();
exit;

function getXMLStructure($xml,$lids)
{
    global $dbprefix;
    
   
    // Label sets table
    $lsquery = "SELECT * FROM {$dbprefix}labelsets WHERE lid=".implode(' or lid=',$lids);
    BuildXMLFromQuery($xml,$lsquery,'labelsets');

    // Labels
    $lquery = "SELECT lid, code, title, sortorder, language, assessment_value FROM {$dbprefix}labels WHERE lid=".implode(' or lid=',$lids);           
    BuildXMLFromQuery($xml,$lquery,'labels');
}

