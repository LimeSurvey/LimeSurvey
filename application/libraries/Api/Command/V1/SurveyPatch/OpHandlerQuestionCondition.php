<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionCondition;
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

    protected TransformerInputQuestionCondition $transformer;

    public function __construct(
        SurveyCondition $surveyCondition,
        TransformerInputQuestionCondition $transformer
    ) {
        $this->entity = 'questionCondition';
        $this->surveyCondition = $surveyCondition;
        $this->transformer = $transformer;
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
        $qid = $op->getProps()['qid'];
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
                }
            }
        }
        //$preparedData = $this->transformer->transformAll($op->getProps());
        /*if (empty($preparedData)) {
            $this->throwNoValuesException($op);
        }*/
        /*$questionId = $op->getEntityId();
        $this->attributesService->saveAdvanced(
            $this->questionService->getQuestionBySidAndQid(
                $surveyId,
                $questionId
            ),
            $preparedData
        );*/
    }

    protected function validateRenumberScenarios($props)
    {
        return true;
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

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return array
     */
    public function validateOperation(OpInterface $op): array
    {
        $props = $op->getProps();
        if (!isset($props['qid'])) {
            throw new \Exception("Question id is mandatory");
        }
        if (isset($props['action'])) {
            switch ($props['action']) {
                case 'deleteAllConditions':
                    if (!$this->validateDeleteAllConditions($props)) {
                        throw new \Exception("Invalid operation");
                    }
                    break;
                case 'renumberScenarios':
                    if (!$this->validateRenumberScenarios($props)) {
                        throw new \Exception("Cannot renumber scenarios");
                    }
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
                                break;
                            case "updateScenario":
                                if (!$this->validateUpdateScenario($scenario)) {
                                    throw new \Exception("Cannot update scenario");
                                }
                                break;
                        }
                    }
                }
            }
        }
        return [];
    }
}
