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

    public function __construct($entityType, OpTypeInterface $type, $entityId, $props)
    {
        $this->entityType = $entityType;
        $this->type = $type;
        $this->entityId = $entityId;
        $this->props = $props;
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

    /**
     * Factory
     *
     * @param string $entityType
     * @param string $type
     * @param mixed $entityId
     * @param array $props
     * @throws ObjectPatchException
     * @return OpStandard
     */
    public static function factory($entityType, $type, $entityId, $props)
    {
        $opType = OpType::factory($type);
        return new static(
            $entityType,
            $opType,
            $entityId,
            $props
        );
    }
}
