<?php
/*
#############################################################
# >>> PHPSurveyor  										#
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
require_once(dirname(__FILE__).'/../config.php');

$action = returnglobal('action');
$surveyid = returnglobal('sid');
$id = returnglobal('id');
$saver['scid']=returnglobal('save_scid');
$surveytable = "{$dbprefix}survey_$surveyid";

include_once("login_check.php");

$actsurquery = "SELECT browse_response FROM {$dbprefix}surveys_rights WHERE sid=$surveyid AND uid = ".$_SESSION['loginID']; //Getting rights for this survey
$actsurresult = $connect->Execute($actsurquery) or die($connect->ErrorMsg());		
$actsurrows = $actsurresult->FetchRow();

if($actsurrows['browse_response']){	
	
	$surveyoptions = browsemenubar();
	if (!$database_exists)
	{
		//echo "</table>\n";
		echo "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._("Data Entry")."</strong></font></td></tr>\n"
		."\t<tr  bgcolor='#CCCCCC'><td align='center'>$setfont\n"
		."<strong><font color='red'>"._("Error")."</font></strong><br />\n"
		._("The defined surveyor database does not exist")."<br />\n"
		._("Either your selected database has not yet been created or there is a problem accessing it.")."<br /><br />\n"
		."<input type='submit' value='"
		._("Main Admin Screen")."' onClick=\"window.open('$scriptname', '_top')\"></font><br />\n"
		."</td></tr></table>\n"
		."</body>\n";
		exit;
	}
	if (!$surveyid && !$action)
	{
		//echo "</table>\n";
		echo "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._("Data Entry")."</strong></font></td></tr>\n"
		."\t<tr bgcolor='#CCCCCC'><td align='center'>$setfont\n"
		."<strong><font color='red'>"._("Error")."</font></strong><br />\n"
		._("You have not selected a survey for data-entry.")."<br /><br />\n"
		."<input type='submit' value='"
		._("Main Admin Screen")."' onClick=\"window.open('$scriptname', '_top')\"><br />\n"
		."</font></td></tr></table>\n"
		."</body>\n";
		exit;
	}
	
	if ($action == "edit" || $action == "" || $action == "editsaved")
	{
		GetBaseLanguageFromSurveyID($surveyid);
	}
	
	if ($action == "insert")
	{
		echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._("Data Entry")."</strong></font></td></tr>\n"
		."\t<tr bgcolor='#CCCCCC'><td align='center'>$setfont\n";
	
		if (isset($_POST['save']) && $_POST['save'] == "on")
		{
			//Save this, don't submit to final response table
			GetBaseLanguageFromSurveyID($surveyid);
			$saver['identifier']=returnglobal('save_identifier');
			$saver['password']=returnglobal('save_password');
			$saver['passwordconfirm']=returnglobal('save_confirmpassword');
			$saver['email']=returnglobal('save_email');
			if (!returnglobal('redo'))
			{
				$password=md5($saver['password']);
			}
			else
			{
				$password=$saver['password'];
			}
			$errormsg="";
			if (!$saver['identifier']) {$errormsg .= _("Error").": "._("You must supply a name for this saved session.");}
			if (!$saver['password']) {$errormsg .= _("Error").": "._("You must supply a password for this saved session.");}
			if ($saver['password'] != $saver['passwordconfirm']) {$errormsg .= _("Error").": "._("Your passwords do not match.");}
			if (!$errormsg && $saver['identifier'] && !returnglobal('redo'))
			{
				//All the fields are correct. Now make sure there's not already a matching saved item
				$query = "SELECT * FROM {$dbprefix}saved_control\n"
				."WHERE sid=$surveyid\n"
				."AND identifier='".$saver['identifier']."'\n"
				."AND access_code='$password'\n";
				$result = $connect->Execute($query) or die("Error checking for duplicates!<br />$query<br />".htmlspecialchars($connect->ErrorMsg()));
				if ($result->RecordCount() > 0)
				{
					$errormsg.=_("This name has already been used for this survey. You must use a unique save name.")."<br />\n";
				}
			}
			if ($errormsg)
			{
				echo $errormsg;
				echo "Try again:<br />
					   <form method='post'>
					  <table class='outlinetable' cellspacing='0' align='center'>
					  <tr>
					   <td align='right'>"._("Identifier:")."</td>
					   <td><input type='text' name='save_identifier' value='".$_POST['save_identifier']."'></td></tr>
					  <tr><td align='right'>"._("Password:")."</td>
					   <td><input type='password' name='save_password' value='".$_POST['save_password']."'></td></tr>
					  <tr><td align='right'>"._("Confirm Password:")."</td>
					   <td><input type='password' name='save_confirmpassword' value='".$_POST['save_confirmpassword']."'></td></tr>
					  <tr><td align='right'>"._("Email:")."</td>
					   <td><input type='text' name='save_email' value='".$_POST['save_email']."'>\n";
				foreach ($_POST as $key=>$val)
				{
					if (substr($key, 0, 4) != "save" && $key != "action" && $key != "surveytable" && $key !="sid" && $key != "datestamp" && $key !="ipaddr")
					{
						echo "<input type='hidden' name='$key' value='$val'>\n";
					}
				}
				echo "</td></tr><tr><td></td><td><input type='submit' value='"._("submit")."'>
					 <input type='hidden' name='sid' value='$surveyid'>
					 <input type='hidden' name='surveytable' value='".$_POST['surveytable']."'>
					 <input type='hidden' name='action' value='".$_POST['action']."'>
					 <input type='hidden' name='save' value='on'></td>";
				if (isset($_POST['datestamp']))
				{
					echo "<input type='hidden' name='datestamp' value='".$_POST['datestamp']."'>\n";
				}
				if (isset($_POST['ipaddr']))
				{
					echo "<input type='hidden' name='ipaddr' value='".$_POST['ipaddr']."'>\n";
				}echo "</table></form>\n";
			}
			else
			{
				if (returnglobal('redo')=="yes")
				{
					//Delete all the existing entries
					$delete="DELETE FROM {$dbprefix}saved
							 WHERE scid=".$saver['scid'];
					$result=$connect->Execute($delete) or die("Couldn't delete old record<br />$delete<br />".htmlspecialchars($connect->ErrorMsg()));
					$delete="DELETE FROM {$dbprefix}saved_control
							 WHERE scid=".$saver['scid'];
					$result=$connect->Execute($delete) or die("Couldn't delete old record<br />$delete<br />".htmlspecialchars($connect->ErrorMsg()));
				}
				$insert1=$connect->GetInsertSQL("{$dbprefix}saved_control", array(
				'sid' => $surveyid,
				'identifier' => $saver['identifier'],
				'access_code' => $password,
				'email' => $saver['email'],
				'ip' => $_SERVER['REMOTE_ADDR'],
				'saved_thisstep' => 0,
				'status' => 'S',
				'saved_date' => date("Y-m-d H:i:s")));
				if ($result1=$connect->Execute($insert1))
				{
					//control table entry worked, lets do the rest
					$scid=$connect->Insert_ID();
					foreach ($_POST as $key=>$val)
					{
						if (substr($key, 0, 4) != "save" && $key != "action" && $key != "surveytable" && $key !="sid" && $key != "datestamp" && $key != "ipaddr")
						{
							if($val)
							{
								$insert="INSERT INTO {$dbprefix}saved\n"
								. "(`scid`,\n`datestamp`, `fieldname`,\n`ipaddr`,"
								. "`value`)\n"
								."VALUES (\n"
								."'$scid',\n"
								."'".date("Y-m-d H:i:s")."',\n"
								."'".$key."',\n"
								."NULL,\n"
								."'".$val."')\n";
								//echo "$insert<br />\n";
								if (!$result=$connect->Execute($insert))
								{
									$failed=1;
								}
							}
						}
					}
					if (!isset($failed) || $failed < 1)
					{
						echo "<font color='green'>"._("Your survey responses have been saved succesfully")."</font><br />\n";
						if ($saver['email'])
						{
							//Send email
							if (validate_email($saver['email']) && !returnglobal('redo'))
							{
								$subject=_("Saved Survey Details");
								$message=_("You, or someone using your email address, have saved a survey in progress. The following details can be used to return to this survey and continue where you left off.");
								$message.="\n\n".$thissurvey['name']."\n\n";
								$message.=_("Name").": ".$saver['identifier']."\n";
								$message.=_("Password").": ".$saver['password']."\n\n";
								$message.=_("Reload your survey by clicking on the following URL:").":\n";
								$message.=$homeurl."/dataentry.php?sid=$surveyid&action=editsaved&identifier=".$saver['identifier']."&accesscode=".$saver['password']."&public=true";
								$from = $thissurvey['adminemail'];
	
								if (MailTextMessage($message, $subject, $saver['email'], $from, $sitename))
								{
									$emailsent="Y";
									echo "<font color='green'>"._("An email has been sent with details about your saved survey")."</font><br />\n";
								}
							}
						}
					}
					else
					{
						echo "<font color='red'>"._("An error occurred and your survey responses were not saved.")."</font><br />\n<pre>$insert</pre>".htmlspecialchars($connect->ErrorMsg())."<br />\n";
					}
				}
				else
				{
					echo "ERROR: $insert1<br />".htmlspecialchars($connect->ErrorMsg());
				}
			}
		}
		else
		{
			//BUILD THE SQL TO INSERT RESPONSES
			$iquery = "SELECT * FROM {$dbprefix}questions, {$dbprefix}groups WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}questions.sid=$surveyid ORDER BY {$dbprefix}groups.group_order, title";
			$iresult = db_execute_assoc($iquery);
			$col_name="";
			$insertqr="";
			while ($irow = $iresult->FetchRow())
			{
				if ($irow['type'] != "M" && $irow['type'] != "A" && $irow['type'] != "B" && $irow['type'] != "C" && $irow['type'] != "E" && $irow['type'] != "F" && $irow['type'] != "H" && $irow['type'] != "P" && $irow['type'] != "O" && $irow['type'] != "R" && $irow['type'] != "Q" && $irow['type'] != "J")
				{
					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
					if (isset($_POST[$fieldname]))
					{
						$col_name .= "`$fieldname`, \n";
						$insertqr .= "'" . auto_escape($_POST[$fieldname]) . "', \n";
					}
				}
				elseif ($irow['type'] == "O")
				{
					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
					$fieldname2 = $fieldname . "comment";
					$col_name .= "`$fieldname`, \n`$fieldname2`, \n";
					$insertqr .= "'" . auto_escape($_POST[$fieldname]) . "', \n'" . auto_escape($_POST[$fieldname2]) . "', \n";
				}
				elseif ($irow['type'] == "R")
				{
					$i2query = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}questions.qid={$irow['qid']} AND {$dbprefix}questions.sid=$surveyid ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
					$i2result = $connect->Execute($i2query);
					$i2count = $i2result->RecordCount();
					for ($i=1; $i<=$i2count; $i++)
					{
						$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}$i";
						$col_name .= "`$fieldname`, \n";
						$insertqr .= "'" . auto_escape($_POST["d$fieldname"]) . "', \n";
					}
				}
				else
				{
					$i2query = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}questions.qid={$irow['qid']} AND {$dbprefix}questions.sid=$surveyid ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
					$i2result = db_execute_assoc($i2query);
					while ($i2row = $i2result->FetchRow())
					{
						$otherexists = "";
						$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2row['code']}";
						if (isset($_POST[$fieldname]))
						{
							$col_name .= "`$fieldname`, \n";
							$insertqr .= "'" . auto_escape($_POST[$fieldname]) . "', \n";
							$otherexists = "";
							if ($i2row['other'] == "Y" and ($irow['type']=="!" or $irow['type']=="L" or $irow['type']=="M" or $irow['type']=="P")) {$otherexists = "Y";}
							if ($irow['type'] == "P")
							{
								$fieldname2 = $fieldname."comment";
								$col_name .= "`$fieldname2`, \n";
								$insertqr .= "'" . auto_escape($_POST[$fieldname2]) . "', \n";
							}
						}
					}
					if (isset($otherexists) && $otherexists == "Y")
					{
						$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}other";
						$col_name .= "`$fieldname`, \n";
						$insertqr .= "'" . auto_escape($_POST[$fieldname]) . "', \n";
					}
				}
			}
	
			$col_name = substr($col_name, 0, -3); //Strip off the last comma-space
			$insertqr = substr($insertqr, 0, -3); //Strip off the last comma-space
	
			//NOW SHOW SCREEN
			if (isset($_POST['token']) && $_POST['token']) //handle tokens if survey needs them
			{
				$col_name .= ", token\n";
				$insertqr .= ", '{$_POST['token']}'";
			}
			if (isset($_POST['datestamp']) && $_POST['datestamp']) //handle datestamp if needed
			{
				$col_name .= ", datestamp\n";
				$insertqr .= ", '{$_POST['datestamp']}'";
			}
			if (isset($_POST['ipaddr']) && $_POST['ipaddr']) //handle datestamp if needed
			{
				$col_name .= ", ipaddr\n";
				$insertqr .= ", '{$_POST['ipaddr']}'";
			}
			//		echo "\t\t\t<strong>Inserting data</strong><br />\n"
			//			."SID: $surveyid, ($surveytable)<br /><br />\n";
			$SQL = "INSERT INTO $surveytable
					($col_name)
					VALUES 
					($insertqr)";
			//echo $SQL; //Debugging line
			$iinsert = $connect->Execute($SQL) or die ("Could not insert your data:<br />$SQL<br />\n" . htmlspecialchars($connect->ErrorMsg()) . "\n<pre style='text-align: left'>$SQL</pre>\n</body>\n");
			if (returnglobal('redo')=="yes")
			{
				//This submission of data came from a saved session. Must delete the
				//saved session now that it has been recorded in the responses table
				$dquery = "DELETE FROM {$dbprefix}saved_control
						  WHERE scid=".$saver['scid'];
				if ($dresult=$connect->Execute($dquery))
				{
					$dquery = "DELETE FROM {$dbprefix}saved
							  WHERE scid=".$saver['scid'];
					$dresult=$connect->Execute($dquery) or die("Couldn't delete saved data<br />$dquery<br />".htmlspecialchars($connect->ErrorMsg()));
				}
				else
				{
					echo "Couldn't delete saved data<br />$dquery<br />".htmlspecialchars($connect->ErrorMsg());
				}
			}
			echo "\t\t\t<font color='green'><strong>"._("Success")."</strong></font><br />\n";
	
			$fquery = "SELECT id FROM $surveytable ORDER BY id DESC LIMIT 1";
			$fresult = db_execute_assoc($fquery);
			while ($frow = $fresult->FetchRow())
			{
				echo "\t\t\t"._("The entry was assigned the following record id: ")." {$frow['id']}<br />\n";
				$thisid=$frow['id'];
			}
		}
	
	
		echo "\t\t\t</font><br />[<a href='dataentry.php?sid=$surveyid'>"._("Add Another Record")."</a>]<br />\n";
		echo "[<a href='$scriptname?sid=$surveyid'>"._("Return to Survey Administration")."</a><br />\n";
		if (isset($thisid))
		{
			echo "\t\t\t[<a href='browse.php?sid=$surveyid&action=id&id=$thisid'>"._("View This Record")."</a>]<br />\n";
		}
		if (isset($_POST['save']) && $_POST['save'] == "on")
		{
			echo "\t\t\t[<a href='saved.php?sid=$surveyid&action=all'>"._("Browse Saved Responses")."</a>]<br />\n";
		}
		echo "\t\t\t[<a href='browse.php?sid=$surveyid&action=all&limit=50'>"._("Browse Responses")."</a>]<br />\n"
		."\t</td></tr>\n"
		."</table>\n"
		."</body>\n";
	
	}
	
	elseif ($action == "edit" || $action == "editsaved")
	{
		echo "<table width='99%' align='center' style='margin: 3px 6px; border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._("Browse Responses")."</strong></font></td></tr>\n";
		if (isset($surveyheader)) {echo $surveyheader;}
		echo $surveyoptions
		."</table>\n";
	
		//FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
		$fnquery = "SELECT * FROM {$dbprefix}questions, {$dbprefix}groups, {$dbprefix}surveys WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}questions.sid={$dbprefix}surveys.sid AND {$dbprefix}questions.sid='$surveyid'";
		$fnresult = db_execute_assoc($fnquery);
		$fncount = $fnresult->RecordCount();
		//echo "$fnquery<br /><br />\n";
		$arows = array(); //Create an empty array in case FetchRow does not return any rows
		while ($fnrow = $fnresult->FetchRow())
		{
			$fnrows[] = $fnrow;
			$private=$fnrow['private'];
			$datestamp=$fnrow['datestamp'];
			$ipaddr=$fnrow['ipaddr'];
		} // Get table output into array
		// Perform a case insensitive natural sort on group name then question title of a multidimensional array
		usort($fnrows, 'CompareGroupThenTitle');
		// $fnames = (Field Name in Survey Table, Short Title of Question, Question Type, Field Name, Question Code, Predetermined Answers if exist)
		$fnames[] = array("id", "id", "id", "id", "id", "id", "id", "");
	
		if ($private == "N") //show token info if survey not private
		{
			$fnames[] = array ("token", "Token ID", "Token", "token", "TID", "", "");
		}
		if ($datestamp == "Y")
		{
			$fnames[] = array ("datestamp", "Date Stamp", "Datestamp", "datestamp", "datestamp", "", "");
		}
		if ($ipaddr == "Y")
		{
			$fnames[] = array ("ipaddr", "IP Address", "IP Address", "ipaddr", "ipaddr", "", "");
		}
		$fcount=0;
		foreach ($fnrows as $fnrow)
		{
			$fcount++;
			$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}";
			$ftitle = "Grp{$fnrow['gid']}Qst{$fnrow['title']}";
			$fquestion = $fnrow['question'];
			if ($fnrow['type'] == "M" || $fnrow['type'] == "A" || $fnrow['type'] == "B" || $fnrow['type'] == "C" || $fnrow['type'] == "E" || $fnrow['type'] == "F" || $fnrow['type'] == "H" || $fnrow['type'] == "P" || $fnrow['type'] == "Q" || $fnrow['type'] == "^" || $fnrow['type'] == "J")
			{
				$fnrquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$fnrow['qid']} ORDER BY sortorder, answer";
				$fnrresult = db_execute_assoc($fnrquery);
				while ($fnrrow = $fnrresult->FetchRow())
				{
					$fnames[] = array("$field{$fnrrow['code']}", "$ftitle ({$fnrrow['code']})", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
					if ($fnrow['type'] == "P")
					{
						$fnames[] = array("$field{$fnrrow['code']}"."comment", "$ftitle"."comment", "{$fnrow['question']}(comment)", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
					}
				}
				if ($fnrow['other'] == "Y")
				{
					$fnames[] = array("$field"."other", "$ftitle"."other", "{$fnrow['question']}(other)", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
					if ($fnrow['type'] == "P")
					{
						$fnames[] = array("$field"."othercomment", "$ftitle"."othercomment", "{$fnrow['question']}(other comment)", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
					}
				}
			}
			elseif ($fnrow['type'] == "R")
			{
				$fnrquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$fnrow['qid']} ORDER BY sortorder, answer";
				$fnrresult = $connect->Execute($fnrquery);
				$fnrcount = $fnrresult->RecordCount();
				for ($j=1; $j<=$fnrcount; $j++)
				{
					$fnames[] = array("$field$j", "$ftitle ($j)", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "$j", "{$fnrow['qid']}", "{$fnrow['lid']}");
				}
			}
			elseif ($fnrow['type'] == "O")
			{
				$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
				$field2 = $field."comment";
				$ftitle2 = $ftitle."[Comment]";
				$longtitle = "{$fnrow['question']}<br />(Comment)";
				$fnames[] = array("$field2", "$ftitle", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
			}
			else
			{
				if (!isset($fnrrow)) {$fnrrow=array("code"=>"", "answer"=>"");}
				$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
				if (($fnrow['type'] == "L" || $fnrow['type'] == "!") && $fnrow['other'] =="Y")
				{
					$fnames[] = array("$field"."other", "$ftitle"."other", "{$fnrow['question']}(other)", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
				}
			}
		}
		$nfncount = count($fnames)-1;
	
		//	foreach ($fnames as $fnm)
		//		{
		//echo "<!-- DEBUG FNAMES: $fnm[0], $fnm[1], $fnm[2], $fnm[3], $fnm[4], $fnm[5], $fnm[6]";
		//if (isset($fnm[7])){echo $fnm[7];}
		//echo ",";
		//if (isset($fnm[8])) {echo $fnm[8];}
		//echo " -->\n";
		//		}
	
		//SHOW INDIVIDUAL RECORD
		if ($action == "edit")
		{
			$idquery = "SELECT * FROM $surveytable WHERE id=$id";
			$idresult = db_execute_assoc($idquery) or die ("Couldn't get individual record<br />$idquery<br />".htmlspecialchars($connect->ErrorMsg()));
			while ($idrow = $idresult->FetchRow())
			{
				$results[]=$idrow;
			}
		}
		elseif ($action == "editsaved")
		{
			if (isset($_GET['public']) && $_GET['public']=="true")
			{
				$password=md5($_GET['accesscode']);
			}
			else
			{
				$password=$_GET['accesscode'];
			}
			$svquery = "SELECT * FROM {$dbprefix}saved_control
						WHERE sid=$surveyid
						AND identifier='".$_GET['identifier']."'
						AND access_code='".$password."'";
			$svresult=db_execute_assoc($svquery) or die("Error getting save<br />$svquery<br />".htmlspecialchars($connect->ErrorMsg()));
			while($svrow=$svresult->FetchRow())
			{
				$saver['email']=$svrow['email'];
				$saver['scid']=$svrow['scid'];
				$saver['ip']=$svrow['ip'];
			}
			$svquery = "SELECT * FROM {$dbprefix}saved WHERE scid=".$saver['scid'];
			$svresult=db_execute_assoc($svquery) or die("Error getting saved info<br />$svquery<br />".htmlspecialchars($connect->ErrorMsg()));
			while($svrow=$svresult->FetchRow())
			{
				$responses[$svrow['fieldname']]=$svrow['value'];
			} // while
			$fieldmap = createFieldMap($surveyid);
			foreach($fieldmap as $fm)
			{
				if (isset($responses[$fm['fieldname']]))
				{
					$results1[$fm['fieldname']]=$responses[$fm['fieldname']];
				}
				else
				{
					$results1[$fm['fieldname']]="";
				}
			}
			$results1['id']="";
			$results1['datestamp']=date("Y-m-d H:i:s");
			$results1['ipaddr']=$saver['ip'];
			$results[]=$results1;
		}
		//	echo "<pre>";print_r($results);echo "</pre>";
	
		echo "<form method='post' action='dataentry.php' name='editsurvey' id='editsurvey'>\n"
		."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._("Data Entry")."</strong></font></td></tr>\n"
		."\t<tr><td style='border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #555555' colspan='2' bgcolor='#999999' align='center'>$setfont<strong>"
		._("Editing Response")." (ID $id)</strong></font></td></tr>\n"
		."\t<tr><td colspan='2' bgcolor='#CCCCCC' height='1'></td></tr>\n";
	
		foreach ($results as $idrow)
		{
			//echo "<pre>"; print_r($idrow);echo "</pre>";
			for ($i=0; $i<$nfncount+1; $i++)
			{
				//echo "<pre>"; print_r($fnames[$i]);echo "</pre>";
				$answer = $idrow[$fnames[$i][0]];
				$question=$fnames[$i][2];
				echo "\t<tr>\n"
				."\t\t<td bgcolor='#EEEEEE' valign='top' align='right' width='25%'>$setfont"
				."\n";
				echo "\t\t\t<strong>{$fnames[$i][2]}</strong>\n";
				echo "\t\t</font></td>\n"
				."\t\t<td valign='top'>\n";
				//echo "\t\t\t-={$fnames[$i][3]}=-"; //Debugging info
				switch ($fnames[$i][3])
				{
					case "X": //Boilerplate question
					echo "";
					break;
					case "Q":
					echo "\t\t\t{$fnames[$i][6]}&nbsp;<input type='text' name='{$fnames[$i][0]}' value='"
					.$idrow[$fnames[$i][0]] . "'>\n";
					break;
					case "id":
					echo "\t\t\t{$idrow[$fnames[$i][0]]} <font color='red' size='1'>"._("Cannot be modified")."</font>\n";
					break;
					case "5": //5 POINT CHOICE radio-buttons
					for ($x=1; $x<=5; $x++)
					{
						echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='$x'";
						if ($idrow[$fnames[$i][0]] == $x) {echo " checked";}
						echo " />$x \n";
					}
					break;
					case "D": //DATE
					echo "\t\t\t<input type='text' size='10' name='{$fnames[$i][0]}' value='{$idrow[$fnames[$i][0]]}' />\n";
					break;
					case "G": //GENDER drop-down list
					echo "\t\t\t<select name='{$fnames[$i][0]}'>\n"
					."\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {echo " selected";}
					echo ">"._("Please choose")."..</option>\n"
					."\t\t\t\t<option value='F'";
					if ($idrow[$fnames[$i][0]] == "F") {echo " selected";}
					echo ">"._("Female")."</option>\n"
					."\t\t\t\t<option value='M'";
					if ($idrow[$fnames[$i][0]] == "M") {echo " selected";}
					echo ">"._("Male")."</option>\n"
					."\t\t\t</select>\n";
					break;
					case "W":
					case "Z":
					if (substr($fnames[$i][0], -5) == "other")
					{
						echo "\t\t\t$setfont<input type='text' name='{$fnames[$i][0]}' value='"
						.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' /></font>\n";
					}
					else
					{
						$lquery = "SELECT * FROM {$dbprefix}labels WHERE lid={$fnames[$i][8]} ORDER BY sortorder, code";
						$lresult = db_execute_assoc($lquery);
						//$lquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$fnames[$i][7]} ORDER BY sortorder, answer";
						//$lresult = $connect->Execute($lquery);
						echo "\t\t\t<select name='{$fnames[$i][0]}'>\n"
						."\t\t\t\t<option value=''";
						if ($idrow[$fnames[$i][0]] == "") {echo " selected";}
						echo ">"._("Please choose")."..</option>\n";
	
						while ($llrow = $lresult->FetchRow())
						{
							echo "\t\t\t\t<option value='{$llrow['code']}'";
							if ($idrow[$fnames[$i][0]] == $llrow['code']) {echo " selected";}
							echo ">{$llrow['title']}</option>\n";
						}
						$oquery="SELECT other FROM {$dbprefix}questions WHERE qid={$fnames[$i][7]}";
						$oresult=db_execute_assoc($oquery) or die("Couldn't get other for list question<br />".$oquery."<br />".htmlspecialchars($connect->ErrorMsg()));
						while($orow = $oresult->FetchRow())
						{
							$fother=$orow['other'];
						}
						if ($fother =="Y")
						{
							echo "<option value='-oth-'";
							if ($idrow[$fnames[$i][0]] == "-oth-"){echo " selected";}
							echo ">"._("Other")."</option>\n";
						}
						echo "\t\t\t</select>\n";
					}
					break;
					case "L": //LIST drop-down
					case "!": //List (Radio)
					if (substr($fnames[$i][0], -5) == "other")
					{
						echo "\t\t\t$setfont<input type='text' name='{$fnames[$i][0]}' value='"
						.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' /></font>\n";
					}
					else
					{
						$lquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$fnames[$i][7]} ORDER BY sortorder, answer";
						$lresult = db_execute_assoc($lquery);
						echo "\t\t\t<select name='{$fnames[$i][0]}'>\n"
						."\t\t\t\t<option value=''";
						if ($idrow[$fnames[$i][0]] == "") {echo " selected";}
						echo ">"._("Please choose")."..</option>\n";
	
						while ($llrow = $lresult->FetchRow())
						{
							echo "\t\t\t\t<option value='{$llrow['code']}'";
							if ($idrow[$fnames[$i][0]] == $llrow['code']) {echo " selected";}
							echo ">{$llrow['answer']}</option>\n";
						}
						$oquery="SELECT other FROM {$dbprefix}questions WHERE qid={$fnames[$i][7]}";
						$oresult=db_execute_assoc($oquery) or die("Couldn't get other for list question<br />".$oquery."<br />".htmlspecialchars($connect->ErrorMsg()));
						while($orow = $oresult->FetchRow())
						{
							$fother=$orow['other'];
						}
						if ($fother =="Y")
						{
							echo "<option value='-oth-'";
							if ($idrow[$fnames[$i][0]] == "-oth-"){echo " selected";}
							echo ">"._("Other")."</option>\n";
						}
						echo "\t\t\t</select>\n";
					}
					break;
					case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
					$lquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$fnames[$i][7]} ORDER BY sortorder, answer";
					$lresult = db_execute_assoc($lquery);
					echo "\t\t\t<select name='{$fnames[$i][0]}'>\n"
					."\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {echo " selected";}
					echo ">"._("Please choose")."..</option>\n";
	
					while ($llrow = $lresult->FetchRow())
					{
						echo "\t\t\t\t<option value='{$llrow['code']}'";
						if ($idrow[$fnames[$i][0]] == $llrow['code']) {echo " selected";}
						echo ">{$llrow['answer']}</option>\n";
					}
					$i++;
					echo "\t\t\t</select>\n"
					."\t\t\t<br />\n"
					."\t\t\t<textarea cols='45' rows='5' name='{$fnames[$i][0]}'>"
					.htmlspecialchars($idrow[$fnames[$i][0]]) . "</textarea>\n";
					break;
					case "R": //RANKING TYPE QUESTION
					$l=$i;
					$thisqid=$fnames[$l][7];
					$myfname=substr($fnames[$i][0], 0, -1);
					while ($fnames[$i][3] == "R")
					{
						//Let's get all the existing values into an array
						if ($idrow[$fnames[$i][0]])
						{
							$currentvalues[] = $idrow[$fnames[$i][0]];
						}
						$i++;
					}
					$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$thisqid ORDER BY sortorder, answer";
					$ansresult = db_execute_assoc($ansquery);
					$anscount = $ansresult->RecordCount();
					echo "\t\t\t<script type='text/javascript'>\n"
					."\t\t\t<!--\n"
					."\t\t\t\tfunction rankthis_$thisqid(\$code, \$value)\n"
					."\t\t\t\t\t{\n"
					."\t\t\t\t\t\$index=document.editsurvey.CHOICES_$thisqid.selectedIndex;\n"
					."\t\t\t\t\tdocument.editsurvey.CHOICES_$thisqid.selectedIndex=-1;\n"
					."\t\t\t\t\tfor (i=1; i<=$anscount; i++)\n"
					."\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\t\$b=i;\n"
					."\t\t\t\t\t\t\$b += '';\n"
					."\t\t\t\t\t\t\$inputname=\"RANK_$thisqid\"+\$b;\n"
					."\t\t\t\t\t\t\$hiddenname=\"d$myfname\"+\$b;\n"
					."\t\t\t\t\t\t\$cutname=\"cut_$thisqid\"+i;\n"
					."\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='none';\n"
					."\t\t\t\t\t\tif (!document.getElementById(\$inputname).value)\n"
					."\t\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\t\tdocument.getElementById(\$inputname).value=\$value;\n"
					."\t\t\t\t\t\t\tdocument.getElementById(\$hiddenname).value=\$code;\n"
					."\t\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='';\n"
					."\t\t\t\t\t\t\tfor (var b=document.getElementById('CHOICES_$thisqid').options.length-1; b>=0; b--)\n"
					."\t\t\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\t\t\tif (document.getElementById('CHOICES_$thisqid').options[b].value == \$code)\n"
					."\t\t\t\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').options[b] = null;\n"
					."\t\t\t\t\t\t\t\t\t}\n"
					."\t\t\t\t\t\t\t\t}\n"
					."\t\t\t\t\t\t\ti=$anscount;\n"
					."\t\t\t\t\t\t\t}\n"
					."\t\t\t\t\t\t}\n"
					."\t\t\t\t\tif (document.getElementById('CHOICES_$thisqid').options.length == 0)\n"
					."\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').disabled=true;\n"
					."\t\t\t\t\t\t}\n"
					."\t\t\t\t\t}\n"
					."\t\t\t\tfunction deletethis_$thisqid(\$text, \$value, \$name, \$thisname)\n"
					."\t\t\t\t\t{\n"
					."\t\t\t\t\tvar qid='$thisqid';\n"
					."\t\t\t\t\tvar lngth=qid.length+4;\n"
					."\t\t\t\t\tvar cutindex=\$thisname.substring(lngth, \$thisname.length);\n"
					."\t\t\t\t\tcutindex=parseFloat(cutindex);\n"
					."\t\t\t\t\tdocument.getElementById(\$name).value='';\n"
					."\t\t\t\t\tdocument.getElementById(\$thisname).style.display='none';\n"
					."\t\t\t\t\tif (cutindex > 1)\n"
					."\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\t\$cut1name=\"cut_$thisqid\"+(cutindex-1);\n"
					."\t\t\t\t\t\t\$cut2name=\"d$myfname\"+(cutindex);\n"
					."\t\t\t\t\t\tdocument.getElementById(\$cut1name).style.display='';\n"
					."\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n"
					."\t\t\t\t\t\t}\n"
					."\t\t\t\t\telse\n"
					."\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\t\$cut2name=\"d$myfname\"+(cutindex);\n"
					."\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n"
					."\t\t\t\t\t\t}\n"
					."\t\t\t\t\tvar i=document.getElementById('CHOICES_$thisqid').options.length;\n"
					."\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').options[i] = new Option(\$text, \$value);\n"
					."\t\t\t\t\tif (document.getElementById('CHOICES_$thisqid').options.length > 0)\n"
					."\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').disabled=false;\n"
					."\t\t\t\t\t\t}\n"
					."\t\t\t\t\t}\n"
					."\t\t\t//-->\n"
					."\t\t\t</script>\n";
					while ($ansrow = $ansresult->FetchRow()) //Now we're getting the codes and answers
					{
						$answers[] = array($ansrow['code'], $ansrow['answer']);
					}
					//now find out how many existing values there are
	
					$chosen[]=""; //create array
					if (!isset($ranklist)) {$ranklist="";}
	
					if (isset($currentvalues))
					{
						$existing = count($currentvalues);
					}
					else {$existing=0;}
					for ($j=1; $j<=$anscount; $j++) //go through each ranking and check for matching answer
					{
						$k=$j-1;
						if (isset($currentvalues) && $currentvalues[$k])
						{
							foreach ($answers as $ans)
							{
								if ($ans[0] == $currentvalues[$k])
								{
									$thiscode=$ans[0];
									$thistext=$ans[1];
								}
							}
						}
						$ranklist .= "\t\t\t\t\t\t&nbsp;<font color='#000080'>$j:&nbsp;<input style='width:150; color: #222222; font-size: 10; background-color: silver' id='RANK_$thisqid$j'";
						if (isset($currentvalues) && $currentvalues[$k])
						{
							$ranklist .= " value='"
							. $thistext
							. "'";
						}
						$ranklist .= " onFocus=\"this.blur()\">\n"
						. "\t\t\t\t\t\t<input type='hidden' id='d$myfname$j' name='d$myfname$j' value='";
						if (isset($currentvalues) && $currentvalues[$k])
						{
							$ranklist .= $thiscode;
							$chosen[]=array($thiscode, $thistext);
						}
						$ranklist .= "'>\n"
						. "\t\t\t\t\t\t<img src='$imagefiles/cut.gif' alt='"._("Remove this item")."' title='"._("Remove this item")."' ";
						if ($j != $existing)
						{
							$ranklist .= "style='display:none'";
						}
						$ranklist .= " id='cut_$thisqid$j' onClick=\"deletethis_$thisqid(document.editsurvey.RANK_$thisqid$j.value, document.editsurvey.d$myfname$j.value, document.editsurvey.RANK_$thisqid$j.id, this.id)\"></font><br />\n\n";
					}
	
					if (!isset($choicelist)) {$choicelist="";}
					$choicelist .= "\t\t\t\t\t\t<select size='$anscount' name='CHOICES' id='CHOICES_$thisqid' onClick=\"rankthis_$thisqid(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" style='background-color: #EEEFFF; font-family: verdana; font-size: 12; color: #000080; width: 150'>\n";
					foreach ($answers as $ans)
					{
						if (!in_array($ans, $chosen))
						{
							$choicelist .= "\t\t\t\t\t\t\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
						}
					}
					$choicelist .= "\t\t\t\t\t\t</select>\n";
					echo "\t\t\t<table align='left' border='0' cellspacing='5'>\n"
					."\t\t\t\t<tr>\n"
					."\t\t\t\t\t<td align='left' valign='top' width='200' style='border: solid 1 #111111' bgcolor='silver'>\n"
					."\t\t\t\t\t\t$setfont<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
					._("Your Choices").":</strong><br />\n"
					."&nbsp;&nbsp;&nbsp;&nbsp;".$choicelist
					."\t\t\t\t\t</font></td>\n"
					."\t\t\t\t\t<td align='left' bgcolor='silver' width='200' style='border: solid 1 #111111'>\n"
					."\t\t\t\t\t\t$setfont<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
					._("Your Ranking").":</strong></font><br />\n"
					.$ranklist
					."\t\t\t\t\t</td>\n"
					."\t\t\t\t</tr>\n"
					."\t\t\t</table>\n"
					."\t\t\t<input type='hidden' name='multi' value='$anscount' />\n"
					."\t\t\t<input type='hidden' name='lastfield' value='";
					if (isset($multifields)) {echo $multifields;}
					echo "' />\n";
					$choicelist="";
					$ranklist="";
					unset($answers);
					$i--;
					break;
	
					case "M": //MULTIPLE OPTIONS checkbox
					$qidattributes=getQuestionAttributes($fnames[$i][7]);
					if ($displaycols=arraySearchByKey("display_columns", $qidattributes, "attribute", 1))
					{
						$dcols=$displaycols['value'];
					}
					else
					{
						$dcols=0;
					}
	
					while ($fnames[$i][3] == "M" && $question != "" && $question == $fnames[$i][2])
					{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
						//echo substr($fnames[$i][0], strlen($fnames[$i][0])-5, 5)."<br />\n";
						if (substr($fnames[$i][0], -5) == "other")
						{
							echo "\t\t\t$setfont<input type='text' name='{$fnames[$i][0]}' value='"
							.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' /></font>\n";
						}
						else
						{
							echo "\t\t\t$setfont<input type='checkbox' name='{$fnames[$i][0]}' value='Y'";
							if ($idrow[$fnames[$i][0]] == "Y") {echo " checked";}
							echo " />{$fnames[$i][6]}</font><br />\n";
						}
						if ($i<$nfncount)
						{
							$i++;
						}
						else
						{
							$i++;
							break;
						}
					}
					$i--;
					break;
	
					case "J": //FILE CSV MORE
					while ($fnames[$i][3] == "U" && $question != "" && $question == $fnames[$i][2])
					{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
						echo "\t\t\t$setfont<input type='checkbox' name='{$fnames[$i][0]}' value='Y'";
						if ($idrow[$fnames[$i][0]] == "Y") {echo " checked";}
						echo " />{$fnames[$i][6]}<br />\n";
						if ($i<$nfncount)
						{
							$i++;
						}
						else
						{
							$i++;
							break;
						}
					}
					$i--;
					break;
	
					case "I": //FILE CSV ONE
					$lquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$fnames[$i][7]} ORDER BY sortorder, answer";
					$lresult = db_execute_assoc($lquery);
					echo "\t\t\t<select name='{$fnames[$i][0]}'>\n"
					."\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {echo " selected";}
					echo ">"._("Please choose")."..</option>\n";
	
					while ($llrow = $lresult->FetchRow())
					{
						echo "\t\t\t\t<option value='{$llrow['code']}'";
						if ($idrow[$fnames[$i][0]] == $llrow['code']) {echo " selected";}
						echo ">{$llrow['answer']}</option>\n";
					}
					echo "\t\t\t</select>\n";
					break;
	
					case "P": //MULTIPLE OPTIONS WITH COMMENTS checkbox + text
					echo "<table>\n";
					while ($fnames[$i][3] == "P")
					{
						$thefieldname=$fnames[$i][0];
						if (substr($thefieldname, -7) == "comment")
						{
							echo "\t\t<td>$setfont<input type='text' name='{$fnames[$i][0]}' size='50' value='"
							.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' /></font></td>\n"
							."\t</tr>\n";
						}
						elseif (substr($fnames[$i][0], -5) == "other")
						{
							echo "\t<tr>\n"
							."\t\t<td>\n"
							."\t\t\t<input type='text' name='{$fnames[$i][0]}' size='30' value='"
							.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n"
							."\t\t</td>\n"
							."\t\t<td>\n";
							$i++;
							echo "\t\t\t<input type='text' name='{$fnames[$i][0]}' size='50' value='"
							.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n"
							."\t\t</td>\n"
							."\t</tr>\n";
						}
						else
						{
							echo "\t<tr>\n"
							."\t\t<td>$setfont<input type='checkbox' name=\"{$fnames[$i][0]}\" value='Y'";
							if ($idrow[$fnames[$i][0]] == "Y") {echo " checked";}
							echo " />{$fnames[$i][6]}</font></td>\n";
						}
						$i++;
					}
					echo "</table>\n";
					$i--;
					break;
					case "N": //NUMERICAL TEXT
					echo keycontroljs()
					."\t\t\t<input type='text' name='{$fnames[$i][0]}' value='{$idrow[$fnames[$i][0]]}' "
					."onKeyPress=\"return goodchars(event,'0123456789.,')\" />\n";
					break;
					case "S": //SHORT FREE TEXT
					echo "\t\t\t<input type='text' name='{$fnames[$i][0]}' value='"
					.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n";
					break;
					case "T": //LONG FREE TEXT
					echo "\t\t\t<textarea rows='5' cols='45' name='{$fnames[$i][0]}'>"
					.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "</textarea>\n";
					break;
					case "U": //HUGE FREE TEXT
					echo "\t\t\t<textarea rows='50' cols='70' name='{$fnames[$i][0]}'>"
					.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "</textarea>\n";
					break;
					case "Y": //YES/NO radio-buttons
					echo "\t\t\t<select name='{$fnames[$i][0]}'>\n"
					."\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {echo " selected";}
					echo ">"._("Please choose")."..</option>\n"
					."\t\t\t\t<option value='Y'";
					if ($idrow[$fnames[$i][0]] == "Y") {echo " selected";}
					echo ">"._("Yes")."</option>\n"
					."\t\t\t\t<option value='N'";
					if ($idrow[$fnames[$i][0]] == "N") {echo " selected";}
					echo ">"._("No")."</option>\n"
					."\t\t\t</select>\n";
					break;
					case "A": //ARRAY (5 POINT CHOICE) radio-buttons
					echo "<table>\n";
					$thisqid=$fnames[$i][7];
					while ($fnames[$i][7] == $thisqid)
					{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
						echo "\t<tr>\n"
						."\t\t<td align='right'>$setfont{$fnames[$i][6]}</font></td>\n"
						."\t\t<td>$setfont\n";
						for ($j=1; $j<=5; $j++)
						{
							echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='$j'";
							if ($idrow[$fnames[$i][0]] == $j) {echo " checked";}
							echo " />$j&nbsp;\n";
						}
						echo "\t\t</font></td>\n"
						."\t</tr>\n";
						$i++;
					}
					echo "</table>\n";
					$i--;
					break;
					case "B": //ARRAY (10 POINT CHOICE) radio-buttons
					echo "<table>\n";
					$thisqid=$fnames[$i][7];
					while ($fnames[$i][7] == $thisqid)
					{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
						echo "\t<tr>\n"
						."\t\t<td align='right'>$setfont{$fnames[$i][6]}</font></td>\n"
						."\t\t<td>$setfont\n";
						for ($j=1; $j<=10; $j++)
						{
							echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='$j'";
							if ($idrow[$fnames[$i][0]] == $j) {echo " checked";}
							echo " />$j&nbsp;\n";
						}
						echo "\t\t</font></td>\n"
						."\t</tr>\n";
						$i++;
					}
					$i--;
					echo "</table>\n";
					break;
					case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
					echo "<table>\n";
					$thisqid=$fnames[$i][7];
					while ($fnames[$i][7] == $thisqid)
					{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
						echo "\t<tr>\n"
						."\t\t<td align='right'>$setfont{$fnames[$i][6]}</font></td>\n"
						."\t\t<td>$setfont\n"
						."\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='Y'";
						if ($idrow[$fnames[$i][0]] == "Y") {echo " checked";}
						echo " />"._("Yes")."&nbsp;\n"
						."\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='U'";
						if ($idrow[$fnames[$i][0]] == "U") {echo " checked";}
						echo " />"._("Uncertain")."&nbsp;\n"
						."\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='N'";
						if ($idrow[$fnames[$i][0]] == "N") {echo " checked";}
						echo " />"._("No")."&nbsp;\n"
						."\t\t</font></td>\n"
						."\t</tr>\n";
						$i++;
					}
					$i--;
					echo "</table>\n";
					break;
					case "E": //ARRAY (Increase/Same/Decrease) radio-buttons
					echo "<table>\n";
					$thisqid=$fnames[$i][7];
					while ($fnames[$i][7] == $thisqid)
					{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
						echo "\t<tr>\n"
						."\t\t<td align='right'>$setfont{$fnames[$i][6]}</font></td>\n"
						."\t\t<td>$setfont\n"
						."\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='I'";
						if ($idrow[$fnames[$i][0]] == "I") {echo " checked";}
						echo " />Increase&nbsp;\n"
						."\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='S'";
						if ($idrow[$fnames[$i][0]] == "I") {echo " checked";}
						echo " />Same&nbsp;\n"
						."\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='D'";
						if ($idrow[$fnames[$i][0]] == "D") {echo " checked";}
						echo " />Decrease&nbsp;\n"
						."\t\t</font></td>\n"
						."\t</tr>\n";
						$i++;
					}
					$i--;
					echo "</table>\n";
					break;
					case "F": //ARRAY (Flexible Labels)
					case "H":
					echo "<table>\n";
					$thisqid=$fnames[$i][7];
					while (isset($fnames[$i][7]) && $fnames[$i][7] == $thisqid)
					{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
						echo "\t<tr>\n"
						."\t\t<td align='right' valign='top'>$setfont{$fnames[$i][6]}</font></td>\n";
						$fquery = "SELECT * FROM {$dbprefix}labels WHERE lid='{$fnames[$i][8]}' order by sortorder, code";
						$fresult = db_execute_assoc($fquery);
						echo "\t\t<td>$setfont\n";
						while ($frow=$fresult->FetchRow())
						{
							echo "\t\t\t<input type='radio' name='{$fnames[$i][0]}' value='{$frow['code']}'";
							if ($idrow[$fnames[$i][0]] == $frow['code']) {echo " checked";}
							echo " />".$frow['title']."&nbsp;\n";
						}
						echo "\t\t</font></td>\n"
						."\t</tr>\n";
						$i++;
					}
					$i--;
					echo "</table>\n";
					break;
					default: //This really only applies to tokens for non-private surveys
					echo "\t\t\t<input type='text' name='{$fnames[$i][0]}' value='"
					.$idrow[$fnames[$i][0]] . "'>\n";
					break;
				}
				echo "		</td>
							</tr>
							<tr>
								<td colspan='2' bgcolor='#CCCCCC' height='1'>
								</td>
							</tr>\n";
			}
		}
		echo "</table>\n"
		."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
		if ($action == "edit")
		{
			echo "	<tr>
						<td bgcolor='#CCCCCC' align='center'>
						 <input type='submit' value='"._("Update Entry")."'>
						 <input type='hidden' name='id' value='$id'>
						 <input type='hidden' name='sid' value='$surveyid'>
						 <input type='hidden' name='action' value='update'>
						 <input type='hidden' name='surveytable' value='{$dbprefix}survey_$surveyid'>
						</td>
					</tr>\n";
		}
		elseif ($action == "editsaved")
		{
			echo "<script type='text/javascript'>
				  <!--
					function saveshow(value)
						{
						if (document.getElementById(value).checked == true)
							{
							document.getElementById(\"saveoptions\").style.display=\"\";
							}
						else
							{
							document.getElementById(\"saveoptions\").style.display=\"none\";
							}
						}
				  //-->
				  </script>\n";
			echo "\t<tr>\n";
			echo "\t\t<td colspan='3' align='center' bgcolor='#CCCCCC'>$setfont\n";
			echo "\t\t\t<input type='checkbox' name='save' id='save' onChange='saveshow(this.id)' onLoad='saveshow(this.id)'><label for='save'>"._("Save as a partially completed survey")."</label>\n";
			echo "<div name='saveoptions' id='saveoptions' style='display: none'>\n";
			echo "<table align='center' class='outlinetable' cellspacing='0'>
				  <tr><td align='right'>"._("Identifier:")."</td>
				  <td><input type='text' name='save_identifier'";
			if (returnglobal('identifier'))
			{
				echo " value=\"".stripslashes(stripslashes(returnglobal('identifier')))."\"";
			}
			echo "></td></tr>
				  </table>\n"
			."<input type='hidden' name='save_password' value='".returnglobal('accesscode')."'>\n"
			."<input type='hidden' name='save_confirmpassword' value='".returnglobal('accesscode')."'>\n"
			."<input type='hidden' name='save_email' value='".$saver['email']."'>\n"
			."<input type='hidden' name='save_scid' value='".$saver['scid']."'>\n"
			."<input type='hidden' name='redo' value='yes'>\n";
			echo "\t\t</td>\n";
			echo "\t</tr>"
			."</div>\n";
			echo "	<tr>
					<td bgcolor='#CCCCCC' align='center'>
					 <input type='submit' value='"._("submit")."'>
					 <input type='hidden' name='sid' value='$surveyid'>
					 <input type='hidden' name='action' value='insert'>
					 <input type='hidden' name='surveytable' value='{$dbprefix}survey_$surveyid'>
					</td>
				</tr>\n";
		}
	
		echo  "</table>\n"
		."</form>\n";
	}
	
	
	elseif ($action == "update")
	{
		echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._("Data Entry")."</strong></font></td></tr>\n"
		."\t<tr><td align='center'>\n";
		$iquery = "SELECT * FROM {$dbprefix}questions, {$dbprefix}groups WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}questions.sid=$surveyid ORDER BY {$dbprefix}groups.group_order, title";
		$iresult = db_execute_assoc($iquery);
	
		$updateqr = "UPDATE $surveytable SET \n";
	
		while ($irow = $iresult->FetchRow())
		{
			if ($irow['type'] != "Q" && $irow['type'] != "M" && $irow['type'] != "P" && $irow['type'] != "A" && $irow['type'] != "B" && $irow['type'] != "C" && $irow['type'] != "E" && $irow['type'] != "F" && $irow['type'] != "H" && $irow['type'] != "O" && $irow['type'] != "R" && $irow['type'] != "^" && $irow['type'] != "J")
			{
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
				if (isset($_POST[$fieldname])) { $thisvalue=$_POST[$fieldname]; } else {$thisvalue="";}
				$updateqr .= "`$fieldname` = '" . auto_escape($thisvalue) . "', \n";
				unset($thisvalue);
				// handle ! other
				if ($irow['type'] == "!" && $irow['other'] == "Y")
				{
					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}other";
					if (isset($_POST[$fieldname])) {$thisvalue=$_POST[$fieldname];} else {$thisvalue="";}
					$updateqr .= "`$fieldname` = '" . auto_escape($thisvalue) . "', \n";
					unset($thisvalue);
				}
			}
			elseif ($irow['type'] == "O")
			{
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
				$updateqr .= "`$fieldname` = '" . $_POST[$fieldname] . "', \n";
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}comment";
				$updateqr .= "`$fieldname` = '" . auto_escape($_POST[$fieldname]) . "', \n";
			}
			elseif ($irow['type'] == "R")
			{
				$i2query = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}questions.qid={$irow['qid']} AND {$dbprefix}questions.sid=$surveyid ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
				$i2result = $connect->Execute($i2query);
				$i2count = $i2result->RecordCount();
				for ($x=1; $x<=$i2count; $x++)
				{
					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}$x";
					$updateqr .= "`$fieldname` = '" . auto_escape($_POST["d$fieldname"]) . "', \n";
				}
			}
			else
			{
				$i2query = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}questions.qid={$irow['qid']} AND {$dbprefix}questions.sid=$surveyid ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
				$i2result = db_execute_assoc($i2query);
				$otherexists = "";
				while ($i2row = $i2result->FetchRow())
				{
					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2row['code']}";
					if (isset($_POST[$fieldname])) {$thisvalue=$_POST[$fieldname];} else {$thisvalue="";}
					$updateqr .= "`$fieldname` = '" . $thisvalue . "', \n";
					if ($i2row['other'] == "Y") {$otherexists = "Y";}
					if ($irow['type'] == "P")
					{
						$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2row['code']}comment";
						$updateqr .= "`$fieldname` = '" . auto_escape($_POST[$fieldname]) . "', \n";
					}
					unset($thisvalue);
				}
				if ($otherexists == "Y")
				{
					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}other";
					if (isset($_POST[$fieldname])) {$thisvalue=$_POST[$fieldname];} else {$thisvalue="";}
					$updateqr .= "`$fieldname` = '" . auto_escape($thisvalue) . "', \n";
					unset($thisvalue);
				}
			}
		}
		$updateqr = substr($updateqr, 0, -3);
		if (isset($_POST['datestampe']) && $_POST['datestamp']) {$updateqr .= ", datestamp='{$_POST['datestamp']}'";}
		if (isset($_POST['ipaddr']) && $_POST['ipaddr']) {$updateqr .= ", ipaddr='{$_POST['ipaddr']}'";}
		if (isset($_POST['token']) && $_POST['token']) {$updateqr .= ", token='{$_POST['token']}'";}
		$updateqr .= " WHERE id=$id";
		$updateres = $connect->Execute($updateqr) or die("Update failed:<br />\n" . htmlspecialchars($connect->ErrorMsg()) . "\n<pre style='text-align: left'>$updateqr</pre>");
		$thissurvey=getSurveyInfo($surveyid);
		if (isset($thissurvey['autoredirect']) && $thissurvey['autoredirect']=='Y' && $thissurvey['url']) {
			session_write_close();
			$url=$thissurvey['url'];
			header("Location: $url");
		}
		while (ob_get_level() > 0) {
			ob_end_flush();
		}
		echo "<font color='green'><strong>"._("Success")."</strong></font><br />\n"
		._("Record has been updated.")."<br /><br />\n"
		."<a href='browse.php?sid=$surveyid&action=id&id=$id'>"._("View This Record")."</a>\n<br />\n"
		."<a href='browse.php?sid=$surveyid&action=all'>"._("Browse Responses")."</a><br />\n"
		."</td></tr></table>\n"
		."</body>\n";
	}
	
	elseif ($action == "delete")
	{
		$thissurvey=getSurveyInfo($surveyid);
		echo "<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._("Data Entry")."</strong></font></td></tr>\n"
		."\t<tr  bgcolor='#CCCCCC'><td align='center'>$setfont\n"
		."\t\t\t<strong>".$thissurvey['name']."</strong><br />\n"
		."\t\t\t".$thissurvey['description']."\n"
		."\t\t</font></td>\n"
		."\t</tr>\n";
		$delquery = "DELETE FROM $surveytable WHERE id=$id";
		echo "\t<tr>\n";
		$delresult = $connect->Execute($delquery) or die ("Couldn't delete record $id<br />\n".htmlspecialchars($connect->ErrorMsg()));
		echo "\t\t<td align='center'><br />$setfont<strong>"._("Record Deleted")." (ID: $id)</strong><br /><br />\n"
		."\t\t\t<a href='browse.php?sid=$surveyid&action=all'>"._("Browse Responses")."</a></font>\n"
		."\t\t</td>\n"
		."\t</tr>\n"
		."</table>\n"
		."</body>\n";
	}
	else
	{
		if (!isset($_GET['language'])) {$_GET['language']=GetBaseLanguageFromSurveyID($surveyid);} 
        $langlistbox = languageDropdown($surveyid,$_GET['language']);
		$thissurvey=getSurveyInfo($surveyid);
		//This is the default, presenting a blank dataentry form
		$fieldmap=createFieldMap($surveyid);
		// PRESENT SURVEY DATAENTRY SCREEN
		echo "<table width='99%' align='center' style='margin: 3px 6px; border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		._("Browse Responses")."</strong></font></td></tr>\n"
		.$surveyoptions
		."</table>";
		$slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
		$baselang = GetBaseLanguageFromSurveyID($surveyid);
		array_unshift($slangs,$baselang);
		
		if(!isset($_GET['language']) || !in_array($_GET['language'],$slangs))
		{
			$baselang = GetBaseLanguageFromSurveyID($surveyid);
		} else {
			$baselang = $_GET['language'];
		}
	
		echo "<form action='dataentry.php' name='addsurvey' method='post' id='addsurvey'>\n"
		."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='3' height='4'><font size='1' face='verdana' color='white'><strong>"
		._("Data Entry")."</strong></font></td></tr>\n"
		."\t<tr bgcolor='#777777'>\n"
		."\t\t<td align='left'>$langlistbox</td><td colspan='2' align='center'><font color='white'>\n"
		."\t\t\t<strong>".$thissurvey['name']."</strong>\n"
		."\t\t\t<br />$setfont".$thissurvey['description']."</font></font>\n"
		."\t\t</td>\n"
		."\t</tr>\n";
	
		if ($thissurvey['private'] == "N") //Give entry field for token id
		{
			echo "\t<tr>\n"
			."\t\t<td valign='top' width='1%'></td>\n"
			."\t\t<td valign='top' align='right' width='30%'>$setfont<strong>"._("Token").":</strong></font></td>\n"
			."\t\t<td valign='top' style='padding-left: 20px'>\n"
			."\t\t\t<input type='text' name='token'>\n"
			."\t\t</td>\n"
			."\t</tr>\n";
		}
		if ($thissurvey['datestamp'] == "Y") //Give datestampentry field
		{
			echo "\t<tr>\n"
			."\t\t<td valign='top' width='1%'></td>\n"
			."\t\t<td valign='top' align='right' width='30%'>$setfont<strong>"
			._("Datestamp").":</strong></font></td>\n"
			."\t\t<td valign='top' style='padding-left: 20px'>\n"
			."\t\t\t<input type='text' name='datestamp' value='$localtimedate'>\n"
			."\t\t</td>\n"
			."\t</tr>\n";
		}
		if ($thissurvey['ipaddr'] == "Y") //Give ipaddress field
		{
			echo "\t<tr>\n"
			."\t\t<td valign='top' width='1%'></td>\n"
			."\t\t<td valign='top' align='right' width='30%'>$setfont<strong>"
			._("IP-Address").":</strong></font></td>\n"
			."\t\t<td valign='top' style='padding-left: 20px'>\n"
			."\t\t\t<input type='text' name='ipaddr' value='NULL'>\n"
			."\t\t</td>\n"
			."\t</tr>\n";
		}
	
	
		// SURVEY NAME AND DESCRIPTION TO GO HERE
		$degquery = "SELECT * FROM {$dbprefix}groups WHERE sid=$surveyid AND language='{$baselang}' ORDER BY {$dbprefix}groups.group_order";
		$degresult = db_execute_assoc($degquery);
		// GROUP NAME
		while ($degrow = $degresult->FetchRow())
		{
			$deqquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid={$degrow['gid']} AND language='{$baselang}'";
			$deqresult = db_execute_assoc($deqquery);
			echo "\t<tr>\n"
			."\t\t<td colspan='3' align='center' bgcolor='#AAAAAA'>$setfont<strong>{$degrow['group_name']}</strong></font></td>\n"
			."\t</tr>\n";
			$gid = $degrow['gid'];
	
			//Alternate bgcolor for different groups
			$bgc="";
			if ($bgc == "#EEEEEE") {$bgc = "#DDDDDD";}
			else {$bgc = "#EEEEEE";}
			if (!$bgc) {$bgc = "#EEEEEE";}
	
			$deqrows = array(); //Create an empty array in case FetchRow does not return any rows
			while ($deqrow = $deqresult->FetchRow()) {$deqrows[] = $deqrow;} //Get table output into array
	
			// Perform a case insensitive natural sort on group name then question title of a multidimensional array
			usort($deqrows, 'CompareGroupThenTitle');
	
			foreach ($deqrows as $deqrow)
			{
				//GET ANY CONDITIONS THAT APPLY TO THIS QUESTION
				$explanation = ""; //reset conditions explanation
				$x=0;
				$distinctquery="SELECT DISTINCT cqid, {$dbprefix}questions.title FROM {$dbprefix}conditions, {$dbprefix}questions WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid AND {$dbprefix}conditions.qid={$deqrow['qid']} ORDER BY cqid";
				$distinctresult=db_execute_assoc($distinctquery);
				while ($distinctrow=$distinctresult->FetchRow())
				{
					if ($x > 0) {$explanation .= " <i>"._("AND")."</i><br />";}
					$conquery="SELECT cid, cqid, cfieldname, {$dbprefix}questions.title, {$dbprefix}questions.lid, {$dbprefix}questions.question, value, {$dbprefix}questions.type FROM {$dbprefix}conditions, {$dbprefix}questions WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid AND {$dbprefix}conditions.cqid={$distinctrow['cqid']} AND {$dbprefix}conditions.qid={$deqrow['qid']}";
					$conresult=db_execute_assoc($conquery);
					while ($conrow=$conresult->FetchRow())
					{
						switch($conrow['type'])
						{
							case "Y":
							switch ($conrow['value'])
							{
								case "Y": $conditions[]=_("Yes"); break;
								case "N": $conditions[]=_("No"); break;
							}
							break;
							case "G":
							switch($conrow['value'])
							{
								case "M": $conditions[]=_("Male"); break;
								case "F": $conditions[]=_("Female"); break;
							} // switch
							break;
							case "A":
							case "B":
							$conditions[]=$conrow['value'];
							break;
							case "C":
							switch($conrow['value'])
							{
								case "Y": $conditions[]=_("Yes"); break;
								case "U": $conditions[]=_("Uncertain"); break;
								case "N": $conditions[]=_("No"); break;
							} // switch
							break;
							case "E":
							switch($conrow['value'])
							{
								case "I": $conditions[]=_("Increase"); break;
								case "D": $conditions[]=_("Decrease"); break;
								case "S": $conditions[]=_("Same"); break;
							}
							case "F":
							case "H":
							default:
							$value=substr($conrow['cfieldname'], strpos($conrow['cfieldname'], "X".$conrow['cqid'])+strlen("X".$conrow['cqid']), strlen($conrow['cfieldname']));
							$fquery = "SELECT * FROM {$dbprefix}labels\n"
							. "WHERE lid='{$conrow['lid']}'\n"
							. "AND code='{$conrow['value']}'";
							$fresult=db_execute_assoc($fquery) or die("$fquery<br />".htmlspecialchars($connect->ErrorMsg()));
							while($frow=$fresult->FetchRow())
							{
								$postans=$frow['title'];
								$conditions[]=$frow['title'];
							} // while
							break;
						} // switch
						$answer_section="";
						switch($conrow['type'])
						{
							case "A":
							case "B":
							case "C":
							case "E":
							case "F":
							case "H":
							$thiscquestion=arraySearchByKey($conrow['cfieldname'], $fieldmap, "fieldname");
							$ansquery="SELECT answer FROM {$dbprefix}answers WHERE qid='{$conrow['cqid']}' AND code='{$thiscquestion[0]['aid']}' AND language='{$baselang}'";
							$ansresult=db_execute_assoc($ansquery);
							$i=0;
							while ($ansrow=$ansresult->FetchRow())
							{
								if (isset($conditions) && count($conditions) > 0)
								{
									$conditions[sizeof($conditions)-1]="(".$ansrow['answer'].") : ".end($conditions);
								}
							}
							$operator=_("AND");	// this is a dirty, DIRTY fix but it works since only array questions seem to be ORd
							break;
							default:
							$ansquery="SELECT answer FROM {$dbprefix}answers WHERE qid='{$conrow['cqid']}' AND code='{$conrow['value']}' AND language='{$baselang}'";
							$ansresult=db_execute_assoc($ansquery);
							while ($ansrow=$ansresult->FetchRow())
							{
								$conditions[]=$ansrow['answer'];
							}
							$operator=_("OR");
							$conditions = array_unique($conditions);
							break;
						}
					}
					if (isset($conditions) && count($conditions) > 1)
					{
						$conanswers = "'".implode("' ".$operator." '", $conditions)."'";
						$explanation .= " -" . str_replace("{ANSWER}", $conanswers, _("to question {QUESTION}, you answered {ANSWER}"));
					}
					else
					{
						$explanation .= " -" . str_replace("{ANSWER}", "'{$conditions[0]}'", _("to question {QUESTION}, you answered {ANSWER}"));
					}
					unset($conditions);
					$explanation = str_replace("{QUESTION}", "'{$distinctrow['title']}$answer_section'", $explanation);
					$x++;
				}
	
				if ($explanation)
				{
					$explanation = "<font color='maroon' size='1'>["._("Only answer this if the following conditions are met:")."]<br />$explanation\n";
					echo "<tr bgcolor='$bgc'><td colspan='3'>$setfont$explanation</font></td></tr>\n";
				}
	
				//END OF GETTING CONDITIONS
	
				$qid = $deqrow['qid'];
				$fieldname = "$surveyid"."X"."$gid"."X"."$qid";
				echo "\t<tr bgcolor='$bgc'>\n"
				."\t\t<td valign='top' width='1%'>$setfont<font size='1'>{$deqrow['title']}</font></font></td>\n"
				."\t\t<td valign='top' align='right' width='30%'>";
				if ($deqrow['mandatory']=="Y") //question is mandatory
				{
					echo "$setfont<font color='red'>*</font></font>";
				}
				echo "<strong>{$deqrow['question']}</strong></td>\n"
				."\t\t<td valign='top' style='padding-left: 20px'>\n";
				//DIFFERENT TYPES OF DATA FIELD HERE
				if ($deqrow['help'])
				{
					$hh = addcslashes($deqrow['help'], "\0..\37'\""); //Escape ASCII decimal 0-32 plus single and double quotes to make JavaScript happy.
					$hh = htmlspecialchars($hh, ENT_QUOTES); //Change & " ' < > to HTML entities to make HTML happy.
					echo "\t\t\t<img src='$imagefiles/help.gif' alt='"._("Help about this question")."' align='right' onClick=\"javascript:alert('Question {$deqrow['title']} Help: $hh')\" />\n";
				}
				switch($deqrow['type'])
				{
					case "5": //5 POINT CHOICE radio-buttons
					echo "\t\t\t<select name='$fieldname'>\n"
					."\t\t\t\t<option value=''>"._("No answer")."</option>\n";
					for ($x=1; $x<=5; $x++)
					{
						echo "\t\t\t\t<option value='$x'>$x</option>\n";
					}
					echo "\t\t\t</select>\n";
					break;
					case "D": //DATE
					echo "\t\t\t<input type='text' name='$fieldname' size='10' />\n";
					break;
					case "G": //GENDER drop-down list
					echo "\t\t\t<select name='$fieldname'>\n"
					."\t\t\t\t<option selected value=''>"._("Please choose")."..</option>\n"
					."\t\t\t\t<option value='F'>"._("Female")."</option>\n"
					."\t\t\t\t<option value='M'>"._("Male")."</option>\n"
					."\t\t\t</select>\n";
					break;
					case "Q": //MULTIPLE SHORT TEXT
					case "^": //Slider
					$deaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$dearesult = db_execute_assoc($deaquery);
					echo "\t\t\t<table>\n";
					while ($dearow = $dearesult->FetchRow())
					{
						echo "\t\t\t\t<tr><td align='right'>$setfont"
						.$dearow['answer']
						."</font></td>\n"
						."\t\t\t\t\t<td><input type='text' name='$fieldname{$dearow['code']}'></td>\n"
						."\t\t\t\t</tr>\n";
					}
					echo "\t\t\t</table>\n";
					break;
					case "W": //Flexible List drop-down/radio-button
					case "Z":
					$deaquery = "SELECT * FROM {$dbprefix}labels WHERE lid={$deqrow['lid']} ORDER BY sortorder, code";
					$dearesult = db_execute_assoc($deaquery);
					echo "\t\t\t<select name='$fieldname'>\n";
					while ($dearow = $dearesult->FetchRow())
					{
						echo "\t\t\t\t<option value='{$dearow['code']}'";
						echo ">{$dearow['title']}</option>\n";
					}
					echo "\t\t\t\t<option selected value=''>"._("Please choose")."..</option>\n";
	
					$oquery="SELECT other FROM {$dbprefix}questions WHERE qid={$deqrow['qid']} AND language='{$baselang}";
					$oresult=db_execute_assoc($oquery) or die("Couldn't get other for list question<br />".$oquery."<br />".htmlspecialchars($connect->ErrorMsg()));
					while($orow = $oresult->FetchRow())
					{
						$fother=$orow['other'];
					}
					if ($fother == "Y")
					{
						echo "<option value='-oth-'>"._("Other")."</option>\n";
					}
					echo "\t\t\t</select>\n";
					if ($fother == "Y")
					{
						echo "\t\t\t$setfont"
						._("Other").":</font>"
						."<input type='text' name='{$fieldname}other' value='' />\n";
					}
					break;
					case "L": //LIST drop-down/radio-button list
					case "!":
					$defexists="";
					$deaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$dearesult = db_execute_assoc($deaquery);
					echo "\t\t\t<select name='$fieldname'>\n";
					while ($dearow = $dearesult->FetchRow())
					{
						echo "\t\t\t\t<option value='{$dearow['code']}'";
						if ($dearow['default_value'] == "Y") {echo " selected"; $defexists = "Y";}
						echo ">{$dearow['answer']}</option>\n";
					}
					if (!$defexists) {echo "\t\t\t\t<option selected value=''>"._("Please choose")."..</option>\n";}
	
					$oquery="SELECT other FROM {$dbprefix}questions WHERE qid={$deqrow['qid']} AND language='{$baselang}";
					$oresult=db_execute_assoc($oquery) or die("Couldn't get other for list question<br />".$oquery."<br />".htmlspecialchars($connect->ErrorMsg()));
					while($orow = $oresult->FetchRow())
					{
						$fother=$orow['other'];
					}
					if ($fother == "Y")
					{
						echo "<option value='-oth-'>"._("Other")."</option>\n";
					}
					echo "\t\t\t</select>\n";
					if ($fother == "Y")
					{
						echo "\t\t\t$setfont"
						._("Other").":</font>"
						."<input type='text' name='{$fieldname}other' value='' />\n";
					}
					break;
					case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
					$defexists="";
					$deaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} AND language='{$baselang}' ORDER BY sortorder, answer";
					$dearesult = db_execute_assoc($deaquery);
					echo "\t\t\t<select name='$fieldname'>\n";
					while ($dearow = $dearesult->FetchRow())
					{
						echo "\t\t\t\t<option value='{$dearow['code']}'";
						if ($dearow['default_value'] == "Y") {echo " selected"; $defexists = "Y";}
						echo ">{$dearow['answer']}</option>\n";
					}
					if (!$defexists) {echo "\t\t\t\t<option selected value=''>"._("Please choose")."..</option>\n";}
					echo "\t\t\t</select>\n"
					."\t\t\t<br />"._("Comment").":<br />\n"
					."\t\t\t<textarea cols='40' rows='5' name='$fieldname"
					."comment'></textarea>\n";
					break;
					case "R": //RANKING TYPE QUESTION
					$thisqid=$deqrow['qid'];
					$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$thisqid ORDER BY sortorder, answer";
					$ansresult = db_execute_assoc($ansquery);
					$anscount = $ansresult->RecordCount();
					echo "\t\t\t<script type='text/javascript'>\n"
					."\t\t\t<!--\n"
					."\t\t\t\tfunction rankthis_$thisqid(\$code, \$value)\n"
					."\t\t\t\t\t{\n"
					."\t\t\t\t\t\$index=document.addsurvey.CHOICES_$thisqid.selectedIndex;\n"
					."\t\t\t\t\tdocument.addsurvey.CHOICES_$thisqid.selectedIndex=-1;\n"
					."\t\t\t\t\tfor (i=1; i<=$anscount; i++)\n"
					."\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\t\$b=i;\n"
					."\t\t\t\t\t\t\$b += '';\n"
					."\t\t\t\t\t\t\$inputname=\"RANK_$thisqid\"+\$b;\n"
					."\t\t\t\t\t\t\$hiddenname=\"d$fieldname\"+\$b;\n"
					."\t\t\t\t\t\t\$cutname=\"cut_$thisqid\"+i;\n"
					."\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='none';\n"
					."\t\t\t\t\t\tif (!document.getElementById(\$inputname).value)\n"
					."\t\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\t\tdocument.getElementById(\$inputname).value=\$value;\n"
					."\t\t\t\t\t\t\tdocument.getElementById(\$hiddenname).value=\$code;\n"
					."\t\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='';\n"
					."\t\t\t\t\t\t\tfor (var b=document.getElementById('CHOICES_$thisqid').options.length-1; b>=0; b--)\n"
					."\t\t\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\t\t\tif (document.getElementById('CHOICES_$thisqid').options[b].value == \$code)\n"
					."\t\t\t\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').options[b] = null;\n"
					."\t\t\t\t\t\t\t\t\t}\n"
					."\t\t\t\t\t\t\t\t}\n"
					."\t\t\t\t\t\t\ti=$anscount;\n"
					."\t\t\t\t\t\t\t}\n"
					."\t\t\t\t\t\t}\n"
					."\t\t\t\t\tif (document.getElementById('CHOICES_$thisqid').options.length == 0)\n"
					."\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').disabled=true;\n"
					."\t\t\t\t\t\t}\n"
					."\t\t\t\t\t}\n"
					."\t\t\t\tfunction deletethis_$thisqid(\$text, \$value, \$name, \$thisname)\n"
					."\t\t\t\t\t{\n"
					."\t\t\t\t\tvar qid='$thisqid';\n"
					."\t\t\t\t\tvar lngth=qid.length+4;\n"
					."\t\t\t\t\tvar cutindex=\$thisname.substring(lngth, \$thisname.length);\n"
					."\t\t\t\t\tcutindex=parseFloat(cutindex);\n"
					."\t\t\t\t\tdocument.getElementById(\$name).value='';\n"
					."\t\t\t\t\tdocument.getElementById(\$thisname).style.display='none';\n"
					."\t\t\t\t\tif (cutindex > 1)\n"
					."\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\t\$cut1name=\"cut_$thisqid\"+(cutindex-1);\n"
					."\t\t\t\t\t\t\$cut2name=\"d$fieldname\"+(cutindex);\n"
					."\t\t\t\t\t\tdocument.getElementById(\$cut1name).style.display='';\n"
					."\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n"
					."\t\t\t\t\t\t}\n"
					."\t\t\t\t\telse\n"
					."\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\t\$cut2name=\"d$fieldname\"+(cutindex);\n"
					."\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n"
					."\t\t\t\t\t\t}\n"
					."\t\t\t\t\tvar i=document.getElementById('CHOICES_$thisqid').options.length;\n"
					."\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').options[i] = new Option(\$text, \$value);\n"
					."\t\t\t\t\tif (document.getElementById('CHOICES_$thisqid').options.length > 0)\n"
					."\t\t\t\t\t\t{\n"
					."\t\t\t\t\t\tdocument.getElementById('CHOICES_$thisqid').disabled=false;\n"
					."\t\t\t\t\t\t}\n"
					."\t\t\t\t\t}\n"
					."\t\t\t//-->\n"
					."\t\t\t</script>\n";
					while ($ansrow = $ansresult->FetchRow())
					{
						$answers[] = array($ansrow['code'], $ansrow['answer']);
					}
					for ($i=1; $i<=$anscount; $i++)
					{
						if (isset($fname))
						{
							$myfname=$fname.$i;
						}
						if (isset($myfname) && $_SESSION[$myfname])
						{
							$existing++;
						}
					}
					for ($i=1; $i<=$anscount; $i++)
					{
						if (isset($fname))
						{
							$myfname = $fname.$i;
						}
						if (isset($myfname) && $_SESSION[$myfname])
						{
							foreach ($answers as $ans)
							{
								if ($ans[0] == $_SESSION[$myfname])
								{
									$thiscode=$ans[0];
									$thistext=$ans[1];
								}
							}
						}
						if (!isset($ranklist)) {$ranklist="";}
						$ranklist .= "\t\t\t\t\t\t&nbsp;<font color='#000080'>$i:&nbsp;<input type='text' style='width:150; color: #222222; font-size: 10; background-color: silver' name='RANK$i' id='RANK_$thisqid$i'";
						if (isset($myfname) && $_SESSION[$myfname])
						{
							$ranklist .= " value='";
							$ranklist .= $thistext;
							$ranklist .= "'";
						}
						$ranklist .= " onFocus=\"this.blur()\">\n";
						$ranklist .= "\t\t\t\t\t\t<input type='hidden' id='d$fieldname$i' name='d$fieldname$i' value='";
						$chosen[]=""; //create array
						if (isset($myfname) && $_SESSION[$myfname])
						{
							$ranklist .= $thiscode;
							$chosen[]=array($thiscode, $thistext);
						}
						$ranklist .= "'></font>\n";
						$ranklist .= "\t\t\t\t\t\t<img src='$imagefiles/cut.gif' alt='"._("Remove this item")."' title='"._("Remove this item")."' ";
						if (!isset($existing) || $i != $existing)
						{
							$ranklist .= "style='display:none'";
						}
						$mfn=$fieldname.$i;
						$ranklist .= " id='cut_$thisqid$i' onClick=\"deletethis_$thisqid(document.addsurvey.RANK_$thisqid$i.value, document.addsurvey.d$fieldname$i.value, document.addsurvey.RANK_$thisqid$i.id, this.id)\"><br />\n\n";
					}
					if (!isset($choicelist)) {$choicelist="";}
					$choicelist .= "\t\t\t\t\t\t<select size='$anscount' name='CHOICES' id='CHOICES_$thisqid' onClick=\"rankthis_$thisqid(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" style='background-color: #EEEFFF; font-family: verdana; font-size: 12; color: #000080; width: 150'>\n";
					foreach ($answers as $ans)
					{
						if (_PHPVERSION < "4.2.0")
						{
							if (!array_in_array($ans, $chosen))
							{
								$choicelist .= "\t\t\t\t\t\t\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
							}
						}
						else
						{
							if (!in_array($ans, $chosen))
							{
								$choicelist .= "\t\t\t\t\t\t\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
							}
						}
					}
					$choicelist .= "\t\t\t\t\t\t</select>\n";
	
					echo "\t\t\t<table align='left' border='0' cellspacing='5'>\n"
					."\t\t\t\t<tr>\n"
					."\t\t\t\t\t<td align='left' valign='top' width='200' style='border: solid 1 #111111' bgcolor='silver'>\n"
					."\t\t\t\t\t\t$setfont<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
					._("Your Choices").":</strong></font><br />\n"
					."&nbsp;&nbsp;&nbsp;&nbsp;".$choicelist
					."\t\t\t\t\t</td>\n"
					."\t\t\t\t\t<td align='left' bgcolor='silver' width='200' style='border: solid 1 #111111'>\n"
					."\t\t\t\t\t\t$setfont<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
					._("Your Ranking").":</strong><br />\n"
					.$ranklist
					."\t\t\t\t\t</font></td>\n"
					."\t\t\t\t</tr>\n"
					."\t\t\t</table>\n"
					."\t\t\t<input type='hidden' name='multi' value='$anscount' />\n"
					."\t\t\t<input type='hidden' name='lastfield' value='";
					if (isset($multifields)) {echo $multifields;}
					echo "' />\n";
					$choicelist="";
					$ranklist="";
					unset($answers);
					break;
					case "M": //MULTIPLE OPTIONS checkbox (Quite tricky really!)
					$qidattributes=getQuestionAttributes($deqrow['qid']);
					if ($displaycols=arraySearchByKey("display_columns", $qidattributes, "attribute", 1))
					{
						$dcols=$displaycols['value'];
					}
					else
					{
						$dcols=0;
					}
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} AND language='{$baselang}' ORDER BY sortorder, answer";
					$mearesult = db_execute_assoc($meaquery);
					$meacount = $mearesult->RecordCount();
					if ($deqrow['other'] == "Y") {$meacount++;}
					if ($dcols > 0 && $meacount >= $dcols)
					{
						$width=sprintf("%0d", 100/$dcols);
						$maxrows=ceil(100*($meacount/$dcols)/100); //Always rounds up to nearest whole number
						$divider=" </td>\n <td valign='top' width='$width%' nowrap>";
						$upto=0;
						echo "<table class='question'><tr>\n <td valign='top' width='$width%' nowrap>";
						while ($mearow = $mearesult->FetchRow())
						{
							if ($upto == $maxrows)
							{
								echo $divider;
								$upto=0;
							}
							echo "\t\t\t$setfont<input type='checkbox' name='$fieldname{$mearow['code']}' id='answer$fieldname{$mearow['code']}' value='Y'";
							if ($mearow['default_value'] == "Y") {echo " checked";}
							echo " /><label for='$fieldname{$mearow['code']}'>{$mearow['answer']}</label></font><br />\n";
							$upto++;
						}
						if ($deqrow['other'] == "Y")
						{
							echo "\t\t\t"._("Other")." <input type='text' name='$fieldname";
							echo "other' />\n";
						}
						echo "</td></tr></table>\n";
						//Let's break the presentation into columns.
					}
					else
					{
						while ($mearow = $mearesult->FetchRow())
						{
							echo "\t\t\t$setfont<input type='checkbox' name='$fieldname{$mearow['code']}' id='answer$fieldname{$mearow['code']}' value='Y'";
							if ($mearow['default_value'] == "Y") {echo " checked";}
							echo " /><label for='$fieldname{$mearow['code']}'>{$mearow['answer']}</label></font><br />\n";
						}
						if ($deqrow['other'] == "Y")
						{
							echo "\t\t\t"._("Other")." <input type='text' name='$fieldname";
							echo "other' />\n";
						}
					}
					break;
					case "J": //FILE CSV MORE
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult = db_execute_assoc($meaquery);
					while ($mearow = $mearesult->FetchRow())
					{
						echo "\t\t\t$setfont<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y'";
						if ($mearow['default_value'] == "Y") {echo " checked";}
						echo " />{$mearow['answer']}<br />\n";
					}
					break;
					case "I": //FILE CSV ONE - LIST drop-down/radio-button list
					$deaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$dearesult = db_execute_assoc($deaquery);
					echo "\t\t\t<select name='$fieldname'>\n";
					while ($dearow = $dearesult->FetchRow())
					{
						echo "\t\t\t\t<option value='{$dearow['code']}'";
						if ($dearow['default_value'] == "Y") {echo " selected"; $defexists = "Y";}
						echo ">{$dearow['answer']}</option>\n";
					}
					if (!$defexists) {echo "\t\t\t\t<option selected value=''>"._("Please choose")."..</option>\n";}
					break;
					case "P": //MULTIPLE OPTIONS WITH COMMENTS checkbox + text
					echo "<table border='0'>\n";
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult = db_execute_assoc($meaquery);
					while ($mearow = $mearesult->FetchRow())
					{
						echo "\t<tr>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t$setfont<input type='checkbox' name='$fieldname{$mearow['code']}' value='Y'";
						if ($mearow['default_value'] == "Y") {echo " checked";}
						echo " />{$mearow['answer']}\n";
						echo "\t\t</font></td>\n";
						//This is the commments field:
						echo "\t\t<td>\n";
						echo "\t\t\t<input type='text' name='$fieldname{$mearow['code']}comment' size='50' />\n";
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
					}
					if ($deqrow['other'] == "Y")
					{
						echo "\t<tr>\n";
						echo "\t\t<td style='padding-left: 22px'>$setfont"._("Other").":</font></td>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t<input type='text' name='$fieldname"."other' size='50'/>\n";
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
					}
					echo "</table>\n";
					break;
					case "N": //NUMERICAL TEXT
					echo keycontroljs();
					echo "\t\t\t<input type='text' name='$fieldname' onKeyPress=\"return goodchars(event,'0123456789.,')\" />";
					break;
					case "S": //SHORT FREE TEXT
					echo "\t\t\t<input type='text' name='$fieldname' />\n";
					break;
					case "T": //LONG FREE TEXT
					echo "\t\t\t<textarea cols='40' rows='5' name='$fieldname'></textarea>\n";
					break;
					case "U": //LONG FREE TEXT
					echo "\t\t\t<textarea cols='50' rows='70' name='$fieldname'></textarea>\n";
					break;
					case "Y": //YES/NO radio-buttons
					echo "\t\t\t<select name='$fieldname'>\n";
					echo "\t\t\t\t<option selected value=''>"._("Please choose")."..</option>\n";
					echo "\t\t\t\t<option value='Y'>"._("Yes")."</option>\n";
					echo "\t\t\t\t<option value='N'>"._("No")."</option>\n";
					echo "\t\t\t</select>\n";
					break;
					case "A": //ARRAY (5 POINT CHOICE) radio-buttons
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult = db_execute_assoc($meaquery);
					echo "<table>\n";
					while ($mearow = $mearesult->FetchRow())
					{
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$mearow['answer']}</font></td>\n";
						echo "\t\t<td>$setfont\n";
						echo "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
						echo "\t\t\t\t<option value=''>"._("Please choose")."..</option>\n";
						for ($i=1; $i<=5; $i++)
						{
							echo "\t\t\t\t<option value='$i'>$i</option>\n";
						}
						echo "\t\t\t</select>\n";
						echo "\t\t</font></td>\n";
						echo "\t</tr>\n";
					}
					echo "</table>\n";
					break;
					case "B": //ARRAY (10 POINT CHOICE) radio-buttons
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult = db_execute_assoc($meaquery);
					echo "<table>\n";
					while ($mearow = $mearesult->FetchRow())
					{
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$mearow['answer']}</font></td>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
						echo "\t\t\t\t<option value=''>"._("Please choose")."..</option>\n";
						for ($i=1; $i<=10; $i++)
						{
							echo "\t\t\t\t<option value='$i'>$i</option>\n";
						}
						echo "</select>\n";
						echo "\t\t</td>\n";
						echo "\t</tr>\n";
					}
					echo "</table>\n";
					break;
					case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult=db_execute_assoc($meaquery);
					echo "<table>\n";
					while ($mearow = $mearesult->FetchRow())
					{
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$mearow['answer']}</font></td>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
						echo "\t\t\t\t<option value=''>"._("Please choose")."..</option>\n";
						echo "\t\t\t\t<option value='Y'>"._("Yes")."</option>\n";
						echo "\t\t\t\t<option value='U'>"._("Uncertain")."</option>\n";
						echo "\t\t\t\t<option value='N'>"._("No")."</option>\n";
						echo "\t\t\t</select>\n";
						echo "\t\t</td>\n";
						echo "</tr>\n";
					}
					echo "</table>\n";
					break;
					case "E": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult=db_execute_assoc($meaquery) or die ("Couldn't get answers, Type \"E\"<br />$meaquery<br />".htmlspecialchars($connect->ErrorMsg()));
					echo "<table>\n";
					while ($mearow = $mearesult->FetchRow())
					{
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$mearow['answer']}</font></td>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
						echo "\t\t\t\t<option value=''>"._("Please choose")."..</option>\n";
						echo "\t\t\t\t<option value='I'>"._("Increase")."</option>\n";
						echo "\t\t\t\t<option value='S'>"._("Same")."</option>\n";
						echo "\t\t\t\t<option value='D'>"._("Decrease")."</option>\n";
						echo "\t\t\t</select>\n";
						echo "\t\t</td>\n";
						echo "</tr>\n";
					}
					echo "</table>\n";
					break;
					case "F": //ARRAY (Flexible Labels)
					case "H":
					$meaquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult=db_execute_assoc($meaquery) or die ("Couldn't get answers, Type \"E\"<br />$meaquery<br />".htmlspecialchars($connect->ErrorMsg()));
					echo "<table>\n";
					while ($mearow = $mearesult->FetchRow())
					{
						echo "\t<tr>\n";
						echo "\t\t<td align='right'>$setfont{$mearow['answer']}</font></td>\n";
						echo "\t\t<td>\n";
						echo "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
						echo "\t\t\t\t<option value=''>"._("Please choose")."..</option>\n";
						$fquery = "SELECT * FROM {$dbprefix}labels WHERE lid={$deqrow['lid']} ORDER BY sortorder, code";
						$fresult = db_execute_assoc($fquery);
						while ($frow = $fresult->FetchRow())
						{
							echo "\t\t\t\t<option value='{$frow['code']}'>".$frow['title']."</option>\n";
						}
						echo "\t\t\t</select>\n";
						echo "\t\t</td>\n";
						echo "</tr>\n";
					}
					echo "</table>\n";
					break;
				}
				//echo " [$surveyid"."X"."$gid"."X"."$qid]";
				echo "\t\t</td>\n";
				echo "\t</tr>\n";
				echo "\t<tr><td colspan='3' height='2' bgcolor='silver'></td></tr>\n";
			}
		}
	
		if ($thissurvey['active'] == "Y")
		{
			if ($thissurvey['allowsave'] == "Y")
			{
				//Show Save Option
				echo "<script type='text/javascript'>
					  <!--
						function saveshow(value)
							{
							if (document.getElementById(value).checked == true)
								{
								document.getElementById(\"saveoptions\").style.display=\"\";
								}
							else
								{
								document.getElementById(\"saveoptions\").style.display=\"none\";
								}
							}
					  //-->
					  </script>\n";
				echo "\t<tr>\n";
				echo "\t\t<td colspan='3' align='center' bgcolor='#CCCCCC'>$setfont\n";
				echo "\t\t\t<input type='checkbox' name='save' id='save' onChange='saveshow(this.id)' onLoad='saveshow(this.id)'><label for='save'>"._("Save as a partially completed survey")."</label>\n";
				echo "<div name='saveoptions' id='saveoptions' style='display: none'>\n";
				echo "<table align='center' class='outlinetable' cellspacing='0'>
					  <tr><td align='right'>"._("Identifier:")."</td>
					  <td><input type='text' name='save_identifier'></td></tr>
					  <tr><td align='right'>"._("Password:")."</td>
					  <td><input type='password' name='save_password'></td></tr>
					  <tr><td align='right'>"._("Confirm Password:")."</td>
					  <td><input type='password' name='save_confirmpassword'></td></tr>
					  <tr><td align='right'>"._("Email:")."</td>
					  <td><input type='text' name='save_email'></td></tr>
					  </table>\n";
				echo "\t\t</font></td>\n";
				echo "\t</tr>\n";
			}
			echo "\t<tr>\n";
			echo "\t\t<td colspan='3' align='center' bgcolor='#CCCCCC'>$setfont\n";
			echo "\t\t\t<input type='submit' value='"._("submit")."' />\n";
			echo "\t\t</font></td>\n";
			echo "\t</tr>\n";
		}
		elseif ($thissurvey['active'] == "N")
		{
			echo "\t<tr>\n";
			echo "\t\t<td colspan='3' align='center' bgcolor='#CCCCCC'>$setfont\n";
			echo "\t\t\t<font color='red'><strong>"._("This survey is not yet active. Your response cannot be saved")."\n";
			echo "\t\t</strong></font></font></td>\n";
			echo "\t</tr>\n";
		}
		else
		{
			echo "</form>\n";
			echo "\t<tr>\n";
			echo "\t\t<td colspan='3' align='center' bgcolor='#CCCCCC'>$setfont\n";
			echo "\t\t\t<font color='red'><strong>"._("Error")."</strong></font><br />\n";
			echo "\t\t\t"._("The survey you selected does not exist")."</font><br /><br />\n";
			echo "\t\t\t<input type='submit' value='"._("Main Admin Screen")."' onClick=\"window.open('$scriptname', '_top')\">\n";
			echo "\t\t</td>\n";
			echo "\t</tr>\n";
			echo "</table>";
			echo getAdminFooter("$langdir/instructions.html#Editing and Deleting Responses", "Using PHPSurvey to Edit Responses");
			exit;
		}
		echo "\t<tr>\n";
		echo "\t<td>\n";
		echo "\t<input type='hidden' name='action' value='insert' />\n";
		echo "\t<input type='hidden' name='surveytable' value='$surveytable' />\n";
		echo "\t<input type='hidden' name='sid' value='$surveyid' />\n";
		echo "\t</td>\n";
		echo "\t</tr>\n";
		echo "</table>\n";
		echo "\t</form>\n";
	}
	echo "&nbsp;";
	echo getAdminFooter("$langdir/instructions.html#Editing and Deleting Responses", "Using PHPSurvey to Edit Responses");

	}
else
	{	
	$action = "browse_response";
	include("access_denied.php");
	include("admin.php");
	//echo("Wird gel?scht: " . $sumrows5['DELETE_SURVEY']);	
	}


function array_in_array($needle, $haystack)
{
	foreach ($haystack as $value)
	{
		if ($needle == $value)
		return true;
	}
	return false;
}
?>
