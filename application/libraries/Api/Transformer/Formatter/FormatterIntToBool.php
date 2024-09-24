<?php

namespace LimeSurvey\Api\Transformer\Formatter;

class FormatterIntToBool implements FormatterInterface
{
    /**
     * Cast integer to boolean
     *
     * Converts number greater than zero to boolean true.
     * Converts number of zero or less to boolean false.
     * Convert empty string to null.
     * Null is passed through unchanged.
     *
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
            ? $this->revert($value)
            : $this->apply($value);
    }

    /**
     * Cast integer to boolean
     *
     * Converts number greater than zero to boolean true.
     * Converts number of zero or less to boolean false.
     * Convert empty string to null.
     * Null is passed through unchanged.
     *
     * @param ?mixed $value
     * @return ?mixed
     */
    protected function apply($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        return !is_numeric($value) && !empty($value)
            ? true
            : intval($value) > 0;
    }

    /**
     * Cast boolean to int
     *
     * Converts true'y to 1.
     * Converts false'y to 0.
     * Converts empty string to null.
     * Null is passed through unchanged.
     *
     * @param ?mixed $value
     * @return ?mixed
     */
    protected function revert($value)
    {
        $result = $this->apply($value);
        return is_bool($result) ? (int) $result : null;
    }
}
