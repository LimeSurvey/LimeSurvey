<?php
/*
#############################################################
# >>> PHPSurveyor  										#
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
if (isset($_GET['sid'])) {$surveyid = $_GET['sid'];}
if (isset($_GET['ok'])) {$ok = $_GET['ok'];}

require_once(dirname(__FILE__).'/../config.php');

include_once("login_check.php");

$actsurquery = "SELECT delete_survey FROM {$dbprefix}surveys_rights WHERE sid=$surveyid AND uid = ".$_SESSION['loginID']; //Getting rights for this survey
//$actsurresult = $connect->Execute($actsurquery) or die($connect->ErrorMsg());	
$actsurresult = &db_execute_assoc($actsurquery);
$actsurrows = $actsurresult->FetchRow();

if($actsurrows['delete_survey'])
	{

	sendcacheheaders();                      // HTTP/1.0
	
	echo $htmlheader;
	
	echo "<br />\n";
	echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>".$clang->gT("Delete Survey")."</strong></font></td></tr>\n";
	echo "\t<tr bgcolor='#CCCCCC'><td align='center'>\n";
	
	if (!isset($surveyid) || !$surveyid)
	{
		echo "<br /><font color='red'><strong>".$clang->gT("Error")."</strong></font><br />\n";
		echo $clang->gT("You have not selected a survey to delete")."<br /><br />\n";
		echo "<input type='submit' value='".$clang->gT("Main Admin Screen")."' onClick=\"window.open('$scriptname', '_top')\">\n";
		echo "</td></tr></table>\n";
		echo "</body>\n</html>";
		exit;
	}
	
	if (!isset($ok) || !$ok)
	{
		$tablelist = $connect->MetaTables();
	
		echo "<table width='100%' align='center'>\n";
		echo "\t<tr>\n";
		echo "\t\t<td align='center'>$setfont<br />\n";
		echo "\t\t\t<font color='red'><strong>".$clang->gT("Warning")."</strong></font><br />\n";
		echo "\t\t\t<strong>".$clang->gT("You are about to delete this survey")." ($surveyid)</strong><br /><br />\n";
		echo "\t\t\t".$clang->gT("This process will delete this survey, and all related groups, questions answers and conditions.")."<br /><br />\n";
		echo "\t\t\t".$clang->gT("We recommend that before you delete this survey you export the entire survey from the main administration screen.")."\n";
	
		if (in_array("{$dbprefix}survey_$surveyid", $tablelist))
		{
			echo "\t\t\t<br /><br />\n".$clang->gT("This survey is active and a responses table exists. If you delete this survey, these responses will be deleted. We recommend that you export the responses before deleting this survey.")."<br /><br />\n";
		}
	
		if (in_array("{$dbprefix}tokens_$surveyid", $tablelist))
		{
			echo "\t\t\t".$clang->gT("This survey has an associated tokens table. If you delete this survey this tokens table will be deleted. We recommend that you export or backup these tokens before deleting this survey.")."<br /><br />\n";
		}
	
		echo "\t\t</font></td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td align='center'><br />\n";
		echo "\t\t\t<input type='submit'  value='".$clang->gT("Cancel")."' onClick=\"window.open('admin.php?sid=$surveyid', '_top')\" /><br />\n";
		echo "\t\t\t<input type='submit'  value='".$clang->gT("Delete")."' onClick=\"window.open('{$_SERVER['PHP_SELF']}?sid=$surveyid&amp;ok=Y','_top')\" />\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "</table>\n";
	}
	
	else //delete the survey
	{
		$tablelist = $connect->MetaTables();
	
		if (in_array("{$dbprefix}survey_$surveyid", $tablelist)) //delete the survey_$surveyid table
		{
			$dsquery = "DROP TABLE `{$dbprefix}survey_$surveyid`";
			$dsresult = $connect->Execute($dsquery) or die ("Couldn't \"$dsquery\" because <br />".$connect->ErrorMsg());
		}
	
		if (in_array("{$dbprefix}tokens_$surveyid", $tablelist)) //delete the tokens_$surveyid table
		{
			$dsquery = "DROP TABLE `{$dbprefix}tokens_$surveyid`";
			$dsresult = $connect->Execute($dsquery) or die ("Couldn't \"$dsquery\" because <br />".$connect->ErrorMsg());
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
    	$slsres = mysql_query($slsdel);
	
		$sdel = "DELETE FROM {$dbprefix}surveys WHERE sid=$surveyid";
		$sres = $connect->Execute($sdel);

        $srdel = "DELETE FROM {$dbprefix}surveys_rights WHERE sid=$surveyid";
		$srres = $connect->Execute($srdel);

	
		echo "<table width='100%' align='center'>\n";
		echo "\t<tr>\n";
		echo "\t\t<td align='center'>$setfont<br />\n";
		echo "\t\t\t<strong>".$clang->gT("This survey has been deleted.")."<br /><br />\n";
		echo "\t\t\t<input type='submit' value='".$clang->gT("Main Admin Screen")."' onClick=\"window.open('$scriptname', '_top')\">\n";
		echo "\t\t</strong></font></td>\n";
		echo "\t</tr>\n";
		echo "</table>\n";
	}
	echo "</td></tr></table>\n";
	echo "</body>\n</html>";
	
	}
else
	{
	$action = "delsurvey";
	include("access_denied.php");
	include("admin.php");	
	}
?>
