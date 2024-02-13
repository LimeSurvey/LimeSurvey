<?php

namespace LimeSurvey\Api\Transformer\Validators;

class LengthValidator implements ValidatorInterface
{
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
        $length = $this->getLengthOfValue($value, $config);
        $min = $this->getMin($config);
        $max = $this->getMax($config, $length);
        if ($length < $min || $length > $max) {
            $messages[] = $key . ' length must be between ' . $min . ' and ' . $max . '.';
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
        return is_array($config['length']) && array_key_exists(
            'min',
            $config['length']
        ) ? (int) $config['length']['min'] : 0;
    }

    /**
     * @param array $config
     * @param int $length
     * @return int
     */
    private function getMax($config, $length)
    {
        return is_array($config['length']) && array_key_exists(
            'max',
            $config['length']
        ) ? (int) $config['length']['max'] : $length;
    }
}
