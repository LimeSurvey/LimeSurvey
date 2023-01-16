<?php

namespace LimeSurvey\Model\Service\SurveyPatch;

/**
 * Survey Patch Meta
 *
 * Represents an updatable survey patch path.
 */
class Meta
{
    protected $path = null;
    protected $variables = [];

    /**
     * Survey Patch Constructor
     *
     * @param string $path
     * @param array $variables
     */
    public function __construct($realPath, $variables)
    {
        $this->path = $realPath;
        $this->variables = $variables;
    }

    /**
     * Get path
     *
     * @return string $path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get variables
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
