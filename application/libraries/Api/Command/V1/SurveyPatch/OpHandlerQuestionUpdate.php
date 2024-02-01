<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{OpHandlerSurveyTrait,
    OpHandlerExceptionTrait,
    OpHandlerValidationTrait};
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputQuestion;
use LimeSurvey\Models\Services\QuestionAggregateService;
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
     */
    public function handle(OpInterface $op): void
    {
        $this->questionAggregateService->save(
            $this->getSurveyIdFromContext($op),
            ['question' => $this->getPreparedData($op)]
        );
    }

    /**
     * Organizes the patch data into the structure which
     * is expected by the service.
     * @param OpInterface $op
     * @return ?array
     * @throws OpHandlerException
     */
    public function getPreparedData(OpInterface $op)
    {
        $props = $this->transformer->transform(
            $op->getProps(),
            ['operation' => $op->getType()->getId()]
        );
        // Set qid from op entity id
        if (
            is_array($props)
            && (
                !array_key_exists(
                    'qid',
                    $props
                )
                || $props['qid'] === null
            )
        ) {
            $props['qid'] = $op->getEntityId();
        }

        return $props;
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

        return $this->getValidationReturn(
            $validationData,
            $op
        );
    }
}
