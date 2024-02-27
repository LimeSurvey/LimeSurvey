<?php

namespace LimeSurvey\Api\Transformer\Formatter;

class FormatterYnToBool implements FormatterInterface
{
    private string $name = 'ynToBool';
    /** @var bool */
    public $revert = false;
    /** @var bool */
    public $lowercaseCase = false;

    /**
     * @param bool $revert
     * @param bool $lowercase
     */
    public function __construct($revert = false, $lowercase = false)
    {
        $this->revert = $revert;
        $this->lowercaseCase = $lowercase;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Cast y/n to boolean
     *
     * Converts 'Y' or 'y' to boolean true.
     * Converts 'N' or 'n' to boolean false.
     * Any other value will produce null.
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
     * Cast y/n to boolean
     *
     * Converts 'Y' or 'y' to boolean true.
     * Converts 'N' or 'n' to boolean false.
     * Any other value will produce null.
     *
     * @param ?string $value
     * @return ?boolean
     */
    protected function apply($value)
    {
        $lowercase = is_string($value)
            ? strtolower($value)
            : $value;
        if (
            $value === null
            || $value === ''
            || !in_array($lowercase, ['y', 'n'])
        ) {
            return null;
        }
        return $lowercase === 'y';
    }

    /**
     * Cast boolean to y/n
     *
     * Converts boolean true 'y'.
     * Converts boolean false to 'n'.
     * Any other value will produce null.
     *
     * @param ?mixed $value
     * @return ?mixed
     */
    protected function revert($value)
    {
        if (!is_bool($value)) {
            return null;
        }

        $string = ($value ? 'y' : 'n');

        return $this->lowercaseCase
            ? $string
            : strtoupper($string);
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
                if (array_key_exists('lowercaseCase', $formatterConfig)) {
                    $this->lowercaseCase = $formatterConfig['lowercaseCase'];
                }
            }
        }
    }
}
