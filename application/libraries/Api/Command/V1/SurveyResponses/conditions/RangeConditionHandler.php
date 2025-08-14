<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class RangeConditionHandler implements HandlerInterface
{
    public function canHandle(string $operation): bool
    {
        if (strtolower($operation) == 'range') {
            return true;
        }
        return false;
    }

    public function execute($key, $value): object
    {
        $key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        $key = App()->db->quoteColumnName($key);

        $range = $this->parseRange($value);

        $min = isset($range['min']) && is_numeric($range['min']) ? (float)$range['min'] : null;
        $max = isset($range['max']) && is_numeric($range['max']) ? (float)$range['max'] : null;


        $criteria = new \CDbCriteria();

        // Do another more strict strip, to allow only letters and numbers
        // so that we don't have :`id`Max in parameter
        $keyStripped = preg_replace('/[^a-zA-Z0-9_]/', '', $key);

        if ($min === null) {
            $criteria->condition = "CAST($key AS UNSIGNED) <= :{$keyStripped}Max";
            $criteria->params = [":{$keyStripped}Max" => $max];
        } elseif ($max === null) {
            $criteria->condition = "CAST($key AS UNSIGNED) >= :{$keyStripped}Min";
            $criteria->params = [":{$keyStripped}Min" => $min];
        } else {
            $criteria->condition = "CAST($key AS UNSIGNED) >= :{$keyStripped}Min AND CAST($key AS UNSIGNED) <= :{$keyStripped}Max";
            $criteria->params = [":{$keyStripped}Min" => $min, ":{$keyStripped}Max" => $max];
        }
        return $criteria;
    }

    protected function parseRange($range): array
    {
        if (count($range) > 2) {
            throw new InvalidArgumentException("Invalid range sent.");
        }

        $min = isset($range[0]) && $range[0] !== '' ? $range[0] : null;
        $max = isset($range[1]) && $range[1] !== '' ? $range[1] : null;

        if ($min === null && $max === null) {
            throw new InvalidArgumentException("Missing min and max array values.");
        }

        return ['min' => $min, 'max' => $max];
    }
}
