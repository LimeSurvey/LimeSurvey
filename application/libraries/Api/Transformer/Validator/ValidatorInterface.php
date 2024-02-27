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
}
