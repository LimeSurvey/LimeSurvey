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

session_start();

if ($_GET['sid']) {$_SESSION['sid'] = $_GET['sid'];}
if ($_POST['sid']) {$_SESSION['sid'] = $_POST['sid'];}
$sid = $_SESSION['sid'];
if ($_GET['token']) {$_SESSION['token'] = $_GET['token'];}
if ($_POST['token']) {$_SESSION['token'] = $_POST['token'];}
$token = $_SESSION['token'];
if ($_GET['move']) {$move = $_GET['move'];}
if ($_POST['move']) {$move = $_POST['move'];}
if ($_POST['fvalue']) {$fvalue = $_POST['fvalue'];}
if ($_POST['fvalue1']) {$fvalue1 = $_POST['fvalue1'];}
if ($_POST['multi']) {$multi = $_POST['multi'];}
if ($_POST['thisstep']) {$thisstep = $_POST['thisstep'];}
//$thisstep = $_POST['thisstep']; if (!$thisstep) {$thisstep=$_SESSION['thisstep'];}
#$totalsteps = $_SESSION['totalsteps'];
#$fieldarray = $_SESSION['fieldarray'];
#$insertarray = $_SESSION['insertarray'];
if ($_POST['lastgroupname']) {$lastgroupname = $_POST['lastgroupname'];}
if ($_POST['newgroup']) {$newgroup = $_POST['newgroup'];}
if ($_POST['lastfield']) {$lastfield = $_POST['lastfield'];}

if ($move == "clearall" || $move == "here" || $move == "completed") 
	{
	session_unset();
	session_destroy();
	}

if ($fvalue) 
	{
	if ($fvalue == " ")
		{
		//$$lastfield = "";
		$_SESSION[$lastfield] = "";
		}
	else
		{
		//$$lastfield = $fvalue;
		$_SESSION[$lastfield] = $fvalue;
		}
	}

if ($multi)
	{
	$myfields = explode("|", $lastfield);
	for ($i=1; $i<=$multi; $i++)
		{
		$mylist = "fvalue$i";
		$arrayno = $i-1;
		$_SESSION[$myfields[$arrayno]] = $_POST[$mylist];
		//echo "$mylist: " . $_POST[$mylist] . " (session: " . $myfields[$arrayno] . ")<br />";
		}
	$mylist = substr($mylist, 0, strlen($mylist)-1);
	}

if ($move == " << prev " && $newgroup != "yes") {$_SESSION['step'] = $thisstep-1;} else {$_SESSION['step'] = $thisstep;}
if ($move == " next >> ") {$_SESSION['step'] = $thisstep+1;}
if ($move == " last ") {$_SESSION['step'] = $thisstep+1;}

include("./admin/config.php");

//if ($sid != $_GET['sid'] && $sid != $_POST['sid']){$sid = $_GET['sid'];}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
                                                     // always modified
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0


echo "<html>\n<head>\n<title>$sitename</title>\n</head>\n<body>\n<font face='Verdana'>\n";

//FIRST, LETS HANDLE SOME CONTINGENCIES

if (!$sid && (!$move == "clearall" || !$move=="here"))
	{
	echo "<center><b>$sitename</b><br />\n<br />\n<b>You cannot access this website without a valid Survey ID code.</b><br />\n";
	echo "<br />\nPlease contact $siteadminemail for information.";
	echo "</body>\n</html>";
	exit;
	}

if (!mysql_selectdb ($databasename, $connect))
	{
	echo "<center><b>$sitename<br />\n<br />\n<font color='red'>ERROR</font></b><br />\n<br />\n";
	echo "This system has not yet been installed properly.<br />\n";
	echo "Contact your $siteadminemail for information";
	echo "</body>\n</html>";
	exit;
	}

// NOW LETS GATHER SOME INFORMATION ABOUT THIS PARTICULAR SURVEY (This happens on every page)
if ($sid)
	{
	$desquery = "SELECT * FROM surveys WHERE sid=$sid";
	$desresult = mysql_query($desquery) or die ("Couldn't get survey with sid of $sid<br />$desquery<br />".mysql_error());
	$descount = mysql_num_rows($desresult);
	while ($desr = mysql_fetch_array($desresult)) {$expirydate = $desr['expires'];}
	if ($descount == 0) 
		{
		echo "There is no survey with that SID. Sorry. [$descount][$desquery]";
		echo "</body>\n</html>";
		exit;
		}
	elseif ($expirydate < date("Y-m-d") && $expirydate != "0000-00-00")
		{
		echo "<center><b>$sitename<br />\n<br />\n<font color='red'>ERROR</font></b><br />\n<br />\n";
		echo "Sorry. This survey has expired and is no longer available.<br />\n(Expiry date $expirydate)";
		echo "</body>\n</html>";
		exit;
		}
	$desresult = mysql_query($desquery);
	while ($desrow = mysql_fetch_array($desresult))
		{
		$surveyname = $desrow['short_title'];
		$surveydesc = $desrow['description'];
		$surveyactive = $desrow['active'];
		$surveytable = "survey_{$desrow['sid']}";
		$surveywelcome = $desrow['welcome'];
		$surveyadminname = $desrow['admin'];
		$surveyadminemail = $desrow['adminemail'];
		$surveyprivate = $desrow['private'];
		}
	$surveyheader = "<table width='95%' align='center' style='border-collapse: collapse; border: 1px solid #111111'>\n";
	$surveyheader .= "\t<tr>\n";
	$surveyheader .= "\t\t<td colspan='2' bgcolor='silver' align='center' valign='middle' style='padding: 1em 1em 1em 1em'>\n";
	$surveyheader .= "\t\t\t<font color='#000080' size='4'><b>$surveyname</b></font><br />\n";
	$surveyheader .= "\t\t\t<font size='1' color='#444444'>$surveydesc\n";
	$surveyheader .= "\t\t</td>\n";
	$surveyheader .= "\t</tr>\n";	
	
	//LETS SEE IF THERE ARE TOKENS FOR THIS SURVEY
	$i = 0; $tokensexist = 0;
	$tresult = @mysql_list_tables($databasename);
	while($tbl = @mysql_tablename($tresult, $i++))
		{
		if ($tbl == "tokens_$sid") {$tokensexist = 1;}
		}
	}

//THIS CLEARS ALL DATA WHEN CLEARALL OR FINISH HAS BEEN CHOSEN
if ($move == "clearall" || $move == "here")
	{
	$fieldname = "";
	$_SESSION['fieldarray'] = "";
	$_SESSION['step'] = "";
	$_SESSION['totalsteps'] = "";
	$_SESSION['token'] = "";
	echo "<br />\n&nbsp;<br />\n";
	echo "<center>All data has been deleted.<br />\n&nbsp;<br />\n";
	echo "<a href='javascript:window.close()'>Close</a><br />\n<br />\n&nbsp;$sid</center>\n";
	echo "</body>\n</html>";
	exit;
	}


// This is the LAST POINT. The survey is completed and saved and now we're just letting the user close the window
if ($move == "completed")
	{
	echo "<table width='95%' align='center' style='border-collapse: collapse; border: 1px solid #111111'>\n";
	echo "\t<tr>\n";
	echo "\t\t<td colspan='2' bgcolor='silver' align='center'>\n";
	echo "\t\t\t<font color='#000080' size='4'><b>$sitename</b></font><br />\n";
	echo "\t\t\t<font color='#444444' size='1'>&nbsp;\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";	
	echo "\t<tr>\n";
	echo "\t\t<td colspan='2' align='center'>\n";
	echo "\t\t\t$setfont<br />\nThis is the \"$sitename\" Survey site.<br />\n";
	echo "\t\t\t<br />\n";
	echo "\t\t\t<a href='javascript: window.close()'>Close Window</a><br />\n";
	echo "\t\t\t<br />\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "<table>\n";
	echo "</body>\n</html>";
	exit;
	}

// Here we present the user with the option to submit their responses and provide general information about stopping, and privacy (if appropriate)
if ($move == " last ")
	{
	echo $surveyheader;
	$s = $_SESSION['step']-1;
	$t = $s-1;
	$u = $_SESSION['totalsteps'];
	$chart = 105;
	echo "\t<tr>\n";
	echo "\t\t<td colspan='2' align='center' bgcolor='#EEEEEE'>\n";
	echo "\t\t\tSurvey Complete<br />\n";
	echo "\t\t\t<table width='175' align='center' style='border-collapse: collapse; border: 1px solid #111111'>\n";
	echo "\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t<td width='35' align='right'><font size='1'>0%</td>\n";
	echo "\t\t\t\t\t<td width='105'><img src='chart.jpg' height='15' width='$chart'></td>\n";
	echo "\t\t\t\t\t<td width='35'><font size='1'>100%</td>\n";
	echo "\t\t\t\t</tr>\n";
	echo "\t\t\t</table>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "<form method='post'>\n";
	echo "<input type='hidden' name='sid' value='$sid' />\n";
	echo "<input type='hidden' name='thisstep' value='{$_SESSION['step']}' />\n";
	echo "\t<tr>\n";
	echo "\t\t<td>\n";
	echo "\t\t\t<table border='0' width='100%'>\n";
	echo "\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t<td>&nbsp;</td>\n";
	echo "\t\t\t\t\t<td align='center' width='500'>\n";
	echo "$setfont<p><b>Congratulations. You have completed answering the questions in this survey.</b>\n";
	echo "<p>Click on \"Submit\" now to complete the process and submit your answers to our records. ";
	echo "If you want to check any of the answers you have made, and/or change them, you can do that now by ";
	echo "clicking on the \" << prev \" button and browsing through your responses.<br />\n";
	echo "&nbsp;<br />\n";
	echo "<input type='submit' value=' submit ' name='move' /><br />&nbsp;\n";
	if ($surveyprivate != "N")
		{
		echo "\t\t\t\t\t\t<table align='center' width='400' bgcolor='#EFEFEF' border='0'>\n";
		echo "\t\t\t\t\t\t\t<tr>\n";
		echo "\t\t\t\t\t\t\t\t<td align='center'>\n";
		echo "$setfont<b>A note on privacy</b><br />\n";
		echo "<font size='1'>The record kept of this survey does not contain any identifying information about you unless ";
		echo "a specific question in the survey has asked for this. If you have responded to a survey that ";
		echo "used an identifying token to allow you to access the survey, you can rest assured that the ";
		echo "identifying token is not kept with your responses. It is managed in a seperate database, and will ";
		echo "only be updated to indicate that you have (or haven't) completed this survey. There is no way of ";
		echo "relating identification tokens with responses in this system.\n";
		echo "\t\t\t\t\t\t\t\t</td>\n";
		echo "\t\t\t\t\t\t\t</tr>\n";
		echo "\t\t\t\t\t\t</table>\n";
		}
	else
		{
		// Just in case we want to add a comment for non-private surveys
		}
	echo "<font size='1'>&nbsp;<br />\nIf you do not wish to submit responses to this survey, ";
	echo "and you would like to delete all records on your computer that may have saved your responses, ";
	echo "click <a href='index.php?move=clearall&sid=$sid'>here</a><br />&nbsp;\n";
	//echo "<input type='submit' name='move' value='here' style='height:15; font-size:9; font-family:verdana' onclick=\"window.open('index.php?clearall', '_top')\" />\n";
	echo "\t\t\t\t\t</td>\n";
	echo "\t\t\t\t\t<td>&nbsp;</td>\n";
	echo "\t\t\t\t</tr>\n";
	echo "\t\t\t</table>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo surveymover();
	echo "</table>\n";
	//debugging info
	echo "<!-- DEBUG INFO \n";
	foreach ($_SESSION['insertarray'] as $posted) 
		{
		echo "$posted: ".$_SESSION[$posted] ."\n";
		}
	echo "SID: $sid\n";
	echo "Token: $token\n";
	echo "-->\n";
	// end debugging info
	echo "</body>\n</html>";
	exit;
	}

//THIS IS THE SECOND LAST POINT. HERE, WE GATHER ALL THE SESSION VARIABLES AND INSERT THEM INTO THE DATABASE.
if ($move == " submit ")
	{
	echo "$surveyheader";
	//echo $surveyactive;
	echo "\t<tr>\n";
	echo "\t\t<td>\n";
	echo "\t\t\t<br />&nbsp;<br />\n";
	echo "\t\t\t<table width='250' align='center' style='border-collapse: collapse; border: 1px solid #111111'>\n";
	echo "\t\t\t\t<tr>\n";
	echo "\t\t\t\t\t<td colspan='2' align='center' bgcolor='#CCCCCC'>\n";
	echo "\t\t\t\t\t\t<br /><b>Results are being submitted...<br /><br />\n";
	echo "\t\t\t\t\t</td>\n";
	echo "\t\t\t\t</tr>\n";
	$subquery = "INSERT INTO $surveytable ";
	foreach ($_SESSION['insertarray'] as $value)
		{
		$col_name .= ", " . substr($value, 1); //Add separator and strip off leading 'F'
		if (get_magic_quotes_gpc() == "0")
			{
			if (phpversion() >= "4.3.0")
				{
				$values .= ", '" . mysql_real_escape_string($_SESSION[$value], "'") . "'";	
				}
			else
				{
				$values .= ", '" . mysql_escape_string($_SESSION[$value]) . "'";
				}
			}
		else
			{
			$values .= ", '" . $_SESSION[$value] . "'";
			}
		//echo "$value<br />\n"; //Debugging info
		}
	$col_name = substr($col_name, 2); //Strip off first comma & space
	$values = substr($values, 2); //Strip off first comma & space
	$subquery .= "\n($col_name) \nVALUES \n($values)";
	//echo "<pre style='text-align: left'>$subquery</pre>\n"; //Debugging info
	
	if ($surveyactive == "Y")
		{
		$subresult = mysql_query($subquery) or die ("</table>\n</td>\n</tr>\n</table>\nCouldn't update $surveytable<br />\n".mysql_error()."<br />\n<pre>$subquery</pre>\n");
		echo "\t\t\t\t<tr>\n";
		echo "\t\t\t\t\t<td colspan='2' align='center' bgcolor='#EEEEEE'>\n";
		echo "\t\t\t\t\t\t<br /><font color='red'>Thank you!</font><br />\n";
		echo "\t\t\t\t\t\tResults have been successfully updated.<br />&nbsp;\n";
		echo "\t\t\t\t\t</td>\n";
		echo "\t\t\t\t</tr>\n";
		if ($token)
			{
			$utquery = "UPDATE tokens_$sid SET completed='Y' WHERE token='$token'";
			$utresult = mysql_query($utquery) or die ("Couldn't update tokens table!<br />\n$utquery<br />\n".mysql_error());
			
			//MAIL CONFIRMATION TO PARTICIPANT
			$cnfquery = "SELECT * FROM tokens_$sid WHERE token='$token' AND completed='Y'";
			$cnfresult = mysql_query($cnfquery);
			while ($cnfrow = mysql_fetch_array($cnfresult))
				{
				$headers = "From: $surveyadminemail\r\n";
				$headers .= "X-Mailer: $sitename Email Inviter";
				$to = $cnfrow['email'];
				$subject = "Confirmation: $surveyname Survey Completed";
				$message = "Dear {$cnfrow['firstname']},\n\n";
				$message .= "This email is to confirm that you have completed the survey titled \"$surveyname\" ";
				$message .= "and your response has been saved. Thank you for participating.\n\n";
				if ($surveyprivate != "N")
					{
					$message .= "Please note that your survey submission does not contain any link to your personal ";
					$message .= "information used to send you this confirmation or the original invitation.\n";
					$message .= "The information you submitted in the survey is anonymous unless a question in the ";
					$message .= "survey itself actually asks for such information.\n\n";
					}
				else
					{
					//just in case we want to add info about it not being private
					}
				$message .= "If you have any questions about this survey please contact $surveyadminname on ";
				$message .= "$surveyadminemail.\n\n";
				$message .= "Sincerely,\n\n";
				$message .= "$surveyadminname";
				if ($cnfrow['email']) {mail($to, $subject, $message, $headers);} //Only send confirmation email if there is an email address
				}
			}
		}
	else
		{
		echo "\t\t\t\t<tr>\n";
		echo "\t\t\t\t\t<td colspan='2' align='center' bgcolor='#EEEEEE'>\n";
		echo "\t\t\t\t\t\t<br /><font color='red'>Sorry!</font><br />\n";
		echo "\t\t\t\t\t\tCould not submit results - survey has not been activated<br />&nbsp;\n";
		echo "\t\t\t\t\t</td>\n";
		echo "\t\t\t\t</tr>\n";
		echo "\t\t\t\t<tr>\n";
		echo "\t\t\t\t\t<td><font size='1'>$subquery</td>\n";
		echo "\t\t\t\t</tr>\n";
		}
	echo "\t\t\t</table>\n";
	echo "\t\t\t<center><br /><a href='index.php?move=completed&sid=$sid'>Finish</a></center><br />\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	
	// debugging info
	echo "<!-- DEBUG INFO \n";
	foreach ($_SESSION['insertarray'] as $posted) 
		{
		echo "$posted: ".$_SESSION[$posted] ."\n";
		}
	echo "SID: $sid\n";
	echo "Token: $token\n";
	echo "-->\n";
	// end debugging info

	
	echo "</body>\n</html>";
	exit;
	}
	
// THIS IS FOR WHEN THE SURVEY SCRIPTS AND STUFF HAVEN'T STARTED YET
if (!$_SESSION['step'])
	{
	if ($tokensexist == 1 && !$token)
		{
		echo "<center><b>$sitename</b><br />\n<br />\n<b>You cannot access this website without a valid token.</b><br />\n";
		echo "Tokens are issued to invited participants. If you have been invited to participate in this<br />\n";
		echo "survey but have not got a token, please contact $siteadminemail for information.<br />\n&nbsp;";
		echo "<table align='center' bgcolor='#EEEEEE'>\n";
		echo "\t<tr>\n";
		echo "\t\t<form method='post'>\n";
		echo "\t\t<td align='center'>\n";
		echo "\t\t\tIf you have been issued a token, please enter it here to proceed:<br />\n";
		echo "\t\t\t<input type='text' size='10' name='token' /><br />\n";
		echo "\t\t\t<input type='submit' value='Go' />\n";
		echo "\t\t</td>\n";
		echo "\t<input type='hidden' name='sid' value='$sid' />\n";
		echo "\t</tr>\n";
		echo "\t</form>\n";
		echo "</table>\n";
		echo "</body>\n</html>";
		exit;
		}
	if ($tokensexist == 1 && $token)
		{
		//check if token actually does exist
		$tkquery = "SELECT * FROM tokens_$sid WHERE token='$token' AND completed != 'Y'";
		$tkresult = mysql_query($tkquery);
		$tkexist = mysql_num_rows($tkresult);
		if ($tkexist > 0)
			{
#			session_register("token"); //No need to register $token as long as it's passed via GET
			}
		else
			{
			echo "<center><b>$sitename</b><br />\n<br />\n<b>The token you have submitted has either been used or does not exist.</b><br />\n";
			echo "Tokens are issued to invited participants. If you have been invited to participate in this<br />\n";
			echo "survey but your token has failed, please contact $siteadminemail for more information.<br />\n&nbsp;";
			echo "<table align='center' bgcolor='#EEEEEE'>\n";
			echo "\t<tr><form method='post'>\n";
			echo "\t\t<td align='center'>\n";
			echo "\t\t\tIf you have been issued a token, please enter it here to proceed:<br />\n";
			echo "\t\t\t<input type='text' size='10' name='token' /><br />\n";
			echo "\t\t\t<input type='submit' value='Go' />\n";
			echo "\t\t</td>\n";
			echo "\t\t<input type='hidden' name='sid' value='$sid' />\n";
			echo "\t</form></tr>\n";
			echo "</table>\n";
			echo "</body>\n</html>";
			exit;
			}
		}
	echo "<table width='95%' align='center' style='border-collapse: collapse; border: 1px solid #111111'>\n";
	echo "\t<tr>\n";
	echo "\t\t<td colspan='2' bgcolor='silver' align='center'><font color='#000080'><b>$sitename</b></font></td>\n";
	echo "\t</tr>\n";	
	echo "\t<tr>\n";
	echo "\t\t<td colspan='2' bgcolor='#dddddd' align='center'><font size='4'><b>Welcome</b></font></td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td colspan='2' align='center'>&nbsp;<br />\n";
	echo "\t\t\t<b>$surveyname</b><br />\n";
	//echo "\t\t\t$setfont<i>$surveydesc</i><br />\n";
	echo "\t\t\t$surveywelcome<br />&nbsp;<br />\n";
	echo "\t\t\tClick \"Next\" to begin.<br />&nbsp;\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center' colspan='2' bgcolor='#DDDDDD'>\n";
	echo "\t\t\t$setfont There are {$_SESSION['totalsteps']} questions in this survey.\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<form method='post'>\n";
	echo "\t<input type='hidden' name='sid' value='$sid' />\n";
	echo "\t<input type='hidden' name='thisstep' value='{$_SESSION['step']}' />\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center' colspan='2'>\n";

#	session_register("fieldarray");
#	$_SESSION['step'] = $step; // session_register("step") causes really strange session behavior on PHP 4.3.0, Apache 2.0.43, WinXP
#	session_register("totalsteps");
#	session_register("insertarray");
#	session_register("sid");
	
	$aquery = "SELECT * FROM questions, groups WHERE questions.gid=groups.gid AND questions.sid=$sid ORDER BY group_name";
	$aresult = mysql_query($aquery);
	$_SESSION['totalsteps'] = mysql_num_rows($aresult);
	
	if ($_SESSION['totalsteps'] == "0")
		{
		//break out and crash if there are no questions!
		echo "$setfont<center><b>$sitename</b><br />\n<br />\n<b>This survey does not yet have any questions, and so cannot be accessed.</b><br />\n";
		echo "<br />\nPlease contact $siteadminemail for information.<br /><br />\n";
		echo "<a href=\"javascript:window.close()\">Close Window</a>\n";		
		exit;
		}
	
	$arows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
	while ($arow = mysql_fetch_assoc($aresult)) {$arows[] = $arow;} // Get table output into array
	
	// Perform a case insensitive natural sort on group name then question title of a multidimensional array
	usort($arows, 'CompareGroupThenTitle');
	
	if ($surveyprivate == "N")
		{
#		session_register("Ftoken");
#		$Ftoken=$token;
		$_SESSION['Ftoken'] = $token;
		$_SESSION['insertarray'][]= "Ftoken";
		}
	
	foreach ($arows as $arow)
		{
		//WE ARE CREATING A SESSION VARIABLE FOR EVERY FIELD IN THE SURVEY
		$fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}";
		if ($arow['type'] == "M" || $arow['type'] == "A" || $arow['type'] == "B" || $arow['type'] == "C" || $arow['type'] == "P")
			{
			$abquery = "SELECT answers.*, questions.other FROM answers, questions WHERE answers.qid=questions.qid AND sid=$sid AND questions.qid={$arow['qid']} ORDER BY answers.code";
			$abresult = mysql_query($abquery);
			while ($abrow = mysql_fetch_array($abresult))
				{
#				session_register("F$fieldname".$abrow['code']); //THE F HAS TO GO IN FRONT OF THE FIELDNAME SO THAT PHP RECOGNISES IT AS A VARIABLE
				$_SESSION['insertarray'][] = "F$fieldname".$abrow['code'];
				$alsoother = "";
				if ($abrow['other'] == "Y") {$alsoother = "Y";}
				if ($arow['type'] == "P") 
					{
#					session_register("F$fieldname".$abrow['code']."comment");
					$_SESSION['insertarray'][] = "F$fieldname".$abrow['code']."comment";	
					}
				}
			if ($alsoother) 
				{
#				session_register("F$fieldname"."other");
				$_SESSION['insertarray'][] = "F$fieldname"."other";
				if ($arow['type'] == "P")
					{
#					session_register("F$fieldname"."othercomment");
					$_SESSION['insertarray'][] = "F$fieldname"."othercomment";	
					}
				}
			
			}
		elseif ($arow['type'] == "O")
			{
#			session_register("F$fieldname");
			$_SESSION['insertarray'][] = "F$fieldname";
			$fn2 = "F$fieldname"."comment";
#			session_register("$fn2");
			$_SESSION['insertarray'][] = "$fn2";
			
			}
		else
			{
#			session_register("F$fieldname");
			$_SESSION['insertarray'][] = "F$fieldname";
			}
		//echo "F$fieldname, {$arow['title']}, {$arow['question']}, {$arow['type']}<br />\n"; //MORE DEBUGGING STUFF
		//NOW WE'RE CREATING AN ARRAY CONTAINING EACH FIELD
		//ARRAY CONTENTS - [0]=questions.qid, [1]=fieldname, [2]=questions.title, [3]=questions.question
		//                 [4]=questions.type, [5]=questions.gid
		$_SESSION['fieldarray'][] = array("{$arow['qid']}", "$fieldname", "{$arow['title']}", "{$arow['question']}", "{$arow['type']}", "{$arow['gid']}");
		}
	//echo count($_SESSION['fieldarray']);
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	//$_SESSION['step'] = 1;
	}

else
	{
	echo $surveyheader;
	//echo "STEP: $_SESSION['step'], TOTALSTEPS: {$_SESSION['totalsteps']}, LASTFIELD: $lastfield";
	$s = $_SESSION['step'];
	//$t indicates which question in the array we should be displaying
	$t = $s-1;
	$v = $t-1;
	$u = $_SESSION['totalsteps'];
	$chart = (($s-1)/$u*100);

	// GET AND SHOW GROUP NAME
	$gdquery = "SELECT group_name, groups.description FROM groups, questions WHERE groups.gid=questions.gid and qid={$_SESSION['fieldarray'][$t][0]}";
	$gdresult = mysql_query($gdquery);
	while ($gdrow = mysql_fetch_array($gdresult))
		{
		$currentgroupname = $gdrow['group_name'];
		echo "\t<tr>\n";
		echo "\t\t<td colspan='2' align='center' bgcolor='#DDDDDD'>\n";
		echo "\t\t\t$setfont<font color='#800000'><b>$currentgroupname</b><br />&nbsp;\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		$groupdescription = $gdrow['description'];
		}

	//if (($currentgroupname != $lastgroupname) && ($move != " << prev "))
	if ($_SESSION['fieldarray'][$t][5] != $_SESSION['fieldarray'][$v][5] && $newgroup != "yes" && $groupdescription  && $move != " << prev ")
		{
		$presentinggroupdescription = "yes";
		echo "\t<form method='post'>\n";
		echo "\t<tr>\n";
		echo "\t\t<td colspan='2' align='center'>\n";
		echo "\t\t\t$setfont<br />$groupdescription<br />&nbsp;\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<input type='hidden' name='sid' value='$sid' />\n";
		echo "\t<input type='hidden' name='thisstep' value='$t' />\n";
		echo "\t<input type='hidden' name='newgroup' value='yes' />\n";
		}
	
	
	else
		{
		// SHOW % CHART
		echo "\t<tr>\n";;
		echo "\t\t<td colspan='2' align='center' bgcolor='EEEEEE'>$setfont\n";
		
		echo "\t\t\t<table width='175' align='center' style='border-collapse: collapse; border: 1px solid #111111'>\n";
		echo "\t\t\t\t<tr>\n";
		echo "\t\t\t\t\t<td width='35' align='right'><font size='1'>0%</td>\n";
		echo "\t\t\t\t\t<td width='105'><img src='chart.jpg' height='15' width='$chart'></td>\n";
		echo "\t\t\t\t\t<td width='35'><font size='1'>100%</td>\n";
		echo "\t\t\t\t</tr>\n";
		echo "\t\t\t</table>\n";
		
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		
		// PRESENT QUESTION
		echo "\t<form method='post'>\n";
		echo "\t<input type='hidden' name='sid' value='$sid' />\n";
		echo "\t<input type='hidden' name='thisstep' value='{$_SESSION['step']}' />\n";
		
		// QUESTION STUFF
		echo "\t<tr>\n";
		echo "\t\t<td colspan='2'>\n";
		echo "<!-- THE QUESTION IS HERE -->\n";
		echo "<table width='100%' border='0'>\n";
		echo "\t<tr><td colspan='2' height='20'></td></tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td colspan='2' height='4'>\n";
		echo "\t\t\t<table width='50%' align='center'>\n";
		echo "\t\t\t\t<tr><td bgcolor='#888888' height='3'></td></tr>\n";
		echo "\t\t\t</table>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td colspan='2' align='center' valign='top'>\n";
		echo "\t\t\t<b><font color='#000080'>\n";
		echo $_SESSION['fieldarray'][$t][3]."\n";
		echo "\t\t\t</font></b>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td colspan='2' height='4'>\n";
		echo "\t\t\t<table width='50%' align='center'>\n";
		echo "\t\t\t\t<tr><td bgcolor='silver' height='3'></td></tr>\n";
		echo "\t\t\t</table>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		$fname = "F".$_SESSION['fieldarray'][$t][1];
		
		// THE FOLLOWING PRESENTS THE QUESTION BASED ON THE QUESTION TYPE
		switch ($_SESSION['fieldarray'][$t][4])
			{
			case "5": //5 POINT CHOICE radio-buttons
				echo "\t<tr>\n";
				echo "\t\t<td colspan='2' align='center'>\n";
				echo "\t\t\t<input type='hidden' name='lastfield' value='$fname' />\n";
				for ($fp=1; $fp<=5; $fp++)
					{
					echo "\t\t\t<input type='radio' name='fvalue' value='$fp'";
					if ($_SESSION[$fname] == $fp) {echo " checked";}
					echo " />$fp\n";
					}
				break;
			case "D": //DATE
				echo "\t<tr>\n";
				echo "\t\t<td colspan='2' align='center'>\n";
				echo "\t\t\t<input type='hidden' name='lastfield' value='$fname' />\n";
				echo "\t\t\t<input type='text' size=10 name='fvalue' value=\"".$_SESSION[$fname]."\" />\n";
				echo "\t\t\t<table width='230' align='center' bgcolor='#EEEEEE'>\n";
				echo "\t\t\t\t<tr>\n";
				echo "\t\t\t\t\t<td align='center'>\n";
				echo "\t\t\t\t\t\t<font size='1'>Format: YYYY-MM-DD<br />\n";
				echo "\t\t\t\t\t\t(eg: 2003-12-25 for Christmas day)\n";
				echo "\t\t\t\t\t</td>\n";
				echo "\t\t\t\t</tr>\n";
				echo "\t\t\t</table>\n";
				break;
			case "G": //GENDER drop-down list
				echo "\t<tr>\n";
				echo "\t\t<td colspan='2' align='center'>\n";
				echo "\t\t\t<input type='hidden' name='lastfield' value='$fname' />\n";
				echo "\t\t\t<select name='fvalue'>\n";
				echo "\t\t\t\t<option value='F'";
				if ($_SESSION[$fname] == "F") {echo " selected";}
				echo ">Female</option>\n";
				echo "\t\t\t\t<option value='M'";
				if ($_SESSION[$fname] == "M") {echo " selected";}
				echo ">Male</option>\n";
				echo "\t\t\t\t<option value=' '";
				if ($_SESSION[$fname] != "F" && $_SESSION[$fname] !="M") {echo " selected";}
				echo ">Please choose</option>\n";
				echo "\t\t\t</select>\n";
				break;
			case "L": //LIST drop-down/radio-button list
				echo "\t<tr>\n";
				echo "\t\t<td colspan='2' align='center'>\n";
				echo "\t\t\t<input type='hidden' name='lastfield' value='$fname' />\n";
				$ansquery = "SELECT * FROM answers WHERE qid={$_SESSION['fieldarray'][$t][0]} ORDER BY code";
				$ansresult = mysql_query($ansquery);
				if ($dropdowns == "L" || !$dropdowns)
					{
					echo "\t\t\t<select name='fvalue'>\n";
					while ($ansrow = mysql_fetch_array($ansresult))
						{
						echo "\t\t\t\t  <option value='{$ansrow['code']}'";
						if ($_SESSION[$fname] == $ansrow['code'])
							{ echo " selected"; }
						elseif ($ansrow['default'] == "Y") {echo " selected"; $defexists = "Y";}
						echo ">{$ansrow['answer']}</option>\n";
						}
					if (!$_SESSION[$fname] && !$defexists) {echo "\t\t\t\t  <option value=' ' selected>Please choose..</option>\n";}
					if ($_SESSION[$fname] && !$defexists) {echo "\t\t\t\t  <option value=' '>No answer</option>\n";}
					echo "\t\t\t</select>\n";
					}
				elseif ($dropdowns == "R")
					{
					echo "\t\t\t<table align='center'>\n";
					echo "\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t<td>$setfont\n";
					while ($ansrow = mysql_fetch_array($ansresult))
						{
						echo "\t\t\t\t\t\t  <input type='radio' value='{$ansrow['code']}' name='fvalue'";
						if ($_SESSION[$fname] == $ansrow['code'])
							{ echo " checked"; }
						elseif ($ansrow['default'] == "Y") {echo " checked"; $defexists = "Y";}
						echo " />{$ansrow['answer']}<br />\n";
						}
					if (!$_SESSION[$fname] && !$defexists) {echo "\t\t\t\t\t\t  <input type='radio' name='fvalue' value=' ' checked />No answer\n";}
					elseif ($ffname && !$defexists) {echo "\t\t\t\t\t\t  <input type='radio' name='fvalue' value=' ' />No answer\n";}
					echo "\t\t\t\t\t</td>\n";
					echo "\t\t\t\t</tr>\n";
					echo "\t\t\t</table>\n";
					}
				break;
			case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
				echo "\t<tr>\n";
				echo "\t\t<td colspan='2' align='center'>\n";
				//echo "\t\t\t<input type='hidden' name='lastfield' value='$fname' />\n";
				$ansquery = "SELECT * FROM answers WHERE qid={$_SESSION['fieldarray'][$t][0]} ORDER BY code";
				$ansresult = mysql_query($ansquery);
				$anscount = mysql_num_rows($ansresult);
				echo "\t\t\t<table align='center'>\n";
				echo "\t\t\t\t<tr>\n";
				echo "\t\t\t\t\t<td>$setfont<u>Choose one of the following:</u></td>\n";
				echo "\t\t\t\t\t<td>$setfont<u>Please enter your comment here:</td>\n";
				echo "\t\t\t\t</tr>\n";
				echo "\t\t\t\t<tr>\n";
				echo "\t\t\t\t\t<td valign='top'>$setfont\n";
				
				while ($ansrow=mysql_fetch_array($ansresult))
					{
					echo "\t\t\t\t\t\t<input type='radio' value='{$ansrow['code']}' name='fvalue1'";
					if ($_SESSION[$fname] == $ansrow['code'])
						{ echo " checked"; }
					elseif ($ansrow['default'] == "Y") {echo " checked"; $defexists = "Y";}
					echo " />{$ansrow['answer']}<br />\n";
					}
				if (!$_SESSION[$fname] && !$defexists) {echo "\t\t\t\t\t\t<input type='radio' name='fvalue1' value=' ' checked />No answer\n";}
				elseif ($_SESSION[$fname] && !$defexists) {echo "\t\t\t\t\t\t<input type='radio' name='fvalue1' value=' ' />No answer\n";}
				echo "\t\t\t\t\t</td>\n";
				$fname2 = $fname."comment";
				if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
				echo "\t\t\t\t\t<td valign='top'>\n";
				echo "\t\t\t\t\t\t<textarea name='fvalue2' rows='$tarows' cols='30'>".$_SESSION[$fname2]."</textarea>\n";
				$multifields = "$fname|$fname"."comment|";
				echo "\t\t\t\t\t\t<input type='hidden' name='multi' value='2' />\n";
				echo "\t\t\t\t\t\t<input type='hidden' name='lastfield' value='$multifields' />\n";
				echo "\t\t\t\t\t</td>\n";
				echo "\t\t\t\t</tr>\n";
				echo "\t\t\t</table>\n";
				break;
			case "M": //MULTIPLE OPTIONS checkbox
				echo "\t<tr>\n";
				echo "\t\t<td colspan='2'>\n";
				echo "\t\t\t<table align='center' border='0'>\n";
				echo "\t\t\t\t<tr>\n";
				echo "\t\t\t\t\t<td>&nbsp;</td>\n";
				echo "\t\t\t\t\t<td align='left'>\n";
				$qquery = "SELECT other FROM questions WHERE qid=".$_SESSION['fieldarray'][$t][0];
				$qresult = mysql_query($qquery);
				while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
				$ansquery = "SELECT * FROM answers WHERE qid={$_SESSION['fieldarray'][$t][0]} ORDER BY code";
				$ansresult = mysql_query($ansquery);
				$anscount = mysql_num_rows($ansresult);
				$fn = 1;
				while ($ansrow = mysql_fetch_array($ansresult))
					{
					$myfname = $fname.$ansrow['code'];
					$multifields .= "$fname{$ansrow['code']}|";
					echo "\t\t\t\t\t\t$setfont<input type='checkbox' name='fvalue$fn' value='Y'";
					if ($_SESSION[$myfname] == "Y") {echo " checked";}
					echo " />{$ansrow['answer']}<br />\n";
					$fn++;
					}
				$multifields = substr($multifields, 0, strlen($multifields)-1);
				if ($other == "Y")
					{
					$myfname = $fname."other";
					echo "\t\t\t\t\t\tOther: <input type='text' name='fvalue$fn'";
					if ($$myfname) {echo " value='".$$myfname."'";}
					echo " />\n";
					$multifields .= "|$fname"."other";
					$anscount++;
					}
				echo "\t\t\t\t\t</td>\n";
				echo "\t\t\t\t\t<td>&nbsp;</td>\n";
				echo "\t\t\t\t</tr>\n";
				echo "\t\t\t</table>\n";
				echo "\t\t\t<input type='hidden' name='multi' value='$anscount' />\n";
				echo "\t\t\t<input type='hidden' name='lastfield' value='$multifields' />\n";
				break;
			case "P": //MULTIPLE OPTIONS WITH COMMENTS checkbox + text
				echo "\t<tr>\n";
				echo "\t\t<td colspan='2'>\n";
				echo "\t\t\t<table align='center' border='0'>\n";
				echo "\t\t\t\t<tr>\n";
				echo "\t\t\t\t\t<td>&nbsp;</td>\n";
				echo "\t\t\t\t\t<td align='left'>\n";
				$qquery = "SELECT other FROM questions WHERE qid=".$_SESSION['fieldarray'][$t][0];
				$qresult = mysql_query($qquery);
				while ($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
				$ansquery = "SELECT * FROM answers WHERE qid={$_SESSION['fieldarray'][$t][0]} ORDER BY code";
				$ansresult = mysql_query($ansquery);
				$anscount = mysql_num_rows($ansresult)*2;
				$fn = 1;
				echo "\t\t\t\t\t\t<table border='0'>\n";
				while ($ansrow = mysql_fetch_array($ansresult))
					{
					$myfname = $fname.$ansrow['code'];
					$myfname2 = $myfname."comment";
					$multifields .= "$fname{$ansrow['code']}|$fname{$ansrow['code']}comment|";
					echo "\t\t\t\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t\t\t\t<td>$setfont\n";
					echo "\t\t\t\t\t\t\t\t\t<input type='checkbox' name='fvalue$fn' value='Y'";
					if ($_SESSION[$myfname] == "Y") {echo " checked";}
					echo " /><b>{$ansrow['answer']}</b>\n";
					echo "\t\t\t\t\t\t\t\t</td>\n";
					$fn++;
					echo "\t\t\t\t\t\t\t\t<td>\n";
					echo "\t\t\t\t\t\t\t\t\t<input style='background-color: #EEEEEE; height:18; font-face: verdana; font-size: 9' type='text' size='40' name='fvalue$fn' value='".$_SESSION[$myfname2]."' />\n";
					echo "\t\t\t\t\t\t\t\t</td>\n";
					echo "\t\t\t\t\t\t\t</tr>\n";
					$fn++;
					}
				$multifields = substr($multifields, 0, strlen($multifields)-1);
				if ($other == "Y")
					{
					$myfname = $fname."other";
					$myfname2 = $myfname."comment";
					$multifields .= "|$fname"."other|$fname"."othercomment";
					$anscount = $anscount + 2;
					echo "\t\t\t\t\t\t\t<tr>\n";
					echo "\t\t\t\t\t\t\t\t<td>$setfont\n";
					echo "\t\t\t\t\t\t\t\t\tOther: <input type='text' name='fvalue$fn'";
					if ($_SESSION[$myfname]) {echo " value='".$_SESSION[$myfname]."'";}
					echo " />\n";
					echo "\t\t\t\t\t\t\t\t</td>\n";
					$fn++;
					echo "\t\t\t\t\t\t\t\t<td>\n";
					echo "\t\t\t\t\t\t\t\t\t<input style='background-color: #EEEEEE; height:18; font-face: verdana; font-size: 9' type='text' size='40' name='fvalue$fn' value='".$_SESSION[$myfname2]."' />\n";
					echo "\t\t\t\t\t\t\t\t</td>\n";
					echo "\t\t\t\t\t\t\t</tr>\n";
					}
				echo "\t\t\t\t\t\t</table>\n";
				echo "\t\t\t\t\t</td>\n";
				echo "\t\t\t\t\t<td>&nbsp;</td>\n";
				echo "\t\t\t\t</tr>\n";
				echo "\t\t\t</table>\n";
				echo "\t\t\t<input type='hidden' name='multi' value='$anscount' />\n";
				echo "\t\t\t<input type='hidden' name='lastfield' value='$multifields' />\n";
				break;
			case "S": //SHORT FREE TEXT
				echo "\t<tr>\n";
				echo "\t\t<td colspan='2' align='center'>\n";
				echo "\t\t\t<input type='hidden' name='lastfield' value='$fname' />\n";
				echo "\t\t\t<input type='text' size='50' name='fvalue' value=\"".str_replace ("\"", "'", str_replace("\\", "", $_SESSION[$fname]))."\" />\n";
				break;
			case "T": //LONG FREE TEXT
				echo "\t<tr>\n";
				echo "\t\t<td colspan='2' align='center'>\n";
				echo "\t\t\t<input type='hidden' name='lastfield' value='$fname' />\n";
				echo "\t\t\t<textarea name='fvalue' rows='5' cols='40'>";
				if ($_SESSION[$fname]) {echo str_replace("\\", "", $_SESSION[$fname]);}	
				echo "</textarea>\n";
				break;
			case "Y": //YES/NO radio-buttons
				echo "\t<tr>\n";
				echo "\t\t<td colspan='1' align='center'>\n";
				echo "\t\t\t<input type='hidden' name='lastfield' value='$fname' />\n";
				echo "\t\t\t<table align='center'>\n";
				echo "\t\t\t\t<tr>\n";
				echo "\t\t\t\t\t<td>$setfont\n";
				echo "\t\t\t\t\t\t<input type='radio' name='fvalue' value='Y'";
				if ($_SESSION[$fname] == "Y") {echo " checked";}
				echo " />Yes<br />\n";
				echo "\t\t\t\t\t\t<input type='radio' name='fvalue' value='N'";
				if ($_SESSION[$fname] == "N") {echo " checked";}
				echo " />No<br />\n";
				echo "\t\t\t\t\t</td>\n";
				echo "\t\t\t\t</tr>\n";
				echo "\t\t\t</table>\n";
				break;
			case "A": //ARRAY (5 POINT CHOICE) radio-buttons
				echo "\t<tr>\n";
				echo "\t\t<td colspan='2'>\n";
				$qquery = "SELECT other FROM questions WHERE qid=".$_SESSION['fieldarray'][$t][0];
				$qresult = mysql_query($qquery);
				while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
				$ansquery = "SELECT * FROM answers WHERE qid={$_SESSION['fieldarray'][$t][0]} ORDER BY code";
				$ansresult = mysql_query($ansquery);
				$anscount = mysql_num_rows($ansresult);
				$fn = 1;
				echo "\t\t\t<table align='center' border='0'>\n";
				while ($ansrow = mysql_fetch_array($ansresult))
					{
					$myfname = $fname.$ansrow['code'];
					$multifields .= "$fname{$ansrow['code']}|";
					if ($trbc == "#E1E1E1" || !$trbc) {$trbc = "#F1F1F1";} else {$trbc = "#E1E1E1";}
					echo "\t\t\t\t<tr bgcolor='$trbc'>\n";
					echo "\t\t\t\t\t<td align='right'>$setfont{$ansrow['answer']}</td>\n";
					echo "\t\t\t\t\t<td>";
					for ($i=1; $i<=5; $i++)
						{
						echo "\t\t\t\t\t$setfont<input type='radio' name='fvalue$fn' value='$i'";
						if ($_SESSION[$myfname] == $i) {echo " checked";}
						echo " />$i&nbsp;\n";
						}
					echo "\t\t\t\t\t</td>\n";
					echo "\t\t\t\t</tr>\n";
					$fn++;
					}			
				echo "\t\t\t</table>\n";
				echo "\t\t\t<input type='hidden' name='multi' value='$anscount' />\n";
				echo "\t\t\t<input type='hidden' name='lastfield' value='$multifields' />\n";
				break;
			case "B": //ARRAY (10 POINT CHOICE) radio-buttons
				echo "\t<tr>\n";
				echo "\t\t<td colspan='2'>\n";
				$qquery = "SELECT other FROM questions WHERE qid=".$_SESSION['fieldarray'][$t][0];
				$qresult = mysql_query($qquery);
				while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
				$ansquery = "SELECT * FROM answers WHERE qid={$_SESSION['fieldarray'][$t][0]} ORDER BY code";
				$ansresult = mysql_query($ansquery);
				$anscount = mysql_num_rows($ansresult);
				$fn = 1;
				echo "\t\t\t<table align='center'>\n";
				while ($ansrow = mysql_fetch_array($ansresult))
					{
					$myfname = $fname.$ansrow['code'];
					$multifields .= "$fname{$ansrow['code']}|";
					if ($trbc == "#E1E1E1" || !$trbc) {$trbc = "#F1F1F1";} else {$trbc = "#E1E1E1";}
					echo "\t\t\t\t<tr bgcolor='$trbc'>\n";
					echo "\t\t\t\t\t<td align='right'>$setfont{$ansrow['answer']}</td>\n";
					echo "\t\t\t\t\t<td>\n";
					for ($i=1; $i<=10; $i++)
						{
						echo "\t\t\t\t\t\t$setfont<input type='radio' name='fvalue$fn' value='$i'";
						if ($_SESSION[$myfname] == $i) {echo " checked";}
						echo " />$i&nbsp;\n";
						}
					echo "\t\t\t\t\t</td>\n";
					echo "\t\t\t\t</tr>\n";
					$fn++;
					}			
				echo "\t\t\t</table>\n";
				echo "\t\t\t<input type='hidden' name='multi' value='$anscount' />\n";
				echo "\t\t\t<input type='hidden' name='lastfield' value='$multifields' />\n";
				break;
			case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
				echo "\t<tr>\n";
				echo "\t\t<td colspan='2'>\n";
				$qquery = "SELECT other FROM questions WHERE qid=".$_SESSION['fieldarray'][$t][0];
				$qresult = mysql_query($qquery);
				while($qrow = mysql_fetch_array($qresult)) {$other = $qrow['other'];}
				$ansquery = "SELECT * FROM answers WHERE qid={$_SESSION['fieldarray'][$t][0]} ORDER BY code";
				$ansresult = mysql_query($ansquery);
				$anscount = mysql_num_rows($ansresult);
				$fn = 1;
				echo "\t\t\t<table align='center'>\n";
				while ($ansrow = mysql_fetch_array($ansresult))
					{
					$myfname = $fname.$ansrow['code'];
					$multifields .= "$fname{$ansrow['code']}|";
					if ($trbc == "#E1E1E1" || !$trbc) {$trbc = "#F1F1F1";} else {$trbc = "#E1E1E1";}
					echo "\t\t\t\t<tr bgcolor='$trbc'>\n";
					echo "\t\t\t\t\t<td align='right'>$setfont{$ansrow['answer']}</td>\n";
					echo "\t\t\t\t\t<td>\n";
					echo "\t\t\t\t\t\t$setfont<input type='radio' name='fvalue$fn' value='Y'";
					if ($_SESSION[$myfname] == "Y") {echo " checked";}
					echo " />Yes&nbsp;\n";
					echo "\t\t\t\t\t\t$setfont<input type='radio' name='fvalue$fn' value='U'";
					if ($_SESSION[$myfname] == "U") {echo " checked";}
					echo " />Uncertain&nbsp;\n";
					echo "\t\t\t\t\t\t$setfont<input type='radio' name='fvalue$fn' value='N'";
					if ($_SESSION[$myfname] == "N") {echo " checked";}
					echo " />No&nbsp;\n";
					echo "\t\t\t\t\t</td>\n";
					echo "\t\t\t\t</tr>\n";
					$fn++;
					}			
				echo "\t\t\t</table>\n";
				echo "\t\t\t<input type='hidden' name='multi' value='$anscount' />\n";
				echo "\t\t\t<input type='hidden' name='lastfield' value='$multifields' />\n";
				break;
			}	

		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td colspan='2' height='4'>\n";
		echo "\t\t\t<table width='50%' align='center'>\n";
		echo "\t\t\t\t<tr><td bgcolor='silver' height='3'></td></tr>\n";
		echo "\t\t\t</table>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";


		//SHOW HELP INFORMATION IF THERE IS ANY
		$helpquery = "SELECT help FROM questions WHERE qid=".$_SESSION['fieldarray'][$t][0];
		$helpresult = mysql_query($helpquery);
		while ($helprow = mysql_fetch_array($helpresult))
			{
			if ($helprow['help'])
				{
				echo "\t<tr>\n";
				echo "\t\t<td colspan='2'>\n";
				echo "\t\t\t<table width='50%' align='center' cellspacing='0'>\n";
				echo "\t\t\t\t<tr>\n";
				echo "\t\t\t\t\t<td bgcolor='#DEDEDE' valign='top'>\n";
				echo "\t\t\t\t\t\t<img src='help.gif' vspace='1' align='left' alt='Help for this question..'>\n";
				echo "\t\t\t\t\t</td>\n";
				echo "\t\t\t\t\t<td bgcolor='#DEDEDE'>\n";
				echo "\t\t\t\t\t\t<font size='1'>{$helprow['help']}</td>\n";
				echo "\t\t\t\t</tr>\n";
				echo "\t\t\t</table>\n";
				echo "\t\t</td>\n";
				echo "\t</tr>\n";
				}
			}
		
		echo "\t<tr><td colspan='2' height='20'></td></tr>\n";
		echo "</table>\n";
		echo "<!-- END OF QUESTION -->\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		//echo "<tr><td colspan='2'>$token</td></tr>\n";
		}
	}

echo surveymover();
echo "\t<input type='hidden' name='lastgroupname' value='$currentgroupname' />\n";
echo "\t</form>\n";
if ($surveyactive != "Y")
	{
	echo "\t<tr>\n";
	echo "\t\t<td colspan='2' align='center'>\n";
	echo "\t\t\t$setfont<font color='red'>Warning: Survey Not Active. Your survey results will not be recorded.\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	}
echo "</table>\n";
echo "</body>\n</html>";

function surveymover()
	{
	global $presentinggroupdescription;
	$surveymover = "\t<tr>\n";
	$surveymover .= "\t\t<td colspan='2' align='center' bgcolor='#EEEEEE'>\n";
	$surveymover .= "\t\t\t<table width='50%' align='center'>\n";
	$surveymover .= "\t\t\t\t<tr>\n";
	$surveymover .= "\t\t\t\t\t<td align='center'>\n";
	if ($_SESSION['step'] > 0)
		{$surveymover .= "\t\t\t\t\t\t<input type='submit' value=' << prev ' name='move' />\n";}
	if ($_SESSION['step'] && (!$_SESSION['totalsteps'] || ($_SESSION['step'] < $_SESSION['totalsteps'])))
		{$surveymover .=  "\t\t\t\t\t\t<input type='submit' value=' next >> ' name='move' />";}
	if (!$_SESSION['step'])
		{$surveymover .=  "\t\t\t\t\t\t<input type='submit' value=' next >> ' name='move' />";}
	if ($_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && $presentinggroupdescription == "yes")
		{$surveymover .=  "\t\t\t\t\t\t<input type='submit' value=' next >> ' name='move' />";}
	if ($_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && !$presentinggroupdescription)
		{$surveymover .= "\t\t\t\t\t\t <input type='submit' value=' last ' name='move' />";}
	//if ($_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']+1))
		//{$surveymover .= "\t\t\t\t\t\t<input type='submit' value=' submit ' name='move' />";}
	//$surveymover .= " <a href='?move=clearall&sid={$_SESSION['sid']}'>X</a>";
	$surveymover .= "\t\t\t\t\t</td>\n";
	$surveymover .= "\t\t\t\t</tr>\n";
	$surveymover .= "\t\t\t</table>\n";
	$surveymover .= "\t\t\t<font size='1'>[<a href='index.php?sid={$_SESSION['sid']}&move=clearall'>Exit and Clear Survey</a>]\n";
	$surveymover .= "\t\t</td>\n";
	$surveymover .= "\t</tr>\n";
	return $surveymover;	
	}
?>