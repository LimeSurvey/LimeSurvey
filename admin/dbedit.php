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

//Ensure script is not run directly, avoid path disclosure
if (empty($homedir)) {die ("Cannot run this script directly (dbedit.php)");}

if (call_user_func($auth_function)) {
	switch($dbaction){
		case "addlabelset":
		$lid=addLabelset($dbprefix);
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
		case "delsurvey":
		delSurvey($surveyid, $dbprefix);
		break;
		case "delgroup":
		if (delGroup($surveyid, $gid, $dbprefix)) {
			$gid="";
		};
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
		case "delquestion":
		if (delQuestion($surveyid, $qid, $dbprefix)) {
			$qid = "";
		}
		break;
		case "editquestion":
		case "addquestion":
		case "copyquestion":
		$qid=editQuestion($surveyid, $gid, $qid, $dbprefix, $dbaction);
		break;
		case "renumbergroup":
		case "renumbersurvey":
		renumber($surveyid, $gid, $dbprefix);
		break;
		case "editlabel":
		case "addlabel":
		editLabel($lid, $dbprefix, $dbaction);
	} // switch
}

function renumber($surveyid, $gid=null, $dbprefix) {
	global $connect;
	$question_number=1;
	$gselect="SELECT *
			  FROM {$dbprefix}questions, {$dbprefix}groups
			  WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid
			  AND {$dbprefix}questions.sid=$surveyid\n";
	if (!empty($gid)) {
		$gselect .= "AND {$dbprefix}questions.gid=$gid\n";
	}
	$gselect .= "ORDER BY {$dbprefix}groups.sortorder, title";
	$gresult=db_execute_assoc($gselect) or die ($connect->ErrorMsg());
	$grows = array(); //Create an empty array in case FetchRow does not return any rows
	while ($grow = $gresult->FetchRow()) {$grows[] = $grow;} // Get table output into array
	usort($grows, 'CompareGroupThenTitle');
	$count=count($grows);
	$len=strlen($count);
	foreach($grows as $grow) {
		$sortednumber=sprintf("%0{$len}d", $question_number);
		$usql="UPDATE {$dbprefix}questions\n"
		."SET title='".$sortednumber."'\n"
		."WHERE qid=".$grow['qid'];
		//echo "[$usql]";
		$uresult=$connect->Execute($usql) or die("Error:".$connect->ErrorMsg());
		$question_number++;
	}
}

function addLabelset($dbprefix) {
	global $connect;
	$query = "INSERT INTO {$dbprefix}labelsets
			  (`label_name`)
			  VALUES ('".auto_escape($_POST['label_name'])."')";
	$result=$connect->Execute($query);
	return $connect->Insert_ID();
}

function editLabel($lid, $dbprefix, $dbaction) {
	global $connect;
	switch($dbaction) {
		case "addlabel":
		echo "Hi";
		$query = "INSERT INTO {$dbprefix}labels
				  VALUES ('$lid',
				  	      '".auto_escape($_POST['code'])."',
						  '".auto_escape($_POST['title'])."',
						  '".auto_escape($_POST['sortorder'])."')";
		$result = $connect->Execute($query);
		break;
		case "editlabelset":
		break;
	}
}

function editSurvey($surveyid, $dbprefix, $dbaction) {
	global $connect;
	$tablefields=array("short_title",
	"description",
	"admin",
	"active",
	"welcome",
	"useexpiry",
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
	"ipaddr",
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
	"allowprev",
//NEW for multilanguage surveys 
		"additional_languages");
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
		$result = $connect->Execute($query);
		break;
		case "addsurvey":
		$data = array();
		foreach ($tablefields as $tf) {
			if (isset($_POST[$tf]))
			$data[$tf] = $_POST[$tf];
		}
		$query = $connect->GetInsertSQL("{$dbprefix}surveys", $data);
		if ($result = $connect->Execute($query)) {
			$surveyid = $connect->Insert_ID();
		} else {
			echo $query."<br />".$connect->ErrorMsg();
		}
		break;
	}
	return $surveyid;
}

function editGroup($surveyid, $gid, $dbprefix, $dbaction) {
	global $connect;
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
		$result = $connect->Execute($query);

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
		if ($result = $connect->Execute($query)) {
			$gid = $connect->Insert_ID();
		} else {
			echo $query."<br />".$connect->ErrorMsg();
		}
		break;
	}
	return $gid;
}

function delQuestion($surveyid, $qid, $dbprefix) {
	global $databasename;
	global $connect;
	if (!is_numeric($qid)) {
		return FALSE;
	} elseif ($_GET['ok'] == "yes") {
		if (!isActivated($surveyid)) {
			$query = "DELETE FROM {$dbprefix}answers WHERE qid = ".$qid;
			$result = $connect->Execute($query);
			//2: Delete all conditions to questions in this group
			$query = "DELETE FROM {$dbprefix}conditions WHERE qid = ".$qid;
			$result = $connect->Execute($query);
			//3: Delete all question_attributes to questions in this group
			$query = "DELETE FROM {$dbprefix}question_attributes WHERE qid = ".$qid;
			$result = $connect->Execute($query);
			//4: Delete all questions in this group
			$query = "DELETE FROM {$dbprefix}questions WHERE qid = ".$qid;
			$result = $connect->Execute($query);
			return TRUE;
		}
	}
	return FALSE;
}


function editQuestion($surveyid, $gid, $qid, $dbprefix, $dbaction) {
	global $connect;
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
		$result = $connect->Execute($query);

		if (isset($_POST['gid'])) {
			$query = "UPDATE {$dbprefix}conditions
						  SET cfieldname = '{$surveyid}X{$_POST['gid']}X{$qid}'
						  WHERE cqid={$qid}";
			$result = $connect->Execute($query);
		}

		break;
		case "copyquestion":
		$oldqid=$qid;
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
		if ($result = $connect->Execute($query)) {
			$qid = $connect->Insert_ID();
		} else {
			echo $query."<br />".$connect->ErrorMsg();
		}
		break;
	}
	if ($dbaction == "copyquestion") { //Also copy the answers and the attributes
		$query="SELECT * FROM {$dbprefix}answers
				WHERE qid=$oldqid";
		$results=db_execute_assoc($query);
		while($row=$results->FetchRow()) {
			$qinsert="INSERT INTO {$dbprefix}answers
		  			VALUES ('$qid',
							'".auto_escape($row['code'])."',
							'".auto_escape($row['answer'])."',
							'".auto_escape($row['default_value'])."',
							'".auto_escape($row['sortorder'])."')";
			$qresult=$connect->Execute($qinsert);
		}
		$query="SELECT * FROM {$dbprefix}question_attributes
				WHERE qid=$oldqid";
		$results=db_execute_assoc($query);
		while($row=$results->FetchRow()) {
			$qinsert="INSERT INTO {$dbprefix}question_attributes (qid,attribute,value)
		  			VALUES ('$qid',
							'".auto_escape($row['attribute'])."',
							'".auto_escape($row['value'])."')";
			$qresult=$connect->Execute($qinsert);
		}
	}
	return $qid;
}

function addAttribute($qid, $dbprefix) {
	global $connect;
	$query = "INSERT INTO {$dbprefix}question_attributes
			  (qid, attribute, value)
			  VALUES ($qid,
			  '".$_POST['attribute']."',
			  '".$_POST['value']."')";
	$result = $connect->Execute($query);
}

function delAttribute($qid, $dbprefix) {
	global $connect;
	$query = "DELETE FROM {$dbprefix}question_attributes
			  WHERE qid=$qid
			  AND qaid=".$_GET['qaid'];
	$result = $connect->Execute($query);
}

function updateAttribute($qid, $dbprefix) {
	global $connect;
	$query = "UPDATE {$dbprefix}question_attributes
			  SET value='".$_POST['value']."'
			  WHERE qaid=".$_POST['qaid'];
	$result = $connect->Execute($query);
}

function addAnswer($qid, $dbprefix) {
	global $connect;
	$where=array("qid"=>$qid,
	"code"=>$_POST['code']);

	if (matchExists("{$dbprefix}answers", $where) !== true) {
		$query = "INSERT INTO {$dbprefix}answers
				 (qid, code, answer, default_value, sortorder)
				 VALUES ($qid,
				 '".$_POST['code']."',
				 ?,
				 '".$_POST['default_value']."',
				 '".sprintf("%05d", $_POST['sortorder'])."')";
		$result = $connect->Execute($query, $_POST['answer']);
	} else {
		echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Answer could not be added. There is already an answer with this code")."\")\n //-->\n</script>\n";
	}
}

function delAssessment($surveyid, $dbprefix) {
	global $connect;
	$query = "DELETE FROM {$dbprefix}assessments
			  WHERE id=".$_POST['id']."
			  AND sid=$surveyid";
	$result = $connect->Execute($query);
}

function updateAssessment($surveyid, $dbprefix) {
	global $connect;
	$query = "UPDATE {$dbprefix}assessments
			  SET scope='".$_POST['scope']."',
			  gid=".$_POST['assessment_gid'].",
			  name='".auto_escape($_POST['name'])."',
			  minimum='".$_POST['minimum']."',
			  maximum='".$_POST['maximum']."',
			  message='".auto_escape($_POST['message'])."',
			  link='".auto_escape($_POST['link'])."'
			  WHERE id=".$_POST['id'];
	$result=$connect->Execute($query);
}

function addAssessment($surveyid, $dbprefix) {
	global $connect;
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
	$result = $connect->Execute($query);
}

function delAnswer($qid, $dbprefix) {
	global $connect;
	$query = "DELETE FROM {$dbprefix}answers
			  WHERE qid=$qid
			  AND code='".returnglobal('code')."'";
	$result = $connect->Execute($query);
}

function delGroup($surveyid, $gid, $dbprefix) {
	global $connect;
	global $databasename;
	if (!is_numeric($gid)) {
		return _("Error");
	} elseif ($_GET['ok'] == "yes") {
		if (!isActivated($surveyid)) {
			$query = "SELECT qid FROM {$dbprefix}questions WHERE gid=".$gid;
			$result = db_execute_num($query);
			$qids=array();
			while($row = $result->FetchRow()){
				$qids[]=$row[0];
			} // while
			$qids="'".implode("', '",$qids)."'";
			//1: Delete all answers to questions in this group
			$query = "DELETE FROM {$dbprefix}answers WHERE qid IN (".$qids.")";
			$result = $connect->Execute($query);
			//2: Delete all conditions to questions in this group
			$query = "DELETE FROM {$dbprefix}conditions WHERE qid IN (".$qids.")";
			$result = $connect->Execute($query);
			//3: Delete all question_attributes to questions in this group
			$query = "DELETE FROM {$dbprefix}question_attributes WHERE qid IN (".$qids.")";
			$result = $connect->Execute($query);
			//4: Delete all questions in this group
			$query = "DELETE FROM {$dbprefix}questions WHERE qid IN (".$qids.")";
			$result = $connect->Execute($query);
			//5: Delete Group
			$query = "DELETE FROM {$dbprefix}groups WHERE gid = ".$gid;
			$result = $connect->Execute($query);
			return TRUE;
		}
	}
	return FALSE;
}

function delSurvey($surveyid, $dbprefix) {
	global $connect;
	if (!is_numeric($surveyid)) { //make sure it's just a number!
		return _("Error")." "._("You have not selected a survey to delete");
	} elseif ($_GET['ok'] == "yes") {
		$tablelist = $connect->MetaTables(); //Get a list of table names

		if (in_array("{$dbprefix}survey_$surveyid", $tablelist)) //delete the survey_$surveyid table
		{
			$dsquery = "DROP TABLE `{$dbprefix}survey_$surveyid`";
			$dsresult = $connect->Execute($dsquery) or die ("Couldn't \"$dsquery\" because <br />".$connect->ErrorMsg());
		}

		if (in_array("{$dbprefix}tokens_$surveyid", $tablelist)) //delete the tokens_$surveyid table
		{
			$dsquery = "DROP TABLE `{$dbprefix}tokens_$surveyid`";
			$dsresult = $connect->Execute($dsquery) or die ("Couldn't \"$dsquery\" because <br />".$connect->ErrorMsg());
		}

		$dsquery = "SELECT qid FROM {$dbprefix}questions WHERE sid=$surveyid";
		$dsresult = db_execute_assoc($dsquery) or die ("Couldn't find matching survey to delete<br />$dsquery<br />".$connect->ErrorMsg());
		while ($dsrow = $dsresult->FetchRow())
		{
			$asdel = "DELETE FROM {$dbprefix}answers WHERE qid={$dsrow['qid']}";
			$asres = $connect->Execute($asdel);
			$cddel = "DELETE FROM {$dbprefix}conditions WHERE qid={$dsrow['qid']}";
			$cdres = $connect->Execute($cddel) or die ("Delete conditions failed<br />$cddel<br />".$connect->ErrorMsg());
			$qadel = "DELETE FROM {$dbprefix}question_attributes WHERE qid={$dsrow['qid']}";
			$qares = $connect->Execute($qadel);
		}

		$qdel = "DELETE FROM {$dbprefix}questions WHERE sid=$surveyid";
		$qres = $connect->Execute($qdel);

		$scdel = "DELETE FROM {$dbprefix}assessments WHERE sid=$surveyid";
		$scres = $connect->Execute($scdel);

		$gdel = "DELETE FROM {$dbprefix}groups WHERE sid=$surveyid";
		$gres = $connect->Execute($gdel);

		$sdel = "DELETE FROM {$dbprefix}surveys WHERE sid=$surveyid";
		$sres = $connect->Execute($sdel);
	} else {
		echo $_GET['ok'];
	}
}

function updateAnswer($qid, $dbprefix) {
	global $connect;
	//echo "Hi";
	//echo "<pre>";print_r($_POST); echo "</pre>";
	//FIRST: Renumber all the existing answers (we can fix if a problem occurs)
	$query = "UPDATE {$dbprefix}answers SET qid=99999999 WHERE qid=$qid";
	$result = $connect->Execute($query) or die($connect->ErrorMsg());
	foreach($_POST['code'] as $key=>$code) {
		$where=array("qid"=>$qid,
		"code"=>$code);
		if (matchExists("{$dbprefix}answers", $where) !== true) {
			$insert="INSERT INTO {$dbprefix}answers
					 (qid, code, answer, default_value, sortorder)
					 VALUES
					 ($qid, '$code', '".auto_escape($_POST['answer'][$key])."', '".$_POST['default_value'][$key]."', '".$_POST['sortorder'][$key]."')";
			$result = $connect->Execute($insert) or die($connect->ErrorMsg());
		} else {
			$query = "DELETE FROM {$dbprefix}answers WHERE qid=$qid";
			$result = $connect->Execute($query) or die($connect->ErrorMsg());
			$query = "UPDATE {$dbprefix}answers SET qid=$qid WHERE qid=99999999";
			$result = $connect->Execute($query) or die($connect->ErrorMsg());
			echo "<script type=\"text/javascript\">\n<!--\n alert(\""._("Answer could not be updated. There is already an answer with this code")."\")\n //-->\n</script>\n";
		}
	}
	$query = "DELETE FROM {$dbprefix}answers WHERE qid=99999999";
	$result = $connect->Execute($query) or die($connect->ErrorMsg());
}

function moveAnswer($qid, $dbprefix) {
	global $connect;
	$newsortorder=sprintf("%05d", $_GET['sortorder']+$_GET['moveorder']);
	$query = "UPDATE {$dbprefix}answers
			  SET sortorder='PEND'
			  WHERE qid=$qid
			  AND sortorder='$newsortorder'";
	$result = $connect->Execute($query);
	$query = "UPDATE {$dbprefix}answers
			   SET sortorder='$newsortorder'
			   WHERE qid=$qid
			   AND sortorder='".$_GET['sortorder']."'";
	$result = $connect->Execute($query);
	$query = "UPDATE {$dbprefix}answers
			  SET sortorder='".$_GET['sortorder']."'
			  WHERE qid=$qid
			  AND sortorder='PEND'";
	$result = $connect->Execute($query);
}

function matchExists($table, $where) {
	global $connect;
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
	$result = $connect->Execute($query);
	if ($result->RecordCount() > 0) {
		return true;
	} else {
		return false;
	}
}

function isActivated($surveyid) {
	//This function will return true if a survey is currently active
	//and false if not
	$query = "SELECT active FROM {$dbprefix}surveys WHERE sid=".$surveyid;
	$result = db_execute_num($query);
	while ($row=$result->FetchRow()) {
		if ($row[0] == "Y") {
			return TRUE;
		}
	}
	return FALSE;
}
?>
