<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use \LimeSurvey\Api\Command\V1\SurveyPatch\Traits\{
    OpHandlerExceptionTrait,
    OpHandlerSurveyTrait,
    OpHandlerValidationTrait
};

use \LimeSurvey\Models\Services\{
    Exception\NotFoundException,
    Exception\PermissionDeniedException,
    Exception\PersistErrorException
};

use \LimeSurvey\ObjectPatch\{
    Op\OpInterface,
    OpHandler\OpHandlerException,
    OpHandler\OpHandlerInterface,
    OpType\OpTypeCreate,
    OpType\OpTypeUpdate,
    OpType\OpTypeDelete
};

use LimeSurvey\Models\Services\SurveyActivate;
use Permission;

class OpHandlerImport extends OpHandlerInterface
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
    )
    {
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
        return ($op->getType()->getId() === OpTypeUpdate::ID);
    }

    /**
     * @param \LimeSurvey\ObjectPatch\Op\OpInterface $op
     * @return void
     * @throws OpHandlerException
     * @throws PersistErrorException
     * @throws NotFoundException
     * @throws PermissionDeniedException
     */
    public function handle(OpInterface $op)
    {
        $this->surveyActivate->restoreData((int)$op->getProps()['sid']);
    }

    public function validateOperation(OpInterface $op): array
    {
        $props = $op->getProps();
        if (!($sid = intval($props['sid']))) {
            throw new NotFoundException('sid is not a number');
        }
        if (!Permission::model()->hasSurveyPermission($sid, 'surveycontent', 'update')) {
            throw new PermissionDeniedException('Permission denied for this survey');
        }
        return [];
    }
}