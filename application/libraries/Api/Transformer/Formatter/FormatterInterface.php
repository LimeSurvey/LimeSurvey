<?php

namespace LimeSurvey\Api\Transformer\Formatter;

interface FormatterInterface
{
    /**
     * Formats the incoming value based on the config
     * @param ?mixed $value
     * @param array $config
     * @param array $options
     * @return ?mixed
     */
    public function format($value, $config, $options = []);


    /**
     * Returns boolean indicating whether this formatter is active.
     * @return boolean
     */
    public function isActive();
}
