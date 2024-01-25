<?php

namespace LimeSurvey\Api\Transformer\Formatter;

class FormatterIntToBool implements FormatterInterface
{
    /** @var bool */
    private $revert = false;

    /**
     * @param bool $revert
     */
    public function __construct($revert = false)
    {
        $this->revert = $revert;
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
    public function format($value)
    {
        return $this->revert
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
     * @param ?string $value
     * @return ?boolean
     */
    private function apply($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        return !is_numeric($value) || intval($value) > 0;
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
    private function revert($value)
    {
        $result = $this->apply($value);
        return is_bool($result) ? (int) $result : null;
    }
}
