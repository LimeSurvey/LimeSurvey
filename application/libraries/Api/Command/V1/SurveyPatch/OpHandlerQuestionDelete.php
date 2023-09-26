<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Models\Services\QuestionAggregateService;
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeDelete
};

class OpHandlerQuestionDelete implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;

    protected QuestionAggregateService $questionAggregateService;

    public function __construct(
        QuestionAggregateService $questionAggregateService
    ) {
        $this->questionAggregateService = $questionAggregateService;
    }

    /**
     * Checks if the operation is applicable for the given entity.
     *
     * @param OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        return $op->getType()->getId() === OpTypeDelete::ID
            && $op->getEntityType() === 'question';
    }

    /**
     * Handle question delete operation.
     *
     * Expects a patch structure like this:
     * {
     *      "entity": "question",
     *      "op": "delete",
     *      "id": 1
     * }
     *
     * @param OpInterface $op
     * @throws OpHandlerException
     */
    public function handle(OpInterface $op): void
    {
        $this->questionAggregateService->delete(
            $this->getSurveyIdFromContext($op),
            $op->getEntityId()
        );
    }
}
