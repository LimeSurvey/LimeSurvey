<?php
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *  $Id$
 */

/**
 * fixes the numbering of questions
 * @param <type> $fixnumbering
 */
function fixNumbering($fixnumbering, $iSurveyID)
{

    Yii::app()->loadHelper("database");

    LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyID);
     //Fix a question id - requires renumbering a question
    $oldqid = $fixnumbering;
    $query = "SELECT qid FROM {{questions}} ORDER BY qid DESC";
    $result = dbSelectLimitAssoc($query, 1);
    foreach ($result->readAll() as $row) {$lastqid=$row['qid'];}
    $newqid=$lastqid+1;
    $query = "UPDATE {{questions}} SET qid=$newqid WHERE qid=$oldqid";
    $result = db_execute_assosc($query);
    // Update subquestions
    $query = "UPDATE {{questions}} SET parent_qid=$newqid WHERE parent_qid=$oldqid";
    $result = db_execute_assosc($query);
    //Update conditions.. firstly conditions FOR this question
    $query = "UPDATE {{conditions}} SET qid=$newqid WHERE qid=$oldqid";
    $result = db_execute_assosc($query);
    //Now conditions based upon this question
    $query = "SELECT cqid, cfieldname FROM {{conditions}} WHERE cqid=$oldqid";
    $result = dbExecuteAssoc($query);
    foreach ($result->readAll() as $row)
    {
        $switcher[]=array("cqid"=>$row['cqid'], "cfieldname"=>$row['cfieldname']);
    }
    if (isset($switcher))
    {
        foreach ($switcher as $switch)
        {
            $query = "UPDATE {{conditions}}
                                              SET cqid=$newqid,
                                              cfieldname='".str_replace("X".$oldqid, "X".$newqid, $switch['cfieldname'])."'
                                              WHERE cqid=$oldqid";
            $result = db_execute_assosc($query);
        }
    }
    // TMSW Conditions->Relevance:  (1) Call LEM->ConvertConditionsToRelevance()when done. (2) Should relevance for old conditions be removed first?
    //Now question_attributes
    $query = "UPDATE {{question_attributes}} SET qid=$newqid WHERE qid=$oldqid";
    $result = db_execute_assosc($query);
    //Now answers
    $query = "UPDATE {{answers}} SET qid=$newqid WHERE qid=$oldqid";
    $result = db_execute_assosc($query);

    LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyID);
}
/**
 * checks consistency of groups
 * @return <type>
 */
function checkGroup($postsid)
{
    $clang = Yii::app()->lang;

    $baselang = Survey::model()->findByPk($postsid)->language;
    $groupquery = "SELECT g.gid,g.group_name,count(q.qid) as count from {{questions}} as q RIGHT JOIN {{groups}} as g ON q.gid=g.gid AND g.language=q.language WHERE g.sid=$postsid AND g.language='$baselang' group by g.gid,g.group_name;";
    $groupresult=Yii::app()->db->createCommand($groupquery)->query()->readAll();
    foreach ($groupresult as $row)
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
* @param <type> $iSurveyID
 * @return array $faildcheck
 */
function checkQuestions($postsid, $iSurveyID)
{
    $clang = Yii::app()->lang;

    //CHECK TO MAKE SURE ALL QUESTION TYPES THAT REQUIRE ANSWERS HAVE ACTUALLY GOT ANSWERS

    $chkresult = Questions::model()->with('question_types')->with('groups')->findAllByAttributes(array('sid' => $iSurveyID, 'parent_qid' => 0), array('order' => 'group_order, question_order'));

    foreach ($chkresult as $chkrow)
    {
        if ($chkrow['tid'] == 0)
        {
            $failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question does not have a question 'tid' set."), $chkrow['gid']);
            continue;
        }

        $q = createQuestion($chkrow->question_types['class']);
        $qproperties=$q->questionProperties();
        if ($qproperties['subquestions'] > 0)
        {
            $charesult = Questions::model()->findAllByAttributes(array('sid' => $iSurveyID, 'parent_qid' => $chkrow['qid']));
            $chacount = count($charesult);
            if ($chacount == 0)
            {
                $failedcheck[] = array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question has no subquestions."), $chkrow['gid']);
            }
        }

        if ($qproperties['answerscales'] > 0)
        {
            $charesult = Answers::model()->findAllByAttributes(array('qid' => $chkrow['qid'], 'scale_id' => 0));
            $chacount = count($charesult);
            if ($chacount == 0)
            {
                $failedcheck[] = array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question has no answers."), $chkrow['gid']);
            }
        }

        if ($qproperties['answerscales']>1)
        {
            $charesult = Answers::model()->findAllByAttributes(array('qid' => $chkrow['qid'], 'scale_id' => 1));
            $chacount = count($charesult);
            if ($chacount == 0)
            {
                $failedcheck[] = array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question has no answers."), $chkrow['gid']);
            }
        }

        $conresult = Conditions::model()->with('questions')->with('groups')->findAllByAttributes(array('qid' => $chkrow['qid']));
        foreach ($conresult as $conrow)
        {
            if ($conrow->groups['group_order'] > $chkrow->groups['group_order'] ||
                ($conrow->groups['group_order'] == $chkrow->groups['group_order'] &&
                $conrow->questions['question_order'] >= $chkrow['question_order']))
            {
                $failedcheck[]=array($chkrow['qid'], $chkrow['question'], ": ".$clang->gT("This question has a condition set, however the condition is based on a question that appears after it."), $chkrow['gid']);
            }
        }
    }

    //CHECK THAT ALL THE CREATED FIELDS WILL BE UNIQUE
    $fieldmap = createFieldMap($iSurveyID,false,false,getBaseLanguageFromSurveyID($iSurveyID));
    if (isset($fieldmap))
    {
        foreach($fieldmap as $q)
        {
            $fieldlist[]=$q->fieldname;
        }
        $fieldlist=array_reverse($fieldlist); //let's always change the later duplicate, not the earlier one
    }

    $checkKeysUniqueComparison = create_function('$value','if ($value > 1) return true;');
    @$duplicates = array_keys (array_filter (array_count_values($fieldlist), $checkKeysUniqueComparison));
    if (isset($duplicates))
    {
        foreach ($duplicates as $dup)
        {
            $q = $fieldmap[$dup];
            $fix = "[<a href='$scriptname?action=activate&amp;sid=$iSurveyID&amp;fixnumbering=".$q->id."'>Click Here to Fix</a>]";
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
* @param int $iSurveyID The Survey ID
 * @param bool $simulate
 * @return string
 */


function activateSurvey($iSurveyID, $simulate = false)
{


    $createsurvey='';
    $activateoutput='';
    $createsurveytimings='';
    $fieldstiming = array();
    $createsurveydirectory=false;
    //Check for any additional fields for this survey and create necessary fields (token and datestamp)
    $prow = Survey::model()->findByAttributes(array('sid' => $iSurveyID));

    //Get list of questions for the base language
    $fieldmap = createFieldMap($iSurveyID,true,false,getBaseLanguageFromSurveyID($iSurveyID));
    
    $createsurvey = array();
    
    foreach ($fieldmap as $q) //With each question, create the appropriate field(s)
    {
        switch($q->fieldname)
        {
            case 'startlanguage':
                $createsurvey[$q->fieldname] = "VARCHAR(20) NOT NULL";
                break;
            case 'id':
                $createsurvey[$q->fieldname] = "pk";
                break;
            case "startdate":
            case "datestamp":
                $createsurvey[$q->fieldname] = "datetime NOT NULL";
                break;
            case "submitdate":
                $createsurvey[$q->fieldname] = "datetime";
                break;
            case "lastpage":
                $createsurvey[$q->fieldname] = "integer";
                break;
            case "ipaddr":
                if ($prow->ipaddr == "Y")
                    $createsurvey[$q->fieldname] = "text";
                break;
            case "refurl":
                if ($prow->refurl == "Y")
                    $createsurvey[$q->fieldname] = "text";
                break;
            case "token":
                if ($prow->anonymized == "N")
                {
                    $createsurvey[$q->fieldname] = "VARCHAR(36)";
                }
                break;
            default:
                $createsurvey[$q->fieldname] = $q->getDBField();
        }
        if ($prow->anonymized == 'N'  && !array_key_exists('token',$createsurvey)) {
            $createsurvey['token'] = "VARCHAR(36)";
        }
        if (is_a($q, 'QuestionModule') && $q->fileUpload()) $createsurveydirectory = true;

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

    }

    if ($simulate){
        return array('dbengine'=>$CI->db->databasetabletype, 'dbtype'=>Yii::app()->db->driverName, 'fields'=>$arrSim);
    }


    // If last question is of type MCABCEFHP^QKJR let's get rid of the ending coma in createsurvey
    //$createsurvey = rtrim($createsurvey, ",\n")."\n"; // Does nothing if not ending with a comma

    $tabname = "{{survey_{$iSurveyID}}}";
    Yii::app()->loadHelper("database");
    try
    {
        $execresult = createTable($tabname, $createsurvey);
    }
    catch (CDbException $e)
    {
        return array('error'=>'surveytablecreation');
    }
    try
    {
        if (isset($createsurvey['token'])) Yii::app()->db->createCommand()->createIndex("idx_survey_token_{$iSurveyID}_".rand(1,50000),$tabname,'token');
    }
    catch (CDbException $e)
    {
    }
    
    $anquery = "SELECT autonumber_start FROM {{surveys}} WHERE sid={$iSurveyID}";
        if ($anresult=Yii::app()->db->createCommand($anquery)->query()->readAll())
        {
            //if there is an autonumber_start field, start auto numbering here
            foreach($anresult as $row)
            {
                if ($row['autonumber_start'] > 0)
                {
                    if (Yii::app()->db->driverName=='mssql' || Yii::app()->db->driverName=='sqlsrv') {
                    mssql_drop_primary_index('survey_'.$iSurveyID);
                    mssql_drop_constraint('id','survey_'.$iSurveyID);
                    $autonumberquery = "alter table {{survey_{$iSurveyID}}} drop column id ";
                    Yii::app()->db->createCommand($autonumberquery)->execute();
                    $autonumberquery = "alter table {{survey_{$iSurveyID}}} add [id] int identity({$row['autonumber_start']},1)";
                    Yii::app()->db->createCommand($autonumberquery)->execute();
                    }
                elseif (Yii::app()->db->driverName=='pgsql')
                {
                    
                }
                    else
                    {
                    $autonumberquery = "ALTER TABLE {{survey_{$iSurveyID}}} AUTO_INCREMENT = ".$row['autonumber_start'];
                        $result = @Yii::app()->db->createCommand($autonumberquery)->execute();
                    }
                }
            }
        }

    if ($prow->savetimings == "Y")
        {
            $timingsfieldmap = createFieldMap($iSurveyID,false,false,getBaseLanguageFromSurveyID($iSurveyID));

            $column['id'] = $createsurvey['id'];
            $column['interviewtime'] = 'FLOAT';
            foreach ($timingsfieldmap as $q)
            {
                if (!empty($q->gid)) {
                    // field for time spent on page
                    $column["{$q->surveyid}X{$q->gid}time"]='FLOAT';

                    // field for time spent on answering a question
                    $column["{$q->surveyid}X{$q->gid}X{$q->id}time"]='FLOAT';
                }
            }

        $tabname = "{{survey_{$iSurveyID}}}_timings";
        try
        {
            $execresult = createTable($tabname,$column);
        }
        catch (CDbException $e)
        {
            return array('error'=>'timingstablecreation');
            }

        }
    $aResult=array('status'=>'OK');
        // create the survey directory where the uploaded files can be saved
        if ($createsurveydirectory)
        {
        if (!file_exists(Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyID . "/files"))
            {
            if (!(mkdir(Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyID . "/files", 0777, true)))
                {
                $aResult['warning']='nouploadsurveydir';
                } else {
                file_put_contents(Yii::app()->getConfig('uploaddir') . "/surveys/" . $iSurveyID . "/files/index.html", '<html><head></head><body></body></html>');
                }
            }
        }
    $acquery = "UPDATE {{surveys}} SET active='Y' WHERE sid=".$iSurveyID;
        $acresult = Yii::app()->db->createCommand($acquery)->query();

    return $aResult;
        }

function mssql_drop_constraint($fieldname, $tablename)
{
    global $modifyoutput;
    Yii::app()->loadHelper("database");

    // find out the name of the default constraint
    // Did I already mention that this is the most suckiest thing I have ever seen in MSSQL database?
    $dfquery ="SELECT c_obj.name AS constraint_name
                FROM  sys.sysobjects AS c_obj INNER JOIN
                      sys.sysobjects AS t_obj ON c_obj.parent_obj = t_obj.id INNER JOIN
                      sys.sysconstraints AS con ON c_obj.id = con.constid INNER JOIN
                      sys.syscolumns AS col ON t_obj.id = col.id AND con.colid = col.colid
    WHERE (c_obj.xtype = 'D') AND (col.name = '$fieldname') AND (t_obj.name='{{{$tablename}}}')";
    $result = dbExecuteAssoc($dfquery)->read();
    $defaultname=$result['CONTRAINT_NAME'];
    if ($defaultname!=false)
    {
        modifyDatabase("","ALTER TABLE {{{$tablename}}} DROP CONSTRAINT {$defaultname[0]}"); echo $modifyoutput; flush();
    }
}


function mssql_drop_primary_index($tablename)
{
    global $modifyoutput;
    Yii::app()->loadHelper("database");

    // find out the constraint name of the old primary key
    $pkquery = "SELECT CONSTRAINT_NAME "
              ."FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS "
    ."WHERE     (TABLE_NAME = '{{{$tablename}}}') AND (CONSTRAINT_TYPE = 'PRIMARY KEY')";

    $primarykey = Yii::app()->db->createCommand($pkquery)->queryRow(false);
    if ($primarykey!==false)
    {
        Yii::app()->db->createCommand("ALTER TABLE {{{$tablename}}} DROP CONSTRAINT {$primarykey[0]}")->execute();
    }
}
