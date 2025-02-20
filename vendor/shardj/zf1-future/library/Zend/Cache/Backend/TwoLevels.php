<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Cache
 * @subpackage Zend_Cache_Backend
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @see Zend_Cache_Backend_ExtendedInterface
 */
require_once 'Zend/Cache/Backend/ExtendedInterface.php';

/**
 * @see Zend_Cache_Backend
 */
require_once 'Zend/Cache/Backend.php';


/**
 * @package    Zend_Cache
 * @subpackage Zend_Cache_Backend
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Cache_Backend_TwoLevels extends Zend_Cache_Backend implements Zend_Cache_Backend_ExtendedInterface
{
    /**
     * Available options
     *
     * =====> (string) slow_backend :
     * - Slow backend name
     * - Must implement the Zend_Cache_Backend_ExtendedInterface
     * - Should provide a big storage
     *
     * =====> (string) fast_backend :
     * - Flow backend name
     * - Must implement the Zend_Cache_Backend_ExtendedInterface
     * - Must be much faster than slow_backend
     *
     * =====> (array) slow_backend_options :
     * - Slow backend options (see corresponding backend)
     *
     * =====> (array) fast_backend_options :
     * - Fast backend options (see corresponding backend)
     *
     * =====> (int) stats_update_factor :
     * - Disable / Tune the computation of the fast backend filling percentage
     * - When saving a record into cache :
     *     1               => systematic computation of the fast backend filling percentage
     *     x (integer) > 1 => computation of the fast backend filling percentage randomly 1 times on x cache write
     *
     * =====> (boolean) slow_backend_custom_naming :
     * =====> (boolean) fast_backend_custom_naming :
     * =====> (boolean) slow_backend_autoload :
     * =====> (boolean) fast_backend_autoload :
     * - See Zend_Cache::factory() method
     *
     * =====> (boolean) auto_fill_fast_cache
     * - If true, automatically fill the fast cache when a cache record was not found in fast cache, but did
     *   exist in slow cache. This can be usefull when a non-persistent cache like APC or Memcached got
     *   purged for whatever reason.
     *
     * =====> (boolean) auto_refresh_fast_cache
     * - If true, auto refresh the fast cache when a cache record is hit
     *
     * @var array available options
     */
    protected $_options = [
        'slow_backend' => 'File',
        'fast_backend' => 'Apc',
        'slow_backend_options' => [],
        'fast_backend_options' => [],
        'stats_update_factor' => 10,
        'slow_backend_custom_naming' => false,
        'fast_backend_custom_naming' => false,
        'slow_backend_autoload' => false,
        'fast_backend_autoload' => false,
        'auto_fill_fast_cache' => true,
        'auto_refresh_fast_cache' => true
    ];

    /**
     * Slow Backend
     *
     * @var Zend_Cache_Backend_ExtendedInterface
     */
    protected $_slowBackend;

    /**
     * Fast Backend
     *
     * @var Zend_Cache_Backend_ExtendedInterface
     */
    protected $_fastBackend;

    /**
     * Cache for the fast backend filling percentage
     *
     * @var int
     */
    protected $_fastBackendFillingPercentage = null;

    /**
     * Constructor
     *
     * @param  array $options Associative array of options
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if ($this->_options['slow_backend'] === null) {
            Zend_Cache::throwException('slow_backend option has to set');
        } elseif ($this->_options['slow_backend'] instanceof Zend_Cache_Backend_ExtendedInterface) {
            $this->_slowBackend = $this->_options['slow_backend'];
        } else {
            $this->_slowBackend = Zend_Cache::_makeBackend(
                $this->_options['slow_backend'],
                $this->_options['slow_backend_options'],
                $this->_options['slow_backend_custom_naming'],
                $this->_options['slow_backend_autoload']
            );
            if (!in_array('Zend_Cache_Backend_ExtendedInterface', class_implements($this->_slowBackend))) {
                Zend_Cache::throwException('slow_backend must implement the Zend_Cache_Backend_ExtendedInterface interface');
            }
        }

        if ($this->_options['fast_backend'] === null) {
            Zend_Cache::throwException('fast_backend option has to set');
        } elseif ($this->_options['fast_backend'] instanceof Zend_Cache_Backend_ExtendedInterface) {
            $this->_fastBackend = $this->_options['fast_backend'];
        } else {
            $this->_fastBackend = Zend_Cache::_makeBackend(
                $this->_options['fast_backend'],
                $this->_options['fast_backend_options'],
                $this->_options['fast_backend_custom_naming'],
                $this->_options['fast_backend_autoload']
            );
            if (!in_array('Zend_Cache_Backend_ExtendedInterface', class_implements($this->_fastBackend))) {
                Zend_Cache::throwException('fast_backend must implement the Zend_Cache_Backend_ExtendedInterface interface');
            }
        }

        $this->_slowBackend->setDirectives($this->_directives);
        $this->_fastBackend->setDirectives($this->_directives);
    }

    /**
     * Test if a cache is available or not (for the given id)
     *
     * @param  string $id cache id
     * @return mixed|false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    public function test($id)
    {
        $fastTest = $this->_fastBackend->test($id);
        if ($fastTest) {
            return $fastTest;
        } else {
            return $this->_slowBackend->test($id);
        }
    }

    /**
     * Save some string datas into a cache record
     *
     * Note : $data is always "string" (serialization is done by the
     * core not by the backend)
     *
     * @param  string $data            Datas to cache
     * @param  string $id              Cache id
     * @param  array $tags             Array of strings, the cache record will be tagged by each string entry
     * @param  int   $specificLifetime If != false, set a specific lifetime for this cache record (null => infinite lifetime)
     * @param  int   $priority         integer between 0 (very low priority) and 10 (maximum priority) used by some particular backends
     * @return boolean true if no problem
     */
    public function save($data, $id, $tags = [], $specificLifetime = false, $priority = 8)
    {
        $this->_log('Calling two level save', Zend_Log::DEBUG);
        $usage = $this->_getFastFillingPercentage('saving');
        $this->_log('Fast filling usage percentage on save: (' . gettype($usage) . ') ' . var_export($usage, true), Zend_Log::DEBUG);
        $boolFast = true;
        $lifetime = $this->getLifetime($specificLifetime);
        $preparedData = $this->_prepareData($data, $lifetime, $priority);
        if (($priority > 0) && (10 * $priority >= $usage)) {
            $this->_log('Saving in fast and slow cache', Zend_Log::DEBUG);
            $fastLifetime = $this->_getFastLifetime($lifetime, $priority);
            $boolFast = $this->_fastBackend->save($preparedData, $id, [], $fastLifetime);
            $this->_log($boolFast ? 'Saving in fast cache is OK' : 'Saving in fast cache is not OK', Zend_Log::DEBUG);
            $boolSlow = $this->_slowBackend->save($preparedData, $id, $tags, $lifetime);
            $this->_log($boolSlow ? 'Saving in slow cache is OK' : 'Saving in slow cache is not OK', Zend_Log::DEBUG);
        } else {
            $this->_log('Saving in slow cache only', Zend_Log::DEBUG);
            $boolSlow = $this->_slowBackend->save($preparedData, $id, $tags, $lifetime);
            $this->_log($boolSlow ? 'Saving in slow cache is OK' : 'Saving in slow cache is not OK', Zend_Log::DEBUG);
            if ($boolSlow === true) {
                $this->_log('Purging from fast cache on save', Zend_Log::DEBUG);
                $boolFast = $this->_fastBackend->remove($id);
                if (!$boolFast && !$this->_fastBackend->test($id)) {
                    // some backends return false on remove() even if the key never existed. (and it won't if fast is full)
                    // all we care about is that the key doesn't exist now
                    $boolFast = true;
                }
                $this->_log($boolFast ? 'Successfully purged from fast cache on save' : 'Failed to purge from fast cache on save', Zend_Log::DEBUG);
            }
        }

        return ($boolFast && $boolSlow);
    }

    /**
     * Test if a cache is available for the given id and (if yes) return it (false else)
     *
     * Note : return value is always "string" (unserialization is done by the core not by the backend)
     *
     * @param  string  $id                     Cache id
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @return string|false cached datas
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        $this->_log('Calling two level load', Zend_Log::DEBUG);
        $resultFast = $this->_fastBackend->load($id, $doNotTestCacheValidity);
        $array = null;
        $isFastResult = is_string($resultFast);
        if ($isFastResult) {
            $array = unserialize($resultFast, ['allowed_classes' => false]);
        }
        if (!is_array($array)) {
            if ($isFastResult) {
                // If the fast result data is not an array, it means it got corrupted somehow at rest.
                // Remove it before trying the slow one.
                $this->_fastBackend->remove($id);
            }
            $resultSlow = $this->_slowBackend->load($id, $doNotTestCacheValidity);
            if (!is_string($resultSlow)) {
                // there is no cache at all for this id
                return false;
            }
            $array = unserialize($resultSlow, ['allowed_classes' => false]);
            if (!is_array($array)) {
                // If the slow result data is not an array, it means it got corrupted somehow at rest.
                // Remove it and return false.
                $this->_slowBackend->remove($id);
                return false;
            }
            $isFastResult = false;
        }

        //In case no cache entry was found in the FastCache (or was corrupted) and auto-filling is enabled, copy data to FastCache
        if (!$isFastResult && $this->_options['auto_fill_fast_cache']) {
            $preparedData = $this->_prepareData($array['data'], $array['lifetime'], $array['priority']);
            $this->_fastBackend->save($preparedData, $id, [], $array['lifetime']);
        }
        // maybe, we have to refresh the fast cache ?
        elseif ($this->_options['auto_refresh_fast_cache']) {
            if ($array['priority'] == 10) {
                // no need to refresh the fast cache with priority = 10
                return $array['data'];
            }
            $newFastLifetime = $this->_getFastLifetime($array['lifetime'], $array['priority'], time() - $array['expire']);
            // we have the time to refresh the fast cache
            $usage = $this->_getFastFillingPercentage('loading');
            if (($array['priority'] > 0) && (10 * $array['priority'] >= $usage)) {
                // we can refresh the fast cache
                $preparedData = $this->_prepareData($array['data'], $array['lifetime'], $array['priority']);
                $this->_fastBackend->save($preparedData, $id, [], $newFastLifetime);
            }
        }
        return $array['data'];
    }

    /**
     * Remove a cache record
     *
     * @param  string $id Cache id
     * @return boolean True if no problem
     */
    public function remove($id)
    {
        $boolFast = $this->_fastBackend->remove($id);
        $boolSlow = $this->_slowBackend->remove($id);
        return $boolFast && $boolSlow;
    }

    /**
     * Clean some cache records
     *
     * Available modes are :
     * Zend_Cache::CLEANING_MODE_ALL (default)    => remove all cache entries ($tags is not used)
     * Zend_Cache::CLEANING_MODE_OLD              => remove too old cache entries ($tags is not used)
     * Zend_Cache::CLEANING_MODE_MATCHING_TAG     => remove cache entries matching all given tags
     *                                               ($tags can be an array of strings or a single string)
     * Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG => remove cache entries not {matching one of the given tags}
     *                                               ($tags can be an array of strings or a single string)
     * Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG => remove cache entries matching any given tags
     *                                               ($tags can be an array of strings or a single string)
     *
     * @param  string $mode Clean mode
     * @param  array  $tags Array of tags
     * @throws Zend_Cache_Exception
     * @return boolean true if no problem
     */
    public function clean($mode = Zend_Cache::CLEANING_MODE_ALL, $tags = [])
    {
        switch($mode) {
            case Zend_Cache::CLEANING_MODE_ALL:
                $boolFast = $this->_fastBackend->clean(Zend_Cache::CLEANING_MODE_ALL);
                $boolSlow = $this->_slowBackend->clean(Zend_Cache::CLEANING_MODE_ALL);
                return $boolFast && $boolSlow;
                break;
            case Zend_Cache::CLEANING_MODE_OLD:
                return $this->_slowBackend->clean(Zend_Cache::CLEANING_MODE_OLD);
            case Zend_Cache::CLEANING_MODE_MATCHING_TAG:
                $ids = $this->_slowBackend->getIdsMatchingTags($tags);
                $res = true;
                foreach ($ids as $id) {
                    $bool = $this->remove($id);
                    $res = $res && $bool;
                }
                return $res;
                break;
            case Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG:
                $ids = $this->_slowBackend->getIdsNotMatchingTags($tags);
                $res = true;
                foreach ($ids as $id) {
                    $bool = $this->remove($id);
                    $res = $res && $bool;
                }
                return $res;
                break;
            case Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG:
                $ids = $this->_slowBackend->getIdsMatchingAnyTags($tags);
                $res = true;
                foreach ($ids as $id) {
                    $bool = $this->remove($id);
                    $res = $res && $bool;
                }
                return $res;
                break;
            default:
                Zend_Cache::throwException('Invalid mode for clean() method');
                break;
        }
    }

    /**
     * Return an array of stored cache ids
     *
     * @return array array of stored cache ids (string)
     */
    public function getIds()
    {
        return $this->_slowBackend->getIds();
    }

    /**
     * Return an array of stored tags
     *
     * @return array array of stored tags (string)
     */
    public function getTags()
    {
        return $this->_slowBackend->getTags();
    }

    /**
     * Return an array of stored cache ids which match given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param array $tags array of tags
     * @return array array of matching cache ids (string)
     */
    public function getIdsMatchingTags($tags = [])
    {
        return $this->_slowBackend->getIdsMatchingTags($tags);
    }

    /**
     * Return an array of stored cache ids which don't match given tags
     *
     * In case of multiple tags, a logical OR is made between tags
     *
     * @param array $tags array of tags
     * @return array array of not matching cache ids (string)
     */
    public function getIdsNotMatchingTags($tags = [])
    {
        return $this->_slowBackend->getIdsNotMatchingTags($tags);
    }

    /**
     * Return an array of stored cache ids which match any given tags
     *
     * In case of multiple tags, a logical AND is made between tags
     *
     * @param array $tags array of tags
     * @return array array of any matching cache ids (string)
     */
    public function getIdsMatchingAnyTags($tags = [])
    {
        return $this->_slowBackend->getIdsMatchingAnyTags($tags);
    }

    /**
     * Return the filling percentage of the backend storage
     *
     * @return int integer between 0 and 100
     */
    public function getFillingPercentage()
    {
        return $this->_slowBackend->getFillingPercentage();
    }

    /**
     * Return an array of metadatas for the given cache id
     *
     * The array must include these keys :
     * - expire : the expire timestamp
     * - tags : a string array of tags
     * - mtime : timestamp of last modification time
     *
     * @param string $id cache id
     * @return array array of metadatas (false if the cache id is not found)
     */
    public function getMetadatas($id)
    {
        return $this->_slowBackend->getMetadatas($id);
    }

    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $id cache id
     * @param int $extraLifetime
     * @return boolean true if ok
     */
    public function touch($id, $extraLifetime)
    {
        return $this->_slowBackend->touch($id, $extraLifetime);
    }

    /**
     * Return an associative array of capabilities (booleans) of the backend
     *
     * The array must include these keys :
     * - automatic_cleaning (is automating cleaning necessary)
     * - tags (are tags supported)
     * - expired_read (is it possible to read expired cache records
     *                 (for doNotTestCacheValidity option for example))
     * - priority does the backend deal with priority when saving
     * - infinite_lifetime (is infinite lifetime can work with this backend)
     * - get_list (is it possible to get the list of cache ids and the complete list of tags)
     *
     * @return array associative of with capabilities
     */
    public function getCapabilities()
    {
        $slowBackendCapabilities = $this->_slowBackend->getCapabilities();
        return [
            'automatic_cleaning' => $slowBackendCapabilities['automatic_cleaning'],
            'tags' => $slowBackendCapabilities['tags'],
            'expired_read' => $slowBackendCapabilities['expired_read'],
            'priority' => $slowBackendCapabilities['priority'],
            'infinite_lifetime' => $slowBackendCapabilities['infinite_lifetime'],
            'get_list' => $slowBackendCapabilities['get_list']
        ];
    }

    /**
     * Prepare a serialized array to store data and metadata information
     *
     * @param string $data data to store
     * @param int $lifetime original lifetime
     * @param int $priority priority
     * @return string serialize array to store into cache
     */
    private function _prepareData($data, $lifetime, $priority)
    {
        $lt = $lifetime;
        if ($lt === null) {
            $lt = 9999999999;
        }
        return serialize([
            'data' => $data,
            'lifetime' => $lifetime,
            'expire' => time() + $lt,
            'priority' => $priority
        ]);
    }

    /**
     * Compute and return the lifetime for the fast backend
     *
     * @param int $lifetime original lifetime
     * @param int $priority priority
     * @param int $maxLifetime maximum lifetime
     * @return int lifetime for the fast backend
     */
    private function _getFastLifetime($lifetime, $priority, $maxLifetime = null)
    {
        if ($lifetime <= 0) {
            // if no lifetime, we have an infinite lifetime
            // we need to use arbitrary lifetimes
            $fastLifetime = (int) (2592000 / (11 - $priority));
        } else {
            // prevent computed infinite lifetime (0) by ceil
            $fastLifetime = (int) ceil($lifetime / (11 - $priority));
        }

        if ($maxLifetime >= 0 && $fastLifetime > $maxLifetime) {
            return $maxLifetime;
        }

        return $fastLifetime;
    }

    /**
     * PUBLIC METHOD FOR UNIT TESTING ONLY !
     *
     * Force a cache record to expire
     *
     * @param string $id cache id
     */
    public function ___expire($id)
    {
        $this->_fastBackend->remove($id);
        $this->_slowBackend->___expire($id);
    }

    private function _getFastFillingPercentage($mode)
    {

        if ($mode === 'saving') {
            // mode saving
            if ($this->_fastBackendFillingPercentage === null) {
                $this->_fastBackendFillingPercentage = $this->_fastBackend->getFillingPercentage();
            } else {
                $rand = rand(1, $this->_options['stats_update_factor']);

                if ($rand === 1) {
                    // we force a refresh
                    $this->_fastBackendFillingPercentage = $this->_fastBackend->getFillingPercentage();
                }
            }
        } else {
            // mode loading
            // we compute the percentage only if it's not available in cache
            if ($this->_fastBackendFillingPercentage === null) {
                $this->_fastBackendFillingPercentage = $this->_fastBackend->getFillingPercentage();
            }
        }
        return $this->_fastBackendFillingPercentage;
    }

}
