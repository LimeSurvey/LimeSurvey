<?php

namespace LimeSurvey\Api\Transformer\Validators;

/**
 * Example config:
 * 'showGroupInfo' => ['range' => ['B', 'N', 'D', 'X', 'I']]
 */
class RangeValidator implements ValidatorInterface
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
