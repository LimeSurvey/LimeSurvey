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

// Security Checked: POST, GET, SESSION, REQUEST, returnglobal, DB

if (!isset($homedir) || isset($_REQUEST['$homedir'])) {die("Cannot run this script directly");}

/*
* Let's explain what this strange $ia var means
*
* $ia[0] => question id
* $ia[1] => fieldname
* $ia[2] => title
* $ia[3] => question text
* $ia[4] => type --  text, radio, select, array, etc
* $ia[5] => group id
* $ia[6] => mandatory Y || N
* $ia[7] => conditions ??
*
* $conditions element structure
* $condition[n][0] => qid = question id
* $condition[n][1] => cqid = question id of the target question, or 0 for TokenAttr leftOperand
* $condition[n][2] => field name of element [1] (Except for type M or P)
* $condition[n][3] => value to be evaluated on answers labeled.
* $condition[n][4] => type of question
* $condition[n][5] => SGQ code of element [1] (sub-part of [2])
* $condition[n][6] => method used to evaluate
* $condition[n][7] => scenario *NEW BY R.L.J. van den Burg*
*/

function retrieveConditionInfo($ia)
{
	//This function returns an array containing all related conditions
	//for a question - the array contains the fields from the conditions table
	global $dbprefix, $connect;

	if ($ia[7] == "Y")
	{	//DEVELOP CONDITIONS ARRAY FOR THIS QUESTION
		$cquery =	"SELECT {$dbprefix}conditions.qid, "
				      ."{$dbprefix}conditions.scenario, "
				      ."{$dbprefix}conditions.cqid, "
				      ."{$dbprefix}conditions.cfieldname, "
				      ."{$dbprefix}conditions.value, "
				      ."{$dbprefix}questions.type, "
				      ."{$dbprefix}questions.sid, "
				      ."{$dbprefix}questions.gid, "
				      ."{$dbprefix}conditions.method "
				."FROM {$dbprefix}conditions, "
				     ."{$dbprefix}questions "
				."WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid "
				."AND {$dbprefix}conditions.qid=$ia[0] "
				."AND {$dbprefix}questions.language='".$_SESSION['s_lang']."' "
				."AND {$dbprefix}conditions.cfieldname NOT LIKE '{%' "
				."ORDER BY {$dbprefix}conditions.scenario, "
					 ."{$dbprefix}conditions.cqid, "
					 ."{$dbprefix}conditions.cfieldname";
		$cresult = db_execute_assoc($cquery) or safe_die ("OOPS<br />$cquery<br />".$connect->ErrorMsg());     //Checked
		$cquerytoken =	"SELECT {$dbprefix}conditions.qid, "
				      ."{$dbprefix}conditions.scenario, "
				      ."{$dbprefix}conditions.cqid, "
				      ."{$dbprefix}conditions.cfieldname, "
				      ."{$dbprefix}conditions.value, "
				      ."'' as type, "
				      ."0 as sid, "
				      ."0 as gid, "
				      ."{$dbprefix}conditions.method "
				."FROM {$dbprefix}conditions "
				."WHERE "
				." {$dbprefix}conditions.qid=$ia[0] "
				."AND {$dbprefix}conditions.cfieldname LIKE '{%' "
				."ORDER BY {$dbprefix}conditions.scenario, "
				 ."{$dbprefix}conditions.cqid, "
				 ."{$dbprefix}conditions.cfieldname";
		$cresulttoken = db_execute_assoc($cquerytoken) or safe_die ("OOPS<br />$cquerytoken<br />".$connect->ErrorMsg());     //Checked

		while ($tempcrow = $cresulttoken->FetchRow())
		{
			$aAllConditions[] = $tempcrow;
		}
		while ($tempcrow = $cresult->FetchRow())
		{
			$aAllConditions[] = $tempcrow;
		}
//		while ($crow = $cresult->FetchRow())
		foreach ($aAllConditions as $crow)
		{
			if (preg_match("/^\+(.*)$/",$crow['cfieldname'],$cfieldnamematch))
			{ // this condition uses a single checkbox as source
				$crow['type'] = "+".$crow['type'];
				$crow['cfieldname'] = $cfieldnamematch[1];
			}

			$conditions[] = array ($crow['qid'],
						$crow['cqid'],
						$crow['cfieldname'],
						$crow['value'],
						$crow['type'],
						$crow['sid']."X".$crow['gid']."X".$crow['cqid'],
						$crow['method'],
						$crow['scenario']);
		}
		return $conditions;
	}
	else
	{
		return null;
	}
}

// returns the Javascript IdName of a question used in conditions 
// $cd = Array (
//   0 => Unused
//   1 => qid of the question
//   2 => fieldname of the question
//   3 => value used in comparison (only usd for type M and P egals 'Y', optionnal for other types)
//   4 => type of the question
//   5 => SGQ code corresponding to the fieldname
// if $currentgid is not null (Group by group survey), the fieldname depends on the groupId
function retrieveJSidname($cd,$currentgid=null)
{
	global $dbprefix, $connect;

	preg_match("/^[0-9]+X([0-9]+)X([0-9]+)$/",$cd[5],$matchGID);
	$questiongid=$matchGID[1];

	if ($cd[4] == "L")
	{
		$cccquery="SELECT code FROM {$dbprefix}answers WHERE qid={$cd[1]} AND language='".$_SESSION['s_lang']."'";
		$cccresult=$connect->Execute($cccquery); // Checked
		$cccount=$cccresult->RecordCount();
	}
	if ($cd[4] == "R")
	{
		if (!isset($currentgid) || $questiongid == $currentgid)
		{ // if question is on same page then field is fvalue_XXXX
			$idname="fvalue_".$cd[1].substr($cd[2], strlen($cd[2])-1,1);
		}
		else
		{ // If question is on another page then field if javaXXXX
			$idname="java$cd[2]";
		} 
	}
	elseif ($cd[4] == "5" ||
			$cd[4] == "A" ||
			$cd[4] == "B" ||
			$cd[4] == "C" ||
			$cd[4] == "E" ||
			$cd[4] == "F" ||
			$cd[4] == "H" ||
			$cd[4] == "G" ||
			$cd[4] == "Y" ||
			$cd[4] == "1" ||
			($cd[4] == "L" && $cccount <= $dropdownthreshold))
	{
		$idname="java$cd[2]";
	}
	elseif ($cd[4] == "M" || 
			$cd[4] == "P")
	{
		$idname="java$cd[5]$cd[3]";
	}
	elseif ($cd[4] == "+M" || 
			$cd[4] == "+P")
	{
		$idname="java$cd[2]";
	}
	elseif ($cd[4] == "D" ||
			$cd[4] == "N" ||
			$cd[4] == "S" ||
			$cd[4] == "T" ||
			$cd[4] == "U" ||
			$cd[4] == "Q" ||
			$cd[4] == "K" )
	{
		if (!isset($currentgid) || $questiongid == $currentgid)
		{ // if question is on same page then field is answerXXXX
			$idname="answer$cd[2]";
		}
		else
		{ // If question is on another page then field if javaXXXX
			$idname="java$cd[2]";
		}
	}
	else
	{
		$idname="java".$cd[2];
	}

	return $idname;
}

function create_mandatorylist($ia)
{
	//Checks current question and returns required mandatory arrays if required
	if ($ia[6] == 'Y')
	{
		switch($ia[4])
		{
			case 'R':
				$thismandatory = setman_ranking($ia);
				break;
			case 'M':
				$thismandatory = setman_questionandcode($ia);
				break;
			case 'J':
			case 'P':
			case 'Q':
			case 'K':
			case 'A':
			case 'B':
			case 'C':
			case 'E':
			case 'F':
			case 'H':
				$thismandatory = setman_questionandcode($ia);
				break;
			case ':':
			case ';':
			    $thismandatory = setman_multiflex($ia);
			    break;
			case '1':
				$thismandatory = setman_questionandcode_multiscale($ia);
				break;
			case 'X':
			//Do nothing - boilerplate questions CANNOT be mandatory
				break;
			default:
				$thismandatory = setman_normal($ia);
		}

		if ($ia[7] != 'Y' && isset($thismandatory)) //Question is not conditional - addto mandatory arrays
		{
			$mandatory=$thismandatory;
		}
		if ($ia[7] == 'Y' && isset($thismandatory)) //Question IS conditional - add to conmandatory arrays
		{
			$conmandatory=$thismandatory;
		}
	}

	if (isset($mandatory))
	{
		return array($mandatory, null);
	}
	elseif (isset($conmandatory))
	{
		return array(null, $conmandatory);
	}
	else
	{
		return array(null, null);
	}
}

function setman_normal($ia)
{
	$mandatorys[]=$ia[1];
	$mandatoryfns[]=$ia[1];
	return array($mandatorys, $mandatoryfns);
}

function setman_ranking($ia)
{
	global $dbprefix, $connect;
	$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	$ansresult = $connect->Execute($ansquery);  //Checked
	$anscount = $ansresult->RecordCount();
	$qidattributes=getQuestionAttributes($ia[0]);

	if ($ma=arraySearchByKey("max_answers", $qidattributes, "attribute", 1)) {
		$max_answers = $ma['value'];
	}
	else
	{
		$max_answers = $anscount;
	}

	for ($i=1; $i<=$max_answers; $i++)
	{
		$mandatorys[]=$ia[1].$i;
		$mandatoryfns[]=$ia[1];
	}

	return array($mandatorys, $mandatoryfns);
}

function setman_questionandcode($ia)
{
	global $dbprefix, $connect;
	$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
	$qresult = db_execute_assoc($qquery);     //Checked
	while ($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}
	$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	$ansresult = db_execute_assoc($ansquery); //Checked

	while ($ansrow = $ansresult->FetchRow())
	{
		$mandatorys[]=$ia[1].$ansrow['code'];
		$mandatoryfns[]=$ia[1];
	}

	if ($other == "Y" and ($ia[4]=="!" or $ia[4]=="L" or $ia[4]=="M" or $ia[4]=="P"))
	{
		$mandatorys[]=$ia[1]."other";
		$mandatoryfns[]=$ia[1];
	}

	return array($mandatorys, $mandatoryfns);
}

function setman_multiflex($ia)
{
    //The point of these functions (setman) is to return an array containing two arrays. 
	// The first ($mandatorys) is an array containing question, so they can all be checked
	// The second ($mandatoryfns) is an arry containing the fieldnames of every question
	// What's the difference? The difference arises from multiple option questions, and came
	// about when trying to distinguish between answering just one option (which satisfies
	// the mandatory requirement, and answering them all). The "mandatorys" input contains the
	// actual specific response items that could be filled in.. ie: in a multiple option
	// question, there will be a unique one for every possible answer. The "mandatoryfns" array
	// contains the generic question fieldname for the question as a whole (it will be repeated
	// for multiple option qeustions, but won't contain unique items.
	global $dbprefix, $connect;
	
	$qq="SELECT lid FROM {$dbprefix}questions WHERE qid={$ia[0]}";
	$qr=db_execute_assoc($qq);

	while($qd=$qr->FetchRow())
	{
		$lid=$qd['lid'];
	}

	$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	$ansresult = db_execute_assoc($ansquery);
	$ans2query = "SELECT * FROM {$dbprefix}labels WHERE lid={$lid} AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, title";
	$ans2result = db_execute_assoc($ans2query);

	while ($ans2row=$ans2result->FetchRow())
	{
		$lset[]=$ans2row;
	}

	$qidattributes=getQuestionAttributes($ia[0]);

	while ($ansrow = $ansresult->FetchRow())
	{
		//Don't add to mandatory list if the row is filtered out with the array_filter option
		if ($htmltbody=arraySearchByKey("array_filter", $qidattributes, "attribute", 1))
		{
			//This particular one may not be mandatory if it's hidden
			$selected = getArrayFiltersForQuestion($ia[0]);
			if (!in_array($ansrow['code'],$selected))
			{
				//This one's hidden, so don't add it to the mandatory list
			}
			else
			{
				//This one's not hidden. so add it to the mandatory list
			}
		} else { //There is no array_filter option, so we should definitely add to the mandatory list here!
			foreach($lset as $ls)
			{
				$mandatorys[]=$ia[1].$ansrow['code']."_".$ls['code'];
				$mandatoryfns[]=$ia[1];
			}
		}
	}

	return array($mandatorys, $mandatoryfns);
}

function setman_questionandcode_multiscale($ia)
{
	global $dbprefix, $connect;
	$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
	$qresult = db_execute_assoc($qquery);   //Checked
	while ($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}
	$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	$ansresult = db_execute_assoc($ansquery); //Checked

	$lquery = "SELECT q.qid FROM {$dbprefix}labels l, {$dbprefix}questions q WHERE l.lid = q.lid AND q.qid=".$ia[0]." AND l.language='".$_SESSION['s_lang']."' AND q.language='".$_SESSION['s_lang']."'";
	$labelsresult = db_execute_assoc($lquery);   //Checked
	$labelscount = $labelsresult->RowCount();
	
	$lquery1 = "SELECT q.qid FROM {$dbprefix}labels l, {$dbprefix}questions q WHERE l.lid = q.lid1 AND q.qid=".$ia[0]." AND l.language='".$_SESSION['s_lang']."' AND q.language='".$_SESSION['s_lang']."'";
	$labelsresult1 = db_execute_assoc($lquery1);   //Checked
	$labelscount1 = $labelsresult1->RowCount();

	while ($ansrow = $ansresult->FetchRow())
	{
		if ($labelscount > 0)
		{
				$mandatorys[]=$ia[1].$ansrow['code']."#0";
				$mandatoryfns[]=$ia[1];
		}
		else
		{
			$mandatorys[]=$ia[1].$ansrow['code'];
			$mandatoryfns[]=$ia[1];
		}
		// second label set

		if ($labelscount1 > 0)
		{
				$mandatorys[]=$ia[1].$ansrow['code']."#1";
				$mandatoryfns[]=$ia[1];
		}
		else
		{
			$mandatorys[]=$ia[1].$ansrow['code'];
			$mandatoryfns[]=$ia[1];
		}
	}

	if ($other == "Y" and ($ia[4]=="!" or $ia[4]=="L" or $ia[4]=="M" or $ia[4]=="P" or $ia[4]=="1"))
	{
		$mandatorys[]=$ia[1]."other";
		$mandatoryfns[]=$ia[1];
	}

	return array($mandatorys, $mandatoryfns);
}


function retrieveAnswers($ia, $notanswered=null, $notvalidated=null)
{
	//This function returns an array containing the "question/answer" html display
	//and a list of the question/answer fieldnames associated. It is called from
	//question.php, group.php or survey.php

	//globalise required config variables
	global $dbprefix, $shownoanswer, $clang; //These are from the config-defaults.php file
	//-----
	global $thissurvey, $gl; //These are set by index.php
	global $connect;

	//DISPLAY
	$display = $ia[7];

	//QUESTION NAME
	$name = $ia[0];

	$qtitle=$ia[3];
	//Replace INSERTANS statements with previously provided answers;
	while (strpos($qtitle, "{INSERTANS:") !== false)
	{
		$replace=substr($qtitle, strpos($qtitle, "{INSERTANS:"), strpos($qtitle, "}", strpos($qtitle, "{INSERTANS:"))-strpos($qtitle, "{INSERTANS:")+1);
		$replace2=substr($replace, 11, strpos($replace, "}", strpos($replace, "{INSERTANS:"))-11);
		$replace3=retrieve_Answer($replace2);
		$qtitle=str_replace($replace, $replace3, $qtitle);
	} //while

	//GET HELP
	$hquery="SELECT help FROM {$dbprefix}questions WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."'";
	$hresult=db_execute_num($hquery) or safe_die($connect->ErrorMsg());       //Checked
	$help="";
	while ($hrow=$hresult->FetchRow()) {$help=$hrow[0];}

	//A bit of housekeeping to stop PHP Notices
	$answer = "";
	if (!isset($_SESSION[$ia[1]])) {$_SESSION[$ia[1]] = "";}
	$qidattributes=getQuestionAttributes($ia[0]);
	//echo "<pre>";print_r($qidattributes);echo "</pre>";
	//Create the question/answer html
	switch ($ia[4])
	{
		case 'X': //BOILERPLATE QUESTION
			$values = do_boilerplate($ia);
			break;
		case '5': //5 POINT CHOICE radio-buttons
			$values = do_5pointchoice($ia);
			break;
		case 'D': //DATE
			$values = do_date($ia);
			break;
		case 'Z': //LIST Flexible drop-down/radio-button list
			$values = do_list_flexible_radio($ia);
			if (!$displaycols=arraySearchByKey('hide_tip', $qidattributes, 'attribute', 1))
			{
				$qtitle .= "<br />\n<span class=\"questionhelp\">"
				. $clang->gT('Choose one of the following answers').'</span>';
			}
			break;
		case 'L': //LIST drop-down/radio-button list
			$values = do_list_radio($ia);
			if (!$displaycols=arraySearchByKey('hide_tip', $qidattributes, 'attribute', 1))
			{
				$qtitle .= "<br />\n<span class=\"questionhelp\">"
				. $clang->gT('Choose one of the following answers').'</span>';
			}
			break;
		case 'W': //List - dropdown
			$values=do_list_flexible_dropdown($ia);
			if (!$displaycols=arraySearchByKey('hide_tip', $qidattributes, 'attribute', 1))
			{
				$qtitle .= "<br />\n<span class=\"questionhelp\">"
				. $clang->gT('Choose one of the following answers').'</span>';
			}
			break;
		case '!': //List - dropdown
			$values=do_list_dropdown($ia);
			if (!$displaycols=arraySearchByKey('hide_tip', $qidattributes, 'attribute', 1))
			{
				$qtitle .= "<br />\n<span class=\"questionhelp\">"
				. $clang->gT('Choose one of the following answers').'</span>';
			}
			break;
		case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
			$values=do_listwithcomment($ia);
			if (count($values[1]) > 1 && !$displaycols=arraySearchByKey('hide_tip', $qidattributes, 'attribute', 1))
			{
				$qtitle .= "<br />\n<span class=\"questionhelp\">"
				. $clang->gT('Choose one of the following answers').'</span>';
			}
			break;
		case 'R': //RANKING STYLE
			$values=do_ranking($ia);
			if (count($values[1]) > 1 && !$displaycols=arraySearchByKey('hide_tip', $qidattributes, 'attribute', 1))
			{
				if ($minansw=arraySearchByKey("min_answers", $qidattributes, "attribute", 1))
				{
					$qtitle .= "<br />\n<span class=\"questionhelp\">"
					. sprintf($clang->gT("Rank at least %d items"), $minansw['value'])."</span>";
				
				}
			}
			break;
		case 'M': //MULTIPLE OPTIONS checkbox
			$values=do_multiplechoice($ia);
			if (count($values[1]) > 1 && !$displaycols=arraySearchByKey('hide_tip', $qidattributes, 'attribute', 1))
			{
				$maxansw=arraySearchByKey("max_answers", $qidattributes, "attribute", 1);
				$minansw=arraySearchByKey("min_answers", $qidattributes, "attribute", 1);
				if (!($maxansw || $minansw))
				{
					$qtitle .= "<br />\n<span class=\"questionhelp\">"
					. $clang->gT('Check any that apply').'</span>';
				}
				else
				{
					if ($maxansw && $minansw)
					{
						$qtitle .= "<br />\n<span class=\"questionhelp\">"
						. sprintf($clang->gT("Check between %d and %d answers"), $minansw['value'], $maxansw['value'])."</span>";
					} elseif ($maxansw) 
					{
						$qtitle .= "<br />\n<span class=\"questionhelp\">"
						. sprintf($clang->gT("Check at most %d answers"), $maxansw['value'])."</span>";
					} else 
					{
						$qtitle .= "<br />\n<span class=\"questionhelp\">"
						. sprintf($clang->gT("Check at least %d answers"), $minansw['value'])."</span>";
					}
				}
			}
			break;

		case 'I': //Language Question
			$values=do_language($ia);
			if (count($values[1]) > 1)
			{
				$qtitle .= "<br />\n<span class=\"questionhelp\">"
				. $clang->gT('Choose your language').'</span>';
			}
			break;
		case 'P': //MULTIPLE OPTIONS WITH COMMENTS checkbox + text
			$values=do_multiplechoice_withcomments($ia);
			if (count($values[1]) > 1 && !$displaycols=arraySearchByKey('hide_tip', $qidattributes, 'attribute', 1))
			{
				$maxansw=arraySearchByKey("max_answers", $qidattributes, "attribute", 1);
				$minansw=arraySearchByKey("min_answers", $qidattributes, "attribute", 1);
				if (!($maxansw || $minansw))
				{
					$qtitle .= "<br />\n<span class=\"questionhelp\">"
					. $clang->gT('Check any that apply').'</span>';
				}
				else
				{
					if ($maxansw && $minansw)
					{
						$qtitle .= "<br />\n<span class=\"questionhelp\">"
						. sprintf($clang->gT("Check between %d and %d answers"), $minansw['value'], $maxansw['value'])."</span>";
					} elseif ($maxansw) 
					{
						$qtitle .= "<br />\n<span class=\"questionhelp\">"
						. sprintf($clang->gT("Check at most %d answers"), $maxansw['value'])."</span>";
					} else 
					{
						$qtitle .= "<br />\n<span class=\"questionhelp\">"
						. sprintf($clang->gT("Check at least %d answers"), $minansw['value'])."</span>";
					}
				}
			}
			break;
		case 'Q': //MULTIPLE SHORT TEXT
			$values=do_multipleshorttext($ia);
			break;
		case 'K': //MULTIPLE NUMERICAL QUESTION
			$values=do_multiplenumeric($ia);
			break;
		case 'N': //NUMERICAL QUESTION TYPE
			$values=do_numerical($ia);
			break;
		case 'S': //SHORT FREE TEXT
			$values=do_shortfreetext($ia);
			break;
		case 'T': //LONG FREE TEXT
			$values=do_longfreetext($ia);
			break;
		case 'U': //HUGE FREE TEXT
			$values=do_hugefreetext($ia);
			break;
		case 'Y': //YES/NO radio-buttons
			$values=do_yesno($ia);
			break;
		case 'G': //GENDER drop-down list
			$values=do_gender($ia);
			break;
		case 'A': //ARRAY (5 POINT CHOICE) radio-buttons
			$values=do_array_5point($ia);
			break;
		case 'B': //ARRAY (10 POINT CHOICE) radio-buttons
			$values=do_array_10point($ia);
			break;
		case 'C': //ARRAY (YES/UNCERTAIN/NO) radio-buttons
			$values=do_array_yesnouncertain($ia);
			break;
		case 'E': //ARRAY (Increase/Same/Decrease) radio-buttons
			$values=do_array_increasesamedecrease($ia);
			break;
		case 'F': //ARRAY (Flexible) - Row Format
			$values=do_array_flexible($ia);
			break;
		case 'H': //ARRAY (Flexible) - Column Format
			$values=do_array_flexiblecolumns($ia);
			break;
//		case '^': //SLIDER CONTROL
//			$values=do_slider($ia);
//			break;
		case ':': //ARRAY (Multi Flexi) 1 to 10
			$values=do_array_multiflexi($ia);
			break;
		case ';': //ARRAY (Multi Flexi) Text
			$values=do_array_multitext($ia);  //It's like the "5th element" movie, come to life
			break;
		case '1': //Array (Flexible Labels) dual scale
			$values=do_array_flexible_dual($ia);
			break;
	} //End Switch

	if (isset($values)) //Break apart $values array returned from switch
	{
		//$answer is the html code to be printed
		//$inputnames is an array containing the names of each input field
		list($answer, $inputnames)=$values;
	}

	$answer .= "\n\t\t\t<input type='hidden' name='display$ia[1]' id='display$ia[0]' value='";
	$answer .= 'on'; //Ifthis is single format, then it must be showing. Needed for checking conditional mandatories
	$answer .= "' />\n"; //for conditional mandatory questions

	if ($ia[6] == 'Y')
	{
		$qtitle = '<span class="asterisk">'.$clang->gT('*').'</span>'.$qtitle;
	}
	//If this question is mandatory but wasn't answered in the last page
	//add a message HIGHLIGHTING the question
	$qtitle .= mandatory_message($ia);

	$qtitle .= validation_message($ia);

	$qanda=array($qtitle, $answer, $help, $display, $name, $ia[2], $gl[0], $ia[1]);
	//New Return
	return array($qanda, $inputnames);
}

function validation_message($ia)
{
	//This function checks to see if this question requires validation and
	//that validation has not been met.
	global $notvalidated, $dbprefix, $connect, $clang;
	$qtitle="";
	if (isset($notvalidated) && is_array($notvalidated)) //ADD WARNINGS TO QUESTIONS IF THEY ARE NOT VALID
	{
		global $validationpopup, $popup;
		if (in_array($ia[1], $notvalidated))
		{
			$help='';
			$helpselect="SELECT help\n"
			."FROM {$dbprefix}questions\n"
			."WHERE qid={$ia[0]} AND language='".$_SESSION['s_lang']."'";
			$helpresult=db_execute_assoc($helpselect) or safe_die($helpselect.'<br />'.$connect->ErrorMsg());     //Checked
			while ($helprow=$helpresult->FetchRow())
			{
				$help=' <span class="questionhelp">('.$helprow['help'].')</span>';
			}
			$qtitle .= '<br /><span class="errormandatory">'.$clang->gT('This question must be answered correctly').' '.$help.'</span><br />
';
		}
	}

	return $qtitle;
}

function mandatory_message($ia)
{
	//This function checks to see if this question is mandatory and
	//is being re-displayed because it wasn't answered. It returns
	global $notanswered, $clang, $dbprefix;
	$qtitle="";
	if (isset($notanswered) && is_array($notanswered)) //ADD WARNINGS TO QUESTIONS IF THEY WERE MANDATORY BUT NOT ANSWERED
	{
		global $mandatorypopup, $popup;
		if (in_array($ia[1], $notanswered))
		{
			$qtitle .= "<strong><br /><span class='errormandatory'>".$clang->gT('This question is mandatory').'.';
			switch($ia[4])
			{
				case 'A':
				case 'B':
				case 'C':
				case 'Q':
				case 'K':
				case 'F':
				case 'J':
				case 'H':
				case ':':
					$qtitle .= "<br />\n".$clang->gT('Please complete all parts').'.';
					break;
				case '1':
					$qtitle .= "<br />\n".$clang->gT('Please check the items').'.';
					break;
				case 'R':
					$qtitle .= "<br />\n".$clang->gT('Please rank all items').'.';
					break;
				case 'M':
				case 'P':
					$qtitle .= ' '.$clang->gT('Please check at least one item').'.';
					$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
					$qresult = db_execute_assoc($qquery);    //Checked
					$qrow = $qresult->FetchRow();
					if ($qrow['other']=='Y')
					{
						$qidattributes=getQuestionAttributes($ia[0]);
						if ($othertexts=arraySearchByKey('other_replace_text', $qidattributes, 'attribute', 1))
						{
							$othertext=$clang->gT($othertexts['value']);
						}
						else
						{
							$othertext=$clang->gT('Other');
						}
						$qtitle .= "<br />\n".sprintf($clang->gT("If you choose '%s' you must provide a description."), $othertext);
					}
					break;
			} // end switch
			$qtitle .= "</span></strong><br />\n";
		}
	}
	return $qtitle;
}

function mandatory_popup($ia, $notanswered=null)
{
	//This sets the mandatory popup message to show if required
	//Called from question.php, group.php or survey.php
	if ($notanswered === null) {unset($notanswered);}
	if (isset($notanswered) && is_array($notanswered)) //ADD WARNINGS TO QUESTIONS IF THEY WERE MANDATORY BUT NOT ANSWERED
	{
		global $mandatorypopup, $popup, $clang;
		//POPUP WARNING
		if (!isset($mandatorypopup) && ($ia[4] == 'T' || $ia[4] == 'S' || $ia[4] == 'U'))
		{
            $popup="<script type=\"text/javascript\">\n
                    <!--\n $(document).ready(function(){
                        alert(\"".$clang->gT("You cannot proceed until you enter some text for one or more questions.", "js")."\");});\n //-->\n
                    </script>\n";
			$mandatorypopup="Y";
		}else
		{
			$popup="<script type=\"text/javascript\">\n
                    <!--\n $(document).ready(function(){
                        alert(\"".$clang->gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed", "js")."\");});\n //-->\n
                    </script>\n";
			$mandatorypopup="Y";
		}
		return array($mandatorypopup, $popup);
	}
	else
	{
		return false;
	}
}

function validation_popup($ia, $notvalidated=null)
{
	//This sets the validation popup message to show if required
	//Called from question.php, group.php or survey.php
	if ($notvalidated === null) {unset($notvalidated);}
	$qtitle="";
	if (isset($notvalidated) && is_array($notvalidated))  //ADD WARNINGS TO QUESTIONS IF THEY ARE NOT VALID
	{
		global $validationpopup, $vpopup, $clang;
		//POPUP WARNING
		if (!isset($validationpopup))
		{
            $vpopup="<script type=\"text/javascript\">\n
                    <!--\n $(document).ready(function(){
                        alert(\"".$clang->gT("One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid", "js")."\");});\n //-->\n
                    </script>\n";
			$validationpopup="Y";
		}
		return array($validationpopup, $vpopup);
	}
	else
	{
		return false;
	}
}


// ==================================================================
// setting constants for 'checked' and 'selected' inputs
define('CHECKED' , ' checked="checked"' , true);
define('SELECTED' , ' selected="selected"' , true);

// ==================================================================
// QUESTION METHODS =================================================

function do_boilerplate($ia)
{
	$answer = '
		<input type="hidden" name="$ia[1]" id="answer'.$ia[1].'" value="" />
';
	$inputnames[]=$ia[1];

	return array($answer, $inputnames);
}


// ---------------------------------------------------------------
function do_5pointchoice($ia)
{
	global $shownoanswer, $clang;
	
	$answer = "\n<ul>\n";
	for ($fp=1; $fp<=5; $fp++)
	{
		$answer .= "\t<li>\n\t\t<input class=\"radio\" type=\"radio\" name=\"$ia[1]\" id=\"answer$ia[1]$fp\" value=\"$fp\"";
		if ($_SESSION[$ia[1]] == $fp)
		{
			$answer .= CHECKED;
		}
		$answer .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t<label for=\"answer$ia[1]$fp\" class=\"answertext\">$fp</label>\n\t</li>\n";
	}

	if ($ia[6] != "Y"  && $shownoanswer == 1) // Add "No Answer" option if question is not mandatory
	{
		$answer .= "\t<li>\n\t\t<input class=\"radio\" type=\"radio\" name=\"$ia[1]\" id=\"NoAnswer\" value=\"\"";
		if (!$_SESSION[$ia[1]])
		{
			$answer .= CHECKED;
		}
		$answer .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t<label for=\"NoAnswer\" class=\"answertext\">".$clang->gT('No answer')."</label>\n\t</li>\n";

	}
	$answer .= "</ul>\n<input type=\"hidden\" name=\"java$ia[1]\" id=\"java$ia[1]\" value=\"{$_SESSION[$ia[1]]}\" />\n";
	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_date($ia)
{
	global $clang;
	$qidattributes=getQuestionAttributes($ia[0]);
	if (arraySearchByKey('dropdown_dates', $qidattributes, 'attribute', 1)) {
		if (!empty($_SESSION[$ia[1]]))
		{
			list($currentyear, $currentmonth, $currentdate) = explode('-', $_SESSION[$ia[1]]);
		} else {
			$currentdate=''; 
			$currentmonth=''; 
			$currentyear='';
		}
		$answer = keycontroljs();
		$answer .= '
			<p class="question">
				<select id="day'.$ia[1].'" onchange="dateUpdater(\''.$ia[1].'\');" class="day">
					<option value="">'.$clang->gT('Day').'</option>
';
		for ($i=1; $i<=31; $i++) {
			if ($i == $currentdate)
			{
				$i_date_selected = SELECTED;
			}
			else
			{
				$i_date_selected = '';
			}
			$answer .= '					<option value="'.sprintf('%02d', $i).'"'.$i_date_selected.'>'.sprintf('%02d', $i).'</option>
';
		}

		$answer .= '				</select>
				<select id="month'.$ia[1].'" onchange="dateUpdater(\''.$ia[1].'\');" class="month">
					<option value="">'.$clang->gT('Month').'</option>
';
		$montharray=array(
			$clang->gT('Jan'), 
			$clang->gT('Feb'), 
			$clang->gT('Mar'), 
			$clang->gT('Apr'), 
			$clang->gT('May'),
			$clang->gT('Jun'),
			$clang->gT('Jul'),
			$clang->gT('Aug'),
			$clang->gT('Sep'),
			$clang->gT('Oct'),
			$clang->gT('Nov'),
			$clang->gT('Dec')); 
		for ($i=1; $i<=12; $i++) {
			if ($i == $currentmonth)
			{
				$i_date_selected = SELECTED;
			}
			else
			{
				$i_date_selected = '';
			}

			$answer .= '					<option value="'.sprintf('%02d', $i).'"'.$i_date_selected.'>'.$montharray[$i-1].'</option>
';
		}

		$answer .= '				</select>
				<select id="year'.$ia[1].'" onchange="dateUpdater(\''.$ia[1].'\');" class="year">
					<option value="">'.$clang->gT('Year').'</option>
';

		/*
		 * Maziminke (2008-11-25): New question attributes used only if question attribute
		 * "dropdown_dates" is used (see IF(...) above).
		 * 
		 * yearmin = Minimum year value for dropdown list, if not set default is 1900
		 * yearmax = Maximum year value for dropdown list, if not set default is 2020
		 */
		if($yearmin = arraySearchByKey('dropdown_dates_year_min', $qidattributes, 'attribute', 1))
		{
			$yearmin = $yearmin['value'];
		}
		else
		{
			$yearmin = 1900;
		}
		
		if($yearmax = arraySearchByKey('dropdown_dates_year_max', $qidattributes, 'attribute', 1))
		{
			$yearmax = $yearmax['value'];
		}
		else
		{
			$yearmax = 2020;
		}
		
		for ($i=$yearmax; $i>=$yearmin; $i--) {
			if ($i == $currentyear)
			{
				$i_date_selected = SELECTED;
			}
			else
			{
				$i_date_selected = '';
			}
			$answer .= '					<option value="'.$i.'"'.$i_date_selected.'>'.$i.'</option>
';
		}
		$answer .= '				</select>
				<input class="text" type="text" size="10" name="'.$ia[1].'" style="display: none" id="answer'.$ia[1].'" value="'.$_SESSION[$ia[1]].'" maxlength="10" onchange="checkconditions(this.value, this.name, this.type)" />
			</p>
';

		$answer .= '<input type="hidden" name="qattribute_answer[]" value="'.$ia[1].'" />
			<input type="hidden" name="qattribute_answer'.$ia[1].'" />
			<script type="text/javascript">
'
		. "function dateUpdater(val) {\n"
		. "  label='answer'+val;\n"
		. "  yearlabel='year'+val;\n"
		. "  monthlabel='month'+val;\n"
		. "  daylabel='day'+val;\n"
		. "  bob = eval('document.limesurvey.qattribute_answer".$ia[1]."');\n"
		. "  if(document.getElementById(yearlabel).value != '' && document.getElementById(monthlabel).value != '' && document.getElementById(daylabel).value != '')\n"
		. "  {\n"
		. "    document.getElementById(label).value=document.getElementById(yearlabel).value+'-'+document.getElementById(monthlabel).value+'-'+document.getElementById(daylabel).value;\n"
		. "    ValidDate(document.getElementById(label));\n"
		. "    checkconditions(document.getElementById(label).value, document.getElementById(label).name, document.getElementById(label).type);\n"
		. "    bob.value='';\n"
		. "  } else if (document.getElementById(yearlabel).value == '' && document.getElementById(monthlabel).value == '' && document.getElementById(daylabel).value == '') {\n"
		. "    document.getElementById(label).value='';\n"
		. "    bob.value='';\n"
		. "  } else {\n"
		. "    document.getElementById(label).value='';\n"
		. "    bob.value='".$clang->gT("Please complete all parts of the date")."';\n"
		. "  }\n"
		. "}\n"
		. "dateUpdater(\"{$ia[1]}\");\n"
		. "</script>\n";

	} else {
	$answer = keycontroljs()
		. "
			<p class=\"question\">
				<input class=\"text\" type=\"text\" size=\"10\" name=\"{$ia[1]}\" id=\"answer{$ia[1]}\" value=\"{$_SESSION[$ia[1]]}\" maxlength=\"10\" onkeypress=\"return goodchars(event,'0123456789-')\" onchange=\"checkconditions(this.value, this.name, this.type)\" onblur=\"ValidDate(this)\" />
				<button type=\"reset\" id=\"f_trigger_{$ia[1]}\">...</button>
			</p>
			<p class=\"tip\">
				".$clang->gT('Format: YYYY-MM-DD')."<br />
				".$clang->gT('(eg: 2003-12-25 for Christmas day)')."
			</p>
";
	// Here we do setup the date javascript
	$answer .= "<script type=\"text/javascript\">\n"
	. "Calendar.setup({\n"
	. "inputField     :    \"answer{$ia[1]}\",\n"	// id of the input field
	. "ifFormat       :    \"%Y-%m-%d\",\n"	// format of the input field
	. "showsTime      :    false,\n"	// will display a time selector
	. "button         :    \"f_trigger_{$ia[1]}\",\n"	// trigger for the calendar (button ID)
	. "singleClick    :    true,\n"	// double-click mode
	. "step           :    1\n"	// show all years in drop-down boxes (instead of every other year as default)
	. "});\n"
	. "</script>\n";
	}
	$inputnames[]=$ia[1];

	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_language($ia)
{
	global $dbprefix, $surveyid, $clang;
	$answerlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
	$answerlangs [] = GetBaseLanguageFromSurveyID($surveyid);
	$answer = "\n\t\t\t<p class=\"question\">\n\t\t\t\t<select name=\"$ia[1]\" id=\"answer$ia[1]\" onchange=\"document.getElementById('lang').value=this.value; checkconditions(this.value, this.name, this.type);\">\n";
	if (!$_SESSION[$ia[1]]) {$answer .= "\t\t\t\t\t<option value=\"\" selected=\"selected\">".$clang->gT('Please choose')."..</option>\n";}
	foreach ($answerlangs as $ansrow)
	{
		$answer .= "\t\t\t\t\t<option value=\"{$ansrow}\"";
		if ($_SESSION[$ia[1]] == $ansrow)
		{
			$answer .= SELECTED;
		}
		elseif ($ansrow['default_value'] == 'Y')
		{
			$answer .= SELECTED; 
		 	$defexists = "Y";
		}
		$answer .= '>'.getLanguageNameFromCode($ansrow, true)."</option>\n";
	}
	$answer .= "\t\t\t\t</select>\n";
	$answer .= "\t\t\t\t<input type=\"hidden\" name=\"java$ia[1]\" id=\"java$ia[1]\" value=\"{$_SESSION[$ia[1]]}\" />\n";
	
	$inputnames[]=$ia[1];
	$answer .= "\n\t\t\t\t<input type=\"hidden\" name=\"lang\" id=\"lang\" value=\"\" />\n\t\t\t</p>\n";

	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_list_dropdown($ia)
{
	global $dbprefix,  $dropdownthreshold, $lwcdropdowns, $connect;
	global $shownoanswer, $clang;
	$qidattributes=getQuestionAttributes($ia[0]);

	if ($othertexts=arraySearchByKey('other_replace_text', $qidattributes, 'attribute', 1))
	{
		$othertext=$clang->gT($othertexts['value']);
	}
	else
	{
		$othertext=$clang->gT('Other');
	}

	if ($optCategorySeparator = arraySearchByKey('category_separator', $qidattributes, 'attribute', 1))
	{
		$optCategorySeparator = $optCategorySeparator['value'];
	}
	else
	{
		unset($optCategorySeparator);
	}


	$answer='';

	if (isset($defexists)) {unset ($defexists);}
	$query = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."' ";
	$result = db_execute_assoc($query);      //Checked
	while($row = $result->FetchRow()) {$other = $row['other'];}
	
	//question attribute random order set?
	if (arraySearchByKey('random_order', $qidattributes, 'attribute', 1))
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
	}
	
	//question attribute alphasort set?
	elseif(arraySearchByKey('alphasort', $qidattributes, 'attribute', 1))
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY answer";
	}		
		
	//no question attributes -> order by sortorder
	else
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	}
	
	$ansresult = db_execute_assoc($ansquery) or safe_die('Couldn\'t get answers<br />'.$ansquery.'<br />'.$connect->ErrorMsg());    //Checked

	if (!isset($optCategorySeparator))
	{
		while ($ansrow = $ansresult->FetchRow())
		{
			if ($_SESSION[$ia[1]] == $ansrow['code'])
			{
				$opt_select = SELECTED;
			}
			elseif ($ansrow['default_value'] == 'Y')
			{
				$opt_select = SELECTED; 
				$defexists = 'Y';
			}
			else
			{
				$opt_select = '';
			}
			$answer .= '					<option value="'.$ansrow['code'].'"'.$opt_select.'>'.$ansrow['answer'].'</option>
				';
		}
	}
	else
	{
		$defaultopts = Array();
		$optgroups = Array();
		while ($ansrow = $ansresult->FetchRow())
		{
			// Let's sort answers in an array indexed by subcategories
			list ($categorytext, $answertext) = explode($optCategorySeparator,$ansrow['answer']);
			// The blank category is left at the end outside optgroups
			if ($categorytext == '')
			{ 
				$defaultopts[] = array ( 'code' => $ansrow['code'], 'answer' => $answertext, 'default_value' => $ansrow['default_value']);
			}
			else
			{
				$optgroups[$categorytext][] = array ( 'code' => $ansrow['code'], 'answer' => $answertext, 'default_value' => $ansrow['default_value']);
			}


		}

		foreach ($optgroups as $categoryname => $optionlistarray)
		{
			$answer .= '                                   <optgroup class="dropdowncategory" label="'.$categoryname.'"> 
                                ';

			foreach ($optionlistarray as $optionarray)
			{
				if ($_SESSION[$ia[1]] == $optionarray['code'])
				{
					$opt_select = SELECTED;
				}
				elseif ($optionarray['default_value'] == 'Y')
				{
					$opt_select = SELECTED; 
					$defexists = 'Y';
				}
				else
				{
					$opt_select = '';
				}

				$answer .= '     					<option value="'.$optionarray['code'].'"'.$opt_select.'>'.$optionarray['answer'].'</option>
					';
			}

			$answer .= '                                   </optgroup>';
		}
		foreach ($defaultopts as $optionarray)
		{
			if ($_SESSION[$ia[1]] == $optionarray['code'])
			{
				$opt_select = SELECTED;
			}
			elseif ($optionarray['default_value'] == 'Y')
			{
				$opt_select = SELECTED; 
				$defexists = 'Y';
			}
			else
			{
				$opt_select = '';
			}

			$answer .= '     					<option value="'.$optionarray['code'].'"'.$opt_select.'>'.$optionarray['answer'].'</option>
				';
		}
	}

	if (!$_SESSION[$ia[1]] && (!isset($defexists) || !$defexists))
	{
		$answer = '					<option value=""'.SELECTED.'>'.$clang->gT('Please choose').'...</option>
'.$answer;
	}

	if (isset($other) && $other=='Y')
	{
		if ($_SESSION[$ia[1]] == '-oth-')
		{
			$opt_select = SELECTED;
		}
		else
		{
			$opt_select = '';
		}
		$answer .= '					<option value="-oth-"'.$opt_select.'>'.$othertext."</option>\n";
	}
	
	if ((isset($_SESSION[$ia[1]]) || $_SESSION[$ia[1]] != '') && (!isset($defexists) || !$defexists) && $ia[6] != 'Y' && $shownoanswer == 1)
	{
		$answer .= '					<option value=" ">'.$clang->gT('No answer')."</option>\n";
	}
	$answer .= '				</select>
				<input type="hidden" name="java'.$ia[1].'" id="java'.$ia[1]."\" value=\"{$_SESSION[$ia[1]]}\" />
";

	if (isset($other) && $other=='Y')
	{
		$sselect_show_hide = ' showhideother(this.name, this.value);';
	}
	else
	{
		$sselect_show_hide = '';
	}
	$sselect = '
			<p class="question">
				<select name="'.$ia[1].'" id="answer'.$ia[1].'" onchange="checkconditions(this.value, this.name, this.type);'.$sselect_show_hide.'">
';
	$answer = $sselect.$answer;

	if (isset($other) && $other=='Y')
	{
		$answer = "\n<script type=\"text/javascript\">\n"
		."<!--\n"
		."function showhideother(name, value)\n"
		."\t{\n"
		."\tvar hiddenothername='othertext'+name;\n"
		."\tif (value == \"-oth-\")\n"
		."\t\t{\n"
		."\t\tdocument.getElementById(hiddenothername).style.display='';\n"
		."\t\tdocument.getElementById(hiddenothername).focus();\n"
		."\t\t}\n"
		."\telse\n"
		."\t\t{\n"
		."\t\tdocument.getElementById(hiddenothername).style.display='none';\n"
		."\t\t}\n"
		."\t}\n"
		."//--></script>\n".$answer;
		$answer .= '				<input type="text" id="othertext'.$ia[1].'" name="'.$ia[1].'other" style="display:';

		$inputnames[]=$ia[1].'other';

		if ($_SESSION[$ia[1]] != '-oth-')
		{
			$answer .= 'none';
		}

		// --> START BUG FIX - text field for other was not repopulating when returning to page via << PREV
		$answer .= '"';
		$thisfieldname=$ia[1].'other';
		if (isset($_SESSION[$thisfieldname])) { $answer .= '" value="'.htmlspecialchars($_SESSION[$thisfieldname],ENT_QUOTES).'" ';}
		// --> END BUG FIX

		// --> START NEW FEATURE - SAVE
		$answer .= "' onchange='checkconditions(this.value, this.name, this.type);'";
		$thisfieldname="$ia[1]other";
		if (isset($_SESSION[$thisfieldname])) { $answer .= " value='".htmlspecialchars($_SESSION[$thisfieldname],ENT_QUOTES)."' ";}
		$answer .= ' />';
		$answer .= "</p>";
		// --> END NEW FEATURE - SAVE
		$inputnames[]=$ia[1]."other";
	}
	else
	{
		$answer .= "</p>";
	}

	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_list_flexible_dropdown($ia)
{
	global $dbprefix, $dropdownthreshold, $lwcdropdowns, $connect;
	global $shownoanswer, $clang;
	$qidattributes=getQuestionAttributes($ia[0]);

	if ($othertexts=arraySearchByKey('other_replace_text', $qidattributes, 'attribute', 1))
	{
		$othertext=$clang->gT($othertexts['value']);
	}
	else
	{
		$othertext=$clang->gT('Other');
	}

	$answer='';

	$qquery = "SELECT other, lid FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
	$qresult = db_execute_assoc($qquery);  //Checked
	while($row = $qresult->FetchRow()) {$other = $row['other']; $lid=$row['lid'];}
	$filter='';
	if ($code_filter=arraySearchByKey('code_filter', $qidattributes, 'attribute', 1))
	{
		$filter=$code_filter['value'];
		if(in_array($filter, $_SESSION['insertarray']))
		{
			$filter=trim($_SESSION[$filter]);
		}
	}
	$filter .= '%';
	
	//question attribute random order set?
	if (arraySearchByKey('random_order', $qidattributes, 'attribute', 1))
	{
		$ansquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND code LIKE '$filter' AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
	}
	
	//question attribute alphasort set?
	elseif(arraySearchByKey('alphasort', $qidattributes, 'attribute', 1))
	{
		$ansquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND code LIKE '$filter' AND language='".$_SESSION['s_lang']."' ORDER BY title";
	}
	
	//no question attributes -> order by sortorder
	else
	{
		$ansquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND code LIKE '$filter' AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, code";
	}
	
	$ansresult = db_execute_assoc($ansquery) or safe_die('Couldn\'t get answers<br />$ansquery<br />'.$connect->ErrorMsg());//Checked

	if (labelset_exists($lid,$_SESSION['s_lang']))
	{
		while ($ansrow = $ansresult->FetchRow())
		{
			if ($_SESSION[$ia[1]] == $ansrow['code'])
			{
				$opt_select = SELECTED;
			}
			else
			{
				$opt_select = '';
			}
			$answer .= '					<option value="'.$ansrow['code'].'"'.$opt_select.'>'.$ansrow['title']."</option>\n";
		}

		if (!$_SESSION[$ia[1]] && (!isset($defexists) || !$defexists))
		{
			$answer = '					<option value=""'.$opt_select.'>'.$clang->gT('Please choose')."...</option>\n".$answer;
		}

		if (isset($other) && $other=='Y')
		{
			if ($_SESSION[$ia[1]] == '-oth-')
			{
				$opt_select = SELECTED;
			}
			else
			{
				$opt_select = '';
			}
			$answer .= '					<option value="-oth-"'.$opt_select.'>'.$othertext."</option>\n";
		}

		if ((isset($_SESSION[$ia[1]]) || $_SESSION[$ia[1]] != '') && (!isset($defexists) || !$defexists) && $ia[6] != 'Y' && $shownoanswer == 1)
		{
			$answer .= '					<option value=" ">'.$clang->gT('No answer')."</option>\n";
		}
		$answer .= "\t\t\t\t</select>\n";
	}
	else 
	{
		$answer .= '					<option>'.$clang->gT('Error: The labelset used for this question is not available in this language.').'</option>
';
	}

	$answer .= '				<input type="hidden" name="java'.$ia[1].'" id="java'.$ia[1]."\" value=\"{$_SESSION[$ia[1]]}\" />\n";

	if (isset($other) && $other=="Y")
	{
		$sselect_show_hide = ' showhideother(this.name, this.value)';
	}
	else
	{
		$sselect_show_hide = '';
	}
	$sselect = '
			<p class="question">
				<select name="'.$ia[1].'" id="answer'.$ia[1].'" onchange="checkconditions(this.value, this.name, this.type);'.$sselect_show_hide."\">\n";
	$answer = $sselect.$answer;

	if (isset($other) && $other=='Y')
	{
		$answer = "\n<script type=\"text/javascript\">\n"
		."<!--\n"
		."function showhideother(name, value)\n"
		."\t{\n"
		."\tvar hiddenothername='othertext'+name;\n"
		."\tif (value == \"-oth-\")\n"
		."\t\t{\n"
		."\t\tdocument.getElementById(hiddenothername).style.display='';\n"
		."\t\tdocument.getElementById(hiddenothername).focus();\n"
		."\t\t}\n"
		."\telse\n"
		."\t\t{\n"
		."\t\tdocument.getElementById(hiddenothername).style.display='none';\n"
		."\t\t}\n"
		."\t}\n"
		."//--></script>\n".$answer;

		$answer .= '				<input type="text" id="othertext'.$ia[1].'" name="'.$ia[1].'other" style="display:';
		if ($_SESSION[$ia[1]] != '-oth-')
		{
			$answer .= ' none';
		}
		$answer .= "\" />\n\t\t\t</p>\n";

		$inputnames[]=$ia[1]."other";
	}

	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_list_radio($ia)
{
	global $dbprefix, $dropdownthreshold, $lwcdropdowns, $connect, $clang;
	global $shownoanswer;

	$qidattributes=getQuestionAttributes($ia[0]);

	if (isset($defexists))
	{
		unset ($defexists);
	}
	$query = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."' ";
	$result = db_execute_assoc($query);  //Checked
	while($row = $result->FetchRow())
	{
		$other = $row['other'];
	}
	
	//question attribute random order set?
	if (arraySearchByKey('random_order', $qidattributes, 'attribute', 1))
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
	}
	
	//question attribute alphasort set?
	elseif (arraySearchByKey('alphasort', $qidattributes, 'attribute', 1))
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY answer";
	}	
	
	//no question attributes -> order by sortorder
	else 
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	}
	
	$ansresult = db_execute_assoc($ansquery) or safe_die('Couldn\'t get answers<br />$ansquery<br />'.$connect->ErrorMsg());  //Checked
	$anscount = $ansresult->RecordCount();

	if ($displaycols=arraySearchByKey('display_columns', $qidattributes, 'attribute', 1))
	{
		$dcols = $displaycols['value'];
	}
	else
	{
		$dcols= 1;
	}

	if ($othertexts=arraySearchByKey('other_replace_text', $qidattributes, 'attribute', 1))
	{
		$othertext=$clang->gT($othertexts['value']);
	}
	else
	{
		$othertext=$clang->gT('Other');
	}

	if (isset($other) && $other=='Y') {$anscount++;} //Count up for the Other answer
	if ($ia[6] != 'Y' && $shownoanswer == 1) {$anscount++;} //Count up if "No answer" is showing

	$wrapper = setup_columns($dcols , $anscount);
	$answer = $wrapper['whole-start'];

	$rowcounter = 0;
	$colcounter = 1;
	while ($ansrow = $ansresult->FetchRow())
	{
		if ($_SESSION[$ia[1]] == $ansrow['code'])
		{
			$check_ans = CHECKED;
		}
		elseif ($ansrow['default_value'] == 'Y') 
		{
			$check_ans = CHECKED;
			$defexists = 'Y';
		}
		else
		{
			$check_ans = '';
		}
		$answer .= $wrapper['item-start'].'		<input class="radio" type="radio" value="'.$ansrow['code'].'" name="'.$ia[1].'" id="answer'.$ia[1].$ansrow['code'].'"'.$check_ans.' onclick="checkconditions(this.value, this.name, this.type)" />
		<label for="answer'.$ia[1].$ansrow['code'].'" class="answertext">'.$ansrow['answer'].'</label>
'.$wrapper['item-end'];

		++$rowcounter;
		if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
		{
			if($colcounter == $wrapper['cols'] - 1)
			{
				$answer .= $wrapper['col-devide-last'];
			}
			else
			{
				$answer .= $wrapper['col-devide'];
			}
			$rowcounter = 0;
			++$colcounter;
		}
	}

	if (isset($other) && $other=='Y')
	{
		if ($_SESSION[$ia[1]] == '-oth-')
		{
			$check_ans = CHECKED;
		}
		else
		{
			$check_ans = '';
		}

		$thisfieldname=$ia[1].'other';
		if (isset($_SESSION[$thisfieldname]))
		{
			$answer_other = ' value="'.htmlspecialchars($_SESSION[$thisfieldname],ENT_QUOTES).'"';
		}
		else
		{
			$answer_other = ' value=""';
		}

		$answer .= $wrapper['item-start'].'		<input class="radio" type="radio" value="-oth-" name="'.$ia[1].'" id="SOTH'.$ia[1].'"'.$check_ans.' onclick="checkconditions(this.value, this.name, this.type)" />
		<label for="SOTH'.$ia[1].'" class="answertext">'.$othertext.'</label>
		<label for="answer'.$ia[1].'othertext">
			<input type="text" class="text" id="answer'.$ia[1].'othertext" name="'.$ia[1].'other" title="'.$clang->gT('Other').'"'.$answer_other.' onclick="javascript:document.getElementById(\'SOTH'.$ia[1].'\').checked=true; checkconditions(document.getElementById(\'SOTH'.$ia[1].'\').value, document.getElementById(\'SOTH'.$ia[1].'\').name, document.getElementById(\'SOTH'.$ia[1].'\').type);" />
		</label>
'.$wrapper['item-end'];

		$inputnames[]=$thisfieldname;

		++$rowcounter;
		if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
		{
			if($colcounter == $wrapper['cols'] - 1)
			{
				$answer .= $wrapper['col-devide-last'];
			}
			else
			{
				$answer .= $wrapper['col-devide'];
			}
			$rowcounter = 0;
			++$colcounter;
		}
	}

	if ($ia[6] != 'Y' && $shownoanswer == 1)
	{
		if (((!isset($_SESSION[$ia[1]]) || $_SESSION[$ia[1]] == '') && (!isset($defexists) || !$defexists)) || ($_SESSION[$ia[1]] == ' ' && (!isset($defexists) || !$defexists)))
		{
			$check_ans = CHECKED; //Check the "no answer" radio button if there is no default, and user hasn't answered this.
		}
		else
		{
			$check_ans = '';
		}

		$answer .= $wrapper['item-start'].'		<input class="radio" type="radio" name="'.$ia[1].'" id="answer'.$ia[1].'NANS" value=""'.$check_ans.' onclick="checkconditions(this.value, this.name, this.type)" />
		<label for="answer'.$ia[1].'NANS" class="answertext">'.$clang->gT('No answer').'</label>
'.$wrapper['item-end'];
		// --> END NEW FEATURE - SAVE


		++$rowcounter;
		if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
		{
			if($colcounter == $wrapper['cols'] - 1)
			{
				$answer .= $wrapper['col-devide-last'];
			}
			else
			{
				$answer .= $wrapper['col-devide'];
			}
			$rowcounter = 0;
			++$colcounter;
		}

	}
	$answer .= $wrapper['whole-end'].'
<input type="hidden" name="java'.$ia[1].'" id="java'.$ia[1]."\" value=\"{$_SESSION[$ia[1]]}\" />\n";

	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_list_flexible_radio($ia)
{
	global $dbprefix, $dropdownthreshold, $lwcdropdowns, $connect;
	global $shownoanswer, $clang;

	$qidattributes=getQuestionAttributes($ia[0]);
	if ($othertexts=arraySearchByKey('other_replace_text', $qidattributes, 'attribute', 1))
	{
		$othertext=$clang->gT($othertexts['value']);
	}
	else
	{
		$othertext=$clang->gT('Other');
	}

	if ($displaycols=arraySearchByKey('display_columns', $qidattributes, 'attribute', 1))
	{
		$dcols=$displaycols['value'];
	}
	else
	{
		$dcols = 1;
	}

	if (isset($defexists)) {unset ($defexists);}

	$query = "SELECT other, lid FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
	$result = db_execute_assoc($query);       //Checked
	while($row = $result->FetchRow()) {$other = $row['other']; $lid = $row['lid'];}
	$filter='';
	if ($code_filter=arraySearchByKey("code_filter", $qidattributes, "attribute", 1))
	{
		$filter=$code_filter['value'];
		if(in_array($filter, $_SESSION['insertarray']))
		{
			$filter=trim($_SESSION[$filter]);
		}
	}
	$filter .= '%';
	
	//question attribute random order set?
	if (arraySearchByKey('random_order', $qidattributes, 'attribute', 1))
	{
		$ansquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND code LIKE '$filter' AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
	}
	
	//question attribute alphasort set?
	elseif (arraySearchByKey('alphasort', $qidattributes, 'attribute', 1))
	{
		$ansquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND code LIKE '$filter' AND language='".$_SESSION['s_lang']."' ORDER BY title";
	}
	
	//no question attributes -> order by sortorder
	else
	{
		$ansquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid AND code LIKE '$filter' AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, code";
	}
	
	$ansresult = db_execute_assoc($ansquery) or safe_die("Couldn't get answers<br />$ansquery<br />".$connect->ErrorMsg());    //Checked
	$anscount = $ansresult->RecordCount();

	if ((isset($other) && $other=='Y') || ($ia[6] != 'Y' && $shownoanswer == 1)) {$anscount++;} //Count

	$wrapper = setup_columns($dcols , $anscount);
	$answer = $wrapper['whole-start'];

	$rowcounter = 0;
	$colcounter = 1;

	if (labelset_exists($lid,$_SESSION['s_lang']))
	{
		while ($ansrow = $ansresult->FetchRow())
		{
			if ($_SESSION[$ia[1]] == $ansrow['code'])
			{
				$check_ans = CHECKED;
			}
			else
			{
				$check_ans ='';
			};
			$answer .= $wrapper['item-start'].'		<input class="radio" type="radio" value="'.$ansrow['code'].'" name="'.$ia[1].'" id="answer'.$ia[1].$ansrow['code'].'"'.$check_ans.' onclick="checkconditions(this.value, this.name, this.type)" />
		<label for="answer'.$ia[1].$ansrow['code'].'" class="answertext">'.$ansrow['title'].'</label>
'.$wrapper['item-end'];

			++$rowcounter;
			if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
			{
				if($colcounter == $wrapper['cols'] - 1)
				{
					$answer .= $wrapper['col-devide-last'];
				}
				else
				{
					$answer .= $wrapper['col-devide'];
				}
				$rowcounter = 0;
				++$colcounter;
			}
		}
	}
	else 
	{
		$answer .= $clang->gT('Error: The labelset used for this question is not available in this language.').'<br />';
	}

	if (isset($other) && $other=='Y')
	{
		if ($_SESSION[$ia[1]] == '-oth-')
		{
			$check_ans = CHECKED;
		}
		else
		{
			$check_ans = '';
		}

		$thisfieldname=$ia[1].'other';
		if (isset($_SESSION[$thisfieldname]))
		{
			$answer_other = ' value="'.htmlspecialchars($_SESSION[$thisfieldname],ENT_QUOTES).'"';
		}
		else
		{
			$answer_other = ' value=""';
		}

		$answer .= $wrapper['item-start'].'		<input class="radio" type="radio" value="-oth-" name="'.$ia[1].'" id="SOTH'.$ia[1].'"'.$check_ans.' onclick="checkconditions(this.value, this.name, this.type)" />
		<label for="SOTH'.$ia[1].'" class="answertext">'.$othertext.'</label>
		<label for="answer'.$ia[1].'othertext">
			<input type="text" class="text" id="answer'.$ia[1].'othertext" name="'.$ia[1].'other" title="'.$clang->gT('Other').'"'.$answer_other.' onclick="javascript:document.getElementById(\'SOTH'.$ia[1].'\').checked=true; checkconditions(document.getElementById(\'SOTH'.$ia[1].'\').value, document.getElementById(\'SOTH'.$ia[1].'\').name, document.getElementById(\'SOTH'.$ia[1].'\').type);" />
		</label>
'.$wrapper['item-end'];

		$inputnames[]=$thisfieldname;

		++$rowcounter;
		if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
		{
			if($colcounter == $wrapper['cols'] - 1)
			{
				$answer .= $wrapper['col-devide-last'];
			}
			else
			{
				$answer .= $wrapper['col-devide'];
			}
			$rowcounter = 0;
			++$colcounter;
		}
	}

	if ($ia[6] != 'Y' && $shownoanswer == 1)
	{
		if ((!isset($defexists) || $defexists != 'Y') && (!isset($_SESSION[$ia[1]]) || $_SESSION[$ia[1]] == '' || $_SESSION[$ia[1]] == ' '))
		{
			$check_ans = CHECKED; //Check the "no answer" radio button if there is no default, and user hasn't answered this.
		}
		else
		{
			$check_ans = '';
		}

		$answer .= $wrapper['item-start'].'		<input class="radio" type="radio" name="'.$ia[1].'" id="answer'.$ia[1].'NANS" value=""'.$check_ans.' onclick="checkconditions(this.value, this.name, this.type)" />
		<label for="answer'.$ia[1].'NANS" class="answertext">'.$clang->gT('No answer').'</label>
'.$wrapper['item-end'];


		++$rowcounter;
		if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
		{
			if($colcounter == $wrapper['cols'] - 1)
			{
				$answer .= $wrapper['col-devide-last'];
			}
			else
			{
				$answer .= $wrapper['col-devide'];
			}
			$rowcounter = 0;
			++$colcounter;
		}

	}

	$answer .= $wrapper['whole-end'].'
<input type="hidden" name="java'.$ia[1].'" id="java'.$ia[1]."\" value=\"{$_SESSION[$ia[1]]}\" />\n";

	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_listwithcomment($ia)
{
	global $maxoptionsize, $dbprefix, $dropdownthreshold, $lwcdropdowns;
	global $shownoanswer, $clang;

	$answer = '';

	$qidattributes=getQuestionAttributes($ia[0]);
	if (!isset($maxoptionsize)) {$maxoptionsize=35;}

	//question attribute random order set?
	if (arraySearchByKey('random_order', $qidattributes, 'attribute', 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
	}
	
	//question attribute alphasort set?
	elseif (arraySearchByKey('alphasort', $qidattributes, 'attribute', 1)) 
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY answer";
	}
	
	//no question attributes -> order by sortorder
	else
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	}
	
	$ansresult = db_execute_assoc($ansquery);      //Checked
	$anscount = $ansresult->RecordCount();

	$hint_list = $clang->gT('Please choose one of the following');
	$hint_comment = $clang->gT('Please enter your comment here');

	if ($lwcdropdowns == 'R' && $anscount <= $dropdownthreshold)
	{
		$answer .= '
<div class="list">
	<p class="tip">'.$hint_list.':</p>

	<ul>
';

		while ($ansrow=$ansresult->FetchRow())
		{
			if ($_SESSION[$ia[1]] == $ansrow['code'])
			{
				$check_ans = CHECKED;
			}
			elseif ($ansrow['default_value'] == 'Y')
			{
				$check_ans = CHECKED; 
				$defexists = 'Y';
			}
			else
			{
				$check_ans = '';
			}
			$answer .= '		<li>
			<input type="radio" name="'.$ia[1].'" id="answer'.$ia[1].$ansrow['code'].'" value="'.$ansrow['code'].'" class="radio" '.$check_ans.' onclick="checkconditions(this.value, this.name, this.type)" />
			<label for="answer'.$ia[1].$ansrow['code'].'" class="answertext">'.$ansrow['answer'].'</label>
		</li>
';
		}

		if ($ia[6] != 'Y' && $shownoanswer == 1)
		{
			if (((!isset($_SESSION[$ia[1]]) || $_SESSION[$ia[1]] == '') && (!isset($defexists) || !$defexists)) ||($_SESSION[$ia[1]] == ' ' && (!isset($defexists) || !$defexists)))
			{
				$check_ans = CHECKED;
			}
			elseif ((isset($_SESSION[$ia[1]]) || $_SESSION[$ia[1]] != '') && (!isset($defexists) || !$defexists))
			{
				$check_ans = '';
			}
			$answer .= '		<li>
			<input class="radio" type="radio" name="'.$ia[1].'" id="answer'.$ia[1].'" value=" " onclick="checkconditions(this.value, this.name, this.type)"'.$check_ans.' />
			<label for="answer'.$ia[1].'" class="answertext">'.$clang->gT('No answer').'</label>
		</li>
';
		}

		$fname2 = $ia[1].'comment';
		if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
		// --> START NEW FEATURE - SAVE
		//    --> START ORIGINAL
		//        $answer .= "\t\t\t\t\t<td valign='top'>\n"
		//                 . "\t\t\t\t\t\t<textarea class='textarea' name='$ia[1]comment' id='answer$ia[1]comment' rows='$tarows' cols='30'>";
		//    --> END ORIGINAL
		$answer .= '	</ul>
</div>

<p class="comment">
	<label for="answer'.$ia[1].'comment">'.$hint_comment.':</label>

	<textarea class="textarea" name="'.$ia[1].'comment" id="answer'.$ia[1].'comment" rows="'.floor($tarows).'" cols="30" >';
		// --> END NEW FEATURE - SAVE
		if (isset($_SESSION[$fname2]) && $_SESSION[$fname2])
		{
			$answer .= str_replace("\\", "", $_SESSION[$fname2]);
		}
		$answer .= '</textarea>
</p>

<input class="radio" type="hidden" name="java'.$ia[1].'" id="java'.$ia[1]."\" value=\"{$_SESSION[$ia[1]]}\" />
";
		$inputnames[]=$ia[1];
		$inputnames[]=$ia[1].'comment';
	}
	else //Dropdown list
	{
		// --> START NEW FEATURE - SAVE
		$answer .= '<p class="select">
	<select class="select" name="'.$ia[1].'" id="answer'.$ia[1].'" onclick="checkconditions(this.value, this.name, this.type)" >
';
		// --> END NEW FEATURE - SAVE
		while ($ansrow=$ansresult->FetchRow())
		{
			if ($_SESSION[$ia[1]] == $ansrow['code'])
			{
				$check_ans = SELECTED;
			}
			elseif ($ansrow['default_value'] == 'Y')
			{
				$check_ans = SELECTED; 
				$defexists = "Y";
			}
			$answer .= '		<option value="'.$ansrow['code'].'"'.$check_ans.'>'.$ansrow['answer']."</option>\n";

			if (strlen($ansrow['answer']) > $maxoptionsize)
			{
				$maxoptionsize = strlen($ansrow['answer']);
			}
		}
		if ($ia[6] != 'Y' && $shownoanswer == 1)
		{
			if (((!isset($_SESSION[$ia[1]]) || $_SESSION[$ia[1]] == '') && (!isset($defexists) || !$defexists)) ||($_SESSION[$ia[1]] == ' ' && (!isset($defexists) || !$defexists)))
			{
				$check_ans = SELECTED;
			}
			elseif ((isset($_SESSION[$ia[1]]) || $_SESSION[$ia[1]] != '') && (!isset($defexists) || !$defexists))
			{
				$check_ans = '';
			}
			$answer .= '		<option value=" "'.$check_ans.'>'.$clang->gT('No answer')."</option>\n";
		}
		$answer .= '	</select>
</p>
';
		$fname2 = $ia[1].'comment';
		if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
		if ($tarows > 15) {$tarows=15;}
		$maxoptionsize=$maxoptionsize*0.72;
		if ($maxoptionsize < 33) {$maxoptionsize=33;}
		if ($maxoptionsize > 70) {$maxoptionsize=70;}
		$answer .= '<p class="comment">
	'.$hint_comment.'
	<textarea class="textarea" name="'.$ia[1].'comment" id="answer'.$ia[1].'comment" rows="'.$tarows.'" cols="'.$maxoptionsize.'" >';
		// --> END NEW FEATURE - SAVE
		if (isset($_SESSION[$fname2]) && $_SESSION[$fname2])
		{
			$answer .= str_replace("\\", "", $_SESSION[$fname2]);
		}
		$answer .= '</textarea>
	<input class="radio" type="hidden" name="java'.$ia[1].'" id="java'.$ia[1]." value=\"{$_SESSION[$ia[1]]}\" />\n</p>\n";
		$inputnames[]=$ia[1];
		$inputnames[]=$ia[1].'comment';
	}
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_ranking($ia)
{
	global $dbprefix, $imagefiles, $clang, $thissurvey;
	$qidattributes=getQuestionAttributes($ia[0]);
	$answer="";
	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
	} else {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	}
	if ($ma=arraySearchByKey("max_answers", $qidattributes, "attribute", 1)) {
		$max_answers = $ma['value'];
	} else {
	    $max_answers = false;
	}
	$ansresult = db_execute_assoc($ansquery);   //Checked
	$anscount = $ansresult->RecordCount();
	if(!$max_answers) {
	  $max_answers=$anscount;
	}
	$finished=$anscount-$max_answers;
	$answer .= "\t\t\t<script type='text/javascript'>\n"
	. "\t\t\t<!--\n"
	. "\t\t\t\tfunction rankthis_{$ia[0]}(\$code, \$value)\n"
	. "\t\t\t\t\t{\n"
	. "\t\t\t\t\t\$index=document.limesurvey.CHOICES_{$ia[0]}.selectedIndex;\n"
	. "\t\t\t\t\tdocument.limesurvey.CHOICES_{$ia[0]}.selectedIndex=-1;\n"
	. "\t\t\t\t\tfor (i=1; i<=$max_answers; i++)\n"
	. "\t\t\t\t\t\t{\n"
	. "\t\t\t\t\t\t\$b=i;\n"
	. "\t\t\t\t\t\t\$b += '';\n"
	. "\t\t\t\t\t\t\$inputname=\"RANK_{$ia[0]}\"+\$b;\n"
	. "\t\t\t\t\t\t\$hiddenname=\"fvalue_{$ia[0]}\"+\$b;\n"
	. "\t\t\t\t\t\t\$cutname=\"cut_{$ia[0]}\"+i;\n"
	. "\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='none';\n"
	. "\t\t\t\t\t\tif (!document.getElementById(\$inputname).value)\n"
	. "\t\t\t\t\t\t\t{\n"
	//CREATE A SECRET HIDDEN ELEMENT WITH THE ID FOR ARRAY_FILTER CONTROLS! 
	// SO FAR THIS JUST STOPS A JAVASCRIPT ERROR OCCURRING
	. "\t\t\t\t\t\t\tcurrentElement=document.createElement('input');\n"
	. "\t\t\t\t\t\t\tcurrentElement.setAttribute('id', 'javatbd{$ia[1]}'+\$code);\n"
	. "\t\t\t\t\t\t\tcurrentElement.setAttribute('name', 'javatbd{$ia[1]}'+\$code);\n"
	. "\t\t\t\t\t\t\tcurrentElement.setAttribute('value', \$value);\n"
	. "document.body.appendChild(currentElement);\n"
	//END OF SECRET HIDDEN ELEMENT
	. "\t\t\t\t\t\t\tdocument.getElementById(\$inputname).value=\$value;\n"
	. "\t\t\t\t\t\t\tdocument.getElementById(\$hiddenname).value=\$code;\n"
	. "\t\t\t\t\t\t\tdocument.getElementById(\$cutname).style.display='';\n"
	. "\t\t\t\t\t\t\tfor (var b=document.getElementById('CHOICES_{$ia[0]}').options.length-1; b>=0; b--)\n"
	. "\t\t\t\t\t\t\t\t{\n"
	. "\t\t\t\t\t\t\t\tif (document.getElementById('CHOICES_{$ia[0]}').options[b].value == \$code)\n"
	. "\t\t\t\t\t\t\t\t\t{\n"
	. "\t\t\t\t\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').options[b] = null;\n"
	. "\t\t\t\t\t\t\t\t\t}\n"
	. "\t\t\t\t\t\t\t\t}\n"
	. "\t\t\t\t\t\t\ti=$max_answers;\n"
	. "\t\t\t\t\t\t\t}\n"
	. "\t\t\t\t\t\t}\n"
	. "\t\t\t\t\tif (document.getElementById('CHOICES_{$ia[0]}').options.length == $finished)\n"
	. "\t\t\t\t\t\t{\n"
	. "\t\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').disabled=true;\n"
	. "\t\t\t\t\t\t}\n"
	. "\t\t\t\t\tcheckconditions(\$code);\n"
	. "\t\t\t\t\t}\n"
	. "\t\t\t\tfunction deletethis_{$ia[0]}(\$text, \$value, \$name, \$thisname)\n"
	. "\t\t\t\t\t{\n"
	. "\t\t\t\t\tvar qid='{$ia[0]}';\n"
	. "\t\t\t\t\tvar lngth=qid.length+4;\n"
	. "\t\t\t\t\tvar cutindex=\$thisname.substring(lngth, \$thisname.length);\n"
	. "\t\t\t\t\tcutindex=parseFloat(cutindex);\n"
	. "\t\t\t\t\tdocument.getElementById(\$name).value='';\n"
	. "\t\t\t\t\tdocument.getElementById(\$thisname).style.display='none';\n"
	. "\t\t\t\t\tif (cutindex > 1)\n"
	. "\t\t\t\t\t\t{\n"
	. "\t\t\t\t\t\t\$cut1name=\"cut_{$ia[0]}\"+(cutindex-1);\n"
	. "\t\t\t\t\t\t\$cut2name=\"fvalue_{$ia[0]}\"+(cutindex);\n"
	. "\t\t\t\t\t\tdocument.getElementById(\$cut1name).style.display='';\n"
	. "\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n"
	. "\t\t\t\t\t\t}\n"
	. "\t\t\t\t\telse\n"
	. "\t\t\t\t\t\t{\n"
	. "\t\t\t\t\t\t\$cut2name=\"fvalue_{$ia[0]}\"+(cutindex);\n"
	. "\t\t\t\t\t\tdocument.getElementById(\$cut2name).value='';\n"
	. "\t\t\t\t\t\t}\n"
	. "\t\t\t\t\tvar i=document.getElementById('CHOICES_{$ia[0]}').options.length;\n"
	. "\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').options[i] = new Option(\$text, \$value);\n"
	. "\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').options[i].id = 'javatbd{$ia[1]}'+\$value;\n"
	. "\t\t\t\t\tif (document.getElementById('CHOICES_{$ia[0]}').options.length > 0)\n"
	. "\t\t\t\t\t\t{\n"
	. "\t\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').disabled=false;\n"
	. "\t\t\t\t\t\t}\n"
	. "\t\t\t\t\tcheckconditions('');\n"
	. "\t\t\t\t\t}\n"
	. "\t\t\t//-->\n"
	. "\t\t\t</script>\n";
	unset($answers);
	//unset($inputnames);
	unset($chosen);
	$ranklist="";
	while ($ansrow = $ansresult->FetchRow())
	{
		$answers[] = array($ansrow['code'], $ansrow['answer']);
	}
	$existing=0;
	for ($i=1; $i<=$anscount; $i++)
	{
		$myfname=$ia[1].$i;
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname])
		{
			$existing++;
		}
	}
	for ($i=1; $i<=$max_answers; $i++)
	{
		$myfname = $ia[1].$i;
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname])
		{
			foreach ($answers as $ans)
			{
				if ($ans[0] == $_SESSION[$myfname])
				{
					$thiscode=$ans[0];
					$thistext=$ans[1];
				}
			}
		}
		$ranklist .= "\t\t\t\t\t\t\t<tr><td style=\"text-align:right;\">&nbsp;<label for='RANK_{$ia[0]}$i'>"
		."$i:&nbsp;</label></td><td><input class='text' type='text' name='RANK_{$ia[0]}$i' id='RANK_{$ia[0]}$i'";
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname])
		{
			$ranklist .= " value='";
			$ranklist .= htmlspecialchars($thistext, ENT_QUOTES);
			$ranklist .= "'";
		}
		$ranklist .= " onfocus=\"this.blur()\" />\n";
		$ranklist .= "\t\t\t\t\t\t<input type='hidden' name='$myfname' id='fvalue_{$ia[0]}$i' value='";
		$chosen[]=""; //create array
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname])
		{
			$ranklist .= $thiscode;
			$chosen[]=array($thiscode, $thistext);
		}
		$ranklist .= "' />\n";
		$ranklist .= "\t\t\t\t\t\t<img src='$imagefiles/cut.gif' alt='".$clang->gT("Remove this item")."' title='".$clang->gT("Remove this item")."' ";
		if ($i != $existing)
		{
			$ranklist .= "style='display:none'";
		}
		$ranklist .= " id='cut_{$ia[0]}$i' onclick=\"deletethis_{$ia[0]}(document.limesurvey.RANK_{$ia[0]}$i.value, document.limesurvey.fvalue_{$ia[0]}$i.value, document.limesurvey.RANK_{$ia[0]}$i.name, this.id)\" /><br />\n";
		$inputnames[]=$myfname;
		$ranklist .= "</td></tr>\n";
	}

	$choicelist = "\t\t\t\t\t\t<select size='$anscount' name='CHOICES_{$ia[0]}' ";
	if (isset($choicewidth)) {$choicelist.=$choicewidth;}
    $choicelist .= " id='CHOICES_{$ia[0]}' onclick=\"if (this.options.length>0 && this.selectedIndex<0) {this.options[this.options.length-1].selected=true;}; rankthis_{$ia[0]}(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" class='select'>\n";
	$hiddens="";
	foreach ($answers as $ans)
	{
		if (!in_array($ans, $chosen))
		{
		    $choicelist .= "\t\t\t\t\t\t\t<option value='{$ans[0]}' id='javatbd{$ia[1]}{$ans[0]}'";
			if (($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == false)  || 
			    ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'A'))
			{
			    $choicelist .= " style='display: none'";
			}
			$choicelist .= ">{$ans[1]}</option>\n";
			if (isset($maxselectlength) && strlen($ans[1]) > $maxselectlength) {$maxselectlength = strlen($ans[1]);}
		}
		$hiddens.="<input type=\"hidden\" name=\"tbdisp{$ia[1]}{$ans[0]}\" id=\"tbdisp{$ia[1]}{$ans[0]}\" value=\"off\" />\n";
	}
	$choicelist .= "\t\t\t\t\t\t</select>\n";
	$choicelist .= $hiddens;

	$answer .= "\t\t\t<table border='0' cellspacing='5' width='500' class='rank'>\n"
	. "\t\t\t\t<tr>\n"
	. "\t\t\t\t\t<td colspan='2' class='rank'><font size='1'>\n"
	. "\t\t\t\t\t\t".$clang->gT("Click on an item in the list on the left, starting with your")
	. "\t\t\t\t\t\t".$clang->gT("highest ranking item, moving through to your lowest ranking item.")
	. "\t\t\t\t\t</font></td>\n"
	. "\t\t\t\t</tr>\n"
	. "\t\t\t\t<tr>\n"
	. "\t\t\t\t\t<td align='left' valign='top' class='rank'>\n"
	. "\t\t\t\t\t\t<strong>&nbsp;&nbsp;<label for='CHOICES_{$ia[0]}'>".$clang->gT("Your Choices").":</label></strong><br />\n"
	. "&nbsp;".$choicelist
	. "\t\t\t\t\t&nbsp;</td>\n";
	if (isset($maxselectlength) && $maxselectlength > 60)
	{
		$ranklist = str_replace("<input class='text'", "<input size='60' class='text'", $ranklist);
		$answer .= "\t\t\t\t</tr>\n\t\t\t\t<tr>\n"
		. "\t\t\t\t\t<td align='left' width='250' class='rank'>\n"
		. "\t\t\t\t\t\t\t<table border='0' cellspacing='1' cellpadding='0'>\n"
		. "\t\t\t\t\t\t\t<tr><td></td><td><strong>".$clang->gT("Your Ranking").":</strong></td></tr>\n";
	}
	else
	{
		$answer .= "\t\t\t\t\t<td style=\"text-align:left; width:250px; white-space:nowrap;\" class=\"rank\">\n"
		. "\t\t\t\t\t\t\t<table border='0' cellspacing='1' cellpadding='0'>\n"
		. "\t\t\t\t\t\t\t<tr><td></td><td><strong>".$clang->gT("Your Ranking").":</strong></td></tr>\n";
	}
	$answer .= $ranklist
	. "\t\t\t\t\t\t\t</table>\n"
	. "\t\t\t\t\t</td>\n"
	. "\t\t\t\t</tr>\n"
	. "\t\t\t\t<tr>\n"
	. "\t\t\t\t\t<td colspan='2' class='rank'><font size='1'>\n"
	. "\t\t\t\t\t\t".$clang->gT("Click on the scissors next to each item on the right to remove the last entry in your ranked list").""
	. "\t\t\t\t\t</font></td>\n"
	. "\t\t\t\t</tr>\n"
	. "\t\t\t</table>\n";

	if ($minanswattr=arraySearchByKey("min_answers", $qidattributes, "attribute", 1))
	{ 
		$minansw=$minanswattr['value'];
		$minanswscript = "<script type='text/javascript'>\n"
			. "\t\t\t<!--\n"
			. "\t\t\t\toldonsubmit_{$ia[0]} = document.limesurvey.onsubmit;\n"
			. "\t\t\t\tfunction ensureminansw_{$ia[0]}()\n"
			. "\t\t\t\t{\n"
			. "\t\t\t\t\tcount={$anscount} - document.limesurvey.CHOICES_{$ia[0]}.options.length;\n"
			. "\t\t\t\t\tif (count < {$minansw} && document.getElementById('display{$ia[0]}').value == 'on'){\n"
			. "\t\t\t\t\t\talert('".sprintf($clang->gT("Please rank at least '%d' item(s) for question \"%s\"","js"),  
				$minansw, trim(javascript_escape($ia[3],true,true)))."');\n"
			. "\t\t\t\t\t\treturn false;\n"
			. "\t\t\t\t\t} else {\n"	
			. "\t\t\t\t\t\tif (oldonsubmit_{$ia[0]}){\n"
			. "\t\t\t\t\t\t\treturn oldonsubmit_{$ia[0]}();\n"
			. "\t\t\t\t\t\t}\n"
			. "\t\t\t\t\t\treturn true;\n"
			. "\t\t\t\t\t}\n"	
			. "\t\t\t\t}\n"
			. "\t\t\t\tdocument.limesurvey.onsubmit = ensureminansw_{$ia[0]}\n"
			. "\t\t\t\t-->\n"
			. "\t\t\t</script>\n";
		$answer = $minanswscript . $answer;
	}

	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_multiplechoice($ia)
{
	global $dbprefix, $clang, $connect;

	$qidattributes=getQuestionAttributes($ia[0]);

	if ($othertexts=arraySearchByKey('other_replace_text', $qidattributes, 'attribute', 1))
	{
		$othertext=$clang->gT($othertexts['value']);
	}
	else
	{
		$othertext=$clang->gT('Other');
	}

	if ($displaycols=arraySearchByKey('display_columns', $qidattributes, 'attribute', 1))
	{
		$dcols = $displaycols['value'];
	}
	else
	{
		$dcols = 1;
	}

	// Check if the max_answers attribute is set
	$maxansw = 0;
	$callmaxanswscriptcheckbox = '';
	$callmaxanswscriptother = '';
	$maxanswscript = '';
	if ($excludeothers=arraySearchByKey('exclude_all_others', $qidattributes, 'attribute', ''))
	{
		foreach($excludeothers as $excludeother) {
		$excludeallothers[]=$excludeother['value'];
	}
	$excludeallotherscript = "
		<script type='text/javascript'>
		<!--
		function excludeAllOthers$ia[1](value, doconditioncheck)
		{\n";
		$excludeallotherscripton='';
		$excludeallotherscriptoff='';
	}
	else
	{
		$excludeallothers=array();
	}
	if ($maxanswattr=arraySearchByKey('max_answers', $qidattributes, 'attribute', 1))
	{
		$maxansw=$maxanswattr['value'];
		$callmaxanswscriptcheckbox = "limitmaxansw_{$ia[0]}(this);";
		$callmaxanswscriptother = "onkeyup='limitmaxansw_{$ia[0]}(this)'";
		$maxanswscript = "\t\t\t<script type='text/javascript'>\n"
			. "\t\t\t<!--\n"
			. "\t\t\t\tfunction limitmaxansw_{$ia[0]}(me)\n"
			. "\t\t\t\t\t{\n"
			. "\t\t\t\t\tmax=$maxansw\n"
			. "\t\t\t\t\tcount=0;\n"
			. "\t\t\t\t\tif (max == 0) { return count; }\n";
	}


	// Check if the min_answers attribute is set
	$minansw=0;
	$minanswscript = "";
	if ($minanswattr=arraySearchByKey("min_answers", $qidattributes, "attribute", 1))
	{
		$minansw=$minanswattr['value'];
		$minanswscript = "<script type='text/javascript'>\n"
			. "\t\t\t<!--\n"
			. "\t\t\t\toldonsubmit_{$ia[0]} = document.limesurvey.onsubmit;\n"
			. "\t\t\t\tfunction ensureminansw_{$ia[0]}()\n"
			. "\t\t\t\t{\n"
			. "\t\t\t\t\tcount=0;\n"
			;		
	}

	$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
	$qresult = db_execute_assoc($qquery);     //Checked
	while($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}
	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0]  AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
	}
	else
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0]  AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	}

	$ansresult = db_execute_assoc($ansquery);  //Checked
	$anscount = $ansresult->RecordCount();

	if ($other == 'Y') {$anscount++;} //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!

	$wrapper = setup_columns($dcols, $anscount);

	$answer = '<input type="hidden" name="MULTI'.$ia[1].'" value="'.$anscount."\" />\n\n".$wrapper['whole-start'];

	$fn = 1;
	if (!isset($multifields))
	{
		$multifields = '';
	}

	$rowcounter = 0;
	$colcounter = 1;
	$postrow = '';
	while ($ansrow = $ansresult->FetchRow())
	{
		$myfname = $ia[1].$ansrow['code'];
		$answer .= $wrapper['item-start'].'		<input class="checkbox" type="checkbox" name="'.$ia[1].$ansrow['code'].'" id="answer'.$ia[1].$ansrow['code'].'" value="Y"';
		if (isset($_SESSION[$myfname]))
		{
			if ($_SESSION[$myfname] == 'Y')
			{
				$answer .= CHECKED;
				if(in_array($ansrow['code'], $excludeallothers)) 
				{
					$postrow.="\n\n<script type='text/javascript'>\n<!--\nexcludeAllOthers$ia[1]('answer$ia[1]{$ansrow['code']}', 'no');\n-->\n</script>\n";
				}
			}
		}
		elseif ($ansrow['default_value'] == 'Y')
		{
			$answer .= CHECKED;
		}
		// --> START NEW FEATURE - SAVE
		$answer .= " onclick='cancelBubbleThis(event);";
		if(in_array($ansrow['code'], $excludeallothers))
		{
			$answer .= "excludeAllOthers$ia[1](this.id, \"yes\");"; // was "this.id"
			$excludeallotherscripton .= "/* SKIPPING QUESTION {$ia[1]} */\n";
//			$excludeallotherscripton .= "alert(value+'---'+'answer$ia[1]{$ansrow['code']}');\n";
			$excludeallotherscripton .= "if( value != 'answer$ia[1]{$ansrow['code']}') {\n"
						 . "\tthiselt=document.getElementById('answer$ia[1]{$ansrow['code']}');\n"
						 . "\t\tthiselt.checked='';\n"
						 . "\t\tthiselt.disabled='true';\n"
						 . "\t\tif (doconditioncheck == 'yes') {\n"
						 . "\t\t\tcheckconditions(thiselt.value, thiselt.name, thiselt.type);\n"
						 . "\t\t}\n}\n";
			$excludeallotherscriptoff .= "document.getElementById('answer$ia[1]{$ansrow['code']}').disabled='';\n";
		}
		elseif (count($excludeallothers)>0)
		{
			$excludeallotherscripton .= "\tthiselt=document.getElementById('answer$ia[1]{$ansrow['code']}');\n"
						 . "\t\tthiselt.checked='';\n"
						 . "\t\tthiselt.disabled='true';\n"
						 . "\t\tif (doconditioncheck == 'yes') {\n"
						 . "\t\t\tcheckconditions(thiselt.value, thiselt.name, thiselt.type);\n"
						 . "\t\t}\n";
			$excludeallotherscriptoff.= "document.getElementById('answer$ia[1]{$ansrow['code']}').disabled='';\n";
		}
		$answer .= $callmaxanswscriptcheckbox."checkconditions(this.value, this.name, this.type)' />\n\t\t<label for=\"answer$ia[1]{$ansrow['code']}\" class=\"answertext\">{$ansrow['answer']}</label>\n";
		// --> END NEW FEATURE - SAVE

		if ($maxansw > 0) {$maxanswscript .= "\t\t\t\t\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}
		if ($minansw > 0) {$minanswscript .= "\t\t\t\t\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}

		++$fn;
		$answer .= '		<input type="hidden" name="java'.$myfname.'" id="java'.$myfname.'" value="'; 
		if (isset($_SESSION[$myfname]))
		{
			$answer .= $_SESSION[$myfname];
		}
		$answer .= "\" />\n{$wrapper['item-end']}";

		$inputnames[]=$myfname;

		++$rowcounter;
		if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
		{
			if($colcounter == $wrapper['cols'] - 1)
			{
				$answer .= $wrapper['col-devide-last'];
			}
			else
			{
				$answer .= $wrapper['col-devide'];
			}
			$rowcounter = 0;
			++$colcounter;
		}
	}
	if ($other == 'Y')
	{
		$myfname = $ia[1].'other';
		if(count($excludeallothers) > 0) 
		{
			$excludeallotherscripton .= "thiselt=document.getElementById('answer{$ia[1]}othercbox');\n"
						 . "\t\tthiselt.checked='';\n"
						 . "\t\tthiselt.disabled='true';\n";
			$excludeallotherscripton .= "thiselt=document.getElementById('answer$ia[1]other');\n"
						 . "\t\tthiselt.value='';\n"
						 . "\t\tthiselt.disabled='true';\n"
						 . "\t\tif (doconditioncheck == 'yes') {\n"
						 . "\t\t\tcheckconditions(thiselt.value, thiselt.name, thiselt.type);\n"
						 . "\t\t}\n";
			$excludeallotherscriptoff .="document.getElementById('answer$ia[1]other').disabled='';\n";
			$excludeallotherscriptoff .="document.getElementById('answer{$ia[1]}othercbox').disabled='';\n";
		}

		$answer .= $wrapper['item-start'].'
		<input class="checkbox" type="checkbox" name="'.$myfname.'cbox" id="answer'.$myfname.'cbox"';

		if (isset($_SESSION[$myfname]) && trim($_SESSION[$myfname])!='')
		{
			$answer .= CHECKED;
		}
		$answer .= " onclick='cancelBubbleThis(event);".$callmaxanswscriptcheckbox."document.getElementById(\"answer$myfname\").value=\"\";' />
		<label for=\"answer$myfname\" class=\"answertext\">".$othertext.":</label>
		<input class=\"text\" type=\"text\" name=\"$myfname\" id=\"answer$myfname\"";
		if (isset($_SESSION[$myfname]))
		{
			$answer .= ' value="'.htmlspecialchars($_SESSION[$myfname],ENT_QUOTES).'"';
		}
		$answer .= " onkeypress='document.getElementById(\"answer{$myfname}cbox\").checked=true;' ".$callmaxanswscriptother.' />
		<input type="hidden" name="java'.$myfname.'" id="java'.$myfname.'" value="';

		if ($maxansw > 0)
		{
		//TODO: implement the other_comment_mandatory attribute 
		// and a new db field for other cbox
		//
		// For multiplechoice question there is no DB field for the other Checkbox
		// so in fact I need to assume that other_comment_mandatory is set to true
		// otherwise, the min/max asnwer script will conflict with the 
		// MANDATORY status of the question
		// ==> hence the 1==1
			if (1==1 ||$other_comment_mandatory=arraySearchByKey('other_comment_mandatory', $qidattributes, 'attribute', 1))
			{
				$maxanswscript .= "\t\t\t\t\tif (document.getElementById('answer".$myfname."').value != '' && document.getElementById('answer".$myfname."cbox').checked ) { count += 1; }\n"; 
			}
			else
			{
				$maxanswscript .= "\t\t\t\t\tif (document.getElementById('answer".$myfname."').value != '' || document.getElementById('answer".$myfname."cbox').checked ) { count += 1; }\n"; 
			}
		}
		if ($minansw > 0)
		{ 
		//TODO: implement the other_comment_mandatory attribute 
		// and a new db field for other cbox
		//
		// For multiplechoice question there is no DB field for the other Checkbox
		// so in fact I need to assume that other_comment_mandatory is set to true
		// otherwise, the min/max asnwer script will conflict with the 
		// MANDATORY status of the question
		// ==> hence the 1==1
			if (1==1 || $other_comment_mandatory=arraySearchByKey('other_comment_mandatory', $qidattributes, 'attribute', 1))
			{
				$minanswscript .= "\t\t\t\t\tif (document.getElementById('answer".$myfname."').value != '' && document.getElementById('answer".$myfname."cbox').checked ) { count += 1; }\n"; 
			}
			else
			{
				$minanswscript .= "\t\t\t\t\tif (document.getElementById('answer".$myfname."').value != '' || document.getElementById('answer".$myfname."cbox').checked ) { count += 1; }\n"; 
			}
		}


		if (isset($_SESSION[$myfname]))
		{
			$answer .= htmlspecialchars($_SESSION[$myfname],ENT_QUOTES);
		}

		$answer .= "\" />\n{$wrapper['item-end']}";
		$inputnames[]=$myfname;
		++$anscount;

		++$rowcounter;
		if ($rowcounter == $wrapper['maxrows'] && $colcounter < $wrapper['cols'])
		{
			if($colcounter == $wrapper['cols'] - 1)
			{
				$answer .= $wrapper['col-devide-last'];
			}
			else
			{
				$answer .= $wrapper['col-devide'];
			}
			$rowcounter = 0;
			++$colcounter;
		}
	}
	$answer .= $wrapper['whole-end'];

	if ( $maxansw > 0 )
	{
		$maxanswscript .= "\t\t\t\t\tif (count > max)\n"
			. "\t\t\t\t\t\t{\n"
			. "\t\t\t\t\t\talert('".sprintf($clang->gT("Please choose at most '%d' answer(s) for question \"%s\"","js"), $maxansw, trim(javascript_escape(str_replace(array("\n", "\r"), "", $ia[3]),true,true)))."');\n"
			. "\t\t\t\t\t\tif (me.type == 'checkbox') {me.checked = false;}\n"
			. "\t\t\t\t\t\tif (me.type == 'text') {\n"
			. "\t\t\t\t\t\t\tme.value = '';\n"
			. "\t\t\t\t\t\t\tif (document.getElementById(me.name + 'cbox') ){\n"
			. "\t\t\t\t\t\t\t\t document.getElementById(me.name + 'cbox').checked = false;\n"
			. "\t\t\t\t\t\t\t}\n"
			. "\t\t\t\t\t\t}"
			. "\t\t\t\t\t\treturn max;\n"
			. "\t\t\t\t\t\t}\n"
			. "\t\t\t\t\t}\n"
			. "\t\t\t//-->\n"
			. "\t\t\t</script>\n";
		$answer = $maxanswscript . $answer;  
	}
	
	
	if ( $minansw > 0 )
	{
		$minanswscript .= 		
			"\t\t\t\t\tif (count < {$minansw} && document.getElementById('display{$ia[0]}').value == 'on'){\n"
			. "\t\t\t\t\t\talert('".sprintf($clang->gT("Please choose at least '%d' answer(s) for question \"%s\"","js"),  
				$minansw, trim(javascript_escape($ia[3],true,true)))."');\n"
			. "\t\t\t\t\t\treturn false;\n"
			. "\t\t\t\t\t} else {\n"	
			. "\t\t\t\t\t\tif (oldonsubmit_{$ia[0]}){\n"
			. "\t\t\t\t\t\t\treturn oldonsubmit_{$ia[0]}();\n"
			. "\t\t\t\t\t\t}\n"
			. "\t\t\t\t\t\treturn true;\n"
			. "\t\t\t\t\t}\n"	
			. "\t\t\t\t}\n"
			. "\t\t\t\tdocument.limesurvey.onsubmit = ensureminansw_{$ia[0]}\n"
			. "\t\t\t\t-->\n"
			. "\t\t\t</script>\n";
		$answer = $minanswscript . $answer;
	}
	
	if (count($excludeallothers)>0)
	{
		$excludeallotherscript .= "
		if (document.getElementById(value).checked)
		{
			$excludeallotherscripton
		}
		else
		{
			$excludeallotherscriptoff
		}
		}
		//-->
		</script>";
		$answer = $excludeallotherscript . $answer;
	}
	$answer .= $postrow;
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_multiplechoice_withcomments($ia)
{
	global $dbprefix, $clang;
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($othertexts=arraySearchByKey('other_replace_text', $qidattributes, 'attribute', 1))
	{
		$othertext=$clang->gT($othertexts['value']);
	}
	else
	{
		$othertext=$clang->gT('Other');
	}
	// Check if the max_answers attribute is set
	$maxansw=0;
	$callmaxanswscriptcheckbox = '';
	$callmaxanswscriptcheckbox2 = '';
	$callmaxanswscriptother = '';
	$maxanswscript = '';
	if ($maxanswattr=arraySearchByKey('max_answers', $qidattributes, 'attribute', 1))
	{
		$maxansw=$maxanswattr['value'];
		$callmaxanswscriptcheckbox = "limitmaxansw_{$ia[0]}(this);";
		$callmaxanswscriptcheckbox2= "limitmaxansw_{$ia[0]}";
		$callmaxanswscriptother = "onkeyup=\"limitmaxansw_{$ia[0]}(this)\"";

		$maxanswscript = "\t\t\t<script type='text/javascript'>\n"
			. "\t\t\t<!--\n"
			. "\t\t\t\tfunction limitmaxansw_{$ia[0]}(me)\n"
			. "\t\t\t\t\t{\n"
			. "\t\t\t\t\tmax=$maxansw\n"
			. "\t\t\t\t\tcount=0;\n"
			. "\t\t\t\t\tif (max == 0) { return count; }\n";
	}

	// Check if the min_answers attribute is set
	$minansw=0;
	$minanswscript = "";
	if ($minanswattr=arraySearchByKey("min_answers", $qidattributes, "attribute", 1))
	{
		$minansw=$minanswattr['value'];
		$minanswscript = "<script type='text/javascript'>\n"
			. "\t\t\t<!--\n"
			. "\t\t\t\toldonsubmit_{$ia[0]} = document.limesurvey.onsubmit;\n"
			. "\t\t\t\tfunction ensureminansw_{$ia[0]}()\n"
			. "\t\t\t\t{\n"
			. "\t\t\t\t\tcount=0;\n"
			;		
	}

	$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."' ";
	$qresult = db_execute_assoc($qquery);     //Checked
	while ($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}
	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0]  AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
	} else {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0]  AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	}
	$ansresult = db_execute_assoc($ansquery);  //Checked
	$anscount = $ansresult->RecordCount()*2;

	$answer = "<input type='hidden' name='MULTI$ia[1]' value='$anscount' />\n";
	$answer_main = '';

	$fn = 1;
	if($other == 'Y')
	{
		$label_width = 25;
	}
	else
	{
		$label_width = 0;
	}

	while ($ansrow = $ansresult->FetchRow())
	{
		if($label_width < strlen(trim(strip_tags($ansrow['answer']))))
		{
			$label_width = strlen(trim(strip_tags($ansrow['answer'])));
		}

		$myfname = $ia[1].$ansrow['code'];
		$myfname2 = $myfname."comment";
		$answer_main .= "\t<li>\n\t\t<label for=\"answer$myfname\" class=\"answertext\">\n"
		. "\t\t\t<input class=\"checkbox\" type=\"checkbox\" name=\"$myfname\" id=\"answer$myfname\" value=\"Y\"";
		if (isset($_SESSION[$myfname]))
		{
			if ($_SESSION[$myfname] == 'Y')
			{
				$answer_main .= CHECKED;
			}
		}
		elseif ($ansrow['default_value'] == 'Y')
		{
			$answer_main .= CHECKED;
		}
		$answer_main .=" onclick='cancelBubbleThis(event);".$callmaxanswscriptcheckbox."checkconditions(this.value, this.name, this.type)' "
				. " onchange='document.getElementById(\"answer$myfname2\").value=\"\";' />\n"
				. $ansrow['answer']."\t\t</label>\n";

		if ($maxansw > 0) {$maxanswscript .= "\t\t\t\t\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}
		if ($minansw > 0) {$minanswscript .= "\t\t\t\t\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}

		$answer_main .= "\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='";
		if (isset($_SESSION[$myfname])) {$answer_main .= $_SESSION[$myfname];}
		$answer_main .= "' />\n";
		$fn++;
		$answer_main .= "\t\t<label for='answer$myfname2' class=\"answer-comment\">\n"
		."\t\t\t<input class='text' type='text' size='40' id='answer$myfname2' name='$myfname2' title='".$clang->gT("Make a comment on your choice here:")."' value='";
		if (isset($_SESSION[$myfname2])) {$answer_main .= htmlspecialchars($_SESSION[$myfname2],ENT_QUOTES);}
		// --> START NEW FEATURE - SAVE
		$answer_main .= "'  onclick='cancelBubbleThis(event);' onkeypress='document.getElementById(\"answer{$myfname}\").checked=true;checkconditions(document.getElementById(\"answer{$myfname}\").value,\"$myfname\",\"checkbox\");' onKeyUp='".$callmaxanswscriptcheckbox2."(document.getElementById(\"answer{$myfname}\"))' />\n\t\t</label>\n"

		. "\t</li>\n";
		// --> END NEW FEATURE - SAVE

		$fn++;
		$inputnames[]=$myfname;
		$inputnames[]=$myfname2;
	}
	if ($other == 'Y')
	{
		$myfname = $ia[1].'other';
		$myfname2 = $myfname.'comment';
		$anscount = $anscount + 2;
		$answer_main .= "\t<li class=\"other\">\n"
		. "\t\t<label for=\"answer$myfname\" class=\"answertext\">\n\t\t\t".$othertext.":\n\t\t\t<input class=\"text other\" type=\"text\" name=\"$myfname\" id=\"answer$myfname\" title=\"".$clang->gT('Other').'" size="10"';
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname])
		{
			$answer_main .= ' value="'.htmlspecialchars($_SESSION[$myfname],ENT_QUOTES).'"';
		}
		$fn++;
		// --> START NEW FEATURE - SAVE
		$answer_main .= "  $callmaxanswscriptother />\n\t\t</label>\n"
		. "\t\t<label for=\"answer$myfname2\" class=\"answer-comment\">\n"
		. '			<input class="text" type="text" size="40" name="'.$myfname2.'" id="answer'.$myfname2.'" title="'.$clang->gT('Make a comment on your choice here:').'" value="';
		// --> END NEW FEATURE - SAVE

		if (isset($_SESSION[$myfname2])) {$answer_main .= htmlspecialchars($_SESSION[$myfname2],ENT_QUOTES);}
		// --> START NEW FEATURE - SAVE
		$answer_main .= '"  onKeyUp="'.$callmaxanswscriptcheckbox2.'(document.getElementById(\'answer'.$myfname."'))\" />\n";

		if ($maxansw > 0)
		{
			if ($other_comment_mandatory=arraySearchByKey('other_comment_mandatory', $qidattributes, 'attribute', 1))
			{
				$maxanswscript .= "\t\t\t\t\tif (document.getElementById('answer".$myfname."').value != '' && document.getElementById('answer".$myfname2."').value != '') { count += 1; }\n"; 
			}
			else
			{
				$maxanswscript .= "\t\t\t\t\tif (document.getElementById('answer".$myfname."').value != '') { count += 1; }\n"; 
			}
		}

		if ($minansw > 0)
		{
			if ($other_comment_mandatory=arraySearchByKey('other_comment_mandatory', $qidattributes, 'attribute', 1))
			{
				$minanswscript .= "\t\t\t\t\tif (document.getElementById('answer".$myfname."').value != '' && document.getElementById('answer".$myfname2."').value != '') { count += 1; }\n"; 
			}
			else
			{
				$minanswscript .= "\t\t\t\t\tif (document.getElementById('answer".$myfname."').value != '') { count += 1; }\n"; 
			}
		}

		$answer_main .= "\t\t</label>\n\t</li>\n";
		// --> END NEW FEATURE - SAVE

		$inputnames[]=$myfname;
		$inputnames[]=$myfname2;
	}
	$label_width = round($label_width * 0.5);
	if($label_width < 2) $label_width = 2;
	switch($label_width / 2)
	{
		case 1: 
		case 2:
		case 3:
		case 4:
		case 5:
		case 6:
		case 7:
		case 8:
		case 9:
		case 10:
		case 11:
		case 12:
		case 13:
		case 14:
		case 15:	$label_width = $label_width;
				break;
		default:	++$label_width;
	}
	if($label_width > 20)
	{
		$label_width = 'X-large';
	}
	else
	{
		$label_width = 'X'.$label_width;
	}

	$answer .= '<ul class="'.$label_width."\">\n".$answer_main."</ul>\n";


	if ( $maxansw > 0 )
	{
		$maxanswscript .= "\t\t\t\t\tif (count > max)\n"
			. "\t\t\t\t\t\t{\n"
			. "\t\t\t\t\t\talert('".sprintf($clang->gT("Please choose at most '%d' answer(s) for question \"%s\"","js"), $maxansw, trim(javascript_escape($ia[3],true,true)))."');\n"
			. "\t\t\t\t\t\tvar commentname='answer'+me.name+'comment';\n"
			. "\t\t\t\t\t\tif (me.type == 'checkbox') {\n"
			. "\t\t\t\t\t\t\tme.checked = false;\n"
			. "\t\t\t\t\t\t\tvar commentname='answer'+me.name+'comment';\n"
			. "\t\t\t\t\t\t}\n"
			. "\t\t\t\t\t\tif (me.type == 'text') {\n"
			. "\t\t\t\t\t\t\tme.value = '';\n"
			. "\t\t\t\t\t\t\tif (document.getElementById(me.name + 'cbox') ){\n"
			. "\t\t\t\t\t\t\t\t document.getElementById(me.name + 'cbox').checked = false;\n"
			. "\t\t\t\t\t\t\t}\n"
			. "\t\t\t\t\t\t}"
			. "\t\t\t\t\t\tdocument.getElementById(commentname).value='';\n"
			. "\t\t\t\t\t\treturn max;\n"
			. "\t\t\t\t\t\t}\n"
			. "\t\t\t\t\t}\n"
			. "\t\t\t//-->\n"
			. "\t\t\t</script>\n";
		$answer = $maxanswscript . $answer;
	}

	if ( $minansw > 0 )
	{
		$minanswscript .= 		
			"\t\t\t\t\tif (count < {$minansw} && document.getElementById('display{$ia[0]}').value == 'on'){\n"
			. "\t\t\t\t\t\talert('".sprintf($clang->gT("Please choose at least '%d' answer(s) for question \"%s\"","js"),  
				$minansw, trim(javascript_escape($ia[3],true,true)))."');\n"
			. "\t\t\t\t\t\treturn false;\n"
			. "\t\t\t\t\t} else {\n"	
			. "\t\t\t\t\t\tif (oldonsubmit_{$ia[0]}){\n"
			. "\t\t\t\t\t\t\treturn oldonsubmit_{$ia[0]}();\n"
			. "\t\t\t\t\t\t}\n"
			. "\t\t\t\t\t\treturn true;\n"
			. "\t\t\t\t\t}\n"	
			. "\t\t\t\t}\n"
			. "\t\t\t\tdocument.limesurvey.onsubmit = ensureminansw_{$ia[0]}\n"
			. "\t\t\t\t-->\n"
			. "\t\t\t</script>\n";
		$answer = $minanswscript . $answer;
	}

	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_multipleshorttext($ia)
{
	global $dbprefix, $clang;
	$qidattributes=getQuestionAttributes($ia[0]);

	if (arraySearchByKey('numbers_only', $qidattributes, 'attribute', 1))
	{
		$numbersonly = 'onkeypress="return goodchars(event,\'0123456789.\')"';
		$class_num_only = ' numbers-only';
	}
	else
	{
		$numbersonly = '';
		$class_num_only = '';
	}
	if ($maxchars=arraySearchByKey('maximum_chars', $qidattributes, 'attribute', 1))
	{
		$maxsize=$maxchars['value'];
	}
	else
	{
		$maxsize=255;
	}
	if ($textinputwidth=arraySearchByKey('text_input_width', $qidattributes, 'attribute', 1))
	{
		$tiwidth=$textinputwidth['value'];
	}
	else
	{
		$tiwidth=20;
	}

	if ($prefix=arraySearchByKey('prefix', $qidattributes, 'attribute', 1))
	{
		$prefix = $prefix['value'];
	}
	else
	{
		$prefix = '';
	}

	if ($suffix=arraySearchByKey('suffix', $qidattributes, 'attribute', 1))
	{
		$suffix = $suffix['value'];
	}
	else
	{
		$suffix = '';
	}

	if (arraySearchByKey('random_order', $qidattributes, 'attribute', 1))
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0]  AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
	}
	else
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0]  AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	}

	$ansresult = db_execute_assoc($ansquery);    //Checked
	$anscount = $ansresult->RecordCount()*2;
	//$answer .= "\t\t\t\t\t<input type='hidden' name='MULTI$ia[1]' value='$anscount'>\n";
	$fn = 1;

	$answer = keycontroljs();
	$answer_main = '';

	$label_width = 0;

	if ($anscount==0) 
	{
		$inputnames=array();
		$answer_main .= '	<li>'.$clang->gT('Error: This question has no answers.')."</li>\n";
	}
	else
	{
	 	while ($ansrow = $ansresult->FetchRow())
		{
			$myfname = $ia[1].$ansrow['code'];
			$answer_main .= "\t<li>\n"
			. "\t\t<label for=\"answer$myfname\">{$ansrow['answer']}</label>\n"
			. "\t\t\t<span>\n\t\t\t\t".$prefix."\n\t\t\t\t".'<input class="text" type="text" size="'.$tiwidth.'" name="'.$myfname.'" id="answer'.$myfname.'" value="';

			if($label_width < strlen(trim(strip_tags($ansrow['answer']))))
			{
				$label_width = strlen(trim(strip_tags($ansrow['answer'])));
			}

			if (isset($_SESSION[$myfname]))
			{
				$answer_main .= $_SESSION[$myfname];
			}
	
			// --> START NEW FEATURE - SAVE
			$answer_main .= '" onchange="checkconditions(this.value, this.name, this.type);" '.$numbersonly.' maxlength="'.$maxsize.'" />'."\n\t\t\t\t".$suffix."\n\t\t\t</span>\n"
			. "\t</li>\n";
			// --> END NEW FEATURE - SAVE
	
			$fn++;
			$inputnames[]=$myfname;
		}
	}
	$label_width = round($label_width * 0.6);
	if($label_width < 2) $label_width = 2;
	switch($label_width / 2)
	{
		case 1: 
		case 2:
		case 3:
		case 4:
		case 5:
		case 6:
		case 7:
		case 8:
		case 9:
		case 10:
		case 11:
		case 12:
		case 13:
		case 14:
		case 15:	$label_width = $label_width;
				break;
		default:	++$label_width;
	}
	if (!empty($numbersonly))
	{
		$class_num_only = ' numbers-only';
		if($label_width > 30)
		{
			$label_width = 'X-large';
		}
	}
	else
	{
		if($label_width > 20)
		{
			$label_width = 'X-large';
		}
		$class_num_only = '';
	}

	if($label_width != 'X-large')
	{
		$label_width = 'X'.$label_width;
	}

	$answer .= '<ul class="'.$label_width.$class_num_only."\">\n".$answer_main."</ul>\n";

	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_multiplenumeric($ia)
{
	global $dbprefix, $clang, $js_header_includes;
	$qidattributes=getQuestionAttributes($ia[0]);

	//Must turn on the "numbers only javascript"
	$numbersonly = 'onkeypress="return goodchars(event,\'0123456789.\')"';
	if ($maxchars=arraySearchByKey('maximum_chars', $qidattributes, 'attribute', 1))
	{
		$maxsize=$maxchars['value'];
	}
	else
	{
		$maxsize = 255;
	}

	//EQUALS VALUE
	if ($equalvalue=arraySearchByKey('equals_num_value', $qidattributes, 'attribute', 1))
	{
		$equals_num_value=$equalvalue['value'];
		$numbersonlyonblur[]='calculateValue'.$ia[1].'(3)';
		$calculateValue[]=3;
	}
	elseif ($equalvalue=arraySearchByKey('num_value_equals_sgqa', $qidattributes, 'attribute', 1))
	{
	    $equals_num_value=$_SESSION[$equalvalue['value']];
		$numbersonlyonblur[]='calculateValue'.$ia[1].'(3)';
		$calculateValue[]=3;
	}
	else
	{
		$equals_num_value[]=0;
	}

    //MIN VALUE
	if ($minvalue=arraySearchByKey('min_num_value', $qidattributes, 'attribute', 1))
	{
		$min_num_value=$minvalue['value'];
		$numbersonlyonblur[]='calculateValue'.$ia[1].'(2)';
		$calculateValue[]=2;
	}
	elseif ($minvalue=arraySearchByKey('min_num_value_sgqa', $qidattributes, 'attribute', 1))
	{
	    $min_num_value=$_SESSION[$minvalue['value']];
		$numbersonlyonblur[]='calculateValue'.$ia[1].'(2)';
		$calculateValue[]=2;
	}
	else
	{
		$min_num_value=0;
	}

    //MAX VALUE
	if ($maxvalue=arraySearchByKey('max_num_value', $qidattributes, 'attribute', 1))
	{
		$max_num_value = $maxvalue['value'];
		$numbersonlyonblur[]='calculateValue'.$ia[1].'(1)'; 
		$calculateValue[]=1;
	}
    elseif ($maxvalue=arraySearchByKey('max_num_value_sgqa', $qidattributes, 'attribute', 1))
    {
        $max_num_value = $_SESSION[$maxvalue['value']];	    
		$numbersonlyonblur[]='calculateValue'.$ia[1].'(1)'; 
		$calculateValue[]=1;
	}
	else
	{
		$max_num_value = 0;
	}

	if ($prefix=arraySearchByKey('prefix', $qidattributes, 'attribute', 1))
	{
		$prefix = $prefix['value'];
	}
	else
	{
		$prefix = '';
	}

	if ($suffix=arraySearchByKey('suffix', $qidattributes, 'attribute', 1))
	{
		$suffix = $suffix['value'];
	}
	else
	{
		$suffix = '';
	}

	if(!empty($numbersonlyonblur))
	{
		$numbersonly .= ' onblur="'.implode(';', $numbersonlyonblur).'"';
		$numbersonly_slider = implode(';', $numbersonlyonblur);
	}
	else
	{
		$numbersonly_slider = '';
	}
	if ($maxchars=arraySearchByKey('text_input_width', $qidattributes, 'attribute', 1))
	{
		$tiwidth=$maxchars['value'];
	}
	else
	{
		$tiwidth=10;
	}
	if (arraySearchByKey('slider_layout', $qidattributes, 'attribute', 1))
	{
		$slider_layout=true;

		$slider_accuracy=arraySearchByKey('slider_accuracy', $qidattributes, 'attribute', 1);
		if (isset($slider_accuracy['value']))
		{
			//$slider_divisor = 1 / $slider_accuracy['value'];
			$decimnumber = strlen($slider_accuracy['value']) - strpos($slider_accuracy['value'],'.') -1; 
			$slider_divisor = pow(10,$decimnumber);
			$slider_stepping = $slider_accuracy['value'] * $slider_divisor;
		//	error_log('acc='.$slider_accuracy['value']." div=$slider_divisor stepping=$slider_stepping");
		}
		else
		{
			$slider_divisor = 1;
			$slider_stepping = 1;
		}

		$slider_min=arraySearchByKey('slider_min', $qidattributes, 'attribute', 1);
		if (isset($slider_min['value']))
		{
			$slider_min = $slider_min['value'] * $slider_divisor;
		}
		else
		{
			$slider_min = 0;
		}
		$slider_max=arraySearchByKey('slider_max', $qidattributes, 'attribute', 1);
		if (isset($slider_max['value']))
		{
			$slider_max = $slider_max['value'] * $slider_divisor;
		}
		else
		{
			$slider_max = 100 * $slider_divisor;
		}
		$slider_default=arraySearchByKey('slider_default', $qidattributes, 'attribute', 1);
		if (isset($slider_default['value']))
		{
			$slider_default = $slider_default['value'];
		}
		else
		{
			$slider_default = '';
		}
	}
	else
	{
		$slider_layout = false;
	}
	if ($hidetip=arraySearchByKey('hide_tip', $qidattributes, 'attribute', 1))
	{
		$hidetip=$hidetip['value'];
	}
	elseif ($slider_layout === true) // auto hide tip when using sliders
	{
		$hidetip=1;
	}
	else
	{
		$hidetip=0;
	}
	if (arraySearchByKey('random_order', $qidattributes, 'attribute', 1))
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0]  AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
	}
	else
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0]  AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	}

	$ansresult = db_execute_assoc($ansquery);	//Checked
	$anscount = $ansresult->RecordCount()*2;
	//$answer .= "\t\t\t\t\t<input type='hidden' name='MULTI$ia[1]' value='$anscount'>\n";
	$fn = 1;
	$answer = keycontroljs();

	$answer_main = '';

	if ($anscount==0) 
	{
		$inputnames=array();
		$answer_main .= '	<li>'.$clang->gT('Error: This question has no answers.')."</li>\n";
	}
	else 
	{
		$label_width = 0;
		while ($ansrow = $ansresult->FetchRow())
		{
			$myfname = $ia[1].$ansrow['code'];
			$answer_main .= "\t<li>\n\t\t<label for=\"answer$myfname\">{$ansrow['answer']}</label>\n";
			if($label_width < strlen(trim(strip_tags($ansrow['answer']))))
			{
				$label_width = strlen(trim(strip_tags($ansrow['answer'])));
			}

			if ($slider_layout === false)
			{
				$answer_main .= "\t\t<div class=\"input\">\n\t\t\t".$prefix."\n\t\t\t<input class=\"text\" type=\"text\" size=\"".$tiwidth.'" name="'.$myfname.'" id="answer'.$myfname.'" value="';
				if (isset($_SESSION[$myfname]))
				{
					$answer_main .= $_SESSION[$myfname];
				}

				// --> START NEW FEATURE - SAVE
				$answer_main .= '" onchange="checkconditions(this.value, this.name, this.type);" '.$numbersonly.' maxlength="'.$maxsize."\" />\n\t\t\t".$suffix."\n\t\t</div>\n\t</li>\n";
				// --> END NEW FEATURE - SAVE
			}
			else
			{
				$js_header_includes[] = '/scripts/jquery/jquery-ui.js';
				$js_header_includes[] = '/scripts/jquery/lime-slider.js';
				$js_header_includes = array_unique($js_header_includes);

				if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] != '')
				{
					$slider_startvalue = $_SESSION[$myfname] * $slider_divisor;
				} 
				elseif ($slider_default != "")
				{
					$slider_startvalue = $slider_default * $slider_divisor;
				}
				else 
				{
					$slider_startvalue = 'NULL';
				}
				$answer_main .= "\t\t<div id='container-$myfname' class='multinum-slider'>\n"
					. "\t\t\t<input type=\"text\" id=\"slider-param-min-$myfname\" value=\"$slider_min\" style=\"display: none;\" />\n"
					. "\t\t\t<input type=\"text\" id=\"slider-param-max-$myfname\" value=\"$slider_max\" style=\"display: none;\" />\n"
					. "\t\t\t<input type=\"text\" id=\"slider-param-stepping-$myfname\" value=\"$slider_stepping\" style=\"display: none;\" />\n"
					. "\t\t\t<input type=\"text\" id=\"slider-param-divisor-$myfname\" value=\"$slider_divisor\" style=\"display: none;\" />\n"
					. "\t\t\t<input type=\"text\" id=\"slider-param-startvalue-$myfname\" value='$slider_startvalue' style=\"display: none;\" />\n"
					. "\t\t\t<input type=\"text\" id=\"slider-onchange-js-$myfname\" value=\"$numbersonly_slider\" style=\"display: none;\" />\n"
					. "\t\t\t<input type=\"text\" id=\"slider-prefix-$myfname\" value=\"$prefix\" style=\"display: none;\" />\n"
					. "\t\t\t<input type=\"text\" id=\"slider-suffix-$myfname\" value=\"$suffix\" style=\"display: none;\" />\n"
					. "\t\t\t<div id=\"slider-$myfname\" class=\"ui-slider-1\">\n"
					. "\t\t\t\t<div class=\"slider_callout\" id=\"slider-callout-$myfname\"></div>\n"
					. "\t\t\t\t<div class=\"ui-slider-handle\" id=\"slider-handle-$myfname\"></div>\n"
					. "\t\t\t</div>\n"
					. "\t\t</div>\n"
					. "\t\t<input class=\"text\" type=\"text\" name=\"$myfname\" id=\"answer$myfname\" style=\"display: none;\" value=\"";
				if (isset($_SESSION[$myfname]))
				{
					$answer_main .= $_SESSION[$myfname];
				}
				elseif ($slider_default != "")
				{
					$answer_main .= $slider_default;
				}
				$answer_main .= "\"/>\n"
					. "\t</li>\n";
			}

//			$answer .= "\t\t\t\t\t\t\t</tr>\n";

			$fn++;
			$inputnames[]=$myfname;
		}
		$question_tip = '';
		if($hidetip == 0) 
		{
			$question_tip .= '<p class="tip">'.$clang->gT('Only numbers may be entered in these fields')."</p>\n";
		}
		if ($maxvalue)
		{
			$question_tip .= '<p id="max_num_value_'.$ia[1].'" class="tip">'.sprintf($clang->gT('Total of all entries must not exceed %d'), $max_num_value)."</p>\n";
		}
		if ($equalvalue)
		{
			$question_tip .= '<p id="equals_num_value_'.$ia[1].'" class="tip">'.sprintf($clang->gT('Total of all entries must equal %d'),$equals_num_value)."</p>\n";
		}
		if ($minvalue)
		{
			$question_tip .= '<p id="min_num_value_'.$ia[1].'" class="tip">'.sprintf($clang->gT('Total of all entries must be at least %d'),$min_num_value)."</p>\n";
		}

		$label_width = round($label_width * 0.8);
		if($label_width < 2) $label_width = 2;
		switch($label_width / 2)
		{
			case 1: 
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
			case 8:
			case 9:
			case 10:
			case 11:
			case 12:
			case 13:
			case 14:
			case 15:	$label_width = $label_width;
					break;
			default:	++$label_width;
		}
		if ($maxvalue || $equalvalue || $minvalue)
		{
			$class_computed = ' computed';
			if($label_width > 20)
			{
				$label_width = 'X-large';
				$comp_width = $label_width;
			}
			else
			{
				$comp_width = $label_width;
				if( isset($prefix) && !empty($prefix) )
				{
					$comp_width = $comp_width + round(strlen($prefix) * 0.8);
				};
				if(isset($suffix) && !empty($suffix))
				{
					$comp_width = $comp_width + round(strlen($suffix) * 0.8);
				}
				if($comp_width > 20)
				{
					$comp_width = 'X-large';
				}
				else
				{
					$comp_width = 'X'.$comp_width;
				}
			}
		}
		else
		{
			if($label_width > 30)
			{
				$label_width = 'X-large';
			}
			$class_computed = '';
		}

		if ($slider_layout)
		{
			$label_width = 'slider';
			$comp_width = $label_width;
		}
		else
		{
			if($label_width != 'X-large')
			{
				$label_width = 'X'.$label_width;
			}
		}
		$answer .= $question_tip.'<ul class="'.$label_width.$class_computed."\">\n".$answer_main."</ul>\n";

		if ($maxvalue || $equalvalue || $minvalue)
		{
			$answer_computed  = "\n<dl class=\"multiplenumerichelp $comp_width\">\n";
			$answer_computed .= "\t<dt>".$clang->gT('Total: ')."</dt>\n\t\t<dd>$prefix<input type=\"text\" id=\"totalvalue_{$ia[1]}\" disabled=\"disabled\" />$suffix</dd>\n";
			if ($equalvalue)
			{
				$answer_computed .= "\n\t<dt>".$clang->gT('Remaining: ')."</dt>\n\t\t<dd>$prefix<input type='text' id=\"remainingvalue_{$ia[1]}\" disabled=\"disabled\" />$suffix</dd>\n";
			}
			$answer_computed .= "</dl>\n";
		}
	}
	$answer_computed = isset($answer_computed)?$answer_computed:'';
	$answer .= $answer_computed;
//just added these here so its easy to change in one place
	$errorClass = 'tip error';
	$goodClass = ' tip good';
/* ==================================
Style to be applied to all templates.
.numeric-multi p.tip.error
{
	color: #f00;
}
.numeric-multi p.tip.good
{
	color: #0f0;
}
*/
	if ($maxvalue || $equalvalue || $minvalue) 
	{ //Do value validation
		$answer .= '<input type="hidden" name="qattribute_answer[]" value="'.$ia[1]."\" />\n";
		$answer .= '<input type="hidden" name="qattribute_answer'.$ia[1]."\" />\n";

		$answer .= "<script type='text/javascript'>\n";
		$answer .= "    function calculateValue".$ia[1]."(method) {\n";
		//Make all empty fields 0 (or else calculation won't work
		foreach ($inputnames as $inputname)
		{
			$answer .= "       if(document.limesurvey.answer".$inputname.".value == '') { document.limesurvey.answer".$inputname.".value = 0; }\n";
			$javainputnames[]="parseInt(parseFloat(document.limesurvey.answer".$inputname.".value)*1000)"; 
		}
		$answer .= "       bob = eval('document.limesurvey.qattribute_answer".$ia[1]."');\n";
		$answer .= "       totalvalue_".$ia[1]."=(";
		$answer .= implode(" + ", $javainputnames);
		$answer .= ")/1000;\n";
		$answer .= "       document.getElementById('totalvalue_{$ia[1]}').value=parseFloat(totalvalue_{$ia[1]});\n";
		$answer .= "       var ua = navigator.appVersion.indexOf('MSIE');\n";
		$answer .= "       var ieAtt = ua != -1 ? 'className' : 'class';\n";
		$answer .= "       switch(method)\n";
		$answer .= "       {\n";
		$answer .= "       case 1:\n";
		$answer .= "          if (totalvalue_".$ia[1]." > $max_num_value)\n";
		$answer .= "             {\n";
		$answer .= "               bob.value = '".$clang->gT("Answer is invalid. The total of all entries should not add up to more than ").$max_num_value."';\n";
//		$answer .= "               document.getElementById('totalvalue_{$ia[1]}').style.color='red';\n";
//		$answer .= "               document.getElementById('max_num_value_{$ia[1]}').style.color='red';\n";
		$answer .= "               document.getElementById('totalvalue_{$ia[1]}').setAttribute(ieAtt,'" . $errorClass . "');\n";
		$answer .= "               document.getElementById('max_num_value_{$ia[1]}').setAttribute(ieAtt,'" . $errorClass . "');\n";
		$answer .= "             }\n";
		$answer .= "             else\n";
		$answer .= "             {\n";
		$answer .= "               if (bob.value == '' || bob.value == '".$clang->gT("Answer is invalid. The total of all entries should not add up to more than ").$max_num_value."')\n";
		$answer .= "               {\n";
		$answer .= "                 bob.value = '';\n";
//		$answer .= "                 document.getElementById('totalvalue_{$ia[1]}').style.color='black';\n";
		$answer .= "                 document.getElementById('totalvalue_{$ia[1]}').setAttribute(ieAtt,'" . $goodClass . "');\n";
		$answer .= "               }\n";
//		$answer .= "               document.getElementById('max_num_value_{$ia[1]}').style.color='black';\n";
		$answer .= "               document.getElementById('max_num_value_{$ia[1]}').setAttribute(ieAtt,'" . $goodClass . "');\n";
		$answer .= "             }\n";
		$answer .= "          break;\n";
		$answer .= "       case 2:\n";
		$answer .= "          if (totalvalue_".$ia[1]." < $min_num_value)\n";
		$answer .= "             {\n";
		$answer .= "               bob.value = '".$clang->gT("Answer is invalid. The total of all entries should add up to at least ").$min_num_value."';\n";
//		$answer .= "               document.getElementById('totalvalue_".$ia[1]."').style.color='red';\n";
//		$answer .= "               document.getElementById('min_num_value_".$ia[1]."').style.color='red';\n";
		$answer .= "               document.getElementById('totalvalue_".$ia[1]."').setAttribute(ieAtt,'" . $errorClass . "');\n";
		$answer .= "               document.getElementById('min_num_value_".$ia[1]."').setAttribute(ieAtt,'" . $errorClass . "');\n";
		$answer .= "             }\n";
		$answer .= "             else\n";
		$answer .= "             {\n";
		$answer .= "               if (bob.value == '' || bob.value == '".$clang->gT("Answer is invalid. The total of all entries should add up to at least ").$min_num_value."')\n";
		$answer .= "               {\n";
		$answer .= "                 bob.value = '';\n";
//		$answer .= "                 document.getElementById('totalvalue_".$ia[1]."').style.color='black';\n";
		$answer .= "                 document.getElementById('totalvalue_".$ia[1]."').setAttribute(ieAtt,'" . $goodClass . "');\n";
		$answer .= "               }\n";
//		$answer .= "               document.getElementById('min_num_value_".$ia[1]."').style.color='black';\n";
		$answer .= "               document.getElementById('min_num_value_".$ia[1]."').setAttribute(ieAtt,'" . $goodClass . "');\n";
		$answer .= "             }\n";
		$answer .= "          break;\n";
		$answer .= "       case 3:\n";
		$answer .= "          remainingvalue = (parseInt(parseFloat($equals_num_value)*1000) - parseInt(parseFloat(totalvalue_".$ia[1].")*1000))/1000;\n";
		$answer .= "          document.getElementById('remainingvalue_".$ia[1]."').value=remainingvalue;\n";
		$answer .= "          if (totalvalue_".$ia[1]." == $equals_num_value)\n";
		$answer .= "             {\n";
		$answer .= "               if (bob.value == '' || bob.value == '".$clang->gT("Answer is invalid. The total of all entries should not add up to more than ").$equals_num_value."')\n";
		$answer .= "               {\n";
		$answer .= "                 bob.value = '';\n";
//		$answer .= "                 document.getElementById('totalvalue_".$ia[1]."').style.color='black';\n";
//		$answer .= "                 document.getElementById('equals_num_value_".$ia[1]."').style.color='black';\n";
		$answer .= "                 document.getElementById('totalvalue_".$ia[1]."').setAttribute(ieAtt,'" . $goodClass . "');\n";
		$answer .= "                 document.getElementById('equals_num_value_".$ia[1]."').setAttribute(ieAtt,'" . $goodClass . "');\n";
		$answer .= "               }\n";
		$answer .= "             }\n";
		$answer .= "             else\n";
		$answer .= "             {\n";
		$answer .= "             bob.value = '".$clang->gT("Answer is invalid. The total of all entries should not add up to more than ").$equals_num_value."';\n";
//		$answer .= "             document.getElementById('totalvalue_".$ia[1]."').style.color='red';\n";
//		$answer .= "             document.getElementById('equals_num_value_".$ia[1]."').style.color='red';\n";
		$answer .= "             document.getElementById('totalvalue_".$ia[1]."').setAttribute(ieAtt,'" . $errorClass . "');\n";
		$answer .= "             document.getElementById('equals_num_value_".$ia[1]."').setAttribute(ieAtt,'" . $errorClass . "');\n";
		$answer .= "             }\n";
		$answer .= "             break;\n";
		$answer .= "       }\n";
		$answer .= "    }\n";
		foreach($calculateValue as $cValue) 
		{
			$answer .= "    calculateValue".$ia[1]."($cValue);\n";
		}
		$answer .= "</script>\n";

	}

	return array($answer, $inputnames);
}





// ---------------------------------------------------------------
function do_numerical($ia)
{
	global $clang;
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($prefix=arraySearchByKey("prefix", $qidattributes, "attribute", 1))
	{
		$prefix = $prefix['value'];
	}
	else
	{
		$prefix = '';
	}
	if ($suffix=arraySearchByKey('suffix', $qidattributes, 'attribute', 1))
	{
		$suffix = $suffix['value'];
	}
	else 
	{
		$suffix = '';
	}
	if ($maxchars=arraySearchByKey('maximum_chars', $qidattributes, 'attribute', 1))
	{
		$maxsize=$maxchars['value'];
		if ($maxsize>20)
		{
			$maxsize=20;
		}
	}
	else
	{
		$maxsize=20;  // The field length for numerical fields is 20
	}
	if ($maxchars=arraySearchByKey('text_input_width', $qidattributes, 'attribute', 1))
	{
		$tiwidth=$maxchars['value'];
	}
	else
	{
		$tiwidth=10;
	}
	// --> START NEW FEATURE - SAVE
	$answer = keycontroljs()
	. "<p class=\"question\">\n\t$prefix\n\t<input class=\"text\" type=\"text\" size=\"$tiwidth\" name=\"$ia[1]\" "
	. "id=\"answer{$ia[1]}\" value=\"{$_SESSION[$ia[1]]}\" onkeypress=\"return goodchars(event,'0123456789.')\" onkeyup='checkconditions(this.value, this.name, this.type)'"
	. "maxlength=\"$maxsize\" />\n\t$suffix\n</p>\n"
	. "<p class=\"tip\">".$clang->gT('Only numbers may be entered in this field')."</p>\n";
	// --> END NEW FEATURE - SAVE

	$inputnames[]=$ia[1];
	$mandatory=null;
	return array($answer, $inputnames, $mandatory);
}




// ---------------------------------------------------------------
function do_shortfreetext($ia)
{
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($maxchars=arraySearchByKey('maximum_chars', $qidattributes, 'attribute', 1))
	{
		$maxsize=$maxchars['value'];
	}
	else
	{
		$maxsize=255;
	}
	if ($maxchars=arraySearchByKey('text_input_width', $qidattributes, 'attribute', 1))
	{
		$tiwidth=$maxchars['value'];
	}
	else
	{
		$tiwidth=50;
	}
	if ($prefix=arraySearchByKey('prefix', $qidattributes, 'attribute', 1))
	{
		$prefix = $prefix['value'];
	}
	else 
	{
		$prefix = '';
	}
	if ($suffix=arraySearchByKey('suffix', $qidattributes, 'attribute', 1))
	{
		$suffix = $suffix['value'];
	}
	else
	{
		$suffix = '';
	}
	// --> START NEW FEATURE - SAVE
	$answer = "<p class=\"question\">\n\t$prefix\n\t<input class=\"text\" type=\"text\" size=\"$tiwidth\" name=\"$ia[1]\" id=\"answer$ia[1]\" value=\""
	.str_replace ("\"", "'", str_replace("\\", "", $_SESSION[$ia[1]]))
	."\" maxlength=\"$maxsize\" onkeyup=\"checkconditions(this.value, this.name, this.type)\" />\n\t$suffix\n</p>\n";
	// --> END NEW FEATURE - SAVE

	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_longfreetext($ia)
{
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($maxchars=arraySearchByKey('maximum_chars', $qidattributes, 'attribute', 1))
	{
		$maxsize=$maxchars['value'];
	}
	else
	{
		$maxsize=65525;
	}

	// --> START ENHANCEMENT - DISPLAY ROWS
	if ($displayrows=arraySearchByKey('display_rows', $qidattributes, 'attribute', 1))
	{
		$drows=$displayrows['value'];
	}
	else
	{
		$drows=5;
	}
	// <-- END ENHANCEMENT - DISPLAY ROWS

	// --> START ENHANCEMENT - TEXT INPUT WIDTH
	if ($maxchars=arraySearchByKey('text_input_width', $qidattributes, 'attribute', 1))
	{
		$tiwidth=$maxchars['value'];
	}
	else
	{
		$tiwidth=40;
	}
	// <-- END ENHANCEMENT - TEXT INPUT WIDTH


	$answer = "<script type='text/javascript'>
               <!--
               function textLimit(field, maxlen) {
                if (document.getElementById(field).value.length > maxlen)
                document.getElementById(field).value = document.getElementById(field).value.substring(0, maxlen);
                }
               //-->
               </script>\n";

	// --> START ENHANCEMENT - DISPLAY ROWS
	// --> START ENHANCEMENT - TEXT INPUT WIDTH

	// --> START NEW FEATURE - SAVE
	$answer .= '<textarea class="textarea" name="'.$ia[1].'" id="answer'.$ia[1].'" '
	.'rows="'.$drows.'" cols="'.$tiwidth.'" onkeyup="textLimit(\'answer'.$ia[1].'\', '.$maxsize.'); checkconditions(this.value, this.name, this.type)">';
	// --> END NEW FEATURE - SAVE

	// <-- END ENHANCEMENT - TEXT INPUT WIDTH
	// <-- END ENHANCEMENT - DISPLAY ROWS

	if ($_SESSION[$ia[1]]) {$answer .= str_replace("\\", "", $_SESSION[$ia[1]]);}

	$answer .= "</textarea>\n";

	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_hugefreetext($ia)
{
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($maxchars=arraySearchByKey('maximum_chars', $qidattributes, 'attribute', 1))
	{
		$maxsize=$maxchars['value'];
	}
	else
	{
		$maxsize=65525;
	}

	// --> START ENHANCEMENT - DISPLAY ROWS
	if ($displayrows=arraySearchByKey('display_rows', $qidattributes, 'attribute', 1))
	{
		$drows=$displayrows['value'];
	}
	else
	{
		$drows=30;
	}
	// <-- END ENHANCEMENT - DISPLAY ROWS

	// --> START ENHANCEMENT - TEXT INPUT WIDTH
	if ($maxchars=arraySearchByKey('text_input_width', $qidattributes, 'attribute', 1))
	{
		$tiwidth=$maxchars['value'];
	}
	else
	{
		$tiwidth=70;
	}
	// <-- END ENHANCEMENT - TEXT INPUT WIDTH

	$answer = "<script type='text/javascript'>
               <!--
               function textLimit(field, maxlen) {
                if (document.getElementById(field).value.length > maxlen)
                document.getElementById(field).value = document.getElementById(field).value.substring(0, maxlen);
                }
               //-->
               </script>\n";
	// --> START ENHANCEMENT - DISPLAY ROWS
	// --> START ENHANCEMENT - TEXT INPUT WIDTH

	// --> START NEW FEATURE - SAVE
	$answer .= '<textarea class="display" name="'.$ia[1].'" id="answer'.$ia[1].'" '
	.'rows="'.$drows.'" cols="'.$tiwidth.'" onkeyup="textLimit(\'answer'.$ia[1].'\', '.$maxsize.'); checkconditions(this.value, this.name, this.type)">';
	// --> END NEW FEATURE - SAVE

	// <-- END ENHANCEMENT - TEXT INPUT WIDTH
	// <-- END ENHANCEMENT - DISPLAY ROWS

	if ($_SESSION[$ia[1]]) {$answer .= str_replace("\\", "", $_SESSION[$ia[1]]);}

	$answer .= "</textarea>\n";
	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_yesno($ia)
{
	global $shownoanswer, $clang;
	$answer = "<ul>\n"
	. "\t<li>\n\t\t<input class=\"radio\" type=\"radio\" name=\"{$ia[1]}\" id=\"answer{$ia[1]}Y\" value=\"Y\"";

	if ($_SESSION[$ia[1]] == 'Y')
	{
		$answer .= CHECKED;
	}
	// --> START NEW FEATURE - SAVE
	$answer .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t<label for=\"answer{$ia[1]}Y\" class=\"answertext\">\n\t\t\t".$clang->gT('Yes')."\n\t\t</label>\n\t</li>\n"
	. "\t<li>\n\t\t<input class=\"radio\" type=\"radio\" name=\"{$ia[1]}\" id=\"answer{$ia[1]}N\" value=\"N\"";
	// --> END NEW FEATURE - SAVE

	if ($_SESSION[$ia[1]] == 'N')
	{
		$answer .= CHECKED;
	}
	// --> START NEW FEATURE - SAVE
	$answer .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t<label for=\"answer{$ia[1]}N\" class=\"answertext\" >\n\t\t\t".$clang->gT('No')."\n\t\t</label>\n\t</li>\n";
	// --> END NEW FEATURE - SAVE

	if ($ia[6] != 'Y' && $shownoanswer == 1)
	{
		$answer .= "\t<li>\n\t\t<input class=\"radio\" type=\"radio\" name=\"{$ia[1]}\" id=\"answer{$ia[1]}\" value=\"\"";
		if ($_SESSION[$ia[1]] == '')
		{
			$answer .= CHECKED;
		}
		// --> START NEW FEATURE - SAVE
		$answer .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t<label for=\"answer{$ia[1]}\" class=\"answertext\">\n\t\t\t".$clang->gT('No answer')."\n\t\t</label>\n\t</li>\n";
		// --> END NEW FEATURE - SAVE
	}

	$answer .= "</ul>\n\n<input type=\"hidden\" name=\"java{$ia[1]}\" id=\"java{$ia[1]}\" value=\"{$_SESSION[$ia[1]]}\" />\n";
	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_gender($ia)
{
	global $shownoanswer, $clang;
	
	$qidattributes=getQuestionAttributes($ia[0]);

/* This can (and should) now be done by CSS rather than using tables.
	if ($displaycols=arraySearchByKey('display_columns', $qidattributes, 'attribute', 1))
	{
		$dcols=$displaycols['value'];
	}
	else
	{
		$dcols=0;
	}
*/
	$answer = "<ul>\n"
	. "\t<li>\n"
	. '		<input class="radio" type="radio" name="'.$ia[1].'" id="answer'.$ia[1].'F" value="F"';
	if ($_SESSION[$ia[1]] == 'F')
	{
		$answer .= CHECKED;
	}
	// --> START NEW FEATURE - SAVE
	$answer .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n"
	. '		<label for="answer'.$ia[1].'F" class="answertext">'.$clang->gT('Female')."</label>\n\t</li>\n";
/* columns now done by CSS
	if ($dcols > 1 ) //Break into columns - don't need any detailed calculations becauase there's only ever 2 possible columns
	{
		$answer .= "\n</td><td>\n";
	}
	else
	{
		$answer .= "<br />\n";
	}
*/
	$answer .= "\t<li>\n\t\t<input class=\"radio\" type=\"radio\" name=\"$ia[1]\" id=\"answer".$ia[1].'M" value="M"';
	// --> END NEW FEATURE - SAVE

	if ($_SESSION[$ia[1]] == 'M')
	{
		$answer .= CHECKED;
	}
	// --> START NEW FEATURE - SAVE
	$answer .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t<label for=\"answer$ia[1]M\" class=\"answertext\">".$clang->gT('Male')."</label>\n\t</li>\n";
	// --> END NEW FEATURE - SAVE

	if ($ia[6] != 'Y' && $shownoanswer == 1)
	{
/* columns now done by CSS
		if ($dcols > 2)
		{
			$answer .= "\n</td><td>\n";
		}
		elseif ($dcols > 1)
		{
			$answer .= "\n</td></tr><tr><td colspan='2' align='center'>\n";
		}
		else
		{
			$answer .= "<br />";
		}
*/
		$answer .= "\t<li>\n\t\t<input class=\"radio\" type=\"radio\" name=\"$ia[1]\" id=\"answer".$ia[1].'" value=""';
		if ($_SESSION[$ia[1]] == '')
		{
			$answer .= CHECKED;
		}
		// --> START NEW FEATURE - SAVE
		$answer .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t<label for=\"answer$ia[1]\" class=\"answertext\">".$clang->gT('No answer')."</label>\n\t</li>\n";
		// --> END NEW FEATURE - SAVE

	}
	$answer .= "</ul>\n\n<input type=\"hidden\" name=\"java$ia[1]\" id=\"java$ia[1]\" value=\"{$_SESSION[$ia[1]]}\" />\n";

	$inputnames[]=$ia[1];
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_array_5point($ia)
{
	global $dbprefix, $shownoanswer, $notanswered, $thissurvey, $clang;
	
	
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($answerwidth=arraySearchByKey('answer_width', $qidattributes, 'attribute', 1))
	{
		$answerwidth=$answerwidth['value'];
	}
	else
	{
		$answerwidth = 20;
	}
	$cellwidth  = 5; // number of columns
	if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory
	{
		++$cellwidth; // add another column
	}
	$cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

	$ansquery = "SELECT answer FROM {$dbprefix}answers WHERE qid=".$ia[0]." AND answer like '%|%'";
	$ansresult = db_execute_assoc($ansquery);   //Checked
	if ($ansresult->RecordCount()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;} 
	// $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
	

	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
	}
	else
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	}
	$ansresult = db_execute_assoc($ansquery);     //Checked
	$anscount = $ansresult->RecordCount();

	$fn = 1;
	$answer = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - a five point Likert scale array\">\n\n"
	. "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n"
	. "\t<colgroup class=\"col-responses\">\n";
	$odd_even = '';
	for ($xc=1; $xc<=5; $xc++)
	{
		$odd_even = alternation($odd_even);
		$answer .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
	}
	if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory
	{
		$odd_even = alternation($odd_even);
		$answer .= "\t\t<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
	}
	$answer .= "\t</colgroup>\n\n"
	. "\t<thead>\n\t\t<tr class=\"array1\">\n"
	. "\t\t\t<td>&nbsp;</td>\n";
	for ($xc=1; $xc<=5; $xc++)
	{
		$answer .= "\t\t\t<th>$xc</th>\n";
	}
	if ($right_exists) {$answer .= "\t\t\t<td width='$answerwidth%'>&nbsp;</td>\n";} 
	if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory
	{
		$answer .= "\t\t\t<th>".$clang->gT('No answer')."</th>\n";
	}
	$answer .= "\t\t</tr>\n";
	
	$answer_t_content = '';
	$trbc = '';
	while ($ansrow = $ansresult->FetchRow())
	{
		$myfname = $ia[1].$ansrow['code'];

		$answertext=answer_replace($ansrow['answer']);
		if (strpos($answertext,'|')) {$answertext=substr($answertext,0,strpos($answertext,'|'));}

		/* Check if this item has not been answered: the 'notanswered' variable must be an array,
		containing a list of unanswered questions, the current question must be in the array,
		and there must be no answer available for the item in this session. */
		if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && ($_SESSION[$myfname] == '') ) {
			$answertext = "<span class=\"errormandatory\">{$answertext}</span>";
		}

		$trbc = alternation($trbc , 'row');
		$htmltbody2 = '';
		if (($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == false)  || ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'A'))
		{
			$htmltbody2 = "\t</thead>\n\n\t<tbody id=\"javatbd$myfname\" style=\"display: none\">\n\t\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"off\" />\n";
		}
		elseif (($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'S') || ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == true))
		{
			$selected = getArrayFiltersForQuestion($ia[0]);
			if (!in_array($ansrow['code'],$selected))
			{
				$htmltbody2 = "\t</thead>\n\n\t<tbody id=\"javatbd$myfname\" style=\"display: none\">\n\t\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"off\" />\n";
				$_SESSION[$myfname] = '';
			}
			else
			{
				$htmltbody2 = "\t</thead>\n\n\t<tbody id=\"javatbd$myfname\" style=\"display: \">\n\t\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"on\" />\n";
			}
		}
		else
		{
			$htmltbody2 = "\t</thead>\n\n\t<tbody id=\"javatbd$myfname\" style=\"display: \">\n\t\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"on\" />\n";
		}
		$answer_t_content .= "\t\t<tr class=\"$trbc\">\n"
		. "\t\t\t<th class=\"answertext\" width=\"$answerwidth%\">\n\t\t\t\t$answertext\n"
		. "\t\t\t\t<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
		if (isset($_SESSION[$myfname]))
		{
			$answer_t_content .= $_SESSION[$myfname];
		}
		$answer_t_content .= "\" />\n\t\t\t</th>\n";
		for ($i=1; $i<=5; $i++)
		{
			$answer_t_content .= "\t\t\t<td>\n\t\t\t\t<label for=\"answer$myfname-$i\">"
			."\n\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-$i\" value=\"$i\" title=\"$i\"";
			if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $i)
			{
				$answer_t_content .= CHECKED;
			}
			$answer_t_content .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t\t\t</label>\n\t\t\t</td>\n";
		}

		$answertext2=answer_replace($ansrow['answer']);
		if (strpos($answertext2,'|')) 
		{
			$answertext2=substr($answertext2,strpos($answertext2,'|')+1);
			$answer_t_content .= "\t\t\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">$answertext2</td>\n";
		}
		elseif ($right_exists)
		{
			$answer_t_content .= "\t\t\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">&nbsp;</td>\n";
		}

		
		if ($ia[6] != 'Y' && $shownoanswer == 1)
		{
			$answer_t_content .= "\t\t\t<td>\n\t\t\t\t<label for=\"answer$myfname-\">"
			."\n\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer').'"';
			if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == '')
			{
				$answer_t_content .= CHECKED;
			}
			$answer_t_content .= " onclick='checkconditions(this.value, this.name, this.type)'  />\n\t\t\t\t</label>\n\t\t\t</td>\n";
		}

		$answer_t_content .= "\t\t</tr>\n\n";
		$fn++;
		$inputnames[]=$myfname;
	}

	$answer .= $htmltbody2 . $answer_t_content . "\t\t\t</table>\n";
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_array_10point($ia)
{
	global $dbprefix, $shownoanswer, $notanswered, $thissurvey, $clang;
	$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]."  AND language='".$_SESSION['s_lang']."'";
	$qresult = db_execute_assoc($qquery);      //Checked
	while($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}

	$qidattributes=getQuestionAttributes($ia[0]);
	if ($answerwidth=arraySearchByKey("answer_width", $qidattributes, "attribute", 1))
	{
		$answerwidth = $answerwidth['value'];
	}
	else
	{
		$answerwidth = 20;
	}
	$cellwidth  = 10; // number of columns
	if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory
	{
		++$cellwidth; // add another column
	}
	$cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1))
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
	}
	else
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	}
	$ansresult = db_execute_assoc($ansquery);   //Checked
	$anscount = $ansresult->RecordCount();

	$fn = 1;
	$answer = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - a ten point Likert scale array\">\n\n"
	. "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n"
	. "\t<colgroup class=\"col-responses\">\n";

	$odd_even = '';
	for ($xc=1; $xc<=10; $xc++)
	{
		$odd_even = alternation($odd_even);
		$answer .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
	}
	if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory
	{
		$odd_even = alternation($odd_even);
		$answer .= "\t\t<col class=\"col-no-answer $odd_even\" width=\"$cellwidth$\" />\n";
	}
	$answer .= "\t</colgroup>\n\n"
	. "\t<thead>\n\t\t<tr class=\"array1\">\n"
	. "\t\t\t<td>&nbsp;</td>\n";
	for ($xc=1; $xc<=10; $xc++)
	{
		$answer .= "\t\t\t<th>$xc</th>\n";
	}
	if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory
	{
		$answer .= "\t\t\t\t\t<th>".$clang->gT('No answer')."</th>\n";
	}
	$answer .= "\t\t</tr>\n";
	$answer_t_content = '';
	$trbc = '';
	while ($ansrow = $ansresult->FetchRow())
	{
		$myfname = $ia[1].$ansrow['code'];
		$answertext=answer_replace($ansrow['answer']);
		/* Check if this item has not been answered: the 'notanswered' variable must be an array,
		containing a list of unanswered questions, the current question must be in the array,
		and there must be no answer available for the item in this session. */
		if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && ($_SESSION[$myfname] == "") ) {
			$answertext = "<span class='errormandatory'>{$answertext}</span>";
		}
		$trbc = alternation($trbc , 'row');
		$htmltbody2 = "";
		if ($htmltbody=arraySearchByKey("array_filter", $qidattributes, "attribute", 1) && $thissurvey['format'] == "G" && getArrayFiltersOutGroup($ia[0]) == false)
		{
			$htmltbody2 = "\t</thead>\n\n\t<tbody id='javatbd$myfname' style='display: none'>\n\t\t<input type='hidden' name='tbdisp$myfname' id='tbdisp$myfname' value='off' />";
		} else if (($htmltbody=arraySearchByKey("array_filter", $qidattributes, "attribute", 1) && $thissurvey['format'] == "S") || ($htmltbody=arraySearchByKey("array_filter", $qidattributes, "attribute", 1) && $thissurvey['format'] == "G" && getArrayFiltersOutGroup($ia[0]) == true))
		{
			$selected = getArrayFiltersForQuestion($ia[0]);
			if (!in_array($ansrow['code'],$selected))
			{
				$htmltbody2 = "\t</thead>\n\n\t<tbody id='javatbd$myfname' style='display: none'>\n\t\t<input type='hidden' name='tbdisp$myfname' id='tbdisp$myfname' value='off' />";
				$_SESSION[$myfname] = "";
			}
			else
			{
				$htmltbody2 = "\t</thead>\n\n\t<tbody id='javatbd$myfname' style='display: '>\n\t\t<input type='hidden' name='tbdisp$myfname' id='tbdisp$myfname' value='on' />\n";
			}
		}
		else
		{
			$htmltbody2 = "\t</thead>\n\n\t<tbody id='javatbd$myfname' style='display: '>\n\t\t<input type='hidden' name='tbdisp$myfname' id='tbdisp$myfname' value='on' />\n";
		}
		$answer_t_content .= "\t\t<tr class=\"$trbc\">\n"
		. "\t\t\t<th class=\"answertext\">\n\t\t\t\t$answertext\n"
		. "\t\t\t\t<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
		if (isset($_SESSION[$myfname]))
		{
			$answer_t_content .= $_SESSION[$myfname];
		}
		$answer_t_content .= "\" />\n\t\t\t</th>\n";

		for ($i=1; $i<=10; $i++)
		{
			$answer_t_content .= "\t\t\t<td>\n\t\t\t\t<label for=\"answer$myfname-$i\">\n"
			."\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-$i\" value=\"$i\" title=\"$i\"";
			if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $i)
			{
				$answer_t_content .= CHECKED;
			}
			// --> START NEW FEATURE - SAVE
			$answer_t_content .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t\t\t</label>\n\t\t\t</td>\n";
			// --> END NEW FEATURE - SAVE

		}
		if ($ia[6] != "Y" && $shownoanswer == 1)
		{
			$answer_t_content .= "\t\t\t<td>\n\t\t\t\t<label for=\"answer$myfname-\">\n"
			."\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer')."\"";
			if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == '')
			{
				$answer_t_content .= CHECKED;
			}
			$answer_t_content .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t\t\t</label>\n\t\t\t</td>\n";

		}
		$answer_t_content .= "\t\t</tr>\n";
		$inputnames[]=$myfname;
		$fn++;
	}
	$answer .= $htmltbody2 . $answer_t_content . "\t</tbody>\n</table>\n";
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_array_yesnouncertain($ia)
{
	global $dbprefix, $shownoanswer, $notanswered, $thissurvey, $clang;
	$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
	$qresult = db_execute_assoc($qquery);	//Checked
	while($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($answerwidth=arraySearchByKey('answer_width', $qidattributes, 'attribute', 1))
	{
		$answerwidth=$answerwidth['value'];
	}
	else
	{
		$answerwidth = 20;
	}
	$cellwidth  = 3; // number of columns
	if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory
	{
		++$cellwidth; // add another column
	}
	$cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

	if (arraySearchByKey("random_order", $qidattributes, "attribute", 1)) {
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
	}
	else
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	}
	$ansresult = db_execute_assoc($ansquery);	//Checked
	$anscount = $ansresult->RecordCount();
	$fn = 1;
	$answer = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - a Yes/No/uncertain Likert scale array\">\n"
	. "\n\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n"
	. "\t<colgroup class=\"col-responses\">\n";
	$odd_even = '';
	for ($xc=1; $xc<=3; $xc++)
	{
		$odd_even = alternation($odd_even);
		$answer .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
	}
	if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory
	{
		$odd_even = alternation($odd_even);
		$answer .= "\t\t<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
	}
	$answer .= "\t</colgroup>\n\n"
	. "\t<thead>\n\t\t<tr class=\"array1\">\n"
	. "\t\t\t<td>&nbsp;</td>\n"
	. "\t\t\t<th>".$clang->gT('Yes')."</th>\n"
	. "\t\t\t<th>".$clang->gT('Uncertain')."</th>\n"
	. "\t\t\t<th>".$clang->gT('No')."</th>\n";
	if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory
	{
		$answer .= "\t\t\t<th>".$clang->gT('No answer')."</th>\n";
	}
	$answer .= "\t\t</tr>\n";
	
	$answer_t_content = '';
	$htmltbody2 = '';
	if ($anscount==0) 
	{
		$inputnames=array();
		$answer.="\t\t<tr>\t\t\t<th class=\"answertext\">".$clang->gT('Error: This question has no answers.')."</th>\n\t\t</tr>\n";
	}
	else
	{
		$trbc = '';
		while ($ansrow = $ansresult->FetchRow())
		{
			$myfname = $ia[1].$ansrow['code'];
			$answertext=answer_replace($ansrow['answer']);
			/* Check if this item has not been answered: the 'notanswered' variable must be an array,
			containing a list of unanswered questions, the current question must be in the array,
			and there must be no answer available for the item in this session. */
			if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && ($_SESSION[$myfname] == '') ) {
				$answertext = "<span class='errormandatory'>{$answertext}</span>";
			}
			$trbc = alternation($trbc , 'row');
			if ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == false)
			{
				$htmltbody2 = "\t</thead>\n\n\t<tbody id=\"javatbd$myfname\" style=\"display: none\">\n\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"off\" />\n";
			} else if (($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'S') || ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == true))
			{
				$selected = getArrayFiltersForQuestion($ia[0]);
				if (!in_array($ansrow['code'],$selected))
				{
					$htmltbody2 = "\t</thead>\n\n\t<tbody id=\"javatbd$myfname\" style=\"display: none\">\n\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"off\" />\n";
					$_SESSION[$myfname] = '';
				}
				else
				{
					$htmltbody2 = "\t</thead>\n\n\t<tbody id=\"javatbd$myfname\" style=\"display: \">\n\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"on\" />\n";
				}
			}
			else
			{
				$htmltbody2 = "\t</thead>\n\n\t<tbody id=\"javatbd$myfname\" style=\"display: \">\n\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"on\" />\n";
			}
			$answer_t_content .= "\t\t<tr class=\"$trbc\">\n"
			. "\t\t\t<th class=\"answertext\">$answertext</th>\n"
			. "\t\t\t<td>\n\t\t\t\t<label for=\"answer$myfname-Y\">\n"
			. "\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-Y\" value=\"Y\" title=\"".$clang->gT('Yes').'"';
			if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == 'Y')
			{
				$answer_t_content .= CHECKED;
			}
			// --> START NEW FEATURE - SAVE
			$answer_t_content .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t\t</label>\n\t\t\t</td>\n"
			. "\t\t\t<td>\n\t\t\t\t<label for=\"answer$myfname-U\">\n"
			. "\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-U\" value=\"U\" title=\"".$clang->gT('Uncertain')."\"";
			// --> END NEW FEATURE - SAVE
	
			if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == 'U')
			{
				$answer_t_content .= CHECKED;
			}
			// --> START NEW FEATURE - SAVE
			$answer_t_content .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t\t\t</label>\n\t\t\t</td>\n"
			. "\t\t\t<td>\n\t\t\t\t<label for=\"answer$myfname-N\">\n"
			. "\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-N\" value=\"N\" title=\"".$clang->gT('No').'"';
			// --> END NEW FEATURE - SAVE
	
			if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == 'N')
			{
				$answer_t_content .= CHECKED;
			}
			// --> START NEW FEATURE - SAVE
			$answer_t_content .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t\t\t</label>\n"
			. "\t\t\t\t<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
			// --> END NEW FEATURE - SAVE
			if (isset($_SESSION[$myfname]))
			{
				$answer_t_content .= $_SESSION[$myfname];
			}
			$answer_t_content .= "\" />\n\t\t\t</td>\n";

			if ($ia[6] != 'Y' && $shownoanswer == 1)
			{
				$answer_t_content .= "\t\t\t<td>\n\t\t\t<label for=\"answer$myfname-\">\n"
				. "\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer')."\"";
				if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == '')
				{
					$answer_t_content .= CHECKED;
				}
				// --> START NEW FEATURE - SAVE
				$answer_t_content .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t\t\t</label>\n\t\t\t</td>\n";
				// --> END NEW FEATURE - SAVE
			}
			$answer_t_content .= "\t\t</tr>\n";
			$inputnames[]=$myfname;
			$fn++;
		}
	}
	$answer .= $htmltbody2 . $answer_t_content . "\t</tbody>\n</table>\n";
	return array($answer, $inputnames);
}
/*
// ---------------------------------------------------------------
function do_slider($ia)
{
	global $shownoanswer;
	global $dbprefix;

	$qidattributes=getQuestionAttributes($ia[0]);
	if ($defaultvalue=arraySearchByKey("default_value", $qidattributes, "attribute", 1)) {
		$defaultvalue=$defaultvalue['value'];
	} else {$defaultvalue=0;}
	if ($minimumvalue=arraySearchByKey("minimum_value", $qidattributes, "attribute", 1)) {
		$minimumvalue=$minimumvalue['value'];
	} else {
		$minimumvalue=0;
	}
	if ($maximumvalue=arraySearchByKey("maximum_value", $qidattributes, "attribute", 1)) {
		$maximumvalue=$maximumvalue['value'];
	} else {
		$maximumvalue=50;
	}
	if ($answerwidth=arraySearchByKey("answer_width", $qidattributes, "attribute", 1)) {
		$answerwidth=$answerwidth['value'];
	} else {
		$answerwidth=20;
	}
	$sliderwidth=100-$answerwidth;

	//Get answers
	$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]}  AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	$ansresult = db_execute_assoc($ansquery);     //Checked
	$anscount = $ansresult->RecordCount();

	//Get labels
	$qquery = "SELECT lid FROM {$dbprefix}questions WHERE qid=".$ia[0]."  AND language='".$_SESSION['s_lang']."'";
	$qresult = db_execute_assoc($qquery);      //Checked
	while($qrow = $qresult->FetchRow()) {$lid = $qrow['lid'];}
	$lquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid  AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, code";
	$lresult = db_execute_assoc($lquery);     //Checked

	$answer = "\t\t\t<table class='question'>\n";
	$answer .= "\t\t\t\t<tr><th width='$answerwidth%'></th>\n";
	$lcolspan=$lresult->RecordCount();
	$lcount=1;
	while($lrow=$lresult->FetchRow()) {
		$answer .= "<th align='";
		if ($lcount == 1) {
			$answer .= "left";
		} elseif ($lcount == $lcolspan) {
			$answer .= "right";
		} else {
			$answer .= "center";
		}
		$answer .= "' class='array1'><font size='1'>".$lrow['title']."</font></th>\n";
		$lcount++;
	}
	$answer .= "\t\t\t\t</tr>\n";


	$answer .="\t\t\t\t<tr>\n"
	. "\t\t\t\t\t<td>\n"
	. "\t\t\t\t\t\t";
	$fn=1;
	$trbc = '';
	while ($ansrow = $ansresult->FetchRow())
	{
		//A row for each slider control
		$myfname = $ia[1].$ansrow['code'];
		$answertext=answer_replace($ansrow['answer']);
		$trbc = alternation($trbc , 'row');
		$answer .= "\t\t\t\t<tr class='$trbc'>\n"
		. "\t\t\t\t\t<td align='right'>$answertext</td>\n";
		$answer .= "\t\t\t\t\t<td width='$sliderwidth%' colspan='$lcolspan'>"
		. "<div class=\"slider\" id=\"slider-$myfname\" style='width:100%'>"
		. "<input class=\"slider-input\" id=\"slider-input-$myfname\" name=\"$myfname\" />"
		. "</div>";
		$answer .= "
<script type=\"text/javascript\">

var s = new Slider(document.getElementById(\"slider-$myfname\"),
                   document.getElementById(\"slider-input-$myfname\"));
	s.setValue(";
		if (isset($_SESSION[$myfname])) {
			$answer .= $_SESSION[$myfname];
		} else {
			$answer .= $defaultvalue;
		}
		$answer .= ");
	s.setMinimum($minimumvalue);
	s.setMaximum($maximumvalue);
</script>\n"
		. "\n";
		$answer .= "\t\t\t\t\n"
		. "\t\t\t\t<input type='hidden' name='java$myfname' id='java$myfname' value='";
		if (isset($_SESSION[$myfname])) {$answer .= $_SESSION[$myfname];}
		$answer .= "' />\n</td></tr>";
		$inputnames[]=$myfname;
		$fn++;
	}

	$answer .="\t\t\t\t\t</td>\n"
	. "\t\t\t\t</tr>\n"
	. "\t\t\t</table>\n";

	$inputnames[]=$ia[1];

	return array($answer, $inputnames);
}*/

// ---------------------------------------------------------------
function do_array_increasesamedecrease($ia)
{
	global $dbprefix, $thissurvey, $clang;
	global $shownoanswer;
	global $notanswered;

	$qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
	$qresult = db_execute_assoc($qquery);   //Checked
	$qidattributes=getQuestionAttributes($ia[0]);
	if ($answerwidth=arraySearchByKey('answer_width', $qidattributes, 'attribute', 1))
	{
		$answerwidth=$answerwidth['value'];
	}
	else
	{
		$answerwidth = 20;
	}
	$cellwidth  = 3; // number of columns
	if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory
	{
		++$cellwidth; // add another column
	}
	$cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

	while($qrow = $qresult->FetchRow())
	{
		$other = $qrow['other'];
	}
	if (arraySearchByKey('random_order', $qidattributes, 'attribute', 1))
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
	}
	else
	{
		$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
	}
	$ansresult = db_execute_assoc($ansquery);  //Checked
	$anscount = $ansresult->RecordCount();

	$fn = 1;

	$answer = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - Increase/Same/Decrease Likert scale array\">\n"
	. "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n"
	. "\t<colgroup class=\"col-responses\">\n";
	$odd_even = '';
	for ($xc=1; $xc<=3; $xc++)
	{
		$odd_even = alternation($odd_even);
		$answer .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
	}
	if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory
	{
		$odd_even = alternation($odd_even);
		$answer .= "\t\t<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
	}
	$answer .= "\t</colgroup>\n"
	. "\t<thead>\n"
	. "\t\t<tr>\n"
	. "\t\t\t<td>&nbsp;</td>\n"
	. "\t\t\t<th>".$clang->gT('Increase')."</th>\n"
	. "\t\t\t<th>".$clang->gT('Same')."</th>\n"
	. "\t\t\t<th>".$clang->gT('Decrease')."</th>\n";
	if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory
	{
		$answer .= "\t\t\t<th>".$clang->gT('No answer')."</th>\n";
	}
	$answer .= "\t\t</tr>\n"
	."\t</thead>\n";
	$answer_body = '';
	$trbc = '';
	while ($ansrow = $ansresult->FetchRow())
	{
		$myfname = $ia[1].$ansrow['code'];
		$answertext=answer_replace($ansrow['answer']);
		/* Check if this item has not been answered: the 'notanswered' variable must be an array,
		containing a list of unanswered questions, the current question must be in the array,
		and there must be no answer available for the item in this session. */
		if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && ($_SESSION[$myfname] == "") )
		{
			$answertext = "<span class=\"errormandatory\">{$answertext}</span>";
		}

		$trbc = alternation($trbc , 'row');

		$htmltbody2 = "\t<tbody>\n";
		if ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == "G" && getArrayFiltersOutGroup($ia[0]) == false)
		{
			$htmltbody2 = "<tbody id=\"javatbd$myfname\" style=\"display: none\">\n\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"off\" />\n";
		}
		elseif(	($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'S') || ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == true))
		{
			$selected = getArrayFiltersForQuestion($ia[0]);
			if (!in_array($ansrow['code'],$selected))
			{
				$htmltbody2 = "<tbody id=\"javatbd$myfname\" style=\"display: none\">\n\t\t<input type='hidden' name='tbdisp$myfname' id='tbdisp$myfname' value='off' />\n";
				$_SESSION[$myfname] = '';
			}
			else
			{
				$htmltbody2 = "<tbody id=\"javatbd$myfname\" style=\"display: \">\n\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"on\" />";
			}
		}
		$answer_body .= "\t\t<tr class=\"$trbc\">\n"
		. "\t\t\t<th class=\"answertext\">$answertext</th>\n"
		. "\t\t\t<td>\n"
		. "\t\t\t\t<label for=\"answer$myfname-I\">\n"
		."\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-I\" value=\"I\" title=\"".$clang->gT('Increase').'"';
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == 'I')
		{
			$answer_body .= CHECKED;
		}

		$answer_body .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n"
		. "\t\t\t\t</label>\n"
		. "\t\t\t</td>\n"
		. "\t\t\t<td>\n"
		. "\t\t\t\t<label for=\"answer$myfname-S\">\n"
		. "\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-S\" value=\"S\" title=\"".$clang->gT('Same').'"';

		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == 'S')
		{
			$answer_body .= CHECKED;
		}

		$answer_body .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n"
		. "\t\t\t\t</label>\n"
		. "\t\t\t</td>\n"
		. "\t\t\t<td>\n"
		. "\t\t\t\t<label for=\"answer$myfname-D\">\n"
		. "\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-D\" value=\"D\" title=\"".$clang->gT('Decrease').'"';
		// --> END NEW FEATURE - SAVE
		if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == 'D')
		{
			$answer_body .= CHECKED;
		}

		$answer_body .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n"
		. "\t\t\t\t</label>\n"
		. "\t\t\t\t<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";

		if (isset($_SESSION[$myfname])) {$answer .= $_SESSION[$myfname];}
		$answer_body .= "\" />\n\t\t\t</td>\n";

		if ($ia[6] != 'Y' && $shownoanswer == 1)
		{
			$answer_body .= "\t\t\t<td>\n"
			. "\t\t\t\t<label for=\"answer$myfname-\">\n"
			. "\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer').'"';
			if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == '')
			{
				$answer_body .= CHECKED;
			}
			$answer_body .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n"
			. "\t\t\t\t</label>\n"
			. "\t\t\t</td>\n";
		}
		$answer_body .= "\t\t</tr>\n";
		$inputnames[]=$myfname;
		$fn++;
	}
	$answer .= $htmltbody2 . $answer_body . "\t</tbody>\n</table>\n";
	return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_array_flexible($ia)
{
	global $dbprefix, $connect, $thissurvey, $clang;
	global $shownoanswer;
	global $repeatheadings;
	global $notanswered;
	global $minrepeatheadings;
	$qquery = "SELECT other, lid FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
	$qresult = db_execute_assoc($qquery);     //Checked
	while($qrow = $qresult->FetchRow()) {$other = $qrow['other']; $lid = $qrow['lid'];}
	$lquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid  AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, code";

	$qidattributes=getQuestionAttributes($ia[0]);
	if ($answerwidth=arraySearchByKey('answer_width', $qidattributes, 'attribute', 1))
	{
		$answerwidth=$answerwidth['value'];
	}
	else
	{
		$answerwidth=20;
	}
	$columnswidth=100-$answerwidth;

	$lresult = db_execute_assoc($lquery);   //Checked
	if ($lresult->RecordCount() > 0)
	{
		while ($lrow=$lresult->FetchRow())
		{
			$labelans[]=$lrow['title'];
			$labelcode[]=$lrow['code'];
		}

//		$cellwidth=sprintf('%02d', $cellwidth);
		
		$ansquery = "SELECT answer FROM {$dbprefix}answers WHERE qid=".$ia[0]." AND answer like '%|%' ";
		$ansresult = db_execute_assoc($ansquery);  //Checked
		if ($ansresult->RecordCount()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;} 
		// $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
		if (arraySearchByKey('random_order', $qidattributes, 'attribute', 1))
		{
			$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
		}
		else
		{
			$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
		}
		$ansresult = db_execute_assoc($ansquery); //Checked
		$anscount = $ansresult->RecordCount();
		$fn=1;

		$numrows = count($labelans);
		if ($ia[6] != 'Y' && $shownoanswer == 1)
		{
			++$numrows;
		}
		if ($right_exists)
		{
			++$numrows;
		}
		$cellwidth = round( ($columnswidth / $numrows ) , 1 );

		$answer_start = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an array type question\">\n";
		$answer_head = "\t<thead>\n"
		. "\t\t<tr>\n"
		. "\t\t\t<td>&nbsp;</td>\n";
		foreach ($labelans as $ld)
		{
			$answer_head .= "\t\t\t<th>".$ld."</th>\n";
		}
		if ($right_exists) {$answer_head .= "\t\t\t<td>&nbsp;</td>\n";} 
		if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory and we can show "no answer"
		{
			$answer_head .= "\t\t\t<th>".$clang->gT('No answer')."</th>\n";
		}
		$answer_head .= "\t\t</tr>\n\t</thead>\n\n\t<tbody>\n";

		$answer = '';
		$trbc = '';
		while ($ansrow = $ansresult->FetchRow())
		{
			if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
			{
				if ( ($anscount - $fn + 1) >= $minrepeatheadings )
				{
					$answer .= "\t\t<tr class=\"repeat headings\">\n"
					. "\t\t\t<td>&nbsp;</td>\n";
					foreach ($labelans as $ld)
					{
						$answer .= "\t\t\t<th>".$ld."</th>\n";
					}
					if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory and we can show "no answer"
					{
						$answer .= "\t\t\t<td>&nbsp;</td>\n\t\t\t<th>".$clang->gT('No answer')."</th>\n";
					}
					$answer .= "\t\t</tr>\n";
				}
			}
			$myfname = $ia[1].$ansrow['code'];
			$trbc = alternation($trbc , 'row');
			$answertext=answer_replace($ansrow['answer']);
			$answertextsave=$answertext;
			/* Check if this item has not been answered: the 'notanswered' variable must be an array,
			containing a list of unanswered questions, the current question must be in the array,
			and there must be no answer available for the item in this session. */

			if (strpos($answertext,'|')) {$answerwidth=$answerwidth/2;}

			if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && ($_SESSION[$myfname] == '') ) {
				$answertext = '<span class="errormandatory">'.$answertext.'</span>';
			}
			$htmltbody2 = '';
			if ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == false)
			{
				$htmltbody2 = "\t\t<tr id=\"javatbd$myfname\" style=\"display: none\" class=\"$trbc\">\n\t\t\t<td class=\"answertext\">\n\t\t\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"off\" />\n";
			}
			else if (($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'S') || ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == true))
			{
				$selected = getArrayFiltersForQuestion($ia[0]);
				if (!in_array($ansrow['code'],$selected))
				{
					$htmltbody2 = "\t\t<tr id=\"javatbd$myfname\" style=\"display: none\" class=\"$trbc\">\n\t\t\t<th class=\"answertext\">\n\t\t\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"off\" />\n";
					$_SESSION[$myfname] = "";
				}
				else
				{
					$htmltbody2 = "\t\t<tr id=\"javatbd$myfname\" style=\"display: \" class=\"$trbc\">\n\t\t\t<th class=\"answertext\">\n\t\t\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"on\" />\n";
				}
			}
			else 
			{
				$htmltbody2 = "\t\t<tr id=\"javatbd$myfname\" class=\"$trbc\">\n\t\t\t<th class=\"answertext\">\n\t\t\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"on\" />\n";
			}
			if (strpos($answertext,'|'))
			{
				$answertext=substr($answertext,0, strpos($answertext,'|'));
			}

			$answer .= $htmltbody2
			. "\t\t\t\t$answertext\n"
			. "\t\t\t\t<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
			if (isset($_SESSION[$myfname]))
			{
				$answer .= $_SESSION[$myfname];
			}
			$answer .= "\" />\n"
			. "\t\t\t</th>\n";
			$thiskey=0;
			foreach ($labelcode as $ld)
			{
				$answer .= "\t\t\t<td>\n"
				. "\t\t\t\t<label for=\"answer$myfname-$ld\">\n"
				. "\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"$ld\" id=\"answer$myfname-$ld\" title=\""
				. html_escape(strip_tags($labelans[$thiskey])).'"';
				if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $ld)
				{
					$answer .= CHECKED;
				}
				// --> START NEW FEATURE - SAVE
				$answer .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n"
				. "\t\t\t\t</label>\n"
				. "\t\t\t</td>\n";
				// --> END NEW FEATURE - SAVE

				$thiskey++;
			}
			if (strpos($answertextsave,'|')) 
			{
				$answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
				$answer .= "\t\t\t<th class=\"answertextright\">$answertext</th>\n";
			}
			elseif ($right_exists)
			{
				$answer .= "\t\t\t<td class=\"answertextright\">&nbsp;</td>\n";
			}

			if ($ia[6] != 'Y' && $shownoanswer == 1)
			{
				$answer .= "\t\t\t<td>\n\t\t\t\t<label for=\"answer$myfname-\">\n"
				."\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"\" id=\"answer$myfname-\" title=\"".$clang->gT('No answer').'"';
				if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == '')
				{
					$answer .= CHECKED;
				}
				// --> START NEW FEATURE - SAVE
				$answer .= " onclick=\"checkconditions(this.value, this.name, this.type)\"  />\n\t\t\t\t</label>\n\t\t\t</td>\n";
				// --> END NEW FEATURE - SAVE
			}
			
			$answer .= "\t\t</tr>\n";
			$inputnames[]=$myfname;
			//IF a MULTIPLE of flexi-redisplay figure, repeat the headings
			$fn++;
		}

		$answer_cols = "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n"
		. "\t<colgroup class=\"col-responses\">\n";

		$odd_even = '';
		foreach ($labelans as $c)
		{
			$odd_even = alternation($odd_even);
			$answer_cols .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
		}
		if ($right_exists)
		{
			$odd_even = alternation($odd_even);
			$answer_cols .= "\t\t<col class=\"answertextright $odd_even\" width=\"$answerwidth%\" />\n";
		}
		if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory
		{
			$odd_even = alternation($odd_even);
			$answer_cols .= "\t\t<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
		}
		$answer_cols .= "\t</colgroup>\n";

		$answer = $answer_start . $answer_cols . $answer_head .$answer . "\t</tbody>\n</table>\n";
	}
	else
	{
		$answer = "\n<p class=\"error\">".$clang->gT('Error: The labelset used for this question is not available in this language and/or does not exist.')."</p>\n";
		$inputnames='';
	}
	return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_array_multitext($ia)
{
	global $dbprefix, $connect, $thissurvey, $clang;
	global $shownoanswer;
	global $repeatheadings;
	global $notanswered;
	global $minrepeatheadings;
	//echo "<pre>"; print_r($_POST); echo "</pre>";
	$defaultvaluescript = "";
	$qquery = "SELECT other, lid FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
	$qresult = db_execute_assoc($qquery);
	while($qrow = $qresult->FetchRow()) {$other = $qrow['other']; $lid = $qrow['lid'];}
	$lquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid  AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, code";

	$qidattributes=getQuestionAttributes($ia[0]);


        if (arraySearchByKey('numbers_only', $qidattributes, 'attribute', 1))
        {
                $numbersonly = 'onkeypress="return goodchars(event,\'0123456789.\')"';
                $class_num_only = ' numbers-only';
        }
        else
        {
                $numbersonly = '';
                $class_num_only = '';
        }

	if ($answerwidth=arraySearchByKey("answer_width", $qidattributes, "attribute", 1))
	{
		$answerwidth=$answerwidth['value'];
	}
	else
	{
		$answerwidth=20;
	}
	if ($inputwidth=arraySearchByKey('text_input_width', $qidattributes, 'attribute', 1))
	{
		$inputwidth = $inputwidth['value'];
	}
	else
	{
		$inputwidth = 20;
	}
	$columnswidth=100-($answerwidth*2);

	$lresult = db_execute_assoc($lquery);
	if ($lresult->RecordCount() > 0)
	{
		while ($lrow=$lresult->FetchRow())
		{
			$labelans[]=$lrow['title'];
			$labelcode[]=$lrow['code'];
		}
		$numrows=count($labelans);
		if ($ia[6] != 'Y' && $shownoanswer == 1) {$numrows++;}
		$cellwidth=$columnswidth/$numrows;

		$cellwidth=sprintf('%02d', $cellwidth);
		
		$ansquery = "SELECT answer FROM {$dbprefix}answers WHERE qid=".$ia[0]." AND answer like '%|%'";
		$ansresult = db_execute_assoc($ansquery);
		if ($ansresult->RecordCount()>0)
		{
			$right_exists=true;
			$answerwidth=$answerwidth/2;
		}
		else
		{
			$right_exists=false;
		} 
		// $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
		if (arraySearchByKey('random_order', $qidattributes, 'attribute', 1))
		{
			$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
		}
		else
		{
			$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
		}
		$ansresult = db_execute_assoc($ansquery);
		$anscount = $ansresult->RecordCount();
		$fn=1;

		$answer_cols = "\n\t<col class=\"answertext\" width=\"$answerwidth%\" />\n"
		. "\t<colgroup class=\"col-responses\">\n";

		$answer_head = "\n\t<thead>\n"
		. "\t\t<tr>\n"
		. "\t\t\t<td width='$answerwidth%'>&nbsp;</td>\n";

		$odd_even = '';
		foreach ($labelans as $ld)
		{
			$answer_head .= "\t\t\t<th>".$ld."</th>\n";
			$odd_even = alternation($odd_even);
			$answer_cols .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
		}
		if ($right_exists)
		{
			$answer_head .= "\t\t\t<td>&nbsp;</td>\n";// class=\"answertextright\"
			$odd_even = alternation($odd_even);
			$answer_cols .= "\t\t<col class=\"answertextright $odd_even\" width=\"$cellwidth%\" />\n";
		}

		$answer_cols .= "\n\t</colgroup>\n";

		$answer_head .= "\t\t</tr>\n"
		. "\t</thead>\n"
		. "\n\t<tbody>\n";

		$answer = "\n" . keycontroljs() . "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an array of text responses\">\n" . $answer_cols . $answer_head;

		$trbc = '';
		while ($ansrow = $ansresult->FetchRow())
		{
			if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
			{
				if ( ($anscount - $fn + 1) >= $minrepeatheadings )
				{
					$trbc = alternation($trbc , 'row');
					$answer .= "\t\t<tr class=\"$trbc repeat\">\n"
					. "\t\t\t<td>&nbsp;</td>\n";
					foreach ($labelans as $ld)
					{
						$answer .= "\t\t\t<th>".$ld."</td>\n";
					}
					$answer .= "\t\t</tr>\n";
				}
			}
			$myfname = $ia[1].$ansrow['code'];
			$answertext=answer_replace($ansrow['answer']);
			$answertextsave=$answertext;
			/* Check if this item has not been answered: the 'notanswered' variable must be an array,
			containing a list of unanswered questions, the current question must be in the array,
			and there must be no answer available for the item in this session. */
			if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE))
			{
				//Go through each labelcode and check for a missing answer! If any are found, highlight this line
				$emptyresult=0;
				foreach($labelcode as $ld)
				{
					$myfname2=$myfname.'_'.$ld;
					if($_SESSION[$myfname2] == '')
					{
						$emptyresult=1;
					}
				}
				if ($emptyresult == 1)
				{
					$answertext = "<span class=\"errormandatory\">{$answertext}</span>";
				}
			}

			$htmltbody2 = '';
			if ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == false)
			{
				$htmltbody2 = "\n\t<tbody id=\"javatbd$myfname\" style=\"display: none\">\n\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"off\" />\n";
			} else if (($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'S') || ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == true))
			{
				$selected = getArrayFiltersForQuestion($ia[0]);
				if (!in_array($ansrow['code'],$selected))
				{
					$htmltbody2 = "\n\t<tbody id=\"javatbd$myfname\" style=\"display: none\">\n\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"off\" />\n";
					$_SESSION[$myfname] = '';
				}
				else
				{
					$htmltbody2 = "\n\t<tbody id=\"javatbd$myfname\" style=\"display: \"><input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"on\" />\n";
				}
			}
			if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
			$trbc = alternation($trbc , 'row');
			$answer .= $htmltbody2."\t\t<tr class=\"$trbc\">\n"
			. "\t\t\t<th class=\"answertext\">\n"
			. "\t\t\t\t$answertext\n"
			. "\t\t\t\t<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
			if (isset($_SESSION[$myfname])) {$answer .= $_SESSION[$myfname];}
			$answer .= "\" />\n\t\t\t</th>\n";
			$thiskey=0;
			foreach ($labelcode as $ld)
			{

				$myfname2=$myfname."_$ld";
				$myfname2value = isset($_SESSION[$myfname2]) ? $_SESSION[$myfname2] : "";
				$answer .= "\t\t\t<td>\n"
				. "\t\t\t\t<label for=\"answer{$myfname2}\">\n"
				. "\t\t\t\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" />\n"
				. "\t\t\t\t<input type=\"text\" name=\"$myfname2\" id=\"answer{$myfname2}\" title=\""
				. html_escape($labelans[$thiskey]).'" '
				. "onchange=\"getElementById('java{$myfname2}').value=this.value;checkconditions(this.value, this.name, this.type)\" size=\"$inputwidth\" "
				. $numbersonly 
				. ' value="'.str_replace ('"', "'", str_replace('\\', '', $myfname2value))."\" />\n";
				$inputnames[]=$myfname2;
				$answer .= "\t\t\t\t</label>\n\t\t\t</td>\n";
				$thiskey += 1;
			}
			if (strpos($answertextsave,'|')) 
			{
				$answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
				$answer .= "\t\t\t<td class=\"answertextright\" style='text-align:left;' width='$answerwidth%'>$answertext</td>\n";
			}
			elseif ($right_exists)
			{
				$answer .= "\t\t\t<td class=\"answertextright\" style='text-align:left;' width='$answerwidth%'>&nbsp;</td>\n";
			}
			$answer .= "\t\t</tr>\n";
			//IF a MULTIPLE of flexi-redisplay figure, repeat the headings
			$fn++;
		}
		$answer .= "\t</tbody>\n</table>\n";
	}
	else
	{
		$answer = "\n<p class=\"error\">".$clang->gT('Error: The labelset used for this question is not available in this language and/or does not exist.')."</p>";
		$inputnames='';
	}
	return array($answer, $inputnames);
}


// ---------------------------------------------------------------
function do_array_multiflexi($ia)
{
	global $dbprefix, $connect, $thissurvey, $clang;
	global $shownoanswer;
	global $repeatheadings;
	global $notanswered;
	global $minrepeatheadings;
	//echo '<pre>'; print_r($_POST); echo '</pre>';
	$defaultvaluescript = '';
	$qquery = "SELECT other, lid FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
	$qresult = db_execute_assoc($qquery);
	while($qrow = $qresult->FetchRow()) {$other = $qrow['other']; $lid = $qrow['lid'];}
	$lquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid  AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, code";

	$qidattributes=getQuestionAttributes($ia[0]);
	if ($maxvalue=arraySearchByKey('multiflexible_max', $qidattributes, 'attribute', 1))
	{
		$maxvalue=$maxvalue['value'];
	}
	else
	{
		$maxvalue=10;
	}
	if ($minvalue=arraySearchByKey('multiflexible_min', $qidattributes, 'attribute', 1))
	{
		$minvalue=$minvalue['value'];
	}
	else
	{
		if(isset($minvalue['value']) && $minvalue['value'] == 0) {$minvalue = 0;} else {$minvalue=1;}
	}

	if ($stepvalue=arraySearchByKey('multiflexible_step', $qidattributes, 'attribute', 1))
	{
		$stepvalue=$stepvalue['value'];
	}
	else
	{
		$stepvalue=1;
	}

	$checkboxlayout=false;
	if (arraySearchByKey('multiflexible_checkbox', $qidattributes, 'attribute', 1))
	{
		$minvalue=0;
		$maxvalue=1;
		$checkboxlayout=true;
	} 

	if ($answerwidth=arraySearchByKey('answer_width', $qidattributes, 'attribute', 1))
	{
		$answerwidth=$answerwidth['value'];
	}
	else
	{
		$answerwidth=20;
	}
	$columnswidth=100-($answerwidth*2);

	$lresult = db_execute_assoc($lquery);
	if ($lresult->RecordCount() > 0)
	{
		while ($lrow=$lresult->FetchRow())
		{
			$labelans[]=$lrow['title'];
			$labelcode[]=$lrow['code'];
		}
		$numrows=count($labelans);
		if ($ia[6] != 'Y' && $shownoanswer == 1) {$numrows++;}
		$cellwidth=$columnswidth/$numrows;

		$cellwidth=sprintf('%02d', $cellwidth);
		
		$ansquery = "SELECT answer FROM {$dbprefix}answers WHERE qid=".$ia[0]." AND answer like '%|%'";
		$ansresult = db_execute_assoc($ansquery);
		if ($ansresult->RecordCount()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;} 
		// $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
		if (arraySearchByKey('random_order', $qidattributes, 'attribute', 1))
		{
			$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
		}
		else
		{
			$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
		}
		$ansresult = db_execute_assoc($ansquery);
		$anscount = $ansresult->RecordCount();
		$fn=1;

		$mycols = "\n\t<col class=\"answertext\" width=\"$answerwidth%\" />\n"
		. "\t<colgroup class=\"col-responses\">\n";

		$myheader = "\n\t<thead>\n"
		. "\t\t<tr>\n"
		. "\t\t\t<td >&nbsp;</td>\n";

		$odd_even = '';
		foreach ($labelans as $ld)
		{
			$myheader .= "\t\t\t<th>".$ld."</th>\n";
			$odd_even = alternation($odd_even);
			$mycols .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
		}
		if ($right_exists)
		{
			$myheader .= "\t\t\t<td>&nbsp;</td>";
			$odd_even = alternation($odd_even);
			$mycols .= "\t\t<col class=\"answertextright $odd_even\" width=\"$answerwidth%\" />\n";
		}
		$myheader .= "\t\t</tr>\n"
		. "\t</thead>\n";
		$mycols .= "\t</colgroup>\n";

		$trbc = '';
		$answer = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an array type question with dropdown responses\">\n" . $mycols . $myheader . "\n\t<tbody>\n";

		while ($ansrow = $ansresult->FetchRow())
		{
			if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
			{
				if ( ($anscount - $fn + 1) >= $minrepeatheadings )
				{
					$trbc = alternation($trbc , 'row');
					$answer .= "\t\t<tr class=\"$trbc repeat\">\n"
					. "\t\t\t<td>&nbsp;</td>\n";
					foreach ($labelans as $ld)
					{
						$answer .= "\t\t\t<th>".$ld."</th>\n";
					}
					$answer .= "\t\t</tr>\n";
				}
			}
			$myfname = $ia[1].$ansrow['code'];
			$answertext=answer_replace($ansrow['answer']);
			$answertextsave=$answertext;
			/* Check if this item has not been answered: the 'notanswered' variable must be an array,
			containing a list of unanswered questions, the current question must be in the array,
			and there must be no answer available for the item in this session. */
			if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE))
			{
				//Go through each labelcode and check for a missing answer! If any are found, highlight this line
				$emptyresult=0;
				foreach($labelcode as $ld)
				{
					$myfname2=$myfname.'_'.$ld;
					if($_SESSION[$myfname2] == "")
					{
						$emptyresult=1;
					}
				}
				if ($emptyresult == 1)
				{
					$answertext = '<span class="errormandatory">'.$answertext.'</span>';
				}
			}
			
			$htmltbody2 = '';
			$first_hidden_field = '';
			if ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == false)
			{
				$htmltbody2 = "\n\t<tbody id=\"javatbd$myfname\" style=\"display: none\">\n";
				$first_hidden_field = '<input type="hidden" name="tbdisp'.$myfname.'" id="tbdisp'.$myfname."\" value=\"off\" />\n";
//				$htmltbody2 .= "\t\t" . $first_hidden_field; // This is how it used to be. I have moved $first_hidden_field into the first cell of the table so it validates.
//				$first_hidden_field = ''; // These two lines have been commented because they replace badly validating code.
			}
			else if (($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'S') || ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == true))
			{
				$selected = getArrayFiltersForQuestion($ia[0]);
				if (!in_array($ansrow['code'],$selected))
				{
					$htmltbody2 = "\n\t<tbody id=\"javatbd$myfname\" style=\"display: none\">\n";
					$first_hidden_field = '<input type="hidden" name="tbdisp'.$myfname.'" id="tbdisp'.$myfname."\" value=\"off\" />\n";
//					$htmltbody2 .= "\t\t" . $first_hidden_field; // This is how it used to be. I have moved $first_hidden_field into the first cell of the table so it validates.
//					$first_hidden_field = ''; // These two lines have been commented because they replace badly validating code.

					$_SESSION[$myfname] = '';
				}
				else
				{
					$htmltbody2 = "\n\t<tbody id=\"javatbd$myfname\" style=\"display: \">\n";
					$first_hidden_field = '<input type="hidden" name="tbdisp'.$myfname.'" id="tbdisp'.$myfname."\" value=\"on\" />\n";
//					$htmltbody2 .= "\t\t" . $first_hidden_field; // This is how it used to be. I have moved $first_hidden_field into the first cell of the table so it validates.
//					$first_hidden_field = ''; // These two lines have been commented because they replace badly validating code.
				}
			}
			if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}

			$trbc = alternation($trbc , 'row');
			$answer .= $htmltbody2 . "\t\t<tr class=\"$trbc\">\n"
			. "\t\t\t<th class=\"answertext\" width=\"$answerwidth%\">\n"
			. "\t\t\t\t$answertext\n"
			. "\t\t\t\t" . $first_hidden_field
			. "\t\t\t\t<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
			if (isset($_SESSION[$myfname]))
			{
				$answer .= $_SESSION[$myfname];
			}
			$answer .= "\" />\n\t\t\t</th>\n";
			$first_hidden_field = '';
			$thiskey=0;
			foreach ($labelcode as $ld)
			{

				if ($checkboxlayout == false)
				{
					$myfname2=$myfname."_$ld";
					$answer .= "\t\t\t<td>\n"
					. "\t\t\t\t<label for=\"answer{$myfname2}\">\n"
					. "\t\t\t\t\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" />\n"
					. "\t\t\t\t\t<select class=\"multiflexiselect\" name=\"$myfname2\" id=\"answer{$myfname2}\" title=\""
					. html_escape($labelans[$thiskey]).'"'
					. " onchange=\"checkconditions(this.value, this.name, this.type)\">\n"
					. "\t\t\t\t\t\t<option value=\"\">...</option>\n";

					for($ii=$minvalue; $ii<=$maxvalue; $ii+=$stepvalue) {
						$answer .= "\t\t\t\t\t\t<option value=\"$ii\"";
						if(isset($_SESSION[$myfname2]) && $_SESSION[$myfname2] == $ii) {
							$answer .= SELECTED;
						}
						$answer .= ">$ii</option>\n";
					}
					$inputnames[]=$myfname2;
					$answer .= "\t\t\t\t\t</select>\n"
					. "\t\t\t\t</label>\n"
					. "\t\t\t</td>\n";

					$thiskey++;
				}
				else
				{
					$myfname2=$myfname."_$ld";
					if(isset($_SESSION[$myfname2]) && $_SESSION[$myfname2] == '1')
					{
						$myvalue = '1';
						$setmyvalue = CHECKED;
					}
					else
					{
						$myvalue = '0';
						$setmyvalue = '';
					}
					$answer .= "\t\t\t<td>\n"
//					. "\t\t\t\t<label for=\"answer{$myfname2}\">\n"
					. "\t\t\t\t\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" value=\"$myvalue\"/>\n"
					. "\t\t\t\t\t<input type=\"hidden\" name=\"$myfname2\" id=\"answer{$myfname2}\" value=\"$myvalue\" />\n";
					$answer .= "\t\t\t\t\t<input type=\"checkbox\" name=\"cbox_$myfname2\" id=\"cbox_$myfname2\" $setmyvalue "
					. " onclick=\"cancelBubbleThis(event); "
					. " aelt=document.getElementById('answer{$myfname2}');"
					. " jelt=document.getElementById('java{$myfname2}');"
					. " if(this.checked) {"
					. "  aelt.value=1;jelt.value=1;checkconditions(1,'answer{$myfname2}',aelt.type);"
					. " } else {" 
					. "  aelt.value=0;jelt.value=0;checkconditions(0,'answer{$myfname2}',aelt.type);"
					. " }; return true;\" "
//					. " onchange=\"checkconditions(this.value, this.name, this.type)\" "
					. " />\n";
					$inputnames[]=$myfname2;
//					$answer .= "\t\t\t\t</label>\n"
					$answer .= ""
					. "\t\t\t</td>\n";
					$thiskey++;
				}
			}
			if (strpos($answertextsave,'|')) 
			{
				$answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
				$answer .= "\t\t\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">$answertext</td>\n";
			}
			elseif ($right_exists)
			{
				$answer .= "\t\t\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">&nbsp;</td>\n";
			}

			$answer .= "\t\t</tr>\n";
			//IF a MULTIPLE of flexi-redisplay figure, repeat the headings
			$fn++;
		}
		$answer .= "\t</tbody>\n</table>\n";
	}
	else
	{
		$answer = "\n<p class=\"error\">".$clang->gT('Error: The labelset used for this question is not available in this language and/or does not exist.')."</p>\n";
		$inputnames = '';
	}
	return array($answer, $inputnames);
}


// ---------------------------------------------------------------
function do_array_flexiblecolumns($ia)
{
	global $dbprefix;
	global $shownoanswer;
	global $notanswered, $clang;
	$qidattributes=getQuestionAttributes($ia[0]);
	$qquery = "SELECT other, lid FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
	$qresult = db_execute_assoc($qquery);    //Checked
	while($qrow = $qresult->FetchRow()) {$other = $qrow['other']; $lid = $qrow['lid'];}
	$lquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid  AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, code";
	$lresult = db_execute_assoc($lquery);   //Checked
	if ($lresult->RecordCount() > 0)
	{
		while ($lrow=$lresult->FetchRow())
		{
			$labelans[]=$lrow['title'];
			$labelcode[]=$lrow['code'];
			$labels[]=array("answer"=>$lrow['title'], "code"=>$lrow['code']);
		}
		if ($ia[6] != 'Y' && $shownoanswer == 1)
		{
			$labelcode[]='';
			$labelans[]=$clang->gT('No answer');
			$labels[]=array('answer'=>$clang->gT('No answer'), 'code'=>'');
		}
		if (arraySearchByKey('random_order', $qidattributes, 'attribute', 1))
		{
			$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
		}
		else
		{
			$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
		}
		$ansresult = db_execute_assoc($ansquery);  //Checked
		$anscount = $ansresult->RecordCount();
		if ($anscount>0)
		{
			$fn=1;
			$cellwidth=$anscount;
			$cellwidth=round(( 50 / $cellwidth ) , 1);
			$answer = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an array type question with a single response per row\">\n\n"
			. "\t<col class=\"col-answers\" width=\"50%\" />\n"
			. "\t<colgroup class=\"col-responses\">\n";
			$odd_even = '';
			for( $c = 0 ; $c < $anscount ; ++$c )
			{
				$odd_even = alternation($odd_even);
				$answer .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
			}
			$answer .= "\t</colgroup>\n\n"
			. "\t<thead>\n"
			. "\t\t<tr>\n"
			. "\t\t\t<td>&nbsp;</td>\n";
			while ($ansrow = $ansresult->FetchRow())
			{
				$anscode[]=$ansrow['code'];
				$answers[]=answer_replace($ansrow['answer']);
			}
			$trbc = '';
			$odd_even = '';
			foreach ($answers as $ld)
			{
				$myfname = $ia[1].$ansrow['code'];
				$trbc = alternation($trbc , 'row');
				/* Check if this item has not been answered: the 'notanswered' variable must be an array,
				containing a list of unanswered questions, the current question must be in the array,
				and there must be no answer available for the item in this session. */
				if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && ($_SESSION[$myfname] == "") )
				{
					$ld = "<span class=\"errormandatory\">{$ld}</span>";
				}
				$odd_even = alternation($odd_even);
				$answer .= "\t\t\t<th class=\"$odd_even\">$ld</th>\n";
			}
			unset($trbc);
			$answer .= "\t\t</tr>\n\t</thead>\n\n\t<tbody>\n";
			$ansrowcount=0;
			$ansrowtotallength=0;
			while ($ansrow = $ansresult->FetchRow())
			{
				$ansrowcount++;
				$ansrowtotallength=$ansrowtotallength+strlen($ansrow['answer']);
			}
			$percwidth=100 - ($cellwidth*$anscount);
			foreach($labels as $ansrow)
			{
				$answer .= "\t\t<tr>\n"
				. "\t\t\t<th class=\"arraycaptionleft\">{$ansrow['answer']}</th>\n";
				foreach ($anscode as $ld)
				{
					//if (!isset($trbc) || $trbc == 'array1') {$trbc = 'array2';} else {$trbc = 'array1';}
					$myfname=$ia[1].$ld;
					$answer .= "\t\t\t<td>\n"
					. "\t\t\t\t<label for=\"answer".$myfname.'-'.$ansrow['code']."\">\n"
					. "\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"".$myfname.'" value="'.$ansrow['code'].'" '
					. 'id="answer'.$myfname.'-'.$ansrow['code'].'" '
					. 'title="'.html_escape(strip_tags($ansrow['answer'])).'"';
					if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $ansrow['code'])
					{
						$answer .= CHECKED;
					}
					elseif (!isset($_SESSION[$myfname]) && $ansrow['code'] == '')
					{
						$answer .= CHECKED;
						// Humm.. (by lemeur), not sure this section can be reached
						// because I think $_SESSION[$myfname] is always set (by save.php ??) !
						// should remove the !isset part I think !!
					}
					$answer .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t\t\t</label>\n\t\t\t</td>\n";
				}
			unset($trbc);
				$answer .= "\t\t</tr>\n";
				$fn++;
			}

			$answer .= "\t</tbody>\n</table>\n";
			foreach($anscode as $ld)
			{
				$myfname=$ia[1].$ld;
				$answer .= '<input type="hidden" name="java'.$myfname.'" id="java'.$myfname.'" value="';
				if (isset($_SESSION[$myfname]))
				{
					$answer .= $_SESSION[$myfname];
				}
				$answer .= "\" />\n";
				$inputnames[]=$myfname;
			}
		}
		else
		{
			$answer = '<p class="error">'.$clang->gT('Error: There are no answers defined for this question.')."</p>";
			$inputnames="";
		}
	}
	else
	{
		$answer = '<p class="error">'.$clang->gT('Error: The labelset used for this question is not available in this language and/or does not exist.')."</p>";
		$inputnames = '';
	}
	return array($answer, $inputnames);
}


// ---------------------------------------------------------------
function do_array_flexible_dual($ia)
{
	global $dbprefix, $connect, $thissurvey, $clang;
	global $shownoanswer;
	global $repeatheadings;
	global $notanswered;
	global $minrepeatheadings;
	$inputnames=array();
	$qquery = "SELECT other, lid, lid1 FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
	$qresult = db_execute_assoc($qquery);    //Checked
	while($qrow = $qresult->FetchRow()) {$other = $qrow['other']; $lid = $qrow['lid']; $lid1 = $qrow['lid1'];}
	$lquery = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid  AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, code";
	$lquery1 = "SELECT * FROM {$dbprefix}labels WHERE lid=$lid1  AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, code";
	$qidattributes=getQuestionAttributes($ia[0]);

	if ($useDropdownLayout=arraySearchByKey('use_dropdown', $qidattributes, 'attribute', 1))
	{
		$useDropdownLayout = true;
	}
	else
	{
		$useDropdownLayout = false;
	}

	if ($dsheaderA=arraySearchByKey('dualscale_headerA', $qidattributes, 'attribute', 1))
	{
		$leftheader= $dsheaderA['value'];
	}
	else
	{
		$leftheader ='';
	}
	if ($dsheaderB=arraySearchByKey('dualscale_headerB', $qidattributes, 'attribute', 1))
	{
		$rightheader= $dsheaderB['value'];
	}
	else
	{
		$rightheader ='';
	}

	$lresult = db_execute_assoc($lquery); //Checked
	if ($useDropdownLayout === false && $lresult->RecordCount() > 0)
	{

		if ($answerwidth=arraySearchByKey('answer_width', $qidattributes, 'attribute', 1))
		{
			$answerwidth=$answerwidth['value'];
		}
		else
		{
			$answerwidth=20;
		}
		$columnswidth = 100 - $answerwidth;


		while ($lrow=$lresult->FetchRow())
		{
			$labelans[]=$lrow['title'];
			$labelcode[]=$lrow['code'];
		}
		$lresult1 = db_execute_assoc($lquery1); //Checked
		if ($lresult1->RecordCount() > 0)
		{
			while ($lrow1=$lresult1->FetchRow())
			{
				$labelans1[]=$lrow1['title'];
				$labelcode1[]=$lrow1['code'];
			}
		}
		$numrows=count($labelans) + count($labelans1);
		if ($ia[6] != "Y" && $shownoanswer == 1) {$numrows++;}
		$cellwidth=$columnswidth/$numrows;

		$cellwidth=sprintf("%02d", $cellwidth);
		
		$ansquery = "SELECT answer FROM {$dbprefix}answers WHERE qid=".$ia[0]." AND answer like '%|%'";
		$ansresult = db_execute_assoc($ansquery);   //Checked
		if ($ansresult->RecordCount()>0)
		{
			$right_exists=true;
		}
		else
		{
			$right_exists=false;
		}
		// $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
		if (arraySearchByKey('random_order', $qidattributes, 'attribute', 1))
		{
			$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
		}
		else
		{
			$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
		}
		$ansresult = db_execute_assoc($ansquery);   //Checked
		$anscount = $ansresult->RecordCount();
		$fn=1;
		// unselect second scale when using "no answer"
		$answer = "<script type='text/javascript'>\n"
		. "<!--\n"
    	. "\tfunction noanswer_checkconditions(value, name, type)\n"
    	. "\t\t{\n"
    	. "\t\t\tvar vname;\n"
        . "\t\t\tvname = name.replace(/#0/g,\"#1\");\n"
		. "\t\t\tfor(var i=0, n=document.getElementsByName(vname).length; i<n; ++i)\n"
    	. "\t\t\t{\n"
    	. "\t\t\t\tdocument.getElementsByName(vname)[i].checked=false;\n"
    	. "\t\t\t}\n"
    	. "\t\t\tcheckconditions(value, name, type);\n"
		. "\t\t}\n"
        . "\tfunction secondlabel_checkconditions(value, name, type)\n"
        . "\t\t{\n"
        . "\t\t\tvar vname;\n"
        . "\t\t\tvname = \"answer\"+name.replace(/#1/g,\"#0-\");\n"
        . "\t\t\tif(document.getElementById(vname))\n"
        . "\t\t\t{\n"
        . "\t\t\t\tdocument.getElementById(vname).checked=false;\n"
        . "\t\t\t}\n"  
        . "\t\t\tcheckconditions(value, name, type);\n"
        . "\t\t}\n"        
		. " //-->\n"
		. " </script>\n";



		$mycolumns = "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n"
		. "\t<colgroup class=\"col-responses group-1\">\n";

		$myheader2 = "\n\t\t<tr class=\"array1\">\n"
		. "\t\t\t<td>&nbsp;</td>\n\n";
		$odd_even = '';
		foreach ($labelans as $ld)
		{
			$myheader2 .= "\t\t\t<th>".$ld."</th>\n";
			$odd_even = alternation($odd_even);
			$mycolumns .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
		}
		$mycolumns .= "\t</colgroup>\n";

		if (count($labelans1)>0) // if second label set is used
		{
			$mycolumns .= "\t<col class=\"seperator\" />\n"
			. "\t<colgroup class=\"col-responses group-2\">\n";
			$myheader2 .= "\n\t\t\t<td>&nbsp;</td>\n\n";
			foreach ($labelans1 as $ld)
			{
				$myheader2 .= "\t\t\t<th>".$ld."</th>\n";
				$odd_even = alternation($odd_even);
				$mycolumns .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
			}
			$mycolumns .= "\t</colgroup>\n";
		}
		$myheader2 .= "\t\t\t<td>&nbsp;</td>\n";
		if ($right_exists)
		{
			$mycolumns .= "\n\t<col class=\"answertextright\" />\n\n";
		}
		else
		{
			$mycolumns .= "\n\t<col class=\"seperator\" />\n\n";
		} 
		if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory and we can show "no answer"
		{
			$myheader2 .= "\t\t\t<th>".$clang->gT('No answer')."</th>\n";
			$odd_even = alternation($odd_even);
			$mycolumns .= "\t<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
		}
		$myheader2 .= "\t\t</tr>\n";



		// build first row of header if needed
		if ($leftheader != '' || $rightheader !='')
		{
			$myheader1 = "\t\t<tr class=\"array1 groups\">\n"
			. "\t\t\t<td>&nbsp;</td>\n"
			. "\t\t\t<th colspan=\"".count($labelans)."\" class=\"dsheader\">$leftheader</th>\n";

			if (count($labelans1)>0)
			{
				$myheader1 .= "\t\t\t<td>&nbsp;</td>\n"
				."\t\t\t<th colspan=\"".count($labelans1)."\" class=\"dsheader\">$rightheader</th>\n";
			}

			$myheader1 .= "\t\t\t<td>&nbsp;</td>\n";

			if ($ia[6] != 'Y' && $shownoanswer == 1)
			{
				$myheader1 .= "\t\t\t<td>&nbsp;</td>\n";
			}
			$myheader1 .= "\t\t</tr>\n";
		}
		else
		{
			$myheader1 = '';
		}

		$answer .= "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an duel array type question\">\n"
		. $mycolumns
		. "\n\t<thead>\n"
		. $myheader1
		. $myheader2
		. "\n\t</thead>\n"
		. "\n\t<tbody>\n";

		$trbc = '';
		while ($ansrow = $ansresult->FetchRow())
		{
			if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
			{
				if ( ($anscount - $fn + 1) >= $minrepeatheadings )
				{
					$answer .= "\t\t\t\t<tr  class=\"repeat\">\n"
					. "\t\t\t<td>&nbsp;</td>\n";
					foreach ($labelans as $ld)
					{
						$answer .= "\t\t\t<th>".$ld."</td>\n";
					}
					if (count($labelans1)>0) // if second label set is used
					{
//						$answer .= "\t\t\t\t\t<td><font size='1'></font></td>\n";		// separator	
						foreach ($labelans1 as $ld)
						{
						$answer .= "\t\t\t<th>".$ld."</th>\n";
						}
					}
					if ($ia[6] != 'Y' && $shownoanswer == 1) //Question is not mandatory and we can show "no answer"
					{
						$answer .= "\t\t\t<td>&nbsp;</td>\n";		// separator	
						$answer .= "\t\t\t<th>".$clang->gT('No answer')."</th>\n";
					}
					$answer .= "\t\t</tr>\n";
				}
			}

			$trbc = alternation($trbc , 'row');
			$answertext=answer_replace($ansrow['answer']);
			$answertextsave=$answertext;

			$dualgroup=0; 
			$myfname = $ia[1].$ansrow['code'].'#0';
			$myfname1 = $ia[1].$ansrow['code'].'#1'; // new multi-scale-answer
			/* Check if this item has not been answered: the 'notanswered' variable must be an array,
			containing a list of unanswered questions, the current question must be in the array,
			and there must be no answer available for the item in this session. */
			if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && (($_SESSION[$myfname] == '') || ($_SESSION[$myfname1] == '')) ) 
			{
				$answertext = "<span class='errormandatory'>{$answertext}</span>";
			}
			$htmltbody2 = '';
			$hiddenanswers='';
			if ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == false)
			{
				$htmltbody2 = "\n\t<tbody id=\"javatbd$myfname\" style=\"display: none\">\n";
				$hiddenanswers  .="\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"off\" />\n";
			}
			else if (($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'S') || ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == true))
			{
				$selected = getArrayFiltersForQuestion($ia[0]);
				if (!in_array($ansrow['code'],$selected))
				{
					$htmltbody2 = "\t<tbody id=\"javatbd$myfname\" style=\"display: none\">";
					$hiddenanswers  .="<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"off\" />";
					$_SESSION[$myfname] = "";
				}
				else
				{
					$htmltbody2 = "\t<tbody id=\"javatbd$myfname\" style=\"display: \">";
					$hiddenanswers  .="<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"on\" />";
				}
			}
			if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}

			array_push($inputnames,$myfname);
			$answer .= $htmltbody2
			. "\t\t<tr class=\"$trbc\">\n"
			. "\t\t\t<th class=\"answertext\">\n\t\t\t\t$answertext\n"
			. "\t\t\t\t<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
			if (isset($_SESSION[$myfname])) {$answer .= $_SESSION[$myfname];}
			$answer .= "\" />\n\t\t\t\t$hiddenanswers\n\t\t\t</th>\n";
			$hiddenanswers='';
			$thiskey=0;

			foreach ($labelcode as $ld)
			{
				$answer .= "\t\t\t<td>\n"
				. "\t\t\t\t<label for=\"answer$myfname-$ld\">\n"
				. "\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"$ld\" id=\"answer$myfname-$ld\" title=\""
				. html_escape(strip_tags($labelans[$thiskey])).'"';
				if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $ld)
				{
					$answer .= CHECKED;
				}
				// --> START NEW FEATURE - SAVE
				$answer .= " onclick=\"checkconditions(this.value, this.name, this.type)\" />\n\t\t\t\t</label>\n";
				// --> END NEW FEATURE - SAVE
				$answer .= "\n\t\t\t</td>\n";
				$thiskey++;
			}
			if (count($labelans1)>0) // if second label set is used
			{			
				$dualgroup++;
				$hiddenanswers='';
				$answer .= "\t\t\t<td>&nbsp;</td>\n";		// separator
				array_push($inputnames,$myfname1);
				$hiddenanswers .= "\t\t\t\t<input type=\"hidden\" name=\"java$myfname1\" id=\"java$myfname1\" value=\"";
				if (isset($_SESSION[$myfname1])) {$hiddenanswers .= $_SESSION[$myfname1];}
				$hiddenanswers .= "\" />\n";
				$thiskey=0;
				foreach ($labelcode1 as $ld) // second label set
				{
					$answer .= "\t\t\t<td>\n";
					if ($hiddenanswers!='')
					{
						$answer .=$hiddenanswers;
						$hiddenanswers='';
					}
					$answer .= "\t\t\t\t<label for=\"answer$myfname1-$ld\">\n"
					. "\t\t\t\t\t<input class=\"radio\" type=\"radio\" name=\"$myfname1\" value=\"$ld\" id=\"answer$myfname1-$ld\" title=\""
					. html_escape(strip_tags($labelans1[$thiskey])).'"';
					if (isset($_SESSION[$myfname1]) && $_SESSION[$myfname1] == $ld)
					{
						$answer .= CHECKED;
					}
					// --> START NEW FEATURE - SAVE
					$answer .= " onclick=\"secondlabel_checkconditions(this.value, this.name, this.type)\" />\n\t\t\t\t</label>\n";
					// --> END NEW FEATURE - SAVE

					$answer .= "\t\t\t</td>\n";
					$thiskey++;
				}
			}
			if (strpos($answertextsave,'|')) 
			{
				$answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
				$answer .= "\t\t\t<td class=\"answertextright\">$answertext</td>\n";
				$hiddenanswers = '';
			}
			elseif ($right_exists)
			{
				$answer .= "\t\t\t<td class=\"answertextright\">&nbsp;</td>\n";
			}
			else
			{
				$answer .= "\t\t\t<td>&nbsp;</td>\n";		// separator
			}

			if ($ia[6] != "Y" && $shownoanswer == 1)
			{
				$answer .= "\t\t\t<td>\n"
				. "\t\t\t\t<label for='answer$myfname-'>\n"
				. "\t\t\t\t\t<input class='radio' type='radio' name='$myfname' value='' id='answer$myfname-' title='".$clang->gT("No answer")."'";
				if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == "")
				{
					$answer .= CHECKED;
				}
				// --> START NEW FEATURE - SAVE
				$answer .= " onclick=\"noanswer_checkconditions(this.value, this.name, this.type)\" />\n"
				. "\t\t\t\t</label>\n"
				. "\t\t\t</td>\n";
				// --> END NEW FEATURE - SAVE
			}
			
			$answer .= "\t\t</tr>\n";
			// $inputnames[]=$myfname;
			//IF a MULTIPLE of flexi-redisplay figure, repeat the headings
			$fn++;
		}
		$answer .= "\t</tbody>\n</table>\n";
	}
	elseif ($useDropdownLayout === true && $lresult->RecordCount() > 0)
	{ 

		if ($answerwidth=arraySearchByKey('answer_width', $qidattributes, 'attribute', 1)) {
			$answerwidth=$answerwidth['value'];
		} else {
			$answerwidth=20;
		}
		$separatorwidth=(100-$answerwidth)/10;
		$columnswidth=100-$answerwidth-($separatorwidth*2);

		$answer = "<script type='text/javascript'>\n"
		. "<!--\n"
		. "\tfunction special_checkconditions(value, name, type, rank)\n"
		. "\t\t{\n"
		. "\t\t\tif (value == '') {\n"
		. "\t\t\t\tif (rank == 0) { dualname = name.replace(/#0/g,\"#1\"); }\n"
		. "\t\t\t\telse if (rank == 1) { dualname = name.replace(/#1/g,\"#0\"); }\n"
		. "\t\t\t\tdocument.getElementsByName(dualname)[0].value=value;\n"
		. "\t\t\t}\n"
		. "\t\t\t\tcheckconditions(value, name, type);\n"
		. "}\n"
		. " //-->\n"
		. " </script>\n";

		// Get Answers
		
		//question atribute random_order set?
		if (arraySearchByKey('random_order', $qidattributes, 'attribute', 1))
		{
			$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
		}		
		
		//no question attributes -> order by sortorder
		else
		{
			$ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, answer";
		}
		$ansresult = db_execute_assoc($ansquery);    //Checked
		$anscount = $ansresult->RecordCount();

		if ($anscount==0) 
		{
			$inputnames = array();
			$answer .="\n<p class=\"error\">".$clang->gT('Error: This question has no answers.')."</p>\n";
		}
		else 
		{

			//already done $lresult = db_execute_assoc($lquery);
			while ($lrow=$lresult->FetchRow())
			{
				$labels0[]=Array('code' => $lrow['code'],
						'title' => $lrow['title']);
			}
			$lresult1 = db_execute_assoc($lquery1);   //Checked
			while ($lrow1=$lresult1->FetchRow())
			{
				$labels1[]=Array('code' => $lrow1['code'],
						'title' => $lrow1['title']);
			}


			// Get attributes for Headers and Prefix/Suffix

			if ($ddprepostfix=arraySearchByKey("dropdown_prepostfix", $qidattributes, "attribute", 1))
			{
				list ($ddprefix, $ddsuffix) =explode("|",$ddprepostfix['value']);
				$ddprefix = $ddprefix;
				$ddsuffix = $ddsuffix;
			}
			else
			{
				$ddprefix ='';
				$ddsuffix='';
			}
			if ($ddseparators=arraySearchByKey('dropdown_separators', $qidattributes, 'attribute', 1))
			{
				list ($postanswSep, $interddSep) =explode('|',$ddseparators['value']);
				$postanswSep = $postanswSep;
				$interddSep = $interddSep;
			}
			else
			{
				$postanswSep = '';
				$interddSep = '';
			}

			$colspan_1 = '';
			$colspan_2 = '';
			$suffix_cell = '';
			$answer .= "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an duel array type question\">\n\n"
			. "\t<col class=\"answertext\" width=\"$answerwidth%\" />\n";
			if($ddprefix != '')
			{
				$answer .= "\t<col class=\"ddprefix\" />\n";
				$colspan_1 = ' colspan="2"';
			}
			$answer .= "\t<col class=\"dsheader\" />\n";
			if($ddsuffix != '')
			{
				$answer .= "\t<col class=\"ddsuffix\" />\n";
				if(!empty($colspan_1))
				{
					$colspan_2 = ' colspan="3"';
				}
				$suffix_cell = "\t\t\t<td>&nbsp;</td>\n"; // suffix
			}
			$answer .= "\t<col class=\"ddarrayseparator\" width=\"$separatorwidth%\" />\n";
			if($ddprefix != '')
			{
				$answer .= "\t<col class=\"ddprefix\" />\n";
			}
			$answer .= "\t<col class=\"dsheader\" />\n";
			if($ddsuffix != '')
			{
				$answer .= "\t<col class=\"ddsuffix\" />\n";
			};
			// headers
			$answer .= "\n\t<thead>\n"
			. "\t\t<tr>\n"
			. "\t\t\t<td$colspan_1>&nbsp;</td>\n" // prefix
			. "\n"
//			. "\t\t\t<td align='center' width='$columnswidth%'><span class='dsheader'>$leftheader</span></td>\n"
			. "\t\t\t<th>$leftheader</th>\n"
			. "\n"
			. "\t\t\t<td$colspan_2>&nbsp;</td>\n" // suffix // Inter DD separator // prefix
//			. "\t\t\t<td align='center' width='$columnswidth%'><span class='dsheader'>$rightheader</span></td>\n"
			. "\t\t\t<th>$rightheader</th>\n"
			. $suffix_cell."\t\t</tr>\n"
			. "\t</thead>\n\n"
			. "\t<tbody>\n";

			$trbc = '';
			while ($ansrow = $ansresult->FetchRow())
			{
				$rowname = $ia[1].$ansrow['code'];
				$dualgroup=0;
				$myfname = $ia[1].$ansrow['code']."#".$dualgroup;
				$dualgroup1=1;
				$myfname1 = $ia[1].$ansrow['code']."#".$dualgroup1;

				if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && ($_SESSION[$myfname] == "" || $_SESSION[$myfname1] == "") )
				{
					$answertext="<span class='errormandatory'>".answer_replace($ansrow['answer'])."</span>";
				}
				else
				{
					$answertext=answer_replace($ansrow['answer']);
				}

				$trbc = alternation($trbc , 'row');
				$htmltbody2 = '';
				if (($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == false)  || ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'A'))
				{
					$htmltbody2 = "\n\t<tbody id=\"javatbd$myfname\" style=\"display: none\">\n";
					$hiddenanswers = "\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"off\"  />\n\t\t<input type=\"hidden\" name=\"tbdisp$myfname1\" id=\"tbdisp$myfname1\" value=\"off\" />\n";
				}
				else if (($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'S') || ($htmltbody=arraySearchByKey('array_filter', $qidattributes, 'attribute', 1) && $thissurvey['format'] == 'G' && getArrayFiltersOutGroup($ia[0]) == true))
				{
					$selected = getArrayFiltersForQuestion($ia[0]);
					if (!in_array($ansrow['code'],$selected))
					{
						$htmltbody2 = "\n\t<tbody id=\"javatbd$myfname\" style=\"display: none\">\n";
						$hiddenanswers="\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"off\" />\n\t\t<input type=\"hidden\" name=\"tbdisp$myfname1\" id=\"tbdisp$myfname1\" value=\"off\" />\n";
						$_SESSION[$myfname] = '';
					}
					else
					{
						$htmltbody2 = "\n\t<tbody id=\"javatbd$myfname\" style=\"display: \">";
						$hiddenanswers="\n\t\t<input type=\"hidden\" name=\"tbdisp$myfname\" id=\"tbdisp$myfname\" value=\"on\" />\n\t\t<input type=\"hidden\" name=\"tbdisp$myfname1\" id=\"tbdisp$myfname1\" value=\"on\" />";
					}
				}
				else
				{
				//	$htmltbody2 = "\n\t<tbody>\n";
					$hiddenanswers="";
				}

				$answer .= $htmltbody2."\t\t<tr class=\"$trbc\">\n"
				. "\t\t\t<th class=\"answertext\">\n"
				. "\t\t\t\t<label for=\"answer$rowname\">$answertext</label>\n"
				. "\t\t\t</th>\n";

				// Label0

				// prefix
				if($ddprefix != '')
				{
					$answer .= "\t\t\t<td class=\"ddprefix\">$ddprefix</td>\n";
				}
				$answer .= "\t\t\t<td >\n"
				. "\t\t\t\t<select name=\"$myfname\" id=\"answer$myfname\" onchange=\"special_checkconditions(this.value, this.name, this.type,$dualgroup);\">\n";

				if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] =='')
				{
					$answer .= "\t\t\t\t\t<option value=\"\" ".SELECTED.'>'.$clang->gT('Please choose')."...</option>\n";
				}

				foreach ($labels0 as $lrow)
				{
					$answer .= "\t\t\t\t\t<option value=\"".$lrow['code'].'" ';
					if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $lrow['code'])
					{
						$answer .= SELECTED;
					}
					$answer .= '>'.$lrow['title']."</option>\n";
				}
				// If not mandatory and showanswer, show no ans
				if ($ia[6] != 'Y' && $shownoanswer == 1)
				{
					$answer .= "\t\t\t\t\t<option value=\"\" ";
					if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == '')
					{
						$answer .= SELECTED;
					}
					$answer .= '>'.$clang->gT('No answer')."</option>\n";
				}
				$answer .= "\t\t\t\t</select>\n";

				// suffix
				if($ddsuffix != '')
				{
					$answer .= "\t\t\t<td class=\"ddsuffix\">$ddsuffix</td>\n";
				}
				$answer .= "\t\t\t\t<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
				if (isset($_SESSION[$myfname]))
				{
					$answer .= $_SESSION[$myfname];
				}
				$answer .= "\" />\n"
				. "\t\t\t</td>\n";

				$inputnames[]=$myfname;

				$answer .= "\t\t\t<td class=\"ddarrayseparator\">$interddSep</td>\n"; //Separator

				// Label1

				// prefix
				if($ddprefix != '')
				{
					$answer .= "\t\t\t<td class='ddprefix'>$ddprefix</td>\n";
				}
//				$answer .= "\t\t\t<td align='left' width='$columnswidth%'>\n"
				$answer .= "\t\t\t<td>\n"
				. "\t\t\t\t<select name=\"$myfname1\" id=\"answer$myfname1\" onchange=\"special_checkconditions(this.value, this.name, this.type,$dualgroup1);\">\n";

				if (!isset($_SESSION[$myfname1]) || $_SESSION[$myfname1] =='')
				{
					$answer .= "\t\t\t\t\t<option value=\"\"".SELECTED.'>'.$clang->gT('Please choose')."...</option>\n";
				}

				foreach ($labels1 as $lrow1)
				{
					$answer .= "\t\t\t\t\t<option value=\"".$lrow1['code'].'" ';
					if (isset($_SESSION[$myfname1]) && $_SESSION[$myfname1] == $lrow1['code'])
					{
						$answer .= SELECTED;
					}
					$answer .= '>'.$lrow1['title']."</option>\n";
				}
				// If not mandatory and showanswer, show no ans
				if ($ia[6] != 'Y' && $shownoanswer == 1)
				{
					$answer .= "\t\t\t\t\t<option value='' ";
					if (!isset($_SESSION[$myfname1]) || $_SESSION[$myfname1] == '')
					{
						$answer .= SELECTED;
					}
					$answer .= ">".$clang->gT('No answer')."</option>\n";
				}
				$answer .= "\t\t\t\t</select>\n";

				// suffix
				if($ddsuffix != '')
				{
					$answer .= "\t\t\t<td class=\"ddsuffix\">$ddsuffix</td>\n";
				}
				$answer .= "\t\t\t\t<input type=\"hidden\" name=\"java$myfname1\" id=\"java$myfname1\" value=\"";
				if (isset($_SESSION[$myfname1]))
				{
					$answer .= $_SESSION[$myfname1];
				}
				$answer .= "\" />\n"
				. "\t\t\t</td>\n";
				$inputnames[]=$myfname1;

				$answer .= "\t\t</tr>\n";
			}
		} // End there are answers
		$answer .= "\t</tbody>\n</table>\n\n$hiddenanswers\n";
		$hiddenanswers='';
	}
	else
	{
		$answer = '<span class="error" style="color:#f00">'.$clang->gT('Error: The labelset used for this question is not available in this language and/or does not exist.')."</span>";
		$inputnames="";
	}
	return array($answer, $inputnames);
}





// ---------------------------------------------------------------
function answer_replace($text)
{
	while (strpos($text, "{INSERTANS:") !== false)
	{
		$replace=substr($text, strpos($text, "{INSERTANS:"), strpos($text, "}", strpos($text, "{INSERTANS:"))-strpos($text, "{INSERTANS:")+1);
		$replace2=substr($replace, 11, strpos($replace, "}", strpos($replace, "{INSERTANS:"))-11);
		$replace3=retrieve_Answer($replace2);
		$text=str_replace($replace, $replace3, $text);
	} //while
	return $text;
}



// ---------------------------------------------------------------
function labelset_exists($labelid,$language)
{

	$qulabel = "SELECT * FROM ".db_table_name('labels')." WHERE lid=$labelid AND language='$language'";
	$tablabel = db_execute_assoc($qulabel) or safe_die("Couldn't check for labelset<br />$ansquery<br />".$connect->ErrorMsg()); //Checked
	if ($tablabel->RecordCount()>0) {return true;} else {return false;}
}

?>
