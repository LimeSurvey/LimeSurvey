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

if (!$ok)
	{
	echo "<TABLE WIDTH='350' ALIGN='CENTER'><TR><TD ALIGN='CENTER' BGCOLOR='PINK'><FONT COLOR='RED'>$setfont<B>";
	echo ":WARNING:<BR>READ THIS CAREFULLY BEFORE PROCEEDING</TD></TR>\n";
	echo "<TR><TD>$setfont";
	echo "You should only activate a survey when you are absolutely certain that your survey ";
	echo "setup is finished and will not need changing.<P>";
	echo "Once a survey is activated you can no longer:";
	echo "<UL><LI>Add or delete groups</LI>";
	echo "<LI>Add or remove answers to Multiple Answer questions</LI>";
	echo "<LI>Add or delete questions</LI></UL>";
	echo "However you can still:";
	echo "<UL><LI>Edit (change) your questions code, text or type</LI>";
	echo "<LI>Edit (change) your group names</LI>";
	echo "<LI>Add, Remove or Edit pre-defined question answers <I>(except for Multi-answer questions)</I></LI>";
	echo "<LI>Change survey name or description</LI></UL>";
	echo "Once data has been entered into this survey, if you want to add or remove groups ";
	echo "or questions, you will need to de-activate this survey, which will move all data ";
	echo "that has already been entered into a seperate archived table.<P>";
	echo "The point of all this being that you should not proceed to the next step unless ";
	echo "you are ABSOLUTELY SURE!";
	echo "</TD></TR>";
	echo "<TR><TD ALIGN='CENTER'>";
	echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='I`m Unsure' onclick=\"window.open('$scriptname?sid=$sid', '_top')\"><BR>\n";
	echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='Activate' onClick=\"window.open('$scriptname?action=activate&ok=Y&sid=$sid', '_top')\">";
	echo "</TD></TR></TABLE>\n";
	
	}
else
	{
	$createsurvey = "CREATE TABLE survey_$sid (\n";
	$createsurvey .= "  id INT(11) NOT NULL auto_increment,\n";
	$aquery = "SELECT * FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid=$sid ORDER BY group_name, title";
	$aresult = mysql_query($aquery);
	//echo "<BR><BR>$aquery<BR><BR>\n";
	while ($arow=mysql_fetch_row($aresult))
		{
		if ($arow[3] != "M" && $arow[3] != "A" && $arow[3] != "B" && $arow[3] !="C" &&$arow[3] !="P")
			{
			$createsurvey .= "  $arow[1]"."X"."$arow[2]"."X"."$arow[0]";
			switch($arow[3])
				{
						case "S":  //SHORT TEXT
							$createsurvey .= " VARCHAR(200)";
							break;
						case "L":  //DROPDOWN LIST
							$createsurvey .= " VARCHAR(5)";
							break;
						case "O": //DROPDOWN LIST WITH COMMENT
							$createsurvey .= " VARCHAR(5),\n $arow[1]"."X"."$arow[2]"."X"."$arow[0]"."comment TEXT";
							break;
						case "T":  //LONG TEXT
							$createsurvey .= " TEXT";
							break;
						case "D":  //DATE
							$createsurvey .= " DATE";
							break;
						case "5":  //5 Point Choice
							$createsurvey .= " VARCHAR(1)";
							break;
						case "G":  //Gender
							$createsurvey .= " VARCHAR(1)";
							break;
						case "Y":  //YesNo
							$createsurvey .= " VARCHAR(1)";
							break;
				}
			}
		elseif ($arow[3] == "M" || $arow[3] == "A" || $arow[3] == "B" || $arow[3] == "C" || $arow[3] == "P")
			{
			//MULTI ENTRY
			$abquery = "SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND sid=$sid AND questions.qid=$arow[0] ORDER BY code";
			$abresult=mysql_query($abquery);
			while ($abrow=mysql_fetch_row($abresult))
				{
				$createsurvey .= "  $arow[1]"."X"."$arow[2]"."X"."$arow[0]"."$abrow[1] VARCHAR(5),\n";
				if ($abrow[4]=="Y") {$alsoother="Y";}
				if ($arow[3] == "P")
					{
					$createsurvey .= "  $arow[1]"."X"."$arow[2]"."X"."$arow[0]"."$abrow[1]comment VARCHAR(100),\n";
					}
				}
			if ($alsoother=="Y" && ($arow[3]=="M" || $arow[3]=="P"))
				{
				$createsurvey .= " $arow[1]"."X"."$arow[2]"."X"."$arow[0]"."other VARCHAR(100),\n";
				if ($arow[3]=="P")
					{
					$createsurvey .= " $arow[1]"."X"."$arow[2]"."X"."$arow[0]"."othercomment VARCHAR(100),\n";
					}
				}
			
			}

		
		if ( substr($createsurvey, strlen($createsurvey)-2, 2) != ",\n") {$createsurvey .= ",\n";}
		}
	//$createsurvey = substr($createsurvey, 0, strlen($createsurvey)-2);
	$createsurvey .= "  INDEX(id)";
	$createsurvey .= ") TYPE=MyISAM;";
	//echo "<pre style='text-align: left'>$createsurvey</pre>\n"; //Debugging info
	
	$createtable=mysql_query($createsurvey) or die ("Could not activate this survey. <BR>".mysql_error() . "<BR><BR><a href='$scriptname?sid=$sid'>Back to Admin</A>");
	
	echo "<FONT COLOR='GREEN'>Results Table has been created!<BR><BR>";
	
	$acquery = "UPDATE surveys SET active='Y' WHERE sid=$sid";
	$acresult = mysql_query($acquery);
	
	echo "Survey is now active and data entry can proceed!<BR><BR>";
	echo "<a href='$scriptname?sid=$sid'>Return to administration</a>\n";
	}	
?>