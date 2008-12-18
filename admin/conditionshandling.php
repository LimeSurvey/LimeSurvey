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
// Optionnally mark ADDED, UPDATED lines with different colors jus as the EDITTED one
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
if (!isset($p_scenario)) {$p_scenario=returnglobal('scenario');}
if (!isset($p_cqid)) {$p_cqid=returnglobal('cqid');}
if (!isset($p_cid)) {$p_cid=returnglobal('cid');}
if (!isset($p_subaction)) {$p_subaction=returnglobal('subaction');}
if (!isset($p_cquestions)) {$p_cquestions=returnglobal('cquestions');}

if (!isset($p_canswers))
{
	
	if (isset($_POST['canswers']) && is_array($_POST['canswers']))
	{
		foreach ($_POST['canswers'] as $key => $val)
		{
			$p_canswers[$key]=sanitize_paranoid_string($val);
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
		$p_method = $_POST['method'];
	}
}

if (isset($_POST['ValOrRegEx']))
{
	$html_ValOrRegEx = html_escape(auto_unescape($_POST['ValOrRegEx']));
}

if (isset($_POST['newscenarionum']))
{
	$p_newscenarionum = sanitize_int($_POST['newscenarionum']);
}
//END Sanitizing POSTed data

include_once("login_check.php");
include_once("database.php");


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
."\t\t<div class='menubar-left'>\n"
."\t\t\t<a href=\"#\" onclick=\"window.open('$scriptname?sid=$surveyid$extraGetParams', '_top')\" onmouseout=\"hideTooltip()\""
."onmouseover=\"showTooltip(event,'".$clang->gT("Return to survey administration", "js")."');return false\">" .
"<img name='HomeButton' src='$imagefiles/home.png' alt='' /></a>\n"
."\t\t\t<img src='$imagefiles/blank.gif' alt='' width='11' />\n"
."\t\t\t<img src='$imagefiles/seperator.gif' alt='' />\n"
."\t\t\t<a href=\"#\" onclick=\"window.open('$scriptname?action=conditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid', '_top')\" onmouseout=\"hideTooltip()\" onmouseover=\"showTooltip(event,'".$clang->gT("Show conditions for this question", "js")."');return false\" >" 
."<img name='SummaryButton' src='$imagefiles/summary.png' title='' alt='' /></a>\n"
."\t\t\t<img src='$imagefiles/seperator.gif' alt='' />\n"
."\t\t\t<a href=\"#\" onclick=\"window.open('$scriptname?action=conditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;subaction=editconditionsform', '_top')\" onmouseout=\"hideTooltip()\""
."onmouseover=\"showTooltip(event,'".$clang->gT("Add and edit conditions", "js")."');return false\">" 
."<img name='ViewAllButton' src='$imagefiles/document.png' title='' alt='' /></a>\n"
."\t\t\t<a href=\"#\" onclick=\"window.open('$scriptname?action=conditions&amp;sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;subaction=copyconditionsform', '_top')\" onmouseout=\"hideTooltip()\""
."onmouseover=\"showTooltip(event,'".$clang->gT("Copy conditions", "js")."');return false\">" 
."<img name='ViewAllButton' src='$imagefiles/document.png' title='' alt='' /></a>\n";


$conditionsoutput .="\t\t\t</div><div class='menubar-right'>\n"
		."<img width=\"11\" alt=\"\" src=\"$imagefiles/blank.gif\"/>\n"
		."<font class=\"boxcaption\">".$clang->gT("Questions").":</font>\n"
		."<select id='questionNav' onchange=\"window.open(this.options[this.selectedIndex].value,'_top')\"></select>\n"
		."<img hspace=\"0\" border=\"0\" alt=\"\" src=\"$imagefiles/seperator.gif\"/>\n"
		."<a href=\"#\" onclick=\"showhelp('show')\"" 
		."onmouseout=\"hideTooltip()\"" 
		."title=\"".$clang->gTview("Show help")."\"" 
		."onmouseover=\"showTooltip(event,'".$clang->gT("Show help", "js")."');return false\">" 
		."<img src='$imagefiles/showhelp.png' name='ShowHelp' title=''" 
		."alt='". $clang->gT("Show help")."' /></a>";


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
				!isset($_POST['ValOrRegEx'])) ||
			!isset($p_cquestions))
	{
		$conditionsoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Your condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer.","js")."\")\n //-->\n</script>\n";
	}
	else
	{
		if (isset($p_canswers))
		{
			foreach ($p_canswers as $ca)
			{
				$query = "INSERT INTO {$dbprefix}conditions (qid, scenario, cqid, cfieldname, method, value) VALUES "
					. "('{$qid}', '{$p_scenario}', '{$p_cqid}', '{$p_cquestions}', '{$p_method}', '$ca')";
				$result = $connect->Execute($query) or safe_die ("Couldn't insert new condition<br />$query<br />".$connect->ErrorMsg());
			}
		}
		if (isset($_POST['ValOrRegEx']) && $_POST['ValOrRegEx']) //Remmember: '', ' ', 0 are evaluated as FALSE
		{ //here is saved the textarea for constants or regex
			$query = "INSERT INTO {$dbprefix}conditions (qid, scenario, cqid, cfieldname, method, value) VALUES "
				. "('{$qid}', '{$p_scenario}', '{$p_cqid}', '{$p_cquestions}', '{$p_method}', ".$connect->qstr($_POST['ValOrRegEx'],get_magic_quotes_gpc()).")";
			$result = $connect->Execute($query) or safe_die ("Couldn't insert new condition<br />$query<br />".$connect->ErrorMsg());
		}
	}
}

// UPDATE ENTRY IF THIS IS AN EDIT
if (isset($p_subaction) && $p_subaction == "updatecondition")
{
	if ((!isset($p_canswers) &&
				!isset($_POST['ValOrRegEx'])) ||
			!isset($p_cquestions))
	{
		$conditionsoutput .= "<script type=\"text/javascript\">\n<!--\n alert(\"".$clang->gT("Your condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer.","js")."\")\n //-->\n</script>\n";
	}
	else
	{
		if (isset($p_canswers))
		{
			foreach ($p_canswers as $ca)
			{ // This is an Edit, there will only be ONE VALUE
				$query = "UPDATE {$dbprefix}conditions SET qid='{$qid}', scenario='{$p_scenario}', cqid='{$p_cqid}', cfieldname='{$p_cquestions}', method='{$p_method}', value='$ca' "
					. " WHERE cid={$p_cid}";
				$result = $connect->Execute($query) or safe_die ("Couldn't update condition<br />$query<br />".$connect->ErrorMsg());
			}
		}
		if (isset($_POST['ValOrRegEx']) && $_POST['ValOrRegEx']) //Remmember: '', ' ', 0 are evaluated as FALSE
		{ //here is saved the textarea for constants or regex
			$query = "UPDATE {$dbprefix}conditions SET qid='{$qid}', scenario='{$p_scenario}' , cqid='{$p_cqid}', cfieldname='{$p_cquestions}', method='{$p_method}', value=".$connect->qstr($_POST['ValOrRegEx'],get_magic_quotes_gpc())." "
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
//END PROCESS ACTIONS



unset($cquestions);
unset($canswers);



//BEGIN: GATHER INFORMATION 
// 1: Get information for this question
if (!isset($qid)) {$qid=returnglobal('qid');}
if (!isset($surveyid)) {$surveyid=returnglobal('sid');}

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
		if ($qrow['type'] != "UNSUPPORTEDTYPE")
		{
		// There is currently no unsupported question 
		// type for use in conditions
		// So remember the questions of this type
			$questionlist[]=$qrow["qid"];
		}
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
                   ."{$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."'" ;

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
	// Will detect if the questions are type D to use later
	$dquestions=array();
	// Will detect if the questions are of Numerical type, for use in @SGQA@ conditions
	$numquestions=array();

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
				$shortanswer = strip_tags($arows['answer']);

				$shortanswer .= " [{$arows['code']}]";
				$cquestions[]=array("$shortquestion [$shortanswer]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']);

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

				if ($rows['type'] == 'A' ||
						$rows['type'] == 'B')
				{
					$rows['acode']=$arows['code']; // let's add the answer code data
					$numquestions[]=$rows; // This is a numerical question type

					foreach ($numquestions as $numq)
					{
						if ($rows['qid'] != $numq['qid'] ||
								($rows['qid'] == $numq['qid'] && $rows['acode'] != $numq['acode']))
						{
							if ($numq['type'] == "A" ||
									$numq['type'] == "B" ||
									$numq['type'] == "K" ) // multiple line numerical questions
							{
								$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "@".$numq['sid'].$X.$numq['gid'].$X.$numq['qid'].$numq['acode']."@", $numq['title'].": ".$numq['question']." [".$numq['acode']."]");
							}
							elseif ($numq['type'] == "N" ||
									$numq['type'] == "5") // single line numerical questions
							{
								$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "@".$numq['sid'].$X.$numq['gid'].$X.$numq['qid']."@", $numq['title'].": ".$numq['question']);
							}
						}
					}
				}

			} //while
		} elseif ($rows['type'] == ":") { // Multiflexi
			//Get question attribute for $canswers
			$qidattributes=getQuestionAttributes($rows['qid']);
        	if ($maxvalue=arraySearchByKey("multiflexible_max", $qidattributes, "attribute", 1)) {
        		$maxvalue=$maxvalue['value'];
        	} else {
        		$maxvalue=10;
        	}
        	if ($minvalue=arraySearchByKey("multiflexible_min", $qidattributes, "attribute", 1)) {
        		$minvalue=$minvalue['value'];
        	} else {
        		$minvalue=1;
        	}
        	if ($stepvalue=arraySearchByKey("multiflexible_step", $qidattributes, "attribute", 1)) {
        		$stepvalue=$stepvalue['value'];
        	} else {
        		$stepvalue=1;
        	}
		if (arraySearchByKey("multiflexible_checkbox", $qidattributes, "attribute", 1)) {
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
				$shortanswer = strip_tags($arows['answer']);

				$shortanswer .= " [{$arows['code']}]";
				foreach($lids as $key=>$val) 
				{
				    $cquestions[]=array("$shortquestion [$shortanswer [$val]] ", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']."_".$key);
        			for($ii=$minvalue; $ii<=$maxvalue; $ii+=$stepvalue) 
        			{
        			    $canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']."_".$key, $ii, $ii);
        			}
				}
			}

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
				$shortanswer = strip_tags($arows['answer']);
				$shortanswer .= "[[Label 1]{$arows['code']}]";
				$cquestions[]=array("$shortquestion [$shortanswer]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']."#0");

				$shortanswer = strip_tags($arows['answer']);            
				$shortanswer .= "[[Label 2]{$arows['code']}]";
				$cquestions[]=array("$shortquestion [$shortanswer]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']."#1");

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
				$shortanswer = strip_tags($arows['answer']);
				$shortanswer .= "[{$arows['code']}]";
				$cquestions[]=array("$shortquestion [$shortanswer]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']);

				// Only Show No-Answer if question is not mandatory
				if ($rows['mandatory'] != 'Y')
				{
					$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "", $clang->gT("No answer"));
				}

				if ($rows['type'] == 'K')
				{
					$rows['acode']=$arows['code']; // let's add the answer code data
					$numquestions[]=$rows; // This is a numerical question type

					foreach ($numquestions as $numq)
					{
						if ($rows['qid'] != $numq['qid'] ||
								($rows['qid'] == $numq['qid'] && $rows['acode'] != $numq['acode']))
						{
							if ($numq['type'] == "A" ||
									$numq['type'] == "B" ||
									$numq['type'] == "K") // multiple line numerical questions
							{
								$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "@".$numq['sid'].$X.$numq['gid'].$X.$numq['qid'].$numq['acode']."@", $numq['title'].": ".$numq['question']." [".$numq['acode']."]");
							}
							elseif ($numq['type'] == "N" ||
									$numq['type'] == "5") // single line numerical questions
							{
								$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'], "@".$numq['sid'].$X.$numq['gid'].$X.$numq['qid']."@", $numq['title'].": ".$numq['question']);
							}
						}
					}
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
				$cquestions[]=array("$shortquestion [RANK $i]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i);
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
				$numquestions[]=$rows; // This is a numerical question type

				foreach ($numquestions as $numq)
				{
					if ($rows['qid'] != $numq['qid'])
					{
						if ($numq['type'] == "A" ||
								$numq['type'] == "B" ||
								$numq['type'] == "K" ) // multiple line numerical questions
						{
							$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "@".$numq['sid'].$X.$numq['gid'].$X.$numq['qid'].$numq['acode']."@", $numq['title'].": ".$numq['question']." [".$numq['acode']."]");
						}
						elseif ($numq['type'] == "N" ||
								$numq['type'] == "5") // single line numerical questions
						{
							$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "@".$numq['sid'].$X.$numq['gid'].$X.$numq['qid']."@", $numq['title'].": ".$numq['question']);
						}
					}
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
				$numquestions[]=$rows; // This is a numerical question type

				foreach ($numquestions as $numq)
				{
					if ($rows['qid'] != $numq['qid'])
					{
						if ($numq['type'] == "A" ||
								$numq['type'] == "B" ||
								$numq['type'] == "K" ) // multiple line numerical questions
						{
							$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "@".$numq['sid'].$X.$numq['gid'].$X.$numq['qid'].$numq['acode']."@", $numq['title'].": ".$numq['question']." [".$numq['acode']."]");
						}
						elseif ($numq['type'] == "N" ||
								$numq['type'] == "5") // single line numerical questions
						{
							$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "@".$numq['sid'].$X.$numq['gid'].$X.$numq['qid']."@", $numq['title'].": ".$numq['question']);
						}
					}
				}

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

					// Now, save the questions type D only, then
					// it don�t need pass by all the array elements...
					$dquestions[]=$rows;

					// offer previous date questions to compare
					foreach ($dquestions as $dq)
					{
						if ($rows['qid'] != $dq['qid'] &&
								$dq['type'] == "D")
						{   // Can�t compare with the same question, and only if are D
							// The question tittle is enclossed by @ to be identified latter
							// and be processed accordingly
//							$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "@".$dq['title']."@", $dq['title'].": ".$dq['question']);
							$canswers[]=array($rows['sid'].$X.$rows['gid'].$X.$rows['qid'], "@".$rows['sid'].$X.$rows['gid'].$X.$rows['qid']."@", $dq['title'].": ".$dq['question']);
						}
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
."\t\t<td align='center'>\n";

// BEGIN UPDATE THE questionNav SELECT INPUT
$conditionsoutput .= "<script type='text/javascript'>\n"
	."<!--\n";
if (strlen($questiontext)<35)
{
	$questiontextshort = $questiontext;
}
else
{
	$questiontextshort = substr($questiontext, 0, 35)."..";
}

$conditionsoutput .= "\t$(\"<option value='$scriptname?sid=$surveyid&amp;gid=$gid&amp;qid=$qid&amp;action=conditions' selected='selected'>$questiontitle: $questiontextshort</option>\").appendTo(\"#questionNav\");\n"; 

$conditionsoutput .=  "\t$(\"<optgroup class='activesurveyselect' label='".$clang->gT("Before","js")."'>\").appendTo(\"#questionNav\");\n";
foreach ($theserows as $row)
{
		$question=strip_tags($row['question']);
		if (strlen($question)<35)
		{
			$questionselecter = $question;
		}
		else
		{
			$questionselecter = substr($question, 0, 35)."..";
		}
		$conditionsoutput .=  ""
		. "\t\t$(\"<option value='$scriptname?sid=$surveyid&amp;gid={$row['gid']}&amp;qid={$row['qid']}&amp;action=conditions'>{$row['title']}: $questionselecter</option>\").appendTo(\"#questionNav\");\n";
}
$conditionsoutput .=  "\t$(\"</optgroup>\").appendTo(\"#questionNav\");\n";


$conditionsoutput .=  "\t$(\"<optgroup class='activesurveyselect' label='".$clang->gT("After","js")."'>\").appendTo(\"#questionNav\");\n";
foreach ($postrows as $row)
{
		$question=strip_tags($row['question']);
		if (strlen($question)<35)
		{
			$questionselecter = $question;
		}
		else
		{
			$questionselecter = substr($question, 0, 35)."..";
		}
		$conditionsoutput .=  ""
		. "\t\t$(\"<option value='$scriptname?sid=$surveyid&amp;gid={$row['gid']}&amp;qid={$row['qid']}&amp;action=conditions'>{$row['title']}: $questionselecter</option>\").appendTo(\"#questionNav\");\n";
}
$conditionsoutput .=  "\t$(\"</optgroup>\").appendTo(\"#questionNav\");\n";

$conditionsoutput .=  "-->\n"
		."</script>\n";
// END UPDATE THE questionNav SELECT INPUT

//Now display the information and forms
//BEGIN: PREPARE jAVASCRIPT TO SHOW MATCHING ANSWERS TO SELECTED QUESTION
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
		."\t\tQcqids[$jn]='$cqn[1]';\n"
		."\t\tQtypes[$jn]='$cqn[2]';\n";
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
$conditionsoutput .= "\t\t\tfor (var i=document.getElementById('canswers').options.length-1; i>=0; i--)\n"
."\t\t\t\t{\n";
$conditionsoutput .= "\t\t\t\t\tdocument.getElementById('canswers').options[i] = null;\n"
."\t\t\t\t}\n";
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
."\t\t\t\t\tif (Qtypes[i] == 'D' || Qtypes[i] == 'N' ||\n"
."\t\t\t\t\t\tQtypes[i] == 'K' || Qtypes[i] == ':' ||\n"
."\t\t\t\t\t\tQtypes[i] == ';' || Qtypes[i] == 'S' ||\n"
."\t\t\t\t\t\tQtypes[i] == 'Q' || Qtypes[i] == 'U' ||\n"
."\t\t\t\t\t\tQtypes[i] == 'T' )\n"
."\t\t\t\t\t\t{\n"
."\t\t\t\t\t\t$('#conditiontarget > ul').tabs('select', '#CONST_RGX');\n"
."\t\t\t\t\t\t}\n"
."\t\t\t\t\telse\n"
."\t\t\t\t\t\t{\n"
."\t\t\t\t\t\tif (document.getElementById('method').value == 'RX')\n"
."\t\t\t\t\t\t\t{\n"
."\t\t\t\t\t\t\tdocument.getElementById('method').value = '==';\n"
."\t\t\t\t\t\t\t$('#ValOrRegExLabel').text('".$clang->gT("Constant value or @SGQA@ code")."');\n"
."\t\t\t\t\t\t\t$('#conditiontarget > ul').tabs('enable', 0);\n"
."\t\t\t\t\t\t\t}\n"
."\t\t\t\t\t\t$('#conditiontarget > ul').tabs('select', '#CANSWERSTAB');\n"
."\t\t\t\t\t\t}\n"
."\t\t\t\t\t}\n"
."\t\t\t\t}\n";
$conditionsoutput .= "\t\t\tfor (var i=0;i<Keys.length;i++)\n"
."\t\t\t\t{\n";
$conditionsoutput .= "\t\t\t\tdocument.getElementById('canswers').options[document.getElementById('canswers').options.length] = new Option(Answers[Keys[i]], Codes[Keys[i]]);\n"
."\t\t\t\t}\n"
. "\t\t\tif (document.getElementById('canswers').options.length > 0){\n"                                                                         
. "\t\t\t\tdocument.getElementById('canswers').style.display = '';}\n"
. "\t\t\telse {\n"                                                                         
. "\t\t\t\tdocument.getElementById('canswers').style.display = 'none';}\n"
."\t\t}\n"
."function evaluateLabels(val)\n"
."{\n"
."\tif(val == 'RX')\n"
."\t{\n"
."\t\t$('#conditiontarget > ul').tabs('select', '#CONST_RGX');\n"
."\t\t$('#conditiontarget > ul').tabs('disable', 0);\n"
."\t\t$('#ValOrRegExLabel').text('".$clang->gT("Regular expression")."');\n"
."\t}\n"
."\telse {\n"
."\t\t$('#conditiontarget > ul').tabs('enable', 0);\n"
."\t\t$('#ValOrRegExLabel').text('".$clang->gT("Constant value or @SGQA@ code")."');\n"
."\t}\n"
."}\n"
."//-->\n"
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
		."\t\t<td><table align='center'><tr>\n";
	$showreplace="$questiontitle". showSpeaker($questiontext);
	$onlyshow=str_replace("{QID}", $showreplace, $clang->gT("Only show question {QID} IF"));


	if ($subaction== "editconditionsform" || $subaction=='insertcondition' ||
		$subaction == "editthiscondition" || $subaction == "delete" ||
		$subaction == "updatecondition" || $subaction == "deletescenario" ||
		$subaction == "updatescenario" ||
		$subaction == "renumberscenarios")
	{
		$conditionsoutput .= "\t\t\t<td align='center' width='90%'><strong>$onlyshow</strong>\n"
			."\t\t</td>\n"
			."\t\t<td width='10%'><form id='deleteallconditions' action='$scriptname?action=conditions' method='post' name='deleteallconditions' style='margin-bottom:0;'>\n"
			."\t\t<input type='hidden' name='qid' value='$qid' />\n"
			."\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
			."\t\t<input type='hidden' id='toplevelsubaction' name='subaction' value='deleteallconditions' />\n";

		$conditionsoutput .= "\t\t<input type='submit' id='deleteallconditionsbtn' value='".$clang->gT("Delete all conditions")."' onclick=\"return confirm('".$clang->gT("Are you sure you want to delete all conditions set to the questions you have selected?","js")."')\" />\n";
		if ($scenariocount > 1)
		{
			$conditionsoutput .= "\t\t<input type='submit' id='renumberscenariosbtn' value='".$clang->gT("Renumber scenarios")."' onclick=\"if (confirm('".$clang->gT("Are you sure you want to renumber the scenarios with incremented numbers beginning from 1?","js")."')) {document.getElementById('toplevelsubaction').value='renumberscenarios'; return true;} else {return false;}\"/>\n";
		}
	}
	else
	{
		$conditionsoutput .= "\t\t\t<td align='center'><strong>$onlyshow</strong>\n"
			."\t\t<form id='deleteallconditions' action='$scriptname?action=conditions' method='post' name='deleteallconditions' style='margin-bottom:0;'>\n"
			."\t\t<input type='hidden' name='qid' value='$qid' />\n"
			."\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
			."\t\t<input type='hidden' id='toplevelsubaction' name='subaction' value='deleteallconditions' />\n";
	}

	$conditionsoutput .= "</form></td></tr></table>\n"
		."\t</td></tr>\n"; 

	if ($scenariocount > 0)
	{
		$js_adminheader_includes .= "<script type=\"text/javascript\" src=\"../scripts/jquery/jquery-checkgroup.js\"></script>\n";
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
				."<table><tr>$initialCheckbox<td width='90%'>$scenariotext&nbsp;\n"
				."<form action='$scriptname?action=conditions' method='post' id='editscenario{$scenarionr['scenario']}' style='display: none'>\n"
				."<label>".$clang->gT("New scenario number").":&nbsp;\n"
				."<input type='text' name='newscenarionum' size='3'/></label>\n"
				."<input type='hidden' name='scenario' value='{$scenarionr['scenario']}'/>\n"
				."<input type='hidden' name='sid' value='$surveyid' />\n"
				."<input type='hidden' name='qid' value='$qid' />\n"
				."<input type='hidden' name='subaction' value='updatescenario' />&nbsp;&nbsp;\n"
				."<input type='submit' name='scenarioupdated' value='".$clang->gT("Update scenario")."' />\n"
				."<input type='button' name='cancel' value='".$clang->gT("Cancel")."' onclick=\"$('#editscenario{$scenarionr['scenario']}').hide('slow');document.getElementById('editscenariobtn{$scenarionr['scenario']}').disabled=false;\"/>\n"
				."</form></td>\n"
				. "<td width='10%' valign='middle' align='right'><form id='deletescenario{$scenarionr['scenario']}' action='$scriptname?action=conditions' method='post' name='deletescenario{$scenarionr['scenario']}' style='margin-bottom:0;'>\n";

			if ($scenariotext != "" && ($subaction == "editconditionsform" ||$subaction == "insertcondition" ||
					$subaction == "updatecondition" || $subaction == "editthiscondition" || 
					$subaction == "renumberscenarios" || $subaction == "updatescenario" ||
					$subaction == "deletescenario" || $subaction == "delete") )
			{
				$conditionsoutput .= "\t<input type='submit' value='".$clang->gT("Delete scenario")."' style='font-family: verdana; font-size: 8; height:15' onclick=\"return confirm('".$clang->gT("Are you sure you want to delete all conditions set in this scenario?","js")."')\" />\n";
				$conditionsoutput .= "\t<input type='button' id='editscenariobtn{$scenarionr['scenario']}' value='".$clang->gT("Edit scenario")."' style='font-family: verdana; font-size: 8; height:15' onclick=\"$('#editscenario{$scenarionr['scenario']}').show('slow');this.disabled=true;\" />\n";
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
				."{$dbprefix}questions "
				."WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid "
				."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
				."AND {$dbprefix}conditions.qid=$qid "
				."AND {$dbprefix}conditions.scenario={$scenarionr['scenario']}\n"
				."ORDER BY {$dbprefix}conditions.cfieldname";
			$result = db_execute_assoc($query) or safe_die ("Couldn't get other conditions for question $qid<br />$query<br />".$connect->ErrorMsg());
			$conditionscount=$result->RecordCount();

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
				while ($rows=$result->FetchRow())
				{
					if($rows['method'] == "") {$rows['method'] = "==";} //Fill in the empty method from previous versions
					$markcidstyle="";
					if (is_null(array_search($rows['cid'], $markcidarray)) || // PHP4
							array_search($rows['cid'], $markcidarray) === FALSE) // PHP5
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
						$conditionsoutput .= "\t\t\t\t<tr class='evenrow'>\n"
							."\t\t\t\t\t<td valign='middle' align='center'>\n"
							."<font size='1'><strong>"
							.$clang->gT("and")."</strong></font></td></tr>";
					}
					elseif (isset($currentfield))
					{
						$conditionsoutput .= "\t\t\t\t<tr class='evenrow'>\n"
							."\t\t\t\t\t<td valign='top' align='center'>\n"
							."<font size='1'><strong>"
							.$clang->gT("OR")."</strong></font></td></tr>";
					}
					$conditionsoutput .= "\t<tr class='oddrow' style='$markcidstyle'>\n"
						."\t<td><form style='margin-bottom:0;' name='conditionaction{$rows['cid']}' id='conditionaction{$rows['cid']}' method='post' action='$scriptname?action=conditions'>\n"
						."\t\t<table width='100%' style='height: 13px;' cellspacing='0' cellpadding='0'>\n"
						."\t\t\t<tr>\n";

					if ( $subaction == "copyconditionsform" || $subaction == "copyconditions")
					{
						$conditionsoutput .= "\t\t\t\t<td>&nbsp;&nbsp;</td>"
							. "\t\t\t\t<td valign='middle' align='right'>\n"
							. "\t\t\t\t\t<input type='checkbox' name='aConditionFromScenario{$scenarionr['scenario']}' id='cbox{$rows['cid']}' value='{$rows['cid']} '/>\n"
							. "\t\t\t\t</td>\n";
					}
					$conditionsoutput .= ""
						."\t\t\t\t<td valign='middle' align='right' width='40%'>\n"
						."\t\t\t\t\t<font size='1' face='verdana'>\n";
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

					$conditionsoutput .= "\t\t\t\t\t</font></td>\n"
						."\t\t\t\t\t<td align='center' valign='middle' width='20%'>\n"
						."\t\t\t\t\t\t<font size='1'>\n" //    .$clang->gT("Equals")."</font></td>"
						.$method[$rows['method']]
						."\t\t\t\t\t\t</font>\n"
						."\t\t\t\t\t</td>\n"
						."\n"
						."\t\t\t\t\t<td align='left' valign='middle' width='30%'>\n"
						."\t\t\t\t\t\t<font size='1' face='verdana'>\n";
					// Here will be searched the conditional answer for this question
					// this conditional part is the labeled one
					// But there is another kind of condition
					// the specified in ValOrRegEx and is in $rows['value']
					$bHasAnswer = false;
					$bIsPredefinedAnswer = false;
					foreach ($canswers as $can)
					{
						//$conditionsoutput .= $rows['cfieldname'] . "- $can[0]<br />";
						//$conditionsoutput .= $can[1];
						if ($can[0] == $rows['cfieldname'] && $can[1] == $rows['value'])
						{
							$conditionsoutput .= "\t\t\t\t\t\t$can[2] ($can[1])\n";
							$bHasAnswer = true;
							$bIsPredefinedAnswer = true;
						}
					}
					if (!$bHasAnswer)
					{
						if ($rows['value'] == ' ' ||
								$rows['value'] == '')
						{
							$conditionsoutput .= "\t\t\t\t\t\t".$clang->gT("No answer")."\n";
							$bIsPredefinedAnswer = true;
						} 
						else
						{
							$conditionsoutput .= "\t\t\t\t\t\t".$rows['value']."\n";
							$bIsPredefinedAnswer = false;
						}
					}
					$conditionsoutput .= "\t\t\t\t\t</font></td>\n"
						."\t\t\t\t\t<td align='right' valign='middle' width='10%'>\n";

					if ($subaction == "editconditionsform" ||$subaction == "insertcondition" ||
						$subaction == "updatecondition" || $subaction == "editthiscondition" || 
						$subaction == "renumberscenarios" || $subaction == "deleteallconditions" || 
						$subaction == "updatescenario" ||
						$subaction == "deletescenario" || $subaction == "delete")
					{
						//TIBO
						$conditionsoutput .= ""
							."\t\t\t\t\t\t<input type='submit' value='".$clang->gT("Delete")."' style='font-family: verdana; font-size: 8; height:15' onclick=\"return confirm('".$clang->gT("Are you sure you want to delete this condition?","js")."')\" />\n"
							."\t\t\t\t\t\t<input type='submit' value='".$clang->gT("Edit")."' onclick='document.getElementById(\"subaction{$rows['cid']}\").value=\"editthiscondition\";' style='font-family: verdana; font-size: 8; height:15' />\n"
							."\t\t\t\t\t<input type='hidden' name='subaction' id='subaction{$rows['cid']}' value='delete' />\n"
							."\t\t\t\t\t<input type='hidden' name='cid' value='{$rows['cid']}' />\n"
							."\t\t\t\t\t<input type='hidden' name='scenario' value='{$rows['scenario']}' />\n"
							."\t\t\t\t\t<input type='hidden' name='cquestions' value='{$rows['cfieldname']}' />\n"
							."\t\t\t\t\t<input type='hidden' name='method' value='{$rows['method']}' />\n"
							."\t\t\t\t\t<input type='hidden' name='sid' value='$surveyid' />\n"
							."\t\t\t\t\t<input type='hidden' name='qid' value='$qid' />\n";
						if ($bIsPredefinedAnswer === true)
						{ // Add canswers[]
							$conditionsoutput .= ""
							."\t\t\t\t\t<input type='hidden' name='canswers[]' value='".html_escape($rows['value'])."' />\n";
						}
						else
						{ // Add ValOrReg
							$conditionsoutput .= ""
							."\t\t\t\t\t<input type='hidden' name='ValOrRegEx' value='".html_escape($rows['value'])."' />\n";
						}
					}

					$conditionsoutput .= ""
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
			$s++;
		}
	}
	else
	{ // no condition ==> disable delete all conditions button, and display a simple comment
		$conditionsoutput .= "<tr><td valign='middle' align='center'>".$clang->gT("Always display this condition")."\n"
			. "<script type='text/javascript'>\n"
			. "<!--\n"
			. "\tdocument.getElementById('deleteallconditionsbtn').style.display='none';\n"
			. "\tdocument.getElementById('renumberscenariosbtn').style.display='none';\n"
			. "-->\n"
			. "</script>\n"
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
		."\t\t<td colspan='3' align='center' class='settingcaption'>\n"
		."\t\t<strong>"
		.$clang->gT("Copy conditions")."</strong>\n"
		."\t\t</td>\n"
		."\t</tr>\n";

	if (isset($conditionsList) && is_array($conditionsList))
	{

/***
		$conditionsoutput .= "\t<tr bgcolor='#EFEFEF'>\n"
			."\t\t<th width='40%'>".$clang->gT("Condition")."</th><th width='200'></th><th width='40%'>".$clang->gT("Question")."</th>\n"
			."\t</tr>\n";
****/

		$conditionsoutput .= "\t<tr>\n";
/*****
		$conditionsoutput .= "";
			."\t\t<td align='center'>\n"
			."\t\t<select name='copyconditionsfrom[]' multiple style='font-family:verdana; font-size:10; width:220; background-color: #E1FFE1' size='4' >\n";
		foreach ($conditionsList as $cl)
		{
			$conditionsoutput .= "<option value='".$cl['cid']."'>".$cl['text']."</option>\n";
		}
		$conditionsoutput .= "\t\t</select>\n"
			."\t\t</td>\n";
****/
		$conditionsoutput .= ""
			."\t\t<td align='center' style='text-align: center' width='250'>\n"
			."\t\t".$clang->gT("Copy the selected conditions to").":\n"
			."\t\t</td>\n"
			."\t\t<td align='left'>\n"
			."\t\t<select name='copyconditionsto[]' multiple style='font-family:verdana; font-size:10; width:600px' size='4'>\n";
		foreach ($pquestions as $pq)
		{
			$conditionsoutput .= "<option value='{$pq['fieldname']}'>".$pq['text']."</option>\n";
		}
		$conditionsoutput .= "\t\t</select>\n";
		$conditionsoutput .= "\t\t</td>\n"
			."\t</tr>\n";

		$conditionsoutput .= "\t<tr><td colspan='3' align='center'>\n"
			."<input type='submit' value='".$clang->gT("Copy conditions")."' onclick=\"if (confirm('".$clang->gT("Are you sure you want to copy these condition(s) to the questions you have selected?","js")."')){prepareCopyconditions(); return true;} else {return false;}\" />"
			."\t\t\n";

		$conditionsoutput .= "<input type='hidden' name='subaction' value='copyconditions' />\n"
			."<input type='hidden' name='sid' value='$surveyid' />\n"
			."<input type='hidden' name='qid' value='$qid' />\n";

		$conditionsoutput .= "<script type=\"text/javascript\">\n"
			."function prepareCopyconditions()\n"
			."{\n"
			."\t$(\"input:checked[name^='aConditionFromScenario']\").each(function(i,val)\n"
			."\t{\n"
			."\t\tvar thecid = val.value;\n"
			."\t\tvar theform = document.getElementById('copyconditions');\n"
			."\t\taddHiddenElement(theform,'copyconditionsfrom[]',thecid);\n"
			."\t\treturn true;\n"
			."\t});\n"
			."}\n"
			."</script>\n"
			."</td></tr>";

	}
	else
	{
		$conditionsoutput .= "\t<tr bgcolor='#EFEFEF'>\n"
			."\t\t<th width='40%'>".$clang->gT("Condition")."</th><th width='200'></th><th width='40%'>".$clang->gT("Question")."</th>\n"
			."\t</tr>\n";
	}
			$conditionsoutput .= "</table></form></td></tr>\n";

		$conditionsoutput .= "\t<tr ><td colspan='3'></td></tr>\n"
			."\t<tr bgcolor='#555555'><td colspan='3'></td></tr>\n";
}
// END: DISPLAY THE COPY CONDITIONS FORM

$qcount=isset($cquestions) ? count($cquestions) : 0;


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
		."\t\t<td colspan='2' align='center'>\n"
		."\t\t\t<strong>".$mytitle."</strong>\n"
		."\t\t</td>\n"
		."\t</tr>\n"
		."\t<tr bgcolor='#EFEFEF'>\n"
		."\t\t<th width='25%'></th>\n"
		."\t\t<th width='75%'></th>\n"
		."\t</tr>\n";

	if (isset($scenariocount) && $scenariocount == 1)
	{
		$scenarioAddBtn = "\t\t\t<a id='scenarioaddbtn' href='#' onclick=\"$('#scenarioaddbtn').hide();$('#defaultscenariotxt').hide('slow');$('#scenario').show('slow');\"><img width='14' heigth='14' border='0' src='$imagefiles/add.png' /></a>\n";
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
		. "\t\t<td align='right' valign='bottom'>$scenarioAddBtn&nbsp;".$clang->gT("scenario")."</td>\n"
		. "\t\t<td valign='bottom'><input type='text' name='scenario' id='scenario' value='1' size='2' $scenarioInputStyle/>"
		. "$scenarioTxt</td>\n"
		. "\t</tr>\n"
		. "\t<tr class='conditiontbl'>\n"
		. "\t\t<td align='right' valign='middle'>".$clang->gT("Question")."</td>\n"
		. "\t\t<td><select onclick=\"getAnswers(this.options[this.selectedIndex].value)\" name='cquestions' id='cquestions' style='width:600px;font-family:verdana; font-size:10;' size='".($qcount+1)."'>\n";
		
	
	if (isset($cquestions))
	{
		$js_getAnswers_onload = "";
		foreach ($cquestions as $cqn)
		{
			$conditionsoutput .= "\t\t\t\t<option value='$cqn[3]' title=\"$cqn[0]\"";
			if (isset($p_cquestions) && $cqn[3] == $p_cquestions) {
				$conditionsoutput .= " selected";
				$js_getAnswers_onload .= "getAnswers(\"".$cqn[3]."\");\n";
				if (isset($p_canswers))
				{
					//$js_getAnswers_onload .= "document.getElementById('canswers').value='".$p_canswers."';";
					$js_getAnswers_onload .= "for(i = 0; i < document.getElementById('canswers').length; i++)\n"
						."{\n"
						."\tvar optionval = document.getElementById('canswers').options[i].value;\n"
						."\tif (";
						foreach ($p_canswers as $checkval)
						{
							$js_getAnswers_onload .= " optionval == '".$checkval."' || ";
						}
						$js_getAnswers_onload .= " 0 == 1 )\n"
						."\t{\n"
						."\t\tdocument.getElementById('canswers').options[i].selected=true;\n"
						."\t}\n"
						."}\n";
				}
			}
			$conditionsoutput .= ">$cqn[0]</option>\n";
		}
	}
	$conditionsoutput .= "\t\t\t</select>\n"
		. "\t</tr>\n"
		. "\t<tr class='conditiontbl'>\n"
		. "\t\t<td align='right' valign='middle'>".$clang->gT("Comparison operator")."</td>\n"
		. "\t\t<td><select name='method' id='method' style='font-family:verdana; font-size:10' onChange='evaluateLabels(this.value)'>\n"
		. "\t\t\t<option value='<'>".$clang->gT("Less than")."</option>\n"
		. "\t\t\t<option selected='selected' value='=='>".$clang->gT("Equals")."</option>\n"	
		. "\t\t\t<option selected='selected' value='!='>".$clang->gT("Not equal to")."</option>\n"	
		. "\t\t\t<option selected='selected' value='>='>".$clang->gT("Greater than or equal to")."</option>\n"
		. "\t\t\t<option selected='selected' value='>'>".$clang->gT("Greater than")."</option>\n"
		. "\t\t\t<option selected='selected' value='RX'>".$clang->gT("Regular expression")."</option>\n"
		. "\t\t</select></td>\n"
		. "\t</tr>\n"
		. "\t<tr class='conditiontbl'>\n"
		. "\t\t<td align='right' valign='middle'>".$clang->gT("Answer")."</td>\n";

	if ($subaction == "editthiscondition")
	{
		$multipletext = "";
	}
	else
	{
		$multipletext = "multiple";
	}

	$conditionsoutput .= ""
		."\t\t<td>\n"
		."\t\t<div id=\"conditiontarget\" class=\"tabs-nav\">\n"
		."\t\t<ul>\n"
		."\t\t\t<li><a href=\"#CANSWERSTAB\"><span>".$clang->gT("Predefined")."</span></a></li>\n"
		."\t\t\t<li><a href=\"#CONST_RGX\"><span>".$clang->gT("Advanced")."</span></a></li>\n"
		."\t\t</ul>\n"
		."\t\t\t<div id='CANSWERSTAB'><select name='canswers[]' $multipletext id='canswers' style='font-family:verdana; font-size:10; min-width:600px;' size='7'>\n";
	$conditionsoutput .= "\t\t\t</select>\n"
		."\t\t\t<br /><span id='canswersLabel'>".$clang->gT("Predefined answers")."</span>\n"
		."\t\t\t</div>\n\t\t\t\n";
	$conditionsoutput .= "<div id='CONST_RGX' style='display:'>"
		."\t\t<textarea name='ValOrRegEx' id='ValOrRegEx' cols='113' rows='5'></textarea>\n"
		."\t\t<br /><span id='ValOrRegExLabel'>".$clang->gT("Constant value or @SGQA@ code")."</span>\n"
		."\t\t</div>\n"
		."\t\t</div>\n";


	$js_adminheader_includes .= ""
		. "<script type=\"text/javascript\" src=\"../scripts/jquery/jquery-ui-core-1.6rc2.min.js\"></script>\n"
		. "<script type=\"text/javascript\" src=\"../scripts/jquery/jquery-ui-tabs-1.6rc2.min.js\"></script>\n"
		. "<script type=\"text/javascript\" src=\"../scripts/jquery/lime-conditions-tabs.js\"></script>\n"
		. "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"styles/default/jquery.tabs.css\" />\n"
		. "<!--[if lte IE 7]><link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"styles/default/jquery.tabs-ie.css\" /><![endif]-->\n"
		. "<script type=\"text/javascript\">\n"
		. "\t$(document).ready(function(){\n"
		. "\t\t$('#conditiontarget > ul').tabs();\n"
		. "\t});\n"
		. "</script>\n";

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
		."\t\t</td>"
		."\t</tr>\n"
		."\t<tr>\n"
		."\t\t<td colspan='2' align='center'>\n"
		."\t\t\t<input type='reset' value='".$clang->gT("Clear")."' onclick=\"clearAnswers()\" />\n"
		."\t\t\t<input type='submit' value='".$submitLabel."' />\n"
		."<input type='hidden' name='sid' value='$surveyid' />\n"
		."<input type='hidden' name='qid' value='$qid' />\n"
		."<input type='hidden' name='subaction' value='$submitSubaction' />\n"
		."<input type='hidden' name='cqid' id='cqid' value='' />\n"
		."<input type='hidden' name='cid' id='cid' value='".$submitcid."' />\n"
		."\t\t</td>\n"
		."\t</tr>\n"
		."</table>\n"
		."</form>\n";
	$conditionsoutput .= "</td></tr>\n";

	if (isset($js_getAnswers_onload) && $js_getAnswers_onload != '')
	{
		$conditionsoutput .= "<script type='text/javascript'>\n"
			. "<!--\n"
			. "\t".$js_getAnswers_onload."\n";
		if (isset($p_method))
		{
			$conditionsoutput .= "\tdocument.getElementById('method').value='".$p_method."';\n";
		}
		if (isset($_POST['ValOrRegEx']))
		{
			$conditionsoutput .= "\tdocument.getElementById('ValOrRegEx').value='".javascript_escape(auto_unescape($_POST['ValOrRegEx']))."';\n";
		}
		if (isset($p_scenario))
		{
			$conditionsoutput .= "\tdocument.getElementById('scenario').value='".$p_scenario."';\n";
		}
		$conditionsoutput .= "-->\n"
			. "</script>\n";
	}
}
//END: DISPLAY THE ADD or EDIT CONDITION FORM


$conditionsoutput .= "</table>\n";


////////////// FUNCTIONS /////////////////////////////

function showSpeaker($hinttext)
{
	global $clang, $imagefiles, $max;

	if(!isset($max))
	{
		$max = 12;
	}

	if(strlen($hinttext) > ($max))
	{
		$shortstring = strip_tags($hinttext);

		//create short string
		$shortstring = substr($hinttext, 0, $max);

		//output with hoover effect
		$reshtml= "<span style='cursor: hand' alt=\"".html_escape($hinttext)."\" title=\"".html_escape($hinttext)."\" "
           ." onclick=\"alert('".$clang->gT("Question","js").": ".javascript_escape($hinttext,true,true)."')\" >"
           ." \"$shortstring...\" </span>"
           ."<img style='cursor: hand' src='$imagefiles/speaker.png' align='bottom' alt=\"".html_escape($hinttext)."\" title=\"".html_escape($hinttext)."\" "
           ." onclick=\"alert('".$clang->gT("Question","js").": ".javascript_escape($hinttext,true,true)."')\" />";
	}
	else
	{
		$reshtml= "<span alt=\"".html_escape($hinttext)."\" title=\"".html_escape($hinttext)."\"> \"$hinttext\"</span>";
	}

  return $reshtml; 
  
}





?>
