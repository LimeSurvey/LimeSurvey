<?php

namespace LimeSurvey\Api\Transformer\Validator;

/**
 * Example config:
 * 'expires' => ['date']
 * or
 * 'expires' => ['date' => true]
 */
class ValidatorDate implements ValidatorInterface
{
    private string $name = 'date';

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
            // We expect incoming dates to be in ISO 8601 format
            //-- Complete precision
            //- 2024-12-24T18:00:01.1234Z
            //- 2024-12-24T18:00:01.1234
            //- 2024-12-24T18:00:01
            //- 2024-12-24 18:00:01
            $complete = '^\d{4}-\d{2}-\d{2}(T|\s)\d{2}:\d{2}:\d{2}(\.\d+)?Z?$';
            //-- No Seconds
            //- 2024-12-24T18:00
            //- 2024-12-24 18:00
            $noSec = '^\d{4}-\d{2}-\d{2}(T|\s)\d{2}:\d{2}$';
            //-- No Time (2024-12-24):
            $noTime = '^\d{4}-\d{2}-\d{2}$';

            $regex = "/($complete)|($noSec)|($noTime)/";
            $regexValidator = new ValidatorRegex();
            $result = $regexValidator->validateByPattern($regex, $value);

            if (is_string($result)) {
                $messages[] = $result;
            }
        }

        return empty($messages) ? true : $messages;
    }

    /**
     * Normalises the config value for this validator
     * @param array $config
     * @return mixed
     */
    public function normaliseConfigValue($config)
    {
        // date is also allowed as array value
        $key = array_search($this->name, $config, true);
        if (is_int($key)) {
            $config[$this->name] = true;
        }
        return $config[$this->name] ?? false;
    }
}
