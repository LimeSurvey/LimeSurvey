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

if ($sid)
	{
	$sumquery1 = "SELECT * FROM surveys WHERE sid=$sid";
	$sumresult1 = mysql_query($sumquery1);
	$surveysummary = "<table width='100%' align='center' bgcolor='silver' border='0'>\n";
	while ($s1row = mysql_fetch_array($sumresult1))
		{
		$surveysummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Title:</b></font></td>\n\t<td>$setfont<b><font color='#000080'>{$s1row['short_title']} (ID {$s1row['sid']})</font></b></td></tr>\n";
		$surveysummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Description:</b></font></td>\n\t<td bgcolor='#DDDDDD'>$setfont {$s1row['description']}</font></td></tr>\n";
		$surveysummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Welcome:</b></font></td>\n\t<td bgcolor='#DDDDDD'>$setfont {$s1row['welcome']}</font></td></tr>\n";
		$surveysummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Admin:</b></font></td>\n\t<td bgcolor='#DDDDDD'>$setfont {$s1row['admin']} ({$s1row['adminemail']})</font></td></tr>\n";
		if ($s1row['expires'] != "0000-00-00") {$surveysummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Expires:</b></font></td>\n\t<td bgcolor='#DDDDDD'>$setfont {$s1row['expires']}</font></td></tr>\n";}
		$activated = $s1row['active'];
		}
	
	$sumquery2 = "SELECT * FROM groups WHERE sid=$sid";
	$sumresult2 = mysql_query($sumquery2);
	$sumcount2 = mysql_num_rows($sumresult2);
	$surveysummary .= "\t<tr><td align='right'>$setfont<b>Groups:</b></font></td>\n\t<td bgcolor='#DDDDDD'>$setfont";
	if ($groupselect)
		{
		$surveysummary .= "\t\t<select $slstyle name='groupselect' onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
		$surveysummary .= $groupselect;
		$surveysummary .= "</select>\n";
		}

	$sumquery3 = "SELECT * FROM questions WHERE sid=$sid";
	$sumresult3 = mysql_query($sumquery3);
	$sumcount3 = mysql_num_rows($sumresult3);

	$surveysummary .= "\t<font size='1'>($sumcount2 groups, $sumcount3 questions)</font></td></tr>\n";

	$surveysummary .= "\t<tr><td align='right'>$setfont<b>Activation</b></font></td>\n\t<td valign='top' bgcolor='#DDDDDD'>$setfont";
	if ($activated == "N" && $sumcount3 > 0)
		{
		$surveysummary .= "\t\t<input $btstyle type='submit' value='Activate' onClick=\"window.open('$scriptname?action=activate&sid=$sid', '_top')\">\n";
		}
	elseif ($activated == "Y")
		{
		$surveysummary .= "\t\t<input $btstyle type='submit' value='De-activate' onClick=\"window.open('$scriptname?action=deactivate&sid=$sid', '_top')\">\n";
		//$surveysummary .= "&nbsp;&nbsp;&nbsp;<FONT SIZE='1'>Survey Table is 'survey_$sid'<BR>";
		$surveysummary .= "\t\t<input $btstyle type='submit' value='Tokens' onClick=\"window.open('tokens.php?sid=$sid', '_top')\">\n";
		}
	else
		{
		$surveysummary .= "&nbsp;&nbsp;&nbsp;<font size='1'>Survey cannot yet be activated";
		}
	$surveysummary .= "</td></tr>\n";
	
	//OPTIONS
	$surveysummary .= "\t<tr><td colspan='2' align='right'>\n";
	$surveysummary .= "\t\t<input type='submit' $btstyle value='Export' title='Export Survey Structure..' onClick=\"window.open('dumpsurvey.php?sid=$sid', '_top')\">\n";
	if ($activated == "N") 
		{
		$surveysummary .= "\t\t<input type='submit' $btstyle value='Test DataEntry' onClick=\"window.open('dataentry.php?sid=$sid', '_blank')\">\n";
		$surveysummary .= "\t\t<input type='submit' $btstyle value='Test Survey' onClick=\"window.open('../index.php?sid=$sid', '_blank')\">\n";
		}
	else 
		{
		$surveysummary .= "\t\t<input type='submit' $btstyle value='Browse' onClick=\"window.open('browse.php?sid=$sid', '_top')\">\n";
		$surveysummary .= "\t\t<input type='submit' $btstyle value='DataEntry' onClick=\"window.open('dataentry.php?sid=$sid', '_blank')\">\n";
		$surveysummary .= "\t\t<input type='submit' $btstyle value='Do Survey' onClick=\"window.open('../index.php?sid=$sid', '_blank')\">\n";
		}
	$surveysummary .= "\t\t<input type='submit' $btstyle value='Edit Survey' onClick=\"window.open('$scriptname?action=editsurvey&sid=$sid', '_top')\">\n";
	if ($activated == "N") {$surveysummary .= "\t\t<input type='submit' $btstyle value='Add Group' onClick=\"window.open('$scriptname?action=addgroup&sid=$sid', '_top')\">\n";}
	if ($sumcount3 == 0 && $sumcount2 == 0) {$surveysummary .= "\t\t<input type='submit' $btstyle value='Delete Survey' onClick=\"window.open('$scriptname?action=delsurvey&sid=$sid', '_top')\">\n";}
	
	$surveysummary .= "\t</td></tr>\n";
	
	$surveysummary .= "</table>\n";
	}

if ($gid)
	{
	$grpquery =" SELECT * FROM groups WHERE gid=$gid ORDER BY group_name";
	$grpresult = mysql_query($grpquery);
	$groupsummary = "<table width='100%' align='center' bgcolor='#DDDDDD' border='0'>\n";
	while ($grow = mysql_fetch_array($grpresult))
		{
		$groupsummary .= "\t<tr><td width='20%' align='right'>$setfont<b>Group Title:</b></font></td>\n\t<td>$setfont{$grow['group_name']} ({$grow['gid']})</font></td></tr>\n";
		if ($grow['description']) {$groupsummary .= "\t<tr><td valign='top' align='right'>$setfont<b>Description:</b></font></td>\n\t<td>$setfont{$grow['description']}</font></td></tr>\n";}
		}
	$groupsummary .="\t<tr><td align='right'>$setfont<b>Questions:</b></font></td>\n";
	$groupsummary .="\t<td><select $slstyle name='qid' onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
	$groupsummary .= getquestions();
	$groupsummary .= "\n\t\t</select></td></tr>\n";
	$groupsummary .= "\n\t<tr><td colspan='2' align='right'>\n";
	$groupsummary .= "\t\t<input type='submit' $btstyle value='Edit Group' onClick=\"window.open('$scriptname?action=editgroup&sid=$sid&gid=$gid', '_top')\">\n";
	if ($activated == "N") {$groupsummary .= "\t\t<input type='submit' $btstyle value='Add Question' onClick=\"window.open('$scriptname?action=addquestion&sid=$sid&gid=$gid', '_top')\">\n";}
	$qquery = "SELECT * FROM questions WHERE sid=$sid AND gid=$gid ORDER BY title";
	$qresult = mysql_query($qquery);
	$qcount = mysql_num_rows($qresult);
	if ($qcount == 0) {$groupsummary .= "\t\t<input type='submit' $btstyle value='Delete Group' onClick=\"window.open('$scriptname?action=delgroup&sid=$sid&gid=$gid', '_top')\">";}
	$groupsummary .= "\t</td></tr>\n</table>\n";
	}

if ($qid)
	{
	$qrquery = "SELECT * FROM questions WHERE gid=$gid AND sid=$sid AND qid=$qid";
	$qrresult = mysql_query($qrquery);
	$questionsummary = "<table width='100%' align='center' bgcolor='#EEEEEE' border='0'>\n";
	while ($qrrow = mysql_fetch_array($qrresult))
		{
		$questionsummary .= "\t<tr><td width='20%' align='RIGHT'>$setfont<b>Question Title:</b></font></td>\n\t<td>$setfont{$qrrow['title']}</td></tr>\n";
		$questionsummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Question:</b></font></td>\n\t<td>$setfont{$qrrow['question']}</td></tr>\n";
		$questionsummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Help:</b></font></td>\n\t<td>$setfont{$qrrow['help']}</td></tr>\n";
		$questionsummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Type:</b></font></TD>\n\t<td>$setfont{$qrrow['type']}</td></tr>\n";
		if ($qrrow['type']== "O" || $qrrow['type'] == "L" || $qrrow['type'] == "M" || $qrrow['type'] == "A" || $grrow[3] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "P")
			{
			$questionsummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Answers:</b></font></td>\n";
			$questionsummary .= "\t<td>\n\t\t<select $slstyle name='answer' onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
			$questionsummary .= getanswers();
			$questionsummary .= "\n\t\t</select>\n\t</td></tr>\n";
			}
		$questionsummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Other?</b></font></td>\n\t<td>$setfont$qrrow[7]</td></tr>\n";
		$questionsummary .= "\t<tr><td colspan='2' align='right'>\n";
		$questionsummary .= "\t\t<input type='submit' $btstyle value='Edit Question' onClick=\"window.open('$scriptname?action=editquestion&sid=$sid&gid=$gid&qid=$qid', '_top')\">\n";
		if ($qrrow['type'] == "O" || $qrrow['type'] == "L" || $qrrow['type'] == "M" || $qrrow['type']=="A" || $qrrow['type'] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "P") 
			{
			if (($activated == "Y" && $qrrow['type'] == "L") || ($activated == "N"))
				{
				$questionsummary .= "\t\t<input type='submit' $btstyle value='Add Answer' onClick=\"window.open('$scriptname?action=addanswer&sid=$sid&gid=$gid&qid=$qid', '_top')\">\n";
				}
			$qrq = "SELECT * FROM answers WHERE qid=$qid";
			$qrr = mysql_query($qrq);
			$qct = mysql_num_rows($qrr);
			if ($qct == 0)
				{
				$questionsummary .= "\t\t<input type='submit' $btstyle value='Delete Question' onClick=\"window.open('$scriptname?action=delquestion&sid=$sid&gid=$gid&qid=$qid', '_top')\">";
				}
			}
		else {$questionsummary .= "\t\t<input type='submit' $btstyle value='Delete Question' onClick=\"window.open('$scriptname?action=delquestion&sid=$sid&gid=$gid&qid=$qid', '_top')\">";}
		if ($activated == "N") {$questionsummary .= "\t\t<input type='submit' $btstyle value='Copy Question' onClick=\"window.open('$scriptname?action=copyquestion&sid=$sid&gid=$gid&qid=$qid', '_top')\">\n";}
		$questionsummary .= "\t</td></tr>\n";
		}
	$questionsummary .= "</table>\n";
	}

if ($code)
	{
	$cdquery = "SELECT * FROM answers WHERE qid=$qid AND code='$code'";
	$cdresult = mysql_query($cdquery);
	$answersummary = "<table width='100%' align='center' border='0'>\n";
	while ($cdrow = mysql_fetch_array($cdresult))
		{
		$answersummary .= "\t<tr><td width='20%' align='right'>$setfont<b>Code:</b></font></td>\n\t<td>$setfont{$cdrow['code']}</td></tr>\n";
		$answersummary .= "\t<tr><td align='right'>$setfont<b>Answer:</b></font></td>\n\t<td>$setfont{$cdrow['answer']}</td></tr>\n";
		$answersummary .= "\t<tr><td align='right'>$setfont<b>Default?</b></font></td>\n\t<td>$setfont{$cdrow['default']}</td></tr>\n";
		}
	$answersummary .= "\t<tr><td align='right' colspan='2'>\n";
	$answersummary .= "\t\t<input type='submit' $btstyle value='Delete Answer' onClick=\"window.open('$scriptname?action=delanswer&sid=$sid&gid=$gid&qid=$qid&code=$code', '_top')\">\n";
	$answersummary .= "\t\t<input type='submit' $btstyle value='Edit Answer' onClick=\"window.open('$scriptname?action=editanswer&sid=$sid&gid=$gid&qid=$qid&code=$code', '_top')\">\n";
	$answersummary .= "\t</td></tr>\n";
	$answersummary .= "</table>\n";
	}

if ($action == "setupsecurity")
	{
	$action = "setup";
	include("usercontrol.php");
	}

if ($action == "turnoffsecurity")
	{
	$action = "deleteall";
	include("usercontrol.php");
	}
	
if ($action == "adduser" || $action=="deluser" || $action == "moduser")
	{
	include("usercontrol.php");
	}

if ($action == "modifyuser")
	{
	$usersummary = "<table width='100%' border='0'>\n\t<tr><td colspan='3' bgcolor='black' align='center'>\n";
	$usersummary .= "\t\t<b>$setfont<font color='white'>Modify User</td></tr>\n";
	$muq = "SELECT * FROM users WHERE user='$user' LIMIT 1";
	$mur = mysql_query($muq);
	$usersummary .= "\t<tr><form action='$scriptname' method='post'>";
	while ($mrw = mysql_fetch_array($mur))
		{
		$usersummary .= "\t<td>$setfont<b>$mrw[0]</b></font></td>\n";
		$usersummary .= "\t\t<input type='hidden' name='user' value='{$mrw['user']}'>\n";
		$usersummary .= "\t<td>\n\t\t<input type='text' name='pass' value='{$mrw['password']}'></td>\n";
		$usersummary .= "\t<td>\n\t\t<input type='text' size='2' name='level' value='{$mrw['security']}'></td>\n";
		}
	$usersummary .= "\t</tr>\n\t<tr><td colspan='3' align='center'>\n";
	$usersummary .= "\t\t<input type='submit' $btstyle value='Update'></td></tr>\n";
	$usersummary .= "<input type='hidden' name='action' value='moduser'>\n";
	$usersummary .= "</form></table>\n";
	}

if ($action == "editusers")
	{
	if (!file_exists("$homedir/.htaccess"))
		{
		$usersummary = "<table width='100%' border='0'>\n\t<tr><td bgcolor='black' align='center'>\n";
		$usersummary .= "\t\t<b>$setfont<font color='WHITE'>Security Control</font></font></b></td></tr>\n";
		$usersummary .= "\t<tr><td>$setfont<font color='RED'><b>Warning:</font></font></b><br />\n";
		$usersummary .= "\t\tYou have not yet initialised security settings for your survey system \n";
		$usersummary .= "\t\tand subsequently there are no restrictions on access.\n";
		$usersummary .= "\t\t<p>If you click on the 'initialise security' button below, standard APACHE \n";
		$usersummary .= "\t\tsecurity settings will be added to the administration directory of this \n";
		$usersummary .= "\t\tscript. You will then need to use the default access username and password \n";
		$usersummary .= "\t\tto access the administration and dataentry scripts.</p>\n";
		$usersummary .= "\t\t<p>USERNAME: $defaultuser<BR>PASSWORD: $defaultpass</p>\n";
		$usersummary .= "\t\t<p>It is highly recommended that once your security system has been initialised \n";
		$usersummary .= "\t\tyou change this default password.</p></td></tr>\n";
		$usersummary .= "\t<tr><td align='center'><input type='submit' $btstyle value='Initialise Security' onClick=\"window.open('$scriptname?action=setupsecurity', '_top')\"></td></tr>\n";
		$usersummary .= "</table>\n";
		}
	else
		{
		$usersummary = "<table width='100%' border='0'>\n\t<tr><td colspan='4' bgcolor='BLACK' align='center'>\n";
		$usersummary .= "\t\t<b>$setfont<font color='WHITE'>List of users</font><font></b></td></tr>\n";
		$usersummary .= "\t<tr bgcolor='#444444'><td>$setfont<font color='WHITE'><b>User</td>\n\t<td>$setfont<font color='WHITE'><b>Password</b></font></font></td>";
		$usersummary .= "\t<td>$setfont<font color='WHITE'><b>Security</b></font></font></td>\n\t<td>$setfont<font color='WHITE'><b>Actions</b></font></font></td></tr>\n";
		$userlist = getuserlist();
		$ui = count($userlist);
		if ($ui < 1) {$usersummary .= "<center>WARNING: No users exist in your table. We recommend you 'turn off' security. You can then 'turn it on' again.</center>";}
		else
			{
			foreach ($userlist as $usr)
				{
				$usersummary .= "\t<tr><td>$setfont<b>$usr[0]</b></font></td>\n\t<td>$setfont$usr[1]</font></td>\n\t<td>$setfont$usr[2]</td>\n";
				$usersummary .= "\t<td><input type='submit' $btstyle value='Edit' onClick=\"window.open('$scriptname?action=modifyuser&user=$usr[0]', '_top')\">\n";
				if ($ui > 1 ) {$usersummary .= "\t\t<input type='submit' $btstyle value='Del' onClick=\"window.open('$scriptname?action=deluser&user=$usr[0]', '_top')\">\n";}
				$usersummary .= "\t</td></tr>\n";
				$ui++;
				}
			}
		$usersummary .= "\t<tr bgcolor='#EEEFFF'><form action='$scriptname' method='post'>\n\t<td>\n";
		$usersummary .= "\t\t<input type='text' $slstyle name='user'></td>\n";
		$usersummary .= "\t<td><input type='text' $slstyle name='pass'></td>\n";
		$usersummary .= "\t<td><input type='text' $slstyle name='level' size='2'></td>\n";
		$usersummary .= "\t<td><input type='submit' $btstyle value='Add New User'></td></tr>\n";
		$usersummary .= "\t\t<input type='hidden' name='action' value='adduser'></form>\n";
		$usersummary .= "\t<tr><td colspan='3'></td>\n\t<td><input type='submit' $btstyle value='Turn Off Security' ";
		$usersummary .= "onClick=\"window.open('$scriptname?action=turnoffsecurity', '_top')\"></td></tr>\n";		
		$usersummary .= "</table>\n";
		}
	}
if ($action == "addquestion")
	{
	$newquestion = "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='BLACK' align='center'>";
	$newquestion .= "<b>$setfont<font color='WHITE'>Create New Question for Survey ID($sid), Group ID($gid) </b></td></tr>\n";
	$newquestion .= "\t<tr><form action='$scriptname' name='addnewquestion' method='post'>\n";
	$newquestion .= "\t<td align='right'>$setfont<b>Question Code:</b></font></td>\n";
	$newquestion .= "\t<td><input type='text' size='20' name='title'></td></tr>\n";
	$newquestion .= "\t<tr><td align='right'>$setfont<b>Question:</b></font></td>\n";
	$newquestion .= "\t<td><textarea cols='35' rows='3' name='question'></textarea></td></tr>\n";
	$newquestion .= "\t<tr><td align='right'>$setfont<b>Help:</b></font></td>\n";
	$newquestion .= "\t<td><textarea cols='35' rows='3' name='help'></textarea></td></tr>\n";
	$newquestion .= "\t<tr><td align='right'>$setfont<b>Question Type:</b></font></td>\n";
	$newquestion .= "\t<td>\n\t\t<select $slstyle name='type'>\n";
	$newquestion .= "$qtypeselect";
	$newquestion .= "\n\t\t</select></td></tr>\n";
	$newquestion .= "\t<tr><td align='right'>$setfont<b>Other?</b></font></td>\n";
	$newquestion .= "\t<td><input type='text' size='1' value='N' name='other'></td></tr>\n";
	$newquestion .= "\t<tr><td colspan='2' align='center'>\n\t\t<input type='submit' $btstyle value='Add Question'></td></tr>\n";
	$newquestion .= "\t<input type='hidden' name='action' value='insertnewquestion'>\n";
	$newquestion .= "\t<input type='hidden' name='sid' value='$sid'>\n";
	$newquestion .= "\t<input type='hidden' name='gid' value='$gid'>\n";
	$newquestion .= "</form></table>\n";
	}

if ($action == "copyquestion")
	{
	$eqquery = "SELECT * FROM questions WHERE sid=$sid AND gid=$gid AND qid=$qid";
	$eqresult = mysql_query($eqquery);
	while ($eqrow = mysql_fetch_array($eqresult))
		{
		$editquestion = "<table width='100%' border='0'>";
		$editquestion .= "\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
		$editquestion .= "\t\t<b>$setfont<font color='white'>Copy Question $qid (Code {$eqrow['title']})</b><br />Note: You MUST enter a new Question Code!</font></font>\n\t</td></tr>\n";
		$editquestion .= "\t<tr><form action='$scriptname' name='editquestion' >\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Question Code:</b></font></td>";
		$editquestion .= "\t\t<td><input type='text' size='20' name='title' value=''></td>\n\t</tr>\n";
		$editquestion .= "\t<tr>\n\t\t<td align='right' valign='top'>$setfont<b>Question:</b></font></td>\n";
		$editquestion .= "\t\t<td><textarea cols='35' rows='4' name='question'>{$eqrow['question']}</textarea></td></tr>\n";
		$editquestion .= "\t<tr>\n\t\t<td align='right' valign='top'>$setfont<b>Help:</b></font></td>\n";
		$editquestion .= "\t<td><textarea cols='35' rows='4' name='help'>{$eqrow['help']}</textarea></td></tr>\n";
		$editquestion .= "\t<tr>\n\t\t<td align='right'>$setfont<b>Type:</b></font></td>\n";
		$editquestion .= "\t\t<td><select $slstyle name='type'>\n";
		$editquestion .= getqtypelist($eqrow['type']);
		$editquestion .= "\n\t\t</select></td></tr>\n";
		//$editquestion .= "<TD><INPUT TYPE='TEXT' SIZE='1' NAME='type' VALUE='{$eqrow['type']}'></TD></TR>\n";
		$editquestion .= "\t<tr><td align='right'>$setfont<b>Group?</b></font></td>\n";
		$editquestion .= "\t\t<td><select $slstyle name='gid'>\n";
		$editquestion .= getgrouplist2($eqrow['gid']);
		$editquestion .= "\n\t\t</select></td></tr>\n";
		$editquestion .= "\t<tr><td align='right'>$setfont<b>Other?</b></font></td>\n";
		$editquestion .= "\t\t<td><input type='text' size='1' value='{$eqrow['other']}' name='other'></td></tr>\n";
		$editquestion .= "\t<tr><td align='right'>$setfont<b>Copy answers:</b></font></td>\n";
		$editquestion .= "\t\t<td>$setfont<input type='checkbox' checked name='copyanswers' value='Y'></font></td></tr>\n";
		$editquestion .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Update Question'></td>\n";
		$editquestion .= "\t<input type='hidden' name='action' value='copynewquestion'>\n";
		$editquestion .= "\t<input type='hidden' name='sid' value='$sid'>\n";
		$editquestion .= "\t<input type='hidden' name='oldqid' value='$qid'>\n";
		$editquestion .= "\t</form></tr>\n";
		$editquestion .= "</table>\n";
		}

	}

if ($action == "addanswer")
	{
	$newanswer = "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
	$newanswer .= "\t\t<b>$setfont<font color='white'>Create New Answer for Survey ID($sid), Group ID($gid), Question ID($qid)</b></font></font></td></tr>\n";
	$newanswer .= "\t<tr><form action='$scriptname' name='addnewanswer' method='post'>\n";
	$newanswer .= "\t\t<td align='right'>$setfont<b>Answer Code:</b></font></td>\n";
	$newanswer .= "\t\t<td><input type='text' size='5' name='code'></td></tr>\n";
	$newanswer .= "\t<tr><td align='right'>$setfont<b>Answer:</b></font></td>\n";
	$newanswer .= "\t\t<td><input type='text' name='answer'></td></tr>\n";
	$newanswer .= "\t<tr><td align='right'>$setfont<b>Default?</b></font></td>\n";
	$newanswer .= "\t\t<td><input type='text' size='1' value='N' name='default'></td></tr>\n";
	$newanswer .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Add Answer'></td></tr>\n";
	$newanswer .= "\t<input type='hidden' name='action' value='insertnewanswer'>\n";
	$newanswer .= "\t<input type='hidden' name='qid' value='$qid'>\n";
	$newanswer .= "\t<input type='hidden' name='sid' value='$sid'>\n";
	$newanswer .= "\t<input type='hidden' name='gid' value='$gid'>\n";
	$newanswer .= "</form></table>\n";
	} 

if ($action == "addgroup")
	{
	$newgroup = "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
	$newgroup .= "\t\t<b>$setfont<font color='white'>Create New Group for Survey ID($sid)</font></font></b></td></tr>\n";
	$newgroup .= "\t<tr><form action='$scriptname' name='addnewgroup' method='post'>\n";
	$newgroup .= "\t\t<td align='right'>$setfont<b>Group Name:</b></font></td>\n";
	$newgroup .= "\t\t<td><input type='text' size='40' name='group_name'></td></tr>\n";
	$newgroup .= "\t<tr><td align='right'>$setfont<b>Group Description:</b>(optional)</font></td>\n";
	$newgroup .= "\t\t<td><textarea cols='40' rows='4' name='description'></textarea></td></tr>\n";
	$newgroup .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Create New Group'></td>\n";
	$newgroup .= "\t<input type='hidden' name='action' value='insertnewgroup'>\n";
	$newgroup .= "\t<input type='hidden' name='sid' value='$sid'>\n";
	$newgroup .= "\t</form></tr>\n";
	$newgroup .= "</table>\n";
	}

if ($action == "editgroup")
	{
	$egquery = "SELECT * FROM groups WHERE sid=$sid AND gid=$gid";
	$egresult = mysql_query($egquery);
	while ($esrow = mysql_fetch_array($egresult))	
		{
		$editgroup = "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
		$editgroup .= "\t\t<b>$setfont<font color='white'>Edit Group for Survey ID($sid)</font></font></b></td></tr>\n";
		$editgroup .= "\t<tr><form action='$scriptname' name='editgroup' method='post'>\n";
		$editgroup .= "\t\t<td align='right' width='20%'>$setfont<b>Group Name:</b></font></td>\n";
		$editgroup .= "\t\t<td><input type='text' size='40' name='group_name' value='{$esrow['group_name']}'></td></tr>\n";
		$editgroup .= "\t<tr><td align='right'>$setfont<b>Description:</b>(optional)</font></td>\n";
		$editgroup .= "\t\t<td><textarea cols='40' rows='4' name='description'>{$esrow['description']}</textarea></td></tr>\n";
		$editgroup .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Update Group'></td>\n";
		$editgroup .= "\t<input type='hidden' name='action' value='updategroup'>\n";
		$editgroup .= "\t<input type='hidden' name='sid' value='$sid'>\n";
		$editgroup .= "\t<input type='hidden' name='gid' value='$gid'>\n";
		$editgroup .= "\t</form></tr>\n";
		$editgroup .= "</table>\n";
		}
	}
	
if ($action == "editquestion")
	{
	$eqquery = "SELECT * FROM questions WHERE sid=$sid AND gid=$gid AND qid=$qid";
	$eqresult = mysql_query($eqquery);
	while ($eqrow = mysql_fetch_array($eqresult))
		{
		$editquestion = "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
		$editquestion .= "\t\t<b>$setfont<font color='white'>Edit Question $qid</b></font></font></td></tr>\n";
		$editquestion .= "\t<tr><form action='$scriptname' name='editquestion' method='post'>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Question Code:</b></font></td>\n";
		$editquestion .= "\t\t<td><input type='text' size='20' name='title' value='{$eqrow['title']}'></td></tr>\n";
		$editquestion .= "\t<tr>\t\t<td align='right' valign='top'>$setfont<b>Question:</b></font></td>\n";
		$editquestion .= "\t\t<td><textarea cols='35' rows='4' name='question'>{$eqrow['question']}</textarea></td></tr>\n";
		$editquestion .= "\t<tr>\n\t\t<td align='right' valign='top'>$setfont<b>Help:</b></font></td>\n";
		$editquestion .= "\t\t<td><textarea cols='35' rows='4' name='help'>{$eqrow['help']}</textarea></td></tr>\n";
		$editquestion .= "\t<tr>\n\t\t<td align='right'>$setfont<b>Type:</b></font></td>\n";
		$editquestion .= "\t\t<td><select $slstyle name='type'>\n";
		$editquestion .= getqtypelist($eqrow['type']);
		$editquestion .= "\t\t</select></td></tr>\n";
		$editquestion .= "\t<tr><td align='right'>$setfont<b>Group?</b></font></td>\n";
		$editquestion .= "\t\t<td><select $slstyle name='gid'>\n";
		$editquestion .= getgrouplist2($eqrow['gid']);
		$editquestion .= "\t\t</select></td></tr>\n";
		$editquestion .= "\t<tr><td align='right'>$setfont<b>Other?</b></font></td>\n";
		$editquestion .= "\t\t<td><input type='text' size='1' value='{$eqrow['other']}' name='other'></td></tr>\n";
		$editquestion .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Update Question'></td>\n";
		$editquestion .= "\t<input type='hidden' name='action' value='updatequestion'>\n";
		$editquestion .= "\t<input type='hidden' name='sid' value='$sid'>\n";
		$editquestion .= "\t<input type='hidden' name='qid' value='$qid'>\n";
		$editquestion .= "\t</form></tr>\n";
		$editquestion .= "</table>\n";
		}
	}

if ($action == "editanswer")
	{
	$eaquery = "SELECT * FROM answers WHERE qid=$qid AND code='$code'";
	$earesult = mysql_query($eaquery);
	while ($earow = mysql_fetch_array($earesult))
		{
		$editanswer = "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
		$editanswer .= "\t\t<b>$setfont<font color='WHITE'>Edit Answer $qid, $code</b></font></font></td></tr>\n";
		$editanswer .= "\t<tr><form action='$scriptname' name='editanswer' method='post'>\n";
		$editanswer .= "\t\t<td align='right'>$setfont<b>Answer Code:</b></font></td>\n";
		$editanswer .= "\t\t<td><input type='text' size='5' value='{$earow['code']}' name='code'></td></tr>\n";
		$editanswer .= "\t<tr><td align='right'>$setfont<b>Answer:</b></font></td>\n";
		$editanswer .= "\t\t<td><input type='text' value='{$earow['answer']}' name='answer'></td></tr>\n";
		$editanswer .= "\t<tr><td align='right'>$setfont<b>Default?</b></font></td>\n";
		$editanswer .= "\t\t<td><input type='text' size='1' value='{$earow['default']}' name='default'></td></tr>\n";
		$editanswer .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Update Answer'></td>\n";
		$editanswer .= "\t<input type='hidden' name='action' value='updateanswer'>\n";
		$editanswer .= "\t<input type='hidden' name='sid' value='$sid'>\n";
		$editanswer .= "\t<input type='hidden' name='gid' value='$gid'>\n";
		$editanswer .= "\t<input type='hidden' name='qid' value='$qid'>\n";
		$editanswer .= "\t<input type='hidden' name='old_code' value='{$earow['code']}'>\n";
		$editanswer .= "\t</form></tr>\n"; 
		$editanswer .= "</table>\n";
		}
	}

if ($action == "editsurvey")
	{
	$esquery = "SELECT * FROM surveys WHERE sid=$sid";
	$esresult = mysql_query($esquery);
	while ($esrow = mysql_fetch_array($esresult))	
		{
		$editsurvey = "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>";
		$editsurvey .= "\t\t<b>$setfont<font color='white'>Create New Survey</font></font></b></td></tr>\n";
		$editsurvey .= "\t<tr><form name='addnewsurvey' action='$scriptname' method='post'>\n";
		$editsurvey .= "\t\t<td align='right'><b>$setfont Short Title:</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='20' name='short_title' value='{$esrow['short_title']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'><b>$setfont Description:</font></b></td>\n";
		$editsurvey .= "\t\t<td><textarea cols='35' rows='5' name='description'>{$esrow['description']}</textarea></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>Welcome Message:</b></font></td>\n";
		$editsurvey .= "\t\t<td><textarea cols='35' rows='5' name='welcome'>".str_replace("<br />", "\n", $esrow['welcome'])."</textarea></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>Administrator</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='20' name='admin' value='{$esrow['admin']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>Admin Email</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='20' name='adminemail' value='{$esrow['adminemail']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>Expiry Date</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='10' name='expires' value='{$esrow['expires']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Update Survey'></td>\n";
		$editsurvey .= "\t<input type='hidden' name='action' value='updatesurvey'>\n";
		$editsurvey .= "\t<input type='hidden' name='sid' value='{$esrow['sid']}'>\n";
		$editsurvey .= "\t</form></tr>\n";	
		$editsurvey .= "</table>\n";
		}
	}
	
if ($action == "newsurvey")
	{
	$newsurvey = "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
	$newsurvey .= "\t\t<b>$setfont<font color='white'>Create New Survey</font></font></b></td></tr>\n";
	$newsurvey .= "\t<TR><FORM NAME='addnewsurvey' ACTION='$scriptname' METHOD='POST'>\n";
	$newsurvey .= "\t\t<td align='right'><b>$setfont Short Title:</font></b></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='20' name='short_title'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'><b>$setfont Description:</b></td>\n";
	$newsurvey .= "\t\t<td><textarea cols='35' rows='5' name='description'></textarea></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>Welcome Message:</b></font></td>\n";
	$newsurvey .= "\t\t<td><textarea cols='35' rows='5' name='welcome'></textarea></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>Administrator</b></font></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='20' name='admin'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>Admin Email</b></font></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='20' name='adminemail'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>Expiry Date</b></font></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='10' name='expires'></td></tr>\n";
	$newsurvey .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Create Survey'></td>\n";
	$newsurvey .= "\t<input type='hidden' name='action' value='insertnewsurvey'>\n";
	$newsurvey .= "\t</form></tr>\n";	
	$newsurvey .= "</table>\n";
	$newsurvey .= "<center><b>OR</b></center>\n";
	$newsurvey .= "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
	$newsurvey .= "\t\t<b>$setfont<font color='white'>Import Survey</font></font></b></td></tr>\n\t<tr>";
	$newsurvey .= "\t<form enctype='multipart/form-data' name='importsurvey' action='$scriptname' method='post'>\n";
	$newsurvey .= "\t\t<td align='right'>$setfont<b>Select SQL File:</b></font></td>\n";
	$newsurvey .= "\t\t<td><input name=\"the_file\" type=\"file\" size=\"35\"></td></tr>\n";
	$newsurvey .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Import Survey'></TD>\n";
	$newsurvey .= "\t<input type='hidden' name='action' value='importsurvey'>\n";
	$newsurvey .= "\t</tr></form>\n</table>\n";
	
	}
?>