<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class MultiSelectConditionHandler implements HandlerInterface
{
    public function canHandle(string $operation): bool
    {
        if (strtolower($operation) == 'multi-select') {
            return true;
        }
        return false;
    }

    public function execute(string $key, string $value): object
    {
        $selectedValues = explode('|', $value);
        $conditions = [];
        $params = [];

        foreach ($selectedValues as $index => $value) {
            $paramName = ":value{$index}";
            $conditions[] = "$key = {$paramName}";
            $params[$paramName] = "{$value}";
        }

        return new \CDbCriteria(
            array(
            'condition' => '(' . implode(' OR ', $conditions) . ')',
            'params'    => $params
            )
        );
    }

}
