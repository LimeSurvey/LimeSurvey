<?php

namespace LimeSurvey\Api\Transformer\Validator;

/**
 * Example config:
 * 'expires' => ['null' => false]
 */
class ValidatorNull implements ValidatorInterface
{
    private string $name = 'null';

    /**
     * @param string $key
     * @param mixed $value
     * @param array $config
     * @param array $data
     * @param array $options
     * @return array|bool
     */
    public function validate($key, $value, $config, $data, $options = [])
    {
        $config[$this->name] = $this->normaliseConfigValue($config);
        $messages = [];
        if ($value === null && $config[$this->name] === false) {
            $messages[] = $key . ' cannot be null';
        }

        return empty($messages) ? true : $messages;
    }

    /**
     * Normalises the config value for this validator
     * @param array $config
     * @return boolean
     */
    public function normaliseConfigValue($config)
    {
        return !isset($config[$this->name]) || $config[$this->name];
    }
}
