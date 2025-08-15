<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use CDbCriteria;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class MultiSelectConditionHandler implements HandlerInterface
{
    use ConditionHandlerHelperTrait;

    public function canHandle(string $operation): bool
    {
        if (strtolower($operation) == 'multi-select') {
            return true;
        }
        return false;
    }

    public function execute($key, $value): object
    {
        $key = $this->sanitizeKey($key);
        $criteria = new CDbCriteria();

        if (!is_array($value)) {
            $value = [$value];
        }

        if (!empty($value)) {
            $placeholders = [];
            $params = [];

            foreach (array_values($value) as $index => $val) {
                $paramName = ":value{$index}";
                $placeholders[] = $paramName;
                $params[$paramName] = $val;
            }

            $criteria->condition = "{$key} IN (" . implode(', ', $placeholders) . ")";
            $criteria->params = $params;
        }

        return $criteria;
    }
}
