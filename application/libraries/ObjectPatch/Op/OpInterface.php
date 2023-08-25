<?php

namespace LimeSurvey\ObjectPatch\Op;

use LimeSurvey\Api\Transformer\TransformerInterface;
use LimeSurvey\ObjectPatch\OpType\OpTypeInterface;

interface OpInterface
{
    public function getEntityType();
    public function getEntityId(?TransformerInterface $transformer = null);
    public function getType(): OpTypeInterface;
    public function getProps();
    public function getContext();
}
