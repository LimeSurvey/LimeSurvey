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

$sid = $_GET['sid'];
$action = $_GET['action'];
$tid = $_GET['tid'];
$order = $_GET['order'];
$ok = $_GET['ok'];

include("config.php");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0

echo $htmlheader;
echo "<table width='100%'>\n";
echo "\t<tr><td bgcolor='#BBBBBB' align='center'>$setfont<b>Token Control</b></td></tr>\n";

// MAKE SURE THAT THERE IS A SID
if (!$_GET['sid'] && !$_POST['sid'])
	{
	echo "\t<tr><td align='center'>$setfont<br /><br />Sorry, you have not chosen a survey_id!</td></tr>\n";
	echo "</table>\n";
	echo "</body>\n</html>";
	exit;
	}
else
	{
	$sid = $_GET['sid'];
	if (!$sid) {$sid = $_POST['sid'];}
	}

//CONVERT POST & GET VARIABLES TO GLOBALS
if ($_GET['action']) {$action = $_GET['action'];}
if ($_POST['action']) {$action = $_POST['action'];}

// MAKE SURE THAT THE SURVEY EXISTS
$chquery = "SELECT * FROM surveys WHERE sid=$sid";
if (!$chresult = mysql_query($chquery))
	{
	echo "\t<tr><td align='center'>$setfont<br /><br />Sorry, this survey does not exist</td></tr>\n";
	echo "</table>\n";
	echo "</body>\n</html>";
	exit;
	}
while ($chrow = mysql_fetch_array($chresult))
	{
	echo "\t<tr><td align='center'>$setfont<b>Survey $sid - {$chrow['short_title']}</td></tr>\n";
	$surveyprivate = $chrow['private'];
	}

// CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
$tkquery = "SELECT * FROM tokens_$sid";
if (!$tkresult = mysql_query($tkquery))
	{
	if (!$createtable)
		{
		echo "\t<tr>\n";
		echo "\t\t<td align='center'>\n";
		echo "\t\t\t$setfont<br /><br />\n";
		echo "\t\t\tNo token system has been created for this survey. Do you want to create one now?<br /><br />\n";
		echo "\t\t\t<input type='submit' $btstyle value='Create Token Table' onClick=\"window.open('tokens.php?sid=$sid&createtable=Y', '_top')\"><br />\n";
		echo "\t\t\t<input type='submit' $btstyle value='Return to Admin' onClick=\"window.open('admin.php?sid=$sid', '_top')\">\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "</table>\n";
		echo "</body>\n</html>";
		exit;
		}
	else
		{
		$createtokentable = "CREATE TABLE tokens_$sid (\n";
		$createtokentable .= "tid int NOT NULL auto_increment,\n ";
		$createtokentable .= "firstname varchar(40) NULL,\n ";
		$createtokentable .= "lastname varchar(40) NULL,\n ";
		$createtokentable .= "email varchar(100) NULL,\n ";
		$createtokentable .= "token varchar(10) NULL,\n ";
		$createtokentable .= "sent varchar(1) NULL DEFAULT 'N',\n ";
		$createtokentable .= "completed varchar(1) NULL DEFAULT 'N',\n ";
		$createtokentable .= "PRIMARY KEY (tid)\n) TYPE=MyISAM;";
		$ctresult = mysql_query($createtokentable) or die ("Completely mucked up<br />$createtokentable<br /><br />".mysql_error());
		echo "\t<tr>\n";
		echo "\t\t<td align='center'>\n";
		echo "\t\t\t$setfont<br /><br />\n";
		echo "\t\t\tA token table has been created for this survey, called \"tokens_$sid\"<br /><br />\n";
		echo "\t\t\t<input type='submit' $btstyle value='Continue' onClick=\"window.open('tokens.php?sid=$sid', '_top')\">\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "</table>\n";
		echo "</body>\n</html>";
		exit;
		
		}
	}

// IF WE MADE IT THIS FAR, THEN THERE IS A TOKENS TABLE, SO LETS DEVELOP THE MENU ITEMS

$tokenmenu = "\t<tr>\n";
$tokenmenu .= "\t\t<td align='center' style='background-color: #EEEEEE; border-top: 1px solid #BBBBBB; border-left: 1px solid #BBBBBB; border-right: 1px solid #BBBBBB'>\n";
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
echo "\t<tr><td align='center'>There are $tkcount records in your token table for this survey.</td></tr>\n";

// GIVE SOME INFORMATION ABOUT THE TOKENS
echo "\t<tr>\n";
echo "\t\t<td align='center'>\n";
echo "\t\t\t<table align='center' bgcolor='#DDDDDD'>\n";
echo "\t\t\t\t<tr>\n";
echo "\t\t\t\t\t<td align='left'>\n";
$tksq = "SELECT count(*) FROM tokens_$sid WHERE sent='Y'";
$tksr = mysql_query($tksq);
while ($tkr = mysql_fetch_row($tksr))
	{echo "\t\t\t\t\t\t$setfont$tkr[0] of $tkcount have been sent an invitation to participate.<br />\n";}
$tksq = "SELECT count(*) FROM tokens_$sid WHERE completed='Y'";
$tksr = mysql_query($tksq);
while ($tkr = mysql_fetch_row($tksr))
	{echo "\t\t\t\t\t\t$setfont$tkr[0] of $tkcount entries have completed the survey.<br />\n";}
$tksq = "SELECT count(*) FROM tokens_$sid WHERE token IS NULL OR token=''";
$tksr = mysql_query($tksq);
while ($tkr = mysql_fetch_row($tksr))
	{echo "\t\t\t\t\t\t$setfont$tkr[0] of $tkcount have not had a token generated.\n";}
echo "\t\t\t\t\t</td>\n";
echo "\t\t\t\t</tr>\n";
echo "\t\t\t</table>\n";
echo "\t\t</td>\n";
echo "\t</tr>\n";
echo "</table>\n";
echo "<center>\n";

if ($action == "browse")
	{
	echo "<table width='600' cellpadding='1' cellspacing='1' align='center' bgcolor='#CCCCCC'>\n";
	//COLUMN HEADINGS
	echo "\t<tr>\n";
	echo "\t\t<th align='left'><a href='tokens.php?sid=$sid&action=browse&order=tid'><img src='DownArrow.gif' alt='Sort by ID' border='0' align='left'></a>$setfont"."ID</th>\n";
	echo "\t\t<th align='left'><a href='tokens.php?sid=$sid&action=browse&order=firstname'><img src='DownArrow.gif' alt='Sort by First Name' border='0' align='left'></a>$setfont"."First</th>\n";
	echo "\t\t<th align='left'><a href='tokens.php?sid=$sid&action=browse&order=lastname'><img src='DownArrow.gif' alt='Sort by Last Name' border='0' align='left'></a>$setfont"."Last</th>\n";
	echo "\t\t<th align='left'><a href='tokens.php?sid=$sid&action=browse&order=email'><img src='DownArrow.gif' alt='Sort by Email' border='0' align='left'></a>$setfont"."Email</th>\n";
	echo "\t\t<th align='left'><a href='tokens.php?sid=$sid&action=browse&order=token'><img src='DownArrow.gif' alt='Sort by Token' border='0' align='left'></a>$setfont"."Token</th>\n";
	echo "\t\t<th align='left'><a href='tokens.php?sid=$sid&action=browse&order=sent%20desc'><img src='DownArrow.gif' alt='Sort by Invite?' border='0' align='left'></a>$setfont"."Invite?</th>\n";
	echo "\t\t<th align='left'><a href='tokens.php?sid=$sid&action=browse&order=completed%20desc'><img src='DownArrow.gif' alt='Sort by Done?' border='0' align='left'></a>$setfont"."Done?</th>\n";
	echo "\t\t<th align='left' colspan='2'>$setfont"."Action</th>\n";
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
		echo "\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-face: verdana' type='submit' value='E' title='Edit' onClick=\"window.open('$PHP_SELF?sid=$sid&action=edit&tid=$brow[0]', '_top')\" />";
		echo "<input style='height: 16; width: 16px; font-size: 8; font-face: verdana' type='submit' value='D' title='Delete' onClick=\"window.open('$PHP_SELF?sid=$sid&action=delete&tid=$brow[0]', '_top')\" />";
		if ($brow['completed'] != "Y" && $brow['token']) {echo "<input style='height: 16; width: 16px; font-size: 8; font-face: verdana' type='submit' value='S' title='Do Survey' onClick=\"window.open('$publicurl/index.php?sid=$sid&token={$brow['token']}', '_blank')\" />\n";}
		echo "\n\t\t</td>\n";
		if ($brow['completed'] == "Y" && $surveyprivate == "N")
			{
			echo "\t\t<form action='browse.php' method='post' target='_blank'>\n";
			echo "\t\t<td align='center'>\n";
			echo "\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-face: verdana' type='submit' value='V' title='View Response' />\n";
			echo "\t\t</td>\n";
			echo "\t\t<input type='hidden' name='sid' value='$sid' />\n";
			echo "\t\t<input type='hidden' name='action' value='id' />\n";
			echo "\t\t<input type='hidden' name='sql' value=\"token='{$brow['token']}'\" />\n";
			echo "\t\t</form>\n";
			}
		elseif ($brow['completed'] != "Y" && $brow['token'] && $brow['sent'] == "Y")
			{
			echo "\t\t<td align='center'>\n";
			echo "\t\t\t<input style='height: 16; width: 16px; font-size: 8; font-face: verdana' type='submit' value='R' title='Send Reminder Email' onClick=\"window.open('$PHP_SELF?sid=$sid&action=remind&tid=$brow[0]', '_top')\" />";
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
	$date = date(YmdHi);
	echo "$setfont<b>Drop/Delete Tokens</b></font><br />\n";
	if (!$ok)
		{
		echo "<span style='display: block; text-align: center; width: 70%; background-color: #DDDDDD'>\n";
		echo "Deleting this token table will mean that tokens are no longer<br />\n";
		echo "required for public access to this survey. It will also delete<br />\n";
		echo "all the existing tokens in this survey. A backup of this table<br />\n";
		echo "will be made, and called \"old_tokens_$sid_$date\". This can be<br />\n";
		echo "recovered by a systems administrator.<br /><br />\n";
		echo "<input type='submit' $btstyle value='Delete Tokens' onClick=\"window.open('tokens.php?sid=$sid&action=kill&ok=surething', '_top')\" /><br />\n";
		echo "<input type='submit' $btstyle value='Cancel' onClick=\"window.open('tokens.php?sid=$sid', '_top')\" />\n";
		echo "</span>\n";
		}
	elseif ($_GET['ok'] == "surething")
		{
		$oldtable = "tokens_{$sid}";
		$newtable = "old_tokens_{$sid}_{$date}";
		$deactivatequery = "RENAME TABLE $oldtable TO $newtable";
		$deactivateresult = mysql_query($deactivatequery) or die ("Couldn't deactivate because:<br />\n".mysql_error()."<br /><br />\n<a href='$scriptname?sid=$sid'>Admin</a>\n");
		echo "<span style='display: block; text-align: center; width: 70%; background-color: #DDDDDD'>\n";
		echo "The tokens table has now been removed and tokens are no longer<br />\n";
		echo "required for public access to this survey. A backup of this table<br />\n";
		echo "has been made, and is called \"old_tokens_$sid_$date\". This can be<br />\n";
		echo "recovered by a systems administrator.<br /><br />\n";
		echo "<input type='submit' $btstyle value='Finished' onClick=\"window.open('tokens.php?sid=$sid', '_top')\" />\n";
		echo "</span>\n";
		}
	}	


if ($_GET['action'] == "email" || $_POST['action'] == "email")
	{
	echo "$setfont<b>Email Invitation</b><br />\n";
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
		echo "<form method='post'>\n";
		echo "\t<tr><td colspan='2' bgcolor='black' align='center'>$setfont<font color='white'><b>Send Invitation</b></td></tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td align='right'>$setfont<b>From:</b></td>\n";
		echo "\t\t<td><input type='text' $slstyle size='50' name='from' value='$surveyadminemail' /></td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td align='right'>$setfont<b>Subject:</b></td>\n";
		echo "\t\t<td><input type='text' $slstyle size='50' name='subject' value='Invitation to participate in $surveyname survey' /></td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td align='right' valign='top'>$setfont<b>Message:</b></td>\n";
		echo "\t\t<td>$setfont<b>The following will be added to the top of your message:</b>\n";
		echo "\t\t\t<table width='500' bgcolor='#EEEEEE' border='1' cellpadding='0' cellspacing='0'>\n";
		echo "\t\t\t\t<tr><td>$setfont Dear [FIRSTNAME],</td></tr>\n";
		echo "\t\t\t</table>\n";
		echo "\t\t\t<b>You can make changes to this part of the message:</b><br />\n";
		echo "<textarea name='message' rows='6' cols='60'>";
		echo "You have been invited to participate in the following survey.\n\n";
		echo "** Survey Name **\n$surveyname\n\n";
		echo "** Survey Description **\n$surveydescription\n\n";
		echo "To participate, please click on the link below.";
		echo "\n\nSincerely,\n\n$surveyadmin ($surveyadminemail)";
		echo "</textarea>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td></td>\n";
		echo "\t\t<td>$setfont<b>The following will be added to the end of your email message:</b><br />\n";
		echo "\t\t\t<span style='width: 500px; background-color: #EEEEEE; border: 2px ridge #FFFFFF; display: block'>\n";
		echo "\t\t\t\t$setfont---------------------------------<br />\n";
		echo "\t\t\t\tClick Here to do Survey:<br />\n";
		echo "\t\t\t\t$publicurl/index.php?sid=$sid&token=[TOKENVALUE]<br />\n";
		echo "\t\t\t</span>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Send Invitations'></td></tr>\n";
		echo "\t<input type='hidden' name='ok' value='absolutely' />\n";
		echo "\t<input type='hidden' name='sid' value='{$_GET['sid']}' />\n";
		echo "\t<input type='hidden' name='action' value='email' />\n";
		echo "</form>\n";
		echo "</table>\n";
		}
	else
		{
		echo "Sending email!<br />\n";
		$ctquery = "SELECT firstname FROM tokens_{$_POST['sid']} WHERE completed !='Y' AND sent !='Y' AND token !=''";
		$ctresult = mysql_query($ctquery) or die("Database error!<br />\n" . mysql_error());
		$ctcount = mysql_num_rows($ctresult);
		$emquery = "SELECT firstname, lastname, email, token, tid FROM tokens_{$_POST['sid']} WHERE completed != 'Y' AND sent != 'Y' AND token !='' LIMIT $maxemails";
		$emresult = mysql_query($emquery) or die ("Couldn't do query.<br />\n$emquery<br />\n".mysql_error());
		$emcount = mysql_num_rows($emresult);
		$headers = "From: {$_POST['from']}\r\n";
		$headers .= "X-Mailer: $sitename Email Inviter";  
		$message = strip_tags($_POST['message']);
		$message = str_replace("&quot;", '"', $message);
		if (get_magic_quotes_gpc() != "0")
			{$message = stripcslashes($message);}
		echo "<table width='500px' align='center' bgcolor='#EEEEEE'>\n";
		echo "\t<tr>\n";
		echo "\t\t<td><font size='1'>\n";
		if ($emcount > 0)
			{
			while ($emrow = mysql_fetch_array($emresult))
				{
				$to = $emrow['email'];
				//echo "To: $to ({$emrow['firstname']} {$emrow['lastname']})<br />\n";
				//$from = $surveyadminemail;
				//echo "From: $from<br />\n";
				//echo "Subject: $subject<br />\n";
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
				echo "\t\t</td>\n";
				echo "\t</tr>\n";
				echo "\t<tr>\n";
				echo "\t\t<td align='center'>$setfont<b>Warning:</b><br />\n";
				echo "\t\t\t<form method='post'>\n";
				echo "The number of emails to send ($ctcount) is greater than the maximum number";
				echo " of emails that can be sent in one lot ($maxemails). There are still $lefttosend";
				echo " emails to go. You can continue sending the next $maxemails by clicking on the";
				echo " button below.<br />\n";
				$message = str_replace('"', "&quot;", $message);
				echo "\t\t\t<input type='submit' value=\"Send More\" />\n";
				echo "\t\t\t<input type='hidden' name='ok' value=\"absolutely\" />\n";
				echo "\t\t\t<input type='hidden' name='action' value=\"email\" />\n";
				echo "\t\t\t<input type='hidden' name='sid' value=\"{$_POST['sid']}\" />\n";
				echo "\t\t\t<input type='hidden' name='from' value=\"{$_POST['from']}\" />\n";
				echo "\t\t\t<input type='hidden' name='subject' value=\"{$_POST['subject']}\" />\n";
				echo "\t\t\t<input type='hidden' name='message' value=\"$message\" />\n";
				echo "\t\t\t</form>\n";
				}
			}
		else
			{
			echo "<center><b>WARNING:</b><br />\nThere were no token recipients who have not already had";
			echo " an invitation sent out, or who have not responded!<br /><br />\n";
			echo "No invitations have been sent out!</center>\n";
			}
			echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "</table>\n";
		}
	}
	
if ($_GET['action'] == "remind" || $_POST['action'] == "remind")
	{
	echo "$setfont<b>Email Reminder</b><br />\n";
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
		echo "\t<tr>\n";
		echo "\t\t<td colspan='2' bgcolor='black' align='center'>\n";
		echo "\t\t\t$setfont<font color='white'><b>Send Reminder\n";
		if ($_GET['tid']) {echo " to TokenID No {$_GET['tid']}";}
		echo "\t\t\t</b>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td align='right'>$setfont<b>From:</td>\n";
		echo "\t\t<td><input type='text' $slstyle size='50' name='from' value='$surveyadminemail' /></td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td align='right'>$setfont<b>Subject:</td>\n";
		echo "\t\t<td><input type='text' $slstyle size='50' name='subject' value='Reminder to participate in $surveyname' /></td>\n";
		echo "\t</tr>\n";
		if (!$_GET['tid'])
			{
			echo "\t<tr>\n";
			echo "\t\t<td align='right' valign='top'>$setfont<b>Start at ID:</b></td>\n";
			echo "\t\t<td><input type='text' $slstyle size='5' name='last_tid' /></td>\n";
			echo "\t</tr>\n";
			}
		echo "\t<tr>\n";
		echo "\t\t<td align='right' valign='top'>$setfont<b>Message:</b></td>\n";
		echo "\t\t<td>\n";
		echo "\t\t\t$setfont<b>The following will be added to the top of your message:</b>\n";
		echo "\t\t\t<table width='500' bgcolor='#EEEEEE' border='1' cellpadding='0' cellspacing='0'>\n";
		echo "\t\t\t\t<tr><td>$setfont Dear [FIRSTNAME],</td></tr>\n";
		echo "\t\t\t</table>\n";
		echo "\t\t\t<b>You can make changes to this part of the message:</b><br />\n";
		echo "<textarea name='message' rows='6' cols='60'>";
		echo "Recently we invited you to participate in a survey.\n\n";
		echo "We note that you have not yet completed the survey, and wish to remind you ";
		echo "that the survey is still available should you wish to take part.\n\n";
		echo "** Survey Name **\n$surveyname\n\n";
		echo "** Survey Description **\n";
		echo strip_tags($surveydescription)."\n\n";
		echo "To participate, please click on the link below.";
		echo "\n\nSincerely,\n\n$surveyadmin ($surveyadminemail)";
		echo "</textarea>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td></td>\n";
		echo "\t\t<td>$setfont<b>The following will be added to the end of your email message:</b><br />\n";
		echo "\t\t\t<span style='width: 500px; background-color: #EEEEEE; border: 2px ridge #FFFFFF; display: block'>\n";
		echo "\t\t\t$setfont---------------------------------<br />\n";
		echo "\t\t\tClick Here to do Survey:<br />\n";
		echo "\t\t\t$publicurl/index.php?sid=$sid&token=[TOKENVALUE]<br />\n";
		echo "\t\t\t</span>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td colspan='2' align='center'>\n";
		echo "\t\t\t<input type='submit' $btstyle value='Send Reminder' />\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<input type='hidden' name='ok' value='absolutely'>\n";
		echo "\t<input type='hidden' name='sid' value='{$_GET['sid']}'>\n";
		echo "\t<input type='hidden' name='action' value='remind'>\n";
		if ($_GET['tid']) {echo "\t<input type='hidden' name='tid' value='{$_GET['tid']}'>\n";}
		echo "\t</form>\n";
		echo "</table>\n";
		}
	else
		{
		echo "Sending reminder email!\n";
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
		echo "<table width='500' align='center' bgcolor='#EEEEEE'>\n";
		echo "\t<tr>\n";
		echo "\t\t<td><font size='1'>\n";
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
				echo "\t\t\t({$emrow['tid']})[Reminder Sent to {$emrow['firstname']} {$emrow['lastname']}]<br />\n";
				$lasttid = $emrow['tid'];
				}
			if ($ctcount > $emcount)
				{
				$lefttosend = $ctcount-$maxemails;
				echo "\t\t</td>\n";
				echo "\t</tr>\n";
				echo "\t<tr><form method='post' action='tokens.php'>\n";
				echo "\t\t<td align='center'>\n";
				echo "\t\t\t$setfont<b>Warning:</b><br />\n";
				echo "\t\t\tThe number of emails to send ($ctcount) is greater than the maximum number";
				echo " of emails that can be sent in one lot ($maxemails). There are still $lefttosend";
				echo " emails to go. You can continue sending the next $maxemails by clicking on the";
				echo " button below.<br />\n";
				echo "\t\t\t<input type='submit' value='Send More' />\n";
				echo "\t\t</td>\n";
				echo "\t<input type='hidden' name='ok' value=\"absolutely\" />\n";
				echo "\t<input type='hidden' name='action' value=\"remind\" />\n";
				echo "\t<input type='hidden' name='sid' value=\"{$_POST['sid']}\" />\n";
				echo "\t<input type='hidden' name='from' value=\"{$_POST['from']}\" />\n";
				echo "\t<input type='hidden' name='subject' value=\"{$_POST['subject']}\" />\n";
				$message = str_replace('"', "&quot;", $message);
				echo "\t<input type='hidden' name='message' value=\"$message}\" />\n";
				echo "\t<input type='hidden' name='last_tid' value=\"$lasttid\" />\n";
				echo "\t</form>\n";
				}
			}
		else
			{
			echo "<center><b>WARNING:</b><br />\nThere were no token recipients who have been sent an invitation but have not yet responded.\n";
			echo "<br /><br />\n";
			echo "No invitations have been sent out!</center>\n";
			echo "\t\t</td>\n";
			}
		echo "\t</tr>\n";
		echo "</table>\n";
		}
	}

	
if ($action == "tokenify")
	{
	echo "$setfont<b>Tokens</b><br />\n";
	if (!$_GET['ok'])
		{
		echo "<br />$setfont Clicking OK will generate tokens for all<br />\nthose in this token list that have not<br />\nbeen issued one. Is this OK?<br />\n";
		echo "<input type='submit' $btstyle value='Yes' onClick=\"window.open('tokens.php?sid=$sid&action=tokenify&ok=Y', '_top')\" />\n";
		echo "<input type='submit' $btstyle value='No' onClick=\"window.open('tokens.php?sid=$sid', '_top')\" />\n";
		}
	else
		{
		$newtokencount = 0;
		$tkquery = "SELECT * FROM tokens_$sid WHERE token IS NULL OR token=''";
		$tkresult = mysql_query($tkquery) or die ("Mucked up!<br />$tkquery<br />".mysql_error());
		while ($tkrow = mysql_fetch_array($tkresult))
			{
			$insert = "NO";
			while ($insert != "OK")
				{
				$newtoken = sprintf("%010s", rand(1, 10000000000));
				$ntquery = "SELECT * FROM tokens_$sid WHERE token='$newtoken'";
				$ntresult = mysql_query($ntquery);
				if (!mysql_num_rows($ntresult)) {$insert = "OK";}
				}
			$itquery = "UPDATE tokens_$sid SET token='$newtoken' WHERE tid={$tkrow['tid']}";
			$itresult = mysql_query($itquery);
			$newtokencount++;
			}
		echo "<br /><br /><b>$newtokencount tokens have been generated.</b>\n";
		}
	}


if ($action == "delete")
	{
	$dlquery = "DELETE FROM tokens_$sid WHERE tid={$_GET['tid']}";
	$dlresult = mysql_query($dlquery) or die ("Couldn't delete record {$_GET['tid']}<br />".mysql_error());
	echo "<br /><b>Record has been deleted.</b>\n";
	}


if ($action == "edit" || $action == "add")
	{
	if ($action == "edit")
		{
		$edquery = "SELECT * FROM tokens_$sid WHERE tid={$_GET['tid']}";
		$edresult = mysql_query($edquery);
		while($edrow = mysql_fetch_array($edresult))
			{
			//Create variables with the same names as the database column names and fill in the value
			foreach ($edrow as $Key=>$Value) {$$Key = $Value;}
			}
		}
	echo "<br />\n";
	echo "<table width='550' bgcolor='#CCCCCC' align='center'>\n";
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
	echo "\t<td bgcolor='#EEEEEE'>$setfont<input type='text' $slstyle size='50' name='email' value='$email'></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "\t<td align='right' width='20%'>$setfont<b>Token:</b></td>\n";
	echo "\t<td bgcolor='#EEEEEE'>$setfont<input type='text' size='15' $slstyle name='token' value='$token'></td>\n";
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
			echo "\t\t<input type='hidden' name='tid' value='{$_GET['tid']}'>\n";
			break;
		case "add":
			echo "\t\t<input type='submit' $btstyle name='action' value='insert'>\n";
			break;
		}
	echo "\t\t<input type='hidden' name='sid' value='$sid'>\n";
	echo "\t</td>\n";
	echo "</tr>\n</form>\n";
	echo "</table>\n";
	}


if ($action == "update")
	{
	echo "<br />$setfont<B>UPDATING TOKEN ENTRY</B><br />\n";
	$udquery = "UPDATE tokens_$sid SET firstname='{$_POST['firstname']}', lastname='{$_POST['lastname']}', email='{$_POST['email']}', token='{$_POST['token']}', sent='{$_POST['sent']}', completed='{$_POST['completed']}' WHERE tid={$_POST['tid']}";
	$udresult = mysql_query($udquery) or die ("Update record {$_POST['tid']} failed:<br />\n$udquery<br />\n".mysql_error());
	echo "<br />Entry succesfully updated!\n";
	}


if ($action == "insert")
	{
	echo "<br />$setfont<B>INSERTING TOKEN ENTRY</b><br />\n";
	$inquery = "INSERT into tokens_$sid \n";
	$inquery .= "(firstname, lastname, email, token, sent, completed) \n";
	$inquery .= "VALUES ('{$_POST['firstname']}', '{$_POST['lastname']}', '{$_POST['email']}', '{$_POST['token']}', '{$_POST['sent']}', '{$_POST['completed']}')";
	$inresult = mysql_query($inquery) or die ("Add new record failed:<br />\n$inquery<br />\n".mysql_error());
	echo "<br />Entry succesfully added!\n";
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
	$the_file_name = $_FILES['the_file']['name'];
	$the_file = $_FILES['the_file']['tmp_name'];
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
				if (phpversion() >= "4.3.0")
					{
					$line = explode(",", mysql_real_escape_string($buffer));
					}
				else
					{
					$line = explode(",", mysql_escape_string($buffer));
					}
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
	}

//echo "ACTION: $action (POST: {$_POST['action']})<br />THEFILE: $the_file (FILES: {$_FILES['the_file']['tmp_name']})<br />THEFILENAME: $the_file_name (FILES: {$_FILES['the_file']['name']})";
echo "</center>\n";
echo "<table width='100%' align='center' bgcolor='#000080'>\n";
echo "\t<tr>\n";
echo "\t\t<td align='center'>\n";
echo "\t\t\t<img src='help.gif' align='left' alt='Help for Tokens' onClick=\"window.open('instructions.html#tokens', '_blank')\">\n";
echo "\t\t\t<img src='help.gif' align='right' alt='Help for Tokens' onClick=\"window.open('instructions.html#tokens', '_blank')\">\n";
echo "\t\t\t<img src='phpsurveyor_logo.jpg' onClick=\"window.open('http://phpsurveyor.sourceforge.net/', '_blank')\">\n";
echo "\t\t</td>\n";
echo "\t</tr>\n";
echo "</table>\n";
echo "</body>\n</html>";


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