<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

/**
 * One filterSet row, resolved: its join plus the conditions it produced.
 *
 * A row can produce more than one condition — a dual-scale question with both
 * scales answered resolves to two columns that must both match. Those AND
 * together *inside* the row, and the row's own join applies to the group as a
 * whole. Flattening them would break that: with a row joined by OR,
 * `A OR (B1 AND B2)` is not `(A OR B1) AND B2`.
 */
class ResolvedFilter
{
    private string $join;

    /** @var ResolvedCondition[] */
    private array $conditions;

    /**
     * @param ResolvedCondition[] $conditions
     */
    public function __construct(string $join, array $conditions)
    {
        $this->join = $join;
        $this->conditions = array_values($conditions);
    }

    public function getJoin(): string
    {
        return $this->join;
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
