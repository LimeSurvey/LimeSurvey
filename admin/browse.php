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

if (!isset($limit)) {$limit=returnglobal('limit');}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($id)) {$id=returnglobal('id');}
if (!isset($action)) {$action=returnglobal('action');}
if (!isset($order)) {$order=returnglobal('order');}

//Ensure script is not run directly, avoid path disclosure
if (empty($surveyid)) {die("No SID provided.");}

sendcacheheaders();

//Select public language file
$query = "SELECT language FROM {$dbprefix}surveys WHERE sid=$surveyid";
$result = mysql_query($query) or die("Error selecting language: <br />".$query."<br />".mysql_error());
while ($row=mysql_fetch_array($result)) {$surveylanguage = $row['language'];}
$langdir="$publicdir/lang";
$langfilename="$langdir/$surveylanguage.lang.php";
if (!is_file($langfilename)) {$langfilename="$langdir/$defaultlang.lang.php";}
require($langfilename);

$surveyoptions = browsemenubar();
echo $htmlheader;
echo "<table><tr><td></td></tr></table>\n"
	."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";

if (!mysql_selectdb($databasename, $connect)) //DATABASE DOESN'T EXIST OR CAN'T CONNECT
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		. _BROWSERESPONSES."</strong></font></td></tr>\n"
		."\t<tr bgcolor='#CCCCCC'><td align='center'>$setfont\n"
		."<strong><font color='red'>"._ERROR."</font></strong><br />\n"
		. _ST_NODB1."<br />\n"
		. _ST_NODB2."<br /><br />\n"
		."<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\"><br />\n"
		."</td></tr></table>\n"
		."</body>\n</html>";
	exit;
	}
if (!$surveyid && !$action) //NO SID OR ACTION PROVIDED
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		. _BROWSERESPONSES."</strong></font></td></tr>\n"
		."\t<tr bgcolor='#CCCCCC'><td align='center'>$setfont\n"
		."<strong><font color='red'>"._ERROR."</font></strong><br />\n"
		. _BR_NOSID."<br /><br />\n"
		."<input $btstyle type='submit' value='"
		. _GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\"><br />\n"
		."</td></tr></table>\n"
		."</body>\n</html>";
	exit;
	}

//CHECK IF SURVEY IS ACTIVATED AND EXISTS
$actquery = "SELECT * FROM {$dbprefix}surveys WHERE sid=$surveyid";
$actresult = mysql_query($actquery);
$actcount = mysql_num_rows($actresult);
if ($actcount > 0)
	{
	while ($actrow = mysql_fetch_array($actresult))
		{
		$surveytable = "{$dbprefix}survey_{$actrow['sid']}";
		$surveyname = "{$actrow['short_title']}";
		if ($actrow['active'] == "N") //SURVEY IS NOT ACTIVE YET
			{
			echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
				. _BROWSERESPONSES.": <font color='silver'>$surveyname</font></strong></td></font></tr>\n"
				."\t<tr bgcolor='#CCCCCC'><td align='center'>$setfont\n"
				."<strong><font color='red'>"._ERROR."</font></strong><br />\n"
				. _BR_NOTACTIVATED."<br /><br />\n"
				."<input $btstyle type='submit' value='"
				. _GO_ADMIN."' onClick=\"window.open('$scriptname?sid=$surveyid', '_top')\"><br />\n"
				."</td></tr></table>\n"
				."</body>\n</html>";
			exit;
			}
		}
	}
else //SURVEY MATCHING $surveyid DOESN'T EXIST
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		. _BROWSERESPONSES."</strong></font></td></tr>\n"
		."\t<tr bgcolor='#CCCCCC'><td align='center'>$setfont\n"
		."<strong><font color='red'>"._ERROR."</font></strong><br />\n"
		. _BR_NOSURVEY." ($surveyid)<br /><br />\n"
		."<input $btstyle type='submit' value='"._GO_ADMIN."' onClick=\"window.open('$scriptname', '_top')\"><br />\n"
		."</td></tr></table>\n"
		."</body>\n</html>";
	exit;
	}

//OK. IF WE GOT THIS FAR, THEN THE SURVEY EXISTS AND IT IS ACTIVE, SO LETS GET TO WORK.

if ($action == "id") // Looking at a SINGLE entry
	{
	//SHOW HEADER
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"._BROWSERESPONSES.": <font color='silver'>$surveyname</font></strong></font></td></tr>\n";
	if (!isset($_POST['sql']) || !$_POST['sql']) {echo "$surveyoptions";} // Don't show options if coming from tokens script
	echo "</table>\n"
		."<table><tr><td></td></tr></table>\n";
	
	//FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
	$fnquery = "SELECT * FROM {$dbprefix}questions, {$dbprefix}groups, {$dbprefix}surveys WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}groups.sid={$dbprefix}surveys.sid AND {$dbprefix}questions.sid='$surveyid' ORDER BY group_name";
	$fnresult = mysql_query($fnquery);
	$fncount = mysql_num_rows($fnresult);
	
	$fnrows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
	while ($fnrow = mysql_fetch_array($fnresult)) {$fnrows[] = $fnrow; $private = $fnrow['private']; $datestamp=$fnrow['datestamp']; $ipaddr=$fnrow['ipaddr'];} // Get table output into array
	
	// Perform a case insensitive natural sort on group name then question title of a multidimensional array
	usort($fnrows, 'CompareGroupThenTitle');
	
	$fnames[] = array("id", "id", "id");
	
	if ($private == "N") //add token to top ofl ist is survey is not private
		{
		$fnames[] = array("token", "token", "Token ID");		
		}
	if ($datestamp == "Y") //add datetime to list if survey is datestamped
		{
		$fnames[] = array("datestamp", "datestamp", "Date Stamp");
		}
        if ($ipaddr == "Y") //add ipaddr to list if survey should save submitters IP address
                {
                 $fnames[] = array("ipaddr", "ipaddr", "IP Address");
                }	
	foreach ($fnrows as $fnrow)
		{
		$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}";
		$ftitle = "Grp{$fnrow['gid']}Qst{$fnrow['title']}";
		$fquestion = $fnrow['question'];
		if ($fnrow['type'] == "Q" || $fnrow['type'] == "M" || 
			$fnrow['type'] == "A" || $fnrow['type'] == "B" || 
			$fnrow['type'] == "C" || $fnrow['type'] == "E" || 
			$fnrow['type'] == "F" || $fnrow['type'] == "H" || 
			$fnrow['type'] == "P" || $fnrow['type'] == "^")
			{
			$fnrquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$fnrow['qid']} ORDER BY sortorder, answer";
			$fnrresult = mysql_query($fnrquery);
			while ($fnrrow = mysql_fetch_array($fnrresult))
				{
				$fnames[] = array("$field{$fnrrow['code']}", "$ftitle ({$fnrrow['code']})", "{$fnrow['question']} ({$fnrrow['answer']})");
				if ($fnrow['type'] == "P") {$fnames[] = array("$field{$fnrrow['code']}"."comment", "$ftitle"."comment", "{$fnrow['question']} (comment)");}
				}
			if ($fnrow['other'] == "Y" and ($fnrow['type']=="!" or $fnrow['type']=="L" or $fnrow['type']=="M" or $fnrow['type']=="P"))
				{
				$fnames[] = array("$field"."other", "$ftitle"."other", "{$fnrow['question']}(other)");
				}
			}
		elseif ($fnrow['type'] == "R")
			{
			$fnrquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$fnrow['qid']} ORDER BY sortorder, answer";
			$fnrresult = mysql_query($fnrquery);
			$fnrcount = mysql_num_rows($fnrresult);
			for ($i=1; $i<=$fnrcount; $i++)
				{
				$fnames[] = array("$field$i", "$ftitle ($i)", "{$fnrow['question']} ($i)");
				}			
			}
		elseif ($fnrow['type'] == "O")
			{
			$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}");
			$field2 = $field."comment";
			$ftitle2 = $ftitle."[Comment]";
			$longtitle = "{$fnrow['question']}<br />[Comment]";
			$fnames[] = array("$field2", "$ftitle2", "$longtitle");
			}
		else
			{
			$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}");
			if (($fnrow['type'] == "L" || $fnrow['type'] == "!") && $fnrow['other'] == "Y") 
				{
				$fnames[] = array("$field"."other", "$ftitle"."other", "{$fnrow['question']}(other)");
				}
			}
		}

	$nfncount = count($fnames)-1;
	//SHOW INDIVIDUAL RECORD
	$idquery = "SELECT * FROM $surveytable WHERE ";
	if (isset($_POST['sql']) && $_POST['sql'])
		{
		if (get_magic_quotes_gpc()) {$idquery .= stripslashes($_POST['sql']);}
		else {$idquery .= "{$_POST['sql']}";}
		}
	else {$idquery .= "id=$id";}
	$idresult = mysql_query($idquery) or die ("Couldn't get entry<br />\n$idquery<br />\n".mysql_error());
	while ($idrow = mysql_fetch_array($idresult)) {$id=$idrow['id'];}
	$next=$id+1;
	$last=$id-1;
	echo "<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'>\n"
		."\t\t<td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		. _VIEWRESPONSE.":</strong> $id</font></td></tr>\n"
		."\t<tr bgcolor='#999999'><td colspan='2'>\n"
		."\t\t\t<img src='$imagefiles/blank.gif' width='31' height='20' border='0' hspace='0' align='left' alt=''>\n"
		."\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt=''>\n"
		."\t\t\t<input type='image' align='left' src='$imagefiles/edit.gif' title='"
		. _BR_EDITRESPONSE."' alt='"._BR_EDITRESPONSE."' onClick=\"window.open('dataentry.php?action=edit&amp;id=$id&amp;sid=$surveyid&amp;surveytable=$surveytable','_top')\" />\n"
		."\t\t\t<a href='dataentry.php?action=delete&amp;id=$id&amp;sid=$surveyid&amp;surveytable=$surveytable'>"
		."<img align='left' hspace='0' border='0' src='$imagefiles/delete.gif' alt='"
		. _BR_DELRESPONSE."' title='"
		. _BR_DELRESPONSE."' onClick=\"return confirm('"._DR_RUSURE."')\" /></a>\n"
		."\t\t\t<img src='$imagefiles/blank.gif' width='20' height='20' border='0' hspace='0' align='left' alt=''>\n"
		."\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt=''>\n"
		."\t\t\t<img src='$imagefiles/blank.gif' width='20' height='20' border='0' hspace='0' align='left' alt=''>\n"
		."\t\t\t<input type='image' name='DataBack' align='left' src='$imagefiles/databack.gif' title='"
		. _D_BACK."' onClick=\"window.open('browse.php?action=id&amp;id=$last&amp;sid=$surveyid&amp;surveytable=$surveytable','_top')\" />\n"
		."\t\t\t<img src='$imagefiles/blank.gif' width='13' height='20' border='0' hspace='0' align='left' alt=''>\n"
		."\t\t\t<input type='image' name='DataForward' align='left' src='$imagefiles/dataforward.gif' title='"
		. _D_FORWARD."' onClick=\"window.open('browse.php?action=id&amp;id=$next&amp;sid=$surveyid&amp;surveytable=$surveytable','_top')\" />\n"
		."\t\t</td>\n"
		."\t</tr>\n"
		."\t<tr><td colspan='2' bgcolor='#CCCCCC' height='1'></td></tr>\n";
	$idresult = mysql_query($idquery) or die ("Couldn't get entry<br />$idquery<br />".mysql_error());
	while ($idrow = mysql_fetch_array($idresult))
		{
		$i=0;
		for ($i; $i<$nfncount+1; $i++)
			{
			echo "\t<tr>\n"
				."\t\t<td bgcolor='#EFEFEF' valign='top' align='right' width='33%' style='padding-right: 5px'>"
				."$setfont{$fnames[$i][2]}</font></td>\n"
				."\t\t<td valign='top' style='padding-left: 5px'>$setfont"
				.htmlspecialchars(getextendedanswer($fnames[$i][0], $idrow[$fnames[$i][0]]), ENT_QUOTES) 
				."</font></td>\n"
				."\t</tr>\n"
				."\t<tr><td colspan='2' bgcolor='#CCCCCC' height='1'></td></tr>\n";
			}
		}
	echo "</table>\n"
		."<table width='99%' align='center'>\n"
		."\t<tr>\n"
		."\t\t<td $singleborderstyle bgcolor='#EEEEEE' align='center'>\n";
	if (isset($_POST['sql']) && $_POST['sql']) {echo "\t\t\t<input type='submit' $btstyle value='Close Window' onClick=\"window.close();\" />\n";}
	echo "\t\t</td>\n"
		."\t</tr>\n"
		."</table>\n";
	}

elseif ($action == "all")
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		. _BROWSERESPONSES.":</strong> <font color='#EEEEEE'>$surveyname</font></font></td></tr>\n";
	
	if (!isset($_POST['sql']))
		{echo "$surveyoptions";} //don't show options when called from another script with a filter on
	else
		{
		echo "\n<table width='100%' align='center' border='0' bgcolor='#EFEFEF'>\n"
			."\t<tr>\n"
			."\t\t<td align='center' $singleborderstyle>$setfont\n"
			."\t\t\tShowing Filtered Results<br />\n"
			."\t\t\t&nbsp;[<a href=\"javascript:window.close()\">Close</a>]"
			."\t\t</font></td>\n"
			."\t</tr>\n"
			."</table>\n";
		
		}
	echo "</table>\n";
	//FIRST LETS GET THE NAMES OF THE QUESTIONS AND MATCH THEM TO THE FIELD NAMES FOR THE DATABASE
	$fnquery = "SELECT * FROM {$dbprefix}questions, {$dbprefix}groups, {$dbprefix}surveys WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}groups.sid={$dbprefix}surveys.sid AND {$dbprefix}questions.sid='$surveyid' ORDER BY group_name";
	$fnresult = mysql_query($fnquery);
	$fncount = mysql_num_rows($fnresult);
	
	$fnrows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
	while ($fnrow = mysql_fetch_assoc($fnresult)) {$fnrows[] = $fnrow; $private = $fnrow['private']; $datestamp=$fnrow['datestamp']; $ipaddr=$fnrow['ipaddr'];} // Get table output into array
	
	// Perform a case insensitive natural sort on group name then question title of a multidimensional array
	usort($fnrows, 'CompareGroupThenTitle');
	
	if ($private == "N") //Add token to list
		{
		$fnames[] = array("token", "Token", "Token ID", "0");
		}
	if ($datestamp == "Y") //Add datestamp
		{
		$fnames[] = array("datestamp", "Datestamp", "Date Stamp", "0");
		}
        if ($ipaddr == "Y") // Add IP Address
		{
                $fnames[] = array("ipaddr", "IPAddress", "IP Address", "0");
		}
	foreach ($fnrows as $fnrow)
		{
		if ($fnrow['type'] != "Q" && $fnrow['type'] != "M" && $fnrow['type'] != "A" && 
			$fnrow['type'] != "B" && $fnrow['type'] != "C" && $fnrow['type'] != "E" && 
			$fnrow['type'] != "F" && $fnrow['type'] != "H" && $fnrow['type'] != "P" && 
			$fnrow['type'] != "O" && $fnrow['type'] != "R" && $fnrow['type'] != "^")
			{
			$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}";
			$ftitle = "Grp{$fnrow['gid']}Qst{$fnrow['title']}";
			$fquestion = $fnrow['question'];
			$fnames[] = array("$field", "$ftitle", "$fquestion", "{$fnrow['gid']}");
			if (($fnrow['type'] == "L" || $fnrow['type'] == "!") && $fnrow['other'] == "Y") 
				{
				$fnames[] = array("$field"."other", "$ftitle"."other", "{$fnrow['question']}(other)", "{$fnrow['gid']}");
				}

			}
		elseif ($fnrow['type'] == "O")
			{
			$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}";
			$ftitle = "Grp{$fnrow['gid']}Qst{$fnrow['title']}";
			$fquestion = $fnrow['question'];
			$fnames[] = array("$field", "$ftitle", "$fquestion", "{$fnrow['gid']}");
			$field .= "comment";
			$ftitle .= "[comment]";
			$fquestion .= " (comment)";
			$fnames[] = array("$field", "$ftitle", "$fquestion", "{$fnrow['gid']}");
			}
		elseif ($fnrow['type'] == "R")
			{
			$i2query = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}questions.qid={$fnrow['qid']} AND {$dbprefix}questions.sid=$surveyid ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
			$i2result = mysql_query($i2query);
			$i2count = mysql_num_rows($i2result);
			for ($i=1; $i<=$i2count; $i++)
				{
				$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}$i";
				$ftitle = "Grp{$fnrow['qid']}Qst{$fnrow['title']}Opt$i";
				$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}<br />\n[$i]", "{$fnrow['gid']}");
				}
			}
		else
			{
			$i2query = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND {$dbprefix}questions.qid={$fnrow['qid']} AND {$dbprefix}questions.sid=$surveyid ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
			$i2result = mysql_query($i2query);
			$otherexists = "";
			while ($i2row = mysql_fetch_array($i2result))
				{
				$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}{$i2row['code']}";
				$ftitle = "Grp{$fnrow['gid']}Qst{$fnrow['title']}Opt{$i2row['code']}";
				if ($i2row['other'] == "Y") {$otherexists = "Y";}
				$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}<br />\n[{$i2row['answer']}]", "{$fnrow['gid']}");
				if ($fnrow['type'] == "P") {$fnames[] = array("$field"."comment", "$ftitle", "{$fnrow['question']}<br />\n[{$i2row['answer']}]<br />\n[Comment]", "{$fnrow['gid']}");}
				}
			if ($otherexists == "Y") 
				{
				$field = "{$fnrow['sid']}X{$fnrow['gid']}X{$fnrow['qid']}"."other";
				$ftitle = "Grp{$fnrow['gid']}Qst{$fnrow['title']}OptOther";
				$fnames[] = array("$field", "$ftitle", "{$fnrow['question']}<br />\n[Other]", "{$fnrow['gid']}");
				if ($fnrow['type'] == "P")
					{
					$fnames[] = array("$field"."comment", "$ftitle"."Comment", "{$fnrow['question']}<br />\n[Other]<br />\n[Comment]", "{$fnrow['gid']}");
					}
				}
			}
		}
	$fncount = count($fnames);
	
	//NOW LETS CREATE A TABLE WITH THOSE HEADINGS
	if ($fncount < 10) {$cellwidth = "10%";} else {$cellwidth = "100";}
	$tableheader = "\n\n<!-- DATA TABLE -->\n";
	if ($fncount < 10) {$tableheader .= "<table width='100%' border='0' cellpadding='0' cellspacing='1' style='border: 1px solid #555555'>\n";}
	else {$fnwidth = (($fncount-1)*100); $tableheader .= "<table width='$fnwidth' border='0' cellpadding='1' cellspacing='1' style='border: 1px solid #555555'>\n";}
	$tableheader .= "\t<tr bgcolor='#555555' valign='top'>\n"
				  . "\t\t<td bgcolor='#333333' width='$cellwidth'><font size='1' color='white' face='verdana'><strong>id</strong></font></td>\n";
	foreach ($fnames as $fn)
		{
		if (!isset($currentgroup))  {$currentgroup = $fn[3]; $gbc = "#555555";}
		if ($currentgroup != $fn[3])
			{
			$currentgroup = $fn[3];
			if ($gbc == "#555555") {$gbc = "#666666";}
			else {$gbc = "#555555";}
			}
		$tableheader .= "\t\t<td bgcolor='$gbc' width='$cellwidth'><font size='1' color='white' face='verdana'><strong>"
					  . "$fn[2]"
					  . "</strong></font></td>\n"; 
		}
	$tableheader .= "\t</tr>\n\n";
	
	$start=returnglobal('start');
	$limit=returnglobal('limit');
	if (!isset($limit)) {$limit = 50;}
	if (!isset($start)) {$start = 0;}
		
	//LETS COUNT THE DATA
	$dtquery = "SELECT count(*) FROM $surveytable";
	$dtresult=mysql_query($dtquery);
	while ($dtrow=mysql_fetch_row($dtresult)) {$dtcount=$dtrow[0];}
	
	if ($limit > $dtcount) {$limit=$dtcount;}
	
	//NOW LETS SHOW THE DATA
	if (isset($_POST['sql']))
		{
		if ($_POST['sql'] == "NULL") {$dtquery = "SELECT * FROM $surveytable ORDER BY id";}
		else {$dtquery = "SELECT * FROM $surveytable WHERE ".stripcslashes($_POST['sql'])." ORDER BY id";}
		}
	else
		{
		$dtquery = "SELECT * FROM $surveytable ORDER BY id";
		}
	if ($order == "desc") {$dtquery .= " DESC LIMIT $limit";}
	if (isset($start) && isset($limit) && !isset($order)) {$dtquery .= " LIMIT $start, $limit";}
	if (!isset($limit)) {$dtquery .= " LIMIT $limit";}
	if (!isset($start)) {$start = 0;}
	$dtresult = mysql_query($dtquery) or die("Couldn't get surveys<br />$dtquery<br />".mysql_error());
	$dtcount2 = mysql_num_rows($dtresult);
	$cells = $fncount+1;

	
	//CONTROL MENUBAR
	$last=$start-$limit;
	$next=$start+$limit;
	$end=$dtcount-$limit;
	if ($end < 0) {$end=0;}
	if ($last <0) {$last=0;}
	if ($next >= $dtcount) {$next=$dtcount-$limit;}
	if ($end < 0) {$end=0;}

	echo "<table><tr><td></td></tr></table>\n"
		."<table width='99%' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n"
		."\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		. _VIEWCONTROL.":</strong></font></td></tr>\n"
		."\t<tr bgcolor='#999999'><td align='left'>\n";
	if (!isset($_POST['sql']))
		{
		echo "\t\t\t<img src='$imagefiles/blank.gif' width='31' height='20' border='0' hspace='0' align='left' alt=''>\n"
			."\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt=''>\n"
			."\t\t\t<input type='image' name='DataBegin' align='left' src='$imagefiles/databegin.gif' title='"
			. _D_BEGIN."' onClick=\"window.open('browse.php?action=all&amp;sid=$surveyid&amp;start=0&amp;limit=$limit','_top')\" />\n"
			."\t\t\t<input type='image' name='DataBack' align='left'  src='$imagefiles/databack.gif' title='"
			. _D_BACK."' onClick=\"window.open('browse.php?action=all&amp;sid=$surveyid&amp;surveytable=$surveytable&amp;start=$last&amp;limit=$limit','_top')\" />\n"
			."\t\t\t<img src='$imagefiles/blank.gif' width='13' height='20' border='0' hspace='0' align='left' alt=''>\n"
			."\t\t\t<input type='image' name='DataForward' align='left' src='$imagefiles/dataforward.gif' title='"
			. _D_FORWARD."' onClick=\"window.open('browse.php?action=all&amp;sid=$surveyid&amp;surveytable=$surveytable&amp;start=$next&amp;limit=$limit','_top')\" />\n"
			."\t\t\t<input type='image' name='DataEnd' align='left' src='$imagefiles/dataend.gif' title='"
			. _D_END."' onClick=\"window.open('browse.php?action=all&amp;sid=$surveyid&amp;start=$end&amp;limit=$limit','_top')\" />\n"
			."\t\t\t<img src='$imagefiles/seperator.gif' border='0' hspace='0' align='left' alt=''>\n";
		}
	echo "\t\t</td>\n"
		."\t\t<td align='right'>\n"
		."\t\t<form action='browse.php' method='post'><font size='1' face='verdana'>\n"
		."\t\t\t<img src='$imagefiles/blank.gif' width='31' height='20' border='0' hspace='0' align='right' alt=''>\n"
		."\t\t\t"._BR_DISPLAYING."<input type='text' $slstyle size='4' value='$dtcount2' name='limit'>\n"
		."\t\t\t"._BR_STARTING."<input type='text' $slstyle size='4' value='$start' name='start'>\n"
		."\t\t\t<input type='submit' value='"._BR_SHOW."' $btstyle>\n"
		."\t\t</font>\n"
		."\t\t<input type='hidden' name='sid' value='$surveyid'>\n"
		."\t\t<input type='hidden' name='action' value='all'>\n";
if (isset($_POST['sql'])) 
	{
	echo "\t\t<input type='hidden' name='sql' value='".html_escape($_POST['sql'])."'>\n";
	}
echo 	 "\t\t</form></td>\n"
		."\t</tr>\n"
		."</table>\n"
		."<table><tr><td></td></tr></table>\n";

	echo $tableheader;
	
	while ($dtrow = mysql_fetch_assoc($dtresult))
		{
		if (!isset($bgcc)) {$bgcc="#EEEEEE";}
		else
			{
			if ($bgcc == "#EEEEEE") {$bgcc = "#CCCCCC";}
			else {$bgcc = "#EEEEEE";}
			}
		echo "\t<tr bgcolor='$bgcc' valign='top'>\n"
			."\t\t<td align='center'><font face='verdana' size='1'>\n"
			."\t\t\t<a href='browse.php?sid=$surveyid&amp;action=id&amp;id={$dtrow['id']}' title='View this record'>"
			."{$dtrow['id']}</a></font></td>\n";
		
		$i = 0;
		if ($private == "N")
			{
			$SQL = "Select * FROM {$dbprefix}tokens_$surveyid WHERE token='{$dtrow['token']}'";
			if ($SQLResult = mysql_query($SQL))
				{
				$TokenRow = mysql_fetch_assoc($SQLResult);
				}
			echo "\t\t<td align='center'><font size='1'>\n";
			if (isset($TokenRow) && $TokenRow) 
				{
				echo "\t\t<a href='tokens.php?sid=$surveyid&amp;action=edit&amp;tid={$TokenRow['tid']}' title='Edit this token'>";
				}
			echo "{$dtrow['token']}";
			if (isset($TokenRow) && $TokenRow) 
				{
				echo "</a>\n";
				}
			$i++;
			}
		
		for ($i; $i<$fncount; $i++)
			{
			echo "\t\t<td align='center'><font size='1' face='verdana'>"
				. htmlspecialchars($dtrow[$fnames[$i][0]])
				."</font></td>\n";
			}
		echo "\t</tr>\n";
		}
	echo "</table>\n<br />\n";
	}
else
	{
	echo "\t<tr bgcolor='#555555'><td colspan='2' height='4'><font size='1' face='verdana' color='white'><strong>"
		. _BROWSERESPONSES.":</strong> <font color='#EEEEEE'>$surveyname</font></font></td></tr>\n"
		. $surveyoptions;
	echo "</table>\n";
	$gnquery = "SELECT count(id) FROM $surveytable";
	$gnresult = mysql_query($gnquery);
	while ($gnrow = mysql_fetch_row($gnresult))
		{
		echo "<table width='100%' border='0'>\n"
			."\t<tr><td align='center'>$setfont$gnrow[0] responses in this database</font></td></tr>\n"
			."</table>\n";
		}
	}
echo htmlfooter("$langdir/instructions.html#browse", "Using PHPSurveyors Browse Function");

?>