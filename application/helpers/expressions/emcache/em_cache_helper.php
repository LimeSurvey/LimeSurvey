<?php

/**
 * Discussion here: https://bugs.limesurvey.org/view.php?id=14859
 * PR: https://github.com/LimeSurvey/LimeSurvey/pull/1273
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

        if (empty($surveyinfo['active'])) {
            throw new \InvalidArgumentException('required key $surveyinfo[active] is empty, cannot initialise helper');
        }

        self::$surveyinfo = $surveyinfo;

        \Yii::app()->emcache->keyPrefix = $surveyinfo['sid'];
    }

    /**
     * Set $surveyinfo to null. Used by tests.
     *
     * @return void
     */
    public static function clearInit()
    {
        if (isset(self::$surveyinfo)) {
            self::flush();
        }
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
    public static function flush()
    {
        if (empty(self::$surveyinfo)) {
            throw new EmCacheException('Cannot flush emcache unless initalised');
        }
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

        if (empty(self::$surveyinfo)) {
            throw new EmCacheException('emcache is not initialised');
        }

        return \Yii::app()->emcache->get($key);
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

        /** @var boolean */
        $result = \Yii::app()->emcache->set($key, $value);

        if (!$result && YII_DEBUG) {
            throw new EmCacheException('Failed caching key ' . $key);
        }
    }

    /**
     * @todo Setting per survey.
     */
    public static function cacheQanda()
    {
        return \Yii::app()->getConfig('emcache_cache_qanda');
    }

    /**
     * True if all conditions are met to use the emcache.
     *
     * @return boolean
     */
    public static function useCache()
    {
        // Never in admin.
        if (get_class(\Yii::app()->getController()) !== 'SurveyController') {
            return false;
        }

        // If forced, always use (except from admin, because that crashes with tests).
        if (\Yii::app()->getConfig("force_emcache")) {
            return true;
        }

        // Don't use when debugging.
        if (YII_DEBUG) {
            return false;
        }

        // No point setting and getting for dummy cache.
        if (get_class(\Yii::app()->emcache) === 'CDummyCache') {
            return false;
        }

        // Only use emcache when survey is active.
        if (self::$surveyinfo['active'] !== 'Y') {
            return false;
        }

        // Never use emcache if the survey is randomized: createFieldMap will need to be remade for every request.
        //$surveyId = $this->surveyinfo['sid'];
        //if (isset(Yii::app()->session['survey_'.$surveyId]['fieldmap-'.$surveyId.'-randMaster'])) {
            //return false;
        //}

        return true;
    }
}
