<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

/**
 * One filterSet row, resolved: its join plus the conditions it produced.
 *
 * A row can produce more than one condition — a dual-scale question with both
 * scales answered resolves to two columns that must both match. Those combine
 * *inside* the row, and the row's own join applies to the group as a whole.
 * Flattening them would break that: with a row joined by OR,
 * `A OR (B1 AND B2)` is not `(A OR B1) AND B2`.
 *
 * Inside a row the conditions normally AND, because they are parts of one
 * answer the user described. The exception is a set of options picked from a
 * single list, where the user means "any of these" — see $innerJoin.
 */
class ResolvedFilter
{
    private string $join;

    /** @var ResolvedCondition[] */
    private array $conditions;

    private string $innerJoin;

    /**
     * @param ResolvedCondition[] $conditions
     * @param string $innerJoin How this row's own conditions combine.
     */
    public function __construct(
        string $join,
        array $conditions,
        string $innerJoin = ResponseFilter::JOIN_AND
    ) {
        $this->join = $join;
        $this->conditions = array_values($conditions);
        $this->innerJoin = $innerJoin;
    }

    public function getJoin(): string
    {
        return $this->join;
    }

    public function getInnerJoin(): string
    {
        return $this->innerJoin;
    }

    /** Whether this row's own conditions OR together instead of ANDing. */
    public function isInnerOr(): bool
    {
        return $this->innerJoin === ResponseFilter::JOIN_OR;
    }

    /** Whether this row ORs onto the one before it (ignored on the first). */
    public function isOr(): bool
    {
        return $this->join === ResponseFilter::JOIN_OR;
    }

    /** @return ResolvedCondition[] */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    /**
     * A row the user started but left without a value. The modal allows this:
     * picking a survey-data field alone makes the row "complete" enough to
     * apply. Such rows are dropped before the conditions are combined — along
     * with their join, so an empty OR row does not widen the result set back to
     * everything. An unfilled row is not yet a filter.
     */
    public function isEmpty(): bool
    {
        return $this->conditions === [];
    }
}
