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
echo "<TABLE WIDTH='100%' BORDER='0' BGCOLOR='#555555'><TR><TD ALIGN='CENTER'><FONT COLOR='WHITE'><B>Browse Survey Data</B></TD></TR></TABLE>\n";
if (!mysql_selectdb ($databasename, $connect))
	{
	echo "<CENTER><B><FONT COLOR='RED'>ERROR: Surveyor database does not exist</FONT></B><BR><BR>";
	echo "It appears that your surveyor script has not yet been set up properly.<BR>";
	echo "Contact your System Administrator";
	exit;
	}
if (!$sid && !$action)
	{
	echo "<CENTER><B>You have not selected a survey.</B><BR>";
	exit;
	}

//CHECK IF SURVEY IS ACTIVATED AND EXISTS
$actquery = "SELECT * FROM surveys WHERE sid=$sid";
$actresult = mysql_query($actquery);
$actcount = mysql_num_rows($actresult);
if ($actcount > 0)
	{
	while ($actrow=mysql_fetch_row($actresult))
		{
		if ($actrow[4]=="N")
			{
			echo "<CENTER><B><FONT COLOR='RED'>ERROR:</FONT><BR>This survey has not yet been activated, and subsequently there is no data to browse.</B>";
			
			exit;
			}
		else
			{
			$surveytable="survey_$actrow[0]";
			$surveyname="$actrow[1]";
			}
		}
	}
else
	{
	echo "<CENTER><B><FONT COLOR='RED'>ERROR:</FONT><BR>There is no matching survey ($sid)</B>";
	exit;
	}

//OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.

//BUT FIRST, A QUICK WORD FROM OUR SPONSORS

$surveyheader = "<TABLE WIDTH='100%' ALIGN='CENTER' BORDER='0' BGCOLOR='#EFEFEF'>";
$surveyheader .= "<TR><TD ALIGN='CENTER' $singleborderstyle>";
$surveyheader .= "$setfont<B>$surveyname</B></TD></TR></TABLE>\n";


if ($action == "id")
	{
	echo "$surveyheader";
	echo "$surveyoptions";
	//FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
	$fnquery = "SELECT * FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid='$sid' ORDER BY group_name, title";
	$fnresult = mysql_query($fnquery);
	$fncount = mysql_num_rows($fnresult);
	//echo "$fnquery<BR><BR>";
	$fnames[]=array("id", "id", "id");
	while ($fnrow = mysql_fetch_row($fnresult))
		{
		$field=$fnrow[1]."X".$fnrow[2]."X".$fnrow[0];
		$ftitle="Grp$fnrow[2]Qst$fnrow[4]";
		$fquestion=$fnrow[5];
		if ($fnrow[3] == "M" || $fnrow[3] == "A" || $fnrow[3] == "B" || $fnrow[3] == "C" || $fnrow[3] == "P")
			{
			$fnrquery="SELECT * FROM answers WHERE qid=$fnrow[0] order by code";
			$fnrresult=mysql_query($fnrquery);
			while ($fnrrow=mysql_fetch_row($fnrresult))
				{
				$fnames[]=array("$field$fnrrow[1]", "$ftitle ($fnrrow[1])", "$fnrow[5]($fnrrow[2])");
				if ($fnrow[3] == "P") {$fnames[]=array("$field$nfrrow[1]"."comment", "$ftitle"."comment", "$fnrow[5] (comment)");}
				}
			if ($fnrow[7] == "Y")
				{
				$fnames[]=array("$field"."other", "$ftitle"."other", "$fnrow[5](other)");
				}
			}
		elseif ($fnrow[3] == "O")
			{
			$fnames[]=array("$field", "$ftitle", "$fnrow[5]");
			$field2=$field."comment";
			$ftitle2=$ftitle."[Comment]";
			$longtitle=$fnrow[5]."<BR>[Comment]";
			$fnames[]=array("$field2", "$ftitle2", "$longtitle");
			}
		else
			{
			$fnames[]=array("$field", "$ftitle", "$fnrow[5]");
			}
		//echo "$field | $ftitle | $fquestion<BR>";
		}

	$nfncount = count($fnames)-1;
	//SHOW INDIVIDUAL RECORD
	$idquery = "SELECT * FROM $surveytable WHERE id=$id";
	$idresult = mysql_query($idquery);
	echo "<TABLE>";
	echo "<TR><TD COLSPAN='2' BGCOLOR='#DDDDDD' ALIGN='CENTER'>$setfont<B>Viewing Answer ID $id</TD></TR>\n";
	echo "<TR><TD COLSPAN='2' BGCOLOR='#CCCCCC' HEIGHT='1'></TD></TR>\n";
	while ($idrow = mysql_fetch_row($idresult))
		{
		$i=0;
		for ($i; $i<$nfncount+1; $i++)
			{
			echo "<TR><TD BGCOLOR='#EFEFEF' VALIGN='TOP' ALIGN='RIGHT' WIDTH='33%'>$setfont<B>{$fnames[$i][2]}</TD>";
			echo "<TD VALIGN='TOP'>$setfont$idrow[$i]</TD></TR>\n";
			echo "<TR><TD COLSPAN='2' BGCOLOR='#CCCCCC' HEIGHT='1'></TD></TR>\n";
			}
		}
	echo "</TABLE>\n";
	echo "<TABLE WIDTH='100%'><TR><TD $singleborderstyle BGCOLOR='#EEEEEE' ALIGN='CENTER'>";
	echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='Edit' onClick=\"window.open('dataentry.php?action=edit&id=$id&sid=$sid&surveytable=$surveytable','_top')\">\n";
	echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='Delete' onClick=\"window.open('dataentry.php?action=delete&id=$id&sid=$sid&surveytable=$surveytable','_top')\">\n";
	echo "</TD></TR></TABLE>\n";
	}



elseif ($action == "all")
	{
	echo "$surveyheader";
	echo "$surveyoptions";
	//FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
	$fnquery = "SELECT * FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid='$sid' ORDER BY group_name, title";
	$fnresult = mysql_query($fnquery);
	$fncount = mysql_num_rows($fnresult);
	//echo "$fnquery<BR><BR>";
	while ($fnrow = mysql_fetch_row($fnresult))
		{
		if ($fnrow[3] != "M" && $fnrow[3] != "A" && $fnrow[3] != "B" && $fnrow[3] != "C" && $fnrow[3] != "P" && $fnrow[3] != "O")
			{
			$field=$fnrow[1]."X".$fnrow[2]."X".$fnrow[0];
			$ftitle="Grp$fnrow[2]Qst$fnrow[4]";
			$fquestion=$fnrow[5];
			$fnames[]=array("$field", "$ftitle", "$fquestion", "$fnrow[2]");
			}
		elseif ($fnrow[3] == "O")
			{
			$field=$fnrow[1]."X".$fnrow[2]."X".$fnrow[0];
			$ftitle="Grp$fnrow[2]Qst$fnrow[4]";
			$fquestion=$fnrow[5];
			$fnames[]=array("$field", "$ftitle", "$fquestion", "$fnrow[2]");
			$field .= "comment";
			$ftitle .= "[comment]";
			$fquestion .= " (comment)";
			$fnames[]=array("$field", "$ftitle", "$fquestion", "$fnrow[2]");
			$fncount++;
			}
		else
			{
			$i2query="SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND questions.qid=$fnrow[0] AND questions.sid=$sid ORDER BY code";
			$i2result=mysql_query($i2query);
			while ($i2row=mysql_fetch_row($i2result))
				{
				$field=$fnrow[1] . "X" . $fnrow[2] . "X" . $fnrow[0].$i2row[1];
				$ftitle="Grp$fnrow[2]Qst$fnrow[4]Opt$i2row[1]";
				if ($i2row[4] == "Y") {$otherexists="Y";}
				$fnames[]=array("$field", "$ftitle", "$fnrow[5]<BR>[$i2row[2]]", "$fnrow[2]");
				if ($fnrow[3] == "P") {$fnames[]=array("$field"."comment", "$ftitle", "$fnrow[5]<BR>[$i2row[2]]<BR>[Comment]", "$fnrow[2]"); $fncount++;}
				$fncount++;
				}
			if ($otherexists == "Y") 
				{
				$field=$fnrow[1]."X".$fnrow[2]."X".$fnrow[0]."other";
				$ftitle="Grp$fnrow[2]Qst$fnrow[4]OptOther";
				$fnames[]=array("$field", "$ftitle", "$fnrow[5]<BR>[Other]", "$fnrow[2]");
				$fncount++;
				}			
			
			}
		//echo "$field | $ftitle | $fquestion<BR>";
		}
	
	//NOW LETS CREATE A TABLE WITH THOSE HEADINGS
	if ($fncount < 10) {$cellwidth="10%";} else {$cellwidth="100";}
	
	echo "\n\n<!-- DATA TABLE -->\n";
	if ($fncount < 10) {echo "<TABLE WIDTH='100%' BORDER='0'>\n";}
	else {$fnwidth=(($fncount-1)*100); echo "<TABLE WIDTH='$fnwidth' BORDER='0'>\n";}
	$tableheader = " <TR BGCOLOR='#000080' VALIGN='TOP'>\n  <TD BGCOLOR='BLACK'><FONT SIZE='1' COLOR='WHITE' WIDTH='$cellwidth'><B>id</B></TD>\n";
	foreach ($fnames as $fn)
		{
		if (!$currentgroup)  {$currentgroup=$fn[3]; $gbc="#000080";}
		if ($currentgroup != $fn[3]) 
			{
			$currentgroup = $fn[3];
			if ($gbc == "#000080") {$gbc = "#0000C0";}
			else {$gbc = "#000080";}
			}
		$tableheader .= "  <TD BGCOLOR='$gbc'><FONT SIZE='1' COLOR='WHITE' WIDTH='$cellwidth'><B>";
		$tableheader .= "$fn[2]";
		$tableheader .="</B></TD>\n"; 
		}
	$tableheader .= " </TR>\n\n";
	
	//NOW LETS SHOW THE DATA
	$dtquery = "SELECT * FROM $surveytable ORDER BY id";
	if ($limit && !$start) {$dtquery .= " DESC LIMIT $limit";}
	if ($start && $limit) {$dtquery = "SELECT * FROM $surveytable WHERE id >= $start AND id <= $limit";}
	$dtresult = mysql_query($dtquery);
	$dtcount = mysql_num_rows($dtresult);
	$cells=$fncount+1;

	echo $tableheader;
	
	while ($dtrow = mysql_fetch_row($dtresult))
		{
		if (!$bgcc) {$bgcc="#EEEEEE";}
		else
			{
			if ($bgcc == "#EEEEEE") {$bgcc="#CCCCCC";}
			else {$bgcc="#EEEEEE";}
			}
		echo " <TR BGCOLOR='$bgcc' VALIGN='TOP'>\n";
		$i=0;
		for ($i; $i<=$fncount; $i++)
			{
			echo "  <TD ALIGN='CENTER'><FONT SIZE='1'>";
			if ($i == 0) {echo "<a href='browse.php?sid=$sid&action=id&id=$dtrow[$i]' TITLE='View this record'>";}
			echo "$dtrow[$i]";
			if ($i == 0) {echo "</a>";}			
			echo "</TD>\n";
			}
		echo " </TR>\n";
		}
	echo "</TABLE>\n<BR>";
	
	echo "\n\n<!-- OTHER OPTIONS -->\n";
	echo "<TABLE WIDTH='100%' ALIGN='CENTER'>\n";
	echo " <TR BGCOLOR='#AAAAAA'><FORM>\n  <TD WIDTH='50%' ALIGN='CENTER'>\n";
	echo "  $setfont View Record ID: ";
	echo "<INPUT TYPE='TEXT' SIZE='4' NAME='id' STYLE='HEIGHT: 18; font-family: verdana; font-size: 9'>\n";
	echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='Go' STYLE='HEIGHT: 18; font-family: verdana; font-size: 9'>\n";
	echo "  </TD>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='action' VALUE='id'>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
	echo "</FORM><FORM>";
	echo "  <TD WIDTH='50%' ALIGN='CENTER'>\n";
	echo "  $setfont View records ranging from: ";
	echo "<INPUT TYPE='TEXT' SIZE='4' NAME='start' STYLE='HEIGHT: 18; font-family: verdana; font-size: 9'>\n";
	echo " to  <INPUT TYPE='TEXT' SIZE='4' NAME='limit' STYLE='HEIGHT: 18; font-family: verdana; font-size: 9'>\n";
	echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='Go' >\n";
	echo "  </TD>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='action' VALUE='all'>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
	echo " </TR>\n";
	echo "</TABLE>\n";
	}
else
	{
	echo "$surveyheader";
	echo "$surveyoptions";
	$gnquery = "SELECT count(id) FROM $surveytable";
	$gnresult = mysql_query($gnquery);
	while ($gnrow = mysql_fetch_row($gnresult))
		{
		echo "<TABLE WIDTH='100%' BORDER='0'><TR><TD ALIGN='CENTER'>$setfont$gnrow[0] entries in this database</TD></TR></TABLE>\n";
		}
	}
?>