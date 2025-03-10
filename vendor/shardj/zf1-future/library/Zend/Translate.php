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
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Loader
 */
require_once 'Zend/Loader.php';

/**
 * @see Zend_Translate_Adapter
 */
require_once 'Zend/Translate/Adapter.php';


/**
 * @category   Zend
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Translate {
    /**
     * Adapter names constants
     */
    public const AN_ARRAY   = 'Array';
    public const AN_CSV     = 'Csv';
    public const AN_GETTEXT = 'Gettext';
    public const AN_INI     = 'Ini';
    public const AN_QT      = 'Qt';
    public const AN_TBX     = 'Tbx';
    public const AN_TMX     = 'Tmx';
    public const AN_XLIFF   = 'Xliff';
    public const AN_XMLTM   = 'XmlTm';

    public const LOCALE_DIRECTORY = 'directory';
    public const LOCALE_FILENAME  = 'filename';

    /**
     * Adapter
     *
     * @var Zend_Translate_Adapter
     */
    private $_adapter;

    /**
     * Generates the standard translation object
     *
     * @param  array|Zend_Config|Zend_Translate_Adapter $options Options to use
     * @param  string|array [$content] Path to content, or content itself
     * @param  string|Zend_Locale [$locale]
     * @throws Zend_Translate_Exception
     */
    public function __construct($options = [])
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (func_num_args() > 1) {
            $args               = func_get_args();
            $options            = [];
            $options['adapter'] = array_shift($args);
            if (!empty($args)) {
                $options['content'] = array_shift($args);
            }

            if (!empty($args)) {
                $options['locale'] = array_shift($args);
            }

            if (!empty($args)) {
                $opt     = array_shift($args);
                $options = array_merge($opt, $options);
            }
        } else if (!is_array($options)) {
            $options = ['adapter' => $options];
        }

        $this->setAdapter($options);
    }

    /**
     * Sets a new adapter
     *
     * @param  array|Zend_Config|Zend_Translate_Adapter $options Options to use
     * @param  string|array [$content] Path to content, or content itself
     * @param  string|Zend_Locale [$locale]
     * @throws Zend_Translate_Exception
     */
    public function setAdapter($options = [])
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (func_num_args() > 1) {
            $args               = func_get_args();
            $options            = [];
            $options['adapter'] = array_shift($args);
            if (!empty($args)) {
                $options['content'] = array_shift($args);
            }

            if (!empty($args)) {
                $options['locale'] = array_shift($args);
            }

            if (!empty($args)) {
                $opt     = array_shift($args);
                $options = array_merge($opt, $options);
            }
        } else if (!is_array($options)) {
            $options = ['adapter' => $options];
        }

        if (Zend_Loader::isReadable('Zend/Translate/Adapter/' . ucfirst($options['adapter']). '.php')) {
            $options['adapter'] = 'Zend_Translate_Adapter_' . ucfirst($options['adapter']);
        }

        if (!class_exists($options['adapter'])) {
            Zend_Loader::loadClass($options['adapter']);
        }

        if (array_key_exists('cache', $options)) {
            Zend_Translate_Adapter::setCache($options['cache']);
        }

        $adapter = $options['adapter'];
        unset($options['adapter']);
        $this->_adapter = new $adapter($options);
        if (!$this->_adapter instanceof Zend_Translate_Adapter) {
            require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception("Adapter " . $adapter . " does not extend Zend_Translate_Adapter");
        }
    }

    /**
     * Returns the adapters name and it's options
     *
     * @return Zend_Translate_Adapter
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * Returns the set cache
     *
     * @return Zend_Cache_Core The set cache
     */
    public static function getCache()
    {
        return Zend_Translate_Adapter::getCache();
    }

    /**
     * Sets a cache for all instances of Zend_Translate
     *
     * @param  Zend_Cache_Core $cache Cache to store to
     * @return void
     */
    public static function setCache(Zend_Cache_Core $cache)
    {
        Zend_Translate_Adapter::setCache($cache);
    }

    /**
     * Returns true when a cache is set
     *
     * @return boolean
     */
    public static function hasCache()
    {
        return Zend_Translate_Adapter::hasCache();
    }

    /**
     * Removes any set cache
     *
     * @return void
     */
    public static function removeCache()
    {
        Zend_Translate_Adapter::removeCache();
    }

    /**
     * Clears all set cache data
     *
     * @param string $tag Tag to clear when the default tag name is not used
     * @return void
     */
    public static function clearCache($tag = null)
    {
        Zend_Translate_Adapter::clearCache($tag);
    }

    /**
     * Calls all methods from the adapter
     */
    public function __call($method, array $options)
    {
        if (method_exists($this->_adapter, $method)) {
            return call_user_func_array([$this->_adapter, $method], $options);
        }
        require_once 'Zend/Translate/Exception.php';
        throw new Zend_Translate_Exception("Unknown method '" . $method . "' called!");
    }
}
