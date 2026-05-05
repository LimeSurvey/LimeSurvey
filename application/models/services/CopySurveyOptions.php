<?php

namespace LimeSurvey\Models\Services;

/**
 * This class is a data provider containing all
 * options that could be selected for copying a survey.
 */
class CopySurveyOptions
{
    /** @var bool whether to copy resources and links */
    private bool $resourcesAndLinks;

    /** @var bool whether to copy answer options of the questions of a survey */
    private bool $answerOptions;

    /** @var bool whether to copy survey conditions */
    private bool $conditions;

    /** @var bool whether to copy survey quotas */
    private bool $quotas;

    /** @var bool whether to copy survey permissions */
    private bool $permissions;

    /** @var bool whether to reset a surveys star and end date */
    private bool $resetStartAndEndDate;

    /** @var bool whether to reset a surveys response start-id */
    private bool $resetResponseStartId;

    /**
     * Sets an initial state for copying options.
     *
     * The initial state is, that everything is copied and nothing is reset.
     */
    public function __construct()
    {
        $this->resourcesAndLinks = true;
        $this->answerOptions = true;
        $this->conditions = true;
        $this->quotas = true;
        $this->permissions = true;
        $this->resetStartAndEndDate = false;
        $this->resetResponseStartId = false;
    }

    public function isResourcesAndLinks(): bool
    {
        return $this->resourcesAndLinks;
    }

    public function setResourcesAndLinks(bool $resourcesAndLinks): void
    {
        $this->resourcesAndLinks = $resourcesAndLinks;
    }

    public function isAnswerOptions(): bool
    {
        return $this->answerOptions;
    }

    public function setAnswerOptions(bool $answerOptions): void
    {
        $this->answerOptions = $answerOptions;
    }

    public function isConditions(): bool
    {
        return $this->conditions;
    }

    public function setConditions(bool $conditions): void
    {
        $this->conditions = $conditions;
    }

    public function isQuotas(): bool
    {
        return $this->quotas;
    }

    public function setQuotas(bool $quotas): void
    {
        $this->quotas = $quotas;
    }

    public function isPermissions(): bool
    {
        return $this->permissions;
    }

    public function setPermissions(bool $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function isStartAndEndDate(): bool
    {
        return $this->resetStartAndEndDate;
    }

    public function setStartAndEndDate(bool $resetStartAndEndDate): void
    {
        $this->resetStartAndEndDate = $resetStartAndEndDate;
    }

    public function isResetResponseStartId(): bool
    {
        return $this->resetResponseStartId;
    }

    public function setResetResponseStartId(bool $resetResponseStartId): void
    {
        $this->resetResponseStartId = $resetResponseStartId;
    }
}
