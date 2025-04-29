<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeUpdate
};
use LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerExceptionTrait,
    OpHandlerSurveyTrait,
    OpHandlerValidationTrait
};
use LimeSurvey\Models\Services\Exception\{
    NotFoundException,
    PermissionDeniedException
};

use LimeSurvey\Models\Services\SurveyAccessModeService;
use Permission;

class OpHandlerSurveyAccessMode implements OpHandlerInterface
{
    use OpHandlerExceptionTrait;
    use OpHandlerSurveyTrait;
    use OpHandlerValidationTrait;

    protected string $entity;

    protected SurveyAccessModeService $surveyAccessModeService;

    public function __construct(
        SurveyAccessModeService $surveyAccessModeService
    ) {
        $this->surveyAccessModeService = $surveyAccessModeService;
        $this->entity = 'accessMode';
    }

    public function canHandle(OpInterface $op): bool
    {
        return ($op->getEntityType() === $this->entity) && ($op->getType()->getId() === OpTypeUpdate::ID);
    }

    public function handle(OpInterface $op)
    {
        $this->surveyAccessModeService->changeAccessMode((int)$op->getEntityId(), $op->getProps()['accessMode'], $op->getProps()['archive'] ?? true);
    }

    public function validateOperation(OpInterface $op): array
    {
        if (!($sid = intval($op->getEntityId()))) {
            throw new NotFoundException('sid is not a number');
        }
        if (!$this->surveyAccessModeService->hasPermission((int)$sid, $op->getProps()['accessMode'])) {
            throw new PermissionDeniedException(
                'Access denied'
            );
        }
        return [];
    }
}