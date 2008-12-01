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

//Security Checked: POST, GET, SESSION, DB, REQUEST, returnglobal      

//Ensure script is not run directly, avoid path disclosure
include_once("login_check.php");
if (isset($_POST['uid'])) {$postuserid=sanitize_int($_POST['uid']);}
if (isset($_POST['ugid'])) {$postusergroupid=sanitize_int($_POST['ugid']);}

if ($action == "listsurveys")
{
	$query = " SELECT a.*, c.*, u.users_name FROM ".db_table_name('surveys')." as a "
            ." INNER JOIN ".db_table_name('surveys_languagesettings')." as c ON ( surveyls_survey_id = a.sid AND surveyls_language = a.language ) AND surveyls_survey_id=a.sid and surveyls_language=a.language "
            ." INNER JOIN ".db_table_name('users')." as u ON (u.uid=a.owner_id) ";

	if ($_SESSION['USER_RIGHT_SUPERADMIN'] != 1)
	{
		$query .= " INNER JOIN ".db_table_name('surveys_rights')." AS b ON a.sid = b.sid ";
		$query .= " WHERE b.uid =".$_SESSION['loginID'];
	}

	$query .= " ORDER BY surveyls_title";

	$result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked

	if($result->RecordCount() > 0) {
        $listsurveys= "<br /><table cellpadding='1' width='800'>
				  <tr>
				    <th height=\"22\" width='22'>&nbsp;</th>
				    <th height=\"22\"><strong>".$clang->gT("Survey")."</strong></th>
				    <th><strong>".$clang->gT("Date Created")."</strong></th>
				    <th><strong>".$clang->gT("Owner")."</strong></th>
				    <th><strong>".$clang->gT("Access")."</strong></th>
				    <th><strong>".$clang->gT("Answer Privacy")."</strong></th>
				    <th><strong>".$clang->gT("Status")."</strong></th>
				    <th><strong>".$clang->gT("Full Responses")."</strong></th>
                                    <th><strong>".$clang->gT("Partial Responses")."</strong></th>
                                    <th><strong>".$clang->gT("Total Responses")."</strong></th>
				  </tr>";
        $gbc = "evenrow"; 

		while($rows = $result->FetchRow())
		{
			$sidsecurityQ = "SELECT b.* FROM {$dbprefix}surveys AS a INNER JOIN {$dbprefix}surveys_rights AS b ON a.sid = b.sid WHERE a.sid='{$rows['sid']}' AND b.uid = ".$_SESSION['loginID']; //Getting rights for this survey and user
			$sidsecurityR = db_execute_assoc($sidsecurityQ); //Checked
			$sidsecurity = $sidsecurityR->FetchRow();
			
			if($rows['private']=="Y")
			{
				$privacy=$clang->gT("Anonymous") ;
			}
			else $privacy =$clang->gT("Not Anonymous") ;

			
			if (bHasSurveyGotTokentable(null,$rows['sid']))
			{
				$visibility = $clang->gT("Closed-access");
			}
			else
			{
				$visibility = $clang->gT("Open-access");
			}

			if($rows['active']=="Y")
			{
				if ($rows['useexpiry']=='Y' && $rows['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust))
				{
					$status=$clang->gT("Expired") ;
				} 
                elseif ($rows['usestartdate']=='Y' && $rows['startdate'] > date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust))
                {
                    $status=$clang->gT("Not yet active") ;
                }                
                else {
					$status=$clang->gT("Active") ;
				}
				// Complete Survey Responses - added by DLR
                                $gnquery = "SELECT count(id) FROM ".db_table_name("survey_".$rows['sid'])." WHERE submitdate IS NULL";
                                $gnresult = db_execute_num($gnquery); //Checked
                                while ($gnrow = $gnresult->FetchRow())
                                {
                                        $partial_responses=$gnrow[0];
                                }
                                $gnquery = "SELECT count(id) FROM ".db_table_name("survey_".$rows['sid']);
                                $gnresult = db_execute_num($gnquery); //Checked
                                while ($gnrow = $gnresult->FetchRow())
                                {
                                        $responses=$gnrow[0];
                                }

			}
			else $status =$clang->gT("Inactive") ;

			$datecreated=$rows['datecreated'] ;

			if (in_array($rows['owner_id'],getuserlist('onlyuidarray')))
			{
				$ownername=$rows['users_name'] ;
			}
			else
			{
				$ownername="---";
			}

			$questionsCount = 0;
			$questionsCountQuery = "SELECT * FROM ".db_table_name('questions')." WHERE sid={$rows['sid']} AND language='".$rows['language']."'"; //Getting a count of questions for this survey
			$questionsCountResult = $connect->Execute($questionsCountQuery); //Checked
			$questionsCount = $questionsCountResult->RecordCount();

            if ($gbc == "oddrow") {$gbc = "evenrow";}
            else {$gbc = "oddrow";}
			$listsurveys.="<tr class='$gbc'>";

			if ($rows['active']=="Y")
			{
				if ($rows['useexpiry']=='Y' && $rows['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust))
				{
					$listsurveys .= "<td><img src='$imagefiles/expired.png' title='' "
					. "alt='".$clang->gT("This survey is active but expired.")."' align='left' width='20'"
					. "onmouseout=\"hideTooltip()\""
					. "onmouseover=\"showTooltip(event,'".$clang->gT("This survey is active but expired", "js")."');return false\" />\n";
				}
				else
				{
					if ($_SESSION['USER_RIGHT_SUPERADMIN'] ==1 || $sidsecurity['activate_survey'])
					{
						$listsurveys .= "<td><a href=\"#\" onclick=\"window.open('$scriptname?action=deactivate&amp;sid={$rows['sid']}', '_top')\""
						. "onmouseout=\"hideTooltip()\""
						. "title=\"".$clang->gTview("De-activate this Survey")."\" "
						. "onmouseover=\"showTooltip(event,'".$clang->gT("De-activate this Survey", "js")."');return false\">"
						. "<img src='$imagefiles/active.png' name='DeactivateSurvey' "
						. "alt='".$clang->gT("De-activate this Survey")."'  border='0' hspace='0' align='left' width='20' /></a></td>\n";
					} else 
					{
						$listsurveys .= "<td><img src='$imagefiles/active.png' title='' "
						. "alt='".$clang->gT("This survey is currently active")."' align='left' border='0' hspace='0' align='left' width='20' "
						. "onmouseout=\"hideTooltip()\""
						. "title=\"".$clang->gTview("This survey is currently active")."\""
						. "onmouseover=\"showTooltip(event,'".$clang->gT("This survey is currently active", "js")."');return false\" /></td>\n";
					}
				}
			} else {
				if ( ($_SESSION['USER_RIGHT_SUPERADMIN'] ==1 || $sidsecurity['activate_survey']) && $questionsCount > 0)
				{
					$listsurveys .= "<td><a href=\"#\" onclick=\"window.open('$scriptname?action=activate&amp;sid={$rows['sid']}', '_top')\""
					. "onmouseout=\"hideTooltip()\""
					. "title=\"".$clang->gTview("Activate this Survey")."\""
					. "onmouseover=\"showTooltip(event,'".$clang->gT("Activate this Survey", "js")."');return false\">" .
					"<img src='$imagefiles/inactive.png' title='' alt='".$clang->gT("Activate this Survey")."' border='0' hspace='0' align='left' width='20' /></a></td>\n" ;	
				} else 
				{
					$listsurveys .= "<td><img src='$imagefiles/inactive.png'"
					. "title='' alt='".$clang->gT("This survey is not currently active")."' border='0' hspace='0' align='left'"
					. "onmouseout=\"hideTooltip()\""
					. "onmouseover=\"showTooltip(event,'".$clang->gT("This survey is not currently active", "js")."');return false\" /></td>\n";
				}			
			}
			
			$listsurveys.="<td><a href='".$scriptname."?sid=".$rows['sid']."'>".$rows['surveyls_title']."</a></td>".
					    "<td>".$datecreated."</td>".
					    "<td>".$ownername."</td>".
					    "<td>".$visibility."</td>" .
					    "<td>".$privacy."</td>" .
					    "<td>".$status."</td>";

					    if ($rows['active']=="Y")
					    {
						$complete = $responses - $partial_responses;
                                                $listsurveys .= "<td align='center'>".$complete."</td>";
                                                $listsurveys .= "<td align='center'>".$partial_responses."</td>";
                                                $listsurveys .= "<td align='center'>".$responses."</td>";
					    }else{
						$listsurveys .= "<td>&nbsp;</td>";
						$listsurveys .= "<td>&nbsp;</td>";
						$listsurveys .= "<td>&nbsp;</td>";
					    }
					    $listsurveys .= "</tr>" ;
		}

		$listsurveys.="<tr class='header'>
		<td colspan=\"10\">&nbsp;</td>".
		"</tr>";
		$listsurveys.="</table><br />" ;
	}
	else $listsurveys="<br /><strong> ".$clang->gT("No Surveys available - please create one.")." </strong><br /><br />" ;
}

if ($action == "checksettings" || $action == "changelang" || $action=="changehtmleditormode")
{
	//GET NUMBER OF SURVEYS
	$query = "SELECT sid FROM ".db_table_name('surveys');
	$result = $connect->Execute($query); //Checked
	$surveycount=$result->RecordCount();
	$query = "SELECT sid FROM ".db_table_name('surveys')." WHERE active='Y'";
	$result = $connect->Execute($query); //Checked
	$activesurveycount=$result->RecordCount();
	$query = "SELECT users_name FROM ".db_table_name('users');
	$result = $connect->Execute($query); //Checked
	$usercount = $result->RecordCount();

	// prepare data for the htmleditormode preference
	$edmod1='';
	$edmod2='';
	$edmod3='';
	$edmod4='';
	switch ($_SESSION['htmleditormode'])
	{
		case 'none':
			$edmod2="selected='selected'";
		break;
		case 'inline':
			$edmod3="selected='selected'";
		break;
		case 'popup':
			$edmod4="selected='selected'";
		break;
		default:
			$edmod1="selected='selected'";
		break;
	}

	$tablelist = $connect->MetaTables();
	foreach ($tablelist as $table)
	{
		$stlength=strlen($dbprefix).strlen("old");
		if (substr($table, 0, $stlength+strlen("_tokens")) == $dbprefix."old_tokens")
		{
			$oldtokenlist[]=$table;
		}
		elseif (substr($table, 0, strlen($dbprefix) + strlen("tokens")) == $dbprefix."tokens")
		{
			$tokenlist[]=$table;
		}
		elseif (substr($table, 0, $stlength) == $dbprefix."old")
		{
			$oldresultslist[]=$table;
		}
	}
	if(isset($oldresultslist) && is_array($oldresultslist))
	{$deactivatedsurveys=count($oldresultslist);} else {$deactivatedsurveys=0;}
	if(isset($oldtokenlist) && is_array($oldtokenlist))
	{$deactivatedtokens=count($oldtokenlist);} else {$deactivatedtokens=0;}
	if(isset($tokenlist) && is_array($tokenlist))
	{$activetokens=count($tokenlist);} else {$activetokens=0;}
	$cssummary = "<table><tr><td height='1'></td></tr></table>\n"
	. "<form action='$scriptname' method='post'>"
	. "<table class='table2columns'"
	. "cellpadding='1' cellspacing='0' width='600'>\n"
	. "<tr>\n"
	. "<td colspan='2' align='center' bgcolor='#F8F8FF'>\n"
	. "<strong>".$clang->gT("LimeSurvey System Summary")."</strong>\n"
	. "</td>\n"
	. "</tr>\n";
	// Database name & default language
	$cssummary .= "<tr>\n"
	. "<td width='50%' align='right'>\n"
	. "<strong>".$clang->gT("Database Name").":</strong>\n"
	. "</td><td>\n"
	. "$databasename\n"
	. "</td>\n"
	. "</tr>\n"
	. "<tr>\n"
	. "<td align='right'>\n"
	. "<strong>".$clang->gT("Default Language").":</strong>\n"
	. "</td><td>\n"
	. "".getLanguageNameFromCode($defaultlang)."\n"
	. "</td>\n"
	. "</tr>\n";
	// Current language
	$cssummary .=  "<tr>\n"
	. "<td align='right' >\n"
	. "<strong>".$clang->gT("Current Language").":</strong>\n"
	. "</td><td>\n"
	. "<select name='lang' onchange='form.submit()'>\n";
	foreach (getlanguagedata() as $langkey=>$languagekind)
	{
		$cssummary .= "<option value='$langkey'";
		if ($langkey == $_SESSION['adminlang']) {$cssummary .= " selected='selected'";}
		$cssummary .= ">".$languagekind['description']." - ".$languagekind['nativedescription']."</option>\n";
	}
	$cssummary .= "</select>\n"
	. "<input type='hidden' id='action' name='action' value='changelang' />\n"
	. "</td>\n"
	. "</tr>\n";
	// Current htmleditormode
	$cssummary .=  "<tr>\n"
	. "<td align='right' >\n"
	. "<strong>".$clang->gT("Preferred HTML editor mode").":</strong>\n"
	. "</td><td>\n"
	. "<select name='htmleditormode' onchange='document.getElementById(\"action\").value=\"changehtmleditormode\";form.submit();'>\n"
	. "<option value='default' $edmod1>".$clang->gT("Default HTML editor mode")."</option>\n"
	. "<option value='none' $edmod2>".$clang->gT("No HTML editor")."</option>\n"
	. "<option value='inline' $edmod3>".$clang->gT("Inline HTML editor")."</option>\n"
	. "<option value='popup' $edmod4>".$clang->gT("Popup HTML editor")."</option>\n";
	$cssummary .= "</select>\n"
	. ""
	. "</td>\n"
	. "</tr>\n";
	// Other infos
	$cssummary .=  "<tr>\n"
	. "<td align='right'>\n"
	. "<strong>".$clang->gT("Users").":</strong>\n"
	. "</td><td>\n"
	. "$usercount\n"
	. "</td>\n"
	. "</tr>\n"
	. "<tr>\n"
	. "<td align='right'>\n"
	. "<strong>".$clang->gT("Surveys").":</strong>\n"
	. "</td><td>\n"
	. "$surveycount\n"
	. "</td>\n"
	. "</tr>\n"
	. "<tr>\n"
	. "<td align='right'>\n"
	. "<strong>".$clang->gT("Active Surveys").":</strong>\n"
	. "</td><td>\n"
	. "$activesurveycount\n"
	. "</td>\n"
	. "</tr>\n"
	. "<tr>\n"
	. "<td align='right'>\n"
	. "<strong>".$clang->gT("De-activated Surveys").":</strong>\n"
	. "</td><td>\n"
	. "$deactivatedsurveys\n"
	. "</td>\n"
	. "</tr>\n"
	. "<tr>\n"
	. "<td align='right'>\n"
	. "<strong>".$clang->gT("Active Token Tables").":</strong>\n"
	. "</td><td>\n"
	. "$activetokens\n"
	. "</td>\n"
	. "</tr>\n"
	. "<tr>\n"
	. "<td align='right'>\n"
	. "<strong>".$clang->gT("De-activated Token Tables").":</strong>\n"
	. "</td><td>\n"
	. "$deactivatedtokens\n"
	. "</td>\n"
	. "</tr>\n"
	. "</table></form>\n"
	. "<table><tr><td height='1'></td></tr></table>\n";
    
    if ($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1) 
    {
    $cssummary .= "<table><tr><td><form action='$scriptname' method='post'><input type='hidden' name='action' value='showphpinfo' /><input type='submit' value='".$clang->gT("Show PHPInfo")."' /></form></td></tr></table>";
    }
}

if ($surveyid)
{
	$query = "SELECT * FROM ".db_table_name('surveys_rights')." WHERE  sid = {$surveyid} AND uid = ".$_SESSION['loginID'];
	$result = $connect->SelectLimit($query, 1); 
	if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $result->RecordCount() > 0)
	{
		$baselang = GetBaseLanguageFromSurveyID($surveyid);
		$sumquery5 = "SELECT b.* FROM {$dbprefix}surveys AS a INNER JOIN {$dbprefix}surveys_rights AS b ON a.sid = b.sid WHERE a.sid=$surveyid AND b.uid = ".$_SESSION['loginID']; //Getting rights for this survey and user
		$sumresult5 = db_execute_assoc($sumquery5); //Checked
		$sumrows5 = $sumresult5->FetchRow();
		$sumquery3 = "SELECT * FROM ".db_table_name('questions')." WHERE sid=$surveyid AND language='".$baselang."'"; //Getting a count of questions for this survey
		$sumresult3 = $connect->Execute($sumquery3); //Checked
		$sumcount3 = $sumresult3->RecordCount();
		$sumquery6 = "SELECT * FROM ".db_table_name('conditions')." as c, ".db_table_name('questions')."as q WHERE c.qid = q.qid AND q.sid=$surveyid"; //Getting a count of conditions for this survey
		$sumresult6 = $connect->Execute($sumquery6) or die("Can't coun't conditions"); //Checked
		$sumcount6 = $sumresult6->RecordCount();
		$sumquery2 = "SELECT * FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='".$baselang."'"; //Getting a count of groups for this survey
		$sumresult2 = $connect->Execute($sumquery2); //Checked
		$sumcount2 = $sumresult2->RecordCount();
		$sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
		$sumresult1 = db_select_limit_assoc($sumquery1, 1); //Checked

        // Output starts here...
		$surveysummary = "";

		$s1row = $sumresult1->FetchRow();

        $s1row = array_map('strip_tags', $s1row);
		//$s1row = array_map('htmlspecialchars', $s1row);
		$activated = $s1row['active'];
		//BUTTON BAR
		$surveysummary .= ""  //"<tr><td colspan=2>\n"
		. "<div class='menubar'>\n"
		. "<div class='menubar-title'>\n"
		. "<strong>".$clang->gT("Survey")."</strong> "
		. "<font class='basic'>{$s1row['surveyls_title']} (".$clang->gT("ID").":$surveyid)</font></div>\n"
		. "<div class='menubar-main'>\n"
		. "<div class='menubar-left'>\n";
		if ($activated == "N" )
		{
			$surveysummary .= "<img src='$imagefiles/inactive.png' "
			. "title='' alt='".$clang->gT("This survey is not currently active")."'"
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("This survey is not currently active", "js")."');return false\" />\n";
			if(($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['activate_survey']) && $sumcount3>0)
			{
				$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=activate&amp;sid=$surveyid', '_top')\""
				. "onmouseout=\"hideTooltip()\""
				. "title=\"".$clang->gTview("Activate this Survey")."\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Activate this Survey", "js")."');return false\">"
				. "<img src='$imagefiles/activate.png' name='ActivateSurvey' title='' alt='".$clang->gT("Activate this Survey")."'/></a>\n" ;
			}
			else
			{
				$surveysummary .= "<img src='$imagefiles/activate_disabled.png' onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'"
				. $clang->gT("Survey cannot be activated. Either you have no permission or there are no questions.", "js")
				. "');return false\" name='ActivateDisabledSurvey' title='' alt='"
				. $clang->gT("Survey cannot be activated. Either you have no permission or there are no questions.")."' />\n" ;
			}
		}
		elseif ($activated == "Y")
		{
			if (($s1row['useexpiry']=='Y') && ($s1row['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust)))
			{
				$surveysummary .= "<img src='$imagefiles/expired.png' title='' "
				. "alt='".$clang->gT("This survey is active but expired.")."' "
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("This survey is active but expired", "js")."');return false\" />\n";
			}
            elseif (($s1row['usestartdate']=='Y') && ($s1row['startdate'] > date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust)))
            {
                $surveysummary .= "<img src='$imagefiles/notyetstarted.png' title='' "
                . "alt='".$clang->gT("This survey is active but has a start date.")."' "
                . "onmouseout=\"hideTooltip()\""
                . "onmouseover=\"showTooltip(event,'".$clang->gT("This survey is active but has a start date.", "js")."');return false\" />\n";
            }
			else
			{
				$surveysummary .= "<img src='$imagefiles/active.png' title='' "
				. "alt='".$clang->gT("This survey is currently active")."' "
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("This survey is currently active", "js")."');return false\" />\n";
			}
			if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['activate_survey'])
			{
				$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=deactivate&amp;sid=$surveyid', '_top')\""
				. "onmouseout=\"hideTooltip()\" "
				. "title=\"".$clang->gTview("De-activate this Survey")."\" "
				. "onmouseover=\"showTooltip(event,'".$clang->gT("De-activate this Survey", "js")."');return false\">"
				. "<img src='$imagefiles/deactivate.png' name='DeactivateSurvey' "
				. "alt='".$clang->gT("De-activate this Survey")."' title='' /></a>\n" ;
			}
			else
			{
				$surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='14' />\n";
			}
		}

		$surveysummary .= "<img src='$imagefiles/seperator.gif' alt=''  />\n";
		// survey rights

		if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $s1row['owner_id'] == $_SESSION['loginID'])
		{
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=surveysecurity&amp;sid=$surveyid', '_top')\" "
			. "onmouseout=\"hideTooltip()\" title=\"".$clang->gTview("Survey Security Settings")."\" "
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Survey Security Settings", "js")."');return false\">"
			. "<img src='$imagefiles/survey_security.png' name='SurveySecurity' "
			. "title='' alt='".$clang->gT("Survey Security Settings")."' /></a>\n";
		}
		else
		{
			$surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
		}
		
		if ($activated == "N")
        {
            $icontext=$clang->gT("Test This Survey");
            $icontext2=$clang->gTview("Test This Survey");
        } else
            {
            $icontext=$clang->gT("Execute This Survey");
            $icontext2=$clang->gTview("Execute This Survey");
            }
		$baselang = GetBaseLanguageFromSurveyID($surveyid);
		if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
		{
			$surveysummary .= "<a href=\"#\" accesskey='d' onclick=\"window.open('"
			. $publicurl."/index.php?sid=$surveyid&amp;newtest=Y&amp;lang=$baselang', '_blank')\" "
			. "onmouseout=\"hideTooltip()\" "
			. "title=\"".$icontext2."\" "
			. "onmouseover=\"showTooltip(event,'$icontext');return false\">"
			. "<img  src='$imagefiles/do.png' title='' "
			. "name='DoSurvey' alt='$icontext' /></a>\n";
		
		} else {
			$surveysummary .= "<a href=\"#\" accesskey='d' onclick=\"hideTooltip(); "
			. "document.getElementById('printpopup').style.visibility='hidden'; document.getElementById('langpopup2').style.visibility='visible';\" "
			. "onmouseout=\"hideTooltip()\" "
			. "title=\"".$icontext2."\" "
			. "onmouseover=\"showTooltip(event,'$icontext');return false\">"
			. "<img  src='$imagefiles/do.png' title='' "
			. "name='DoSurvey' alt='$icontext' /></a>\n";
			
			$tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
			$tmp_survlangs[] = $baselang;
			rsort($tmp_survlangs);
			// Test Survey Language Selection Popup
			$surveysummary .="<div class=\"langpopup2\" id=\"langpopup2\"><table width=\"100%\"><tr><td>".$clang->gT("Please select a language:")."</td></tr>";
			foreach ($tmp_survlangs as $tmp_lang)
			{
				$surveysummary .= "<tr><td><a href=\"#\" accesskey='d' onclick=\"document.getElementById('langpopup2').style.visibility='hidden'; window.open('".$publicurl."/index.php?sid=$surveyid&amp;newtest=Y&amp;lang=".$tmp_lang."', '_blank')\"><font color=\"#097300\"><b>".getLanguageNameFromCode($tmp_lang,false)."</b></font></a></td></tr>";
			}
			$surveysummary .= "<tr><td align=\"center\"><a href=\"#\" accesskey='d' onclick=\"document.getElementById('langpopup2').style.visibility='hidden';\"><font color=\"#DF3030\">".$clang->gT("Cancel")."</font></a></td></tr></table></div>";
			
			$tmp_pheight = getPopupHeight();
			$surveysummary .= "<script type='text/javascript'>document.getElementById('langpopup2').style.height='".$tmp_pheight."px';</script>\n";

		}

		if($activated == "Y" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['browse_response']))
		{
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('".$homeurl."/".$scriptname."?action=dataentry&amp;sid=$surveyid', '_top')\""
			. "onmouseout=\"hideTooltip()\" "
			. "title=\"".$clang->gTview("Dataentry Screen for Survey")."\" "
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Dataentry Screen for Survey", "js")."');return false\">"
			. "<img src='$imagefiles/dataentry.png' title='' alt='".$clang->gT("Dataentry Screen for Survey")."'"
			. "name='DoDataentry' /></a>\n";
		} 
		else if (!$sumrows5['browse_response'] && $_SESSION['USER_RIGHT_SUPERADMIN'] !=1)
		{
			$surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
		} else {
			$surveysummary .= "<a href=\"#\" onclick=\"alert('".$clang->gT("This survey is not active, data entry is not allowed","js")."')\""
			. "onmouseout=\"hideTooltip()\""
			. "title=\"".$clang->gTview("Dataentry Screen for Survey")."\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Dataentry Screen for Survey", "js")."');return false\">"
			. "<img src='$imagefiles/dataentry_disabled.png' title='' alt='".$clang->gT("Dataentry Screen for Survey")."'"
			. "name='DoDataentry' /></a>\n";
		}
		
		if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
		{
			
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=showprintablesurvey&amp;sid=$surveyid', '_blank')\""
			. "onmouseout=\"hideTooltip()\" "
			. "title=\"".$clang->gTview("Printable Version of Survey")."\" "
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Printable Version of Survey", "js")."');return false\">"
			. "<img src='$imagefiles/print.png' title='' name='ShowPrintableSurvey' alt='".$clang->gT("Printable Version of Survey")."' /></a>"
			. "<img src='$imagefiles/seperator.gif' alt='' />\n";
		
		} else {
			
			$surveysummary .= "<a href=\"#\" onclick=\"hideTooltip(); document.getElementById('printpopup').style.visibility='visible'; "
			. "document.getElementById('langpopup2').style.visibility='hidden';\" "
			. "onmouseout=\"hideTooltip()\" "
			. "title=\"".$clang->gTview("Printable Version of Survey")."\" "
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Printable Version of Survey", "js")."');return false\">"
			. "<img src='$imagefiles/print.png' title='' name='ShowPrintableSurvey' alt='".$clang->gT("Printable Version of Survey")."' /></a>\n"
			. "<img src='$imagefiles/seperator.gif' alt='' />\n";
			
			$tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
			$baselang = GetBaseLanguageFromSurveyID($surveyid);
			$tmp_survlangs[] = $baselang;
			rsort($tmp_survlangs);
			
			// Test Survey Language Selection Popup
			$surveysummary .="<div class=\"langpopup2\" id=\"printpopup\"><table width=\"100%\"><tr><td>".$clang->gT("Please select a language:")."</td></tr>";
			foreach ($tmp_survlangs as $tmp_lang)
			{
				$surveysummary .= "<tr><td><a href=\"#\" accesskey='d' onclick=\"document.getElementById('printpopup').style.visibility='hidden'; window.open('$scriptname?action=showprintablesurvey&amp;sid=$surveyid&amp;lang=".$tmp_lang."', '_blank')\"><font color=\"#097300\"><b>".getLanguageNameFromCode($tmp_lang,false)."</b></font></a></td></tr>";
			}
			$surveysummary .= "<tr><td align=\"center\"><a href=\"#\" accesskey='d' onclick=\"document.getElementById('printpopup').style.visibility='hidden';\"><font color=\"#DF3030\">".$clang->gT("Cancel")."</font></a></td></tr></table></div>";
			
			$surveysummary .= "<script type='text/javascript'>document.getElementById('printpopup').style.left='152px';</script>\n";
			
			$tmp_pheight = getPopupHeight();
			$surveysummary .= "<script type='text/javascript'>document.getElementById('printpopup').style.height='".$tmp_pheight."px';</script>\n";
			
		}

		if($_SESSION['USER_RIGHT_SUPERADMIN'] ==1 || $sumrows5['edit_survey_property'])
		{
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=editsurvey&amp;sid=$surveyid', '_top')\" "
			. "onmouseout=\"hideTooltip()\" "
			. "title=\"".$clang->gTview("Edit Current Survey")."\" "
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Edit Current Survey", "js")."');return false\">"
			. "<img src='$imagefiles/edit.png' title='' name='EditSurvey' alt='".$clang->gT("Edit Current Survey")."' /></a>\n";
		}
		else
		{
			$surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
		}


		if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1  || $sumrows5['delete_survey'])
		{
//			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=deletesurvey&amp;sid=$surveyid', '_top')\""
			$surveysummary .= "<a href=\"#\" onclick=\"".get2post("$scriptname?action=deletesurvey&amp;sid=$surveyid")."\" "
			. "onmouseout=\"hideTooltip()\" "
			. "title=\"".$clang->gTview("Delete Current Survey")."\" "
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Delete Current Survey", "js")."');return false\">"
			. "<img src='$imagefiles/delete.png' title='' name='DeleteWholeSurvey' alt='".$clang->gT("Delete Current Survey")."' /></a>\n" ;
		}
		else
		{
			$surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40'  />\n";
		}

		if ( $_SESSION['USER_RIGHT_SUPERADMIN'] == 1  || $sumrows5['define_questions'])
		{
			if ($sumcount6 > 0) {
				$surveysummary .= "<a href=\"#\" onclick=\"".get2post("$scriptname?action=resetsurveylogic&amp;sid=$surveyid")."\" "
				. "onmouseout=\"hideTooltip()\" "
				. "title=\"".$clang->gTview("Reset Survey Logic")."\" "
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Reset Survey Logic", "js")."');return false\">"
				. "<img src='$imagefiles/resetsurveylogic.png' title='' name='ResetSurveyLogic' alt='".$clang->gT("Reset Survey Logic")."' /></a>\n";
			}
			else
			{
				$surveysummary .= "<a href=\"#\" onclick=\"alert('".$clang->gT("This survey's questions don't use conditions", "js")."');\" "
				. "onmouseout=\"hideTooltip()\" "
				. "title=\"".$clang->gTview("Reset Survey Logic")."\" "
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Reset Survey Logic", "js")." (".$clang->gT("disabled", "js").")"."');return false\">"
				. "<img src='$imagefiles/resetsurveylogic_disabled.png' title='' name='ResetSurveyLogic' "
				. "alt='".$clang->gT("Reset Survey Logic")."' /></a>\n";
			}
		}
		else
		{
			$surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
		}

		if($activated!="Y" && getGroupSum($surveyid,$s1row['language'])>1 && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions']))
		{
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=ordergroups&amp;sid=$surveyid', '_top')\" "
			. "onmouseout=\"hideTooltip()\" "
			. "title=\"".$clang->gTview("Change Group Order")."\" "
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Change Group Order", "js")."');return false\">"
			. "<img src='$imagefiles/reorder.png' title='' alt='".$clang->gT("Change Group Order")."' name='ordergroups' /></a>\n";
		}
		else
		{
            $surveysummary .= "<a href=\"#\""
            . "onmouseout=\"hideTooltip()\" "
            . "title=\"".$clang->gTview("Disabled - Change Group Order")."\" "
            . "onmouseover=\"showTooltip(event,'".$clang->gT("Change Group Order", "js")." (".$clang->gT("disabled", "js").")"."');return false\">"
            . "<img src='$imagefiles/reorder_disabled.png' title='' alt='".$clang->gT("Change Group Order")." (".$clang->gT("disabled").")"."' name='ordergroups' /></a>";
		}

		if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['export'])
		{
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=exportstructure&amp;sid=$surveyid', '_top')\" "
			. "onmouseout=\"hideTooltip()\" "
			. "title=\"".$clang->gTview("Export Survey Structure")."\" "
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Export Survey Structure", "js")."');return false\">"
			. "<img src='$imagefiles/export.png' title='' alt='". $clang->gT("Export Survey Structure")."' name='ExportSurvey' /></a>\n" ;
		}
		else
		{
			$surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />";
		}

		if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['edit_survey_property'])
		{
        $surveysummary .= "<img src='$imagefiles/seperator.gif' alt=''  />\n";                 
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=assessments&amp;sid=$surveyid', '_top')\" "
			. "onmouseout=\"hideTooltip()\" "
			. "title=\"".$clang->gTview("Set Assessment Rules")."\" "
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Set Assessment Rules", "js")."');return false\">"
			. "<img src='$imagefiles/assessments.png' title='' alt='". $clang->gT("Set Assessment Rules")."' name='SurveyAssessment' /></a>\n";
		}
		else
		{
			$surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40'  />\n";
		}
		
		if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['edit_survey_property'])
		{
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=quotas&amp;sid=$surveyid', '_top')\" "
			. "onmouseout=\"hideTooltip()\" "
			. "title=\"".$clang->gTview("Set Survey Quotas")."\" "
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Set Survey Quotas", "js")."');return false\">"
			. "<img src='$imagefiles/quota.png' title='' alt='". $clang->gT("Set Survey Quotas")."' name='SurveyQuotas' /></a>\n" ;
		}
		else
		{
			$surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40'  />\n";
		}

		if ($activated == "Y" && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['browse_response']))
		{
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=browse&amp;sid=$surveyid', '_top')\" "
			. "onmouseout=\"hideTooltip()\" "
			. "title=\"".$clang->gTview("Browse Responses For This Survey")."\" "
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Browse Responses For This Survey", "js")."');return false\">"
			. "<img src='$imagefiles/browse.png' title='' name='BrowseSurveyResults' alt='".$clang->gT("Browse Responses For This Survey")."' /></a>\n";
			if ($s1row['allowsave'] == "Y")
			{
				$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=saved&amp;sid=$surveyid', '_top')\" "
				. "onmouseout=\"hideTooltip()\" "
				. "title=\"".$clang->gTview("View Saved but not submitted Responses")."\" "
				. "onmouseover=\"showTooltip(event,'".$clang->gT("View Saved but not submitted Responses", "js")."');return false\">"
				. "<img src='$imagefiles/saved.png' title='' name='BrowseSaved' alt='".$clang->gT("View Saved but not submitted Responses")."' /></a>\n";
			}
		}
		if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['export'] || $sumrows5['activate_survey'])
		{
            $surveysummary .= "<img src='$imagefiles/seperator.gif' alt=''  />\n";     
            if ($activated == "Y")
            {
			    $surveysummary .="<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\" "
			    . "onmouseout=\"hideTooltip()\" "
			    . "title=\"".$clang->gTview("Activate/Edit Tokens for this Survey")."\" "
			    . "onmouseover=\"showTooltip(event,'".$clang->gT("Activate/Edit Tokens for this Survey", "js")."');return false\">"
			    . "<img src='$imagefiles/tokens.png' title='' name='TokensControl' alt='".$clang->gT("Activate/Edit Tokens for this Survey")."' /></a>\n" ;
            }
            else
            {
                $surveysummary .= "<a href=\"#\" onclick=\"alert('"
		        . $clang->gT("Adding/editing tokens is not possible because this survey is not activated.","js")."')\" "
                . "onmouseout=\"hideTooltip()\" "
                . "title=\"".$clang->gTview("Activate/Edit Tokens for this Survey")."\" "
                . "onmouseover=\"showTooltip(event,'".$clang->gT("Activate/Edit Tokens for this Survey", "js")."');return false\">"
                . "<img src='$imagefiles/tokens_disabled.png' title='' alt='".$clang->gT("Activate/Edit Tokens for this Survey")."' "
                . "name='DoTokens' /></a>\n";
                
            }
		}
		$surveysummary .= "</div>\n"
		. "<div class='menubar-right'>\n";
		$surveysummary .= "<font class=\"boxcaption\">".$clang->gT("Groups").":</font>\n"
		. "<select name='groupselect' "
		. "onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";

		if (getgrouplistlang($gid, $baselang))
		{
			$surveysummary .= getgrouplistlang($gid, $baselang);
		}
		else
		{
			$surveysummary .= "<option>".$clang->gT("None")."</option>\n";
		}
		$surveysummary .= "</select>\n";
        if ($activated == "Y")
        {
            $surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }
        elseif($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions'])
        {
            $surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=addgroup&amp;sid=$surveyid', '_top')\""
            . "onmouseout=\"hideTooltip()\""
            . "title=\"".$clang->gTview("Add New Group to Survey")."\""
            . "onmouseover=\"showTooltip(event,'".$clang->gT("Add New Group to Survey", "js")."');return false\"> "
            . "<img src='$imagefiles/add.png' title='' alt='' name='AddNewGroup' /></a>\n";
        }
        $surveysummary .= "<img src='$imagefiles/seperator.gif' alt='' />\n"
        . "<img src='$imagefiles/blank.gif' width='19' alt=''  />\n"
        . "<input type='image' src='$imagefiles/minus.gif' title='". $clang->gT("Hide details of this Survey")."' "
        . "alt='". $clang->gT("Hide details of this Survey")."' name='MinimiseSurveyWindow' "
        . "onclick='document.getElementById(\"surveydetails\").style.display=\"none\";' />\n";
        $surveysummary .= "<input type='image' src='$imagefiles/plus.gif' title='". $clang->gT("Show details of this survey")."' "
        . "alt='". $clang->gT("Show details of this survey")."' name='MaximiseSurveyWindow' "
        . "onclick='document.getElementById(\"surveydetails\").style.display=\"\";' />\n";
        if (!$gid)
        {
            $surveysummary .= "<input type='image' src='$imagefiles/close.gif' title='". $clang->gT("Close this survey")."' "
            . "alt='".$clang->gT("Close this survey")."' name='CloseSurveyWindow' "
            . "onclick=\"window.open('$scriptname', '_top')\" />\n";
        }
        else
        {
            $surveysummary .= "<img src='$imagefiles/blank.gif' width='18' alt='' />\n";
        }
        
		$surveysummary .= "</div>\n"
		. "</div>\n"
		. "</div>\n";
        $surveysummary .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix


		//SURVEY SUMMARY
		if ($gid || $qid || $action=="deactivate"|| $action=="activate" || $action=="surveysecurity" 
                 || $action=="surveyrights" || $action=="addsurveysecurity" || $action=="addusergroupsurveysecurity" 
                 || $action=="setsurveysecurity" ||  $action=="setusergroupsurveysecurity" || $action=="delsurveysecurity" 
                 || $action=="editsurvey" || $action=="addgroup" || $action=="importgroup"
                 || $action=="ordergroups" || $action=="updatesurvey" || $action=="deletesurvey" || $action=="resetsurveylogic"
                 || $action=="importsurvresources" 
                 || $action=="exportstructure" || $action=="quotas" ) {$showstyle="style='display: none'";}
		if (!isset($showstyle)) {$showstyle="";}
		$additionnalLanguagesArray = GetAdditionalLanguagesFromSurveyID($surveyid);
		$surveysummary .= "<table width='100%' align='center' bgcolor='#FFFFFF' border='0'>\n"
		. "<tr id='surveydetails' $showstyle><td><table class='table2columns'><tr><td align='right' valign='top' width='15%'>"
		. "<strong>".$clang->gT("Title").":</strong></td>\n"
		. "<td align='left' class='settingentryhighlight'><strong>{$s1row['surveyls_title']} "
		. "(".$clang->gT("ID")." {$s1row['sid']})</strong></td></tr>\n";
		$surveysummary2 = "";
		if ($s1row['private'] != "N") {$surveysummary2 .= $clang->gT("Answers to this survey are anonymized.")."<br />\n";}
		else {$surveysummary2 .= $clang->gT("This survey is NOT anonymous.")."<br />\n";}
		if ($s1row['format'] == "S") {$surveysummary2 .= $clang->gT("It is presented question by question.")."<br />\n";}
		elseif ($s1row['format'] == "G") {$surveysummary2 .= $clang->gT("It is presented group by group.")."<br />\n";}
		else {$surveysummary2 .= $clang->gT("It is presented on one single page.")."<br />\n";}
		if ($s1row['datestamp'] == "Y") {$surveysummary2 .= $clang->gT("Responses will be date stamped")."<br />\n";}
		if ($s1row['ipaddr'] == "Y") {$surveysummary2 .= $clang->gT("IP Addresses will be logged")."<br />\n";}
		if ($s1row['refurl'] == "Y") {$surveysummary2 .= $clang->gT("Referer-URL will be saved")."<br />\n";}
		if ($s1row['usecookie'] == "Y") {$surveysummary2 .= $clang->gT("It uses cookies for access control.")."<br />\n";}
		if ($s1row['allowregister'] == "Y") {$surveysummary2 .= $clang->gT("If tokens are used, the public may register for this survey")."<br />\n";}
		if ($s1row['allowsave'] == "Y") {$surveysummary2 .= $clang->gT("Participants can save partially finished surveys")."<br />\n";}
		switch ($s1row['notification'])
		{
			case 0:
			$surveysummary2 .= $clang->gT("No email notification")."<br />\n";
			break;
			case 1:
			$surveysummary2 .= $clang->gT("Basic email notification")."<br />\n";
			break;
			case 2:
			$surveysummary2 .= $clang->gT("Detailed email notification with result codes")."<br />\n";
			break;
		}

		if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['edit_survey_property'])
		{
			$surveysummary2 .= $clang->gT("Regenerate Question Codes:")
//			. " [<a href='$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=straight' "
//			. "onclick='return confirm(\"".$clang->gT("Are you sure you want regenerate the question codes?","js")."\")' "
			. " [<a href='#' "
			. "onclick=\"if (confirm('".$clang->gT("Are you sure you want regenerate the question codes?","js")."')) {".get2post("$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=straight")."}\" "
			. ">".$clang->gT("Straight")."</a>] "
//			. "[<a href='$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=bygroup' "
//			. "onclick='return confirm(\"".$clang->gT("Are you sure you want regenerate the question codes?","js")."\")' "
			. " [<a href='#' "
			. "onclick=\"if (confirm('".$clang->gT("Are you sure you want regenerate the question codes?","js")."')) {".get2post("$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=bygroup")."}\" "
			. ">".$clang->gT("By Group")."</a>]";
			$surveysummary2 .= "</td></tr>\n";
		}
		$surveysummary .= "<tr>"
		. "<td align='right' valign='top'><strong>"
		. $clang->gT("Survey URL") ." (".getLanguageNameFromCode($s1row['language'],false)."):</strong></td>\n";
    if ( $modrewrite ) {
        $tmp_url = $GLOBALS['publicurl'] . '/' . $s1row['sid'];
		    $surveysummary .= "<td align='left'> <a href='$tmp_url/lang-".$s1row['language']."' target='_blank'>$tmp_url/lang-".$s1row['language']."</a>";
        foreach ($additionnalLanguagesArray as $langname)
        {
          $surveysummary .= "&nbsp;<a href='$tmp_url/lang-$langname' target='_blank'><img title='".$clang->gT("Survey URL For Language:")." ".getLanguageNameFromCode($langname,false)."' alt='".getLanguageNameFromCode($langname,false)." ".$clang->gT("Flag")."' src='../images/flags/$langname.png' /></a>";  
        }
    } else {
		$tmp_url = $GLOBALS['publicurl'] . '/index.php?sid=' . $s1row['sid'];
		$surveysummary .= "<td align='left'> <a href='$tmp_url&amp;lang=".$s1row['language']."' target='_blank'>$tmp_url&amp;lang=".$s1row['language']."</a>";
        foreach ($additionnalLanguagesArray as $langname)
        {
          $surveysummary .= "&nbsp;<a href='$tmp_url&amp;lang=$langname' target='_blank'><img title='".$clang->gT("Survey URL For Language:")." ".getLanguageNameFromCode($langname,false)."' alt='".getLanguageNameFromCode($langname,false)." ".$clang->gT("Flag")."' src='../images/flags/$langname.png' /></a>";  
        }
    }
        
		$surveysummary .= "</td></tr>\n"
		. "<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Description:")."</strong></td>\n<td align='left'>";
		if (trim($s1row['surveyls_description'])!='') {$surveysummary .= " {$s1row['surveyls_description']}";}
		$surveysummary .= "</td></tr>\n"
		. "<tr >\n"
		. "<td align='right' valign='top'><strong>"
		. $clang->gT("Welcome:")."</strong></td>\n"
		. "<td align='left'> {$s1row['surveyls_welcometext']}</td></tr>\n"
		. "<tr ><td align='right' valign='top'><strong>"
		. $clang->gT("Administrator:")."</strong></td>\n"
		. "<td align='left'> {$s1row['admin']} ({$s1row['adminemail']})</td></tr>\n"
		. "<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Fax To:")."</strong></td>\n<td align='left'>";
		if (trim($s1row['faxto'])!='') {$surveysummary .= " {$s1row['faxto']}";}
		$surveysummary .= "</td></tr>\n"
        . "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Start date:")."</strong></td>\n";
        if ($s1row['usestartdate']== "Y")
        {
            $startdate=$s1row['startdate'];
        }
        else
        {
            $startdate="-";
        }
        $surveysummary .= "<td align='left'>$startdate</td></tr>\n"		
        . "<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Expiry Date:")."</strong></td>\n";
		if ($s1row['useexpiry']== "Y")
		{
			$expdate=$s1row['expires'];
		}
		else
		{
			$expdate="-";
		}
		$surveysummary .= "<td align='left'>$expdate</td></tr>\n"
		. "<tr ><td align='right' valign='top'><strong>"
		. $clang->gT("Template:")."</strong></td>\n"
		. "<td align='left'> {$s1row['template']}</td></tr>\n"
		
		. "<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Base Language:")."</strong></td>\n";
		if (!$s1row['language']) {$language=getLanguageNameFromCode($currentadminlang);} else {$language=getLanguageNameFromCode($s1row['language']);}
		$surveysummary .= "<td align='left'>$language</td></tr>\n";

		// get the rowspan of the Additionnal languages row
		// is at least 1 even if no additionnal language is present
		$additionnalLanguagesCount = count($additionnalLanguagesArray);
		if ($additionnalLanguagesCount == 0) $additionnalLanguagesCount = 1;
		$surveysummary .= "<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Additional Languages").":</strong></td>\n";

		$first=true;
		foreach ($additionnalLanguagesArray as $langname)
		{
			if ($langname)
			{
				if (!$first) {$surveysummary .= "<tr><td>&nbsp;</td>";}
				$first=false;
				$surveysummary .= "<td align='left'>".getLanguageNameFromCode($langname)."</td></tr>\n";
			}
		}
		if ($first) $surveysummary .= "</tr>";

		if ($s1row['surveyls_urldescription']==""){$s1row['surveyls_urldescription']=$s1row['url'];}
		$surveysummary .= "<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Exit Link").":</strong></td>\n"
		. "<td align='left'>";
		if ($s1row['url']!="") {$surveysummary .=" <a href=\"{$s1row['url']}\" title=\"{$s1row['url']}\">{$s1row['surveyls_urldescription']}</a>";}
		$surveysummary .="</td></tr>\n";
		$surveysummary .= "<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Number of questions/groups").":</strong></td><td>$sumcount3/$sumcount2</td></tr>\n";
        $surveysummary .= "<tr><td align='right' valign='top'><strong>"
        . $clang->gT("Survey currently active").":</strong></td><td>";
        if ($activated == "N")
        {
            $surveysummary .= $clang->gT("No");
        }
         else 
                 {
                 $surveysummary .= $clang->gT("Yes");
                 }
        $surveysummary .="</td></tr>\n";         
                 
		if ($activated == "Y")
		{
                $surveysummary .= "<tr><td align='right' valign='top'><strong>"
                . $clang->gT("Survey table name").":</strong></td><td>".$dbprefix."survey_$surveyid</td></tr>\n";
		}
        $surveysummary .= "<tr><td align='right' valign='top'><strong>"
                . $clang->gT("Hints").":</strong></td><td>\n";

        if ($activated == "N" && $sumcount3 == 0)
        {
			$surveysummary .= $clang->gT("Survey cannot be activated yet.")."<br />\n";
			if ($sumcount2 == 0 && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions']))
			{
				$surveysummary .= "<font class='statusentryhighlight'>[".$clang->gT("You need to add groups")."]</font><br />";
			}
			if ($sumcount3 == 0 && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 ||$sumrows5['define_questions']))
			{
				$surveysummary .= "<font class='statusentryhighlight'>[".$clang->gT("You need to add questions")."]</font><br />";
			}
		}
		$surveysummary .=  $surveysummary2
		. "</table></td></tr></table>\n";
	}
	else
	{
		include("access_denied.php");
	}
}


if ($surveyid && $gid )   // Show the group toolbar
{
	// TODO: check that surveyid and thus baselang are always set here
	$sumquery4 = "SELECT * FROM ".db_table_name('questions')." WHERE sid=$surveyid AND
	gid=$gid AND language='".$baselang."'"; //Getting a count of questions for this survey
	$sumresult4 = $connect->Execute($sumquery4); //Checked
	$sumcount4 = $sumresult4->RecordCount();
	$grpquery ="SELECT * FROM ".db_table_name('groups')." WHERE gid=$gid AND
	language='".$baselang."' ORDER BY ".db_table_name('groups').".group_order";
	$grpresult = db_execute_assoc($grpquery); //Checked

	// Check if other questions/groups are dependent upon this group
	$condarray=GetGroupDepsForConditions($surveyid,"all",$gid,"by-targgid");	

    $groupsummary = "<div class='menubar'>\n"
        . "<div class='menubar-title'>\n";

	while ($grow = $grpresult->FetchRow())
	{
        $grow = array_map('strip_tags', $grow);
		//$grow = array_map('htmlspecialchars', $grow);
		$groupsummary .= '<strong>'.$clang->gT("Group").'</strong>&nbsp;'
		. "<font class='basic'>{$grow['group_name']} (".$clang->gT("ID").":$gid)</font>\n"
		. "</div>\n"
        . "<div class='menubar-main'>\n"
        . "<div class='menubar-left'>\n"
		. "<img src='$imagefiles/blank.gif' alt='' width='56' height='20'  />\n"
		. "<img src='$imagefiles/seperator.gif' alt=''  />\n"
		. "<img src='$imagefiles/blank.gif' alt='' width='170' height='20'  />\n"
		. "<img src='$imagefiles/seperator.gif' alt=''  />\n";

		if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions'])
		{
			$groupsummary .=  "<a href=\"#\" onclick=\"window.open('$scriptname?action=editgroup&amp;sid=$surveyid&amp;gid=$gid','_top')\""
			. "onmouseout=\"hideTooltip()\""
			. "title=\"".$clang->gTview("Edit Current Group")."\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Edit Current Group", "js")."');return false\">" .
			"<img src='$imagefiles/edit.png' title='' alt='' name='EditGroup' /></a>\n" ;
		}
		else
		{
			$groupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
		}

		if ((($sumcount4 == 0 && $activated != "Y") || $activated != "Y") &&($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions']))
		{
			if (is_null($condarray))
			{
//				$groupsummary .= "<a href='$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid' onclick=\"return confirm('".$clang->gT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js")."')\""
				$groupsummary .= "<a href='#' onclick=\"if (confirm('".$clang->gT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js")."')) {".get2post("$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid")."}\""
				. "onmouseout=\"hideTooltip()\""
				. "title=\"".$clang->gTview("Delete Current Group")."\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Delete Current Group", "js")."');return false\">"
				. "<img src='$imagefiles/delete.png' alt='' name='DeleteWholeGroup' title=''  /></a>\n";
				//get2post("$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid");
			}
			else
			{
				$groupsummary .= "<a href='$scriptname?sid=$surveyid&amp;gid=$gid' onclick=\"alert('".$clang->gT("Impossible to delete this group because there is at least one question having a condition on its content","js")."')\" "
				. "onmouseout=\"hideTooltip()\""
				. "title=\"".$clang->gTview("Delete Current Group")."\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Disabled","js")."-".$clang->gT("Delete Current Group", "js")."');return false\">"
				. "<img src='$imagefiles/delete_disabled.png' alt='' name='DeleteWholeGroup' title='' /></a>\n";
			}
		}
		else
		{
			$groupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
		}
        $groupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";


        if(($activated!="Y" && getQuestionSum($surveyid, $gid)>1) && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions']))
        {
            $groupsummary .= "<a href='$scriptname?action=orderquestions&amp;sid=$surveyid&amp;gid=$gid' onmouseout=\"hideTooltip()\""
            . "title=\"".$clang->gTview("Change Question Order")."\" "
            . "onmouseover=\"showTooltip(event,'".$clang->gT("Change Question Order", "js")."');return false\">"
            . "<img src='$imagefiles/reorder.png' title='' alt='".$clang->gT("Change Question Order")."' name='updatequestionorder' /></a>\n" ;
        }
        else
        {
            $groupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }
        if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['export'])
        {

            $groupsummary .="<a href='$scriptname?action=dumpgroup&amp;sid=$surveyid&amp;gid=$gid' onmouseout=\"hideTooltip()\""
            . "title=\"".$clang->gTview("Export Current Group")."\" "
            . "onmouseover=\"showTooltip(event,'".$clang->gT("Export Current Group", "js")."');return false\">" .
            "<img src='$imagefiles/exportcsv.png' title='' alt='' name='ExportGroup'  /></a>\n";
        }
        else
        {
            $groupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }
   		$groupsummary .= "<img src='$imagefiles/seperator.gif' alt='' />\n"
		. "</div>\n"
        . "<div class='menubar-right'>\n"
		. "<font class=\"boxcaption\">".$clang->gT("Questions").":</font>&nbsp;<select class=\"listboxquestions\" name='qid' "
		. "onchange=\"window.open(this.options[this.selectedIndex].value, '_top')\">\n"
		. getquestions($surveyid,$gid,$qid)
		. "</select>\n";
        if ($activated == "Y")
        {
            $groupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
        }
        elseif($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions'])
        {
            $groupsummary .= "<a href='$scriptname?action=addquestion&amp;sid=$surveyid&amp;gid=$gid'"
            ."onmouseout=\"hideTooltip()\""
            ."title=\"".$clang->gTview("Add New Question to Group")."\""
            ."onmouseover=\"showTooltip(event,'".$clang->gT("Add New Question to Group", "js")."');return false\">" .
            "<img src='$imagefiles/add.png' title='' alt='' " .
            " name='AddNewQuestion' onclick=\"window.open('', '_top')\" /></a>\n";
        }
        
        $groupsummary .= "<img src='$imagefiles/seperator.gif' alt=''  />\n";
        $groupsummary.= "<img src='$imagefiles/blank.gif' width='19' alt=''  />\n"
        . "<input type='image' src='$imagefiles/minus.gif' title='"
        . $clang->gT("Hide Details of this Group")."' alt='". $clang->gT("Hide Details of this Group")."' name='MinimiseGroupWindow' "
        . " onclick='document.getElementById(\"groupdetails\").style.display=\"none\";' />\n";
        $groupsummary .= "<input type='image' src='$imagefiles/plus.gif' title='"
        . $clang->gT("Show Details of this Group")."' alt='". $clang->gT("Show Details of this Group")."' name='MaximiseGroupWindow' "
        . " onclick='document.getElementById(\"groupdetails\").style.display=\"\";' />\n";
        if (!$qid)
        {
            $groupsummary .= "<input type='image' src='$imagefiles/close.gif' title='"
            . $clang->gT("Close this Group")."' alt='". $clang->gT("Close this Group")."'  name='CloseSurveyWindow' "
            . "onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" />\n";
        }
        else
        {
            $groupsummary .= "<img src='$imagefiles/blank.gif' alt='' width='18' />\n";
        }        
        $groupsummary .="</div></div>\n"
		. "</div>\n";
        $groupsummary .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix
        
		if ($qid || $action=='editgroup'|| $action=='addquestion') {$gshowstyle="style='display: none'";}
		else	  {$gshowstyle="";}

		$groupsummary .= "<table class='table2columns' id='groupdetails' $gshowstyle ><tr ><td width='20%' align='right'><strong>"
		. $clang->gT("Title").":</strong></td>\n"
		. "<td align='left'>"
		. "{$grow['group_name']} ({$grow['gid']})</td></tr>\n"
		. "<tr><td valign='top' align='right'><strong>"
		. $clang->gT("Description:")."</strong></td>\n<td align='left'>";
		if (trim($grow['description'])!='') {$groupsummary .=$grow['description'];}
		$groupsummary .= "</td></tr>\n";

		if (!is_null($condarray))
		{
			$groupsummary .= "<tr><td align='right'><strong>"
			. $clang->gT("Questions with conditions to this group").":</strong></td>\n"
			. "<td valign='bottom' align='left'>";
			foreach ($condarray[$gid] as $depgid => $deprow)
			{
				foreach ($deprow['conditions'] as $depqid => $depcid)
				{
					//$groupsummary .= "[QID: ".$depqid."]"; 
					$listcid=implode("-",$depcid);
					$groupsummary .= " <a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$depgid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."')\">[QID: ".$depqid."]</a>"; 
				}
			}
			$groupsummary .= "</td></tr>";
		}
	}
	$groupsummary .= "\n</table>\n";
}

if ($surveyid && $gid && $qid)  // Show the question toolbar
{
	// TODO: check that surveyid is set and that so is $baselang
	//Show Question Details
	$qrq = "SELECT * FROM ".db_table_name('answers')." WHERE qid=$qid AND language='".$baselang."' ORDER BY sortorder, answer";
	$qrr = $connect->Execute($qrq); //Checked
	$qct = $qrr->RecordCount();
	$qrquery = "SELECT * FROM ".db_table_name('questions')." WHERE gid=$gid AND sid=$surveyid AND qid=$qid AND language='".$baselang."'";
	$qrresult = db_execute_assoc($qrquery) or safe_die($qrquery."<br />".$connect->ErrorMsg()); //Checked
	$questionsummary = "<div class='menubar'>\n";

	// Check if other questions in the Survey are dependent upon this question
	$condarray=GetQuestDepsForConditions($surveyid,"all","all",$qid,"by-targqid","outsidegroup");

	while ($qrrow = $qrresult->FetchRow())
	{
        $qrrow = array_map('strip_tags', $qrrow);
		//$qrrow = array_map('htmlspecialchars', $qrrow);
		$questionsummary .= "<div class='menubar-title'>\n"
		. "<strong>". $clang->gT("Question")."</strong> <font class='basic'>{$qrrow['question']} (".$clang->gT("ID").":$qid)</font>\n"
		. "</div>\n"
        . "<div class='menubar-main'>\n"
        . "<div class='menubar-left'>\n"
		. "<img src='$imagefiles/blank.gif' alt='' width='55' height='20' />\n"
		. "<img src='$imagefiles/seperator.gif' alt='' />\n"
		. "<img src='$imagefiles/blank.gif' alt='' width='171' height='20'  />\n"
		. "<img src='$imagefiles/seperator.gif' alt='' />\n";

		if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions'])
		{
			$questionsummary .= "<a href='$scriptname?action=editquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'" .
			"onmouseout=\"hideTooltip()\""
			. "title=\"".$clang->gTview("Edit Current Question")."\""
			."onmouseover=\"showTooltip(event,'".$clang->gT("Edit Current Question", "js")."');return false\">" .
			"<img src='$imagefiles/edit.png' title='' alt='' name='EditQuestion' /></a>\n" ;
		}
		else
		{
			$questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
		}

		if ((($qct == 0 && $activated != "Y") || $activated != "Y") && ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions']))
		{
			if (is_null($condarray))
			{
//				$questionsummary .= "<a href='$scriptname?action=delquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'" .
//				"onclick=\"return confirm('".$clang->gT("Deleting this question will also delete any answers it includes. Are you sure you want to continue?","js")."')\""
				$questionsummary .= "<a href='#'" .
				"onclick=\"if (confirm('".$clang->gT("Deleting this question will also delete any answers it includes. Are you sure you want to continue?","js")."')) {".get2post("$scriptname?action=delquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid")."}\""
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Delete Current Question", "js")."');return false\">"
				. "<img src='$imagefiles/delete.png' name='DeleteWholeQuestion' alt= '' title='' "
				. "border='0' hspace='0' /></a>\n";
			}
			else
			{
				$questionsummary .= "<a href='$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid'" .
				"onclick=\"alert('".$clang->gT("Impossible to delete this question because  there is at least one question having a condition on it","js")."')\""
				. "onmouseout=\"hideTooltip()\""
				. "title=\"".$clang->gTview("Disabled - Delete Current Question")."\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Disabled - Delete Current Question", "js")."');return false\">"
				. "<img src='$imagefiles/delete_disabled.png' name='DeleteWholeQuestion' alt= '' title='' /></a>\n";
			}
		}
		else {$questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";}
		$questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='84' />\n";

		if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['export'])
		{
			$questionsummary .= "<a href='$scriptname?action=dumpquestion&amp;sid=$surveyid&amp;qid=$qid' onmouseout=\"hideTooltip()\""
			. "title=\"".$clang->gTview("Export this Question")."\" " 
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Export this Question", "js")."');return false\">" .
			"<img src='$imagefiles/exportcsv.png' title='' alt='' name='ExportQuestion' /></a>\n";
		}
		else
		{
			$questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
		}
		$questionsummary .= "<img src='$imagefiles/seperator.gif' alt='' />\n";

		if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions'])
		{
			if ($activated != "Y")
			{
				$questionsummary .= "<a href='$scriptname?action=copyquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'" .
				"onmouseout=\"hideTooltip()\""
				. "title=\"".$clang->gTview("Copy Current Question")."\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Copy Current Question", "js")."');return false\">" .
				"<img src='$imagefiles/copy.png' title='' alt='' name='CopyQuestion' /></a>\n"
				. "<img src='$imagefiles/seperator.gif' alt='' />\n";
			}
			else
			{
				$questionsummary .= "<a href='#'" .
				"onmouseout=\"hideTooltip()\""
				. "title=\"".$clang->gTview("Copy Current Question")."\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Copy Current Question", "js")." (".$clang->gT("disabled","js").")"."');return false\" onclick=\"alert('".$clang->gT("Copy question is not possible in an Active survey","js")."')\">" .
				"<img src='$imagefiles/copy_disabled.png' title='' alt='' name='CopyQuestion' /></a>\n"
				. "<img src='$imagefiles/seperator.gif' alt='' />\n";
			}
		}
		else
		{
			$questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
		}
		if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions'])
		{
			$questionsummary .= "<a href='#' onclick=\"window.open('$scriptname?action=conditions&amp;sid=$surveyid&amp;qid=$qid', 'conditions', 'menubar=no, location=no, status=no, height=500, width=800, scrollbars=yes, resizable=yes, left=50, top=50')\""
			. "onmouseout=\"hideTooltip()\""
			. "title=\"".$clang->gTview("Set Conditions for this Question")."\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Set Conditions for this Question", "js")."');return false\">"
			. "<img src='$imagefiles/conditions.png' title='' alt=''  name='SetQuestionConditions' /></a>\n"
			. "<img src='$imagefiles/seperator.gif' alt='' />\n";
		}
		else
		{
			$questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
		}
		if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions'])
		{
			if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
			{
			$questionsummary .= "<a href=\"#\" accesskey='d' onclick=\"window.open('$scriptname?action=previewquestion&amp;sid=$surveyid&amp;qid=$qid', '_blank')\""
			. "onmouseout=\"hideTooltip()\""
			. "title=\"".$clang->gTview("Preview This Question")."\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Preview This Question", "js")."');return false\">"
			. "<img src='$imagefiles/preview.png' title='' alt='' name='previewquestionimg' /></a>\n"
			. "<img src='$imagefiles/seperator.gif' alt='' />\n";
			} else {
				$questionsummary .= "<a href=\"#\" accesskey='d' onclick=\"hideTooltip(); document.getElementById('printpopup').style.visibility='hidden'; document.getElementById('langpopup2').style.visibility='hidden'; document.getElementById('previewquestion').style.visibility='visible';\""
				. "onmouseout=\"hideTooltip()\""
				. "title=\"".$clang->gTview("Preview This Question")."\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Preview This Question", "js")."');return false\">"
				. "<img src='$imagefiles/preview.png' title='' alt='' name='previewquestionimg' /></a>\n"
				. "<img src='$imagefiles/seperator.gif' alt=''  />\n";
						
				$tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
				$baselang = GetBaseLanguageFromSurveyID($surveyid);
				$tmp_survlangs[] = $baselang;
				rsort($tmp_survlangs);

				// Test Survey Language Selection Popup
				$surveysummary .="<div class=\"previewpopup\" id=\"previewquestion\"><table width=\"100%\"><tr><td>".$clang->gT("Please select a language:")."</td></tr>";
				foreach ($tmp_survlangs as $tmp_lang)
				{
					$surveysummary .= "<tr><td><a href=\"#\" accesskey='d' onclick=\"document.getElementById('previewquestion').style.visibility='hidden'; window.open('$scriptname?action=previewquestion&amp;sid=$surveyid&amp;qid=$qid&amp;lang=".$tmp_lang."', '_blank')\"><font color=\"#097300\"><b>".getLanguageNameFromCode($tmp_lang,false)."</b></font></a></td></tr>";
				}
				$surveysummary .= "<tr><td align=\"center\"><a href=\"#\" accesskey='d' onclick=\"document.getElementById('previewquestion').style.visibility='hidden';\"><font color=\"#DF3030\">".$clang->gT("Cancel")."</font></a></td></tr></table></div>";
				$tmp_pheight = getPopupHeight();
				$surveysummary .= "<script type='text/javascript'>document.getElementById('previewquestion').style.height='".$tmp_pheight."px';</script>";
			}
		}
		else
		{
			$questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
		}
		if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions'])
		{
			if ($qrrow['type'] == "O" || $qrrow['type'] == "L" || 
			    $qrrow['type'] == "!" || $qrrow['type'] == "!" || 
				$qrrow['type'] == "M" || $qrrow['type'] == "Q" || 
				$qrrow['type'] == "A" || $qrrow['type'] == "B" || 
				$qrrow['type'] == "C" || $qrrow['type'] == "E" || 
				$qrrow['type'] == "F" || $qrrow['type'] == "H" || 
				$qrrow['type'] == "P" || $qrrow['type'] == "R" || 
				$qrrow['type'] == "K" || $qrrow['type'] == "1" || 
				$qrrow['type'] == ":" || $qrrow['type'] == ";")
			{
			$questionsummary .= "" .
			"<a href='".$scriptname."?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;viewanswer=Y'" .
			"onmouseout=\"hideTooltip()\""
			. "title=\"".$clang->gTview("Edit/Add Answers for this Question")."\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Edit/Add Answers for this Question", "js")."');return false\">" .
			"<img src='$imagefiles/answers.png' alt='' title='' name='ViewAnswers' /></a>\n" ;
			}
		}
		else
		{
			$questionsummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' />\n";
		}
		$questionsummary .= "</div>\n"
        . "<div class='menubar-right'>\n"
        . "<input type='image' src='$imagefiles/minus.gif' title='"
        . $clang->gT("Hide Details of this Question")."'  alt='". $clang->gT("Hide Details of this Question")."' name='MinimiseQuestionWindow' "
        . "onclick='document.getElementById(\"questiondetails\").style.display=\"none\";' />\n"
        . "<input type='image' src='$imagefiles/plus.gif' title='"
		. $clang->gT("Show Details of this Question")."'  alt='". $clang->gT("Show Details of this Question")."' name='MaximiseQuestionWindow' "
		. "onclick='document.getElementById(\"questiondetails\").style.display=\"\";' />\n"
        . "<input type='image' src='$imagefiles/close.gif' title='"
        . $clang->gT("Close this Question")."' alt='". $clang->gT("Close this Question")."' name='CloseQuestionWindow' "
        . "onclick=\"window.open('$scriptname?sid=$surveyid&amp;gid=$gid', '_top')\" />\n"
		. "</div>\n"
		. "</div>\n"
        . "</div>\n";
        $questionsummary .= "<p style='margin:0;font-size:1px;line-height:1px;height:1px;'>&nbsp;</p>"; //CSS Firefox 2 transition fix
        
		if (returnglobal('viewanswer') || $action =="editquestion" || $action =="copyquestion")	{$qshowstyle = "style='display: none'";}
		else							{$qshowstyle = "";}
		$questionsummary .= "<table class='table2columns' id='questiondetails' $qshowstyle><tr><td width='20%' align='right'><strong>"
		. $clang->gT("Code:")."</strong></td>\n"
		. "<td align='left'>{$qrrow['title']}";
		if ($qrrow['type'] != "X")
		{
			if ($qrrow['mandatory'] == "Y") {$questionsummary .= ": (<i>".$clang->gT("Mandatory Question")."</i>)";}
			else {$questionsummary .= ": (<i>".$clang->gT("Optional Question")."</i>)";}
		}
		$questionsummary .= "</td></tr>\n"
		. "<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Question:")."</strong></td>\n<td align='left'>".strip_tags($qrrow['question'])."</td></tr>\n"
		. "<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Help:")."</strong></td>\n<td align='left'>";
		if (trim($qrrow['help'])!=''){$questionsummary .= strip_tags($qrrow['help']);}
		$questionsummary .= "</td></tr>\n";
		if ($qrrow['preg'])
		{
			$questionsummary .= "<tr ><td align='right' valign='top'><strong>"
			. $clang->gT("Validation:")."</strong></td>\n<td align='left'>{$qrrow['preg']}"
			. "</td></tr>\n";
		}
		$qtypes = getqtypelist("", "array"); //qtypes = array(type code=>type description)
		$questionsummary .= "<tr><td align='right' valign='top'><strong>"
		.$clang->gT("Type:")."</strong></td>\n<td align='left'>{$qtypes[$qrrow['type']]}";
		$questionsummary .="</td></tr>\n";
		if ($qct == 0 && ($qrrow['type'] == "O" || $qrrow['type'] == "L" 
		               || $qrrow['type'] == "!" || $qrrow['type'] == "M" 
					   || $qrrow['type'] == "Q" || $qrrow['type'] == "K" 
					   || $qrrow['type'] == "A" || $qrrow['type'] == "B" 
					   || $qrrow['type'] == "C" || $qrrow['type'] == "E" 
					   || $qrrow['type'] == "P" || $qrrow['type'] == "R" 
					   || $qrrow['type'] == "F" || $qrrow['type'] == "1" 
					   || $qrrow['type'] == "H" || $qrrow['type'] == ":"
					   || $qrrow['type'] == ";"))
		{
			$questionsummary .= "<tr ><td></td><td align='left'>"
			. "<font face='verdana' size='1' color='red'>"
			. $clang->gT("Warning").": ". $clang->gT("You need to add answers to this question")." "
			. "<input align='top' type='image' src='$imagefiles/answerssmall.png' title='"
			. $clang->gT("Edit/Add Answers for this Question")."' name='EditThisQuestionAnswers'"
			. "onclick=\"window.open('".$scriptname."?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;viewanswer=Y', '_top')\" /></font></td></tr>\n";
		}
		
		// For Labelset Questions show the label set and warn if there is no label set configured
		if (($qrrow['type'] == "1" || $qrrow['type'] == "F" || $qrrow['type'] == "H" || 
		     $qrrow['type'] == "W" || $qrrow['type'] == "Z" || $qrrow['type'] == ":" ||
			 $qrrow['type'] == ";" ))
		{
			$questionsummary .= "<tr ><td align='right'><strong>". $clang->gT("Label Set").":</strong></td>";
			if (!$qrrow['lid'])
			{
				$questionsummary .=  "<td align='left'><font face='verdana' size='1' color='red'>"
								 . $clang->gT("Warning")." - ".$clang->gT("You need to choose a label set for this question!")."</font>\n";
			}
			else 
			// If label set ID is configured show the labelset name and ID
			{

			    $labelsetname=$connect->GetOne("SELECT label_name FROM ".db_table_name('labelsets')." WHERE lid = ".$qrrow['lid']);
			 	$questionsummary .= "<td align='left'>".$labelsetname." (LID: {$qrrow['lid']}) ";
			}
			// If the user has the right to edit the label sets show the icon for the label set administration
			if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions'])
			{
			$questionsummary .= "<input align='top' type='image' src='$imagefiles/labelssmall.png' title='"
			. $clang->gT("Edit/Add Label Sets")."' name='EditThisLabelSet' "
			. "onclick=\"window.open('$scriptname?action=labels&amp;lid={$qrrow['lid']}', '_blank')\" />\n";
			}
			$questionsummary .= "</td></tr>";
			
			if ($qrrow['type'] == "1") // Second labelset for "multi scale"
			{
				$questionsummary .= "<tr><td align='right'><strong>". $clang->gT("Second Label Set").":</strong></td>";
				if (!$qrrow['lid1'])
				{
					$questionsummary .=  "<td align='left'><font face='verdana' size='1' color='red'>"
								 . $clang->gT("Warning")." - ".$clang->gT("You need to choose a second label set for this question!")."</font>\n";
				}
				else 
				// If label set ID is configured show the labelset name and ID
				{

			    	$labelsetname=$connect->GetOne("SELECT label_name FROM ".db_table_name('labelsets')." WHERE lid = ".$qrrow['lid1']);
			 		$questionsummary .= "<td align='left'>".$labelsetname." (LID: {$qrrow['lid1']}) ";
				}
			
				// If the user has the right to edit the second label sets show the icon for the label set administration
				if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['define_questions'])
				{
					$questionsummary .= "<input align='top' type='image' src='$imagefiles/labelssmall.png' title='"
					. $clang->gT("Edit/Add second Label Sets")."' name='EditThisLabelSet' "
					. "onclick=\"window.open('$scriptname?action=labels&amp;lid={$qrrow['lid1']}', '_blank')\" />\n";
				}
				$questionsummary .= "</td></tr>";
			}
		}
			  
		
		if ($qrrow['type'] == "M" or $qrrow['type'] == "P")
		{
			$questionsummary .= "<tr>"
			. "<td align='right' valign='top'><strong>"
			. $clang->gT("Other:")."</strong></td>\n"
			. "<td align='left'>";
			$questionsummary .= ($qrrow['other'] == "Y") ? ($clang->gT("Yes")) : ($clang->gT("No")) ;
			$questionsummary .= "</td></tr>\n";
		}
		if (isset($qrrow['mandatory']) and ($qrrow['type'] != "X"))
		{
			$questionsummary .= "<tr>"
			. "<td align='right' valign='top'><strong>"
			. $clang->gT("Mandatory:")."</strong></td>\n"
			. "<td align='left'>";
			$questionsummary .= ($qrrow['mandatory'] == "Y") ? ($clang->gT("Yes")) : ($clang->gT("No")) ;
			$questionsummary .= "</td></tr>\n";
		}
		if (!is_null($condarray))
		{
			$questionsummary .= "<tr>"
			. "<td align='right' valign='top'><strong>"
			. $clang->gT("Other questions having conditions on this question:")
			. "</strong></td>\n<td align='left' valign='bottom'>\n";
			foreach ($condarray[$qid] as $depqid => $depcid)
			{
				$listcid=implode("-",$depcid);
				$questionsummary .= " <a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."')\">[QID: ".$depqid."]</a>";
			}	
           $questionsummary .= "</td></tr>";        
		}
		$qid_attributes=getQuestionAttributes($qid);
        $questionsummary .= "</table>";        
	}
}

if (returnglobal('viewanswer'))
{
	$_SESSION['FileManagerContext']="edit:answer:$surveyid";	
	// Get languages select on survey.
	$anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
	$baselang = GetBaseLanguageFromSurveyID($surveyid);

    // check that there are answers for every language supported by the survey
    foreach ($anslangs as $language)
    {
        $qquery = "SELECT count(*) as num_ans  FROM ".db_table_name('answers')." WHERE qid=$qid AND language='".$language."'";
        $qresult = db_execute_assoc($qquery); //Checked
        $qrow = $qresult->FetchRow(); 
        if ($qrow["num_ans"] == 0)   // means that no record for the language exists in the answers table
        {
            $qquery = "INSERT INTO ".db_table_name('answers')." (SELECT `qid`,`code`,`answer`,`default_value`,`sortorder`, '".$language."' FROM ".db_table_name('answers')." WHERE qid=$qid AND language='".$baselang."')";
            $connect->Execute($qquery); //Checked
        }
    }

    array_unshift($anslangs,$baselang);      // makes an array with ALL the languages supported by the survey -> $anslangs
    
    //delete the answers in languages not supported by the survey
    $qquery = "SELECT DISTINCT language FROM ".db_table_name('answers')." WHERE (qid = $qid) AND (language NOT IN ('".implode("','",$anslangs)."'))";
    $qresult = db_execute_assoc($qquery); //Checked
    while ($qrow = $qresult->FetchRow())
    {
        $qquery = "DELETE FROM ".db_table_name('answers')." WHERE (qid = $qid) AND (language = '".$qrow["language"]."')";
        $connect->Execute($qquery); //Checked
    }
    
	
	// Check sort order for answers
	$qquery = "SELECT type FROM ".db_table_name('questions')." WHERE qid=$qid AND language='".$baselang."'";
	$qresult = db_execute_assoc($qquery); //Checked
	while ($qrow=$qresult->FetchRow()) {$qtype=$qrow['type'];}
	if (!isset($_POST['ansaction']))
	{
		//check if any nulls exist. If they do, redo the sortorders
		$caquery="SELECT * FROM ".db_table_name('answers')." WHERE qid=$qid AND sortorder is null AND language='".$baselang."'";
		$caresult=$connect->Execute($caquery); //Checked
		$cacount=$caresult->RecordCount();
		if ($cacount)
		{
			fixsortorderAnswers($qid); // !!Adjust this!!
		}
	}

	// Print Key Control JavaScript
	$vasummary = PrepareEditorScript("editanswer");

     $query = "SELECT sortorder FROM ".db_table_name('answers')." WHERE qid='{$qid}' AND language='".GetBaseLanguageFromSurveyID($surveyid)."' ORDER BY sortorder desc";
     $result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked
     $anscount = $result->RecordCount();	
     $row=$result->FetchRow();
     $maxsortorder=$row['sortorder']+1;
     $vasummary .= "<table width='100%' >\n"
	."<tr  >\n"
	."<td colspan='4' class='settingcaption'>\n"
	.$clang->gT("Edit Answers")
	."</td>\n"
	."</tr>\n"
	."<tr><td colspan='5'><form name='editanswers' method='post' action='$scriptname'onsubmit=\"return codeCheck('code_',$maxsortorder,'".$clang->gT("Error: You are trying to use duplicate answer codes.",'js')."');\">\n"
	. "<input type='hidden' name='sid' value='$surveyid' />\n"
	. "<input type='hidden' name='gid' value='$gid' />\n"
	. "<input type='hidden' name='qid' value='$qid' />\n"
	. "<input type='hidden' name='viewanswer' value='Y' />\n"
	. "<input type='hidden' name='sortorder' value='' />\n"
	. "<input type='hidden' name='action' value='modanswer' />\n";
	$vasummary .= "<div class='tab-pane' id='tab-pane-1'>";
	$first=true;
	$sortorderids=''; 
	$codeids='';

	$vasummary .= "<div id='xToolbar'></div>\n";

	foreach ($anslangs as $anslang)
	{
		$position=0;
    	$query = "SELECT * FROM ".db_table_name('answers')." WHERE qid='{$qid}' AND language='{$anslang}' ORDER BY sortorder, code";
		$result = db_execute_assoc($query) or safe_die($connect->ErrorMsg()); //Checked
		$anscount = $result->RecordCount();
        $vasummary .= "<div class='tab-page'>"
                ."<h2 class='tab'>".getLanguageNameFromCode($anslang, false);
        if ($anslang==GetBaseLanguageFromSurveyID($surveyid)) {$vasummary .= '('.$clang->gT("Base Language").')';}
                
        $vasummary .= "</h2><table width='100%' style='border: solid; border-width: 0px; border-color: #555555' cellspacing='0'>\n"
                ."<thead align='center'>"
        		."<tr bgcolor='#F8F8FF'>\n"
        		."<td width='20%' align='right'><strong><font size='1' face='verdana' >\n"
        		.$clang->gT("Code")
        		."</font></strong></td>\n"
        		."<td width='50%'><strong><font size='1' face='verdana'>\n"
        		.$clang->gT("Answer")
        		."</font></strong></td>\n"
        		."<td width='20%'><strong><font size='1' face='verdana'>\n"
        		.$clang->gT("Action")
        		."</font></strong></td>\n"
        		."<td width='10%' align='center'><strong><font size='1' face='verdana'>\n"
        		.$clang->gT("Order")
        		."</font></strong>";
              	
        		
        		
                $vasummary .= "</td>\n"
        		."</tr></thead>"
                ."<tbody align='center'>";
		while ($row=$result->FetchRow())
		{
			$row['code'] = htmlspecialchars($row['code']);
			$row['answer']=htmlspecialchars($row['answer']);
			
			$sortorderids=$sortorderids.' '.$row['language'].'_'.$row['sortorder'];
			if ($first) {$codeids=$codeids.' '.$row['sortorder'];}
			
			$vasummary .= "<tr><td width='20%' align='right'>\n";
			if ($row['default_value'] == 'Y') 
            {     
                $vasummary .= "<font color='#FF0000'>".$clang->gT("Default")."</font>"
  			                       ."<input type='hidden' name='default_answer' value=\"{$row['sortorder']}\" />";
            }

			if (($activated != 'Y' && $first) || ($activated == 'Y' && $first && (($qtype=='O')  || ($qtype=='L') || ($qtype=='!') ))) 
			{
				$vasummary .= "<input type='text' id='code_{$row['sortorder']}' name='code_{$row['sortorder']}' value=\"{$row['code']}\" maxlength='5' size='5'"
				."onkeypress=\" if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_$anslang').click(); return false;} return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_')\""
				." />";
				$vasummary .= "<input type='hidden' id='previouscode_{$row['sortorder']}' name='previouscode_{$row['sortorder']}' value=\"{$row['code']}\" />";
			}
			elseif (($activated != 'N' && $first) ) // If survey is activated and its not one of the above question types who allows modfying answers on active survey
			{
				$vasummary .= "<input type='hidden' name='code_{$row['sortorder']}' value=\"{$row['code']}\" maxlength='5' size='5'"
				." />{$row['code']}";
				$vasummary .= "<input type='hidden' id='previouscode_{$row['sortorder']}' name='previouscode_{$row['sortorder']}' value=\"{$row['code']}\" />";
				
			}
			else
			{
				$vasummary .= "{$row['code']}";
			
			}

			$vasummary .= "</td>\n"
			."<td width='50%'>\n"
			."<input type='text' name='answer_{$row['language']}_{$row['sortorder']}' maxlength='1000' size='80' value=\"{$row['answer']}\" onkeypress=\" if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_$anslang').click(); return false;}\" />\n"
			. getEditor("editanswer","answer_".$row['language']."_".$row['sortorder'], "[".$clang->gT("Answer:", "js")."](".$row['language'].")",$surveyid,$gid,$qid,'editanswer')
			."</td>\n"
			."<td width='20%'>\n";
			
			// Deactivate delete button for active surveys
			if ($activated != 'Y' || ($activated == 'Y' && (($qtype=='O' ) || ($qtype=='L' ) ||($qtype=='!' ))))
			{
				$vasummary .= "<input type='submit' name='method' value='".$clang->gT("Del")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
			}
			else
			{
				$vasummary .= "<input type='submit' disabled='disabled 'name='method' value='".$clang->gT("Del")."' />\n";
			}

			// Don't show Default Button for array question types
			if ($qtype != "A" && $qtype != "B" && $qtype != "C" && $qtype != "E" && $qtype != "F" && $qtype != "H" && $qtype != "R" && $qtype != "Q" && $qtype != "1") $vasummary .= "<input type='submit' name='method' value='".$clang->gT("Default")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
			$vasummary .= "</td>\n"
			."<td>\n";
			if ($position > 0)
			{
				$vasummary .= "<input type='submit' name='method' value='".$clang->gT("Up")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
			};
			if ($position < $anscount-1)
			{
				// Fill the sortorder hiddenfield so we now what field is moved down
				$vasummary .= "<input type='submit' name='method' value='".$clang->gT("Dn")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
			}
			$vasummary .= "</td></tr>\n";
			$position++;
		}
        ++$anscount;
		if ($anscount > 0)
		{
			$vasummary .= "<tr><td colspan='4'><center>"
   			."<input type='submit' id='saveallbtn_$anslang' name='method' value='".$clang->gT("Save All")."' />\n"
			."</center></td></tr>\n";
		}
		$position=sprintf("%05d", $position);
		if ($activated != 'Y' || (($activated == 'Y') && (($qtype=='O' ) || ($qtype=='L' ) ||($qtype=='!' ))))
		{
			
            if ($first==true)
			{                                                                                                  
				$vasummary .= "<tr><td><br /></td></tr><tr><td width='20%' align='right'>"
				."<strong>".$clang->gT("New Answer").":</strong> ";
                if (!isset($_SESSION['nextanswercode'])) $_SESSION['nextanswercode']='';
				$vasummary .= "<input type='text' name='insertcode' value=\"{$_SESSION['nextanswercode']}\" id='code_".$maxsortorder."' maxlength='5' size='5' "
				." onkeypress=\" if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('newanswerbtn').click(); return false;} return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_')\""
				." />";
                unset($_SESSION['nextanswercode']);


            	$first=false;
				$vasummary .= "</td>\n"
				."<td width='50%'>\n"
				."<input type='text' maxlength='1000' name='insertanswer' size='80' onkeypress=\" if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('newanswerbtn').click(); return false;}\" />\n"
				. getEditor("addanswer","insertanswer", "[".$clang->gT("Answer:", "js")."]",'','','',$action)
				."</td>\n"
				."<td width='20%'>\n"
				."<input type='submit' id='newanswerbtn' name='method' value='".$clang->gT("Add new Answer")."' />\n"
				."<input type='hidden' name='action' value='modanswer' />\n"
				."</td>\n"
				."<td>\n"
				."<script type='text/javascript'>\n"
				."<!--\n"
				."document.getElementById('code_".$maxsortorder."').focus();\n"
				."//-->\n"
				."</script>\n"
				."</td>\n"
				."</tr>\n";
			}
		}
		else
		{
			$vasummary .= "<tr>\n"
			."<td colspan='4' align='center'>\n"
			."<font color='red' size='1'><i><strong>"
			.$clang->gT("Warning")."</strong>: ".$clang->gT("You cannot add answers or edit answer codes for this question type because the survey is active.")."</i></font>\n"
			."</td>\n"
			."</tr>\n";
		}
		$first=false;
		$vasummary .= "</tbody></table>\n";
		$vasummary .=  "<input type='hidden' name='sortorderids' value='$sortorderids' />\n";
		$vasummary .=  "<input type='hidden' name='codeids' value='$codeids' />\n";
		$vasummary .= "</div>";
	}
	$vasummary .= "</div></form></td></tr></table>";


}

// *************************************************
// Survey Rights Start	****************************
// *************************************************

if($action == "addsurveysecurity")
{
	$addsummary = "<br /><strong>".$clang->gT("Add User")."</strong><br />\n";

	$query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID']." AND owner_id != ".$postuserid;
	$result = db_execute_assoc($query); //Checked
	if( ($result->RecordCount() > 0 && in_array($postuserid,getuserlist('onlyuidarray'))) || 
		$_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
		if($postuserid > 0){

			$isrquery = "INSERT INTO {$dbprefix}surveys_rights VALUES($surveyid,". $postuserid.",0,0,0,0,0,0)";
			$isrresult = $connect->Execute($isrquery); //Checked

			if($isrresult)
			{
				$addsummary .= "<br />".$clang->gT("User added.")."<br />\n";
				$addsummary .= "<br /><form method='post' action='$scriptname?sid={$surveyid}'>"
				."<input type='submit' value='".$clang->gT("Set Survey Rights")."' />"
				."<input type='hidden' name='action' value='setsurveysecurity' />"
				."<input type='hidden' name='uid' value='{$postuserid}' />"
				."</form>\n";
			}
			else
			{
				// Username already exists.
				$addsummary .= "<br /><strong>".$clang->gT("Failed to add User.")."</strong><br />\n" . " " . $clang->gT("Username already exists.")."<br />\n";
			}
			$addsummary .= "<br /><a href='$scriptname?action=surveysecurity&amp;sid={$surveyid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
		else
		{
			$addsummary .= "<br /><strong>".$clang->gT("Failed to add User.")."</strong><br />\n" . " " . $clang->gT("No Username selected.")."<br />\n";
			$addsummary .= "<br /><a href='$scriptname?action=surveysecurity&amp;sid={$surveyid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	}
	else
	{
		include("access_denied.php");
	}
}


if($action == "addusergroupsurveysecurity")
{
	$addsummary = "<br /><strong>".$clang->gT("Add User Group")."</strong><br />\n";

	$query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID'];
	$result = db_execute_assoc($query); //Checked
	if( ($result->RecordCount() > 0 && in_array($postusergroupid,getsurveyusergrouplist('simpleugidarray')) ) ||
	     $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
		if($postusergroupid > 0){
			$query2 = "SELECT b.uid FROM (SELECT uid FROM ".db_table_name('surveys_rights')." WHERE sid = {$surveyid}) AS c RIGHT JOIN ".db_table_name('user_in_groups')." AS b ON b.uid = c.uid WHERE c.uid IS NULL AND b.ugid = {$postusergroupid}";
			$result2 = db_execute_assoc($query2); //Checked
			if($result2->RecordCount() > 0)
			{
				while ($row2 = $result2->FetchRow())
				{
					$uid_arr[] = $row2['uid'];
					$values[] = "($surveyid, {$row2['uid']},0,0,0,0,0,0)";
				}
				$values_implode = implode(",", $values);

				$isrquery = "INSERT INTO {$dbprefix}surveys_rights VALUES ".$values_implode;
				$isrresult = $connect->Execute($isrquery); //Checked

				if($isrresult)
				{
					$addsummary .= "<br />".$clang->gT("User Group added.")."<br />\n";
					$_SESSION['uids'] = $uid_arr;
					$addsummary .= "<br /><form method='post' action='$scriptname?sid={$surveyid}'>"
					."<input type='submit' value='".$clang->gT("Set Survey Rights")."' />"
					."<input type='hidden' name='action' value='setusergroupsurveysecurity' />"
					."<input type='hidden' name='ugid' value='{$postusergroupid}' />"
					."</form>\n";
				}
			}
			else
			{
				// no user to add
				$addsummary .= "<br /><strong>".$clang->gT("Failed to add User Group.")."</strong><br />\n";
			}
			$addsummary .= "<br /><a href='$scriptname?action=surveysecurity&amp;sid={$surveyid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
		else
		{
			$addsummary .= "<br /><strong>".$clang->gT("Failed to add User.")."</strong><br />\n" . " " . $clang->gT("No Username selected.")."<br />\n";
			$addsummary .= "<br /><a href='$scriptname?action=surveysecurity&amp;sid={$surveyid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
		}
	}
	else
	{
		include("access_denied.php");
	}
}

if($action == "delsurveysecurity"){
	{
		$addsummary = "<br /><strong>".$clang->gT("Deleting User")."</strong><br />\n";

		$query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID']." AND owner_id != ".$postuserid;
		$result = db_execute_assoc($query); //Checked
		if($result->RecordCount() > 0 || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
		{
			if (isset($postuserid))
			{
				$dquery="DELETE FROM {$dbprefix}surveys_rights WHERE uid={$postuserid} AND sid={$surveyid}";	//	added by Dennis
				$dresult=$connect->Execute($dquery); //Checked

				$addsummary .= "<br />".$clang->gT("Username").": ".sanitize_xss_string($_POST['user'])."<br />\n";
			}
			else
			{
				$addsummary .= "<br />".$clang->gT("Could not delete user. User was not supplied.")."<br />\n";
			}
		}
		else
		{
			include("access_denied.php");
		}
		$addsummary .= "<br /><br /><a href='$scriptname?sid={$surveyid}&amp;action=surveysecurity'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
	}
}

if($action == "setsurveysecurity")
{
	$query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID']." AND owner_id != ".$postuserid;
	$result = db_execute_assoc($query); //Checked
	if($result->RecordCount() > 0 || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
		$query2 = "SELECT uid, edit_survey_property, define_questions, browse_response, export, delete_survey, activate_survey FROM ".db_table_name('surveys_rights')." WHERE sid = {$surveyid} AND uid = ".$postuserid;
		$result2 = db_execute_assoc($query2); //Checked

		if($result2->RecordCount() > 0)
		{
			$resul2row = $result2->FetchRow();

			$usersummary = "<table width='100%' border='0'>\n<tr><td colspan='6' class='header'>\n"
			. "".$clang->gT("Set Survey Rights")."</td></tr>\n";

			$usersummary .= "<th align='center'>".$clang->gT("Edit Survey Properties")."</th>\n"
			. "<th align='center'>".$clang->gT("Define Questions")."</th>\n"
			. "<th align='center'>".$clang->gT("Browse Responses")."</th>\n"
			. "<th align='center'>".$clang->gT("Export")."</th>\n"
			. "<th align='center'>".$clang->gT("Delete Survey")."</th>\n"
			. "<th align='center'>".$clang->gT("Activate Survey")."</th>\n"
			. "</tr>\n"
			. "<form action='$scriptname?sid={$surveyid}' method='post'>\n";

			//content
			$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"edit_survey_property\" value=\"edit_survey_property\"";
			if($resul2row['edit_survey_property']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"define_questions\" value=\"define_questions\"";
			if($resul2row['define_questions']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"browse_response\" value=\"browse_response\"";
			if($resul2row['browse_response']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"export\" value=\"export\"";
			if($resul2row['export']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"delete_survey\" value=\"delete_survey\"";
			if($resul2row['delete_survey']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"activate_survey\" value=\"activate_survey\"";
			if($resul2row['activate_survey']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";

			$usersummary .= "\n<tr><td colspan='6' align='center'>"
			."<input type='submit' value='".$clang->gT("Save Now")."' />"
			."<input type='hidden' name='action' value='surveyrights' />"
			."<input type='hidden' name='uid' value='{$postuserid}' /></td></tr>"
			."</form>"
			. "</table>\n";
		}
	}
	else
	{
		include("access_denied.php");
	}
}


if($action == "setusergroupsurveysecurity")
{
	$query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID'];//." AND owner_id != ".$postuserid;
	$result = db_execute_assoc($query); //Checked
	if($result->RecordCount() > 0 || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
		$usersummary = "<table width='100%' border='0'>\n<tr><td colspan='6' class='header'>\n"
		. "".$clang->gT("Set Survey Rights")."</td></tr>\n";

		$usersummary .= "<th align='center'>".$clang->gT("Edit Survey Property")."</th>\n"
		. "<th align='center'>".$clang->gT("Define Questions")."</th>\n"
		. "<th align='center'>".$clang->gT("Browse Response")."</th>\n"
		. "<th align='center'>".$clang->gT("Export")."</th>\n"
		. "<th align='center'>".$clang->gT("Delete Survey")."</th>\n"
		. "<th align='center'>".$clang->gT("Activate Survey")."</th>\n"
		. "</tr>\n"
		. "<form action='$scriptname?sid={$surveyid}' method='post'>\n";

		//content
		$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"edit_survey_property\" value=\"edit_survey_property\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"define_questions\" value=\"define_questions\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"browse_response\" value=\"browse_response\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"export\" value=\"export\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"delete_survey\" value=\"delete_survey\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"activate_survey\" value=\"activate_survey\"";

		$usersummary .=" /></td>\n";

		$usersummary .= "\n<tr><td colspan='6' align='center'>"
		."<input type='submit' value='".$clang->gT("Save Now")."' />"
		."<input type='hidden' name='action' value='surveyrights' />"
		."<input type='hidden' name='ugid' value='{$postusergroupid}' /></td></tr>"
		."</form>"
		. "</table>\n";
	}
	else
	{
		include("access_denied.php");
	}
}

// This is the action to export the structure of a complete survey
if($action == "exportstructure")
{
    if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['export'])
    {
    $exportstructure = "<form name='exportstructure' action='$scriptname' method='post'>\n" 
    ."<table width='100%' border='0' >\n<tr><td class='settingcaption'>"
    .$clang->gT("Export Survey Structure")."\n</td></tr>\n"
    ."<tr>\n"
    ."<td style='text-align:center;'>\n"
    ."<br /><input type='radio' class='radiobtn' name='type' value='structurecsv' checked='checked' id='surveycsv' onclick=\"this.form.action.value='exportstructurecsv'\";/>"
    ."<label for='surveycsv'>"
    .$clang->gT("LimeSurvey Survey File (*.csv)")."</label><br />\n"
    ."<input type='radio' class='radiobtn' name='type' value='structurequeXML'  id='queXML' onclick=\"this.form.action.value='exportstructurequexml'\"";
    $exportstructure.="/>"
    ."<label for='queXML'>"
    .$clang->gT("queXML Survey XML Format (*.xml)")." ";
    
    $exportstructure.="</label>\n"
    ."<br />&nbsp;</td>\n"
    ."</tr>\n"
    ."<tr><td height='2' bgcolor='silver'></td></tr>\n"
    ."<tr>\n"
    ."<td align='center'>\n"
    ."<input type='submit' value='"
    .$clang->gT("Export To File")."' />\n"
    ."<input type='hidden' name='sid' value='$surveyid' />\n"
    ."<input type='hidden' name='action' value='exportstructurecsv' />\n"
    ."</td>\n"
    ."</tr>\n"
    ."</table><br /></from>\n";
    }
}


if($action == "surveysecurity")
{
	$query = "SELECT sid FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID'];
	$result = db_execute_assoc($query); //Checked
	if($result->RecordCount() > 0 || $_SESSION['USER_RIGHT_SUPERADMIN'] == 1)
	{
		$query2 = "SELECT a.uid, b.users_name FROM ".db_table_name('surveys_rights')." AS a INNER JOIN ".db_table_name('users')." AS b ON a.uid = b.uid WHERE a.sid = {$surveyid} AND b.uid != ".$_SESSION['loginID'] ." ORDER BY b.users_name";
		$result2 = db_execute_assoc($query2); //Checked
		$surveysecurity = "<table width='100%' rules='rows' border='1' class='table2columns'>\n<tr><td colspan='3' align='center' class='settingcaption'>\n"
		. "<strong>".$clang->gT("Survey Security")."</strong></td></tr>\n"
		. "<tr>\n"
		. "<th>".$clang->gT("Username")."</th>\n"
		. "<th>".$clang->gT("User Group")."</th>\n"
		. "<th>".$clang->gT("Action")."</th>\n"
		. "</tr>\n";
		
		if (isset($usercontrolSameGroupPolicy) &&
			$usercontrolSameGroupPolicy === true)
		{
			$authorizedGroupsList=getusergrouplist('simplegidarray');
		}

		if($result2->RecordCount() > 0)
		{
			//	output users
			$row = 0;
			while ($resul2row = $result2->FetchRow())
			{
				$query3 = "SELECT a.ugid FROM ".db_table_name('user_in_groups')." AS a RIGHT OUTER JOIN ".db_table_name('users')." AS b ON a.uid = b.uid WHERE b.uid = ".$resul2row['uid'];
				$result3 = db_execute_assoc($query3); //Checked
				while ($resul3row = $result3->FetchRow())
				{
					if (!isset($usercontrolSameGroupPolicy) ||
						$usercontrolSameGroupPolicy === false ||
						in_array($resul3row['ugid'],$authorizedGroupsList))
					{
						$group_ids[] = $resul3row['ugid'];
					}
				}
				
				if(isset($group_ids) && $group_ids[0] != NULL)
				{				
					$group_ids_query = implode(" OR ugid=", $group_ids);
					unset($group_ids);
	
					$query4 = "SELECT name FROM ".db_table_name('user_groups')." WHERE ugid = ".$group_ids_query;
					$result4 = db_execute_assoc($query4); //Checked
					
					while ($resul4row = $result4->FetchRow())
					{
						$group_names[] = $resul4row['name'];
					}
					if(count($group_names) > 0)
					$group_names_query = implode(", ", $group_names);
				}
//                  else {break;} //TODO Commented by lemeur
				if(($row % 2) == 0)
					$surveysecurity .= "<tr>\n";
				else
					$surveysecurity .= "<tr>\n";

				$surveysecurity .= "<td align='center'>{$resul2row['users_name']}\n"
								 . "<td align='center'>";
					
				if(isset($group_names) > 0)
				{
					$surveysecurity .= $group_names_query;
				}
				else
				{
					$surveysecurity .= "---";
				}
				unset($group_names);

				$surveysecurity .= "</td>\n"
				. "<td align='center' style='padding-top:10px;'>\n";

				$surveysecurity .= "<form method='post' action='$scriptname?sid={$surveyid}'>"
				."<input type='submit' value='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry?","js")."\")' />"
				."<input type='hidden' name='action' value='delsurveysecurity' />"
				."<input type='hidden' name='user' value='{$resul2row['users_name']}' />"
				."<input type='hidden' name='uid' value='{$resul2row['uid']}' />"
				."</form>";

				$surveysecurity .= "<form method='post' action='$scriptname?sid={$surveyid}'>"
				."<input type='submit' value='".$clang->gT("Set Survey Rights")."' />"
				."<input type='hidden' name='action' value='setsurveysecurity' />"
				."<input type='hidden' name='user' value='{$resul2row['users_name']}' />"
				."<input type='hidden' name='uid' value='{$resul2row['uid']}' />"
				."</form>\n";

				$surveysecurity .= "</td>\n"
				. "</tr>\n";
				$row++;
			}
		}
		$surveysecurity .= "<form action='$scriptname?sid={$surveyid}' method='post'>\n"
		. "<tr>\n"

		. "<td colspan='2' align='right'>"
		. "<strong>".$clang->gT("User").": </strong><select name='uid'>\n"
		//. $surveyuserselect
		. getsurveyuserlist()
		. "</select>\n"
		. "</td>\n"

		. "<td align='center'><input type='submit' value='".$clang->gT("Add User")."' />"
		. "<input type='hidden' name='action' value='addsurveysecurity' /></td></form>\n"
		. "</tr>\n";
		//. "</table>\n";

		$surveysecurity .= "<form action='$scriptname?sid={$surveyid}' method='post'>\n"
		. "<tr>\n"

		. "<td colspan='2' align='right'>"
		. "<strong>".$clang->gT("Groups").": </strong><select name='ugid'>\n"
		//. $surveyuserselect
		. getsurveyusergrouplist()
		. "</select>\n"
		. "</td>\n"

		. "<td align='center'><input type='submit' value='".$clang->gT("Add User Group")."' />"
		. "<input type='hidden' name='action' value='addusergroupsurveysecurity' /></td></form>\n"
		. "</tr>\n"
		. "</table>\n";
	}
	else
	{
		include("access_denied.php");
	}
}

elseif ($action == "surveyrights")
{
	$addsummary = "<br /><strong>".$clang->gT("Set Survey Rights")."</strong><br />\n";

	if(isset($postuserid)){
		$query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} ";
        if ($_SESSION['USER_RIGHT_SUPERADMIN'] != 1)  
        {
            $query.=" AND owner_id != ".$postuserid." AND owner_id = ".$_SESSION['loginID'];
        }
    }
	else{
		$query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID'];
	}
	$result = db_execute_assoc($query); //Checked
	if($result->RecordCount() > 0)
	{
		$rights = array();

		if(isset($_POST['edit_survey_property']))$rights['edit_survey_property']=1;	else $rights['edit_survey_property']=0;
		if(isset($_POST['define_questions']))$rights['define_questions']=1;			else $rights['define_questions']=0;
		if(isset($_POST['browse_response']))$rights['browse_response']=1;			else $rights['browse_response']=0;
		if(isset($_POST['export']))$rights['export']=1;								else $rights['export']=0;
		if(isset($_POST['delete_survey']))$rights['delete_survey']=1;				else $rights['delete_survey']=0;
		if(isset($_POST['activate_survey']))$rights['activate_survey']=1;			else $rights['activate_survey']=0;

		if(isset($postuserid)){
			$uids[] = $postuserid;
		}
		else{
			$uids = $_SESSION['uids'];
			unset($_SESSION['uids']);
		}
		if(setsurveyrights($uids, $rights))
		{
			$addsummary .= "<br />".$clang->gT("Update survey rights successful.")."<br />\n";
		}
		else
		{
			$addsummary .= "<br /><strong>".$clang->gT("Failed to update survey rights!")."</strong><br />\n";
		}
		$addsummary .= "<br /><br /><a href='$scriptname?sid={$surveyid}&amp;action=surveysecurity'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
	}
	else
	{
		include("access_denied.php");
	}
}

// *************************************************
// Survey Rights End	****************************
// *************************************************


// Editing the survey
if ($action == "editsurvey")
{
	if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['edit_survey_property'])
	{
		$esquery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid";
		$esresult = db_execute_assoc($esquery); //Checked
		while ($esrow = $esresult->FetchRow())
		{
			$esrow = array_map('htmlspecialchars', $esrow);
			$editsurvey = include2var('./scripts/addremove.js');
			$editsurvey .= "<form id='addnewsurvey' name='addnewsurvey' action='$scriptname' method='post'>\n";

			// header
			$editsurvey .= "<table width='100%' border='0'>\n<tr><td colspan='4' class='settingcaption'>"
			. "".$clang->gT("Edit Survey - Step 1 of 2")."</td></tr></table>\n";


			// beginning TABs section
			$editsurvey .= "<div class='tab-pane' id='tab-pane-1'>\n";
			// General & Contact TAB
			$editsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("General")."</h2>\n";

			// Base Language
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Base Language:")."</span>\n"
			. "<span class='settingentry'>\n".GetLanguageNameFromCode($esrow['language'])
			. "</span></div>\n"

			// Additional languages listbox
			. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Additional Languages").":</span>\n"
			. "<span class='settingentry'><table><tr><td align='left'><select multiple='multiple' style='min-width:250px;'  size='5' id='additional_languages' name='additional_languages'>";
			$jsX=0;
			$jsRemLang ="<script type=\"text/javascript\">\nvar mylangs = new Array() \n";

			foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname)
			{
				if ($langname && $langname!=$esrow['language']) // base languag must not be shown here
				{
					$jsRemLang .="mylangs[$jsX] = \"$langname\"\n";
					$editsurvey .= "<option id='".$langname."' value='".$langname."'";
					$editsurvey .= ">".getLanguageNameFromCode($langname)."</option>\n";
					$jsX++;
				}
			}
			$jsRemLang .= "</script>\n";
			$editsurvey .= $jsRemLang;
			//  Add/Remove Buttons
			$editsurvey .= "</select></td>"
			. "<td align='left'><input type=\"button\" value=\"<< ".$clang->gT("Add")."\" onclick=\"DoAdd()\" id=\"AddBtn\" /><br /> <input type=\"button\" value=\"".$clang->gT("Remove")." >>\" onclick=\"DoRemove(0,'')\" id=\"RemoveBtn\"  /></td>\n"

			// Available languages listbox
			. "<td align='left'><select size='5' id='available_languages' name='available_languages'>";
			$tempLang=GetAdditionalLanguagesFromSurveyID($surveyid);
			foreach (getLanguageData() as  $langkey2=>$langname)
			{
				if ($langkey2!=$esrow['language'] && in_array($langkey2,$tempLang)==false)  // base languag must not be shown here
				{
					$editsurvey .= "<option id='".$langkey2."' value='".$langkey2."'";
					$editsurvey .= ">".$langname['description']." - ".$langname['nativedescription']."</option>\n";
				}
			}
			$editsurvey .= "</select></td>"
			. " </tr></table></span></div>\n";

			$editsurvey .= "";


			// Administrator...
			$editsurvey .= ""
			. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Administrator:")."</span>\n"
			. "<span class='settingentry'><input type='text' size='50' name='admin' value=\"{$esrow['admin']}\" /></span></div>\n"
			. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Admin Email:")."</span>\n"
			. "<span class='settingentry'><input type='text' size='50' name='adminemail' value=\"{$esrow['adminemail']}\" /></span></div>\n"
			. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Bounce Email:")."</span>\n"
			. "<span class='settingentry'><input type='text' size='50' name='bounce_email' value=\"{$esrow['bounce_email']}\" /></span></div>\n"
			. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Fax To:")."</span>\n"
			. "<span class='settingentry'><input type='text' size='50' name='faxto' value=\"{$esrow['faxto']}\" /></span></div>\n";

		// End General TAB
		// Create Survey Button 
//		$editsurvey .= "<div class='settingrow'><span class='settingcaption'></span><span class='settingentry'><input type='button' onclick='javascript:document.getElementById(\"addnewsurvey\").submit();' value='".$clang->gT("Create Survey")."' /></span></div>\n";
		$editsurvey .= "</div>\n";

		// Presentation and navigation TAB
		$editsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Presentation & Navigation")."</h2>\n";

			//Format
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Format:")."</span>\n"
			. "<span class='settingentry'><select name='format'>\n"
			. "<option value='S'";
			if ($esrow['format'] == "S" || !$esrow['format']) {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Question by Question")."</option>\n"
			. "<option value='G'";
			if ($esrow['format'] == "G") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Group by Group")."</option>\n"
			. "<option value='A'";
			if ($esrow['format'] == "A") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("All in one")."</option>\n"
			. "</select></span>\n"
			. "</div>\n";

			//TEMPLATES
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Template:")."</span>\n"
			. "<span class='settingentry'><select name='template'  "
            . " onkeyup='this.onchange();' onchange='document.getElementById(\"preview\").src=\"".$publicurl."/templates/\"+this.value+\"/preview.png\";'>\n";
			foreach (gettemplatelist() as $tname)
			{
				
				 if ($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] == 1 || hasTemplateManageRights($_SESSION["loginID"], $tname) == 1 )
				 {
                	$editsurvey .= "<option value='$tname'";
                    if ($esrow['template'] && htmlspecialchars($tname) == $esrow['template']) {$editsurvey .= " selected='selected'";}
                    elseif (!$esrow['template'] && $tname == "default") {$editsurvey .= " selected='selected'";}
                    $editsurvey .= ">$tname</option>\n";
                }

			}
			$editsurvey .= "</select> </span>\n"
            . "</div>\n";
            
            $editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Template Preview:")."</span>\n"
            . "<span class='settingentry'><img id='preview' src='$publicurl/templates/{$esrow['template']}/preview.png'>\n"
            . "</span>\n"
            . "</div>\n";

			//ALLOW SAVES
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Allow Saves?")."</span>\n"
			. "<span class='settingentry'><select name='allowsave'>\n"
			. "<option value='Y'";
			if (!$esrow['allowsave'] || $esrow['allowsave'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "<option value='N'";
			if ($esrow['allowsave'] == "N") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "</select></span>\n"
			. "</div>\n";

			//ALLOW PREV
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Show [<< Prev] button")."</span>\n"
			. "<span class='settingentry'><select name='allowprev'>\n"
			. "<option value='Y'";
			if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "<option value='N'";
			if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "</select></span>\n"
			. "</div>\n";

            //Result printing
            $editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Participants may print answers?")."</span>\n"
            . "<span class='settingentry'><select name='printanswers'>\n"
            . "<option value='Y'";
            if (!isset($esrow['printanswers']) || !$esrow['printanswers'] || $esrow['printanswers'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if (isset($esrow['printanswers']) && $esrow['printanswers'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></span>\n"
            . "</div>\n";

            //Public Surveys
            $editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("List survey publicly:")."</span>\n"
            . "<span class='settingentry'><select name='public'>\n"
            . "<option value='Y'";
            if (!isset($esrow['listpublic']) || !$esrow['listpublic'] || $esrow['listpublic'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if (isset($esrow['listpublic']) && $esrow['listpublic'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "</select></span>\n"
            . "</div>\n";


			// End URL block
			$editsurvey .= ""
			. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("End URL:")."</span>\n"
			. "<span class='settingentry'><input type='text' size='50' name='url' value=\"{$esrow['url']}\" /></span></div>\n"
			. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Automatically load URL when survey complete?")."</span>\n"
			. "<span class='settingentry'><select name='autoredirect'>";
			$editsurvey .= "<option value='Y'";
			if (isset($esrow['autoredirect']) && $esrow['autoredirect'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n";
			$editsurvey .= "<option value='N'";
			if (!isset($esrow['autoredirect']) || $esrow['autoredirect'] != "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "</select></span></div>";


		// End Presention and navigation TAB
		// Create Survey Button 
//		$editsurvey .= "<div class='settingrow'><span class='settingcaption'></span><span class='settingentry'><input type='button' onclick='javascript:document.getElementById(\"addnewsurvey\").submit();' value='".$clang->gT("Create Survey")."' /></span></div>\n";
		$editsurvey .= "</div>\n";

		// Publication and access control TAB
		$editsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Publication & Access control")."</h2>\n";


            // Start date
            $editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Timed start?")."</span>\n"
            . "<span class='settingentry'><select name='usestartdate'><option value='Y'";
            if (isset($esrow['usestartdate']) && $esrow['usestartdate'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "<option value='N'";
            if (!isset($esrow['usestartdate']) || $esrow['usestartdate'] != "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option></select></span></div>"
            . "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Start date:")."</span>\n"
            . "<span class='settingentry'><input type='text' id='f_date_a' size='12' name='startdate' value=\"{$esrow['startdate']}\" /><button type='reset' id='f_trigger_a'>...</button></span></div>\n";


			// Expiration
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Expires?")."</span>\n"
			. "<span class='settingentry'><select name='useexpiry'><option value='Y'";
			if (isset($esrow['useexpiry']) && $esrow['useexpiry'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "<option value='N'";
			if (!isset($esrow['useexpiry']) || $esrow['useexpiry'] != "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option></select></span></div>"
			. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Expiry Date:")."</span>\n"
			. "<span class='settingentry'><input type='text' id='f_date_b' size='12' name='expires' value=\"{$esrow['expires']}\" /><button type='reset' id='f_trigger_b'>...</button></span></div>\n";

			//COOKIES
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Use Cookies?")."</span>\n"
			. "<span class='settingentry'><select name='usecookie'>\n"
			. "<option value='Y'";
			if ($esrow['usecookie'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "<option value='N'";
			if ($esrow['usecookie'] != "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "</select></span>\n"
			. "</div>\n";

			// Auto registration
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Allow public registration?")."</span>\n"
			. "<span class='settingentry'><select name='allowregister'>\n"
			. "<option value='Y'";
			if ($esrow['allowregister'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "<option value='N'";
			if ($esrow['allowregister'] != "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "</select></span>\n</div>\n";

	// Use Captcha 
        $editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Use CAPTCHA for").":</span>\n"
        . "<span class='settingentry'><select name='usecaptcha'>\n"
        . "<option value='A'";
	if ($esrow['usecaptcha'] == "A") {$editsurvey .= " selected='selected'";}
	$editsurvey .= ">".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
        . "<option value='B'";
	if ($esrow['usecaptcha'] == "B") {$editsurvey .= " selected='selected'";}

	$editsurvey .= ">".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ---------</option>\n"
        . "<option value='C'";
	if ($esrow['usecaptcha'] == "C") {$editsurvey .= " selected='selected'";}

	$editsurvey .= ">".$clang->gT("Survey Access")." / ------------ / ".$clang->gT("Save & Load")."</option>\n"
        . "<option value='D'";
	if ($esrow['usecaptcha'] == "D") {$editsurvey .= " selected='selected'";}

	$editsurvey .= ">------------- / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
	. "<option value='X'";

	if ($esrow['usecaptcha'] == "X") {$editsurvey .= " selected='selected'";}

	$editsurvey .= ">".$clang->gT("Survey Access")." / ------------ / ---------</option>\n"
	. "<option value='R'";
	if ($esrow['usecaptcha'] == "R") {$editsurvey .= " selected='selected'";}
	$editsurvey .= ">------------- / ".$clang->gT("Registration")." / ---------</option>\n"
	. "<option value='S'";
	if ($esrow['usecaptcha'] == "S") {$editsurvey .= " selected='selected'";}
	$editsurvey .= ">------------- / ------------ / ".$clang->gT("Save & Load")."</option>\n"
	. "<option value='N'";
	if ($esrow['usecaptcha'] == "N") {$editsurvey .= " selected='selected'";}
	$editsurvey .= ">------------- / ------------ / ---------</option>\n"

        . "</select></span>\n</div>\n";

			// token
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Token Attribute Names:")."</span>\n"
			. "<span class='settingentry'><input type='text' size='25' name='attribute1'"
			. " value=\"{$esrow['attribute1']}\" />(".$clang->gT("Attribute 1").")<br />"
			. "<input type='text' size='25' name='attribute2'"
			. " value=\"{$esrow['attribute2']}\" />(".$clang->gT("Attribute 2").")</span>\n</div>\n";

	// Email format
        $editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Use HTML format for token emails?")."</span>\n"
        . "<span class='settingentry'><select name='htmlemail' onchange=\"alert('".$clang->gT("If you switch email mode, you'll have to review your email templates to fit the new format","js")."');\">\n"
        . "<option value='Y'";
	if ($esrow['htmlemail'] == "Y") {$editsurvey .= " selected='selected'";}
	$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
        . "<option value='N'";
	if ($esrow['htmlemail'] == "N") {$editsurvey .= " selected='selected'";}

	$editsurvey .= ">".$clang->gT("No")."</option>\n"
        . "</select></span>\n</div>\n";

		// End Publication and access control TAB
		// Create Survey Button 
//		$editsurvey .= "<div class='settingrow'><span class='settingcaption'></span><span class='settingentry'><input type='button' onclick='javascript:document.getElementById(\"addnewsurvey\").submit();' value='".$clang->gT("Create Survey")."' /></span></div>\n";
		$editsurvey .= "</div>\n";

		// Notification and Data management TAB
		$editsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Notification & Data Management")."</h2>\n";


			//NOTIFICATION
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Admin Notification:")."</span>\n"
			. "<span class='settingentry'><select name='notification'>\n"
			. getNotificationlist($esrow['notification'])
			. "</select></span>\n"
			. "</div>\n";


			//ANONYMOUS
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Anonymous answers?")."\n";
			  // warning message if anonymous + tokens used
			$editsurvey .= "\n"
			. "<script type=\"text/javascript\"><!-- \n"
			. "function alertPrivacy()\n"
			. "{\n"
			. "if (document.getElementById('tokenanswerspersistence').value == 'Y')\n"
			. "{\n"
			. "alert('".$clang->gT("You can't use Anonymous answers when Token-based answers persistence is enabled.","js")."');\n"
			. "document.getElementById('private').value = 'N';\n"
			. "}\n"
			. "else if (document.getElementById('private').value == 'Y')\n"
			. "{\n"
			. "alert('".$clang->gT("Warning").": ".$clang->gT("If you turn on the -Anonymous answers- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.","js")."');\n"
			. "}\n"
			. "}"
			. "//--></script></span>\n";

			if ($esrow['active'] == "Y")
			{
				$editsurvey .= "<span class='settingentry'>\n";
				if ($esrow['private'] == "N") {$editsurvey .= " ".$clang->gT("This survey is NOT anonymous.");}
				else {$editsurvey .= $clang->gT("Answers to this survey are anonymized.");}
				$editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
				. "</font>\n";
				$editsurvey .= "<input type='hidden' name='private' value=\"{$esrow['private']}\" /></span>\n";
			}
			else
			{
				$editsurvey .= "<span class='settingentry'><select id='private' name='private' onchange='alertPrivacy();'>\n"
				. "<option value='Y'";
				if ($esrow['private'] == "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
				. "<option value='N'";
				if ($esrow['private'] != "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("No")."</option>\n"
				. "</select>\n</span>\n";
			}
			$editsurvey .= "</div>\n";

			// date stamp
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Date Stamp?")."</span>\n";
			if ($esrow['active'] == "Y")
			{
				$editsurvey .= "<span class='settingentry'>\n";
				if ($esrow['datestamp'] != "Y") {$editsurvey .= " ".$clang->gT("Responses will not be date stamped.");}
				else {$editsurvey .= $clang->gT("Responses will be date stamped.");}
				$editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
				. "</font>\n";
				$editsurvey .= "<input type='hidden' name='datestamp' value=\"{$esrow['datestamp']}\" /></span>\n";
			}
			else
			{
				$editsurvey .= "<span class='settingentry'><select id='datestamp' name='datestamp' onchange='alertPrivacy();'>\n"
				. "<option value='Y'";
				if ($esrow['datestamp'] == "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
				. "<option value='N'";
				if ($esrow['datestamp'] != "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("No")."</option>\n"
				. "</select>\n</span>\n";
			}
			$editsurvey .= "</div>\n";

			// Ip Addr
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Save IP Address?")."</span>\n";

			if ($esrow['active'] == "Y")
			{
				$editsurvey .= "<span class='settingentry'>\n";
				if ($esrow['ipaddr'] != "Y") {$editsurvey .= " ".$clang->gT("Responses will not have the IP address logged.");}
				else {$editsurvey .= $clang->gT("Responses will have the IP address logged");}
				$editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
				. "</font>\n";
				$editsurvey .= "<input type='hidden' name='ipaddr' value='".$esrow['ipaddr']."' />\n</span>";
			}
			else
			{
				$editsurvey .= "<span class='settingentry'><select name='ipaddr'>\n"
				. "<option value='Y'";
				if ($esrow['ipaddr'] == "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
				. "<option value='N'";
				if ($esrow['ipaddr'] != "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("No")."</option>\n"
				. "</select>\n</span>\n";
			}

			$editsurvey .= "</div>\n";

			// begin REF URL Block
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Save Referring URL?")."</span>\n";

			if ($esrow['active'] == "Y")
			{
				$editsurvey .= "<span class='settingentry'>\n";
				if ($esrow['refurl'] != "Y") {$editsurvey .= " ".$clang->gT("Responses will not have their referring URL logged.");}
				else {$editsurvey .= $clang->gT("Responses will have their referring URL logged.");}
				$editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
				. "</font>\n";
				$editsurvey .= "<input type='hidden' name='refurl' value='".$esrow['refurl']."' />\n</span>";
			}
			else
			{
				$editsurvey .= "<span class='settingentry'><select name='refurl'>\n"
				. "<option value='Y'";
				if ($esrow['refurl'] == "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
				. "<option value='N'";
				if ($esrow['refurl'] != "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("No")."</option>\n"
				. "</select>\n</span>\n";
			}
			$editsurvey .= "</div>\n";
			// BENBUN - END REF URL Block

		// Token answers persistence
		$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Enable Token-based answers persistence?")."</span>\n"
		. "<span class='settingentry'><select id='tokenanswerspersistence' name='tokenanswerspersistence' onchange=\"javascript: if (document.getElementById('private').value == 'Y') {alert('".$clang->gT("This option can't be set if Anonymous answers are used","js")."'); this.value='N';}\">\n" 
        . "<option value='Y'";
		if ($esrow['tokenanswerspersistence'] == "Y") {$editsurvey .= " selected='selected'";}
		$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
		. "<option value='N'";
		if ($esrow['tokenanswerspersistence'] == "N") {$editsurvey .= " selected='selected'";}
		$editsurvey .= ">".$clang->gT("No")."</option>\n"
		. "</select></span>\n</div>\n";

			// End Notification and Data management TAB
			// Create Survey Button
//			$editsurvey .= "<div class='settingrow'><span class='settingcaption'></span><span class='settingentry'><input type='button' onclick='javascript:document.getElementById(\"addnewsurvey\").submit();' value='".$clang->gT("Create Survey")."' /></span></div>\n";
			$editsurvey .= "</div>\n";

		// Ending First TABs Form
			$editsurvey .= ""
			. "<input type='hidden' name='action' value='updatesurvey' />\n"
			. "<input type='hidden' name='sid' value=\"{$esrow['sid']}\" />\n"
			. "<input type='hidden' name='languageids' id='languageids' value=\"{$esrow['additional_languages']}\" />\n"
			. "<input type='hidden' name='language' value=\"{$esrow['language']}\" />\n"
			."</form>";


		// TAB Uploaded Resources Management

		$ZIPimportAction = " onclick='if (validatefilename(this.form,\"".$clang->gT('Please select a file to import!','js')."\")) {this.form.submit();}'";
		if (!function_exists("zip_open"))
		{
			$ZIPimportAction = " onclick='alert(\"".$clang->gT("zip library not supported by PHP, Import ZIP Disabled","js")."\");'";
		}

		$disabledIfNoResources = '';
		if (hasResources($surveyid,'survey') === false)
		{
			$disabledIfNoResources = " disabled='disabled'";
		}

		$editsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Uploaded Resources Management")."</h2>\n"
		. "<form enctype='multipart/form-data' name='importsurvresources' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
		. "<input type='hidden' name='sid' value='$surveyid'>\n"
		. "<input type='hidden' name='action' value='importsurvresources'>\n"
		. "<table width='100%' class='form2columns'>\n"
		. "<tbody align='center'>"
		. "<tr><td></td><td>\n"
		. "<input type='button' onclick='window.open(\"$fckeditordir/editor/filemanager/browser/default/browser.html?Connector=../../connectors/php/connector.php\", \"_blank\")'/ value=\"".$clang->gT("Browse Uploaded Resources")."\" $disabledIfNoResources></td><td><td></tr>\n"
		. "<tr><td></td><td><input type='button' onclick='window.open(\"$scriptname?action=exportsurvresources&amp;sid={$surveyid}\", \"_blank\")'/ value=\"".$clang->gT("Export Resources As ZIP Archive")."\" $disabledIfNoResources></td><td><td></tr>\n"
		. "<tr></tr>&nbsp;<tr><td>".$clang->gT("Select ZIP File:")."</td>\n"
		. "<td><input name=\"the_file\" type=\"file\" size=\"50\" /></td><td></td></tr>\n"
		. "<tr><td></td><td><input type='button' value='".$clang->gT("Import Resources ZIP Archive")."' $ZIPimportAction /></td><td></td>\n"
		. "</tr>\n"
		. "</tbody></table></form>\n";

		// End TAB Uploaded Resources Management
		$editsurvey .= "</div>\n";

		// End TAB pane 
		$editsurvey .= "</div>\n";


			// The external button to sumbit Survey edit changes
			$editsurvey .= "<table><tr><td colspan='4' align='center'><input type='button' onclick='if (UpdateLanguageIDs(mylangs,\"".$clang->gT("All questions, answers, etc for removed languages will be lost. Are you sure?","js")."\")) {document.getElementById(\"addnewsurvey\").submit();}' class='standardbtn' value='".$clang->gT("Save and Continue")." >>' />\n"
			. "</td></tr>\n"
			. "</table>\n";

			// Here we do the setup the date javascript
			$editsurvey .= "<script type=\"text/javascript\">\n"
			. "Calendar.setup({\n"
			. "inputField     :    \"f_date_b\",\n"     // id of the input field
			. "ifFormat       :    \"%Y-%m-%d\",\n"     // format of the input field
			. "showsTime      :    false,\n"            // will display a time selector
			. "button         :    \"f_trigger_b\",\n"  // trigger for the calendar (button ID)
			. "singleClick    :    true,\n"             // double-click mode
			. "step           :    1\n"                 // show all years in drop-down boxes (instead of every other year as default)
			. "});\n"
            . "Calendar.setup({\n"
            . "inputField     :    \"f_date_a\",\n"     // id of the input field
            . "ifFormat       :    \"%Y-%m-%d\",\n"     // format of the input field
            . "showsTime      :    false,\n"            // will display a time selector
            . "button         :    \"f_trigger_a\",\n"  // trigger for the calendar (button ID)
            . "singleClick    :    true,\n"             // double-click mode
            . "step           :    1\n"                 // show all years in drop-down boxes (instead of every other year as default)
            . "});\n"
            . "</script>\n";
		}

	}
	else
	{
		include("access_denied.php");
	}

}


if ($action == "updatesurvey")  // Edit survey step 2  - editing language dependent settings
{
	if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['edit_survey_property'])
	{
	
    	$grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
		$baselang = GetBaseLanguageFromSurveyID($surveyid);
		array_unshift($grplangs,$baselang);

		$editsurvey = PrepareEditorScript();
		
	
		$editsurvey .="<script type='text/javascript'>\n"
		. "<!--\n"
		. "function fillin(tofield, fromfield)\n"
		. "{\n"
		. "if (confirm(\"".$clang->gT("This will replace the existing text. Continue?","js")."\")) {\n"
		. "document.getElementById(tofield).value = document.getElementById(fromfield).value\n"
		. "}\n"
		. "}\n"
		. "--></script>\n"
        . "<table width='100%' border='0'>\n<tr><td class='settingcaption'>"
		. "".$clang->gT("Edit Survey - Step 2 of 2")."</td></tr></table>\n";
		$editsurvey .= "<form name='addnewsurvey' action='$scriptname' method='post'>\n"
		. '<div class="tab-pane" id="tab-pane-1">';
		foreach ($grplangs as $grouplang)
		{
            // this one is created to get the right default texts fo each language
            $bplang = new limesurvey_lang($grouplang);		
    		$esquery = "SELECT * FROM ".db_table_name("surveys_languagesettings")." WHERE surveyls_survey_id=$surveyid and surveyls_language='$grouplang'";
    		$esresult = db_execute_assoc($esquery); //Checked
    		$esrow = $esresult->FetchRow();
			$editsurvey .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($esrow['surveyls_language'],false);
			if ($esrow['surveyls_language']==GetBaseLanguageFromSurveyID($surveyid)) {$editsurvey .= '('.$clang->gT("Base Language").')';}
			$editsurvey .= '</h2>';
			$esrow = array_map('htmlspecialchars', $esrow);
			$editsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Title").":</span>\n"
			. "<span class='settingentry'><input type='text' size='80' name='short_title_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_title']}\" /></span>\n"
			. "</div><div class='settingrow'><span class='settingcaption'>".$clang->gT("Description:")."</span>\n"
			. "<span class='settingentry'><textarea cols='80' rows='15' name='description_".$esrow['surveyls_language']."'>{$esrow['surveyls_description']}</textarea>\n"
			. getEditor("survey-desc","description_".$esrow['surveyls_language'], "[".$clang->gT("Description:", "js")."](".$esrow['surveyls_language'].")",'','','',$action)
			. "</span>\n"
			. "</div><div class='settingrow'><span class='settingcaption'>".$clang->gT("Welcome:")."</span>\n"
			. "<span class='settingentry'><textarea cols='80' rows='15' name='welcome_".$esrow['surveyls_language']."'>{$esrow['surveyls_welcometext']}</textarea>\n"
			. getEditor("survey-welc","welcome_".$esrow['surveyls_language'], "[".$clang->gT("Welcome:", "js")."](".$esrow['surveyls_language'].")",'','','',$action)
			. "</span></div>\n"
			. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("URL Description:")."</span>\n"
			. "<span class='settingentry'><input type='text' size='80' name='urldescrip_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_urldescription']}\" />\n"
			. "</span></div></div>";
		}
		$editsurvey .= '</div>';
		$editsurvey .= "<p><input type='submit' class='standardbtn' value='".$clang->gT("Save")."' />\n"
		. "<input type='hidden' name='action' value='updatesurvey2' />\n"
		. "<input type='hidden' name='sid' value=\"{$surveyid}\" />\n"
		. "<input type='hidden' name='language' value=\"{$esrow['surveyls_language']}\" />\n"
		. "</p>\n"
		. "</form>\n";

	}
	else
	{
		include("access_denied.php");
	}

}

if($action == "quotas")
{
	include("quota.php");
}

// Show the screen to order groups

if ($action == "ordergroups")
{
	if($_SESSION['USER_RIGHT_SUPERADMIN'] == 1 || $sumrows5['edit_survey_property'])
	{
	// Check if one of the up/down buttons have been clicked
	if (isset($_POST['groupordermethod']) && isset($_POST['sortorder']))
	{
       $postsortorder=sanitize_int($_POST['sortorder']);
	   switch($_POST['groupordermethod'])
	   {
        // Pressing the Up button
		case $clang->gT("Up", "unescaped"):
		$newsortorder=$postsortorder-1;
		$oldsortorder=$postsortorder;
		$cdquery = "UPDATE ".db_table_name('groups')." SET group_order=-1 WHERE sid=$surveyid AND group_order=$newsortorder";
		$cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg()); //Checked
		$cdquery = "UPDATE ".db_table_name('groups')." SET group_order=$newsortorder WHERE sid=$surveyid AND group_order=$oldsortorder";
		$cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg()); //Checked
		$cdquery = "UPDATE ".db_table_name('groups')." SET group_order='$oldsortorder' WHERE sid=$surveyid AND group_order=-1";
		$cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg()); //Checked
		break;

        // Pressing the Down button
		case $clang->gT("Dn", "unescaped"):
		$newsortorder=$postsortorder+1;
		$oldsortorder=$postsortorder;
		$cdquery = "UPDATE ".db_table_name('groups')." SET group_order=-1 WHERE sid=$surveyid AND group_order=$newsortorder";
		$cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());//Checked
		$cdquery = "UPDATE ".db_table_name('groups')." SET group_order='$newsortorder' WHERE sid=$surveyid AND group_order=$oldsortorder";
		$cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());//Checked
		$cdquery = "UPDATE ".db_table_name('groups')." SET group_order=$oldsortorder WHERE sid=$surveyid AND group_order=-1";
		$cdresult=$connect->Execute($cdquery) or safe_die($connect->ErrorMsg());//Checked
		break;
        }
    }

        $ordergroups = "<table width='100%' border='0'>\n<tr ><td colspan='2' class='settingcaption'>"
		. "".$clang->gT("Change Group Order")."</td></tr>"
		. "</table>\n";

	// Get groups dependencies regarding conditions
	// => Get an array of groups containing questions with conditions outside the group
	// $groupdepsarray[dependent-gid][target-gid]['conditions'][qid-having-conditions]=Array(cids...)
	$groupdepsarray = GetGroupDepsForConditions($surveyid);
	if (!is_null($groupdepsarray))
	{
		$ordergroups .= "<ul class='movableList'><li class='movableNode'><strong><font color='orange'>".$clang->gT("Warning").":</font> ".$clang->gT("Current survey has questions with conditions outside their own group")."</strong><br /><br /><i>".$clang->gT("Re-ordering groups is restricted to ensure that questions on which conditions are based aren't reordered after questions having the conditions set")."</i></strong><br /><br/>".$clang->gT("The following groups are concerned").":<ul>\n";
		foreach ($groupdepsarray as $depgid => $depgrouprow)
		{
			foreach($depgrouprow as $targgid => $targrow)
			{
				$ordergroups .= "<li>".sprintf($clang->gT("Group %s depends on group %s, see the marked conditions on:"), "<a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$depgid."')\">".$targrow['depgpname']."</a>", "<a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$targgid."')\">".$targrow['targetgpname']."</a> ");
				foreach($targrow['conditions'] as $depqid => $depqrow)
				{
					$listcid=implode("-",$depqrow);
					$ordergroups .= " <a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$depgid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."')\"> [".$clang->gT("QID").": ".$depqid."]</a>";
				}
				$ordergroups .= "</li>\n";
			}
		}
		$ordergroups .= "</ul></li></ul>";
	}

	$ordergroups .= "<form method='post' action=''><ul class='movableList'>";
		//Get the groups from this survey
		$s_lang = GetBaseLanguageFromSurveyID($surveyid);
		$ogquery = "SELECT * FROM {$dbprefix}groups WHERE sid='{$surveyid}' AND language='{$s_lang}' order by group_order,group_name" ;
		$ogresult = db_execute_assoc($ogquery) or safe_die($connect->ErrorMsg());//Checked

		$ogarray = $ogresult->GetArray();
    		$groupcount = count($ogarray);
		for($i=0; $i < $groupcount ; $i++)
		{
			$downdisabled = "";
			$updisabled = "";
			if ( !is_null($groupdepsarray) && $i < $groupcount-1 && 
			   array_key_exists($ogarray[$i+1]['gid'],$groupdepsarray) &&
			   array_key_exists($ogarray[$i]['gid'],$groupdepsarray[$ogarray[$i+1]['gid']]) )
			{
				$downdisabled = "disabled=\"true\" class=\"disabledbtn\"";
			}
			if ( !is_null($groupdepsarray) && $i !=0  && 
			   array_key_exists($ogarray[$i]['gid'],$groupdepsarray) &&
			   array_key_exists($ogarray[$i-1]['gid'],$groupdepsarray[$ogarray[$i]['gid']]) )
			{
				$updisabled = "disabled=\"true\" class=\"disabledbtn\"";
			}
	
			$ordergroups.="<li class='movableNode' id='gid".$ogarray[$i]['gid']."'>\n" ;
			$ordergroups.= "<input style='float:right;";
	
	                if ($i == 0){$ordergroups.="visibility:hidden;";}
	                $ordergroups.="' type='submit' name='groupordermethod' value='".$clang->gT("Up")."' onclick=\"this.form.sortorder.value='{$ogarray[$i]['group_order']}'\" ".$updisabled."/>\n";
	
	   		if ($i < $groupcount-1)
	    			{
	    				// Fill the hidden field 'sortorder' so we know what field is moved down
					$ordergroups.= "<input type='submit' style='float:right;' name='groupordermethod' value='".$clang->gT("Dn")."' onclick=\"this.form.sortorder.value='{$ogarray[$i]['group_order']}'\" ".$downdisabled."/>\n";
	    			}
				$ordergroups.=$ogarray[$i]['group_name']."</li>\n" ;
	
		}

		$ordergroups.="</ul>\n"
		. "<input type='hidden' name='sortorder' />"
		. "<input type='hidden' name='action' value='ordergroups' />" 
        . "</form>" ;
		$ordergroups .="<br />" ;
	}
	else
	{
		include("access_denied.php");
	}
}


if ($action == "newsurvey")
{
	if($_SESSION['USER_RIGHT_CREATE_SURVEY'])
	{
		$newsurvey = PrepareEditorScript();
		$newsurvey  .= "<form name='addnewsurvey' id='addnewsurvey' action='$scriptname' method='post' onsubmit=\"return isEmpty(document.getElementById('surveyls_title'), '".$clang->gT("Error: You have to enter a title for this survey.",'js')."');\" >\n";

		// header
		$newsurvey .= "<table width='100%' border='0'>\n<tr><td class='settingcaption'>"
		. "".$clang->gT("Create or Import Survey")."</td></tr></table>\n";

		// begin Tabs section
		$newsurvey .= "<div class='tab-pane' id='tab-pane-1'>\n";

		// General and Contact TAB
		$newsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("General")."</h2>\n";
// could be used to add a header
//		$newsurvey .= "<table width='100%' border='0'>\n<tr><td class='settingcaption'>"
//		. "".$clang->gT("Create Survey")."</td></tr></table>\n";

		// * Survey Language
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Base Language:")."</span>\n"
		. "<span class='settingentry'><select name='language'>\n";


		foreach (getLanguageData() as  $langkey2=>$langname)
		{
			$newsurvey .= "<option value='".$langkey2."'";
			if ($defaultlang == $langkey2) {$newsurvey .= " selected='selected'";}
			$newsurvey .= ">".$langname['description']." - ".$langname['nativedescription']."</option>\n";
		}

		$newsurvey .= "</select><font size='1'> ".$clang->gT("This setting cannot be changed later!")."</font>\n"
		. "</span></div>\n";

		$newsurvey .= ""
		. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Title").":</span>\n"
		. "<span class='settingentry'><input type='text' size='82' maxlength='200' id='surveyls_title' name='surveyls_title' /><font size='1'> ".$clang->gT("(This field is mandatory.)")."</font></span></div>\n"
		. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Description:")."</span>\n"
		. "<span class='settingentry'><textarea cols='80' rows='10' name='description'></textarea>"
		. getEditor("survey-desc","description", "[".$clang->gT("Description:", "js")."]",'','','',$action)
		. "</span></div>\n"
		. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Welcome:")."</span>\n"
		. "<span class='settingentry'><textarea cols='80' rows='10' name='welcome'></textarea>"
		. getEditor("survey-welc","welcome", "[".$clang->gT("Welcome:", "js")."]",'','','',$action)
		. "</span></div>\n"
		. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Administrator:")."</span>\n"
		. "<span class='settingentry'><input type='text' size='50' name='admin' value='$siteadminname' /></span></div>\n"
		. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Admin Email:")."</span>\n"
		. "<span class='settingentry'><input type='text' size='50' name='adminemail' value='$siteadminemail' /></span></div>\n"
		. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Bounce Email:")."</span>\n"
		. "<span class='settingentry'><input type='text' size='50' name='bounce_email' value='$siteadminemail' /></span></div>\n";
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Fax To:")."</span>\n"
		. "<span class='settingentry'><input type='text' size='50' name='faxto' /></span></div>\n";


		// End General TAB
		// Create Survey Button 
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'></span><span class='settingentry'><input type='button' onclick='javascript:document.getElementById(\"addnewsurvey\").submit();' value='".$clang->gT("Create Survey")."' /></span></div>\n";
        
		$newsurvey .= "</div>\n";

		// Presentation and navigation TAB
		$newsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Presentation & Navigation")."</h2>\n";


		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Format:")."</span>\n"
		. "<span class='settingentry'><select name='format'>\n"
		. "<option value='S'>".$clang->gT("Question by Question")."</option>\n"
		. "<option value='G' selected='selected'>".$clang->gT("Group by Group")."</option>\n"
		. "<option value='A'>".$clang->gT("All in one")."</option>\n"
		. "</select></span>\n"
		. "</div>\n";
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Template:")."</span>\n"
		. "<span class='settingentry'><select name='template'>\n";
		foreach (gettemplatelist() as $tname)
		{
			
			if ($_SESSION["loginID"] == 1 || $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] == 1 || hasTemplateManageRights($_SESSION["loginID"], $tname) == 1 )  {
				$newsurvey .= "<option value='$tname'";
				if (isset($esrow) && $esrow['template'] && $tname == $esrow['template']) {$newsurvey .= " selected='selected'";}
				elseif ((!isset($esrow) || !$esrow['template']) && $tname == "default") {$newsurvey .= " selected='selected'";}
				$newsurvey .= ">$tname</option>\n";
			}
			
		}
		$newsurvey .= "</select></span>\n"
		. "</div>\n";

		//ALLOW SAVES
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Allow Saves?")."</span>\n"
		. "<span class='settingentry'><select name='allowsave'>\n"
		. "<option value='Y'";
		if (!isset($esrow['allowsave']) || !$esrow['allowsave'] || $esrow['allowsave'] == "Y") {$newsurvey .= " selected='selected'";}
		$newsurvey .= ">".$clang->gT("Yes")."</option>\n"
		. "<option value='N'";
		if (isset($esrow['allowsave']) && $esrow['allowsave'] == "N") {$newsurvey .= " selected='selected'";}
		$newsurvey .= ">".$clang->gT("No")."</option>\n"
		. "</select></span>\n"
		. "</div>\n";
		//ALLOW PREV
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Show [<< Prev] button")."</span>\n"
		. "<span class='settingentry'><select name='allowprev'>\n"
		. "<option value='Y' selected='selected'>".$clang->gT("Yes")."</option>\n"
		. "<option value='N'>".$clang->gT("No")."</option>\n"
		. "</select></span>\n"
		. "</div>\n";

        //Result printing
        $newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Participants may print answers?")."</span>\n"
        . "<span class='settingentry'><select name='printanswers'>\n"
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "</select></span>\n"
        . "</div>\n";

        //Public Surveys
        $newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("List survey publicly:")."</span>\n"
        . "<span class='settingentry'><select name='public'>\n"
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "</select></span>\n"
        . "</div>\n";


		// End URL
		$newsurvey .= ""
		. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("End URL:")."</span>\n"
		. "<span class='settingentry'><input type='text' size='50' name='url' value='http://";
		if (isset($esrow)) {$newsurvey .= $esrow['url'];}
		$newsurvey .= "' /></span></div>\n"
		. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("URL Description:")."</span>\n"
		. "<span class='settingentry'><input type='text' maxlength='255' size='50' name='urldescrip' value='";
		if (isset($esrow)) {$newsurvey .= $esrow['surveyls_urldescription'];}
		$newsurvey .= "' /></span></div>\n"
		. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Automatically load URL when survey complete?")."</span>\n"
		. "<span class='settingentry'><select name='autoredirect'>\n"
		. "<option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
		. "</select></span></div>";

		// End Presention and navigation TAB
		// Create Survey Button 
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'></span><span class='settingentry'><input type='button' onclick='javascript:document.getElementById(\"addnewsurvey\").submit();' value='".$clang->gT("Create Survey")."' /></span></div>\n";
		$newsurvey .= "</div>\n";

		// Publication and access control TAB
		$newsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Publication & Access control")."</h2>\n";


        // Timed Start
        $newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Timed start?")."</span>\n"
        . "<span class='settingentry'><select name='usestartdate'><option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option></select></span></div>\n"
        . "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Start date:")."</span>\n"
        . "<span class='settingentry'><input type='text' id='f_date_a' size='12' name='startdate' value='"
        . date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust)."' /><button type='reset' id='f_trigger_a'>...</button>"
        . "<font size='1'> ".$clang->gT("Date Format").": YYYY-MM-DD</font></span></div>\n";

		// Expiration
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Expires?")."</span>\n"
		. "<span class='settingentry'><select name='useexpiry'><option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "<option value='N' selected='selected'>".$clang->gT("No")."</option></select></span></div>\n"
		. "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Expiry Date:")."</span>\n"
		. "<span class='settingentry'><input type='text' id='f_date_b' size='12' name='expires' value='"
		. date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust)."' /><button type='reset' id='f_trigger_b'>...</button>"
		. "<font size='1'> ".$clang->gT("Date Format").": YYYY-MM-DD</font></span></div>\n";

		//COOKIES
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Use Cookies?")."</span>\n"
		. "<span class='settingentry'><select name='usecookie'>\n"
		. "<option value='Y'";
		if (isset($esrow) && $esrow['usecookie'] == "Y") {$newsurvey .= " selected='selected'";}
		$newsurvey .= ">".$clang->gT("Yes")."</option>\n"
		. "<option value='N' selected='selected'";
		$newsurvey .= ">".$clang->gT("No")."</option>\n"
		. "</select></span>\n"
		. "</div>\n";

	// Public registration
        $newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Allow public registration?")."</span>\n"
        . "<span class='settingentry'><select name='allowregister'>\n"
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "</select></span>\n</div>\n";

	// Use Captcha 
        $newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Use CAPTCHA for").":</span>\n"
        . "<span class='settingentry'><select name='usecaptcha'>\n"
        . "<option value='A'>".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
        . "<option value='B'>".$clang->gT("Survey Access")." / ".$clang->gT("Registration")." / ---------</option>\n"
        . "<option value='C'>".$clang->gT("Survey Access")." / ------------ / ".$clang->gT("Save & Load")."</option>\n"
        . "<option value='D' selected='selected'>------------- / ".$clang->gT("Registration")." / ".$clang->gT("Save & Load")."</option>\n"
        . "<option value='X'>".$clang->gT("Survey Access")." / ------------ / ---------</option>\n"
        . "<option value='R'>------------- / ".$clang->gT("Registration")." / ---------</option>\n"
        . "<option value='S'>------------- / ------------ / ".$clang->gT("Save & Load")."</option>\n"
        . "<option value='N'>------------- / ------------ / ---------</option>\n"
        . "</select></span>\n</div>\n";

		// Token attributes names
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Token Attribute Names:")."</span>\n"
		. "<span class='settingentry'><input type='text' size='25' name='attribute1' />(".$clang->gT("Attribute 1").")<br />"
		. "<input type='text' size='25' name='attribute2' />(".$clang->gT("Attribute 2").")</span>\n</div>\n";

	// Email format
        $newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Use HTML format for token emails?")."</span>\n"
        . "<span class='settingentry'><select name='htmlemail'>\n"
        . "<option value='Y' selected='selected'>".$clang->gT("Yes")."</option>\n"
        . "<option value='N'>".$clang->gT("No")."</option>\n"
        . "</select></span>\n</div>\n";

		// End Publication and access control TAB
		// Create Survey Button 
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'></span><span class='settingentry'><input type='button' onclick='javascript:document.getElementById(\"addnewsurvey\").submit();' value='".$clang->gT("Create Survey")."' /></span></div>\n";
		$newsurvey .= "</div>\n";

		// Notification and Data management TAB
		$newsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Notification & Data Management")."</h2>\n";

		//NOTIFICATIONS
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Admin Notification:")."</span>\n"
		. "<span class='settingentry'><select name='notification'>\n"
		. getNotificationlist(0)
		. "</select></span>\n"
		. "</div>\n";


		// ANONYMOUS
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Anonymous answers?")."\n";
		// warning message if anonymous + datestamped anwsers
		$newsurvey .= "\n"
		. "<script type=\"text/javascript\"><!-- \n"
		. "function alertPrivacy()\n"
		. "{"
		. "if (document.getElementById('private').value == 'Y')\n"
		. "{\n"
		. "alert('".$clang->gT("Warning").": ".$clang->gT("If you turn on the -Anonymous answers- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.","js")."');\n"
		. "}\n"
		. "}"
		. "//--></script></span>\n";
		$newsurvey .= "<span class='settingentry'><select id='private' name='private' onchange='alertPrivacy();'>\n"
		. "<option value='Y' selected='selected'>".$clang->gT("Yes")."</option>\n"
		. "<option value='N'>".$clang->gT("No")."</option>\n"
		. "</select></span>\n</div>\n";

		// Datestamp
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Date Stamp?")."</span>\n"
		. "<span class='settingentry'><select id='datestamp' name='datestamp' onchange='alertPrivacy();'>\n"
		. "<option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
		. "</select></span>\n</div>\n";

		// IP Address
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Save IP Address?")."</span>\n"
		. "<span class='settingentry'><select name='ipaddr'>\n"                                
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
		. "</select></span>\n</div>\n";

		// Referring URL
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Save Referring URL?")."</span>\n"
		. "<span class='settingentry'><select name='refurl'>\n"                                
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
		. "</select></span>\n</div>\n";

		// Token answers persistence
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'>".$clang->gT("Enable Token-based answers persistence?")."</span>\n"
		. "<span class='settingentry'><select name='tokenanswerspersistence'>\n" 
        . "<option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
		. "</select></span>\n</div>\n";

		// end of addnewsurvey form
		$newsurvey .= ""
//		. "<div class='settingrow'><span><input type='submit' value='".$clang->gT("Create Survey")."' />\n"
//		. "<input type='hidden' name='action' value='insertnewsurvey' /></span>\n"
		. "<input type='hidden' name='action' value='insertnewsurvey' />\n"
		. "</form>\n";

		// End Notification and Data management TAB
		// Create Survey Button
		$newsurvey .= "<div class='settingrow'><span class='settingcaption'></span><span class='settingentry'><input type='button' onclick='javascript:document.getElementById(\"addnewsurvey\").submit();' value='".$clang->gT("Create Survey")."' /></span></div>\n";
		$newsurvey .= "</div>\n";

		// Import TAB
		$newsurvey .= "<div class='tab-page'> <h2 class='tab'>".$clang->gT("Import Survey")."</h2>\n";

		// Import Survey
		$newsurvey .= "<form enctype='multipart/form-data' name='importsurvey' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
		. "<table width='100%' border='0' class='form2columns'>\n"
//		. "<tr><th colspan='2'>\n"
//		. "".$clang->gT("Import Survey")."</th></tr>\n"
		. "<tr><td>".$clang->gT("Select CSV/SQL File:")."</td>\n"
		. "<td><input name=\"the_file\" type=\"file\" size=\"50\" /></td></tr>\n"
		. "<tr><td><label>".$clang->gT("Convert resources links and INSERTANS fields?")."</td>\n"
		. "<td><input name=\"translinksfields\" type=\"checkbox\" /></label></td></tr>\n"
		. "<tr><td colspan='2' class='centered'><input type='submit' value='".$clang->gT("Import Survey")."' />\n"
		. "<input type='hidden' name='action' value='importsurvey' /></td>\n"
		. "</tr>\n"
//		. "</div>" // end tab
//		. "</div>" // end tab-pane
		. "</table></form>\n";
//		. "</form>\n";

		// End Import TAB
		$newsurvey .= "</div>\n";

		// End TAB pane 
		$newsurvey .= "</div>\n";

		// Here we do setup the date javascript
		$newsurvey .= "<script type=\"text/javascript\">\n"
        . "Calendar.setup({\n"
        . "inputField     :    \"f_date_a\",\n"    // id of the input field
        . "ifFormat       :    \"%Y-%m-%d\",\n"   // format of the input field
        . "showsTime      :    false,\n"                    // will display a time selector
        . "button         :    \"f_trigger_a\",\n"         // trigger for the calendar (button ID)
        . "singleClick    :    true,\n"                   // double-click mode
        . "step           :    1\n"                        // show all years in drop-down boxes (instead of every other year as default)
        . "});\n"
		. "Calendar.setup({\n"
		. "inputField     :    \"f_date_b\",\n"    // id of the input field
		. "ifFormat       :    \"%Y-%m-%d\",\n"   // format of the input field
		. "showsTime      :    false,\n"                    // will display a time selector
		. "button         :    \"f_trigger_b\",\n"         // trigger for the calendar (button ID)
		. "singleClick    :    true,\n"                   // double-click mode
		. "step           :    1\n"                        // show all years in drop-down boxes (instead of every other year as default)
		. "});\n"
		. "</script>\n";
	}
	else
	{
		include("access_denied.php");
	}
}


function replacenewline ($texttoreplace)
{
	$texttoreplace = str_replace( "\n", '<br />', $texttoreplace);
	//  $texttoreplace = htmlentities( $texttoreplace, ENT_QUOTES, UTF-8);
	$new_str = '';

	for($i = 0; $i < strlen($texttoreplace); $i++) {
		$new_str .= '\x' . dechex(ord(substr($texttoreplace, $i, 1)));
	}

	return $new_str;
}
/*
function questionjavascript($type, $qattributes)
	{
	$newquestion = "<script type='text/javascript'>\n"
				 . "<!--\n";
		$jc=0;
		$newquestion .= "var qtypes = new Array();\n";
		$newquestion .= "var qnames = new Array();\n\n";
		foreach ($qattributes as $key=>$val)
			{
			foreach ($val as $vl)
				{
				$newquestion .= "qtypes[$jc]='".$key."';\n";
				$newquestion .= "qnames[$jc]='".$vl['name']."';\n";
				$jc++;
				}
			}
		$newquestion .= " function buildQTlist(type)
				{
				document.getElementById('QTattributes').style.display='none';
				for (var i=document.getElementById('QTlist').options.length-1; i>=0; i--)
					{
					document.getElementById('QTlist').options[i] = null;
					}
				for (var i=0;i<qtypes.length;i++)
					{
					if (qtypes[i] == type)
						{
						document.getElementById('QTattributes').style.display='';
						document.getElementById('QTlist').options[document.getElementById('QTlist').options.length] = new Option(qnames[i], qnames[i]);
						}
					}
				}";
	$newquestion .="\nfunction OtherSelection(QuestionType)\n"
				 . "{\n"
				 . "if (QuestionType == '') {QuestionType=document.getElementById('question_type').value;}\n"
				 . "if (QuestionType == 'M' || QuestionType == 'P' || QuestionType == 'L' || QuestionType == '!')\n"
				 . "{\n"
				 . "document.getElementById('OtherSelection').style.display = '';\n"
				 . "document.getElementById('LabelSets').style.display = 'none';\n"
				 . "document.getElementById('Validation').style.display = 'none';\n"
				 . "}\n"
				 . "else if (QuestionType == 'F' || QuestionType == 'H' || QuestionType == 'W' || QuestionType == 'Z')\n"
				 . "{\n"
				 . "document.getElementById('LabelSets').style.display = '';\n"
				 . "document.getElementById('OtherSelection').style.display = 'none';\n"
				 . "document.getElementById('Validation').style.display = 'none';\n"
				 . "}\n"
				 . "else if (QuestionType == 'S' || QuestionType == 'T' || QuestionType == 'U' || QuestionType == 'N' || QuestionType=='')\n"
				 . "{\n"
				 . "document.getElementById('Validation').style.display = '';\n"
				 . "document.getElementById('OtherSelection').style.display ='none';\n"
				 . "document.getElementById('ON').checked = true;\n"
				 . "document.getElementById('LabelSets').style.display='none';\n"
				 . "}\n"
				 . "else\n"
				 . "{\n"
				 . "document.getElementById('LabelSets').style.display = 'none';\n"
				 . "document.getElementById('OtherSelection').style.display = 'none';\n"
				 . "document.getElementById('ON').checked = true;\n"
				 . "document.getElementById('Validation').style.display = 'none';\n"
				 //. "document.addnewquestion.other[1].checked = true;\n"
				 . "}\n"
				 . "buildQTlist(QuestionType);\n"
				 . "}\n"
				 . "OtherSelection('$type');\n"
				 . "-->\n"
				 . "</script>\n";

}      */
?>
