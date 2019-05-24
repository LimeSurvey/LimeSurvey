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
     * Survey info from getSurveyInfo.
     *
     * @var array|null
     */
    protected static $surveyinfo = null;

    /**
     * Set survey info used by this request.
     *
     * @param array|null $surveyinfo
     * @return void
     * @throws InvalidArgumentException if $surveyinfo is null.
     */
    public static function init(array $surveyinfo = null)
    {
        if (empty($surveyinfo)) {
            throw new \InvalidArgumentException('$surveyinfo is empty, cannot initialise helper');
        }

        if (empty($surveyinfo['sid'])) {
            throw new \InvalidArgumentException('required key $surveyinfo[sid] is empty, cannot initialise helper');
        }

        self::$surveyinfo = $surveyinfo;
    }

    /**
     * Set $surveyinfo to null. Used by tests.
     *
     * @return void
     */
    public static function clearInit()
    {
        self::$surveyinfo = null;
    }

    /**
     * Flush cache for initialised survey.
     * Should be done at all places where the cache is invalidated, e.g. at save survey/question/etc.
     *
     * @param int|null $sid Set to a value if you don't want to run init() first. Useful when flushing in models.
     * @return void
     * @throws EmCacheException if surveyinfo is not initialised.
     */
    public static function flush($sid = null)
    {
        if ($sid) {
            \Yii::app()->emcache->set($sid, []);
            return;
        }

        if (empty(self::$surveyinfo)) {
            throw new EmCacheException('self::$surveyinfo is null, helper not initialised');
        }

        // Set survey cache array to empty.
        \Yii::app()->emcache->set(self::$surveyinfo['sid'], []);
    }

    /**
     * Flush ALL emcache, for all surveys.
     *
     * @return void
     */
    public static function flushAll()
    {
        \Yii::app()->emcache->flush();
    }

    /**
     * Get cache value with $key for initialised survey.
     *
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        if (!self::useCache()) {
            return false;
        }

        $surveyCache = self::getSurveyCache();

        if (empty($surveyCache)) {
            return false;
        }

        if (empty($surveyCache[$key])) {
            return false;
        }

        return $surveyCache[$key];
    }

    /**
     * Set cache $value for $key for initialised survey.
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

        /** @var array */
        $surveyCache = self::getSurveyCache();
        $surveyCache[$key] = $value;

        // TODO: Reset ALL values for survey cache everytime we set? Slow?
        \Yii::app()->emcache->set(self::$surveyinfo['sid'], $surveyCache);
    }

    /**
     * Get cache for initialised survey.
     *
     * @return array|null
     * @throws EmCacheException if surveyinfo is not initialised.
     */
    protected static function getSurveyCache()
    {
        if (empty(self::$surveyinfo)) {
            throw new EmCacheException('self::$surveyinfo is null, helper not initialised');
        }

        if (empty(self::$surveyinfo['sid'])) {
            throw new EmCacheException('self::$surveyinfo[sid] is null, helper not properly initialised');
        }

        return \Yii::app()->emcache->get(self::$surveyinfo['sid']);
    }

    /**
     * True if all conditions are set to use the emcache.
     *
     * @return boolean
     */
    public static function useCache()
    {
        // If forced, always use.
        if (\Yii::app()->getConfig("force_emcache")) {
            return true;
        }

        // Don't use when debugging.
        if (YII_DEBUG) {
            return false;
        }

        // TODO: Check activated, randomized.

        return true;
    }
}
