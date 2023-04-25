<?php

namespace LimeSurvey\JsonPatch\Op;

use LimeSurvey\JsonPatch\OpType\OpTypeInterface;

interface OpInterface
{
    public function getPath();
    public function getType(): OpTypeInterface;
    public function getValue();
}
