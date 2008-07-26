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


include_once("login_check.php");
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($action)) {$action=returnglobal('action');}


$actsurquery = "SELECT edit_survey_property FROM {$dbprefix}surveys_rights WHERE sid=$surveyid AND uid = ".$_SESSION['loginID']; //Getting rights for this survey
$actsurresult = $connect->Execute($actsurquery) or safe_die($connect->ErrorMsg());		
$actsurrows = $actsurresult->FetchRow();

if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['edit_survey_property']){

	if ($action == "assessmentadd") {
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
		$result=$connect->Execute($query) or safe_die("Error inserting<br />$query<br />".$connect->ErrorMsg());
	} elseif ($action == "assessmentupdate") {
		$query = "UPDATE {$dbprefix}assessments
				  SET scope='".db_quote($_POST['scope'])."',
				  gid=".sanitize_int($_POST['gid']).",
				  minimum='".sanitize_int($_POST['minimum'])."',
				  maximum='".sanitize_int($_POST['maximum'])."',
				  name='".db_quote($_POST['name'])."',
				  message='".db_quote($_POST['message'])."',
				  link='".db_quote($_POST['link'])."'
				  WHERE id=".sanitize_int($_POST['id']);
		$result = $connect->Execute($query) or safe_die("Error updating<br />$query<br />".$connect->ErrorMsg());
	} elseif ($action == "assessmentdelete") {
		$query = "DELETE FROM {$dbprefix}assessments
				  WHERE id=".sanitize_int($_POST['id']);
		$result=$connect->Execute($query);
	}
	
    $assessmentsoutput=  "<table width='100%' border='0' >\n"
        . "\t<tr>\n"
        . "\t\t<td>\n"
        . "\t\t\t<table class='menubar'>\n"
        . "\t\t\t<tr>\n"
        . "\t\t\t\t<td colspan='2' height='8'>\n"
        . "\t\t\t\t\t<strong>".$clang->gT("Assessments")."</strong></td></tr>\n";
	
	$assessmentsoutput.= "\t<tr >\n"
	. "\t\t<td>\n"
	. "\t\t\t<a href=\"#\" onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Return to Survey Administration", "js")."');return false\">" .
			"<img name='Administration' src='$imagefiles/home.png' title='' alt='' align='left'  /></a>\n"
	. "\t\t\t<img src='$imagefiles/blank.gif' alt='' width='11' border='0' hspace='0' align='left' />\n"
	. "\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n"
	. "\t\t</td>\n"
	. "\t</tr>\n";
	$assessmentsoutput.= "</table>";
	
	if ($surveyid == "") {
		$assessmentsoutput.= $clang->gT("No SID Provided");
		exit;
	}
	
	$assessments=getAssessments($surveyid);
	//$assessmentsoutput.= "<pre>";print_r($assessments);echo "</pre>";
	$groups=getGroups($surveyid);
	$groupselect="<select name='gid'>\n";
	foreach($groups as $group) {
		$groupselect.="<option value='".$group['gid']."'>".$group['group_name']."</option>\n";
	}
	$groupselect .="</select>\n";
	$headings=array($clang->gT("Scope"), $clang->gT("Group"), $clang->gT("Minimum"), $clang->gT("Maximum"), $clang->gT("Heading"), $clang->gT("Message"), $clang->gT("URL"));
	$inputs=array("<select name='scope'><option value='T'>".$clang->gT("Total")."</option><option value='G'>".$clang->gT("Group")."</option></select>",
	$groupselect,
	"<input type='text' name='minimum' />",
	"<input type='text' name='maximum' />",
	"<input type='text' name='name' size='80'/>",
	"<textarea name='message' rows='10' cols='80'></textarea >",
	"<input type='text' name='link' size='80' />");
	$actiontitle=$clang->gT("Add");
	$actionvalue="assessmentadd";
	$thisid="";
	
	if ($action == "assessmentedit") {
		$query = "SELECT * FROM {$dbprefix}assessments WHERE id=".sanitize_int($_POST['id']);
		$results = db_execute_assoc($query);
		while($row=$results->FetchRow()) {
			$editdata=$row;
		}
		$scopeselect = "<select name='scope'><option ";
		if ($editdata['scope'] == "T") {$scopeselect .= "selected='selected' ";}
		$scopeselect .= "value='T'>".$clang->gT("Total")."</option><option value='G'";
		if ($editdata['scope'] == "G") {$scopeselect .= " selected='selected'";}
		$scopeselect .= ">".$clang->gT("Group")."</option></select>";
		$groupselect=str_replace("'".$editdata['gid']."'", "'".$editdata['gid']."' selected", $groupselect);
		$inputs=array($scopeselect,
		$groupselect,
		"<input type='text' name='minimum' value='".$editdata['minimum']."' />",
		"<input type='text' name='maximum' value='".$editdata['maximum']."' />",
		"<input type='text' name='name' size='80' value='".htmlentities(stripslashes($editdata['name']), ENT_QUOTES,'UTF-8')."'/>",
		"<textarea name='message' rows='10' cols='80'>".htmlentities(stripslashes($editdata['message']), ENT_QUOTES,'UTF-8')."</textarea>",
		"<input type='text' name='link' size='80' value='".$editdata['link']."' />");
		$actiontitle=$clang->gT("Edit");	
		$actionvalue="assessmentupdate";
		$thisid=$editdata['id'];
	}
	//$assessmentsoutput.= "<pre>"; print_r($edits); $assessmentsoutput.= "</pre>";
	//PRESENT THE PAGE
	
	$assessmentsoutput.= "<br /><table align='center'  width='90%'>
		<tr><td colspan='12'>".$clang->gT("If you create any assessments in this page, for the currently selected survey, the assessment will be performed at the end of the survey after submission")."</th></tr>"
		."<tr><th>".$clang->gT("ID")."</th><th>".$clang->gT("SID")."</th>\n";
	foreach ($headings as $head) {
		$assessmentsoutput.= "<th>$head</th>\n";
	}
	$assessmentsoutput.= "<th>".$clang->gT("Actions")."</th>";
	$assessmentsoutput.= "</tr>\n";
    $flipflop=true;
	foreach($assessments as $assess) {
        $flipflop=!$flipflop;
		if ($flipflop==true){$assessmentsoutput.= "<tr class='oddrow'>\n";}
          else {$assessmentsoutput.= "<tr class='evenrow'>\n";} 
		$assessmentsoutput.= "<td>".$assess['id']."</td>\n";
		$assessmentsoutput.= "<td>".$assess['sid']."</td>\n";

		if ($assess['scope'] == "T") {	$assessmentsoutput.= "<td>".$clang->gT("Total")."</td>\n"; }
		else {$assessmentsoutput.= "<td>".$clang->gT("Group")."</td>\n"; }

		$assessmentsoutput.= "<td>".$groups[$assess['gid']]['group_name']." (".$assess['gid'].")</td>\n";
		
		$assessmentsoutput.= "<td>".$assess['minimum']."</td>\n";
		$assessmentsoutput.= "<td>".$assess['maximum']."</td>\n";
		$assessmentsoutput.= "<td>".stripslashes($assess['name'])."</td>\n";
		$assessmentsoutput.= "<td>".stripslashes($assess['message'])."</td>\n";
		$assessmentsoutput.= "<td>".stripslashes($assess['link'])."</td>\n";
		
		$assessmentsoutput.= "<td>
			   <table width='100%'>
				<tr><td align='center'><form method='post' action='$scriptname?sid=$surveyid'>
				 <input type='submit' value='".$clang->gT("Edit")."' />
				 <input type='hidden' name='action' value='assessmentedit' />
				 <input type='hidden' name='id' value='".$assess['id']."' />
				 </form></td>
				 <td align='center'><form method='post' action='$scriptname?sid=$surveyid'>
				 <input type='submit' value='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")' />
				 <input type='hidden' name='action' value='assessmentdelete' />
				 <input type='hidden' name='id' value='".$assess['id']."' />
				 </form>
				 </td>
				</tr>
			   </table>
			  </td>\n";
		$assessmentsoutput.= "</tr>\n";
	}
	$assessmentsoutput.= "</table>";
	$assessmentsoutput.= "<br /><form method='post' name='assessmentsform' action='$scriptname?sid=$surveyid'><table align='center' cellspacing='0' border='0' class='form2columns'>\n";
	$assessmentsoutput.= "<tr><th colspan='2'>$actiontitle</th></tr>\n";
	$i=0;
	
	foreach ($headings as $head) {
		$assessmentsoutput.= "<tr><td>$head</td><td>".$inputs[$i]."</td></tr>\n";
		$i++;
	}
	$assessmentsoutput.= "<tr><th colspan='2' align='center'><input type='submit' value='".$clang->gT("Save")."' />\n";
	if ($action == "assessmentedit") $assessmentsoutput.= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='".$clang->gT("Cancel")."' onclick=\"document.assessmentsform.action.value='assessments'\" />\n";
	$assessmentsoutput.= "<input type='hidden' name='sid' value='$surveyid' />\n"
	."<input type='hidden' name='action' value='$actionvalue' />\n"
	."<input type='hidden' name='id' value='$thisid' />\n"
	."</th></tr>\n"                                            
	."</table></form></td></tr></table>\n";
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
			  FROM ".db_table_name('assessments')."
			  WHERE sid='$surveyid'
			  ORDER BY scope, gid";
	$result=db_execute_assoc($query) or safe_die("Error getting assessments<br />$query<br />".$connect->ErrorMsg());
	$output=array();
	while($row=$result->FetchRow()) {
		$output[]=$row;
	}
	return $output;
}

function getGroups($surveyid) {
	global $dbprefix, $connect;
	$baselang = GetBaseLanguageFromSurveyID($surveyid);
	$query = "SELECT gid, group_name
			  FROM ".db_table_name('groups')."
			  WHERE sid='$surveyid' and language='$baselang'
			  ORDER BY group_order";
	$result = db_execute_assoc($query) or safe_die("Error getting groups<br />$query<br />".$connect->ErrorMsg());
	$output=array();
	while($row=$result->FetchRow()) {
		$output[$row['gid']]=$row;
	}
	return $output;
}
?>
