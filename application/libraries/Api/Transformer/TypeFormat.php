<?php

namespace LimeSurvey\Api\Transformer;

class TypeFormat
{
    /**
     * Cast Y/N to boolean
     *
     * Converts 'Y' or 'y' to boolean true.
     * Converts 'N' or 'n' to boolean false.
     * Any other value will produce null.
     *
     * @param mixed $value
     * @return boolean|null
     */
    public static function ynToBool($value)
    {
        $lowercase = strtolower($value);
        if (
             $value === null
             || $value === ""
             || !in_array($lowercase, ['y', 'n'])
        ) {
            return null;
        }
        return $lowercase === 'y';
    }

    /**
     * Cast UTC datetime string to JSON datetime string
     *
     * @see https://www.w3.org/TR/NOTE-datetime
     * @param string $value
     * @param string $timezone
     * @return string|null
     */
    public static function dateTimeToJson($value, $timezone = 'UTC')
    {
        if ($value === null || $value === "") {
            return null;
        }
        $dateTime = date_create(
            $value,
            timezone_open($timezone)
        );
        if (!$dateTime) {
            return null;
        }
        $dateTime->setTimezone(
            timezone_open('UTC')
        );
        return $dateTime->format(
            'Y-m-d\TH:i:s.000\Z'
        );
    }
}
