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
if (!isset($action)) {$action=returnglobal('action');}

if ($action == "delattribute")
	{
    $query = "DELETE FROM {$dbprefix}question_attributes
			  WHERE qaid=".$_POST['qaid']."
			  AND qid=".$_POST['qid'];
	$result=mysql_query($query) or die("Couldn't delete attribute<br />$query<br />".mysql_error());
	}
elseif ($action == "addattribute")
	{
	if (isset($_POST['attribute_value']) && $_POST['attribute_value'])
		{
		$query = "INSERT INTO {$dbprefix}question_attributes
				  (qaid, qid, attribute, value)
				  VALUES
				  ('',
				  '".$_POST['qid']."',
				  '".$_POST['attribute_name']."',
				  '".$_POST['attribute_value']."')";
		$result = mysql_query($query) or die("Error<br />$query<br />".mysql_error());
	    }
	}
elseif ($action == "editattribute")
	{
	if (isset($_POST['attribute_value']) && $_POST['attribute_value'])
		{
		$query = "UPDATE {$dbprefix}question_attributes
				  SET value='".$_POST['attribute_value']."'
				  WHERE qaid=".$_POST['qaid']."
				  AND qid=".$_POST['qid'];
		$result = mysql_query($query) or die("Error<br />$query<br />".mysql_error());
		}
	}
elseif ($action == "insertnewgroup")
	{
	if (!$_POST['group_name'])
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_GROUPNAME."\")\n //-->\n</script>\n";		
		}
	else
		{
		if (get_magic_quotes_gpc() == "0")
			{
			$_POST['description'] = addcslashes($_POST['description'], "'");
			$_POST['group_name'] = addcslashes($_POST['group_name'], "'");
			}

		$query = "INSERT INTO {$dbprefix}groups (sid, group_name, description) VALUES ('{$_POST['sid']}', '{$_POST['group_name']}', '{$_POST['description']}')";
		$result = mysql_query($query);
		
		if ($result)
			{
			//echo "<script type=\"text/javascript\">\n<!--\n alert(\"New group ({$_POST['group_name']}) has been created for survey id $sid\")\n //-->\n</script>\n";
			$query = "SELECT gid FROM {$dbprefix}groups WHERE group_name='{$_POST['group_name']}' AND sid={$_POST['sid']}";
			$result = mysql_query($query);
			while ($res = mysql_fetch_array($result)) {$gid = $res['gid'];}
			$groupselect = getgrouplist($gid);
			}
		else
			{
			echo _ERROR.": The database reported the following error:<br />\n";
			echo "<font color='red'>" . mysql_error() . "</font>\n";
			echo "<pre>$query</pre>\n";
			echo "</body>\n</html>";
			exit;
			}
		}
	}

elseif ($action == "updategroup")
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$_POST['description'] = addcslashes($_POST['description'], "'");
		$_POST['group_name'] = addcslashes($_POST['group_name'], "'");
		}

	$ugquery = "UPDATE {$dbprefix}groups SET group_name='{$_POST['group_name']}', description='{$_POST['description']}' WHERE sid={$_POST['sid']} AND gid={$_POST['gid']}";
	$ugresult = mysql_query($ugquery);
	if ($ugresult)
		{
		//echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your Group ($group_name) has been updated!\")\n //-->\n</script>\n";
		$groupsummary = getgrouplist($_POST['gid']);
		}
	else
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_GROUPUPDATE."\")\n //-->\n</script>\n";
		}

	}

elseif ($action == "delgroupnone")
	{
	if (!isset($gid)) {returnglobal('gid');}
	$query = "DELETE FROM {$dbprefix}groups WHERE sid=$sid AND gid=$gid";
	$result = mysql_query($query);
	if ($result)
		{
		$gid = "";
		$groupselect = getgrouplist($gid);
		}
	else
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_GROUPDELETE."\n$error\")\n //-->\n</script>\n";
		}
	}

elseif ($action == "delgroup")
	{
	if (!isset($gid)) {returnglobal('gid');}
	$query = "SELECT qid FROM {$dbprefix}groups, {$dbprefix}questions WHERE {$dbprefix}groups.gid={$dbprefix}questions.gid AND {$dbprefix}groups.gid=$gid";
	if ($result = mysql_query($query))
		{
		if (!isset($total)) {$total=0;}
		$qtodel=mysql_num_rows($result);
		while ($row=mysql_fetch_array($result))
			{
			$dquery = "DELETE FROM {$dbprefix}conditions WHERE qid={$row['qid']}";
			if ($dresult=mysql_query($dquery)) {$total++;}
			$dquery = "DELETE FROM {$dbprefix}answers WHERE qid={$row['qid']}";
			if ($dresult=mysql_query($dquery)) {$total++;}
			$dquery = "DELETE FROM {$dbprefix}questions WHERE qid={$row['qid']}";
			if ($dresult=mysql_query($dquery)) {$total++;}
			}
		if ($total != $qtodel*3)
			{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_GROUPDELETE."\")\n //-->\n</script>\n";
			}
		}
	$query = "DELETE FROM {$dbprefix}groups WHERE sid=$sid AND gid=$gid";
	$result = mysql_query($query);
	if ($result)
		{
		$gid = "";
		$groupselect = getgrouplist($gid);
		}
	else
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_GROUPDELETE."\n$error\")\n //-->\n</script>\n";
		}
	}

elseif ($action == "insertnewquestion")
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$_POST['title'] = addcslashes($_POST['title'], "'");
		$_POST['question'] = addcslashes($_POST['question'], "'");
		$_POST['help'] = addcslashes($_POST['help'], "'");
		$_POST['preg'] = mysql_escape_string($_POST['preg']);
		}
	if (!isset($_POST['lid'])) {$_POST['lid']="";}
	$query = "INSERT INTO {$dbprefix}questions (qid, sid, gid, type, title, question, preg, help, other, mandatory, lid)"
			." VALUES ('', '{$_POST['sid']}', '{$_POST['gid']}', '{$_POST['type']}', '{$_POST['title']}',"
			." '{$_POST['question']}', '{$_POST['preg']}', '{$_POST['help']}', '{$_POST['other']}', '{$_POST['mandatory']}', '{$_POST['lid']}')";
	$result = mysql_query($query);
	if (!$result)
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_NEWQUESTION."\\n".mysql_error()."\")\n //-->\n</script>\n";
		}
	else
		{
		$query = "SELECT qid FROM {$dbprefix}questions ORDER BY qid DESC LIMIT 1"; //get last question id
		$result=mysql_query($query);
		while ($row=mysql_fetch_array($result)) {$qid = $row['qid'];}
		}
	if (isset($_POST['attribute_value']) && $_POST['attribute_value'])
		{
	    $query = "INSERT INTO {$dbprefix}question_attributes
				  (qaid, qid, attribute, value)
				  VALUES
				  ('',$qid, '".$_POST['attribute_name']."', '".$_POST['attribute_value']."')";
		$result = mysql_query($query);
		}
	}	

elseif ($action == "renumberquestions")
	{
	//Automatically renumbers the "question codes" so that they follow
	//a methodical numbering method
	$question_number=1;
	$group_number=0;
	$gselect="SELECT *\n"
			."FROM {$dbprefix}questions, {$dbprefix}groups\n"
			."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid\n"
			."AND {$dbprefix}questions.sid=$sid\n"
			."ORDER BY group_name, title";
	$gresult=mysql_query($gselect) or die (mysql_error());
	$grows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
	while ($grow = mysql_fetch_array($gresult)) {$grows[] = $grow;} // Get table output into array
	usort($grows, 'CompareGroupThenTitle');
	foreach($grows as $grow)
		{
		//Go through all the questions
		if ((isset($_GET['style']) && $_GET['style']=="bygroup") && (!isset($groupname) || $groupname != $grow['group_name']))
			{
		    $question_number=1;
			$group_number++;
			}
		//echo "GROUP: ".$grow['group_name']."<br />";
		$usql="UPDATE {$dbprefix}questions\n"
			."SET title='".$question_number."'\n"
			."WHERE qid=".$grow['qid'];
		//echo "[$sql]";
		$uresult=mysql_query($usql) or die("Error:".mysql_error());
		$question_number++;
		$groupname=$grow['group_name'];
		}
	}

elseif ($action == "updatequestion")
	{
	$cqquery = "SELECT type FROM {$dbprefix}questions WHERE qid={$_POST['qid']}";
	$cqresult=mysql_query($cqquery) or die ("Couldn't get question type to check for change<br />$cqquery<br />".mysql_error());
	while ($cqr=mysql_fetch_array($cqresult)) {$oldtype=$cqr['type'];}
	if ($oldtype != $_POST['type'])
		{
		//Make sure there are no conditions based on this question, since we are changing the type
		$ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid={$_POST['qid']}";
		$ccresult = mysql_query($ccquery) or die ("Couldn't get list of cqids for this question<br />$ccquery<br />".mysql_error());
		$cccount=mysql_num_rows($ccresult);
		while ($ccr=mysql_fetch_array($ccresult)) {$qidarray[]=$ccr['qid'];}
		if (isset($qidarray) && $qidarray) {$qidlist=implode(", ", $qidarray);}
		}
	if (get_magic_quotes_gpc() == "0")
		{
		$_POST['title'] = addcslashes($_POST['title'], "'");
		$_POST['question'] = addcslashes($_POST['question'], "'");
		$_POST['help'] = addcslashes($_POST['help'], "'");
		$_POST['preg'] = mysql_escape_string($_POST['preg']);
		}
	if (isset($cccount) && $cccount)
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_QUESTIONTYPECONDITIONS." ($qidlist)\")\n //-->\n</script>\n";
		}
	else
		{
		if (isset($_POST['gid']) && $_POST['gid'] != "")
			{
			$uqquery = "UPDATE {$dbprefix}questions "
					. "SET type='".returnglobal('type')."', title='".returnglobal('title')."', "
					. "question='".returnglobal('question')."', preg='".returnglobal('preg')."', help='".returnglobal('help')."', "
					. "gid='".returnglobal('gid')."', other='".returnglobal('other')."', "
					. "mandatory='".returnglobal('mandatory')."', lid='".returnglobal('lid')."' "
					. "WHERE sid={$_POST['sid']} AND qid={$_POST['qid']}";
			$uqresult = mysql_query($uqquery) or die ("Error Update Question: $uqquery<br />".mysql_error());
			if (!$uqresult)
				{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_QUESTIONUPDATE."\n".mysql_error()."\")\n //-->\n</script>\n";
				}
			}
		else
			{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_QUESTIONUPDATE."\")\n //-->\n</script>\n";
			}
		}
	}

elseif ($action == "copynewquestion")
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$_POST['title'] = addcslashes($_POST['title'], "'");
		$_POST['question'] = addcslashes($_POST['question'], "'");
		$_POST['help'] = addcslashes($_POST['help'], "'");
		}
	if (!isset($_POST['lid'])) {$_POST['lid']="";}
	$query = "INSERT INTO {$dbprefix}questions (qid, sid, gid, type, title, question, help, other, mandatory, lid) VALUES ('', '{$_POST['sid']}', '{$_POST['gid']}', '{$_POST['type']}', '{$_POST['title']}', '{$_POST['question']}', '{$_POST['help']}', '{$_POST['other']}', '{$_POST['mandatory']}', '{$_POST['lid']}')";
	$result = mysql_query($query);
	$newqid = mysql_insert_id();
	if (!$result)
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_NEWQUESTION."\\n".mysql_error()."\")\n //-->\n</script>\n";
		}
	if (returnglobal('copyanswers') == "Y")
		{
		$q1 = "SELECT * FROM {$dbprefix}answers WHERE qid="
			. returnglobal('oldqid')
			. " ORDER BY code";
		$r1 = mysql_query($q1);
		while ($qr1 = mysql_fetch_array($r1))
			{
			$i1 = "INSERT INTO {$dbprefix}answers (qid, code, answer, default_value, sortorder) "
				. "VALUES ('$newqid', '{$qr1['code']}', "
				. "'".mysql_escape_string($qr1['answer'])."', '{$qr1['default_value']}', "
				. "'{$qr1['sortorder']}')";
			$ir1 = mysql_query($i1);
			}		
		}
	if (returnglobal('copyattributes') == "Y") 
		{
		$q1 = "SELECT * FROM {$dbprefix}question_attributes
			   WHERE qid=".returnglobal('oldqid')."
			   ORDER BY qaid";
		$r1 = mysql_query($q1);
		while($qr1 = mysql_fetch_array($r1))
			{
			$i1 = "INSERT INTO {$dbprefix}question_attributes
				   (qid, attribute, value)
				   VALUES ('$newqid',
				   '{$qr1['attribute']}',
				   '{$qr1['value']}')";
			$ir1 = mysql_query($i1);
			} // while
		}
	}	

elseif ($action == "delquestion")
	{
	if (!isset($qid)) {$qid=returnglobal('qid');}
	//check if any other questions have conditions which rely on this question. Don't delete if there are.
	$ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid={$_GET['qid']}";
	$ccresult = mysql_query($ccquery) or die ("Couldn't get list of cqids for this question<br />$ccquery<br />".mysql_error());
	$cccount=mysql_num_rows($ccresult);
	while ($ccr=mysql_fetch_array($ccresult)) {$qidarray[]=$ccr['qid'];}
	if (isset($qidarray)) {$qidlist=implode(", ", $qidarray);}
	if ($cccount) //there are conditions dependant on this question
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_QUESTIONDELCONDITIONS." ($qidlist)\")\n //-->\n</script>\n";
		}
	else
		{
		//see if there are any conditions/attributes/answers for this question, and delete them now as well
		$cquery = "DELETE FROM {$dbprefix}conditions WHERE qid=$qid";
		$cresult = mysql_query($cquery);
		$query = "DELETE FROM {$dbprefix}question_attributes WHERE qid=$qid";
		$result = mysql_query($query);
		$cquery = "DELETE FROM {$dbprefix}answers WHERE qid=$qid";
		$cresult = mysql_query($cquery);
		$query = "DELETE FROM {$dbprefix}questions WHERE qid=$qid";
		$result = mysql_query($query);
		if ($result) 
			{
			$qid="";
			$_POST['qid']="";
			$_GET['qid']="";
			}
		else
			{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_QUESTIONDELETE."\n$error\")\n //-->\n</script>\n";
			}
		}
	}

elseif ($action == "delquestionall")
	{
	if (!isset($qid)) {returnglobal('qid');}
	//check if any other questions have conditions which rely on this question. Don't delete if there are.
	$ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid={$_GET['qid']}";
	$ccresult = mysql_query($ccquery) or die ("Couldn't get list of cqids for this question<br />$ccquery<br />".mysql_error());
	$cccount=mysql_num_rows($ccresult);
	while ($ccr=mysql_fetch_array($ccresult)) {$qidarray[]=$ccr['qid'];}
	if (isset($qidarray) && $qidarray) {$qidlist=implode(", ", $qidarray);}
	if ($cccount) //there are conditions dependant on this question
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_QUESTIONDELCONDITIONS." ($qidlist)\")\n //-->\n</script>\n";
		}
	else
		{
		//First delete all the answers
		if (!isset($total)) {$total=0;}
		$query = "DELETE FROM {$dbprefix}answers WHERE qid=$qid";
		if ($result=mysql_query($query)) {$total++;}
		$query = "DELETE FROM {$dbprefix}conditions WHERE qid=$qid";
		if ($result=mysql_query($query)) {$total++;}
		$query = "DELETE FROM {$dbprefix}questions WHERE qid=$qid";
		if ($result=mysql_query($query)) {$total++;}
		}
		if ($total==3)
			{
			$qid="";
			$_POST['qid']="";
			$_GET['qid']="";
			}
		else
			{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_QUESTIONDELETE."\n$error\")\n //-->\n</script>\n";
			}
	}
	
elseif ($action == "modanswer")
	{
	if ((!isset($_POST['olddefault']) || ($_POST['olddefault'] != $_POST['default']) && $_POST['default'] == "Y") || ($_POST['default'] == "Y" && $_POST['ansaction'] == _AL_ADD)) //TURN ALL OTHER DEFAULT SETTINGS TO NO
		{
		$query = "UPDATE {$dbprefix}answers SET default_value = 'N' WHERE qid={$_POST['qid']}";
		$result=mysql_query($query) or die("Error occurred updating default_value settings");
		}
	if (get_magic_quotes_gpc() == "0")
		{
		$_POST['code'] = addcslashes($_POST['code'], "'");
		if (isset($_POST['oldcode'])) {$_POST['oldcode'] = addcslashes($_POST['oldcode'], "'");}
		$_POST['answer'] = addcslashes($_POST['answer'], "'");
		if (isset($_POST['oldanswer'])) {$_POST['oldanswer'] = addcslashes($_POST['oldanswer'], "'");}
		}
	switch ($_POST['ansaction'])
		{
		case _AL_FIXSORT:
			fixsortorder($_POST['qid']);
			break;
		case _AL_SORTALPHA:
			$uaquery = "SELECT * FROM {$dbprefix}answers WHERE qid='{$_POST['qid']}' ORDER BY answer";
			$uaresult = mysql_query($uaquery) or die("Cannot get answers<br />$uaquery<br />".mysql_error());
			while($uarow=mysql_fetch_array($uaresult))
				{
				$orderedanswers[]=array("qid"=>$uarow['qid'],
										"code"=>$uarow['code'],
										"answer"=>$uarow['answer'],
										"default_value"=>$uarow['default_value'],
										"sortorder"=>$uarow['sortorder']);
				} // while
			$i=0;
			foreach ($orderedanswers as $oa)
				{
				$position=sprintf("%05d", $i);
				$upquery = "UPDATE {$dbprefix}answers SET sortorder='$position' WHERE qid='{$oa['qid']}' AND code='{$oa['code']}'";
				$upresult = mysql_query($upquery);
				$i++;
				} // foreach
			break;
		case _AL_ADD:
			if (!$_POST['code'] || !$_POST['answer'])
				{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_NEWANSWERMISSING."\")\n //-->\n</script>\n";
				}
			else
				{
				$uaquery = "SELECT * FROM {$dbprefix}answers WHERE code = '{$_POST['code']}' AND qid={$_POST['qid']}";
				$uaresult = mysql_query($uaquery) or die ("Cannot check for duplicate codes<br />$uaquery<br />".mysql_error());
				$matchcount = mysql_num_rows($uaresult);
				if ($matchcount) //another answer exists with the same code
					{
					echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_NEWANSWERDUPLICATE."\")\n //-->\n</script>\n";
					}
				else
					{
					$cdquery = "INSERT INTO {$dbprefix}answers (qid, code, answer, sortorder, default_value) VALUES ('{$_POST['qid']}', '{$_POST['code']}', '{$_POST['answer']}', '{$_POST['sortorder']}', '{$_POST['default']}')";
					$cdresult = mysql_query($cdquery) or die ("Couldn't add answer<br />$cdquery<br />".mysql_error());
					}
				}
			break;
		case _AL_SAVE:
			if (!$_POST['code'] || !$_POST['answer'])
				{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_ANSWERUPDATEMISSING."\")\n //-->\n</script>\n";
				}
			else
				{
				if ($_POST['code'] != $_POST['oldcode']) //code is being changed. Check against other codes and conditions
					{
					$uaquery = "SELECT * FROM {$dbprefix}answers WHERE code = '{$_POST['code']}' AND qid={$_POST['qid']}";
					$uaresult = mysql_query($uaquery) or die ("Cannot check for duplicate codes<br />$uaquery<br />".mysql_error());
					$matchcount = mysql_num_rows($uaresult);
					$ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid={$_POST['qid']} AND value='{$_POST['oldcode']}'";
					$ccresult = mysql_query($ccquery) or die ("Couldn't get list of cqids for this answer<br />$ccquery<br />".mysql_error());
					$cccount=mysql_num_rows($ccresult);
					while ($ccr=mysql_fetch_array($ccresult)) {$qidarray[]=$ccr['qid'];}
					if (isset($qidarray)) {$qidlist=implode(", ", $qidarray);}
					}
				if (isset($matchcount) && $matchcount) //another answer exists with the same code
					{
					echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_ANSWERUPDATEDUPLICATE."\")\n //-->\n</script>\n";
					}
				else
					{
					if (isset($cccount) && $cccount) // there are conditions dependent upon this answer to this question
						{
						echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_ANSWERUPDATECONDITIONS." ($qidlist)\")\n //-->\n</script>\n";
						}
					else
						{
						$cdquery = "UPDATE {$dbprefix}answers SET qid='{$_POST['qid']}', code='{$_POST['code']}', answer='{$_POST['answer']}', sortorder='{$_POST['sortorder']}', default_value='{$_POST['default']}' WHERE code='{$_POST['oldcode']}' AND answer='{$_POST['oldanswer']}' AND qid='{$_POST['qid']}'";
						$cdresult = mysql_query($cdquery) or die ("Couldn't update answer<br />$cdquery<br />".mysql_error());
						}
					}
				}
			break;
		case _AL_DEL:
			$ccquery = "SELECT * FROM {$dbprefix}conditions WHERE cqid={$_POST['qid']} AND value='{$_POST['oldcode']}'";
			$ccresult = mysql_query($ccquery) or die ("Couldn't get list of cqids for this answer<br />$ccquery<br />".mysql_error());
			$cccount=mysql_num_rows($ccresult);
			while ($ccr=mysql_fetch_array($ccresult)) {$qidarray[]=$ccr['qid'];}
			if (isset($qidarray) && $qidarray) {$qidlist=implode(", ", $qidarray);}
			if ($cccount)
				{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_ANSWERDELCONDITIONS." ($qidlist)\")\n //-->\n</script>\n";
				}
			else
				{
				$cdquery = "DELETE FROM {$dbprefix}answers WHERE code='{$_POST['oldcode']}' AND answer='{$_POST['oldanswer']}' AND qid='{$_POST['qid']}'";
				$cdresult = mysql_query($cdquery) or die ("Couldn't update answer<br />$cdquery<br />".mysql_error());
				}
			fixsortorder($qid);
			break;
		case _AL_UP:
			$newsortorder=sprintf("%05d", $_POST['sortorder']-1);
			$replacesortorder=$newsortorder;
			$newreplacesortorder=sprintf("%05d", $_POST['sortorder']);
			$cdquery = "UPDATE {$dbprefix}answers SET sortorder='PEND' WHERE qid=$qid AND sortorder='$newsortorder'";
			$cdresult=mysql_query($cdquery) or die(mysql_error());
			$cdquery = "UPDATE {$dbprefix}answers SET sortorder='$newsortorder' WHERE qid=$qid AND sortorder='$newreplacesortorder'";
			$cdresult=mysql_query($cdquery) or die(mysql_error());
			$cdquery = "UPDATE {$dbprefix}answers SET sortorder='$newreplacesortorder' WHERE qid=$qid AND sortorder='PEND'";
			$cdresult=mysql_query($cdquery) or die(mysql_error());
			break;
		case _AL_DN:
			$newsortorder=sprintf("%05d", $_POST['sortorder']+1);
			$replacesortorder=$newsortorder;
			$newreplacesortorder=sprintf("%05d", $_POST['sortorder']);
			$newreplace2=sprintf("%05d", $_POST['sortorder']);
			$cdquery = "UPDATE {$dbprefix}answers SET sortorder='PEND' WHERE qid=$qid AND sortorder='$newsortorder'";
			$cdresult=mysql_query($cdquery) or die(mysql_error());
			$cdquery = "UPDATE {$dbprefix}answers SET sortorder='$newsortorder' WHERE qid=$qid AND sortorder='{$_POST['sortorder']}'";
			$cdresult=mysql_query($cdquery) or die(mysql_error());
			$cdquery = "UPDATE {$dbprefix}answers SET sortorder='$newreplacesortorder' WHERE qid=$qid AND sortorder='PEND'";
			$cdresult=mysql_query($cdquery) or die(mysql_error());
			break;
		default:
			break;
		}
	}

elseif ($action == "insertnewsurvey")
	{
	if ($_POST['url'] == "http://") {$_POST['url']="";}
	if (!$_POST['short_title'])
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_NEWSURVEY_TITLE."\")\n //-->\n</script>\n";
		}
	else
		{
		if (get_magic_quotes_gpc()=="0")
			{
			$_POST['short_title'] = addcslashes($_POST['short_title'], "'");
			$_POST['description'] = addcslashes($_POST['description'], "'");
			$_POST['welcome'] = addcslashes($_POST['welcome'], "'");
			$_POST['attribute1'] = addcslashes($_POST['attribute1'], "'");
			$_POST['attribute2'] = addcslashes($_POST['attribute2'], "'");
			$_POST['email_invite_subj'] = addcslashes($_POST['email_invite_subj'], "'");
			$_POST['email_invite'] = addcslashes($_POST['email_invite'], "'");
			$_POST['email_remind_subj'] = addcslashes($_POST['email_remind_subj'], "'");
			$_POST['email_remind'] = addcslashes($_POST['email_remind'], "'");
			$_POST['email_register_subj'] = addcslashes($_POST['email_register_subj'], "'");
			$_POST['email_register'] = addcslashes($_POST['email_register'], "'");
			$_POST['email_confirm_subj'] = addcslashes($_POST['email_confirm_subj'], "'");
			$_POST['email_confirm'] = addcslashes($_POST['email_confirm'], "'");
			}
		$isquery = "INSERT INTO {$dbprefix}surveys\n"
				  . "(sid, short_title, description, admin, active, welcome, expires, "
				  . "adminemail, private, faxto, format, template, url, urldescrip, "
				  . "language, datestamp, usecookie, notification, allowregister, attribute1, attribute2, "
				  . "email_invite_subj, email_invite, email_remind_subj, email_remind, "
				  . "email_register_subj, email_register, email_confirm_subj, email_confirm, "
				  . "allowsave, autoredirect, allowprev)\n"
				  . "VALUES ('', '{$_POST['short_title']}', '{$_POST['description']}',\n"
				  . "'{$_POST['admin']}', 'N', '".str_replace("\n", "<br />", $_POST['welcome'])."',\n"
				  . "'{$_POST['expires']}', '{$_POST['adminemail']}', '{$_POST['private']}',\n"
				  . "'{$_POST['faxto']}', '{$_POST['format']}', '{$_POST['template']}', '{$_POST['url']}',\n"
				  . "'{$_POST['urldescrip']}', '{$_POST['language']}', '{$_POST['datestamp']}',\n"
				  . "'{$_POST['usecookie']}', '{$_POST['notification']}', '{$_POST['allowregister']}',\n"
				  . "'{$_POST['attribute1']}', '{$_POST['attribute2']}', '{$_POST['email_invite_subj']}',\n"
				  . "'{$_POST['email_invite']}', '{$_POST['email_remind_subj']}',\n"
				  . "'{$_POST['email_remind']}', '{$_POST['email_register_subj']}',\n"
				  . "'{$_POST['email_register']}', '{$_POST['email_confirm_subj']}',\n"
				  . "'{$_POST['email_confirm']}', \n"
				  . "'{$_POST['allowsave']}', '{$_POST['autoredirect']}', '{$_POST['allowprev']}')";
		$isresult = mysql_query ($isquery);
		if ($isresult)
			{
			$isquery = "SELECT sid FROM {$dbprefix}surveys WHERE short_title like '{$_POST['short_title']}'";
			$isquery .= " AND description like '{$_POST['description']}' AND admin like '{$_POST['admin']}'";
			$isresult = mysql_query($isquery);
			while ($isr = mysql_fetch_array($isresult)) {$sid = $isr['sid'];}
			$surveyidselect = getsurveylist();
			}
		else
			{
			$errormsg=_DB_FAIL_NEWSURVEY." - ".mysql_error();
			echo "<script type=\"text/javascript\">\n<!--\n alert(\"$errormsg\")\n //-->\n</script>\n";
			echo $isquery;
			}
		}
	}

elseif ($action == "updatesurvey")
	{
	if ($_POST['url'] == "http://") {$_POST['url']="";}
	if (get_magic_quotes_gpc() == "0")
		{
		$_POST['short_title'] = addcslashes($_POST['short_title'], "'");
		$_POST['description'] = addcslashes($_POST['description'], "'");
		$_POST['welcome'] = addcslashes($_POST['welcome'], "'");
		$_POST['attribute1'] = addcslashes($_POST['attribute1'], "'");
		$_POST['attribute2'] = addcslashes($_POST['attribute2'], "'");
		$_POST['email_invite_subj'] = addcslashes($_POST['email_invite_subj'], "'");
		$_POST['email_invite'] = addcslashes($_POST['email_invite'], "'");
		$_POST['email_remind_subj'] = addcslashes($_POST['email_remind_subj'], "'");
		$_POST['email_remind'] = addcslashes($_POST['email_remind'], "'");
		$_POST['email_register_subj'] = addcslashes($_POST['email_register_subj'], "'");
		$_POST['email_register'] = addcslashes($_POST['email_register'], "'");
		$_POST['email_confirm_subj'] = addcslashes($_POST['email_confirm_subj'], "'");
		$_POST['email_confirm'] = addcslashes($_POST['email_confirm'], "'");
		}
	$usquery = "UPDATE {$dbprefix}surveys \n"
			  . "SET short_title='{$_POST['short_title']}', description='{$_POST['description']}',\n"
			  . "admin='{$_POST['admin']}', welcome='".str_replace("\n", "<br />", $_POST['welcome'])."',\n"
			  . "expires='{$_POST['expires']}', adminemail='{$_POST['adminemail']}',\n"
			  . "private='{$_POST['private']}', faxto='{$_POST['faxto']}',\n"
			  . "format='{$_POST['format']}', template='{$_POST['template']}',\n"
			  . "url='{$_POST['url']}', urldescrip='{$_POST['urldescrip']}',\n"
			  . "language='{$_POST['language']}', datestamp='{$_POST['datestamp']}',\n"
			  . "usecookie='{$_POST['usecookie']}', notification='{$_POST['notification']}',\n"
			  . "allowregister='{$_POST['allowregister']}', attribute1='{$_POST['attribute1']}',\n"
			  . "attribute2='{$_POST['attribute2']}', email_invite_subj='{$_POST['email_invite_subj']}',\n"
			  . "email_invite='{$_POST['email_invite']}', email_remind_subj='{$_POST['email_remind_subj']}',\n"
			  . "email_remind='{$_POST['email_remind']}', email_register_subj='{$_POST['email_register_subj']}',\n"
			  . "email_register='{$_POST['email_register']}', email_confirm_subj='{$_POST['email_confirm_subj']}',\n"
			  . "email_confirm='{$_POST['email_confirm']}', allowsave='{$_POST['allowsave']}',\n"
			  . "autoredirect='{$_POST['autoredirect']}', allowprev='{$_POST['allowprev']}'\n"
			  . "WHERE sid={$_POST['sid']}";
	$usresult = mysql_query($usquery) or die("Error updating<br />$usquery<br /><br /><b>".mysql_error());
	if ($usresult)
		{
		$surveyidselect = getsurveylist();
		}
	else
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_SURVEYUPDATE."\n".mysql_error() ." ($usquery)\")\n //-->\n</script>\n";
		}
	}

elseif ($action == "delsurvey") //can only happen if there are no groups, no questions, no answers etc.
	{
	$query = "DELETE FROM {$dbprefix}surveys WHERE sid=$sid";
	$result = mysql_query($query);
	if ($result)
		{
		$sid = "";
		$surveyidselect = getsurveylist();
		}
	else
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\"Survey id($sid) was NOT DELETED!\n$error\")\n //-->\n</script>\n";
		}
	}

else
	{
	echo "$action Not Yet Available!";
	}

?>