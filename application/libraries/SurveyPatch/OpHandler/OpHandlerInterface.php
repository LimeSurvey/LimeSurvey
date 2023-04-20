<?php

namespace LimeSurvey\SurveyPatch\OpHandler;

use LimeSurvey\SurveyPatch\Op\OpInterface;
use LimeSurvey\SurveyPatch\Pattern\PatternInterface;

interface OpHandlerInterface
{
    public function applyOperation($params, $value);
    public function getOp(): OpInterface;
    public function getPattern(): PatternInterface;
}
