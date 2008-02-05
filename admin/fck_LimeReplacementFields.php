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

include_once("login_check.php");

if(!isset($_SESSION['loginID']))
{
	die ("Unauthenticated Access Forbiden");
}

$surveyid=returnglobal('sid');
if (!isset($gid)) {$gid=returnglobal('gid');}
if (!isset($qid)) {$qid=returnglobal('qid');}
$fieldtype=preg_replace("/[^_.a-zA-Z0-9-]/", "",$_GET['fieldtype']);
$action=preg_replace("/[^_.a-zA-Z0-9-]/", "",$_GET['editedaction']);

//$InsertansUnsupportedtypes=Array('TEST-A','TEST-B','TEST-C','TEST-D');
$InsertansUnsupportedtypes=Array(); // Currently all question types are supported

$replFields=Array();
$isInstertansEnabled=false;

$limereplacementoutput=""
	. "<script language=\"javascript\">\n"
	. "var oEditor = window.parent.InnerDialogLoaded() ;\n"
	. "var dialog = oEditor.FCK ;\n"
	. "var FCKLang = oEditor.FCKLang ;\n"
	. "var FCKLimeReplacementFieldss = oEditor.FCKLimeReplacementFieldss ;\n"
	. "window.onload = function ()\n"
	. "{\n"
	. "\toEditor.FCKLanguageManager.TranslatePage( document ) ;\n"
	. "\tLoadSelected() ;\n"
	. "\twindow.parent.SetOkButton( true ) ;\n"
	. "}\n"
	. "\n"
	. "var innertext = '' + dialog.EditorWindow.getSelection() + '' ;\n"
	. "\n"
	. "function Ok()\n"
	. "{\n"
	. "\tvar sValue = document.getElementById('cquestions').value ;\n"
	. "\tFCKLimeReplacementFieldss.Add( sValue ) ;\n"
	. "\treturn true ;\n"
	. "}\n"
	. "function LoadSelected()\n"
	. "{\n"
	. "\tif ( innertext == '' )\n"
	. "\t\treturn ;\n"
//	. "\tif ( eSelected.tagName == 'SPAN' && eSelected._fckLimeReplacementFields )\n"
	. "var replcode=innertext.substring(innertext.indexOf('{')+1,innertext.lastIndexOf('}'));\n"
//	. "alert('TIBO=' + replcode);\n"
	. "\t\tdocument.getElementById('cquestions').value = replcode;\n"
//	. "\telse\n"
//	. "\t\teSelected == null ;\n"
	. "}\n"
	. "</script>\n";
	
	
$limereplacementoutput .= "<table><tr><td>";

switch ($fieldtype)
{
	case 'survey-desc':
	case 'survey-welc':
	break;

	case 'email-inv':
	case 'email-rem':
	case 'email-conf':
	case 'email-reg':
		$replFields[]=array('FIRSTNAME',$clang->gT("Firstname from token"));
		$replFields[]=array('LASTNAME',$clang->gT("Lastname from token"));
		$replFields[]=array('SURVEYNAME',$clang->gT("Name of the survey"));
		$replFields[]=array('SURVEYDESCRIPTION',$clang->gT("Description of the survey"));
		$replFields[]=array('ATTRIBUTE_1',$clang->gT("Attribute_1 from token"));
		$replFields[]=array('ATTRIBUTE_2',$clang->gT("Attribute_2 from token"));
		$replFields[]=array('ADMINNAME',$clang->gT("Name of the survey administrator"));
		$replFields[]=array('ADMINEMAIL',$clang->gT("Email address of the survey administrator"));
		$replFields[]=array('SURVEYURL',$clang->gT("URL of the survey"));
	break;

	case 'group-desc':
	case 'question-text':
	case 'question-help':
		$replFields[]=array('TOKEN:FIRSTNAME',$clang->gT("Firstname from token"));
		$replFields[]=array('TOKEN:LASTNAME',$clang->gT("Lastname from token"));
		$replFields[]=array('TOKEN:EMAIL',$clang->gT("Email from the token"));
		$replFields[]=array('TOKEN:ATTRIBUTE_1',$clang->gT("Attribute_1 from token"));
		$replFields[]=array('TOKEN:ATTRIBUTE_2',$clang->gT("Attribute_2 from token"));
		$isInstertansEnabled=true;
	break;
}


if ($isInstertansEnabled===true)
{
	if (empty($surveyid)) {die("No SID provided.");}
	
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
	


	$surveyformat =  getSurveyFormat($surveyid);// S, G, A
	$prevquestion=null;
	$previouspagequestion = true;
	//Go through each question until we reach the current one
	//error_log(print_r($qrows,true));
	foreach ($qrows as $qrow)
	{
		$AddQuestion=True;
		switch ($action)
		{
			case 'addgroup':
				$AddQuestion=True;
			break;

			case 'editgroup':
				if (empty($gid)) {die("No GID provided.");}

				if ($qrow['gid'] == $gid)
				{
					$AddQuestion=False;
				}
			break;

			case 'addquestion':
				if (empty($gid)) {die("No GID provided.");}

				if ( !is_null($prevquestion) &&
					$prevquestion['gid'] == $gid &&
					$qrow['gid'] != $gid)
				{
					$AddQuestion=False;
				}
			break;

			case 'editquestion':
				if (empty($gid)) {die("No GID provided.");}
				if (empty($qid)) {die("No QID provided.");}

				if ($qrow['gid'] == $gid &&
					$qrow['qid'] == $qid)
				{
					$AddQuestion=False;
				}
			break;
			default:
				die("No Action provided.");
			break;
		}

		if ( $AddQuestion===True)
		{
			if ($surveyformat == "S")
			{
				$previouspagequestion = true;
			}
			elseif ($surveyformat == "G")
			{
				if ($previouspagequestion === true)
				{ // Last question was on a previous page
					if ($qrow["gid"] == $gid)
					{ // This question is on same page
						$previouspagequestion = false;
					}
				}
			}
			elseif ($surveyformat == "A")
			{
				$previouspagequestion = false;
			}

			$questionlist[]=Array( "qid" => $qrow["qid"], "previouspage" => $previouspagequestion);
			$prevquestion=$qrow;
		}
		else
		{
			break;
		}
	}
	
//		if ($qrow["qid"] != $qid)
//		{
//			if (!in_array($qrow['type'],$InsertansUnsupportedtypes)
//			{ //remember the questions of this type
//				$questionlist[]=$qrow["qid"];
//			}
//		}

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
	                   ."{$dbprefix}questions.title "
	              ."FROM {$dbprefix}questions, "
	                   ."{$dbprefix}groups "
	             ."WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid "
	               ."AND {$dbprefix}questions.qid=".$ql['qid']." "
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
	                         "previouspage"=>$ql['previouspage'],
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
					$cquestions[]=array("$shortquestion [$shortanswer]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$arows['code'],$rows['previouspage']);
	
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
					$cquestions[]=array("$shortquestion [RANK $i]", $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'].$i,$rows['previouspage']);
				}
				unset($quicky);
	    } // for type R
			else
			{
				$cquestions[]=array($shortquestion, $rows['qid'], $rows['type'], $rows['sid'].$X.$rows['gid'].$X.$rows['qid'],$rows['previouspage']);
	    } //else
	  } //foreach theserows
	} //if questionscount > 0
	
	// Now I´ll add a hack to add the questions before as option
	// if they are date type
	
	
	//$limereplacementoutput .="\t\t\t<div style='overflow-x:scroll; width:100%; overflow: -moz-scrollbars-horizontal; overflow-y:scroll; height: 100px;'>\n"
	
	
}


if (count($replFields) > 0 || isset($cquestions) )
{
	$limereplacementoutput .= "\t\t\t<select name='cquestions' id='cquestions' style='font-family:verdana; background-color: #FFFFFF; font-size:10; border: 0px;width:99%;' size='15' ondblclick='Ok();'>\n";
}
else
{
	$limereplacementoutput = $clang->gT("No replacement variable available for this field");
	echo $limereplacementoutput;
	return;
}

if (count($replFields) > 0)
{
	$limereplacementoutput .= "\t\t\t\t<optgroup label='".$clang->gT("Standard Fields")."'>\n";

	foreach ($replFields as $stdfield)
	{
		$limereplacementoutput .= "\t\t\t\t<option value='".$stdfield[0]."'";
		$limereplacementoutput .= ">".$stdfield[1]."</option>\n";
	}
	$limereplacementoutput .= "\t\t\t\t</optgroup>\n";
}

if (isset($cquestions))
{
	$limereplacementoutput .= "\t\t\t\t<optgroup label='".$clang->gT("Previous Answers Fields")."'>\n";
	foreach ($cquestions as $cqn)
	{
		$isDisabled="";
		if (in_array($cqn[2],$InsertansUnsupportedtypes))
		{
			 $isDisabled=" disabled='disabled'";
		}
		elseif ($cqn[4] === false)
		{
			 $isDisabled=" disabled='disabled'";
		}

		$limereplacementoutput .= "\t\t\t\t<option value='INSERTANS:$cqn[3]'";
		$limereplacementoutput .= " $isDisabled >$cqn[0]</option>\n";
	}
	$limereplacementoutput .= "\t\t\t\t</optgroup>\n";
}


$limereplacementoutput .= "\t\t\t</select>\n";
$limereplacementoutput .= "</td></tr>\n";


switch ($surveyformat)
{
	case 'A':
		$limereplacementoutput .= "<tr><td>\n";
		$limereplacementoutput .= "<br /><font color='orange'>".$clang->gT("Some Question have been disabled")."</font>";
		$limereplacementoutput .= "<br />".$clang->gT("Survey Format is ")." ".$clang->gT("All in one").": <br /><i>".$clang->gT("Only Previous pages answers are available")."</i><br />";
		$limereplacementoutput .= "</td></tr>\n";
	break;
	case 'G':
		$limereplacementoutput .= "<tr><td>\n";
		$limereplacementoutput .= "<br /><font color='orange'>".$clang->gT("Some Question have been disabled")."</font>";
		$limereplacementoutput .= "<br />".$clang->gT("Survey mode is set to ")." ".$clang->gT("Group by Group").": <br/><i>".$clang->gT("Only Previous pages answers are available")."</i><br />";
		$limereplacementoutput .= "</td></tr>\n";
	break;
}

$limereplacementoutput .= "</table>\n";

echo $limereplacementoutput;

?>
