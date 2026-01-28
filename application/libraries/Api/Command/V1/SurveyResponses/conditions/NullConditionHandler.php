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

        if (is_array($key)) {
            return $criteria;
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        $conditions = [];

        foreach ($value as $item) {
            $item = (string) $item;
            if (!in_array($item, ['true', 'false'])) {
                continue;
            }

            $conditions[] = $key . ' IS ' . ($item === 'true' ? 'NOT ' : '') . 'NULL';
        }

        if (!empty($conditions)) {
            $criteria->condition = '(' . implode(' OR ', $conditions) . ')';
        }
        return $criteria;
    }
}
