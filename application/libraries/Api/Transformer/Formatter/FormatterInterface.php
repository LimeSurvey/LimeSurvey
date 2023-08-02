<?php

namespace LimeSurvey\Api\Transformer\Formatter;

interface FormatterInterface
{
    /**
     * @param ?mixed $value
     * @return ?mixed
     */
    public function format($value);
}
