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
echo "<TABLE WIDTH='100%'><TR><TD BGCOLOR='#BBBBBB' COLSPAN='2' ALIGN='CENTER'>$setfont<B>Token Control</TD></TR>\n";

// MAKE SURE THAT THERE IS A SID
if (!$sid)
	{
	echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>$setfont<BR><BR>Sorry, you have not chosen a survey_id!</tD></TR>\n";
	echo "</TABLE>\n";
	exit;
	}

// MAKE SURE THAT THE SURVEY EXISTS
$chquery = "SELECT * FROM surveys WHERE sid=$sid";
if (!$chresult=mysql_query($chquery))
	{
	echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>$setfont<BR><BR>Sorry, this survey does not exist</TD></TR>\n";
	echo "</TABLE>\n";
	exit;
	}
while ($chrow=mysql_fetch_row($chresult))
	{
	echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>$setfont<B>Survey $sid - $chrow[1]</TD></TR>\n";
	}

// CHECK TO SEE IF A TOKEN TABLE EXISTS FOR THIS SURVEY
$tkquery="SELECT * FROM tokens_$sid";
if (!$tkresult=mysql_query($tkquery))
	{
	if (!$createtable)
		{
		echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>$setfont<BR><BR>No token system has been created for this survey. Do you want to create one now?";
		echo "<BR><BR><INPUT TYPE='SUBMIT' $btstyle VALUE='Create Token Table' onClick=\"window.open('tokens.php?sid=$sid&createtable=Y', '_top')\"><BR>";
		echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='Return to Admin' onClick=\"window.open('admin.php?sid=$sid', '_top')\">";
		echo "</TD></TR>\n";
		echo "</TABLE>\n";
		exit;
		}
	else
		{
		$createtokentable="CREATE TABLE tokens_$sid (\n  tid int NOT NULL auto_increment,\n  firstname varchar(40) NULL,  lastname varchar(40) NULL,\n  email varchar(100) NULL,  token varchar(10) NULL,\n sent varchar(1) NULL DEFAULT 'N', completed varchar(1) NULL DEFAULT 'N',  PRIMARY KEY (tid)\n) TYPE=MyISAM;";
		$ctresult=mysql_query($createtokentable) or die ("Completely mucked up<BR>$createtokentable<BR><BR>".mysql_error());
		echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>$setfont<BR><BR>A token table has been created for this survey, called \"tokens_$sid\"";
		echo "<BR><BR><INPUT TYPE='SUBMIT' $btstyle VALUE='Continue' onClick=\"window.open('tokens.php?sid=$sid', '_top')\">";
		echo "</TD></TR>\n";
		echo "</TABLE>\n";
		exit;
		
		}
	}

// IF WE MADE IT THIS FAR, THEN THERE IS A TOKENS TABLE, SO LETS DEVELOP THE MENU ITEMS

$tokenmenu = "<TR><TD BGCOLOR='#EEEEEE' COLSPAN='2' ALIGN='CENTER' STYLE='border-top-style: solid; border-top-width: 1; border-top-color:#BBBBBB; border-left-color:#BBBBBB; border-left-width:1; border-left-style:solid; border-right-style: solid; border-right-width:1; border-right-color:#BBBBBB'>";
$tokenmenu .="[<a href='admin.php?sid=$sid'>admin</a>] ";
$tokenmenu .="[<a href='tokens.php?sid=$sid&action=browse'>browse</a>] ";
$tokenmenu .="[<a href='tokens.php?sid=$sid&action=add'>add</a>] ";
$tokenmenu .="[<a href='tokens.php?sid=$sid&action=import'>import</a>] ";
$tokenmenu .="[<a href='tokens.php?sid=$sid&action=email'>invite</a>] ";
$tokenmenu .="[<a href='tokens.php?sid=$sid&action=tokenify'>tokenify</a>] ";
$tokenmenu .="[<a href='tokens.php?sid=$sid&action=kill'>drop tokens</a>] ";
$tokenmenu .="</TD></TR>\n";

// SEE HOW MANY RECORDS ARE IN THE TOKEN TABLE
$tkcount=mysql_num_rows($tkresult);
echo "$tokenmenu";
echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>There are $tkcount records in your token table for this survey.</TD></TR>\n";

// GIVE SOME INFORMATION ABOUT THE TOKENS
echo "<TR><TD COLSPAN='2' ALIGN='CENTER'><TABLE WIDTH='400' ALIGN='CENTER' BGCOLOR='#DDDDDD'><TR><TD ALIGN='CENTER'>";
$tksq="SELECT count(*) FROM tokens_$sid WHERE sent='Y'";
$tksr=mysql_query($tksq);
while ($tkr=mysql_fetch_row($tksr))
	{echo "$setfont$tkr[0] of $tkcount have been sent an invitation to participate<BR>";}
$tksq="SELECT count(*) FROM tokens_$sid WHERE completed='Y'";
$tksr=mysql_query($tksq);
while ($tkr=mysql_fetch_row($tksr))
	{echo "$setfont$tkr[0] of $tkcount entries have completed the survey<BR>";}
$tksq="SELECT count(*) FROM tokens_$sid WHERE token IS NULL OR token=''";
$tksr=mysql_query($tksq);
while ($tkr=mysql_fetch_row($tksr))
	{echo "$setfont$tkr[0] of $tkcount have not had a token generated";}
echo "</TD></TR></TABLE>\n";





if ($action == "browse")
	{
	echo "<BR><TABLE WIDTH='600' CELLPADDING='1' CELLSPACING='1' ALIGN='CENTER' BGCOLOR='#CCCCCC'>\n";
	//COLUMN HEADINGS
	echo "<TR><TD><A HREF='tokens.php?sid=$sid&action=browse&order=tid'><IMG SRC='DownArrow.gif' BORDER='0' ALIGN='LEFT'></a>$setfont<B>ID</TD>";
	echo "<TD><A HREF='tokens.php?sid=$sid&action=browse&order=firstname'><IMG SRC='DownArrow.gif' BORDER='0' ALIGN='LEFT'></a>$setfont<B>First</TD>";
	echo "<TD><A HREF='tokens.php?sid=$sid&action=browse&order=lastname'><IMG SRC='DownArrow.gif' BORDER='0' ALIGN='LEFT'></a>$setfont<B>Last</TD>";
	echo "<TD><A HREF='tokens.php?sid=$sid&action=browse&order=email'><IMG SRC='DownArrow.gif' BORDER='0' ALIGN='LEFT'></a>$setfont<B>Email</TD>";
	echo "<TD><A HREF='tokens.php?sid=$sid&action=browse&order=token'><IMG SRC='DownArrow.gif' BORDER='0' ALIGN='LEFT'></a>$setfont<B>Token</TD>";
	echo "<TD><A HREF='tokens.php?sid=$sid&action=browse&order=sent%20desc'><IMG SRC='DownArrow.gif' BORDER='0' ALIGN='LEFT'></a>$setfont<B>Invite?</TD>";
	echo "<TD><A HREF='tokens.php?sid=$sid&action=browse&order=completed%20desc'><IMG SRC='DownArrow.gif' BORDER='0' ALIGN='LEFT'></a>$setfont<B>Complete?</TD>";
	echo "<TD>$setfont<B>Action</TD></TR>\n";
	$bquery="SELECT * FROM tokens_$sid";
	if (!$order) {$bquery .= " ORDER BY tid";}
	else {$bquery .= " ORDER BY $order";}
	$bresult=mysql_query($bquery);
	while ($brow=mysql_fetch_row($bresult))
		{
		if ($bgc=="#EEEEEE") {$bgc="#DDDDDD";} else {$bgc="#EEEEEE";}
		echo "<TR BGCOLOR='$bgc'>";
		for ($i=0; $i<=6; $i++)
			{
			echo "<TD>$setfont$brow[$i]</TD>";
			}
		echo "<TD ALIGN='CENTER'><INPUT STYLE='height: 14; font-size: 8; font-face: verdana' TYPE='SUBMIT' VALUE='E' TITLE='Edit' onClick=\"window.open('$PHP_SELF?sid=$sid&action=edit&tid=$brow[0]', '_top')\">";
		echo "<INPUT STYLE='height: 14; font-size: 8; font-face: verdana' TYPE='SUBMIT' VALUE='D' TITLE='Delete' onClick=\"window.open('$PHP_SELF?sid=$sid&action=delete&tid=$brow[0]', '_top')\">";
		echo "</TD>";
		echo "</TR>\n";
		}
	}

if ($action == "kill")
	{
	$date = date(Ymd);
	echo "<CENTER>$setfont<B>Drop/Delete Tokens</B><BR>\n";
	if (!$ok)
		{
		echo "<TABLE WIDTH='80%' ALIGN='CENTER' BGCOLOR='#DDDDDD'>\n";
		echo " <TR><TD ALIGN='CENTER'>";
		echo "Deleting this tokens table will mean that tokens are no longer<BR>";
		echo "required for public access to this survey. It will also delete<BR>";
		echo "all the existing tokens in this survey. A backup of this table<BR>";
		echo "will be made, and called \"old_tokens_$sid\". This can be<BR>";
		echo "recovered by a systems administrator.<BR><BR>";
		echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='Delete Tokens' onClick=\"window.open('tokens.php?sid=$sid&action=kill&ok=surething', '_top')\"><BR>\n";
		echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='Cancel' onClick=\"window.open('tokens.php?sid=$sid', '_top')\">";
		echo " </TD></TR></TABLE>\n";
		}
	elseif ($ok == "surething")
		{
		$oldtable="tokens_{$sid}";
		$newtable="old_tokens_{$sid}_{$date}";
		$deactivatequery = "RENAME TABLE $oldtable TO $newtable";
		$deactivateresult = mysql_query($deactivatequery) or die ("Couldn't deactivate because:<BR>".mysql_error()."<BR><BR><a href='$scriptname?sid=$sid'>Admin</a>");
		echo "<TABLE WIDTH='80%' ALIGN='CENTER' BGCOLOR='#DDDDDD'>\n";
		echo " <TR><TD ALIGN='CENTER'>";
		echo "The tokens table has now been removed and tokens are no longer<BR>";
		echo "required for public access to this survey. A backup of this table<BR>";
		echo "has been made, and is called \"old_tokens_$sid_$date\". This can be<BR>";
		echo "recovered by a systems administrator.<BR><BR>";
		echo "<INPUT TYPE='SUBMIT' $btstyle VALUE='Finished' onClick=\"window.open('tokens.php?sid=$sid', '_top')\">";
		echo " </TD></TR></TABLE>\n";
			
		}		
	}	


if ($action == "email")
	{
	echo "<CENTER>$setfont<B>Email Invitation</B><BR>\n";
	if (!$ok)
		{
		//GET SURVEY DETAILS
		$esquery="SELECT * FROM surveys WHERE sid=$sid";
		$esresult=mysql_query($esquery);
		while ($esrow=mysql_fetch_row($esresult))
			{
			$surveyname=$esrow[1];
			$surveydescription=$esrow[2];
			$surveyadmin=$esrow[3];
			$surveyadminemail=$esrow[7];
			}
		echo "<TABLE WIDTH='80%' ALIGN='CENTER' BGCOLOR='#DDDDDD'>\n";
		//echo "<FORM METHOD='POST'>\n";
		echo "<FORM>\n";
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
		echo "<B>You can make changes to this part of the message:</B><BR>";
		echo "<TEXTAREA NAME='message' ROWS='6' COLS='60'>";
		echo "You have been invited to participate in the following survey.\n\n";
		echo "** Survey Name **\n$surveyname\n\n";
		echo "** Survey Description **\n$surveydescription\n\n";
		echo "To participate, please click on the link below.";
		echo "\n\nSincerely,\n\n$surveyadmin ($surveyadminemail)";
		echo "</TEXTAREA></TD></TR>\n";
		echo " <TR>\n  <TD></TD>";
		echo "  <TD>$setfont<B>The following will be added to the end of your email message:</B><BR>";
		echo "<TABLE WIDTH='500' BGCOLOR='#EEEEEE' BORDER='1' CELLPADDING='0' CELLSPACING='0'><TR><TD>";
		echo "$setfont---------------------------------<BR> Click Here to do Survey:<BR>$publicurl/index.php?sid=$sid&token=[TOKENVALUE]<BR>";
		echo "</TD></TR></TABLE>\n</TD></TR>\n";
		echo " <TR>";
		echo "  <TD COLSPAN='2' ALIGN='CENTER'><INPUT TYPE='SUBMIT' $btstyle VALUE='Send Invitations'></TD></TR>\n";
		echo "<INPUT TYPE='HIDDEN' NAME='ok' VALUE='absolutely'>\n";
		echo "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
		echo "<INPUT TYPE='HIDDEN' NAME='action' VALUE='email'\n";
		echo "</FORM></TABLE>\n";
		}
	else
		{
		echo "Sending email!";
		$ctquery="SELECT firstname FROM tokens_$sid WHERE completed !='Y' AND sent !='Y' AND token !=''";
		$ctresult=mysql_query($ctquery);
		$ctcount=mysql_num_rows($ctresult);
		$emquery="SELECT firstname, lastname, email, token, tid FROM tokens_$sid WHERE completed != 'Y' AND sent != 'Y' AND token !='' LIMIT $maxemails";
		$emresult=mysql_query($emquery) or die ("Couldn't do query.<BR>$emquery<BR>".mysql_error());
		$emcount=mysql_num_rows($emresult);
		$headers = "From: $from\r\n";
		$headers .= "X-Mailer: $sitename Email Inviter";  
		echo "<TABLE WIDTH='500' ALIGN='CENTER' BGCOLOR='#EEEEEE'><TR><TD><FONT SIZE='1'>\n";
		if ($emcount > 0)
			{
			while ($emrow=mysql_fetch_row($emresult))
				{
				$to=$emrow[2];
				//echo "To: $to ($emrow[0] $emrow[1])<BR>";
				//$from=$surveyadminemail;
				//echo "From: $from<BR>";
				//echo "Subject: $subject<BR>";
				$sendmessage = "Dear $emrow[0],\n\n".$message;
				$sendmessage .= "\n\n-------------------------------------------\n\n";
				$sendmessage .= "Click here to do this survey:\n\n";
				$sendmessage .= "$publicurl/index.php?sid=$sid&token=$emrow[3]\n\n";
				//echo "Message:". str_replace("\n", "<BR>", $sendmessage) . "<P>";
				mail($to, $subject, $sendmessage, $headers);
				$udequery = "UPDATE tokens_$sid SET sent='Y' WHERE tid=$emrow[4]";
				$uderesult=mysql_query($udequery) or die ("Couldn't update tokens<BR>$udequery<BR>".mysql_error());
				echo "[Invite Sent to $emrow[0] $emrow[1]] ";
				}
			if ($ctcount > $emcount)
				{
				$lefttosend=$ctcount-$maxemails;
				echo "</TD></TR><TR><FORM METHOD='POST'><TD ALIGN='CENTER'>$setfont<B>Warning:</B><BR>";
				echo "The number of emails to send ($ctcount) is greater than the maximum number";
				echo " of emails that can be sent in one lot ($maxemails). There are still $lefttosend";
				echo " emails to go. You can continue sending the next $maxemails by clicking on the";
				echo " button below.<BR>";
				echo "<INPUT TYPE='SUBMIT' VALUE='Send More'></TD>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='ok' VALUE='absolutely'>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='action' VALUE='email'>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='from' VALUE='$from'>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='subject' VALUE='$subject'>\n";
				echo "<INPUT TYPE='HIDDEN' NAME='message' VALUE='$message'>\n";
				echo "</FORM>\n";
				}
			}
		else
			{
			echo "<CENTER><B>WARNING:</B><BR>There were no token recipients who have not already had";
			echo " an invitation sent out, or who have not responded!<BR><BR>";
			echo "No invitations have been sent out!";
			}
		
		echo "</TD></TR></TABLE>\n";
		}
	}	
	
	
if ($action == "tokenify")
	{
	echo "<CENTER>$setfont<B>Tokens</B><BR>\n";
	if (!$ok)
		{
		echo "<CENTER><BR>$setfont Clicking OK will generate tokens for all<BR>those in this token list that have not<BR>been issued one. Is this OK?";
		echo "<BR><INPUT TYPE='SUBMIT' $btstyle VALUE='Yes' onClick=\"window.open('tokens.php?sid=$sid&action=tokenify&ok=Y', '_top')\">";
		echo "<BR><INPUT TYPE='SUBMIT' $btstyle VALUE='No' onClick=\"window.open('tokens.php?sid=$sid', '_top')\">";
		}
	else
		{
		$tkquery="SELECT * FROM tokens_$sid WHERE token IS NULL OR token=''";
		$tkresult=mysql_query($tkquery) or die ("Mucked up!<BR>$tkquery<BR>".mysql_error());
		while ($tkrow=mysql_fetch_row($tkresult))
			{
			$insert="NO";
			while ($insert!="OK")
				{
				$newtoken=sprintf("%010s", rand(1,10000000000));
				$ntquery="SELECT * FROM tokens_$sid WHERE token='$newtoken'";
				$ntresult=mysql_query($ntquery);
				if (!mysql_num_rows($ntresult)) {$insert="OK";}
				}
			$itquery="UPDATE tokens_$sid SET token='$newtoken' WHERE tid=$tkrow[0]";
			$itresult=mysql_query($itquery);
			$newtokencount++;
			}
		echo "<BR><BR><B>$newtokencount tokens have been generated";
		}
	}
if ($action == "delete")
	{
	$dlquery="DELETE FROM tokens_$sid WHERE tid=$tid";
	$dlresult=mysql_query($dlquery) or die ("Couldn't delete record $tid<BR>".mysql_error());
	echo "<BR><B>Record has been deleted.";
	}

	
	
if ($action == "edit" || $action == "add")
	{
	if ($action == "edit")
		{
		$edquery="SELECT * FROM tokens_$sid WHERE tid=$tid";
		$edresult=mysql_query($edquery);
		while($edrow=mysql_fetch_row($edresult))
			{
			$id=$edrow[0]; $firstname=$edrow[1]; $lastname=$edrow[2]; $email=$edrow[3]; $token=$edrow[4]; $sent=$edrow[5]; $completed=$edrow[6];
			}
		}
	echo "<BR><TABLE WIDTH='550' BGCOLOR='#CCCCCC'>\n";
	echo "<FORM METHOD='POST'>\n";
	echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>$setfont<B>Edit/Add Token Entry</TD></TR>\n";
	echo "<TR><TD ALIGN='RIGHT' WIDTH='10%'>$setfont<B>ID:</TD><TD BGCOLOR='#EEEEEE'>$setfont Auto</TD></TR>\n";
	echo "<TR><TD ALIGN='RIGHT' WIDTH='10%'>$setfont<B>Firstname:</TD><TD BGCOLOR='#EEEEEE'>$setfont<INPUT TYPE='TEXT' $slstyle NAME='firstname' VALUE='$firstname'></TD></TR>\n";
	echo "<TR><TD ALIGN='RIGHT' WIDTH='10%'>$setfont<B>Lastname:</TD><TD BGCOLOR='#EEEEEE'>$setfont<INPUT TYPE='TEXT' $slstyle NAME='lastname' VALUE='$lastname'></TD></TR>\n";
	echo "<TR><TD ALIGN='RIGHT' WIDTH='10%'>$setfont<B>Email:</TD><TD BGCOLOR='#EEEEEE'>$setfont<INPUT TYPE='TEXT' $slstyle NAME='email' VALUE='$email'></TD></TR>\n";
	echo "<TR><TD ALIGN='RIGHT' WIDTH='10%'>$setfont<B>Token:</TD><TD BGCOLOR='#EEEEEE'>$setfont<INPUT SIZE='10' $slstyle TYPE='TEXT' NAME='token' VALUE='$token'></TD></TR>\n";
	echo "<TR><TD ALIGN='RIGHT' WIDTH='10%'>$setfont<B>Sent?:</TD><TD BGCOLOR='#EEEEEE'>$setfont<INPUT SIZE='1' $slstyle TYPE='TEXT' NAME='sent' VALUE='$sent'></TD></TR>\n";
	echo "<TR><TD ALIGN='RIGHT' WIDTH='10%'>$setfont<B>Complete?:</TD><TD BGCOLOR='#EEEEEE'>$setfont<INPUT SIZE='1' TYPE='TEXT' $slstyle NAME='completed' VALUE='$completed'></TD></TR>\n";
	echo "<TR><TD COLSPAN='2' ALIGN='CENTER'>";
	switch($action)
		{
		case "edit":
			echo "<INPUT TYPE='SUBMIT' $btstyle NAME='action' VALUE='update'>\n";
			echo "<INPUT TYPE='HIDDEN' NAME='tid' VALUE='$tid'>\n";
			break;
		case "add":
			echo "<INPUT TYPE='SUBMIT' $btstyle NAME='action' VALUE='insert'>\n";
			break;
		}
	echo "<INPUT TYPE='HIDDEN' NAME='sid' VALUE='$sid'>";
	echo "</TD></TR>\n</FORM>\n</TABLE>\n";
	}


if ($action == "update")
	{
	echo "<BR>$setfont<B>UPDATING TOKEN ENTRY</B><BR>\n";
	$udquery="UPDATE tokens_$sid SET firstname='$firstname', lastname='$lastname', email='$email', token='$token', sent='$sent', completed='$completed' WHERE tid=$tid";
	$udresult=mysql_query($udquery) or die ("Update record $tid failed:<BR>$udquery<BR>".mysql_error());
	echo "<BR>Entry succesfully updated!";
	}


if ($action == "insert")
	{
	echo "<BR>$setfont<B>INSERTING TOKEN ENTRY</B><BR>\n";
	$inquery = "INSERT into tokens_$sid VALUES ('', '$firstname', '$lastname', '$email', '$token', '$sent', '$completed')";
	$inresult=mysql_query($inquery) or die ("Add new record failed:<BR>$inquery<BR>".mysql_error());
	echo "<BR>Entry succesfully added!";
	}


if ($action == "import") 
	{
	form();
	echo "<TABLE WIDTH='400' BGCOLOR='#EEEEEE'><TR><TD ALIGN='CENTER'>";
	echo "<FONT SIZE='1'><B>Note:</B><BR>File should be a standard comma delimited file with no quotes in the form of:<BR><BR>";
	echo "<I>Firstname, Lastname, Email, Token</I></TD></TR></TABLE>\n";
	}


if ($action == "upload") 
	{
	$the_path="$homedir";
	$the_full_file_path=$homedir."/".$the_file_name;
    if (!@copy($the_file, $the_path . "/" . $the_file_name)) 
		{
		form("\n<b>Something went horribly wrong, check the path to and ".
		"the permissions for the upload directory</b>");
		}
		else
		{
		echo "\n<BR><B>IMPORTING FILE</B><BR>File succesfully uploaded<BR><BR>";
		echo "\nReading File...<BR>";
		$handle=fopen($the_full_file_path, "r");
		while (!feof($handle))
			{
			$buffer=fgets($handle);
			//echo "$xx:".$buffer."<BR>";
			if (!$xx)
				{
				//THIS IS THE FIRST LINE. IT IS THE HEADINGS. IGNORE IT
				}
			else
				{
				$line=explode(",",$buffer);
				$elements=count($line); 
				if ($elements > 1)
					{
					$xy=0;
					foreach($line as $el)
						{
						//echo "[$el]($xy)<BR>";
						if ($xy<$elements)
							{
							if ($xy==0){$firstname=$el;}
							if ($xy==1){$lastname=$el;}
							if ($xy==2){$email=$el;}
							if ($xy==3){$token=$el;}
							}
						$xy++;
						}
					//CHECK FOR DUPLICATES?
					$iq="INSERT INTO tokens_$sid VALUES('','$firstname', '$lastname', '$email', '$token', '', '')";
					$ir=mysql_query($iq) or die ("Couldn't insert line<BR>$iq<BR>".mysql_error());
					$xz++;
					}
				}
			$xx++;
			}
		echo "\nProcess completed. $xz records added.<BR>";
		fclose($handle);
		unlink($the_full_file_path);
		}

	}

//echo "ACTION: $action<BR>THEFILE: $the_file<BR>THEFILENAME: $the_file_name";
echo "</TD></TR>\n</TABLE>\n";


function form($error=false) {

global $PHP_SELF, $sid, $btstyle, $slstyle, $setfont;

    if ($error) print $error . "<br><br>";
    
    print "\n<form ENCTYPE=\"multipart/form-data\"  action=\"" . $PHP_SELF . "\" method=\"post\">";
    print "\n<INPUT TYPE=\"hidden\" name=\"action\" value=\"upload\">";
	print "\n<INPUT TYPE=\"hidden\" name=\"sid\" value=\"$sid\">";
    print "\n<P>$setfont Upload a file";
    print "\n<br><INPUT $slstyle NAME=\"the_file\" TYPE=\"file\" SIZE=\"35\"><br>";
    print "\n<input type=\"submit\" $btstyle Value=\"Upload\">";
    print "\n</form>";

} # END form

?>