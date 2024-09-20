<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\DI;
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerSurveyTrait,
    OpHandlerValidationTrait
};
use LimeSurvey\Models\Services\{Exception\NotFoundException,
    Exception\PermissionDeniedException,
    QuestionAggregateService,
    QuestionAggregateService\SubQuestionsService};
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeDelete
};

class OpHandlerSubquestionDelete implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerValidationTrait;

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
     * @throws NotFoundException
     * @throws PermissionDeniedException
     */
    public function handle(OpInterface $op)
    {
        $questionService = DI::getContainer()->get(
            QuestionAggregateService::class
        );
        $surveyId = $this->getSurveyIdFromContext($op);
        $questionService->checkDeletePermission($surveyId);
        $this->subQuestionsService->delete(
            $surveyId,
            $op->getEntityId()
        );
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return array
     */
    public function validateOperation(OpInterface $op): array
    {
        $validationData = $this->validateSurveyIdFromContext($op, []);
        $validationData = $this->validateEntityId($op, $validationData);
        return $this->getValidationReturn(
            gT('Could not delete subquestion'),
            $validationData,
            $op
        );
    }
}
