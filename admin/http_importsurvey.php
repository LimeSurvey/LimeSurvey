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

// Ensure script is not run directly, avoid path disclosure
include_once("login_check.php");

// Enable 'Convert resource links and INSERTANS fields?' if selected
if ( (isset($_POST['copysurveytranslinksfields']) && $_POST['copysurveytranslinksfields'] == "on")  || (isset($_POST['translinksfields']) && $_POST['translinksfields'] == "on"))
{
    $sTransLinks = true;    
}

// Start the HTML
if ($action == 'importsurvey')
{
    $importsurvey = "<div class='header ui-widget-header'>".$clang->gT("Import survey")."</div>\n";
    $importingfrom = "http";
}
elseif($action == 'copysurvey')
{
    $importsurvey = "<div class='header ui-widget-header'>".$clang->gT("Copy survey")."</div>\n";
    $copyfunction= true;
}

// Start traitment and messagebox
$importsurvey .= "<div class='messagebox ui-corner-all'>\n";
$importerror=false; // Put a var for continue

if ($action == 'importsurvey')
{
    $the_full_file_path = $tempdir . "/" . $_FILES['the_file']['name'];
    if (!@move_uploaded_file($_FILES['the_file']['tmp_name'], $the_full_file_path))
    {
        $importsurvey .= "<div class='errorheader'>".$clang->gT("Error")."</div>\n";
        $importsurvey .= sprintf ($clang->gT("An error occurred uploading your file. This may be caused by incorrect permissions in your %s folder."),$tempdir)."<br /><br />\n";
        $importsurvey .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\"><br /><br />\n";
        $importerror=true;
    } 
    else
    {
        $importsurvey .= "<div class='successheader'>".$clang->gT("Success")."</div>&nbsp;<br />\n";
        $importsurvey .= $clang->gT("File upload succeeded.")."<br /><br />\n";
        $importsurvey .= $clang->gT("Reading file..")."<br />\n";
        $sFullFilepath=$the_full_file_path;
        $aPathInfo = pathinfo($sFullFilepath);
        if (isset($aPathInfo['extension']))
        {
            $sExtension = $aPathInfo['extension'];
        }
        else
        {
            $sExtension = "";
        }

    }
    if (!$importerror && (strtolower($sExtension)!='csv' && strtolower($sExtension)!='lss'))
    {
        $importsurvey .= "<div class='errorheader'>".$clang->gT("Error")."</div>\n";
        $importsurvey .= $clang->gT("Import failed. You specified an invalid file type.")."\n";
        $importerror=true;
    }
}
elseif ($action == 'copysurvey')
{
    $surveyid = sanitize_int($_POST['copysurveylist']);
    $exclude = array();
    if (get_magic_quotes_gpc()) {$sNewSurveyName = stripslashes($_POST['copysurveyname']);}
    else{
        $sNewSurveyName=$_POST['copysurveyname'];
    }

    require_once("../classes/inputfilter/class.inputfilter_clean.php");
    $myFilter = new InputFilter('','',1,1,1);
    if ($filterxsshtml)
    {
        $sNewSurveyName = $myFilter->process($sNewSurveyName);
    } else {
        $sNewSurveyName = html_entity_decode($sNewSurveyName, ENT_QUOTES, "UTF-8");
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
    include("export_structure_xml.php");
    $copysurveydata = getXMLData($exclude);
}

// Now, we have the survey : start importing
require_once('import_functions.php');

if ($action == 'importsurvey' && !$importerror)
{
    if (isset($sExtension) && strtolower($sExtension)=='csv')
    {
        $aImportResults=CSVImportSurvey($sFullFilepath);
    }
    elseif (isset($sExtension) && strtolower($sExtension)=='lss')
    {
        $aImportResults=XMLImportSurvey($sFullFilepath,null,null, null,(isset($_POST['translinksfields'])));
    }
    else
    {
        $importerror = true;
    }
}
elseif ($action == 'copysurvey' && !$importerror)
{
    $aImportResults=XMLImportSurvey('',$copysurveydata,$sNewSurveyName);
}
else
{
    $importerror=true;
}

if (isset($aImportResults['error']) && $aImportResults['error']!=false)
{
    $importsurvey .= "<div class='warningheader'>".$clang->gT("Error")."</div><br />\n";
    $importsurvey .= $aImportResults['error']."<br /><br />\n";
    $importsurvey .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";
    $importerror = true;
}

if (!$importerror)
{
    $importsurvey .= "<br />\n<div class='successheader'>".$clang->gT("Success")."</div><br /><br />\n";
    if ($action == 'importsurvey')
    {
        $importsurvey .= "<strong>".$clang->gT("Survey copy summary")."</strong><br />\n";        
    }
    elseif($action == 'copysurvey')
    {
        $importsurvey .= "<strong>".$clang->gT("Survey import summary")."</strong><br />\n";        
    }
    
    $importsurvey .= "<ul style=\"text-align:left;\">\n\t<li>".$clang->gT("Surveys").": {$aImportResults['surveys']}</li>\n";
    $importsurvey .= "\t<li>".$clang->gT("Languages").": {$aImportResults['languages']}</li>\n";
    $importsurvey .= "\t<li>".$clang->gT("Question groups").": {$aImportResults['groups']}</li>\n";
    $importsurvey .= "\t<li>".$clang->gT("Questions").": {$aImportResults['questions']}</li>\n";
    $importsurvey .= "\t<li>".$clang->gT("Answers").": {$aImportResults['answers']}</li>\n";
    if (isset($aImportResults['subquestions']))
    {
        $importsurvey .= "\t<li>".$clang->gT("Subquestions").": {$aImportResults['subquestions']}</li>\n";     
    }
    if (isset($aImportResults['defaultvalues']))
    {
        $importsurvey .= "\t<li>".$clang->gT("Default answers").": {$aImportResults['defaultvalues']}</li>\n";     
    }
    if (isset($aImportResults['conditions']))
    {
        $importsurvey .= "\t<li>".$clang->gT("Conditions").": {$aImportResults['conditions']}</li>\n";     
    }
    if (isset($aImportResults['labelsets']))
    {
        $importsurvey .= "\t<li>".$clang->gT("Label sets").": {$aImportResults['labelsets']}</li>\n";
    }
    if (isset($aImportResults['deniedcountls']) && $aImportResults['deniedcountls']>0)
    {
        $importsurvey .= "\t<li>".$clang->gT("Not imported label sets").": {$aImportResults['deniedcountls']} ".$clang->gT("(Label sets were not imported since you do not have the permission to create new label sets.)")."</li>\n";
    }
    $importsurvey .= "\t<li>".$clang->gT("Question attributes").": {$aImportResults['question_attributes']}</li>\n";
    $importsurvey .= "\t<li>".$clang->gT("Assessments").": {$aImportResults['assessments']}</li>\n";
    $importsurvey .= "\t<li>".$clang->gT("Quotas").": {$aImportResults['quota']} ({$aImportResults['quotamembers']} ".$clang->gT("quota members")." ".$clang->gT("and")." {$aImportResults['quotals']} ".$clang->gT("quota language settings").")</li>\n</ul><br />\n";
    
    if (count($aImportResults['importwarnings'])>0) 
    {
        $importsurvey .= "<div class='warningheader'>".$clang->gT("Warnings").":</div><ul style=\"text-align:left;\">";
        foreach ($aImportResults['importwarnings'] as $warning)
        {
            $importsurvey .='<li>'.$warning.'</li>';
        }
        $importsurvey .= "</ul><br />\n";
    }
    
    if ($action == 'importsurvey')
    {
        $importsurvey .= "<strong>".$clang->gT("Import of Survey is completed.")."</strong><br />\n"
        . "<a href='$scriptname?sid={$aImportResults['newsid']}'>".$clang->gT("Go to survey")."</a><br />\n";       
    }
    elseif($action == 'copysurvey')
    {
        $importsurvey .= "<strong>".$clang->gT("Copy of survey is completed.")."</strong><br />\n"
        . "<a href='$scriptname?sid={$aImportResults['newsid']}'>".$clang->gT("Go to survey")."</a><br />\n"; 
    }    

    if ($action == 'importsurvey')
    {
        unlink($sFullFilepath);    
    }
    
}
    // end of traitment an close message box
    $importsurvey .= "</div><br />\n";
?>
