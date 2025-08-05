<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class NullConditionHandler implements HandlerInterface
{
    public function canHandle(string $operation): bool
    {
        return true;
    }

    public function execute($key, $value): object
    {
        $criteria = new \CDbCriteria();
        $upper = strtoupper($value);
        switch ($upper) {
            case 'NULL':
            case 'IS NULL':
                $match = "$key IS NULL";
                break;
            case 'IS NOT NULL':
                $match = "$key IS NOT NULL";
                break;
            default:
                $match = null;
        }

        if (!empty($match)) {
            $criteria->condition = $match;
        }

        return $criteria;
    }
}
