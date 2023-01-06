<?php

namespace LimeSurvey\Model\Service\SurveyPatch;

class Meta
{
    protected $path = null;
    protected $variables = [];

    /**
     * @param Path $path
     * @param array $variables
     */
    public function __construct(Path $path, $variables)
    {
        $this->path = $path;
        $this->variables = $variables;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getVariables()
    {
        return $this->variables;
    }
}
