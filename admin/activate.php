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

if (!$_GET['ok'])
	{
	//CHECK TO MAKE SURE ALL QUESTION TYPES THAT REQUIRE ANSWERS HAVE ACTUALLY GOT ANSWERS
	//THESE QUESTION TYPES ARE:
	//	# "L" -> LIST
	//  # "O" -> LIST WITH COMMENT
	//  # "M" -> MULTIPLE OPTIONS
	//	# "P" -> MULTIPLE OPTIONS WITH COMMENTS
	//	# "A", "B", "C" -> Various Array Types
	$chkquery = "SELECT qid, question FROM questions WHERE sid={$_GET['sid']} AND type IN ('L', 'O', 'M', 'P', 'A', 'B', 'C')";
	$chkresult = mysql_query($chkquery) or die ("Couldn't get list of questions<br />$chkquery<br />".mysql_error());
	while ($chkrow = mysql_fetch_array($chkresult))
		{
		$chaquery = "SELECT * FROM answers WHERE qid = {$chkrow['qid']}";
		$charesult=mysql_query($chaquery);
		$chacount=mysql_num_rows($charesult);
		if (!$chacount > 0) 
			{
			$failedcheck[]=array($chkrow['qid'], $chkrow['question']);
			}
		}
	if ($failedcheck)
		{
		echo "<table width='350' align='center'>\n";
		echo "\t<tr>\n";
		echo "\t\t<td align='center' bgcolor='pink'>\n";
		echo "\t\t\t<font color='red'>$setfont<b>:ERROR:</b><br />\n";
		echo "\t\t\tSurvey does not pass consistency check</font></font>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td>\n";
		echo "\t\t\t$setfont<b>The following problems have been found:</b><br />\n";
		echo "\t\t\t<ul>\n";
		foreach ($failedcheck as $fc)
			{
			echo "\t\t\t\t<li>Question qid-{$fc[0]} (\"{$fc[1]}\") is a multiple answer style question "
				."but does not have any answers.</li>\n";
			}
		echo "\t\t\t</ul>\n";
		echo "\t\t\tThe survey cannot be activated until these problems have been resolved.\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td align='center'>\n";
		echo "\t\t\t<input type='submit' $btstyle value='Return to Admin' onClick=\"window.open('$scriptname?sid={$_GET['sid']}', '_top')\">\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "</table>\n";
		exit;		
		}
	
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
	echo "\t\t\t<input type='submit' $btstyle value='I`m Unsure' onclick=\"window.open('$scriptname?sid={$_GET['sid']}', '_top')\"><br />\n";
	echo "\t\t\t<input type='submit' $btstyle value='Activate' onClick=\"window.open('$scriptname?action=activate&ok=Y&sid={$_GET['sid']}', '_top')\">\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	echo "</body>\n</html>";
	
	}
else
	{
	$createsurvey = "CREATE TABLE survey_{$_GET['sid']} (\n";
	$createsurvey .= "  id INT(11) NOT NULL auto_increment,\n";
	$pquery = "SELECT private FROM surveys WHERE sid={$_GET['sid']}";
	$presult=mysql_query($pquery);
	while($prow=mysql_fetch_array($presult))
		{
		if ($prow['private'] == "N") {$createsurvey .= "  token VARCHAR(10),\n";}
		$surveynotprivate="TRUE";
		}
	$aquery = "SELECT * FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid={$_GET['sid']} ORDER BY group_name, title";
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
			$abquery = "SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND sid={$_GET['sid']} AND questions.qid={$arow['qid']} ORDER BY code";
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
	$createsurvey .= "  INDEX(id)\n";
	$createsurvey .= ") TYPE=MyISAM;";
	//echo "<pre style='text-align: left'>$createsurvey</pre>\n"; //Debugging info
	
	$createtable=mysql_query($createsurvey) or die 
		(
		"<center><h3>Could not activate this survey.</h3></center><br />\n" .
		"<center><a href='$scriptname?sid={$_GET['sid']}'>Back to Admin</a></center>\n" .
		"The database reported:<br />\n<font color='red'>" . mysql_error() . "</font>\n" .
		"<pre>$createsurvey</pre>\n" .
		"</body>\n</html>"
		);
	
	echo "<center><font color='green'>Results Table has been created!<br /><br />\n";
	
	$acquery = "UPDATE surveys SET active='Y' WHERE sid={$_GET['sid']}";
	$acresult = mysql_query($acquery);
	
	if ($surveynotprivate) //This survey is tracked, and therefore a tokens table MUST exist
		{
		echo "This survey is registered as NOT PRIVATE and therefore requires a tokens table\n";
		echo "to be created.<br /><br />\n";
		echo "<input type='submit' value='Create Tokens Table' $btstyle onClick=\"window.open('tokens.php?sid={$_GET['sid']}&createtable=Y', '_top')\">\n";
		
		}
	else
		{
		echo "Survey is now active and data entry can proceed!<br /><br />\n";
		echo "<input type='submit' value='Return to Administration' $btstyle onClick=\"window.open('$scriptname?sid={$_GET['sid']}', '_top')\">\n";
		}
	echo "</body>\n</html>";
	}	
?>