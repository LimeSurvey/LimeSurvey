<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\patch;

use LimeSurvey\Models\Services\Exception\NotFoundException;
use LimeSurvey\Models\Services\Exception\PermissionDeniedException;
use LimeSurvey\Models\Services\Exception\PersistErrorException;
use LimeSurvey\Models\Services\Exception\QuestionHasConditionsException;
use LimeSurvey\Models\Services\SurveyResponseService;
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

class OpHandlerResponsesFileDelete implements OpHandlerInterface
{
    use OpHandlerValidationTrait;

    protected SurveyResponseService $surveyResponseService;

    public function __construct(
        SurveyResponseService $surveyResponseService
    ) {
        $this->surveyResponseService = $surveyResponseService;
    }

    /**
     * Checks if the operation is applicable for the given entity.
     *
     * @param  OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        return $op->getType()->getId() === OpTypeDelete::ID
            && $op->getEntityType() === 'response-file';
    }

    /**
     * Expects a patch structure like this:
     * {
     *      "entity": "response-file",
     *      "op": "delete",
     *      "id": 90
     * }
     *
     * @param OpInterface $op
     * @throws \CDbException
     * @throws \CException
     * @throws PersistErrorException
     * @throws QuestionHasConditionsException
     * @throws NotFoundException
     */
    public function handle(OpInterface $op): void
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $responseId = $op->getEntityId();
        $this->surveyResponseService->deleteAttachments($surveyId, $responseId);
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
            gT('Could not delete response file'),
            $validationData,
            $op
        );
    }
}
