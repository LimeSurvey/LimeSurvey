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
	echo "\t\t\t$setfont<b>Export Data</b>\n";
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
	echo "\t</form>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align=\"center\">\n";
	echo "\t\t\t<input type='submit' value='Close' onClick=\"window.close()\">\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	echo "</body>\n</html>";
	}
else
	{
	//header("Content-Type: application/vnd.ms-excel"); //EXCEL FILE
	//header("Content-Type: application/msword"); //EXPORT INTO MSWORD
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
	$cquery = "SELECT * FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid=$sid ORDER BY group_name, title";
	$cresult = mysql_query($cquery);
	$s = "\t";
	$firstline = "id$s ";
	while ($crow = mysql_fetch_array($cresult))
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
				$i2query="SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND questions.qid={$crow['qid']} AND questions.sid=$sid ORDER BY code";
				$i2result=mysql_query($i2query);
				while ($i2row=mysql_fetch_array($i2result))
					{
					$firstline .= "Grp{$crow['sid']}Qst{$crow['title']}Opt$i2row[1]$s ";
					if ($i2row[4] == "Y") {$otherexists="Y";}
					if ($crow['type'] == "P") {$firstline .= "Grp{$crow['sid']}Qst{$crow['title']}Opt$i2row[1]comment$s ";}
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
		$legitqs[] = $lw[0];
		}
	$surveytable = "survey_{$sid}";
	$dquery = "SELECT * FROM $surveytable ORDER BY id";
	$dresult = mysql_query($dquery);
	$fieldcount = mysql_num_fields($dresult);
	while ($drow = mysql_fetch_array($dresult))
		{
		if ($answers == "short") {echo implode($s, str_replace("\r\n", " ", $drow)) . "\n";}
		elseif ($answers == "long")
			{
			$i = 0;
			for ($i; $i<$fieldcount; $i++)
				{
				list($fsid, $fgid, $fqid) = split("X", mysql_field_name($dresult, $i));
				if (!$fqid) {$fqid = 0;}
				while (!in_array($fqid, $legitqs))
					{
					$fqid = substr($fqid, 0, strlen($fqid)-1);
					}
				$qq = "SELECT type FROM questions WHERE qid=$fqid";
				$qr = mysql_query($qq);
				while ($qrow = mysql_fetch_array($qr))
					{$ftype = $qrow['qid'];}
				switch ($ftype)
					{
					case "L": //DROPDOWN LIST
						$lq = "SELECT * FROM answers WHERE qid=$fqid";
						$lr = mysql_query($lq);
						while ($lrow = mysql_fetch_array($lr))
							{
							if ($lrow['code'] == $drow[$i]) {echo $lrow['answer'];} 
							}
						break;
					case "O": //DROPDOWN LIST WITH COMMENT
						$lq = "SELECT * FROM answers WHERE qid=$fqid";
						$lr = mysql_query($lq);
						while ($lrow = mysql_fetch_array($lr))
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
						echo str_replace("\r\n", " ", $drow[$i]);
					}
				echo "$s";
				$ftype = "";
				}
			echo "\n";
			}
		}
	}
?>