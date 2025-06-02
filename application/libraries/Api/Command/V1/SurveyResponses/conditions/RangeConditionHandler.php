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
        $range = $this->parseRange($value);

        if ($range['min'] == null) {
            return new \CDbCriteria(
                array(
                'condition' => "CAST(`$key` AS UNSIGNED) <= :max",
                'params'    => array(':max' => $range['max'])
                )
            );
        }
        else if ($range['max'] == null) {
            return new \CDbCriteria(
                array(
                'condition' => "CAST(`$key` AS UNSIGNED) >= :min",
                'params'    => array(':min' => $range['min'])
                )
            );
        }
        return new \CDbCriteria(
            array(
            'condition' => "CAST(`$key` AS UNSIGNED) >= :min AND CAST(`$key` AS UNSIGNED) <= :max",
            'params'    => array(':min' => $range['min'], ':max' => $range['max'])
            )
        );
    }

    function parseRange(string $range)
    {
        $parts = explode('::', $range);

        $min = isset($parts[0]) && $parts[0] !== '' ? floatval($parts[0]): null;
        $max = isset($parts[1]) && $parts[1] !== '' ? floatval($parts[1]) : null;

        return ['min' => $min, 'max' => $max];
    }
}
