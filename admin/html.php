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

if ($sid)
	{
	$surveysummary = "<script type='text/javascript'>\n";
	$surveysummary .= "<!--\n";
	$surveysummary .= "\tfunction showdetails(action)\n";
	$surveysummary .= "\t\t{\n";
	$surveysummary .= "\t\tif (action == \"hides\")\n";
	$surveysummary .= "\t\t\t{\n";
	$surveysummary .= "\t\t\tfor (i=0; i<=10; i++)\n";
	$surveysummary .= "\t\t\t\t{\n";
	$surveysummary .= "\t\t\t\tvar name='surveydetails'+i;\n";
	$surveysummary .= "\t\t\t\tdocument.getElementById(name).style.display='none';\n";
	$surveysummary .= "\t\t\t\t}\n";
	$surveysummary .= "\t\t\t}\n";
	$surveysummary .= "\t\telse if (action == \"shows\")\n";
	$surveysummary .= "\t\t\t{\n";
	$surveysummary .= "\t\t\tfor (i=0; i<=10; i++)\n";
	$surveysummary .= "\t\t\t\t{\n";
	$surveysummary .= "\t\t\t\tvar name='surveydetails'+i;\n";
	$surveysummary .= "\t\t\t\tdocument.getElementById(name).style.display='';\n";
	$surveysummary .= "\t\t\t\t}\n";
	$surveysummary .= "\t\t\t}\n";
	$surveysummary .= "\t\telse if (action == \"hideg\")\n";
	$surveysummary .= "\t\t\t{\n";
	$surveysummary .= "\t\t\tfor (i=20; i<=21; i++)\n";
	$surveysummary .= "\t\t\t\t{\n";
	$surveysummary .= "\t\t\t\tvar name='surveydetails'+i;\n";
	$surveysummary .= "\t\t\t\tdocument.getElementById(name).style.display='none';\n";
	$surveysummary .= "\t\t\t\t}\n";
	$surveysummary .= "\t\t\t}\n";
	$surveysummary .= "\t\telse if (action == \"showg\")\n";
	$surveysummary .= "\t\t\t{\n";
	$surveysummary .= "\t\t\tfor (i=20; i<=21; i++)\n";
	$surveysummary .= "\t\t\t\t{\n";
	$surveysummary .= "\t\t\t\tvar name='surveydetails'+i;\n";
	$surveysummary .= "\t\t\t\tdocument.getElementById(name).style.display='';\n";
	$surveysummary .= "\t\t\t\t}\n";
	$surveysummary .= "\t\t\t}\n";
	$surveysummary .= "\t\telse if (action == \"hideq\")\n";
	$surveysummary .= "\t\t\t{\n";
	$surveysummary .= "\t\t\tfor (i=30; i<=34; i++)\n";
	$surveysummary .= "\t\t\t\t{\n";
	$surveysummary .= "\t\t\t\tvar name='surveydetails'+i;\n";
	$surveysummary .= "\t\t\t\tdocument.getElementById(name).style.display='none';\n";
	$surveysummary .= "\t\t\t\t}\n";
	$surveysummary .= "\t\t\t}\n";
	$surveysummary .= "\t\telse if (action == \"showq\")\n";
	$surveysummary .= "\t\t\t{\n";
	$surveysummary .= "\t\t\tfor (i=30; i<=34; i++)\n";
	$surveysummary .= "\t\t\t\t{\n";
	$surveysummary .= "\t\t\t\tvar name='surveydetails'+i;\n";
	$surveysummary .= "\t\t\t\tdocument.getElementById(name).style.display='';\n";
	$surveysummary .= "\t\t\t\t}\n";
	$surveysummary .= "\t\t\t}\n";
	$surveysummary .= "\t\t}\n";
	$surveysummary .= "-->\n";
	$surveysummary .= "</script>\n";
	$sumquery3 = "SELECT * FROM questions WHERE sid=$sid"; //Getting a count of questions for this survey
	$sumresult3 = mysql_query($sumquery3);
	$sumcount3 = mysql_num_rows($sumresult3);
	$sumquery2 = "SELECT * FROM groups WHERE sid=$sid"; //Getting a count of groups for this survey
	$sumresult2 = mysql_query($sumquery2);
	$sumcount2 = mysql_num_rows($sumresult2);
	$sumquery1 = "SELECT * FROM surveys WHERE sid=$sid"; //Getting data for this survey
	$sumresult1 = mysql_query($sumquery1);
	$surveysummary .= "<table width='100%' align='center' bgcolor='#DDDDDD' border='0'>\n";
	while ($s1row = mysql_fetch_array($sumresult1))
		{
		$activated = $s1row['active'];
		//BUTTON BAR
		$surveysummary .= "\t<tr>\n";
		$surveysummary .= "\t\t<td colspan='2'>\n";
		$surveysummary .= "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
		$surveysummary .= "\t\t\t\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._SURVEY."</b> <font color='silver'>{$s1row['short_title']}</td></tr>\n";
		$surveysummary .= "\t\t\t\t<tr height='22' bgcolor='#999999'><td align='right'>\n";
		if ($activated == "N" && $sumcount3>0) 
			{
			$surveysummary .= "\t\t\t\t\t<img src='./images/inactive.gif' title='"._S_INACTIVE_BT."' alt='"._A_INACTIVE_BT."' border='0' hspace='0' align='left'>\n";
			$surveysummary .= "\t\t\t\t\t<input type='image' src='./images/activate.gif' title='"._S_ACTIVATE_BT."' border='0' hspace='0' align='left' onClick=\"window.open('$scriptname?action=activate&sid=$sid', '_top')\">\n";
			}
		elseif ($activated == "Y")
			{
			$surveysummary .= "\t\t\t\t\t<img src='./images/active.gif' title='"._S_ACTIVE_BT."' alt='"._S_ACTIVE_BT."' border='0' hspace='0' align='left'>\n";
			$surveysummary .= "\t\t\t\t\t<input type='image' src='./images/deactivate.gif' title='"._S_DEACTIVATE_BT."' border='0' hspace='0' align='left' onClick=\"window.open('$scriptname?action=deactivate&sid=$sid', '_top')\">\n";
			}
		elseif ($activated == "N")
			{
			$surveysummary .= "\t\t\t\t\t<img src='./images/inactive.gif' title='"._S_INACTIVE_BT."' ALT='"._S_INACTIVE_BT."' border='0' hspace='0' align='left'>\n";
			$surveysummary .= "\t\t\t\t\t<img src='./images/blank.gif' width='11' title='"._S_CANNOTACTIVATE_BT."' alt='"._S_CANNOTACTIVATE_BT."' border='0' align='left' hspace='0'>\n";
			}
		$surveysummary .= "\t\t\t\t\t<img src='./images/seperator.gif' align='left' border='0' hspace='0'>\n";
		$surveysummary .= "\t\t\t\t\t<input type='image' src='./images/do.gif' title='"._S_DOSURVEY_BT."' align='left' border='0' hspace='0' onclick=\"window.open('../index.php?sid=$sid&newtest=Y', '_blank')\">\n";
		$surveysummary .= "\t\t\t\t\t<input type='image' src='./images/dataentry.gif' title='"._S_DATAENTRY_BT."' align='left' border='0' hspace='0' onclick=\"window.open('dataentry.php?sid=$sid', '_blank')\">\n";
		$surveysummary .= "\t\t\t\t\t<input type='image' src='./images/print.gif' title='"._S_PRINTABLE_BT."' align='left' border='0' hspace='0' onclick=\"window.open('printablesurvey.php?sid=$sid', '_blank')\">\n";
		$surveysummary .= "\t\t\t\t\t<img src='./images/seperator.gif' align='left' border='0' hspace='0'>\n";
		$surveysummary .= "\t\t\t\t\t<input type='image' src='./images/edit.gif' title='"._S_EDIT_BT."' align='left' border='0' hspace='0' onclick=\"window.open('$scriptname?action=editsurvey&sid=$sid', '_top')\">\n";
		if ($sumcount3 == 0 && $sumcount2 == 0)
			{
			$surveysummary .= "\t\t\t\t\t<input type='image' src='./images/delete.gif' title='"._S_DELETE_BT."' align='left' border='0' hspace='0' onclick=\"window.open('$scriptname?action=delsurvey&sid=$sid', '_top')\">\n";
			}
		else
			{
			$surveysummary .= "\t\t\t\t\t<img src='./images/blank.gif' width='20' align='left' border='0' hspace='0'>\n";
			}
		$surveysummary .= "\t\t\t\t\t<input type='image' src='./images/export.gif' title='"._S_EXPORT_BT."' align='left' border='0' hspace='0' onclick=\"window.open('dumpsurvey.php?sid=$sid', '_top')\">\n";
		if ($activated == "Y")
			{
			$surveysummary .= "\t\t\t\t\t<img src='./images/seperator.gif' align='left' border='0' hspace='0'>\n";
			$surveysummary .= "\t\t\t\t\t<input type='image' src='./images/browse.gif' title='"._S_BROWSE_BT."' align='left' border='0' hspace='0' onclick=\"window.open('browse.php?sid=$sid', '_top')\">\n";
			$surveysummary .= "\t\t\t\t\t<img src='./images/seperator.gif' align='left' border='0' hspace='0'>\n";
			$surveysummary .= "\t\t\t\t\t<input type='image' src='./images/tokens.gif' title='"._S_TOKENS_BT."' align='left' border='0' hspace='0' onclick=\"window.open('tokens.php?sid=$sid', '_top')\">\n";
			}
		$surveysummary .= "\t\t\t\t\t<img src='./images/seperator.gif' align='left' border='0' hspace='0'>\n";
		$surveysummary .= "\t\t\t\t</td>\n";
		$surveysummary .= "\t\t\t\t<td align='right' valign='middle' width='320'>\n";
		if (!$gid) {$surveysummary .= "\t\t\t\t\t<input type='image' src='./images/close.gif' title='"._S_CLOSE_BT."' align='right' border='0' hspace='0' onclick=\"window.open('$scriptname', '_top')\">\n";}
		else {$surveysummary .= "\t\t\t\t\t<img src='./images/blank.gif' width='21' align='right' border='0' hspace='0'>\n";}
		$surveysummary .= "\t\t\t\t\t<input type='image' src='./images/plus.gif' title='"._S_MAXIMISE_BT."' align='right' border='0' hspace='0' onclick='showdetails(\"shows\")'>\n";
		$surveysummary .= "\t\t\t\t\t<input type='image' src='./images/minus.gif' title='"._S_MINIMISE_BT."' align='right' border='0' hspace='0' onclick='showdetails(\"hides\")'>\n";
		$surveysummary .= "\t\t\t\t\t<img src='./images/seperator.gif' align='right' border='0' hspace='0'>\n";
		if ($activated == "Y")
			{
			$surveysummary .= "<img src='./images/blank.gif' width='20' align='right' border='0' hspace='0'>\n";
			}
		else
			{
			$surveysummary .= "\t\t\t\t\t<input type='image' src='./images/add.gif' title='"._S_ADDGROUP_BT."' align='right' border='0' hspace='0' onClick=\"window.open('$scriptname?action=addgroup&sid=$sid', '_top')\">\n";
			}
		$surveysummary .= "<font size='1' color='#222222'><b>Groups:</b>";
		$surveysummary .= "\t\t<select style='font-size: 9; font-family: verdana; font-color: #222222; background: silver; width: 160' name='groupselect' onChange=\"window.open(this.options[this.selectedIndex].value,'_top')\">\n";
		if (getgrouplist($gid))
			{
			$surveysummary .= getgrouplist($gid);
			}
		else
			{
			$surveysummary .= "<option>No Groups</option>\n";
			}
		$surveysummary .= "</select>\n";
		$surveysummary .= "\t\t\t\t</td>";
		$surveysummary .= "</tr>\n";
		$surveysummary .= "\t\t\t</table>\n";
		$surveysummary .= "\t\t</td>\n";
		$surveysummary .= "\t</tr>\n";
		
		//SURVEY SUMMARY
		if ($gid || $qid || $action=="editsurvey" || $action=="addgroup") {$showstyle="style='display: none'";}
		
		$surveysummary .= "\t<tr $showstyle id='surveydetails0'><td align='right' valign='top'>$setfont<b>"._SL_TITLE."</b></font></td>\n";
		$surveysummary .= "\t<td>";
		$surveysummary .= "$setfont<font color='#000080'><b>{$s1row['short_title']} (ID {$s1row['sid']})</b></td></tr>\n";
		$surveysummary2 = "\t<tr $showstyle id='surveydetails1'><td width='80'></td><td>$setfont<font size='1' color='#000080'>\n";
		if ($s1row['private'] != "N") {$surveysummary2 .= "This survey is anonymous";}
		else {$surveysummary2 .= "This survey is <b>not</b> anonymous";}
		if ($s1row['format'] == "S") {$surveysummary2 .= " and is presented question by question. ";}
		elseif ($s1row['format'] == "G") {$surveysummary2 .= " and is presented group by group. ";}
		else {$surveysummary2 .= " and is presented as one single page. ";}
		if ($s1row['datestamp'] == "Y") {$surveysummary2 .= "Responses will be date-stamped.";}
		$surveysummary2 .= "</font></td></tr>\n";
		$surveysummary .= "\t<tr $showstyle id='surveydetails2'><td align='right' valign='top'>$setfont<b>"._SL_DESCRIPTION."</b></font></td>\n";
		$surveysummary .= "\t\t<td>$setfont {$s1row['description']}</font></td></tr>\n";
		$surveysummary .= "\t<tr $showstyle id='surveydetails3'>\n";
		$surveysummary .= "\t\t<td align='right' valign='top'>$setfont<b>"._SL_WELCOME."</b></font></td>\n";
		$surveysummary .= "\t\t<td>$setfont {$s1row['welcome']}</font></td></tr>\n";
		$surveysummary .= "\t<tr $showstyle id='surveydetails4'><td align='right' valign='top'>$setfont<b>"._SL_ADMIN."</b></font></td>\n";
		$surveysummary .= "\t\t<td>$setfont {$s1row['admin']} ({$s1row['adminemail']})</font></td></tr>\n";
		$surveysummary .= "\t<tr $showstyle id='surveydetails5'><td align='right' valign='top'>$setfont<b>"._SL_FAXTO."</b></font></td>\n";
		$surveysummary .= "\t\t<td>$setfont {$s1row['faxto']}</font></td></tr>\n";
		$surveysummary .= "\t<tr $showstyle id='surveydetails6'><td align='right' valign='top'>$setfont<b>"._SL_EXPIRES."</b></font></td>\n";
		if ($s1row['expires'] != "0000-00-00") 
			{
			$expdate=$s1row['expires'];
			}
		$expdate="Never";
		$surveysummary .= "\t<td>$setfont$expdate</font></td></tr>\n";
		$surveysummary .= "\t<tr $showstyle id='surveydetails7'><td align='right' valign='top'>$setfont<b>"._SL_TEMPLATE."</b></font></td>\n";
		$surveysummary .= "\t\t<td>$setfont {$s1row['template']}</font></td></tr>\n";
		$surveysummary .= "\t<tr $showstyle id='surveydetails8'><td align='right' valign='top'>$setfont<b>"._SL_LANGUAGE."</b></font></td>\n";
		if (!$s1row['language']) {$language=$defaultlang;} else {$language=$s1row['language'];}
		$surveysummary .= "\t\t<td>$setfont$language</font></td></tr>\n";
		$surveysummary .= "\t<tr $showstyle id='surveydetails9'><td align='right' valign='top'>$setfont<b>"._SL_LINK."</b></font></td>\n";
		$surveysummary .= "\t\t<td>$setfont <a href='{$s1row['url']}' title='{$s1row['url']}'>{$s1row['urldescrip']}</a></font></td></tr>\n";
		}
	
	$surveysummary .= "\t<tr $showstyle id='surveydetails10'><td align='right' valign='top'>$setfont<b>"._SL_STATUS."</b></font></td>\n";
	$surveysummary .= "\t<td valign='top'>$setfont";
	$surveysummary .= "<font size='1'>This survey contains $sumcount2 groups and $sumcount3 questions.</font><br />\n";
	if ($activated == "N" && $sumcount3 > 0)
		{
		$surveysummary .= "<font size='1'>Survey is not active. Activate using the button bar above.<br />\n";
		}
	elseif ($activated == "Y")
		{
		$surveysummary .= "<font size='1'>Survey is currently active. De-activate it by using the button bar above.<br />\n";
		$surveysummary .= "<FONT SIZE='1'>Survey Table is 'survey_$sid'<BR>";
		}
	else
		{
		$surveysummary .= "<font size='1'>Survey cannot be activated yet.<br />\n";
		if ($sumcount2 == 0) 
			{
			$surveysummary .= "\t<font color='green'>[You need to Add Groups]</font><br />";
			}
		if ($sumcount3 == 0)
			{
			$surveysummary .= "\t<font color='green'>[You need to Add Questions]</font>";
			}
		}
	$surveysummary .= "</td></tr>\n";
	$surveysummary .= $surveysummary2;
	$surveysummary .= "</table>\n";
	}

if ($gid)
	{
	$grpquery ="SELECT * FROM groups WHERE gid=$gid ORDER BY group_name";
	$grpresult = mysql_query($grpquery);
	$groupsummary = "<table width='100%' align='center' bgcolor='#DDDDDD' border='0'>\n";
	while ($grow = mysql_fetch_array($grpresult))
		{
		$groupsummary .= "\t<tr>\n";
		$groupsummary .= "\t\t<td colspan='2'>\n";
		$groupsummary .= "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
		$groupsummary .= "\t\t\t\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>Group</b> <font color='silver'>{$grow['group_name']}</td></tr>\n";
		$groupsummary .= "\t\t\t\t<tr bgcolor='#AAAAAA'>\n";
		$groupsummary .= "\t\t\t\t\t<td>\n";
		$groupsummary .= "\t\t\t\t\t<img src='./images/blank.gif' width='31' height='20' border='0' hspace='0' align='left'>\n";
		$groupsummary .= "\t\t\t\t\t<img src='./images/seperator.gif' border='0' hspace='0' align='left'>\n";
		$groupsummary .= "\t\t\t\t\t<img src='./images/blank.gif' width='60' height='20' border='0' hspace='0' align='left'>\n";
		$groupsummary .= "\t\t\t\t\t<img src='./images/seperator.gif' border='0' hspace='0' align='left'>\n";
		$groupsummary .= "\t\t\t\t\t<input type='image' src='./images/edit.gif' title='"._G_EDIT_BT."' align='left' border='0' hspace='0' onclick=\"window.open('$scriptname?action=editgroup&sid=$sid&gid=$gid', '_top')\">";
		if ($sumcount3 == 0) {$groupsummary .= "\t\t\t\t\t<input type='image' src='./images/delete.gif' title='"._G_DELETE_BT."' align='left' border='0' hspace='0' onclick=\"window.open('$scriptname?action=delgroup&sid=$sid&gid=$gid', '_top')\">";}
		else				 {$groupsummary .= "\t\t\t\t\t<img src='./images/blank.gif' width='20' align='left' border='0' hspace='0'>\n";}
		$groupsummary .= "\t\t\t\t\t<img src='./images/blank.gif' width='20' align='left' border='0' hspace='0'>\n";
		$groupsummary .= "\t\t\t\t\t<img src='./images/seperator.gif' border='0' hspace='0' align='left'>\n";
		$groupsummary .= "\t\t\t\t\t</td>\n";
		$groupsummary .= "\t\t\t\t\t<td align='right' width='320'>\n";
		if (!$qid) {$groupsummary .= "\t\t\t\t\t<input type='image' src='./images/close.gif' title='"._G_CLOSE_BT."' align='right' border='0' hspace='0' onclick=\"window.open('$scriptname?sid=$sid', '_top')\">\n";}
		else {$groupsummary .= "\t\t\t\t\t<img src='./images/blank.gif' width='21' align='right' border='0' hspace='0'>\n";}
		$groupsummary .= "\t\t\t\t\t<input type='image' src='./images/plus.gif' title='"._G_MAXIMISE_BT."' align='right' border='0' hspace='0' onclick='showdetails(\"showg\")'>";
		$groupsummary .= "\t\t\t\t\t<input type='image' src='./images/minus.gif' title='"._G_MINIMISE_BT."' align='right' border='0' hspace='0' onclick='showdetails(\"hideg\")'>\n";
		$groupsummary .= "\t\t\t\t\t<img src='./images/seperator.gif' align='right' border='0' hspace='0'>\n";
		if ($activated == "Y")
			{
			$groupsummary .= "\t\t\t\t\t<img src='./images/blank.gif' width='20' border='0' hspace='0' align='right'>\n";
			}
		else
			{
			$groupsummary .= "\t\t\t\t\t<input type='image' src='./images/add.gif' title='"._G_ADDQUESTION_BT."' border='0' hspace='0' align='right' onClick=\"window.open('$scriptname?action=addquestion&sid=$sid&gid=$gid', '_top')\">\n";
			}
		$groupsummary .= "\t\t\t\t\t$setfont<font size='1'><b>Questions:</b> <select name='qid' onChange=\"window.open(this.options[this.selectedIndex].value, '_top')\" style='font-size:9; font-family: verdana; font-color: #333333; background-color: silver; width: 160'>\n";
		$groupsummary .= getquestions();
		$groupsummary .= "\t\t\t\t\t</select>\n";
		$groupsummary .= "\t\t\t\t</td></tr>\n";
		$groupsummary .= "\t\t\t</table>\n";
		$groupsummary .= "\t\t</td>\n";
		$groupsummary .= "\t</tr>\n";
		if ($qid) {$gshowstyle="style='display: none'";}

		$groupsummary .= "\t<tr $gshowstyle id='surveydetails20'><td width='20%' align='right'>$setfont<b>"._GL_TITLE."</b></font></td>\n";
		$groupsummary .="\t<td>";
		$groupsummary .="$setfont{$grow['group_name']} ({$grow['gid']})</font></td></tr>\n";
		$groupsummary .= "\t<tr $gshowstyle id='surveydetails21'><td valign='top' align='right'>$setfont<b>"._GL_DESCRIPTION."</b></font></td>\n\t<td>$setfont{$grow['description']}</font></td></tr>\n";
		}
	$groupsummary .= "\n</table>\n";
	}

if ($qid)
	{
	$qrq = "SELECT * FROM answers WHERE qid=$qid ORDER BY sortorder, answer";
	$qrr = mysql_query($qrq);
	$qct = mysql_num_rows($qrr);
	$qrquery = "SELECT * FROM questions WHERE gid=$gid AND sid=$sid AND qid=$qid";
	$qrresult = mysql_query($qrquery);
	$questionsummary = "<table width='100%' align='center' bgcolor='#EEEEEE' border='0'>\n";
	while ($qrrow = mysql_fetch_array($qrresult))
		{
		$questionsummary .= "\t<tr>\n";
		$questionsummary .= "\t\t<td colspan='2'>\n";
		$questionsummary .= "\t\t\t<table width='100%' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
		$questionsummary .= "\t\t\t\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><b>"._QUESTION."</b> <font color='silver'>{$qrrow['question']}</td></tr>\n";
		$questionsummary .= "\t\t\t\t<tr bgcolor='#AAAAAA'>\n";
		$questionsummary .= "\t\t\t\t\t<td>\n";
		$questionsummary .= "\t\t\t\t\t<img src='./images/blank.gif' width='31' height='20' border='0' hspace='0' align='left'>\n";
		$questionsummary .= "\t\t\t\t\t<img src='./images/seperator.gif' border='0' hspace='0' align='left'>\n";
		$questionsummary .= "\t\t\t\t\t<img src='./images/blank.gif' width='60' height='20' border='0' hspace='0' align='left'>\n";
		$questionsummary .= "\t\t\t\t\t<img src='./images/seperator.gif' border='0' hspace='0' align='left'>\n";
		$questionsummary .= "\t\t\t\t\t<input type='image' src='./images/edit.gif' title='"._Q_EDIT_BT."' align='left' border='0' hspace='0' onclick=\"window.open('$scriptname?action=editquestion&sid=$sid&gid=$gid&qid=$qid', '_top')\">";
		if ($qct == 0 && $activated=="N") {$questionsummary .= "\t\t\t\t\t<input type='image' src='./images/delete.gif' title='"._Q_DELETE_BT."' align='left' border='0' hspace='0' onclick=\"window.open('$scriptname?action=delquestion&sid=$sid&gid=$gid&qid=$qid', '_top')\">";}
		else				 {$questionsummary .= "\t\t\t\t\t<img src='./images/blank.gif' width='20' align='left' border='0' hspace='0'>\n";}
		$questionsummary .= "\t\t\t\t\t<input type='image' src='./images/export.gif' title='"._Q_EXPORT_BT."' align='left' border='0' hspace='0' onclick=\"window.open('dumpquestion.php?qid=$qid', '_top')\">\n";
		$questionsummary .= "\t\t\t\t\t<img src='./images/seperator.gif' border='0' hspace='0' align='left'>\n";
		$questionsummary .= "\t\t\t\t\t<input type='image' src='./images/conditions.gif' border='0' hspace='0' align='left' title='"._Q_CONDITIONS_BT."' onClick=\"window.open('conditions.php?sid=$sid&qid=$qid', 'conditions', 'menubar=no, location=no, status=no, height=350, width=560, scrollbars=yes, resizable=yes')\">\n";
		$questionsummary .= "\t\t\t\t\t<img src='./images/seperator.gif' border='0' hspace='0' align='left'>\n";
		if ($qrrow['type'] == "O" || $qrrow['type'] == "L" || $qrrow['type'] == "M" || $qrrow['type']=="A" || $qrrow['type'] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "E" || $qrrow['type'] == "P" || $qrrow['type'] == "R") 
			{
			$questionsummary .= "\t\t\t\t\t<input type='image' src='./images/answers.gif' border='0' hspace='0' align='left' title='"._Q_ANSWERS_BT."' onClick=\"window.open('admin.php?sid=$sid&gid=$gid&qid=$qid&viewanswer=Y', '_top')\">\n";
			}
		$questionsummary .= "\t\t\t\t\t</td>\n";
		$questionsummary .= "\t\t\t\t\t<td align='right'>\n";
		$questionsummary .= "\t\t\t\t\t<input type='image' src='./images/close.gif' title='"._Q_CLOSE_BT."' align='right' border='0' hspace='0' onclick=\"window.open('$scriptname?sid=$sid&gid=$gid', '_top')\">\n";
		$questionsummary .= "\t\t\t\t\t<input type='image' src='./images/plus.gif' title='"._Q_MAXIMISE_BT."' align='right' border='0' hspace='0' onclick='showdetails(\"showq\")'>";
		$questionsummary .= "\t\t\t\t\t<input type='image' src='./images/minus.gif' title='"._Q_MINIMISE_BT."' align='right' border='0' hspace='0' onclick='showdetails(\"hideq\")'>\n";
		$questionsummary .= "\t\t\t\t</td></tr>\n";
		$questionsummary .= "\t\t\t</table>\n";
		$questionsummary .= "\t\t</td>\n";
		$questionsummary .= "\t</tr>\n";
		if ($_GET['viewanswer'] || $_POST['viewanswer'])
			{
			$qshowstyle = "style='display: none'";
			}
		$questionsummary .= "\t<tr $qshowstyle id='surveydetails30'><td width='20%' align='right'>$setfont<b>"._QL_TITLE."</b></font></td>\n";
		$questionsummary .= "\t<td>$setfont{$qrrow['title']}";
		if ($qrrow['mandatory'] == "Y") {$questionsummary .= ": (<i>Mandatory Question</i>)";}
		else {$questionsummary .= ": (<i>Optional Question</i>)";}
		$questionsummary .= "</td></tr>\n";
		$questionsummary .= "\t<tr $qshowstyle id='surveydetails31'><td align='right' valign='top'>$setfont<b>"._QL_QUESTION."</b></font></td>\n\t<td>$setfont{$qrrow['question']}</td></tr>\n";
		$questionsummary .= "\t<tr $qshowstyle id='surveydetails32'><td align='right' valign='top'>$setfont<b>"._QL_HELP."</b></font></td>\n\t<td>$setfont{$qrrow['help']}</td></tr>\n";
		$qtypes = getqtypelist("", "array"); //qtypes = array(type code=>type description)
		$questionsummary .= "\t<tr $qshowstyle id='surveydetails33'><td align='right' valign='top'>$setfont<b>"._QL_TYPE."</b></font></td>\n\t<td>$setfont{$qtypes[$qrrow['type']]}</td></tr>\n";
		if ($qct == 0 && ($qrrow['type'] == "O" || $qrrow['type'] == "L" || $qrrow['type'] == "M" || $qrrow['type'] == "A" || $qrrow['type'] == "B" || $qrrow['type'] == "C" || $qrrow['type'] == "E" || $qrrow['type'] == "P" || $qrrow['type'] == "R"))
			{
			$questionsummary .= "\t\t<tr $qshowstyle id='surveydetails34'><td></td><td><font face='verdana' size='1' color='green'>WARNING: You need to Add Answers to this question <input type='image' src='./images/answers.gif' border='0' hspace='0' title='Add Answers' onClick=\"window.open('admin.php?sid=$sid&gid=$gid&qid=$qid&viewanswer=Y', '_top')\"></font></td></tr>\n";
			}
		if ($qrrow['type'] == "M" or $qrrow['type'] == "P")
			{
			$questionsummary .= "\t<tr $qshowstyle id='surveydetails34'><td align='right' valign='top'>$setfont<b>"._QL_OTHER."</b></font></td>\n\t<td>$setfont{$qrrow['other']}</td></tr>\n";
			}
		}
	$questionsummary .= "</table>\n";
	}

if ($_GET['viewanswer'] || $_POST['viewanswer'])
	{
	$qquery = "SELECT type FROM questions WHERE qid=$qid";
	$qresult = mysql_query($qquery);
	while ($qrow=mysql_fetch_array($qresult)) {$qtype=$qrow['type'];}
	if (!$_POST['ansaction'])
		{
		//check if any nulls exist. If they do, redo the sortorders
		$caquery="SELECT * FROM answers WHERE qid=$qid AND sortorder is null";
		$caresult=mysql_query($caquery);
		$cacount=mysql_num_rows($caresult);
		if ($cacount)
			{
			fixsortorder($qid);
			}
		}
	$vasummary .= "<table width='100%' align='center' border='0' bgcolor='#EEEEEE'>\n";
	$vasummary .= "<tr bgcolor='#555555'><td colspan='5'><font size='1' color='white'><b>"._ANSWERS."</b></td></tr>\n";
	$cdquery = "SELECT * FROM answers WHERE qid=$qid ORDER BY sortorder, answer";
	$cdresult = mysql_query($cdquery);
	$cdcount = mysql_num_rows($cdresult);
	$vasummary .= "\t<tr><th>$setfont"._AL_CODE."</th><th>$setfont"._AL_ANSWER."</th><th>$setfont"._AL_DEFAULT."</th><th>$setfont"._AL_MOVE."</th><th>$setfont"._AL_ACTION."</th></tr>\n";
	$position=0;
	while ($cdrow = mysql_fetch_array($cdresult))
		{
		$cdrow['answer']=htmlentities($cdrow['answer']);
		$position=sprintf("%05d", $position);
		if ($cdrow['sortorder'] || $cdrow['sortorder'] == "0") {$position=$cdrow['sortorder'];}
		$vasummary .= "\t<tr><form action='admin.php' method='post'>\n";
		$vasummary .= "\t\t<td align='center'>";
		if (($activated == "Y" && $qtype == "L") || ($activated == "N"))
			{
			$vasummary .="<input name='code' type='text' $btstyle value=\"{$cdrow['code']}\" size='5'>";
			}
		else
			{
			$vasummary .= "$setfont<font size='1'>{$cdrow['code']}<input type='hidden' name='code' value='{$cdrow['code']}'>";
			}
		$vasummary .="</td>\n";
		$vasummary .= "\t\t<td align='center'><input name='answer' type='text' $btstyle value=\"{$cdrow['answer']}\" size='50'></td>\n";
		$vasummary .= "\t\t<input name='sortorder' type='hidden' $btstyle value=\"$position\" >";
		$vasummary .= "\t\t<td align='center'>";
		if (($activated == "Y" && $qtype == "L") || ($activated == "N"))
			{
			$vasummary .= "<input name='default' type='text' $btstyle value=\"{$cdrow['default']}\" size='2'></td>\n";
			}
		else
			{
			$vasummary .= "$setfont<font size='1'>{$cdrow['default']}<input type='hidden' name='default' value='{$cdrow['default']}'>";
			}
		$vasummary .= "\t\t<td align='center'>";
		if ($position > 0) {$vasummary .= "<input name='ansaction' $btstyle type='submit' value='Up'>";}
		else {$vasummary .= "&nbsp;&nbsp;&nbsp;&nbsp;";}
		if ($position < $cdcount-1) {$vasummary .= "<input name='ansaction' $btstyle type='submit' value='Dn'>";}
		else {$vasummary .= "&nbsp;&nbsp;&nbsp;&nbsp;";}
		$vasummary .= "\t\t</td>\n";
		if (($activated == "Y" && $qtype == "L") || ($activated == "N"))
			{
			$vasummary .= "\t\t<td>\n";
			$vasummary .= "\t\t\t<input name='ansaction' $btstyle type='submit' value='Save'>";
			$vasummary .= "<input name='ansaction' $btstyle type='submit' value='Del'>\n";
			$vasummary .= "\t\t</td>\n";
		}
		else
			{
			$vasummary .= "\t\t<td><input name='ansaction' $btstyle type='submit' value='Save'></td>\n";
			}
		$vasummary .= "\t<input type='hidden' name='oldcode' value=\"{$cdrow['code']}\">\n";
		$vasummary .= "\t<input type='hidden' name='oldanswer' value=\"{$cdrow['answer']}\">\n";
		$vasummary .= "\t<input type='hidden' name='sid' value='$sid'>\n";
		$vasummary .= "\t<input type='hidden' name='gid' value='$gid'>\n";
		$vasummary .= "\t<input type='hidden' name='qid' value='$qid'>\n";
		$vasummary .= "\t<input type='hidden' name='viewanswer' value='Y'>\n";
		$vasummary .= "\t<input type='hidden' name='action' value='modanswer'>\n";
		$vasummary .= "\t</form></tr>\n";
		$position++;
		}
	if (($activated == "Y" && $qtype == "L") || ($activated == "N"))
		{
		$position=sprintf("%05d", $position);
		$vasummary .= "\t<tr><form action='admin.php' method='post'>\n";
		$vasummary .= "\t\t<td align='center'><input name='code' type='text' $btstyle size='5'></td>\n";
		$vasummary .="\t\t<td align='center'><input name='answer' type='text' $btstyle size='50'></td>\n";
		$vasummary .="\t\t<input name='sortorder' type='hidden' $btstyle value='$position'>\n";
		$vasummary .="\t\t<td align='center'><input name='default' type='text' $btstyle value='N' size='2'></td>\n";
		$vasummary .= "\t\t<td></td>\n";
		$vasummary .="\t\t<td align='center'><input name='ansaction' $btstyle type='submit' value='Add'></td>\n";
		$vasummary .= "\t<input type='hidden' name='sid' value='$sid'>\n";
		$vasummary .= "\t<input type='hidden' name='gid' value='$gid'>\n";
		$vasummary .= "\t<input type='hidden' name='qid' value='$qid'>\n";
		$vasummary .= "\t<input type='hidden' name='action' value='modanswer'>\n";
		$vasummary .= "\t<input type='hidden' name='viewanswer' value='Y'>\n";
		$vasummary .="\t</form></tr>\n";
		}
	if ($cdcount > 0)
		{
		$vasummary .= "<tr><form action='admin.php' method='post'><td colspan='3'></td>";
		$vasummary .= "<td align='center'><input $btstyle type='submit' name='ansaction' value='Fix Sort'></td><td></td>\n";
		$vasummary .= "\t<input type='hidden' name='sid' value='$sid'>\n";
		$vasummary .= "\t<input type='hidden' name='gid' value='$gid'>\n";
		$vasummary .= "\t<input type='hidden' name='qid' value='$qid'>\n";
		$vasummary .= "\t<input type='hidden' name='action' value='modanswer'>\n";
		$vasummary .= "\t<input type='hidden' name='viewanswer' value='Y'>\n";
		$vasummary .= "</form></tr>\n";
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
	$usersummary = "<table width='100%' border='0'>\n\t<tr><td colspan='3' bgcolor='black' align='center'>\n";
	$usersummary .= "\t\t<b>$setfont<font color='white'>Modify User</td></tr>\n";
	$muq = "SELECT * FROM users WHERE user='$user' LIMIT 1";
	$mur = mysql_query($muq);
	$usersummary .= "\t<tr><form action='$scriptname' method='post'>";
	while ($mrw = mysql_fetch_array($mur))
		{
		$usersummary .= "\t<td>$setfont<b>{$mrw['user']}</b></font></td>\n";
		$usersummary .= "\t\t<input type='hidden' name='user' value='{$mrw['user']}'>\n";
		$usersummary .= "\t<td>\n\t\t<input type='text' name='pass' value='{$mrw['password']}'></td>\n";
		$usersummary .= "\t<td>\n\t\t<input type='text' size='2' name='level' value='{$mrw['security']}'></td>\n";
		}
	$usersummary .= "\t</tr>\n\t<tr><td colspan='3' align='center'>\n";
	$usersummary .= "\t\t<input type='submit' $btstyle value='Update'></td></tr>\n";
	$usersummary .= "<input type='hidden' name='action' value='moduser'>\n";
	$usersummary .= "</form></table>\n";
	}

if ($action == "editusers")
	{
	if (!file_exists("$homedir/.htaccess"))
		{
		$usersummary = "<table width='100%' border='0'>\n";
		$usersummary .= "\t<tr>\n";
		$usersummary .= "\t\t<td bgcolor='black' align='center'>\n";
		$usersummary .= "\t\t\t<b>$setfont<font color='WHITE'>Security Control</font></font></b>\n";
		$usersummary .= "\t\t</td>\n";
		$usersummary .= "\t</tr>\n";
		$usersummary .= "\t<tr>\n";
		$usersummary .= "\t\t<td>\n";
		$usersummary .= "\t\t\t$setfont<font color='RED'><b>Warning:</font></font></b><br />\n";
		$usersummary .= "\t\t\tYou have not yet initialised security settings for your survey system \n";
		$usersummary .= "\t\t\tand subsequently there are no restrictions on access.\n";
		$usersummary .= "\t\t\t<p>If you click on the 'initialise security' button below, standard APACHE \n";
		$usersummary .= "\t\t\tsecurity settings will be added to the administration directory of this \n";
		$usersummary .= "\t\t\tscript. You will then need to use the default access username and password \n";
		$usersummary .= "\t\t\tto access the administration and data entry scripts.</p>\n";
		$usersummary .= "\t\t\t<p>USERNAME: $defaultuser<br />PASSWORD: $defaultpass</p>\n";
		$usersummary .= "\t\t\t<p>It is highly recommended that once your security system has been initialised \n";
		$usersummary .= "\t\t\tyou change this default password.</p>\n";
		$usersummary .= "\t\t</td>\n";
		$usersummary .= "\t</tr>\n";
		$usersummary .= "\t<tr>\n";
		$usersummary .= "\t\t<td align='center'>\n";
		$usersummary .= "\t\t\t<input type='submit' $btstyle value='Initialise Security' onClick=\"window.open('$scriptname?action=setupsecurity', '_top')\">\n";
		$usersummary .= "\t\t</td>\n";
		$usersummary .= "\t</tr>\n";
		$usersummary .= "</table>\n";
		}
	else
		{
		$usersummary = "<table width='100%' border='0'>\n";
		$usersummary .= "\t<tr>\n";
		$usersummary .= "\t\t<td colspan='4' bgcolor='black' align='center'>\n";
		$usersummary .= "\t\t\t<b>$setfont<font color='white'>List of users</font><font></b>\n";
		$usersummary .= "\t\t</td>\n";
		$usersummary .= "\t</tr>\n";
		$usersummary .= "\t<tr bgcolor='#444444'>\n";
		$usersummary .= "\t\t<td>$setfont<font color='white'><b>User</b></td>\n";
		$usersummary .= "\t\t<td>$setfont<font color='white'><b>Password</b></font></font></td>\n";
		$usersummary .= "\t\t<td>$setfont<font color='white'><b>Security</b></font></font></td>\n";
		$usersummary .= "\t\t<td>$setfont<font color='white'><b>Actions</b></font></font></td>\n";
		$usersummary .= "\t</tr>\n";
		$userlist = getuserlist();
		$ui = count($userlist);
		if ($ui < 1)
			{
			$usersummary .= "\t<tr>\n";
			$usersummary .= "\t\t<td>\n";
			$usersummary .= "\t\t\t<center>WARNING: No users exist in your table. We recommend you 'turn off' security. You can then 'turn it on' again.</center>";
			$usersummary .= "\t\t</td>\n";
			$usersummary .= "\t</tr>\n";
			}
		else
			{
			foreach ($userlist as $usr)
				{
				$usersummary .= "\t<tr>\n";
				$usersummary .= "\t<td>$setfont<b>{$usr['user']}</b></font></td>\n";
				$usersummary .= "\t\t<td>$setfont{$usr['password']}</font></td>\n";
				$usersummary .= "\t\t<td>$setfont{$usr['security']}</td>\n";
				$usersummary .= "\t\t<td>\n";
				$usersummary .= "\t\t\t<input type='submit' $btstyle value='Edit' onClick=\"window.open('$scriptname?action=modifyuser&user={$usr['user']}', '_top')\" />\n";
				if ($ui > 1 )
					{
					$usersummary .= "\t\t\t<input type='submit' $btstyle value='Del' onClick=\"window.open('$scriptname?action=deluser&user={$usr['user']}', '_top')\" />\n";
					}
				$usersummary .= "\t\t</td>\n";
				$usersummary .= "\t</tr>\n";
				$ui++;
				}
			}
		$usersummary .= "\t\t<form action='$scriptname' method='post'>\n";
		$usersummary .= "\t\t<tr bgcolor='#EEEFFF'>\n";
		$usersummary .= "\t\t<td><input type='text' $slstyle name='user'></td>\n";
		$usersummary .= "\t\t<td><input type='text' $slstyle name='pass'></td>\n";
		$usersummary .= "\t\t<td><input type='text' $slstyle name='level' size='2'></td>\n";
		$usersummary .= "\t\t<td><input type='submit' $btstyle value='Add New User'></td>\n";
		$usersummary .= "\t</tr>\n";
		$usersummary .= "\t<tr>\n";
		$usersummary .= "\t\t<td><input type='hidden' name='action' value='adduser'></td>\n";
		$usersummary .= "\t</tr>\n";
		$usersummary .= "\t</form>\n";
		$usersummary .= "\t<tr>\n";
		$usersummary .= "\t\t<td colspan='3'></td>\n";
		$usersummary .= "\t\t<td><input type='submit' $btstyle value='Turn Off Security' ";
		$usersummary .= "onClick=\"window.open('$scriptname?action=turnoffsecurity', '_top')\" /></td>\n";
		$usersummary .= "\t</tr>\n";		
		$usersummary .= "</table>\n";
		}
	}
if ($action == "addquestion")
	{
	$newquestion = "<table width='100%' border='0'>\n\n";
	$newquestion .= "\t<tr>\n";
	$newquestion .= "\t\t<td colspan='2' bgcolor='black' align='center'>";
	$newquestion .= "\t\t<b>$setfont<font color='white'>Create New Question for Survey ID($sid), Group ID($gid) </b>\n";
	$newquestion .= "\t\t</td>\n";
	$newquestion .= "\t</tr>\n";
	$newquestion .= "\t<form action='$scriptname' name='addnewquestion' method='post'>\n";
	$newquestion .= "\t<tr>\n";
	$newquestion .= "\t\t<td align='right'>$setfont<b>Question Code:</b></font></td>\n";
	$newquestion .= "\t\t<td><input type='text' size='20' name='title'><font color='red' face='verdana' size='1'>*Required</font></td></tr>\n";
	$newquestion .= "\t<tr>\n";
	$newquestion .= "\t\t<td align='right'>$setfont<b>Question:</b></font></td>\n";
	$newquestion .= "\t\t<td><textarea cols='35' rows='3' name='question'></textarea></td>\n";
	$newquestion .= "\t</tr>\n";
	$newquestion .= "\t<tr>\n";
	$newquestion .= "\t\t<td align='right'>$setfont<b>Help:</b></font></td>\n";
	$newquestion .= "\t\t<td><textarea cols='35' rows='3' name='help'></textarea></td>\n";
	$newquestion .= "\t</tr>\n";
	$newquestion .= "\t<tr>\n";
	$newquestion .= "\t\t<td align='right'>$setfont<b>Question Type:</b></font></td>\n";
	$newquestion .= "\t\t<td><select $slstyle name='type' onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n";
	$newquestion .= "$qtypeselect";
	$newquestion .= "\t\t</select></td>\n";
	$newquestion .= "\t</tr>\n";
	
	$newquestion .= "\t<tr id='OtherSelection' style='display: none'>\n";
	$newquestion .= "\t\t<td align='right'>$setfont<b>Other?</b></font></td>\n";
	$newquestion .= "\t\t<td>$setfont\n";
	$newquestion .= "\t\t\tYes <input type='radio' name='other' value='Y' />&nbsp;&nbsp;\n";
	$newquestion .= "\t\t\tNo <input type='radio' name='other' value='N' checked />\n";
	$newquestion .= "\t\t</td>\n";
	$newquestion .= "\t</tr>\n";

	$newquestion .= "\t<tr id='MandatorySelection'>\n";
	$newquestion .= "\t\t<td align='right'>$setfont<b>Mandatory?</b></font></td>\n";
	$newquestion .= "\t\t<td>$setfont\n";
	$newquestion .= "\t\t\tYes <input type='radio' name='mandatory' value='Y' />&nbsp;&nbsp;\n";
	$newquestion .= "\t\t\tNo <input type='radio' name='mandatory' value='N' checked />\n";
	$newquestion .= "\t\t</td>\n";
	$newquestion .= "\t</tr>\n";
	
	$newquestion .= "<script type='text/javascript'>\n";
	$newquestion .= "<!--\n";
	$newquestion .= "function OtherSelection(QuestionType)\n";
	$newquestion .= "\t{\n";
	$newquestion .= "\tif (QuestionType == 'M' || QuestionType == 'P')\n";
	$newquestion .= "\t\t{\n";
	$newquestion .= "\t\tdocument.getElementById('OtherSelection').style.display = '';\n";
	$newquestion .= "\t\t}\n";
	$newquestion .= "\telse\n";
	$newquestion .= "\t\t{\n";
	$newquestion .= "\t\tdocument.getElementById('OtherSelection').style.display = 'none';\n";
	$newquestion .= "\t\tdocument.addnewquestion.other[1].checked = true;\n";
	$newquestion .= "\t\t}\n";
	$newquestion .= "\t}\n";
	$newquestion .= "\tOtherSelection('{$eqrow['type']}');\n";
	$newquestion .= "-->\n";
	$newquestion .= "</script>\n";
	
	$newquestion .= "\t<tr>\n";
	$newquestion .= "\t\t<td colspan='2' align='center'><input type='submit' $btstyle value='Add Question' /></td>\n";
	$newquestion .= "\t</tr>\n";
	$newquestion .= "\t<input type='hidden' name='action' value='insertnewquestion' />\n";
	$newquestion .= "\t<input type='hidden' name='sid' value='$sid' />\n";
	$newquestion .= "\t<input type='hidden' name='gid' value='$gid' />\n";
	$newquestion .= "\t</form>\n";
	$newquestion .= "</table>\n";
	$newquestion .= "<center><b>OR</b></center>\n";
	$newquestion .= "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
	$newquestion .= "\t\t<b>$setfont<font color='white'>Import Question</font></font></b></td></tr>\n\t<tr>";
	$newquestion .= "\t<form enctype='multipart/form-data' name='importquestion' action='$scriptname' method='post'>\n";
	$newquestion .= "\t\t<td align='right'>$setfont<b>Select SQL File:</b></font></td>\n";
	$newquestion .= "\t\t<td><input name=\"the_file\" type=\"file\" size=\"35\"></td></tr>\n";
	$newquestion .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Import Question'></TD>\n";
	$newquestion .= "\t<input type='hidden' name='action' value='importquestion'>\n";
	$newquestion .= "\t<input type='hidden' name='sid' value='$sid'>\n";
	$newquestion .= "\t<input type='hidden' name='gid' value='$gid'>\n";
	$newquestion .= "\t</tr></form>\n</table>\n";
	}

if ($action == "copyquestion")
	{
	$eqquery = "SELECT * FROM questions WHERE sid=$sid AND gid=$gid AND qid=$qid";
	$eqresult = mysql_query($eqquery);
	while ($eqrow = mysql_fetch_array($eqresult))
		{
		$editquestion = "<table width='100%' border='0'>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td colspan='2' bgcolor='black' align='center'>\n";
		$editquestion .= "\t\t\t<b>$setfont<font color='white'>Copy Question $qid (Code {$eqrow['title']})</b><br />Note: You MUST enter a new Question Code!</font></font>\n";
		$editquestion .= "\t\t</td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr><form action='$scriptname' name='editquestion' method='post'>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Question Code:</b></font></td>\n";
		$editquestion .= "\t\t<td><input type='text' size='20' name='title' value='' /></td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right' valign='top'>$setfont<b>Question:</b></font></td>\n";
		$editquestion .= "\t\t<td><textarea cols='35' rows='4' name='question'>{$eqrow['question']}</textarea></td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right' valign='top'>$setfont<b>Help:</b></font></td>\n";
		$editquestion .= "\t\t<td><textarea cols='35' rows='4' name='help'>{$eqrow['help']}</textarea></td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Type:</b></font></td>\n";
		$editquestion .= "\t\t<td><select $slstyle name='type' onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n";
		$editquestion .= getqtypelist($eqrow['type']);
		$editquestion .= "\t\t</select></td>\n";
		$editquestion .= "\t</tr>\n";
		//$editquestion .= "<TD><INPUT TYPE='TEXT' SIZE='1' NAME='type' VALUE='{$eqrow['type']}'></TD></TR>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Group?</b></font></td>\n";
		$editquestion .= "\t\t<td><select $slstyle name='gid'>\n";
		$editquestion .= getgrouplist2($eqrow['gid']);
		$editquestion .= "\t\t\t</select></td>\n";
		$editquestion .= "\t</tr>\n";
		
		$editquestion .= "\t<tr id='OtherSelection' style='display: none'>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Other?</b></font></td>\n";
		$editquestion .= "\t\t<td>$setfont\n";
		$editquestion .= "\t\t\tYes <input type='radio' name='other' value='Y'";
		if ($eqrow['other'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n";
		$editquestion .= "\t\t\tNo <input type='radio' name='other' value='N'";
		if ($eqrow['other'] == "N") {$editquestion .= " checked";}
		$editquestion .= " />\n";
		$editquestion .= "\t\t</td>\n";
		$editquestion .= "\t</tr>\n";
		
		$editquestion .= "\t<tr id='MandatorySelection'>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Mandatory?</b></font></td>\n";
		$editquestion .= "\t\t<td>$setfont\n";
		$editquestion .= "\t\t\tYes <input type='radio' name='mandatory' value='Y'";
		if ($eqrow['mandatory'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n";
		$editquestion .= "\t\t\tNo <input type='radio' name='mandatory' value='N'";
		if ($eqrow['mandatory'] != "Y") {$editquestion .= " checked";}
		$editquestion .= " />\n";
		$editquestion .= "\t\t</td>\n";
		$editquestion .= "\t</tr>\n";
		
		$editquestion .= "<script type='text/javascript'>\n";
		$editquestion .= "<!--\n";
		$editquestion .= "function OtherSelection(QuestionType)\n";
		$editquestion .= "\t{\n";
		$editquestion .= "\tif (QuestionType == 'M' || QuestionType == 'P')\n";
		$editquestion .= "\t\t{\n";
		$editquestion .= "\t\tdocument.getElementById('OtherSelection').style.display = '';\n";
		$editquestion .= "\t\t}\n";
		$editquestion .= "\telse\n";
		$editquestion .= "\t\t{\n";
		$editquestion .= "\t\tdocument.getElementById('OtherSelection').style.display = 'none';\n";
		$editquestion .= "\t\tdocument.editquestion.other[1].checked = true;\n";
		$editquestion .= "\t\t}\n";
		$editquestion .= "\t}\n";
		$editquestion .= "\tOtherSelection('{$eqrow['type']}');\n";
		$editquestion .= "-->\n";
		$editquestion .= "</script>\n";
		
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>Copy answers:</b></font></td>\n";
		$editquestion .= "\t\t<td>$setfont<input type='checkbox' checked name='copyanswers' value='Y' /></font></td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td colspan='2' align='center'><input type='submit' $btstyle value='Copy Question'></td>\n";
		$editquestion .= "\t\t<input type='hidden' name='action' value='copynewquestion'>\n";
		$editquestion .= "\t\t<input type='hidden' name='sid' value='$sid' />\n";
		$editquestion .= "\t\t<input type='hidden' name='oldqid' value='$qid' />\n";
		$editquestion .= "\t</form></tr>\n";
		$editquestion .= "</table>\n";
		}
	}

if ($action == "addanswer")
	{
	//Get sortorder number that is one greater than the last.
	$saquery="SELECT sortorder FROM answers WHERE qid=$qid ORDER BY sortorder desc LIMIT 1";
	$saresult=mysql_query($saquery) or die ("Couldn't get last sortorder<br />$saquery<br />".mysql_error());
	while ($sarow=mysql_fetch_array($saresult)) {$lastsa=$sarow['sortorder'];}
	if ($lastsa) 
		{
		if (strlen($lastsa)>1) 
			{
			$newsa=substr($lastsa, 0, strlen($lastsa)-1).chr(ord(strtoupper(substr($lastsa, -1)))+1);
			$lastsa=substr($lastsa, -1);
			} 
		else
			{
			$newsa=chr(ord(strtoupper($lastsa))+1);
			}
		}
	else {$newsa=1;}
	
	$newanswer = "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
	$newanswer .= "\t\t<b>$setfont<font color='white'>Create New Answer for Survey ID($sid), Group ID($gid), Question ID($qid)</b></font></font></td></tr>\n";
	$newanswer .= "\t<tr><form action='$scriptname' name='addnewanswer' method='post'>\n";
	$newanswer .= "\t\t<td align='right'>$setfont<b>Answer Code:</b></font></td>\n";
	$newanswer .= "\t\t<td><input type='text' size='5' name='code' maxlength='5'><font color='red' face='verdana' size='1'>*Required</font></td></tr>\n";
	$newanswer .= "\t<tr><td align='right'>$setfont<b>Answer:</b></font></td>\n";
	$newanswer .= "\t\t<td><input type='text' name='answer'><font color='red' face='verdana' size='1'>*Required</font></td></tr>\n";
	$newanswer .= "\t<tr><td align='right'>$setfont<b>Sort Order:</b></font></td>\n";
	$newanswer .= "\t\t<td><input type='text' size='5' maxlength='5' value='$newsa' name='sortorder'></td></tr>\n";
	$newanswer .= "\t<tr><td align='right'>$setfont<b>Default?</b></font></td>\n";
	$newanswer .= "\t\t<td><input type='text' size='1' maxlength='1' value='N' name='default'></td></tr>\n";
	$newanswer .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Add Answer'></td></tr>\n";
	$newanswer .= "\t<input type='hidden' name='action' value='insertnewanswer'>\n";
	$newanswer .= "\t<input type='hidden' name='qid' value='$qid'>\n";
	$newanswer .= "\t<input type='hidden' name='sid' value='$sid'>\n";
	$newanswer .= "\t<input type='hidden' name='gid' value='$gid'>\n";
	$newanswer .= "</form></table>\n";
	} 

if ($action == "editanswer")
	{
	$eaquery = "SELECT * FROM answers WHERE qid=$qid AND code='$code'";
	$earesult = mysql_query($eaquery);
	while ($earow = mysql_fetch_array($earesult))
		{
		$editanswer = "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
		$editanswer .= "\t\t<b>$setfont<font color='WHITE'>Edit Answer $qid, $code</b></font></font></td></tr>\n";
		$editanswer .= "\t<tr><form action='$scriptname' name='editanswer' method='post'>\n";
		$editanswer .= "\t\t<td align='right'>$setfont<b>Answer Code:</b></font></td>\n";
		$editanswer .= "\t\t<td><input type='text' size='5' value='{$earow['code']}' name='code'></td></tr>\n";
		$editanswer .= "\t<tr><td align='right'>$setfont<b>Answer:</b></font></td>\n";
		$editanswer .= "\t\t<td><input type='text' value=\"".str_replace('"', "&quot;", $earow['answer'])."\" name='answer'></td></tr>\n";
		$editanswer .= "\t<tr><td align='right'>$setfont<b>Sort Order:</b></font></td>\n";
		$editanswer .= "\t\t<td><input type='text' size='5' maxlength='5' value='{$earow['sortorder']}' name='sortorder'></td></tr>\n";
		$editanswer .= "\t<tr><td align='right'>$setfont<b>Default?</b></font></td>\n";
		$editanswer .= "\t\t<td><input type='text' size='1' maxlength='1' value='{$earow['default']}' name='default'></td></tr>\n";
		$editanswer .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Update Answer'></td>\n";
		$editanswer .= "\t<input type='hidden' name='action' value='updateanswer'>\n";
		$editanswer .= "\t<input type='hidden' name='sid' value='$sid'>\n";
		$editanswer .= "\t<input type='hidden' name='gid' value='$gid'>\n";
		$editanswer .= "\t<input type='hidden' name='qid' value='$qid'>\n";
		$editanswer .= "\t<input type='hidden' name='old_code' value='{$earow['code']}'>\n";
		$editanswer .= "\t</form></tr>\n"; 
		$editanswer .= "</table>\n";
		}
	}

if ($action == "addgroup")
	{
	$newgroup = "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
	$newgroup .= "\t\t<b>$setfont<font color='white'>Create New Group for Survey ID($sid)</font></font></b></td></tr>\n";
	$newgroup .= "\t<tr><form action='$scriptname' name='addnewgroup' method='post'>\n";
	$newgroup .= "\t\t<td align='right'>$setfont<b>"._GL_TITLE."</b></font></td>\n";
	$newgroup .= "\t\t<td><input type='text' size='40' name='group_name'><font color='red' face='verdana' size='1'>*Required</font></td></tr>\n";
	$newgroup .= "\t<tr><td align='right'>$setfont<b>"._GL_DESCRIPTION."</b>(optional)</font></td>\n";
	$newgroup .= "\t\t<td><textarea cols='40' rows='4' name='description'></textarea></td></tr>\n";
	$newgroup .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Create New Group'></td>\n";
	$newgroup .= "\t<input type='hidden' name='action' value='insertnewgroup'>\n";
	$newgroup .= "\t<input type='hidden' name='sid' value='$sid'>\n";
	$newgroup .= "\t</form></tr>\n";
	$newgroup .= "</table>\n";
	}

if ($action == "editgroup")
	{
	$egquery = "SELECT * FROM groups WHERE sid=$sid AND gid=$gid";
	$egresult = mysql_query($egquery);
	while ($esrow = mysql_fetch_array($egresult))	
		{
		$editgroup = "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
		$editgroup .= "\t\t<b>$setfont<font color='white'>Edit Group for Survey ID($sid)</font></font></b></td></tr>\n";
		$editgroup .= "\t<tr><form action='$scriptname' name='editgroup' method='post'>\n";
		$editgroup .= "\t\t<td align='right' width='20%'>$setfont<b>"._GL_TITLE."</b></font></td>\n";
		$editgroup .= "\t\t<td><input type='text' size='40' name='group_name' value='{$esrow['group_name']}'></td></tr>\n";
		$editgroup .= "\t<tr><td align='right'>$setfont<b>"._GL_DESCRIPTION."</b>(optional)</font></td>\n";
		$editgroup .= "\t\t<td><textarea cols='40' rows='4' name='description'>{$esrow['description']}</textarea></td></tr>\n";
		$editgroup .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Update Group'></td>\n";
		$editgroup .= "\t<input type='hidden' name='action' value='updategroup'>\n";
		$editgroup .= "\t<input type='hidden' name='sid' value='$sid'>\n";
		$editgroup .= "\t<input type='hidden' name='gid' value='$gid'>\n";
		$editgroup .= "\t</form></tr>\n";
		$editgroup .= "</table>\n";
		}
	}
	
if ($action == "editquestion")
	{
	$eqquery = "SELECT * FROM questions WHERE sid=$sid AND gid=$gid AND qid=$qid";
	$eqresult = mysql_query($eqquery);
	while ($eqrow = mysql_fetch_array($eqresult))
		{
		$editquestion = "<table width='100%' border='0'>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td colspan='2' bgcolor='black' align='center'>\n";
		$editquestion .= "\t\t\t<b>$setfont<font color='white'>Edit Question $qid</b></font></font>\n";
		$editquestion .= "\t\t</td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr><form action='$scriptname' name='editquestion' method='post'>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>"._QL_CODE."</b></font></td>\n";
		$editquestion .= "\t\t<td><input type='text' size='20' name='title' value='{$eqrow['title']}'></td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right' valign='top'>$setfont<b>"._QL_QUESTION."</b></font></td>\n";
		$editquestion .= "\t\t<td><textarea cols='35' rows='4' name='question'>{$eqrow['question']}</textarea></td>\n";
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right' valign='top'>$setfont<b>"._QL_HELP."</b></font></td>\n";
		$editquestion .= "\t\t<td><textarea cols='35' rows='4' name='help'>{$eqrow['help']}</textarea></td>\n";
		$editquestion .= "\t</tr>\n";
		//question type:
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>"._QL_TYPE."</b></font></td>\n";
		if ($activated != "Y")
			{
			$editquestion .= "\t\t<td><select $slstyle name='type' onchange='OtherSelection(this.options[this.selectedIndex].value);'>\n";
			$editquestion .= getqtypelist($eqrow['type']);
			$editquestion .= "\t\t</select></td>\n";
			}
		else
			{
			$editquestion .= "\t\t<td>{$setfont}[{$eqrow['type']}] - Cannot be changed in activated survey.\n";
			$editquestion .= "\t\t\t<input type='hidden' name='type' value='{$eqrow['type']}'>\n";
			$editquestion .= "\t\t</td>\n";
			}
		$editquestion .= "\t</tr>\n";
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t<td align='right'>$setfont<b>"._GL_GROUP."</b></font></td>\n";
		$editquestion .= "\t\t<td><select $slstyle name='gid'>\n";
		$editquestion .= getgrouplist2($eqrow['gid']);
		$editquestion .= "\t\t</select></td>\n";
		$editquestion .= "\t</tr>\n";
		
		$editquestion .= "\t<tr id='OtherSelection'>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>"._GL_OTHER."</b></font></td>\n";
		$editquestion .= "\t\t<td>$setfont\n";
		$editquestion .= "\t\t\tYes <input type='radio' name='other' value='Y'";
		if ($eqrow['other'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n";
		$editquestion .= "\t\t\tNo <input type='radio' name='other' value='N'";
		if ($eqrow['other'] == "N") {$editquestion .= " checked";}
		$editquestion .= " />\n";
		$editquestion .= "\t\t</td>\n";
		$editquestion .= "\t</tr>\n";
		
		$editquestion .= "\t<tr id='MandatorySelection'>\n";
		$editquestion .= "\t\t<td align='right'>$setfont<b>"._GL_MANDATORY."</b></font></td>\n";
		$editquestion .= "\t\t<td>$setfont\n";
		$editquestion .= "\t\t\tYes <input type='radio' name='mandatory' value='Y'";
		if ($eqrow['mandatory'] == "Y") {$editquestion .= " checked";}
		$editquestion .= " />&nbsp;&nbsp;\n";
		$editquestion .= "\t\t\tNo <input type='radio' name='mandatory' value='N'";
		if ($eqrow['mandatory'] != "Y") {$editquestion .= " checked";}
		$editquestion .= " />\n";
		$editquestion .= "\t\t</td>\n";
		$editquestion .= "\t</tr>\n";
		
		$editquestion .= "<script type='text/javascript'>\n";
		$editquestion .= "<!--\n";
		$editquestion .= "function OtherSelection(QuestionType)\n";
		$editquestion .= "\t{\n";
		$editquestion .= "\tif (QuestionType == 'M' || QuestionType == 'P')\n";
		$editquestion .= "\t\t{\n";
		$editquestion .= "\t\tdocument.getElementById('OtherSelection').style.display = '';\n";
		$editquestion .= "\t\t}\n";
		$editquestion .= "\telse\n";
		$editquestion .= "\t\t{\n";
		$editquestion .= "\t\tdocument.getElementById('OtherSelection').style.display = 'none';\n";
		$editquestion .= "\t\tdocument.editquestion.other[1].checked = true;\n";
		$editquestion .= "\t\t}\n";
		$editquestion .= "\t}\n";
		$editquestion .= "\tOtherSelection('{$eqrow['type']}');\n";
		$editquestion .= "-->\n";
		$editquestion .= "</script>\n";
		
		$editquestion .= "\t<tr>\n";
		$editquestion .= "\t\t<td colspan='2' align='center'><input type='submit' $btstyle value='Update Question'></td>\n";
		$editquestion .= "\t<input type='hidden' name='action' value='updatequestion'>\n";
		$editquestion .= "\t<input type='hidden' name='sid' value='$sid'>\n";
		$editquestion .= "\t<input type='hidden' name='qid' value='$qid'>\n";
		$editquestion .= "\t</form></tr>\n";
		$editquestion .= "</table>\n";
		}
	}

if ($action == "editsurvey")
	{
	$esquery = "SELECT * FROM surveys WHERE sid=$sid";
	$esresult = mysql_query($esquery);
	while ($esrow = mysql_fetch_array($esresult))	
		{
		$editsurvey = "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>";
		$editsurvey .= "\t\t<b>$setfont<font color='white'>Edit Survey</font></font></b></td></tr>\n";
		$editsurvey .= "\t<tr><form name='addnewsurvey' action='$scriptname' method='post'>\n";
		$editsurvey .= "\t\t<td align='right'><b>$setfont"._SL_TITLE."</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='20' name='short_title' value='{$esrow['short_title']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'><b>$setfont"._SL_DESCRIPTION."</font></b></td>\n";
		$editsurvey .= "\t\t<td><textarea cols='35' rows='5' name='description'>{$esrow['description']}</textarea></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_WELCOME."</b></font></td>\n";
		$editsurvey .= "\t\t<td><textarea cols='35' rows='5' name='welcome'>".str_replace("<br />", "\n", $esrow['welcome'])."</textarea></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_ADMIN."</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='20' name='admin' value='{$esrow['admin']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_EMAIL."</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='20' name='adminemail' value='{$esrow['adminemail']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_FAXTO."</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='20' name='faxto' value='{$esrow['faxto']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_FORMAT."</b></font></td>\n";
		$editsurvey .= "\t\t<td><select name='format'>\n";
		$editsurvey .= "\t\t\t<option value='S'";
		if ($esrow['format'] == "S" || !$esrow['format']) {$editsurvey .= " selected";}
		$editsurvey .= ">One at a time</option>\n";
		$editsurvey .= "\t\t\t<option value='G'";
		if ($esrow['format'] == "G") {$editsurvey .= " selected";}
		$editsurvey .= ">Group at a time</option>\n";
		$editsurvey .= "\t\t\t<option value='A'";
		if ($esrow['format'] == "A") {$editsurvey .= " selected";}
		$editsurvey .= ">All in one</option>\n";
		$editsurvey .= "\t\t</select></td>\n";
		$editsurvey .= "\t</tr>\n";

		$editsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_TEMPLATE."</b></font></td>\n";
		$editsurvey .= "\t\t<td><select name='template'>\n";
		foreach (gettemplatelist() as $tname)
			{
			$editsurvey .= "\t\t\t<option value='$tname'";
			if ($esrow['template'] && $tname == $esrow['template']) {$editsurvey .= " selected";}
			elseif (!$esrow['template'] && $tname == "default") {$editsurvey .= " selected";}
			$editsurvey .= ">$tname</option>\n";
			}
		$editsurvey .= "\t\t</select></td>\n";
		$editsurvey .= "\t</tr>\n";
		
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_ANONYMOUS."</b></font></td>\n";
				
		if ($esrow['active'] == "Y")
			{
			$editsurvey .= "\t\t<td>\n\t\t\t$setfont";
			if ($esrow['private'] == "N") {$editsurvey .= " This survey is <b>not</b> anonymous";}
			else {$editsurvey .= "This survey <b>is</b> anonymous";}
			$editsurvey .= "<font size='1' color='red'>&nbsp;(Cannot be changed)\n";
			$editsurvey .= "\t\t</td>\n";
			}
		else
			{
			$editsurvey .= "\t\t<td><select name='private'>\n";
			$editsurvey .= "\t\t\t<option value='Y'";
			if ($esrow['private'] == "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">Yes</option>\n";
			$editsurvey .= "\t\t\t<option value='N'";
			if ($esrow['private'] != "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">No</option>\n";
			$editsurvey .= "</select>\n\t\t</td>\n";
			}
		$editsurvey .= "</tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_DATESTAMP."</b></font></td>\n";
				
		if ($esrow['active'] == "Y")
			{
			$editsurvey .= "\t\t<td>\n\t\t\t$setfont";
			if ($esrow['datestamp'] != "Y") {$editsurvey .= " Responses <b>will not</b> be date stamped.";}
			else {$editsurvey .= "Responses <b>will</b> be date stamped";}
			$editsurvey .= "<font size='1' color='red'>&nbsp;(Cannot be changed)\n";
			$editsurvey .= "\t\t</td>\n";
			}
		else
			{
			$editsurvey .= "\t\t<td><select name='datestamp'>\n";
			$editsurvey .= "\t\t\t<option value='Y'";
			if ($esrow['datestamp'] == "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">Yes</option>\n";
			$editsurvey .= "\t\t\t<option value='N'";
			if ($esrow['datestamp'] != "Y") {$editsurvey .= " selected";}
			$editsurvey .= ">No</option>\n";
			$editsurvey .= "</select>\n\t\t</td>\n";
			}
		$editsurvey .= "</tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_LANGUAGE."</b></font></td>\n";
		$editsurvey .= "\t\t<td><select name='language'>\n";
		foreach (getlanguages() as $langname)
			{
			$editsurvey .= "\t\t\t<option value='$langname'";
			if ($esrow['language'] && $esrow['language'] == $langname) {$editsurvey .= " selected";}
			if (!$esrow['language'] && $defaultlang && $defaultlang == $langname) {$editsurvey .= " selected";}
			$editsurvey .= ">$langname</option>\n";
			}
		$editsurvey .= "\t\t</select></td>\n";
		$editsurvey .= "\t</tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_EXPIRES."</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='10' name='expires' value='{$esrow['expires']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_URL."</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='50' name='url' value='{$esrow['url']}'></td></tr>\n";
		$editsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_URLDESCRIP."</b></font></td>\n";
		$editsurvey .= "\t\t<td><input type='text' size='50' name='urldescrip' value='{$esrow['urldescrip']}'></td></tr>\n";

		$editsurvey .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Update Survey'></td>\n";
		$editsurvey .= "\t<input type='hidden' name='action' value='updatesurvey'>\n";
		$editsurvey .= "\t<input type='hidden' name='sid' value='{$esrow['sid']}'>\n";
		$editsurvey .= "\t</form></tr>\n";	
		$editsurvey .= "</table>\n";
		}
	}
	
if ($action == "newsurvey")
	{
	$newsurvey = "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
	$newsurvey .= "\t\t<b>$setfont<font color='white'>Create New Survey</font></font></b></td></tr>\n";
	$newsurvey .= "\t<TR><FORM NAME='addnewsurvey' ACTION='$scriptname' METHOD='POST'>\n";
	$newsurvey .= "\t\t<td align='right'><b>$setfont"._SL_TITLE."</font></b></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='20' name='short_title'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'><b>$setfont"._SL_DESCRIPTION."</b></td>\n";
	$newsurvey .= "\t\t<td><textarea cols='35' rows='5' name='description'></textarea></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_WELCOME."</b></font></td>\n";
	$newsurvey .= "\t\t<td><textarea cols='35' rows='5' name='welcome'></textarea></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_ADMIN."</b></font></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='20' name='admin'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_EMAIL."</b></font></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='20' name='adminemail'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_FAXTO."</b></font></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='20' name='faxto'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_ANONYMOUS."</b></font></td>\n";
	$newsurvey .= "\t\t<td><select name='private'>\n";
	$newsurvey .= "\t\t\t<option value='Y' selected>Yes</option>\n";
	$newsurvey .= "\t\t\t<option value='N'>No</option>\n";
	$newsurvey .= "\t\t</select></td>\n\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_DATESTAMP."</b></font></td>\n";
	$newsurvey .= "\t\t<td><select name='datestamp'>\n";
	$newsurvey .= "\t\t\t<option value='Y'>Yes</option>\n";
	$newsurvey .= "\t\t\t<option value='N' selected>No</option>\n";
	$newsurvey .= "\t\t</select></td>\n\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_FORMAT."</b></font></td>\n";
	$newsurvey .= "\t\t<td><select name='format'>\n";
	$newsurvey .= "\t\t\t<option value='S' selected>One at a time</option>\n";
	$newsurvey .= "\t\t\t<option value='G'>Group at a time</option>\n";
	$newsurvey .= "\t\t\t<option value='A'>All in one</option>\n";
	$newsurvey .= "\t\t</select></td>\n";
	$newsurvey .= "\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_TEMPLATE."</b></font></td>\n";
	$newsurvey .= "\t\t<td><select name='template'>\n";
	foreach (gettemplatelist() as $tname)
		{
		$newsurvey .= "\t\t\t<option value='$tname'";
		if ($esrow['template'] && $tname == $esrow['template']) {$newsurvey .= " selected";}
		elseif (!$esrow['template'] && $tname == "default") {$newsurvey .= " selected";}
		$newsurvey .= ">$tname</option>\n";
		}
	$newsurvey .= "\t\t</select></td>\n";
	$newsurvey .= "\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_LANGUAGE."</b></font></td>\n";
	$newsurvey .= "\t\t<td><select name='language'>\n";
	foreach (getlanguages() as $langname)
		{
		$newsurvey .= "\t\t\t<option value='$langname'";
		if ($defaultlang && $defaultlang == $langname) {$newsurvey .= " selected";}
		$newsurvey .= ">$langname</option>\n";
		}
	$newsurvey .= "\t\t</select></td>\n";
	$newsurvey .= "\t</tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_EXPIRES."</b></font></td>\n";
	$newsurvey .= "\t\t<td>$setfont<input type='text' size='10' name='expires'><font size='1'>Date Format: YYYY-MM-DD</font></font></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_URL."</b></font></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='50' name='url' value='{$esrow['url']}'></td></tr>\n";
	$newsurvey .= "\t<tr><td align='right'>$setfont<b>"._SL_URLDESCRIP."</b></font></td>\n";
	$newsurvey .= "\t\t<td><input type='text' size='50' name='urldescrip' value='{$esrow['urldescrip']}'></td></tr>\n";
	$newsurvey .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Create Survey'></td>\n";
	$newsurvey .= "\t<input type='hidden' name='action' value='insertnewsurvey'>\n";
	$newsurvey .= "\t</form></tr>\n";	
	$newsurvey .= "</table>\n";
	$newsurvey .= "<center><b>OR</b></center>\n";
	$newsurvey .= "<table width='100%' border='0'>\n\t<tr><td colspan='2' bgcolor='black' align='center'>\n";
	$newsurvey .= "\t\t<b>$setfont<font color='white'>Import Survey</font></font></b></td></tr>\n\t<tr>";
	$newsurvey .= "\t<form enctype='multipart/form-data' name='importsurvey' action='$scriptname' method='post'>\n";
	$newsurvey .= "\t\t<td align='right'>$setfont<b>"._SL_SELSQL."</b></font></td>\n";
	$newsurvey .= "\t\t<td><input name=\"the_file\" type=\"file\" size=\"35\"></td></tr>\n";
	$newsurvey .= "\t<tr><td colspan='2' align='center'><input type='submit' $btstyle value='Import Survey'></TD>\n";
	$newsurvey .= "\t<input type='hidden' name='action' value='importsurvey'>\n";
	$newsurvey .= "\t</tr></form>\n</table>\n";
	
	}
?>