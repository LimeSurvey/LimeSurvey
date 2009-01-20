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

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

if  (!isset($subaction))
{ // subaction is not set, then display instructions
    $iteratesurveyoutput = browsemenubar($clang->gT('Iterate Survey'));
    $iteratesurveyoutput .= "<br />\n";
    $iteratesurveyoutput .= "<div class='header'>".$clang->gT("Iterate Survey")."</div>\n";
    $iteratesurveyoutput .=  "<h3>".$clang->gT("Important Instructions")."</h3>"
			. "<p style='width:80%;'>\n"
//			. $clang->gT("Click on the following button if you want to run again this survey for the same set of participants so that they will be able to retrieve their previously given answers, and if required, modify their answers.")."<br />\n"
			. "<div align='center'>".$clang->gT("Click on the following button if you want to").":<br /></div>\n"
			. "<ol style='width:500px;margin:0 auto; font-size:8pt;'>"
			. "<li>".$clang->gT("Delete all incomplete answers that correspond to a token for which a completed answers is already recorded")."</li>"
			. "<li>".$clang->gT("Reset the completed answers to the incomplete state")."</li>"
			. "<li>".$clang->gT("Reset all your tokens to the 'not used' state")."</li>"
			. "</ol><br />\n"
			. "<input type='button' onclick=\"if( confirm('".$clang->gT("Are you really sure you want to *delete* some incomplete answers and reset the completed state of both answers and tokens?","js")."')){".get2post("$scriptname?action=iteratesurvey&amp;sid=$surveyid&amp;subaction=unfinalizeanswers")."}\" value='".$clang->gT("Reset answers and token completed state")."'>"
			. "<table><tr><td>";
}

if  ($subaction=='unfinalizeanswers') 
{
	$iteratesurveyoutput = browsemenubar($clang->gT('Iterate Survey'));
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
	$updateqr = "UPDATE $surveytable SET submitdate=NULL;\n";
	$updateres = $connect->Execute($updateqr) or safe_die("UnFinilize answers failed:<br />\n" . $connect->ErrorMsg() . "<br />$updateqr");
	// Finally, reset the token completed and sent status
	$updateqr="UPDATE ".db_table_name("tokens_$surveyid")." SET sent='N', remindersent='N', remindercount=0, completed='N'";
	$updateres=$connect->Execute($updateqr) or safe_die ("Couldn't reset token completed state<br />$updateqr<br />".$connect->ErrorMsg());
	$iteratesurveyoutput .= "<br />\n";
	$iteratesurveyoutput .= "<div class='header'>".$clang->gT("Iterate Survey")."</div>\n";
	$iteratesurveyoutput .=  "<p style='width:100%;'>\n"
		. "<font class='successtitle'>".$clang->gT("Success")."</font><br />\n"
		. $clang->gT("Answers and Tokens have been re-opened.")."<br />\n"
		. "</p>\n"
		. "<table><tr><td>";
}

?>       
