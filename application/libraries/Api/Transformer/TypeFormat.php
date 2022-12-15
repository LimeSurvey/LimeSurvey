<?php

namespace LimeSurvey\Api\Transformer;

class TypeFormat
{
    /**
     * Cast Y/N to boolean
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
     * @param string $value
     * @return string|null
     */
    public static function dateTimeUtcToJson($value)
    {
        if ($value === null || $value === "") {
            return null;
        }
        $dateTime = date_create(
            $value,
            timezone_open('UTC')
        );
        if (!$dateTime) {
            return null;
        }
        return date_format(
            $dateTime,
            'Y-m-d\TH:i:s.000\Z'
        );
    }
}
