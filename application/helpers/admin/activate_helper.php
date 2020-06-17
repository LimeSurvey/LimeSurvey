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
        $aSwitcher[] = array("cqid"=>$row['cqid'], "cfieldname"=>$row['cfieldname']);
    }
    if (isset($aSwitcher)) {
        foreach ($aSwitcher as $aSwitch) {
            $sQuery = "UPDATE {{conditions}}
            SET cqid=$iNewQID,
            cfieldname='".str_replace("X".$iQuestionID, "X".$iNewQID, $aSwitch['cfieldname'])."'
            WHERE cqid=$iQuestionID";
            db_execute_assosc($sQuery);
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

    $groupquery = "SELECT 1 as count from ".Yii::app()->db->quoteTableName('{{groups}}')." as g WHERE g.sid=$postsid;";
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
    $groupquery = "SELECT g.gid,g.group_name,count(q.qid) as count from {{questions}} as q RIGHT JOIN ".Yii::app()->db->quoteTableName('{{groups}}')." as g ON q.gid=g.gid AND g.language=q.language WHERE g.sid=$postsid AND g.language='$baselang' group by g.gid,g.group_name;";
    $groupresult = Yii::app()->db->createCommand($groupquery)->query()->readAll();
    foreach ($groupresult as $row) {
//TIBO
        if ($row['count'] == 0) {
            $failedgroupcheck[] = array($row['gid'], $row['group_name'], ": ".gT("This group does not contain any question(s)."));
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
function checkQuestions($postsid, $iSurveyID, $qtypes)
{

    //CHECK TO MAKE SURE ALL QUESTION TYPES THAT REQUIRE ANSWERS HAVE ACTUALLY GOT ANSWERS
    //THESE QUESTION TYPES ARE:
    //    # "L" -> LIST
    //  # "O" -> LIST WITH COMMENT
    //  # "M" -> Multiple choice
    //    # "P" -> Multiple choice with comments
    //    # "A", "B", "C", "E", "F", "H", "^" -> Various Array Types
    //  # "R" -> RANKING
    //  # "U" -> FILE CSV MORE
    //  # "I" -> LANGUAGE SWITCH
    //  # ":" -> Array Multi Flexi Numbers
    //  # ";" -> Array Multi Flexi Text
    //  # "1" -> MULTI SCALE

    $survey = Survey::model()->findByPk($iSurveyID);

    $chkquery = "SELECT qid, question, gid, type FROM {{questions}} WHERE sid={$iSurveyID} and parent_qid=0";
    $chkresult = Yii::app()->db->createCommand($chkquery)->query()->readAll();
    foreach ($chkresult as $chkrow) {
        if ($qtypes[$chkrow['type']]['subquestions'] > 0) {
            for ($i = 0; $i < $qtypes[$chkrow['type']]['subquestions']; $i++) {
                $chaquery = "SELECT * FROM {{questions}} WHERE parent_qid = {$chkrow['qid']} and scale_id={$i} ORDER BY question_order";
                $charesult = Yii::app()->db->createCommand($chaquery)->query()->readAll();
                $chacount = count($charesult);
                if ($chacount == 0) {
                    $failedcheck[] = array($chkrow['qid'], flattenText($chkrow['question'], true, true, 'utf-8', true), ": ".gT("This question has missing subquestions."), $chkrow['gid']);
                }
            }
        }
        if ($qtypes[$chkrow['type']]['answerscales'] > 0) {
            for ($i = 0; $i < $qtypes[$chkrow['type']]['answerscales']; $i++) {
                $chaquery = "SELECT * FROM {{answers}} WHERE qid = {$chkrow['qid']} and scale_id={$i} ORDER BY sortorder, answer";
                $charesult = Yii::app()->db->createCommand($chaquery)->query()->readAll();
                $chacount = count($charesult);
                if ($chacount == 0) {
                    $failedcheck[] = array($chkrow['qid'], flattenText($chkrow['question'], true, true, 'utf-8', true), ": ".gT("This question has missing answer options."), $chkrow['gid']);
                }
            }
        }
    }

    //NOW CHECK THAT ALL QUESTIONS HAVE A 'QUESTION TYPE' FIELD SET
    $chkquery = "SELECT qid, question, gid FROM {{questions}} WHERE sid={$iSurveyID} AND type = ''";
    $chkresult = Yii::app()->db->createCommand($chkquery)->query()->readAll();
    foreach ($chkresult as $chkrow) {
        $failedcheck[] = array($chkrow['qid'], $chkrow['question'], ": ".gT("This question does not have a question 'type' set."), $chkrow['gid']);
    }




    //Check that certain array question types have answers set
    $chkquery = "SELECT q.qid, question, gid FROM {{questions}} as q WHERE (select count(*) from {{answers}} as a where a.qid=q.qid and scale_id=0)=0 and sid={$iSurveyID} AND type IN ('F', 'H', 'W', 'Z', '1') and q.parent_qid=0";
    $chkresult = Yii::app()->db->createCommand($chkquery)->query()->readAll();
    foreach ($chkresult as $chkrow) {
        $failedcheck[] = array($chkrow['qid'], $chkrow['question'], ": ".gT("This question requires answers, but none are set."), $chkrow['gid']);
    } // while

    //CHECK THAT DUAL Array has answers set
    $chkquery = "SELECT q.qid, question, gid FROM {{questions}} as q WHERE (select count(*) from {{answers}} as a where a.qid=q.qid and scale_id=1)=0 and sid={$iSurveyID} AND type='1' and q.parent_qid=0";
    $chkresult = Yii::app()->db->createCommand($chkquery)->query()->readAll();
    foreach ($chkresult as $chkrow) {
        $failedcheck[] = array($chkrow['qid'], $chkrow['question'], ": ".gT("This question requires a second answer set but none is set."), $chkrow['gid']);
    } // while

    //TO AVOID NATURAL SORT ORDER ISSUES, FIRST GET ALL QUESTIONS IN NATURAL SORT ORDER, AND FIND OUT WHICH NUMBER IN THAT ORDER THIS QUESTION IS
    $qorderquery = "SELECT * FROM {{questions}} WHERE sid=$iSurveyID AND type not in ('S', 'D', 'T', 'Q')";
    $qorderresult = Yii::app()->db->createCommand($qorderquery)->query()->readAll();
    $qrows = array(); //Create an empty array in case FetchRow does not return any rows
    foreach ($qorderresult as $qrow) {$qrows[] = $qrow; } // Get table output into array
    usort($qrows, 'groupOrderThenQuestionOrder'); // Perform a case insensitive natural sort on group name then question title of a multidimensional array
    $c = 0;
    foreach ($qrows as $qr) {
        $qidorder[] = array($c, $qrow['qid']);
        $c++;
    }

    $qordercount = "";
    //1: Get each condition's question id
    $conquery = "SELECT {{conditions}}.qid, cqid, {{questions}}.question, "
    . "{{questions}}.gid "
    . "FROM {{conditions}}, {{questions}}, ".Yii::app()->db->quoteTableName('{{groups}}')." g "
    . "WHERE {{questions}}.sid={$iSurveyID} "
    . "AND {{conditions}}.qid={{questions}}.qid "
    . "AND {{questions}}.gid=g.gid ORDER BY {{conditions}}.qid";
    $conresult = Yii::app()->db->createCommand($conquery)->query()->readAll();
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
                $failedcheck[] = array($conrow['qid'], $conrow['question'], ": ".gT("This question has a condition set, however the condition is based on a question that appears after it."), $conrow['gid']);
            }
            $b++;
        }
    }

    //CHECK THAT ALL THE CREATED FIELDS WILL BE UNIQUE
    $aDuplicateQIDs = array();
    $fieldmap = createFieldMap($survey, 'full', true, false, $survey->language, $aDuplicateQIDs);
    if (count($aDuplicateQIDs)) {
        foreach ($aDuplicateQIDs as $iQID=>$aDuplicate) {
            $sFixLink = "[<a href='".Yii::app()->getController()->createUrl("/admin/survey/sa/activate/surveyid/{$iSurveyID}/fixnumbering/{$iQID}")."'>Click here to fix</a>]";
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
* Function to activate a survey
* @param int $iSurveyID The Survey ID
* @param bool $simulate Set to true to test the activation regarding table size limit
* @return array
*/
function activateSurvey($iSurveyID, $simulate = false)
{
    // Event beforeSurveyActivate
    $oSurvey = Survey::model()->findByPk($iSurveyID);
    $event = new PluginEvent('beforeSurveyActivate');
    $event->set('surveyId', $iSurveyID);
    $event->set('simulate', $simulate);
    App()->getPluginManager()->dispatchEvent($event);
    $success = $event->get('success');
    $message = $event->get('message');
    if ($success === false) {
        Yii::app()->user->setFlash('error', $message);
        return array('error' => 'plugin');
    } else if (!empty($message)) {
        Yii::app()->user->setFlash('info', $message);
    }

    $aTableDefinition = array();
    $bCreateSurveyDir = false;
    // Specify case sensitive collations for the token
    $sCollation = '';
    if (Yii::app()->db->driverName == 'mysqli' || Yii::app()->db->driverName == 'mysql') {
        $sCollation = " COLLATE 'utf8mb4_bin'";
    }
    if (Yii::app()->db->driverName == 'sqlsrv' || Yii::app()->db->driverName == 'dblib' || Yii::app()->db->driverName == 'mssql') {
        $sCollation = " COLLATE SQL_Latin1_General_CP1_CS_AS";
    }
    //Check for any additional fields for this survey and create necessary fields (token and datestamp)
    $oSurvey->fixInvalidQuestions();
    //Get list of questions for the base language
    $sFieldMap = createFieldMap($oSurvey, 'full', true, false, $oSurvey->language);
    //For each question, create the appropriate field(s)
    foreach ($sFieldMap as $j=>$aRow) {
        switch ($aRow['type']) {
            case 'seed':
                $aTableDefinition[$aRow['fieldname']] = "string(31)";
                break;
            case 'startlanguage':
                $aTableDefinition[$aRow['fieldname']] = "string(20) NOT NULL";
                break;
            case 'id':
                $aTableDefinition[$aRow['fieldname']] = "pk";
                break;
            case "startdate":
            case "datestamp":
                $aTableDefinition[$aRow['fieldname']] = "datetime NOT NULL";
                break;
            case "submitdate":
                $aTableDefinition[$aRow['fieldname']] = "datetime";
                break;
            case "lastpage":
                $aTableDefinition[$aRow['fieldname']] = "integer";
                break;
            case "N":  //Numerical
            case "K":  //Multiple Numerical
                $aTableDefinition[$aRow['fieldname']] = "decimal (30,10)";
                break;
            case "S":  //SHORT TEXT
                $aTableDefinition[$aRow['fieldname']] = "text";
                break;
            case "L":  //LIST (RADIO)
            case "!":  //LIST (DROPDOWN)
            case "M":  //Multiple choice
            case "P":  //Multiple choice with comment
            case "O":  //DROPDOWN LIST WITH COMMENT
                if ($aRow['aid'] != 'other' && strpos($aRow['aid'], 'comment') === false && strpos($aRow['aid'], 'othercomment') === false) {
                    $aTableDefinition[$aRow['fieldname']] = "string(5)";
                } else {
                    $aTableDefinition[$aRow['fieldname']] = "text";
                }
                break;
            case "U":  //Huge text
            case "Q":  //Multiple short text
            case "T":  //LONG TEXT
            case ";":  //Multi Flexi
            case ":":  //Multi Flexi
                $aTableDefinition[$aRow['fieldname']] = "text";
                break;
            case "D":  //DATE
                $aTableDefinition[$aRow['fieldname']] = "datetime";
                break;
            case "5":  //5 Point Choice
            case "G":  //Gender
            case "Y":  //YesNo
            case "X":  //Boilerplate
                $aTableDefinition[$aRow['fieldname']] = "string(1)";
                break;
            case "I":  //Language switch
                $aTableDefinition[$aRow['fieldname']] = "string(20)";
                break;
            case "|":
                $bCreateSurveyDir = true;
                if (strpos($aRow['fieldname'], "_")) {
                                    $aTableDefinition[$aRow['fieldname']] = "integer";
                } else {
                                    $aTableDefinition[$aRow['fieldname']] = "text";
                }
                break;
            case "ipaddress":
                if ($oSurvey->ipaddr == "Y") {
                                    $aTableDefinition[$aRow['fieldname']] = "text";
                }
                break;
            case "url":
                if ($oSurvey->refurl == "Y") {
                                    $aTableDefinition[$aRow['fieldname']] = "text";
                }
                break;
            case "token":
                $aTableDefinition[$aRow['fieldname']] = 'string(35)'.$sCollation;
                break;
            case '*': // Equation
                $aTableDefinition[$aRow['fieldname']] = "text";
                break;
            case 'R':
                /**
                 * See bug #09828: Ranking question : update allowed can broke Survey DB
                 * If max_subquestions is not set or is invalid : set it to actual answers numbers
                 */

                $nrOfAnswers = Answer::model()->countByAttributes(
                    array('qid' => $aRow['qid'], 'language'=>Survey::model()->findByPk($iSurveyID)->language)
                );
                $oQuestionAttribute = QuestionAttribute::model()->find(
                    "qid = :qid AND attribute = 'max_subquestions'",
                    array(':qid' => $aRow['qid'])
                );
                if (empty($oQuestionAttribute)) {
                    $oQuestionAttribute = new QuestionAttribute();
                    $oQuestionAttribute->qid = $aRow['qid'];
                    $oQuestionAttribute->attribute = 'max_subquestions';
                    $oQuestionAttribute->value = $nrOfAnswers;
                    $oQuestionAttribute->save();
                } elseif (intval($oQuestionAttribute->value) < 1) {
// Fix it if invalid : disallow 0, but need a sub question minimum for EM
                    $oQuestionAttribute->value = $nrOfAnswers;
                    $oQuestionAttribute->save();
                }
                $aTableDefinition[$aRow['fieldname']] = "string(5)";
                break;
            default:
                $aTableDefinition[$aRow['fieldname']] = "string(5)";
        }
        if ($oSurvey->anonymized == 'N' && !array_key_exists('token', $aTableDefinition)) {
            $aTableDefinition['token'] = 'string(35)'.$sCollation;
        }
        if ($simulate) {
            $tempTrim = trim($aTableDefinition);
            $brackets = strpos($tempTrim, "(");
            if ($brackets === false) {
                $type = substr($tempTrim, 0, 2);
            } else {
                $type = substr($tempTrim, 0, 2);
            }
            $arrSim[] = array($type);
        }
    }

    if ($simulate) {
        return array('dbengine'=>Yii::app()->db->getDriverName(), 'dbtype'=>Yii::app()->db->driverName, 'fields'=>$arrSim);
    }

    // If last question is of type MCABCEFHP^QKJR let's get rid of the ending coma in createsurvey

    $sTableName = "{{survey_{$iSurveyID}}}";
    Yii::app()->loadHelper("database");
    try {
        Yii::app()->db->createCommand()->createTable($sTableName, $aTableDefinition);
        Yii::app()->db->schema->getTable($sTableName, true); // Refresh schema cache just in case the table existed in the past
    } catch (CDbException $e) {
        if (App()->getConfig('debug')) {
            return array('error'=>$e->getMessage());
        } else {
            return array('error'=>'surveytablecreation');
        }
    }
    try {
        if (isset($aTableDefinition['token'])) {
            Yii::app()->db->createCommand()->createIndex("idx_survey_token_{$iSurveyID}_".rand(1, 50000), $sTableName, 'token');
        }
    } catch (CDbException $e) {
    }

    $sQuery = "SELECT autonumber_start FROM {{surveys}} WHERE sid={$iSurveyID}";
    $iAutoNumberStart = Yii::app()->db->createCommand($sQuery)->queryScalar();
    //if there is an autonumber_start field, start auto numbering here
    if ($iAutoNumberStart !== false && $iAutoNumberStart > 0) {
        if (Yii::app()->db->driverName == 'mssql' || Yii::app()->db->driverName == 'sqlsrv' || Yii::app()->db->driverName == 'dblib') {
            mssql_drop_primary_index('survey_'.$iSurveyID);
            mssql_drop_constraint('id', 'survey_'.$iSurveyID);
            $sQuery = "ALTER TABLE {{survey_{$iSurveyID}}} drop column id ";
            Yii::app()->db->createCommand($sQuery)->execute();
            $sQuery = "ALTER TABLE {{survey_{$iSurveyID}}} ADD [id] int identity({$iAutoNumberStart},1)";
            Yii::app()->db->createCommand($sQuery)->execute();
            // Add back the primaryKey

            Yii::app()->db->createCommand()->addPrimaryKey('PRIMARY_'.rand(1, 50000), $oSurvey->responsesTableName, 'id');
        } elseif (Yii::app()->db->driverName == 'pgsql') {
            $sQuery = "SELECT setval(pg_get_serial_sequence('{{survey_{$iSurveyID}}}', 'id'),{$iAutoNumberStart},false);";
            @Yii::app()->db->createCommand($sQuery)->execute();
        } else {
            $sQuery = "ALTER TABLE {{survey_{$iSurveyID}}} AUTO_INCREMENT = {$iAutoNumberStart}";
            @Yii::app()->db->createCommand($sQuery)->execute();
        }
    }

    if ($oSurvey->savetimings == "Y") {
        $timingsfieldmap = createTimingsFieldMap($iSurveyID, "full", false, false, $oSurvey->language);

        $aTimingTableDefinition = array();
        $aTimingTableDefinition['id'] = $aTableDefinition['id'];
        foreach ($timingsfieldmap as $field=>$fielddata) {
            $aTimingTableDefinition[$field] = 'FLOAT';
        }

        $sTableName = "{{survey_{$iSurveyID}_timings}}";
        try {
            Yii::app()->db->createCommand()->createTable($sTableName, $aTimingTableDefinition);
            Yii::app()->db->schema->getTable($sTableName, true); // Refresh schema cache just in case the table existed in the past
        } catch (CDbException $e) {
            return array('error'=>'timingstablecreation');
        }

    }
    $aResult = array(
        'status' => 'OK',
        'pluginFeedback' => $event->get('pluginFeedback')
    );
    // create the survey directory where the uploaded files can be saved
    if ($bCreateSurveyDir) {
        if (!file_exists(Yii::app()->getConfig('uploaddir')."/surveys/".$iSurveyID."/files")) {
            if (!(mkdir(Yii::app()->getConfig('uploaddir')."/surveys/".$iSurveyID."/files", 0777, true))) {
                $aResult['warning'] = 'nouploadsurveydir';
            } else {
                file_put_contents(Yii::app()->getConfig('uploaddir')."/surveys/".$iSurveyID."/files/index.html", '<html><head></head><body></body></html>');
            }
        }
    }
    $sQuery = "UPDATE {{surveys}} SET active='Y' WHERE sid=".$iSurveyID;
    Yii::app()->db->createCommand($sQuery)->query();
    LimeExpressionManager::SetDirtyFlag();

    $event = new PluginEvent('afterSurveyActivate');
    $event->set('surveyId', $iSurveyID);
    $event->set('simulate', $simulate);
    App()->getPluginManager()->dispatchEvent($event);

    return $aResult;
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
    WHERE (c_obj.xtype = 'D') AND (col.name = '$fieldname') AND (t_obj.name='{{{$tablename}}}')";
    $result = dbExecuteAssoc($dfquery)->read();
    $defaultname = $result['CONTRAINT_NAME'];
    if ($defaultname != false) {
        modifyDatabase("", "ALTER TABLE {{{$tablename}}} DROP CONSTRAINT {$defaultname[0]}"); echo $modifyoutput; flush();
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
    ."FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS "
    ."WHERE     (TABLE_NAME = '{{{$tablename}}}') AND (CONSTRAINT_TYPE = 'PRIMARY KEY')";

    $primarykey = Yii::app()->db->createCommand($pkquery)->queryRow(false);
    if ($primarykey !== false) {
        Yii::app()->db->createCommand("ALTER TABLE {{{$tablename}}} DROP CONSTRAINT {$primarykey[0]}")->execute();
    }
}
