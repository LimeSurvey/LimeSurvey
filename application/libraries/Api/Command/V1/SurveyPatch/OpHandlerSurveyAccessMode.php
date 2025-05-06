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

    /**
     * Checks whether the operation can be handled
     * @param \LimeSurvey\ObjectPatch\Op\OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        return ($op->getEntityType() === $this->entity) && ($op->getType()->getId() === OpTypeUpdate::ID);
    }

    /**
     * Handle survey access mode update.
     *
     *   Expects a patch structure like this:
     *   {
     *        "id": 571271,
     *        "op": "update",
     *        "entity": "accessMode",
     *        "error": false,
     *        "props": {
     *            "accessMode": "D"
     *        }
     *   }
     *
     * Access mode can be:
     * - O: open access mode: no table, survey can be filled anonymously
     * - C: closed access mode: tokens table exists, survey can be filled with token
     * - D: dual access mode: tokens table exists, survey can be filled with token or anonymously
     * Optionally an active parameter can be passed besides the accessMode parameter, which may be K (keep), D (Drop) or A (Archive), depending on
     * what we intend with the tokens table if switching to O
     * @param \LimeSurvey\ObjectPatch\Op\OpInterface $op
     * @return void
     */
    public function handle(OpInterface $op): void
    {
        $this->surveyAccessModeService->changeAccessMode((int)$op->getEntityId(), $op->getProps()['accessMode'], $op->getProps()['action'] ?? 'K');
    }

    public function validateOperation(OpInterface $op): array
    {
        if (!($sid = intval($op->getEntityId()))) {
            throw new NotFoundException('sid is not a number');
        }
        if (!$this->surveyAccessModeService->hasPermission($sid, $op->getProps()['accessMode'])) {
            throw new PermissionDeniedException(
                'Access denied'
            );
        }
        return [];
    }
}
