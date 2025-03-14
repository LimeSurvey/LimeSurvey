<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerExceptionTrait,
    OpHandlerSurveyTrait,
    OpHandlerValidationTrait
};
use LimeSurvey\Api\Command\V1\SurveyPatch\Response\TempIdMapItem;
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

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */

class OpHandlerQuestionCondition implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerValidationTrait;
    use OpHandlerExceptionTrait;

    protected string $entity;

    protected SurveyCondition $surveyCondition;

    protected array $permissionMap = [];

    /**
     * Constructor
     * @param \LimeSurvey\Models\Services\SurveyCondition $surveyCondition the survey condition service object
     */
    public function __construct(
        SurveyCondition $surveyCondition
    ) {
        $this->entity = 'questionCondition';
        $this->surveyCondition = $surveyCondition;
    }

    /**
     * Detemines whether the action can be handled
     * @param \LimeSurvey\ObjectPatch\Op\OpInterface $op the operation
     * @return bool whether the action can be handled
     */
    public function canHandle(OpInterface $op): bool
    {
        return $op->getEntityType() === $this->entity;
    }

    /**
     * Since the service class depends on an output functionality due to legacy reasons, we have here a message function that will be used as a callback
     * @param string $message the actual message
     * @param string $type the type of the message, such as success, warning or error
     * @throws \Exception
     * @return void
     */
    public function setFlashMessage(string $message, string $type = 'success')
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
     * deleteAllConditionsOfSurvey:
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
     * conditionScript:
     * {
     *     "patch": [{
     *             "entity": "questionCondition",
     *             "op": "update",
     *             "id": 809,
     *             "props": {
     *                 "qid": 15977,
     *                 "action": "conditionScript",
     *                 "script":"((TOKEN:LASTNAME == \"pomegrenade\"))"
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
     *                                 "tempcids":["temp_0004"],
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
     *                                 "tempcids":["temp_0004"],
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
     *                                 "canswers":["A01","A02","A03"],
     *                                 "ConditionConst":"",
     *                                 "ConditionRegexp":"",
     *                                 "cqid":15979,
     *                                 "canswersToSelect":"",
     *                                 "tempcids":["temp_0004","temp_0008","temp_00012"],
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
     *                                 "tempcids":["temp_0004"],
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
     *                             "tempcids":["temp_0004"],
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
     *                             "tempcids":["temp_0004"],
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
     *                             "tempcids":["temp_0004"],
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
     *                             "tempcids":["temp_0004"],
     *                             "editSourceTab": "#SRCPREVQUEST",
     *                             "editTargetTab": "#REGEXP"
     *                         }
     *                     ]
     *                 }
     *             ]
     *         }}
     *     ]
     * }
     * updateCondition (prevq-answer)
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
     *                             "canswers": ["A4988"],
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
     * updateCondition (prevq-prevq)
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
     *                             "csrctoken": "{TOKEN:LASTNAME}",
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
     *             "qid": 32,
     *             "scenarios": [
     *                 {
     *                     "scid": 9,
     *                     "conditions": [
     *                         {
     *                             "cid": 65,
     *                             "action": "updateCondition",
     *                             "method": "==",
     *                             "csrctoken": "{TOKEN:FIRSTNAME}",
     *                             "ConditionConst": "",
     *                             "prevQuestionSGQA": "@543869X1X1@",
     *                             "cquestions":"543869X1X1",
     *                             "ConditionRegexp": "",
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     * @throws OpHandlerException
     * @throws PersistErrorException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     */
    public function handle(OpInterface $op): array
    {
        $qid = $op->getProps()['qid'] ?? '';
        $mapping = [];
        if (isset($op->getProps()['action'])) {
            $action = $op->getProps()['action'];
            switch ($action) {
                case "deleteAllConditions":
                    if ($op->getType()->getId() !== OpTypeDelete::ID) {
                        throw new \Exception("Incompatible op with the action");
                    }
                    $this->surveyCondition->deleteAllConditions(intval($qid), $this);
                    break;
                case "renumberScenarios":
                    if ($op->getType()->getId() !== OpTypeUpdate::ID) {
                        throw new \Exception("Incompatible op with the action");
                    }
                    $this->surveyCondition->renumberScenarios(intval($qid), $this);
                    break;
                case "copyConditions":
                    if ($op->getType()->getId() !== OpTypeCreate::ID) {
                        throw new \Exception("Incompatible op with the action");
                    }
                    $question = \Question::model()->findByPk($qid);
                    $this->surveyCondition->copyConditions($this->surveyCondition->getCidsOfQid($op->getProps()['fromqid']), [$this->surveyCondition->setISurveyID(intval($question->sid ?? 0))->getFieldName($question->sid, intval($question->gid ?? 0), intval($question->qid ?? 0))], $this);
                    break;
                case "deleteAllConditionsOfSurvey":
                    $this->surveyCondition->deleteAllConditionsOfSurvey($op->getProps()['sid'], $this);
                    break;
                case "conditionScript":
                    $this->surveyCondition->conditionScript((int)$qid, $op->getProps()['script']);
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
                            $this->surveyCondition->deleteScenario((int)$qid, $scid);
                            break;
                        case "updateScenario":
                            if ($op->getType()->getId() !== OpTypeUpdate::ID) {
                                throw new \Exception("Incompatible op with the action");
                            }
                            $this->surveyCondition->updateScenario($scenario['scenarioNumber'], (int)$qid, $scid, $this);
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
                                $cids = $this->surveyCondition->insertCondition(
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
                                    $this,
                                    $condition['ConditionConst'] ?? '',
                                    $condition['prevQuestionSGQA'] ?? '',
                                    $condition['tokenAttr'] ?? '',
                                    $condition['ConditionRegexp'] ?? '',
                                    $condition['tempcids']
                                );
                                if (!isset($mapping['conditionsMap'])) {
                                    $mapping['conditionsMap'] = [];
                                }
                                foreach ($cids as $key => $value) {
                                    $mapping['conditionsMap'][] = new TempIdMapItem(
                                        $key,
                                        $value,
                                        'cid'
                                    );
                                }
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
                                        'p_cid' => $condition['cid'],
                                        ],
                                    $condition['editTargetTab'],
                                    $this,
                                    $condition['ConditionConst'] ?? '',
                                    $condition['prevQuestionSGQA'] ?? '',
                                    $condition['tokenAttr'] ?? '',
                                    $condition['ConditionRegexp'] ?? ''
                                );
                                break;
                            case 'deleteCondition':
                                $this->surveyCondition->deleteCondition(intval($qid), (int)$condition['cid']);
                                break;
                        }
                    }
                }
            }
        }
        $return = [];
        if ($qid) {
            $question = \Question::model()->findByPk($qid);
            $return['additional'] = [
                'relevance' => $question->relevance ?? 1,
                'qid' => $qid
            ];
        }
        if (!empty($mapping)) {
            $return['tempIdMapping'] = $mapping;
        }
        return $return;
    }

    /**
     * Validates the renumberScenarios action
     * @param array $props the properties received
     * @return bool whether the action is valid
     */
    protected function validateRenumberScenarios(array $props)
    {
        return true;
    }

    /**
     * Validates the copyConditions action
     * @param array $props the properties received
     * @return bool whether the action is valid
     */
    protected function validateCopyConditions(array $props)
    {
        return !!intval($props['fromqid']);
    }

    /**
     * Validates the deleteAllConditions action
     * @param array $props the properties received
     * @return bool whether the action is valid
     */
    protected function validateDeleteAllConditions(array $props)
    {
        //At this point we have already checked everything we needed
        return true;
    }

    /**
     * Validates the deleteScenario action
     * @param array $scenario the scenario
     * @return bool whether the action is valid
     */
    protected function validateDeleteScenario(array $scenario)
    {
        return true;
    }

    /**
     * Validates the updateScenario action
     * @param array $scenario the scenario
     * @return bool whether the action is valid
     */
    protected function validateUpdateScenario(array $scenario)
    {
        return !!intval($scenario['scenarioNumber'] ?? 0);
    }

    /**
     * Validates the insertCondition action
     * @param array $condition the condition
     * @return bool whether the action is valid
     */
    protected function validateInsertCondition(array $condition)
    {
        return
        isset($condition['method']) &&
        isset($condition['editSourceTab']) &&
        isset($condition['editTargetTab']) &&
        isset($condition['tempcids']);
    }

    /**
     * Validates the updateCondition action
     * @param array $condition the condition
     * @return bool whether the action is valid
     */
    protected function validateUpdateCondition(array $condition)
    {
        return
        intval($condition['cid'] ?? 0) &&
        isset($condition['method']) &&
        isset($condition['editTargetTab']);
    }

    /**
     * Validates the deleteCondition action
     * @param array $condition the condition
     * @return bool whether the action is valid
     */
    protected function validateDeleteCondition(array $condition)
    {
        return
        !!intval($condition['cid'] ?? 0);
    }

    /**
     * Validates the deleteAllConditionsOfSurvey action
     * @param array $condition the condition
     * @return bool whether the action is valid
     */
    protected function validateDeleteAllConditionsOfSurvey(array $condition)
    {
        return !!intval($condition['sid']);
    }

    /**
     * Validates the conditionScript action
     * @param array $props the properties
     * @return bool whether the action is valid
     */
    protected function validateConditionScript(array $props)
    {
        return isset($props['script']);
    }

    /**
     * Checks if patch is valid for this operation.
     * We support three kinds of patches:
     * - general, where we do someting to all conditions related to a question or a survey
     * - scenario-based, where we do actions for scenarios inside the scenarios array
     * - condition-based, where we do actions for conditions in the condition arrays of the scenarios in the scenarios array
     * @param OpInterface $op the operation
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array the validation responses
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
                    $sid = \Question::model()->findByPk($props['fromqid'])->sid ?? 0;
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
                case "conditionScript":
                    if (!$this->validateConditionScript($props)) {
                        throw new \Exception("Cannot update condition script");
                    }
                    $this->permissionMap['update'] = false;
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
                $sid = ($question->sid ?? 0);
                if (!\Permission::model()->hasSurveyPermission($sid, 'surveycontent', $permission)) {
                    throw new \Exception("Missing {$permission} permission from {$question->sid}");
                }
            }
        }
        return [];
    }
}
