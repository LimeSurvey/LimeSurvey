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
		$surveysummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Title:</b></font></td>\n";
		$surveysummary .= "\t<td>$setfont<font color='#000080'><b>{$s1row['short_title']} (ID {$s1row['sid']})</b><br />";
		if ($s1row['private'] != "N") {$surveysummary .= "This survey is anonymous";}
		else {$surveysummary .= "This survey is <b>not</b> anonymous";}
		if ($s1row['format'] == "S") {$surveysummary .= " and is presented question by question.";}
		elseif ($s1row['format'] == "G") {$surveysummary .= " and is presented group by group.";}
		else {$surveysummary .= " and is presented as one single page.";}
		$surveysummary .= "</font></td></tr>\n";
		$surveysummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Description:</b></font></td>\n";
		$surveysummary .= "\t\t<td>$setfont {$s1row['description']}</font></td></tr>\n";
		$surveysummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Welcome:</b></font></td>\n";
		$surveysummary .= "\t\t<td>$setfont {$s1row['welcome']}</font></td></tr>\n";
		$surveysummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Admin:</b></font></td>\n";
		$surveysummary .= "\t\t<td>$setfont {$s1row['admin']} ({$s1row['adminemail']})</font></td></tr>\n";
		$surveysummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Fax To:</b></font></td>\n";
		$surveysummary .= "\t\t<td>$setfont {$s1row['faxto']}</font></td></tr>\n";
		if ($s1row['expires'] != "0000-00-00") 
			{
			$surveysummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Expires:</b></font></td>\n";
			$surveysummary .= "\t<td>$setfont {$s1row['expires']}</font></td></tr>\n";
			}
		$activated = $s1row['active'];
		$surveysummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Template:</b></font></td>\n";
		$surveysummary .= "\t\t<td>$setfont {$s1row['template']}</font></td></tr>\n";
		$surveysummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Link:</b></font></td>\n";
		$surveysummary .= "\t\t<td>$setfont <a href='{$s1row['url']}' title='{$s1row['url']}'>{$s1row['urldescrip']}</a></font></td></tr>\n";
		}
	
	$sumquery2 = "SELECT * FROM groups WHERE sid=$sid";
	$sumresult2 = mysql_query($sumquery2);
	$sumcount2 = mysql_num_rows($sumresult2);
	$surveysummary .= "\t<tr><td align='right'>$setfont<b>Groups:</b></font></td>\n";
	$surveysummary .= "\t<td>$setfont";
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

	$surveysummary .= "\t<tr><td align='right'>$setfont<b>Activation</b></font></td>\n";
	$surveysummary .= "\t<td valign='top'>$setfont";
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
		$surveysummary .= "<font size='1'>Survey cannot be activated yet.\n";
		if ($sumcount2 == 0) 
			{
			$surveysummary .= "\t<font color='green'>[You need to Add Groups]</font>";
			}
		if ($sumcount3 == 0)
			{
			$surveysummary .= "\t<font color='green'>[You need to Add Questions]</font>";
			}
		}
	$surveysummary .= "</td></tr>\n";
	
	//OPTIONS
	$surveysummary .= "\t<tr><td colspan='2' align='right'>\n";
	$surveysummary .= "\t\t<input type='submit' $btstyle value='Export' title='Export Survey Structure..' onClick=\"window.open('dumpsurvey.php?sid=$sid', '_top')\">\n";
	if ($activated == "N") 
		{
		$surveysummary .= "\t\t<input type='submit' $btstyle value='Test Data Entry' onClick=\"window.open('dataentry.php?sid=$sid', '_blank')\">\n";
		$surveysummary .= "\t\t<input type='submit' $btstyle value='Test Survey' onClick=\"window.open('../index.php?sid=$sid', '_blank')\">\n";
		}
	else 
		{
		$surveysummary .= "\t\t<input type='submit' $btstyle value='Browse' onClick=\"window.open('browse.php?sid=$sid', '_top')\">\n";
		$surveysummary .= "\t\t<input type='submit' $btstyle value='Data Entry' onClick=\"window.open('dataentry.php?sid=$sid', '_blank')\">\n";
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
	$grpquery ="SELECT * FROM groups WHERE gid=$gid ORDER BY group_name";
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
		$questionsummary .= "\t<tr><td width='20%' align='right'>$setfont<b>Question Title:</b></font></td>\n";
		$questionsummary .= "\t<td>$setfont{$qrrow['title']}";
		if ($qrrow['mandatory'] == "Y") {$questionsummary .= ": (<i>Mandatory Question</i>)";}
		else {$questionsummary .= ": (<i>Optional Question</i>)";}
		$questionsummary .= "</td></tr>\n";
		$questionsummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Question:</b></font></td>\n\t<td>$setfont{$qrrow['question']}</td></tr>\n";
		$questionsummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Help:</b></font></td>\n\t<td>$setfont{$qrrow['help']}</td></tr>\n";
		$qtypes = getqtypelist("", "array"); //qtypes = array(type code=>type description)
		$questionsummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Type:</b></font></td>\n\t<td>$setfont{$qtypes[$qrrow['type']]}</td></tr>\n";
		$qrq = "SELECT * FROM answers WHERE qid=$qid ORDER BY answer";
		$qrr = mysql_query($qrq);
		$qct = mysql_num_rows($qrr);
		if ($qrrow['type'] == "O" || $qrrow['type'] == "L" || $qrrow['type'] == "M" || $qrrow['type'] == "A" || $qrrow['type'] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "P" || $qrrow['type'] == "R")
			{
			$questionsummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Answers:</b></font></td>\n";
			$questionsummary .= "\t<td>\n\t\t<select $slstyle name='answer' onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
			$questionsummary .= getanswers();
			$questionsummary .= "\n\t\t</select>\n";
			if ($qct == 0) {$questionsummary .= "\t\t<font face='verdana' size='1' color='green'>[You need to Add Answers]</font>\n";}
			$questionsummary .= "\t</td></tr>\n";
			}
		if ($qrrow['type'] == "M" or $qrrow['type'] == "P")
			{
			$questionsummary .= "\t<tr><td align='right' valign='top'>$setfont<b>Other?</b></font></td>\n\t<td>$setfont{$qrrow['other']}</td></tr>\n";
			}
		$questionsummary .= "\t<tr><td colspan='2' align='right'>\n";
		$questionsummary .= "\t\t<input type='submit' $btstyle value='Set Conditions' onClick=\"window.open('conditions.php?sid=$sid&qid=$qid', 'conditions', 'menubar=no, location=no, status=no, height=350, width=560, scrollbars=yes, resizable=yes')\">\n";
		$questionsummary .= "\t\t<input type='submit' $btstyle value='Edit Question' onClick=\"window.open('$scriptname?action=editquestion&sid=$sid&gid=$gid&qid=$qid', '_top')\">\n";
		if ($qrrow['type'] == "O" || $qrrow['type'] == "L" || $qrrow['type'] == "M" || $qrrow['type']=="A" || $qrrow['type'] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "P" || $qrrow['type'] == "R") 
			{
			if (($activated == "Y" && $qrrow['type'] == "L") || ($activated == "N"))
				{
				$questionsummary .= "\t\t<input type='submit' $btstyle value='Add Answer' onClick=\"window.open('$scriptname?action=addanswer&sid=$sid&gid=$gid&qid=$qid', '_top')\">\n";
				}
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
		$usersummary .= "\t<td>$setfont<b>{$mrw['user']}</b></font></td>\n";
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
		$usersummary = "<table width='100%' border='0'>\n";
		$usersummary .= "\t<tr>\n";
		$usersummary .= "\t\t<td bgcolor='black' align='center'>\n";
		$usersummary .= "\t\t\t<b>$setfont<font color='WHITE'>Security Control</font></font></b>\n";
		$usersummary .= "\t\t</td>\n";
		$usersummary .= "\t</tr>\n";
		$usersummary .= "\t<tr>\n";
		$usersummary .= "\t\t<td>\n";
		$usersummary .= "\t\t\t$setfont<font color='RED'><b>Warning:</font></font></b><br />\n";
		$usersummary .= "\t\t\tYou have not yet initialised security settings for your survey system \n";
		$usersummary .= "\t\t\tand subsequently there are no restrictions on access.\n";
		$usersummary .= "\t\t\t<p>If you click on the 'initialise security' button below, standard APACHE \n";
		$usersummary .= "\t\t\tsecurity settings will be added to the administration directory of this \n";
		$usersummary .= "\t\t\tscript. You will then need to use the default access username and password \n";
		$usersummary .= "\t\t\tto access the administration and data entry scripts.</p>\n";
		$usersummary .= "\t\t\t<p>USERNAME: $defaultuser<br />PASSWORD: $defaultpass</p>\n";
		$usersummary .= "\t\t\t<p>It is highly recommended that once your security system has been initialised \n";
		$usersummary .= "\t\t\tyou change this default password.</p>\n";
		$usersummary .= "\t\t</td>\n";
		$usersummary .= "\t</tr>\n";
		$usersummary .= "\t<tr>\n";
		$usersummary .= "\t\t<td align='center'>\n";
		$usersummary .= "\t\t\t<input type='submit' $btstyle value='Initialise Security' onClick=\"window.open('$scriptname?action=setupsecurity', '_top')\">\n";
		$usersummary .= "\t\t</td>\n";
		$usersummary .= "\t</tr>\n";
		$usersummary .= "</table>\n";
		}
	else
		{
		$usersummary = "<table width='100%' border='0'>\n";
		$usersummary .= "\t<tr>\n";
		$usersummary .= "\t\t<td colspan='4' bgcolor='black' align='center'>\n";
		$usersummary .= "\t\t\t<b>$setfont<font color='white'>List of users</font><font></b>\n";
		$usersummary .= "\t\t</td>\n";
		$usersummary .= "\t</tr>\n";
		$usersummary .= "\t<tr bgcolor='#444444'>\n";
		$usersummary .= "\t\t<td>$setfont<font color='white'><b>User</b></td>\n";
		$usersummary .= "\t\t<td>$setfont<font color='white'><b>Password</b></font></font></td>\n";
		$usersummary .= "\t\t<td>$setfont<font color='white'><b>Security</b></font></font></td>\n";
		$usersummary .= "\t\t<td>$setfont<font color='white'><b>Actions</b></font></font></td>\n";
		$usersummary .= "\t</tr>\n";
		$userlist = getuserlist();
		$ui = count($userlist);
		if ($ui < 1)
			{
			$usersummary .= "\t<tr>\n";
			$usersummary .= "\t\t<td>\n";
			$usersummary .= "\t\t\t<center>WARNING: No users exist in your table. We recommend you 'turn off' security. You can then 'turn it on' again.</center>";
			$usersummary .= "\t\t</td>\n";
			$usersummary .= "\t</tr>\n";
			}
		else
			{
			foreach ($userlist as $usr)
				{
				$usersummary .= "\t<tr>\n";
				$usersummary .= "\t<td>$setfont<b>{$usr['user']}</b></font></td>\n";
				$usersummary .= "\t\t<td>$setfont{$usr['password']}</font></td>\n";
				$usersummary .= "\t\t<td>$setfont{$usr['security']}</td>\n";
				$usersummary .= "\t\t<td>\n";
				$usersummary .= "\t\t\t<input type='submit' $btstyle value='Edit' onClick=\"window.open('$scriptname?action=modifyuser&user={$usr['user']}', '_top')\" />\n";
				if ($ui > 1 )
					{
					$usersummary .= "\t\t\t<input type='submit' $btstyle value='Del' onClick=\"window.open('$scriptname?action=deluser&user={$usr['user']}', '_top')\" />\n";
					}
				$usersummary .= "\t\t</td>\n";
				$usersummary .= "\t</tr>\n";
				$ui++;
				}
			}
		$usersummary .= "\t\t<form action='$scriptname' method='post'>\n";
		$usersummary .= "\t\t<tr bgcolor='#EEEFFF'>\n";
		$usersummary .= "\t\t<td><input type='text' $slstyle name='user'></td>\n";
		$usersummary .= "\t\t<td><input type='text' $slstyle name='pass'></td>\n";
		$usersummary .= "\t\t<td><input type='text' $slstyle name='level' size='2'></td>\n";
		$usersummary .= "\t\t<td><input type='submit' $btstyle value='Add New User'></td>\n";
		$usersummary .= "\t</tr>\n";
		$usersummary .= "\t<tr>\n";
		$usersummary .= "\t\t<td><input type='hidden' name='action' value='adduser'></td>\n";
		$usersummary .= "\t</tr>\n";
		$usersummary .= "\t</form>\n";
		$usersummary .= "\t<tr>\n";
		$usersummary .= "\t\t<td colspan='3'></td>\n";
		$usersummary .= "\t\t<td><input type='submit' $btstyle value='Turn Off Security' ";
		$usersummary .= "onClick=\"window.open('$scriptname?action=turnoffsecurity', '_top')\" /></td>\n";
		$usersummary .= "\t</tr>\n";		
		$usersummary .= "</table>\n";
		}
	}
if ($action == "addquestion")
	{
	$newquestion = "<table width='100%' border='0'>\n\n";
	$newquestion .= "\t<tr>\n";
	$newquestion .= "\t\t<td colspan='2' bgcolor='black' align='center'>";
	$newquestion .= "\t\t<b>$setfont<font color='white'>Create New Question for Survey ID($sid), Group ID($gid) </b>\n";
	$newquestion .= "\t\t</td>\n";
	$newquestion .= "\t</tr>\n";
	$newquestion .= "\t<form action='$scriptname' name='addnewquestion' method='post'>\n";
	$newquestion .= "\t<tr>\n";
	$newquestion .= "\t\t<td align='right'>$setfont<b>Question Code:</b></font></td>\n";
	$newquestion .= "\t\t<td><input type='text' size='20' name='title'><font color='red' face='verdana' size='1'>*Required</font></td></tr>\n";
	$newquestion .= "\t<tr>\n";
	$newquestion .= "\t\t<td align='right'>$setfont<b>Question:</b></font></td>\n";
	$newquestion .= "\t\t<td><textarea cols='35' rows='3' name='question'></textarea></td>\n";
	$newquestion .= "\t</tr>\n";
	$newquestion .= "\t<tr>\n";
	$newquestion .= "\t\t<td align='right'>$setfont<b>Help:</b></font></td>\n";
	$newquestion .= "\t\t<td><textarea cols='35' rows='3' name='help'></textarea></td>\n";
	$newquestion .= "\t</tr>\n";
	$newquestion .= "\t<tr>\n";
	$newquestion .= "\t\t<td align='right'>$setfont<b>Question Type:</b></font></td>\n";
	$newquestion .= "\t\t<td><select $slstyle name='type' onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n";
	$newquestion .= "$qtypeselect";
	$newquestion .= "\t\t</select></td>\n";
	$newquestion .= "\t</tr>\n";
	
	$newquestion .= "\t<tr id='OtherSelection' style='display: none'>\n";
	$newquestion .= "\t\t<td align='right'>$setfont<b>Other?</b></font></td>\n";
	$newquestion .= "\t\t<td>$setfont\n";
	$newquestion .= "\t\t\tYes <input type='radio' name='other' value='Y' />&nbsp;&nbsp;\n";
	$newquestion .= "\t\t\tNo <input type='radio' name='other' value='N' checked />\n";
	$newquestion .= "\t\t</td>\n";
	$newquestion .= "\t</tr>\n";

	$newquestion .= "\t<tr id='MandatorySelection' style='display: none'>\n";
	$newquestion .= "\t\t<td align='right'>$setfont<b>Mandatory?</b></font></td>\n";
	$newquestion .= "\t\t<td>$setfont\n";
	$newquestion .= "\t\t\tYes <input type='radio' name='mandatory' value='Y' />&nbsp;&nbsp;\n";
	$newquestion .= "\t\t\tNo <input type='radio' name='mandatory' value='N' checked />\n";
	$newquestion .= "\t\t</td>\n";
	$newquestion .= "\t</tr>\n";
	
	$newquestion .= "<script type='text/javascript'>\n";
	$newquestion .= "<!--\n";
	$newquestion .= "function OtherSelection(QuestionType)\n";
	$newquestion .= "\t{\n";
	$newquestion .= "\tif (QuestionType == 'M' || QuestionType == 'P')\n";
	$newquestion .= "\t\t{\n";
	$newquestion .= "\t\tdocument.getElementById('OtherSelection').style.display = '';\n";
	$newquestion .= "\t\t}\n";
	$newquestion .= "\telse\n";
	$newquestion .= "\t\t{\n";
	$newquestion .= "\t\tdocument.getElementById('OtherSelection').style.display = 'none';\n";
	$newquestion .= "\t\tdocument.addnewquestion.other[1].checked = true;\n";
	$newquestion .= "\t\t}\n";
	$newquestion .= "\tif (QuestionType == 'S' || QuestionType == 'T' || QuestionType == '')\n";
	$newquestion .= "\t\t{\n";
	$newquestion .= "\t\tdocument.getElementById('MandatorySelection').style.display = 'none';\n";
	$newquestion .= "\t\tdocument.editquestion.mandatory[1].checked=true;\n";
	$newquestion .= "\t\t}\n";
	$newquestion .= "\telse\n";
	$newquestion .= "\t\t{\n";
	$newquestion .= "\t\tdocument.getElementById('MandatorySelection').style.display = '';\n";
	$newquestion .= "\t\t}\n";
	$newquestion .= "\t}\n";
	$newquestion .= "\tOtherSelection('{$eqrow['type']}');\n";
	$newquestion .= "-->\n";
	$newquestion .= "</script>\n";
	
	$newquestion .= "\t<tr>\n";
	$newquestion .= "\t\t<td colspan='2' align='center'><input type='submit' $btstyle value='Add Question' /></td>\n";
	$newquestion .= "\t</tr>\n";
	$newquestion .= "\t<input type='hidden' name='action' value='insertnewquestion' />\n";
	$newquestion .= "\t<input type='hidden' name='sid' value='$sid' />\n";
	$newquestion .= "\t<input type='hidden' name='gid' value='$gid' />\n";
	$newquestion .= "\t</form>\n";
	$newquestion .= "</table>\n";
	}

if ($action == "copyquestion")
	{
	$eqquery = "SELECT * FROM questions WHERE sid=$sid AND gid=$gid AND qid=$qid";
	$eqresult = mysql_query($eqquery);
	while ($eqrow = mysql_fetch_array($eqresult))
		{
		$editquestion = "<table width='100%' border='0'>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td colspan='2' bgcolor='black' align='center'>\n";
		$editquestion .= "\t\t\t<b>$setfont<font color='white'>Copy Question $qid (Code {$eqrow['title']})</b><br />Note: You MUST enter a new Question Code!</font></font>\n";
		$editquestion .= "\t\t</td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr><form action='$scriptname' name='editquestion' method='post'>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Question Code:</b></font></td>\n";
		$editquestion .= "\t\t<td><input type='text' size='20' name='title' value='' /></td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right' valign='top'>$setfont<b>Question:</b></font></td>\n";
		$editquestion .= "\t\t<td><textarea cols='35' rows='4' name='question'>{$eqrow['question']}</textarea></td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right' valign='top'>$setfont<b>Help:</b></font></td>\n";
		$editquestion .= "\t\t<td><textarea cols='35' rows='4' name='help'>{$eqrow['help']}</textarea></td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Type:</b></font></td>\n";
		$editquestion .= "\t\t<td><select $slstyle name='type' onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n";
		$editquestion .= getqtypelist($eqrow['type']);
		$editquestion .= "\t\t</select></td>\n";
		$editquestion .= "\t</tr>\n";
		//$editquestion .= "<TD><INPUT TYPE='TEXT' SIZE='1' NAME='type' VALUE='{$eqrow['type']}'></TD></TR>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Group?</b></font></td>\n";
		$editquestion .= "\t\t<td><select $slstyle name='gid'>\n";
		$editquestion .= getgrouplist2($eqrow['gid']);
		$editquestion .= "\t\t\t</select></td>\n";
		$editquestion .= "\t</tr>\n";
		
		$editquestion .= "\t<tr id='OtherSelection' style='display: none'>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Other?</b></font></td>\n";
		$editquestion .= "\t\t<td>$setfont\n";
		$editquestion .= "\t\t\tYes <input type='radio' name='other' value='Y'";
		if ($eqrow['other'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n";
		$editquestion .= "\t\t\tNo <input type='radio' name='other' value='N'";
		if ($eqrow['other'] == "N") {$editquestion .= " checked";}
		$editquestion .= " />\n";
		$editquestion .= "\t\t</td>\n";
		$editquestion .= "\t</tr>\n";
		
		$editquestion .= "\t<tr id='MandatorySelection' style='display: none'>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Mandatory?</b></font></td>\n";
		$editquestion .= "\t\t<td>$setfont\n";
		$editquestion .= "\t\t\tYes <input type='radio' name='mandatory' value='Y'";
		if ($eqrow['mandatory'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n";
		$editquestion .= "\t\t\tNo <input type='radio' name='mandatory' value='N'";
		if ($eqrow['mandatory'] != "Y") {$editquestion .= " checked";}
		$editquestion .= " />\n";
		$editquestion .= "\t\t</td>\n";
		$editquestion .= "\t</tr>\n";
		
		$editquestion .= "<script type='text/javascript'>\n";
		$editquestion .= "<!--\n";
		$editquestion .= "function OtherSelection(QuestionType)\n";
		$editquestion .= "\t{\n";
		$editquestion .= "\tif (QuestionType == 'M' || QuestionType == 'P')\n";
		$editquestion .= "\t\t{\n";
		$editquestion .= "\t\tdocument.getElementById('OtherSelection').style.display = '';\n";
		$editquestion .= "\t\t}\n";
		$editquestion .= "\telse\n";
		$editquestion .= "\t\t{\n";
		$editquestion .= "\t\tdocument.getElementById('OtherSelection').style.display = 'none';\n";
		$editquestion .= "\t\tdocument.editquestion.other[1].checked = true;\n";
		$editquestion .= "\t\t}\n";
		$editquestion .= "\tif (QuestionType == 'S' || QuestionType == 'T')\n";
		$editquestion .= "\t\t{\n";
		$editquestion .= "\t\tdocument.getElementById('MandatorySelection').style.display = 'none';\n";
		$editquestion .= "\t\tdocument.editquestion.mandatory[1].checked=true;\n";
		$editquestion .= "\t\t}\n";
		$editquestion .= "\telse\n";
		$editquestion .= "\t\t{\n";
		$editquestion .= "\t\tdocument.getElementById('MandatorySelection').style.display = '';\n";
		$editquestion .= "\t\t}\n";
		$editquestion .= "\t}\n";
		$editquestion .= "\tOtherSelection('{$eqrow['type']}');\n";
		$editquestion .= "-->\n";
		$editquestion .= "</script>\n";
		
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Copy answers:</b></font></td>\n";
		$editquestion .= "\t\t<td>$setfont<input type='checkbox' checked name='copyanswers' value='Y' /></font></td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td colspan='2' align='center'><input type='submit' $btstyle value='Copy Question'></td>\n";
		$editquestion .= "\t\t<input type='hidden' name='action' value='copynewquestion'>\n";
		$editquestion .= "\t\t<input type='hidden' name='sid' value='$sid' />\n";
		$editquestion .= "\t\t<input type='hidden' name='oldqid' value='$qid' />\n";
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
	$newanswer .= "\t\t<td><input type='text' size='5' name='code' maxlength='5'><font color='red' face='verdana' size='1'>*Required</font></td></tr>\n";
	$newanswer .= "\t<tr><td align='right'>$setfont<b>Answer:</b></font></td>\n";
	$newanswer .= "\t\t<td><input type='text' name='answer'><font color='red' face='verdana' size='1'>*Required</font></td></tr>\n";
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
	$newgroup .= "\t\t<td><input type='text' size='40' name='group_name'><font color='red' face='verdana' size='1'>*Required</font></td></tr>\n";
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
		$editquestion = "<table width='100%' border='0'>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td colspan='2' bgcolor='black' align='center'>\n";
		$editquestion .= "\t\t\t<b>$setfont<font color='white'>Edit Question $qid</b></font></font>\n";
		$editquestion .= "\t\t</td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr><form action='$scriptname' name='editquestion' method='post'>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Question Code:</b></font></td>\n";
		$editquestion .= "\t\t<td><input type='text' size='20' name='title' value='{$eqrow['title']}'></td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right' valign='top'>$setfont<b>Question:</b></font></td>\n";
		$editquestion .= "\t\t<td><textarea cols='35' rows='4' name='question'>{$eqrow['question']}</textarea></td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right' valign='top'>$setfont<b>Help:</b></font></td>\n";
		$editquestion .= "\t\t<td><textarea cols='35' rows='4' name='help'>{$eqrow['help']}</textarea></td>\n";
		$editquestion .= "\t</tr>\n";
		//question type:
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Type:</b></font></td>\n";
		if ($activated != "Y")
			{
			$editquestion .= "\t\t<td><select $slstyle name='type' onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n";
			$editquestion .= getqtypelist($eqrow['type']);
			$editquestion .= "\t\t</select></td>\n";
			}
		else
			{
			$editquestion .= "\t\t<td>{$setfont}[{$eqrow['type']}] - Cannot be changed in activated survey.\n";
			$editquestion .= "\t\t\t<input type='hidden' name='type' value='{$eqrow['type']}'>\n";
			$editquestion .= "\t\t</td>\n";
			}
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t<td align='right'>$setfont<b>Group?</b></font></td>\n";
		$editquestion .= "\t\t<td><select $slstyle name='gid'>\n";
		$editquestion .= getgrouplist2($eqrow['gid']);
		$editquestion .= "\t\t</select></td>\n";
		$editquestion .= "\t</tr>\n";
		
		$editquestion .= "\t<tr id='OtherSelection' style='display: none'>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Other?</b></font></td>\n";
		$editquestion .= "\t\t<td>$setfont\n";
		$editquestion .= "\t\t\tYes <input type='radio' name='other' value='Y'";
		if ($eqrow['other'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n";
		$editquestion .= "\t\t\tNo <input type='radio' name='other' value='N'";
		if ($eqrow['other'] == "N") {$editquestion .= " checked";}
		$editquestion .= " />\n";
		$editquestion .= "\t\t</td>\n";
		$editquestion .= "\t</tr>\n";
		
		$editquestion .= "\t<tr id='MandatorySelection' style='display: none'>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Mandatory?</b></font></td>\n";
		$editquestion .= "\t\t<td>$setfont\n";
		$editquestion .= "\t\t\tYes <input type='radio' name='mandatory' value='Y'";
		if ($eqrow['mandatory'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n";
		$editquestion .= "\t\t\tNo <input type='radio' name='mandatory' value='N'";
		if ($eqrow['mandatory'] != "Y") {$editquestion .= " checked";}
		$editquestion .= " />\n";
		$editquestion .= "\t\t</td>\n";
		$editquestion .= "\t</tr>\n";
		
		$editquestion .= "<script type='text/javascript'>\n";
		$editquestion .= "<!--\n";
		$editquestion .= "function OtherSelection(QuestionType)\n";
		$editquestion .= "\t{\n";
		$editquestion .= "\tif (QuestionType == 'M' || QuestionType == 'P')\n";
		$editquestion .= "\t\t{\n";
		$editquestion .= "\t\tdocument.getElementById('OtherSelection').style.display = '';\n";
		$editquestion .= "\t\t}\n";
		$editquestion .= "\telse\n";
		$editquestion .= "\t\t{\n";
		$editquestion .= "\t\tdocument.getElementById('OtherSelection').style.display = 'none';\n";
		$editquestion .= "\t\tdocument.editquestion.other[1].checked = true;\n";
		$editquestion .= "\t\t}\n";
		$editquestion .= "\tif (QuestionType == 'S' || QuestionType == 'T')\n";
		$editquestion .= "\t\t{\n";
		$editquestion .= "\t\tdocument.getElementById('MandatorySelection').style.display = 'none';\n";
		$editquestion .= "\t\tdocument.editquestion.mandatory[1].checked=true;\n";
		$editquestion .= "\t\t}\n";
		$editquestion .= "\telse\n";
		$editquestion .= "\t\t{\n";
		$editquestion .= "\t\tdocument.getElementById('MandatorySelection').style.display = '';\n";
		$editquestion .= "\t\t}\n";
		$editquestion .= "\t}\n";
		$editquestion .= "\tOtherSelection('{$eqrow['type']}');\n";
		$editquestion .= "-->\n";
		$editquestion .= "</script>\n";
		
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td colspan='2' align='center'><input type='submit' $btstyle value='Update Question'></td>\n";
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
		$editanswer .= "\t\t<td><input type='text' value=\"".str_replace('"', "&quot;", $earow['answer'])."\" name='answer'></td></tr>\n";
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
		$editsurvey .= "\t\t<b>$setfont<font color='white'>Edit Survey</font></font></b></td></tr>\n";
		$editsurvey .= "\t<tr><form name='addnewsurvey' action='$scriptname' method='post'>\n";
		$editsurvey .= "\t\t<td align='right'><b>$setfont Short Title:</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='20' name='short_title' value='{$esrow['short_title']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'><b>$setfont Description:</font></b></td>\n";
		$editsurvey .= "\t\t<td><textarea cols='35' rows='5' name='description'>{$esrow['description']}</textarea></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>Welcome Message:</b></font></td>\n";
		$editsurvey .= "\t\t<td><textarea cols='35' rows='5' name='welcome'>".str_replace("<br />", "\n", $esrow['welcome'])."</textarea></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>Administrator:</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='20' name='admin' value='{$esrow['admin']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>Admin Email:</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='20' name='adminemail' value='{$esrow['adminemail']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>Fax To:</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='20' name='faxto' value='{$esrow['faxto']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>Format:</b></font></td>\n";
		$editsurvey .= "\t\t<td><select name='format'>\n";
		$editsurvey .= "\t\t\t<option value='S'";
		if ($esrow['format'] == "S" || !$esrow['format']) {$editsurvey .= " selected";}
		$editsurvey .= ">One at a time</option>\n";
		$editsurvey .= "\t\t\t<option value='G'";
		if ($esrow['format'] == "G") {$editsurvey .= " selected";}
		$editsurvey .= ">Group at a time</option>\n";
		$editsurvey .= "\t\t\t<option value='A'";
		if ($esrow['format'] == "A") {$editsurvey .= " selected";}
		$editsurvey .= ">All in one</option>\n";
		$editsurvey .= "\t\t</select></td>\n";
		$editsurvey .= "\t</tr>\n";

		$editsurvey .= "\t<tr><td align='right'>$setfont<b>Template:</b></font></td>\n";
		$editsurvey .= "\t\t<td><select name='template'>\n";
		foreach (gettemplatelist() as $tname)
			{
			$editsurvey .= "\t\t\t<option value='$tname'";
			if ($esrow['template'] && $tname == $esrow['template']) {$editsurvey .= " selected";}
			elseif (!$esrow['template'] && $tname == "default") {$editsurvey .= " selected";}
			$editsurvey .= ">$tname</option>\n";
			}
		$editsurvey .= "\t\t</select></td>\n";
		$editsurvey .= "\t</tr>\n";
		
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>Anonymous?</b></font></td>\n";
				
		if ($esrow['active'] == "Y")
			{
			$editsurvey .= "\t\t<td>\n\t\t\t$setfont";
			if ($esrow['private'] == "N") {$editsurvey .= " This survey is <b>not</b> anonymous";}
			else {$editsurvey .= "This survey <b>is</b> anonymous";}
			$editsurvey .= "<font size='1' color='red'>&nbsp;(Cannot be changed)\n";
			$editsurvey .= "\t\t</td>\n";
			}
		else
			{
			$editsurvey .= "\t\t<td><select name='private'>\n";
			$editsurvey .= "\t\t\t<option value='Y'";
			if ($esrow['private'] == "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">Yes</option>\n";
			$editsurvey .= "\t\t\t<option value='N'";
			if ($esrow['private'] != "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">No</option>\n";
			$editsurvey .= "</select>\n\t\t</td>\n";
			}
		$editsurvey .= "</tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>Expiry Date:</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='10' name='expires' value='{$esrow['expires']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>End URL:</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='50' name='url' value='{$esrow['url']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>URL Description:</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='50' name='urldescrip' value='{$esrow['urldescrip']}'></td></tr>\n";

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
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>Administrator:</b></font></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='20' name='admin'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>Admin Email:</b></font></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='20' name='adminemail'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>Fax To:</b></font></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='20' name='faxto'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>Anonymous?</b></font></td>\n";
	$newsurvey .= "\t\t<td><select name='private'>\n";
	$newsurvey .= "\t\t\t<option value='Y' selected>Yes</option>\n";
	$newsurvey .= "\t\t\t<option value='N'>No</option>\n";
	$newsurvey .= "\t\t</select></td>\n\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>Format:</b></font></td>\n";
	$newsurvey .= "\t\t<td><select name='format'>\n";
	$newsurvey .= "\t\t\t<option value='S' selected>One at a time</option>\n";
	$newsurvey .= "\t\t\t<option value='G'>Group at a time</option>\n";
	$newsurvey .= "\t\t\t<option value='A'>All in one</option>\n";
	$newsurvey .= "\t\t</select></td>\n";
	$newsurvey .= "\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>Template:</b></font></td>\n";
	$newsurvey .= "\t\t<td><select name='template'>\n";
	foreach (gettemplatelist() as $tname)
		{
		$newsurvey .= "\t\t\t<option value='$tname'";
		if ($esrow['template'] && $tname == $esrow['template']) {$newsurvey .= " selected";}
		elseif (!$esrow['template'] && $tname == "default") {$newsurvey .= " selected";}
		$newsurvey .= ">$tname</option>\n";
		}
	$newsurvey .= "\t\t</select></td>\n";
	$newsurvey .= "\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>Expiry Date:</b></font></td>\n";
	$newsurvey .= "\t\t<td>$setfont<input type='text' size='10' name='expires'><font size='1'>Date Format: YYYY-MM-DD</font></font></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>End URL:</b></font></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='50' name='url' value='{$esrow['url']}'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>URL Description:</b></font></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='50' name='urldescrip' value='{$esrow['urldescrip']}'></td></tr>\n";
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