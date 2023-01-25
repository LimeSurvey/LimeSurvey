<?php

namespace LimeSurvey\Models\Services\SurveyPatch;

/**
 * Patch operation
 */
class Operation
{
    const OPERATION_TYPE_ADD = 'add';
    const OPERATION_TYPE_REMOVE = 'remove';
    const OPERATION_TYPE_REPLACE = 'replace';
    const OPERATION_TYPE_COPY = 'copy';
    const OPERATION_TYPE_MOVE = 'move';
    const OPERATION_TYPE_TEST = 'test';
    const OPERATION_TYPE_UPDATE = 'update'; // custom operation for partial object replacement

    protected $type = null;
    protected $path = null;
    protected $value = null;

    public function __construct($type, $path, $value)
    {
        $this->type = $type;
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
     * @return ?mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
