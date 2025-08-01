<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class NullConditionHandler implements HandlerInterface
{
    public function canHandle(string $operation): bool
    {
        return true;
    }

    public function execute(string $key, string $value): object
    {
        $criteria = new \CDbCriteria();
        $match = match (strtoupper($value)) {
            'NULL', 'IS NULL' => "$key IS NULL",
            'IS NOT NULL' => "$key IS NOT NULL",
            default => null,
        };

        if (!empty($match)) {
            $criteria->condition = $match;
        }

        return $criteria;
    }
}
