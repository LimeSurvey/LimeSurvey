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
		$questionsummary .= "\t<tr><td width='20%' align='RIGHT'>$setfont<b>Question Title:</b></font></td>\n\t<tr><td>$setfont{$qrrow['title']}</td></tr>\n";
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
		$usersummary .= "\t\t<input type='text' $slstyle naeme'user'></td>\n";
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
		$editquestion = "<TABLE WIDTH='100%' BORDER='0'><TR><TD COLSPAN='2' BGCOLOR='BLACK' ALIGN='CENTER'>";
		$editquestion .= "<B>$setfont<FONT COLOR='WHITE'>Copy Question $qid (Code {$eqrow['title']})</B><BR>Note: You MUST enter a new Question Code!</TD></TR>\n";
		$editquestion .= "<TR><FORM ACTION='$scriptname' NAME='editquestion' >\n";
		$editquestion .= "<TD ALIGN='RIGHT'>$setfont<B>Question Code:</TD>";
		$editquestion .= "<TD><INPUT TYPE='TEXT' SIZE='20' NAME='title' VALUE=''></TD></TR>\n";
		$editquestion .= "<TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Question:</TD>";
		$editquestion .= "<TD><TEXTAREA COLS='35' ROWS='4' NAME='question'>{$eqrow['question']}</TEXTAREA></TD></TR>\n";
		$editquestion .= "<TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Help:</TD>";
		$editquestion .= "<TD><TEXTAREA COLS='35' ROWS='4' NAME='help'>{$eqrow['help']}</TEXTAREA></TD></TR>\n";
		$editquestion .= "<TD ALIGN='RIGHT'>$setfont<B>Type:</TD>";
		$editquestion .= "<TD><SELECT $slstyle NAME='type'>\n";
		$editquestion .= getqtypelist($eqrow['type']);
		$editquestion .= "</SELECT></TD></TR>\n";
		//$editquestion .= "<TD><INPUT TYPE='TEXT' SIZE='1' NAME='type' VALUE='{$eqrow['type']}'></TD></TR>\n";
		$editquestion .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Group?</tD>";
		$editquestion .= "<TD><SELECT $slstyle NAME='gid'>\n";
		$editquestion .= getgrouplist2($eqrow['gid']);
		$editquestion .= "</SELECT></TD></TR>\n";
		$editquestion .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Other?</TD>";
		$editquestion .= "<TD><INPUT TYPE='TEXT' SIZE='1' VALUE='{$eqrow['other']}' NAME='other'></TD></TR>\n";
		$editquestion .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Copy answers:</TD>";
		$editquestion .= "<TD>$setfont<INPUT TYPE='checkbox' CHECKED NAME='copyanswers' VALUE='Y'></TD></TR>\n";
		$editquestion .= "<TR><TD COLSPAN='2' ALIGN='CENTER'><INPUT TYPE='SUBMIT' $btstyle VALUE='Update Question'></TD>";
		$editquestion .= "<INPUT TYPE='HIDDEN' NAME='action' VALUE='copynewquestion'>\n";
		$editquestion .= "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
		$editquestion .= "<INPUT TYPE='HIDDEN' NAME='oldqid' VALUE='$qid'>\n";
		$editquestion .= "</FORM></TR>\n";
		$editquestion .= "</TABLE>\n";
		}

	}

if ($action == "addanswer")
	{
	$newanswer = "<TABLE WIDTH='100%' BORDER='0'><TR><TD COLSPAN='2' BGCOLOR='BLACK' ALIGN='CENTER'>";
	$newanswer .= "<B>$setfont<FONT COLOR='WHITE'>Create New Answer for Survey ID($sid), Group ID($gid), Question ID($qid) </B></TD></TR>\n";
	$newanswer .= "<TR><FORM ACTION='$scriptname' NAME='addnewanswer' ACTION='' METHOD='POST'>\n";
	$newanswer .= "<TD ALIGN='RIGHT'>$setfont<B>Answer Code:</TD>";
	$newanswer .= "<TD><INPUT TYPE='TEXT' SIZE='5' NAME='code'></TD></TR>\n";
	$newanswer .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Answer:</tD>";
	$newanswer .= "<TD><INPUT TYPE='TEXT' NAME='answer'></TD></TR>\n";
	$newanswer .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Default?</TD>";
	$newanswer .= "<TD><INPUT TYPE='TEXT' SIZE='1' VALUE='N' NAME='default'></TD></TR>\n";
	$newanswer .= "<TR><TD COLSPAN='2' ALIGN='CENTER'><INPUT TYPE='SUBMIT' $btstyle VALUE='Add Answer'></TD></TR>\n";
	$newanswer .= "<INPUT TYPE='HIDDEN' NAME='action' VALUE='insertnewanswer'>\n";
	$newanswer .= "<INPUT TYPE='HIDDEN' NAME='qid' VALUE='$qid'>\n";
	$newanswer .= "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
	$newanswer .= "<INPUT TYPE='HIDDEN' NAME='gid' VALUE='$gid'>\n";
	$newanswer .= "</FORM></TABLE>\n";
	} 

if ($action == "addgroup")
	{
	$newgroup = "<TABLE WIDTH='100%' BORDER='0'><TR><TD COLSPAN='2' BGCOLOR='BLACK' ALIGN='CENTER'>";
	$newgroup .= "<B>$setfont<FONT COLOR='WHITE'>Create New Group for Survey ID($sid) </B></TD></TR>\n";
	$newgroup .= "<TR><FORM ACTION='$scriptname' NAME='addnewgroup' ACTION='' METHOD='POST'>\n";
	$newgroup .= "<TD ALIGN='RIGHT'>$setfont<B>Group Name:</TD>";
	$newgroup .= "<TD><INPUT TYPE='TEXT' SIZE='40' NAME='group_name'></TD></TR>\n";
	$newgroup .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Group Description:</B>(optional)</TD>";
	$newgroup .= "<TD><TEXTAREA COLS='40' ROWS='4' NAME='description'></TEXTAREA></TD></TR>\n";
	$newgroup .= "<TR><TD COLSPAN='2' ALIGN='CENTER'><INPUT TYPE='SUBMIT' $btstyle VALUE='Create New Group'></TD>";
	$newgroup .= "<INPUT TYPE='HIDDEN' NAME='action' VALUE='insertnewgroup'>\n";
	$newgroup .= "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
	$newgroup .= "</FORM></TR>\n";
	$newgroup .= "</TABLE>\n";
	}

if ($action == "editgroup")
	{
	$egquery = "SELECT * FROM groups WHERE sid=$sid AND gid=$gid";
	$egresult = mysql_query($egquery);
	while ($esrow = mysql_fetch_array($egresult))	
		{
		$editgroup = "<TABLE WIDTH='100%' BORDER='0'><TR><TD COLSPAN='2' BGCOLOR='BLACK' ALIGN='CENTER'>";
		$editgroup .= "<B>$setfont<FONT COLOR='WHITE'>Edit Group for Survey ID($sid) </B></TD></TR>\n";
		$editgroup .= "<TR><FORM ACTION='$scriptname' NAME='editgroup' METHOD='POST'>\n";
		$editgroup .= "<TD ALIGN='RIGHT' WIDTH='20%'>$setfont<B>Group Name:</TD>";
		$editgroup .= "<TD><INPUT TYPE='TEXT' SIZE='40' NAME='group_name' value='{$esrow['group_name']}'></TD></TR>\n";
		$editgroup .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Description:</B>(optional)</TD>";
		$editgroup .= "<TD><TEXTAREA COLS='40' ROWS='4' NAME='description'>{$esrow['description']}</TEXTAREA></TD></TR>\n";
		$editgroup .= "<TR><TD COLSPAN='2' ALIGN='CENTER'><INPUT TYPE='SUBMIT' $btstyle VALUE='Update Group'></TD>";
		$editgroup .= "<INPUT TYPE='HIDDEN' NAME='action' VALUE='updategroup'>\n";
		$editgroup .= "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
		$editgroup .= "<INPUT TYPE='HIDDEN' NAME='gid' VALUE='$gid'>\n";
		$editgroup .= "</FORM></TR>\n";
		$editgroup .= "</TABLE>\n";
		}	
	}
	
if ($action == "editquestion")
	{
	$eqquery = "SELECT * FROM questions WHERE sid=$sid AND gid=$gid AND qid=$qid";
	$eqresult = mysql_query($eqquery);
	while ($eqrow = mysql_fetch_array($eqresult))
		{
		$editquestion = "<TABLE WIDTH='100%' BORDER='0'><TR><TD COLSPAN='2' BGCOLOR='BLACK' ALIGN='CENTER'>";
		$editquestion .= "<B>$setfont<FONT COLOR='WHITE'>Edit Question $qid</B></TD></TR>\n";
		$editquestion .= "<TR><FORM ACTION='$scriptname' NAME='editquestion' >\n";
		$editquestion .= "<TD ALIGN='RIGHT'>$setfont<B>Question Code:</TD>";
		$editquestion .= "<TD><INPUT TYPE='TEXT' SIZE='20' NAME='title' VALUE='{$eqrow['title']}'></TD></TR>\n";
		$editquestion .= "<TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Question:</TD>";
		$editquestion .= "<TD><TEXTAREA COLS='35' ROWS='4' NAME='question'>{$eqrow['question']}</TEXTAREA></TD></TR>\n";
		$editquestion .= "<TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Help:</TD>";
		$editquestion .= "<TD><TEXTAREA COLS='35' ROWS='4' NAME='help'>{$eqrow['help']}</TEXTAREA></TD></TR>\n";
		$editquestion .= "<TD ALIGN='RIGHT'>$setfont<B>Type:</TD>";
		$editquestion .= "<TD><SELECT $slstyle NAME='type'>\n";
		$editquestion .= getqtypelist($eqrow['type']);
		$editquestion .= "</SELECT></TD></TR>\n";
		//$editquestion .= "<TD><INPUT TYPE='TEXT' SIZE='1' NAME='type' VALUE='{$eqrow['type']}'></TD></TR>\n";
		$editquestion .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Group?</tD>";
		$editquestion .= "<TD><SELECT $slstyle NAME='gid'>\n";
		$editquestion .= getgrouplist2($eqrow['gid']);
		$editquestion .= "</SELECT></TD></TR>\n";
		$editquestion .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Other?</TD>";
		$editquestion .= "<TD><INPUT TYPE='TEXT' SIZE='1' VALUE='{$eqrow['other']}' NAME='other'></TD></TR>\n";
		//$editquestion .= "<TD><INPUT TYPE='TEXT' SIZE='1' VALUE='{$eqrow['gid']}' NAME='gid'></TD></TR>\n";
		$editquestion .= "<TR><TD COLSPAN='2' ALIGN='CENTER'><INPUT TYPE='SUBMIT' $btstyle VALUE='Update Question'></TD>";
		$editquestion .= "<INPUT TYPE='HIDDEN' NAME='action' VALUE='updatequestion'>\n";
		$editquestion .= "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
		//$editquestion .= "<INPUT TYPE='HIDDEN' NAME='gid' VALUE='$gid'>\n";
		$editquestion .= "<INPUT TYPE='HIDDEN' NAME='qid' VALUE='$qid'>\n";
		$editquestion .= "</FORM></TR>\n";
		$editquestion .= "</TABLE>\n";
		}
	}

if ($action == "editanswer")
	{
	$eaquery = "SELECT * FROM answers WHERE qid=$qid AND code='$code'";
	$earesult = mysql_query($eaquery);
	while ($earow = mysql_fetch_array($earesult))
		{
		$editanswer = "<TABLE WIDTH='100%' BORDER='0'><TR><TD COLSPAN='2' BGCOLOR='BLACK' ALIGN='CENTER'>";
		$editanswer .= "<B>$setfont<FONT COLOR='WHITE'>Edit Answer $qid, $code</B></TD></TR>\n";
		$editanswer .= "<TR><FORM ACTION='$scriptname' NAME='editanswer' METHOD='POST'>\n";
		$editanswer .= "<TD ALIGN='RIGHT'>$setfont<B>Answer Code:</TD>";
		$editanswer .= "<TD><INPUT TYPE='TEXT' SIZE='5' VALUE='{$earow['code']}' NAME='code'></TD></TR>\n";
		$editanswer .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Answer:</TD>";
		$editanswer .= "<TD><INPUT TYPE='TEXT' VALUE='{$earow['answer']}' NAME='answer'></TD></TR>\n";
		$editanswer .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Default?</TD>";
		$editanswer .= "<TD><INPUT TYPE='TEXT' SIZE='1' VALUE='{$earow['default']}' NAME='default'></TD></TR>\n";
		$editanswer .= "<TR><TD COLSPAN='2' ALIGN='CENTER'><INPUT TYPE='SUBMIT' $btstyle VALUE='Update Answer'></TD>";
		$editanswer .= "<INPUT TYPE='HIDDEN' NAME='action' VALUE='updateanswer'>\n";
		$editanswer .= "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
		$editanswer .= "<INPUT TYPE='HIDDEN' NAME='gid' VALUE='$gid'>\n";
		$editanswer .= "<INPUT TYPE='HIDDEN' NAME='qid' VALUE='$qid'>\n";
		$editanswer .= "<INPUT TYPE='HIDDEN' NAME='old_code' VALUE='{$earow['code']}'>\n";
		$editanswer .= "</FORM></TR>\n"; 
		$editanswer .= "</TABLE>\n";
				
		}
	}

if ($action == "editsurvey")
	{
	$esquery = "SELECT * FROM surveys WHERE sid=$sid";
	$esresult = mysql_query($esquery);
	while ($esrow = mysql_fetch_array($esresult))	
		{
		$editsurvey = "<TABLE WIDTH='100%' BORDER='0'><TR><TD COLSPAN='2' BGCOLOR='BLACK' ALIGN='CENTER'>";
		$editsurvey .= "<B>$setfont<FONT COLOR='WHITE'>Create New Survey </B></TD></TR>\n";
		$editsurvey .= "<TR><FORM NAME='addnewsurvey' ACTION='$scriptname' METHOD='POST'>\n";
		$editsurvey .= "<TD ALIGN='RIGHT'><B>$setfont Short Title:</TD>";
		$editsurvey .= "<TD><INPUT TYPE='text' SIZE='20' NAME='short_title' VALUE='{$esrow['short_title']}'></tD></TR>\n";
		$editsurvey .= "<TR><TD ALIGN='RIGHT'><B>$setfont Description:</TD>";
		$editsurvey .= "<TD><TEXTAREA COLS='35' ROWS='5' NAME='description'>{$esrow['description']}</TEXTAREA></TD></TR>\n";
		$editsurvey .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Welcome Message:</TD>";
		$editsurvey .= "<TD><TEXTAREA COLS='35' ROWS='5' NAME='welcome'>".str_replace("<BR>", "\n", $esrow['welcome'])."</TEXTAREA></TD></TR>\n";
		$editsurvey .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Administrator</TD>";
		$editsurvey .= "<TD><INPUT TYPE='TEXT' SIZE='20' NAME='admin' VALUE='{$esrow['admin']}'></TD></TR>\n";
		$editsurvey .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Admin Email</TD>";
		$editsurvey .= "<TD><INPUT TYPE='TEXT' SIZE='20' NAME='adminemail' VALUE='{$esrow['adminemail']}'></TD></TR>\n";
		$editsurvey .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Expiry Date</TD>";
		$editsurvey .= "<TD><INPUT TYPE='TEXT' SIZE='10' NAME='expires' VALUE='{$esrow['expires']}'></TD></TR>\n";
		$editsurvey .= "<TR><TD COLSPAN='2' ALIGN='CENTER'><INPUT TYPE='SUBMIT' $btstyle VALUE='Update Survey'></TD>";
		$editsurvey .= "<INPUT TYPE='HIDDEN' NAME='action' VALUE='updatesurvey'>\n";
		$editsurvey .= "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='{$esrow['sid']}'>\n";
		$editsurvey .= "</FORM></TR>\n";	
		$editsurvey .= "</TABLE>\n";
		}
	}
	
if ($action == "newsurvey")
	{
	$newsurvey = "<TABLE WIDTH='100%' BORDER='0'><TR><TD COLSPAN='2' BGCOLOR='BLACK' ALIGN='CENTER'>";
	$newsurvey .= "<B>$setfont<FONT COLOR='WHITE'>Create New Survey </B></TD></TR>\n";
	$newsurvey .= "<TR><FORM NAME='addnewsurvey' ACTION='$scriptname' METHOD='POST'>\n";
	$newsurvey .= "<TD ALIGN='RIGHT'><B>$setfont Short Title:</TD>";
	$newsurvey .= "<TD><INPUT TYPE='text' SIZE='20' NAME='short_title'></tD></TR>\n";
	$newsurvey .= "<TR><TD ALIGN='RIGHT'><B>$setfont Description:</TD>";
	$newsurvey .= "<TD><TEXTAREA COLS='35' ROWS='5' NAME='description'></TEXTAREA></TD></TR>\n";
	$newsurvey .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Welcome Message:</TD>";
	$newsurvey .= "<TD><TEXTAREA COLS='35' ROWS='5' NAME='welcome'></TEXTAREA></TD></TR>\n";
	$newsurvey .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Administrator</TD>";
	$newsurvey .= "<TD><INPUT TYPE='TEXT' SIZE='20' NAME='admin'></TD></TR>\n";
	$newsurvey .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Admin Email</TD>";
	$newsurvey .= "<TD><INPUT TYPE='TEXT' SIZE='20' NAME='adminemail'></TD></TR>\n";
	$newsurvey .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Expiry Date</TD>";
	$newsurvey .= "<TD><INPUT TYPE='TEXT' SIZE='10' NAME='expires'></TD></TR>\n";
	$newsurvey .= "<TR><TD COLSPAN='2' ALIGN='CENTER'><INPUT TYPE='SUBMIT' $btstyle VALUE='Create Survey'></TD>";
	$newsurvey .= "<INPUT TYPE='HIDDEN' NAME='action' VALUE='insertnewsurvey'>\n";
	$newsurvey .= "</FORM></TR>\n";	
	$newsurvey .= "</TABLE>\n";
	$newsurvey .= "<CENTER><B>OR</B></CENTER>";
	$newsurvey .= "<TABLE WIDTH='100%' BORDER='0'><TR><TD COLSPAN='2' BGCOLOR='BLACK' ALIGN='CENTER'>";
	$newsurvey .= "<B>$setfont<FONT COLOR='WHITE'>Import Survey</B></TD></TR>\n<TR>";
	//$newsurvey .= "<FORM ENCTYPE='multipart/form-data' NAME='importsurvey' ACTION='importsurvey.php' METHOD='POST'>\n";
	$newsurvey .= "<FORM ENCTYPE='multipart/form-data' NAME='importsurvey' ACTION='$scriptname' METHOD='POST'>\n";
	$newsurvey .= "<TD ALIGN='RIGHT'>$setfont<B>Select SQL File:</TD>";
	$newsurvey .= "<TD><INPUT NAME=\"the_file\" TYPE=\"file\" SIZE=\"35\"></TD></TR>\n";
	$newsurvey .= "<TR><TD COLSPAN='2' ALIGN='CENTER'><INPUT TYPE='SUBMIT' $btstyle VALUE='Import Survey'></TD>";
	$newsurvey .= "<INPUT TYPE='HIDDEN' NAME='action' VALUE='importsurvey'>\n";
	$newsurvey .= "</TR></FORM></TABLE>\n";
	
	}
?>