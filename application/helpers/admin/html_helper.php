<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
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
 * $Id: html_functions.php 10193 2011-06-05 12:20:37Z c_schmitz $
 */

function browsemenubar($title='', $surveyid=null, $load=false, $controller = false)
{
    //$surveyid; //$sumrows5, $surrows; //$yii->getConfig('scriptname'), $yii->getConfig('imageurl'), $yii->getConfig('homeurl'),
    $yii = Yii::app();
    $yii->loadHelper('common');
    $lang = array('fr');
	//$lang = array($this->config->item('defaultlang'));
	$yii->loadLibrary('Limesurvey_lang',$lang);
    $clang = $yii->lang;
    $thissurvey=getSurveyInfo($surveyid);
    //$thissurvey = array('name'=>'');

    //BROWSE MENU BAR
    $browsemenubar = "<div class='menubar'>\n"
    . "<div class='menubar-title ui-widget-header'>\n"
    . "<strong>$title</strong>: (".FlattenText($thissurvey['name'],true).")"
    . "</div>"
    . "<div class='menubar-main'>\n"
    . "<div class='menubar-left'>\n"
    //Return to survey administration
    . "<a href='".$yii->homeUrl.('/admin/survey/view/'.$surveyid)."' title=\"".$clang->gTview("Return to survey administration")."\" >"
    . "<img name='Administration' src='".$yii->getConfig('imageurl')."/home.png' title='' alt='".$clang->gT("Return to survey administration")."' /></a>\n"
    . "<img src='".$yii->getConfig('imageurl')."/blank.gif' alt='' width='11' />\n"
    . "<img src='".$yii->getConfig('imageurl')."/seperator.gif' alt='' />\n";
    //Show summary information
    
    if (bHasSurveyPermission($surveyid,'responses','read'))
    {
        $browsemenubar.= "<a href='".$yii->homeUrl.('/admin/browse/'.$surveyid)."' title=\"".$clang->gTview("Show summary information")."\" >"
        . "<img name='SurveySummary' src='".$yii->getConfig('imageurl')."/summary.png' title='' alt='".$clang->gT("Show summary information")."' /></a>\n";
        //Display responses
        if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
        {
            $browsemenubar .="<a href='".$yii->homeUrl.('/admin/browse/'.$surveyid.'/all')."' title=\"".$clang->gTview("Display Responses")."\" >" .
            "<img name='ViewAll' src='".$yii->getConfig('imageurl')."/document.png' title='' alt='".$clang->gT("Display Responses")."' /></a>\n";
        }
        else
        {
            $browsemenubar .= "<a href=\"#\" accesskey='b' id='browseresponses'"
            . "title=\"".$clang->gTview("Display Responses")."\" >"
            ."<img src='".$yii->getConfig('imageurl')."/document.png' alt='".$clang->gT("Display Responses")."' name='ViewAll' /></a>";

            $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            $tmp_survlangs[] = $baselang;
            rsort($tmp_survlangs);

            $browsemenubar .="<div class=\"langpopup\" id=\"browselangpopup\">".$clang->gT("Please select a language:")."<ul>";
            $yii->loadHelper("surveytranslator");
            foreach ($tmp_survlangs as $tmp_lang)
            {
                $browsemenubar .= "<li><a href=\"".$yii->homeUrl.('/admin/browse/'.$surveyid.'/all/'.$tmp_lang)."\" accesskey='b'>".getLanguageNameFromCode($tmp_lang,false)."</a></li>";
            }
            $browsemenubar .= "</ul></div>";
        }

        // Display last 50 responses
        $browsemenubar .= "<a href='".$yii->homeUrl.('/admin/browse/'.$surveyid.'/50/desc')."'" .
                        " title=\"".$clang->gTview("Display Last 50 Responses")."\" >" .
                        "<img name='ViewLast' src='".$yii->getConfig('imageurl')."/viewlast.png' alt='".$clang->gT("Display Last 50 Responses")."' /></a>\n";
                    
    }
    
    // Data entry
    if (bHasSurveyPermission($surveyid,'responses','create'))
    {
        $browsemenubar .= "<a href='".$yii->homeUrl.('/admin/dataentry/view/'.$surveyid)."'".
                        " title=\"".$clang->gTview("Dataentry Screen for Survey")."\" >" .
                        "<img name='DataEntry' src='".$yii->getConfig('imageurl')."/dataentry.png' alt='".$clang->gT("Dataentry Screen for Survey")."' /></a>\n";
    }
    // Statistics
    if (bHasSurveyPermission($surveyid,'statistics','read'))
    {
        $browsemenubar .= "<a href='".$yii->homeUrl.('/admin/statistics/'.$surveyid)."' "
        ."title=\"".$clang->gTview("Get statistics from these responses")."\" >"
        ."<img name='Statistics' src='".$yii->getConfig('imageurl')."/statistics.png' alt='".$clang->gT("Get statistics from these responses")."' /></a>\n";
        // Time Statistics
        if (isset($thissurvey['savetimings']) && $thissurvey['savetimings']=="Y")
        {
            $browsemenubar .= "<a href='".$yii->homeUrl.('/admin/browse/'.$surveyid.'time')."' "
            ."title=\"".$clang->gTview("Get time statistics from these responses")."\" >"
            ."<img name='timeStatistics' src='".$yii->getConfig('imageurl')."/timeStatistics.png' alt='".$clang->gT("Get time statistics from these responses")."' /></a>\n";
        }    
    }
    $browsemenubar .= "<img src='".$yii->getConfig('imageurl')."/seperator.gif' alt='' />\n";

    if (bHasSurveyPermission($surveyid,'responses','export'))         
    {
        // Export to application
        $browsemenubar .= "<a href='".$yii->getConfig('scriptname')."?action=exportresults&amp;sid=$surveyid' title=\"".$clang->gTview("Export results to application")."\" >"
        . "<img name='Export' src='".$yii->getConfig('imageurl')."/export.png' "
        . "alt='".$clang->gT("Export results to application")."' /></a>\n"
        
        // Export to SPSS
        . "<a href='".$yii->getConfig('scriptname')."?action=exportspss&amp;sid=$surveyid' title=\"".$clang->gTview("Export results to a SPSS/PASW command file")."\" >"
        . "<img src='".$yii->getConfig('imageurl')."/exportspss.png' "
        . "alt='". $clang->gT("Export results to a SPSS/PASW command file")."' /></a>\n"
        
        // Export to R
        . "<a href='".$yii->getConfig('scriptname')."?action=exportr&amp;sid=$surveyid' title=\"".$clang->gTview("Export results to a R data file")."\" >"
        . "<img src='".$yii->getConfig('imageurl')."/exportr.png' "
        . "alt='". $clang->gT("Export results to a R data file")."' /></a>\n";
    }
    //Import old response table
    if (bHasSurveyPermission($surveyid,'responses','create'))  
    {
        $browsemenubar .= "<a href='".$yii->homeUrl.('/admin/dataentry/import/'.$surveyid)."' title=\"".$clang->gTview("Import responses from a deactivated survey table")."\" >"
        . "<img name='ImportOldResponses' src='".$yii->getConfig('imageurl')."/importold.png' alt='".$clang->gT("Import responses from a deactivated survey table")."' /></a>\n";
    }       

    $browsemenubar .= "<img src='".$yii->getConfig('imageurl')."/seperator.gif' alt='' />\n";

    //browse saved responses
    if (bHasSurveyPermission($surveyid,'responses','read'))  
    {
        $browsemenubar .= "<a href='".$yii->homeUrl.'/admin/saved/view/'.$surveyid."' title=\"".$clang->gTview("View Saved but not submitted Responses")."\" >"
        . "<img src='".$yii->getConfig('imageurl')."/saved.png' title='' alt='".$clang->gT("View Saved but not submitted Responses")."' name='BrowseSaved' /></a>\n";
    }

    //Import VV
    if (bHasSurveyPermission($surveyid,'responses','import'))  
    {    
        $browsemenubar .= "<a href='".$yii->homeUrl.('/admin/dataentry/vvimport/'.$surveyid)."' title=\"".$clang->gTview("Import a VV survey file")."\" >"
        . "<img src='".$yii->getConfig('imageurl')."/importvv.png' alt='".$clang->gT("Import a VV survey file")."' /></a>\n";
    }

    //Export VV
    if (bHasSurveyPermission($surveyid,'responses','export'))   
    {
        $browsemenubar .= "<a href='".$yii->getConfig('scriptname')."?action=vvexport&amp;sid=$surveyid' title=\"".$clang->gTview("Export a VV survey file")."\" >"
        ."<img src='".$yii->getConfig('imageurl')."/exportvv.png' title='' alt='".$clang->gT("Export a VV survey file")."' /></a>\n";
    }

    //Iterate survey
    if (bHasSurveyPermission($surveyid,'responses','delete') && isset($thissurvey['anonymized']) && $thissurvey['anonymized'] == 'N' && $thissurvey['tokenanswerspersistence'] == 'Y')
    {
        $browsemenubar .= "<a href='".$yii->getConfig('scriptname')."?action=iteratesurvey&amp;sid=$surveyid' title=\"".$clang->gTview("Iterate survey")."\" >"
        ."<img src='".$yii->getConfig('imageurl')."/iterate.png' title='' alt='".$clang->gT("Iterate survey")."' /></a>\n";
    }
    $browsemenubar .= "</div>\n"
    . "\t</div>\n"
    . "</div>\n";
   
    if ($load)
    {
        
        $data['display'] = $browsemenubar;
		Yii::app()->getController()->render('/survey_view',$data);
    }
    else
    {
        return $browsemenubar;
    }
    
}

//LAST LINE LEFT BLANK