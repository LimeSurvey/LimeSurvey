<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;

class OpHandlerSurveyActivate implements OpHandlerInterface
{

    public function canHandle(OpInterface $op): bool
    {
        // TODO: Implement canHandle() method.
    }

    public function handle(OpInterface $op)
    {
        // TODO: Implement handle() method.
    }

    public function validateOperation(OpInterface $op): array
    {
        // TODO: Implement validateOperation() method.
    }
}
