<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch;

use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;

class OpApiHelper
{
    /**
     * Extracts and returns surveyId from context
     * @param OpInterface $op
     * @return int
     * @throws OpHandlerException
     */
    public static function getSurveyIdFromContext(OpInterface $op)
    {
        $context = $op->getContext();
        $surveyId = $context['id'] ? (int)$context['id'] : null;
        if ($surveyId === null) {
            throw new OpHandlerException(
                printf(
                    'Missing survey id in context for entity %s',
                    $op->getEntityType()
                )
            );
        }
        return $surveyId;
    }
}
