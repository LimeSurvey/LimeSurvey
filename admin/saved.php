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
	# Public License as published by the Free Software 			#
	# Foundation; either version 2 of the License, or (at your 	#
	# option) any later version.								#
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
$surveyid=returnglobal('sid');
$action=returnglobal('action');
$scid=returnglobal('scid');

//Ensure script is not run directly, avoid path disclosure
if (empty($surveyid)) {die (_("Error")." - Cannot run this script directly");}

sendcacheheaders();

$thissurvey=getSurveyInfo($surveyid);
echo $htmlheader;

if ($action == "delete" && $surveyid && $scid) 
	{
    $query = "DELETE FROM {$dbprefix}saved_control
			  WHERE scid=$scid
			  AND sid=$surveyid
			  ";
	if ($result = $connect->Execute($query)) 
		{
		//If we were succesful deleting the saved_control entry, 
		//then delete the rest
		$query = "DELETE FROM {$dbprefix}saved
				  WHERE scid=$scid";
		$result = $connect->Execute($query) or die("Couldn't delete");
	    
		} 
	else
		{
		echo  "Couldn't delete<br />$query<br />".$connect->ErrorMsg();
		}
	}

echo "<table><tr><td></td></tr></table>\n"
	."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	. _("Browse Saved Responses").":</strong> <font color='#EEEEEE'>".$thissurvey['name']."</font></font></td></tr>\n";
echo savedmenubar();
echo "</table>\n";
echo "<table><tr><td></td></tr></table>\n"
	."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
echo "<tr><td>";
switch ($action) 
	{
	case "all":
	case "delete":
		echo "<center>".$setfont._("Saved Responses:") . " ". getSavedCount($surveyid)."</font></center>";
		showSavedList($surveyid);
		break;
	default:
		echo "<center>".$setfont._("Saved Responses:") . " ". getSavedCount($surveyid)."</font></center>";
	}
echo "</td></tr></table>\n";

function showSavedList($surveyid)
	{
	global $dbprefix, $connect;
	$query = "SELECT scid, identifier, ip, saved_date, email, access_code\n"
			."FROM {$dbprefix}saved_control\n"
			."WHERE sid=$surveyid\n"
			."ORDER BY saved_date desc";
	$result = db_execute_assoc($query) or die ("Couldn't summarise saved entries<br />$query<br />".$connect->ErrorMsg());
	if ($result->RecordCount() > 0)
		{
		echo "<table class='outlinetable' cellspacing='0' align='center'>\n";
		echo "<tr><th>SCID</th><th>"
			._("Identifier")."</th><th>"
			._("IP Address")."</th><th>"
			._("Date Saved")."</th><th>"
			._("Email Address")."</th><th>"
			._("Action")."</th>"
			."</tr>\n";
		while($row=$result->FetchRow())
			{
			echo "<tr>
				<td>".$row['scid']."</td>
				<td>".$row['identifier']."</td>
				<td>".$row['ip']."</td>
				<td>".$row['saved_date']."</td>
				<td><a href='mailto:".$row['email']."'>".$row['email']."</td>
				<td align='center'>
				[<a href='saved.php?sid=$surveyid&amp;action=delete&amp;scid=".$row['scid']."'"
				." onClick='return confirm(\""._("Are you sure you want t."\")'"
				.">"._("Delete")."</a>]
				[<a href='dataentry.php?sid=$surveyid&amp;action=editsaved&amp;identifier=".rawurlencode ($row['identifier'])."&amp;scid=".$row['scid']."&amp;accesscode=".$row['access_code']."'>"._("Edit")."</a>]
				</td>
			   </tr>\n";
			} // while
		echo "</table>\n";
		}
	}

//				[<a href='saved.php?sid=$surveyid&amp;action=remind&amp;scid=".$row['scid']."'>"._("Remind")."</a>]
//               c_schmitz: Since its without function at the moment i removed it from the above lines

function savedmenubar()
	{
	global $surveyid, $scriptname, $imagefiles;
	//BROWSE MENU BAR
	if (!isset($surveyoptions)) {$surveyoptions="";}
	$surveyoptions .= "\t<tr bgcolor='#999999'>\n"
					. "\t\t<td>\n"
					. "\t\t\t<input type='image' name='Administration' src='$imagefiles/home.png' title='"
					. _("Return to Survey Administration")."' align='left'  onClick=\"window.open('$scriptname?sid=$surveyid', '_top')\">\n"
					. "\t\t\t<img src='$imagefiles/blank.gif' alt='' width='11' border='0' hspace='0' align='left'>\n"
					. "\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
					. "\t\t\t<input type='image' name='SurveySummary' src='$imagefiles/summary.png' title='"
					. _("Show summary information")."'  align='left' onClick=\"window.open('saved.php?sid=$surveyid', '_top')\">\n"
					. "\t\t\t<input type='image' name='ViewAll' src='$imagefiles/document.png' title='"
					. _("Display Responses")."'  align='left'  onClick=\"window.open('saved.php?sid=$surveyid&amp;action=all', '_top')\">\n"
					//. "\t\t\t<input type='image' name='ViewLast' src='$imagefiles/viewlast.png' title='"
					//. _("Display Last 50 Responses")."'  align='left'  onClick=\"window.open('saved.php?sid=$surveyid&action=all&limit=50&order=desc', '_top')\">\n"
					. "\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt=''>\n"
					. "\t\t</td>\n"
					. "\t</tr>\n";
	return $surveyoptions;
	}
?>
