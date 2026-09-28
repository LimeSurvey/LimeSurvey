<?php

namespace LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\conditions;

use CDbCriteria;
use InvalidArgumentException;
use LimeSurvey\Libraries\Api\Command\V1\SurveyResponses\HandlerInterface;

/**
 * One element of a JSON array column equals a value.
 *
 * Rankings keep the whole answer as a single array of item codes in rank order,
 * so "who put this item second" is a test on element 1 of that array rather
 * than a comparison of a column.
 *
 * Reading inside JSON is the one thing SQL spells differently everywhere, so
 * this branches per driver — the same branching
 * ResponseAggregateBatch::jsonElement() already does for the statistics counts.
 * The value is bound; only the position, an integer, is written into the SQL.
 */
class JsonElementConditionHandler implements HandlerInterface
{
    use ConditionHandlerHelperTrait;

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

        $quotedKey = $this->sanitizeKey((string) $key);
        $paramName = $this->nextParamName();

        $criteria = new CDbCriteria();
        $criteria->condition = $this->conditionFor($quotedKey, $position, $paramName);
        $criteria->params = [$paramName => (string) $value['value']];

        return $criteria;
    }

    private function conditionFor(string $quotedKey, int $position, string $paramName): string
    {
        $db = App()->db;

        switch ($db->getDriverName()) {
            case 'pgsql':
                return "($quotedKey ->> $position) = $paramName";
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
}
