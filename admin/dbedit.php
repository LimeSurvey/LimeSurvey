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
if (call_user_func($auth_function)) {
	switch($dbaction){
		case "addlabelset":
			$lid=addLabel($dbprefix);
			break;
		case "addattribute": 
			addAttribute($qid, $dbprefix);
			break;
		case "editattribute":
			updateAttribute($qid, $dbprefix);
			break;
		case "deleteattribute":
			delAttribute($qid, $dbprefix);
			break;
		case "deleteassessment":
			delAssessment($surveyid, $dbprefix);
			break;
		case "addassessment":
			addAssessment($surveyid, $dbprefix);
			break;
		case "editassessment":
			updateAssessment($surveyid, $dbprefix);
			break;
		case "addanswer":
			addAnswer($qid, $dbprefix);
			break;
		case "deleteanswer":
			delAnswer($qid, $dbprefix);
			break;
		case "updateanswers":
			updateAnswer($qid, $dbprefix);
			break;
		case "moveanswer":
			moveAnswer($qid, $dbprefix);		
			break;
		case "editsurvey":
		case "addsurvey":
			$surveyid=editSurvey($surveyid, $dbprefix, $dbaction);
			break;
		case "editgroup":
		case "addgroup":
			$gid=editGroup($surveyid, $gid, $dbprefix, $dbaction);
			break;
		case "editquestion":
		case "addquestion":
			$qid=editQuestion($surveyid, $gid, $qid, $dbprefix, $dbaction);
			break;
		case "renumbergroup":
		case "renumbersurvey":
			renumber($surveyid, $gid, $dbprefix);
			break;
	} // switch
}

function renumber($surveyid, $gid=null, $dbprefix) {
	$question_number=1;
	$gselect="SELECT *
			  FROM {$dbprefix}questions, {$dbprefix}groups
			  WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid
			  AND {$dbprefix}questions.sid=$surveyid\n";
	if (!empty($gid)) {
	    $gselect .= "AND {$dbprefix}questions.gid=$gid\n";
	}
	$gselect .= "ORDER BY group_name, title";
	$gresult=mysql_query($gselect) or die (mysql_error());
	$grows = array(); //Create an empty array in case mysql_fetch_array does not return any rows
	while ($grow = mysql_fetch_array($gresult)) {$grows[] = $grow;} // Get table output into array
	usort($grows, 'CompareGroupThenTitle');
	$count=count($grows);
	$len=strlen($count);
	foreach($grows as $grow) {
		$sortednumber=sprintf("%0{$len}d", $question_number);
		$usql="UPDATE {$dbprefix}questions\n"
			."SET title='".$sortednumber."'\n"
			."WHERE qid=".$grow['qid'];
		//echo "[$usql]";
		$uresult=mysql_query($usql) or die("Error:".mysql_error());
		$question_number++;
	}
}

function addLabel($dbprefix) {
	$query = "INSERT INTO {$dbprefix}labelsets
			  (`label_name`)
			  VALUES ('".auto_escape($_POST['label_name'])."')";
	$result=mysql_query($query);
	return mysql_insert_id();
}

function editSurvey($surveyid, $dbprefix, $dbaction) {
	$tablefields=array("short_title",
					   "description",
					   "admin",
					   "active",
					   "welcome",
					   "expires",
					   "adminemail",
					   "private",
					   "faxto",
					   "format",
					   "template",
					   "url",
					   "urldescrip",
					   "language",
					   "datestamp",
					   "usecookie",
					   "notification",
					   "allowregister",
					   "attribute1",
					   "attribute2",
					   "email_invite_subj",
					   "email_invite",
					   "email_remind_subj",
					   "email_remind",
					   "email_register_subj",
					   "email_register",
					   "email_confirm_subj",
					   "email_confirm",
					   "allowsave",
					   "autonumber_start",
					   "autoredirect",
					   "allowprev");
	switch ($dbaction) {
		case "editsurvey":
			$query = "UPDATE {$dbprefix}surveys
					  SET ";
			foreach ($tablefields as $tf) {
				if (isset($_POST[$tf])) {
					$querys[] = "$tf = '".auto_escape($_POST[$tf])."'";
				}
			}
			$query .= implode(",\n", $querys);
			$query .= "\nWHERE sid=$surveyid";
			$result = mysql_query($query);
			break;
		case "addsurvey":
			$query = "INSERT INTO {$dbprefix}surveys\n";
			foreach ($tablefields as $tf) {
				if (isset($_POST[$tf])) {
				    $fields[]=$tf;
					$values[]=mysql_escape_string($_POST[$tf]);
				}
			}
			$query .= "(".implode(",\n", $fields).")";
			$query .= "\nVALUES ('";
			$query .= implode("',\n'", $values)."')";
			if ($result = mysql_query($query)) {
				$surveyid = mysql_insert_id();
			} else {
				echo $query."<br />".mysql_error();
			}
			break;
	}
	return $surveyid;
}

function editGroup($surveyid, $gid, $dbprefix, $dbaction) {
	$tablefields=array("sid",
					   "group_name",
					   "description");
	switch($dbaction) {
		case "editgroup":
			$query = "UPDATE {$dbprefix}groups
					  SET ";
			foreach($tablefields as $tf) {
				if (isset($_POST[$tf])) {
				    $querys[]="$tf = '".auto_escape($_POST[$tf])."'";
				}
			}
			$query .= implode(",\n", $querys);
			$query .= "\nWHERE gid=$gid";
			$result = mysql_query($query);
			
			break;
		case "addgroup":
			$query = "INSERT INTO {$dbprefix}groups\n";
			foreach ($tablefields as $tf) {
				if (isset($_POST[$tf])) {
				    $fields[]=$tf;
					$values[]=auto_escape($_POST[$tf]);
				}
			}
			$query .= "(".implode(",\n", $fields).")";
			$query .= "\nVALUES ('";
			$query .= implode("',\n'", $values)."')";
//			echo $query;
			if ($result = mysql_query($query)) {
				$gid = mysql_insert_id();
			} else {
				echo $query."<br />".mysql_error();
			}			
			break;
	}
	return $gid;
}

function editQuestion($surveyid, $gid, $qid, $dbprefix, $dbaction) {
	$tablefields=array("sid",
					   "gid",
					   "type",
					   "title",
					   "question",
					   "help",
					   "other",
					   "mandatory",
					   "lid",
					   "preg");
	switch($dbaction) {
		case "editquestion":
			$query = "UPDATE {$dbprefix}questions
					  SET ";
			foreach($tablefields as $tf) {
				if (isset($_POST[$tf])) {
				    $querys[]="$tf = '".auto_escape($_POST[$tf])."'";
				}
			}
			$query .= implode(",\n", $querys);
			$query .= "\nWHERE qid=$qid";
//			echo $query;
			$result = mysql_query($query);
			
			break;
		case "addquestion":
			$query = "INSERT INTO {$dbprefix}questions\n";
			foreach ($tablefields as $tf) {
				if (isset($_POST[$tf])) {
				    $fields[]=$tf;
					$values[]=auto_escape($_POST[$tf]);
				}
			}
			$query .= "(".implode(",\n", $fields).")";
			$query .= "\nVALUES ('";
			$query .= implode("',\n'", $values)."')";
//			echo $query;
			if ($result = mysql_query($query)) {
					$qid = mysql_insert_id();
				} else {
					echo $query."<br />".mysql_error();
				}
			break;
	}
	return $qid;
}

function addAttribute($qid, $dbprefix) {
	$query = "INSERT INTO {$dbprefix}question_attributes
			  (qid, attribute, value)
			  VALUES ($qid,
			  '".$_POST['attribute']."',
			  '".$_POST['value']."')";
	$result = mysql_query($query);
}

function delAttribute($qid, $dbprefix) {
	$query = "DELETE FROM {$dbprefix}question_attributes
			  WHERE qid=$qid
			  AND qaid=".$_GET['qaid'];
	$result = mysql_query($query);
}

function updateAttribute($qid, $dbprefix) {
	$query = "UPDATE {$dbprefix}question_attributes
			  SET value='".$_POST['value']."'
			  WHERE qaid=".$_POST['qaid'];
	$result = mysql_query($query);
}

function addAnswer($qid, $dbprefix) {
	$where=array("qid"=>$qid,
				   "code"=>$_POST['code']);
	
	if (matchExists("{$dbprefix}answers", $where) !== true) {
		$query = "INSERT INTO {$dbprefix}answers
				 (qid, code, answer, default_value, sortorder)
				 VALUES ($qid,
				 '".$_POST['code']."',
				 '".mysql_escape_string($_POST['answer'])."',
				 '".$_POST['default_value']."',
				 '".sprintf("%05d", $_POST['sortorder'])."')";
		$result = mysql_query($query);
	} else {
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_NEWANSWERDUPLICATE."\")\n //-->\n</script>\n";
	}
}

function delAssessment($surveyid, $dbprefix) {
	$query = "DELETE FROM {$dbprefix}assessments
			  WHERE id=".$_POST['id']."
			  AND sid=$surveyid";
	$result = mysql_query($query);
}

function updateAssessment($surveyid, $dbprefix) {
	$query = "UPDATE {$dbprefix}assessments
			  SET scope='".$_POST['scope']."',
			  gid=".$_POST['assessment_gid'].",
			  name='".auto_escape($_POST['name'])."',
			  minimum='".$_POST['minimum']."',
			  maximum='".$_POST['maximum']."',
			  message='".auto_escape($_POST['message'])."',
			  link='".auto_escape($_POST['link'])."'
			  WHERE id=".$_POST['id'];
	$result=mysql_query($query);
}

function addAssessment($surveyid, $dbprefix) {
	$query = "INSERT INTO {$dbprefix}assessments
			  (sid, scope, gid, name, minimum, maximum, message, link)
			  VALUES
			  ($surveyid,
			  '".$_POST['scope']."',
			  ".$_POST['assessment_gid'].",
			  '".auto_escape($_POST['name'])."',
			  '".$_POST['minimum']."',
			  '".$_POST['maximum']."',
			  '".auto_escape($_POST['message'])."',
			  '".auto_escape($_POST['link'])."')";
	$result = mysql_query($query);
}

function delAnswer($qid, $dbprefix) {
	$query = "DELETE FROM {$dbprefix}answers
			  WHERE qid=$qid
			  AND code='".returnglobal('code')."'";
	$result = mysql_query($query);
}

function updateAnswer($qid, $dbprefix) {
	//echo "Hi";
	//echo "<pre>";print_r($_POST); echo "</pre>";
	//FIRST: Renumber all the existing answers (we can fix if a problem occurs)
	$query = "UPDATE {$dbprefix}answers SET qid=99999999 WHERE qid=$qid";
	$result = mysql_query($query) or die(mysql_error());
	foreach($_POST['code'] as $key=>$code) {
		$where=array("qid"=>$qid,
					 "code"=>$code);
		if (matchExists("{$dbprefix}answers", $where) !== true) {
			$insert="INSERT INTO {$dbprefix}answers
					 (qid, code, answer, default_value, sortorder)
					 VALUES
					 ($qid, '$code', '".auto_escape($_POST['answer'][$key])."', '".$_POST['default_value'][$key]."', '".$_POST['sortorder'][$key]."')";
			$result = mysql_query($insert) or die(mysql_error());
		} else {
			$query = "DELETE FROM {$dbprefix}answers WHERE qid=$qid";
			$result = mysql_query($query) or die(mysql_error());
			$query = "UPDATE {$dbprefix}answers SET qid=$qid WHERE qid=99999999";
			$result = mysql_query($query) or die(mysql_error());
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._DB_FAIL_ANSWERUPDATEDUPLICATE."\")\n //-->\n</script>\n";
		}
	}
	$query = "DELETE FROM {$dbprefix}answers WHERE qid=99999999";
	$result = mysql_query($query) or die(mysql_error());
}

function moveAnswer($qid, $dbprefix) {
	$newsortorder=sprintf("%05d", $_GET['sortorder']+$_GET['moveorder']);
	$query = "UPDATE {$dbprefix}answers
			  SET sortorder='PEND'
			  WHERE qid=$qid
			  AND sortorder='$newsortorder'";
	$result = mysql_query($query);
	$query = "UPDATE {$dbprefix}answers
			   SET sortorder='$newsortorder'
			   WHERE qid=$qid
			   AND sortorder='".$_GET['sortorder']."'";
	$result = mysql_query($query);
	$query = "UPDATE {$dbprefix}answers
			  SET sortorder='".$_GET['sortorder']."'
			  WHERE qid=$qid
			  AND sortorder='PEND'";
	$result = mysql_query($query);
}

function matchExists($table, $where) {
	//This function will return true if a duplicate entry is found
	//and false if one is not
	//$table = tablename
	//$duplicates = keyed array containing "field"=>"value" where duplicates are being searched for
	//$where = keyed array containing "field"=>"value" for the other conditions of the search
	$query = "SELECT * FROM $table
			  WHERE ";
	foreach ($where as $key=>$val) {
		$wheres[]= "$key = '$val'";
	}
	$query .= implode("\nAND ", $wheres);
	$result = mysql_query($query);
	if (mysql_num_rows($result) > 0) {
	    return true;
	} else {
		return false;
	}
}

?>