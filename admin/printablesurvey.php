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
$boxstyle = "style='border-color: #111111; border-width: 1; border-style: solid'";
include("config.php");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                                                     // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
//Send ("Expires: " & Format$(Date - 30, "ddd, d mmm yyyy") & " " & Format$(Time, "hh:mm:ss") & " GMT ")
//echo $htmlheader;
echo "<html>\n<head></head>\n<body>\n";

// PRESENT SURVEY DATAENTRY SCREEN

$desquery = "SELECT * FROM surveys WHERE sid=$sid";
$desresult = mysql_query($desquery);
while ($desrow = mysql_fetch_array($desresult))
	{
	$surveyname = $desrow['short_title'];
	$surveydesc = $desrow['description'];
	$surveyactive = $desrow['active'];
	$surveytable = "survey_{$desrow['sid']}";
	$surveyexpirydate = $desrow['expires'];
	}
//if ($surveyactive == "Y") {echo "$surveyoptions\n";}
echo "<table width='100%' cellspacing='0'>\n";
echo "\t<tr>\n";
echo "\t\t<td colspan='3' align='center'><font color='black'>\n";
echo "\t\t\t<table border='1' style='border-collapse: collapse; border-color: #111111; width: 100%'>\n";
echo "\t\t\t\t<tr><td align='center'>\n";
echo "\t\t\t\t\t<font size='5' face='verdana'><b>$surveyname</b></font>\n";
echo "\t\t\t\t\t<font size='4' face='verdana'><br />$setfont$surveydesc</font>\n";
echo "\t\t\t\t</td></tr>\n";
echo "\t\t\t</table>\n";
echo "\t\t</td>\n";
echo "\t</tr>\n";
// SURVEY NAME AND DESCRIPTION TO GO HERE

$degquery = "SELECT * FROM groups WHERE sid=$sid ORDER BY group_name";
$degresult = mysql_query($degquery);
// GROUP NAME
while ($degrow = mysql_fetch_array($degresult))
	{
	$deqquery = "SELECT * FROM questions WHERE sid=$sid AND gid={$degrow['gid']} ORDER BY title";
	$deqresult = mysql_query($deqquery);
	$deqrows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
	while ($deqrow = mysql_fetch_array($deqresult)) {$deqrows[] = $deqrow;} // Get table output into array
	
	// Perform a case insensitive natural sort on group name then question title of a multidimensional array
	usort($deqrows, 'CompareGroupThenTitle');
	
	echo "\t<tr>\n";
	echo "\t\t<td colspan='3' align='center' bgcolor='#EEEEEE' style='border-width: 1; border-style: double; border-color: #111111'>\n";
	echo "\t\t\t<font size='3' face='verdana'><b>{$degrow['group_name']}</b>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	$gid = $degrow['gid'];
	echo "\t<form action='dataentry.php' name='addsurvey'>\n";
	//Alternate bgcolor for different groups
	if ($bgc == "#EEEEEE") {$bgc = "#DDDDDD";}
	else {$bgc = "#EEEEEE";}
	if (!$bgc) {$bgc = "#EEEEEE";}
	
	foreach ($deqrows as $deqrow)
		{
		$qid = $deqrow['qid'];
		$fieldname = "$sid"."X"."$gid"."X"."$qid";
		echo "\t<tr bgcolor='$bgc'>\n";
		echo "\t\t<td valign='top' width='1%'>$setfont{$deqrow['title']}</td>\n";
		echo "\t\t<td valign='top' align='right' width='30%'>\n";
		echo "\t\t\t<b>$setfont{$deqrow['question']}</b>\n";
		//DIFFERENT TYPES OF DATA FIELD HERE
		if ($deqrow['help'])
			{
			$hh = $deqrow['help'];
			echo "\t\t\t<table width='100%' border='1'><tr><td align='center'><font size='1'>$hh</td></tr></table>\n";
			//echo "\t\t\t<img src='help.gif' alt='Help about this question' align='right' onClick=\"javascript:alert('Question {$deqrow['qid']} Help: $hh')\">\n";
			}
		echo "\t\t</td>\n";
		echo "\t\t<td style='padding-left: 20px'>\n";
		switch($deqrow['type'])
			{
			case "5":  //5 POINT CHOICE
				echo "\t\t\t$setfont<u>Please tick one response</u><br />\n";
				echo "\t\t\t<input type='checkbox' name='$fieldname' value='1' />1 \n";
				echo "\t\t\t<input type='checkbox' name='$fieldname' value='2' />2 \n";
				echo "\t\t\t<input type='checkbox' name='$fieldname' value='3' />3 \n";
				echo "\t\t\t<input type='checkbox' name='$fieldname' value='4' />4 \n";
				echo "\t\t\t<input type='checkbox' name='$fieldname' value='5' />5 \n";
				break;
			case "D":  //DATE
				echo "\t\t\t$setfont<u>Please enter your birth date:</u><br />\n";
				echo "\t\t\t<input type='text' $boxstyle name='$fieldname' size='30' value='&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;' />\n";
				break;
			case "G":  //GENDER
				echo "\t\t\t$setfont<u>Please tick <b>only one</b> of the following:</u><br />\n";
				echo "\t\t\t<input type='checkbox' name='$fieldname' value='F' />Female<br />\n";
				echo "\t\t\t<input type='checkbox' name='$fieldname' value='M' />Male<br />\n";
				break;
			case "L":  //LIST
				echo "\t\t\t$setfont<u>Please tick <b>only one</b> of the following:</u><br />\n";
				$deaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY answer";
				$dearesult = mysql_query($deaquery);
				while ($dearow = mysql_fetch_array($dearesult))
					{
					echo "\t\t\t<input type='checkbox' name='$fieldname' value='$dearow[1]' />$dearow[2]<br />\n";
					}
				break;
			case "O":  //LIST WITH COMMENT
				echo "\t\t\t$setfont<u>Please tick <b>only one</b> of the following:</u><br />\n";
				$deaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY answer";
				$dearesult = mysql_query($deaquery);
				while ($dearow = mysql_fetch_array($dearesult))
					{
					echo "\t\t\t<input type='checkbox' name='$fieldname' value='$dearow[1]' />$dearow[2]<br />\n";
					}
				echo "\t\t\t<u>Make a comment on your choice here:</u><br />\n";
				echo "\t\t\t<textarea $boxstyle cols='50' rows='8' name='$fieldname"."comment"."'></textarea>\n";
				break;
			case "M":  //MULTIPLE OPTIONS (Quite tricky really!)
				echo "\t\t\t$setfont<u>Please tick <b>any</b> that apply</u><br />\n";
				$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
				$mearesult = mysql_query($meaquery);
				while ($mearow = mysql_fetch_array($mearesult))
					{
					echo "\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y'";
					if ($mearow['default'] == "Y") {echo " checked";}
					echo " />{$mearow['answer']}<br />\n";
					}
				if ($deqrow['other'] == "Y")
					{
					echo "\t\t\tOther: <input type='text' $boxstyle size='60' name='$fieldname" . "other' />\n";
					}
				break;
			case "P":  //MULTIPLE OPTIONS WITH COMMENTS
				$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
				$mearesult = mysql_query($meaquery);
				echo "\t\t\t$setfont<u>Please tick the appropriate response for each question and provide a comment</u><br />\n";
				echo "\t\t\t<table border='0'>\n";
				while ($mearow = mysql_fetch_array($mearesult))
					{
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t<td>$setfont<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y'";
					if ($mearow[3] == "Y") {echo " checked";}
					echo " />{$mearow['answer']} </td>\n";
					//This is the commments field:
					echo "\t\t\t\t\t<td>$setfont<input type='text' $boxstyle name='$fieldname{$mearow['code']}comment' size='60' /></td>\n";
					echo "\t\t\t\t</tr>\n";
					}
				echo "\t\t\t</table>\n";
				break;
			case "S":  //SHORT TEXT
				echo "\t\t\t$setfont<u>Put your answer here:</u><br />\n";
				echo "\t\t\t<input type='text' name='$fieldname' size='60' $boxstyle />\n";
				break;
			case "T":  //LONG TEXT
				echo "\t\t\t$setfont<u>Please write your answer in the box below:</u><br />\n";
				echo "\t\t\t<textarea $boxstyle cols='50' rows='8' name='$fieldname'></textarea>\n";
				break;
			case "Y":  //YES/NO
				echo "\t\t\t$setfont<u>Please tick <b>only one</b> of the following:</u><br />\n";
				echo "\t\t\t<input type='checkbox' name='$fieldname' value='Y' />Yes<br />\n";
				echo "\t\t\t<input type='checkbox' name='$fieldname' value='N' />No<br />\n";
				break;
			case "A":  //ARRAY (5 POINT CHOICE)
				$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
				$mearesult = mysql_query($meaquery);
				echo "\t\t\t$setfont<u>Please tick the appropriate response for each question</u><br />\n";
				echo "\t\t\t<table>\n";
				while ($mearow = mysql_fetch_array($mearesult))
					{
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t<td align='right'>$setfont{$mearow['answer']}</td>\n";
					echo "\t\t\t\t\t<td>$setfont";
					for ($i=1; $i<=5; $i++)
						{
						echo "\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='$i'";
						if ($idrow[$i] == $i) {echo " checked";}
						echo " />$i&nbsp;\n";
						}
					echo "\t\t\t\t\t</td>\n";
					echo "\t\t\t\t</tr>\n";
					}
				echo "\t\t\t</table>\n";
				break;
			case "B":  //ARRAY (10 POINT CHOICE)
				$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
				$mearesult = mysql_query($meaquery);
				echo "\t\t\t$setfont<u>Please tick the appropriate response for each question</u><br />";
				echo "\t\t\t<table border='0'>\n";
				while ($mearow = mysql_fetch_array($mearesult))
					{
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t<td align='right'>$setfont{$mearow['answer']}</td>\n";
					echo "\t\t\t\t\t<td>$setfont\n";
					for ($i=1; $i<=10; $i++)
						{
						echo "\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='$i'";
						if ($idrow[$i] == $i) {echo " checked";}
						echo ">$i&nbsp;\n";
						}
					echo "\t\t\t\t\t</td>\n";
					echo "\t\t\t\t</tr>\n";
					}
				echo "\t\t\t</table>\n";
				break;
			case "C":  //ARRAY (YES/UNCERTAIN/NO)
				$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
				$mearesult = mysql_query($meaquery);
				echo "\t\t\t$setfont<u>Please tick the appropriate response for each question</u><br />\n";
				echo "\t\t\t<table>\n";
				while ($mearow = mysql_fetch_array($mearesult))
					{
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t<td align='right'>$setfont{$mearow['answer']}</td>\n";
					echo "\t\t\t\t\t<td>$setfont\n";
					echo "\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y'";
					if ($idrow[$i] == "Y") {echo " checked";}
					echo ">Yes&nbsp;\n";
					echo "\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='U'";
					if ($idrow[$i] == "U") {echo " checked";}
					echo ">Uncertain&nbsp;\n";
					echo "\t\t\t\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='N'";
					if ($idrow[$i] == "N") {echo " checked";}
					echo ">No&nbsp;\n";
					echo "\t\t\t\t\t</td>\n";
					echo "\t\t\t\t</tr>\n";
					}
				echo "\t\t\t</table>\n";
				break;
			}
		//echo "\t\t[$sid"."X"."$gid"."X"."$qid]\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<tr><td height='3' colspan='3'><hr noshade size='1' color='#111111'></td></tr>\n";
		}
	}
echo "\t<tr>\n";
echo "\t\t<td colspan='3' align='center'>\n";
echo "\t\t\t<table width='100%' border='1' style='border-collapse: collapse' bordercolor='#111111'>\n";
echo "\t\t\t\t<tr>\n";
echo "\t\t\t\t\t<td align='center'>\n";
echo "\t\t\t\t\t\t$setfont<b>Submit your survey!</b><br />\n";
echo "\t\t\t\t\t\tThank you for completing this survey. Please fax your completed survey to $surveyfaxnumber";
if ($surveyexpirydate && $surveyexpirydate != "0000-00-00")
	{
	echo " by $surveyexpirydate";
	}
echo ".\n";
echo "\t\t\t\t\t</td>\n";
echo "\t\t\t\t</tr>\n";
echo "\t\t\t</table>\n";
echo "\t\t</td>\n";
echo "\t</tr>\n";
echo "\t</form>\n";
echo "</table>\n";
echo "</body>\n</html>";

?>