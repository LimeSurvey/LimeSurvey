<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Traits;

use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;
use LimeSurvey\ObjectPatch\Op\OpInterface;

trait OpHandlerExceptionTrait
{
    /**
     * @param OpInterface $op
     * @param string $name
     * @return void
     * @throws OpHandlerException
     */
    private function throwNoValuesException(OpInterface $op, string $name = '')
    {
        if ($name !== '') {
            $msg = sprintf(
                'No values to update for %s in entity %s',
                $name,
                $op->getEntityType()
            );
        } else {
            $msg = sprintf(
                'No values to update for entity %s',
                $op->getEntityType()
            );
        }

        throw new OpHandlerException($msg);
    }
}
