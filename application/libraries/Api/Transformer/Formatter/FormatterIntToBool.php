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
     * @param array $config
     * @param array $options
     * @return ?mixed
     */
    public function format($value, $config, $options = [])
    {
        $this->setClassBasedOnConfig($config);
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

    /**
     * Checks config for this specific formatter,
     * and adjusts class properties based on the config.
     * @param array $config
     * @return void
     */
    public function setClassBasedOnConfig($config)
    {
        if (isset($config['formatter'][$this->name])) {
            $formatterConfig = $config['formatter'][$this->name];
            if (is_array($formatterConfig)) {
                if (array_key_exists('revert', $formatterConfig)) {
                    $this->revert = $formatterConfig['revert'];
                }
            }
        }
    }
}
