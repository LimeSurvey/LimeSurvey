<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAttribute;
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerExceptionTrait,
    OpHandlerSurveyTrait,
    OpHandlerValidationTrait};
use LimeSurvey\Models\Services\{
    Exception\NotFoundException,
    Exception\PermissionDeniedException,
    QuestionAggregateService,
    QuestionAggregateService\AttributesService,
    QuestionAggregateService\QuestionService,
    Exception\PersistErrorException};
use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};

class OpHandlerQuestionAttributeUpdate implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;
    use OpHandlerValidationTrait;
    use OpHandlerExceptionTrait;

    protected string $entity;
    protected AttributesService $attributesService;
    protected QuestionService $questionService;
    protected QuestionAggregateService $questionAggregateService;
    protected TransformerInputQuestionAttribute $transformer;

    public function __construct(
        AttributesService $attributesService,
        QuestionService $questionService,
        QuestionAggregateService $questionAggregateService,
        TransformerInputQuestionAttribute $transformer
    ) {
        $this->entity = 'questionAttribute';
        $this->attributesService = $attributesService;
        $this->questionService = $questionService;
        $this->questionAggregateService = $questionAggregateService;
        $this->transformer = $transformer;
    }

    public function canHandle(OpInterface $op): bool
    {
        $isUpdateOperation = $op->getType()->getId() === OpTypeUpdate::ID;
        $isAttributeEntity = $op->getEntityType() === $this->entity;

        return $isUpdateOperation && $isAttributeEntity;
    }

    /**
     * Updates multiple attributes for a single question. Format is exactly the
     * same as in Question create, so they share the prepare function.
     *
     * patch structure:
     * {
     *     "patch": [{
     *             "entity": "questionAttribute",
     *             "op": "update",
     *             "id": 809,
     *             "props": {
     *                 "dualscale_headerA": {
     *                     "de": "A ger",
     *                     "en": "A"
     *                 },
     *                 "dualscale_headerB": {
     *                     "de": "B ger",
     *                     "en": "B"
     *                 },
     *                 "public_statistics": {
     *                     "": "1"
     *                 }
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
        $surveyId = $this->getSurveyIdFromContext($op);
        $this->questionAggregateService->checkUpdatePermission($surveyId);
        $preparedData = $this->transformer->transformAll($op->getProps());
        if (empty($preparedData)) {
            $this->throwNoValuesException($op);
        }
        $questionId = $op->getEntityId();
        $this->attributesService->saveAdvanced(
            $this->questionService->getQuestionBySidAndQid(
                $surveyId,
                $questionId
            ),
            $preparedData
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
        $validationData = $this->validateCollectionIndex($op, $validationData);
        if (empty($validationData)) {
            $validationData = $this->transformer->validateAll(
                $op->getProps(),
                ['operation' => $op->getType()->getId()]
            );
        }
        return $this->getValidationReturn(
            gT('Could not save question attributes'),
            !is_array($validationData) ? [] : $validationData,
            $op
        );
    }
}
