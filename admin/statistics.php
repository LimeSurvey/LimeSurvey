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
 *
 */

/*
 * We need this later:
 *  1 - Array (Flexible Labels) Dual Scale ),
 5 - 5 Point Choice
 A - Array (5 Point Choice)
 B - Array (10 Point Choice)
 C - Array (Yes/No/Uncertain)
 D - Date
 E - Array (Increase, Same, Decrease)
 F - Array (Flexible Labels)
 G - Gender
 H - Array (Flexible Labels) by Column
 I - Language Switch
 K - Multiple Numerical Input
 L - List (Radio)
 M - Multiple Options
 N - Numerical Input
 O - List With Comment
 P - Multiple Options With Comments
 Q - Multiple Short Text
 R - Ranking
 S - Short Free Text
 T - Long Free Text
 U - Huge Free Text
 W - List (Flexible Labels) (Dropdown)
 X - Boilerplate Question
 Y - Yes/No
 Z - List (Flexible Labels) (Radio)
 ! - List (Dropdown)
 : - Array (Flexible Labels) multiple drop down
 ; - Array (Flexible Labels) multiple texts


 Debugging help:
 echo '<script language="javascript" type="text/javascript">alert("HI");</script>';
 */

//split up results to extend statistics -> NOT WORKING YET! DO NOT ENABLE THIS!
$showcombinedresults = 0;

/*
 * this variable is used in the function shortencode() which cuts off a question/answer title
 * after $maxchars and shows the rest as tooltip
 */
$maxchars = 13;



//don't call this script directly!
if (isset($_REQUEST['homedir'])) {die('You cannot start this script directly');}

//some includes, the progressbar is used to show a progressbar while generating the graphs
include_once("login_check.php");
require_once('classes/core/class.progressbar.php');

//we collect all the output within this variable
$statisticsoutput ='';

//output for chosing questions to cross query
$cr_statisticsoutput = '';

//for creating graphs we need some more scripts which are included here
if (isset($_POST['usegraph']))
{
	require_once('../classes/pchart/pchart/pChart.class');
	require_once('../classes/pchart/pchart/pData.class');
	require_once('../classes/pchart/pchart/pCache.class');

	$MyCache = new pCache($tempdir.'/');
}

// This gets all the 'to be shown questions' from the POST and puts these into an array
$summary=returnglobal('summary');

//if $summary isn't an array we create one
if (isset($summary) && !is_array($summary)) {
	$summary = explode("+", $summary);
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

// Set language for questions and labels to base language of this survey
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
//$statisticsoutput .= "<table width='99%' class='menubar' cellpadding='1' cellspacing='0'>\n"
//."\t<tr'><td colspan='2' height='4'><font size='1'><strong>".$clang->gT("Quick Statistics")."</strong></font></td></tr>\n";
//Get the menubar
$statisticsoutput .= browsemenubar($clang->gT("Quick statistics"))


//second row below options -> filter settings headline
."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1'"
." cellspacing='0'>\n"
."<tr><td align='center' class='settingcaption' height='22'>"
."<input type='image' src='$imagefiles/plus.gif' align='right' id='showfilter' /><input type='image' src='$imagefiles/minus.gif' align='right' id='hidefilter' />"
.$clang->gT("Filter settings")
."</td></tr>\n"
."</table>\n"

//we need a form which can pass the selected data later
."<form method='post' name='formbuilder' action='$scriptname?action=statistics'>\n"

//table which holds all the filter forms
."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";


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
$query = "SELECT ".db_table_name("questions").".*, group_name, group_order\n"
."FROM ".db_table_name("questions").", ".db_table_name("groups")."\n"
."WHERE ".db_table_name("groups").".gid=".db_table_name("questions").".gid\n"
."AND ".db_table_name("groups").".language='".$language."'\n"
."AND ".db_table_name("questions").".language='".$language."'\n"
."AND ".db_table_name("questions").".sid=$surveyid";
$result = db_execute_assoc($query) or safe_die("Couldn't do it!<br />$query<br />".$connect->ErrorMsg());

//store all the data in $rows
$rows = $result->GetRows();

//SORT IN NATURAL ORDER!
usort($rows, 'CompareGroupThenTitle');

//put the question information into the filter array
foreach ($rows as $row)
{
	//store some column names in $filters array
	$filters[]=array($row['qid'],
	$row['gid'],
	$row['type'],
	$row['title'],
	$row['group_name'],
	FlattenText($row['question']),
	$row['lid'],
	$row['lid1']);
}

//var_dump($filters);
// SHOW ID FIELD

//some more output: I = filter by ID
//{VIEWALL} is a placemarker and is replaced by the html to choose to view all answers. Later there is a str_replace
// to insert this code into this top section
$statisticsoutput .= "\t\t<tr><td align='center'><div id='filtersettings'";

if (isset($_POST['display']) && $_POST['display'])  
{
    $statisticsoutput .=' style="display:none;" ';
}




$statisticsoutput .= ">
                       <table cellspacing='0' cellpadding='0' width='100%'>{VIEWALL}</table>
                       <table cellspacing='0' cellpadding='0' width='100%' id='filterchoices'>
	                     <tr><td align='center' class='settingcaption'>
	                       <font size='1' face='verdana'>".$clang->gT("General Filters")."</font>
		                  </td></tr>
                         <tr><td>
                        <table align='center'><tr>\n";

$myfield = "id";
$myfield2=$myfield."G";	//greater than field
$myfield3=$myfield."L";	//less than field
$myfield4=$myfield."=";	//equals field
$statisticsoutput .= "<td align='center'><strong>".$clang->gT("ID")."</strong><br />";
$statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Number greater than").":<br />\n"
."\t\t\t\t\t<input type='text' name='$myfield2' value='";
if (isset($_POST[$myfield2])){$statisticsoutput .= $_POST[$myfield2];}
$statisticsoutput .= "' onkeypress=\"return goodchars(event,'0123456789')\" /><br />\n"
."\t\t\t\t\t".$clang->gT("Number less than").":<br />\n"
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

//if the survey contains timestamps you can filter by timestamp, too
if (isset($datestamp) && $datestamp == "Y") {
	$myfield = "datestamp";		//timestamp equals
	$myfield2 = "datestampG";	//timestamp greater than
	$myfield3 = "datestampL";	//timestamp less than
	$myfield2="$myfield";
	$myfield3="$myfield2=";
	$myfield4="$myfield2<";
	$myfield5="$myfield2>";

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
	$statisticsoutput .= "' type='text' /> <br />".$clang->gT("and")."<br /> <input  name='$myfield5' value='";
	if (isset($_POST[$myfield5])) {$statisticsoutput .= $_POST[$myfield5];}
	$statisticsoutput .= "' type='text' /></font></td>\n";
	$allfields[]=$myfield2;
	$allfields[]=$myfield3;
	$allfields[]=$myfield4;
	$allfields[]=$myfield5;
}

$statisticsoutput .= "</tr></table></td></tr>";	//close table with filter by ID or timestamp forms




// 2: Get answers for each question

//is there a currentgroup set?
if (!isset($currentgroup)) {$currentgroup="";}

//is there a previous question type set?
if (!isset($previousquestiontype)) {$previousquestiontype="";}


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
foreach ($filters as $flt)
{
	//is there a previous question type set?
	if (!isset($previousquestiontype)) {$previousquestiontype="";}


	//does gid equal $currentgroup?
	if ($flt[1] != $currentgroup)
	{
		//If the groupname has changed, start a new row
		if ($currentgroup)
		{
			//if we've already drawn a table for a group, and we're changing - close off table
			$statisticsoutput .= "\n\t\t\t\t<!-- --></tr>\n\t\t\t</table></div></td></tr>\n";
		}
		
//$statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield'";
		$statisticsoutput .= "\t\t<tr><td align='center' class='settingcaption'>\n"
		
		//."<input type='button' name='btn_$flt[1]' onclick='selectAll(grp_$flt[1]);' />"
		
		."<input type=\"checkbox\" id='btn_$flt[1]' onclick=\"selectCheckboxes('grp_$flt[1]', 'summary[]', 'btn_$flt[1]');\">"
		
		//use current groupname and groupid as heading
		."\t\t<font size='1'><strong>$flt[4]</strong> (".$clang->gT("Question group")." $flt[1])</font></td></tr>\n\t\t"
		."<tr><td align='center'>\n"
		."\t\t\t<div id='grp_$flt[1]'><table class='statisticstable'><tr>\n";

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
		M - Multiple Options
		N - Numerical Input
		O - List With Comment
		P - Multiple Options With Comments
		Y - Yes/No
		! - List (Dropdown) )
	 */
	if ($flt[2] != "A" && $flt[2] != "B" && $flt[2] != "C" && $flt[2] != "E" &&
	$flt[2] != "F" && $flt[2] != "H" && $flt[2] != "T" && $flt[2] != "U" &&
	$flt[2] != "S" && $flt[2] != "D" && $flt[2] != "R" && $flt[2] != "Q" && $flt[2] != "1" &&
	$flt[2] != "X" && $flt[2] != "W" && $flt[2] != "Z" && $flt[2] != "K" &&
	$flt[2] != ":" && $flt[2] != ";") //Have to make an exception for these types!
	{

		$statisticsoutput .= "\t\t\t\t<td align='center'>";

		//multiple options:
		if ($flt[2] == "M" || $flt[2] == "P") {$myfield = "M$myfield";}

		//numerical input will get special treatment (arihtmetic mean, standard derivation, ...)
		if ($flt[2] == "N") {$myfield = "N$myfield";}
		$statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield'";

		/*
		 * one of these conditions has to be true
		 * 1. SGQ can be found within the summary array
		 * 2. M-SGQ can be found within the summary array (M = multiple options)
		 * 3. N-SGQ can be found within the summary array (N = numerical input)
		 *
		 * Always remember that we just have very few question types that are checked here
		 * due to the if ouside this section!
		 *
		 * Auto-check the question types mentioned above
		 */
		if (isset($summary) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE  || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
		{$statisticsoutput .= " checked='checked'";}

		//show speaker symbol which contains full question text
		$statisticsoutput .= " /><strong>".showspeaker(FlattenText($flt[5]))."</strong>"
		."<br />\n";

		//numerical question type -> add some HTML to the output
		//if ($flt[2] == "N") {$statisticsoutput .= "</font>";}		//removed to correct font error
		if ($flt[2] != "N") {$statisticsoutput .= "\t\t\t\t<select name='";}

		//multiple options ("M"/"P") -> add "M" to output
		if ($flt[2] == "M" || $flt[2] == "P") {$statisticsoutput .= "M";}

		//numerical -> add SGQ to output
		if ($flt[2] != "N") {$statisticsoutput .= "{$surveyid}X{$flt[1]}X{$flt[0]}[]' multiple='multiple'>\n";}

		//Add the field name into the allfields array, which is used later to know which are the available fields for selection
		$allfields[]=$myfield;

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
			$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language = '{$language}' ORDER BY sortorder, answer";
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
				$statisticsoutput .= " />&nbsp;<strong>";
					
				//show speaker
				$statisticsoutput .= showSpeaker($flt[3]." - ".FlattenText($row[1]))."</strong><br />\n";

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
					
				//add fields to array which contains all fields names
				$allfields[]=$myfield1;
				$allfields[]=$myfield2;
				$allfields[]=$myfield3;
			}
			break;



		case "Q": // Multiple Short Text
				
			//new section
			$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

			//get answers//XXX
			$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
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
					
				$statisticsoutput .= " />&nbsp;<strong>";
				$statisticsoutput .= showSpeaker($flt[3]." - ".FlattenText($row[1]))
				."</strong><br />\n"
				."\t\t\t\t\t<font size='1'>".$clang->gT("Responses containing").":</font><br />\n"
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



			/*
			 * all "free text" types (T, U, S)  get the same prefix ("T")
			 */
		case "T": // Long free text
		case "U": // Huge free text
				
			$myfield2="T$myfield";
			$statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>";
			$statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield2'";
			if (isset($summary) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
			{$statisticsoutput .= " checked='checked'";}

			$statisticsoutput .= " />&nbsp;<strong>"
			."&nbsp;".showSpeaker($niceqtext)
			."</strong><br />\n"
			."\t\t\t\t\t<font size='1'>".$clang->gT("Responses containing").":</font><br />\n"
			."\t\t\t\t\t<textarea name='$myfield2' rows='3' cols='80'>";

			if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}

			$statisticsoutput .= "</textarea>";
			$allfields[]=$myfield2;
			break;



		case "S": // Short free text
				
			$myfield2="T$myfield";
			$statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>";
			$statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield2'";

			if (isset($summary) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
			{$statisticsoutput .= " checked='checked'";}

			$statisticsoutput .= " />&nbsp;<strong>"
			."&nbsp;".showSpeaker($niceqtext)
			."</strong><br />\n"
			."\t\t\t\t\t<font size='1'>".$clang->gT("Responses containing").":</font><br />\n"
			."\t\t\t\t\t<input type='text' name='$myfield2' value='";

			if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}

			$statisticsoutput .= "' />";
			$allfields[]=$myfield2;
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
			$allfields[]=$myfield2;
			$allfields[]=$myfield3;

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

			//no statistics available yet!
			//."<input type='checkbox'  name='summary[]' value='$myfield2'";

			//if (isset($summary) && (array_search("D{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
			//{$statisticsoutput .= " checked='checked'";}

			//$statisticsoutput .= " /><strong>"
			$statisticsoutput .= "<strong>"
			.showSpeaker($niceqtext)
			."</strong><br />\n"
		
			."\t\t\t\t\t<font size='1'>".$clang->gT("Date (YYYY-MM-DD) equals").":<br />\n"
			."\t\t\t\t\t<input name='$myfield3' type='text' value='";

			if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}

			$statisticsoutput .= "' /><br />\n"
			."\t\t\t\t\t&nbsp;&nbsp;".$clang->gT("OR between").":<br />\n"
			."\t\t\t\t\t<input name='$myfield4' value='";

			if (isset($_POST[$myfield4])) {$statisticsoutput .= $_POST[$myfield4];}

			$statisticsoutput .= "' type='text' /> <br />"
			.$clang->gT("and")."<br /> <input  name='$myfield5' value='";

			if (isset($_POST[$myfield5])) {$statisticsoutput .= $_POST[$myfield5];}

			$statisticsoutput .= "' type='text' /></font>\n";
			$allfields[]=$myfield3;
			$allfields[]=$myfield4;
			$allfields[]=$myfield5;
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
			$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
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
					
				$statisticsoutput .= " />&nbsp;<strong>"
				.showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
				."</strong><br />\n"
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
				$allfields[]=$myfield2;
			}

			$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$counter=0;
			break;



			//just like above only a different loop
		case "B": // ARRAY OF 10 POINT CHOICE QUESTIONS
			$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
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
					
				$statisticsoutput .= " />&nbsp;<strong>"
				.showSpeaker($niceqtext." ".str_replace("'", "`", $row[1])." - # ".$flt[3])
				."</strong><br />\n"
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
				$allfields[]=$myfield2;
			}

			$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$counter=0;
			break;



		case "C": // ARRAY OF YES\No\$clang->gT("Uncertain") QUESTIONS
			$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

			//get answers
			$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
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
				$allfields[]=$myfield2;
			}

			$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$counter=0;
			break;



			//similiar to the above one
		case "E": // ARRAY OF Increase/Same/Decrease QUESTIONS
			$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

			$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
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
				$allfields[]=$myfield2;
			}

			$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$counter=0;
			break;

		case ";":  //ARRAY (Multi Flex) (Text)
			$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
			$result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
			$counter2=0;
			while ($row=$result->FetchRow())
			{
				$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$flt[6]} AND language='{$language}' ORDER BY sortorder, code";
				$fresult = db_execute_assoc($fquery);
				while ($frow = $fresult->FetchRow())
				{
					$myfield2 = "T".$myfield . $row[0] . "_" . $frow['code'];
					$statisticsoutput .= "<!-- $myfield2 - ";
					if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
					$statisticsoutput .= " -->\n";
					if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
					$statisticsoutput .= "\t\t\t\t<td align='center'>"
					."<input type='checkbox'  name='summary[]' value='$myfield2'";
					if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}
					$statisticsoutput .= " />&nbsp;<strong>"
					.showSpeaker($niceqtext." ".str_replace("'", "`", $row[1]." [".$frow['title']."]")." - ".$row[0]."/".$frow['code'])
					."</strong><br />\n";
					//$statisticsoutput .= $fquery;
					$statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Responses containing").":</font><br />\n";
					$statisticsoutput .= "\t\t\t\t<input type='text' name='{$myfield2}' value='";
					if(isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
					$statisticsoutput .= "' />\n\t\t\t\t</td>\n";
					$counter2++;
					$allfields[]=$myfield2;
				}
			}
			$statisticsoutput .= "\t\t\t\t<td>\n";
			$counter=0;
			break;

		case ":":  //ARRAY (Multi Flex) (Numbers)
			$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
			$result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
			$counter2=0;
			//Get qidattributes for this question
			$qidattributes=getQuestionAttributes($flt[0]);
			if ($maxvalue=arraySearchByKey("multiflexible_max", $qidattributes, "attribute", 1)) {
				$maxvalue=$maxvalue['value'];
			} else {
				$maxvalue=10;
			}
			if ($minvalue=arraySearchByKey("multiflexible_min", $qidattributes, "attribute", 1)) {
				$minvalue=$minvalue['value'];
			} else {
				$minvalue=1;
			}
			if ($stepvalue=arraySearchByKey("multiflexible_step", $qidattributes, "attribute", 1)) {
				$stepvalue=$stepvalue['value'];
			} else {
				$stepvalue=1;
			}
			if (arraySearchByKey("multiflexible_checkbox", $qidattributes, "attribute", 1)) {
				$minvalue=0;
				$maxvalue=1;
				$stepvalue=1;
			}
			while ($row=$result->FetchRow())
			{
				$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$flt[6]} AND language='{$language}' ORDER BY sortorder, code";
				$fresult = db_execute_assoc($fquery);
				while ($frow = $fresult->FetchRow())
				{
					$myfield2 = $myfield . $row[0] . "_" . $frow['code'];
					$statisticsoutput .= "<!-- $myfield2 - ";
					if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
					$statisticsoutput .= " -->\n";
					if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
					$statisticsoutput .= "\t\t\t\t<td align='center'>"
					."<input type='checkbox'  name='summary[]' value='$myfield2'";
					if (isset($summary) && array_search($myfield2, $summary)!== FALSE) {$statisticsoutput .= " checked='checked'";}
					$statisticsoutput .= " />&nbsp;<strong>"
					.showSpeaker($niceqtext." ".str_replace("'", "`", $row[1]." [".$frow['title']."]")." - ".$row[0]."/".$frow['code'])
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
					$allfields[]=$myfield2;
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
		case "F": // ARRAY OF Flexible QUESTIONS
		case "H": // ARRAY OF Flexible Questions (By Column)
			$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

			//Get answers. We always use the answer code because the label might be too long elsewise
			$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
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
				$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$flt[6]} AND language='{$language}' ORDER BY sortorder, code";
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

					$statisticsoutput .= ">({$frow['code']}) ".FlattenText($frow['title'])."</option>\n";
				}
					
				$statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
				$counter2++;
					
				//add fields to main array
				$allfields[]=$myfield2;
			}

			$statisticsoutput .= "\t\t\t\t<td>\n";
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
				$allfields[]=$myfield2;
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



			//Dropdown and radio lists
		case "W":
		case "Z":
				
			$statisticsoutput .= "\t\t\t\t<td align='center'>";
			$statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield'";

			//pre-check
			if (isset($summary) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE  || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
			{$statisticsoutput .= " checked='checked'";}

			$statisticsoutput .= " />&nbsp;<strong>".showSpeaker($niceqtext)."</strong><br />\n";
			$statisticsoutput .= "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}[]' multiple='multiple'>\n";
			$allfields[]=$myfield;

			//get labels (code and title)
			$query = "SELECT code, title FROM ".db_table_name("labels")." WHERE lid={$flt[6]} AND language='{$language}' ORDER BY sortorder";
			$result = db_execute_num($query) or safe_die("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());

			//loop through all the labels
			while($row=$result->FetchRow())
			{
				$statisticsoutput .= "\t\t\t\t\t\t<option value='{$row[0]}'";
					
				//pre-check
				if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {$statisticsoutput .= " selected";}
					
				$statisticsoutput .= ">({$row[0]}) ".FlattenText($row[1])."</option>\n";

			} // while

			$statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
			break;




		case "1": // MULTI SCALE
			$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";

			//special dual scale counter
			$counter2=0;

			//get answers
			$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
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
				else
				{
					//get label text
					$lquery = "SELECT label_name FROM ".db_table_name("labelsets")." WHERE lid={$flt[6]}";
					$lresult = db_execute_num($lquery) or safe_die ("Couldn't get label title!<br />$lquery<br />".$connect->ErrorMsg());

					//get title
					while ($lrow=$lresult->FetchRow())
					{
						$labeltitle = $lrow[0];
					}
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
				 
				$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$flt[6]} AND language='{$language}' ORDER BY sortorder, code";
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

					$statisticsoutput .= ">({$frow['code']}) ".FlattenText($frow['title'])."</option>\n";

				}

				$statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
				$counter2++;
				$allfields[]=$myfield2;




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
					$lquery2 = "SELECT label_name FROM ".db_table_name("labelsets")." WHERE lid={$flt[7]}";
					$lresult2 = db_execute_num($lquery2) or safe_die ("Couldn't get label title!<br />$lquery2<br />".$connect->ErrorMsg());

					//get title
					while($lrow2=$lresult2->FetchRow())
					{
						$labeltitle2 = $lrow2[0];
					}
				}

				$statisticsoutput .= " />&nbsp;<strong>"
				.showSpeaker($niceqtext." [".str_replace("'", "`", $row[1])."] - ".$clang->gT("Label").": ".$labeltitle2)
				."</strong><br />\n";
				 
				$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$flt[7]} AND language='{$language}' ORDER BY sortorder, code";
				$fresult = db_execute_assoc($fquery);

				//this is for debugging only
				//$statisticsoutput .= $fquery;

				$statisticsoutput .= "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}#{1}[]' multiple='multiple'>\n";

				//list answers
				while ($frow = $fresult->FetchRow())
				{
					$statisticsoutput .= "\t\t\t\t\t<option value='{$frow['code']}'";

					//pre-check
					if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {$statisticsoutput .= " selected";}
					 
					$statisticsoutput .= ">({$frow['code']}) ".FlattenText($frow['title'])."</option>\n";

				}

				$statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
				$counter2++;
				$allfields[]=$myfield2;

			}	//end WHILE -> loop through all answers

			$statisticsoutput .= "\t\t\t\t<td>\n";
			 

			$counter=0;
			break;



			/*
			 * This question types use the default settings:
			 * 	L - List (Radio)
			 M - Multiple Options
			 O - List With Comment
			 P - Multiple Options With Comments
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
}

//complete output
$statisticsoutput .= "\n\t\t\t\t</tr>\n";

//array allfields contains question codes
if (isset($allfields))
{
	//connect all array elements using "+"
	$allfield=implode("+", $allfields);
}

//pre-selection of filter forms
if (incompleteAnsFilterstate() == "filter")
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





// ------------------------ BEGINN CROSS QUERY

//This section contains the first lines of a new function which might be implemented
//by HIS GmbH (Germany) later.

//Commented out because not used yet. Mazi 30.10.2008

/*
 * supported question types:
 * 	G - Gender
 L - List (Radio)
 M - Multiple Options
 O - List With Comment
 P - Multiple Options With Comments
 W - List (Flexible Labels) (Dropdown)
 Y - Yes/No
 Z - List (Flexible Labels) (Radio)
 ! - List (Dropdown)
 *
 */
/*
 //check if this option is set
 if(isset($showcombinedresults) && $showcombinedresults == 1)
 {

 // Get answers for each supported question type
 	
 //is there a currentgroup set?
 if (!isset($currentgroup)) {$currentgroup="";}



 //just show the headline for the filter once
 $showcrheadline = 0;

 ///...and show 4 questions in each row
 $crcounter = 0;


 * let's go through the filter array which contains
 * 	['qid'],
 ['gid'],
 ['type'],
 ['title'],
 ['group_name'],
 ['question'],
 ['lid'],
 ['lid1']);

 foreach ($filters as $flt)
 {
 	
 //SGQ identifier
 $myfield = "{$surveyid}X{$flt[1]}X{$flt[0]}";

 //full question title
 $niceqtext = FlattenText($flt[5]);

 	

 * Check question type: This question types will be used (all others are separated in the if clause)
 * 	G - Gender
 L - List (Radio)
 M - Multiple Options
 O - List With Comment
 P - Multiple Options With Comments
 Y - Yes/No
 ! - List (Dropdown))
 	
 if ($flt[2] != "A" && $flt[2] != "B" && $flt[2] != "C" && $flt[2] != "E" &&
 $flt[2] != "F" && $flt[2] != "H" && $flt[2] != "T" && $flt[2] != "U" &&
 $flt[2] != "S" && $flt[2] != "D" && $flt[2] != "R" && $flt[2] != "Q" &&
 //deleted question types "W" and "Z" to adjust output
 $flt[2] != "1" && $flt[2] != "X" && $flt[2] != "K" && $flt[2] != "5"  &&
 //old statement: $flt[2] != "X" && $flt[2] != "W" && $flt[2] != "Z" && $flt[2] != "K" &&
 $flt[2] != ":" && $flt[2] != "I" && $flt[2] != "N" && $flt[2] != ";") //Have to make an exception for these types!
 {
 if($showcrheadline == 0)
 {
 $cr_statisticsoutput = "\n\t\t\t\t<!-- new headline --></tr>\n\t\t\t</table><br /><br /></td></tr>\n"
 ."<tr><td align='center' class='settingcaption'  height='22'  style='border: 2px solid #555555;'>\n"
 ."<font size='3'><strong>".$clang->gT("Split up results")."</strong></font>"
 ."</td></tr>\n"
 ."<tr><td align='center'>\n"
 ."\t\t\t<table align='center' width='70%' class='statisticstable'><tr>\n";

 $showcrheadline = 1;
 }
 	
 //we don't want more than 4 questions in a row
 if (isset($crcounter) && $crcounter == 4)
 {
 $cr_statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>";
 $crcounter=0;
 }
 	
 //add temporary output
 //$cr_statisticsoutput .= $tempoutput;
 	
 //the headlines for question types "W" and "Z" are created later
 if($flt[2] != "W" && $flt[2] != "Z")
 {
 $cr_statisticsoutput .= "\t\t\t\t<td align='center'>"
 ."<strong>".showspeaker($flt[5])."&nbsp;"; //Heading (Question No)
 }

 //multiple options:
 if ($flt[2] == "M" || $flt[2] == "P")
 {
 $myfield = "M$myfield";
 }
 	
 //numerical input will get special treatment (arihtmetic mean, standard derivation, ...)
 if ($flt[2] == "N")
 {
 $myfield = "N$myfield";
 }
 	
 //the checkboxes for question types "W" and "Z" are created later
 if($flt[2] != "W" && $flt[2] != "Z")
 {
 $cr_statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield'";
 }
 	
 	
 	
 * one of these conditions has to be true
 * 1. SGQ can be found within the summary array
 * 2. M-SGQ can be found within the summary array (M = multiple options)
 * 3. N-SGQ can be found within the summary array (N = numerical input)
 *
 * Always remember that we just have very few question types that are checked here
 * due to the if ouside this section!
 *
 * Auto-check the question types mentioned above

 if (isset($summary) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE  || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
 {
 $cr_statisticsoutput .= " checked='checked'";
 }
 	
 //the speaker symbol for question types "W" and "Z" are created later
 if($flt[2] != "W" && $flt[2] != "Z")
 {
 //show speaker symbol which contains full question text
 $cr_statisticsoutput .= " />&nbsp;".showSpeaker($niceqtext)."</strong>"
 ."<br />\n";
 }
 	
 //numerical question type -> add some HTML to the output
 if ($flt[2] == "N") {$cr_statisticsoutput .= "</font>";}
 	
 //only for non-numerical question types. output for types "W" and "Z" is created later
 if ($flt[2] != "N" && $flt[2] != "W" && $flt[2] != "Z") {$cr_statisticsoutput .= "\t\t\t\t<select name='";}
 	
 //multiple options ("M"/"P") -> add "M" to output
 if ($flt[2] == "M" || $flt[2] == "P") {$cr_statisticsoutput .= "M";}
 	
 //only for non-numerical question types. qoutput for types "W" and "Z" is created later
 if ($flt[2] != "N" && $flt[2] != "W" && $flt[2] != "Z")
 {
 $cr_statisticsoutput .= "{$surveyid}X{$flt[1]}X{$flt[0]}[]' multiple='multiple'>\n";
 }
 	
 //Add the field name into the allfields array, which is used later to know which are the available fields for selection
 $allfields[]=$myfield;
 	
 }	//end if -> filter certain question types

 $cr_statisticsoutput .= "\t\t\t\t\t<!-- QUESTION TYPE = $flt[2] -->\n";
 /////////////////////////////////////////////////////////////////////////////////////////////////
 //This section presents the filter list, in various different ways depending on the question type
 /////////////////////////////////////////////////////////////////////////////////////////////////

 //let's switch through the question type for each question
 switch ($flt[2])
 {
 case "G": // Gender
 $cr_statisticsoutput .= "\t\t\t\t\t<option value='F'";
 	
 //pre-select values which were marked before
 if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("F", $_POST[$myfield])) {$cr_statisticsoutput .= " selected";}
 	
 $cr_statisticsoutput .= ">".$clang->gT("Female")."</option>\n";
 $cr_statisticsoutput .= "\t\t\t\t\t<option value='M'";
 	
 //pre-select values which were marked before
 if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("M", $_POST[$myfield])) {$cr_statisticsoutput .= " selected";}
 	
 $cr_statisticsoutput .= ">".$clang->gT("Male")."</option>\n\t\t\t\t</select></font>\n";
 	
 //increase counter to adjust layout
 $crcounter++;
 	
 break;
 	
 	
 	
 case "Y": // Yes\No
 $cr_statisticsoutput .= "\t\t\t\t\t<option value='Y'";
 	
 //pre-select values which were marked before
 if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("Y", $_POST[$myfield])) {$cr_statisticsoutput .= " selected";}
 	
 $cr_statisticsoutput .= ">".$clang->gT("Yes")."</option>\n"
 ."\t\t\t\t\t<option value='N'";
 	
 //pre-select values which were marked before
 if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("N", $_POST[$myfield])) {$cr_statisticsoutput .= " selected";}
 	
 $cr_statisticsoutput .= ">".$clang->gT("No")."</option></select></font>\n";
 	
 //increase counter to adjust layout
 $crcounter++;
 	
 break;

 	
 //Dropdown and radio lists
 case "W":
 case "Z":

 $cr_statisticsoutput .= "\t\t\t\t<td align='center'>";
 $cr_statisticsoutput .= "<input type='checkbox'  name='summary[]' value='$myfield'";
 	
 //pre-check
 if (isset($summary) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE  || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $summary) !== FALSE))
 {$cr_statisticsoutput .= " checked='checked'";}
 	
 $cr_statisticsoutput .= " />&nbsp;"
 .showSpeaker($niceqtext)."</strong>"
 ."<br />\n";
 $cr_statisticsoutput .= "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}[]' multiple='multiple'>\n";
 $allfields[]=$myfield;
 	
 //get labels (code and title)
 $query = "SELECT code, title FROM ".db_table_name("labels")." WHERE lid={$flt[6]} AND language='{$language}' ORDER BY sortorder";
 $result = db_execute_num($query) or safe_die("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
 	
 //loop through all the labels
 while($row=$result->FetchRow())
 {
 $cr_statisticsoutput .= "\t\t\t\t\t\t<option value='{$row[0]}'";

 //pre-check
 if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {$cr_statisticsoutput .= " selected";}

 $cr_statisticsoutput .= ">({$row[0]}) ".strip_tags($row[1])."</option>\n";
  
 } // while
 	
 $cr_statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
 	
 //increase counter to adjust layout
 $crcounter++;
 	
 break;
  
  
  
 * This question types use the default settings:
 * 	L - List (Radio)
 M - Multiple Options
 O - List With Comment
 P - Multiple Options With Comments
 ! - List (Dropdown)

 //default:
 case "L":
 case "M":
 case "O":
 case "P":
 case "!":
 	
 //get answers
 $query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
 $result = db_execute_num($query) or safe_die("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
 	
 //loop through answers
 while ($row=$result->FetchRow())
 {
 $cr_statisticsoutput .= "\t\t\t\t\t\t<option value='{$row[0]}'";

 //pre-check
 if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {$cr_statisticsoutput .= " selected";}

 $cr_statisticsoutput .= ">$row[1]</option>\n";
 }
 	
 $cr_statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</td>\n";
 	
 //increase counter to adjust layout
 $crcounter++;
 	
 break;

 }	//end switch -> check question types and create filter forms

 $currentgroup=$flt[1];

 //if (!isset($crcounter)) {$crcounter=0;}
 //$crcounter++;

 }	//end foreach -> loop thorugh all available questions

 //array allfields contains question codes
 if (isset($allfields))
 {
 //connect all array elements using "+"
 $allfield=implode("+", $allfields);
 }



 //add own output to general output
 $statisticsoutput .= $cr_statisticsoutput;

 }	//end if -> option crossquery set?
 */

// --------------------------------------- END CROSS QUERY ------------------------------------





//add last lines to filter forms
$statisticsoutput .= "\t\t\t</table>\n"
."\t\t</td></tr>\n";

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
	unset($_POST['usegraph']);
}

$viewalltext = "<tr><td align='center' class='settingcaption'>\n"
."\t\t<font size='1'>&nbsp;</font></td></tr>\n"
."<tr><td align='center'><ul class='myul'><li><input type='checkbox' id='viewsummaryall' name='summary' value='$allfield' />"
."<label for='viewsummaryall'>".$clang->gT("View summary of all available fields")."</label></li>"
."<li><input type='checkbox' id='usegraph' name='usegraph' ";
if (isset($_POST['usegraph'])) {$viewalltext .= "checked='checked'";}
$viewalltext .= "/><label for='usegraph'>".$clang->gT("Show graphs")."</label><br />";
if ($grapherror!='')
{
	$viewalltext.="<span id='grapherror' style='display:none'>$grapherror<hr /></span>";
}
$viewalltext.="</li>\n"
."<li><label for='filterinc'>".$clang->gT("Include:")."</label><select name='filterinc' id='filterinc'>\n"
."<option value='show' $selectshow>".$clang->gT("All records")."</option>\n"
."<option value='filter' $selecthide>".$clang->gT("Completed records only")."</option>\n"
."<option value='incomplete' $selectinc>".$clang->gT("Incomplete records only")."</option>\n"
."</select></li></ul></td></tr>\n";
$statisticsoutput = str_replace("{VIEWALL}", $viewalltext, $statisticsoutput);

//add line to separate the the filters from the other options
$statisticsoutput .= "<tr><td align='center' class='settingcaption'>
	       <font size='1' face='verdana'>&nbsp;</font>
		  </td></tr>";

$statisticsoutput .= "</table>
<table cellpadding='0' cellspacing='0' width='100%'>\n";

$statisticsoutput .="\t\t\t\t<tr><td align='center'>    <div id='vertical_slide'";
if ($selecthide!='')
{
	$statisticsoutput .= " style='display:none' ";
}

//this fixes bug #2470
$statisticsoutput.=" ><input type='checkbox' id='noncompleted' name='noncompleted' ";
if (isset($_POST['noncompleted'])) {$statisticsoutput .= "checked='checked'";}
$statisticsoutput.=" /><label for='noncompleted'>".$clang->gT("Don't consider NON completed responses")."</label></div><br /></td></tr>\n";

//very last lines of output
$statisticsoutput .= "\t\t<tr><td align='center'>\n\t\t\t<br />\n"
."\t\t\t<input type='submit' value='".$clang->gT("View stats")."' />\n"
."\t\t\t<input type='button' value='".$clang->gT("Clear")."' onclick=\"window.open('$scriptname?action=statistics&amp;sid=$surveyid', '_top')\" />\n"
."\t\t<br />&nbsp;\n"
."\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
."\t\t<input type='hidden' name='display' value='stats' />\n"
."\t</td></tr>\n"
."</table></div>\n"
."</td></tr></table>\n"
."\t</form>\n";

// ----------------------------------- END FILTER FORM ---------------------------------------


// DISPLAY RESULTS
if (isset($_POST['display']) && $_POST['display'])
{
	// Create progress bar which is shown while creating the results
	$prb = new ProgressBar();
	$prb->pedding = 2;	// Bar Pedding
	$prb->brd_color = "#404040 #dfdfdf #dfdfdf #404040";	// Bar Border Color

	$prb->setFrame();	// set ProgressBar Frame
	$prb->frame['left'] = 50;	// Frame position from left
	$prb->frame['top'] = 	80;	// Frame position from top
	$prb->addLabel('text','txt1',$clang->gT("Please wait ..."));	// add Text as Label 'txt1' and value 'Please wait'
	$prb->addLabel('percent','pct1');	// add Percent as Label 'pct1'
	$prb->addButton('btn1',$clang->gT('Go back'),'?action=statistics&amp;sid='.$surveyid);	// add Button as Label 'btn1' and action '?restart=1'

	//progress bar starts with 35%
	$process_status = 35;
	$prb->show();	// show the ProgressBar

	// 1: Get list of questions with answers chosen
	//"Getting Questions and Answers ..." is shown above the bar
	$prb->setLabelValue('txt1',$clang->gT('Getting questions and answers ...'));
	$prb->moveStep(5);

	// creates array of post variable names
	for (reset($_POST); $key=key($_POST); next($_POST)) { $postvars[]=$key;}

	/*
	 * Iterate through postvars to create "nice" data for SQL later.
	 *
	 * Remember there might be some filters applied which have to be put into an SQL statement
	 */
	foreach ($postvars as $pv)
	{
		//Only do this if there is actually a value for the $pv
		if (in_array($pv, $allfields))
		{
			$firstletter=substr($pv,0,1);
				
			/*
			 * these question types WON'T be handled here:
			 * M = Multiple Options
			 * T - Long Free Text
			 * Q - Multiple Short Text
			 * D - Date
			 * N - Numerical Input
			 * K - Multiple Numerical Input
			 */
			if ($pv != "sid" && $pv != "display" && $firstletter != "M" && $firstletter != "T" &&
			$firstletter != "Q" && $firstletter != "D" && $firstletter != "N" && $firstletter != "K" &&
			$pv != "summary" && substr($pv, 0, 2) != "id" && substr($pv, 0, 9) != "datestamp") //pull out just the fieldnames
			{
				//put together some SQL here
				$thisquestion = db_quote_id($pv)." IN (";

				foreach ($_POST[$pv] as $condition)
				{
					$thisquestion .= "'$condition', ";
				}

				$thisquestion = substr($thisquestion, 0, -2)
				. ")";

				//we collect all the to be selected data in this array
				$selects[]=$thisquestion;
			}
				
			//M - Multiple Options
			elseif ($firstletter == "M")
			{
				//create a list out of the $pv array
				list($lsid, $lgid, $lqid) = explode("X", $pv);

				$aquery="SELECT code FROM ".db_table_name("answers")." WHERE qid=$lqid AND language='{$language}' ORDER BY sortorder, answer";
				$aresult=db_execute_num($aquery) or safe_die ("Couldn't get answers<br />$aquery<br />".$connect->ErrorMsg());

				// go through every possible answer
				while ($arow=$aresult->FetchRow())
				{
					// only add condition if answer has been chosen
					if (in_array($arow[0], $_POST[$pv]))
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
				
			//N - Numerical Input
			//K - Multiple Numerical Input
			elseif ($firstletter == "N" || $firstletter == "K")
			{
				//value greater than
				if (substr($pv, strlen($pv)-1, 1) == "G" && $_POST[$pv] != "")
				{
					$selects[]=db_quote_id(substr($pv, 1, -1))." > ".sanitize_int($_POST[$pv]);
				}

				//value less than
				if (substr($pv, strlen($pv)-1, 1) == "L" && $_POST[$pv] != "")
				{
					$selects[]=db_quote_id(substr($pv, 1, -1))." < ".sanitize_int($_POST[$pv]);
				}
			}
				
			//"id" is a built in field, the unique database id key of each response row
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
				
			//T - Long Free Text
			//Q - Multiple Short Text
			elseif (($firstletter == "T" || $firstletter == "Q" ) && $_POST[$pv] != "")
			{
				$selects[]=db_quote_id(substr($pv, 1, strlen($pv)))." like '%".$_POST[$pv]."%'";
			}
				
			//D - Date
			elseif ($firstletter == "D" && $_POST[$pv] != "")
			{
				//Date equals
				if (substr($pv, -1, 1) == "=")
				{
					$selects[]=db_quote_id(substr($pv, 1, strlen($pv)-2))." = '".$_POST[$pv]."'";
				}
				else
				{
					//date less than
					if (substr($pv, -1, 1) == "<")
					{
						$selects[]= db_quote_id(substr($pv, 1, strlen($pv)-2)) . " > '".$_POST[$pv]."'";
					}
						
					//date greater than
					if (substr($pv, -1, 1) == ">")
					{
						$selects[]= db_quote_id(substr($pv, 1, strlen($pv)-2)) . " < '".$_POST[$pv]."'";
					}
				}
			}
				
			//check for datestamp of given answer
			elseif (substr($pv, 0, 9) == "datestamp")
			{
				//timestamp equals
				if (substr($pv, -1, 1) == "=" && !empty($_POST[$pv]))
				{
					$selects[] = db_quote_id('datestamp')." = '".$_POST[$pv]."'";
				}
				else
				{
					//timestamp less than
					if (substr($pv, -1, 1) == "<" && !empty($_POST[$pv]))
					{
						$selects[]= db_quote_id('datestamp')." > '".$_POST[$pv]."'";
					}
						
					//timestamp greater than
					if (substr($pv, -1, 1) == ">" && !empty($_POST[$pv]))
					{
						$selects[]= db_quote_id('datestamp')." < '".$_POST[$pv]."'";
					}
				}
			}
		}

		else
		{
			$statisticsoutput .= "<!-- $pv DOES NOT EXIST IN ARRAY -->";
		}

	}	//end foreach -> loop through filter options to create SQL





	// 2: Do SQL query
	//"Getting Result Count ..." is shown above the progress bar
	$prb->setLabelValue('txt1',$clang->gT('Getting result count ...'));
	$prb->moveStep(35);

	//count number of answers
	$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid");

	//if incompleted answers should be filtert submitdate has to be not null
	if (incompleteAnsFilterstate() == "inc") {$query .= " WHERE submitdate is null";}
	elseif (incompleteAnsFilterstate() == "filter") {$query .= " WHERE submitdate is not null";}
	$result = db_execute_num($query) or safe_die ("Couldn't get total<br />$query<br />".$connect->ErrorMsg());

	//$total = total number of answers
	while ($row=$result->FetchRow()) {$total=$row[0];}

	//are there any filters that have to be taken care of?
	if (isset($selects) && $selects)
	{
		//filter incomplete answers?
		if (incompleteAnsFilterstate() == "filter" || incompleteAnsFilterstate() == "inc") {$query .= " AND ";}

		else {$query .= " WHERE ";}

		//add filter criteria to SQL
		$query .= implode(" AND ", $selects);
	}

	//$_POST['sql'] is a post field that is sent from the statistics script to the export script in order
	// to export just those results filtered by this statistics script. It can also be passed to the statistics
	// script to filter from external scripts.
	elseif (!empty($_POST['sql']) && !isset($_POST['id=']))
	{
		$newsql=substr($_POST['sql'], strpos($_POST['sql'], "WHERE")+5, strlen($_POST['sql']));

		//for debugging only
		//$query = $_POST['sql'];

		//filter incomplete answers?
		if (incompleteAnsFilterstate() == "inc") {$query .= " AND ".$newsql;}
		elseif (incompleteAnsFilterstate() == "filter") {$query .= " AND ".$newsql;}

		else {$query .= " WHERE ".$newsql;}
	}

	//get me some data Scotty
	$result=db_execute_num($query) or safe_die("Couldn't get results<br />$query<br />".$connect->ErrorMsg());

	//put all results into $results
	while ($row=$result->FetchRow()) {$results=$row[0];}





	// 3: Present results including option to view those rows

	//put everything into this table

	//show some main data at the beginnung
	$statisticsoutput .= "<br />\n<table class='statisticssummary' align='center' >\n"
	."\t<thead><tr><th colspan='2'>".$clang->gT("Results")."</th></tr></thead>\n"
	."\t<tr><th>".$clang->gT("Number of records in this query:").'</th>'
	."<td>$results</td></tr>\n"
	."\t<tr><th>".$clang->gT("Total records in survey:").'</th>'
	."<td>$total</td></tr>\n";

	//only calculate percentage if $total is set
	if ($total)
	{
		$percent=sprintf("%01.2f", ($results/$total)*100);
		$statisticsoutput .= "\t<tr><th align='right'>".$clang->gT("Percentage of total:").'</th>'
		."<td>$percent%</td></tr>\n";
	}

	//put everything from $selects array into a string connected by AND
	if (isset ($selects) && $selects) {$sql=implode(" AND ", $selects);}

	elseif (!empty($newsql)) {$sql = $newsql;}

	if (!isset($sql) || !$sql) {$sql="NULL";}

	//only continue if we have something to output
	if ($results > 0)
	{
		//add two buttons to browse results and export results
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
			//The summary array contains the fields that have been selected from the filter screen
			//  to be displayed in the results page. So we're iterating through them one at a time
			foreach($summary as $viewfields)
			{
				//We're checking the first letter of each of the selected-to-be-displayed fields
				//  in order to deal with the special types (usually those with multiple files per question)
				switch(substr($viewfields, 0, 1))
				{
					/*
					 * some special treatment for
					 *
					 * N - Numerical Input
					 * T - Long Free Text
					 * K - Multiple Numerical Input
					 */
					case "N":
					case "T":
					case "K":
							
						// Now we remove the first character from the fieldname, and so are left
						//   with just the actual SGQ code for this field/answer.
						$field = substr($viewfields, 1, strlen($viewfields)-1);
						$statisticsoutput .= "\t\t\t<input type='hidden' name='summary[]' value='$field' />\n";
						break;
							
							
						//M - Multiple Options
					case "M":
							
						//create a SGQ identifier
						list($lsid, $lgid, $lqid) = explode("X", substr($viewfields, 1, strlen($viewfields)-1));
							
						//we need all the answer codes.
						//there might be more than one because it's a multiple options question
						$aquery="SELECT code FROM ".db_table_name("answers")." WHERE qid=$lqid AND language='{$language}' ORDER BY sortorder, answer";
						$aresult=db_execute_num($aquery) or safe_die ("Couldn't get answers<br />$aquery<br />".$connect->ErrorMsg());
							
						// go through every possible answer
						while ($arow=$aresult->FetchRow())
						{
							$field = substr($viewfields, 1, strlen($viewfields)-1).$arow[0];
							$statisticsoutput .= "\t\t\t<input type='hidden' name='summary[]' value='$field' />\n";
						}
							
						//check data of "other" field
						$aquery = "SELECT other FROM ".db_table_name("questions")." WHERE qid=$lqid AND language='{$language}'";
						$aresult = db_execute_num($aquery);
							
						while($arow = $aresult->FetchRow())
						{
							//"other" answer set?
							if ($arow[0] == "Y") {
									
								//some debugging output
								//$statisticsoutput .= $arow[0];
									
								$field = substr($viewfields, 1, strlen($viewfields)-1)."other";
								$statisticsoutput .= "\t\t\t<input type='hidden' name='summary[]' value='$field' />\n";
							}

						} // while
							
						break;
							
						//default treatment for all the other question types
					default:
						$field = $viewfields;
						$statisticsoutput .= "\t\t\t<input type='hidden' name='summary[]' value='$field' />\n";
						break;
				}

			}	//end foreach
				
		}	//end if (summary)

		//close form
		$statisticsoutput .= "\t\t</form></td>\n\t</tr>\n";

	}	//end if (results available?)

	//close table
	$statisticsoutput .= "</table><br />\n";
}

//push progress bar from 35 to 40
$process_status = 40;







//Show Summary results
if (isset($summary) && $summary)
{
	//"Generating Summaries ..." is shown above the progress bar
	$prb->setLabelValue('txt1',$clang->gT('Generating summaries ...'));
	$prb->moveStep($process_status);

	//let's run through the survey
	$runthrough=$summary;

	//START Chop up fieldname and find matching questions

	//GET LIST OF LEGIT QIDs FOR TESTING LATER
	$lq = "SELECT DISTINCT qid FROM ".db_table_name("questions")." WHERE sid=$surveyid";
	$lr = db_execute_assoc($lq);

	//loop through the IDs
	while ($lw = $lr->FetchRow())
	{
		//this creates an array of question id's'
		$legitqids[] = $lw['qid'];
	}

	//loop through all selected questions
	foreach ($runthrough as $rt)
	{
		//update progress bar
		if ($process_status < 100) $process_status++;
		$prb->moveStep($process_status);

		$firstletter = substr($rt, 0, 1);
		// 1. Get answers for question ##############################################################

		//M - Multiple Options, therefore multiple fields
		if ($firstletter == "M")
		{
			//get SGQ data
			list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);
				
			//select details for this question
			$nquery = "SELECT title, type, question, lid, other FROM ".db_table_name("questions")." WHERE language='{$language}' and qid='$qqid'";
			$nresult = db_execute_num($nquery) or safe_die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());
				
			//loop through question data
			while ($nrow=$nresult->FetchRow())
			{
				$qtitle=$nrow[0];
				$qtype=$nrow[1];
				$qquestion=FlattenText($nrow[2]);
				$qlid=$nrow[3];
				$qother=$nrow[4];
			}

			//1. Get list of answers
			$query="SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qqid' AND language='{$language}' ORDER BY sortorder, answer";
			$result=db_execute_num($query) or safe_die("Couldn't get list of answers for multitype<br />$query<br />".$connect->ErrorMsg());
				
			//loop through multiple answers
			while ($row=$result->FetchRow())
			{
				$mfield=substr($rt, 1, strlen($rt))."$row[0]";

				//create an array containing answer code, answer and fieldname(??)
				$alist[]=array("$row[0]", FlattenText($row[1]), $mfield);
			}
				
			//check "other" field. is it set?
			if ($qother == "Y")
			{
				$mfield=substr($rt, 1, strlen($rt))."other";

				//create an array containing answer code, answer and fieldname(??)
				$alist[]=array($clang->gT("Other"), $clang->gT("Other"), $mfield);
			}
		}


		//S - Short Free Text
		//T - Long Free Text
		elseif ($firstletter == "T" || $firstletter == "S") //Short and long text
		{
			$fieldmap=createFieldMap($surveyid, "full");
				
			//search for key
			$fielddata=arraySearchByKey(substr($rt, 1, strlen($rt)), $fieldmap, "fieldname", 1);
				
			//get SGQA IDs
			$qsid=$fielddata['sid'];
			$qgid=$fielddata['gid'];
			$qqid=$fielddata['qid'];
				

			list($qanswer, $qlid)=!empty($fielddata['aid']) ? explode("_", $fielddata['aid']) : array("", "");
			//get SGQ data
			//list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);
				
				
			//get question data
			$nquery = "SELECT title, type, question, other, lid FROM ".db_table_name("questions")." WHERE qid='$qqid' AND language='{$language}'";
			$nresult = db_execute_num($nquery) or safe_die("Couldn't get text question<br />$nquery<br />".$connect->ErrorMsg());
				
			//loop through question data
			while ($nrow=$nresult->FetchRow())
			{
				$qtitle=FlattenText($nrow[0]);
				$qtype=$nrow[1];
				$qquestion=FlattenText($nrow[2]);
				$nlid=$nrow[4];
			}
				
			if(!empty($qanswer)) {
				$nquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qqid' AND language = '{$language}' AND code='$qanswer' ORDER BY sortorder, answer";
				$nresult = db_execute_assoc($nquery) or safe_die("Couldn't get text question answer<br />$nquery<br />".$connect->ErrorMsg());
				while ($nrow=$nresult->FetchRow())
				{
					$qtitle .= " [".FlattenText($nrow['answer'])."]";
				}
			}

			if(!empty($qlid)) {
				$nquery = "SELECT code, title FROM ".db_table_name("labels")." WHERE lid={$nlid} AND code='$qlid' AND language='{$language}' ORDER BY sortorder, code";
				$nresult = db_execute_assoc($nquery) or safe_die("Couldn't get labelset for text question<br />$nquery<br />".$connect->ErrorMsg());
				while ($nrow = $nresult->FetchRow())
				{
					$qtitle .= " [".FlattenText($nrow['title'])."]";
				}
			}
				
			$mfield=substr($rt, 1, strlen($rt));
				
			//Text questions either have an answer, or they don't. There's no other way of quantising the results.
			// So, instead of building an array of predefined answers like we do with lists & other types,
			// we instead create two "types" of possible answer - either there is a response.. or there isn't.
			// This question type then can provide a % of the question answered in the summary.
			$alist[]=array("Answers", $clang->gT("Answer"), $mfield);
			$alist[]=array("NoAnswer", $clang->gT("No answer"), $mfield);
		}


		//Multiple short text
		elseif ($firstletter == "Q")
		{
			//get SGQ data
			list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)), 3);

			//separating another ID
			$tmpqid=substr($qqid, 0, strlen($qqid)-1);

			//check if we have legid QIDs. if not create them by substringing
			while (!in_array ($tmpqid,$legitqids)) $tmpqid=substr($tmpqid, 0, strlen($tmpqid)-1);

			//length of QID
			$qidlength=strlen($tmpqid);
				
			//we somehow get the answer code (see SQL later) from the $qqid
			$qaid=substr($qqid, $qidlength, strlen($qqid)-$qidlength);
				
			//get some question data
			$nquery = "SELECT title, type, question, other FROM ".db_table_name("questions")." WHERE qid='".substr($qqid, 0, $qidlength)."' AND language='{$language}'";
			$nresult = db_execute_num($nquery) or safe_die("Couldn't get text question<br />$nquery<br />".$connect->ErrorMsg());
				
			//more substrings
			$count = substr($qqid, strlen($qqid)-1);
				
			//loop through question data
			while ($nrow=$nresult->FetchRow())
			{
				$qtitle=FlattenText($nrow[0]).'-'.$count;
				$qtype=$nrow[1];
				$qquestion=FlattenText($nrow[2]);
			}
				
			//get answers
			$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='".substr($qqid, 0, $qidlength)."' AND code='$qaid' AND language='{$language}' ORDER BY sortorder, answer";
			$qresult=db_execute_num($qquery) or safe_die ("Couldn't get answer details (Array 5p Q)<br />$qquery<br />".$connect->ErrorMsg());

			//loop through answer data
			while ($qrow=$qresult->FetchRow())
			{
				//store each answer here
				$atext=FlattenText($qrow[1]);
			}

			//add this to the question title
			$qtitle .= " [$atext]";

			//even more substrings...
			$mfield=substr($rt, 1, strlen($rt));
				
			//Text questions either have an answer, or they don't. There's no other way of quantising the results.
			// So, instead of building an array of predefined answers like we do with lists & other types,
			// we instead create two "types" of possible answer - either there is a response.. or there isn't.
			// This question type then can provide a % of the question answered in the summary.
			$alist[]=array("Answers", $clang->gT("Answer"), $mfield);
			$alist[]=array("NoAnswer", $clang->gT("No answer"), $mfield);
		}


		//RANKING OPTION THEREFORE CONFUSING
		elseif ($firstletter == "R")
		{
			//getting the needed IDs somehow
			$lengthofnumeral=substr($rt, strpos($rt, "-")+1, 1);
			list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strpos($rt, "-")-($lengthofnumeral+1)), 3);
				
			//get question data
			$nquery = "SELECT title, type, question FROM ".db_table_name("questions")." WHERE qid='$qqid' AND language='{$language}'";
			$nresult = db_execute_num($nquery) or safe_die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());
				
			//loop through question data
			while ($nrow=$nresult->FetchRow())
			{
				$qtitle=FlattenText($nrow[0]). " [".substr($rt, strpos($rt, "-")-($lengthofnumeral), $lengthofnumeral)."]";
				$qtype=$nrow[1];
				$qquestion=FlattenText($nrow[2]). "[".$clang->gT("Ranking")." ".substr($rt, strpos($rt, "-")-($lengthofnumeral), $lengthofnumeral)."]";
			}
				
			//get answers
			$query="SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qqid' AND language='{$language}' ORDER BY sortorder, answer";
			$result=db_execute_num($query) or safe_die("Couldn't get list of answers for multitype<br />$query<br />".$connect->ErrorMsg());
				
			//loop through answers
			while ($row=$result->FetchRow())
			{
				//create an array containing answer code, answer and fieldname(??)
				$mfield=substr($rt, 1, strpos($rt, "-")-1);
				$alist[]=array("$row[0]", FlattenText($row[1]), $mfield);
			}
		}

		//N = numerical input
		//K = multiple numerical input
		elseif ($firstletter == "N" || $firstletter == "K") //NUMERICAL TYPE
		{
			//Zero handling
			if (!isset($excludezeros)) //If this hasn't been set, set it to on as default:
			{
				$excludezeros=1;
			}
			//check last character, greater/less/equals don't need special treatment
			if (substr($rt, -1) == "G" ||  substr($rt, -1) == "L" || substr($rt, -1) == "=")
			{
				//DO NOTHING
			}
			else
			{
				//create SGQ identifier
				list($qsid, $qgid, $qqid) = explode("X", $rt, 3);

				//multiple numerical input
				if($firstletter == "K")
				{
					// This is a multiple numerical question so we need to strip of the answer id to find the question title
					$tmpqid=substr($qqid, 0, strlen($qqid)-1);

					//did we get a valid ID?
					while (!in_array ($tmpqid,$legitqids))
					$tmpqid=substr($tmpqid, 0, strlen($tmpqid)-1);

					//check lenght of ID
					$qidlength=strlen($tmpqid);

					//get answer ID from qid
					$qaid=substr($qqid, $qidlength, strlen($qqid)-$qidlength);

					//get question details from DB
					$nquery = "SELECT title, type, question, qid, lid
							   FROM ".db_table_name("questions")." 
							   WHERE qid='".substr($qqid, 0, $qidlength)."' 
							   AND language='{$language}'";
					$nresult = db_execute_num($nquery) or safe_die("Couldn't get text question<br />$nquery<br />".$connect->ErrorMsg());
				}

				//probably question type "N" = numerical input
				else
				{
					//we can use the qqid without any editing
					$nquery = "SELECT title, type, question, qid, lid FROM ".db_table_name("questions")." WHERE qid='$qqid' AND language='{$language}'";
					$nresult = db_execute_num($nquery) or safe_die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());
				}

				//loop through results
				while ($nrow=$nresult->FetchRow())
				{
					$qtitle=FlattenText($nrow[0]); //clean up title
					$qtype=$nrow[1];
					$qquestion=FlattenText($nrow[2]);
					$qiqid=$nrow[3];
					$qlid=$nrow[4];
				}

				//Get answer texts for multiple numerical
				if(substr($rt, 0, 1) == "K")
				{
					//get answer data
					$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qaid' AND language='{$language}' ORDER BY sortorder, answer";
					$qresult=db_execute_num($qquery) or safe_die ("Couldn't get answer details (Array 5p Q)<br />$qquery<br />".$connect->ErrorMsg());

					//handle answer
					while ($qrow=$qresult->FetchRow())
					{
						$atext=FlattenText($qrow[1]);
					}
					//put single items in brackets at output
					$qtitle .= " [$atext]";
				}

				//outputting headline
				$statisticsoutput .= "\n<table class='statisticstable' >\n"
				."\t<thead><tr><th colspan='2' align='center'><strong>".sprintf($clang->gT("Field summary for %s"),$qtitle).":</strong>"
				."</th></tr>\n"
				."\t<tr><th colspan='2' align='center'><strong>$qquestion</strong></th></tr>\n"
				."\t<tr>\n\t\t<th width='50%' align='center' ><strong>"
				.$clang->gT("Calculation")."</strong></th>\n"
				."\t\t<th width='50%' align='center' ><strong>"
				.$clang->gT("Result")."</strong></th>\n"
				."\t</tr></thead>\n";

				//this field is queried using mathematical functions
				$fieldname=substr($rt, 1, strlen($rt));

				//special treatment for MS SQL databases
				if ($connect->databaseType == 'odbc_mssql' || $connect->databaseType == 'odbtp' || $connect->databaseType == 'mssql_n')
				{
					//standard deviation
					$query = "SELECT STDEVP(".db_quote_id($fieldname)."*1) as stdev";
				}

				//other databases (MySQL, Postgres)
				else
				{
					//standard deviation
					$query = "SELECT STDDEV(".db_quote_id($fieldname).") as stdev";
				}

				//sum
				$query .= ", SUM(".db_quote_id($fieldname)."*1) as sum";

				//average
				$query .= ", AVG(".db_quote_id($fieldname)."*1) as average";

				//min
				$query .= ", MIN(".db_quote_id($fieldname)."*1) as minimum";

				//max
				$query .= ", MAX(".db_quote_id($fieldname)."*1) as maximum";
				//Only select responses where there is an actual number response, ignore nulls and empties (if these are included, they are treated as zeroes, and distort the deviation/mean calculations)

				//special treatment for MS SQL databases
				if ($connect->databaseType == 'odbc_mssql' || $connect->databaseType == 'odbtp' || $connect->databaseType == 'mssql_n')
				{
					//no NULL/empty values please
					$query .= " FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT NULL";
					if(!$excludezeros)
					{
						//NO ZERO VALUES
						$query .= " AND (".db_quote_id($fieldname)." <> 0)";
					}
				}

				//other databases (MySQL, Postgres)
				else
				{
					//no NULL/empty values please
					$query .= " FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT NULL";
					if(!$excludezeros)
					{
						//NO ZERO VALUES
						$query .= " AND (".db_quote_id($fieldname)." != 0)";
					}
				}

				//filter incomplete answers if set
				if (incompleteAnsFilterstate() == "inc") {$query .= " AND submitdate is null";}
				elseif (incompleteAnsFilterstate() == "filter") {$query .= " AND submitdate is not null";}

				//$sql was set somewhere before
				if ($sql != "NULL") {$query .= " AND $sql";}

				//execute query
				$result=db_execute_assoc($query) or safe_die("Couldn't do maths testing<br />$query<br />".$connect->ErrorMsg());

				//get calculated data
				while ($row=$result->FetchRow())
				{
					//put translation of mean and calculated data into $showem array
					$showem[]=array($clang->gT("Sum"), $row['sum']);
					$showem[]=array($clang->gT("Standard deviation"), round($row['stdev'],2));
					$showem[]=array($clang->gT("Average"), round($row['average'],2));
					$showem[]=array($clang->gT("Minimum"), $row['minimum']);
						
					//Display the maximum and minimum figures after the quartiles for neatness
					$maximum=$row['maximum'];
					$minimum=$row['minimum'];
				}



				//CALCULATE QUARTILES

				//get data
				$query ="SELECT ".db_quote_id($fieldname)." FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT null";
				//NO ZEROES
				if(!$excludezeros)
				{
					$query .= " AND ".db_quote_id($fieldname)." != 0";
				}

				//filtering enabled?
				if (incompleteAnsFilterstate() == "inc") {$query .= " AND submitdate is null";}
				elseif (incompleteAnsFilterstate() == "filter") {$query .= " AND submitdate is not null";}

				//if $sql values have been passed to the statistics script from another script, incorporate them
				if ($sql != "NULL") {$query .= " AND $sql";}

				//execute query
				$result=$connect->Execute($query) or safe_die("Disaster during median calculation<br />$query<br />".$connect->ErrorMsg());

				$querystarter="SELECT ".db_quote_id($fieldname)." FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($fieldname)." IS NOT null";
				//No Zeroes
				if(!$excludezeros)
				{
					$querystart .= " AND ".db_quote_id($fieldname)." != 0";
				}
				//filtering enabled?
				if (incompleteAnsFilterstate() == "inc") {$querystarters .= " AND submitdate is null";}
				elseif (incompleteAnsFilterstate() == "filter") {$querystarter .= " AND submitdate is not null";}

				//if $sql values have been passed to the statistics script from another script, incorporate them
				if ($sql != "NULL") {$querystarter .= " AND $sql";}

				//we just count the number of records returned
				$medcount=$result->RecordCount();

				//put the total number of records at the beginning of this array
				array_unshift($showem, array($clang->gT("Count"), $medcount));


				//no more comment from Mazi regarding the calculation

				// Calculating only makes sense with more than one result
				if ($medcount>1)
				{
					//1ST QUARTILE (Q1)
					$q1=(1/4)*($medcount+1);
					$q1b=(int)((1/4)*($medcount+1));
					$q1c=$q1b-1;
					$q1diff=$q1-$q1b;
					$total=0;
						
					// fix if there are too few values to evaluate.
					if ($q1c<1) {$q1c=1;$lastnumber=0;}
						
					if ($q1 != $q1b)
					{
						//ODD NUMBER
						$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
						$result=db_select_limit_assoc($query, $q1c, 2) or safe_die("1st Quartile query failed<br />".$connect->ErrorMsg());

						while ($row=$result->FetchRow())
						{
							if ($total == 0)    {$total=$total-$row[$fieldname];}
								
							else                {$total=$total+$row[$fieldname];}
								
							$lastnumber=$row[$fieldname];
						}

						$q1total=$lastnumber-(1-($total*$q1diff));

						if ($q1total < $minimum) {$q1total=$minimum;}

						$showem[]=array($clang->gT("1st quartile (Q1)"), $q1total);
					}
					else
					{
						//EVEN NUMBER
						$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
						$result=db_select_limit_assoc($query,1, $q1c) or safe_die ("1st Quartile query failed<br />".$connect->ErrorMsg());

						while ($row=$result->FetchRow())
						{
							$showem[]=array("1st Quartile (Q1)", $row[$fieldname]);
						}
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
						$result=db_select_limit_assoc($query,2, $medianc) or safe_die("What a complete mess with the remainder<br />$query<br />".$connect->ErrorMsg());

						while
						(
						$row=$result->FetchRow()) {$total=$total+$row[$fieldname];
						}

						$showem[]=array($clang->gT("2nd quartile (Median)"), $total/2);
					}
						
					else
					{
						//EVEN NUMBER
						$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1 ";
						$result=db_select_limit_assoc($query,1, $medianc) or safe_die("What a complete mess<br />$query<br />".$connect->ErrorMsg());

						while ($row=$result->FetchRow())
						{
							$showem[]=array("Median Value", $row[$fieldname]);
						}
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
						$result = db_select_limit_assoc($query,2,$q3c) or safe_die("3rd Quartile query failed<br />".$connect->ErrorMsg());

						$lastnumber='';

						while ($row=$result->FetchRow())
						{
							if ($total == 0)    {$total=$total-$row[$fieldname];}
								
							else                {$total=$total+$row[$fieldname];}
								
							if (!$lastnumber) {$lastnumber=$row[$fieldname];}
						}
						$q3total=$lastnumber+($total*$q3diff);

						if ($q3total < $maximum) {$q1total=$maximum;}

						$showem[]=array($clang->gT("3rd quartile (Q3)"), $q3total);
					}
						
					else
					{
						$query = $querystarter . " ORDER BY ".db_quote_id($fieldname)."*1";
						$result = db_select_limit_assoc($query,1, $q3c) or safe_die("3rd Quartile even query failed<br />".$connect->ErrorMsg());

						while ($row=$result->FetchRow())
						{
							$showem[]=array("3rd Quartile (Q3)", $row[$fieldname]);
						}
					}
						
					$total=0;
						
					$showem[]=array($clang->gT("Maximum"), $maximum);
						
					//output results
					foreach ($showem as $shw)
					{
						$statisticsoutput .= "\t<tr>\n"
						."\t\t<td align='center' >$shw[0]</td>\n"
						."\t\t<td align='center' >$shw[1]</td>\n"
						."\t</tr>\n";
					}
						
					//footer of question type "N"
					$statisticsoutput .= "\t<tr>\n"
					."\t\t<td colspan='4' align='center' bgcolor='#EEEEEE'>\n"
					."\t\t\t<font size='1'>".$clang->gT("Null values are ignored in calculations")."<br />\n"
					."\t\t\t".sprintf($clang->gT("Q1 and Q3 calculated using %s"), "<a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>".$clang->gT("minitab method")."</a>")
					."</font>\n"
					."\t\t</td>\n"
					."\t</tr>\n</table>\n";
						
					//clean up
					unset($showem);
						
				}	//end if (enough results?)

				//not enough (<1) results for calculation
				else
				{
					//output
					$statisticsoutput .= "\t<tr>\n"
					."\t\t<td align='center'  colspan='4'>".$clang->gT("Not enough values for calculation")."</td>\n"
					."\t</tr>\n</table><br />\n";
					unset($showem);
				}

			}	//end else -> check last character, greater/less/equals don't need special treatment
				
		}	//end else-if -> multiple numerical types

		//is there some "id", "datestamp" or "D" within the type?
		elseif (substr($rt, 0, 2) == "id" || substr($rt, 0, 9) == "datestamp" || ($firstletter == "D"))
		{
			/*
			 * DON'T show anything for date questions
			 * because there aren't any statistics implemented yet!
			 *
			 * See bug report #2539 and
			 * feature request #2620
			 */
		}


		// NICE SIMPLE SINGLE OPTION ANSWERS
		else
		{
			//get database fields for this survey
			$fieldmap=createFieldMap($surveyid, "full");
				
			//search for key
			$fielddata=arraySearchByKey($rt, $fieldmap, "fieldname", 1);
				
			//get SGQA IDs
			$qsid=$fielddata['sid'];
			$qgid=$fielddata['gid'];
			$qqid=$fielddata['qid'];
			$qanswer=$fielddata['aid'];
				
			//question type
			$qtype=$fielddata['type'];

			//question string
			$qastring=$fielddata['question'];

			//question ID
			$rqid=$qqid;
				
			//get question data
			$nquery = "SELECT title, type, question, qid, lid, lid1, other FROM ".db_table_name("questions")." WHERE qid='{$rqid}' AND language='{$language}'";
			$nresult = db_execute_num($nquery) or safe_die ("Couldn't get question<br />$nquery<br />".$connect->ErrorMsg());
				
			//loop though question data
			while ($nrow=$nresult->FetchRow())
			{
				$qtitle=FlattenText($nrow[0]);
				$qtype=$nrow[1];
				$qquestion=FlattenText($nrow[2]);
				$qiqid=$nrow[3];
				$qlid=$nrow[4];
				$qlid1=$nrow[5];
				$qother=$nrow[6];
			}
				
			//check question types
			switch($qtype)
			{
				//Array of 5 point choices (several items to rank!)
				case "A":

					//get data
					$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
					$qresult=db_execute_num($qquery) or safe_die ("Couldn't get answer details (Array 5p Q)<br />$qquery<br />".$connect->ErrorMsg());

					//loop through results
					while ($qrow=$qresult->FetchRow())
					{
						//5-point array
						for ($i=1; $i<=5; $i++)
						{
							//add data
							$alist[]=array("$i", "$i");
						}
						//add counter
						$atext=FlattenText($qrow[1]);
					}

					//list IDs and answer codes in brackets
					$qquestion .= "<br />\n[".$atext."]";
					$qtitle .= "($qanswer)";
					break;



					//Array of 10 point choices
					//same as above just with 10 items
				case "B":
					$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
					$qresult=db_execute_num($qquery) or safe_die ("Couldn't get answer details (Array 10p Q)<br />$qquery<br />".$connect->ErrorMsg());
					while ($qrow=$qresult->FetchRow())
					{
						for ($i=1; $i<=10; $i++)
						{
							$alist[]=array("$i", "$i");
						}
						$atext=FlattenText($qrow[1]);
					}

					$qquestion .= "<br />\n[".$atext."]";
					$qtitle .= "($qanswer)";
					break;



					//Array of Yes/No/$clang->gT("Uncertain")
				case "C":
					$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
					$qresult=db_execute_num($qquery) or safe_die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());

					//loop thorugh results
					while ($qrow=$qresult->FetchRow())
					{
						//add results
						$alist[]=array("Y", $clang->gT("Yes"));
						$alist[]=array("N", $clang->gT("No"));
						$alist[]=array("U", $clang->gT("Uncertain"));
						$atext=FlattenText($qrow[1]);
					}
					//output
					$qquestion .= "<br />\n[".$atext."]";
					$qtitle .= "($qanswer)";
					break;



					//Array of Yes/No/$clang->gT("Uncertain")
					//same as above
				case "E":
					$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
					$qresult=db_execute_num($qquery) or safe_die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());
					while ($qrow=$qresult->FetchRow())
					{
						$alist[]=array("I", $clang->gT("Increase"));
						$alist[]=array("S", $clang->gT("Same"));
						$alist[]=array("D", $clang->gT("Decrease"));
						$atext=FlattenText($qrow[1]);
					}
					$qquestion .= "<br />\n[".$atext."]";
					$qtitle .= "($qanswer)";
					break;


				case ";": //Array (Multi Flexi) (Text)
					list($qacode, $licode)=explode("_", $qanswer);

					$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qacode' AND language='{$language}' ORDER BY sortorder, answer";
					//echo $qquery."<br />";
					$qresult=db_execute_num($qquery) or die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());

					while ($qrow=$qresult->FetchRow())
					{
						$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$qlid}' AND code = '{$licode}' AND language='{$language}'ORDER BY sortorder, code";
						$fresult = db_execute_assoc($fquery);
						while ($frow=$fresult->FetchRow())
						{
							$alist[]=array($frow['code'], $frow['title']);
							$ltext=$frow['title'];
						}
						$atext=FlattenText($qrow[1]);
					}

					$qquestion .= "<br />\n[".$atext."] [".$ltext."]";
					$qtitle .= "($qanswer)";
					break;


				case ":": //Array (Multiple Flexi) (Numbers)
					$qidattributes=getQuestionAttributes($qiqid);
					if ($maxvalue=arraySearchByKey("multiflexible_max", $qidattributes, "attribute", 1)) {
						$maxvalue=$maxvalue['value'];
					}
					else {
						$maxvalue=10;
					}

					if ($minvalue=arraySearchByKey("multiflexible_min", $qidattributes, "attribute", 1)) {
						$minvalue=$minvalue['value'];
					}
					else {
						$minvalue=1;
					}
					 
					if ($stepvalue=arraySearchByKey("multiflexible_step", $qidattributes, "attribute", 1)) {
						$stepvalue=$stepvalue['value'];
					}
					else {
						$stepvalue=1;
					}
					 
					if (arraySearchByKey("multiflexible_checkbox", $qidattributes, "attribute", 1)) {
						$minvalue=0;
						$maxvalue=1;
						$stepvalue=1;
					}

					list($qacode, $licode)=explode("_", $qanswer);

					$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qacode' AND language='{$language}' ORDER BY sortorder, answer";
					//echo $qquery."<br />";
					$qresult=db_execute_num($qquery) or die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());

					while ($qrow=$qresult->FetchRow())
					{
						$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$qlid}' AND code = '{$licode}' AND language='{$language}'ORDER BY sortorder, code";
						$fresult = db_execute_assoc($fquery);
						while ($frow=$fresult->FetchRow())
						{
							//$alist[]=array($frow['code'], $frow['title']);
							$ltext=$frow['title'];
						}
						$atext=FlattenText($qrow[1]);
					}

					for($i=$minvalue; $i<=$maxvalue; $i+=$stepvalue)
					{
						$alist[]=array($i, $i);
					}

					$qquestion .= "<br />\n[".$atext."] [".$ltext."]";
					list($myans, $mylabel)=explode("_", $qanswer);
					$qtitle .= "[$myans][$mylabel]";
					break;

				case "F": //Array of Flexible
				case "H": //Array of Flexible by Column
					$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qiqid' AND code='$qanswer' AND language='{$language}' ORDER BY sortorder, answer";
					$qresult=db_execute_num($qquery) or safe_die ("Couldn't get answer details<br />$qquery<br />".$connect->ErrorMsg());

					//loop through answers
					while ($qrow=$qresult->FetchRow())
					{
						//this question type uses its own labels
						$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$qlid}' AND language='{$language}'ORDER BY sortorder, code";
						$fresult = db_execute_assoc($fquery);
							
						//add code and title to results for outputting them later
						while ($frow=$fresult->FetchRow())
						{
							$alist[]=array($frow['code'], FlattenText($frow['title']));
						}
							
						//counter
						$atext=FlattenText($qrow[1]);
					}

					//output
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
					{
						$alist[]=array($availlang, getLanguageNameFromCode($availlang,false));
					}
					break;



				case "5": //5 Point (just 1 item to rank!)
					for ($i=1; $i<=5; $i++)
					{
						$alist[]=array("$i", "$i");
					}
					break;


						
				case "W":	//List felixble labels (dropdown)
					
				case "Z":	//List flexible labels (radio)
						
					//get labels
					$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$qlid}' AND language='{$language}' ORDER BY sortorder, code";
					$fresult = db_execute_assoc($fquery);

					//put label code and label title into array
					while ($frow=$fresult->FetchRow())
					{
						$alist[]=array($frow['code'], FlattenText($frow['title']));
					}

					//does "other" field exist?
					if ($qother == "Y")
					{
						$alist[]=array($clang->gT("Other"),$clang->gT("Other"),$fielddata['fieldname'].'other');
					}
					break;




				case "1":	//array flexible labels (dual scale)
					 
					//get question attributes
					$qidattributes=getQuestionAttributes($qqid);

					//check last character -> label 1
					if (substr($rt,-1,1) == 0)
					{
						//get label 1
						$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$qlid}' AND language='{$language}' ORDER BY sortorder, code";

						//header available?
						if ($dsheaderA=arraySearchByKey("dualscale_headerA", $qidattributes, "attribute", 1))
						{
							//output
							$labelheader= "[".$dsheaderA['value']."]";
						}

						//no header
						else
						{
							$labelheader ='';
						}

						//output
						$labelno = "Label 1";
					}

					//label 2
					else
					{
						//get label 2
						$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$qlid1}' AND language='{$language}' ORDER BY sortorder, code";

						//header available?
						if ($dsheaderB=arraySearchByKey("dualscale_headerB", $qidattributes, "attribute", 1))
						{
							//output
							$labelheader= "[" . $dsheaderB['value'] . "]";
						}

						//no header
						else
						{
							$labelheader ='';
						}

						//output
						$labelno = "Label 2";
					}

					//get data
					$fresult = db_execute_assoc($fquery);

					//put label code and label title into array
					while ($frow=$fresult->FetchRow())
					{
						$alist[]=array($frow['code'], FlattenText($frow['title']));
					}

					//adapt title and question
					$qtitle = $qtitle." [".$qanswer."][".$labelno."]";
					$qquestion  = $qastring .$labelheader;
					break;




				default:	//default handling

					//get answer code and title
					$qquery = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$qqid' AND language='{$language}' ORDER BY sortorder, answer";
					$qresult = db_execute_num($qquery) or safe_die ("Couldn't get answers list<br />$qquery<br />".$connect->ErrorMsg());

					//put answer code and title into array
					while ($qrow=$qresult->FetchRow())
					{
						$alist[]=array("$qrow[0]", FlattenText($qrow[1]));
					}

					//handling for "other" field for list radio or list drowpdown
					if (($qtype == "L" || $qtype == "!") && $qother == "Y")
					{
						//add "other"
						$alist[]=array($clang->gT("Other"),$clang->gT("Other"),$fielddata['fieldname'].'other');
					}

			}	//end switch question type
				
			//moved because it's better to have "no answer" at the end of the list instead of the beginning
			//put data into array
			$alist[]=array("", $clang->gT("No answer"));
				
		}	//end else -> single option answers

		//foreach ($alist as $al) {$statisticsoutput .= "$al[0] - $al[1]<br />";} //debugging line
		//foreach ($fvalues as $fv) {$statisticsoutput .= "$fv | ";} //debugging line





		//2. Collect and Display results #######################################################################
		if (isset($alist) && $alist) //Make sure there really is an answerlist, and if so:
		{
			//output
			$statisticsoutput .= "<table class='statisticstable'>\n"
			."\t<thead><tr><th colspan='4' align='center'><strong>"
			
			//headline
			.sprintf($clang->gT("Field summary for %s"),$qtitle)."</strong>"
			."</th></tr>\n"
			."\t<tr><th colspan='4' align='center'><strong>"
			
			//question title
			.$qquestion."</strong></th></tr>\n"
			."\t<tr>\n\t\t<th width='50%' align='center' >";
				
			// this will count the answers considered completed
			$TotalCompleted = 0;
				
			//loop thorugh the array which contains all answer data
			foreach ($alist as $al)
			{
				//picks out alist that come from the multiple list above
				if (isset($al[2]) && $al[2])
				{
					//handling for "other" option
					if ($al[1] == $clang->gT("Other"))
					{
						//get data
						$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE ";
						$query .= ($connect->databaseType == "mysql")?  db_quote_id($al[2])." != ''" : "NOT (".db_quote_id($al[2])." LIKE '')";
					}
						
					/*
					 * text questions:
					 *
					 * U = huge free text
					 * T = long free text
					 * S = short free text
					 * Q = multiple short text
					 */

					elseif ($qtype == "U" || $qtype == "T" || $qtype == "S" || $qtype == "Q" || $qtype == ";")
					{
						//free text answers
						if($al[0]=="Answers")
						{
							$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE ";
							$query .= ($connect->databaseType == "mysql")?  db_quote_id($al[2])." != ''" : "NOT (".db_quote_id($al[2])." LIKE '')";
						}
						//"no answer" handling
						elseif($al[0]=="NoAnswer")
						{
							$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE (".db_quote_id($al[2])." IS NULL OR ";
							$query .= ($connect->databaseType == "mysql")?  db_quote_id($al[2])." = '')" : " (".db_quote_id($al[2])." LIKE ''))";
						}
					}
					// all other question types
					else
					{
						$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($al[2])." =";

						//ranking question?
						if (substr($rt, 0, 1) == "R")
						{
							$query .= " '$al[0]'";
						}
						else
						{
							$query .= " 'Y'";
						}
					}
						
				}	//end if -> alist set

				else
				{
					//get more data

					if ($connect->databaseType == 'odbc_mssql' || $connect->databaseType == 'odbtp' || $connect->databaseType == 'mssql_n')
					{
						// mssql cannot compare text blobs so we have to cast here
						$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE cast(".db_quote_id($rt)." as varchar)= '$al[0]'";
					}
					else
					$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid")." WHERE ".db_quote_id($rt)." = '$al[0]'";

				}

				//check filter option
				if (incompleteAnsFilterstate() == "inc") {$query .= " AND submitdate is null";}
				elseif (incompleteAnsFilterstate() == "filter") {$query .= " AND submitdate is not null";}

				//check for any "sql" that has been passed from another script
				if ($sql != "NULL") {$query .= " AND $sql";}

				//get data
				$result=db_execute_num($query) or safe_die ("Couldn't do count of values<br />$query<br />".$connect->ErrorMsg());

				// $statisticsoutput .= "\n<!-- ($sql): $query -->\n\n";

				// this just extracts the data, after we present
				while ($row=$result->FetchRow())
				{
					//increase counter
					$TotalCompleted += $row[0];

					//"no answer" handling
					if ($al[0] == "")
					{$fname=$clang->gT("No answer");}
						
					//"other" handling
					//"Answers" means that we show an option to list answer to "other" text field
					elseif ($al[0] == $clang->gT("Other") || $al[0] == "Answers")
					{$fname="$al[1] <input type='submit' value='".$clang->gT("Browse")."' onclick=\"window.open('admin.php?action=listcolumn&sid=$surveyid&amp;column=$al[2]&amp;sql=".urlencode($sql)."', 'results', 'width=460, height=500, left=50, top=50, resizable=yes, scrollbars=yes, menubar=no, status=no, location=no, toolbar=no')\" />";}
						
					/*
					 * text questions:
					 *
					 * U = huge free text
					 * T = long free text
					 * S = short free text
					 * Q = multiple short text
					 */
					elseif ($qtype == "S" || $qtype == "U" || $qtype == "T" || $qtype == "Q")
					{
						//show free text answers
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
						
						
					//check if aggregated results should be shown
					elseif ($showaggregateddata == 1 && isset($showaggregateddata))
					{
						if(!isset($showheadline) || $showheadline != false)
						{
							if($qtype == "5" || $qtype == "A")
							{
								//four columns
								$statisticsoutput .= "<strong>".$clang->gT("Answer")."</strong></th>\n"
								."\t\t<th width='15%' align='center' >"
								."<strong>".$clang->gT("Count")."</strong></th>\n"
								."\t\t<th width='20%' align='center' >"
								."<strong>".$clang->gT("Percentage")."</strong></th>\n"
								."\t\t<th width='15%' align='center' >"
								."<strong>".$clang->gT("Sum")."</strong></th>\n"
								."\t</tr></thead>\n";

								$showheadline = false;
							}
							else
							{
								//three columns
								$statisticsoutput .= "<strong>".$clang->gT("Answer")."</strong></td>\n"
								."\t\t<th width='25%' align='center' >"
								."<strong>".$clang->gT("Count")."</strong></th>\n"
								."\t\t<th width='25%' align='center' >"
								."<strong>".$clang->gT("Percentage")."</strong></th>\n"
								."\t</tr></thead>\n";

								$showheadline = false;
							}
								
						}

						//text for answer column is always needed
						$fname="$al[1] ($al[0])";

						//these question types get special treatment by $showaggregateddata
						if($qtype == "5" || $qtype == "A")
						{
							//put non-edited data in here because $row will be edited later
							$grawdata[]=$row[0];
								
							//keep in mind that we already added data (will be checked later)
							$justadded = true;
								
							//we need a counter because we want to sum up certain values
							//reset counter if 5 items have passed
							if(!isset($testcounter) || $testcounter >= 4)
							{
								$testcounter = 0;
							}
							else
							{
								$testcounter++;
							}
								
							//beside the known percentage value a new aggregated value should be shown
							//therefore this item is marked in a certain way
								
							if($testcounter == 0 )	//add 300 to original value
							{
								//HACK: add three times the total number of results to the value
								//This way we get a 300 + X percentage which can be checked later
								$row[0] += (3*$results);
							}
								
							//the third value should be shown twice later -> mark it
							if($testcounter == 2)	//add 400 to original value
							{
								//HACK: add four times the total number of results to the value
								//This way there should be a 400 + X percentage which can be checked later
								$row[0] += (4*$results);
							}
								
							//the last value aggregates the data of item 4 + item 5 later
							if($testcounter == 4 )	//add 200 to original value
							{
								//HACK: add two times the total number of results to the value
								//This way there should be a 200 + X percentage which can be checked later
								$row[0] += (2*$results);
							}
								
						}	//end if -> question type = "5"/"A"
							
					}	//end if -> show aggregated data
						
					//handling what's left
					else
					{
						if(!isset($showheadline) || $showheadline != false)
						{
							//three columns
							$statisticsoutput .= "<strong>".$clang->gT("Answer")."</strong></th>\n"
							."\t\t<th width='25%' align='center' >"
							."<strong>".$clang->gT("Count")."</strong></th>\n"
							."\t\t<th width='25%' align='center' >"
							."<strong>".$clang->gT("Percentage")."</strong></th>\n"
							."\t</tr></thead>\n";

							$showheadline = false;
						}
						//answer text
						$fname="$al[1] ($al[0])";
					}
						
					//are there some results to play with?
					if ($results > 0)
					{
						//calculate percentage
						$gdata[] = ($row[0]/$results)*100;
					}
					//no results
					else
					{
						//no data!
						$gdata[] = "N/A";
					}
						
					//only add this if we don't handle question type "5"/"A"
					if(!isset($justadded))
					{
						//put absolute data into array
						$grawdata[]=$row[0];
					}
					else
					{
						//unset to handle "no answer" data correctly
						unset($justadded);
					}
						
					//put question title and code into array
					$label[]=$fname;

					//put only the code into the array
					$justcode[]=$al[0];
						
					//edit labels and put them into antoher array
					$lbl[] = wordwrap(FlattenText("$al[1] ($row[0])"), 25, "\n"); // NMO 2009-03-24
					$lblrtl[] = utf8_strrev(wordwrap(FlattenText("$al[1] )$row[0]("), 25, "\n")); // NMO 2009-03-24

				}	//end while -> loop through results

			}	//end foreach -> loop through answer data

			//no filtering of incomplete answers and NO multiple option questions
			if ((incompleteAnsFilterstate() != "filter") and ($qtype != "M") and ($qtype != "P"))
			{
				//is the checkbox "Don't consider NON completed responses (only works when Filter incomplete answers is Disable)" checked?
				if (isset($_POST["noncompleted"]) and ($_POST["noncompleted"] == "on") && (isset($showaggregateddata) && $showaggregateddata == 0))
				{
					//counter
					$i=0;

					while (isset($gdata[$i]))
					{
						//we want to have some "real" data here
						if ($gdata[$i] != "N/A")
						{
							//calculate percentage
							$gdata[$i] = ($grawdata[$i]/$TotalCompleted)*100;
						}

						//increase counter
						$i++;

					}	//end while (data available)
						
				}	//end if -> noncompleted checked
					
				//noncompleted is NOT checked
				else
				{
					//calculate total number of incompleted records
					$TotalIncomplete = $results - $TotalCompleted;
					 
					//output
					$fname=$clang->gT("Non completed");
					 
					//we need some data
					if ($results > 0)
					{
						//calculate percentage
						$gdata[] = ($TotalIncomplete/$results)*100;
					}
					 
					//no data :(
					else
					{
						$gdata[] = "N/A";
					}
					 
					//put data of incompleted records into array
					$grawdata[]=$TotalIncomplete;
					 
					//put question title ("Not completed") into array
					$label[]= $fname;
					 
					//put the code ("Not completed") into the array
					$justcode[]=$fname;
					 
					//edit labels and put them into antoher array
					$lbl[] = wordwrap(FlattenText($clang->gT("Non completed")." ($TotalIncomplete)"), 20, "\n"); // NMO 2009-03-24
				}	//end else -> noncompleted NOT checked
				 
			}	//end if -> no filtering of incomplete answers and no multiple option questions


			//counter
			$i=0;

			//we need to know which item we are editing
			$itemcounter = 1;
			 
			//array to store items 1 - 5 of question types "5" and "A"
			$stddevarray = array();
				
			//loop through all available answers
			while (isset($gdata[$i]))
			{
				//repeat header (answer, count, ...) for each new question
				unset($showheadline);
				 
				 
				/*
				 * there are 3 colums:
				 *
				 * 1 (50%) = answer (title and code in brackets)
				 * 2 (25%) = count (absolute)
				 * 3 (25%) = percentage
				 */
				$statisticsoutput .= "\t<tr>\n\t\t<td align='center' >" . $label[$i] ."\n"
				."\t\t</td>\n"
                
				//output absolute number of records
				."\t\t<td align='center' >" . $grawdata[$i] . "\n";


				//no data
				if ($gdata[$i] == "N/A")
				{
					//output when having no data
					$statisticsoutput .= "\t\t</td><td  align='center' >";
					 
					//percentage = 0
					$statisticsoutput .= sprintf("%01.2f", $gdata[$i]) . "%";
					$gdata[$i] = 0;

					//check if we have to adjust ouput due to $showaggregateddata setting
					if($showaggregateddata == 1 && isset($showaggregateddata) && ($qtype == "5" || $qtype == "A"))
					{
						$statisticsoutput .= "\t\t</td><td>";
					}
				}

				//data available
				else
				{
					//check if data should be aggregated
					if($showaggregateddata == 1 && isset($showaggregateddata) && ($qtype == "5" || $qtype == "A"))
					{
						//mark that we have done soemthing special here
						$aggregated = true;

						//just calculate everything once. the data is there in the array
						if($itemcounter == 1)
						{
							//there are always 5 answers
							for($x = 0; $x < 5; $x++)
							{
								//put 5 items into array for further calculations
								array_push($stddevarray, $grawdata[$x]);
							}
						}

						//"no answer" & items 2 / 4 - nothing special to do here, just adjust output
						if($gdata[$i] <= 100)
						{
							if($itemcounter == 2 && $label[$i+4] == $clang->gT("No answer"))
							{
								//prevent division by zero
								if(($results - $grawdata[$i+4]) > 0)
								{
									//re-calculate percentage
									$percentage = ($grawdata[$i] / ($results - $grawdata[$i+4])) * 100;
								}
								else
								{
									$percentage = 0;
								}

							}
							elseif($itemcounter == 4 && $label[$i+2] == $clang->gT("No answer"))
							{
								//prevent division by zero
								if(($results - $grawdata[$i+2]) > 0)
								{
									//re-calculate percentage
									$percentage = ($grawdata[$i] / ($results - $grawdata[$i+2])) * 100;
								}
								else
								{
									$percentage = 0;
								}
							}
							else
							{
								$percentage = $gdata[$i];
							}
							 
							//output
							$statisticsoutput .= "\t\t</td><td align='center'>";
							 
							//output percentage
							$statisticsoutput .= sprintf("%01.2f", $percentage) . "%";
							 
							//adjust output
							$statisticsoutput .= "\t\t</td><td>";
						}

						//item 3 - just show results twice
						//old: if($gdata[$i] >= 400)
						//trying to fix bug #2583:
						if($gdata[$i] >= 400 && $i != 0)
						{
							//remove "400" which was added before
							$gdata[$i] -= 400;
							 
							if($itemcounter == 3 && $label[$i+3] == $clang->gT("No answer"))
							{
								//prevent division by zero
								if(($results - $grawdata[$i+3]) > 0)
								{
									//re-calculate percentage
									$percentage = ($grawdata[$i] / ($results - $grawdata[$i+3])) * 100;
								}
								else
								{
									$percentage = 0;
								}
							}
							else
							{
								//get the original percentage
								$percentage = $gdata[$i];
							}
							 
							//output percentage
							$statisticsoutput .= "\t\t</td><td align='center' >";
							$statisticsoutput .= sprintf("%01.2f", $percentage) . "%";
								
							//output again (no real aggregation here)
							$statisticsoutput .= "\t\t</td><td align='center' >";
							$statisticsoutput .= sprintf("%01.2f", $percentage)."%";
							$statisticsoutput .= "\t\t";
						}

						//FIRST value -> add percentage of item 1 + item 2
						//old: if($gdata[$i] >= 300 && $gdata[$i] < 400)
						//trying to fix bug #2583:
						if(($gdata[$i] >= 300 && $gdata[$i] < 400) || ($i == 0 && $gdata[$i] <= 400))
						{
							//remove "300" which was added before
							$gdata[$i] -= 300;
							 
							if($itemcounter == 1 && $label[$i+5] == $clang->gT("No answer"))
							{
								//prevent division by zero
								if(($results - $grawdata[$i+5]) > 0)
								{
									//re-calculate percentage
									$percentage = ($grawdata[$i] / ($results - $grawdata[$i+5])) * 100;
									$percentage2 = ($grawdata[$i + 1] / ($results - $grawdata[$i+5])) * 100;
								}
								else
								{
									$percentage = 0;
									$percentage2 = 0;
	         
								}
							}
							else
							{
								$percentage = $gdata[$i];
								$percentage2 = $gdata[$i+1];
							}
							 
							//percentage of item 1 + item 2
							$aggregatedgdata = $percentage + $percentage2;
							 
							//output percentage
							$statisticsoutput .= "\t\t</td><td align='center' >";
							$statisticsoutput .= sprintf("%01.2f", $percentage) . "%";
								
							//output aggregated data
							$statisticsoutput .= "\t\t</td><td align='center' >";
							$statisticsoutput .= sprintf("%01.2f", $aggregatedgdata)."%";
							$statisticsoutput .= "\t\t";
						}

						//LAST value -> add item 4 + item 5
						if($gdata[$i] > 100 && $gdata[$i] < 300)
						{
							//remove "200" which was added before
							$gdata[$i] -= 200;
							 
							if($itemcounter == 5 && $label[$i+1] == $clang->gT("No answer"))
							{
								//prevent division by zero
								if(($results - $grawdata[$i+1]) > 0)
								{
									//re-calculate percentage
									$percentage = ($grawdata[$i] / ($results - $grawdata[$i+1])) * 100;
									$percentage2 = ($grawdata[$i - 1] / ($results - $grawdata[$i+1])) * 100;
								}
								else
								{
									$percentage = 0;
									$percentage2 = 0;
								}
							}
							else
							{
								$percentage = $gdata[$i];
								$percentage2 = $gdata[$i-1];
							}
							 
							//item 4 + item 5
							$aggregatedgdata = $percentage + $percentage2;
							 
							//output percentage
							$statisticsoutput .= "\t\t</td><td align='center' >";
							$statisticsoutput .= sprintf("%01.2f", $percentage) . "%";
								
							//output aggregated data
							$statisticsoutput .= "\t\t</td><td align='center' >";
							$statisticsoutput .= sprintf("%01.2f", $aggregatedgdata)."%";
							$statisticsoutput .= "\t\t";
							 
							// create new row "sum"
							//calculate sum of items 1-5
							$sumitems = $grawdata[$i]
							+ $grawdata[$i-1]
							+ $grawdata[$i-2]
							+ $grawdata[$i-3]
							+ $grawdata[$i-4];
							 
							//special treatment for zero values
							if($sumitems > 0)
							{
								$sumpercentage = "100.00";
							}
							else
							{
								$sumpercentage = "0";
							}
							//special treatment for zero values
							if($TotalCompleted > 0)
							{
								$casepercentage = "100.00";
							}
							else
							{
								$casepercentage = "0";
							}
							 
							$statisticsoutput .= "\t\t&nbsp</td>\n\t</tr>\n";
							$statisticsoutput .= "<tr><td align='center'><strong>".$clang->gT("Sum")." (".$clang->gT("Answers").")</strong></td>";
							$statisticsoutput .= "<td align='center' ><strong>".$sumitems."</strong></td>";
							$statisticsoutput .= "<td align='center' ><strong>$sumpercentage%</strong></td>";
							$statisticsoutput .= "<td align='center' ><strong>$sumpercentage%</strong></td>";
							 
							$statisticsoutput .= "\t\t&nbsp</td>\n\t</tr>\n";
							$statisticsoutput .= "<tr><td align='center'>".$clang->gT("Number of cases")."</td>";	//German: "Fallzahl"
							$statisticsoutput .= "<td align='center' >".$TotalCompleted."</td>";
							$statisticsoutput .= "<td align='center' >$casepercentage%</td>";
							//there has to be a whitespace within the table cell to display correctly
							$statisticsoutput .= "<td align='center' >&nbsp</td></tr>";
							 
						}

					}	//end if -> show aggregated data
					 
					//don't show aggregated data
					else
					{
						//output percentage
						$statisticsoutput .= "\t\t</td><td align='center' >";
						$statisticsoutput .= sprintf("%01.2f", $gdata[$i]) . "%";
						$statisticsoutput .= "\t\t";
					}
					 
				}	//end else -> $gdata[$i] != "N/A"

				//end output per line. there has to be a whitespace within the table cell to display correctly
				$statisticsoutput .= "\t\t&nbsp</td>\n\t</tr>\n";

				//increase counter
				$i++;

				$itemcounter++;

			}	//end while

			//only show additional values when this setting is enabled
			if($showaggregateddata == 1 && isset($showaggregateddata))
			{
				//it's only useful to calculate standard deviation and arithmetic means for question types
				//5 = 5 Point Scale
				//A = Array (5 Point Choice)
				if($qtype == "5" || $qtype == "A")
				{
					$stddev = 0;
					$am = 0;
					 
					//calculate arithmetic mean
					if(isset($sumitems) && $sumitems > 0)
					{
						 
						 
						//calculate and round results
						//there are always 5 items
						for($x = 0; $x < 5; $x++)
						{
							//create product of item * value
							$am += (($x+1) * $stddevarray[$x]);
						}
						 
						//prevent division by zero
						if(isset($stddevarray) && array_sum($stddevarray) > 0)
						{
							$am = round($am / array_sum($stddevarray),2);
						}
						else
						{
							$am = 0;
						}
						 
						//calculate standard deviation -> loop through all data
						/*
						 * four steps to calculate the standard deviation
						 * 1 = calculate difference between item and arithmetic mean and multiply with the number of elements
						 * 2 = create sqaure value of difference
						 * 3 = sum up square values
						 * 4 = multiply result with 1 / (number of items)
						 * 5 = get root
						 */
						 
						 
						 
						for($j = 0; $j < 5; $j++)
						{
							//1 = calculate difference between item and arithmetic mean
							$diff = (($j+1) - $am);

							//2 = create square value of difference
							$squarevalue = square($diff);

							//3 = sum up square values and multiply them with the occurence
							//prevent divison by zero
							if($squarevalue != 0 && $stddevarray[$j] != 0)
							{
								$stddev += $squarevalue * $stddevarray[$j];
							}
							 
						}
						 
						//4 = multiply result with 1 / (number of items (=5))
						//There are two different formulas to calculate standard derivation
						//$stddev = $stddev / array_sum($stddevarray);		//formula source: http://de.wikipedia.org/wiki/Standardabweichung
						 
						//prevent division by zero
						if((array_sum($stddevarray)-1) != 0 && $stddev != 0)
						{
							$stddev = $stddev / (array_sum($stddevarray)-1);	//formula source: http://de.wikipedia.org/wiki/Empirische_Varianz
						}
						else
						{
							$stddev = 0;
						}
						 
						//5 = get root
						$stddev = sqrt($stddev);
						$stddev = round($stddev,2);
					}

					//calculate standard deviation
					$statisticsoutput .= "<tr><td align='center'>".$clang->gT("Arithmetic mean")."</td>";	//German: "Fallzahl"
					$statisticsoutput .= "<td>&nbsp;</td><td align='center'> $am</td><td>&nbsp;</td></tr>";
					$statisticsoutput .= "<tr><td align='center'>".$clang->gT("Standard deviation")."</td>";    //German: "Fallzahl"
					$statisticsoutput .= "<td>&nbsp;</td><td align='center'>$stddev</td><td>&nbsp;</td></tr>";
				}
			}




			//-------------------------- PCHART OUTPUT ----------------------------

			//PCHART has to be enabled and we need some data
			if (isset($_POST['usegraph']) && array_sum($gdata)>0)
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
				//to send to pchart.

				$i = 0;
				foreach ($gdata as $data)
				{
					if ($data != 0){$i++;}
				}
				$totallines=$i;
				if ($totallines>15)
				{
					$gheight=320+(6.7*($totallines-15));
					$fontsize=7;
					$legendtop=0.01;
					$setcentrey=0.5/(($gheight/320));
				}
				else
				{
					$gheight=320;
					$fontsize=8;
					$legendtop=0.07;
					$setcentrey=0.5;
				}

				// Create bar chart for multiple options
				if ($qtype == "M" || $qtype == "P")
				{
					//new bar chart using data from array $grawdata which contains percentage

					$DataSet = new pData;
					$counter=0;
					$maxyvalue=0;
					foreach ($grawdata as $datapoint)
					{
						$DataSet->AddPoint(array($datapoint),"Serie$counter");
						$DataSet->AddSerie("Serie$counter");

						$counter++;
						if ($datapoint>$maxyvalue) $maxyvalue=$datapoint;
					}

					if ($maxyvalue<10) {++$maxyvalue;}
					$counter=0;
					foreach ($lbl as $label)
					{
						$DataSet->SetSerieName($label,"Serie$counter");
						$counter++;
					}

					if ($MyCache->IsInCache("graph".$surveyid,$DataSet->GetData()))
					{
						$cachefilename=basename($MyCache->GetFileFromCache("graph".$surveyid,$DataSet->GetData()));
					}
					else
					{
						$graph = new pChart(1,1);

						$graph->setFontProperties($rootdir."/fonts/".$chartfontfile, $chartfontsize);
						$legendsize=$graph->getLegendBoxSize($DataSet->GetDataDescription());
						 
						if ($legendsize[1]<320) $gheight=420; else $gheight=$legendsize[1]+100;
						$graph = new pChart(690+$legendsize[0],$gheight);
						$graph->loadColorPalette($homedir.'/styles/'.$admintheme.'/limesurvey.pal');
						$graph->setFontProperties($rootdir."/fonts/".$chartfontfile,$chartfontsize);
						$graph->setGraphArea(50,30,500,$gheight-60);
						$graph->drawFilledRoundedRectangle(7,7,523+$legendsize[0],$gheight-7,5,254,255,254);
						$graph->drawRoundedRectangle(5,5,525+$legendsize[0],$gheight-5,5,230,230,230);
						$graph->drawGraphArea(255,255,255,TRUE);
						$graph->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_START0,150,150,150,TRUE,90,0,TRUE,5,false);
						$graph->drawGrid(4,TRUE,230,230,230,50);
						// Draw the 0 line
						$graph->setFontProperties($rootdir."/fonts/".$chartfontfile,$chartfontsize);
						$graph->drawTreshold(0,143,55,72,TRUE,TRUE);

						// Draw the bar graph
						$graph->drawBarGraph($DataSet->GetData(),$DataSet->GetDataDescription(),FALSE);
						//$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie4","1","Important point!");
						// Finish the graph
						$graph->setFontProperties($rootdir."/fonts/".$chartfontfile, $chartfontsize);
						$graph->drawLegend(510,30,$DataSet->GetDataDescription(),255,255,255);

						$MyCache->WriteToCache("graph".$surveyid,$DataSet->GetData(),$graph);
						$cachefilename=basename($MyCache->GetFileFromCache("graph".$surveyid,$DataSet->GetData()));
					}
				}	//end if (bar chart)

				//Pie Chart
				else
				{
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

					$lblout=array();
					if (getLanguageRTL($language))
					{
						$lblout=$lblrtl;
					}
					else
					{
						$lblout=$lbl;
					}

					 
					//create new 3D pie chart
					$DataSet = new pData;
					$DataSet->AddPoint($gdata,"Serie1");
					$DataSet->AddPoint($lblout,"Serie2");
					$DataSet->AddAllSeries();
					$DataSet->SetAbsciseLabelSerie("Serie2");
						
					if ($MyCache->IsInCache("graph".$surveyid,$DataSet->GetData()))
					{
						$cachefilename=basename($MyCache->GetFileFromCache("graph".$surveyid,$DataSet->GetData()));
					}
					else
					{
							
						$gheight=ceil($gheight);
						$graph = new pChart(690,$gheight);
						$graph->loadColorPalette($homedir.'/styles/'.$admintheme.'/limesurvey.pal');
						$graph->drawFilledRoundedRectangle(7,7,687,$gheight-3,5,254,255,254);
						$graph->drawRoundedRectangle(5,5,689,$gheight-1,5,230,230,230);
							
						// Draw the pie chart
						$graph->setFontProperties($rootdir."/fonts/".$chartfontfile, $chartfontsize);
						$graph->drawPieGraph($DataSet->GetData(),$DataSet->GetDataDescription(),225,round($gheight/2),170,PIE_PERCENTAGE,TRUE,50,20,5);
						$graph->setFontProperties($rootdir."/fonts/".$chartfontfile,$chartfontsize);
						$graph->drawPieLegend(430,12,$DataSet->GetData(),$DataSet->GetDataDescription(),250,250,250);
						$MyCache->WriteToCache("graph".$surveyid,$DataSet->GetData(),$graph);
						$cachefilename=basename($MyCache->GetFileFromCache("graph".$surveyid,$DataSet->GetData()));
					}
						
				}	//end else -> pie charts

				//introduce new counter
				if (!isset($ci)) {$ci=0;}

				//increase counter, start value -> 1
				$ci++;

				//add graph to output
				$statisticsoutput .= "<tr><td colspan='4' style=\"text-align:center\"><img src=\"$tempurl/".$cachefilename."\" border='1'></td></tr>";
			}
				
			//close table/output
			$statisticsoutput .= "</table><br /> \n";
				
		}	//end if -> collect and display results

		//delete data
		unset($gdata);
		unset($grawdata);
		unset($label);
		unset($lbl);
		unset($lblout);
		unset($justcode);
		unset ($alist);

	}	// end foreach -> loop through all questions

	//output
	$statisticsoutput .= "<br />&nbsp\n";

}	//end if -> show summary results


//done! set progress bar to 100%
if (isset($prb))
{
	$prb->setLabelValue('txt1',$clang->gT('Completed'));
	$prb->moveStep(100);
	$prb->hide();
}


function showSpeaker($hinttext)
{
	global $clang, $imagefiles, $maxchars;

	if(!isset($maxchars))
	{
		$maxchars = 15;
	}
	$htmlhinttext=str_replace("'",'&#039;',$hinttext);  //the string is already HTML except for single quotes so we just replace these only
	$jshinttext=javascript_escape($hinttext,true,true);

	if(strlen($hinttext) > ($maxchars))
	{
		$shortstring = FlattenText($hinttext);

        $shortstring = htmlspecialchars(mb_strcut(html_entity_decode($shortstring,ENT_QUOTES,'UTF-8'), 0, $maxchars, 'UTF-8'));          

		//output with hoover effect
		$reshtml= "<span style='cursor: hand' alt='".$htmlhinttext."' title='".$htmlhinttext."' "
		." onclick=\"alert('".$clang->gT("Question","js").": $jshinttext')\" />"
		." \"$shortstring...\" </span>"
		."<img style='cursor: hand' src='$imagefiles/speaker.png' align='bottom' alt='$htmlhinttext' title='$htmlhinttext' "
		." onclick=\"alert('".$clang->gT("Question","js").": $jshinttext')\" />";
	}
	else
	{
		$reshtml= "<span alt='".$hinttext."' title='".$htmlhinttext."'> \"$htmlhinttext\"</span>";
	}
	return $reshtml;
}

//simple function to square a value
function square($number)
{
	if($number == 0)
	{
		$squarenumber = 0;
	}
	else
	{
		$squarenumber = $number * $number;
	}

	return $squarenumber;
}

?>
