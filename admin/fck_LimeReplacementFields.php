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
* $Id: conditions.php 3736 2007-11-30 16:13:55Z lemeur $
*/

require_once(dirname(__FILE__).'/../config.php');
require_once(dirname(__FILE__).'/../common.php');

@ini_set('session.gc_maxlifetime', $sessionlifetime);

include_once("login_check.php");
$surveyid=returnglobal('sid');
if (!isset($gid)) {$gid=returnglobal('gid');}
if (!isset($qid)) {$qid=returnglobal('qid');}

//Ensure script is not run directly, avoid path disclosure
if (empty($surveyid)) {die("No SID provided.");}

$newgroupquestion=false;

if (empty($gid))
{ // this is a new group creation
  // take the previous group ID and set a flag
	$gquery="SELECT group_name,gid FROM {$dbprefix}groups WHERE sid=$surveyid "
		."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		."ORDER BY group_order DESC";
	$gresult = db_select_limit_assoc($gquery,1) or die("Can't read last valid gid".$connect->ErrorMsg());
	while ($grow=$gresult->FetchRow())
	{
		$gid=$grow['gid'];
		//echo "TIBO: extrapoling group=$gid //".$grow['group_name']."\n";
		$newgroupquestion=true;
	}
}

if (empty($qid))
{ // this is a new question or a new group
  // take the previous question Id and set a flag
	$qquery="SELECT title,qid FROM {$dbprefix}questions WHERE sid=$surveyid AND gid=$gid " 
		."AND language='".GetBaseLanguageFromSurveyID($surveyid)."' "
		."ORDER BY question_order DESC";
	$qresult = db_select_limit_assoc($qquery,1) or die("Can't read last valid qid".$connect->ErrorMsg());
	while ($qrow=$qresult->FetchRow())
	{
		$qid=$qrow['qid'];
		//echo "TIBO: extrapoling ques=$qid //".$qrow['title']."\n";
		$newgroupquestion=true;
	}
}
if (empty($gid)) {die("No GID provided.");}



if (empty($qid)) {die("No QID provided.");}

// *******************************************************************
// ** ADD FORM
// *******************************************************************

//2: Get all other questions that occur before this question that are pre-determined answer types

//TO AVOID NATURAL SORT ORDER ISSUES,
//FIRST GET ALL QUESTIONS IN NATURAL SORT ORDER
//, AND FIND OUT WHICH NUMBER IN THAT ORDER THIS QUESTION IS
$qquery = "SELECT * "
        ."FROM {$dbprefix}questions, "
             ."{$dbprefix}groups "
        ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid "
        ."AND {$dbprefix}questions.sid=$surveyid "
          ."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
          ."AND {$dbprefix}groups.language='".GetBaseLanguageFromSurveyID($surveyid)."' " ;

$qresult = db_execute_assoc($qquery) or die ("$qquery<br />".$connect->ErrorMsg());
$qrows = $qresult->GetRows();
// Perform a case insensitive natural sort on group name then question title (known as "code" in the form) of a multidimensional array
usort($qrows, 'CompareGroupThenTitle');

//Go through each question until we reach the current one
foreach ($qrows as $qrow)
{
	if ($qrow["qid"] != $qid)
	{
		if ($qrow['type'] != "TIBO-T" && // $qrow['type'] != "S" &&
			$qrow['type'] != "TIBO-Q" &&
			$qrow['type'] != "TIBO-K")   //&& $qrow['type'] != "D"
		{ //remember the questions of this type
			$questionlist[]=$qrow["qid"];
		}
	}
	elseif ($qrow["qid"] == $qid)
	{
		if ($newgroupquestion === true )
		{
			$questionlist[]=$qrow["qid"];
			break;
		}
		else
		{
			break;
		}
	}
}

$theserows=array();
//Now, use the questions left appart in the step before, these of type S, T or Q 
//(originally was D too)
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
                   ."{$dbprefix}questions.title "
              ."FROM {$dbprefix}questions, "
                   ."{$dbprefix}groups "
             ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid "
               ."AND {$dbprefix}questions.qid=$ql "
               ."AND {$dbprefix}questions.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
               ."AND {$dbprefix}groups.language='".GetBaseLanguageFromSurveyID($surveyid)."'" ;

        $result=db_execute_assoc($query) or die("Couldn't get question $qid");

		$thiscount=$result->RecordCount();

    // And store ¿again? these questions in this array...
		while ($myrows=$result->FetchRow())
    {                   //key => value
      $theserows[]=array("qid"=>$myrows['qid'],
                         "sid"=>$myrows['sid'],
                         "gid"=>$myrows['gid'],
                         "question"=>$myrows['question'],
                         "type"=>$myrows['type'],
                         "lid"=>$myrows['lid'],
                         "title"=>$myrows['title']);
		}
	}
}


$questionscount=count($theserows);

if ($questionscount > 0)
{
	$X="X";
  // Will detect if the questions are type D to use latter

  $dquestions=array();

	foreach($theserows as $rows)
	{
	    $shortquestion=$rows['title'].": ".strip_tags($rows['question']);

    if ($rows['type'] == "A" ||
        $rows['type'] == "B" ||
        $rows['type'] == "C" ||
        $rows['type'] == "E" ||
        $rows['type'] == "F" ||
        $rows['type'] == "H" ||
        $rows['type'] == "Q" ||
        $rows['type'] == "K") // K added by lemeur
    {
      $aquery="SELECT * "
               ."FROM {$dbprefix}answers "
              ."WHERE qid={$rows['qid']} "
                ."AND {$dbprefix}answers.language='".GetBaseLanguageFromSurveyID($surveyid)."' "
           ."ORDER BY sortorder, "
                    ."answer";

			$aresult=db_execute_assoc($aquery) or die ("Couldn't get answers to Array questions<br />$aquery<br />".$connect->ErrorMsg());

			while ($arows = $aresult->FetchRow())
			{
                $shortanswer = strip_tags($arows['answer']);

				$shortanswer .= " [{$arows['code']}]";
				$cquestions[]=array("$shortquestion [$shortanswer]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code']);

      } //while
    } //if A,B,C,E,F,H
    elseif ($rows['type'] == "R") //Answer Ranking
		{
      $aquery="SELECT * "
             ."FROM {$dbprefix}answers "
             ."WHERE qid={$rows['qid']} "
             ."AND ".db_table_name('answers').".language='".GetBaseLanguageFromSurveyID($surveyid)."' "
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
			}
			unset($quicky);
    } // for type R
		else
		{
			$cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid']);
    } //else
  } //foreach theserows
} //if questionscount > 0

// Now I´ll add a hack to add the questions before as option
// if they are date type


//$limereplacementoutput .="\t\t\t<div style='overflow-x:scroll; width:100%; overflow: -moz-scrollbars-horizontal; overflow-y:scroll; height: 100px;'>\n"


$limereplacementoutput=""
	. "<script language=\"javascript\">\n"
	. "var oEditor = window.parent.InnerDialogLoaded() ;\n"
	. "var FCKLang = oEditor.FCKLang ;\n"
	. "var FCKLimeReplacementFieldss = oEditor.FCKLimeReplacementFieldss ;\n"
	. "window.onload = function ()\n"
	. "{\n"
	. "\twindow.parent.SetOkButton( true ) ;\n"
	. "}\n"
	. "function Ok()\n"
	. "{\n"
	. "\tvar sValue = document.getElementById('cquestions').value ;\n"
	. "\tFCKLimeReplacementFieldss.Add( sValue ) ;\n"
	. "}\n"
	. "</script>\n";


$limereplacementoutput .= "\t\t\t<select name='cquestions' id='cquestions' style='font-family:verdana; background-color: #FFFFFF; font-size:10; border: 0px;' size='15'>\n";
if (isset($cquestions))
{
	foreach ($cquestions as $cqn)
	{
		$limereplacementoutput .= "\t\t\t\t<option value='$cqn[3]'";
		if (isset($_GET['cquestions']) && $cqn[3] == $_GET['cquestions']) {
			$limereplacementoutput .= " selected";
		}
		$limereplacementoutput .= ">$cqn[0]</option>\n";
	}
}
$limereplacementoutput .= "\t\t\t</select>\n";

echo $limereplacementoutput;

?>
