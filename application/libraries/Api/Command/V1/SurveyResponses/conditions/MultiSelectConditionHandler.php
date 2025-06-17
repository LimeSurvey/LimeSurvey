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
        $key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        $key = App()->db->quoteColumnName($key);

        $selectedValues = array_filter(array_map('trim', explode('|', $value)));
        $conditions = [];
        $params = [];

        foreach ($selectedValues as $index => $val) {
            $paramName = ":value{$index}";
            $conditions[] = "$key = {$paramName}";
            $params[$paramName] = $val;
        }

        $criteria = new \CDbCriteria();

        if (!empty($conditions)) {
            $criteria->condition = '(' . implode(' OR ', $conditions) . ')';
            $criteria->params = $params;
        }

        return $criteria;
    }
}
