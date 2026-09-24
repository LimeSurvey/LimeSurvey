<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

use InvalidArgumentException;

/**
 * The `filterSet` request param: an ordered list of condition-designer rows,
 * each joined to the previous one with AND or OR.
 *
 * Joins are evaluated left to right, so `A OR B AND C` means `(A OR B) AND C`.
 * The UI is a linear list read top to bottom, and this matches how it reads;
 * SQL's "AND binds tighter" would give `A OR (B AND C)` and surprise the user.
 *
 * `filterSet` is separate from the long-standing `filters` param, which keeps
 * its `[{key, filterMethod, value}]` shape. Existing API clients are unaffected;
 * when both are sent they combine with AND.
 */
class ResponseFilterSet
{
    /** @var ResponseFilter[] */
    private array $filters;

    /**
     * @param ResponseFilter[] $filters
     */
    private function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Build from the raw request value. A missing or empty param yields an
     * empty set, which applies no filtering.
     *
     * @param mixed $raw
     * @throws InvalidArgumentException on a malformed set
     */
    public static function fromRequestValue($raw): self
    {
        if ($raw === null || $raw === '' || $raw === []) {
            return new self([]);
        }

        if (!is_array($raw) || !array_is_list($raw)) {
            throw new InvalidArgumentException('filterSet must be a list of filters.');
        }

        $filters = [];
        foreach ($raw as $index => $entry) {
            $filters[] = ResponseFilter::fromArray($entry, $index);
        }

        return new self($filters);
    }

    /** @return ResponseFilter[] */
    public function all(): array
    {
        return $this->filters;
    }

    public function isEmpty(): bool
    {
        return $this->filters === [];
    }

    public function count(): int
    {
        return count($this->filters);
    }
}
