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
$deleteok = returnglobal('deleteok');

$deletesurveyoutput = "<br />\n";
$deletesurveyoutput .= "<div class='messagebox'>\n";
$deletesurveyoutput .= "<div class='header'>".$clang->gT("Delete survey")."</div>\n";

if (!isset($surveyid) || !$surveyid)
{
    $deletesurveyoutput .= "<br /><font color='red'><strong>".$clang->gT("Error")."</strong></font><br />\n";
    $deletesurveyoutput .= $clang->gT("You have not selected a survey to delete")."<br /><br />\n";
    $deletesurveyoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n";
    $deletesurveyoutput .= "</td></tr></table>\n";
    $deletesurveyoutput .= "</body>\n</html>";
    return;
}

if (!isset($deleteok) || !$deleteok)
{
    $deletesurveyoutput .= "\t<div class='warningheader'>\n".$clang->gT("Warning")."</div><br />\n";
    $deletesurveyoutput .= "\t<strong>".$clang->gT("You are about to delete this survey")." ($surveyid)</strong><br /><br />\n";
    $deletesurveyoutput .= "\t".$clang->gT("This process will delete this survey, and all related groups, questions answers and conditions.")."<br /><br />\n";
    $deletesurveyoutput .= "\t".$clang->gT("We recommend that before you delete this survey you export the entire survey from the main administration screen.")."\n";

    if (tableExists("survey_$surveyid"))
    {
        $deletesurveyoutput .= "\t<br /><br />\n".$clang->gT("This survey is active and a responses table exists. If you delete this survey, these responses will be deleted. We recommend that you export the responses before deleting this survey.")."<br /><br />\n";
    }

    if (tableExists("tokens_$surveyid"))
    {
        $deletesurveyoutput .= "\t".$clang->gT("This survey has an associated tokens table. If you delete this survey this tokens table will be deleted. We recommend that you export or backup these tokens before deleting this survey.")."<br /><br />\n";
    }
		
		if (tableExists("grouptokens_$surveyid"))
    {
        $deletesurveyoutput .= "\t".$clang->gT("This survey has an associated group tokens table. If you delete this survey this group tokens table will be deleted. We recommend that you export or backup these tokens before deleting this survey.")."<br /><br />\n";
    }

    $deletesurveyoutput .= "<p>\n";
    $deletesurveyoutput .= "\t<input type='submit'  value='".$clang->gT("Delete survey")."' onclick=\"".get2post("$scriptname?action=deletesurvey&amp;sid=$surveyid&amp;deleteok=Y")."\" />\n";
    $deletesurveyoutput .= "\t<input type='submit'  value='".$clang->gT("Cancel")."' onclick=\"window.open('admin.php?sid=$surveyid', '_top')\" />\n";

}

else //delete the survey
{
    $dict = NewDataDictionary($connect);

    if (tableExists("survey_$surveyid"))  //delete the survey_$surveyid table
    {
        $dsquery = $dict->DropTableSQL("{$dbprefix}survey_$surveyid");
        //$dict->ExecuteSQLArray($sqlarray);
        $dsresult = $dict->ExecuteSQLArray($dsquery) or safe_die ("Couldn't \"$dsquery\" because <br />".$connect->ErrorMsg());
    }
    
	if (tableExists("survey_{$surveyid}_timings"))  //delete the survey_$surveyid_timings table
    {    	
        $dsquery = $dict->DropTableSQL("{$dbprefix}survey_{$surveyid}_timings");
        //$dict->ExecuteSQLArray($sqlarraytimings);
        $dsresult = $dict->ExecuteSQLArray($dsquery) or safe_die ("Couldn't \"$dsquery\" because <br />".$connect->ErrorMsg());
    }

    if (tableExists("tokens_$surveyid")) //delete the tokens_$surveyid table
    {
        $dsquery = $dict->DropTableSQL("{$dbprefix}tokens_$surveyid");
        $dsresult = $dict->ExecuteSQLArray($dsquery) or safe_die ("Couldn't \"$dsquery\" because <br />".$connect->ErrorMsg());
    }
		
		if (tableExists("grouptokens_$surveyid")) //delete the grouptokens_$surveyid table
    {
        $dsquery = $dict->DropTableSQL("{$dbprefix}grouptokens_$surveyid");
        $dsresult = $dict->ExecuteSQLArray($dsquery) or safe_die ("Couldn't \"$dsquery\" because <br />".$connect->ErrorMsg());
    }
		
		if (tableExists("usedtokens_$surveyid")) //delete the usedtokens_$surveyid table
    {
        $dsquery = $dict->DropTableSQL("{$dbprefix}usedtokens_$surveyid");
        $dsresult = $dict->ExecuteSQLArray($dsquery) or safe_die ("Couldn't \"$dsquery\" because <br />".$connect->ErrorMsg());
    }

    $dsquery = "SELECT qid FROM {$dbprefix}questions WHERE sid=$surveyid";
    $dsresult = db_execute_assoc($dsquery) or safe_die ("Couldn't find matching survey to delete<br />$dsquery<br />".$connect->ErrorMsg());
    while ($dsrow = $dsresult->FetchRow())
    {
        $asdel = "DELETE FROM {$dbprefix}answers WHERE qid={$dsrow['qid']}";
        $asres = $connect->Execute($asdel);
        $cddel = "DELETE FROM {$dbprefix}conditions WHERE qid={$dsrow['qid']}";
        $cdres = $connect->Execute($cddel) or safe_die ("Delete conditions failed<br />$cddel<br />".$connect->ErrorMsg());
        $qadel = "DELETE FROM {$dbprefix}question_attributes WHERE qid={$dsrow['qid']}";
        $qares = $connect->Execute($qadel);
    }

    $qdel = "DELETE FROM {$dbprefix}questions WHERE sid=$surveyid";
    $qres = $connect->Execute($qdel);

    $scdel = "DELETE FROM {$dbprefix}assessments WHERE sid=$surveyid";
    $scres = $connect->Execute($scdel);

    $gdel = "DELETE FROM {$dbprefix}groups WHERE sid=$surveyid";
    $gres = $connect->Execute($gdel);

    $slsdel = "DELETE FROM {$dbprefix}surveys_languagesettings WHERE surveyls_survey_id=$surveyid";
    $slsres = $connect->Execute($slsdel);

    $srdel = "DELETE FROM {$dbprefix}surveys_rights WHERE sid=$surveyid";
    $srres = $connect->Execute($srdel);

    $srdel = "DELETE FROM {$dbprefix}saved_control WHERE sid=$surveyid";
    $srres = $connect->Execute($srdel);

    $sdel = "DELETE FROM {$dbprefix}surveys WHERE sid=$surveyid";
    $sres = $connect->Execute($sdel);

    $sdel = "DELETE {$dbprefix}quota_languagesettings FROM {$dbprefix}quota_languagesettings, {$dbprefix}quota WHERE {$dbprefix}quota_languagesettings.quotals_quota_id={$dbprefix}quota.id and sid=$surveyid";
    $sres = $connect->Execute($sdel);

    $sdel = "DELETE FROM {$dbprefix}quota WHERE sid=$surveyid";
    $sres = $connect->Execute($sdel);

    $sdel = "DELETE FROM {$dbprefix}quota_members WHERE sid=$surveyid;";
    $sres = $connect->Execute($sdel);

    $deletesurveyoutput .= "\t<p>".$clang->gT("This survey has been deleted.")."<br /><br />\n";
    $deletesurveyoutput .= "\t<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";

    $surveyid=false;

}
$deletesurveyoutput .= "</div><br />&nbsp;\n";
?>
