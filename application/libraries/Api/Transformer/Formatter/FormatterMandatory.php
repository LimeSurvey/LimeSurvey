<?php

namespace LimeSurvey\Api\Transformer\Formatter;

/**
 * This class is extending the FormatterYnToBool class
 * to be able to have 'S' as a third option.
 * It is only needed for prop of type "mandatory"
 */
class FormatterMandatory extends FormatterYnToBool
{
    /**
     * Cast y/n to boolean while keeping 'S' as a third option
     *
     * Converts 'Y' or 'y' to boolean true.
     * Converts 'N' or 'n' to boolean false.
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
            || !in_array($lowercase, ['y', 'n', 's'])
        ) {
            return null;
        }
        return $lowercase === 's' ? 'S' : $lowercase === 'y';
    }

    /**
     * if value is 'S' it will be returned,
     * otherwise the parent revert function will be called
     *
     * @param ?mixed $value
     * @param array $config
     * @return mixed|string|null
     */
    protected function revert($value, array $config = [])
    {
        if ($value !== 'S') {
            $value = parent::revert($value, $config);
        }

        return $value;
    }
}
