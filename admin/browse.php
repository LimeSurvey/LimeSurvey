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

echo $htmlheader;
echo "<table width='100%' border='0' bgcolor='#555555'>\n";
echo "\t<tr><td align='center'><font color='white'><b>Browse Survey Data</b></td></tr>\n";
echo "</table>\n";

if (!mysql_selectdb($databasename, $connect))
	{
	echo "<center><b><font color='red'>ERROR: Surveyor database does not exist</font></b><br /><br />\n";
	echo "It appears that your surveyor script has not yet been set up properly.<br />\n";
	echo "Contact your System Administrator";
	echo "</body>\n</html>";
	exit;
	}
if (!$sid && !$action)
	{
	echo "<center><b>You have not selected a survey.</b></center><br />\n";
	echo "</body>\n</html>";
	exit;
	}

//CHECK IF SURVEY IS ACTIVATED AND EXISTS
$actquery = "SELECT * FROM surveys WHERE sid=$sid";
$actresult = mysql_query($actquery);
$actcount = mysql_num_rows($actresult);
if ($actcount > 0)
	{
	while ($actrow = mysql_fetch_array($actresult))
		{
		if ($actrow['active'] == "N")
			{
			echo "<center><b><font color='red'>ERROR:</font><br />\n";
			echo "This survey has not yet been activated, and subsequently there is no data to browse.</b></center>\n";
			echo "</body>\n</html>";
			exit;
			}
		else
			{
			$surveytable = "survey_{$actrow['sid']}";
			$surveyname = "{$actrow['short_title']}";
			}
		}
	}
else
	{
	echo "<center><b><font color='red'>ERROR:</font><br />\n";
	echo "There is no matching survey ($sid)</b></center>\n";
	echo "</body>\n</html>";
	exit;
	}

//OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.

//BUT FIRST, A QUICK WORD FROM OUR SPONSORS

$surveyheader = "<table width='100%' align='center' border='0' bgcolor='#EFEFEF'>\n";
$surveyheader .= "\t<tr>\n";
$surveyheader .= "\t\t<td align='center' $singleborderstyle>\n";
$surveyheader .= "\t\t\t$setfont<b>$surveyname</b>\n";
$surveyheader .= "\t\t</td>\n";
$surveyheader .= "\t</tr>\n";
$surveyheader .= "</table>\n";


if ($action == "id")
	{
	echo "$surveyheader";
	
	if (!$_POST['sql']) {echo "$surveyoptions";} // Don't show options if coming from tokens script
	
	//FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
	$fnquery = "SELECT * FROM questions, groups, surveys WHERE questions.gid=groups.gid AND groups.sid=surveys.sid AND questions.sid='$sid' ORDER BY group_name";
	$fnresult = mysql_query($fnquery);
	$fncount = mysql_num_rows($fnresult);
	//echo "$fnquery<br /><br />\n";
	
	$fnrows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
	while ($fnrow = mysql_fetch_array($fnresult)) {$fnrows[] = $fnrow; $private = $fnrow['private'];} // Get table output into array
	
	// Perform a case insensitive natural sort on group name then question title of a multidimensional array
	usort($fnrows, 'CompareGroupThenTitle');
	
	$fnames[] = array("id", "id", "id");
	
	if ($private == "N") //add token to top ofl ist is survey is not private
		{
		$fnames[] = array("token", "token", "Token ID");		
		}
	
	foreach ($fnrows as $fnrow)
		{
		$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}";
		$ftitle = "Grp{$fnrow['gid']}Qst{$fnrow['title']}";
		$fquestion = $fnrow['question'];
		if ($fnrow['type'] == "M" || $fnrow['type'] == "A" || $fnrow['type'] == "B" || $fnrow['type'] == "C" || $fnrow['type'] == "P")
			{
			$fnrquery = "SELECT * FROM answers WHERE qid={$fnrow['qid']} ORDER BY code";
			$fnrresult = mysql_query($fnrquery);
			while ($fnrrow = mysql_fetch_array($fnrresult))
				{
				$fnames[] = array("$field{$fnrrow['code']}", "$ftitle ({$fnrrow['code']})", "{$fnrow['question']} ({$fnrrow['answer']})");
				if ($fnrow['type'] == "P") {$fnames[] = array("$field{$fnrrow['code']}"."comment", "$ftitle"."comment", "{$fnrow['question']} (comment)");}
				}
			if ($fnrow['other'] == "Y")
				{
				$fnames[] = array("$field"."other", "$ftitle"."other", "{$fnrow['question']}(other)");
				}
			}
		elseif ($fnrow['type'] == "O")
			{
			$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}");
			$field2 = $field."comment";
			$ftitle2 = $ftitle."[Comment]";
			$longtitle = "{$fnrow['question']}<br />[Comment]";
			$fnames[] = array("$field2", "$ftitle2", "$longtitle");
			}
		else
			{
			$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}");
			}
		//echo "$field | $ftitle | $fquestion<br />\n";
		}

	$nfncount = count($fnames)-1;
	//SHOW INDIVIDUAL RECORD
	$idquery = "SELECT * FROM $surveytable WHERE ";
	if ($_POST['sql'])
		{
		if (get_magic_quotes_gpc) {$idquery .= stripslashes($_POST['sql']);}
		else {$idquery .= "{$_POST['sql']}";}
		}
	else {$idquery .= "id=$id";}
	$idresult = mysql_query($idquery) or die ("Couldn't get entry<br />\n$idquery<br />\n".mysql_error());
	while ($idrow = mysql_fetch_array($idresult)) {$id=$idrow['id'];}
	echo "<table align='center'>\n";
	echo "\t<tr>\n";
	echo "\t\t<td colspan='2' bgcolor='#DDDDDD' align='center'>$setfont\n";
	echo "\t\t\t<b>Viewing Answer ID $id</b><br />\n";
	echo "\t\t\t<input type='submit' $btstyle value='Edit' onClick=\"window.open('dataentry.php?action=edit&id=$id&sid=$sid&surveytable=$surveytable','_top')\" />\n";
	echo "\t\t\t<input type='submit' $btstyle value='Delete' onClick=\"window.open('dataentry.php?action=delete&id=$id&sid=$sid&surveytable=$surveytable','_top')\" />\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr><td colspan='2' bgcolor='#CCCCCC' height='1'></td></tr>\n";
	$idresult = mysql_query($idquery) or die ("Couldn't get entry<br />$idquery<br />".mysql_error());
	while ($idrow = mysql_fetch_array($idresult))
		{
		$i=0;
		for ($i; $i<$nfncount+1; $i++)
			{
			echo "\t<tr>\n";
			echo "\t\t<td bgcolor='#EFEFEF' valign='top' align='right' width='33%' style='padding-right: 5px'>$setfont<b>{$fnames[$i][2]}</b></td>\n";
			echo "\t\t<td valign='top' style='padding-left: 5px'>$setfont";
			echo htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "</td>\n";
			echo "\t</tr>\n";
			echo "\t<tr><td colspan='2' bgcolor='#CCCCCC' height='1'></td></tr>\n";
			}
		}
	echo "</table>\n";
	echo "<table width='100%'>\n";
	echo "\t<tr>\n";
	echo "\t\t<td $singleborderstyle bgcolor='#EEEEEE' align='center'>\n";
	echo "\t\t\t<input type='submit' $btstyle value='Edit' onClick=\"window.open('dataentry.php?action=edit&id=$id&sid=$sid&surveytable=$surveytable','_top');\" />\n";
	echo "\t\t\t<input type='submit' $btstyle value='Delete' onClick=\"window.open('dataentry.php?action=delete&id=$id&sid=$sid&surveytable=$surveytable','_top');\" />\n";
	if ($_POST['sql']) {echo "\t\t\t<input type='submit' $btstyle value='Close Window' onClick=\"window.close();\" />\n";}
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	}



elseif ($action == "all")
	{
	echo "$surveyheader";
	if (!$_POST['sql'])
		{echo "$surveyoptions";} //don't show options when called from another script with a filter on
	else
		{
		echo "\n<table width='100%' align='center' border='0' bgcolor='#EFEFEF'>\n";
		echo "\t<tr>\n";
		echo "\t\t<td align='center' $singleborderstyle>$setfont\n";
		echo "\t\t\tShowing Filtered Results<br />\n";
		echo "\t\t\t&nbsp;[<a href=\"javascript:window.close()\">Close</a>]";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "</table>\n";
		
		}
	//FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
	$fnquery = "SELECT * FROM questions, groups, surveys WHERE questions.gid=groups.gid AND groups.sid=surveys.sid AND questions.sid='$sid' ORDER BY group_name";
	$fnresult = mysql_query($fnquery);
	$fncount = mysql_num_rows($fnresult);
	//echo "$fnquery<br /><br />\n";
	
	$fnrows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
	while ($fnrow = mysql_fetch_assoc($fnresult)) {$fnrows[] = $fnrow; $private = $fnrow['private'];} // Get table output into array
	
	// Perform a case insensitive natural sort on group name then question title of a multidimensional array
	usort($fnrows, 'CompareGroupThenTitle');
	
	if ($private == "N") //Add token to list
		{
		$fnames[] = array("token", "Token", "Token ID", "0");
		}
	
	foreach ($fnrows as $fnrow)
		{
		if ($fnrow['type'] != "M" && $fnrow['type'] != "A" && $fnrow['type'] != "B" && $fnrow['type'] != "C" && $fnrow['type'] != "P" && $fnrow['type'] != "O")
			{
			$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}";
			$ftitle = "Grp{$fnrow['gid']}Qst{$fnrow['title']}";
			$fquestion = $fnrow['question'];
			$fnames[] = array("$field", "$ftitle", "$fquestion", "{$fnrow['gid']}");
			}
		elseif ($fnrow['type'] == "O")
			{
			$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}";
			$ftitle = "Grp{$fnrow['gid']}Qst{$fnrow['title']}";
			$fquestion = $fnrow['question'];
			$fnames[] = array("$field", "$ftitle", "$fquestion", "{$fnrow['gid']}");
			$field .= "comment";
			$ftitle .= "[comment]";
			$fquestion .= " (comment)";
			$fnames[] = array("$field", "$ftitle", "$fquestion", "{$fnrow['gid']}");
			$fncount++;
			}
		else
			{
			$i2query = "SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND questions.qid={$fnrow['qid']} AND questions.sid=$sid ORDER BY code";
			$i2result = mysql_query($i2query);
			$otherexists = "";
			while ($i2row = mysql_fetch_array($i2result))
				{
				$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}{$i2row['code']}";
				$ftitle = "Grp{$fnrow['gid']}Qst{$fnrow['title']}Opt{$i2row['code']}";
				if ($i2row['other'] == "Y") {$otherexists = "Y";}
				$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}<br />\n[{$i2row['answer']}]", "{$fnrow['gid']}");
				if ($fnrow['type'] == "P") {$fnames[] = array("$field"."comment", "$ftitle", "{$fnrow['question']}<br />\n[{$i2row['answer']}]<br />\n[Comment]", "{$fnrow['gid']}"); $fncount++;}
				$fncount++;
				}
			if ($otherexists == "Y") 
				{
				$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}"."other";
				$ftitle = "Grp{$fnrow['gid']}Qst{$fnrow['title']}OptOther";
				$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}<br />\n[Other]", "{$fnrow['gid']}");
				$fncount++;
				if ($fnrow['type'] == "P")
					{
					$fnames[] = array("$field"."comment", "$ftitle"."Comment", "{$fnrow['question']}<br />\n[Other]<br />\n[Comment]", "{$fnrow['gid']}");
					$fncount++;
					}
				}			
			
			}
		//echo "$field | $ftitle | $fquestion<br />\n";
		}
	
	//NOW LETS CREATE A TABLE WITH THOSE HEADINGS
	if ($fncount < 10) {$cellwidth = "10%";} else {$cellwidth = "100";}
	
	echo "\n\n<!-- DATA TABLE -->\n";
	if ($fncount < 10) {echo "<table width='100%' border='0'>\n";}
	else {$fnwidth = (($fncount-1)*100); echo "<table width='$fnwidth' border='0'>\n";}
	$tableheader = "\t<tr bgcolor='#000080' valign='top'>\n";
	$tableheader .= "\t\t<td bgcolor='black'><font size='1' color='white' width='$cellwidth'><b>id</b></td>\n";
	foreach ($fnames as $fn)
		{
		if (!$currentgroup)  {$currentgroup = $fn[3]; $gbc = "#000080";}
		if ($currentgroup != $fn[3]) 
			{
			$currentgroup = $fn[3];
			if ($gbc == "#000080") {$gbc = "#0000C0";}
			else {$gbc = "#000080";}
			}
		$tableheader .= "\t\t<td bgcolor='$gbc'><font size='1' color='white' width='$cellwidth'><b>";
		$tableheader .= "$fn[2]";
		$tableheader .= "</b></td>\n"; 
		}
	$tableheader .= "\t</tr>\n\n";
	
	//NOW LETS SHOW THE DATA
	if ($_POST['sql'])
		{
		$dtquery = "SELECT * FROM $surveytable WHERE ".stripcslashes($_POST['sql'])." ORDER BY id";
		}
	else
		{
		$dtquery = "SELECT * FROM $surveytable ORDER BY id";
		}
	if ($limit && !$start) {$dtquery .= " DESC LIMIT $limit";}
	if ($start && $limit) {$dtquery = "SELECT * FROM $surveytable WHERE id >= $start AND id <= $limit";}
	$dtresult = mysql_query($dtquery);
	$dtcount = mysql_num_rows($dtresult);
	$cells = $fncount+1;

	echo $tableheader;
	
	while ($dtrow = mysql_fetch_assoc($dtresult))
		{
		if (!$bgcc) {$bgcc="#EEEEEE";}
		else
			{
			if ($bgcc == "#EEEEEE") {$bgcc = "#CCCCCC";}
			else {$bgcc = "#EEEEEE";}
			}
		echo "\t<tr bgcolor='$bgcc' valign='top'>\n";
		echo "\t\t<td align='center'><font size='1'>\n";
		echo "\t\t\t<a href='browse.php?sid=$sid&action=id&id={$dtrow['id']}' title='View this record'>";
		echo "{$dtrow['id']}</a>\n";
		for ($i=0; $i<=$fncount; $i++)
			{
			echo "\t\t<td align='center'><font size='1'>";
			echo htmlspecialchars($dtrow[$fnames[$i][0]]);
			echo "</td>\n";
			}
		echo "\t</tr>\n";
		}
	echo "</table>\n<br />\n";
	
	echo "\n\n<!-- OTHER OPTIONS -->\n";
	echo "<table width='100%' align='center'>\n";
	echo "\t<tr bgcolor='#AAAAAA'>\n";
	echo "\t<form>\n";
	echo "\t\t<td width='50%' align='center'>\n";
	echo "\t\t\t$setfont View Record ID: \n";
	echo "\t\t\t<input type='text' size='4' name='id' style='height: 18; font-family: verdana; font-size: 9' />\n";
	echo "\t\t\t<input type='submit' $btstyle value='Go' style='height: 18; font-family: verdana; font-size: 9' />\n";
	echo "\t\t</td>\n";
	echo "\t<input type='hidden' name='action' value='id' />\n";
	echo "\t<input type='hidden' name='sid' value='$sid' />\n";
	echo "\t</form>\n";
	echo "\t<form>\n";
	echo "\t\t<td width='50%' align='center'>\n";
	echo "\t\t\t$setfont View records ranging from: \n";
	echo "\t\t\t<input type='text' size='4' name='start' style='height: 18; font-family: verdana; font-size: 9' />\n";
	echo "\t\t\t to <input type='text' size='4' name='limit' style='height: 18; font-family: verdana; font-size: 9' />\n";
	echo "\t\t\t<input type='submit' $btstyle value='Go' />\n";
	echo "\t\t</td>\n";
	echo "\t<input type='hidden' name='action' value='all' />\n";
	echo "\t<input type='hidden' name='sid' value='$sid' />\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	}
else
	{
	echo "$surveyheader";
	echo "$surveyoptions";
	$gnquery = "SELECT count(id) FROM $surveytable";
	$gnresult = mysql_query($gnquery);
	while ($gnrow = mysql_fetch_row($gnresult))
		{
		echo "<table width='100%' border='0'>\n";
		echo "\t<tr><td align='center'>$setfont$gnrow[0] entries in this database</td></tr>\n";
		echo "</table>\n";
		}
	}
?>