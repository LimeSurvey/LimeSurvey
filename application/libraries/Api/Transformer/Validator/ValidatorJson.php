<?php

namespace LimeSurvey\Api\Transformer\Validator;

/**
 * Validates that an incoming value is a valid JSON encoded string.
 * Used for fields that are stored as-is (already JSON encoded) in the
 * database, e.g. survey welcome_image, so malformed JSON is rejected
 * before it can be persisted.
 *
 * Example config:
 * 'welcomeImage' => ['key' => 'welcome_image', 'json' => true]
 */
class ValidatorJson implements ValidatorInterface
{
    private string $name = 'json';

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
        if ($config[$this->name] && !empty($value)) {
            if (!is_string($value)) {
                $messages[] = $key . ' must be a JSON encoded string.';
            } else {
                json_decode($value);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $messages[] = $key . ' is not valid JSON.';
                }
            }
        }

        return empty($messages) ? true : $messages;
    }

    /**
     * Normalises the config value for this validator
     * @param array $config
     * @return bool
     */
    public function normaliseConfigValue($config)
    {
        return $config[$this->name] ?? false;
    }
}
