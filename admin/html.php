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
	$result = $connect->Execute($query);
	$surveycount=$result->RecordCount();
	$query = "SELECT sid FROM {$dbprefix}surveys WHERE active='Y'";
	$result = $connect->Execute($query);
	$activesurveycount=$result->RecordCount();
	$query = "SELECT user FROM {$dbprefix}users";
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
				. "<table align='center' bgcolor='#DDDDDD' style='border: 1px solid #555555' "
				. "cellpadding='1' cellspacing='0' width='450'>\n"
				. "\t<tr>\n"
				. "\t\t<td colspan='2' align='center' bgcolor='#BBBBBB'>$setfont\n"
				. "\t\t\t<strong>"._("PHPSurveyor System Summary")."</strong>\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td width='50%' align='right'>$setfont\n"
				. "\t\t\t<strong>"._("Database Name").":</strong></font>\n"
				. "\t\t</td><td>$setfont\n"
				. "\t\t\t$databasename\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right'>$setfont\n"
				. "\t\t\t<strong>"._("Default Language").":</strong></font>\n"
				. "\t\t</td><td>$setfont\n"
				. "\t\t\t".getLanguageNameFromCode($defaultlang)."\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right' >$setfont\n"
				. "\t\t\t<strong>"._("Current Language").":</strong>\n"
				. "\t\t</font></td><td>$setfont\n"
				. "\t\t\t<select name='lang' $slstyle onChange='form.submit()'>\n";
  	foreach (getlanguagedata() as $langkey=>$languagekind)
	{
    	$cssummary .= "\t\t\t\t<option value='$langkey'";
		if ($langkey == $currentlang) {$cssummary .= " selected";}
		$cssummary .= ">".$languagekind['description']."</option>\n";
	}
	$cssummary .= "\t\t\t</select>\n"
				. "\t\t\t<input type='hidden' name='action' value='changelang'>\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right'>$setfont\n"
				. "\t\t\t<strong>"._("Users").":</strong>\n"
				. "\t\t</font></td><td>$setfont\n"
				. "\t\t\t$usercount\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right'>$setfont\n"
				. "\t\t\t<strong>"._("Surveys").":</strong>\n"
				. "\t\t</font></td><td>$setfont\n"
				. "\t\t\t$surveycount\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right'>$setfont\n"
				. "\t\t\t<strong>"._("Active Surveys").":</strong>\n"
				. "\t\t</font></td><td>$setfont\n"
				. "\t\t\t$activesurveycount\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right'>$setfont\n"
				. "\t\t\t<strong>"._("De-activated Surveys").":</strong>\n"
				. "\t\t</font></td><td>$setfont\n"
				. "\t\t\t$deactivatedsurveys\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right'>$setfont\n"
				. "\t\t\t<strong>"._("Active Token Tables").":</strong>\n"
				. "\t\t</font></td><td>$setfont\n"
				. "\t\t\t$activetokens\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right'>$setfont\n"
				. "\t\t\t<strong>"._("De-activated Token Tables").":</strong></font>\n"
				. "\t\t</td><td>$setfont\n"
				. "\t\t\t$deactivatedtokens\n"
				. "\t\t</font></td>\n"
				. "\t</tr>\n"
				. "</table></form>\n"
				. "<table><tr><td height='1'></td></tr></table>\n";
	$cssummary .= "<table align='center' bgcolor='#DDDDDD' style='border: 1px solid #555555' "
				. "cellpadding='1' cellspacing='0' width='450'>\n"
				. "<tr><td align='center'>$setfont<br />"
				. "<a href='".$homeurl."/dbchecker.php'>"._("Check PHPSurveyor Data Integrity")."</a>"
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
	$sumresult3 = $connect->Execute($sumquery3);
	$sumcount3 = $sumresult3->RecordCount();
	$sumquery2 = "SELECT * FROM {$dbprefix}groups WHERE sid=$surveyid"; //Getting a count of groups for this survey
	$sumresult2 = $connect->Execute($sumquery2);
	$sumcount2 = $sumresult2->RecordCount();
	$sumquery1 = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid"; //Getting data for this survey
	$sumresult1 = db_execute_assoc($sumquery1);
	$surveysummary .= "<table width='100%' align='center' bgcolor='#DDDDDD' border='0'>\n";
	while ($s1row = $sumresult1->FetchRow())
		{
		$s1row = array_map('htmlspecialchars', $s1row);
		$activated = $s1row['active'];
		//BUTTON BAR
		$surveysummary .= "\t<tr>\n"
						. "\t\t<td colspan='2'>\n"
						. "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
						. "\t\t\t\t<tr bgcolor='#555555'><td colspan='2' height='4'>"
						. "$setfont<font size='1' face='verdana' color='white'><strong>"._("Survey")."</strong> "
						. "<font color='silver'>{$s1row['short_title']}</font></font></font></td></tr>\n"
						. "\t\t\t\t<tr bgcolor='#999999'><td align='right' height='22'>\n";
		if ($activated == "N" && $sumcount3>0) 
			{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/inactive.png' "
							. "title='' alt='"._("This survey is not currently active")."' border='0' hspace='0' align='left'"
							. "onmouseout=\"hideTooltip()\"" 
                    		. "onmouseover=\"showTooltip(event,'"._("This survey is not currently active")."');return false\">\n" 
							. "\t\t\t\t\t<input type='image' src='$imagefiles/activate.png' name='ActivateSurvey' "
							. "title='' alt='"._("Activate this Survey")."' align='left' "
							. "onClick=\"window.open('$scriptname?action=activate&amp;sid=$surveyid', '_top')\""
							. "onmouseout=\"hideTooltip()\"" 
                    		. "onmouseover=\"showTooltip(event,'"._("Activate this Survey")."');return false\">\n" ; 
			}
		elseif ($activated == "Y")
			{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/active.png' title='' "
							. "alt='"._("This survey is currently active")."' align='left'"
							. "onmouseout=\"hideTooltip()\"" 
                    		. "onmouseover=\"showTooltip(event,'"._("This survey is currently active")."');return false\">\n" 
							. "\t\t\t\t\t<input type='image' src='$imagefiles/deactivate.png' name='DeactivateSurvey' "
							. "alt='"._("De-activate this Survey")."' title='' align='left' "
							. "onClick=\"window.open('$scriptname?action=deactivate&amp;sid=$surveyid', '_top')\"" 
							. "onmouseout=\"hideTooltip()\"" 
                    		. "onmouseover=\"showTooltip(event,'"._("De-activate this Survey")."');return false\">\n" ; 
			}
		elseif ($activated == "N")
			{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/inactive.png' title='"._("This survey is not currently active")."' "
							. "alt='"._("This survey is not currently active")."' border='0' hspace='0' align='left'>\n"
							. "\t\t\t\t\t<img src='$imagefiles/blank.gif' width='11' title='"._("Cannot Activate this Survey")."' "
							. "alt='"._("Cannot Activate this Survey")."' border='0' align='left' hspace='0'>\n";
			}
		$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0'>\n"
						. "\t\t\t\t\t<input type='image' accesskey='d' src='$imagefiles/do.png' title='' "
						. "name='DoSurvey' align='left' alt='"._("Do Survey")."' "
						. "onclick=\"window.open('".$publicurl."/index.php?sid=$surveyid&amp;newtest=Y', '_blank')\"" 
						. "onmouseout=\"hideTooltip()\"" 
                    	. "onmouseover=\"showTooltip(event,'"._("Do Survey")."');return false\">\n"
						. "\t\t\t\t\t<input type='image' src='$imagefiles/dataentry.png' "
						. "title='' align='left' alt='"._("Dataentry Screen for Survey")."'"
						. "name='DoDataentry' onclick=\"window.open('".$homeurl."/dataentry.php?sid=$surveyid', '_blank')\"" 
						. "onmouseout=\"hideTooltip()\"" 
                    	. "onmouseover=\"showTooltip(event,'"._("Dataentry Screen for Survey")."');return false\">\n"
						. "\t\t\t\t\t<input type='image' src='$imagefiles/print.png' title='' "
						. "name='ShowPrintableSurvey' align='left' alt='"._("Printable Version of Survey")."' "
						. "onclick=\"window.open('".$homeurl."/printablesurvey.php?sid=$surveyid', '_blank')\""
						. "onmouseout=\"hideTooltip()\"" 
                    	. "onmouseover=\"showTooltip(event,'"._("Printable Version of Survey")."');return false\">\n"
						. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0'>\n"
						. "\t\t\t\t\t<input type='image' src='$imagefiles/edit.png' title='' "
						. "name='EditSurvey' align='left' alt='"._("Edit Current Survey")."'"
						. "onclick=\"window.open('$scriptname?action=editsurvey&amp;sid=$surveyid', '_top')\"" 
						. "onmouseout=\"hideTooltip()\"" 
                    	. "onmouseover=\"showTooltip(event,'"._("Edit Current Survey")."');return false\">\n" ;
		if ($sumcount3 == 0 && $sumcount2 == 0)
			{
			$surveysummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/delete.png' title='' align='left' name='DeleteWholeSurvey' "
							. "onclick=\"window.open('$scriptname?action=delsurvey&amp;sid=$surveyid', '_top')\""
							. "onmouseout=\"hideTooltip()\"" 
                    		. "onmouseover=\"showTooltip(event,'". _("Delete Current Survey")."');return false\">\n" ;
			}
		else
			{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' align='left' border='0' hspace='0'>\n";
			}
		$surveysummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/exportsql.png' title='' alt='". _("Export this Survey")."' align='left' name='ExportSurvey' "
						. "onclick=\"window.open('".$homeurl."/dumpsurvey.php?sid=$surveyid', '_top')\""
						. "onmouseout=\"hideTooltip()\"" 
                    	. "onmouseover=\"showTooltip(event,'". _("Export this Survey")."');return false\">\n" ;
		$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0'>\n"
						. "<input type='image' src='$imagefiles/assessments.png' title='' alt='". _("Set Assessment Rules")."' align='left' name='SurveyAssessment' "
						. "onclick=\"window.open('".$homeurl."/assessments.php?sid=$surveyid', '_top')\"" 
						. "onmouseout=\"hideTooltip()\"" 
                    	. "onmouseover=\"showTooltip(event,'". _("Set Assessment Rules")."');return false\">\n" ;		
		
		if ($activated == "Y")
			{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0'>\n"
							. "\t\t\t\t\t<input type='image' src='$imagefiles/browse.png' title='' "
							. "align='left' name='BrowseSurveyResults' alt='"._("Browse Responses for this Survey")."'"
							. "onclick=\"window.open('".$homeurl."/browse.php?sid=$surveyid', '_top')\"" 
							. "onmouseout=\"hideTooltip()\"" 
                    		. "onmouseover=\"showTooltip(event,'"._("Browse Responses for this Survey")."');return false\">\n" 
							. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0'>\n";
			if ($s1row['allowsave'] == "Y")
				{
				$surveysummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/saved.png' title='' "
								. "align='left'  name='BrowseSaved' alt='"._("View Saved but not submitted Responses")."' "
								. "onclick=\"window.open('".$homeurl."/saved.php?sid=$surveyid', '_top')\"" 
								. "onmouseout=\"hideTooltip()\"" 
                    			. "onmouseover=\"showTooltip(event,'"._("View Saved but not submitted Responses")."');return false\">\n" 
								. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0'>\n";
				}				
			$surveysummary .="\t\t\t\t\t<input type='image' src='$imagefiles/tokens.png' title='' "
							. "align='left'  name='TokensControl' alt='"._("Activate/Edit Tokens for this Survey")."'"
							. "onclick=\"window.open('$homeurl/tokens.php?sid=$surveyid', '_top')\"" 
							. "onmouseout=\"hideTooltip()\"" 
                    		. "onmouseover=\"showTooltip(event,'"._("Activate/Edit Tokens for this Survey")."');return false\">\n" ;
			}
		$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='left' border='0' hspace='0'>\n"
						. "\t\t\t\t</td>\n"
						. "\t\t\t\t<td align='right' valign='middle' width='330'>\n";
		if (!$gid) 
			{
			$surveysummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' title='". _("Close this Survey")."' alt='". _("Close this Survey")."' align='right'  name='CloseSurveyWindow' "
							. "onclick=\"window.open('$scriptname', '_top')\">\n";
			}
		else 
			{
			$surveysummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' width='21' align='right' "
							. "border='0' hspace='0' alt=''>\n";
			}
		$surveysummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/plus.gif' title='". _("Show Details of this Survey")."' alt='". _("Show Details of this Survey")."' name='MaximiseSurveyWindow' "
						. "align='right' onclick='showdetails(\"shows\")'>\n"
						. "\t\t\t\t\t<input type='image' src='$imagefiles/minus.gif' title='". _("Hide Details of this Survey")."' alt='". _("Hide Details of this Survey")."' name='MinimiseSurveyWindow' "
						. "align='right' onclick='showdetails(\"hides\")'>\n"
						. "\t\t\t\t\t<img src='$imagefiles/seperator.gif' align='right' border='0' "
						. "alt='' hspace='0'>\n";
		if ($activated == "Y")
			{
			$surveysummary .= "<img src='$imagefiles/blank.gif' alt='' width='20' align='right' border='0' hspace='0'>\n";
			}
		else
			{
			$surveysummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/add.png' title='' " 
							. "alt='". _("Add New Group to Survey")."'align='right'  name='AddNewGroup' "
							. "onClick=\"window.open('$scriptname?action=addgroup&amp;sid=$surveyid', '_top')\"" 
							. "onmouseout=\"hideTooltip()\"" 
                    		. "onmouseover=\"showTooltip(event,'"._("Add New Group to Survey")."');return false\">\n" ;
			}
		$surveysummary .= "$setfont<font size='1' color='#222222'><strong>"._("Groups").":</strong>"
						. "\t\t<select style='font-size: 9; font-family: verdana; color: #222222; "
						. "background: silver; width: 160' name='groupselect' "
						. "onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
		if (getgrouplist($gid))
			{
			$surveysummary .= getgrouplist($gid);
			}
		else
			{
			$surveysummary .= "<option>"._("None")."</option>\n";
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
						. "$setfont<strong>"._("Title:")."</strong></font></td>\n"
						. "\t<td>$setfont<font color='#000080'><strong>{$s1row['short_title']} "
						. "(ID {$s1row['sid']})</strong></font></font></td></tr>\n";
		$surveysummary2 = "\t<tr $showstyle id='surveydetails1'><td width='80'></td>"
						. "<td>$setfont<font size='1' color='#000080'>\n";
		if ($s1row['private'] != "N") {$surveysummary2 .= _("This survey is anonymous.")."<br />\n";}
		else {$surveysummary2 .= _("This survey is NOT anonymous.")."<br />\n";}
		if ($s1row['format'] == "S") {$surveysummary2 .= _("It is presented question by question.")."<br />\n";}
		elseif ($s1row['format'] == "G") {$surveysummary2 .= _("It is presented group by group.")."<br />\n";}
		else {$surveysummary2 .= _("It is presented on one single page.")."<br />\n";}
		if ($s1row['datestamp'] == "Y") {$surveysummary2 .= _("Responses will be date stamped")."<br />\n";}
		if ($s1row['ipaddr'] == "Y") {$surveysummary2 .= _("IP Addresses will be logged")."<br />\n";}
		if ($s1row['refurl'] == "Y") {$surveysummary2 .= _SS_REFURL."<br />\n";}
		if ($s1row['usecookie'] == "Y") {$surveysummary2 .= _("It uses cookies for access control.")."<br />\n";}
		if ($s1row['allowregister'] == "Y") {$surveysummary2 .= _("If tokens are used, the public may register for this survey")."<br />\n";}
		if ($s1row['allowsave'] == "Y") {$surveysummary2 .= _("Participants can save partially finished surveys")."<br />\n";}
		switch ($s1row['notification'])
			{
			case 0:
				$surveysummary2 .= _("No email notification")."<br />\n";
				break;
			case 1:
				$surveysummary2 .= _("Basic email notification")."<br />\n";
				break;
			case 2:
				$surveysummary2 .= _("Detailed email notification with result codes")."<br />\n";
				break;
			}
		$surveysummary2 .= _("Regenerate Question Numbers:")
						 . " [<a href='$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=straight' "
						 . "onClick='return confirm(\"Are you sure?\")' "
						 . ">"._("Straight")."</a>] "
						 . "[<a href='$scriptname?action=renumberquestions&amp;sid=$surveyid&amp;style=bygroup' "
						 . "onClick='return confirm(\"Are you sure?\")' "
						 . ">"._("By Group")."</a>]";
		$surveysummary2 .= "</font></font></td></tr>\n";
		$surveysummary .= "\t<tr $showstyle id='surveydetails11'>"
			. "<td align='right' valign='top'>$setfont<strong>"
			. _("Survey URL:") . "</strong></font></td>\n";
		$tmp_url = $GLOBALS['publicurl'] . '/index.php?sid=' . $s1row['sid'];
		$surveysummary .= "\t\t<td>$setfont <a href='$tmp_url' target='_blank'>$tmp_url</a>"
						. "</font></td></tr>\n"
						. "\t<tr $showstyle id='surveydetails2'><td align='right' valign='top'>$setfont<strong>"
						. _("Description:")."</strong></font></td>\n\t\t<td>";
						if (trim($s1row['description'])!='') {$surveysummary .= "$setfont {$s1row['description']}</font>";}
		$surveysummary .= "</td></tr>\n"
						. "\t<tr $showstyle id='surveydetails3'>\n"
						. "\t\t<td align='right' valign='top'>$setfont<strong>"
						. _("Welcome:")."</strong></font></td>\n"
						. "\t\t<td>$setfont {$s1row['welcome']}</font></td></tr>\n"
						. "\t<tr $showstyle id='surveydetails4'><td align='right' valign='top'>$setfont<strong>"
						. _("Administrator:")."</strong></font></td>\n"
						. "\t\t<td>$setfont {$s1row['admin']} ({$s1row['adminemail']})</font></td></tr>\n"
						. "\t<tr $showstyle id='surveydetails5'><td align='right' valign='top'>$setfont<strong>"
						. _("Fax To:")."</strong></font></td>\n\t\t<td>";
						if (trim($s1row['faxto'])!='') {$surveysummary .= "$setfont {$s1row['faxto']}</font>";}
		$surveysummary .= "</td></tr>\n"
						. "\t<tr $showstyle id='surveydetails6'><td align='right' valign='top'>$setfont<strong>"
						. _("Expiry Date:")."</strong></font></td>\n";
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
						. _("Template:")."</strong></font></td>\n"
						. "\t\t<td>$setfont {$s1row['template']}</font></td></tr>\n"
						. "\t<tr $showstyle id='surveydetails8'><td align='right' valign='top'>$setfont<strong>"
						. _("Language:")."</strong></font></td>\n";
		if (!$s1row['language']) {$language=$currentlang;} else {$language=$s1row['language'];}
		if ($s1row['urldescrip']==""){$s1row['urldescrip']=$s1row['url'];}
		$surveysummary .= "\t\t<td>$setfont$language</font></td></tr>\n"
						. "\t<tr $showstyle id='surveydetails9'><td align='right' valign='top'>$setfont<strong>"
						. _("Exit Link:")."</strong></font></td>\n"
						. "\t\t<td>";
		if ($s1row['url']!="") {$surveysummary .="$setfont <a href=\"{$s1row['url']}\" title=\"{$s1row['url']}\">{$s1row['urldescrip']}</a></font>";}
		$surveysummary .="</td></tr>\n";
    } 	
	$surveysummary .= "\t<tr $showstyle id='surveydetails10'><td align='right' valign='top'>$setfont<strong>"
					. _("Status:")."</strong></font></td>\n"
					. "\t<td valign='top'>$setfont"
					. "<font size='1'>"._("Number of groups in survey:")." $sumcount2<br />\n"
					. _("Number of questions in survey:")." $sumcount3<br />\n";
	if ($activated == "N" && $sumcount3 > 0)
		{
		$surveysummary .= _("Survey is not currently active.")."<br />\n";
		}
	elseif ($activated == "Y")
		{
		$surveysummary .= _("Survey is currently active.")."<br />\n"
						. _("Survey table name is:")." 'survey_$surveyid'<br />";
		}
	else
		{
		$surveysummary .= _("Survey cannot be activated yet.")."<br />\n";
		if ($sumcount2 == 0) 
			{
			$surveysummary .= "\t<font color='green'>["._("You need to add groups")."]</font><br />";
			}
		if ($sumcount3 == 0)
			{
			$surveysummary .= "\t<font color='green'>["._("You need to add questions")."]</font>";
			}
		}
	$surveysummary .= "</font></font></td></tr>\n"
					. $surveysummary2
					. "</table>\n";
	}

if ($gid)
	{
	$sumquery4 = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid=$gid"; //Getting a count of questions for this survey
	$sumresult4 = $connect->Execute($sumquery4);
	$sumcount4 = $sumresult4->RecordCount();
	$grpquery ="SELECT * FROM {$dbprefix}groups WHERE gid=$gid ORDER BY group_name";
	$grpresult = db_execute_assoc($grpquery);
	$groupsummary = "<table width='100%' align='center' bgcolor='#DDDDDD' border='0'>\n";
	while ($grow = $grpresult->FetchRow())
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
					   . "\t\t\t\t\t<input type='image' src='$imagefiles/edit.png' title='' alt='". _("Edit Current Group")."' name='EditGroup' "
					   . "align='left'  onclick=\"window.open('$scriptname?action=editgroup&amp;sid=$surveyid&amp;gid=$gid', "
					   . "'_top')\"" 
					   . "onmouseout=\"hideTooltip()\"" 
                       . "onmouseover=\"showTooltip(event,'". _("Edit Current Group")."');return false\">\n" ;  
		if (($sumcount4 == 0 && $activated != "Y") || $activated != "Y") 
			{
			$groupsummary .= "\t\t\t\t\t<a href='$scriptname?action=delgroup&amp;sid=$surveyid&amp;gid=$gid'>"
						   . "<img src='$imagefiles/delete.png' alt='"
						   . _("Delete Current Group")."' name='DeleteWholeGroup' title='' align='left' border='0' hspace='0' "
						   . "onclick=\"return confirm('"._("Deleting this group will also delete any questions and answers it contains. Are you sure you want to continue?")."')\"" 
						   . "onmouseout=\"hideTooltip()\"" 
                           . "onmouseover=\"showTooltip(event,'". _("Delete Current Group")."');return false\"></a>";
			}
		else				
			{
			$groupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' align='left' border='0' hspace='0'>\n";
			}
		$groupsummary .= "<input type='image' src='$imagefiles/reorder.png' title=''" 
					   . "alt='"._("Reorder the questions of this group")."'name='ReorderQuestions' "
					   . "align='left' "
					   . "onclick=\"window.open('$scriptname?action=orderquestions&amp;sid=$surveyid&amp;gid=$gid', '_top')\"" 
					   . "onmouseout=\"hideTooltip()\"" 
                       . "onmouseover=\"showTooltip(event,'"._("Reorder the questions of this group")."');return false\">"
					   ."\t\t\t\t\t<input type='image' src='$imagefiles/exportsql.png' title=''" 
					   . "alt='". _("Export Current Group")."'name='ExportGroup' "
					   . "align='left' "
					   . "onclick=\"window.open('dumpgroup.php?sid=$surveyid&amp;gid=$gid', '_top')\"" 
					   . "onmouseout=\"hideTooltip()\"" 
                       . "onmouseover=\"showTooltip(event,'"._("Export Current Group")."');return false\">"
					   . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
					   . "\t\t\t\t\t</td>\n"
					   . "\t\t\t\t\t<td align='right' width='350'>\n";
		if (!$qid) 
			{
			$groupsummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' title='"
						   . _("Close this Group")."' alt='". _("Close this Group")."' align='right'  name='CloseSurveyWindow' "
						   . "onclick=\"window.open('$scriptname?sid=$surveyid', '_top')\">\n";
			}
		else 
			{
			$groupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='21' align='right' border='0' hspace='0'>\n";
			}
		$groupsummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/plus.gif' title='"
					   . _("Show Details of this Group")."' alt='". _("Show Details of this Group")."' name='MaximiseGroupWindow' "
					   . "align='right'  onclick='showdetails(\"showg\")'>"
					   . "\t\t\t\t\t<input type='image' src='$imagefiles/minus.gif' title='"
					   . _("Hide Details of this Group")."' alt='". _("Hide Details of this Group")."' name='MinimiseGroupWindow' "
					   . "align='right'  onclick='showdetails(\"hideg\")'>\n"
					   . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' align='right' border='0' hspace='0'>\n";
		if ($activated == "Y")
			{
			$groupsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' border='0' hspace='0' align='right'>\n";
			}
		else
			{
			$groupsummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/add.png' title=''"
						   . " alt='". _("Add New Question to Group")."' align='right' name='AddNewQuestion' "
						   . "onClick=\"window.open('$scriptname?action=addquestion&amp;sid=$surveyid&amp;gid=$gid', '_top')\"" 
						   . "onmouseout=\"hideTooltip()\"" 
                           . "onmouseover=\"showTooltip(event,'"._("Add New Question to Group")."');return false\">\n";
			}
		$groupsummary .= "\t\t\t\t\t$setfont<font size='1'><strong>"._("Questions").":</strong> <select name='qid' "
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
					   . _("Title:")."</strong></font></td>\n"
					   . "\t<td>"
					   . "$setfont{$grow['group_name']} ({$grow['gid']})</font></td></tr>\n"
					   . "\t<tr $gshowstyle id='surveydetails21'><td valign='top' align='right'>$setfont<strong>"
					   . _("Description:")."</strong></font></td>\n\t<td>";
					   if (trim($grow['description'])!='') {$groupsummary .="$setfont{$grow['description']}</font>";}
		$groupsummary .= "</td></tr>\n";
		}
	$groupsummary .= "\n</table>\n";
	}

if ($qid)
	{
	//Show Question Details
	$qrq = "SELECT * FROM {$dbprefix}answers WHERE qid=$qid ORDER BY sortorder, answer";
	$qrr = $connect->Execute($qrq);
	$qct = $qrr->RecordCount();
	$qrquery = "SELECT * FROM {$dbprefix}questions WHERE gid=$gid AND sid=$surveyid AND qid=$qid";
	$qrresult = db_execute_assoc($qrquery) or die($qrquery."<br />".$connect->ErrorMsg());
	$questionsummary = "<table width='100%' align='center' bgcolor='#EEEEEE' border='0'>\n";
	while ($qrrow = $qrresult->FetchRow())
		{
		$qrrow = array_map('htmlspecialchars', $qrrow);
		$questionsummary .= "\t<tr>\n"
						  . "\t\t<td colspan='2'>\n"
						  . "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
						  . "\t\t\t\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
						  . _("Question")."</strong> <font color='silver'>{$qrrow['question']}</font></font></td></tr>\n"
						  . "\t\t\t\t<tr bgcolor='#AAAAAA'>\n"
						  . "\t\t\t\t\t<td>\n"
						  . "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='31' height='20' border='0' hspace='0' align='left'>\n"
						  . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
						  . "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='60' height='20' border='0' hspace='0' align='left'>\n"
						  . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
						  . "\t\t\t\t\t<input type='image' src='$imagefiles/edit.png' title='' alt='". _("Edit Current Question")."' align='left' name='EditQuestion' "
						  . "onclick=\"window.open('$scriptname?action=editquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid', '_top')\"" 
						  . "onmouseout=\"hideTooltip()\"" 
                          . "onmouseover=\"showTooltip(event,'". _("Edit Current Question")."');return false\">\n";
		if (($qct == 0 && $activated != "Y") || $activated != "Y") 
			{
			$questionsummary .= "\t\t\t\t\t<a href='$scriptname?action=delquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid'>"
							  . "<img src='$imagefiles/delete.png' name='DeleteWholeQuestion' alt= '"._("Delete Current Question")."' title='' "
							  ."align='left' border='0' hspace='0' "
							  . "onclick=\"return confirm('"._("Deleting this question will also delete any answers it includes. Are you sure you want to continue?")."')\"" 
							  . "onmouseout=\"hideTooltip()\"" 
                          	  . "onmouseover=\"showTooltip(event,'"._("Delete Current Question")."');return false\"></a>\n";
			}
		else {$questionsummary .= "\t\t\t\t\t<img src='$imagefiles/blank.gif' alt='' width='20' align='left' border='0' hspace='0'>\n";}
		$questionsummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/exportsql.png' title=''"
						  . "alt='". _("Export this Question")."'align='left' name='ExportQuestion' "
						  . "onclick=\"window.open('dumpquestion.php?qid=$qid', '_top')\"" 
						  . "onmouseout=\"hideTooltip()\"" 
                          . "onmouseover=\"showTooltip(event,'"._("Export this Question")."');return false\">\n"  
						  . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
						  . "\t\t\t\t\t<input type='image' src='$imagefiles/copy.png' title=''"
						  . " alt='". _("Copy Current Question")."' align='left' name='CopyQuestion' "
						  . "onclick=\"window.open('$scriptname?action=copyquestion&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid', '_top')\""
						  . "onmouseout=\"hideTooltip()\"" 
                          . "onmouseover=\"showTooltip(event,'". _("Copy Current Question")."');return false\">\n"
						  . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n"
						  . "\t\t\t\t\t<input type='image' src='$imagefiles/conditions.png' title='' " 
						  . "alt='". _("Set Conditions for this Question")."' align='left' name='SetQuestionConditions' "
						  . "onClick=\"window.open('".$homeurl."/conditions.php?sid=$surveyid&amp;qid=$qid', 'conditions', 'menubar=no, location=no, status=no, height=475, width=560, scrollbars=yes, resizable=yes, left=50, top=50')\"" 
						  . "onmouseout=\"hideTooltip()\"" 
                          . "onmouseover=\"showTooltip(event,'"._("Set Conditions for this Question")."');return false\">\n"
						  . "\t\t\t\t\t<img src='$imagefiles/seperator.gif' alt='' border='0' hspace='0' align='left'>\n";
		if ($qrrow['type'] == "O" || $qrrow['type'] == "L" || $qrrow['type'] == "!" || $qrrow['type'] == "!" || $qrrow['type'] == "M" || $qrrow['type'] == "Q" || $qrrow['type']=="A" || $qrrow['type'] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "E" || $qrrow['type'] == "F" || $qrrow['type'] == "H" || $qrrow['type'] == "P" || $qrrow['type'] == "R") 
			{
			$questionsummary .= "\t\t\t\t\t<input type='image' src='$imagefiles/answers.png' title='"
							  . _("Edit/Add Answers for this Question")."' align='left' name='ViewAnswers' "
							  . "onClick=\"window.open('".$scriptname."?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;viewanswer=Y', '_top')\"" 
							  . "onmouseout=\"hideTooltip()\"" 
                              . "onmouseover=\"showTooltip(event,'"._("Edit/Add Answers for this Question")."');return false\">\n" ; 
			}
		$questionsummary .= "\t\t\t\t\t</td>\n"
						  . "\t\t\t\t\t<td align='right' width='330'>\n"
						  . "\t\t\t\t\t<input type='image' src='$imagefiles/close.gif' title='"
						  . _("Close this Question")."' alt='". _("Close this Question")."' align='right' name='CloseQuestionWindow' "
						  . "onclick=\"window.open('$scriptname?sid=$surveyid&amp;gid=$gid', '_top')\">\n"
						  . "\t\t\t\t\t<input type='image' src='$imagefiles/plus.gif' title='"
						  . _("Show Details of this Question")."'  alt='". _("Show Details of this Question")."'align='right'  name='MaximiseQuestionWindow' "
						  . "onclick='showdetails(\"showq\")'>"
						  . "\t\t\t\t\t<input type='image' src='$imagefiles/minus.gif' title='"
						  . _("Hide Details of this Question")."'  alt='". _("Hide Details of this Question")."'align='right'  name='MinimiseQuestionWindow' "
						  . "onclick='showdetails(\"hideq\")'>\n"
						  . "\t\t\t\t</td></tr>\n"
						  . "\t\t\t</table>\n"
						  . "\t\t</td>\n"
						  . "\t</tr>\n";
		if (returnglobal('viewanswer'))	{$qshowstyle = "style='display: none'";}
		else							{$qshowstyle = "";}
		$questionsummary .= "\t<tr $qshowstyle id='surveydetails30'><td width='20%' align='right'>$setfont<strong>"
						  . _("Code:")."</strong></font></td>\n"
						  . "\t<td>$setfont{$qrrow['title']}";
		if ($qrrow['mandatory'] == "Y") {$questionsummary .= ": (<i>"._("Mandatory Question")."</i>)";}
		else {$questionsummary .= ": (<i>"._("Optional Question")."</i>)";}
		$questionsummary .= "</font></td></tr>\n"
						  . "\t<tr $qshowstyle id='surveydetails31'><td align='right' valign='top'>$setfont<strong>"
						  . _("Question:")."</strong></font></td>\n\t<td>$setfont{$qrrow['question']}</font></td></tr>\n"
						  . "\t<tr $qshowstyle id='surveydetails32'><td align='right' valign='top'>$setfont<strong>"
						  . _("Help:")."</strong></font></td>\n\t<td>";
						  if (trim($qrrow['help'])!=''){$questionsummary .= "$setfont{$qrrow['help']}</font>";}
		$questionsummary .= "</td></tr>\n";
		if ($qrrow['preg'])
			{
		    $questionsummary .= "\t<tr $qshowstyle id='surveydetails33'><td align='right' valign='top'>$setfont<strong>"
							  . _("Validation:")."</strong></font></td>\n\t<td>$setfont{$qrrow['preg']}"
							  . "</font></td></tr>\n";
			}
		$qtypes = getqtypelist("", "array"); //qtypes = array(type code=>type description)
		$questionsummary .= "\t<tr $qshowstyle id='surveydetails34'><td align='right' valign='top'>$setfont<strong>"
						  ._("Type:")."</strong></font></td>\n\t<td>$setfont{$qtypes[$qrrow['type']]}";
		if ($qrrow['type'] == "F" ||$qrrow['type'] == "H") 
			{
			$questionsummary .= " (LID: {$qrrow['lid']}) "
							  . "<input align='top' type='image' src='$imagefiles/labels.png' title='"
							  . _("Edit/Add Label Sets")."' height='15' width='15' hspace='0' name='EditThisLabelSet' "
							  . "onClick=\"window.open('labels.php?lid={$qrrow['lid']}', '_blank')\">\n";
			}
		$questionsummary .="</font></td></tr>\n";
		if ($qct == 0 && ($qrrow['type'] == "O" || $qrrow['type'] == "L" || $qrrow['type'] == "!" || $qrrow['type'] == "M" || $qrrow['type'] == "Q" || $qrrow['type'] == "A" || $qrrow['type'] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "E" || $qrrow['type'] == "P" || $qrrow['type'] == "R" || $qrrow['type'] == "F" ||$qrrow['type'] == "H"))
			{
			$questionsummary .= "\t\t<tr $qshowstyle id='surveydetails35'><td></td><td>"
							 . "<font face='verdana' size='1' color='green'>"
							 . _("Warning").": ". _("You need to add answers to this question")." "
							 . "<input type='image' src='$imagefiles/answers.png' title='"
							 . _("Edit/Add Answers for this Question")."' border='0' hspace='0' name='EditThisQuestionAnswers'"
							 . "onClick=\"window.open('".$scriptname."?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;viewanswer=Y', '_top')\"></font></td></tr>\n";
			}
		if (!$qrrow['lid'] && ($qrrow['type'] == "F" ||$qrrow['type'] == "H"))
			{
			$questionsummary .= "\t\t<tr $qshowstyle id='surveydetails36'><td></td>"
							  . "<td><font face='verdana' size='1' color='green'>"
							  . _("Warning").": "._("You need to choose a Label Set for this question")."</font></td></tr>\n";
			}
		if ($qrrow['type'] == "M" or $qrrow['type'] == "P")
			{
			$questionsummary .= "\t<tr $qshowstyle id='surveydetails37'>"
							  . "<td align='right' valign='top'>$setfont<strong>"
							  . _("Other:")."</strong></font></td>\n"
							  . "\t<td>$setfont{$qrrow['other']}</td></tr>\n";
			}
		if ($qrrow['type'] == "J" || $qrrow['type'] == "I")
			{
			if ($action == "insertCSV")
				{
				$questionsummary .= "\t\t<tr $qshowstyle id='surveydetails37'><td></td><td>"
							 . "<font face='verdana' size='2' color='green'><b>
							 ". _("Upload completed")."</font></b></td></tr>\n";
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
	$qresult = db_execute_assoc($qquery);
	while ($qrow=$qresult->FetchRow()) {$qtype=$qrow['type'];}
	if (!isset($_POST['ansaction']))
		{
		//check if any nulls exist. If they do, redo the sortorders
		$caquery="SELECT * FROM {$dbprefix}answers WHERE qid=$qid AND sortorder is null";
		$caresult=$connect->Execute($caquery);
		$cacount=$caresult->RecordCount();
		if ($cacount)
			{
			fixsortorder($qid);
			}
		}
	$vasummary  = "<table width='100%' align='center' border='0' bgcolor='#EEEEEE'>\n"
				. "<tr bgcolor='#555555'><td colspan='5'><font size='1' color='white'><strong>"
				. _("Answers")."</strong></font></td></tr>\n";
	$cdquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$qid ORDER BY sortorder, answer";
	$cdresult = db_execute_assoc($cdquery);
	$cdcount = $cdresult->RecordCount();
	$vasummary .= "\t<tr><th width='10%'>$setfont"._("Code")."</font></th><th width='50%'>$setfont"._("Answer")."</font></th>"
				. "<th width='10%'>$setfont"._("Default")."</font></th><th width='15%'>$setfont"._("Action")."</font></th>"
				. "<th>$setfont"._("Move")."</font></th></tr>\n";
	$position=0;
	while ($cdrow = $cdresult->FetchRow())
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
			$vasummary .= ">"._("Yes")."</option>\n"
						. "\t\t\t\t<option value='N'";
			if ($cdrow['default_value'] != "Y") {$vasummary .= " selected";};
			$vasummary .= ">"._("No")."</option>\n"
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
						. "\t\t\t<input name='ansaction' $btstyle type='submit' value='"._("Save")."'>"
						. "<input name='ansaction' $btstyle type='submit' value='"._("Del")."'>\n"
						. "\t\t</td>\n";
			}
		else
			{
			$vasummary .= "\t\t<td align='center' width='15%'><input name='ansaction' "
						. "$btstyle type='submit' value='"._("Save")."'></td>\n";
			}
		$vasummary .= "\t\t<td align='center'>";
		if ($position > 0) {$vasummary .= "<input name='ansaction' $btstyle type='submit' value='"._("Up")."'>";}
		else {$vasummary .= "&nbsp;&nbsp;&nbsp;&nbsp;";}
		if ($position < $cdcount-1) {$vasummary .= "<input name='ansaction' $btstyle type='submit' value='"._("Dn")."'>";}
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
					. "\t\t\t\t<option value='Y'>"._("Yes")."</option>\n"
					. "\t\t\t\t<option value='N' selected>"._("No")."</option>\n"
					. "\t\t\t</select></td>\n"
					. "\t\t<td align='center' width='15%'><input name='ansaction' $btstyle type='submit' value='"._("Add")."'></td>\n"
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
					. "<input $btstyle type='submit' name='ansaction' value='"._("Sort Alpha")."'>\n"
					. "\t<input type='hidden' name='sid' value='$surveyid'>\n"
					. "\t<input type='hidden' name='gid' value='$gid'>\n"
					. "\t<input type='hidden' name='qid' value='$qid'>\n"
					. "\t<input type='hidden' name='action' value='modanswer'>\n"
					. "\t<input type='hidden' name='viewanswer' value='Y'></form>\n</td>"
					. "\t<td align='center'>\n"
					. "\t<form style='margin-bottom:0;' action='".$scriptname."' method='post'>"
					. "<input $btstyle type='submit' name='ansaction' value='"._("Fix Sort")."'>\n"
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
	$mur = db_execute_assoc($muq);
	$usersummary .= "\t<tr><form action='$scriptname' method='post'>";
	while ($mrw = $mur->FetchRow())
		{
		$mrw = array_map('htmlspecialchars', $mrw);
		$usersummary .= "\t<td>$setfont<strong>{$mrw['user']}</strong></font>\n"
					  . "\t\t<input type='hidden' name='user' value=\"{$mrw['user']}\"></td>\n"
					  . "\t<td>\n\t\t<input $slstyle type='text' name='pass' value=\"{$mrw['password']}\"></td>\n"
					  . "\t<td>\n\t\t<input $slstyle type='text' size='2' name='level' value=\"{$mrw['security']}\"></td>\n";
		}
	$usersummary .= "\t</tr>\n\t<tr><td colspan='3' align='center'>\n"
				  . "\t\t<input type='submit' $btstyle value='"._("Update")."'>\n"
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
					 . "<font size='1' face='verdana' color='white'><strong>"._("User Control")."</strong></font></td></tr>\n"
					 . "\t<tr>\n"
					 . "\t\t<td>\n"
					 . "\t\t\t$setfont<font color='RED'><strong>"._("Warning")."</strong></font></font><br />\n"
					 . "\t\t\t"._("You have not yet initialised security settings for your survey system and subsequently there are no restrictions on access.</p>\nIf you click on the 'initialise security' button below, standard APACHE security settings will be added to the administration directory of this script. You will then need to use the default access username and password to access the administration and data entry scripts.")."\n"
					 . "\t\t\t<p>"._("Username").": $defaultuser<br />"._("Password").": $defaultpass</p>\n"
					 . "\t\t\t<p>"._("It is highly recommended that once your security system has been initialised you change this default password.")."</p>\n"
					 . "\t\t</td>\n"
					 . "\t</tr>\n"
					 . "\t<tr>\n"
					 . "\t\t<td align='center'>\n"
					 . "\t\t\t<input type='submit' $btstyle value='"._("Initialise Security")."' "
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
					 . "<font size='1' face='verdana' color='white'><strong>"._("User Control")."</strong></td></tr>\n"
					 . "\t<tr>\n"
					 . "\t\t<th>$setfont"._("User")."</th>\n"
					 . "\t\t<th>$setfont"._("Password")."</font></th>\n"
					 . "\t\t<th>$setfont"._("Security")."</font></th>\n"
					 . "\t\t<th>$setfont"._("Action")."</font></th>\n"
					 . "\t</tr>\n";
		$userlist = getuserlist();
		$ui = count($userlist);
		if ($ui < 1)
			{
			$usersummary .= "\t<tr>\n"
						 . "\t\t<td>\n"
						 . "\t\t\t<center>"._("Warning").": "._("No users exist in your table. We recommend you 'turn off' security. You can then 'turn it on' again.")."</center>"
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
							  . "\t\t\t<input type='submit' $btstyle value='"._("Edit")."' "
							  . "onClick=\"window.open('$scriptname?action=modifyuser&user={$usr['user']}', '_top')\" />\n";
				if ($ui > 1 )
					{
					$usersummary .= "\t\t\t<input type='submit' $btstyle value='"._("Delete")."' "
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
					  . "\t\t<td align='center'><input type='submit' $btstyle value='"._("Add User")."'></td>\n"
					  . "\t</tr>\n"
					  . "\t<tr>\n"
					  . "\t\t<td align='center'><input type='hidden' name='action' value='adduser'></td>\n"
					  . "\t</tr>\n"
					  . "\t</form>\n"
					  . "\t<tr>\n"
					  . "\t\t<td colspan='3'></td>\n"
					  . "\t\t<td align='center'><input type='submit' $btstyle value='"._("Turn Off Security")."' "
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
				  . "\t\t<strong>$setfont<font color='white'>"._("Add Question")."\n"
				  . "\t\t</font></font></strong></td>\n"
				  . "\t</tr>\n"
				  . "\t<tr>\n"
				  . "\t\t<td align='right'  width='35%'>$setfont<strong>"._("Code:")."</strong></font></td>\n"
				  . "\t\t<td><input $slstyle type='text' size='20' name='title'>"
				  . "<font color='red' face='verdana' size='1'>"._("Required")."</font></td></tr>\n"
				  . "\t<tr>\n"
				  . "\t\t<td align='right' width='35%'>$setfont<strong>"._("Question:")."</strong></font></td>\n"
				  . "\t\t<td><textarea $slstyle2 cols='50' rows='3' name='question'></textarea></td>\n"
				  . "\t</tr>\n"
				  . "\t<tr>\n"
				  . "\t\t<td align='right' width='35%'>$setfont<strong>"._("Help:")."</strong></font></td>\n"
				  . "\t\t<td><textarea $slstyle2 cols='50' rows='3' name='help'></textarea></td>\n"
				  . "\t</tr>\n"
				  . "\t<tr>\n"
				  . "\t\t<td align='right' width='35%'>$setfont<strong>"._("Type:")."</strong></font></td>\n"
				  . "\t\t<td><select $slstyle name='type' id='question_type' "
				  . "onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n"
				  . "$qtypeselect"
				  . "\t\t</select></td>\n"
				  . "\t</tr>\n";

	$newquestion .= "\t<tr id='Validation'>\n"
				  . "\t\t<td align='right'>$setfont<strong>"._("Validation:")."</strong></font></td>\n"
				  . "\t\t<td>$setfont\n"
				  . "\t\t<input type='text' $slstyle name='preg' size=50></font>\n"
				  . "\t\t</td>\n"
				  . "\t</tr>\n";
	
	$newquestion .= "\t<tr id='LabelSets' style='display: none'>\n"
				  . "\t\t<td align='right'>$setfont<strong>"._("Label Set:")."</strong></font></td>\n"
				  . "\t\t<td>$setfont\n"
				  . "\t\t<select name='lid' $slstyle>\n";
	$labelsets=getlabelsets();
	if (count($labelsets)>0)
		{
		$newquestion .= "\t\t\t<option value=''>"._("Please Choose...")."</option>\n";
		foreach ($labelsets as $lb)
			{
			$newquestion .= "\t\t\t<option value='{$lb[0]}'>{$lb[1]}</option>\n";
			}
		}
	$newquestion .= "\t\t</select>\n"
				  . "\t\t</font></td>\n"
				  . "\t</tr>\n";
				  
	$newquestion .= "\t<tr id='OtherSelection' style='display: none'>\n"
				  . "\t\t<td align='right'>$setfont<strong>"._("Other:")."</strong></font></td>\n"
				  . "\t\t<td>$setfont\n"
				  . "\t\t\t<label for='OY'>"._("Yes")."</label>"
				  . "<input id='OY' type='radio' name='other' value='Y' />&nbsp;&nbsp;\n"
				  . "\t\t\t<label for='ON'>"._("No")."</label>"
				  . "<input id='ON' type='radio' name='other' value='N' checked />\n"
				  . "\t\t</font></td>\n"
				  . "\t</tr>\n";

	$newquestion .= "\t<tr id='MandatorySelection'>\n"
				  . "\t\t<td align='right'>$setfont<strong>"._("Mandatory:")."</strong></font></td>\n"
				  . "\t\t<td>$setfont\n"
				  . "\t\t\t<label for='MY'>"._("Yes")."</label>"
				  . "<input id='MY' type='radio' name='mandatory' value='Y' />&nbsp;&nbsp;\n"
				  . "\t\t\t<label for='MN'>"._("No")."</label>"
				  . "<input id='MN' type='radio' name='mandatory' value='N' checked />\n"
				  . "\t\t</font></td>\n"
				  . "\t</tr>\n";
	
	//Question attributes
	$qattributes=questionAttributes();

	$newquestion .= "\t<tr id='QTattributes'>
						<td align='right'>{$setfont}<strong>"._("Question Attributes:")."</strong></font></td>
						<td><select id='QTlist' name='attribute_name' $slstyle>
						</select>
						<input type='text' id='QTtext' name='attribute_value' $slstyle></td></tr>\n";
	$newquestion .= "\t<tr>\n"
				  . "\t\t<td colspan='2' align='center'>";
	
	if (isset($eqrow)) {$newquestion .= questionjavascript($eqrow['type'], $qattributes);}
	else {$newquestion .= questionjavascript('', $qattributes);}

	$newquestion .= "<input type='submit' $btstyle value='"
				  . _("Add Question")."' />\n"
				  . "\t\n"
				  . "\t<input type='hidden' name='action' value='insertnewquestion' />\n"
				  . "\t<input type='hidden' name='sid' value='$surveyid' />\n"
				  . "\t<input type='hidden' name='gid' value='$gid' />\n"
				  . "</td></tr></table>\n"
				  . "\t</form>\n"
				  . "\t<form enctype='multipart/form-data' name='importquestion' action='$scriptname' method='post'>\n"
				  . "<table width='100%' border='0' >\n\t"
				  . "<tr><td colspan='2' align='center'>$setfont<strong>"._("OR")."</strong></font></td></tr>\n"
				  . "<tr><td colspan='2' bgcolor='black' align='center'>\n"
				  . "\t\t<strong>$setfont<font color='white'>"._("Import Question")."</font></font></strong></td></tr>\n\t<tr>"
				  . "\t\t<td align='right' width='35%'>$setfont<strong>"._("Select SQL File:")."</strong></font></td>\n"
				  . "\t\t<td><input $slstyle name=\"the_file\" type=\"file\" size=\"50\"></td></tr>\n"
				  . "\t<tr><td colspan='2' align='center'><input type='submit' "
				  . "$btstyle value='"._("Import Question")."'>\n"
				  . "\t<input type='hidden' name='action' value='importquestion'>\n"
				  . "\t<input type='hidden' name='sid' value='$surveyid'>\n"
				  . "\t<input type='hidden' name='gid' value='$gid'>\n"
				  . "\t</td></tr></table></form>\n\n";
	}

if ($action == "copyquestion")
	{
	$eqquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid=$gid AND qid=$qid";
	$eqresult = db_execute_assoc($eqquery);
	$qattributes=questionAttributes();
	while ($eqrow = $eqresult->FetchRow())
		{
		$eqrow = array_map('htmlspecialchars', $eqrow);
		$editquestion = "<form action='$scriptname' name='editquestion' method='post'>\n<table width='100%' border='0'>\n"
					  . "\t<tr>\n"
					  . "\t\t<td colspan='2' bgcolor='black' align='center'>\n"
					  . "\t\t\t$setfont<font color='white'><strong>"._("Copy Question")."</strong><br />"._("Note: You MUST enter a new question code")."</font></font>\n"
					  . "\t\t</td>\n"
					  . "\t</tr>\n"
					  . "\t<tr>\n"
					  . "\t\t<td align='right'>$setfont<strong>"._("Code:")."</strong></font></td>\n"
					  . "\t\t<td><input $slstyle type='text' size='20' name='title' value='' /></td>\n"
					  . "\t</tr>\n"
					  . "\t<tr>\n"
					  . "\t\t<td align='right' valign='top'>$setfont<strong>"._("Question:")."</strong></font></td>\n"
					  . "\t\t<td><textarea $slstyle2 cols='50' rows='4' name='question'>{$eqrow['question']}</textarea></td>\n"
					  . "\t</tr>\n"
					  . "\t<tr>\n"
					  . "\t\t<td align='right' valign='top'>$setfont<strong>"._("Help:")."</strong></font></td>\n"
					  . "\t\t<td><textarea $slstyle2 cols='50' rows='4' name='help'>{$eqrow['help']}</textarea></td>\n"
					  . "\t</tr>\n"
					  . "\t<tr>\n"
					  . "\t\t<td align='right'>$setfont<strong>"._("Type:")."</strong></font></td>\n"
					  . "\t\t<td><select $slstyle name='type' onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n"
					  . getqtypelist($eqrow['type'])
					  . "\t\t</select></td>\n"
					  . "\t</tr>\n";

		$editquestion .= "\t<tr id='Validation'>\n"
					  . "\t\t<td align='right'>$setfont<strong>"._("Validation:")."</strong></font></td>\n"
					  . "\t\t<td>$setfont\n"
					  . "\t\t<input type='text' $slstyle name='preg' size=50 value=\"".$eqrow['preg']."\">\n"
					  . "\t\t</font></td>\n"
					  . "\t</tr>\n";

		$editquestion .= "\t<tr id='LabelSets' style='display: none'>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._("Label Set:")."</strong></font></td>\n"
					   . "\t\t<td>$setfont\n"
					   . "\t\t<select name='lid' $slstyle>\n";
		$labelsets=getlabelsets();
		if (count($labelsets)>0)
			{
			if (!$eqrow['lid'])
				{
				$editquestion .= "\t\t\t<option value=''>"._("Please Choose...")."</option>\n";
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
					   . "\t\t<td align='right'>$setfont<strong>"._("Group:")."</strong></font></td>\n"
					   . "\t\t<td><select $slstyle name='gid'>\n"
					   . getgrouplist3($eqrow['gid'])
					   . "\t\t\t</select></td>\n"
					   . "\t</tr>\n";
		
		$editquestion .= "\t<tr id='OtherSelection' style='display: none'>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._("Other:")."</strong></font></td>\n";
		
		$editquestion .= "\t\t<td>$setfont\n"
					   . "\t\t\t"._("Yes")." <input type='radio' name='other' value='Y'";
		if ($eqrow['other'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n"
					   . "\t\t\t"._("No")." <input type='radio' name='other' value='N'";
		if ($eqrow['other'] == "N") {$editquestion .= " checked";}
		$editquestion .= " />\n"
					   . "\t\t</font></td>\n"
					   . "\t</tr>\n";

		$editquestion .= "\t<tr id='MandatorySelection'>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._("Mandatory:")."</strong></font></td>\n"
					   . "\t\t<td>$setfont\n"
					   . "\t\t\t"._("Yes")." <input type='radio' name='mandatory' value='Y'";
		if ($eqrow['mandatory'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n"
					   . "\t\t\t"._("No")." <input type='radio' name='mandatory' value='N'";
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
						   . "\t\t<td colspan='2' align='center'><input type='submit' $btstyle value='"._("Copy Question")."'></td>\n"
						   . "\t\t<input type='hidden' name='action' value='copynewquestion'>\n"
						   . "\t\t<input type='hidden' name='sid' value='$sid' />\n"
						   . "\t\t<input type='hidden' name='oldqid' value='$qid' />\n"
						   . "\t</form></tr>\n"
						   . "</table>\n";	
			}
		else 							   	
			{	
		
		$editquestion .= "$setfont<strong>"._("Copy Answers?")."</strong></font></td>\n"
					   . "\t\t<td>$setfont<input type='checkbox' checked name='copyanswers' value='Y' />"
					   . "</font></td>\n"
					   . "\t</tr>\n"
					   . "\t<tr>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._("Copy Attributes?")."</strong></font></td>\n"
					   . "\t\t<td>$setfont<input type='checkbox' checked name='copyattributes' value='Y' />"
					   . "</font></td>\n"
					   . "\t</tr>\n"
					   . "\t<tr>\n"
					   . "\t\t<td colspan='2' align='center'><input type='submit' $btstyle value='"._("Copy Question")."'>\n"
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
	$eqresult = db_execute_assoc($eqquery);
	while ($eqrow = $eqresult->FetchRow())
		{
		$eqrow  = array_map('htmlspecialchars', $eqrow);
		$editquestion = "<tr><td>\n"
						."<table width='100%' border='0' bgcolor='#EEEEEE'><tr>"
					    . "<td colspan='3' bgcolor='black' align='center'>"
					    . "\t\t\t$setfont<font color='white'><strong>"._("Edit Question")." $qid</strong></font></font>\n"
					    . "\t\t</td>\n"
					    . "\t</tr>\n"
					    . "\t<tr>\n"
					    . "\t\t<td valign='top'><form action='$scriptname' name='editquestion' method='post'><table width='100%' border='0'>\n"
					    . "\t<tr>\n"
					    . "\t\t<td align='right'>$setfont<strong>"._("Code:")."</strong></font></td>\n"
					    . "\t\t<td><input $slstyle type='text' size='20' name='title' value=\"{$eqrow['title']}\"></td>\n"
					    . "\t</tr>\n"
					    . "\t<tr>\n"
					    . "\t\t<td align='right' valign='top'>$setfont<strong>"._("Question:")."</strong></font></td>\n"
					    . "\t\t<td><textarea $slstyle2 cols='50' rows='4' name='question'>{$eqrow['question']}</textarea></td>\n"
					    . "\t</tr>\n"
					    . "\t<tr>\n"
					    . "\t\t<td align='right' valign='top'>$setfont<strong>"._("Help:")."</strong></font></td>\n"
					    . "\t\t<td><textarea $slstyle2 cols='50' rows='4' name='help'>{$eqrow['help']}</textarea></td>\n"
					    . "\t</tr>\n";
		//question type:
		$editquestion .= "\t<tr>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._("Type:")."</strong></font></td>\n";
		if ($activated != "Y")
			{
			$editquestion .= "\t\t<td><select $slstyle id='question_type' name='type' "
						   . "onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n"
						   . getqtypelist($eqrow['type'])
						   . "\t\t</select></td>\n";
			}
		else
			{
			$editquestion .= "\t\t<td>{$setfont}[{$eqrow['type']}] - "._("Cannot be modified")." - "._("Survey is currently active.")."\n"
						   . "\t\t\t<input type='hidden' name='type' id='question_type' value='{$eqrow['type']}'>\n"
						   . "\t\t</font></td>\n";
			}

		$editquestion .= "\t<tr id='Validation'>\n"
					  . "\t\t<td align='right'>$setfont<strong>"._("Validation:")."</strong></font></td>\n"
					  . "\t\t<td>$setfont\n"
					  . "\t\t<input type='text' $slstyle name='preg' size=50 value=\"".$eqrow['preg']."\">\n"
					  . "\t\t</font></td>\n"
					  . "\t</tr>\n";

		$editquestion  .="\t<tr id='LabelSets' style='display: none'>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._("Label Set:")."</strong></font></td>\n"
					   . "\t\t<td>$setfont\n";
		if ($activated != "Y")
			{
			$editquestion .= "\t\t<select name='lid' $slstyle>\n";
			$labelsets=getlabelsets();
			if (count($labelsets)>0)
				{
				if (!$eqrow['lid'])
					{
					$editquestion .= "\t\t\t<option value=''>"._("Please Choose...")."</option>\n";
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
			$editquestion .= "[{$eqrow['lid']}] - "._("Cannot be modified")." - "._("Survey is currently active.")."\n"
						   . "\t\t\t<input type='hidden' name='lid' value=\"{$eqrow['lid']}\">\n";
			}
		$editquestion .= "\t\t</font></td>\n"
					   . "\t</tr>\n"
					   . "\t<tr>\n"
					   . "\t<td align='right'>$setfont<strong>"._("Group:")."</strong></font></td>\n"
					   . "\t\t<td><select $slstyle name='gid'>\n"
					   . getgrouplist3($eqrow['gid'])
					   . "\t\t</select></td>\n"
					   . "\t</tr>\n";
		$editquestion .= "\t<tr id='OtherSelection'>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._("Other:")."</strong></font></td>\n";
		if ($activated != "Y") 
			{
			$editquestion .= "\t\t<td>$setfont\n"
						   . "\t\t\t<label for='OY'>"._("Yes")."</label><input id='OY' type='radio' name='other' value='Y'";
			if ($eqrow['other'] == "Y") {$editquestion .= " checked";}
			$editquestion .= " />&nbsp;&nbsp;\n"
						   . "\t\t\t<label for='ON'>"._("No")."</label><input id='ON' type='radio' name='other' value='N'";
			if ($eqrow['other'] == "N") {$editquestion .= " checked";}
			$editquestion .= " />\n"
						   . "\t\t</font></td>\n";
			}
		else
			{
			$editquestion .= "<td>$setfont [{$eqrow['other']}] - "._("Cannot be modified")." - "._("Survey is currently active.")."\n"
						   . "\t\t\t<input type='hidden' name='other' value=\"{$eqrow['other']}\"></font></td>\n";
			}
		$editquestion .= "\t</tr>\n";

		$editquestion .= "\t<tr id='MandatorySelection'>\n"
					   . "\t\t<td align='right'>$setfont<strong>"._("Mandatory:")."</strong></font></td>\n"
					   . "\t\t<td>$setfont\n"
					   . "\t\t\t<label for='MY'>"._("Yes")."</label><input id='MY' type='radio' name='mandatory' value='Y'";
		if ($eqrow['mandatory'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n"
					   . "\t\t\t<label for='MN'>"._("No")."</label><input id='MN' type='radio' name='mandatory' value='N'";
		if ($eqrow['mandatory'] != "Y") {$editquestion .= " checked";}
		$editquestion .= " />\n"
					   . "\t\t</font></td>\n"
					   . "\t</tr>\n";
		$qattributes=questionAttributes();
		
		$editquestion .= "\t<tr>\n"
					   . "\t\t<td colspan='2' align='center'>"
					   . "<input type='submit' $btstyle value='"._("Update Question")."'>\n"
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
						    <th colspan='4'>{$setfont}"._("Question Attributes:")."</font></th>
   					      </tr>
						  <tr><th colspan='4' height='5'></th></tr>
                          <tr>  			  
						  <td nowrap width='50%' ><select id='QTlist' name='attribute_name' $slstyle>
						  </select></td><td align='center' width='20%'><input type='text' id='QTtext' size='6' name='attribute_value' $slstyle></td>
						  <td align='center'><input type='submit' value='"._("Add")."' $btstyle>
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
					   ._("Save")."' />
					   <input type='hidden' name='action' value='editattribute'>\n
					   <input type='hidden' name='sid' value='$surveyid'>\n
					   <input type='hidden' name='gid' value='$gid'>\n
					   <input type='hidden' name='qid' value='$qid'>\n
					   <input type='hidden' name='qaid' value='".$qa['qaid']."'>\n"
					   ."\t\t\t</td></tr></table></form></td><td>
					   <form action='$scriptname' method='post'><table width='100%'><tr><td width='5%'>
					   <input type='submit' $btstyle value='"
					   ._("Delete")."' />"
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
//Constructing the drag and drop interface here... 
if($action == "orderquestions")
	{
		
	$oqquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND gid=$gid order by title" ; 
	$oqresult = mysql_query($oqquery) ; 
	$orderquestions ="<p align='left'>" ;
	$orderquestions="<ul id='arrangableNodes'>" ; 
	while($oqrow = mysql_fetch_array($oqresult))
	{
		       $oqrow = array_map('htmlspecialchars',$oqrow) ; 	       
		       $orderquestions.= "<li id='".$oqrow['qid']."'>".$oqrow['question']."</li>" ;
	}
	 
	$orderquestions .="</ul>" ; 
    $orderquestions .="<a href=\"#\" onclick=\"saveArrangableNodes();return false\" class=\"saveOrderbtn\">&nbsp;"._Q_SAVEORD."&nbsp;</a>" ;
    				   
	$orderquestions .="<div id=\"movableNode\"><ul></ul></div>	
			   		   <div id=\"arrDestInditcator\"><img src=\"images/insert.gif\"></div>
        			   <div id=\"arrDebug\"></div>" ; 					 
	//    $orderquestions .="<a href='javascript:testjs()'>test</a>" ; 
	$orderquestions .= "<form action='$scriptname' name='orderquestions' method='post'>
						<input type='hidden' name='hiddenNodeIds'>
						<input type='hidden' name='action' value='reorderquestions'> 
						<input type='hidden' name='gid' value='$gid'>
						<input type='hidden' name='sid' value='$surveyid'>
						</form>" ; 
    $orderquestions .="</p>" ;			
		
	}

if ($action == "addgroup")
	{
	$newgroup = "<tr><td><form action='$scriptname' name='addnewgroup' method='post'><table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n"
			   . "\t\t<strong>$setfont<font color='white'>"._("Add Group")."</font></font></strong></td></tr>\n"
			   . "\t<tr>\n"
			   . "\t\t<td align='right'>$setfont<strong>"._("Title:")."</strong></font></td>\n"
			   . "\t\t<td><input $slstyle type='text' size='50' name='group_name'><font color='red' face='verdana' size='1'>"._("Required")."</font></td></tr>\n"
			   . "\t<tr><td align='right'>$setfont<strong>"._("Description:")."</strong>("._("Optional").")</font></td>\n"
			   . "\t\t<td><textarea $slstyle2 cols='50' rows='4' name='description'></textarea></td></tr>\n"
			   . "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='"._("Add Group")."'>\n"
			   . "\t<input type='hidden' name='action' value='insertnewgroup'>\n"
			   . "\t<input type='hidden' name='sid' value='$surveyid'>\n"
			   . "\t</td></table>\n"
			   . "</form></td></tr>\n"
			   . "<tr><td align='center'>$setfont<strong>"._("OR")."</strong></font></td></tr>\n"
			   . "<tr><td><form enctype='multipart/form-data' name='importgroup' action='$scriptname' method='post'>"
			   . "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n"
			   . "\t\t<strong>$setfont<font color='white'>"._("Import Group")."</font></font></strong></td></tr>\n\t<tr>"
			   . "\t\n"
			   . "\t\t<td align='right'>$setfont<strong>"._("Select SQL File:")."</strong></font></td>\n"
			   . "\t\t<td><input $slstyle2 name=\"the_file\" type=\"file\" size=\"35\"></td></tr>\n"
			   . "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='"._("Import Group")."'>\n"
			   . "\t<input type='hidden' name='action' value='importgroup'>\n"
			   . "\t<input type='hidden' name='sid' value='$surveyid'>\n"
			   . "\t</td></tr>\n</table></form>\n";
	}

if ($action == "editgroup")
	{
	$egquery = "SELECT * FROM {$dbprefix}groups WHERE sid=$surveyid AND gid=$gid";
	$egresult = db_execute_assoc($egquery);
	while ($esrow = $egresult->FetchRow())	
		{
		$esrow = array_map('htmlspecialchars', $esrow);
		$editgroup =  "<form action='$scriptname' name='editgroup' method='post'>"
		 			. "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n"
					. "\t\t<strong>$setfont<font color='white'>"._("Edit Group for Survey ID")."($surveyid)</font></font></strong></td></tr>\n"
					. "\t<tr>\n"
					. "\t\t<td align='right' width='20%'>$setfont<strong>"._("Title:")."</strong></font></td>\n"
					. "\t\t<td><input $slstyle type='text' size='50' name='group_name' value=\"{$esrow['group_name']}\"></td></tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._("Description:")."</strong>(optional)</font></td>\n"
					. "\t\t<td><textarea $slstyle2 cols='50' rows='4' name='description'>{$esrow['description']}</textarea></td></tr>\n"
					. "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='"._("Update Group")."'>\n"
					. "\t<input type='hidden' name='action' value='updategroup'>\n"
					. "\t<input type='hidden' name='sid' value='$surveyid'>\n"
					. "\t<input type='hidden' name='gid' value='$gid'>\n"
					. "\t</td></tr>\n"
					. "</table>\n"
					. "\t</form>\n";
		}
	}


// Editing the survey
if ($action == "editsurvey")
	{
	$esquery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid";
	$esresult = db_execute_assoc($esquery);
	while ($esrow = $esresult->FetchRow())	
		{
		$esrow = array_map('htmlspecialchars', $esrow);
		$editsurvey = "<form name='addnewsurvey' action='$scriptname' method='post'>\n<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>"
					. "\t\t<strong>$setfont<font color='white'>Edit Survey</font></font></strong></td></tr>\n"
					. "\t<tr>"
					. "\t\t<td align='right' width='25%'>$setfont<strong>"._("Title:")."</strong></font></td>\n"
					. "\t\t<td><input $slstyle type='text' size='50' name='short_title' value=\"{$esrow['short_title']}\"></td></tr>\n"
					. "\t<tr><td align='right' valign='top'><strong>$setfont"._("Description:")."</font></strong></td>\n"
					. "\t\t<td><textarea $slstyle2 cols='50' rows='5' name='description'>{$esrow['description']}</textarea></td></tr>\n"
					. "\t<tr><td align='right' valign='top'>$setfont<strong>"._("Welcome:")."</strong></font></td>\n"
					. "\t\t<td><textarea $slstyle2 cols='50' rows='5' name='welcome'>".str_replace("<br />", "\n", $esrow['welcome'])."</textarea></td></tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._("Administrator:")."</strong></font></td>\n"
					. "\t\t<td><input $slstyle type='text' size='50' name='admin' value=\"{$esrow['admin']}\"></td></tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._("Admin Email:")."</strong></font></td>\n"
					. "\t\t<td><input $slstyle type='text' size='50' name='adminemail' value=\"{$esrow['adminemail']}\"></td></tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._("Fax To:")."</strong></font></td>\n"
					. "\t\t<td><input $slstyle type='text' size='50' name='faxto' value=\"{$esrow['faxto']}\"></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Format:")."</strong></font></td>\n"
					. "\t\t<td><select $slstyle name='format'>\n"
					. "\t\t\t<option value='S'";
		if ($esrow['format'] == "S" || !$esrow['format']) {$editsurvey .= " selected";}
		$editsurvey .= ">"._("Question by Question")."</option>\n"
					. "\t\t\t<option value='G'";
		if ($esrow['format'] == "G") {$editsurvey .= " selected";}
		$editsurvey .= ">"._("Group by Group")."</option>\n"
					. "\t\t\t<option value='A'";
		if ($esrow['format'] == "A") {$editsurvey .= " selected";}
		$editsurvey .= ">"._("All in one")."</option>\n"
					. "\t\t</select></td>\n"
					. "\t</tr>\n";
		//TEMPLATES
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Template:")."</strong></font></td>\n"
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
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Use Cookies?")."</strong></font></td>\n"
					 . "\t\t<td><select $slstyle name='usecookie'>\n"
					 . "\t\t\t<option value='Y'";
		if ($esrow['usecookie'] == "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._("Yes")."</option>\n"
					 . "\t\t\t<option value='N'";
		if ($esrow['usecookie'] != "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._("No")."</option>\n"
					 . "\t\t</select></td>\n"
					 . "\t</tr>\n";
		//ALLOW SAVES
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Allow Saves?")."</strong></font></td>\n"
					. "\t\t<td><select $slstyle name='allowsave'>\n"
					. "\t\t\t<option value='Y'";
		if (!$esrow['allowsave'] || $esrow['allowsave'] == "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._("Yes")."</option>\n"
					. "\t\t<option value='N'";
		if ($esrow['allowsave'] == "N") {$editsurvey .= " selected";}
		$editsurvey .= ">"._("No")."</option>\n"
					. "\t\t</select></td>\n"
					. "\t</tr>\n";
		//ALLOW PREV
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Show [<< Prev] button")."</strong></font></td>\n"
					. "\t\t<td><select $slstyle name='allowprev'>\n"
					. "\t\t\t<option value='Y'";
		if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._("Yes")."</option>\n"
					. "\t\t<option value='N'";
		if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") {$editsurvey .= " selected";}
		$editsurvey .= ">"._("No")."</option>\n"
					. "\t\t</select></td>\n"
					. "\t</tr>\n";
		//NOTIFICATION
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Notification:")."</strong></font></td>\n"
					 . "\t\t<td><select $slstyle name='notification'>\n"
					 . getNotificationlist($esrow['notification'])
					 . "\t\t</select></td>\n"
					 . "\t</tr>\n";
		//ANONYMOUS
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Anonymous?")."</strong></font></td>\n";
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
			$editsurvey .= ">"._("Yes")."</option>\n"
						 . "\t\t\t<option value='N'";
			if ($esrow['private'] != "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">"._("No")."</option>\n"
						 . "</select>\n\t\t</td>\n";
			}
		$editsurvey .= "</tr>\n";
		$editsurvey .= "<tr><td align='right'><script type='text/javascript'>\n"
					 . "<!--\n"
					 . "function fillin(tofield, fromfield)\n"
					 . "\t{\n"
					 . "\t\tif (confirm(\""._("This will replace the existing text. Continue?")."\")) {\n"
					 . "\t\t\tdocument.getElementById(tofield).value = document.getElementById(fromfield).value\n"
					 . "\t\t}\n"
					 . "\t}\n"
					 . "--></script>\n";
		$editsurvey .= "\t$setfont<strong>"._("Invitation Email Subject:")."</strong></font></td>\n"
					 . "\t\t<td><input type='text' $slstyle size='54' name='email_invite_subj' id='email_invite_subj' value=\"{$esrow['email_invite_subj']}\">\n"
					 . "\t\t<input type='hidden' name='email_invite_subj_default' id='email_invite_subj_default' value='".html_escape(_("Invitation to participate in survey"))."'>\n"
					 . "\t\t<input type='button' $slstyle value='"._("Use default")."' onClick='javascript: fillin(\"email_invite_subj\",\"email_invite_subj_default\")'>\n"
					 . "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Invitation Email:")."</strong></font></td>\n"
					. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_invite' id='email_invite'>{$esrow['email_invite']}</textarea>\n"
					. "\t\t<input type='hidden' name='email_invite_default' id='email_invite_default' value='".html_escape(_("Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}"))."'>\n"
					. "\t\t<input type='button' $slstyle value='"._("Use default")."' onClick='javascript: fillin(\"email_invite\",\"email_invite_default\")'>\n"
					. "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Email Reminder Subject:")."</strong></font></td>\n"
					 . "\t\t<td><input type='text' $slstyle size='54' name='email_remind_subj' id='email_remind_subj' value=\"{$esrow['email_remind_subj']}\">\n"
					 . "\t\t<input type='hidden' name='email_remind_subj_default' id='email_remind_subj_default' value='".html_escape(_("Reminder to participate in survey"))."'>\n"
					 . "\t\t<input type='button' $slstyle value='"._("Use default")."' onClick='javascript: fillin(\"email_remind_subj\",\"email_remind_subj_default\")'>\n"
					 . "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Email Reminder:")."</strong></font></td>\n"
					. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_remind' id='email_remind'>{$esrow['email_remind']}</textarea>\n"
					. "\t\t<input type='hidden' name='email_remind_default' id='email_remind_default' value='".html_escape(_("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}"))."'>\n"
					. "\t\t<input type='button' $slstyle value='"._("Use default")."' onClick='javascript: fillin(\"email_remind\",\"email_remind_default\")'>\n"
					. "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Confirmation Email Subject")."</strong></font></td>\n"
					 . "\t\t<td><input type='text' $slstyle size='54' name='email_confirm_subj' id='email_confirm_subj' value=\"{$esrow['email_confirm_subj']}\">\n"
					 . "\t\t<input type='hidden' name='email_confirm_subj_default' id='email_confirm_subj_default' value='".html_escape(_("Confirmation of completed survey"))."'>\n"
					 . "\t\t<input type='button' $slstyle value='"._("Use default")."' onClick='javascript: fillin(\"email_confirm_subj\",\"email_confirm_subj_default\")'>\n"
					 . "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Confirmation Email")."</strong></font></td>\n"
					. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_confirm' id='email_confirm'>{$esrow['email_confirm']}</textarea>\n"
					. "\t\t<input type='hidden' name='email_confirm_default' id='email_confirm_default' value='".html_escape(_("Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}"))."'>\n"
					. "\t\t<input type='button' $slstyle value='"._("Use default")."' onClick='javascript: fillin(\"email_confirm\",\"email_confirm_default\")'>\n"
					. "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Allow public registration?")."</strong></font></td>\n"
					. "\t\t<td><select $slstyle name='allowregister'>\n"
					. "\t\t\t<option value='Y'";
		if ($esrow['allowregister'] == "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._("Yes")."</option>\n"
					. "\t\t\t<option value='N'";
		if ($esrow['allowregister'] != "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._("No")."</option>\n"
					. "\t\t</select></td>\n\t</tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Public registration Email Subject:")."</strong></font></td>\n"
					 . "\t\t<td><input type='text' $slstyle size='54' name='email_register_subj' id='email_register_subj' value=\"{$esrow['email_register_subj']}\">\n"
					 . "\t\t<input type='hidden' name='email_register_subj_default' id='email_register_subj_default' value='".html_escape(_("Survey Registration Confirmation"))."'>\n"
					 . "\t\t<input type='button' $slstyle value='"._("Use default")."' onClick='javascript:  fillin(\"email_register_subj\",\"email_register_subj_default\")'>\n"
					 . "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Public registration Email:")."</strong></font></td>\n"
					. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_register' id='email_register'>{$esrow['email_register']}</textarea>\n"
					 . "\t\t<input type='hidden' name='email_register_default' id='email_register_default' value='".html_escape(_("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}."))."'>\n"
					. "\t\t<input type='button' $slstyle value='"._("Use default")."' onClick='javascript:  fillin(\"email_register\",\"email_register_default\")'>\n"
					. "\t</td></tr>\n";
		$editsurvey .= "\t<tr><td align='right' valign='top'>$setfont<strong>"._("Token Attribute Names:")."</strong></font></td>\n"
					. "\t\t<td>$setfont<input $slstyle type='text' size='25' name='attribute1'"
					. " value=\"{$esrow['attribute1']}\">("._("Attribute 1").")<br />"
					. "<input $slstyle type='text' size='25' name='attribute2'"
					. " value=\"{$esrow['attribute2']}\">("._("Attribute 2").")</font></td>\n\t</tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Date Stamp?")."</strong></font></td>\n";
				
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
			$editsurvey .= ">"._("Yes")."</option>\n"
						 . "\t\t\t<option value='N'";
			if ($esrow['datestamp'] != "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">"._("No")."</option>\n"
						 . "</select>\n\t\t</td>\n";
			}
		$editsurvey .= "</tr>\n";

		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("IP Address")."</strong></font></td>\n";

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
			$editsurvey .= ">"._("Yes")."</option>\n"
						 . "\t\t\t<option value='N'";
			if ($esrow['ipaddr'] != "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">"._("No")."</option>\n"
						 . "</select>\n\t\t</td>\n";
			}
			
	// begin REF URL Block
	$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Referring URL")."</strong></font></td>\n";
	
			if ($esrow['active'] == "Y")
			{
			$editsurvey .= "\t\t<td>\n\t\t\t$setfont";
			if ($esrow['refurl'] != "Y") {$editsurvey .= " Responses <strong>will not</strong> have their referring URL logged.";}
			else {$editsurvey .= "Responses <strong>will</strong> have their referring URL logged.";}
			$editsurvey .= "<font size='1' color='red'>&nbsp;(Cannot be changed)\n"
						 . "\t\t</font></font>\n";
			$editsurvey .= "<input type='hidden' name='refurl' value='".$esrow['refurl']."'>\n</td>";
			}
		else
			{
			$editsurvey .= "\t\t<td><select $slstyle name='refurl'>\n"
						 . "\t\t\t<option value='Y'";
			if ($esrow['refurl'] == "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">"._("Yes")."</option>\n"
						 . "\t\t\t<option value='N'";
			if ($esrow['refurl'] != "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">"._("No")."</option>\n"
						 . "</select>\n\t\t</td>\n";
			}
	// BENBUN - END REF URL Block
		$editsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Language:")."</strong></font></td>\n"
		 . "\t\t<td><select $slstyle name='language'>\n";
		 
	    foreach (getLanguageData() as  $langkey2=>$langname)
        {
    		$editsurvey .= "\t\t\t<option value='".$langkey2."'";
			if ($esrow['language'] && $esrow['language'] == htmlspecialchars($langname['description'])) {$editsurvey .= " selected";}
             // if language has been renamed then default to DefaultLanguage
			if (!$esrow['language'] && $currentlang && $currentlang == $langname) {$editsurvey .= " selected";}
			$editsurvey .= ">".$langname['description']."</option>\n";
	    }
		$editsurvey .= "\t\t</select></td>\n"
			    	. "\t</tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._("Expiry Date:")."</strong></font></td>\n"
					. "\t\t\t<td><select $slstyle name='useexpiry'><option value='Y'";
		if (isset($esrow['useexpiry']) && $esrow['useexpiry'] == "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._("Yes")."</option>\n"
					. "\t\t\t<option value='N'";
		if (!isset($esrow['useexpiry']) || $esrow['useexpiry'] != "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._("No")."</option></select></td></tr><tr><td></td>\n"
					. "\t\t<td><input $slstyle type='text' size='12' name='expires' value=\"{$esrow['expires']}\"></td></tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._("End URL:")."</strong></font></td>\n"
					. "\t\t<td><input $slstyle type='text' size='50' name='url' value=\"{$esrow['url']}\"></td></tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._("URL Description:")."</strong></font></td>\n"
					. "\t\t<td><input $slstyle type='text' size='50' name='urldescrip' value=\"{$esrow['urldescrip']}\"></td></tr>\n"
					. "\t<tr><td align='right'>$setfont<strong>"._("Automatically load URL when survey complete?")."</strong></font></td>\n"
					. "\t\t<td><select $slstyle name='autoredirect'>";
		$editsurvey .= "\t\t\t<option value='Y'";
		if (isset($esrow['autoredirect']) && $esrow['autoredirect'] == "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._("Yes")."</option>\n";
		$editsurvey .= "\t\t\t<option value='N'";
		if (!isset($esrow['autoredirect']) || $esrow['autoredirect'] != "Y") {$editsurvey .= " selected";}
		$editsurvey .= ">"._("No")."</option>\n"
					 . "</select></td></tr>";

		$editsurvey .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='"._("Update survey")."'>\n"
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
			$editcsv .="<b><font color='red'>"._("Error").":</font> "._("The uploaded file is bigger than the upload_max_filesize directive in php.ini")."</b>\n";
			break;
		case UPLOAD_ERR_PARTIAL:	
			upload();
			$editcsv .="<b><font color='red'>"._("Error").":</font> "._("The file was only partially uploaded")."</b>\n";
			break;
		case UPLOAD_ERR_NO_FILE:
			upload();	
			$editcsv .="<b><font color='red'>"._("Error").":</font> "._("No file was uploaded")."</b>\n";
			break;
		case UPLOAD_ERR_OK:		     
			control();
			break;
		default:
			$editcsv .="<b><font color='red'>"._("Error").":</font> "._("Error on file transfer. You must select a CSV file")."</b>\n";
		}	
	}
	
if ($action == "newsurvey")
	{
	$newsurvey = "<form name='addnewsurvey' action='$scriptname' method='post'>\n<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n"
				. "\t\t<strong>$setfont<font color='white'>"._("Create Survey")."</font></font></strong></td></tr>\n"
				. "\t<tr>\n"
				. "\t\t<td align='right' width='25%'><strong>$setfont"._("Title:")."</font></strong></td>\n"
				. "\t\t<td><input $slstyle type='text' size='50' name='short_title'></td></tr>\n"
				. "\t<tr><td align='right'><strong>$setfont"._("Description:")."</font></strong>	</td>\n"
				. "\t\t<td><textarea $slstyle2 cols='50' rows='5' name='description'></textarea></td></tr>\n"
				. "\t<tr><td align='right'>$setfont<strong>"._("Welcome:")."</strong></font></td>\n"
				. "\t\t<td><textarea $slstyle2 cols='50' rows='5' name='welcome'></textarea></td></tr>\n"
				. "\t<tr><td align='right'>$setfont<strong>"._("Administrator:")."</strong></font></td>\n"
				. "\t\t<td><input $slstyle type='text' size='50' name='admin'></td></tr>\n"
				. "\t<tr><td align='right'>$setfont<strong>"._("Admin Email:")."</strong></font></td>\n"
				. "\t\t<td><input $slstyle type='text' size='50' name='adminemail'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Fax To:")."</strong></font></td>\n"
				. "\t\t<td><input $slstyle type='text' size='50' name='faxto'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Format:")."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='format'>\n"
				. "\t\t\t<option value='S' selected>"._("Question by Question")."</option>\n"
				. "\t\t\t<option value='G'>"._("Group by Group")."</option>\n"
				. "\t\t\t<option value='A'>"._("All in one")."</option>\n"
				. "\t\t</select></td>\n"
				. "\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Template:")."</strong></font></td>\n"
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
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Use Cookies?")."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='usecookie'>\n"
				. "\t\t\t<option value='Y'";
	if (isset($esrow) && $esrow['usecookie'] == "Y") {$newsurvey .= " selected";}
	$newsurvey .= ">"._("Yes")."</option>\n"
				. "\t\t\t<option value='N'";
	if (isset($esrow) && $esrow['usecookie'] != "Y" || !isset($esrow)) {$newsurvey .= " selected";}
	$newsurvey .= ">"._("No")."</option>\n"
				. "\t\t</select></td>\n"
				. "\t</tr>\n";
	//ALLOW SAVES
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Allow Saves?")."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='allowsave'>\n"
				. "\t\t\t<option value='Y'";
	if (!isset($esrow['allowsave']) || !$esrow['allowsave'] || $esrow['allowsave'] == "Y") {$newsurvey .= " selected";}
	$newsurvey .= ">"._("Yes")."</option>\n"
				. "\t\t<option value='N'";
	if (isset($esrow['allowsave']) && $esrow['allowsave'] == "N") {$newsurvey .= " selected";}
	$newsurvey .= ">"._("No")."</option>\n"
				. "\t\t</select></td>\n"
				. "\t</tr>\n";
	//ALLOW PREV
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Show [<< Prev] button")."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='allowprev'>\n"
				. "\t\t\t<option value='Y'";
	if (!isset($esrow['allowprev']) || !$esrow['allowprev'] || $esrow['allowprev'] == "Y") {$newsurvey .= " selected";}
	$newsurvey .= ">"._("Yes")."</option>\n"
				. "\t\t<option value='N'";
	if (isset($esrow['allowprev']) && $esrow['allowprev'] == "N") {$newsurvey .= " selected";}
	$newsurvey .= ">"._("No")."</option>\n"
				. "\t\t</select></td>\n"
				. "\t</tr>\n";
	//NOTIFICATIONS
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Notification:")."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='notification'>\n"
				. getNotificationlist(0)
				. "\t\t</select></td>\n"
				. "\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Anonymous?")."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='private'>\n"
				. "\t\t\t<option value='Y' selected>"._("Yes")."</option>\n"
				. "\t\t\t<option value='N'>"._("No")."</option>\n"
				. "\t\t</select></td>\n\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Invitation Email Subject:")."</strong></font></td>\n"
				 . "\t\t<td><input type='text' $slstyle size='54' name='email_invite_subj' value='".html_escape(_("Invitation to participate in survey"))."'>\n"
				 . "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Invitation Email:")."</strong></font></td>\n"
				. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_invite'>"._("Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}")."</textarea>\n"
				. "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Email Reminder Subject:")."</strong></font></td>\n"
				 . "\t\t<td><input type='text' $slstyle size='54' name='email_remind_subj' value='".html_escape(_("Reminder to participate in survey"))."'>\n"
				 . "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Email Reminder:")."</strong></font></td>\n"
				. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_remind'>"._("Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\nWe note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\nThe survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\nTo participate, please click on the link below.\n\nSincerely,\n\n{ADMINNAME} ({ADMINEMAIL})\n\n----------------------------------------------\nClick here to do the survey:\n{SURVEYURL}")."</textarea>\n"
				. "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Confirmation Email Subject")."</strong></font></td>\n"
				 . "\t\t<td><input type='text' $slstyle size='54' name='email_confirm_subj' value='".html_escape(_("Confirmation of completed survey"))."'>\n"
				 . "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Confirmation Email")."</strong></font></td>\n"
				. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_confirm'>"._("Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} and your response has been saved. Thank you for participating.\n\nIf you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\nSincerely,\n\n{ADMINNAME}")."</textarea>\n"
				. "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Allow public registration?")."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='allowregister'>\n"
				. "\t\t\t<option value='Y'>"._("Yes")."</option>\n"
				. "\t\t\t<option value='N' selected>"._("No")."</option>\n"
				. "\t\t</select></td>\n\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Public registration Email Subject:")."</strong></font></td>\n"
				 . "\t\t<td><input type='text' $slstyle size='54' name='email_register_subj' value='".html_escape(_("Survey Registration Confirmation"))."'>\n"
				 . "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Public registration Email:")."</strong></font></td>\n"
				. "\t\t<td><textarea $slstyle2 cols=50 rows=5 name='email_register'>"._("Dear {FIRSTNAME},\n\nYou, or someone using your email address, have registered to participate in an online survey titled {SURVEYNAME}.\n\nTo complete this survey, click on the following URL:\n\n{SURVEYURL}\n\nIf you have any questions about this survey, or if you did not register to participate and believe this email is in error, please contact {ADMINNAME} at {ADMINEMAIL}.")."</textarea>\n"
				. "\t</td></tr>\n";
	$newsurvey .= "\t<tr><td align='right' valign='top'>$setfont<strong>"._("Token Attribute Names:")."</strong></font></td>\n"
				. "\t\t<td>$setfont<input $slstyle type='text' size='25' name='attribute1'>("._("Attribute 1").")<br />"
				. "<input $slstyle type='text' size='25' name='attribute2'>("._("Attribute 2").")</font></td>\n\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Date Stamp?")."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='datestamp'>\n"
				. "\t\t\t<option value='Y'>"._("Yes")."</option>\n"
				. "\t\t\t<option value='N' selected>"._("No")."</option>\n"
				. "\t\t</select></td>\n\t</tr>\n";
	// IP Address
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("IP Address")."</strong></font></td>\n"
                                . "\t\t<td><select $slstyle name='ipaddr'>\n"                                . "\t\t\t<option value='Y'>"._("Yes")."</option>\n"
                                . "\t\t\t<option value='N' selected>"._("No")."</option>\n"
                                . "\t\t</select></td>\n\t</tr>\n";
	// Referring URL
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Referring URL")."</strong></font></td>\n"
                                . "\t\t<td><select $slstyle name='refurl'>\n"                                . "\t\t\t<option value='Y'>"._("Yes")."</option>\n"
                                . "\t\t\t<option value='N' selected>"._("No")."</option>\n"
                                . "\t\t</select></td>\n\t</tr>\n";
	//Survey Language
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Language:")."</strong></font></td>\n"
				. "\t\t<td><select $slstyle name='language'>\n";
   	foreach (getLanguageData() as $languages)
    	{
	    foreach ($languages as  $langkey2=>$langname)
            {
	   	    $newsurvey .= "\t\t\t<option value='".$langkey2."'";
    //		if ($currentlang && $currentlang == $langname) {$newsurvey .= " selected";}
	       	$newsurvey .= ">".$langname['description']."</option>\n";
            }
		}
	$newsurvey .= "\t\t</select></td>\n"
				. "\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<strong>"._("Expiry Date:")."</strong></font></td>\n"
				. "\t\t\t<td><select $slstyle name='useexpiry'><option value='Y'>"._("Yes")."</option>\n"
				. "\t\t\t<option value='N' selected>"._("No")."</option></select></td></tr><tr><td></td>\n"
				. "\t\t<td>$setfont<input $slstyle type='text' size='12' name='expires' value='1980-01-01'>"
				. "<font size='1'>Date Format: YYYY-MM-DD</font></font></td></tr>\n"
				. "\t<tr><td align='right'>$setfont<strong>"._("End URL:")."</strong></font></td>\n"
				. "\t\t<td><input $slstyle type='text' size='50' name='url' value='http://";
	if (isset($esrow)) {$newsurvey .= $esrow['url'];}
	$newsurvey .= "'></td></tr>\n"
				. "\t<tr><td align='right'>$setfont<strong>"._("URL Description:")."</strong></font></td>\n"
				. "\t\t<td><input $slstyle type='text' size='50' name='urldescrip' value='";
	if (isset($esrow)) {$newsurvey .= $esrow['urldescrip'];}
	$newsurvey .= "'></td></tr>\n"
				 . "\t<tr><td align='right'>$setfont<strong>"._("Automatically load URL when survey complete?")."</strong></font></td>\n"
				 . "\t\t<td><select $slstyle name='autoredirect'>\n"
				 . "\t\t\t<option value='Y'>"._("Yes")."</option>\n"
				 . "\t\t\t<option value='N' selected>"._("No")."</option>\n"
				 . "</select></td></tr>"
				. "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='"._("Create Survey")."'>\n"
				. "\t<input type='hidden' name='action' value='insertnewsurvey'></td>\n"
				. "\t</tr>\n"
				. "</table></form>\n";
	$newsurvey .= "<center>$setfont<strong>"._("OR")."</strong></font></center>\n";
	$newsurvey .= "<form enctype='multipart/form-data' name='importsurvey' action='$scriptname' method='post'>\n"
				. "<table width='100%' border='0'>\n"
				. "<tr><td colspan='2' bgcolor='black' align='center'>\n"
				. "\t\t<strong>$setfont<font color='white'>"._("Import Survey")."</font></font></strong></td></tr>\n\t<tr>"
				. "\t\t<td align='right'>$setfont<strong>"._("Select SQL File:")."</strong></font></td>\n"
				. "\t\t<td><input $slstyle2 name=\"the_file\" type=\"file\" size=\"35\"></td></tr>\n"
				. "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='"._("Import Survey")."'>\n"
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
						 . _("Warning").": ". _("You need to upload the file")." "
						 . "\n$setfont<form enctype='multipart/form-data' action='" . $_SERVER['PHP_SELF'] . "' method='post'>\n"
						 . "<input type='hidden' name='action' value='uploadf' />\n"
						 . "<input type='hidden' name='sid' value='$sid' />\n"
						 . "<input type='hidden' name='gid' value='$gid' />\n"
                         . "<input type='hidden' name='qid' value='$qid' />\n"
						 . "<font face='verdana' size='2' color='green'><b>"
                         . _("You must upload a CSV file")."</font><br />\n"
 						 . "<input type='file' $slstyle name='the_file' size='35' /><br />\n"
						 . "<input type='submit' $btstyle value='"._("Upload CSV file")."' />\n"
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
		$editcsv .="<b><font color='red'>"._("Error").":</font> "._("It is impossible to upload a file other than CSV type")."</b>\n";
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
			$editcsv .="<b><font color='red'>"._("Error").":</font> "._("It is impossible to upload an empty file")."</b>\n";
			$questionsummary .= "</table>\n";	
			}
		else 			
			{
			$editcsv  = "<table width='100%' align='center' border='0'>\n"
				. "<tr bgcolor='#555555'><td colspan='2'><font color='white'><b>"
				. _("Uploading CSV file")."</b></td></tr>\n";
		    $editcsv .= "<tr><th>$setfont"._("Visualization:")."</font></th><th>$setfont"._("Select the field number you would like to use for your answers:")."</font></th>"
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
					._("Continue")."'></td>\n";
			}
		}	
	}	
	
?>
