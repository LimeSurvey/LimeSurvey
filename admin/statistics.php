<?php
/*
<<<<<<< HEAD
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
 *
 */

/*
 * We need this later:
 *  1 - Array Dual Scale
 *  5 - 5 Point Choice
 *  A - Array (5 Point Choice)
 *  B - Array (10 Point Choice)
 *  C - Array (Yes/No/Uncertain)
 *  D - Date
 *  E - Array (Increase, Same, Decrease)
 *  F - Array (Flexible Labels)
 *  G - Gender
 *  H - Array (Flexible Labels) by Column
 *  I - Language Switch
 *  K - Multiple Numerical Input
 *  L - List (Radio)
 *  M - Multiple choice
 *  N - Numerical Input
 *  O - List With Comment
 *  P - Multiple choice with comments
 *  Q - Multiple Short Text
 *  R - Ranking
 *  S - Short Free Text
 *  T - Long Free Text
 *  U - Huge Free Text
 *  X - Boilerplate Question
 *  Y - Yes/No
 *  ! - List (Dropdown)
 *  : - Array (Flexible Labels) multiple drop down
 *  ; - Array (Flexible Labels) multiple texts
 *  | - File Upload


 Debugging help:
 echo '<script language="javascript" type="text/javascript">alert("HI");</script>';
 */

//split up results to extend statistics -> NOT WORKING YET! DO NOT ENABLE THIS!
$showcombinedresults = 0;

/*
 * this variable is used in the function shortencode() which cuts off a question/answer title
 * after $maxchars and shows the rest as tooltip
 */
$maxchars = 50;


=======
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
>>>>>>> refs/heads/stable_plus

if (isset($_REQUEST['jpgraphdir'])) {die('You cannot start this script directly');}

include_once("login_check.php");

//some includes, the progressbar is used to show a progressbar while generating the graphs
//include_once("login_check.php");
require_once('classes/core/class.progressbar.php');

//we collect all the output within this variable
$statisticsoutput ='';
<<<<<<< HEAD

//output for chosing questions to cross query
$cr_statisticsoutput = '';
=======
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

>>>>>>> refs/heads/stable_plus

// This gets all the 'to be shown questions' from the POST and puts these into an array
$summary=returnglobal('summary');
$statlang=returnglobal('statlang');

<<<<<<< HEAD
//if $summary isn't an array we create one
if (isset($summary) && !is_array($summary)) {
    $summary = explode("+", $summary);
=======
// This gets all the 'to be shown questions' from the POST and puts these into an array 
$summary=returnglobal('summary');
if (isset($summary) && !is_array($summary)) {
	$summary = explode("+", $summary);
>>>>>>> refs/heads/stable_plus
}

//no survey ID? -> come and get one
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

//still no survey ID -> error
if (!$surveyid)
{
    //need to have a survey id
    $statisticsoutput .= "<center>You have not selected a survey!</center>";
    exit;
}

// Set language for questions and answers to base language of this survey
$language = GetBaseLanguageFromSurveyID($surveyid);


//pick the best font file if font setting is 'auto'
if ($chartfontfile=='auto')
{
    $chartfontfile='vera.ttf';
    if ( $language=='ar')
    {
        $chartfontfile='KacstOffice.ttf';
    }
    elseif  ($language=='fa' )
    {
        $chartfontfile='KacstFarsi.ttf';
    }
    elseif  ($language=='el' )
    {
        $chartfontfile='DejaVuLGCSans.ttf';
    }
    elseif  ($language=='zh-Hant-HK' || $language=='zh-Hant-TW' || $language=='zh-Hans')
    {
        $chartfontfile='fireflysung.ttf';
    }

}
//$statisticsoutput .= "
//<script type='text/javascript'' >
//<!--
//function selectAll(name){
//	//var name=name;
//
//	alert(name);
//
//	temp = document.+name+.elements.length;
//
//    for (i=0; i < temp; i++) {
//    if(document.+name+.elements[i].checked == 1)
//    	{document.+name+.elements[i].checked = 0;
//    	 document.+name+.+name+_btn.value = 'Select All'; }
//    else {document.+name.elements[i].checked = 1;
//   	 document.+name+.+name+_btn.value = 'Deselect All'; }
//	}
//}
////-->
//</script>";

//hide/show the filter
//filtersettings by default aren't shown when showing the results
$statisticsoutput .= '<script type="text/javascript" src="scripts/statistics.js"></script>';

//headline with all icons for available statistic options
//Get the menubar
$statisticsoutput .= browsemenubar($clang->gT("Quick statistics"))


//we need a form which can pass the selected data later
."<form method='post' name='formbuilder' action='$scriptname?action=statistics#start'>\n";

//Select public language file
$query = "SELECT datestamp FROM {$dbprefix}surveys WHERE sid=$surveyid";
$result = db_execute_assoc($query) or safe_die("Error selecting language: <br />".$query."<br />".$connect->ErrorMsg());

/*
 * check if there is a datestamp available for this survey
 * yes -> $datestamp="Y"
 * no -> $datestamp="N"
 */
while ($row=$result->FetchRow()) {$datestamp=$row['datestamp'];}



// 1: Get list of questions from survey

/*
 * We want to have the following data
 * a) "questions" -> all table namens, e.g.
 * qid
 * sid
 * gid
 * type
 * title
 * question
 * preg
 * help
 * other
 * mandatory
 * lid
 * lid1
 * question_order
 * language
 *
 * b) "groups" -> group_name + group_order *
 */
$query = "SELECT questions.*, groups.group_name, groups.group_order\n"
." FROM ".db_table_name("questions") ." as questions, ".db_table_name("groups")." as groups\n"
." WHERE groups.gid=questions.gid\n"
." AND groups.language='".$language."'\n"
." AND questions.language='".$language."'\n"
." AND questions.parent_qid=0\n"
." AND questions.sid=$surveyid";
$result = db_execute_assoc($query) or safe_die("Couldn't do it!<br />$query<br />".$connect->ErrorMsg());

//store all the data in $rows
$rows = $result->GetRows();

//SORT IN NATURAL ORDER!
usort($rows, 'GroupOrderThenQuestionOrder');

//put the question information into the filter array
foreach ($rows as $row)
{
    //store some column names in $filters array
    $filters[]=array($row['qid'],
    $row['gid'],
    $row['type'],
    $row['title'],
    $row['group_name'],
    FlattenText($row['question']));
}

//var_dump($filters);
// SHOW ID FIELD

$statisticsoutput .= "<div class='header ui-widget-header'>".$clang->gT("General filters")."</div><div id='statistics_general_filter'>";


$grapherror='';
if (!function_exists("gd_info")) {
    $grapherror.='<br />'.$clang->gT('You do not have the GD Library installed. Showing charts requires the GD library to function properly.');
    $grapherror.='<br />'.$clang->gT('visit http://us2.php.net/manual/en/ref.image.php for more information').'<br />';
}
elseif (!function_exists("imageftbbox")) {
    $grapherror.='<br />'.$clang->gT('You do not have the Freetype Library installed. Showing charts requires the Freetype library to function properly.');
    $grapherror.='<br />'.$clang->gT('visit http://us2.php.net/manual/en/ref.image.php for more information').'<br />';
}
if ($grapherror!='')
{
<<<<<<< HEAD
    unset($_POST['usegraph']);
}


//pre-selection of filter forms
if (incompleteAnsFilterstate() == "filter")
=======
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
	if ($flt[2] != "A" && $flt[2] != "B" && $flt[2] != "C" && $flt[2] != "E" && $flt[2] != "F" && $flt[2] != "H" && $flt[2] != "T" && $flt[2] != "U" && $flt[2] != "S" && $flt[2] != "D" && $flt[2] != "R" && $flt[2] != "Q" && $flt[2] != "X" && $flt[2] != "W" && $flt[2] != "Z") //Have to make an exception for these types!
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
	switch ($flt[2])
	{
		case "Q":
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
		$statisticsoutput .= "' /><br />\n"
		."\t\t\t\t\t".$clang->gT("Number Less Than").":<br />\n"
		."\t\t\t\t\t<input type='text' name='$myfield3' value='";
		if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}
		$statisticsoutput .= "' /><br />\n";
		$allfields[]=$myfield2;
		$allfields[]=$myfield3;
		break;
		case "D": // Date
		$myfield2="D$myfield";
		$myfield3="$myfield2=";
		$myfield4="$myfield2<"; $myfield5="$myfield2>";
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
		$query = "SELECT code, title FROM ".db_table_name("labels")." WHERE lid={$flt[6]} AND language='{$language}' ORDER BY sortorder, title";
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
>>>>>>> refs/heads/stable_plus
{
    $selecthide="selected='selected'";
    $selectshow="";
    $selectinc="";
}
elseif (incompleteAnsFilterstate() == "inc")
{
    $selecthide="";
    $selectshow="";
    $selectinc="selected='selected'";
}
else
{
    $selecthide="";
    $selectshow="selected='selected'";
    $selectinc="";
}
$statisticsoutput .="<fieldset style='clear:both;'><legend>".$clang->gT("Data selection")."</legend><ul>";
$statisticsoutput .="<li><label for='filterinc'>".$clang->gT("Include:")."</label><select name='filterinc' id='filterinc'>\n"
."<option value='show' $selectshow>".$clang->gT("All responses")."</option>\n"
."<option value='filter' $selecthide>".$clang->gT("Completed responses only")."</option>\n"
."<option value='incomplete' $selectinc>".$clang->gT("Incomplete responses only")."</option>\n"
."</select></li>\n"

."<li><label for='viewsummaryall'>".$clang->gT("View summary of all available fields")."</label>
                <input type='checkbox' id='viewsummaryall' name='viewsummaryall' ";
if (isset($_POST['viewsummaryall'])) {$statisticsoutput .= "checked='checked'";}
$statisticsoutput.="/></li>";

$statisticsoutput .="<li id='vertical_slide'";
//if ($selecthide!='')
//{
//    $statisticsoutput .= " style='display:none' ";
//}
$statisticsoutput.=" ><label id='noncompletedlbl' for='noncompleted' title='".$clang->gT("Count stats for each question based only on the total number of responses for which the question was displayed")."'>".$clang->gT("Subtotals based on displayed questions")."</label>
                <input type='checkbox' id='noncompleted' name='noncompleted' ";
if (isset($_POST['noncompleted'])) {$statisticsoutput .= "checked='checked'";}
$statisticsoutput.=" />\n</li>\n";

$survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
$survlangs [] = GetBaseLanguageFromSurveyID($surveyid);
$language_options="";
foreach ($survlangs as $survlang)
{
    $language_options .= "\t<option value=\"{$survlang}\"";
    if ($_SESSION['adminlang'] == $survlang)
    {
        $language_options .= "selected=\"selected\" " ;
    }
    $language_options .= ">".getLanguageNameFromCode($survlang,true)."</option>\n";
}

$statisticsoutput .="<li><label for='statlang'>".$clang->gT("Statistics report language")."</label>"
. " <select name=\"statlang\" id=\"statlang\">".$language_options."</select></li>\n";

$statisticsoutput.="\n</ul></fieldset>\n";

$statisticsoutput .= "<fieldset id='left'><legend>".$clang->gT("Response ID")."</legend><ul><li>"
."<label for='idG'>".$clang->gT("Greater than:")."</label>\n"
."<input type='text' id='idG' name='idG' size='10' value='";
if (isset($_POST['idG'])){$statisticsoutput .= sanitize_int($_POST['idG']);}
$statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789')\" /></li><li><label for='idL'>\n"
.$clang->gT("Less than:")."</label>\n"
."<input type='text' id='idL' name='idL' size='10' value='";
if (isset($_POST['idL'])) {$statisticsoutput .= sanitize_int($_POST['idL']);}
$statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789')\" /></li></ul></fieldset>\n";
$statisticsoutput .= "<input type='hidden' name='summary[]' value='idG' />";
$statisticsoutput .= "<input type='hidden' name='summary[]' value='idL' />";


//if the survey contains timestamps you can filter by timestamp, too
if (isset($datestamp) && $datestamp == "Y") {


    $statisticsoutput .= "<fieldset id='right'><legend>".$clang->gT("Submission date")."</legend><ul><li>"
    ."<label for='datestampE'>".$clang->gT("Equals:")."</label>\n"
    ."<input class='popupdate' id='datestampE' name='datestampE' type='text' value='";
    if (isset($_POST['datestampE'])) {$statisticsoutput .= $_POST['datestampE'];}
    $statisticsoutput .= "' /></li><li><label for='datestampG'>\n"
    ."&nbsp;&nbsp;".$clang->gT("Later than:")."</label>\n"
    ."<input class='popupdatetime' id='datestampG' name='datestampG' value='";
    if (isset($_POST['datestampG'])) {$statisticsoutput .= $_POST['datestampG'];}
    $statisticsoutput .= "' type='text' /></li><li><label for='datestampL'> ".$clang->gT("Earlier than:")."</label><input  class='popupdatetime' id='datestampL' name='datestampL' value='";
    if (isset($_POST['datestampL'])) {$statisticsoutput .= $_POST['datestampL'];}
    $statisticsoutput .= "' type='text' /></li></ul></fieldset>\n";
    $statisticsoutput .= "<input type='hidden' name='summary[]' value='datestampE' />";
    $statisticsoutput .= "<input type='hidden' name='summary[]' value='datestampG' />";
    $statisticsoutput .= "<input type='hidden' name='summary[]' value='datestampL' />";

}


$statisticsoutput .="<fieldset><legend>".$clang->gT("Output options")."</legend><ul>"

."<li><label for='usegraph'>".$clang->gT("Show graphs")."</label><input type='checkbox' id='usegraph' name='usegraph' ";
if (isset($_POST['usegraph'])) {$statisticsoutput .= "checked='checked'";}
$statisticsoutput .= "/><br />";
if ($grapherror!='')
{
    $statisticsoutput.="<span id='grapherror' style='display:none'>$grapherror<hr /></span>";
}
$statisticsoutput.="</li>\n";

//Output selector
$statisticsoutput .= "<li>"
."<label>"
.$clang->gT("Select output format").":</label>"
."<input type='radio' name='outputtype' value='html' checked='checked' /><label for='outputtype'>HTML</label> <input type='radio' name='outputtype' value='pdf' /><label for='outputtype'>PDF</label> <input type='radio' onclick='nographs();' name='outputtype' value='xls' /><label for='outputtype'>Excel</label>"
."</li>";

$statisticsoutput .= "</ul></fieldset></div><p>"
."<input type='submit' value='".$clang->gT("View stats")."' />\n"
."<input type='button' value='".$clang->gT("Clear")."' onclick=\"window.open('$scriptname?action=statistics&amp;sid=$surveyid', '_top')\" />\n"
."</p>";

//second row below options -> filter settings headline
$statisticsoutput.="<div class='header header_statistics'>"
."<img src='$imageurl/plus.gif' align='right' id='showfilter' /><img src='$imageurl/minus.gif' align='right' id='hidefilter' />"
.$clang->gT("Response filters")
."</div>\n";

$filterchoice_state=returnglobal('filterchoice_state');
$statisticsoutput.="<input type='hidden' id='filterchoice_state' name='filterchoice_state' value='{$filterchoice_state}' />\n";

<<<<<<< HEAD
$statisticsoutput .="<table cellspacing='0' cellpadding='0' width='100%' id='filterchoices' ";
if ($filterchoice_state!='')
{
    $statisticsoutput .= " style='display:none' ";
=======
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
>>>>>>> refs/heads/stable_plus
}
$statisticsoutput .=">";


<<<<<<< HEAD
/*
 * let's go through the filter array which contains
 * 	['qid'],
 ['gid'],
 ['type'],
 ['title'],
 ['group_name'],
 ['question'],
 ['lid'],
 ['lid1']);
 */

$currentgroup='';
foreach ($filters as $flt)
{
    //is there a previous question type set?
    if (!isset($previousquestiontype)) {$previousquestiontype="";}


    //does gid equal $currentgroup?
    if ($flt[1] != $currentgroup)
    {
        //If the groupname has changed, start a new row
        if ($currentgroup!='')
        {
            //if we've already drawn a table for a group, and we're changing - close off table
            $statisticsoutput .= "<!-- Close filter group --></tr>\n</table></div></td></tr>\n";
        }

        $statisticsoutput .= "\t\t<tr><td><div class='header ui-widget-header'>\n"

        ."<input type=\"checkbox\" id='btn_$flt[1]' onclick=\"selectCheckboxes('grp_$flt[1]', 'summary[]', 'btn_$flt[1]');\" />"

        //use current groupname and groupid as heading
        ."<font size='1'><strong>$flt[4]</strong> (".$clang->gT("Question group")." $flt[1])</font></div></td></tr>\n\t\t"
        ."<tr><td align='center'>\n"
        ."<div id='grp_$flt[1]'><table class='filtertable'><tr>\n";

        //counter which is used to adapt layout depending on counter #
        $counter=0;
    }

    //we don't want more than 4 questions in a row
    //and we need a new row after each multiple/array question
    if (isset($counter) && $counter == 4 ||
    ($previousquestiontype == "1" ||
    $previousquestiontype == "A" ||
    $previousquestiontype == "B" ||
    $previousquestiontype == "C" ||
    $previousquestiontype == "E" ||
    $previousquestiontype == "F" ||
    $previousquestiontype == "H" ||
    $previousquestiontype == "K" ||
    $previousquestiontype == "Q" ||
    $previousquestiontype == "R" ||
    $previousquestiontype == ":" ||
    $previousquestiontype == ";"))
    {
        $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>"; $counter=0;
    }

    /*
     * remember: $flt is structured like this
     *  ['qid'],
     ['gid'],
     ['type'],
     ['title'],
     ['group_name'],
     ['question'],
     ['lid'],
     ['lid1']);
     */

    //SGQ identifier
    $myfield = "{$surveyid}X{$flt[1]}X{$flt[0]}";

    //full question title
    $niceqtext = FlattenText($flt[5]);

    /*
     * Check question type: This question types will be used (all others are separated in the if clause)
     *  5 - 5 Point Choice
     G - Gender
     I - Language Switch
     L - List (Radio)
     M - Multiple choice
     N - Numerical Input
     | - File Upload
     O - List With Comment
     P - Multiple choice with comments
     Y - Yes/No
     ! - List (Dropdown) )
     */
    if ($flt[2]=='M' || $flt[2]=='P' || $flt[2]=='N' || $flt[2]=='L' || $flt[2]=='5'
     || $flt[2]=='G' || $flt[2]=='I' || $flt[2]=='O' || $flt[2]=='Y' || $flt[2]=='!') //Have to make an exception for these types!
    {

        $statisticsoutput .= "\t\t\t\t<td align='center'>";

        //Multiple choice:
        if ($flt[2] == "M") {$myfield = "M$myfield";}
        if ($flt[2] == "P") {$myfield = "P$myfield";}

        // File Upload will need special filters in future, hence the special treatment
        if ($flt[2] == "|") {$myfield = "|$myfield";}

        //numerical input will get special treatment (arihtmetic mean, standard derivation, ...)
        if ($flt[2] == "N") {$myfield = "N$myfield";}
        $statisticsoutput .= "<input type='checkbox'  id='filter$myfield' name='summary[]' value='$myfield'";

        /*
         * one of these conditions has to be true
         * 1. SGQ can be found within the summary array
         * 2. M-SGQ can be found within the summary array (M = Multiple choice)
         * 3. N-SGQ can be found within the summary array (N = numerical input)
         *
         * Always remember that we just have very few question types that are checked here
         * due to the if ouside this section!
         *
         * Auto-check the question types mentioned above
         */
        if (isset($summary) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE
            || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE
            || array_search("P{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE
            || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
        {$statisticsoutput .= " checked='checked'";}

        //show speaker symbol which contains full question text
        $statisticsoutput .= " /><label for='filter$myfield'>".showspeaker(FlattenText($flt[5]))
        ."</label><br />\n";

        //numerical question type -> add some HTML to the output
        //if ($flt[2] == "N") {$statisticsoutput .= "</font>";}		//removed to correct font error
        if ($flt[2] != "N" && $flt[2] != "|") {$statisticsoutput .= "\t\t\t\t<select name='";}

        //Multiple choice ("M"/"P") -> add "M" to output
        if ($flt[2] == "M" ) {$statisticsoutput .= "M";}
        if ($flt[2] == "P" ) {$statisticsoutput .= "P";}

        //numerical -> add SGQ to output
        if ($flt[2] != "N" && $flt[2] != "|") {$statisticsoutput .= "{$surveyid}X{$flt[1]}X{$flt[0]}[]' multiple='multiple'>\n";}

    }	//end if -> filter certain question types

    $statisticsoutput .= "\t\t\t\t\t<!-- QUESTION TYPE = $flt[2] -->\n";
    /////////////////////////////////////////////////////////////////////////////////////////////////
    //This section presents the filter list, in various different ways depending on the question type
    /////////////////////////////////////////////////////////////////////////////////////////////////

    //let's switch through the question type for each question
    switch ($flt[2])
    {
        case "K": // Multiple Numerical
            $statisticsoutput .= "\t\t\t\t\t</tr>\n\t\t\t\t\t<tr>\n";

            //get answers
            $query = "SELECT title as code, question as answer FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language = '{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

            //counter is used for layout
            $counter2=0;

            //go through all the (multiple) answers
            while ($row=$result->FetchRow())
            {
                /*
                 * filter form for numerical input
                 * - checkbox
                 * - greater than
                 * - less than
                 */
                $myfield1="K".$myfield.$row[0];
                $myfield2="K{$myfield}".$row[0]."G";
                $myfield3="K{$myfield}".$row[0]."L";
                if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}

                //start new TD
                $statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>";

                //checkbox
                $statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield1'";

                //check SGQA -> do we want to pre-check the checkbox?
                if (isset($summary) && (array_search("K{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}", $summary) !== FALSE))
                {$statisticsoutput .= " checked='checked'";}
                $statisticsoutput .= " />&nbsp;";

                //show speaker
                $statisticsoutput .= showSpeaker($flt[3]." - ".FlattenText($row[1]))."<br />\n";

                //input fields
                $statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Number greater than").":</font><br />\n"
                ."\t\t\t\t\t<input type='text' name='$myfield2' value='";
                if (isset($_POST[$myfield2])){$statisticsoutput .= $_POST[$myfield2];}

                //check number input using JS
                $statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n"
                ."\t\t\t\t\t<font size='1'>".$clang->gT("Number less than").":</font><br />\n"
                ."\t\t\t\t\t<input type='text' name='$myfield3' value='";
                if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}
                $statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n";

                //we added 1 form -> increase counter
                $counter2++;

            }
            break;



        case "Q": // Multiple Short Text

            //new section
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

            //get subqestions
            $query = "SELECT title as code, question as answer FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;

            //loop through all answers
            while ($row = $result->FetchRow())
            {
                //collecting data for output, for details see above (question type "N")

                //we have one input field for each answer
                $myfield2 = "Q".$myfield."$row[0]";
                if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}

                $statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>";
                $statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield2'";
                if (isset($summary) && (array_search("Q{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}", $summary) !== FALSE))
                {$statisticsoutput .= " checked='checked'";}

                $statisticsoutput .= " />&nbsp;";
                $statisticsoutput .= showSpeaker($flt[3]." - ".FlattenText($row[1]))
                ."<br />\n"
                ."\t\t\t\t\t<font size='1'>".$clang->gT("Responses containing").":</font><br />\n"
                ."\t\t\t\t\t<input type='text' name='$myfield2' value='";
                if (isset($_POST[$myfield2]))
                {$statisticsoutput .= $_POST[$myfield2];}

                $statisticsoutput .= "' />"
                ."\t\t\t\t</td>\n";
                $counter2++;
            }
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;



            /*
             * all "free text" types (T, U, S)  get the same prefix ("T")
             */
        case "T": // Long free text
        case "U": // Huge free text

            $myfield2="T$myfield";
            $statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>\n";
            $statisticsoutput .= "\t\t\t\t\t<input type='checkbox'  name='summary[]' value='$myfield2'";
            if (isset($summary) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
            {$statisticsoutput .= " checked='checked'";}

            $statisticsoutput .= " />&nbsp;"
            ."&nbsp;".showSpeaker($niceqtext)
            ."<br />\n"
            ."\t\t\t\t\t<font size='1'>".$clang->gT("Responses containing").":</font><br />\n"
            ."\t\t\t\t\t<textarea name='$myfield2' rows='3' cols='80'>";

            if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}

            $statisticsoutput .= "</textarea>\n"
            ."\t\t\t\t</td>\n";
            break;



        case "S": // Short free text

            $myfield2="T$myfield";
            $statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>";
            $statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield2'";

            if (isset($summary) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
            {$statisticsoutput .= " checked='checked'";}

            $statisticsoutput .= " />&nbsp;"
            ."&nbsp;".showSpeaker($niceqtext)
            ."<br />\n"
            ."\t\t\t\t\t<font size='1'>".$clang->gT("Responses containing").":</font><br />\n"
            ."\t\t\t\t\t<input type='text' name='$myfield2' value='";

            if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}

            $statisticsoutput .= "' />";
            $statisticsoutput .= "\t\t\t\t</td>\n";
            break;



        case "N": // Numerical

            //textfields for greater and less than X
            $myfield2="{$myfield}G";
            $myfield3="{$myfield}L";
            $statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Number greater than").":</font><br />\n"
            ."\t\t\t\t\t<input type='text' name='$myfield2' value='";

            if (isset($_POST[$myfield2])){$statisticsoutput .= $_POST[$myfield2];}

            $statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n"
            ."\t\t\t\t\t<font size='1'>".$clang->gT("Number less than").":</font><br />\n"
            ."\t\t\t\t\t<input type='text' name='$myfield3' value='";

            if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}

            //only numeriacl input allowed -> check using JS
            $statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n";

            //put field names into array

            break;


        case "|": // File Upload

            // Number of files uploaded for greater and less than X
            $myfield2 = "{$myfield}G";
            $myfield3 = "{$myfield}L";
            $statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Number of files greater than").":</font><br />\n"
            ."\t\t\t\t\t<input type='text' name='$myfield2' value='";

            if (isset($_POST[$myfield2])){$statisticsoutput .= $_POST[$myfield2];}

            $statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n"
            ."\t\t\t\t\t<font size='1'>".$clang->gT("Number of files less than").":</font><br />\n"
            ."\t\t\t\t\t<input type='text' name='$myfield3' value='";

            if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}

            //only numeriacl input allowed -> check using JS
            $statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789.,')\" /><br />\n";

            //put field names into array

            break;


            /*
             * DON'T show any statistics for date questions
             * because there aren't any statistics implemented yet!
             *
             * Only filtering by date is possible.
             *
             * See bug report #2539 and
             * feature request #2620
             */
        case "D": // Date

            /*
             * - input name
             * - date equals
             * - date less than
             * - date greater than
             */
            $myfield2="D$myfield";
            $myfield3="$myfield2=";
            $myfield4="$myfield2<";
            $myfield5="$myfield2>";
            $statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>";

            $statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield2'";

            if (isset($summary) && (array_search("D{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
            {$statisticsoutput .= " checked='checked'";}

            $statisticsoutput .= " /><strong>";
            $statisticsoutput .= showSpeaker($niceqtext)
            ."<br />\n"

            ."\t\t\t\t\t<font size='1'>".$clang->gT("Date (YYYY-MM-DD) equals").":<br />\n"
            ."\t\t\t\t\t<input name='$myfield3' type='text' value='";

            if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}

            $statisticsoutput .= "' /><br />\n"
            ."\t\t\t\t\t&nbsp;&nbsp;".$clang->gT("Date is")." >=<br />\n"
            ."\t\t\t\t\t<input name='$myfield4' value='";

            if (isset($_POST[$myfield4])) {$statisticsoutput .= $_POST[$myfield4];}

            $statisticsoutput .= "' type='text' /> <br />"
            .$clang->gT("AND/OR Date is")." <= <br /> <input  name='$myfield5' value='";

            if (isset($_POST[$myfield5])) {$statisticsoutput .= $_POST[$myfield5];}

            $statisticsoutput .= "' type='text' /></font>\n";
            break;



        case "5": // 5 point choice

            //we need a list of 5 entries
            for ($i=1; $i<=5; $i++)
            {
                $statisticsoutput .= "\t\t\t\t\t<option value='$i'";

                //pre-select values which were marked before
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($i, $_POST[$myfield]))
                {$statisticsoutput .= " selected";}

                $statisticsoutput .= ">$i</option>\n";
            }

            //End the select which starts before the CASE statement (around line 411)
            $statisticsoutput .="\t\t\t\t</select>\n";
            break;



        case "G": // Gender
            $statisticsoutput .= "\t\t\t\t\t<option value='F'";

            //pre-select values which were marked before
            if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("F", $_POST[$myfield])) {$statisticsoutput .= " selected";}

            $statisticsoutput .= ">".$clang->gT("Female")."</option>\n";
            $statisticsoutput .= "\t\t\t\t\t<option value='M'";

            //pre-select values which were marked before
            if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("M", $_POST[$myfield])) {$statisticsoutput .= " selected";}

            $statisticsoutput .= ">".$clang->gT("Male")."</option>\n\t\t\t\t</select>\n";
            $statisticsoutput .= "\t\t\t\t</td>\n";
            break;



        case "Y": // Yes\No
            $statisticsoutput .= "\t\t\t\t\t<option value='Y'";

            //pre-select values which were marked before
            if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("Y", $_POST[$myfield])) {$statisticsoutput .= " selected";}

            $statisticsoutput .= ">".$clang->gT("Yes")."</option>\n"
            ."\t\t\t\t\t<option value='N'";

            //pre-select values which were marked before
            if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("N", $_POST[$myfield])) {$statisticsoutput .= " selected";}

            $statisticsoutput .= ">".$clang->gT("No")."</option></select>\n";
            break;



        case "I": // Language
            $survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
            $survlangs[] = GetBaseLanguageFromSurveyID($surveyid);
            foreach ($survlangs  as $availlang)
            {
                $statisticsoutput .= "\t\t\t\t\t<option value='".$availlang."'";

                //pre-select values which were marked before
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($availlang, $_POST[$myfield]))
                {$statisticsoutput .= " selected";}

                $statisticsoutput .= ">".getLanguageNameFromCode($availlang,false)."</option>\n";
            }
            break;



            //----------------------- ARRAYS --------------------------

        case "A": // ARRAY OF 5 POINT CHOICE QUESTIONS

            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

            //get answers
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;

            //check all the results
            while ($row=$result->FetchRow())
            {
                $myfield2 = $myfield.$row[0];
                $statisticsoutput .= "<!-- $myfield2 - ";

                if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}

                $statisticsoutput .= " -->\n";

                if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}

                $statisticsoutput .= "\t\t\t\t<td align='center'>"
                ."<input type='checkbox'  name='summary[]' value='$myfield2'";

                //pre-check
                if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}

                $statisticsoutput .= " />&nbsp;"
                .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                ."<br />\n"
                ."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n";

                //there are always exactly 5 values which have to be listed
                for ($i=1; $i<=5; $i++)
                {
                    $statisticsoutput .= "\t\t\t\t\t<option value='$i'";

                    //pre-select
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {$statisticsoutput .= " selected";}
                    if (isset($_POST[$myfield2]) && $_POST[$myfield2] == $i) {$statisticsoutput .= " selected";}

                    $statisticsoutput .= ">$i</option>\n";
                }

                $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;

                //add this to all the other fields
            }

            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;



            //just like above only a different loop
        case "B": // ARRAY OF 10 POINT CHOICE QUESTIONS
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;
            while ($row=$result->FetchRow())
            {
                $myfield2 = $myfield . "$row[0]";
                $statisticsoutput .= "<!-- $myfield2 - ";

                if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}

                $statisticsoutput .= " -->\n";

                if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}

                $statisticsoutput .= "\t\t\t\t<td align='center'>"; //heading
                $statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield2'";

                if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}

                $statisticsoutput .= " />&nbsp;"
                .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                ."<br />\n"
                ."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n";

                //here wo loop through 10 entries to create a larger output form
                for ($i=1; $i<=10; $i++)
                {
                    $statisticsoutput .= "\t\t\t\t\t<option value='$i'";
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {$statisticsoutput .= " selected";}
                    if (isset($_POST[$myfield2]) && $_POST[$myfield2] == $i) {$statisticsoutput .= " selected";}
                    $statisticsoutput .= ">$i</option>\n";
                }

                $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;
            }

            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;



        case "C": // ARRAY OF YES\No\$clang->gT("Uncertain") QUESTIONS
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

            //get answers
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;

            //loop answers
            while ($row=$result->FetchRow())
            {
                $myfield2 = $myfield . "$row[0]";
                $statisticsoutput .= "<!-- $myfield2 - ";

                if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}

                $statisticsoutput .= " -->\n";

                if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}

                $statisticsoutput .= "\t\t\t\t<td align='center'>"
                ."<input type='checkbox'  name='summary[]' value='$myfield2'";

                if (isset($summary) && array_search($myfield2, $summary)!== FALSE)
                {$statisticsoutput .= " checked='checked'";}

                $statisticsoutput .= " />&nbsp;<strong>"
                .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                ."</strong><br />\n"
                ."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n"
                ."\t\t\t\t\t<option value='Y'";

                //pre-select "yes"
                if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("Y", $_POST[$myfield2])) {$statisticsoutput .= " selected";}

                $statisticsoutput .= ">".$clang->gT("Yes")."</option>\n"
                ."\t\t\t\t\t<option value='U'";

                //pre-select "uncertain"
                if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("U", $_POST[$myfield2])) {$statisticsoutput .= " selected";}

                $statisticsoutput .= ">".$clang->gT("Uncertain")."</option>\n"
                ."\t\t\t\t\t<option value='N'";

                //pre-select "no"
                if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("N", $_POST[$myfield2])) {$statisticsoutput .= " selected";}

                $statisticsoutput .= ">".$clang->gT("No")."</option>\n"
                ."\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;

                //add to array
            }

            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;



            //similiar to the above one
        case "E": // ARRAY OF Increase/Same/Decrease QUESTIONS
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;

            while ($row=$result->FetchRow())
            {
                $myfield2 = $myfield . "$row[0]";
                $statisticsoutput .= "<!-- $myfield2 - ";

                if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}

                $statisticsoutput .= " -->\n";

                if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}

                $statisticsoutput .= "\t\t\t\t<td align='center'>"
                ."<input type='checkbox'  name='summary[]' value='$myfield2'";

                if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}

                $statisticsoutput .= " />&nbsp;<strong>"
                .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                ."</strong><br />\n"
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
            }

            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            break;

        case ";":  //ARRAY (Multi Flex) (Text)
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' AND scale_id=0 ORDER BY question_order";
            $result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;
            while ($row=$result->FetchRow())
            {
                $fquery = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' AND scale_id=1 ORDER BY question_order";
                $fresult = db_execute_assoc($fquery);
                while ($frow = $fresult->FetchRow())
                {
                    $myfield2 = "T".$myfield . $row[0] . "_" . $frow['title'];
                    $statisticsoutput .= "<!-- $myfield2 - ";
                    if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
                    $statisticsoutput .= " -->\n";
                    if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
                    $statisticsoutput .= "\t\t\t\t<td align='center'>"
                    ."<input type='checkbox'  name='summary[]' value='$myfield2'";
                    if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}
                    $statisticsoutput .= " />&nbsp;<strong>"
                    .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1]." [".$frow['question']."]")." - ".$row[0]."/".$frow['title'])
                    ."</strong><br />\n";
                    //$statisticsoutput .= $fquery;
                    $statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Responses containing").":</font><br />\n";
                    $statisticsoutput .= "\t\t\t\t<input type='text' name='{$myfield2}' value='";
                    if(isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
                    $statisticsoutput .= "' />\n\t\t\t\t</td>\n";
                    $counter2++;
                }
            }
            $statisticsoutput .= "\t\t\t\t<td>\n";
            $counter=0;
            break;

        case ":":  //ARRAY (Multi Flex) (Numbers)
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language = '{$language}'  AND scale_id=0 ORDER BY question_order";
            $result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;
            //Get qidattributes for this question
            $qidattributes=getQuestionAttributes($flt[0]);
            if (trim($qidattributes['multiflexible_max'])!='' && trim($qidattributes['multiflexible_min']) ==''){
                $maxvalue=$qidattributes['multiflexible_max'];
                $minvalue=1;
            }
            if (trim($qidattributes['multiflexible_min'])!='' && trim($qidattributes['multiflexible_max']) ==''){
                $minvalue=$qidattributes['multiflexible_min'];
                $maxvalue=$qidattributes['multiflexible_min'] + 10;
            }
            if (trim($qidattributes['multiflexible_min'])=='' && trim($qidattributes['multiflexible_max']) ==''){
                $minvalue=1;
                $maxvalue=10;
            }
            if (trim($qidattributes['multiflexible_min']) !='' && trim($qidattributes['multiflexible_max']) !=''){
                if($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']){
                    $minvalue=$qidattributes['multiflexible_min'];
                    $maxvalue=$qidattributes['multiflexible_max'];
                }
            }

            if (trim($qidattributes['multiflexible_step'])!='') {
                $stepvalue=$qidattributes['multiflexible_step'];
            } else {
                $stepvalue=1;
            }
            if ($qidattributes['multiflexible_checkbox']!=0)
            {
                $minvalue=0;
                $maxvalue=1;
                $stepvalue=1;
            }
            while ($row=$result->FetchRow())
            {
                $fquery = "SELECT * FROM ".db_table_name("questions")." WHERE parent_qid={$flt[0]} AND language='{$language}' AND scale_id=1 ORDER BY question_order, title";
                $fresult = db_execute_assoc($fquery);
                while ($frow = $fresult->FetchRow())
                {
                    $myfield2 = $myfield . $row[0] . "_" . $frow['title'];
                    $statisticsoutput .= "<!-- $myfield2 - ";
                    if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
                    $statisticsoutput .= " -->\n";
                    if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
                    $statisticsoutput .= "\t\t\t\t<td align='center'>"
                    ."<input type='checkbox'  name='summary[]' value='$myfield2'";
                    if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}
                    $statisticsoutput .= " />&nbsp;<strong>"
                    .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1]." [".$frow['question']."]")." - ".$row[0]."/".$frow['title'])
                    ."</strong><br />\n";
                    //$statisticsoutput .= $fquery;
                    $statisticsoutput .= "\t\t\t\t<select name='{$myfield2}[]' multiple='multiple' rows='5' cols='5'>\n";
                    for($ii=$minvalue; $ii<=$maxvalue; $ii+=$stepvalue)
                    {
                        $statisticsoutput .= "\t\t\t\t\t<option value='$ii'";
                        if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {$statisticsoutput .= " selected";}
                        $statisticsoutput .= ">$ii</option>\n";
                    }
                    $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                    $counter2++;
                }
            }
            $statisticsoutput .= "\t\t\t\t<td>\n";
            $counter=0;
            break;
            /*
             * For question type "F" and "H" you can use labels.
             * The only difference is that the labels are applied to column heading
             * or rows respectively
             */
        case "F": // FlEXIBLE ARRAY
        case "H": // ARRAY (By Column)
            //$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

            //Get answers. We always use the answer code because the label might be too long elsewise
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
            $counter2=0;

            //check all the answers
            while ($row=$result->FetchRow())
            {
                $myfield2 = $myfield . "$row[0]";
                $statisticsoutput .= "<!-- $myfield2 - ";

                if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}

                $statisticsoutput .= " -->\n";

                if ($counter2 == 4)
                {
                    $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
                    $counter2=0;
                }

                $statisticsoutput .= "\t\t\t\t<td align='center'>"
                ."<input type='checkbox'  name='summary[]' value='$myfield2'";

                if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}

                $statisticsoutput .= " />&nbsp;<strong>"
                .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                ."</strong><br />\n";

                /*
                 * when hoovering the speaker symbol we show the whole question
                 *
                 * flt[6] is the label ID
                 *
                 * table "labels" contains
                 * - lid
                 * - code
                 * - title
                 * - sortorder
                 * - language
                 */
                $fquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$flt[0]} AND language='{$language}' ORDER BY sortorder, code";
                $fresult = db_execute_assoc($fquery);

                //for debugging only:
                //$statisticsoutput .= $fquery;

                //creating form
                $statisticsoutput .= "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple='multiple'>\n";

                //loop through all possible answers
                while ($frow = $fresult->FetchRow())
                {
                    $statisticsoutput .= "\t\t\t\t\t<option value='{$frow['code']}'";

                    //pre-select
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {$statisticsoutput .= " selected";}

                    $statisticsoutput .= ">({$frow['code']}) ".FlattenText($frow['answer'])."</option>\n";
                }

                $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;

                //add fields to main array
            }

            //$statisticsoutput .= "\t\t\t\t<td>\n";
            $counter=0;
            break;



        case "R": //RANKING

            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

            //get some answers
            $query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
            $result = db_execute_assoc($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

            //get number of answers
            $count = $result->RecordCount();

            //lets put the answer code and text into the answers array
            while ($row = $result->FetchRow())
            {
                $answers[]=array($row['code'], $row['answer']);
            }

            $counter2=0;

            //loop through all answers. if there are 3 items to rate there will be 3 statistics
            for ($i=1; $i<=$count; $i++)
            {
                //adjust layout depending on counter
                if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter=0;}

                //myfield is the SGQ identifier
                //myfield2 is just used as comment in HTML like "R40X34X1721-1"
                $myfield2 = "R" . $myfield . $i . "-" . strlen($i);
                $myfield3 = $myfield . $i;
                $statisticsoutput .= "<!-- $myfield2 - ";

                if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}

                $statisticsoutput .= " -->\n"
                ."\t\t\t\t<td align='center'>"
                ."<input type='checkbox'  name='summary[]' value='$myfield2'";

                //pre-check
                if (isset($summary) && array_search($myfield2, $summary) !== FALSE) {$statisticsoutput .= " checked='checked'";}

                $statisticsoutput .= " />&nbsp;<strong>"
                .showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
                ."</strong><br />\n"
                ."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$i}[]' multiple='multiple'>\n";

                //output lists of ranking items
                foreach ($answers as $ans)
                {
                    $statisticsoutput .= "\t\t\t\t\t<option value='$ans[0]'";

                    //pre-select
                    if (isset($_POST[$myfield3]) && is_array($_POST[$myfield3]) && in_array("$ans[0]", $_POST[$myfield3])) {$statisticsoutput .= " selected";}

                    $statisticsoutput .= ">$ans[1]</option>\n";
                }

                $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;

                //add averything to main array
            }

            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

            //Link to rankwinner script - awaiting completion - probably never gonna happen. Mystery creator.
            //          $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr bgcolor='#DDDDDD'>\n"
            //              ."<td colspan=$count align=center>"
            //              ."<input type='button' value='Show Rank Summary' onclick=\"window.open('rankwinner.php?sid=$surveyid&amp;qid=$flt[0]', '_blank', 'toolbar=no, directories=no, location=no, status=yes, menubar=no, resizable=no, scrollbars=no, width=400, height=300, left=100, top=100')\">"
            //              ."</td></tr>\n\t\t\t\t<tr>\n";
            $counter=0;
            unset($answers);
            break;

            //Boilerplate questions are only used to put some text between other questions -> no analysis needed
        case "X": //This is a boilerplate question and it has no business in this script
            $statisticsoutput .= "\t\t\t\t<td></td>";
            break;

        case "1": // MULTI SCALE
            $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

            //special dual scale counter
            $counter2=0;

            //get answers
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

            //loop through answers
            while ($row=$result->FetchRow())
            {

                //----------------- LABEL 1 ---------------------
                //myfield2 = answer code.
                $myfield2 = $myfield . "$row[0]#0";

                //3 lines of debugging output
                $statisticsoutput .= "<!-- $myfield2 - ";
                if (isset($_POST[$myfield2]))
                {
                    $statisticsoutput .= $_POST[$myfield2];
                }
                $statisticsoutput .= " -->\n";

                //some layout adaptions -> new line after 4 entries
                if ($counter2 == 4)
                {
                    $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
                    $counter2=0;
                }

                //output checkbox and question/label text
                $statisticsoutput .= "\t\t\t\t<td align='center'>";
                $statisticsoutput .= "<input type='checkbox' name='summary[]' value='$myfield2'";

                //pre-check
                if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}

                //check if there is a dualscale_headerA/B
                $dshquery = "SELECT value FROM ".db_table_name("question_attributes")." WHERE qid={$flt[0]} AND attribute='dualscale_headerA'";
                $dshresult = db_execute_num($dshquery) or safe_die ("Couldn't get dualscale header!<br />$dshquery<br />".$connect->ErrorMsg());

                //get header
                while($dshrow=$dshresult->FetchRow())
                {
                    $dualscaleheadera = $dshrow[0];
                }

                if(isset($dualscaleheadera) && $dualscaleheadera != "")
                {
                    $labeltitle = $dualscaleheadera;
                }
=======
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
			$nquery = "SELECT title, type, question, other FROM ".db_table_name("questions")." WHERE qid='".substr($qqid, 0, $qidlength)."' AND language='{$language}'";
			$nresult = db_execute_num($nquery) or die("Couldn't get text question<br />$nquery<br />".$connect->ErrorMsg());
			$count = substr($qqid, strlen($qqid)-1);
			while ($nrow=$nresult->FetchRow())
			{
				$qtitle=$nrow[0].'-'.$count; $qtype=$nrow[1];
				$qquestion=strip_tags($nrow[2]);
			}
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
		elseif (substr($rt, 0, 1) == "N") //NUMERICAL TYPE
		{
			if (substr($rt, -1) == "G" || substr($rt, -1) == "L" || substr($rt, -1) == "=")
			{
				//DO NUSSINK
			}
			else
			{
				list($qsid, $qgid, $qqid) = explode("X", $rt, 3);
				$nquery = "SELECT title, type, question, qid, lid FROM ".db_table_name("questions")." WHERE qid='$qqid' AND language='{$language}'";
				$nresult = db_execute_num($nquery) or die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());
				while ($nrow=$nresult->FetchRow()) {$qtitle=$nrow[0]; $qtype=$nrow[1]; $qquestion=strip_tags($nrow[2]); $qiqid=$nrow[3]; $qlid=$nrow[4];}
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
>>>>>>> refs/heads/stable_plus
                else
                {
                    $labeltitle='';
                }

                $statisticsoutput .= " />&nbsp;<strong>"
                .showSpeaker($niceqtext." [".str_replace("'", "`", $row[1])."] - ".$clang->gT("Label").": ".$labeltitle)
                ."</strong><br />\n";

                /* get labels
                 * table "labels" contains
                 * - lid
                 * - code
                 * - title
                 * - sortorder
                 * - language
                 */

                $fquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$flt[0]} AND language='{$language}' and scale_id=0 ORDER BY sortorder, code";
                $fresult = db_execute_assoc($fquery);

                //this is for debugging only
                //$statisticsoutput .= $fquery;

                $statisticsoutput .= "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}#{0}[]' multiple='multiple'>\n";

                //list answers
                while ($frow = $fresult->FetchRow())
                {
                    $statisticsoutput .= "\t\t\t\t\t<option value='{$frow['code']}'";

                    //pre-check
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {$statisticsoutput .= " selected";}

                    $statisticsoutput .= ">({$frow['code']}) ".FlattenText($frow['answer'])."</option>\n";

                }

                $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;




                //----------------- LABEL 2 ---------------------

                //myfield2 = answer code
                $myfield2 = $myfield . "$row[0]#1";

                //3 lines of debugging output
                $statisticsoutput .= "<!-- $myfield2 - ";
                if (isset($_POST[$myfield2]))
                {
                    $statisticsoutput .= $_POST[$myfield2];
                }

                $statisticsoutput .= " -->\n";

                //some layout adaptions -> new line after 4 entries
                if ($counter2 == 4)
                {
                    $statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
                    $counter2=0;
                }

                //output checkbox and question/label text
                $statisticsoutput .= "\t\t\t\t<td align='center'>";
                $statisticsoutput .= "<input type='checkbox' name='summary[]' value='$myfield2'";

                //pre-check
                if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}

                //check if there is a dualsclae_headerA/B
                $dshquery2 = "SELECT value FROM ".db_table_name("question_attributes")." WHERE qid={$flt[0]} AND attribute='dualscale_headerB'";
                $dshresult2 = db_execute_num($dshquery2) or safe_die ("Couldn't get dualscale header!<br />$dshquery2<br />".$connect->ErrorMsg());

                //get header
                while($dshrow2=$dshresult2->FetchRow())
                {
                    $dualscaleheaderb = $dshrow2[0];
                }

                if(isset($dualscaleheaderb) && $dualscaleheaderb != "")
                {
                    $labeltitle2 = $dualscaleheaderb;
                }
                else
                {
                    //get label text

                    $labeltitle2 = '';
                }

                $statisticsoutput .= " />&nbsp;<strong>"
                .showSpeaker($niceqtext." [".str_replace("'", "`", $row[1])."] - ".$clang->gT("Label").": ".$labeltitle2)
                ."</strong><br />\n";

                $fquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$flt[0]} AND language='{$language}' and scale_id=1 ORDER BY sortorder, code";
                $fresult = db_execute_assoc($fquery);

<<<<<<< HEAD
                //this is for debugging only
                //$statisticsoutput .= $fquery;

                $statisticsoutput .= "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}#{1}[]' multiple='multiple'>\n";

                //list answers
                while ($frow = $fresult->FetchRow())
                {
                    $statisticsoutput .= "\t\t\t\t\t<option value='{$frow['code']}'";

                    //pre-check
                    if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {$statisticsoutput .= " selected";}
=======
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
				while ($row=$result->FetchRow())
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
					$statisticsoutput .= "\t<tr>\n\t\t<td width='50%' align='center' >"
					."$fname\n"
					."\t\t</td>\n"
					."\t\t<td width='25%' align='center' >$row[0]\n";
					if ($results > 0) {$vp=sprintf("%01.2f", ($row[0]/$results)*100)."%";} else {$vp="N/A";}
					$statisticsoutput .= "\t\t</td><td width='25%' align='center' >$vp"
					."\t\t</td>\n\t</tr>\n";
					if ($results > 0)
					{
						$gdata[] = ($row[0]/$results)*100;
					} else
					{
						$gdata[] = 0;
					}
					$grawdata[]=$row[0];
					$label=strip_tags($fname);
					$justcode[]=$al[0];
					$lbl[] = wordwrap($label, 20, "\n");
				}
			}
            if (incompleteAnsFilterstate() === false)
            {
                $TotalIncomplete = $results - $TotalCompleted;
                $fname=$clang->gT("Non completed");
                $statisticsoutput .= "\t<tr>\n\t\t<td width='50%' align='center' >"
                ."$fname\n"
                ."\t\t</td>\n"
                ."\t\t<td width='25%' align='center' >$TotalIncomplete\n";
                if ($results > 0) {$vp=sprintf("%01.2f", ($TotalIncomplete/$results)*100)."%";} else {$vp="N/A";}
                $statisticsoutput .= "\t\t</td><td width='25%' align='center' >$vp"
                ."\t\t</td>\n\t</tr>\n";
                if ($results > 0)
                {
                    $gdata[] = ($TotalIncomplete/$results)*100;
                } else
                {
                    $gdata[] = 0;
                }
                $grawdata[]=$TotalIncomplete;
                $label=strip_tags($fname);
                $justcode[]=$fname;
                $lbl[] = wordwrap($label, 20, "\n");
            }

			if ($usejpgraph == 1 && array_sum($gdata)>0) //JPGRAPH CODING ORIGINALLY SUBMITTED BY Pieterjan Heyse
			{
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
					$totallines=countLines($lbl);
					if ($totallines>26) {
						$gheight=320+(6.7*($totallines-26));
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
//    				$graph->SetAntiAliasing();
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
>>>>>>> refs/heads/stable_plus

                    $statisticsoutput .= ">({$frow['code']}) ".FlattenText($frow['answer'])."</option>\n";

                }

                $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
                $counter2++;

            }	//end WHILE -> loop through all answers

            $statisticsoutput .= "\t\t\t\t<td>\n";


            $counter=0;
            break;

        case "P":  //P - Multiple choice with comments
        case "M":  //M - Multiple choice

            //get answers
            $query = "SELECT title, question FROM ".db_table_name("questions")." WHERE parent_qid='$flt[0]' AND language='{$language}' ORDER BY question_order";
            $result = db_execute_num($query) or safe_die("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

            //loop through answers
            while ($row=$result->FetchRow())
            {
                $statisticsoutput .= "\t\t\t\t\t\t<option value='{$row[0]}'";

                //pre-check
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {$statisticsoutput .= " selected";}

                $statisticsoutput .= '>'.FlattenText($row[1])."</option>\n";
            }

            $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
            break;


            /*
             * This question types use the default settings:
             * 	L - List (Radio)
             O - List With Comment
             P - Multiple choice with comments
             ! - List (Dropdown)
             */
        default:

            //get answers
            $query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
            $result = db_execute_num($query) or safe_die("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

            //loop through answers
            while ($row=$result->FetchRow())
            {
                $statisticsoutput .= "\t\t\t\t\t\t<option value='{$row[0]}'";

                //pre-check
                if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {$statisticsoutput .= " selected";}

                $statisticsoutput .= '>'.FlattenText($row[1])."</option>\n";
            }

            $statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
            break;

    }	//end switch -> check question types and create filter forms

    $currentgroup=$flt[1];

    if (!isset($counter)) {$counter=0;}
    $counter++;

    //temporary save the type of the previous question
    //used to adjust linebreaks
    $previousquestiontype = $flt[2];

    //Group close
    //$statisticsoutput .= "\n\t\t\t\t<!-- --></tr>\n\t\t\t</table></div></td></tr>\n";
}

//complete output
$statisticsoutput .= "\n\t\t\t\t</tr>\n";






//add last lines to filter forms
$statisticsoutput .= "\t\t\t</table></div>\n"
."\t\t</td></tr>\n";


//add line to separate the the filters from the other options
$statisticsoutput .= "<tr class='statistics-tbl-separator'><td></td></tr>";

$statisticsoutput .= "</table>";




//very last lines of output
$statisticsoutput .= "\t\t<p id='vertical_slide2'>\n"
."\t\t\t<input type='submit' value='".$clang->gT("View stats")."' />\n"
."\t\t\t<input type='button' value='".$clang->gT("Clear")."' onclick=\"window.open('$scriptname?action=statistics&amp;sid=$surveyid', '_top')\" />\n"
."\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
."\t\t<input type='hidden' name='display' value='stats' />\n"
."\t\t</p>\n"
."\t</form><br /><a name='start'></a>\n";

// ----------------------------------- END FILTER FORM ---------------------------------------


//Show Summary results
if (isset($summary) && $summary)
{
    if(isset($_POST['usegraph']))
    {
        $usegraph = 1;
    }
    else
    {
        $usegraph = 0;
    }
    include_once("statistics_function.php");
    $outputType = $_POST['outputtype'];
    switch($outputType){

        case 'html':
            $statisticsoutput .= generate_statistics($surveyid,$summary,$summary,$usegraph,$outputType,'DD',$statlang);
            break;
        case 'pdf':
            generate_statistics($surveyid,$summary,$summary,$usegraph,$outputType,'I',$statlang);
            exit;
            break;
        case 'xls':
            generate_statistics($surveyid,$summary,$summary,$usegraph,$outputType,'DD',$statlang);
            exit;
            break;
        default:

            break;

    }

    //print_r($summary); exit;

}	//end if -> show summary results

function showSpeaker($hinttext)
{
    global $clang, $imageurl, $maxchars;

    if(!isset($maxchars))
    {
        $maxchars = 100;
    }
    $htmlhinttext=str_replace("'",'&#039;',$hinttext);  //the string is already HTML except for single quotes so we just replace these only
    $jshinttext=javascript_escape($hinttext,true,true);

    if(strlen($hinttext) > ($maxchars))
    {
        $shortstring = FlattenText($hinttext);

        $shortstring = htmlspecialchars(mb_strcut(html_entity_decode($shortstring,ENT_QUOTES,'UTF-8'), 0, $maxchars, 'UTF-8'));

        //output with hoover effect
        $reshtml= "<span style='cursor: hand' title='".$htmlhinttext."' "
        ." onclick=\"alert('".$clang->gT("Question","js").": $jshinttext')\">"
        ." \"$shortstring...\" </span>"
        ."<img style='cursor: hand' src='$imageurl/speaker.png' align='bottom' alt='$htmlhinttext' title='$htmlhinttext' "
        ." onclick=\"alert('".$clang->gT("Question","js").": $jshinttext')\" />";
    }
    else
    {
        $reshtml= "<span title='".$htmlhinttext."'> \"$htmlhinttext\"</span>";
    }
    return $reshtml;
}

////simple function to square a value
//function square($number)
//{
//	if($number == 0)
//	{
//		$squarenumber = 0;
//	}
//	else
//	{
//		$squarenumber = $number * $number;
//	}
//
//	return $squarenumber;
//}

?>
