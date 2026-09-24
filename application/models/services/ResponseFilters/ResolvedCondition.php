<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

/**
 * One comparison against one or more real response columns.
 *
 * This is the resolver's output: the point where a question-shaped filter
 * ("question 42, answer Y") has become storage-shaped ("column 132241X130X2110
 * equals Y"). Nothing past here needs to know about questions.
 *
 * The operators are the vocabulary the existing condition handlers already
 * speak, so a resolved condition maps onto one handler call.
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

    /** @var array<int,string> One column, or several to be OR'd (multiple choice). */
    private array $keys;

    private string $operator;

    /** @var mixed */
    private $value;

    /**
     * @param array<int,string> $keys
     * @param mixed $value
     */
    public function __construct(array $keys, string $operator, $value)
    {
        $this->keys = array_values($keys);
        $this->operator = $operator;
        $this->value = $value;
    }

    /** @return array<int,string> */
    public function getKeys(): array
    {
        return $this->keys;
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
