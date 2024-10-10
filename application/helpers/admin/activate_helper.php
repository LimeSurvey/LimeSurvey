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
*/

/**
* fixes the numbering of questions
* This can happen if question 1 have subquestion code 1 and have question 11 in same survey and group (then same SGQA)
* @param int $fixnumbering
* @param integer $iSurveyID
* @todo can call this function (no $_GET, but getParam) AND do it with Yii
*/
function fixNumbering($iQuestionID, $iSurveyID)
{
    Yii::app()->loadHelper("database");

    LimeExpressionManager::RevertUpgradeConditionsToRelevance($iSurveyID);
    //Fix a question id - requires renumbering a question
    $iQuestionID = (int) $iQuestionID;
    $iMaxQID = Question::model()->getMaxId('qid', true); // Always refresh as we insert new qid's
    $iNewQID = $iMaxQID + 1;

    // Not sure we can do this in MSSQL ?
    $sQuery = "UPDATE {{questions}} SET qid=$iNewQID WHERE qid=$iQuestionID";
    Yii::app()->db->createCommand($sQuery)->query();
    // Update subquestions
    $sQuery = "UPDATE {{questions}} SET parent_qid=$iNewQID WHERE parent_qid=$iQuestionID";
    Yii::app()->db->createCommand($sQuery)->query();
    //Update conditions.. firstly conditions FOR this question
    $sQuery = "UPDATE {{conditions}} SET qid=$iNewQID WHERE qid=$iQuestionID";
    Yii::app()->db->createCommand($sQuery)->query();
    //Update default values
    $sQuery = "UPDATE {{defaultvalues}} SET qid=$iNewQID WHERE qid=$iQuestionID";
    Yii::app()->db->createCommand($sQuery)->query();
    $sQuery = "UPDATE {{defaultvalues}} SET sqid=$iNewQID WHERE sqid=$iQuestionID";
    Yii::app()->db->createCommand($sQuery)->query();
    //Update quotas
    $sQuery = "UPDATE {{quota_members}} SET qid=$iNewQID WHERE qid=$iQuestionID";
    Yii::app()->db->createCommand($sQuery)->query();
    //Update url params
    $sQuery = "UPDATE {{survey_url_parameters}} SET targetqid=$iNewQID WHERE targetqid=$iQuestionID";
    Yii::app()->db->createCommand($sQuery)->query();
    $sQuery = "UPDATE {{survey_url_parameters}} SET targetsqid=$iNewQID WHERE targetsqid=$iQuestionID";
    Yii::app()->db->createCommand($sQuery)->query();
    //Now conditions based upon this question
    $sQuery = "SELECT cqid, cfieldname FROM {{conditions}} WHERE cqid=$iQuestionID";
    $sResult = Yii::app()->db->createCommand($sQuery)->query();
    foreach ($sResult->readAll() as $row) {
        $aSwitcher[] = array("cqid" => $row['cqid'], "cfieldname" => $row['cfieldname']);
    }
    if (isset($aSwitcher)) {
        foreach ($aSwitcher as $aSwitch) {
            $sQuery = "UPDATE {{conditions}}
            SET cqid=$iNewQID,
            cfieldname='" . str_replace("X" . $iQuestionID, "X" . $iNewQID, (string) $aSwitch['cfieldname']) . "'
            WHERE cqid=$iQuestionID";
            Yii::app()->db->createCommand($sQuery)->query();
        }
    }
    //Now question_attributes
    $sQuery = "UPDATE {{question_attributes}} SET qid=$iNewQID WHERE qid=$iQuestionID";
    Yii::app()->db->createCommand($sQuery)->query();
    //Now answers
    $sQuery = "UPDATE {{answers}} SET qid=$iNewQID WHERE qid=$iQuestionID";
    Yii::app()->db->createCommand($sQuery)->query();

    LimeExpressionManager::UpgradeConditionsToRelevance($iSurveyID);
}
/**
* checks if any group exists
* @param integer $postsid
* @return <type>
*/
function checkHasGroup($postsid)
{
    $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
    $groupquery = "SELECT 1 as count from $quotedGroups as g WHERE g.sid=$postsid;";
    $groupresult = Yii::app()->db->createCommand($groupquery)->query()->readAll();

    if (count($groupresult) == 0) {
        return gT("This survey does not contain any question groups.");
    } else {
        return false;
    }
}
/**
* checks consistency of groups
* @param integer $postsid
* @return <type>
*/
function checkGroup($postsid)
{
    $baselang = Survey::model()->findByPk($postsid)->language;
    $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
    $groupquery = "SELECT g.gid,ls.group_name,count(q.qid) as count from {{questions}} as q 
                   RIGHT JOIN $quotedGroups as g ON q.gid=g.gid 
                   join {{group_l10ns}} ls on g.gid=ls.gid
                   WHERE g.sid=$postsid AND ls.language='$baselang' group by g.gid,ls.group_name;";
    $groupresult = Yii::app()->db->createCommand($groupquery)->query()->readAll();
    foreach ($groupresult as $row) {
        //TIBO
        if ($row['count'] == 0) {
            $failedgroupcheck[] = array($row['gid'], $row['group_name'], ": " . gT("This group does not contain any question(s)."));
        }
    }
    if (isset($failedgroupcheck)) {
        return $failedgroupcheck;
    } else {
        return false;
    }
}
/**
* checks questions in a survey for consistency
* @param integer $postsid
* @param integer $iSurveyID
* @return array|bool $faildcheck
*/
function checkQuestions($postsid, $iSurveyID)
{

    //CHECK TO MAKE SURE ALL QUESTION TYPES THAT REQUIRE ANSWERS HAVE ACTUALLY GOT ANSWERS
    //THESE QUESTION TYPES ARE:
    //    # "L" -> LIST
    //  # "O" -> LIST WITH COMMENT
    //  # "M" -> Multiple choice
    //    # "P" -> Multiple choice with comments
    //    # "A", "B", "C", "E", "F", "H" -> Various Array Types
    //  # "R" -> RANKING
    //  # "U" -> FILE CSV MORE
    //  # "I" -> LANGUAGE SWITCH
    //  # ":" -> Array Multi Flexi Numbers
    //  # ";" -> Array Multi Flexi Text
    //  # "1" -> Dual scale
    $questionTypesMetaData = QuestionTheme::findQuestionMetaDataForAllTypes();

    $survey = Survey::model()->findByPk($iSurveyID);
    $oDB = Yii::app()->db;

    $chkquery = $oDB->createCommand()
        ->select(['q.qid', 'ls.question', 'gid', 'type'])
        ->from('{{questions}} q')
        ->join('{{question_l10ns}} ls', 'ls.qid=q.qid')
        ->where('sid=:sid and parent_qid=0', [':sid' => $iSurveyID]);

    $chkresult = $chkquery->queryAll();

    foreach ($chkresult as $chkrow) {
        if ((int)$questionTypesMetaData[$chkrow['type']]['settings']->subquestions > 0) {
            for ($i = 0; $i < (int)$questionTypesMetaData[$chkrow['type']]['settings']->subquestions; $i++) {
                $chaquery = Yii::app()->db->createCommand()
                    ->select('COUNT(qid)')
                    ->from('{{questions}}')
                    ->where('parent_qid = :qid and scale_id=:scaleid', [':qid' => $chkrow['qid'], ':scaleid' => $i]);
                $chacount = $chaquery->queryScalar();
                if ($chacount == 0) {
                    $failedcheck[] = array($chkrow['qid'], flattenText($chkrow['question'], true, true, 'utf-8', true), ": " . gT("This question has missing subquestions."), $chkrow['gid']);
                }
            }
        }
        if ((int)$questionTypesMetaData[$chkrow['type']]['settings']->answerscales > 0) {
            for ($i = 0; $i < (int)$questionTypesMetaData[$chkrow['type']]['settings']->answerscales; $i++) {
                $chaquery = Yii::app()->db->createCommand()
                    ->select('COUNT(aid)')
                    ->from('{{answers}}')
                    ->where('qid = :qid and scale_id=:scaleid', [':qid' => $chkrow['qid'], ':scaleid' => $i]);
                $chacount = $chaquery->queryScalar();
                if ($chacount == 0) {
                    $failedcheck[] = array($chkrow['qid'], flattenText($chkrow['question'], true, true, 'utf-8', true), ": " . gT("This question has missing answer options."), $chkrow['gid']);
                }
            }
        }
    }
    unset($chkrow);

    //NOW CHECK THAT ALL QUESTIONS HAVE A 'QUESTION TYPE' FIELD SET
    $chkquery = Yii::app()->db->createCommand()
        ->select(['q.qid', 'ls.question', 'gid'])
        ->from('{{questions}} q')
        ->join('{{question_l10ns}} ls', 'ls.qid=q.qid')
        ->where("sid=:sid AND type = ''", [':sid' => $iSurveyID]);
    $chkresult = $chkquery->queryAll();
    foreach ($chkresult as $chkrow) {
        $failedcheck[] = array($chkrow['qid'], $chkrow['question'], ": " . gT("This question does not have a question 'type' set."), $chkrow['gid']);
    }


    //Check that certain array question types have answers set
    $chkquery = Yii::app()->db->createCommand()
        ->select(['q.qid', 'ls.question', 'gid'])
        ->from('{{questions}} q')
        ->join('{{question_l10ns}} ls', 'ls.qid=q.qid')
        ->andWhere("(SELECT count(*) from {{answers}} as a where a.qid=q.qid and scale_id=0)=0")
        ->andWhere("sid=:sid", [':sid' => $iSurveyID])
        ->andWhere("type IN ('" . Question::QT_F_ARRAY . "', '" . Question::QT_H_ARRAY_COLUMN . "', '" . Question::QT_1_ARRAY_DUAL . "')")
        ->andWhere("q.parent_qid=0");
    $chkresult = $chkquery->queryAll();
    foreach ($chkresult as $chkrow) {
        $failedcheck[] = array($chkrow['qid'], $chkrow['question'], ": " . gT("This question requires answers, but none are set."), $chkrow['gid']);
    } // while

    //CHECK THAT DUAL Array has answers set
    $chkquery = Yii::app()->db->createCommand()
    ->select(['q.qid', 'ls.question', 'gid'])
    ->from('{{questions}} q')
    ->join('{{question_l10ns}} ls', 'ls.qid=q.qid')
    ->andWhere("(Select count(*) from {{answers}} a where a.qid=q.qid and scale_id=1)=0")
    ->andWhere("sid=:sid", [':sid' => $iSurveyID])
    ->andWhere("type='" . Question::QT_1_ARRAY_DUAL . "'")
    ->andWhere("q.parent_qid=0");
    $chkresult = $chkquery->queryAll();
    foreach ($chkresult as $chkrow) {
        $failedcheck[] = array($chkrow['qid'], $chkrow['question'], ": " . gT("This question requires a second answer set but none is set."), $chkrow['gid']);
    } // while

    //TO AVOID NATURAL SORT ORDER ISSUES, FIRST GET ALL QUESTIONS IN NATURAL SORT ORDER, AND FIND OUT WHICH NUMBER IN THAT ORDER THIS QUESTION IS
    $qorderquery = "SELECT * FROM {{questions}} WHERE sid=$iSurveyID AND type not in ('" . Question::QT_S_SHORT_FREE_TEXT . "', '" . Question::QT_D_DATE . "', '" . Question::QT_T_LONG_FREE_TEXT . "', '" . Question::QT_Q_MULTIPLE_SHORT_TEXT . "')";
    $qorderresult = Yii::app()->db->createCommand($qorderquery)->query()->readAll();
    $qrows = array(); //Create an empty array in case FetchRow does not return any rows
    foreach ($qorderresult as $qrow) {
        $qrows[] = $qrow;
    } // Get table output into array
    usort($qrows, 'groupOrderThenQuestionOrder'); // Perform a case insensitive natural sort on group name then question title of a multidimensional array
    $c = 0;
    foreach ($qrows as $qr) {
        $qidorder[] = array($c, $qrow['qid']);
        $c++;
    }

    $qordercount = "";
    //1: Get each condition's question id
    $conquery = Yii::app()->db->createCommand()
    ->select(['cndn.qid', 'cqid', 'ls.question', 'q.gid'])
    ->from('{{conditions}} cndn')
    ->join('{{questions}} q', 'cndn.qid=q.qid')
    ->join('{{question_l10ns}} ls', 'ls.qid=q.qid')
    ->andWhere('q.sid=:sid', [':sid' => $iSurveyID])
    ->andWhere('ls.language=:lngn', [':lngn' => $survey->language])
    ->order('cndn.qid');

    $conresult = $conquery->queryAll();
    //2: Check each conditions cqid that it occurs later than the cqid
    foreach ($conresult as $conrow) {
        $cqidfound = 0;
        $qidfound = 0;
        $b = 0;
        while ($b < $qordercount) {
            if ($conrow['cqid'] == $qidorder[$b][1]) {
                $cqidfound = 1;
                $b = $qordercount;
            }
            if ($conrow['qid'] == $qidorder[$b][1]) {
                $qidfound = 1;
                $b = $qordercount;
            }
            if ($qidfound == 1) {
                $failedcheck[] = array($conrow['qid'], $conrow['question'], ": " . gT("This question has a condition set, however the condition is based on a question that appears after it."), $conrow['gid']);
            }
            $b++;
        }
    }

    //CHECK THAT ALL THE CREATED FIELDS WILL BE UNIQUE
    $aDuplicateQIDs = array();
    $fieldmap = createFieldMap($survey, 'full', true, false, $survey->language, $aDuplicateQIDs);
    if (count($aDuplicateQIDs)) {
        foreach ($aDuplicateQIDs as $iQID => $aDuplicate) {
            $sFixLink = "[<a class='selector__fixConsistencyProblem'
            href='" . Yii::app()->getController()->createUrl("/surveyAdministration/fixNumbering/iSurveyID/{$iSurveyID}/questionId/{$iQID}") . "'>Click here to fix</a>]";
            $failedcheck[] = array($iQID, $aDuplicate['question'], ": Bad duplicate fieldname {$sFixLink}", $aDuplicate['gid']);
        }
    }
    if (isset($failedcheck)) {
        return $failedcheck;
    } else {
        return false;
    }
}

/**
 * @param string $fieldname
 * @param string $tablename
 */
function mssql_drop_constraint($fieldname, $tablename)
{
    global $modifyoutput;
    Yii::app()->loadHelper("database");

    // find out the name of the default constraint
    // Did I already mention that this is the most suckiest thing I have ever seen in MSSQL database?
    $dfquery = "SELECT c_obj.name AS constraint_name
    FROM  sys.sysobjects AS c_obj INNER JOIN
    sys.sysobjects AS t_obj ON c_obj.parent_obj = t_obj.id INNER JOIN
    sys.sysconstraints AS con ON c_obj.id = con.constid INNER JOIN
    sys.syscolumns AS col ON t_obj.id = col.id AND con.colid = col.colid
    WHERE (c_obj.xtype = 'D') AND (col.name = '{$fieldname}') AND (t_obj.name='{{{$tablename}}}')";
    $result = Yii::app()->db->createCommand($dfquery)->query();
    $result = $result->read();
    if (!empty($result['CONSTRAINT_NAME'])) {
        $defaultname = $result['CONSTRAINT_NAME'];
        modifyDatabase("", "ALTER TABLE {{{$tablename}}} DROP CONSTRAINT {$defaultname[0]}");
        echo $modifyoutput;
        flush();
    }
}


/**
 * @param string $tablename
 */
function mssql_drop_primary_index($tablename)
{
    Yii::app()->loadHelper("database");

    // find out the constraint name of the old primary key
    $pkquery = "SELECT CONSTRAINT_NAME "
    . "FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS "
    . "WHERE     (TABLE_NAME = '{{{$tablename}}}') AND (CONSTRAINT_TYPE = 'PRIMARY KEY')";

    $primarykey = Yii::app()->db->createCommand($pkquery)->queryRow(false);
    if ($primarykey !== false) {
        Yii::app()->db->createCommand("ALTER TABLE {{{$tablename}}} DROP CONSTRAINT {$primarykey[0]}")->execute();
    }
}

/**
 * Deletes a column and removes all constraints from it
 *
 * @param string $tablename The table the column should be deleted
 * @param string $columnname The column that should be deleted
 */
function mssql_drop_column_with_constraints($tablename, $columnname)
{
    Yii::app()->loadHelper("database");

    // find out the constraint name of the old primary key
    $pkquery = "SELECT constraint_name
    FROM information_schema.constraint_column_usage
    WHERE table_name = '" . $tablename . "' AND column_name = '" . $columnname . "'";

    $result = Yii::app()->db->createCommand($pkquery)->queryColumn();
    foreach ($result as $constraintName) {
        Yii::app()->db->createCommand('ALTER TABLE [' . $tablename . '] DROP CONSTRAINT "' . $constraintName . '"')->execute();
    }
    $success = Yii::app()->db->createCommand('ALTER TABLE [' . $tablename . '] DROP COLUMN "' . $columnname . '"')->execute();
    return $success;
}
