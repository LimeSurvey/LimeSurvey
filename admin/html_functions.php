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
 * $Id: html_functions.php 9668 2010-12-21 00:49:44Z c_schmitz $
 */
function browsemenubar($title='')
{
    global $surveyid, $scriptname, $imageurl, $homeurl, $clang, $sumrows5, $surrows;

    $thissurvey=getSurveyInfo($surveyid);
    //BROWSE MENU BAR
    $browsemenubar = "<div class='menubar'>\n"
    . "<div class='menubar-title ui-widget-header'>\n"
    . "<strong>$title</strong>: ({$thissurvey['name']})"
    . "</div>"
    . "<div class='menubar-main'>\n"
    . "<div class='menubar-left'>\n"
    //Return to survey administration
    . "<a href='$scriptname?sid=$surveyid' title=\"".$clang->gTview("Return to survey administration")."\" >"
    . "<img name='Administration' src='$imageurl/home.png' title='' alt='".$clang->gT("Return to survey administration")."' /></a>\n"
    . "<img src='$imageurl/blank.gif' alt='' width='11' />\n"
    . "<img src='$imageurl/seperator.gif' alt='' />\n";
    //Show summary information
    if (bHasSurveyPermission($surveyid,'responses','read'))
    {
        $browsemenubar.= "<a href='$scriptname?action=browse&amp;sid=$surveyid' title=\"".$clang->gTview("Show summary information")."\" >"
        . "<img name='SurveySummary' src='$imageurl/summary.png' title='' alt='".$clang->gT("Show summary information")."' /></a>\n";
        //Display responses
        if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
        {
            $browsemenubar .="<a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=all' title=\"".$clang->gTview("Display Responses")."\" >" .
            "<img name='ViewAll' src='$imageurl/document.png' title='' alt='".$clang->gT("Display Responses")."' /></a>\n";
        }
        else
        {
            $browsemenubar .= "<a href=\"#\" accesskey='b' id='browseresponses'"
            . "title=\"".$clang->gTview("Display Responses")."\" >"
            ."<img src='$imageurl/document.png' alt='".$clang->gT("Display Responses")."' name='ViewAll' /></a>";

            $tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $baselang = GetBaseLanguageFromSurveyID($surveyid);
            $tmp_survlangs[] = $baselang;
            rsort($tmp_survlangs);

            $browsemenubar .="<div class=\"langpopup\" id=\"browselangpopup\">".$clang->gT("Please select a language:")."<ul>";
            foreach ($tmp_survlangs as $tmp_lang)
            {
                $browsemenubar .= "<li><a href=\"{$scriptname}?action=browse&amp;sid={$surveyid}&amp;subaction=all&amp;browselang={$tmp_lang}\" accesskey='b'>".getLanguageNameFromCode($tmp_lang,false)."</a></li>";
            }
            $browsemenubar .= "</ul></div>";
        }

        // Display last 50 responses
        $browsemenubar .= "<a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=all&amp;limit=50&amp;order=desc'" .
                        " title=\"".$clang->gTview("Display Last 50 Responses")."\" >" .
                        "<img name='ViewLast' src='$imageurl/viewlast.png' alt='".$clang->gT("Display Last 50 Responses")."' /></a>\n";
                    
    }
    // Data entry
    if (bHasSurveyPermission($surveyid,'responses','create'))
    {
        $browsemenubar .= "<a href='$scriptname?action=dataentry&amp;sid=$surveyid'".
                        " title=\"".$clang->gTview("Dataentry Screen for Survey")."\" >" .
                        "<img name='DataEntry' src='$imageurl/dataentry.png' alt='".$clang->gT("Dataentry Screen for Survey")."' /></a>\n";
    }
    // Statistics
    if (bHasSurveyPermission($surveyid,'statistics','read'))
    {
        $browsemenubar .= "<a href='$scriptname?action=statistics&amp;sid=$surveyid' "
        ."title=\"".$clang->gTview("Get statistics from these responses")."\" >"
        ."<img name='Statistics' src='$imageurl/statistics.png' alt='".$clang->gT("Get statistics from these responses")."' /></a>\n";
        // Time Statistics
        if ($thissurvey['savetimings']=="Y")
        {
            $browsemenubar .= "<a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=time' "
            ."title=\"".$clang->gTview("Get time statistics from these responses")."\" >"
            ."<img name='timeStatistics' src='$imageurl/timeStatistics.png' alt='".$clang->gT("Get time statistics from these responses")."' /></a>\n";
        }    
    }
    $browsemenubar .= "<img src='$imageurl/seperator.gif' alt='' />\n";

    if (bHasSurveyPermission($surveyid,'exportresponses','read'))         
    {
        // Export to application
        $browsemenubar .= "<a href='$scriptname?action=exportresults&amp;sid=$surveyid' title=\"".$clang->gTview("Export results to application")."\" >"
        . "<img name='Export' src='$imageurl/export.png' "
        . "alt='".$clang->gT("Export results to application")."' /></a>\n"
        
        // Export to SPSS
        . "<a href='$scriptname?action=exportspss&amp;sid=$surveyid' title=\"".$clang->gTview("Export results to a SPSS/PASW command file")."\" >"
        . "<img src='$imageurl/exportspss.png' "
        . "alt='". $clang->gT("Export results to a SPSS/PASW command file")."' /></a>\n"
        
        // Export to R
        . "<a href='$scriptname?action=exportr&amp;sid=$surveyid' title=\"".$clang->gTview("Export results to a R data file")."\" >"
        . "<img src='$imageurl/exportr.png' "
        . "alt='". $clang->gT("Export results to a R data file")."' /></a>\n";
    }
    //Import old response table
    if (bHasSurveyPermission($surveyid,'responses','create'))  
    {
        $browsemenubar .= "<a href='$scriptname?action=importoldresponses&amp;sid=$surveyid' title=\"".$clang->gTview("Import responses from a deactivated survey table")."\" >"
        . "<img name='ImportOldResponses' src='$imageurl/importold.png' alt='".$clang->gT("Import answers from a deactivated survey table")."' /></a>\n";
    }       

    $browsemenubar .= "<img src='$imageurl/seperator.gif' alt='' />\n";

    //browse saved responses
    if (bHasSurveyPermission($surveyid,'responses','read'))  
    {
        $browsemenubar .= "<a href='$scriptname?action=saved&amp;sid=$surveyid' title=\"".$clang->gTview("View Saved but not submitted Responses")."\" >"
        . "<img src='$imageurl/saved.png' title='' alt='".$clang->gT("View Saved but not submitted Responses")."' name='BrowseSaved' /></a>\n";
    }

    //Import VV
    if (bHasSurveyPermission($surveyid,'responses','create'))  
    {    
        $browsemenubar . "<a href='$scriptname?action=vvimport&amp;sid=$surveyid' title=\"".$clang->gTview("Import a VV survey file")."\" >"
        . "<img src='$imageurl/importvv.png' alt='".$clang->gT("Import a VV survey file")."' /></a>\n";
    }

    //Export VV
    if (bHasSurveyPermission($surveyid,'exportresponses','read'))   
    {
        $browsemenubar .= "<a href='$scriptname?action=vvexport&amp;sid=$surveyid' title=\"".$clang->gTview("Export a VV survey file")."\" >"
        ."<img src='$imageurl/exportvv.png' title='' alt='".$clang->gT("Export a VV survey file")."' /></a>\n";
    }

    //Iterate survey
    if (bHasSurveyPermission($surveyid,'responses','delete') && $thissurvey['anonymized'] == 'N' && $thissurvey['tokenanswerspersistence'] == 'Y')
    {
        $browsemenubar .= "<a href='$scriptname?action=iteratesurvey&amp;sid=$surveyid' title=\"".$clang->gTview("Iterate survey")."\" >"
        ."<img src='$imagefiles/iterate.png' title='' alt='".$clang->gT("Iterate survey")."' /></a>\n";
    }
    $browsemenubar .= "</div>\n"
    . "\t</div>\n"
    . "</div>\n";

    return $browsemenubar;
}

