<?php

namespace LimeSurvey\Api\Transformer\Formatter;

class FormatterDateTimeToJson implements
    FormatterInterface,
    FormatterRevertibleInterface
{
    private $revert = false;
    private $inputTimezone = 'UTC';

    public function __construct($revert = false, $inputTimezone = null)
    {
        $this->revert = $revert;
        $this->inputTimezone = $inputTimezone ?? date_default_timezone_get();
    }

    /**
     * Cast UTC datetime string to JSON datetime string
     *
     * @see https://www.w3.org/TR/NOTE-datetime
     * @param string $value
     * @return string|null
     */
    public function format($value)
    {
        return $this->revert
            ? $this->revert($value)
            : $this->apply($value);
    }

    /**
     * Cast UTC datetime string to JSON datetime string
     *
     * @see https://www.w3.org/TR/NOTE-datetime
     * @param string $value
     * @return string|null
     */
    private function apply($value)
    {
        return $this->dateFormat(
            $value,
            $this->inputTimezone,
            'UTC',
            'Y-m-d\TH:i:s.000\Z'
        );
    }

    /**
     * Cast JSON datetime string to UTC datetime string
     *
     * @see https://www.w3.org/TR/NOTE-datetime
     * @param string $value
     * @return string|null
     */
    public function revert($value)
    {
        return $this->dateFormat(
            $value,
            'UTC',
            $this->inputTimezone,
            'c'
        );
    }

    private function dateFormat(
        $value,
        $inputTimeZone, $outputTimezone,
        $outputFormat
    )
    {
        $timezone = $inputTimeZone;
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
            timezone_open($outputTimezone)
        );
        return $dateTime->format(
            $outputFormat
        );
    }
}
