<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class DateRangeConditionHandler implements HandlerInterface
{
    use ConditionHandlerHelperTrait;

    public function canHandle(string $operation): bool
    {
        if (strtolower($operation) == 'date-range') {
            return true;
        }
        return false;
    }

    public function execute($key, $value): object
    {
        if (is_array($key)) {
            throw new InvalidArgumentException('Multiple keys are not supported for range conditions.');
        }

        if (!is_array($value) || count($value) > 2) {
            throw new InvalidArgumentException("Invalid date range sent.");
        }
        $key = $this->sanitizeKey($key);

        $range = $this->parseRange($value);

        $min = $this->validateDate($range['min']);
        $max = $this->validateDate($range['max'], true);

        $criteria = new \CDbCriteria();

        $keyStripped = $this->stripKey($key);

        if ($min === false) {
            $criteria->condition = "$key <= :{$keyStripped}Max";
            $criteria->params = [":{$keyStripped}Max" => $max];
        } elseif ($max === false) {
            $criteria->condition = "$key >= :{$keyStripped}Min";
            $criteria->params = [":{$keyStripped}Min" => $min];
        } else {
            $criteria->condition = "$key BETWEEN :{$keyStripped}Min AND :{$keyStripped}Max";
            $criteria->params = [":{$keyStripped}Min" => $min, ":{$keyStripped}Max" => $max];
        }

        return $criteria;
    }

    protected function parseRange(array $range): array
    {
        $min = isset($range[0]) && $range[0] !== '' ? $range[0] : null;
        $max = isset($range[1]) && $range[1] !== '' ? $range[1] : null;

        if ($min === null && $max === null) {
            throw new InvalidArgumentException("Missing min and max array values.");
        }

        return ['min' => $min, 'max' => $max];
    }

    /**
     * @param string|null $date
     * @param bool $max
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
