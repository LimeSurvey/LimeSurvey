<?php

/**
 * Discussion here: https://bugs.limesurvey.org/view.php?id=14859
 *
 * @since 2019-05-23
 * @author Olle Haerstedt
 */
class EmCacheHelper
{
    /**
     * Flush cache. Should be done at all places where the cache is invalidated, e.g. at save survey/question/etc.
     *
     * @return void
     */
    public static function flush()
    {
        
    }

    /**
     * Get cache value with $key.
     *
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        if (!self::useCache()) {
            return false;
        }

        /** @var mixed */
        $value = \Yii::app()->emcache->get($key);
        return $value;
    }

    /**
     * Set cache $value for $key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        if (!self::useCache()) {
            return;
        }
    }

    /**
     * True if all conditions are set to use the emcache.
     *
     * @return boolean
     */
    protected static function useCache()
    {
    }
}
