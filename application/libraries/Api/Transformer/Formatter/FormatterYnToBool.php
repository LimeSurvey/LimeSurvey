<?php

namespace LimeSurvey\Api\Transformer\Formatter;

class FormatterYnToBool implements FormatterRevertibleInterface
{
    /** @var bool */
    private $revert = false;
    /** @var bool */
    private $lowercaseCase = false;

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
     * Cast y/n to boolean
     *
     * Converts 'Y' or 'y' to boolean true.
     * Converts 'N' or 'n' to boolean false.
     * Any other value will produce null.
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
     * Cast y/n to boolean
     *
     * Converts 'Y' or 'y' to boolean true.
     * Converts 'N' or 'n' to boolean false.
     * Any other value will produce null.
     *
     * @param ?string $value
     * @return ?boolean
     */
    private function apply($value)
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
    public function revert($value)
    {
        if (!is_bool($value)) {
            return null;
        }

        $string = ($value ? 'y' : 'n');

        return $this->lowercaseCase
            ? $string
            : strtoupper($string);
    }
}
