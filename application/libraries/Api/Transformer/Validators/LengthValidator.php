<?php

namespace LimeSurvey\Api\Transformer\Validators;

class LengthValidator implements ValidatorInterface
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
        $messages = [];
        if (is_array($config[$this->name])) {
            $length = $this->getLengthOfValue($value, $config);
            $min = $this->getMin($config);
            $max = $this->getMax($config, $length);
            if ($length < $min || $length > $max) {
                $messages[] = $key . ' length must be between ' . $min . ' and ' . $max . '.';
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

    public function normaliseConfigValue(
        $config,
        $options = []
    ) {
        return $config[$this->name] ?? $this->getDefaultConfig();
    }

    public function getDefaultConfig()
    {
        return false;
    }
}
