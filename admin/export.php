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
	echo "<TABLE WIDTH='350' ALIGN='CENTER'><TR><TD ALIGN='CENTER'>$setfont<B>Export Data</TD></TR>\n";
	echo "<FORM ACTION='export.php' METHOD='post'>";
	echo "<TR><TD HEIGHT='2' BGCOLOR='SILVER'></TD></TR>\n";
	echo "<TR><TD>$setfont<INPUT TYPE='radio' CHECKED NAME='style' VALUE='abrev'>Abreviated Headings<BR>";
	echo "<INPUT TYPE='radio' NAME='style' VALUE='full'>Full question headings</TD></TR>\n";
	echo "<TR><TD HEIGHT='2' BGCOLOR='SILVER'></TD></TR>\n";
	echo "<TR><TD>$setfont<INPUT TYPE='radio' CHECKED NAME='answers' VALUE='short'>Brief Answers<BR>";
	echo "<INPUT TYPE='radio' NAME='answers' VALUE='long'>Extended Answers</TD></TR>\n";
	echo "<TR><TD HEIGHT='2' BGCOLOR='SILVER'></TD></TR>\n";
	echo "<TR><TD>$setfont<INPUT TYPE='radio' CHECKED NAME='type' VALUE='doc'>Microsoft Word Format<BR>";
	echo "<INPUT TYPE='radio' NAME='type' VALUE='xls' checked>Microsoft Excel Format<BR>\n";
	echo "<INPUT TYPE='radio' NAME='type' VALUE='csv'>CSV Comma Delimited Format</TD></TR>\n";
	echo "<TR><TD HEIGHT='2' BGCOLOR='SILVER'></TD></TR>\n";
	echo "<TR><TD ALIGN='CENTER'>$setfont<INPUT TYPE='SUBMIT' VALUE='Export Data'></TD></TR>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'></FORM>";
	echo "<TR><TD ALIGN=\"CENTER\"><INPUT TYPE='SUBMIT' VALUE='Close' onClick=\"window.close()\"></TD></TR>\n";
	echo "</TABLE>\n";	
	}
else
	{
	//header ("Content-Type: application/vnd.ms-excel"); //EXCEL FILE
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
	$s="\t";
	$firstline = "id$s ";
	while ($crow = mysql_fetch_row($cresult))
		{
		if ($style == "abrev")
			{
			if ($crow[3] != "M" && $crow[3] != "A" && $crow[3] != "B" && $crow[3] != "C" && $crow[3] != "P" && $crow[3] != "O")
				{
				$firstline .= "Grp$crow[1]Qst$crow[4]$s ";
				}
			elseif ($crow[3] == "O")
				{
				$firstline .= "Grp$crow[1]Qst$crow[4]$s ";
				$firstline .= "Grp$crow[1]Qst$crow[4][comment]$s ";
				}
			else
				{
				$i2query="SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND questions.qid=$crow[0] AND questions.sid=$sid ORDER BY code";
				$i2result=mysql_query($i2query);
				while ($i2row=mysql_fetch_row($i2result))
					{
					$firstline .= "Grp$crow[1]Qst$crow[4]Opt$i2row[1]$s ";
					if ($i2row[4] == "Y") {$otherexists="Y";}
					if ($crow[3] == "P") {$firstline .= "Grp$crow[1]Qst$crow[4]Opt$i2row[1]comment$s ";}
					}
				if ($otherexists == "Y") 
					{
					$firstline .= "Grp$crow[1]Qst$crow[4]OptOther$s ";
					}			
				}
			}
		elseif ($style == "full")
			{
			if ($crow[3] != "M" && $crow[3] != "A" && $crow[3] != "B" && $crow[3] != "C" && $crow[3] != "P" && $crow[3] != "O")
				{
				$firstline .= str_replace("<BR>", " ", str_replace("\r\n", " ", $crow[5]))."$s ";
				}
			elseif ($crow[3] == "O")
				{
				$firstline .= str_replace("<BR>", " ", str_replace("\r\n", " ", $crow[5]))."$s ";
				$firstline .= str_replace("<BR>", " ", str_replace("\r\n", " ", $crow[5]))."[Comment]$s ";
				}
			else
				{
				$i2query="SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND questions.qid=$crow[0] AND questions.sid=$sid ORDER BY code";
				$i2result=mysql_query($i2query);
				while ($i2row=mysql_fetch_row($i2result))
					{
					$firstline .= str_replace("<BR>", " ", str_replace("\r\n", " ", $crow[5])).": $i2row[2]$s ";
					if ($i2row[4] == "Y") {$otherexists="Y";}
					if ($crow[3] == "P") {$firstline .= str_replace("<BR>", " ", str_replace("\r\n", " ", $crow[5])).": $i2row[2](comment)$s ";}
					}
				if ($otherexists == "Y") 
					{
					$firstline .= str_replace("<BR>", " ", str_replace("\r\n", " ", $crow[5])).": Other$s ";
					}			
				}
			}
		}
	$firstline = substr($firstline, 0, strlen($firstline)-2);
	$firstline .= "\n";
	echo $firstline;
	
	//Now dump the data
	$lq="SELECT DISTINCT qid FROM questions WHERE sid=$sid";
	$lr=mysql_query($lq);
	$legitqs[]="DUMMY ENTRY";
	while ($lw=mysql_fetch_row($lr))
		{
		$legitqs[]=$lw[0];
		}
	$surveytable="survey_{$sid}";
	$dquery = "SELECT * FROM $surveytable ORDER BY id";
	$dresult = mysql_query($dquery);
	$fieldcount = mysql_num_fields($dresult);
	while ($drow = mysql_fetch_row($dresult))
		{
		if ($answers == "short") {echo implode($s, str_replace("\r\n", " ", $drow)) . "\n";}
		elseif ($answers == "long")
			{
			$i=0;
			for ($i; $i<$fieldcount; $i++)
				{
				list($fsid, $fgid, $fqid)=split("X",mysql_field_name($dresult, $i));
				if (!$fqid) {$fqid=0;}
				while (!in_array($fqid, $legitqs))
					{
					$fqid = substr($fqid, 0, strlen($fqid)-1);
					}
				$qq="SELECT type FROM questions WHERE qid=$fqid";
				$qr=mysql_query($qq);
				while ($qrow=mysql_fetch_row($qr))
					{$ftype=$qrow[0];}
				switch ($ftype)
					{
					case "L": //DROPDOWN LIST
						$lq="SELECT * FROM answers WHERE qid=$fqid";
						$lr=mysql_query($lq);
						while ($lrow=mysql_fetch_row($lr))
							{
							if ($lrow[1] == $drow[$i]) {echo $lrow[2];} 
							}
						break;
					case "O": //DROPDOWN LIST WITH COMMENT
						$lq="SELECT * FROM answers WHERE qid=$fqid";
						$lr=mysql_query($lq);
						while ($lrow=mysql_fetch_row($lr))
							{
							if ($lrow[1] == $drow[$i]) {echo $lrow[2]; $found="Y";}
							}
						if ($found != "Y") {echo str_replace("\r\n", " ", $drow[$i]);}
						$found="";
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
				$ftype="";
				}
			echo "\n";
			}
		}
	}
?>