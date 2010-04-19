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

//Ensure script is not run directly, avoid path disclosure
include_once("login_check.php");


// A FILE TO IMPORT A DUMPED SURVEY FILE, AND CREATE A NEW SURVEY
if ($action == 'importsurvey')
{
    $importsurvey = "<div class='header'>".$clang->gT("Import Survey")."</div>\n";


$the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];

if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
{
    $importsurvey .= "<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n";
    $importsurvey .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$tempdir)."<br /><br />\n";
    $importsurvey .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\"><br /><br />\n";
    return;
}

// IF WE GOT THIS FAR, THEN THE FILE HAS BEEN UPLOADED SUCCESFULLY

$importsurvey .= "<div class='messagebox'><div class='successheader'>".$clang->gT("Success")."</div>&nbsp;<br />\n";
$importsurvey .= $clang->gT("File upload succeeded.")."<br /><br />\n";
$importsurvey .= $clang->gT("Reading file..")."<br />\n";

$importingfrom = "http";	// "http" for the web version and "cmdline" for the command line version
} elseif ($action == 'copysurvey')
{
    $importsurvey = "<div class='header'>".$clang->gT("Copy Survey")."</div>\n";
    $surveyid = sanitize_int($_POST['copysurveylist']);
    $exclude = array();
    require_once("../classes/inputfilter/class.inputfilter_clean.php");
    $myFilter = new InputFilter('','',1,1,1);
    if ($filterxsshtml)
    {
        $surveyname = $myFilter->process($_POST['copysurveyname']);
    } else {
        $surveyname = html_entity_decode($_POST['copysurveyname'], ENT_QUOTES, "UTF-8");
    }
    if (isset($_POST['copysurveyexcludequotas']) && $_POST['copysurveyexcludequotas'] == "on")
    {
        $exclude['quotas'] = true;
    }
    if (isset($_POST['copysurveyexcludeanswers']) && $_POST['copysurveyexcludeanswers'] == "on")
    {
        $exclude['answers'] = true;
    }
    if (isset($_POST['copysurveyresetconditions']) && $_POST['copysurveyresetconditions'] == "on")
    {
        $exclude['conditions'] = true;
    }
    $copyfunction = 1;
    include("export_structure_xml.php");
    $copysurveydata = getXMLData();
    die(print_r($copysurveydata));
}

include("importsurvey.php");

?>
