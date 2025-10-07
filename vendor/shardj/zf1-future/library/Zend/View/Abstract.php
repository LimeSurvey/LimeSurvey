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
 * @package    Zend_View
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** @see Zend_Loader */
require_once 'Zend/Loader.php';

/** @see Zend_Loader_PluginLoader */
require_once 'Zend/Loader/PluginLoader.php';

/** @see Zend_View_Interface */
require_once 'Zend/View/Interface.php';

/**
 * Abstract class for Zend_View to help enforce private constructs.
 *
 * @category   Zend
 * @package    Zend_View
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_View_Abstract implements Zend_View_Interface
{
    /**
     * Path stack for script, helper, and filter directories.
     *
     * @var array
     */
    private $_path = [
        'script' => [],
        'helper' => [],
        'filter' => [],
    ];

    /**
     * Script file name to execute
     *
     * @var string
     */
    private $_file = null;

    /**
     * Instances of helper objects.
     *
     * @var array
     */
    private $_helper = [];

    /**
     * Map of helper => class pairs to help in determining helper class from
     * name
     * @var array
     */
    private $_helperLoaded = [];

    /**
     * Map of helper => classfile pairs to aid in determining helper classfile
     * @var array
     */
    private $_helperLoadedDir = [];

    /**
     * Stack of Zend_View_Filter names to apply as filters.
     * @var array
     */
    private $_filter = [];

    /**
     * Stack of Zend_View_Filter objects that have been loaded
     * @var array
     */
    private $_filterClass = [];

    /**
     * Map of filter => class pairs to help in determining filter class from
     * name
     * @var array
     */
    private $_filterLoaded = [];

    /**
     * Map of filter => classfile pairs to aid in determining filter classfile
     * @var array
     */
    private $_filterLoadedDir = [];

    /**
     * Callback for escaping.
     *
     * @var string
     */
    private $_escape = 'htmlspecialchars';

    /**
     * Encoding to use in escaping mechanisms; defaults to utf-8
     * @var string
     */
    private $_encoding = 'UTF-8';

    /**
     * Flag indicating whether or not LFI protection for rendering view scripts is enabled
     * @var bool
     */
    private $_lfiProtectionOn = true;

    /**
     * Plugin loaders
     * @var array
     */
    private $_loaders = [];

    /**
     * Plugin types
     * @var array
     */
    private $_loaderTypes = ['filter', 'helper'];

    /**
     * Strict variables flag; when on, undefined variables accessed in the view
     * scripts will trigger notices
     * @var boolean
     */
    private $_strictVars = false;

    /**
     * Data container
     *
     * @var array
     */
    private $_data = [];

    /**
     * Constructor.
     *
     * @param array $config Configuration key-value pairs.
     */
    public function __construct($config = [])
    {
        // set inital paths and properties
        $this->setScriptPath(null);

        // $this->setHelperPath(null);
        $this->setFilterPath(null);

        // user-defined escaping callback
        if (array_key_exists('escape', $config)) {
            $this->setEscape($config['escape']);
        }

        // encoding
        if (array_key_exists('encoding', $config)) {
            $this->setEncoding($config['encoding']);
        }

        // base path
        if (array_key_exists('basePath', $config)) {
            $prefix = 'Zend_View';
            if (array_key_exists('basePathPrefix', $config)) {
                $prefix = $config['basePathPrefix'];
            }
            $this->setBasePath($config['basePath'], $prefix);
        }

        // user-defined view script path
        if (array_key_exists('scriptPath', $config)) {
            $this->addScriptPath($config['scriptPath']);
        }

        // user-defined helper path
        if (array_key_exists('helperPath', $config)) {
            if (is_array($config['helperPath'])) {
                foreach ($config['helperPath'] as $prefix => $path) {
                    $this->addHelperPath($path, $prefix);
                }
            } else {
                $prefix = 'Zend_View_Helper';
                if (array_key_exists('helperPathPrefix', $config)) {
                    $prefix = $config['helperPathPrefix'];
                }
                $this->addHelperPath($config['helperPath'], $prefix);
            }
        }

        // user-defined filter path
        if (array_key_exists('filterPath', $config)) {
            if (is_array($config['filterPath'])) {
                foreach ($config['filterPath'] as $prefix => $path) {
                    $this->addFilterPath($path, $prefix);
                }
            } else {
                $prefix = 'Zend_View_Filter';
                if (array_key_exists('filterPathPrefix', $config)) {
                    $prefix = $config['filterPathPrefix'];
                }
                $this->addFilterPath($config['filterPath'], $prefix);
            }
        }

        // user-defined filters
        if (array_key_exists('filter', $config)) {
            $this->addFilter($config['filter']);
        }

        // strict vars
        if (array_key_exists('strictVars', $config)) {
            $this->strictVars($config['strictVars']);
        }

        // LFI protection flag
        if (array_key_exists('lfiProtectionOn', $config)) {
            $this->setLfiProtection($config['lfiProtectionOn']);
        }

        if (array_key_exists('assign', $config)
            && is_array($config['assign'])
        ) {
            foreach ($config['assign'] as $key => $value) {
                $this->assign($key, $value);
            }
        }

        $this->init();
    }

    /**
     * Return the template engine object
     *
     * Returns the object instance, as it is its own template engine
     *
     * @return Zend_View_Abstract
     */
    public function getEngine()
    {
        return $this;
    }

    /**
     * Allow custom object initialization when extending Zend_View_Abstract or
     * Zend_View
     *
     * Triggered by {@link __construct() the constructor} as its final action.
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Prevent E_NOTICE for nonexistent values
     *
     * If {@link strictVars()} is on, raises a notice.
     *
     * @param  string $key
     * @return null
     */
    public function &__get($key)
    {
        $value = null;
        if ('_' != substr($key, 0, 1) && isset($this->_data[$key])) {

            $value = &$this->_data[$key];
            return $value;
        }

        if ($this->_strictVars) {
            trigger_error('Key "' . $key . '" does not exist', E_USER_NOTICE);
        }

        return $value;
    }

    /**
     * Allows testing with empty() and isset() to work inside
     * templates.
     *
     * @param  string $key
     * @return boolean
     */
    public function __isset($key)
    {
        if ('_' != substr($key, 0, 1)) {
            return isset($this->_data[$key]);
        }

        return false;
    }

    /**
     * Directly assigns a variable to the view script.
     *
     * Checks first to ensure that the caller is not attempting to set a
     * protected or private member (by checking for a prefixed underscore); if
     * not, the public member is set; otherwise, an exception is raised.
     *
     * @param string $key The variable name.
     * @param mixed $val The variable value.
     * @return void
     * @throws Zend_View_Exception if an attempt to set a private or protected
     * member is detected
     */
    public function __set($key, $val)
    {
        if ('_' != substr($key, 0, 1)) {
            $this->_data[$key] = $val;
            return;
        }

        require_once 'Zend/View/Exception.php';
        $e = new Zend_View_Exception('Setting private or protected class members is not allowed');
        $e->setView($this);
        throw $e;
    }

    /**
     * Allows unset() on object properties to work
     *
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        if ('_' != substr($key, 0, 1) && isset($this->_data[$key])) {
            unset($this->_data[$key]);
        }
    }

    /**
     * Accesses a helper object from within a script.
     *
     * If the helper class has a 'view' property, sets it with the current view
     * object.
     *
     * @param string $name The helper name.
     * @param array $args The parameters for the helper.
     * @return string The result of the helper output.
     */
    public function __call($name, $args)
    {
        // is the helper already loaded?
        $helper = $this->getHelper($name);

        // call the helper method
        return call_user_func_array(
            [$helper, $name],
            $args
        );
    }

    /**
     * Given a base path, sets the script, helper, and filter paths relative to it
     *
     * Assumes a directory structure of:
     * <code>
     * basePath/
     *     scripts/
     *     helpers/
     *     filters/
     * </code>
     *
     * @param  string $path
     * @param  string $prefix Prefix to use for helper and filter paths
     * @return Zend_View_Abstract
     */
    public function setBasePath($path, $classPrefix = 'Zend_View')
    {
        $path        = rtrim($path, '/');
        $path        = rtrim($path, '\\');
        $path       .= DIRECTORY_SEPARATOR;
        $classPrefix = rtrim($classPrefix, '_') . '_';
        $this->setScriptPath($path . 'scripts');
        $this->setHelperPath($path . 'helpers', $classPrefix . 'Helper');
        $this->setFilterPath($path . 'filters', $classPrefix . 'Filter');
        return $this;
    }

    /**
     * Given a base path, add script, helper, and filter paths relative to it
     *
     * Assumes a directory structure of:
     * <code>
     * basePath/
     *     scripts/
     *     helpers/
     *     filters/
     * </code>
     *
     * @param  string $path
     * @param  string $prefix Prefix to use for helper and filter paths
     * @return Zend_View_Abstract
     */
    public function addBasePath($path, $classPrefix = 'Zend_View')
    {
        $path        = rtrim($path, '/');
        $path        = rtrim($path, '\\');
        $path       .= DIRECTORY_SEPARATOR;
        $classPrefix = rtrim($classPrefix, '_') . '_';
        $this->addScriptPath($path . 'scripts');
        $this->addHelperPath($path . 'helpers', $classPrefix . 'Helper');
        $this->addFilterPath($path . 'filters', $classPrefix . 'Filter');
        return $this;
    }

    /**
     * Adds to the stack of view script paths in LIFO order.
     *
     * @param string|array $path The directory (-ies) to add.
     * @return Zend_View_Abstract
     */
    public function addScriptPath($path)
    {
        $this->_addPath('script', $path);
        return $this;
    }

    /**
     * Resets the stack of view script paths.
     *
     * To clear all paths, use Zend_View::setScriptPath(null).
     *
     * @param string|array $path The directory (-ies) to set as the path.
     * @return Zend_View_Abstract
     */
    public function setScriptPath($path)
    {
        $this->_path['script'] = [];
        $this->_addPath('script', $path);
        return $this;
    }

    /**
     * Return full path to a view script specified by $name
     *
     * @param  string $name
     * @return false|string False if script not found
     * @throws Zend_View_Exception if no script directory set
     */
    public function getScriptPath($name)
    {
        try {
            return $this->_script($name);
        } catch (Zend_View_Exception $e) {
            if (strstr($e->getMessage(), 'no view script directory set')) {
                throw $e;
            }

            return false;
        }
    }

    /**
     * Returns an array of all currently set script paths
     *
     * @return array
     */
    public function getScriptPaths()
    {
        return $this->_getPaths('script');
    }

    /**
     * Set plugin loader for a particular plugin type
     *
     * @param  Zend_Loader_PluginLoader $loader
     * @param  string $type
     * @return Zend_View_Abstract
     */
    public function setPluginLoader(Zend_Loader_PluginLoader $loader, $type)
    {
        $type = strtolower($type);
        if (!in_array($type, $this->_loaderTypes)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception(sprintf('Invalid plugin loader type "%s"', $type));
            $e->setView($this);
            throw $e;
        }

        $this->_loaders[$type] = $loader;
        return $this;
    }

    /**
     * Retrieve plugin loader for a specific plugin type
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader
     */
    public function getPluginLoader($type)
    {
        $type = strtolower($type);
        if (!in_array($type, $this->_loaderTypes)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception(sprintf('Invalid plugin loader type "%s"; cannot retrieve', $type));
            $e->setView($this);
            throw $e;
        }

        if (!array_key_exists($type, $this->_loaders)) {
            $prefix     = 'Zend_View_';
            $pathPrefix = 'Zend/View/';

            $pType = ucfirst($type);
            switch ($type) {
                case 'filter':
                case 'helper':
                default:
                    $prefix     .= $pType;
                    $pathPrefix .= $pType;
                    $loader = new Zend_Loader_PluginLoader([
                        $prefix => $pathPrefix
                    ]);
                    $this->_loaders[$type] = $loader;
                    break;
            }
        }
        return $this->_loaders[$type];
    }

    /**
     * Adds to the stack of helper paths in LIFO order.
     *
     * @param string|array $path The directory (-ies) to add.
     * @param string $classPrefix Class prefix to use with classes in this
     * directory; defaults to Zend_View_Helper
     * @return Zend_View_Abstract
     */
    public function addHelperPath($path, $classPrefix = 'Zend_View_Helper_')
    {
        return $this->_addPluginPath('helper', $classPrefix, (array) $path);
    }

    /**
     * Resets the stack of helper paths.
     *
     * To clear all paths, use Zend_View::setHelperPath(null).
     *
     * @param string|array $path The directory (-ies) to set as the path.
     * @param string $classPrefix The class prefix to apply to all elements in
     * $path; defaults to Zend_View_Helper
     * @return Zend_View_Abstract
     */
    public function setHelperPath($path, $classPrefix = 'Zend_View_Helper_')
    {
        unset($this->_loaders['helper']);
        return $this->addHelperPath($path, $classPrefix);
    }

    /**
     * Get full path to a helper class file specified by $name
     *
     * @param  string $name
     * @return string|false False on failure, path on success
     */
    public function getHelperPath($name)
    {
        return $this->_getPluginPath('helper', $name);
    }

    /**
     * Returns an array of all currently set helper paths
     *
     * @return array
     */
    public function getHelperPaths()
    {
        return $this->getPluginLoader('helper')->getPaths();
    }

    /**
     * Registers a helper object, bypassing plugin loader
     *
     * @param  Zend_View_Helper_Abstract|object $helper
     * @param  string $name
     * @return Zend_View_Abstract
     * @throws Zend_View_Exception
     */
    public function registerHelper($helper, $name)
    {
        if (!is_object($helper)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('View helper must be an object');
            $e->setView($this);
            throw $e;
        }

        if (!$helper instanceof Zend_View_Interface) {
            if (!method_exists($helper, $name)) {
                require_once 'Zend/View/Exception.php';
                $e =  new Zend_View_Exception(
                    'View helper must implement Zend_View_Interface or have a method matching the name provided'
                );
                $e->setView($this);
                throw $e;
            }
        }

        if (method_exists($helper, 'setView')) {
            $helper->setView($this);
        }

        $name = ucfirst($name);
        $this->_helper[$name] = $helper;
        return $this;
    }

    /**
     * Get a helper by name
     *
     * @param  string $name
     * @return object
     */
    public function getHelper($name)
    {
        return $this->_getPlugin('helper', $name);
    }

    /**
     * Get array of all active helpers
     *
     * Only returns those that have already been instantiated.
     *
     * @return array
     */
    public function getHelpers()
    {
        return $this->_helper;
    }

    /**
     * Adds to the stack of filter paths in LIFO order.
     *
     * @param string|array $path The directory (-ies) to add.
     * @param string $classPrefix Class prefix to use with classes in this
     * directory; defaults to Zend_View_Filter
     * @return Zend_View_Abstract
     */
    public function addFilterPath($path, $classPrefix = 'Zend_View_Filter_')
    {
        return $this->_addPluginPath('filter', $classPrefix, (array) $path);
    }

    /**
     * Resets the stack of filter paths.
     *
     * To clear all paths, use Zend_View::setFilterPath(null).
     *
     * @param string|array $path The directory (-ies) to set as the path.
     * @param string $classPrefix The class prefix to apply to all elements in
     * $path; defaults to Zend_View_Filter
     * @return Zend_View_Abstract
     */
    public function setFilterPath($path, $classPrefix = 'Zend_View_Filter_')
    {
        unset($this->_loaders['filter']);
        return $this->addFilterPath($path, $classPrefix);
    }

    /**
     * Get full path to a filter class file specified by $name
     *
     * @param  string $name
     * @return string|false False on failure, path on success
     */
    public function getFilterPath($name)
    {
        return $this->_getPluginPath('filter', $name);
    }

    /**
     * Get a filter object by name
     *
     * @param  string $name
     * @return object
     */
    public function getFilter($name)
    {
        return $this->_getPlugin('filter', $name);
    }

    /**
     * Return array of all currently active filters
     *
     * Only returns those that have already been instantiated.
     *
     * @return array
     */
    public function getFilters()
    {
        return $this->_filter;
    }

    /**
     * Returns an array of all currently set filter paths
     *
     * @return array
     */
    public function getFilterPaths()
    {
        return $this->getPluginLoader('filter')->getPaths();
    }

    /**
     * Return associative array of path types => paths
     *
     * @return array
     */
    public function getAllPaths()
    {
        $paths = $this->_path;
        $paths['helper'] = $this->getHelperPaths();
        $paths['filter'] = $this->getFilterPaths();
        return $paths;
    }

    /**
     * Add one or more filters to the stack in FIFO order.
     *
     * @param string|array $name One or more filters to add.
     * @return Zend_View_Abstract
     */
    public function addFilter($name)
    {
        foreach ((array) $name as $val) {
            $this->_filter[] = $val;
        }
        return $this;
    }

    /**
     * Resets the filter stack.
     *
     * To clear all filters, use Zend_View::setFilter(null).
     *
     * @param string|array $name One or more filters to set.
     * @return Zend_View_Abstract
     */
    public function setFilter($name)
    {
        $this->_filter = [];
        $this->addFilter($name);
        return $this;
    }

    /**
     * Sets the _escape() callback.
     *
     * @param mixed $spec The callback for _escape() to use.
     * @return Zend_View_Abstract
     */
    public function setEscape($spec)
    {
        $this->_escape = $spec;
        return $this;
    }

    /**
     * Set LFI protection flag
     *
     * @param  bool $flag
     * @return Zend_View_Abstract
     */
    public function setLfiProtection($flag)
    {
        $this->_lfiProtectionOn = (bool) $flag;
        return $this;
    }

    /**
     * Return status of LFI protection flag
     *
     * @return bool
     */
    public function isLfiProtectionOn()
    {
        return $this->_lfiProtectionOn;
    }

    /**
     * Assigns variables to the view script via differing strategies.
     *
     * Zend_View::assign('name', $value) assigns a variable called 'name'
     * with the corresponding $value.
     *
     * Zend_View::assign($array) assigns the array keys as variable
     * names (with the corresponding array values).
     *
     * @see    __set()
     * @param  string|array $spec The assignment strategy to use.
     * @param  mixed $value (Optional) If assigning a named variable, use this
     * as the value.
     * @return $this
     * @throws Zend_View_Exception if $spec is neither a string nor an array,
     * or if an attempt to set a private or protected member is detected
     */
    public function assign($spec, $value = null)
    {
        // which strategy to use?
        if (is_string($spec)) {
            // assign by name and value
            if ('_' == substr($spec, 0, 1)) {
                require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception('Setting private or protected class members is not allowed');
                $e->setView($this);
                throw $e;
            }
            $this->$spec = $value;
        } elseif (is_array($spec)) {
            // assign from associative array
            $error = false;
            foreach ($spec as $key => $val) {
                if ('_' == substr($key, 0, 1)) {
                    $error = true;
                    break;
                }
                $this->$key = $val;
            }
            if ($error) {
                require_once 'Zend/View/Exception.php';
                $e = new Zend_View_Exception('Setting private or protected class members is not allowed');
                $e->setView($this);
                throw $e;
            }
        } else {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('assign() expects a string or array, received ' . gettype($spec));
            $e->setView($this);
            throw $e;
        }

        return $this;
    }

    /**
     * Return list of all assigned variables
     *
     * @return array
     */
    public function getVars()
    {
        return $this->_data;
    }

    /**
     * Clear all assigned variables
     *
     * Clears all variables assigned to Zend_View either via {@link assign()} or
     * property overloading ({@link __set()}).
     *
     * @return void
     */
    public function clearVars()
    {
        $this->_data = [];
    }

    /**
     * Processes a view script and returns the output.
     *
     * @param string $name The script name to process.
     * @return string The script output.
     */
    public function render($name)
    {
        // find the script file name using the parent private method
        $this->_file = $this->_script($name);
        unset($name); // remove $name from local scope

        ob_start();
        $this->_run($this->_file);

        return $this->_filter(ob_get_clean()); // filter output
    }

    /**
     * Escapes a value for output in a view script.
     *
     * If escaping mechanism is one of htmlspecialchars or htmlentities, uses
     * {@link $_encoding} setting.
     *
     * @param mixed $var The output to escape.
     * @return mixed The escaped value.
     */
    public function escape($var)
    {
        if (in_array($this->_escape, ['htmlspecialchars', 'htmlentities'])) {
            return call_user_func($this->_escape, (string) $var, ENT_COMPAT, $this->_encoding);
        }

        if (1 == func_num_args()) {
            return call_user_func($this->_escape, $var);
        }
        $args = func_get_args();
        return call_user_func_array($this->_escape, $args);
    }

    /**
     * Set encoding to use with htmlentities() and htmlspecialchars()
     *
     * @param string $encoding
     * @return Zend_View_Abstract
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        return $this;
    }

    /**
     * Return current escape encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Enable or disable strict vars
     *
     * If strict variables are enabled, {@link __get()} will raise a notice
     * when a variable is not defined.
     *
     * Use in conjunction with {@link Zend_View_Helper_DeclareVars the declareVars() helper}
     * to enforce strict variable handling in your view scripts.
     *
     * @param  boolean $flag
     * @return Zend_View_Abstract
     */
    public function strictVars($flag = true)
    {
        $this->_strictVars = ($flag) ? true : false;

        return $this;
    }

    /**
     * Finds a view script from the available directories.
     *
     * @param string $name The base name of the script.
     * @return string
     */
    protected function _script($name)
    {
        if ($this->isLfiProtectionOn() && preg_match('#\.\.[\\\/]#', $name)) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('Requested scripts may not include parent directory traversal ("../", "..\\" notation)');
            $e->setView($this);
            throw $e;
        }

        if (0 == count($this->_path['script'])) {
            require_once 'Zend/View/Exception.php';
            $e = new Zend_View_Exception('no view script directory set; unable to determine location for view script');
            $e->setView($this);
            throw $e;
        }

        foreach ($this->_path['script'] as $dir) {
            if (is_readable($dir . $name)) {
                return $dir . $name;
            }
        }

        require_once 'Zend/View/Exception.php';
        $message = "script '$name' not found in path ("
                 . implode(PATH_SEPARATOR, $this->_path['script'])
                 . ")";
        $e = new Zend_View_Exception($message);
        $e->setView($this);
        throw $e;
    }

    /**
     * Applies the filter callback to a buffer.
     *
     * @param string $buffer The buffer contents.
     * @return string The filtered buffer.
     */
    private function _filter($buffer)
    {
        // loop through each filter class
        foreach ($this->_filter as $name) {
            // load and apply the filter class
            $filter = $this->getFilter($name);
            $buffer = call_user_func([$filter, 'filter'], $buffer);
        }

        // done!
        return $buffer;
    }

    /**
     * Adds paths to the path stack in LIFO order.
     *
     * Zend_View::_addPath($type, 'dirname') adds one directory
     * to the path stack.
     *
     * Zend_View::_addPath($type, $array) adds one directory for
     * each array element value.
     *
     * In the case of filter and helper paths, $prefix should be used to
     * specify what class prefix to use with the given path.
     *
     * @param string $type The path type ('script', 'helper', or 'filter').
     * @param string|array $path The path specification.
     * @param string $prefix Class prefix to use with path (helpers and filters
     * only)
     * @return void
     */
    private function _addPath($type, $path, $prefix = null)
    {
        foreach ((array) $path as $dir) {
            // attempt to strip any possible separator and
            // append the system directory separator
            $dir  = rtrim($dir, '/');
            $dir  = rtrim($dir, '\\');
            $dir .= '/';

            switch ($type) {
                case 'script':
                    // add to the top of the stack.
                    array_unshift($this->_path[$type], $dir);
                    break;
                case 'filter':
                case 'helper':
                default:
                    // add as array with prefix and dir keys
                    array_unshift($this->_path[$type], ['prefix' => $prefix, 'dir' => $dir]);
                    break;
            }
        }
    }

    /**
     * Resets the path stack for helpers and filters.
     *
     * @param string $type The path type ('helper' or 'filter').
     * @param string|array $path The directory (-ies) to set as the path.
     * @param string $classPrefix Class prefix to apply to elements of $path
     */
    private function _setPath($type, $path, $classPrefix = null)
    {
        $dir = DIRECTORY_SEPARATOR . ucfirst($type) . DIRECTORY_SEPARATOR;

        switch ($type) {
            case 'script':
                $this->_path[$type] = [dirname(__FILE__) . $dir];
                $this->_addPath($type, $path);
                break;
            case 'filter':
            case 'helper':
            default:
                $this->_path[$type] = [[
                    'prefix' => 'Zend_View_' . ucfirst($type) . '_',
                    'dir'    => dirname(__FILE__) . $dir
                ]];
                $this->_addPath($type, $path, $classPrefix);
                break;
        }
    }

    /**
     * Return all paths for a given path type
     *
     * @param string $type The path type  ('helper', 'filter', 'script')
     * @return array
     */
    private function _getPaths($type)
    {
        return $this->_path[$type];
    }

    /**
     * Register helper class as loaded
     *
     * @param  string $name
     * @param  string $class
     * @param  string $file path to class file
     * @return void
     */
    private function _setHelperClass($name, $class, $file)
    {
        $this->_helperLoadedDir[$name] = $file;
        $this->_helperLoaded[$name]    = $class;
    }

    /**
     * Register filter class as loaded
     *
     * @param  string $name
     * @param  string $class
     * @param  string $file path to class file
     * @return void
     */
    private function _setFilterClass($name, $class, $file)
    {
        $this->_filterLoadedDir[$name] = $file;
        $this->_filterLoaded[$name]    = $class;
    }

    /**
     * Add a prefixPath for a plugin type
     *
     * @param  string $type
     * @param  string $classPrefix
     * @param  array $paths
     * @return Zend_View_Abstract
     */
    private function _addPluginPath($type, $classPrefix, array $paths)
    {
        $loader = $this->getPluginLoader($type);
        foreach ($paths as $path) {
            $loader->addPrefixPath($classPrefix, $path);
        }
        return $this;
    }

    /**
     * Get a path to a given plugin class of a given type
     *
     * @param  string $type
     * @param  string $name
     * @return string|false
     */
    private function _getPluginPath($type, $name)
    {
        $loader = $this->getPluginLoader($type);
        if ($loader->isLoaded($name)) {
            return $loader->getClassPath($name);
        }

        try {
            $loader->load($name);
            return $loader->getClassPath($name);
        } catch (Zend_Loader_Exception $e) {
            return false;
        }
    }

    /**
     * Retrieve a plugin object
     *
     * @param  string $type
     * @param  string $name
     * @return object
     */
    private function _getPlugin($type, $name)
    {
        $name = ucfirst($name);
        switch ($type) {
            case 'filter':
                $storeVar = '_filterClass';
                $store    = $this->_filterClass;
                break;
            case 'helper':
                $storeVar = '_helper';
                $store    = $this->_helper;
                break;
        }

        if (!isset($store[$name])) {
            $class = $this->getPluginLoader($type)->load($name);
            $store[$name] = new $class();
            if (method_exists($store[$name], 'setView')) {
                $store[$name]->setView($this);
            }
        }

        $this->$storeVar = $store;
        return $store[$name];
    }

    /**
     * Use to include the view script in a scope that only allows public
     * members.
     *
     * @return mixed
     */
    abstract protected function _run();
}
