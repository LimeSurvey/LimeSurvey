<?php

namespace LimeSurvey\ObjectPatch\OpType;

use LimeSurvey\ObjectPatch\ObjectPatchException;

abstract class OpType implements OpTypeInterface
{
    private const ID = '';

    public function getId()
    {
        return static::ID;
    }

    public function __toString()
    {
        return static::getId();
    }

    /**
     * Factory
     *
     * @throws ObjectPatchException
     */
    public static function factory($type): OpTypeInterface
    {
        $validTypes = ['create', 'update', 'delete'];

        if (!in_array($type, $validTypes)) {
            throw new ObjectPatchException(sprintf(
                'Invalid operation type "%s"',
                $type
            ));
        }
        $class = __NAMESPACE__ . '\OpType' . ucfirst($type);
        return new $class();
    }
}
