<?php

namespace LimeSurvey\Api\Transformer\Validator;

/**
 * Example config (requires min and/or max):
 * 'admin' => ['length' => ['min' => 1, 'max' => 50]]
 * or
 * 'admin' => ['length' => ['min' => 1]]
 * or
 * 'admin' => ['length' => ['max' => 50]]
 */
class ValidatorLength implements ValidatorInterface
{
    private string $name = 'length';

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
        if (is_array($config[$this->name])) {
            $length = $this->getLengthOfValue($value, $config);
            $min = $this->getMin($config);
            $max = $this->getMax($config, $length);
            if ($length < $min || $length > $max) {
                $messages[] = $key . ' length must be between ' . $min .
                    ' and ' . $max . '.';
            }
        }

        return empty($messages) ? true : $messages;
    }

    /**
     * @param mixed $value
     * @param array $config
     * @return int
     */
    private function getLengthOfValue($value, $config)
    {
        return is_string($value) ? strlen($value) : $this->getMin($config);
    }

    /**
     * @param array $config
     * @return int
     */
    private function getMin($config)
    {
        return is_array($config[$this->name]) && array_key_exists(
            'min',
            $config[$this->name]
        ) ? (int)$config[$this->name]['min'] : 0;
    }

    /**
     * @param array $config
     * @param int $length
     * @return int
     */
    private function getMax($config, $length)
    {
        return is_array($config[$this->name]) && array_key_exists(
            'max',
            $config[$this->name]
        ) ? (int)$config[$this->name]['max'] : $length;
    }

    /**
     * Normalises the config value for this validator
     * @param array $config
     * @return mixed
     */
    public function normaliseConfigValue($config)
    {
        return $config[$this->name] ?? false;
    }
}
