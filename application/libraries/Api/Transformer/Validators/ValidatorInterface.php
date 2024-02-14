<?php

namespace LimeSurvey\Api\Transformer\Validators;

interface ValidatorInterface
{
    /**
     * @param string $key
     * @param mixed $value
     * @param array $config
     * @param array $data
     * @param array $options
     * @return array|bool Returns true on success or array of errors.
     */
    public function validate($key, $value, $config, $data, $options = []);

    /**
     * @return mixed
     */
    public function getDefaultConfig();


    /**
     * @param array $config
     * @param array $options
     * @return mixed
     */
    public function normaliseConfigValue($config, $options = []);
}
