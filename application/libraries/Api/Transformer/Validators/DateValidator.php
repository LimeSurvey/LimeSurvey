<?php

namespace LimeSurvey\Api\Transformer\Validators;

/**
 * Example config:
 * 'expires' => ['date']
 * or
 * 'expires' => ['date' => true]
 */
class DateValidator implements ValidatorInterface
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
        $messages = [];
        if ($config[$this->name] !== false && !empty($value)) {
            // we expect incoming dates to be in ISO 8601 format (Z at the end is optional)
            //-- Complete precision (2024-12-24T18:00:01.1234):
            $complete = '^\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d:[0-5]\d\.(\d+$|\d+Z$)';
            //-- No milliseconds (2024-12-24T18:00:01):
            $noMili = '^\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d:([0-5]\d$|[0-5]\dZ$)';
            //-- No Seconds (2024-12-24T18:00):
            $noSec = '^\d{4}-[01]\d-[0-3]\dT[0-2]\d:([0-5]\d$|[0-5]\dZ$)';
            //-- No Time (2024-12-24):
            $noTime = '^\d{4}-\d{2}-\d{2}$';

            $regex = "/($complete)|($noMili)|($noSec)|($noTime)/";
            $regexValidator = new RegexValidator();
            $result = $regexValidator->validateByPattern($regex, $value);

            if (is_string($result)) {
                $messages[] = $result;
            }
        }

        return empty($messages) ? true : $messages;
    }

    public function normaliseConfigValue(
        $config,
        $options = []
    ) {
        // date is also allowed as array value
        $key = array_search($this->name, $config, true);
        if (is_int($key)) {
            $config[$this->name] = true;
        }
        return $config[$this->name] ?? $this->getDefaultConfig();
    }

    public function getDefaultConfig()
    {
        return false;
    }
}
