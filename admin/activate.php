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
	echo "<table width='350' align='center'>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center' bgcolor='pink'>\n";
	echo "\t\t\t<font color='red'>$setfont<b>:WARNING:</b><br />\n";
	echo "\t\t\tREAD THIS CAREFULLY BEFORE PROCEEDING\n";
	echo "\t\t\t</font></font>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td>$setfont\n";
	echo "You should only activate a survey when you are absolutely certain that your survey ";
	echo "setup is finished and will not need changing.\n";
	echo "<p>Once a survey is activated you can no longer:\n";
	echo "<ul>\n";
	echo "<li>Add or delete groups</li>\n";
	echo "<li>Add or remove answers to Multiple Answer questions</li>\n";
	echo "<li>Add or delete questions</li>\n";
	echo "</ul>\n";
	echo "However you can still:\n";
	echo "<ul>\n";
	echo "<li>Edit (change) your questions code, text or type</li>\n";
	echo "<li>Edit (change) your group names</li>\n";
	echo "<li>Add, Remove or Edit pre-defined question answers <i>(except for Multi-answer questions)</i></li>\n";
	echo "<li>Change survey name or description</li>\n";
	echo "</ul>\n";
	echo "Once data has been entered into this survey, if you want to add or remove groups ";
	echo "or questions, you will need to de-activate this survey, which will move all data ";
	echo "that has already been entered into a seperate archived table.\n";
	echo "<p>The point of all this being that you should not proceed to the next step unless ";
	echo "you are ABSOLUTELY SURE!\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>\n";
	echo "\t\t\t<input type='submit' $btstyle value='I`m Unsure' onclick=\"window.open('$scriptname?sid=$sid', '_top')\"><br />\n";
	echo "\t\t\t<input type='submit' $btstyle value='Activate' onClick=\"window.open('$scriptname?action=activate&ok=Y&sid=$sid', '_top')\">\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	echo "</body>\n</html>";
	
	}
else
	{
	$createsurvey = "CREATE TABLE survey_$sid (\n";
	$createsurvey .= "  id INT(11) NOT NULL auto_increment,\n";
	$aquery = "SELECT * FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid=$sid ORDER BY group_name, title";
	$aresult = mysql_query($aquery);
	//echo "<br /><br />$aquery<br /><br />\n";
	while ($arow=mysql_fetch_array($aresult))
		{
		if ($arow['type'] != "M" && $arow['type'] != "A" && $arow['type'] != "B" && $arow['type'] !="C" &&$arow['type'] !="P")
			{
			$createsurvey .= "  {$arow['sid']}X{$arow['gid']}X{$arow['qid']}";
			switch($arow['type'])
				{
						case "S":  //SHORT TEXT
							$createsurvey .= " VARCHAR(200)";
							break;
						case "L":  //DROPDOWN LIST
							$createsurvey .= " VARCHAR(5)";
							break;
						case "O": //DROPDOWN LIST WITH COMMENT
							$createsurvey .= " VARCHAR(5),\n {$arow['sid']}X{$arow['gid']}X{$arow['qid']}comment TEXT";
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
		elseif ($arow['type'] == "M" || $arow['type'] == "A" || $arow['type'] == "B" || $arow['type'] == "C" || $arow['type'] == "P")
			{
			//MULTI ENTRY
			$abquery = "SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND sid=$sid AND questions.qid={$arow['qid']} ORDER BY code";
			$abresult=mysql_query($abquery);
			while ($abrow=mysql_fetch_array($abresult))
				{
				$createsurvey .= "  {$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']} VARCHAR(5),\n";
				if ($abrow['other']=="Y") {$alsoother="Y";}
				if ($arow['type'] == "P")
					{
					$createsurvey .= "  {$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}comment VARCHAR(100),\n";
					}
				}
			if ($alsoother=="Y" && ($arow['type']=="M" || $arow['type']=="P"))
				{
				$createsurvey .= " {$arow['sid']}X{$arow['gid']}X{$arow['qid']}other VARCHAR(100),\n";
				if ($arow['type']=="P")
					{
					$createsurvey .= " {$arow['sid']}X{$arow['gid']}X{$arow['qid']}othercomment VARCHAR(100),\n";
					}
				}
			
			}

		
		if ( substr($createsurvey, strlen($createsurvey)-2, 2) != ",\n") {$createsurvey .= ",\n";}
		}
	//$createsurvey = substr($createsurvey, 0, strlen($createsurvey)-2);
	$createsurvey .= "  INDEX(id)";
	$createsurvey .= ") TYPE=MyISAM;";
	//echo "<pre style='text-align: left'>$createsurvey</pre>\n"; //Debugging info
	
	$createtable=mysql_query($createsurvey) or die 
		(
		"<center>Could not activate this survey.</center>\n" .
		"<pre>$createsurvey</pre>\n" .
		mysql_error() . "<br /><br />\n" .
		"<center><a href='$scriptname?sid=$sid'>Back to Admin</a></center>\n" .
		"</body>\n<html>"
		);
	
	echo "<font color='green'>Results Table has been created!<br /><br />\n";
	
	$acquery = "UPDATE surveys SET active='Y' WHERE sid=$sid";
	$acresult = mysql_query($acquery);
	
	echo "Survey is now active and data entry can proceed!<br /><br />\n";
	echo "<a href='$scriptname?sid=$sid'>Return to administration</a>\n";
	echo "</body>\n</html>";
	}	
?>