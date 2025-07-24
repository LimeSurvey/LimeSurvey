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
use LimeSurvey\Models\Services\SurveyActivate;
use Permission;

class OpHandlerImport implements OpHandlerInterface
{
    use OpHandlerExceptionTrait;
    use OpHandlerSurveyTrait;
    use OpHandlerValidationTrait;

    protected string $entity;

    protected SurveyActivate $surveyActivate;

    /**
     * Constructor
     * @param \LimeSurvey\Models\Services\SurveyActivate $surveyActivate the activation ojbect for the purpose of the import
     */
    public function __construct(
        SurveyActivate $surveyActivate
    ) {
        $this->entity = 'importResponses';
        $this->surveyActivate = $surveyActivate;
    }

    /**
     * Determines whether the request can be handled
     * @param \LimeSurvey\ObjectPatch\Op\OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        return ($op->getEntityType() === $this->entity) && ($op->getType()->getId() === OpTypeUpdate::ID);
    }

    /**
     *     "patch": [{
     *             "entity": "importResponses",
     *             "op": "update",
     *             "id": 809,
     *             "props": {
     *                 "timestamp": 20250303173908,
     *                 "preserveIDs": true,
     *                 "archiveType": "RP",
     *                 "useFallback": true,
     *             }
     *         }
     *     ]
     * @param \LimeSurvey\ObjectPatch\Op\OpInterface $op
     * @return void
     */
    public function handle(OpInterface $op)
    {
        $entityId = (int)$op->getEntityId();
        $props = $op->getProps();

        $timestamp = isset($props['timestamp']) ? (int)$props['timestamp'] : null;
        $preserveIDs = ($props['preserveIDs'] ?? false) == "true";
        $archiveType = $props['archiveType'] ?? 'all';
        $useFallback = $props['useFallback'] ?? true;

        $this->surveyActivate->restoreData($entityId, $timestamp, $preserveIDs, $archiveType, $useFallback);
    }

    /**
     * Checks if patch is valid for this operation.
     * @param OpInterface $op
     * @return array
     * @throws NotFoundException
     * @throws PermissionDeniedException
     */
    public function validateOperation(OpInterface $op): array
    {
        if (!($sid = intval($op->getEntityId()))) {
            throw new NotFoundException('sid is not a number');
        }
        if (!Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'update')) {
            throw new PermissionDeniedException('Permission denied for this survey');
        }
        return [];
    }
}
