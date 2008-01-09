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

//Ensure script is not run directly, avoid path disclosure
include_once("login_check.php");


if ($action == "listsurveys")
{
	$query = "SELECT a.*, c.* FROM ".db_table_name('surveys')." as a INNER JOIN ".db_table_name('surveys_rights')." AS b ON a.sid = b.sid INNER JOIN ".db_table_name('surveys_languagesettings')." as c ON ( surveyls_survey_id = a.sid AND surveyls_language = a.language ) WHERE b.uid = ".$_SESSION['loginID']." and surveyls_survey_id=a.sid and surveyls_language=a.language ORDER BY surveyls_title";
	$result = db_execute_assoc($query) or die($connect->ErrorMsg());

	if($result->RecordCount() > 0) {
        $listsurveys= "<br /><table cellpadding='1' width='800'>
				  <tr>
				    <th height=\"22\" width='22'>&nbsp</th>
				    <th height=\"22\"><strong>".$clang->gT("Survey")."</strong></th>
				    <th><strong>".$clang->gT("Date Created")."</strong></th>
				    <th><strong>".$clang->gT("Access")."</strong></th>
				    <th><strong>".$clang->gT("Answer Privacy")."</strong></th>
				    <th><strong>".$clang->gT("Status")."</strong></th>
				    <th><strong>".$clang->gT("Responses")."</strong></th>
				  </tr>";
        $gbc = "evenrow"; 

		while($rows = $result->FetchRow())
		{
			$sidsecurityQ = "SELECT b.* FROM {$dbprefix}surveys AS a INNER JOIN {$dbprefix}surveys_rights AS b ON a.sid = b.sid WHERE a.sid='{$rows['sid']}' AND b.uid = ".$_SESSION['loginID']; //Getting rights for this survey and user
			$sidsecurityR = db_execute_assoc($sidsecurityQ);
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
				} else {
					$status=$clang->gT("Active") ;
				}
				
				// Survey Responses - added by DLR
				$gnquery = "SELECT count(id) FROM ".db_table_name("survey_".$rows['sid']);
			    $gnresult = db_execute_num($gnquery);
				while ($gnrow = $gnresult->FetchRow())	     
                {
					$responses=$gnrow[0];
	            }
			}
			else $status =$clang->gT("Inactive") ;

			$datecreated=$rows['datecreated'] ;

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
					if ($sidsecurity['activate_survey'])
					{
						$listsurveys .= "<td><a href=\"#\" onclick=\"window.open('$scriptname?action=deactivate&amp;sid={$rows['sid']}', '_top')\""
						. "onmouseout=\"hideTooltip()\""
						. "onmouseover=\"showTooltip(event,'".$clang->gT("De-activate this Survey", "js")."');return false\">"
						. "<img src='$imagefiles/active.png' name='DeactivateSurvey' "
						. "alt='".$clang->gT("De-activate this Survey")."'  border='0' hspace='0' align='left' width='20' /></a></td>\n";
					} else 
					{
						$listsurveys .= "<td><img src='$imagefiles/active.png' title='' "
						. "alt='".$clang->gT("This survey is currently active")."' align='left' border='0' hspace='0' align='left' width='20' "
						. "onmouseout=\"hideTooltip()\""
						. "onmouseover=\"showTooltip(event,'".$clang->gT("This survey is currently active", "js")."');return false\" /></td>\n";
					}
				}
			} else {
				if ($sidsecurity['activate_survey'])
				{
					$listsurveys .= "<td><a href=\"#\" onclick=\"window.open('$scriptname?action=activate&amp;sid={$rows['sid']}', '_top')\""
					. "onmouseout=\"hideTooltip()\""
					. "onmouseover=\"showTooltip(event,'".$clang->gT("Activate this Survey", "js")."');return false\">" .
					"<img src='$imagefiles/inactive.png' name='ActivateSurvey' title='' alt='".$clang->gT("Activate this Survey")."' border='0' hspace='0' align='left' width='20' /></a></td>\n" ;	
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
					    "<td>".$visibility."</td>" .
					    "<td>".$privacy."</td>" .
					    "<td>".$status."</td>";

					    if ($status==$clang->gT("Active"))
					    {
					        $listsurveys .= "<td align='center'>".$responses."</td>";
					    }else{
						$listsurveys .= "<td>&nbsp;</td>";
					    }
					    $listsurveys .= "</tr>" ;
		}

		$listsurveys.="<tr class='header'>
		<td colspan=\"7\">&nbsp;</td>".
		"</tr>";
		$listsurveys.="</table><br />" ;
	}
	else $listsurveys="<br /><strong> ".$clang->gT("No Surveys available - please create one.")." </strong><br /><br />" ;
}

if ($action == "checksettings" || $action == "changelang" || $action=="changehtmleditormode")
{
	//GET NUMBER OF SURVEYS
	$query = "SELECT sid FROM ".db_table_name('surveys');
	$result = $connect->Execute($query);
	$surveycount=$result->RecordCount();
	$query = "SELECT sid FROM ".db_table_name('surveys')." WHERE active='Y'";
	$result = $connect->Execute($query);
	$activesurveycount=$result->RecordCount();
	$query = "SELECT users_name FROM ".db_table_name('users');
	$result = $connect->Execute($query);
	$usercount = $result->RecordCount();

	// prepare data for the htmleditormode preference
	$htmleditormode='default';
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
	. "<form action='$scriptname'>"
	. "<table class='table2columns'"
	. "cellpadding='1' cellspacing='0' width='600'>\n"
	. "\t<tr>\n"
	. "\t\t<td colspan='2' align='center' bgcolor='#F8F8FF'>\n"
	. "\t\t\t<strong>".$clang->gT("LimeSurvey System Summary")."</strong>\n"
	. "\t\t</td>\n"
	. "\t</tr>\n";
	// Database name & default language
	$cssummary .= "\t<tr>\n"
	. "\t\t<td width='50%' align='right'>\n"
	. "\t\t\t<strong>".$clang->gT("Database Name").":</strong>\n"
	. "\t\t</td><td>\n"
	. "\t\t\t$databasename\n"
	. "\t\t</td>\n"
	. "\t</tr>\n"
	. "\t<tr>\n"
	. "\t\t<td align='right'>\n"
	. "\t\t\t<strong>".$clang->gT("Default Language").":</strong>\n"
	. "\t\t</td><td>\n"
	. "\t\t\t".getLanguageNameFromCode($defaultlang)."\n"
	. "\t\t</td>\n"
	. "\t</tr>\n";
	// Current language
	$cssummary .=  "\t<tr>\n"
	. "\t\t<td align='right' >\n"
	. "\t\t\t<strong>".$clang->gT("Current Language").":</strong>\n"
	. "\t\t</td><td>\n"
	. "\t\t\t<select name='lang' onchange='form.submit()'>\n";
	foreach (getlanguagedata() as $langkey=>$languagekind)
	{
		$cssummary .= "\t\t\t\t<option value='$langkey'";
		if ($langkey == $_SESSION['adminlang']) {$cssummary .= " selected='selected'";}
		$cssummary .= ">".$languagekind['description']." - ".$languagekind['nativedescription']."</option>\n";
	}
	$cssummary .= "\t\t\t</select>\n"
	. "\t\t\t<input type='hidden' name='action' value='changelang' />\n"
	. "\t\t</td>\n"
	. "\t</tr>\n";
	// Current htmleditormode
	$cssummary .=  "\t<tr>\n"
	. "\t\t<td align='right' >\n"
	. "\t\t\t<strong>".$clang->gT("Preferred HTML editor mode").":</strong>\n"
	. "\t\t</td><td>\n"
	. "\t\t\t<select name='htmleditormode' onchange='form.submit()'>\n"
	. "\t\t\t\t<option value='default' $edmod1>".$clang->gT("Default HTML editor mode")."</option>\n"
	. "\t\t\t\t<option value='none' $edmod2>".$clang->gT("No HTML editor")."</option>\n"
	. "\t\t\t\t<option value='inline' $edmod3>".$clang->gT("Inline HTML editor")."</option>\n"
	. "\t\t\t\t<option value='popup' $edmod4>".$clang->gT("Popup HTML editor")."</option>\n";
	$cssummary .= "\t\t\t</select>\n"
	. "\t\t\t<input type='hidden' name='action' value='changehtmleditormode' />\n"
	. ""
	. "\t\t</td>\n"
	. "\t</tr>\n";
	// Other infos
	$cssummary .=  "\t<tr>\n"
	. "\t\t<td align='right'>\n"
	. "\t\t\t<strong>".$clang->gT("Users").":</strong>\n"
	. "\t\t</td><td>\n"
	. "\t\t\t$usercount\n"
	. "\t\t</td>\n"
	. "\t</tr>\n"
	. "\t<tr>\n"
	. "\t\t<td align='right'>\n"
	. "\t\t\t<strong>".$clang->gT("Surveys").":</strong>\n"
	. "\t\t</td><td>\n"
	. "\t\t\t$surveycount\n"
	. "\t\t</td>\n"
	. "\t</tr>\n"
	. "\t<tr>\n"
	. "\t\t<td align='right'>\n"
	. "\t\t\t<strong>".$clang->gT("Active Surveys").":</strong>\n"
	. "\t\t</td><td>\n"
	. "\t\t\t$activesurveycount\n"
	. "\t\t</td>\n"
	. "\t</tr>\n"
	. "\t<tr>\n"
	. "\t\t<td align='right'>\n"
	. "\t\t\t<strong>".$clang->gT("De-activated Surveys").":</strong>\n"
	. "\t\t</td><td>\n"
	. "\t\t\t$deactivatedsurveys\n"
	. "\t\t</td>\n"
	. "\t</tr>\n"
	. "\t<tr>\n"
	. "\t\t<td align='right'>\n"
	. "\t\t\t<strong>".$clang->gT("Active Token Tables").":</strong>\n"
	. "\t\t</td><td>\n"
	. "\t\t\t$activetokens\n"
	. "\t\t</td>\n"
	. "\t</tr>\n"
	. "\t<tr>\n"
	. "\t\t<td align='right'>\n"
	. "\t\t\t<strong>".$clang->gT("De-activated Token Tables").":</strong>\n"
	. "\t\t</td><td>\n"
	. "\t\t\t$deactivatedtokens\n"
	. "\t\t</td>\n"
	. "\t</tr>\n"
	. "</table></form>\n"
	. "<table><tr><td height='1'></td></tr></table>\n";
    
    if ($_SESSION['USER_RIGHT_CONFIGURATOR'] == 1) 
    {
    $cssummary .= "<table><tr><td><form action='$scriptname' method='post'><input type='hidden' name='action' value='showphpinfo' /><input type='submit' value='".$clang->gT("Show PHPInfo")."'></form></td></tr></table>";
    }
}

if ($surveyid)
{
	$query = "SELECT * FROM ".db_table_name('surveys_rights')." WHERE  sid = {$surveyid} AND uid = ".$_SESSION['loginID'];
	$result = $connect->SelectLimit($query, 1);
	if($result->RecordCount() > 0)
	{
		$baselang = GetBaseLanguageFromSurveyID($surveyid);
		$sumquery5 = "SELECT b.* FROM {$dbprefix}surveys AS a INNER JOIN {$dbprefix}surveys_rights AS b ON a.sid = b.sid WHERE a.sid=$surveyid AND b.uid = ".$_SESSION['loginID']; //Getting rights for this survey and user
		$sumresult5 = db_execute_assoc($sumquery5);
		$sumrows5 = $sumresult5->FetchRow();
		$sumquery3 = "SELECT * FROM ".db_table_name('questions')." WHERE sid=$surveyid AND language='".$baselang."'"; //Getting a count of questions for this survey
		$sumresult3 = $connect->Execute($sumquery3);
		$sumcount3 = $sumresult3->RecordCount();
		$sumquery2 = "SELECT * FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='".$baselang."'"; //Getting a count of groups for this survey
		$sumresult2 = $connect->Execute($sumquery2);
		$sumcount2 = $sumresult2->RecordCount();
		$sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
		$sumresult1 = db_select_limit_assoc($sumquery1, 1);

        // Output starts here...
		$surveysummary = "<table width='100%' align='center' bgcolor='#FFFFFF' border='0'>\n";

		$s1row = $sumresult1->FetchRow();

		$s1row = array_map('htmlspecialchars', $s1row);
		$activated = $s1row['active'];
		//BUTTON BAR
		$surveysummary .= "\t<tr>\n"
		. "\t\t<td colspan='2'>\n"
		. "\t\t\t<table class='menubar'>\n"
		. "\t\t\t\t<tr><td align='left'colspan='2' height='4'>"
		. "<strong>".$clang->gT("Survey")."</strong> "
		. "<font class='basic'>{$s1row['surveyls_title']} (ID:$surveyid)</font></td></tr>\n"
		. "\t\t\t\t<tr ><td align='right' height='22'>\n";
		if ($activated == "N" )
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/inactive.png' "
			. "title='' alt='".$clang->gT("This survey is not currently active")."' border='0' hspace='0' align='left'"
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("This survey is not currently active", "js")."');return false\" />\n";
			if($sumrows5['activate_survey'] && $sumcount3>0)
			{
				$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=activate&amp;sid=$surveyid', '_top')\""
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Activate this Survey", "js")."');return false\">" .
				"<img src='$imagefiles/activate.png' name='ActivateSurvey' title='' alt='".$clang->gT("Activate this Survey")."' align='left' /></a>\n" ;
			}
			else
			{
				$surveysummary .= "<img src='$imagefiles/activate_disabled.png' onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Survey cannot be activated. Either you have no permission or there are no questions.", "js")."');return false\" name='ActivateSurvey' title='' alt='".$clang->gT("Survey cannot be activated. Either you have no permission or there are no questions.")."' align='left' />\n" ;
			}
		}
		elseif ($activated == "Y")
		{
			if (($s1row['useexpiry']=='Y') && ($s1row['expires'] < date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust)))
			{
				$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/expired.png' title='' "
				. "alt='".$clang->gT("This survey is active but expired.")."' align='left'"
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("This survey is active but expired", "js")."');return false\" />\n";
			}
			else
			{
				$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/active.png' title='' "
				. "alt='".$clang->gT("This survey is currently active")."' align='left'"
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("This survey is currently active", "js")."');return false\" />\n";
			}
			if($sumrows5['activate_survey'])
			{
				$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=deactivate&amp;sid=$surveyid', '_top')\""
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("De-activate this Survey", "js")."');return false\">" .
				"<img src='$imagefiles/deactivate.png' name='DeactivateSurvey' "
				. "alt='".$clang->gT("De-activate this Survey")."' title='' align='left' /></a>\n" ;
			}
			else
			{
				$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='14' align='left' border='0' hspace='0' />\n";
			}
		}

		$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0' />\n";
		// survey rights

		if($s1row['owner_id'] == $_SESSION['loginID'])
		{
			$surveysummary .= "\t\t\t\t\t<a href=\"#\" onclick=\"window.open('$scriptname?action=surveysecurity&amp;sid=$surveyid', '_top')\"" .
			"onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Survey Security Settings", "js")."');return false\">" .
			"<img src='$imagefiles/survey_security.png' name='SurveySecurity'"
			." title='' alt='".$clang->gT("Survey Security Settings")."'  align='left' /></a>";
		}
		else
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}
		
		if ($activated == "N")
        {
            $icontext=$clang->gT("Test This Survey");
        } else
            {
            $icontext=$clang->gT("Execute This Survey");
            }
		if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
		{
			$surveysummary .= "<a href=\"#\" accesskey='d' onclick=\"window.open('".$publicurl."/index.php?sid=$surveyid&amp;newtest=Y', '_blank')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'$icontext');return false\">"
			."<img  src='$imagefiles/do.png' title='' "
			. "name='DoSurvey' align='left' alt='$icontext' /></a>";
		
		} else {
			$surveysummary .= "<a href=\"#\" accesskey='d' onclick=\"hideTooltip(); document.getElementById('printpopup').style.visibility='hidden'; document.getElementById('testsurvpopup').style.visibility='visible';\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'$icontext');return false\">"
			."<img  src='$imagefiles/do.png' title='' "
			. "name='DoSurvey' align='left' alt='$icontext' /></a>";
			
			$tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
			$baselang = GetBaseLanguageFromSurveyID($surveyid);
			$tmp_survlangs[] = $baselang;
			rsort($tmp_survlangs);
			// Test Survey Language Selection Popup
			$surveysummary .="<div class=\"testsurvpopup\" id=\"testsurvpopup\"><table width=\"100%\"><tr><td>".$clang->gT("Please select a language:")."</td></tr>";
			foreach ($tmp_survlangs as $tmp_lang)
			{
				$surveysummary .= "<tr><td><a href=\"#\" accesskey='d' onclick=\"document.getElementById('testsurvpopup').style.visibility='hidden'; window.open('".$publicurl."/index.php?sid=$surveyid&amp;newtest=Y&amp;lang=".$tmp_lang."', '_blank')\"><font color=\"#097300\"><b>".getLanguageNameFromCode($tmp_lang,false)."</b></font></a></td></tr>";
			}
			$surveysummary .= "<tr><td align=\"center\"><a href=\"#\" accesskey='d' onclick=\"document.getElementById('testsurvpopup').style.visibility='hidden';\"><font color=\"#DF3030\">".$clang->gT("Cancel")."</font></a></td></tr></table></div>";
			
			$tmp_pheight = getPopupHeight();
			$surveysummary .= "<script type='text/javascript'>document.getElementById('testsurvpopup').style.height='".$tmp_pheight."px';</script>";

		}

		if($activated == "Y" && $sumrows5['browse_response'])
		{
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('".$homeurl."/".$scriptname."?action=dataentry&amp;sid=$surveyid', '_top')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Dataentry Screen for Survey", "js")."');return false\">"
			. "<img src='$imagefiles/dataentry.png' title='' align='left' alt='".$clang->gT("Dataentry Screen for Survey")."'"
			. "name='DoDataentry' /></a>\n";
		} 
		else if (!$sumrows5['browse_response'])
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		} else {
			$surveysummary .= "<a href=\"#\" onclick=\"alert('".$clang->gT("This survey is not active, data entry is not allowed","js")."')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Dataentry Screen for Survey", "js")."');return false\">"
			. "<img src='$imagefiles/dataentry_disabled.png' title='' align='left' alt='".$clang->gT("Dataentry Screen for Survey")."'"
			. "name='DoDataentry' /></a>\n";
		}
		
		if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
		{
			
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=showprintablesurvey&amp;sid=$surveyid', '_blank')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Printable Version of Survey", "js")."');return false\">\n"
			. "<img src='$imagefiles/print.png' title='' name='ShowPrintableSurvey' align='left' alt='".$clang->gT("Printable Version of Survey")."' />"
			."</a>"
			. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0' />\n";
		
		} else {
			
			$surveysummary .= "<a href=\"#\" onclick=\"hideTooltip(); document.getElementById('printpopup').style.visibility='visible'; document.getElementById('testsurvpopup').style.visibility='hidden';\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Printable Version of Survey", "js")."');return false\">\n"
			. "<img src='$imagefiles/print.png' title='' name='ShowPrintableSurvey' align='left' alt='".$clang->gT("Printable Version of Survey")."' />"
			."</a>"
			. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0' />\n";
			
			$tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
			$baselang = GetBaseLanguageFromSurveyID($surveyid);
			$tmp_survlangs[] = $baselang;
			rsort($tmp_survlangs);
			
			// Test Survey Language Selection Popup
			$surveysummary .="<div class=\"testsurvpopup\" id=\"printpopup\"><table width=\"100%\"><tr><td>".$clang->gT("Please select a language:")."</td></tr>";
			foreach ($tmp_survlangs as $tmp_lang)
			{
				$surveysummary .= "<tr><td><a href=\"#\" accesskey='d' onclick=\"document.getElementById('printpopup').style.visibility='hidden'; window.open('$scriptname?action=showprintablesurvey&amp;sid=$surveyid&amp;lang=".$tmp_lang."', '_blank')\"><font color=\"#097300\"><b>".getLanguageNameFromCode($tmp_lang,false)."</b></font></a></td></tr>";
			}
			$surveysummary .= "<tr><td align=\"center\"><a href=\"#\" accesskey='d' onclick=\"document.getElementById('printpopup').style.visibility='hidden';\"><font color=\"#DF3030\">".$clang->gT("Cancel")."</font></a></td></tr></table></div>";
			
			$surveysummary .= "<script type='text/javascript'>document.getElementById('printpopup').style.left='152px';</script>";
			
			$tmp_pheight = getPopupHeight();
			$surveysummary .= "<script type='text/javascript'>document.getElementById('printpopup').style.height='".$tmp_pheight."px';</script>";
			
		}

		if($sumrows5['edit_survey_property'])
		{
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=editsurvey&amp;sid=$surveyid', '_top')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Edit Current Survey", "js")."');return false\">" .
			"<img src='$imagefiles/edit.png' title=''name='EditSurvey' align='left' alt='".$clang->gT("Edit Current Survey")."' /></a>" ;
		}
		else
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}


		if ($sumrows5['delete_survey'])
		{
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=deletesurvey&amp;sid=$surveyid', '_top')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Delete Current Survey", "js")."');return false\">\n" .
			"<img src='$imagefiles/delete.png' title='' align='left' name='DeleteWholeSurvey' alt='Delete Current Survey'  /></a>" ;
		}
		else
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}

		if($activated!="Y" && getGroupSum($surveyid,$s1row['language'])>1 && $sumrows5['define_questions'])
		{
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=ordergroups&amp;sid=$surveyid', '_top')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Change Group Order", "js")."');return false\">" .
			"<img src='$imagefiles/reorder.png' title='' alt='".$clang->gT("Change Group Order")."' align='left' name='ordergroups' /></a>" ;
		}
		else
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}

		if ($sumrows5['export'])
		{
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=exportstructure&amp;sid=$surveyid', '_top')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Export Survey Structure", "js")."');return false\">" .
			"<img src='$imagefiles/export.png' title='' alt='". $clang->gT("Export Current Survey")."' align='left' name='ExportSurvey' /></a>" ;
		}
		else
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}

		if ($sumrows5['edit_survey_property'])
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0' />\n"
			. "<a href=\"#\" onclick=\"window.open('$scriptname?action=assessments&amp;sid=$surveyid', '_top')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Set Assessment Rules", "js")."');return false\">" .
			"<img src='$imagefiles/assessments.png' title='' alt='". $clang->gT("Set Assessment Rules")."' align='left' name='SurveyAssessment' /></a>\n" ;
		}
		else
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}
		
		if ($sumrows5['edit_survey_property'])
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0' />\n"
			. "<a href=\"#\" onclick=\"window.open('$scriptname?action=quotas&amp;sid=$surveyid', '_top')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Set Survey Quotas", "js")."');return false\">" .
			"<img src='$imagefiles/quota.png' title='' alt='". $clang->gT("Set Survey Quotas")."' align='left' name='SurveyAssessment' /></a>\n" ;
		}
		else
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}

		if ($activated == "Y" && $sumrows5['browse_response'])
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0' />\n"
			. "<a href=\"#\" onclick=\"window.open('$scriptname?action=browse&amp;sid=$surveyid', '_top')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Browse Responses For This Survey", "js")."');return false\">" .
			"<img src='$imagefiles/browse.png' title=''align='left' name='BrowseSurveyResults' alt='".$clang->gT("Browse Responses For This Survey")."' /></a>\n"
			. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0' />\n";
			if ($s1row['allowsave'] == "Y")
			{
				$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=saved&amp;sid=$surveyid', '_top')\""
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("View Saved but not submitted Responses", "js")."');return false\">"
				. "<img src='$imagefiles/saved.png' title='' align='left'  name='BrowseSaved' alt='".$clang->gT("View Saved but not submitted Responses")."' /></a>"
				. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0' />\n";
			}
		}
		if ($activated == "Y" && ($sumrows5['browse_response'] || $sumrows5['export'] || $sumrows5['activate_survey']))
		{
						$surveysummary .="<a href=\"#\" onclick=\"window.open('$scriptname?action=tokens&amp;sid=$surveyid', '_top')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Activate/Edit Tokens for this Survey", "js")."');return false\">" .
			"<img src='$imagefiles/tokens.png' title='' align='left'  name='TokensControl' alt='".$clang->gT("Activate/Edit Tokens for this Survey")."' /></a>\n" ;
		}
		$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0' />\n"
		. "\t\t\t\t</td>\n"
		. "\t\t\t\t<td align='right' valign='middle' width='400'>\n";
		if (!$gid)
		{
			$surveysummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' title='". $clang->gT("Close this Survey")."' alt='". $clang->gT("Close this Survey")."' align='right'  name='CloseSurveyWindow' "
			. "onclick=\"window.open('$scriptname', '_top')\" />\n";
		}
		else
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' width='21' align='right' "
			. "border='0' hspace='0' alt='' />\n";
		}
		$surveysummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/plus.gif' title='". $clang->gT("Show Details of this Survey")."' alt='". $clang->gT("Show Details of this Survey")."' name='MaximiseSurveyWindow' "
		. "align='right' onclick='document.getElementById(\"surveydetails\").style.display=\"\";' />\n"
		. "\t\t\t\t\t<input type='image' src='$imagefiles/minus.gif' title='". $clang->gT("Hide Details of this Survey")."' alt='". $clang->gT("Hide Details of this Survey")."' name='MinimiseSurveyWindow' "
		. "align='right' onclick='document.getElementById(\"surveydetails\").style.display=\"none\";' />\n"
		. "\t\t\t\t\t<img src='$imagefiles/blank.gif' align='right' border='0' width='18' alt='' />\n"
		. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' align='right' border='0' alt='' hspace='0' />\n";
		if ($activated == "Y")
		{
			$surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' align='right' border='0' hspace='0' />\n";
		}
		elseif($sumrows5['define_questions'])
		{
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('$scriptname?action=addgroup&amp;sid=$surveyid', '_top')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Add New Group to Survey", "js")."');return false\"> " .
			"<img src='$imagefiles/add.png' title='' alt=''align='right'  name='AddNewGroup' /></a>\n" ;
		}
		$surveysummary .= "<font class=\"boxcaption\">".$clang->gT("Groups").":</font>"
		. "\t\t<select name='groupselect' "
		. "onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";

		if (getgrouplistlang($gid, $baselang))
		{
			$surveysummary .= getgrouplistlang($gid, $baselang);
		}
		else
		{
			$surveysummary .= "<option>".$clang->gT("None")."</option>\n";
		}
		$surveysummary .= "</select>\n"
		. "\t\t\t\t</td>"
		. "</tr>\n"
		. "\t\t\t</table>\n"
		. "\t\t</td>\n"
		. "\t</tr>\n";

		//SURVEY SUMMARY
		if ($gid || $qid || $action=="deactivate"|| $action=="activate" || $action=="surveysecurity" 
                 || $action=="surveyrights" || $action=="addsurveysecurity" || $action=="addusergroupsurveysecurity" 
                 || $action=="setsurveysecurity" ||  $action=="setusergroupsurveysecurity" || $action=="delsurveysecurity" 
                 || $action=="editsurvey" || $action=="addgroup" || $action=="importgroup"
                 || $action=="ordergroups" || $action=="updatesurvey" || $action=="deletesurvey"
                 || $action=="exportstructure" || $action=="quotas" ) {$showstyle="style='display: none'";}
		if (!isset($showstyle)) {$showstyle="";}
        $additionnalLanguagesArray = GetAdditionalLanguagesFromSurveyID($surveyid);
		$surveysummary .= "\t<tr id='surveydetails' $showstyle><td><table class='table2columns'><tr><td align='right' valign='top' width='15%'>"
		. "<strong>".$clang->gT("Title").":</strong></td>\n"
		. "\t<td align='left' class='settingentryhighlight'><strong>{$s1row['surveyls_title']} "
		. "(ID {$s1row['sid']})</strong></td></tr>\n";
		$surveysummary2 = "";
		if ($s1row['private'] != "N") {$surveysummary2 .= $clang->gT("This survey is anonymous.")."<br />\n";}
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

		if($sumrows5['edit_survey_property'])
		{
			$surveysummary2 .= $clang->gT("Regenerate Question Codes:")
			. " [<a href='$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=straight' "
			. "onclick='return confirm(\"".$clang->gT("Are you sure you want regenerate the question codes?","js")."\")' "
			. ">".$clang->gT("Straight")."</a>] "
			. "[<a href='$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=bygroup' "
			. "onclick='return confirm(\"".$clang->gT("Are you sure you want regenerate the question codes?","js")."\")' "
			. ">".$clang->gT("By Group")."</a>]";
			$surveysummary2 .= "</td></tr>\n";
		}
		$surveysummary .= "\t<tr>"
		. "<td align='right' valign='top'><strong>"
		. $clang->gT("Survey URL") ." (".getLanguageNameFromCode($s1row['language'],false)."):</strong></td>\n";
		$tmp_url = $GLOBALS['publicurl'] . '/index.php?sid=' . $s1row['sid'];
		$surveysummary .= "\t\t<td align='left'> <a href='$tmp_url&amp;lang=".$s1row['language']."' target='_blank'>$tmp_url&amp;lang=".$s1row['language']."</a>";
        foreach ($additionnalLanguagesArray as $langname)
        {
          $surveysummary .= "&nbsp;<a href='$tmp_url&amp;lang=$langname' target='_blank'><img title='".$clang->gT("Survey URL For Language:")." ".getLanguageNameFromCode($langname,false)."' alt='".getLanguageNameFromCode($langname,false)." ".$clang->gT("Flag")."' src='../images/flags/$langname.png' /></a>";  
        }
        
        
		$surveysummary .= "</td></tr>\n"
		. "\t<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Description:")."</strong></td>\n\t\t<td align='left'>";
		if (trim($s1row['surveyls_description'])!='') {$surveysummary .= " {$s1row['surveyls_description']}";}
		$surveysummary .= "</td></tr>\n"
		. "\t<tr >\n"
		. "\t\t<td align='right' valign='top'><strong>"
		. $clang->gT("Welcome:")."</strong></td>\n"
		. "\t\t<td align='left'> {$s1row['surveyls_welcometext']}</td></tr>\n"
		. "\t<tr ><td align='right' valign='top'><strong>"
		. $clang->gT("Administrator:")."</strong></td>\n"
		. "\t\t<td align='left'> {$s1row['admin']} ({$s1row['adminemail']})</td></tr>\n"
		. "\t<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Fax To:")."</strong></td>\n\t\t<td align='left'>";
		if (trim($s1row['faxto'])!='') {$surveysummary .= " {$s1row['faxto']}";}
		$surveysummary .= "</td></tr>\n"
		. "\t<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Expiry Date:")."</strong></td>\n";
		if ($s1row['useexpiry']== "Y")
		{
			$expdate=$s1row['expires'];
		}
		else
		{
			$expdate="-";
		}
		$surveysummary .= "\t<td align='left'>$expdate</td></tr>\n"
		. "\t<tr ><td align='right' valign='top'><strong>"
		. $clang->gT("Template:")."</strong></td>\n"
		. "\t\t<td align='left'> {$s1row['template']}</td></tr>\n"
		
		. "\t<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Base Language:")."</strong></td>\n";
		if (!$s1row['language']) {$language=getLanguageNameFromCode($currentadminlang);} else {$language=getLanguageNameFromCode($s1row['language']);}
		$surveysummary .= "\t<td align='left'>$language</td></tr>\n";

		// get the rowspan of the Additionnal languages row
		// is at least 1 even if no additionnal language is present
		$additionnalLanguagesCount = count($additionnalLanguagesArray);
		if ($additionnalLanguagesCount == 0) $additionnalLanguagesCount = 1;
		$surveysummary .= "\t<tr><td align='right' valign='top' rowspan='".$additionnalLanguagesCount."'><strong>"
		. $clang->gT("Additional Languages").":</strong></td>\n";

		$first=true;
		foreach ($additionnalLanguagesArray as $langname)
		{
			if ($langname)
			{
				if (!$first) {$surveysummary .= "\t\t\t<tr>";}
				$first=false;
				$surveysummary .= "<td align='left'>".getLanguageNameFromCode($langname)."</td></tr>\n";
			}
		}
		if ($first) $surveysummary .= "\t</tr>";

		if ($s1row['surveyls_urldescription']==""){$s1row['surveyls_urldescription']=$s1row['url'];}
		$surveysummary .= "\t<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Exit Link").":</strong></td>\n"
		. "\t\t<td align='left'>";
		if ($s1row['url']!="") {$surveysummary .=" <a href=\"{$s1row['url']}\" title=\"{$s1row['url']}\">{$s1row['surveyls_urldescription']}</a>";}
		$surveysummary .="</td></tr>\n";
		$surveysummary .= "\t<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Number of questions/groups").":</strong></td><td>$sumcount3/$sumcount2</td></tr>\n";
        $surveysummary .= "\t<tr><td align='right' valign='top'><strong>"
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
                $surveysummary .= "\t<tr><td align='right' valign='top'><strong>"
                . $clang->gT("Survey table name").":</strong></td><td>".$dbprefix."survey_$surveyid</td></tr>\n";
		}
        $surveysummary .= "\t<tr><td align='right' valign='top'><strong>"
                . $clang->gT("Hints").":</strong></td><td>\n";

        if ($activated == "N" && $sumcount3 == 0)
        {
			$surveysummary .= $clang->gT("Survey cannot be activated yet.")."<br />\n";
			if ($sumcount2 == 0 && $sumrows5['define_questions'])
			{
				$surveysummary .= "\t<font class='statusentryhighlight'>[".$clang->gT("You need to add groups")."]</font><br />";
			}
			if ($sumcount3 == 0 && $sumrows5['define_questions'])
			{
				$surveysummary .= "\t<font class='statusentryhighlight'>[".$clang->gT("You need to add questions")."]</font><br />";
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
	$sumresult4 = $connect->Execute($sumquery4);
	$sumcount4 = $sumresult4->RecordCount();
	$grpquery ="SELECT * FROM ".db_table_name('groups')." WHERE gid=$gid AND
	language='".$baselang."' ORDER BY ".db_table_name('groups').".group_order";
	$grpresult = db_execute_assoc($grpquery);

	// Check if other questions/groups are dependent upon this group
	$condarray=GetGroupDepsForConditions($surveyid,"all",$gid,"by-targgid");	

	$groupsummary = "<table width='100%' align='center' bgcolor='#FFFFFF' border='0'>\n";
	while ($grow = $grpresult->FetchRow())
	{
		$grow = array_map('htmlspecialchars', $grow);
		$groupsummary .= "\t<tr>\n"
		. "\t\t<td colspan='2'>\n"
		. "\t\t\t<table class='menubar'>\n"
		. "\t\t\t\t<tr><td align='left' colspan='2' height='4'>"
		. "<strong>".$clang->gT("Group")."</strong> "
		. "<font class='basic'>{$grow['group_name']} (ID:$gid)</font></td></tr>\n"
		. "\t\t\t\t<tr>\n"
		. "\t\t\t\t\t<td>\n"
		. "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='55' height='20' border='0' hspace='0' align='left' />\n"
		. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n"
		. "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='160' height='20' border='0' hspace='0' align='left' />\n"
		. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n";

		if($sumrows5['define_questions'])
		{
			$groupsummary .=  "<a href=\"#\" onclick=\"window.open('$scriptname?action=editgroup&amp;sid=$surveyid&amp;gid=$gid','_top')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Edit Current Group", "js")."');return false\">" .
			"<img src='$imagefiles/edit.png' title='' alt='' name='EditGroup' align='left' /></a>\n" ;
		}
		else
		{
			$groupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}

		if ((($sumcount4 == 0 && $activated != "Y") || $activated != "Y") && $sumrows5['define_questions'])
		{
			if (is_null($condarray))
			{
				$groupsummary .= "\t\t\t\t\t<a href='$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid' onclick=\"return confirm('".$clang->gT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?","js")."')\""
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Delete Current Group", "js")."');return false\">"
				. "<img src='$imagefiles/delete.png' alt='' name='DeleteWholeGroup' title='' align='left' border='0' hspace='0' /></a>";
			}
			else
			{
				$groupsummary .= "\t\t\t\t\t<a href='$scriptname?sid=$surveyid&amp;gid=$gid' onclick=\"alert('".$clang->gT("Impossible to delete this group because there is at least one question having a condition on its content","js")."')\" "
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Disabled","js")."-".$clang->gT("Delete Current Group", "js")."');return false\">"
				. "<img src='$imagefiles/delete_disabled.png' alt='' name='DeleteWholeGroup' title='' align='left' border='0' hspace='0' /></a>";
			}
		}
		else
		{
			$groupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}
		if(($activated!="Y" && getQuestionSum($surveyid, $gid)>1) && $sumrows5['define_questions'])
		{
			$groupsummary .= "<a href='$scriptname?action=orderquestions&amp;sid=$surveyid&amp;gid=$gid' onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Change Question Order", "js")."');return false\">"
			. "<img src='$imagefiles/reorder.png' title='' alt='".$clang->gT("Change Question Order")."' name='updatequestionorder' align='left' /></a>" ;
		}
		else
		{
			$groupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}
		if($sumrows5['export'])
		{

			$groupsummary .="<a href='$scriptname?action=dumpgroup&amp;sid=$surveyid&amp;gid=$gid' onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Export Current Group", "js")."');return false\">" .
			"<img src='$imagefiles/exportcsv.png' title='' alt=''name='ExportGroup' align='left' /></a>";
		}
		else
		{
			$groupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}

		$groupsummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n"
		. "\t\t\t\t\t</td>\n"
		. "\t\t\t\t\t<td align='right' width='400'>\n";

		if (!$qid)
		{
			$groupsummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' title='"
			. $clang->gT("Close this Group")."' alt='". $clang->gT("Close this Group")."' align='right'  name='CloseSurveyWindow' "
			. "onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\" />\n";
		}
		else
		{
			$groupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' align='right' border='0' hspace='0' />\n";
		}
		$groupsummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/plus.gif' title='"
		. $clang->gT("Show Details of this Group")."' alt='". $clang->gT("Show Details of this Group")."' name='MaximiseGroupWindow' "
		. "align='right' onclick='document.getElementById(\"groupdetails\").style.display=\"\";' />"
		. "\t\t\t\t\t<input type='image' src='$imagefiles/minus.gif' title='"
		. $clang->gT("Hide Details of this Group")."' alt='". $clang->gT("Hide Details of this Group")."' name='MinimiseGroupWindow' "
		. "align='right'  onclick='document.getElementById(\"groupdetails\").style.display=\"none\";' />\n"
		. "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' align='right' border='0' width='20' height='20' />\n"
		. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='right' border='0' hspace='0' />\n";
		if ($activated == "Y")
		{
			$groupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' border='0' hspace='0' align='right' />\n";
		}
		elseif($sumrows5['define_questions'])
		{
			$groupsummary .= "<a href='$scriptname?action=addquestion&amp;sid=$surveyid&amp;gid=$gid'"
			."onmouseout=\"hideTooltip()\""
			."onmouseover=\"showTooltip(event,'".$clang->gT("Add New Question to Group", "js")."');return false\">" .
			"<img src='$imagefiles/add.png' title='' alt='' " .
			"align='right' name='AddNewQuestion' onclick=\"window.open('', '_top')\" /></a>\n";
		}
		$groupsummary .= "\t\t\t\t\t<font class=\"boxcaption\">".$clang->gT("Questions").":</font>&nbsp;<select class=\"listboxquestions\" name='qid' "
		. "onchange=\"window.open(this.options[this.selectedIndex].value, '_top')\">\n"
		. getquestions()
		. "\t\t\t\t\t</select>\n"
		. "\t\t\t\t</td></tr>\n"
		. "\t\t\t</table>\n"
		. "\t\t</td>\n"
		. "\t</tr>\n";
		if ($qid || $action=='editgroup'|| $action=='addquestion') {$gshowstyle="style='display: none'";}
		else	  {$gshowstyle="";}

		$groupsummary .= "\t<tr id='groupdetails' $gshowstyle><td><table class='table2columns' ><tr ><td width='20%' align='right'><strong>"
		. $clang->gT("Title").":</strong></td>\n"
		. "\t<td align='left'>"
		. "{$grow['group_name']} ({$grow['gid']})</td></tr>\n"
		. "\t<tr><td valign='top' align='right'><strong>"
		. $clang->gT("Description:")."</strong></td>\n\t<td align='left'>";
		if (trim($grow['description'])!='') {$groupsummary .=$grow['description'];}
		$groupsummary .= "</td></tr>\n";

		if (!is_null($condarray))
		{
			$groupsummary .= "\t<tr><td align='right'><strong>"
			. $clang->gT("Questions with conditions to this group").":</strong></td>\n"
			. "\t<td valign='bottom' align='left'>";
			foreach ($condarray[$gid] as $depgid => $deprow)
			{
				foreach ($deprow['conditions'] as $depqid => $depcid)
				{
					//$groupsummary .= "[QID: ".$depqid."]"; 
					$listcid=implode("-",$depcid);
					$groupsummary .= " <a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$depgid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."')\">[QID: ".$depqid."]</a>"; 
				}
			}
		}
	}
	$groupsummary .= "\n</table></td></tr></table>\n";
}

if ($surveyid && $gid && $qid)  // Show the question toolbar
{
	// TODO: check that surveyid is set and that so is $baselang
	//Show Question Details
	$qrq = "SELECT * FROM ".db_table_name('answers')." WHERE qid=$qid AND language='".$baselang."' ORDER BY sortorder, answer";
	$qrr = $connect->Execute($qrq);
	$qct = $qrr->RecordCount();
	$qrquery = "SELECT * FROM ".db_table_name('questions')." WHERE gid=$gid AND sid=$surveyid AND qid=$qid AND language='".$baselang."'";
	$qrresult = db_execute_assoc($qrquery) or die($qrquery."<br />".$connect->ErrorMsg());
	$questionsummary = "<table width='100%' align='center' border='0'>\n";

	// Check if other questions in the Survey are dependent upon this question
	$condarray=GetQuestDepsForConditions($surveyid,"all","all",$qid,"by-targqid","outsidegroup");

	while ($qrrow = $qrresult->FetchRow())
	{
		$qrrow = array_map('htmlspecialchars', $qrrow);
		$questionsummary .= "\t<tr>\n"
		. "\t\t<td colspan='2'>\n"
		. "\t\t\t<table class='menubar'>\n"
		. "\t\t\t\t<tr><td colspan='2' height='4' align='left'><strong>"
		. $clang->gT("Question")."</strong> <font class='basic'>{$qrrow['question']} (ID:$qid)</font></td></tr>\n"
		. "\t\t\t\t<tr>\n"
		. "\t\t\t\t\t<td>\n"
		. "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='55' height='20' border='0' hspace='0' align='left' />\n"
		. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n"
		. "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='160' height='20' border='0' hspace='0' align='left' />\n"
		. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n";

		if($sumrows5['define_questions'])
		{
			$questionsummary .= "<a href='$scriptname?action=editquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'" .
			"onmouseout=\"hideTooltip()\""
			."onmouseover=\"showTooltip(event,'".$clang->gT("Edit Current Question", "js")."');return false\">" .
			"<img src='$imagefiles/edit.png' title='' alt='' align='left' name='EditQuestion' /></a>\n" ;
		}
		else
		{
			$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}

		if ((($qct == 0 && $activated != "Y") || $activated != "Y") && $sumrows5['define_questions'])
		{
			if (is_null($condarray))
			{
				$questionsummary .= "\t\t\t\t\t<a href='$scriptname?action=delquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'" .
				"onclick=\"return confirm('".$clang->gT("Deleting this question will also delete any answers it includes. Are you sure you want to continue?","js")."')\""
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Delete Current Question", "js")."');return false\">"
				. "<img src='$imagefiles/delete.png' name='DeleteWholeQuestion' alt= '' title='' "
				."align='left' border='0' hspace='0' /></a>\n";
			}
			else
			{
				$questionsummary .= "\t\t\t\t\t<a href='$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid'" .
				"onclick=\"alert('".$clang->gT("Impossible to delete this question because  there is at least one question having a condition on it","js")."')\""
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Disabled","js")."-".$clang->gT("Delete Current Question", "js")."');return false\">"
				. "<img src='$imagefiles/delete_disabled.png' name='DeleteWholeQuestion' alt= '' title='' "
				."align='left' border='0' hspace='0' /></a>\n";
			}
		}
		else {$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";}
		$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";

		if($sumrows5['export'])
		{
			$questionsummary .= "<a href='$scriptname?action=dumpquestion&amp;sid=$surveyid&amp;qid=$qid' onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Export this Question", "js")."');return false\">" .
			"<img src='$imagefiles/exportcsv.png' title=''"
			. "alt=''align='left' name='ExportQuestion' /></a>\n";
		}
		else
		{
			$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}
		$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n";

		if($sumrows5['define_questions'])
		{
			if ($activated != "Y")
			{
				$questionsummary .= "<a href='$scriptname?action=copyquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'" .
				"onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Copy Current Question", "js")."');return false\">" .
				"<img src='$imagefiles/copy.png' title=''alt='' align='left' name='CopyQuestion' /></a>\n"
				. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n";
			}
			else
			{
				$questionsummary .= "<a href='#'" .
				"onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Disabled","js")."-".$clang->gT("Copy Current Question", "js")."');return false\" onclick=\"alert('".$clang->gT("Copy question is not possible in an Active survey","js")."')\">" .
				"<img src='$imagefiles/copy_disabled.png' title=''alt='' align='left' name='CopyQuestion' /></a>\n"
				. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n";
			}
		}
		else
		{
			$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}
		if($sumrows5['define_questions'])
		{
			$questionsummary .= "<a href='#' onclick=\"window.open('$scriptname?action=conditions&amp;sid=$surveyid&amp;qid=$qid', 'conditions', 'menubar=no, location=no, status=no, height=475, width=650, scrollbars=yes, resizable=yes, left=50, top=50')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Set Conditions for this Question", "js")."');return false\">"
			. "<img src='$imagefiles/conditions.png' title='' alt='' align='left' name='SetQuestionConditions' /></a>\n"
			. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n";
		}
		else
		{
			$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}
		if($sumrows5['define_questions'])
		{
			if (count(GetAdditionalLanguagesFromSurveyID($surveyid)) == 0)
			{
			$questionsummary .= "<a href=\"#\" accesskey='d' onclick=\"window.open('$scriptname?action=previewquestion&amp;sid=$surveyid&amp;qid=$qid', '_blank')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Preview This Question", "js")."');return false\">"
			. "<img src='$imagefiles/preview.png' title='' alt='' align='left' name='previewquestion' /></a>\n"
			. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n";
			} else {
				$questionsummary .= "<a href=\"#\" accesskey='d' onclick=\"hideTooltip(); document.getElementById('printpopup').style.visibility='hidden'; document.getElementById('testsurvpopup').style.visibility='hidden'; document.getElementById('previewquestion').style.visibility='visible';\""
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Preview This Question", "js")."');return false\">"
				. "<img src='$imagefiles/preview.png' title='' alt='' align='left' name='previewquestion' /></a>\n"
				. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n";
						
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
			$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}
		if($sumrows5['define_questions'])
		{
			if ($qrrow['type'] == "O" || $qrrow['type'] == "L" || $qrrow['type'] == "!" || $qrrow['type'] == "!" || $qrrow['type'] == "M" || $qrrow['type'] == "Q" || $qrrow['type']=="A" || $qrrow['type'] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "E" || $qrrow['type'] == "F" || $qrrow['type'] == "H" || $qrrow['type'] == "P" || $qrrow['type'] == "R" || $qrrow['type'] == "K" || $qrrow['type'] == "1")
			{
			$questionsummary .= "\t\t\t\t\t" .
			"<a href='".$scriptname."?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;viewanswer=Y'" .
			"onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Edit/Add Answers for this Question", "js")."');return false\">" .
			"<img src='$imagefiles/answers.png' alt='' title='' align='left' name='ViewAnswers' /></a>\n" ;
			}
		}
		else
		{
			$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}
		$questionsummary .= "\t\t\t\t\t</td>\n"
		. "\t\t\t\t\t<td align='right' width='400' valign='top'>\n"
		. "\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' title='"
		. $clang->gT("Close this Question")."' alt='". $clang->gT("Close this Question")."' align='right' name='CloseQuestionWindow' "
		. "onclick=\"window.open('$scriptname?sid=$surveyid&amp;gid=$gid', '_top')\" />\n"
		. "\t\t\t\t\t<input type='image' src='$imagefiles/plus.gif' title='"
		. $clang->gT("Show Details of this Question")."'  alt='". $clang->gT("Show Details of this Question")."'align='right'  name='MaximiseQuestionWindow' "
		. "onclick='document.getElementById(\"questiondetails\").style.display=\"\";' />"
		. "\t\t\t\t\t<input type='image' src='$imagefiles/minus.gif' title='"
		. $clang->gT("Hide Details of this Question")."'  alt='". $clang->gT("Hide Details of this Question")."'align='right'  name='MinimiseQuestionWindow' "
		. "onclick='document.getElementById(\"questiondetails\").style.display=\"none\";' />\n"
		. "\t\t\t\t</td></tr>\n"
		. "\t\t\t</table>\n"
		. "\t\t</td>\n"
		. "\t</tr>\n";
		if (returnglobal('viewanswer') || $action =="editquestion" || $action =="copyquestion")	{$qshowstyle = "style='display: none'";}
		else							{$qshowstyle = "";}
		$questionsummary .= "\t<tr $qshowstyle id='questiondetails'><td><table class='table2columns'><tr><td width='20%' align='right'><strong>"
		. $clang->gT("Code:")."</strong></td>\n"
		. "\t<td align='left'>{$qrrow['title']}";
		if ($qrrow['type'] != "X")
		{
			if ($qrrow['mandatory'] == "Y") {$questionsummary .= ": (<i>".$clang->gT("Mandatory Question")."</i>)";}
			else {$questionsummary .= ": (<i>".$clang->gT("Optional Question")."</i>)";}
		}
		$questionsummary .= "</td></tr>\n"
		. "\t<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Question:")."</strong></td>\n\t<td align='left'>{$qrrow['question']}</td></tr>\n"
		. "\t<tr><td align='right' valign='top'><strong>"
		. $clang->gT("Help:")."</strong></td>\n\t<td align='left'>";
		if (trim($qrrow['help'])!=''){$questionsummary .= "{$qrrow['help']}";}
		$questionsummary .= "</td></tr>\n";
		if ($qrrow['preg'])
		{
			$questionsummary .= "\t<tr ><td align='right' valign='top'><strong>"
			. $clang->gT("Validation:")."</strong></td>\n\t<td align='left'>{$qrrow['preg']}"
			. "</td></tr>\n";
		}
		$qtypes = getqtypelist("", "array"); //qtypes = array(type code=>type description)
		$questionsummary .= "\t<tr><td align='right' valign='top'><strong>"
		.$clang->gT("Type:")."</strong></td>\n\t<td align='left'>{$qtypes[$qrrow['type']]}";
		$questionsummary .="</td></tr>\n";
		if ($qct == 0 && ($qrrow['type'] == "O" || $qrrow['type'] == "L" || $qrrow['type'] == "!" || $qrrow['type'] == "M" || $qrrow['type'] == "Q" || $qrrow['type'] == "K" || $qrrow['type'] == "A" || $qrrow['type'] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "E" || $qrrow['type'] == "P" || $qrrow['type'] == "R" || $qrrow['type'] == "F"  || $qrrow['type'] == "1" ||$qrrow['type'] == "H"))
		{
			$questionsummary .= "\t\t<tr ><td></td><td align='left'>"
			. "<font face='verdana' size='1' color='red'>"
			. $clang->gT("Warning").": ". $clang->gT("You need to add answers to this question")." "
			. "<input align='top' type='image' src='$imagefiles/answerssmall.png' title='"
			. $clang->gT("Edit/Add Answers for this Question")."' name='EditThisQuestionAnswers'"
			. "onclick=\"window.open('".$scriptname."?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;viewanswer=Y', '_top')\" /></font></td></tr>\n";
		}
		
		// For Labelset Questions show the label set and warn if there is no label set configured
		if (($qrrow['type'] == "1" || $qrrow['type'] == "F" ||  $qrrow['type'] == "H" || $qrrow['type'] == "W" ||  $qrrow['type'] == "Z"))
		{
			$questionsummary .= "\t\t<tr ><td align='right'><strong>". $clang->gT("Label Set").":</strong></td>";
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
			if ($sumrows5['define_questions'])
			{
			$questionsummary .= "<input align='top' type='image' src='$imagefiles/labelssmall.png' title='"
			. $clang->gT("Edit/Add Label Sets")."' name='EditThisLabelSet' "
			. "onclick=\"window.open('$scriptname?action=labels&amp;lid={$qrrow['lid']}', '_blank')\" />\n";
			}
			$questionsummary .= "</td></tr>";
		}
			  
		
		if ($qrrow['type'] == "M" or $qrrow['type'] == "P")
		{
			$questionsummary .= "\t<tr>"
			. "<td align='right' valign='top'><strong>"
			. $clang->gT("Other:")."</strong></td>\n"
			. "\t<td align='left'>";
			$questionsummary .= ($qrrow['other'] == "Y") ? ($clang->gT("Yes")) : ($clang->gT("No")) ;
			$questionsummary .= "</td></tr>\n";
		}
		if (isset($qrrow['mandatory']) and ($qrrow['type'] != "X"))
		{
			$questionsummary .= "\t<tr>"
			. "<td align='right' valign='top'><strong>"
			. $clang->gT("Mandatory:")."</strong></td>\n"
			. "\t<td align='left'>";
			$questionsummary .= ($qrrow['mandatory'] == "Y") ? ($clang->gT("Yes")) : ($clang->gT("No")) ;
			$questionsummary .= "</td></tr>\n";
		}
		if (!is_null($condarray))
		{
			$questionsummary .= "\t<tr>"
			. "<td align='right' valign='top'><strong>"
			. $clang->gT("Other questions having conditions on this question:")
			. "\t</strong></td>\n<td align='left' valign='bottom'>\n";
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
	$questionsummary .= "</td></tr></table>";
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
        $qresult = db_execute_assoc($qquery);
        $qrow = $qresult->FetchRow();
        if ($qrow["num_ans"] == 0)   // means that no record for the language exists in the answers table
        {
            $qquery = "INSERT INTO ".db_table_name('answers')." (SELECT `qid`,`code`,`answer`,`default_value`,`sortorder`, '".$language."' FROM ".db_table_name('answers')." WHERE qid=$qid AND language='".$baselang."')";
            $connect->Execute($qquery);
        }
    }

    array_unshift($anslangs,$baselang);      // makes an array with ALL the languages supported by the survey -> $anslangs
    
    //delete the answers in languages not supported by the survey
    $qquery = "SELECT DISTINCT language FROM ".db_table_name('answers')." WHERE (qid = $qid) AND (language NOT IN ('".implode("','",$anslangs)."'))";
    $qresult = db_execute_assoc($qquery);
    while ($qrow = $qresult->FetchRow())
    {
        $qquery = "DELETE FROM ".db_table_name('answers')." WHERE (qid = $qid) AND (language = '".$qrow["language"]."')";
        $connect->Execute($qquery);
    }
    
	
	// Check sort order for answers
	$qquery = "SELECT type FROM ".db_table_name('questions')." WHERE qid=$qid AND language='".$baselang."'";
	$qresult = db_execute_assoc($qquery);
	while ($qrow=$qresult->FetchRow()) {$qtype=$qrow['type'];}
	if (!isset($_POST['ansaction']))
	{
		//check if any nulls exist. If they do, redo the sortorders
		$caquery="SELECT * FROM ".db_table_name('answers')." WHERE qid=$qid AND sortorder is null AND language='".$baselang."'";
		$caresult=$connect->Execute($caquery);
		$cacount=$caresult->RecordCount();
		if ($cacount)
		{
			fixsortorderAnswers($qid); // !!Adjust this!!
		}
	}

	// Print Key Control JavaScript
	$vasummary = keycontroljs();
	$vasummary .= PrepareEditorScript("editanswer");

	$vasummary .= "\t<table width='100%' >\n"
	."<tr  >\n"
	."\t<td colspan='4' class='settingcaption'>\n"
	.$clang->gT("Edit Answers")
	."\t</td>\n"
	."</tr>\n"
	."\t<tr><td colspan='5'><form name='editanswers' method='post' action='$scriptname'>\n"
	. "\t<input type='hidden' name='sid' value='$surveyid' />\n"
	. "\t<input type='hidden' name='gid' value='$gid' />\n"
	. "\t<input type='hidden' name='qid' value='$qid' />\n"
	. "\t<input type='hidden' name='viewanswer' value='Y' />\n"
	. "<input type='hidden' name='sortorder' value='' />\n"
	. "\t<input type='hidden' name='action' value='modanswer' />\n";
	$vasummary .= "<div class='tab-pane' id='tab-pane-1'>";
	$first=true;
	$sortorderids=''; 
	$codeids='';

	$vasummary .= "\t<div id='xToolbar'></div>\n";

	foreach ($anslangs as $anslang)
	{
		$position=0;
    	$query = "SELECT * FROM ".db_table_name('answers')." WHERE qid='{$qid}' AND language='{$anslang}' ORDER BY sortorder, code";
		$result = db_execute_assoc($query) or die($connect->ErrorMsg());
		$anscount = $result->RecordCount();
        $vasummary .= "<div class='tab-page'>"
                ."<h2 class='tab'>".getLanguageNameFromCode($anslang, false);
        if ($anslang==GetBaseLanguageFromSurveyID($surveyid)) {$vasummary .= '('.$clang->gT("Base Language").')';}
                
        $vasummary .= "</h2>\t<table width='100%' style='border: solid; border-width: 0px; border-color: #555555' cellspacing='0'>\n"
                ."<thead align='center'>"
        		."<tr bgcolor='#F8F8FF'>\n"
        		."\t<td width='20%' align='right'><strong><font size='1' face='verdana' >\n"
        		.$clang->gT("Code")
        		."\t</font></strong></td>\n"
        		."\t<td width='50%'><strong><font size='1' face='verdana'>\n"
        		.$clang->gT("Answer")
        		."\t</font></strong></td>\n"
        		."\t<td width='20%'><strong><font size='1' face='verdana'>\n"
        		.$clang->gT("Action")
        		."\t</font></strong></td>\n"
        		."\t<td width='10%' align='center'><strong><font size='1' face='verdana'>\n"
        		.$clang->gT("Order")
        		."\t</font></strong>";
              	
        		
        		
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
			if ($row['default_value'] == 'Y') $vasummary .= "<font color='#FF0000'>".$clang->gT("Default")."</font>";

			if (($activated != 'Y' && $first) || ($activated == 'Y' && $first && (($qtype=='O')  || ($qtype=='L') || ($qtype=='!') ))) 
			{
				$vasummary .= "\t<input type='text' name='code_{$row['sortorder']}' value=\"{$row['code']}\" maxlength='5' size='5'"
				."onkeypress=\" if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_$anslang').click(); return false;} return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_')\""
				." />";
			}
			elseif (($activated != 'N' && $first) ) // If survey is activated and its not one of the above question types who allows modfying answers on active survey
			{
				$vasummary .= "\t<input type='hidden' name='code_{$row['sortorder']}' value=\"{$row['code']}\" maxlength='5' size='5'"
				." />{$row['code']}";
				
			}
			else
			{
				$vasummary .= "\t{$row['code']}";
			
			}

			$vasummary .= "\t</td>\n"
			."\t<td width='50%'>\n"
			."\t<input type='text' name='answer_{$row['language']}_{$row['sortorder']}' maxlength='1000' size='80' value=\"{$row['answer']}\" onkeypress=\" if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('saveallbtn_$anslang').click(); return false;}\" />\n"
			. getEditor("editanswer","answer_".$row['language']."_".$row['sortorder'], "[".$clang->gT("Answer:", "js")."](".$row['language'].")",'','','',$action)
			."\t</td>\n"
			."\t<td width='20%'>\n";
			
			// Deactivate delete button for active surveys
			if ($activated != 'Y' || ($activated == 'Y' && (($qtype=='O' ) || ($qtype=='L' ) ||($qtype=='!' ))))
			{
				$vasummary .= "\t<input type='submit' name='method' value='".$clang->gT("Del")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
			}
			else
			{
				$vasummary .= "\t<input type='submit' disabled='disabled 'name='method' value='".$clang->gT("Del")."' />\n";
			}

			// Don't show Default Button for array question types
			if ($qtype != "A" && $qtype != "B" && $qtype != "C" && $qtype != "E" && $qtype != "F" && $qtype != "H" && $qtype != "R" && $qtype != "Q" && $qtype != "1") $vasummary .= "\t<input type='submit' name='method' value='".$clang->gT("Default")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
			$vasummary .= "\t</td>\n"
			."\t<td>\n";
			if ($position > 0)
			{
				$vasummary .= "\t<input type='submit' name='method' value='".$clang->gT("Up")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
			};
			if ($position < $anscount-1)
			{
				// Fill the sortorder hiddenfield so we now what field is moved down
				$vasummary .= "\t<input type='submit' name='method' value='".$clang->gT("Dn")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
			}
			$vasummary .= "\t</td></tr>\n";
			$position++;
		}
		if ($anscount > 0)
		{
			$vasummary .= "\t<tr><td colspan='4'><center><input type='submit' id='saveallbtn_$anslang' name='method' value='".$clang->gT("Save All")."'  />"
			."</center></td></tr>\n";
		}
		$position=sprintf("%05d", $position);
		if ($activated != 'Y' || (($activated == 'Y') && (($qtype=='O' ) || ($qtype=='L' ) ||($qtype=='!' ))))
		{
			
            if ($first==true)
			{
				$vasummary .= "<tr><td><br /></td></tr><tr><td width='20%' align='right'>"
				."<strong>".$clang->gT("New Answer").":</strong> ";
				$vasummary .= "\t<input type='text' name='insertcode' value=\"{$row['code']}\"id='addnewanswercode' maxlength='5' size='5' "
				." onkeypress=\" if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('newanswerbtn').click(); return false;} return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_')\""
				." />";


            	$first=false;
				$vasummary .= "\t</td>\n"
				."\t<td width='50%'>\n"
				."\t<input type='text' maxlength='1000' name='insertanswer' size='80' onkeypress=\" if(event.keyCode==13) {if (event && event.preventDefault) event.preventDefault(); document.getElementById('newanswerbtn').click(); return false;}\" />\n"
				. getEditor("addanswer","insertanswer", "[".$clang->gT("Answer:", "js")."]",'','','',$action)
				."\t</td>\n"
				."\t<td width='20%'>\n"
				."\t<input type='submit' id='newanswerbtn' name='method' value='".$clang->gT("Add new Answer")."' />\n"
				."\t<input type='hidden' name='action' value='modanswer' />\n"
				."\t</td>\n"
				."\t<td>\n"
				."<script type='text/javascript'>\n"
				."<!--\n"
				."document.getElementById('addnewanswercode').focus();\n"
				."//-->\n"
				."</script>\n"
				."\t</td>\n"
				."</tr>\n";
			}
		}
		else
		{
			$vasummary .= "<tr>\n"
			."\t<td colspan='4' align='center'>\n"
			."<font color='red' size='1'><i><strong>"
			.$clang->gT("Warning")."</strong>: ".$clang->gT("You cannot add answers or edit answer codes for this question type because the survey is active.")."</i></font>\n"
			."\t</td>\n"
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

	$query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID']." AND owner_id != ".$_POST['uid'];
	$result = db_execute_assoc($query);
	if($result->RecordCount() > 0)
	{
		if($_POST['uid'] > 0){

			$isrquery = "INSERT INTO {$dbprefix}surveys_rights VALUES($surveyid,". $_POST['uid'].",0,0,0,0,0,0)";
			$isrresult = $connect->Execute($isrquery);

			if($isrresult)
			{
				$addsummary .= "<br />".$clang->gT("User added.")."<br />\n";
				$addsummary .= "<br /><form method='post' action='$scriptname?sid={$surveyid}'>"
				."<input type='submit' value='".$clang->gT("Set Survey Rights")."' />"
				."<input type='hidden' name='action' value='setsurveysecurity' />"
				//."<input type='hidden' name='user' value='{$_POST['user']}'>"
				."<input type='hidden' name='uid' value='{$_POST['uid']}' />"
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
	$result = db_execute_assoc($query);
	if($result->RecordCount() > 0)
	{
		if($_POST['ugid'] > 0){
			$query2 = "SELECT b.uid FROM (SELECT uid FROM ".db_table_name('surveys_rights')." WHERE sid = {$surveyid}) AS c RIGHT JOIN ".db_table_name('user_in_groups')." AS b ON b.uid = c.uid WHERE c.uid IS NULL AND b.ugid = {$_POST['ugid']}";
			$result2 = db_execute_assoc($query2);
			if($result2->RecordCount() > 0)
			{
				while ($row2 = $result2->FetchRow())
				{
					$uid_arr[] = $row2['uid'];
					$values[] = "($surveyid, {$row2['uid']},0,0,0,0,0,0)";
				}
				$values_implode = implode(",", $values);

				$isrquery = "INSERT INTO {$dbprefix}surveys_rights VALUES ".$values_implode;
				$isrresult = $connect->Execute($isrquery);

				if($isrresult)
				{
					$addsummary .= "<br />".$clang->gT("User Group added.")."<br />\n";
					$_SESSION['uids'] = $uid_arr;
					$addsummary .= "<br /><form method='post' action='$scriptname?sid={$surveyid}'>"
					."<input type='submit' value='".$clang->gT("Set Survey Rights")."' />"
					."<input type='hidden' name='action' value='setusergroupsurveysecurity' />"
					."<input type='hidden' name='ugid' value='{$_POST['ugid']}' />"
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

		$query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID']." AND owner_id != ".$_POST['uid'];
		$result = db_execute_assoc($query);
		if($result->RecordCount() > 0)
		{
			if (isset($_POST['uid']))
			{
				$dquery="DELETE FROM {$dbprefix}surveys_rights WHERE uid={$_POST['uid']} AND sid={$surveyid}";	//	added by Dennis
				$dresult=$connect->Execute($dquery);

				$addsummary .= "<br />".$clang->gT("Username").": {$_POST['user']}<br />\n";
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
	$query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID']." AND owner_id != ".$_POST['uid'];
	$result = db_execute_assoc($query);
	if($result->RecordCount() > 0)
	{
		$query2 = "SELECT uid, edit_survey_property, define_questions, browse_response, export, delete_survey, activate_survey FROM ".db_table_name('surveys_rights')." WHERE sid = {$surveyid} AND uid = ".$_POST['uid'];
		$result2 = db_execute_assoc($query2);

		if($result2->RecordCount() > 0)
		{
			$resul2row = $result2->FetchRow();

			$usersummary = "<table width='100%' border='0'>\n\t<tr><td colspan='6' class='header'>\n"
			. "\t\t".$clang->gT("Set Survey Rights")."</td></tr>\n";

			$usersummary .= "\t\t<th align='center'>".$clang->gT("Edit Survey Properties")."</th>\n"
			. "\t\t<th align='center'>".$clang->gT("Define Questions")."</th>\n"
			. "\t\t<th align='center'>".$clang->gT("Browse Responses")."</th>\n"
			. "\t\t<th align='center'>".$clang->gT("Export")."</th>\n"
			. "\t\t<th align='center'>".$clang->gT("Delete Survey")."</th>\n"
			. "\t\t<th align='center'>".$clang->gT("Activate Survey")."</th>\n"
			. "\t\t</tr>\n"
			. "<form action='$scriptname?sid={$surveyid}' method='post'>\n";

			//content
			$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"edit_survey_property\" value=\"edit_survey_property\"";
			if($resul2row['edit_survey_property']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"define_questions\" value=\"define_questions\"";
			if($resul2row['define_questions']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"browse_response\" value=\"browse_response\"";
			if($resul2row['browse_response']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"export\" value=\"export\"";
			if($resul2row['export']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"delete_survey\" value=\"delete_survey\"";
			if($resul2row['delete_survey']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"activate_survey\" value=\"activate_survey\"";
			if($resul2row['activate_survey']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";

			$usersummary .= "\t\n\t<tr><td colspan='6' align='center'>"
			."<input type='submit' value='".$clang->gT("Save Now")."' />"
			."<input type='hidden' name='action' value='surveyrights' />"
			."<input type='hidden' name='uid' value='{$_POST['uid']}' /></td></tr>"
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
	$query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID'];//." AND owner_id != ".$_POST['uid'];
	$result = db_execute_assoc($query);
	if($result->RecordCount() > 0)
	{
		$usersummary = "<table width='100%' border='0'>\n\t<tr><td colspan='6' class='header'>\n"
		. "\t\t".$clang->gT("Set Survey Rights")."</td></tr>\n";

		$usersummary .= "\t\t<th align='center'>".$clang->gT("Edit Survey Property")."</th>\n"
		. "\t\t<th align='center'>".$clang->gT("Define Questions")."</th>\n"
		. "\t\t<th align='center'>".$clang->gT("Browse Response")."</th>\n"
		. "\t\t<th align='center'>".$clang->gT("Export")."</th>\n"
		. "\t\t<th align='center'>".$clang->gT("Delete Survey")."</th>\n"
		. "\t\t<th align='center'>".$clang->gT("Activate Survey")."</th>\n"
		. "\t\t</tr>\n"
		. "<form action='$scriptname?sid={$surveyid}' method='post'>\n";

		//content
		$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"edit_survey_property\" value=\"edit_survey_property\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"define_questions\" value=\"define_questions\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"browse_response\" value=\"browse_response\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"export\" value=\"export\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"delete_survey\" value=\"delete_survey\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "\t\t<td align='center'><input type=\"checkbox\"  class=\"checkboxbtn\" name=\"activate_survey\" value=\"activate_survey\"";

		$usersummary .=" /></td>\n";

		$usersummary .= "\t\n\t<tr><td colspan='6' align='center'>"
		."<input type='submit' value='".$clang->gT("Save Now")."' />"
		."<input type='hidden' name='action' value='surveyrights' />"
		."<input type='hidden' name='ugid' value='{$_POST['ugid']}' /></td></tr>"
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
    if($sumrows5['export'])
    {
    $exportstructure = "<form name='exportstructure' action='$scriptname' method='post'>\n" 
    ."<table width='100%' border='0' >\n\t<tr><td class='settingcaption'>"
    .$clang->gT("Export Survey Structure")."\n</td></tr>\n"
    ."\t<tr>\n"
    ."\t\t<td style='text-align:left; padding-left:40%;padding-right:30%;'>\n"
    ."\t\t\t<br /><input type='radio' class='radiobtn' name='type' value='structurecsv' checked='checked' id='surveycsv' onclick=\"this.form.action.value='exportstructurecsv'\";/>"
    ."<label for='surveycsv'>"
    .$clang->gT("LimeSurvey Survey File (*.csv)")."</label><br />\n"
    ."\t\t\t<input type='radio' class='radiobtn' name='type' value='structurequeXML'  id='queXML' onclick=\"this.form.action.value='exportstructurequexml'\";/>"
    ."<label for='queXML'>"
    .$clang->gT("queXML Survey XML Format (*.xml)")."</label>\n"
    ."\t\t<br />&nbsp;</td>\n"
    ."\t</tr>\n"
    ."\t<tr><td height='2' bgcolor='silver'></td></tr>\n"
    ."\t<tr>\n"
    ."\t\t<td align='center'>\n"
    ."\t\t\t<input type='submit' value='"
    .$clang->gT("Export To File")."' />\n"
    ."\t\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
    ."\t\t\t<input type='hidden' name='action' value='exportstructurecsv' />\n"
    ."\t\t</td>\n"
    ."\t</tr>\n"
    ."\t</table><br /></from>\n";
    }
}


if($action == "surveysecurity")
{
	$query = "SELECT sid FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID'];
	$result = db_execute_assoc($query);
	if($result->RecordCount() > 0)
	{
		$query2 = "SELECT a.uid, b.users_name FROM ".db_table_name('surveys_rights')." AS a INNER JOIN ".db_table_name('users')." AS b ON a.uid = b.uid WHERE a.sid = {$surveyid} AND b.uid != ".$_SESSION['loginID'] ." ORDER BY b.users_name";
		$result2 = db_execute_assoc($query2);
		$surveysecurity = "<table width='100%' rules='rows' border='1' class='table2columns'>\n\t<tr><td colspan='3' align='center' class='settingcaption'>\n"
		. "\t\t<strong>".$clang->gT("Survey Security")."</strong></td></tr>\n"
		. "\t<tr>\n"
		. "\t\t<th>".$clang->gT("Username")."</th>\n"
		. "\t\t<th>".$clang->gT("User Group")."</th>\n"
		. "\t\t<th>".$clang->gT("Action")."</th>\n"
		. "\t</tr>\n";
		if($result2->RecordCount() > 0)
		{
			//	output users
			$row = 0;
			while ($resul2row = $result2->FetchRow())
			{
				$query3 = "SELECT a.ugid FROM ".db_table_name('user_in_groups')." AS a RIGHT OUTER JOIN ".db_table_name('users')." AS b ON a.uid = b.uid WHERE b.uid = ".$resul2row['uid'];
				$result3 = db_execute_assoc($query3);
				while ($resul3row = $result3->FetchRow())
				{
					$group_ids[] = $resul3row['ugid'];
				}
				
				if($group_ids[0] != NULL)
				{				
					if(!isset($group_ids)) break;	// TODO
					$group_ids_query = implode(" OR ugid=", $group_ids);
					unset($group_ids);
	
					$query4 = "SELECT name FROM ".db_table_name('user_groups')." WHERE ugid = ".$group_ids_query;
					$result4 = db_execute_assoc($query4);
					
					while ($resul4row = $result4->FetchRow())
					{
						$group_names[] = $resul4row['name'];
					}
					if(count($group_names) > 0)
					$group_names_query = implode(", ", $group_names);
				}
				if(($row % 2) == 0)
					$surveysecurity .= "\t<tr>\n";
				else
					$surveysecurity .= "\t<tr>\n";

				$surveysecurity .= "\t<td align='center'>{$resul2row['users_name']}\n"
								 . "\t<td align='center'>";
					
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
				. "\t\t<td align='center' style='padding-top:10px;'>\n";

				$surveysecurity .= "<form method='post' action='$scriptname?sid={$surveyid}'>"
				."<input type='submit' value='".$clang->gT("Delete")."' onclick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry.","js")."\")' />"
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

				$surveysecurity .= "\t\t</td>\n"
				. "\t</tr>\n";
				$row++;
			}
		}
		$surveysecurity .= "\t\t<form action='$scriptname?sid={$surveyid}' method='post'>\n"
		. "\t\t<tr>\n"

		. "\t\t\t\t\t<td colspan='2' align='right'>"
		. "\t\t\t\t\t<strong>".$clang->gT("User").": </strong><select name='uid'>\n"
		//. $surveyuserselect
		. getsurveyuserlist()
		. "\t\t\t\t\t</select>\n"
		. "\t\t\t\t</td>\n"

		. "\t\t<td align='center'><input type='submit' value='".$clang->gT("Add User")."' />"
		. "<input type='hidden' name='action' value='addsurveysecurity' /></td></form>\n"
		. "\t</tr>\n";
		//. "\t</table>\n";

		$surveysecurity .= "\t\t<form action='$scriptname?sid={$surveyid}' method='post'>\n"
		. "\t\t<tr>\n"

		. "\t\t\t\t\t<td colspan='2' align='right'>"
		. "\t\t\t\t\t<strong>".$clang->gT("Groups").": </strong><select name='ugid'>\n"
		//. $surveyuserselect
		. getsurveyusergrouplist()
		. "\t\t\t\t\t</select>\n"
		. "\t\t\t\t</td>\n"

		. "\t\t<td align='center'><input type='submit' value='".$clang->gT("Add User Group")."' />"
		. "<input type='hidden' name='action' value='addusergroupsurveysecurity' /></td></form>\n"
		. "\t</tr>\n"
		. "\t</table>\n";
	}
	else
	{
		include("access_denied.php");
	}
}

elseif ($action == "surveyrights")
{
	$addsummary = "<br /><strong>".$clang->gT("Set Survey Rights")."</strong><br />\n";

	if(isset($_POST['uid'])){
		$query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID']." AND owner_id != ".$_POST['uid'];
	}
	else{
		$query = "SELECT sid, owner_id FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID'];//." AND owner_id != ".$_POST['uid'];
	}
	$result = db_execute_assoc($query);
	if($result->RecordCount() > 0)
	{
		$rights = array();

		if(isset($_POST['edit_survey_property']))$rights['edit_survey_property']=1;	else $rights['edit_survey_property']=0;
		if(isset($_POST['define_questions']))$rights['define_questions']=1;			else $rights['define_questions']=0;
		if(isset($_POST['browse_response']))$rights['browse_response']=1;			else $rights['browse_response']=0;
		if(isset($_POST['export']))$rights['export']=1;								else $rights['export']=0;
		if(isset($_POST['delete_survey']))$rights['delete_survey']=1;				else $rights['delete_survey']=0;
		if(isset($_POST['activate_survey']))$rights['activate_survey']=1;			else $rights['activate_survey']=0;

		if(isset($_POST['uid'])){
			$uids[] = $_POST['uid'];
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
	if($sumrows5['edit_survey_property'])
	{
		$esquery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid";
		$esresult = db_execute_assoc($esquery);
		while ($esrow = $esresult->FetchRow())
		{
			$esrow = array_map('htmlspecialchars', $esrow);
			$editsurvey = include2var('./scripts/addremove.js');
			$editsurvey .= "<form name='addnewsurvey' action='$scriptname' method='post'>\n<table width='100%' border='0'>\n\t<tr><td colspan='4' class='settingcaption'>"
			. "\t\t".$clang->gT("Edit Survey - Step 1 of 2")."</td></tr>\n"
			. "\t<tr><td align='right'>".$clang->gT("Administrator:")."</td>\n"
			. "\t\t<td align='left'><input type='text' size='50' name='admin' value=\"{$esrow['admin']}\" /></td></tr>\n"
			. "\t<tr><td align='right'>".$clang->gT("Admin Email:")."</td>\n"
			. "\t\t<td align='left'><input type='text' size='50' name='adminemail' value=\"{$esrow['adminemail']}\" /></td></tr>\n"
			. "\t<tr><td align='right'>".$clang->gT("Fax To:")."</td>\n"
			. "\t\t<td align='left'><input type='text' size='50' name='faxto' value=\"{$esrow['faxto']}\" /></td></tr>\n";
			$editsurvey .= "\t<tr><td align='right'>".$clang->gT("Format:")."</td>\n"
			. "\t\t<td align='left'><select name='format'>\n"
			. "\t\t\t<option value='S'";
			if ($esrow['format'] == "S" || !$esrow['format']) {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Question by Question")."</option>\n"
			. "\t\t\t<option value='G'";
			if ($esrow['format'] == "G") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Group by Group")."</option>\n"
			. "\t\t\t<option value='A'";
			if ($esrow['format'] == "A") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("All in one")."</option>\n"
			. "\t\t</select></td>\n"
			. "\t</tr>\n";
			//TEMPLATES
			$editsurvey .= "\t<tr><td align='right'>".$clang->gT("Template:")."</td>\n"
			. "\t\t<td align='left'><select name='template'>\n";
			foreach (gettemplatelist() as $tname)
			{
				
				 if ($_SESSION["loginID"] == 1 || $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] == 1 || hasTemplateManageRights($_SESSION["loginID"], $tname) == 1 )
				 {
                	$editsurvey .= "\t\t\t<option value='$tname'";
                    if ($esrow['template'] && htmlspecialchars($tname) == $esrow['template']) {$editsurvey .= " selected='selected'";}
                    elseif (!$esrow['template'] && $tname == "default") {$editsurvey .= " selected='selected'";}
                    $editsurvey .= ">$tname</option>\n";
                }

			}
			$editsurvey .= "\t\t</select></td>\n"
			. "\t</tr>\n";
			//COOKIES
			$editsurvey .= "\t<tr><td align='right'>".$clang->gT("Use Cookies?")."</td>\n"
			. "\t\t<td align='left'><select name='usecookie'>\n"
			. "\t\t\t<option value='Y'";
			if ($esrow['usecookie'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "\t\t\t<option value='N'";
			if ($esrow['usecookie'] != "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "\t\t</select></td>\n"
			. "\t</tr>\n";
			//ALLOW SAVES
			$editsurvey .= "\t<tr><td align='right'>".$clang->gT("Allow Saves?")."</td>\n"
			. "\t\t<td align='left'><select name='allowsave'>\n"
			. "\t\t\t<option value='Y'";
			if (!$esrow['allowsave'] || $esrow['allowsave'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "\t\t<option value='N'";
			if ($esrow['allowsave'] == "N") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "\t\t</select></td>\n"
			. "\t</tr>\n";
			//ALLOW PREV
			$editsurvey .= "\t<tr><td align='right'>".$clang->gT("Show [<< Prev] button")."</td>\n"
			. "\t\t<td align='left'><select name='allowprev'>\n"
			. "\t\t\t<option value='Y'";
			if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "\t\t<option value='N'";
			if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "\t\t</select></td>\n"
			. "\t</tr>\n";
            //Result printing
            $editsurvey .= "\t<tr><td align='right'>".$clang->gT("Participiants may print answers?")."</td>\n"
            . "\t\t<td><select name='printanswers'>\n"
            . "\t\t\t<option value='Y'";
            if (!isset($esrow['printanswers']) || !$esrow['printanswers'] || $esrow['printanswers'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "\t\t<option value='N'";
            if (isset($esrow['printanswers']) && $esrow['printanswers'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "\t\t</select></td>\n"
            . "\t</tr>\n";
            //Public Surveys
            $editsurvey .= "\t<tr><td align='right'>".$clang->gT("List survey publicly:")."</td>\n"
            . "\t\t<td><select name='public'>\n"
            . "\t\t\t<option value='Y'";
            if (!isset($esrow['listpublic']) || !$esrow['listpublic'] || $esrow['listpublic'] == "Y") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("Yes")."</option>\n"
            . "\t\t<option value='N'";
            if (isset($esrow['listpublic']) && $esrow['listpublic'] == "N") {$editsurvey .= " selected='selected'";}
            $editsurvey .= ">".$clang->gT("No")."</option>\n"
            . "\t\t</select></td>\n"
            . "\t</tr>\n";
			//NOTIFICATION
			$editsurvey .= "\t<tr><td align='right'>".$clang->gT("Admin Notification:")."</td>\n"
			. "\t\t<td align='left'><select name='notification'>\n"
			. getNotificationlist($esrow['notification'])
			. "\t\t</select></td>\n"
			. "\t</tr>\n";
			//ANONYMOUS
			$editsurvey .= "\t<tr><td align='right'>".$clang->gT("Anonymous answers?")."\n";
			  // warning message if anonymous + datestamped anwsers
			$editsurvey .= "\n"
			. "\t<script type=\"text/javascript\"><!-- \n"
			. "\tfunction alertPrivacy()\n"
			. "\t{"
			. "\t\tif (document.getElementById('private').value == 'Y')\n"
			. "\t\t{\n"
			. "\t\t\talert('".$clang->gT("Warning").": ".$clang->gT("If you turn on the -Anonymous answers- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.","js")."');\n"
			. "\t\t}\n"
			. "\t}"
			. "\t//--></script></td>\n";

			if ($esrow['active'] == "Y")
			{
				$editsurvey .= "\t\t<td align='left'>\n\t\t\t";
				if ($esrow['private'] == "N") {$editsurvey .= " ".$clang->gT("This survey is NOT anonymous.");}
				else {$editsurvey .= $clang->gT("This survey is anonymous.");}
				$editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
				. "\t\t</font>\n";
				$editsurvey .= "<input type='hidden' name='private' value=\"{$esrow['private']}\" /></td>\n";
			}
			else
			{
				$editsurvey .= "\t\t<td align='left'><select id='private' name='private' onchange='alertPrivacy();'>\n"
				. "\t\t\t<option value='Y'";
				if ($esrow['private'] == "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
				. "\t\t\t<option value='N'";
				if ($esrow['private'] != "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("No")."</option>\n"
				. "</select>\n\t\t</td>\n";
			}
			$editsurvey .= "</tr>\n";
			$editsurvey .= "\t<tr><td align='right'>".$clang->gT("Allow public registration?")."</td>\n"
			. "\t\t<td align='left'><select name='allowregister'>\n"
			. "\t\t\t<option value='Y'";
			if ($esrow['allowregister'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "\t\t\t<option value='N'";
			if ($esrow['allowregister'] != "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "\t\t</select></td>\n\t</tr>\n";
			$editsurvey .= "\t<tr><td align='right' valign='top'>".$clang->gT("Token Attribute Names:")."</td>\n"
			. "\t\t<td align='left'><input type='text' size='25' name='attribute1'"
			. " value=\"{$esrow['attribute1']}\" />(".$clang->gT("Attribute 1").")<br />"
			. "<input type='text' size='25' name='attribute2'"
			. " value=\"{$esrow['attribute2']}\" />(".$clang->gT("Attribute 2").")</td>\n\t</tr>\n";
			$editsurvey .= "\t<tr><td align='right'>".$clang->gT("Date Stamp?")."</td>\n";

			if ($esrow['active'] == "Y")
			{
				$editsurvey .= "\t\t<td align='left'>\n\t\t\t";
				if ($esrow['datestamp'] != "Y") {$editsurvey .= " ".$clang->gT("Responses will not be date stamped.");}
				else {$editsurvey .= $clang->gT("Responses will be date stamped.");}
				$editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
				. "\t\t</font>\n";
				$editsurvey .= "<input type='hidden' name='datestamp' value=\"{$esrow['datestamp']}\" /></td>\n";
			}
			else
			{
				$editsurvey .= "\t\t<td align='left'><select id='datestamp' name='datestamp' onchange='alertPrivacy();'>\n"
				. "\t\t\t<option value='Y'";
				if ($esrow['datestamp'] == "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
				. "\t\t\t<option value='N'";
				if ($esrow['datestamp'] != "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("No")."</option>\n"
				. "</select>\n\t\t</td>\n";
			}
			$editsurvey .= "</tr>\n";

			$editsurvey .= "\t<tr><td align='right'>".$clang->gT("Save IP Address?")."</td>\n";

			if ($esrow['active'] == "Y")
			{
				$editsurvey .= "\t\t<td align='left'>\n\t\t\t";
				if ($esrow['ipaddr'] != "Y") {$editsurvey .= " ".$clang->gT("Responses will not have the IP address logged.");}
				else {$editsurvey .= $clang->gT("Responses will have the IP address logged");}
				$editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
				. "\t\t</font>\n";
				$editsurvey .= "<input type='hidden' name='ipaddr' value='".$esrow['ipaddr']."' />\n</td>";
			}
			else
			{
				$editsurvey .= "\t\t<td align='left'><select name='ipaddr'>\n"
				. "\t\t\t<option value='Y'";
				if ($esrow['ipaddr'] == "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
				. "\t\t\t<option value='N'";
				if ($esrow['ipaddr'] != "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("No")."</option>\n"
				. "</select>\n\t\t</td>\n";
			}

			// begin REF URL Block
			$editsurvey .= "\t</tr><tr><td align='right'>".$clang->gT("Save Referring URL?")."</td>\n";

			if ($esrow['active'] == "Y")
			{
				$editsurvey .= "\t\t<td align='left'>\n\t\t\t";
				if ($esrow['refurl'] != "Y") {$editsurvey .= " ".$clang->gT("Responses will not have their referring URL logged.");}
				else {$editsurvey .= $clang->gT("Responses will have their referring URL logged.");}
				$editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
				. "\t\t</font>\n";
				$editsurvey .= "<input type='hidden' name='refurl' value='".$esrow['refurl']."' />\n</td>";
			}
			else
			{
				$editsurvey .= "\t\t<td align='left'><select name='refurl'>\n"
				. "\t\t\t<option value='Y'";
				if ($esrow['refurl'] == "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
				. "\t\t\t<option value='N'";
				if ($esrow['refurl'] != "Y") {$editsurvey .= " selected='selected'";}
				$editsurvey .= ">".$clang->gT("No")."</option>\n"
				. "</select>\n\t\t</td>\n";
			}
			// BENBUN - END REF URL Block

			// Base Language
			$editsurvey .= "\t</tr><tr><td align='right'>".$clang->gT("Base Language:")."</td>\n"
			. "\t\t<td align='left'>\n".GetLanguageNameFromCode($esrow['language'])
			. "\t\t</td>\t</tr>\n"

			// Additional languages listbox
			. "\t<tr><td align='right'>".$clang->gT("Additional Languages").":</td>\n"
			. "\t\t<td align='left'><select multiple='multiple' style='min-width:250px;'  size='5' id='additional_languages' name='additional_languages'>";
			$jsX=0;
			$jsRemLang ="<script type=\"text/javascript\">\nvar mylangs = new Array() \n";

			foreach (GetAdditionalLanguagesFromSurveyID($surveyid) as $langname)
			{
				if ($langname && $langname!=$esrow['language']) // base languag must not be shown here
				{
					$jsRemLang .="mylangs[$jsX] = \"$langname\"\n";
					$editsurvey .= "\t\t\t<option id='".$langname."' value='".$langname."'";
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
			. "\t\t<td align='left'><select size='5' id='available_languages' name='available_languages'>";
			$tempLang=GetAdditionalLanguagesFromSurveyID($surveyid);
			foreach (getLanguageData() as  $langkey2=>$langname)
			{
				if ($langkey2!=$esrow['language'] && in_array($langkey2,$tempLang)==false)  // base languag must not be shown here
				{
					$editsurvey .= "\t\t\t<option id='".$langkey2."' value='".$langkey2."'";
					$editsurvey .= ">".$langname['description']." - ".$langname['nativedescription']."</option>\n";
				}
			}
			$editsurvey .= "</select></td>"
			. " </tr>\n"
			. "\t<tr><td align='right'>".$clang->gT("Expires?")."</td>\n"
			. "\t\t\t<td align='left'><select name='useexpiry'><option value='Y'";
			if (isset($esrow['useexpiry']) && $esrow['useexpiry'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "\t\t\t<option value='N'";
			if (!isset($esrow['useexpiry']) || $esrow['useexpiry'] != "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option></select></td></tr><tr><td align='right'>".$clang->gT("Expiry Date:")."</td>\n"
			. "\t\t<td align='left'><input type='text' id='f_date_b' size='12' name='expires' value=\"{$esrow['expires']}\" /><button type='reset' id='f_trigger_b'>...</button></td></tr>\n"
			. "\t<tr><td align='right'>".$clang->gT("End URL:")."</td>\n"
			. "\t\t<td align='left'><input type='text' size='50' name='url' value=\"{$esrow['url']}\" /></td></tr>\n"
			. "\t<tr><td align='right'>".$clang->gT("Automatically load URL when survey complete?")."</td>\n"
			. "\t\t<td align='left'><select name='autoredirect'>";
			$editsurvey .= "\t\t\t<option value='Y'";
			if (isset($esrow['autoredirect']) && $esrow['autoredirect'] == "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n";
			$editsurvey .= "\t\t\t<option value='N'";
			if (!isset($esrow['autoredirect']) || $esrow['autoredirect'] != "Y") {$editsurvey .= " selected='selected'";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "</select></td></tr>";

			$editsurvey .= "\t<tr><td colspan='4' align='center'><input type='submit' onclick='return UpdateLanguageIDs(mylangs,\"".$clang->gT("All questions, answers, etc for removed languages will be lost. Are you sure?")."\");' class='standardbtn' value='".$clang->gT("Save and Continue")." >>' />\n"
			. "\t<input type='hidden' name='action' value='updatesurvey' />\n"
			. "\t<input type='hidden' name='sid' value=\"{$esrow['sid']}\" />\n"
			. "\t<input type='hidden' name='languageids' id='languageids' value=\"{$esrow['additional_languages']}\" />\n"
			. "\t<input type='hidden' name='language' value=\"{$esrow['language']}\" />\n"
			. "\t</td></tr>\n"
			. "</table></form>\n";

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
	if($sumrows5['edit_survey_property'])
	{
	
    	$grplangs = GetAdditionalLanguagesFromSurveyID($surveyid);
		$baselang = GetBaseLanguageFromSurveyID($surveyid);
		array_unshift($grplangs,$baselang);

		$editsurvey = PrepareEditorScript();
		
	
		$editsurvey .="<script type='text/javascript'>\n"
		. "<!--\n"
		. "function fillin(tofield, fromfield)\n"
		. "\t{\n"
		. "\t\tif (confirm(\"".$clang->gT("This will replace the existing text. Continue?","js")."\")) {\n"
		. "\t\t\tdocument.getElementById(tofield).value = document.getElementById(fromfield).value\n"
		. "\t\t}\n"
		. "\t}\n"
		. "--></script>\n"
        . "<table width='100%' border='0'>\n\t<tr><td class='settingcaption'>"
		. "\t\t".$clang->gT("Edit Survey - Step 2 of 2")."</td></tr></table>\n"
		. '<div class="tab-pane" id="tab-pane-1">';
		foreach ($grplangs as $grouplang)
		{
            // this one is created to get the right default texts fo each language
            $bplang = new limesurvey_lang($grouplang);		
    		$esquery = "SELECT * FROM ".db_table_name("surveys_languagesettings")." WHERE surveyls_survey_id=$surveyid and surveyls_language='$grouplang'";
    		$esresult = db_execute_assoc($esquery);
    		$esrow = $esresult->FetchRow();
			$editsurvey .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($esrow['surveyls_language'],false);
			if ($esrow['surveyls_language']==GetBaseLanguageFromSurveyID($surveyid)) {$editsurvey .= '('.$clang->gT("Base Language").')';}
			$editsurvey .= '</h2>';
			$esrow = array_map('htmlspecialchars', $esrow);
			$editsurvey .= "<form name='addnewsurvey' action='$scriptname' method='post'>\n";

			$editsurvey .= "\t\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Title").":</span>\n"
			. "\t\t<span class='settingentry'><input type='text' size='80' name='short_title_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_title']}\" /></span>\n"
			. "\t</div><div class='settingrow'><span class='settingcaption'>".$clang->gT("Description:")."</span>\n"
			. "\t\t<span class='settingentry'><textarea cols='80' rows='15' name='description_".$esrow['surveyls_language']."'>{$esrow['surveyls_description']}</textarea>\n"
			. getEditor("survey-desc","description_".$esrow['surveyls_language'], "[".$clang->gT("Description:", "js")."](".$esrow['surveyls_language'].")",'','','',$action)
			. "</span>\n"
			. "\t</div><div class='settingrow'><span class='settingcaption'>".$clang->gT("Welcome:")."</span>\n"
			. "\t\t<span class='settingentry'><textarea cols='80' rows='15' name='welcome_".$esrow['surveyls_language']."'>{$esrow['surveyls_welcometext']}</textarea>\n"
			. getEditor("survey-welc","welcome_".$esrow['surveyls_language'], "[".$clang->gT("Welcome:", "js")."](".$esrow['surveyls_language'].")",'','','',$action)
			. "</span></div>\n"
			. "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("URL Description:")."</span>\n"
			. "\t\t<span class='settingentry'><input type='text' size='80' name='urldescrip_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_urldescription']}\" />\n"
			. "\t</span></div></div>";
		}
		$editsurvey .= '</div>';
		$editsurvey .= "\t<p><input type='submit' class='standardbtn' value='".$clang->gT("Save")."' />\n"
		. "\t<input type='hidden' name='action' value='updatesurvey2' />\n"
		. "\t<input type='hidden' name='sid' value=\"{$surveyid}\" />\n"
		. "\t<input type='hidden' name='language' value=\"{$esrow['surveyls_language']}\" />\n"
		. "\t</p>\n"
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
	if($sumrows5['edit_survey_property'])
	{
	// Check if one of the up/down buttons have been clicked
	if (isset($_POST['groupordermethod']))
	{
	   switch($_POST['groupordermethod'])
	   {
        // Pressing the Up button
		case $clang->gT("Up", "unescaped"):
		$newsortorder=$_POST['sortorder']-1;
		$oldsortorder=$_POST['sortorder'];
		$cdquery = "UPDATE ".db_table_name('groups')." SET group_order=-1 WHERE sid=$surveyid AND group_order=$newsortorder";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE ".db_table_name('groups')." SET group_order=$newsortorder WHERE sid=$surveyid AND group_order=$oldsortorder";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE ".db_table_name('groups')." SET group_order='$oldsortorder' WHERE sid=$surveyid AND group_order=-1";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		break;

        // Pressing the Down button
		case $clang->gT("Dn", "unescaped"):
		$newsortorder=$_POST['sortorder']+1;
		$oldsortorder=$_POST['sortorder'];
		$cdquery = "UPDATE ".db_table_name('groups')." SET group_order=-1 WHERE sid=$surveyid AND group_order=$newsortorder";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE ".db_table_name('groups')." SET group_order='$newsortorder' WHERE sid=$surveyid AND group_order=$oldsortorder";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		$cdquery = "UPDATE ".db_table_name('groups')." SET group_order=$oldsortorder WHERE sid=$surveyid AND group_order=-1";
		$cdresult=$connect->Execute($cdquery) or die($connect->ErrorMsg());
		break;
        }
    }

        $ordergroups = "<table width='100%' border='0'>\n\t<tr ><td colspan='2' class='settingcaption'>"
		. "\t\t".$clang->gT("Change Group Order")."</td></tr>"
		. "</table>\n";

	// Get groups dependencies regarding conditions
	// => Get an array of groups containing questions with conditions outside the group
	// $groupdepsarray[dependent-gid][target-gid]['conditions'][qid-having-conditions]=Array(cids...)
	$groupdepsarray = GetGroupDepsForConditions($surveyid);
	if (!is_null($groupdepsarray))
	{
		$ordergroups .= "<li class='movableNode'><strong><font color='orange'>".$clang->gT("Warning").":</font> ".$clang->gT("Current survey has questions with conditions outside their own group")."</strong><br /><br /><i>".$clang->gT("Re-ordering groups is restricted to ensure that questions on which conditions are based aren't reordered after questions having the conditions set")."</i></strong><br /><br/>".$clang->gT("The following groups are concerned").":<ul>\n";
		foreach ($groupdepsarray as $depgid => $depgrouprow)
		{
			foreach($depgrouprow as $targgid => $targrow)
			{
				$ordergroups .= "<li>".$clang->gT("Group")." <a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$depgid."')\">".$targrow['depgpname']."</a> ".$clang->gT("depends on group")." <a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$targgid."')\">".$targrow['targetgpname']."</a> ".$clang->gT("see the marked conditions on").":";
				foreach($targrow['conditions'] as $depqid => $depqrow)
				{
					$listcid=implode("-",$depqrow);
					$ordergroups .= " <a href='#' onclick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$depgid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."')\"> [QID: ".$depqid."]</a>";
				}
				$ordergroups .= "</li>\n";
			}
		}
		$ordergroups .= "</ul></li>";
	}

	$ordergroups .= "<form method='post'>";
		//Get the groups from this survey
		$s_lang = GetBaseLanguageFromSurveyID($surveyid);
		$ogquery = "SELECT * FROM {$dbprefix}groups WHERE sid='{$surveyid}' AND language='{$s_lang}' order by group_order,group_name" ;
		$ogresult = db_execute_assoc($ogquery) or die($connect->ErrorMsg());

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
	
			$ordergroups.="<li class='movableNode' id='".$ogarray[$i]['gid']."'>\n" ;
			$ordergroups.= "\t<input style='float:right;";
	
	                if ($i == 0){$ordergroups.="visibility:hidden;";}
	                $ordergroups.="' type='submit' name='groupordermethod' value='".$clang->gT("Up")."' onclick=\"this.form.sortorder.value='{$ogarray[$i]['group_order']}'\" ".$updisabled."/>\n";
	
	   		if ($i < $groupcount-1)
	    			{
	    				// Fill the sortorder hiddenfield so we now what field is moved down
					$ordergroups.= "\t<input type='submit' style='float:right;' name='groupordermethod' value='".$clang->gT("Dn")."' onclick=\"this.form.sortorder.value='{$ogarray[$i]['group_order']}'\" ".$downdisabled."/>\n";
	    			}
				$ordergroups.=$ogarray[$i]['group_name']."</li>\n" ;
	
		}

		$ordergroups.="</ul>\n"
		. "\t<input type='hidden' name='sortorder' />"
		. "\t<input type='hidden' name='action' value='ordergroups' />" 
        . "</form>" ;
		$ordergroups .="<br />" ;
	}
	else
	{
		include("access_denied.php");
	}
}
if ($action == "uploadf")
{
	if (!isset($tempdir))
	{
		$the_path = $homedir;
	}
	else
	{
		$the_path = $tempdir;
	}
	$the_file_name = $_FILES['the_file']['name'];
	$the_file = $_FILES['the_file']['tmp_name'];
	$the_full_file_path = $the_path."/".$the_file_name;
	switch($_FILES['the_file']['error'])
	{
		case UPLOAD_ERR_INI_SIZE:
		upload();
		$editcsv .="<b><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("The uploaded file is bigger than the upload_max_filesize directive in php.ini")."</b>\n";
		break;
		case UPLOAD_ERR_PARTIAL:
		upload();
		$editcsv .="<b><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("The file was only partially uploaded")."</b>\n";
		break;
		case UPLOAD_ERR_NO_FILE:
		upload();
		$editcsv .="<b><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("No file was uploaded")."</b>\n";
		break;
		case UPLOAD_ERR_OK:
		control();
		break;
		default:
		$editcsv .="<b><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("Error on file transfer. You must select a CSV file")."</b>\n";
	}
}



if ($action == "newsurvey")
{
	if($_SESSION['USER_RIGHT_CREATE_SURVEY'])
	{
		$newsurvey = PrepareEditorScript();
		$newsurvey  .= "<form name='addnewsurvey' action='$scriptname' method='post' onsubmit=\"return isEmpty(document.getElementById('surveyls_title'), '".$clang->gT("Error: You have to enter a title for this survey.",'js')."');\" >\n"
        . "<table width='100%' border='0' class='form2columns'>\n\t<thead><tr><th colspan='2'>\n"
		. "\t\t".$clang->gT("Create Survey")."</th></tr></thead>\n"
		. "\t<tr>\n"
		. "\t\t<td width='25%'>".$clang->gT("Title").":</td>\n"
		. "\t\t<td><input type='text' size='82' maxlength='200' id='surveyls_title' name='surveyls_title' /><font size='1'> ".$clang->gT("(This field is mandatory.)")."</font></td></tr>\n"
		. "\t<tr><td>".$clang->gT("Description:")."</td>\n"
		. "\t\t<td><textarea cols='80' rows='10' name='description'></textarea>"
		. getEditor("survey-desc","description", "[".$clang->gT("Description:", "js")."]",'','','',$action)
		. "</td></tr>\n"
		. "\t<tr><td>".$clang->gT("Welcome:")."</td>\n"
		. "\t\t<td><textarea cols='80' rows='10' name='welcome'></textarea>"
		. getEditor("survey-welc","welcome", "[".$clang->gT("Welcome:", "js")."]",'','','',$action)
		. "</td></tr>\n"
		. "\t<tr><td>".$clang->gT("Administrator:")."</td>\n"
		. "\t\t<td><input type='text' size='50' name='admin' /></td></tr>\n"
		. "\t<tr><td>".$clang->gT("Admin Email:")."</td>\n"
		. "\t\t<td><input type='text' size='50' name='adminemail' /></td></tr>\n";
		$newsurvey .= "\t<tr><td>".$clang->gT("Fax To:")."</td>\n"
		. "\t\t<td><input type='text' size='50' name='faxto' /></td></tr>\n";
		$newsurvey .= "\t<tr><td>".$clang->gT("Format:")."</td>\n"
		. "\t\t<td><select name='format'>\n"
		. "\t\t\t<option value='S' selected='selected'>".$clang->gT("Question by Question")."</option>\n"
		. "\t\t\t<option value='G'>".$clang->gT("Group by Group")."</option>\n"
		. "\t\t\t<option value='A'>".$clang->gT("All in one")."</option>\n"
		. "\t\t</select></td>\n"
		. "\t</tr>\n";
		$newsurvey .= "\t<tr><td>".$clang->gT("Template:")."</td>\n"
		. "\t\t<td><select name='template'>\n";
		foreach (gettemplatelist() as $tname)
		{
			
			if ($_SESSION["loginID"] == 1 || $_SESSION['USER_RIGHT_MANAGE_TEMPLATE'] == 1 || hasTemplateManageRights($_SESSION["loginID"], $tname) == 1 )  {
				$newsurvey .= "\t\t\t<option value='$tname'";
				if (isset($esrow) && $esrow['template'] && $tname == $esrow['template']) {$newsurvey .= " selected='selected'";}
				elseif ((!isset($esrow) || !$esrow['template']) && $tname == "default") {$newsurvey .= " selected='selected'";}
				$newsurvey .= ">$tname</option>\n";
			}
			
		}
		$newsurvey .= "\t\t</select></td>\n"
		. "\t</tr>\n";
		//COOKIES
		$newsurvey .= "\t<tr><td>".$clang->gT("Use Cookies?")."</td>\n"
		. "\t\t<td><select name='usecookie'>\n"
		. "\t\t\t<option value='Y'";
		if (isset($esrow) && $esrow['usecookie'] == "Y") {$newsurvey .= " selected='selected'";}
		$newsurvey .= ">".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N' selected='selected'";
		$newsurvey .= ">".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n"
		. "\t</tr>\n";
		//ALLOW SAVES
		$newsurvey .= "\t<tr><td>".$clang->gT("Allow Saves?")."</td>\n"
		. "\t\t<td><select name='allowsave'>\n"
		. "\t\t\t<option value='Y'";
		if (!isset($esrow['allowsave']) || !$esrow['allowsave'] || $esrow['allowsave'] == "Y") {$newsurvey .= " selected='selected'";}
		$newsurvey .= ">".$clang->gT("Yes")."</option>\n"
		. "\t\t<option value='N'";
		if (isset($esrow['allowsave']) && $esrow['allowsave'] == "N") {$newsurvey .= " selected='selected'";}
		$newsurvey .= ">".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n"
		. "\t</tr>\n";
		//ALLOW PREV
		$newsurvey .= "\t<tr><td>".$clang->gT("Show [<< Prev] button")."</td>\n"
		. "\t\t<td><select name='allowprev'>\n"
		. "\t\t\t<option value='Y' selected='selected'>".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N'>".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n"
		. "\t</tr>\n";
        //Result printing
        $newsurvey .= "\t<tr><td>".$clang->gT("Participiants may print answers?")."</td>\n"
        . "\t\t<td><select name='printanswers'>\n"
        . "\t\t\t<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "\t\t\t<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "\t\t</select></td>\n"
        . "\t</tr>\n";
        //Public Surveys
        $newsurvey .= "\t<tr><td>".$clang->gT("List survey publicly:")."</td>\n"
        . "\t\t<td><select name='public'>\n"
        . "\t\t\t<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "\t\t\t<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "\t\t</select></td>\n"
        . "\t</tr>\n";
		//NOTIFICATIONS
		$newsurvey .= "\t<tr><td>".$clang->gT("Admin Notification:")."</td>\n"
		. "\t\t<td><select name='notification'>\n"
		. getNotificationlist(0)
		. "\t\t</select></td>\n"
		. "\t</tr>\n";
		// ANONYMOUS
		$newsurvey .= "\t<tr><td>".$clang->gT("Anonymous answers?")."\n";
		  // warning message if anonymous + datestamped anwsers
		$newsurvey .= "\n"
		. "\t<script type=\"text/javascript\"><!-- \n"
		. "\tfunction alertPrivacy()\n"
		. "\t{"
		. "\t\tif (document.getElementById('private').value == 'Y')\n"
		. "\t\t{\n"
		. "\t\t\talert('".$clang->gT("Warning").": ".$clang->gT("If you turn on the -Anonymous answers- option and create a tokens table, LimeSurvey will mark your completed tokens only with a 'Y' instead of date/time to ensure the anonymity of your participants.","js")."');\n"
		. "\t\t}\n"
		. "\t}"
		. "\t//--></script></td>\n";

		$newsurvey .= "\t\t<td><select id='private' name='private' onchange='alertPrivacy();'>\n"
		. "\t\t\t<option value='Y' selected='selected'>".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N'>".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n\t</tr>\n";
        $newsurvey .= "\t<tr><td>".$clang->gT("Allow public registration?")."</td>\n"
        . "\t\t<td align='left'><select name='allowregister'>\n"
        . "\t\t\t<option value='Y'>".$clang->gT("Yes")."</option>\n"
        . "\t\t\t<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
        . "\t\t</select></td>\n\t</tr>\n";
		$newsurvey .= "\t<tr><td valign='top'>".$clang->gT("Token Attribute Names:")."</td>\n"
		. "\t\t<td align='left'><input type='text' size='25' name='attribute1' />(".$clang->gT("Attribute 1").")<br />"
		. "<input type='text' size='25' name='attribute2' />(".$clang->gT("Attribute 2").")</td>\n\t</tr>\n";
		$newsurvey .= "\t<tr><td>".$clang->gT("Date Stamp?")."</td>\n"
		. "\t\t<td><select id='datestamp' name='datestamp' onchange='alertPrivacy();'>\n"
		. "\t\t\t<option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n\t</tr>\n";
		// IP Address
		$newsurvey .= "\t<tr><td>".$clang->gT("Save IP Address?")."</td>\n"
		. "\t\t<td><select name='ipaddr'>\n"                                
        . "\t\t\t<option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n\t</tr>\n";
		// Referring URL
		$newsurvey .= "\t<tr><td>".$clang->gT("Save Referring URL?")."</td>\n"
		. "\t\t<td><select name='refurl'>\n"                                
        . "\t\t\t<option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n\t</tr>\n";
		//Survey Language
		$newsurvey .= "\t<tr><td>".$clang->gT("Base Language:")."</td>\n"
		. "\t\t<td><select name='language'>\n";


		foreach (getLanguageData() as  $langkey2=>$langname)
		{
			$newsurvey .= "\t\t\t<option value='".$langkey2."'";
			if ($defaultlang == $langkey2) {$newsurvey .= " selected='selected'";}
			$newsurvey .= ">".$langname['description']." - ".$langname['nativedescription']."</option>\n";
		}

		$newsurvey .= "\t\t</select><font size='1'> ".$clang->gT("This setting cannot be changed later!")."</font></td>\n"
		. "\t</tr>\n";
		$newsurvey .= "\t<tr><td>".$clang->gT("Expires?")."</td>\n"
		. "\t\t\t<td align='left'><select name='useexpiry'><option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N' selected='selected'>".$clang->gT("No")."</option></select></td></tr>\n"
		. "<tr><td>".$clang->gT("Expiry Date:")."</td>\n"
		. "\t\t<td align='left'><input type='text' id='f_date_b' size='12' name='expires' value='"
		. date_shift(date("Y-m-d H:i:s"), "Y-m-d", $timeadjust)."' /><button type='reset' id='f_trigger_b'>...</button>"
		. "<font size='1'> ".$clang->gT("Date Format").": YYYY-MM-DD</font></td></tr>\n"
		. "\t<tr><td>".$clang->gT("End URL:")."</td>\n"
		. "\t\t<td align='left'><input type='text' size='50' name='url' value='http://";
		if (isset($esrow)) {$newsurvey .= $esrow['url'];}
		$newsurvey .= "' /></td></tr>\n"
		. "\t<tr><td>".$clang->gT("URL Description:")."</td>\n"
		. "\t\t<td align='left'><input type='text' maxlength='255' size='50' name='urldescrip' value='";
		if (isset($esrow)) {$newsurvey .= $esrow['surveyls_urldescription'];}
		$newsurvey .= "' /></td></tr>\n"
		. "\t<tr><td>".$clang->gT("Automatically load URL when survey complete?")."</td>\n"
		. "\t\t<td align='left'><select name='autoredirect'>\n"
		. "\t\t\t<option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N' selected='selected'>".$clang->gT("No")."</option>\n"
		. "</select></td></tr>"
		. "\t<tr><td colspan='2' class='centered'><input type='submit' value='".$clang->gT("Create Survey")."' />\n"
		. "\t<input type='hidden' name='action' value='insertnewsurvey' /></td>\n"
		. "\t</tr>\n"
		. "</table></form>\n";
		$newsurvey .= "<center>".$clang->gT("OR")."</center>\n";
		$newsurvey .= "<form enctype='multipart/form-data' name='importsurvey' action='$scriptname' method='post' onsubmit='return validatefilename(this,\"".$clang->gT('Please select a file to import!','js')."\");'>\n"
		. "<table width='100%' border='0' class='form2columns'>\n"
		. "<tr><th colspan='2'>\n"
		. "\t\t".$clang->gT("Import Survey")."</th></tr>\n\t<tr>"
		. "\t\t<td>".$clang->gT("Select CSV/SQL File:")."</td>\n"
		. "\t\t<td><input name=\"the_file\" type=\"file\" size=\"50\" /></td></tr>\n"
		. "\t<tr><td colspan='2' class='centered'><input type='submit' value='".$clang->gT("Import Survey")."' />\n"
		. "\t<input type='hidden' name='action' value='importsurvey' /></td>\n"
		. "\t</tr>\n</table></form>\n";
        
		// Here we do setup the date javascript
		$newsurvey .= "<script type=\"text/javascript\">\n"
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

function getPopupHeight() 
{
	global $clang, $surveyid;
	
	$rowheight = 20;
	$height = 0;
	$bottomPad = 15;
	
	// header text height
	$htext = ceil(strlen($clang->gT("Please select a language:")) / 17);
	$height += $rowheight * $htext;
		
	// language list height
	$survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
	$baselang = GetBaseLanguageFromSurveyID($surveyid);
	$survlangs[] = $baselang;
	
	foreach ($survlangs as $lang)
	{
		$ltext = ceil(strlen(getLanguageNameFromCode($lang,false)) / 17);
		$height += $rowheight * $ltext;
		if ($ltext > 1) $height += ($ltext * 3);
	}

	// footer height
	$ftext = ceil(count($clang->gT("Cancel")) / 17);
	$height += $rowheight * $ftext;
	
	$height += $bottomPad;
	
	return $height;
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
	$newquestionoutput = "<script type='text/javascript'>\n"
    ."if (navigator.userAgent.indexOf(\"Gecko\") != -1)\n"
	."window.addEventListener(\"load\", init_gecko_select_hack, false);\n";	
	$jc=0;
	$newquestionoutput .= "\t\t\tvar qtypes = new Array();\n";
	$newquestionoutput .= "\t\t\tvar qnames = new Array();\n\n";
	foreach ($qattributes as $key=>$val)
	{
		foreach ($val as $vl)
		{
			$newquestionoutput .= "\t\t\tqtypes[$jc]='".$key."';\n";
			$newquestionoutput .= "\t\t\tqnames[$jc]='".$vl['name']."';\n";
			$jc++;
		}
	}
	$newquestionoutput .= "\t\t\t function buildQTlist(type)
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
	$newquestionoutput .="\nfunction OtherSelection(QuestionType)\n"
	. "\t{\n"
	. "if (QuestionType == '') {QuestionType=document.getElementById('question_type').value;}\n"
	. "\tif (QuestionType == 'M' || QuestionType == 'P' || QuestionType == 'L' || QuestionType == '!')\n"
	. "\t\t{\n"
	. "\t\tdocument.getElementById('OtherSelection').style.display = '';\n"
	. "\t\tdocument.getElementById('LabelSets').style.display = 'none';\n"
	. "\t\tdocument.getElementById('Validation').style.display = 'none';\n"
	. "\t\t}\n"
	. "\telse if (QuestionType == 'F' || QuestionType == 'H' || QuestionType == 'W' || QuestionType == 'Z')\n"
	. "\t\t{\n"
	. "\t\tdocument.getElementById('LabelSets').style.display = '';\n"
	. "\t\tdocument.getElementById('OtherSelection').style.display = 'none';\n"
	. "\t\tdocument.getElementById('Validation').style.display = 'none';\n"
	. "\t\t}\n"
	. "\telse if (QuestionType == 'S' || QuestionType == 'T' || QuestionType == 'U' || QuestionType == 'N' || QuestionType == 'D' || QuestionType=='')\n"
	. "\t\t{\n"
	. "\t\tdocument.getElementById('Validation').style.display = '';\n"
	. "\t\tdocument.getElementById('OtherSelection').style.display ='none';\n"
	. "\t\tdocument.getElementById('ON').checked = true;\n"
	. "\t\tdocument.getElementById('LabelSets').style.display='none';\n"
	. "\t\t}\n"
	. "\telse\n"
	. "\t\t{\n"
	. "\t\tdocument.getElementById('LabelSets').style.display = 'none';\n"
	. "\t\tdocument.getElementById('OtherSelection').style.display = 'none';\n"
	. "\t\tdocument.getElementById('ON').checked = true;\n"
	. "\t\tdocument.getElementById('Validation').style.display = 'none';\n"
	//. "\t\tdocument.addnewquestion.other[1].checked = true;\n"
	. "\t\t}\n"
	. "\tbuildQTlist(QuestionType);\n"
	. "\t}\n"
	. "\tOtherSelection('$type');\n"
	. "</script>\n";
    
}      */

function upload()
{
	global $questionsummary, $sid, $qid, $gid;
	$questionsummary .= "\t\t<tr><td>&nbsp;</td><td>"
	. "<font face='verdana' size='1' color='green'>"
	. $clang->gT("Warning").": ". $clang->gT("You need to upload the file")." "
	. "\n<form enctype='multipart/form-data' action='" . $_SERVER['PHP_SELF'] . "' method='post'>\n"
	. "<input type='hidden' name='action' value='uploadf' />\n"
	. "<input type='hidden' name='sid' value='$sid' />\n"
	. "<input type='hidden' name='gid' value='$gid' />\n"
	. "<input type='hidden' name='qid' value='$qid' />\n"
	. "<font face='verdana' size='2' color='green'><b>"
	. $clang->gT("You must upload a CSV file")."</font><br />\n"
	. "<input type='file' name='the_file' size='35' /><br />\n"
	. "<input type='submit' value='".$clang->gT("Upload CSV file")."' />\n"
	. "</form></font>\n\n";
}

function control()
{
	global $editcsv, $questionsummary, $sid, $qid, $gid;
	$info = pathinfo($_FILES['the_file']['name']);
	$ext = $info['extension'] ;
	if ($ext != "csv")
	{
		upload();
		$editcsv .="<b><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("It is impossible to upload a file other than CSV type")."</b>\n";
		$questionsummary .= "</table>\n";
	}
	else
	{
		copy($_FILES['the_file']['tmp_name'],".\\".$_FILES['the_file']['name']);
		unlink($_FILES['the_file']['tmp_name']);
		$lines = file($_FILES['the_file']['name']);
		$result = count($lines);
		if ($result <= 1)
		{
			upload();
			$editcsv .="<b><font color='red'>".$clang->gT("Error").":</font> ".$clang->gT("It is impossible to upload an empty file")."</b>\n";
			$questionsummary .= "</table>\n";
		}
		else
		{
			$editcsv  = "<table width='100%' align='center' border='0'>\n"
			. "<tr bgcolor='#555555'><td colspan='2'><font color='white'><b>"
			. $clang->gT("Uploading CSV file")."</b></td></tr>\n";
			$editcsv .= "<tr><th>".$clang->gT("Visualization:")."</font></th><th>".$clang->gT("Select the field number you would like to use for your answers:")."</font></th>"
			. "</tr>\n";
			$ricorpv = substr_count($lines[0],";");
			$ricorv = substr_count($lines[0],",");
			if ($ricorpv > $ricorv)
			{
				$vettoreriga = explode(";",$lines[0]);
				$elem = ";";
			}
			else
			{
				$vettoreriga = explode(",",$lines[0]);
				$elem = ",";
			}
			$editcsv .= "<tr><form action='".$scriptname."' method='post'>\n"
			. "<td align = 'center'><select name=\"$K\">\n";
			$band = 0;
			foreach ($lines as $K => $v)
			{
				if ($band == 1)
				{
					$editcsv .= "<option value=$lines[$K]>$lines[$K]</option>\n";
				}
				$band = 1;
			}
			$svettore = implode("^", $lines);
			$editcsv .= "</select></td>\n";
			$svettore = htmlspecialchars($svettore, ENT_QUOTES);
			$editcsv.="<input type='hidden' name='sid' value='$sid' />\n"
			. "\t<input type='hidden' name='gid' value='$gid' />\n"
			. "\t<input type='hidden' name='qid' value='$qid' />\n"
			. "\t<input type='hidden' name='elem' value='$elem' />\n"
			. "\t<input type='hidden' name='svettore' value='".$svettore."' />\n";

			$editcsv.="\t\t\t<td align = 'center'><select name='numcol'>\n";
			$numerocampo = 0;
			foreach ($vettoreriga as $K => $v)
			{
				$numerocampo = $numerocampo + 1;
				$editcsv .= "\t\t<option value=$numerocampo>$numerocampo</option>\n";
			}
			$editcsv .= "</select></td>\n"
			. "\t<input type='hidden' name='filev' value='$fp' />\n"
			. "\t<input type='hidden' name='action' value='insertCSV' />\n"
			. "\t<tr><td align='right'><input type='submit' value='"
			.$clang->gT("Continue")."'></td>\n";
		}
	}
}

?>
