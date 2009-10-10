<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
* 
* $Id$
*/

//
// TODO
//
// Optionnally mark ADDED, UPDATED lines with different colors just as the EDITTED one
//
// add warning if updating scenario with a scenario number which already exists (merging)
//
// Try a wrapping with scrollbar for conditions display: http://www.htmlcodetutorial.com/help/ftopic2394.html
// or http://www.kelvinluck.com/assets/jquery/jScrollPane/jScrollPane.html
//
// Try to use an intelligent dropdown: http://www.fairwaytech.com/flexbox/Demos.aspx

include_once("login_check.php");

//BEGIN Sanitizing POSTed data
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
if (!isset($qid)) {$qid=returnglobal('qid');}
if (!isset($gid)) {$gid=returnglobal('gid');}
if (!isset($p_scenario)) {$p_scenario=returnglobal('scenario');}
if (!isset($p_cqid)) {$p_cqid=returnglobal('cqid');}
if (!isset($p_cid)) {$p_cid=returnglobal('cid');}
if (!isset($p_subaction)) {$p_subaction=returnglobal('subaction');}
if (!isset($p_cquestions)) {$p_cquestions=returnglobal('cquestions');}
if (!isset($p_csrctoken)) {$p_csrctoken=returnglobal('csrctoken');}
if (!isset($p_prevquestionsgqa)) {$p_prevquestionsgqa=returnglobal('prevQuestionSGQA');}

if (!isset($p_canswers))
{
	
	if (isset($_POST['canswers']) && is_array($_POST['canswers']))
	{
		foreach ($_POST['canswers'] as $key => $val)
		{
		 	$p_canswers[$key]= preg_replace("/[^_.a-zA-Z0-9]@/", "", $val);
		}
	}
}
if (isset($_POST['method']))
{
	if (!in_array($_POST['method'], array('<','<=','>','>=','==','!=','RX')))
	{
		$p_method = "==";
	}
	else
	{
		$p_method = trim ($_POST['method']);
	}
}


if (isset($_POST['newscenarionum']))
{
	$p_newscenarionum = sanitize_int($_POST['newscenarionum']);
}
//END Sanitizing POSTed data

include_once("login_check.php");
include_once("database.php");
// Caution (lemeur): database.php uses auto_unescape on all entries in $_POST
// Take care to not use auto_unescape on $_POST variables after this

$conditionsoutput = "";

// add the conditions container table
$conditionsoutput .= "<table width='100%' border='0' cellpadding='0' cellspacing='0'><tr><td>\n";

//MAKE SURE THAT THERE IS A SID
if (!isset($surveyid) || !$surveyid)
{
	$conditionsoutput .= "\t<tr><td colspan='2' height='4'><font size='1'><strong>"
	.$clang->gT("Conditions manager").":</strong></font></td></tr>\n"
	."\t<tr><td align='center'><br /><font color='red'><strong>"
	.$clang->gT("Error")."</strong></font><br />".$clang->gT("You have not selected a survey")."<br /><br />"
	."<input type='submit' value='"
	.$clang->gT("Main admin screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br /><br /></td></tr>\n"
	."</table>\n"
	."</body>\n</html>";
	return;
}

//MAKE SURE THAT THERE IS A QID
if (!isset($qid) || !$qid)
{
	$conditionsoutput .= "\t<tr><td colspan='2' height='4'><font size='1'><strong>"
	.$clang->gT("Conditions manager").":</strong></font></td></tr>\n"
	."\t<tr><td align='center'><br /><font color='red'><strong>"
	.$clang->gT("Error")."</strong></font><br />".$clang->gT("You have not selected a question")."<br /><br />"
	."<input type='submit' value='"
	.$clang->gT("Main admin screen")."' onclick=\"window.open('$scriptname', '_top')\" /><br /><br /></td></tr>\n"
	."</table>\n"
	."</body>\n</html>";
	return;
}

	$conditionsoutput .= "\t<div class='menubar'>"
    ."<div class='menubar-title'>"
	."<strong>".$clang->gT("Conditions designer").":</strong> "
	."</div>\n";

$extraGetParams ="";
if (isset($qid) && isset($gid))
{
	$extraGetParams="&amp;gid=$gid&amp;qid=$qid";
}
// If we made it this far, then lets develop the menu items
$conditionsoutput .= "\t<div class='menubar-main'>\n"
."<div class='menubar-left'>\n"
."<a href=\"#\" onclick=\"window.open('$scriptname?sid=$surveyid$extraGetParams', '_top')\" title='".$clang->gTview("Return to survey administration")."'>" 
."<img name='HomeButton' src='$imagefiles/home.png' alt='".$clang->gT("Return to survey administration")."' /></a>\n"
."<img src='$imagefiles/blank.gif' alt='' width='11' />\n"
."<img src='$imagefiles/seperator.gif' alt='' />\n"
."<a href=\"#\" onclick=\"window.open('$scriptname?action=conditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid', '_top')\" title='".$clang->gTview("Show conditions for this question")."' >" 
."<img name='SummaryButton' src='$imagefiles/summary.png' alt='".$clang->gT("Show conditions for this question")."' /></a>\n"
."<img src='$imagefiles/seperator.gif' alt='' />\n"
."<a href=\"#\" onclick=\"window.open('$scriptname?action=conditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;subaction=editconditionsform', '_top')\" title='".$clang->gTview("Add and edit conditions")."' >" 
."<img name='ConditionAddButton' src='$imagefiles/conditions_add.png' alt='".$clang->gT("Add and edit conditions")."' /></a>\n"
."<a href=\"#\" onclick=\"window.open('$scriptname?action=conditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;subaction=copyconditionsform', '_top')\" title='".$clang->gTview("Copy conditions")."' >" 
."<img name='ConditionCopyButton' src='$imagefiles/conditions_copy.png' alt='".$clang->gT("Copy conditions")."' /></a>\n";


$conditionsoutput .="\t</div><div class='menubar-right'>\n"
		."<img width=\"11\" alt=\"\" src=\"$imagefiles/blank.gif\"/>\n"
		."<font class=\"boxcaption\">".$clang->gT("Questions").":</font>\n"
		."<select id='questionNav' onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\"></select>\n"
		."<img hspace=\"0\" border=\"0\" alt=\"\" src=\"$imagefiles/seperator.gif\"/>\n"
		."<a href=\"http://docs.limesurvey.org\" target='_blank' title=\"".$clang->gTview("LimeSurvey manual")."\">" 
		."<img src='$imagefiles/showhelp.png' name='ShowHelp' title=''" 
		."alt='". $clang->gT("LimeSurvey manual")."' /></a>";


$conditionsoutput .= "\t</div></div></div>\n"
		."<p style='margin: 0pt; font-size: 1px; line-height: 1px; height: 1px;'> </p>"
		."</td></tr>\n";


$markcidarray=Array();
if (isset($_GET['markcid']))
{
	$markcidarray=explode("-",$_GET['markcid']);
}


//BEGIN PROCESS ACTIONS
// ADD NEW ENTRY IF THIS IS AN ADD
if (isset($p_subaction) && $p_subaction == "insertcondition")
{
	if ((!isset($p_canswers) &&
				!isset($_POST['ConditionConst']) &&
				!isset($_POST['prevQuestionSGQA']) &&
				!isset($_POST['tokenAttr']) &&
				!isset($_POST['ConditionRegexp'])) ||
			(!isset($p_cquestions) && !isset($p_csrctoken)))
	{
		$conditionsoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Your condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer.","js")."\")\n //-->\n</script>\n";
	}
	else
	{
		if (isset($p_cquestions) && $p_cquestions != '')
		{
			$conditionCfieldname=$p_cquestions;
		}
		elseif(isset($p_csrctoken) && $p_csrctoken != '')
		{
			$conditionCfieldname=$p_csrctoken;
		}

		if (isset($p_canswers))
		{
			foreach ($p_canswers as $ca)
			{
				$query = "INSERT INTO {$dbprefix}conditions (qid, scenario, cqid, cfieldname, method, value) VALUES "
					. "('{$qid}', '{$p_scenario}', '{$p_cqid}', '{$conditionCfieldname}', '{$p_method}', '$ca')";
				$result = $connect->Execute($query) or safe_die ("Couldn't insert new condition<br />$query<br />".$connect->ErrorMsg());
			}
		}

		unset($posted_condition_value);
		// Please note that auto_unescape is already applied in database.php included above
		// so we only need to db_quote _POST variables
		if (isset($_POST['ConditionConst']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#CONST")
		{
			$posted_condition_value = db_quote($_POST['ConditionConst']);
		}
		elseif (isset($_POST['prevQuestionSGQA']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#PREVQUESTIONS")
		{
			$posted_condition_value = db_quote($_POST['prevQuestionSGQA']);
		}
		elseif (isset($_POST['tokenAttr']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#TOKENATTRS")
		{
			$posted_condition_value = db_quote($_POST['tokenAttr']);
		}
		elseif (isset($_POST['ConditionRegexp']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#REGEXP")
		{
			$posted_condition_value = db_quote($_POST['ConditionRegexp']);
		}

		if (isset($posted_condition_value))
		{ 
			$query = "INSERT INTO {$dbprefix}conditions (qid, scenario, cqid, cfieldname, method, value) VALUES "
				. "('{$qid}', '{$p_scenario}', '{$p_cqid}', '{$conditionCfieldname}', '{$p_method}', '".$posted_condition_value."')";
			$result = $connect->Execute($query) or safe_die ("Couldn't insert new condition<br />$query<br />".$connect->ErrorMsg());
		}
	}
}

// UPDATE ENTRY IF THIS IS AN EDIT
if (isset($p_subaction) && $p_subaction == "updatecondition")
{
	if ((!isset($p_canswers) &&
				!isset($_POST['ConditionConst']) &&
				!isset($_POST['prevQuestionSGQA']) &&
				!isset($_POST['tokenAttr']) &&
				!isset($_POST['ConditionRegexp'])) ||
			(!isset($p_cquestions) && !isset($p_csrctoken)))
	{
		$conditionsoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Your condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer.","js")."\")\n //-->\n</script>\n";
	}
	else
	{
		if (isset($p_cquestions) && $p_cquestions != '')
		{
			$conditionCfieldname=$p_cquestions;
		}
		elseif(isset($p_csrctoken) && $p_csrctoken != '')
		{
			$conditionCfieldname=$p_csrctoken;
		}

		if (isset($p_canswers))
		{
			foreach ($p_canswers as $ca)
			{ // This is an Edit, there will only be ONE VALUE
				$query = "UPDATE {$dbprefix}conditions SET qid='{$qid}', scenario='{$p_scenario}', cqid='{$p_cqid}', cfieldname='{$conditionCfieldname}', method='{$p_method}', value='$ca' "
					. " WHERE cid={$p_cid}";
				$result = $connect->Execute($query) or safe_die ("Couldn't update condition<br />$query<br />".$connect->ErrorMsg());
			}
		}

		unset($posted_condition_value);
		// Please note that auto_unescape is already applied in database.php included above
		// so we only need to db_quote _POST variables
		if (isset($_POST['ConditionConst']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#CONST")
		{
			$posted_condition_value = db_quote($_POST['ConditionConst']);
		}
		elseif (isset($_POST['prevQuestionSGQA']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#PREVQUESTIONS")
		{
			$posted_condition_value = db_quote($_POST['prevQuestionSGQA']);
		}
		elseif (isset($_POST['tokenAttr']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#TOKENATTRS")
		{
			$posted_condition_value = db_quote($_POST['tokenAttr']);
		}
		elseif (isset($_POST['ConditionRegexp']) && isset($_POST['editTargetTab']) && $_POST['editTargetTab']=="#REGEXP")
		{
			$posted_condition_value = db_quote($_POST['ConditionRegexp']);
		}

		if (isset($posted_condition_value)) 
		{ 
			$query = "UPDATE {$dbprefix}conditions SET qid='{$qid}', scenario='{$p_scenario}' , cqid='{$p_cqid}', cfieldname='{$conditionCfieldname}', method='{$p_method}', value='".$posted_condition_value."' "
				. " WHERE cid={$p_cid}";
			$result = $connect->Execute($query) or safe_die ("Couldn't insert new condition<br />$query<br />".$connect->ErrorMsg());
		}
	}
}

// DELETE ENTRY IF THIS IS DELETE
if (isset($p_subaction) && $p_subaction == "delete")
{
	$query = "DELETE FROM {$dbprefix}conditions WHERE cid={$p_cid}";
	$result = $connect->Execute($query) or safe_die ("Couldn't delete condition<br />$query<br />".$connect->ErrorMsg());
}

// DELETE ALL CONDITIONS IN THIS SCENARIO
if (isset($p_subaction) && $p_subaction == "deletescenario")
{
	$query = "DELETE FROM {$dbprefix}conditions WHERE qid={$qid} AND scenario={$p_scenario}";
	$result = $connect->Execute($query) or safe_die ("Couldn't delete scenario<br />$query<br />".$connect->ErrorMsg());
}

// UPDATE SCENARIO
if (isset($p_subaction) && $p_subaction == "updatescenario" && isset($p_newscenarionum))
{
	$query = "UPDATE {$dbprefix}conditions SET scenario=$p_newscenarionum WHERE qid={$qid} AND scenario={$p_scenario}";
	$result = $connect->Execute($query) or safe_die ("Couldn't delete scenario<br />$query<br />".$connect->ErrorMsg());
}

// DELETE ALL CONDITIONS FOR THIS QUESTION
if (isset($p_subaction) && $p_subaction == "deleteallconditions")
{
	$query = "DELETE FROM {$dbprefix}conditions WHERE qid={$qid}";
	$result = $connect->Execute($query) or safe_die ("Couldn't delete scenario<br />$query<br />".$connect->ErrorMsg());
}

// RENUMBER SCENARIOS
if (isset($p_subaction) && $p_subaction == "renumberscenarios")
{
	$query = "SELECT DISTINCT scenario FROM {$dbprefix}conditions WHERE qid={$qid} ORDER BY scenario";
	$result = $connect->Execute($query) or safe_die ("Couldn't select scenario<br />$query<br />".$connect->ErrorMsg());
	$newindex=1;
	while ($srow = $result->FetchRow())
	{
		$query2 = "UPDATE {$dbprefix}conditions set scenario=$newindex WHERE qid={$qid} AND scenario=".$srow['scenario'].";";
		$result2 = $connect->Execute($query2) or safe_die ("Couldn't renumber scenario<br />$query<br />".$connect->ErrorMsg());
		$newindex++;
	}

}

// COPY CONDITIONS IF THIS IS COPY
if (isset($p_subaction) && $p_subaction == "copyconditions")
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
		$result = db_execute_assoc($query) or safe_die("Couldn't get conditions for copy<br />$query<br />".$connect->ErrorMsg());
		while($row=$result->FetchRow())
		{
			$proformaconditions[]=array("scenario"=>$row['scenario'],
			"cqid"=>$row['cqid'],
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
				."AND scenario='".$pfc['scenario']."'\n"
				."AND cqid='".$pfc['cqid']."'\n"
				."AND cfieldname='".$pfc['cfieldname']."'\n"
				."AND method='".$pfc['method']."'\n"
				."AND value='".$pfc['value']."'";
				$result = $connect->Execute($query) or safe_die("Couldn't check for existing condition<br />$query<br />".$connect->ErrorMsg());
				$count = $result->RecordCount();
				if ($count == 0) //If there is no match, add the condition.
				{
					$query = "INSERT INTO {$dbprefix}conditions ( qid,scenario,cqid,cfieldname,method,value) \n"
					."VALUES ( '$newqid', '".$pfc['scenario']."', '".$pfc['cqid']."',"
					."'".$pfc['cfieldname']."', '".$pfc['method']."',"
					."'".$pfc['value']."')";
					$result=$connect->Execute($query) or safe_die ("Couldn't insert query<br />$query<br />".$connect->ErrorMsg());
					$conditionCopied=true;
				}
				else
				{
					$conditionDuplicated=true;
				}
			}
		}
		if (isset($conditionCopied) && $conditionCopied === true)
		{
			if (isset($conditionDuplicated) && $conditionDuplicated ==true)
			{
				$CopyConditionsMessage = "<font class='warningtitle'>(".$clang->gT("Conditions successfully copied (some were skipped because they were duplicates)").")</font>";
			}
			else
			{
				$CopyConditionsMessage = "<font class='successtitle'>(".$clang->gT("Conditions successfully copied").")</font>";
			}
		}
		else
		{
				$CopyConditionsMessage = "<font class='errortitle'>(".$clang->gT("No conditions could be copied (due to duplicates)").")</font>";
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
//END PROCESS ACTIONS



$cquestions=Array();
$canswers=Array();



//BEGIN: GATHER INFORMATION 
// 1: Get information for this question
if (!isset($qid)) {$qid=returnglobal('qid');}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}
$thissurvey=getSurveyInfo($surveyid);

$query = "SELECT * "
         ."FROM {$dbprefix}questions, "
              ."{$dbprefix}groups "
        ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid "
          ."AND qid=$qid "
          ."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."'" ;
$result = db_execute_assoc($query) or safe_die ("Couldn't get information for question $qid<br />$query<br />".$connect->ErrorMsg());
while ($rows=$result->FetchRow())
{
	$questiongroupname=$rows['group_name'];
	$questiontitle=$rows['title'];
	$questiontext=$rows['question'];
	$questiontype=$rows['type'];
}

// 2: Get all other questions that occur before this question that are pre-determined answer types

// To avoid natural sort order issues,
// first get all questions in natural sort order
// , and find out which number in that order this question is
$qquery = "SELECT * "
        ."FROM {$dbprefix}questions, "
             ."{$dbprefix}groups "
        ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid "
        ."AND {$dbprefix}questions.sid=$surveyid "
          ."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
          ."AND {$dbprefix}groups.language='".GetBaseLanguageFromSurveyID($surveyid)."' " ;

$qresult = db_execute_assoc($qquery) or safe_die ("$qquery<br />".$connect->ErrorMsg());
$qrows = $qresult->GetRows();
// Perform a case insensitive natural sort on group name then question title (known as "code" in the form) of a multidimensional array
usort($qrows, 'CompareGroupThenTitle');

$position="before";
// Go through each question until we reach the current one
foreach ($qrows as $qrow)
{
	if ($qrow["qid"] != $qid && $position=="before")
	{
		// remember all previous questions
		// all question types are supported.
			$questionlist[]=$qrow["qid"];
		}
	elseif ($qrow["qid"] == $qid)
	{
		break;
	}
}

// Now, using the same array which is now properly sorted by group then question
// Create an array of all the questions that appear AFTER the current one
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
$postrows=array();

if (isset($questionlist) && is_array($questionlist))
{
	foreach ($questionlist as $ql)
	{
		$query = "SELECT {$dbprefix}questions.qid, "
			."{$dbprefix}questions.sid, "
			."{$dbprefix}questions.gid, "
			."{$dbprefix}questions.question, "
			."{$dbprefix}questions.type, "
			."{$dbprefix}questions.lid, "
			."{$dbprefix}questions.lid1, "                   
			."{$dbprefix}questions.title, "
			."{$dbprefix}questions.other, "
			."{$dbprefix}questions.mandatory "
			."FROM {$dbprefix}questions, "
			."{$dbprefix}groups "
			."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid "
			."AND {$dbprefix}questions.qid=$ql "
			."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
			."AND {$dbprefix}groups.language='".GetBaseLanguageFromSurveyID($surveyid)."'" ;

		$result=db_execute_assoc($query) or die("Couldn't get question $qid");

		$thiscount=$result->RecordCount();

		// And store again these questions in this array...
		while ($myrows=$result->FetchRow())
		{                   //key => value
			$theserows[]=array("qid"=>$myrows['qid'],
					"sid"=>$myrows['sid'],
					"gid"=>$myrows['gid'],
					"question"=>$myrows['question'],
					"type"=>$myrows['type'],
					"lid"=>$myrows['lid'],
					"lid1"=>$myrows['lid1'],
					"mandatory"=>$myrows['mandatory'],
					"other"=>$myrows['other'],
					"title"=>$myrows['title']);
		}
	}
}

if (isset($postquestionlist) && is_array($postquestionlist))
{
	foreach ($postquestionlist as $pq)
	{
    $query = "SELECT {$dbprefix}questions.qid, "
                   ."{$dbprefix}questions.sid, "
                   ."{$dbprefix}questions.gid, "
                   ."{$dbprefix}questions.question, "
                   ."{$dbprefix}questions.type, "
                   ."{$dbprefix}questions.lid, "
                   ."{$dbprefix}questions.lid1, "                   
                   ."{$dbprefix}questions.title, "
                   ."{$dbprefix}questions.other, "
                   ."{$dbprefix}questions.mandatory "
              ."FROM {$dbprefix}questions, "
                   ."{$dbprefix}groups "
             ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND "
                   ."{$dbprefix}questions.qid=$pq AND "
                   ."{$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."' AND " 
                   ."{$dbprefix}groups.language='".GetBaseLanguageFromSurveyID($surveyid)."'"; 
		

		$result = db_execute_assoc($query) or safe_die("Couldn't get postquestions $qid<br />$query<br />".$connect->ErrorMsg());

		$postcount=$result->RecordCount();

		while($myrows=$result->FetchRow())
		{
      $postrows[]=array("qid"=>$myrows['qid'],
                        "sid"=>$myrows['sid'],
                        "gid"=>$myrows['gid'],
                        "question"=>$myrows['question'],
                        "type"=>$myrows['type'],
                        "lid"=>$myrows['lid'],
                        "lid1"=>$myrows['lid1'],                        
                        "mandatory"=>$myrows['mandatory'],
                        "other"=>$myrows['other'],
                        "title"=>$myrows['title']);
		} // while
	}
	$postquestionscount=count($postrows);
}

$questionscount=count($theserows);

if (isset($postquestionscount) && $postquestionscount > 0)
{ //Build the array used for the questionNav and copyTo select boxes
	foreach ($postrows as $pr)
	{
		$pquestions[]=array("text"=>$pr['title'].": ".substr(strip_tags($pr['question']), 0, 80),
		"fieldname"=>$pr['sid']."X".$pr['gid']."X".$pr['qid']);
	}
}

// Previous question parsing ==> building cquestions[] and canswers[]
if ($questionscount > 0)
{
	$X="X";

	foreach($theserows as $rows)
	{
		$shortquestion=$rows['title'].": ".strip_tags($rows['question']);

		if ($rows['type'] == "A" ||
				$rows['type'] == "B" ||
				$rows['type'] == "C" ||
				$rows['type'] == "E" ||
				$rows['type'] == "F" ||
				$rows['type'] == "H" )
		{
			$aquery="SELECT * "
				."FROM {$dbprefix}answers "
				."WHERE qid={$rows['qid']} "
				."AND {$dbprefix}answers.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
				."ORDER BY sortorder, "
				."answer";

			$aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to Array questions<br />$aquery<br />".$connect->ErrorMsg());

			while ($arows = $aresult->FetchRow())
			{
				$shortanswer = "{$arows['code']}: [" . strip_tags($arows['answer']) . "]";
				$shortquestion=$rows['title'].":$shortanswer ".strip_tags($rows['question']);
				$cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']);

				switch ($rows['type'])
				{
					case "A": //Array 5 buttons
						for ($i=1; $i<=5; $i++)
						{
							$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], $i, $i);
						}
					break;
					case "B": //Array 10 buttons
						for ($i=1; $i<=10; $i++)
						{
							$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], $i, $i);
						}
					break;
					case "C": //Array Y/N/NA
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "Y", $clang->gT("Yes"));
					    $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "U", $clang->gT("Uncertain"));
					    $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "N", $clang->gT("No"));
					break;
					case "E": //Array >/=/<
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "I", $clang->gT("Increase"));
					    $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "S", $clang->gT("Same"));
					    $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "D", $clang->gT("Decrease"));
					break;
					case "F": //Array Flexible Row
					case "H": //Array Flexible Column
						$fquery = "SELECT * "
						."FROM {$dbprefix}labels "
						."WHERE lid={$rows['lid']} "
						."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
						."ORDER BY sortorder, code ";
					$fresult = db_execute_assoc($fquery);
					while ($frow=$fresult->FetchRow())
					{
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], $frow['code'], $frow['title']);
					}
					break;
				}
				// Only Show No-Answer if question is not mandatory
				if ($rows['mandatory'] != 'Y')
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "", $clang->gT("No answer"));
				}

			} //while
		} 
        elseif ($rows['type'] == ":" || $rows['type'] == ";") 
        { // Multiflexi
        
			//Get question attribute for $canswers
		    $qidattributes=getQuestionAttributes($rows['qid'], $rows['type']);
            if (trim($qidattributes['multiflexible_max'])!='') {              
        	    $maxvalue=$qidattributes['multiflexible_max'];
        	} else {
        		$maxvalue=10;
        	}
            if (trim($qidattributes['multiflexible_min'])!='') {              
        	    $minvalue=$qidattributes['multiflexible_min'];
        	} else {
        		$minvalue=1;
        	}
            if (trim($qidattributes['multiflexible_step'])!='') {              
        	    $stepvalue=$qidattributes['multiflexible_step'];
        	} else {
        		$stepvalue=1;
        	}
            
            if ($qidattributes['multiflexible_checkbox']!=0) {
			$minvalue=0;
			$maxvalue=1;
			$stepvalue=1;
		}
			//Get the LIDs
		    $fquery = "SELECT * "
						."FROM {$dbprefix}labels "
						."WHERE lid={$rows['lid']} "
						."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
						."ORDER BY sortorder, code ";
			$fresult = db_execute_assoc($fquery);
			while ($frow=$fresult->FetchRow())
				{
					$lids[$frow['code']]=$frow['title'];
				}
			//Now cycle through the answers
            $aquery="SELECT * "
				."FROM {$dbprefix}answers "
				."WHERE qid={$rows['qid']} "
				."AND {$dbprefix}answers.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
				."ORDER BY sortorder, "
				."answer";
			$aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to Array questions<br />$aquery<br />".$connect->ErrorMsg());

			while ($arows = $aresult->FetchRow())
			{
				foreach($lids as $key=>$val) 
				{
					$shortquestion=$rows['title'].":{$arows['code']}:$key: [".strip_tags($arows['answer']). "][" .strip_tags($val). "] " . strip_tags($rows['question']);
				    $cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']."_".$key);
				if ($rows['type'] == ":")
				{
					for($ii=$minvalue; $ii<=$maxvalue; $ii+=$stepvalue) 
					{
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']."_".$key, $ii, $ii);
					}
				}
				}
			}
			unset($lids);
		} //if A,B,C,E,F,H
		elseif ($rows['type'] == "1") //Multi Scale
		{
			$aquery="SELECT * "
				."FROM {$dbprefix}answers "
				."WHERE qid={$rows['qid']} "
				."AND {$dbprefix}answers.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
				."ORDER BY sortorder, "
				."answer";
			$aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to Array questions<br />$aquery<br />".$connect->ErrorMsg());

			while ($arows = $aresult->FetchRow())
			{
				$attr = getQAttributes($rows['qid']);
				$label1 = isset($attr['dualscale_headerA']) ? $attr['dualscale_headerA'] : 'Label1';
				$label2 = isset($attr['dualscale_headerB']) ? $attr['dualscale_headerB'] : 'Label2';
				$shortanswer = "{$arows['code']}: [" . strip_tags($arows['answer']) . "][$label1]";
				$shortquestion=$rows['title'].":$shortanswer ".strip_tags($rows['question']);
				$cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']."#0");

				$shortanswer = "{$arows['code']}: [" . strip_tags($arows['answer']) . "][$label2]";
				$shortquestion=$rows['title'].":$shortanswer ".strip_tags($rows['question']);
				$cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']."#1");

				// first label
				$lquery="SELECT * "
					."FROM {$dbprefix}labels "
					."WHERE lid={$rows['lid']} "
					."AND {$dbprefix}labels.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
					."ORDER BY sortorder, "
					."lid";
				$lresult=db_execute_assoc($lquery) or safe_die ("Couldn't get labels to Array <br />$lquery<br />".$connect->ErrorMsg());                
				while ($lrows = $lresult->FetchRow())
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']."#0", "{$lrows['code']}", "{$lrows['code']}");
				}

				// second label
				$lquery="SELECT * "
					."FROM {$dbprefix}labels "
					."WHERE lid={$rows['lid1']} "
					."AND {$dbprefix}labels.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
					."ORDER BY sortorder, "
					."lid";
				$lresult=db_execute_assoc($lquery) or safe_die ("Couldn't get labels to Array <br />$lquery<br />".$connect->ErrorMsg());                
				while ($lrows = $lresult->FetchRow())
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']."#1", "{$lrows['code']}", "{$lrows['code']}");
				}

				// Only Show No-Answer if question is not mandatory
				if ($rows['mandatory'] != 'Y')
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']."#0", "", $clang->gT("No answer"));
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']."#1", "", $clang->gT("No answer"));
				}
			} //while
		}
		elseif ($rows['type'] == "K" ||$rows['type'] == "Q") //Multi shorttext/numerical
		{ 
			$aquery="SELECT * "
				."FROM {$dbprefix}answers "
				."WHERE qid={$rows['qid']} "
				."AND {$dbprefix}answers.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
				."ORDER BY sortorder, "
				."answer";
			$aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to Array questions<br />$aquery<br />".$connect->ErrorMsg());

			while ($arows = $aresult->FetchRow())
			{
				$shortanswer = "{$arows['code']}: [" . strip_tags($arows['answer']) . "]";
				$shortquestion=$rows['title'].":$shortanswer ".strip_tags($rows['question']);
				$cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']);

				// Only Show No-Answer if question is not mandatory
				if ($rows['mandatory'] != 'Y')
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "", $clang->gT("No answer"));
				}

			} //while
		}
		elseif ($rows['type'] == "R") //Answer Ranking
		{
			$aquery="SELECT * "
				."FROM {$dbprefix}answers "
				."WHERE qid={$rows['qid']} "
				."AND ".db_table_name('answers').".language='".GetBaseLanguageFromSurveyID($surveyid)."' "
				."ORDER BY sortorder, answer";
			$aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to Ranking question<br />$aquery<br />".$connect->ErrorMsg());
			$acount=$aresult->RecordCount();
			while ($arow=$aresult->FetchRow())
			{
				$theanswer = addcslashes($arow['answer'], "'");
				$quicky[]=array($arow['code'], $theanswer);
			}
			for ($i=1; $i<=$acount; $i++)
			{
				$cquestions[]=array("{$rows['title']}: [RANK $i] ".strip_tags($rows['question']), $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i);
				foreach ($quicky as $qck)
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i, $qck[0], $qck[1]);
				}
				// Only Show No-Answer if question is not mandatory
				if ($rows['mandatory'] != 'Y')
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i, " ", $clang->gT("No answer"));
				}
			}
			unset($quicky);
		} // End if type R
		elseif($rows['type'] == "M" || $rows['type'] == "P")
		{
			$shortanswer = " [".$clang->gT("Group of checkboxes")."]";
			$shortquestion=$rows['title'].":$shortanswer ".strip_tags($rows['question']);
			$cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid']);
			$aquery="SELECT * "
				."FROM {$dbprefix}answers "
				."WHERE qid={$rows['qid']} "
				."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
				."ORDER BY sortorder, "
				."answer";
			$aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to this question<br />$aquery<br />".$connect->ErrorMsg());

			while ($arows=$aresult->FetchRow())
			{
				$theanswer = addcslashes($arows['answer'], "'");
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], $arows['code'], $theanswer);

				$shortanswer = "{$arows['code']}: [" . strip_tags($arows['answer']) . "]";
				$shortanswer .= "[".$clang->gT("Single checkbox")."]";
				$shortquestion=$rows['title'].":$shortanswer ".strip_tags($rows['question']);				
				$cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], "+".$rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']);
				$canswers[]=array("+".$rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], 'Y', 'checked');
				$canswers[]=array("+".$rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], '', 'not checked');
			}
		}
		elseif($rows['type'] == "X") //Boilerplate question
		{
			//Just ignore this questiontype
		}
		else
		{
			$cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid']);
			switch ($rows['type'])
			{
				case "Y": // Y/N/NA
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "Y", $clang->gT("Yes"));
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "N", $clang->gT("No"));
				// Only Show No-Answer if question is not mandatory
				if ($rows['mandatory'] != 'Y')
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
				}
				break;
				case "G": //Gender
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "F", $clang->gT("Female"));
				$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "M", $clang->gT("Male"));
				// Only Show No-Answer if question is not mandatory
				if ($rows['mandatory'] != 'Y')
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
				}
				break;
				case "5": // 5 choice
					for ($i=1; $i<=5; $i++)
					{
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], $i, $i);
					}
				// Only Show No-Answer if question is not mandatory
				if ($rows['mandatory'] != 'Y')
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
				}
				break;
				case "W": // List Flexibel Label Dropdown
				case "Z": // List Flexible Radio Button
					$fquery = "SELECT * FROM {$dbprefix}labels\n"
					. "WHERE lid={$rows['lid']} AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
					. "ORDER BY sortorder, code";

				$fresult = db_execute_assoc($fquery);

				if (!isset($arows['code'])) {$arows['code']='';}  // for some questions types there is no code
				while ($frow=$fresult->FetchRow())
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], $frow['code'], $frow['title']);
				}
				// Only Show No-Answer if question is not mandatory
				if ($rows['mandatory'] != 'Y')
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
				}
				break;

				case "N": // Simple Numerical questions

				// Only Show No-Answer if question is not mandatory
				if ($rows['mandatory'] != 'Y')
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
				}
				break;

				default:
				$aquery="SELECT * "
					."FROM {$dbprefix}answers "
					."WHERE qid={$rows['qid']} "
					."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
					."ORDER BY sortorder, "
					."answer";
				// Ranking question? Replacing "Ranking" by "this"
				$aresult=db_execute_assoc($aquery) or safe_die ("Couldn't get answers to this question<br />$aquery<br />".$connect->ErrorMsg());

				while ($arows=$aresult->FetchRow())
				{
					$theanswer = addcslashes($arows['answer'], "'");
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], $arows['code'], $theanswer);
				}
				if ($rows['type'] == "D")
				{
					// Only Show No-Answer if question is not mandatory
					if ($rows['mandatory'] != 'Y')
					{
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
					}
						}
				elseif ($rows['type'] != "M" &&
						$rows['type'] != "P" &&
						$rows['type'] != "J" &&
						$rows['type'] != "I" )
				{
					// For dropdown questions
					// optinnaly add the 'Other' answer
					if ( ($rows['type'] == "L" ||
						$rows['type'] == "!") &&
						$rows['other'] == "Y")
					{
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "-oth-", $clang->gT("Other"));
					}

					// Only Show No-Answer if question is not mandatory
					if ($rows['mandatory'] != 'Y')
					{
						$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], " ", $clang->gT("No answer"));
					}
				}
				break;
			}//switch row type
		} //else
	} //foreach theserows
} //if questionscount > 0
//END Gather Information for this question

$conditionsoutput .= "\t<tr>\n"
."<td align='center'>\n";

// BEGIN UPDATE THE questionNav SELECT INPUT
$conditionsoutput .= "<script type='text/javascript'>\n"
	."<!--\n";
$conditionsoutput .=  "\t$(\"<optgroup class='activesurveyselect' label='".$clang->gT("Before","js")."'>\").appendTo(\"#questionNav\");\n";
foreach ($theserows as $row)
{
		$question=$row['question'];
		$question=str_replace("\r","",$question);
		$question=str_replace("\n","",$question);
		$question=str_replace("'", "`", $question);
		$question=strip_tags($question);
		if (strlen($question)<35)
		{
			$questionselecter = $question;
		}
		else
		{
			$questionselecter = substr($question, 0, 35)."..";
		}
		$conditionsoutput .=  ""
		. "$(\"<option value='$scriptname?sid=$surveyid&amp;gid={$row['gid']}&amp;qid={$row['qid']}&amp;action=conditions'>{$row['title']}: ".javascript_escape(htmlspecialchars($questionselecter,ENT_NOQUOTES))."</option>\").appendTo(\"#questionNav\");\n";
}
$conditionsoutput .=  "\t$(\"</optgroup>\").appendTo(\"#questionNav\");\n";

$conditionsoutput .=  "\t$(\"<optgroup class='activesurveyselect' label='".$clang->gT("Current","js")."'>\").appendTo(\"#questionNav\");\n";
$question=str_replace("'", "`", $questiontext);
$question=str_replace("\r","",$question);
$question=str_replace("\n","",$question);
$question=strip_tags($question);
if (strlen($question)<35)
{
	$questiontextshort = $question;
}
else
{
	$questiontextshort = substr($question, 0, 35)."..";
}

$conditionsoutput .= "\t$(\"<option value='$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;action=conditions' selected='selected'>$questiontitle: $questiontextshort</option>\").appendTo(\"#questionNav\");\n"; 
$conditionsoutput .=  "\t$(\"</optgroup>\").appendTo(\"#questionNav\");\n";


$conditionsoutput .=  "\t$(\"<optgroup class='activesurveyselect' label='".$clang->gT("After","js")."'>\").appendTo(\"#questionNav\");\n";
foreach ($postrows as $row)
{
		$question=$row['question'];
		$question=str_replace("'", "`", $question);
		$question=str_replace("\r","",$question);
		$question=str_replace("\n","",$question);
		$question=strip_tags($question);
		if (strlen($question)<35)
		{
			$questionselecter = $question;
		}
		else
		{
			$questionselecter = substr($question, 0, 35)."..";
		}
		$conditionsoutput .=  ""
		. "$(\"<option value='$scriptname?sid=$surveyid&amp;gid={$row['gid']}&amp;qid={$row['qid']}&amp;action=conditions'>{$row['title']}: ".javascript_escape(htmlspecialchars($questionselecter,ENT_NOQUOTES))."</option>\").appendTo(\"#questionNav\");\n";
}
$conditionsoutput .=  "\t$(\"</optgroup>\").appendTo(\"#questionNav\");\n";

$conditionsoutput .=  "-->\n"
		."</script>\n";
// END UPDATE THE questionNav SELECT INPUT

//Now display the information and forms
//BEGIN: PREPARE JAVASCRIPT TO SHOW MATCHING ANSWERS TO SELECTED QUESTION
$conditionsoutput .= "<script type='text/javascript'>\n"
."<!--\n"
."\tvar Fieldnames = new Array();\n"
."\tvar Codes = new Array();\n"
."\tvar Answers = new Array();\n"
."\tvar QFieldnames = new Array();\n"
."\tvar Qcqids = new Array();\n"
."\tvar Qtypes = new Array();\n";
$jn=0;
if (isset($canswers))
{
	foreach($canswers as $can)
	{
		$an=str_replace("'", "`", $can[2]);
		$an=str_replace("\r", " ", $an);
		$an=str_replace("\n", " ", $an);
		$an=strip_tags($an);
		$conditionsoutput .= "Fieldnames[$jn]='$can[0]';\n"
		."Codes[$jn]='$can[1]';\n"
		."Answers[$jn]='$an';\n";
		$jn++;
	}
}
$jn=0;

if (isset($cquestions))
{
	foreach ($cquestions as $cqn)
	{
		$conditionsoutput .= "QFieldnames[$jn]='$cqn[3]';\n"
		."Qcqids[$jn]='$cqn[1]';\n"
		."Qtypes[$jn]='$cqn[2]';\n";
		$jn++;
	}
}

//  record a JS variable to let jQuery know if survey is Anonymous
if ($thissurvey['private'] == 'Y')
{
	$conditionsoutput .= "isAnonymousSurvey = true;";
}
else
{
	$conditionsoutput .= "isAnonymousSurvey = false;";
}

$conditionsoutput .= "//-->\n"
."</script>\n";

$conditionsoutput .= "</td></tr>\n";
//END: PREPARE JAVASCRIPT TO SHOW MATCHING ANSWERS TO SELECTED QUESTION

//BEGIN DISPLAY CONDITIONS FOR THIS QUESTION
if ($subaction=='' ||
	$subaction=='editconditionsform' || $subaction=='insertcondition' ||
	$subaction == "editthiscondition" || $subaction == "delete" ||
	$subaction == "updatecondition" || $subaction == "deletescenario" ||
	$subaction == "renumberscenarios" || $subaction == "deleteallconditions" ||
	$subaction == "updatescenario" ||
	$subaction=='copyconditionsform' || $subaction=='copyconditions')
{
	$conditionsoutput .= "<tr><td>\n";

	//3: Get other conditions currently set for this question
	$conditionscount=0;
	$s=0;
	$scenarioquery = "SELECT DISTINCT {$dbprefix}conditions.scenario "
		."FROM {$dbprefix}conditions "
		."WHERE {$dbprefix}conditions.qid=$qid\n"
		."ORDER BY {$dbprefix}conditions.scenario";
	$scenarioresult = db_execute_assoc($scenarioquery) or safe_die ("Couldn't get other (scenario) conditions for question $qid<br />$query<br />".$connect->Error);
	$scenariocount=$scenarioresult->RecordCount();

	$conditionsoutput .= "<table width='100%' align='center' cellspacing='0' cellpadding='0'>\n"
		."\t<tr bgcolor='#E1FFE1'>\n"
		."<td><table align='center' width='100%' cellspacing='0'><tr>\n";
	$showreplace="$questiontitle". showSpeaker($questiontext);
	$onlyshow=str_replace("{QID}", $showreplace, $clang->gT("Only show question {QID} IF"));


	if ($subaction== "editconditionsform" || $subaction=='insertcondition' ||
		$subaction == "editthiscondition" || $subaction == "delete" ||
		$subaction == "updatecondition" || $subaction == "deletescenario" ||
		$subaction == "updatescenario" ||
		$subaction == "renumberscenarios")
	{
		$conditionsoutput .= "\t<td align='center' width='90%'><strong>$onlyshow</strong>\n"
			."</td>\n"
			."<td width='10%' align='right' valign='middle'><form id='deleteallconditions' action='$scriptname?action=conditions' method='post' name='deleteallconditions' style='margin-bottom:0;'>\n"
			."<input type='hidden' name='qid' value='$qid' />\n"
			."<input type='hidden' name='gid' value='$gid' />\n"
			."<input type='hidden' name='sid' value='$surveyid' />\n"
			."<input type='hidden' id='toplevelsubaction' name='subaction' value='deleteallconditions' />\n";


		if ($scenariocount > 0)
		{ // show the Delete all conditions for this question button
			$conditionsoutput .= "<a href='#' "
				. " onclick=\"if ( confirm('".$clang->gT("Are you sure you want to delete all conditions set to the questions you have selected?","js")."')) {document.getElementById('deleteallconditions').submit();}\""
				." title='".$clang->gTview("Delete all conditions")."' >"
				." <img src='$imagefiles/conditions_deleteall.png'  alt='".$clang->gT("Delete all conditions")."' name='DeleteAllConditionsImage' /></a>\n";
		}

		if ($scenariocount > 1)
		{ // show the renumber scenario button for this question
		$conditionsoutput .= "<a href='#' "
			. " onclick=\"if ( confirm('".$clang->gT("Are you sure you want to renumber the scenarios with incremented numbers beginning from 1?","js")."')) {document.getElementById('toplevelsubaction').value='renumberscenarios'; document.getElementById('deleteallconditions').submit();}\""
			." title='".$clang->gTview("Renumber scenario automatically")."' >"
			." <img src='$imagefiles/scenario_renumber.png'  alt='".$clang->gT("Renumber scenario automatically")."' name='renumberscenarios' /></a>\n";
		}
	}
	else
	{
		$conditionsoutput .= "\t<td align='center'><strong>$onlyshow</strong>\n"
			."<form id='deleteallconditions' action='$scriptname?action=conditions' method='post' name='deleteallconditions' style='margin-bottom:0;'>\n"
			."<input type='hidden' name='qid' value='$qid' />\n"
			."<input type='hidden' name='sid' value='$surveyid' />\n"
			."<input type='hidden' id='toplevelsubaction' name='subaction' value='deleteallconditions' />\n";
	}

	$conditionsoutput .= "</form></td></tr></table>\n"
		."\t</td></tr>\n"; 

	if ($scenariocount > 0)
	{
        $js_adminheader_includes[]= $homeurl.'/scripts/conditions.js';
        $js_adminheader_includes[]= $rooturl.'/scripts/jquery/jquery-checkgroup.js';
		while ($scenarionr=$scenarioresult->FetchRow())
		{
			$scenariotext = "";
			if ($s == 0 && $scenariocount > 1)
			{ 
				$scenariotext = " -------- <i>Scenario {$scenarionr['scenario']}</i> --------";
			}
			if ($s > 0) 
			{ 
				$scenariotext = " -------- <i>".$clang->gT("OR")." Scenario {$scenarionr['scenario']}</i> --------";
			}
			if ($subaction == "copyconditionsform" || $subaction == "copyconditions")
			{
				$initialCheckbox = "<td><input type='checkbox' id='scenarioCbx{$scenarionr['scenario']}'/>\n"
					."<script type='text/javascript'>$('#scenarioCbx{$scenarionr['scenario']}').checkgroup({groupName:'aConditionFromScenario{$scenarionr['scenario']}'});</script>"
					."</td><td>&nbsp;</td>\n";
			}
			else
			{
				$initialCheckbox = "";
			}

			$conditionsoutput .= "<tr><td>\n"
				."<table width='100%' cellspacing='0'><tr>$initialCheckbox<td width='90%'>$scenariotext&nbsp;\n"
				."<form action='$scriptname?action=conditions' method='post' id='editscenario{$scenarionr['scenario']}' style='display: none'>\n"
				."<label>".$clang->gT("New scenario number").":&nbsp;\n"
				."<input type='text' name='newscenarionum' size='3'/></label>\n"
				."<input type='hidden' name='scenario' value='{$scenarionr['scenario']}'/>\n"
				."<input type='hidden' name='sid' value='$surveyid' />\n"
				."<input type='hidden' name='gid' value='$gid' />\n"
				."<input type='hidden' name='qid' value='$qid' />\n"
				."<input type='hidden' name='subaction' value='updatescenario' />&nbsp;&nbsp;\n"
				."<input type='submit' name='scenarioupdated' value='".$clang->gT("Update scenario")."' />\n"
				."<input type='button' name='cancel' value='".$clang->gT("Cancel")."' onclick=\"$('#editscenario{$scenarionr['scenario']}').hide('slow');\"/>\n"
				."</form></td>\n"
				. "<td width='10%' valign='middle' align='right'><form id='deletescenario{$scenarionr['scenario']}' action='$scriptname?action=conditions' method='post' name='deletescenario{$scenarionr['scenario']}' style='margin-bottom:0;'>\n";

			if ($scenariotext != "" && ($subaction == "editconditionsform" ||$subaction == "insertcondition" ||
					$subaction == "updatecondition" || $subaction == "editthiscondition" || 
					$subaction == "renumberscenarios" || $subaction == "updatescenario" ||
					$subaction == "deletescenario" || $subaction == "delete") )
			{
				$conditionsoutput .= "\t<a href='#' "
						." onclick=\"if ( confirm('".$clang->gT("Are you sure you want to delete all conditions set in this scenario?","js")."')) {document.getElementById('deletescenario{$scenarionr['scenario']}').submit();}\""
                        ." title='".$clang->gTview("Delete this scenario")."' >"
						." <img src='$imagefiles/scenario_delete.png' ".$clang->gT("Delete this scenario")." name='DeleteWholeGroup' /></a>\n";

				$conditionsoutput .= "\t<a href='#' "
						." id='editscenariobtn{$scenarionr['scenario']}'" 
						." onclick=\"$('#editscenario{$scenarionr['scenario']}').toggle('slow');\""
                        ." title='".$clang->gTview("Edit scenario")."' >"
						." <img src='$imagefiles/scenario_edit.png' alt='".$clang->gT("Edit scenario")."' name='DeleteWholeGroup' /></a>\n";

			}

			$conditionsoutput .= "\t<input type='hidden' name='scenario' value='{$scenarionr['scenario']}' />\n"
				."\t<input type='hidden' name='qid' value='$qid' />\n"
				."\t<input type='hidden' name='sid' value='$surveyid' />\n"
				."\t<input type='hidden' name='subaction' value='deletescenario' />\n"
				."</form></td></tr></table></td></tr>\n";

			unset($currentfield);

			$query = "SELECT {$dbprefix}conditions.cid, "
				."{$dbprefix}conditions.scenario, "
				."{$dbprefix}conditions.cqid, "
				."{$dbprefix}conditions.cfieldname, "
				."{$dbprefix}conditions.method, "
				."{$dbprefix}conditions.value, "
				."{$dbprefix}questions.type "
				."FROM {$dbprefix}conditions, "
				."{$dbprefix}questions, "
				."{$dbprefix}groups "
				."WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid "
				."AND {$dbprefix}questions.gid={$dbprefix}groups.gid "
				."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
				."AND {$dbprefix}conditions.qid=$qid "
				."AND {$dbprefix}conditions.scenario={$scenarionr['scenario']}\n"
				."AND {$dbprefix}conditions.cfieldname NOT LIKE '{%' \n" // avoid catching SRCtokenAttr conditions
				."ORDER BY {$dbprefix}groups.group_order,{$dbprefix}questions.question_order"; 
			$result = db_execute_assoc($query) or safe_die ("Couldn't get other conditions for question $qid<br />$query<br />".$connect->ErrorMsg());
			$conditionscount=$result->RecordCount();

			$querytoken = "SELECT {$dbprefix}conditions.cid, "
				."{$dbprefix}conditions.scenario, "
				."{$dbprefix}conditions.cqid, "
				."{$dbprefix}conditions.cfieldname, "
				."{$dbprefix}conditions.method, "
				."{$dbprefix}conditions.value, "
				."'' AS type "
				."FROM {$dbprefix}conditions "
				."WHERE "
				." {$dbprefix}conditions.qid=$qid "
				."AND {$dbprefix}conditions.scenario={$scenarionr['scenario']}\n"
				."AND {$dbprefix}conditions.cfieldname LIKE '{%' \n" // only catching SRCtokenAttr conditions
				."ORDER BY {$dbprefix}conditions.cfieldname";
			$resulttoken = db_execute_assoc($querytoken) or safe_die ("Couldn't get other conditions for question $qid<br />$query<br />".$connect->ErrorMsg());
			$conditionscounttoken=$resulttoken->RecordCount();

			$conditionscount=$conditionscount+$conditionscounttoken;

			// this array will be used soon,
			// to explain wich conditions is used to evaluate the question
			$method = array( "<"  => $clang->gT("Less than"),
					"<=" => $clang->gT("Less than or equal to"),
					"==" => $clang->gT("equals"),
					"!=" => $clang->gT("Not equal to"),
					">=" => $clang->gT("Greater than or equal to"),
					">"  => $clang->gT("Greater than"),
					"RX" => $clang->gT("Regular expression")
				       );

			if ($conditionscount > 0)
			{
				$aConditionsMerged=Array();
				while ($arow=$resulttoken->FetchRow())
				{
					$aConditionsMerged[]=$arow;
				}
				while ($arow=$result->FetchRow())
				{
					$aConditionsMerged[]=$arow;
				}
				
//				while ($rows=$result->FetchRow())
				foreach ($aConditionsMerged as $rows)
				{
					if($rows['method'] == "") {$rows['method'] = "==";} //Fill in the empty method from previous versions
					$markcidstyle="";
					if (array_search($rows['cid'], $markcidarray) === FALSE) // PHP5
						// === required cause key 0 would otherwise be interpreted as FALSE
					{
						$markcidstyle="";
					}
					else {
						// This is the style used when the condition editor is called
						// in order to check which conditions prevent a question deletion
						$markcidstyle="background-color: #5670A1;";
					}
					if ($subaction == "editthiscondition" && isset($p_cid) &&
							$rows['cid'] === $p_cid)
					{
						// Style used when editing a condition
						$markcidstyle="background-color: #FCCFFF;";
					}

					if (isset($currentfield) && $currentfield != $rows['cfieldname'])
					{
						$conditionsoutput .= "<tr class='evenrow'>\n"
							."\t<td valign='middle' align='center'>\n"
							."<font size='1'><strong>"
							.$clang->gT("and")."</strong></font></td></tr>";
					}
					elseif (isset($currentfield))
					{
						$conditionsoutput .= "<tr class='evenrow'>\n"
							."\t<td valign='top' align='center'>\n"
							."<font size='1'><strong>"
							.$clang->gT("OR")."</strong></font></td></tr>";
					}
					$conditionsoutput .= "\t<tr class='oddrow' style='$markcidstyle'>\n"
						."\t<td><form style='margin-bottom:0;' name='conditionaction{$rows['cid']}' id='conditionaction{$rows['cid']}' method='post' action='$scriptname?action=conditions'>\n"
						."<table width='100%' style='height: 13px;' cellspacing='0' cellpadding='0'>\n"
						."\t<tr>\n";

					if ( $subaction == "copyconditionsform" || $subaction == "copyconditions")
					{
						$conditionsoutput .= "<td>&nbsp;&nbsp;</td>"
							. "<td valign='middle' align='right'>\n"
							. "\t<input type='checkbox' name='aConditionFromScenario{$scenarionr['scenario']}' id='cbox{$rows['cid']}' value='{$rows['cid']} '/>\n"
							. "</td>\n";
					}
					$conditionsoutput .= ""
						."<td valign='middle' align='right' width='40%'>\n"
						."\t<font size='1' face='verdana'>\n";
			
					$leftOperandType = 'unknown'; // prevquestion, tokenattr                                                     
					if ($thissurvey['private'] != 'Y' && preg_match('/^{TOKEN:([^}]*)}$/',$rows['cfieldname'],$extractedTokenAttr) > 0)                   
					{
						$leftOperandType = 'tokenattr';
						$aTokenAttrNames=GetTokenFieldsAndNames($surveyid);
						if (count($aTokenAttrNames) != 0)
						{
							$thisAttrName=html_escape($aTokenAttrNames[strtolower($extractedTokenAttr[1])])." [".$clang->gT("From token table")."]";
						}
						else
						{
							$thisAttrName=html_escape($extractedTokenAttr[1])." [".$clang->gT("Inexistant token table")."]";
						}
						$conditionsoutput .= "\t$thisAttrName\n";
						// TIBO not sure this is used anymore !!
						$conditionsList[]=array("cid"=>$rows['cid'],
								"text"=>$thisAttrName);
					}
					else
					{
						$leftOperandType = 'prevquestion';
					foreach ($cquestions as $cqn)
					{
						if ($cqn[3] == $rows['cfieldname'])
						{
							$conditionsoutput .= "\t$cqn[0] (qid{$rows['cqid']})\n";
							$conditionsList[]=array("cid"=>$rows['cid'],
									"text"=>$cqn[0]." ({$rows['value']})");
						}
						else
						{
							//$conditionsoutput .= "\t<font color='red'>ERROR: Delete this condition. It is out of order.</font>\n";
						}
					}
					}

					$conditionsoutput .= "\t</font></td>\n"
						."\t<td align='center' valign='middle' width='20%'>\n"
						."<font size='1'>\n" //    .$clang->gT("Equals")."</font></td>"
						.$method[trim ($rows['method'])]
						."</font>\n"
						."\t</td>\n"
						."\n"
						."\t<td align='left' valign='middle' width='30%'>\n"
						."<font size='1' face='verdana'>\n";

					// let's read the condition's right operand
					// determine its type and display it
					$rightOperandType = 'unknown'; // predefinedAnsw,constantVal, prevQsgqa, tokenAttr, regexp
					if ($rows['method'] == 'RX')
					{
						$rightOperandType = 'regexp';
						$conditionsoutput .= "".html_escape($rows['value'])."\n";
					}
					elseif (preg_match('/^@([0-9]+X[0-9]+X[^@]*)@$/',$rows['value'],$matchedSGQA) > 0)
					{ // SGQA
						$rightOperandType = 'prevQsgqa';
						$textfound=false;
						foreach ($cquestions as $cqn)
						{
							if ($cqn[3] == $matchedSGQA[1])
							{
								$matchedSGQAText=$cqn[0];
								$textfound=true;
								break;
							}
						}
						if ($textfound === false)
						{
							$matchedSGQAText=$rows['value'].' ('.$clang->gT("Not found").')';
						}
				
						$conditionsoutput .= "".html_escape($matchedSGQAText)."\n";
					}
					elseif ($thissurvey['private'] != 'Y' && preg_match('/^{TOKEN:([^}]*)}$/',$rows['value'],$extractedTokenAttr) > 0)
					{
						$rightOperandType = 'tokenAttr';
						$aTokenAttrNames=GetTokenFieldsAndNames($surveyid);
						if (count($aTokenAttrNames) != 0)
						{
							$thisAttrName=html_escape($aTokenAttrNames[strtolower($extractedTokenAttr[1])])." [".$clang->gT("From token table")."]";
						}
						else
						{
							$thisAttrName=html_escape($extractedTokenAttr[1])." [".$clang->gT("Inexistant token table")."]";
						}
						$conditionsoutput .= "\t$thisAttrName\n";
					}
					elseif (isset($canswers))
					{
						foreach ($canswers as $can)
						{
							if ($can[0] == $rows['cfieldname'] && $can[1] == $rows['value'])
							{
								$conditionsoutput .= "$can[2] ($can[1])\n";
								$rightOperandType = 'predefinedAnsw';
								
							}
						}
					}
					// if $rightOperandType is still unkown then it is a simple constant
					if ($rightOperandType == 'unknown')
					{
						$rightOperandType = 'constantVal';
						if ($rows['value'] == ' ' ||
								$rows['value'] == '')
						{
							$conditionsoutput .= "".$clang->gT("No answer")."\n";
						} 
						else
						{
							$conditionsoutput .= "".html_escape($rows['value'])."\n";
						}
					}

					$conditionsoutput .= "\t</font></td>\n"
						."\t<td align='right' valign='middle' width='10%'>\n";

					if ($subaction == "editconditionsform" ||$subaction == "insertcondition" ||
						$subaction == "updatecondition" || $subaction == "editthiscondition" || 
						$subaction == "renumberscenarios" || $subaction == "deleteallconditions" || 
						$subaction == "updatescenario" ||
						$subaction == "deletescenario" || $subaction == "delete")
					{ // show single condition action buttons in edit mode
						$conditionsoutput .= ""
							."<a href='#' "
							." onclick=\"if ( confirm('".$clang->gT("Are you sure you want to delete this condition?","js")."')) {\$('#editModeTargetVal{$rows['cid']}').remove();\$('#cquestions{$rows['cid']}').remove();document.getElementById('conditionaction{$rows['cid']}').submit();}\""
							." title='".$clang->gTview("Delete this condition")."' >"
							." <img src='$imagefiles/conditions_delete.png'  alt='".$clang->gT("Delete this condition")."' name='DeleteThisCondition' title='' /></a>\n"
							."<a href='#' "
							." onclick='document.getElementById(\"subaction{$rows['cid']}\").value=\"editthiscondition\";document.getElementById(\"conditionaction{$rows['cid']}\").submit();'>" 
							." <img src='$imagefiles/conditions_edit.png'  alt='".$clang->gT("Edit this condition")."' name='EditThisCondition' /></a>\n"
							."\t<input type='hidden' name='subaction' id='subaction{$rows['cid']}' value='delete' />\n"
							."\t<input type='hidden' name='cid' value='{$rows['cid']}' />\n"
							."\t<input type='hidden' name='scenario' value='{$rows['scenario']}' />\n"
//							."\t<input type='hidden' id='cquestions{$rows['cid']}'  name='cquestions' value='{$rows['cfieldname']}' />\n"
							."\t<input type='hidden' name='method' value='{$rows['method']}' />\n"
							."\t<input type='hidden' name='sid' value='$surveyid' />\n"
							."\t<input type='hidden' name='gid' value='$gid' />\n"
							."\t<input type='hidden' name='qid' value='$qid' />\n";
						// now sets e corresponding hidden input field
						// depending on the leftOperandType		
						if ($leftOperandType == 'tokenattr')
						{
							$conditionsoutput .= ""
							."\t<input type='hidden' id='csrctoken{$rows['cid']}' name='csrctoken' value='".html_escape($rows['cfieldname'])."' />\n";
						}
						else
						{
							$conditionsoutput .= ""
							."\t<input type='hidden' id='cquestions{$rows['cid']}' name='cquestions' value='".html_escape($rows['cfieldname'])."' />\n";
						}
				
						// now set the corresponding hidden input field
						// depending on the rightOperandType
						// This is used when Editting a condition
						if ($rightOperandType == 'predefinedAnsw')
						{
							$conditionsoutput .= ""
							."\t<input type='hidden' name='EDITcanswers[]' id='editModeTargetVal{$rows['cid']}' value='".html_escape($rows['value'])."' />\n";
					}
						elseif ($rightOperandType == 'prevQsgqa')
						{
							$conditionsoutput .= ""
							."\t<input type='hidden' id='editModeTargetVal{$rows['cid']}' name='EDITprevQuestionSGQA' value='".html_escape($rows['value'])."' />\n";
						}
						elseif ($rightOperandType == 'tokenAttr')
						{
							$conditionsoutput .= ""
							."\t<input type='hidden' id='editModeTargetVal{$rows['cid']}' name='EDITtokenAttr' value='".html_escape($rows['value'])."' />\n";
						}
						elseif ($rightOperandType == 'regexp')
						{
							$conditionsoutput .= ""
							."\t<input type='hidden' id='editModeTargetVal{$rows['cid']}' name='EDITConditionRegexp' value='".html_escape($rows['value'])."' />\n";
						}
						else
						{
							$conditionsoutput .= ""
							."\t<input type='hidden' id='editModeTargetVal{$rows['cid']}' name='EDITConditionConst' value='".html_escape($rows['value'])."' />\n";
						}
					}

					$conditionsoutput .= ""
						."\t</td>\n"
						."\t</table></form>\n"
						."\t</tr>\n";
					$currentfield=$rows['cfieldname'];
				}
				$conditionsoutput .= "\t<tr>\n"
					."<td height='3'>\n"
					."</td>\n"
					."\t</tr>\n";
			}
			else
			{
				$conditionsoutput .= "\t<tr>\n"
					."<td colspan='3' height='3'>\n"
					."</td>\n"
					."\t</tr>\n";
			}
			$s++;
		}
	}
	else
	{ // no condition ==> disable delete all conditions button, and display a simple comment
		$conditionsoutput .= "<tr><td valign='middle' align='center'>".$clang->gT("This question is always shown.")."\n"
			. "</td></tr>\n";
	}
	$conditionsoutput .= ""
		. "</table>\n";

	$conditionsoutput .= "</td></tr>\n";
}
//END DISPLAY CONDITIONS FOR THIS QUESTION


// Separator
$conditionsoutput .= "\t<tr bgcolor='#555555'><td colspan='3'></td></tr>\n";


// BEGIN: DISPLAY THE COPY CONDITIONS FORM
if ($subaction == "copyconditionsform" || $subaction == "copyconditions")
{
	$conditionsoutput .= "<tr class=''><td colspan='3'><form action='$scriptname?action=conditions' name='copyconditions' id='copyconditions' method='post'>\n";

	$conditionsoutput .= "\t<table width='100%' cellpadding='5' cellspacing='0'><tr>\n"
		."<td colspan='3' align='center' class='settingcaption'>\n"
		."<strong>"
		.$clang->gT("Copy conditions")."</strong>";

	if (isset ($CopyConditionsMessage))
	{
		$conditionsoutput .= " $CopyConditionsMessage";
	}
	//CopyConditionsMessage
	$conditionsoutput .=  "\n"
		."</td>\n"
		."\t</tr>\n";

	if (isset($conditionsList) && is_array($conditionsList))
	{

		$conditionsoutput .= "\t<tr bgcolor='#EFEFEF'>\n"
			."<td align='center' style='text-align: center' width='250'>\n"
			."".$clang->gT("Copy the selected conditions to").":\n"
			."</td>\n"
			."<td align='left'>\n"
			."<select name='copyconditionsto[]' multiple style='font-family:verdana; font-size:10; width:600px' size='10'>\n";
		if (isset($pquestions) && count($pquestions) != 0)
		{
			foreach ($pquestions as $pq)
			{
				$conditionsoutput .= "<option value='{$pq['fieldname']}'>".$pq['text']."</option>\n";
			}
		}
		$conditionsoutput .= "</select>\n";
		$conditionsoutput .= "</td>\n"
			."\t</tr>\n";

		if ( !isset($pquestions) || count($pquestions) == 0)
		{
			$disableCopyCondition=" disabled='disabled'";
		}
		else
		{
			$disableCopyCondition=" ";
		}
		$conditionsoutput .= "\t<tr><td colspan='3' align='center'>\n"
			."<input type='submit' value='".$clang->gT("Copy conditions")."' onclick=\"if (confirm('".$clang->gT("Are you sure you want to copy these condition(s) to the questions you have selected?","js")."')){prepareCopyconditions(); return true;} else {return false;}\" $disableCopyCondition/>"
			."\n";

		$conditionsoutput .= "<input type='hidden' name='subaction' value='copyconditions' />\n"
			."<input type='hidden' name='sid' value='$surveyid' />\n"
			."<input type='hidden' name='gid' value='$gid' />\n"
			."<input type='hidden' name='qid' value='$qid' />\n";

		$conditionsoutput .= "<script type=\"text/javascript\">\n"
			."function prepareCopyconditions()\n"
			."{\n"
			."\t$(\"input:checked[name^='aConditionFromScenario']\").each(function(i,val)\n"
			."\t{\n"
			."var thecid = val.value;\n"
			."var theform = document.getElementById('copyconditions');\n"
			."addHiddenElement(theform,'copyconditionsfrom[]',thecid);\n"
			."return true;\n"
			."\t});\n"
			."}\n"
			."</script>\n"
			."</td></tr>";

	}
	else
	{
		$conditionsoutput .= "\t<tr bgcolor='#EFEFEF'>\n"
			."<th width='40%'>".$clang->gT("Condition")."</th><th width='200'></th><th width='40%'>".$clang->gT("Question")."</th>\n"
			."\t</tr>\n";
	}
			$conditionsoutput .= "</table></form></td></tr>\n";

		$conditionsoutput .= "\t<tr ><td colspan='3'></td></tr>\n"
			."\t<tr bgcolor='#555555'><td colspan='3'></td></tr>\n";
}
// END: DISPLAY THE COPY CONDITIONS FORM

if ( isset($cquestions) )
{
	if ( count($cquestions) > 0 && count($cquestions) <=10)
	{
		$qcount = count($cquestions);
	}
	else
	{
		$qcount = 9;
	}
}
else
{
	$qcount = 0;
}


//BEGIN: DISPLAY THE ADD or EDIT CONDITION FORM
if ($subaction == "editconditionsform" || $subaction == "insertcondition" ||
	$subaction == "updatecondition" || $subaction == "deletescenario" ||
	$subaction == "renumberscenarios" || $subaction == "deleteallconditions" ||
	$subaction == "updatescenario" ||
	$subaction == "editthiscondition" || $subaction == "delete")
{
	$conditionsoutput .= "<tr><td colspan='3'>\n";
	$conditionsoutput .= "<form action='$scriptname?action=conditions' name='editconditions' id='editconditions' method='post'>\n";
	$conditionsoutput .= "<table width='100%' align='center' cellspacing='0' cellpadding='5'>\n";
	if ($subaction == "editthiscondition" &&  isset($p_cid))
	{
		$mytitle = $clang->gT("Edit condition");
	}
	else
	{
		$mytitle = $clang->gT("Add condition");
	}

	$conditionsoutput .= "\t<tr class='settingcaption'>\n"
		."<td colspan='2' align='center'>\n"
		."\t<strong>".$mytitle."</strong>\n"
		."</td>\n"
		."\t</tr>\n"
		."\t<tr bgcolor='#EFEFEF'>\n"
		."<th width='25%'></th>\n"
		."<th width='75%'></th>\n"
		."\t</tr>\n";

	if  ( ( $subaction != "editthiscondition" && isset($scenariocount) && ($scenariocount == 1 || $scenariocount==0)) ||
		( $subaction == "editthiscondition" && isset($scenario) && $scenario == 1) )
	{
		$scenarioAddBtn = "\t<a id='scenarioaddbtn' href='#' title='".$clang->gTview('Add scenario')."' onclick=\"$('#scenarioaddbtn').hide();$('#defaultscenariotxt').hide('slow');$('#scenario').show('slow');\">"
                         ."<img src='$imagefiles/plus.png' alt='".$clang->gT('Add scenario')."' /></a>\n";
		$scenarioTxt = "<span id='defaultscenariotxt'>".$clang->gT("Default scenario")."</span>";
		$scenarioInputStyle = "style = 'display: none;'";
	}
	else
	{
		$scenarioAddBtn = "";
		$scenarioTxt = "";
		$scenarioInputStyle = "style = ''";
	}

	$conditionsoutput .= "\t<tr class='conditiontbl'>\n"
		. "<td align='right' valign='bottom'>$scenarioAddBtn&nbsp;".$clang->gT("Scenario")."</td>\n"
		. "<td valign='bottom'><input type='text' name='scenario' id='scenario' value='1' size='2' $scenarioInputStyle/>"
		. "$scenarioTxt</td>\n"
		. "\t</tr>\n"
		. "\t<tr class='conditiontbl'>\n";

	// Source condition selection
	$conditionsoutput .= ""
		. "<td align='right' valign='middle'>".$clang->gT("Question")."</td>\n"
		."<td valign='top' align='left'>\n"
		."\t<div id=\"conditionsource\" class=\"tabs-nav\">\n"
		."\t<ul>\n"
		."\t<li><a href=\"#SRCPREVQUEST\"><span>".$clang->gT("Previous questions")."</span></a></li>\n"
		."\t<li><a href=\"#SRCTOKENATTRS\"><span>".$clang->gT("Token")."</span></a></li>\n"
		."\t</ul>\n";
		
	// Previous question tab
	$conditionsoutput .= "<div id='SRCPREVQUEST'><select name='cquestions' id='cquestions' style='width:600px;font-family:verdana; font-size:10;' size='".($qcount+1)."' >\n";
	if (isset($cquestions))
	{
		$js_getAnswers_onload = "";
		foreach ($cquestions as $cqn)
		{
			$conditionsoutput .= "<option value='$cqn[3]' title=\"".htmlspecialchars($cqn[0])."\"";
			if (isset($p_cquestions) && $cqn[3] == $p_cquestions) {
				$conditionsoutput .= " selected";
				if (isset($p_canswers))
				{
					$canswersToSelect = "";
						foreach ($p_canswers as $checkval)
						{
						$canswersToSelect .= ";$checkval";
						}
					$canswersToSelect = substr($canswersToSelect,1);
					$js_getAnswers_onload .= "$('#canswersToSelect').val('$canswersToSelect');\n";
				}
			}
			$conditionsoutput .= ">$cqn[0]</option>\n";
		}
	}

	$conditionsoutput .= "</select>\n"
		."</div>\n";

	// Source token Tab
	$conditionsoutput .= "<div id='SRCTOKENATTRS'><select name='csrctoken' id='csrctoken' style='width:600px;font-family:verdana; font-size:10;' size='".($qcount+1)."' >\n";
	foreach (GetTokenFieldsAndNames($surveyid) as $tokenattr => $tokenattrName)
	{
		// Check to select
		if (isset($p_csrctoken) && $p_csrctoken == '{TOKEN:'.strtoupper($tokenattr).'}')
		{
			$selectThisSrcTokenAttr = "selected=\"selected\"";
		}
		else
		{
			$selectThisSrcTokenAttr = "";
		}
		$conditionsoutput .= "<option value='{TOKEN:".strtoupper($tokenattr)."}' $selectThisSrcTokenAttr>".html_escape($tokenattrName)."</option>\n";
	}

	$conditionsoutput .= "</select>\n"
		."</div>\n\n";

	 $conditionsoutput .= "\t</div>\n" // End Source tabs
		. "\t</td>\n"
		. "\t</tr>\n"
		. "\t<tr class='conditiontbl'>\n"
		. "<td align='right' valign='middle'>".$clang->gT("Comparison operator")."</td>\n"
		. "<td><select name='method' id='method' style='font-family:verdana; font-size:10' >\n"
		. "\t<option value='<'>".$clang->gT("Less than")."</option>\n"
		. "\t<option value='<='>".$clang->gT("Less than or equal to")."</option>\n"
		. "\t<option selected='selected' value='=='>".$clang->gT("Equals")."</option>\n"	
		. "\t<option value='!='>".$clang->gT("Not equal to")."</option>\n"	
		. "\t<option value='>='>".$clang->gT("Greater than or equal to")."</option>\n"
		. "\t<option value='>'>".$clang->gT("Greater than")."</option>\n"
		. "\t<option value='RX'>".$clang->gT("Regular expression")."</option>\n"
		. "</select></td>\n"
		. "\t</tr>\n"
		. "\t<tr class='conditiontbl'>\n"
		. "<td align='right' valign='middle'>".$clang->gT("Answer")."</td>\n";

	if ($subaction == "editthiscondition")
	{
		$multipletext = "";
		if (isset($_POST['EDITConditionConst']) && $_POST['EDITConditionConst'] != '')
		{
			$EDITConditionConst=html_escape($_POST['EDITConditionConst']);
		}
		else
		{
			$EDITConditionConst="";
		}
		if (isset($_POST['EDITConditionRegexp']) && $_POST['EDITConditionRegexp'] != '')
		{
			$EDITConditionRegexp=html_escape($_POST['EDITConditionRegexp']);
		}
		else
		{
			$EDITConditionRegexp="";
		}
	}
	else
	{
		$multipletext = "multiple";
		if (isset($_POST['ConditionConst']) && $_POST['ConditionConst'] != '')
		{
			$EDITConditionConst=html_escape($_POST['ConditionConst']);
		}
		else
		{
			$EDITConditionConst="";
		}
		if (isset($_POST['ConditionRegexp']) && $_POST['ConditionRegexp'] != '')
		{
			$EDITConditionRegexp=html_escape($_POST['ConditionRegexp']);
		}
		else
		{
			$EDITConditionRegexp="";
		}
	}


	$conditionsoutput .= ""
		."<td valign='top' align='left'>\n"
		."<div id=\"conditiontarget\" class=\"tabs-nav\">\n"
		."<ul>\n"
		."\t<li><a href=\"#CANSWERSTAB\"><span>".$clang->gT("Predefined")."</span></a></li>\n"
		."\t<li><a href=\"#CONST\"><span>".$clang->gT("Constant")."</span></a></li>\n"
		."\t<li><a href=\"#PREVQUESTIONS\"><span>".$clang->gT("Questions")."</span></a></li>\n"
		."\t<li><a href=\"#TOKENATTRS\"><span>".$clang->gT("Token")."</span></a></li>\n"
		."\t<li><a href=\"#REGEXP\"><span>".$clang->gT("RegExp")."</span></a></li>\n"
		."</ul>\n";

	// Predefined answers tab
	$conditionsoutput .= "\t<div id='CANSWERSTAB'><select  name='canswers[]' $multipletext id='canswers' style='font-family:verdana; font-size:10; width:600px;' size='7'>\n"
		."\t</select>\n"
		."\t<br /><span id='canswersLabel'>".$clang->gT("Predefined answers for this question")."</span>\n"
		."\t</div>\n\t\n";
	// Constant tab 
	$conditionsoutput .= "<div id='CONST' style='display:' >"
		."\t\t<textarea name='ConditionConst' id='ConditionConst' cols='113' rows='5' style='width:600px;font-family:verdana; font-size:10' >$EDITConditionConst</textarea>\n"
		."\t\t<br /><div id='ConditionConstLabel'>".$clang->gT("Constant value")."</div>\n"
		."\t\t</div>\n";
	// Previous answers tab @SGQA@ placeholders
	$conditionsoutput .= "\t<div id='PREVQUESTIONS'><select name='prevQuestionSGQA' id='prevQuestionSGQA' style='font-family:verdana; font-size:10; width:600px;' size='7' >\n";
	foreach ($cquestions as $cqn) 
	{ // building the @SGQA@ placeholders options
		if ($cqn[2] != 'M' && $cqn[2] != 'P')
		{ // Type M or P aren't real fieldnames and thus can't be used in @SGQA@ placehodlers
			$conditionsoutput .= "<option value='@$cqn[3]@' title=\"".htmlspecialchars($cqn[0])."\"";
			if (isset($p_prevquestionsgqa) && $p_prevquestionsgqa == "@".$cqn[3]."@")
			{
				$conditionsoutput .= " selected='selected'";
			}
			$conditionsoutput .= ">$cqn[0]</option>\n";
		}
	}
	$conditionsoutput .= "\t</select>\n"
		."\t<br /><span id='prevQuestionSGQALabel'>".$clang->gT("Answers from previous questions")."</span>\n"
		."\t</div>\n\t\n";

	// tokenAttr Tab

	$conditionsoutput .= "\t<div id='TOKENATTRS'><select name='tokenAttr' id='tokenAttr' style='font-family:verdana; font-size:10; width:600px;' size='7' >\n";
	foreach (GetTokenFieldsAndNames($surveyid) as $tokenattr => $tokenattrName)
	{
		$conditionsoutput .= "<option value='{TOKEN:".strtoupper($tokenattr)."}'>".html_escape($tokenattrName)."</option>\n";
	}

	$conditionsoutput .= "\t</select>\n"
		."\t<br /><span id='tokenAttrLabel'>".$clang->gT("Attributes values from the participant's token")."</span>\n"
		."\t</div>\n\t\n";

	// Regexp Tab
	$conditionsoutput .= "<div id='REGEXP' style='display:'>"
		."<textarea name='ConditionRegexp' id='ConditionRegexp' cols='113' rows='5' style='width:600px;' ></textarea>\n"
		."<br /><div id='ConditionRegexpLabel'><a href=\"http://docs.limesurvey.org/tiki-index.php?page=Using+Regular+Expressions\" target=\"_blank\">".$clang->gT("Regular expression")."</a></div>\n"
		."</div>\n";
	$conditionsoutput .= "</div>\n"; // end conditiontarget div


    $js_adminheader_includes[]= $homeurl.'/scripts/conditions.js';
    $js_adminheader_includes[]= $rooturl.'/scripts/jquery/lime-conditions-tabs.js';
    $js_adminheader_includes[]= $rooturl.'/scripts/jquery/jquery-ui.js';
    
	$css_adminheader_includes[]= $homeurl."/styles/default/jquery-ui-tibo.css";

	if ($subaction == "editthiscondition" && isset($p_cid))
	{
		$submitLabel = $clang->gT("Update condition");
		$submitSubaction = "updatecondition";
		$submitcid = sanitize_int($p_cid);
	}
	else
	{
		$submitLabel = $clang->gT("Add condition");
		$submitSubaction = "insertcondition";
		$submitcid = "";
	}
	
	$conditionsoutput .= ""
		."</td>"
		."\t</tr>\n"
		."\t<tr>\n"
		."<td colspan='2' align='center'>\n"
		."\t<input type='reset' id='resetForm' value='".$clang->gT("Clear")."' />\n"
		."\t<input type='submit' value='".$submitLabel."' />\n"
		."<input type='hidden' name='sid' value='$surveyid' />\n"
		."<input type='hidden' name='gid' value='$gid' />\n"
		."<input type='hidden' name='qid' value='$qid' />\n"
		."<input type='hidden' name='subaction' value='$submitSubaction' />\n"
		."<input type='hidden' name='cqid' id='cqid' value='' />\n"
		."<input type='hidden' name='cid' id='cid' value='".$submitcid."' />\n"
		."<input type='hidden' name='editTargetTab' id='editTargetTab' value='' />\n" // auto-select tab by jQuery when editing a condition
		."<input type='hidden' name='editSourceTab' id='editSourceTab' value='' />\n" // auto-select tab by jQuery when editing a condition
		."<input type='hidden' name='canswersToSelect' id='canswersToSelect' value='' />\n" // auto-select target answers by jQuery when editing a condition
		."</td>\n"
		."\t</tr>\n"
		."</table>\n"
		."</form>\n";

	if (!isset($js_getAnswers_onload))
	{
		$js_getAnswers_onload = '';
	}

		$conditionsoutput .= "<script type='text/javascript'>\n"
			. "<!--\n"
			. "\t".$js_getAnswers_onload."\n";
		if (isset($p_method))
		{
			$conditionsoutput .= "\tdocument.getElementById('method').value='".$p_method."';\n";
		}

	if ($subaction == "editthiscondition")
	{ // in edit mode we read previous values in order to dusplay them in the corresponding inputs
		if (isset($_POST['EDITConditionConst']) && $_POST['EDITConditionConst'] != '')
		{
			// In order to avoid issues with backslash escaping, I don't use javascript to set the value
			// Thus the value is directly set when creating the Textarea element
			//$conditionsoutput .= "\tdocument.getElementById('ConditionConst').value='".html_escape($_POST['EDITConditionConst'])."';\n";
			$conditionsoutput .= "\tdocument.getElementById('editTargetTab').value='#CONST';\n";
		}
		elseif (isset($_POST['EDITprevQuestionSGQA']) && $_POST['EDITprevQuestionSGQA'] != '')
		{ 
			$conditionsoutput .= "\tdocument.getElementById('prevQuestionSGQA').value='".html_escape($_POST['EDITprevQuestionSGQA'])."';\n";
			$conditionsoutput .= "\tdocument.getElementById('editTargetTab').value='#PREVQUESTIONS';\n";
		}
		elseif (isset($_POST['EDITtokenAttr']) && $_POST['EDITtokenAttr'] != '')
		{
			$conditionsoutput .= "\tdocument.getElementById('tokenAttr').value='".html_escape($_POST['EDITtokenAttr'])."';\n";
			$conditionsoutput .= "\tdocument.getElementById('editTargetTab').value='#TOKENATTRS';\n";
		}
		elseif (isset($_POST['EDITConditionRegexp']) && $_POST['EDITConditionRegexp'] != '')
		{
			// In order to avoid issues with backslash escaping, I don't use javascript to set the value
			// Thus the value is directly set when creating the Textarea element
			//$conditionsoutput .= "\tdocument.getElementById('ConditionRegexp').value='".html_escape($_POST['EDITConditionRegexp'])."';\n";
			$conditionsoutput .= "\tdocument.getElementById('editTargetTab').value='#REGEXP';\n";
		}
		elseif (isset($_POST['EDITcanswers']) && is_array($_POST['EDITcanswers']))
		{ // was a predefined answers post
			$conditionsoutput .= "\tdocument.getElementById('editTargetTab').value='#CANSWERSTAB';\n";
			$conditionsoutput .= "\t$('#canswersToSelect').val('".$_POST['EDITcanswers'][0]."');\n";
		}

		if (isset($_POST['csrctoken']) && $_POST['csrctoken'] != '')
		{
			$conditionsoutput .= "\tdocument.getElementById('csrctoken').value='".html_escape($_POST['csrctoken'])."';\n";
			$conditionsoutput .= "\tdocument.getElementById('editSourceTab').value='#SRCTOKENATTRS';\n";
		}
		else
		{
			$conditionsoutput .= "\tdocument.getElementById('cquestions').value='".html_escape($_POST['cquestions'])."';\n";
			$conditionsoutput .= "\tdocument.getElementById('editSourceTab').value='#SRCPREVQUEST';\n";
		}
	}
	else
	{ // in other modes, for the moment we do the same as for edit mode
		if (isset($_POST['ConditionConst']) && $_POST['ConditionConst'] != '')
		{
			// In order to avoid issues with backslash escaping, I don't use javascript to set the value
			// Thus the value is directly set when creating the Textarea element
			//$conditionsoutput .= "\tdocument.getElementById('ConditionConst').value='".html_escape($_POST['ConditionConst'])."';\n";
			$conditionsoutput .= "\tdocument.getElementById('editTargetTab').value='#CONST';\n";
		}
		elseif (isset($_POST['prevQuestionSGQA']) && $_POST['prevQuestionSGQA'] != '')
		{ 
			$conditionsoutput .= "\tdocument.getElementById('prevQuestionSGQA').value='".html_escape($_POST['prevQuestionSGQA'])."';\n";
			$conditionsoutput .= "\tdocument.getElementById('editTargetTab').value='#PREVQUESTIONS';\n";
		}
		elseif (isset($_POST['tokenAttr']) && $_POST['tokenAttr'] != '')
		{
			$conditionsoutput .= "\tdocument.getElementById('tokenAttr').value='".html_escape($_POST['tokenAttr'])."';\n";
			$conditionsoutput .= "\tdocument.getElementById('editTargetTab').value='#TOKENATTRS';\n";
		}
		elseif (isset($_POST['ConditionRegexp']) && $_POST['ConditionRegexp'] != '')
		{
			// In order to avoid issues with backslash escaping, I don't use javascript to set the value
			// Thus the value is directly set when creating the Textarea element
			//$conditionsoutput .= "\tdocument.getElementById('ConditionRegexp').value='".html_escape($_POST['ConditionRegexp'])."';\n";
			$conditionsoutput .= "\tdocument.getElementById('editTargetTab').value='#REGEXP';\n";
		}
		else
		{ // was a predefined answers post
			if (isset($_POST['cquestions']))
			{
				$conditionsoutput .= "\tdocument.getElementById('cquestions').value='".html_escape($_POST['cquestions'])."';\n";
			}
			$conditionsoutput .= "\tdocument.getElementById('editTargetTab').value='#CANSWERSTAB';\n";
		}

		if (isset($_POST['csrctoken']) && $_POST['csrctoken'] != '')
		{
			$conditionsoutput .= "\tdocument.getElementById('csrctoken').value='".html_escape($_POST['csrctoken'])."';\n";
			$conditionsoutput .= "\tdocument.getElementById('editSourceTab').value='#SRCTOKENATTRS';\n";
		}
		else
		{
			if (isset($_POST['cquestions'])) $conditionsoutput .= "\tdocument.getElementById('cquestions').value='".javascript_escape($_POST['cquestions'])."';\n";
			$conditionsoutput .= "\tdocument.getElementById('editSourceTab').value='#SRCPREVQUEST';\n";
		}
	}

		if (isset($p_scenario))
		{
			$conditionsoutput .= "\tdocument.getElementById('scenario').value='".$p_scenario."';\n";
		}
		$conditionsoutput .= "-->\n"
			. "</script>\n";
	$conditionsoutput .= "</td></tr>\n";
	}
//END: DISPLAY THE ADD or EDIT CONDITION FORM


$conditionsoutput .= "</table>\n";


////////////// FUNCTIONS /////////////////////////////

function showSpeaker($hinttext)
{
	global $clang, $imagefiles, $max;

	if(!isset($max))
	{
		$max = 20;
	}
    $htmlhinttext=str_replace("'",'&#039;',$hinttext);  //the string is already HTML except for single quotes so we just replace these only
    $jshinttext=javascript_escape($hinttext,true,true);
    
	if(strlen(html_entity_decode($hinttext,ENT_QUOTES,'UTF-8')) > ($max+3))
	{
        $shortstring = FlattenText($hinttext);

        $shortstring = htmlspecialchars(mb_strcut(html_entity_decode($shortstring,ENT_QUOTES,'UTF-8'), 0, $max, 'UTF-8'));          

        //output with hoover effect
        $reshtml= "<span style='cursor: hand' alt='".$htmlhinttext."' title='".$htmlhinttext."' "
        ." onclick=\"alert('".$clang->gT("Question","js").": $jshinttext')\" />"
        ." \"$shortstring...\" </span>"
        ."<img style='cursor: hand' src='$imagefiles/speaker.png' align='bottom' alt='$htmlhinttext' title='$htmlhinttext' "
        ." onclick=\"alert('".$clang->gT("Question","js").": $jshinttext')\" />";
	}
	else
	{
        $reshtml= "<span title='".$htmlhinttext."'> \"$htmlhinttext\"</span>";                
	}

  return $reshtml; 
  
}

?>
