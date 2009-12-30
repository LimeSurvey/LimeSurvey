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
$ok = returnglobal('ok');

$resetsurveylogicoutput = "<br />\n";
$resetsurveylogicoutput .= "<table class='alertbox' >\n";
$resetsurveylogicoutput .= "\t<tr ><td colspan='2' height='4'><font size='1'><strong>".$clang->gT("Reset Survey Logic")."</strong></font></td></tr>\n";

if (!isset($surveyid) || !$surveyid)
{
    $resetsurveylogicoutput .= "\t<tr ><td align='center'>\n";
	$resetsurveylogicoutput .= "<br /><font color='red'><strong>".$clang->gT("Error")."</strong></font><br />\n";
	$resetsurveylogicoutput .= $clang->gT("You have not selected a survey to delete")."<br /><br />\n";
	$resetsurveylogicoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n";
	$resetsurveylogicoutput .= "</td></tr></table>\n";
	$resetsurveylogicoutput .= "</body>\n</html>";
	return;
}

if (!isset($ok) || !$ok)
{
	$resetsurveylogicoutput .= "\t<tr>\n";
	$resetsurveylogicoutput .= "\t\t<td align='center'><br />\n";
	$resetsurveylogicoutput .= "\t\t\t<font color='red'><strong>".$clang->gT("Warning")."</strong></font><br />\n";
	$resetsurveylogicoutput .= "\t\t\t<strong>".$clang->gT("You are about to delete all conditions on this survey's questions")." ($surveyid)</strong><br /><br />\n";
	$resetsurveylogicoutput .= "\t\t\t".$clang->gT("We recommend that before you proceed, you export the entire survey from the main administration screen.")."\n";

	$resetsurveylogicoutput .= "\t\t</td>\n";
	$resetsurveylogicoutput .= "\t</tr>\n";
	$resetsurveylogicoutput .= "\t<tr>\n";
	$resetsurveylogicoutput .= "\t\t<td align='center'><br />\n";
	$resetsurveylogicoutput .= "\t\t\t<input type='submit'  value='".$clang->gT("Cancel")."' onclick=\"window.open('admin.php?sid=$surveyid', '_top')\" /><br />\n";
//	$resetsurveylogicoutput .= "\t\t\t<input type='submit'  value='".$clang->gT("Delete")."' onclick=\"window.open('$scriptname?action=resetsurveylogic&amp;sid=$surveyid&amp;ok=Y','_top')\" />\n";
	$resetsurveylogicoutput .= "\t\t\t<input type='submit'  value='".$clang->gT("Delete")."' onclick=\"".get2post("$scriptname?action=resetsurveylogic&amp;sid=$surveyid&amp;ok=Y")."\" />\n";
	$resetsurveylogicoutput .= "\t\t</td>\n";
	$resetsurveylogicoutput .= "\t</tr>\n";
	$resetsurveylogicoutput .= "\n";
}

else //delete conditions in the survey
{
	$dict = NewDataDictionary($connect);

	$resetlogicquery = "DELETE FROM {$dbprefix}conditions WHERE qid in (select qid from {$dbprefix}questions where sid=$surveyid)";
	$resetlogicresult = $connect->Execute($resetlogicquery) or safe_die ("Couldn't delete conditions<br />$resetlogicquery<br />".$connect->ErrorMsg());

	$resetsurveylogicoutput .= "\t<tr>\n";
	$resetsurveylogicoutput .= "\t\t<td align='center'><br />\n";
	$resetsurveylogicoutput .= "\t\t\t<strong>".$clang->gT("All conditions in this survey have been deleted.")."<br /><br />\n";
	$resetsurveylogicoutput .= "\t\t\t<input type='submit' value='".$clang->gT("Continue")."' onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" />\n";
	$resetsurveylogicoutput .= "\t\t</strong></td>\n";
	$resetsurveylogicoutput .= "\t</tr>\n";
	$surveyid=false;

}
$resetsurveylogicoutput .= "</table><br />&nbsp;\n";

?>
