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
if ($_POST['action']) {$action = $_POST['action'];}
if ($_GET['action']) {$action = $_GET['action'];}

if ($action == "insertnewgroup")
	{
	if (!$_POST['group_name'])
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your group could not be added! It did not include a group name (which is required)\")\n //-->\n</script>\n";		
		}
	else
		{
		if (get_magic_quotes_gpc() == "0")
			{
			$_POST['description'] = addcslashes($_POST['description'], "'");
			$_POST['group_name'] = addcslashes($_POST['group_name'], "'");
			}

		$query = "INSERT INTO groups (sid, group_name, description) VALUES ('{$_POST['sid']}', '{$_POST['group_name']}', '{$_POST['description']}')";
		$result = mysql_query($query);
		
		if ($result)
			{
			//echo "<script type=\"text/javascript\">\n<!--\n alert(\"New group ($group_name) has been created for survey id $sid\")\n //-->\n</script>\n";
			$query = "SELECT gid FROM groups WHERE group_name='$group_name' AND sid={$_POST['sid']}";
			$result = mysql_query($query);
			while ($res = mysql_fetch_array($result)) {$gid = $res['gid'];}
			$groupselect = getgrouplist($gid);
			}
		else
			{
			echo "The database reported the following error:<br />\n";
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

	$ugquery = "UPDATE groups SET group_name='{$_POST['group_name']}', description='{$_POST['description']}' WHERE sid={$_POST['sid']} AND gid={$_POST['gid']}";
	$ugresult = mysql_query($ugquery);
	if ($ugresult)
		{
		//echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your Group ($group_name) has been updated!\")\n //-->\n</script>\n";
		$groupsummary = getgrouplist($_POST['gid']);
		}
	else
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your group could not be updated!\")\n //-->\n</script>\n";
		}

	}

elseif ($action == "delgroup")
	{
	$query = "DELETE FROM groups WHERE sid=$sid AND gid=$gid";
	$result = mysql_query($query);
	if ($result)
		{
		//echo "<script type=\"text/javascript\">\n<!--\n alert(\"Group id($gid) for survey $sid has been deleted!\")\n //-->\n</script>\n";
		$gid = "";
		$groupselect = getgrouplist($gid);
		}
	else
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\"Group id($gid) for survey $sid was NOT DELETED!\n$error\")\n //-->\n</script>\n";
		}
	}

elseif ($action == "insertnewquestion")
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$_POST['title'] = addcslashes($_POST['title'], "'");
		$_POST['question'] = addcslashes($_POST['question'], "'");
		$_POST['help'] = addcslashes($_POST['help'], "'");
		}
	$query = "INSERT INTO questions (qid, sid, gid, type, title, question, help, other, mandatory)"
			." VALUES ('', '{$_POST['sid']}', '{$_POST['gid']}', '{$_POST['type']}', '{$_POST['title']}',"
			." '{$_POST['question']}', '{$_POST['help']}', '{$_POST['other']}', '{$_POST['mandatory']}')";
	$result = mysql_query($query);
	if ($result)
		{
		//echo "<script type=\"text/javascript\">\n<!--\n alert(\"New question ($title) has been created for survey id $sid, group id $gid\")\n //-->\n</script>\n";
		}
	
	}	

elseif ($action == "updatequestion")
	{
	$cqquery = "SELECT type FROM questions WHERE qid={$_POST['qid']}";
	$cqresult=mysql_query($cqquery) or die ("Couldn't get question type to check for change<br />$cqquery<br />".mysql_error());
	while ($cqr=mysql_fetch_array($cqresult)) {$oldtype=$cqr['type'];}
	if ($oldtype != $_POST['type'])
		{
		//Make sure there are no conditions based on this question, since we are changing the type
		$ccquery = "SELECT * FROM conditions WHERE cqid={$_POST['qid']}";
		$ccresult = mysql_query($ccquery) or die ("Couldn't get list of cqids for this question<br />$ccquery<br />".mysql_error());
		$cccount=mysql_num_rows($ccresult);
		while ($ccr=mysql_fetch_array($ccresult)) {$qidarray[]=$ccr['qid'];}
		if ($qidarray) {$qidlist=implode(", ", $qidarray);}
		}
	if (get_magic_quotes_gpc() == "0")
		{
		$_POST['title'] = addcslashes($_POST['title'], "'");
		$_POST['question'] = addcslashes($_POST['question'], "'");
		$_POST['help'] = addcslashes($_POST['help'], "'");
		}
	if ($cccount)
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your question could not be updated! Other questions (qid $qidlist) are conditional based on the responses to this question and changing the type will cause problems. You must remove any conditions based on this question before changing this question type.\")\n //-->\n</script>\n";
		}
	else
		{
		$uqquery = "UPDATE questions SET type='{$_POST['type']}', title='{$_POST['title']}', "
				. "question='{$_POST['question']}', help='{$_POST['help']}', gid='{$_POST['gid']}', "
				. "other='{$_POST['other']}', mandatory='{$_POST['mandatory']}' "
				. "WHERE sid={$_POST['sid']} AND qid={$_POST['qid']}";
		//echo $uqquery;
		$uqresult = mysql_query($uqquery);
		if ($uqresult)
			{
			}
		else
			{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your question could not be updated!\")\n //-->\n</script>\n";
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
	$query = "INSERT INTO questions (qid, sid, gid, type, title, question, help, other, mandatory) VALUES ('', '{$_POST['sid']}', '{$_POST['gid']}', '{$_POST['type']}', '{$_POST['title']}', '{$_POST['question']}', '{$_POST['help']}', '{$_POST['other']}', '{$_POST['mandatory']}')";
	$result = mysql_query($query);
	if ($result)
		{
		//echo "<script type=\"text/javascript\">\n<!--\n alert(\"New question ($title) has been created for survey id $sid, group id $gid\")\n //-->\n</script>\n";
		}
	//echo "COPYANSWERS: $copyanswers, OLDQID: $oldqid";
	if ($copyanswers == "Y")
		{
		$q2 = "SELECT qid FROM questions ORDER BY qid DESC LIMIT 1";
		$r2 = mysql_query($q2);
		while ($qr2 = mysql_fetch_array($r2)) {$newqid = $qr2['qid'];}
		$q1 = "SELECT * FROM answers WHERE qid='$oldqid' ORDER BY code";
		$r1 = mysql_query($q1);
		while ($qr1 = mysql_fetch_array($r1))
			{
			$i1 = "INSERT INTO answers (qid, code, answer, `default`) VALUES ('$newqid', '{$qr1['code']}', '{$qr1['answer']}', '{$qr1['default']}')";
			$ir1 = mysql_query($i1);
			}		
		}	
	}	

elseif ($action == "delquestion")
	{
	$query = "DELETE FROM questions WHERE qid=$qid";
	$result = mysql_query($query);
	if ($result)
		{
		//echo "<script type=\"text/javascript\">\n<!--\n alert(\"Question id($qid) has been deleted!\")\n //-->\n</script>\n";
		$qid = "";
		}
	else
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\"Question id($sid) was NOT DELETED!\n$error\")\n //-->\n</script>\n";
		}
	}

elseif ($action == "insertnewanswer")
	{
	if (!$_POST['code'] || !$_POST['answer'])
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your answer could not be created! It does not contain both an answer code and answer text (which are required).\")\n //-->\n</script>\n";
		}
	else
		{
		$iaquery="SELECT * FROM answers WHERE qid={$_POST['qid']} AND code='{$_POST['code']}'";
		$iaresult=mysql_query($iaquery) or die ("Error checking for answers with the same code<br />$iaquery<br />".mysql_error());
		$matchcount=mysql_num_rows($iaresult);
		if ($matchcount)
			{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your answer could not be created! There is already an answer to this question which has the same code. Your codes should be unique for each answer.\")\n //-->\n</script>\n";
			}
		else
			{
			if (get_magic_quotes_gpc() == "0")
				{
				$_POST['answer'] = addcslashes($_POST['answer'], "'");
				}
			$iaquery = "INSERT INTO answers (qid, code, answer, `default`) VALUES ('{$_POST['qid']}', '{$_POST['code']}', '{$_POST['answer']}', '{$_POST['default']}')";
			$iaresult = mysql_query ($iaquery);
			if ($iaresult)
				{
				//echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your New Answer has been added!\")\n //-->\n</script>\n";
				$surveyselect = getsurveylist();
				}
			else
				{
				echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your answer could not be created!\")\n //-->\n</script>\n";
				}
			}
		}	
	}

elseif ($action == "updateanswer")
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$_POST['answer'] = addcslashes($_POST['answer'], "'");
		}
	if ($_POST['code'] != $_POST['old_code'])
		{
		$uaquery = "SELECT * FROM answers WHERE code = '{$_POST['code']}' AND qid={$_POST['qid']}";
		$uaresult = mysql_query($uaquery) or die ("Cannot check for duplicate codes<br />$uaquery<br />".mysql_error());
		$matchcount = mysql_num_rows($uaresult);
		}
	if ($matchcount)
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your answer could not be updated! There is another answer to this question with the same code. All codes should be unique.\")\n //-->\n</script>\n";
		}
	else
		{
		$uaquery = "UPDATE answers SET code='{$_POST['code']}', answer='{$_POST['answer']}', `default`='{$_POST['default']}' WHERE qid={$_POST['qid']} AND code='{$_POST['old_code']}'";
		//echo $uaquery;
		$uaresult = mysql_query($uaquery);
		if ($uaresult)
			{
			//echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your Answer ($qid, $code) has been updated!\")\n //-->\n</script>\n";
			}
		else
			{
			echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your answer could not be updated!\")\n //-->\n</script>\n";
			}
		}
	}

elseif ($action == "delanswer")
	{
	$query = "DELETE FROM answers WHERE qid=$qid AND code='$code'";
	$result = mysql_query($query);
	if ($result)
		{
		//echo "<script type=\"text/javascript\">\n<!--\n alert(\"Answer for question $qid ($code) has been deleted!\")\n //-->\n</script>\n";
		$code = "";
		}
	else
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\"Answer for question $qid was NOT DELETED!\n$error\")\n //-->\n</script>\n";
		}
	}

elseif ($action == "insertnewsurvey")
	{
	if (!$_POST['short_title'])
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your survey could not be created because it did not have a short title.\")\n //-->\n</script>\n";
		}
	else
		{
		if (get_magic_quotes_gpc()=="0")
			{
			$_POST['short_title'] = addcslashes($_POST['short_title'], "'");
			$_POST['description'] = addcslashes($_POST['description'], "'");
			$_POST['welcome'] = addcslashes($_POST['welcome'], "'");
			}
		$isquery = "INSERT INTO surveys (sid, short_title, description, admin, active, welcome, expires,";
		$isquery .= " adminemail, private, faxto, format, template, url, urldescrip) VALUES ('', '{$_POST['short_title']}', '{$_POST['description']}',";
		$isquery .= " '{$_POST['admin']}', 'N', '".str_replace("\n", "<br />", $_POST['welcome'])."',";
		$isquery .= " '{$_POST['expires']}', '{$_POST['adminemail']}', '{$_POST['private']}',";
		$isquery .= " '{$_POST['faxto']}', '{$_POST['format']}', '{$_POST['template']}', '{$_POST['url']}', '{$_POST['urldescrip']}')";
		$isresult = mysql_query ($isquery);
		if ($isresult)
			{
			//echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your Survey ($short_title) has been created!\")\n //-->\n</script>\n";
			$isquery = "SELECT sid FROM surveys WHERE short_title like '{$_POST['short_title']}'";
			$isquery .= " AND description like '{$_POST['description']}' AND admin like '{$_POST['admin']}'";
			$isresult = mysql_query($isquery);
			while ($isr = mysql_fetch_array($isresult)) {$sid = $isr['sid'];}
			$surveyselect = getsurveylist();
			}
		else
			{
			$errormsg="Your survey could not be created - ".mysql_error();
			echo "<script type=\"text/javascript\">\n<!--\n alert(\"$errormsg\")\n //-->\n</script>\n";
			}
		}
	}

elseif ($action == "updatesurvey")
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$_POST['short_title'] = addcslashes($_POST['short_title'], "'");
		$_POST['description'] = addcslashes($_POST['description'], "'");
		$_POST['welcome'] = addcslashes($_POST['welcome'], "'");
		}
	$usquery = "UPDATE surveys SET short_title='{$_POST['short_title']}', description='{$_POST['description']}',";
	$usquery .= " admin='{$_POST['admin']}', welcome='".str_replace("\n", "<br />", $_POST['welcome'])."',";
	$usquery .= " expires='{$_POST['expires']}', adminemail='{$_POST['adminemail']}',";
	$usquery .= " private='{$_POST['private']}', faxto='{$_POST['faxto']}',";
	$usquery .= " format='{$_POST['format']}', template='{$_POST['template']}', ";
	$usquery .= " url='{$_POST['url']}', urldescrip='{$_POST['urldescrip']}'";
	$usquery .= " WHERE sid={$_POST['sid']}";
	$usresult = mysql_query($usquery) or die("Error updating<br />$usquery<br /><br /><b>".mysql_error());
	if ($usresult)
		{
		//echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your Survey ($short_title) has been updated!\")\n //-->\n</script>\n";
		$surveyselect = getsurveylist();
		}
	else
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\"Your survey could not be updated! " . mysql_error() ." ($usquery)\")\n //-->\n</script>\n";
		}
	}

elseif ($action == "delsurvey")
	{
	$query = "DELETE FROM surveys WHERE sid=$sid";
	$result = mysql_query($query);
	if ($result)
		{
		//echo "<script type=\"text/javascript\">\n<!--\n alert(\"Survey id($sid) has been deleted!\")\n //-->\n</script>\n";
		$sid = "";
		$surveyselect = getsurveylist();
		}
	else
		{
		echo "<script type=\"text/javascript\">\n<!--\n alert(\"Survey id($sid) was NOT DELETED!\n$error\")\n //-->\n</script>\n";
		}
	}

elseif ($action == "adduser")
	{
	exec("$homedir\htpasswd.exe -b .htpasswd {$_POST['user']} {$_POST['pass']}"); 
	}	

else
	{
	echo "$action Not Yet Available!";
	}

?>