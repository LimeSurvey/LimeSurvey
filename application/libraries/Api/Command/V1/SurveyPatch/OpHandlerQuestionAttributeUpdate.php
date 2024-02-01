<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAttribute;
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{OpHandlerSurveyTrait,
    OpHandlerValidationTrait};
use LimeSurvey\Models\Services\{
    QuestionAggregateService,
    QuestionAggregateService\AttributesService,
    QuestionAggregateService\QuestionService,
    Exception\PersistErrorException
};
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
     *             "id": 744, // qid !
     *             "props": {
     *                 "dualscale_headerA": {
     *                     "de-informal": {
     *                         "value": "A ger"
     *                     },
     *                     "en": {
     *                         "value": "A"
     *                     }
     *                 },
     *                 "dualscale_headerB": {
     *                     "de-informal": {
     *                         "value": "B ger"
     *                     },
     *                     "en": {
     *                         "value": "B"
     *                     }
     *                 },
     *                 "public_statistics": {
     *                     "": {
     *                         "value": "1"
     *                     }
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
     */
    public function handle(OpInterface $op): void
    {
        $surveyId = $this->getSurveyIdFromContext($op);
        $this->questionAggregateService->checkUpdatePermission($surveyId);
        $preparedData = $this->transformer->transformAll($op->getProps());
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
        $validationData = $this->validateCollection($op, []);
        // We only validate further, if props came as
        // or were enhanced into collection
        if (empty($validationData)) {
            $validationData = $this->transformer->validateAll(
                $op->getProps(),
                ['operation' => $op->getType()->getId()]
            );
        }
        return $this->getValidationReturn(
            !is_array($validationData) ? [] : $validationData,
            $op
        );
    }
}
