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

include_once("login_check.php");  //Login Check dies also if the script is started directly

if (!isset($limit)) {$limit=returnglobal('limit');}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($id)) {$id=returnglobal('id');}
if (!isset($order)) {$order=returnglobal('order');}
if (!isset($browselang)) {$browselang=returnglobal('browselang');}

//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly");}

//Check if results table exists
if (tableExists('survey_'.$surveyid)==false) die("Your results table is missing!");

$surveyinfo=getSurveyInfo($surveyid);
$sumquery5 = "SELECT b.* FROM {$dbprefix}surveys AS a INNER JOIN {$dbprefix}surveys_rights AS b ON a.sid = b.sid WHERE a.sid=$surveyid AND b.uid = ".$_SESSION['loginID']; //Getting rights for this survey and user
$sumresult5 = db_execute_assoc($sumquery5);
$sumrows5 = $sumresult5->FetchRow();

require_once(dirname(__FILE__).'/sessioncontrol.php');

// Set language for questions and labels to base language of this survey

if (isset($browselang) && $browselang!='')
{
   $_SESSION['browselang']=$browselang; 
   $language=$_SESSION['browselang'];
}
elseif (isset($_SESSION['browselang']))
{
   $language=$_SESSION['browselang'];
   $languagelist = GetAdditionalLanguagesFromSurveyID($surveyid);
   $languagelist[]=GetBaseLanguageFromSurveyID($surveyid);
   if (!in_array($language,$languagelist))
   {
        $language = GetBaseLanguageFromSurveyID($surveyid); 
   }
}
else
{    
    $language = GetBaseLanguageFromSurveyID($surveyid);
}

$surveyoptions = browsemenubar($clang->gT("Browse Responses"));
$browseoutput = "";

if (!$database_exists) //DATABASE DOESN'T EXIST OR CAN'T CONNECT
{
	$browseoutput .= "\t<div class='messagebox'><div class='header'>"
	. $clang->gT("Browse Responses")."</div><div class='warningheader'>"       
	.$clang->gT("Error")."\t</div>\n"
	. $clang->gT("The defined LimeSurvey database does not exist")."<br />\n"
	. $clang->gT("Either your selected database has not yet been created or there is a problem accessing it.")."<br /><br />\n"
	."<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br />\n"
	."</div>";
	return;
}
if (!$surveyid && !$subaction) //NO SID OR ACTION PROVIDED
{
	$browseoutput .= "\t<tr ><td colspan='2' height='4'><strong>"
	. $clang->gT("Browse Responses")."</strong></td></tr>\n"
	."\t<tr><td align='center'>\n"
	."<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n"
	. $clang->gT("You have not selected a survey to browse.")."<br /><br />\n"
	."<input type='submit' value='"
	. $clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br />\n"
	."</td></tr></table>\n";
	return;
}

//CHECK IF SURVEY IS ACTIVATED AND EXISTS
$actquery = "SELECT * FROM ".db_table_name('surveys')." as a inner join ".db_table_name('surveys_languagesettings')." as b on (b.surveyls_survey_id=a.sid and b.surveyls_language=a.language) WHERE a.sid=$surveyid";

$actresult = db_execute_assoc($actquery);
$actcount = $actresult->RecordCount();
if ($actcount > 0)
{
	while ($actrow = $actresult->FetchRow())
	{
		$surveytable = db_table_name("survey_".$actrow['sid']);
		/*
		 * DO NEVER EVER PUT VARIABLES AND FUNCTIONS WHICH GIVE BACK DIFFERENT QUOTES 
		 * IN DOUBLE QUOTED(' and " and \" is used) JAVASCRIPT/HTML CODE!!! (except for: you know what you are doing)
		 * 
		 * Used for deleting a record, fix quote bugs..
		 */
		$surveytableNq = db_table_name_nq("survey_".$surveyid);
		
		$surveyname = "{$actrow['surveyls_title']}";
		if ($actrow['active'] == "N") //SURVEY IS NOT ACTIVE YET
		{
			$browseoutput .= "\t<tr><td colspan='2' height='4'><strong>"
			. $clang->gT("Browse Responses").":</strong> $surveyname</td></tr>\n"
			."\t<tr><td align='center'>\n"
			."<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n"
			. $clang->gT("This survey has not been activated. There are no results to browse.")."<br /><br />\n"
			."<input type='submit' value='"
			. $clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" /><br />\n"
			."</td></tr></table>\n"
			."</body>\n</html>";
			return;
		}
	}
}
else //SURVEY MATCHING $surveyid DOESN'T EXIST
{
	$browseoutput .= "\t<tr><td colspan='2' height='4'><strong>"
	. $clang->gT("Browse Responses")."</strong></td></tr>\n"
	."\t<tr><td align='center'>\n"
	."<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n"
	. $clang->gT("There is no matching survey.")." ($surveyid)<br /><br />\n"
	."<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br />\n"
	."</td></tr></table>\n"
	."</body>\n</html>";
	return;
}

//OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.
$qulanguage = GetBaseLanguageFromSurveyID($surveyid);
if ($subaction == "id") // Looking at a SINGLE entry
{
    $dateformatdetails=getDateFormatData($_SESSION['dateformat']);
	//SHOW HEADER
	if (!isset($_POST['sql']) || !$_POST['sql']) {$browseoutput .= $surveyoptions;} // Don't show options if coming from tokens script
	//FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
	$fnquery = "SELECT * FROM ".db_table_name("questions").", ".db_table_name("groups").", ".db_table_name("surveys")."
	WHERE ".db_table_name("questions").".gid=".db_table_name("groups").".gid AND ".db_table_name("groups").".sid=".db_table_name("surveys").".sid
	AND ".db_table_name("questions").".sid='$surveyid' AND
	".db_table_name("questions").".language='{$language}' AND ".db_table_name("groups").".language='{$language}' ORDER BY ".db_table_name("groups").".group_order, ".db_table_name("questions").".title";
	$fnresult = db_execute_assoc($fnquery);
	$fncount = 0;

	$fnrows = array(); //Create an empty array in case fetch_array does not return any rows
	while ($fnrow = $fnresult->FetchRow()) {++$fncount; $fnrows[] = $fnrow; $private = $fnrow['private']; $datestamp=$fnrow['datestamp']; $ipaddr=$fnrow['ipaddr']; $refurl=$fnrow['refurl'];} // Get table output into array

	// Perform a case insensitive natural sort on group name then question title of a multidimensional array
	usort($fnrows, 'CompareGroupThenTitle');


	$fnames[] = array("id", "id");
	if ($private == "N") //add token to top ofl ist is survey is not private
	{
		$fnames[] = array("token", $clang->gT("Token ID"));
	}

	$fnames[] = array("completed", $clang->gT("Completed"), "0");

	if ($datestamp == "Y") //add datetime to list if survey is datestamped
	{
		// submitdate for not-datestamped surveys is always 1980/01/01
		// so only display it when datestamped
		$fnames[] = array("startdate", $clang->gT("Date Started"));
		$fnames[] = array("datestamp", $clang->gT("Date Last Action"));
		$fnames[] = array("submitdate", $clang->gT("Date Submitted"));
	}
	if ($ipaddr == "Y") //add ipaddr to list if survey should save submitters IP address
	{
		$fnames[] = array("ipaddr", $clang->gT("IP Address"));
	}
	if ($refurl == "Y") //add refer_URL  to list if survey should save referring URL
	{
		$fnames[] = array("refurl", $clang->gT("Referring URL"));
	}
	
    $fieldmap=createFieldMap($surveyid,'full');
    unset($fieldmap[0]);
    unset($fieldmap[1]);
    foreach ($fieldmap as $field)
    {
       $question=$field['question'];
       if (isset($field['subquestion']) && $field['subquestion']!='')
       {
          $question .=' ('.$field['subquestion'];
       }
       if (isset($field['answer']) && $field['answer']!='')
       {
          $question .=' : '.$field['answer'];
       }
       $question .=')'; 
       if (isset($field['scale_id']))
       {
          $question .='['.$field['scale'].']';
       }
       $fnames[]=array($field['fieldname'],$question); 
    }

	$nfncount = count($fnames)-1;
	//SHOW INDIVIDUAL RECORD
	$idquery = "SELECT *, CASE WHEN submitdate IS NULL THEN 'N' ELSE 'Y' END as completed FROM $surveytable WHERE ";
	if (incompleteAnsFilterstate() == "inc") {$idquery .= "(submitdate = ".$connect->DBDate('1980-01-01'). " OR submitdate IS NULL) AND ";}
	elseif (incompleteAnsFilterstate() == "filter") {$idquery .= "submitdate >= ".$connect->DBDate('1980-01-01'). " AND ";}
	if ($id<1) {$id=1;}
	if (isset($_POST['sql']) && $_POST['sql'])
	{
		if (get_magic_quotes_gpc()) {$idquery .= stripslashes($_POST['sql']);}
		else {$idquery .= "{$_POST['sql']}";}
	}
	else {$idquery .= "id=$id";}
	$idresult = db_execute_assoc($idquery) or safe_die ("Couldn't get entry<br />\n$idquery<br />\n".$connect->ErrorMsg());
	while ($idrow = $idresult->FetchRow()) {$id=$idrow['id']; $rlangauge=$idrow['startlanguage'];}
	$next=$id+1;
	$last=$id-1;
	$browseoutput .= "<div class='menubar'>\n"
	."<div class='menubar-title'>"
	."<strong>".$clang->gT("View Response").":</strong> $id\n"
	."\t</div><div class='menubar-main'>\n"
	."<img src='$imagefiles/blank.gif' width='31' height='20' border='0' hspace='0' align='left' alt='' />\n"
	."<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt='' />\n";
	if (isset($rlangauge))
    {
            $browseoutput .="<a href='$scriptname?action=dataentry&amp;subaction=edit&amp;id=$id&amp;sid=$surveyid&amp;language=$rlangauge' " 
                           ."title='".$clang->gTview("Edit this entry")."'>"
			               ."<img align='left' src='$imagefiles/edit.png' alt='".$clang->gT("Edit this entry")."' /></a>\n";
	}
	if (($sumrows5['delete_survey'] || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1) && isset($rlangauge))
	{
		
		$browseoutput .= "<a href='#' title='".$clang->gTview("Delete this entry")."' onclick=\"if (confirm('".$clang->gT("Are you sure you want to delete this entry?","js")."')) {".get2post($scriptname.'?action=dataentry&amp;subaction=delete&amp;id='.$id.'&amp;sid='.$surveyid)."}\" >" 
                        ."<img align='left' hspace='0' border='0' src='$imagefiles/delete.png' alt='".$clang->gT("Delete this entry")."' /></a>\n";
	}
    else
    {
        $browseoutput .=  "<img align='left' hspace='0' border='0' src='$imagefiles/delete_disabled.png' alt='".$clang->gT("You don't have permission to delete this entry.")."'/>";
    }
    //Export this response
	$browseoutput .= "<a href='$scriptname?action=exportresults&amp;sid=$surveyid&amp;id=$id'" .
		"title='".$clang->gTview("Export this Response")."' >" .
		"<img name='ExportAnswer' src='$imagefiles/export.png' alt='". $clang->gT("Export this Response")."' align='left' /></a>\n"
	    ."<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt='' />\n"
	    ."<img src='$imagefiles/blank.gif' width='20' height='20' border='0' hspace='0' align='left' alt='' />\n"
	    ."<a href='$scriptname?action=browse&amp;subaction=id&amp;id=$last&amp;sid=$surveyid' " 
        ."title='".$clang->gTview("Show previous...")."' >"
		."<img name='DataBack' align='left' src='$imagefiles/databack.png' alt='".$clang->gT("Show previous...")."' /></a>\n"
	    ."<img src='$imagefiles/blank.gif' width='13' height='20' border='0' hspace='0' align='left' alt='' />\n"
	    ."<a href='$scriptname?action=browse&amp;subaction=id&amp;id=$next&amp;sid=$surveyid' title='".$clang->gTview("Show next...")."'>"
		."<img name='DataForward' align='left' src='$imagefiles/dataforward.png' alt='".$clang->gT("Show next...")."' /></a>\n"
	."</div>\n"
	."\t</div>\n";
    
    $browseoutput .= "<table class='browsetable' width='99%'>\n";
	$idresult = db_execute_assoc($idquery) or safe_die ("Couldn't get entry<br />$idquery<br />".$connect->ErrorMsg());
	while ($idrow = $idresult->FetchRow())
	{
		$i=0;
		for ($i; $i<$nfncount+1; $i++)
		{
			$browseoutput .= "\t<tr>\n"
			."<th align='right' width='50%'>"
			.strip_tags(strip_javascript($fnames[$i][1]))."</th>\n"
			."<td align='left' >"
			.htmlspecialchars(strip_tags(strip_javascript(getextendedanswer($fnames[$i][0], $idrow[$fnames[$i][0]], '', $dateformatdetails['phpdate']))), ENT_QUOTES)
			."</td>\n"
			."\t</tr>\n";
		}
	}
	$browseoutput .= "</table>\n";

}

elseif ($subaction == "all")
{
    /**
    * fnames is used as informational array 
    * it containts
    *             $fnames[] = array(<dbfieldname>, <some strange title>, <questiontext>, <group_id>, <questiontype>);   
    */
	if (!isset($_POST['sql']))
	{$browseoutput .= $surveyoptions;} //don't show options when called from another script with a filter on
	else
	{
        $browseoutput .= "\t<tr><td colspan='2' height='4'><strong>".$clang->gT("Browse Responses").":</strong> $surveyname</td></tr>\n"
		."\n<tr><td><table width='100%' align='center' border='0' bgcolor='#EFEFEF'>\n"
		."\t<tr>\n"
		."<td align='center'>\n"
		."".$clang->gT("Showing Filtered Results")."<br />\n"
		."&nbsp;[<a href=\"javascript:window.close()\">".$clang->gT("Close")."</a>]"
		."</font></td>\n"
		."\t</tr>\n"
		."</table></td></tr>\n";

	}
    
    $fields=createFieldMap($surveyid,'full');
    foreach ($fields as $fielddetails)
    {
        if ($fielddetails['fieldname']=='startdate'){
            $fielddetails['question']=$clang->gT("Date started");
        }
        elseif ($fielddetails['fieldname']=='datestamp'){
            $fielddetails['question']=$clang->gT("Date last action");
        }
        elseif ($fielddetails['fieldname']=='token'){
            $fielddetails['question']=$clang->gT("Token ID");
        }        
        elseif ($fielddetails['fieldname']=='ipaddr'){
            $fielddetails['question']=$clang->gT("IP address");
        }        
     
        $fnames[]=array($fielddetails['fieldname'],
                        $fielddetails['fieldname'],
                        $fielddetails['question'],
                        $fielddetails['gid'],
                        $fielddetails['type']
                       );
    }

	$fnames[] = array("completed", "Completed", $clang->gT("Completed"), "0", 'D');

	if ($surveyinfo['datestamp'] == "Y") //Add datestamp
	{
		// submitdate for not-datestamped surveys is always 1980/01/01
		// so only display it when datestamped
		$fnames[] = array("submitdate", "submitdate", $clang->gT("Date Submitted"), "0", 'D');
	}

    if ($surveyinfo['refurl'] == "Y") //refurl
	{
		$fnames[] = array("refurl", "refurl", $clang->gT("Referring URL"), "0",'');
	}
	$fncount = count($fnames);

	//NOW LETS CREATE A TABLE WITH THOSE HEADINGS
	if ($fncount < 10) {$cellwidth = "10%";} else {$cellwidth = "100";}
	$tableheader = "<!-- DATA TABLE -->";
	if ($fncount < 10) {$tableheader .= "<table class='browsetable' width='100%' cellpadding='0' cellspacing='1'>\n";}
	else {$tableheader .= "<table class='browsetable' border='0' cellpadding='1' cellspacing='1' style='border: 1px solid #555555'>\n";}
	$tableheader .= "\t<thead><tr valign='top'>\n"
	. "<th  class='evenrow' width='$cellwidth'><strong>id</strong></th>\n";
	foreach ($fnames as $fn)
	{
		if (!isset($currentgroup))  {$currentgroup = $fn[3]; $gbc = "oddrow";}
		if ($currentgroup != $fn[3])
		{
			$currentgroup = $fn[3];
			if ($gbc == "oddrow") {$gbc = "evenrow";}
			else {$gbc = "oddrow";}
		}
		$tableheader .= "<th class='$gbc' width='$cellwidth'><strong>"
		. strip_javascript("$fn[2]")
		. "</strong></th>\n";
	}
	$tableheader .= "\t</tr></thead>\n\n";

	$start=returnglobal('start');
	$limit=returnglobal('limit');
	if (!isset($limit) || $limit== '') {$limit = 50;}
	if (!isset($start) || $start =='') {$start = 0;}

	//LETS COUNT THE DATA
	$dtquery = "SELECT count(*) FROM $surveytable";
	if (incompleteAnsFilterstate() == "inc") {$dtquery .= "WHERE submitdate IS NULL ";}
	elseif (incompleteAnsFilterstate() == "filter") {$dtquery .= " WHERE submitdate is not null ";}
	$dtresult=db_execute_num($dtquery);
	while ($dtrow=$dtresult->FetchRow()) {$dtcount=$dtrow[0];}

	if ($limit > $dtcount) {$limit=$dtcount;}

	//NOW LETS SHOW THE DATA
	if (isset($_POST['sql']))
	{
		if ($_POST['sql'] == "NULL")
		{
			$dtquery = "SELECT *, CASE WHEN submitdate IS NULL THEN 'N' ELSE 'Y' END as completed FROM $surveytable ";
	        if (incompleteAnsFilterstate() == "inc") {$dtquery .= "WHERE submitdate is null";}
	            elseif (incompleteAnsFilterstate() == "filter") {$dtquery .= " WHERE submitdate is not null ";}
			$dtquery .= " ORDER BY id";
		}
		else
		{
            $dtquery = "SELECT *, CASE WHEN submitdate IS NULL THEN 'N' ELSE 'Y' END as completed FROM $surveytable WHERE ";
	        if (incompleteAnsFilterstate() == "inc") {
			    $dtquery .= "submitdate is null ";
			    if (stripcslashes($_POST['sql']) !== "") { $dtquery .= " AND "; }
			}
	        elseif (incompleteAnsFilterstate() == "filter") {
                $dtquery .= " submitdate is not null ";
                if (stripcslashes($_POST['sql']) !== "") { $dtquery .= " AND "; }
            }
            if (stripcslashes($_POST['sql']) !== "") { $dtquery .= stripcslashes($_POST['sql'])." "; }
            $dtquery .= " ORDER BY id";
		}
	}
	else
	{
		$dtquery = "SELECT *, CASE WHEN submitdate IS NULL THEN 'N' ELSE 'Y' END as completed FROM $surveytable ";
		if (incompleteAnsFilterstate() == "inc") {$dtquery .= " WHERE submitdate is null ";}
		elseif (incompleteAnsFilterstate() == "filter") {$dtquery .= " WHERE submitdate is not null ";}
		$dtquery .= " ORDER BY id";
	}
	if ($order == "desc") {$dtquery .= " DESC";}

	if (isset($limit))
	{
		if (!isset($start)) {$start = 0;}
		$dtresult = db_select_limit_assoc($dtquery, $limit, $start) or safe_die("Couldn't get surveys<br />$dtquery<br />".$connect->ErrorMsg());
	}
	else
	{
		$dtresult = db_execute_assoc($dtquery) or safe_die("Couldn't get surveys<br />$dtquery<br />".$connect->ErrorMsg());
	}
	$dtcount2 = $dtresult->RecordCount();
	$cells = $fncount+1;


	//CONTROL MENUBAR
	$last=$start-$limit;
	$next=$start+$limit;
	$end=$dtcount-$limit;
	if ($end < 0) {$end=0;}
	if ($last <0) {$last=0;}
	if ($next >= $dtcount) {$next=$dtcount-$limit;}
	if ($end < 0) {$end=0;}

	$browseoutput .= "<div class='menubar'>\n"
        . "\t<div class='menubar-title'>\n"
        . "<strong>".$clang->gT("Data View Control")."</strong></div>\n"
        . "\t<div class='menubar-main'>\n";            
	if (!isset($_POST['sql']))
	{
		$browseoutput .= "<a href='$scriptname?action=browse&amp;subaction=all&amp;sid=$surveyid&amp;start=0&amp;limit=$limit' " 
                        ."title='".$clang->gTview("Show start...")."' >" 
						."<img name='DataBegin' align='left' src='$imagefiles/databegin.png' alt='".$clang->gT("Show start...")."' /></a>\n"
		                ."<a href='$scriptname?action=browse&amp;subaction=all&amp;sid=$surveyid&amp;start=$last&amp;limit=$limit' "
                        ."title='".$clang->gTview("Show previous..")."' >" 
				        ."<img name='DataBack' align='left'  src='$imagefiles/databack.png' alt='".$clang->gT("Show previous..")."' /></a>\n"
		."<img src='$imagefiles/blank.gif' width='13' height='20' border='0' hspace='0' align='left' alt='' />\n"
        
		."<a href='$scriptname?action=browse&amp;subaction=all&amp;sid=$surveyid&amp;start=$next&amp;limit=$limit' " .
				"title='".$clang->gT("Show next...")."' >".
				"<img name='DataForward' align='left' src='$imagefiles/dataforward.png' alt='".$clang->gT("Show next..")."' /></a>\n"
		."<a href='$scriptname?action=browse&amp;subaction=all&amp;sid=$surveyid&amp;start=$end&amp;limit=$limit' " .
				"title='".$clang->gT("Show last...")."' >" .
				"<img name='DataEnd' align='left' src='$imagefiles/dataend.png' alt='".$clang->gT("Show last..")."' /></a>\n"
		."<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt='' />\n";
	}
    if(incompleteAnsFilterstate() == "inc")
	{
	    $selecthide="";
	    $selectshow="";
	    $selectinc="selected='selected'";
	}
	elseif (incompleteAnsFilterstate() == "filter")
	{
		$selecthide="selected='selected'";
		$selectshow="";
		$selectinc="";
	}
	else
	{
		$selecthide="";
		$selectshow="selected='selected'";
		$selectinc="";
	}

	$browseoutput .="<form action='$scriptname?action=browse' id='browseresults' method='post'><font size='1' face='verdana'>\n"
	."<img src='$imagefiles/blank.gif' width='31' height='20' border='0' hspace='0' align='right' alt='' />\n"
	."".$clang->gT("Records Displayed:")."<input type='text' size='4' value='$dtcount2' name='limit' id='limit' />\n"
	."&nbsp;&nbsp; ".$clang->gT("Starting From:")."<input type='text' size='4' value='$start' name='start' id='start' />\n"
	."&nbsp;&nbsp; ".$clang->gT("Display:")."<select name='filterinc' onchange='javascript:document.getElementById(\"limit\").value=\"\";submit();'>\n"
	."\t<option value='filter' $selecthide>".$clang->gT("Completed responses only")."</option>\n"
	."\t<option value='show' $selectshow>".$clang->gT("All responses")."</option>\n"
	."\t<option value='incomplete' $selectinc>".$clang->gT("Incomplete responses only")."</option>\n"
	."</select>\n"
	."&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' value='".$clang->gT("Show")."' />\n"
	."</font>\n"
	."<input type='hidden' name='sid' value='$surveyid' />\n"
	."<input type='hidden' name='action' value='browse' />\n"
	."<input type='hidden' name='subaction' value='all' />\n";
	if (isset($_POST['sql']))
	{
		$browseoutput .= "<input type='hidden' name='sql' value='".html_escape($_POST['sql'])."' />\n";
	}
	$browseoutput .= 	 "</form></div>\n"
	."\t</div>\n";

	$browseoutput .= $tableheader;
    $dateformatdetails=getDateFormatData($_SESSION['dateformat']);

	while ($dtrow = $dtresult->FetchRow())
	{
		if (!isset($bgcc)) {$bgcc="evenrow";}
		else
		{
			if ($bgcc == "evenrow") {$bgcc = "oddrow";}
			else {$bgcc = "evenrow";}
		}
		$browseoutput .= "\t<tr class='$bgcc' valign='top'>\n"
		."<td align='center'>\n"
		."<a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=id&amp;id={$dtrow['id']}' title='".$clang->gT("View This Record")."'>"
		."{$dtrow['id']}</a></td>\n";

		$i = 0;
		if ($surveyinfo['private'] == "N" && $dtrow['token'])
		{
			$SQL = "Select * FROM ".db_table_name('tokens_'.$surveyid)." WHERE token=?";
			if ( db_tables_exist(db_table_name_nq('tokens_'.$surveyid)) &&
				$SQLResult = db_execute_assoc($SQL, $dtrow['token']))
			{
				$TokenRow = $SQLResult->FetchRow();
			}
			$browseoutput .= "<td align='center'>\n";
			if (isset($TokenRow) && $TokenRow)
			{
				$browseoutput .= "<a href='$scriptname?action=tokens&amp;sid=$surveyid&amp;subaction=edit&amp;tid={$TokenRow['tid']}' title='".$clang->gT("Edit this token")."'>";
			}
			$browseoutput .= "{$dtrow['token']}";
			if (isset($TokenRow) && $TokenRow)
			{
				$browseoutput .= "</a>\n";
			}
			$i++;
		}

		for ($i; $i<$fncount; $i++)
		{
            $browsedatafield=htmlspecialchars($dtrow[$fnames[$i][0]]);
            if ($fnames[$i][4]=='D' && $dtrow[$fnames[$i][0]]!='N' && $dtrow[$fnames[$i][0]]!='Y' && $dtrow[$fnames[$i][0]]!='')
            {
                $datetimeobj = new Date_Time_Converter($dtrow[$fnames[$i][0]] , "Y-m-d H:i:s");
                $browsedatafield=$datetimeobj->convert($dateformatdetails['phpdate'].' H:i');                      
            }
            if (trim($browsedatafield=='')) $browsedatafield='&nbsp;';
			$browseoutput .= "<td align='center'>$browsedatafield</td>\n";
		}
		$browseoutput .= "\t</tr>\n";
	}
	$browseoutput .= "</table>\n<br />\n";
}
else
{
	$browseoutput .= $surveyoptions;
	$num_total_answers=0;
	$num_completed_answers=0;
	$gnquery = "SELECT count(id) FROM $surveytable";
	$gnquery2 = "SELECT count(id) FROM $surveytable WHERE submitdate is not null";
	$gnresult = db_execute_num($gnquery);
	$gnresult2 = db_execute_num($gnquery2);

	while ($gnrow=$gnresult->FetchRow()) {$num_total_answers=$gnrow[0];}
	while ($gnrow2=$gnresult2->FetchRow()) {$num_completed_answers=$gnrow2[0];}
	$browseoutput .= "<div class='header'>".$clang->gT("Response summary")."</div>"
    ."<p><table class='statisticssummary'>\n"
    ."<tfoot><tr><th>".$clang->gT("Total responses:")."</th><td>".$num_total_answers."</td></tr></tfoot>"
	."\t<tbody>"
    ."<tr><th>".$clang->gT("Full responses:")."</th><td>".$num_completed_answers."</td></tr>"
    ."<tr><th>".$clang->gT("Incomplete responses:")."</th><td>".($num_total_answers-$num_completed_answers)."</td></tr></tbody>"
	."</table>";

}
