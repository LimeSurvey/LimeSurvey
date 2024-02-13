<?php

namespace LimeSurvey\Api\Transformer\Validators;

class NullValidator implements ValidatorInterface
{
    /**
     * @param string $key
     * @param mixed $value
     * @param array $config
     * @param array $data
     * @param array$options
     * @return array|bool
     */
    public function validate($key, $value, $config, $data, $options = [])
    {
        $messages = [];
        if ($value === null && $config['null'] === false) {
            $messages[] = $key . ' cannot be null';
        }

        return empty($messages) ? true : $messages;
    }
}
