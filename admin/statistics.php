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

include("config.php");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
//Send ("Expires: " & Format$(Date - 30, "ddd, d mmm yyyy") & " " & Format$(Time, "hh:mm:ss") & " GMT ") 

$slstyle2 = "style='background-color: #EEEFFF; font-family: verdana; font-size: 10; color: #000080; width: 150'";

echo $htmlheader;

if (!$_GET['sid'] && !$_GET['sid'])
	{
	//need to have a survey id
	echo "<center>You have not selected a survey!</center>";
	exit;
	}

if ($_GET['sid']) {$sid=$_GET['sid'];}
elseif ($_POST['sid']) {$sid=$_POST['sid'];}

echo "<table width='100%' border='0' bgcolor='#555555'><tr><td align='center'><font color='white'><b>Quick Statistics</b></td></tr></table>\n";
echo $surveyoptions;
echo "<br />\n";
echo "<table width='95%' align='center' border='1' cellpadding='0' cellspacing='0' bordercolor='#555555'>\n";
echo "<tr><td align='center' bgcolor='#555555'>$setfont<font color='orange'><b>Filter Settings</b></td></tr>\n";
echo "\t<form method='post'>\n";
// 1: Get list of questions with predefined answers from survey
$query = "SELECT qid, questions.gid, type, title, group_name, question FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid='$sid' AND type IN ('5', 'G', 'L', 'O', 'M', 'P', 'Y', 'A', 'B', 'C') ORDER BY group_name, title";
$result = mysql_query($query) or die("Couldn't do it!<br />$query<br />".mysql_error());
while ($row=mysql_fetch_row($result))
	{
	$filters[]=array("$row[0]", "$row[1]", "$row[2]", "$row[3]", "$row[4]", strip_tags($row[5]));
	}
// 2: Get answers for each question
foreach ($filters as $flt)
	{
	if ($flt[1] != $currentgroup) 
		{
		if ($currentgroup)
			{
			echo "\n\t\t\t\t</td></tr>\n\t\t\t</table>\n";
			}
		echo "\t\t<tr><td bgcolor='#CCCCCC' align='center'>\n";
		echo "\t\t<b>Group $flt[1]: $flt[4]</b></td></tr>\n\t\t<tr><td align='center'>\n";
		echo "\t\t\t<table><tr>\n";
		$counter=0;
		}
	//echo $flt[2];	//debugging line
	if ($counter == 5) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>";}
	$myfield = "{$sid}X{$flt[1]}X{$flt[0]}";
	if ($flt[2] != "A" && $flt[2] != "B" && $flt[2] != "C") //Have to make an exception for these types!
		{
		echo "\t\t\t\t<td align='center'>";
		echo "$setfont<B>$flt[3]&nbsp;"; //Heading (Question No)
		if ($flt[2] == "M" || $flt[2] == "P") {$myfield = "M$myfield";}
		echo "<input type='radio' name='summary' value='$myfield'";
		if ($_POST['summary'] == "{$sid}X{$flt[1]}X{$flt[0]}" || $_POST['summary'] == "M{$sid}X{$flt[1]}X{$flt[0]}") {echo " CHECKED";}
		echo ">&nbsp;";
		echo "<img src='speaker.jpg' align='bottom' alt='$flt[5]' onClick=\"alert('QUESTION: $flt[5]')\">";
		echo "<br />\n";
		echo "\t\t\t\t<select name='";
		if ($flt[2] == "M" || $flt[2] == "P") {echo "M";}
		echo "{$sid}X{$flt[1]}X{$flt[0]}[]' multiple $slstyle2>\n";
		}
	echo "\t\t\t\t\t<!-- QUESTION TYPE = $flt[2] -->\n";
	switch ($flt[2])
		{
		case "5": // 5 point choice
			for ($i=1; $i<=5; $i++)
				{
				echo "\t\t\t\t\t<option value='$i'";
				if (is_array($_POST[$myfield]) && in_array($i, $_POST[$myfield])) {echo " selected";}
				echo ">$i</option>\n";
				}
			break;
		case "G": // Gender
			echo "\t\t\t\t\t<option value='F'";
			if (is_array($_POST[$myfield]) && in_array("F", $_POST[$myfield])) {echo " selected";}
			echo ">Female</option>\n";
			echo "\t\t\t\t\t<option value='M'";
			if (is_array($_POST[$myfield]) && in_array("M", $_POST[$myfield])) {echo " selected";}
			echo ">Male</option>\n";
			break;
		case "Y": // Yes\No
			echo "\t\t\t\t\t<option value='Y'";
			if (is_array($_POST[$myfield]) && in_array("Y", $_POST[$myfield])) {echo " selected";}
			echo ">Yes</option>\n";
			echo "\t\t\t\t\t<option value='N'";
			if (is_array($_POST[$myfield]) && in_array("N", $_POST[$myfield])) {echo " selected";}
			echo ">No</option>\n";
			break;
		// ARRAYS
		case "A": // ARRAY OF 5 POINT CHOICE QUESTIONS
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$query = "SELECT code, answer FROM answers WHERE qid='$flt[0]'";
			$result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
			while ($row=mysql_fetch_row($result))
				{
				$myfield2 = $myfield."$row[0]";
				echo "<!-- $myfield2 -- $_POST[$myfield2] -->\n";
				
				echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])<br />\n";
				echo "\t\t\t\t<select name='{$sid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple $slstyle2>\n";
				for ($i=1; $i<=5; $i++)
					{
					echo "\t\t\t\t\t<option value='$i'";
					if (is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {echo " selected";}
					if ($_POST[$myfield2] == $i) {echo " selected";}
					echo ">$i</option>\n";
					}
				echo "\t\t\t\t</select>\n";
				}
			$counter=0;
			break;
		case "B": // ARRAY OF 10 POINT CHOICE QUESTIONS
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$query = "SELECT code, answer FROM answers WHERE qid='$flt[0]'";
			$result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
			while ($row=mysql_fetch_row($result))
				{
				$myfield2 = $myfield . "$row[0]";
				echo "<!-- $myfield2 -- $_POST[$myfield2] -->\n";
				
				echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])<br />\n";
				echo "\t\t\t\t<select name='{$sid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple $slstyle2>\n";
				for ($i=1; $i<=10; $i++)
					{
					echo "\t\t\t\t\t<option value='$i'";
					if (is_array($_POST[$myfield2]) && in_array($i, $_POST[$myfield2])) {echo " selected";}
					if ($_POST[$myfield2] == $i) {echo " selected";}
					echo ">$i</option>\n";
					}
				echo "\t\t\t\t</select>\n";
				}
			$counter=0;
			break;
		case "C": // ARRAY OF YES\No\Uncertain QUESTIONS
			echo "\t\t\t\t</tr>\n\t\t\t\t<tr>\n";
			$query = "SELECT code, answer FROM answers WHERE qid='$flt[0][]'";
			$result = mysql_query($query) or die ("Couldn't get answers!<br />$query<br />".mysql_error());
			while ($row=mysql_fetch_row($result))
				{
				$myfield2 = $myfield . "$row[0]";
				echo "<!-- $myfield2 -- $_POST[$myfield2] -->\n";
				echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])<br />\n";
				echo "\t\t\t\t<select name='{$sid}X{$flt[1]}X{$flt[0]}{$row[0]}[]' multiple $slstyle2>\n";
				echo "\t\t\t\t\t<option value='Y'";
				if (is_array($_POST[$myfield2]) && in_array("Y", $_POST[$myfield2])) {echo " selected";}
				echo ">Yes</option>\n";
				echo "\t\t\t\t\t<option value='U'";
				if (is_array($_POST[$myfield2]) && in_array("U", $_POST[$myfield2])) {echo " selected";}
				echo ">Uncertain</option>\n";
				echo "\t\t\t\t\t<option value='N'";
				if (is_array($_POST[$myfield2]) && in_array("N", $_POST[$myfield2])) {echo " selected";}
				echo ">No</option>\n";
				echo "\t\t\t\t</select>\n";
				}
			$counter=0;
			break;
		default:
			$query = "SELECT code, answer FROM answers WHERE qid='$flt[0]'";
			$result = mysql_query($query) or die("Couldn't get answers!<br />$query<br />".mysql_error());
			
			while ($row=mysql_fetch_row($result))
				{
				echo "\t\t\t\t\t\t<option value='$row[0]'";
				if (is_array($_POST[$myfield]) && in_array($row[0], $_POST[$myfield])) {echo " selected";}
				echo ">$row[1]</option>\n";
				}
		}
	if ($flt[2] != "A" && $flt[2] != "B" && $flt[2] != "C") //Have to make an exception for these types!
		{
		echo "\t\t\t\t</select>\n\t\t\t\t</td>\n";
		}
	$currentgroup=$flt[1];
	$counter++;
	}
echo "\n\t\t\t\t</td></tr>\n\t\t\t</table>\n";
echo "\t\t</td></tr>\n";
echo "\t\t<tr><td align='center' bgcolor='#CCCCCC'>\n\t\t\t<br />\n";
echo "\t\t\t<input $btstyle type='submit' value='View Stats'>\n";
echo "\t\t\t<input $btstyle type='button' value='Clear' onClick=\"window.open('statistics.php?sid=$sid', '_top')\">\n";
echo "\t\t<br />&nbsp;\n\t\t</td></tr>\n";
echo "\t<input type='hidden' name='sid' value='$sid'>\n";
echo "\t<input type='hidden' name='display' value='stats'>\n";
echo "\t</form>\n";
echo "</table>\n";



// DISPLAY RESULTS

if ($_POST['display'])
	{
	// 1: Get list of questions with answers chosen
	for (reset($_POST); $key=key($_POST); next($_POST)) { $postvars[]=$key;} // creates array of post variable names
	foreach ($postvars as $pv) 
		{
		if ($pv != "sid" && $pv != "display" && substr($pv, 0, 1) != "M" && $pv != "summary") //pull out just the fieldnames
			{
			$thisquestion = "$pv IN (";
			foreach ($$pv as $condition)
				{
				$thisquestion .= "'$condition', ";
				}
			$thisquestion = substr($thisquestion, 0, -2);
			$thisquestion .= ")";
			$selects[]=$thisquestion;
			}
		elseif (substr($pv, 0, 1) == "M")
			{
			list($lsid, $lgid, $lqid) = explode("X", $pv);
			$aquery="SELECT code FROM answers WHERE qid=$lqid";
			$aresult=mysql_query($aquery) or die ("Couldn't get answers<br />$aquery<br />".mysql_error());
			while ($arow=mysql_fetch_row($aresult)) // go through every possible answer
				{
				if (in_array($arow[0], $$pv)) // only add condition if answer has been chosen
					{
					$mselects[]=substr($pv, 1, strlen($pv))."$arow[0] = 'Y'";
					}
				}
			if ($mselects) 
				{
				$thismulti=implode(" OR ", $mselects);
				$selects[]="($thismulti)";
				}
			}
		}
	// 2: Do SQL query
	$query = "SELECT count(*) FROM survey_$sid";
	$result = mysql_query($query) or die ("Couldn't get total<br />$query<br />".mysql_error());
	while ($row=mysql_fetch_row($result)) {$total=$row[0];}
	if ($selects) 
		{
		$query .= " WHERE ";
		$query .= implode(" AND ", $selects);
		}
	$result=mysql_query($query) or die("Couldn't get results<br />$query<br />".mysql_error());
	while ($row=mysql_fetch_row($result)) {$results=$row[0];}
	
	// 3: Present results including option to view those rows
	echo "<br />\n<table align='center' width='95%' border='1' bgcolor='#444444' cellpadding='2' cellspacing='0' bordercolor='black'>\n";
	echo "\t<tr><td colspan='2' align='center'><b>$setfont<font color='orange'>Results</b></td></tr>\n";
	echo "\t<tr><td colspan='2' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>";
	echo "<B>Your query returns $results record(s)!</b><br />\n\t\t";
	echo "There are $total records in your survey. This query represents ";
	$percent=sprintf("%02d", ($results/$total)*100);
	echo "$percent% of your total results<br />\n\t\t<br />\n";
	echo "\t\t<font size='1'>$query\n";
	echo "\t</td></tr>\n";
	if ($selects) {$sql=implode(" AND ", $selects);}
	if ($results > 0)
		{
		echo "\t<tr>";
		echo "\t\t<form action='browse.php' method='post'><td align='right' width='50%'>\n";
		echo "\t\t<input type='submit' value='Browse' $btstyle>\n";
		echo "\t\t\t<input type='hidden' name='sid' value='$sid'>\n";
		echo "\t\t\t<input type='hidden' name='sql' value=\"$sql\">\n";
		echo "\t\t\t<input type='hidden' name='action' value='all'>\n";
		echo "\t\t</td></form>\n";
		echo "\t\t<form action='export.php' method='post' target='_blank'><td width='50%'>\n";
		echo "\t\t<input type='submit' value='Export' $btstyle>\n";
		echo "\t\t\t<input type='hidden' name='sid' value='$sid'>\n";
		echo "\t\t\t<input type='hidden' name='sql' value=\"$sql\">\n";
		echo "\t\t</td></form>\n\t</tr>\n";
		}
	echo "</table>\n";
	}

if ($_POST['summary'])
	{
	// 1. Get answers for question
	if (substr($_POST['summary'], 0, 1) == "M") //MULTIPLE OPTION, THEREFORE MULTIPLE FIELDS. HOW THE HELL DO WE DO THIS ONE?
		{
		
		list($qsid, $qgid, $qqid) = explode("X", substr($_POST['summary'], 1, strlen($_POST['summary'])));
		$nquery = "SELECT title, type, question FROM questions WHERE qid='$qqid'";
		$nresult = mysql_query($nquery) or die ("Couldn't get question<br />$nquery<br />".mysql_error());
		while ($nrow=mysql_fetch_row($nresult)) {$qtitle=$nrow[0]; $qtype=$nrow[1]; $qquestion=strip_tags($nrow[2]);}
		
		//1. Get list of answers
		$query="SELECT code, answer FROM answers WHERE qid='$qqid'";
		$result=mysql_query($query) or die("Couldn't get list of answers for multitype<br />$query<br />".mysql_error());
		while ($row=mysql_fetch_row($result))
			{
			$mfield=substr($_POST['summary'], 1, strlen($_POST['summary']))."$row[0]";
			$alist[]=array("$row[0]", "$row[1]", $mfield);
			}
		//foreach ($mfields as $mf) {echo "$mf";} //debug line
		//2. 
		}
	else // NICE SIMPLE SINGLE OPTION ANSWERS
		{
		list($qsid, $qgid, $qqid) = explode("X", $_POST['summary']);
		$nquery = "SELECT title, type, question FROM questions WHERE qid='$qqid'";
		$nresult = mysql_query($nquery) or die ("Couldn't get question<br />$nquery<br />".mysql_error());
		while ($nrow=mysql_fetch_row($nresult)) {$qtitle=$nrow[0]; $qtype=$nrow[1]; $qquestion=strip_tags($nrow[2]);}
		$alist[]=array("", "No Answer");
		switch($qtype)
			{
			case "G": //Gender
				$alist[]=array("F", "Female");
				$alist[]=array("M", "Male");
				break;
			case "Y": //Yes\No
				$alist[]=array("Y", "Yes");
				$alist[]=array("N", "No");
				break;
			case "5": //5 Point
				for ($i=1; $i<=5; $i++)
					{
					$alist[]=array("$i", "$i");
					}
			default:
				$qquery = "SELECT code, answer FROM answers WHERE qid='$qqid' ORDER BY code";
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
	echo "<br />\n<table align='center' width='95%' border='1' bgcolor='#444444' cellpadding='2' cellspacing='0' bordercolor='black'>\n";
	echo "\t<tr><td colspan='3' align='center'><b>$setfont<font color='orange'>Field Summary for $qtitle:</b>";
	echo "</td></tr>\n";
	echo "\t<tr><td colspan='3' align='center'><b>$setfont<font color='#EEEEEE'>$qquestion</b></font></font></td></tr>\n";
	echo "\t<tr>\n\t\t<td width='50%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'><b>Answer</b></font></td>\n";
	echo "\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'><b>Count</b></font></td>\n";
	echo "\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'><b>Percentage</b></font></td>\n";
	echo "\t</tr>\n";
	foreach ($alist as $al)
		{
		if ($al[2]) //picks out alist that come from the multiple list above
			{
			$query = "SELECT count($al[2]) FROM survey_$sid WHERE $al[2] = 'Y'";
			}
		else
			{
			$query = "SELECT count({$_POST['summary']}) FROM survey_$sid WHERE {$_POST['summary']} = '$al[0]'";
			}
		if ($sql) {$query .= " AND $sql";}
		$result=mysql_query($query) or die ("Couldn't do count of values<br />$query<br />".mysql_error());
		while ($row=mysql_fetch_row($result))
			{
			if ($al[0] == "") {$fname="No Answer";} else {$fname="$al[1] ($al[0])";}
			echo "\t<tr>\n\t\t<td width='50%' align='right' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$fname:\n\t\t</td>\n";
			echo "\t\t<td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$row[0]";
			if ($results > 0) {$vp=sprintf("%02d", ($row[0]/$results)*100);} else {$vp="No Records";}
			echo "\t\t</td><td width='25%' align='center' bgcolor='#666666'>$setfont<font color='#EEEEEE'>$vp%";
			echo "\t\t</td></tr>\n";
			}
		}
	echo "</table>\n<br />";
	}
?>