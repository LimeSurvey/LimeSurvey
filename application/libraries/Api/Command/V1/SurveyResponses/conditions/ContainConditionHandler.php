<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class ContainConditionHandler implements HandlerInterface
{
    public function canHandle(string $operation): bool
    {
        if ($operation == 'contain') {
            return true;
        }
        return false;
    }

    public function execute(string $key, string $value): object
    {
        return new \CDbCriteria(
            array(
            'condition' => "$key LIKE :match",
            'params'    => array(':match' => "%$value%")
            )
        );
    }
}
