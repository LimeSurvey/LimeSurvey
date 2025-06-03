<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

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

    public function execute(string $key, string $value): object
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

    protected function parseRange(string $range): array
    {
        $parts = explode('::', $range);

        $min = isset($parts[0]) && $parts[0] !== '' ? floatval($parts[0]) : null;
        $max = isset($parts[1]) && $parts[1] !== '' ? floatval($parts[1]) : null;

        return ['min' => $min, 'max' => $max];
    }
}
