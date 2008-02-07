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

if (isset($_REQUEST['jpgraphdir'])) {die('You cannot start this script directly');}

include_once("login_check.php");
require_once('classes/core/class.progressbar.php');
$statisticsoutput ='';
if ($usejpgraph == 1 && isset($jpgraphdir)) //JPGRAPH CODING SUBMITTED BY Pieterjan Heyse
{
  if (isset($jpgraphfontdir) && $jpgraphfontdir!="")
  {
  DEFINE("TTF_DIR",$jpgraphfontdir); // url of fonts files
  }
	require_once ("$jpgraphdir/jpgraph.php");
	require_once ("$jpgraphdir/jpgraph_pie.php");
	require_once ("$jpgraphdir/jpgraph_pie3d.php");
	require_once ("$jpgraphdir/jpgraph_bar.php");


	//$currentuser is created as prefix for jpgraph files
	if (isset($_SERVER['REDIRECT_REMOTE_USER']))
	{
		$currentuser=$_SERVER['REDIRECT_REMOTE_USER'];
	}
	elseif (session_id())
	{
		$currentuser=substr(session_id(), 0, 15);
	}
	else
	{
		$currentuser="standard";
	}
}

// This gets all the 'to be shown questions' from the POST and puts these into an array 
$summary=returnglobal('summary');
if (isset($summary) && !is_array($summary)) {
	$summary = explode("+", $summary);
}

if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

if (!$surveyid)
{
	//need to have a survey id
	$statisticsoutput .= "<center>You have not selected a survey!</center>";
	exit;
}

// Set language for questions and labels to base language of this survey
$language = GetBaseLanguageFromSurveyID($surveyid);

//Delete any stats files from the temp directory that aren't from today.
deleteNotPattern($tempdir, "STATS_*.png","STATS_".date("d")."*.png");


$statisticsoutput .= "\t<script type='text/javascript'>
      <!--
       function hide(element) {
        document.getElementById(element).style.display='none';
       }
       function show(element) {
        document.getElementById(element).style.display='';
       }
      //-->
      </script>\n";

$statisticsoutput .= "<table width='99%' class='menubar' cellpadding='1' cellspacing='0'>\n"
."\t<tr><td colspan='2' height='4'><font size='1'><strong>".$clang->gT("Quick Statistics")."</strong></font></td></tr>\n";
//Get the menubar
$statisticsoutput .= browsemenubar();
$statisticsoutput .= "</table>\n"
."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1'"
." cellspacing='0'>\n"
."<tr><td align='center' class='settingcaption' height='22'>"
."<input type='image' src='$imagefiles/plus.gif' align='right' onclick='show(\"filtersettings\")' /><input type='image' src='$imagefiles/minus.gif' align='right' onclick='hide(\"filtersettings\")' />"
."<font size='2'><strong>".$clang->gT("Filter Settings")."</strong></font>"
."</td></tr>\n"
."</table>\n"
."<form method='post' name='formbuilder' action='$scriptname?action=statistics'>\n"
."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";


//Select public language file
$query = "SELECT datestamp FROM {$dbprefix}surveys WHERE sid=$surveyid";
$result = db_execute_assoc($query) or die("Error selecting language: <br />".$query."<br />".mysql_error());
while ($row=$result->FetchRow()) {$datestamp=$row['datestamp'];}

// 1: Get list of questions from survey
$query = "SELECT ".db_table_name("questions").".*, group_name, group_order\n"
."FROM ".db_table_name("questions").", ".db_table_name("groups")."\n"
."WHERE ".db_table_name("groups").".gid=".db_table_name("questions").".gid\n"
."AND ".db_table_name("groups").".language='".$language."'\n"
."AND ".db_table_name("questions").".language='".$language."'\n"
."AND ".db_table_name("questions").".sid=$surveyid";
$result = db_execute_assoc($query) or die("Couldn't do it!<br />$query<br />".$connect->ErrorMsg());
$rows = $result->GetRows();
//SORT IN NATURAL ORDER!
usort($rows, 'CompareGroupThenTitle');
foreach ($rows as $row)
{
	$filters[]=array($row['qid'],
	$row['gid'],
	$row['type'],
	$row['title'],
	$row['group_name'],
	strip_tags($row['question']),
	$row['lid']);

}
//var_dump($filters);
// SHOW ID FIELD

$statisticsoutput .= "\t\t<tr><td align='center'>
       <table cellspacing='0' cellpadding='0' width='100%' id='filtersettings'><tr><td>
        <table align='center'><tr>\n";
$myfield = "id";
$myfield2=$myfield."G";
$myfield3=$myfield."L";
$myfield4=$myfield."=";
$statisticsoutput .= "<td align='center'><strong>".$clang->gT("ID")."</strong><br />";
$statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Number greater than").":<br />\n"
."\t\t\t\t\t<input type='text' name='$myfield2' value='";
if (isset($_POST[$myfield2])){$statisticsoutput .= $_POST[$myfield2];}
$statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789')\" /><br />\n"
."\t\t\t\t\t".$clang->gT("Number Less Than").":<br />\n"
."\t\t\t\t\t<input type='text' name='$myfield3' value='";
if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}
$statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789')\" /><br />\n";
$statisticsoutput .= "\t\t\t\t\t=<br />
            <input type='text' name='$myfield4' value='";
if (isset($_POST[$myfield4])) {$statisticsoutput .= $_POST[$myfield4];}
$statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789')\" /><br /></font></td>\n";
$allfields[]=$myfield2;
$allfields[]=$myfield3;
$allfields[]=$myfield4;

if (isset($datestamp) && $datestamp == "Y") {
	$myfield = "datestamp";
	$myfield2 = "datestampG";
	$myfield3 = "datestampL";
	$myfield2="$myfield";
	$myfield3="$myfield2=";
	$myfield4="$myfield2<"; $myfield5="$myfield2>";
	$statisticsoutput .= "<td width='40'></td>";
	$statisticsoutput .= "\t\t\t\t<td align='center' valign='top'><strong>".$clang->gT("Datestamp")."</strong>"
	."<br />\n"
	."\t\t\t\t\t<font size='1'>".$clang->gT("Date (YYYY-MM-DD) equals").":<br />\n"
	."\t\t\t\t\t<input name='$myfield3' type='text' value='";
	if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}
	$statisticsoutput .= "' /><br />\n"
	."\t\t\t\t\t&nbsp;&nbsp;".$clang->gT("OR between").":<br />\n"
	."\t\t\t\t\t<input name='$myfield4' value='";
	if (isset($_POST[$myfield4])) {$statisticsoutput .= $_POST[$myfield4];}
	$statisticsoutput .= "' type='text' /> ".$clang->gT("and")." <input  name='$myfield5' value='";
	if (isset($_POST[$myfield5])) {$statisticsoutput .= $_POST[$myfield5];}
	$statisticsoutput .= "' type='text' /></font></td>\n";
	$allfields[]=$myfield2;
	$allfields[]=$myfield3;
	$allfields[]=$myfield4;
	$allfields[]=$myfield5;
}
$statisticsoutput .= "</tr></table></td></tr>";

// 2: Get answers for each question
if (!isset($currentgroup)) {$currentgroup="";}
foreach ($filters as $flt)
{
	if ($flt[1] != $currentgroup)
	{   //If the groupname has changed, start a new row
		if ($currentgroup)
		{
			//if we've already drawn a table for a group, and we're changing - close off table
			$statisticsoutput .= "\n\t\t\t\t<!-- --></tr>\n\t\t\t</table></td></tr>\n";
		}
		$statisticsoutput .= "\t\t<tr><td align='center' class='settingcaption'>\n"
		."\t\t<font size='1' face='verdana'><strong>$flt[4]</strong> (".$clang->gT("Group")." $flt[1])</font></td></tr>\n\t\t"
		."<tr><td align='center'>\n"
		."\t\t\t<table align='center' class='statisticstable'><tr>\n";
		$counter=0;
	}
	if (isset($counter) && $counter == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>"; $counter=0;}
	$myfield = "{$surveyid}X{$flt[1]}X{$flt[0]}";
	$niceqtext = FlattenText($flt[5]);
	//headings
	if ($flt[2] != "A" && $flt[2] != "B" && $flt[2] != "C" && $flt[2] != "E" && 
	    $flt[2] != "F" && $flt[2] != "H" && $flt[2] != "T" && $flt[2] != "U" && 
		$flt[2] != "S" && $flt[2] != "D" && $flt[2] != "R" && $flt[2] != "Q" && 
		$flt[2] != "X" && $flt[2] != "W" && $flt[2] != "Z" && $flt[2] != "K") //Have to make an exception for these types!
	{
		$statisticsoutput .= "\t\t\t\t<td align='center'>"
		."<strong>$flt[3]&nbsp;"; //Heading (Question No)
		if ($flt[2] == "M" || $flt[2] == "P" || $flt[2] == "R" || $flt[2] == "J") {$myfield = "M$myfield";}
		if ($flt[2] == "N") {$myfield = "N$myfield";}
		$statisticsoutput .= "<input type='checkbox' class='checkboxbtn' name='summary[]' value='$myfield'";
		if (isset($summary) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE  || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
		{$statisticsoutput .= " checked='checked'";}
		$statisticsoutput .= " />&nbsp;".showSpeaker($niceqtext)."</strong>"
		."<br />\n";
		if ($flt[2] == "N") {$statisticsoutput .= "</font>";}
		if ($flt[2] != "N") {$statisticsoutput .= "\t\t\t\t<select name='";}
		if ($flt[2] == "M" || $flt[2] == "P" || $flt[2] == "R" || $flt[2] == "J") {$statisticsoutput .= "M";}
		if ($flt[2] != "N") {$statisticsoutput .= "{$surveyid}X{$flt[1]}X{$flt[0]}[]' multiple='multiple'>\n";}
		$allfields[]=$myfield;
	}
	$statisticsoutput .= "\t\t\t\t\t<!-- QUESTION TYPE = $flt[2] -->\n";
	/////////////////////////////////////////////////////////////////////////////////////////////////
	//This section presents the filter list, in various different ways depending on the question type
	/////////////////////////////////////////////////////////////////////////////////////////////////
	switch ($flt[2])
	{
		case "K": // Multiple Numerical
		$statisticsoutput .= "\t\t\t\t\t</tr>\n\t\t\t\t\t<tr>\n";
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language = '{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		$counter2=0;
		while ($row=$result->FetchRow())
		{
		    $myfield1="K".$myfield.$row[0];
		    $myfield2="K{$myfield}".$row[0]."G";
		    $myfield3="K{$myfield}".$row[0]."L";
			if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
			$statisticsoutput .= "\t\t\t\t<td align='center' valign='top'><strong>$flt[3]-".$row[0]."</strong></font>";
			$statisticsoutput .= "<input type='checkbox' class='checkboxbtn' name='summary[]' value='$myfield1'";
			if (isset($summary) && (array_search("K{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}", $summary) !== FALSE))
			{$statisticsoutput .= " checked='checked'";}
			$statisticsoutput .= " />&nbsp;&nbsp;";
		    $statisticsoutput .= showSpeaker(FlattenText($row[1]))."<br />\n";
		    $statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Number greater than").":</font><br />\n"
		    ."\t\t\t\t\t<input type='text' name='$myfield2' value='";
		    if (isset($_POST[$myfield2])){$statisticsoutput .= $_POST[$myfield2];}
		    $statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n"
		    ."\t\t\t\t\t".$clang->gT("Number Less Than").":<br />\n"
		    ."\t\t\t\t\t<input type='text' name='$myfield3' value='";
		    if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}
		    $statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n";
			$counter2++;
			$allfields[]=$myfield1;
		    $allfields[]=$myfield2;
		    $allfields[]=$myfield3;
		}
		break;
		case "Q": // Multiple Short Text
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		$counter2=0;
		while ($row = $result->FetchRow())
		{
			$myfield2 = "Q".$myfield."$row[0]";
			if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
			$statisticsoutput .= "\t\t\t\t<td align='center' valign='top'><strong>$flt[3]-".$row[0]."</strong></font>";
			$statisticsoutput .= "<input type='checkbox' class='checkboxbtn' name='summary[]' value='$myfield2'";
			if (isset($summary) && (array_search("Q{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}", $summary) !== FALSE))
			{$statisticsoutput .= " checked='checked'";}
			$statisticsoutput .= " />&nbsp;&nbsp;";
		    $statisticsoutput .= showSpeaker(FlattenText($row[1]))
			."<br />\n"
			."\t\t\t\t\t<font size='1'>".$clang->gT("Responses Containing").":</font><br />\n"
			."\t\t\t\t\t<input type='text' name='$myfield2' value='";
			if (isset($_POST[$myfield2]))
			{$statisticsoutput .= $_POST[$myfield2];}
			$statisticsoutput .= "' />";
			$counter2++;
			$allfields[]=$myfield2;
		}
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$counter=0;
		break;
		case "T": // Long free text
		case "U": // Huge free text
		$myfield2="T$myfield";
		$statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>"
		."<strong>$flt[3]</strong></font>";
		$statisticsoutput .= "<input type='checkbox' class='checkboxbtn' name='summary[]' value='$myfield2'";
		if (isset($summary) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
		{$statisticsoutput .= " checked='checked'";}
		$statisticsoutput .= " />&nbsp;"
		."&nbsp;".showSpeaker($niceqtext)
		."<br />\n"
		."\t\t\t\t\t<font size='1'>".$clang->gT("Responses Containing").":</font><br />\n"
		."\t\t\t\t\t<textarea name='$myfield2' rows='3' cols='80'>";
		if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
		$statisticsoutput .= "</textarea>";
		$allfields[]=$myfield2;
		break;
		case "S": // Short free text
		$myfield2="T$myfield";
		$statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>"
		."<strong>$flt[3]</strong>";
		$statisticsoutput .= "<input type='checkbox' class='checkboxbtn' name='summary[]' value='$myfield2'";
		if (isset($summary) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
		{$statisticsoutput .= " checked='checked'";}
		$statisticsoutput .= " />&nbsp;"
		."&nbsp;".showSpeaker($niceqtext)
		."<br />\n"
		."\t\t\t\t\t<font size='1'>".$clang->gT("Responses Containing").":</font><br />\n"
		."\t\t\t\t\t<input type='text' name='$myfield2' value='";
		if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
		$statisticsoutput .= "' />";
		$allfields[]=$myfield2;
		break;
		case "N": // Numerical
		$myfield2="{$myfield}G";
		$myfield3="{$myfield}L";
		$statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Number greater than").":</font><br />\n"
		."\t\t\t\t\t<input type='text' name='$myfield2' value='";
		if (isset($_POST[$myfield2])){$statisticsoutput .= $_POST[$myfield2];}
		$statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n"
		."\t\t\t\t\t".$clang->gT("Number Less Than").":<br />\n"
		."\t\t\t\t\t<input type='text' name='$myfield3' value='";
		if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}
		$statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n";
		$allfields[]=$myfield2;
		$allfields[]=$myfield3;
		break;
		case "D": // Date
		$myfield2="D$myfield";
		$myfield3="$myfield2=";
		$myfield4="$myfield2<"; 
        $myfield5="$myfield2>";
		$statisticsoutput .= "\t\t\t\t<td align='center' valign='top'><strong>$flt[3]</strong>"
		."&nbsp;".showSpeaker($niceqtext)
		."<br />\n"
		."\t\t\t\t\t<font size='1'>".$clang->gT("Date (YYYY-MM-DD) equals").":<br />\n"
		."\t\t\t\t\t<input name='$myfield3' type='text' value='";
		if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}
		$statisticsoutput .= "' ".substr(2, 0, -13) ."; width:80' /><br />\n"
		."\t\t\t\t\t&nbsp;&nbsp;".$clang->gT("OR between").":<br />\n"
		."\t\t\t\t\t<input name='$myfield4' value='";
		if (isset($_POST[$myfield4])) {$statisticsoutput .= $_POST[$myfield4];}
		$statisticsoutput .= "' type='text' ".substr(2, 0, -13)
		."; width:65' /> ".$clang->gT("and")." <input  name='$myfield5' value='";
		if (isset($_POST[$myfield5])) {$statisticsoutput .= $_POST[$myfield5];}
		$statisticsoutput .= "' type='text' ".substr(2, 0, -13)
		."; width:65' /></font>\n";
        $allfields[]=$myfield3;
        $allfields[]=$myfield4;
        $allfields[]=$myfield5;
		break;
		case "5": // 5 point choice
		for ($i=1; $i<=5; $i++)
		{
			$statisticsoutput .= "\t\t\t\t\t<option value='$i'";
			if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($i, $_POST[$myfield]))
			{$statisticsoutput .= " selected";}
			$statisticsoutput .= ">$i</option>\n";
		}
		$statisticsoutput .="\t\t\t\t</select></font>\n";
		break;
		case "G": // Gender
		$statisticsoutput .= "\t\t\t\t\t<option value='F'";
		if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("F", $_POST[$myfield])) {$statisticsoutput .= " selected";}
		$statisticsoutput .= ">".$clang->gT("Female")."</option>\n";
		$statisticsoutput .= "\t\t\t\t\t<option value='M'";
		if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("M", $_POST[$myfield])) {$statisticsoutput .= " selected";}
		$statisticsoutput .= ">".$clang->gT("Male")."</option>\n\t\t\t\t</select></font>\n";
		break;
		case "Y": // Yes\No
		$statisticsoutput .= "\t\t\t\t\t<option value='Y'";
		if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("Y", $_POST[$myfield])) {$statisticsoutput .= " selected";}
		$statisticsoutput .= ">".$clang->gT("Yes")."</option>\n"
		."\t\t\t\t\t<option value='N'";
		if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("N", $_POST[$myfield])) {$statisticsoutput .= " selected";}
		$statisticsoutput .= ">".$clang->gT("No")."</option></select></font>\n";
		break;
		case "I": // Language
		$survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
		$survlangs[] = GetBaseLanguageFromSurveyID($surveyid);
		foreach ($survlangs  as $availlang)
		{
			$statisticsoutput .= "\t\t\t\t\t<option value='".$availlang."'";
			if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($availlang, $_POST[$myfield])) 
				{$statisticsoutput .= " selected";}
			$statisticsoutput .= ">".getLanguageNameFromCode($availlang,false)."</option>\n";
		}
		break;
		// ARRAYS
		case "A": // ARRAY OF 5 POINT CHOICE QUESTIONS
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		$counter2=0;
		while ($row=$result->FetchRow())
		{
			$myfield2 = $myfield.$row[0];
			$statisticsoutput .= "<!-- $myfield2 - ";
			if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
			$statisticsoutput .= " -->\n";
			if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}

			$statisticsoutput .= "\t\t\t\t<td align='center'><b>$flt[3] ($row[0])</b>"
			."<input type='checkbox' class='checkboxbtn' name='summary[]' value='$myfield2'";
			if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}
			$statisticsoutput .= " />&nbsp;"
			.showSpeaker($niceqtext." ".str_replace("'", "`", $row[1]))
			."<br />\n"
			."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n";
			for ($i=1; $i<=5; $i++)
			{
				$statisticsoutput .= "\t\t\t\t\t<option value='$i'";
				if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {$statisticsoutput .= " selected";}
				if (isset($_POST[$myfield2]) && $_POST[$myfield2] == $i) {$statisticsoutput .= " selected";}
				$statisticsoutput .= ">$i</option>\n";
			}
			$statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</font></td>\n";
			$counter2++;
			$allfields[]=$myfield2;
		}
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$counter=0;
		break;
		case "B": // ARRAY OF 10 POINT CHOICE QUESTIONS
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		$counter2=0;
		while ($row=$result->FetchRow())
		{
			$myfield2 = $myfield . "$row[0]";
			$statisticsoutput .= "<!-- $myfield2 - ";
			if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
			$statisticsoutput .= " -->\n";
			if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}

			$statisticsoutput .= "\t\t\t\t<td align='center'><b>$flt[3] ($row[0])</b>"; //heading
			$statisticsoutput .= "<input type='checkbox' class='checkboxbtn' name='summary[]' value='$myfield2'";
			if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}
			$statisticsoutput .= " />&nbsp;"
			.showSpeaker($niceqtext." ".str_replace("'", "`", $row[1]))
			."<br />\n"
			."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n";
			for ($i=1; $i<=10; $i++)
			{
				$statisticsoutput .= "\t\t\t\t\t<option value='$i'";
				if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {$statisticsoutput .= " selected";}
				if (isset($_POST[$myfield2]) && $_POST[$myfield2] == $i) {$statisticsoutput .= " selected";}
				$statisticsoutput .= ">$i</option>\n";
			}
			$statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
			$counter2++;
			$allfields[]=$myfield2;
		}
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$counter=0;
		break;
		case "C": // ARRAY OF YES\No\$clang->gT("Uncertain") QUESTIONS
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		$counter2=0;
		while ($row=$result->FetchRow())
		{
			$myfield2 = $myfield . "$row[0]";
			$statisticsoutput .= "<!-- $myfield2 - ";
			if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
			$statisticsoutput .= " -->\n";
			if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
			$statisticsoutput .= "\t\t\t\t<td align='center'><b>$flt[3] ($row[0])</b>"
			."<input type='checkbox' class='checkboxbtn' name='summary[]' value='$myfield2'";
			if (isset($summary) && array_search($myfield2, $summary)!== FALSE)
			{$statisticsoutput .= " checked='checked'";}
			$statisticsoutput .= " />&nbsp;"
			.showSpeaker($niceqtext." ".str_replace("'", "`", $row[1]))
			."<br />\n"
			."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n"
			."\t\t\t\t\t<option value='Y'";
			if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("Y", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">".$clang->gT("Yes")."</option>\n"
			."\t\t\t\t\t<option value='U'";
			if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("U", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">".$clang->gT("Uncertain")."</option>\n"
			."\t\t\t\t\t<option value='N'";
			if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("N", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">".$clang->gT("No")."</option>\n"
			."\t\t\t\t</select>\n\t\t\t\t</td>\n";
			$counter2++;
			$allfields[]=$myfield2;
		}
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$counter=0;
		break;
		case "E": // ARRAY OF Increase/Same/Decrease QUESTIONS
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		$counter2=0;
		while ($row=$result->FetchRow())
		{
			$myfield2 = $myfield . "$row[0]";
			$statisticsoutput .= "<!-- $myfield2 - ";
			if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
			$statisticsoutput .= " -->\n";
			if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
			$statisticsoutput .= "\t\t\t\t<td align='center'><b>$flt[3] ($row[0])</b>"
			."<input type='checkbox' class='checkboxbtn' name='summary[]' value='$myfield2'";
			if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}
			$statisticsoutput .= " />&nbsp;"
			.showSpeaker($niceqtext." ".str_replace("'", "`", $row[1]))
			."<br />\n"
			."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n"
			."\t\t\t\t\t<option value='I'";
			if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("I", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">".$clang->gT("Increase")."</option>\n"
			."\t\t\t\t\t<option value='S'";
			if (isset($_POST[$myfield]) && is_array($_POST[$myfield2]) && in_array("S", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">".$clang->gT("Same")."</option>\n"
			."\t\t\t\t\t<option value='D'";
			if (isset($_POST[$myfield]) && is_array($_POST[$myfield2]) && in_array("D", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">".$clang->gT("Decrease")."</option>\n"
			."\t\t\t\t</select>\n\t\t\t\t</td>\n";
			$counter2++;
			$allfields[]=$myfield2;
		}
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$counter=0;
		break;
		case "F": // ARRAY OF Flexible QUESTIONS
		case "H": // ARRAY OF Flexible Questions (By Column)
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		$counter2=0;
		while ($row=$result->FetchRow())
		{
			$myfield2 = $myfield . "$row[0]";
			$statisticsoutput .= "<!-- $myfield2 - ";
			if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
			$statisticsoutput .= " -->\n";
			if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
			$statisticsoutput .= "\t\t\t\t<td align='center'><b>$flt[3] ($row[0])</b>"
			."<input type='checkbox' class='checkboxbtn' name='summary[]' value='$myfield2'";
			if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}
			$statisticsoutput .= " />&nbsp;"
			.showSpeaker($niceqtext." ".str_replace("'", "`", $row[1]))
			."<br />\n";
			$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$flt[6]} AND language='{$language}' ORDER BY sortorder, code";
			//$statisticsoutput .= $fquery;
			$fresult = db_execute_assoc($fquery);
			$statisticsoutput .= "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n";
			while ($frow = $fresult->FetchRow())
			{
				$statisticsoutput .= "\t\t\t\t\t<option value='{$frow['code']}'";
				if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {$statisticsoutput .= " selected";}
				$statisticsoutput .= ">{$frow['title']}</option>\n";
			}
			$statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
			$counter2++;
			$allfields[]=$myfield2;
		}
		$statisticsoutput .= "\t\t\t\t<td>\n";
		$counter=0;
		break;
		case "R": //RANKING
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_assoc($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		$count = $result->RecordCount();
		while ($row = $result->FetchRow())
		{
			$answers[]=array($row['code'], $row['answer']);
		}
		$counter2=0;
		for ($i=1; $i<=$count; $i++)
		{
			if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter=0;}
			$myfield2 = "R" . $myfield . $i . "-" . strlen($i);
			$myfield3 = $myfield . $i;
			$statisticsoutput .= "<!-- $myfield2 - ";
			if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
			$statisticsoutput .= " -->\n"
			."\t\t\t\t<td align='center'><b>$flt[3] ($i)</b>"
			."<input type='checkbox' class='checkboxbtn' name='summary[]' value='$myfield2'";
			if (isset($summary) && array_search($myfield2, $summary) !== FALSE) {$statisticsoutput .= " checked='checked'";}
			$statisticsoutput .= " />&nbsp;"
			.showSpeaker($niceqtext." ".str_replace("'", "`", $row[1]))
			."<br />\n"
			."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$i}[]' multiple='multiple'>\n";
			foreach ($answers as $ans)
			{
				$statisticsoutput .= "\t\t\t\t\t<option value='$ans[0]'";
				if (isset($_POST[$myfield3]) && is_array($_POST[$myfield3]) && in_array("$ans[0]", $_POST[$myfield3])) {$statisticsoutput .= " selected";}
				$statisticsoutput .= ">$ans[1]</option>\n";
			}
			$statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</font></td>\n";
			$counter2++;
			$allfields[]=$myfield2;
		}
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		//Link to rankwinner script - awaiting completion
		//          $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr bgcolor='#DDDDDD'>\n"
		//              ."<td colspan=$count align=center>"
		//              ."<input type='button' value='Show Rank Summary' onclick=\"window.open('rankwinner.php?sid=$surveyid&amp;qid=$flt[0]', '_blank', 'toolbar=no, directories=no, location=no, status=yes, menubar=no, resizable=no, scrollbars=no, width=400, height=300, left=100, top=100')\">"
		//              ."</td></tr>\n\t\t\t\t<tr>\n";
		$counter=0;
		unset($answers);
		break;
		case "X": //This is a boilerplate question and it has no business in this script
                $statisticsoutput .= "\t\t\t\t<td></td>";
		break;
		case "W":
		case "Z":
		$statisticsoutput .= "\t\t\t\t<td align='center'>"
		."<strong>$flt[3]&nbsp;"; //Heading (Question No)
		$statisticsoutput .= "<input type='checkbox' class='checkboxbtn' name='summary[]' value='$myfield'";
		if (isset($summary) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE  || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
		{$statisticsoutput .= " checked='checked'";}
		$statisticsoutput .= " />&nbsp;"
		.showSpeaker($niceqtext)."</strong>"
		."<br />\n";
		$statisticsoutput .= "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}[]' multiple='multiple'>\n";
		$allfields[]=$myfield;
		$query = "SELECT code, title FROM ".db_table_name("labels")." WHERE lid={$flt[6]} AND language='{$language}' ORDER BY sortorder";
		$result = db_execute_num($query) or die("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		while($row=$result->FetchRow())
		{
			$statisticsoutput .= "\t\t\t\t\t\t<option value='{$row[0]}'";
			if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">$row[1]</option>\n";
		} // while
		$statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
		break;
        
		default:
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or die("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		while ($row=$result->FetchRow())
		{
			$statisticsoutput .= "\t\t\t\t\t\t<option value='{$row[0]}'";
			if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">$row[1]</option>\n";
		}
		$statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
		break;
	}
	$currentgroup=$flt[1];
	if (!isset($counter)) {$counter=0;}
	$counter++;
}
$statisticsoutput .= "\n\t\t\t\t</tr>\n";
if (isset($allfields))
{
	$allfield=implode("+", $allfields);
}
if (incompleteAnsFilterstate() === true)
{
	$selecthide="selected='selected'";
	$selectshow="";
}
else
{
	$selecthide="";
	$selectshow="selected='selected'";
}

$statisticsoutput .= "\t\t\t</table>\n"
."\t\t</td></tr>\n"
."\t\t<tr><td align='center' class='settingcaption'>\n"
."\t\t<font size='1' face='verdana'>&nbsp;</font></td></tr>\n"
."\t\t\t\t<tr><td align='center'><input type='radio' class='radiobtn' id='viewsummaryall' name='summary' value='$allfield'"
." /><label for='viewsummaryall'>".$clang->gT("View summary of all available fields")."</label></font></td></tr>\n"
."\t\t<tr><td align='center' class='settingcaption'>\n"
."\t\t<font size='1' face='verdana'>&nbsp;</font></td></tr>\n"
."\t\t\t\t<tr><td align='center'>".$clang->gT("Filter incomplete answers:")."<select name='filterinc'>\n"
."\t\t\t\t\t<<option value='filter' $selecthide>".$clang->gT("Enable")."</option>\n"
."\t\t\t\t\t<option value='show' $selectshow>".$clang->gT("Disable")."</option>\n"
."\t\t\t\t</select></td></tr>\n"
."\t\t\t\t<tr><td align='center'><input type='checkbox' id='noncompleted' name='noncompleted'/><label for='noncompleted'>".$clang->gT("Don't consider NON completed responses (only works when Filter incomplete answers is Disable)")."</label></font></td></tr>\n";

if (isset($usejpgraph) && $usejpgraph == 1)
{
	$statisticsoutput .= "\t\t\t\t<tr><td align='center'><input type='checkbox' id='usegraph' name='usegraph' checked='checked'/><label for='usergraph'>".$clang->gT("Use graphical output")."</label></font></td></tr>\n";
}

$statisticsoutput .= "\t\t<tr><td align='center'>\n\t\t\t<br />\n"
."\t\t\t<input type='submit' value='".$clang->gT("View Stats")."' />\n"
."\t\t\t<input type='button' value='".$clang->gT("Clear")."' onclick=\"window.open('$scriptname?action=statistics&amp;sid=$surveyid', '_top')\" />\n"
."\t\t<br />&nbsp;\n"
."\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
."\t\t<input type='hidden' name='display' value='stats' />\n"
."\t</td></tr>\n"
."</table>\n"
."</td></tr></table>\n"
."\t</form>\n";

$fieldmap = createFieldMap($surveyid, "full");
$selectlist = "";
foreach ($fieldmap as $field)
{
	$selectlist .= "<option value='".$field['fieldname']."'>"
	.$field['title'].": ".$field['question']."</option>\n";
	//create a select list of all the possible answers to this question
	switch($field['type'])
	{
		case "Q":
		case "S":
		case "T":
		case "U":
		case "N":
		case "K":
		//text type - don't do anything
		break;
		case "G":
		$thisselect="<div id='question{$field['fieldname']}' style='display:none'><select size='10' style='font-size: 8.5px'>\n";
		$thisselect .= "<option value='F'>[F] ".$clang->gT("Female")."</option>\n";
		$thisselect .= "<option value='Y'>[M] ".$clang->gT("Male")."</option>\n";
		$thisselect .= "</select></div>\n";
		$answerselects[]=$thisselect;
		$asnames[]=$field['fieldname'];
		break;
		case "Y":
		$thisselect="<div id='question{$field['fieldname']}' style='display:none'><select size='10' style='font-size: 8.5px'>\n";
		$thisselect .= "<option value='Y'>[Y] ".$clang->gT("Yes")."</option>\n";
		$thisselect .= "<option value='N'>[N] ".$clang->gT("No")."</option>\n";
		$thisselect .= "</select></div>\n";
		$answerselects[]=$thisselect;
		$asnames[]=$field['fieldname'];
		break;
		case "M":
		//multiple choice - yes or nothing
		$thisselect="<div id='question{$field['fieldname']}' style='display:none'><select size='10' style='font-size: 8.5px'>\n";
		$thisselect .= "<option value='Y'>[Y] ".$clang->gT("Yes")."</option>\n";
		$thisselect .= "</select></div>\n";
		$answerselects[]=$thisselect;
		$asnames[]=$field['fieldname'];
		break;
		case "L":
		//list - show possible answers
		$query = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$field['qid']} AND language='{$language}'";
		$result = db_execute_assoc($query);
		$thisselect="<div id='question{$field['fieldname']}' style='display:none'><select size='10' style='font-size: 8.5px'>\n";
		while($row = $result->FetchRow())
		{
			$thisselect .= "<option value='".$row['code']."'>[".$row['code']."] ".$row['answer']."</option>\n";
		} // while
		$thisselect .= "</select></div>\n";
		$answerselects[]=$thisselect;
		$asnames[]=$field['fieldname'];
		break;
	} // switch
}

// DISPLAY RESULTS
if (isset($_POST['display']) && $_POST['display'])
{
	// Create progress bar
	$prb = new ProgressBar();
	$prb->pedding = 2;	// Bar Pedding
	$prb->brd_color = "#404040 #dfdfdf #dfdfdf #404040";	// Bar Border Color

	$prb->setFrame();	// set ProgressBar Frame
	$prb->frame['left'] = 50;	// Frame position from left
	$prb->frame['top'] = 	80;	// Frame position from top
	$prb->addLabel('text','txt1',$clang->gT("Please wait ..."));	// add Text as Label 'txt1' and value 'Please wait'
	$prb->addLabel('percent','pct1');	// add Percent as Label 'pct1'
	$prb->addButton('btn1',$clang->gT('Go Back'),'?action=statistics&amp;sid='.$surveyid);	// add Button as Label 'btn1' and action '?restart=1'

	$process_status = 35;
	$prb->show();	// show the ProgressBar
	
	$statisticsoutput .= "<script type='text/javascript'>
    <!--
     hide('filtersettings');
    //-->
    </script>\n";
	// 1: Get list of questions with answers chosen
	$prb->setLabelValue('txt1',$clang->gT('Getting Questions and Answers ...'));
	$prb->moveStep(5);
	for (reset($_POST); $key=key($_POST); next($_POST)) { $postvars[]=$key;} // creates array of post variable names
	foreach ($postvars as $pv)
	{
		if (in_array($pv, $allfields)) //Only do this if there is actually a value for the $pv
		{
			$firstletter=substr($pv,0,1);
			if ($pv != "sid" && $pv != "display" && $firstletter != "M" && $firstletter != "T" && 
			    $firstletter != "Q" && $firstletter != "D" && $firstletter != "N" && $firstletter != "K" &&
				$pv != "summary" && substr($pv, 0, 2) != "id" && substr($pv, 0, 9) != "datestamp") //pull out just the fieldnames
			{
				$thisquestion = db_quote_id($pv)." IN (";
				foreach ($_POST[$pv] as $condition)
				{
					$thisquestion .= "'$condition', ";
				}
				$thisquestion = substr($thisquestion, 0, -2)
				. ")";
				$selects[]=$thisquestion;
			}
			elseif (substr($pv, 0, 1) == "M")
			{
				list($lsid, $lgid, $lqid) = explode("X", $pv);
				$aquery="SELECT code FROM ".db_table_name("answers")." WHERE qid=$lqid AND language='{$language}' ORDER BY sortorder, answer";
				$aresult=db_execute_num($aquery) or die ("Couldn't get answers<br />$aquery<br />".$connect->ErrorMsg());
				while ($arow=$aresult->FetchRow()) // go through every possible answer
				{
					if (in_array($arow[0], $_POST[$pv])) // only add condition if answer has been chosen
					{
						$mselects[]=db_quote_id(substr($pv, 1, strlen($pv)).$arow[0])." = 'Y'";
					}
				}
				if ($mselects)
				{
					$thismulti=implode(" OR ", $mselects);
					$selects[]="($thismulti)";
				}
			}
			elseif (substr($pv, 0, 1) == "N" || substr($pv, 0, 1) == "K")
			{
				if (substr($pv, strlen($pv)-1, 1) == "G" && $_POST[$pv] != "")
				{
					$selects[]=db_quote_id(substr($pv, 1, -1))." > ".sanitize_int($_POST[$pv]);
				}
				if (substr($pv, strlen($pv)-1, 1) == "L" && $_POST[$pv] != "")
				{
                    $selects[]=db_quote_id(substr($pv, 1, -1))." < ".sanitize_int($_POST[$pv]);
				}
			}
			elseif (substr($pv, 0, 2) == "id")
			{
				if (substr($pv, strlen($pv)-1, 1) == "G" && $_POST[$pv] != "")
				{
                    $selects[]=db_quote_id(substr($pv, 0, -1))." > '".$_POST[$pv]."'";
				}
				if (substr($pv, strlen($pv)-1, 1) == "L" && $_POST[$pv] != "")
				{
                    $selects[]=db_quote_id(substr($pv, 0, -1))." < '".$_POST[$pv]."'";
				}
				if (substr($pv, strlen($pv)-1, 1) == "=" && $_POST[$pv] != "")
				{
                    $selects[]=db_quote_id(substr($pv, 0, -1))." = '".$_POST[$pv]."'";
				}
			}
			elseif ((substr($pv, 0, 1) == "T" || substr($pv, 0, 1) == "Q" ) && $_POST[$pv] != "")
			{
                $selects[]=db_quote_id(substr($pv, 1, strlen($pv)))." like '%".$_POST[$pv]."%'";
			}
			elseif (substr($pv, 0, 1) == "D" && $_POST[$pv] != "")
			{
				if (substr($pv, -1, 1) == "=")
				{
                    $selects[]=db_quote_id(substr($pv, 1, strlen($pv)-2))." = '".$_POST[$pv]."'";
				}
				else
				{
					if (substr($pv, -1, 1) == "<")
					{
						$selects[]= db_quote_id(substr($pv, 1, strlen($pv)-2)) . " > '".$_POST[$pv]."'";
					}
					if (substr($pv, -1, 1) == ">")
					{
                        $selects[]= db_quote_id(substr($pv, 1, strlen($pv)-2)) . " < '".$_POST[$pv]."'";
					}
				}
			}
			elseif (substr($pv, 0, 9) == "datestamp")
			{
				if (substr($pv, -1, 1) == "=" && !empty($_POST[$pv]))
				{
					$selects[] = db_quote_id('datestamp')." = '".$_POST[$pv]."'";
				}
				else
				{
					if (substr($pv, -1, 1) == "<" && !empty($_POST[$pv]))
					{
						$selects[]= db_quote_id('datestamp')." > '".$_POST[$pv]."'";
					}
					if (substr($pv, -1, 1) == ">" && !empty($_POST[$pv]))
					{
						$selects[]= db_quote_id('datestamp')." < '".$_POST[$pv]."'";
					}
				}
			}
		} else {
			$statisticsoutput .= "<!-- $pv DOES NOT EXIST IN ARRAY -->";
		}
	}
	// 2: Do SQL query
	$prb->setLabelValue('txt1',$clang->gT('Getting Result Count ...'));
	$prb->moveStep(35);
	$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid");
	if (incompleteAnsFilterstate() === true) {$query .= " WHERE submitdate is not null";}
	$result = db_execute_num($query) or die ("Couldn't get total<br />$query<br />".$connect->ErrorMsg());
	while ($row=$result->FetchRow()) {$total=$row[0];}
	if (isset($selects) && $selects)
	{
		if (incompleteAnsFilterstate() === true) {$query .= " AND ";}
		else {$query .= " WHERE ";}
		$query .= implode(" AND ", $selects);
	}
	elseif (!empty($_POST['sql']) && !isset($_POST['id=']))
	{
		$newsql=substr($_POST['sql'], strpos($_POST['sql'], "WHERE")+5, strlen($_POST['sql']));
		//$query = $_POST['sql'];
		if (incompleteAnsFilterstate() === true) {$query .= " AND ".$newsql;}
		else {$query .= " WHERE ".$newsql;}
	}
	$result=db_execute_num($query) or die("Couldn't get results<br />$query<br />".$connect->ErrorMsg());
	while ($row=$result->FetchRow()) {$results=$row[0];}

	// 3: Present results including option to view those rows
	$statisticsoutput .= "<br />\n<table align='center' width='95%' border='1'  "
	."cellpadding='2' cellspacing='0' >\n"
	."\t<tr><td colspan='2' align='center'><strong>"
	.$clang->gT("Results")."</strong></td></tr>\n"
	."\t<tr><td colspan='2' align='center'>"
	.""
	."<strong>".$clang->gT("No of records in this query").": $results </strong><br />\n\t\t"
	.$clang->gT("Total records in survey").": $total<br />\n";
	if ($total)
	{
		$percent=sprintf("%01.2f", ($results/$total)*100);
		$statisticsoutput .= $clang->gT("Percentage of total")
		.": $percent%<br />";
	}
	$statisticsoutput .= "\n\t\t</td></tr>\n";
	if (isset ($selects) && $selects) {$sql=implode(" AND ", $selects);}
	elseif (!empty($newsql)) {$sql = $newsql;}
	if (!isset($sql) || !$sql) {$sql="NULL";}
	if ($results > 0)
	{
		$statisticsoutput .= "\t<tr>"
		."\t\t<td align='right' width='50%'><form action='$scriptname?action=browse' method='post' target='_blank'>\n"
		."\t\t<input type='submit' value='".$clang->gT("Browse")."'  />\n"
		."\t\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
		."\t\t\t<input type='hidden' name='sql' value=\"$sql\" />\n"
		."\t\t\t<input type='hidden' name='subaction' value='all' />\n"
		."\t\t</form></td>\n"
		."\t\t<td width='50%'><form action='$scriptname?action=exportresults' method='post' target='_blank'>\n"
		."\t\t<input type='submit' value='".$clang->gT("Export")."'  />\n"
		."\t\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
		."\t\t\t<input type='hidden' name='sql' value=\"$sql\" />\n";
		//Add the fieldnames
		if (isset($summary) && $summary)
		{
			foreach($summary as $viewfields)
			{
				switch(substr($viewfields, 0, 1))
				{
					case "N":
					case "T":
					case "K":
					$field = substr($viewfields, 1, strlen($viewfields)-1);
					$statisticsoutput .= "\t\t\t<input type='hidden' name='summary[]' value='$field' />\n";
					break;
					case "M":
					list($lsid, $lgid, $lqid) = explode("X", substr($viewfields, 1, strlen($viewfields)-1));
					$aquery="SELECT code FROM ".db_table_name("answers")." WHERE qid=$lqid AND language='{$language}' ORDER BY sortorder, answer";
					$aresult=db_execute_num($aquery) or die ("Couldn't get answers<br />$aquery<br />".$connect->ErrorMsg());
					while ($arow=$aresult->FetchRow()) // go through every possible answer
					{
						$field = substr($viewfields, 1, strlen($viewfields)-1).$arow[0];
						$statisticsoutput .= "\t\t\t<input type='hidden' name='summary[]' value='$field' />\n";
					}
					$aquery = "SELECT other FROM ".db_table_name("questions")." WHERE qid=$lqid AND language='{$language}'";
					$aresult = db_execute_num($aquery);
					while($arow = $aresult->FetchRow()){
						if ($arow[0] == "Y") {
							//$statisticsoutput .= $arow[0];
							$field = substr($viewfields, 1, strlen($viewfields)-1)."other";
							$statisticsoutput .= "\t\t\t<input type='hidden' name='summary[]' value='$field' />\n";
						}
					} // while
					break;
					default:
					$field = $viewfields;
					$statisticsoutput .= "\t\t\t<input type='hidden' name='summary[]' value='$field' />\n";
					break;
				}
			}
		}
		$statisticsoutput .= "\t\t</form></td>\n\t</tr>\n";
	}
	$statisticsoutput .= "</table><br />\n";
}

$process_status = 40;

//Show Summary results
if (isset($summary) && $summary)
{
	$prb->setLabelValue('txt1',$clang->gT('Generating Summaries ...'));
	$prb->moveStep($process_status);
	if ($usejpgraph == 1 && isset($jpgraphdir)) //JPGRAPH CODING SUBMITTED BY Pieterjan Heyse
	{
		//Delete any old temp image files
		deletePattern($tempdir, "STATS_".date("d")."X".$currentuser."X".$surveyid."X"."*.png");
	}

	$runthrough=$summary;

	//START Chop up fieldname and find matching questions
	$lq = "SELECT DISTINCT qid FROM ".db_table_name("questions")." WHERE sid=$surveyid"; //GET LIST OF LEGIT QIDs FOR TESTING LATER
	$lr = db_execute_assoc($lq);
	while ($lw = $lr->FetchRow())
	{
		$legitqids[] = $lw['qid']; //this creates an array of question id's'
	}
	//Finished collecting legitqids
	foreach ($runthrough as $rt)
	{
		if ($process_status < 100) $process_status++;
		$prb->moveStep($process_status);
		// 1. Get answers for question ##############################################################
		if (substr($rt, 0, 1) == "M" || substr($rt, 0, 1) == "J") //MULTIPLE OPTION, THEREFORE MULTIPLE FIELDS.
		{
			list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);
			$nquery = "SELECT title, type, question, lid, other FROM ".db_table_name("questions")." WHERE language='{$language}' and qid='$qqid'";
			$nresult = db_execute_num($nquery) or die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());
			while ($nrow=$nresult->FetchRow())
			{
				$qtitle=$nrow[0];
				$qtype=$nrow[1];
				$qquestion=strip_tags($nrow[2]);
				$qlid=$nrow[3];
				$qother=$nrow[4];
			}

			//1. Get list of answers
			$query="SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qqid' AND language='{$language}' ORDER BY sortorder, answer";
			$result=db_execute_num($query) or die("Couldn't get list of answers for multitype<br />$query<br />".$connect->ErrorMsg());
			while ($row=$result->FetchRow())
			{
				$mfield=substr($rt, 1, strlen($rt))."$row[0]";
				$alist[]=array("$row[0]", "$row[1]", $mfield);
			}
			if ($qother == "Y")
			{
				$mfield=substr($rt, 1, strlen($rt))."other";
				$alist[]=array($clang->gT("Other"), $clang->gT("Other"), $mfield);
			}
		}
		elseif (substr($rt, 0, 1) == "T" || substr($rt, 0, 1) == "S") //Short and long text
		{
			list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);
			$nquery = "SELECT title, type, question, other FROM ".db_table_name("questions")." WHERE qid='$qqid' AND language='{$language}'";
			$nresult = db_execute_num($nquery) or die("Couldn't get text question<br />$nquery<br />".$connect->ErrorMsg());
			while ($nrow=$nresult->FetchRow())
			{
				$qtitle=$nrow[0]; $qtype=$nrow[1];
				$qquestion=strip_tags($nrow[2]);
			}
			$mfield=substr($rt, 1, strlen($rt));
			$alist[]=array("Answers", $clang->gT("Answer"), $mfield);
			$alist[]=array("NoAnswer", $clang->gT("No answer"), $mfield);
		}
		elseif (substr($rt, 0, 1) == "Q") //Multiple short text
		{
			list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);
            $tmpqid=substr($qqid, 0, strlen($qqid)-1);
            while (!in_array ($tmpqid,$legitqids)) $tmpqid=substr($tmpqid, 0, strlen($tmpqid)-1); 
            $qidlength=strlen($tmpqid);
            $qaid=substr($qqid, $qidlength, strlen($qqid)-$qidlength);
			$nquery = "SELECT title, type, question, other FROM ".db_table_name("questions")." WHERE qid='".substr($qqid, 0, $qidlength)."' AND language='{$language}'";
			$nresult = db_execute_num($nquery) or die("Couldn't get text question<br />$nquery<br />".$connect->ErrorMsg());
			$count = substr($qqid, strlen($qqid)-1);
			while ($nrow=$nresult->FetchRow())
			{
				$qtitle=$nrow[0].'-'.$count; $qtype=$nrow[1];
				$qquestion=strip_tags($nrow[2]);
			}
		    $qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qqid' AND code='$qaid' AND language='{$language}' ORDER BY sortorder, answer";
		    $qresult=db_execute_num($qquery) or die ("Couldn't get answer details (Array 5p Q)<br />$qquery<br />".$connect->ErrorMsg());
		    while ($qrow=$qresult->FetchRow())
	    	{
		    	$atext=$qrow[1];
		    }
		    $qtitle .= " [$atext]";
			$mfield=substr($rt, 1, strlen($rt));
			$alist[]=array("Answers", $clang->gT("Answer"), $mfield);
			$alist[]=array("NoAnswer", $clang->gT("No answer"), $mfield);
		}
		elseif (substr($rt, 0, 1) == "R") //RANKING OPTION THEREFORE CONFUSING
		{
			$lengthofnumeral=substr($rt, strpos($rt, "-")+1, 1);
			list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strpos($rt, "-")-($lengthofnumeral+1)), 3);
			$nquery = "SELECT title, type, question FROM ".db_table_name("questions")." WHERE qid='$qqid' AND language='{$language}'";
			$nresult = db_execute_num($nquery) or die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());
			while ($nrow=$nresult->FetchRow())
			{
				$qtitle=$nrow[0]. " [".substr($rt, strpos($rt, "-")-($lengthofnumeral), $lengthofnumeral)."]";
				$qtype=$nrow[1];
				$qquestion=strip_tags($nrow[2]). "[".$clang->gT("Ranking")." ".substr($rt, strpos($rt, "-")-($lengthofnumeral), $lengthofnumeral)."]";
			}
			$query="SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qqid' AND language='{$language}' ORDER BY sortorder, answer";
			$result=db_execute_num($query) or die("Couldn't get list of answers for multitype<br />$query<br />".$connect->ErrorMsg());
			while ($row=$result->FetchRow())
			{
				$mfield=substr($rt, 1, strpos($rt, "-")-1);
				$alist[]=array("$row[0]", "$row[1]", $mfield);
			}
		}
		elseif (substr($rt, 0, 1) == "N" || substr($rt, 0, 1) == "K") //NUMERICAL TYPE
		{
			if (substr($rt, -1) == "G" || substr($rt, -1) == "L" || substr($rt, -1) == "=")
			{
				//DO NOTHING
			}
			else
			{
		        list($qsid, $qgid, $qqid) = explode("X", $rt, 3);

			    if(substr($rt, 0, 1) == "K")
			    { // This is a multiple numerical question so we need to strip of the answer id to find the question title
                    $tmpqid=substr($qqid, 0, strlen($qqid)-1);
                    while (!in_array ($tmpqid,$legitqids)) $tmpqid=substr($tmpqid, 0, strlen($tmpqid)-1); 
                    $qidlength=strlen($tmpqid);
                    $qaid=substr($qqid, $qidlength, strlen($qqid)-$qidlength);
			        $nquery = "SELECT title, type, question, qid, lid 
							   FROM ".db_table_name("questions")." 
							   WHERE qid='".substr($qqid, 0, $qidlength)."' 
							   AND language='{$language}'";
			        $nresult = db_execute_num($nquery) or die("Couldn't get text question<br />$nquery<br />".$connect->ErrorMsg());
				} else {
				    $nquery = "SELECT title, type, question, qid, lid FROM ".db_table_name("questions")." WHERE qid='$qqid' AND language='{$language}'";
				    $nresult = db_execute_num($nquery) or die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());
				}
				while ($nrow=$nresult->FetchRow()) 
				{
				    $qtitle=$nrow[0]; 
					$qtype=$nrow[1]; 
					$qquestion=strip_tags($nrow[2]); 
					$qiqid=$nrow[3]; 
					$qlid=$nrow[4];
				}
				//Get answer texts for multiple numerical
				if(substr($rt, 0, 1) == "K")
				{
				    $qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qaid' AND language='{$language}' ORDER BY sortorder, answer";
				    $qresult=db_execute_num($qquery) or die ("Couldn't get answer details (Array 5p Q)<br />$qquery<br />".$connect->ErrorMsg());
				    while ($qrow=$qresult->FetchRow())
			    	{
				    	$atext=$qrow[1];
				    }
				    $qtitle .= " [$atext]";
				}
				
				$statisticsoutput .= "<br />\n<table align='center' width='95%' border='1'  cellpadding='2' cellspacing='0' >\n"
				."\t<tr><td colspan='2' align='center'><strong>".$clang->gT("Field Summary for")." $qtitle:</strong>"
				."</td></tr>\n"
				."\t<tr><td colspan='2' align='center'><strong>$qquestion</strong></td></tr>\n"
				."\t<tr>\n\t\t<td width='50%' align='center' ><strong>"
				.$clang->gT("Calculation")."</strong></td>\n"
				."\t\t<td width='50%' align='center' ><strong>"
				.$clang->gT("Result")."</strong></td>\n"
				."\t</tr>\n";
				$fieldname=substr($rt, 1, strlen($rt));
                if ($connect->databaseType == 'odbc_mssql')
                    { $query = "SELECT STDEVP(".db_quote_id($fieldname)."*1) as stdev"; }
                else
                    { $query = "SELECT STDDEV(".db_quote_id($fieldname).") as stdev"; }
				$query .= ", SUM(".db_quote_id($fieldname)."*1) as sum";
				$query .= ", AVG(".db_quote_id($fieldname)."*1) as average";
				$query .= ", MIN(".db_quote_id($fieldname)."*1) as minimum";
				$query .= ", MAX(".db_quote_id($fieldname)."*1) as maximum";
                if ($connect->databaseType == 'odbc_mssql')
                    { $query .= " FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT NULL AND (".db_quote_id($fieldname)." NOT LIKE ' ')"; }
                else
                    { $query .= " FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT NULL AND (".db_quote_id($fieldname)." != ' ')"; }
				
                if (incompleteAnsFilterstate() === true) {$query .= " AND submitdate is not null";}
				if ($sql != "NULL") {$query .= " AND $sql";}
				$result=db_execute_assoc($query) or die("Couldn't do maths testing<br />$query<br />".$connect->ErrorMsg());
				while ($row=$result->FetchRow())
				{
					$showem[]=array($clang->gT("Sum"), $row['sum']);
					$showem[]=array($clang->gT("Standard Deviation"), $row['stdev']);
					$showem[]=array($clang->gT("Average"), $row['average']);
					$showem[]=array($clang->gT("Minimum"), $row['minimum']);
					$maximum=$row['maximum']; //we're going to put this after the quartiles for neatness
					$minimum=$row['minimum'];
				}


				//CALCULATE QUARTILES
				$query ="SELECT ".db_quote_id($fieldname)." FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT null AND ".db_quote_id($fieldname)." != ' '";
				if (incompleteAnsFilterstate() === true) {$query .= " AND submitdate is not null";}
				if ($sql != "NULL") {$query .= " AND $sql";}
				$result=$connect->Execute($query) or die("Disaster during median calculation<br />$query<br />".$connect->ErrorMsg());
				$querystarter="SELECT ".db_quote_id($fieldname)." FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT null AND ".db_quote_id($fieldname)." != ' '";
				if (incompleteAnsFilterstate() === true) {$querystarter .= " AND submitdate is not null";}                     
				if ($sql != "NULL") {$querystarter .= " AND $sql";}
				$medcount=$result->RecordCount();

				array_unshift($showem, array($clang->gT("Count"), $medcount));

				if ($medcount>1)   // Calculating only makes sense with more than one result
				{
					//1ST QUARTILE (Q1)
					$q1=(1/4)*($medcount+1);
					$q1b=(int)((1/4)*($medcount+1));
					$q1c=$q1b-1;
					$q1diff=$q1-$q1b;
					$total=0;
					if ($q1c<1) {$q1c=1;$lastnumber=0;}  // fix if there are too few values to evaluate.
					if ($q1 != $q1b)
					{
						//ODD NUMBER
						$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
						$result=db_select_limit_assoc($query, $q1c, 2) or die("1st Quartile query failed<br />".$connect->ErrorMsg());
						while ($row=$result->FetchRow())
						{
							if ($total == 0)    {$total=$total-$row[$fieldname];}
							else                {$total=$total+$row[$fieldname];}
							$lastnumber=$row[$fieldname];
						}
						$q1total=$lastnumber-(1-($total*$q1diff));
						if ($q1total < $minimum) {$q1total=$minimum;}
						$showem[]=array($clang->gT("1st Quartile (Q1)"), $q1total);
					}
					else
					{
						//EVEN NUMBER
						//TODO: See note above for 'ODD'
						$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
						$result=db_select_limit_assoc($query,1, $q1c) or die ("1st Quartile query failed<br />".$connect->ErrorMsg());
						while ($row=$result->FetchRow()) {$showem[]=array("1st Quartile (Q1)", $row[$fieldname]);}
					}
					$total=0;
					//MEDIAN (Q2)
					$median=(1/2)*($medcount+1);
					$medianb=(int)((1/2)*($medcount+1));
					$medianc=$medianb-1;
					$mediandiff=$median-$medianb;
					if ($median != (int)((($medcount+1)/2)-1))
					{
						//remainder
						$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
						$result=db_select_limit_assoc($query,2, $medianc) or die("What a complete mess with the remainder<br />$query<br />".$connect->ErrorMsg());
						while ($row=$result->FetchRow()) {$total=$total+$row[$fieldname];}
						$showem[]=array($clang->gT("2nd Quartile (Median)"), $total/2);
					}
					else
					{
						//EVEN NUMBER
						$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
						$result=db_select_limit_assoc($query,1, $medianc) or die("What a complete mess<br />$query<br />".$connect->ErrorMsg());
						while ($row=$result->FetchRow()) {$showem[]=array("Median Value", $row[$fieldname]);}
					}
					$total=0;
					//3RD QUARTILE (Q3)
					$q3=(3/4)*($medcount+1);
					$q3b=(int)((3/4)*($medcount+1));
					$q3c=$q3b-1;
					$q3diff=$q3-$q3b;
					if ($q3 != $q3b)
					{
						$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
						$result = db_select_limit_assoc($query,2,$q3c) or die("3rd Quartile query failed<br />".$connect->ErrorMsg());
						$lastnumber='';
						while ($row=$result->FetchRow())
						{
							if ($total == 0)    {$total=$total-$row[$fieldname];}
							else                {$total=$total+$row[$fieldname];}
							if (!$lastnumber) {$lastnumber=$row[$fieldname];}
						}
						$q3total=$lastnumber+($total*$q3diff);
						if ($q3total < $maximum) {$q1total=$maximum;}
						$showem[]=array($clang->gT("3rd Quartile (Q3)"), $q3total);
					}
					else
					{
						$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1";
						$result = db_select_limit_assoc($query,1, $q3c) or die("3rd Quartile even query failed<br />".$connect->ErrorMsg());
						while ($row=$result->FetchRow()) {$showem[]=array("3rd Quartile (Q3)", $row[$fieldname]);}
					}
					$total=0;
					$showem[]=array($clang->gT("Maximum"), $maximum);
					foreach ($showem as $shw)
					{
						$statisticsoutput .= "\t<tr>\n"
						."\t\t<td align='center' >$shw[0]</td>\n"
						."\t\t<td align='center' >$shw[1]</td>\n"
						."\t</tr>\n";
					}
					$statisticsoutput .= "\t<tr>\n"
					."\t\t<td colspan='3' align='center' bgcolor='#EEEEEE'>\n"
					."\t\t\t<font size='1'>".$clang->gT("Null values are ignored in calculations")."<br />\n"
					."\t\t\t".$clang->gT("Q1 and Q3 calculated using")." <a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>".$clang->gT("minitab method")."</a>"
					."</font>\n"
					."\t\t</td>\n"
					."\t</tr>\n</table>\n";
					unset($showem);
				}
				else
				{
					$statisticsoutput .= "\t<tr>\n"
					."\t\t<td align='center'  colspan='3'>Not enough values for calculation</td>\n"
					."\t</tr>\n</table>\n";
					unset($showem);
				}
			}
		}
		elseif (substr($rt, 0, 2) == "id" || substr($rt, 0, 9) == "datestamp")
		{
		}
		else // NICE SIMPLE SINGLE OPTION ANSWERS
		{
			$fieldmap=createFieldMap($surveyid);
			$fielddata=arraySearchByKey($rt, $fieldmap, "fieldname", 1);
			$qsid=$fielddata['sid'];
			$qgid=$fielddata['gid'];
			$qqid=$fielddata['qid'];
			$qanswer=$fielddata['aid'];
			$rqid=$qqid;
			$nquery = "SELECT title, type, question, qid, lid, other FROM ".db_table_name("questions")." WHERE qid='{$rqid}' AND language='{$language}'";
			$nresult = db_execute_num($nquery) or die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());
			while ($nrow=$nresult->FetchRow())
			{
				$qtitle=$nrow[0];
				$qtype=$nrow[1];
				$qquestion=strip_tags($nrow[2]);
				$qiqid=$nrow[3];
				$qlid=$nrow[4];
				$qother=$nrow[5];
			}
			$alist[]=array("", $clang->gT("No answer"));
			switch($qtype)
			{
				case "A": //Array of 5 point choices
				$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
				$qresult=db_execute_num($qquery) or die ("Couldn't get answer details (Array 5p Q)<br />$qquery<br />".$connect->ErrorMsg());
				while ($qrow=$qresult->FetchRow())
				{
					for ($i=1; $i<=5; $i++)
					{
						$alist[]=array("$i", "$i");
					}
					$atext=$qrow[1];
				}
				$qquestion .= "<br />\n[".$atext."]";
				$qtitle .= "($qanswer)";
				break;
				case "B": //Array of 10 point choices
				$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
				$qresult=db_execute_num($qquery) or die ("Couldn't get answer details (Array 10p Q)<br />$qquery<br />".$connect->ErrorMsg());
				while ($qrow=$qresult->FetchRow())
				{
					for ($i=1; $i<=10; $i++)
					{
						$alist[]=array("$i", "$i");
					}
					$atext=$qrow[1];
				}
				$qquestion .= "<br />\n[".$atext."]";
				$qtitle .= "($qanswer)";
				break;
				case "C": //Array of Yes/No/$clang->gT("Uncertain")
				$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
				$qresult=db_execute_num($qquery) or die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());
				while ($qrow=$qresult->FetchRow())
				{
					$alist[]=array("Y", $clang->gT("Yes"));
					$alist[]=array("N", $clang->gT("No"));
					$alist[]=array("U", $clang->gT("Uncertain"));
					$atext=$qrow[1];
				}
				$qquestion .= "<br />\n[".$atext."]";
				$qtitle .= "($qanswer)";
				break;
				case "E": //Array of Yes/No/$clang->gT("Uncertain")
				$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
				$qresult=db_execute_num($qquery) or die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());
				while ($qrow=$qresult->FetchRow())
				{
					$alist[]=array("I", $clang->gT("Increase"));
					$alist[]=array("S", $clang->gT("Same"));
					$alist[]=array("D", $clang->gT("Decrease"));
					$atext=$qrow[1];
				}
				$qquestion .= "<br />\n[".$atext."]";
				$qtitle .= "($qanswer)";
				break;
				case "F": //Array of Flexible
				case "H": //Array of Flexible by Column
				$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
				$qresult=db_execute_num($qquery) or die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());
				while ($qrow=$qresult->FetchRow())
				{
					$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$qlid}' AND language='{$language}'ORDER BY sortorder, code";
					$fresult = db_execute_assoc($fquery);
					while ($frow=$fresult->FetchRow())
					{
						$alist[]=array($frow['code'], $frow['title']);
					}
					$atext=$qrow[1];
				}
				$qquestion .= "<br />\n[".$atext."]";
				$qtitle .= "($qanswer)";
				break;
				case "G": //Gender
				$alist[]=array("F", $clang->gT("Female"));
				$alist[]=array("M", $clang->gT("Male"));
				break;
				case "Y": //Yes\No
				$alist[]=array("Y", $clang->gT("Yes"));
				$alist[]=array("N", $clang->gT("No"));
				break;
				case "I": //Language
				// Using previously defined $survlangs array of language codes
				foreach ($survlangs as $availlang)
				{$alist[]=array($availlang, getLanguageNameFromCode($availlang,false));}
				break;
				case "5": //5 Point
				for ($i=1; $i<=5; $i++)
				{
					$alist[]=array("$i", "$i");
				}
				break;
				case "W":
				case "Z":
				$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$qlid}' AND language='{$language}' ORDER BY sortorder, code";
				$fresult = db_execute_assoc($fquery);
				while ($frow=$fresult->FetchRow())
				{
					$alist[]=array($frow['code'], $frow['title']);
				}
				break;
				default:
				$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qqid' AND language='{$language}' ORDER BY sortorder, answer";
				$qresult = db_execute_num($qquery) or die ("Couldn't get answers list<br />$qquery<br />".$connect->ErrorMsg());
				while ($qrow=$qresult->FetchRow())
				{
					$alist[]=array("$qrow[0]", "$qrow[1]");
				}
				if (($qtype == "L" || $qtype == "!") && $qother == "Y")
				{
					$alist[]=array($clang->gT("Other"),$clang->gT("Other"),$fielddata['fieldname'].'other');
				}
			}
		}

		//foreach ($alist as $al) {$statisticsoutput .= "$al[0] - $al[1]<br />";} //debugging line
		//foreach ($fvalues as $fv) {$statisticsoutput .= "$fv | ";} //debugging line
		
		//2. Collect and Display results #######################################################################
		if (isset($alist) && $alist) //Make sure there really is an answerlist, and if so:
		{
			$statisticsoutput .= "<table width='95%' align='center' border='1'  cellpadding='2' cellspacing='0' class='statisticstable'>\n"
			."\t<tr><td colspan='3' align='center'><strong>"
			.$clang->gT("Field Summary for")." $qtitle:</strong>"
			."</td></tr>\n"
			."\t<tr><td colspan='3' align='center'><strong>"
			."$qquestion</strong></td></tr>\n"
			."\t<tr>\n\t\t<td width='50%' align='center' >"
			."<strong>".$clang->gT("Answer")."</strong></td>\n"
			."\t\t<td width='25%' align='center' >"
			."<strong>".$clang->gT("Count")."</strong></td>\n"
			."\t\t<td width='25%' align='center' >"
			."<strong>".$clang->gT("Percentage")."</strong></td>\n"
			."\t</tr>\n";
            $TotalCompleted = 0;    // this will count the asnwers considered completed
			foreach ($alist as $al)
			{
				if (isset($al[2]) && $al[2]) //picks out alist that come from the multiple list above
				{
					if ($al[1] == $clang->gT("Other"))
					{
						$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE ";
                        $query .= ($connect->databaseType == "mysql")?  db_quote_id($al[2])." != ''" : "NOT (".db_quote_id($al[2])." LIKE '')";
					}
					elseif ($qtype == "U" || $qtype == "T" || $qtype == "S" || $qtype == "Q")
					{
						if($al[0]=="Answers")
						{
							$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE ";
                            $query .= ($connect->databaseType == "mysql")?  db_quote_id($al[2])." != ''" : "NOT (".db_quote_id($al[2])." LIKE '')";
						}
						elseif($al[0]=="NoAnswer")
						{
							$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE (".db_quote_id($al[2])." IS NULL OR ";
                            $query .= ($connect->databaseType == "mysql")?  db_quote_id($al[2])." = '')" : " (".db_quote_id($al[2])." LIKE ''))";
						}
					}
					else
					{
						$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($al[2])." =";
						if (substr($rt, 0, 1) == "R")
						{
							$query .= " '$al[0]'";
						}
						else
						{
							$query .= " 'Y'";
						}
					}
				}
				else
				{                           
					$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($rt)." = '$al[0]'";
				}
				if (incompleteAnsFilterstate() === true) {$query .= " AND submitdate is not null";}                     
				if ($sql != "NULL") {$query .= " AND $sql";}
				$result=db_execute_num($query) or die ("Couldn't do count of values<br />$query<br />".$connect->ErrorMsg());
				// $statisticsoutput .= "\n<!-- ($sql): $query -->\n\n";
                while ($row=$result->FetchRow())                   // this just extracts the data, after we present
				{
                    $TotalCompleted += $row[0];
					if ($al[0] == "")
					{$fname=$clang->gT("No answer");}
					elseif ($al[0] == $clang->gT("Other") || $al[0] == "Answers")
					{$fname="$al[1] <input type='submit' value='".$clang->gT("Browse")."' onclick=\"window.open('admin.php?action=listcolumn&sid=$surveyid&amp;column=$al[2]&amp;sql=".urlencode($sql)."', 'results', 'width=460, height=500, left=50, top=50, resizable=yes, scrollbars=yes, menubar=no, status=no, location=no, toolbar=no')\" />";}
					elseif ($qtype == "S" || $qtype == "U" || $qtype == "T" || $qtype == "Q")
					{
						if ($al[0] == "Answers")
						{
							$fname= "$al[1] <input type='submit' value='"
							. $clang->gT("Browse")."' onclick=\"window.open('admin.php?action=listcolumn&sid=$surveyid&amp;column=$al[2]&amp;sql="
							. urlencode($sql)."', 'results', 'width=460, height=500, left=50, top=50, resizable=yes, scrollbars=yes, menubar=no, status=no, location=no, toolbar=no')\" />";
						}
						elseif ($al[0] == "NoAnswer")
						{
							$fname= "$al[1]";
						}
					}
					else
					{$fname="$al[1] ($al[0])";}
					if ($results > 0)
					{
						$gdata[] = ($row[0]/$results)*100;
					} else
					{
                        $gdata[] = "N/A";
					}
					$grawdata[]=$row[0];
                    $label[]=$fname;
					$justcode[]=$al[0];
                    $lbl[] = wordwrap(strip_tags($fname), 20, "\n");
                }
			}

            if ((incompleteAnsFilterstate() === false) and ($qtype != "M") and ($qtype != "P"))
            {
                if (isset($_POST["noncompleted"]) and ($_POST["noncompleted"] == "on"))
                {
                    $i=0;
                    while (isset($gdata[$i]))
                    {
                        if ($gdata[$i] != "N/A") { $gdata[$i] = ($grawdata[$i]/$TotalCompleted)*100; }
                        $i++;
				}
			}
                else
            {
                $TotalIncomplete = $results - $TotalCompleted;
                $fname=$clang->gT("Non completed");
                if ($results > 0)
                {
                    $gdata[] = ($TotalIncomplete/$results)*100;
                } else
                {
                        $gdata[] = "N/A";
                }
                $grawdata[]=$TotalIncomplete;
                    $label[]= $fname;
                $justcode[]=$fname;
                    $lbl[] = wordwrap(strip_tags($fname), 20, "\n");
                }
            }
            $i=0;
            while (isset($gdata[$i]))
            {
                $statisticsoutput .= "\t<tr>\n\t\t<td width='50%' align='center' >" . $label[$i] ."\n"
                ."\t\t</td>\n"
                ."\t\t<td width='25%' align='center' >" . $grawdata[$i] . "\n";
                
                if ($results > 0) {$vp=sprintf("%01.2f", ($row[0]/$results)*100)."%";} else {$vp="N/A";}
                
                $statisticsoutput .= "\t\t</td><td width='25%' align='center' >";
                if ($gdata[$i] == "N/A") 
                {
                    $statisticsoutput .= $gdata[$i];
                    $gdata[$i] = 0;
                }
                else
                    $statisticsoutput .= sprintf("%01.2f", $gdata[$i]) . "%";
                
                $statisticsoutput .= "\t\t</td>\n\t</tr>\n";
                
                $i++;
            }

			if ($usejpgraph == 1 && isset($_POST['usegraph']) && array_sum($gdata)>0) //JPGRAPH CODING ORIGINALLY SUBMITTED BY Pieterjan Heyse
			{
				$graph = "";
				$p1 = "";
				//                  $statisticsoutput .= "<pre>";
				//                  $statisticsoutput .= "GDATA:\n";
				//                  print_r($gdata);
				//                  $statisticsoutput .= "GRAWDATA\n";
				//                  print_r($grawdata);
				//                  $statisticsoutput .= "LABEL\n";
				//                  print_r($label);
				//                  $statisticsoutput .= "JUSTCODE\n";
				//                  print_r($justcode);
				//                  $statisticsoutput .= "LBL\n";
				//                  print_r($lbl);
				//                  $statisticsoutput .= "</pre>";
				//First, lets delete any earlier graphs from the tmp directory
				//$gdata and $lbl are arrays built at the end of the last section
				//that contain the values, and labels for the data we are about
				//to send to jpgraph.
				if ($qtype == "M" || $qtype == "P") { //Bar Graph
					$graph = new Graph(640,320,'png');
					$graph->SetScale("textint");
					$graph->img->SetMargin(50,50,50,50);
					$graph->xaxis->SetTickLabels($justcode);
					$graph->xaxis->SetFont(constant($jpgraphfont), FS_NORMAL, 8);
					$graph->xaxis->SetColor("black");
				//	$graph->xaxis->title->Set($clang->gT("Code"));
					$graph->xaxis->title->SetFont(constant($jpgraphfont), FS_BOLD, 9);
					$graph->xaxis->title->SetColor("black");
					$graph->yaxis->SetFont(constant($jpgraphfont), FS_NORMAL, 8);
					$graph->yaxis->SetColor("black");
					$graph->yaxis->title->Set($clang->gT("Count")." / $results");
					$graph->yaxis->title->SetFont(constant($jpgraphfont), FS_BOLD, 9);
					$graph->yaxis->title->SetColor("black");
					//$graph->Set90AndMargin();
				} else { //Pie Charts
				
                    $i = 0;
                    foreach ($gdata as $data)
                    {
                        if ($data != 0){$i++;}
                    }				
					$totallines=$i;
					if ($totallines>15) {
						$gheight=320+(6.7*($totallines-15));
						$fontsize=7;
						$legendtop=0.01;
						$setcentrey=0.5/(($gheight/320));
					} else {
						$gheight=320;
						$fontsize=8;
						$legendtop=0.07;
						$setcentrey=0.5;
					}
					$graph = new PieGraph(640,$gheight,'png');
					$graph->legend->SetFont(constant($jpgraphfont), FS_NORMAL, $fontsize);
					$graph->legend->SetPos(0.015, $legendtop, 'right', 'top');
					$graph->legend->SetFillColor("white");
    				global $jpgraph_antialiasing;
					if ($jpgraph_antialiasing == 1) $graph->SetAntiAliasing();
				}
				$graph->title->SetColor("#EEEEEE");
				$graph->SetMarginColor("#FFFFFF");
				// Set A title for the plot
				//$graph->title->Set($qquestion);
				$graph->title->SetFont(constant($jpgraphfont),FS_BOLD,13);
				// Create pie plot
				if ($qtype == "M" || $qtype == "P") { //Bar Graph
					$p1 = new BarPlot($grawdata);
					$p1->SetWidth(0.8);
					$p1->SetValuePos("center");
					$p1->SetFillColor("#4f81bd");
					if (!in_array(0, $grawdata)) { //don't show shadows if any of the values are 0 - jpgraph bug
						$p1->SetShadow();
					}
					$p1->value->Show();
					$p1->value->SetFont(constant($jpgraphfont),FS_BOLD,8);
					$p1->value->SetColor("#FFFFFF");
				} else { //Pie Chart
                    // this block is to remove the items with value == 0
                    $i = 0;
                    while (isset ($gdata[$i]))
                    {
                        if ($gdata[$i] == 0)
                        {
                           array_splice ($gdata, $i, 1);
                           array_splice ($lbl, $i, 1);
                        }
                        else
                        {$i++;}
                    }
                
					$p1 = new PiePlot3d($gdata);
					//                        $statisticsoutput .= "<pre>";print_r($lbl);$statisticsoutput .= "</pre>";
					//                        $statisticsoutput .= "<pre>";print_r($gdata);$statisticsoutput .= "</pre>";
					$p1->SetTheme("earth");
					$p1->SetLegends($lbl);
					$p1->SetSize(200);
					$p1->SetCenter(0.375,$setcentrey);
					$p1->value->SetColor("#000000");
					$p1->value->SetFont(constant($jpgraphfont),FS_NORMAL,12);
					// Set how many pixels each slice should explode
					//$p1->Explode(array(0,15,15,25,15));
				}

				if (!isset($ci)) {$ci=0;}
				$ci++;
				$graph->Add($p1);
				$gfilename="STATS_".date("d")."X".$currentuser."X".$surveyid."X".$ci.date("His").".png";
				$graph->Stroke($tempdir."/".$gfilename);
				$statisticsoutput .= "<tr><td colspan='3' style=\"text-align:center\"><img src=\"$tempurl/".$gfilename."\" border='1'></td></tr>";

				////// PIE ALL DONE
			}
			$statisticsoutput .= "</table>";
		}
		unset($gdata);
		unset($grawdata);
        unset($label);
		unset($lbl);
		unset($justcode);
		unset ($alist);
	}
    $statisticsoutput .= "<br />&nbsp\n";
}

if (isset($prb))
{
	$prb->setLabelValue('txt1',$clang->gT('Completed'));
	$prb->moveStep(100);
	$prb->hide();
}

function deletePattern($dir, $pattern = "")
{
	$deleted = false;
	$pattern = str_replace(array("\*","\?"), array(".*","."), preg_quote($pattern));
	if (substr($dir,-1) != "/") $dir.= "/";
	if (is_dir($dir))
	{
		$d = opendir($dir);
		while ($file = readdir($d))
		{
			if (is_file($dir.$file) && ereg("^".$pattern."$", $file))
			{
				if (unlink($dir.$file))
				{
					$deleted[] = $file;
				}
			}
		}
		closedir($d);
		return $deleted;
	}
	else return 0;
}

function deleteNotPattern($dir, $matchpattern, $pattern = "")
{
	$deleted = false;
	$pattern = str_replace(array("\*","\?"), array(".*","."), preg_quote($pattern));
	$matchpattern = str_replace(array("\*","\?"), array(".*","."), preg_quote($matchpattern));
	if (substr($dir,-1) != "/") $dir.= "/";
	if (is_dir($dir))
	{
		$d = opendir($dir);
		while ($file = readdir($d))
		{
			if (is_file($dir.$file) && ereg("^".$matchpattern."$", $file) && !ereg("^".$pattern."$", $file))
			{
				if (unlink($dir.$file))
				{
					$deleted[] = $file;
				}
			}
		}
		closedir($d);
		return $deleted;
	}
	else return 0;
}


function showSpeaker($hinttext)
{
  global $imagefiles, $clang;
  $reshtml= "<img src='$imagefiles/speaker.png' align='bottom' alt='$hinttext' title='$hinttext' "
           ." onclick=\"alert('".$clang->gT("Question","js").": $hinttext')\" />";
  return $reshtml; 
}


function countLines($array)
{
	//$totalelements=count($array);
	$totalnewlines=0;
	foreach ($array as $ar)
	{
		$totalnewlines=$totalnewlines+substr_count($ar, "\n")+1;
	}
	$totallines=$totalnewlines+count($array);
	return $totallines;
}

?>
