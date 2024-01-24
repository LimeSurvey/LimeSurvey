<?php

namespace LimeSurvey\ObjectPatch\Op;

use LimeSurvey\ObjectPatch\ObjectPatchException;
use LimeSurvey\ObjectPatch\OpType\OpTypeInterface;
use LimeSurvey\ObjectPatch\OpType\OpType;

class OpStandard implements OpInterface
{
    private $entityType = null;
    private $type = null;
    private $entityId = null;
    private $props = null;
    private $context = null;

    public function __construct($entityType, OpTypeInterface $type, $entityId, $props, $context)
    {
        $this->entityType = $entityType;
        $this->type = $type;
        $this->entityId = $entityId;
        $this->props = $props;
        $this->context = $context;
    }

    public function getEntityType()
    {
        return $this->entityType;
    }

    public function getEntityId()
    {
        return $this->entityId;
    }

    public function getType(): OpTypeInterface
    {
        return $this->type;
    }

    public function getProps()
    {
        return $this->props;
    }

    public function getContext()
    {
        return $this->context;
    }

    /**
     * Factory
     *
     * @param string $entityType
     * @param string $type
     * @param mixed $entityId
     * @param array $props
     * @param array $context
     * @throws ObjectPatchException
     * @return OpStandard
     */
    public static function factory($entityType, $type, $entityId, $props, $context)
    {
        $opType = OpType::factory($type);
        return new static(
            $entityType,
            $opType,
            $entityId,
            $props,
            $context
        );
    }
}
