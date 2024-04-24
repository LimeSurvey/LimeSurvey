<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeActivate
};
use LimeSurvey\Api\Command\V1\Transformer\Input\TransformerInputSurvey;
use LimeSurvey\Models\Services\SurveyAggregateService;
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerExceptionTrait,
    OpHandlerSurveyTrait,
    OpHandlerValidationTrait
};

class OpHandlerSurveyActivate implements OpHandlerInterface
{
    use OpHandlerExceptionTrait;
    use OpHandlerSurveyTrait;
    use OpHandlerValidationTrait;

    protected TransformerInputSurvey $transformer;

    public function __construct(TransformerInputSurvey $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @param OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        $isUpdateOperation = $op->getType()->getId() === OpTypeActivate::ID;
        $isSurveyEntity = $op->getEntityType() === 'survey';

        return $isUpdateOperation && $isSurveyEntity;
    }

    /**
     * Handle subquestion delete operation.
     *
     *   Expects a patch structure like this:
     *   {
     *        "id": 571271,
     *        "op": "activate",
     *        "entity": "survey",
     *        "error": false,
     *        "props": {
     *            "anonymized": false
     *        }
     *   }
     *
     * @param OpInterface $op
     * @return void
     * @throws \LimeSurvey\Models\Services\Exception\NotFoundException
     * @throws \LimeSurvey\Models\Services\Exception\PermissionDeniedException
     * @throws \LimeSurvey\ObjectPatch\OpHandler\OpHandlerException
     */
    public function handle(OpInterface $op)
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $surveyActivateService = $diContainer->get(
            SurveyAggregateService::class
        );
        $props = $op->getProps();
        $surveyActivateService->activate($op->getEntityId(), $props);
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

        return $this->getValidationReturn(
            gT('Could not save survey'),
            !is_array($validationData) ? [] : $validationData,
            $op
        );
    }
}
