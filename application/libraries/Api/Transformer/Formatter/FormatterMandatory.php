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
     * this formatter is set to revert mode by default as it is only called
     * from an input transformer
     * @param ?mixed $value
     * @param array $config
     * @param array $options
     * @return ?mixed
     */
    public function format($value, $config = [], $options = [])
    {
        $revert = array_key_exists(
            'revert',
            $config
        ) ? $config['revert'] : true;
        return $revert
            ? $this->revert($value)
            : $this->apply($value);
    }

    /**
     * if parent revert function returns null, 'S' is returned
     *
     * @param ?mixed $value
     * @param array $config
     * @return ?mixed
     */
    protected function revert($value, array $config = [])
    {
        $string = parent::revert($value, $config);
        return $string === null ? 'S' : $string;
    }
}
