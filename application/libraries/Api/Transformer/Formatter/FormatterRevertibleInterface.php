<?php

namespace LimeSurvey\Api\Transformer\Formatter;

interface FormatterRevertibleInterface
{
    public function revert($value);
}
