<?php

namespace LimeSurvey\Api\Transformer\Validators;

class RequiredValidator implements ValidatorInterface
{
    private string $name = 'required';

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
            $config[$this->name]
            && !array_key_exists($key, $data)
        ) {
            $messages[] = $key . ' is required'; // TODO: translate?
        }

        return empty($messages) ? true : $messages;
    }

    public function normaliseConfigValue(
        $config,
        $options = []
    ) {
        if (isset($config[$this->name])) {
            // required can be operation specific by specifying
            // - a string or an array of operation names
            if (
                isset($options['operation'])
                && (
                    is_string($config[$this->name])
                    || is_array($config[$this->name])
                )
            ) {
                $config[$this->name] = (
                    (
                        is_string($config[$this->name])
                        && $config[$this->name] == $options['operation']
                    )
                    ||
                    (
                        is_array($config[$this->name])
                        && in_array(
                            $options['operation'],
                            $config[$this->name]
                        )
                    )
                );
            }
        } else {
            $config[$this->name] = $this->getDefaultConfig();
        }

        return $config[$this->name];
    }

    public function getDefaultConfig()
    {
        return false;
    }
}
