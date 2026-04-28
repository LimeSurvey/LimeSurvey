<?php

namespace LimeSurvey\Api\Transformer\Formatter;

/**
 * Formatter DateTime to Json
 *
 * This formatter converts date/time string values assumed to be in server timezone
 * to UTC time and formats to the JSON standard 'Y-m-d\TH:i:s.000\Z'.
 *
 *
 * For values that should always be displayed as is, we should not use this formatter
 * but instead use only the 'date' valitator. For exmaple we use this formatter on
 * 'survey.dateCreated' but not on 'survey.expires' or 'survey.startDate' because we
 * want to display and edit the values of 'survey.expires' or 'survey.startDate' using
 * the server timezone not the local timezone.
 */
class FormatterDateTimeToJson implements FormatterInterface
{
    /**
     * Cast UTC datetime string to JSON datetime string
     *
     * @see https://www.w3.org/TR/NOTE-datetime
     * @param ?mixed $value
     * @param array $config
     * @param array $options
     * @return ?mixed
     */
    public function format($value, $config = [], $options = [])
    {
        $revert = array_key_exists(
            'revert',
            $config
        ) ? $config['revert'] : false;
        return $revert
            ? $this->revert($value, $config)
            : $this->apply($value, $config);
    }

    /**
     * Cast UTC datetime string to JSON datetime string
     *
     * @see https://www.w3.org/TR/NOTE-datetime
     * @param ?mixed $value
     * @param array $config
     * @return ?string
     */
    protected function apply($value, array $config)
    {
        $inputTimezone = array_key_exists(
            'inputTimezone',
            $config
        ) ? $config['inputTimezone'] : date_default_timezone_get();
        return $this->dateFormat(
            $value,
            $inputTimezone,
            'UTC',
            'Y-m-d\TH:i:s.000\Z',
            $config
        );
    }

    /**
     * Cast JSON datetime string to UTC datetime string
     *
     * @see https://www.w3.org/TR/NOTE-datetime
     * @param ?mixed $value
     * @param array $config
     * @return ?string
     */
    protected function revert($value, array $config)
    {
        $inputTimezone = array_key_exists(
            'inputTimezone',
            $config
        ) ? $config['inputTimezone'] : date_default_timezone_get();
        return $this->dateFormat(
            $value,
            'UTC',
            $inputTimezone,
            'Y-m-d H:i:s',
            $config
        );
    }

    /**
     * Date format
     *
     * @param ?string $value
     * @param string $inputTimeZone
     * @param string $outputTimezone
     * @param string $outputFormat
     * @param array $config
     * @return ?string
     */
    protected function dateFormat(
        $value,
        $inputTimeZone,
        $outputTimezone,
        $outputFormat,
        $config
    ) {
        $timezone = $inputTimeZone;
        if ($value === null || $value === '') {
            return array_key_exists(
                'clearWithEmptyString',
                $config
            ) && $config['clearWithEmptyString'] ? '' : null;
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
