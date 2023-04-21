<?php

namespace LimeSurvey\JsonPatch\OpHandler;

use LimeSurvey\JsonPatch\Op\OpInterface;
use LimeSurvey\JsonPatch\Pattern\PatternInterface;

interface OpHandlerInterface
{
    public function applyOperation($params, $value);
    public function getOp(): OpInterface;
    public function getPattern(): PatternInterface;
}
