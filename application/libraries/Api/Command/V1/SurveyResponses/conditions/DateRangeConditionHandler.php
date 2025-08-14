<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class DateRangeConditionHandler implements HandlerInterface
{
    public function canHandle(string $operation): bool
    {
        if (strtolower($operation) == 'date-range') {
            return true;
        }
        return false;
    }

    public function execute($key, $value): object
    {
        $key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        $key = App()->db->quoteColumnName($key);

        $range = $this->parseRange($value);

        $min = $this->validateDate($range['min']);
        $max = $this->validateDate($range['max'], true);

        $criteria = new \CDbCriteria();

        $keyStripped = preg_replace('/[^a-zA-Z0-9_]/', '', $key);

        if ($min === false) {
            $criteria->condition = "$key <= :{$keyStripped}Max";
            $criteria->params = [":{$keyStripped}Max" => $max];
        } elseif ($max === false) {
            $criteria->condition = "$key >= :{$keyStripped}Min";
            $criteria->params = [":{$keyStripped}Min" => $min];
        } else {
            $criteria->condition = "$key >= :{$keyStripped}Min AND $key <= :{$keyStripped}Max";
            $criteria->params = [":{$keyStripped}Min" => $min, ":{$keyStripped}Max" => $max];
        }

        return $criteria;
    }

    protected function parseRange($range): array
    {
        if (count($range) > 2) {
            throw new InvalidArgumentException("Invalid date range sent.");
        }

        $min = isset($range[0]) && $range[0] !== '' ? $range[0] : null;
        $max = isset($range[1]) && $range[1] !== '' ? $range[1] : null;

        if ($min === null && $max === null) {
            throw new InvalidArgumentException("Missing min and max array values.");
        }

        return ['min' => $min, 'max' => $max];
    }

    /**
     * @param string $date
     * @return string|false
     */
    private function validateDate(?string $date, $max = false)
    {
        if ($date === null) {
            return false;
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $date);

        // If input may contain time, use 'Y-m-d H:i:s' or other format accordingly
        if ($dt && $dt->format('Y-m-d') === $date) {
            return $date . ($max ? ' 23:59:59' : ' 00:00:00');
        }

        return false;
    }
}
