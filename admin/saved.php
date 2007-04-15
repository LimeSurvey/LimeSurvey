<?php
/*
#############################################################
# >>> PHPSurveyor  						    				#
#############################################################
# > Author:  Jason Cleeland									#
# > E-mail:  jason@cleeland.org								#
# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
# >          CARLTON SOUTH 3053, AUSTRALIA					#
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
include_once(dirname(__FILE__).'/../config.php');
include_once("login_check.php");

$surveyid=returnglobal('sid');
$srid=returnglobal('srid');
$scid=returnglobal('scid');
$subaction=returnglobal('subaction');

//Ensure script is not run directly, avoid path disclosure
if (empty($surveyid)) {die ($clang->gT("Error")." - Cannot run this script directly");}

$thissurvey=getSurveyInfo($surveyid);
$savedsurveyoutput='';
$surveytable = db_table_name("survey_".$surveyid);

if ($subaction == "delete" && $surveyid && $scid)
{
	$query = "DELETE FROM {$dbprefix}saved_control
			  WHERE scid=$scid
			  AND sid=$surveyid
			  ";
	if ($result = $connect->Execute($query))
	{
		//If we were succesful deleting the saved_control entry,
		//then delete the rest
		$query = "DELETE FROM {$surveytable} WHERE id={$srid}";
		$result = $connect->Execute($query) or die("Couldn't delete");

	}
	else
	{
		$savedsurveyoutput .=  "Couldn't delete<br />$query<br />".$connect->ErrorMsg();
	}
}

$savedsurveyoutput .= "<table><tr><td></td></tr></table>\n"
."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
$savedsurveyoutput .= "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
. $clang->gT("Browse Saved Responses").":</strong> <font color='#EEEEEE'>".$thissurvey['name']."</font></font></td></tr>\n";
$savedsurveyoutput .= savedmenubar();
$savedsurveyoutput .= "</table>\n";
$savedsurveyoutput .= "<table><tr><td></td></tr></table>\n"
."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
$savedsurveyoutput .= "<tr><td>";

switch ($subaction)
{
	case "all":
	case "delete":
	$savedsurveyoutput .= "<center>".$setfont.$clang->gT("Saved Responses:") . " ". getSavedCount($surveyid)."</font></center>";
	showSavedList($surveyid);
	break;
	default:
	$savedsurveyoutput .= "<center>".$setfont.$clang->gT("Saved Responses:") . " ". getSavedCount($surveyid)."</font></center>";
}
$savedsurveyoutput .= "</td></tr></table><br />&nbsp;\n";

function showSavedList($surveyid)
{
	global $dbprefix, $connect, $clang, $savedsurveyoutput, $scriptname, $surveytable;
	$query = "SELECT scid, srid, identifier, ip, saved_date, email, access_code\n"
	."FROM {$dbprefix}saved_control\n"
	."WHERE sid=$surveyid\n"
	."ORDER BY saved_date desc";
	$result = db_execute_assoc($query) or die ("Couldn't summarise saved entries<br />$query<br />".$connect->ErrorMsg());
	if ($result->RecordCount() > 0)
	{
		$savedsurveyoutput .= "<table class='outlinetable' cellspacing='0' align='center'>\n";
		$savedsurveyoutput .= "<tr><th>SCID</th><th>"
		.$clang->gT("Identifier")."</th><th>"
		.$clang->gT("IP Address")."</th><th>"
		.$clang->gT("Date Saved")."</th><th>"
		.$clang->gT("Email Address")."</th><th>"
		.$clang->gT("Action")."</th>"
		."</tr>\n";
		while($row=$result->FetchRow())
		{
			$savedsurveyoutput .= "<tr>
				<td>".$row['scid']."</td>
				<td>".$row['identifier']."</td>
				<td>".$row['ip']."</td>
				<td>".$row['saved_date']."</td>
				<td><a href='mailto:".$row['email']."'>".$row['email']."</td>
				<td align='center'>
				[<a href='$scriptname?action=saved&amp;sid=$surveyid&amp;subaction=delete&amp;scid=".$row['scid']."&amp;srid=".$row['srid']."'"
			." onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?")."\")'"
			.">".$clang->gT("Delete")."</a>]
				[<a href='".$scriptname."?action=dataentry&amp;subaction=edit&amp;id=".$row['srid']."&amp;sid={$surveyid}&amp;surveytable={$surveytable}'>".$clang->gT("Edit")."</a>]
				</td>
			   </tr>\n";
		} // while
		$savedsurveyoutput .= "</table><br />&nbsp\n";
	}
}

//				[<a href='saved.php?sid=$surveyid&amp;action=remind&amp;scid=".$row['scid']."'>".$clang->gT("Remind")."</a>]
//               c_schmitz: Since its without function at the moment i removed it from the above lines

function savedmenubar()
{
	global $surveyid, $scriptname, $imagefiles, $clang;
	//BROWSE MENU BAR
	if (!isset($surveyoptions)) {$surveyoptions="";}
	$surveyoptions .= "\t<tr bgcolor='#999999'>\n"
	. "\t\t<td>\n"
	. "\t\t\t<a href='$scriptname?sid=$surveyid' onmouseout=\"hideTooltip()\" " .
			"onmouseover=\"showTooltip(event,'".$clang->gT("Return to Survey Administration", "js")."')\" >" .
			"<img name='Administration' src='$imagefiles/home.png' title='' align='left'></a>\n"
	. "\t\t\t<img src='$imagefiles/blank.gif' alt='' width='11' border='0' hspace='0' align='left'>\n"
	. "\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
	. "\t\t\t<a href='$scriptname?action=saved&amp;sid=$surveyid' onmouseout=\"hideTooltip()\" " .
			"onmouseover=\"showTooltip(event,'".$clang->gT("Show summary information", "js")."')\">" .
			"<img name='SurveySummary' src='$imagefiles/summary.png' title=''  align='left'></a>\n"
	. "\t\t\t<a href='$scriptname?action=saved&amp;sid=$surveyid&amp;subaction=all' onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Display Responses", "js")."')\">" .
	"<img name='ViewAll' src='$imagefiles/document.png' title=''  align='left'></a>\n"
	//. "\t\t\t<input type='image' name='ViewLast' src='$imagefiles/viewlast.png' title='"
	//. $clang->gT("Display Last 50 Responses")."'  align='left'  onclick=\"window.open('saved.php?sid=$surveyid&action=all&limit=50&order=desc', '_top')\">\n"
	. "\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt=''>\n"
	. "\t\t</td>\n"
	. "\t</tr>\n";
	return $surveyoptions;
}
?>
