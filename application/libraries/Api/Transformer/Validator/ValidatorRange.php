<?php

namespace LimeSurvey\Api\Transformer\Validator;

/**
 * Example config:
 * 'showGroupInfo' => ['range' => ['B', 'N', 'D', 'X', 'I']]
 */
class ValidatorRange implements ValidatorInterface
{
    private string $name = 'range';

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
        if ($config[$this->name] !== false && !empty($value)) {
            $range = $this->getRange($config);
            if (!empty($range)) {
                if (!in_array($value, $range, true)) {
                    $messages[] = $value . ' is not in the list.';
                }
            }
        }

        return empty($messages) ? true : $messages;
    }

    /**
     * @param array $config
     * @return array
     */
    private function getRange($config)
    {
        return is_array($config[$this->name]) ? $config[$this->name] : [];
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
