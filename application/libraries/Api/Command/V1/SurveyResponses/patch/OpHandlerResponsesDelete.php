<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\patch;

use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\Exception\PersistErrorException;
use LimeSurvey\Models\Services\Exception\QuestionHasConditionsException;
use LimeSurvey\Models\Services\SurveyResponseService;
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerValidationTrait
};
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeDelete
};

class OpHandlerResponsesDelete implements OpHandlerInterface
{
    use OpHandlerValidationTrait;

    protected SurveyResponseService $surveyResponseService;

    public function __construct(
        SurveyResponseService $surveyResponseService
    ) {
        $this->surveyResponseService = $surveyResponseService;
    }

    /**
     * @param  OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        return $op->getType()->getId() === OpTypeDelete::ID
            && $op->getEntityType() === 'response';
    }

    /**
     * Expects a patch structure like this:
     * {
     *      "entity": "response",
     *      "op": "delete",
     *      "id": 1
     * }
     *
     * @param  OpInterface $op
     * @throws \CDbException
     * @throws \CException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     * @throws QuestionHasConditionsException
     */
    public function handle(OpInterface $op): void
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $responseId = $op->getEntityId();
        $this->surveyResponseService->delete($surveyId, [$responseId]);
    }

    /**
     * Checks if patch is valid for this operation.
     *
     * @param  OpInterface $op
     * @return array
     */
    public function validateOperation(OpInterface $op): array
    {
        $validationData = $this->validateSurveyIdFromContext($op, []);
        $validationData = $this->validateEntityId($op, $validationData);
        return $this->getValidationReturn(
            gT('Could not delete response'),
            $validationData,
            $op
        );
    }
}
