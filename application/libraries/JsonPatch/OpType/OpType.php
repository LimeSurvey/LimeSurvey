<?php

namespace LimeSurvey\JsonPatch\OpType;

use LimeSurvey\JsonPatch\JsonPatchException;

abstract class OpType implements OpTypeInterface
{
    private const CODE = '';

    public function getId()
    {
        return static::CODE;
    }

    public function __toString()
    {
        return static::getId();
    }

    /**
     * Factory
     *
     * @throws JsonPatchException
     */
    public static function factory($type): OpTypeInterface
    {
        $validTypes = ['add', 'copy', 'move', 'remove', 'replace', 'test'];

        if (!in_array($type, $validTypes)) {
            throw new JsonPatchException(sprintf(
                'Invalid operation type "%s"',
                $type
            ));
        }
        $class = __NAMESPACE__ . '\OpType' . ucfirst($type);
        return new $class;
    }
}
