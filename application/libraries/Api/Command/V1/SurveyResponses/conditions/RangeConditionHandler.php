<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;
use LimeSurvey\Models\Services\ResponseFilters\NumericRangeSql;

class RangeConditionHandler implements HandlerInterface, SurveyContextAwareInterface
{
    use ConditionHandlerHelperTrait;
    use SurveyResponseColumnTrait;

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

        $rawKey = (string) $key;
        $key = $this->sanitizeKey($key);

        $range = $this->parseRange($value);

        $min = isset($range['min']) && is_numeric($range['min']) ? (float)$range['min'] : null;
        $max = isset($range['max']) && is_numeric($range['max']) ? (float)$range['max'] : null;

        $criteria = new \CDbCriteria();

        $minParam = $this->nextParamName();
        $maxParam = $this->nextParamName();

        $number = $this->isIntegerColumn($rawKey) ? $key : $this->numericExpression($key);

        if ($min === null) {
            $criteria->condition = "$number <= $maxParam";
            $criteria->params = [$maxParam => $max];
        } elseif ($max === null) {
            $criteria->condition = "$number >= $minParam";
            $criteria->params = [$minParam => $min];
        } else {
            $criteria->condition = "$number BETWEEN $minParam AND $maxParam";
            $criteria->params = [$minParam => $min, $maxParam => $max];
        }
        return $criteria;
    }

    private function numericExpression(string $quotedKey): string
    {
        return NumericRangeSql::expression(App()->db, $quotedKey, $this->driverName());
    }

    /** Seam: the other drivers' branches are otherwise only reachable on them. */
    protected function driverName(): string
    {
        return (string) App()->db->getDriverName();
    }

    /**
     * Whether the response column is an integer. Without the survey the type
     * is unknown, and the guarded expression is used.
     *
     * @param string $field Column name, optionally prefixed with a table alias
     * @return bool
     */
    protected function isIntegerColumn(string $field): bool
    {
        $column = $this->responseColumn($field);
        return $column !== null && $column->type === 'integer';
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
