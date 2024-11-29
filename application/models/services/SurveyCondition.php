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

    public function getSurveyTable($name, $id)
    {
        switch ($name) {
            case 'token':
                return "{{tokens_$id}}";
            default:
                return '';
        }
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

    public function resetSurveyLogic()
    {
        \LimeExpressionManager::RevertUpgradeConditionsToRelevance($this->iSurveyID);
        \Condition::model()->deleteRecords("qid in (select qid from {{questions}} where sid={$this->iSurveyID})");
    }

    public function insertCondition(array $args, $editSourceTab, $editTargetTab, callable $f, $ConditionConst, $prevQuestionSGQA, $tokenAttr, $ConditionRegexp)
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
                $f(gT('Could not insert all conditions.'), 'error');
            } elseif (!empty($results)) {
                $f(gT('Condition added.'), 'success');
            } else {
                $f(
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
                $f(gT('Condition added.'), 'success');
            } else {
                if ($result === false) {
                    $f(gT('Could not insert all conditions.'), 'error');
                } else {
                    $f(gT("The condition could not be added! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer."), 'error');
                }
            }
        }
        \LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
    }

    public function updateCondition(array $args, $editTargetTab, callable $f, $ConditionConst, $prevQuestionSGQA, $tokenAttr, $ConditionRegexp)
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
                $f(gT('Could not update condition.'), 'error');
            } elseif (!empty($results)) {
                $f(gT('Condition updated.'), 'success');
            } else {
                $f(gT('Could not update condition.'), 'error');
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
                $f(gT('Condition updated.'), 'success');
            } else {
                if ($result === false) {
                    $f(gT('Could not update condition.'), 'error');
                } else {
                    $f(gT("The condition could not be updated! It did not include the question and/or answer upon which the condition was based. Please ensure you have selected a question and an answer."), 'error');
                }
            }
        }

        \LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
    }

    public function deleteCondition($qid, $p_cid)
    {
        \LimeExpressionManager::RevertUpgradeConditionsToRelevance(null, $qid); // in case deleted the last condition
        \Condition::model()->deleteRecords(array('cid' => $p_cid));
        \LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
    }

    public function updateScenario($p_newscenarionum, $qid, $p_scenario, callable $f)
    {
        if ($p_newscenarionum === null) {
            $f(gT("No scenario number specified"), 'error');
        } else {
            \Condition::model()->insertRecords(array('scenario' => $p_newscenarionum), true, array(
                'qid' => $qid, 'scenario' => $p_scenario));
            \LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
        }
    }

    public function deleteAllConditions($qid, callable $f)
    {
        \LimeExpressionManager::RevertUpgradeConditionsToRelevance(null, $qid); // in case deleted the last condition
        \Condition::model()->deleteRecords(array('qid' => $qid));
        $f(gT("All conditions for this question have been deleted."), 'success');
    }

    public function deleteScenario($qid, $p_scenario)
    {
        \LimeExpressionManager::RevertUpgradeConditionsToRelevance(null, $qid); // in case deleted the last condition
        \Condition::model()->deleteRecords(array('qid' => $qid, 'scenario' => $p_scenario));
        \LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
    }

    public function renumberScenarios(array $args, callable $f)
    {
        /** @var string $p_cid */
        extract($args);

        $query = "SELECT DISTINCT scenario FROM {{conditions}} WHERE qid=:qid ORDER BY scenario";
        $result = $this->app->db->createCommand($query)->bindParam(":qid", $qid, \PDO::PARAM_INT)->query() or safeDie("Couldn't select scenario<br />$query<br />");
        $newindex = 1;

        foreach ($result->readAll() as $srow) {
            \Condition::model()->insertRecords(array('scenario' => $newindex), true, array('qid' => $qid, 'scenario' => $srow['scenario']));
            $newindex++;
        }
        \LimeExpressionManager::UpgradeConditionsToRelevance(null, $qid);
        $f(gT("All conditions scenarios were renumbered."));
    }

    public function copyConditions(array $args, callable $f)
    {
        extract($args);

        $copyconditionsfrom = returnGlobal('copyconditionsfrom');
        $copyconditionsto = returnGlobal('copyconditionsto');
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
                list(,, $newqid) = explode("X", (string) $copyc);
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
                    $f(gT("Condition successfully copied (some were skipped because they were duplicates)"), 'warning');
                } else {
                    $f(gT("Condition successfully copied"));
                }
            } else {
                $f(gT("No conditions could be copied (due to duplicates)"), 'error');
            }
        }
        \LimeExpressionManager::UpgradeConditionsToRelevance($this->iSurveyID); // do for whole survey, since don't know which questions affected.
    }
}
