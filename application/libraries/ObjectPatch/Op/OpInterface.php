<?php

namespace LimeSurvey\ObjectPatch\Op;

use LimeSurvey\ObjectPatch\OpType\OpTypeInterface;

interface OpInterface
{
    public function getEntityType();
    public function getEntityId();
    public function getType(): OpTypeInterface;
    public function getProps();
    public function getContext();
}
