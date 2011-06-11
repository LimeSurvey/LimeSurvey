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
global $thissurvey;

/*
 * Let's explain what this strange $ia var means
 *
 * The $ia string comes from the $_SESSION['insertarray'] variable which is built at the commencement of the survey.
 * See index.php, function "buildsurveysession()"
 * One $ia array exists for every question in the survey. The $_SESSION['insertarray']
 * string is an array of $ia arrays.
 *
 * $ia[0] => question id
 * $ia[1] => fieldname
 * $ia[2] => title
 * $ia[3] => question text
 * $ia[4] => type --  text, radio, select, array, etc
 * $ia[5] => group id
 * $ia[6] => mandatory Y || N
 * $ia[7] => conditions exist for this question
 * $ia[8] => other questions have conditions which rely on this question (including array_filter and array_filter_exclude attributes)
 * $ia[9] => incremental question count (used by {QUESTION_NUMBER})
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

if($shownoanswer > 0 && $thissurvey['shownoanswer'] != 'N')
{
    define('SHOW_NO_ANSWER',1);
}
else
{
    define('SHOW_NO_ANSWER',0);
};
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
        ."{$dbprefix}conditions.method, "
        ."questionssrc.gid as srcgid "
        ."FROM {$dbprefix}conditions, "
        ."{$dbprefix}questions ,"
        ."{$dbprefix}questions as questionssrc "
        ."WHERE {$dbprefix}conditions.cqid={$dbprefix}questions.qid "
        ."AND {$dbprefix}conditions.qid=questionssrc.qid "
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
        ."{$dbprefix}conditions.method,"
        ."questionssrc.gid as srcgid "
        ."FROM {$dbprefix}conditions, {$dbprefix}questions as questionssrc "
        ."WHERE {$dbprefix}conditions.qid=questionssrc.qid "
        ."AND {$dbprefix}conditions.qid=$ia[0] "
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
            $crow['scenario'],
            $crow['srcgid']);
        }


        foreach ($conditions as $condkey => $condarr)
        { // We need to sort the merged array by Ascending scenario, cqid, then cfieldname
          // otherwise condition evaluations of Tokens in Scenarii won't work in "live" Javascript eval
            $scenariolist[$condkey] = $condarr[7];
            $cqidlist[$condkey] = $condarr[1];
            $cfieldnamelist[$condkey] = $condarr[2];
        }
        array_multisort($scenariolist,SORT_ASC,$cqidlist,SORT_ASC,$cfieldnamelist,SORT_ASC,$conditions);

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
    global $dbprefix, $connect, $dropdownthreshold;

    if (preg_match("/^\+{0,1}[0-9]+X([0-9]+)X(.*)$/",$cd[2]) == 0)
    { // This is not a true fieldname (for instance a {TOKEN:ATTR..}
      // placeholder
        return "NoJSidname";
    }
    //preg_match("/^[0-9]+X([0-9]+)X([0-9]+)$/",$cd[2],$matchFields);
    //^^^^^does not seem to work, explode below should
    $matchFields = explode('X', $cd[2], 3);
    $questiongid=$matchFields[1];
    $questionFieldpart=$matchFields[2];


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
        //$idname="fvalue_".$cd[1].substr($cd[2], strlen($cd[2])-1,1); // broken when ranking more than 9 items
            $idname="fvalue_".$questionFieldpart;
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
    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);

    if (trim($qidattributes['max_answers'])!='') {
        $max_answers = $qidattributes['max_answers'];
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
    $qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."' and parent_qid=0";
    $qresult = db_execute_assoc($qquery);     //Checked
    while ($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}
    $subquestionquery = "SELECT title FROM {$dbprefix}questions WHERE parent_qid={$ia[0]} AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
    $sqresult = db_execute_assoc($subquestionquery); //Checked

    while ($subquestionrow = $sqresult->FetchRow())
    {
        $mandatorys[]=$ia[1].$subquestionrow['title'];
        $mandatoryfns[]=$ia[1];
    }

    if ($other == "Y" and ($ia[4]=="!" or $ia[4]=="L" or $ia[4]=="M" or $ia[4]=="P"))
    {
        $mandatorys[]=$ia[1]."other";
        $mandatoryfns[]=$ia[1];
    }

    return array($mandatorys, $mandatoryfns);
}


/**
 * The point of these functions (setman) is to return an array containing two arrays.
 * The first ($mandatorys) is an array containing question, so they can all be checked
 * The second ($mandatoryfns) is an arry containing the fieldnames of every question
 * What's the difference? The difference arises from multiple option questions, and came
 * about when trying to distinguish between answering just one option (which satisfies
 * the mandatory requirement, and answering them all). The "mandatorys" input contains the
 * actual specific response items that could be filled in.. ie: in a multiple option
 * question, there will be a unique one for every possible answer. The "mandatoryfns" array
 * contains the generic question fieldname for the question as a whole (it will be repeated
 *  for multiple option qeustions, but won't contain unique items.
 *
 * @param mixed $ia
 * @return array See explanation above
 */
function setman_multiflex($ia)
{

    global $dbprefix, $connect;

    $mandatorys=array();
    $mandatoryfns=array();
    $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid={$ia[0]} AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY question_order, title";
    $ansresult = db_execute_assoc($ansquery);
    $ans2query = "SELECT * FROM {$dbprefix}questions WHERE parent_qid={$ia[0]} AND language='".$_SESSION['s_lang']."' and scale_id=1 ORDER BY question_order, title";
    $ans2result = db_execute_assoc($ans2query);

    while ($ans2row=$ans2result->FetchRow())
    {
        $lset[]=$ans2row;
    }

    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    while ($ansrow = $ansresult->FetchRow())
    {
        //Don't add to mandatory list if the row is filtered out with the array_filter option
        if (trim($qidattributes['array_filter'])!='')
        {
            //This particular one may not be mandatory if it's hidden
            $selected = getArrayFiltersForQuestion($ia[0]);
            if (!in_array($ansrow['title'],$selected))
            {
                //This one's hidden, so don't add it to the mandatory list
            }
            else
            {
                //This one's not hidden. so add it to the mandatory list
                foreach($lset as $ls)
                {
                    $mandatorys[]=$ia[1].$ansrow['title']."_".$ls['title'];
                    $mandatoryfns[]=$ia[1];
                }
            }
        }
        elseif (trim($qidattributes['array_filter_exclude'])!='')
        {
            //This particular one may not be mandatory if it's hidden
            $selected = getArrayFilterExcludesForQuestion($ia[0]);
            if ($selected!=false && in_array($ansrow['title'],$selected))
            {
                //This one's hidden, so don't add it to the mandatory list
            }
            else
            {
                //This one's not hidden. so add it to the mandatory list
                foreach($lset as $ls)
                {
                    $mandatorys[]=$ia[1].$ansrow['title']."_".$ls['title'];
                    $mandatoryfns[]=$ia[1];
                }
            }
        } else { //There is no array_filter option, so we should definitely add to the mandatory list here!
            foreach($lset as $ls)
            {
                $mandatorys[]=$ia[1].$ansrow['title']."_".$ls['title'];
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
    while ($qrow = $qresult->FetchRow())
    {
        $other = $qrow['other'];
    }

    // Get Subquestions
    $subquery="SELECT * "
            ."FROM {$dbprefix}questions "
            ."WHERE parent_qid={$ia[0]} "
            ."AND language='".$_SESSION['s_lang']."' "
            ."ORDER BY question_order";
    $subresult = db_execute_assoc($subquery); //Checked

    // Get Answer Scale 1
    $ans1query="SELECT qid "
            ."FROM {$dbprefix}answers "
            ."WHERE qid={$ia[0]} "
            ."AND scale_id=0 "
            ."AND language='".$_SESSION['s_lang']."' "
            ."ORDER BY sortorder";
    $ans1result = db_execute_assoc($ans1query);   //Checked
    $ans1count = $ans1result->RowCount();

    // Get Answer Scale 2
    $ans2query="SELECT qid "
            ."FROM {$dbprefix}answers "
            ."WHERE qid={$ia[0]} "
            ."AND scale_id=1 "
            ."AND language='".$_SESSION['s_lang']."' "
            ."ORDER BY sortorder";
    $ans2result = db_execute_assoc($ans2query);   //Checked
    $ans2count = $ans2result->RowCount();

    while ($subrow = $subresult->FetchRow())
    {
        // first answer set
        if ($ans1count > 0)
        {
            $mandatorys[]=$ia[1].$subrow['title']."#0";
            $mandatoryfns[]=$ia[1];
        }
        else
        {
            $mandatorys[]=$ia[1].$subrow['title'];
            $mandatoryfns[]=$ia[1];
        }

        // second answer set
        if ($ans2count > 0)
        {
            $mandatorys[]=$ia[1].$subrow['title']."#1";
            $mandatoryfns[]=$ia[1];
        }
        else
        {
            $mandatorys[]=$ia[1].$subrow['title'];
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

/**
 * This function returns an array containing the "question/answer" html display
 * and a list of the question/answer fieldnames associated. It is called from
 * question.php, group.php or survey.php
 *
 * @param mixed $ia
 * @param mixed $notanswered
 * @param mixed $notvalidated
 * @param mixed $filenotvalidated
 * @return mixed
 */
function retrieveAnswers($ia, $notanswered=null, $notvalidated=null, $filenotvalidated=null)
{
    //globalise required config variables
    global $dbprefix, $clang; //These are from the config-defaults.php file
    global $thissurvey, $gl; //These are set by index.php
    global $connect;

    //DISPLAY
    $display = $ia[7];

    //QUESTION NAME
    $name = $ia[0];

    $qtitle=$ia[3];
    //Replace INSERTANS statements with previously provided answers;
    $qtitle=dTexts::run($qtitle);


    //GET HELP
    $hquery="SELECT help FROM {$dbprefix}questions WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."'";
    $hresult=db_execute_num($hquery) or safe_die($connect->ErrorMsg());       //Checked
    $help="";
    while ($hrow=$hresult->FetchRow()) {$help=$hrow[0];}

    //A bit of housekeeping to stop PHP Notices
    $answer = "";
    if (!isset($_SESSION[$ia[1]])) {$_SESSION[$ia[1]] = "";}
    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    //echo "<pre>";print_r($qidattributes);echo "</pre>";
    //Create the question/answer html

    // Previously in limesurvey, it was virtually impossible to control how the start of questions were formatted.
    // this is an attempt to allow users (or rather system admins) some control over how the starting text is formatted.
    $number = isset($ia[9]) ? $ia[9] : '';

    $question_text = array(
				 'all' => '' // All has been added for backwards compatibility with templates that use question_start.pstpl (now redundant)
    ,'text' => $qtitle
    ,'code' => $ia[2]
    ,'number' => $number
    ,'help' => ''
    ,'mandatory' => ''
    ,'man_message' => ''
    ,'valid_message' => ''
    ,'file_valid_message' => ''
    ,'class' => ''
    ,'man_class' => ''
    ,'input_error_class' => ''// provides a class.
    ,'essentials' => ''
    );

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
        case 'L': //LIST drop-down/radio-button list
            $values = do_list_radio($ia);
            if ($qidattributes['hide_tip']==0)
            {
                $qtitle .= "<br />\n<span class=\"questionhelp\">"
                . $clang->gT('Choose one of the following answers').'</span>';
                $question_text['help'] = $clang->gT('Choose one of the following answers');
            }
            break;
        case '!': //List - dropdown
            $values=do_list_dropdown($ia);
            if ($qidattributes['hide_tip']==0)
            {
                $qtitle .= "<br />\n<span class=\"questionhelp\">"
                . $clang->gT('Choose one of the following answers').'</span>';
                $question_text['help'] = $clang->gT('Choose one of the following answers');
            }
            break;
        case 'O': //LIST WITH COMMENT drop-down/radio-button list + textarea
            $values=do_listwithcomment($ia);
            if (count($values[1]) > 1 && $qidattributes['hide_tip']==0)
            {
                $qtitle .= "<br />\n<span class=\"questionhelp\">"
                . $clang->gT('Choose one of the following answers').'</span>';
                $question_text['help'] = $clang->gT('Choose one of the following answers');
            }
            break;
        case 'R': //RANKING STYLE
            $values=do_ranking($ia);
            if (count($values[1]) > 1 && $qidattributes['hide_tip']==0)
            {
                $question_text['help'] = $clang->gT("Click on an item in the list on the left, starting with your highest ranking item, moving through to your lowest ranking item.");
                if (trim($qidattributes['min_answers'])!='')
                {
                    $qtitle .= "<br />\n<span class=\"questionhelp\">"
                    . sprintf($clang->ngT("Check at least %d item","Check at least %d items",$qidattributes['min_answers']),$qidattributes['min_answers'])."</span>";
                    $question_text['help'] .=' '.sprintf($clang->ngT("Check at least %d item","Check at least %d items",$qidattributes['min_answers']),$qidattributes['min_answers']);
                }
            }
            break;
        case 'M': //Multiple choice checkbox
            $values=do_multiplechoice($ia);
            if (count($values[1]) > 1 && $qidattributes['hide_tip']==0)
            {
                $maxansw=trim($qidattributes['max_answers']);
                $minansw=trim($qidattributes['min_answers']);
                if (!($maxansw || $minansw))
                {
                    $qtitle .= "<br />\n<span class=\"questionhelp\">"
                    . $clang->gT('Check any that apply').'</span>';
                    $question_text['help'] = $clang->gT('Check any that apply');
                }
                else
                {
                    if ($maxansw && $minansw)
                    {
                        $qtitle .= "<br />\n<span class=\"questionhelp\">"
                        . sprintf($clang->gT("Check between %d and %d answers"), $minansw, $maxansw)."</span>";
                        $question_text['help'] = sprintf($clang->gT("Check between %d and %d answers"), $minansw, $maxansw);
                    } elseif ($maxansw)
                    {
                        $qtitle .= "<br />\n<span class=\"questionhelp\">"
                        . sprintf($clang->gT("Check at most %d answers"), $maxansw)."</span>";
                        $question_text['help'] = sprintf($clang->gT("Check at most %d answers"), $maxansw);
                    } else
                    {
                        $qtitle .= "<br />\n<span class=\"questionhelp\">"
                        . sprintf($clang->ngT("Check at least %d answer","Check at least %d answers",$minansw),$minansw)."</span>";
                        $question_text['help'] = sprintf($clang->ngT("Check at least %d answer","Check at least %d answers",$minansw),$minansw);
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
                $question_text['help'] = $clang->gT('Choose your language');
            }
            break;
        case 'P': //Multiple choice with comments checkbox + text
            $values=do_multiplechoice_withcomments($ia);
            if (count($values[1]) > 1 && $qidattributes['hide_tip']==0)
            {
                $maxansw=trim($qidattributes["max_answers"]);
                $minansw=trim($qidattributes["min_answers"]);
                if (!($maxansw || $minansw))
                {
                    $qtitle .= "<br />\n<span class=\"questionhelp\">"
                    . $clang->gT('Check any that apply').'</span>';
                    $question_text['help'] = $clang->gT('Check any that apply');
                }
                else
                {
                    if ($maxansw && $minansw)
                    {
                        $qtitle .= "<br />\n<span class=\"questionhelp\">"
                        . sprintf($clang->gT("Check between %d and %d answers"), $minansw, $maxansw)."</span>";
                        $question_text['help'] = sprintf($clang->gT("Check between %d and %d answers"), $minansw, $maxansw);
                    } elseif ($maxansw)
                    {
                        $qtitle .= "<br />\n<span class=\"questionhelp\">"
                        . sprintf($clang->gT("Check at most %d answers"), $maxansw)."</span>";
                        $question_text['help'] = sprintf($clang->gT("Check at most %d answers"), $maxansw);
                    } else
                    {
                        $qtitle .= "<br />\n<span class=\"questionhelp\">"
                        . sprintf($clang->gT("Check at least %d answers"), $minansw)."</span>";
                        $question_text['help'] = sprintf($clang->gT("Check at least %d answers"), $minansw);
                    }
                }
            }
            break;
        case '|': //File Upload
            $values=do_file_upload($ia);
            if ($qidattributes['min_num_of_files'] != 0)
            {
                if (trim($qidattributes['min_num_of_files']) != 0)
                {
                    $qtitle .= "<br />\n<span class = \"questionhelp\">"
                    .sprintf($clang->gT("At least %d files must be uploaded for this question"), $qidattributes['min_num_of_files'])."<span>";
                    $question_text['help'] .= ' '.sprintf($clang->gT("At least %d files must be uploaded for this question"), $qidattributes['min_num_of_files']);
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
            $values=do_array($ia);
            break;
        case 'H': //ARRAY (Flexible) - Column Format
            $values=do_arraycolumns($ia);
            break;
        case ':': //ARRAY (Multi Flexi) 1 to 10
            $values=do_array_multiflexi($ia);
            break;
        case ';': //ARRAY (Multi Flexi) Text
            $values=do_array_multitext($ia);  //It's like the "5th element" movie, come to life
            break;
        case '1': //Array (Flexible Labels) dual scale
            $values=do_array_dual($ia);
            break;
    } //End Switch

    if (isset($values)) //Break apart $values array returned from switch
    {
        //$answer is the html code to be printed
        //$inputnames is an array containing the names of each input field
        list($answer, $inputnames)=$values;
    }

    $answer .= "\n\t<input type='hidden' name='display$ia[1]' id='display$ia[0]' value='";
    $answer .= 'on'; //If this is single format, then it must be showing. Needed for checking conditional mandatories
    $answer .= "' />\n"; //for conditional mandatory questions

    if ($ia[6] == 'Y')
    {
        $qtitle = '<span class="asterisk">'.$clang->gT('*').'</span>'.$qtitle;
        $question_text['mandatory'] = $clang->gT('*');
    }
    //If this question is mandatory but wasn't answered in the last page
    //add a message HIGHLIGHTING the question
    $qtitle .= mandatory_message($ia);
    $question_text['man_message'] = mandatory_message($ia);

    $qtitle .= validation_message($ia);
    $question_text['valid_message'] = validation_message($ia);

    $qtitle .= $ia[4] == "|" ? file_validation_message($ia) : "";
    $question_text['file_valid_message'] = $ia[4] == "|" ? file_validation_message($ia) : "";

    if(!empty($question_text['man_message']) || !empty($question_text['valid_message']) || !empty($question_text['file_valid_message']))
    {
        $question_text['input_error_class'] = ' input-error';// provides a class to style question wrapper differently if there is some kind of user input error;
    }

    // =====================================================
    // START: legacy question_start.pstpl code
    // The following section adds to the templating system by allowing
    // templaters to control where the various parts of the question text
    // are put.

    if(is_file('templates/'.validate_templatedir($thissurvey['template']).'/question_start.pstpl'))
    {
        $qtitle_custom = '';

        $replace=array();
        foreach($question_text as $key => $value)
        {
            $find[] = '{QUESTION_'.strtoupper($key).'}'; // Match key words from template
            $replace[] = $value; // substitue text
        };
        if(!defined('QUESTION_START'))
        {
            define('QUESTION_START' , file_get_contents(sGetTemplatePath($thissurvey['template']).'/question_start.pstpl' , true));
        };
        $qtitle_custom = str_replace( $find , $replace , QUESTION_START);

        $c = 1;
        // START: <EMBED> work-around step 1
        $qtitle_custom = preg_replace( '/(<embed[^>]+>)(<\/embed>)/i' , '\1NOT_EMPTY\2' , $qtitle_custom );
        // END <EMBED> work-around step 1
        while($c > 0) // This recursively strips any empty tags to minimise rendering bugs.
        {
            $matches = 0;
            $oldtitle=$qtitle_custom;
            $qtitle_custom = preg_replace( '/<([^ >]+)[^>]*>[\r\n\t ]*<\/\1>[\r\n\t ]*/isU' , '' , $qtitle_custom , -1); // I removed the $count param because it is PHP 5.1 only.

            $c = ($qtitle_custom!=$oldtitle)?1:0;
        };
        // START <EMBED> work-around step 2
        $qtitle_custom = preg_replace( '/(<embed[^>]+>)NOT_EMPTY(<\/embed>)/i' , '\1\2' , $qtitle_custom );
        // END <EMBED> work-around step 2
        while($c > 0) // This recursively strips any empty tags to minimise rendering bugs.
        {
            $matches = 0;
            $oldtitle=$qtitle_custom;
            $qtitle_custom = preg_replace( '/(<br(?: ?\/)?>(?:&nbsp;|\r\n|\n\r|\r|\n| )*)+$/i' , '' , $qtitle_custom , -1 ); // I removed the $count param because it is PHP 5.1 only.
            $c = ($qtitle_custom!=$oldtitle)?1:0;
        };

        //		$qtitle = $qtitle_custom;
        $question_text['all'] = $qtitle_custom;
    }
    else
    {
        $question_text['all'] = $qtitle;
    };
    // END: legacy question_start.pstpl code
    //===================================================================
    //	echo '<pre>[qanda.php] line '.__LINE__.": $question_text =\n".htmlspecialchars(print_r($question_text,true)).'</pre>';
    $qtitle = $question_text;
    // =====================================================

    $qanda=array($qtitle, $answer, $help, $display, $name, $ia[2], $gl[0], $ia[1] );
    //New Return
    return array($qanda, $inputnames);
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
                    $qtitle .= ' '.$clang->gT('Please check at least one item.').'.';
                    $qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0];
                    $qresult = db_execute_assoc($qquery);    //Checked
                    $qrow = $qresult->FetchRow();
                    if ($qrow['other']=='Y')
                    {
                        $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
                        if (trim($qidattributes['other_replace_text'])!='')
                        {
                            $othertext=$qidattributes['other_replace_text'];
                        }
                        else
                        {
                            $othertext=$clang->gT('Other:');
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
                $help=' <span class="questionhelp">'.$helprow['help'].'</span>';
            }
            $qtitle .= '<br /><span class="errormandatory">'.$clang->gT('This question must be answered correctly').'.'.$help.'</span><br />
';
        }
    }

    return $qtitle;
}

function file_validation_message($ia)
{
    global $filenotvalidated, $clang;
    $qtitle = "";
    if (isset($filenotvalidated) && is_array($filenotvalidated) && $ia[4] == "|")
    {
        global $filevalidationpopup, $popup;

        foreach ($filenotvalidated as $k => $v)
        {
            if ($ia[1] == $k || strpos($k, "_") && $ia[1] == substr(0, strpos($k, "_") - 1));
                $qtitle .= '<br /><span class="errormandatory">'.$clang->gT($filenotvalidated[$k]).'</span><br />';
        }
    }
    return $qtitle;
}

function mandatory_popup($ia, $notanswered=null)
{
    global $showpopups;
    //This sets the mandatory popup message to show if required
    //Called from question.php, group.php or survey.php
    if ($notanswered === null) {unset($notanswered);}
    if (isset($notanswered) && is_array($notanswered) && isset($showpopups) && $showpopups == 1) //ADD WARNINGS TO QUESTIONS IF THEY WERE MANDATORY BUT NOT ANSWERED
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
                        alert(\"".$clang->gT("One or more mandatory questions have not been answered. You cannot proceed until these have been completed.", "js")."\");});\n //-->\n
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
    global $showpopups;
    //This sets the validation popup message to show if required
    //Called from question.php, group.php or survey.php
    if ($notvalidated === null) {unset($notvalidated);}
    $qtitle="";
    if (isset($notvalidated) && is_array($notvalidated) && isset($showpopups) && $showpopups == 1)  //ADD WARNINGS TO QUESTIONS IF THEY ARE NOT VALID
    {
        global $validationpopup, $vpopup, $clang;
        //POPUP WARNING
        if (!isset($validationpopup))
        {
            $vpopup="<script type=\"text/javascript\">\n
                    <!--\n $(document).ready(function(){
                        alert(\"".$clang->gT("One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid.", "js")."\");});\n //-->\n
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

function file_validation_popup($ia, $filenotvalidated = null)
{
    global $showpopups;
    if ($filenotvalidated === null) { unset($filenotvalidated); }
    if (isset($filenotvalidated) && is_array($filenotvalidated) && isset($showpopups) && $showpopups == 1)
    {
        global $filevalidationpopup, $fpopup, $clang;

        if (!isset($filevalidationpopup))
        {
            $fpopup="<script type=\"text/javascript\">\n
                    <!--\n $(document).ready(function(){
                        alert(\"".$clang->gT("One or more file have either exceeded the filesize/are not in the right format or the minimum number of required files have not been uploaded. You cannot proceed until these have been completed", "js")."\");});\n //-->\n
                    </script>\n";
            $filevalidationpopup = "Y";
        }
        return array($filevalidationpopup, $fpopup);
    }
    else
        return false;
}

function return_timer_script($qidattributes, $ia, $disable=null) {
    global $thissurvey, $clang;

    /* The following lines cover for previewing questions, because no $_SESSION['fieldarray'] exists.
     This just stops error messages occuring */
    if(!isset($_SESSION['fieldarray']))
    {
        $_SESSION['fieldarray'] = array();
    }
    /* End */

    if(isset($thissurvey['timercount']))
    {
        $thissurvey['timercount']++; //Used to count how many timer questions in a page, and ensure scripts only load once
    } else {
        $thissurvey['timercount']=1;
    }

    if($thissurvey['format'] != "S")
    {
        if($thissurvey['format'] != "G")
        {
            return "\n\n<!-- TIMER MODE DISABLED DUE TO INCORRECT SURVEY FORMAT -->\n\n";
            //We don't do the timer in any format other than question-by-question
        }
    }

    $time_limit=$qidattributes['time_limit'];

    $disable_next=trim($qidattributes['time_limit_disable_next']) != '' ? $qidattributes['time_limit_disable_next'] : 0;
    $disable_prev=trim($qidattributes['time_limit_disable_prev']) != '' ? $qidattributes['time_limit_disable_prev'] : 0;
    $time_limit_action=trim($qidattributes['time_limit_action']) != '' ? $qidattributes['time_limit_action'] : 1;
    $time_limit_message_delay=trim($qidattributes['time_limit_message_delay']) != '' ? $qidattributes['time_limit_message_delay']*1000 : 1000;
    $time_limit_message=trim($qidattributes['time_limit_message']) != '' ? htmlspecialchars($qidattributes['time_limit_message'], ENT_QUOTES) : $clang->gT("Your time to answer this question has expired");
    $time_limit_warning=trim($qidattributes['time_limit_warning']) != '' ? $qidattributes['time_limit_warning'] : 0;
    $time_limit_warning_2=trim($qidattributes['time_limit_warning_2']) != '' ? $qidattributes['time_limit_warning_2'] : 0;
    $time_limit_countdown_message=trim($qidattributes['time_limit_countdown_message']) != '' ? htmlspecialchars($qidattributes['time_limit_countdown_message'], ENT_QUOTES) : $clang->gT("Time remaining");
    $time_limit_warning_message=trim($qidattributes['time_limit_warning_message']) != '' ? htmlspecialchars($qidattributes['time_limit_warning_message'], ENT_QUOTES) : $clang->gT("Your time to answer this question has nearly expired. You have {TIME} remaining.");
    $time_limit_warning_message=str_replace("{TIME}", "<div style='display: inline' id='LS_question".$ia[0]."_Warning'> </div>", $time_limit_warning_message);
    $time_limit_warning_display_time=trim($qidattributes['time_limit_warning_display_time']) != '' ? $qidattributes['time_limit_warning_display_time']+1 : 0;
    $time_limit_warning_2_message=trim($qidattributes['time_limit_warning_2_message']) != '' ? htmlspecialchars($qidattributes['time_limit_warning_2_message'], ENT_QUOTES) : $clang->gT("Your time to answer this question has nearly expired. You have {TIME} remaining.");
    $time_limit_warning_2_message=str_replace("{TIME}", "<div style='display: inline' id='LS_question".$ia[0]."_Warning_2'> </div>", $time_limit_warning_2_message);
    $time_limit_warning_2_display_time=trim($qidattributes['time_limit_warning_2_display_time']) != '' ? $qidattributes['time_limit_warning_2_display_time']+1 : 0;
    $time_limit_message_style=trim($qidattributes['time_limit_message_style']) != '' ? $qidattributes['time_limit_message_style'] : "position: absolute;
        top: 10px;
        left: 35%;
        width: 30%;
        height: 60px;
        padding: 16px;
        border: 8px solid #555;
        background-color: white;
        z-index:1002;
		text-align: center;
        overflow: auto;";
    $time_limit_message_style.="\n		display: none;"; //Important to hide time limit message at start
    $time_limit_warning_style=trim($qidattributes['time_limit_warning_style']) != '' ? $qidattributes['time_limit_warning_style'] : "position: absolute;
        top: 10px;
        left: 35%;
        width: 30%;
        height: 60px;
        padding: 16px;
        border: 8px solid #555;
        background-color: white;
        z-index:1001;
		text-align: center;
        overflow: auto;";
    $time_limit_warning_style.="\n		display: none;"; //Important to hide time limit warning at the start
    $time_limit_warning_2_style=trim($qidattributes['time_limit_warning_2_style']) != '' ? $qidattributes['time_limit_warning_2_style'] : "position: absolute;
        top: 10px;
        left: 35%;
        width: 30%;
        height: 60px;
        padding: 16px;
        border: 8px solid #555;
        background-color: white;
        z-index:1001;
		text-align: center;
        overflow: auto;";
    $time_limit_warning_2_style.="\n		display: none;"; //Important to hide time limit warning at the start
    $time_limit_timer_style=trim($qidattributes['time_limit_timer_style']) != '' ? $qidattributes['time_limit_timer_style'] : "position: relative;
		width: 150px;
		margin-left: auto;
		margin-right: auto;
		border: 1px solid #111;
		text-align: center;
		background-color: #EEE;
		margin-bottom: 5px;
		font-size: 8pt;";
    $timersessionname="timer_question_".$ia[0];
    if(isset($_SESSION[$timersessionname])) {
        $time_limit=$_SESSION[$timersessionname];
    }

    $output = "
	<input type='hidden' name='timerquestion' value='".$timersessionname."' />
	<input type='hidden' name='".$timersessionname."' id='".$timersessionname."' value='".$time_limit."' />\n";
    if($thissurvey['timercount'] < 2)
    {
        $output .="
    <script type='text/javascript'>
	<!--
		function freezeFrame(elementid) {
			if(document.getElementById(elementid) !== null) {
				var answer=document.getElementById(elementid);
				if(answer.value == '') {
					answer.value=' ';
				}
				answer.blur();
				answer.onfocus=function() {answer.blur();};
			}
		};
	//-->
	</script>";
        $output .= "
    <script type='text/javascript'>
	<!--\n
		function countdown(questionid,timer,action,warning,warning2,warninghide,warning2hide,disable){
		    if(!timeleft) {var timeleft=timer;}
			if(!warning) {var warning=0;}
			if(!warning2) {var warning2=0;}
			if(!warninghide) {var warninghide=0;}
			if(!warning2hide) {var warning2hide=0;}";

        if($thissurvey['format'] == "G")
        {
            global $gid;
            $qcount=0;
            foreach($_SESSION['fieldarray'] as $ib)
            {
                if($ib[5] == $gid)
                {
                    $qcount++;
                }
            }
            //Override all other options and just allow freezing, survey is presented in group by group mode
            if($qcount > 1) {
                $output .="
					action = 3;";
            }
        }
        $output .="
			var timerdisplay='LS_question'+questionid+'_Timer';
			var warningtimedisplay='LS_question'+questionid+'_Warning';
			var warningdisplay='LS_question'+questionid+'_warning';
			var warning2timedisplay='LS_question'+questionid+'_Warning_2';
			var warning2display='LS_question'+questionid+'_warning_2';
			var expireddisplay='question'+questionid+'_timer';
			var timersessionname='timer_question_'+questionid;
			document.getElementById(timersessionname).value=timeleft;
			timeleft--;
			cookietimer=subcookiejar.fetch('limesurvey_timers',timersessionname);
			if(cookietimer) {
				if(cookietimer <= timeleft) {
				  timeleft=cookietimer;
				}
			}
			var timeleftobject=new Object();
			subcookiejar.crumble('limesurvey_timers', timersessionname);
			timeleftobject[timersessionname]=timeleft;
			subcookiejar.bake('limesurvey_timers', timeleftobject, 7)\n";
        if($disable_next > 0) {
            $output .= "
		if(document.getElementById('movenextbtn') !== null && timeleft > $disable_next) {
			document.getElementById('movenextbtn').disabled=true;
		} else if (document.getElementById('movenextbtn') !== null && $disable_next > 1 && timeleft <= $disable_next) {
		    document.getElementById('movenextbtn').disabled=false;
		}\n";
        }
        if($disable_prev > 0) {
            $output .= "
		if(document.getElementById('moveprevbtn') !== null && timeleft > $disable_prev) {
			document.getElementById('moveprevbtn').disabled=true;
		} else if (document.getElementById('moveprevbtn') !== null && $disable_prev > 1 && timeleft <= $disable_prev) {
		    document.getElementById('moveprevbtn').disabled=false;
		}\n";
        }
        if(!is_numeric($disable_prev)) {
            $output .= "
		if(document.getElementById('moveprevbtn') !== null) {
			document.getElementById('moveprevbtn').disabled=true;
		}\n";
        }
        $output .="
			if(warning > 0 && timeleft<=warning) {
			  var wsecs=warning%60;
			  if(wsecs<10) wsecs='0' + wsecs;
			  var WT1 = (warning - wsecs) / 60;
			  var wmins = WT1 % 60; if (wmins < 10) wmins = '0' + wmins;
			  var whours = (WT1 - wmins) / 60;
			  var dmins=''
			  var dhours=''
			  var dsecs=''
			  if (whours < 10) whours = '0' + whours;
			  if (whours > 0) dhours = whours + ' ".$clang->gT('hours').", ';
			  if (wmins > 0) dmins = wmins + ' ".$clang->gT('mins').", ';
			  if (wsecs > 0) dsecs = wsecs + ' ".$clang->gT('seconds')."';
			  if(document.getElementById(warningtimedisplay) !== null) {
			      document.getElementById(warningtimedisplay).innerHTML = dhours+dmins+dsecs;
			  }
			  document.getElementById(warningdisplay).style.display='';
			}
			if(warning2 > 0 && timeleft<=warning2) {
			  var w2secs=warning2%60;
			  if(wsecs<10) w2secs='0' + wsecs;
			  var W2T1 = (warning2 - w2secs) / 60;
			  var w2mins = W2T1 % 60; if (w2mins < 10) w2mins = '0' + w2mins;
			  var w2hours = (W2T1 - w2mins) / 60;
			  var d2mins=''
			  var d2hours=''
			  var d2secs=''
			  if (w2hours < 10) w2hours = '0' + w2hours;
			  if (w2hours > 0) d2hours = w2hours + ' ".$clang->gT('hours').", ';
			  if (w2mins > 0) d2mins = w2mins + ' ".$clang->gT('mins').", ';
			  if (w2secs > 0) d2secs = w2secs + ' ".$clang->gT('seconds')."';
			  if(document.getElementById(warning2timedisplay) !== null) {
			      document.getElementById(warning2timedisplay).innerHTML = dhours+dmins+dsecs;
			  }
			  document.getElementById(warning2display).style.display='';
			}
			if(warning > 0 && warninghide > 0 && document.getElementById(warningdisplay).style.display != 'none') {
			  if(warninghide == 1) {
			    document.getElementById(warningdisplay).style.display='none';
			    warning=0;
			  }
			  warninghide--;
			}
			if(warning2 > 0 && warning2hide > 0 && document.getElementById(warning2display).style.display != 'none') {
			  if(warning2hide == 1) {
			    document.getElementById(warning2display).style.display='none';
			    warning2=0;
			  }
			  warning2hide--;
			}
			var secs = timeleft % 60;
			if (secs < 10) secs = '0'+secs;
			var T1 = (timeleft - secs) / 60;
			var mins = T1 % 60; if (mins < 10) mins = '0'+mins;
			var hours = (T1 - mins) / 60;
			if (hours < 10) hours = '0'+hours;
			var d2hours='';
			var d2mins='';
			var d2secs='';
			if (hours > 0) d2hours = hours+' ".$clang->gT('hours').": ';
			if (mins > 0) d2mins = mins+' ".$clang->gT('mins').": ';
			if (secs > 0) d2secs = secs+' ".$clang->gT('seconds')."';
			if (secs < 1) d2secs = '0 ".$clang->gT('seconds')."';
			document.getElementById(timerdisplay).innerHTML = '".$time_limit_countdown_message."<br />'+d2hours + d2mins + d2secs;
			if (timeleft>0){
				var text='countdown('+questionid+', '+timeleft+', '+action+', '+warning+', '+warning2+', '+warninghide+', '+warning2hide+', \"'+disable+'\")';
				setTimeout(text,1000);
			} else {
			    //Countdown is finished, now do action
				switch(action) {
					case 2: //Just move on, no warning
						if(document.getElementById('movenextbtn') !== null) {
						    if(document.getElementById('movenextbtn').disabled==true) document.getElementById('movenextbtn').disabled=false;
						}
						if(document.getElementById('moveprevbtn') !== null) {
							if(document.getElementById('moveprevbtn').disabled==true && '$disable_prev' > 0) document.getElementById('moveprevbtn').disabled=false;
						}
						freezeFrame(disable);
						subcookiejar.crumble('limesurvey_timers', timersessionname);
						if(document.getElementById('movenextbtn') != null) {
						  document.limesurvey.submit();
						} else {
							setTimeout(\"document.limesurvey.submit();\", 1000);
						}
						break;
					case 3: //Just warn, don't move on
						document.getElementById(expireddisplay).style.display='';
						if(document.getElementById('movenextbtn') !== null) {
						    if(document.getElementById('movenextbtn').disabled==true) document.getElementById('movenextbtn').disabled=false;
						}
						if(document.getElementById('moveprevbtn') !== null) {
						    if(document.getElementById('moveprevbtn').disabled==true && '$disable_prev' > 0) document.getElementById('moveprevbtn').disabled=false;
						}
						freezeFrame(disable);
						this.onsubmit=function() {subcookiejar.crumble('limesurvey_timers', timersessionname);};
						break;
					default: //Warn and move on
						document.getElementById(expireddisplay).style.display='';
						if(document.getElementById('movenextbtn') !== null) {
						    if(document.getElementById('movenextbtn').disabled==true) document.getElementById('movenextbtn').disabled=false;
						}
						if(document.getElementById('moveprevbtn') !== null) {
						    if(document.getElementById('moveprevbtn').disabled==true && '$disable_prev' > 0) document.getElementById('moveprevbtn').disabled=false;
						}
						freezeFrame(disable);
						subcookiejar.crumble('limesurvey_timers', timersessionname);
						setTimeout('document.limesurvey.submit()', ".$time_limit_message_delay.");
						break;
				}
			}
		}
	//-->
	</script>";
    }
    $output .= "<div id='question".$ia[0]."_timer' style='".$time_limit_message_style."'>".$time_limit_message."</div>\n\n";

    $output .= "<div id='LS_question".$ia[0]."_warning' style='".$time_limit_warning_style."'>".$time_limit_warning_message."</div>\n\n";
    $output .= "<div id='LS_question".$ia[0]."_warning_2' style='".$time_limit_warning_2_style."'>".$time_limit_warning_2_message."</div>\n\n";
    $output .= "<div id='LS_question".$ia[0]."_Timer' style='".$time_limit_timer_style."'></div>\n\n";
    //Call the countdown script
    $output .= "<script type='text/javascript'>
	$(document).ready(function() {
		countdown(".$ia[0].", ".$time_limit.", ".$time_limit_action.", ".$time_limit_warning.", ".$time_limit_warning_2.", ".$time_limit_warning_display_time.", ".$time_limit_warning_2_display_time.", '".$disable."');
	});
</script>\n\n";
    return $output;
}

function return_array_filter_selected($ia, $qidattributes, $thissurvey, $ansrow, $rowname, $trbc='', $valuename, $method="tbody", $class=null)
// function which returns TRUE if the given $ansrow contains a row which is selected, ie, not filtered out in previous answer
{
	$filter_select = TRUE;
	if
    (
    (trim($qidattributes['array_filter'])!='' && 		// The array filter attribute is set
    $thissurvey['format'] == 'S'						// and the survey is being presented in question-by-question mode
    ) || 												// OR
    (trim($qidattributes['array_filter'])!='' && 		// The array filter attribute is set
    $thissurvey['format'] == 'G' && 					// and the survey is being presented in group-by-group mode
    getArrayFiltersOutGroup($ia[0]) == true			// and the source question for the array filter is in a different group than this question
    )
    )
    {
        $selected = getArrayFiltersForQuestion($ia[0]);
        if (isset($ansrow['code'])) $ansrow['title'] = $ansrow['code'];
        if (!empty($selected) && !in_array($ansrow['title'],$selected))
        {
			$filter_select = FALSE;
		}
		else
		{
			$filter_select = TRUE;
		}
	}

	if
    (isset($qidattributes['array_filter_exclude']) &&
	(
    (trim($qidattributes['array_filter_exclude'])!='' &&
    $thissurvey['format'] == 'S'
    ) ||
    (trim($qidattributes['array_filter_exclude'])!='' &&
    $thissurvey['format'] == 'G' &&
    getArrayFiltersExcludesOutGroup($ia[0]) == true
    )
	)
    )
    {
        $selected = getArrayFilterExcludesForQuestion($ia[0]);
        if (isset($ansrow['code'])) $ansrow['title'] = $ansrow['code'];
        if (!empty($selected) && !in_array($ansrow['title'],$selected))
        {
			$filter_select = TRUE;
		}
		else
		{
			$filter_select = FALSE;
		}
	}
	return $filter_select;
}

function return_array_filter_strings($ia, $qidattributes, $thissurvey, $ansrow, $rowname, $trbc='', $valuename, $method="tbody", $class=null) {
    /* We're just going to work out whether to do the include or exclude version of the function at this point */
    if(isset($qidattributes['array_filter_exclude']) && trim($qidattributes['array_filter_exclude']) != '') {
        list($html2body, $hiddenfield) = return_array_filter_exclude_strings($ia, $qidattributes, $thissurvey, $ansrow, $rowname, $trbc, $valuename, $method, $class);
    } else {
        list($html2body, $hiddenfield) = return_array_filter_include_strings($ia, $qidattributes, $thissurvey, $ansrow, $rowname, $trbc, $valuename, $method, $class);
    }
    return array($html2body, $hiddenfield);
}

function return_array_filter_include_strings($ia, $qidattributes, $thissurvey, $ansrow, $rowname, $trbc='', $valuename, $method="tbody", $class=null) {
    /* DO ARRAY_FILTER ATTRIBUTE
     We set the $hiddenfield for each answer, and the value of this is available to java to let javascripts
     know whether each answer is currently being displayed. $htmltbody2 determines whether the answer row
     should be displayed initially. If no answers for the source question have been selected then the whole
     answer row should start in the display-off position for array_filter or the display-on position
     for array_filter_exclude. */

    //Are we doing array_filter, or array_filter_exclude. Where a conflict occurs, do array_filter

    $htmltbody2 = '';
    $hiddenfield= '';
    if  (
    (trim($qidattributes['array_filter'])!='' && 	// the array_filter attribute is set
    $thissurvey['format'] == 'G' && 				// and the survey is being presented group by group
    getArrayFiltersOutGroup($ia[0]) == false		// and the source question is in the same group (ie displayed on same page)
    ) ||											// OR
    (trim($qidattributes['array_filter'])!='' &&	// the array_filter attribute is set
    $thissurvey['format'] == 'A'					// and the survey is being presented all on one page
    )
    )
    {
        $htmltbody2 = "\n\n\t<$method id='javatbd$rowname' style='display: none'";
        $htmltbody2 .= ($class !== null) ? " class='$class'": "";
        $htmltbody2 .= ">\n";
        if($ia[4]=="1") {
        //This is an array dual scale question and we have to massage the tbidpslay rowname
            $hiddenfield = "<input type='hidden' name='tbdisp$rowname#0' id='tbdisp$rowname#0' value='off' />\n";
            $hiddenfield .= "<input type='hidden' name='tbdisp$rowname#1' id='tbdisp$rowname#1' value='off' />\n";
        } else {
            $hiddenfield = "<input type='hidden' name='tbdisp$rowname' id='tbdisp$rowname' value='off' />\n";
        }
    } else if
    (
    (trim($qidattributes['array_filter'])!='' && 		// The array filter attribute is set
    $thissurvey['format'] == 'S'						// and the survey is being presented in question-by-question mode
    ) || 												// OR
    (trim($qidattributes['array_filter'])!='' && 		// The array filter attribute is set
    $thissurvey['format'] == 'G' && 					// and the survey is being presented in group-by-group mode
    getArrayFiltersOutGroup($ia[0]) == true			// and the source question for the array filter is in a different group than this question
    )
    )
    {
        $selected = getArrayFiltersForQuestion($ia[0]);
        if (isset($ansrow['code'])) $ansrow['title'] = $ansrow['code'];
        if (!empty($selected) && !in_array($ansrow['title'],$selected))
        {
            $htmltbody2 = "\n\n\t<$method id='javatbd$rowname' style='display: none'";
            $htmltbody2 .= ($class !== null) ? " class='$class'": "";
            $htmltbody2 .= ">\n";
            if($ia[4]=="1") {
            //This is an array dual scale question and we have to massage the tbidpslay rowname
                $hiddenfield = "<input type='hidden' name='tbdisp$rowname#0' id='tbdisp$rowname#0' value='off' />\n";
                $hiddenfield .= "<input type='hidden' name='tbdisp$rowname#1' id='tbdisp$rowname#1' value='off' />\n";
            } else {
                $hiddenfield = "<input type='hidden' name='tbdisp$rowname' id='tbdisp$rowname' value='off' />\n";
            }
            $_SESSION[$valuename] = ''; //Remove any saved results for this since it is no longer being displayed
        }
        else
        {
            $htmltbody2 = "\n\n\t<$method id='javatbd$rowname'";
            $htmltbody2 .= ($class !== null) ? " class='$class'": "";
            $htmltbody2 .= ">";
            if($ia[4]=="1") {
            //This is an array dual scale question and we have to massage the tbidpslay rowname
                $hiddenfield = "<input type='hidden' name='tbdisp$rowname#0' id='tbdisp$rowname#0' value='on' />\n";
                $hiddenfield .= "<input type='hidden' name='tbdisp$rowname#1' id='tbdisp$rowname#1' value='on' />\n";
            } else {
                $hiddenfield = "<input type='hidden' name='tbdisp$rowname' id='tbdisp$rowname' value='on' />\n";
        }
    }
    }
    else
    {
        $htmltbody2 = "\n\n\t<$method id='javatbd$rowname'";
        $htmltbody2 .= ($class !== null) ? " class='$class'": "";
        $htmltbody2 .= ">\n";
        if($ia[4]=="1") {
        //This is an array dual scale question and we have to massage the tbidpslay rowname
            $hiddenfield = "<input type='hidden' name='tbdisp$rowname#0' id='tbdisp$rowname#0' value='on' />\n";
            $hiddenfield .= "<input type='hidden' name='tbdisp$rowname#1' id='tbdisp$rowname#1' value='on' />\n";
        } else {
            $hiddenfield = "<input type='hidden' name='tbdisp$rowname' id='tbdisp$rowname' value='on' />\n";
        }
    }

    //End of array_filter attribute

    return array($htmltbody2, $hiddenfield);
}

function return_array_filter_exclude_strings($ia, $qidattributes, $thissurvey, $ansrow, $rowname, $trbc='', $valuename, $method="tbody", $class=null) {
    /* DO ARRAY_FILTER_EXCLUDE ATTRIBUTE
     We set the $hiddenfield for each answer, and the value of this is available to java to let javascripts
     know whether each answer is currently being displayed. $htmltbody2 determines whether the answer row
     should be displayed initially. If no answers for the source question have been selected then the whole
     answer row should start in the display-on position. */

    $htmltbody2 = '';
    $hiddenfield= '';
    if  (
    (trim($qidattributes['array_filter_exclude'])!='' && 	// the array_filter attribute is set
    $thissurvey['format'] == 'G' && 						// and the survey is being presented group by group
    getArrayFiltersExcludesOutGroup($ia[0]) == false		// and this question _is_ in the current group for the array filter (ie it's on the same page)
    ) ||													// OR
    (trim($qidattributes['array_filter_exclude'])!='' &&	// the array_filter attribute is set
    $thissurvey['format'] == 'A'							// and the survey is being presented all on one page
    )
    )
    {
        $htmltbody2 = "\n\n\t<$method id='javatbd$rowname'>\n";
        if($ia[4]=="1") {
            //This is an array dual scale question and we have to massage the tbidpslay rowname
            $hiddenfield = "<input type='hidden' name='tbdisp$rowname#0' id='tbdisp$rowname#0' value='on' />\n";
            $hiddenfield .= "<input type='hidden' name='tbdisp$rowname#1' id='tbdisp$rowname#1' value='on' />\n";
        } else {
        $hiddenfield = "<input type='hidden' name='tbdisp$rowname' id='tbdisp$rowname' value='on' />\n";
        }
    } else if
    (
    (trim($qidattributes['array_filter_exclude'])!='' &&
    $thissurvey['format'] == 'S'
    ) ||
    (trim($qidattributes['array_filter_exclude'])!='' &&
    $thissurvey['format'] == 'G' &&
    getArrayFiltersExcludesOutGroup($ia[0]) == true
    )
    )
    {
        $selected = getArrayFilterExcludesForQuestion($ia[0]);
        if (isset($ansrow['code'])) $ansrow['title'] = $ansrow['code'];
        if (!empty($selected) && !in_array($ansrow['title'],$selected))
        {
            $htmltbody2 = "\n\n\t<$method id='javatbd$rowname'>\n";
            if($ia[4]=="1") {
                //This is an array dual scale question and we have to massage the tbidpslay rowname
                $hiddenfield = "<input type='hidden' name='tbdisp$rowname#0' id='tbdisp$rowname#0' value='on' />\n";
                $hiddenfield .= "<input type='hidden' name='tbdisp$rowname#1' id='tbdisp$rowname#1' value='on' />\n";
            } else {
            $hiddenfield="<input type='hidden' name='tbdisp$rowname' id='tbdisp$rowname' value='on' />";
        }
        }
        else
        {
            $htmltbody2 = "\n\n\t<$method id='javatbd$rowname' style='display: none'>";
            if($ia[4]=="1") {
                //This is an array dual scale question and we have to massage the tbidpslay rowname
                $hiddenfield = "<input type='hidden' name='tbdisp$rowname#0' id='tbdisp$rowname#0' value='off' />\n";
                $hiddenfield .= "<input type='hidden' name='tbdisp$rowname#1' id='tbdisp$rowname#1' value='off' />\n";
            } else {
            $hiddenfield="\n<input type='hidden' name='tbdisp$rowname' id='tbdisp$rowname' value='off' />";
            }
            $_SESSION[$valuename]=''; //Remove any saved results for this since it is no longer being displayed
        }
    }
    else
    {
        $htmltbody2 = "\n\n\t<$method id='javatbd$rowname' style='display: none'>\n";
        if($ia[4]=="1") {
            //This is an array dual scale question and we have to massage the tbidpslay rowname
            $hiddenfield = "<input type='hidden' name='tbdisp$rowname#0' id='tbdisp$rowname#0' value='off' />\n";
            $hiddenfield .= "<input type='hidden' name='tbdisp$rowname#1' id='tbdisp$rowname#1' value='off' />\n";
        } else {
        $hiddenfield = "<input type='hidden' name='tbdisp$rowname' id='tbdisp$rowname' value='off' />";
    }
    }
    //End of array_filter attribute

    return array($htmltbody2, $hiddenfield);
}

// ==================================================================
// setting constants for 'checked' and 'selected' inputs
define('CHECKED' , ' checked="checked"' , true);
define('SELECTED' , ' selected="selected"' , true);

// ==================================================================
// QUESTION METHODS =================================================

function do_boilerplate($ia)
{
    global $js_header_includes;
    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    $answer='';

    if (trim($qidattributes['time_limit'])!='')
    {
        $js_header_includes[] = '/scripts/coookies.js';
        $answer .= return_timer_script($qidattributes, $ia);
    }

    $answer .= '<input type="hidden" name="$ia[1]" id="answer'.$ia[1].'" value="" />';
    $inputnames[]=$ia[1];

    return array($answer, $inputnames);
}


// ---------------------------------------------------------------
function do_5pointchoice($ia)
{
    global $clang, $imageurl;
	global $js_header_includes, $css_header_includes;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }
    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    //print_r($qidattributes);
	$id = 'slider'.time().rand(0,100);
    $answer = "\n<ul id=\"{$id}\">\n";
    for ($fp=1; $fp<=5; $fp++)
    {
        $answer .= "\t<li>\n<input class=\"radio\" type=\"radio\" name=\"$ia[1]\" id=\"answer$ia[1]$fp\" value=\"$fp\"";
        if ($_SESSION[$ia[1]] == $fp)
        {
            $answer .= CHECKED;
        }
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer$ia[1]$fp\" class=\"answertext\">$fp</label>\n\t</li>\n";
    }

    if ($ia[6] != "Y"  && SHOW_NO_ANSWER == 1) // Add "No Answer" option if question is not mandatory
    {
        $answer .= "\t<li>\n<input class=\"radio\" type=\"radio\" name=\"$ia[1]\" id=\"NoAnswer\" value=\"\"";
        if (!$_SESSION[$ia[1]])
        {
            $answer .= CHECKED;
        }
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"NoAnswer\" class=\"answertext\">".$clang->gT('No answer')."</label>\n\t</li>\n";

    }
    $answer .= "</ul>\n<input type=\"hidden\" name=\"java$ia[1]\" id=\"java$ia[1]\" value=\"{$_SESSION[$ia[1]]}\" />\n";
    $inputnames[]=$ia[1];
    if($qidattributes['slider_rating']==1){
    	$css_header_includes[]= '/admin/scripts/rating/jquery.rating.css';
    	$js_header_includes[]='/admin/scripts/rating/jquery.rating.js';
    	$answer.='<br/><center><div id="'.$id.'div"><input type="radio" id="stars1" name="stars" class="'.$id.'st" value="1"/><input type="radio" id="stars2" name="stars" class="'.$id.'st" value="2"/><input type="radio" name="stars" id="stars3" class="'.$id.'st" value="3"/><input type="radio" id="stars4" name="stars" class="'.$id.'st" value="4"/><input type="radio" name="stars" id="stars5" class="'.$id.'st" value="5"/></div></center><br/>';
	    $answer.="
			<script type=\"text/javascript\">
				$('#$id').hide();
				var checked = $('#$id input:checked').attr('value');
				if(checked!=''){
					$('#stars'+checked).attr('checked','checked');
    			}
				$('.{$id}st').rating({
    				callback: function(value,link){
    					if(value==undefined || value==''){
    						$('#$id input').each(function(){ $(this).removeAttr('checked');});
    						$('#{$id} #NoAnswer').attr('checked','checked');
    					}
    					else{
    						$('#$id input').each(function(){ $(this).removeAttr('checked');});
    						$('#answer$ia[1]'+value).attr('checked','checked');
    					}
    				}

    			});
			</script>
			";
    }

    if($qidattributes['slider_rating']==2){
	    if(!IsSet($_SESSION[$ia[1]]) OR $_SESSION[$ia[1]]==''){
	    	$value=1;
	    }else{
	    	$value=$_SESSION[$ia[1]];
	    }
    	$answer.="
    		<div style=\"float:left;\">
    		<div style=\"text-align:center; margin-bottom:6px; width:370px;\"><div style=\"width:2%; float:left;\">1</div><div style=\"width:46%;float:left;\">2</div><div style=\"width:4%;float:left;\">3</div><div style=\"width:46%;float:left;\">4</div><div style=\"width:2%;float:left;\">5</div></div><br/>
    	 	<div id=\"{$id}sliderBg\" style=\"background-image:url('{$imageurl}/sliderBg.png'); text-align:center; background-repeat:no-repeat; height:22px; width:396px;\">
    	 	<center>
    		<div id=\"{$id}slider\" style=\"width:365px;\"></div>
    		</center>
    		</div></div>
    	 	<div id=\"{$id}emoticon\" style=\"text-align:left; margin:10px; padding-left:10px;\"><img id=\"{$id}img1\" style=\"margin-left:10px;\" src=\".{$imageurl}/emoticons/{$value}.png\"/><img id=\"{$id}img2\" style=\"margin-left:-31px;margin-top:-31px;\" src=\"{$imageurl}/emoticons/{$value}.png\" /></div>
			<script type=\"text/javascript\">
				$('#$id').hide();
				var value=$value;
				var checked = $('#$id input:checked').attr('value');
				if(checked!=''){
					value=checked;
    			}
    			var time=200;
    			var old=value;
				$('#{$id}slider').slider({
				value: value,
				min: 1,
				max: 5,
				step: 1,
				slide: function(event,ui){
						$('#{$id}img2').attr('src','{$imageurl}/emoticons/'+ui.value+'.png');
						$('#{$id}img2').fadeIn(time);
						$('#$id input').each(function(){ $(this).removeAttr('checked');});
    					$('#answer$ia[1]'+ui.value).attr('checked','checked');
						$('#{$id}img1').fadeOut(time,function(){
    						$('#{$id}img1').attr('src',$('#{$id}img2').attr('src'));
    						$('#{$id}img1').show();
    						$('#{$id}img2').hide();
    					});
    				}
				});
				$('#{$id}slider a').css('background-image', 'url(\'{$imageurl}/slider.png\')');
				$('#{$id}slider a').css('width', '11px');
				$('#{$id}slider a').css('height', '28px');
				$('#{$id}slider a').css('border', 'none');
				//$('#{$id}slider').css('background-image', 'url(\'{$imageurl}/sliderBg.png\')');
				$('#{$id}slider').css('visibility','hidden');
				$('#{$id}slider a').css('visibility', 'visible');
			</script>
			";

    }
    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_date($ia)
{
    global $clang, $js_header_includes, $css_header_includes, $thissurvey;
    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    $js_header_includes[] = '/scripts/jquery/jquery-ui.js';
    $js_header_includes[] = '/scripts/jquery/lime-calendar.js';


    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $dateformatdetails=getDateFormatData($thissurvey['surveyls_dateformat']);
    $numberformatdatat = getRadixPointData($thissurvey['surveyls_numberformat']);

    if (trim($qidattributes['dropdown_dates'])!=0) {
        if (!empty($_SESSION[$ia[1]]))
        {
            list($currentyear, $currentmonth, $currentdate) = explode('-', $_SESSION[$ia[1]]);
        } else {
            $currentdate='';
            $currentmonth='';
            $currentyear='';
        }

        $dateorder = preg_split('/[-\.\/ ]/', $dateformatdetails['phpdate']);
        $answer='<p class="question">';
        foreach($dateorder as $datepart)
        {
            switch($datepart)
            {
                // Show day select box
                case 'j':
                case 'd':   $answer .= ' <select id="day'.$ia[1].'" class="day">
                                                <option value="">'.$clang->gT('Day')."</option>\n";
                for ($i=1; $i<=31; $i++) {
                    if ($i == $currentdate)
                    {
                        $i_date_selected = SELECTED;
                    }
                    else
                    {
                        $i_date_selected = '';
                    }
                    $answer .= '    <option value="'.sprintf('%02d', $i).'"'.$i_date_selected.'>'.sprintf('%02d', $i)."</option>\n";
                }
                $answer .='</select>';
                break;
                // Show month select box
                case 'n':
                case 'm':   $answer .= ' <select id="month'.$ia[1].'" class="month">
                                            <option value="">'.$clang->gT('Month')."</option>\n";
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

                    $answer .= '    <option value="'.sprintf('%02d', $i).'"'.$i_date_selected.'>'.$montharray[$i-1].'</option>';
                }
                $answer .= '    </select>';
                break;
                // Show year select box
                case 'Y':   $answer .= ' <select id="year'.$ia[1].'" class="year">
                                            <option value="">'.$clang->gT('Year').'</option>';

                /*
                 *  New question attributes used only if question attribute
                 * "dropdown_dates" is used (see IF(...) above).
                 *
                 * yearmin = Minimum year value for dropdown list, if not set default is 1900
                 * yearmax = Maximum year value for dropdown list, if not set default is 2020
                 */
                if (trim($qidattributes['dropdown_dates_year_min'])!='')
                {
                    $yearmin = $qidattributes['dropdown_dates_year_min'];
                }
                else
                {
                    $yearmin = 1900;
                }

                if (trim($qidattributes['dropdown_dates_year_max'])!='')
                {
                    $yearmax = $qidattributes['dropdown_dates_year_max'];
                }
                else
                {
                    $yearmax = 2020;
                }

                if ($yearmin > $yearmax)
                {
                    $yearmin = 1900;
                    $yearmax = 2020;
                }

                if ($qidattributes['reverse']==1)
                {
                    $tmp = $yearmin;
                    $yearmin = $yearmax;
                    $yearmax = $tmp;
                    $step = 1;
                    $reverse = true;
                }
                else
                {
                    $step = -1;
                    $reverse = false;
                }

                for ($i=$yearmax; ($reverse? $i<=$yearmin: $i>=$yearmin); $i+=$step) {
                    if ($i == $currentyear)
                    {
                        $i_date_selected = SELECTED;
                    }
                    else
                    {
                        $i_date_selected = '';
                    }
                    $answer .= '  <option value="'.$i.'"'.$i_date_selected.'>'.$i.'</option>';
                }
                $answer .= '</select>';

                break;
            }
        }

        $answer .= '<input class="text" type="text" size="10" name="'.$ia[1].'" style="display: none" id="answer'.$ia[1].'" value="'.$_SESSION[$ia[1]].'" maxlength="10" alt="'.$clang->gT('Answer').'" onchange="'.$checkconditionFunction.'(this.value, this.name, this.type)" />
			</p>';
        $answer .= '<input type="hidden" name="qattribute_answer[]" value="'.$ia[1].'" />
			        <input type="hidden" id="qattribute_answer'.$ia[1].'" name="qattribute_answer'.$ia[1].'" />
                    <input type="hidden" id="dateformat'.$ia[1].'" value="'.$dateformatdetails['jsdate'].'"/>';


    }
    else
    {
        if ($clang->langcode !== 'en')
        {
        $js_header_includes[] = '/scripts/jquery/locale/jquery.ui.datepicker-'.$clang->langcode.'.js';
        }
        $css_header_includes[]= '/scripts/jquery/css/start/jquery-ui.css';

        // Format the date  for output
        if (trim($_SESSION[$ia[1]])!='')
        {
            $datetimeobj = new Date_Time_Converter($_SESSION[$ia[1]] , "Y-m-d");
            $dateoutput=$datetimeobj->convert($dateformatdetails['phpdate']);
        }
        else
        {
            $dateoutput='';
        }

        if (trim($qidattributes['dropdown_dates_year_min'])!='') {
            $minyear=$qidattributes['dropdown_dates_year_min'];
        }
        else
        {
            $minyear='1980';
        }

        if (trim($qidattributes['dropdown_dates_year_max'])!='') {
            $maxyear=$qidattributes['dropdown_dates_year_max'];
        }
        else
        {
            $maxyear='2020';
        }

        $goodchars = str_replace( array("m","d","y"), "", $dateformatdetails['jsdate']);
        $goodchars = "0123456789".$goodchars[0];

        $answer ="<p class=\"question\">
                        <input class='popupdate' type=\"text\" alt=\"".$clang->gT('Date picker')."\" size=\"10\" name=\"{$ia[1]}\" id=\"answer{$ia[1]}\" value=\"$dateoutput\" maxlength=\"10\" onkeypress=\"return goodchars(event,'".$goodchars."')\" onchange=\"$checkconditionFunction(this.value, this.name, this.type)\" />
                        <input  type='hidden' name='dateformat{$ia[1]}' id='dateformat{$ia[1]}' value='{$dateformatdetails['jsdate']}'  />
                        <input  type='hidden' name='datelanguage{$ia[1]}' id='datelanguage{$ia[1]}' value='{$clang->langcode}'  />
                        <input  type='hidden' name='dateyearrange{$ia[1]}' id='dateyearrange{$ia[1]}' value='{$minyear}:{$maxyear}'  />

			         </p>
			         <p class=\"tip\">
				         ".sprintf($clang->gT('Format: %s'),$dateformatdetails['dateformat'])."
			         </p>";
    }
    $inputnames[]=$ia[1];

    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_language($ia)
{
    global $dbprefix, $surveyid, $clang;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $answerlangs = GetAdditionalLanguagesFromSurveyID($surveyid);
    $answerlangs [] = GetBaseLanguageFromSurveyID($surveyid);
    $answer = "\n\t<p class=\"question\">\n<select name=\"$ia[1]\" id=\"answer$ia[1]\" onchange=\"document.getElementById('lang').value=this.value; $checkconditionFunction(this.value, this.name, this.type);\">\n";
    if (!$_SESSION[$ia[1]]) {$answer .= "\t<option value=\"\" selected=\"selected\">".$clang->gT('Please choose...')."</option>\n";}
    foreach ($answerlangs as $ansrow)
    {
        $answer .= "\t<option value=\"{$ansrow}\"";
        if ($_SESSION[$ia[1]] == $ansrow)
        {
            $answer .= SELECTED;
        }
        $answer .= '>'.getLanguageNameFromCode($ansrow, true)."</option>\n";
    }
    $answer .= "</select>\n";
    $answer .= "<input type=\"hidden\" name=\"java$ia[1]\" id=\"java$ia[1]\" value=\"{$_SESSION[$ia[1]]}\" />\n";

    $inputnames[]=$ia[1];
    $answer .= "\n<input type=\"hidden\" name=\"lang\" id=\"lang\" value=\"\" />\n\t</p>\n";

    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_list_dropdown($ia)
{
    global $dbprefix,  $dropdownthreshold, $lwcdropdowns, $connect;
    global $clang;
    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }
    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);

    if (trim($qidattributes['other_replace_text'])!='')
    {
        $othertext=$clang->gT($qidattributes['other_replace_text']);
    }
    else
    {
        $othertext=$clang->gT('Other:');
    }

    if (trim($qidattributes['category_separator'])!='')
    {
        $optCategorySeparator = $qidattributes['category_separator'];
    }



    $answer='';


    $query = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."' ";
    $result = db_execute_assoc($query);      //Checked
    while($row = $result->FetchRow()) {$other = $row['other'];}

    //question attribute random order set?
    if ($qidattributes['random_order']==1)
    {
        $ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY ".db_random();
    }
    //question attribute alphasort set?
    elseif ($qidattributes['alphasort']==1)
    {
        $ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY answer";
    }
    //no question attributes -> order by sortorder
    else
    {
        $ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
    }

    $ansresult = db_execute_assoc($ansquery) or safe_die('Couldn\'t get answers<br />'.$ansquery.'<br />'.$connect->ErrorMsg());    //Checked

    if (!isset($optCategorySeparator))
    {
        while ($ansrow = $ansresult->FetchRow())
        {
            $opt_select = '';
            if ($_SESSION[$ia[1]] == $ansrow['code'])
            {
                $opt_select = SELECTED;
            }
            $answer .= "<option value='{$ansrow['code']}' {$opt_select}>{$ansrow['answer']}</option>\n";
        }
    }
    else
    {
        $defaultopts = Array();
        $optgroups = Array();
        while ($ansrow = $ansresult->FetchRow())
        {
            // Let's sort answers in an array indexed by subcategories
            @list ($categorytext, $answertext) = explode($optCategorySeparator,$ansrow['answer']);
            // The blank category is left at the end outside optgroups
            if ($categorytext == '')
            {
                $defaultopts[] = array ( 'code' => $ansrow['code'], 'answer' => $answertext);
            }
            else
            {
                $optgroups[$categorytext][] = array ( 'code' => $ansrow['code'], 'answer' => $answertext);
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
                else
                {
                    $opt_select = '';
                }

                $answer .= '     					<option value="'.$optionarray['code'].'"'.$opt_select.'>'.$optionarray['answer'].'</option>
					';
            }

            $answer .= '                                   </optgroup>';
        }
        $opt_select='';
        foreach ($defaultopts as $optionarray)
        {
            if ($_SESSION[$ia[1]] == $optionarray['code'])
            {
                $opt_select = SELECTED;
            }
            else
            {
                $opt_select = '';
            }

            $answer .= '     					<option value="'.$optionarray['code'].'"'.$opt_select.'>'.$optionarray['answer'].'</option>
				';
        }
    }

    if (!$_SESSION[$ia[1]])
    {
        $answer = '					<option value=""'.SELECTED.'>'.$clang->gT('Please choose...').'</option>'."\n".$answer;
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

    if ((isset($_SESSION[$ia[1]]) || $_SESSION[$ia[1]] != '') && $ia[6] != 'Y' && $ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
    {
        $answer .= '<option value="">'.$clang->gT('No answer')."</option>\n";
    }
    $answer .= '				</select>
				<input type="hidden" name="java'.$ia[1].'" id="java'.$ia[1].'" value="'.$_SESSION[$ia[1]].'" />';

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
				<select name="'.$ia[1].'" id="answer'.$ia[1].'" onchange="'.$checkconditionFunction.'(this.value, this.name, this.type);'.$sselect_show_hide.'">
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
        ."{\n"
        ."document.getElementById(hiddenothername).style.display='';\n"
        ."document.getElementById(hiddenothername).focus();\n"
        ."}\n"
        ."\telse\n"
        ."{\n"
        ."document.getElementById(hiddenothername).style.display='none';\n"
        ."document.getElementById(hiddenothername).value='';\n" // reset othercomment field
        ."}\n"
        ."\t}\n"
        ."//--></script>\n".$answer;
        $answer .= '				<input type="text" id="othertext'.$ia[1].'" name="'.$ia[1].'other" style="display:';

        $inputnames[]=$ia[1].'other';

        if ($_SESSION[$ia[1]] != '-oth-')
        {
            $answer .= 'none';
        }

        //		// --> START BUG FIX - text field for other was not repopulating when returning to page via << PREV
        $answer .= '"';
        //		$thisfieldname=$ia[1].'other';
        //		if (isset($_SESSION[$thisfieldname])) { $answer .= ' value="'.htmlspecialchars($_SESSION[$thisfieldname],ENT_QUOTES).'" ';}
        //		// --> END BUG FIX

        // --> START NEW FEATURE - SAVE
        $answer .= "  alt='".$clang->gT('Other answer')."' onchange='$checkconditionFunction(this.value, this.name, this.type);'";
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

    $checkotherscript = "";
    if (isset($other) && $other == 'Y' && $qidattributes['other_comment_mandatory']==1)
    {
        $checkotherscript = "\n<script type='text/javascript'>\n"
        . "\t<!--\n"
        . "oldonsubmitOther_{$ia[0]} = document.limesurvey.onsubmit;\n"
        . "function ensureOther_{$ia[0]}()\n"
        . "{\n"
        . "\tothercommentval=document.getElementById('othertext{$ia[1]}').value;\n"
        . "\totherval=document.getElementById('answer{$ia[1]}').value;\n"
        . "\tif (otherval == '-oth-' && othercommentval == '') {\n"
        . "alert('".sprintf($clang->gT("You've selected the \"%s\" answer for question \"%s\". Please also fill in the accompanying \"other comment\" field.","js"),trim(javascript_escape($othertext,true,true)),trim(javascript_escape($ia[3],true,true)))."');\n"
        . "return false;\n"
        . "\t}\n"
        . "\telse {\n"
        . "if(typeof oldonsubmitOther_{$ia[0]} == 'function') {\n"
        . "\treturn oldonsubmitOther_{$ia[0]}();\n"
        . "}\n"
        . "\t}\n"
        . "}\n"
        . "document.limesurvey.onsubmit = ensureOther_{$ia[0]};\n"
        . "\t-->\n"
        . "</script>\n";
    }
    $answer = $checkotherscript . $answer;

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);
}








// ---------------------------------------------------------------
function do_list_radio($ia)
{
    global $dbprefix, $dropdownthreshold, $lwcdropdowns, $connect, $clang;
    global $thissurvey;


    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);

    $query = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."' ";
    $result = db_execute_assoc($query);  //Checked
    while($row = $result->FetchRow())
    {
        $other = $row['other'];
    }

    //question attribute random order set?
    if ($qidattributes['random_order']==1) {
        $ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY ".db_random();
    }

    //question attribute alphasort set?
    elseif ($qidattributes['alphasort']==1)
    {
        $ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY answer";
    }

    //no question attributes -> order by sortorder
    else
    {
        $ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
    }

    $ansresult = db_execute_assoc($ansquery) or safe_die('Couldn\'t get answers<br />$ansquery<br />'.$connect->ErrorMsg());  //Checked
    $anscount = $ansresult->RecordCount();


    if (trim($qidattributes['display_columns'])!='') {
        $dcols = $qidattributes['display_columns'];
    }
    else
    {
        $dcols= 1;
    }

    if (trim($qidattributes['other_replace_text'])!='')
    {
        $othertext=$clang->gT($qidattributes['other_replace_text']);
    }
    else
    {
        $othertext=$clang->gT('Other:');
    }

    if (isset($other) && $other=='Y') {$anscount++;} //Count up for the Other answer
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {$anscount++;} //Count up if "No answer" is showing

    $wrapper = setup_columns($dcols , $anscount);
    $answer = $wrapper['whole-start'];

    // Get array_filter stuff

    $rowcounter = 0;
    $colcounter = 1;
    $trbc='';

    while ($ansrow = $ansresult->FetchRow())
    {
        $myfname = $ia[1].$ansrow['code'];
        $check_ans = '';
        if ($_SESSION[$ia[1]] == $ansrow['code'])
        {
            $check_ans = CHECKED;
        }

        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname, "li");

        if($wrapper['item-start'] == "\t<li>\n")
        {
            $startitem = "\t$htmltbody2\n";
        } else {
            $startitem = $wrapper['item-start'];
        }

        $answer .= $startitem;
        $answer .= "\t$hiddenfield\n";
        $answer .='		<input class="radio" type="radio" value="'.$ansrow['code'].'" name="'.$ia[1].'" id="answer'.$ia[1].$ansrow['code'].'"'.$check_ans.' onclick="if (document.getElementById(\'answer'.$ia[1].'othertext\') != null) document.getElementById(\'answer'.$ia[1].'othertext\').value=\'\';'.$checkconditionFunction.'(this.value, this.name, this.type)" />
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

        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeperator = $sSeperator['seperator'];

        if ($qidattributes['other_numbers_only']==1)
        {
            $numbersonly = 'onkeypress="return goodchars(event,\'-0123456789'.$sSeperator.'\')"';
        }
        else
        {
            $numbersonly = '';
        }


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

        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, array("code"=>"other"), $thisfieldname, $trbc, $myfname, "li", "other");

        if($wrapper['item-start-other'] == "\t<li class=\"other\">\n")
        {
            $startitem = "\t$htmltbody2\n";
        } else {
            $startitem = $wrapper['item-start-other'];
        }
        $answer .= $startitem;
        $answer .= "\t$hiddenfield\n";
        $answer .= '		<input class="radio" type="radio" value="-oth-" name="'.$ia[1].'" id="SOTH'.$ia[1].'"'.$check_ans.' onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)" />
		<label for="SOTH'.$ia[1].'" class="answertext">'.$othertext.'</label>
		<label for="answer'.$ia[1].'othertext">
			<input type="text" class="text" id="answer'.$ia[1].'othertext" name="'.$ia[1].'other" title="'.$clang->gT('Other').'"'.$answer_other.' '.$numbersonly.' onkeyup="javascript:document.getElementById(\'SOTH'.$ia[1].'\').checked=true; '.$checkconditionFunction.'(document.getElementById(\'SOTH'.$ia[1].'\').value, document.getElementById(\'SOTH'.$ia[1].'\').name, document.getElementById(\'SOTH'.$ia[1].'\').type);" />
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

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
    {
        if ((!isset($_SESSION[$ia[1]]) || $_SESSION[$ia[1]] == '') || ($_SESSION[$ia[1]] == ' ' ))
        {
            $check_ans = CHECKED; //Check the "no answer" radio button if there is no answer in session.
        }
        else
        {
            $check_ans = '';
        }

        $answer .= $wrapper['item-start'].'		<input class="radio" type="radio" name="'.$ia[1].'" id="answer'.$ia[1].'NANS" value=""'.$check_ans.' onclick="if (document.getElementById(\'answer'.$ia[1].'othertext\') != null) document.getElementById(\'answer'.$ia[1].'othertext\').value=\'\';'.$checkconditionFunction.'(this.value, this.name, this.type)" />
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
    //END OF ITEMS
    $answer .= $wrapper['whole-end'].'
<input type="hidden" name="java'.$ia[1].'" id="java'.$ia[1]."\" value=\"{$_SESSION[$ia[1]]}\" />\n";

    $checkotherscript = "";

    if (isset($other) && $other == 'Y' && $qidattributes['other_comment_mandatory']==1)
    {
        $checkotherscript = "<script type='text/javascript'>\n"
        . "\t<!--\n"
        . "oldonsubmitOther_{$ia[0]} = document.limesurvey.onsubmit;\n"
        . "function ensureOther_{$ia[0]}()\n"
        . "{\n"
        . "\tothercommentval=document.getElementById('answer{$ia[1]}othertext').value;\n"
        . "\totherval=document.getElementById('SOTH{$ia[1]}').checked;\n"
        . "\tif (otherval == true && othercommentval == '') {\n"
        . "alert('".sprintf($clang->gT("You've selected the \"%s\" answer for question \"%s\". Please also fill in the accompanying \"other comment\" field.","js"),trim(javascript_escape($othertext,true,true)),trim(javascript_escape($ia[3],true,true)))."');\n"
        . "return false;\n"
        . "\t}\n"
        . "\telse {\n"
        . "if(typeof oldonsubmitOther_{$ia[0]} == 'function') {\n"
        . "\treturn oldonsubmitOther_{$ia[0]}();\n"
        . "}\n"
        . "\t}\n"
        . "}\n"
        . "document.limesurvey.onsubmit = ensureOther_{$ia[0]};\n"
        . "\t-->\n"
        . "</script>\n";
    }

    $answer = $checkotherscript . $answer;

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_listwithcomment($ia)
{
    global $maxoptionsize, $dbprefix, $dropdownthreshold, $lwcdropdowns;
    global $clang;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $answer = '';

    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    if (!isset($maxoptionsize)) {$maxoptionsize=35;}

    //question attribute random order set?
    if ($qidattributes['random_order']==1) {
        $ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY ".db_random();
    }
    //question attribute alphasort set?
    elseif ($qidattributes['alphasort']==1)
    {
        $ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY answer";
    }
    //no question attributes -> order by sortorder
    else
    {
        $ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
    }

    $ansresult = db_execute_assoc($ansquery);      //Checked
    $anscount = $ansresult->RecordCount();


    $hint_comment = $clang->gT('Please enter your comment here');

    if ($lwcdropdowns == 'R' && $anscount <= $dropdownthreshold)
    {
        $answer .= '<div class="list">
	                    <ul>
                    ';

        while ($ansrow=$ansresult->FetchRow())
        {
            $check_ans = '';
            if ($_SESSION[$ia[1]] == $ansrow['code'])
            {
                $check_ans = CHECKED;
            }
            $answer .= '		<li>
			<input type="radio" name="'.$ia[1].'" id="answer'.$ia[1].$ansrow['code'].'" value="'.$ansrow['code'].'" class="radio" '.$check_ans.' onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)" />
			<label for="answer'.$ia[1].$ansrow['code'].'" class="answertext">'.$ansrow['answer'].'</label>
		</li>
';
        }

        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            if ((!isset($_SESSION[$ia[1]]) || $_SESSION[$ia[1]] == '') ||($_SESSION[$ia[1]] == ' ' ))
            {
                $check_ans = CHECKED;
            }
            elseif ((isset($_SESSION[$ia[1]]) || $_SESSION[$ia[1]] != ''))
            {
                $check_ans = '';
            }
            $answer .= '		<li>
			<input class="radio" type="radio" name="'.$ia[1].'" id="answer'.$ia[1].'" value=" " onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)"'.$check_ans.' />
			<label for="answer'.$ia[1].'" class="answertext">'.$clang->gT('No answer').'</label>
		</li>
';
        }

        $fname2 = $ia[1].'comment';
        if ($anscount > 8) {$tarows = $anscount/1.2;} else {$tarows = 4;}
        // --> START NEW FEATURE - SAVE
        //    --> START ORIGINAL
        //        $answer .= "\t<td valign='top'>\n"
        //                 . "<textarea class='textarea' name='$ia[1]comment' id='answer$ia[1]comment' rows='$tarows' cols='30'>";
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
	<select class="select" name="'.$ia[1].'" id="answer'.$ia[1].'" onclick="'.$checkconditionFunction.'(this.value, this.name, this.type)" >
';
        // --> END NEW FEATURE - SAVE
        while ($ansrow=$ansresult->FetchRow())
        {
            $check_ans = '';
            if ($_SESSION[$ia[1]] == $ansrow['code'])
            {
                $check_ans = SELECTED;
            }
            $answer .= '		<option value="'.$ansrow['code'].'"'.$check_ans.'>'.$ansrow['answer']."</option>\n";

            if (strlen($ansrow['answer']) > $maxoptionsize)
            {
                $maxoptionsize = strlen($ansrow['answer']);
            }
        }
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            if ((!isset($_SESSION[$ia[1]]) || $_SESSION[$ia[1]] == '') ||($_SESSION[$ia[1]] == ' '))
            {
                $check_ans = SELECTED;
            }
            elseif (isset($_SESSION[$ia[1]]) || $_SESSION[$ia[1]] != '')
            {
                $check_ans = '';
            }
            $answer .= '<option value=""'.$check_ans.'>'.$clang->gT('No answer')."</option>\n";
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
    global $dbprefix, $imageurl, $clang, $thissurvey, $showpopups;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    $answer="";
    if ($qidattributes['random_order']==1) {
        $ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY ".db_random();
    } else {
        $ansquery = "SELECT * FROM {$dbprefix}answers WHERE qid=$ia[0] AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY sortorder, answer";
    }
    $ansresult = db_execute_assoc($ansquery);   //Checked
    $anscount= $ansresult->RecordCount();
    if (trim($qidattributes["max_answers"])!='')
    {
        $max_answers=trim($qidattributes["max_answers"]);
    } else {
        $max_answers=$anscount;
    }
    $finished=$anscount-$max_answers;
    $answer .= "\t<script type='text/javascript'>\n"
    . "\t<!--\n"
    . "function rankthis_{$ia[0]}(\$code, \$value)\n"
    . "\t{\n"
    . "\t\$index=document.getElementById('CHOICES_{$ia[0]}').selectedIndex;\n"
    . "\tfor (i=1; i<=$max_answers; i++)\n"
    . "{\n"
    . "\$b=i;\n"
    . "\$b += '';\n"
    . "\$inputname=\"RANK_{$ia[0]}\"+\$b;\n"
    . "\$hiddenname=\"fvalue_{$ia[0]}\"+\$b;\n"
    . "\$cutname=\"cut_{$ia[0]}\"+i;\n"
    . "document.getElementById(\$cutname).style.display='none';\n"
    . "if (!document.getElementById(\$inputname).value)\n"
    . "\t{\n"
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
    . "\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').selectedIndex=-1;\n"
    . "\t\t\t\t\t$checkconditionFunction(\$code);\n"
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
    . "\t\t\t\t\tif (document.getElementById('CHOICES_{$ia[0]}').options.length > 0)\n"
    . "\t\t\t\t\t\t{\n"
    . "\t\t\t\t\t\tdocument.getElementById('CHOICES_{$ia[0]}').disabled=false;\n"
    . "\t\t\t\t\t\t}\n"
    . "\t\t\t\t\t$checkconditionFunction('');\n"
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
        $ranklist .= "\t<tr><td class=\"position\">&nbsp;<label for='RANK_{$ia[0]}$i'>"
        ."$i:&nbsp;</label></td><td class=\"item\"><input class=\"text\" type=\"text\" name=\"RANK_{$ia[0]}$i\" id=\"RANK_{$ia[0]}$i\"";
        if (isset($_SESSION[$myfname]) && $_SESSION[$myfname])
        {
            $ranklist .= " value='";
            $ranklist .= htmlspecialchars($thistext, ENT_QUOTES);
            $ranklist .= "'";
        }
        $ranklist .= " onfocus=\"this.blur()\" />\n";
        $ranklist .= "<input type=\"hidden\" name=\"$myfname\" id=\"fvalue_{$ia[0]}$i\" value='";
        $chosen[]=""; //create array
        if (isset($_SESSION[$myfname]) && $_SESSION[$myfname])
        {
            $ranklist .= $thiscode;
            $chosen[]=array($thiscode, $thistext);
        }
        $ranklist .= "' />\n";
        $ranklist .= "<img src=\"$imageurl/cut.gif\" alt=\"".$clang->gT("Remove this item")."\" title=\"".$clang->gT("Remove this item")."\" ";
        if ($i != $existing)
        {
            $ranklist .= "style=\"display:none\"";
        }
        $ranklist .= " id=\"cut_{$ia[0]}$i\" onclick=\"deletethis_{$ia[0]}(document.getElementById('RANK_{$ia[0]}$i').value, document.getElementById('fvalue_{$ia[0]}$i').value, document.getElementById('RANK_{$ia[0]}$i').name, this.id)\" /><br />\n";
        $inputnames[]=$myfname;
        $ranklist .= "</td></tr>\n";
    }

    $maxselectlength=0;
    $choicelist = "<select size=\"$anscount\" name=\"CHOICES_{$ia[0]}\" ";
    if (isset($choicewidth)) {$choicelist.=$choicewidth;}
    $choicelist .= " id=\"CHOICES_{$ia[0]}\" onclick=\"if (this.options.length>0 && this.selectedIndex<0) {this.options[this.options.length-1].selected=true;}; rankthis_{$ia[0]}(this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)\" class=\"select\">\n";

        foreach ($answers as $ans)
        {
            if (!in_array($ans, $chosen))
            {
                $choicelist .= "\t\t\t\t\t\t\t<option value='{$ans[0]}'>{$ans[1]}</option>\n";
            }
        if (strlen($ans[1]) > $maxselectlength) {$maxselectlength = strlen($ans[1]);}
        }
    $choicelist .= "</select>\n";

    $answer .= "\t<table border='0' cellspacing='0' class='rank'>\n"
    . "<tr>\n"
    . "\t<td align='left' valign='top' class='rank label'>\n"
    . "<strong>&nbsp;&nbsp;<label for='CHOICES_{$ia[0]}'>".$clang->gT("Your Choices").":</label></strong><br />\n"
    . "&nbsp;".$choicelist
    . "\t&nbsp;</td>\n";
    $maxselectlength=$maxselectlength+2;
    if ($maxselectlength > 60)
    {
        $maxselectlength=60;
    }
    $ranklist = str_replace("<input class=\"text\"", "<input size='{$maxselectlength}' class='text'", $ranklist);
    $answer .= "\t<td style=\"text-align:left; white-space:nowrap;\" class='rank output'>\n"
        . "\t<table border='0' cellspacing='1' cellpadding='0'>\n"
        . "\t<tr><td></td><td><strong>".$clang->gT("Your Ranking").":</strong></td></tr>\n";

    $answer .= $ranklist
    . "\t</table>\n"
    . "\t</td>\n"
    . "</tr>\n"
    . "<tr>\n"
    . "\t<td colspan='2' class='rank helptext'><font size='1'>\n"
    . "".$clang->gT("Click on the scissors next to each item on the right to remove the last entry in your ranked list")
    . "\t</font size='1'></td>\n"
    . "</tr>\n"
    . "\t</table>\n";

    if (trim($qidattributes["min_answers"])!='')
    {
        $minansw=trim($qidattributes["min_answers"]);
        if(!isset($showpopups) || $showpopups == 0)
        {
            $answer .= "<div id='rankingminanswarning{$ia[0]}' style='display: none; color: red' class='errormandatory'>"
                       .sprintf($clang->ngT("Please rank at least %d item for question \"%s\"","Please rank at least %d items for question \"%s\".",$minansw),$minansw, trim(str_replace(array("\n", "\r"), "", $ia[3])))."</div>";
        }
        $minanswscript = "<script type='text/javascript'>\n"
        . "  <!--\n"
        . "  oldonsubmit_{$ia[0]} = document.limesurvey.onsubmit;\n"
        . "  function ensureminansw_{$ia[0]}()\n"
        . "  {\n"
        . "     count={$anscount} - document.getElementById('CHOICES_{$ia[0]}').options.length;\n"
        . "     if (count < {$minansw} && document.getElementById('display{$ia[0]}').value == 'on'){\n";
        if(!isset($showpopups) || $showpopups == 0)
        {
            $minanswscript .= "\n
			document.getElementById('rankingminanswarning{$ia[0]}').style.display='';\n";
        } else {
            $minanswscript .="
                    alert('".sprintf($clang->ngT("Please rank at least %d item for question \"%s\"","Please rank at least %d items for question \"%s\"",$minansw,'js'),$minansw, trim(javascript_escape(str_replace(array("\n", "\r"), "",$ia[3]),true,true)))."');\n";
        }
        $minanswscript .= ""
        . "     return false;\n"
        . "   } else {\n"
        . "     if (oldonsubmit_{$ia[0]}){\n"
        . "         return oldonsubmit_{$ia[0]}();\n"
        . "     }\n"
        . "     return true;\n"
        . "     }\n"
        . "  }\n"
        . "  document.limesurvey.onsubmit = ensureminansw_{$ia[0]}\n"
        . "  -->\n"
        . "  </script>\n";
        $answer = $minanswscript . $answer;
    }

    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_multiplechoice($ia)
{
    global $dbprefix, $clang, $connect, $thissurvey;

    // Find out if any questions have attributes which reference this questions
    // based on value of attribute. This could be array_filter and array_filter_exclude

    $attribute_ref=false;
    $inputnames=array();

    $qaquery = "SELECT qid,attribute FROM ".db_table_name('question_attributes')." WHERE value LIKE '".strtolower($ia[2])."' and (attribute='array_filter' or attribute='array_filter_exclude')";
    $qaresult = db_execute_assoc($qaquery);     //Checked
    while($qarow = $qaresult->FetchRow())
    {
        $qquery = "SELECT qid FROM ".db_table_name('questions')." WHERE sid=".$thissurvey['sid']." AND scale_id=0 AND qid=".$qarow['qid'];
        $qresult = db_execute_assoc($qquery);     //Checked
        if ($qresult->RecordCount() > 0)
        {
            $attribute_ref = true;
        }
    }

    if ($ia[8] == 'Y' || $attribute_ref === true)
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);

    if (trim($qidattributes['other_replace_text'])!='')
    {
        $othertext=$clang->gT($qidattributes['other_replace_text']);
    }
    else
    {
        $othertext=$clang->gT('Other:');
    }

    if (trim($qidattributes['display_columns'])!='')
    {
        $dcols = $qidattributes['display_columns'];
    }
    else
    {
        $dcols = 1;
    }

    if ($qidattributes['other_numbers_only']==1)
    {
        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeperator= $sSeperator['seperator'];
        $numbersonly = 'return goodchars(event,"0123456789'.$sSeperator.'")';
    }
    else
    {
        $numbersonly = '';
    }

    // Check if the max_answers attribute is set
    $maxansw = 0;
    $callmaxanswscriptcheckbox = '';
    $callmaxanswscriptother = '';
    $maxanswscript = '';

    $exclude_all_others_auto = trim($qidattributes["exclude_all_others_auto"]);

    if ($exclude_all_others_auto=='1'){
        $autoArray['list'][]=$ia[1];
        $autoArray[$ia[1]]['parent'] = $ia[1];
    }

    if (trim($qidattributes['exclude_all_others'])!='')
    {
        $excludeallothers=explode(';',trim($qidattributes['exclude_all_others']));
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


    if (((int)$qidattributes['max_answers']>0) && $exclude_all_others_auto=='0')
    {
        $maxansw=$qidattributes['max_answers'];
        $callmaxanswscriptcheckbox = "limitmaxansw_{$ia[0]}(this);";
        $callmaxanswscriptother = "onkeyup='limitmaxansw_{$ia[0]}(this)'";
        $maxanswscript = "\t<script type='text/javascript'>\n"
        . "\t<!--\n"
        . "function limitmaxansw_{$ia[0]}(me)\n"
        . "{\n"
        . "\tmax=$maxansw\n"
        . "\tcount=0;\n"
        . "\tif (max == 0) { return count; }\n";
    }


    // Check if the min_answers attribute is set
    $minansw=0;
    $minanswscript = "";

    if ((int)$qidattributes['min_answers']>0)
    {
        $minansw=trim($qidattributes["min_answers"]);
        $minanswscript = "<script type='text/javascript'>\n"
        . "\t<!--\n"
        . "oldonsubmit_{$ia[0]} = document.limesurvey.onsubmit;\n"
        . "function ensureminansw_{$ia[0]}()\n"
        . "{\n"
        . "\tcount=0;\n"
        ;
    }

    $qquery = "SELECT other FROM ".db_table_name('questions')." WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."' and parent_qid=0";
    $qresult = db_execute_assoc($qquery);     //Checked
    while($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}

    if ($qidattributes['random_order']==1) {
        $ansquery = "SELECT * FROM ".db_table_name('questions')." WHERE parent_qid=$ia[0] AND scale_id=0 AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
    }
    else
    {
        $ansquery = "SELECT * FROM ".db_table_name('questions')." WHERE parent_qid=$ia[0] AND scale_id=0 AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
    }

    $ansresult = $connect->GetAll($ansquery);  //Checked
    $anscount = count($ansresult);

    if (trim($qidattributes['exclude_all_others'])!='' && $qidattributes['random_order']==1)
    {
        //if  exclude_all_others is set then the related answer should keep its position at all times
        //thats why we have to re-position it if it has been randomized
        $position=0;
        foreach ($ansresult as $answer)
        {
            if ((trim($qidattributes['exclude_all_others']) != '')  &&    ($answer['title']==trim($qidattributes['exclude_all_others'])))
            {
                if ($position==$answer['question_order']-1) break; //already in the right position
                $tmp  = array_splice($ansresult, $position, 1);
                array_splice($ansresult, $answer['question_order']-1, 0, $tmp);
                break;
            }
            $position++;
        }
    }


    if ($other == 'Y')
    {
        $anscount++; //COUNT OTHER AS AN ANSWER FOR MANDATORY CHECKING!
    }

    $wrapper = setup_columns($dcols, $anscount);

    $answer = '<input type="hidden" name="MULTI'.$ia[1].'" value="'.$anscount."\" />\n\n".$wrapper['whole-start'];

    $fn = 1;
    if (!isset($multifields))
    {
        $multifields = '';
    }

    $rowcounter = 0;
    $colcounter = 1;
    $startitem='';
    $postrow = '';
    $trbc='';
    foreach ($ansresult as $ansrow)
    {
        $myfname = $ia[1].$ansrow['title'];

        if ($exclude_all_others_auto==1){
            if ($ansrow['title']==trim($qidattributes['exclude_all_others'])){
                $autoArray[$ia[1]]['focus'] = $ia[1].trim($qidattributes['exclude_all_others']);
            }
            else{
                $autoArray[$ia[1]]['children'][] = $myfname;
            }
        }

        $trbc='';
        /* Check for array_filter */
        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname, "li");

        if($wrapper['item-start'] == "\t<li>\n")
        {
            $startitem = "\t$htmltbody2\n";
        } else {
            $startitem = $wrapper['item-start'];
        }

        /* Print out the checkbox */
        $answer .= $startitem;
        $answer .= "\t$hiddenfield\n";
        $answer .= '		<input class="checkbox" type="checkbox" name="'.$ia[1].$ansrow['title'].'" id="answer'.$ia[1].$ansrow['title'].'" value="Y"';

        /* If the question has already been ticked, check the checkbox */
        if (isset($_SESSION[$myfname]))
        {
            if ($_SESSION[$myfname] == 'Y')
            {
                $answer .= CHECKED;
                if(in_array($ansrow['title'], $excludeallothers))
                {
                    $postrow.="\n\n<script type='text/javascript'>\n<!--\nexcludeAllOthers$ia[1]('answer$ia[1]{$ansrow['title']}', 'no');\n-->\n</script>\n";
                }
            }
        }
        $answer .= " onclick='cancelBubbleThis(event);";
        /* Exclude all others coding */
        if(in_array($ansrow['title'], $excludeallothers))
        {
            $answer .= "excludeAllOthers$ia[1](this.id, \"yes\");"; // was "this.id"
            $excludeallotherscripton .= "/* SKIPPING QUESTION {$ia[1]} */\n";
            //			$excludeallotherscripton .= "alert(value+'---'+'answer$ia[1]{$ansrow['code']}');\n";
            $excludeallotherscripton .= "if( value != 'answer$ia[1]{$ansrow['title']}') {\n"
            . "\tthiselt=document.getElementById('answer$ia[1]{$ansrow['title']}');\n"
            . "thiselt.checked='';\n"
            . "thiselt.disabled='true';\n"
            . "if (doconditioncheck == 'yes') {\n"
            . "\t$checkconditionFunction(thiselt.value, thiselt.name, thiselt.type);\n"
            . "}\n}\n";
            $excludeallotherscriptoff .= "document.getElementById('answer$ia[1]{$ansrow['title']}').disabled='';\n";
        }
        elseif (count($excludeallothers)>0)
        {
            $excludeallotherscripton .= "\tthiselt=document.getElementById('answer$ia[1]{$ansrow['title']}');\n"
            . "thiselt.checked='';\n"
            . "thiselt.disabled='true';\n"
            . "if (doconditioncheck == 'yes') {\n"
            . "\t$checkconditionFunction(thiselt.value, thiselt.name, thiselt.type);\n"
            . "}\n";
            $excludeallotherscriptoff.= "document.getElementById('answer$ia[1]{$ansrow['title']}').disabled='';\n";
        }
        /* End of exclude all others coding */

        $answer .= $callmaxanswscriptcheckbox    	/* Include checkbox for script for maxanswers if that attribute is selected */
        .  "$checkconditionFunction(this.value, this.name, this.type)' />\n"
        .  "<label for=\"answer$ia[1]{$ansrow['title']}\" class=\"answertext\">"
        .  $ansrow['question']
        .  "</label>\n";


        if ($maxansw > 0) {$maxanswscript .= "\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}
        if ($minansw > 0) {$minanswscript .= "\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}

        ++$fn;
        /* Now add the hidden field to contain information about this answer */
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
    if ($exclude_all_others_auto==1){
        $answer .= "<script type='text/javascript'>autoArray = ".json_encode($autoArray).";</script>";
    }
    if ($other == 'Y')
    {
        $myfname = $ia[1].'other';
        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, array("code"=>"other"), $myfname, $trbc, $myfname, "li");
        if(count($excludeallothers) > 0)
        {
            $excludeallotherscripton .= "thiselt=document.getElementById('answer{$ia[1]}othercbox');\n"
            . "thiselt.checked='';\n"
            . "thiselt.disabled='true';\n";
            $excludeallotherscripton .= "thiselt=document.getElementById('answer$ia[1]other');\n"
            . "thiselt.value='';\n"
            . "thiselt.disabled='true';\n"
            . "if (doconditioncheck == 'yes') {\n"
            . "\t$checkconditionFunction(thiselt.value, thiselt.name, thiselt.type);\n"
            . "}\n";
            $excludeallotherscriptoff .="document.getElementById('answer$ia[1]other').disabled='';\n";
            $excludeallotherscriptoff .="document.getElementById('answer{$ia[1]}othercbox').disabled='';\n";
        }
        if($wrapper['item-start'] == "\t<li>\n")
        {
            $startitem = "\t$htmltbody2\n";
        } else {
            $startitem = $wrapper['item-start'];
        }
        $answer .= $startitem;
		$answer .= $hiddenfield.'
		<input class="checkbox" type="checkbox" name="'.$myfname.'cbox" alt="'.$clang->gT('Other').'" id="answer'.$myfname.'cbox"';

        if (isset($_SESSION[$myfname]) && trim($_SESSION[$myfname])!='')
        {
            $answer .= CHECKED;
        }
        $answer .= " onclick='cancelBubbleThis(event);".$callmaxanswscriptcheckbox."if(this.checked===false) document.getElementById(\"answer$myfname\").value=\"\";";
        $answer .= " if(this.checked===true) document.getElementById(\"answer$myfname\").focus();";
        $answer .= " $checkconditionFunction(document.getElementById(\"answer$myfname\").value, document.getElementById(\"answer$myfname\").name, document.getElementById(\"answer$myfname\").type);";
        $answer .= "' />
		<label for=\"answer$myfname\" class=\"answertext\">".$othertext."</label>
		<input class=\"text\" type=\"text\" name=\"$myfname\" id=\"answer$myfname\"";
        if (isset($_SESSION[$myfname]))
        {
            $answer .= ' value="'.htmlspecialchars($_SESSION[$myfname],ENT_QUOTES).'"';
        }
        $answer .= " onkeyup='$checkconditionFunction(this.value, this.name, this.type);if ($.trim($(\"#answer{$myfname}\").val())!=\"\" && !document.getElementById(\"answer{$myfname}cbox\").checked) {\$(\"#answer{$myfname}cbox\").attr(\"checked\",\"checked\");} $numbersonly ".$callmaxanswscriptcheckbox."' />";
        $answer .= '<input type="hidden" name="java'.$myfname.'" id="java'.$myfname.'" value="';

        if ($maxansw > 0)
        {
            // For multiplechoice question there is no DB field for the other Checkbox
            // I've added a javascript which will warn a user if no other comment is given while the other checkbox is checked
            // For the maxanswer script, I will alert the participant
            // if the limit is reached when he checks the other cbox
            // even if the -other- input field is still empty
            $maxanswscript .= "\tif (document.getElementById('answer".$myfname."cbox').checked ) { count += 1; }\n";
        }
        if ($minansw > 0)
        {
            //
            // For multiplechoice question there is no DB field for the other Checkbox
            // We only count the -other- as valid if both the cbox and the other text is filled
            $minanswscript .= "\tif (document.getElementById('answer".$myfname."').value != '' && document.getElementById('answer".$myfname."cbox').checked ) { count += 1; }\n";
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
        $maxanswscript .= "
        if (count > max)
        {
            alert('".sprintf($clang->gT("Please choose at most %d answers for question \"%s\"","js"), $maxansw, trim(javascript_escape(str_replace(array("\n", "\r"), "", $ia[3]),true,true)))."');
            if (me.type == 'checkbox') {me.checked = false;}
            if (me.type == 'text') {
                me.value = '';
                if (document.getElementById('answer'+me.name + 'cbox') ){
                    document.getElementById('answer'+me.name + 'cbox').checked = false;
                }
            }
            return max;
        }
}
//-->
</script>\n";
        $answer = $maxanswscript . $answer;
    }


    if ( $minansw > 0 )
    {
        $minanswscript .=
			"\tif (count < {$minansw} && document.getElementById('display{$ia[0]}').value == 'on'){\n"
        . "alert('".sprintf($clang->gT("Please choose at least %d answer(s) for question \"%s\"","js"),
        $minansw, trim(javascript_escape(str_replace(array("\n", "\r"), "",$ia[3]),true,true)))."');\n"
        . "return false;\n"
        . "\t} else {\n"
        . "if (oldonsubmit_{$ia[0]}){\n"
        . "\treturn oldonsubmit_{$ia[0]}();\n"
        . "}\n"
        . "return true;\n"
        . "\t}\n"
        . "}\n"
        . "document.limesurvey.onsubmit = ensureminansw_{$ia[0]}\n"
        . "-->\n"
        . "\t</script>\n";
        //$answer = $minanswscript . $answer;
    }

    $checkotherscript = "";
    if ($other == 'Y')
    {
        // Multiple choice with 'other' is a specific case as the checkbox isn't recorded into DB
        // this means that if it is cehcked We must force the end-user to enter text in the input
        // box
        $checkotherscript = "<script type='text/javascript'>\n"
        . "\t<!--\n"
        . "oldonsubmitOther_{$ia[0]} = document.limesurvey.onsubmit;\n"
        . "function ensureOther_{$ia[0]}()\n"
        . "{\n"
        . "\tothercboxval=document.getElementById('answer".$myfname."cbox').checked;\n"
        . "\totherval=document.getElementById('answer".$myfname."').value;\n"
        . "\tif (otherval != '' || othercboxval != true) {\n"
        . "if(typeof oldonsubmitOther_{$ia[0]} == 'function') {\n"
        . "\treturn oldonsubmitOther_{$ia[0]}();\n"
        . "}\n"
        . "\t}\n"
        . "\telse {\n"
        . "alert('".sprintf($clang->gT("You've marked the \"other\" field for question \"%s\". Please also fill in the accompanying \"other comment\" field.","js"),trim(javascript_escape($ia[3],true,true)))."');\n"
        . "return false;\n"
        . "\t}\n"
        . "}\n"
        . "document.limesurvey.onsubmit = ensureOther_{$ia[0]};\n"
        . "\t-->\n"
        . "</script>\n";
    }

    $answer = $minanswscript . $checkotherscript . $answer;

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
    global $dbprefix, $clang, $thissurvey;

    $attribute_ref=false;
    $qaquery = "SELECT qid,attribute FROM ".db_table_name('question_attributes')." WHERE value LIKE '".strtolower($ia[2])."'";
    $qaresult = db_execute_assoc($qaquery);     //Checked
    $attribute_ref=false;
    while($qarow = $qaresult->FetchRow())
    {
        $qquery = "SELECT qid FROM ".db_table_name('questions')." WHERE sid=".$thissurvey['sid']." AND qid=".$qarow['qid'];
        $qresult = db_execute_assoc($qquery);     //Checked
        if ($qresult->RecordCount() > 0)
        {
            $attribute_ref = true;
        }
    }

    if ($ia[8] == 'Y' || $attribute_ref == true)
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);

    if ($qidattributes['other_numbers_only']==1)
    {
        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeperator = $sSeperator['seperator'];
        $numbersonly = 'onkeypress="return goodchars(event,\'-0123456789'.$sSeperator.'\')"';
    }
    else
    {
        $numbersonly = '';
    }

    if (trim($qidattributes['other_replace_text'])!='')
    {
        $othertext=$clang->gT($qidattributes['other_replace_text']);
    }
    else
    {
        $othertext=$clang->gT('Other:');
    }
    // Check if the max_answers attribute is set
    $maxansw=0;
    $callmaxanswscriptcheckbox = '';
    $callmaxanswscriptcheckbox2 = '';
    $callmaxanswscriptother = '';
    $maxanswscript = '';
    if (trim($qidattributes['max_answers'])!='') {
        $maxansw=$qidattributes['max_answers'];
        $callmaxanswscriptcheckbox = "limitmaxansw_{$ia[0]}(this);";
        $callmaxanswscriptcheckbox2= "limitmaxansw_{$ia[0]}";
        $callmaxanswscriptother = "onkeyup=\"limitmaxansw_{$ia[0]}(this)\"";

        $maxanswscript = "\t<script type='text/javascript'>\n"
        . "\t<!--\n"
        . "function limitmaxansw_{$ia[0]}(me)\n"
        . "\t{\n"
        . "\tmax=$maxansw\n"
        . "\tcount=0;\n"
        . "\tif (max == 0) { return count; }\n";
    }

    // Check if the min_answers attribute is set
    $minansw=0;
    $minanswscript = "";
    if (trim($qidattributes["min_answers"])!='')
    {
        $minansw=trim($qidattributes["min_answers"]);
        $minanswscript = "<script type='text/javascript'>\n"
        . "\t<!--\n"
        . "oldonsubmit_{$ia[0]} = document.limesurvey.onsubmit;\n"
        . "function ensureminansw_{$ia[0]}()\n"
        . "{\n"
        . "\tcount=0;\n"
        ;
    }

    $qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."' and parent_qid=0";
    $qresult = db_execute_assoc($qquery);     //Checked
    while ($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}
    if ($qidattributes['random_order']==1) {
        $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0]  AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
    } else {
        $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0]  AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
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
        $myfname = $ia[1].$ansrow['title'];
        $trbc='';
        /* Check for array_filter */

        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname, "li");

        if($label_width < strlen(trim(strip_tags($ansrow['question']))))
        {
            $label_width = strlen(trim(strip_tags($ansrow['question'])));
        }

        $myfname2 = $myfname."comment";
        $startitem = "\t$htmltbody2\n";
        /* Print out the checkbox */
        $answer_main .= $startitem;
        $answer_main .= "\t$hiddenfield\n";
        $answer_main .= "<span class=\"option\">\n"
        . "\t<label for=\"answer$myfname\" class=\"answertext\">\n"
        . "\t<input class=\"checkbox\" type=\"checkbox\" name=\"$myfname\" id=\"answer$myfname\" value=\"Y\"";

        /* If the question has already been ticked, check the checkbox */
        if (isset($_SESSION[$myfname]))
        {
            if ($_SESSION[$myfname] == 'Y')
            {
                $answer_main .= CHECKED;
            }
        }
        $answer_main .=" onclick='cancelBubbleThis(event);".$callmaxanswscriptcheckbox."$checkconditionFunction(this.value, this.name, this.type);' "
        . " onchange='document.getElementById(\"answer$myfname2\").value=\"\";' />\n"
        . $ansrow['question']."</label>\n";

        if ($maxansw > 0) {$maxanswscript .= "\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}
        if ($minansw > 0) {$minanswscript .= "\tif (document.getElementById('answer".$myfname."').checked) { count += 1; }\n";}

        $answer_main .= "<input type='hidden' name='java$myfname' id='java$myfname' value='";
        if (isset($_SESSION[$myfname]))
        {
            $answer_main .= $_SESSION[$myfname];
        }
        $answer_main .= "' />\n";
        $fn++;
        $answer_main .= "</span>\n<span class=\"comment\">\n\t<label for='answer$myfname2' class=\"answer-comment\">\n"
        ."<input class='text' type='text' size='40' id='answer$myfname2' name='$myfname2' title='".$clang->gT("Make a comment on your choice here:")."' value='";
        if (isset($_SESSION[$myfname2])) {$answer_main .= htmlspecialchars($_SESSION[$myfname2],ENT_QUOTES);}
        // --> START NEW FEATURE - SAVE
        $answer_main .= "'  onclick='cancelBubbleThis(event);' onkeypress='if (jQuery.trim($(\"#answer{$myfname2}\").val())!=\"\") { if document.getElementById(\"answer{$myfname}\").checked=true;$checkconditionFunction(document.getElementById(\"answer{$myfname}\").value,\"$myfname\",\"checkbox\");}' onkeyup='".$callmaxanswscriptcheckbox2."(document.getElementById(\"answer{$myfname}\"))' />\n\t</label>\n</span>\n"

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
        $answer_main .= "\t<li class=\"other\">\n<span class=\"option\">\n"
        . "\t<label for=\"answer$myfname\" class=\"answertext\">\n".$othertext."\n<input class=\"text other\" $numbersonly type=\"text\" name=\"$myfname\" id=\"answer$myfname\" title=\"".$clang->gT('Other').'" size="10"';
        if (isset($_SESSION[$myfname]) && $_SESSION[$myfname])
        {
            $answer_main .= ' value="'.htmlspecialchars($_SESSION[$myfname],ENT_QUOTES).'"';
        }
        $fn++;
        // --> START NEW FEATURE - SAVE
        $answer_main .= "  $callmaxanswscriptother />\n\t</label>\n</span>\n"
        . "<span class=\"comment\">\n\t<label for=\"answer$myfname2\" class=\"answer-comment\">\n"
        . '
				<input class="text" type="text" size="40" name="'.$myfname2.'" id="answer'.$myfname2.'" title="'.$clang->gT('Make a comment on your choice here:').'" value="';
        // --> END NEW FEATURE - SAVE

        if (isset($_SESSION[$myfname2])) {$answer_main .= htmlspecialchars($_SESSION[$myfname2],ENT_QUOTES);}
        // --> START NEW FEATURE - SAVE
        $answer_main .= '" onkeyup="'.$callmaxanswscriptcheckbox2.'(document.getElementById(\'answer'.$myfname."'))\" />\n";

        if ($maxansw > 0)
        {
            if ($qidattributes['other_comment_mandatory']==1)
            {
                $maxanswscript .= "\tif (document.getElementById('answer".$myfname."').value != '' && document.getElementById('answer".$myfname2."').value != '') { count += 1; }\n";
            }
            else
            {
                $maxanswscript .= "\tif (document.getElementById('answer".$myfname."').value != '') { count += 1; }\n";
            }
        }

        if ($minansw > 0)
        {
            if ($qidattributes['other_comment_mandatory']==1)
            {
                $minanswscript .= "\tif (document.getElementById('answer".$myfname."').value != '' && document.getElementById('answer".$myfname2."').value != '') { count += 1; }\n";
            }
            else
            {
                $minanswscript .= "\tif (document.getElementById('answer".$myfname."').value != '') { count += 1; }\n";
            }
        }

        $answer_main .= "\t</label>\n</span>\n\t</li>\n";
        // --> END NEW FEATURE - SAVE

        $inputnames[]=$myfname;
        $inputnames[]=$myfname2;
    }
    $answer .= "<ul>\n".$answer_main."</ul>\n";


    if ( $maxansw > 0 )
    {
        $maxanswscript .= "\tif (count > max)\n"
        . "{\n"
        . "alert('".sprintf($clang->gT("Please choose at most %d answers for question \"%s\"","js"), $maxansw, trim(javascript_escape($ia[3],true,true)))."');\n"
        . "var commentname='answer'+me.name+'comment';\n"
        . "if (me.type == 'checkbox') {\n"
        . "\tme.checked = false;\n"
        . "\tvar commentname='answer'+me.name+'comment';\n"
        . "}\n"
        . "if (me.type == 'text') {\n"
        . "\tme.value = '';\n"
        . "\tif (document.getElementById(me.name + 'cbox') ){\n"
        . " document.getElementById(me.name + 'cbox').checked = false;\n"
        . "\t}\n"
        . "}"
        . "document.getElementById(commentname).value='';\n"
        . "return max;\n"
        . "}\n"
        . "\t}\n"
        . "\t//-->\n"
        . "\t</script>\n";
        $answer = $maxanswscript . $answer;
    }

    if ( $minansw > 0 )
    {
        $minanswscript .=
			"\tif (count < {$minansw} && document.getElementById('display{$ia[0]}').value == 'on'){\n"
        . "alert('".sprintf($clang->gT("Please choose at least %d answer(s) for question \"%s\"","js"),
        $minansw, trim(javascript_escape(str_replace(array("\n", "\r"), "",$ia[3]),true,true)))."');\n"
        . "return false;\n"
        . "\t} else {\n"
        . "if (oldonsubmit_{$ia[0]}){\n"
        . "\treturn oldonsubmit_{$ia[0]}();\n"
        . "}\n"
        . "return true;\n"
        . "\t}\n"
        . "}\n"
        . "document.limesurvey.onsubmit = ensureminansw_{$ia[0]}\n"
        . "-->\n"
        . "\t</script>\n";
        //$answer = $minanswscript . $answer;
    }

    $checkotherscript = "";
    //if ($other == 'Y' && $qidattributes['other_comment_mandatory']==1) //TIBO
    if ($other == 'Y' && $qidattributes['other_comment_mandatory']==1) //TIBO
    {
        // Multiple choice with 'other' is a specific case as the checkbox isn't recorded into DB
        // this means that if it is cehcked We must force the end-user to enter text in the input
        // box
        $checkotherscript = "<script type='text/javascript'>\n"
        . "\t<!--\n"
        . "oldonsubmitOther_{$ia[0]} = document.limesurvey.onsubmit;\n"
        . "function ensureOther_{$ia[0]}()\n"
        . "{\n"
        . "\tothercommentval=document.getElementById('answer".$myfname2."').value;\n"
        . "\totherval=document.getElementById('answer".$myfname."').value;\n"
        . "\tif (otherval != '' && othercommentval == '') {\n"
        . "alert('".sprintf($clang->gT("You've marked the \"other\" field for question \"%s\". Please also fill in the accompanying \"other comment\" field.","js"),trim(javascript_escape($ia[3],true,true)))."');\n"
        . "return false;\n"
        . "\t}\n"
        . "\telse {\n"
        . "if(typeof oldonsubmitOther_{$ia[0]} == 'function') {\n"
        . "\treturn oldonsubmitOther_{$ia[0]}();\n"
        . "}\n"
        . "\t}\n"
        . "}\n"
        . "document.limesurvey.onsubmit = ensureOther_{$ia[0]};\n"
        . "\t-->\n"
        . "</script>\n";
    }

    $answer = $minanswscript . $checkotherscript . $answer;

    return array($answer, $inputnames);
}



// ---------------------------------------------------------------
function do_file_upload($ia)
{
    global $clang, $js_header_includes, $thissurvey, $surveyid;

    if ($ia[8] == 'Y')
        $checkconditionFunction = "checkconditions";
    else
        $checkconditionFunction = "noop_checkconditions";

   	$qidattributes=getQuestionAttributes($ia[0]);

    // Fetch question attributes
    $_SESSION['fieldname'] = $ia[1];

    // Basic uploader
  /*  $basic  = '<br /><br /><table border="0" cellpadding="10" cellspacing="10" align="center">'
                    .'<tr>';
    if ($_SESSION['show_title']) { $basic .= '<th align="center"><b>Title</b></th><th>&nbsp;&nbsp;</th>'; }
    if ($_SESSION['show_comment']) { $basic .= '<th align="center"><b>Comment</b></th><th>&nbsp;&nbsp;</th>'; }
    $basic .=           '<th align="center"><b>Select file</b></th>'
                    .'</tr>'
                    .'<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>'
                    .'<tbody>';

    for ($i = 1; $i <= $_SESSION['maxfiles']; $i++) {
         $basic .= '<tr>'
                        .'<td>';
         if ($_SESSION['show_title'])
             $basic .=      '<input class="basic_'.$ia[1].'" type="text" name="'.$ia[1].'_title_'.$i
                            .'" id="'.$ia[1].'_title_'.$i.'" value="'.$_SESSION[$ia[1]]
                            .'" maxlength="100" />'
                        .'</td>'
                        .'<td>&nbsp;&nbsp;</td>';
         if ($_SESSION['show_comment'])
             $basic .=  '<td>'
                            .'<input class="basic_'.$ia[1].'" type="textarea" name="'.$ia[1].'_comment_'.$i
                            .'" id="'.$ia[1].'_comment_'.$i.'" value="'.$_SESSION[$ia[1]]
                            .'" maxlength="100" />'
                        .'</td>'
                        .'<td>&nbsp;&nbsp;</td>';

         $basic .=      '<td>'
                            .' <input class="basic_'.$ia[1].'" '
                            .'type="file" name="'.$ia[1].'_file_'.$i.'" id="'.$ia[1].'_'.$i.'" alt="'
                            .$clang->gT("Answer").'" ></input></td>'
                        .'</tr>'
                        .'<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
    }

    $basic .= '</tbody></table>';
    $basic .= '<br /><br /><a href="#" onclick="hideBasic()">Hide Simple Uploader</a>';
*/
    $currentdir = getcwd();
    $pos = stripos($currentdir, "admin");

    if ($pos)
    {
        $_SESSION['preview'] = 1;
        $questgrppreview = 1;   // Preview is launched from Question or group level
        $scriptloc = '../uploader.php';
    }
    else if ($thissurvey['active'] != "Y")
    {
        $_SESSION['preview'] = 1;
        $questgrppreview = 0;
        $scriptloc = 'uploader.php';
    }
    else
    {
        $_SESSION['preview'] = 0;
        $questgrppreview = 0;
        $scriptloc = 'uploader.php';
    }

    $uploadbutton = "<h2><a id='upload_".$ia[1]."' class='upload' href='$scriptloc?sid=$surveyid&fieldname=".$ia[1]."&qid=".$ia[0]."&preview="
    .$questgrppreview."&show_title=".$qidattributes['show_title']."&show_comment="
    .$qidattributes['show_comment']."&pos=".($pos?1:0)."'>Upload files</a></h2><br /><br />";

    if ($pos)
        $answer = "<script type='text/javascript' src='../scripts/modaldialog.js'></script>";
    else
        $answer = "<script type='text/javascript' src='scripts/modaldialog.js'></script>";

    // Modal dialog
    $answer .= $uploadbutton;

    $answer .= "<input type='hidden' id='".$ia[1]."' name='".$ia[1]."' value='".$_SESSION[$ia[1]]."' />";
    $answer .= "<input type='hidden' id='".$ia[1]."_filecount' name='".$ia[1]."_filecount' value=";

    if (array_key_exists($ia[1]."_filecount", $_SESSION))
        $answer .= $_SESSION[$ia[1]."_filecount"]." />";
    else
        $answer .= "0 />";

    $answer .= "<div id='".$ia[1]."_uploadedfiles'></div>";

    //$answer .= '<br />Trouble uploading files? Try the <a href="#" onclick="showBasic()">Simple Uploader</a><div id="basic">'.$basic.'</div>';

    $answer .= '<script type="text/javascript">
                    $(document).ready(function(){
                        var fieldname = "'.$ia[1].'";
                        var filecount = $("#"+fieldname+"_filecount").val();
                        var json = $("#"+fieldname).val();
                        var show_title = "'.$qidattributes["show_title"].'";
                        var show_comment = "'.$qidattributes["show_comment"].'";
                        var pos = "'.($pos ? 1 : 0).'";
                        displayUploadedFiles(json, filecount, fieldname, show_title, show_comment, pos);
                    });
                </script>';

    $answer .= '<script type="text/javascript">
                    $(".basic_'.$ia[1].'").change(function() {
                        var i;
                        var jsonstring = "[";

                        for (i = 1, filecount = 0; i <= '.$qidattributes['max_num_of_files'].'; i++)
                        {
                            if ($("#'.$ia[1].'_"+i).val() == "")
                                continue;

                            filecount++;
                            if (i != 1)
                                jsonstring += ", ";

                            if ($("#answer'.$ia[1].'_"+i).val() != "")
                                jsonstring += "{';

    if ($qidattributes['show_title'])
        $answer .= '\"title\":\""+$("#'.$ia[1].'_title_"+i).val()+"\",';
    else
        $answer .= '\"title\":\"\",';

    if ($qidattributes['show_comment'])
        $answer .= '\"comment\":\""+$("#'.$ia[1].'_comment_"+i).val()+"\",';
    else
        $answer .= '\"comment\":\"\",';

    $answer .= '\"size\":\"\",\"name\":\"\",\"ext\":\"\"}";
                        }
                        jsonstring += "]";

                        $("#'.$ia[1].'").val(jsonstring);
                        $("#'.$ia[1].'_filecount").val(filecount);
                    });
                </script>';

    $inputnames[] = $ia[1];
    $inputnames[] = $ia[1]."_filecount";
    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_multipleshorttext($ia)
{
    global $dbprefix, $clang, $thissurvey;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }
    $answer='';
    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);

    if ($qidattributes['numbers_only']==1)
    {
        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeperator = $sSeperator['seperator'];
		$numbersonly = 'onkeypress="return goodchars(event,\'-0123456789'.$sSeperator.'\')"';
    }
    else
    {
        $numbersonly = '';
    }
    if (trim($qidattributes['maximum_chars'])!='')
    {
        $maxsize=$qidattributes['maximum_chars'];
    }
    else
    {
        $maxsize=255;
    }
    if (trim($qidattributes['text_input_width'])!='')
    {
        $tiwidth=$qidattributes['text_input_width'];
    }
    else
    {
        $tiwidth=20;
    }

    if (trim($qidattributes['prefix'])!='') {
        $prefix=$qidattributes['prefix'];
    }
    else
    {
        $prefix = '';
    }

    if (trim($qidattributes['suffix'])!='') {
        $suffix=$qidattributes['suffix'];
    }
    else
    {
        $suffix = '';
    }

    if ($thissurvey['nokeyboard']=='Y')
    {
        vIncludeKeypad();
        $kpclass = "text-keypad";
    }
    else
    {
        $kpclass = "";
    }

    if ($qidattributes['random_order']==1) {
        $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0]  AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
    }
    else
    {
        $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0]  AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
    }

    $ansresult = db_execute_assoc($ansquery);    //Checked
    $anscount = $ansresult->RecordCount()*2;
    //$answer .= "\t<input type='hidden' name='MULTI$ia[1]' value='$anscount'>\n";
    $fn = 1;

    $answer_main = '';

    $label_width = 0;

    if ($anscount==0)
    {
        $inputnames=array();
        $answer_main .= '	<li>'.$clang->gT('Error: This question has no answers.')."</li>\n";
    }
    else
    {
        if (trim($qidattributes['display_rows'])!='')
        {
            //question attribute "display_rows" is set -> we need a textarea to be able to show several rows
            $drows=$qidattributes['display_rows'];

            //extend maximum chars if this is set to short text default of 255
            if($maxsize == 255)
            {
                $maxsize=65525;
            }

            //some JS to check max possible input
            $answer = "<script type='text/javascript'>
               <!--
               function textLimit(field, maxlen) {
                if (document.getElementById(field).value.length > maxlen)
                document.getElementById(field).value = document.getElementById(field).value.substring(0, maxlen);
                }
               //-->
               </script>\n";



            while ($ansrow = $ansresult->FetchRow())
            {
                $myfname = $ia[1].$ansrow['title'];
                if ($ansrow['question'] == "")
                {
                    $ansrow['question'] = "&nbsp;";
                }

                //NEW: textarea instead of input=text field
                $answer_main .= "\t<li>\n"
                . "<label for=\"answer$myfname\">{$ansrow['question']}</label>\n"
                . "\t<span>\n".$prefix."\n".'
				<textarea class="textarea '.$kpclass.'" name="'.$myfname.'" id="answer'.$myfname.'"
				rows="'.$drows.'" cols="'.$tiwidth.'" onkeyup="textLimit(\'answer'.$myfname.'\', '.$maxsize.'); '.$checkconditionFunction.'(this.value, this.name, this.type);" '.$numbersonly.'>';

                if($label_width < strlen(trim(strip_tags($ansrow['question']))))
                {
                    $label_width = strlen(trim(strip_tags($ansrow['question'])));
                }

                if (isset($_SESSION[$myfname]))
                {
                    $answer_main .= $_SESSION[$myfname];
                }

                $answer_main .= "</textarea>\n".$suffix."\n\t</span>\n"
                . "\t</li>\n";

                $fn++;
                $inputnames[]=$myfname;
            }

        }
        else
        {
            while ($ansrow = $ansresult->FetchRow())
            {
                $myfname = $ia[1].$ansrow['title'];
                if ($ansrow['question'] == "") {$ansrow['question'] = "&nbsp;";}
                $answer_main .= "\t<li>\n"
                . "<label for=\"answer$myfname\">{$ansrow['question']}</label>\n"
                . "\t<span>\n".$prefix."\n".'<input class="text '.$kpclass.'" type="text" size="'.$tiwidth.'" name="'.$myfname.'" id="answer'.$myfname.'" value="';

                if($label_width < strlen(trim(strip_tags($ansrow['question']))))
                {
                    $label_width = strlen(trim(strip_tags($ansrow['question'])));
                }

                if (isset($_SESSION[$myfname]))
                {
                    $answer_main .= $_SESSION[$myfname];
                }

                // --> START NEW FEATURE - SAVE
                $answer_main .= '" onkeyup="'.$checkconditionFunction.'(this.value, this.name, this.type);" '.$numbersonly.' maxlength="'.$maxsize.'" />'."\n".$suffix."\n\t</span>\n"
                . "\t</li>\n";
                // --> END NEW FEATURE - SAVE

                $fn++;
                $inputnames[]=$myfname;
            }

        }
    }

    $answer .= "<ul>\n".$answer_main."</ul>\n";

    return array($answer, $inputnames);
}


// ---------------------------------------------------------------
function do_multiplenumeric($ia)
{
    global $dbprefix, $clang, $js_header_includes, $css_header_includes, $thissurvey;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }
    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    $answer='';
    $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeperator = $sSeperator['seperator'];
    //Must turn on the "numbers only javascript"
    $numbersonly = 'onkeypress="inputField = event.srcElement ? event.srcElement : event.target || event.currentTarget; if (inputField.value.indexOf(\''.$sSeperator.'\')>0 && String.fromCharCode(getkey(event))==\''.$sSeperator.'\') return false; return goodchars(event,\'0123456789'.$sSeperator.'\')"';
    if (trim($qidattributes['maximum_chars'])!='')
    {
        $maxsize=$qidattributes['maximum_chars'];
    }
    else
    {
        $maxsize = 25;
    }

    //EQUALS VALUE
    if (trim($qidattributes['equals_num_value'])!=''){
        $equals_num_value=$qidattributes['equals_num_value'];
        $numbersonlyonblur[]='calculateValue'.$ia[1].'(3)';
        $calculateValue[]=3;
    }
    elseif (trim($qidattributes['num_value_equals_sgqa'])!='' && isset($_SESSION[$qidattributes['num_value_equals_sgqa']]))
    {
        $equals_num_value=$_SESSION[$qidattributes['num_value_equals_sgqa']];
        $numbersonlyonblur[]='calculateValue'.$ia[1].'(3)';
        $calculateValue[]=3;
    }
    else
    {
        $equals_num_value=0;
    }

    //MIN VALUE
    if (trim($qidattributes['min_num_value'])!=''){
        $min_num_value=$qidattributes['min_num_value'];
        $numbersonlyonblur[]='calculateValue'.$ia[1].'(2)';
        $calculateValue[]=2;
    }
    elseif (trim($qidattributes['min_num_value_sgqa'])!='' && isset($_SESSION[$qidattributes['min_num_value_sgqa']])){
        $min_num_value=$_SESSION[$qidattributes['min_num_value_sgqa']];
        $numbersonlyonblur[]='calculateValue'.$ia[1].'(2)';
        $calculateValue[]=2;
    }
    else
    {
        $min_num_value=0;
    }

    //MAX VALUE
    if (trim($qidattributes['max_num_value'])!=''){
        $max_num_value = $qidattributes['max_num_value'];
        $numbersonlyonblur[]='calculateValue'.$ia[1].'(1)';
        $calculateValue[]=1;
    }
    elseif (trim($qidattributes['max_num_value_sgqa'])!='' && isset($_SESSION[$qidattributes['max_num_value_sgqa']])){
        $max_num_value = $_SESSION[$qidattributes['max_num_value_sgqa']];
        $numbersonlyonblur[]='calculateValue'.$ia[1].'(1)';
        $calculateValue[]=1;
    }
    else
    {
        $max_num_value = 0;
    }

    if (trim($qidattributes['prefix'])!='') {
        $prefix=$qidattributes['prefix'];
    }
    else
    {
        $prefix = '';
    }

    if (trim($qidattributes['suffix'])!='') {
        $suffix=$qidattributes['suffix'];
    }
    else
    {
        $suffix = '';
    }

    if ($thissurvey['nokeyboard']=='Y')
    {
        vIncludeKeypad();
        $kpclass = "num-keypad";
    }
    else
    {
        $kpclass = "";
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

    if (trim($qidattributes['text_input_width'])!='')
    {
        $tiwidth=$qidattributes['text_input_width'];
    }
    else
    {
        $tiwidth=10;
    }
    if ($qidattributes['slider_layout']==1)
    {
        $slider_layout=true;
        $css_header_includes[]= '/scripts/jquery/css/start/jquery-ui.css';


        if (trim($qidattributes['slider_accuracy'])!='')
        {
            //$slider_divisor = 1 / $slider_accuracy['value'];
            $decimnumber = strlen($qidattributes['slider_accuracy']) - strpos($qidattributes['slider_accuracy'],'.') -1;
            $slider_divisor = pow(10,$decimnumber);
            $slider_stepping = $qidattributes['slider_accuracy'] * $slider_divisor;
            //	error_log('acc='.$slider_accuracy['value']." div=$slider_divisor stepping=$slider_stepping");
        }
        else
        {
            $slider_divisor = 1;
            $slider_stepping = 1;
        }

        if (trim($qidattributes['slider_min'])!='')
        {
            $slider_mintext = $qidattributes['slider_min'];
            $slider_min = $qidattributes['slider_min'] * $slider_divisor;
        }
        else
        {
            $slider_mintext = 0;
            $slider_min = 0;
        }
        if (trim($qidattributes['slider_max'])!='')
        {
            $slider_maxtext = $qidattributes['slider_max'];
            $slider_max = $qidattributes['slider_max'] * $slider_divisor;
        }
        else
        {
            $slider_maxtext = "100";
            $slider_max = 100 * $slider_divisor;
        }
        if (trim($qidattributes['slider_default'])!='')
        {
            $slider_default = $qidattributes['slider_default'];
        }
        else
        {
            $slider_default = '';
        }
        if ($slider_default == '' && $qidattributes['slider_middlestart']==1)
        {
            $slider_middlestart = intval(($slider_max + $slider_min)/2);
        }
        else
        {
            $slider_middlestart = '';
        }

        if (trim($qidattributes['slider_separator'])!='')
        {
            $slider_separator = $qidattributes['slider_separator'];
        }
        else
        {
            $slider_separator = '';
        }
    }
    else
    {
        $slider_layout = false;
    }
    $hidetip=$qidattributes['hide_tip'];
    if ($slider_layout === true) // auto hide tip when using sliders
    {
        $hidetip=1;
    }

    if ($qidattributes['random_order']==1)
    {
        $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0]  AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
    }
    else
    {
        $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0]  AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
    }

    $ansresult = db_execute_assoc($ansquery);	//Checked
    $anscount = $ansresult->RecordCount()*2;
    //$answer .= "\t<input type='hidden' name='MULTI$ia[1]' value='$anscount'>\n";
    $fn = 1;

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
            $myfname = $ia[1].$ansrow['title'];
            if ($ansrow['question'] == "") {$ansrow['question'] = "&nbsp;";}
            if ($slider_layout === false || $slider_separator == '')
            {
                $theanswer = $ansrow['question'];
                $sliderleft='';
                $sliderright='';
            }
            else
            {
                $answer_and_slider_array=explode($slider_separator,$ansrow['question']);
                if (isset($answer_and_slider_array[0]))
                $theanswer=$answer_and_slider_array[0];
                else
                $theanswer="";
                if (isset($answer_and_slider_array[1]))
                $sliderleft=$answer_and_slider_array[1];
                else
                $sliderleft="";
                if (isset($answer_and_slider_array[2]))
                $sliderright=$answer_and_slider_array[2];
                else
                $sliderright="";

                $sliderleft="<div class=\"slider_lefttext\">$sliderleft</div>";
                $sliderright="<div class=\"slider_righttext\">$sliderright</div>";
            }

            if ($slider_layout === false)
            {
                $answer_main .= "\t<li>\n<label for=\"answer$myfname\">{$theanswer}</label>\n";

            }
            else
            {
                $answer_main .= "\t<li>\n<label for=\"answer$myfname\" class=\"slider-label\">{$theanswer}</label>\n";

            }

            if($label_width < strlen(trim(strip_tags($ansrow['question']))))
            {
                $label_width = strlen(trim(strip_tags($ansrow['question'])));
            }

            if ($slider_layout === false)
            {
                $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
                $sSeperator = $sSeperator['seperator'];


                $answer_main .= "<span class=\"input\">\n\t".$prefix."\n\t<input class=\"text $kpclass\" type=\"text\" size=\"".$tiwidth.'" name="'.$myfname.'" id="answer'.$myfname.'" value="';
                if (isset($_SESSION[$myfname]))
                {
                    $_SESSION[$myfname] = str_replace('.',$sSeperator,$_SESSION[$myfname]);
                    $answer_main .= $_SESSION[$myfname];
                }

                $answer_main .= '" onkeyup="'.$checkconditionFunction.'(this.value, this.name, this.type);" '.$numbersonly.' maxlength="'.$maxsize."\" />\n\t".$suffix."\n</span>\n\t</li>\n";

            }
            else
            {

                if ($qidattributes['slider_showminmax']==1)
                {
                    //$slider_showmin=$slider_min;
                    $slider_showmin= "\t<div id=\"slider-left-$myfname\" class=\"slider_showmin\">$slider_mintext</div>\n";
                    $slider_showmax= "\t<div id=\"slider-right-$myfname\" class=\"slider_showmax\">$slider_maxtext</div>\n";
                }
                else
                {
                    $slider_showmin='';
                    $slider_showmax='';
                }

                $js_header_includes[] = '/scripts/jquery/jquery-ui.js';
                $js_header_includes[] = '/scripts/jquery/lime-slider.js';

                if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] != '')
                {
                    $slider_startvalue = $_SESSION[$myfname] * $slider_divisor;
                    $displaycallout_atstart=1;
                }
                elseif ($slider_default != "")
                {
                    $slider_startvalue = $slider_default * $slider_divisor;
                    $displaycallout_atstart=1;
                }
                elseif ($slider_middlestart != '')
                {
                    $slider_startvalue = $slider_middlestart;
                    $displaycallout_atstart=0;
                }
                else
                {
                    $slider_startvalue = 'NULL';
                    $displaycallout_atstart=0;
                }
                $answer_main .= "$sliderleft<div id='container-$myfname' class='multinum-slider'>\n"
                . "\t<input type=\"text\" id=\"slider-modifiedstate-$myfname\" value=\"$displaycallout_atstart\" style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-param-min-$myfname\" value=\"$slider_min\" style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-param-max-$myfname\" value=\"$slider_max\" style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-param-stepping-$myfname\" value=\"$slider_stepping\" style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-param-divisor-$myfname\" value=\"$slider_divisor\" style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-param-startvalue-$myfname\" value='$slider_startvalue' style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-onchange-js-$myfname\" value=\"$numbersonly_slider\" style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-prefix-$myfname\" value=\"$prefix\" style=\"display: none;\" />\n"
                . "\t<input type=\"text\" id=\"slider-suffix-$myfname\" value=\"$suffix\" style=\"display: none;\" />\n"
                . "<div id=\"slider-$myfname\" class=\"ui-slider-1\">\n"
                .  $slider_showmin
                . "<div class=\"slider_callout\" id=\"slider-callout-$myfname\"></div>\n"
                . "<div class=\"ui-slider-handle\" id=\"slider-handle-$myfname\"></div>\n"
                . $slider_showmax
                . "\t</div>"
                . "</div>$sliderright\n"
                . "<input class=\"text\" type=\"text\" name=\"$myfname\" id=\"answer$myfname\" style=\"display: none;\" value=\"";
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

            //			$answer .= "\t</tr>\n";

            $fn++;
            $inputnames[]=$myfname;
        }
        $question_tip = '';
        if($hidetip == 0)
        {
            $question_tip .= '<p class="tip">'.$clang->gT('Only numbers may be entered in these fields')."</p>\n";
        }
        if ($max_num_value)
        {
            $question_tip .= '<p id="max_num_value_'.$ia[1].'" class="tip">'.sprintf($clang->gT('Total of all entries must not exceed %d'), $max_num_value)."</p>\n";
        }
        if ($equals_num_value)
        {
            $question_tip .= '<p id="equals_num_value_'.$ia[1].'" class="tip">'.sprintf($clang->gT('Total of all entries must equal %d'),$equals_num_value)."</p>\n";
        }
        if ($min_num_value)
        {
            $question_tip .= '<p id="min_num_value_'.$ia[1].'" class="tip">'.sprintf($clang->gT('Total of all entries must be at least %s'),$min_num_value)."</p>\n";
        }

        if ($max_num_value || $equals_num_value || $min_num_value)
        {
            $answer_computed = '';
            if ($equals_num_value)
            {
                $answer_computed .= "\t<li class='multiplenumerichelp'>\n<label for=\"remainingvalue_{$ia[1]}\">\n\t".$clang->gT('Remaining: ')."\n</label>\n<span>\n\t$prefix\n\t<input size=10 type='text' id=\"remainingvalue_{$ia[1]}\" disabled=\"disabled\" />\n\t$suffix\n</span>\n\t</li>\n";
            }
            $answer_computed .= "\t<li class='multiplenumerichelp'>\n<label for=\"totalvalue_{$ia[1]}\">\n\t".$clang->gT('Total: ')."\n</label>\n<span>\n\t$prefix\n\t<input size=10  type=\"text\" id=\"totalvalue_{$ia[1]}\" disabled=\"disabled\" />\n\t$suffix\n</span>\n\t</li>\n";
            $answer_main.=$answer_computed;
        }
        $answer .= $question_tip."<ul>\n".$answer_main."</ul>\n";
    }
    //just added these here so its easy to change in one place
    $errorClass = 'tip problem';
    $goodClass = 'tip good';
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
    $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeperator = $sSeperator['seperator'];
    if ($max_num_value || $equals_num_value || $min_num_value)
    { //Do value validation
        $answer .= '<input type="hidden" name="qattribute_answer[]" value="'.$ia[1]."\" />\n";
        $answer .= '<input type="hidden" name="qattribute_answer'.$ia[1]."\" />\n";

        $answer .= "<script type='text/javascript'>\n";
        $answer .= "    function calculateValue".$ia[1]."(method) {\n";
        //Make all empty fields 0 (or else calculation won't work
        foreach ($inputnames as $inputname)
        {
            $answer .= "       if(document.limesurvey.answer".$inputname.".value == '') { document.limesurvey.answer".$inputname.".value = 0; }\n";
            $javainputnames[]="parseInt(parseFloat((document.limesurvey.answer".$inputname.".value).split(',').join('.'))*1000)";
        }
        $answer .= "       bob = eval('document.limesurvey.qattribute_answer".$ia[1]."');\n";
        $answer .= "       totalvalue_".$ia[1]."=(";
        $answer .= implode(" + ", $javainputnames);
        $answer .= ")/1000;\n";
        $answer .= "       $('#totalvalue_{$ia[1]}').val((parseFloat(totalvalue_{$ia[1]})+'').split('.').join('{$sSeperator}'));\n";
        $answer .= "       var ua = navigator.appVersion.indexOf('MSIE');\n";
        $answer .= "       var ieAtt = ua != -1 ? 'className' : 'class';\n";
        $answer .= "       switch(method)\n";
        $answer .= "       {\n";
        $answer .= "       case 1:\n";
        $answer .= "          if (totalvalue_".$ia[1]." > $max_num_value)\n";
        $answer .= "             {\n";
        $answer .= "               bob.value = '".$clang->gT("Answer is invalid. The total of all entries should not add up to more than ").$max_num_value."';\n";
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
        $answer .= "               bob.value = '".sprintf($clang->gT("Answer is invalid. The total of all entries should add up to at least %s.",'js'),$min_num_value)."';\n";
        //		$answer .= "               document.getElementById('totalvalue_".$ia[1]."').style.color='red';\n";
        //		$answer .= "               document.getElementById('min_num_value_".$ia[1]."').style.color='red';\n";
        $answer .= "               document.getElementById('totalvalue_".$ia[1]."').setAttribute(ieAtt,'" . $errorClass . "');\n";
        $answer .= "               document.getElementById('min_num_value_".$ia[1]."').setAttribute(ieAtt,'" . $errorClass . "');\n";
        $answer .= "             }\n";
        $answer .= "             else\n";
        $answer .= "             {\n";
        $answer .= "               if (bob.value == '' || bob.value == '".sprintf($clang->gT("Answer is invalid. The total of all entries should add up to at least %s.",'js'),$min_num_value)."')\n";
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
    global $clang, $thissurvey;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }
    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    if (trim($qidattributes['prefix'])!='') {
        $prefix=$qidattributes['prefix'];
    }
    else
    {
        $prefix = '';
    }
    if (trim($qidattributes['suffix'])!='') {
        $suffix=$qidattributes['suffix'];
    }
    else
    {
        $suffix = '';
    }
    if (trim($qidattributes['maximum_chars'])!='')
    {
        $maxsize=$qidattributes['maximum_chars'];
        if ($maxsize>20)
        {
            $maxsize=20;
        }
    }
    else
    {
        $maxsize=20;  // The field length for numerical fields is 20
    }
    if (trim($qidattributes['text_input_width'])!='')
    {
        $tiwidth=$qidattributes['text_input_width'];
    }
    else
    {
        $tiwidth=10;
    }

    if (trim($qidattributes['num_value_int_only'])==1)
    {
        $acomma="";
    }
    else
    {
        $acomma=getRadixPointData($thissurvey['surveyls_numberformat']);
        $acomma = $acomma['seperator'];

    }
    $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
    $sSeperator = $sSeperator['seperator'];
    $_SESSION[$ia[1]] = str_replace('.',$sSeperator,$_SESSION[$ia[1]]);

    if ($thissurvey['nokeyboard']=='Y')
    {
        vIncludeKeypad();
        $kpclass = "num-keypad";
    }
    else
    {
        $kpclass = "";
    }
    // --> START NEW FEATURE - SAVE
    $answer = "<p class=\"question\">\n\t$prefix\n\t<input class=\"text $kpclass\" type=\"text\" size=\"$tiwidth\" name=\"$ia[1]\" "
    . "id=\"answer{$ia[1]}\" value=\"{$_SESSION[$ia[1]]}\" alt=\"".$clang->gT('Answer')."\" onkeypress=\"return goodchars(event,'-0123456789{$acomma}')\" onchange='$checkconditionFunction(this.value, this.name, this.type)'"
    . "maxlength=\"{$maxsize}\" />\n\t{$suffix}\n</p>\n";
    if ($qidattributes['hide_tip']==0)
    {
        $answer .= "<p class=\"tip\">".$clang->gT('Only numbers may be entered in this field')."</p>\n";
    }

    // --> END NEW FEATURE - SAVE

    $inputnames[]=$ia[1];
    $mandatory=null;
    return array($answer, $inputnames, $mandatory);
}




// ---------------------------------------------------------------
function do_shortfreetext($ia)
{
    global $clang, $js_header_includes, $thissurvey,$googleMapsAPIKey;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);

    if ($qidattributes['numbers_only']==1)
    {
        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
        $sSeperator = $sSeperator['seperator'];
        $numbersonly = 'onkeypress="return goodchars(event,\'0123456789'.$sSeperator.'\')"';
    }
    else
    {
        $numbersonly = '';
    }
    if (trim($qidattributes['maximum_chars'])!='')
    {
        $maxsize=$qidattributes['maximum_chars'];
    }
    else
    {
        $maxsize=255;
    }
    if (trim($qidattributes['text_input_width'])!='')
    {
        $tiwidth=$qidattributes['text_input_width'];
    }
    else
    {
        $tiwidth=50;
    }
    if (trim($qidattributes['prefix'])!='') {
        $prefix=$qidattributes['prefix'];
    }
    else
    {
        $prefix = '';
    }
    if (trim($qidattributes['suffix'])!='') {
        $suffix=$qidattributes['suffix'];
    }
    else
    {
        $suffix = '';
    }
    if ($thissurvey['nokeyboard']=='Y')
    {
        vIncludeKeypad();
        $kpclass = "text-keypad";
    }
    else
    {
        $kpclass = "";
    }
    if (trim($qidattributes['display_rows'])!='')
    {
        //question attribute "display_rows" is set -> we need a textarea to be able to show several rows
        $drows=$qidattributes['display_rows'];

        //extend maximum chars if this is set to short text default of 255
        if($maxsize == 255)
        {
            $maxsize=65525;
        }

        //if a textarea should be displayed we make it equal width to the long text question
        //this looks nicer and more continuous
        if($tiwidth == 50)
        {
            $tiwidth=40;
        }


        //some JS to check max possible input
        $answer = "<script type='text/javascript'>
               <!--
               function textLimit(field, maxlen) {
                if (document.getElementById(field).value.length > maxlen)
                document.getElementById(field).value = document.getElementById(field).value.substring(0, maxlen);
                }
               //-->
               </script>\n";

        //NEW: textarea instead of input=text field

        // --> START NEW FEATURE - SAVE
        $answer .= '<textarea class="textarea '.$kpclass.'" name="'.$ia[1].'" id="answer'.$ia[1].'" '
        .'rows="'.$drows.'" cols="'.$tiwidth.'" onkeyup="textLimit(\'answer'.$ia[1].'\', '.$maxsize.'); '.$checkconditionFunction.'(this.value, this.name, this.type);" '.$numbersonly.'>';
        // --> END NEW FEATURE - SAVE

        if ($_SESSION[$ia[1]]) {$answer .= str_replace("\\", "", $_SESSION[$ia[1]]);}

        $answer .= "</textarea>\n";
    }
    elseif((int)($qidattributes['location_mapservice'])!=0){

        $mapservice = $qidattributes['location_mapservice'];
        $currentLocation = $_SESSION[$ia[1]];
        $currentLatLong = null;

        $floatLat = 0;
        $floatLng = 0;

        // Get the latitude/longtitude for the point that needs to be displayed by default
        if (strlen($currentLocation) > 2){
            $currentLatLong = explode(';',$currentLocation);
            $currentLatLong = array($currentLatLong[0],$currentLatLong[1]);
        }
        else{
            if ((int)($qidattributes['location_nodefaultfromip'])==0)
                $currentLatLong = getLatLongFromIp($_SERVER['REMOTE_ADDR']);
            if (!isset($currentLatLong) || $currentLatLong==false){
                $floatLat = 0;
                $floatLng = 0;
                $LatLong = explode(" ",trim($qidattributes['location_defaultcoordinates']));

                if (isset($LatLong[0]) && isset($LatLong[1])){
                    $floatLat = $LatLong[0];
                    $floatLng = $LatLong[1];
                }

                $currentLatLong = array($floatLat,$floatLng);
            }
        }
        // 2 - city; 3 - state; 4 - country; 5 - postal
        $strBuild = "";
        if ($qidattributes['location_city'])
            $strBuild .= "2";
        if ($qidattributes['location_state'])
            $strBuild .= "3";
        if ($qidattributes['location_country'])
            $strBuild .= "4";
        if ($qidattributes['location_postal'])
            $strBuild .= "5";

        $currentLocation = $currentLatLong[0] . " " . $currentLatLong[1];
        $answer = "
        	<script type=\"text/javascript\">
        		zoom['$ia[1]'] = {$qidattributes['location_mapzoom']};
        	</script>
            <p class=\"question\">
            <input type=\"hidden\" name=\"$ia[1]\" id=\"answer$ia[1]\" value=\"{$_SESSION[$ia[1]]}\">

            <input class=\"text location\" type=\"text\" size=\"20\" name=\"$ia[1]_c\"
                id=\"answer$ia[1]_c\" value=\"$currentLocation\"
                onkeyup=\"$checkconditionFunction(this.value, this.name, this.type)\" />
            </p>

            <input type=\"hidden\" name=\"boycott_$ia[1]\" id=\"boycott_$ia[1]\"
                value = \"{$strBuild}\" >
            <input type=\"hidden\" name=\"mapservice_$ia[1]\" id=\"mapservice_$ia[1]\"
                class=\"mapservice\" value = \"{$qidattributes['location_mapservice']}\" >
            <div id=\"gmap_canvas_$ia[1]_c\" style=\"width: {$qidattributes['location_mapwidth']}px; height: {$qidattributes['location_mapheight']}px\"></div>";

        if ($qidattributes['location_mapservice']==1)
            $js_header_includes[] = "http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key={$googleMapsAPIKey}";
        elseif ($qidattributes['location_mapservice']==2)
            $js_header_includes[] = "http://www.openlayers.org/api/OpenLayers.js";

	    if (isset($qidattributes['hide_tip']) && $qidattributes['hide_tip']==0)
            {
                $answer .= "<br />\n<span class=\"questionhelp\">"
                . $clang->gT('Drag and drop the pin to the desired location. You may also right click on the map to move the pin.').'</span>';
                $question_text['help'] = $clang->gT('Drag and drop the pin to the desired location. You may also right click on the map to move the pin.');
            }


    }
    else
    {
        //no question attribute set, use common input text field
        $answer = "<p class=\"question\">\n\t$prefix\n\t<input class=\"text $kpclass\" type=\"text\" size=\"$tiwidth\" name=\"$ia[1]\" id=\"answer$ia[1]\" value=\""
        .htmlspecialchars($_SESSION[$ia[1]],ENT_QUOTES,'UTF-8')
        ."\" maxlength=\"$maxsize\" onkeyup=\"$checkconditionFunction(this.value, this.name, this.type)\" $numbersonly />\n\t$suffix\n</p>\n";
    }


    if (trim($qidattributes['time_limit'])!='')
    {
		$js_header_includes[] = '/scripts/coookies.js';
        $answer .= return_timer_script($qidattributes, $ia, "answer".$ia[1]);
    }

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);

}

function getLatLongFromIp($ip){
    global $ipInfoDbAPIKey;
    $xml = simplexml_load_file("http://api.ipinfodb.com/v2/ip_query.php?key=$ipInfoDbAPIKey&ip=$ip&timezone=false");
    if ($xml->{'Status'} == "OK"){
        $lat = (float)$xml->{'Latitude'};
        $lng = (float)$xml->{'Longitude'};

        return(array($lat,$lng));
    }
    else
        return false;
}



// ---------------------------------------------------------------
function do_longfreetext($ia)
{
    global $clang, $js_header_includes;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

   	$qidattributes=getQuestionAttributes($ia[0],$ia[4]);

    if (trim($qidattributes['maximum_chars'])!='')
    {
        $maxsize=$qidattributes['maximum_chars'];
    }
    else
    {
        $maxsize=65525;
    }

    // --> START ENHANCEMENT - DISPLAY ROWS
    if (trim($qidattributes['display_rows'])!='')
    {
        $drows=$qidattributes['display_rows'];
    }
    else
    {
        $drows=5;
    }
    // <-- END ENHANCEMENT - DISPLAY ROWS

    // --> START ENHANCEMENT - TEXT INPUT WIDTH
    if (trim($qidattributes['text_input_width'])!='')
    {
        $tiwidth=$qidattributes['text_input_width'];
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


    // --> START NEW FEATURE - SAVE
    $answer .= '<textarea class="textarea" name="'.$ia[1].'" id="answer'.$ia[1].'" alt="'.$clang->gT('Answer').'" '
    .'rows="'.$drows.'" cols="'.$tiwidth.'" onkeyup="textLimit(\'answer'.$ia[1].'\', '.$maxsize.'); '.$checkconditionFunction.'(this.value, this.name, this.type)">';
    // --> END NEW FEATURE - SAVE

    if ($_SESSION[$ia[1]]) {$answer .= str_replace("\\", "", $_SESSION[$ia[1]]);}

    $answer .= "</textarea>\n";

    if (trim($qidattributes['time_limit'])!='')
    {
		$js_header_includes[] = '/scripts/coookies.js';
        $answer .= return_timer_script($qidattributes, $ia, "answer".$ia[1]);
    }

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_hugefreetext($ia)
{
    global $clang;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);

    if (trim($qidattributes['maximum_chars'])!='')
    {
        $maxsize=$qidattributes['maximum_chars'];
    }
    else
    {
        $maxsize=65525;
    }

    // --> START ENHANCEMENT - DISPLAY ROWS
    if (trim($qidattributes['display_rows'])!='')
    {
        $drows=$qidattributes['display_rows'];
    }
    else
    {
        $drows=30;
    }
    // <-- END ENHANCEMENT - DISPLAY ROWS

    // --> START ENHANCEMENT - TEXT INPUT WIDTH
    if (trim($qidattributes['text_input_width'])!='')
    {
        $tiwidth=$qidattributes['text_input_width'];
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
    $answer .= '<textarea class="textarea" name="'.$ia[1].'" id="answer'.$ia[1].'" alt="'.$clang->gT('Answer').'" '
    .'rows="'.$drows.'" cols="'.$tiwidth.'" onkeyup="textLimit(\'answer'.$ia[1].'\', '.$maxsize.'); '.$checkconditionFunction.'(this.value, this.name, this.type)">';
    // --> END NEW FEATURE - SAVE

    // <-- END ENHANCEMENT - TEXT INPUT WIDTH
    // <-- END ENHANCEMENT - DISPLAY ROWS

    if ($_SESSION[$ia[1]]) {$answer .= str_replace("\\", "", $_SESSION[$ia[1]]);}

    $answer .= "</textarea>\n";

    if (trim($qidattributes['time_limit']) != '')
    {
		$js_header_includes[] = '/scripts/coookies.js';
        $answer .= return_timer_script($qidattributes, $ia, "answer".$ia[1]);
    }

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_yesno($ia)
{
    global $clang;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $answer = "<ul>\n"
    . "\t<li>\n<input class=\"radio\" type=\"radio\" name=\"{$ia[1]}\" id=\"answer{$ia[1]}Y\" value=\"Y\"";

    if ($_SESSION[$ia[1]] == 'Y')
    {
        $answer .= CHECKED;
    }
    // --> START NEW FEATURE - SAVE
    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$ia[1]}Y\" class=\"answertext\">\n\t".$clang->gT('Yes')."\n</label>\n\t</li>\n"
    . "\t<li>\n<input class=\"radio\" type=\"radio\" name=\"{$ia[1]}\" id=\"answer{$ia[1]}N\" value=\"N\"";
    // --> END NEW FEATURE - SAVE

    if ($_SESSION[$ia[1]] == 'N')
    {
        $answer .= CHECKED;
    }
    // --> START NEW FEATURE - SAVE
    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$ia[1]}N\" class=\"answertext\" >\n\t".$clang->gT('No')."\n</label>\n\t</li>\n";
    // --> END NEW FEATURE - SAVE

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
    {
        $answer .= "\t<li>\n<input class=\"radio\" type=\"radio\" name=\"{$ia[1]}\" id=\"answer{$ia[1]}\" value=\"\"";
        if ($_SESSION[$ia[1]] == '')
        {
            $answer .= CHECKED;
        }
        // --> START NEW FEATURE - SAVE
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer{$ia[1]}\" class=\"answertext\">\n\t".$clang->gT('No answer')."\n</label>\n\t</li>\n";
        // --> END NEW FEATURE - SAVE
    }

    $answer .= "</ul>\n\n<input type=\"hidden\" name=\"java{$ia[1]}\" id=\"java{$ia[1]}\" value=\"{$_SESSION[$ia[1]]}\" />\n";
    $inputnames[]=$ia[1];
    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_gender($ia)
{
    global $clang;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);

    $answer = "<ul>\n"
    . "\t<li>\n"
    . '		<input class="radio" type="radio" name="'.$ia[1].'" id="answer'.$ia[1].'F" value="F"';
    if ($_SESSION[$ia[1]] == 'F')
    {
        $answer .= CHECKED;
    }
    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
    . '		<label for="answer'.$ia[1].'F" class="answertext">'.$clang->gT('Female')."</label>\n\t</li>\n";

    $answer .= "\t<li>\n<input class=\"radio\" type=\"radio\" name=\"$ia[1]\" id=\"answer".$ia[1].'M" value="M"';

    if ($_SESSION[$ia[1]] == 'M')
    {
        $answer .= CHECKED;
    }
    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer$ia[1]M\" class=\"answertext\">".$clang->gT('Male')."</label>\n\t</li>\n";

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
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
        $answer .= "\t<li>\n<input class=\"radio\" type=\"radio\" name=\"$ia[1]\" id=\"answer".$ia[1].'" value=""';
        if ($_SESSION[$ia[1]] == '')
        {
            $answer .= CHECKED;
        }
        // --> START NEW FEATURE - SAVE
        $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n<label for=\"answer$ia[1]\" class=\"answertext\">".$clang->gT('No answer')."</label>\n\t</li>\n";
        // --> END NEW FEATURE - SAVE

    }
    $answer .= "</ul>\n\n<input type=\"hidden\" name=\"java$ia[1]\" id=\"java$ia[1]\" value=\"{$_SESSION[$ia[1]]}\" />\n";

    $inputnames[]=$ia[1];
    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
/**
 * DONE: well-formed valid HTML is appreciated
 * Enter description here...
 * @param $ia
 * @return unknown_type
 */
function do_array_5point($ia)
{
    global $dbprefix, $notanswered, $thissurvey, $clang;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }


    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);

    if (trim($qidattributes['answer_width'])!='')
    {
        $answerwidth=$qidattributes['answer_width'];
    }
    else
    {
        $answerwidth = 20;
    }
    $cellwidth  = 5; // number of columns

    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        ++$cellwidth; // add another column
    }
    $cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

    $ansquery = "SELECT question FROM {$dbprefix}questions WHERE parent_qid=".$ia[0]." AND question like '%|%'";
    $ansresult = db_execute_assoc($ansquery);   //Checked

    if ($ansresult->RecordCount()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;}
    // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column


    if ($qidattributes['random_order']==1) {
        $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
    }
    else
    {
        $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
    }

    $ansresult = db_execute_assoc($ansquery);     //Checked
    $anscount = $ansresult->RecordCount();

    $fn = 1;
    $answer = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - a five point Likert scale array\">\n\n"
    . "\t<colgroup class=\"col-responses\">\n"
    . "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";
    $odd_even = '';

    for ($xc=1; $xc<=5; $xc++)
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
    }
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
    }
    $answer .= "\t</colgroup>\n\n"
    . "\t<thead>\n<tr class=\"array1\">\n"
    . "\t<th>&nbsp;</th>\n";
    for ($xc=1; $xc<=5; $xc++)
    {
        $answer .= "\t<th>$xc</th>\n";
    }
    if ($right_exists) {$answer .= "\t<td width='$answerwidth%'>&nbsp;</td>\n";}
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $answer .= "\t<th>".$clang->gT('No answer')."</th>\n";
    }
    $answer .= "</tr></thead>\n";

    $answer_t_content = '';
    $trbc = '';
    $n=0;
    //return array($answer, $inputnames);
    while ($ansrow = $ansresult->FetchRow())
    {
        $myfname = $ia[1].$ansrow['title'];

        $answertext=dTexts::run($ansrow['question']);
        if (strpos($answertext,'|')) {$answertext=substr($answertext,0,strpos($answertext,'|'));}

        /* Check if this item has not been answered: the 'notanswered' variable must be an array,
         containing a list of unanswered questions, the current question must be in the array,
         and there must be no answer available for the item in this session. */
        if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && ($_SESSION[$myfname] == '') ) {
            $answertext = "<span class=\"errormandatory\">{$answertext}</span>";
        }

        $trbc = alternation($trbc , 'row');

        // Get array_filter stuff
        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname);

        $answer_t_content .= $htmltbody2;

        $answer_t_content .= "<tr class=\"$trbc\">\n"
        . "\t<th class=\"answertext\" width=\"$answerwidth%\">\n$answertext\n"
        . $hiddenfield
        . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
        if (isset($_SESSION[$myfname]))
        {
            $answer_t_content .= $_SESSION[$myfname];
        }
        $answer_t_content .= "\" />\n\t</th>\n";
        for ($i=1; $i<=5; $i++)
        {
            $answer_t_content .= "\t<td class=\"answer_cell_00$i\">\n<label for=\"answer$myfname-$i\">"
            ."\n\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-$i\" value=\"$i\" title=\"$i\"";
            if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $i)
            {
                $answer_t_content .= CHECKED;
            }
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n";
        }

        $answertext2=dTexts::run($ansrow['question']);
        if (strpos($answertext2,'|'))
        {
            $answertext2=substr($answertext2,strpos($answertext2,'|')+1);
            $answer_t_content .= "\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">$answertext2</td>\n";
        }
        elseif ($right_exists)
        {
            $answer_t_content .= "\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">&nbsp;</td>\n";
        }


        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            $answer_t_content .= "\t<td>\n<label for=\"answer$myfname-\">"
            ."\n\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer').'"';
            if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == '')
            {
                $answer_t_content .= CHECKED;
            }
            $answer_t_content .= " onclick='$checkconditionFunction(this.value, this.name, this.type)'  />\n</label>\n\t</td>\n";
        }

        $answer_t_content .= "</tr>\n\n\t</tbody>";
        $fn++;
        $inputnames[]=$myfname;
    }

    $answer .= $answer_t_content . "\t</table>\n";
    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
/**
 * DONE: well-formed valid HTML is appreciated
 * Enter description here...
 * @param $ia
 * @return unknown_type
 */
function do_array_10point($ia)
{
    global $dbprefix, $notanswered, $thissurvey, $clang;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]."  AND language='".$_SESSION['s_lang']."'";
    $qresult = db_execute_assoc($qquery);      //Checked
    while($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}

    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    if (trim($qidattributes['answer_width'])!='')
    {
        $answerwidth=$qidattributes['answer_width'];
    }
    else
    {
        $answerwidth = 20;
    }
    $cellwidth  = 10; // number of columns
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        ++$cellwidth; // add another column
    }
    $cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

    if ($qidattributes['random_order']==1) {
        $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
    }
    else
    {
        $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
    }
    $ansresult = db_execute_assoc($ansquery);   //Checked
    $anscount = $ansresult->RecordCount();

    $fn = 1;
    $answer = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - a ten point Likert scale array\" >\n\n"
    . "\t<colgroup class=\"col-responses\">\n"
    . "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";

    $odd_even = '';
    for ($xc=1; $xc<=10; $xc++)
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
    }
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth$\" />\n";
    }
    $answer .= "\t</colgroup>\n\n"
    . "\t<thead>\n<tr class=\"array1\">\n"
    . "\t<th>&nbsp;</th>\n";
    for ($xc=1; $xc<=10; $xc++)
    {
        $answer .= "\t<th>$xc</th>\n";
    }
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $answer .= "\t<th>".$clang->gT('No answer')."</th>\n";
    }
    $answer .= "</tr>\n</thead>";
    $answer_t_content = '';
    $trbc = '';
    while ($ansrow = $ansresult->FetchRow())
    {
        $myfname = $ia[1].$ansrow['title'];
        $answertext=dTexts::run($ansrow['question']);
        /* Check if this item has not been answered: the 'notanswered' variable must be an array,
         containing a list of unanswered questions, the current question must be in the array,
         and there must be no answer available for the item in this session. */
        if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && ($_SESSION[$myfname] == "") ) {
            $answertext = "<span class='errormandatory'>{$answertext}</span>";
        }
        $trbc = alternation($trbc , 'row');

        //Get array filter stuff
        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname);

        $answer_t_content .= $htmltbody2;

        $answer_t_content .= "<tr class=\"$trbc\">\n"
        . "\t<th class=\"answertext\">\n$answertext\n"
        . $hiddenfield
        . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
        if (isset($_SESSION[$myfname]))
        {
            $answer_t_content .= $_SESSION[$myfname];
        }
        $answer_t_content .= "\" />\n\t</th>\n";

        for ($i=1; $i<=10; $i++)
        {
            $answer_t_content .= "\t<td class=\"answer_cell_00$i\">\n<label for=\"answer$myfname-$i\">\n"
            ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-$i\" value=\"$i\" title=\"$i\"";
            if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $i)
            {
                $answer_t_content .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n";
            // --> END NEW FEATURE - SAVE

        }
        if ($ia[6] != "Y" && SHOW_NO_ANSWER == 1)
        {
            $answer_t_content .= "\t<td>\n<label for=\"answer$myfname-\">\n"
            ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer')."\"";
            if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == '')
            {
                $answer_t_content .= CHECKED;
            }
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n";

        }
        $answer_t_content .= "</tr>\n</tbody>";
        $inputnames[]=$myfname;
        $fn++;
    }
    $answer .=  $answer_t_content . "\t\n</table>\n";
    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_array_yesnouncertain($ia)
{
    global $dbprefix, $notanswered, $thissurvey, $clang;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
    $qresult = db_execute_assoc($qquery);	//Checked
    while($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}
    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    if (trim($qidattributes['answer_width'])!='')
    {
        $answerwidth=$qidattributes['answer_width'];
    }
    else
    {
        $answerwidth = 20;
    }
    $cellwidth  = 3; // number of columns
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        ++$cellwidth; // add another column
    }
    $cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

    if ($qidattributes['random_order']==1) {
        $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
    }
    else
    {
        $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
    }
    $ansresult = db_execute_assoc($ansquery);	//Checked
    $anscount = $ansresult->RecordCount();
    $fn = 1;
    $answer = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - a Yes/No/uncertain Likert scale array\">\n"
    . "\t<colgroup class=\"col-responses\">\n"
    . "\n\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";
    $odd_even = '';
    for ($xc=1; $xc<=3; $xc++)
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
    }
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
    }
    $answer .= "\t</colgroup>\n\n"
    . "\t<thead>\n<tr class=\"array1\">\n"
    . "\t<td>&nbsp;</td>\n"
    . "\t<th>".$clang->gT('Yes')."</th>\n"
    . "\t<th>".$clang->gT('Uncertain')."</th>\n"
    . "\t<th>".$clang->gT('No')."</th>\n";
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $answer .= "\t<th>".$clang->gT('No answer')."</th>\n";
    }
    $answer .= "</tr>\n\t</thead>";
    $answer_t_content = '';
    if ($anscount==0)
    {
        $inputnames=array();
        $answer.="<tr>\t<th class=\"answertext\">".$clang->gT('Error: This question has no answers.')."</th>\n</tr>\n";
    }
    else
    {
        $trbc = '';
        while ($ansrow = $ansresult->FetchRow())
        {
            $myfname = $ia[1].$ansrow['title'];
            $answertext=dTexts::run($ansrow['question']);
            /* Check if this item has not been answered: the 'notanswered' variable must be an array,
             containing a list of unanswered questions, the current question must be in the array,
             and there must be no answer available for the item in this session. */
            if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && ($_SESSION[$myfname] == '') ) {
                $answertext = "<span class='errormandatory'>{$answertext}</span>";
            }
            $trbc = alternation($trbc , 'row');

            // Get array_filter stuff
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname);

            $answer_t_content .= $htmltbody2;

            $answer_t_content .= "<tr class=\"$trbc\">\n"
            . "\t<th class=\"answertext\">\n"
            . $hiddenfield
            . "\t\t\t\t$answertext</th>\n"
            . "\t<td class=\"answer_cell_Y\">\n<label for=\"answer$myfname-Y\">\n"
            . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-Y\" value=\"Y\" title=\"".$clang->gT('Yes').'"';
            if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == 'Y')
            {
                $answer_t_content .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n\t</label>\n\t</td>\n"
            . "\t<td class=\"answer_cell_U\">\n<label for=\"answer$myfname-U\">\n"
            . "<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-U\" value=\"U\" title=\"".$clang->gT('Uncertain')."\"";
            // --> END NEW FEATURE - SAVE

            if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == 'U')
            {
                $answer_t_content .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n"
            . "\t<td class=\"answer_cell_N\">\n<label for=\"answer$myfname-N\">\n"
            . "<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-N\" value=\"N\" title=\"".$clang->gT('No').'"';
            // --> END NEW FEATURE - SAVE

            if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == 'N')
            {
                $answer_t_content .= CHECKED;
            }
            // --> START NEW FEATURE - SAVE
            $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n"
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            // --> END NEW FEATURE - SAVE
            if (isset($_SESSION[$myfname]))
            {
                $answer_t_content .= $_SESSION[$myfname];
            }
            $answer_t_content .= "\" />\n\t</td>\n";

            if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
            {
                $answer_t_content .= "\t<td>\n\t<label for=\"answer$myfname-\">\n"
                . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer')."\"";
                if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == '')
                {
                    $answer_t_content .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer_t_content .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n";
                // --> END NEW FEATURE - SAVE
            }
            $answer_t_content .= "</tr>\n</tbody>";
            $inputnames[]=$myfname;
            $fn++;
        }
    }
    $answer .=  $answer_t_content . "\t\n</table>\n";
    return array($answer, $inputnames);
}

function do_array_increasesamedecrease($ia)
{
    global $dbprefix, $thissurvey, $clang;
    global $notanswered;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
    $qresult = db_execute_assoc($qquery);   //Checked
    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    if (trim($qidattributes['answer_width'])!='')
    {
        $answerwidth=$qidattributes['answer_width'];
    }
    else
    {
        $answerwidth = 20;
    }
    $cellwidth  = 3; // number of columns
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        ++$cellwidth; // add another column
    }
    $cellwidth = round((( 100 - $answerwidth ) / $cellwidth) , 1); // convert number of columns to percentage of table width

    while($qrow = $qresult->FetchRow())
    {
        $other = $qrow['other'];
    }
    if ($qidattributes['random_order']==1) {
        $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
    }
    else
    {
        $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
    }
    $ansresult = db_execute_assoc($ansquery);  //Checked
    $anscount = $ansresult->RecordCount();

    $fn = 1;

    $answer = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - Increase/Same/Decrease Likert scale array\">\n"
    . "\t<colgroup class=\"col-responses\">\n"
    . "\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";

    $odd_even = '';
    for ($xc=1; $xc<=3; $xc++)
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
    }
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $odd_even = alternation($odd_even);
        $answer .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
    }
    $answer .= "\t</colgroup>\n"
    . "\t<thead>\n"
    . "<tr>\n"
    . "\t<td>&nbsp;</td>\n"
    . "\t<th>".$clang->gT('Increase')."</th>\n"
    . "\t<th>".$clang->gT('Same')."</th>\n"
    . "\t<th>".$clang->gT('Decrease')."</th>\n";
    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
    {
        $answer .= "\t<th>".$clang->gT('No answer')."</th>\n";
    }
    $answer .= "</tr>\n"
    ."\t</thead>\n";
    $answer_body = '';
    $trbc = '';
    while ($ansrow = $ansresult->FetchRow())
    {
        $myfname = $ia[1].$ansrow['title'];
        $answertext=dTexts::run($ansrow['question']);
        /* Check if this item has not been answered: the 'notanswered' variable must be an array,
         containing a list of unanswered questions, the current question must be in the array,
         and there must be no answer available for the item in this session. */
        if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && ($_SESSION[$myfname] == "") )
        {
            $answertext = "<span class=\"errormandatory\">{$answertext}</span>";
        }

        $trbc = alternation($trbc , 'row');

        // Get array_filter stuff
        list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname);

        $answer_body .= $htmltbody2;

        $answer_body .= "<tr class=\"$trbc\">\n"
        . "\t<th class=\"answertext\">\n"
        . "$answertext\n"
        . $hiddenfield
        . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
        if (isset($_SESSION[$myfname]))
        {
            $answer_body .= $_SESSION[$myfname];
        }
        $answer_body .= "\" />\n\t</th>\n";

        $answer_body .= "\t<td class=\"answer_cell_I\">\n"
        . "<label for=\"answer$myfname-I\">\n"
        ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-I\" value=\"I\" title=\"".$clang->gT('Increase').'"';
        if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == 'I')
        {
            $answer_body .= CHECKED;
        }

        $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
        . "</label>\n"
        . "\t</td>\n"
        . "\t<td class=\"answer_cell_S\">\n"
        . "<label for=\"answer$myfname-S\">\n"
        . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-S\" value=\"S\" title=\"".$clang->gT('Same').'"';

        if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == 'S')
        {
            $answer_body .= CHECKED;
        }

        $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
        . "</label>\n"
        . "\t</td>\n"
        . "\t<td class=\"answer_cell_D\">\n"
        . "<label for=\"answer$myfname-D\">\n"
        . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-D\" value=\"D\" title=\"".$clang->gT('Decrease').'"';
        // --> END NEW FEATURE - SAVE
        if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == 'D')
        {
            $answer_body .= CHECKED;
        }

        $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
        . "</label>\n"
        . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";

        if (isset($_SESSION[$myfname])) {$answer_body .= $_SESSION[$myfname];}
        $answer_body .= "\" />\n\t</td>\n";

        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            $answer_body .= "\t<td>\n"
            . "<label for=\"answer$myfname-\">\n"
            . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" id=\"answer$myfname-\" value=\"\" title=\"".$clang->gT('No answer').'"';
            if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == '')
            {
                $answer_body .= CHECKED;
            }
            $answer_body .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
            . "</label>\n"
            . "\t</td>\n";
        }
        $answer_body .= "</tr>\n\t</tbody>";
        $inputnames[]=$myfname;
        $fn++;
    }
    $answer .=  $answer_body . "\t\n</table>\n";
    return array($answer, $inputnames);
}

// ---------------------------------------------------------------
function do_array($ia)
{
    global $dbprefix, $connect, $thissurvey, $clang;
    global $repeatheadings;
    global $notanswered;
    global $minrepeatheadings;

    if (isset($ia[8]) && $ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $qquery = "SELECT other FROM {$dbprefix}questions WHERE qid={$ia[0]} AND language='".$_SESSION['s_lang']."'";
    $qresult = db_execute_assoc($qquery);     //Checked
    while($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}
    $lquery = "SELECT * FROM {$dbprefix}answers WHERE qid={$ia[0]} AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY sortorder, code";

    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    if (trim($qidattributes['answer_width'])!='')
    {
        $answerwidth=$qidattributes['answer_width'];
    }
    else
    {
        $answerwidth=20;
    }
    $columnswidth=100-$answerwidth;

   if ($qidattributes['use_dropdown'] == 1)
   {
       $useDropdownLayout = true;
   }
   else
   {
       $useDropdownLayout = false;
   }

    $lresult = db_execute_assoc($lquery);   //Checked
    if ($useDropdownLayout === false && $lresult->RecordCount() > 0)
    {
        while ($lrow=$lresult->FetchRow())
        {
            $labelans[]=$lrow['answer'];
            $labelcode[]=$lrow['code'];
        }

        //		$cellwidth=sprintf('%02d', $cellwidth);

        $ansquery = "SELECT question FROM {$dbprefix}questions WHERE parent_qid={$ia[0]} AND question like '%|%' ";
        $ansresult = db_execute_assoc($ansquery);  //Checked
        if ($ansresult->RecordCount()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;}
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($qidattributes['random_order']==1) {
            $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid={$ia[0]} AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
        }
        else
        {
            $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid={$ia[0]} AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
        }
        $ansresult = db_execute_assoc($ansquery); //Checked
        $anscount = $ansresult->RecordCount();
        $fn=1;

        $numrows = count($labelans);
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            ++$numrows;
        }
        if ($right_exists)
        {
            ++$numrows;
        }
        $cellwidth = round( ($columnswidth / $numrows ) , 1 );

        $answer_start = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an array type question\" >\n";
        $answer_head = "\t<thead>\n"
        . "<tr>\n"
        . "\t<td>&nbsp;</td>\n";
        foreach ($labelans as $ld)
        {
            $answer_head .= "\t<th>".$ld."</th>\n";
        }
        if ($right_exists) {$answer_head .= "\t<td>&nbsp;</td>\n";}
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory and we can show "no answer"
        {
            $answer_head .= "\t<th>".$clang->gT('No answer')."</th>\n";
        }
        $answer_head .= "</tr>\n\t</thead>\n\n\t\n";

        $answer = '';
        $trbc = '';
        $inputnames=array();

        while ($ansrow = $ansresult->FetchRow())
        {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
            {
                if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                {
                    $answer .= "<tr class=\"repeat headings\">\n"
                    . "\t<td>&nbsp;</td>\n";
                    foreach ($labelans as $ld)
                    {
                        $answer .= "\t<th>".$ld."</th>\n";
                    }
                    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory and we can show "no answer"
                    {
                        $answer .= "\t<th>".$clang->gT('No answer')."</th>\n";
                    }
                    $answer .= "</tr>\n";
                }
            }
            $myfname = $ia[1].$ansrow['title'];
            $answertext=dTexts::run($ansrow['question']);
            $answertextsave=$answertext;
            if (strpos($answertext,'|'))
            {
                $answertext=substr($answertext,0, strpos($answertext,'|'));
            }
            /* Check if this item has not been answered: the 'notanswered' variable must be an array,
             containing a list of unanswered questions, the current question must be in the array,
             and there must be no answer available for the item in this session. */

            if (strpos($answertext,'|')) {$answerwidth=$answerwidth/2;}

            if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && ($_SESSION[$myfname] == '') ) {
                $answertext = '<span class="errormandatory">'.$answertext.'</span>';
            }
            // Get array_filter stuff
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname);
            $row_selected = return_array_filter_selected($ia, $qidattributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname);
			if($row_selected)
			{
				$trbc = alternation($trbc , 'row');
				$fn++;
			}
			$answer .= $htmltbody2;

            $answer .= "<tr class=\"$trbc\">\n"
            . "\t<th class=\"answertext\">\n$answertext"
            . $hiddenfield
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            if (isset($_SESSION[$myfname]))
            {
                $answer .= $_SESSION[$myfname];
            }
            $answer .= "\" />\n\t</th>\n";

            $thiskey=0;
            foreach ($labelcode as $ld)
            {
                $answer .= "\t\t\t<td class=\"answer_cell_00$ld\">\n"
                . "<label for=\"answer$myfname-$ld\">\n"
                . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"$ld\" id=\"answer$myfname-$ld\" title=\""
                . html_escape(strip_tags($labelans[$thiskey])).'"';
                if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $ld)
                {
                    $answer .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n"
                . "</label>\n"
                . "\t</td>\n";
                // --> END NEW FEATURE - SAVE

                $thiskey++;
            }
            if (strpos($answertextsave,'|'))
            {
                $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                $answer .= "\t<th class=\"answertextright\">$answertext</th>\n";
            }
            elseif ($right_exists)
            {
                $answer .= "\t<td class=\"answertextright\">&nbsp;</td>\n";
            }

            if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
            {
                $answer .= "\t<td>\n<label for=\"answer$myfname-\">\n"
                ."\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"\" id=\"answer$myfname-\" title=\"".$clang->gT('No answer').'"';
                if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == '')
                {
                    $answer .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\"  />\n</label>\n\t</td>\n";
                // --> END NEW FEATURE - SAVE
            }

            $answer .= "</tr>\n";
            $inputnames[]=$myfname;
            //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
        }

        $answer_cols = "\t<colgroup class=\"col-responses\">\n"
        ."\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n" ;

        $odd_even = '';
        foreach ($labelans as $c)
        {
            $odd_even = alternation($odd_even);
            $answer_cols .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        if ($right_exists)
        {
            $odd_even = alternation($odd_even);
            $answer_cols .= "<col class=\"answertextright $odd_even\" width=\"$answerwidth%\" />\n";
        }
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory
        {
            $odd_even = alternation($odd_even);
            $answer_cols .= "<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
        }
        $answer_cols .= "\t</colgroup>\n";

        $answer = $answer_start . $answer_cols . $answer_head .$answer . "\t</tbody>\n</table>\n";
    }
   elseif ($useDropdownLayout === true && $lresult->RecordCount() > 0)
   {
       while ($lrow=$lresult->FetchRow())
           $labels[]=Array('code' => $lrow['code'],
                           'answer' => $lrow['answer']);
        $ansquery = "SELECT question FROM {$dbprefix}questions WHERE parent_qid={$ia[0]} AND question like '%|%' ";
       $ansresult = db_execute_assoc($ansquery);  //Checked
       if ($ansresult->RecordCount()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;}
       // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($qidattributes['random_order']==1) {
            $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid={$ia[0]} AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
        }
        else
        {
            $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid={$ia[0]} AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
        }
       $ansresult = db_execute_assoc($ansquery); //Checked
       $anscount = $ansresult->RecordCount();
       $fn=1;

       $numrows = count($labels);
       if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
       {
           ++$numrows;
       }
       if ($right_exists)
       {
           ++$numrows;
       }
       $cellwidth = round( ($columnswidth / $numrows ) , 1 );

       $answer_start = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an array type question\" >\n";

       $answer = "\t<tbody>\n";
       $trbc = '';
        $inputnames=array();

       while ($ansrow = $ansresult->FetchRow())
       {
           $myfname = $ia[1].$ansrow['title'];
           $trbc = alternation($trbc , 'row');
           $answertext=answer_replace($ansrow['question']);
            $answertextsave=$answertext;
           if (strpos($answertext,'|'))
           {
               $answertext=substr($answertext,0, strpos($answertext,'|'));
           }
           /* Check if this item has not been answered: the 'notanswered' variable must be an array,
           containing a list of unanswered questions, the current question must be in the array,
           and there must be no answer available for the item in this session. */

           if (strpos($answertext,'|')) {$answerwidth=$answerwidth/2;}

           if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && ($_SESSION[$myfname] == '') ) {
               $answertext = '<span class="errormandatory">'.$answertext.'</span>';
           }
           // Get array_filter stuff
           list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname);
           $answer .= $htmltbody2;

           $answer .= "<tr class=\"$trbc\">\n"
           . "\t<th class=\"answertext\">\n$answertext"
           . $hiddenfield
           . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
           if (isset($_SESSION[$myfname]))
           {
               $answer .= $_SESSION[$myfname];
           }
           $answer .= "\" />\n\t</th>\n";

           $answer .= "\t<td >\n"
           . "<select name=\"$myfname\" id=\"answer$myfname\" onchange=\"$checkconditionFunction(this.value, this.name, this.type);\">\n";

           if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] =='')
           {
               $answer .= "\t<option value=\"\" ".SELECTED.'>'.$clang->gT('Please choose')."...</option>\n";
           }

           foreach ($labels as $lrow)
           {
               $answer .= "\t<option value=\"".$lrow['code'].'" ';
               if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $lrow['code'])
               {
                   $answer .= SELECTED;
               }
               $answer .= '>'.$lrow['answer']."</option>\n";
           }
           // If not mandatory and showanswer, show no ans
           if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
           {
               $answer .= "\t<option value=\"\" ";
               if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == '')
               {
                   $answer .= SELECTED;
               }
               $answer .= '>'.$clang->gT('No answer')."</option>\n";
           }
           $answer .= "</select>\n";

           if (strpos($answertextsave,'|'))
           {
               $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
               $answer .= "\t<th class=\"answertextright\">$answertext</th>\n";
           }
           elseif ($right_exists)
           {
               $answer .= "\t<td class=\"answertextright\">&nbsp;</td>\n";
           }

           $answer .= "</tr>\n</tbody>";
           $inputnames[]=$myfname;
           //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
           $fn++;
       }

       $answer = $answer_start . $answer . "\t</tbody>\n</table>\n";
   }
   else
    {
        $answer = "\n<p class=\"error\">".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        $inputnames='';
    }
    return array($answer, $inputnames);
}




// ---------------------------------------------------------------
function do_array_multitext($ia)
{
    global $dbprefix, $connect, $thissurvey, $clang;
    global $repeatheadings;
    global $notanswered;
    global $minrepeatheadings;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    //echo "<pre>"; print_r($_POST); echo "</pre>";
    $defaultvaluescript = "";
    $qquery = "SELECT other FROM {$dbprefix}questions WHERE qid={$ia[0]} AND language='".$_SESSION['s_lang']."'";
    $qresult = db_execute_assoc($qquery);
    while($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}

    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);

    $show_grand = $qidattributes['show_grand_total'];
    $totals_class = '';
    $num_class = '';
    $show_totals = '';
    $col_total = '';
    $row_total = '';
    $total_col = '';
    $col_head = '';
    $row_head = '';
    $grand_total = '';
    $q_table_id = '';
    $q_table_id_HTML = '';
    $numbersonly = '';

    if ($qidattributes['numbers_only']==1)
    {
        $q_table_id = 'totals_'.$ia[0];
	$q_table_id_HTML = ' id="'.$q_table_id.'"';
//	$numbersonly = 'onkeypress="return goodchars(event,\'-0123456789.\')"';
        $num_class = ' numbers-only';
	switch ($qidattributes['show_totals'])
	{
	    case 'R':
	        $totals_class = $show_totals = 'row';
		$row_total = '			<td class="total">
 				<label>
 					<input name="[[ROW_NAME]]_total" title="[[ROW_NAME]] total" size="[[INPUT_WIDTH]]" value="" type="text" disabled="disabled" class="disabled" />
 				</label>
 			</td>';
 		$col_head = '			<th class="total">Total</th>';
 		if($show_grand == true)
 		{
 			$row_head = '
 			<th class="answertext total">Grand total</th>';
 			$col_total = '
 			<td>&nbsp;</td>';
 			$grand_total = '
 			<td class="total grand">
 				<input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
 			</td>';
 		};
 		break;
	    case 'C':
	        $totals_class = $show_totals = 'col';
		$col_total = '
 			<td>
 				<input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
 			</td>';
 		$row_head = '
 			<th class="answertext total">Total</th>';
 		if($show_grand == true)
 		{
 		    $row_total = '
 			<td class="total">&nbsp;</td>';
 		    $col_head = '			<th class="total">Grand Total</th>';
		    $grand_total = '
 			<td class="total grand">
 				<input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
 			</td>';
 		};
 		break;
 	    case 'B':
	        $totals_class = $show_totals = 'both';
		$row_total = '			<td class="total">
 				<label>
 					<input name="[[ROW_NAME]]_total" title="[[ROW_NAME]] total" size="[[INPUT_WIDTH]]" value="" type="text" disabled="disabled" class="disabled" />
 				</label>
 			</td>';
 		$col_total = '
 			<td>
 				<input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled" class="disabled" />
 			</td>';
 		$col_head = '			<th class="total">Total</th>';
		$row_head = '
 			<th class="answertext">Total</th>';
 		if($show_grand == true)
 		{
 		    $grand_total = '
 			<td class="total grand">
 				<input type="text" size="[[INPUT_WIDTH]]" value="" disabled="disabled"/>
 			</td>';
 		}
 		else
 		{
 		    $grand_total = '
 			<td>&nbsp;</td>';
 		};
 		break;
 	};
 	if(!empty($totals_class))
 	{
 	    $totals_class = ' show-totals '.$totals_class;
	    if($qidattributes['show_grand_total'])
	    {
	        $totals_class .= ' grand';
		$show_grand = true;
	    };
	};
    }
    else
    {
        $numbersonly = '';
    };
    if (trim($qidattributes['answer_width'])!='')
    {
        $answerwidth=$qidattributes['answer_width'];
    }
    else
    {
        $answerwidth=20;
    };
    if (trim($qidattributes['text_input_width'])!='')
    {
        $inputwidth=$qidattributes['text_input_width'];
    }
    else
    {
        $inputwidth = 20;
    }
    $columnswidth=100-($answerwidth*2);

    $lquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid={$ia[0]}  AND language='".$_SESSION['s_lang']."' and scale_id=1 ORDER BY question_order";
    $lresult = db_execute_assoc($lquery);
    if ($lresult->RecordCount() > 0)
    {
        while ($lrow=$lresult->FetchRow())
        {
            $labelans[]=$lrow['question'];
            $labelcode[]=$lrow['title'];
        }
        $numrows=count($labelans);
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {$numrows++;}
	if( ($show_grand == true &&  $show_totals == 'col' ) || $show_totals == 'row' ||  $show_totals == 'both' )
	{
	    ++$numrows;
	};
        $cellwidth=$columnswidth/$numrows;

        $cellwidth=sprintf('%02d', $cellwidth);

        $ansquery = "SELECT count(question) FROM {$dbprefix}questions WHERE parent_qid={$ia[0]} and scale_id=0 AND question like '%|%'";
        $ansresult = $connect->GetOne($ansquery);
        if ($ansresult>0)
        {
            $right_exists=true;
            $answerwidth=$answerwidth/2;
        }
        else
        {
            $right_exists=false;
        }
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($qidattributes['random_order']==1) {
            $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] and scale_id=0 AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
        }
        else
        {
            $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] and scale_id=0 AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
        }
        $ansresult = db_execute_assoc($ansquery);
        $anscount = $ansresult->RecordCount();
        $fn=1;

        $answer_cols = "\t<colgroup class=\"col-responses\">\n"
        ."\n\t\t<col class=\"answertext\" width=\"$answerwidth%\" />\n";

        $answer_head = "\n\t<thead>\n"
        . "\t\t<tr>\n"
        . "\t\t\t<td width='$answerwidth%'>&nbsp;</td>\n";

        $odd_even = '';
        foreach ($labelans as $ld)
        {
            $answer_head .= "\t<th>".$ld."</th>\n";
            $odd_even = alternation($odd_even);
            $answer_cols .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        if ($right_exists)
        {
            $answer_head .= "\t<td>&nbsp;</td>\n";// class=\"answertextright\"
            $odd_even = alternation($odd_even);
            $answer_cols .= "<col class=\"answertextright $odd_even\" width=\"$cellwidth%\" />\n";
        }

	if( ($show_grand == true &&  $show_totals == 'col' ) || $show_totals == 'row' ||  $show_totals == 'both' )
	{
	    $answer_head .= $col_head;
	    $odd_even = alternation($odd_even);
	    $answer_cols .= "\t\t<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
	};
        $answer_cols .= "\t</colgroup>\n";

        $answer_head .= "</tr>\n"
        . "\t</thead>\n";

	$answer = "\n<table$q_table_id_HTML class=\"question$num_class"."$totals_class\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an array of text responses\">\n" . $answer_cols . $answer_head;

        $trbc = '';
        while ($ansrow = $ansresult->FetchRow())
        {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
            {
                if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                {
                    $trbc = alternation($trbc , 'row');
                    $answer .= "<tbody>\n<tr class=\"$trbc repeat\">\n"
                    . "\t<td>&nbsp;</td>\n";
                    foreach ($labelans as $ld)
                    {
                        $answer .= "\t<th>".$ld."</th>\n";
                    }
                    $answer .= "</tr>\n</tbody>\n";
                }
            }
            $myfname = $ia[1].$ansrow['title'];
            $answertext=dTexts::run($ansrow['question']);
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

            // Get array_filter stuff
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname);

            $answer .= $htmltbody2;

            if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}
            $trbc = alternation($trbc , 'row');
            $answer .= "\t\t<tr class=\"$trbc\" id=\"$myfname\">\n"
            . "\t\t\t<th class=\"answertext\">\n"
            . "\t\t\t\t".$hiddenfield
            . "$answertext\n"
            . "\t\t\t\t<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            if (isset($_SESSION[$myfname])) {$answer .= $_SESSION[$myfname];}
            $answer .= "\" />\n\t\t\t</th>\n";
            $thiskey=0;
            foreach ($labelcode as $ld)
            {

                $myfname2=$myfname."_$ld";
                $myfname2value = isset($_SESSION[$myfname2]) ? $_SESSION[$myfname2] : "";
                $answer .= "\t<td class=\"answer_cell_00$ld\">\n"
                . "\t\t\t\t<label for=\"answer{$myfname2}\">\n"
                . "\t\t\t\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" />\n"
                . "\t\t\t\t<input type=\"text\" name=\"$myfname2\" id=\"answer{$myfname2}\" title=\""
                . FlattenText($labelans[$thiskey]).'" '
                . 'size="'.$inputwidth.'" '
                . ' value="'.str_replace ('"', "'", str_replace('\\', '', $myfname2value))."\" />\n";
                $inputnames[]=$myfname2;
                $answer .= "\t\t\t\t</label>\n\t\t\t</td>\n";
                $thiskey += 1;
            }
            if (strpos($answertextsave,'|'))
            {
                $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                $answer .= "\t\t\t<td class=\"answertextright\" style=\"text-align:left;\" width=\"$answerwidth%\">$answertext</td>\n";
            }
            elseif ($right_exists)
            {
                $answer .= "\t\t\t<td class=\"answertextright\" style='text-align:left;' width='$answerwidth%'>&nbsp;</td>\n";
            }

            $answer .= str_replace(array('[[ROW_NAME]]','[[INPUT_WIDTH]]') , array(strip_tags($answertext),$inputwidth) , $row_total);
	    $answer .= "\n\t\t</tr>\n";
            $answer .= "</tbody>\n";
            //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
            $fn++;
        }
	if($show_totals == 'col' || $show_totals = 'both' || $grand_total == true)
	{
            $answer .= "\t\t<tr class=\"total\">$row_head";
	    for( $a = 0; $a < count($labelcode) ; ++$a )
	    {
	        $answer .= str_replace(array('[[ROW_NAME]]','[[INPUT_WIDTH]]') , array(strip_tags($answertext),$inputwidth) , $col_total);
	    };
	    $answer .= str_replace(array('[[ROW_NAME]]','[[INPUT_WIDTH]]') , array(strip_tags($answertext),$inputwidth) , $grand_total)."\n\t\t</tr>\n";
        };
        $answer .= "\t</tbody>\n</table>\n";
	if(!empty($q_table_id))
	{
            $answer .= "\n<script type=\"text/javascript\">new multi_set('$q_table_id');</script>\n";
	};
    }
    else
    {
        $answer = "\n<p class=\"error\">".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        $inputnames='';
    }
    return array($answer, $inputnames);
}


// ---------------------------------------------------------------
function do_array_multiflexi($ia)
{
    global $dbprefix, $connect, $thissurvey, $clang;
    global $repeatheadings;
    global $notanswered;
    global $minrepeatheadings;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    //echo '<pre>'; print_r($_POST); echo '</pre>';
    $defaultvaluescript = '';
    $qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."' and parent_qid=0";
    $qresult = db_execute_assoc($qquery);
    while($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}

    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    if (trim($qidattributes['multiflexible_max'])!='' && trim($qidattributes['multiflexible_min']) ==''){
        $maxvalue=$qidattributes['multiflexible_max'];
        if(isset($minvalue['value']) && $minvalue['value'] == 0) {$minvalue = 0;} else {$minvalue=1;}
    }
    if (trim($qidattributes['multiflexible_min'])!='' && trim($qidattributes['multiflexible_max']) ==''){
        $minvalue=$qidattributes['multiflexible_min'];
        $maxvalue=$qidattributes['multiflexible_min'] + 10;
    }
    if (trim($qidattributes['multiflexible_min'])=='' && trim($qidattributes['multiflexible_max']) ==''){
        if(isset($minvalue['value']) && $minvalue['value'] == 0) {$minvalue = 0;} else {$minvalue=1;}
        $maxvalue=10;
    }
    if (trim($qidattributes['multiflexible_min']) !='' && trim($qidattributes['multiflexible_max']) !=''){
        if($qidattributes['multiflexible_min'] < $qidattributes['multiflexible_max']){
            $minvalue=$qidattributes['multiflexible_min'];
            $maxvalue=$qidattributes['multiflexible_max'];
        }
    }

    if (trim($qidattributes['multiflexible_step'])!='' && $qidattributes['multiflexible_step'] > 0)
    {
        $stepvalue=$qidattributes['multiflexible_step'];
    }
    else
    {
        $stepvalue=1;
    }

    if($qidattributes['reverse']==1)
    {
        $tmp = $minvalue;
        $minvalue = $maxvalue;
        $maxvalue = $tmp;
        $reverse=true;
        $stepvalue=-$stepvalue;
    }
    else
    {
        $reverse=false;
    }

    $checkboxlayout=false;
    if ($qidattributes['multiflexible_checkbox']!=0)
    {
        $minvalue=0;
        $maxvalue=1;
        $checkboxlayout=true;
    }

    $inputboxlayout=false;
    if ($qidattributes['input_boxes']!=0)
    {
        $inputboxlayout=true;
    }

    if (trim($qidattributes['maximum_chars'])!='')
    {
        $maxsize=$qidattributes['maximum_chars'];
    }
    else
    {
        $maxsize=255;
    }

    if ($thissurvey['nokeyboard']=='Y')
    {
        vIncludeKeypad();
        $kpclass = "num-keypad";
    }
    else
    {
        $kpclass = "";
    }

    if (trim($qidattributes['answer_width'])!='')
    {
        $answerwidth=$qidattributes['answer_width'];
    }
    else
    {
        $answerwidth=20;
    }
    $columnswidth=100-($answerwidth*2);

    $lquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid={$ia[0]}  AND language='".$_SESSION['s_lang']."' and scale_id=1 ORDER BY question_order";
    $lresult = db_execute_assoc($lquery);
    if ($lresult->RecordCount() > 0)
    {
        while ($lrow=$lresult->FetchRow())
        {
            $labelans[]=$lrow['question'];
            $labelcode[]=$lrow['title'];
        }
        $numrows=count($labelans);
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) {$numrows++;}
        $cellwidth=$columnswidth/$numrows;

        $cellwidth=sprintf('%02d', $cellwidth);

        $ansquery = "SELECT question FROM {$dbprefix}questions WHERE parent_qid=".$ia[0]." AND scale_id=0 AND question like '%|%'";
        $ansresult = db_execute_assoc($ansquery);
        if ($ansresult->RecordCount()>0) {$right_exists=true;$answerwidth=$answerwidth/2;} else {$right_exists=false;}
        // $right_exists is a flag to find out if there are any right hand answer parts. If there arent we can leave out the right td column
        if ($qidattributes['random_order']==1) {
            $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] AND scale_id=0 AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
        }
        else
        {
            $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] AND scale_id=0 AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
        }
        $ansresult = db_execute_assoc($ansquery);
        $anscount = $ansresult->RecordCount();
        $fn=1;

        $mycols = "\t<colgroup class=\"col-responses\">\n"
        . "\n\t<col class=\"answertext\" width=\"$answerwidth%\" />\n";

        $myheader = "\n\t<thead>\n"
        . "<tr>\n"
        . "\t<td >&nbsp;</td>\n";

        $odd_even = '';
        foreach ($labelans as $ld)
        {
            $myheader .= "\t<th>".$ld."</th>\n";
            $odd_even = alternation($odd_even);
            $mycols .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        if ($right_exists)
        {
            $myheader .= "\t<td>&nbsp;</td>";
            $odd_even = alternation($odd_even);
            $mycols .= "<col class=\"answertextright $odd_even\" width=\"$answerwidth%\" />\n";
        }
        $myheader .= "</tr>\n"
        . "\t</thead>\n";
        $mycols .= "\t</colgroup>\n";

        $trbc = '';
        $answer = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an array type question with dropdown responses\">\n" . $mycols . $myheader . "\n";

        while ($ansrow = $ansresult->FetchRow())
        {
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
            {
                if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                {
                    $trbc = alternation($trbc , 'row');
                    $answer .= "<tbody>\n<tr class=\"$trbc repeat\">\n"
                    . "\t<td>&nbsp;</td>\n";
                    foreach ($labelans as $ld)
                    {
                        $answer .= "\t<th>".$ld."</th>\n";
                    }
                    $answer .= "</tr>\n</tbody>\n";
                }
            }
            $myfname = $ia[1].$ansrow['title'];
            $answertext=dTexts::run($ansrow['question']);
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

            // Get array_filter stuff
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, $ansrow, $myfname, $trbc, $myfname);

            $answer .= $htmltbody2;

            if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}

            $trbc = alternation($trbc , 'row');

            $answer .= "<tr class=\"$trbc\">\n"
            . "\t<th class=\"answertext\" width=\"$answerwidth%\">\n"
            . "$answertext\n"
            . $hiddenfield
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            if (isset($_SESSION[$myfname]))
            {
                $answer .= $_SESSION[$myfname];
            }
            $answer .= "\" />\n\t</th>\n";
            $first_hidden_field = '';
            $thiskey=0;
            foreach ($labelcode as $ld)
            {

                if ($checkboxlayout == false)
                {
                    $myfname2=$myfname."_$ld";
                    if(isset($_SESSION[$myfname2]))
                    {
                        $myfname2_java_value = " value=\"$_SESSION[$myfname2]\" ";
                    }
                    else
                    {
                        $myfname2_java_value = "";
                    }
                    $answer .= "\t<td class=\"answer_cell_00$ld\">\n"
                    . "<label for=\"answer{$myfname2}\">\n"
                    . "\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" $myfname2_java_value />\n";

                    if($inputboxlayout == false) {
                        $answer .= "\t<select class=\"multiflexiselect\" name=\"$myfname2\" id=\"answer{$myfname2}\" title=\""
                        . html_escape($labelans[$thiskey]).'"'
                        . " onchange=\"$checkconditionFunction(this.value, this.name, this.type)\">\n"
                        . "<option value=\"\">".$clang->gT('...')."</option>\n";

                        for($ii=$minvalue; ($reverse? $ii>=$maxvalue:$ii<=$maxvalue); $ii+=$stepvalue) {
                            $answer .= "<option value=\"$ii\"";
                            if(isset($_SESSION[$myfname2]) && $_SESSION[$myfname2] == $ii) {
                                $answer .= SELECTED;
                            }
                            $answer .= ">$ii</option>\n";
                        }
                        $answer .= "\t</select>\n";
                    } elseif ($inputboxlayout == true)
                    {
                        $sSeperator = getRadixPointData($thissurvey['surveyls_numberformat']);
                        $sSeperator = $sSeperator['seperator'];
                        $answer .= "\t<input type='text' class=\"multiflexitext $kpclass\" name=\"$myfname2\" id=\"answer{$myfname2}\" maxlength=\"{$maxsize}\" size=5 title=\""
                        . html_escape($labelans[$thiskey]).'"'
                        . " onchange=\"$checkconditionFunction(this.value, this.name, this.type)\" onkeypress=\"return goodchars(event,'0123456789$sSeperator')\""
                        . " value=\"";
                        if(isset($_SESSION[$myfname2]) && $_SESSION[$myfname2]) {
                            $answer .= $_SESSION[$myfname2];
                        }
                        $answer .= "\" />\n";
                    }
                    $answer .= "</label>\n"
                    . "\t</td>\n";

                    $inputnames[]=$myfname2;
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
                    $answer .= "\t<td class=\"answer_cell_00$ld\">\n"
                    //					. "<label for=\"answer{$myfname2}\">\n"
                    . "\t<input type=\"hidden\" name=\"java{$myfname2}\" id=\"java{$myfname2}\" value=\"$myvalue\"/>\n"
                    . "\t<input type=\"hidden\" name=\"$myfname2\" id=\"answer{$myfname2}\" value=\"$myvalue\" />\n";
                    $answer .= "\t<input type=\"checkbox\" name=\"cbox_$myfname2\" id=\"cbox_$myfname2\" $setmyvalue "
                    . " onclick=\"cancelBubbleThis(event); "
                    . " aelt=document.getElementById('answer{$myfname2}');"
                    . " jelt=document.getElementById('java{$myfname2}');"
                    . " if(this.checked) {"
                    . "  aelt.value=1;jelt.value=1;$checkconditionFunction(1,'answer{$myfname2}',aelt.type);"
                    . " } else {"
                    . "  aelt.value=0;jelt.value=0;$checkconditionFunction(0,'answer{$myfname2}',aelt.type);"
                    . " }; return true;\" "
                    //					. " onchange=\"checkconditions(this.value, this.name, this.type)\" "
                    . " />\n";
                    $inputnames[]=$myfname2;
                    //					$answer .= "</label>\n"
                    $answer .= ""
                    . "\t</td>\n";
                    $thiskey++;
                }
            }
            if (strpos($answertextsave,'|'))
            {
                $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                $answer .= "\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">$answertext</td>\n";
            }
            elseif ($right_exists)
            {
                $answer .= "\t<td class=\"answertextright\" style='text-align:left;' width=\"$answerwidth%\">&nbsp;</td>\n";
            }

            $answer .= "</tr>\n\t</tbody>";
            //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
            $fn++;
        }
        $answer .= "\n</table>\n";
    }
    else
    {
        $answer = "\n<p class=\"error\">".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        $inputnames = '';
    }
    return array($answer, $inputnames);
}


// ---------------------------------------------------------------
function do_arraycolumns($ia)
{
    global $dbprefix;
    global $notanswered, $clang;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);
    $qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
    $qresult = db_execute_assoc($qquery);    //Checked
    while($qrow = $qresult->FetchRow()) {$other = $qrow['other'];}
    $lquery = "SELECT * FROM {$dbprefix}answers WHERE qid=".$ia[0]."  AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY sortorder, code";
    $lresult = db_execute_assoc($lquery);   //Checked
    if ($lresult->RecordCount() > 0)
    {
        while ($lrow=$lresult->FetchRow())
        {
            $labelans[]=$lrow['answer'];
            $labelcode[]=$lrow['code'];
            $labels[]=array("answer"=>$lrow['answer'], "code"=>$lrow['code']);
        }
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
        {
            $labelcode[]='';
            $labelans[]=$clang->gT('No answer');
            $labels[]=array('answer'=>$clang->gT('No answer'), 'code'=>'');
        }
        if ($qidattributes['random_order']==1) {
            $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
        }
        else
        {
            $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
        }
        $ansresult = db_execute_assoc($ansquery);  //Checked
        $anscount = $ansresult->RecordCount();
        if ($anscount>0)
        {
            $fn=1;
            $cellwidth=$anscount;
            $cellwidth=round(( 50 / $cellwidth ) , 1);
            $answer = "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an array type question with a single response per row\">\n\n"
            . "\t<colgroup class=\"col-responses\">\n"
            . "\t<col class=\"col-answers\" width=\"50%\" />\n";
            $odd_even = '';
            for( $c = 0 ; $c < $anscount ; ++$c )
            {
                $odd_even = alternation($odd_even);
                $answer .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
            }
            $answer .= "\t</colgroup>\n\n"
            . "\t<thead>\n"
            . "<tr>\n"
            . "\t<td>&nbsp;</td>\n";
            while ($ansrow = $ansresult->FetchRow())
            {
                $anscode[]=$ansrow['title'];
                $answers[]=dTexts::run($ansrow['question']);
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
                $answer .= "\t<th class=\"$odd_even\">$ld</th>\n";
            }
            unset($trbc);
            $answer .= "</tr>\n\t</thead>\n\n\t<tbody>\n";
            $ansrowcount=0;
            $ansrowtotallength=0;
            while ($ansrow = $ansresult->FetchRow())
            {
                $ansrowcount++;
                $ansrowtotallength=$ansrowtotallength+strlen($ansrow['question']);
            }
            $percwidth=100 - ($cellwidth*$anscount);
            foreach($labels as $ansrow)
            {
                $answer .= "<tr>\n"
                . "\t<th class=\"arraycaptionleft\">{$ansrow['answer']}</th>\n";
                foreach ($anscode as $ld)
                {
                    //if (!isset($trbc) || $trbc == 'array1') {$trbc = 'array2';} else {$trbc = 'array1';}
                    $myfname=$ia[1].$ld;
                    $answer .= "\t<td class=\"answer_cell_00$ld\">\n"
                    . "<label for=\"answer".$myfname.'-'.$ansrow['code']."\">\n"
                    . "\t<input class=\"radio\" type=\"radio\" name=\"".$myfname.'" value="'.$ansrow['code'].'" '
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
                    $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n\t</td>\n";
                }
                unset($trbc);
                $answer .= "</tr>\n";
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
        $answer = "<p class='error'>".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
        $inputnames = '';
    }
    return array($answer, $inputnames);
}


// ---------------------------------------------------------------
function do_array_dual($ia)
{
    global $dbprefix, $connect, $thissurvey, $clang;
    global $repeatheadings;
    global $notanswered;
    global $minrepeatheadings;

    if ($ia[8] == 'Y')
    {
        $checkconditionFunction = "checkconditions";
    }
    else
    {
        $checkconditionFunction = "noop_checkconditions";
    }

    $inputnames=array();
    $labelans1=array();
    $labelans=array();
    $qquery = "SELECT other FROM {$dbprefix}questions WHERE qid=".$ia[0]." AND language='".$_SESSION['s_lang']."'";
    $other = $connect->GetOne($qquery);    //Checked
    $lquery =  "SELECT * FROM {$dbprefix}answers WHERE scale_id=0 AND qid={$ia[0]} AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, code";
    $lquery1 = "SELECT * FROM {$dbprefix}answers WHERE scale_id=1 AND qid={$ia[0]} AND language='".$_SESSION['s_lang']."' ORDER BY sortorder, code";
    $qidattributes=getQuestionAttributes($ia[0],$ia[4]);

    if ($qidattributes['use_dropdown']==1)
    {
        $useDropdownLayout = true;
    }
    else
    {
        $useDropdownLayout = false;
    }

    if (trim($qidattributes['dualscale_headerA'])!='') {
        $leftheader= $clang->gT($qidattributes['dualscale_headerA']);
    }
    else
    {
        $leftheader ='';
    }

    if (trim($qidattributes['dualscale_headerB'])!='')
    {
        $rightheader= $clang->gT($qidattributes['dualscale_headerB']);
    }
    else {
        $rightheader ='';
    }

    $lresult = db_execute_assoc($lquery); //Checked
    if ($useDropdownLayout === false && $lresult->RecordCount() > 0)
    {

        if (trim($qidattributes['answer_width'])!='')
        {
            $answerwidth=$qidattributes['answer_width'];
        }
        else
        {
            $answerwidth=20;
        }
        $columnswidth = 100 - $answerwidth;


        while ($lrow=$lresult->FetchRow())
        {
            $labelans[]=$lrow['answer'];
            $labelcode[]=$lrow['code'];
        }
        $lresult1 = db_execute_assoc($lquery1); //Checked
        if ($lresult1->RecordCount() > 0)
        {
            while ($lrow1=$lresult1->FetchRow())
            {
                $labelans1[]=$lrow1['answer'];
                $labelcode1[]=$lrow1['code'];
            }
        }
        $numrows=count($labelans) + count($labelans1);
        if ($ia[6] != "Y" && SHOW_NO_ANSWER == 1) {$numrows++;}
        $cellwidth=$columnswidth/$numrows;

        $cellwidth=sprintf("%02d", $cellwidth);

        $ansquery = "SELECT question FROM {$dbprefix}questions WHERE parent_qid=".$ia[0]." and scale_id=0 AND question like '%|%'";
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
        if ($qidattributes['random_order']==1) {
            $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY ".db_random();
        }
        else
        {
            $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] AND language='".$_SESSION['s_lang']."' and scale_id=0 ORDER BY question_order";
        }
        $ansresult = db_execute_assoc($ansquery);   //Checked
        $anscount = $ansresult->RecordCount();
        $fn=1;
        // unselect second scale when using "no answer"
        $answer = "<script type='text/javascript'>\n"
        . "<!--\n"
        . "\tfunction noanswer_checkconditions(value, name, type)\n"
        . "{\n"
        . "\tvar vname;\n"
        . "\tvname = name.replace(/#0/g,\"#1\");\n"
        . "\tfor(var i=0, n=document.getElementsByName(vname).length; i<n; ++i)\n"
        . "\t{\n"
        . "document.getElementsByName(vname)[i].checked=false;\n"
        . "\t}\n"
        . "\t$checkconditionFunction(value, name, type);\n"
        . "}\n"
        . "\tfunction secondlabel_checkconditions(value, name, type)\n"
        . "{\n"
        . "\tvar vname;\n"
        . "\tvname = \"answer\"+name.replace(/#1/g,\"#0-\");\n"
        . "\tif(document.getElementById(vname))\n"
        . "\t{\n"
        . "document.getElementById(vname).checked=false;\n"
        . "\t}\n"
        . "\t$checkconditionFunction(value, name, type);\n"
        . "}\n"
        . " //-->\n"
        . " </script>\n";



        // Header row and colgroups
        $mycolumns = "\t<colgroup class=\"col-responses group-1\">\n"
        ."\t<col class=\"col-answers\" width=\"$answerwidth%\" />\n";


        $myheader2 = "\n<tr class=\"array1 header_row\">\n"
        . "\t<th class=\"header_answer_text\">&nbsp;</th>\n\n";
        $odd_even = '';
        foreach ($labelans as $ld)
        {
            $myheader2 .= "\t<th>".$ld."</th>\n";
            $odd_even = alternation($odd_even);
            $mycolumns .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
        }
        $mycolumns .= "\t</colgroup>\n";

        if (count($labelans1)>0) // if second label set is used
        {
            $mycolumns .= "\t<colgroup class=\"col-responses group-2\">\n"
            . "\t<col class=\"seperator\" />\n";
            $myheader2 .= "\n\t<td class=\"header_separator\">&nbsp;</td>\n\n"; // Separator
            foreach ($labelans1 as $ld)
            {
                $myheader2 .= "\t<th>".$ld."</th>\n";
                $odd_even = alternation($odd_even);
                $mycolumns .= "<col class=\"$odd_even\" width=\"$cellwidth%\" />\n";
            }

        }
        if ($right_exists)
        {
        	$myheader2 .= "\t<td class=\"header_answer_text_right\">&nbsp;</td>\n";
            $mycolumns .= "\n\t<col class=\"answertextright\" />\n\n";
        }
        if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory and we can show "no answer"
        {
        	$myheader2 .= "\t<td class=\"header_separator\">&nbsp;</td>\n"; // Separator
            $myheader2 .= "\t<th class=\"header_no_answer\">".$clang->gT('No answer')."</th>\n";
            $odd_even = alternation($odd_even);
            $mycolumns .= "\n\t<col class=\"seperator\" />\n\n";
            $mycolumns .= "\t<col class=\"col-no-answer $odd_even\" width=\"$cellwidth%\" />\n";
        }

        $mycolumns .= "\t</colgroup>\n";
        $myheader2 .= "</tr>\n";



        // build first row of header if needed
        if ($leftheader != '' || $rightheader !='')
        {
            $myheader1 = "<tr class=\"array1 groups header_row\">\n"
            . "\t<th class=\"header_answer_text\">&nbsp;</th>\n"
            . "\t<th colspan=\"".count($labelans)."\" class=\"dsheader\">$leftheader</th>\n";

            if (count($labelans1)>0)
            {
                $myheader1 .= "\t<td class=\"header_separator\">&nbsp;</td>\n" // Separator
                ."\t<th colspan=\"".count($labelans1)."\" class=\"dsheader\">$rightheader</th>\n";
            }
			if ($right_exists)
			{
				$myheader1 .= "\t<td class=\"header_answer_text_right\">&nbsp;</td>\n";
			}
            if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
            {
            	$myheader1 .= "\t<td class=\"header_separator\">&nbsp;</td>\n"; // Separator
                $myheader1 .= "\t<th class=\"header_no_answer\">&nbsp;</th>\n";
            }
            $myheader1 .= "</tr>\n";
        }
        else
        {
            $myheader1 = '';
        }

        $answer .= "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - a dual array type question\">\n"
        . $mycolumns
        . "\n\t<thead>\n"
        . $myheader1
        . $myheader2
        . "\n\t</thead>\n";

        $trbc = '';
        while ($ansrow = $ansresult->FetchRow())
        {
            // Build repeat headings if needed
            if (isset($repeatheadings) && $repeatheadings > 0 && ($fn-1) > 0 && ($fn-1) % $repeatheadings == 0)
            {
                if ( ($anscount - $fn + 1) >= $minrepeatheadings )
                {
                    $answer .= "<tbody>\n<tr  class=\"repeat\">\n"
                    . "\t<th class=\"header_answer_text\">&nbsp;</th>\n";
                    foreach ($labelans as $ld)
                    {
                        $answer .= "\t<th>".$ld."</th>\n";
                    }
                    if (count($labelans1)>0) // if second label set is used
                    {
                        $answer .= "<th class=\"header_separator\">&nbsp;</th>\n"; // Separator
                        foreach ($labelans1 as $ld)
                        {
                            $answer .= "\t<th>".$ld."</th>\n";
                        }
                    }
					if ($right_exists)
					{
						$answer .= "\t<td class=\"header_answer_text_right\">&nbsp;</td>\n";
					}
                    if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1) //Question is not mandatory and we can show "no answer"
                    {
                        $answer .= "\t<td class=\"header_separator\">&nbsp;</td>\n"; // Separator
                        $answer .= "\t<th class=\"header_no_answer\">".$clang->gT('No answer')."</th>\n";
                    }
                    $answer .= "</tr>\n</tbody>\n";
                }
            }

            $trbc = alternation($trbc , 'row');
            $answertext=dTexts::run($ansrow['question']);
            $answertextsave=$answertext;

            $dualgroup=0;
            $myfname0= $ia[1].$ansrow['title'];
            $myfname = $ia[1].$ansrow['title'].'#0';
            $myfname1 = $ia[1].$ansrow['title'].'#1'; // new multi-scale-answer
            /* Check if this item has not been answered: the 'notanswered' variable must be an array,
            containing a list of unanswered questions, the current question must be in the array,
            and there must be no answer available for the item in this session. */
            if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && (($_SESSION[$myfname] == '') || ($_SESSION[$myfname1] == '')) )
            {
                $answertext = "<span class='errormandatory'>{$answertext}</span>";
            }

            // Get array_filter stuff
            list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, $ansrow, $myfname0, $trbc, $myfname);

            $answer .= $htmltbody2;

            if (strpos($answertext,'|')) {$answertext=substr($answertext,0, strpos($answertext,'|'));}

            array_push($inputnames,$myfname);
            $answer .= "<tr class=\"$trbc\">\n"
            . "\t<th class=\"answertext\">\n"
            . $hiddenfield
            . "$answertext\n"
            . "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
            if (isset($_SESSION[$myfname])) {$answer .= $_SESSION[$myfname];}
            $answer .= "\" />\n\t</th>\n";
            $hiddenanswers='';
            $thiskey=0;

            foreach ($labelcode as $ld)
            {
                $answer .= "\t<td class=\"answer_cell_1_00$ld\">\n"
                . "<label for=\"answer$myfname-$ld\">\n"
                . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname\" value=\"$ld\" id=\"answer$myfname-$ld\" title=\""
                . html_escape(strip_tags($labelans[$thiskey])).'"';
                if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $ld)
                {
                    $answer .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer .= " onclick=\"$checkconditionFunction(this.value, this.name, this.type)\" />\n</label>\n";
                // --> END NEW FEATURE - SAVE
                $answer .= "\n\t</td>\n";
                $thiskey++;
            }
            if (count($labelans1)>0) // if second label set is used
            {
                $dualgroup++;
                $hiddenanswers='';
                $answer .= "\t<td class=\"dual_scale_separator\">&nbsp;</td>\n";		// separator
                array_push($inputnames,$myfname1);
                $hiddenanswers .= "<input type=\"hidden\" name=\"java$myfname1\" id=\"java$myfname1\" value=\"";
                if (isset($_SESSION[$myfname1])) {$hiddenanswers .= $_SESSION[$myfname1];}
                $hiddenanswers .= "\" />\n";
                $thiskey=0;
                foreach ($labelcode1 as $ld) // second label set
                {
                    $answer .= "\t<td class=\"answer_cell_2_00$ld\">\n";
                    if ($hiddenanswers!='')
                    {
                        $answer .=$hiddenanswers;
                        $hiddenanswers='';
                    }
                    $answer .= "<label for=\"answer$myfname1-$ld\">\n"
                    . "\t<input class=\"radio\" type=\"radio\" name=\"$myfname1\" value=\"$ld\" id=\"answer$myfname1-$ld\" title=\""
                    . html_escape(strip_tags($labelans1[$thiskey])).'"';
                    if (isset($_SESSION[$myfname1]) && $_SESSION[$myfname1] == $ld)
                    {
                        $answer .= CHECKED;
                    }
                    // --> START NEW FEATURE - SAVE
                    $answer .= " onclick=\"secondlabel_checkconditions(this.value, this.name, this.type)\" />\n</label>\n";
                    // --> END NEW FEATURE - SAVE

                    $answer .= "\t</td>\n";
                    $thiskey++;
                }
            }
            if (strpos($answertextsave,'|'))
            {
                $answertext=substr($answertextsave,strpos($answertextsave,'|')+1);
                $answer .= "\t<td class=\"answertextright\">$answertext</td>\n";
                $hiddenanswers = '';
            }
            elseif ($right_exists)
            {
                $answer .= "\t<td class=\"answertextright\">&nbsp;</td>\n";
            }

            if ($ia[6] != "Y" && SHOW_NO_ANSWER == 1)
            {
                $answer .= "\t<td class=\"dual_scale_separator\">&nbsp;</td>\n"; // separator
				$answer .= "\t<td class=\"dual_scale_no_answer\">\n"
                . "<label for='answer$myfname-'>\n"
                . "\t<input class='radio' type='radio' name='$myfname' value='' id='answer$myfname-' title='".$clang->gT("No answer")."'";
                if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == "")
                {
                    $answer .= CHECKED;
                }
                // --> START NEW FEATURE - SAVE
                $answer .= " onclick=\"noanswer_checkconditions(this.value, this.name, this.type)\" />\n"
                . "</label>\n"
                . "\t</td>\n";
                // --> END NEW FEATURE - SAVE
            }

            $answer .= "</tr>\n";
        	$answer .= "\t</tbody>\n";
            // $inputnames[]=$myfname;
            //IF a MULTIPLE of flexi-redisplay figure, repeat the headings
            $fn++;
        }
        $answer .= "</table>\n";
    }
    elseif ($useDropdownLayout === true && $lresult->RecordCount() > 0)
    {

        if (trim($qidattributes['answer_width'])!='')
        {
            $answerwidth=$qidattributes['answer_width'];
        } else {
            $answerwidth=20;
        }
        $separatorwidth=(100-$answerwidth)/10;
        $columnswidth=100-$answerwidth-($separatorwidth*2);

        $answer = "";

        // Get Answers

        //question atribute random_order set?
        if ($qidattributes['random_order']==1) {
            $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] and scale_id=0 AND language='".$_SESSION['s_lang']."' ORDER BY ".db_random();
        }

        //no question attributes -> order by sortorder
        else
        {
            $ansquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid=$ia[0] and scale_id=0 AND language='".$_SESSION['s_lang']."' ORDER BY question_order";
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
						'title' => $lrow['answer']);
            }
            $lresult1 = db_execute_assoc($lquery1);   //Checked
            while ($lrow1=$lresult1->FetchRow())
            {
                $labels1[]=Array('code' => $lrow1['code'],
						'title' => $lrow1['answer']);
            }


            // Get attributes for Headers and Prefix/Suffix

            if (trim($qidattributes['dropdown_prepostfix'])!='') {
                list ($ddprefix, $ddsuffix) =explode("|",$qidattributes['dropdown_prepostfix']);
                $ddprefix = $ddprefix;
                $ddsuffix = $ddsuffix;
            }
            else
            {
                $ddprefix ='';
                $ddsuffix='';
            }
            if (trim($qidattributes['dropdown_separators'])!='') {
                list ($postanswSep, $interddSep) =explode('|',$qidattributes['dropdown_separators']);
                $postanswSep = $postanswSep;
                $interddSep = $interddSep;
            }
            else {
                $postanswSep = '';
                $interddSep = '';
            }

            $colspan_1 = '';
            $colspan_2 = '';
            $suffix_cell = '';
            $answer .= "\n<table class=\"question\" summary=\"".str_replace('"','' ,strip_tags($ia[3]))." - an dual array type question\">\n\n"
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
                $suffix_cell = "\t<td>&nbsp;</td>\n"; // suffix
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
            . "<tr>\n"
            . "\t<td$colspan_1>&nbsp;</td>\n" // prefix
            . "\n"
            //			. "\t<td align='center' width='$columnswidth%'><span class='dsheader'>$leftheader</span></td>\n"
            . "\t<th>$leftheader</th>\n"
            . "\n"
            . "\t<td$colspan_2>&nbsp;</td>\n" // suffix // Inter DD separator // prefix
            //			. "\t<td align='center' width='$columnswidth%'><span class='dsheader'>$rightheader</span></td>\n"
            . "\t<th>$rightheader</th>\n"
            . $suffix_cell."</tr>\n"
            . "\t</thead>\n\n";

            $trbc = '';
            while ($ansrow = $ansresult->FetchRow())
            {
                $rowname = $ia[1].$ansrow['title'];
                $dualgroup=0;
                $myfname = $ia[1].$ansrow['title']."#".$dualgroup;
                $dualgroup1=1;
                $myfname1 = $ia[1].$ansrow['title']."#".$dualgroup1;

                if ((is_array($notanswered)) && (array_search($ia[1], $notanswered) !== FALSE) && ($_SESSION[$myfname] == "" || $_SESSION[$myfname1] == "") )
                {
                    $answertext="<span class='errormandatory'>".dTexts::run($ansrow['question'])."</span>";
                }
                else
                {
                    $answertext=dTexts::run($ansrow['question']);
                }

                $trbc = alternation($trbc , 'row');

                // Get array_filter stuff
                list($htmltbody2, $hiddenfield)=return_array_filter_strings($ia, $qidattributes, $thissurvey, $ansrow, $rowname, $trbc, $myfname);

                $answer .= $htmltbody2;

                $answer .= "<tr class=\"$trbc\">\n"
                . "\t<th class=\"answertext\">\n"
                . "<label for=\"answer$rowname\">\n"
                . $hiddenfield
                . "$answertext\n"
                . "</label>\n"
                . "\t</th>\n";

                // Label0

                // prefix
                if($ddprefix != '')
                {
                    $answer .= "\t<td class=\"ddprefix\">$ddprefix</td>\n";
                }
                $answer .= "\t<td >\n"
                . "<select name=\"$myfname\" id=\"answer$myfname\" onchange=\"array_dual_dd_checkconditions(this.value, this.name, this.type,$dualgroup,$checkconditionFunction);\">\n";

                if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] =='')
                {
                    $answer .= "\t<option value=\"\" ".SELECTED.'>'.$clang->gT('Please choose...')."</option>\n";
                }

                foreach ($labels0 as $lrow)
                {
                    $answer .= "\t<option value=\"".$lrow['code'].'" ';
                    if (isset($_SESSION[$myfname]) && $_SESSION[$myfname] == $lrow['code'])
                    {
                        $answer .= SELECTED;
                    }
                    $answer .= '>'.$lrow['title']."</option>\n";
                }
                // If not mandatory and showanswer, show no ans
                if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
                {
                    $answer .= "\t<option value=\"\" ";
                    if (!isset($_SESSION[$myfname]) || $_SESSION[$myfname] == '')
                    {
                        $answer .= SELECTED;
                    }
                    $answer .= '>'.$clang->gT('No answer')."</option>\n";
                }
                $answer .= "</select>\n";

                // suffix
                if($ddsuffix != '')
                {
                    $answer .= "\t<td class=\"ddsuffix\">$ddsuffix</td>\n";
                }
                $answer .= "<input type=\"hidden\" name=\"java$myfname\" id=\"java$myfname\" value=\"";
                if (isset($_SESSION[$myfname]))
                {
                    $answer .= $_SESSION[$myfname];
                }
                $answer .= "\" />\n"
                . "\t</td>\n";

                $inputnames[]=$myfname;

                $answer .= "\t<td class=\"ddarrayseparator\">$interddSep</td>\n"; //Separator

                // Label1

                // prefix
                if($ddprefix != '')
                {
                    $answer .= "\t<td class='ddprefix'>$ddprefix</td>\n";
                }
                //				$answer .= "\t<td align='left' width='$columnswidth%'>\n"
                $answer .= "\t<td>\n"
                . "<select name=\"$myfname1\" id=\"answer$myfname1\" onchange=\"array_dual_dd_checkconditions(this.value, this.name, this.type,$dualgroup1,$checkconditionFunction);\">\n";

                if (!isset($_SESSION[$myfname1]) || $_SESSION[$myfname1] =='')
                {
                    $answer .= "\t<option value=\"\"".SELECTED.'>'.$clang->gT('Please choose...')."</option>\n";
                }

                foreach ($labels1 as $lrow1)
                {
                    $answer .= "\t<option value=\"".$lrow1['code'].'" ';
                    if (isset($_SESSION[$myfname1]) && $_SESSION[$myfname1] == $lrow1['code'])
                    {
                        $answer .= SELECTED;
                    }
                    $answer .= '>'.$lrow1['title']."</option>\n";
                }
                // If not mandatory and showanswer, show no ans
                if ($ia[6] != 'Y' && SHOW_NO_ANSWER == 1)
                {
                    $answer .= "\t<option value='' ";
                    if (!isset($_SESSION[$myfname1]) || $_SESSION[$myfname1] == '')
                    {
                        $answer .= SELECTED;
                    }
                    $answer .= ">".$clang->gT('No answer')."</option>\n";
                }
                $answer .= "</select>\n";

                // suffix
                if($ddsuffix != '')
                {
                    $answer .= "\t<td class=\"ddsuffix\">$ddsuffix</td>\n";
                }
                $answer .= "<input type=\"hidden\" name=\"java$myfname1\" id=\"java$myfname1\" value=\"";
                if (isset($_SESSION[$myfname1]))
                {
                    $answer .= $_SESSION[$myfname1];
                }
                $answer .= "\" />\n"
                . "\t</td>\n";
                $inputnames[]=$myfname1;

                $answer .= "</tr>\n";
				$answer .= "\t</tbody>\n";
            }
        } // End there are answers
        $answer .= "</table>\n";
    }
    else
    {
        $answer = "<p class='error'>".$clang->gT("Error: There are no answer options for this question and/or they don't exist in this language.")."</p>\n";
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
        $replace3=retrieve_Answer($replace2, $_SESSION['dateformats']['phpdate']);
        $text=str_replace($replace, $replace3, $text);
    } //while
    return $text;
}


// Closing PHP tag intentionally left out - yes, it is okay
