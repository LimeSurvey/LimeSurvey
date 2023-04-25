<?php

namespace LimeSurvey\JsonPatch\Op;

use LimeSurvey\JsonPatch\JsonPatchException;
use LimeSurvey\JsonPatch\OpType\OpTypeInterface;
use LimeSurvey\JsonPatch\OpType\OpType;

class OpStandard implements OpInterface
{
    private $path = null;
    private $type = null;
    private $value = null;

    public function __construct($path, OpTypeInterface $type, $value)
    {
        $this->path = $path;
        $this->type = $type;
        $this->value = $value;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getType(): OpTypeInterface
    {
        return $this->type;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * Factory
     *
     * @param string $path
     * @param string $type
     * @param string $value
     * @throws JsonPatchException
     * @return OpStandard
     */
    public static function factory($path, $type, $value)
    {
        if (!isset($path)) {
            throw new JsonPatchException(sprintf(
                'Invalid operation path for "%s":"%s"',
                $type ? $type->getid() : '?',
                $path
            ));
        }

        $opType = OpType::factory($type);

        return new static($path, $opType, $value);
    }
}
