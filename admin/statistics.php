<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA
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

require_once("config.php");

if (!isset($sid)) {$sid=returnglobal('sid');}
sendcacheheaders();

$slstyle2 = "style='background-color: #EEEFFF; font-family: verdana; font-size: 10; color: #000080; width: 150'";

echo $htmlheader;

if (!$sid)
	{
	//need to have a survey id
	echo "<center>You have not selected a survey!</center>";
	exit;
	}

$surveyoptions=browsemenubar();

echo "<table height='1'><tr><td></td></tr></table>\n"
	."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
	."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._STATISTICS."</b></td></tr>\n";
echo $surveyoptions;
echo "</table>\n"
	."<table height='1'><tr><td></td></tr></table>\n"
	."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0' bordercolor='#555555'>\n"
	."<tr><td align='center' bgcolor='#555555'><font size='2' face='verdana' color='orange'><b>"._ST_FILTERSETTINGS."</b></td></tr>\n"
	."\t<form method='post'>\n";

//Select public language file
$query = "SELECT language FROM {$dbprefix}surveys WHERE sid=$sid";
$result = mysql_query($query) or die("Error selecting language: <br />".$query."<br />".mysql_error());
while ($row=mysql_fetch_array($result)) {$surveylanguage = $row['language'];}
$langdir="$publicdir/lang";
$langfilename="$langdir/$surveylanguage.lang.php";
if (!is_file($langfilename)) {$langfilename="$langdir/$defaultlang.lang.php";}
require($langfilename);	

// 1: Get list of questions from survey
$query = "SELECT qid, {$dbprefix}questions.gid, type, title, group_name, question, lid FROM {$dbprefix}questions, {$dbprefix}groups WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}questions.sid='$sid' ORDER BY group_name, title";
$result = mysql_query($query) or die("Couldn't do it!<br />$query<br />".mysql_error());
while ($row=mysql_fetch_row($result))
	{
	//filters='qid','gid','type','title','group_name','question', 'lid'
	$filters[]=array("$row[0]", "$row[1]", "$row[2]", "$row[3]", "$row[4]", strip_tags($row[5]), $row[6]);
	}
// 2: Get answers for each question
if (!isset($currentgroup)) {$currentgroup="";}
foreach ($filters as $flt)
	{
	if ($flt[1] != $currentgroup) 
		{   //If the groupname has changed, start a new row
		if ($currentgroup)
			{
			//if we've already drawn a table for a group, and we're changing - close off table
			echo "\n\t\t\t\t</td></tr>\n\t\t\t</table>\n";
			}
		echo "\t\t<tr><td bgcolor='#CCCCCC' align='center'>\n"
			."\t\t<font size='1' face='verdana'><b>$flt[4]</b> (Group $flt[1])</font></td></tr>\n\t\t"
			."<tr><td align='center'>\n"
			."\t\t\t<table align='center'><tr>\n";
		$counter=0;
		}
	//echo $flt[2];	//debugging line
	if (isset($counter) && $counter == 4) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>"; $counter=0;}
	$myfield = "{$sid}X{$flt[1]}X{$flt[0]}";
	$niceqtext = str_replace("\"", "`", $flt[5]);
	$niceqtext = str_replace("'", "`", $niceqtext);
	$niceqtext = str_replace("\r", "", $niceqtext);
	$niceqtext = str_replace("\n", "", $niceqtext);
	//headings
	if ($flt[2] != "A" && $flt[2] != "B" && $flt[2] != "C" && $flt[2] != "E" && $flt[2] != "F" && $flt[2] != "T" && $flt[2] != "S" && $flt[2] != "D" && $flt[2] != "R" && $flt[2] != "Q") //Have to make an exception for these types!
		{
		echo "\t\t\t\t<td align='center'>"
			."$setfont<b>$flt[3]&nbsp;"; //Heading (Question No)
		if ($flt[2] == "M" || $flt[2] == "P" || $flt[2] == "R") {$myfield = "M$myfield";}
		if ($flt[2] == "N") {$myfield = "N$myfield";}
		echo "<input type='radio' name='summary' value='$myfield'";
		if (isset($_POST['summary']) && ($_POST['summary'] == "{$sid}X{$flt[1]}X{$flt[0]}" || $_POST['summary'] == "M{$sid}X{$flt[1]}X{$flt[0]}" || $_POST['summary'] == "N{$sid}X{$flt[1]}X{$flt[0]}")) {echo " CHECKED";}
		echo ">&nbsp;"
			."<img src='./images/speaker.jpg' align='bottom' alt=\"".str_replace("\"", "`", $flt[5])."\" onClick=\"alert('QUESTION: ".$niceqtext."')\"></b>"
			."<br />\n";
		if ($flt[2] != "N") {echo "\t\t\t\t<select name='";}
		if ($flt[2] == "M" || $flt[2] == "P" || $flt[2] == "R") {echo "M";}
		if ($flt[2] != "N") {echo "{$sid}X{$flt[1]}X{$flt[0]}[]' multiple $slstyle2>\n";}
		$allfields[]=$myfield;
		}
	echo "\t\t\t\t\t<!-- QUESTION TYPE = $flt[2] -->\n";
	switch ($flt[2])
		{
		case "Q":
			//DO NUSSINK
			break;
		case "T": // Long free text
			$myfield2="T$myfield";
			echo "\t\t\t\t<td align='center' valign='top'>$setfont<b>$flt[3]</b>"
				."&nbsp;<img src='./images/speaker.jpg' align='bottom' alt=\"".str_replace("\"", "`", $flt[5])." [$row[1]]\" onClick=\"alert('QUESTION: ".$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
				."<br />\n"
				."\t\t\t\t\t<font size='1'>Responses containing:</font><br />\n"
				."\t\t\t\t\t<textarea $slstyle2 name='$myfield2' rows='3'>";
			if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
			echo "</textarea>";
			$allfields[]=$myfield2;
			break;
		case "S": // Short free text
			$myfield2="T$myfield";
			echo "\t\t\t\t<td align='center' valign='top'>$setfont<b>$flt[3]</b>"
				."&nbsp;<img src='./images/speaker.jpg' align='bottom' alt=\"".str_replace("\"", "`", $flt[5])." [$row[1]]\" onClick=\"alert('QUESTION: ".$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
				."<br />\n"
				."\t\t\t\t\t<font size='1'>Responses containing:</font><br />\n"
				."\t\t\t\t\t<input type='text' $slstyle2 name='$myfield2' value='";
			if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
			echo "'>";
			$allfields[]=$myfield2;
			break;
		case "N": // Numerical
			$myfield2="{$myfield}G";
			$myfield3="{$myfield}L";
			echo "\t\t\t\t\t<font size='1'>Number greater than:<br />\n"
				."\t\t\t\t\t<input type='text' $slstyle2 name='$myfield2' value='";
			if (isset($_POST[$myfield2])){echo $_POST[$myfield2];}
			echo "'><br />\n"
				."\t\t\t\t\tNumber less than:<br />\n"
				."\t\t\t\t\t<input type='text' $slstyle2 name='$myfield3' value='";
			if (isset($_POST[$myfield2])) {echo $_POST[$myfield3];}
			echo "'><br />\n";
			break;
		case "D": // Date
			$myfield2="D$myfield";
			$myfield3="$myfield2=";
			$myfield4="$myfield2<"; $myfield5="$myfield2>";
			echo "\t\t\t\t<td align='center' valign='top'>$setfont<b>$flt[3]</b>"
				."&nbsp;<img src='./images/speaker.jpg' align='bottom' alt=\"".str_replace("\"", "`", $flt[5])
				." [$row[1]]\" onClick=\"alert('QUESTION: ".$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
				."<br />\n"
				."\t\t\t\t\t<font size='1'>Date (YYYY-MM-DD) equals:<br />\n"
				."\t\t\t\t\t<input name='$myfield3' type='text' value='";
			if (isset($_POST[$myfield3])) {echo $_POST[$myfield3];}
			echo "' ".substr($slstyle2, 0, -13) ."; width:80'><br />\n"
				."\t\t\t\t\t&nbsp;&nbsp;OR between:<br />\n"
				."\t\t\t\t\t<input name='$myfield4' value='";
			if (isset($_POST[$myfield4])) {echo $_POST[$myfield4];}
			echo "' type='text' ".substr($slstyle2, 0, -13) ."; width:65'> & <input  name='$myfield5' value='";
			if (isset($_POST[$myfield5])) {echo $_POST[$myfield5];}
			echo "' type='text' ".substr($slstyle2, 0, -13) ."; width:65'>\n";
			break;
		case "5": // 5 point choice
			for ($i=1; $i<=5; $i++)
				{
				echo "\t\t\t\t\t<option value='$i'";
				if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($i, $_POST[$myfield])) {echo " selected";}
				echo ">$i</option>\n";
				}
			break;
		case "G": // Gender
			echo "\t\t\t\t\t<option value='F'";
			if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("F", $_POST[$myfield])) {echo " selected";}
			echo ">"._FEMALE."</option>\n";
			echo "\t\t\t\t\t<option value='M'";
			if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("M", $_POST[$myfield])) {echo " selected";}
			echo ">"._MALE."</option>\n";
			break;
		case "Y": // Yes\No
			echo "\t\t\t\t\t<option value='Y'";
			if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("Y", $_POST[$myfield])) {echo " selected";}
			echo ">"._YES."</option>\n"
				."\t\t\t\t\t<option value='N'";
			if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array("N", $_POST[$myfield])) {echo " selected";}
			echo ">"._NO."</option>\n";
			break;
		// ARRAYS
		case "A": // ARRAY OF 5 POINT CHOICE QUESTIONS
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$query = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$flt[0]' ORDER BY sortorder, answer";
			$result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
			$counter2=0;
			while ($row=mysql_fetch_row($result))
				{
				$myfield2 = $myfield."$row[0]";
				echo "<!-- $myfield2 -- ";
				if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
				echo " -->\n";
				if ($counter2 == 4) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}

				echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])"
					."<input type='radio' name='summary' value='$myfield2'";
				if (isset($_POST['summary']) && $_POST['summary'] == "$myfield2") {echo " CHECKED";}
				echo ">&nbsp;"
					."<img src='./images/speaker.jpg' align='bottom' alt=\"".str_replace("\"", "`", $flt[5])." [$row[1]]\" onClick=\"alert('QUESTION: ".$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
					."<br />\n"
					."\t\t\t\t<select name='{$sid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple $slstyle2>\n";
				for ($i=1; $i<=5; $i++)
					{
					echo "\t\t\t\t\t<option value='$i'";
					if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {echo " selected";}
					if ($_POST[$myfield2] == $i) {echo " selected";}
					echo ">$i</option>\n";
					}
				echo "\t\t\t\t</select>\n\t\t\t\t</td>\n";
				$counter2++;
				$allfields[]=$myfield2;
				}
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$counter=0;
			break;
		case "B": // ARRAY OF 10 POINT CHOICE QUESTIONS
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$query = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$flt[0]' ORDER BY sortorder, answer";
			$result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
			$counter2=0;
			while ($row=mysql_fetch_row($result))
				{
				$myfield2 = $myfield . "$row[0]";
				echo "<!-- $myfield2 -- ";
				if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
				echo " -->\n";
				if ($counter2 == 4) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
				
				echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])"; //heading
				echo "<input type='radio' name='summary' value='$myfield2'";
				if (isset($_POST['summary']) && $_POST['summary'] == "$myfield2") {echo " CHECKED";}
				echo ">&nbsp;"
					."<img src='./images/speaker.jpg' align='bottom' alt=\"".str_replace("\"", "`", $flt[5])
					." [$row[1]]\" onClick=\"alert('QUESTION: ".$niceqtext." ".str_replace("'", "`", $row[1])
					."')\">"
					."<br />\n"
					."\t\t\t\t<select name='{$sid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple $slstyle2>\n";
				for ($i=1; $i<=10; $i++)
					{
					echo "\t\t\t\t\t<option value='$i'";
					if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {echo " selected";}
					if (isset($_POST[$myfield2]) && $_POST[$myfield2] == $i) {echo " selected";}
					echo ">$i</option>\n";
					}
				echo "\t\t\t\t</select>\n\t\t\t\t</td>\n";
				$count2++;
				$allfields[]=$myfield2;
				}
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$counter=0;
			break;
		case "C": // ARRAY OF YES\No\Uncertain QUESTIONS
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$query = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$flt[0][]' ORDER BY sortorder, answer";
			$result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
			$counter2=0;
			while ($row=mysql_fetch_row($result))
				{
				$myfield2 = $myfield . "$row[0]";
				echo "<!-- $myfield2 -- ";
				if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
				echo " -->\n";
				if ($counter2 == 4) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
				echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])"
					."<input type='radio' name='summary' value='$myfield2'";
				if (isset($_POST['summary']) && $_POST['summary'] == "$myfield2") {echo " CHECKED";}
				echo ">&nbsp;"
					."<img src='./images/speaker.jpg' align='bottom' alt=\"".str_replace("\"", "`", $flt[5])." [$row[1]]\" onClick=\"alert('QUESTION: ".$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
					."<br />\n"
					."\t\t\t\t<select name='{$sid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple $slstyle2>\n"
					."\t\t\t\t\t<option value='Y'";
				if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("Y", $_POST[$myfield2])) {echo " selected";}
				echo ">"._YES."</option>\n"
					."\t\t\t\t\t<option value='U'";
				if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("U", $_POST[$myfield2])) {echo " selected";}
				echo ">"._UNCERTAIN."</option>\n"
					."\t\t\t\t\t<option value='N'";
				if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("N", $_POST[$myfield2])) {echo " selected";}
				echo ">"._NO."</option>\n"
					."\t\t\t\t</select>\n\t\t\t\t</td>\n";
				$counter2++;
				$allfields[]=$myfield2;
				}
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$counter=0;
			break;
		case "E": // ARRAY OF Increase/Same/Decrease QUESTIONS
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$query = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$flt[0][]' ORDER BY sortorder, answer";
			$result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
			$counter2=0;
			while ($row=mysql_fetch_row($result))
				{
				$myfield2 = $myfield . "$row[0]";
				echo "<!-- $myfield2 -- ";
				if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
				echo " -->\n";
				if ($counter2 == 4) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
				echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])"
					."<input type='radio' name='summary' value='$myfield2'";
				if (isset($_POST['summary']) && $_POST['summary'] == "$myfield2") {echo " CHECKED";}
				echo ">&nbsp;"
					."<img src='./images/speaker.jpg' align='bottom' alt=\"".str_replace("\"", "`", $flt[5])." [$row[1]]\" onClick=\"alert('QUESTION: ".$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
					."<br />\n"
					."\t\t\t\t<select name='{$sid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple $slstyle2>\n"
					."\t\t\t\t\t<option value='I'";
				if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array("I", $_POST[$myfield2])) {echo " selected";}
				echo ">"._INCREASE."</option>\n"
					."\t\t\t\t\t<option value='S'";
				if (isset($_POST[$myfield]) && is_array($_POST[$myfield2]) && in_array("S", $_POST[$myfield2])) {echo " selected";}
				echo ">"._SAME."</option>\n"
					."\t\t\t\t\t<option value='D'";
				if (isset($_POST[$myfield]) && is_array($_POST[$myfield2]) && in_array("D", $_POST[$myfield2])) {echo " selected";}
				echo ">"._DECREASE."</option>\n"
					."\t\t\t\t</select>\n\t\t\t\t</td>\n";
				$counter2++;
				$allfields[]=$myfield2;
				}
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$counter=0;
			break;
		case "F": // ARRAY OF Flexible QUESTIONS
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$query = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$flt[0][]' ORDER BY sortorder, answer";
			$result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
			$counter2=0;
			while ($row=mysql_fetch_row($result))
				{
				$myfield2 = $myfield . "$row[0]";
				echo "<!-- $myfield2 -- ";
				if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
				echo " -->\n";
				if ($counter2 == 4) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter2=0;}
				echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])"
					."<input type='radio' name='summary' value='$myfield2'";
				if (isset($_POST['summary']) && $_POST['summary'] == "$myfield2") {echo " CHECKED";}
				echo ">&nbsp;"
					."<img src='./images/speaker.jpg' align='bottom' alt=\"".str_replace("\"", "`", $flt[5])." [$row[1]]\" onClick=\"alert('QUESTION: ".$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
					."<br />\n";
				$fquery = "SELECT * FROM labels WHERE lid={$flt[6]} ORDER BY sortorder, code";
				//echo $fquery;
				$fresult = mysql_query($fquery);
				echo "\t\t\t\t<select name='{$sid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple $slstyle2>\n";
				while ($frow = mysql_fetch_array($fresult))
					{
					echo "\t\t\t\t\t<option value='{$frow['code']}'";
					if (isset($_POST[$myfield2]) && is_array($_POST[$myfield2]) && in_array($frow['code'], $_POST[$myfield2])) {echo " selected";}
					echo ">{$frow['title']}</option>\n";
					}
				echo "\t\t\t\t</select>\n\t\t\t\t</td>\n";
				$counter2++;
				$allfields[]=$myfield2;
				}
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$counter=0;
			break;
		case "R": //RANKING
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$query = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$flt[0]' ORDER BY sortorder, answer";
			$result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
			$count = mysql_num_rows($result);
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
				{
				$answers[]=array($row['code'], $row['answer']);
				}
			$counter2=0;
			for ($i=1; $i<=$count; $i++)
				{
				if ($counter2 == 4) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"; $counter=0;}
				$myfield2 = "R" . $myfield . $i . "-" . strlen($i);
				$myfield3 = $myfield . $i;
				echo "<!-- $myfield2 -- ";
				if (isset($_POST[$myfield2])) {echo $_POST[$myfield2];}
				echo " -->\n"
					."\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($i)"
					."<input type='radio' name='summary' value='$myfield2'";
				if (isset($_POST['summary']) && $_POST['summary'] == "$myfield2") {echo " CHECKED";}
				echo ">&nbsp;"
					."<img src='./images/speaker.jpg' align='bottom' alt=\"".str_replace("\"", "`", $flt[5])." [$row[1]]\" onClick=\"alert('QUESTION: ".$niceqtext." ".str_replace("'", "`", $row[1])."')\">"
					."<br />\n"
					."\t\t\t\t<select name='{$sid}X{$flt[1]}X{$flt[0]}{$i}[]' multiple $slstyle2>\n";
				foreach ($answers as $ans)
					{
					echo "\t\t\t\t\t<option value='$ans[0]'";
					if (isset($_POST[$myfield3]) && is_array($_POST[$myfield3]) && in_array("$ans[0]", $_POST[$myfield3])) {echo " selected";}
					echo ">$ans[1]</option>\n";
					}
				echo "\t\t\t\t</select>\n\t\t\t\t</td>\n";
				$counter2++;
				$allfields[]=$myfield2;
				}
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$counter=0;
			break;
		default:
			$query = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$flt[0]' ORDER BY sortorder, answer";
			$result = mysql_query($query) or die("Couldn't get answers!<br />$query<br />".mysql_error());
			while ($row=mysql_fetch_row($result))
				{
				echo "\t\t\t\t\t\t<option value='$row[0]'";
				if (isset($_POST[$myfield]) && is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {echo " selected";}
				echo ">$row[1]</option>\n";
				}
			break;
		}
	if ($flt[2] != "A" && $flt[2] != "B" && $flt[2] != "C" && $flt[2] != "E" && $flt[2] != "T" && $flt[2] != "S" && $flt[2] != "D" && $flt[2] != "R" && $flt[2] != "Q") //Have to make an exception for these types!
		{
		echo "\n\t\t\t\t</td>\n";
		}
	$currentgroup=$flt[1];
	if (!isset($counter)) {$counter=0;}
	$counter++;
	}
echo "\n\t\t\t\t</td></tr>\n";
if (isset($allfields))
	{
	$allfield=implode("|", $allfields);
	}

echo "\t\t\t</table>\n"
	."\t\t</td></tr>\n"
	."\t\t<tr><td bgcolor='#CCCCCC' align='center'>\n"
	."\t\t<font size='1' face='verdana'>&nbsp;</font></td></tr>\n"
	."\t\t\t\t<tr><td align='center'>$setfont<input type='radio' name='summary' value='$allfield'";
if (isset($_POST['summary']) && $_POST['summary'] == "$allfield") {echo " CHECKED";}
echo ">View summary of all available fields</td></tr>\n"
	."\t\t<tr><td align='center' bgcolor='#CCCCCC'>\n\t\t\t<br />\n"
	."\t\t\t<input $btstyle type='submit' value='View Stats'>\n"
	."\t\t\t<input $btstyle type='button' value='Clear' onClick=\"window.open('statistics.php?sid=$sid', '_top')\">\n"
	."\t\t<br />&nbsp;\n\t\t</td></tr>\n"
	."\t<input type='hidden' name='sid' value='$sid'>\n"
	."\t<input type='hidden' name='display' value='stats'>\n"
	."\t</form>\n"
	."</table>\n";



// DISPLAY RESULTS

if (isset($_POST['display']) && $_POST['display'])
	{
	// 1: Get list of questions with answers chosen
	for (reset($_POST); $key=key($_POST); next($_POST)) { $postvars[]=$key;} // creates array of post variable names
	foreach ($postvars as $pv) 
		{
		
		$firstletter=substr($pv,0,1);
		if ($pv != "sid" && $pv != "display" && $firstletter != "M" && $firstletter != "T" && $firstletter != "D" && $firstletter != "N" && $pv != "summary") //pull out just the fieldnames
			{
			$thisquestion = "`$pv` IN (";
			//foreach ($$pv as $condition)
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
			$aquery="SELECT code FROM {$dbprefix}answers WHERE qid=$lqid ORDER BY sortorder, answer";
			$aresult=mysql_query($aquery) or die ("Couldn't get answers<br />$aquery<br />".mysql_error());
			while ($arow=mysql_fetch_row($aresult)) // go through every possible answer
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
		elseif (substr($pv, 0, 1) == "T" && $_POST[$pv] != "")
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
		}
	// 2: Do SQL query
	$query = "SELECT count(*) FROM {$dbprefix}survey_$sid";
	$result = mysql_query($query) or die ("Couldn't get total<br />$query<br />".mysql_error());
	while ($row=mysql_fetch_row($result)) {$total=$row[0];}
	if (isset($selects) && $selects) 
		{
		$query .= " WHERE ";
		$query .= implode(" AND ", $selects);
		}
	$result=mysql_query($query) or die("Couldn't get results<br />$query<br />".mysql_error());
	while ($row=mysql_fetch_row($result)) {$results=$row[0];}
	
	// 3: Present results including option to view those rows
	echo "<br />\n<table align='center' width='95%' border='1' bgcolor='#444444' cellpadding='2' cellspacing='0' bordercolor='black'>\n"
		."\t<tr><td colspan='2' align='center'><b>$setfont<font color='orange'>Results</b></td></tr>\n"
		."\t<tr><td colspan='2' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>"
		."<b>Your query returns $results record(s)!</b><br />\n\t\t"
		."There are $total records in your survey.";
	if ($total)
		{
		$percent=sprintf("%01.2f", ($results/$total)*100);
		echo " This query represents "
			."$percent% of your total results<br />";
		}
	echo "\n\t\t<br />\n"
		."\t\t<font size='1'><b>SQL:</b> $query\n"
		."\t</td></tr>\n";
	if (isset ($selects) && $selects) {$sql=implode(" AND ", $selects);}
	if (!isset($sql) || !$sql) {$sql="NULL";}
	if ($results > 0)
		{
		echo "\t<tr>"
			."\t\t<form action='browse.php' method='post' target='_blank'><td align='right' width='50%'>\n"
			."\t\t<input type='submit' value='Browse' $btstyle>\n"
			."\t\t\t<input type='hidden' name='sid' value='$sid'>\n"
			."\t\t\t<input type='hidden' name='sql' value=\"$sql\">\n"
			."\t\t\t<input type='hidden' name='action' value='all'>\n"
			."\t\t</td></form>\n"
			."\t\t<form action='export.php' method='post' target='_blank'><td width='50%'>\n"
			."\t\t<input type='submit' value='Export' $btstyle>\n"
			."\t\t\t<input type='hidden' name='sid' value='$sid'>\n"
			."\t\t\t<input type='hidden' name='sql' value=\"$sql\">\n"
			."\t\t</td></form>\n\t</tr>\n";
		}
	echo "</table>\n";
	}

if (isset($_POST['summary']) && $_POST['summary'])
	{
	$pipepos=strpos($_POST['summary'], "|");
	if ($pipepos == 0) {$runthrough[]=$_POST['summary'];}
	else {$runthrough=explode("|", $_POST['summary']);}
	foreach ($runthrough as $rt)
		{
		// 1. Get answers for question
		if (substr($rt, 0, 1) == "M") //MULTIPLE OPTION, THEREFORE MULTIPLE FIELDS.
			{
			list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)));
			$nquery = "SELECT title, type, question, lid, other FROM {$dbprefix}questions WHERE qid='$qqid'";
			$nresult = mysql_query($nquery) or die ("Couldn't get question<br />$nquery<br />".mysql_error());
			while ($nrow=mysql_fetch_row($nresult)) 
				{
				$qtitle=$nrow[0]; $qtype=$nrow[1]; 
				$qquestion=strip_tags($nrow[2]); 
				$qlid=$nrow[3];
				$qother=$nrow[4];
				}
			
			//1. Get list of answers
			$query="SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qqid' ORDER BY sortorder, answer";
			$result=mysql_query($query) or die("Couldn't get list of answers for multitype<br />$query<br />".mysql_error());
			while ($row=mysql_fetch_row($result))
				{
				$mfield=substr($rt, 1, strlen($rt))."$row[0]";
				$alist[]=array("$row[0]", "$row[1]", $mfield);
				}
			if ($qother == "Y")
				{
				$mfield=substr($rt, 1, strlen($rt))."other";
				$alist[]=array(_OTHER, _OTHER, $mfield);
				}
			}
		elseif (substr($rt, 0, 1) == "T" || substr($rt, 0, 1) == "S") //Short and long text
			{
			list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strlen($rt)));
			$nquery = "SELECT title, type, question FROM {$dbprefix}questions WHERE qid='$qqid'";
			$nresult = mysql_query($nquery) or die("Couldn't get text question<br />$nquery<br />".mysql_error());
			while ($nrow=mysql_fetch_row($nresult))
				{
				$qtitle=$nrow[0]; $qtype=$nrow[1];
				$qquestion=strip_tags($nrow[2]);
				}
			$mfield=substr($rt, 1, strlen($rt));
			$alist[]=array("Answers", "", $mfield);
			}
		elseif (substr($rt, 0, 1) == "R") //RANKING OPTION THEREFORE CONFUSING
			{
			$lengthofnumeral=substr($rt, strchr($rt, "-"), 1);
			list($qsid, $qgid, $qqid) = explode("X", substr($rt, 1, strchr($rt, "-")-($lengthofnumeral+1)));	
	
			$nquery = "SELECT title, type, question FROM {$dbprefix}questions WHERE qid='$qqid'";
			$nresult = mysql_query($nquery) or die ("Couldn't get question<br />$nquery<br />".mysql_error());
			while ($nrow=mysql_fetch_row($nresult)) {$qtitle=$nrow[0]. " [".substr($rt, strchr($rt, "-")-($lengthofnumeral+1), $lengthofnumeral)."]"; $qtype=$nrow[1]; $qquestion=strip_tags($nrow[2]). "[Rank ".substr($rt, strchr($rt, "-")-($lengthofnumeral+1), $lengthofnumeral)."]";}
			
			$query="SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qqid' ORDER BY sortorder, answer";
			$result=mysql_query($query) or die("Couldn't get list of answers for multitype<br />$query<br />".mysql_error());
			while ($row=mysql_fetch_row($result))
				{
				$mfield=substr($rt, 1, strchr($rt, "-")-($lengthofnumeral));
				$alist[]=array("$row[0]", "$row[1]", $mfield);
				}
			}
		elseif (substr($rt, 0, 1) == "N") //NUMERICAL TYPE
			{
			list($qsid, $qgid, $qqid) = explode("X", $rt);
			$nquery = "SELECT title, type, question, qid, lid FROM {$dbprefix}questions WHERE qid='$qqid'";
			$nresult = mysql_query($nquery) or die ("Couldn't get question<br />$nquery<br />".mysql_error());
			while ($nrow=mysql_fetch_row($nresult)) {$qtitle=$nrow[0]; $qtype=$nrow[1]; $qquestion=strip_tags($nrow[2]); $qiqid=$nrow[3]; $qlid=$nrow[4];}
			echo "<br />\n<table align='center' width='95%' border='1' bgcolor='#444444' cellpadding='2' cellspacing='0' bordercolor='black'>\n"
				."\t<tr><td colspan='3' align='center'><b>$setfont<font color='orange'>Field Summary for $qtitle:</b>"
				."</td></tr>\n"
				."\t<tr><td colspan='3' align='center'><b>$setfont<font color='#EEEEEE'>$qquestion</b></font></font></td></tr>\n"
				."\t<tr>\n\t\t<td width='50%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'><b>Calculation</b></font></td>\n"
				."\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'><b>Result</b></font></td>\n"
				."\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'><b></b></font></td>\n"
				."\t</tr>\n";
			$fieldname=substr($rt, 1, strlen($rt));
			$query = "SELECT STDDEV(`$fieldname`) as stdev";
			$query .= ", SUM(`$fieldname`*1) as sum";
			$query .= ", AVG(`$fieldname`*1) as average";
			$query .= ", MIN(`$fieldname`*1) as minimum";
			$query .= ", MAX(`$fieldname`*1) as maximum";
			$query .= " FROM {$dbprefix}survey_$sid WHERE `$fieldname` IS NOT NULL AND `$fieldname` != ' '";
			if ($sql != "NULL") {$query .= " AND $sql";}
			$result=mysql_query($query) or die("Couldn't do maths testing<br />$query<br />".mysql_error());
			while ($row=mysql_fetch_array($result))
				{
				$showem[]=array("Sum", $row['sum']);
				$showem[]=array("Standard Deviation", $row['stdev']);
				$showem[]=array("Average", $row['average']);
				$showem[]=array("Minimum", $row['minimum']);
				$maximum=$row['maximum']; //we're going to put this after the quartiles for neatness
				$minimum=$row['minimum'];
				}
			
			//CALCULATE QUARTILES
			$query ="SELECT `$fieldname` FROM {$dbprefix}survey_$sid WHERE `$fieldname` IS NOT null AND `$fieldname` != ' '";
			if ($sql != "NULL") {$query .= " AND $sql";}
			$result=mysql_query($query) or die("Disaster during median calculation<br />$query<br />".mysql_error());
			$querystarter="SELECT `$fieldname` FROM {$dbprefix}survey_$sid WHERE `$fieldname` IS NOT null AND `$fieldname` != ' '";
			if ($sql != "NULL") {$querystarter .= " AND $sql";}
			$medcount=mysql_num_rows($result);
			
			//1ST QUARTILE (Q1)
			$q1=(1/4)*($medcount+1);
			$q1b=(int)((1/4)*($medcount+1));
			$q1c=$q1b-1;
			$q1diff=$q1-$q1b;
			$total=0;
			if ($q1 != $q1b)
				{
				//ODD NUMBER
				$query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $q1c, 2";
				$result=mysql_query($query) or die("1st Quartile query failed<br />".mysql_error());
				while ($row=mysql_fetch_array($result))	
					{
					if ($total == 0) 	{$total=$total-$row[$fieldname];}
					else				{$total=$total+$row[$fieldname];}
					$lastnumber=$row[$fieldname];
					}
				$q1total=$lastnumber-(1-($total*$q1diff));
				if ($q1total < $minimum) {$q1total=$minimum;}
				$showem[]=array("1st Quartile (Q1)", $q1total);
				}
			else
				{
				//EVEN NUMBER
				$query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $q1c, 1";
				$result=mysql_query($query) or die ("1st Quartile query failed<br />".mysql_error());
				while ($row=mysql_fetch_array($result)) {$showem[]=array("1st Quartile (Q1)", $row[$fieldname]);}
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
				$result=mysql_query($query) or die("What a complete mess<br />".mysql_error());
				while ($row=mysql_fetch_array($result))	{$total=$total+$row[$fieldname];}
				$showem[]=array("2nd Quartile (Median)", $total/2);
				}
			else
				{
				//EVEN NUMBER
				$query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $medianc, 1";
				$result=mysql_query($query) or die("What a complete mess<br />".mysql_error());
				while ($row=mysql_fetch_array($result))	{$showem[]=array("Median Value", $row[$fieldname]);}
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
				$result = mysql_query($query) or die("3rd Quartile query failed<br />".mysql_error());
				$lastnumber='';
				while ($row=mysql_fetch_array($result)) 
					{
					if ($total == 0)	{$total=$total-$row[$fieldname];}
					else				{$total=$total+$row[$fieldname];}
					if (!$lastnumber) {$lastnumber=$row[$fieldname];}
					}
				$q3total=$lastnumber+($total*$q3diff);
				if ($q3total < $maximum) {$q1total=$maximum;}
				$showem[]=array("3rd Quartile (Q3)", $q3total);
				}
			else
				{
				$query = $querystarter . " ORDER BY `$fieldname`*1 LIMIT $q3c, 1";
				$result = mysql_query($query) or die("3rd Quartile even query failed<br />".mysql_error());
				while ($row=mysql_fetch_array($result)) {$showem[]=array("3rd Quartile (Q3)", $row[$fieldname]);}
				}
			$total=0;
			$showem[]=array("Maximum", $maximum);
			foreach ($showem as $shw)
				{
				echo "\t<tr>\n"
					."\t\t<td align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$shw[0]</font></font></td>\n"
					."\t\t<td align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$shw[1]</td>\n"
					."\t\t<td bgcolor='#666666'></td>\n"
					."\t</tr>\n";
				}
			echo "\t<tr>\n"
				."\t\t<td colspan='3' align='center' bgcolor='#EEEEEE'>\n"
				."\t\t\t$setfont<font size='1'>*Null values are ignored in calculations<br />\n"
				."\t\t\t*Q1 and Q3 calculated using <a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>minitab method</a>"
				."</font></font>\n"
				."\t\t</td>\n"
				."\t</tr>\n";
			unset($showem);
			}
		else // NICE SIMPLE SINGLE OPTION ANSWERS
			{
			list($qsid, $qgid, $qqid) = explode("X", $rt);
			$lq = "SELECT DISTINCT qid FROM {$dbprefix}questions WHERE sid=$sid"; //GET LIST OF LEGIT QIDs FOR TESTING LATER
			$lr = mysql_query($lq);
			$legitqs[] = "DUMMY ENTRY";
			while ($lw = mysql_fetch_array($lr))
				{
				$legitqids[] = $lw['qid']; //this creates an array of question id's'
				}
			$rqid=$qqid;
			while (!in_array($rqid, $legitqids)) //checks that the qid exists in our list. If not, have to do some tricky stuff to find where qid ends and answer code begins:
				{
				$rqid = substr($rqid, 0, strlen($rqid)-1); //keeps cutting off the end until it finds the real qid
				}
			$nquery = "SELECT title, type, question, qid, lid FROM {$dbprefix}questions WHERE qid=$rqid";
			$nresult = mysql_query($nquery) or die ("Couldn't get question<br />$nquery<br />".mysql_error());
			while ($nrow=mysql_fetch_row($nresult)) 
				{
				$qtitle=$nrow[0]; $qtype=$nrow[1]; $qquestion=strip_tags($nrow[2]); $qiqid=$nrow[3]; $qlid=$nrow[4];
				}
			$alist[]=array("", _NOANSWER);
			switch($qtype)
				{
				case "A": //Array of 5 point choices
					$qanswer=substr($qqid, strlen($qiqid), strlen($qqid));
					$qquery = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qiqid' AND code='$qanswer' ORDER BY sortorder, answer";
					$qresult=mysql_query($qquery) or die ("Couldn't get answer details (Array 5p Q)<br />$qquery<br />".mysql_error());
					while ($qrow=mysql_fetch_row($qresult))
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
					$qanswer=substr($qqid, strlen($qiqid), strlen($qqid));
					$qquery = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qiqid' AND code='$qanswer' ORDER BY sortorder, answer";
					$qresult=mysql_query($qquery) or die ("Couldn't get answer details (Array 10p Q)<br />$qquery<br />".mysql_error());
					while ($qrow=mysql_fetch_row($qresult))
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
				case "C": //Array of Yes/No/Uncertain
					$qanswer=substr($qqid, strlen($qiqid), strlen($qqid));
					$qquery = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qiqid' AND code='$qanswer' ORDER BY sortorder, answer";
					$qresult=mysql_query($qquery) or die ("Couldn't get answer details<br />$qquery<br />".mysql_error());
					while ($qrow=mysql_fetch_row($qresult))
						{
						$alist[]=array("Y", _YES);
						$alist[]=array("N", _NO);
						$alist[]=array("U", _UNCERTAIN);
						$atext=$qrow[1];
						}
					$qquestion .= "<br />\n[".$atext."]";
					$qtitle .= "($qanswer)";
					break;
				case "E": //Array of Yes/No/Uncertain
					$qanswer=substr($qqid, strlen($qiqid), strlen($qqid));
					$qquery = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qiqid' AND code='$qanswer' ORDER BY sortorder, answer";
					$qresult=mysql_query($qquery) or die ("Couldn't get answer details<br />$qquery<br />".mysql_error());
					while ($qrow=mysql_fetch_row($qresult))
						{
						$alist[]=array("I", _INCREASE);
						$alist[]=array("S", _SAME);
						$alist[]=array("D", _DECREASE);
						$atext=$qrow[1];
						}
					$qquestion .= "<br />\n[".$atext."]";
					$qtitle .= "($qanswer)";
					break;
				case "F": //Array of Flexible
					$qanswer=substr($qqid, strlen($qiqid), strlen($qqid));
					$qquery = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qiqid' AND code='$qanswer' ORDER BY sortorder, answer";
					$qresult=mysql_query($qquery) or die ("Couldn't get answer details<br />$qquery<br />".mysql_error());
					while ($qrow=mysql_fetch_row($qresult))
						{
						$fquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$qlid ORDER BY sortorder, code";
						$fresult = mysql_query($fquery);
						while ($frow=mysql_fetch_array($fresult))
							{
							$alist[]=array($frow['code'], $frow['title']);
							}
						$atext=$qrow[1];
						}
					$qquestion .= "<br />\n[".$atext."]";
					$qtitle .= "($qanswer)";
					break;
				case "G": //Gender
					$alist[]=array("F", _FEMALE);
					$alist[]=array("M", _MALE);
					break;
				case "Y": //Yes\No
					$alist[]=array("Y", _YES);
					$alist[]=array("N", _NO);
					break;
				case "5": //5 Point
					for ($i=1; $i<=5; $i++)
						{
						$alist[]=array("$i", "$i");
						}
					break;
				default:
					$qquery = "SELECT code, answer FROM {$dbprefix}answers WHERE qid='$qqid' ORDER BY sortorder, answer";
					$qresult = mysql_query($qquery) or die ("Couldn't get answers list<br />$qquery<br />".mysql_error());
					while ($qrow=mysql_fetch_row($qresult))
						{
						$alist[]=array("$qrow[0]", "$qrow[1]");
						}
				}
			}
	
		//foreach ($alist as $al) {echo "$al[0] - $al[1]<br />";} //debugging line
		//foreach ($fvalues as $fv) {echo "$fv | ";} //debugging line
		
		//2. Display results
		if (isset($alist) && $alist) //JUST IN CASE SOMETHING GOES WRONG
			{
			echo "<br />\n<table align='center' width='95%' border='1' bgcolor='#444444' cellpadding='2' cellspacing='0' bordercolor='black'>\n"
				."\t<tr><td colspan='3' align='center'><b>$setfont<font color='orange'>Field Summary for $qtitle:</b>"
				."</td></tr>\n"
				."\t<tr><td colspan='3' align='center'><b>$setfont<font color='#EEEEEE'>$qquestion</b></font></font></td></tr>\n"
				."\t<tr>\n\t\t<td width='50%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'><b>Answer</b></font></td>\n"
				."\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'><b>Count</b></font></td>\n"
				."\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'><b>Percentage</b></font></td>\n"
				."\t</tr>\n";
			foreach ($alist as $al)
				{
				if (isset($al[2]) && $al[2]) //picks out alist that come from the multiple list above
					{
					if ($al[1] == _OTHER)
						{
						$query = "SELECT count(`$al[2]`) FROM {$dbprefix}survey_$sid WHERE `$al[2]` != ''";
						}
					elseif ($qtype == "T" || $qtype == "S")
						{
						$query = "SELECT count(`$al[2]`) FROM {$dbprefix}survey_$sid WHERE `$al[2]` != ''";
						}
					else
						{
						$query = "SELECT count(`$al[2]`) FROM {$dbprefix}survey_$sid WHERE `$al[2]` =";
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
					$query = "SELECT count(`$rt`) FROM {$dbprefix}survey_$sid WHERE `$rt` = '$al[0]'";
					}
				if ($sql != "NULL") {$query .= " AND $sql";}
				$result=mysql_query($query) or die ("Couldn't do count of values<br />$query<br />".mysql_error());
				echo "\n<!-- ($sql): $query -->\n\n";
				while ($row=mysql_fetch_row($result))
					{
					if ($al[0] == "") 
						{$fname=_NOANSWER;} 
					elseif ($al[0] == _OTHER || $al[0] == "Answers")
						{$fname="$al[1] <input $btstyle type='submit' value='"._BROWSE."' onclick=\"window.open('listcolumn.php?sid=$sid&column=$al[2]&sql=".urlencode($sql)."', 'results', 'width=300, height=500, left=50, top=50, resizable=yes, scrollbars=yes, menubar=no, status=no, location=no, toolbar=no')\">";}
					elseif ($qtype == "S" || $qtype == "T")
						{$fname="$al[1] $qtype<input $btstyle type='submit' value='"._BROWSE."' onclick=\"window.open('listcolumn.php?sid=$sid&column=$al[2]&sql=".urlencode($sql)."', 'results', 'width=300, height=500, left=50, top=50, resizable=yes, scrollbars=yes, menubar=no, status=no, location=no, toolbar=no')\">";}
					else
						{$fname="$al[1] ($al[0])";}
					echo "\t<tr>\n\t\t<td width='50%' align='center' bgcolor='#666666'>$setfont"
						."<font color='#EEEEEE'>$fname\n"
						."\t\t</td>\n"
						."\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$row[0]";
					if ($results > 0) {$vp=sprintf("%01.2f", ($row[0]/$results)*100)."%";} else {$vp="N/A";}
					echo "\t\t</td><td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$vp"
						."\t\t</td></tr>\n";
					}
				}
			}
		echo "</table>\n";
		unset ($alist);
		}
	}
echo "<br />&nbsp;";
echo htmlfooter("instructions.html#statistics", "Using PHPSurveyors Statistics Function");
?>