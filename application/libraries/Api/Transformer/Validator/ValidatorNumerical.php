<?php

namespace LimeSurvey\Api\Transformer\Validator;

/**
 * Example config (optional min and max):
 * 'tokenLength' => ['numerical' => true] or 'tokenLength' => ['numerical']
 * or
 * 'tokenLength' => ['numerical' => ['min' => -1]]
 * or
 * 'tokenLength' => ['numerical' => ['max' => 50]]
 * or
 * 'tokenLength' => ['numerical' => ['min' => -1, 'max' => 50]]
 */
class ValidatorNumerical implements ValidatorInterface
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
        $config[$this->name] = $this->normaliseConfigValue($config);
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
                    $messages[] = $key . ' must be between ' . $min . ' and ' .
                        $max . '.';
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

    /**
     * Normalises the config value for this validator
     * @param array $config
     * @return mixed
     */
    public function normaliseConfigValue($config)
    {
        // numeric is also allowed as array value
        $key = array_search($this->name, $config, true);
        if (is_int($key)) {
            $config[$this->name] = true;
        }
        return $config[$this->name] ?? false;
    }
}
