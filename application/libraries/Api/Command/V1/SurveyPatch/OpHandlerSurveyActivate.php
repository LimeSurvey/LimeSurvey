<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeActivate
};
use LimeSurvey\Models\Services\SurveyAggregateService;

class OpHandlerSurveyActivate implements OpHandlerInterface
{
    public function canHandle(OpInterface $op): bool
    {
        $isUpdateOperation = $op->getType()->getId() === OpTypeActivate::ID;
        $isSurveyEntity = $op->getEntityType() === 'survey';

        return $isUpdateOperation && $isSurveyEntity;
    }

    public function handle(OpInterface $op)
    {
        $diContainer = \LimeSurvey\DI::getContainer();
        $surveyActivateService = $diContainer->get(
            SurveyAggregateService::class
        );
        $props = $op->getProps();
        $surveyActivateService->activate($op->getEntityId(), $props);
    }

    public function validateOperation(OpInterface $op): array
    {
        return [];
    }
}
