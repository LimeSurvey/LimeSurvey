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

if (!$sid)
	{
	//need to have a survey id
	echo "<center>You have not selected a survey!</center>";
	exit;
	}

echo "<table width='100%' border='0' bgcolor='#555555'><tr><td align='center'><font color='white'><b>Quick Statistics</b></td></tr></table>\n";

echo "<table width='100%'>\n";
echo "\t<form method='post'>\n";
// 1: Get list of questions with predefined answers from survey
$query = "SELECT qid, gid, type, title FROM questions WHERE sid='$sid' AND type IN ('5', 'G', 'L', 'O', 'M', 'P', 'Y', 'A', 'B', 'C')";
$result = mysql_query($query) or die("Couldn't do it!<br />$query<br />".mysql_error());
while ($row=mysql_fetch_row($result))
	{
	$filters[]=array("$row[0]", "$row[1]", "$row[2]", "$row[3]");
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
		echo "\t\t<b>Group $flt[1]</b></td></tr>\n\t\t<tr><td align='center'>\n";
		echo "\t\t\t<table><tr>\n";
		$counter=0;
		}
	//echo $flt[2];	//debugging line
	if ($counter == 5) {echo "\t\t\t\t</tr>\n\t\t\t\t<tr>";}
	if ($flt[2] != "A" && $flt[2] != "B" && $flt[2] != "C") //Have to make an exception for these types!
		{
		echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3]<br />\n";
		echo "\t\t\t\t<select name='QID$flt[0]GID$flt[1][]' multiple $slstyle2>\n";
		}
	$myfield = "QID$flt[0]GID$flt[1]";
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
				$myfield2 = $myfield."ANS$row[0]";
				echo "<!-- $myfield2 -- $_POST[$myfield2] -->\n";
				
				echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])<br />\n";
				echo "\t\t\t\t<select name='QID$flt[0]GID$flt[1]ANS$row[0][]' multiple $slstyle2>\n";
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
				$myfield2 = $myfield . "ANS$row[0]";
				echo "<!-- $myfield2 -- $_POST[$myfield2] -->\n";
				
				echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])<br />\n";
				echo "\t\t\t\t<select name='QID$flt[0]GID$flt[1]ANS$row[0][]' multiple $slstyle2>\n";
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
				$myfield2 = $myfield . "ANS$row[0]";
				echo "<!-- $myfield2 -- $_POST[$myfield2] -->\n";
				echo "\t\t\t\t<td align='center'>$setfont<B>$flt[3] ($row[0])<br />\n";
				echo "\t\t\t\t<select name='QID$flt[0]GID$flt[1]ANS$row[0][]' multiple $slstyle2>\n";
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
echo "\t\t<tr><td align='center' bgcolor='#CCCCCC'>\n";
echo "\t\t\t<input $btstyle type='submit' value='View Stats'>\n";
echo "\t\t\t<input $btstyle type='button' value='Clear' onClick=\"window.open('statistics.php?sid=$sid', '_top')\">\n";
echo "\t\t</td></tr>\n";
echo "\t<input type='hidden' name='sid' value='$sid'>\n";
echo "\t<input type='hidden' name='display' value='stats'>\n";
echo "\t</form>\n";
echo "</table>\n";


/// MUCKING AROUND ----
echo count($_POST) . " elements<br />";
foreach ($_POST as $post)
	{
	if (is_array($post))
		{
		foreach ($post as $pst)
			{
			echo "$pst<br />\n";
			}
		}
	else
		{
		echo "$post<br />\n";
		}
	echo "<br />\n";
	}

//for($i=0;$i<count($_POST);$i++){
//$tmpvar=$_POST[$i];
// echo("Key: ".$tmpvar.key()." Value: ".$tmpvar.value());
//}
// -----------

if ($display)
	{
	// 1: Get list of questions with answers chosen
	
	// 2: Develop SQL query
	
	// 3: Present results
	}
?>