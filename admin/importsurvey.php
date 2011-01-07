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
//importsurvey.php should be called from cmdline_importsurvey.php or http_importsurvey.php, they set the $importingfrom variable
if ((!isset($importingfrom) && !isset($copyfunction)) || isset($_REQUEST['importingfrom']))
{
    die("Cannot run this script directly");
}
require_once('import_functions.php');

if (!isset($copyfunction))
{
    $sFullFilepath=$the_full_file_path;
    $aPathInfo = pathinfo($sFullFilepath);
    $sExtension = $aPathInfo['extension'];
}
                  
$bImportFailed=false;  
if (isset($sExtension) && strtolower($sExtension)=='csv')
{
    $aImportResults=CSVImportSurvey($sFullFilepath);
}
elseif (isset($sExtension) && strtolower($sExtension)=='lss')
{
    $aImportResults=XMLImportSurvey($sFullFilepath,null,null,(isset($_POST['translinksfields'])));
} elseif (isset($copyfunction))
{
    $aImportResults=XMLImportSurvey('',$copysurveydata,$sNewSurveyName);
}
else
{
    $bImportFailed=true;
}

// Create old fieldnames
                 
                
if ((!$bImportFailed && isset($importingfrom) && $importingfrom == "http") || isset($copyfunction))
{
    $importsurvey .= "<br />\n<div class='successheader'>".$clang->gT("Success")."</div><br /><br />\n";
    if (isset($copyfunction))
    {
        $importsurvey .= "<strong><u>".$clang->gT("Survey copy summary")."</u></strong><br />\n";        
    } else
    {
        $importsurvey .= "<strong><u>".$clang->gT("Survey import summary")."</u></strong><br />\n";        
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
    if (isset($copyfunction))
    {
        $importsurvey .= "<strong>".$clang->gT("Copy of survey is completed.")."</strong><br />\n"
        . "<a href='$scriptname?sid={$aImportResults['newsid']}'>".$clang->gT("Go to survey")."</a><br />\n";        
    } else
    {
        $importsurvey .= "<strong>".$clang->gT("Import of Survey is completed.")."</strong><br />\n"
        . "<a href='$scriptname?sid={$aImportResults['newsid']}'>".$clang->gT("Go to survey")."</a><br />\n";        
    }
    $importsurvey .= "</div><br />\n";
    if (!isset($copyfunction))
    {
        unlink($sFullFilepath);    
    }
}
elseif (isset($bImportFailed) && $bImportFailed==true)
{
    echo "\n".$clang->gT("Error")."\n\n";
    echo $clang->gT("Import failed. You specified an invalid file.")."\n";
    
}
else
{
    echo "\n".$clang->gT("Success")."\n\n";
    echo $clang->gT("Survey import summary")."\n";
    echo $clang->gT("Surveys").": {$aImportResults['surveys']}\n";
    if ($aImportResults['importversion']>=111)
    {
        echo $clang->gT("Languages").": {$aImportResults['languages']}\n";
    }
    echo $clang->gT("Groups").": {$aImportResults['groups']}\n";
    echo $clang->gT("Questions").": {$aImportResults['questions']}\n";
    echo $clang->gT("Answers").": {$aImportResults['answers']}\n";
    if (isset($aImportResults['subquestions']))
    {
        echo $clang->gT("Subquestions").": {$aImportResults['subquestions']}\n";      
    }
    if (isset($aImportResults['defaultvalues']))
    {
        echo $clang->gT("Default answers").": {$aImportResults['defaultvalues']}\n";     
    }
    if (isset($aImportResults['conditions']))
    {
        echo $clang->gT("Conditions").": {$aImportResults['conditions']}\n";       
    }
    if (isset($aImportResults['labelsets']))
    {
        echo $clang->gT("Label sets").": {$aImportResults['labelsets']}\n";
    }
    if ($importresults['deniedcountls']>0) echo $clang->gT("Not imported label sets").": {$importresults['deniedcountls']} (".$clang->gT("(Label sets were not imported since you do not have the permission to create new label sets.)");
    echo $clang->gT("Question Attributes").": {$aImportResults['question_attributes']}\n";
    echo $clang->gT("Assessments").": {$aImportResults['assessments']}\n\n";

    echo $clang->gT("Import of Survey is completed.")."\n";
    if ($aImportResults['importwarnings'] != "") echo "\n".$clang->gT("Warnings").":\n" . $aImportResults['importwarnings'] . "\n";
    $surveyid=$newsid;

}


