<?php

/**
 * Checks the fieldmap after survey structure modifications
 * and compares them to the survey table and makes changes.
 * Returns true if successful and false if not
 * @param $surveyid - The Survey Identifier
 * @return bool
 */
function surveyFixColumns($surveyid)
{
    global $dbprefix, $connect;
    // Check and fix duplicate column names
    surveyFixDuplicateColumns($surveyid);
    
    // Get latest fieldmap
    $fieldmap=createFieldMap($surveyid);
    
    // Find any fields that do not exist
    $fieldlist = array();
    foreach($fieldmap as $fielddata)
    {
        $fieldlist[]=$fielddata['fieldname'];
    }
        
    $dict = NewDataDictionary($connect);
    $sqlfields = $dict->MetaColumns("{$dbprefix}survey_{$surveyid}");
    $sqllist = array();
    foreach ($sqlfields as $sqlfield)
    {
        $sqllist[]=$sqlfield->name;
    }
    
    $missingFields = array_diff($fieldlist, $sqllist);
    $removedFields = array_diff($sqllist, $fieldlist);
    
    die(print_r($missingFields).print_r($removedFields));
    
    // Find any fields that have been removed
    
    // Add fields that need to be added
    
    // Remove fields that need to be removed
}

/**
 * Checks for duplicate column names and fixes them
 * Returns true if a correction has been made and false if not
 *
 * @param $surveyid - The Survey Identifier
 * @return bool
 */
function surveyFixDuplicateColumns($surveyid)
{
    list ($duplicates, $fieldmap) = surveyCheckUniqueColumns($surveyid);
    if (!empty($duplicates))
    {
        foreach ($duplicates as $dup)
        {
            $badquestion=arraySearchByKey($dup, $fieldmap, "fieldname", 1);
            surveyFixQuestionNumbering($badquestion['qid']);
        }
        return true;
    }
    return false;
}

/**
 * Checks to see if any columns being created for the survey will
 * conflict with field names.
 *
 * @param $surveyid - The Survey Identifier
 * @return bool
 */
function surveyCheckUniqueColumns($surveyid)
{
    //CHECK THAT ALL THE CREATED FIELDS WILL BE UNIQUE
    $fieldmap=createFieldMap($surveyid, "full");
    $duplicates = array();
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
    // Return the fieldmap as well, so we dont have to generate this again for fixing
    return array($duplicates, $fieldmap);
}

/**
 * Fixes the question numbering of a single question inorder to
 * prevent column name conflicts
 *
 * @param none
 * @return bool
 */
function surveyFixQuestionNumbering($qid)
{
    global $dbprefix, $connect;
    //Fix a question id - requires renumbering a question
    $oldqid = $_GET['fixnumbering'];
    $query = "SELECT qid FROM {$dbprefix}questions ORDER BY qid DESC";
    $result = db_select_limit_assoc($query, 1) or safe_die($query."<br />".$connect->ErrorMsg());
    while ($row=$result->FetchRow())
    {
        $lastqid=$row['qid'];
    }
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
 * Check the structure of the survey to identify any problems with groups, questions, etc
 * Returns an array containing error information
 * @param surveyid
 * @return string
 */
function surveyCheckStructure($surveyid)
{
    global $dbprefix, $connect;
    $baselang = GetBaseLanguageFromSurveyID($surveyid);
    $failedcheck = array();

    // Check for empty groups
    $groupquery = "SELECT g.gid,g.group_name,count(q.qid) as count from {$dbprefix}questions as q RIGHT JOIN {$dbprefix}groups as g ON q.gid=g.gid WHERE g.sid=$surveyid AND g.language='$baselang' AND q.language='$baselang' group by g.gid,g.group_name;";
    $groupresult=db_execute_assoc($groupquery) or safe_die($groupquery."<br />".$connect->ErrorMsg());
    while ($row=$groupresult->FetchRow())
    {
        if ($row['count'] == 0)
        {
            $failedcheck[]=array($row['gid'], $row['group_name'], ": ".$clang->gT("This group does not contain any question(s)."));
        }
    }

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
            $chaquery = "SELECT * FROM {$dbprefix}questions WHERE parent_qid = {$chkrow['qid']} ORDER BY question_order, question";
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
    $chkquery = "SELECT q.qid, question, gid FROM {$dbprefix}questions as q WHERE (select count(*) from {$dbprefix}answers as a where a.qid=q.qid and scale_id=0)=0 and sid={$_GET['sid']} AND type IN ('F', 'H', 'W', 'Z', '1')";
    $chkresult = db_execute_assoc($chkquery) or safe_die ("Couldn't check questions for missing answers<br />$chkquery<br />".$connect->ErrorMsg());
    while($chkrow = $chkresult->FetchRow())
    {
        $failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question requires answers, but none are set."), $chkrow['gid']);
    } // while

    //CHECK THAT DUAL Array has answers set
    $chkquery = "SELECT q.qid, question, gid FROM {$dbprefix}questions as q WHERE (select count(*) from {$dbprefix}answers as a where a.qid=q.qid and scale_id=1)=0 and sid={$_GET['sid']} AND type='1'";
    $chkresult = db_execute_assoc($chkquery) or safe_die ("Couldn't check questions for missing 2nd answer set<br />$chkquery<br />".$connect->ErrorMsg());
    while($chkrow = $chkresult->FetchRow())
    {
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
    while ($qrow = $qorderresult->FetchRow())
    {
        $qrows[] = $qrow;
    }
    usort($qrows, 'CompareGroupThenTitle'); // Perform a case insensitive natural sort on group name then question title of a multidimensional array
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
    // End Check Survey Structure
    return $failedcheck;
}

/**
 * Creates the initial survey table with columns for selected survey settings
 * Returns true if successful and database error if not
 * @param surveyid
 * @return mixed
 */
function surveyCreateTable($surveyid)
{
    global $dbprefix, $databasetabletype, $connect;
    $createsurvey='';
     
    //Check for any additional fields for this survey and create necessary fields (token and datestamp)
    $pquery = "SELECT private, allowregister, datestamp, ipaddr, refurl FROM {$dbprefix}surveys WHERE sid={$surveyid}";
    $presult=db_execute_assoc($pquery);
    $prow=$presult->FetchRow();

    //Get list of questions for the base language
    $fieldmap=createFieldMap($surveyid);
 
    foreach ($fieldmap as $arow) //With each question, create the appropriate field(s)
    {
        $createsurvey .= " `{$arow['fieldname']}`";
        switch($arow['type'])
        {
            case 'id':
                $createsurvey .= " I NOTNULL AUTO PRIMARY";
                break;
            case 'token':
                $createsurvey .= " C(36)";
                break;
            case 'startlanguage':
                $createsurvey .= " C(20) NOTNULL";
                break;
            case "startdate":
            case "datestamp":
                $createsurvey .= " T NOTNULL";
                break;
            case "submitdate":
                $createsurvey .= " T";
                break;
            case "lastpage":
                $createsurvey .= " I";
                break;
            case "ipaddress":
                if ($prow['ipaddr'] == "Y")
                $createsurvey .= " X";
                break;
            case "url":
                if ($prow['refurl'] == "Y")
                $createsurvey .= " X";
                break;
        }
        $createsurvey .= ",\n";
    }
    
    //strip trailing comma and new line feed (if any)
    $createsurvey = rtrim($createsurvey, ",\n");

    $tabname = "{$dbprefix}survey_{$surveyid}"; # not using db_table_name as it quotes the table name (as does CreateTableSQL)

    $taboptarray = array('mysql' => 'ENGINE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci',
                         'mysqli'=> 'ENGINE='.$databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    $dict = NewDataDictionary($connect);
    $sqlarray = $dict->CreateTableSQL($tabname, $createsurvey, $taboptarray);
    $execresult=$dict->ExecuteSQLArray($sqlarray,1);
    if ($execresult==0 || $execresult==1)
    {
        return $connect->ErrorMsg();
    } elseif ($execresult != 0 && $execresult !=1)
    {
        // Set Auto Increment value if specified
        $anquery = "SELECT autonumber_start FROM {$dbprefix}surveys WHERE sid={$surveyid}";
        if ($anresult=db_execute_assoc($anquery))
        {
            //if there is an autonumber_start field, start auto numbering here
            while($row=$anresult->FetchRow())
            {
                if ($row['autonumber_start'] > 0)
                {
                    $autonumberquery = "ALTER TABLE {$dbprefix}survey_{$surveyid} AUTO_INCREMENT = ".$row['autonumber_start'];
                    $result = $connect->Execute($autonumberquery);
                }
            }
        }
        return true;
    }
}


?>