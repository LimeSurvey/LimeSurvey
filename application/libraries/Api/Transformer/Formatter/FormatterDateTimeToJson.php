<?php

namespace LimeSurvey\Api\Transformer\Formatter;

class FormatterDateTimeToJson implements
    FormatterInterface,
    FormatterRevertibleInterface
{
    /** @var bool */
    private $revert = false;
    /** @var string */
    private $inputTimezone = 'UTC';

    /**
     * @param bool $revert If true performs reverse format conversion
     * @param ?string $inputTimezone Defaults to date_default_timezone_get()
     */
    public function __construct($revert = false, $inputTimezone = null)
    {
        $this->revert = $revert;
        $this->inputTimezone = $inputTimezone ?? date_default_timezone_get();
    }

    /**
     * Cast UTC datetime string to JSON datetime string
     *
     * @see https://www.w3.org/TR/NOTE-datetime
     * @param ?mixed $value
     * @return ?mixed
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
     * @param ?mixed $value
     * @return ?string
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
     * @param ?mixed $value
     * @return ?string
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

    /**
     * Date format
     *
     * @param ?string $value
     * @param string $inputTimeZone
     * @param string $outputTimezone
     * @param string $outputFormat
     * @return ?string
     */
    private function dateFormat(
        $value,
        $inputTimeZone,
        $outputTimezone,
        $outputFormat
    ) {
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
