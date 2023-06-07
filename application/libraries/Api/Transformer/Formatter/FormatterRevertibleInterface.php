<?php

namespace LimeSurvey\Api\Transformer\Formatter;

interface FormatterRevertibleInterface
{
    /**
     * @param ?mixed $value
     * @return ?mixed
     */
    public function revert($value);
}
