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

$sid = $_GET['sid'];

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
echo "<html>\n<head>\n";
echo "<meta http-equiv='content-script-type' content='text/javascript' />\n";
echo "</head>\n<body>\n";

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
	$surveyfaxto = $desrow['faxto'];
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
	//Alternate bgcolor for different groups
	if ($bgc == "#EEEEEE") {$bgc = "#DDDDDD";}
	else {$bgc = "#EEEEEE";}
	if (!$bgc) {$bgc = "#EEEEEE";}
	
	foreach ($deqrows as $deqrow)
		{
		//GET ANY CONDITIONS THAT APPLY TO THIS QUESTION
		$explanation = ""; //reset conditions explanation
		$x=0;
		$distinctquery="SELECT DISTINCT cqid, questions.title FROM conditions, questions WHERE conditions.cqid=questions.qid AND conditions.qid={$deqrow['qid']} ORDER BY cqid";
		$distinctresult=mysql_query($distinctquery);
		while ($distinctrow=mysql_fetch_array($distinctresult))
			{
			if ($x > 0) {$explanation .= " <i>and</i> ";}
			$explanation .= "if you answered ";
			$conquery="SELECT cid, cqid, questions.title, questions.question, value, questions.type FROM conditions, questions WHERE conditions.cqid=questions.qid AND conditions.cqid={$distinctrow['cqid']} AND conditions.qid={$deqrow['qid']}";
			$conresult=mysql_query($conquery);
			while ($conrow=mysql_fetch_array($conresult))
				{
				if ($conrow['type'] == "Y")
					{
					switch ($conrow['value'])
						{
						case "Y":
							$conditions[]="Yes";
							break;
						case "N":
							$conditions[]="No";
						}
					}
				$ansquery="SELECT answer FROM answers WHERE qid='{$conrow['cqid']}' AND code='{$conrow['value']}'";
				$ansresult=mysql_query($ansquery);
				while ($ansrow=mysql_fetch_array($ansresult))
					{
					$conditions[]=$ansrow['answer'];
					}
				}
			if (count($conditions) > 1)
				{
				$explanation .=  "'".implode("' or '", $conditions)."'";	
				}
			else
				{
				$explanation .= "'".$conditions[0]."'";
				}
			unset($conditions);
			$explanation .= " to question '".$distinctrow['title']."'";
			$x++;
			}
		
		if ($explanation) 
			{
			$explanation = "[Answer this question ".$explanation."]";
			echo "<tr bgcolor='$bgc'><td colspan='3'>$setfont$explanation</font></td></tr>\n";
			}
		
		//END OF GETTING CONDITIONS
		
		$qid = $deqrow['qid'];
		$fieldname = "$sid"."X"."$gid"."X"."$qid";
		echo "\t<tr bgcolor='$bgc'>\n";
		echo "\t\t<td valign='top' align='left' colspan='3'>\n";
		echo "\t\t\t<b>$setfont{$deqrow['title']}: {$deqrow['question']}</b>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		//DIFFERENT TYPES OF DATA FIELD HERE
		echo "\t<tr bgcolor='$bgc'>\n";
		echo "\t\t<td width='15%' valign='top'>\n";
		if ($deqrow['help'])
			{
			$hh = $deqrow['help'];
			echo "\t\t\t<table width='100%' border='1'><tr><td align='center'><font size='1'>$hh</td></tr></table>\n";

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
				echo "\t\t\t$setfont<u>Please enter a date:</u><br />\n";
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
					echo "\t\t\t<input type='checkbox' name='$fieldname' value='{$dearow['code']}' />{$dearow['answer']}<br />\n";
					}
				break;
			case "O":  //LIST WITH COMMENT
				echo "\t\t\t$setfont<u>Please tick <b>only one</b> of the following:</u><br />\n";
				$deaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY answer";
				$dearesult = mysql_query($deaquery);
				while ($dearow = mysql_fetch_array($dearesult))
					{
					echo "\t\t\t<input type='checkbox' name='$fieldname' value='{$dearow['code']}' />{$dearow['answer']}<br />\n";
					}
				echo "\t\t\t<u>Make a comment on your choice here:</u><br />\n";
				echo "\t\t\t<textarea $boxstyle cols='50' rows='8' name='$fieldname"."comment"."'></textarea>\n";
				break;
			case "R":  //RANKING Type Question
				$reaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
				$rearesult = mysql_query($reaquery) or die ("Couldn't get ranked answers<br />".mysql_error());
				$reacount = mysql_num_rows($rearesult);
				echo "\t\t\t$setfont<u>Please number each box in order of preference from 1 to $reacount</u><br />\n";
				while ($rearow = mysql_fetch_array($rearesult))
					{
					echo "\t\t\t<table cellspacing='1' cellpadding='0'><tr><td width='20' height='20' bgcolor='white' style='border: solid 1 #111111'>&nbsp;</td>\n";
					echo "\t\t\t<td valign='middle'>$setfont{$rearow['answer']}</td></tr></table>\n";
					}
				break;
			case "M":  //MULTIPLE OPTIONS (Quite tricky really!)
				echo "\t\t\t$setfont<u>Please tick <b>any</b> that apply</u><br />\n";
				$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
				$mearesult = mysql_query($meaquery);
				while ($mearow = mysql_fetch_array($mearesult))
					{
					echo "\t\t\t<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y' />{$mearow['answer']}<br />\n";
					}
				if ($deqrow['other'] == "Y")
					{
					echo "\t\t\tOther: <input type='text' $boxstyle size='60' name='$fieldname" . "other' />\n";
					}
				break;
			case "P":  //MULTIPLE OPTIONS WITH COMMENTS
				$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
				$mearesult = mysql_query($meaquery);
				echo "\t\t\t$setfont<u>Please tick any that apply and provide a comment</u><br />\n";
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
				echo "\t\t\t$setfont<u>Please write your answer here:</u><br />\n";
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
				echo "\t\t\t$setfont<u>Please tick the appropriate response for each item</u><br />\n";
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
				echo "\t\t\t$setfont<u>Please tick the appropriate response for each item</u><br />";
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
				echo "\t\t\t$setfont<u>Please tick the appropriate response for each item</u><br />\n";
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
echo "\t\t\t\t\t\tThank you for completing this survey. Please fax your completed survey to $surveyfaxto";
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
echo "</table>\n";
echo "</body>\n</html>";

?>