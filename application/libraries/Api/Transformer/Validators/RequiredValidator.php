<?php

namespace LimeSurvey\Api\Transformer\Validators;

class RequiredValidator implements ValidatorInterface
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
        if (
            $config['required']
            && !array_key_exists($key, $data)
        ) {
            $messages[] = $key . ' is required'; // TODO: translate?
        }

        return empty($messages) ? true : $messages;
    }
}
