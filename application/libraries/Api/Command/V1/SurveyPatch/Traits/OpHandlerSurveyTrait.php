<?php

namespace LimeSurvey\Api\Command\V1\SurveyPatch\Traits;

use LimeSurvey\ObjectPatch\Op\OpInterface;
use LimeSurvey\ObjectPatch\OpHandler\OpHandlerException;

trait OpHandlerSurveyTrait
{
    /**
     * Extracts and returns surveyId from context
     * @param OpInterface $op
     * @return int
     */
    public function getSurveyIdFromContext(OpInterface $op)
    {
        $context = $op->getContext();
        return isset($context['id']) ? (int)$context['id'] : 0;
    }

    /**
     * returns and removes tempId from dataset
     * @param array $dataSet
     * @return int|mixed
     */
    public function extractTempId(array &$dataSet)
    {
        if (isset($dataSet['tempId'])) {
            $tempId = $dataSet['tempId'];
            unset($dataSet['tempId']);
            return $tempId;
        }
        return 0;
    }
}
