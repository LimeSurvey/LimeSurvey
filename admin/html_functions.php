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
 * $Id: html_functions.php 8672 2010-05-03 14:02:14Z lemeur $
 */
function browsemenubar($title='')
{
    global $surveyid, $scriptname, $imagefiles, $homeurl, $clang, $sumrows5, $surrows;

    $thissurvey=getSurveyInfo($surveyid);
    //BROWSE MENU BAR
    $browsemenubar = "<div class='menubar'>\n"
    . "<div class='menubar-title'>\n"
    . "<strong>$title</strong>: ({$thissurvey['name']})"
    . "</div>"
    . "<div class='menubar-main'>\n"
    . "<div class='menubar-left'>\n"
    //Return to survey administration
    . "<a href='$scriptname?sid=$surveyid' title=\"".$clang->gTview("Return to survey administration")."\" >"
    . "<img name='Administration' src='$imagefiles/home.png' title='' alt='".$clang->gT("Return to survey administration")."' /></a>\n"
    . "<img src='$imagefiles/blank.gif' alt='' width='11' />\n"
    . "<img src='$imagefiles/seperator.gif' alt='' />\n"
    //Show summary information
    . "<a href='$scriptname?action=browse&amp;sid=$surveyid' title=\"".$clang->gTview("Show summary information")."\" >"
    . "<img name='SurveySummary' src='$imagefiles/summary.png' title='' alt='".$clang->gT("Show summary information")."' /></a>\n";

    //Display responses
    if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
    {
        $browsemenubar .="<a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=all' title=\"".$clang->gTview("Display Responses")."\" >" .
        "<img name='ViewAll' src='$imagefiles/document.png' title='' alt='".$clang->gT("Display Responses")."' /></a>\n";
    }
    else
    {
        $browsemenubar .= "<a href=\"#\" accesskey='b' id='browseresponses'"
        . "title=\"".$clang->gTview("Display Responses")."\" >"
        ."<img src='$imagefiles/document.png' alt='".$clang->gT("Display Responses")."' name='ViewAll' /></a>";

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
                    "<img name='ViewLast' src='$imagefiles/viewlast.png' alt='".$clang->gT("Display Last 50 Responses")."' /></a>\n";
    // Data entry
    $browsemenubar .= "<a href='$scriptname?action=dataentry&amp;sid=$surveyid'".
                    " title=\"".$clang->gTview("Dataentry Screen for Survey")."\" >" .
                    "<img name='DataEntry' src='$imagefiles/dataentry.png' alt='".$clang->gT("Dataentry Screen for Survey")."' /></a>\n";
    // Statistics
    $browsemenubar .= "<a href='$scriptname?action=statistics&amp;sid=$surveyid' "
    ."title=\"".$clang->gTview("Get statistics from these responses")."\" >"
    ."<img name='Statistics' src='$imagefiles/statistics.png' alt='".$clang->gT("Get statistics from these responses")."' /></a>\n";

    // Time Statistics
    if ($thissurvey['savetimings']=="Y")
    {
		$browsemenubar .= "<a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=time' "
		."title=\"".$clang->gTview("Get time statistics from these responses")."\" >"
		."<img name='timeStatistics' src='$imagefiles/timeStatistics.png' alt='".$clang->gT("Get time statistics from these responses")."' /></a>\n";
	}
	
    
    $browsemenubar .= "<img src='$imagefiles/seperator.gif' alt='' />\n";

    if ($sumrows5['export'] == "1" || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        // Export to application
        $browsemenubar .= "<a href='$scriptname?action=exportresults&amp;sid=$surveyid' title=\"".$clang->gTview("Export Results to Application")."\" >"
        . "<img name='Export' src='$imagefiles/export.png' "
        . "alt='".$clang->gT("Export Results to Application")."' /></a>\n"
        
        // Export to SPSS
        . "<a href='$scriptname?action=exportspss&amp;sid=$surveyid' title=\"".$clang->gTview("Export results to a SPSS/PASW command file")."\" >"
        . "<img src='$imagefiles/exportspss.png' "
        . "alt='". $clang->gT("Export results to a SPSS/PASW command file")."' /></a>\n"
        
        // Export to R
        . "<a href='$scriptname?action=exportr&amp;sid=$surveyid' title=\"".$clang->gTview("Export results to a R data file")."\" >"
        . "<img src='$imagefiles/exportr.png' "
        . "alt='". $clang->gT("Export results to a R data file")."' /></a>\n";
    }
    //Import old response table
    $browsemenubar .= "<a href='$scriptname?action=importoldresponses&amp;sid=$surveyid' title=\"".$clang->gTview("Import answers from a deactivated survey table")."\" >"
    . "<img name='ImportOldResponses' src='$imagefiles/importold.png' alt='".$clang->gT("Import answers from a deactivated survey table")."' /></a>\n";

    $browsemenubar .= "<img src='$imagefiles/seperator.gif' alt='' />\n";

    //browse saved responses
    $browsemenubar .= "<a href='$scriptname?action=saved&amp;sid=$surveyid' title=\"".$clang->gTview("View Saved but not submitted Responses")."\" >"
    . "<img src='$imagefiles/saved.png' title='' alt='".$clang->gT("View Saved but not submitted Responses")."' name='BrowseSaved' /></a>\n"

    //Import VV
    . "<a href='$scriptname?action=vvimport&amp;sid=$surveyid' title=\"".$clang->gTview("Import a VV survey file")."\" >"
    . "<img src='$imagefiles/importvv.png' alt='".$clang->gT("Import a VV survey file")."' /></a>\n";

    //Export VV
    if ($sumrows5['export'] == "1" || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
    {
        $browsemenubar .= "<a href='$scriptname?action=vvexport&amp;sid=$surveyid' title=\"".$clang->gTview("Export a VV survey file")."\" >"
        ."<img src='$imagefiles/exportvv.png' title='' alt='".$clang->gT("Export a VV survey file")."' /></a>\n";
    }

    //Iterate survey
    if (( ($surrows['browse_response'] && $surrows['activate_survey']) ||
    $_SESSION['USER_RIGHT_SUPERADMIN'] == 1
    ) &&
    (
    $thissurvey['private'] == 'N' &&
    $thissurvey['tokenanswerspersistence'] == 'Y'
    ))
    {
        $browsemenubar .= "<a href='$scriptname?action=iteratesurvey&amp;sid=$surveyid' title=\"".$clang->gTview("Iterate survey")."\" >"
        ."<img src='$imagefiles/iterate.png' title='' alt='".$clang->gT("Iterate surevey")."' /></a>\n";
    }
    $browsemenubar .= "</div>\n"
    . "\t</div>\n"
    . "</div>\n";

    return $browsemenubar;
}

