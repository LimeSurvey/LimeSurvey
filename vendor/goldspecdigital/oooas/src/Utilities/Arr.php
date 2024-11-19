<?php

declare(strict_types=1);

namespace GoldSpecDigital\ObjectOrientedOAS\Utilities;

use GoldSpecDigital\ObjectOrientedOAS\Objects\BaseObject;

/**
 * @internal
 */
class Arr
{
    /**
     * @param array $array
     * @return array
     */
    public static function filter(array $array): array
    {
        foreach ($array as $index => &$value) {
            // If the value is an object, then parse to array.
            if ($value instanceof BaseObject) {
                $value = $value->toArray();
            }

            // If the value is a filled array then recursively filter it.
            if (is_array($value)) {
                $value = static::filter($value);
                continue;
            }

            // If the value is a specification extension, then skip the null
            // check below.
            if (is_string($index) && mb_strpos($index, 'x-') === 0) {
                continue;
            }

            // If the value is null then remove it.
            if ($value === null) {
                unset($array[$index]);
                continue;
            }
        }

        return $array;
    }
}
