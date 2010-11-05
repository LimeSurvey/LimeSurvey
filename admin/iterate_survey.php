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
/*
 * Set completed answers to the incomplete state and reset the tokens to 'not used' so that
 * the survey can be published again to the same set of participants.
 * Partipants will see their previous answers and may change them.
 */

include_once('login_check.php');
include('html_functions.php');

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

if  (!isset($subaction))
{ // subaction is not set, then display instructions
$iteratesurveyoutput = browsemenubar($clang->gT('Iterate survey'));
$iteratesurveyoutput .= "<div class='header ui-widget-header'>".$clang->gT("Iterate survey")."</div>\n";
$iteratesurveyoutput .=  "<div class='messagebox ui-corner-all'><div class='header ui-widget-header'>".$clang->gT("Important instructions")."</div>"
. "<br/>".$clang->gT("Click on the following button if you want to").":<br />\n"
. "<ol>"
. "<li>".$clang->gT("Delete all incomplete answers that correspond to a token for which a completed answers is already recorded")."</li>"
. "<li>".$clang->gT("Reset the completed answers to the incomplete state")."</li>"
. "<li>".$clang->gT("Reset all your tokens to the 'not used' state")."</li>"
. "</ol><br />\n"
. "<input type='button' onclick=\"if( confirm('".$clang->gT("Are you really sure you want to *delete* some incomplete answers and reset the completed state of both answers and tokens?","js")."')){".get2post("$scriptname?action=iteratesurvey&amp;sid=$surveyid&amp;subaction=unfinalizeanswers")."}\" value='".$clang->gT("Reset answers and token completed state")."' />"
. "</div>";
}

if  ($subaction=='unfinalizeanswers')
{
    $iteratesurveyoutput = browsemenubar($clang->gT('Iterate survey'));
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    $surveytable = db_table_name("survey_$surveyid");
    // First delete incomplete answers that correspond to a token for which a completed answers is already recorded
    // subquery in delete or update are tricky things when using the same table for delete and Select
    // see http://www.developpez.net/forums/d494961/bases-donnees/mysql/requetes/cant-specify-target-in-from-clause/
    $updateqr = "DELETE from $surveytable WHERE submitdate IS NULL AND token in (SELECT * FROM ( SELECT answ2.token from $surveytable AS answ2 WHERE answ2.submitdate IS NOT NULL) tmp );\n";
    //	$updateqr = "DELETE from $surveytable WHERE submitdate IS NULL AND token in (SELECT b.token from $surveytable AS b WHERE b.submitdate IS NOT NULL);\n";
    //error_log("TIBO query = $updateqr");
    $updateres = $connect->Execute($updateqr) or safe_die("Delete incomplete answers with duplicate tokens failed:<br />\n" . $connect->ErrorMsg() . "<br />$updateqr");
    // Then set all remaining answers to incomplete state
    $updateqr = "UPDATE $surveytable SET submitdate=NULL, lastpage=NULL;\n";
    $updateres = $connect->Execute($updateqr) or safe_die("UnFinilize answers failed:<br />\n" . $connect->ErrorMsg() . "<br />$updateqr");
    // Finally, reset the token completed and sent status
    $updateqr="UPDATE ".db_table_name("tokens_$surveyid")." SET sent='N', remindersent='N', remindercount=0, completed='N'";
    $updateres=$connect->Execute($updateqr) or safe_die ("Couldn't reset token completed state<br />$updateqr<br />".$connect->ErrorMsg());
    $iteratesurveyoutput .= "<br />\n";
    $iteratesurveyoutput .= "<div class='header ui-widget-header'>".$clang->gT("Iterate survey")."</div>\n";
    $iteratesurveyoutput .=  "<p style='width:100%;'>\n"
    . "<font class='successtitle'>".$clang->gT("Success")."</font><br />\n"
    . $clang->gT("Answers and tokens have been re-opened.")."<br />\n"
    . "</p>\n"
    . "<table><tr><td>";
}
