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

$surveyinfo=getSurveyInfo($surveyid);

$js_adminheader_includes[]= $homeurl.'/scripts/assessments.js';
$js_adminheader_includes[]= $rooturl.'/scripts/jquery/jquery-ui.js';
//                          . "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"styles/default/jquery-ui.css\" />\n";


$actsurquery = "SELECT edit_survey_property FROM {$dbprefix}surveys_rights WHERE sid=$surveyid AND uid = ".$_SESSION['loginID']; //Getting rights for this survey
$actsurresult = $connect->Execute($actsurquery) or safe_die($connect->ErrorMsg());		
$actsurrows = $actsurresult->FetchRow();


$assessmentlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
$baselang = GetBaseLanguageFromSurveyID($surveyid);
array_unshift($assessmentlangs,$baselang);      // makes an array with ALL the languages supported by the survey -> $assessmentlangs     


if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['edit_survey_property']){

	if ($action == "assessmentadd") {
		$inserttable=$dbprefix."assessments";
        $first=true;
        foreach ($assessmentlangs as $assessmentlang)
        {
            if (!isset($_POST['gid'])) $_POST['gid']=0;   
                        
            $datarray=array(
            'sid' => $surveyid,
            'scope' => $_POST['scope'],
            'gid' => $_POST['gid'],
            'minimum' => $_POST['minimum'],
            'maximum' => $_POST['maximum'],
            'name' => $_POST['name_'.$assessmentlang],
            'language' => $assessmentlang,
            'message' => $_POST['assessmentmessage_'.$assessmentlang]);
            
            if ($first==false)
            {
                $datarray['id']=$aid;
            }
            
		    $query = $connect->GetInsertSQL($inserttable, $datarray, get_magic_quotes_gpc());
		    $result=$connect->Execute($query) or safe_die("Error inserting<br />$query<br />".$connect->ErrorMsg());
            if ($first==true)
            {
              $first=false;
              $aid=$connect->Insert_ID(db_table_name_nq('assessments'),"id");           
            }
        }
	} elseif ($action == "assessmentupdate") {

        require_once("../classes/inputfilter/class.inputfilter_clean.php");
        $myFilter = new InputFilter('','',1,1,1); 
        
        foreach ($assessmentlangs as $assessmentlang)
        {
        
            if (!isset($_POST['gid'])) $_POST['gid']=0;
	        $query = "UPDATE {$dbprefix}assessments
			          SET scope='".db_quote($_POST['scope'])."',
			          gid=".sanitize_int($_POST['gid']).",
			          minimum='".sanitize_signedint($_POST['minimum'])."',
			          maximum='".sanitize_signedint($_POST['maximum'])."',
			          name='".db_quote($myFilter->process($_POST['name_'.$assessmentlang]))."',
			          message='".db_quote($myFilter->process($_POST['assessmentmessage_'.$assessmentlang]))."'
			          WHERE language='$assessmentlang' and id=".sanitize_int($_POST['id']);
	        $result = $connect->Execute($query) or safe_die("Error updating<br />$query<br />".$connect->ErrorMsg());
        }
	} elseif ($action == "assessmentdelete") {
		$query = "DELETE FROM {$dbprefix}assessments
				  WHERE id=".sanitize_int($_POST['id']);
		$result=$connect->Execute($query);
	}
	
    $assessmentsoutput=PrepareEditorScript();  
    $assessmentsoutput.="<script type=\"text/javascript\">
                        <!-- 
                            var strnogroup='".$clang->gT("There are no groups available.", "js")."';
                        --></script>";
    $assessmentsoutput.="<table width='100%' border='0' >\n"
        . "\t<tr>\n"
        . "<td>\n"
        . "<div class='menubar'>\n"
        . "\t<div class='menubar-title'>\n"
        . "<strong>".$clang->gT("Assessments")."</strong>\n";
	
	$assessmentsoutput.= "\t</div>\n"
    . "\t<div class='menubar-main'>\n"
    . "<div class='menubar-left'>\n"
	. "\t<a href=\"#\" onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" title='".$clang->gTview("Return to survey administration")."'>" .
			"<img name='Administration' src='$imagefiles/home.png' alt='".$clang->gT("Return to survey administration")."' /></a>\n"
	. "\t<img src='$imagefiles/blank.gif' alt='' width='11'  />\n"
	. "\t<img src='$imagefiles/seperator.gif' alt='' />\n";
    
    if ($surveyinfo['assessments']!='Y')
    {
        $assessmentsoutput.='<span style="font-size:11px;">'.sprintf($clang->gT("Notice: Assessment mode for this survey is not activated. You can activate it in the %s survey settings %s (tab 'Notification & data management')."),'<a href="admin.php?action=editsurvey&sid='.$surveyid.'">','</a>').'</span>';   
    }
	$assessmentsoutput.= "</div>\n"
	. "\t</div>\n"
    . "</div>\n";
    $assessmentsoutput .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix
	
	if ($surveyid == "") {
		$assessmentsoutput.= $clang->gT("No SID Provided");
		exit;
	}
	
	$assessments=getAssessments($surveyid);
	//$assessmentsoutput.= "<pre>";print_r($assessments);echo "</pre>";
	$groups=getGroups($surveyid);
	$groupselect="<select name='gid' id='newgroupselect'>\n";
	foreach($groups as $group) {
		$groupselect.="<option value='".$group['gid']."'>".$group['group_name']."</option>\n";
	}
	$groupselect .="</select>\n";
	$headings=array($clang->gT("Scope"), $clang->gT("Question group"), $clang->gT("Minimum"), $clang->gT("Maximum"));
	$inputs=array("<input type='radio' id='radiototal' name='scope' value='T' checked='checked'>".$clang->gT("Total")."</input><input type='radio' id='radiogroup' name='scope' value='G'>".$clang->gT("Group")."</input>",
	$groupselect,
	"<input type='text' name='minimum' class='numbersonly' />",
	"<input type='text' name='maximum' class='numbersonly' />");
	$actiontitle=$clang->gT("Add");
	$actionvalue="assessmentadd";
	$thisid="";
	
	if ($action == "assessmentedit") {
		$query = "SELECT * FROM {$dbprefix}assessments WHERE id=".sanitize_int($_POST['id'])." and language='$baselang'";
		$results = db_execute_assoc($query);
		while($row=$results->FetchRow()) {
			$editdata=$row;
		}
		$scopeselect = "<input type='radio' id='radiototal' name='scope' ";
		if ($editdata['scope'] == "T") {$scopeselect .= "checked='checked' ";}
		$scopeselect .= "value='T'>".$clang->gT("Total")."</input>";
        $scopeselect .= "<input type='radio' name='scope' id='radiogroup' value='G'";
		if ($editdata['scope'] == "G") {$scopeselect .= " checked='checked'";}
		$scopeselect .= ">".$clang->gT("Question group")."</input>";
		$groupselect=str_replace("'".$editdata['gid']."'", "'".$editdata['gid']."' selected", $groupselect);
		$inputs=array($scopeselect,
		$groupselect,
		"<input type='text' name='minimum' value='".$editdata['minimum']."' class='numbersonly' />",
		"<input type='text' name='maximum' value='".$editdata['maximum']."' class='numbersonly' />",
		"<input type='text' name='name' size='80' value='".htmlentities(stripslashes($editdata['name']), ENT_QUOTES,'UTF-8')."'/>",
		"<textarea name='message' id='assessmentmessage' rows='10' cols='80'>".htmlentities(stripslashes($editdata['message']), ENT_QUOTES,'UTF-8')."</textarea>");
		$actiontitle=$clang->gT("Edit");	
		$actionvalue="assessmentupdate";
		$thisid=$editdata['id'];
	}
	//$assessmentsoutput.= "<pre>"; print_r($edits); $assessmentsoutput.= "</pre>";
	//PRESENT THE PAGE

	
	$assessmentsoutput.= "<br /><table align='center'  width='90%'>
		<tr><th colspan='12'>".$clang->gT("Assessment rules")."</th></tr>"
		."<tr><th>".$clang->gT("ID")."</th><th>".$clang->gT("SID")."</th>\n";
	foreach ($headings as $head) {
		$assessmentsoutput.= "<th>$head</th>\n";
	}
	$assessmentsoutput.= "<th>".$clang->gT("Title")."</th><th>".$clang->gT("Message")."</th><th>".$clang->gT("Actions")."</th>";
	$assessmentsoutput.= "</tr>\n";
    $flipflop=true;
	foreach($assessments as $assess) {
        $flipflop=!$flipflop;
		if ($flipflop==true){$assessmentsoutput.= "<tr class='oddrow'>\n";}
          else {$assessmentsoutput.= "<tr class='evenrow'>\n";} 
		$assessmentsoutput.= "<td>".$assess['id']."</td>\n";
		$assessmentsoutput.= "<td>".$assess['sid']."</td>\n";

		if ($assess['scope'] == "T") 
        {	
            $assessmentsoutput.= "<td>".$clang->gT("Total")."</td>\n"; 
            $assessmentsoutput.= "<td>-</td>\n";
        }
		else 
        {
            $assessmentsoutput.= "<td>".$clang->gT("Question group")."</td>\n"; 
            $assessmentsoutput.= "<td>".$groups[$assess['gid']]['group_name']." (".$assess['gid'].")</td>\n";
        }

		
		$assessmentsoutput.= "<td>".$assess['minimum']."</td>\n";
		$assessmentsoutput.= "<td>".$assess['maximum']."</td>\n";
		$assessmentsoutput.= "<td>".stripslashes($assess['name'])."</td>\n";
		$assessmentsoutput.= "<td>".strip_tags(strip_javascript($assess['message']))."</td>\n";
		
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
    
    
    //now present edit/insert form
	$assessmentsoutput.= "<br /><form method='post' name='assessmentsform' action='$scriptname?sid=$surveyid'><table align='center' cellspacing='0' border='0' class='form2columns'>\n";
	$assessmentsoutput.= "<tr><th colspan='2'>$actiontitle</th></tr>\n";
	$i=0;
	
	foreach ($headings as $head) {
		$assessmentsoutput.= "<tr><td>$head</td><td>".$inputs[$i]."<br /></td></tr>\n";
		$i++;
	}
    
    
    // start tabs
    $assessmentsoutput.= "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
    $assessmentsoutput.='</table><div id="languagetabs">'
                        .'<ul>';
    foreach ($assessmentlangs as $assessmentlang)
    {
        $position=0;
        $assessmentsoutput .= '<li><a href="#tablang'.$assessmentlang.'"><span>'.getLanguageNameFromCode($assessmentlang, false);
        if ($assessmentlang==$baselang) {$assessmentsoutput .= ' ('.$clang->gT("Base Language").')';}
        $assessmentsoutput .='</span></a></li>';
    }
    $assessmentsoutput.= '</ul>';
    foreach ($assessmentlangs as $assessmentlang)
    {
        $heading=''; $message='';
        if ($action == "assessmentedit") 
        {       
            $query = "SELECT * FROM {$dbprefix}assessments WHERE id=".sanitize_int($_POST['id'])." and language='$assessmentlang'";
            $results = db_execute_assoc($query);
            while($row=$results->FetchRow()) {
                $editdata=$row;
            }
            $heading=$editdata['name'];
            $message=$editdata['message'];
        }
        $assessmentsoutput .= '<div id="tablang'.$assessmentlang.'">';
        $assessmentsoutput .= $clang->gT("Heading")."<br/>"
        ."<input type='text' name='name_$assessmentlang' size='80' value='$heading'/><br /><br />"
        .$clang->gT("Message")
        ."<textarea name='assessmentmessage_$assessmentlang' id='assessmentmessage_$assessmentlang' rows='10' cols='80'>$message</textarea >";
        
        $assessmentsoutput .='</div>';
    }    
    $assessmentsoutput .='</div>'; 
    
    
	$assessmentsoutput.= "<div style='width:200px;margin:5px auto;'><input type='submit' value='".$clang->gT("Save")."' />\n";
	if ($action == "assessmentedit") $assessmentsoutput.= "&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='".$clang->gT("Cancel")."' onclick=\"document.assessmentsform.action.value='assessments'\" />\n";
	$assessmentsoutput.= "<input type='hidden' name='sid' value='$surveyid' />\n"
	."<input type='hidden' name='action' value='$actionvalue' />\n"
	."<input type='hidden' name='id' value='$thisid' />\n"
	."<div>\n"                                            
	."</form>\n";
    foreach ($assessmentlangs as $assessmentlang)   
    {
        $assessmentsoutput.=getEditor("assessment-text","assessmentmessage_$assessmentlang", "[".$clang->gT("Message:", "js")."]",$surveyid,$gid,$qid,$action);
    }
    
	}
else
	{
	$action = "assessment";
	include("access_denied.php");
	include("admin.php");
	}
	
function getAssessments($surveyid) {
	global $dbprefix, $connect, $baselang;
	$query = "SELECT id, sid, scope, gid, minimum, maximum, name, message
			  FROM ".db_table_name('assessments')."
			  WHERE sid='$surveyid' and language='$baselang'
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
