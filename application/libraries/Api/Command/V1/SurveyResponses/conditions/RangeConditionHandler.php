<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

class RangeConditionHandler implements HandlerInterface
{
    use ConditionHandlerHelperTrait;

    /**
     * Plain decimal test, the same one the statistics aggregates use. Numeric
     * answers live in text columns that may hold anything, and '' and NULL
     * both have to fall out of the range rather than become a zero.
     */
    private const NUMERIC_PATTERN = '^-?[0-9]*\.?[0-9]+$';

    /**
     * Wide and exact. An unsigned integer would truncate 2.5 to 2 and wrap -3
     * to 18446744073709551613, and is not a cast target on Postgres or MSSQL.
     */
    private const NUMERIC_TYPE = 'DECIMAL(30,10)';

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

        $number = $this->numericExpression($key);

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

    /**
     * The column read as a number, or as nothing when it does not hold one.
     *
     * A cell that cannot be read as a number yields NULL, which makes the
     * comparison unknown and drops the row — rather than MySQL's silent 0,
     * which would pull every junk row into any range spanning zero.
     *
     * The guard casts to text first so the expression is valid whether the
     * column is numeric (id, seed) or the varchar that answers are stored in.
     * Testing inside CASE also keeps Postgres from evaluating the cast on a
     * cell that would make it fail.
     */
    private function numericExpression(string $quotedKey): string
    {
        $db = App()->db;
        $pattern = $db->quoteValue(self::NUMERIC_PATTERN);

        switch ($this->driverName()) {
            case 'pgsql':
                return "(CASE WHEN CAST($quotedKey AS TEXT) ~ $pattern"
                    . " THEN CAST($quotedKey AS " . self::NUMERIC_TYPE . ") END)";
            case 'sqlsrv':
            case 'mssql':
            case 'dblib':
                // TRY_CAST already answers with NULL instead of failing.
                return "TRY_CAST($quotedKey AS " . self::NUMERIC_TYPE . ")";
            default:
                return "(CASE WHEN CAST($quotedKey AS CHAR) REGEXP $pattern"
                    . " THEN CAST($quotedKey AS " . self::NUMERIC_TYPE . ") END)";
        }
    }

    /** Seam: the other drivers' branches are otherwise only reachable on them. */
    protected function driverName(): string
    {
        return (string) App()->db->getDriverName();
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
