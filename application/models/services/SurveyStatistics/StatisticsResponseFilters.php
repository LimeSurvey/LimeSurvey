<?php

namespace LimeSurvey\Models\Services\SurveyStatistics;

use InvalidArgumentException;

class StatisticsResponseFilters
{
    /** @var int|null */
    private $minId = null;

    /** @var int|null */
    private $maxId = null;

    /** @var bool|null */
    private $completed = null;

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

    /**
     * @return array<string, int|bool|null>
     */
    public function getFilters(): array
    {
        return array_filter(
            [
                'minId' => $this->minId,
                'maxId' => $this->maxId,
                'completed' => $this->completed,
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
