<?php

namespace LimeSurvey\Api\Transformer;

class TypeFormat
{
    public static function ynToBool($value)
    {
        return strtolower($value) === 'y';
    }

    public static function dateTimeUtcToJson($value)
    {
        return date_format(
            date_create($value),
            'Y-m-d\TH:i:s.000\Z'
        );
    }
}
