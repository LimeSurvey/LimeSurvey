<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
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
include_once("config.php");
$surveyid=returnglobal('sid');
$action=returnglobal('action');
$scid=returnglobal('scid');
if (!$surveyid) {echo _ERROR;}

sendcacheheaders();

$thissurvey=getSurveyInfo($surveyid);
echo $htmlheader;

if ($action == "delete" && $surveyid && $scid) 
	{
    $query = "DELETE FROM {$dbprefix}saved_control
			  WHERE scid=$scid
			  AND sid=$surveyid
			  ";
	if ($result = mysql_query($query)) 
		{
		//If we were succesful deleting the saved_control entry, 
		//then delete the rest
		$query = "DELETE FROM {$dbprefix}saved
				  WHERE scid=$scid";
		$result = mysql_query($query) or die("Couldn't delete");
	    
		} 
	else
		{
		echo  "Couldn't delete<br />$query<br />".mysql_error();
		}
	}

echo "<table height='1'><tr><td></td></tr></table>\n"
	."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"
	. _BROWSESAVED.":</b> <font color='#EEEEEE'>".$thissurvey['name']."</font></font></td></tr>\n";
echo savedmenubar();
echo "</table>\n";
echo "<table height='1'><tr><td></td></tr></table>\n"
	."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
echo "<tr><td>";
switch ($action) 
	{
	case "all":
	case "delete":
		echo "<center>".$setfont._SV_RESPONSES . " ". getSavedCount($surveyid)."</font></center>";
		showSavedList($surveyid);
		break;
	default:
		echo "<center>".$setfont._SV_RESPONSES . " ". getSavedCount($surveyid)."</font></center>";
	}
echo "</td></tr></table>\n";

function showSavedList($surveyid)
	{
	global $dbprefix;
	$query = "SELECT scid, identifier, ip, saved_date, email, access_code\n"
			."FROM {$dbprefix}saved_control\n"
			."WHERE sid=$surveyid\n"
			."ORDER BY saved_date desc";
	$result = mysql_query($query) or die ("Couldn't summarise saved entries<br />$query<br />".mysql_error());
	if (mysql_num_rows($result) > 0)
		{
		echo "<table class='outlinetable' cellspacing='0' align='center'>\n";
		echo "<tr><th>SCID</th><th>"
			._SV_IDENTIFIER."</th><th>"
			._SV_IP."</th><th>"
			._SV_DATE."</th><th>"
			._EMAIL."</th><th>"
			._AL_ACTION."</th>"
			."</tr>\n";
		while($row=mysql_fetch_array($result))
			{
			echo "<tr>
				<td>".$row['scid']."</td>
				<td>".$row['identifier']."</td>
				<td>".$row['ip']."</td>
				<td>".$row['saved_date']."</td>
				<td><a href='mailto:".$row['email']."'>".$row['email']."</td>
				<td align='center'>
				[<a href='saved.php?sid=$surveyid&amp;action=delete&amp;scid=".$row['scid']."'"
				." onClick='return confirm(\""._DR_RUSURE."\")'"
				.">"._DELETE."</a>]
				[<a href='dataentry.php?sid=$surveyid&action=editsaved&amp;identifier=".rawurlencode ($row['identifier'])."&amp;scid=".$row['scid']."&amp;accesscode=".$row['access_code']."'>"._SV_EDIT."</a>]
				</td>
			   </tr>\n";
			} // while
		echo "</table>\n";
		}
	}

//				[<a href='saved.php?sid=$surveyid&amp;action=remind&amp;scid=".$row['scid']."'>"._SV_REMIND."</a>]
//               c_schmitz: Since its without function at the moment i removed it from the above lines

function savedmenubar()
	{
	global $surveyid, $scriptname, $imagefiles;
	//BROWSE MENU BAR
	if (!isset($surveyoptions)) {$surveyoptions="";}
	$surveyoptions .= "\t<tr bgcolor='#999999'>\n"
					. "\t\t<td>\n"
					. "\t\t\t<input type='image' name='Administration' src='$imagefiles/home.gif' title='"
					. _B_ADMIN_BT."' border='0' align='left' hspace='0' onClick=\"window.open('$scriptname?sid=$surveyid', '_top')\">\n"
					. "\t\t\t<img src='$imagefiles/blank.gif' alt='-' width='11' border='0' hspace='0' align='left'>\n"
					. "\t\t\t<img src='$imagefiles/seperator.gif' alt='|' border='0' hspace='0' align='left'>\n"
					. "\t\t\t<input type='image' name='SurveySummary' src='$imagefiles/summary.gif' title='"
					. _B_SUMMARY_BT."' border='0' align='left' hspace='0' onClick=\"window.open('saved.php?sid=$surveyid', '_top')\">\n"
					. "\t\t\t<input type='image' name='ViewAll' src='$imagefiles/document.gif' title='"
					. _B_ALL_BT."' border='0' align='left' hspace='0' onClick=\"window.open('saved.php?sid=$surveyid&action=all', '_top')\">\n"
					//. "\t\t\t<input type='image' name='ViewLast' src='$imagefiles/viewlast.gif' title='"
					//. _B_LAST_BT."' border='0' align='left' hspace='0' onClick=\"window.open('saved.php?sid=$surveyid&action=all&limit=50&order=desc', '_top')\">\n"
					. "\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left'>\n"
					. "\t\t</td>\n"
					. "\t</tr>\n";
	return $surveyoptions;
	}
?>