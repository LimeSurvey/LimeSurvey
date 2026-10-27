<?php

namespace LimeSurvey\Models\Services\SurveyStatistics;

use InvalidArgumentException;

class StatisticsResponseFilters
{
    public const MAX_SEARCH_TERMS = 10;
    public const MAX_SEARCH_TERM_LENGTH = 250;

    /** @var int|null */
    private $minId = null;

    /** @var int|null */
    private $maxId = null;

    /** @var bool|null */
    private $completed = null;
    private ?string $completion = null;
    /** @var list<array{key: string, filterMethod: string, value: mixed}> */
    private array $conditions = [];

    /** @var string[] */
    private $searchTerms = [];

    /**
     * @param int|null $minId
     * @return self
     * @throws InvalidArgumentException
     */
    public function setMinId(?int $minId): self
    {
        if ($minId !== null && $minId < 0) {
            throw new InvalidArgumentException('Min ID cannot be negative');
        }

        if ($minId !== null && $this->maxId !== null && $minId > $this->maxId) {
            throw new InvalidArgumentException('Min ID cannot be greater than Max ID');
        }

        $this->minId = $minId;
        return $this;
    }

    /**
     * @param int|null $maxId
     * @return self
     * @throws InvalidArgumentException
     */
    public function setMaxId(?int $maxId): self
    {
        if ($maxId !== null && $maxId < 0) {
            throw new InvalidArgumentException('Max ID cannot be negative');
        }

        if ($maxId !== null && $this->minId !== null && $maxId < $this->minId) {
            throw new InvalidArgumentException('Max ID cannot be less than Min ID');
        }

        $this->maxId = $maxId;
        return $this;
    }

    /**
     * @param bool|null $completed
     * @return self
     */
    public function setCompleted(?bool $completed): self
    {
        $this->completed = $completed;
        return $this;
    }

    public function setCompletion(string $completion): self
    {
        if (!in_array($completion, ['all', 'completed', 'incomplete', 'screened_out'], true)) {
            throw new InvalidArgumentException('Invalid completion filter');
        }
        $this->completion = $completion === 'all' ? null : $completion;
        return $this;
    }

    public function setConditions(array $conditions): self
    {
        if (count($conditions) > 20) {
            throw new InvalidArgumentException('No more than 20 response conditions are allowed');
        }
        foreach ($conditions as $condition) {
            if (!is_array($condition)
                || !isset($condition['key'], $condition['filterMethod'], $condition['value'])
                || !is_string($condition['key'])
                || !is_string($condition['filterMethod'])
                || !in_array($condition['filterMethod'], ['equal', 'multi-select', 'range'], true)
            ) {
                throw new InvalidArgumentException('Invalid response condition');
            }
            $values = $condition['filterMethod'] === 'equal' ? [$condition['value']] : $condition['value'];
            if (!is_array($values) || !$values || count($values) > 50
                || ($condition['filterMethod'] === 'range' && count($values) !== 2)
            ) {
                throw new InvalidArgumentException('Invalid response condition values');
            }
            foreach ($values as $value) {
                if (!is_scalar($value) || mb_strlen((string)$value) > 250) {
                    throw new InvalidArgumentException('Response condition values must be short scalar values');
                }
            }
        }
        $this->conditions = array_values($conditions);
        return $this;
    }

    /**
     * Free-text search terms, response matches when every term appears
     * (case-insensitively) in at least one of its text answer columns.
     *
     * @param string[] $terms
     * @return self
     * @throws InvalidArgumentException
     */
    public function setSearchTerms(array $terms): self
    {
        $clean = [];
        foreach ($terms as $term) {
            if (!is_string($term)) {
                throw new InvalidArgumentException('Search terms must be strings');
            }

            $term = trim($term);
            if ($term === '' || in_array($term, $clean, true)) {
                continue;
            }

            if (mb_strlen($term) > self::MAX_SEARCH_TERM_LENGTH) {
                throw new InvalidArgumentException(
                    'Search terms cannot be longer than ' . self::MAX_SEARCH_TERM_LENGTH . ' characters'
                );
            }

            $clean[] = $term;
        }

        if (count($clean) > self::MAX_SEARCH_TERMS) {
            throw new InvalidArgumentException(
                'No more than ' . self::MAX_SEARCH_TERMS . ' search terms are allowed'
            );
        }

        $this->searchTerms = $clean;
        return $this;
    }

    /**
     * @return array<string, int|bool|string|list<string>|list<array{key: string, filterMethod: string, value: mixed}>>
     */
    public function getFilters(): array
    {
        return array_filter(
            [
                'minId' => $this->minId,
                'maxId' => $this->maxId,
                'completed' => $this->completed,
                'completion' => $this->completion,
                'search' => $this->searchTerms ?: null,
                'conditions' => $this->conditions ?: null,
            ],
            static function ($value): bool {
                return $value !== null;
            }
        );
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->getFilters());
    }
}
