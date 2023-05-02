<?php

namespace LimeSurvey\JsonPatch\OpHandler;

use LimeSurvey\JsonPatch\OpType\OpTypeInterface;
use LimeSurvey\JsonPatch\Pattern\PatternInterface;

interface OpHandlerInterface
{
    public function applyOperation($params, $value);
    public function getOpType(): OpTypeInterface;
    public function getPattern(): PatternInterface;
}
