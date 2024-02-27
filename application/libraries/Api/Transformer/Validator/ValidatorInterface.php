<?php

namespace LimeSurvey\Api\Transformer\Validator;

interface ValidatorInterface
{
    /**
     * Validates the key and it's value based on the config.
     * @param string $key
     * @param mixed $value
     * @param array $config
     * @param array $data
     * @param array $options
     * @return array|bool Returns true on success or array of errors.
     */
    public function validate($key, $value, $config, $data, $options = []);

    /**
     * Returns the default config for this validator
     * @return mixed
     */
    public function getDefaultConfig();


    /**
     * Normalises the config value for this validator
     * @param array $config
     * @param array $options
     * @return mixed
     */
    public function normaliseConfigValue($config, $options = []);
}
