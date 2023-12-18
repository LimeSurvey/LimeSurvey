<?php

namespace LimeSurvey\Api\Transformer\Formatter;

/**
 * this class is extending the FormatterYnToBool class in revert mode
 * to be able to translate null value to 'S'.
 * It is only needed for prop of type "mandatory"
 */
class FormatterMandatory extends FormatterYnToBool
{
    public function __construct()
    {
        parent::__construct(true);
    }

    /**
     * if parent revert function returns null, 'S' is returned
     *
     * @param ?mixed $value
     * @return ?mixed
     */
    public function revert($value)
    {
        $string = parent::revert($value);

        return $string === null ? 'S' : $string;
    }
}
