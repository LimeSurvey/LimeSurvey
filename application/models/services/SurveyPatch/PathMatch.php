<?php

namespace LimeSurvey\Model\Service\SurveyPatch;

/**
 * Survey Patch Path Match
 */
class PathMatch
{
    protected $modelClass = null;
    protected $variables = [];
    protected $type = null;

    public function __construct($modelClass, $variables = [], $type = null)
    {
        $this->modelClass = $modelClass;
        $this->variables = $variables;
        $this->type = $type;
    }

    /**
     * Get model class
     *
     * @return string
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get variables
     *
     * @return string
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
