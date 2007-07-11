<?php
/*
#############################################################
# >>> LimeSurvey  										#
#############################################################
# > Author:  Jason Cleeland									#
# > E-mail:  jason@cleeland.org								#
# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
# >          CARLTON SOUTH 3053, AUSTRALIA
# > Date: 	 20 February 2003								#
#															#
# This set of scripts allows you to develop, publish and	#
# perform data-entry on surveys.							#
#############################################################
#															#
#	Copyright (C) 2003  Jason Cleeland						#
#															#
# This program is free software; you can redistribute 		#
# it and/or modify it under the terms of the GNU General 	#
# Public License Version 2 as published by the Free         #
# Software Foundation.										#
#															#
#															#
# This program is distributed in the hope that it will be 	#
# useful, but WITHOUT ANY WARRANTY; without even the 		#
# implied warranty of MERCHANTABILITY or FITNESS FOR A 		#
# PARTICULAR PURPOSE.  See the GNU General Public License 	#
# for more details.											#
#															#
# You should have received a copy of the GNU General 		#
# Public License along with this program; if not, write to 	#
# the Free Software Foundation, Inc., 59 Temple Place - 	#
# Suite 330, Boston, MA  02111-1307, USA.					#
#############################################################
*/
if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}
if (isset($_GET['sid'])) {$surveyid = $_GET['sid'];}
if (isset($_GET['ok'])) {$ok = $_GET['ok'];}


$actsurquery = "SELECT delete_survey FROM {$dbprefix}surveys_rights WHERE sid=$surveyid AND uid = ".$_SESSION['loginID']; //Getting rights for this survey
//$actsurresult = $connect->Execute($actsurquery) or die($connect->ErrorMsg());	
$actsurresult = &db_execute_assoc($actsurquery);
$actsurrows = $actsurresult->FetchRow();
$deletesurveyoutput = "";

if($actsurrows['delete_survey'])
	{

	$deletesurveyoutput .= "<br />\n";
	$deletesurveyoutput .= "<table class='alertbox' >\n";
	$deletesurveyoutput .= "\t<tr ><td colspan='2' height='4'><font size='1'><strong>".$clang->gT("Delete Survey")."</strong></font></td></tr>\n";
	
	if (!isset($surveyid) || !$surveyid)
	{
    	$deletesurveyoutput .= "\t<tr ><td align='center'>\n";
		$deletesurveyoutput .= "<br /><font color='red'><strong>".$clang->gT("Error")."</strong></font><br />\n";
		$deletesurveyoutput .= $clang->gT("You have not selected a survey to delete")."<br /><br />\n";
		$deletesurveyoutput .= "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n";
		$deletesurveyoutput .= "</td></tr></table>\n";
		$deletesurveyoutput .= "</body>\n</html>";
		return;
	}
	
	if (!isset($ok) || !$ok)
	{
		$tablelist = $connect->MetaTables();
	
		$deletesurveyoutput .= "\t<tr>\n";
		$deletesurveyoutput .= "\t\t<td align='center'><br />\n";
		$deletesurveyoutput .= "\t\t\t<font color='red'><strong>".$clang->gT("Warning")."</strong></font><br />\n";
		$deletesurveyoutput .= "\t\t\t<strong>".$clang->gT("You are about to delete this survey")." ($surveyid)</strong><br /><br />\n";
		$deletesurveyoutput .= "\t\t\t".$clang->gT("This process will delete this survey, and all related groups, questions answers and conditions.")."<br /><br />\n";
		$deletesurveyoutput .= "\t\t\t".$clang->gT("We recommend that before you delete this survey you export the entire survey from the main administration screen.")."\n";
	
		if (in_array("{$dbprefix}survey_$surveyid", $tablelist))
		{
			$deletesurveyoutput .= "\t\t\t<br /><br />\n".$clang->gT("This survey is active and a responses table exists. If you delete this survey, these responses will be deleted. We recommend that you export the responses before deleting this survey.")."<br /><br />\n";
		}
	
		if (in_array("{$dbprefix}tokens_$surveyid", $tablelist))
		{
			$deletesurveyoutput .= "\t\t\t".$clang->gT("This survey has an associated tokens table. If you delete this survey this tokens table will be deleted. We recommend that you export or backup these tokens before deleting this survey.")."<br /><br />\n";
		}
	
		$deletesurveyoutput .= "\t\t</font></td>\n";
		$deletesurveyoutput .= "\t</tr>\n";
		$deletesurveyoutput .= "\t<tr>\n";
		$deletesurveyoutput .= "\t\t<td align='center'><br />\n";
		$deletesurveyoutput .= "\t\t\t<input type='submit'  value='".$clang->gT("Cancel")."' onclick=\"window.open('admin.php?sid=$surveyid', '_top')\" /><br />\n";
		$deletesurveyoutput .= "\t\t\t<input type='submit'  value='".$clang->gT("Delete")."' onclick=\"window.open('$scriptname?action=deletesurvey&amp;sid=$surveyid&amp;ok=Y','_top')\" />\n";
		$deletesurveyoutput .= "\t\t</td>\n";
		$deletesurveyoutput .= "\t</tr>\n";
		$deletesurveyoutput .= "\n";
	}
	
	else //delete the survey
	{
		$tablelist = $connect->MetaTables();
		$dict = NewDataDictionary($connect);
	
		if (in_array("{$dbprefix}survey_$surveyid", $tablelist)) //delete the survey_$surveyid table
		{			
			$dsquery = $dict->DropTableSQL("{$dbprefix}survey_$surveyid");	
			//$dict->ExecuteSQLArray($sqlarray);		
			$dsresult = $dict->ExecuteSQLArray($dsquery) or die ("Couldn't \"$dsquery\" because <br />".$connect->ErrorMsg());
		}
	
		if (in_array("{$dbprefix}tokens_$surveyid", $tablelist)) //delete the tokens_$surveyid table
		{
			$dsquery = $dict->DropTableSQL("{$dbprefix}tokens_$surveyid");
			$dsresult = $dict->ExecuteSQLArray($dsquery) or die ("Couldn't \"$dsquery\" because <br />".$connect->ErrorMsg());
		}
	
		$dsquery = "SELECT qid FROM {$dbprefix}questions WHERE sid=$surveyid";
		$dsresult = db_execute_assoc($dsquery) or die ("Couldn't find matching survey to delete<br />$dsquery<br />".$connect->ErrorMsg());
		while ($dsrow = $dsresult->FetchRow())
		{
			$asdel = "DELETE FROM {$dbprefix}answers WHERE qid={$dsrow['qid']}";
			$asres = $connect->Execute($asdel);
			$cddel = "DELETE FROM {$dbprefix}conditions WHERE qid={$dsrow['qid']}";
			$cdres = $connect->Execute($cddel) or die ("Delete conditions failed<br />$cddel<br />".$connect->ErrorMsg());
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
	
		$sdel = "DELETE FROM {$dbprefix}surveys WHERE sid=$surveyid";
		$sres = $connect->Execute($sdel);

        $srdel = "DELETE FROM {$dbprefix}surveys_rights WHERE sid=$surveyid";
		$srres = $connect->Execute($srdel);

	
		$deletesurveyoutput .= "<table width='100%' align='center'>\n";
		$deletesurveyoutput .= "\t<tr>\n";
		$deletesurveyoutput .= "\t\t<td align='center'>$setfont<br />\n";
		$deletesurveyoutput .= "\t\t\t<strong>".$clang->gT("This survey has been deleted.")."<br /><br />\n";
		$deletesurveyoutput .= "\t\t\t<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\">\n";
		$deletesurveyoutput .= "\t\t</strong></font></td>\n";
		$deletesurveyoutput .= "\t</tr>\n";
		$deletesurveyoutput .= "</table>\n";
	}
	$deletesurveyoutput .= "</table><br />&nbsp;\n";
	
	}
else
	{
	$action = "delsurvey";
	include("access_denied.php");
	include("admin.php");	
	}
?>
