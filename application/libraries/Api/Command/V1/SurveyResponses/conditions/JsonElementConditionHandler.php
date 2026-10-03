<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use CDbCriteria;
use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

/**
 * One element of a JSON array column equals a value.
 */
class JsonElementConditionHandler implements HandlerInterface, SurveyContextAwareInterface
{
    use ConditionHandlerHelperTrait;

    /** A column that cannot hold JSON has no element to match. */
    private const NO_MATCH = '1=0';

    /** Set by the builder; needed only to read a column's storage type. */
    private ?int $surveyId = null;

    public function setSurveyId(?int $surveyId): void
    {
        $this->surveyId = $surveyId;
    }

    public function canHandle(string $operation): bool
    {
        return strtolower($operation) === 'json-element';
    }

    /**
     * @param string|array $key
     * @param string|array $value ['position' => int, 'value' => string]
     */
    public function execute($key, $value): object
    {
        if (is_array($key)) {
            throw new InvalidArgumentException('Multiple keys are not supported for json element conditions.');
        }

        if (!is_array($value) || !isset($value['position']) || !isset($value['value'])) {
            throw new InvalidArgumentException('A json element condition needs a position and a value.');
        }

        $position = (int) $value['position'];
        if ($position < 0) {
            throw new InvalidArgumentException('A json element position cannot be negative.');
        }

        $paramName = $this->nextParamName();
        $condition = $this->conditionFor((string) $key, $position, $paramName);

        $criteria = new CDbCriteria();
        $criteria->condition = $condition;
        // A condition that matches nothing never mentions the parameter, and a
        // bound value the SQL does not use is an error on some drivers.
        if ($condition !== self::NO_MATCH) {
            $criteria->params = [$paramName => (string) $value['value']];
        }

        return $criteria;
    }

    private function conditionFor(string $key, int $position, string $paramName): string
    {
        $db = App()->db;
        $quotedKey = $this->sanitizeKey($key);

        switch ($this->driverName()) {
            case 'pgsql':
                // ->> on a text column is a hard SQL error rather than a row
                // that fails to match, so Postgres only gets the operator when
                // the column really is json. Encrypted rankings are stored as
                // text, and every response would otherwise fail to load.
                return $this->isJsonColumn($key)
                    ? "($quotedKey ->> $position) = $paramName"
                    : self::NO_MATCH;
            case 'sqlsrv':
            case 'mssql':
            case 'dblib':
                $path = $db->quoteValue('$[' . $position . ']');
                return "(ISJSON($quotedKey) = 1 AND JSON_VALUE($quotedKey, $path) = $paramName)";
            default:
                $path = $db->quoteValue('$[' . $position . ']');
                return "(JSON_VALID($quotedKey) "
                    . "AND JSON_UNQUOTE(JSON_EXTRACT($quotedKey, $path)) = $paramName)";
        }
    }

    /** Seam: the two Postgres branches are otherwise only reachable on Postgres. */
    protected function driverName(): string
    {
        return (string) App()->db->getDriverName();
    }

    /**
     * Whether the response column is stored as json, which only Postgres has
     * to ask: the other drivers guard per row inside the condition itself.
     *
     * Mirrors ResponseAggregateBatch::isJsonColumn().
     */
    protected function isJsonColumn(string $field): bool
    {
        if ($this->surveyId === null) {
            return false;
        }

        $column = \SurveyDynamic::model($this->surveyId)->getTableSchema()->getColumn($field);

        return $column !== null && preg_match('/^jsonb?$/i', (string) $column->dbType) === 1;
    }
}
