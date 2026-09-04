<?php

namespace LimeSurvey\Api\Transformer\Formatter;

class FormatterLongText implements FormatterInterface
{
    /**
     * Converts $value from a given type into a long text
     * falling back to null if the conversion is unsupported
     *
     * @param ?mixed $value
     * @param array $config
     * @param array $options
     * @return ?mixed
     */
    public function format($value, $config = [], $options = [])
    {
        $fromType = ($options['type'] ?? '');
        switch ($fromType) {
            case \Question::QT_S_SHORT_FREE_TEXT:
                return $value;
            default:
                return null;
        }
    }
}
