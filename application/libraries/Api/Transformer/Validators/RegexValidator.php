<?php

namespace LimeSurvey\Api\Transformer\Validators;

class RegexValidator implements ValidatorInterface
{
    private string $name = 'pattern';

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
            $match = preg_match($config[$this->name], $value);
            if ($match !== 1) {
                $messages[] = $value . " doesn't match expected pattern.";
            }
        }

        return empty($messages) ? true : $messages;
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
