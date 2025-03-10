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
 * @package    Zend_Cache
 * @subpackage Zend_Cache_Backend
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cache_Backend
{
    /**
     * Frontend or Core directives
     *
     * =====> (int) lifetime :
     * - Cache lifetime (in seconds)
     * - If null, the cache is valid forever
     *
     * =====> (int) logging :
     * - if set to true, a logging is activated throw Zend_Log
     *
     * @var array directives
     */
    protected $_directives = [
        'lifetime' => 3600,
        'logging'  => false,
        'logger'   => null
    ];

    /**
     * Available options
     *
     * @var array available options
     */
    protected $_options = [];

    /**
     * Constructor
     *
     * @param  array $options Associative array of options
     */
    public function __construct(array $options = [])
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    /**
     * Set the frontend directives
     *
     * @param  array $directives Assoc of directives
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function setDirectives($directives)
    {
        if (!is_array($directives)) Zend_Cache::throwException('Directives parameter must be an array');
        foreach ($directives as $name => $value) {
            if (!is_string($name)) {
                Zend_Cache::throwException("Incorrect option name : $name");
            }
            $name = strtolower($name);
            if (array_key_exists($name, $this->_directives)) {
                $this->_directives[$name] = $value;
            }

        }

        $this->_loggerSanity();
    }

    /**
     * Set an option
     *
     * @param  string $name
     * @param  mixed  $value
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            Zend_Cache::throwException("Incorrect option name : $name");
        }
        $name = strtolower($name);
        if (array_key_exists($name, $this->_options)) {
            $this->_options[$name] = $value;
        }
    }

    /**
     * Returns an option
     *
     * @param string $name Optional, the options name to return
     * @throws Zend_Cache_Exceptions
     * @return mixed
     */
    public function getOption($name)
    {
        $name = strtolower($name);

        if (array_key_exists($name, $this->_options)) {
            return $this->_options[$name];
        }

        if (array_key_exists($name, $this->_directives)) {
            return $this->_directives[$name];
        }

        Zend_Cache::throwException("Incorrect option name : {$name}");
    }

    /**
     * Get the life time
     *
     * if $specificLifetime is not false, the given specific life time is used
     * else, the global lifetime is used
     *
     * @param  int $specificLifetime
     * @return int Cache life time
     */
    public function getLifetime($specificLifetime)
    {
        if ($specificLifetime === false) {
            return $this->_directives['lifetime'];
        }
        return $specificLifetime;
    }

    /**
     * Return true if the automatic cleaning is available for the backend
     *
     * DEPRECATED : use getCapabilities() instead
     *
     * @deprecated
     * @return boolean
     */
    public function isAutomaticCleaningAvailable()
    {
        return true;
    }

    /**
     * Determine system TMP directory and detect if we have read access
     *
     * inspired from Zend_File_Transfer_Adapter_Abstract
     *
     * @return string
     * @throws Zend_Cache_Exception if unable to determine directory
     */
    public function getTmpDir()
    {
        $tmpdir = [];
        foreach ([$_ENV, $_SERVER] as $tab) {
            foreach (['TMPDIR', 'TEMP', 'TMP', 'windir', 'SystemRoot'] as $key) {
                if (isset($tab[$key]) && is_string($tab[$key])) {
                    if (($key == 'windir') || ($key == 'SystemRoot')) {
                        $dir = realpath($tab[$key] . '\\temp');
                    } else {
                        $dir = realpath($tab[$key]);
                    }
                    if ($this->_isGoodTmpDir($dir)) {
                        return $dir;
                    }
                }
            }
        }
        $upload = ini_get('upload_tmp_dir');
        if ($upload) {
            $dir = realpath($upload);
            if ($this->_isGoodTmpDir($dir)) {
                return $dir;
            }
        }
        if (function_exists('sys_get_temp_dir')) {
            $dir = sys_get_temp_dir();
            if ($this->_isGoodTmpDir($dir)) {
                return $dir;
            }
        }
        // Attemp to detect by creating a temporary file
        $tempFile = tempnam(md5(uniqid(rand(), TRUE)), '');
        if ($tempFile) {
            $dir = realpath(dirname($tempFile));
            unlink($tempFile);
            if ($this->_isGoodTmpDir($dir)) {
                return $dir;
            }
        }
        if ($this->_isGoodTmpDir('/tmp')) {
            return '/tmp';
        }
        if ($this->_isGoodTmpDir('\\temp')) {
            return '\\temp';
        }
        Zend_Cache::throwException('Could not determine temp directory, please specify a cache_dir manually');
    }

    /**
     * Verify if the given temporary directory is readable and writable
     *
     * @param string $dir temporary directory
     * @return boolean true if the directory is ok
     */
    protected function _isGoodTmpDir($dir)
    {
        if (is_readable($dir)) {
            if (is_writable($dir)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Make sure if we enable logging that the Zend_Log class
     * is available.
     * Create a default log object if none is set.
     *
     * @throws Zend_Cache_Exception
     * @return void
     */
    protected function _loggerSanity()
    {
        if (!isset($this->_directives['logging']) || !$this->_directives['logging']) {
            return;
        }

        if (isset($this->_directives['logger'])) {
            if ($this->_directives['logger'] instanceof Zend_Log) {
                return;
            }
            Zend_Cache::throwException('Logger object is not an instance of Zend_Log class.');
        }

        // Create a default logger to the standard output stream
        require_once 'Zend/Log.php';
        require_once 'Zend/Log/Writer/Stream.php';
        require_once 'Zend/Log/Filter/Priority.php';
        $logger = new Zend_Log(new Zend_Log_Writer_Stream('php://output'));
        $logger->addFilter(new Zend_Log_Filter_Priority(Zend_Log::WARN, '<='));
        $this->_directives['logger'] = $logger;
    }

    /**
     * Log a message at the WARN (4) priority.
     *
     * @param  string $message
     * @param  int    $priority
     * @return void
     */
    protected function _log($message, $priority = 4)
    {
        if (!$this->_directives['logging']) {
            return;
        }

        if (!isset($this->_directives['logger'])) {
            Zend_Cache::throwException('Logging is enabled but logger is not set.');
        }
        $logger = $this->_directives['logger'];
        if (!$logger instanceof Zend_Log) {
            Zend_Cache::throwException('Logger object is not an instance of Zend_Log class.');
        }
        $logger->log($message, $priority);
    }
}
