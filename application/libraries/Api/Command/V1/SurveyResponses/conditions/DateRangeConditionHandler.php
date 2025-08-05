<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

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
        $max = $this->validateDate($range['max']);

        $criteria = new \CDbCriteria();

        if ($min === false) {
            $criteria->condition = "$key <= :max";
            $criteria->params = [':max' => $max];
        } elseif ($max === false) {
            $criteria->condition = "$key >= :min";
            $criteria->params = [':min' => $min];
        } else {
            $criteria->condition = "$key >= :min AND $key <= :max";
            $criteria->params = [':min' => $min, ':max' => $max];
        }

        return $criteria;
    }

    protected function parseRange(string $range): array
    {
        $parts = explode('::', $range);

        $min = isset($parts[0]) && $parts[0] !== '' ? $parts[0] : null;
        $max = isset($parts[1]) && $parts[1] !== '' ? $parts[1] : null;

        return ['min' => $min, 'max' => $max];
    }

    /**
     * @param string $date
     * @return string|false
     */
    private function validateDate(string $date)
    {
        $dt = \DateTime::createFromFormat('Y-m-d', $date);

        // If input may contain time, use 'Y-m-d H:i:s' or other format accordingly
        if ($dt && $dt->format('Y-m-d') === $date) {
            return $date;
        }

        return false;
    }
}
