<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class RangeConditionHandler implements HandlerInterface
{
    use ConditionHandlerHelperTrait;

    public function canHandle(string $operation): bool
    {
        if (strtolower($operation) == 'range') {
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
            throw new InvalidArgumentException("Invalid range sent.");
        }

        $key = $this->sanitizeKey($key);

        $range = $this->parseRange($value);

        $min = isset($range['min']) && is_numeric($range['min']) ? (float)$range['min'] : null;
        $max = isset($range['max']) && is_numeric($range['max']) ? (float)$range['max'] : null;

        $criteria = new \CDbCriteria();

        $minParam = $this->nextParamName();
        $maxParam = $this->nextParamName();

        if ($min === null) {
            $criteria->condition = "CAST($key AS UNSIGNED) <= $maxParam";
            $criteria->params = [$maxParam => $max];
        } elseif ($max === null) {
            $criteria->condition = "CAST($key AS UNSIGNED) >= $minParam";
            $criteria->params = [$minParam => $min];
        } else {
            $criteria->condition = "CAST($key AS UNSIGNED) BETWEEN $minParam AND $maxParam";
            $criteria->params = [$minParam => $min, $maxParam => $max];
        }
        return $criteria;
    }

    /**
     * @param array $range
     * @return array
     */
    protected function parseRange(array $range): array
    {
        $min = isset($range[0]) && $range[0] !== '' ? $range[0] : null;
        $max = isset($range[1]) && $range[1] !== '' ? $range[1] : null;

        if ($min === null && $max === null) {
            throw new InvalidArgumentException("Missing min and max array values.");
        }

        return ['min' => $min, 'max' => $max];
    }
}
