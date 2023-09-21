<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyPatch;

use LimeSurvey\Api\Command\V1\SurveyPatch\OpHandlerSurveyTrait;
use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerInterface;
use LimeSurvey\ObjectPatch\OpType\OpTypeDelete;

class OpHandlerSubquestionDelete implements OpHandlerInterface
{
    use OpHandlerSurveyTrait;

    /**
     * @param OpInterface $op
     * @return bool
     */
    public function canHandle(OpInterface $op): bool
    {
        return $op->getType()->getId() === OpTypeDelete::ID
            && $op->getEntityType() === 'subquestion';
    }

    /**
     *  Handle subquestion delete operation.
     *
     *  Expects a patch structure like this:
     *  {
     *       "entity": "subquestion",
     *       "op": "delete",
     *       "id": 1
     *  }
     * @param OpInterface $op
     */
    public function handle(OpInterface $op)
    {
        // TODO: Implement handle() method.
    }
}
