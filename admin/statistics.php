<?php
/*
#############################################################
# >>> PHPSurveyor                                           #
#############################################################
# > Author:  Jason Cleeland                                 #
# > E-mail:  jason@cleeland.org                             #
# > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
# >          CARLTON SOUTH 3053, AUSTRALIA
# > Date:    20 February 2003                               #
#                                                           #
# This set of scripts allows you to develop, publish and    #
# perform data-entry on surveys.                            #
#############################################################
#                                                           #
#   Copyright (C) 2003  Jason Cleeland                      #
#                                                           #
# This program is free software; you can redistribute       #
# it and/or modify it under the terms of the GNU General    #
# Public License Version 2 as published by the Free         #
# Software Foundation.										#
#                                                           #
#                                                           #
# This program is distributed in the hope that it will be   #
# useful, but WITHOUT ANY WARRANTY; without even the        #
# implied warranty of MERCHANTABILITY or FITNESS FOR A      #
# PARTICULAR PURPOSE.  See the GNU General Public License   #
# for more details.                                         #
#                                                           #
# You should have received a copy of the GNU General        #
# Public License along with this program; if not, write to  #
# the Free Software Foundation, Inc., 59 Temple Place -     #
# Suite 330, Boston, MA  02111-1307, USA.                   #
#############################################################
*/
require_once(dirname(__FILE__).'/../config.php');

include_once("login_check.php");
$statisticsoutput ='';
if ($usejpgraph == 1 && isset($jpgraphdir)) //JPGRAPH CODING SUBMITTED BY Pieterjan Heyse
{
	require_once ("$jpgraphdir/jpgraph.php");
	require_once ("$jpgraphdir/jpgraph_pie.php");
	require_once ("$jpgraphdir/jpgraph_pie3d.php");
	require_once ("$jpgraphdir/jpgraph_bar.php");

    if (isset($jpgraphfontdir) && $jpgraphfontdir!="")
    {
    DEFINE("TTF_DIR",$jpgraphfontdir); // url of fonts files
    }

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

if (isset($_POST['summary']) && !is_array($_POST['summary'])) {
	$_POST['summary'] = explode("|", $_POST['summary']);
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

$statisticsoutput .= "<table width='99%' align='center' style='margin: 5px; border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>".$clang->gT("Quick Statistics")."</strong></font></td></tr>\n";
//Get the menubar
$statisticsoutput .= browsemenubar();
$statisticsoutput .= "</table>\n"
."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1'"
." cellspacing='0'>\n"
."<tr><td align='center' bgcolor='#555555' height='22'>"
."<input type='image' src='$imagefiles/plus.gif' align='right' onclick='show(\"filtersettings\")'><input type='image' src='$imagefiles/minus.gif' align='right' onclick='hide(\"filtersettings\")'>"
."<font size='2' face='verdana' color='#FF9900'><strong>".$clang->gT("Filter Settings")."</strong></font>"
."</td></tr>\n"
."</table>\n"
."<form method='post' name='formbuilder' action='$scriptname?action=statistics'>\n"
."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";

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
$statisticsoutput .= "<td align='center'>$setfont<strong>id</strong><br />";
$statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Number greater than").":<br />\n"
."\t\t\t\t\t<input type='text' name='$myfield2' value='";
if (isset($_POST[$myfield2])){$statisticsoutput .= $_POST[$myfield2];}
$statisticsoutput .= "'><br />\n"
."\t\t\t\t\t".$clang->gT("Number Less Than").":<br />\n"
."\t\t\t\t\t<input type='text' name='$myfield3' value='";
if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}
$statisticsoutput .= "'><br />\n";
$statisticsoutput .= "\t\t\t\t\t=<br />
            <input type='text' name='$myfield4' value='";
if (isset($_POST[$myfield4])) {$statisticsoutput .= $_POST[$myfield4];}
$statisticsoutput .= "'><br /></font></font></td>\n";
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
	$statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>$setfont<strong>datestamp</strong>"
	."<br />\n"
	."\t\t\t\t\t<font size='1'>".$clang->gT("Date (YYYY-MM-DD) equals").":<br />\n"
	."\t\t\t\t\t<input name='$myfield3' type='text' value='";
	if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}
	$statisticsoutput .= "' ".substr(2, 0, -13) ."; width:80'><br />\n"
	."\t\t\t\t\t&nbsp;&nbsp;".$clang->gT("OR between").":<br />\n"
	."\t\t\t\t\t<input name='$myfield4' value='";
	if (isset($_POST[$myfield4])) {$statisticsoutput .= $_POST[$myfield4];}
	$statisticsoutput .= "' type='text' ".substr(2, 0, -13)
	."; width:65'> ".$clang->gT("and")." <input  name='$myfield5' value='";
	if (isset($_POST[$myfield5])) {$statisticsoutput .= $_POST[$myfield5];}
	$statisticsoutput .= "' type='text' ".substr(2, 0, -13)
	."; width:65'></font></font>\n";
	$allfields[]=$myfield2;
	$allfields[]=$myfield3;
	$allfields[]=$myfield4;
	$allfields[]=$myfield5;
}
$statisticsoutput .= "</td></tr></table>";

// 2: Get answers for each question
if (!isset($currentgroup)) {$currentgroup="";}
foreach ($filters as $flt)
{
	if ($flt[1] != $currentgroup)
	{   //If the groupname has changed, start a new row
		if ($currentgroup)
		{
			//if we've already drawn a table for a group, and we're changing - close off table
			$statisticsoutput .= "\n\t\t\t\t</td></tr>\n\t\t\t</table>\n";
		}
		$statisticsoutput .= "\t\t<tr><td bgcolor='#CCCCCC' align='center'>\n"
		."\t\t<font size='1' face='verdana'><strong>$flt[4]</strong> (".$clang->gT("Group")." $flt[1])</font></td></tr>\n\t\t"
		."<tr><td align='center'>\n"
		."\t\t\t<table align='center'><tr>\n";
		$counter=0;
	}
	if (isset($counter) && $counter == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>"; $counter=0;}
	$myfield = "{$surveyid}X{$flt[1]}X{$flt[0]}";
	$niceqtext = FlattenText($flt[5]);
	//headings
	if ($flt[2] != "A" && $flt[2] != "B" && $flt[2] != "C" && $flt[2] != "E" && $flt[2] != "F" && $flt[2] != "H" && $flt[2] != "T" && $flt[2] != "U" && $flt[2] != "S" && $flt[2] != "D" && $flt[2] != "R" && $flt[2] != "Q" && $flt[2] != "X" && $flt[2] != "W" && $flt[2] != "Z") //Have to make an exception for these types!
	{
		$statisticsoutput .= "\t\t\t\t<td align='center'>"
		."$setfont<strong>$flt[3]&nbsp;"; //Heading (Question No)
		if ($flt[2] == "M" || $flt[2] == "P" || $flt[2] == "R" || $flt[2] == "J") {$myfield = "M$myfield";}
		if ($flt[2] == "N") {$myfield = "N$myfield";}
		$statisticsoutput .= "<input type='checkbox' name='summary[]' value='$myfield'";
		if (isset($_POST['summary']) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE  || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE))
		{$statisticsoutput .= " CHECKED";}
		$statisticsoutput .= ">&nbsp;"
		."<img src='$imagefiles/speaker.png' align='bottom' alt=\"".str_replace("\"", "`", $flt[5])."\" onclick=\"alert('".$clang->gT("Question","js").": ".$niceqtext."')\"></strong>"
		."<br />\n";
		if ($flt[2] == "N") {$statisticsoutput .= "</font>";}
		if ($flt[2] != "N") {$statisticsoutput .= "\t\t\t\t<select name='";}
		if ($flt[2] == "M" || $flt[2] == "P" || $flt[2] == "R" || $flt[2] == "J") {$statisticsoutput .= "M";}
		if ($flt[2] != "N") {$statisticsoutput .= "{$surveyid}X{$flt[1]}X{$flt[0]}[]' multiple 2>\n";}
		$allfields[]=$myfield;
	}
	$statisticsoutput .= "\t\t\t\t\t<!-- QUESTION TYPE = $flt[2] -->\n";
	switch ($flt[2])
	{
		case "Q":
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_assoc($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		$counter2=0;
		while ($row = $result->FetchRow())
		{
			$myfield2 = "Q".$myfield."$row[0]";
			if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
			$statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>$setfont<strong>$flt[3]-".$row[0]."</strong></font>";
			$statisticsoutput .= "<input type='checkbox' name='summary[]' value='$myfield2'";
			if (isset($_POST['summary']) && (array_search("Q{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}", $_POST['summary']) !== FALSE))
			{$statisticsoutput .= " CHECKED";}
			$statisticsoutput .= ">&nbsp;"
			."&nbsp;<img src='$imagefiles/speaker.png' align='bottom' alt=\""
			.str_replace("\"", "`", $flt[5])
			." [$flt[1]]\" onclick=\"alert('".$clang->gT("Question","js").": ".FlattenText($row[1])." "
			."')\">"
			."<br />\n"
			."\t\t\t\t\t<font size='1'>".$clang->gT("Responses Containing").":</font><br />\n"
			."\t\t\t\t\t<input type='text' name='$myfield2' value='";
			if (isset($_POST[$myfield2]))
			{$statisticsoutput .= $_POST[$myfield2];}
			$statisticsoutput .= "'>";
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
		."$setfont<strong>$flt[3]</strong></font>";
		$statisticsoutput .= "<input type='checkbox' name='summary[]' value='$myfield2'";
		if (isset($_POST['summary']) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE))
		{$statisticsoutput .= " CHECKED";}
		$statisticsoutput .= ">&nbsp;"
		."&nbsp;<img src='$imagefiles/speaker.png' align='bottom' alt=\""
		.str_replace("\"", "`", $flt[5])." \" "
		."onclick=\"alert('".$clang->gT("Question","js").": ".$niceqtext." "
		."')\">"
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
		."$setfont<strong>$flt[3]</strong></font>";
		$statisticsoutput .= "<input type='checkbox' name='summary[]' value='$myfield2'";
		if (isset($_POST['summary']) && (array_search("T{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE))
		{$statisticsoutput .= " CHECKED";}
		$statisticsoutput .= ">&nbsp;"
		."&nbsp;<img src='$imagefiles/speaker.png' align='bottom' alt=\""
		.str_replace("\"", "`", $flt[5])
		." [$flt[1]]\" onclick=\"alert('".$clang->gT("Question","js").": ".$niceqtext." "
		."')\">"
		."<br />\n"
		."\t\t\t\t\t<font size='1'>".$clang->gT("Responses Containing").":</font><br />\n"
		."\t\t\t\t\t<input type='text' name='$myfield2' value='";
		if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
		$statisticsoutput .= "'>";
		$allfields[]=$myfield2;
		break;
		case "N": // Numerical
		$myfield2="{$myfield}G";
		$myfield3="{$myfield}L";
		$statisticsoutput .= "\t\t\t\t\t<font size='1'>".$clang->gT("Number greater than").":</font><br />\n"
		."\t\t\t\t\t<input type='text' name='$myfield2' value='";
		if (isset($_POST[$myfield2])){$statisticsoutput .= $_POST[$myfield2];}
		$statisticsoutput .= "'><br />\n"
		."\t\t\t\t\t".$clang->gT("Number Less Than").":<br />\n"
		."\t\t\t\t\t<input type='text' name='$myfield3' value='";
		if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}
		$statisticsoutput .= "'><br />\n";
		$allfields[]=$myfield2;
		$allfields[]=$myfield3;
		break;
		case "D": // Date
		$myfield2="D$myfield";
		$myfield3="$myfield2=";
		$myfield4="$myfield2<"; $myfield5="$myfield2>";
		$statisticsoutput .= "\t\t\t\t<td align='center' valign='top'>$setfont<strong>$flt[3]</strong>"
		."&nbsp;<img src='$imagefiles/speaker.png' align='bottom' alt=\""
		.str_replace("\"", "`", $flt[5])
		." \" onclick=\"alert('".$clang->gT("Question","js").": ".$niceqtext." "
		."')\">"
		."<br />\n"
		."\t\t\t\t\t<font size='1'>".$clang->gT("Date (YYYY-MM-DD) equals").":<br />\n"
		."\t\t\t\t\t<input name='$myfield3' type='text' value='";
		if (isset($_POST[$myfield3])) {$statisticsoutput .= $_POST[$myfield3];}
		$statisticsoutput .= "' ".substr(2, 0, -13) ."; width:80'><br />\n"
		."\t\t\t\t\t&nbsp;&nbsp;".$clang->gT("OR between").":<br />\n"
		."\t\t\t\t\t<input name='$myfield4' value='";
		if (isset($_POST[$myfield4])) {$statisticsoutput .= $_POST[$myfield4];}
		$statisticsoutput .= "' type='text' ".substr(2, 0, -13)
		."; width:65'> ".$clang->gT("and")." <input  name='$myfield5' value='";
		if (isset($_POST[$myfield5])) {$statisticsoutput .= $_POST[$myfield5];}
		$statisticsoutput .= "' type='text' ".substr(2, 0, -13)
		."; width:65'></font></font>\n";
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

			$statisticsoutput .= "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])</B>"
			."<input type='checkbox' name='summary[]' value='$myfield2'";
			if (isset($_POST['summary']) && array_search($myfield2, $_POST['summary'])!== FALSE) {$statisticsoutput .= " CHECKED";}
			$statisticsoutput .= ">&nbsp;"
			."<img src='$imagefiles/speaker.png' align='bottom' alt=\""
			.str_replace("\"", "`", $flt[5])." [$row[1]]\" onclick=\"alert('".$clang->gT("Question","js").": "
			.$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
			."<br />\n"
			."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple 2>\n";
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

			$statisticsoutput .= "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])</B>"; //heading
			$statisticsoutput .= "<input type='checkbox' name='summary[]' value='$myfield2'";
			if (isset($_POST['summary']) && array_search($myfield2, $_POST['summary'])!== FALSE) {$statisticsoutput .= " CHECKED";}
			$statisticsoutput .= ">&nbsp;"
			."<img src='$imagefiles/speaker.png' align='bottom' alt=\""
			.str_replace("\"", "`", $flt[5])
			." [$row[1]]\" onclick=\"alert('".$clang->gT("Question","js").": ".$niceqtext." "
			.str_replace("'", "`", $row[1])
			."')\">"
			."<br />\n"
			."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple 2>\n";
			for ($i=1; $i<=10; $i++)
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
		case "C": // ARRAY OF YES\No\$clang->gT("Uncertain") QUESTIONS
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0][]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		$counter2=0;
		while ($row=$result->FetchRow())
		{
			$myfield2 = $myfield . "$row[0]";
			$statisticsoutput .= "<!-- $myfield2 - ";
			if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
			$statisticsoutput .= " -->\n";
			if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
			$statisticsoutput .= "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])</B>"
			."<input type='checkbox' name='summary[]' value='$myfield2'";
			if (isset($_POST['summary']) && array_search($myfield2, $_POST['summary'])!== FALSE)
			{$statisticsoutput .= " CHECKED";}
			$statisticsoutput .= ">&nbsp;"
			."<img src='$imagefiles/speaker.png' align='bottom' alt=\""
			.str_replace("\"", "`", $flt[5])." [$row[1]]\" onclick=\"alert('".$clang->gT("Question","js").": ".$niceqtext." "
			.str_replace("'", "`", $row[1])."')\">"
			."<br />\n"
			."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple 2>\n"
			."\t\t\t\t\t<option value='Y'";
			if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("Y", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">".$clang->gT("Yes")."</option>\n"
			."\t\t\t\t\t<option value='U'";
			if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("U", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">".$clang->gT("Uncertain")."</option>\n"
			."\t\t\t\t\t<option value='N'";
			if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("N", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">".$clang->gT("No")."</option>\n"
			."\t\t\t\t</select>\n\t\t\t\t</font></td>\n";
			$counter2++;
			$allfields[]=$myfield2;
		}
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$counter=0;
		break;
		case "E": // ARRAY OF Increase/Same/Decrease QUESTIONS
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0][]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		$counter2=0;
		while ($row=$result->FetchRow())
		{
			$myfield2 = $myfield . "$row[0]";
			$statisticsoutput .= "<!-- $myfield2 - ";
			if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
			$statisticsoutput .= " -->\n";
			if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
			$statisticsoutput .= "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])</B>"
			."<input type='checkbox' name='summary[]' value='$myfield2'";
			if (isset($_POST['summary']) && array_search($myfield2, $_POST['summary'])!== FALSE) {$statisticsoutput .= " CHECKED";}
			$statisticsoutput .= ">&nbsp;"
			."<img src='$imagefiles/speaker.png' align='bottom' alt=\""
			.str_replace("\"", "`", $flt[5])." [$row[1]]\" onclick=\"alert('".$clang->gT("Question","js")
			.": ".$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
			."<br />\n"
			."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple 2>\n"
			."\t\t\t\t\t<option value='I'";
			if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("I", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">".$clang->gT("Increase")."</option>\n"
			."\t\t\t\t\t<option value='S'";
			if (isset($_POST[$myfield]) && is_array($_POST[$myfield2]) && in_array("S", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">".$clang->gT("Same")."</option>\n"
			."\t\t\t\t\t<option value='D'";
			if (isset($_POST[$myfield]) && is_array($_POST[$myfield2]) && in_array("D", $_POST[$myfield2])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">".$clang->gT("Decrease")."</option>\n"
			."\t\t\t\t</select>\n\t\t\t\t</font></td>\n";
			$counter2++;
			$allfields[]=$myfield2;
		}
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$counter=0;
		break;
		case "F": // ARRAY OF Flexible QUESTIONS
		case "H": // ARRAY OF Flexible Questions (By Column)
		$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
		$query = "SELECT code, answer FROM ".db_table_name("answers")." WHERE qid='$flt[0][]' AND language='{$language}' ORDER BY sortorder, answer";
		$result = db_execute_num($query) or die ("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		$counter2=0;
		while ($row=$result->FetchRow())
		{
			$myfield2 = $myfield . "$row[0]";
			$statisticsoutput .= "<!-- $myfield2 - ";
			if (isset($_POST[$myfield2])) {$statisticsoutput .= $_POST[$myfield2];}
			$statisticsoutput .= " -->\n";
			if ($counter2 == 4) {$statisticsoutput .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
			$statisticsoutput .= "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])</B>"
			."<input type='checkbox' name='summary[]' value='$myfield2'";
			if (isset($_POST['summary']) && array_search($myfield2, $_POST['summary'])!== FALSE) {$statisticsoutput .= " CHECKED";}
			$statisticsoutput .= ">&nbsp;"
			."<img src='$imagefiles/speaker.png' align='bottom' alt=\""
			.str_replace("\"", "`", $flt[5])." [$row[1]]\" onclick=\"alert('".$clang->gT("Question","js")
			.": ".$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
			."<br />\n";
			$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$flt[6]} AND language='{$language}' ORDER BY sortorder, code";
			//$statisticsoutput .= $fquery;
			$fresult = db_execute_assoc($fquery);
			$statisticsoutput .= "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple 2>\n";
			while ($frow = $fresult->FetchRow())
			{
				$statisticsoutput .= "\t\t\t\t\t<option value='{$frow['code']}'";
				if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {$statisticsoutput .= " selected";}
				$statisticsoutput .= ">{$frow['title']}</option>\n";
			}
			$statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</font></td>\n";
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
			."\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($i)</B>"
			."<input type='checkbox' name='summary[]' value='$myfield2'";
			if (isset($_POST['summary']) && array_search($myfield2, $_POST['summary']) !== FALSE) {$statisticsoutput .= " CHECKED";}
			$statisticsoutput .= ">&nbsp;"
			."<img src='$imagefiles/speaker.png' align='bottom' alt=\""
			.str_replace("\"", "`", $flt[5])." [$row[1]]\" onclick=\"alert('".$clang->gT("Question","js")
			.": ".$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
			."<br />\n"
			."\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}{$i}[]' multiple 2>\n";
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
		//              ."<td colspan=$count align=center>$setfont"
		//              ."<input type='button' value='Show Rank Summary' onclick=\"window.open('rankwinner.php?sid=$surveyid&amp;qid=$flt[0]', '_blank', 'toolbar=no, directories=no, location=no, status=yes, menubar=no, resizable=no, scrollbars=no, width=400, height=300, left=100, top=100')\">"
		//              ."</td></tr>\n\t\t\t\t<tr>\n";
		$counter=0;
		unset($answers);
		break;
		case "X": //This is a boilerplate question and it has no business in this script
		break;
		case "W":
		case "Z":
		$statisticsoutput .= "\t\t\t\t<td align='center'>"
		."$setfont<strong>$flt[3]&nbsp;"; //Heading (Question No)
		$statisticsoutput .= "<input type='checkbox' name='summary[]' value='$myfield'";
		if (isset($_POST['summary']) && (array_search("{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE  || array_search("M{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE || array_search("N{$surveyid}X{$flt[1]}X{$flt[0]}", $_POST['summary']) !== FALSE))
		{$statisticsoutput .= " CHECKED";}
		$statisticsoutput .= ">&nbsp;"
		."<img src='$imagefiles/speaker.png' align='bottom' alt=\"".str_replace("\"", "`", $flt[5])."\" onclick=\"alert('".$clang->gT("Question","js").": ".$niceqtext."')\"></strong>"
		."<br />\n";
		$statisticsoutput .= "\t\t\t\t<select name='{$surveyid}X{$flt[1]}X{$flt[0]}[]' multiple 2>\n";
		$allfields[]=$myfield;
		$query = "SELECT code, title FROM ".db_table_name("labels")." WHERE lid={$flt[6]} AND language='{$language}' ORDER BY sortorder, title";
		$result = db_execute_num($query) or die("Couldn't get answers!<br />$query<br />".$connect->ErrorMsg());
		while($row=$result->FetchRow())
		{
			$statisticsoutput .= "\t\t\t\t\t\t<option value='{$row[0]}'";
			if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {$statisticsoutput .= " selected";}
			$statisticsoutput .= ">$row[1]</option>\n";
		} // while
		$statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</font>\n";
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
		$statisticsoutput .= "\t\t\t\t</select>\n\t\t\t\t</font>\n";

		break;
	}
	$currentgroup=$flt[1];
	if (!isset($counter)) {$counter=0;}
	$counter++;
}
$statisticsoutput .= "\n\t\t\t\t</tr>\n";
if (isset($allfields))
{
	$allfield=implode("|", $allfields);
}

$statisticsoutput .= "\t\t\t</table>\n"
."\t\t</td></tr>\n"
."\t\t<tr><td bgcolor='#CCCCCC' align='center'>\n"
."\t\t<font size='1' face='verdana'>&nbsp;</font></td></tr>\n"
."\t\t\t\t<tr><td align='center'>$setfont<input type='radio' id='viewsummaryall' name='summary' value='$allfield'"
."><label for='viewsummaryall'>".$clang->gT("View summary of all available fields")."</label></font></td></tr>\n"
."\t\t<tr><td align='center' bgcolor='#CCCCCC'>\n\t\t\t<br />\n"
."\t\t\t<input type='submit' value='".$clang->gT("View Stats")."'>\n"
."\t\t\t<input type='button' value='".$clang->gT("Clear")."' onclick=\"window.open('$scriptname?action=statistics&amp;sid=$surveyid', '_top')\">\n"
."\t\t<br />&nbsp;\n"
."\t\t<input type='hidden' name='sid' value='$surveyid'>\n"
."\t\t<input type='hidden' name='display' value='stats'>\n"
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
	$statisticsoutput .= "<script type='text/javascript'>
    <!-- 
     hide('filtersettings'); 
    //-->
    </script>\n";
	// 1: Get list of questions with answers chosen
	for (reset($_POST); $key=key($_POST); next($_POST)) { $postvars[]=$key;} // creates array of post variable names
	foreach ($postvars as $pv)
	{
		if (in_array($pv, $allfields)) //Only do this if there is actually a value for the $pv
		{
			$firstletter=substr($pv,0,1);
			if ($pv != "sid" && $pv != "display" && $firstletter != "M" && $firstletter != "T" && $firstletter != "Q" && $firstletter != "D" && $firstletter != "N" && $pv != "summary" && substr($pv, 0, 2) != "id" && substr($pv, 0, 9) != "datestamp") //pull out just the fieldnames
			{
				$thisquestion = "`$pv` IN (";
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
						$mselects[]="`".substr($pv, 1, strlen($pv))."$arow[0]` = 'Y'";
					}
				}
				if ($mselects)
				{
					$thismulti=implode(" OR ", $mselects);
					$selects[]="($thismulti)";
				}
			}
			elseif (substr($pv, 0, 1) == "N")
			{
				if (substr($pv, strlen($pv)-1, 1) == "G" && $_POST[$pv] != "")
				{
					$selects[]="`".substr($pv, 1, -1)."` > '".$_POST[$pv]."'";
				}
				if (substr($pv, strlen($pv)-1, 1) == "L" && $_POST[$pv] != "")
				{
					$selects[]="`".substr($pv, 1, -1)."` < '".$_POST[$pv]."'";
				}
			}
			elseif (substr($pv, 0, 2) == "id")
			{
				if (substr($pv, strlen($pv)-1, 1) == "G" && $_POST[$pv] != "")
				{
					$selects[]="`".substr($pv, 0, -1)."` > '".$_POST[$pv]."'";
				}
				if (substr($pv, strlen($pv)-1, 1) == "L" && $_POST[$pv] != "")
				{
					$selects[]="`".substr($pv, 0, -1)."` < '".$_POST[$pv]."'";
				}
				if (substr($pv, strlen($pv)-1, 1) == "=" && $_POST[$pv] != "")
				{
					$selects[]="`".substr($pv, 0, -1)."` = '".$_POST[$pv]."'";
				}
			}
			elseif ((substr($pv, 0, 1) == "T" || substr($pv, 0, 1) == "Q" ) && $_POST[$pv] != "")
			{
				$selects[]="`".substr($pv, 1, strlen($pv))."` like '%".$_POST[$pv]."%'";
			}
			elseif (substr($pv, 0, 1) == "D" && $_POST[$pv] != "")
			{
				if (substr($pv, -1, 1) == "=")
				{
					$selects[] = "`".substr($pv, 1, strlen($pv)-2)."` = '".$_POST[$pv]."'";
				}
				else
				{
					if (substr($pv, -1, 1) == "<")
					{
						$selects[]= "`".substr($pv, 1, strlen($pv)-2) . "` > '".$_POST[$pv]."'";
					}
					if (substr($pv, -1, 1) == ">")
					{
						$selects[]= "`".substr($pv, 1, strlen($pv)-2) . "` < '".$_POST[$pv]."'";
					}
				}
			}
			elseif (substr($pv, 0, 9) == "datestamp")
			{
				if (substr($pv, -1, 1) == "=" && !empty($_POST[$pv]))
				{
					$selects[] = "`datestamp` = '".$_POST[$pv]."'";
				}
				else
				{
					if (substr($pv, -1, 1) == "<" && !empty($_POST[$pv]))
					{
						$selects[]= "`datestamp` > '".$_POST[$pv]."'";
					}
					if (substr($pv, -1, 1) == ">" && !empty($_POST[$pv]))
					{
						$selects[]= "`datestamp` < '".$_POST[$pv]."'";
					}
				}
			}
		} else {
			$statisticsoutput .= "<!-- $pv DOES NOT EXIST IN ARRAY -->";
		}
	}
	// 2: Do SQL query
	$query = "SELECT count(*) FROM ".db_table_name("survey_$surveyid");
	$result = db_execute_num($query) or die ("Couldn't get total<br />$query<br />".$connect->ErrorMsg());
	while ($row=$result->FetchRow()) {$total=$row[0];}
	if (isset($selects) && $selects)
	{
		$query .= " WHERE ";
		$query .= implode(" AND ", $selects);
	}
	elseif (!empty($_POST['sql']) && !isset($_POST['id=']))
	{
		$newsql=substr($_POST['sql'], strpos($_POST['sql'], "WHERE")+5, strlen($_POST['sql']));
		//$query = $_POST['sql'];
		$query .= " WHERE ".$newsql;
	}
	$result=db_execute_num($query) or die("Couldn't get results<br />$query<br />".$connect->ErrorMsg());
	while ($row=$result->FetchRow()) {$results=$row[0];}

	// 3: Present results including option to view those rows
	$statisticsoutput .= "<br />\n<table align='center' width='95%' border='1' bgcolor='#444444' "
	."cellpadding='2' cellspacing='0' >\n"
	."\t<tr><td colspan='2' align='center'><strong>$setfont<font color='#FF9900'>"
	.$clang->gT("Results")."</font></font></strong></td></tr>\n"
	."\t<tr><td colspan='2' align='center' bgcolor='#666666'>"
	."$setfont<font color='#EEEEEE'>"
	."<strong>".$clang->gT("No of records in this query").": $results </strong></font></font><br />\n\t\t"
	.$clang->gT("Total records in survey").": $total<br />\n";
	if ($total)
	{
		$percent=sprintf("%01.2f", ($results/$total)*100);
		$statisticsoutput .= $clang->gT("Percentage of total")
		.": $percent%<br />";
	}
	$statisticsoutput .= "\n\t\t<br />\n"
	."\t\t<font size='1'><strong>".$clang->gT("SQL").":</strong> $query\n"
	."\t</font></td></tr>\n";
	if (isset ($selects) && $selects) {$sql=implode(" AND ", $selects);}
	elseif (!empty($newsql)) {$sql = $newsql;}
	if (!isset($sql) || !$sql) {$sql="NULL";}
	if ($results > 0)
	{
		$statisticsoutput .= "\t<tr>"
		."\t\t<td align='right' width='50%'><form action='$scriptname?action=browse' method='post' target='_blank'>\n"
		."\t\t<input type='submit' value='".$clang->gT("Browse")."' >\n"
		."\t\t\t<input type='hidden' name='sid' value='$surveyid'>\n"
		."\t\t\t<input type='hidden' name='sql' value=\"$sql\">\n"
		."\t\t\t<input type='hidden' name='subaction' value='all'>\n"
		."\t\t</form>\n"
		."\t\t<td width='50%'><form action='$scriptname?action=exportresults' method='post' target='_blank'>\n"
		."\t\t<input type='submit' value='".$clang->gT("Export")."' >\n"
		."\t\t\t<input type='hidden' name='sid' value='$surveyid'>\n"
		."\t\t\t<input type='hidden' name='sql' value=\"$sql\">\n";
		//Add the fieldnames
		if (isset($_POST['summary']) && $_POST['summary'])
		{
			foreach($_POST['summary'] as $viewfields)
			{
				switch(substr($viewfields, 0, 1))
				{
					case "N":
					case "T":
					$field = substr($viewfields, 1, strlen($viewfields)-1);
					$statisticsoutput .= "\t\t\t<input type='hidden' name='summary[]' value='$field'>\n";
					break;
					case "M":
					list($lsid, $lgid, $lqid) = explode("X", substr($viewfields, 1, strlen($viewfields)-1));
					$aquery="SELECT code FROM ".db_table_name("answers")." WHERE qid=$lqid AND language='{$language}' ORDER BY sortorder, answer";
					$aresult=db_execute_num($aquery) or die ("Couldn't get answers<br />$aquery<br />".$connect->ErrorMsg());
					while ($arow=$aresult->FetchRow()) // go through every possible answer
					{
						$field = substr($viewfields, 1, strlen($viewfields)-1).$arow[0];
						$statisticsoutput .= "\t\t\t<input type='hidden' name='summary[]' value='$field'>\n";
					}
					$aquery = "SELECT other FROM ".db_table_name("questions")." WHERE qid=$lqid AND language='{$language}'";
					$aresult = db_execute_num($aquery);
					while($arow = $aresult->FetchRow()){
						if ($arow[0] == "Y") {
							//$statisticsoutput .= $arow[0];
							$field = substr($viewfields, 1, strlen($viewfields)-1)."other";
							$statisticsoutput .= "\t\t\t<input type='hidden' name='summary[]' value='$field'>\n";
						}
					} // while
					break;
					default:
					$field = $viewfields;
					$statisticsoutput .= "\t\t\t<input type='hidden' name='summary[]' value='$field'>\n";
					break;
				}
			}
		}
		$statisticsoutput .= "\t\t</form></td>\n\t</tr>\n";
	}
	$statisticsoutput .= "</table>\n";
}

//Show Summary results
if (isset($_POST['summary']) && $_POST['summary'])
{
	if ($usejpgraph == 1 && isset($jpgraphdir)) //JPGRAPH CODING SUBMITTED BY Pieterjan Heyse
	{
		//Delete any old temp image files
		deletePattern($tempdir, "STATS_".date("d")."X".$currentuser."X".$surveyid."X"."*.png");
	}
	$runthrough=returnglobal('summary');

	//START Chop up fieldname and find matching questions
	$lq = "SELECT DISTINCT qid FROM ".db_table_name("questions")." WHERE sid=$surveyid"; //GET LIST OF LEGIT QIDs FOR TESTING LATER
	$lr = db_execute_assoc($lq);
	$legitqs[] = "DUMMY ENTRY";
	while ($lw = $lr->FetchRow())
	{
		$legitqids[] = $lw['qid']; //this creates an array of question id's'
	}
	//Finished collecting legitqids
	foreach ($runthrough as $rt)
	{
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
			$nquery = "SELECT title, type, question, other FROM ".db_table_name("questions")." WHERE qid='".substr($qqid, 0, strlen($qqid)-1)."' AND language='{$language}'";
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
				$statisticsoutput .= "<br />\n<table align='center' width='95%' border='1' bgcolor='#444444' cellpadding='2' cellspacing='0' >\n"
				."\t<tr><td colspan='3' align='center'><strong>$setfont<font color='#FF9900'>".$clang->gT("Field Summary for")." $qtitle:</font></font></strong>"
				."</td></tr>\n"
				."\t<tr><td colspan='3' align='center'><strong>$setfont<font color='#EEEEEE'>$qquestion</font></font></strong></td></tr>\n"
				."\t<tr>\n\t\t<td width='50%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'><strong>"
				.$clang->gT("Calculation")."</strong></font></font></td>\n"
				."\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'><strong>"
				.$clang->gT("Result")."</strong></font></font></td>\n"
				."\t\t<td width='25%' align='center' bgcolor='#666666'></td>\n"
				."\t</tr>\n";
				$fieldname=substr($rt, 1, strlen($rt));
				$query = "SELECT STDDEV(`$fieldname`) as stdev";
				$query .= ", SUM(`$fieldname`*1) as sum";
				$query .= ", AVG(`$fieldname`*1) as average";
				$query .= ", MIN(`$fieldname`*1) as minimum";
				$query .= ", MAX(`$fieldname`*1) as maximum";
				$query .= " FROM ".db_table_name("survey_$surveyid")." WHERE `$fieldname` IS NOT NULL AND `$fieldname` != ' '";
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
				$query ="SELECT `$fieldname` FROM ".db_table_name("survey_$surveyid")." WHERE `$fieldname` IS NOT null AND `$fieldname` != ' '";
				if ($sql != "NULL") {$query .= " AND $sql";}
				$result=$connect->Execute($query) or die("Disaster during median calculation<br />$query<br />".$connect->ErrorMsg());
				$querystarter="SELECT `$fieldname` FROM ".db_table_name("survey_$surveyid")." WHERE `$fieldname` IS NOT null AND `$fieldname` != ' '";
				if ($sql != "NULL") {$querystarter .= " AND $sql";}
				$medcount=$result->RecordCount();

				if ($medcount>1)   // Calculating makes only sens with more than one result
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
						//TODO: This is going to be trick to replicate under MS SQL Server, as there is no LIMIT x,y equivalent
						// It can be done, but it may require having different queries.
						// There are a number of queries in this unit that need to be fixed 
						$query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $q1c, 2";
						$result=db_execute_assoc($query) or die("1st Quartile query failed<br />".$connect->ErrorMsg());
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
						$query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $q1c, 1";
						$result=db_execute_assoc($query) or die ("1st Quartile query failed<br />".$connect->ErrorMsg());
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
						$query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $medianc, 2";
						$result=db_execute_assoc($query) or die("What a complete mess with the remainder<br />$query<br />".$connect->ErrorMsg());
						while ($row=$result->FetchRow()) {$total=$total+$row[$fieldname];}
						$showem[]=array($clang->gT("2nd Quartile (Median)"), $total/2);
					}
					else
					{
						//EVEN NUMBER
						$query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $medianc, 1";
						$result=db_execute_assoc($query) or die("What a complete mess<br />$query<br />".$connect->ErrorMsg());
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
						$query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $q3c, 2";
						$result = db_execute_assoc($query) or die("3rd Quartile query failed<br />".$connect->ErrorMsg());
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
						$query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $q3c, 1";
						$result = db_execute_assoc($query) or die("3rd Quartile even query failed<br />".$connect->ErrorMsg());
						while ($row=$result->FetchRow()) {$showem[]=array("3rd Quartile (Q3)", $row[$fieldname]);}
					}
					$total=0;
					$showem[]=array($clang->gT("Maximum"), $maximum);
					foreach ($showem as $shw)
					{
						$statisticsoutput .= "\t<tr>\n"
						."\t\t<td align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$shw[0]</font></font></td>\n"
						."\t\t<td align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$shw[1]</font></font></td>\n"
						."\t\t<td bgcolor='#666666'></td>\n"
						."\t</tr>\n";
					}
					$statisticsoutput .= "\t<tr>\n"
					."\t\t<td colspan='3' align='center' bgcolor='#EEEEEE'>\n"
					."\t\t\t$setfont<font size='1'>".$clang->gT("Null values are ignored in calculations")."<br />\n"
					."\t\t\t".$clang->gT("Q1 and Q3 calculated using")." <a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>".$clang->gT("minitab method")."</a>"
					."</font></font>\n"
					."\t\t</td>\n"
					."\t</tr>\n</table>\n";
					unset($showem);
				}
				else
				{
					$statisticsoutput .= "\t<tr>\n"
					."\t\t<td align='center' bgcolor='#666666' colspan='3'>$setfont<font color='#EEEEEE'>Not enough values for calculation</font></font></td>\n"
					."\t</tr>\n</table>\n";

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
					$alist[]=array("-oth-", $clang->gT("Other"));
				}
			}
		}

		//foreach ($alist as $al) {$statisticsoutput .= "$al[0] - $al[1]<br />";} //debugging line
		//foreach ($fvalues as $fv) {$statisticsoutput .= "$fv | ";} //debugging line

		//2. Collect and Display results #######################################################################
		if (isset($alist) && $alist) //Make sure there really is an answerlist, and if so:
		{
			$statisticsoutput .= "<table width='95%' align='center' border='1' bgcolor='#444444' cellpadding='2' cellspacing='0' style='margin: 2px auto;'>\n"
			."\t<tr><td colspan='3' align='center'><strong>$setfont<font color='#FF9900'>"
			.$clang->gT("Field Summary for")." $qtitle:</font></font></strong>"
			."</td></tr>\n"
			."\t<tr><td colspan='3' align='center'><strong>$setfont<font color='#EEEEEE'>"
			."$qquestion</font></font></strong></td></tr>\n"
			."\t<tr>\n\t\t<td width='50%' align='center' bgcolor='#666666'>$setfont"
			."<font color='#EEEEEE'><strong>".$clang->gT("Answer")."</strong></font></font></td>\n"
			."\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont"
			."<font color='#EEEEEE'><strong>".$clang->gT("Count")."</strong></font></font></td>\n"
			."\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont"
			."<font color='#EEEEEE'><strong>".$clang->gT("Percentage")."</strong></font></font></td>\n"
			."\t</tr>\n";
			foreach ($alist as $al)
			{
				if (isset($al[2]) && $al[2]) //picks out alist that come from the multiple list above
				{
					if ($al[1] == $clang->gT("Other"))
					{
						$query = "SELECT count(`$al[2]`) FROM ".db_table_name("survey_$surveyid")." WHERE `$al[2]` != ''";
					}
					elseif ($qtype == "T" || $qtype == "S" || $qtype == "Q")
					{
						if($al[0]=="Answers")
						{
							$query = "SELECT count(`$al[2]`) FROM ".db_table_name("survey_$surveyid")." WHERE `$al[2]` != ''";
						}
						elseif($al[0]=="NoAnswer")
						{
							$query = "SELECT count(`$al[2]`) FROM ".db_table_name("survey_$surveyid")." WHERE `$al[2]` IS NULL OR `$al[2]` = ''";
						}
					}
					else
					{
						$query = "SELECT count(`$al[2]`) FROM ".db_table_name("survey_$surveyid")." WHERE `$al[2]` =";
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
					$query = "SELECT count(`$rt`) FROM ".db_table_name("survey_$surveyid")." WHERE `$rt` = '$al[0]'";
				}
				if ($sql != "NULL") {$query .= " AND $sql";}
				$result=db_execute_num($query) or die ("Couldn't do count of values<br />$query<br />".$connect->ErrorMsg());
				$statisticsoutput .= "\n<!-- ($sql): $query -->\n\n";
				while ($row=$result->FetchRow())
				{
					if ($al[0] == "")
					{$fname=$clang->gT("No answer");}
					elseif ($al[0] == $clang->gT("Other") || $al[0] == "Answers")
					{$fname="$al[1] <input type='submit' value='".$clang->gT("Browse")."' onclick=\"window.open('listcolumn.php?sid=$surveyid&amp;column=$al[2]&amp;sql=".urlencode($sql)."', 'results', 'width=300, height=500, left=50, top=50, resizable=yes, scrollbars=yes, menubar=no, status=no, location=no, toolbar=no')\">";}
					elseif ($qtype == "S" || $qtype == "T" || $qtype == "Q")
					{
						if ($al[0] == "Answer")
						{
							$fname= "$al[1] <input type='submit' value='"
							. $clang->gT("Browse")."' onclick=\"window.open('listcolumn.php?sid=$surveyid&amp;column=$al[2]&amp;sql="
							. urlencode($sql)."', 'results', 'width=300, height=500, left=50, top=50, resizable=yes, scrollbars=yes, menubar=no, status=no, location=no, toolbar=no')\">";
						}
						elseif ($al[0] == "NoAnswer")
						{
							$fname= "$al[1]";
						}
					}
					else
					{$fname="$al[1] ($al[0])";}
					$statisticsoutput .= "\t<tr>\n\t\t<td width='50%' align='center' bgcolor='#666666'>$setfont"
					."<font color='#EEEEEE'>$fname\n"
					."\t\t</font></font></td>\n"
					."\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$row[0]</font></font>\n";
					if ($results > 0) {$vp=sprintf("%01.2f", ($row[0]/$results)*100)."%";} else {$vp="N/A";}
					$statisticsoutput .= "\t\t</td><td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$vp</font></font>"
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
					$graph->xaxis->SetColor("silver");
					$graph->xaxis->title->Set($clang->gT("Code"));
					$graph->xaxis->title->SetFont(constant($jpgraphfont), FS_BOLD, 9);
					$graph->xaxis->title->SetColor("silver");
					$graph->yaxis->SetFont(constant($jpgraphfont), FS_NORMAL, 8);
					$graph->yaxis->SetColor("silver");
					$graph->yaxis->title->Set($clang->gT("Count")." / $results");
					$graph->yaxis->title->SetFont(constant($jpgraphfont), FS_BOLD, 9);
					$graph->yaxis->title->SetColor("silver");
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
					$graph->legend->SetFillColor("silver");
    				$graph->SetAntiAliasing();

				}
				$graph->title->SetColor("#EEEEEE");
				$graph->SetMarginColor("#666666");
				// Set A title for the plot
				//$graph->title->Set($qquestion);
				$graph->title->SetFont(constant($jpgraphfont),FS_BOLD,13);
				// Create pie plot
				if ($qtype == "M" || $qtype == "P") { //Bar Graph
					$p1 = new BarPlot($grawdata);
					$p1->SetWidth(0.8);
					$p1->SetValuePos("center");
					$p1->SetFillColor("#FF9900");
					if (!in_array(0, $grawdata)) { //don't show shadows if any of the values are 0 - jpgraph bug
						$p1->SetShadow();
					}
					$p1->value->Show();
					$p1->value->SetFont(constant($jpgraphfont),FS_NORMAL,8);
					$p1->value->SetColor("#555555");
				} else { //Pie Chart
					$p1 = new PiePlot3d($gdata);
					//                        $statisticsoutput .= "<pre>";print_r($lbl);$statisticsoutput .= "</pre>";
					//                        $statisticsoutput .= "<pre>";print_r($gdata);$statisticsoutput .= "</pre>";
					$p1->SetTheme("earth");
					$p1->SetLegends($lbl);
					$p1->SetSize(200);
					$p1->SetCenter(0.375,$setcentrey);
					$p1->value->SetColor("#FF9900");
					$p1->value->SetFont(constant($jpgraphfont),FS_NORMAL,10);
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
		unset($lbl);
		unset($justcode);
		unset ($alist);
	}
    $statisticsoutput .= "<br />&nbsp\n";
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
