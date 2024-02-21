<?php

namespace LimeSurvey\Api\Transformer\Formatter;

class FormatterIntToBool implements FormatterInterface
{
    private string $name = 'intToBool';
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
    protected function apply($value)
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
    protected function revert($value)
    {
        $result = $this->apply($value);
        return is_bool($result) ? (int) $result : null;
    }

    public function normaliseConfigValue($config, $options = [])
    {
        if (isset($config['formatter'][$this->name])) {
            if (is_array($config['formatter'][$this->name])) {
                if (
                    array_key_exists(
                        'revert',
                        $config['formatter'][$this->name]
                    )
                ) {
                    $this->revert = $config['formatter'][$this->name]['revert'];
                }
            }
            return $this;
        }
        return $config['formatter'] ?? null;
    }
}
