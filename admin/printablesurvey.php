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
$boxstyle="STYLE='border-color: #111111; border-width: 1; border-style: solid'";
include("config.php");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
//Send ("Expires: " & Format$(Date - 30, "ddd, d mmm yyyy") & " " & Format$(Time, "hh:mm:ss") & " GMT ") 
//echo $htmlheader;
	// PRESENT SURVEY DATAENTRY SCREEN

	$desquery = "SELECT * FROM surveys WHERE sid=$sid";
	$desresult = mysql_query($desquery);
	while ($desrow=mysql_fetch_row($desresult))
		{
		$surveyname=$desrow[1];
		$surveydesc=$desrow[2];
		$surveyactive=$desrow[4];
		$surveytable="survey_$desrow[0]";
		$surveyexpirydate=$desrow[6];
		}
	//if ($surveyactive == "Y") {echo "$surveyoptions";}
	echo "<TABLE WIDTH='100%' CELLSPACING='0'>";
	echo "<TR><TD COLSPAN='3' ALIGN='CENTER'><FONT COLOR='BLACK'>";
	echo "<TABLE BORDER='1' STYLE='border-collapse: collapse' BORDERCOLOR='#111111' WIDTH='100%'><TR><TD ALIGN='CENTER'>";
	echo "<FONT SIZE='5' FACE='VERDANA'><B>$surveyname</B></FONT>";
	echo "<FONT SIZE='4' FACE='VERDANA'><BR>$setfont$surveydesc</FONT></TD></TR></TABLE></TD></TR>\n";
	// SURVEY NAME AND DESCRIPTION TO GO HERE

	$degquery = "SELECT * FROM groups WHERE sid=$sid ORDER BY group_name";
	$degresult = mysql_query($degquery);
	// GROUP NAME
	while ($degrow=mysql_fetch_row($degresult))
		{
		$deqquery = "SELECT * FROM questions WHERE sid=$sid AND gid=$degrow[0] ORDER BY title";
		$deqresult = mysql_query($deqquery);
		echo "<TR><TD COLSPAN='3' ALIGN='CENTER' BGCOLOR='#EEEEEE' STYLE='border-top-width:1; border-left-width:1; border-right-width:1; border-bottom-width:1;border-top-style:double; border-left-style:double; border-right-style:double; border-bottom-style: double; border-left-color:#111111; border-right-color:#111111;border-top-color:#111111;border-bottom-color:#111111'>";
		echo "<FONT SIZE='3' FACE='VERDANA'><B>$degrow[2]</TD></TR>\n";
		$gid=$degrow[0];
		echo "<FORM ACTION='dataentry.php' NAME='addsurvey' >\n";
		//Alternate bgcolor for different groups
		if ($bgc == "#EEEEEE") {$bgc = "#DDDDDD";}
		else {$bgc = "#EEEEEE";}
		if (!$bgc) {$bgc="#EEEEEE";}
		
		while ($deqrow=mysql_fetch_row($deqresult))
			{
			$qid=$deqrow[0];
			$fieldname="$sid"."X"."$gid"."X"."$qid";
			echo "<TR BGCOLOR='$bgc'><TD VALIGN='TOP' WIDTH='1%'>$setfont$deqrow[4]</TD>";
			echo "<TD VALIGN='TOP' ALIGN='RIGHT' WIDTH='30%'><B>$setfont$deqrow[5]</B>";
			//DIFFERENT TYPES OF DATA FIELD HERE
			if ($deqrow[6])
				{
				$hh=str_replace("'", "\'", $deqrow[6]);
				echo "<TABLE WIDTH='100%' BORDER=1><TR><TD ALIGN='CENTER'><FONT SIZE='1'>$hh</TD></TR></TABLE>\n";
				//echo "<IMG SRC='help.gif' ALT='Help about this question' ALIGN='RIGHT' onClick=\"javascript:alert('Question $deqrow[0] Help: $hh')\">";
				}
			echo "</TD><TD>";
			switch($deqrow[3])
				{
				case "S":  //SHORT TEXT
					echo "$setfont<U>Put your answer here:</U><BR>";
					echo "<INPUT TYPE='TEXT' NAME='$fieldname' SIZE='60' $boxstyle>";				
					break;
				case "M":  //MULTIPLE OPTIONS (Quite tricky really!)
					echo "$setfont<U>Please tick <B>any</B> that apply</U><BR>";
					$meaquery = "SELECT * FROM answers WHERE qid=$deqrow[0] ORDER BY code";
					$mearesult = mysql_query($meaquery);
					while ($mearow = mysql_fetch_row($mearesult))
						{
						echo "$setfont<INPUT TYPE='checkbox' name='$fieldname$mearow[1]' VALUE='Y'";
						if ($mearow[3] == "Y") {echo " CHECKED";}
						echo ">$mearow[2]<BR>";
						}
					if ($deqrow[7] == "Y")
						{
						echo "Other: <INPUT TYPE='TEXT' $boxstyle SIZE='60' NAME='$fieldname";
						echo "other'>";
						}				
					echo "\n\n";
					break;
				case "P":  //MULTIPLE OPTIONS (with comments)
					$meaquery = "SELECT * FROM answers WHERE qid=$deqrow[0] ORDER BY code";
					$mearesult = mysql_query($meaquery);
					echo "<U>Please tick the appropriate response for each question and provide a comment</U><BR>";
					while ($mearow = mysql_fetch_row($mearesult))
						{
						echo "$setfont<INPUT TYPE='checkbox' name='$fieldname$mearow[1]' VALUE='Y'";
						if ($mearow[3] == "Y") {echo " CHECKED";}
						echo ">$mearow[2]";
						//This is the commments field:
						echo " <INPUT TYPE='TEXT' $boxstyle name='$fieldname$mearow[1]comment' SIZE='60'><BR>\n";
						}
					echo "\n\n";
					break;
				case "A":  //MULTI ARRAY
					$meaquery = "SELECT * FROM answers WHERE qid=$deqrow[0] ORDER BY code";
					$mearesult=mysql_query($meaquery);
					echo "<U>Please tick the appropriate response for each question</U><BR>";
					echo "<TABLE>";
					while ($mearow = mysql_fetch_row($mearesult))
						{
						echo "<TR><TD ALIGN='RIGHT'>$setfont$mearow[2]</tD><TD>";
						for ($i=1; $i<=5; $i++)
							{
							echo "$setfont<INPUT TYPE='CHECKBOX' NAME='$fieldname$mearow[1]' VALUE='$i'";
							if ($idrow[$i]== $i) {echo " CHECKED";}
							echo ">$i&nbsp;";
							}
						echo "</TD></TR>\n";
						}
					echo "</TABLE>\n\n";
					break;
				case "B":  //MULTI ARRAY
					$meaquery = "SELECT * FROM answers WHERE qid=$deqrow[0] ORDER BY code";
					$mearesult=mysql_query($meaquery);
					echo "<U>Please tick the appropriate response for each question</U><BR>";
					echo "<TABLE>";
					while ($mearow = mysql_fetch_row($mearesult))
						{
						echo "<TR><TD ALIGN='RIGHT'>$setfont$mearow[2]</tD><TD>";
						for ($i=1; $i<=10; $i++)
							{
							echo "$setfont<INPUT TYPE='CHECKBOX' NAME='$fieldname$mearow[1]' VALUE='$i'";
							if ($idrow[$i]== $i) {echo " CHECKED";}
							echo ">$i&nbsp;";
							}
						echo "</TD></TR>";
						}
					echo "</TABLE>\n\n";
					break;
				case "C":  //MULTI ARRAY
					$meaquery = "SELECT * FROM answers WHERE qid=$deqrow[0] ORDER BY code";
					$mearesult=mysql_query($meaquery);
					echo "<U>Please tick the appropriate response for each question</U><BR>";
					echo "<TABLE>";
					while ($mearow = mysql_fetch_row($mearesult))
						{
						echo "<TR><TD ALIGN='RIGHT'>$setfont$mearow[2]</tD><TD>";
						echo "$setfont<INPUT TYPE='CHECKBOX' NAME='$fieldname$mearow[1]' VALUE='Y'";
						if ($idrow[$i]== "Y") {echo " CHECKED";}
						echo ">Yes&nbsp;";
						echo "$setfont<INPUT TYPE='CHECKBOX' NAME='$fieldname$mearow[1]' VALUE='U'";
						if ($idrow[$i]== "U") {echo " CHECKED";}
						echo ">Uncertain&nbsp;";
						echo "$setfont<INPUT TYPE='CHECKBOX' NAME='$fieldname$mearow[1]' VALUE='N'";
						if ($idrow[$i]== "N") {echo " CHECKED";}
						echo ">No&nbsp;";
						echo "</TD></TR>";
						}
					echo "</TABLE>\n\n";
					break;
				case "L":  //DROPDOWN LIST
					echo "$setfont<U>Please tick <B>only one</B> of the following:</U><BR>";
					$deaquery = "SELECT * FROM answers WHERE qid=$deqrow[0] ORDER BY answer";
					$dearesult = mysql_query($deaquery);
					while ($dearow = mysql_fetch_row($dearesult))
						{
						echo "  <INPUT TYPE='CHECKBOX' NAME='$fieldname' VALUE='$dearow[1]'>$dearow[2]<BR>";
						}
					break;
				case "O":  //DROPDOWN LIST
					echo "$setfont<U>Please tick <B>only one</B> of the following:</U><BR>";
					$deaquery = "SELECT * FROM answers WHERE qid=$deqrow[0] ORDER BY answer";
					$dearesult = mysql_query($deaquery);
					while ($dearow = mysql_fetch_row($dearesult))
						{
						echo "  <INPUT TYPE='CHECKBOX' NAME='$fieldname' VALUE='$dearow[1]'>$dearow[2]<BR>";
						}
					echo "<U>Make a comment on your choice here:</U><BR>\n";
					echo "<TEXTAREA $boxstyle COLS='50' ROWS='8' NAME='$fieldname"."comment"."'></TEXTAREA>\n";
					break;
				case "T":  //LONG TEXT
					echo "<U>Please write your answer in the box below:</U><BR>";
					echo "<TEXTAREA $boxstyle COLS='50' ROWS='8' NAME='$fieldname'></TEXTAREA>\n\n";
					break;
				case "D":  //DATE
					echo "<U>Please enter your date:</U><BR>";
					echo "<INPUT TYPE='TEXT' $boxstyle NAME='$fieldname' SIZE='30' VALUE='&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;'>\n\n";
					break;
				case "5":  //5 Point Choice
					echo "$setfont<U>Please tick one response</U><BR>";
					echo "<INPUT TYPE='CHECKBOX' NAME='$fieldname' VALUE='1'>1 ";
					echo "<INPUT TYPE='CHECKBOX' NAME='$fieldname' VALUE='2'>2 ";
					echo "<INPUT TYPE='CHECKBOX' NAME='$fieldname' VALUE='3'>3 ";
					echo "<INPUT TYPE='CHECKBOX' NAME='$fieldname' VALUE='4'>4 ";
					echo "<INPUT TYPE='CHECKBOX' NAME='$fieldname' VALUE='5'>5 ";
					echo "\n\n";
					break;
				case "G":  //Gender
					echo "$setfont<U>Please tick <B>only one</B> of the following:</U><BR>";
					echo "<INPUT TYPE='CHECKBOX' NAME='$fieldname' VALUE='F'>Female<BR>";
					echo "<INPUT TYPE='CHECKBOX' NAME='$fieldname' VALUE='M'>Male<BR>";
					break;
				case "Y":  //YesNo
					echo "$setfont<U>Please tick <B>only one</B> of the following:</U><BR>";
					echo "<INPUT TYPE='CHECKBOX' NAME='$fieldname' VALUE='Y'>Yes<BR>";
					echo "<INPUT TYPE='CHECKBOX' NAME='$fieldname' VALUE='N'>No<BR>";
					break;
				}
			//echo " [$sid"."X"."$gid"."X"."$qid]";
			echo "</TD></TR>\n";
			echo "<TR><TD HEIGHT='3' COLSPAN='3'><HR NOSHADE SIZE='1' COLOR='#111111'></TD></TR>\n";		
			}		
		}
	echo "<TR><TD COLSPAN='3' ALIGN='CENTER'>";
	echo "<TABLE WIDTH='100%' BORDER='1' STYLE='border-collapse: collapse' BORDERCOLOR='#111111'><TR><TD ALIGN='CENTER'>";
	echo "$setfont<FONT FACE='VERDANA'><B>Submit your survey!</B><BR>";
	echo "Thank you for completing this survey. Please fax your completed survey to $surveyfaxnumber";
	if ($surveyexpirydate && $surveyexpirydate != "0000-00-00")
		{
		echo " by $surveyexpirydate";
		}
	echo ".</TD></TR></TABLE></TD></TR>\n";
	echo "</FORM></TABLE>\n";


?>