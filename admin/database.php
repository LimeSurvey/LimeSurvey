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
if ($action == "insertnewgroup")
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$description = addcslashes($description, "'");
		$group_name = addcslashes($group_name, "'");
		}

	$query = "INSERT INTO groups (sid, group_name, description) VALUES ('$sid', '$group_name', '$description')";
	$result = mysql_query($query);
	
	if ($result)
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"New group ($group_name) has been created for survey id $sid\")\n //-->\n</script>\n";
		$query = "SELECT gid FROM groups WHERE group_name='$group_name' AND sid=$sid";
		$result = mysql_query($query);
		while ($res = mysql_fetch_row($result))
			{
			$gid = $res['gid'];
			}
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

elseif ($action == "updategroup")
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$description = addcslashes($description, "'");
		$group_name = addcslashes($group_name, "'");
		}

	$ugquery = "UPDATE groups SET group_name='$group_name', description='$description' WHERE sid=$sid AND gid=$gid";
	$ugresult = mysql_query($ugquery);
	if ($ugresult)
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Your Group ($group_name) has been updated!\")\n //-->\n</script>\n";
		$groupsummary = getgrouplist($gid);
		}
	else
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Your group could not be updated!\")\n //-->\n</SCRIPT>\n";
		}

	}

elseif ($action == "delgroup")
	{
	$query = "DELETE FROM groups WHERE sid=$sid AND gid=$gid";
	$result = mysql_query($query);
	if ($result)
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Group id($gid) for survey $sid has been deleted!\")\n //-->\n</script>\n";
		$gid = "";
		$groupselect = getgrouplist($gid);
		}
	else
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Group id($gid) for survey $sid was NOT DELETED!\n$error\")\n //-->\n</script>\n";
		}
	}

elseif ($action == "insertnewquestion")
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$title = addcslashes($title, "'");
		$question = addcslashes($question, "'");
		$help = addcslashes($help, "'");
		}
	$query = "INSERT INTO questions (qid, sid, gid, type, title, question, help, other) VALUES ('', '$sid', '$gid', '$type', '$title', '$question', '$help', '$other')";
	$result = mysql_query($query);
	if ($result)
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"New question ($title) has been created for survey id $sid, group id $gid\")\n //-->\n</script>\n";
		}
	
	}	

elseif ($action == "updatequestion")
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$title = addcslashes($title, "'");
		$question = addcslashes($question, "'");
		$help = addcslashes($help, "'");
		}
	$uqquery = "UPDATE questions SET type='$type', title='$title', question='$question', help='$help', gid='$gid', other='$other' WHERE sid=$sid AND qid=$qid";
	//echo $uqquery;
	$uqresult = mysql_query($uqquery);
	if ($uqresult)
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Your Question ($title) has been updated!\")\n //-->\n</script>\n";
		}
	else
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Your question could not be updated!\")\n //-->\n</SCRIPT>\n";
		}
	}

elseif ($action == "copynewquestion")
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$title = addcslashes($title, "'");
		$question = addcslashes($question, "'");
		$help = addcslashes($help, "'");
		}
	$query = "INSERT INTO questions (qid, sid, gid, type, title, question, help, other) VALUES ('', '$sid', '$gid', '$type', '$title', '$question', '$help', '$other')";
	$result = mysql_query($query);
	if ($result)
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"New question ($title) has been created for survey id $sid, group id $gid\")\n //-->\n</script>\n";
		}
	//echo "COPYANSWERS: $copyanswers, OLDQID: $oldqid";
	if ($copyanswers == "Y")
		{
		$q2 = "SELECT qid FROM questions ORDER BY qid DESC LIMIT 1";
		$r2 = mysql_query($q2);
		while ($qr2 = mysql_fetch_row($r2)) {$newqid = $qr2['qid'];}
		$q1 = "SELECT * FROM answers WHERE qid='$oldqid' ORDER BY code";
		$r1 = mysql_query($q1);
		while ($qr1 = mysql_fetch_row($r1))
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
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Question id($qid) has been deleted!\")\n //-->\n</script>\n";
		$qid = "";
		}
	else
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Question id($sid) was NOT DELETED!\n$error\")\n //-->\n</script>\n";
		}
	}

elseif ($action == "insertnewanswer")
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$answer = addcslashes($answer, "'");
		}
	$iaquery = "INSERT INTO answers (qid, code, answer, `default`) VALUES ('$qid', '$code', '$answer', '$default')";
	$iaresult = mysql_query ($iaquery);
	if ($iaresult)
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Your New Answer has been added!\")\n //-->\n</script>\n";
		$surveyselect = getsurveylist();
		}
	else
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Your survey could not be created!\")\n //-->\n</SCRIPT>\n";
		}
	
	}

elseif ($action == "updateanswer")
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$answer = addcslashes($answer, "'");
		}
	$uaquery = "UPDATE answers SET code='$code', answer='$answer', `default`='$default' WHERE qid=$qid AND code='$old_code'";
	//echo $uaquery;
	$uaresult = mysql_query($uaquery);
	if ($uaresult)
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Your Answer ($qid, $code) has been updated!\")\n //-->\n</script>\n";
		}
	else
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Your answer could not be updated!\")\n //-->\n</SCRIPT>\n";
		}

	}

elseif ($action == "delanswer")
	{
	$query = "DELETE FROM answers WHERE qid=$qid AND code='$code'";
	$result = mysql_query($query);
	if ($result)
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Answer for question $qid ($code) has been deleted!\")\n //-->\n</script>\n";
		$code = "";
		}
	else
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Answer for question $qid was NOT DELETED!\n$error\")\n //-->\n</script>\n";
		}
	}

elseif ($action == "insertnewsurvey")
	{
	if (get_magic_quotes_gpc()=="0")
		{
		$short_title = addcslashes($short_title, "'");
		$description = addcslashes($description, "'");
		}
	$isquery = "INSERT INTO surveys (sid, short_title, description, admin, active, welcome, expires, adminemail) VALUES ('', '$short_title', '$description', '$admin', 'N', '".str_replace("\n", "<BR>", $welcome)."', '$expires', '$adminemail')";
	$isresult = mysql_query ($isquery);
	if ($isresult)
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Your Survey ($short_title) has been created!\")\n //-->\n</script>\n";
		$isquery = "SELECT sid FROM surveys WHERE short_title like '$short_title' AND description like '$description' AND admin like '$admin'";
		$isresult = mysql_query($isquery);
		while ($isr = mysql_fetch_row($isresult))
			{
			$sid=$isr[0];
			}
		$surveyselect = getsurveylist();
		}
	else
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Your survey could not be created!\")\n //-->\n</SCRIPT>\n";
		}
	}

elseif ($action == "updatesurvey")
	{
	if (get_magic_quotes_gpc() == "0")
		{
		$short_title = addcslashes($short_title, "'");
		$description = addcslashes($description, "'");
		}
	$usquery = "UPDATE surveys SET short_title='$short_title', description='$description',";
	$usquery .= " admin='$admin', welcome='".str_replace("\n", "<BR>", $welcome)."',";
	$usquery .= " expires='$expires', adminemail='$adminemail'";
	$usquery .= " WHERE sid=$sid";
	$usresult = mysql_query($usquery);
	if ($usresult)
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Your Survey ($short_title) has been updated!\")\n //-->\n</script>\n";
		$surveyselect = getsurveylist();
		}
	else
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Your survey could not be updated!\")\n //-->\n</SCRIPT>\n";
		}
	}

elseif ($action == "delsurvey")
	{
	$query = "DELETE FROM surveys WHERE sid=$sid";
	$result = mysql_query($query);
	if ($result)
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Survey id($sid) has been deleted!\")\n //-->\n</script>\n";
		$sid = "";
		$surveyselect = getsurveylist();
		}
	else
		{
		echo "<SCRIPT TYPE=\"text/javascript\">\n<!--\n alert(\"Survey id($sid) was NOT DELETED!\n$error\")\n //-->\n</script>\n";
		}
	}

elseif ($action == "adduser")
	{
	exec("$homedir\htpasswd.exe -b .htpasswd $user $pass"); 
	}	

else
	{
	echo "$action Not Yet Available!";
	}

?>