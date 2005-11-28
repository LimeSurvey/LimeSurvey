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

require_once("config.php");

// THIS FILE CHECKS THE CONSISTENCY OF THE DATABASE, IT LOOKS FOR 
// STRAY QUESTIONS, ANSWERS, CONDITIONS OR GROUPS AND DELETES THEM
$ok=returnglobal('ok');

if (!isset($ok) || $ok != "Y") // do the check, but don't delete anything
	{
    sendcacheheaders();		
	echo $htmlheader;
	echo "<table><tr><td height='1'></td></tr></table>\n"
		. "<table align='center' bgcolor='#DDDDDD' style='border: 1px solid #555555' "
		. "cellpadding='1' cellspacing='0' width='450'>\n"
		. "\t<tr>\n"
		. "\t\t<td colspan='2' align='center' bgcolor='#BBBBBB'>$setfont\n"
		. "\t\t\t<strong>"._DC_TITLE."</strong>\n"
		. "\t\t</font></td>\n"
		. "\t</tr>\n"
		. "\t<tr><td align='center'>$setfont";
	echo "<br />\n";
	// Check conditions
//	$query = "SELECT {$dbprefix}questions.sid, {$dbprefix}conditions.* "
//			."FROM {$dbprefix}conditions, {$dbprefix}questions "
//			."WHERE {$dbprefix}conditions.qid={$dbprefix}questions.qid "
//			."ORDER BY qid, cqid, cfieldname, value";
	$query = "SELECT * FROM {$dbprefix}conditions ORDER BY cid";
	$result = mysql_query($query) or die("Couldn't get list of conditions from database<br />$query<br />".mysql_error());
	while ($row=mysql_fetch_array($result))
		{
		$qquery="SELECT qid FROM {$dbprefix}questions WHERE qid='{$row['qid']}'";
		$qresult=mysql_query($qquery) or die ("Couldn't check questions table for qids<br />$qquery<br />".mysql_error());
		$qcount=mysql_num_rows($qresult);
		if (!$qcount) {$cdelete[]=array("cid"=>$row['cid'], "reason"=>"No matching qid");}
		$qquery = "SELECT qid FROM {$dbprefix}questions WHERE qid='{$row['cqid']}'";
		$qresult=mysql_query($qquery) or die ("Couldn't check questions table for qids<br />$qquery<br />".mysql_error());
		$qcount=mysql_num_rows($qresult);
		if (!$qcount) {$cdelete[]=array("cid"=>$row['cid'], "reason"=>"No matching Cqid");}
		if ($row['cfieldname']) //Only do this if there actually is a "cfieldname"
			{
			list ($surveyid, $gid, $rest) = explode("X", $row['cfieldname']);
			$qquery = "SELECT gid FROM {$dbprefix}groups WHERE gid=$gid";
			$qresult = mysql_query($qquery) or die ("Couldn't check conditional group matches<br />$qquery<br />".mysql_error());
			$qcount=mysql_num_rows($qresult);
			if ($qcount < 1) {$cdelete[]=array("cid"=>$row['cid'], "reason"=>"No matching CFIELDNAME Group! ($gid) ({$row['cfieldname']})");}
			}
		elseif (!$row['cfieldname'])
			{
			$cdelete[]=array("cid"=>$row['cid'], "reason"=>"No \"CFIELDNAME\" field set! ({$row['cfieldname']})");
			}
		}
	if (isset($cdelete) && $cdelete)
		{
		echo "<strong>"._DC_CONDITIONSTODELETE.":</strong><br /><font size='1'>\n";
		foreach ($cdelete as $cd) {
			echo "CID: {$cd['cid']} because {$cd['reason']}<br />\n";
		}
		echo "</font><br />\n";
		}
	else
		{
		echo "<strong>"._DC_CONDITIONSSOK."</strong><br />\n";
		}
	
	// Check question_attributes to delete
	$query = "SELECT * FROM {$dbprefix}question_attributes ORDER BY qid";
	$result = mysql_query($query) or die(mysql_error());
	while($row = mysql_fetch_array($result))
		{
		$aquery = "SELECT * FROM {$dbprefix}questions WHERE qid = {$row['qid']}";
		$aresult = mysql_query($aquery) or die(mysql_error());
		$qacount = mysql_num_rows($aresult);
		if (!$qacount) {
		    $qadelete[]=array("qaid"=>$row['qaid'], "attribute"=>$row['attribute'], "reason"=>"No matching qid");
		}
		} // while
	if (isset($qadelete) && $qadelete) {
	    echo "<strong>"._DC_QATODELETE.":</strong><br /><font size='1'>\n";
		foreach ($qadelete as $qad) {echo "QAID `{$qad['qaid']}` ATTRIBUTE `{$qad['attribute']}` because `{$qad['reason']}`<br />\n";}
		echo "</font><br />\n";
	}
	else
		{
		echo "<strong>"._DC_QAOK."</strong><br />\n";
		}

	// Check assessments
	$query = "SELECT * FROM {$dbprefix}assessments WHERE scope='T' ORDER BY sid";
	$result = mysql_query($query) or die ("Couldn't get list of assessments<br />$query<br />".mysql_error());
	while($row = mysql_fetch_array($result))
		{
		$aquery = "SELECT * FROM {$dbprefix}surveys WHERE sid = {$row['sid']}";
		$aresult = mysql_query($aquery) or die("Oh dear - died in assessments surveys:".$aquery ."<br />".mysql_error());
		$acount = mysql_num_rows($aresult);
		if (!$acount) {
		    $assdelete[]=array("id"=>$row['id'], "assessment"=>$row['name'], "reason"=>"No matching survey");
		}
		} // while

	$query = "SELECT * FROM {$dbprefix}assessments WHERE scope='G' ORDER BY gid";
	$result = mysql_query($query) or die ("Couldn't get list of assessments<br />$query<br />".mysql_error());
	while($row = mysql_fetch_array($result))
		{
		$aquery = "SELECT * FROM {$dbprefix}groups WHERE gid = {$row['gid']}";
		$aresult = mysql_query($aquery) or die("Oh dear - died:".$aquery ."<br />".mysql_error());
		$acount = mysql_num_rows($aresult);
		if (!$acount) {
		    $asgdelete[]=array("id"=>$row['id'], "assessment"=>$row['name'], "reason"=>"No matching group");
		}
		}

	if (isset($assdelete) && $assdelete) 
		{
	    echo "<strong>"._DC_ASSESSTODELETE.":</strong><br /><font size='1'>\n";
		foreach ($assdelete as $ass) {echo "ID `{$ass['id']}` ASSESSMENT `{$ass['assessment']}` because `{$ass['reason']}`<br />\n";}
		echo "</font><br />\n";
		}
	else
		{
		echo "<strong>"._DC_ASSESSOK."</strong><br />\n";
		}
	if (isset($asgdelete) && $asgdelete) 
		{
	    echo "<strong>"._DC_ASSESSTODELETE.":</strong><br /><font size='1'>\n";
		foreach ($asgdelete as $asg) {echo "ID `{$asg['id']}` ASSESSMENT `{$asg['assessment']}` because `{$asg['reason']}`<br />\n";}
		echo "</font><br />\n";
		}
	else
		{
		echo "<strong>"._DC_ASSESSOK."</strong><br />\n";
		}
		
	// Check answers
	$query = "SELECT * FROM {$dbprefix}answers ORDER BY qid";
	$result = mysql_query($query) or die ("Couldn't get list of answers from database<br />$query<br />".mysql_error());
	while ($row=mysql_fetch_array($result))
		{
		//echo "Checking answer {$row['code']} to qid {$row['qid']}<br />\n";
		$qquery="SELECT qid FROM {$dbprefix}questions WHERE qid='{$row['qid']}'";
		$qresult=mysql_query($qquery) or die ("Couldn't check questions table for qids from answers<br />$qquery<br />".mysql_error());
		$qcount=mysql_num_rows($qresult);
		if (!$qcount) {
			$adelete[]=array("qid"=>$row['qid'], "code"=>$row['code'], "reason"=>"No matching question");
		}
		//echo "<br />\n";
		}
	if (isset($adelete) && $adelete)
		{
		echo "<strong>"._DC_ANSWERSTODELETE.":</strong><br /><font size='1'>\n";
		foreach ($adelete as $ad) {echo "QID `{$ad['qid']}` CODE `{$ad['code']}` because `{$ad['reason']}`<br />\n";}
		echo "</font><br />\n";
		}
	else
		{
		echo "<strong>"._DC_ANSWERSOK."</strong><br />\n";
		}
	
	//check questions
	$query = "SELECT * FROM {$dbprefix}questions ORDER BY sid, gid, qid";
	$result = mysql_query($query) or die ("Couldn't get list of questions from database<br />$query<br />".mysql_error());
	while ($row=mysql_fetch_array($result))
		{
		//Make sure group exists
		$qquery="SELECT * FROM {$dbprefix}groups WHERE gid={$row['gid']}";
		$qresult=mysql_query($qquery) or die ("Couldn't check groups table for gids from questions<br />$qquery<br />".mysql_error());
		$qcount=mysql_num_rows($qresult);
		if (!$qcount) {$qdelete[]=array("qid"=>$row['qid'], "reason"=>"No matching Group ({$row['gid']})");}
		//Make sure survey exists
		$qquery="SELECT * FROM {$dbprefix}surveys WHERE sid={$row['sid']}";
		$qresult=mysql_query($qquery) or die ("Couldn't check surveys table for sids from questions<br />$qquery<br />".mysql_error());
		$qcount=mysql_num_rows($qresult);
		if (!$qcount) {
			if (!isset($qdelete) || !in_array($row['qid'], $qdelete)) {$qdelete[]=array("qid"=>$row['qid'], "reason"=>"No matching Survey! ({$row['sid']})");}
			}
		}
	if (isset($qdelete) && $qdelete)
		{
		echo "<strong>"._DC_QUESTIONSTODELETE.":</strong><br /><font size='1'>\n";
		foreach ($qdelete as $qd) {echo "QID `{$qd['qid']}` because `{$qd['reason']}`<br />\n";}
		echo "</font><br />\n";
		}
	else
		{
		echo "<strong>"._DC_QUESTIONSOK."</strong><br />\n";
		}
	//check groups
	$query = "SELECT * FROM {$dbprefix}groups ORDER BY sid, gid";
	$result=mysql_query($query) or die ("Couldn't get list of groups for checking<br />$query<br />".mysql_error());
	while ($row=mysql_fetch_array($result))
		{
		//make sure survey exists
		$qquery = "SELECT * FROM {$dbprefix}groups WHERE sid={$row['sid']}";
		$qresult=mysql_query($qquery) or die("Couldn't check surveys table for gids from groups<br />$qquery<br />".mysql_error());
		$qcount=mysql_num_rows($qresult);
		if (!$qcount) {$gdelete[]=array($row['gid']);}
		}
	if (isset($gdelete) && $gdelete)
		{
		echo "<strong>"._DC_GROUPSTODELETE.":</strong><br /><font size='1'>\n";
		echo implode(", ", $gdelete);
		echo "</font><br />\n";
		}
	else
		{
		echo "<strong>"._DC_GROUPSOK."</strong><br />\n";
		}
//NOW CHECK FOR STRAY SURVEY RESPONSE TABLES AND TOKENS TABLES
	if (!isset($cdelete) && !isset($adelete) && !isset($qdelete) && !isset($gdelete) && !isset($asgdelete) && !isset($assdelete) && !isset($qadelete)) {
	    echo "<br />"._DC_NOACTIONREQUIRED;
	} else {
		echo "<br />Should we proceed with the delete?<br />\n";
		echo "<form action='{$_SERVER['PHP_SELF']}' method='POST'>\n";
		if (isset($cdelete)) {
			foreach ($cdelete as $cd) {
				echo "<input type='hidden' name='cdelete[]' value='{$cd['cid']}'>\n";
			}
		}
		if (isset($adelete)) {
			foreach ($adelete as $ad) {
				echo "<input type='hidden' name='adelete[]' value='{$ad['qid']}|{$ad['code']}'>\n";
			}
		}
		if (isset($qdelete)) {
			foreach($qdelete as $qd) {
				echo "<input type='hidden' name='qdelete[]' value='{$qd['qid']}'>\n";
			}
		}
		if (isset($gdelete)) {
			foreach ($gdelete as $gd) {
				echo "<input type='hidden' name='gdelete[]' value='{$gd['gid']}'>\n";
			}
		}
		if (isset($qadelete)) {
		    foreach ($qadelete as $qad) {
				echo "<input type='hidden' name='qadelete[]' value='{$qad['qaid']}'\n";
			}
		}
		if (isset($assdelete)) {
		    foreach ($assdelete as $ass) {
				echo "<input type='hidden' name='assdelete[]' value='{$ass['id']}'\n";
			}
		}
		if (isset($asgdelete)) {
		    foreach ($asgdelete as $asg) {
				echo "<input type='hidden' name='asgdelete[]' value='{$asg['id']}'\n";
			}
		}
		echo "<input type='hidden' name='ok' value='Y'>\n";
		echo "<input type='submit' value='Yes - Delete Them!'>\n";
		echo "</form>\n";
		}
	echo "<br /><br /><a href='$scriptname'>"._B_ADMIN_BT."</a><br /><br />\n"
		."</font></td></tr></table>\n"
		."<table><tr><td height='1'></td></tr></table>\n";
	echo htmlfooter("", "");
	}
elseif ($ok == "Y")
	{
	sendcacheheaders();
	echo $htmlheader;
	echo "<table><tr><td height='1'></td></tr></table>\n"
		. "<table align='center' bgcolor='#DDDDDD' style='border: 1px solid #555555' "
		. "cellpadding='1' cellspacing='0' width='450'>\n"
		. "\t<tr>\n"
		. "\t\t<td colspan='2' align='center' bgcolor='#BBBBBB'>$setfont\n"
		. "\t\t\t<strong>"._DC_TITLE."</strong>\n"
		. "\t\t</font></td>\n"
		. "\t</tr>\n"
		. "\t<tr><td align='center'>$setfont";
	$cdelete=returnglobal('cdelete');
	$adelete=returnglobal('adelete');
	$qdelete=returnglobal('qdelete');
	$gdelete=returnglobal('gdelete');
	$assdelete=returnglobal('assdelete');
	$asgdelete=returnglobal('asgdelete');
	$qadelete=returnglobal('qadelete');
	
	if (isset($assdelete)) {
	    echo "Deleting Assessments:<br /><fontsize='1'>\n";
		foreach ($assdelete as $ass) {
			echo "Deleting ID:".$ass."<br />\n";
			$sql = "DELETE FROM {$dbprefix}assessments WHERE id=$ass";
			$result = mysql_query($sql) or die ("Couldn't delete ($sql)<br />".mysql_error());
		}
	}
	if (isset($asgdelete)) {
	    echo "Deleting Assessments:<br /><fontsize='1'>\n";
		foreach ($asgdelete as $asg) {
			echo "Deleting ID:".$asg."<br />\n";
			$sql = "DELETE FROM {$dbprefix}assessments WHERE id=$asg";
			$result = mysql_query($sql) or die ("Couldn't delete ($sql)<br />".mysql_error());
		}
	}
	if (isset($qadelete)) {
	    echo "Deleting Question_Attributes:<br /><fontsize='1'>\n";
		foreach ($qadelete as $qad) {
			echo "Deleting QAID:".$qad."<br />\n";
			$sql = "DELETE FROM {$dbprefix}question_attributes WHERE qaid=$qad";
			$result = mysql_query($sql) or die ("Couldn't delete ($sql)<br />".mysql_error());
		}
	}
	if (isset($cdelete)) {
	    echo "Deleting Conditions:<br /><font size='1'>\n";
		foreach ($cdelete as $cd) {
			echo "Deleting cid:".$cd."<br />\n";
			$sql = "DELETE FROM {$dbprefix}conditions WHERE cid=$cd";
			$result=mysql_query($sql) or die ("Couldn't Delete ($sql)<br />".mysql_error());
		}
		echo "</font><br />\n";
	}
	if (isset($adelete)) {
	    echo "Deleting Answers:<br /><font size='1'>\n";
		foreach ($adelete as $ad) {
			list($ad1, $ad2)=explode("|", $ad);
			echo "Deleting answer with qid:".$ad1." and code: ".$ad2."<br />\n";
			$sql = "DELETE FROM {$dbprefix}answers WHERE qid=$ad1 AND code='$ad2'";
			$result=mysql_query($sql) or die ("Couldn't Delete ($sql)<br />".mysql_error());
		}
		echo "</font><br />\n";
	}
	if (isset($qdelete)) {
	    echo "Deleting Questions:<br /><font size='1'>\n";
		foreach ($qdelete as $qd) {
			echo "Deleting qid:".$qd."<br />\n";
			$sql = "DELETE FROM {$dbprefix}questions WHERE qid=$qd";
			$result=mysql_query($sql) or die ("Couldn't Delete ($sql)<br />".mysql_error());
		}
		echo "</font><br />\n";
	}
	if (isset($gdelete)) {
	    echo "Deleting Groups:<br /><font size='1'>\n";
		foreach ($gdelete as $gd) {
			echo "Deleting gid:".$gd."<br />\n";
			$sql = "DELETE FROM {$dbprefix}groups WHERE gid=$gd";
			$result=mysql_query($sql) or die ("Couldn't Delete ($sql)<br />".mysql_error());
		}
		echo "</font><br />\n";
	}
	echo "Check database again?<br />\n";
	echo "<a href='dbchecker.php'>Check Again</a><br />\n";
	echo "<a href='$scriptname'>"._B_ADMIN_BT."</a>";
	echo "</td></tr></table></body></html>\n";
	}
?>