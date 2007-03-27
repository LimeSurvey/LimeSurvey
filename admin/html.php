<?php
/*
#############################################################
# >>> PHPSurveyor  										    #
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
# Public License Version 2 as published by the Free         #
# Software Foundation.										#
#															#
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

//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix)) {die ("Cannot run this script directly");}

if ($action == "listsurveys")
{
	$query = "SELECT a.*, c.* FROM ".db_table_name('surveys')." as a INNER JOIN ".db_table_name('surveys_rights')." AS b ON a.sid = b.sid INNER JOIN ".db_table_name('surveys_languagesettings')." as c ON ( surveyls_survey_id = a.sid AND surveyls_language = a.language ) WHERE b.uid = ".$_SESSION['loginID']." and surveyls_survey_id=a.sid and surveyls_language=a.language";
	$result = db_execute_assoc($query) or die($connect->ErrorMsg());

	if($result->RecordCount() > 0) {
		$listsurveys= "<br /><table  align='center' bgcolor='#DDDDDD' style='border: 1px solid #555555' "
		. "cellpadding='1' cellspacing='0' width='800'>
				  <tr bgcolor='#BBBBBB'>
				    <td height=\"22\" width='22'>&nbsp</strong></td>
				    <td height=\"22\"><strong>".$clang->gT("Survey")."</strong></td>
				    <td><strong>".$clang->gT("Date Created")."</strong></td>
				    <td><strong>".$clang->gT("Visibility")."</strong></td>
				    <td><strong>".$clang->gT("Status")."</strong></td>
				    <td colspan=\"3\"><strong>".$clang->gT("Action")."</strong></td>
				    <td colspan=\"3\"><strong>".$clang->gT("Responses")."</strong></td>
				  </tr>" ; 

		while($rows = $result->FetchRow())
		{
			$sidsecurityQ = "SELECT b.* FROM {$dbprefix}surveys AS a INNER JOIN {$dbprefix}surveys_rights AS b ON a.sid = b.sid WHERE a.sid='{$rows['sid']}' AND b.uid = ".$_SESSION['loginID']; //Getting rights for this survey and user
			$sidsecurityR = db_execute_assoc($sidsecurityQ);
			$sidsecurity = $sidsecurityR->FetchRow();
			
			if($rows['private']=="Y")
			{
				$visibility=$clang->gT("Private") ;
			}
			else $visibility =$clang->gT("Public") ;
			if($rows['active']=="Y")
			{
				if ($rows['useexpiry']=='Y' && $rows['expires'] < date("Y-m-d"))
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

			$listsurveys.="<tr>";

			if ($rows['active']=="Y")
			{
				if ($rows['useexpiry']=='Y' && $rows['expires'] < date("Y-m-d"))
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
						$listsurveys .= "<td><a href=\"#\" onClick=\"window.open('$scriptname?action=deactivate&amp;sid={$rows['sid']}', '_top')\""
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
					$listsurveys .= "<td><a href=\"#\" onClick=\"window.open('$scriptname?action=activate&amp;sid={$rows['sid']}', '_top')\""
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
					    "<td>".$status."</td>".
					    "<td>&nbsp;</td>
					    <td>&nbsp;</td>";

					    if ($status=="Active")
					    {
					        $listsurveys .= "<td colspan=\"3\" align='center'>".$responses."</td>";
					    }else{
						$listsurveys .= "<td>&nbsp;</td>";
					    }
					    $listsurveys .= "</tr>" ;
		}

		$listsurveys.="<tr bgcolor='#BBBBBB'>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td colspan=\"6\">&nbsp;</td>".
		"</tr>";
		$listsurveys.="</table><br />" ;
	}
	else $listsurveys="<br /><strong> ".$clang->gT("No Surveys available - please create one.")." </strong><br /><br />" ;
}

if ($action == "checksettings" || $action == "changelang")
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
	. "<table class='table2columns' align='center' bgcolor='#DDDDDD' style='border: 1px solid #555555' "
	. "cellpadding='1' cellspacing='0' width='600'>\n"
	. "\t<tr>\n"
	. "\t\t<td colspan='2' align='center' bgcolor='#BBBBBB'>\n"
	. "\t\t\t<strong>".$clang->gT("PHPSurveyor System Summary")."</strong>\n"
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
	. "\t\t\t<select name='lang' onChange='form.submit()'>\n";
	foreach (getlanguagedata() as $langkey=>$languagekind)
	{
		$cssummary .= "\t\t\t\t<option value='$langkey'";
		if ($langkey == $_SESSION['adminlang']) {$cssummary .= " selected";}
		$cssummary .= ">".$languagekind['description']." - ".$languagekind['nativedescription']."</option>\n";
	}
	$cssummary .= "\t\t\t</select>\n"
	. "\t\t\t<input type='hidden' name='action' value='changelang' />\n"
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
}

if ($surveyid)
{
	$query = "SELECT * FROM ".db_table_name('surveys_rights')." WHERE  sid = {$surveyid} AND uid = ".$_SESSION['loginID'];
	$result = $connect->SelectLimit($query, 1);
	if($result->RecordCount() > 0)
	{
		$surveysummary = "<script type='text/javascript'>\n"
		. "<!--\n"
		. "\tfunction showdetails(action)\n"
		. "\t\t{\n"
		. "\t\tif (action == \"hides\")\n"
		. "\t\t\t{\n"
		. "\t\t\tfor (i=0; i<=12; i++)\n"
		. "\t\t\t\t{\n"
		. "\t\t\t\tvar name='surveydetails'+i;\n"
		. "\t\t\t\tdocument.getElementById(name).style.display='none';\n"
		. "\t\t\t\t}\n"
		. "\t\t\t}\n"
		. "\t\telse if (action == \"shows\")\n"
		. "\t\t\t{\n"
		. "\t\t\tfor (i=0; i<=12; i++)\n"
		. "\t\t\t\t{\n"
		. "\t\t\t\tvar name='surveydetails'+i;\n"
		. "\t\t\t\tdocument.getElementById(name).style.display='';\n"
		. "\t\t\t\t}\n"
		. "\t\t\t}\n"
		. "\t\telse if (action == \"hideg\")\n"
		. "\t\t\t{\n"
		. "\t\t\tfor (i=20; i<=21; i++)\n"
		. "\t\t\t\t{\n"
		. "\t\t\t\tvar name='surveydetails'+i;\n"
		. "\t\t\t\tdocument.getElementById(name).style.display='none';\n"
		. "\t\t\t\t}\n"
		. "\t\t\t}\n"
		. "\t\telse if (action == \"showg\")\n"
		. "\t\t\t{\n"
		. "\t\t\tfor (i=20; i<=21; i++)\n"
		. "\t\t\t\t{\n"
		. "\t\t\t\tvar name='surveydetails'+i;\n"
		. "\t\t\t\tdocument.getElementById(name).style.display='';\n"
		. "\t\t\t\t}\n"
		. "\t\t\t}\n"
		. "\t\telse if (action == \"hideq\")\n"
		. "\t\t\t{\n"
		. "\t\t\tfor (i=30; i<=37; i++)\n"
		. "\t\t\t\t{\n"
		. "\t\t\t\tvar name='surveydetails'+i;\n"
		. "\t\t\t\tdocument.getElementById(name).style.display='none';\n"
		. "\t\t\t\t}\n"
		. "\t\t\t}\n"
		. "\t\telse if (action == \"showq\")\n"
		. "\t\t\t{\n"
		. "\t\t\tfor (i=30; i<=37; i++)\n"
		. "\t\t\t\t{\n"
		. "\t\t\t\tvar name='surveydetails'+i;\n"
		. "\t\t\t\tdocument.getElementById(name).style.display='';\n"
		. "\t\t\t\t}\n"
		. "\t\t\t}\n"
		. "\t\t}\n"
		. "-->\n"
		. "</script>\n";

		$baselang = GetBaseLanguageFromSurveyID($surveyid);
		$sumquery5 = "SELECT b.* FROM {$dbprefix}surveys AS a INNER JOIN {$dbprefix}surveys_rights AS b ON a.sid = b.sid WHERE a.sid=$surveyid AND b.uid = ".$_SESSION['loginID']; //Getting rights for this survey and user
		$sumquery3 = "SELECT * FROM ".db_table_name('questions')." WHERE sid=$surveyid AND language='".$baselang."'"; //Getting a count of questions for this survey
		$sumresult5 = db_execute_assoc($sumquery5);
		$sumrows5 = $sumresult5->FetchRow();
		$sumresult3 = $connect->Execute($sumquery3);
		$sumcount3 = $sumresult3->RecordCount();
		$sumquery2 = "SELECT * FROM ".db_table_name('groups')." WHERE sid=$surveyid AND language='".$baselang."'"; //Getting a count of groups for this survey
		$sumresult2 = $connect->Execute($sumquery2);
		$sumcount2 = $sumresult2->RecordCount();
		$sumquery1 = "SELECT * FROM ".db_table_name('surveys')." inner join ".db_table_name('surveys_languagesettings')." on (surveyls_survey_id=sid and surveyls_language=language) WHERE sid=$surveyid"; //Getting data for this survey
		$sumresult1 = db_select_limit_assoc($sumquery1, 1);
		$surveysummary .= "<table width='100%' align='center' bgcolor='#DDDDDD' border='0'>\n";

		$s1row = $sumresult1->FetchRow();

		$s1row = array_map('htmlspecialchars', $s1row);
		$activated = $s1row['active'];
		//BUTTON BAR
		$surveysummary .= "\t<tr>\n"
		. "\t\t<td colspan='2'>\n"
		. "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		. "\t\t\t\t<tr bgcolor='#555555'><td align='left'colspan='2' height='4'>"
		. "<font size='1' face='verdana' color='white'><strong>".$clang->gT("Survey")."</strong> "
		. "<font color='silver'>{$s1row['surveyls_title']} (ID:$surveyid)</font></font></td></tr>\n"
		. "\t\t\t\t<tr bgcolor='#999999'><td align='right' height='22'>\n";
		if ($activated == "N" && $sumcount3>0)
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/inactive.png' "
			. "title='' alt='".$clang->gT("This survey is not currently active")."' border='0' hspace='0' align='left'"
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("This survey is not currently active", "js")."');return false\" />\n";
			if($sumrows5['activate_survey'])
			{
				$surveysummary .= "<a href=\"#\" onClick=\"window.open('$scriptname?action=activate&amp;sid=$surveyid', '_top')\""
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Activate this Survey", "js")."');return false\">" .
				"<img src='$imagefiles/activate.png' name='ActivateSurvey' title='' alt='".$clang->gT("Activate this Survey")."' align='left' /></a>\n" ;
			}
			else
			{
				$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='14' align='left' border='0' hspace='0' />\n";
			}
		}
		elseif ($activated == "Y")
		{
			if (($s1row['useexpiry']=='Y') && ($s1row['expires'] < date("Y-m-d")))
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
				$surveysummary .= "<a href=\"#\" onClick=\"window.open('$scriptname?action=deactivate&amp;sid=$surveyid', '_top')\""
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
		elseif ($activated == "N")
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/inactive.png' title='".$clang->gT("This survey is not currently active")."' "
			. "alt='".$clang->gT("This survey is not currently active")."' border='0' hspace='0' align='left' />\n"
			. "\t\t\t\t\t<img src='$imagefiles/blank.gif' width='14' title='".$clang->gT("Cannot Activate this Survey")."' "
			. "alt='".$clang->gT("Cannot Activate this Survey")."' border='0' align='left' hspace='0' />\n";
		}

		$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0' />\n";
		// survey rights

		if($s1row['owner_id'] == $_SESSION['loginID'])
		{
			$surveysummary .= "\t\t\t\t\t<a href=\"#\" onClick=\"window.open('$scriptname?action=surveysecurity&amp;sid=$surveyid', '_top')\"" .
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
			$surveysummary .= "<a href=\"#\" accesskey='d' onclick=\"hideTooltip(); document.getElementById('testsurvpopup').style.visibility='visible';\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'$icontext');return false\">"
			."<img  src='$imagefiles/do.png' title='' "
			. "name='DoSurvey' align='left' alt='$icontext' /></a>";
			
			$tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
			$baselang = GetBaseLanguageFromSurveyID($surveyid);
			$tmp_survlangs[] = $baselang;

			// Test Survey Language Selection Popup
			$surveysummary .="<DIV class=\"testsurvpopup\" id=\"testsurvpopup\"><table width=\"100%\"><tr><td>".$clang->gT("Please select a language:")."</td></tr>";
			foreach ($tmp_survlangs as $tmp_lang)
			{
				$surveysummary .= "<tr><td><a href=\"#\" accesskey='d' onclick=\"document.getElementById('testsurvpopup').style.visibility='hidden'; window.open('".$publicurl."/index.php?sid=$surveyid&amp;newtest=Y&amp;lang=".$tmp_lang."', '_blank')\"><font color=\"#097300\"><b>".getLanguageNameFromCode($tmp_lang,false)."</b></font></a></td></tr>";
			}
			$surveysummary .= "<tr><td align=\"center\"><a href=\"#\" accesskey='d' onclick=\"document.getElementById('testsurvpopup').style.visibility='hidden';\"><font color=\"#DF3030\">".$clang->gT("Cancel")."</font></a></td></tr></table></DIV>";
			
			if (count($tmp_survlangs) > 2)
			{
				$tmp_pheight = 127 + ((count($tmp_survlangs)-2) * 28);
				$surveysummary .= "<script type='text/javascript'>document.getElementById('testsurvpopup').style.height='".$tmp_pheight."px';</script>";
			}
		}

		if($sumrows5['browse_response'])
		{
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('".$homeurl."/".$scriptname."?action=dataentry&amp;sid=$surveyid', '_blank')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Dataentry Screen for Survey", "js")."');return false\">"
			. "<img src='$imagefiles/dataentry.png' title='' align='left' alt='".$clang->gT("Dataentry Screen for Survey")."'"
			. "name='DoDataentry' /></a>\n";
		}
		else
		{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
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
			
			$surveysummary .= "<a href=\"#\" onclick=\"hideTooltip(); document.getElementById('printpopup').style.visibility='visible';\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Printable Version of Survey", "js")."');return false\">\n"
			. "<img src='$imagefiles/print.png' title='' name='ShowPrintableSurvey' align='left' alt='".$clang->gT("Printable Version of Survey")."' />"
			."</a>"
			. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0' />\n";
			
			$tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
			$baselang = GetBaseLanguageFromSurveyID($surveyid);
			$tmp_survlangs[] = $baselang;

			// Test Survey Language Selection Popup
			$surveysummary .="<DIV class=\"testsurvpopup\" id=\"printpopup\"><table width=\"100%\"><tr><td>".$clang->gT("Please select a language:")."</td></tr>";
			foreach ($tmp_survlangs as $tmp_lang)
			{
				$surveysummary .= "<tr><td><a href=\"#\" accesskey='d' onclick=\"document.getElementById('printpopup').style.visibility='hidden'; window.open('$scriptname?action=showprintablesurvey&amp;sid=$surveyid&amp;lang=".$tmp_lang."', '_blank')\"><font color=\"#097300\"><b>".getLanguageNameFromCode($tmp_lang,false)."</b></font></a></td></tr>";
			}
			$surveysummary .= "<tr><td align=\"center\"><a href=\"#\" accesskey='d' onclick=\"document.getElementById('printpopup').style.visibility='hidden';\"><font color=\"#DF3030\">".$clang->gT("Cancel")."</font></a></td></tr></table></DIV>";
			
			$surveysummary .= "<script type='text/javascript'>document.getElementById('printpopup').style.left='152px';</script>";
			if (count($tmp_survlangs) > 2)
			{
				$tmp_pheight = 110 + ((count($tmp_survlangs)-2) * 20);
				$surveysummary .= "<script type='text/javascript'>document.getElementById('printpopup').style.height='".$tmp_pheight."px';</script>";
			}
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
			$surveysummary .= "<a href=\"#\" onclick=\"window.open('".$homeurl."/dumpsurvey.php?sid=$surveyid', '_top')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Export Current Survey", "js")."');return false\">" .
			"<img src='$imagefiles/exportcsv.png' title='' alt='". $clang->gT("Export Current Survey")."' align='left' name='ExportSurvey' /></a>" ;
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
		. "align='right' onclick='showdetails(\"shows\")' />\n"
		. "\t\t\t\t\t<input type='image' src='$imagefiles/minus.gif' title='". $clang->gT("Hide Details of this Survey")."' alt='". $clang->gT("Hide Details of this Survey")."' name='MinimiseSurveyWindow' "
		. "align='right' onclick='showdetails(\"hides\")' />\n"
		. "\t\t\t\t\t<img src='$imagefiles/blank.gif' align='right' border='0' width='18' alt='' />\n"
		. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' align='right' border='0' alt='' hspace='0' />\n";
		if ($activated == "Y")
		{
			$surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='40' align='right' border='0' hspace='0' />\n";
		}
		elseif($sumrows5['define_questions'])
		{
			$surveysummary .= "<a href=\"#\" onClick=\"window.open('$scriptname?action=addgroup&amp;sid=$surveyid', '_top')\""
			. "onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Add New Group to Survey", "js")."');return false\"> " .
			"<img src='$imagefiles/add.png' title='' alt=''align='right'  name='AddNewGroup' /></a>\n" ;
		}
		$surveysummary .= "<font class=\"boxcaption\">".$clang->gT("Groups").":</font>"
		. "\t\t<select class=\"listboxgroups\" name='groupselect' "
		. "onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";

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
                 || $action=="editsurvey" || $action=="addgroup" || $action=="importgroup"|| $action=="ordergroups" || $action=="updatesurvey") {$showstyle="style='display: none'";}
		if (!isset($showstyle)) {$showstyle="";}
		$surveysummary .= "\t<tr $showstyle id='surveydetails0'><td><table class='table2columns'><tr><td align='right' valign='top' width='15%'>"
		. "<strong>".$clang->gT("Title").":</strong></td>\n"
		. "\t<td align='left' class='settingentryhighlight'><strong>{$s1row['surveyls_title']} "
		. "(ID {$s1row['sid']})</strong></td></tr>\n";
		$surveysummary2 = "\t<tr $showstyle id='surveydetails1'><td width='80'></td>"
		. "<td align='left'>\n";
		if ($s1row['private'] != "N") {$surveysummary2 .= $clang->gT("This survey is anonymous.")."<br />\n";}
		else {$surveysummary2 .= $clang->gT("This survey is NOT anonymous.")."<br />\n";}
		if ($s1row['format'] == "S") {$surveysummary2 .= $clang->gT("It is presented question by question.")."<br />\n";}
		elseif ($s1row['format'] == "G") {$surveysummary2 .= $clang->gT("It is presented group by group.")."<br />\n";}
		else {$surveysummary2 .= $clang->gT("It is presented on one single page.")."<br />\n";}
		if ($s1row['datestamp'] == "Y") {$surveysummary2 .= $clang->gT("Responses will be date stamped")."<br />\n";}
		if ($s1row['ipaddr'] == "Y") {$surveysummary2 .= $clang->gT("IP Addresses will be logged")."<br />\n";}
		if ($s1row['refurl'] == "Y") {$surveysummary2 .= $clang->gT("Referer-URL")."<br />\n";}
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
			. "onClick='return confirm(\"".$clang->gT("Are you sure you want regenerate the question codes?","js")."\")' "
			. ">".$clang->gT("Straight")."</a>] "
			. "[<a href='$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=bygroup' "
			. "onClick='return confirm(\"".$clang->gT("Are you sure you want regenerate the question codes?","js")."\")' "
			. ">".$clang->gT("By Group")."</a>]";
			$surveysummary2 .= "</td></tr>\n";
		}
		$surveysummary .= "\t<tr $showstyle id='surveydetails11'>"
		. "<td align='right' valign='top'><strong>"
		. $clang->gT("Survey URL:") . "</strong></td>\n";
		$tmp_url = $GLOBALS['publicurl'] . '/index.php?sid=' . $s1row['sid'];
		$surveysummary .= "\t\t<td align='left'> <a href='$tmp_url' target='_blank'>$tmp_url</a>"
		. "</td></tr>\n"
		. "\t<tr $showstyle id='surveydetails2'><td align='right' valign='top'><strong>"
		. $clang->gT("Description:")."</strong></td>\n\t\t<td align='left'>";
		if (trim($s1row['surveyls_description'])!='') {$surveysummary .= " {$s1row['surveyls_description']}";}
		$surveysummary .= "</td></tr>\n"
		. "\t<tr $showstyle id='surveydetails3'>\n"
		. "\t\t<td align='right' valign='top'><strong>"
		. $clang->gT("Welcome:")."</strong></td>\n"
		. "\t\t<td align='left'> {$s1row['surveyls_welcometext']}</td></tr>\n"
		. "\t<tr $showstyle id='surveydetails4'><td align='right' valign='top'><strong>"
		. $clang->gT("Administrator:")."</strong></td>\n"
		. "\t\t<td align='left'> {$s1row['admin']} ({$s1row['adminemail']})</td></tr>\n"
		. "\t<tr $showstyle id='surveydetails5'><td align='right' valign='top'><strong>"
		. $clang->gT("Fax To:")."</strong></td>\n\t\t<td align='left'>";
		if (trim($s1row['faxto'])!='') {$surveysummary .= " {$s1row['faxto']}";}
		$surveysummary .= "</td></tr>\n"
		. "\t<tr $showstyle id='surveydetails6'><td align='right' valign='top'><strong>"
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
		. "\t<tr $showstyle id='surveydetails7'><td align='right' valign='top'><strong>"
		. $clang->gT("Template:")."</strong></td>\n"
		. "\t\t<td align='left'> {$s1row['template']}</td></tr>\n"
		
		. "\t<tr $showstyle id='surveydetails8'><td align='right' valign='top'><strong>"
		. $clang->gT("Base Language:")."</strong></td>\n";
		if (!$s1row['language']) {$language=getLanguageNameFromCode($currentadminlang);} else {$language=getLanguageNameFromCode($s1row['language']);}
		$surveysummary .= "\t<td align='left'>$language</td></tr>\n";

		$additionnalLanguagesArray = GetAdditionalLanguagesFromSurveyID($surveyid);
		// get the rowspan of the Additionnal languages row
		// is at least 1 even if no additionnal language is present
		$additionnalLanguagesCount = count($additionnalLanguagesArray);
		if ($additionnalLanguagesCount == 0) $additionnalLanguagesCount = 1;
		$surveysummary .= "\t<tr $showstyle id='surveydetails12'><td align='right' valign='top' rowspan='".$additionnalLanguagesCount."'><strong>"
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

		if ($s1row['surveyls_urldescription']==""){$s1row['surveyls_urldescription']=$s1row['url'];}
		$surveysummary .= "\t<tr $showstyle id='surveydetails9'><td align='right' valign='top'><strong>"
		. $clang->gT("Exit Link").":</strong></td>\n"
		. "\t\t<td align='left'>";
		if ($s1row['url']!="") {$surveysummary .=" <a href=\"{$s1row['url']}\" title=\"{$s1row['url']}\">{$s1row['surveyls_urldescription']}</a>";}
		$surveysummary .="</td></tr>\n";
		$surveysummary .= "\t<tr $showstyle id='surveydetails10'><td align='right' valign='top'><strong>"
		. $clang->gT("Status").":</strong></td>\n"
		. "\t<td valign='top' align='left'>"
		. "<font size='1'>".$clang->gT("Number of groups in survey").": $sumcount2<br />\n"
		. $clang->gT("Number of questions in survey").": $sumcount3<br />\n";
		if ($activated == "N" && $sumcount3 > 0)
		{
			$surveysummary .= $clang->gT("Survey is not currently active.")."<br />\n";
		}
		elseif ($activated == "Y")
		{
			$surveysummary .= $clang->gT("Survey is currently active.")."<br />\n"
			. $clang->gT("Survey table name is").": 'survey_$surveyid'<br />";
		}
		else
		{
			$surveysummary .= $clang->gT("Survey cannot be activated yet.")."<br />\n";
			if ($sumcount2 == 0 && $sumrows5['define_questions'])
			{
				$surveysummary .= "\t<font class='statusentryhighlight'>[".$clang->gT("You need to add groups")."]</font><br />";
			}
			if ($sumcount3 == 0 && $sumrows5['define_questions'])
			{
				$surveysummary .= "\t<font class='statusentryhighlight'>[".$clang->gT("You need to add questions")."]</font>";
			}
		}
		$surveysummary .= "</font></td></tr>\n"
		. $surveysummary2
		. "</table></table>\n";
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

	$groupsummary = "<table width='100%' align='center' bgcolor='#DDDDDD' border='0'>\n";
	while ($grow = $grpresult->FetchRow())
	{
		$grow = array_map('htmlspecialchars', $grow);
		$groupsummary .= "\t<tr>\n"
		. "\t\t<td colspan='2'>\n"
		. "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		. "\t\t\t\t<tr bgcolor='#555555'><td align='left' colspan='2' height='4'>"
		. "<font size='1' face='verdana' color='white'><strong>".$clang->gT("Group")."</strong> "
		. "<font color='silver'>{$grow['group_name']} (ID:$gid)</font></font></td></tr>\n"
		. "\t\t\t\t<tr bgcolor='#AAAAAA'>\n"
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
				$groupsummary .= "\t\t\t\t\t<a href='$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid' onclick=\"return confirm('".$clang->gT("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?")."')\""
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Delete Current Group", "js")."');return false\">"
				. "<img src='$imagefiles/delete.png' alt='' name='DeleteWholeGroup' title='' align='left' border='0' hspace='0' /></a>";
			}
			else
			{
				$groupsummary .= "\t\t\t\t\t<a href='$scriptname?sid=$surveyid&amp;gid=$gid' onclick=\"alert('".$clang->gT("Impossible to delete this group because there is at least one question having a condition on its content","js")."')\" "
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Disabled")."-".$clang->gT("Delete Current Group", "js")."');return false\">"
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

			$groupsummary .="<a href='dumpgroup.php?sid=$surveyid&amp;gid=$gid' onmouseout=\"hideTooltip()\""
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
		. "align='right'  onclick='showdetails(\"showg\")' />"
		. "\t\t\t\t\t<input type='image' src='$imagefiles/minus.gif' title='"
		. $clang->gT("Hide Details of this Group")."' alt='". $clang->gT("Hide Details of this Group")."' name='MinimiseGroupWindow' "
		. "align='right'  onclick='showdetails(\"hideg\")' />\n"
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
			"align='right' name='AddNewQuestion' onClick=\"window.open('', '_top')\" /></a>\n";
		}
		$groupsummary .= "\t\t\t\t\t<font class=\"boxcaption\">".$clang->gT("Questions").":</font>&nbsp;<select class=\"listboxquestions\" name='qid' "
		. "onChange=\"window.open(this.options[this.selectedIndex].value, '_top')\">\n"
		. getquestions()
		. "\t\t\t\t\t</select>\n"
		. "\t\t\t\t</td></tr>\n"
		. "\t\t\t</table>\n"
		. "\t\t</td>\n"
		. "\t</tr>\n";
		if ($qid) {$gshowstyle="style='display: none'";}
		else	  {$gshowstyle="";}

		$groupsummary .= "\t<tr><td><table class='table2columns'><tr $gshowstyle id='surveydetails20'><td width='20%' align='right'><strong>"
		. $clang->gT("Title").":</strong></td>\n"
		. "\t<td align='left'>"
		. "{$grow['group_name']} ({$grow['gid']})</td></tr>\n"
		. "\t<tr $gshowstyle id='surveydetails21'><td valign='top' align='right'><strong>"
		. $clang->gT("Description:")."</strong></td>\n\t<td align='left'>";
		if (trim($grow['description'])!='') {$groupsummary .=$grow['description'];}
		$groupsummary .= "</td></tr>\n";

		if (!is_null($condarray))
		{
			$groupsummary .= "\t<tr $gshowstyle id='surveydetails22'><td align='right'><strong>"
			. $clang->gT("Questions with conditions to this group").":</strong></td>\n"
			. "\t<td valign='bottom' align='left'>";
			foreach ($condarray[$gid] as $depgid => $deprow)
			{
				foreach ($deprow['conditions'] as $depqid => $depcid)
				{
					//$groupsummary .= "[QID: ".$depqid."]"; 
					$listcid=implode("-",$depcid);
					$groupsummary .= " <a href='#' onClick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$depgid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."')\">[QID: ".$depqid."]</a>"; 
				}
			}
		}
	}
	$groupsummary .= "\n</table></table>\n";
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
	$questionsummary = "<table width='100%' align='center' bgcolor='#EEEEEE' border='0'>\n";

	// Check if other questions in the Survey are dependent upon this question
	$condarray=GetQuestDepsForConditions($surveyid,"all","all",$qid,"by-targqid","outsidegroup");

	while ($qrrow = $qrresult->FetchRow())
	{
		$qrrow = array_map('htmlspecialchars', $qrrow);
		$questionsummary .= "\t<tr>\n"
		. "\t\t<td colspan='2'>\n"
		. "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		. "\t\t\t\t<tr bgcolor='#555555'><td colspan='2' height='4' align='left'><font size='1' face='verdana' color='white'><strong>"
		. $clang->gT("Question")."</strong> <font color='silver'>{$qrrow['question']} (ID:$qid)</font></font></td></tr>\n"
		. "\t\t\t\t<tr bgcolor='#AAAAAA'>\n"
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
				"onclick=\"return confirm('".$clang->gT("Deleting this question will also delete any answers it includes. Are you sure you want to continue?")."')\""
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
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Disabled")."-".$clang->gT("Delete Current Question", "js")."');return false\">"
				. "<img src='$imagefiles/delete_disabled.png' name='DeleteWholeQuestion' alt= '' title='' "
				."align='left' border='0' hspace='0' /></a>\n";
			}
		}
		else {$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";}
		$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";

		if($sumrows5['export'])
		{
			$questionsummary .= "<a href='dumpquestion.php?qid=$qid' onmouseout=\"hideTooltip()\""
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
			$questionsummary .= "<a href='$scriptname?action=copyquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'" .
			"onmouseout=\"hideTooltip()\""
			. "onmouseover=\"showTooltip(event,'".$clang->gT("Copy Current Question", "js")."');return false\">" .
			"<img src='$imagefiles/copy.png' title=''alt='' align='left' name='CopyQuestion' /></a>\n"
			. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n";
		}
		else
		{
			$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}
		if($sumrows5['define_questions'])
		{
			$questionsummary .= "<a href='#' onClick=\"window.open('$scriptname?action=conditions&amp;sid=$surveyid&amp;qid=$qid', 'conditions', 'menubar=no, location=no, status=no, height=475, width=650, scrollbars=yes, resizable=yes, left=50, top=50')\""
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
				$questionsummary .= "<a href=\"#\" accesskey='d' onclick=\"hideTooltip(); document.getElementById('previewquestion').style.visibility='visible';\""
				. "onmouseout=\"hideTooltip()\""
				. "onmouseover=\"showTooltip(event,'".$clang->gT("Preview This Question", "js")."');return false\">"
				. "<img src='$imagefiles/preview.png' title='' alt='' align='left' name='previewquestion' /></a>\n"
				. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left' />\n";
			
				$tmp_survlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
				$baselang = GetBaseLanguageFromSurveyID($surveyid);
				$tmp_survlangs[] = $baselang;

				// Test Survey Language Selection Popup
				$surveysummary .="<DIV class=\"previewpopup\" id=\"previewquestion\"><table width=\"100%\"><tr><td>".$clang->gT("Please select a language:")."</td></tr>";
				foreach ($tmp_survlangs as $tmp_lang)
				{
					$surveysummary .= "<tr><td><a href=\"#\" accesskey='d' onclick=\"document.getElementById('previewquestion').style.visibility='hidden'; window.open('$scriptname?action=previewquestion&amp;sid=$surveyid&amp;qid=$qid&amp;lang=".$tmp_lang."', '_blank')\"><font color=\"#097300\"><b>".getLanguageNameFromCode($tmp_lang,false)."</b></font></a></td></tr>";
				}
				$surveysummary .= "<tr><td align=\"center\"><a href=\"#\" accesskey='d' onclick=\"document.getElementById('previewquestion').style.visibility='hidden';\"><font color=\"#DF3030\">".$clang->gT("Cancel")."</font></a></td></tr></table></DIV>";
			
				if (count($tmp_survlangs) > 2)
				{
					$tmp_pheight = 127 + ((count($tmp_survlangs)-2) * 28);
					$surveysummary .= "<script type='text/javascript'>document.getElementById('previewquestion').style.height='".$tmp_pheight."px';</script>";
				}
			}
		}
		else
		{
			$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='40' align='left' border='0' hspace='0' />\n";
		}
		if($sumrows5['define_questions'])
		{
			if ($qrrow['type'] == "O" || $qrrow['type'] == "L" || $qrrow['type'] == "!" || $qrrow['type'] == "!" || $qrrow['type'] == "M" || $qrrow['type'] == "Q" || $qrrow['type']=="A" || $qrrow['type'] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "E" || $qrrow['type'] == "F" || $qrrow['type'] == "H" || $qrrow['type'] == "P" || $qrrow['type'] == "R")
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
		. "onclick='showdetails(\"showq\")' />"
		. "\t\t\t\t\t<input type='image' src='$imagefiles/minus.gif' title='"
		. $clang->gT("Hide Details of this Question")."'  alt='". $clang->gT("Hide Details of this Question")."'align='right'  name='MinimiseQuestionWindow' "
		. "onclick='showdetails(\"hideq\")' />\n"
		. "\t\t\t\t</td></tr>\n"
		. "\t\t\t</table>\n"
		. "\t\t</td>\n"
		. "\t</tr>\n";
		if (returnglobal('viewanswer') || $action =="editquestion")	{$qshowstyle = "style='display: none'";}
		else							{$qshowstyle = "";}
		$questionsummary .= "\t<tr><td><table class='table2columns'><tr $qshowstyle id='surveydetails30'><td width='20%' align='right'><strong>"
		. $clang->gT("Code:")."</strong></td>\n"
		. "\t<td align='left'>{$qrrow['title']}";
		if ($qrrow['mandatory'] == "Y") {$questionsummary .= ": (<i>".$clang->gT("Mandatory Question")."</i>)";}
		else {$questionsummary .= ": (<i>".$clang->gT("Optional Question")."</i>)";}
		$questionsummary .= "</td></tr>\n"
		. "\t<tr $qshowstyle id='surveydetails31'><td align='right' valign='top'><strong>"
		. $clang->gT("Question:")."</strong></td>\n\t<td align='left'>{$qrrow['question']}</td></tr>\n"
		. "\t<tr $qshowstyle id='surveydetails32'><td align='right' valign='top'><strong>"
		. $clang->gT("Help:")."</strong></td>\n\t<td align='left'>";
		if (trim($qrrow['help'])!=''){$questionsummary .= "{$qrrow['help']}";}
		$questionsummary .= "</td></tr>\n";
		if ($qrrow['preg'])
		{
			$questionsummary .= "\t<tr $qshowstyle id='surveydetails33'><td align='right' valign='top'><strong>"
			. $clang->gT("Validation:")."</strong></td>\n\t<td align='left'>{$qrrow['preg']}"
			. "</td></tr>\n";
		}
		$qtypes = getqtypelist("", "array"); //qtypes = array(type code=>type description)
		$questionsummary .= "\t<tr $qshowstyle id='surveydetails34'><td align='right' valign='top'><strong>"
		.$clang->gT("Type:")."</strong></td>\n\t<td align='left'>{$qtypes[$qrrow['type']]}";
		if (($qrrow['type'] == "F" ||$qrrow['type'] == "H") && $sumrows5['define_questions'])
		{
			$questionsummary .= " (LID: {$qrrow['lid']}) "
			. "<input align='top' type='image' src='$imagefiles/labelssmall.png' title='"
			. $clang->gT("Edit/Add Label Sets")."' name='EditThisLabelSet' "
			. "onClick=\"window.open('$scriptname?action=labels&amp;lid={$qrrow['lid']}', '_blank')\" />\n";
		}
		$questionsummary .="</td></tr>\n";
		if ($qct == 0 && ($qrrow['type'] == "O" || $qrrow['type'] == "L" || $qrrow['type'] == "!" || $qrrow['type'] == "M" || $qrrow['type'] == "Q" || $qrrow['type'] == "A" || $qrrow['type'] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "E" || $qrrow['type'] == "P" || $qrrow['type'] == "R" || $qrrow['type'] == "F" ||$qrrow['type'] == "H"))
		{
			$questionsummary .= "\t\t<tr $qshowstyle id='surveydetails35'><td></td><td align='left'>"
			. "<font face='verdana' size='1' color='green'>"
			. $clang->gT("Warning").": ". $clang->gT("You need to add answers to this question")." "
			. "<input type='image' src='$imagefiles/answers.png' title='"
			. $clang->gT("Edit/Add Answers for this Question")."' name='EditThisQuestionAnswers'"
			. "onClick=\"window.open('".$scriptname."?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;viewanswer=Y', '_top')\" /></font></td></tr>\n";
		}
		if (!$qrrow['lid'] && ($qrrow['type'] == "F" ||$qrrow['type'] == "H"))
		{
			$questionsummary .= "\t\t<tr $qshowstyle id='surveydetails36'><td></td>"
			. "<td align='left'><font face='verdana' size='1' color='green'>"
			. $clang->gT("Warning").": ".$clang->gT("You need to choose a Label Set for this question")."</font></td></tr>\n";
		}
		if ($qrrow['type'] == "M" or $qrrow['type'] == "P")
		{
			$questionsummary .= "\t<tr $qshowstyle id='surveydetails37'>"
			. "<td align='right' valign='top'><strong>"
			. $clang->gT("Other:")."</strong></td>\n"
			. "\t<td align='left'>{$qrrow['other']}</td></tr>\n";
		}
		if (!is_null($condarray))
		{
			$questionsummary .= "\t<tr $qshowstyle id='surveydetails38'>"
			. "<td align='right' valign='top'><strong>"
			. $clang->gT("Other questions having conditions on this question:")
			. "\t<td align='left' valign='bottom'>";
			foreach ($condarray[$qid] as $depqid => $depcid)
			{
				$listcid=implode("-",$depcid);
				$questionsummary .= " <a href='#' onClick=\"window.open('admin.php?sid=".$surveyid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."')\">[QID: ".$depqid."]</a>";
			}	
		}
		$qid_attributes=getQuestionAttributes($qid);
	    $questionsummary .= "</table>";		
	}
	$questionsummary .= "</table>";
}

if (returnglobal('viewanswer'))
{
	
	// Get languages select on survey.
	$anslangs = GetAdditionalLanguagesFromSurveyID($surveyid);
	$baselang = GetBaseLanguageFromSurveyID($surveyid);
	array_unshift($anslangs,$baselang);
	
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

	// Print Key Contorl JavaScript
	$vasummary = keycontroljs();

	$vasummary .= "\t<table width='100%' align='center' border='0' bgcolor='#EEEEEE'>\n"
	."<tr bgcolor='#555555' >\n"
	."\t<td colspan='4'><strong><font size='1' face='verdana' color='white'>\n"
	.$clang->gT("Answers")
	."\t</font></strong></td>\n"
	."</tr>\n"
	."\t<tr><td colspan='5'><form name='editanswers' method='post' action='$scriptname'>\n"
	. "\t<input type='hidden' name='sid' value='$surveyid' />\n"
	. "\t<input type='hidden' name='gid' value='$gid' />\n"
	. "\t<input type='hidden' name='qid' value='$qid' />\n"
	. "\t<input type='hidden' name='viewanswer' value='Y' />\n"
	."<input type='hidden' name='sortorder' value='' />\n"
	. "\t<input type='hidden' name='action' value='modanswer' />\n";
	$vasummary .= "<div class='tab-pane' id='tab-pane-1'>";
	$first=true;
	$sortorderids=''; 
	$codeids='';

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
        		."<tr bgcolor='#BBBBBB'>\n"
        		."\t<td width='25%' align=right><strong><font size='1' face='verdana' >\n"
        		.$clang->gT("Code")
        		."\t</font></strong></td>\n"
        		."\t<td width='35%'><strong><font size='1' face='verdana'>\n"
        		.$clang->gT("Answer")
        		."\t</font></strong></td>\n"
        		."\t<td width='25%'><strong><font size='1' face='verdana'>\n"
        		.$clang->gT("Action")
        		."\t</font></strong></td>\n"
        		."\t<td width='15%' align=center><strong><font size='1' face='verdana'>\n"
        		.$clang->gT("Order")
        		."\t</font></strong></td>\n"
        		."</tr></thead>"
                ."<tbody align='center'>";
		while ($row=$result->FetchRow())
		{
			$row['code'] = htmlspecialchars($row['code']);
			$row['answer']=htmlspecialchars($row['answer']);
			
			$sortorderids=$sortorderids.' '.$row['language'].'_'.$row['sortorder'];
			if ($first) {$codeids=$codeids.' '.$row['sortorder'];}
			
			$vasummary .= "<tr><td width='25%' align=right>\n";
			if ($row['default_value'] == 'Y') $vasummary .= "<font color='#FF0000'>".$clang->gT("Default")."</font>";
			if ($activated > 1)
			{
				$vasummary .= "\t{$row['code']}"
				."<input type='hidden' name='code_{$row['sortorder']}' value=\"{$row['code']}\" maxlength='5' size='5'"
				."onKeyPress=\"return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_-')\""
				." />";
			}
			elseif (!$first)
			{
				$vasummary .= "\t{$row['code']}";
			}
			else
			{
				$vasummary .= "\t<input type='text' name='code_{$row['sortorder']}' maxlength='10' size='10' value=\"{$row['code']}\" />\n";
			}

			$vasummary .= "\t</td>\n"
			."\t<td width='35%'>\n"
			."\t<input type='text' name='answer_{$row['language']}_{$row['sortorder']}' maxlength='1000' size='80' value=\"{$row['answer']}\" />\n"
			."\t</td>\n"
			."\t<td width='25%'>\n";
			if ($activated == 0)
			{
				$vasummary .= "\t<input type='submit' name='method' value='".$clang->gT("Del")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
			}
			// Don't show Default Button for array question types
			if ($qtype != "A" && $qtype != "B" && $qtype != "C" && $qtype != "E" && $qtype != "F" && $qtype != "H" && $qtype != "R" && $qtype != "Q") $vasummary .= "\t<input type='submit' name='method' value='".$clang->gT("Default")."' onclick=\"this.form.sortorder.value='{$row['sortorder']}'\" />\n";
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
			$vasummary .= "\t<tr><td colspan=4><center><input type='submit' name='method' value='".$clang->gT("Save All")."'  />"
			."</center></td></tr>\n";
		}
		$position=sprintf("%05d", $position);
		if ($activated == 0)
		{
			if ($first==true)
			{
				$vasummary .= "<tr><td><br /></td></tr><tr><td width='25%' align=right>"
				."<strong>".$clang->gT("New Answer").":</strong> ";
            	$vasummary .= "<input type='text' maxlength='10' name='insertcode' size='10' id='addnewanswercode' />\n";
            	$first=false;
				$vasummary .= "\t</td>\n"
				."\t<td width='35%'>\n"
				."\t<input type='text' maxlength='1000' name='insertanswer' size='80' />\n"
				."\t</td>\n"
				."\t<td width='25%'>\n"
				."\t<input type='submit' name='method' value='".$clang->gT("Add new Answer")."' />\n"
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
			.$clang->gT("Warning")."</strong>: ".$clang->gT("You cannot add answers because they are being used by an active survey.")."</i></strong></font>\n"
			."\t</td>\n"
			."</tr>\n";
		}
		$first=false;
		$vasummary .= "</tbody></table>\n";
		$vasummary .=  "<input type='hidden' name='sortorderids' value='$sortorderids' />\n";
		$vasummary .=  "<input type='hidden' name='codeids' value='$codeids' />\n";
		$vasummary .= "</div></form>";
	}


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

			$usersummary = "<table width='100%' border='0'>\n\t<tr><td colspan='6' bgcolor='black' align='center'>\n"
			. "\t\t<strong><font color='white'>".$clang->gT("Set Survey Rights")."</td></tr>\n";

			$usersummary .= "\t\t<th align='center'>".$clang->gT("Edit Survey Properties")."</th>\n"
			. "\t\t<th align='center'>".$clang->gT("Define Questions")."</th>\n"
			. "\t\t<th align='center'>".$clang->gT("Browse Responses")."</th>\n"
			. "\t\t<th align='center'>".$clang->gT("Export")."</th>\n"
			. "\t\t<th align='center'>".$clang->gT("Delete Survey")."</th>\n"
			. "\t\t<th align='center'>".$clang->gT("Activate Survey")."</th>\n"
			. "\t\t<th></th>\n\t</tr>\n"
			. "<form action='$scriptname?sid={$surveyid}' method='post'>\n";

			//content
			$usersummary .= "\t\t<td align='center'><input type=\"checkbox\" name=\"edit_survey_property\" value=\"edit_survey_property\"";
			if($resul2row['edit_survey_property']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "\t\t<td align='center'><input type=\"checkbox\" name=\"define_questions\" value=\"define_questions\"";
			if($resul2row['define_questions']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "\t\t<td align='center'><input type=\"checkbox\" name=\"browse_response\" value=\"browse_response\"";
			if($resul2row['browse_response']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "\t\t<td align='center'><input type=\"checkbox\" name=\"export\" value=\"export\"";
			if($resul2row['export']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "\t\t<td align='center'><input type=\"checkbox\" name=\"delete_survey\" value=\"delete_survey\"";
			if($resul2row['delete_survey']) {
				$usersummary .= " checked ";
			}
			$usersummary .=" /></td>\n";
			$usersummary .= "\t\t<td align='center'><input type=\"checkbox\" name=\"activate_survey\" value=\"activate_survey\"";
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
		$usersummary = "<table width='100%' border='0'>\n\t<tr><td colspan='6' bgcolor='black' align='center'>\n"
		. "\t\t<strong><font color='white'>".$clang->gT("Set Survey Rights")."</td></tr>\n";

		$usersummary .= "\t\t<th align='center'>Edit Survey Property</th>\n"
		. "\t\t<th align='center'>".$clang->gT("Define Questions")."</th>\n"
		. "\t\t<th align='center'>".$clang->gT("Browse Response")."</th>\n"
		. "\t\t<th align='center'>".$clang->gT("Export")."</th>\n"
		. "\t\t<th align='center'>".$clang->gT("Delete Survey")."</th>\n"
		. "\t\t<th align='center'>".$clang->gT("Activate Survey")."</th>\n"
		. "\t\t<th></th>\n\t</tr>\n"
		. "<form action='$scriptname?sid={$surveyid}' method='post'>\n";

		//content
		$usersummary .= "\t\t<td align='center'><input type=\"checkbox\" name=\"edit_survey_property\" value=\"edit_survey_property\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "\t\t<td align='center'><input type=\"checkbox\" name=\"define_questions\" value=\"define_questions\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "\t\t<td align='center'><input type=\"checkbox\" name=\"browse_response\" value=\"browse_response\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "\t\t<td align='center'><input type=\"checkbox\" name=\"export\" value=\"export\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "\t\t<td align='center'><input type=\"checkbox\" name=\"delete_survey\" value=\"delete_survey\"";

		$usersummary .=" /></td>\n";
		$usersummary .= "\t\t<td align='center'><input type=\"checkbox\" name=\"activate_survey\" value=\"activate_survey\"";

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

if($action == "surveysecurity")
{
	$query = "SELECT sid FROM ".db_table_name('surveys')." WHERE sid = {$surveyid} AND owner_id = ".$_SESSION['loginID'];
	$result = db_execute_assoc($query);
	if($result->RecordCount() > 0)
	{
		$query2 = "SELECT a.uid, b.users_name FROM ".db_table_name('surveys_rights')." AS a INNER JOIN ".db_table_name('users')." AS b ON a.uid = b.uid WHERE a.sid = {$surveyid} AND b.uid != ".$_SESSION['loginID'] ." ORDER BY b.users_name";
		$result2 = db_execute_assoc($query2);
		$surveysecurity = "<table width='100%' rules='rows' border='0'>\n\t<tr><td colspan='3' bgcolor='black' align='center'>\n"
		. "\t\t<strong><font color='white'>".$clang->gT("Survey Security")."</td></tr>\n"
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
					$surveysecurity .= "\t<tr  bgcolor='#999999'>\n";
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
				."<input type='submit' value='".$clang->gT("Delete")."' onClick='return confirm(\"".$clang->gT("Are you sure you want to delete this entry.")."\")' />"
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
		. "\t\t\t\t\t<strong>".$clang->gT("User").": </strong><select name='uid' class=\"listboxsurveys\">\n"
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
		. "\t\t\t\t\t<strong>".$clang->gT("Groups").": </strong><select name='ugid' class=\"listboxsurveys\">\n"
		//. $surveyuserselect
		. getsurveyusergrouplist()
		. "\t\t\t\t\t</select>\n"
		. "\t\t\t\t</td>\n"

		. "\t\t<td align='center'><input type='submit' value='".$clang->gT("Add Group")."' />"
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
			$editsurvey .= "<form name='addnewsurvey' action='$scriptname' method='post'>\n<table width='100%' border='0' class='table2columns'>\n\t<tr><td colspan='4' bgcolor='black' align='center'>"
			. "\t\t<font class='settingcaption'><font color='white'>".$clang->gT("Edit Survey - Step 1 of 2")."</font></font></td></tr>\n"
			. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Administrator:")."</font></td>\n"
			. "\t\t<td align='left'><input type='text' size='50' name='admin' value=\"{$esrow['admin']}\" /></td></tr>\n"
			. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Admin Email:")."</font></td>\n"
			. "\t\t<td align='left'><input type='text' size='50' name='adminemail' value=\"{$esrow['adminemail']}\" /></td></tr>\n"
			. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Fax To:")."</font></td>\n"
			. "\t\t<td align='left'><input type='text' size='50' name='faxto' value=\"{$esrow['faxto']}\" /></td></tr>\n";
			$editsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Format:")."</font></td>\n"
			. "\t\t<td align='left'><select name='format'>\n"
			. "\t\t\t<option value='S'";
			if ($esrow['format'] == "S" || !$esrow['format']) {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("Question by Question")."</option>\n"
			. "\t\t\t<option value='G'";
			if ($esrow['format'] == "G") {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("Group by Group")."</option>\n"
			. "\t\t\t<option value='A'";
			if ($esrow['format'] == "A") {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("All in one")."</option>\n"
			. "\t\t</select></td>\n"
			. "\t</tr>\n";
			//TEMPLATES
			$editsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Template:")."</font></td>\n"
			. "\t\t<td align='left'><select name='template'>\n";
			foreach (gettemplatelist() as $tname)
			{
				$editsurvey .= "\t\t\t<option value='$tname'";
				if ($esrow['template'] && htmlspecialchars($tname) == $esrow['template']) {$editsurvey .= " selected";}
				elseif (!$esrow['template'] && $tname == "default") {$editsurvey .= " selected";}
				$editsurvey .= ">$tname</option>\n";
			}
			$editsurvey .= "\t\t</select></td>\n"
			. "\t</tr>\n";
			//COOKIES
			$editsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Use Cookies?")."</font></td>\n"
			. "\t\t<td align='left'><select name='usecookie'>\n"
			. "\t\t\t<option value='Y'";
			if ($esrow['usecookie'] == "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "\t\t\t<option value='N'";
			if ($esrow['usecookie'] != "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "\t\t</select></td>\n"
			. "\t</tr>\n";
			//ALLOW SAVES
			$editsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Allow Saves?")."</font></td>\n"
			. "\t\t<td align='left'><select name='allowsave'>\n"
			. "\t\t\t<option value='Y'";
			if (!$esrow['allowsave'] || $esrow['allowsave'] == "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "\t\t<option value='N'";
			if ($esrow['allowsave'] == "N") {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "\t\t</select></td>\n"
			. "\t</tr>\n";
			//ALLOW PREV
			$editsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Show [<< Prev] button")."</font></td>\n"
			. "\t\t<td align='left'><select name='allowprev'>\n"
			. "\t\t\t<option value='Y'";
			if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "\t\t<option value='N'";
			if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "\t\t</select></td>\n"
			. "\t</tr>\n";
			//NOTIFICATION
			$editsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Admin Notification:")."</font></td>\n"
			. "\t\t<td align='left'><select name='notification'>\n"
			. getNotificationlist($esrow['notification'])
			. "\t\t</select></td>\n"
			. "\t</tr>\n";
			//ANONYMOUS
			$editsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Anonymous?")."</font></td>\n";
			if ($esrow['active'] == "Y")
			{
				$editsurvey .= "\t\t<td align='left'>\n\t\t\t<font class='settingcaption'>";
				if ($esrow['private'] == "N") {$editsurvey .= " ".$clang->gT("This survey is NOT anonymous.");}
				else {$editsurvey .= $clang->gT("This survey is anonymous.");}
				$editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
				. "\t\t</font></font>\n";
				$editsurvey .= "<input type='hidden' name='private' value=\"{$esrow['private']}\" /></td>\n";
			}
			else
			{
				$editsurvey .= "\t\t<td align='left'><select name='private'>\n"
				. "\t\t\t<option value='Y'";
				if ($esrow['private'] == "Y") {$editsurvey .= " selected";}
				$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
				. "\t\t\t<option value='N'";
				if ($esrow['private'] != "Y") {$editsurvey .= " selected";}
				$editsurvey .= ">".$clang->gT("No")."</option>\n"
				. "</select>\n\t\t</td>\n";
			}
			$editsurvey .= "</tr>\n";
			$editsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Allow public registration?")."</font></td>\n"
			. "\t\t<td align='left'><select name='allowregister'>\n"
			. "\t\t\t<option value='Y'";
			if ($esrow['allowregister'] == "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "\t\t\t<option value='N'";
			if ($esrow['allowregister'] != "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "\t\t</select></td>\n\t</tr>\n";
			$editsurvey .= "\t<tr><td align='right' valign='top'><font class='settingcaption'>".$clang->gT("Token Attribute Names:")."</font></td>\n"
			. "\t\t<td align='left'><font class='settingcaption'><input type='text' size='25' name='attribute1'"
			. " value=\"{$esrow['attribute1']}\" />(".$clang->gT("Attribute 1").")<br />"
			. "<input type='text' size='25' name='attribute2'"
			. " value=\"{$esrow['attribute2']}\" />(".$clang->gT("Attribute 2").")</font></td>\n\t</tr>\n";
			$editsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Date Stamp?")."</font></td>\n";

			if ($esrow['active'] == "Y")
			{
				$editsurvey .= "\t\t<td align='left'>\n\t\t\t<font class='settingcaption'>";
				if ($esrow['datestamp'] != "Y") {$editsurvey .= " ".$clang->gT("Responses will not be date stamped.");}
				else {$editsurvey .= $clang->gT("Responses will be date stamped.");}
				$editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
				. "\t\t</font></font>\n";
				$editsurvey .= "<input type='hidden' name='datestamp' value=\"{$esrow['datestamp']}\" /></td>\n";
			}
			else
			{
				$editsurvey .= "\t\t<td align='left'><select name='datestamp'>\n"
				. "\t\t\t<option value='Y'";
				if ($esrow['datestamp'] == "Y") {$editsurvey .= " selected";}
				$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
				. "\t\t\t<option value='N'";
				if ($esrow['datestamp'] != "Y") {$editsurvey .= " selected";}
				$editsurvey .= ">".$clang->gT("No")."</option>\n"
				. "</select>\n\t\t</td>\n";
			}
			$editsurvey .= "</tr>\n";

			$editsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Save IP Address?")."</font></td>\n";

			if ($esrow['active'] == "Y")
			{
				$editsurvey .= "\t\t<td align='left'>\n\t\t\t<font class='settingcaption'>";
				if ($esrow['ipaddr'] != "Y") {$editsurvey .= " ".$clang->gT("Responses will not have the IP address logged.");}
				else {$editsurvey .= $clang->gT("Responses will have the IP address logged");}
				$editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
				. "\t\t</font></font>\n";
				$editsurvey .= "<input type='hidden' name='ipaddr' value='".$esrow['ipaddr']."' />\n</td>";
			}
			else
			{
				$editsurvey .= "\t\t<td align='left'><select name='ipaddr'>\n"
				. "\t\t\t<option value='Y'";
				if ($esrow['ipaddr'] == "Y") {$editsurvey .= " selected";}
				$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
				. "\t\t\t<option value='N'";
				if ($esrow['ipaddr'] != "Y") {$editsurvey .= " selected";}
				$editsurvey .= ">".$clang->gT("No")."</option>\n"
				. "</select>\n\t\t</td>\n";
			}

			// begin REF URL Block
			$editsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Save Referring URL?")."</font></td>\n";

			if ($esrow['active'] == "Y")
			{
				$editsurvey .= "\t\t<td align='left'>\n\t\t\t<font class='settingcaption'>";
				if ($esrow['refurl'] != "Y") {$editsurvey .= " ".$clang->gT("Responses will not have their referring URL logged.");}
				else {$editsurvey .= $clang->gT("Responses will have their referring URL logged.");}
				$editsurvey .= "<font size='1' color='red'>&nbsp;(".$clang->gT("Cannot be changed").")\n"
				. "\t\t</font></font>\n";
				$editsurvey .= "<input type='hidden' name='refurl' value='".$esrow['refurl']."' />\n</td>";
			}
			else
			{
				$editsurvey .= "\t\t<td align='left'><select name='refurl'>\n"
				. "\t\t\t<option value='Y'";
				if ($esrow['refurl'] == "Y") {$editsurvey .= " selected";}
				$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
				. "\t\t\t<option value='N'";
				if ($esrow['refurl'] != "Y") {$editsurvey .= " selected";}
				$editsurvey .= ">".$clang->gT("No")."</option>\n"
				. "</select>\n\t\t</td>\n";
			}
			// BENBUN - END REF URL Block

			// Base Language
			$editsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Base Language:")."</font></td>\n"
			. "\t\t<td align='left'>\n".GetLanguageNameFromCode($esrow['language'])
			. "\t\t</td>\t</tr>\n"

			// Additional languages listbox
			. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Additional Languages").":</font></td>\n"
			. "\t\t<td align='left'><select multiple style='min-width:250px;'  size='5' id='additional_languages' name='additional_languages'>";
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
			. "<td align=left><INPUT type=\"button\" value=\"<< ".$clang->gT("Add")."\" onclick=\"DoAdd()\" ID=\"AddBtn\" /><BR /> <INPUT type=\"button\" value=\"".$clang->gT("Remove")." >>\" onclick=\"DoRemove(0,'')\" ID=\"RemoveBtn\"  /></td>\n"

			// Available languages listbox
			. "\t\t<td align=left><select size='5' id='available_languages' name='available_languages'>";
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
			. " </tr>\n";

			$editsurvey .= "</select></td>"
			. " </tr>\n"
			. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Expires?")."</font></td>\n"
			. "\t\t\t<td align='left'><select name='useexpiry'><option value='Y'";
			if (isset($esrow['useexpiry']) && $esrow['useexpiry'] == "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n"
			. "\t\t\t<option value='N'";
			if (!isset($esrow['useexpiry']) || $esrow['useexpiry'] != "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("No")."</option></select></td></tr><tr><td align='right'><font class='settingcaption'>".$clang->gT("Expiry Date:")."</font></td>\n"
			. "\t\t<td align='left'><input type='text' id='f_date_b' size='12' name='expires' value=\"{$esrow['expires']}\" /><button type='reset' id='f_trigger_b'>...</button></td></tr>\n"
			. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("End URL:")."</font></td>\n"
			. "\t\t<td align='left'><input type='text' size='50' name='url' value=\"{$esrow['url']}\" /></td></tr>\n"
			. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Automatically load URL when survey complete?")."</font></td>\n"
			. "\t\t<td align='left'><select name='autoredirect'>";
			$editsurvey .= "\t\t\t<option value='Y'";
			if (isset($esrow['autoredirect']) && $esrow['autoredirect'] == "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("Yes")."</option>\n";
			$editsurvey .= "\t\t\t<option value='N'";
			if (!isset($esrow['autoredirect']) || $esrow['autoredirect'] != "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">".$clang->gT("No")."</option>\n"
			. "</select></td></tr>";

			$editsurvey .= "\t<tr><td colspan='4' align='center'><input type='submit' onClick='return UpdateLanguageIDs(mylangs,\"".$clang->gT("All questions, answers, etc for removed languages will be lost. Are you sure?")."\");' class='standardbtn' value='".$clang->gT("Save and Continue")." >>' />\n"
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

	
		$editsurvey ="<script type='text/javascript'>\n"
		. "<!--\n"
		. "function fillin(tofield, fromfield)\n"
		. "\t{\n"
		. "\t\tif (confirm(\"".$clang->gT("This will replace the existing text. Continue?")."\")) {\n"
		. "\t\t\tdocument.getElementById(tofield).value = document.getElementById(fromfield).value\n"
		. "\t\t}\n"
		. "\t}\n"
		. "--></script>\n"
        . "<table width='100%' border='0'>\n\t<tr><td bgcolor='black' align='center'>"
		. "\t\t<font class='settingcaption'><font color='white'>".$clang->gT("Edit Survey - Step 2 of 2")."</font></font></td></tr></table>\n"
		. '<div class="tab-pane" id="tab-pane-1">';
		foreach ($grplangs as $grouplang)
		{
            // this one is created to get the right default texts fo each language
            $bplang = new phpsurveyor_lang($grouplang);		
    		$esquery = "SELECT * FROM ".db_table_name("surveys_languagesettings")." WHERE surveyls_survey_id=$surveyid and surveyls_language='$grouplang'";
    		$esresult = db_execute_assoc($esquery);
    		$esrow = $esresult->FetchRow();
			$editsurvey .= '<div class="tab-page"> <h2 class="tab">'.getLanguageNameFromCode($esrow['surveyls_language'],false);
			if ($esrow['surveyls_language']==GetBaseLanguageFromSurveyID($surveyid)) {$editsurvey .= '('.$clang->gT("Base Language").')';}
			$editsurvey .= '</h2>';
			$esrow = array_map('htmlspecialchars', $esrow);
			$editsurvey .= "<form name='addnewsurvey' action='$scriptname' method='post'>\n";

			$editsurvey .= "\t\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Title").":</span>\n"
			. "\t\t<span class='settingentry'><input type='text' size='50' name='short_title_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_title']}\" /></span>\n"
			. "\t</div><div class='settingrow'><span class='settingcaption'>".$clang->gT("Description:")."</span>\n"
			. "\t\t<span class='settingentry'><textarea cols='50' rows='5' name='description_".$esrow['surveyls_language']."'>{$esrow['surveyls_description']}</textarea></span>\n"
			. "\t</div><div class='settingrow'><span class='settingcaption'>".$clang->gT("Welcome:")."</span>\n"
			. "\t\t<span class='settingentry'><textarea cols='50' rows='5' name='welcome_".$esrow['surveyls_language']."'>".str_replace("&lt;br /&gt;", "\n", $esrow['surveyls_welcometext'])."</textarea></span></div>\n";


			$editsurvey .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Invitation Email Subject:")."</span>\n"
			. "\t\t<span class='settingentry'><input type='text' size='54' name='email_invite_subj_".$esrow['surveyls_language']."' id='email_invite_subj_{$grouplang}' value=\"{$esrow['surveyls_email_invite_subj']}\" />\n"
			. "\t\t<input type='hidden' name='email_invite_subj_default_".$esrow['surveyls_language']."' id='email_invite_subj_default_{$grouplang}' value='".$bplang->gT("Invitation to participate in survey")."' />\n"
			. "\t\t<input type='button' value='".$clang->gT("Use default")."' onClick='javascript: fillin(\"email_invite_subj_{$grouplang}\",\"email_invite_subj_default_{$grouplang}\")' />\n"
			. "\t</span></div>\n";
			$editsurvey .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Invitation Email:")."</span>\n"
			. "\t\t<span class='settingentry'><textarea cols=50 rows=5 name='email_invite_".$esrow['surveyls_language']."' id='email_invite_{$grouplang}'>{$esrow['surveyls_email_invite']}</textarea>\n"
			. "\t\t<input type='hidden' name='email_invite_default_".$esrow['surveyls_language']."' id='email_invite_default_{$grouplang}' value='".$bplang->gT("Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}")."' />\n"
			. "\t\t<input type='button' value='".$clang->gT("Use default")."' onClick='javascript: fillin(\"email_invite_{$grouplang}\",\"email_invite_default_{$grouplang}\")' />\n"
			. "\t</span></div>\n";
			$editsurvey .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Email Reminder Subject:")."</span>\n"
			. "\t\t<span class='settingentry'><input type='text' size='54' name='email_remind_subj_".$esrow['surveyls_language']."' id='email_remind_subj_{$grouplang}' value=\"{$esrow['surveyls_email_remind_subj']}\" />\n"
			. "\t\t<input type='hidden' name='email_remind_subj_default_".$esrow['surveyls_language']."' id='email_remind_subj_default_{$grouplang}' value='".$bplang->gT("Reminder to participate in survey")."' />\n"
			. "\t\t<input type='button' value='".$clang->gT("Use default")."' onClick='javascript: fillin(\"email_remind_subj_{$grouplang}\",\"email_remind_subj_default_{$grouplang}\")' />\n"
			. "\t</span></div>\n";
			$editsurvey .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Email Reminder:")."</span>\n"
			. "\t\t<span class='settingentry'><textarea cols=50 rows=5 name='email_remind_".$esrow['surveyls_language']."' id='email_remind_{$grouplang}'>{$esrow['surveyls_email_remind']}</textarea>\n"
			. "\t\t<input type='hidden' name='email_remind_default_".$esrow['surveyls_language']."' id='email_remind_default_{$grouplang}' value='".$bplang->gT("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}")."' />\n"
			. "\t\t<input type='button' value='".$clang->gT("Use default")."' onClick='javascript: fillin(\"email_remind_{$grouplang}\",\"email_remind_default_{$grouplang}\")' />\n"
			. "\t</span></div>\n";
			$editsurvey .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Confirmation Email Subject")."</span>\n"
			. "\t\t<span class='settingentry'><input type='text' size='54' name='email_confirm_subj_".$esrow['surveyls_language']."' id='email_confirm_subj_{$grouplang}' value=\"{$esrow['surveyls_email_confirm_subj']}\" />\n"
			. "\t\t<input type='hidden' name='email_confirm_subj_default_".$esrow['surveyls_language']."' id='email_confirm_subj_default_{$grouplang}' value='".$bplang->gT("Confirmation of completed survey")."' />\n"
			. "\t\t<input type='button' value='".$clang->gT("Use default")."' onClick='javascript: fillin(\"email_confirm_subj_{$grouplang}\",\"email_confirm_subj_default_{$grouplang}\")' />\n"
			. "\t</span></div>\n";
			$editsurvey .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Confirmation Email")."</span>\n"
			. "\t\t<span class='settingentry'><textarea cols=50 rows=5 name='email_confirm_".$esrow['surveyls_language']."' id='email_confirm_{$grouplang}'>{$esrow['surveyls_email_confirm']}</textarea>\n"
			. "\t\t<input type='hidden' name='email_confirm_default_".$esrow['surveyls_language']."' id='email_confirm_default_{$grouplang}' value='".$bplang->gT("Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}")."' />\n"
			. "\t\t<input type='button' value='".$clang->gT("Use default")."' onClick='javascript: fillin(\"email_confirm_{$grouplang}\",\"email_confirm_default_{$grouplang}\")'>\n"
			. "\t</span></div>\n";
			$editsurvey .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Public registration Email Subject:")."</span>\n"
			. "\t\t<span class='settingentry'><input type='text' size='54' name='email_register_subj_".$esrow['surveyls_language']."' id='email_register_subj_{$grouplang}' value=\"{$esrow['surveyls_email_register_subj']}\" />\n"
			. "\t\t<input type='hidden' name='email_register_subj_default_".$esrow['surveyls_language']."' id='email_register_subj_default_{$grouplang}' value='".$bplang->gT("Survey Registration Confirmation")."' />\n"
			. "\t\t<input type='button' value='".$clang->gT("Use default")."' onClick='javascript:  fillin(\"email_register_subj_{$grouplang}\",\"email_register_subj_default_{$grouplang}\")' />\n"
			. "\t</span></div>\n";
			$editsurvey .= "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("Public registration Email:")."</span>\n"
			. "\t\t<span class='settingentry'><textarea cols=50 rows=5 name='email_register_{$grouplang}' id='email_register_{$grouplang}'>{$esrow['surveyls_email_register']}</textarea>\n"
			. "\t\t<input type='hidden' name='email_register_default_".$esrow['surveyls_language']."' id='email_register_default_{$grouplang}' value='".$bplang->gT("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.")."' />\n"
			. "\t\t<input type='button' value='".$clang->gT("Use default")."' onClick='javascript:  fillin(\"email_register_{$grouplang}\",\"email_register_default_{$grouplang}\")' />\n"
			. "\t</span class='settingentry'></div>\n"
			. "\t<div class='settingrow'><span class='settingcaption'>".$clang->gT("URL Description:")."</span>\n"
			. "\t\t<span class='settingentry'><input type='text' size='50' name='urldescrip_".$esrow['surveyls_language']."' value=\"{$esrow['surveyls_urldescription']}\" />\n"
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

        $ordergroups = "<table width='100%' border='0'>\n\t<tr ><td colspan='2' bgcolor='black' align='center'>"
		. "\t\t<font class='settingcaption'><font color='white'>".$clang->gT("Change Group Order")."</font></font></td></tr>"
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
				$ordergroups .= "<li>".$clang->gT("Group")." <a href='#' onClick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$depgid."')\">".$targrow['depgpname']."</a> ".$clang->gT("depends on group")." <a href='#' onClick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$targgid."')\">".$targrow['targetgpname']."</a> ".$clang->gT("see the marked conditions on").":";
				foreach($targrow['conditions'] as $depqid => $depqrow)
				{
					$listcid=implode("-",$depqrow);
					$ordergroups .= " <a href='#' onClick=\"window.open('admin.php?sid=".$surveyid."&amp;gid=".$depgid."&amp;qid=".$depqid."&amp;action=conditions&amp;markcid=".$listcid."')\"> [QID: ".$depqid."]</a>";
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
		$newsurvey  = "<form name='addnewsurvey' action='$scriptname' method='post' onsubmit=\"return isEmpty(document.getElementById('surveyls_title'), '".("Error: You have to enter a title for this survey.")."');\" >\n"
        . "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n"
		. "\t\t<font class='settingcaption'><font color='white'>".$clang->gT("Create Survey")."</font></font></td></tr>\n"
		. "\t<tr>\n"
		. "\t\t<td align='right' width='25%'><font class='settingcaption'>".$clang->gT("Title").":</font></td>\n"
		. "\t\t<td><input type='text' size='50' id='surveyls_title' name='surveyls_title' /><font size=1> ".$clang->gT("(This field is mandatory.)")."</font></td></tr>\n"
		. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Description:")."</font>	</td>\n"
		. "\t\t<td><textarea cols='50' rows='5' name='description'></textarea></td></tr>\n"
		. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Welcome:")."</font></td>\n"
		. "\t\t<td><textarea cols='50' rows='5' name='welcome'></textarea></td></tr>\n"
		. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Administrator:")."</font></td>\n"
		. "\t\t<td><input type='text' size='50' name='admin' /></td></tr>\n"
		. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Admin Email:")."</font></td>\n"
		. "\t\t<td><input type='text' size='50' name='adminemail' /></td></tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Fax To:")."</font></td>\n"
		. "\t\t<td><input type='text' size='50' name='faxto' /></td></tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Format:")."</font></td>\n"
		. "\t\t<td><select name='format'>\n"
		. "\t\t\t<option value='S' selected>".$clang->gT("Question by Question")."</option>\n"
		. "\t\t\t<option value='G'>".$clang->gT("Group by Group")."</option>\n"
		. "\t\t\t<option value='A'>".$clang->gT("All in one")."</option>\n"
		. "\t\t</select></td>\n"
		. "\t</tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Template:")."</font></td>\n"
		. "\t\t<td><select name='template'>\n";
		foreach (gettemplatelist() as $tname)
		{
			$newsurvey .= "\t\t\t<option value='$tname'";
			if (isset($esrow) && $esrow['template'] && $tname == $esrow['template']) {$newsurvey .= " selected";}
			elseif ((!isset($esrow) || !$esrow['template']) && $tname == "default") {$newsurvey .= " selected";}
			$newsurvey .= ">$tname</option>\n";
		}
		$newsurvey .= "\t\t</select></td>\n"
		. "\t</tr>\n";
		//COOKIES
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Use Cookies?")."</font></td>\n"
		. "\t\t<td><select name='usecookie'>\n"
		. "\t\t\t<option value='Y'";
		if (isset($esrow) && $esrow['usecookie'] == "Y") {$newsurvey .= " selected";}
		$newsurvey .= ">".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N'";
		if (isset($esrow) && $esrow['usecookie'] != "Y" || !isset($esrow)) {$newsurvey .= " selected";}
		$newsurvey .= ">".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n"
		. "\t</tr>\n";
		//ALLOW SAVES
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Allow Saves?")."</font></td>\n"
		. "\t\t<td><select name='allowsave'>\n"
		. "\t\t\t<option value='Y'";
		if (!isset($esrow['allowsave']) || !$esrow['allowsave'] || $esrow['allowsave'] == "Y") {$newsurvey .= " selected";}
		$newsurvey .= ">".$clang->gT("Yes")."</option>\n"
		. "\t\t<option value='N'";
		if (isset($esrow['allowsave']) && $esrow['allowsave'] == "N") {$newsurvey .= " selected";}
		$newsurvey .= ">".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n"
		. "\t</tr>\n";
		//ALLOW PREV
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Show [<< Prev] button")."</font></td>\n"
		. "\t\t<td><select name='allowprev'>\n"
		. "\t\t\t<option value='Y'";
		if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") {$newsurvey .= " selected";}
		$newsurvey .= ">".$clang->gT("Yes")."</option>\n"
		. "\t\t<option value='N'";
		if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") {$newsurvey .= " selected";}
		$newsurvey .= ">".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n"
		. "\t</tr>\n";
		//NOTIFICATIONS
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Admin Notification:")."</font></td>\n"
		. "\t\t<td><select name='notification'>\n"
		. getNotificationlist(0)
		. "\t\t</select></td>\n"
		. "\t</tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Anonymous?")."</font></td>\n"
		. "\t\t<td><select name='private'>\n"
		. "\t\t\t<option value='Y' selected>".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N'>".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n\t</tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Invitation Email Subject:")."</font></td>\n"
		. "\t\t<td><input type='text' size='54' name='email_invite_subj' value='".$clang->gT("Invitation to participate in survey")."' />\n"
		. "\t</td></tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Invitation Email:")."</font></td>\n"
		. "\t\t<td><textarea cols=50 rows=5 name='email_invite'>".$clang->gT("Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}")."</textarea>\n"
		. "\t</td></tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Email Reminder Subject:")."</font></td>\n"
		. "\t\t<td><input type='text' size='54' name='email_remind_subj' value='".$clang->gT("Reminder to participate in survey")."' />\n"
		. "\t</td></tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Email Reminder:")."</font></td>\n"
		. "\t\t<td><textarea cols=50 rows=5 name='email_remind'>".$clang->gT("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}")."</textarea>\n"
		. "\t</td></tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Confirmation Email Subject")."</font></td>\n"
		. "\t\t<td><input type='text' size='54' name='email_confirm_subj' value='".$clang->gT("Confirmation of completed survey")."' />\n"
		. "\t</td></tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Confirmation Email")."</font></td>\n"
		. "\t\t<td><textarea cols=50 rows=5 name='email_confirm'>".$clang->gT("Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}")."</textarea>\n"
		. "\t</td></tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Allow public registration?")."</font></td>\n"
		. "\t\t<td><select name='allowregister'>\n"
		. "\t\t\t<option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N' selected>".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n\t</tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Public registration Email Subject:")."</font></td>\n"
		. "\t\t<td><input type='text' size='54' name='email_register_subj' value='".$clang->gT("Survey Registration Confirmation")."' />\n"
		. "\t</td></tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Public registration Email:")."</font></td>\n"
		. "\t\t<td><textarea cols=50 rows=5 name='email_register'>".$clang->gT("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.")."</textarea>\n"
		. "\t</td></tr>\n";
		$newsurvey .= "\t<tr><td align='right' valign='top'><font class='settingcaption'>".$clang->gT("Token Attribute Names:")."</font></td>\n"
		. "\t\t<td><font class='settingcaption'><input type='text' size='25' name='attribute1' />(".$clang->gT("Attribute 1").")<br />"
		. "<input type='text' size='25' name='attribute2' />(".$clang->gT("Attribute 2").")</font></td>\n\t</tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Date Stamp?")."</font></td>\n"
		. "\t\t<td><select name='datestamp'>\n"
		. "\t\t\t<option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N' selected>".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n\t</tr>\n";
		// IP Address
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Save IP Address?")."</font></td>\n"
		. "\t\t<td><select name='ipaddr'>\n"                                . "\t\t\t<option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N' selected>".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n\t</tr>\n";
		// Referring URL
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Save Referring URL?")."</font></td>\n"
		. "\t\t<td><select name='refurl'>\n"                                . "\t\t\t<option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N' selected>".$clang->gT("No")."</option>\n"
		. "\t\t</select></td>\n\t</tr>\n";
		//Survey Language
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Base Language:")."</font></td>\n"
		. "\t\t<td><select name='language'>\n";


		foreach (getLanguageData() as  $langkey2=>$langname)
		{
			$newsurvey .= "\t\t\t<option value='".$langkey2."'";
			if ($defaultlang == $langkey2) {$newsurvey .= " selected";}
			$newsurvey .= ">".$langname['description']." - ".$langname['nativedescription']."</option>\n";
		}

		$newsurvey .= "\t\t</select><font size='1'> ".$clang->gT("This setting cannot be changed later!")."</font></td>\n"
		. "\t</tr>\n";
		$newsurvey .= "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Expires?")."</font></td>\n"
		. "\t\t\t<td><select name='useexpiry'><option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N' selected>".$clang->gT("No")."</option></select></td></tr>\n"
		. "<tr><td align='right'><font class='settingcaption'>".$clang->gT("Expiry Date:")."</font></td>\n"
		. "\t\t<td><input type='text' id='f_date_b' size='12' name='expires' value='"
		. date("Y-m-d")."' /><button type='reset' id='f_trigger_b'>...</button>"
		. "<font size='1'> ".$clang->gT("Date Format").": YYYY-MM-DD</font></td></tr>\n"
		. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("End URL:")."</font></td>\n"
		. "\t\t<td><input type='text' size='50' name='url' value='http://";
		if (isset($esrow)) {$newsurvey .= $esrow['url'];}
		$newsurvey .= "' /></td></tr>\n"
		. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("URL Description:")."</font></td>\n"
		. "\t\t<td><input type='text' size='50' name='urldescrip' value='";
		if (isset($esrow)) {$newsurvey .= $esrow['surveyls_urldescription'];}
		$newsurvey .= "' /></td></tr>\n"
		. "\t<tr><td align='right'><font class='settingcaption'>".$clang->gT("Automatically load URL when survey complete?")."</font></td>\n"
		. "\t\t<td><select name='autoredirect'>\n"
		. "\t\t\t<option value='Y'>".$clang->gT("Yes")."</option>\n"
		. "\t\t\t<option value='N' selected>".$clang->gT("No")."</option>\n"
		. "</select></td></tr>"
		. "\t<tr><td colspan='2' align='center'><input type='submit' value='".$clang->gT("Create Survey")."' />\n"
		. "\t<input type='hidden' name='action' value='insertnewsurvey' /></td>\n"
		. "\t</tr>\n"
		. "</table></form>\n";
		$newsurvey .= "<center><font class='settingcaption'>".$clang->gT("OR")."</font></center>\n";
		$newsurvey .= "<form enctype='multipart/form-data' name='importsurvey' action='$scriptname' method='post' onsubmit=\"return validatefilename(this);\">\n"
		. "<table width='100%' border='0'>\n"
		. "<tr><td colspan='2' bgcolor='black' align='center'>\n"
		. "\t\t<font class='settingcaption'><font color='white'>".$clang->gT("Import Survey")."</font></font></td></tr>\n\t<tr>"
		. "\t\t<td align='right'><font class='settingcaption'>".$clang->gT("Select CSV/SQL File:")."</font></td>\n"
		. "\t\t<td><input name=\"the_file\" type=\"file\" size=\"35\" /></td></tr>\n"
		. "\t<tr><td colspan='2' align='center'><input type='submit' value='".$clang->gT("Import Survey")."' />\n"
		. "\t<input type='hidden' name='action' value='importsurvey' /></TD>\n"
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

function questionjavascript($type, $qattributes)
{
	$newquestionoutput = "<script type='text/javascript'>\n"
	. "<!--\n"
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
	. "\telse if (QuestionType == 'S' || QuestionType == 'T' || QuestionType == 'U' || QuestionType == 'N' || QuestionType=='')\n"
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
	. "-->\n"
	. "</script>\n";

	return $newquestionoutput;
}


function upload()
{
	global $questionsummary, $sid, $qid, $gid;
	$questionsummary .= "\t\t<tr id='surveydetails37'><td></td><td>"
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
