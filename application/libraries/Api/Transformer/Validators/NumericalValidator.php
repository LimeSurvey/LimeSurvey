<?php

namespace LimeSurvey\Api\Transformer\Validators;

class NumericalValidator implements ValidatorInterface
{
    private string $name = 'numerical';

    /**
     * @param string $key
     * @param mixed $value
     * @param array $config
     * @param array $data
     * @param array $options
     * @return array|bool
     * @psalm-suppress PossiblyFalseOperand
     */
    public function validate($key, $value, $config, $data, $options = [])
    {
        $messages = [];
        $validate = ($config[$this->name] == true
                || is_array($config[$this->name]))
            && $value !== null;
        if ($validate) {
            if (is_numeric($value)) {
                $min = $this->getMin($config);
                $max = $this->getMax($config);
                $minValid = $min === false || $value >= $min;
                $maxValid = $max === false || $value <= $max;
                if (!$minValid && !$maxValid) {
                    $messages[] = $key . ' must be between ' . $min . ' and ' . $max . '.';
                } elseif (!$minValid) {
                    $messages[] = $key . ' must be higher than ' . $min . '.';
                } elseif (!$maxValid) {
                    $messages[] = $key . ' must be lower than ' . $max . '.';
                }
            } else {
                $messages[] = $key . ' must be numeric.';
            }
        }

        return empty($messages) ? true : $messages;
    }

    /**
     * @param array $config
     * @return int|false
     */
    private function getMin($config)
    {
        return is_array($config[$this->name]) && array_key_exists(
            'min',
            $config[$this->name]
        ) ? (int)$config[$this->name]['min'] : false;
    }

    /**
     * @param array $config
     * @return int|false
     */
    private function getMax($config)
    {
        return is_array($config[$this->name]) && array_key_exists(
            'max',
            $config[$this->name]
        ) ? (int)$config[$this->name]['max'] : false;
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
