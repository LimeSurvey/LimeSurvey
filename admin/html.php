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
	$surveysummary = "<TABLE WIDTH='100%' ALIGN='CENTER' BGCOLOR='SILVER' BORDER='0'>\n";
	while ($s1row = mysql_fetch_array($sumresult1))
		{
		$surveysummary .= "<TR><TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Title:</B></TD><TD>$setfont<B><FONT COLOR='#000080'>{$s1row['short_title']} (ID {$s1row['sid']})</FONT></B></TD></TR>\n";
		$surveysummary .= "<TR><TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Description:</B></TD><TD BGCOLOR='#DDDDDD'>$setfont {$s1row['description']}</TD></TR>\n";
		$surveysummary .= "<TR><TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Welcome:</B></TD><TD BGCOLOR='#DDDDDD'>$setfont {$s1row['welcome']}</TD></TR>\n";
		$surveysummary .= "<TR><TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Admin:</B></TD><TD BGCOLOR='#DDDDDD'>$setfont {$s1row['admin']} ({$s1row['adminemail']})</TD></TR>\n";
		if ($s1row['expires'] != "0000-00-00") {$surveysummary .= "<TR><TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Expires:</B></TD><TD BGCOLOR='#DDDDDD'>$setfont {$s1row['expires']}</TD></TR>\n";}
		$activated = $s1row['active'];
		}
	
	$sumquery2 = "SELECT * FROM groups WHERE sid=$sid";
	$sumresult2 = mysql_query($sumquery2);
	$sumcount2 = mysql_num_rows($sumresult2);
	$surveysummary .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Groups:</B></TD><TD BGCOLOR='#DDDDDD'>$setfont";
	if ($groupselect)
		{
		$surveysummary .= "<SELECT $slstyle NAME='groupselect' onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
		$surveysummary .= $groupselect;
		$surveysummary .= "</SELECT>\n";
		}

	$sumquery3 = "SELECT * FROM questions WHERE sid=$sid";
	$sumresult3 = mysql_query($sumquery3);
	$sumcount3 = mysql_num_rows($sumresult3);

	$surveysummary .= " <FONT SIZE='1'>($sumcount2 groups, $sumcount3 questions)</FONT></TD></TR>\n";

	$surveysummary .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Activation</TD><TD VALIGN='TOP' BGCOLOR='#DDDDDD'>$setfont";
	if ($activated == "N" && $sumcount3 > 0)
		{
		$surveysummary .= "<INPUT $btstyle TYPE='SUBMIT' VALUE='Activate' onClick=\"window.open('$scriptname?action=activate&sid=$sid', '_top')\">\n";
		}
	elseif ($activated == "Y")
		{
		$surveysummary .= "<INPUT $btstyle TYPE='SUBMIT' VALUE='De-activate' onClick=\"window.open('$scriptname?action=deactivate&sid=$sid', '_top')\">\n";
		//$surveysummary .= "&nbsp;&nbsp;&nbsp;<FONT SIZE='1'>Survey Table is 'survey_$sid'<BR>";
		$surveysummary .= "<INPUT $btstyle TYPE='SUBMIT' VALUE='Tokens' onClick=\"window.open('tokens.php?sid=$sid', '_top')\">\n";
		}
	else
		{
		$surveysummary .= "&nbsp;&nbsp;&nbsp;<FONT SIZE='1'>Survey cannot yet be activated";
		}
	$surveysummary .= "</TD></TR>\n";
	
	//OPTIONS
	$surveysummary .= "<TR><TD COLSPAN='2' ALIGN='RIGHT'>";
	$surveysummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Export' TITLE='Export Survey Structure..' onClick=\"window.open('dumpsurvey.php?sid=$sid', '_top')\">\n";
	if ($activated == "N") 
		{
		$surveysummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Test DataEntry' onClick=\"window.open('dataentry.php?sid=$sid', '_blank')\">\n";
		$surveysummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Test Survey' onClick=\"window.open('../index.php?sid=$sid', '_blank')\">\n";
		}
	else 
		{
		$surveysummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Browse' onClick=\"window.open('browse.php?sid=$sid', '_top')\">\n";
		$surveysummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='DataEntry' onClick=\"window.open('dataentry.php?sid=$sid', '_blank')\">\n";
		$surveysummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Do Survey' onClick=\"window.open('../index.php?sid=$sid', '_blank')\">\n";
		}
	$surveysummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Edit Survey' onClick=\"window.open('$scriptname?action=editsurvey&sid=$sid', '_top')\">\n";
	if ($activated == "N") {$surveysummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Add Group' onClick=\"window.open('$scriptname?action=addgroup&sid=$sid', '_top')\">\n";}
	if ($sumcount3 == 0 && $sumcount2 == 0) {$surveysummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Delete Survey' onClick=\"window.open('$scriptname?action=delsurvey&sid=$sid', '_top')\">\n";}
	
	$surveysummary .= "</TD></TR>\n";
	
	$surveysummary .= "</TABLE>\n";
	}

if ($gid)
	{
	$grpquery =" SELECT * FROM groups WHERE gid=$gid ORDER BY group_name";
	$grpresult = mysql_query($grpquery);
	$groupsummary = "<TABLE WIDTH='100%' ALIGN='CENTER' BGCOLOR='#DDDDDD' BORDER='0'>\n";
	while ($grow = mysql_fetch_array($grpresult))
		{
		$groupsummary .= "<TR><TD WIDTH='20%' ALIGN='RIGHT'>$setfont<B>Group Title:</TD><TD>$setfont{$grow['group_name']} ({$grow['gid']})</TD></TR>\n";
		if ($grow['description']) {$groupsummary .= "<TR><TD VALIGN='TOP' ALIGN='RIGHT'>$setfont<B>Description:</TD><TD>$setfont{$grow['description']}</TD></TR>\n";}
		}
	$groupsummary .="<TR><TD ALIGN='RIGHT'>$setfont<B>Questions:</TD>";
	$groupsummary .="<TD><SELECT $slstyle NAME='qid' onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
	$groupsummary .= getquestions();
	$groupsummary .= "</SELECT></TD></TR>\n";
	$groupsummary .= "<TR><TD COLSPAN='2' ALIGN='RIGHT'>";
	$groupsummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Edit Group' onClick=\"window.open('$scriptname?action=editgroup&sid=$sid&gid=$gid', '_top')\">\n";
	if ($activated == "N") {$groupsummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Add Question' onClick=\"window.open('$scriptname?action=addquestion&sid=$sid&gid=$gid', '_top')\">\n";}
	$qquery = "SELECT * FROM questions WHERE sid=$sid AND gid=$gid ORDER BY title";
	$qresult = mysql_query($qquery);
	$qcount = mysql_num_rows($qresult);
	if ($qcount == 0) {$groupsummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Delete Group' onClick=\"window.open('$scriptname?action=delgroup&sid=$sid&gid=$gid', '_top')\">";}
	$groupsummary .= "</TD></TR></TABLE>\n";
	}

if ($qid)
	{
	$qrquery = "SELECT * FROM questions WHERE gid=$gid AND sid=$sid AND qid=$qid";
	$qrresult = mysql_query($qrquery);
	$questionsummary = "<TABLE WIDTH='100%' ALIGN='CENTER' BGCOLOR='#EEEEEE' BORDER='0'>\n";
	while ($qrrow = mysql_fetch_array($qrresult))
		{
		$questionsummary .= "<TR><TD WIDTH='20%' ALIGN='RIGHT'>$setfont<B>Question Title:</TD><TD>$setfont{$qrrow['title']}</TD></TR>\n";
		$questionsummary .= "<TR><TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Question:</TD><TD>$setfont{$qrrow['question']}</TD></TR>\n";
		$questionsummary .= "<TR><TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Help:</TD><TD>$setfont{$qrrow['help']}</TD></TR>\n";
		$questionsummary .= "<TR><TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Type:</TD><TD>$setfont{$qrrow['type']}</TD></TR>\n";
		if ($qrrow['type']== "O" || $qrrow['type'] == "L" || $qrrow['type'] == "M" || $qrrow['type'] == "A" || $grrow[3] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "P")
			{
			$questionsummary .= "<TR><TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Answers:</TD>";
			$questionsummary .= "<TD><SELECT $slstyle NAME='answer' onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
			$questionsummary .= getanswers();
			$questionsummary .= "</SELECT></TD></TR>\n";
			}
		$questionsummary .= "<TR><TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Other?</TD><TD>$setfont$qrrow[7]</TD></TR>\n";
		$questionsummary .= "<TR><TD COLSPAN='2' ALIGN='RIGHT'>";
		$questionsummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Edit Question' onClick=\"window.open('$scriptname?action=editquestion&sid=$sid&gid=$gid&qid=$qid', '_top')\">\n";
		if ($qrrow['type'] == "O" || $qrrow['type'] == "L" || $qrrow['type'] == "M" || $qrrow['type']=="A" || $qrrow['type'] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "P") 
			{
			if (($activated == "Y" && $qrrow['type'] == "L") || ($activated == "N"))
				{
				$questionsummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Add Answer' onClick=\"window.open('$scriptname?action=addanswer&sid=$sid&gid=$gid&qid=$qid', '_top')\">\n";
				}
			$qrq = "SELECT * FROM answers WHERE qid=$qid";
			$qrr = mysql_query($qrq);
			$qct = mysql_num_rows($qrr);
			if ($qct == 0)
				{
				$questionsummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Delete Question' onClick=\"window.open('$scriptname?action=delquestion&sid=$sid&gid=$gid&qid=$qid', '_top')\">";
				}
			}
		else {$questionsummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Delete Question' onClick=\"window.open('$scriptname?action=delquestion&sid=$sid&gid=$gid&qid=$qid', '_top')\">";}
		if ($activated == "N") {$questionsummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Copy Question' onClick=\"window.open('$scriptname?action=copyquestion&sid=$sid&gid=$gid&qid=$qid', '_top')\">\n";}
		$questionsummary .= "</TD></TR>";
		}
	$questionsummary .= "</TABLE>\n";
	}

if ($code)
	{
	$cdquery = "SELECT * FROM answers WHERE qid=$qid AND code='$code'";
	$cdresult = mysql_query($cdquery);
	$answersummary = "<TABLE WIDTH='100%' ALIGN='CENTER' BORDER='0'>\n";
	while ($cdrow = mysql_fetch_array($cdresult))
		{
		$answersummary .= "<TR><TD WIDTH='20%' ALIGN='RIGHT'>$setfont<B>Code:</TD><TD>$setfont{$cdrow['code']}</TD></TR>\n";
		$answersummary .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Answer:</TD><TD>$setfont{$cdrow['answer']}</TD></TR>\n";
		$answersummary .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Default?</TD><TD>$setfont{$cdrow['default']}</TD></TR>\n";
		}
	$answersummary .= "<TR><TD ALIGN='RIGHT' COLSPAN='2'>";
	$answersummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Delete Answer' onClick=\"window.open('$scriptname?action=delanswer&sid=$sid&gid=$gid&qid=$qid&code=$code', '_top')\">\n";
	$answersummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Edit Answer' onClick=\"window.open('$scriptname?action=editanswer&sid=$sid&gid=$gid&qid=$qid&code=$code', '_top')\">\n";
	$answersummary .= "</TD></TR>\n";
	$answersummary .= "</TABLE>\n";
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
	$usersummary = "<TABLE WIDTH='100%' BORDER='0'><TR><TD COLSPAN='3' BGCOLOR='BLACK' ALIGN='CENTER'>";
	$usersummary .= "<B>$setfont<FONT COLOR='WHITE'>Modify User</TD></TR>\n";
	$muq = "SELECT * FROM users WHERE user='$user' LIMIT 1";
	$mur = mysql_query($muq);
	$usersummary .= "<TR><FORM ACTION='$scriptname' METHOD='POST'>";
	while ($mrw = mysql_fetch_array($mur))
		{
		$usersummary .= "<TD>$setfont<B>$mrw[0]</TD>";
		$usersummary .= "<INPUT TYPE='HIDDEN' NAME='user' VALUE='{$mrw['user']}'>";
		$usersummary .= "<TD><INPUT TYPE='text' NAME='pass' VALUE='{$mrw['password']}'></TD>";
		$usersummary .= "<TD><INPUT TYPE='text' SIZE='2' NAME='level' VALUE='{$mrw['security']}'></TD>";
		}
	$usersummary .= "</TR>\n<TR><TD COLSPAN='3' ALIGN='CENTER'>";
	$usersummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Update'></TD></TR>\n";
	$usersummary .= "<INPUT TYPE='HIDDEN' NAME='action' VALUE='moduser'>\n";
	$usersummary .= "</FORM></TABLE>\n";
	}

if ($action == "editusers")
	{
	if (!file_exists("$homedir/.htaccess"))
		{
		$usersummary = "<TABLE WIDTH='100%' BORDER='0'><TR><TD BGCOLOR='BLACK' ALIGN='CENTER'>";
		$usersummary .= "<B>$setfont<FONT COLOR='WHITE'>Security Control</TD></TR>\n";
		$usersummary .= "<TR><TD>$setfont<FONT COLOR='RED'><B>Warning:</FONT></B><BR>";
		$usersummary .= "You have not yet initialised security settings for your survey system ";
		$usersummary .= "and subsequently there are no restrictions on access.<P>";
		$usersummary .= "If you click on the 'initialise security' button below, standard APACHE ";
		$usersummary .= "security settings will be added to the administration directory of this ";
		$usersummary .= "script. You will then need to use the default access username and password ";
		$usersummary .= "to access the administration and dataentry scripts.<P>";
		$usersummary .= "USERNAME: $defaultuser<BR>PASSWORD: $defaultpass<P>";
		$usersummary .= "It is highly recommended that once your security system has been initialised ";
		$usersummary .= "you change this default password.</TD></TR>\n";
		$usersummary .= "<TR><TD ALIGN='CENTER'><INPUT TYPE='SUBMIT' $btstyle VALUE='Initialise Security' onClick=\"window.open('$scriptname?action=setupsecurity', '_top')\"></TD></TR>\n";
		$usersummary .= "</TABLE>\n";
		}
	else
		{
		$usersummary = "<TABLE WIDTH='100%' BORDER='0'><TR><TD COLSPAN='4' BGCOLOR='BLACK' ALIGN='CENTER'>";
		$usersummary .= "<B>$setfont<FONT COLOR='WHITE'>List of users</TD></TR>\n";
		$usersummary .= "<TR BGCOLOR='#444444'><TD>$setfont<FONT COLOR='WHITE'><B>User</TD><TD>$setfont<FONT COLOR='WHITE'><B>Password</TD><TD>$setfont<FONT COLOR='WHITE'><B>Security</TD><TD>$setfont<FONT COLOR='WHITE'><B>Actions</TD></TR>\n";
		$userlist = getuserlist();
		$ui = count($userlist);
		if ($ui < 1) {$usersummary .= "<CENTER>WARNING: No users exist in your table. We recommend you 'turn off' security. You can then 'turn it on' again.";}
		else
			{
			foreach ($userlist as $usr)
				{
				$usersummary .= "<TR><TD>$setfont<B>$usr[0]</TD><TD>$setfont$usr[1]</TD><TD>$setfont$usr[2]</TD>";
				$usersummary .= "<TD><INPUT TYPE='SUBMIT' $btstyle VALUE='Edit' onClick=\"window.open('$scriptname?action=modifyuser&user=$usr[0]', '_top')\">";
				if ($ui > 1 ) {$usersummary .= "<INPUT TYPE='SUBMIT' $btstyle VALUE='Del' onClick=\"window.open('$scriptname?action=deluser&user=$usr[0]', '_top')\">";}
				$usersummary .= "</TD></TR>\n";
				$ui++;
				}
			}
		$usersummary .= "<TR BGCOLOR='#EEEFFF'><FORM ACTION='$scriptname' METHOD='POST'><TD>";
		$usersummary .= "<INPUT TYPE='TEXT' $slstyle NAME='user'></TD>";
		$usersummary .= "<TD><INPUT TYPE='TEXT' $slstyle NAME='pass'></TD>";
		$usersummary .= "<TD><INPUT TYPE='TEXT' $slstyle NAME='level' SIZE='2'></TD>";
		$usersummary .= "<TD><INPUT TYPE='SUBMIT' $btstyle VALUE='Add New User'></TD></TR>\n";
		$usersummary .= "<INPUT TYPE='HIDDEN' NAME='action' VALUE='adduser'></FORM>\n";
		$usersummary .= "<TR><TD COLSPAN='3'></TD><TD><INPUT TYPE='SUBMIT' $btstyle VALUE='Turn Off Security' ";
		$usersummary .= "onClick=\"window.open('$scriptname?action=turnoffsecurity', '_top')\"></TD></TR>\n";		
		$usersummary .= "</TABLE>\n";
		}
	}
if ($action == "addquestion")
	{
	$newquestion = "<TABLE WIDTH='100%' BORDER='0'><TR><TD COLSPAN='2' BGCOLOR='BLACK' ALIGN='CENTER'>";
	$newquestion .= "<B>$setfont<FONT COLOR='WHITE'>Create New Question for Survey ID($sid), Group ID($gid) </B></TD></TR>\n";
	$newquestion .= "<TR><FORM ACTION='$scriptname' NAME='addnewquestion' METHOD='POST'>\n";
	$newquestion .= "<TD ALIGN='RIGHT'>$setfont<B>Question Code:</TD>";
	$newquestion .= "<TD><INPUT TYPE='TEXT' SIZE='20' NAME='title'></TD></TR>\n";
	$newquestion .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Question:</TD>";
	$newquestion .= "<TD><TEXTAREA COLS='35' ROWS='3' NAME='question'></TEXTAREA></TD></TR>\n";
	$newquestion .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Help:</TD>";
	$newquestion .= "<TD><TEXTAREA COLS='35' ROWS='3' NAME='help'></TEXTAREA></TD></TR>\n";
	$newquestion .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Question Type:</TD>";
	$newquestion .= "<TD><SELECT $slstyle NAME='type'>";
	$newquestion .= "$qtypeselect";
	$newquestion .= "</SELECT></TD></TR>\n";
	$newquestion .= "<TR><TD ALIGN='RIGHT'>$setfont<B>Other?</TD>";
	$newquestion .= "<TD><INPUT TYPE='TEXT' SIZE='1' VALUE='N' NAME='other'></TD></TR>\n";
	$newquestion .= "<TR><TD COLSPAN='2' ALIGN='CENTER'><INPUT TYPE='SUBMIT' $btstyle VALUE='Add Question'></TD></TR>\n";
	$newquestion .= "<INPUT TYPE='HIDDEN' NAME='action' VALUE='insertnewquestion'>\n";
	$newquestion .= "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
	$newquestion .= "<INPUT TYPE='HIDDEN' NAME='gid' VALUE='$gid'>\n";
	$newquestion .= "</FORM></TABLE>\n";
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