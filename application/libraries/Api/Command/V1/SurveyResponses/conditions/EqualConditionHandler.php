<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class EqualConditionHandler implements HandlerInterface
{
    public function canHandle(string $operation): bool
    {
        if (strtolower($operation) == 'equal') {
            return true;
        }
        return false;
    }

    public function execute(string $key, string $value): object
    {
        return new \CDbCriteria(
            array(
            'condition' => "$key = :match",
            'params'    => array(':match' => "$value")
            )
        );
    }
}
