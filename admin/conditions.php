<?php
/*
#############################################################
# >>> PHPSurveyor  										    #
#############################################################
# > Author:  Jason Cleeland									#
# > E-mail:  jason@cleeland.org								#
# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
# >          CARLTON SOUTH 3053, AUSTRALIA					#
# > Date: 	 19 April 2003								    #
#															#
# This set of scripts allows you to develop, publish and	#
# perform data-entry on surveys.							#
#############################################################
#															#
#	Copyright (C) 2003  Jason Cleeland						#
#															#
# This program is free software; you can redistribute 		#
# it and/or modify it under the terms of the GNU General 	#
# Public License Version 2 as published by the Free         #
# Software Foundation.										#
#															#
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
include_once("login_check.php");


$surveyid=returnglobal('sid');
$conditionsoutput='';

//Ensure script is not run directly, avoid path disclosure
if (empty($surveyid)) {die("No SID provided.");}

// ToDo: activate this again
//if(isset($_POST['cquestions'])) {
//	echo str_replace("<body ", "<body onload='getAnswers(\"".$_POST['cquestions']."\")'", $htmlheader);
//}; 


$conditionsoutput .= "<table width='100%' border='0' bgcolor='#555555' cellspacing='0' cellpadding='0'>\n"
."\t<tr><td align='center'><font color='white'><strong>"
.$clang->gT("Condition Designer")."</strong></font></td></tr>\n"
."</table>\n";


if (!isset($surveyid))
{
	$conditionsoutput .= "<br /><center><strong>"
	.$clang->gT("You have not selected a Survey.")." ".$clang->gT("You cannot run this script directly.")
	."</strong></center>\n"
	."</body></html>\n";
	return;
}
if (!isset($_GET['qid']) && !isset($_POST['qid']))
{
	$conditionsoutput .= "<br /><center><strong>"
	.$clang->gT("You have not selected a Question.")." ".$clang->gT("You cannot run this script directly.")
	."</strong></center>\n"
	."</body></html>\n";
	return;
}

$markcidarray=Array();
if (isset($_GET['markcid']))
{
	$markcidarray=explode("-",$_GET['markcid']);
	
}

//ADD NEW ENTRY IF THIS IS AN ADD
if (isset($_POST['subaction']) && $_POST['subaction'] == "insertcondition")
{
	if (!isset($_POST['canswers']) || !isset($_POST['cquestions']))
	{
		$conditionsoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Your condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer.","js")."\")\n //-->\n</script>\n";
	}
	else
	{
		foreach ($_POST['canswers'] as $ca)
		{
			$query = "INSERT INTO {$dbprefix}conditions (qid, cqid, cfieldname, value) VALUES "
			. "('{$_POST['qid']}', '{$_POST['cqid']}', '{$_POST['cquestions']}', '$ca')";
			$result = $connect->Execute($query) or die ("Couldn't insert new condition<br />$query<br />".$connect->ErrorMsg());
		}
	}
}
//DELETE ENTRY IF THIS IS DELETE
if (isset($_POST['subaction']) && $_POST['subaction'] == "delete")
{
	$query = "DELETE FROM {$dbprefix}conditions WHERE cid={$_POST['cid']}";
	$result = $connect->Execute($query) or die ("Couldn't delete condition<br />$query<br />".$connect->ErrorMsg());
}
//COPY CONDITIONS IF THIS IS COPY
if (isset($_POST['subaction']) && $_POST['subaction'] == "copyconditions")
{
	$qid=returnglobal('qid');
	$copyconditionsfrom=returnglobal('copyconditionsfrom');
	$copyconditionsto=returnglobal('copyconditionsto');
	if (isset($copyconditionsto) && is_array($copyconditionsto) && isset($copyconditionsfrom) && is_array($copyconditionsfrom))
	{
		//Get the conditions we are going to copy
		$query = "SELECT * FROM {$dbprefix}conditions\n"
		."WHERE cid in ('";
		$query .= implode("', '", $copyconditionsfrom);
		$query .= "')";
		$result = db_execute_assoc($query) or die("Couldn't get conditions for copy<br />$query<br />".$connect->ErrorMsg());
		while($row=$result->FetchRow())
		{
			$proformaconditions[]=array("cqid"=>$row['cqid'],
			"cfieldname"=>$row['cfieldname'],
			"method"=>$row['method'],
			"value"=>$row['value']);
		} // while
		foreach ($copyconditionsto as $copyc)
		{
			list($newsid, $newgid, $newqid)=explode("X", $copyc);
			foreach ($proformaconditions as $pfc)
			{
				//First lets make sure there isn't already an exact replica of this condition
				$query = "SELECT * FROM {$dbprefix}conditions\n"
				."WHERE qid='$newqid'\n"
				."AND cqid='".$pfc['cqid']."'\n"
				."AND cfieldname='".$pfc['cfieldname']."'\n"
				."AND method='".$pfc['method']."'\n"
				."AND value='".$pfc['value']."'";
				$result = $connect->Execute($query) or die("Couldn't check for existing condition<br />$query<br />".$connect->ErrorMsg());
				$count = $result->RecordCount();
				if ($count == 0) //If there is no match, add the condition.
				{
					$query = "INSERT INTO {$dbprefix}conditions ( qid,cqid,cfieldname,method,value) \n"
					."VALUES ( '$newqid', '".$pfc['cqid']."',"
					."'".$pfc['cfieldname']."', '".$pfc['method']."',"
					."'".$pfc['value']."')";
					$result=$connect->Execute($query) or die ("Couldn't insert query<br />$query<br />".$connect->ErrorMsg());
				}
			}
		}
	}
	else
	{
		$message = $clang->gT("Did not copy questions","js").": ";
		if (!isset($copyconditionsfrom))
		{
			$message .= $clang->gT("No condition selected to copy from","js").". ";
		}
		if (!isset($copyconditionsto))
		{
			$message .= $clang->gT("No question selected to copy condition to","js").".";
		}
		$conditionsoutput .= "<script type=\"text/javascript\">\n<!--\nalert('$message');\n//-->\n</script>\n";
	}
}

unset($cquestions);
unset($canswers);


// *******************************************************************
// ** ADD FORM
// *******************************************************************
//1: Get information for this question
if (!isset($qid)) {$qid=returnglobal('qid');}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

$query = "SELECT * FROM {$dbprefix}questions, {$dbprefix}groups\n"
."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid\n"
."AND qid=$qid AND ".db_table_name('questions').".language='".GetBaseLanguageFromSurveyID($surveyid)."'" ;
$result = db_execute_assoc($query) or die ("Couldn't get information for question $qid<br />$query<br />".$connect->ErrorMsg());
while ($rows=$result->FetchRow())
{
	$questiongroupname=$rows['group_name'];
	$questiontitle=$rows['title'];
	$questiontext=$rows['question'];
	$questiontype=$rows['type'];
}

//2: Get all other questions that occur before this question that are pre-determined answer types

//TO AVOID NATURAL SORT ORDER ISSUES, FIRST GET ALL QUESTIONS IN NATURAL SORT ORDER, AND FIND OUT WHICH NUMBER IN THAT ORDER THIS QUESTION IS
$qquery = "SELECT * "
        ."FROM {$dbprefix}questions, {$dbprefix}groups "
        ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid "
        ."AND {$dbprefix}questions.sid=$surveyid "
        ."AND ".db_table_name('questions').".language='".GetBaseLanguageFromSurveyID($surveyid)."' " 
        ."AND ".db_table_name('groups').".language='".GetBaseLanguageFromSurveyID($surveyid)."' " ;

$qresult = db_execute_assoc($qquery) or die ("$qquery<br />".$connect->ErrorMsg());
$qrows = $qresult->GetRows();
usort($qrows, 'CompareGroupThenTitle'); // Perform a case insensitive natural sort on group name then question title of a multidimensional array

$position="before";
foreach ($qrows as $qrow) //Go through each question until we reach the current one
{
	if ($qrow["qid"] != $qid && $position=="before")
	{
		if ($qrow['type'] != "S" && $qrow['type'] != "D" && $qrow['type'] != "T" && $qrow['type'] != "Q")
		{
			$questionlist[]=$qrow["qid"];
		}
	}
	elseif ($qrow["qid"] == $qid)
	{
		break;
	}
}

//Now, using the same array which is now properly sorted by group then question
//Create an array of all the questions that appear AFTER the current one
$position = "before";
foreach ($qrows as $qrow) //Go through each question until we reach the current one
{
	if ($qrow["qid"] == $qid)
	{
		$position="after";
		//break;
	}
	elseif ($qrow["qid"] != $qid && $position=="after")
	{
		$postquestionlist[]=$qrow['qid'];
	}
}

$theserows=array();
if (isset($questionlist) && is_array($questionlist))
{
	foreach ($questionlist as $ql)
	{
		$query = "SELECT {$dbprefix}questions.qid, {$dbprefix}questions.sid, {$dbprefix}questions.gid, {$dbprefix}questions.question, {$dbprefix}questions.type, {$dbprefix}questions.lid, {$dbprefix}questions.title FROM {$dbprefix}questions, {$dbprefix}groups "
                ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}questions.qid=$ql AND ".db_table_name('questions').".language='".GetBaseLanguageFromSurveyID($surveyid)."' AND ".db_table_name('groups').".language='".GetBaseLanguageFromSurveyID($surveyid)."'" ;
        $result=db_execute_assoc($query) or die("Couldn't get question $qid");
		$thiscount=$result->RecordCount();
		while ($myrows=$result->FetchRow())
		{
			$theserows[]=array("qid"=>$myrows['qid'], "sid"=>$myrows['sid'], "gid"=>$myrows['gid'], "question"=>$myrows['question'], "type"=>$myrows['type'], "lid"=>$myrows['lid'], "title"=>$myrows['title']);
		}
	}
}

if (isset($postquestionlist) && is_array($postquestionlist))
{
	foreach ($postquestionlist as $pq)
	{
		$query = "SELECT {$dbprefix}questions.qid, {$dbprefix}questions.sid, {$dbprefix}questions.gid, {$dbprefix}questions.question, {$dbprefix}questions.type, {$dbprefix}questions.lid, {$dbprefix}questions.title FROM {$dbprefix}questions, {$dbprefix}groups WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}questions.qid=$pq AND ".db_table_name('questions').".language='".GetBaseLanguageFromSurveyID($surveyid)."'" ;
		$result = db_execute_assoc($query) or die("Couldn't get postquestions $qid<br />$query<br />".$connect->ErrorMsg());
		$postcount=$result->RecordCount();
		while($myrows=$result->FetchRow())
		{
			$postrows[]=array("qid"=>$myrows['qid'], "sid"=>$myrows['sid'], "gid"=>$myrows['gid'], "question"=>$myrows['question'], "type"=>$myrows['type'], "lid"=>$myrows['lid'], "title"=>$myrows['title']);
		} // while
	}
	$postquestionscount=count($postrows);
}

$questionscount=count($theserows);

if (isset($postquestionscount) && $postquestionscount > 0) //Build the select box for questions after this one
{
	foreach ($postrows as $pr)
	{
		$pquestions[]=array("text"=>$pr['title'].": ".substr($pr['question'], 0, 30),
		"fieldname"=>$pr['sid']."X".$pr['gid']."X".$pr['qid']);
	}
}

if ($questionscount > 0)
{
	$X="X";
	foreach($theserows as $rows)
	{
		if (strlen($rows['question']) > 30) {$shortquestion=$rows['title'].": ".substr(strip_tags($rows['question']), 0, 30).".. ";}
		else {$shortquestion=$rows['title'].": ".strip_tags($rows['question']);}
		if ($rows['type'] == "A" || $rows['type'] == "B" || $rows['type'] == "C" || $rows['type'] == "E" || $rows['type'] == "F" || $rows['type'] == "H")
		{
			$aquery="SELECT * FROM {$dbprefix}answers WHERE qid={$rows['qid']} AND {$dbprefix}answers.language='".GetBaseLanguageFromSurveyID($surveyid)."' ORDER BY sortorder, answer";
			$aresult=db_execute_assoc($aquery) or die ("Couldn't get answers to Array questions<br />$aquery<br />".$connect->ErrorMsg());
			while ($arows = $aresult->FetchRow())
			{
				if (strlen($arows['answer']) > 10) {$shortanswer=substr($arows['answer'], 0, 10).".. ";}
				else {$shortanswer = $arows['answer'];}
				$shortanswer .= " [{$arows['code']}]";
				$cquestions[]=array("$shortquestion [$shortanswer]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']);
				switch ($rows['type'])
				{
					case "A":
					for ($i=1; $i<=5; $i++)
					{
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], $i, $i);
					}
					break;
					case "B":
					for ($i=1; $i<=10; $i++)
					{
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], $i, $i);
					}
					break;
					case "C":
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "Y", $clang->gT("Yes"));
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "U", $clang->gT("Uncertain"));
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "N", $clang->gT("No"));
					break;
					case "E":
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "I", $clang->gT("Increase"));
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "S", $clang->gT("Same"));
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "D", $clang->gT("Decrease"));
					break;
					case "F":
					case "H":
					$fquery = "SELECT * FROM {$dbprefix}labels "
					. "WHERE lid={$rows['lid']} AND language='".GetBaseLanguageFromSurveyID($surveyid)."' " 
					. "ORDER BY sortorder, code ";
					$fresult = db_execute_assoc($fquery);
					while ($frow=$fresult->FetchRow())
					{
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], $frow['code'], $frow['title']);
					}
					break;
				}
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "", $clang->gT("No answer"));
			}
		}
		elseif ($rows['type'] == "R")
		{
			$aquery="SELECT * FROM {$dbprefix}answers "
			."WHERE qid={$rows['qid']} AND ".db_table_name('answers').".language='".GetBaseLanguageFromSurveyID($surveyid)."' " 
			."ORDER BY sortorder, answer";
			$aresult=db_execute_assoc($aquery) or die ("Couldn't get answers to Ranking question<br />$aquery<br />".$connect->ErrorMsg());
			$acount=$aresult->RecordCount();
			while ($arow=$aresult->FetchRow())
			{
				$theanswer = addcslashes($arow['answer'], "'");
				$quicky[]=array($arow['code'], $theanswer);
			}
			for ($i=1; $i<=$acount; $i++)
			{
				$cquestions[]=array("$shortquestion [RANK $i]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i);
				foreach ($quicky as $qck)
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i, $qck[0], $qck[1]);
				}
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i, "", $clang->gT("No answer"));
			}
			unset($quicky);
		}
		else
		{
			$cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid']);
			switch ($rows['type'])
			{
				case "Y":
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "Y", $clang->gT("Yes"));
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "N", $clang->gT("No"));
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "", $clang->gT("No answer"));
				break;
				case "G":
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "F", $clang->gT("Female"));
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "M", $clang->gT("Male"));
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "", $clang->gT("No answer"));
				break;
				case "5":
				for ($i=1; $i<=5; $i++)
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], $i, $i);
				}
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "", $clang->gT("No answer"));
				break;
				case "W":
				case "Z":
				$fquery = "SELECT * FROM {$dbprefix}labels\n"
				. "WHERE lid={$rows['lid']} AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
				. "ORDER BY sortorder, code";

				$fresult = db_execute_assoc($fquery);
				while ($frow=$fresult->FetchRow())
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], $frow['code'], $frow['title']);
				}
				break;
				default:
				$aquery="SELECT * FROM {$dbprefix}answers\n"
				."WHERE qid={$rows['qid']} AND language='".GetBaseLanguageFromSurveyID($surveyid)."' " 
				."ORDER BY sortorder, answer";
				$aresult=db_execute_assoc($aquery) or die ("Couldn't get answers to Ranking question<br />$aquery<br />".$connect->ErrorMsg());
				while ($arows=$aresult->FetchRow())
				{
					$theanswer = addcslashes($arows['answer'], "'");
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], $arows['code'], $theanswer);
				}
				if ($rows['type'] != "M" && $rows['type'] != "P" && $rows['type'] != "J" && $rows['type'] != "I")

				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "", $clang->gT("No answer"));
				}
				break;
			}
		}
	}
}

//JAVASCRIPT TO SHOW MATCHING ANSWERS TO SELECTED QUESTION
$conditionsoutput .= "<script type='text/javascript'>\n"
."<!--\n"
."\tvar Fieldnames = new Array();\n"
."\tvar Codes = new Array();\n"
."\tvar Answers = new Array();\n"
."\tvar QFieldnames = new Array();\n"
."\tvar Qcqids = new Array();\n";
$jn=0;
if (isset($canswers))
{
	foreach($canswers as $can)
	{
		$an=str_replace("'", "`", $can[2]);
		$conditionsoutput .= "\t\tFieldnames[$jn]='$can[0]';\n"
		."\t\tCodes[$jn]='$can[1]';\n"
		."\t\tAnswers[$jn]='$an';\n";
		$jn++;
	}
}
$jn=0;

if (isset($cquestions))
{
	foreach ($cquestions as $cqn)
	{
		$conditionsoutput .= "\t\tQFieldnames[$jn]='$cqn[3]';\n"
		."\t\tQcqids[$jn]='$cqn[1]';\n";
		$jn++;
	}
}
$conditionsoutput .= "\n"
."\tfunction clearAnswers()\n"
."\t\t{\n"
."\t\t\tfor (var i=document.getElementById('canswers').options.length-1; i>=0; i--)\n"
."\t\t\t\t{\n";
//$conditionsoutput .= "alert(i);\n";
$conditionsoutput .= "\t\t\t\t\tdocument.getElementById('canswers').options[i] = null;\n"
."\t\t\t\t}\n"
."\t\t}\n";

$conditionsoutput .= "\tfunction getAnswers(fname)\n"
."\t\t{\n";
//$conditionsoutput .= "\t\talert(getElementById('canswers').options.length)\n";
//$conditionsoutput .= "\t\t\t{\n";
$conditionsoutput .= "\t\t\tfor (var i=document.getElementById('canswers').options.length-1; i>=0; i--)\n"
."\t\t\t\t{\n";
//$conditionsoutput .= "alert(i);\n";
$conditionsoutput .= "\t\t\t\t\tdocument.getElementById('canswers').options[i] = null;\n"
."\t\t\t\t}\n";
//$conditionsoutput .= "\t\t\t}\n";
//$conditionsoutput .= "\t\t\talert(fname);\n";
$conditionsoutput .= "\t\t\tvar Keys = new Array();\n"
."\t\t\tfor (var i=0;i<Fieldnames.length;i++)\n"
."\t\t\t\t{\n"
."\t\t\t\tif (Fieldnames[i] == fname)\n"
."\t\t\t\t\t{\n"
."\t\t\t\t\tKeys[Keys.length]=i;\n"
."\t\t\t\t\t}\n"
."\t\t\t\t}\n"
."\t\t\tfor (var i=0;i<QFieldnames.length;i++)\n"
."\t\t\t\t{\n"
."\t\t\t\tif (QFieldnames[i] == fname)\n"
."\t\t\t\t\t{\n"
."\t\t\t\t\tdocument.getElementById('cqid').value=Qcqids[i];\n"
."\t\t\t\t\t}\n"
."\t\t\t\t}\n";
//$conditionsoutput .= "\t\t\talert(Keys.length);\n";
$conditionsoutput .= "\t\t\tfor (var i=0;i<Keys.length;i++)\n"
."\t\t\t\t{\n";
//$conditionsoutput .= "\t\t\t\talert(Answers[Keys[i]]);\n";
$conditionsoutput .= "\t\t\t\tdocument.getElementById('canswers').options[document.getElementById('canswers').options.length] = new Option(Answers[Keys[i]], Codes[Keys[i]]);\n"
."\t\t\t\t}\n"
."\t\t}\n"
."//-->\n"
."</script>\n";

//SHOW FORM TO CREATE IT!
$conditionsoutput .= "<table width='100%' align='center' cellspacing='0' cellpadding='0' style='border-style: solid; border-width: 1; border-color: #555555'>\n"
."\t<tr bgcolor='#CCFFCC'>\n"
."\t\t<td  align='center' >\n";
$showreplace="$questiontitle<img src='$imagefiles/speaker.png' alt=\""
. htmlspecialchars($questiontext)
. "\" onclick=\"alert('"
. htmlspecialchars(addslashes(strip_tags($questiontext)))
. "')\" />";
$onlyshow=str_replace("{QID}", $showreplace, $clang->gT("Only show question {QID} IF"));
$conditionsoutput .= "\t\t\t<strong>$onlyshow</strong>\n"
."\t\t</td>\n"
."\t</tr>\n";

//3: Get other conditions currently set for this question
$query = "SELECT {$dbprefix}conditions.cid, {$dbprefix}conditions.cqid, {$dbprefix}conditions.cfieldname, {$dbprefix}conditions.value, {$dbprefix}questions.type\n"
."FROM {$dbprefix}conditions, {$dbprefix}questions\n"
."WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid AND ".db_table_name('questions').".language='".GetBaseLanguageFromSurveyID($surveyid)."' " 
."AND {$dbprefix}conditions.qid=$qid\n"
."ORDER BY {$dbprefix}conditions.cfieldname"; 
$result = db_execute_assoc($query) or die ("Couldn't get other conditions for question $qid<br />$query<br />".$connect->ErrorMsg());
$conditionscount=$result->RecordCount();

if ($conditionscount > 0)
{
	while ($rows=$result->FetchRow())
	{
		if (is_null(array_search($rows['cid'], $markcidarray)) || // PHP4
			array_search($rows['cid'], $markcidarray) === FALSE) // PHP5
			// === required cause key 0 would otherwise be interpreted as FALSE
		{
			$markcidstyle="";
		}
		else {
			$markcidstyle="background-color: orange;";
		}

		if (isset($currentfield) && $currentfield != $rows['cfieldname'])
		{
			$conditionsoutput .= "\t\t\t\t<tr bgcolor='#E1FFE1'>\n"
			."\t\t\t\t\t<td valign='middle' align='center'>\n"
			."<font size='1'><strong>"
			.$clang->gT("and")."</strong></font>";
		}
		elseif (isset($currentfield))
		{
			$conditionsoutput .= "\t\t\t\t<tr bgcolor='#E1FFE1'>\n"
			."\t\t\t\t\t<td valign='top' align='center'>\n"
			."<font size='1'><strong>"
			.$clang->gT("OR")."</strong></font>";
		}
		$conditionsoutput .= "\t<tr bgcolor='#E1FFE1' style='$markcidstyle'>\n"
		."\t<td><form style='margin-bottom:0;' name='del{$rows['cid']}' id='del{$rows['cid']}' method='post' action='$scriptname?action=conditions'>\n"
		."\t\t<table width='100%' style='height: 13px;' cellspacing='0' cellpadding='0'><tr><td valign='middle' align='right' width='50%'><font size='1' face='verdana'>\n";
		//BUILD FIELDNAME?
		foreach ($cquestions as $cqn)
		{
			if ($cqn[3] == $rows['cfieldname'])
			{
				$conditionsoutput .= "\t\t\t$cqn[0] (qid{$rows['cqid']})\n";
				$conditionsList[]=array("cid"=>$rows['cid'],
				"text"=>$cqn[0]." ({$rows['value']})");
			}
			else
			{
				//$conditionsoutput .= "\t\t\t<font color='red'>ERROR: Delete this condition. It is out of order.</font>\n";
			}
		}
		$conditionsoutput .= "\t\t</font></td>\n"
		."\t\t<td align='center' valign='middle' width='15%'><font size='1'>"
		.$clang->gT("Equals")."</font></td>"
		."\t\t\t\t\n"
		."\t\t\t\t\t<td align='left' valign='middle' width='30%'>\n"
		."\t\t\t\t\t\t<font size='1' face='verdana'>\n";
		foreach ($canswers as $can)
		{
			//$conditionsoutput .= $rows['cfieldname'] . "- $can[0]<br />";
			//$conditionsoutput .= $can[1];
			if ($can[0] == $rows['cfieldname'] && $can[1] == $rows['value'])
			{
				$conditionsoutput .= "\t\t\t\t\t\t$can[2] ($can[1])\n";
			}
		}
		$conditionsoutput .= "\t\t\t\t\t</font></td>\n"
		."\t\t\t\t\t<td align='right' valign='middle' >\n"
		."\t\t\t\t\t\t<input type='submit' value='".$clang->gT("Delete")."' style='font-family: verdana; font-size: 8; height:15' />\n"
		."\t\t\t\t\t<input type='hidden' name='subaction' value='delete' />\n"
		."\t\t\t\t\t<input type='hidden' name='cid' value='{$rows['cid']}' />\n"
		."\t\t\t\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
		."\t\t\t\t\t<input type='hidden' name='qid' value='$qid' />\n"
		."\t\t\t\t\t</td>\n"
		."\t</table></form>\n"
		."\t</tr>\n";
		$currentfield=$rows['cfieldname'];
	}
	$conditionsoutput .= "\t<tr>\n"
	."\t\t<td height='3'>\n"
	."\t\t</td>\n"
	."\t</tr>\n";
}
else
{
	$conditionsoutput .= "\t<tr>\n"
	."\t\t<td colspan='3' height='3'>\n"
	."\t\t</td>\n"
	."\t</tr>\n";
}

$conditionsoutput .= "\t<tr bgcolor='#555555'><td colspan='3'></td></tr>\n";

if ($conditionscount > 0 && isset($postquestionscount) && $postquestionscount > 0)
{
	$conditionsoutput .= "<tr bgcolor='#555555'><td colspan='3'><form action='$scriptname?action=conditions' name='copyconditions' id='copyconditions' method='post'>\n";

	$conditionsoutput .= "\t<table width='100%'><tr bgcolor='#CDCDCD'>\n"
	."\t\t<td colspan='3' align='center'>\n"
	."\t\t<strong>"
	.$clang->gT("Copy Conditions")."</strong>\n"
	."\t\t</td>\n"
	."\t</tr>\n";

	$conditionsoutput .= "\t<tr>\n"
	."\t\t<th>".$clang->gT("Condition")."</th><th></th><th>".$clang->gT("Question")."</th>\n"
	."\t</tr>\n";

	$conditionsoutput .= "\t<tr>\n"
	."\t\t<td align='center'>\n"
	."\t\t<select name='copyconditionsfrom[]' multiple style='font-family:verdana; font-size:10; width:220; background-color: #E1FFE1' size='4' >\n";
	foreach ($conditionsList as $cl)
	{
		$conditionsoutput .= "<option value='".$cl['cid']."'>".$cl['text']."</option>\n";
	}
	$conditionsoutput .= "\t\t</select>\n"
	."\t\t</td>\n"
	."\t\t<td align='center'>\n"
	."\t\t".$clang->gT("copy to")."\n"
	."\t\t</td>\n"
	."\t\t<td align='center'>\n"
	."\t\t<select name='copyconditionsto[]' multiple style='font-family:verdana; font-size:10; width:220' size='4'>\n";
	foreach ($pquestions as $pq)
	{
		$conditionsoutput .= "<option value='{$pq['fieldname']}'>".$pq['text']."</option>\n";
	}
	$conditionsoutput .= "\t\t</select>\n";
	$conditionsoutput .= "\t\t</td>\n"
	."\t</tr>\n";

	$conditionsoutput .= "\t<tr><td colspan='3' align='center'>\n"
	."<input type='submit' value='".$clang->gT("Copy Conditions")."' onclick=\"return confirm('".$clang->gT("Are you sure you want to copy these condition(s) to the questions you have selected?")."')\" />"
	."\t\t\n";

	$conditionsoutput .= "<input type='hidden' name='subaction' value='copyconditions' />\n"
	."<input type='hidden' name='sid' value='$surveyid' />\n"
	."<input type='hidden' name='qid' value='$qid' />\n"
	."</td></tr></table></form>\n";

	$conditionsoutput .= "\t<tr ><td colspan='3'></td></tr>\n"
	."\t<tr bgcolor='#555555'><td colspan='3'></td></tr>\n";
}

$conditionsoutput .= "</table>\n";
$conditionsoutput .= "<form action='$scriptname?action=conditions' name='addconditions' id='addconditions' method='post'>\n";
$conditionsoutput .= "<table width='100%' border='0' >";
$conditionsoutput .= "\t<tr bgcolor='#CDCDCD'>\n"
."\t\t<td colspan='3' align='center'>\n"
."\t\t\t<strong>".$clang->gT("Add Condition")."</strong>\n"
."\t\t</td>\n"
."\t</tr>\n"
."\t<tr bgcolor='#EFEFEF'>\n"
."\t\t<th width='40%'>\n"
."\t\t\t<strong>".$clang->gT("Question")."</strong>\n"
."\t\t</th>\n"
."\t\t<th width='20%'>\n"
."\t\t</th>\n"
."\t\t<th width='40%'>\n"
."\t\t\t<strong>".$clang->gT("Answer")."</strong>\n"
."\t\t</th>\n"
."\t</tr>\n"
."\t<tr>\n"
."\t\t<td valign='top' align='center'>\n"
."\t\t\t<select onclick=\"getAnswers(this.options[this.selectedIndex].value)\" name='cquestions' id='cquestions' style='font-family:verdana; font-size:10; width:220' size='5'>\n";
if (isset($cquestions))
{
	foreach ($cquestions as $cqn)
	{
		$conditionsoutput .= "\t\t\t\t<option value='$cqn[3]'";
		if (isset($_POST['cquestions']) && $cqn[3] == $_POST['cquestions']) {
			$conditionsoutput .= " selected";
		}
		$conditionsoutput .= ">$cqn[0]</option>\n";
	}
}
$conditionsoutput .= "\t\t\t</select>\n"
."\t\t</td>\n"
."\t\t<td align='center'>\n";
//$conditionsoutput .= "\t\t\t<select name='method' id='method' style='font-family:verdana; font-size:10'>\n";
//$conditionsoutput .= "\t\t\t\t<option value='='>Equals</option>\n";
//$conditionsoutput .= "\t\t\t\t<option value='!'>Does not equal</option>\n";
//$conditionsoutput .= "\t\t\t</select>\n";
$conditionsoutput .= "\t\t\t".$clang->gT("Equals")."\n"
."\t\t</td>\n"
."\t\t<td valign='top' align='center'>\n"
."\t\t\t<select name='canswers[]' multiple id='canswers' style='font-family:verdana; font-size:10; width:220' size='5'>\n";

$conditionsoutput .= "\t\t\t</select>\n"
."\t</tr>\n"
."\t<tr>\n"
."\t\t<td colspan='3' align='center'>\n"
."\t\t\t<input type='reset' value='".$clang->gT("Clear")."' onclick=\"clearAnswers()\" />\n"
."\t\t\t<input type='submit' value='".$clang->gT("Add Condition")."' />\n"
."<input type='hidden' name='sid' value='$surveyid' />\n"
."<input type='hidden' name='qid' value='$qid' />\n"
."<input type='hidden' name='subaction' value='insertcondition' />\n"
."<input type='hidden' name='cqid' id='cqid' value='' />\n"
."\t\t</td>\n"
."\t</tr>\n"
."</table>\n"
."</form>\n"
."<table width='100%'  border='0'>\n";
$conditionsoutput .= "\t<tr><td colspan='3'></td></tr>\n"
."\t<tr bgcolor='#555555'>\n"
."\t\t<td height='5' colspan='3'>\n"
."\t\t</td>\n";
$conditionsoutput .= "\t<tr bgcolor='#CDCDCD'><td colspan=3 height='10'></td></tr>\n"
."\t\t<tr><td colspan='3' align='center'>\n"
."\t\t\t<input type='submit' value='".$clang->gT("Close Window")."' onclick=\"window.close()\"  />\n"
."\t\t</td>\n"
."\t</tr>\n";
$conditionsoutput .= "\t<tr><td colspan='3'></td></tr>\n"
."</table><br />&nbsp;\n";


?>
