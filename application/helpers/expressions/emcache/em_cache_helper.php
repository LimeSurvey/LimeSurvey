<?php

class EmCachePlugin extends PluginBase
{
    /**
     * 
     */
    public function beforeModelSave()
    {
        $event = $this->getEvent();
        var_dump($event->get('model')->sid);
        //EmCacheHelper::flush();
    }
}

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
     * Bind this helper to a bunch of events that will clear the cache when activated.
     *
     * NB: This need to happen even if useCache() returns false, because
     * all the events are applied in the admin.
     *
     * NB: Always use beforeSave. afterSave is only invoked if the save
     * was successful.
     *
     * @return void
     */
    public static function bindEvents()
    {
        $pm = \Yii::app()->pluginManager;
        $plugin = new EmCachePlugin($pm, 'emcacheplugin');
        $pm->subscribe($plugin, 'beforeModelSave', 'beforeModelSave');
        $s = new Survey();
        $s->sid = rand(1, 100000);
        $result = $s->save();
        //var_dump($result);
        die('end');
    }

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
        // TODO: How to flush all keys with previx survey id?
        \Yii::app()->emcache->flush();

        //if ($sid) {
            //\Yii::app()->emcache->set($sid, []);
            //return;
        //}

        //if (empty(self::$surveyinfo)) {
            //throw new EmCacheException('self::$surveyinfo is null, helper not initialised');
        //}

        // Set survey cache array to empty.
        //\Yii::app()->emcache->set(self::$surveyinfo['sid'], []);
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

        //$surveyCache = self::getSurveyCache();

        //if (empty($surveyCache)) {
            //return false;
        //}

        //if (empty($surveyCache[$key])) {
            //return false;
        //}

        // Append survey id to cache key.
        $key = 'survey' . self::$surveyinfo['sid'] . $key;
        return \Yii::app()->emcache->get($key);

        //return $surveyCache[$key];
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

        // Append survey id to cache key.
        $key = 'survey' . self::$surveyinfo['sid'] . $key;

        /** @var array */
        //$surveyCache = self::getSurveyCache();
        //$surveyCache[$key] = $value;

        echo ' | setting cache key ' . $key;  // . ' with value ' . json_encode($value);

        $result = \Yii::app()->emcache->set($key, $value);

        if (!$result && YII_DEBUG) {
            throw new EmCacheException('Failed caching key ' . $key);
        }
    }

    /**
     * Get cache for initialised survey.
     *
     * @return array|null
     * @throws EmCacheException if surveyinfo is not initialised.
     */
    protected static function getSurveyCache()
    {
        //static $cache = null;
        //if ($cache) {
            //return $cache;
        //}

        if (empty(self::$surveyinfo)) {
            throw new EmCacheException('self::$surveyinfo is null, helper not initialised');
        }

        if (empty(self::$surveyinfo['sid'])) {
            throw new EmCacheException('self::$surveyinfo[sid] is null, helper not properly initialised');
        }

        $cache = \Yii::app()->emcache->get(self::$surveyinfo['sid']);
        return $cache;
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

        // TODO: Check activated, randomized.

        return true;
    }
}
