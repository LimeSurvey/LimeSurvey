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

# TOKENS FILE

include("config.php");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0

echo $htmlheader;
echo "<table width='100%'>\n";
echo "\t<tr><td bgcolor='#BBBBBB' colspan='2' align='center'>$setfont<b>Token Control</b></td></tr>\n";

// MAKE SURE THAT THERE IS A SID
if (!$_GET['sid'] && !$_POST['sid'])
	{
	echo "\t<tr><td colspan='2' align='center'>$setfont<br /><br />Sorry, you have not chosen a survey_id!</td></tr>\n";
	echo "</table>\n";
	exit;
	}
else
	{
	$sid = $_GET['sid'];
	if (!$sid) {$sid = $_POST['sid'];}
	}
// MAKE SURE THAT THE SURVEY EXISTS
$chquery = "SELECT * FROM surveys WHERE sid=$sid";
if (!$chresult = mysql_query($chquery))
	{
	echo "\t<tr><td colspan='2' align='center'>$setfont<br /><br />Sorry, this survey does not exist</td></tR>\n";
	echo "</table>\n";
	exit;
	}
while ($chrow = mysql_fetch_array($chresult))
	{
	echo "\t<tr><td colspan='2' align='center'>$setfont<b>Survey $sid - {$chrow['short_title']}</td></tr>\n";
	$surveyprivate = $chrow['private'];
	}

// CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
$tkquery = "SELECT * FROM tokens_$sid";
if (!$tkresult = mysql_query($tkquery))
	{
	if (!$createtable)
		{
		echo "\t<tr>\n";
		echo "\t\t<td colspan='2' align='center'>\n";
		echo "\t\t\t$setfont<br /><br />\n";
		echo "\t\t\tNo token system has been created for this survey. Do you want to create one now?<br /><br />\n";
		echo "\t\t\t<input type='submit' $btstyle value='Create Token Table' onClick=\"window.open('tokens.php?sid=$sid&createtable=Y', '_top')\"><br />\n";
		echo "\t\t\t<input type='submit' $btstyle value='Return to Admin' onClick=\"window.open('admin.php?sid=$sid', '_top')\">\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "</table>\n";
		exit;
		}
	else
		{
		$createtokentable = "CREATE TABLE tokens_$sid (\n  tid int NOT NULL auto_increment,\n  firstname varchar(40) NULL,  lastname varchar(40) NULL,\n  email varchar(100) NULL,  token varchar(10) NULL,\n sent varchar(1) NULL DEFAULT 'N', completed varchar(1) NULL DEFAULT 'N',  PRIMARY KEY (tid)\n) TYPE=MyISAM;";
		$ctresult = mysql_query($createtokentable) or die ("Completely mucked up<br />$createtokentable<br /><br />".mysql_error());
		echo "\t<tr>\n";
		echo "\t\t<td colspan='2' align='center'>\n";
		echo "\t\t\t$setfont<br /><br />\n";
		echo "\t\t\tA token table has been created for this survey, called \"tokens_$sid\"<br /><br />\n";
		echo "\t\t\t<input type='submit' $btstyle value='Continue' onClick=\"window.open('tokens.php?sid=$sid', '_top')\">\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "</table>\n";
		exit;
		
		}
	}

// IF WE MADE IT THIS FAR, THEN THERE IS A TOKENS TABLE, SO LETS DEVELOP THE MENU ITEMS

$tokenmenu = "\t<tr>\n";
$tokenmenu .= "\t\t<td bgcolor='#EEEEEE' colspan='2' align='center' ";
$tokenmenu .= "style='border-top-style: solid; border-top-width: 1; border-top-color:#BBBBBB; border-left-color:#BBBBBB; border-left-width:1; border-left-style:solid; border-right-style: solid; border-right-width:1; border-right-color:#BBBBBB'>\n";
$tokenmenu .= "\t\t\t<font size='2'>[<a href='admin.php?sid=$sid'>admin</a>] \n";
$tokenmenu .= "\t\t\t[<a href='tokens.php?sid=$sid'>summary</a>] \n";
$tokenmenu .= "\t\t\t[<a href='tokens.php?sid=$sid&action=browse'>browse</a>] \n";
$tokenmenu .= "\t\t\t[<a href='tokens.php?sid=$sid&action=add'>add</a>] \n";
$tokenmenu .= "\t\t\t[<a href='tokens.php?sid=$sid&action=import'>import</a>] \n";
$tokenmenu .= "\t\t\t[<a href='tokens.php?sid=$sid&action=email'>invite</a>] \n";
$tokenmenu .= "\t\t\t[<a href='tokens.php?sid=$sid&action=remind'>remind</a>] \n";
$tokenmenu .= "\t\t\t[<a href='tokens.php?sid=$sid&action=tokenify'>tokenify</a>] \n";
$tokenmenu .= "\t\t\t[<a href='tokens.php?sid=$sid&action=kill'>drop&nbsp;tokens</a>] \n";
$tokenmenu .= "\t\t</td>\n";
$tokenmenu .= "\t</tr>\n";

// SEE HOW MANY RECORDS ARE IN THE TOKEN TABLE
$tkcount = mysql_num_rows($tkresult);
echo "$tokenmenu";
echo "\t<tr><td colspan='2' align='center'>There are $tkcount records in your token table for this survey.</td></tr>\n";

// GIVE SOME INFORMATION ABOUT THE TOKENS
echo "\t<tr>\n";
echo "\t\t<td colspan='2' align='center'>\n";
echo "\t\t\t<table width='400' align='center' bgcoloR='#DDDDDD'>\n";
echo "\t\t\t\t<tr>\n";
echo "\t\t\t\t\t<td align='center'>\n";
$tksq = "SELECT count(*) FROM tokens_$sid WHERE sent='Y'";
$tksr = mysql_query($tksq);
while ($tkr = mysql_fetch_row($tksr))
	{echo "\t\t\t\t\t\t$setfont$tkr[0] of $tkcount have been sent an invitation to participate<br />\n";}
$tksq = "SELECT count(*) FROM tokens_$sid WHERE completed='Y'";
$tksr = mysql_query($tksq);
while ($tkr = mysql_fetch_row($tksr))
	{echo "\t\t\t\t\t\t$setfont$tkr[0] of $tkcount entries have completed the survey<br />\n";}
$tksq = "SELECT count(*) FROM tokens_$sid WHERE token IS NULL OR token=''";
$tksr = mysql_query($tksq);
while ($tkr = mysql_fetch_row($tksr))
	{echo "\t\t\t\t\t\t$setfont$tkr[0] of $tkcount have not had a token generated\n";}
echo "\t\t\t\t\t</td>\n";
echo "\t\t\t\t</tr>\n";
echo "\t\t\t</table>\n";




if ($action == "browse")
	{
	echo "<br />\n<table width='600' cellpadding='1' cellspacing='1' align='center' bgcolor='#CCCCCC'>\n";
	//COLUMN HEADINGS
	echo "\t<tr>\n";
	echo "\t\t<td><a href='tokens.php?sid=$sid&action=browse&order=tid'><img src='DownArrow.gif' border='0' align='left'></a>$setfont<b>ID</b></td>\n";
	echo "\t\t<td><a href='tokens.php?sid=$sid&action=browse&order=firstname'><img src='DownArrow.gif' border='0' align='left'></a>$setfont<b>First</b></td>\n";
	echo "\t\t<td><a href='tokens.php?sid=$sid&action=browse&order=lastname'><img src='DownArrow.gif' border='0' align='left'></a>$setfont<b>Last</b></td>\n";
	echo "\t\t<td><a href='tokens.php?sid=$sid&action=browse&order=email'><img src='DownArrow.gif' border='0' align='left'></a>$setfont<b>Email</b></td>\n";
	echo "\t\t<td><a href='tokens.php?sid=$sid&action=browse&order=token'><img src='DownArrow.gif' border='0' align='left'></a>$setfont<b>Token</b></td>\n";
	echo "\t\t<td><a href='tokens.php?sid=$sid&action=browse&order=sent%20desc'><img src='DownArrow.gif' border='0' align='left'></a>$setfont<b>Invite?</b></td>\n";
	echo "\t\t<td><a href='tokens.php?sid=$sid&action=browse&order=completed%20desc'><img src='DownArrow.gif' border='0' align='left'></a>$setfont<b>Done?</b></td>\n";
	echo "\t\t<td>$setfont<b>Action</b></td>\n";
	echo "\t</tr>\n";
	$bquery = "SELECT * FROM tokens_$sid";
	if (!$order) {$bquery .= " ORDER BY tid";}
	else {$bquery .= " ORDER BY $order";}
	$bresult = mysql_query($bquery);
	while ($brow = mysql_fetch_array($bresult))
		{
		if ($bgc == "#EEEEEE") {$bgc = "#DDDDDD";} else {$bgc = "#EEEEEE";}
		echo "\t<tr bgcolor='$bgc'>\n";
		for ($i=0; $i<=6; $i++)
			{
			echo "\t\t<td>$setfont$brow[$i]</td>\n";
			}
		echo "\t\t<td align='left'>\n";
		echo "\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-face: verdana' type='submit' value='E' title='Edit' onClick=\"window.open('$PHP_SELF?sid=$sid&action=edit&tid=$brow[0]', '_top')\">";
		echo "&nbsp;<input style='height: 16; width: 16px; font-size: 8; font-face: verdana' type='submit' value='D' title='Delete' onClick=\"window.open('$PHP_SELF?sid=$sid&action=delete&tid=$brow[0]', '_top')\">";
		if ($brow['completed'] != "Y" && $brow['token']) {echo "&nbsp;<input style='height: 16; width: 16px; font-size: 8; font-face: verdana' type='submit' value='S' title='Do Survey' onClick=\"window.open('$publicurl/index.php?sid=$sid&token={$brow['token']}', '_blank')\">";}
		echo "\n\t\t</td>\n";
		if ($brow['completed'] == "Y" && $surveyprivate == "N")
			{
			echo "\t\t<form action='browse.php' method='post' target='_blank'>\n";
			echo "\t\t<td align='center'>\n";
			echo "\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-face: verdana' type='submit' value='V' title='View Response'>\n";
			echo "\t\t</td>\n";
			echo "\t\t<input type='hidden' name='sid' value='$sid'>\n";
			echo "\t\t<input type='hidden' name='action' value='id'>\n";
			echo "\t\t<input type='hidden' name='sql' value=\"token='{$brow['token']}'\">\n";
			echo "\t\t</form>\n";
			}
		elseif ($brow['completed'] != "Y" && $brow['token'])
			{
			echo "\t\t<td align='center'>\n";
			echo "\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-face: verdana' type='submit' value='R' title='Send Reminder Email' onClick=\"window.open('$PHP_SELF?sid=$sid&action=remind&tid=$brow[0]', '_top')\">";
			echo "\t\t</td>\n";
			}
		else
			{
			echo "\t\t<td>\n";
			echo "\t\t</td>\n";
			}
		echo "\t</tr>\n";
		}
	echo "</table>\n";
	}

if ($action == "kill")
	{
	$date = date(Ymd);
	echo "<CENTER>$setfont<B>Drop/Delete Tokens</B><br />\n";
	if (!$ok)
		{
		echo "<TABLE WIDTH='80%' ALIGN='CENTER' BGCOLOR='#DDDDDD'>\n";
		echo " <TR><TD ALIGN='CENTER'>";
		echo "Deleting this tokens table will mean that tokens are no longer<br />";
		echo "required for public access to this survey. It will also delete<br />";
		echo "all the existing tokens in this survey. A backup of this table<br />";
		echo "will be made, and called \"old_tokens_$sid\". This can be<br />";
		echo "recovered by a systems administrator.<br /><br />";
		echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='Delete Tokens' onClick=\"window.open('tokens.php?sid=$sid&action=kill&ok=surething', '_top')\"><br />\n";
		echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='Cancel' onClick=\"window.open('tokens.php?sid=$sid', '_top')\">";
		echo " </TD></TR></TABLE>\n";
		}
	elseif ($ok == "surething")
		{
		$oldtable = "tokens_{$sid}";
		$newtable = "old_tokens_{$sid}_{$date}";
		$deactivatequery = "RENAME TABLE $oldtable TO $newtable";
		$deactivateresult = mysql_query($deactivatequery) or die ("Couldn't deactivate because:<br />".mysql_error()."<br /><br /><a href='$scriptname?sid=$sid'>Admin</a>");
		echo "<TABLE WIDTH='80%' ALIGN='CENTER' BGCOLOR='#DDDDDD'>\n";
		echo " <TR><TD ALIGN='CENTER'>";
		echo "The tokens table has now been removed and tokens are no longer<br />";
		echo "required for public access to this survey. A backup of this table<br />";
		echo "has been made, and is called \"old_tokens_$sid_$date\". This can be<br />";
		echo "recovered by a systems administrator.<br /><br />";
		echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='Finished' onClick=\"window.open('tokens.php?sid=$sid', '_top')\">";
		echo " </TD></TR></TABLE>\n";
			
		}		
	}	


if ($_GET['action'] == "email" || $_POST['action'] == "email")
	{
	echo "<CENTER>$setfont<B>Email Invitation</B><br />\n";
	if (!$_POST['ok'])
		{
		//GET SURVEY DETAILS
		$esquery = "SELECT * FROM surveys WHERE sid=$sid";
		$esresult = mysql_query($esquery);
		while ($esrow = mysql_fetch_array($esresult))
			{
			$surveyname = $esrow['short_title'];
			$surveydescription = $esrow['description'];
			$surveyadmin = $esrow['admin'];
			$surveyadminemail = $esrow['adminemail'];
			}
		echo "<TABLE WIDTH='80%' ALIGN='CENTER' BGCOLOR='#DDDDDD'>\n";
		echo "<FORM METHOD='POST'>\n";
		//echo "<FORM>\n";
		echo " <TR><TD COLSPAN='2' BGCOLOR='BLACK' ALIGN='CENTER'>$setfont<FONT COLOR='WHITE'><B>Send Invitation</TD></TR>\n";
		echo " <TR>\n";
		echo "  <TD ALIGN='RIGHT'>$setfont<B>From:</TD>\n";
		echo "  <TD><INPUT TYPE='TEXT' $slstyle SIZE='50' NAME='from' VALUE='$surveyadminemail'></TD></TR>\n";
		echo " <TR>\n  <TD ALIGN='RIGHT'>$setfont<B>Subject:</TD>\n";
		echo "  <TD><INPUT TYPE='TEXT' $slstyle SIZE='50' NAME='subject' VALUE='Invitation to participate in $surveyname survey'></TD></TR>\n";
		echo " <TR>\n  <TD ALIGN='RIGHT' VALIGN='TOP'>$setfont<B>Message:</TD>\n";
		echo "  <TD>$setfont<B>The following will be added to the top of your message:</B>";
		echo "<TABLE WIDTH='500' BGCOLOR='#EEEEEE' BORDER='1' CELLPADDING='0' CELLSPACING='0'><TR><TD>";
		echo "$setfont Dear [FIRSTNAME],";
		echo "</TD></TR></TABLE>\n";
		echo "<B>You can make changes to this part of the message:</B><br />";
		echo "<TEXTAREA NAME='message' ROWS='6' COLS='60'>";
		echo "You have been invited to participate in the following survey.\n\n";
		echo "** Survey Name **\n$surveyname\n\n";
		echo "** Survey Description **\n$surveydescription\n\n";
		echo "To participate, please click on the link below.";
		echo "\n\nSincerely,\n\n$surveyadmin ($surveyadminemail)";
		echo "</TEXTAREA></TD></TR>\n";
		echo " <TR>\n  <TD></TD>";
		echo "  <TD>$setfont<B>The following will be added to the end of your email message:</B><br />";
		echo "<TABLE WIDTH='500' BGCOLOR='#EEEEEE' BORDER='1' CELLPADDING='0' CELLSPACING='0'><TR><TD>";
		echo "$setfont---------------------------------<br /> Click Here to do Survey:<br />$publicurl/index.php?sid=$sid&token=[TOKENVALUE]<br />";
		echo "</TD></TR></TABLE>\n</TD></TR>\n";
		echo " <TR>";
		echo "  <TD COLSPAN='2' ALIGN='CENTER'><INPUT TYPE='SUBMIT' $btstyle VALUE='Send Invitations'></TD></TR>\n";
		echo "<INPUT TYPE='HIDDEN' NAME='ok' VALUE='absolutely'>\n";
		echo "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='{$_GET['sid']}'>\n";
		echo "<INPUT TYPE='HIDDEN' NAME='action' VALUE='email'\n";
		echo "</FORM></TABLE>\n";
		}
	else
		{
		echo "Sending email!";
		$ctquery = "SELECT firstname FROM tokens_{$_POST['sid']} WHERE completed !='Y' AND sent !='Y' AND token !=''";
		$ctresult = mysql_query($ctquery);
		$ctcount = mysql_num_rows($ctresult);
		$emquery = "SELECT firstname, lastname, email, token, tid FROM tokens_{$_POST['sid']} WHERE completed != 'Y' AND sent != 'Y' AND token !='' LIMIT $maxemails";
		$emresult = mysql_query($emquery) or die ("Couldn't do query.<br />$emquery<br />".mysql_error());
		$emcount = mysql_num_rows($emresult);
		$headers = "From: {$_POST['from']}\r\n";
		$headers .= "X-Mailer: $sitename Email Inviter";  
		$message = strip_tags($_POST['message']);
		$message = str_replace("&quot;", '"', $message);
		if (get_magic_quotes_gpc() != "0")
			{$message = stripcslashes($message);}
		echo "<TABLE WIDTH='500' ALIGN='CENTER' BGCOLOR='#EEEEEE'><TR><TD><FONT SIZE='1'>\n";
		if ($emcount > 0)
			{
			while ($emrow = mysql_fetch_array($emresult))
				{
				$to = $emrow['email'];
				//echo "To: $to ({$emrow['firstname']} {$emrow['lastname']})<br />";
				//$from = $surveyadminemail;
				//echo "From: $from<br />";
				//echo "Subject: $subject<br />";
				$sendmessage = "Dear {$emrow['firstname']},\n\n".$message;
				$sendmessage .= "\n\n-------------------------------------------\n\n";
				$sendmessage .= "Click here to do this survey:\n\n";
				$sendmessage .= "$publicurl/index.php?sid=$sid&token={$emrow['token']}\n\n";
				//echo "Message:". str_replace("\n", "<br />", $sendmessage) . "<p>";
				mail($to, $_POST['subject'], $sendmessage, $headers);
				$udequery = "UPDATE tokens_{$_POST['sid']} SET sent='Y' WHERE tid={$emrow['tid']}";
				$uderesult = mysql_query($udequery) or die ("Couldn't update tokens<br />$udequery<br />".mysql_error());
				echo "[Invite Sent to {$emrow['firstname']} {$emrow['lastname']}]<br />\n";
				}
			if ($ctcount > $emcount)
				{
				$lefttosend = $ctcount-$maxemails;
				echo "</TD></TR><TR><FORM METHOD='POST'><TD ALIGN='CENTER'>$setfont<B>Warning:</B><br />";
				echo "The number of emails to send ($ctcount) is greater than the maximum number";
				echo " of emails that can be sent in one lot ($maxemails). There are still $lefttosend";
				echo " emails to go. You can continue sending the next $maxemails by clicking on the";
				echo " button below.<br />";
				$message = str_replace('"', "&quot;", $message);
				echo "<INPUT TYPE='SUBMIT' VALUE=\"Send More\"></TD>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='ok' VALUE=\"absolutely\">\n";
				echo "<INPUT TYPE='HIDDEN' NAME='action' VALUE=\"email\">\n";
				echo "<INPUT TYPE='HIDDEN' NAME='sid' VALUE=\"{$_POST['sid']}\">\n";
				echo "<INPUT TYPE='HIDDEN' NAME='from' VALUE=\"{$_POST['from']}\">\n";
				echo "<INPUT TYPE='HIDDEN' NAME='subject' VALUE=\"{$_POST['subject']}\">\n";
				echo "<INPUT TYPE='HIDDEN' NAME='message' VALUE=\"$message\">\n";
				echo "</FORM>\n";
				}
			}
		else
			{
			echo "<CENTER><B>WARNING:</B><br />There were no token recipients who have not already had";
			echo " an invitation sent out, or who have not responded!<br /><br />";
			echo "No invitations have been sent out!";
			}
		
		echo "</TD></TR></TABLE>\n";
		}
	}	
	
if ($_GET['action'] == "remind" || $_POST['action'] == "remind")
	{
	echo "<center>$setfont<b>Email Reminder</b><br />\n";
	if (!$_POST['ok'])
		{
		//GET SURVEY DETAILS
		$esquery = "SELECT * FROM surveys WHERE sid=$sid";
		$esresult = mysql_query($esquery);
		while ($esrow = mysql_fetch_array($esresult))
			{
			$surveyname = $esrow['short_title'];
			$surveydescription = $esrow['description'];
			$surveyadmin = $esrow['admin'];
			$surveyadminemail = $esrow['adminemail'];
			}
		echo "<table width='80%' align='center' bgcolor='#DDDDDD'>\n";
		echo "\t<form method='post' action='tokens.php'>\n";
		echo "\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
		echo "\t\t$setfont<font color='white'><b>Send Reminder\n";
		if ($_GET['tid']) {echo " to TokenID No {$_GET['tid']}";}
		echo "\t</td></tr>\n";
		echo "\t<tr><td align='right'>\n";
		echo "\t\t$setfont<b>From:</td>\n";
		echo "\t\t<td><input type='text' $slstyle size='50' name='from' value='$surveyadminemail'>\n";
		echo "\t</td></tr>\n";
		echo "\t<tr><td align='right'>\n\t\t$setfont<b>Subject:\n\t</td>\n\t<td>\n";
		echo "\t\t<input type='text' $slstyle size='50' name='subject' value='Reminder to participate in $surveyname'>\n";
		echo "\t</td></tr>\n";
		if (!$_GET['tid'])
			{
			echo "\t<tr><td align='right' valign='top'>\n\t\t$setfont<b>Start at ID:\n\t</td>\n";
			echo "\t\t<td><input type='text' $slstyle size='5' name='last_tid'>\n";
			echo "\t</td></tr>\n";
			}
		echo "\t<tr><td align='right' valign='top'>\n\t\t$setfont<b>Message:\n\t</td>\n";
		echo "\t<td>\n\t\t$setfont<b>The following will be added to the top of your message:</b>\n";
		echo "\t\t\t<table width='500' bgcolor='#EEEEEE' border='1' cellpadding='0' cellspacing='0'>\n\t\t\t\t<tr><td>\n";
		echo "\t\t\t\t\t$setfont Dear [FIRSTNAME],\n";
		echo "\t\t\t\t</td></tr>\n\t\t\t</table>\n";
		echo "\t\t\t<b>You can make changes to this part of the message:</b><br />\n";
		echo "\t\t\t<textarea name='message' rows='6' cols='60'>";
		echo "Recently we invited you to participate in a survey.\n\n";
		echo "We note that you have not yet completed the survey, and wish to remind you ";
		echo "that the survey is still available should you wish to take part.\n\n";
		echo "** Survey Name **\n$surveyname\n\n";
		echo "** Survey Description **\n";
		echo strip_tags($surveydescription)."\n\n";
		echo "To participate, please click on the link below.";
		echo "\n\nSincerely,\n\n$surveyadmin ($surveyadminemail)";
		echo "</textarea>\n\t\t</td></tr>\n";
		echo "\t\t<tr>\n\t\t<td></td>";
		echo "\t\t<td>$setfont<b>The following will be added to the end of your email message:</b><br />\n";
		echo "\t\t\t<table width='500' bgcolor='#EEEEEE' border='1' cellpadding='0' cellspacing='0'>\n";
		echo "\t\t\t\t<tr><td>";
		echo "$setfont---------------------------------<br /> Click Here to do Survey:<br />$publicurl/index.php?sid=$sid&token=[TOKENVALUE]<br />\n";
		echo "\t\t\t\t</td></tr>\n\t\t\t</table>\n\t\t</td></tr>\n";
		echo "\t\t<tr><td colspan='2' align='center'>\n";
		echo "\t\t\t<input type='submit' $btstyle value='Send Reminder'>\n\t\t</td></tr>\n";
		echo "\t\t<input type='hidden' name='ok' value='absolutely'>\n";
		echo "\t\t<input type='hidden' name='sid' value='{$_GET['sid']}'>\n";
		echo "\t\t<input type='hidden' name='action' value='remind'>\n";
		if ($_GET['tid']) {echo "\t\t<input type='hidden' name='tid' value='{$_GET['tid']}'>\n";}
		echo "\t</form>\n</table>\n";
		}
	else
		{
		echo "Sending reminder email!";
		if ($_POST['last_tid']) {echo " (Starting after {$_POST['last_tid']})";}
		if ($_POST['tid']) {echo " (Sending just to TokenID {$_POST['tid']})";}
		$ctquery = "SELECT firstname FROM tokens_{$_POST['sid']} WHERE completed !='Y' AND sent='Y' AND token !=''";
		if ($_POST['last_tid']) {$ctquery .= " AND tid > '{$_POST['last_tid']}'";}
		if ($_POST['tid']) {$ctquery .= " AND tid = '{$_POST['tid']}'";}
		$ctresult = mysql_query($ctquery);
		$ctcount = mysql_num_rows($ctresult);
		$emquery = "SELECT firstname, lastname, email, token, tid FROM tokens_{$_POST['sid']} WHERE completed != 'Y' AND sent = 'Y' AND token !=''";
		if ($_POST['last_tid']) {$emquery .= " AND tid > '{$_POST['last_tid']}'";}
		if ($_POST['tid']) {$emquery .= " AND tid = '{$_POST['tid']}'";}
		$emquery .= " ORDER BY tid LIMIT $maxemails";
		$emresult = mysql_query($emquery) or die ("Couldn't do query.<br />$emquery<br />".mysql_error());
		$emcount = mysql_num_rows($emresult);
		$headers = "From: {$_POST['from']}\r\n";
		$headers .= "X-Mailer: $sitename Email Reminder";  
		echo "\n<table width='500' align='CENTER' bgcolor='#EEEEEE'><tr><td><font size='1'>\n";
		$message = strip_tags($_POST['message']);
		$message = str_replace("&quot;", '"', $message);
		if (get_magic_quotes_gpc() != "0")
			{$message = stripcslashes($message);}
		if ($emcount > 0)
			{
			while ($emrow = mysql_fetch_array($emresult))
				{
				$to = $emrow['email'];
				$sendmessage = "Dear {$emrow['firstname']},\n\n";
				$sendmessage .= $message;
				$sendmessage .= "\n\n-------------------------------------------\n\n";
				$sendmessage .= "Click here to do this survey:\n\n";
				$sendmessage .= "$publicurl/index.php?sid={$_POST['sid']}&token={$emrow['token']}\n\n";
				mail($to, $_POST['subject'], $sendmessage, $headers);
				echo "({$emrow['tid']})[Reminder Sent to {$emrow['firstname']} {$emrow['lastname']}]<br />\n";
				$lasttid = $emrow['tid'];
				}
			if ($ctcount > $emcount)
				{
				$lefttosend = $ctcount-$maxemails;
				echo "</td></tr><tr><form method='post' action='tokens.php'><td align='center'>$setfont<b>Warning:</b><br />";
				echo "The number of emails to send ($ctcount) is greater than the maximum number";
				echo " of emails that can be sent in one lot ($maxemails). There are still $lefttosend";
				echo " emails to go. You can continue sending the next $maxemails by clicking on the";
				echo " button below.<br />";
				echo "<input type='submit' value='Send More'></TD>\n";
				echo "<input type='hidden' name='ok' value=\"absolutely\">\n";
				echo "<input type='hidden' name='action' value=\"remind\">\n";
				echo "<input type='hidden' name='sid' value=\"{$_POST['sid']}\">\n";
				echo "<input type='hidden' name='from' value=\"{$_POST['from']}\">\n";
				echo "<input type='hidden' name='subject' value=\"{$_POST['subject']}\">\n";
				$message = str_replace('"', "&quot;", $message);
				echo "<input type='hidden' name='message' value=\"$message}\">\n";
				echo "<input type='hidden' name='last_tid' value=\"$lasttid\">\n";
				echo "</form>\n";
				}
			}
		else
			{
			echo "<center><b>WARNING:</b><br />There were no token recipients who have been sent an invitation but have not yet responded.";
			echo "<br /><br />";
			echo "No invitations have been sent out!";
			}
		
		echo "</td></tr></table>\n";
		}
	}

	
if ($action == "tokenify")
	{
	echo "<CENTER>$setfont<B>Tokens</B><br />\n";
	if (!$ok)
		{
		echo "<CENTER><br />$setfont Clicking OK will generate tokens for all<br />those in this token list that have not<br />been issued one. Is this OK?";
		echo "<br /><INPUT TYPE='SUBMIT' $btstyle VALUE='Yes' onClick=\"window.open('tokens.php?sid=$sid&action=tokenify&ok=Y', '_top')\">";
		echo "<br /><INPUT TYPE='SUBMIT' $btstyle VALUE='No' onClick=\"window.open('tokens.php?sid=$sid', '_top')\">";
		}
	else
		{
		$tkquery = "SELECT * FROM tokens_$sid WHERE token IS NULL OR token=''";
		$tkresult = mysql_query($tkquery) or die ("Mucked up!<br />$tkquery<br />".mysql_error());
		while ($tkrow = mysql_fetch_array($tkresult))
			{
			$insert = "NO";
			while ($insert != "OK")
				{
				$newtoken = sprintf("%010s", rand(1,10000000000));
				$ntquery = "SELECT * FROM tokens_$sid WHERE token='$newtoken'";
				$ntresult = mysql_query($ntquery);
				if (!mysql_num_rows($ntresult)) {$insert = "OK";}
				}
			$itquery = "UPDATE tokens_$sid SET token='$newtoken' WHERE tid={$tkrow['tid']}";
			$itresult = mysql_query($itquery);
			$newtokencount++;
			}
		echo "<br /><br /><B>$newtokencount tokens have been generated";
		}
	}
if ($action == "delete")
	{
	$dlquery = "DELETE FROM tokens_$sid WHERE tid=$tid";
	$dlresult = mysql_query($dlquery) or die ("Couldn't delete record $tid<br />".mysql_error());
	echo "<br /><B>Record has been deleted.";
	}

	
	
if ($action == "edit" || $action == "add")
	{
	if ($action == "edit")
		{
		$edquery = "SELECT * FROM tokens_$sid WHERE tid=$tid";
		$edresult = mysql_query($edquery);
		while($edrow = mysql_fetch_array($edresult))
			{
			//Create variables with the same names as the database column names and fill in the value
			foreach ($edrow as $Key=>$Value) {$$Key = $Value;}
			}
		}
	echo "<br />\n";
	echo "<table width='550' bgcolor='#CCCCCC'>\n";
	echo "<form method='post'>\n";
	echo "<tr>\n";
	echo "\t<td colspan='2' align='center'>$setfont<b>Edit/Add Token Entry</b></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "\t<td align='right' width='20%'>$setfont<b>ID:</b></td><td bgcolor='#EEEEEE'>$setfont Auto</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "\t<td align='right' width='20%'>$setfont<b>First Name:</b></td>\n";
	echo "\t<td bgcolor='#EEEEEE'>$setfont<input type='text' $slstyle size='30' name='firstname' value='$firstname'></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "\t<td align='right' width='20%'>$setfont<b>Last Name:</b></td>\n";
	echo "\t<td bgcolor='#EEEEEE'>$setfont<input type='text' $slstyle size='30' name='lastname' value='$lastname'></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "\t<td align='right' width='20%'>$setfont<b>Email:</b></td>\n";
	echo "\t<td bgcolor='#EEEEEE'>$setfont<input type='text' $slstyle size='40' name='email' value='$email'></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "\t<td align='right' width='20%'>$setfont<b>Token:</b></td>\n";
	echo "\t<td bgcolor='#EEEEEE'>$setfont<input type='text' size='10' $slstyle name='token' value='$token'></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "\t<td align='right' width='20%'>$setfont<b>Sent?:</b></td>\n";
	echo "\t<td bgcolor='#EEEEEE'>$setfont<input type='text' size='1' $slstyle name='sent' value='$sent'></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "\t<td align='right' width='20%'>$setfont<b>Complete?:</b></td>\n";
	echo "\t<td bgcolor='#EEEEEE'>$setfont<input type='text' size='1' $slstyle name='completed' value='$completed'></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "\t<td colspan='2' align='center'>";
	switch($action)
		{
		case "edit":
			echo "\t\t<input type='submit' $btstyle name='action' value='update'>\n";
			echo "\t\t<input type='hidden' name='tid' value='$tid'>\n";
			break;
		case "add":
			echo "\t\t<input type='submit' $btstyle name='action' value='insert'>\n";
			break;
		}
	echo "\t\t<input type='hidden' name='sid' value='$sid'>\n";
	echo "\t</td>\n";
	echo "</tr>\n</form>\n</table>\n";
	echo "</body>\n</html>";
	}


if ($action == "update")
	{
	echo "<br />$setfont<B>UPDATING TOKEN ENTRY</B><br />\n";
	$udquery = "UPDATE tokens_$sid SET firstname='$firstname', lastname='$lastname', email='$email', token='$token', sent='$sent', completed='$completed' WHERE tid=$tid";
	$udresult = mysql_query($udquery) or die ("Update record $tid failed:<br />$udquery<br />".mysql_error());
	echo "<br />Entry succesfully updated!";
	}


if ($action == "insert")
	{
	echo "<br />$setfont<B>INSERTING TOKEN ENTRY</B><br />\n";
	$inquery = "INSERT into tokens_$sid \n";
	$inquery .= "(firstname, lastname, email, token, sent, completed) \n";
	$inquery .= "VALUES ('$firstname', '$lastname', '$email', '$token', '$sent', '$completed')";
	$inresult = mysql_query($inquery) or die ("Add new record failed:<br />\n$inquery<br />\n".mysql_error());
	echo "<br />Entry succesfully added!";
	}


if ($action == "import") 
	{
	form();
	echo "<table width='400' bgcolor='#eeeeee'>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>\n";
	echo "\t\t\t<font size='1'><b>Note:</b><br />\n";
	echo "\t\t\tFile should be a standard comma delimited file with no quotes in the form of:<br /><br />\n";
	echo "\t\t\t<i>Firstname, Lastname, Email, Token</i>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	}


if ($action == "upload") 
	{
	$the_path = "$homedir";
	$the_full_file_path = $homedir."/".$the_file_name;
	if (!@copy($the_file, $the_path . "/" . $the_file_name)) 
		{
		form("<b>Something went horribly wrong, check the path to and ".
		"the permissions for the upload directory</b>\n");
		}
		else
		{
		echo "<br /><b>IMPORTING FILE</b><br />\nFile succesfully uploaded<br /><br />\n";
		echo "Reading File...<br />\n";
		$xz = 0; $xx = 0;
		$handle = fopen($the_full_file_path, "r");
		if ($handle == false) {echo "Failed to open the uploaded file!\n";}
		while (!feof($handle))
			{
			$buffer = fgets($handle);
			
			//Delete trailing CR from Windows files.
			//Macintosh files end lines with just a CR, which fgets() doesn't handle correctly.
			//It will read the entire file in as one line.
			if (substr($buffer, -1) == "\n") {$buffer = substr($buffer, 0, -1);}
			
			//echo "$xx:".$buffer."<br />\n"; //Debugging info
			$firstname = ""; $lastname = ""; $email = ""; $token = ""; //Clear out values from the last path, in case the next line is missing a value
			if (!$xx)
				{
				//THIS IS THE FIRST LINE. IT IS THE HEADINGS. IGNORE IT
				}
			else
				{
				$line = explode(",", $buffer);
				$elements = count($line); 
				if ($elements > 1)
					{
					$xy = 0;
					foreach($line as $el)
						{
						//echo "[$el]($xy)<br />\n"; //Debugging info
						if ($xy < $elements)
							{
							if ($xy == 0) {$firstname = $el;}
							if ($xy == 1) {$lastname = $el;}
							if ($xy == 2) {$email = $el;}
							if ($xy == 3) {$token = $el;}
							}
						$xy++;
						}
					//CHECK FOR DUPLICATES?
					$iq = "INSERT INTO tokens_$sid \n";
					$iq .= "(firstname, lastname, email, token) \n";
					$iq .= "VALUES ('$firstname', '$lastname', '$email', '$token')";
					//echo "<pre style='text-align: left'>$iq</pre>\n"; //Debugging info
					$ir = mysql_query($iq) or die ("Couldn't insert line<br />\n$buffer<br />\n".mysql_error()."<pre style='text-align: left'>$iq</pre>\n");
					$xz++;
					}
				}
			$xx++;
			}
		echo "Process completed.<br />\n";
		echo "$xz records added.<br />\n";
		fclose($handle);
		unlink($the_full_file_path);
		}
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	echo "</body>\n</html>";
	}

//echo "ACTION: $action<br />THEFILE: $the_file<br />THEFILENAME: $the_file_name";


function form($error=false) {

global $PHP_SELF, $sid, $btstyle, $slstyle, $setfont;

	if ($error) {print $error . "<br /><br />\n";}
	
	print "\n<form enctype='multipart/form-data' action='" . $PHP_SELF . "' method='post'>\n";
	print "<input type='hidden' name='action' value='upload' />\n";
	print "<input type='hidden' name='sid' value='$sid' />\n";
	print "$setfont Upload a file<br />\n";
	print "<input type='file' $slstyle name='the_file' size='35' /><br />\n";
	print "<input type='submit' $btstyle value='Upload' />\n";
	print "</form>\n\n";

} # END form

?>