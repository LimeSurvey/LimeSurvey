<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerExceptionTrait,
    OpHandlerSurveyTrait,
    OpHandlerValidationTrait
};
use LimeSurvey\Models\Services\{
    Exception\NotFoundException,
    Exception\PermissionDeniedException,
    SurveyCondition,
    Exception\PersistErrorException
};
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeCreate,
    OpType\OpTypeUpdate,
    OpType\OpTypeDelete
};

class OpHandlerQuestionCondition implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerValidationTrait;
    use OpHandlerExceptionTrait;

    protected string $entity;

    protected SurveyCondition $surveyCondition;

    protected $permissionMap = [];

    public function __construct(
        SurveyCondition $surveyCondition
    ) {
        $this->entity = 'questionCondition';
        $this->surveyCondition = $surveyCondition;
    }

    public function canHandle(OpInterface $op): bool
    {
        return $op->getEntityType() === $this->entity;
    }

    public function transformAll($collection, $options = [])
    {
        return $collection;
    }

    public function message($message, $type = 'success')
    {
        //dummy method at this point, because we do not support success messages yet
        if ($type !== 'success') {
            throw new \Exception($message);
        }
    }

    /**
     * Updates multiple attributes for a single question. Format is exactly the
     * same as in Question create, so they share the prepare function.
     *
     * renumberScenarios:
     * {
     *     "patch": [{
     *             "entity": "questionCondition",
     *             "op": "update",
     *             "id": 809,
     *             "props": {
     *                 "qid": 15977,
     *                 "action": "renumberScenarios"
     *             }
     *         }
     *     ]
     * }
     * copyConditions:
     * {
     *     "patch": [{
     *             "entity": "questionCondition",
     *             "op": "create",
     *             "id": 809,
     *             "props": {
     *                 "qid": 15977,
     *                 "fromqid": 15976,
     *                 "action": "copyConditions"
     *             }
     *         }
     *     ]
     * }
     * deleteAllConditions:
     * {
     *     "patch": [{
     *             "entity": "questionCondition",
     *             "op": "delete",
     *             "id": 809,
     *             "props": {
     *                 "sid": 1234,
     *                 "action": "deleteAllConditionsOfSurvey"
     *             }
     *         }
     *     ]
     * }
     * deleteAllConditions:
     * {
     *     "patch": [{
     *             "entity": "questionCondition",
     *             "op": "delete",
     *             "id": 809,
     *             "props": {
     *                 "qid": 15977,
     *                 "action": "deleteAllConditions"
     *             }
     *         }
     *     ]
     * }
     * updateScenario:
     * {
     *     "patch": [{
     *             "entity": "questionCondition",
     *             "op": "update",
     *             "id": 809,
     *             "props": {
     *                 "qid": 15977,
     *                 "scenarios": [
     *                     {
     *                         "scid": 3,
     *                         "action": "updateScenario",
     *                         "scenarioNumber": 123
     *                     }
     *                 ]
     *             }
     *         }
     *     ]
     * }
     * deleteScenario:
     * {
     *     "patch": [{
     *             "entity": "questionCondition",
     *             "op": "delete",
     *             "id": 809,
     *             "props": {
     *                 "qid": 15977,
     *                 "scenarios": [
     *                     {
     *                         "scid": 3,
     *                         "action": "deleteScenario"
     *                     }
     *                 ]
     *             }
     *         }
     *     ]
     * }
     * insertCondition (field-constant):
     * {
     *     "patch": [{
     *             "entity": "questionCondition",
     *             "op": "create",
     *             "id": 809,
     *             "props": {
     *                 "qid": 15977,
     *                 "scenarios": [
     *                     {
     *                         "scid": 3,
     *                         "conditions": [
     *                             {
     *                                 "action":"insertCondition",
     *                                 "method":"==",
     *                                 "csrctoken":"{TOKEN:LASTNAME}",
     *                                 "ConditionConst":"sdfsdf",
     *                                 "ConditionRegexp":"",
     *                                 "cqid":0,
     *                                 "canswersToSelect":"",
     *                                 "editSourceTab":"#SRCTOKENATTRS",
     *                                 "editTargetTab":"#CONST"
     *                             }
     *                         ]
     *                     }
     *                 ]
     *             }
     *         }
     *     ]
     * }
     * insertCondition (field-field):
     * {
     *     "patch": [{
     *             "entity": "questionCondition",
     *             "op": "create",
     *             "id": 809,
     *             "props": {
     *                 "qid": 15977,
     *                 "scenarios": [
     *                     {
     *                         "scid": 3,
     *                         "conditions": [
     *                             {
     *                                 "action":"insertCondition",
     *                                 "method":"==",
     *                                 "csrctoken":"{TOKEN:FIRSTNAME}",
     *                                 "ConditionConst":"",
     *                                 "tokenAttr":"{TOKEN:TOKEN}",
     *                                 "ConditionRegexp":"",
     *                                 "cqid":0,
     *                                 "canswersToSelect":"",
     *                                 "editSourceTab":"#SRCTOKENATTRS",
     *                                 "editTargetTab":"#TOKENATTRS"
     *                             }
     *                         ]
     *                     }
     *                 ]
     *             }
     *         }
     *     ]
     * }
     * insertCondition (prevq-predef):
     * {
     *     "patch": [{
     *             "entity": "questionCondition",
     *             "op": "create",
     *             "id": 809,
     *             "props": {
     *                 "qid": 15977,
     *                 "scenarios": [
     *                     {
     *                         "scid": 3,
     *                         "conditions": [
     *                             {
     *                                 "action":"insertCondition",
     *                                 "method":"==",
     *                                 "cquestions":"453614X608X15979",
     *                                 "csrctoken":"{TOKEN:EMAIL}",
     *                                 "canswers":[""],
     *                                 "ConditionConst":"",
     *                                 "ConditionRegexp":"",
     *                                 "cqid":15979,
     *                                 "canswersToSelect":"",
     *                                 "editSourceTab":"#SRCPREVQUEST",
     *                                 "editTargetTab":"#CANSWERSTAB"
     *                             }
     *                         ]
     *                     }
     *                 ]
     *             }
     *         }
     *     ]
     * }
     * insertCondition (prevq-const):
     * {
     *     "patch": [{
     *             "entity": "questionCondition",
     *             "op": "create",
     *             "id": 809,
     *             "props": {
     *                 "qid": 15977,
     *                 "scenarios": [
     *                     {
     *                         "scid": 3,
     *                         "conditions": [
     *                             {
     *                                 "action":"insertCondition",
     *                                 "method":"==",
     *                                 "cquestions":"453614X608X15978",
     *                                 "ConditionConst":"my only virtue is modesty",
     *                                 "ConditionRegexp":"",
     *                                 "cqid":15978,
     *                                 "canswersToSelect":"",
     *                                 "editSourceTab":"#SRCPREVQUEST",
     *                                 "editTargetTab":"#CONST"
     *                             }
     *                         ]
     *                     }
     *                 ]
     *             }
     *         }
     *     ]
     * }
     * insertCondition (prevq-prevq)
     * {
     *     "patch": [{
     *         "op": "create",
     *         "entity": "questionCondition",
     *         "error": false,
     *         "props": {
     *             "qid": 15977,
     *             "scenarios": [
     *                 {
     *                     "scid": 123,
     *                     "conditions": [
     *                         {
     *                             "action": "insertCondition",
     *                             "method": "==",
     *                             "cquestions": "453614X608X15979",
     *                             "prevQuestionSGQA": "@453614X608X15982@",
     *                             "ConditionRegexp": "",
     *                             "cqid": 15979,
     *                             "canswersToSelect": "",
     *                             "editSourceTab": "#SRCPREVQUEST",
     *                             "editTargetTab": "#PREVQUESTIONS"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }}
     *     ]
     * }
     * insertCondition (prevq-field)
     * {
     *     "patch": [{
     *         "op": "create",
     *         "entity": "questionCondition",
     *         "error": false,
     *         "props": {
     *             "qid": 15977,
     *             "scenarios": [
     *                 {
     *                     "scid": 123,
     *                     "conditions": [
     *                         {
     *                             "action": "insertCondition",
     *                             "method": "==",
     *                             "cquestions": "453614X608X15979",
     *                             "ConditionConst": "",
     *                             "tokenAttr":"{TOKEN:FIRSTNAME}",
     *                             "prevQuestionSGQA": "@453614X608X15979@",
     *                             "ConditionRegexp": "",
     *                             "cqid": 15979,
     *                             "canswersToSelect": "",
     *                             "editSourceTab": "#SRCPREVQUEST",
     *                             "editTargetTab": "#TOKENATTRS"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }}
     *     ]
     * }
     * insertCondition (field-regex)
     * {
     *     "patch": [{
     *         "op": "create",
     *         "entity": "questionCondition",
     *         "error": false,
     *         "props": {
     *             "qid": 15977,
     *             "scenarios": [
     *                 {
     *                     "scid": 123,
     *                     "conditions": [
     *                         {
     *                             "action": "insertCondition",
     *                             "method": "RX",
     *                             "cquestions": "453614X608X15979",
     *                             "csrctoken": "{TOKEN:FIRSTNAME}",
     *                             "ConditionConst": "",
     *                             "ConditionRegexp": "La*",
     *                             "cqid": 0,
     *                             "canswersToSelect": "",
     *                             "editSourceTab": "#SRCTOKENATTRS",
     *                             "editTargetTab": "#REGEXP"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }}
     *     ]
     * }
     * insertCondition (prevq-regex)
     * {
     *     "patch": [{
     *         "op": "create",
     *         "entity": "questionCondition",
     *         "error": false,
     *         "props": {
     *             "qid": 15977,
     *             "scenarios": [
     *                 {
     *                     "scid": 123,
     *                     "conditions": [
     *                         {
     *                             "action": "insertCondition",
     *                             "method": "RX",
     *                             "cquestions": "453614X608X15979",
     *                             "ConditionConst": "",
     *                             "ConditionRegexp": "La*",
     *                             "cqid": 15978,
     *                             "canswersToSelect": "",
     *                             "editSourceTab": "#SRCPREVQUEST",
     *                             "editTargetTab": "#REGEXP"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }}
     *     ]
     * }
     * updateCondition (prevq-regex)
     * {
     *     "patch": [{
     *         "op": "update",
     *         "entity": "questionCondition",
     *         "error": false,
     *         "props": {
     *             "qid": 15977,
     *             "scenarios": [
     *                 {
     *                     "scid": 123,
     *                     "conditions": [
     *                         {
     *                             "cid": 2601,
     *                             "action": "updateCondition",
     *                             "method": "<=",
     *                             "cquestions": "453614X608X15979",
     *                             "canswers": ["A4988"]
     *                             "ConditionConst": "",
     *                             "ConditionRegexp": "",
     *                             "cqid": 15978,
     *                             "canswersToSelect": "A4988",
     *                             "editSourceTab": "#SRCPREVQUEST",
     *                             "editTargetTab": "#CANSWERSTAB"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }}
     *     ]
     * }
     * updateCondition (prevq-const)
     * {
     *     "patch": [{
     *         "op": "update",
     *         "entity": "questionCondition",
     *         "error": false,
     *         "props": {
     *             "qid": 15977,
     *             "scenarios": [
     *                 {
     *                     "scid": 123,
     *                     "conditions": [
     *                         {
     *                             "cid": 2601,
     *                             "action": "updateCondition",
     *                             "method": "<=",
     *                             "cquestions": "453614X608X15979",
     *                             "ConditionConst": "test",
     *                             "ConditionRegexp": "",
     *                             "cqid": 15978,
     *                             "canswersToSelect": "",
     *                             "editSourceTab": "#SRCPREVQUEST",
     *                             "editTargetTab": "#CONST"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }}
     *     ]
     * }
     * updateCondition (prevq-const)
     * {
     *     "patch": [{
     *         "op": "update",
     *         "entity": "questionCondition",
     *         "error": false,
     *         "props": {
     *             "qid": 15977,
     *             "scenarios": [
     *                 {
     *                     "scid": 123,
     *                     "conditions": [
     *                         {
     *                             "cid": 2601,
     *                             "action": "updateCondition",
     *                             "method": "<=",
     *                             "cquestions": "453614X608X15979",
     *                             "ConditionConst": "test",
     *                             "prevQuestionSGQA":"@453614X608X15978@",
     *                             "ConditionRegexp": "",
     *                             "cqid": 15978,
     *                             "canswersToSelect": "",
     *                             "editSourceTab": "#SRCPREVQUEST",
     *                             "editTargetTab": "#PREVQUESTIONS"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }}
     *     ]
     * }
     * updateCondition (prevq-field)
     * {
     *     "patch": [{
     *         "op": "update",
     *         "entity": "questionCondition",
     *         "error": false,
     *         "props": {
     *             "qid": 15977,
     *             "scenarios": [
     *                 {
     *                     "scid": 123,
     *                     "conditions": [
     *                         {
     *                             "cid": 2601,
     *                             "action": "updateCondition",
     *                             "method": "<=",
     *                             "cquestions": "453614X608X15979",
     *                             "ConditionConst": "",
     *                             "prevQuestionSGQA":"@453614X608X15978@",
     *                             "tokenAttr":"{TOKEN:LASTNAME}",
     *                             "ConditionRegexp": "",
     *                             "cqid": 15978,
     *                             "canswersToSelect": "",
     *                             "editSourceTab": "#SRCPREVQUEST",
     *                             "editTargetTab": "#TOKENATTRS"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }}
     *     ]
     * }
     * updateCondition (prevq-regex)
     * {
     *     "patch": [{
     *         "op": "update",
     *         "entity": "questionCondition",
     *         "error": false,
     *         "props": {
     *             "qid": 15977,
     *             "scenarios": [
     *                 {
     *                     "scid": 123,
     *                     "conditions": [
     *                         {
     *                             "cid": 2601,
     *                             "action": "updateCondition",
     *                             "method": "RX",
     *                             "cquestions": "453614X608X15979",
     *                             "ConditionConst": "",
     *                             "prevQuestionSGQA":"@453614X608X15978@",
     *                             "ConditionRegexp": "def",
     *                             "cqid": 15978,
     *                             "canswersToSelect": "",
     *                             "editSourceTab": "#SRCPREVQUEST",
     *                             "editTargetTab": "#REGEXP"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }}
     *     ]
     * }
     * updateCondition (field-constant)
     * {
     *     "patch": [{
     *         "op": "update",
     *         "entity": "questionCondition",
     *         "error": false,
     *         "props": {
     *             "qid": 15977,
     *             "scenarios": [
     *                 {
     *                     "scid": 123,
     *                     "conditions": [
     *                         {
     *                             "cid": 2601,
     *                             "action": "updateCondition",
     *                             "method": "==",
     *                             "ConditionConst": "ABCDE",
     *                             "ConditionRegexp": "",
     *                             "cqid": 15978,
     *                             "canswersToSelect": "",
     *                             "editSourceTab": "#SRCTOKENATTRS",
     *                             "editTargetTab": "#CONST"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }}
     *     ]
     * }
     * updateCondition (field-prevq)
     * {
     *     "patch": [{
     *         "op": "update",
     *         "entity": "questionCondition",
     *         "error": false,
     *         "props": {
     *             "qid": 15977,
     *             "scenarios": [
     *                 {
     *                     "scid": 123,
     *                     "conditions": [
     *                         {
     *                             "cid": 2601,
     *                             "action": "updateCondition",
     *                             "method": "==",
     *                             "csrctoken": "{TOKEN:LASTNAME}",
     *                             "ConditionConst": "",
     *                             "prevQuestionSGQA": "@453614X608X15978@",
     *                             "ConditionRegexp": "",
     *                             "cqid": 0,
     *                             "canswersToSelect": "",
     *                             "editSourceTab": "#SRCTOKENATTRS",
     *                             "editTargetTab": "#PREVQUESTIONS"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }}
     *     ]
     * }
     * updateCondition (field-regex)
     * {
     *     "patch": [{
     *         "op": "update",
     *         "entity": "questionCondition",
     *         "error": false,
     *         "props": {
     *             "qid": 15977,
     *             "scenarios": [
     *                 {
     *                     "scid": 123,
     *                     "conditions": [
     *                         {
     *                             "cid": 2601,
     *                             "action": "updateCondition",
     *                             "method": "RX",
     *                             "csrctoken": "{TOKEN:LASTNAME}",
     *                             "ConditionConst": "",
     *                             "ConditionRegexp": "abc",
     *                             "cqid": 0,
     *                             "canswersToSelect": "",
     *                             "editSourceTab": "#SRCTOKENATTRS",
     *                             "editTargetTab": "#REGEXP"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }}
     *     ]
     * }
     * deleteCondition
     * {
     *     "patch": [{
     *         "op": "delete",
     *         "entity": "questionCondition",
     *         "error": false,
     *         "props": {
     *             "qid": 15977,
     *             "scenarios": [
     *                 {
     *                     "scid": 123,
     *                     "conditions": [
     *                         {
     *                             "cid": 2601,
     *                             "action": "deleteCondition"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }}
     *     ]
     * }
     *
     *
     * @param OpInterface $op
     * @return void
     * @throws OpHandlerException
     * @throws PersistErrorException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     */
    public function handle(OpInterface $op): void
    {
        $qid = $op->getProps()['qid'] ?? '';
        if (isset($op->getProps()['action'])) {
            $action = $op->getProps()['action'];
            switch ($action) {
                case "deleteAllConditions":
                    if ($op->getType()->getId() !== OpTypeDelete::ID) {
                        throw new \Exception("Incompatible op with the action");
                    }
                    $this->surveyCondition->deleteAllConditions($qid, $this->message(...));
                    break;
                case "renumberScenarios":
                    if ($op->getType()->getId() !== OpTypeUpdate::ID) {
                        throw new \Exception("Incompatible op with the action");
                    }
                    $this->surveyCondition->renumberScenarios($qid, $this->message(...));
                    break;
                case "copyConditions":
                    if ($op->getType()->getId() !== OpTypeCreate::ID) {
                        throw new \Exception("Incompatible op with the action");
                    }
                    $question = \Question::model()->findByPk($qid);
                    $this->surveyCondition->copyConditions($this->surveyCondition->getCidsOfQid($op->getProps()['fromqid']), [$this->surveyCondition->setISurveyID($question->sid)->getFieldName($question->sid, $question->gid, $question->qid)], $this->message(...));
                    break;
                case "deleteAllConditionsOfSurvey":
                    $this->surveyCondition->deleteAllConditionsOfSurvey($op->getProps()['sid'], $this->message(...));
                    break;
            }
        } else {
            foreach ($op->getProps()['scenarios'] as $scenario) {
                if (!isset($scenario['scid'])) {
                    throw new \Exception('scid not specified');
                }
                $scid = $scenario['scid'];
                if (isset($scenario['action'])) {
                    $action = $scenario['action'];
                    switch ($action) {
                        case "deleteScenario":
                            if ($op->getType()->getId() !== OpTypeDelete::ID) {
                                throw new \Exception("Incompatible op with the action");
                            }
                            $this->surveyCondition->deleteScenario($qid, $scid);
                            break;
                        case "updateScenario":
                            if ($op->getType()->getId() !== OpTypeUpdate::ID) {
                                throw new \Exception("Incompatible op with the action");
                            }
                            $this->surveyCondition->updateScenario($scenario['scenarioNumber'], $qid, $scid, $this->message(...));
                            break;
                    }
                } else {
                    foreach ($scenario['conditions'] as $condition) {
                        if (!isset($condition['action'])) {
                            throw new \Exception('action is not specified');
                        }
                        $action = $condition['action'];
                        switch ($action) {
                            case 'insertCondition':
                                $this->surveyCondition->insertCondition(
                                    [
                                    'p_cquestions' => $condition['cquestions'] ?? '',
                                    'p_csrctoken' => $condition['csrctoken'] ?? '',
                                    'qid' => $qid,
                                    'p_scenario' => $scid,
                                    'p_cqid' => $condition['cqid'] ?? 0,
                                    'conditionCfieldname' => $condition['fieldname'] ?? '',
                                    'p_method' => $condition['method'],
                                    'p_canswers' => $condition['canswers'] ?? [],
                                    ],
                                    $condition['editSourceTab'],
                                    $condition['editTargetTab'],
                                    $this->message(...),
                                    $condition['ConditionConst'] ?? '',
                                    $condition['prevQuestionSGQA'] ?? '',
                                    $condition['tokenAttr'] ?? '',
                                    $condition['ConditionRegexp'] ?? ''
                                );
                                break;
                            case 'updateCondition':
                                $this->surveyCondition->updateCondition(
                                    [
                                        'p_cquestions' => $condition['cquestions'] ?? '',
                                        'p_csrctoken' => $condition['csrctoken'] ?? '',
                                        'qid' => $qid,
                                        'p_scenario' => $scid,
                                        'p_cqid' => $condition['cqid'] ?? 0,
                                        'conditionCfieldname' => $condition['fieldname'] ?? '',
                                        'p_method' => $condition['method'],
                                        'p_canswers' => $condition['canswers'] ?? [],
                                        'p_cid' => $condition['cid']
                                        ],
                                    $condition['editTargetTab'],
                                    $this->message(...),
                                    $condition['ConditionConst'] ?? '',
                                    $condition['prevQuestionSGQA'] ?? '',
                                    $condition['tokenAttr'] ?? '',
                                    $condition['ConditionRegexp'] ?? ''
                                );
                                break;
                            case 'deleteCondition':
                                $this->surveyCondition->deleteCondition($qid, $condition['cid']);
                                break;
                        }
                    }
                }
            }
        }
    }

    protected function validateRenumberScenarios($props)
    {
        return true;
    }

    protected function validateCopyConditions($props)
    {
        return intval($props['fromqid']);
    }

    protected function validateDeleteAllConditions($props)
    {
        //At this point we have already checked everything we needed
        return true;
    }

    protected function validateDeleteScenario($scenario)
    {
        return true;
    }

    protected function validateUpdateScenario($scenario)
    {
        return intval($scenario['scenarioNumber'] ?? 0);
    }

    protected function validateInsertCondition($condition)
    {
        return
        isset($condition['method']) &&
        isset($condition['editSourceTab']) &&
        isset($condition['editTargetTab']);
    }

    protected function validateUpdateCondition($condition)
    {
        return
        intval($condition['cid'] ?? 0) &&
        isset($condition['method']) &&
        isset($condition['editTargetTab']);
    }

    protected function validateDeleteCondition($condition)
    {
        return
        intval($condition['cid'] ?? 0);
    }

    protected function validateDeleteAllConditionsOfSurvey($condition)
    {
        return intval($condition['sid']);
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return array
     */
    public function validateOperation(OpInterface $op): array
    {
        $props = $op->getProps();
        if ((!isset($props['qid'])) && (!isset($props['sid']))) {
            throw new \Exception("Question id is mandatory");
        }
        if (isset($props['action'])) {
            switch ($props['action']) {
                case 'renumberScenarios':
                    if (!$this->validateRenumberScenarios($props)) {
                        throw new \Exception("Cannot renumber scenarios");
                    }
                    $this->permissionMap['update'] = false;
                    break;
                case 'copyConditions':
                    if (!$this->validateCopyConditions($props)) {
                        throw new \Exception("Cannot copy conditions");
                    }
                    $sid = \Question::model()->findByPk($props['fromqid'])->sid;
                    if (!\Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'read')) {
                        throw new \Exception("Missing read permission from {$sid}");
                    }
                    $this->permissionMap['create'] = false;
                    break;
                case 'deleteAllConditions':
                    if (!$this->validateDeleteAllConditions($props)) {
                        throw new \Exception("Invalid operation");
                    }
                    $this->permissionMap['delete'] = false;
                    break;
                case 'deleteAllConditionsOfSurvey':
                    if (!$this->validateDeleteAllConditionsOfSurvey($props)) {
                        throw new \Exception("Invalid operation");
                    }
                    if (!\Permission::model()->hasSurveyPermission($props['sid'], 'surveycontent', 'delete')) {
                        throw new \Exception("Missing delete permission from {$props['sid']}");
                    }
                    $this->permissionMap['delete'] = false;
                    break;
            }
        } else {
            if (!isset($props['scenarios'])) {
                throw new \Exception("No action for the scenarios");
            } else {
                foreach ($props['scenarios'] as $scenario) {
                    if (isset($scenario['action'])) {
                        switch ($scenario['action']) {
                            case "deleteScenario":
                                if (!$this->validateDeleteScenario($scenario)) {
                                    throw new \Exception("Cannot delete scenario");
                                }
                                $this->permissionMap['delete'] = false;
                                break;
                            case "updateScenario":
                                if (!$this->validateUpdateScenario($scenario)) {
                                    throw new \Exception("Cannot update scenario");
                                }
                                $this->permissionMap['update'] = false;
                                break;
                        }
                    } else {
                        if (!isset($scenario['conditions'])) {
                            throw new \Exception('No action for scenario');
                        } else {
                            foreach ($scenario['conditions'] as $condition) {
                                switch ($condition['action']) {
                                    case "insertCondition":
                                        if (!$this->validateInsertCondition($condition)) {
                                            throw new \Exception("Cannot create condition");
                                        }
                                        $this->permissionMap['create'] = false;
                                        break;
                                    case "updateCondition":
                                        if (!$this->validateUpdateCondition($condition)) {
                                            throw new \Exception("Cannot update condition");
                                        }
                                        $this->permissionMap['update'] = false;
                                        break;
                                    case "deleteCondition":
                                        if (!$this->validateDeleteCondition($condition)) {
                                            throw new \Exception("Cannot delete condition");
                                        }
                                        $this->permissionMap['delete'] = false;
                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }
        $qid = $op->getProps()['qid'] ?? '';
        if ($qid) {
            $question = \Question::model()->findByPk($qid);
            foreach ($this->permissionMap as $permission => $value) {
                if (!\Permission::model()->hasSurveyPermission($question->sid, 'surveycontent', $permission)) {
                    throw new \Exception("Missing {$permission} permission from {$question->sid}");
                }
            }
        }
        return [];
    }
}
