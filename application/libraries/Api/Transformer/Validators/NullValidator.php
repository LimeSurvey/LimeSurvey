<?php

namespace LimeSurvey\Api\Transformer\Validators;

/**
 * Example config:
 * 'expires' => ['null' => false]
 */
class NullValidator implements ValidatorInterface
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
        $messages = [];
        if ($value === null && $config[$this->name] === false) {
            $messages[] = $key . ' cannot be null';
        }

        return empty($messages) ? true : $messages;
    }

    public function normaliseConfigValue(
        $config,
        $options = []
    ) {
        return isset($config[$this->name]) ? (bool)$config[$this->name] : $this->getDefaultConfig();
    }

    public function getDefaultConfig()
    {
        return true;
    }
}
