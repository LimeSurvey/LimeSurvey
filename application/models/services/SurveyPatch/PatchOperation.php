<?php

namespace LimeSurvey\Model\Service\SurveyPatch;

/**
 * Patch operation
 */
class PatchOperation
{
    protected $type = null;
    protected $path = null;
    protected $value = null;

    public function __construct($op, $path, $value)
    {
        $this->type = $op;
        $this->path = $path;
        $this->value = $value;
    }

    /**
     * Get type
     *
     * @return ?string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get path
     *
     * @return ?string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get value
     *
     * @return ?string
     */
    public function getValue()
    {
        return $this->value;
    }
}
