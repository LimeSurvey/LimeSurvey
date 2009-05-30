<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
* 
* $Id$
*/

/* 
 * We need this later:
 *  1 - Array (Flexible Labels) Dual Scale 
    5 - 5 Point Choice 
    A - Array (5 Point Choice) 
    B - Array (10 Point Choice) 
    C - Array (Yes/No/Uncertain) 
    D - Date 
    E - Array (Increase, Same, Decrease) 
    F - Array (Flexible Labels) 
    G - Gender 
    H - Array (Flexible Labels) by Column 
    I - Language Switch 
    K - Multiple Numerical Input 
    L - List (Radio) 
    M - Multiple Options 
    N - Numerical Input 
    O - List With Comment 
    P - Multiple Options With Comments 
    Q - Multiple Short Text 
    R - Ranking 
    S - Short Free Text 
    T - Long Free Text 
    U - Huge Free Text 
    W - List (Flexible Labels) (Dropdown) 
    X - Boilerplate Question 
    Y - Yes/No 
    Z - List (Flexible Labels) (Radio) 
    ! - List (Dropdown)
    : - Array (Flexible Labels) multiple drop down
    ; - Array (Flexible Labels) multiple texts


 */

include_once("login_check.php");
$language = $_SESSION['adminlang'];
//RL: set language for questions and labels to current admin language for browsing responses

$action = returnglobal('action');
$surveyid = returnglobal('sid');
$id = returnglobal('id');
$saver['scid']=returnglobal('save_scid');
$surveytable = db_table_name("survey_".$surveyid);
$dataentryoutput ='';

include_once("login_check.php");

$dateformatdetails=getDateFormatData($_SESSION['dateformat']);
$language = GetBaseLanguageFromSurveyID($surveyid);

$actsurquery = "SELECT browse_response FROM ".db_table_name("surveys_rights")." WHERE sid=$surveyid AND uid = ".$_SESSION['loginID']; //Getting rights for this survey
$actsurresult = db_execute_assoc($actsurquery) or safe_die($connect->ErrorMsg());
$actsurrows = $actsurresult->FetchRow();

if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $actsurrows['browse_response'])
{

	$surveyoptions = browsemenubar($clang->gT("Browse Responses"));
	if (!$surveyid && !$subaction)
	{
		//$dataentryoutput .= "</table>\n";
		$dataentryoutput .= "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr><td colspan='2' height='4' class='settingcaption'><strong>"
		.$clang->gT("Data Entry")."</strong></td></tr>\n"
		."\t<tr><td align='center'>\n"
		."<strong><font color='red'>".$clang->gT("Error")."</font></strong><br />\n"
		.$clang->gT("You have not selected a survey for data-entry.")."<br /><br />\n"
		."<input type='submit' value='"
		.$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br />\n"
		."</font></td></tr></table>\n";
		return;
	}

	if ($subaction == "edit" || $subaction == "" || $subaction == "editsaved" || $subaction == "insert")
	{
		$language = GetBaseLanguageFromSurveyID($surveyid);
	}

	if ($subaction == "insert")
	{
		$thissurvey=getSurveyInfo($surveyid); 
		$errormsg="";
		$dataentryoutput .= "<table width='450' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr><td colspan='2' height='4'><strong>"
		.$clang->gT("Data Entry")."</strong></td></tr>\n"
		."\t<tr><td align='center'>\n";

		$lastanswfortoken=''; // check if a previous answer has been submitted or saved
		$rlanguage='';
		if (isset($_POST['token']) && $_POST['token'])
		{ 
			$tokencompleted = "";
			$tokentable = db_table_name("tokens_".$surveyid);
			$tcquery = "SELECT completed from $tokentable WHERE token='".$_POST['token']."'";
			$tcresult = db_execute_assoc($tcquery);
			$tccount = $tcresult->RecordCount();
			while ($tcrow = $tcresult->FetchRow())
			{
				$tokencompleted = $tcrow['completed'];
			}

			if ($tccount < 1)
			{ // token doesn't exist in token table
				$lastanswfortoken='UnknownToken';
			}
			elseif ($thissurvey['private'] == "Y")
			{ // token exist but survey is anonymous, check completed state
				if ($tokencompleted != "" && $tokencompleted != "N")
				{ // token is not completed
					$lastanswfortoken='PrivacyProtected';
				}
			}
			else
			{ // token is valid, survey not anonymous, try to get last recorded response id
				$aquery = "SELECT id,startlanguage FROM $surveytable WHERE token='".$_POST['token']."'";
				$aresult = db_execute_assoc($aquery);
				while ($arow = $aresult->FetchRow())
				{
					$lastanswfortoken=$arow['id'];
					$rlanguage=$arow['startlanguage'];
				}
			}
		}

		if (tokenTableExists($thissurvey['sid']) && (!isset($_POST['token']) || !$_POST['token']))
		{// First Check if the survey uses tokens and if a token has been provided
			$errormsg="<strong><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("This is a closed-access survey, so you must supply a valid token.  Please contact the administrator for assistance.")."</strong>\n";
		}
		elseif (tokenTableExists($thissurvey['sid']) && $lastanswfortoken == 'UnknownToken')
		{
			$errormsg="<strong><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("The token you have provided is not valid or has already been used.")."</strong>\n";
		}
		elseif (tokenTableExists($thissurvey['sid']) && $lastanswfortoken != '')
		{
			$errormsg="<strong><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("There is already a recorded answer for this token")."</strong>\n";
			if ($lastanswfortoken != 'PrivacyProtected')
			{
				$errormsg .= "<br /><br />".$clang->gT("Follow the following link to update it").":\n"
				. "<a href='$scriptname?action=dataentry&amp;subaction=edit&amp;id=$lastanswfortoken&amp;sid=$surveyid&amp;language=$rlanguage&amp;surveytable=$surveytable'"
				. "onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Edit this entry", "js")."')\">[id:$lastanswfortoken]</a>";
			}
			else
			{
				$errormsg .= "<br /><br />".$clang->gT("This surveys uses anonymous answers, so you can't update your response.")."\n";
			}
		}
		else
		{
			if (isset($_POST['save']) && $_POST['save'] == "on")
			{
				$saver['identifier']=returnglobal('save_identifier');
				$saver['language']=returnglobal('save_language');
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
				if (!$saver['identifier']) {$errormsg .= $clang->gT("Error").": ".$clang->gT("You must supply a name for this saved session.");}
				if (!$saver['password']) {$errormsg .= $clang->gT("Error").": ".$clang->gT("You must supply a password for this saved session.");}
				if ($saver['password'] != $saver['passwordconfirm']) {$errormsg .= $clang->gT("Error").": ".$clang->gT("Your passwords do not match.");}
				if ($errormsg)
				{
					$dataentryoutput .= $errormsg;
					$dataentryoutput .= $clang->gT("Try again").":<br />
    				 <form method='post'>
					  <table class='outlinetable' cellspacing='0' align='center'>
					  <tr>
					   <td align='right'>".$clang->gT("Identifier:")."</td>
					   <td><input type='text' name='save_identifier' value='".$_POST['save_identifier']."' /></td></tr>
					  <tr><td align='right'>".$clang->gT("Password:")."</td>
					   <td><input type='password' name='save_password' value='".$_POST['save_password']."' /></td></tr>
					  <tr><td align='right'>".$clang->gT("Confirm Password:")."</td>
					   <td><input type='password' name='save_confirmpassword' value='".$_POST['save_confirmpassword']."' /></td></tr>
					  <tr><td align='right'>".$clang->gT("Email:")."</td>
					   <td><input type='text' name='save_email' value='".$_POST['save_email']."' />
					  <tr><td align='right'>".$clang->gT("Start Language:")."</td>
					   <td><input type='text' name='save_language' value='".$_POST['save_language']."' />\n";
					foreach ($_POST as $key=>$val)
					{
						if (substr($key, 0, 4) != "save" && $key != "action" && $key != "surveytable" && $key !="sid" && $key != "datestamp" && $key !="ipaddr")
						{
							$dataentryoutput .= "<input type='hidden' name='$key' value='$val' />\n";
						}
					}
					$dataentryoutput .= "</td></tr><tr><td></td><td><input type='submit' value='".$clang->gT("Submit")."' />
					 <input type='hidden' name='sid' value='$surveyid' />
					 <input type='hidden' name='surveytable' value='".$_POST['surveytable']."' />
					 <input type='hidden' name='subaction' value='".$_POST['subaction']."' />
					 <input type='hidden' name='language' value='".$_POST['language']."' />
					 <input type='hidden' name='save' value='on' /></td>";
					if (isset($_POST['datestamp']))
					{
						$dataentryoutput .= "<input type='hidden' name='datestamp' value='".$_POST['datestamp']."' />\n";
					}
					if (isset($_POST['ipaddr']))
					{
						$dataentryoutput .= "<input type='hidden' name='ipaddr' value='".$_POST['ipaddr']."' />\n";
					}
					$dataentryoutput .= "</table></form>\n";
				} elseif (returnglobal('redo')=="yes")
				{
					//Delete all the existing entries TODO WTF IS REDO?
					//$delete="DELETE FROM ".db_table_name("saved")." WHERE scid=".$saver['scid'];
					//$result=$connect->Execute($delete) or safe_die("Couldn't delete old record<br />$delete<br />".htmlspecialchars($connect->ErrorMsg()));
					//$delete="DELETE FROM ".db_table_name("saved_control")." WHERE scid=".$surveytable['scid'];
					//$result=$connect->Execute($delete) or safe_die("Couldn't delete old record<br />$delete<br />".htmlspecialchars($connect->ErrorMsg()));
				}
			}
			//BUILD THE SQL TO INSERT RESPONSES
			$baselang = GetBaseLanguageFromSurveyID($surveyid);
			$iquery = "SELECT * FROM ".db_table_name("questions").", ".db_table_name("groups")." WHERE
			".db_table_name("questions").".gid=".db_table_name("groups").".gid AND 
			".db_table_name("questions").".language = '{$baselang}' AND ".db_table_name("groups").".language = '{$baselang}' AND
			".db_table_name("questions").".sid=$surveyid ORDER BY ".db_table_name("groups").".group_order, title";
			$iresult = db_execute_assoc($iquery);
			$col_name="";
			$insertqr="";
			while ($irow = $iresult->FetchRow())
			{
				if ($irow['type'] != "M" && $irow['type'] != "A" && $irow['type'] != "B" && $irow['type'] != "C" && 
						$irow['type'] != "E" && $irow['type'] != "F" && $irow['type'] != "H" && $irow['type'] != "P" && 
						$irow['type'] != "O" && $irow['type'] != "R" && $irow['type'] != "Q" && $irow['type'] != "J" &&
						$irow['type'] != "K" && $irow['type'] != ":" && $irow['type'] != "1" && $irow['type'] != ";")
				{
					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
					if (isset($_POST[$fieldname]))
					{
						if ($irow['type'] == 'D' && $_POST[$fieldname] == "")
						{ // can't add '' in Date column
							// Do nothing
						}
						elseif ($irow['type'] == 'N' && $_POST[$fieldname] == "")
						{ // can't add '' to numerical column
							// Do nothing
						}
						else
						{
							$col_name .= db_quote_id($fieldname).", \n";
							$insertqr .= "'" . auto_escape($_POST[$fieldname]) . "', \n";
						}
					}
					// if "!" "L" "W" "Z", and Other ==> add other fieldname
					if ($irow['type'] == "!" || $irow['type'] == "L" ||
						$irow['type'] == "W" || $irow['type'] == "Z")
					{
						$fieldname2=$fieldname."other";
						if (isset($_POST[$fieldname2]) && isset($_POST[$fieldname]) && $_POST[$fieldname] == '-oth-' && $_POST[$fieldname2]!= "")
						{
							$col_name .= db_quote_id($fieldname2).", \n";
							$insertqr .= "'" . auto_escape($_POST[$fieldname2]) . "', \n";
						}
					}
				}
				elseif ($irow['type'] == "O")
				{
					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
					$fieldname2 = $fieldname . "comment";
					$col_name .= db_quote_id($fieldname).", \n".db_quote_id($fieldname2).", \n";
					$insertqr .= "'" . auto_escape($_POST[$fieldname]) . "', \n'" . auto_escape($_POST[$fieldname2]) . "', \n";
				}
				elseif ($irow['type'] == "1")
				{
					$i2query = "SELECT ".db_table_name("answers").".*, ".db_table_name("questions").".other FROM ".db_table_name("answers").", ".db_table_name("questions")." WHERE
						".db_table_name("answers").".qid=".db_table_name("questions").".qid AND ".db_table_name("questions").".qid={$irow['qid']} AND 
						".db_table_name("questions").".language = '{$language}' AND ".db_table_name("answers").".language = '{$language}' AND
						".db_table_name("questions").".sid=$surveyid ORDER BY ".db_table_name("answers").".sortorder, ".db_table_name("answers").".answer";

					$i2result = $connect->Execute($i2query);
					$i2count = $i2result->RecordCount();
					while ($i2answ = $i2result->FetchRow())
					{
						// first scale
						$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2answ['code']}#0";
						$col_name .= db_quote_id($fieldname).", \n";
						$insertqr .= "'" . auto_escape($_POST["$fieldname"]) . "', \n";
						// second scale
						$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2answ['code']}#1";
						$col_name .= db_quote_id($fieldname).", \n";
						$insertqr .= "'" . auto_escape($_POST["$fieldname"]) . "', \n";
					}

				}
				elseif ($irow['type'] == "R")
				{
					$i2query = "SELECT ".db_table_name("answers").".*, ".db_table_name("questions").".other FROM ".db_table_name("answers").", ".db_table_name("questions")." WHERE
						".db_table_name("answers").".qid=".db_table_name("questions").".qid AND ".db_table_name("questions").".qid={$irow['qid']} AND 
						".db_table_name("questions").".language = '{$language}' AND ".db_table_name("answers").".language = '{$language}' AND
						".db_table_name("questions").".sid=$surveyid ORDER BY ".db_table_name("answers").".sortorder, ".db_table_name("answers").".answer";
					$i2result = $connect->Execute($i2query);
					$i2count = $i2result->RecordCount();
					for ($i=1; $i<=$i2count; $i++)
					{
						$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}$i";
						$col_name .= db_quote_id($fieldname).", \n";
						$insertqr .= "'" . auto_escape($_POST["d$fieldname"]) . "', \n";
					}
				}
				elseif ($irow['type'] == ":" || $irow['type'] == ";")
				{
					$i2query = "SELECT ".db_table_name("answers").".*, ".db_table_name("questions").".other FROM ".db_table_name("answers").", ".db_table_name("questions")."
						WHERE ".db_table_name("answers").".qid=".db_table_name("questions").".qid AND
						".db_table_name("answers").".language='{$language}' AND ".db_table_name("questions").".language='{$language}' AND
						".db_table_name("questions").".qid={$irow['qid']} AND ".db_table_name("questions").".sid=$surveyid
						ORDER BY ".db_table_name("answers").".sortorder, ".db_table_name("answers").".answer";
					$i2result = db_execute_assoc($i2query);
					$ab2query = "SELECT ".db_table_name('labels').".*
						FROM ".db_table_name('questions').", ".db_table_name('labels')."
						WHERE sid=$surveyid 
						AND ".db_table_name('labels').".lid=".db_table_name('questions').".lid
						AND ".db_table_name('questions').".language='".$language."'
						AND ".db_table_name('labels').".language='".$language."'
						AND ".db_table_name('questions').".qid=".$irow['qid']."
						ORDER BY ".db_table_name('labels').".sortorder, ".db_table_name('labels').".title";
					$ab2result=db_execute_assoc($ab2query) or die("Couldn't get list of labels in createFieldMap function (case :)<br />$ab2query<br />".htmlspecialchars($connection->ErrorMsg()));
					while($ab2row=$ab2result->FetchRow())
					{
						$lset[]=$ab2row;
					}
					while ($i2row = $i2result->FetchRow())
					{
						foreach($lset as $ls)
						{
							$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2row['code']}_{$ls['code']}";
							$col_name .= db_quote_id($fieldname).", \n";
							$insertqr .= "'" . auto_escape($_POST[$fieldname]) . "', \n";
						}
					}
					unset($lset);
				}
				else
				{
					$i2query = "SELECT ".db_table_name("answers").".*, ".db_table_name("questions").".other FROM ".db_table_name("answers").", ".db_table_name("questions")."
						WHERE ".db_table_name("answers").".qid=".db_table_name("questions").".qid AND 
						".db_table_name("questions").".language = '{$language}' AND ".db_table_name("answers").".language = '{$language}' AND
						".db_table_name("questions").".qid={$irow['qid']} 
					AND ".db_table_name("questions").".sid=$surveyid ORDER BY ".db_table_name("answers").".sortorder, ".db_table_name("answers").".answer";
					$i2result = db_execute_assoc($i2query);
					while ($i2row = $i2result->FetchRow())
					{
						$otherexists = "";
						if ($i2row['other'] == "Y" and ($irow['type']=="!" or $irow['type']=="L" or $irow['type']=="M" or $irow['type']=="P" or $irow['type'] == "W" or $irow['type'] == "Z")) {$otherexists = "Y";}
						$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2row['code']}";
						if (isset($_POST[$fieldname]))
						{
							if ($irow['type'] == 'K' && $_POST[$fieldname] == "")
							{ // can't add '' in a numerical column
								// Do nothing
							} 
							else
							{	
								$col_name .= db_quote_id($fieldname).", \n";
								$insertqr .= "'" . auto_escape($_POST[$fieldname]) . "', \n";
							}

							if ($irow['type'] == "P")
							{
								$fieldname2 = $fieldname."comment";
								$col_name .= db_quote_id($fieldname2).", \n";
								$insertqr .= "'" . auto_escape($_POST[$fieldname2]) . "', \n";
							}
						}
					}
					if (isset($otherexists) && $otherexists == "Y")
					{
						$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}other";
						$col_name .= db_quote_id($fieldname).", \n";
						$insertqr .= "'" . auto_escape($_POST[$fieldname]) . "', \n";

						if ($irow['type']=="P")
						{
							$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}othercomment";
							$col_name .= db_quote_id($fieldname).", \n";
							$insertqr .= "'" . auto_escape($_POST[$fieldname]) . "', \n";
						}
					}
				}
			}

			$stripCommaColName = false;
			if ($col_name == "")
			{ // if cold_name is empty, set a flag so that we delete the beginning ","
				$stripCommaColName = true;
			}
			$stripCommaInsertqr = false;
			if ($insertqr =="")
			{ // if insertqr is empty, set a flag so that we delete the beginning ","
				$stripCommaInsertqr = true;
			}
			
			$col_name = substr($col_name, 0, -3); //Strip off the last comma-space
			$insertqr = substr($insertqr, 0, -3); //Strip off the last comma-space

			//NOW SHOW SCREEN
			if (tokenTableExists($thissurvey['sid']) && 
			    isset($_POST['token']) && $_POST['token'] &&
			    $thissurvey['private'] == 'N') //handle tokens if survey needs them
			{
				$col_name .= ", token\n";
				$insertqr .= ", '{$_POST['token']}'";
			}
			if (isset($_POST['datestamp']) && $_POST['datestamp']) //handle datestamp if needed
			{
				$col_name .= ", datestamp\n";
				$insertqr .= ", '{$_POST['datestamp']}'";
				$col_name .= ", startdate\n";
				$insertqr .= ", '{$_POST['datestamp']}'";
			}
			if (isset($_POST['ipaddr']) && $_POST['ipaddr']) //handle datestamp if needed
			{
				$col_name .= ", ipaddr\n";
				$insertqr .= ", '{$_POST['ipaddr']}'";
			}
			if (isset($_POST['language']) && $_POST['language']) // handle language
			{
				$col_name .= ", startlanguage\n";
				$insertqr .= ", '{$_POST['language']}'";
			}
			if (isset($_POST['closerecord'])) // handle Submidate if required
			{
				if ($thissurvey['private'] =="Y" && $thissurvey['datestamp'] =="N")
				{
					$col_name .= ", submitdate\n";
					$insertqr .= ", '".date("Y-m-d H:i:s",mktime(0,0,0,1,1,1980))."'";
				}
				elseif (isset($_POST['closedate']) && $_POST['closedate'] != '')
				{
					$col_name .= ", submitdate\n";
					$insertqr .= ", '{$_POST['closedate']}'";
				}
			}

			if ($stripCommaColName === true)
			{
				$col_name=substr($col_name, 1);
			}
			if ($stripCommaInsertqr === true)
			{
				$insertqr=substr($insertqr, 1);
			}

			//		$dataentryoutput .= "\t\t\t<strong>Inserting data</strong><br />\n"
			//			."SID: $surveyid, ($surveytable)<br /><br />\n";
			$SQL = "INSERT INTO $surveytable
					($col_name)
					VALUES 
					($insertqr)";
			//$dataentryoutput .= $SQL; //Debugging line
		
			$iinsert = $connect->Execute($SQL) or safe_die ("Could not insert your data:<br />$SQL<br />\n" .$connect->ErrorMsg());
			/*if (returnglobal('redo')=="yes")
			{
			//This submission of data came from a saved session. Must delete the
			//saved session now that it has been recorded in the responses table
			$dquery = "DELETE FROM ".db_table_name("saved_control")." WHERE scid=".$saver['scid'];
			if ($dresult=$connect->Execute($dquery))
			{
			$dquery = "DELETE FROM ".db_table_name("saved")." WHERE scid=".$saver['scid'];
			$dresult=$connect->Execute($dquery) or safe_die("Couldn't delete saved data<br />$dquery<br />".htmlspecialchars($connect->ErrorMsg()));
			}
			else
			{
			$dataentryoutput .= "Couldn't delete saved data<br />$dquery<br />".htmlspecialchars($connect->ErrorMsg());
			}
			}*/
	
			if (isset($_POST['closerecord']) && isset($_POST['token']) && $_POST['token'] != '') // submittoken
			{
				$today = date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust);      
				$utquery = "UPDATE {$dbprefix}tokens_$surveyid\n";
				if (bIsTokenCompletedDatestamped($thissurvey))
				{
					$utquery .= "SET completed='$today'\n";
				}
				else
				{
					$utquery .= "SET completed='Y'\n";
				}
				$utquery .= "WHERE token='{$_POST['token']}'";
				$utresult = $connect->Execute($utquery) or safe_die ("Couldn't update tokens table!<br />\n$utquery<br />\n".$connect->ErrorMsg());
			}
			if (isset($_POST['save']) && $_POST['save'] == "on")
			{
				$srid = $connect->Insert_ID();
				//CREATE ENTRY INTO "saved_control"
				$scdata = array("sid"=>$surveyid,
				"srid"=>$srid,
				"identifier"=>$saver['identifier'],
				"access_code"=>$password,
				"email"=>$saver['email'],
				"ip"=>$_SERVER['REMOTE_ADDR'],
				"refurl"=>getenv("HTTP_REFERER"),
				'saved_thisstep' => 0,
				"status"=>"S",
				"saved_date"=>date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust));

				if ($connect->AutoExecute("{$dbprefix}saved_control", $scdata,'INSERT'))
				{
					$scid = $connect->Insert_ID("{$dbprefix}saved_control","scid");
					
					$dataentryoutput .= "<font class='successtitle'>".$clang->gT("Your survey responses have been saved successfully.  You will be sent a confirmation e-mail. Please make sure to save your password, since we will not be able to retrieve it for you.")."</font><br />\n";
                    
                    $tkquery = "SELECT * FROM ".db_table_name("tokens_$surveyid");
                    if ($tkresult = $connect->Execute($tkquery)) //If the query fails, assume no tokens table exists
                    {
                    $tokendata = array (
                    "firstname"=> $saver['identifier'],	
                    "lastname"=> $saver['identifier'], 	
    				        "email"=>$saver['email'],
                    "token"=>randomkey(15),
                    "language"=>$saver['language'],
                    "sent"=>date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust), 	
                    "completed"=>"N");
                    $connect->AutoExecute(db_table_name("tokens_".$surveyid), $tokendata,'INSERT');
					$dataentryoutput .= "<font class='successtitle'>".$clang->gT("A token entry for the saved survey has been created too.")."</font><br />\n";

                    }					
					
					if ($saver['email'])
					{
						//Send email
						if (validate_email($saver['email']) && !returnglobal('redo'))
						{
							$subject=$clang->gT("Saved Survey Details");
							$message=$clang->gT("Thank you for saving your survey in progress.  The following details can be used to return to this survey and continue where you left off.  Please keep this e-mail for your reference - we cannot retrieve the password for you.");
							$message.="\n\n".$thissurvey['name']."\n\n";
							$message.=$clang->gT("Name").": ".$saver['identifier']."\n";
							$message.=$clang->gT("Password").": ".$saver['password']."\n\n";
							$message.=$clang->gT("Reload your survey by clicking on the following link (or pasting it into your browser):").":\n";
							$message.=$publicurl."/index.php?sid=$surveyid&loadall=reload&scid=".$scid."&lang=".urlencode($saver['language'])."&loadname=".urlencode($saver['identifier'])."&loadpass=".urlencode($saver['password']);
							if (isset($tokendata['token'])) {$message.="&token=".$tokendata['token'];}
							$from = $thissurvey['adminemail'];

							if (MailTextMessage($message, $subject, $saver['email'], $from, $sitename, false, getBounceEmail($surveyid)))
							{
								$emailsent="Y";
								$dataentryoutput .= "<font class='successtitle'>".$clang->gT("An email has been sent with details about your saved survey")."</font><br />\n";
							}
						}
					}

				}
				else
				{
					safe_die("Unable to insert record into saved_control table.<br /><br />".$connect->ErrorMsg());
				}

			}
			$dataentryoutput .= "\t\t\t<font class='successtitle'><strong>".$clang->gT("Success")."</strong></font><br />\n";
			$thisid=$connect->Insert_ID();
			$dataentryoutput .= "\t\t\t".$clang->gT("The entry was assigned the following record id: ")." {$thisid}<br />\n";
		}

		$dataentryoutput .= $errormsg;
		$dataentryoutput .= "\t\t\t</font><br />[<a href='$scriptname?action=dataentry&amp;sid=$surveyid&amp;language=".$_POST['language']."'>".$clang->gT("Add Another Record")."</a>]<br />\n";
		$dataentryoutput .= "[<a href='$scriptname?sid=$surveyid'>".$clang->gT("Return to Survey Administration")."</a>]<br />\n";
		if (isset($thisid))
		{
			$dataentryoutput .= "\t\t\t[<a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=id&amp;id=$thisid'>".$clang->gT("View This Record")."</a>]<br />\n";
		}
		if (isset($_POST['save']) && $_POST['save'] == "on")
		{
			$dataentryoutput .= "\t\t\t[<a href='$scriptname?action=saved&amp;sid=$surveyid&subaction=all'>".$clang->gT("Browse Saved Responses")."</a>]<br />\n";
		}
		$dataentryoutput .= "\t\t\t[<a href='$scriptname?action=browse&amp;sid=$surveyid&subaction=all&limit=50'>".$clang->gT("Browse Responses")."</a>]<br />\n"
		."\t</td></tr>\n"
		."</table>\n"
		."</body>\n";

	}

	elseif ($subaction == "edit" || $subaction == "editsaved")
	{
		$dataentryoutput .= $surveyoptions;

		if (!isset($_GET['language'])) $_GET['language'] = GetBaseLanguageFromSurveyID($surveyid);






		//FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
		$fnquery = "SELECT * FROM ".db_table_name("questions").", ".db_table_name("groups").", ".db_table_name("surveys")." WHERE
		".db_table_name("questions").".gid=".db_table_name("groups").".gid AND 
		".db_table_name("questions").".language = '{$language}' AND ".db_table_name("groups").".language = '{$language}' AND
		".db_table_name("questions").".sid=".db_table_name("surveys").".sid AND ".db_table_name("questions").".sid='$surveyid'";
		$fnresult = db_execute_assoc($fnquery);
		$fncount = $fnresult->RecordCount();
		//$dataentryoutput .= "$fnquery<br /><br />\n";
		$fnrows = array(); //Create an empty array in case FetchRow does not return any rows
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
        $fnames[] = array ("submitdate", $clang->gT("Completed"), $clang->gT("Completed"), "completed", "completed", "", "");
		
		if ($private == "N") //show token info if survey not private
		{
			$fnames[] = array ("token", $clang->gT("Token ID"), $clang->gT("Token"), "token", "TID", "", "");
		}
		if ($datestamp == "Y")
		{
			$fnames[] = array ("datestamp", $clang->gT("Date Stamp"), $clang->gT("Date Stamp"), "datestamp", "datestamp", "", "");
		}
		if ($ipaddr == "Y")
		{
			$fnames[] = array ("ipaddr", $clang->gT("IP Address"), $clang->gT("IP Address"), "ipaddr", "ipaddr", "", "");
		}
		$fcount=0;
		foreach ($fnrows as $fnrow)
		{
			$fcount++;
			$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}";
			$ftitle = "Grp{$fnrow['gid']}Qst{$fnrow['title']}";
			$fquestion = $fnrow['question'];
			if ($fnrow['type'] == "M" || $fnrow['type'] == "A" || $fnrow['type'] == "B" || $fnrow['type'] == "C" || 
			    $fnrow['type'] == "E" || $fnrow['type'] == "F" || $fnrow['type'] == "H" || $fnrow['type'] == "P" || 
				$fnrow['type'] == "Q" || $fnrow['type'] == "^" || $fnrow['type'] == "J" || $fnrow['type'] == "K")
			{
				$fnrquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$fnrow['qid']} and language='{$language}' ORDER BY sortorder, answer";
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
			elseif ($fnrow['type'] == ":" || $fnrow['type'] == ";")
			{
				$fnrquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$fnrow['qid']} and language='{$language}' ORDER BY sortorder, answer";
				$fnrresult = db_execute_assoc($fnrquery);
    			$fnr2query = "SELECT *
    			              FROM ".db_table_name('labels')."
    			              WHERE lid={$fnrow['lid']}
    			              AND language = '{$language}'
    			              ORDER BY sortorder, title";
    			$fnr2result = db_execute_assoc($fnr2query);
    			while( $fnr2row = $fnr2result->FetchRow())
    			{
    			  $lset[]=$fnr2row;
    			}
				while ($fnrrow = $fnrresult->FetchRow())
				{
				    foreach($lset as $ls)
				    {
					    $fnames[] = array("$field{$fnrrow['code']}_{$ls['code']}", "$ftitle ({$fnrrow['code']})", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}<br /><i>{$ls['title']}</i>", "{$fnrow['qid']}", "{$fnrow['lid']}");
				    }
				}
				unset($lset);
			}
			elseif ($fnrow['type'] == "R")
			{
				$fnrquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$fnrow['qid']} and language='{$language}' ORDER BY sortorder, answer";
				$fnrresult = $connect->Execute($fnrquery);
				$fnrcount = $fnrresult->RecordCount();
				for ($j=1; $j<=$fnrcount; $j++)
				{
					$fnames[] = array("$field$j", "$ftitle ($j)", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "$j", "{$fnrow['qid']}", "{$fnrow['lid']}");
				}
			}
			elseif ($fnrow['type'] == "1")
			{
				$fnrquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$fnrow['qid']} and language='{$language}' ORDER BY sortorder, answer";
				$fnrresult = $connect->Execute($fnrquery);
                while ($fnrrow = $fnrresult->FetchRow())
				{
					$fnames[] = array("$field{$fnrrow['code']}#0", "$ftitle ({$fnrrow['code']})", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']} (1)", "{$fnrow['qid']}", "{$fnrow['lid']}");
					$fnames[] = array("$field{$fnrrow['code']}#1", "$ftitle ({$fnrrow['code']})", "{$fnrow['question']}", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']} (2)", "{$fnrow['qid']}", "{$fnrow['lid1']}");
				}
			}
			elseif ($fnrow['type'] == "O")
			{
                if (!isset($fnrrow)) {$fnrrow=array("code"=>"", "answer"=>"");}
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
				if (($fnrow['type'] == "L" || $fnrow['type'] == "!" || $fnrow['type'] == "Z" || $fnrow['type'] == "W") && $fnrow['other'] =="Y")
				{
					$fnames[] = array("$field"."other", "$ftitle"."other", "{$fnrow['question']}(other)", "{$fnrow['type']}", "$field", "{$fnrrow['code']}", "{$fnrrow['answer']}", "{$fnrow['qid']}", "{$fnrow['lid']}");
				}
			}
		}
		$nfncount = count($fnames)-1;

		//SHOW INDIVIDUAL RECORD

		if ($subaction == "edit")
		{
			$idquery = "SELECT * FROM $surveytable WHERE id=$id";
			$idresult = db_execute_assoc($idquery) or safe_die ("Couldn't get individual record<br />$idquery<br />".$connect->ErrorMsg());
			while ($idrow = $idresult->FetchRow())
			{
				$results[]=$idrow;
			}
		}
		elseif ($subaction == "editsaved")
		{
			if (isset($_GET['public']) && $_GET['public']=="true")
			{
				$password=md5($_GET['accesscode']);
			}
			else
			{
				$password=$_GET['accesscode'];
			}
			$svquery = "SELECT * FROM ".db_table_name("saved_control")."
						WHERE sid=$surveyid
						AND identifier='".$_GET['identifier']."'
						AND access_code='".$password."'";
			$svresult=db_execute_assoc($svquery) or safe_die("Error getting save<br />$svquery<br />".$connect->ErrorMsg());
			while($svrow=$svresult->FetchRow())
			{
				$saver['email']=$svrow['email'];
				$saver['scid']=$svrow['scid'];
				$saver['ip']=$svrow['ip'];
			}
			$svquery = "SELECT * FROM ".db_table_name("saved_control")." WHERE scid=".$saver['scid'];
			$svresult=db_execute_assoc($svquery) or safe_die("Error getting saved info<br />$svquery<br />".$connect->ErrorMsg());
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
			$results1['datestamp']=date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust);      
			$results1['ipaddr']=$saver['ip'];
			$results[]=$results1;
		}
		//	$dataentryoutput .= "<pre>";print_r($results);$dataentryoutput .= "</pre>";

		$dataentryoutput .= "<form method='post' action='$scriptname?action=dataentry' name='editsurvey' id='editsurvey'>\n"
		."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr><td colspan='2' height='4'><strong>"
		.$clang->gT("Data Entry")."</strong></td></tr>\n"
		."\t<tr><td style='border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: #555555' colspan='2' align='center'><strong>"
		.sprintf($clang->gT("Editing response (ID %s)"),$id)."</strong></td></tr>\n"
		."\t<tr><td colspan='2' height='1'></td></tr>\n";

		foreach ($results as $idrow)
		{
			//$dataentryoutput .= "<pre>"; print_r($idrow);$dataentryoutput .= "</pre>";
			for ($i=0; $i<$nfncount+1; $i++)
			{
				//$dataentryoutput .= "<pre>"; print_r($fnames[$i]);$dataentryoutput .= "</pre>";
				$answer = $idrow[$fnames[$i][0]];
				$question=$fnames[$i][2];
				$dataentryoutput .= "\t<tr>\n"
				."\t\t<td valign='top' align='right' width='25%'>"
				."\n";
				$dataentryoutput .= "\t\t\t<strong>".strip_javascript($question)."</strong>\n";
				$dataentryoutput .= "\t\t</font></td>\n"
				."\t\t<td valign='top' align='left'>\n";
				//$dataentryoutput .= "\t\t\t-={$fnames[$i][3]}=-"; //Debugging info
				switch ($fnames[$i][3])
				{
				    case "completed":
                		// First compute the submitdate
                		if ($private == "Y" && $datestamp == "N")
                		{
                			// In case of anonymous answers survey with no datestamp
                			// then the the answer submutdate gets a conventional timestamp
                			// 1st Jan 1980
                			$mysubmitdate = date("Y-m-d H:i:s",mktime(0,0,0,1,1,1980));
                		}
                		else
                		{
                			$mysubmitdate = date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust);      
                		}
				        $completedate= empty($idrow[$fnames[$i][0]]) ? $mysubmitdate : $idrow[$fnames[$i][0]];

				        $dataentryoutput .= "                <select name='submitdate'>\n";
				        $dataentryoutput .= "                    <option value=";
				        if(empty($idrow[$fnames[$i][0]])) { $dataentryoutput .= "'' selected"; }
				                                  else    { $dataentryoutput .= "'N'"; }
				        $dataentryoutput .= ">".$clang->gT("No")."</option>\n";
				        $dataentryoutput .= "                    <option value=";
				        if(!empty($idrow[$fnames[$i][0]])) { $dataentryoutput .= "'' selected"; }
				                                  else     { $dataentryoutput .= "'$completedate'"; }
				        $dataentryoutput .= ">".$clang->gT("Yes")."</option>\n";
				        $dataentryoutput .= "                </select>\n";
				        break;
					case "X": //Boilerplate question
					    $dataentryoutput .= "";
					    break;
					case "Q":
					case "K":
						$dataentryoutput .= "\t\t\t{$fnames[$i][6]}&nbsp;<input type='text' name='{$fnames[$i][0]}' value='"
						.$idrow[$fnames[$i][0]] . "' />\n";
						break;
					case "id":
						$dataentryoutput .= "\t\t\t&nbsp;{$idrow[$fnames[$i][0]]} <font color='red' size='1'>".$clang->gT("Cannot be modified")."</font>\n";
						break;
					case "5": //5 POINT CHOICE radio-buttons
					for ($x=1; $x<=5; $x++)
					{
						$dataentryoutput .= "\t\t\t<input type='radio' class='radiobtn' name='{$fnames[$i][0]}' value='$x'";
						if ($idrow[$fnames[$i][0]] == $x) {$dataentryoutput .= " checked";}
						$dataentryoutput .= " />$x \n";
					}
					break;
					case "D": //DATE
                        $datetimeobj = new Date_Time_Converter($idrow[$fnames[$i][0]] , "Y-m-d H:i:s");
                        $thisdate=$datetimeobj->convert($dateformatdetails['phpdate']);                 
					    $dataentryoutput .= "\t\t\t<input type='text' class='popupdate' size='12' name='{$fnames[$i][0]}' value='{$thisdate}' />\n";
					break;
					case "G": //GENDER drop-down list
					    $dataentryoutput .= "\t\t\t<select name='{$fnames[$i][0]}'>\n"
					    ."\t\t\t\t<option value=''";
					    if ($idrow[$fnames[$i][0]] == "") {$dataentryoutput .= " selected='selected'";}
					    $dataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n"
					    ."\t\t\t\t<option value='F'";
					    if ($idrow[$fnames[$i][0]] == "F") {$dataentryoutput .= " selected='selected'";}
					    $dataentryoutput .= ">".$clang->gT("Female")."</option>\n"
					    ."\t\t\t\t<option value='M'";
					    if ($idrow[$fnames[$i][0]] == "M") {$dataentryoutput .= " selected='selected'";}
					    $dataentryoutput .= ">".$clang->gT("Male")."</option>\n"
					    ."\t\t\t</select>\n";
					break;
					case "W":
					case "Z":
						if (substr($fnames[$i][0], -5) == "other")
						{
							$dataentryoutput .= "\t\t\t<input type='text' name='{$fnames[$i][0]}' value='"
							.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n";
						}
						else
						{
							$lquery = "SELECT * FROM ".db_table_name("labels")
                                     ." WHERE lid={$fnames[$i][8]} AND ".db_table_name("labels").".language = '{$language}' ORDER BY sortorder, code";
							$lresult = db_execute_assoc($lquery);
							$dataentryoutput .= "\t\t\t<select name='{$fnames[$i][0]}'>\n"
							."\t\t\t\t<option value=''";
							if ($idrow[$fnames[$i][0]] == "") {$dataentryoutput .= " selected='selected'";}
							$dataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n";

							while ($llrow = $lresult->FetchRow())
							{
								$dataentryoutput .= "\t\t\t\t<option value='{$llrow['code']}'";
								if ($idrow[$fnames[$i][0]] == $llrow['code']) {$dataentryoutput .= " selected='selected'";}
								$dataentryoutput .= ">{$llrow['title']}</option>\n";
							}
							$oquery="SELECT other FROM ".db_table_name("questions")." WHERE qid={$fnames[$i][7]} AND ".db_table_name("questions").".language = '{$language}'";
							$oresult=db_execute_assoc($oquery) or safe_die("Couldn't get other for list question<br />".$oquery."<br />".$connect->ErrorMsg());
							while($orow = $oresult->FetchRow())
							{
								$fother=$orow['other'];
							}
							if ($fother =="Y")
							{
								$dataentryoutput .= "<option value='-oth-'";
								if ($idrow[$fnames[$i][0]] == "-oth-"){$dataentryoutput .= " selected='selected'";}
								$dataentryoutput .= ">".$clang->gT("Other")."</option>\n";
							}
							$dataentryoutput .= "\t\t\t</select>\n";
						}
						break;
					case "L": //LIST drop-down
					case "!": //List (Radio)
					$qidattributes=getQuestionAttributes($fnames[$i][7]);
					if ($optCategorySeparator = arraySearchByKey('category_separator', $qidattributes, 'attribute', 1))
					{
						$optCategorySeparator = $optCategorySeparator['value'];
					}
					else
					{
						unset($optCategorySeparator);
					}

					if (substr($fnames[$i][0], -5) == "other")
					{
						$dataentryoutput .= "\t\t\t<input type='text' name='{$fnames[$i][0]}' value='"
						.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n";
					}
					else
					{
						$lquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$fnames[$i][7]} AND ".db_table_name("answers").".language = '{$language}' ORDER BY sortorder, answer";
						$lresult = db_execute_assoc($lquery);
						$dataentryoutput .= "\t\t\t<select name='{$fnames[$i][0]}'>\n"
						."\t\t\t\t<option value=''";
						if ($idrow[$fnames[$i][0]] == "") {$dataentryoutput .= " selected='selected'";}
						$dataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n";

						if (!isset($optCategorySeparator))
						{
							while ($llrow = $lresult->FetchRow())
							{
								$dataentryoutput .= "\t\t\t\t<option value='{$llrow['code']}'";
								if ($idrow[$fnames[$i][0]] == $llrow['code']) {$dataentryoutput .= " selected='selected'";}
								$dataentryoutput .= ">{$llrow['answer']}</option>\n";
							}
						}
						else
						{
							$defaultopts = array();
							$optgroups = array();
							while ($llrow = $lresult->FetchRow())
							{
								list ($categorytext, $answertext) = explode($optCategorySeparator,$llrow['answer']);
								if ($categorytext == '')
								{
									$defaultopts[] = array ( 'code' => $llrow['code'], 'answer' => $answertext, 'default_value' => $llrow['default_value']);
								}
								else
								{
									 $optgroups[$categorytext][] = array ( 'code' => $llrow['code'], 'answer' => $answertext, 'default_value' => $llrow['default_value']);
								}
							}

							foreach ($optgroups as $categoryname => $optionlistarray)
							{
								$dataentryoutput .= "\t\t\t\t<optgroup class=\"dropdowncategory\" label=\"".$categoryname."\">\n";
								foreach ($optionlistarray as $optionarray)
								{
									$dataentryoutput .= "\t\t\t\t\t<option value='{$optionarray['code']}'";
									if ($idrow[$fnames[$i][0]] == $optionarray['code']) {$dataentryoutput .= " selected='selected'";}
									$dataentryoutput .= ">{$optionarray['answer']}</option>\n";
								}
								$dataentryoutput .= "\t\t\t\t</optgroup>\n";
							}
							foreach ($defaultopts as $optionarray)
							{
								$dataentryoutput .= "\t\t\t\t<option value='{$optionarray['code']}'";
								if ($idrow[$fnames[$i][0]] == $optionarray['code']) {$dataentryoutput .= " selected='selected'";}
								$dataentryoutput .= ">{$optionarray['answer']}</option>\n";
							}

						}

						$oquery="SELECT other FROM ".db_table_name("questions")." WHERE qid={$fnames[$i][7]} AND ".db_table_name("questions").".language = '{$language}'";
						$oresult=db_execute_assoc($oquery) or safe_die("Couldn't get other for list question<br />".$oquery."<br />".$connect->ErrorMsg());
						while($orow = $oresult->FetchRow())
						{
							$fother=$orow['other'];
						}
						if ($fother =="Y")
						{
							$dataentryoutput .= "<option value='-oth-'";
							if ($idrow[$fnames[$i][0]] == "-oth-"){$dataentryoutput .= " selected='selected'";}
							$dataentryoutput .= ">".$clang->gT("Other")."</option>\n";
						}
						$dataentryoutput .= "\t\t\t</select>\n";
					}
					break;
					case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
					$lquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$fnames[$i][7]} AND ".db_table_name("answers").".language = '{$language}' ORDER BY sortorder, answer";
					$lresult = db_execute_assoc($lquery);
					$dataentryoutput .= "\t\t\t<select name='{$fnames[$i][0]}'>\n"
					."\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {$dataentryoutput .= " selected='selected'";}
					$dataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n";

					while ($llrow = $lresult->FetchRow())
					{
						$dataentryoutput .= "\t\t\t\t<option value='{$llrow['code']}'";
						if ($idrow[$fnames[$i][0]] == $llrow['code']) {$dataentryoutput .= " selected='selected'";}
						$dataentryoutput .= ">{$llrow['answer']}</option>\n";
					}
					$i++;
					$dataentryoutput .= "\t\t\t</select>\n"
					."\t\t\t<br />\n"
					."\t\t\t<textarea cols='45' rows='5' name='{$fnames[$i][0]}'>"
					.htmlspecialchars($idrow[$fnames[$i][0]]) . "</textarea>\n";
					break;
					case "R": //RANKING TYPE QUESTION
					$l=$i;
					$thisqid=$fnames[$l][7];
					$myfname=substr($fnames[$i][0], 0, -1);
					while (isset($fnames[$i][3]) && $fnames[$i][3] == "R")
					{
						//Let's get all the existing values into an array
						if ($idrow[$fnames[$i][0]])
						{
							$currentvalues[] = $idrow[$fnames[$i][0]];
						}
						$i++;
					}
					$ansquery = "SELECT * FROM ".db_table_name("answers")." WHERE ".db_table_name("answers").".language = '{$language}' AND qid=$thisqid ORDER BY sortorder, answer";
					$ansresult = db_execute_assoc($ansquery);
					$anscount = $ansresult->RecordCount();
					$dataentryoutput .= "\t\t\t<script type='text/javascript'>\n"
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
						$ranklist .= "\t\t\t\t\t\t$j:&nbsp;<input class='ranklist' id='RANK_$thisqid$j'";
						if (isset($currentvalues) && $currentvalues[$k])
						{
							$ranklist .= " value='".$thistext."'";
						}
						$ranklist .= " onFocus=\"this.blur()\"  />\n"
						. "\t\t\t\t\t\t<input type='hidden' id='d$myfname$j' name='d$myfname$j' value='";
						if (isset($currentvalues) && $currentvalues[$k])
						{
							$ranklist .= $thiscode;
							$chosen[]=array($thiscode, $thistext);
						}
						$ranklist .= "' />\n"
						. "\t\t\t\t\t\t<img src='$imagefiles/cut.gif' alt='".$clang->gT("Remove this item")."' title='".$clang->gT("Remove this item")."' ";
						if ($j != $existing)
						{
							$ranklist .= "style='display:none'";
						}
						$ranklist .= " id='cut_$thisqid$j' onclick=\"deletethis_$thisqid(document.editsurvey.RANK_$thisqid$j.value, document.editsurvey.d$myfname$j.value, document.editsurvey.RANK_$thisqid$j.id, this.id)\"><br />\n\n";
					}

					if (!isset($choicelist)) {$choicelist="";}
					$choicelist .= "\t\t\t\t\t\t<select class='choicelist' size='$anscount' name='CHOICES' id='CHOICES_$thisqid' onclick=\"rankthis_$thisqid(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" >\n";
					foreach ($answers as $ans)
					{
						if (!in_array($ans, $chosen))
						{
							$choicelist .= "\t\t\t\t\t\t\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
						}
					}
					$choicelist .= "\t\t\t\t\t\t</select>\n";
					$dataentryoutput .= "\t\t\t<table align='left' border='0' cellspacing='5'>\n"
					."\t\t\t\t<tr>\n"
					."\t\t\t\t\t<td align='left' valign='top' width='200'>\n"
					."\t\t\t\t\t\t<strong>"
					.$clang->gT("Your Choices").":</strong><br />\n"
					.$choicelist
					."\t\t\t\t\t</td>\n"
					."\t\t\t\t\t<td align='left'>\n"
					."\t\t\t\t\t\t<strong>"
					.$clang->gT("Your Ranking").":</strong><br />\n"
					.$ranklist
					."\t\t\t\t\t</td>\n"
					."\t\t\t\t</tr>\n"
					."\t\t\t</table>\n"
					."\t\t\t<input type='hidden' name='multi' value='$anscount' />\n"
					."\t\t\t<input type='hidden' name='lastfield' value='";
					if (isset($multifields)) {$dataentryoutput .= $multifields;}
					$dataentryoutput .= "' />\n";
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

//					while ($fnames[$i][3] == "M" && $question != "" && $question == $fnames[$i][2])
					while ($fnames[$i][3] == "M" && $question == $fnames[$i][2])
					{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
						//$dataentryoutput .= substr($fnames[$i][0], strlen($fnames[$i][0])-5, 5)."<br />\n";
						if (substr($fnames[$i][0], -5) == "other")
						{
							$dataentryoutput .= "\t\t\t<input type='text' name='{$fnames[$i][0]}' value='"
							.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n";
						}
						else
						{
							$dataentryoutput .= "\t\t\t<input type='checkbox' class='checkboxbtn' name='{$fnames[$i][0]}' value='Y'";
							if ($idrow[$fnames[$i][0]] == "Y") {$dataentryoutput .= " checked";}
							$dataentryoutput .= " />{$fnames[$i][6]}<br />\n";
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
						$dataentryoutput .= "\t\t\t<input type='checkbox' class='checkboxbtn' name='{$fnames[$i][0]}' value='Y'";
						if ($idrow[$fnames[$i][0]] == "Y") {$dataentryoutput .= " checked";}
						$dataentryoutput .= " />{$fnames[$i][6]}<br />\n";
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

					case "I": //Language Switch
					$lquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$fnames[$i][7]} AND ".db_table_name("answers").".language = '{$language}' ORDER BY sortorder, answer";
					$lresult = db_execute_assoc($lquery);


                    $slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                    $baselang = GetBaseLanguageFromSurveyID($surveyid);
                    array_unshift($slangs,$baselang);

                    $dataentryoutput.= "<select name='{$fnames[$i][0]}'>\n";
					$dataentryoutput .= "\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {$dataentryoutput .= " selected='selected'";}
					$dataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n";

                    foreach ($slangs as $lang)
                       	{
                            $dataentryoutput.="<option value='{$lang}'";
                       		if ($lang == $idrow[$fnames[$i][0]]) {$dataentryoutput .= " selected='selected'";}
                            $dataentryoutput.=">".getLanguageNameFromCode($lang,false)."</option>\n";
                       	}
                    $dataentryoutput .= "</select>";
					break;

					case "P": //MULTIPLE OPTIONS WITH COMMENTS checkbox + text
					$dataentryoutput .= "<table>\n";
					while (isset($fnames[$i]) && $fnames[$i][3] == "P")
					{
						$thefieldname=$fnames[$i][0];
						if (substr($thefieldname, -7) == "comment")
						{
							$dataentryoutput .= "\t\t<td><input type='text' name='{$fnames[$i][0]}' size='50' value='"
							.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' /></td>\n"
							."\t</tr>\n";
						}
						elseif (substr($fnames[$i][0], -5) == "other")
						{
							$dataentryoutput .= "\t<tr>\n"
							."\t\t<td>\n"
							."\t\t\t<input type='text' name='{$fnames[$i][0]}' size='30' value='"
							.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n"
							."\t\t</td>\n"
							."\t\t<td>\n";
							$i++;
							$dataentryoutput .= "\t\t\t<input type='text' name='{$fnames[$i][0]}' size='50' value='"
							.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n"
							."\t\t</td>\n"
							."\t</tr>\n";
						}
						else
						{
							$dataentryoutput .= "\t<tr>\n"
							."\t\t<td><input type='checkbox' class='checkboxbtn' name=\"{$fnames[$i][0]}\" value='Y'";
							if ($idrow[$fnames[$i][0]] == "Y") {$dataentryoutput .= " checked";}
							$dataentryoutput .= " />{$fnames[$i][6]}</td>\n";
						}
						$i++;
					}
					$dataentryoutput .= "</table>\n";
					$i--;
					break;
					case "N": //NUMERICAL TEXT
					$dataentryoutput .= "\t\t\t<input type='text' name='{$fnames[$i][0]}' value='{$idrow[$fnames[$i][0]]}' "
					."onkeypress=\"return goodchars(event,'0123456789.,')\" />\n";
					break;
					case "S": //SHORT FREE TEXT
					$dataentryoutput .= "\t\t\t<input type='text' name='{$fnames[$i][0]}' value='"
					.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "' />\n";
					break;
					case "T": //LONG FREE TEXT
					$dataentryoutput .= "\t\t\t<textarea rows='5' cols='45' name='{$fnames[$i][0]}'>"
					.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "</textarea>\n";
					break;
					case "U": //HUGE FREE TEXT
					$dataentryoutput .= "\t\t\t<textarea rows='50' cols='70' name='{$fnames[$i][0]}'>"
					.htmlspecialchars($idrow[$fnames[$i][0]], ENT_QUOTES) . "</textarea>\n";
					break;
					case "Y": //YES/NO radio-buttons
					$dataentryoutput .= "\t\t\t<select name='{$fnames[$i][0]}'>\n"
					."\t\t\t\t<option value=''";
					if ($idrow[$fnames[$i][0]] == "") {$dataentryoutput .= " selected='selected'";}
					$dataentryoutput .= ">".$clang->gT("Please choose")."..</option>\n"
					."\t\t\t\t<option value='Y'";
					if ($idrow[$fnames[$i][0]] == "Y") {$dataentryoutput .= " selected='selected'";}
					$dataentryoutput .= ">".$clang->gT("Yes")."</option>\n"
					."\t\t\t\t<option value='N'";
					if ($idrow[$fnames[$i][0]] == "N") {$dataentryoutput .= " selected='selected'";}
					$dataentryoutput .= ">".$clang->gT("No")."</option>\n"
					."\t\t\t</select>\n";
					break;
					case "A": //ARRAY (5 POINT CHOICE) radio-buttons
					$dataentryoutput .= "<table>\n";
					$thisqid=$fnames[$i][7];
					while ($fnames[$i][7] == $thisqid)
					{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
						$dataentryoutput .= "\t<tr>\n"
						."\t\t<td align='right'>{$fnames[$i][6]}</td>\n"
						."\t\t<td>\n";
						for ($j=1; $j<=5; $j++)
						{
							$dataentryoutput .= "\t\t\t<input type='radio' class='radiobtn' name='{$fnames[$i][0]}' value='$j'";
							if ($idrow[$fnames[$i][0]] == $j) {$dataentryoutput .= " checked";}
							$dataentryoutput .= " />$j&nbsp;\n";
						}
						$dataentryoutput .= "\t\t</td>\n"
						."\t</tr>\n";
						$i++;
					}
					$dataentryoutput .= "</table>\n";
					$i--;
					break;
					case "B": //ARRAY (10 POINT CHOICE) radio-buttons
					$dataentryoutput .= "<table>\n";
					$thisqid=$fnames[$i][7];
					while ($fnames[$i][7] == $thisqid)
					{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
						$dataentryoutput .= "\t<tr>\n"
						."\t\t<td align='right'>{$fnames[$i][6]}</td>\n"
						."\t\t<td>\n";
						for ($j=1; $j<=10; $j++)
						{
							$dataentryoutput .= "\t\t\t<input type='radio' class='radiobtn' name='{$fnames[$i][0]}' value='$j'";
							if ($idrow[$fnames[$i][0]] == $j) {$dataentryoutput .= " checked";}
							$dataentryoutput .= " />$j&nbsp;\n";
						}
						$dataentryoutput .= "\t\t</td>\n"
						."\t</tr>\n";
						$i++;
					}
					$i--;
					$dataentryoutput .= "</table>\n";
					break;
					case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
					$dataentryoutput .= "<table>\n";
					$thisqid=$fnames[$i][7];
					while ($fnames[$i][7] == $thisqid)
					{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
						$dataentryoutput .= "\t<tr>\n"
						."\t\t<td align='right'>{$fnames[$i][6]}</td>\n"
						."\t\t<td>\n"
						."\t\t\t<input type='radio' class='radiobtn' name='{$fnames[$i][0]}' value='Y'";
						if ($idrow[$fnames[$i][0]] == "Y") {$dataentryoutput .= " checked";}
						$dataentryoutput .= " />".$clang->gT("Yes")."&nbsp;\n"
						."\t\t\t<input type='radio' class='radiobtn' name='{$fnames[$i][0]}' value='U'";
						if ($idrow[$fnames[$i][0]] == "U") {$dataentryoutput .= " checked";}
						$dataentryoutput .= " />".$clang->gT("Uncertain")."&nbsp;\n"
						."\t\t\t<input type='radio' class='radiobtn' name='{$fnames[$i][0]}' value='N'";
						if ($idrow[$fnames[$i][0]] == "N") {$dataentryoutput .= " checked";}
						$dataentryoutput .= " />".$clang->gT("No")."&nbsp;\n"
						."\t\t</td>\n"
						."\t</tr>\n";
						$i++;
					}
					$i--;
					$dataentryoutput .= "</table>\n";
					break;
					case "E": //ARRAY (Increase/Same/Decrease) radio-buttons
					$dataentryoutput .= "<table>\n";
					$thisqid=$fnames[$i][7];
					while ($fnames[$i][7] == $thisqid)
					{
						$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
						$dataentryoutput .= "\t<tr>\n"
						."\t\t<td align='right'>{$fnames[$i][6]}</td>\n"
						."\t\t<td>\n"
						."\t\t\t<input type='radio' class='radiobtn' name='{$fnames[$i][0]}' value='I'";
						if ($idrow[$fnames[$i][0]] == "I") {$dataentryoutput .= " checked";}
						$dataentryoutput .= " />Increase&nbsp;\n"
						."\t\t\t<input type='radio' class='radiobtn' name='{$fnames[$i][0]}' value='S'";
						if ($idrow[$fnames[$i][0]] == "I") {$dataentryoutput .= " checked";}
						$dataentryoutput .= " />Same&nbsp;\n"
						."\t\t\t<input type='radio' class='radiobtn' name='{$fnames[$i][0]}' value='D'";
						if ($idrow[$fnames[$i][0]] == "D") {$dataentryoutput .= " checked";}
						$dataentryoutput .= " />Decrease&nbsp;\n"
						."\t\t</td>\n"
						."\t</tr>\n";
						$i++;
					}
					$i--;
					$dataentryoutput .= "</table>\n";
					break;
					case "F": //ARRAY (Flexible Labels)
                    case "H":
					case "1":
						$dataentryoutput .= "<table>\n";
						$thisqid=$fnames[$i][7];
						while (isset($fnames[$i][7]) && $fnames[$i][7] == $thisqid)
						{
							$fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
							$dataentryoutput .= "\t<tr>\n"
							."\t\t<td align='right' valign='top'>{$fnames[$i][6]}</td>\n";
							$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid='{$fnames[$i][8]}' and language='$language' order by sortorder, code";
							$fresult = db_execute_assoc($fquery);
							$dataentryoutput .= "\t\t<td>\n";
							while ($frow=$fresult->FetchRow())
							{
								$dataentryoutput .= "\t\t\t<input type='radio' class='radiobtn' name='{$fnames[$i][0]}' value='{$frow['code']}'";
								if ($idrow[$fnames[$i][0]] == $frow['code']) {$dataentryoutput .= " checked";}
								$dataentryoutput .= " />".$frow['title']."&nbsp;\n";
							}
                            //Add 'No Answer'
                            $dataentryoutput .= "\t\t\t<input type='radio' class='radiobtn' name='{$fnames[$i][0]}' value=''";
                            if ($idrow[$fnames[$i][0]] == '') {$dataentryoutput .= " checked";}
                            $dataentryoutput .= " />".$clang->gT("No answer")."&nbsp;\n";
                            
							$dataentryoutput .= "\t\t</td>\n"
							."\t</tr>\n";
							$i++;
						}
						$i--;
						$dataentryoutput .= "</table>\n";
						break;
					case ":": //ARRAY (Multi Flexi) (Numbers)
                    	$qidattributes=getQuestionAttributes($fnames[$i][7]);
                    	if ($maxvalue=arraySearchByKey("multiflexible_max", $qidattributes, "attribute", 1)) {
                    		$maxvalue=$maxvalue['value'];
                    	} else {
                    		$maxvalue=10;
                    	}
                    	if ($minvalue=arraySearchByKey("multiflexible_min", $qidattributes, "attribute", 1)) {
                    		$minvalue=$minvalue['value'];
                    	} else {
                    		$minvalue=1;
                    	}
                    	if ($stepvalue=arraySearchByKey("multiflexible_step", $qidattributes, "attribute", 1)) {
                    		$stepvalue=$stepvalue['value'];
                    	} else {
                    		$stepvalue=1;
                    	}
            			if (arraySearchByKey("multiflexible_checkbox", $qidattributes, "attribute", 1)) {
            				$minvalue=0;
            				$maxvalue=1;
            				$stepvalue=1;
            			}
					    $dataentryoutput .= "<table>\n";
					    $thisqid=$fnames[$i][7];
					    while (isset($fnames[$i][7]) && $fnames[$i][7] == $thisqid)
					    {
						   $fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
						   $dataentryoutput .= "\t<tr>\n"
						                     . "\t\t<td align='right' valign='top'>{$fnames[$i][6]}</td>\n";
							$dataentryoutput .= "\t\t<td>\n";
							$dataentryoutput .= "\t\t\t<select name='{$fnames[$i][0]}'>\n";
							for($ii=$minvalue;$ii<=$maxvalue;$ii+=$stepvalue)
							{
							   $dataentryoutput .= "\t\t\t\t<option value='$ii'";
							   if($idrow[$fnames[$i][0]] == $ii) {$dataentryoutput .= " selected";}
							   $dataentryoutput .= ">$ii</option>\n";
							}
							$dataentryoutput .= "\t\t</td>\n"
							."\t</tr>\n";
						   $i++;
						}
						$i--;
						$dataentryoutput .= "</table>\n";
					break;
					case ";": //ARRAY (Multi Flexi)
					    $dataentryoutput .= "<table>\n";
					    $thisqid=$fnames[$i][7];
					    while (isset($fnames[$i][7]) && $fnames[$i][7] == $thisqid)
					    {
						   $fieldn = substr($fnames[$i][0], 0, strlen($fnames[$i][0]));
						   $dataentryoutput .= "\t<tr>\n"
						                     . "\t\t<td align='right' valign='top'>{$fnames[$i][6]}</td>\n";
							$dataentryoutput .= "\t\t<td>\n";
							$dataentryoutput .= "\t\t\t<input type='text' name='{$fnames[$i][0]}' value='";
							if(!empty($idrow[$fnames[$i][0]])) {$dataentryoutput .= $idrow[$fnames[$i][0]];}
							$dataentryoutput .= "' />\t\t</td>\n"
							."\t</tr>\n";
						   $i++;
						}
						$i--;
						$dataentryoutput .= "</table>\n";
					break;
					default: //This really only applies to tokens for non-private surveys
					$dataentryoutput .= "\t\t\t<input type='text' name='{$fnames[$i][0]}' value='"
					.$idrow[$fnames[$i][0]] . "' />\n";
					break;
				}
		
				$dataentryoutput .= "		</td>
							</tr>
							<tr>
								<td colspan='2' height='1'>
								</td>
							</tr>\n";
			}
		}
		$dataentryoutput .= "</table>\n"
		."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
		if ($subaction == "edit")
		{
			$dataentryoutput .= "	<tr>
						<td align='center'>
						 <input type='submit' value='".$clang->gT("Update Entry")."' />
						 <input type='hidden' name='id' value='$id' />
						 <input type='hidden' name='sid' value='$surveyid' />
						 <input type='hidden' name='subaction' value='update' />
						 <input type='hidden' name='language' value='".$_GET['language']."' />
						 <input type='hidden' name='surveytable' value='".db_table_name("survey_".$surveyid)."' />
						</td>
					</tr>\n";
		}
		elseif ($subaction == "editsaved")
		{
	
		
			$dataentryoutput .= "<script type='text/javascript'>
				  <!--
					function saveshow(value)
						{
						if (document.getElementById(value).checked == true)
							{
							document.getElementById(\"closerecord\").checked=false;
							document.getElementById(\"closerecord\").disabled=true;
							document.getElementById(\"saveoptions\").style.display=\"\";
							}
						else
							{
							document.getElementById(\"saveoptions\").style.display=\"none\";
							document.getElementById(\"closerecord\").disabled=false;
							}
						}
				  //-->
				  </script>\n";
			$dataentryoutput .= "\t<tr>\n";
			$dataentryoutput .= "\t\t<td colspan='3' align='center'>\n";
			$dataentryoutput .= "\t\t<table><tr><td align='left'>\n";
			$dataentryoutput .= "\t\t\t<input type='checkbox' class='checkboxbtn' name='closerecord' id='closerecord' /><label for='closerecord'>".$clang->gT("Finalize response submission")."</label></td></tr>\n";
			$dataentryoutput .="<input type='hidden' name='closedate' value='".date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)."' />\n";
			$dataentryoutput .= "\t\t\t<tr><td align='left'><input type='checkbox' class='checkboxbtn' name='save' id='save' onclick='saveshow(this.id)' /><label for='save'>".$clang->gT("Save for further completion by survey user")."</label>\n";
			$dataentryoutput .= "\t\t</td></tr></table>\n";
			$dataentryoutput .= "<div name='saveoptions' id='saveoptions' style='display: none'>\n";
			$dataentryoutput .= "<table align='center' class='outlinetable' cellspacing='0'>
				  <tr><td align='right'>".$clang->gT("Identifier:")."</td>
				  <td><input type='text' name='save_identifier'";
			if (returnglobal('identifier'))
			{
				$dataentryoutput .= " value=\"".stripslashes(stripslashes(returnglobal('identifier')))."\"";
			}
			$dataentryoutput .= " /></td></tr>
				  </table>\n"
			."<input type='hidden' name='save_password' value='".returnglobal('accesscode')."' />\n"
			."<input type='hidden' name='save_confirmpassword' value='".returnglobal('accesscode')."' />\n"
			."<input type='hidden' name='save_email' value='".$saver['email']."' />\n"
			."<input type='hidden' name='save_scid' value='".$saver['scid']."' />\n"
			."<input type='hidden' name='redo' value='yes' />\n";
			$dataentryoutput .= "\t\t</td>\n";
			$dataentryoutput .= "\t</tr>"
			."</div>\n";
			$dataentryoutput .= "	<tr>
					<td align='center'>
					 <input type='submit' value='".$clang->gT("Submit")."' />
					 <input type='hidden' name='sid' value='$surveyid' />
					 <input type='hidden' name='subaction' value='insert' />
					 <input type='hidden' name='language' value='".$datalang."' />
					 <input type='hidden' name='surveytable' value='".db_table_name("survey_".$surveyid)."' />
					</td>
				</tr>\n";
		}

		$dataentryoutput .=  "</table>\n"
		."</form>\n";
	}


	elseif ($subaction == "update")
	{
		$baselang = GetBaseLanguageFromSurveyID($surveyid);
		$dataentryoutput .= "<table width='450' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr><td colspan='2' height='4'><strong>"
		.$clang->gT("Data Entry")."</strong></td></tr>\n"
		."\t<tr><td align='center'>\n";
		$iquery = "SELECT * FROM ".db_table_name("questions").", ".db_table_name("groups")." WHERE
		".db_table_name("questions").".gid=".db_table_name("groups").".gid  AND
		".db_table_name("questions").".language = '{$baselang}' AND  ".db_table_name("groups").".language = '{$baselang}' AND
		".db_table_name("questions").".sid=$surveyid 
		ORDER BY ".db_table_name("groups").".group_order, title";
		$iresult = db_execute_assoc($iquery);

		$updateqr = "UPDATE $surveytable SET \n";

		while ($irow = $iresult->FetchRow())
		{
			if ($irow['type'] != "Q" && $irow['type'] != "M" && $irow['type'] != "P" && $irow['type'] != "A" && 
			    $irow['type'] != "B" && $irow['type'] != "C" && $irow['type'] != "E" && $irow['type'] != "F" && 
				$irow['type'] != "H" && $irow['type'] != "O" && $irow['type'] != "R" && $irow['type'] != "^" && 
				$irow['type'] != "J" && $irow['type'] != "K" && $irow['type'] != ":" && $irow['type'] != "1" &&
				$irow['type'] != ";")
			{
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
				if (isset($_POST[$fieldname])) { $thisvalue=$_POST[$fieldname]; } else {$thisvalue="";}
				if ($irow['type'] == 'D')
				{
                    if ($thisvalue == "")
                    {
                        $updateqr .= db_quote_id($fieldname)." = NULL, \n";
                    }
                    else
                    {
                        $datetimeobj = new Date_Time_Converter($thisvalue,$dateformatdetails['phpdate']);
                        $updateqr .= db_quote_id($fieldname)." = '{$datetimeobj->convert("Y-m-d H:i:s")}', \n";  
                    }
				}
				elseif ( $irow['type'] == 'N' && $thisvalue == "")
				{
					$updateqr .= db_quote_id($fieldname)." = NULL, \n";
				}
				else
				{
					$updateqr .= db_quote_id($fieldname)." = '" . auto_escape($thisvalue) . "', \n";
				}
				unset($thisvalue);
				// handle ! other
				if (($irow['type'] == "!" || $irow['type'] == "W" || $irow['type'] == "Z" || $irow['type'] == "L") && $irow['other'] == "Y")
				{
					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}other";
					if (isset($_POST[$fieldname])) {$thisvalue=$_POST[$fieldname];} else {$thisvalue="";}
					$updateqr .= db_quote_id($fieldname)." = '" . auto_escape($thisvalue) . "', \n";
					unset($thisvalue);
				}
			}
			elseif ($irow['type'] == "O")
			{
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}";
				$updateqr .= db_quote_id($fieldname)." = '" . auto_escape($_POST[$fieldname]) . "', \n";
				$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}comment";
				$updateqr .= db_quote_id($fieldname)." = '" . auto_escape($_POST[$fieldname]) . "', \n";
			}
            elseif ($irow['type'] == "1")
            {
                $i2query = "SELECT ".db_table_name("answers").".*, ".db_table_name("questions").".other FROM ".db_table_name("answers").", ".db_table_name("questions")." WHERE
                ".db_table_name("answers").".qid=".db_table_name("questions").".qid AND ".db_table_name("questions").".qid={$irow['qid']} AND 
                ".db_table_name("questions").".language = '{$language}' AND ".db_table_name("answers").".language = '{$language}' AND
                ".db_table_name("questions").".sid=$surveyid ORDER BY ".db_table_name("answers").".sortorder, ".db_table_name("answers").".answer";
            
                $i2result = $connect->Execute($i2query);
                $i2count = $i2result->RecordCount();
                while ($i2answ = $i2result->FetchRow())
                {
                    // first scale
                    $fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2answ['code']}#0";
                    $updateqr .= db_quote_id($fieldname)." = '" . auto_escape($_POST[$fieldname]) . "', \n";                                          // second scale
                    // second  scale                        
                    $fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2answ['code']}#1";
                    $updateqr .= db_quote_id($fieldname)." = '" . auto_escape($_POST[$fieldname]) . "', \n";                  
                }
            }
			elseif ($irow['type'] == "R")
			{
				$i2query = "SELECT ".db_table_name("answers").".*, ".db_table_name("questions").".other FROM ".db_table_name("answers").", ".db_table_name("questions")."
				WHERE ".db_table_name("answers").".qid=".db_table_name("questions").".qid AND 
				 ".db_table_name("questions").".language = '{$language}' AND  ".db_table_name("answers").".language = '{$language}' AND
				".db_table_name("questions").".qid={$irow['qid']} AND ".db_table_name("questions").".sid=$surveyid ORDER BY ".db_table_name("answers").".sortorder, ".db_table_name("answers").".answer";
				$i2result = $connect->Execute($i2query);
				$i2count = $i2result->RecordCount();
				for ($x=1; $x<=$i2count; $x++)
				{
					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}$x";
					$updateqr .= db_quote_id($fieldname)." = '" . auto_escape($_POST["d$fieldname"]) . "', \n";
				}
			}
			elseif ($irow['type'] == ":" || $irow['type'] == ";")
			{
				$i2query = "SELECT ".db_table_name("answers").".*, ".db_table_name("questions").".other FROM ".db_table_name("answers").", ".db_table_name("questions")."
				WHERE ".db_table_name("answers").".qid=".db_table_name("questions").".qid AND 
				".db_table_name("questions").".language = '{$language}' AND  ".db_table_name("answers").".language = '{$language}' AND
				".db_table_name("questions").".qid={$irow['qid']} AND ".db_table_name("questions").".sid=$surveyid ORDER BY ".db_table_name("answers").".sortorder, ".db_table_name("answers").".answer";
				$i2result = db_execute_assoc($i2query);
    			$ab2query = "SELECT ".db_table_name('labels').".*
    			             FROM ".db_table_name('questions').", ".db_table_name('labels')."
    			             WHERE sid=$surveyid 
    						 AND ".db_table_name('labels').".lid=".db_table_name('questions').".lid
    			             AND ".db_table_name('questions').".language='".$language."'
    			             AND ".db_table_name('labels').".language='".$language."'
    			             AND ".db_table_name('questions').".qid=".$irow['qid']."
    			             ORDER BY ".db_table_name('labels').".sortorder, ".db_table_name('labels').".title";
    			$ab2result=db_execute_assoc($ab2query) or die("Couldn't get list of labels in createFieldMap function (case :)<br />$ab2query<br />".htmlspecialchars($connection->ErrorMsg()));
                $lset=array();
    			while($ab2row=$ab2result->FetchRow())
    			{
    			    $lset[]=$ab2row;
    			}
				while ($i2row = $i2result->FetchRow())
				{
				    foreach($lset as $ls)
				    {
    					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2row['code']}_{$ls['code']}";
    					if (isset($_POST[$fieldname])) {$thisvalue=$_POST[$fieldname];} else {$thisvalue="";}
    					$updateqr .= db_quote_id($fieldname)." = '" . auto_escape($thisvalue) . "', \n";
    					unset($thisvalue);
    				}
				}
			    unset($lset);
			}
			else
			{
				$i2query = "SELECT ".db_table_name("answers").".*, ".db_table_name("questions").".other FROM ".db_table_name("answers").", ".db_table_name("questions")."
				WHERE ".db_table_name("answers").".qid=".db_table_name("questions").".qid AND 
				".db_table_name("questions").".language = '{$language}' AND  ".db_table_name("answers").".language = '{$language}' AND
				".db_table_name("questions").".qid={$irow['qid']} AND ".db_table_name("questions").".sid=$surveyid ORDER BY ".db_table_name("answers").".sortorder, ".db_table_name("answers").".answer";
				$i2result = db_execute_assoc($i2query);
				$otherexists = "";
				while ($i2row = $i2result->FetchRow())
				{
					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2row['code']}";
					if (isset($_POST[$fieldname])) {$thisvalue=$_POST[$fieldname];} else {$thisvalue="";}
					if ($irow['type'] == 'K' && $thisvalue  == "")
					{
						$updateqr .= db_quote_id($fieldname)." = NULL, \n";
					}
					else
					{
						$updateqr .= db_quote_id($fieldname)." = '" . auto_escape($thisvalue) . "', \n";
					}
					if ($i2row['other'] == "Y") {$otherexists = "Y";}
					if ($irow['type'] == "P")
					{
						$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}{$i2row['code']}comment";
						$updateqr .= db_quote_id($fieldname)." = '" . auto_escape($_POST[$fieldname]) . "', \n";
					}
					unset($thisvalue);
				}
				if ($otherexists == "Y")
				{
					$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}other";
					if (isset($_POST[$fieldname])) {$thisvalue=$_POST[$fieldname];} else {$thisvalue="";}
					$updateqr .= db_quote_id($fieldname)." = '" . auto_escape($thisvalue) . "', \n";
					if ($irow['type'] == "P")
					{
						$fieldname = "{$irow['sid']}X{$irow['gid']}X{$irow['qid']}othercomment";
						if (isset($_POST[$fieldname])) {$thisvalue=$_POST[$fieldname];} else {$thisvalue="";}
						$updateqr .= db_quote_id($fieldname)." = '" . auto_escape($thisvalue) . "', \n";
					}
					unset($thisvalue);
				}
			}
		}
		$updateqr = substr($updateqr, 0, -3);
		if (isset($_POST['datestamp']) && $_POST['datestamp']) {$updateqr .= ", datestamp='".auto_escape($_POST['datestamp'])."'";}
		if (isset($_POST['ipaddr']) && $_POST['ipaddr']) {$updateqr .= ", ipaddr=".auto_escape($_POST['ipaddr'])."'";}
		if (isset($_POST['token']) && $_POST['token']) {$updateqr .= ", token='".auto_escape($_POST['token'])."'";}
		if (isset($_POST['language']) && $_POST['language']) {$updateqr .= ", startlanguage='".auto_escape($_POST['language'])."'";}
		if (isset($_POST['submitdate']) && $_POST['submitdate'] && $_POST['submitdate'] != "N") {$updateqr .= ", submitdate='".auto_escape($_POST['submitdate'])."'";}
		if (isset($_POST['submitdate']) && $_POST['submitdate'] == "N") {$updateqr .= ", submitdate=NULL";}
		$updateqr .= " WHERE id=$id";

		$updateres = $connect->Execute($updateqr) or safe_die("Update failed:<br />\n" . $connect->ErrorMsg() . "<br />$updateqr");
		$thissurvey=getSurveyInfo($surveyid);
		while (ob_get_level() > 0) {
			ob_end_flush();
		}
		$dataentryoutput .= "<font class='successtitle'><strong>".$clang->gT("Success")."</strong></font><br />\n"
		.$clang->gT("Record has been updated.")."<br /><br />\n"
		."<a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=id&amp;id=$id'>".$clang->gT("View This Record")."</a>\n<br />\n"
		."<a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=all'>".$clang->gT("Browse Responses")."</a><br />\n"
		."</td></tr></table>\n"
		."</body>\n";
	}

	elseif ($subaction == "delete")
	{
		$thissurvey=getSurveyInfo($surveyid);
		$dataentryoutput .= "<table width='450' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr><td colspan='2' height='4'><strong>"
		.$clang->gT("Data Entry")."</strong></td></tr>\n"
		."\t<tr><td align='center'>\n"
		."\t\t\t<strong>".$thissurvey['name']."</strong><br />\n"
		."\t\t\t".$thissurvey['description']."\n"
		."\t\t</td>\n"
		."\t</tr>\n";
		$delquery = "DELETE FROM $surveytable WHERE id=$id";
		$dataentryoutput .= "\t<tr>\n";
		$delresult = $connect->Execute($delquery) or safe_die ("Couldn't delete record $id<br />\n".$connect->ErrorMsg());
		$dataentryoutput .= "\t\t<td align='center'><br /><strong>".$clang->gT("Record Deleted")." (ID: $id)</strong><br /><br />\n"
		."\t\t\t<a href='$scriptname?action=browse&amp;sid=$surveyid&amp;subaction=all'>".$clang->gT("Browse Responses")."</a>\n"
		."\t\t</td>\n"
		."\t</tr>\n"
		."</table>\n"
		."</body>\n";
	}
	else
	{
		$slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
		$baselang = GetBaseLanguageFromSurveyID($surveyid);
		array_unshift($slangs,$baselang);
		
		if(!isset($_GET['language']) || !in_array($_GET['language'],$slangs))
		{
			$language = $baselang;
			$blang = $clang;
		} else {
			$blang = new limesurvey_lang($_GET['language']);
			$language = $_GET['language'];
		}
		
		$langlistbox = languageDropdown($surveyid,$language);
		$thissurvey=getSurveyInfo($surveyid);
		//This is the default, presenting a blank dataentry form
		$fieldmap=createFieldMap($surveyid);
		// PRESENT SURVEY DATAENTRY SCREEN
		$dataentryoutput .= $surveyoptions
		."<table><tr><td></td></tr></table>";

		$dataentryoutput .= "<form action='$scriptname?action=dataentry' name='addsurvey' method='post' id='addsurvey'>\n"
		."<table width='100%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr><td colspan='3' height='4' class='header'><strong>"
		.$clang->gT("Data Entry")."</strong></td></tr>\n"
		."\t<tr>\n"
		."\t\t<td align='left'>";
		if (count(GetAdditionalLanguagesFromSurveyID($surveyid))>0) {$dataentryoutput.=$langlistbox;}
		$dataentryoutput .= "</td><td colspan='2' align='center'>\n"
		."\t\t\t<strong>".$thissurvey['name']."</strong>\n"
		."\t\t\t<br />".$thissurvey['description']."\n"
		."\t\t</td>\n"
		."\t</tr>\n";

		if (tokenTableExists($thissurvey['sid'])) //Give entry field for token id 
		{
			$dataentryoutput .= "\t<tr>\n"
			."\t\t<td valign='top' width='1%'></td>\n"
			."\t\t<td valign='top' align='right' width='30%'><font color='red'>*</font><strong>".$blang->gT("Token").":</strong></td>\n"
			."\t\t<td valign='top'  align='left' style='padding-left: 20px'>\n"
			."\t\t\t<input type='text' id='token' name='token' onkeyup='activateSubmit(this);'/>\n"
			."\t\t</td>\n"
			."\t</tr>\n";

			$dataentryoutput .= "\n"
			. "\t<script type=\"text/javascript\"><!-- \n"
			. "\tfunction activateSubmit(me)\n"
			. "\t{"
			. "\t\tif (me.value != '')"
			. "\t\t{\n"
			. "\t\t\tdocument.getElementById('submitdata').disabled = false;\n"
			. "\t\t}\n"
			. "\t\telse\n"
			. "\t\t{\n"
			. "\t\t\tdocument.getElementById('submitdata').disabled = true;\n"
			. "\t\t}\n"
			. "\t}"
			. "\t//--></script>\n";
			
		}
		if ($thissurvey['datestamp'] == "Y") //Give datestampentry field
		{
            $localtimedate=date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i", $timeadjust);        
			$dataentryoutput .= "\t<tr>\n"
			."\t\t<td valign='top' width='1%'></td>\n"
			."\t\t<td valign='top' align='right' width='30%'><strong>"
			.$blang->gT("Datestamp").":</strong></td>\n"
			."\t\t<td valign='top'  align='left' style='padding-left: 20px'>\n"
			."\t\t\t<input type='text' name='datestamp' value='$localtimedate' />\n"
			."\t\t</td>\n"
			."\t</tr>\n";
		}
		if ($thissurvey['ipaddr'] == "Y") //Give ipaddress field
		{
			$dataentryoutput .= "\t<tr>\n"
			."\t\t<td valign='top' width='1%'></td>\n"
			."\t\t<td valign='top' align='right' width='30%'><strong>"
			.$blang->gT("IP-Address").":</strong></td>\n"
			."\t\t<td valign='top'  align='left' style='padding-left: 20px'>\n"
			."\t\t\t<input type='text' name='ipaddr' value='NULL' />\n"
			."\t\t</td>\n"
			."\t</tr>\n";
		}
		// SURVEY NAME AND DESCRIPTION TO GO HERE
		$degquery = "SELECT * FROM ".db_table_name("groups")." WHERE sid=$surveyid AND language='{$language}' ORDER BY ".db_table_name("groups").".group_order";
		$degresult = db_execute_assoc($degquery);
		// GROUP NAME
		while ($degrow = $degresult->FetchRow())
		{
			$deqquery = "SELECT * FROM ".db_table_name("questions")." WHERE sid=$surveyid AND gid={$degrow['gid']} AND language='{$language}'";
			$deqresult = db_execute_assoc($deqquery);
			$dataentryoutput .= "\t<tr>\n"
			."\t\t<td colspan='3' align='center'><strong>{$degrow['group_name']}</strong></td>\n"
			."\t</tr>\n";
			$gid = $degrow['gid'];

			//Alternate bgcolor for different groups
			$bgc="";
			if ($bgc == "evenrow") {$bgc = "oddrow";}
			else {$bgc = "evenrow";}
			if (!$bgc) {$bgc = "evenrow";}

			$deqrows = array(); //Create an empty array in case FetchRow does not return any rows
			while ($deqrow = $deqresult->FetchRow()) {$deqrows[] = $deqrow;} //Get table output into array

			// Perform a case insensitive natural sort on group name then question title of a multidimensional array
			usort($deqrows, 'CompareGroupThenTitle');

			foreach ($deqrows as $deqrow)
			{
				//GET ANY CONDITIONS THAT APPLY TO THIS QUESTION
				$explanation = ""; //reset conditions explanation
				$s=0;
				$scenarioquery="SELECT DISTINCT scenario FROM ".db_table_name("conditions")." WHERE ".db_table_name("conditions").".qid={$deqrow['qid']} ORDER BY scenario";
				$scenarioresult=db_execute_assoc($scenarioquery);
				while ($scenariorow=$scenarioresult->FetchRow())
				{
					if ($s == 0 && $scenarioresult->RecordCount() > 1) { $explanation .= " <br />-------- <i>Scenario {$scenariorow['scenario']}</i> --------<br />";}
					if ($s > 0) { $explanation .= " <br />-------- <i>".$clang->gT("OR")." Scenario {$scenariorow['scenario']}</i> --------<br />";}

					$x=0;
					$distinctquery="SELECT DISTINCT cqid, ".db_table_name("questions").".title FROM ".db_table_name("conditions").", ".db_table_name("questions")." WHERE ".db_table_name("conditions").".cqid=".db_table_name("questions").".qid AND ".db_table_name("conditions").".qid={$deqrow['qid']} AND ".db_table_name("conditions").".scenario={$scenariorow['scenario']} ORDER BY cqid";
					$distinctresult=db_execute_assoc($distinctquery);

					while ($distinctrow=$distinctresult->FetchRow())
					{
						if ($x > 0) {$explanation .= " <i>".$blang->gT("AND")."</i><br />";}
						$conquery="SELECT cid, cqid, cfieldname, ".db_table_name("questions").".title, ".db_table_name("questions").".lid, ".db_table_name("questions").".question, value, ".db_table_name("questions").".type FROM ".db_table_name("conditions").", ".db_table_name("questions")." WHERE ".db_table_name("conditions").".cqid=".db_table_name("questions").".qid AND ".db_table_name("conditions").".cqid={$distinctrow['cqid']} AND ".db_table_name("conditions").".qid={$deqrow['qid']} AND ".db_table_name("conditions").".scenario={$scenariorow['scenario']}";
						$conresult=db_execute_assoc($conquery);
						while ($conrow=$conresult->FetchRow())
						{
							switch($conrow['type'])
							{
								case "Y":
									switch ($conrow['value'])
									{
										case "Y": $conditions[]=$blang->gT("Yes"); break;
										case "N": $conditions[]=$blang->gT("No"); break;
									}
								break;
								case "G":
									switch($conrow['value'])
									{
										case "M": $conditions[]=$blang->gT("Male"); break;
										case "F": $conditions[]=$blang->gT("Female"); break;
									} // switch
								break;
								case "A":
									case "B":
									$conditions[]=$conrow['value'];
								break;
								case "C":
									switch($conrow['value'])
									{
										case "Y": $conditions[]=$blang->gT("Yes"); break;
										case "U": $conditions[]=$blang->gT("Uncertain"); break;
										case "N": $conditions[]=$blang->gT("No"); break;
									} // switch
								break;
								case "1":								
									$value=substr($conrow['cfieldname'], strpos($conrow['cfieldname'], "X".$conrow['cqid'])+strlen("X".$conrow['cqid']), strlen($conrow['cfieldname']));
								$fquery = "SELECT * FROM ".db_table_name("labels")."\n"
									. "WHERE lid='{$conrow['lid']}'\n and language='$language' "
									. "AND code='{$conrow['value']}'";
								$fresult=db_execute_assoc($fquery) or safe_die("$fquery<br />".$connect->ErrorMsg());
								while($frow=$fresult->FetchRow())
								{
									$postans=$frow['title'];
									$conditions[]=$frow['title'];
								} // while
								break;

								case "E":
									switch($conrow['value'])
									{
										case "I": $conditions[]=$blang->gT("Increase"); break;
										case "D": $conditions[]=$blang->gT("Decrease"); break;
										case "S": $conditions[]=$blang->gT("Same"); break;
									}
								break;
								case "F":
									case "H":
								default:
									$value=substr($conrow['cfieldname'], strpos($conrow['cfieldname'], "X".$conrow['cqid'])+strlen("X".$conrow['cqid']), strlen($conrow['cfieldname']));
									$fquery = "SELECT * FROM ".db_table_name("labels")."\n"
										. "WHERE lid='{$conrow['lid']}'\n and language='$language' "
										. "AND code='{$conrow['value']}'";
									$fresult=db_execute_assoc($fquery) or safe_die("$fquery<br />".$connect->ErrorMsg());
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

								case "1":
									$ansquery="SELECT answer FROM ".db_table_name("answers")." WHERE qid='{$conrow['cqid']}' AND code='{$conrow['value']}' AND language='{$baselang}'";
								    $ansresult=db_execute_assoc($ansquery);
								    while ($ansrow=$ansresult->FetchRow())
								    {
									    $conditions[]=$ansrow['answer'];
								    }
								    $operator=$clang->gT("OR");
								    if (isset($conditions)) $conditions = array_unique($conditions);
								    break;

								case "A":
									case "B":
									case "C":
									case "E":
									case "F":
									case "H":
									case ":":
									case ";":
									$thiscquestion=arraySearchByKey($conrow['cfieldname'], $fieldmap, "fieldname");
								$ansquery="SELECT answer FROM ".db_table_name("answers")." WHERE qid='{$conrow['cqid']}' AND code='{$thiscquestion[0]['aid']}' AND language='{$language}'";
								$ansresult=db_execute_assoc($ansquery);
								$i=0;
								while ($ansrow=$ansresult->FetchRow())
								{
									if (isset($conditions) && count($conditions) > 0)
									{
										$conditions[sizeof($conditions)-1]="(".$ansrow['answer'].") : ".end($conditions);
									}
								}
								$operator=$blang->gT("AND");	// this is a dirty, DIRTY fix but it works since only array questions seem to be ORd
								break;
								default:
								$ansquery="SELECT answer FROM ".db_table_name("answers")." WHERE qid='{$conrow['cqid']}' AND code='{$conrow['value']}' AND language='{$language}'";
								$ansresult=db_execute_assoc($ansquery);
								while ($ansrow=$ansresult->FetchRow())
								{
									$conditions[]=$ansrow['answer'];
								}
								$operator=$blang->gT("OR");
								if (isset($conditions)) $conditions = array_unique($conditions);
								break;
							}
						}
						if (isset($conditions) && count($conditions) > 1)
						{
							$conanswers = "'".implode("' ".$operator." '", $conditions)."'";
							$explanation .= " -" . str_replace("{ANSWER}", $conanswers, $blang->gT("to question {QUESTION}, you answered {ANSWER}"));
						}
						else
						{
							if(empty($conditions[0])) $conditions[0] = $blang->gT("No Answer");
							$explanation .= " -" . str_replace("{ANSWER}", "'{$conditions[0]}'", $blang->gT("to question {QUESTION}, you answered {ANSWER}"));
						}
						unset($conditions);
						$explanation = str_replace("{QUESTION}", "'{$distinctrow['title']}$answer_section'", $explanation);
						$x++;
					}
					$s++;
				}
				if ($explanation)
				{
                    if ($bgc == "evenrow") {$bgc = "oddrow";} else {$bgc = "evenrow";} //Do no alternate on explanation row
					$explanation = "<font size='1'>[".$blang->gT("Only answer this if the following conditions are met:")."]<br />$explanation\n";
					$dataentryoutput .= "<tr bgcolor='#FFEEEE'><td colspan='3' align='left'>$explanation</td></tr>\n";
				}

				//END OF GETTING CONDITIONS

				$qid = $deqrow['qid'];
				$fieldname = "$surveyid"."X"."$gid"."X"."$qid";
				$dataentryoutput .= "\t<tr class='$bgc'>\n"
				."\t\t<td valign='top' width='1%'><font size='1'>{$deqrow['title']}</font></td>\n"
				."\t\t<td valign='top' align='right' width='30%'>";
				if ($deqrow['mandatory']=="Y") //question is mandatory
				{
					$dataentryoutput .= "<font color='red'>*</font>";
				}
				$dataentryoutput .= "<strong>{$deqrow['question']}</strong></td>\n"
				."\t\t<td valign='top'  align='left' style='padding-left: 20px'>\n";
				//DIFFERENT TYPES OF DATA FIELD HERE
				if ($deqrow['help'])
				{
					$hh = addcslashes($deqrow['help'], "\0..\37'\""); //Escape ASCII decimal 0-32 plus single and double quotes to make JavaScript happy.
					$hh = htmlspecialchars($hh, ENT_QUOTES); //Change & " ' < > to HTML entities to make HTML happy.
					$dataentryoutput .= "\t\t\t<img src='$imagefiles/help.gif' alt='".$blang->gT("Help about this question")."' align='right' onclick=\"javascript:alert('Question {$deqrow['title']} Help: $hh')\" />\n";
				}
				switch($deqrow['type'])
				{
					case "5": //5 POINT CHOICE radio-buttons
					$dataentryoutput .= "\t\t\t<select name='$fieldname'>\n"
					."\t\t\t\t<option value=''>".$blang->gT("No answer")."</option>\n";
					for ($x=1; $x<=5; $x++)
					{
						$dataentryoutput .= "\t\t\t\t<option value='$x'>$x</option>\n";
					}
					$dataentryoutput .= "\t\t\t</select>\n";
					break;
					case "D": //DATE
					$dataentryoutput .= "\t\t\t<input type='text' name='$fieldname' size='10' />\n";
					break;
					case "G": //GENDER drop-down list
					$dataentryoutput .= "\t\t\t<select name='$fieldname'>\n"
					."\t\t\t\t<option selected='selected' value=''>".$blang->gT("Please choose")."..</option>\n"
					."\t\t\t\t<option value='F'>".$blang->gT("Female")."</option>\n"
					."\t\t\t\t<option value='M'>".$blang->gT("Male")."</option>\n"
					."\t\t\t</select>\n";
					break;
					case "Q": //MULTIPLE SHORT TEXT
//					case "^": //Slider
					case "K":
					$deaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$language}' ORDER BY sortorder, answer";
					$dearesult = db_execute_assoc($deaquery);
					$dataentryoutput .= "\t\t\t<table>\n";
					while ($dearow = $dearesult->FetchRow())
					{
						$dataentryoutput .= "\t\t\t\t<tr><td align='right'>"
						.$dearow['answer']
						."</td>\n"
						."\t\t\t\t\t<td><input type='text' name='$fieldname{$dearow['code']}' /></td>\n"
						."\t\t\t\t</tr>\n";
					}
					$dataentryoutput .= "\t\t\t</table>\n";
					break;
					case "W": //Flexible List drop-down/radio-button
					case "Z":
						$deaquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$deqrow['lid']} ORDER BY sortorder, code";
						$dearesult = db_execute_assoc($deaquery);
						$dataentryoutput .= "\t\t\t<select name='$fieldname'>\n";
						$dataentryoutput .= "\t\t\t\t<option selected='selected' value=''>".$blang->gT("Please choose")."..</option>\n";
						while ($dearow = $dearesult->FetchRow())
						{
							$dataentryoutput .= "\t\t\t\t<option value='{$dearow['code']}'";
							$dataentryoutput .= ">{$dearow['title']}</option>\n";
						}

						$oquery="SELECT other FROM ".db_table_name("questions")." WHERE qid={$deqrow['qid']} AND language='{$language}'";
						$oresult=db_execute_assoc($oquery) or safe_die("Couldn't get other for list question<br />".$oquery."<br />".$connect->ErrorMsg());
						while($orow = $oresult->FetchRow())
						{
							$fother=$orow['other'];
						}
						if ($fother == "Y")
						{
							$dataentryoutput .= "<option value='-oth-'>".$blang->gT("Other")."</option>\n";
						}
						$dataentryoutput .= "\t\t\t</select>\n";
						if ($fother == "Y")
						{
							$dataentryoutput .= "\t\t\t"
							.$blang->gT("Other").":"
							."<input type='text' name='{$fieldname}other' value='' />\n";
						}
						break;
					case "1": // multi scale^
						$deaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$baselang}' ORDER BY sortorder, answer";
						$dearesult = db_execute_assoc($deaquery);
                        $dataentryoutput .='<table><tr><td></td><th>'.$clang->gT('Label 1').'</th><th>'.$clang->gT('Label 2').'</th></tr>';

						while ($dearow = $dearesult->FetchRow())
						{
							// first scale
							$delquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$deqrow['lid']} ORDER BY sortorder, lid, code";
							$delresult = db_execute_assoc($delquery);
							$dataentryoutput .= "<tr><td>{$dearow['answer']}</td><td>";
                            $dataentryoutput .= "<select name='$fieldname{$dearow['code']}#0'>\n";
							$dataentryoutput .= "<option selected='selected' value=''>".$clang->gT("Please choose...")."</option>\n";
							while ($delrow = $delresult->FetchRow())
							{
								$dataentryoutput .= "<option value='{$delrow['code']}'";
								$dataentryoutput .= ">{$delrow['title']}</option>\n";
							}
							// second scale
                            $dataentryoutput .= "</select></td>\n";
							$delquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$deqrow['lid1']} ORDER BY sortorder, lid, code";
							$delresult = db_execute_assoc($delquery);
							$dataentryoutput .= "<td>";
                            $dataentryoutput .="<select name='$fieldname{$dearow['code']}#1'>\n";
							$dataentryoutput .= "<option selected='selected' value=''>".$clang->gT("Please choose...")."</option>\n";
							while ($delrow = $delresult->FetchRow())
							{
								$dataentryoutput .= "<option value='{$delrow['code']}'";
								$dataentryoutput .= ">{$delrow['title']}</option>\n";
							}
                            $dataentryoutput .= "</select></td></tr>\n"; 
						}
						$oquery="SELECT other FROM ".db_table_name("questions")." WHERE qid={$deqrow['qid']} AND language='{$baselang}'";
						$oresult=db_execute_assoc($oquery) or safe_die("Couldn't get other for list question<br />".$oquery."<br />".$connect->ErrorMsg());
						while($orow = $oresult->FetchRow())
						{
							$fother=$orow['other'];
						}
						if ($fother == "Y")
						{
							$dataentryoutput .= "<option value='-oth-'>".$clang->gT("Other")."</option>\n";
						}
						// $dataentryoutput .= "\t\t\t</select>vvv\n";
						if ($fother == "Y")
						{
							$dataentryoutput .= "\t\t\t"
							.$clang->gT("Other").":"
							."<input type='text' name='{$fieldname}other' value='' />\n";
						}
                         $dataentryoutput .= "</tr></table>"; 
						break;
					case "L": //LIST drop-down/radio-button list
					case "!":
						
						$qidattributes=getQuestionAttributes($deqrow['qid']);
						if ($optCategorySeparator = arraySearchByKey('category_separator', $qidattributes, 'attribute', 1))
						{
							$optCategorySeparator = $optCategorySeparator['value'];
						}
						else
						{
							unset($optCategorySeparator);
						}
						$defexists="";
						$deaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$language}' ORDER BY sortorder, answer";
						$dearesult = db_execute_assoc($deaquery);
						$dataentryoutput .= "\t\t\t<select name='$fieldname'>\n";
						$datatemp='';
						if (!isset($optCategorySeparator))
						{						
							while ($dearow = $dearesult->FetchRow())
							{
								$datatemp .= "\t\t\t\t<option value='{$dearow['code']}'";
								if ($dearow['default_value'] == "Y") {$datatemp .= " selected='selected'"; $defexists = "Y";}
								$datatemp .= ">{$dearow['answer']}</option>\n";
							}
						}
						else
						{
							$defaultopts = array();
							$optgroups = array();
							while ($dearow = $dearesult->FetchRow())
							{
								list ($categorytext, $answertext) = explode($optCategorySeparator,$dearow['answer']);
								if ($categorytext == '')
								{
									$defaultopts[] = array ( 'code' => $dearow['code'], 'answer' => $answertext, 'default_value' => $dearow['default_value']);
								}
								else
								{
									$optgroups[$categorytext][] = array ( 'code' => $dearow['code'], 'answer' => $answertext, 'default_value' => $dearow['default_value']);
								}	
							}
							foreach ($optgroups as $categoryname => $optionlistarray)
							{
								$datatemp .= "\t\t\t\t<optgroup class=\"dropdowncategory\" label=\"".$categoryname."\">\n";
								foreach ($optionlistarray as $optionarray)
								{
									$datatemp .= "\t\t\t\t\t<option value='{$optionarray['code']}'";
									if ($optionarray['default_value'] == "Y") {$datatemp .= " selected='selected'"; $defexists = "Y";}
									$datatemp .= ">{$optionarray['answer']}</option>\n";
								}
								$datatemp .= "\t\t\t\t</optgroup>\n";
							}
							foreach ($defaultopts as $optionarray)
							{
								$datatemp .= "\t\t\t\t\t<option value='{$optionarray['code']}'";
								if ($optionarray['default_value'] == "Y") {$datatemp .= " selected='selected'"; $defexists = "Y";}
								$datatemp .= ">{$optionarray['answer']}</option>\n";
							}
						}

						if ($defexists=="") {$dataentryoutput .= "\t\t\t\t<option selected='selected' value=''>".$blang->gT("Please choose")."..</option>\n".$datatemp;}
						else {$dataentryoutput .=$datatemp;}

						$oquery="SELECT other FROM ".db_table_name("questions")." WHERE qid={$deqrow['qid']} AND language='{$language}'";
						$oresult=db_execute_assoc($oquery) or safe_die("Couldn't get other for list question<br />".$oquery."<br />".$connect->ErrorMsg());
						while($orow = $oresult->FetchRow())
						{
							$fother=$orow['other'];
						}
						if ($fother == "Y")
						{
							$dataentryoutput .= "<option value='-oth-'>".$blang->gT("Other")."</option>\n";
						}
						$dataentryoutput .= "\t\t\t</select>\n";
						if ($fother == "Y")
						{
							$dataentryoutput .= "\t\t\t"
							.$blang->gT("Other").":"
							."<input type='text' name='{$fieldname}other' value='' />\n";
						}
						break;
					case "O": //LIST WITH COMMENT drop-down/radio-button list + textarea
					$defexists="";
					$deaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$language}' ORDER BY sortorder, answer";
					$dearesult = db_execute_assoc($deaquery);
					$dataentryoutput .= "\t\t\t<select name='$fieldname'>\n";
					$datatemp='';
					while ($dearow = $dearesult->FetchRow())
					{
						$datatemp .= "\t\t\t\t<option value='{$dearow['code']}'";
						if ($dearow['default_value'] == "Y") {$datatemp .= " selected='selected'"; $defexists = "Y";}
						$datatemp .= ">{$dearow['answer']}</option>\n";
					}
					if ($defexists=="") {$dataentryoutput .= "\t\t\t\t<option selected='selected' value=''>".$blang->gT("Please choose")."..</option>\n".$datatemp;}
					else  {$dataentryoutput .= $datatemp;}
					$dataentryoutput .= "\t\t\t</select>\n"
					."\t\t\t<br />".$blang->gT("Comment").":<br />\n"
					."\t\t\t<textarea cols='40' rows='5' name='$fieldname"
					."comment'></textarea>\n";
					break;
					case "R": //RANKING TYPE QUESTION
					$thisqid=$deqrow['qid'];
					$ansquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid=$thisqid AND language='{$language}' ORDER BY sortorder, answer";
					$ansresult = db_execute_assoc($ansquery);
					$anscount = $ansresult->RecordCount();
					$dataentryoutput .= "\t\t\t<script type='text/javascript'>\n"
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
						$ranklist .= "\t\t\t\t\t\t&nbsp;<font color='#000080'>$i:&nbsp;<input class='ranklist' type='text' name='RANK$i' id='RANK_$thisqid$i'";
						if (isset($myfname) && $_SESSION[$myfname])
						{
							$ranklist .= " value='";
							$ranklist .= $thistext;
							$ranklist .= "'";
						}
						$ranklist .= " onFocus=\"this.blur()\"  />\n";
						$ranklist .= "\t\t\t\t\t\t<input type='hidden' id='d$fieldname$i' name='d$fieldname$i' value='";
						$chosen[]=""; //create array
						if (isset($myfname) && $_SESSION[$myfname])
						{
							$ranklist .= $thiscode;
							$chosen[]=array($thiscode, $thistext);
						}
						$ranklist .= "' /></font>\n";
						$ranklist .= "\t\t\t\t\t\t<img src='$imagefiles/cut.gif' alt='".$blang->gT("Remove this item")."' title='".$blang->gT("Remove this item")."' ";
						if (!isset($existing) || $i != $existing)
						{
							$ranklist .= "style='display:none'";
						}
						$mfn=$fieldname.$i;
						$ranklist .= " id='cut_$thisqid$i' onclick=\"deletethis_$thisqid(document.addsurvey.RANK_$thisqid$i.value, document.addsurvey.d$fieldname$i.value, document.addsurvey.RANK_$thisqid$i.id, this.id)\"><br />\n\n";
					}
					if (!isset($choicelist)) {$choicelist="";}
					$choicelist .= "\t\t\t\t\t\t<select size='$anscount' class='choicelist' name='CHOICES' id='CHOICES_$thisqid' onclick=\"rankthis_$thisqid(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" >\n";
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

					$dataentryoutput .= "\t\t\t<table align='left' border='0' cellspacing='5'>\n"
					."\t\t\t\t<tr>\n"
					."\t\t\t\t\t<td align='left' valign='top' width='200'>\n"
					."\t\t\t\t\t\t<strong>"
					.$blang->gT("Your Choices").":</strong><br />\n"
					.$choicelist
					."\t\t\t\t\t</td>\n"
					."\t\t\t\t\t<td align='left'>\n"
					."\t\t\t\t\t\t<strong>"
					.$blang->gT("Your Ranking").":</strong><br />\n"
					.$ranklist
					."\t\t\t\t\t</td>\n"
					."\t\t\t\t</tr>\n"
					."\t\t\t</table>\n"
					."\t\t\t<input type='hidden' name='multi' value='$anscount' />\n"
					."\t\t\t<input type='hidden' name='lastfield' value='";
					if (isset($multifields)) {$dataentryoutput .= $multifields;}
					$dataentryoutput .= "' />\n";
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
					$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$language}' ORDER BY sortorder, answer";
					$mearesult = db_execute_assoc($meaquery);
					$meacount = $mearesult->RecordCount();
					if ($deqrow['other'] == "Y") {$meacount++;}
					if ($dcols > 0 && $meacount >= $dcols)
					{
						$width=sprintf("%0d", 100/$dcols);
						$maxrows=ceil(100*($meacount/$dcols)/100); //Always rounds up to nearest whole number
						$divider=" </td>\n <td valign='top' width='$width%' nowrap='nowrap'>";
						$upto=0;
						$dataentryoutput .= "<table class='question'><tr>\n <td valign='top' width='$width%' nowrap='nowrap'>";
						while ($mearow = $mearesult->FetchRow())
						{
							if ($upto == $maxrows)
							{
								$dataentryoutput .= $divider;
								$upto=0;
							}
							$dataentryoutput .= "\t\t\t<input type='checkbox' class='checkboxbtn' name='$fieldname{$mearow['code']}' id='answer$fieldname{$mearow['code']}' value='Y'";
							if ($mearow['default_value'] == "Y") {$dataentryoutput .= " checked";}
							$dataentryoutput .= " /><label for='$fieldname{$mearow['code']}'>{$mearow['answer']}</label><br />\n";
							$upto++;
						}
						if ($deqrow['other'] == "Y")
						{
							$dataentryoutput .= "\t\t\t".$blang->gT("Other")." <input type='text' name='$fieldname";
							$dataentryoutput .= "other' />\n";
						}
						$dataentryoutput .= "</td></tr></table>\n";
						//Let's break the presentation into columns.
					}
					else
					{
						while ($mearow = $mearesult->FetchRow())
						{
							$dataentryoutput .= "\t\t\t<input type='checkbox' class='checkboxbtn' name='$fieldname{$mearow['code']}' id='answer$fieldname{$mearow['code']}' value='Y'";
							if ($mearow['default_value'] == "Y") {$dataentryoutput .= " checked";}
							$dataentryoutput .= " /><label for='$fieldname{$mearow['code']}'>{$mearow['answer']}</label><br />\n";
						}
						if ($deqrow['other'] == "Y")
						{
							$dataentryoutput .= "\t\t\t".$blang->gT("Other")." <input type='text' name='$fieldname";
							$dataentryoutput .= "other' />\n";
						}
					}
					break;
					case "I": //Language Switch
                    $slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                    $sbaselang = GetBaseLanguageFromSurveyID($surveyid);
                    array_unshift($slangs,$sbaselang);

                    $dataentryoutput.= "<select name='{$fieldname}'>\n";
					$dataentryoutput .= "\t\t\t\t<option value=''";
					$dataentryoutput .= " selected='selected'";
					$dataentryoutput .= ">".$blang->gT("Please choose")."..</option>\n";

                    foreach ($slangs as $lang)
                       	{
                            $dataentryoutput.="<option value='{$lang}'";
                       		//if ($lang == $idrow[$fnames[$i][0]]) {$dataentryoutput .= " selected='selected'";}
                            $dataentryoutput.=">".getLanguageNameFromCode($lang,false)."</option>\n";
                       	}
                    $dataentryoutput .= "</select>";
					break;
					case "P": //MULTIPLE OPTIONS WITH COMMENTS checkbox + text
					$dataentryoutput .= "<table border='0'>\n";
					$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult = db_execute_assoc($meaquery);
					while ($mearow = $mearesult->FetchRow())
					{
						$dataentryoutput .= "\t<tr>\n";
						$dataentryoutput .= "\t\t<td>\n";
						$dataentryoutput .= "\t\t\t<input type='checkbox' class='checkboxbtn' name='$fieldname{$mearow['code']}' value='Y'";
						if ($mearow['default_value'] == "Y") {$dataentryoutput .= " checked";}
						$dataentryoutput .= " />{$mearow['answer']}\n";
						$dataentryoutput .= "\t\t</td>\n";
						//This is the commments field:
						$dataentryoutput .= "\t\t<td>\n";
						$dataentryoutput .= "\t\t\t<input type='text' name='$fieldname{$mearow['code']}comment' size='50' />\n";
						$dataentryoutput .= "\t\t</td>\n";
						$dataentryoutput .= "\t</tr>\n";
					}
					if ($deqrow['other'] == "Y")
					{
						$dataentryoutput .= "\t<tr>\n";
						$dataentryoutput .= "\t\t<td  align='left'><label>".$blang->gT("Other").":</label>\n";
						$dataentryoutput .= "\t\t\t<input type='text' name='$fieldname"."other' size='10'/>\n";
						$dataentryoutput .= "\t\t</td>\n";
						$dataentryoutput .= "\t\t<td align='left'>\n";
						$dataentryoutput .= "\t\t\t<input type='text' name='$fieldname"."othercomment' size='50'/>\n";
						$dataentryoutput .= "\t\t</td>\n";
						$dataentryoutput .= "\t</tr>\n";
					}
					$dataentryoutput .= "</table>\n";
					break;
					case "N": //NUMERICAL TEXT
					$dataentryoutput .= "\t\t\t<input type='text' name='$fieldname' onkeypress=\"return goodchars(event,'0123456789.,')\" />";
					break;
					case "S": //SHORT FREE TEXT
					$dataentryoutput .= "\t\t\t<input type='text' name='$fieldname' />\n";
					break;
					case "T": //LONG FREE TEXT
					$dataentryoutput .= "\t\t\t<textarea cols='40' rows='5' name='$fieldname'></textarea>\n";
					break;
					case "U": //LONG FREE TEXT
					$dataentryoutput .= "\t\t\t<textarea cols='50' rows='70' name='$fieldname'></textarea>\n";
					break;
					case "Y": //YES/NO radio-buttons
					$dataentryoutput .= "\t\t\t<select name='$fieldname'>\n";
					$dataentryoutput .= "\t\t\t\t<option selected='selected' value=''>".$blang->gT("Please choose")."..</option>\n";
					$dataentryoutput .= "\t\t\t\t<option value='Y'>".$blang->gT("Yes")."</option>\n";
					$dataentryoutput .= "\t\t\t\t<option value='N'>".$blang->gT("No")."</option>\n";
					$dataentryoutput .= "\t\t\t</select>\n";
					break;
					case "A": //ARRAY (5 POINT CHOICE) radio-buttons
					$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$language}' ORDER BY sortorder, answer";
					$mearesult = db_execute_assoc($meaquery);
					$dataentryoutput .= "<table>\n";
					while ($mearow = $mearesult->FetchRow())
					{
						$dataentryoutput .= "\t<tr>\n";
						$dataentryoutput .= "\t\t<td align='right'>{$mearow['answer']}</td>\n";
						$dataentryoutput .= "\t\t<td>\n";
						$dataentryoutput .= "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
						$dataentryoutput .= "\t\t\t\t<option value=''>".$blang->gT("Please choose")."..</option>\n";
						for ($i=1; $i<=5; $i++)
						{
							$dataentryoutput .= "\t\t\t\t<option value='$i'>$i</option>\n";
						}
						$dataentryoutput .= "\t\t\t</select>\n";
						$dataentryoutput .= "\t\t</td>\n";
						$dataentryoutput .= "\t</tr>\n";
					}
					$dataentryoutput .= "</table>\n";
					break;
					case "B": //ARRAY (10 POINT CHOICE) radio-buttons
					$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} AND language='{$language}' ORDER BY sortorder, answer";
					$mearesult = db_execute_assoc($meaquery);
					$dataentryoutput .= "<table>\n";
					while ($mearow = $mearesult->FetchRow())
					{
						$dataentryoutput .= "\t<tr>\n";
						$dataentryoutput .= "\t\t<td align='right'>{$mearow['answer']}</td>\n";
						$dataentryoutput .= "\t\t<td>\n";
						$dataentryoutput .= "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
						$dataentryoutput .= "\t\t\t\t<option value=''>".$blang->gT("Please choose")."..</option>\n";
						for ($i=1; $i<=10; $i++)
						{
							$dataentryoutput .= "\t\t\t\t<option value='$i'>$i</option>\n";
						}
						$dataentryoutput .= "</select>\n";
						$dataentryoutput .= "\t\t</td>\n";
						$dataentryoutput .= "\t</tr>\n";
					}
					$dataentryoutput .= "</table>\n";
					break;
					case "C": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
					$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult=db_execute_assoc($meaquery);
					$dataentryoutput .= "<table>\n";
					while ($mearow = $mearesult->FetchRow())
					{
						$dataentryoutput .= "\t<tr>\n";
						$dataentryoutput .= "\t\t<td align='right'>{$mearow['answer']}</td>\n";
						$dataentryoutput .= "\t\t<td>\n";
						$dataentryoutput .= "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
						$dataentryoutput .= "\t\t\t\t<option value=''>".$blang->gT("Please choose")."..</option>\n";
						$dataentryoutput .= "\t\t\t\t<option value='Y'>".$blang->gT("Yes")."</option>\n";
						$dataentryoutput .= "\t\t\t\t<option value='U'>".$blang->gT("Uncertain")."</option>\n";
						$dataentryoutput .= "\t\t\t\t<option value='N'>".$blang->gT("No")."</option>\n";
						$dataentryoutput .= "\t\t\t</select>\n";
						$dataentryoutput .= "\t\t</td>\n";
						$dataentryoutput .= "</tr>\n";
					}
					$dataentryoutput .= "</table>\n";
					break;
					case "E": //ARRAY (YES/UNCERTAIN/NO) radio-buttons
					$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} ORDER BY sortorder, answer";
					$mearesult=db_execute_assoc($meaquery) or safe_die ("Couldn't get answers, Type \"E\"<br />$meaquery<br />".$connect->ErrorMsg());
					$dataentryoutput .= "<table>\n";
					while ($mearow = $mearesult->FetchRow())
					{
						$dataentryoutput .= "\t<tr>\n";
						$dataentryoutput .= "\t\t<td align='right'>{$mearow['answer']}</td>\n";
						$dataentryoutput .= "\t\t<td>\n";
						$dataentryoutput .= "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
						$dataentryoutput .= "\t\t\t\t<option value=''>".$blang->gT("Please choose")."..</option>\n";
						$dataentryoutput .= "\t\t\t\t<option value='I'>".$blang->gT("Increase")."</option>\n";
						$dataentryoutput .= "\t\t\t\t<option value='S'>".$blang->gT("Same")."</option>\n";
						$dataentryoutput .= "\t\t\t\t<option value='D'>".$blang->gT("Decrease")."</option>\n";
						$dataentryoutput .= "\t\t\t</select>\n";
						$dataentryoutput .= "\t\t</td>\n";
						$dataentryoutput .= "</tr>\n";
					}
					$dataentryoutput .= "</table>\n";
					break;
					case ":": //ARRAY (Multi Flexi)
                    	$qidattributes=getQuestionAttributes($deqrow['qid']);
                    	if ($maxvalue=arraySearchByKey("multiflexible_max", $qidattributes, "attribute", 1)) {
                    		$maxvalue=$maxvalue['value'];
                    	} else {
                    		$maxvalue=10;
                    	}
                    	if ($minvalue=arraySearchByKey("multiflexible_min", $qidattributes, "attribute", 1)) {
                    		$minvalue=$minvalue['value'];
                    	} else {
                    		$minvalue=1;
                    	}
                    	if ($stepvalue=arraySearchByKey("multiflexible_step", $qidattributes, "attribute", 1)) {
                    		$stepvalue=$stepvalue['value'];
                    	} else {
                    		$stepvalue=1;
                    	}
            			if (arraySearchByKey("multiflexible_checkbox", $qidattributes, "attribute", 1)) {
            				$minvalue=0;
            				$maxvalue=1;
            				$stepvalue=1;
            			}
					    
						$dataentryoutput .= "<table>\n";
					    $dataentryoutput .= "  <tr><td></td>\n";
					    $lidquery = "SELECT lid FROM ".db_table_name("questions")." WHERE qid={$deqrow['qid']}";
					    $lidresult = db_execute_assoc($lidquery);
					    while ($data=$lidresult->FetchRow())
					    {
						  $lid=$data['lid'];
						}
                        $labelcodes=array();
						$lquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid=$lid and language='$language' ORDER BY sortorder, title";
						$lresult=db_execute_assoc($lquery) or die ("Couldn't get labels, Type \":\"<br />$lquery<br />".htmlspecialchars($connect->ErrorMsg()));
					    while ($data=$lresult->FetchRow())
					    {
					      $dataentryoutput .= "    <th>{$data['title']}</th>\n";
					      $labelcodes[]=$data['code'];
					    }
					    
					    $dataentryoutput .= "  </tr>\n";
					    
						$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} and language='$language' ORDER BY sortorder, answer";
						$mearesult=db_execute_assoc($meaquery) or die ("Couldn't get answers, Type \":\"<br />$meaquery<br />".htmlspecialchars($connect->ErrorMsg()));
					    $i=0;
						while ($mearow=$mearesult->FetchRow())
					    {
						    if (strpos($mearow['answer'],'|'))
                            {
                              $answerleft=substr($mearow['answer'],0,strpos($mearow['answer'],'|'));
                              $answerright=substr($mearow['answer'],strpos($mearow['answer'],'|')+1);
                            } 
                              else 
                              {
                                $answerleft=$mearow['answer'];
                                $answerright='';
                              }
                        	$dataentryoutput .= "\t<tr>\n";
							$dataentryoutput .= "\t\t<td align='right'>{$answerleft}</td>\n";
							foreach($labelcodes as $ld)
							{
    							$dataentryoutput .= "\t\t<td>\n";
    							$dataentryoutput .= "\t\t\t<select name='$fieldname{$mearow['code']}_$ld'>\n";
    							$dataentryoutput .= "\t\t\t\t<option value=''>...</option>\n";
								for($ii=$minvalue;$ii<=$maxvalue;$ii+=$stepvalue)
    							{
    							   $dataentryoutput .= "\t\t\t\t<option value='$ii'";
    							   $dataentryoutput .= ">$ii</option>\n";
    							}
    							$dataentryoutput .= "\t\t</select></td>\n";
    						}
							$dataentryoutput .= "\t</tr>\n";
						   $i++;
						}
						$i--;
						$dataentryoutput .= "</table>\n";
					break;
					case ";": //ARRAY (Multi Flexi)
						$dataentryoutput .= "<table>\n";
					    $dataentryoutput .= "  <tr><td></td>\n";
					    $lidquery = "SELECT lid FROM ".db_table_name("questions")." WHERE qid={$deqrow['qid']}";
					    $lidresult = db_execute_assoc($lidquery);
					    while ($data=$lidresult->FetchRow())
					    {
						  $lid=$data['lid'];
						}
						$lquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid=$lid and language='$language' ORDER BY sortorder, title";
						$lresult=db_execute_assoc($lquery) or die ("Couldn't get labels, Type \":\"<br />$lquery<br />".htmlspecialchars($connect->ErrorMsg()));
					    $labelcodes=array();
                        while ($data=$lresult->FetchRow())
					    {
					      $dataentryoutput .= "    <th>{$data['title']}</th>\n";
					      $labelcodes[]=$data['code'];
					    }
					    
					    $dataentryoutput .= "  </tr>\n";
					    
						$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} and language='$language' ORDER BY sortorder, answer";
						$mearesult=db_execute_assoc($meaquery) or die ("Couldn't get answers, Type \":\"<br />$meaquery<br />".htmlspecialchars($connect->ErrorMsg()));
					    $i=0;
						while ($mearow=$mearesult->FetchRow())
					    {
						    if (strpos($mearow['answer'],'|'))
                            {
                              $answerleft=substr($mearow['answer'],0,strpos($mearow['answer'],'|'));
                              $answerright=substr($mearow['answer'],strpos($mearow['answer'],'|')+1);
                            } 
                              else 
                              {
                                $answerleft=$mearow['answer'];
                                $answerright='';
                              }
                        	$dataentryoutput .= "\t<tr>\n";
							$dataentryoutput .= "\t\t<td align='right'>{$answerleft}</td>\n";
							foreach($labelcodes as $ld)
							{
    							$dataentryoutput .= "\t\t<td>\n";
    							$dataentryoutput .= "\t\t\t<input type='text' name='$fieldname{$mearow['code']}_$ld' />";
    							$dataentryoutput .= "\t\t</td>\n";
    						}
							$dataentryoutput .= "\t</tr>\n";
						   $i++;
						}
						$i--;
						$dataentryoutput .= "</table>\n";
					break;
					case "F": //ARRAY (Flexible Labels)
					case "H":
						$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} and language='$language' ORDER BY sortorder, answer";
						$mearesult=db_execute_assoc($meaquery) or safe_die ("Couldn't get answers, Type \"E\"<br />$meaquery<br />".$connect->ErrorMsg());
						$dataentryoutput .= "<table>\n";
						while ($mearow = $mearesult->FetchRow())
						{
						    if (strpos($mearow['answer'],'|'))
                            {
                              $answerleft=substr($mearow['answer'],0,strpos($mearow['answer'],'|'));
                              $answerright=substr($mearow['answer'],strpos($mearow['answer'],'|')+1);
                            } 
                              else 
                              {
                                $answerleft=$mearow['answer'];
                                $answerright='';
                              }
                        	$dataentryoutput .= "\t<tr>\n";
							$dataentryoutput .= "\t\t<td align='right'>{$answerleft}</td>\n";
							$dataentryoutput .= "\t\t<td>\n";
							$dataentryoutput .= "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
							$dataentryoutput .= "\t\t\t\t<option value=''>".$blang->gT("Please choose")."..</option>\n";
							$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$deqrow['lid']} and language='$language' ORDER BY sortorder, code";
							$fresult = db_execute_assoc($fquery);
							while ($frow = $fresult->FetchRow())
							{
								$dataentryoutput .= "\t\t\t\t<option value='{$frow['code']}'>".$frow['title']."</option>\n";
							}
							$dataentryoutput .= "\t\t\t</select>\n";
							$dataentryoutput .= "\t\t</td>\n";
							$dataentryoutput .= "\t\t<td align='left'>{$answerright}</td>\n";
							$dataentryoutput .= "</tr>\n";
						}
						$dataentryoutput .= "</table>\n";
						break;
/* 					case "1":
						$meaquery = "SELECT * FROM ".db_table_name("answers")." WHERE qid={$deqrow['qid']} and language='$language' ORDER BY sortorder, answer";
						$mearesult=db_execute_assoc($meaquery) or safe_die ("Couldn't get answers, Type \"E\"<br />$meaquery<br />".$connect->ErrorMsg());
						$dataentryoutput .= "<table>\n";
						while ($mearow = $mearesult->FetchRow())
						{
						    if (strpos($mearow['answer'],'|'))
                            {
                              $answerleft=substr($mearow['answer'],0,strpos($mearow['answer'],'|'));
                              $answerright=substr($mearow['answer'],strpos($mearow['answer'],'|')+1);
                            } 
                              else 
                              {
                                $answerleft=$mearow['answer'];
                                $answerright='';
                              }
                        	$dataentryoutput .= "\t<tr>\n";
							$dataentryoutput .= "\t\t<td align='right'>{$answerleft}</td>\n";
							$dataentryoutput .= "\t\t<td>\n";
							$dataentryoutput .= "\t\t\t<select name='$fieldname{$mearow['code']}'>\n";
							$dataentryoutput .= "\t\t\t\t<option value=''>".$blang->gT("Please choose")."..</option>\n";
							$fquery = "SELECT * FROM ".db_table_name("labels")." WHERE lid={$deqrow['lid']} and language='$language' ORDER BY sortorder, code";
							$fresult = db_execute_assoc($fquery);
							while ($frow = $fresult->FetchRow())
							{
								$dataentryoutput .= "\t\t\t\t<option value='{$frow['code']}'>".$frow['title']."</option>\n";
							}
							$dataentryoutput .= "\t\t\t</select>\n";
							$dataentryoutput .= "\t\t</td>\n";
							$dataentryoutput .= "\t\t<td align='left'>{$answerright}</td>\n";
							$dataentryoutput .= "</tr>\n";
						}
						$dataentryoutput .= "</table>\n";
						break;
*/						
				}
				//$dataentryoutput .= " [$surveyid"."X"."$gid"."X"."$qid]";
				$dataentryoutput .= "\t\t</td>\n";
				$dataentryoutput .= "\t</tr>\n";
				$dataentryoutput .= "\t<tr><td colspan='3' height='2' bgcolor='silver'></td></tr>\n";
			}
		}
		if ($thissurvey['active'] == "Y")
		{
			// Show Finalize response option
			$dataentryoutput .= "<script type='text/javascript'>
				  <!--
					function saveshow(value)
						{
						if (document.getElementById(value).checked == true)
							{
							document.getElementById(\"closerecord\").checked=false;
							document.getElementById(\"closerecord\").disabled=true;
							document.getElementById(\"saveoptions\").style.display=\"\";
							}
						else
							{
							document.getElementById(\"saveoptions\").style.display=\"none\";
							 document.getElementById(\"closerecord\").disabled=false;
							}
						}
				  //-->
				  </script>\n";
			$dataentryoutput .= "\t<tr>\n";
			$dataentryoutput .= "\t\t<td colspan='3' align='center'>\n";
			$dataentryoutput .= "\t\t<table><tr><td align='left'>\n";
			$dataentryoutput .= "\t\t\t<input type='checkbox' class='checkboxbtn' name='closerecord' id='closerecord' checked='checked'/><label for='closerecord'>".$clang->gT("Finalize response submission")."</label></td></tr>\n";
			$dataentryoutput .="<input type='hidden' name='closedate' value='".date_shift(date("Y-m-d H:i:s"), "Y-m-d H:i:s", $timeadjust)."' />\n";

			if ($thissurvey['allowsave'] == "Y")
			{
				//Show Save Option
				$dataentryoutput .= "\t\t\t<tr><td align='left'><input type='checkbox' class='checkboxbtn' name='save' id='save' onclick='saveshow(this.id)' /><label for='save'>".$clang->gT("Save for further completion by survey user")."</label>\n";
				$dataentryoutput .= "\t\t</td></tr></table>\n";
				$dataentryoutput .= "<div name='saveoptions' id='saveoptions' style='display: none'>\n";
				$dataentryoutput .= "<table align='center' class='outlinetable' cellspacing='0'>
					  <tr><td align='right'>".$clang->gT("Identifier:")."</td>
					  <td><input type='text' name='save_identifier' /></td></tr>
					  <tr><td align='right'>".$clang->gT("Password:")."</td>
					  <td><input type='password' name='save_password' /></td></tr>
					  <tr><td align='right'>".$clang->gT("Confirm Password:")."</td>
					  <td><input type='password' name='save_confirmpassword' /></td></tr>
					  <tr><td align='right'>".$clang->gT("Email:")."</td>
					  <td><input type='text' name='save_email' /></td></tr>
					  <tr><td align='right'>".$clang->gT("Start Language:")."</td>
					  <td>";
                $slangs = GetAdditionalLanguagesFromSurveyID($surveyid);
                $sbaselang = GetBaseLanguageFromSurveyID($surveyid);
                array_unshift($slangs,$sbaselang);
                $dataentryoutput.= "<select name='save_language'>\n";
                foreach ($slangs as $lang)
                   	{
                   		if ($lang == $baselang) $dataentryoutput .= "\t<option value='{$lang}' selected='selected'>".getLanguageNameFromCode($lang,false)."</option>\n";
                   		else {$dataentryoutput.="\t<option value='{$lang}'>".getLanguageNameFromCode($lang,false)."</option>\n";}
                   	}
                $dataentryoutput .= "</select>";
                      

				$dataentryoutput .= "</table>\n";
				$dataentryoutput .= "\t\t</td>\n";
				$dataentryoutput .= "\t</tr>\n";
			}
			$dataentryoutput .= "\t<tr>\n";
			$dataentryoutput .= "\t\t<td colspan='3' align='center'>\n";
			$dataentryoutput .= "\t\t\t<input type='submit' id='submitdata' value='".$clang->gT("Submit")."'";

			if (tokenTableExists($thissurvey['sid']))
			{
				$dataentryoutput .= " disabled='disabled'/>\n";
			}
			else
			{
				$dataentryoutput .= " />\n";
			}
			$dataentryoutput .= "\t\t</td>\n";
			$dataentryoutput .= "\t</tr>\n";
		}
		elseif ($thissurvey['active'] == "N")
		{
			$dataentryoutput .= "\t<tr>\n";
			$dataentryoutput .= "\t\t<td colspan='3' align='center'>\n";
			$dataentryoutput .= "\t\t\t<font color='red'><strong>".$clang->gT("This survey is not yet active. Your response cannot be saved")."\n";
			$dataentryoutput .= "\t\t</strong></font></td>\n";
			$dataentryoutput .= "\t</tr>\n";
		}
		else
		{
			$dataentryoutput .= "</form>\n";
			$dataentryoutput .= "\t<tr>\n";
			$dataentryoutput .= "\t\t<td colspan='3' align='center'>\n";
			$dataentryoutput .= "\t\t\t<font color='red'><strong>".$clang->gT("Error")."</strong></font><br />\n";
			$dataentryoutput .= "\t\t\t".$clang->gT("The survey you selected does not exist")."<br /><br />\n";
			$dataentryoutput .= "\t\t\t<input type='submit' value='".$clang->gT("Main Admin Screen")."' onclick=\"window.open('$scriptname', '_top')\" />\n";
			$dataentryoutput .= "\t\t</td>\n";
			$dataentryoutput .= "\t</tr>\n";
			$dataentryoutput .= "</table>";
			return;
		}
		if (!isset($_GET['language']))
		{
			$datalang = GetBaseLanguageFromSurveyID($surveyid);
		} else {
			$datalang = $_GET['language'];
		}
		$dataentryoutput .= "\t<tr>\n";
		$dataentryoutput .= "\t<td>\n";
		$dataentryoutput .= "\t<input type='hidden' name='subaction' value='insert' />\n";
		$dataentryoutput .= "\t<input type='hidden' name='surveytable' value='$surveytable' />\n";
		$dataentryoutput .= "\t<input type='hidden' name='sid' value='$surveyid' />\n";
		$dataentryoutput .= "\t<input type='hidden' name='language' value='$datalang' />\n";
		$dataentryoutput .= "\t</td>\n";
		$dataentryoutput .= "\t</tr>\n";
		$dataentryoutput .= "</table>\n";
		$dataentryoutput .= "\t</form>\n";
	}
	$dataentryoutput .= "&nbsp;";
}
else
{
	$action = "browse_response";
	include("access_denied.php");
	include("admin.php");
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
