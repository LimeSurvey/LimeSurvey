<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use http\Exception\InvalidArgumentException;
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

        if ($min === null) {
            $criteria->condition = "CAST($key AS UNSIGNED) <= :max";
            $criteria->params = [':max' => $max];
        } elseif ($max === null) {
            $criteria->condition = "CAST($key AS UNSIGNED) >= :min";
            $criteria->params = [':min' => $min];
        } else {
            $criteria->condition = "CAST($key AS UNSIGNED) >= :min AND CAST($key AS UNSIGNED) <= :max";
            $criteria->params = [':min' => $min, ':max' => $max];
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
