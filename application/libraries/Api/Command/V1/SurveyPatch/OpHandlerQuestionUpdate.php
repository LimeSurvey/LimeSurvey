<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerSurveyTrait,
    OpHandlerExceptionTrait
};
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
        $transformOptions = ['operation' => $op->getType()->getId()];
        $this->throwTransformerValidationErrors(
            $this->transformer->validate(
                $op->getProps(),
                $transformOptions
            ),
            $op
        );
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
        $transformOptions = ['operation' => $op->getType()->getId()];
        $transformedProps = $this->transformer->transform(
            $op->getProps(),
            $transformOptions
        );
        // Set qid from op entity id
        if (
            is_array($transformedProps)
            && (
                !array_key_exists(
                    'qid',
                    $transformedProps
                )
                || $transformedProps['qid'] === null
            )
        ) {
            $transformedProps['qid'] = $op->getEntityId();
        }

        return $transformedProps;
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return bool
     */
    public function isValidPatch(OpInterface $op): bool
    {
        // patch is already checked by getPreparedData()
        return true;
    }
}
