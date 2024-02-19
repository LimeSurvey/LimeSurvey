<?php

namespace LimeSurvey\Api\Transformer\Formatter;

interface FormatterInterface
{
    /**
     * @param ?mixed $value
     * @return ?mixed
     */
    public function format($value);

    /**
     * @param array $config
     * @param array $options
     * @return mixed
     */
    public function normaliseConfigValue($config, $options = []);
}
