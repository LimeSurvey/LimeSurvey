<?php

namespace LimeSurvey\Api\Transformer\Formatter;

/**
 * This class is extending the FormatterYnToBool class in revert mode
 * to be able to translate null value to 'S'.
 * It is only needed for prop of type "mandatory"
 */
class FormatterMandatory extends FormatterYnToBool
{
    /**
     * @param bool $revert
     */
    public function __construct($revert = false)
    {
        parent::__construct(!$revert);
    }

    /**
     * if parent revert function returns null, 'S' is returned
     *
     * @param ?mixed $value
     * @return ?mixed
     */
    protected function revert($value)
    {
        $string = parent::revert($value);
        return $string === null ? 'S' : $string;
    }
}
