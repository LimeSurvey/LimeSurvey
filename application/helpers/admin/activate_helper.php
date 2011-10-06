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
 *	$Id: activate_functions.php 10193 2011-06-05 12:20:37Z c_schmitz $
 *	Files Purpose: holds functions to activate a survey and precheck the consistency of the survey
 */

/**
 * fixes the numbering of questions
 * @param <type> $fixnumbering
 */
function fixNumbering($fixnumbering)
{

    $CI =& get_instance();
    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;

     //Fix a question id - requires renumbering a question
    $oldqid = $fixnumbering;
    $query = "SELECT qid FROM ".$CI->db->dbprefix."questions ORDER BY qid DESC";
    $result = db_select_limit_assoc($query, 1); // or safe_die($query."<br />".$connect->ErrorMsg());
    foreach ($result->result_array() as $row) {$lastqid=$row['qid'];}
    $newqid=$lastqid+1;
    $query = "UPDATE ".$CI->db->dbprefix."questions SET qid=$newqid WHERE qid=$oldqid";
    $result = db_execute_assosc($query); // or safe_die($query."<br />".$connect->ErrorMsg());
    // Update subquestions
    $query = "UPDATE ".$CI->db->dbprefix."questions SET parent_qid=$newqid WHERE parent_qid=$oldqid";
    $result = db_execute_assosc($query); // or safe_die($query."<br />".$connect->ErrorMsg());
    //Update conditions.. firstly conditions FOR this question
    $query = "UPDATE ".$CI->db->dbprefix."conditions SET qid=$newqid WHERE qid=$oldqid";
    $result = db_execute_assosc($query); // or safe_die($query."<br />".$connect->ErrorMsg());
    //Now conditions based upon this question
    $query = "SELECT cqid, cfieldname FROM ".$CI->db->dbprefix."conditions WHERE cqid=$oldqid";
    $result = db_execute_assoc($query); // or safe_die($query."<br />".$connect->ErrorMsg());
    foreach ($result->result_array() as $row)
    {
        $switcher[]=array("cqid"=>$row['cqid'], "cfieldname"=>$row['cfieldname']);
    }
    if (isset($switcher))
    {
        foreach ($switcher as $switch)
        {
            $query = "UPDATE ".$CI->db->dbprefix."conditions
                                              SET cqid=$newqid,
                                              cfieldname='".str_replace("X".$oldqid, "X".$newqid, $switch['cfieldname'])."'
                                              WHERE cqid=$oldqid";
            $result = db_execute_assosc($query); // or safe_die($query."<br />".$connect->ErrorMsg());
        }
    }
    //Now question_attributes
    $query = "UPDATE ".$CI->db->dbprefix."question_attributes SET qid=$newqid WHERE qid=$oldqid";
    $result = db_execute_assosc($query); // or safe_die($query."<br />".$connect->ErrorMsg());
    //Now answers
    $query = "UPDATE ".$CI->db->dbprefix."answers SET qid=$newqid WHERE qid=$oldqid";
    $result = db_execute_assosc($query); // or safe_die($query."<br />".$connect->ErrorMsg());
}
/**
 * checks consistency of groups
 * @return <type>
 */
function checkGroup($postsid)
{
    $CI =& get_instance();
    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;

    $baselang = GetBaseLanguageFromSurveyID($postsid);
    $groupquery = "SELECT g.gid,g.group_name,count(q.qid) as count from ".$CI->db->dbprefix."questions as q RIGHT JOIN ".$CI->db->dbprefix."groups as g ON q.gid=g.gid AND g.language=q.language WHERE g.sid=$postsid AND g.language='$baselang' group by g.gid,g.group_name;";
    $groupresult=db_execute_assoc($groupquery); // or safe_die($groupquery."<br />".$connect->ErrorMsg());
    foreach ($groupresult->result_array() as $row)
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
 * @param <type> $postsid
 * @param <type> $surveyid
 * @return array $faildcheck
 */
function checkQuestions($postsid, $surveyid, $qtypes)
{
    $CI =& get_instance();
    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;

    //CHECK TO MAKE SURE ALL QUESTION TYPES THAT REQUIRE ANSWERS HAVE ACTUALLY GOT ANSWERS
    //THESE QUESTION TYPES ARE:
    //	# "L" -> LIST
    //  # "O" -> LIST WITH COMMENT
    //  # "M" -> Multiple choice
    //	# "P" -> Multiple choice with comments
    //	# "A", "B", "C", "E", "F", "H", "^" -> Various Array Types
    //  # "R" -> RANKING
    //  # "U" -> FILE CSV MORE
	//  # "I" -> LANGUAGE SWITCH
    //  # ":" -> Array Multi Flexi Numbers
    //  # ";" -> Array Multi Flexi Text
    //  # "1" -> MULTI SCALE

    $chkquery = "SELECT qid, question, gid, type FROM ".$CI->db->dbprefix."questions WHERE sid={$surveyid} and parent_qid=0";
    $chkresult = db_execute_assoc($chkquery) or safe_die ("Couldn't get list of questions<br />$chkquery<br />");
    foreach ($chkresult->result_array() as $chkrow)
    {
        if ($qtypes[$chkrow['type']]['subquestions']>0)
        {
            $chaquery = "SELECT * FROM ".$CI->db->dbprefix."questions WHERE parent_qid = {$chkrow['qid']} ORDER BY question_order";
            $charesult=db_execute_assoc($chaquery);
            $chacount=$charesult->num_rows();
            if ($chacount == 0)
            {
                $failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question is a subquestion type question but has no configured subquestions."), $chkrow['gid']);
            }
        }
        if ($qtypes[$chkrow['type']]['answerscales']>0)
        {
            $chaquery = "SELECT * FROM ".$CI->db->dbprefix."answers WHERE qid = {$chkrow['qid']} ORDER BY sortorder, answer";
            $charesult=db_execute_assoc($chaquery);
            $chacount=$charesult->num_rows();
            if ($chacount == 0)
            {
                $failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question is a multiple answer type question but has no answers."), $chkrow['gid']);
            }
        }
    }

    //NOW CHECK THAT ALL QUESTIONS HAVE A 'QUESTION TYPE' FIELD SET
    //$chkquery = "SELECT qid, question, gid FROM ".$CI->db->dbprefix."questions WHERE sid={$_GET['sid']} AND type = ''";
    $chkquery = "SELECT qid, question, gid FROM ".$CI->db->dbprefix."questions WHERE sid={$surveyid} AND type = ''";
    $chkresult = db_execute_assoc($chkquery) or safe_die ("Couldn't check questions for missing types<br />$chkquery<br />");
    foreach ($chkresult->result_array() as $chkrow)
    {
        $failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question does not have a question 'type' set."), $chkrow['gid']);
    }




    //ChECK THAT certain array question types have answers set
    //$chkquery = "SELECT q.qid, question, gid FROM ".$CI->db->dbprefix."questions as q WHERE (select count(*) from ".$CI->db->dbprefix."answers as a where a.qid=q.qid and scale_id=0)=0 and sid={$_GET['sid']} AND type IN ('F', 'H', 'W', 'Z', '1') and q.parent_qid=0";
    $chkquery = "SELECT q.qid, question, gid FROM ".$CI->db->dbprefix."questions as q WHERE (select count(*) from ".$CI->db->dbprefix."answers as a where a.qid=q.qid and scale_id=0)=0 and sid={$surveyid} AND type IN ('F', 'H', 'W', 'Z', '1') and q.parent_qid=0";
    $chkresult = db_execute_assoc($chkquery) or safe_die ("Couldn't check questions for missing answers<br />$chkquery<br />");
    foreach($chkresult->result_array() as $chkrow){
        $failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question requires answers, but none are set."), $chkrow['gid']);
    } // while

    //CHECK THAT DUAL Array has answers set
    //$chkquery = "SELECT q.qid, question, gid FROM ".$CI->db->dbprefix."questions as q WHERE (select count(*) from ".$CI->db->dbprefix."answers as a where a.qid=q.qid and scale_id=1)=0 and sid={$_GET['sid']} AND type='1' and q.parent_qid=0";
    $chkquery = "SELECT q.qid, question, gid FROM ".$CI->db->dbprefix."questions as q WHERE (select count(*) from ".$CI->db->dbprefix."answers as a where a.qid=q.qid and scale_id=1)=0 and sid={$surveyid} AND type='1' and q.parent_qid=0";
    $chkresult = db_execute_assoc($chkquery) or safe_die ("Couldn't check questions for missing 2nd answer set<br />$chkquery<br />");
    foreach ($chkresult->result_array() as $chkrow){
        $failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question requires a second answer set but none is set."), $chkrow['gid']);
    } // while


    //CHECK THAT ALL CONDITIONS SET ARE FOR QUESTIONS THAT PRECEED THE QUESTION CONDITION
    //A: Make an array of all the qids in order of appearance
    //	$qorderquery="SELECT * FROM ".$CI->db->dbprefix."questions, ".$CI->db->dbprefix."groups WHERE ".$CI->db->dbprefix."questions.gid=".$CI->db->dbprefix."groups.gid AND ".$CI->db->dbprefix."questions.sid={$_GET['sid']} ORDER BY ".$CI->db->dbprefix."groups.sortorder, ".$CI->db->dbprefix."questions.title";
    //	$qorderresult=db_execute_assosc($qorderquery) or safe_die("Couldn't generate a list of questions in order<br />$qorderquery<br />".$connect->ErrorMsg());
    //	$qordercount=$qorderresult->RecordCount();
    //	$c=0;
    //	while ($qorderrow=$qorderresult->FetchRow())
    //		{
    //		$qidorder[]=array($c, $qorderrow['qid']);
    //		$c++;
    //		}
    //TO AVOID NATURAL SORT ORDER ISSUES, FIRST GET ALL QUESTIONS IN NATURAL SORT ORDER, AND FIND OUT WHICH NUMBER IN THAT ORDER THIS QUESTION IS
    $qorderquery = "SELECT * FROM ".$CI->db->dbprefix."questions WHERE sid=$surveyid AND type not in ('S', 'D', 'T', 'Q')";
    $qorderresult = db_execute_assoc($qorderquery) or safe_die ("$qorderquery<br />");
    $qrows = array(); //Create an empty array in case FetchRow does not return any rows
    foreach ($qorderresult->result_array() as $qrow) {$qrows[] = $qrow;} // Get table output into array
    usort($qrows, 'GroupOrderThenQuestionOrder'); // Perform a case insensitive natural sort on group name then question title of a multidimensional array
    $c=0;
    foreach ($qrows as $qr)
    {
        $qidorder[]=array($c, $qrow['qid']);
        $c++;
    }

    $qordercount="";
    //1: Get each condition's question id
    $conquery= "SELECT ".$CI->db->dbprefix."conditions.qid, cqid, ".$CI->db->dbprefix."questions.question, "
    . "".$CI->db->dbprefix."questions.gid "
    . "FROM ".$CI->db->dbprefix."conditions, ".$CI->db->dbprefix."questions, ".$CI->db->dbprefix."groups "
    . "WHERE ".$CI->db->dbprefix."conditions.qid=".$CI->db->dbprefix."questions.qid "
    . "AND ".$CI->db->dbprefix."questions.gid=".$CI->db->dbprefix."groups.gid ORDER BY ".$CI->db->dbprefix."conditions.qid";
    $conresult=db_execute_assoc($conquery) or safe_die("Couldn't check conditions for relative consistency<br />$conquery<br />");
    //2: Check each conditions cqid that it occurs later than the cqid
    foreach ($conresult->result_array() as $conrow)
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
/**
 * Function to activate a survey
 * @param int $surveyid The Survey ID
 * @param bool $simulate
 * @return string
 */


function activateSurvey($surveyid, $simulate = false)
{
    $CI =& get_instance();
    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;


     $createsurvey='';
     $activateoutput='';
     $createsurveytimings='';
     $fieldstiming = array();
     $createsurveydirectory=false;
    //Check for any additional fields for this survey and create necessary fields (token and datestamp)
    $pquery = "SELECT anonymized, allowregister, datestamp, ipaddr, refurl, savetimings FROM ".$CI->db->dbprefix."surveys WHERE sid={$surveyid}";
    $presult=db_execute_assoc($pquery);
    $prow=$presult->row_array();
    if ($prow['allowregister'] == "Y")
    {
        $surveyallowsregistration="TRUE";
    }
    if ($prow['savetimings'] == "Y")
    {
        $savetimings="TRUE";
    }

    //Get list of questions for the base language
    $fieldmap=createFieldMap($surveyid);
    $CI->load->dbforge();
    foreach ($fieldmap as $j=>$arow) //With each question, create the appropriate field(s)
    {
        if ($createsurvey!='') {$createsurvey .= ",\n";}
        $createsurvey .= ' `'.$arow['fieldname'].'`';
        $createsurveybkup = $createsurvey;
        $createsurvey = '';
        switch($arow['type'])
        {
            case 'startlanguage':
                $createsurvey .= " VARCHAR(20) NOT NULL";
                break;
            case 'id':
                $createsurvey .= " INT(10) NOT NULL AUTO_INCREMENT";
                $createsurveytimings .= " `{$arow['fieldname']}` INT NOT NULL PRIMARY,\n";
                break;
            case "startdate":
            case "datestamp":
                $createsurvey .= " DATETIME NOT NULL";
                break;
            case "submitdate":
                $createsurvey .= " DATETIME";
                break;
            case "lastpage":
                $createsurvey .= " INT(10)";
                break;
            case "N":  //NUMERICAL
                $createsurvey .= " FLOAT";
                break;
            case "S":  //SHORT TEXT
                if ($CI->db->dbdriver=='mysql' || $CI->db->dbdriver=='mysqli')    {$createsurvey .= " TEXT";}
                else  {$createsurvey .= " VARCHAR(255)";}
                break;
            case "L":  //LIST (RADIO)
            case "!":  //LIST (DROPDOWN)
            case "M":  //Multiple choice
            case "P":  //Multiple choice with comment
            case "O":  //DROPDOWN LIST WITH COMMENT
                if ($arow['aid'] != 'other' && strpos($arow['aid'],'comment')===false && strpos($arow['aid'],'othercomment')===false)
                {
                    $createsurvey .= " VARCHAR(5)";
                }
                else
                {
                    $createsurvey .= " TEXT";
                }
                break;
            case "K":  // Multiple Numerical
                $createsurvey .= " FLOAT(20)";
                break;
            case "U":  //Huge text
            case "Q":  //Multiple short text
            case "T":  //LONG TEXT
            case ";":  //Multi Flexi
            case ":":  //Multi Flexi
                $createsurvey .= " TEXT";
                break;
            case "D":  //DATE
                $createsurvey .= " DATETIME";
                break;
            case "5":  //5 Point Choice
            case "G":  //Gender
            case "Y":  //YesNo
            case "X":  //Boilerplate
                $createsurvey .= " VARCHAR(1)";
                break;
            case "I":  //Language switch
                $createsurvey .= " VARCHAR(20)";
                break;
            case "|":
                $createsurveydirectory = true;
                if (strpos($arow['fieldname'], "_"))
                    $createsurvey .= " INT(1)";
                else
                    $createsurvey .= " TEXT";
                break;
            case "ipaddress":
                if ($prow['ipaddr'] == "Y")
                    $createsurvey .= " TEXT";
                break;
            case "url":
                if ($prow['refurl'] == "Y")
                    $createsurvey .= " TEXT";
                break;
            case "token":
                if ($prow['anonymized'] == "N")
                {
                    $createsurvey .= " VARCHAR(36)";
                }
                break;
            case '*': // Equation
                $createsurvey .= " TEXT";
                break;
            default:
                $createsurvey .= " VARCHAR(5)";
        }

        if ($simulate){
            $tempTrim = trim($createsurvey);
            $brackets = strpos($tempTrim,"(");
            if ($brackets === false){
                $type = substr($tempTrim,0,2);
            }
            else{
                $type = substr($tempTrim,0,2);
            }
            $arrSim[] = array($type);
        }

        $createsurvey = $createsurveybkup. $createsurvey;
    }

    $CI->dbforge->add_field($createsurvey);
	$CI->dbforge->add_key('id', TRUE);

    if ($simulate){
        return array('dbengine'=>$CI->db->databasetabletype, 'dbtype'=>$CI->db->dbdriver, 'fields'=>$arrSim);
    }

    // If last question is of type MCABCEFHP^QKJR let's get rid of the ending coma in createsurvey
    $createsurvey = rtrim($createsurvey, ",\n")."\n"; // Does nothing if not ending with a comma

    $tabname = "survey_{$surveyid}"; // dbprefix is added automatically

    /**
     $taboptarray = array('mysql' => 'ENGINE='.$CI->db->databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci',
                         'mysqli'=> 'ENGINE='.$CI->db->databasetabletype.'  CHARACTER SET utf8 COLLATE utf8_unicode_ci');
    */
    //$dict = NewDataDictionary($connect);
    //$sqlarray = $dict->CreateTableSQL($tabname, $createsurvey, $taboptarray);

    $execresult=$CI->dbforge->create_table($tabname, TRUE);

    if (isset($savetimings) && $savetimings=="TRUE")
    {
        $tabnametimings = $tabname .'_timings';


        //$sqlarraytimings = $dict->CreateTableSQL($tabnametimings, $createsurveytimings, $taboptarray);
    }

    //$execresult=$dict->ExecuteSQLArray($sqlarray,1);
    //if ($execresult==0 || $execresult==1)
    if (!$execresult)
    {
        $link = site_url("admin/survey/view/".$surveyid);
        $activateoutput .= "<br />\n<div class='messagebox ui-corner-all'>\n" .
        "<div class='header ui-widget-header'>".$clang->gT("Activate Survey")." ($surveyid)</div>\n" .
        "<div class='warningheader'>".$clang->gT("Survey could not be actived.")."</div>\n" .
        "<p>" .
        $clang->gT("Database error!!")."\n <font color='red'>" ."</font>\n" .
        "<pre>$createsurvey</pre>\n
        <a href='$link'>".$clang->gT("Main Admin Screen")."</a>\n</div>" ;
    }

    //if ($execresult != 0 && $execresult !=1)
    if ($execresult)
    {

        $anquery = "SELECT autonumber_start FROM ".$CI->db->dbprefix."surveys WHERE sid={$surveyid}";
        if ($anresult=db_execute_assoc($anquery))
        {
            //if there is an autonumber_start field, start auto numbering here
            foreach($anresult->result_array() as $row)
            {
                if ($row['autonumber_start'] > 0)
                {
                    if ($CI->db->dbdriver=='odbc_mssql' || $CI->db->dbdriver=='odbtp' || $CI->db->dbdriver=='mssql_n' || $CI->db->dbdriver=='mssqlnative') {
                        mssql_drop_primary_index('survey_'.$surveyid);
                        mssql_drop_constraint('id','survey_'.$surveyid);
                        $autonumberquery = "alter table ".$CI->db->dbprefix."survey_{$surveyid} drop column id ";
                        db_execute_assoc($autonumberquery);
                        $autonumberquery = "alter table ".$CI->db->dbprefix."survey_{$surveyid} add [id] int identity({$row['autonumber_start']},1)";
                        db_execute_assoc($autonumberquery);
                    }
                    else
                    {
                        $autonumberquery = "ALTER TABLE ".$CI->db->dbprefix."survey_{$surveyid} AUTO_INCREMENT = ".$row['autonumber_start'];
                        $result = @db_execute_assoc($autonumberquery);

                    }
                }
            }

            if (isset($savetimings) && $savetimings=="TRUE")
            {
                $timingsfieldmap = createTimingsFieldMap($surveyid);
                $createsurveytimings .= '`'.implode("` F DEFAULT '0',\n`",array_keys($timingsfieldmap)) . "` F DEFAULT '0'";

                $CI->dbforge->add_field(array('id'=>array('type'=>'INT')));

                foreach ($timingsfieldmap as $field=>$fielddata)
                {
                    $CI->dbforge->add_field(' `'.$field.'` FLOAT');
                }
                $CI->dbforge->create_table($tabnametimings, TRUE);
                //$dict->ExecuteSQLArray($sqlarraytimings,1);    // create a timings table for this survey
            }
        }

        $activateoutput .= "<br />\n<div class='messagebox ui-corner-all'>\n";
        $activateoutput .= "<div class='header ui-widget-header'>".$clang->gT("Activate Survey")." ($surveyid)</div>\n";
        $activateoutput .= "<div class='successheader'>".$clang->gT("Survey has been activated. Results table has been successfully created.")."</div><br /><br />\n";

        // create the survey directory where the uploaded files can be saved
        if ($createsurveydirectory)
            if (!file_exists($CI->config->item('rootdir')."upload/surveys/" . $surveyid . "/files") && !(mkdir($CI->config->item('rootdir')."upload/surveys/" . $surveyid . "/files", 0777, true)))
            if (!file_exists($CI->config->item('uploaddir')."/surveys/" . $surveyid . "/files"))
            {
               if (!(mkdir($CI->config->item('uploaddir')."/surveys/" . $surveyid . "/files", 0777, true)))
               {

                $activateoutput .= "<div class='warningheader'>".
                    $clang->gT("The required directory for saving the uploaded files couldn't be created. Please check file premissions on the ".$CI->config->item('rootdir')."upload/surveys directory.") . "</div>";
               }
               else
               {
                   file_put_contents($CI->config->item('uploaddir')."/surveys/" . $surveyid . "/files/index.html",'<html><head></head><body></body></html>');
               }
            }
        $acquery = "UPDATE ".$CI->db->dbprefix."surveys SET active='Y' WHERE sid=".$surveyid;
        $acresult = db_execute_assoc($acquery);

        if (isset($surveyallowsregistration) && $surveyallowsregistration == "TRUE")
        {
            $activateoutput .= $clang->gT("This survey allows public registration. A token table must also be created.")."<br /><br />\n";
            $activateoutput .= "<input type='submit' value='".$clang->gT("Initialise tokens")."' onclick=\"".get2post(site_url("admin/survey/activate/".$surveyid)."?action=tokens&amp;sid={$surveyid}&amp;createtable=Y")."\" />\n";
        }
        else
        {
            $link = site_url("admin/survey/view/".$surveyid);

            $activateoutput .= $clang->gT("This survey is now active, and responses can be recorded.")."<br /><br />\n";
            $activateoutput .= "<strong>".$clang->gT("Open-access mode").":</strong> ".$clang->gT("No invitation code is needed to complete the survey.")."<br />".$clang->gT("You can switch to the closed-access mode by initialising a token table with the button below.")."<br /><br />\n";
            $activateoutput .= "<input type='submit' value='".$clang->gT("Switch to closed-access mode")."' onclick=\"".get2post(site_url("admin/survey/activate/".$surveyid)."?action=tokens&amp;sid={$surveyid}&amp;createtable=Y")."\" />\n";
            $activateoutput .= "<input type='submit' value='".$clang->gT("No, thanks.")."' onclick=\"".get2post("$link")."\" />\n";
        }
        $activateoutput .= "</div><br />&nbsp;\n";
        $lsrcOutput = true;
    }


    return $activateoutput;

}

function mssql_drop_constraint($fieldname, $tablename)
{
    global $modifyoutput;
    $CI =& get_instance();
    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;

    // find out the name of the default constraint
    // Did I already mention that this is the most suckiest thing I have ever seen in MSSQL database?
    $dfquery ="SELECT c_obj.name AS constraint_name
                FROM  sys.sysobjects AS c_obj INNER JOIN
                      sys.sysobjects AS t_obj ON c_obj.parent_obj = t_obj.id INNER JOIN
                      sys.sysconstraints AS con ON c_obj.id = con.constid INNER JOIN
                      sys.syscolumns AS col ON t_obj.id = col.id AND con.colid = col.colid
                WHERE (c_obj.xtype = 'D') AND (col.name = '$fieldname') AND (t_obj.name='".$CI->db->dbprefix."{$tablename}')";
    $result = db_execute_assoc($dfquery);
    $defaultname=$result->row_array($dfquery);
    if ($defaultname!=false)
    {
        modify_database("","ALTER TABLE [prefix_$tablename] DROP CONSTRAINT {$defaultname[0]}"); echo $modifyoutput; flush();
    }
}


function mssql_drop_primary_index($tablename)
{
    global $dbprefix, $connect, $modifyoutput;
    $CI =& get_instance();
    $CI->load->helper('database');
    $clang = $CI->limesurvey_lang;

    // find out the constraint name of the old primary key
    $pkquery = "SELECT CONSTRAINT_NAME "
              ."FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS "
              ."WHERE     (TABLE_NAME = '".$CI->db->dbprefix."{$tablename}') AND (CONSTRAINT_TYPE = 'PRIMARY KEY')";

    $result = db_execute_assoc($pkquery);
    $primarykey=$result->row_array($pkquery);
    if ($primarykey!=false)
    {
        modify_database("","ALTER TABLE [prefix_{$tablename}] DROP CONSTRAINT {$primarykey}"); echo $modifyoutput; flush();
    }
}
