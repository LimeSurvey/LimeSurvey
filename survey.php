<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA					#
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

//Move current step
if ($_POST['move'] == " << "._PREV." ") {$_SESSION['step'] = $_POST['thisstep']-1;}
if ($_POST['move'] == " "._NEXT." >> ") {$_SESSION['step'] = $_POST['thisstep']+1;}
if ($_POST['move'] == " "._LAST." ") {$_SESSION['step'] = $_POST['thisstep']+1;}

//CONVERT POSTED ANSWERS TO SESSION VARIABLES
if ($_POST['fieldnames'])
	{
	$postedfieldnames=explode("|", $_POST['fieldnames']);
	foreach ($postedfieldnames as $pf)
		{
		$_SESSION[$pf] = $_POST[$pf];
		}
	}

//CHECK IF ALL MANDATORY QUESTIONS HAVE BEEN ANSWERED
if ($_POST['mandatory'])
	{
	$chkmands=explode("|", $_POST['mandatory']);
	$mfns=explode("|", $_POST['mandatoryfn']);
	$mi=0;
	foreach ($chkmands as $cm)
		{
		if ($multiname != "MULTI$mfns[$mi]") 
			{
			if ($multiname && $_POST[$multiname])
				{
				if ($$multiname == $$multiname2) //so far all multiple choice options are unanswered
					{
					//The number of questions not answered is equal to the number of questions
					if ($_POST['move'] == " << "._PREV." ") {$_SESSION['step'] = $_POST['thisstep'];}
					if ($_POST['move'] == " "._NEXT." >> ") {$_SESSION['step'] = $_POST['thisstep'];}
					if ($_POST['move'] == " "._LAST." ") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == " "._NEXT." >> ";}
				    $notanswered[]=substr($multiname, 5, strlen($multiname));
					$$multiname=0;
					$$multiname2=0;
					}
				}
			$multiname="MULTI$mfns[$mi]";
			$$multiname=0;
			$$multiname2=0;
			}
		else {$multiname="MULTI$mfns[$mi]";}
		if ($_SESSION[$cm] == "0" || $_SESSION[$cm])
			{
			}
		elseif (!$_POST[$multiname])
			{
			//One of the mandatory questions hasn't been asnwered
			if ($_POST['move'] == " << "._PREV." ") {$_SESSION['step'] = $_POST['thisstep'];}
			if ($_POST['move'] == " "._NEXT." >> ") {$_SESSION['step'] = $_POST['thisstep'];}
			if ($_POST['move'] == " "._LAST." ") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == " "._NEXT." >> ";}
			$notanswered[]=$mfns[$mi];
			}
		else
			{
			//One of the mandatory questions hasn't been asnwered
			$$multiname++;
			}
		$$multiname2++;
		$mi++;
		}
	if ($multiname && $_POST[$multiname]) // Catch the last multiple options question in the lot
		{
		if ($$multiname == $$multiname2) //so far all multiple choice options are unanswered
			{
			//The number of questions not answered is equal to the number of questions
			if ($_POST['move'] == " << "._PREV." ") {$_SESSION['step'] = $_POST['thisstep'];}
			if ($_POST['move'] == " "._NEXT." >> ") {$_SESSION['step'] = $_POST['thisstep'];}
			if ($_POST['move'] == " "._LAST." ") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == " "._NEXT." >> ";}
		    $notanswered[]=substr($multiname, 5, strlen($multiname));
			$$multiname="";
			$$multiname2="";
			}
		}
	}

if ($_POST['conmandatory'])
	{
	$chkcmands=explode("|", $_POST['conmandatory']);
	$cmfns=explode("|", $_POST['conmandatoryfn']);
	$mi=0;
	foreach ($chkcmands as $ccm)
		{
		if ($multiname != "MULTI$cmfns[$mi]") //the last multipleanswerchecked is different to this one
			{
			if ($multiname && $_POST[$multiname])
				{
				if ($$multiname == $$multiname2) //For this lot all multiple choice options are unanswered
					{
					//The number of questions not answered is equal to the number of questions
					if ($_POST['move'] == " << "._PREV." ") {$_SESSION['step'] = $_POST['thisstep'];}
					if ($_POST['move'] == " "._NEXT." >> ") {$_SESSION['step'] = $_POST['thisstep'];}
					if ($_POST['move'] == " "._LAST." ") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == " "._NEXT." >> ";}
				    $notanswered[]=substr($multiname, 5, strlen($multiname));
					$$multiname=0;
					$$multiname2=0;
					}
			    }
			$multiname="MULTI$cmfns[$mi]"; 
			$$multiname=0; 
			$$multiname2=0;
			}
		else{$multiname="MULTI$cmfns[$mi]";}
		//$dccm="display$ccm";
		$dccm="display$cmfns[$mi]";
		if (($_SESSION[$ccm] == "0" || $_SESSION[$ccm]) && $_POST[$dccm] == "on")//There is an answer
			{
			}
		elseif ($_POST[$dccm] == "on" && !$_POST[$multiname]) //Question is on, there is no answer, but it's a multiple
			{
			if ($_POST['move'] == " << "._PREV." ") {$_SESSION['step'] = $_POST['thisstep'];}
			if ($_POST['move'] == " "._NEXT." >> ") {$_SESSION['step'] = $_POST['thisstep'];}
			if ($_POST['move'] == " "._LAST." ") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == " "._NEXT." >> ";}
			$notanswered[]=$cmfns[$mi];
			}
		elseif ($_POST[$dccm] == "on")
			{
			//One of the conditional mandatory questions was on, but hasn't been answered
			$$multiname++; 
			}
		$$multiname2++;
		$mi++;
		}
	if ($multiname && $_POST[$multiname])
		{
		if ($$multiname == $$multiname2) //so far all multiple choice options are unanswered
			{
			//The number of questions not answered is equal to the number of questions
			if ($_POST['move'] == " << "._PREV." ") {$_SESSION['step'] = $_POST['thisstep'];}
			if ($_POST['move'] == " "._NEXT." >> ") {$_SESSION['step'] = $_POST['thisstep'];}
			if ($_POST['move'] == " "._LAST." ") {$_SESSION['step'] = $_POST['thisstep']; $_POST['move'] == " "._NEXT." >> ";}
		    $notanswered[]=substr($multiname, 5, strlen($multiname));
			}
		}
	}

//SUBMIT
if ($_POST['move'] == " "._SUBMIT." " && !$notanswered)
	{
	if ($surveyprivate == "Y")
		{
		$privacy="";
		foreach (file("$thistpl/privacy.pstpl") as $op)
			{
			$privacy .= $op;
			}
		}
	//If survey has datestamp turned on, add $localtimedate to sessions
	if ($surveydatestamp == "Y")
		{
		$_SESSION['insertarray'][] = "datestamp";
		$_SESSION['datestamp'] = $localtimedate;
		}

	//DEVELOP SQL TO INSERT RESPONSES
	$subquery = "INSERT INTO $surveytable ";
	if (is_array($_SESSION['insertarray']))
		{
		foreach ($_SESSION['insertarray'] as $value)
			{
			$col_name .= "`, `" . $value; 
			if (get_magic_quotes_gpc() == "0")
				{
				if (phpversion() >= "4.3.0")
					{
					$values .= ", '" . mysql_real_escape_string($_SESSION[$value]) . "'";
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
			}
		$col_name .= "`";
		$col_name = substr($col_name, 3); //Strip off first backtick, comma & space
		$values = substr($values, 2); //Strip off first comma & space
		$subquery .= "\n($col_name) \nVALUES \n($values)";
		}
	else
		{
		echo "<html>\n";
		foreach(file("$thistpl/startpage.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		echo "<br /><center><font face='verdana' size='2'><font color='red'><b>"._ERROR."</b></font><br /><br />\n";
		echo _BADSUBMIT1."<br /><br />\n";
		echo "<font size='1'>"._BADSUBMIT2."<br />\n";
		echo "</font></center><br /><br />";
		exit;
		}	
	//COMMIT CHANGES TO DATABASE
	if ($surveyactive != "Y")
		{
		echo "<html>\n";
		foreach(file("$thistpl/startpage.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		$completed = "<br /><b><font size='2' color='red'>"._DIDNOTSAVE."</b></font><br /><br />\n\n";
		$completed .= _NOTACTIVE1."<br /><br />\n";
		$completed .= "<a href='{$_SERVER['PHP_SELF']}?sid=$sid&move=clearall'>"._CLEARRESP."</a><br /><br />\n";
		$completed .= "<font size='1'>$subquery</font>\n";
		}
	else
		{
		if (mysql_query($subquery)) //save responses was succesful
			{
			//UPDATE COOKIE IF REQUIRED
			if ($surveyusecookie == "Y" && $tokensexist != 1)
				{
				$cookiename="PHPSID".returnglobal('sid')."STATUS";
				setcookie("$cookiename", "COMPLETE", time() + 31536000);
				}
			echo "<html>\n";
			foreach(file("$thistpl/startpage.pstpl") as $op)
				{
				echo templatereplace($op);
				}
			$completed = "<br /><b><font size='2'><font color='green'>"._THANKS."</b></font><br /><br />\n\n";
			$completed .= _SURVEYREC."<br />\n";
			$completed .= "<a href='javascript:window.close()'>"._CLOSEWIN."</a></font><br /><br />\n";
			if ($_POST['token'])
				{
				$utquery = "UPDATE {$dbprefix}tokens_$sid SET completed='Y' WHERE token='{$_POST['token']}'";
				$utresult = mysql_query($utquery) or die ("Couldn't update tokens table!<br />\n$utquery<br />\n".mysql_error());
				$cnfquery = "SELECT * FROM {$dbprefix}tokens_$sid WHERE token='{$_POST['token']}' AND completed='Y'";
				$cnfresult = mysql_query($cnfquery);
				while ($cnfrow = mysql_fetch_array($cnfresult))
					{
					$headers = "From: $surveyadminemail\r\n";
					$headers .= "X-Mailer: $sitename Email Inviter";
					$to = $cnfrow['email'];
					$subject = _CONFIRMATION.": $surveyname "._SURVEYCPL;
					$message="";
					foreach (file("$thistpl/confirmationemail.pstpl") as $ce)
						{
						$add=$ce;
						$add = str_replace("{FIRSTNAME}", $cnfrow['firstname'], $add);
						$add = str_replace("{LASTNAME}", $cnfrow['lastname'], $add);
						$add = str_replace("{ADMINNAME}", $surveyadminname, $add);
						$add = str_replace("{ADMINEMAIL}", $surveyadminemail, $add);
						$add = str_replace("{SURVEYNAME}", $surveyname, $add);
						$message .= $add;
						}
					if ($cnfrow['email']) {mail($to, $subject, $message, $headers);} //Only send confirmation email if there is an email address
					
					//DEBUG INFO: CAN BE REMOVED
					echo "<!-- DEBUG: MAIL INFORMATION\n";
					echo "TO: $to\n";
					echo "SUBJECT: $subject\n";
					echo "MESSAGE: $message\n";
					echo "HEADERS: $headers\n";
					echo "-->\n";
					//END DEBUG
					}					
				}
			if ($sendnotification > 0 && $surveyadminemail) 
				{ //Send notification to survey administrator //Thanks to Jeff Clement http://jclement.ca
				$id = $savedid;
				$to = $surveyadminemail;
				$subject = "$sitename Survey Submitted";
				$message = _CONFIRMATION_MESSAGE1." $surveyname\r\n";
				$message.= "\r\n";
				$message.= _CONFIRMATION_MESSAGE2."\r\n";
				$message.= "  $homeurl/browse.php?sid=$sid&action=id&id=$id\r\n\r\n";
				$message.= _CONFIRMATION_MESSAGE3."\r\n";
				$message.= "  $homeurl/statistics.php?sid=$sid\r\n\r\n";
				if ($sendnotification > 1)
					{ //Send results as well. Currently just bare-bones - will be extended in later release
					$message .= "----------------------------\r\n";
					foreach ($_SESSION['insertarray'] as $value)
						{
						$message .= "$value: {$_SESSION[$value]}\r\n";
						}
					$message .= "----------------------------\r\n\r\n";
					}
				$message.= "PHP Surveyor";
				$headers = "From: $surveyadminemail\r\n";
				mail($to, $subject, $message, $headers);
				}
			session_unset();
			session_destroy();
			}
		else
			{
			echo "<html>\n";
			foreach(file("$thistpl/startpage.pstpl") as $op)
				{
				echo templatereplace($op);
				}
			$completed = "<br /><b><font size='2' color='red'>"._DIDNOTSAVE."</b></font><br /><br />\n\n";
			$completed .= _DIDNOTSAVE2."<br /><br />\n";
			if ($adminemail)
				{	
				$completed .= _DIDNOTSAVE3."<br /><br />\n";
				$email=_DNSAVEEMAIL1." $sid\n\n";
				$email .= DNSAVEEMAIL2.":\n";
				foreach ($_SESSION['insertarray'] as $value)
					{
					$email .= "$value: {$_SESSION[$value]}\n";
					}
				$email .= "\n"._DNSAVEEMAIL3.":\n";
				$email .= "$subquery\n\n";
				$email .= _DNSAVEEMAIL4.":\n";
				$email .= mysql_error()."\n\n";
				mail($surveyadminemail, _DNSAVEEMAIL5, $email);
				}
			else
				{
				$completed .= "<a href='javascript:location.reload()'>"._SUBMITAGAIN."</a><br /><br />\n";
				}
			}
		}
	
	foreach(file("$thistpl/completed.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	
	echo "\n<br />\n";
	foreach(file("$thistpl/endpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	exit;
	}

//LAST PHASE
if ($_POST['move'] == " "._LAST." " && !$notanswered)
	{
	//READ TEMPLATES, INSERT DATA AND PRESENT PAGE
	echo "<html>\n";
	foreach(file("$thistpl/startpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	echo "\n<form method='post' action='{$_SERVER['PHP_SELF']}' id='phpsurveyor' name='phpsurveyor'>\n";
	
	echo "\n\n<!-- START THE SURVEY -->\n";
	foreach(file("$thistpl/survey.pstpl") as $op)
		{
		echo "\t\t".templatereplace($op);
		}
	//READ SUBMIT TEMPLATE
	foreach(file("$thistpl/submit.pstpl") as $op)
		{
		echo "\t\t\t".templatereplace($op);
		}
	
	$navigator = surveymover();
	echo "\n\n<!-- PRESENT THE NAVIGATOR -->\n";
	foreach(file("$thistpl/navigator.pstpl") as $op)
		{
		echo "\t\t".templatereplace($op);
		}
	echo "\n";
	foreach(file("$thistpl/endpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	echo "\n";
	echo "\n<input type='hidden' name='thisstep' value='{$_SESSION['step']}'>\n";
	echo "\n<input type='hidden' name='sid' value='$sid'>\n";
	echo "\n<input type='hidden' name='token' value='$token'>\n";
	echo "\n</form>\n</html>";
	exit;
	}

//SEE IF $sid EXISTS
if ($surveyexists <1)
	{
	echo "<html>\n";
	//SURVEY DOES NOT EXIST. POLITELY EXIT.
	foreach(file("$thistpl/startpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	echo "\t<center><br />\n";
	echo "\t"._SURVEYNOEXIST."<br />&nbsp;\n";	
	foreach(file("$thistpl/endpage.pstpl") as $op)
		{
		echo templatereplace($op);
		}
	exit;
	}

//RUN THIS IF THIS IS THE FIRST TIME
if (!$_SESSION['step'])
	{
	if ($tokensexist == 1 && !$_GET['token'])
		{
		echo "<html>\n";
		//NO TOKEN PRESENTED. EXPLAIN PROBLEM AND PRESENT FORM
		foreach(file("$thistpl/startpage.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		foreach(file("$thistpl/survey.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		echo "\t<center><br />\n";
		echo "\t<center><br />\n";
		echo "\t"._NOTOKEN1."<br /><br />\n";
		echo "\t"._NOTOKEN2."<br />&nbsp;\n";
		echo "\t<table align='center'>";
		echo "\t<form method='get' action='{$_SERVER['PHP_SELF']}'>\n";
		echo "\t<input type='hidden' name='sid' value='$sid'>\n";
		echo "\t\t<tr>\n";
		echo "\t\t\t<td align='center' valign='middle'>\n";
		echo "\t\t\t"._TOKEN.": <input class='text' type='text' name='token'>\n";
		echo "\t\t\t<input class='submit' type='submit' value='"._CONTINUE."'>\n";
		echo "\t\t\t</td>\n";
		echo "\t\t</tr>\n";
		echo "\t</form>\n";
		echo "\t</table>\n";
		echo "\t<br />&nbsp;</center>\n";
		foreach(file("$thistpl/endpage.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		exit;
		}
	if ($tokensexist == 1 && $_GET['token'])
		{
		//check if token actually does exist
		$tkquery = "SELECT * FROM {$dbprefix}tokens_$sid WHERE token='{$_GET['token']}' AND completed != 'Y'";
		$tkresult = mysql_query($tkquery);
		$tkexist = mysql_num_rows($tkresult);

		if (!$tkexist)
			{
			echo "<html>\n";
			//TOKEN DOESN'T EXIST OR HAS ALREADY BEEN USED. EXPLAIN PROBLEM AND EXIT
			foreach(file("$thistpl/startpage.pstpl") as $op)
				{
				echo templatereplace($op);
				}
			foreach(file("$thistpl/survey.pstpl") as $op)
				{
				echo "\t".templatereplace($op);
				}
			echo "\t<center><br />\n";
			echo "\t"._NOTOKEN1."<br /><br />\n";
			echo "\t"._NOTOKEN3."\n";
			echo "\t"._FURTHERINFO." $surveyadminname (<a href='mailto:$surveyadminemail'>$surveyadminemail</a>)<br /><br />\n";
			echo "\t<a href='javascript:window.close()'>"._CLOSEWIN."</a><br />&nbsp;\n";
			foreach(file("$thistpl/endpage.pstpl") as $op)
				{
				echo templatereplace($op);
				}
			exit;
			}
		}
	//RESET ALL THE SESSION VARIABLES AND START AGAIN
	unset($_SESSION['grouplist']);
	unset($_SESSION['fieldarray']);
	unset($_SESSION['insertarray']);

	//LETS COUNT THE NUMBER OF GROUPS (That's how many steps there will be)
	$query = "SELECT * FROM {$dbprefix}groups WHERE sid=$sid ORDER BY group_name";
	$result = mysql_query($query) or die ("Couldn't get group list<br />$query<br />".mysql_error());
	$_SESSION['totalsteps'] = 1;
	while ($row = mysql_fetch_array($result)){$_SESSION['grouplist'][]=array($row['gid'], $row['group_name'], $row['description']);}
	//NOW LETS BUILD THE SESSION VARIABLES
	$query = "SELECT * FROM {$dbprefix}questions, {$dbprefix}groups WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}questions.sid=$sid ORDER BY group_name";
	$result = mysql_query($query);
	$totalquestions = mysql_num_rows($result);
	if ($totalquestions == "0")	//break out if there are no questions!
		{
		echo "<html>\n";
		foreach(file("$thistpl/startpage.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		foreach(file("$thistpl/survey.pstpl") as $op)
			{
			echo "\t".templatereplace($op);
			}
		echo "\t<center><br />\n";
		echo "\t"._NOQUESTIONS."<br /><br />\n";
		echo "\t"._FURTHERINFO." $surveyadminname (<a href='mailto:$surveyadminemail'>$surveyadminemail</a>)<br /><br />\n";
		echo "\t<a href='javascript:window.close()'>"._CLOSEWIN."</a><br />&nbsp;\n";
		foreach(file("$thistpl/endpage.pstpl") as $op)
			{
			echo templatereplace($op);
			}
		exit;
		}

	$arows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
	while ($row = mysql_fetch_assoc($result)) {$arows[] = $row;} // Get table output into array
	usort($arows, 'CompareGroupThenTitle'); // Perform a case insensitive natural sort on group name then question title of a multidimensional array
		if (!$_SESSION['insertarray'])
		{
		if ($surveyprivate == "N")
			{
			$_SESSION['token'] = $token;
			$_SESSION['insertarray'][]= "token";
			}
	
		foreach ($arows as $arow)
			{
			//WE ARE CREATING A SESSION VARIABLE FOR EVERY FIELD IN THE SURVEY
			$fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}";
			if ($arow['type'] == "M" || $arow['type'] == "A" || $arow['type'] == "B" || $arow['type'] == "C" || $arow['type'] == "E" || $arow['type'] == "F" || $arow['type'] == "P") 
				{
				$abquery = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND sid=$sid AND {$dbprefix}questions.qid={$arow['qid']} ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
				$abresult = mysql_query($abquery);
				while ($abrow = mysql_fetch_array($abresult))
					{
					$_SESSION['insertarray'][] = "$fieldname".$abrow['code'];
					$alsoother = "";
					if ($abrow['other'] == "Y") {$alsoother = "Y";}
					if ($arow['type'] == "P")
						{
						$_SESSION['insertarray'][] = "$fieldname".$abrow['code']."comment";	
						}
					}
				if ($alsoother)
					{
					$_SESSION['insertarray'][] = "$fieldname"."other";
					if ($arow['type'] == "P")
						{
						$_SESSION['insertarray'][] = "$fieldname"."othercomment";	
						}
					}
				}
			elseif ($arow['type'] == "R") 
				{
				$abquery = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND sid=$sid AND {$dbprefix}questions.qid={$arow['qid']} ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
				$abresult = mysql_query($abquery);
				$abcount = mysql_num_rows($abresult);
				for ($i=1; $i<=$abcount; $i++)
					{
					$_SESSION['insertarray'][] = "$fieldname".$i;
					}			
				}
			elseif ($arow['type'] == "Q")
				{
				$abquery = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND sid=$sid AND {$dbprefix}questions.qid={$arow['qid']} ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
				$abresult = mysql_query($abquery);
				while ($abrow = mysql_fetch_array($abresult))
					{
					$_SESSION['insertarray'][] = "$fieldname".$abrow['code'];
					}
				}
			elseif ($arow['type'] == "O")	
				{
				$_SESSION['insertarray'][] = "$fieldname";
				$fn2 = "$fieldname"."comment";
				$_SESSION['insertarray'][] = "$fn2";
				} 
			else
				{
				$_SESSION['insertarray'][] = "$fieldname";
				}

			//Check to see if there are any conditions set for this question
			if (conditionscount($arow['qid']) > 0)
				{
				$conditions = "Y";
				}
			else
				{
				$conditions = "N";
				}
			//echo "F$fieldname, {$arow['title']}, {$arow['question']}, {$arow['type']}<br />\n"; //MORE DEBUGGING STUFF
			//NOW WE'RE CREATING AN ARRAY CONTAINING EACH FIELD AND RELEVANT INFO
			//ARRAY CONTENTS - [0]=questions.qid, [1]=fieldname, [2]=questions.title, [3]=questions.question
			//                 [4]=questions.type, [5]=questions.gid, [6]=questions.mandatory, [7]=conditionsexist?
			$_SESSION['fieldarray'][] = array("{$arow['qid']}", "$fieldname", "{$arow['title']}", "{$arow['question']}", "{$arow['type']}", "{$arow['gid']}", "{$arow['mandatory']}", $conditions);
			}
		}
	$_SESSION['step'] = 1;
	}

//******************************************************************************************************
//PRESENT SURVEY
//******************************************************************************************************

//GET GROUP DETAILS
$conditions=array();
foreach ($_SESSION['grouplist'] as $gl)
	{
	$gid=$gl[0];
	foreach ($_SESSION['fieldarray'] as $ia)
		{
		if ($ia[5] == $gid)
			{
			include("qanda.php");
			} 
		}
	}

//READ TEMPLATES, INSERT DATA AND PRESENT PAGE
echo "<html>\n";
foreach(file("$thistpl/startpage.pstpl") as $op)
	{
	echo templatereplace($op);
	}
echo "\n<form method='post' action='{$_SERVER['PHP_SELF']}' id='phpsurveyor' name='phpsurveyor'>\n";
//PUT LIST OF FIELDS INTO HIDDEN FORM ELEMENT
echo "\n\n<!-- INPUT NAMES -->\n";
echo "\t<input type='hidden' name='fieldnames' value='";
echo implode("|", $inputnames);
echo "'>\n";

foreach(file("$thistpl/welcome.pstpl") as $op)
	{
	echo templatereplace($op);
	}

echo "\n\n<!-- JAVASCRIPT FOR CONDITIONAL QUESTIONS -->\n";
echo "\t<script type='text/javascript'>\n";
echo "\t<!--\n";
echo "\t\tfunction checkconditions(value, name, type)\n";
echo "\t\t\t{\n";
if (is_array($conditions))
	{
	echo "\t\t\tif (type == 'radio')\n";
	echo "\t\t\t\t{\n";
	echo "\t\t\t\tvar hiddenformname='java'+name;\n";
	echo "\t\t\t\tdocument.getElementById(hiddenformname).value=value;\n";
	echo "\t\t\t\t}\n";
	$java="";
	$cqcount=1;
	foreach ($conditions as $cd)
		{
		if ($oldq != $cd[0]) //New if statement
			{
			$java .= $endzone;
			$endzone = "";
			$cqcount=1;
			$java .= "\n\t\t\tif ((";
			}
		if (!$oldcq) {$oldcq = $cd[2];}
		if ($cd[4] == "L" && $dropdowns == "R") //Just in case the dropdown threshold is being applied, check number of answers here
			{
			$cccquery="SELECT code FROM {$dbprefix}answers WHERE qid={$cd[1]}";
			$cccresult=mysql_query($cccquery);
			$cccount=mysql_num_rows($cccresult);
			}
		if ($cd[4] == "R") 	{$idname="fvalue".substr($cd[2], strlen($cd[5]), strlen($cd[2]-strlen($cd[5])));}
		elseif ($cd[4] == "5" || $cd[4] == "A" || $cd[4] == "B" || $cd[4] == "C" || $cd[4] == "E" || $cd[4] == "F" || $cd[4] == "G" || $cd[4] == "Y" || ($cd[4] == "L" && $dropdowns == "R" && $cccount <= $dropdownthreshold))
							{$idname="java$cd[2]";}
		elseif ($cd[4] == "M" || $cd[4] == "O" || $cd[4] == "P")
							{$idname="$cd[2]$cd[3]";}
		else				{$idname=$cd[2];}
		if ($cqcount > 1 && $oldcq ==$cd[2]) {$java .= " || ";}
		elseif ($cqcount >1 && $oldcq != $cd[2]) {$java .= ") && (";}
		if ($cd[3] == '') {$java .= "document.getElementById('$idname').value == ' ' || !document.getElementById('$idname').value";}
		elseif($cd[4] == "M" || $cd[4] == "O" || $cd[4] == "P")
			{$java .= "document.getElementById('$idname').checked";}
		else {$java .= "document.getElementById('$idname').value == '$cd[3]'";}
		if ($oldq != $cd[0])//Close if statement
			{
			$endzone = "))\n";
			$endzone .= "\t\t\t\t{\n";
			$endzone .= "\t\t\t\tdocument.getElementById('$cd[0]').style.display='';\n";
			$endzone .= "\t\t\t\tdocument.getElementById('display$cd[0]').value='on';\n";
			$endzone .= "\t\t\t\t}\n";
			$endzone .= "\t\t\telse\n";
			$endzone .= "\t\t\t\t{\n";
			$endzone .= "\t\t\t\tdocument.getElementById('$cd[0]').style.display='none';\n";
			$endzone .= "\t\t\t\tdocument.getElementById('display$cd[0]').value='';\n";
			$endzone .= "\t\t\t\t}\n";
			$cqcount++;
			}
		$oldq = $cd[0]; //Update oldq for next loop
		$oldcq = $cd[2];  //Update oldcq for next loop
		}
	$java .= $endzone;
	}
echo $java;
echo "\t\t\t}\n";
echo "\t//-->\n";
echo "\t</script>\n\n";

foreach ($_SESSION['grouplist'] as $gl)
	{
	$gid=$gl[0];
	$groupname=$gl[1];
	$groupdescription=$gl[2];
//	echo "&nbsp;\n";
	echo "\n\n<!-- START THE GROUP -->\n";
	foreach(file("$thistpl/startgroup.pstpl") as $op)
		{
		echo "\t".templatereplace($op);
		}
	echo "\n";
	
	if ($groupdescription)
		{
		foreach(file("$thistpl/groupdescription.pstpl") as $op)
			{
			echo "\t\t".templatereplace($op);
			}
		}
	echo "\n";
	
	echo "\n\n<!-- PRESENT THE QUESTIONS -->\n";
	if (is_array($qanda))
		{
		foreach ($qanda as $qa)
			{
			if ($gl[0] == $qa[6])
				{
				echo "\n\t<!-- NEW QUESTION -->\n";
				echo "\t\t\t\t<div name='$qa[4]' id='$qa[4]'";
				if ($qa[3] != "Y") {echo ">\n";} else {echo " style='display: none'>\n";}
				$question=$qa[0];
				$answer=$qa[1];
				$help=$qa[2];
				$questioncode=$qa[5];
				foreach(file("$thistpl/question.pstpl") as $op)
					{
					echo "\t\t\t\t\t".templatereplace($op)."\n";
					}
				echo "\t\t\t\t</div>\n";
				}
			}
		}

	echo "\n\n<!-- END THE GROUP -->\n";
	foreach(file("$thistpl/endgroup.pstpl") as $op)
		{
		echo "\t\t\t\t".templatereplace($op);
		}
	echo "\n";
	}
//echo "&nbsp;\n";
$navigator = surveymover();
echo "\n\n<!-- PRESENT THE NAVIGATOR -->\n";
foreach(file("$thistpl/navigator.pstpl") as $op)
	{
	echo "\t\t".templatereplace($op);
	}
echo "\n";

if ($surveyactive != "Y") {echo "\t\t<center><font color='red' size='2'>"._NOTACTIVE."</font></center>\n";}
foreach(file("$thistpl/endpage.pstpl") as $op)
	{
	echo templatereplace($op);
	}
echo "\n";
	
if (is_array($conditions)) //if conditions exist, create hidden inputs for previously answered questions
	{
	foreach (array_keys($_SESSION) as $SESak)
		{
		if (in_array($SESak, $_SESSION['insertarray']))
			{
			echo "<input type='hidden' name='java$SESak' id='java$SESak' value='" . $_SESSION[$SESak] . "'>\n";
			}
		}
	}
//SOME STUFF FOR MANDATORY QUESTIONS
if (is_array($mandatorys))
	{
	$mandatory=implode("|", $mandatorys);
	echo "<input type='hidden' name='mandatory' value='$mandatory'>\n";
	}
if (is_array($conmandatorys))
	{
	$conmandatory=implode("|", $conmandatorys);
	echo "<input type='hidden' name='conmandatory' value='$conmandatory'>\n";
	}
if (is_array($mandatoryfns))
	{
	$mandatoryfn=implode("|", $mandatoryfns);
	echo "<input type='hidden' name='mandatoryfn' value='$mandatoryfn'>\n";
	}
if (is_array($conmandatoryfns))
	{
	$conmandatoryfn=implode("|", $conmandatoryfns);
	echo "<input type='hidden' name='conmandatoryfn' value='$conmandatoryfn'>\n";
	}

echo "<input type='hidden' name='thisstep' value='{$_SESSION['step']}'>\n";
echo "<input type='hidden' name='sid' value='$sid'>\n";
echo "<input type='hidden' name='token' value='$token'>\n";
echo "</form>\n</html>";

function surveymover()
	{
	global $sid, $surveyformat;
	if ($_SESSION['step'] > 0 && $surveyformat != "A")
		{$surveymover .= "<input class='submit' type='submit' value=' << "._PREV." ' name='move' />\n";}
	if ($_SESSION['step'] && (!$_SESSION['totalsteps'] || ($_SESSION['step'] < $_SESSION['totalsteps'])))
		{$surveymover .=  "\t\t\t\t\t<input class='submit' type='submit' value=' "._NEXT." >> ' name='move' />\n";}
	if (!$_SESSION['step'])
		{$surveymover .=  "\t\t\t\t\t<input class='submit' type='submit' value=' "._NEXT." >> ' name='move' />\n";}
	if ($_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && $presentinggroupdescription == "yes")
		{$surveymover .=  "\t\t\t\t\t<input class='submit' type='submit' value=' "._NEXT." >> ' name='move' />\n";}
	if ($_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && !$presentinggroupdescription && $surveyformat != "A")
		{$surveymover .= "\t\t\t\t\t<input class='submit' type='submit' value=' "._LAST." ' name='move' />\n";}
	if ($_SESSION['step'] && ($_SESSION['step'] == $_SESSION['totalsteps']) && !$presentinggroupdescription && $surveyformat == "A")
		{$surveymover .= "\t\t\t\t\t<input class='submit' type='submit' value=' "._SUBMIT." ' name='move' />\n";}
	return $surveymover;	
	}


?>
