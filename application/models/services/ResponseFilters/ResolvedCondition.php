<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

/**
 * One comparison against one or more real response columns.
 */
class ResolvedCondition
{
    public const OPERATOR_EQUAL = 'equal';
    public const OPERATOR_CONTAIN = 'contain';
    public const OPERATOR_RANGE = 'range';
    public const OPERATOR_DATE_RANGE = 'date-range';
    public const OPERATOR_MULTI_SELECT = 'multi-select';

    /**
     * IS NULL / IS NOT NULL, with value 'true' meaning NOT NULL. Unlike the
     * others this has no `filterMethod` name in the public API: NullConditionHandler
     * is invoked implicitly today, and we address it directly.
     */
    public const OPERATOR_NULL = 'null';

    /**
     * "The respondent put something here." An untouched column can be stored as '' rather
     * than NULL, and IS NOT NULL would count that as an answer.
     */
    public const OPERATOR_NOT_EMPTY = 'not-empty';

    /**
     * "The respondent left this alone." The mirror of OPERATOR_NOT_EMPTY, and
     * needed for the same reason: a column nobody filled can hold NULL or '',
     * so neither test alone finds every such response.
     */
    public const OPERATOR_EMPTY = 'empty';

    /**
     * One element of a JSON array column equals a value. Rankings store the
     * whole answer as a single array of item codes, ordered by rank, so this is
     * the only way to ask "which responses put this item in this place".
     *
     * Value: ['position' => int, 'value' => string], the position being the
     * zero-based index into the array. The SQL differs per database driver, so
     * the handler branches — see ResponseAggregateBatch::jsonElement(), which
     * already does this for the statistics counts.
     */
    public const OPERATOR_JSON_ELEMENT = 'json-element';

    /** @var array<int,string> One column, or several to be OR'd (multiple choice). */
    private array $keys;

    private string $operator;

    /** @var mixed */
    private $value;

    private ?string $relation;

    /**
     * @param array<int,string> $keys
     * @param mixed $value
     * @param string|null $relation Table the keys belong to, when not the
     *     response table itself. See getRelation().
     */
    public function __construct(array $keys, string $operator, $value, ?string $relation = null)
    {
        $this->keys = array_values($keys);
        $this->operator = $operator;
        $this->value = $value;
        $this->relation = $relation;
    }

    /** @return array<int,string> */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * The related table holding these columns, or null for the response table.
     */
    public function getRelation(): ?string
    {
        return $this->relation;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    /** @return mixed */
    public function getValue()
    {
        return $this->value;
    }
}
