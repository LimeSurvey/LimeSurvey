<?php
/*
	#############################################################
	# >>> PHPSurveyor  										    #
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

//Ensure script is not run directly, avoid path disclosure
if (empty($homedir)) {die ("Cannot run this script directly");}

if (!isset($_GET['ok']) || !$_GET['ok'])
	{
	if (isset($_GET['fixnumbering']) && $_GET['fixnumbering'])
		{
	    //Fix a question id - requires renumbering a question
		$oldqid = $_GET['fixnumbering'];
		$query = "SELECT qid FROM {$dbprefix}questions ORDER BY qid DESC LIMIT 1";
		$result = mysql_query($query) or die("$query<br />".mysql_error());
		while ($row=mysql_fetch_array($result)) {$lastqid=$row['qid'];}
		$newqid=$lastqid+1;
		$query = "UPDATE {$dbprefix}questions SET qid=$newqid WHERE qid=$oldqid";
		$result = mysql_query($query) or die("$query<br />".mysql_error());
		//Update conditions.. firstly conditions FOR this question
		$query = "UPDATE {$dbprefix}conditions SET qid=$newqid WHERE qid=$oldqid";
		$result = mysql_query($query) or die("$query<br />".mysql_error());
		//Now conditions based upon this question
		$query = "SELECT cqid, cfieldname FROM {$dbprefix}conditions WHERE cqid=$oldqid";
		$result = mysql_query($query) or die("$query<br />".mysql_error());
		while ($row=mysql_fetch_array($result))
			{
			$switcher[]=array("cqid"=>$row['cqid'], "cfieldname"=>$row['cfieldname']);
			}
		if (isset($switcher))
			{
		    foreach ($switcher as $switch)
				{
				$query = "UPDATE {$dbprefix}conditions
						  SET cqid=$newqid,
						  cfieldname='".str_replace("X".$oldqid, "X".$newqid, $switch['cfieldname'])."'
						  WHERE cqid=$oldqid";
				$result = mysql_query($query) or die("$query<br />".mysql_error());
				}
			}
		//Now question_attributes
		$query = "UPDATE {$dbprefix}question_attributes SET qid=$newqid WHERE qid=$oldqid";
		$result = mysql_query($query) or die("$query<br />".mysql_error());
		//Now answers
		$query = "UPDATE {$dbprefix}answers SET qid=$newqid WHERE qid=$oldqid";
		$result = mysql_query($query) or die("$query<br />".mysql_error());
		}
	//CHECK TO MAKE SURE ALL QUESTION TYPES THAT REQUIRE ANSWERS HAVE ACTUALLY GOT ANSWERS
	//THESE QUESTION TYPES ARE:
	//	# "L" -> LIST
	//  # "O" -> LIST WITH COMMENT
	//  # "M" -> MULTIPLE OPTIONS
	//	# "P" -> MULTIPLE OPTIONS WITH COMMENTS
	//	# "A", "B", "C", "E", "F", "H", "^" -> Various Array Types
	//  # "R" -> RANKING
	$chkquery = "SELECT qid, question, gid FROM {$dbprefix}questions WHERE sid={$_GET['sid']} AND type IN ('L', 'O', 'M', 'P', 'A', 'B', 'C', 'E', 'F', 'R', '!', '^')";
	$chkresult = mysql_query($chkquery) or die ("Couldn't get list of questions<br />$chkquery<br />".mysql_error());
	while ($chkrow = mysql_fetch_array($chkresult))
		{
		$chaquery = "SELECT * FROM {$dbprefix}answers WHERE qid = {$chkrow['qid']} ORDER BY sortorder, answer";
		$charesult=mysql_query($chaquery);
		$chacount=mysql_num_rows($charesult);
		if (!$chacount > 0) 
			{
			$failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": "._AC_MULTI_NOANSWER, $chkrow['gid']);
			}
		}
		
	//NOW CHECK THAT ALL QUESTIONS HAVE A 'QUESTION TYPE' FIELD
	$chkquery = "SELECT qid, question, gid FROM {$dbprefix}questions WHERE sid={$_GET['sid']} AND type = ''";
	$chkresult = mysql_query($chkquery) or die ("Couldn't check questions for missing types<br />$chkquery<br />".mysql_error());
	while ($chkrow = mysql_fetch_array($chkresult))
		{
		$failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": "._AC_NOTYPE, $chkrow['gid']);
		}
	
	//CHECK THAT FLEXIBLE LABEL TYPE QUESTIONS HAVE AN "LID" SET
	$chkquery = "SELECT qid, question, gid FROM {$dbprefix}questions WHERE sid={$_GET['sid']} AND type IN ('F', 'H', 'W', 'Z') AND (lid = 0 OR lid is null)";
	$chkresult = mysql_query($chkquery) or die ("Couldn't check questions for missing LIDs<br />$chkquery<br />".mysql_error());
	while($chkrow = mysql_fetch_array($chkresult)){
		$failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": "._AC_NOLID, $chkrow['gid']);
	} // while
	//CHECK THAT ALL CONDITIONS SET ARE FOR QUESTIONS THAT PRECEED THE QUESTION CONDITION
	//A: Make an array of all the qids in order of appearance
//	$qorderquery="SELECT * FROM {$dbprefix}questions, {$dbprefix}groups WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}questions.sid={$_GET['sid']} ORDER BY group_name, {$dbprefix}questions.title";
//	$qorderresult=mysql_query($qorderquery) or die("Couldn't generate a list of questions in order<br />$qorderquery<br />".mysql_error());
//	$qordercount=mysql_num_rows($qorderresult);
//	$c=0;
//	while ($qorderrow=mysql_fetch_array($qorderresult)) 
//		{
//		$qidorder[]=array($c, $qorderrow['qid']);
//		$c++;
//		}
	//TO AVOID NATURAL SORT ORDER ISSUES, FIRST GET ALL QUESTIONS IN NATURAL SORT ORDER, AND FIND OUT WHICH NUMBER IN THAT ORDER THIS QUESTION IS
	$qorderquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND type not in ('S', 'D', 'T', 'Q')";
	$qorderresult = mysql_query($qorderquery) or die ("$qorderquery<br />".mysql_error());
	$qrows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
	while ($qrow = mysql_fetch_array($qorderresult)) {$qrows[] = $qrow;} // Get table output into array
	usort($qrows, 'CompareGroupThenTitle'); // Perform a case insensitive natural sort on group name then question title of a multidimensional array
	$c=0;
	foreach ($qrows as $qr) 
		{
		$qidorder[]=array($c, $qrow['qid']);
		$c++;
		}
	$qordercount="";
	//1: Get each condition's question id
	$conquery= "SELECT {$dbprefix}conditions.qid, cqid, {$dbprefix}questions.question, "
			 . "{$dbprefix}questions.gid "
			 . "FROM {$dbprefix}conditions, {$dbprefix}questions, {$dbprefix}groups "
			 . "WHERE {$dbprefix}conditions.qid={$dbprefix}questions.qid "
			 . "AND {$dbprefix}questions.gid={$dbprefix}groups.gid ORDER BY qid";
	$conresult=mysql_query($conquery) or die("Couldn't check conditions for relative consistency<br />$conquery<br />".mysql_error());
	//2: Check each conditions cqid that it occurs later than the cqid
	while ($conrow=mysql_fetch_array($conresult))
		{
		$cqidfound=0;
		$qidfound=0;
		$b=0;
		while ($b<$qordercount)
			{
			if ($conrow['cqid'] == $qidorder[$b][1])
				{
				$cqidfound = 1;
				$b=$qordercount;
				}
			if ($conrow['qid'] == $qidorder[$b][1])
				{
				$qidfound = 1;
				$b=$qordercount;
				}
			if ($qidfound == 1)
				{
				$failedcheck[]=array($conrow['qid'], $conrow['question'], ": "._AC_CON_OUTOFORDER, $conrow['gid']);
				}
			$b++;
			}
		}
	//CHECK THAT ALL THE CREATED FIELDS WILL BE UNIQUE
	$fieldmap=createFieldMap($surveyid, "full");
	foreach($fieldmap as $fielddata)
		{
		$fieldlist[]=$fielddata['fieldname'];
		}
	$fieldlist=array_reverse($fieldlist); //let's always change the later duplicate, not the earlier one
	$checkKeysUniqueComparison = create_function('$value','if ($value > 1) return true;');
	$duplicates = array_keys (array_filter (array_count_values($fieldlist), $checkKeysUniqueComparison));
	foreach ($duplicates as $dup)
		{
		$badquestion=arraySearchByKey($dup, $fieldmap, "fieldname", 1);
		$fix = "[<a href='$scriptname?action=activate&amp;sid=$surveyid&amp;fixnumbering=".$badquestion['qid']."'>Click Here to Fix</a>]";
		$failedcheck[]=array($badquestion['qid'], $badquestion['question'], ": Bad duplicate fieldname $fix", $badquestion['gid']);
		}
//	echo "<pre>";print_r($duplicates); echo "</pre>";
	
	//IF ANY OF THE CHECKS FAILED, PRESENT THIS SCREEN
	if (isset($failedcheck) && $failedcheck)
		{
		echo "<br />\n<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
		echo "\t\t\t\t<tr bgcolor='#555555'><td height='4'><font size='1' face='verdana' color='white'><strong>"._ACTIVATE." ($surveyid)</strong></font></td></tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td align='center' bgcolor='#ffeeee'>\n";
		echo "\t\t\t<font color='red'>$setfont<strong>"._ERROR."</strong><br />\n";
		echo "\t\t\t"._AC_FAIL."</font></font>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td>\n";
		echo "\t\t\t$setfont<strong>"._AC_PROBS."</strong></font><br />\n";
		echo "\t\t\t<ul>\n";
		foreach ($failedcheck as $fc)
			{
			echo "\t\t\t\t<li>$setfont Question qid-{$fc[0]} (\"<a href='$scriptname?sid=$surveyid&amp;gid=$fc[3]&amp;qid=$fc[0]'>{$fc[1]}</a>\") {$fc[2]}</font></li>\n";
			}
		echo "\t\t\t</ul>\n";
		echo "\t\t\t$setfont"._AC_CANNOTACTIVATE."</font>\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "\t<tr>\n";
		echo "\t\t<td align='center'>\n";
		echo "\t\t\t<input type='submit' $btstyle value='"._GO_ADMIN."' onClick=\"window.open('$scriptname?sid={$_GET['sid']}', '_top')\">\n";
		echo "\t\t</td>\n";
		echo "\t</tr>\n";
		echo "</table>\n";
		exit;		
		}
	
	echo "<br />\n<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t\t\t\t<tr bgcolor='#555555'><td height='4'><font size='1' face='verdana' color='white'><strong>"._ACTIVATE." ($surveyid)</strong></font></td></tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center' bgcolor='#ffeeee'>\n";
	echo "\t\t\t<font color='red'>$setfont<strong>"._WARNING."</strong><br />\n";
	echo "\t\t\t"._AC_READCAREFULLY."\n";
	echo "\t\t\t</font></font>\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td>\n";
	echo _AC_ACTIVATE_MESSAGE1."<br /><br />\n";
	echo _AC_ACTIVATE_MESSAGE2."\n";
	echo _AC_ACTIVATE_MESSAGE3."\n";
	echo _AC_ACTIVATE_MESSAGE4."<br /><br />\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "\t<tr>\n";
	echo "\t\t<td align='center'>\n";
	echo "\t\t\t<input type='submit' $btstyle value=\""._AD_CANCEL."\" onclick=\"window.open('$scriptname?sid={$_GET['sid']}', '_top')\"><br />\n";
	echo "\t\t\t<input type='submit' $btstyle value=\""._AC_ACTIVATE."\" onClick=\"window.open('$scriptname?action=activate&amp;ok=Y&amp;sid={$_GET['sid']}', '_top')\">\n";
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	echo "</body>\n</html>";
	
	}
else
	{
	//Create the survey responses table
	$createsurvey = "CREATE TABLE {$dbprefix}survey_{$_GET['sid']} (\n";
	$createsurvey .= "  id BIGINT(11) NOT NULL auto_increment,\n";
	//Check for any additional fields for this survey and create necessary fields (token and datestamp)
	$pquery = "SELECT private, allowregister, datestamp, ipaddr FROM {$dbprefix}surveys WHERE sid={$_GET['sid']}";
	$presult=mysql_query($pquery);
	while($prow=mysql_fetch_array($presult))
		{
		if ($prow['private'] == "N") 
			{
			$createsurvey .= "  token VARCHAR(10),\n";
			$surveynotprivate="TRUE";
			}
		if ($prow['allowregister'] == "Y") 
			{
			$surveyallowsregistration="TRUE";
			}
		if ($prow['datestamp'] == "Y")
			{
			$createsurvey .= " datestamp DATETIME NOT NULL,\n";
			}
		if ($prow['ipaddr'] == "Y")
			{
			$createsurvey .= " ipaddr MEDIUMTEXT,\n";
			}
}
	//Get list of questions
	$aquery = "SELECT * FROM {$dbprefix}questions, {$dbprefix}groups WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}questions.sid={$_GET['sid']} ORDER BY group_name, title";
	$aresult = mysql_query($aquery);
	while ($arow=mysql_fetch_array($aresult)) //With each question, create the appropriate field(s)
		{
		if ($arow['type'] != "M" && $arow['type'] != "A" && $arow['type'] != "B" && 
			$arow['type'] !="C" && $arow['type'] != "E" && $arow['type'] != "F" && 
			$arow['type'] != "H" && $arow['type'] !="P" && $arow['type'] != "R" && 
			$arow['type'] != "Q" && $arow['type'] != "^")
			{
			$createsurvey .= "  `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}`";
			switch($arow['type'])
				{
				case "N":  //NUMERICAL
					$createsurvey .= " TINYTEXT";
					break;
				case "S":  //SHORT TEXT
					$createsurvey .= " TINYTEXT";
					break;
				case "L":  //LIST (RADIO)
				case "!":  //LIST (DROPDOWN)
				case "W":
				case "Z":
					$createsurvey .= " VARCHAR(5)";
					if ($arow['other'] == "Y") 
						{
						$createsurvey .= ",\n`{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other` TEXT";
						}
					break;
				case "O": //DROPDOWN LIST WITH COMMENT
					$createsurvey .= " VARCHAR(5),\n `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}comment` TEXT";
					break;
				case "T":  //LONG TEXT
					$createsurvey .= " TEXT";
					break;
				case "U":  //HUGE TEXT
					$createsurvey .= " TEXT";
					break;
				case "D":  //DATE
					$createsurvey .= " DATE";
					break;
				case "5":  //5 Point Choice
				case "G":  //Gender
				case "Y":  //YesNo
				case "X":  //Boilerplate
					$createsurvey .= " VARCHAR(1)";
					break;
				}
			}
		elseif ($arow['type'] == "M" || $arow['type'] == "A" || $arow['type'] == "B" || 
				$arow['type'] == "C" || $arow['type'] == "E" || $arow['type'] == "F" || 
				$arow['type'] == "H" || $arow['type'] == "P" || $arow['type'] == "^")
			{
			//MULTI ENTRY
			$abquery = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND sid={$_GET['sid']} AND {$dbprefix}questions.qid={$arow['qid']} ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
			$abresult=mysql_query($abquery) or die ("Couldn't get perform answers query<br />$abquery<br />".mysql_error());
			while ($abrow=mysql_fetch_array($abresult))
				{
				$createsurvey .= "  `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}` VARCHAR(5),\n";
				if ($abrow['other']=="Y") {$alsoother="Y";}
				if ($arow['type'] == "P")
					{
					$createsurvey .= "  `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}comment` TINYTEXT,\n";
					}
				}
			if ((isset($alsoother) && $alsoother=="Y") && ($arow['type']=="M" || $arow['type']=="P"))
				{
				$createsurvey .= " `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other` TINYTEXT,\n";
				if ($arow['type']=="P")
					{
					$createsurvey .= " `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}othercomment` TINYTEXT,\n";
					}
				}
			}
		elseif ($arow['type'] == "Q")
			{
			$abquery = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND sid={$_GET['sid']} AND {$dbprefix}questions.qid={$arow['qid']} ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
			$abresult=mysql_query($abquery) or die ("Couldn't get perform answers query<br />$abquery<br />".mysql_error());
			while ($abrow=mysql_fetch_array($abresult))
				{
				$createsurvey .= "  `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['code']}` TINYTEXT,\n";
				}
			}
		elseif ($arow['type'] == "R")
			{
			//MULTI ENTRY
			$abquery = "SELECT {$dbprefix}answers.*, {$dbprefix}questions.other FROM {$dbprefix}answers, {$dbprefix}questions WHERE {$dbprefix}answers.qid={$dbprefix}questions.qid AND sid={$_GET['sid']} AND {$dbprefix}questions.qid={$arow['qid']} ORDER BY {$dbprefix}answers.sortorder, {$dbprefix}answers.answer";
			$abresult=mysql_query($abquery) or die ("Couldn't get perform answers query<br />$abquery<br />".mysql_error());
			$abcount=mysql_num_rows($abresult);
			for ($i=1; $i<=$abcount; $i++)
				{
				$createsurvey .= "  `{$arow['sid']}X{$arow['gid']}X{$arow['qid']}$i` VARCHAR(5),\n";
				}			
			}
		if ( substr($createsurvey, strlen($createsurvey)-2, 2) != ",\n") {$createsurvey .= ",\n";}
		}
	//$createsurvey = substr($createsurvey, 0, strlen($createsurvey)-2);
	$createsurvey .= "  UNIQUE(id)\n";
	$createsurvey .= ") TYPE=MyISAM;";
	//echo "<pre style='text-align: left'>$createsurvey</pre>\n"; //Debugging info
	
	$createtable=mysql_query($createsurvey) or die 
		(
		"<br />\n<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n" .
		"<tr bgcolor='#555555'><td height='4'><font size='1' face='verdana' color='white'><strong>"._ACTIVATE." ($surveyid)</strong></font></td></tr>\n" .
		"<tr><td>\n" .
		"<font color='red'>"._AC_NOTACTIVATED."</font><br />\n" .
		"<center><a href='$scriptname?sid={$_GET['sid']}'>"._GO_ADMIN."</a></center>\n" .
		"DB "._ERROR.":<br />\n<font color='red'>" . mysql_error() . "</font>\n" .
		"<pre>$createsurvey</pre>\n" .
		"</td></tr></table>\n" .
		"</body>\n</html>"
		);
	
	$anquery = "SELECT autonumber_start FROM {$dbprefix}surveys WHERE sid={$_GET['sid']}";
	if ($anresult=mysql_query($anquery)) 
		{
		//if there is an autonumber_start field, start auto numbering here
		while($row=mysql_fetch_array($anresult))
			{
			if ($row['autonumber_start'] > 0) 
				{
				$autonumberquery = "ALTER TABLE {$dbprefix}survey_{$_GET['sid']} AUTO_INCREMENT = ".$row['autonumber_start'];
				if ($result = mysql_query($autonumberquery))
					{
					//We're happy it worked!
					}
				else
					{
					//Continue regardless - it's not the end of the world
					}
				}
			}
		}
	if (isset($useidprefix) && $useidprefix == 1 && !isset($autonumberquery))
		{
		if (!isset($idprefix) || $idprefix == 0 || is_string($idprefix))
			{
			$idprefix="";
		    $elements=explode(".", $_SERVER['SERVER_ADDR']);
			foreach ($elements as $element)
				{
				$idprefix.=sprintf("%03d", $element);
				}
			}
		$idprefix = "{$idprefix}0000000"; //Setting 7 zeros at the end allows for up to 9,999,999 responses
		$autonumberquery = "ALTER TABLE {$dbprefix}survey_{$_GET['sid']} AUTO_INCREMENT = ".$idprefix;
		if (!$result = mysql_query($autonumberquery))
			{
		    echo "There was an error defining the autonumbering to start at $idprefix.<br />";
			}
		}
	
	echo "<br />\n<table width='350' align='center' style='border: 1px solid #555555' cellpadding='1' cellspacing='0'>\n";
	echo "\t\t\t\t<tr bgcolor='#555555'><td height='4'><font size='1' face='verdana' color='white'><strong>"._ACTIVATE." ($surveyid)</strong></font></td></tr>\n";
	echo "\t\t\t\t<tr><td align='center'>$setfont<font color='green'>"._AC_ACTIVATED."<br /><br />\n";
	
	$acquery = "UPDATE {$dbprefix}surveys SET active='Y' WHERE sid={$_GET['sid']}";
	$acresult = mysql_query($acquery);
	
	if (isset($surveynotprivate) && $surveynotprivate) //This survey is tracked, and therefore a tokens table MUST exist
		{
		echo _AC_NOTPRIVATE."<br /><br />\n";
		echo "<input type='submit' value='"._AC_CREATETOKENS."' $btstyle onClick=\"window.open('tokens.php?sid={$_GET['sid']}&amp;createtable=Y', '_top')\">\n";
		}
	elseif (isset($surveyallowsregistration) && $surveyallowsregistration == "TRUE")
		{
		echo _AC_REGISTRATION."<br /><br />\n";
		echo "<input type='submit' value='"._AC_CREATETOKENS."' $btstyle onClick=\"window.open('tokens.php?sid={$_GET['sid']}&amp;createtable=Y', '_top')\">\n";
		}
	else
		{
		echo _AC_SURVEYACTIVE."<br /><br />\n";
		echo "<input type='submit' value='"._GO_ADMIN."' $btstyle onClick=\"window.open('$scriptname?sid={$_GET['sid']}', '_top')\">\n";
		}
	echo "\t\t\t\t</font></font></td></tr></table>\n";
	echo "</body>\n</html>";
	}	
?>