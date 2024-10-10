<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\Exception\PersistErrorException;
use LimeSurvey\Models\Services\Exception\QuestionHasConditionsException;
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerSurveyTrait,
    OpHandlerValidationTrait
};
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
    use OpHandlerValidationTrait;

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
     * @throws \CDbException
     * @throws \CException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     * @throws QuestionHasConditionsException
     */
    public function handle(OpInterface $op): void
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $this->questionAggregateService->checkDeletePermission($surveyId);
        $this->questionAggregateService->delete(
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
            gT('Could not delete question'),
            $validationData,
            $op
        );
    }
}
