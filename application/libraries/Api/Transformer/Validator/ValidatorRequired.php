<?php

namespace LimeSurvey\Api\Transformer\Validator;

/**
 * Example config:
 * 'expires' => ['required'] or 'expires' => ['required' => true]
 * or required only on certain operation types
 * 'expires' => ['required' => ['create', 'update']]
 */
class ValidatorRequired implements ValidatorInterface
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
        $config[$this->name] = $this->normaliseConfigValue($config, $options);
        $messages = [];
        if (
            $config[$this->name]
            && !array_key_exists($key, $data)
        ) {
            $messages[] = $key . ' is required';
        }

        return empty($messages) ? true : $messages;
    }

    /**
     * Normalises the config value for this validator
     * @param array $config
     * @param array $options
     * @return mixed
     */
    public function normaliseConfigValue($config, $options = [])
    {
        $key = array_search($this->name, $config, true);
        if (is_int($key)) {
            $config[$this->name] = true;
        }
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
            $config[$this->name] = false;
        }

        return $config[$this->name];
    }
}
