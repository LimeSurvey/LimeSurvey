<?php

namespace LimeSurvey\Models\Services;

use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LSYii_Application;
use Permission;
use Survey;
use Response;

class SurveyCondition
{
    private LSYii_Application $app;
    private Permission $permission;
    private Survey $survey;
    protected int $iSurveyID;
    protected bool $tokenTableExists;
    protected array $tokenFieldsAndNames;
    protected string $language;
    protected const X = 'X';

    /**
     * Sets the iSurveyID member of this object
     * @param int $iSurveyID
     * @return static
     */
    public function setISurveyID(int $iSurveyID)
    {
        $this->iSurveyID = $iSurveyID;
        return $this;
    }

    /**
     * Gets the survey table by name and id
     * @param string $name
     * @param int $id
     * @return string
     */
    public function getSurveyTable(string $name, int $id)
    {
        switch ($name) {
            case 'token':
                return "{{tokens_$id}}";
            default:
                return '';
        }
    }

    /**
     * Gets the question's id from the field's name
     * @param string $copyc
     * @return string
     */
    public function getQIDFromFieldName(string $copyc)
    {
        list(,, $newqid) = explode("X", (string) $copyc);
        return $newqid;
    }

    /**
     * Gets the field's name by sid, gid, qid and title
     * @param mixed $sid
     * @param mixed $gid
     * @param mixed $qid
     * @param mixed $title
     * @return string
     */
    public function getFieldName($sid, $gid, $qid, $title = '')
    {
        return $sid . self::X . $gid . self::X . $qid . $title;
    }

    public function __construct(
        LSYii_Application $app,
        Permission $permission,
        Survey $survey
    ) {
        $this->app = $app;
        $this->permission = $permission;
        $this->survey = $survey;
    }

    /**
     * Initializing the service based on a received array that contains:
     * - iSurveyID
     * @param array $params
     * @return array
     */
    public function initialize($params)
    {
        $this->iSurveyID = $params['iSurveyID'];
        $this->tokenTableExists = tableExists($this->getSurveyTable('token', $this->iSurveyID));
        $this->tokenFieldsAndNames = getTokenFieldsAndNames($this->iSurveyID);
        $this->app->loadHelper("database");
        return [
        $this->iSurveyID,
        $this->tokenTableExists,
        $this->tokenFieldsAndNames,
        ];
    }

    /**
     * resetSurveyLogic action
     * @return void
     */
    public function resetSurveyLogic()
    {
        \LimeExpressionManager::RevertUpgradeConditionsToRelevance($this->iSurveyID);
        \Condition::model()->deleteRecords("qid in (select qid from {{questions}} where sid={$this->iSurveyID})");
    }

    /**
     * insertCondition action
     * @param array $args the arguments
     * @param mixed $editSourceTab the source tab
     * @param mixed $editTargetTab the target tab
     * @param callable $write
     * @param mixed $ConditionConst
     * @param mixed $prevQuestionSGQA
     * @param mixed $tokenAttr
     * @param mixed $ConditionRegexp
     * @return void
     */
    public function insertCondition(array $args, $editSourceTab, $editTargetTab, callable $write, $ConditionConst, $prevQuestionSGQA, $tokenAttr, $ConditionRegexp)
    {
        extract($args);
        if (isset($p_cquestions) && $p_cquestions != '' && $editSourceTab == '#SRCPREVQUEST') {
            $conditionCfieldname = $p_cquestions;
        } elseif (isset($p_csrctoken) && $p_csrctoken != '') {
            $conditionCfieldname = $p_csrctoken;
        }

        $condition_data = array(
        'qid'        => $qid,
        'scenario'   => $p_scenario,
        'cqid'       => $p_cqid,
        'cfieldname' => $conditionCfieldname,
        'method'     => $p_method
        );

        if ($editTargetTab == '#CANSWERSTAB') {
            $results = array();

            foreach ($p_canswers as $ca) {
                //First lets make sure there isn't already an exact replica of this condition
                $condition_data['value'] = $ca;

                $result = \Condition::model()->findAllByAttributes($condition_data);

                $count_caseinsensitivedupes = count($result);

                if ($count_caseinsensitivedupes == 0) {
                    $results[] = \Condition::model()->insertRecords($condition_data);
                    ;
                }
            }

            // Check if any result returned false
            if (in_array(false, $results, true)) {
                $write(gT('Could not insert all conditions.'), 'error');
            } elseif (!empty($results)) {
                $write(gT('Condition added.'), 'success');
            } else {
                $write(
                    gT(
                        "The condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer.",
                        "js"
                    ),
                    'error'
                );
            }
        } else {
            $posted_condition_value = null;
            // Other conditions like constant, other question or token field
            switch ($editTargetTab) {
                case '#CONST':
                    $posted_condition_value = $ConditionConst ?? '';
                    break;
                case '#PREVQUESTIONS':
                    $posted_condition_value = $prevQuestionSGQA ?? '';
                    break;
                case '#TOKENATTRS':
                    $posted_condition_value = $tokenAttr ?? '';
                    break;
                case '#REGEXP':
                    $posted_condition_value = $ConditionRegexp ?? '';
                    break;
                default:
                    $posted_condition_value = null;
            }

            $result = null;
            if ($posted_condition_value !== '') {
                $condition_data['value'] = $posted_condition_value;
                $result = \Condition::model()->insertRecords($condition_data);
            }
            if ($result) {
                $write(gT('Condition added.'), 'success');
            } else {
                if ($result === false) {
                    $write(gT('Could not insert all conditions.'), 'error');
                } else {
                    $write(gT("The condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer."), 'error');
                }
            }
        }
        \LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
    }

    /**
     * updateCondition action
     * @param array $args the arguments
     * @param mixed $editTargetTab the target tab
     * @param callable $write the writer callback
     * @param mixed $ConditionConst
     * @param mixed $prevQuestionSGQA
     * @param mixed $tokenAttr
     * @param mixed $ConditionRegexp
     * @return void
     */
    public function updateCondition(array $args, $editTargetTab, callable $write, $ConditionConst, $prevQuestionSGQA, $tokenAttr, $ConditionRegexp)
    {
        extract($args);

        if (isset($p_cquestions) && $p_cquestions != '') {
            $conditionCfieldname = $p_cquestions;
        } elseif (isset($p_csrctoken) && $p_csrctoken != '') {
            $conditionCfieldname = $p_csrctoken;
        }

        $results = array();

        if ($editTargetTab == '#CANSWERSTAB') {
            foreach ($p_canswers as $ca) {
                // This is an Edit, there will only be ONE VALUE
                $updated_data = array(
                    'qid' => $qid,
                    'scenario' => $p_scenario,
                    'cqid' => $p_cqid,
                    'cfieldname' => $conditionCfieldname,
                    'method' => $p_method,
                    'value' => $ca
                );
                $results[] = \Condition::model()->insertRecords($updated_data, true, array('cid' => $p_cid));
            }

            // Check if any result returned false
            if (in_array(false, $results, true)) {
                $write(gT('Could not update condition.'), 'error');
            } elseif (!empty($results)) {
                $write(gT('Condition updated.'), 'success');
            } else {
                $write(gT('Could not update condition.'), 'error');
            }
        } else {
            switch ($editTargetTab) {
                case "#CONST":
                    $posted_condition_value = $ConditionConst;
                    break;
                case "#PREVQUESTIONS":
                    $posted_condition_value = $prevQuestionSGQA;
                    break;
                case "#TOKENATTRS":
                    $posted_condition_value = $tokenAttr;
                    break;
                case "#REGEXP":
                    $posted_condition_value = $ConditionRegexp;
                    break;
                default:
                    $posted_condition_value = null;
            }

            $result = null;
            if ($posted_condition_value !== '') {
                $updated_data = array(
                    'qid' => $qid,
                    'scenario' => $p_scenario,
                    'cqid' => $p_cqid,
                    'cfieldname' => $conditionCfieldname,
                    'method' => $p_method,
                    'value' => $posted_condition_value
                );
                $result = \Condition::model()->insertRecords($updated_data, true, array('cid' => $p_cid));
            }
            if ($result) {
                $write(gT('Condition updated.'), 'success');
            } else {
                if ($result === false) {
                    $write(gT('Could not update condition.'), 'error');
                } else {
                    $write(gT("The condition could not be updated! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer."), 'error');
                }
            }
        }

        \LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
    }

    /**
     * deleteCondition action
     * @param mixed $qid
     * @param mixed $p_cid
     * @return void
     */
    public function deleteCondition($qid, $p_cid)
    {
        \LimeExpressionManager::RevertUpgradeConditionsToRelevance(null, $qid); // in case deleted the last condition
        \Condition::model()->deleteRecords(array('cid' => $p_cid));
        \LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
    }

    /**
     * updateScenario action
     * @param mixed $p_newscenarionum
     * @param mixed $qid
     * @param mixed $p_scenario
     * @param callable $write
     * @return void
     */
    public function updateScenario($p_newscenarionum, $qid, $p_scenario, callable $write)
    {
        if ($p_newscenarionum === null) {
            $write(gT("No scenario number specified"), 'error');
        } else {
            \Condition::model()->insertRecords(array('scenario' => $p_newscenarionum), true, array(
                'qid' => $qid, 'scenario' => $p_scenario));
            \LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
        }
    }

    /**
     * deleteAllConditions action
     * @param mixed $qid
     * @param callable $write
     * @return void
     */
    public function deleteAllConditions($qid, callable $write)
    {
        \LimeExpressionManager::RevertUpgradeConditionsToRelevance(null, $qid); // in case deleted the last condition
        \Condition::model()->deleteRecords(array('qid' => $qid));
        $write(gT("All conditions for this question have been deleted."), 'success');
    }

    public function deleteAllConditionsOfSurvey($sid, callable $write)
    {
        \LimeExpressionManager::RevertUpgradeConditionsToRelevance($sid);
        $qids = [0];
        $questions = \Question::model()->findAllByAttributes(['sid' => $sid]);
        foreach ($questions as $question) {
            $qids [] = $question->qid;
        }
        $qids_str = implode(",", $qids);
        \Condition::model()->deleteRecords("qid in ({$qids_str})");
        $write(gT("All conditions for this survey have been deleted.", 'success'));
    }

    public function conditionScript($qid, $script)
    {
        $question = \Question::model()->findByPk($qid);
        if ($script != $question->relevance) {
            $question->relevance = $script;
            $question->save();
            \Condition::model()->deleteRecords(array('qid' => $qid));
        }
    }

    /**
     * deleteScenario action
     * @param mixed $qid
     * @param mixed $p_scenario
     * @return void
     */
    public function deleteScenario($qid, $p_scenario)
    {
        \LimeExpressionManager::RevertUpgradeConditionsToRelevance(null, $qid); // in case deleted the last condition
        \Condition::model()->deleteRecords(array('qid' => $qid, 'scenario' => $p_scenario));
        \LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
    }

    /**
     * renumberScenarios action
     * @param array $args
     * @param callable $write
     * @return void
     */
    public function renumberScenarios(int $qid, callable $write)
    {
        /** @var string $p_cid */
        $query = "SELECT DISTINCT scenario FROM {{conditions}} WHERE qid=:qid ORDER BY scenario";
        $result = $this->app->db->createCommand($query)->bindParam(":qid", $qid, \PDO::PARAM_INT)->query() or safeDie("Couldn't select scenario<br />$query<br />");
        $newindex = 1;

        foreach ($result->readAll() as $srow) {
            \Condition::model()->insertRecords(array('scenario' => $newindex), true, array('qid' => $qid, 'scenario' => $srow['scenario']));
            $newindex++;
        }
        \LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
        $write(gT("All conditions scenarios were renumbered."));
    }

    /**
     * Finds the condition ids by the question's id they belong to
     * @param mixed $qid
     * @return int[]
     */
    public function getCidsOfQid($qid)
    {
        $conditions = \Condition::model()->findAllByAttributes(["qid" => $qid]);
        $cids = [];
        foreach ($conditions as $c) {
            $cids[] = $c->cid;
        }
        return $cids;
    }

    /**
     * copyConditions action
     * @param array $args
     * @param callable $write
     * @return void
     */
    public function copyConditions($copyconditionsfrom, $copyconditionsto, callable $write)
    {
        if (isset($copyconditionsto) && is_array($copyconditionsto) && isset($copyconditionsfrom) && is_array($copyconditionsfrom)) {
            //Get the conditions we are going to copy and quote them properly
            foreach ($copyconditionsfrom as &$entry) {
                $entry = $this->app->db->quoteValue($entry);
            }
            $query = "SELECT * FROM {{conditions}}\n"
                . "WHERE cid in (";
            $query .= implode(", ", $copyconditionsfrom);
            $query .= ")";
            $result = $this->app->db->createCommand($query)->query() or
                safeDie("Couldn't get conditions for copy<br />$query<br />");

            foreach ($result->readAll() as $row) {
                $proformaconditions[] = array(
                    "scenario"      =>    $row['scenario'],
                    "cqid"          =>    $row['cqid'],
                    "cfieldname"    =>    $row['cfieldname'],
                    "method"        =>    $row['method'],
                    "value"         =>    $row['value']
                );
            } // while

            foreach ($copyconditionsto as $copyc) {
                $newqid = $this->getQIDFromFieldName($copyc);
                foreach ($proformaconditions as $pfc) {
                    //TIBO

                    //First lets make sure there isn't already an exact replica of this condition
                    $conditions_data = array(
                    'qid'        => (int) $newqid,
                    'scenario'   => $pfc['scenario'],
                    'cqid'       => $pfc['cqid'],
                    'cfieldname' => $pfc['cfieldname'],
                    'method'     => $pfc['method'],
                    'value'      => $pfc['value']
                    );

                    $result = \Condition::model()->findAllByAttributes($conditions_data);

                    $count_caseinsensitivedupes = count($result);

                    $countduplicates = 0;
                    if ($count_caseinsensitivedupes != 0) {
                        foreach ($result as $ccrow) {
                            if ($ccrow['value'] == $pfc['value']) {
                                $countduplicates++;
                            }
                        }
                    }

                    if ($countduplicates == 0) {
                        //If there is no match, add the condition.
                        \Condition::model()->insertRecords($conditions_data);
                        $conditionCopied = true;
                    } else {
                        $conditionDuplicated = true;
                    }
                }
            }

            if (isset($conditionCopied) && $conditionCopied === true) {
                if (isset($conditionDuplicated) && $conditionDuplicated == true) {
                    $write(gT("Condition successfully copied (some were skipped because they were duplicates)"), 'warning');
                } else {
                    $write(gT("Condition successfully copied"));
                }
            } else {
                $write(gT("No conditions could be copied (due to duplicates)"), 'error');
            }
        }
        \LimeExpressionManager::UpgradeConditionsToRelevance($this->iSurveyID); // do for whole survey, since don't know which questions affected.
    }

    /**
     * Determines whether the survey is anonymized
     * @param int $iSurveyID
     * @return bool
     */
    public function getSurveyIsAnonymized(int $iSurveyID = 0)
    {
        $info = getSurveyInfo($iSurveyID ?? $this->iSurveyID);
        return $info['anonymized'] == 'Y';
    }

    /**
     * Returns question title and text based on qid
     * @param mixed $qid
     * @return array
     */
    protected function getQuestionTitleAndText($qid)
    {
        $oQuestion = \Question::model()->findByPk($qid);
        return array($oQuestion->title, $oQuestion->questionl10ns[$this->language]->question);
    }

    /**
     * @param int $qid
     * @return array
     */
    protected function getQuestionRows()
    {
        $qresult = \Question::model()->primary()->getQuestionList($this->iSurveyID);

        //'language' => $this->language
        $qrows = array();
        foreach ($qresult as $k => $v) {
            $qrows[$k] = array_merge($v->attributes, $v->group->attributes);
        }

        return $qrows;
    }

    /**
     * Gets a question list by question id and rows
     * @param mixed $qid
     * @param array $qrows
     * @return array
     */
    protected function getQuestionList($qid, array $qrows)
    {
        $position = "before";
        $questionlist = array();
        // Go through each question until we reach the current one
        foreach ($qrows as $qrow) {
            if ($qrow["qid"] != $qid && $position == "before") {
                // remember all previous questions
                // all question types are supported.
                $questionlist[] = $qrow["qid"];
            } elseif ($qrow["qid"] == $qid) {
                break;
            }
        }
        return $questionlist;
    }

    /**
     * Gets the post question list based on question id and question rows
     * @param mixed $qid
     * @param array $qrows
     * @return array
     */
    protected function getPostQuestionList($qid, array $qrows)
    {
        $position = "before";
        $postquestionlist = array();
        foreach ($qrows as $qrow) {
            //Go through each question until we reach the current one
            if ($qrow["qid"] == $qid) {
                $position = "after";
            } elseif ($qrow["qid"] != $qid && $position == "after") {
                $postquestionlist[] = $qrow['qid'];
            }
        }
        return $postquestionlist;
    }

    /**
     * Gets rows from the question list
     * @param array $questionlist
     * @return array{gid: mixed, mandatory: mixed, other: mixed, qid: mixed, question: mixed, sid: mixed, title: mixed, type: mixed[]}
     */
    protected function getTheseRows(array $questionlist)
    {
        $theserows = array();
        foreach ($questionlist as $ql) {
            $result = \Question::model()->with(array(
                'group' => array(
                    'condition' => 'questiongroupl10ns.language = :lang',
                    'params' => array(':lang' => $this->language),
                    'alias'  => 'group',
                ),
                'group.questiongroupl10ns' => array('alias' => 'questiongroupl10ns' ),
                'questionl10ns'
            ))->findAllByAttributes(array('qid' => $ql, 'parent_qid' => 0, 'sid' => $this->iSurveyID));

            // And store again these questions in this array...
            foreach ($result as $myrows) {
                //key => value
                $theserows[] = array(
                    "qid"        =>    $myrows['qid'],
                    "sid"        =>    $myrows['sid'],
                    "gid"        =>    $myrows['gid'],
                    "question"    =>    $myrows->questionl10ns[$this->language]['question'],
                    "type"        =>    $myrows['type'],
                    "mandatory"    =>    $myrows['mandatory'],
                    "other"        =>    $myrows['other'],
                    "title"        =>    $myrows['title']
                );
            }
        }
        return $theserows;
    }

    /**
     * Gets the post rows from the question list
     * @param array $postquestionlist
     * @return array{gid: mixed, mandatory: mixed, other: mixed, qid: mixed, question: string, sid: mixed, title: mixed, type: mixed[]}
     */
    protected function getPostRows(array $postquestionlist)
    {
        $postrows = array();
        foreach ($postquestionlist as $pq) {
            $aoQuestions = \Question::model()->findAllByAttributes(array('qid' => $pq, 'parent_qid' => 0, 'sid' => $this->iSurveyID));

            foreach ($aoQuestions as $oQuestion) {
                $postrows[] = array(
                    "qid"        =>    $oQuestion['qid'],
                    "sid"        =>    $oQuestion['sid'],
                    "gid"        =>    $oQuestion['gid'],
                    "question"    =>    $oQuestion->questionl10ns[$this->language]->question,
                    "type"        =>    $oQuestion['type'],
                    "mandatory"    =>    $oQuestion['mandatory'],
                    "other"        =>    $oQuestion['other'],
                    "title"        =>    $oQuestion['title']
                );
            }
        }
        return $postrows;
    }

    /**
     * Gets C answers and C questions based on an array of rows
     * @param array $theserows
     * @return array
     */
    protected function getCAnswersAndCQuestions(array $theserows)
    {
        $cquestions = array();
        $canswers = array();

        foreach ($theserows as $rows) {
            $shortquestion = $rows['title'] . ": " . strip_tags((string) $rows['question']);

            if ($rows['type'] == "A" || $rows['type'] == "B" || $rows['type'] == "C" || $rows['type'] == "E" || $rows['type'] == "F" || $rows['type'] == "H") {
                $aresult = \Question::model()->with('questionl10ns')->findAllByAttributes(array('parent_qid' => $rows['qid']), array('order' => 'question_order ASC'));

                foreach ($aresult as $arows) {
                    $shortanswer = "{$arows['title']}: [" . flattenText($arows->questionl10ns[$this->language]->question) . "]";
                    $shortquestion = $rows['title'] . ":$shortanswer " . flattenText($rows['question']);
                    $fieldName = $this->getFieldName($rows['sid'], $rows['gid'], $rows['qid'], $arows['title']);
                    $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'],
                        $fieldName
                    );

                    switch ($rows['type']) {
                        // Array 5 buttons
                        case "A":
                            for ($i = 1; $i <= 5; $i++) {
                                $canswers[] = array($fieldName, $i, $i);
                            }
                            break;
                        // Array 10 buttons
                        case "B":
                            for ($i = 1; $i <= 10; $i++) {
                                $canswers[] = array($fieldName, $i, $i);
                            }
                            break;
                        // Array Y/N/NA
                        case "C":
                            $canswers[] = array($fieldName, "Y", gT("Yes"));
                            $canswers[] = array($fieldName, "U", gT("Uncertain"));
                            $canswers[] = array($fieldName, "N", gT("No"));
                            break;
                            // Array >/=/<
                        case "E":
                            $canswers[] = array($fieldName, "I", gT("Increase"));
                            $canswers[] = array($fieldName, "S", gT("Same"));
                            $canswers[] = array($fieldName, "D", gT("Decrease"));
                            break;
                            // Array Flexible Row
                        case "F":
                            // Array Flexible Column
                        case "H":
                            $fresult = \Answer::model()->with(array(
                            'answerl10ns' => array(
                                'condition' => 'answerl10ns.language = :lang',
                                'params' => array(':lang' => $this->language),
                                'alias' => 'answerl10ns',
                            )))->findAllByAttributes(
                                array(
                                    'qid' => $rows['qid'],
                                    'scale_id' => 0,
                                )
                            );
                            foreach ($fresult as $frow) {
                                $canswers[] = array($fieldName, $frow['code'], $frow->answerl10ns[$this->language]->answer);
                            }
                            break;
                    }
                    // Only Show No-Answer if question is not mandatory
                    if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                        $canswers[] = array($fieldName, "", gT("No answer"));
                    }
                } //foreach
            } elseif ($rows['type'] == \Question::QT_COLON_ARRAY_NUMBERS || $rows['type'] == \Question::QT_SEMICOLON_ARRAY_TEXT) {
                // Multiflexi
                // Get the Y-Axis
                $fquery = "SELECT sq.*, q.other, l10ns.question
                    FROM {{questions sq}}, {{questions q}}, {{question_l10ns l10ns}}
                    WHERE sq.sid={$this->iSurveyID}
                    AND sq.parent_qid=q.qid
                    AND sq.qid = l10ns.qid
                    AND l10ns.language=:lang1
                    AND q.qid=:qid
                    AND sq.scale_id=0
                    ORDER BY sq.question_order";
                $sLanguage = $this->language;
                $y_axis_db = $this->app->db->createCommand($fquery)
                    ->bindParam(":lang1", $sLanguage, \PDO::PARAM_STR)
                    ->bindParam(":qid", $rows['qid'], \PDO::PARAM_INT)
                    ->query();

                // Get the X-Axis
                $aquery = "SELECT sq.*, l10ns.question
                    FROM {{questions q}}, {{questions sq}}, {{question_l10ns l10ns}}
                    WHERE q.sid={$this->iSurveyID}
                    AND sq.parent_qid=q.qid
                    AND sq.qid = l10ns.qid
                    AND l10ns.language=:lang1
                    AND q.qid=:qid
                    AND sq.scale_id=1
                    ORDER BY sq.question_order";

                $x_axis_db = $this->app->db->createCommand($aquery)
                    ->bindParam(":lang1", $sLanguage, \PDO::PARAM_STR)
                    ->bindParam(":qid", $rows['qid'], \PDO::PARAM_INT)
                    ->query() or safeDie("Couldn't get answers to Array questions<br />$aquery<br />");

                $x_axis = [];

                foreach ($x_axis_db->readAll() as $frow) {
                    $x_axis[$frow['title']] = $frow['question'];
                }

                foreach ($y_axis_db->readAll() as $yrow) {
                    foreach ($x_axis as $key => $val) {
                        $fieldName = $this->getFieldName($rows['sid'], $rows['gid'], $rows['qid'], $yrow['title'] . "_" . $key);
                        $shortquestion = $rows['title'] . ":{$yrow['title']}:$key: [" . strip_tags((string) $yrow['question']) . "][" . strip_tags((string) $val) . "] " . flattenText($rows['question']);
                        $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $fieldName);
                        if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                        }
                    }
                }
                unset($x_axis);
            } elseif ($rows['type'] == "1") {
                /* Used to get dualscale_headerA and dualscale_headerB */
                $attr = \QuestionAttribute::model()->getQuestionAttributes($rows['qObject']);
                //Dual scale
                $aresult = \Question::model()->with(array(
                            'questionl10ns' => array(
                                'condition' => 'questionl10ns.language = :lang',
                                'params' => array(':lang' => $this->language)
                            )))->findAllByAttributes(array('parent_qid' => $rows['qid']), array('order' => 'question_order ASC, scale_id ASC'));
                foreach ($aresult as $arows) {
                    $fieldName = $this->getFieldName($rows['sid'], $rows['gid'], $rows['qid'], $arows['title']);
                    $sLanguage = $this->language;
                    // dualscale_header are always set, but can be empty
                    $label1 = empty($attr['dualscale_headerA'][$sLanguage]) ? gT('Scale 1') : $attr['dualscale_headerA'][$sLanguage];
                    $label2 = empty($attr['dualscale_headerB'][$sLanguage]) ? gT('Scale 2') : $attr['dualscale_headerB'][$sLanguage];
                    $shortanswer = "{$arows['title']}: [" . strip_tags((string) $arows->questionl10ns[$this->language]->question) . "][$label1]";
                    $shortquestion = $rows['title'] . ":$shortanswer " . strip_tags((string) $arows->questionl10ns[$this->language]->question);
                    $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $fieldName . "#0");

                    $shortanswer = "{$arows['title']}: [" . strip_tags((string) $arows->questionl10ns[$this->language]->question) . "][$label2]";
                    $shortquestion = $rows['title'] . ":$shortanswer " . strip_tags((string) $arows->questionl10ns[$this->language]->question);
                    $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $fieldName . "#1");

                    // first label
                    $lresult = \Answer::model()->with(array(
                            'answerl10ns' => array(
                                'condition' => 'answerl10ns.language = :lang',
                                'params' => array(':lang' => $this->language)
                            )))->findAllByAttributes(array('qid' => $rows['qid'], 'scale_id' => 0));
                    foreach ($lresult as $lrows) {
                        $canswers[] = array($fieldName . "#0", "{$lrows['code']}", "{$lrows['code']}");
                    }

                    // second label
                    $lresult = \Answer::model()->with(array(
                            'answerl10ns' => array(
                                'condition' => 'answerl10ns.language = :lang',
                                'params' => array(':lang' => $this->language)
                            )))->findAllByAttributes(array(
                                'qid' => $rows['qid'],
                                'scale_id' => 1
                            ));

                    foreach ($lresult as $lrows) {
                        $canswers[] = array($fieldName . "#1", "{$lrows['code']}", "{$lrows['code']}");
                    }

                    // Only Show No-Answer if question is not mandatory
                    if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                        $canswers[] = array($fieldName . "#0", "", gT("No answer"));
                        $canswers[] = array($fieldName . "#1", "", gT("No answer"));
                    }
                } //foreach
            } elseif ($rows['type'] == \Question::QT_K_MULTIPLE_NUMERICAL || $rows['type'] == \Question::QT_Q_MULTIPLE_SHORT_TEXT) {
                //Multi shorttext/numerical
                $aresult = \Question::model()->with('questionl10ns')->findAllByAttributes(array(
                    "parent_qid" => $rows['qid']
                ), array('order' => 'question_order desc'));

                foreach ($aresult as $arows) {
                    $fieldName = $this->getFieldName($rows['sid'], $rows['gid'], $rows['qid'], $arows['title']);
                    $shortanswer = "{$arows['title']}: [" . strip_tags((string) $arows->questionl10ns[$this->language]->question) . "]";
                    $shortquestion = $rows['title'] . ":$shortanswer " . strip_tags((string) $rows['question']);
                    $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $fieldName);

                    // Only Show No-Answer if question is not mandatory
                    if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                        $canswers[] = array($fieldName, "", gT("No answer"));
                    }
                } //foreach
            } elseif ($rows['type'] == \Question::QT_R_RANKING) {
                //Answer Ranking
                $aresult = \Answer::model()->with(array(
                            'answerl10ns' => array(
                                'condition' => 'answerl10ns.language = :lang',
                                'params' => array(':lang' => $this->language)
                            )))->findAllByAttributes(
                                array(
                                    "qid" => $rows['qid'],
                                    "scale_id" => 0,
                                )
                            );

                $acount = count($aresult);

                $quicky = [];
                foreach ($aresult as $arow) {
                    $theanswer = $arow->answerl10ns[$this->language]->answer;
                    $quicky[] = array($arow['code'], $theanswer);
                }

                for ($i = 1; $i <= $acount; $i++) {
                    $fieldName = $this->getFieldName($rows['sid'], $rows['gid'], $rows['qid'], $i);
                    $cquestions[] = array("{$rows['title']}: [RANK $i] " . strip_tags((string) $rows['question']), $rows['qid'], $rows['type'], $fieldName);
                    foreach ($quicky as $qck) {
                        $canswers[] = array($fieldName, $qck[0], $qck[1]);
                    }
                    // Only Show No-Answer if question is not mandatory
                    if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                        $canswers[] = array($fieldName, " ", gT("No answer"));
                    }
                }
                unset($quicky);
                // End if type R
            } elseif ($rows['type'] == \Question::QT_M_MULTIPLE_CHOICE || $rows['type'] == \Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS) {
                $fieldName = $this->getFieldName($rows['sid'], $rows['gid'], $rows['qid']);
                $fieldNameWithTitle = $this->getFieldName($rows['sid'], $rows['gid'], $rows['qid'], $arows['title']);
                $shortanswer = " [" . gT("Group of checkboxes") . "]";
                $shortquestion = $rows['title'] . ":$shortanswer " . strip_tags((string) $rows['question']);
                $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $fieldName);

                $aresult = \Question::model()->with('questionl10ns')->findAllByAttributes(array(
                    "parent_qid" => $rows['qid'],
                ), array('order' => 'question_order desc'));

                foreach ($aresult as $arows) {
                    $theanswer = $arows->questionl10ns[$this->language]->question;
                    $canswers[] = array($fieldName, $arows['title'], $theanswer);

                    $shortanswer = "{$arows['title']}: [" . strip_tags((string) $theanswer) . "]";
                    $shortanswer .= "[" . gT("Single checkbox") . "]";
                    $shortquestion = $rows['title'] . ":$shortanswer " . strip_tags((string) $rows['question']);
                    $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], "+" . $fieldNameWithTitle);
                    $canswers[] = array("+" . $fieldNameWithTitle, 'Y', gT("checked"));
                    $canswers[] = array("+" . $fieldNameWithTitle, '', gT("not checked"));
                }
            } else {
                $fieldName = $this->getFieldName($rows['sid'], $rows['gid'], $rows['qid']);
                $cquestions[] = array($shortquestion, $rows['qid'], $rows['type'], $fieldName);

                switch ($rows['type']) {
                    case \Question::QT_Y_YES_NO_RADIO: // Y/N/NA
                        $canswers[] = array($fieldName, "Y", gT("Yes"));
                        $canswers[] = array($fieldName, "N", gT("No"));
                        // Only Show No-Answer if question is not mandatory
                        if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                            $canswers[] = array($fieldName, " ", gT("No answer"));
                        }
                        break;
                    case \Question::QT_G_GENDER: //Gender
                        $canswers[] = array($fieldName, "F", gT("Female"));
                        $canswers[] = array($fieldName, "M", gT("Male"));
                        // Only Show No-Answer if question is not mandatory
                        if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                            $canswers[] = array($fieldName, " ", gT("No answer"));
                        }
                        break;
                    case \Question::QT_5_POINT_CHOICE: // 5 choice
                        for ($i = 1; $i <= 5; $i++) {
                            $canswers[] = array($fieldName, $i, $i);
                        }
                        // Only Show No-Answer if question is not mandatory
                        if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                            $canswers[] = array($fieldName, " ", gT("No answer"));
                        }
                        break;
                    case \Question::QT_N_NUMERICAL: // Simple Numerical questions
                        // Only Show No-Answer if question is not mandatory
                        if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                            $canswers[] = array($fieldName, " ", gT("No answer"));
                        }
                        break;

                    default:
                        $aresult = \Answer::model()->with(array(
                            'answerl10ns' => array(
                                'condition' => 'answerl10ns.language = :lang',
                                'params' => array(':lang' => $this->language),
                                'alias' => 'answerl10ns',
                            )))->findAllByAttributes(array(
                                'qid' => $rows['qid'],
                                'scale_id' => 0,
                            ));

                        foreach ($aresult as $arows) {
                            $theanswer = $arows->answerl10ns[$this->language]->answer;
                            $canswers[] = array($fieldName, $arows['code'], $theanswer);
                        }
                        if ($rows['type'] == \Question::QT_D_DATE) {
                            // Only Show No-Answer if question is not mandatory
                            if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                                $canswers[] = array($fieldName, " ", gT("No answer"));
                            }
                        } elseif (
                            $rows['type'] != \Question::QT_M_MULTIPLE_CHOICE &&
                            $rows['type'] != \Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS &&
                            $rows['type'] != \Question::QT_I_LANGUAGE
                        ) {
                            // For dropdown questions
                            // optinnaly add the 'Other' answer
                            if (
                                ($rows['type'] == \Question::QT_L_LIST ||
                                $rows['type'] == \Question::QT_EXCLAMATION_LIST_DROPDOWN) &&
                                $rows['other'] == "Y"
                            ) {
                                $canswers[] = array($fieldName, "-oth-", gT("Other"));
                            }

                            // Only Show No-Answer if question is not mandatory
                            if ($rows['mandatory'] != 'Y' && $rows['mandatory'] != 'S') {
                                $canswers[] = array($fieldName, " ", gT("No answer"));
                            }
                        }
                        break;
                }//switch row type
            } //else
        } //foreach theserows
        return array($cquestions, $canswers);
    }

    /**
     * Gets question navigation options
     * @param mixed $gid
     * @param mixed $qid
     * @param array $theserows
     * @param array $postrows
     * @param array $args
     * @param callable $getPath
     * @param callable $renderPartial
     * @return string
     */
    protected function getQuestionNavOptions($gid, $qid, array $theserows, array $postrows, array $args, callable $getPath, callable $renderPartial): string
    {
        /** @var integer $gid */
        /** @var integer $qid */
        /** @var string $questiontitle */
        /** @var string $sCurrentFullQuestionText */
        extract($args);

        $theserows2 = array();
        foreach ($theserows as $row) {
            $question = strip_tags((string) $row['question']);
            $questionselecter = \viewHelper::flatEllipsizeText($question, true, '40');
            $theserows2[] = array(
                'value' => $getPath($row['gid'], $row['qid']),
                'text' => strip_tags((string) $row['title']) . ':' . $questionselecter
            );
        }

        $postrows2 = array();
        foreach ($postrows as $row) {
            $question = strip_tags((string) $row['question']);
            $questionselecter = \viewHelper::flatEllipsizeText($question, true, '40');
            $postrows2[] = array(
                'value' => $getPath($row['gid'], $row['qid']),
                'text' => strip_tags((string) $row['title']) . ':' . $questionselecter
            );
        }

        $data = array(
            'theserows' => $theserows2,
            'postrows' => $postrows2,
            'currentValue' => $getPath($gid, $qid),
            'currentText' => $questiontitle . ':' . \viewHelper::flatEllipsizeText(strip_tags((string) $sCurrentFullQuestionText), true, '40')
        );

        //return $this->getController()->renderPartial('/admin/conditions/includes/navigator', $data, true);
        return $renderPartial('navigator', $data, true);
    }

    /**
     * Gets all scenarios based on question id
     * @param int $qid
     * @return mixed
     */
    public function getAllScenarios(int $qid)
    {
        $criteria = new \CDbCriteria();
        $criteria->select = 'scenario'; // only select the 'scenario' column
        $criteria->condition = 'qid=:qid';
        $criteria->params = array(':qid' => $qid);
        $criteria->order = 'scenario';
        $criteria->group = 'scenario';

        return \Condition::model()->findAll($criteria);
    }

    /**
     * Resturns the question count of an array received as parameter
     * @param array $cquestions
     * @return int
     */
    protected function getQCount(array $cquestions): int
    {
        if (count($cquestions) > 0 && count($cquestions) <= 10) {
            $qcount = count($cquestions);
        } else {
            $qcount = 9;
        }

        return $qcount;
    }

    /**
     * Returns the quick add condition form for the group and question based on the received arguments
     * @param mixed $gid
     * @param mixed $qid
     * @param array $args
     * @param callable $renderPartial
     * @return mixed
     */
    protected function getQuickAddConditionForm($gid, $qid, array $args, callable $renderPartial)
    {
        /** @var integer $iSurveyID */
        /** @var integer $gid */
        /** @var integer $qid */
        /** @var string $subaction */
        /** @var string $method */
        /** @var string $p_csrctoken */
        /** @var string $p_prevquestionsgqa */
        /** @var array $cquestions */
        extract($args);
        $data = array(
            'subaction'     => $subaction,
            'iSurveyID'     => $iSurveyID,
            'gid'           => $gid,
            'qid'           => $qid,
            'cquestions'    => $cquestions,
            'p_csrctoken'   => $p_csrctoken,
            'p_prevquestionsgqa'  => $p_prevquestionsgqa,
            'tokenFieldsAndNames' => $this->tokenFieldsAndNames,
            'method'        => $method,
        );
        $html = $renderPartial('quickAddConditionForm', $data, true);
        return $html;
    }

    /**
     * Returns the attribute name based on extracted token attributes
     * @param mixed $extractedTokenAttr
     * @return string
     */
    protected function getAttributeName($extractedTokenAttr): string
    {
        if (isset($this->tokenFieldsAndNames[strtolower($extractedTokenAttr[1])])) {
            $thisAttrName = HTMLEscape($this->tokenFieldsAndNames[strtolower($extractedTokenAttr[1])]['description']);
        } else {
            $thisAttrName = HTMLEscape($extractedTokenAttr[1]);
        }

        if ($this->tokenTableExists) {
            $thisAttrName .= " [" . gT("From survey participants table") . "]";
        } else {
            $thisAttrName .= " [" . gT("Non-existing survey participants table") . "]";
        }

        return $thisAttrName;
    }

    /**
     * Returns hidden fields for rows
     * @param array $rows
     * @param string $leftOperandType
     * @param string $rightOperandType
     * @return string
     */
    protected function getHiddenFields(array $rows, string $leftOperandType, string $rightOperandType): string
    {
        $html = '';

        // now sets e corresponding hidden input field
        // depending on the leftOperandType
        if ($leftOperandType == 'tokenattr') {
            $html .= \CHtml::hiddenField('csrctoken', HTMLEscape($rows['cfieldname']), array(
                'id' => 'csrctoken' . $rows['cid']
            ));
        } else {
            $html .= \CHtml::hiddenField(
                'cquestions',
                HTMLEscape($rows['cfieldname']),
                array(
                    'id' => 'cquestions' . $rows['cid']
                )
            );
        }

        // now set the corresponding hidden input field
        // depending on the rightOperandType
        // This is used when editing a condition
        if ($rightOperandType == 'predefinedAnsw') {
            $html .= \CHtml::hiddenField('EDITcanswers[]', HTMLEscape($rows['value']), array(
                'id' => 'editModeTargetVal' . $rows['cid']
            ));
        } elseif ($rightOperandType == 'prevQsgqa') {
            $html .= \CHtml::hiddenField(
                'EDITprevQuestionSGQA',
                HTMLEscape($rows['value']),
                array(
                    'id' => 'editModeTargetVal' . $rows['cid']
                )
            );
        } elseif ($rightOperandType == 'tokenAttr') {
            $html .= \CHtml::hiddenField('EDITtokenAttr', HTMLEscape($rows['value']), array(
                'id' => 'editModeTargetVal' . $rows['cid']
            ));
        } elseif ($rightOperandType == 'regexp') {
            $html .= \CHtml::hiddenField(
                'EDITConditionRegexp',
                HTMLEscape($rows['value']),
                array(
                    'id' => 'editModeTargetVal' . $rows['cid']
                )
            );
        } else {
            $html .= \CHtml::hiddenField(
                'EDITConditionConst',
                HTMLEscape($rows['value']),
                array(
                    'id' => 'editModeTargetVal' . $rows['cid']
                )
            );
        }

        return $html;
    }

    /**
     * index action. This is a composite action, a legacy code from its original implementation that calls the action and displays the results
     * @param mixed $args
     * @param mixed $aData
     * @param mixed $subaction
     * @param mixed $method
     * @param mixed $gid
     * @param mixed $qid
     * @param mixed $imageurl
     * @param mixed $extraGetParams
     * @param callable $addScript
     * @param callable $getPath
     * @param callable $myCreateUrl
     * @param callable $renderPartial
     * @param callable $getJavascriptForMatching
     * @param callable $getCopyForm
     * @param callable $getEditConditionForm
     * @return array
     */
    public function index($args, $aData, $subaction, $method, $gid, $qid, $imageurl, $extraGetParams, callable $addScript, callable $getPath, callable $myCreateUrl, callable $renderPartial, callable $getJavascriptForMatching, callable $getCopyForm, callable $getEditConditionForm)
    {
        $cquestions = array();
        $canswers   = array();
        $pquestions = array();

        $language = Survey::model()->findByPk($this->iSurveyID)->language;
        $this->language = $language;

        //BEGIN: GATHER INFORMATION
        // 1: Get information for this question
        // @todo : use viewHelper::getFieldText and getFieldCode for 2.06 for string show to user
        $aData['surveyIsAnonymized'] = $surveyIsAnonymized = $this->getSurveyIsAnonymized($this->iSurveyID);

        list($questiontitle, $sCurrentFullQuestionText) = $this->getQuestionTitleAndText($qid);

        // 2: Get all other questions that occur before this question that are pre-determined answer types

        // To avoid natural sort order issues,
        // first get all questions in natural sort order
        // , and find out which number in that order this question is
        // Then, using the same array which is now properly sorted by group then question
        // Create an array of all the questions that appear AFTER the current one
        $questionRows = $this->getQuestionRows();
        $questionlist = $this->getQuestionList($qid, $questionRows);
        $postquestionlist = $this->getPostQuestionList($qid, $questionRows);

        $theserows = $this->getTheseRows($questionlist);
        $postrows  = $this->getPostRows($postquestionlist);

        $questionscount = count($theserows);
        $postquestionscount = count($postrows);

        if (isset($postquestionscount) && $postquestionscount > 0) {
            //Build the array used for the questionNav and copyTo select boxes
            foreach ($postrows as $pr) {
                $pquestions[] = array("text" => $pr['title'] . ": " . (string) substr(strip_tags((string) $pr['question']), 0, 80),
                    "fieldname" => $this->getFieldName($pr['sid'], $pr['gid'], $pr['qid']));
            }
        }


        // Previous question parsing ==> building cquestions[] and canswers[]
        if ($questionscount > 0) {
            $qids = [];
            for ($index = 0; $index < count($theserows); $index++) {
                if ($theserows[$index]['type'] == "1") {
                    $qids[] = $theserows[$index]['qid'];
                }
            }
            if (count($qids)) {
                $rawQuestions = \Question::model()->findAllByPk($qids);
                $questions = [];
                foreach ($rawQuestions as $rawQuestion) {
                    $questions[$rawQuestion->qid] = $rawQuestion;
                }
                for ($index = 0; $index < count($theserows); $index++) {
                    if ($theserows[$index]['type'] == "1") {
                        $theserows[$index]['qObject'] = $questions[$theserows[$index]['qid']];
                    }
                }
            }

            list($cquestions, $canswers) = $this->getCAnswersAndCQuestions($theserows);
        } //if questionscount > 0
        //END Gather Information for this question

        $args['sCurrentFullQuestionText'] = $sCurrentFullQuestionText;
        $args['questiontitle'] = $questiontitle;
        $args['gid'] = $gid;
        $questionNavOptions = $this->getQuestionNavOptions($gid, $qid, $theserows, $postrows, $args, $getPath, $renderPartial);

        //Now display the information and forms

        $javascriptpre = $getJavascriptForMatching($canswers, $cquestions, $surveyIsAnonymized);

        $aViewUrls = array();

        $oQuestion = \Question::model()->find('qid=:qid', array(':qid' => $qid));
        $aData['oQuestion'] = $oQuestion;

        // @todo why surveyid and iSurveyID will be used? Only use one!
        $aData['surveyid'] = $this->iSurveyID;
        $aData['qid'] = $qid;
        $aData['gid'] = $gid;
        $aData['imageurl'] = $imageurl;
        $aData['extraGetParams'] = $extraGetParams;
        $aData['questionNavOptions'] = $questionNavOptions;
        $aData['javascriptpre'] = $javascriptpre;


        // Back Button
        $aData['showBackButton'] = true;

        $scenarios = $this->getAllScenarios($qid);

        // Some extra args to getEditConditionForm
        $args['subaction'] = $subaction;
        // @todo why surveyid and iSurveyID will be used? Only use one!
        $args['iSurveyID'] = $this->iSurveyID;
        $args['gid'] = $gid;
        $args['qcount'] = $this->getQCount($cquestions);
        $args['method'] = $method;
        $args['cquestions'] = $cquestions;
        $args['scenariocount'] = count($scenarios);

        $aData['quickAddConditionForm'] = $this->getQuickAddConditionForm($gid, $qid, $args, $renderPartial);

        $aData['quickAddConditionURL'] = $myCreateUrl(
            'quickAddCondition',
            array(
                'surveyId' => $this->iSurveyID,
                'gid'      => (int)$gid,
                'qid'      => (int)$qid
            )
        );

        $aViewUrls['conditionshead_view'][] = $aData;

        $conditionsList = array();

        //BEGIN DISPLAY CONDITIONS FOR THIS QUESTION
        if (
            $subaction == 'index' ||
            $subaction == 'editconditionsform' || $subaction == 'insertcondition' ||
            $subaction == "editthiscondition" || $subaction == "delete" ||
            $subaction == "updatecondition" || $subaction == "deletescenario" ||
            $subaction == "renumberscenarios" || $subaction == "deleteallconditions" ||
            $subaction == "updatescenario" ||
            $subaction == 'copyconditionsform' || $subaction == 'copyconditions' || $subaction == 'conditions'
        ) {
            //3: Get other conditions currently set for this question
            $s = 0;

            $scenariocount = count($scenarios);

            $aData['conditionsoutput'] = '';
            $aData['extraGetParams'] = $extraGetParams;
            $aData['questionNavOptions'] = $questionNavOptions;
            $aData['javascriptpre'] = $javascriptpre;
            $aData['sCurrentQuestionText'] = $questiontitle . ': ' . \viewHelper::flatEllipsizeText($sCurrentFullQuestionText, true, '120');

            $aData['scenariocount'] = $scenariocount;
            if (empty(trim((string) $oQuestion->relevance)) || !empty($oQuestion->conditions)) {
                $aViewUrls['conditionslist_view'][] = $aData;
            }

            if ($scenariocount > 0) {
                $addScript('adminscripts', 'checkgroup', \LSYii_ClientScript::POS_BEGIN);
                foreach ($scenarios as $scenarionr) {
                    if ($s == 0 && $scenariocount > 1) {
                        $aData['showScenarioText'] = 'normal';
                    } elseif ($s > 0) {
                        $aData['showScenarioText'] = 'withOr';
                    } else {
                        $aData['showScenarioText'] = null;
                    }

                    if (
                        !empty($aData['showScenarioText']) &&
                        ($subaction == "editconditionsform" ||
                        $subaction == "insertcondition" ||
                        $subaction == "updatecondition" ||
                        $subaction == "editthiscondition" ||
                        $subaction == "renumberscenarios" ||
                        $subaction == "updatescenario" ||
                        $subaction == "deletescenario" ||
                        $subaction == "delete")
                    ) {
                        $aData['showScenarioButtons'] = true;
                    } else {
                        $aData['showScenarioButtons'] = false;
                    }

                    $aData['scenarionr'] = $scenarionr;

                    // Used when click on button to add condition to scenario
                    $aData['addConditionToScenarioURL'] = $myCreateUrl(
                        'index',
                        array(
                            'subaction' => 'editconditionsform',
                            'surveyid' => $this->iSurveyID,
                            'gid' => $gid,
                            'qid' => $qid,
                            'scenarioNr' => $scenarionr['scenario']
                        )
                    );

                    if (!isset($aViewUrls['output'])) {
                        $aViewUrls['output'] = '';
                    }

                    $aData['conditionHtml'] = '';

                    unset($currentfield);

                    $conditionscount = \Condition::model()->getConditionCount($qid, $this->language, $scenarionr);
                    $conditions = \Condition::model()->getConditions($qid, $this->language, $scenarionr);
                    $conditionscounttoken = \Condition::model()->getConditionCountToken($qid, $scenarionr);
                    $resulttoken = \Condition::model()->getConditionsToken($qid, $scenarionr);

                    $conditionscount = $conditionscount + $conditionscounttoken;

                    ////////////////// BUILD CONDITIONS DISPLAY
                    if ($conditionscount > 0) {
                        $aConditionsMerged = array();
                        foreach ($resulttoken->readAll() as $arow) {
                            $aConditionsMerged[] = $arow;
                        }
                        foreach ($conditions as $arow) {
                            $aConditionsMerged[] = $arow;
                        }

                        foreach ($aConditionsMerged as $rows) {
                            if ($rows['method'] == "") {
                                $rows['method'] = "==";
                            } //Fill in the empty method from previous versions

                            // This variable is used for condition.php view; $aData is used for other view
                            $data = array();

                            if (isset($currentfield) && $currentfield != $rows['cfieldname']) {
                                $data['andOrOr'] = gT('and');
                            } elseif (isset($currentfield)) {
                                $data['andOrOr'] = gT('or');
                            } else {
                                $data['andOrOr'] = '';
                            }

                            $data['formAction'] = $myCreateUrl(
                                'index',
                                array(
                                    'subaction' => $subaction,
                                    'surveyid' => $this->iSurveyID,
                                    'gid' => $gid,
                                    'qid' => $qid
                                )
                            );
                            $data['row'] = $rows;
                            $data['subaction'] = $subaction;
                            $data['scenarionr'] = $scenarionr;
                            $data['method'] = $method;

                            $leftOperandType = 'unknown'; // prevquestion, tokenattr
                            if (preg_match('/^{TOKEN:([^}]*)}$/', (string) $rows['cfieldname'], $extractedTokenAttr) > 0) {
                                if ($surveyIsAnonymized) {
                                    $data['name'] = sprintf(gT("Unable to use %s in anonymized survey."), trim((string) $rows['cfieldname'], "{}"));
                                } else {
                                    $leftOperandType = 'tokenattr';
                                    $thisAttrName = $this->getAttributeName($extractedTokenAttr);
                                    $data['name'] = $thisAttrName;
                                    // TIBO not sure this is used anymore !!
                                    $conditionsList[] = array(
                                        "cid"  => $rows['cid'],
                                        "text" => $thisAttrName
                                    );
                                }
                            } else {
                                $leftOperandType = 'prevquestion';
                                foreach ($cquestions as $cqn) {
                                    if ($cqn[3] == $rows['cfieldname']) {
                                        $data['name'] = $cqn[0] . "(qid{$rows['cqid']})";
                                        $conditionsList[] = array(
                                            "cid"  => $rows['cid'],
                                            "text" => $cqn[0] . " ({$rows['value']})"
                                        );
                                    }
                                }
                            }
                            if (!isset($data['name'])) {
                                $data['name'] = sprintf(gT("Variable not found: %s"), $rows['cfieldname']);
                            }

                            // let's read the condition's right operand
                            // determine its type and display it
                            $rightOperandType = 'unknown'; // predefinedAnsw,constantVal, prevQsgqa, tokenAttr, regexp
                            if ($rows['method'] == 'RX') {
                                $rightOperandType = 'regexp';
                                $data['target'] = HTMLEscape($rows['value']);
                            } elseif (preg_match('/^@([0-9]+X[0-9]+X[^@]*)@$/', (string) $rows['value'], $matchedSGQA) > 0) {
                                // SGQA
                                $rightOperandType = 'prevQsgqa';
                                $textfound = false;
                                $matchedSGQAText = '';
                                foreach ($cquestions as $cqn) {
                                    if ($cqn[3] == $matchedSGQA[1]) {
                                        $matchedSGQAText = $cqn[0];
                                        $textfound = true;
                                        break;
                                    }
                                }

                                if ($textfound === false) {
                                    $matchedSGQAText = $rows['value'] . ' (' . gT("Not found") . ')';
                                }

                                $data['target'] = HTMLEscape($matchedSGQAText);
                            } elseif (!$surveyIsAnonymized && preg_match('/^{TOKEN:([^}]*)}$/', (string) $rows['value'], $extractedTokenAttr) > 0) {
                                $rightOperandType = 'tokenAttr';
                                $aTokenAttrNames = $this->tokenFieldsAndNames;
                                if ($this->tokenTableExists) {
                                    $thisAttrName = HTMLEscape($aTokenAttrNames[strtolower($extractedTokenAttr[1])]['description']) . " [" . gT("From survey participants table") . "]";
                                } else {
                                    $thisAttrName = HTMLEscape($extractedTokenAttr[1]) . " [" . gT("Non-existing survey participants table") . "]";
                                }
                                $data['target'] = $thisAttrName;
                            } elseif (isset($canswers)) {
                                foreach ($canswers as $can) {
                                    if ($can[0] == $rows['cfieldname'] && $can[1] == $rows['value']) {
                                        $data['target'] = "$can[2] ($can[1])\n";
                                        $rightOperandType = 'predefinedAnsw';
                                    }
                                }
                            }

                            // if $rightOperandType is still unknown then it is a simple constant
                            if ($rightOperandType == 'unknown') {
                                $rightOperandType = 'constantVal';
                                if ($rows['value'] == ' ' || $rows['value'] == '') {
                                    $data['target'] = gT("No answer");
                                } else {
                                    $data['target'] = HTMLEscape($rows['value']);
                                }
                            }

                            if (
                                $subaction == "editconditionsform"
                                || $subaction == "insertcondition"
                                || $subaction == "updatecondition"
                                || $subaction == "editthiscondition"
                                || $subaction == "renumberscenarios"
                                || $subaction == "deleteallconditions"
                                || $subaction == "updatescenario"
                                || $subaction == "deletescenario"
                                || $subaction == "delete"
                            ) {
                                // show single condition action buttons in edit mode

                                $aData['rows'] = $rows;
                                $aData['sImageURL'] = $imageurl;

                                $data['editButtons'] = $renderPartial('conditions_edit', $aData, true);
                                $data['hiddenFields'] = $this->getHiddenFields($rows, $leftOperandType, $rightOperandType);
                            } else {
                                $data['editButtons'] = '';
                                $data['hiddenFields'] = '';
                            }

                            $aData['conditionHtml'] .= $renderPartial(
                                'condition',
                                $data,
                                true
                            );

                            $currentfield = $rows['cfieldname'];
                        }
                    }

                    $s++;

                    $aViewUrls['output'] .= $renderPartial(
                        'conditions_scenario',
                        $aData,
                        true
                    );
                }
                // If we have a condition, all ways reset the condition, this can fix old import (see #09344)
                // LimeExpressionManager::UpgradeConditionsToRelevance(NULL,$qid);
            } elseif (!empty(trim((string) $oQuestion->relevance)) ||  trim((string) $oQuestion->relevance) == '1') {
                $aViewUrls['output'] = $renderPartial('customized_conditions', $aData, true);
            } else {
                // no condition ==> disable delete all conditions button, and display a simple comment
                // no_conditions
                $aViewUrls['output'] = $renderPartial('no_condition', $aData, true);
            }

            //// To close the div opened in condition header....  see : https://goo.gl/BY7gUJ
            $aViewUrls['afteroutput'] = '</div></div></div>';
        }
        //END DISPLAY CONDITIONS FOR THIS QUESTION

        // Display the copy conditions form
        if (
            $subaction == "copyconditionsform"
            || $subaction == "copyconditions"
        ) {
            $aViewUrls['output'] .= $getCopyForm($qid, $gid, $conditionsList, $pquestions);
        }

        if (
            $subaction == "editconditionsform"
            || $subaction == "insertcondition"
            || $subaction == "updatecondition"
            || $subaction == "deletescenario"
            || $subaction == "renumberscenarios"
            || $subaction == "deleteallconditions"
            || $subaction == "updatescenario"
            || $subaction == "editthiscondition"
            || $subaction == "delete"
        ) {
            $aViewUrls['output'] .= $getEditConditionForm($args);
        }

        // Top Bar
        $aData['topbar']['middleButtons'] = $renderPartial(
            'leftSideButtons',
            ['aData' => $aData],
            true
        );
        $aData['topbar']['rightButtons'] = $renderPartial(
            'rightSideButtons',
            ['aData' => $aData],
            true
        );
        $aData['conditionsoutput'] = $aViewUrls['output'] ?? '';
        return [
            'aData' => $aData,
            'aViewUrls' => $aViewUrls
        ];
        // TMSW Condition->Relevance:  Must call LEM->ConvertConditionsToRelevance() whenever Condition is added or updated - what is best location for that action?
    }

    public function getScenariosAndConditionsOfQuestion($qid)
    {
        $results = [];
        $keys = [];
        $conditions = \Condition::model()->findAllByAttributes(["qid" => $qid], ['order' => 'scenario']);
        foreach ($conditions as $condition) {
            if (!isset($keys[$condition->scenario])) {
                $keys[$condition->scenario] = count($results);
                $results [] = [
                    "scid" => $condition->scenario,
                    "conditions" => []
                ];
            }
            $results[$keys[$condition->scenario]]["conditions"] [] = [
                "cid" => $condition->cid,
                "qid" => $qid,
                "cqid" => $condition->cqid,
                "cfieldname" => $condition->cfieldname,
                "method" => $condition->method,
                "value" => $condition->value
            ];
        }
        return $results;
    }

    /**
     * Helper function to render form.
     * Used by create and edit actions.
     *
     * @param \Question $question Question
     * @param callable $render
     * @return string
     * @throws \CException
     * @todo Move to service class
     */
    protected function renderFormAux(\Question $question)
    {
        \LimeExpressionManager::SetSurveyId($question->sid);
        \LimeExpressionManager::StartProcessingPage(false, true);
        \LimeExpressionManager::ProcessString(
            "{" . trim((string) $question->relevance) . "}",
            $question->qid
        );
        return \viewHelper::stripTagsEM(\LimeExpressionManager::GetLastPrettyPrintExpression());
    }

    /**
     * Gets the condition text based on a qid
     * @param \Question $question
     * @return string
     */
    public function getConditionText(\Question $question)
    {
        return $this->renderFormAux($question);
    }
}
