<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyTrait;
use LimeSurvey\Models\Services\QuestionAggregateService\SubQuestionsService;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;
use LimeSurvey\ObjectPatch\OpType\OpTypeDelete;

class OpHandlerSubquestionDelete implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;

    protected SubQuestionsService $subQuestionsService;

    /**
     * @param SubQuestionsService $subQuestionsService
     */
    public function __construct(
        SubQuestionsService $subQuestionsService
    ) {
        $this->subQuestionsService = $subQuestionsService;
    }

    /**
     * @param OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        return $op->getType()->getId() === OpTypeDelete::ID
            && $op->getEntityType() === 'subquestion';
    }

    /**
     * Handle subquestion delete operation.
     *
     *   Expects a patch structure like this:
     *   {
     *        "entity": "subquestion",
     *        "op": "delete",
     *        "id": 1
     *   }
     * @param OpInterface $op
     * @return void
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @throws \LimeSurvey\ObjectPatch\OpHandler\OpHandlerException
     */
    public function handle(OpInterface $op)
    {
        $this->subQuestionsService->delete(
            $this->getSurveyIdFromContext($op),
            $op->getEntityId()
        );
    }
}
