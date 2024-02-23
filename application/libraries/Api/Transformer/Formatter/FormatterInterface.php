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
     * Checks config for this specific formatter,
     * if so it could adjust class properties based on the config.
     * Returns true if this formatter is configured, false otherwise.
     * @param array $config
     * @param array $options
     * @return void
     */
    public function setClassBasedOnConfig($config, $options = []);

    /**
     * Returns boolean indicating whether this formatter is active.
     * @return boolean
     */
    public function isActive();
}
