<?php

namespace LimeSurvey\Models\Services\ResponseFilters;

use InvalidArgumentException;

/**
 * The `filterSet` request param: an ordered list of condition-designer rows,
 * each joined to the previous one with AND or OR.
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

    /**
     * Whether any row filters on participant data, which lives in the survey's
     * token table and is read under a permission of its own.
     */
    public function hasParticipantFilter(): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter->getSource() === ResponseFilter::SOURCE_PARTICIPANT) {
                return true;
            }
        }

        return false;
    }

    public function count(): int
    {
        return count($this->filters);
    }
}
