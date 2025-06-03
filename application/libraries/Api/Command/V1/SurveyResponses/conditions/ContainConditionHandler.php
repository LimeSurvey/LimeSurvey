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
        $key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        $key = App()->db->quoteColumnName($key);
        $value = trim($value);

        return new \CDbCriteria([
            'condition' => "$key LIKE :match",
            'params'    => [':match' => "%$value%"],
        ]);
    }
}
