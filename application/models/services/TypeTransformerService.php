<?php

namespace LimeSurvey\Models\Services;

class TypeTransformerService
{
    public function __construct()
    {
    }

    /**
     * Converts an input from its source type into Long Free Text (T)
     *
     * @param string $from the source type
     * @param mix $input the value according to the source type
     * @return mix
     */
    protected function convertToLongFreeText(string $from, $input)
    {
        switch ($from) {
            case \Question::QT_S_SHORT_FREE_TEXT:
                return $input;
            default:
                return null;
        }
    }

    /**
     * Converts an input from a source type to a destination type
     *
     * @param string $from the source type
     * @param string $to the destination type
     * @param mix $input
     * @return mix
     *
     */
    public function convert(string $from, string $to, $input)
    {
        switch ($to) {
            case $from:
                return $input;
            case \Question::QT_T_LONG_FREE_TEXT:
                return $this->convertToLongFreeText($from, $input);
            default:
                return null;
        }
    }
}
