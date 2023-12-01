<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestionAttribute;
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerQuestionTrait,
    OpHandlerSurveyTrait
};
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
    use OpHandlerQuestionTrait;

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
     * Updates multiple attributes for a single question. Format exactly the
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
        $preparedData = $this->prepareAdvancedSettings(
            $op,
            $this->transformer,
            $op->getProps(),
            ['attributes']
        );
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
     * @return bool
     */
    public function isValidPatch(OpInterface $op): bool
    {
        // prepareAdvancedSettings is taking care of validation
        return true;
    }
}
