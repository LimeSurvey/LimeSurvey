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
 *	$Id:$
 *	Files Purpose:
 */
function fixNumbering($fixnumbering){

    global $dbprefix, $connect, $clang;
     //Fix a question id - requires renumbering a question
        $oldqid = $fixnumbering;
        $query = "SELECT qid FROM {$dbprefix}questions ORDER BY qid DESC";
        $result = db_select_limit_assoc($query, 1) or safe_die($query."<br />".$connect->ErrorMsg());
        while ($row=$result->FetchRow()) {$lastqid=$row['qid'];}
        $newqid=$lastqid+1;
        $query = "UPDATE {$dbprefix}questions SET qid=$newqid WHERE qid=$oldqid";
        $result = $connect->Execute($query) or safe_die($query."<br />".$connect->ErrorMsg());
        // Update subquestions
        $query = "UPDATE {$dbprefix}questions SET parent_qid=$newqid WHERE parent_qid=$oldqid";
        $result = $connect->Execute($query) or safe_die($query."<br />".$connect->ErrorMsg());
        //Update conditions.. firstly conditions FOR this question
        $query = "UPDATE {$dbprefix}conditions SET qid=$newqid WHERE qid=$oldqid";
        $result = $connect->Execute($query) or safe_die($query."<br />".$connect->ErrorMsg());
        //Now conditions based upon this question
        $query = "SELECT cqid, cfieldname FROM {$dbprefix}conditions WHERE cqid=$oldqid";
        $result = db_execute_assoc($query) or safe_die($query."<br />".$connect->ErrorMsg());
        while ($row=$result->FetchRow())
        {
            $switcher[]=array("cqid"=>$row['cqid'], "cfieldname"=>$row['cfieldname']);
        }
        if (isset($switcher))
        {
            foreach ($switcher as $switch)
            {
                $query = "UPDATE {$dbprefix}conditions
						  SET cqid=$newqid,
						  cfieldname='".str_replace("X".$oldqid, "X".$newqid, $switch['cfieldname'])."'
						  WHERE cqid=$oldqid";
                $result = $connect->Execute($query) or safe_die($query."<br />".$connect->ErrorMsg());
            }
        }
        //Now question_attributes
        $query = "UPDATE {$dbprefix}question_attributes SET qid=$newqid WHERE qid=$oldqid";
        $result = $connect->Execute($query) or safe_die($query."<br />".$connect->ErrorMsg());
        //Now answers
        $query = "UPDATE {$dbprefix}answers SET qid=$newqid WHERE qid=$oldqid";
        $result = $connect->Execute($query) or safe_die($query."<br />".$connect->ErrorMsg());
}
/**
 * checks consistency of groups
 * @global  $dbprefix
 * @global  $connect
 * @global  $clang
 * @return <type>
 */
function checkGroup($postsid)
{
    global $dbprefix, $connect, $clang;

     $baselang = GetBaseLanguageFromSurveyID($postsid);
    $groupquery = "SELECT g.gid,g.group_name,count(q.qid) as count from {$dbprefix}questions as q RIGHT JOIN {$dbprefix}groups as g ON q.gid=g.gid AND g.language=q.language WHERE g.sid=$postsid AND g.language='$baselang' group by g.gid,g.group_name;";
    $groupresult=db_execute_assoc($groupquery) or safe_die($groupquery."<br />".$connect->ErrorMsg());
    while ($row=$groupresult->FetchRow())
    { //TIBO
        if ($row['count'] == 0)
        {
            $failedgroupcheck[]=array($row['gid'], $row['group_name'], ": ".$clang->gT("This group does not contain any question(s)."));
        }
    }
    if(isset($failedgroupcheck))
        return $failedgroupcheck;
    else
        return false;

}
/**
 * checks questions in a survey for consistency
 * @global <type> $dbprefix
 * @global <type> $connect
 * @global <type> $clang
 * @param <type> $postsid
 * @param <type> $surveyid
 * @return array $faildcheck
 */
function checkQestions($postsid, $surveyid, $qtypes)
{
     global $dbprefix, $connect, $clang;

    
     //CHECK TO MAKE SURE ALL QUESTION TYPES THAT REQUIRE ANSWERS HAVE ACTUALLY GOT ANSWERS
    //THESE QUESTION TYPES ARE:
    //	# "L" -> LIST
    //  # "O" -> LIST WITH COMMENT
    //  # "M" -> MULTIPLE OPTIONS
    //	# "P" -> MULTIPLE OPTIONS WITH COMMENTS
    //	# "A", "B", "C", "E", "F", "H", "^" -> Various Array Types
    //  # "R" -> RANKING
    //  # "U" -> FILE CSV MORE
	//  # "I" -> LANGUAGE SWITCH
    //  # ":" -> Array Multi Flexi Numbers
    //  # ";" -> Array Multi Flexi Text
    //  # "1" -> MULTI SCALE

    $chkquery = "SELECT qid, question, gid, type FROM {$dbprefix}questions WHERE sid={$surveyid} and parent_qid=0";
    $chkresult = db_execute_assoc($chkquery) or safe_die ("Couldn't get list of questions<br />$chkquery<br />".$connect->ErrorMsg());
    while ($chkrow = $chkresult->FetchRow())
    {
        if ($qtypes[$chkrow['type']]['subquestions']>0)
        {
            $chaquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid = {$chkrow['qid']} ORDER BY question_order";
            $charesult=$connect->Execute($chaquery);
            $chacount=$charesult->RecordCount();
            if ($chacount == 0)
            {
                $failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question is a subquestion type question but has no configured subquestions."), $chkrow['gid']);
            }
        }
        if ($qtypes[$chkrow['type']]['answerscales']>0)
        {
            $chaquery = "SELECT * FROM {$dbprefix}answers WHERE qid = {$chkrow['qid']} ORDER BY sortorder, answer";
            $charesult=$connect->Execute($chaquery);
            $chacount=$charesult->RecordCount();
            if ($chacount == 0)
            {
                $failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question is a multiple answer type question but has no answers."), $chkrow['gid']);
            }
        }
    }

    //NOW CHECK THAT ALL QUESTIONS HAVE A 'QUESTION TYPE' FIELD SET
    $chkquery = "SELECT qid, question, gid FROM {$dbprefix}questions WHERE sid={$_GET['sid']} AND type = ''";
    $chkresult = db_execute_assoc($chkquery) or safe_die ("Couldn't check questions for missing types<br />$chkquery<br />".$connect->ErrorMsg());
    while ($chkrow = $chkresult->FetchRow())
    {
        $failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question does not have a question 'type' set."), $chkrow['gid']);
    }




    //ChECK THAT certain array question types have answers set
    $chkquery = "SELECT q.qid, question, gid FROM {$dbprefix}questions as q WHERE (select count(*) from {$dbprefix}answers as a where a.qid=q.qid and scale_id=0)=0 and sid={$_GET['sid']} AND type IN ('F', 'H', 'W', 'Z', '1') and q.parent_qid=0";
    $chkresult = db_execute_assoc($chkquery) or safe_die ("Couldn't check questions for missing answers<br />$chkquery<br />".$connect->ErrorMsg());
    while($chkrow = $chkresult->FetchRow()){
        $failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question requires answers, but none are set."), $chkrow['gid']);
    } // while

    //CHECK THAT DUAL Array has answers set
    $chkquery = "SELECT q.qid, question, gid FROM {$dbprefix}questions as q WHERE (select count(*) from {$dbprefix}answers as a where a.qid=q.qid and scale_id=1)=0 and sid={$_GET['sid']} AND type='1' and q.parent_qid=0";
    $chkresult = db_execute_assoc($chkquery) or safe_die ("Couldn't check questions for missing 2nd answer set<br />$chkquery<br />".$connect->ErrorMsg());
    while($chkrow = $chkresult->FetchRow()){
        $failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question requires a second answer set but none is set."), $chkrow['gid']);
    } // while


    //CHECK THAT ALL CONDITIONS SET ARE FOR QUESTIONS THAT PRECEED THE QUESTION CONDITION
    //A: Make an array of all the qids in order of appearance
    //	$qorderquery="SELECT * FROM {$dbprefix}questions, {$dbprefix}groups WHERE {$dbprefix}questions.gid={$dbprefix}groups.gid AND {$dbprefix}questions.sid={$_GET['sid']} ORDER BY {$dbprefix}groups.sortorder, {$dbprefix}questions.title";
    //	$qorderresult=$connect->Execute($qorderquery) or safe_die("Couldn't generate a list of questions in order<br />$qorderquery<br />".$connect->ErrorMsg());
    //	$qordercount=$qorderresult->RecordCount();
    //	$c=0;
    //	while ($qorderrow=$qorderresult->FetchRow())
    //		{
    //		$qidorder[]=array($c, $qorderrow['qid']);
    //		$c++;
    //		}
    //TO AVOID NATURAL SORT ORDER ISSUES, FIRST GET ALL QUESTIONS IN NATURAL SORT ORDER, AND FIND OUT WHICH NUMBER IN THAT ORDER THIS QUESTION IS
    $qorderquery = "SELECT * FROM {$dbprefix}questions WHERE sid=$surveyid AND type not in ('S', 'D', 'T', 'Q')";
    $qorderresult = db_execute_assoc($qorderquery) or safe_die ("$qorderquery<br />".$connect->ErrorMsg());
    $qrows = array(); //Create an empty array in case FetchRow does not return any rows
    while ($qrow = $qorderresult->FetchRow()) {$qrows[] = $qrow;} // Get table output into array
    usort($qrows, 'GroupOrderThenQuestionOrder'); // Perform a case insensitive natural sort on group name then question title of a multidimensional array
    $c=0;
    foreach ($qrows as $qr)
    {
        $qidorder[]=array($c, $qrow['qid']);
        $c++;
    }
    $qordercount="";
    //1: Get each condition's question id
    $conquery= "SELECT {$dbprefix}conditions.qid, cqid, {$dbprefix}questions.question, "
    . "{$dbprefix}questions.gid "
    . "FROM {$dbprefix}conditions, {$dbprefix}questions, {$dbprefix}groups "
    . "WHERE {$dbprefix}conditions.qid={$dbprefix}questions.qid "
    . "AND {$dbprefix}questions.gid={$dbprefix}groups.gid ORDER BY {$dbprefix}conditions.qid";
    $conresult=db_execute_assoc($conquery) or safe_die("Couldn't check conditions for relative consistency<br />$conquery<br />".$connect->ErrorMsg());
    //2: Check each conditions cqid that it occurs later than the cqid
    while ($conrow=$conresult->FetchRow())
    {
        $cqidfound=0;
        $qidfound=0;
        $b=0;
        while ($b<$qordercount)
        {
            if ($conrow['cqid'] == $qidorder[$b][1])
            {
                $cqidfound = 1;
                $b=$qordercount;
            }
            if ($conrow['qid'] == $qidorder[$b][1])
            {
                $qidfound = 1;
                $b=$qordercount;
            }
            if ($qidfound == 1)
            {
                $failedcheck[]=array($conrow['qid'], $conrow['question'], ": ".$clang->gT("This question has a condition set, however the condition is based on a question that appears after it."), $conrow['gid']);
            }
            $b++;
        }
    }
    //CHECK THAT ALL THE CREATED FIELDS WILL BE UNIQUE
    $fieldmap=createFieldMap($surveyid, "full");
    if (isset($fieldmap))
    {
        foreach($fieldmap as $fielddata)
        {
            $fieldlist[]=$fielddata['fieldname'];
        }
        $fieldlist=array_reverse($fieldlist); //let's always change the later duplicate, not the earlier one
    }
    $checkKeysUniqueComparison = create_function('$value','if ($value > 1) return true;');
    @$duplicates = array_keys (array_filter (array_count_values($fieldlist), $checkKeysUniqueComparison));
    if (isset($duplicates))
    {
        foreach ($duplicates as $dup)
        {
            $badquestion=arraySearchByKey($dup, $fieldmap, "fieldname", 1);
            $fix = "[<a href='$scriptname?action=activate&amp;sid=$surveyid&amp;fixnumbering=".$badquestion['qid']."'>Click Here to Fix</a>]";
            $failedcheck[]=array($badquestion['qid'], $badquestion['question'], ": Bad duplicate fieldname $fix", $badquestion['gid']);
        }
    }
    if(isset($failedcheck))
        return $failedcheck;
    else
        return false;
}
