<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
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

include("config.php");

// THIS FILE CHECKS THE CONSISTENCY OF THE DATABASE, IT LOOKS FOR 
// STRAY QUESTIONS, ANSWERS, CONDITIONS OR GROUPS AND DELETES THEM

if (!$ok) // do the check, but don't delete anything
	{
	echo "<b>Commencing consistency check.</b><br />\n";
	// Check conditions
	$query = "SELECT * FROM conditions ORDER BY qid, cqid, cfieldname, value";
	$result = mysql_query($query) or die("Couldn't get list of conditions from database<br />$query<br />".mysql_error());
	while ($row=mysql_fetch_array($result))
		{
		$qquery="SELECT qid FROM questions WHERE qid='{$row['qid']}'";
		$qresult=mysql_query($qquery) or die ("Couldn't check questions table for qids<br />$qquery<br />".mysql_error());
		$qcount=mysql_num_rows($qresult);
		if (!$qcount) {$cdelete[]=$row['cid'];}
		$qquery = "SELECT qid FROM questions WHERE qid='{$row['cqid']}'";
		$qresult=mysql_query($qquery) or die ("Couldn't check questions table for qids<br />$qquery<br />".mysql_error());
		$qcount=mysql_num_rows($qresult);
		if (!$qcount) {if (!in_array($row['cid'], $cdelete)) {$cdelete[]=$row['cid'];}}
		}
	if ($cdelete)
		{
		echo "<b>We propose to delete the following orphan conditions:</b><br />\n";
		echo implode(", ", $cdelete);
		echo "<br />\n";
		}
	else
		{
		echo "</font><b>All conditions have both matching questions.<br />\n";
		}
	
	// Check answers
	$query = "SELECT * FROM answers ORDER BY qid";
	$result = mysql_query($query) or die ("Couldn't get list of answers from database<br />$query<br />".mysql_error());
	while ($row=mysql_fetch_array($result))
		{
		//echo "Checking answer {$row['code']} to qid {$row['qid']}<br />\n";
		$qquery="SELECT qid FROM questions WHERE qid='{$row['qid']}'";
		$qresult=mysql_query($qquery) or die ("Couldn't check questions table for qids from answers<br />$qquery<br />".mysql_error());
		$qcount=mysql_num_rows($qresult);
		if (!$qcount) {$adelete[]=array($row['qid'], $row['code']);}
		//echo "<br />\n";
		}
	if ($adelete)
		{
		echo "</font><b>We propose to delete the following answers:</b><br />\n";
		foreach ($adelete as $ad) {echo "QID $ad[0] CODE $ad[1]<br />\n";}
		}
	else
		{
		echo "</font><b>All answers have a matching question.<br />\n";
		}
	
	//check questions
	$query = "SELECT * FROM questions ORDER BY sid, gid, qid";
	$result = mysql_query($query) or die ("Couldn't get list of questions from database<br />$query<br />".mysql_error());
	while ($row=mysql_fetch_array($result))
		{
		//Make sure group exists
		$qquery="SELECT * FROM groups WHERE gid={$row['gid']}";
		$qresult=mysql_query($qquery) or die ("Couldn't check groups table for gids from questions<br />$qquery<br />".mysql_error());
		$qcount=mysql_num_rows($qresult);
		if (!$qcount) {$qdelete[]=array($row['qid']);}
		//Make sure survey exists
		$qquery="SELECT * FROM surveys WHERE sid={$row['sid']}";
		$qresult=mysql_query($qquery) or die ("Couldn't check surveys table for sids from questions<br />$qquery<br />".mysql_error());
		$qcount=mysql_num_rows($qresult);
		if (!$qcount) {if (!in_array($row['qid'], $qdelete)) {$qdelete[]=array($row['qid']);}}
		}
	if ($qdelete)
		{
		echo "<b>We propose to delete the following questions:</b><br />\n";
		echo implode(", ", $qdelete);
		echo "<br />\n";
		}
	else
		{
		echo "</font><b>All questions have a matching group and survey.<br />\n";
		}
	//check groups
	$query = "SELECT * FROM groups ORDER BY sid, gid";
	$result=mysql_query($query) or die ("Couldn't get list of groups for checking<br />$query<br />".mysql_error());
	while ($row=mysql_fetch_array($result))
		{
		//make sure survey exists
		$qquery = "SELECT * FROM groups WHERE sid={$row['sid']}";
		$qresult=mysql_query($qquery) or die("Couldn't check surveys table for gids from groups<br />$qquery<br />".mysql_error());
		$qcount=mysql_num_rows($qresult);
		if (!$qcount) {$gdelete[]=array($row['gid']);}
		}
	if ($gdelete)
		{
		echo "<b>We propose to delete the following groups:</b><br />\n";
		echo implode(", ", $gdelete);
		echo "<br />\n";
		}
	else
		{
		echo "</font><b>All groups have a matching survey.<br />\n";
		}
//NOW CHECK FOR STRAY SURVEY RESPONSE TABLES AND TOKENS TABLES
		
	}
else if ($ok)
	{
	
	}
?>