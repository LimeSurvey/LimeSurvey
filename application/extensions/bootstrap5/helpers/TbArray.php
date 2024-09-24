<?php
/**
 * TbArray class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.helpers
 */

/**
 * Array helper class.
 */
class TbArray
{
    /**
     * Returns a specific value from the given array (or the default value if not set).
     * @param string $key the item key.
     * @param array $array the array to get from.
     * @param mixed $defaultValue the default value.
     * @return mixed the value.
     */
    public static function getValue($key, array $array, $defaultValue = null)
    {
        return array_key_exists($key, $array) ? $array[$key] : $defaultValue;
    }

    /**
     * Removes and returns a specific value from the given array (or the default value if not set).
     * @param string $key the item key.
     * @param array $array the array to pop the item from.
     * @param mixed $defaultValue the default value.
     * @return mixed the value.
     */
    public static function popValue($key, array &$array, $defaultValue = null)
    {
        $value = self::getValue($key, $array, $defaultValue);
        unset($array[$key]);
        return $value;
    }

    /**
     * Sets the default value for a specific key in the given array.
     * @param string $key the item key.
     * @param mixed $value the default value.
     * @param array $array the array.
     */
    public static function defaultValue($key, $value, array &$array)
    {
        if (!isset($array[$key])) {
            $array[$key] = $value;
        }
    }

    /**
     * Sets a set of default values for the given array.
     * @param array $array the array to set values for.
     * @param array $values the default values.
     */
    public static function defaultValues(array $values, array &$array)
    {
        foreach ($values as $name => $value) {
            self::defaultValue($name, $value, $array);
        }
    }

    /**
     * Removes a specific value from the given array.
     * @param string $key the item key.
     */
    public static function removeValue($key, array &$array)
    {
        unset($array[$key]);
    }

    /**
     * Removes a set of items from the given array.
     * @param array $keys the keys to remove.
     * @param array $array the array to remove from.
     */
    public static function removeValues(array $keys, array &$array)
    {
        $array = array_diff_key($array, array_flip($keys));
    }

    /**
     * Copies the given values from one array to another.
     * @param array $keys the keys to copy.
     * @param array $from the array to copy from.
     * @param array $to the array to copy to.
     * @param boolean $force whether to allow overriding of existing values.
     * @return array the options.
     */
    public static function copyValues(array $keys, array $from, array $to, $force = false)
    {
        foreach ($keys as $key) {
            if (isset($from[$key])) {
                if ($force || !isset($to[$key])) {
                    $to[$key] = self::getValue($key, $from);
                }
            }
        }
        return $to;
    }

    /**
     * Moves the given values from one array to another.
     * @param array $keys the keys to move.
     * @param array $from the array to move from.
     * @param array $to the array to move to.
     * @param boolean $force whether to allow overriding of existing values.
     * @return array the options.
     */
    public static function moveValues(array $keys, array &$from, array $to, $force = false)
    {
        foreach ($keys as $key) {
            if (isset($from[$key])) {
                $value = self::popValue($key, $from);
                if ($force || !isset($to[$key])) {
                    $to[$key] = $value;
                    unset($from[$key]);
                }
            }
        }
        return $to;
    }

    /**
     * Merges two arrays.
     * @param array $to array to be merged to.
     * @param array $from array to be merged from.
     * @return array the merged array.
     */
    public static function merge(array $to, array $from)
    {
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $k => $v) {
                if (is_integer($k)) {
                    isset($res[$k]) ? $res[] = $v : $res[$k] = $v;
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = self::merge($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }
        return $res;
    }
}