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
echo $htmlheader;
echo "<TABLE WIDTH='100%' BORDER='0' BGCOLOR='#555555'><TR><TD ALIGN='CENTER'><FONT COLOR='WHITE'><B>Data Entry</B></TD></TR></TABLE>\n";
if (!mysql_selectdb ($databasename, $connect))
	{
	echo "<CENTER><B><FONT COLOR='RED'>ERROR: Surveyor database does not exist</FONT></B><BR><BR>";
	echo "It appears that your surveyor script has not yet been set up properly.<BR>";
	echo "Contact your System Administrator";
	exit;
	}
if (!$sid && !$action)
	{
	echo "You have not selected a survey.";
	exit;
	}

if ($action == "insert")
	{
	echo "<CENTER><B>Inserting data into Survey $sid, tablename $surveytable</B><BR><BR>";
	$iquery = "SELECT * FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid=$sid ORDER BY group_name, title";
	$iresult = mysql_query($iquery);
	
	$insertqr = "INSERT INTO $surveytable VALUES ('',";
	
	while ($irow=mysql_fetch_row($iresult))
		{
		if ($irow[3] != "M" && $irow[3] != "A" && $irow[3] != "B" && $irow[3] != "C" && $irow[3] != "P" && $irow[3] != "O")
			{
			$fieldname=$irow[1] . "X" . $irow[2] . "X" . $irow[0];
			$insertqr .= "'" . $$fieldname . "', ";
			}
		elseif ($irow[3] == "O")
			{
			echo "TYPE O<BR>";
			$fieldname=$irow[1] . "X" . $irow[2] . "X" . $irow[0];
			echo $fieldname . "| ";
			$insertqr .= "'" . $$fieldname . "', ";
			$fieldname .= "comment";
			echo $fieldname."<br />\n";
			$insertqr .= "'" . $$fieldname . "', ";
			}
		else
			{
			$i2query="SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND questions.qid=$irow[0] AND questions.sid=$sid ORDER BY code";
			//echo $i2query."<br />\n";
			$i2result=mysql_query($i2query);
			while ($i2row=mysql_fetch_row($i2result))
				{
				$fieldname=$irow[1] . "X" . $irow[2] . "X" . $irow[0].$i2row[1];
				$insertqr .= "'" . $$fieldname . "', ";
				if ($i2row[4] == "Y") {$otherexists="Y";}
				if ($irow[3] == "P")
					{
					$fieldname2=$fieldname."comment";
					$insertqr .= "'".$$fieldname2."', ";
					}
				}
			if ($otherexists == "Y") 
				{
				$fieldname=$irow[1]."X".$irow[2]."X".$irow[0]."other";
				$insertqr .= "'".$$fieldname . "', ";
				}
			 
			}
		}
	
	$insertqr = substr($insertqr, 0, strlen($insertqr)-2);
	
	$insertqr .= ")";
	
	//echo "$insertqr."<br />\n"";
	
	$iinsert = mysql_query ($insertqr) or die ("Could not insert your data<BR>$insertqr<BR>" . mysql_error());
	
	echo "<FONT COLOR='GREEN'><B>Insert Was A Success</B><BR>";
	
	$fquery = "SELECT id FROM $surveytable ORDER BY id DESC LIMIT 1";
	$fresult = mysql_query($fquery);
	while ($frow=mysql_fetch_row($fresult))
		{
		echo "This record has been assigned the ID number, $frow[0]<BR>";
		}
	
	echo "</FONT><BR>[<a href='dataentry.php?sid=$sid'>Add another record</A>]<BR>";
	echo "[<a href='browse.php?sid=$sid&action=all&limit=100'>Browse Surveys</a>]<BR>";
	
	}

elseif ($action == "edit")
	{
	echo "$surveyheader";
	echo "$surveyoptions";
	//FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
	$fnquery = "SELECT * FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid='$sid' ORDER BY group_name, title";
	$fnresult = mysql_query($fnquery);
	$fncount = mysql_num_rows($fnresult);
	//echo "$fnquery<BR><BR>";
	$fnames[]=array("id", "id", "id", "id", "id", "id", "id");
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
				$fnames[]=array("$field$fnrrow[1]", "$ftitle ($fnrrow[1])", "$fnrow[5]", "$fnrow[3]", "$field", "$fnrrow[1]", "$fnrrow[2]", "$fnrow[0]");
				if ($fnrow[3] == "P")
					{
					$fnames[]=array("$field"."comment", "$ftitle"."comment", "$fnrow[5](comment)", "$fnrow[3]", "$field", "$fnrrow[1]", "$fnrrow[2]", "$fnrow[0]");
					}
				}
			if ($fnrow[7] == "Y")
				{
				$fnames[]=array("$field"."other", "$ftitle"."other", "$fnrow[5](other)", "$fnrow[3]", "$field", "$fnrrow[1]", "$fnrrow[2]", "$fnrow[0]");
				}
			}
		elseif ($fnrow[3] == "O")
			{
			$fnames[]=array("$field", "$ftitle", "$fnrow[5]", "$fnrow[3]", "$field", "$fnrrow[1]", "$fnrrow[2]", "$fnrow[0]");
			$field2=$field."comment";
			$ftitle2=$ftitle."[Comment]";
			$longtitle=$fnrow[5]."<BR>(Comment)";
			$fnames[]=array("$field", "$ftitle", "$fnrow[5]", "$fnrow[3]", "$field", "$fnrrow[1]", "$fnrrow[2]", "$fnrow[0]");
			}
		else
			{
			$fnames[]=array("$field", "$ftitle", "$fnrow[5]", "$fnrow[3]", "$field", "$fnrrow[1]", "$fnrrow[2]", "$fnrow[0]");
			}
	//$fnames[]=array("$field", "$ftitle", "$fnrow[5]", "$fnrow[3]");
		//echo "$field | $ftitle | $fquestion<BR>";
		}
	$nfncount = count($fnames)-1;

	//SHOW INDIVIDUAL RECORD
	$idquery = "SELECT * FROM $surveytable WHERE id=$id";
	$idresult = mysql_query($idquery);
	echo "<TABLE>";
	echo "<TR><TD COLSPAN='2' BGCOLOR='#EEEEEE' ALIGN='CENTER'>$setfont<B>Editing Answer ID $id ($nfncount)</TD></TR>\n";
	echo "<TR><TD COLSPAN='2' BGCOLOR='#CCCCCC' HEIGHT='1'></TD></TR>\n";
	while ($idrow = mysql_fetch_row($idresult))
		{
		$i=0;
		for ($i; $i<$nfncount+1; $i++)
			{
			echo "<TR><FORM>";
			echo "<TD BGCOLOR='#EEEEEE' VALIGN='TOP' ALIGN='RIGHT' WIDTH='20%'>$setfont<B>";
			if ($fnames[$i][3] != "A" && $fnames[$i][3] != "B" && $fnames[$i][3]!="C" && $fnames[$i][3]!="P" && $fnames[$i][3] != "M") 
				{
				echo "{$fnames[$i][2]}";
				}
			else
				{
				echo "{$fnames[$i][2]}";
				}
			echo "</TD>";
			echo "<TD VALIGN='TOP'>";
			//echo "-={$fnames[$i][3]}=-";
			switch($fnames[$i][3])
				{
				case "id":
					echo "$idrow[$i] <FONT COLOR=RED SIZE=1>Cannot be altered</FONT>\n";
					break;
				case "M":
					while ($fnames[$i][3]=="M")
						{
						$fieldn=substr($fnames[$i][0], 0, strlen($fnames[$i]));
						//echo substr($fnames[$i][0], strlen($fnames[$i][0])-5, 5)."<BR>";
						if (substr($fnames[$i][0], strlen($fnames[$i][0])-5, 5) == "other")
							{
							echo "$setfont<INPUT TYPE='TEXT' NAME='{$fnames[$i][0]}' VALUE='$idrow[$i]'>";
							}
						else
							{
							echo "$setfont<INPUT TYPE='CHECKBOX' NAME='{$fnames[$i][0]}' VALUE='Y'";
							if ($idrow[$i] == "Y") {echo " CHECKED";}
							echo ">{$fnames[$i][6]}<BR>";
							}
						$i++;
						}
					$i--;
					break;
				case "P":
					while ($fnames[$i][3]=="P")
						{
						$fieldn=substr($fnames[$i][0], 0, strlen($fnames[$i]));
						if (substr($fnames[$i][0], strlen($fnames[$i][0])-7, 7) == "comment")
							{
							echo "$setfont<INPUT TYPE='TEXT' NAME='{$fnames[$i][0]}' VALUE=\"$idrow[$i]\"><BR>";
							}
						else
							{
							echo "$setfont<INPUT TYPE='CHECKBOX' NAME=\"{$fnames[$i][0]}\" VALUE='Y'";
							if ($idrow[$i] == "Y") {echo " CHECKED";}
							echo ">{$fnames[$i][6]}";
							}
						$i++;
						}
					//$i--;
					break;
				case "A":
					echo "<TABLE>\n";
					while ($fnames[$i][3]=="A")
						{
						$fieldn=substr($fnames[$i][0], 0, strlen($fnames[$i]));
						echo "<TR><TD ALIGN='RIGHT'>$setfont{$fnames[$i][6]}</TD><TD>$setfont";
						for ($j=1; $j<=5; $j++)
							{
							echo "<INPUT TYPE='RADIO' NAME='{$fnames[$i][0]}' VALUE='$j'";
							if ($idrow[$i] == $j) {echo " CHECKED";}
							echo ">$j&nbsp;";
							}
						echo "</TD></TR>";
						$i++;
						}
					echo "</TABLE>\n";
					$i--;
					break;
				case "B":
					echo "<TABLE>\n";
					while ($fnames[$i][3]=="B")
						{
						$fieldn=substr($fnames[$i][0], 0, strlen($fnames[$i]));
						echo "<TR><TD ALIGN='RIGHT'>$setfont{$fnames[$i][6]}</TD><TD>$setfont";
						for ($j=1; $j<=10; $j++)
							{
							echo "<INPUT TYPE='RADIO' NAME='{$fnames[$i][0]}' VALUE='$j'";
							if ($idrow[$i] == $j) {echo " CHECKED";}
							echo ">$j&nbsp;";
							}
						echo "</TD></TR>";
						$i++;
						}
					$i--;
					echo "</TABLE>\n";
					break;
				case "C":
					echo "<TABLE>\n";
					while ($fnames[$i][3]=="C")
						{
						$fieldn=substr($fnames[$i][0], 0, strlen($fnames[$i]));
						echo "<TR><TD ALIGN='RIGHT'>$setfont{$fnames[$i][6]}</TD><TD>$setfont";
						echo "<INPUT TYPE='RADIO' NAME='{$fnames[$i][0]}' VALUE='Y'";
						if ($idrow[$i] == "Y") {echo " CHECKED";}
						echo ">Yes&nbsp;";
						echo "<INPUT TYPE='RADIO' NAME='{$fnames[$i][0]}' VALUE='U'";
						if ($idrow[$i] == "U") {echo " CHECKED";}
						echo ">Uncertain&nbsp";
						echo "<INPUT TYPE='RADIO' NAME='{$fnames[$i][0]}' VALUE='N'";
						if ($idrow[$i] == "N") {echo " CHECKED";}
						echo ">No&nbsp;";
						echo "</TD></TR>";
						$i++;
						}
					$i--;
					echo "</TABLE>\n";
					break;
				case "T": //Long Text
					echo "<TEXTAREA ROWS='5' COLS='45' NAME='{$fnames[$i][0]}'>$idrow[$i]</TEXTAREA>\n";
					break;
				case "S": //Short text
					echo "<INPUT TYPE='TEXT' NAME='{$fnames[$i][0]}' VALUE='$idrow[$i]'>\n";
					break;
				case "D": //Date
					echo "<INPUT TYPE='TEXT' SIZE='10' NAME='{$fnames[$i][0]}' VALUE='$idrow[$i]'>\n";
					break;
				case "G": //Gender
					echo "<SELECT NAME='{$fnames[$i][0]}'>\n";
					echo "  <OPTION VALUE=''";
					if ($idrow[$i] == "") {echo " SELECTED";}
					echo ">Please choose..</OPTION>\n  <OPTION VALUE='F'";
					if ($idrow[$i] == "F") {echo " SELECTED";}
					echo ">Female</OPTION>\n  <OPTION VALUE='M'";
					if ($idrow[$i] == "M") {echo " SELECTED";}
					echo ">Male</OPTION>\n<SELECT>\n";
					break;
				case "Y": //Yes/No
					echo "<SELECT NAME='{$fnames[$i][0]}'>\n";
					echo "  <OPTION VALUE=''";
					if ($idrow[$i] == "") {echo " SELECTED";}
					echo ">Please choose..</OPTION>\n  <OPTION VALUE='Y'";
					if ($idrow[$i] == "Y") {echo " SELECTED";}
					echo ">Yes</OPTION>\n  <OPTION VALUE='N'";
					if ($idrow[$i] == "N") {echo " SELECTED";}
					echo ">No</OPTION>\n</SELECT>\n";
					break;
				case "L": //Dropdown list
					$lquery="SELECT * FROM answers WHERE qid={$fnames[$i][7]} ORDER BY code";
					$lresult=mysql_query($lquery);
					echo "<SELECT NAME='{$fnames[$i][0]}'>\n";
					echo "  <OPTION VALUE=''";
					if ($idrow[$i] == "") {echo " SELECTED";}
					echo ">Please choose..</OPTION>\n";
					
					while ($llrow=mysql_fetch_row($lresult))
						{
						echo "  <OPTION VALUE='$llrow[1]'";
						if ($idrow[$i]==$llrow[1]) {echo " SELECTED";}
						echo ">$llrow[2]</OPTION>\n";
						}
					echo "</SELECT>\n";
					break;
				case "O": //List with Comment
					$lquery="SELECT * FROM answers WHERE qid={$fnames[$i][7]} ORDER BY code";
					$lresult=mysql_query($lquery);
					echo "<SELECT NAME='{$fnames[$i][0]}'>\n";
					echo "  <OPTION VALUE=''";
					if ($idrow[$i] == "") {echo " SELECTED";}
					echo ">Please choose..</OPTION>\n";
					
					while ($llrow=mysql_fetch_row($lresult))
						{
						echo "  <OPTION VALUE='$llrow[1]'";
						if ($idrow[$i]==$llrow[1]) {echo " SELECTED";}
						echo ">$llrow[2]</OPTION>\n";
						}
					echo "</SELECT>\n";
					$i++;
					echo "<BR><TEXTAREA COLS='45' ROWS='5' NAME='{$fnames[$i][0]}comment'>$idrow[$i]</TEXTAREA>\n";
					break;
				case "5": //1 to 5 point spread
					for ($x=1; $x<=5; $x++)
						{
						echo "<INPUT TYPE='RADIO' NAME='{$fnames[$i][0]}' VALUE='$x'";
						if ($idrow[$i] == $x) {echo " CHECKED";}
						echo ">$x ";
						}
					break;
				}
			//echo "$setfont$idrow[$i]";
			//echo $fnames[$i][0], $fnames[$i][1], $fnames[$i][2];
			echo "</TD></TR>\n";
			echo "<TR><TD COLSPAN='2' BGCOLOR='#CCCCCC' HEIGHT='1'></TD></TR>\n";
			}
		}
	echo "</TABLE>\n";
	echo "<TABLE WIDTH='100%'><TR><TD $singleborderstyle BGCOLOR='#EEEEEE' ALIGN='CENTER'>";
	echo "<INPUT TYPE='SUBMIT' VALUE='Update'>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='id' VALUE='$id'>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='action' VALUE='update'>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='surveytable' VALUE='survey_$sid'>\n";
	echo "</TD></FORM></TR></TABLE>\n";
	}
	

elseif ($action == "update")
	{
	echo "$surveyoptions";
	echo "<CENTER><BR><B>Updating data for Survey $sid, tablename $surveytable - Record No $id</B><BR><BR>";
	$iquery = "SELECT * FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid=$sid ORDER BY group_name, title";
	$iresult = mysql_query($iquery);
	
	$updateqr = "UPDATE $surveytable SET ";
	
	while ($irow=mysql_fetch_row($iresult))
		{
		if ($irow[3] != "M" && $irow[3] != "A" && $irow[3] != "B" && $irow[3] != "C" && $irow[3] != "O")
			{
			$fieldname=$irow[1] . "X" . $irow[2] . "X" . $irow[0];
			$updateqr .= "$fieldname = '" . $$fieldname . "', ";
			}
		elseif ($irow[3] == "O")
			{
			$fieldname=$irow[1] . "X" . $irow[2] . "X" . $irow[0];
			$updateqr .= "$fieldname = '" . $$fieldname . "', ";
			$fieldname=$irow[1] . "X" . $irow[2] . "X" . $irow[0] . "comment";
			$updateqr .= "$fieldname = '" . $$fieldname . "', ";
			
			}
		else
			{
			$i2query="SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND questions.qid=$irow[0] AND questions.sid=$sid ORDER BY code";
			//echo $i2query;
			$i2result=mysql_query($i2query);
			while ($i2row=mysql_fetch_row($i2result))
				{
				$fieldname=$irow[1] . "X" . $irow[2] . "X" . $irow[0].$i2row[1];
				$updateqr .= "$fieldname = '" . $$fieldname . "', ";
				if ($i2row[4] == "Y") {$otherexists="Y";}
				}
			if ($otherexists == "Y") 
				{
				$fieldname=$irow[1]."X".$irow[2]."X".$irow[0]."other";
				$updateqr .= "$fieldname='".$$fieldname . "', ";
				}
			}	
		}
	$updateqr = substr($updateqr, 0, -2);
	$updateqr .= " WHERE id=$id";
	$updateres=mysql_query($updateqr);
	echo "<BR><B>Record has been updated.</B><BR><BR>\n";
	echo "<a href='browse.php?sid=$sid&action=id&id=$id'>View record again</a>\n<BR>";
	echo "<a href='browse.php?sid=$sid&action=all'>Browse all records</a>\n";
	
	}

elseif ($action == "delete")
	{
	echo "<TABLE WIDTH='100%' BORDER='0' CELLSPACING='0'>";
	echo "<TR BGCOLOR='#000080'><TD COLSPAN='3' ALIGN='CENTER'><FONT COLOR='WHITE'><B>$surveyname</B>";
	echo "<BR>$setfont$surveydesc</TD></TR>\n";
	$delquery="DELETE FROM $surveytable WHERE id=$id";
	echo "<TR><TD ALIGN='CENTER'><BR>$setfont<B>Deleting Record $id</B><BR><BR>";
	$delresult=mysql_query($delquery) or die ("Couldn't delete record $id<BR>".mysql_error());
	echo "Record succesfully deleted.<BR><BR><a href='browse.php?sid=$sid&action=all'>Back to Browse</a></TD></TR>\n";
	}
	
else
	{
	// PRESENT SURVEY DATAENTRY SCREEN

	$desquery = "SELECT * FROM surveys WHERE sid=$sid";
	$desresult = mysql_query($desquery);
	while ($desrow = mysql_fetch_row($desresult))
		{
		$surveyname = $desrow[1];
		$surveydesc = $desrow[2];
		$surveyactive = $desrow[4];
		$surveytable = "survey_$desrow[0]";
		}
	if ($surveyactive == "Y") {echo "$surveyoptions";}
	echo "<TABLE WIDTH='100%' BORDER='0' CELLSPACING='0'>";
	echo "<TR BGCOLOR='#000080'><TD COLSPAN='3' ALIGN='CENTER'><FONT COLOR='WHITE'><B>$surveyname</B>";
	echo "<BR>$setfont$surveydesc</TD></TR>\n";
	echo "<FORM ACTION='dataentry.php' NAME='addsurvey' >\n";
	// SURVEY NAME AND DESCRIPTION TO GO HERE

	$degquery = "SELECT * FROM groups WHERE sid=$sid ORDER BY group_name";
	$degresult = mysql_query($degquery);
	// GROUP NAME
	while ($degrow = mysql_fetch_row($degresult))
		{
		$deqquery = "SELECT * FROM questions WHERE sid=$sid AND gid=$degrow[0] ORDER BY title";
		$deqresult = mysql_query($deqquery);
		echo "<TR><TD COLSPAN='3' ALIGN='CENTER' BGCOLOR='#AAAAAA'>$setfont<B>$degrow[2]</TD></TR>\n\n";
		$gid=$degrow[0];
		
		//Alternate bgcolor for different groups
		if ($bgc == "#EEEEEE") {$bgc = "#DDDDDD";}
		else {$bgc = "#EEEEEE";}
		if (!$bgc) {$bgc="#EEEEEE";}
		
		$deqrows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
		while ($deqrow = mysql_fetch_array($deqresult)) {$deqrows[] = $deqrow;} //Get table output into array
		
		// Perform a case insensitive natural sort on group name then question title of a multidimensional array
		usort($deqrows, 'CompareGroupThenTitle');
		
		foreach ($deqrows as $deqrow)
			{
			$qid=$deqrow['qid'];
			$fieldname="$sid"."X"."$gid"."X"."$qid";
			echo "<TR BGCOLOR='$bgc'>\n <TD VALIGN='TOP' WIDTH='1%'>$setfont{$deqrow['title']}</TD>\n";
			echo " <TD VALIGN='TOP' ALIGN='RIGHT' WIDTH='30%'>$setfont<B>{$deqrow['question']}</B></TD>\n <TD VALIGN='TOP'>$setfont";
			//DIFFERENT TYPES OF DATA FIELD HERE
			if ($deqrow[6])
				{
				$hh = addcslashes($deqrow['help'], "\0..\37'\""); //Escape ASCII decimal 0-32 plus single and double quotes to make JavaScript happy.
				$hh = htmlspecialchars($hh, ENT_QUOTES); //Change & " ' < > to HTML entities to make HTML happy.
				echo "<IMG SRC='help.gif' ALT='Help about this question' ALIGN='RIGHT' onClick=\"javascript:alert('Question {$deqrow['title']} Help: $hh')\">";
				}
			switch($deqrow['type'])
				{
				case "S":  //SHORT TEXT
					echo "<INPUT TYPE='TEXT' NAME='$fieldname'>";				
					break;
				case "M":  //MULTIPLE OPTIONS (Quite tricky really!)
					$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
					$mearesult = mysql_query($meaquery);
					while ($mearow = mysql_fetch_row($mearesult))
						{
						echo "$setfont<INPUT TYPE='checkbox' name='$fieldname$mearow[1]' VALUE='Y'";
						if ($mearow[3] == "Y") {echo " CHECKED";}
						echo ">$mearow[2]<BR>";
						}
					if ($deqrow['other'] == "Y")
						{
						echo "Other: <INPUT TYPE='TEXT' NAME='$fieldname";
						echo "other'>";
						}				
					echo "\n\n";
					break;
				case "P":  //MULTIPLE OPTIONS (with comments)
					$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
					$mearesult = mysql_query($meaquery);
					while ($mearow = mysql_fetch_row($mearesult))
						{
						echo "$setfont<INPUT TYPE='checkbox' name='$fieldname$mearow[1]' VALUE='Y'";
						if ($mearow[3] == "Y") {echo " CHECKED";}
						echo ">$mearow[2]";
						//This is the commments field:
						echo " <INPUT TYPE='TEXT' name='$fieldname$mearow[1]comment' SIZE='40'><BR>\n";
						}
					echo "\n\n";
					break;
				case "A":  //MULTI ARRAY
					$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
					$mearesult=mysql_query($meaquery);
					echo "<TABLE>";
					while ($mearow = mysql_fetch_row($mearesult))
						{
						echo "<TR><TD ALIGN='RIGHT'>$setfont$mearow[2]</tD><TD>";
						for ($i=1; $i<=5; $i++)
							{
							echo "$setfont<INPUT TYPE='RADIO' NAME='$fieldname$mearow[1]' VALUE='$i'";
							if ($idrow[$i]== $i) {echo " CHECKED";}
							echo ">$i&nbsp;";
							}
						echo "</TD></TR>\n";
						}
					echo "</TABLE>\n\n";
					break;
				case "B":  //MULTI ARRAY
					$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
					$mearesult=mysql_query($meaquery);
					echo "<TABLE>";
					while ($mearow = mysql_fetch_row($mearesult))
						{
						echo "<TR><TD ALIGN='RIGHT'>$setfont$mearow[2]</tD><TD>";
						for ($i=1; $i<=10; $i++)
							{
							echo "$setfont<INPUT TYPE='RADIO' NAME='$fieldname$mearow[1]' VALUE='$i'";
							if ($idrow[$i]== $i) {echo " CHECKED";}
							echo ">$i&nbsp;";
							}
						echo "</TD></TR>";
						}
					echo "</TABLE>\n\n";
					break;
				case "C":  //MULTI ARRAY
					$meaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY code";
					$mearesult=mysql_query($meaquery);
					echo "<TABLE>";
					while ($mearow = mysql_fetch_row($mearesult))
						{
						echo "<TR><TD ALIGN='RIGHT'>$setfont$mearow[2]</tD><TD>";
						echo "$setfont<INPUT TYPE='RADIO' NAME='$fieldname$mearow[1]' VALUE='Y'";
						if ($idrow[$i]== "Y") {echo " CHECKED";}
						echo ">Yes&nbsp;";
						echo "$setfont<INPUT TYPE='RADIO' NAME='$fieldname$mearow[1]' VALUE='U'";
						if ($idrow[$i]== "U") {echo " CHECKED";}
						echo ">Uncertain&nbsp;";
						echo "$setfont<INPUT TYPE='RADIO' NAME='$fieldname$mearow[1]' VALUE='N'";
						if ($idrow[$i]== "N") {echo " CHECKED";}
						echo ">No&nbsp;";
						echo "</TD></TR>";
						}
					echo "</TABLE>\n\n";
					break;
				case "L":  //DROPDOWN LIST
					$deaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY answer";
					$dearesult = mysql_query($deaquery);
					echo "<SELECT NAME='$fieldname'>\n";
					while ($dearow = mysql_fetch_row($dearesult))
						{
						echo "  <OPTION VALUE='$dearow[1]'";
						if ($dearow[3] == "Y") {echo " SELECTED"; $defexists="Y";}
						echo ">$dearow[2]</OPTION>\n";
						}
					if (!$defexists) {echo "  <OPTION SELECTED VALUE=''>Please choose..</OPTION>\n";}
					echo "</SELECT>\n\n";
					break;
				case "O":  //LIST WITH COMMENT
					$deaquery = "SELECT * FROM answers WHERE qid={$deqrow['qid']} ORDER BY answer";
					$dearesult = mysql_query($deaquery);
					echo "<SELECT NAME='$fieldname'>\n";
					while ($dearow = mysql_fetch_row($dearesult))
						{
						echo "  <OPTION VALUE='$dearow[1]'";
						if ($dearow[3] == "Y") {echo " SELECTED"; $defexists="Y";}
						echo ">$dearow[2]</OPTION>\n";
						}
					if (!$defexists) {echo "  <OPTION SELECTED VALUE=''>Please choose..</OPTION>\n";}
					echo "</SELECT>\n\n";
					echo "<BR>Comment:<BR><TEXTAREA COLS='40' ROWS='5' NAME='";
					echo $fieldname;
					echo "comment'>$idrow[$i]</TEXTAREA>\n";
					break;
				case "T":  //LONG TEXT
					echo "\n<TEXTAREA COLS='40' ROWS='5' NAME='$fieldname'></TEXTAREA>\n\n";
					break;
				case "D":  //DATE
					echo "<INPUT TYPE='TEXT' NAME='$fieldname' SIZE='10'>\n\n";
					break;
				case "5":  //5 Point Choice
					echo "<INPUT TYPE='RADIO' NAME='$fieldname' VALUE='1'>1 ";
					echo "<INPUT TYPE='RADIO' NAME='$fieldname' VALUE='2'>2 ";
					echo "<INPUT TYPE='RADIO' NAME='$fieldname' VALUE='3'>3 ";
					echo "<INPUT TYPE='RADIO' NAME='$fieldname' VALUE='4'>4 ";
					echo "<INPUT TYPE='RADIO' NAME='$fieldname' VALUE='5'>5 ";
					echo "\n\n";
					break;
				case "G":  //Gender
					echo "<SELECT NAME='$fieldname'>\n";
					echo " <OPTION SELECTED VALUE=''>Please Choose..</OPTION>\n";
					echo " <OPTION VALUE='F'>Female</OPTION>\n";
					echo " <OPTION VALUE='M'>Male</OPTION>\n";
					echo "</SELECT>\n\n";
					break;
				case "Y":  //YesNo
					echo "<SELECT NAME='$fieldname'>\n";
					echo " <OPTION SELECTED VALUE=''>Please choose..</OPTION>\n";
					echo " <OPTION VALUE='Y'>Yes</OPTION>\n";
					echo " <OPTION VALUE='N'>No</OPTION>\n";
					echo "</SELECT>\n\n";
					break;
				}
			//echo " [$sid"."X"."$gid"."X"."$qid]";
			echo "</TD></TR><TR><TD COLSPAN='3' HEIGHT='2' BGCOLOR='SILVER'></TD></TR>\n";		
			}		
		}
	
	if ($surveyactive == "Y")
		{
		echo "<TR><TD COLSPAN='3' ALIGN='CENTER' BGCOLOR='#AAAAAA'><INPUT TYPE='SUBMIT' VALUE='Submit Survey'></TD></TR>\n";
		}
	else
		{
		echo "<TR><TD COLSPAN='3' ALIGN='CENTER' BGCOLOR='#AAAAAA'><FONT COLOR='RED'><B>This is a test survey only - it is not yet activated</TD></TR>\n";	
		}
	echo "<INPUT TYPE='HIDDEN' NAME='action' VALUE='insert'>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='surveytable' VALUE='$surveytable'>\n";
	echo "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
	echo "</FORM></TABLE>\n";
	}

?>