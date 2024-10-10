<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Transformer\TransformerException;
use LimeSurvey\Models\Services\{
    Exception\NotFoundException,
    Exception\PermissionDeniedException,
    Exception\PersistErrorException,
    QuestionAggregateService
};
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{OpHandlerSurveyTrait,
    OpHandlerExceptionTrait,
    OpHandlerValidationTrait
};
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestion;
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};

class OpHandlerQuestionUpdate implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerExceptionTrait;
    use OpHandlerValidationTrait;

    protected QuestionAggregateService $questionAggregateService;
    protected TransformerInputQuestion $transformer;

    public function __construct(
        QuestionAggregateService $questionAggregateService,
        TransformerInputQuestion $transformer
    ) {
        $this->questionAggregateService = $questionAggregateService;
        $this->transformer = $transformer;
    }

    /**
     * Checks if the operation is applicable for the given entity.
     *
     * @param OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        return $op->getType()->getId() === OpTypeUpdate::ID
            && $op->getEntityType() === 'question';
    }

    /**
     * Handle question update operation.
     *
     * Expects a patch structure like this:
     * {
     *     "entity": "question",
     *     "op": "update",
     *     "id": 1,
     *     "props": {
     *         "title": "Q03",
     *         "mandatory": true,
     *         "encrypted": true
     *     }
     * }
     *
     * @param OpInterface $op
     * @throws OpHandlerException
     * @throws TransformerException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws PersistErrorException
     */
    public function handle(OpInterface $op): void
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $this->questionAggregateService->checkUpdatePermission($surveyId);
        $transformedProps = $this->transformer->transform(
            $op->getProps(),
            [
                'operation' => $op->getType()->getId(),
                'id' => $op->getEntityId()
            ]
        );
        if (empty($transformedProps)) {
            $this->throwNoValuesException($op);
        }
        $this->questionAggregateService->save(
            $surveyId,
            [
                'question' => $transformedProps
            ]
        );
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return array
     */
    public function validateOperation(OpInterface $op): array
    {
        $validationData = $this->transformer->validate(
            $op->getProps(),
            ['operation' => $op->getType()->getId()]
        );
        $validationData = $this->validateEntityId(
            $op,
            !is_array($validationData) ? [] : $validationData
        );
        $validationData = $this->validateSurveyIdFromContext(
            $op,
            $validationData
        );

        return $this->getValidationReturn(
            gT('Could not save question'),
            $validationData,
            $op
        );
    }
}
