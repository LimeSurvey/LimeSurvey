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

//Ensure script is not run directly, avoid path disclosure
if (!isset($dbprefix)) {die ("Cannot run this script directly");}

if ($action == "checksettings" || $action == "changelang")
	{
	//GET NUMBER OF SURVEYS
	$query = "SELECT sid FROM {$dbprefix}surveys";
	$result = mysql_query($query);
	$surveycount=mysql_num_rows($result);
	$query = "SELECT sid FROM {$dbprefix}surveys WHERE active='Y'";
	$result = mysql_query($query);
	$activesurveycount=mysql_num_rows($result);
	$query = "SELECT user FROM {$dbprefix}users";
	$result = mysql_query($query);
	$usercount = mysql_num_rows($result);
	$result = mysql_list_tables($databasename);
	while ($row = mysql_fetch_row($result))
		{
		$stlength=strlen($dbprefix).strlen("old");
		if (substr($row[0], 0, $stlength+strlen("_tokens")) == $dbprefix."old_tokens")
			{
			$oldtokenlist[]=$row[0];
			}
		elseif (substr($row[0], 0, strlen($dbprefix) + strlen("tokens")) == $dbprefix."tokens")
			{
			$tokenlist[]=$row[0];
			}
		elseif (substr($row[0], 0, $stlength) == $dbprefix."old")
			{
			$oldresultslist[]=$row[0];
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
				. "<table align='center' bgcolor='#DDDDDD' style='border: 1px solid #555555' "
				. "cellpadding='1' cellspacing='0' width='450'>\n"
				. "\t<tr>\n"
				. "\t\t<td colspan='2' align='center' bgcolor='#BBBBBB'>$setfont\n"
				. "\t\t\t<strong>"._PS_TITLE."</strong>\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td width='50%' align='right'>$setfont\n"
				. "\t\t\t<strong>"._PS_DBNAME.":</strong></font>\n"
				. "\t\t</td><td>$setfont\n"
				. "\t\t\t$databasename\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right'>$setfont\n"
				. "\t\t\t<strong>"._PS_DEFLANG.":</strong></font>\n"
				. "\t\t</td><td>$setfont\n"
				. "\t\t\t$realdefaultlang\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right' >$setfont\n"
				. "\t\t\t<strong>"._PS_CURLANG.":</strong>\n"
				. "\t\t</font></td><td>$setfont\n"
				. "\t\t\t<select name='lang' $slstyle onChange='form.submit()'>\n";
	foreach (getadminlanguages() as $language)
		{
		$cssummary .= "\t\t\t\t<option value='$language'";
		if ($language == $defaultlang) {$cssummary .= " selected";}
		$cssummary .= ">$language</option>\n";
		}
	$cssummary .= "\t\t\t</select>\n"
				. "\t\t\t<input type='hidden' name='action' value='changelang'>\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right'>$setfont\n"
				. "\t\t\t<strong>"._PS_USERS.":</strong>\n"
				. "\t\t</font></td><td>$setfont\n"
				. "\t\t\t$usercount\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right'>$setfont\n"
				. "\t\t\t<strong>"._SURVEYS.":</strong>\n"
				. "\t\t</font></td><td>$setfont\n"
				. "\t\t\t$surveycount\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right'>$setfont\n"
				. "\t\t\t<strong>"._PS_ACTIVESURVEYS.":</strong>\n"
				. "\t\t</font></td><td>$setfont\n"
				. "\t\t\t$activesurveycount\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right'>$setfont\n"
				. "\t\t\t<strong>"._PS_DEACTSURVEYS.":</strong>\n"
				. "\t\t</font></td><td>$setfont\n"
				. "\t\t\t$deactivatedsurveys\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right'>$setfont\n"
				. "\t\t\t<strong>"._PS_ACTIVETOKENS.":</strong>\n"
				. "\t\t</font></td><td>$setfont\n"
				. "\t\t\t$activetokens\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right'>$setfont\n"
				. "\t\t\t<strong>"._PS_DEACTTOKENS.":</strong></font>\n"
				. "\t\t</td><td>$setfont\n"
				. "\t\t\t$deactivatedtokens\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "</table></form>\n"
				. "<table><tr><td height='1'></td></tr></table>\n";
	$cssummary .= "<table align='center' bgcolor='#DDDDDD' style='border: 1px solid #555555' "
				. "cellpadding='1' cellspacing='0' width='450'>\n"
				. "<tr><td align='center'>$setfont<br />"
				. "<a href='".$homeurl."/dbchecker.php'>"._PS_CHECKDBINTEGRITY."</a>"
				. "<br />&nbsp;</font></td></tr>\n"
				. "</table>\n"
				. "<table><tr><td height='1'></td></tr></table>\n";
	}

if ($surveyid)
	{
	$surveysummary = "<script type='text/javascript'>\n"
					. "<!--\n"
					. "\tfunction showdetails(action)\n"
					. "\t\t{\n"
					. "\t\tif (action == \"hides\")\n"
					. "\t\t\t{\n"
					. "\t\t\tfor (i=0; i<=11; i++)\n"
					. "\t\t\t\t{\n"
					. "\t\t\t\tvar name='surveydetails'+i;\n"
					. "\t\t\t\tdocument.getElementById(name).style.display='none';\n"
					. "\t\t\t\t}\n"
					. "\t\t\t}\n"
					. "\t\telse if (action == \"shows\")\n"
					. "\t\t\t{\n"
					. "\t\t\tfor (i=0; i<=11; i++)\n"
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
	$sumquery3 = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid"; //Getting a count of questions for this survey
	$sumresult3 = mysql_query($sumquery3);
	$sumcount3 = mysql_num_rows($sumresult3);
	$sumquery2 = "SELECT * FROM {$dbprefix}groups WHERE sid=$surveyid"; //Getting a count of groups for this survey
	$sumresult2 = mysql_query($sumquery2);
	$sumcount2 = mysql_num_rows($sumresult2);
	$sumquery1 = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid"; //Getting data for this survey
	$sumresult1 = mysql_query($sumquery1);
	$surveysummary .= "<table width='100%' align='center' bgcolor='#DDDDDD' border='0'>\n";
	while ($s1row = mysql_fetch_array($sumresult1))
		{
		$s1row = array_map('htmlspecialchars', $s1row);
		$activated = $s1row['active'];
		//BUTTON BAR
		$surveysummary .= "\t<tr>\n"
						. "\t\t<td colspan='2'>\n"
						. "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
						. "\t\t\t\t<tr bgcolor='#555555'><td colspan='2' height='4'>"
						. "$setfont<font size='1' face='verdana' color='white'><strong>"._SURVEY."</strong> "
						. "<font color='silver'>{$s1row['short_title']}</font></font></font></td></tr>\n"
						. "\t\t\t\t<tr bgcolor='#999999'><td align='right' height='22'>\n";
		if ($activated == "N" && $sumcount3>0) 
			{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/inactive.gif' "
							. "title='"._S_INACTIVE_BT."' alt='"._S_INACTIVE_BT."' border='0' hspace='0' align='left'>\n"
							. "\t\t\t\t\t<input type='image' src='$imagefiles/activate.gif' name='ActivateSurvey' "
							. "title='"._S_ACTIVATE_BT."' alt='"._S_ACTIVATE_BT."' align='left' "
							. "onClick=\"window.open('$scriptname?action=activate&amp;sid=$surveyid', '_top')\">\n";
			}
		elseif ($activated == "Y")
			{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/active.gif' title='"._S_ACTIVE_BT."' "
							. "alt='"._S_ACTIVE_BT."' align='left'>\n"
							. "\t\t\t\t\t<input type='image' src='$imagefiles/deactivate.gif' name='DeactivateSurvey' "
							. "alt='"._S_DEACTIVATE_BT."' title='"._S_DEACTIVATE_BT."' align='left' "
							. "onClick=\"window.open('$scriptname?action=deactivate&amp;sid=$surveyid', '_top')\">\n";
			}
		elseif ($activated == "N")
			{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/inactive.gif' title='"._S_INACTIVE_BT."' "
							. "alt='"._S_INACTIVE_BT."' border='0' hspace='0' align='left'>\n"
							. "\t\t\t\t\t<img src='$imagefiles/blank.gif' width='11' title='"._S_CANNOTACTIVATE_BT."' "
							. "alt='"._S_CANNOTACTIVATE_BT."' border='0' align='left' hspace='0'>\n";
			}
		$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0'>\n"
						. "\t\t\t\t\t<input type='image' accesskey='d' src='$imagefiles/do.gif' title='"._S_DOSURVEY_BT."' "
						. "name='DoSurvey' align='left' alt='"._S_DOSURVEY_BT."' "
						. "onclick=\"window.open('".$publicurl."/index.php?sid=$surveyid&amp;newtest=Y', '_blank')\">\n"
						. "\t\t\t\t\t<input type='image' src='$imagefiles/dataentry.gif' "
						. "title='"._S_DATAENTRY_BT."' align='left' alt='"._S_DATAENTRY_BT."'"
						. "name='DoDataentry' onclick=\"window.open('".$homeurl."/dataentry.php?sid=$surveyid', '_blank')\">\n"
						. "\t\t\t\t\t<input type='image' src='$imagefiles/print.gif' title='"._S_PRINTABLE_BT."' "
						. "name='ShowPrintableSurvey' align='left' alt='"._S_PRINTABLE_BT."' "
						. "onclick=\"window.open('".$homeurl."/printablesurvey.php?sid=$surveyid', '_blank')\">\n"
						. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0'>\n"
						. "\t\t\t\t\t<input type='image' src='$imagefiles/edit.gif' title='"._S_EDIT_BT."' "
						. "name='EditSurvey' align='left' alt='"._S_EDIT_BT."'"
						. "onclick=\"window.open('$scriptname?action=editsurvey&amp;sid=$surveyid', '_top')\">\n";
		if ($sumcount3 == 0 && $sumcount2 == 0)
			{
			$surveysummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/delete.gif' title='"
							. _S_DELETE_BT."' align='left' name='DeleteWholeSurvey' "
							. "onclick=\"window.open('$scriptname?action=delsurvey&amp;sid=$surveyid', '_top')\">\n";
			}
		else
			{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' align='left' border='0' hspace='0'>\n";
			}
		$surveysummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/export.gif' title='". _S_EXPORT_BT."' alt='". _S_EXPORT_BT."' align='left' name='ExportSurvey' "
						. "onclick=\"window.open('".$homeurl."/dumpsurvey.php?sid=$surveyid', '_top')\">\n";
		$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0'>\n"
						. "<input type='image' src='$imagefiles/assessments.gif' title='". _S_ASSESSMENT_BT."' alt='". _S_ASSESSMENT_BT."' align='left' name='SurveyAssessment' "
						. "onclick=\"window.open('".$homeurl."/assessments.php?sid=$surveyid', '_top')\">\n";		
		
		if ($activated == "Y")
			{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0'>\n"
							. "\t\t\t\t\t<input type='image' src='$imagefiles/browse.gif' title='"._S_BROWSE_BT."' "
							. "align='left' name='BrowseSurveyResults' alt='"._S_BROWSE_BT."'"
							. "onclick=\"window.open('".$homeurl."/browse.php?sid=$surveyid', '_top')\">\n"
							. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0'>\n";
			if ($s1row['allowsave'] == "Y")
				{
				$surveysummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/saved.gif' title='"._S_SAVED_BT."' "
								. "align='left'  name='BrowseSaved' alt='"._S_SAVED_BT."' "
								. "onclick=\"window.open('".$homeurl."/saved.php?sid=$surveyid', '_top')\">\n"
								. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0'>\n";
				}				
			$surveysummary .="\t\t\t\t\t<input type='image' src='$imagefiles/tokens.gif' title='"._S_TOKENS_BT."' "
							. "align='left'  name='TokensControl' alt='"._S_TOKENS_BT."'"
							. "onclick=\"window.open('$homeurl/tokens.php?sid=$surveyid', '_top')\">\n";
			}
		$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0'>\n"
						. "\t\t\t\t</td>\n"
						. "\t\t\t\t<td align='right' valign='middle' width='330'>\n";
		if (!$gid) 
			{
			$surveysummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' title='". _S_CLOSE_BT."' alt='". _S_CLOSE_BT."' align='right'  name='CloseSurveyWindow' "
							. "onclick=\"window.open('$scriptname', '_top')\">\n";
			}
		else 
			{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' width='21' align='right' "
							. "border='0' hspace='0' alt=''>\n";
			}
		$surveysummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/plus.gif' title='". _S_MAXIMISE_BT."' alt='". _S_MAXIMISE_BT."' name='MaximiseSurveyWindow' "
						. "align='right' onclick='showdetails(\"shows\")'>\n"
						. "\t\t\t\t\t<input type='image' src='$imagefiles/minus.gif' title='". _S_MINIMISE_BT."' alt='". _S_MINIMISE_BT."' name='MinimiseSurveyWindow' "
						. "align='right' onclick='showdetails(\"hides\")'>\n"
						. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' align='right' border='0' "
						. "alt='' hspace='0'>\n";
		if ($activated == "Y")
			{
			$surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='20' align='right' border='0' hspace='0'>\n";
			}
		else
			{
			$surveysummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/add.gif' title='"
							. _S_ADDGROUP_BT."' alt='". _S_ADDGROUP_BT."'align='right'  name='AddNewGroup' "
							. "onClick=\"window.open('$scriptname?action=addgroup&amp;sid=$surveyid', '_top')\">\n";
			}
		$surveysummary .= "$setfont<font size='1' color='#222222'><strong>"._GROUPS.":</strong>"
						. "\t\t<select style='font-size: 9; font-family: verdana; color: #222222; "
						. "background: silver; width: 160' name='groupselect' "
						. "onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
		if (getgrouplist($gid))
			{
			$surveysummary .= getgrouplist($gid);
			}
		else
			{
			$surveysummary .= "<option>"._NONE."</option>\n";
			}
		$surveysummary .= "</select>\n"
						. "\t\t\t\t</font></font></td>"
						. "</tr>\n"
						. "\t\t\t</table>\n"
						. "\t\t</td>\n"
						. "\t</tr>\n";
		
		//SURVEY SUMMARY
		if ($gid || $qid || $action=="editsurvey" || $action=="addgroup") {$showstyle="style='display: none'";}
		if (!isset($showstyle)) {$showstyle="";}
		$surveysummary .= "\t<tr $showstyle id='surveydetails0'><td align='right' valign='top' width='15%'>"
						. "$setfont<strong>"._SL_TITLE."</strong></font></td>\n"
						. "\t<td>$setfont<font color='#000080'><strong>{$s1row['short_title']} "
						. "(ID {$s1row['sid']})</strong></font></font></td></tr>\n";
		$surveysummary2 = "\t<tr $showstyle id='surveydetails1'><td width='80'></td>"
						. "<td>$setfont<font size='1' color='#000080'>\n";
		if ($s1row['private'] != "N") {$surveysummary2 .= _SS_ANONYMOUS."<br />\n";}
		else {$surveysummary2 .= _SS_TRACKED."<br />\n";}
		if ($s1row['format'] == "S") {$surveysummary2 .= _SS_QBYQ."<br />\n";}
		elseif ($s1row['format'] == "G") {$surveysummary2 .= _SS_GBYG."<br />\n";}
		else {$surveysummary2 .= _SS_SBYS."<br />\n";}
		if ($s1row['datestamp'] == "Y") {$surveysummary2 .= _SS_DATESTAMPED."<br />\n";}
		if ($s1row['ipaddr'] == "Y") {$surveysummary2 .= _SS_IPADDRESS."<br />\n";}
		if ($s1row['usecookie'] == "Y") {$surveysummary2 .= _SS_COOKIES."<br />\n";}
		if ($s1row['allowregister'] == "Y") {$surveysummary2 .= _SS_ALLOWREGISTER."<br />\n";}
		if ($s1row['allowsave'] == "Y") {$surveysummary2 .= _SS_ALLOWSAVE."<br />\n";}
		switch ($s1row['notification'])
			{
			case 0:
				$surveysummary2 .= _NT_NONE."<br />\n";
				break;
			case 1:
				$surveysummary2 .= _NT_SINGLE."<br />\n";
				break;
			case 2:
				$surveysummary2 .= _NT_RESULTS."<br />\n";
				break;
			}
		$surveysummary2 .= _RE_REGENNUMBER
						 . " [<a href='$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=straight' "
						 . "onClick='return confirm(\"Are you sure?\")' "
						 . ">"._RE_STRAIGHT."</a>] "
						 . "[<a href='$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=bygroup' "
						 . "onClick='return confirm(\"Are you sure?\")' "
						 . ">"._RE_BYGROUP."</a>]";
		$surveysummary2 .= "</font></font></td></tr>\n";
		$surveysummary .= "\t<tr $showstyle id='surveydetails11'>"
			. "<td align='right' valign='top'>$setfont<strong>"
			. _SL_SURVEYURL . "</strong></font></td>\n";
		$tmp_url = $GLOBALS['publicurl'] . '/index.php?sid=' . $s1row['sid'];
		$surveysummary .= "\t\t<td>$setfont <a href='$tmp_url' target='_blank'>$tmp_url</a>"
						. "</font></td></tr>\n"
						. "\t<tr $showstyle id='surveydetails2'><td align='right' valign='top'>$setfont<strong>"
						. _SL_DESCRIPTION."</strong></font></td>\n\t\t<td>";
						if (trim($s1row['description'])!='') {$surveysummary .= "$setfont {$s1row['description']}</font>";}
		$surveysummary .= "</td></tr>\n"
						. "\t<tr $showstyle id='surveydetails3'>\n"
						. "\t\t<td align='right' valign='top'>$setfont<strong>"
						. _SL_WELCOME."</strong></font></td>\n"
						. "\t\t<td>$setfont {$s1row['welcome']}</font></td></tr>\n"
						. "\t<tr $showstyle id='surveydetails4'><td align='right' valign='top'>$setfont<strong>"
						. _SL_ADMIN."</strong></font></td>\n"
						. "\t\t<td>$setfont {$s1row['admin']} ({$s1row['adminemail']})</font></td></tr>\n"
						. "\t<tr $showstyle id='surveydetails5'><td align='right' valign='top'>$setfont<strong>"
						. _SL_FAXTO."</strong></font></td>\n\t\t<td>";
						if (trim($s1row['faxto'])!='') {$surveysummary .= "$setfont {$s1row['faxto']}</font>";}
		$surveysummary .= "</td></tr>\n"
						. "\t<tr $showstyle id='surveydetails6'><td align='right' valign='top'>$setfont<strong>"
						. _SL_EXPIRYDATE."</strong></font></td>\n";
		if ($s1row['useexpiry']== "Y") 
			{
			$expdate=$s1row['expires'];
			}
		else 
			{
			$expdate="-";
			}
		$surveysummary .= "\t<td>$setfont$expdate</font></td></tr>\n"
						. "\t<tr $showstyle id='surveydetails7'><td align='right' valign='top'>$setfont<strong>"
						. _SL_TEMPLATE."</strong></font></td>\n"
						. "\t\t<td>$setfont {$s1row['template']}</font></td></tr>\n"
						. "\t<tr $showstyle id='surveydetails8'><td align='right' valign='top'>$setfont<strong>"
						. _SL_LANGUAGE."</strong></font></td>\n";
		if (!$s1row['language']) {$language=$defaultlang;} else {$language=$s1row['language'];}
		if ($s1row['urldescrip']==""){$s1row['urldescrip']=$s1row['url'];}
		$surveysummary .= "\t\t<td>$setfont$language</font></td></tr>\n"
						. "\t<tr $showstyle id='surveydetails9'><td align='right' valign='top'>$setfont<strong>"
						. _SL_LINK."</strong></font></td>\n"
						. "\t\t<td>";
		if ($s1row['url']!="") {$surveysummary .="$setfont <a href=\"{$s1row['url']}\" title=\"{$s1row['url']}\">{$s1row['urldescrip']}</a></font>";}
		$surveysummary .="</td></tr>\n";
    } 	
	$surveysummary .= "\t<tr $showstyle id='surveydetails10'><td align='right' valign='top'>$setfont<strong>"
					. _SL_STATUS."</strong></font></td>\n"
					. "\t<td valign='top'>$setfont"
					. "<font size='1'>"._SS_NOGROUPS." $sumcount2<br />\n"
					. _SS_NOQUESTS." $sumcount3<br />\n";
	if ($activated == "N" && $sumcount3 > 0)
		{
		$surveysummary .= _SS_NOTACTIVE."<br />\n";
		}
	elseif ($activated == "Y")
		{
		$surveysummary .= _SS_ACTIVE."<br />\n"
						. _SS_SURVEYTABLE." 'survey_$surveyid'<br />";
		}
	else
		{
		$surveysummary .= _SS_CANNOTACTIVATE."<br />\n";
		if ($sumcount2 == 0) 
			{
			$surveysummary .= "\t<font color='green'>["._SS_ADDGROUPS."]</font><br />";
			}
		if ($sumcount3 == 0)
			{
			$surveysummary .= "\t<font color='green'>["._SS_ADDQUESTS."]</font>";
			}
		}
	$surveysummary .= "</font></font></td></tr>\n"
					. $surveysummary2
					. "</table>\n";
	}

if ($gid)
	{
	$sumquery4 = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid=$gid"; //Getting a count of questions for this survey
	$sumresult4 = mysql_query($sumquery4);
	$sumcount4 = mysql_num_rows($sumresult4);
	$grpquery ="SELECT * FROM {$dbprefix}groups WHERE gid=$gid ORDER BY group_name";
	$grpresult = mysql_query($grpquery);
	$groupsummary = "<table width='100%' align='center' bgcolor='#DDDDDD' border='0'>\n";
	while ($grow = mysql_fetch_array($grpresult))
		{
		$grow = array_map('htmlspecialchars', $grow);
		$groupsummary .= "\t<tr>\n"
					   . "\t\t<td colspan='2'>\n"
					   . "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
					   . "\t\t\t\t<tr bgcolor='#555555'><td colspan='2' height='4'>"
					   . "<font size='1' face='verdana' color='white'><strong>Group</strong> "
					   . "<font color='silver'>{$grow['group_name']}</font></font></td></tr>\n"
					   . "\t\t\t\t<tr bgcolor='#AAAAAA'>\n"
					   . "\t\t\t\t\t<td>\n"
					   . "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='31' height='20' border='0' hspace='0' align='left'>\n"
					   . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
					   . "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='60' height='20' border='0' hspace='0' align='left'>\n"
					   . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
					   . "\t\t\t\t\t<input type='image' src='$imagefiles/edit.gif' title='". _G_EDIT_BT."' alt='". _G_EDIT_BT."' name='EditGroup' "
					   . "align='left'  onclick=\"window.open('$scriptname?action=editgroup&amp;sid=$surveyid&amp;gid=$gid', "
					   . "'_top')\">";
		if (($sumcount4 == 0 && $activated != "Y") || $activated != "Y") 
			{
			$groupsummary .= "\t\t\t\t\t<a href='$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid'>"
						   . "<img src='$imagefiles/delete.gif' alt='"
						   . _G_DELETE_BT."' name='DeleteWholeGroup' title='"
						   . _G_DELETE_BT."' align='left' border='0' hspace='0' "
						   . "onclick=\"return confirm('"._DG_RUSURE."')\"></a>";
			}
		else				
			{
			$groupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' align='left' border='0' hspace='0'>\n";
			}
		$groupsummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/export.gif' title='"
					   . _G_EXPORT_BT."' alt='". _G_EXPORT_BT."'name='ExportGroup' "
					   . "align='left' "
					   . "onclick=\"window.open('dumpgroup.php?sid=$surveyid&amp;gid=$gid', '_top')\">"
					   . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
					   . "\t\t\t\t\t</td>\n"
					   . "\t\t\t\t\t<td align='right' width='350'>\n";
		if (!$qid) 
			{
			$groupsummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' title='"
						   . _G_CLOSE_BT."' alt='". _G_CLOSE_BT."' align='right'  name='CloseSurveyWindow' "
						   . "onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\">\n";
			}
		else 
			{
			$groupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='21' align='right' border='0' hspace='0'>\n";
			}
		$groupsummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/plus.gif' title='"
					   . _G_MAXIMISE_BT."' alt='". _G_MAXIMISE_BT."' name='MaximiseGroupWindow' "
					   . "align='right'  onclick='showdetails(\"showg\")'>"
					   . "\t\t\t\t\t<input type='image' src='$imagefiles/minus.gif' title='"
					   . _G_MINIMISE_BT."' alt='". _G_MINIMISE_BT."' name='MinimiseGroupWindow' "
					   . "align='right'  onclick='showdetails(\"hideg\")'>\n"
					   . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='right' border='0' hspace='0'>\n";
		if ($activated == "Y")
			{
			$groupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' border='0' hspace='0' align='right'>\n";
			}
		else
			{
			$groupsummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/add.gif' title='"
						   . _G_ADDQUESTION_BT."'  alt='". _G_ADDQUESTION_BT."' align='right' name='AddNewQuestion' "
						   . "onClick=\"window.open('$scriptname?action=addquestion&amp;sid=$surveyid&amp;gid=$gid', '_top')\">\n";
			}
		$groupsummary .= "\t\t\t\t\t$setfont<font size='1'><strong>"._QUESTIONS.":</strong> <select name='qid' "
					   . "onChange=\"window.open(this.options[this.selectedIndex].value, '_top')\" "
					   . "style='font-size:9; font-family: verdana; color: #333333; background-color: "
					   . "silver; width: 160'>\n"
					   . getquestions()
					   . "\t\t\t\t\t</select>\n"
					   . "\t\t\t\t</font></font></td></tr>\n"
					   . "\t\t\t</table>\n"
					   . "\t\t</td>\n"
					   . "\t</tr>\n";
		if ($qid) {$gshowstyle="style='display: none'";}
		else	  {$gshowstyle="";}

		$groupsummary .= "\t<tr $gshowstyle id='surveydetails20'><td width='20%' align='right'>$setfont<strong>"
					   . _GL_TITLE."</strong></font></td>\n"
					   . "\t<td>"
					   . "$setfont{$grow['group_name']} ({$grow['gid']})</font></td></tr>\n"
					   . "\t<tr $gshowstyle id='surveydetails21'><td valign='top' align='right'>$setfont<strong>"
					   . _GL_DESCRIPTION."</strong></font></td>\n\t<td>";
					   if (trim($grow['description'])!='') {$groupsummary .="$setfont{$grow['description']}</font>";}
		$groupsummary .= "</td></tr>\n";
		}
	$groupsummary .= "\n</table>\n";
	}

if ($qid)
	{
	//Show Question Details
	$qrq = "SELECT * FROM {$dbprefix}answers WHERE qid=$qid ORDER BY sortorder, answer";
	$qrr = mysql_query($qrq);
	$qct = mysql_num_rows($qrr);
	$qrquery = "SELECT * FROM {$dbprefix}questions WHERE gid=$gid AND sid=$surveyid AND qid=$qid";
	$qrresult = mysql_query($qrquery) or die($qrquery."<br />".mysql_error());
	$questionsummary = "<table width='100%' align='center' bgcolor='#EEEEEE' border='0'>\n";
	while ($qrrow = mysql_fetch_array($qrresult))
		{
		$qrrow = array_map('htmlspecialchars', $qrrow);
		$questionsummary .= "\t<tr>\n"
						  . "\t\t<td colspan='2'>\n"
						  . "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
						  . "\t\t\t\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
						  . _QUESTION."</strong> <font color='silver'>{$qrrow['question']}</font></font></td></tr>\n"
						  . "\t\t\t\t<tr bgcolor='#AAAAAA'>\n"
						  . "\t\t\t\t\t<td>\n"
						  . "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='31' height='20' border='0' hspace='0' align='left'>\n"
						  . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
						  . "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='60' height='20' border='0' hspace='0' align='left'>\n"
						  . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
						  . "\t\t\t\t\t<input type='image' src='$imagefiles/edit.gif' title='". _Q_EDIT_BT."' alt='". _Q_EDIT_BT."' align='left' name='EditQuestion' "
						  . "onclick=\"window.open('$scriptname?action=editquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid', '_top')\">\n";
		if (($qct == 0 && $activated != "Y") || $activated != "Y") 
			{
			$questionsummary .= "\t\t\t\t\t<a href='$scriptname?action=delquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'>"
							  . "<img src='$imagefiles/delete.gif' name='DeleteWholeQuestion' alt= '"._Q_DELETE_BT."' title='"
							  . _Q_DELETE_BT."' align='left' border='0' hspace='0' "
							  . "onclick=\"return confirm('"._DQ_RUSURE."')\"></a>\n";
			}
		else {$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' align='left' border='0' hspace='0'>\n";}
		$questionsummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/export.gif' title='"
						  . _Q_EXPORT_BT."' alt='". _Q_EXPORT_BT."'align='left' name='ExportQuestion' "
						  . "onclick=\"window.open('dumpquestion.php?qid=$qid', '_top')\">\n"
						  . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
						  . "\t\t\t\t\t<input type='image' src='$imagefiles/copy.gif' title='"
						  . _Q_COPY_BT."' alt='". _Q_COPY_BT."' align='left' name='CopyQuestion' "
						  . "onclick=\"window.open('$scriptname?action=copyquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid', '_top')\">\n"
						  . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
						  . "\t\t\t\t\t<input type='image' src='$imagefiles/conditions.gif' title='"
						  . _Q_CONDITIONS_BT."' alt='". _Q_CONDITIONS_BT."' align='left' name='SetQuestionConditions' "
						  . "onClick=\"window.open('".$homeurl."/conditions.php?sid=$surveyid&amp;qid=$qid', 'conditions', 'menubar=no, location=no, status=no, height=475, width=560, scrollbars=yes, resizable=yes, left=50, top=50')\">\n"
						  . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n";
		if ($qrrow['type'] == "O" || $qrrow['type'] == "L" || $qrrow['type'] == "!" || $qrrow['type'] == "!" || $qrrow['type'] == "M" || $qrrow['type'] == "Q" || $qrrow['type']=="A" || $qrrow['type'] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "E" || $qrrow['type'] == "F" || $qrrow['type'] == "H" || $qrrow['type'] == "P" || $qrrow['type'] == "R") 
			{
			$questionsummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/answers.gif' title='"
							  . _Q_ANSWERS_BT."' align='left' name='ViewAnswers' "
							  . "onClick=\"window.open('".$scriptname."?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;viewanswer=Y', '_top')\">\n";
			}
		$questionsummary .= "\t\t\t\t\t</td>\n"
						  . "\t\t\t\t\t<td align='right' width='330'>\n"
						  . "\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' title='"
						  . _Q_CLOSE_BT."' alt='". _Q_CLOSE_BT."' align='right' name='CloseQuestionWindow' "
						  . "onclick=\"window.open('$scriptname?sid=$surveyid&amp;gid=$gid', '_top')\">\n"
						  . "\t\t\t\t\t<input type='image' src='$imagefiles/plus.gif' title='"
						  . _Q_MAXIMISE_BT."'  alt='". _Q_MAXIMISE_BT."'align='right'  name='MaximiseQuestionWindow' "
						  . "onclick='showdetails(\"showq\")'>"
						  . "\t\t\t\t\t<input type='image' src='$imagefiles/minus.gif' title='"
						  . _Q_MINIMISE_BT."'  alt='". _Q_MINIMISE_BT."'align='right'  name='MinimiseQuestionWindow' "
						  . "onclick='showdetails(\"hideq\")'>\n"
						  . "\t\t\t\t</td></tr>\n"
						  . "\t\t\t</table>\n"
						  . "\t\t</td>\n"
						  . "\t</tr>\n";
		if (returnglobal('viewanswer'))	{$qshowstyle = "style='display: none'";}
		else							{$qshowstyle = "";}
		$questionsummary .= "\t<tr $qshowstyle id='surveydetails30'><td width='20%' align='right'>$setfont<strong>"
						  . _QL_CODE."</strong></font></td>\n"
						  . "\t<td>$setfont{$qrrow['title']}";
		if ($qrrow['mandatory'] == "Y") {$questionsummary .= ": (<i>"._QS_MANDATORY."</i>)";}
		else {$questionsummary .= ": (<i>"._QS_OPTIONAL."</i>)";}
		$questionsummary .= "</font></td></tr>\n"
						  . "\t<tr $qshowstyle id='surveydetails31'><td align='right' valign='top'>$setfont<strong>"
						  . _QL_QUESTION."</strong></font></td>\n\t<td>$setfont{$qrrow['question']}</font></td></tr>\n"
						  . "\t<tr $qshowstyle id='surveydetails32'><td align='right' valign='top'>$setfont<strong>"
						  . _QL_HELP."</strong></font></td>\n\t<td>";
						  if (trim($qrrow['help'])!=''){$questionsummary .= "$setfont{$qrrow['help']}</font>";}
		$questionsummary .= "</td></tr>\n";
		if ($qrrow['preg'])
			{
		    $questionsummary .= "\t<tr $qshowstyle id='surveydetails33'><td align='right' valign='top'>$setfont<strong>"
							  . _QL_VALIDATION."</strong></font></td>\n\t<td>$setfont{$qrrow['preg']}"
							  . "</font></td></tr>\n";
			}
		$qtypes = getqtypelist("", "array"); //qtypes = array(type code=>type description)
		$questionsummary .= "\t<tr $qshowstyle id='surveydetails34'><td align='right' valign='top'>$setfont<strong>"
						  ._QL_TYPE."</strong></font></td>\n\t<td>$setfont{$qtypes[$qrrow['type']]}";
		if ($qrrow['type'] == "F" ||$qrrow['type'] == "H") 
			{
			$questionsummary .= " (LID: {$qrrow['lid']}) "
							  . "<input align='top' type='image' src='$imagefiles/labels.gif' title='"
							  . _Q_LABELS_BT."' height='15' width='15' hspace='0' name='EditThisLabelSet' "
							  . "onClick=\"window.open('labels.php?lid={$qrrow['lid']}', '_blank')\">\n";
			}
		$questionsummary .="</font></td></tr>\n";
		if ($qct == 0 && ($qrrow['type'] == "O" || $qrrow['type'] == "L" || $qrrow['type'] == "!" || $qrrow['type'] == "M" || $qrrow['type'] == "Q" || $qrrow['type'] == "A" || $qrrow['type'] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "E" || $qrrow['type'] == "P" || $qrrow['type'] == "R" || $qrrow['type'] == "F" ||$qrrow['type'] == "H"))
			{
			$questionsummary .= "\t\t<tr $qshowstyle id='surveydetails35'><td></td><td>"
							 . "<font face='verdana' size='1' color='green'>"
							 . _WARNING.": ". _QS_NOANSWERS." "
							 . "<input type='image' src='$imagefiles/answers.gif' title='"
							 . _Q_ANSWERS_BT."' border='0' hspace='0' name='EditThisQuestionAnswers'"
							 . "onClick=\"window.open('".$scriptname."?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;viewanswer=Y', '_top')\"></font></td></tr>\n";
			}
		if (!$qrrow['lid'] && ($qrrow['type'] == "F" ||$qrrow['type'] == "H"))
			{
			$questionsummary .= "\t\t<tr $qshowstyle id='surveydetails36'><td></td>"
							  . "<td><font face='verdana' size='1' color='green'>"
							  . _WARNING.": "._QS_NOLID."</font></td></tr>\n";
			}
		if ($qrrow['type'] == "M" or $qrrow['type'] == "P")
			{
			$questionsummary .= "\t<tr $qshowstyle id='surveydetails37'>"
							  . "<td align='right' valign='top'>$setfont<strong>"
							  . _QL_OTHER."</strong></font></td>\n"
							  . "\t<td>$setfont{$qrrow['other']}</td></tr>\n";
			}
		if ($qrrow['type'] == "J" || $qrrow['type'] == "I")
			{
			if ($action == "insertCSV")
				{
				$questionsummary .= "\t\t<tr $qshowstyle id='surveydetails37'><td></td><td>"
							 . "<font face='verdana' size='2' color='green'><b>
							 ". _UPLOADCOMP."</font></b></td></tr>\n";
				}
			   elseif ($action == "editquestion" || $action == "copyquestion")
				{
				$questionsummary .= " ";
				}
			  elseif ($action == "insertnewquestion" || ($action == "updatequestion" && $change == "0"))
				{
				upload();
				}
			}
		$qid_attributes=getQuestionAttributes($qid);
		}
	$questionsummary .= "</table>\n";
	}

if (returnglobal('viewanswer'))
	{
	echo keycontroljs();
	$qquery = "SELECT type FROM {$dbprefix}questions WHERE qid=$qid";
	$qresult = mysql_query($qquery);
	while ($qrow=mysql_fetch_array($qresult)) {$qtype=$qrow['type'];}
	if (!isset($_POST['ansaction']))
		{
		//check if any nulls exist. If they do, redo the sortorders
		$caquery="SELECT * FROM {$dbprefix}answers WHERE qid=$qid AND sortorder is null";
		$caresult=mysql_query($caquery);
		$cacount=mysql_num_rows($caresult);
		if ($cacount)
			{
			fixsortorder($qid);
			}
		}
	$vasummary  = "<table width='100%' align='center' border='0' bgcolor='#EEEEEE'>\n"
				. "<tr bgcolor='#555555'><td colspan='5'><font size='1' color='white'><strong>"
				. _ANSWERS."</strong></font></td></tr>\n";
	$cdquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$qid ORDER BY sortorder, answer";
	$cdresult = mysql_query($cdquery);
	$cdcount = mysql_num_rows($cdresult);
	$vasummary .= "\t<tr><th width='10%'>$setfont"._AL_CODE."</font></th><th width='50%'>$setfont"._AL_ANSWER."</font></th>"
				. "<th width='10%'>$setfont"._AL_DEFAULT."</font></th><th width='15%'>$setfont"._AL_ACTION."</font></th>"
				. "<th>$setfont"._AL_MOVE."</font></th></tr>\n";
	$position=0;
	while ($cdrow = mysql_fetch_array($cdresult))
		{
		$cdrow['code'] = htmlspecialchars($cdrow['code']);
		$position=sprintf("%05d", $position);
		if ($cdrow['sortorder'] || $cdrow['sortorder'] == "0") {$position=$cdrow['sortorder'];}
		$vasummary .= "\t<tr><td colspan='5'><form style='margin-bottom:0;' action='".$scriptname."' method='post'>\n";
		$vasummary .= "\t<table width='100%' cellspacing='0' cellpadding='0'><tr><td align='center' width='10%'>";
		if (($activated == "Y" && ($qtype == "L" || $qtype == "!")) || ($activated == "N"))
			{
			$vasummary .="<input name='code' type='text' $btstyle value=\"{$cdrow['code']}\" maxlength='5' size='5' "
						."onKeyPress=\"return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_-')\""
						.">";
			}
		else
			{
			$vasummary .= "$setfont<font size='1'>{$cdrow['code']}"
						. "<input type='hidden' name='code' value=\"{$cdrow['code']}\">";
			}
		$vasummary .="</td>\n";
		$cdrow['answer']=htmlspecialchars($cdrow['answer']);  // So HTML-Code can be used in answers
		$cdrow['default_value'] = htmlspecialchars($cdrow['default_value']);
		$vasummary .= "\t\t<td align='center' width='50%'><input name='answer' "
					. "type='text' $btstyle value=\"{$cdrow['answer']}\" size='50'>\n"
					. "\t\t<input name='sortorder' type='hidden' $btstyle value=\"$position\"></td>"
					. "\t\t<td align='center' width='10%'>";
		if (($activated == "Y" && ($qtype == "L" || $qtype =="!")) || ($activated == "N"))
			{
			$vasummary .= "\t\t\t<select name='default' $btstyle>\n"
						. "\t\t\t\t<option value='Y'";
			if ($cdrow['default_value'] == "Y") {$vasummary .= " selected";};
			$vasummary .= ">"._AD_YES."</option>\n"
						. "\t\t\t\t<option value='N'";
			if ($cdrow['default_value'] != "Y") {$vasummary .= " selected";};
			$vasummary .= ">"._AD_NO."</option>\n"
						. "\t\t\t</select></td>\n";
			}
		else
			{
			$vasummary .= "$setfont<font size='1'>{$cdrow['default_value']}"
						. "<input type='hidden' name='default' value=\"{$cdrow['default_value']}\">";
			}
		if (($activated == "Y" && ($qtype == "L" || $qtype == "!")) || ($activated == "N"))
			{
			$vasummary .= "\t\t<td align='center' width='15%'>\n"
						. "\t\t\t<input name='ansaction' $btstyle type='submit' value='"._AL_SAVE."'>"
						. "<input name='ansaction' $btstyle type='submit' value='"._AL_DEL."'>\n"
						. "\t\t</td>\n";
			}
		else
			{
			$vasummary .= "\t\t<td align='center' width='15%'><input name='ansaction' "
						. "$btstyle type='submit' value='"._AL_SAVE."'></td>\n";
			}
		$vasummary .= "\t\t<td align='center'>";
		if ($position > 0) {$vasummary .= "<input name='ansaction' $btstyle type='submit' value='"._AL_UP."'>";}
		else {$vasummary .= "&nbsp;&nbsp;&nbsp;&nbsp;";}
		if ($position < $cdcount-1) {$vasummary .= "<input name='ansaction' $btstyle type='submit' value='"._AL_DN."'>";}
		else {$vasummary .= "&nbsp;&nbsp;&nbsp;&nbsp;";}
		$vasummary .= "\t\t\n";
		$vasummary .= "\t<input type='hidden' name='oldcode' value=\"{$cdrow['code']}\">\n"
					. "\t<input type='hidden' name='oldanswer' value=\"{$cdrow['answer']}\">\n"
					. "\t<input type='hidden' name='olddefault' value=\"{$cdrow['default_value']}\">\n"
					. "\t<input type='hidden' name='sid' value='$surveyid'>\n"
					. "\t<input type='hidden' name='gid' value='$gid'>\n"
					. "\t<input type='hidden' name='qid' value='$qid'>\n"
					. "\t<input type='hidden' name='viewanswer' value='Y'>\n"
					. "\t<input type='hidden' name='action' value='modanswer'>\n"
					. "\t</td></table></form></tr>\n";
		$position++;
		}
	if (($activated == "Y" && ($qtype == "L" || $qtype == "!")) || ($activated == "N"))
		{
		$position=sprintf("%05d", $position);
		$vasummary .= "\t<tr><td colspan='5'><form style='margin-bottom:0;' action='".$scriptname."' method='post'>\n"
					. "\t<table width='100%'><tr><td align='center' width='10%'><input name='code' type='text' $btstyle size='5' maxlength='5' "
					. "id='addanswercode' "
					. "onKeyPress=\"return goodchars(event,'1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWZYZ_-')\">"
					. "</td>\n"
					. "\t\t<td align='center' width='50%'><input name='answer' type='text' $btstyle size='50'>\n"
					. "\t\t<input name='sortorder' type='hidden' $btstyle value='$position'></td>\n"
					. "\t\t<td align='center' width='10%'>"
					. "\t\t\t<select name='default' $btstyle>\n"
					. "\t\t\t\t<option value='Y'>"._AD_YES."</option>\n"
					. "\t\t\t\t<option value='N' selected>"._AD_NO."</option>\n"
					. "\t\t\t</select></td>\n"
					. "\t\t<td align='center' width='15%'><input name='ansaction' $btstyle type='submit' value='"._AL_ADD."'></td>\n"
					. "\t\t<td>\n"
					. "\t<input type='hidden' name='sid' value='$surveyid'>\n"
					. "\t<input type='hidden' name='gid' value='$gid'>\n"
					. "\t<input type='hidden' name='qid' value='$qid'>\n"
					. "\t<input type='hidden' name='action' value='modanswer'>\n"
					. "\t<input type='hidden' name='viewanswer' value='Y'>\n";
		$vasummary .= "<script type='text/javascript' language='javascript'>\n"
					. "<!--\n"
					. "document.getElementById('addanswercode').focus();\n"
					. "//-->\n"
					. "</script>\n"
					. "\t</td></table></form></tr>\n";
		}
	if ($cdcount > 0)
		{
		$vasummary .= "<tr><td colspan='3'></td><td align='center'>"
					. "<form style='margin-bottom:0;' action='".$scriptname."' method='post'>"
					. "<input $btstyle type='submit' name='ansaction' value='"._AL_SORTALPHA."'>\n"
					. "\t<input type='hidden' name='sid' value='$surveyid'>\n"
					. "\t<input type='hidden' name='gid' value='$gid'>\n"
					. "\t<input type='hidden' name='qid' value='$qid'>\n"
					. "\t<input type='hidden' name='action' value='modanswer'>\n"
					. "\t<input type='hidden' name='viewanswer' value='Y'></form>\n</td>"
					. "\t<td align='center'>\n"
					. "\t<form style='margin-bottom:0;' action='".$scriptname."' method='post'>"
					. "<input $btstyle type='submit' name='ansaction' value='"._AL_FIXSORT."'>\n"
					. "\t<input type='hidden' name='sid' value='$surveyid'>\n"
					. "\t<input type='hidden' name='gid' value='$gid'>\n"
					. "\t<input type='hidden' name='qid' value='$qid'>\n"
					. "\t<input type='hidden' name='action' value='modanswer'>\n"
					. "\t<input type='hidden' name='viewanswer' value='Y'>\n"
					. "</form></td>\n";
		}

	$vasummary .= "</table>\n";
	}
	
if ($action == "setupsecurity")
	{
	$action = "setup";
	include("usercontrol.php");
	}

if ($action == "turnoffsecurity")
	{
	$action = "deleteall";
	include("usercontrol.php");
	}
	
if ($action == "adduser" || $action=="deluser" || $action == "moduser")
	{
	include("usercontrol.php");
	}

if ($action == "modifyuser")
	{
	$usersummary = "<table width='100%' border='0'>\n\t<tr><td colspan='3' bgcolor='black' align='center'>\n"
				 . "\t\t<strong>$setfont<font color='white'>Modify User</td></tr>\n";
	$muq = "SELECT * FROM {$dbprefix}users WHERE user='$user' LIMIT 1";
	$mur = mysql_query($muq);
	$usersummary .= "\t<tr><form action='$scriptname' method='post'>";
	while ($mrw = mysql_fetch_array($mur))
		{
		$mrw = array_map('htmlspecialchars', $mrw);
		$usersummary .= "\t<td>$setfont<strong>{$mrw['user']}</strong></font>\n"
					  . "\t\t<input type='hidden' name='user' value=\"{$mrw['user']}\"></td>\n"
					  . "\t<td>\n\t\t<input $slstyle type='text' name='pass' value=\"{$mrw['password']}\"></td>\n"
					  . "\t<td>\n\t\t<input $slstyle type='text' size='2' name='level' value=\"{$mrw['security']}\"></td>\n";
		}
	$usersummary .= "\t</tr>\n\t<tr><td colspan='3' align='center'>\n"
				  . "\t\t<input type='submit' $btstyle value='"._UPDATE."'>\n"
				  . "<input type='hidden' name='action' value='moduser'></td></tr>\n"
				  . "</form></table>\n";
	}

if ($action == "editusers")
	{
	if (!file_exists("$homedir/.htaccess"))
		{
		$usersummary = "<table width='100%' border='0'><tr><td colspan='2'>\n"
					 . "<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
					 . "\t\t\t\t<tr bgcolor='#555555'><td colspan='4' height='4'>"
					 . "<font size='1' face='verdana' color='white'><strong>"._USERCONTROL."</strong></font></td></tr>\n"
					 . "\t<tr>\n"
					 . "\t\t<td>\n"
					 . "\t\t\t$setfont<font color='RED'><strong>"._WARNING."</strong></font></font><br />\n"
					 . "\t\t\t"._UC_TURNON_MESSAGE1."\n"
					 . "\t\t\t<p>"._USERNAME.": $defaultuser<br />"._PASSWORD.": $defaultpass</p>\n"
					 . "\t\t\t<p>"._UC_TURNON_MESSAGE2."</p>\n"
					 . "\t\t</td>\n"
					 . "\t</tr>\n"
					 . "\t<tr>\n"
					 . "\t\t<td align='center'>\n"
					 . "\t\t\t<input type='submit' $btstyle value='"._UC_INITIALISE."' "
					 . "onClick=\"window.open('$scriptname?action=setupsecurity', '_top')\">\n"
					 . "\t\t</td>\n"
					 . "\t</tr>\n"
					 . "</table>\n"
					 . "</td></tr></table>\n";
		}
	else
		{
		$usersummary = "<table width='100%' border='0'><tr><td colspan='2'>\n"
					 . "<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
					 . "\t\t\t\t<tr bgcolor='#555555'><td colspan='4' height='4'>"
					 . "<font size='1' face='verdana' color='white'><strong>"._USERCONTROL."</strong></td></tr>\n"
					 . "\t<tr>\n"
					 . "\t\t<th>$setfont"._UL_USER."</th>\n"
					 . "\t\t<th>$setfont"._UL_PASSWORD."</font></th>\n"
					 . "\t\t<th>$setfont"._UL_SECURITY."</font></th>\n"
					 . "\t\t<th>$setfont"._UL_ACTION."</font></th>\n"
					 . "\t</tr>\n";
		$userlist = getuserlist();
		$ui = count($userlist);
		if ($ui < 1)
			{
			$usersummary .= "\t<tr>\n"
						 . "\t\t<td>\n"
						 . "\t\t\t<center>"._WARNING.": "._UC_NOUSERS."</center>"
						 . "\t\t</td>\n"
						 . "\t</tr>\n";
			}
		else
			{
			foreach ($userlist as $usr)
				{
				$usersummary .= "\t<tr>\n"
							  . "\t<td align='center'>$setfont{$usr['user']}</font></td>\n"
							  . "\t\t<td align='center'>$setfont{$usr['password']}</font></td>\n"
							  . "\t\t<td align='center'>$setfont{$usr['security']}</td>\n"
							  . "\t\t<td align='center'>\n"
							  . "\t\t\t<input type='submit' $btstyle value='"._UL_EDIT."' "
							  . "onClick=\"window.open('$scriptname?action=modifyuser&user={$usr['user']}', '_top')\" />\n";
				if ($ui > 1 )
					{
					$usersummary .= "\t\t\t<input type='submit' $btstyle value='"._UL_DEL."' "
								  . "onClick=\"window.open('$scriptname?action=deluser&user={$usr['user']}', '_top')\" />\n";
					}
				$usersummary .= "\t\t</td>\n"
							  . "\t</tr>\n";
				$ui++;
				}
			}
		$usersummary .= "\t\t<form action='$scriptname' method='post'>\n"
					  . "\t\t<tr>\n"
					  . "\t\t<td align='center'><input type='text' $slstyle name='user'></td>\n"
					  . "\t\t<td align='center'><input type='text' $slstyle name='pass'></td>\n"
					  . "\t\t<td align='center'><input type='text' $slstyle name='level' size='2'></td>\n"
					  . "\t\t<td align='center'><input type='submit' $btstyle value='"._ADDU."'></td>\n"
					  . "\t</tr>\n"
					  . "\t<tr>\n"
					  . "\t\t<td align='center'><input type='hidden' name='action' value='adduser'></td>\n"
					  . "\t</tr>\n"
					  . "\t</form>\n"
					  . "\t<tr>\n"
					  . "\t\t<td colspan='3'></td>\n"
					  . "\t\t<td align='center'><input type='submit' $btstyle value='"._UC_TURNOFF."' "
					  . "onClick=\"window.open('$scriptname?action=turnoffsecurity', '_top')\" /></td>\n"
					  . "\t</tr>\n"
					  . "</table>\n"
					  . "</td></tr></table>\n";
		}
	}
if ($action == "addquestion")
	{
	$newquestion =  "\t<form action='$scriptname' name='addnewquestion' method='post'>\n"
				  . "<table width='100%' border='0'>\n\n"
				  . "\t<tr>\n"
				  . "\t\t<td colspan='2' bgcolor='black' align='center'>"
				  . "\t\t<strong>$setfont<font color='white'>"._ADDQ."\n"
				  . "\t\t</font></font></strong></td>\n"
				  . "\t</tr>\n"
				  . "\t<tr>\n"
				  . "\t\t<td align='right'  width='35%'>$setfont<strong>"._QL_CODE."</strong></font></td>\n"
				  . "\t\t<td><input $slstyle type='text' size='20' name='title'>"
				  . "<font color='red' face='verdana' size='1'>"._REQ."</font></td></tr>\n"
				  . "\t<tr>\n"
				  . "\t\t<td align='right' width='35%'>$setfont<strong>"._QL_QUESTION."</strong></font></td>\n"
				  . "\t\t<td><textarea $slstyle2 cols='50' rows='3' name='question'></textarea></td>\n"
				  . "\t</tr>\n"
				  . "\t<tr>\n"
				  . "\t\t<td align='right' width='35%'>$setfont<strong>"._QL_HELP."</strong></font></td>\n"
				  . "\t\t<td><textarea $slstyle2 cols='50' rows='3' name='help'></textarea></td>\n"
				  . "\t</tr>\n"
				  . "\t<tr>\n"
				  . "\t\t<td align='right' width='35%'>$setfont<strong>"._QL_TYPE."</strong></font></td>\n"
				  . "\t\t<td><select $slstyle name='type' id='question_type' "
				  . "onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n"
				  . "$qtypeselect"
				  . "\t\t</select></td>\n"
				  . "\t</tr>\n";

	$newquestion .= "\t<tr id='Validation'>\n"
				  . "\t\t<td align='right'>$setfont<strong>"._QL_VALIDATION."</strong></font></td>\n"
				  . "\t\t<td>$setfont\n"
				  . "\t\t<input type='text' $slstyle name='preg' size=50></font>\n"
				  . "\t\t</td>\n"
				  . "\t</tr>\n";
	
	$newquestion .= "\t<tr id='LabelSets' style='display: none'>\n"
				  . "\t\t<td align='right'>$setfont<strong>"._QL_LABELSET."</strong></font></td>\n"
				  . "\t\t<td>$setfont\n"
				  . "\t\t<select name='lid' $slstyle>\n";
	$labelsets=getlabelsets();
	if (count($labelsets)>0)
		{
		$newquestion .= "\t\t\t<option value=''>"._AD_CHOOSE."</option>\n";
		foreach ($labelsets as $lb)
			{
			$newquestion .= "\t\t\t<option value='{$lb[0]}'>{$lb[1]}</option>\n";
			}
		}
	$newquestion .= "\t\t</select>\n"
				  . "\t\t</font></td>\n"
				  . "\t</tr>\n";
				  
	$newquestion .= "\t<tr id='OtherSelection' style='display: none'>\n"
				  . "\t\t<td align='right'>$setfont<strong>"._QL_OTHER."</strong></font></td>\n"
				  . "\t\t<td>$setfont\n"
				  . "\t\t\t<label for='OY'>"._AD_YES."</label>"
				  . "<input id='OY' type='radio' name='other' value='Y' />&nbsp;&nbsp;\n"
				  . "\t\t\t<label for='ON'>"._AD_NO."</label>"
				  . "<input id='ON' type='radio' name='other' value='N' checked />\n"
				  . "\t\t</font></td>\n"
				  . "\t</tr>\n";

	$newquestion .= "\t<tr id='MandatorySelection'>\n"
				  . "\t\t<td align='right'>$setfont<strong>"._QL_MANDATORY."</strong></font></td>\n"
				  . "\t\t<td>$setfont\n"
				  . "\t\t\t<label for='MY'>"._AD_YES."</label>"
				  . "<input id='MY' type='radio' name='mandatory' value='Y' />&nbsp;&nbsp;\n"
				  . "\t\t\t<label for='MN'>"._AD_NO."</label>"
				  . "<input id='MN' type='radio' name='mandatory' value='N' checked />\n"
				  . "\t\t</font></td>\n"
				  . "\t</tr>\n";
	
	//Question attributes
	$qattributes=questionAttributes();

	$newquestion .= "\t<tr id='QTattributes'>
						<td align='right'>{$setfont}<strong>"._QL_QUESTIONATTRIBUTES."</strong></font></td>
						<td><select id='QTlist' name='attribute_name' $slstyle>
						</select>
						<input type='text' id='QTtext' name='attribute_value' $slstyle></td></tr>\n";
	$newquestion .= "\t<tr>\n"
				  . "\t\t<td colspan='2' align='center'>";
	
	if (isset($eqrow)) {$newquestion .= questionjavascript($eqrow['type'], $qattributes);}
	else {$newquestion .= questionjavascript('', $qattributes);}

	$newquestion .= "<input type='submit' $btstyle value='"
				  . _ADDQ."' />\n"
				  . "\t\n"
				  . "\t<input type='hidden' name='action' value='insertnewquestion' />\n"
				  . "\t<input type='hidden' name='sid' value='$surveyid' />\n"
				  . "\t<input type='hidden' name='gid' value='$gid' />\n"
				  . "</td></tr></table>\n"
				  . "\t</form>\n"
				  . "\t<form enctype='multipart/form-data' name='importquestion' action='$scriptname' method='post'>\n"
				  . "<table width='100%' border='0' >\n\t"
				  . "<tr><td colspan='2' align='center'>$setfont<strong>"._AD_OR."</strong></font></td></tr>\n"
				  . "<tr><td colspan='2' bgcolor='black' align='center'>\n"
				  . "\t\t<strong>$setfont<font color='white'>"._IMPORTQUESTION."</font></font></strong></td></tr>\n\t<tr>"
				  . "\t\t<td align='right' width='35%'>$setfont<strong>"._SL_SELSQL."</strong></font></td>\n"
				  . "\t\t<td><input $slstyle name=\"the_file\" type=\"file\" size=\"50\"></td></tr>\n"
				  . "\t<tr><td colspan='2' align='center'><input type='submit' "
				  . "$btstyle value='"._IMPORTQUESTION."'>\n"
				  . "\t<input type='hidden' name='action' value='importquestion'>\n"
				  . "\t<input type='hidden' name='sid' value='$surveyid'>\n"
				  . "\t<input type='hidden' name='gid' value='$gid'>\n"
				  . "\t</td></tr></table></form>\n\n";
	}

if ($action == "copyquestion")
	{
	$eqquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid=$gid AND qid=$qid";
	$eqresult = mysql_query($eqquery);
	$qattributes=questionAttributes();
	while ($eqrow = mysql_fetch_array($eqresult))
		{
		$eqrow = array_map('htmlspecialchars', $eqrow);
		$editquestion = "<form action='$scriptname' name='editquestion' method='post'>\n<table width='100%' border='0'>\n"
					  . "\t<tr>\n"
					  . "\t\t<td colspan='2' bgcolor='black' align='center'>\n"
					  . "\t\t\t$setfont<font color='white'><strong>"._COPYQ."</strong><br />"._QS_COPYINFO."</font></font>\n"
					  . "\t\t</td>\n"
					  . "\t</tr>\n"
					  . "\t<tr>\n"
					  . "\t\t<td align='right'>$setfont<strong>"._QL_CODE."</strong></font></td>\n"
					  . "\t\t<td><input $slstyle type='text' size='20' name='title' value='' /></td>\n"
					  . "\t</tr>\n"
					  . "\t<tr>\n"
					  . "\t\t<td align='right' valign='top'>$setfont<strong>"._QL_QUESTION."</strong></font></td>\n"
					  . "\t\t<td><textarea $slstyle2 cols='50' rows='4' name='question'>{$eqrow['question']}</textarea></td>\n"
					  . "\t</tr>\n"
					  . "\t<tr>\n"
					  . "\t\t<td align='right' valign='top'>$setfont<strong>"._QL_HELP."</strong></font></td>\n"
					  . "\t\t<td><textarea $slstyle2 cols='50' rows='4' name='help'>{$eqrow['help']}</textarea></td>\n"
					  . "\t</tr>\n"
					  . "\t<tr>\n"
					  . "\t\t<td align='right'>$setfont<strong>"._QL_TYPE."</strong></font></td>\n"
					  . "\t\t<td><select $slstyle name='type' onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n"
					  . getqtypelist($eqrow['type'])
					  . "\t\t</select></td>\n"
					  . "\t</tr>\n";

		$editquestion .= "\t<tr id='Validation'>\n"
					  . "\t\t<td align='right'>$setfont<strong>"._QL_VALIDATION."</strong></font></td>\n"
					  . "\t\t<td>$setfont\n"
					  . "\t\t<input type='text' $slstyle name='preg' size=50 value=\"".$eqrow['preg']."\">\n"
					  . "\t\t</font></td>\n"
					  . "\t</tr>\n";

		$editquestion .= "\t<tr id='LabelSets' style='display: none'>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._QL_LABELSET."</strong></font></td>\n"
					   . "\t\t<td>$setfont\n"
					   . "\t\t<select name='lid' $slstyle>\n";
		$labelsets=getlabelsets();
		if (count($labelsets)>0)
			{
			if (!$eqrow['lid'])
				{
				$editquestion .= "\t\t\t<option value=''>"._AD_CHOOSE."</option>\n";
				}
			foreach ($labelsets as $lb)
				{
				$editquestion .= "\t\t\t<option value='{$lb[0]}'";
				if ($eqrow['lid'] == $lb[0]) {$editquestion .= " selected";}
				$editquestion .= ">{$lb[1]}</option>\n";
				}
			}
		$editquestion .= "\t\t</select>\n"
					   . "\t\t</font></td>\n"
					   . "\t</tr>\n"		
					   . "\t<tr>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._QL_GROUP."</strong></font></td>\n"
					   . "\t\t<td><select $slstyle name='gid'>\n"
					   . getgrouplist3($eqrow['gid'])
					   . "\t\t\t</select></td>\n"
					   . "\t</tr>\n";
		
		$editquestion .= "\t<tr id='OtherSelection' style='display: none'>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._QL_OTHER."</strong></font></td>\n";
		
		$editquestion .= "\t\t<td>$setfont\n"
					   . "\t\t\t"._AD_YES." <input type='radio' name='other' value='Y'";
		if ($eqrow['other'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n"
					   . "\t\t\t"._AD_NO." <input type='radio' name='other' value='N'";
		if ($eqrow['other'] == "N") {$editquestion .= " checked";}
		$editquestion .= " />\n"
					   . "\t\t</font></td>\n"
					   . "\t</tr>\n";

		$editquestion .= "\t<tr id='MandatorySelection'>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._QL_MANDATORY."</strong></font></td>\n"
					   . "\t\t<td>$setfont\n"
					   . "\t\t\t"._AD_YES." <input type='radio' name='mandatory' value='Y'";
		if ($eqrow['mandatory'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n"
					   . "\t\t\t"._AD_NO." <input type='radio' name='mandatory' value='N'";
		if ($eqrow['mandatory'] != "Y") {$editquestion .= " checked";}
		$editquestion .= " />\n"
					   . "\t\t</font></td>\n"
					   . "\t</tr>\n"
					   . "\t<tr>\n"
					   . "\t\t<td align='right'>";
		
		$editquestion .= questionjavascript($eqrow['type'], $qattributes);

		if ($eqrow['type'] == "J" || $eqrow['type'] == "I")	
			{
			$editquestion .= "\t<tr>\n"
						   . "\t\t<input type='hidden' name='copyanswers' value='Y'>\n" 	   
						   . "\t\t<td colspan='2' align='center'><input type='submit' $btstyle value='"._COPYQ."'></td>\n"
						   . "\t\t<input type='hidden' name='action' value='copynewquestion'>\n"
						   . "\t\t<input type='hidden' name='sid' value='$sid' />\n"
						   . "\t\t<input type='hidden' name='oldqid' value='$qid' />\n"
						   . "\t</form></tr>\n"
						   . "</table>\n";	
			}
		else 							   	
			{	
		
		$editquestion .= "$setfont<strong>"._QL_COPYANS."</strong></font></td>\n"
					   . "\t\t<td>$setfont<input type='checkbox' checked name='copyanswers' value='Y' />"
					   . "</font></td>\n"
					   . "\t</tr>\n"
					   . "\t<tr>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._QL_COPYATT."</strong></font></td>\n"
					   . "\t\t<td>$setfont<input type='checkbox' checked name='copyattributes' value='Y' />"
					   . "</font></td>\n"
					   . "\t</tr>\n"
					   . "\t<tr>\n"
					   . "\t\t<td colspan='2' align='center'><input type='submit' $btstyle value='"._COPYQ."'>\n"
					   . "\t\t<input type='hidden' name='action' value='copynewquestion'>\n"
					   . "\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
					   . "\t\t<input type='hidden' name='oldqid' value='$qid' />\n"
					   . "\t</td></tr>\n"
					   . "</table>\n</form>\n";
			}
		}
	}

if ($action == "editquestion" || $action == "editattribute" || $action == "delattribute" || $action == "addattribute")
	{
	$eqquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid=$gid AND qid=$qid";
	$eqresult = mysql_query($eqquery);
	while ($eqrow = mysql_fetch_array($eqresult))
		{
		$eqrow  = array_map('htmlspecialchars', $eqrow);
		$editquestion = "<tr><td>\n"
						."<table width='100%' border='0' bgcolor='#EEEEEE'><tr>"
					    . "<td colspan='3' bgcolor='black' align='center'>"
					    . "\t\t\t$setfont<font color='white'><strong>"._QL_EDITQUESTION." $qid</strong></font></font>\n"
					    . "\t\t</td>\n"
					    . "\t</tr>\n"
					    . "\t<tr>\n"
					    . "\t\t<td valign='top'><form action='$scriptname' name='editquestion' method='post'><table width='100%' border='0'>\n"
					    . "\t<tr>\n"
					    . "\t\t<td align='right'>$setfont<strong>"._QL_CODE."</strong></font></td>\n"
					    . "\t\t<td><input $slstyle type='text' size='20' name='title' value=\"{$eqrow['title']}\"></td>\n"
					    . "\t</tr>\n"
					    . "\t<tr>\n"
					    . "\t\t<td align='right' valign='top'>$setfont<strong>"._QL_QUESTION."</strong></font></td>\n"
					    . "\t\t<td><textarea $slstyle2 cols='50' rows='4' name='question'>{$eqrow['question']}</textarea></td>\n"
					    . "\t</tr>\n"
					    . "\t<tr>\n"
					    . "\t\t<td align='right' valign='top'>$setfont<strong>"._QL_HELP."</strong></font></td>\n"
					    . "\t\t<td><textarea $slstyle2 cols='50' rows='4' name='help'>{$eqrow['help']}</textarea></td>\n"
					    . "\t</tr>\n";
		//question type:
		$editquestion .= "\t<tr>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._QL_TYPE."</strong></font></td>\n";
		if ($activated != "Y")
			{
			$editquestion .= "\t\t<td><select $slstyle id='question_type' name='type' "
						   . "onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n"
						   . getqtypelist($eqrow['type'])
						   . "\t\t</select></td>\n";
			}
		else
			{
			$editquestion .= "\t\t<td>{$setfont}[{$eqrow['type']}] - "._DE_NOMODIFY." - "._SS_ACTIVE."\n"
						   . "\t\t\t<input type='hidden' name='type' id='question_type' value='{$eqrow['type']}'>\n"
						   . "\t\t</font></td>\n";
			}

		$editquestion .= "\t<tr id='Validation'>\n"
					  . "\t\t<td align='right'>$setfont<strong>"._QL_VALIDATION."</strong></font></td>\n"
					  . "\t\t<td>$setfont\n"
					  . "\t\t<input type='text' $slstyle name='preg' size=50 value=\"".$eqrow['preg']."\">\n"
					  . "\t\t</font></td>\n"
					  . "\t</tr>\n";

		$editquestion  .="\t<tr id='LabelSets' style='display: none'>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._QL_LABELSET."</strong></font></td>\n"
					   . "\t\t<td>$setfont\n";
		if ($activated != "Y")
			{
			$editquestion .= "\t\t<select name='lid' $slstyle>\n";
			$labelsets=getlabelsets();
			if (count($labelsets)>0)
				{
				if (!$eqrow['lid'])
					{
					$editquestion .= "\t\t\t<option value=''>"._AD_CHOOSE."</option>\n";
					}
				foreach ($labelsets as $lb)
					{
					$editquestion .= "\t\t\t<option value='{$lb[0]}'";
					if ($eqrow['lid'] == $lb[0]) {$editquestion .= " selected";}
					$editquestion .= ">{$lb[1]}</option>\n";
					}
				}
			$editquestion .= "\t\t</select>\n";
			}
		else
			{
			$editquestion .= "[{$eqrow['lid']}] - "._DE_NOMODIFY." - "._SS_ACTIVE."\n"
						   . "\t\t\t<input type='hidden' name='lid' value=\"{$eqrow['lid']}\">\n";
			}
		$editquestion .= "\t\t</font></td>\n"
					   . "\t</tr>\n"
					   . "\t<tr>\n"
					   . "\t<td align='right'>$setfont<strong>"._QL_GROUP."</strong></font></td>\n"
					   . "\t\t<td><select $slstyle name='gid'>\n"
					   . getgrouplist3($eqrow['gid'])
					   . "\t\t</select></td>\n"
					   . "\t</tr>\n";
		$editquestion .= "\t<tr id='OtherSelection'>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._QL_OTHER."</strong></font></td>\n";
		if ($activated != "Y") 
			{
			$editquestion .= "\t\t<td>$setfont\n"
						   . "\t\t\t<label for='OY'>"._AD_YES."</label><input id='OY' type='radio' name='other' value='Y'";
			if ($eqrow['other'] == "Y") {$editquestion .= " checked";}
			$editquestion .= " />&nbsp;&nbsp;\n"
						   . "\t\t\t<label for='ON'>"._AD_NO."</label><input id='ON' type='radio' name='other' value='N'";
			if ($eqrow['other'] == "N") {$editquestion .= " checked";}
			$editquestion .= " />\n"
						   . "\t\t</font></td>\n";
			}
		else
			{
			$editquestion .= "<td>$setfont [{$eqrow['other']}] - "._DE_NOMODIFY." - "._SS_ACTIVE."\n"
						   . "\t\t\t<input type='hidden' name='other' value=\"{$eqrow['other']}\"></font></td>\n";
			}
		$editquestion .= "\t</tr>\n";

		$editquestion .= "\t<tr id='MandatorySelection'>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._QL_MANDATORY."</strong></font></td>\n"
					   . "\t\t<td>$setfont\n"
					   . "\t\t\t<label for='MY'>"._AD_YES."</label><input id='MY' type='radio' name='mandatory' value='Y'";
		if ($eqrow['mandatory'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n"
					   . "\t\t\t<label for='MN'>"._AD_NO."</label><input id='MN' type='radio' name='mandatory' value='N'";
		if ($eqrow['mandatory'] != "Y") {$editquestion .= " checked";}
		$editquestion .= " />\n"
					   . "\t\t</font></td>\n"
					   . "\t</tr>\n";
		$qattributes=questionAttributes();
		
		$editquestion .= "\t<tr>\n"
					   . "\t\t<td colspan='2' align='center'>"
					   . "<input type='submit' $btstyle value='"._QL_UPDATEQUESTION."'>\n"
					   . "\t<input type='hidden' name='action' value='updatequestion'>\n"
					   . "\t<input type='hidden' name='sid' value='$surveyid'>\n"
					   . "\t<input type='hidden' name='qid' value='$qid'>\n"
					   . "\t</td></tr>\n"
					   . "</table></form></td><td>\n";
		}

	$qidattributes=getQuestionAttributes($qid);
	$editquestion .= "\t\t\t<td valign='top' width='35%'><table width='100%' border='0' cellspacing='0'>
					   <tr>
					    <td colspan='2' align='center'>
						  <form action='$scriptname' method='post'><table class='outlinetable' cellspacing='0' width='90%'>
						  <tr id='QTattributes'>
						    <th colspan='4'>{$setfont}"._QL_QUESTIONATTRIBUTES."</font></th>
   					      </tr>
						  <tr><th colspan='4' height='5'></th></tr>
                          <tr>  			  
						  <td nowrap width='50%' ><select id='QTlist' name='attribute_name' $slstyle>
						  </select></td><td align='center' width='20%'><input type='text' id='QTtext' size='6' name='attribute_value' $slstyle></td>
						  <td align='center'><input type='submit' value='"._ADD."' $btstyle>
						  <input type='hidden' name='action' value='addattribute'>
						  <input type='hidden' name='sid' value='$surveyid'>
					      <input type='hidden' name='qid' value='$qid'>
					      <input type='hidden' name='gid' value='$gid'></td></tr>
					      <tr><th colspan='4' height='10'></th></tr>\n";
	$editquestion .= "\t\t\t</table></form>\n";
	foreach ($qidattributes as $qa)
		{
	    $editquestion .= "\t\t\t<table class='outlinetable' width='90%' border='0' cellspacing='0'>"
					   ."<tr><td width='85%'>"
					   ."<form action='$scriptname' method='post'>"
					   ."<table width='100%'><tr><td width='65%'>"
					   .$qa['attribute']."</td>
					   <td align='center' width='25%'><input type='text' name='attribute_value' size='5' $slstyle value='"
					   .$qa['value']."' /></td>
					   <td ><input type='submit' $btstyle value='"
					   ._AL_SAVE."' />
					   <input type='hidden' name='action' value='editattribute'>\n
					   <input type='hidden' name='sid' value='$surveyid'>\n
					   <input type='hidden' name='gid' value='$gid'>\n
					   <input type='hidden' name='qid' value='$qid'>\n
					   <input type='hidden' name='qaid' value='".$qa['qaid']."'>\n"
					   ."\t\t\t</td></tr></table></form></td><td>
					   <form action='$scriptname' method='post'><table width='100%'><tr><td width='5%'>
					   <input type='submit' $btstyle value='"
					   ._DELETE."' />"
					   . "\t<input type='hidden' name='action' value='delattribute'>\n"
					   . "\t<input type='hidden' name='sid' value='$surveyid'>\n"
					   . "\t<input type='hidden' name='qid' value='$qid'>\n"
					   . "\t<input type='hidden' name='gid' value='$gid'>\n"
					   . "\t<input type='hidden' name='qaid' value='".$qa['qaid']."'>\n"
					   . "</td></tr></table>\n"
					   . "</form>\n</table>";
		}
	$editquestion .= "</td></tr></table></table>\n";
	$editquestion .= questionjavascript($eqrow['type'], $qattributes);
	}

if ($action == "addgroup")
	{
	$newgroup = "<tr><td><form action='$scriptname' name='addnewgroup' method='post'><table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n"
			   . "\t\t<strong>$setfont<font color='white'>"._ADDG."</font></font></strong></td></tr>\n"
			   . "\t<tr>\n"
			   . "\t\t<td align='right'>$setfont<strong>"._GL_TITLE."</strong></font></td>\n"
			   . "\t\t<td><input $slstyle type='text' size='50' name='group_name'><font color='red' face='verdana' size='1'>"._REQ."</font></td></tr>\n"
			   . "\t<tr><td align='right'>$setfont<strong>"._GL_DESCRIPTION."</strong>("._OPTIONAL.")</font></td>\n"
			   . "\t\t<td><textarea $slstyle2 cols='50' rows='4' name='description'></textarea></td></tr>\n"
			   . "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='"._ADDG."'>\n"
			   . "\t<input type='hidden' name='action' value='insertnewgroup'>\n"
			   . "\t<input type='hidden' name='sid' value='$surveyid'>\n"
			   . "\t</td></table>\n"
			   . "</form></td></tr>\n"
			   . "<tr><td align='center'>$setfont<strong>"._AD_OR."</strong></font></td></tr>\n"
			   . "<tr><td><form enctype='multipart/form-data' name='importgroup' action='$scriptname' method='post'>"
			   . "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n"
			   . "\t\t<strong>$setfont<font color='white'>"._IMPORTGROUP."</font></font></strong></td></tr>\n\t<tr>"
			   . "\t\n"
			   . "\t\t<td align='right'>$setfont<strong>"._SL_SELSQL."</strong></font></td>\n"
			   . "\t\t<td><input $slstyle2 name=\"the_file\" type=\"file\" size=\"35\"></td></tr>\n"
			   . "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='"._IMPORTGROUP."'>\n"
			   . "\t<input type='hidden' name='action' value='importgroup'>\n"
			   . "\t<input type='hidden' name='sid' value='$surveyid'>\n"
			   . "\t</td></tr>\n</table></form>\n";
	}

if ($action == "editgroup")
	{
	$egquery = "SELECT * FROM {$dbprefix}groups WHERE sid=$surveyid AND gid=$gid";
	$egresult = mysql_query($egquery);
	while ($esrow = mysql_fetch_array($egresult))	
		{
		$esrow = array_map('htmlspecialchars', $esrow);
		$editgroup =  "<form action='$scriptname' name='editgroup' method='post'>"
		 			. "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n"
					. "\t\t<strong>$setfont<font color='white'>"._GL_EDITGROUP."($surveyid)</font></font></strong></td></tr>\n"
					. "\t<tr>\n"
					. "\t\t<td align='right' width='20%'>$setfont<strong>"._GL_TITLE."</strong></font></td>\n"
					. "\t\t<td><input $slstyle type='text' size='50' name='group_name' value=\"{$esrow['group_name']}\"></td></tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._GL_DESCRIPTION."</strong>(optional)</font></td>\n"
					. "\t\t<td><textarea $slstyle2 cols='50' rows='4' name='description'>{$esrow['description']}</textarea></td></tr>\n"
					. "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='"._GL_UPDATEGROUP."'>\n"
					. "\t<input type='hidden' name='action' value='updategroup'>\n"
					. "\t<input type='hidden' name='sid' value='$surveyid'>\n"
					. "\t<input type='hidden' name='gid' value='$gid'>\n"
					. "\t</td></tr>\n"
					. "</table>\n"
					. "\t</form>\n";
		}
	}

if ($action == "editsurvey")
	{
	$esquery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid";
	$esresult = mysql_query($esquery);
	while ($esrow = mysql_fetch_array($esresult))	
		{
		$esrow = array_map('htmlspecialchars', $esrow);
		$editsurvey = "<form name='addnewsurvey' action='$scriptname' method='post'>\n<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>"
					. "\t\t<strong>$setfont<font color='white'>Edit Survey</font></font></strong></td></tr>\n"
					. "\t<tr>"
					. "\t\t<td align='right' width='25%'>$setfont<strong>"._SL_TITLE."</strong></font></td>\n"
					. "\t\t<td><input $slstyle type='text' size='50' name='short_title' value=\"{$esrow['short_title']}\"></td></tr>\n"
					. "\t<tr><td align='right' valign='top'><strong>$setfont"._SL_DESCRIPTION."</font></strong></td>\n"
					. "\t\t<td><textarea $slstyle2 cols='50' rows='5' name='description'>{$esrow['description']}</textarea></td></tr>\n"
					. "\t<tr><td align='right' valign='top'>$setfont<strong>"._SL_WELCOME."</strong></font></td>\n"
					. "\t\t<td><textarea $slstyle2 cols='50' rows='5' name='welcome'>".str_replace("<br />", "\n", $esrow['welcome'])."</textarea></td></tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._SL_ADMIN."</strong></font></td>\n"
					. "\t\t<td><input $slstyle type='text' size='50' name='admin' value=\"{$esrow['admin']}\"></td></tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._SL_EMAIL."</strong></font></td>\n"
					. "\t\t<td><input $slstyle type='text' size='50' name='adminemail' value=\"{$esrow['adminemail']}\"></td></tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._SL_FAXTO."</strong></font></td>\n"
					. "\t\t<td><input $slstyle type='text' size='50' name='faxto' value=\"{$esrow['faxto']}\"></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_FORMAT."</strong></font></td>\n"
					. "\t\t<td><select $slstyle name='format'>\n"
					. "\t\t\t<option value='S'";
		if ($esrow['format'] == "S" || !$esrow['format']) {$editsurvey .= " selected";}
		$editsurvey .= ">"._QBYQ."</option>\n"
					. "\t\t\t<option value='G'";
		if ($esrow['format'] == "G") {$editsurvey .= " selected";}
		$editsurvey .= ">"._GBYG."</option>\n"
					. "\t\t\t<option value='A'";
		if ($esrow['format'] == "A") {$editsurvey .= " selected";}
		$editsurvey .= ">"._SBYS."</option>\n"
					. "\t\t</select></td>\n"
					. "\t</tr>\n";
		//TEMPLATES
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_TEMPLATE."</strong></font></td>\n"
					 . "\t\t<td><select $slstyle name='template'>\n";
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
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_USECOOKIES."</strong></font></td>\n"
					 . "\t\t<td><select $slstyle name='usecookie'>\n"
					 . "\t\t\t<option value='Y'";
		if ($esrow['usecookie'] == "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._AD_YES."</option>\n"
					 . "\t\t\t<option value='N'";
		if ($esrow['usecookie'] != "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._AD_NO."</option>\n"
					 . "\t\t</select></td>\n"
					 . "\t</tr>\n";
		//ALLOW SAVES
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_ALLOWSAVE."</strong></font></td>\n"
					. "\t\t<td><select $slstyle name='allowsave'>\n"
					. "\t\t\t<option value='Y'";
		if (!$esrow['allowsave'] || $esrow['allowsave'] == "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._AD_YES."</option>\n"
					. "\t\t<option value='N'";
		if ($esrow['allowsave'] == "N") {$editsurvey .= " selected";}
		$editsurvey .= ">"._AD_NO."</option>\n"
					. "\t\t</select></td>\n"
					. "\t</tr>\n";
		//ALLOW PREV
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_ALLOWPREV."</strong></font></td>\n"
					. "\t\t<td><select $slstyle name='allowprev'>\n"
					. "\t\t\t<option value='Y'";
		if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._AD_YES."</option>\n"
					. "\t\t<option value='N'";
		if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") {$editsurvey .= " selected";}
		$editsurvey .= ">"._AD_NO."</option>\n"
					. "\t\t</select></td>\n"
					. "\t</tr>\n";
		//NOTIFICATION
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_NOTIFICATION."</strong></font></td>\n"
					 . "\t\t<td><select $slstyle name='notification'>\n"
					 . getNotificationlist($esrow['notification'])
					 . "\t\t</select></td>\n"
					 . "\t</tr>\n";
		//ANONYMOUS
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_ANONYMOUS."</strong></font></td>\n";
		if ($esrow['active'] == "Y")
			{
			$editsurvey .= "\t\t<td>\n\t\t\t$setfont";
			if ($esrow['private'] == "N") {$editsurvey .= " This survey is <strong>not</strong> anonymous";}
			else {$editsurvey .= "This survey <strong>is</strong> anonymous";}
			$editsurvey .= "<font size='1' color='red'>&nbsp;(Cannot be changed)\n"
						 . "\t\t</font></font>\n";
			$editsurvey .= "<input type='hidden' name='private' value=\"{$esrow['private']}\"></td>\n";
			}
		else
			{
			$editsurvey .= "\t\t<td><select $slstyle name='private'>\n"
						 . "\t\t\t<option value='Y'";
			if ($esrow['private'] == "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">"._AD_YES."</option>\n"
						 . "\t\t\t<option value='N'";
			if ($esrow['private'] != "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">"._AD_NO."</option>\n"
						 . "</select>\n\t\t</td>\n";
			}
		$editsurvey .= "</tr>\n";
		$editsurvey .= "<tr><td align='right'><script type='text/javascript'>\n"
					 . "<!--\n"
					 . "function fillin(tofield, fromfield)\n"
					 . "\t{\n"
					 . "\t\tif (confirm(\""._SL_REPLACEOK."\")) {\n"
					 . "\t\t\tdocument.getElementById(tofield).value = document.getElementById(fromfield).value\n"
					 . "\t\t}\n"
					 . "\t}\n"
					 . "--></script>\n";
		$editsurvey .= "\t$setfont<strong>"._SL_EMAILINVITE_SUBJ."</strong></font></td>\n"
					 . "\t\t<td><input type='text' $slstyle size='54' name='email_invite_subj' id='email_invite_subj' value=\"{$esrow['email_invite_subj']}\">\n"
					 . "\t\t<input type='hidden' name='email_invite_subj_default' id='email_invite_subj_default' value='".html_escape(_TC_EMAILINVITE_SUBJ)."'>\n"
					 . "\t\t<input type='button' $slstyle value='"._SL_USE_DEFAULT."' onClick='javascript: fillin(\"email_invite_subj\",\"email_invite_subj_default\")'>\n"
					 . "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILINVITE."</strong></font></td>\n"
					. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_invite' id='email_invite'>{$esrow['email_invite']}</textarea>\n"
					. "\t\t<input type='hidden' name='email_invite_default' id='email_invite_default' value='".html_escape(_TC_EMAILINVITE)."'>\n"
					. "\t\t<input type='button' $slstyle value='"._SL_USE_DEFAULT."' onClick='javascript: fillin(\"email_invite\",\"email_invite_default\")'>\n"
					. "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILREMIND_SUBJ."</strong></font></td>\n"
					 . "\t\t<td><input type='text' $slstyle size='54' name='email_remind_subj' id='email_remind_subj' value=\"{$esrow['email_remind_subj']}\">\n"
					 . "\t\t<input type='hidden' name='email_remind_subj_default' id='email_remind_subj_default' value='".html_escape(_TC_EMAILREMIND_SUBJ)."'>\n"
					 . "\t\t<input type='button' $slstyle value='"._SL_USE_DEFAULT."' onClick='javascript: fillin(\"email_remind_subj\",\"email_remind_subj_default\")'>\n"
					 . "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILREMIND."</strong></font></td>\n"
					. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_remind' id='email_remind'>{$esrow['email_remind']}</textarea>\n"
					. "\t\t<input type='hidden' name='email_remind_default' id='email_remind_default' value='".html_escape(_TC_EMAILREMIND)."'>\n"
					. "\t\t<input type='button' $slstyle value='"._SL_USE_DEFAULT."' onClick='javascript: fillin(\"email_remind\",\"email_remind_default\")'>\n"
					. "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILCONFIRM_SUBJ."</strong></font></td>\n"
					 . "\t\t<td><input type='text' $slstyle size='54' name='email_confirm_subj' id='email_confirm_subj' value=\"{$esrow['email_confirm_subj']}\">\n"
					 . "\t\t<input type='hidden' name='email_confirm_subj_default' id='email_confirm_subj_default' value='".html_escape(_TC_EMAILCONFIRM_SUBJ)."'>\n"
					 . "\t\t<input type='button' $slstyle value='"._SL_USE_DEFAULT."' onClick='javascript: fillin(\"email_confirm_subj\",\"email_confirm_subj_default\")'>\n"
					 . "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILCONFIRM."</strong></font></td>\n"
					. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_confirm' id='email_confirm'>{$esrow['email_confirm']}</textarea>\n"
					. "\t\t<input type='hidden' name='email_confirm_default' id='email_confirm_default' value='".html_escape(_TC_EMAILCONFIRM)."'>\n"
					. "\t\t<input type='button' $slstyle value='"._SL_USE_DEFAULT."' onClick='javascript: fillin(\"email_confirm\",\"email_confirm_default\")'>\n"
					. "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_ALLOWREGISTER."</strong></font></td>\n"
					. "\t\t<td><select $slstyle name='allowregister'>\n"
					. "\t\t\t<option value='Y'";
		if ($esrow['allowregister'] == "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._AD_YES."</option>\n"
					. "\t\t\t<option value='N'";
		if ($esrow['allowregister'] != "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._AD_NO."</option>\n"
					. "\t\t</select></td>\n\t</tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILREGISTER_SUBJ."</strong></font></td>\n"
					 . "\t\t<td><input type='text' $slstyle size='54' name='email_register_subj' id='email_register_subj' value=\"{$esrow['email_register_subj']}\">\n"
					 . "\t\t<input type='hidden' name='email_register_subj_default' id='email_register_subj_default' value='".html_escape(_TC_EMAILREGISTER_SUBJ)."'>\n"
					 . "\t\t<input type='button' $slstyle value='"._SL_USE_DEFAULT."' onClick='javascript:  fillin(\"email_register_subj\",\"email_register_subj_default\")'>\n"
					 . "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILREGISTER."</strong></font></td>\n"
					. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_register' id='email_register'>{$esrow['email_register']}</textarea>\n"
					 . "\t\t<input type='hidden' name='email_register_default' id='email_register_default' value='".html_escape(_TC_EMAILREGISTER)."'>\n"
					. "\t\t<input type='button' $slstyle value='"._SL_USE_DEFAULT."' onClick='javascript:  fillin(\"email_register\",\"email_register_default\")'>\n"
					. "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right' valign='top'>$setfont<strong>"._SL_ATTRIBUTENAMES."</strong></font></td>\n"
					. "\t\t<td>$setfont<input $slstyle type='text' size='25' name='attribute1'"
					. " value=\"{$esrow['attribute1']}\">("._TL_ATTR1.")<br />"
					. "<input $slstyle type='text' size='25' name='attribute2'"
					. " value=\"{$esrow['attribute2']}\">("._TL_ATTR2.")</font></td>\n\t</tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_DATESTAMP."</strong></font></td>\n";
				
		if ($esrow['active'] == "Y")
			{
			$editsurvey .= "\t\t<td>\n\t\t\t$setfont";
			if ($esrow['datestamp'] != "Y") {$editsurvey .= " Responses <strong>will not</strong> be date stamped.";}
			else {$editsurvey .= "Responses <strong>will</strong> be date stamped";}
			$editsurvey .= "<font size='1' color='red'>&nbsp;(Cannot be changed)\n"
						 . "\t\t</font></font>\n";
			$editsurvey .= "<input type='hidden' name='datestamp' value=\"{$esrow['datestamp']}\"></td>\n";
			}
		else
			{
			$editsurvey .= "\t\t<td><select $slstyle name='datestamp'>\n"
						 . "\t\t\t<option value='Y'";
			if ($esrow['datestamp'] == "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">"._AD_YES."</option>\n"
						 . "\t\t\t<option value='N'";
			if ($esrow['datestamp'] != "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">"._AD_NO."</option>\n"
						 . "</select>\n\t\t</td>\n";
			}
		$editsurvey .= "</tr>\n";

		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_IPADDRESS."</strong></font></td>\n";

		if ($esrow['active'] == "Y")
			{
			$editsurvey .= "\t\t<td>\n\t\t\t$setfont";
			if ($esrow['ipaddr'] != "Y") {$editsurvey .= " Responses <strong>will not</strong> have the IP address logged.";}
			else {$editsurvey .= "Responses <strong>will</strong> have the IP address logged";}
			$editsurvey .= "<font size='1' color='red'>&nbsp;(Cannot be changed)\n"
						 . "\t\t</font></font>\n";
			$editsurvey .= "<input type='hidden' name='ipaddr' value='".$esrow['ipaddr']."'>\n</td>";
			}
		else
			{
			$editsurvey .= "\t\t<td><select $slstyle name='ipaddr'>\n"
						 . "\t\t\t<option value='Y'";
			if ($esrow['ipaddr'] == "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">"._AD_YES."</option>\n"
						 . "\t\t\t<option value='N'";
			if ($esrow['ipaddr'] != "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">"._AD_NO."</option>\n"
						 . "</select>\n\t\t</td>\n";
			}
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_LANGUAGE."</strong></font></td>\n"
					 . "\t\t<td><select $slstyle name='language'>\n";
		foreach (getlanguages() as $langname)
			{
			$editsurvey .= "\t\t\t<option value='$langname'";
			if ($esrow['language'] && $esrow['language'] == htmlspecialchars($langname)) {$editsurvey .= " selected";}
			if (!$esrow['language'] && $defaultlang && $defaultlang == $langname) {$editsurvey .= " selected";}
			$editsurvey .= ">$langname</option>\n";
			}
		$editsurvey .= "\t\t</select></td>\n"
			    	. "\t</tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._SL_EXPIRYDATE."</strong></font></td>\n"
					. "\t\t\t<td><select $slstyle name='useexpiry'><option value='Y'";
		if (isset($esrow['useexpiry']) && $esrow['useexpiry'] == "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._AD_YES."</option>\n"
					. "\t\t\t<option value='N'";
		if (!isset($esrow['useexpiry']) || $esrow['useexpiry'] != "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._AD_NO."</option></select></td></tr><tr><td></td>\n"
					. "\t\t<td><input $slstyle type='text' size='12' name='expires' value=\"{$esrow['expires']}\"></td></tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._SL_URL."</strong></font></td>\n"
					. "\t\t<td><input $slstyle type='text' size='50' name='url' value=\"{$esrow['url']}\"></td></tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._SL_URLDESCRIP."</strong></font></td>\n"
					. "\t\t<td><input $slstyle type='text' size='50' name='urldescrip' value=\"{$esrow['urldescrip']}\"></td></tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._SL_AUTORELOAD."</strong></font></td>\n"
					. "\t\t<td><select $slstyle name='autoredirect'>";
		$editsurvey .= "\t\t\t<option value='Y'";
		if (isset($esrow['autoredirect']) && $esrow['autoredirect'] == "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._AD_YES."</option>\n";
		$editsurvey .= "\t\t\t<option value='N'";
		if (!isset($esrow['autoredirect']) || $esrow['autoredirect'] != "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._AD_NO."</option>\n"
					 . "</select></td></tr>";

		$editsurvey .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='"._SL_UPD_SURVEY."'>\n"
					 . "\t<input type='hidden' name='action' value='updatesurvey'>\n"
					 . "\t<input type='hidden' name='sid' value=\"{$esrow['sid']}\">\n"
					 . "\t</td></tr>\n"
					 . "</table></form>\n";
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
			$editcsv .="<b><font color='red'>"._ERROR.":</font> "._ERR_INI_SIZE."</b>\n";
			break;
		case UPLOAD_ERR_PARTIAL:	
			upload();
			$editcsv .="<b><font color='red'>"._ERROR.":</font> "._UPLOAD_ERR_PARTIAL."</b>\n";
			break;
		case UPLOAD_ERR_NO_FILE:
			upload();	
			$editcsv .="<b><font color='red'>"._ERROR.":</font> "._ERR_NO_FILE."</b>\n";
			break;
		case UPLOAD_ERR_OK:		     
			control();
			break;
		default:
			$editcsv .="<b><font color='red'>"._ERROR.":</font> "._TRANSFERERROR."</b>\n";
		}	
	}
	
if ($action == "newsurvey")
	{
	$newsurvey = "<form name='addnewsurvey' action='$scriptname' method='post'>\n<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n"
				. "\t\t<strong>$setfont<font color='white'>"._CREATESURVEY."</font></font></strong></td></tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right' width='25%'><strong>$setfont"._SL_TITLE."</font></strong></td>\n"
				. "\t\t<td><input $slstyle type='text' size='50' name='short_title'></td></tr>\n"
				. "\t<tr><td align='right'><strong>$setfont"._SL_DESCRIPTION."</font></strong>	</td>\n"
				. "\t\t<td><textarea $slstyle2 cols='50' rows='5' name='description'></textarea></td></tr>\n"
				. "\t<tr><td align='right'>$setfont<strong>"._SL_WELCOME."</strong></font></td>\n"
				. "\t\t<td><textarea $slstyle2 cols='50' rows='5' name='welcome'></textarea></td></tr>\n"
				. "\t<tr><td align='right'>$setfont<strong>"._SL_ADMIN."</strong></font></td>\n"
				. "\t\t<td><input $slstyle type='text' size='50' name='admin'></td></tr>\n"
				. "\t<tr><td align='right'>$setfont<strong>"._SL_EMAIL."</strong></font></td>\n"
				. "\t\t<td><input $slstyle type='text' size='50' name='adminemail'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_FAXTO."</strong></font></td>\n"
				. "\t\t<td><input $slstyle type='text' size='50' name='faxto'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_FORMAT."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='format'>\n"
				. "\t\t\t<option value='S' selected>"._QBYQ."</option>\n"
				. "\t\t\t<option value='G'>"._GBYG."</option>\n"
				. "\t\t\t<option value='A'>"._SBYS."</option>\n"
				. "\t\t</select></td>\n"
				. "\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_TEMPLATE."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='template'>\n";
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
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_USECOOKIES."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='usecookie'>\n"
				. "\t\t\t<option value='Y'";
	if (isset($esrow) && $esrow['usecookie'] == "Y") {$newsurvey .= " selected";}
	$newsurvey .= ">"._AD_YES."</option>\n"
				. "\t\t\t<option value='N'";
	if (isset($esrow) && $esrow['usecookie'] != "Y" || !isset($esrow)) {$newsurvey .= " selected";}
	$newsurvey .= ">"._AD_NO."</option>\n"
				. "\t\t</select></td>\n"
				. "\t</tr>\n";
	//ALLOW SAVES
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_ALLOWSAVE."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='allowsave'>\n"
				. "\t\t\t<option value='Y'";
	if (!isset($esrow['allowsave']) || !$esrow['allowsave'] || $esrow['allowsave'] == "Y") {$newsurvey .= " selected";}
	$newsurvey .= ">"._AD_YES."</option>\n"
				. "\t\t<option value='N'";
	if (isset($esrow['allowsave']) && $esrow['allowsave'] == "N") {$newsurvey .= " selected";}
	$newsurvey .= ">"._AD_NO."</option>\n"
				. "\t\t</select></td>\n"
				. "\t</tr>\n";
	//ALLOW PREV
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_ALLOWPREV."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='allowprev'>\n"
				. "\t\t\t<option value='Y'";
	if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") {$newsurvey .= " selected";}
	$newsurvey .= ">"._AD_YES."</option>\n"
				. "\t\t<option value='N'";
	if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") {$newsurvey .= " selected";}
	$newsurvey .= ">"._AD_NO."</option>\n"
				. "\t\t</select></td>\n"
				. "\t</tr>\n";
	//NOTIFICATIONS
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_NOTIFICATION."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='notification'>\n"
				. getNotificationlist(0)
				. "\t\t</select></td>\n"
				. "\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_ANONYMOUS."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='private'>\n"
				. "\t\t\t<option value='Y' selected>"._AD_YES."</option>\n"
				. "\t\t\t<option value='N'>"._AD_NO."</option>\n"
				. "\t\t</select></td>\n\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILINVITE_SUBJ."</strong></font></td>\n"
				 . "\t\t<td><input type='text' $slstyle size='54' name='email_invite_subj' value='".html_escape(_TC_EMAILINVITE_SUBJ)."'>\n"
				 . "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILINVITE."</strong></font></td>\n"
				. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_invite'>"._TC_EMAILINVITE."</textarea>\n"
				. "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILREMIND_SUBJ."</strong></font></td>\n"
				 . "\t\t<td><input type='text' $slstyle size='54' name='email_remind_subj' value='".html_escape(_TC_EMAILREMIND_SUBJ)."'>\n"
				 . "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILREMIND."</strong></font></td>\n"
				. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_remind'>"._TC_EMAILREMIND."</textarea>\n"
				. "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILCONFIRM_SUBJ."</strong></font></td>\n"
				 . "\t\t<td><input type='text' $slstyle size='54' name='email_confirm_subj' value='".html_escape(_TC_EMAILCONFIRM_SUBJ)."'>\n"
				 . "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILCONFIRM."</strong></font></td>\n"
				. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_confirm'>"._TC_EMAILCONFIRM."</textarea>\n"
				. "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_ALLOWREGISTER."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='allowregister'>\n"
				. "\t\t\t<option value='Y'>"._AD_YES."</option>\n"
				. "\t\t\t<option value='N' selected>"._AD_NO."</option>\n"
				. "\t\t</select></td>\n\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILREGISTER_SUBJ."</strong></font></td>\n"
				 . "\t\t<td><input type='text' $slstyle size='54' name='email_register_subj' value='".html_escape(_TC_EMAILREGISTER_SUBJ)."'>\n"
				 . "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EMAILREGISTER."</strong></font></td>\n"
				. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_register'>"._TC_EMAILREGISTER."</textarea>\n"
				. "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right' valign='top'>$setfont<strong>"._SL_ATTRIBUTENAMES."</strong></font></td>\n"
				. "\t\t<td>$setfont<input $slstyle type='text' size='25' name='attribute1'>("._TL_ATTR1.")<br />"
				. "<input $slstyle type='text' size='25' name='attribute2'>("._TL_ATTR2.")</font></td>\n\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_DATESTAMP."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='datestamp'>\n"
				. "\t\t\t<option value='Y'>"._AD_YES."</option>\n"
				. "\t\t\t<option value='N' selected>"._AD_NO."</option>\n"
				. "\t\t</select></td>\n\t</tr>\n";
	// IP Address
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_IPADDRESS."</strong></font></td>\n"
                                . "\t\t<td><select $slstyle name='ipaddr'>\n"                                . "\t\t\t<option value='Y'>"._AD_YES."</option>\n"
                                . "\t\t\t<option value='N' selected>"._AD_NO."</option>\n"
                                . "\t\t</select></td>\n\t</tr>\n";
	//NOTIFICATION
	//LANGUAGE
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_LANGUAGE."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='language'>\n";
	foreach (getlanguages() as $langname)
		{
		$newsurvey .= "\t\t\t<option value='$langname'";
		if ($defaultlang && $defaultlang == $langname) {$newsurvey .= " selected";}
		$newsurvey .= ">$langname</option>\n";
		}
	$newsurvey .= "\t\t</select></td>\n"
				. "\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._SL_EXPIRYDATE."</strong></font></td>\n"
				. "\t\t\t<td><select $slstyle name='useexpiry'><option value='Y'>"._AD_YES."</option>\n"
				. "\t\t\t<option value='N' selected>"._AD_NO."</option></select></td></tr><tr><td></td>\n"
				. "\t\t<td>$setfont<input $slstyle type='text' size='12' name='expires' value='1980-01-01'>"
				. "<font size='1'>Date Format: YYYY-MM-DD</font></font></td></tr>\n"
				. "\t<tr><td align='right'>$setfont<strong>"._SL_URL."</strong></font></td>\n"
				. "\t\t<td><input $slstyle type='text' size='50' name='url' value='http://";
	if (isset($esrow)) {$newsurvey .= $esrow['url'];}
	$newsurvey .= "'></td></tr>\n"
				. "\t<tr><td align='right'>$setfont<strong>"._SL_URLDESCRIP."</strong></font></td>\n"
				. "\t\t<td><input $slstyle type='text' size='50' name='urldescrip' value='";
	if (isset($esrow)) {$newsurvey .= $esrow['urldescrip'];}
	$newsurvey .= "'></td></tr>\n"
				 . "\t<tr><td align='right'>$setfont<strong>"._SL_AUTORELOAD."</strong></font></td>\n"
				 . "\t\t<td><select $slstyle name='autoredirect'>\n"
				 . "\t\t\t<option value='Y'>"._AD_YES."</option>\n"
				 . "\t\t\t<option value='N' selected>"._AD_NO."</option>\n"
				 . "</select></td></tr>"
				. "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='"._CREATESURVEY."'>\n"
				. "\t<input type='hidden' name='action' value='insertnewsurvey'></td>\n"
				. "\t</tr>\n"
				. "</table></form>\n";
	$newsurvey .= "<center>$setfont<strong>"._AD_OR."</strong></font></center>\n";
	$newsurvey .= "<form enctype='multipart/form-data' name='importsurvey' action='$scriptname' method='post'>\n"
				. "<table width='100%' border='0'>\n"
				. "<tr><td colspan='2' bgcolor='black' align='center'>\n"
				. "\t\t<strong>$setfont<font color='white'>"._IMPORTSURVEY."</font></font></strong></td></tr>\n\t<tr>"
				. "\t\t<td align='right'>$setfont<strong>"._SL_SELSQL."</strong></font></td>\n"
				. "\t\t<td><input $slstyle2 name=\"the_file\" type=\"file\" size=\"35\"></td></tr>\n"
				. "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='"._IMPORTSURVEY."'>\n"
				. "\t<input type='hidden' name='action' value='importsurvey'></TD>\n"
				. "\t</tr>\n</table></form>\n";
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
	$newquestion = "<script type='text/javascript'>\n"
				 . "<!--\n";
		$jc=0;
		$newquestion .= "\t\t\tvar qtypes = new Array();\n";
		$newquestion .= "\t\t\tvar qnames = new Array();\n\n";
		foreach ($qattributes as $key=>$val)
			{
			foreach ($val as $vl)
				{
				$newquestion .= "\t\t\tqtypes[$jc]='".$key."';\n";
				$newquestion .= "\t\t\tqnames[$jc]='".$vl['name']."';\n";
				$jc++;
				}
			}
		$newquestion .= "\t\t\t function buildQTlist(type)
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

	return $newquestion;
	}


function upload()	
	{
	global $questionsummary, $sid, $qid, $gid;
	$questionsummary .= "\t\t<tr $qshowstyle id='surveydetails37'><td></td><td>"
	  					 . "<font face='verdana' size='1' color='green'>"
						 . _WARNING.": ". _UPLOADFILE." "
						 . "\n$setfont<form enctype='multipart/form-data' action='" . $_SERVER['PHP_SELF'] . "' method='post'>\n"
						 . "<input type='hidden' name='action' value='uploadf' />\n"
						 . "<input type='hidden' name='sid' value='$sid' />\n"
						 . "<input type='hidden' name='gid' value='$gid' />\n"
                         . "<input type='hidden' name='qid' value='$qid' />\n"
						 . "<font face='verdana' size='2' color='green'><b>"
                         . _UPLOADCSVFILE."</font><br />\n"
 						 . "<input type='file' $slstyle name='the_file' size='35' /><br />\n"
						 . "<input type='submit' $btstyle value='"._UPFILE."' />\n"
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
		$editcsv .="<b><font color='red'>"._ERROR.":</font> "._NO_CSV."</b>\n";
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
			$editcsv .="<b><font color='red'>"._ERROR.":</font> "._EMPTY."</b>\n";
			$questionsummary .= "</table>\n";	
			}
		else 			
			{
			$editcsv  = "<table width='100%' align='center' border='0'>\n"
				. "<tr bgcolor='#555555'><td colspan='2'><font color='white'><b>"
				. _UPLOADRUN."</b></td></tr>\n";
		    $editcsv .= "<tr><th>$setfont"._VISUALIZATION."</font></th><th>$setfont"._SELECTION."</font></th>"
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
			$editcsv.="<input type='hidden' name='sid' value='$sid'>\n"
					. "\t<input type='hidden' name='gid' value='$gid'>\n"
					. "\t<input type='hidden' name='qid' value='$qid'>\n"
					. "\t<input type='hidden' name='elem' value='$elem'>\n"
					. "\t<input type='hidden' name='svettore' value='".$svettore."'>\n";
				
			$editcsv.="\t\t\t<td align = 'center'><select name='numcol'>\n";
			$numerocampo = 0; 
			foreach ($vettoreriga as $K => $v)
				{
				$numerocampo = $numerocampo + 1;
				$editcsv .= "\t\t<option value=$numerocampo>$numerocampo</option>\n";
				}
			$editcsv .= "</select></td>\n"
					. "\t<input type='hidden' name='filev' value='$fp'>\n"
					. "\t<input type='hidden' name='action' value='insertCSV'>\n"
					. "\t<tr><td align='right'><input $btstyle type='submit' value='"
					._CONT."'></td>\n";
			}
		}	
	}	
	
?>
