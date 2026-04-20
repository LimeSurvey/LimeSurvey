<?php

namespace LimeSurvey\Api\Transformer\Formatter;

class FormatterYnToBool implements FormatterInterface
{
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
    public function format($value, $config = [], $options = [])
    {
        $revert = array_key_exists(
            'revert',
            $config
        ) ? $config['revert'] : false;
        return $revert
            ? $this->revert($value, $config)
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
     * @return bool|string|null
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
     * @param array $config
     * @return ?mixed
     */
    protected function revert($value, array $config = [])
    {
        if (!is_bool($value)) {
            return null;
        }
        $lowercaseCase = array_key_exists(
            'lowercaseCase',
            $config
        ) ? $config['lowercaseCase'] : false;
        $string = ($value ? 'y' : 'n');

        return $lowercaseCase
            ? $string
            : strtoupper($string);
    }
}
