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

$sid = $_GET['sid']; if (!$sid) {$sid=$_POST['sid'];}
$style = $_GET['style']; if (!$style) {$style=$_POST['style'];}
$answers = $_GET['answers']; if (!$answers) {$answers=$_POST['answers'];}
$type = $_GET['type']; if (!$type) {$type=$_POST['type'];}


if (!$style)
	{
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
	                                                     // always modified
	header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");                          // HTTP/1.0
	include ("config.php");
	echo "$htmlheader";
	echo "<table width='350' align='center'>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>\n";
	echo "\t\t\t$setfont<b>Export Data";
	if ($_POST['sql']) {echo " from Statistics Filter";}
	echo "</b>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<form action='export.php' method='post'>\n";
	echo "\t<tr><td height='2' bgcolor='silver'></td></tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td>\n";
	echo "\t\t\t$setfont<input type='radio' checked name='style' value='abrev'>Abreviated Headings<br />\n";
	echo "\t\t\t<input type='radio' name='style' value='full'>Full question headings\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr><td height='2' bgcolor='silver'></td></tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td>\n";
	echo "\t\t\t$setfont<input type='radio' checked name='answers' value='short'>Brief Answers<br />\n";
	echo "\t\t\t<input type='radio' name='answers' value='long'>Extended Answers\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr><td height='2' bgcolor='silver'></td></tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td>\n";
	echo "\t\t\t$setfont<input type='radio' checked name='type' value='doc'>Microsoft Word Format<br />\n";
	echo "\t\t\t<input type='radio' name='type' value='xls' checked>Microsoft Excel Format<br />\n";
	echo "\t\t\t<input type='radio' name='type' value='csv'>CSV Comma Delimited Format\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr><td height='2' bgcolor='silver'></td></tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>\n";
	echo "\t\t\t$setfont<input type='submit' value='Export Data'>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<input type='hidden' name='sid' value='$sid'>\n";
	if ($_POST['sql']) {echo "\t<input type='hidden' name='sql' value=\"".stripcslashes($_POST['sql'])."\">\n";}
	echo "\t</form>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align=\"center\">\n";
	echo "\t\t\t<input type='submit' value='Close' onClick=\"window.close()\">\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	echo "</body>\n</html>";
	exit;
	}

if ($type == "doc") {header("Content-Disposition: attachment; filename=survey.doc");}
elseif ($type == "xls") {header("Content-Disposition: attachment; filename=survey.xls");}
elseif ($type == "csv") {header("Content-Disposition: attachment; filename=survey.csv");}
else {header("Content-Disposition: attachment; filename=survey.doc");}
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0

include ("config.php");
//echo "$htmlheader";
//STEP 1: First line is column headings
$cquery = "SELECT * FROM questions, groups, surveys WHERE questions.gid=groups.gid AND groups.sid=surveys.sid AND questions.sid=$sid";
$cresult = mysql_query($cquery);
$crows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
while ($crow = mysql_fetch_assoc($cresult)) {$crows[] = $crow; $privatesurvey = $crow['private'];} // Get table output into array

// Perform a case insensitive natural sort on group name then question title of a multidimensional array
usort($crows, 'CompareGroupThenTitle');

$s = "\t";
$firstline = "id$s ";

if ($privatesurvey == "N" && $answers == "short") {$firstline .= "Token$s ";}
if ($privatesurvey == "N" && $answers == "long") {$firstline .= "Participant Name$s ";}

foreach ($crows as $crow)
	{
	if ($style == "abrev")
		{
		if ($crow['type'] != "M" && $crow['type'] != "A" && $crow['type'] != "B" && $crow['type'] != "C" && $crow['type'] != "P" && $crow['type'] != "O")
			{
			$firstline .= "Grp{$crow['sid']}Qst{$crow['title']}$s ";
			}
		elseif ($crow['type'] == "O")
			{
			$firstline .= "Grp{$crow['sid']}Qst{$crow['title']}$s ";
			$firstline .= "Grp{$crow['sid']}Qst{$crow['title']}[comment]$s ";
			}
		else
			{
			$i2query = "SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND questions.qid={$crow['qid']} AND questions.sid=$sid ORDER BY code";
			$i2result = mysql_query($i2query);
			while ($i2row = mysql_fetch_array($i2result))
				{
				$firstline .= "Grp{$crow['sid']}Qst{$crow['title']}Opt{$i2row['code']}$s ";
				if ($i2row['other'] == "Y") {$otherexists = "Y";}
				if ($crow['type'] == "P") {$firstline .= "Grp{$crow['sid']}Qst{$crow['title']}Opt{$i2row['code']}comment$s ";}
				}
			if ($otherexists == "Y") 
				{
				$firstline .= "Grp{$crow['sid']}Qst{$crow['title']}OptOther$s ";
				}			
			}
		}
	elseif ($style == "full")
		{
		if ($crow['type'] != "M" && $crow['type'] != "A" && $crow['type'] != "B" && $crow['type'] != "C" && $crow['type'] != "P" && $crow['type'] != "O")
			{
			$firstline .= str_replace("<BR>", " ", str_replace("\r\n", " ", $crow['question']))."$s ";
			}
		elseif ($crow['type'] == "O")
			{
			$firstline .= str_replace("<BR>", " ", str_replace("\r\n", " ", $crow['question']))."$s ";
			$firstline .= str_replace("<BR>", " ", str_replace("\r\n", " ", $crow['question']))."[Comment]$s ";
			}
		else
			{
			$i2query = "SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND questions.qid={$crow['qid']} AND questions.sid=$sid ORDER BY code";
			$i2result = mysql_query($i2query);
			while ($i2row = mysql_fetch_array($i2result))
				{
				$firstline .= str_replace("<BR>", " ", str_replace("\r\n", " ", $crow['question'])).": {$i2row['answer']}$s ";
				if ($i2row['other'] == "Y") {$otherexists = "Y";}
				if ($crow['type'] == "P") {$firstline .= str_replace("<BR>", " ", str_replace("\r\n", " ", $crow['question'])).": {$i2row['answer']}(comment)$s ";}
				}
			if ($otherexists == "Y") 
				{
				$firstline .= str_replace("<BR>", " ", str_replace("\r\n", " ", $crow['question'])).": Other$s ";
				}			
			}
		}
	}
$firstline = substr($firstline, 0, strlen($firstline)-2);
$firstline .= "\n";
echo $firstline;

//Now dump the data
$lq = "SELECT DISTINCT qid FROM questions WHERE sid=$sid";
$lr = mysql_query($lq);
$legitqs[] = "DUMMY ENTRY";
while ($lw = mysql_fetch_array($lr))
	{
	$legitqs[] = $lw['qid']; //this creates an array of question id's'
	}

$surveytable = "survey_$sid";

if ($_POST['sql']) //this applies if export has been called from the statistics package
	{
	$dquery = "SELECT * FROM $surveytable WHERE ".stripcslashes($_POST['sql'])." ORDER BY id";
	}
else // this applies for exporting everything
	{
	$dquery = "SELECT * FROM $surveytable ORDER BY id";
	}

if ($answers == "short")
	{
	$dresult = mysql_query($dquery);
	while ($drow = mysql_fetch_array($dresult, MYSQL_ASSOC))
		{
		echo implode($s, str_replace("\r\n", " ", $drow)) . "\n"; //create dump from each row
		}
	}

elseif ($answers == "long")
	{
	$dresult = mysql_query($dquery);
	$fieldcount = mysql_num_fields($dresult);
	while ($drow = mysql_fetch_array($dresult))
		{
		for ($i=0; $i<$fieldcount; $i++)
			{
			list($fsid, $fgid, $fqid) = split("X", mysql_field_name($dresult, $i));
			if (!$fqid) {$fqid = 0;}
			while (!in_array($fqid, $legitqs)) //checks that the qid exists in our list
				{
				$fqid = substr($fqid, 0, strlen($fqid)-1);
				}
			$qq = "SELECT type FROM questions WHERE qid=$fqid";
			$qr = mysql_query($qq);
			while ($qrow = mysql_fetch_array($qr, MYSQL_ASSOC))
				{$ftype = $qrow['type'];}
			switch ($ftype)
				{
				case "L": //DROPDOWN LIST
					$lq = "SELECT * FROM answers WHERE qid=$fqid AND code ='$drow[$i]'";
					$lr = mysql_query($lq);
					while ($lrow = mysql_fetch_array($lr, MYSQL_ASSOC))
						{
						//if ($lrow['code'] == $drow[$i]) {echo $lrow['answer'];} 
						echo $lrow['answer'];
						}
					break;
				case "O": //DROPDOWN LIST WITH COMMENT
					$lq = "SELECT * FROM answers WHERE qid=$fqid";
					$lr = mysql_query($lq) or die ("Could do it<br />$lq<br />".mysql_error());
					while ($lrow = mysql_fetch_array($lr, MYSQL_ASSOC))
						{
						if ($lrow['code'] == $drow[$i]) {echo $lrow['answer']; $found = "Y";}
						}
					if ($found != "Y") {echo str_replace("\r\n", " ", $drow[$i]);}
					$found = "";
					break;
				case "Y": //YES\NO
					switch($drow[$i])
						{
						case "Y": echo "Yes"; break;
						case "N": echo "No"; break;
						default: echo "N/A"; break;
						}
					break;
				case "G": //GENDER
					switch($drow[$i])
						{
						case "M": echo "Male"; break;
						case "F": echo "Female"; break;
						default: echo "N/A"; break;
						}
					break;
				case "M": //multioption
					switch($drow[$i])
						{
						case "Y": echo "Yes"; break;
						case "": echo "No"; break;
						default: echo "N/A"; break;
						}
					break;
				default:
					if (mysql_field_name($dresult, $i) == "token")
						{
						$tokenquery = "SELECT firstname, lastname FROM tokens_$sid WHERE token='$drow[$i]'";
						$tokenresult = mysql_query($tokenquery) or die ("Couldn't get token info<br />$tokenquery<br />".mysql_error());
						while ($tokenrow=mysql_fetch_array($tokenresult))
							{
							echo "{$tokenrow['lastname']}, {$tokenrow['firstname']}";
							}
						}
					else
						{
						echo str_replace("\r\n", " ", $drow[$i]);
						}
				}
			echo "$s";
			$ftype = "";
			}
		echo "\n";
		}
	}
?>