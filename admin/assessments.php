<?php
/*
#############################################################
# >>> PHPSurveyor  										    #
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
require_once(dirname(__FILE__).'/../config.php');

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($action)) {echo $action=returnglobal('action');}

$actsurquery = "SELECT edit_survey_property FROM {$dbprefix}surveys_rights WHERE sid=$surveyid AND uid = ".$_SESSION['loginID']; //Getting rights for this survey
$actsurresult = $connect->Execute($actsurquery) or die($connect->ErrorMsg());		
$actsurrows = $actsurresult->FetchRow();

if($actsurrows['edit_survey_property']){

	if ($action == _("Add")) {
		$inserttable=$dbprefix."assessments";
		$query = $connect->GetInsertSQL($inserttable, array(
		'sid' => $surveyid,
		'scope' => $_POST['scope'],
		'gid' => $_POST['gid'],
		'minimum' => $_POST['minimum'],
		'maximum' => $_POST['maximum'],
		'name' => $_POST['name'],
		'message' => $_POST['message'],
		'link' => $_POST['link'] ));
		$result=$connect->Execute($query) or die("Error inserting<br />$query<br />".$connect->ErrorMsg());
	} elseif ($action == _("Update")) {
		$query = "UPDATE {$dbprefix}assessments
				  SET scope='".$_POST['scope']."',
				  gid=".$_POST['gid'].",
				  minimum='".$_POST['minimum']."',
				  maximum='".$_POST['maximum']."',
				  name='".sanitize_sql_string($_POST['name'])."',
				  message='".sanitize_sql_string($_POST['message'])."',
				  link='".sanitize_sql_string($_POST['link'])."'
				  WHERE id=".$_POST['id'];
		$result = $connect->Execute($query) or die("Error updating<br />$query<br />".$connect->ErrorMsg());
	} elseif ($action == "delete") {
		$query = "DELETE FROM {$dbprefix}assessments
				  WHERE id=".$_POST['id'];
		$result=$connect->Execute($query);
	}
	
	sendcacheheaders();
	echo $htmlheader;
	echo "<table><tr><td height='1'></td></tr></table>\n"
	."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
	. _("Assessments")."</strong></font></td></tr>\n";
	
	echo "\t<tr bgcolor='#999999'>\n"
	. "\t\t<td>\n"
	. "\t\t\t<a href=\"#\" onClick=\"window.open('$scriptname?sid=$surveyid', '_top')\" onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'". _("Return to Survey Administration")."');return false\">" .
			"<img name='Administration' src='$imagefiles/home.png' title='' alt='' align='left'  /></a>\n"
	. "\t\t\t<img src='$imagefiles/blank.gif' alt='' width='11' border='0' hspace='0' align='left' />\n"
	. "\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n"
	. "\t\t</td>\n"
	. "\t</tr>\n";
	echo "</table>";
	
	if ($surveyid == "") {
		echo _("No SID Provided");
		exit;
	}
	
	$assessments=getAssessments($surveyid);
	//echo "<pre>";print_r($assessments);echo "</pre>";
	$groups=getGroups($surveyid);
	$groupselect="<select name='gid'>\n";
	foreach($groups as $group) {
		$groupselect.="<option value='".$group['gid']."'>".$group['group_name']."</option>\n";
	}
	$groupselect .="</select>\n";
	$headings=array(_("Scope"), _("Group"), _("Minimum"), _("Maximum"), _("Heading"), _("Message"), _("URL"));
	$inputs=array("<select name='scope'><option value='T'>"._("Total")."</option><option value='G'>"._("Group")."</option></select>",
	$groupselect,
	"<input type='text' name='minimum'>",
	"<input type='text' name='maximum'>",
	"<input type='text' name='name'>",
	"<textarea name='message'></textarea>",
	"<input type='text' name='link'>");
	$actiontitle=_("Add");
	$actionbutton=_("Add");
	$thisid="";
	
	if ($action == "edit") {
		$query = "SELECT * FROM {$dbprefix}assessments WHERE id=".$_POST['id'];
		$results = db_execute_assoc($query);
		while($row=$results->FetchRow()) {
			$editdata=$row;
		}
		$scopeselect = "<select name='scope'><option ";
		if ($editdata['scope'] == "T") {$scopeselect .= "selected ";}
		$scopeselect .= "value='T'>"._("Total")."</option><option value='G'";
		if ($editdata['scope'] == "G") {$scopeselect .= " selected";}
		$scopeselect .= "'>"._("Group")."</option></select>";
		$groupselect=str_replace("'".$editdata['gid']."'", "'".$editdata['gid']."' selected", $groupselect);
		$inputs=array($scopeselect,
		$groupselect,
		"<input type='text' name='minimum' value='".$editdata['minimum']."'>",
		"<input type='text' name='maximum' value='".$editdata['maximum']."'>",
		"<input type='text' name='name' value='".htmlentities(stripslashes($editdata['name']), ENT_QUOTES)."'>",
		"<textarea name='message'>".htmlentities(stripslashes($editdata['message']), ENT_QUOTES)."</textarea>",
		"<input type='text' name='link' value='".$editdata['link']."'>");
		$actiontitle=_("Edit");
		$actionbutton=_("Update");
		$thisid=$editdata['id'];
	}
	//echo "<pre>"; print_r($edits); echo "</pre>";
	//PRESENT THE PAGE
	
	echo "<br /><table align='center' class='outlinetable' cellspacing='0' width='90%'>
		<tr><th>"._("If you create any assessments in this page, for the currently selected survey, the assessment will be performed at the end of the survey after submission")."</th></tr>
		<tr><td>";
	echo "<table cellspacing='1' align='center' width='90%'>
		<tr><th>ID</th><th>SID</th>\n";
	foreach ($headings as $head) {
		echo "<th>$head</th>\n";
	}
	echo "<th>"._("Actions")."</th>";
	echo "</tr>\n";
	foreach($assessments as $assess) {
		echo "<tr>\n";
		foreach($assess as $as) {
			echo "<td>".stripslashes($as)."</td>\n";
		}
		echo "<td>
			   <table width='100%'>
				<tr><td align='center'><form method='post' action='assessments.php?sid=$surveyid'>
				 <input type='submit' value='"._("Edit")."' />
				 <input type='hidden' name='action' value='edit' />
				 <input type='hidden' name='id' value='".$assess['id']."' />
				 </form></td>
				 <td align='center'><form method='post' action='assessments.php?sid=$surveyid'>
				 <input type='submit' value='"._("Delete")."' onClick='return confirm(\""._("Are you sure you want to delete this entry.")."\")'>
				 <input type='hidden' name='action' value='delete'>
				 <input type='hidden' name='id' value='".$assess['id']."'>
				 </form>
				 </td>
				</tr>
			   </table>
			  </td>\n";
		echo "</tr>\n";
	}
	echo "</table>";
	echo "<br /><form method='post' action='assessments.php?sid=$surveyid'><table align='center' cellspacing='1'>\n";
	echo "<tr><th colspan='2'>$actiontitle</th></tr>\n";
	$i=0;
	
	foreach ($headings as $head) {
		echo "<tr><th>$head</th><td>".$inputs[$i]."</td></tr>\n";
		$i++;
	}
	echo "<tr><th colspan='2'><input type='submit' value='$actionbutton'></th></tr><tr><td>\n";
	echo "<input type='hidden' name='sid' value='$surveyid'>\n"
	."<input type='hidden' name='action' value='$actionbutton'>\n"
	."<input type='hidden' name='id' value='$thisid'>\n"
	."</td></tr>\n"
	."</table></form></table><br />\n";
		
	echo getAdminFooter("", "");
	}
else
	{
	$action = "assessment";
	include("access_denied.php");
	include("admin.php");
	}
	
function getAssessments($surveyid) {
	global $dbprefix, $connect;
	$query = "SELECT id, sid, scope, gid, minimum, maximum, name, message, link
			  FROM {$dbprefix}assessments
			  WHERE sid=$surveyid
			  ORDER BY scope, gid";
	$result=db_execute_assoc($query) or die("Error getting assessments<br />$query<br />".$connect->ErrorMsg());
	$output=array();
	while($row=$result->FetchRow()) {
		$output[]=$row;
	}
	return $output;
}

function getGroups($surveyid) {
	global $dbprefix, $connect;
	$query = "SELECT gid, group_name
			  FROM {$dbprefix}groups
			  WHERE sid=$surveyid
			  ORDER BY group_order";
	$result = db_execute_assoc($query) or die("Error getting groups<br />$query<br />".$connect->ErrorMsg());
	$output=array();
	while($row=$result->FetchRow()) {
		$output[]=$row;
	}
	return $output;
}
?>
