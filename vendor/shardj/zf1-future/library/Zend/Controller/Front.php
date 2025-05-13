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
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Zend_Loader */
require_once 'Zend/Loader.php';

/** Zend_Controller_Action_HelperBroker */
require_once 'Zend/Controller/Action/HelperBroker.php';

/** Zend_Controller_Plugin_Broker */
require_once 'Zend/Controller/Plugin/Broker.php';

/**
 * @category   Zend
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Front
{
    /**
     * Base URL
     * @var string
     */
    protected $_baseUrl = null;

    /**
     * Directory|ies where controllers are stored
     *
     * @var string|array
     */
    protected $_controllerDir = null;

    /**
     * Instance of Zend_Controller_Dispatcher_Interface
     * @var Zend_Controller_Dispatcher_Interface
     */
    protected $_dispatcher = null;

    /**
     * Singleton instance
     *
     * Marked only as protected to allow extension of the class. To extend,
     * simply override {@link getInstance()}.
     *
     * @var Zend_Controller_Front
     */
    protected static $_instance = null;

    /**
     * Array of invocation parameters to use when instantiating action
     * controllers
     * @var array
     */
    protected $_invokeParams = [];

    /**
     * Subdirectory within a module containing controllers; defaults to 'controllers'
     * @var string
     */
    protected $_moduleControllerDirectoryName = 'controllers';

    /**
     * Instance of Zend_Controller_Plugin_Broker
     * @var Zend_Controller_Plugin_Broker
     */
    protected $_plugins = null;

    /**
     * Instance of Zend_Controller_Request_Abstract
     * @var Zend_Controller_Request_Abstract
     */
    protected $_request = null;

    /**
     * Instance of Zend_Controller_Response_Abstract
     * @var Zend_Controller_Response_Abstract
     */
    protected $_response = null;

    /**
     * Whether or not to return the response prior to rendering output while in
     * {@link dispatch()}; default is to send headers and render output.
     * @var boolean
     */
    protected $_returnResponse = false;

    /**
     * Instance of Zend_Controller_Router_Interface
     * @var Zend_Controller_Router_Interface
     */
    protected $_router = null;

    /**
     * Whether or not exceptions encountered in {@link dispatch()} should be
     * thrown or trapped in the response object
     * @var boolean
     */
    protected $_throwExceptions = false;

    /**
     * Constructor
     *
     * Instantiate using {@link getInstance()}; front controller is a singleton
     * object.
     *
     * Instantiates the plugin broker.
     *
     * @return void
     */
    protected function __construct()
    {
        $this->_plugins = new Zend_Controller_Plugin_Broker();
    }

    /**
     * Enforce singleton; disallow cloning
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Singleton instance
     *
     * @return Zend_Controller_Front
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Resets all object properties of the singleton instance
     *
     * Primarily used for testing; could be used to chain front controllers.
     *
     * Also resets action helper broker, clearing all registered helpers.
     *
     * @return void
     */
    public function resetInstance()
    {
        $reflection = new ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();
            switch ($name) {
                case '_instance':
                    break;
                case '_controllerDir':
                case '_invokeParams':
                    $this->{$name} = [];
                    break;
                case '_plugins':
                    $this->{$name} = new Zend_Controller_Plugin_Broker();
                    break;
                case '_throwExceptions':
                case '_returnResponse':
                    $this->{$name} = false;
                    break;
                case '_moduleControllerDirectoryName':
                    $this->{$name} = 'controllers';
                    break;
                default:
                    $this->{$name} = null;
                    break;
            }
        }
        Zend_Controller_Action_HelperBroker::resetHelpers();
    }

    /**
     * Convenience feature, calls setControllerDirectory()->setRouter()->dispatch()
     *
     * In PHP 5.1.x, a call to a static method never populates $this -- so run()
     * may actually be called after setting up your front controller.
     *
     * @param string|array $controllerDirectory Path to Zend_Controller_Action
     * controller classes or array of such paths
     * @return void
     * @throws Zend_Controller_Exception if called from an object instance
     */
    public static function run($controllerDirectory)
    {
        self::getInstance()
            ->setControllerDirectory($controllerDirectory)
            ->dispatch();
    }

    /**
     * Add a controller directory to the controller directory stack
     *
     * If $args is presented and is a string, uses it for the array key mapping
     * to the directory specified.
     *
     * @param string $directory
     * @param string $module Optional argument; module with which to associate directory. If none provided, assumes 'default'
     * @return $this
     * @throws Zend_Controller_Exception if directory not found or readable
     */
    public function addControllerDirectory($directory, $module = null)
    {
        $this->getDispatcher()->addControllerDirectory($directory, $module);
        return $this;
    }

    /**
     * Set controller directory
     *
     * Stores controller directory(ies) in dispatcher. May be an array of
     * directories or a string containing a single directory.
     *
     * @param string|array $directory Path to Zend_Controller_Action controller
     * classes or array of such paths
     * @param  string $module Optional module name to use with string $directory
     * @return $this
     */
    public function setControllerDirectory($directory, $module = null)
    {
        $this->getDispatcher()->setControllerDirectory($directory, $module);
        return $this;
    }

    /**
     * Retrieve controller directory
     *
     * Retrieves:
     * - Array of all controller directories if no $name passed
     * - String path if $name passed and exists as a key in controller directory array
     * - null if $name passed but does not exist in controller directory keys
     *
     * @param  string $name Default null
     * @return array|string|null
     */
    public function getControllerDirectory($name = null)
    {
        return $this->getDispatcher()->getControllerDirectory($name);
    }

    /**
     * Remove a controller directory by module name
     *
     * @param  string $module
     * @return bool
     */
    public function removeControllerDirectory($module)
    {
        return $this->getDispatcher()->removeControllerDirectory($module);
    }

    /**
     * Specify a directory as containing modules
     *
     * Iterates through the directory, adding any subdirectories as modules;
     * the subdirectory within each module named after {@link $_moduleControllerDirectoryName}
     * will be used as the controller directory path.
     *
     * @param  string $path
     * @return $this
     */
    public function addModuleDirectory($path)
    {
        try{
            $dir = new DirectoryIterator($path);
        } catch(Throwable $e) {
            require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Exception("Directory $path not readable", 0, $e);
        }
        foreach ($dir as $file) {
            if ($file->isDot() || !$file->isDir()) {
                continue;
            }

            $module    = $file->getFilename();

            // Don't use SCCS directories as modules
            if (preg_match('/^[^a-z]/i', $module) || ('CVS' == $module)) {
                continue;
            }

            $moduleDir = $file->getPathname() . DIRECTORY_SEPARATOR . $this->getModuleControllerDirectoryName();
            $this->addControllerDirectory($moduleDir, $module);
        }

        return $this;
    }

    /**
     * Return the path to a module directory (but not the controllers directory within)
     *
     * @param  string $module
     * @return string|null
     */
    public function getModuleDirectory($module = null)
    {
        if (null === $module) {
            $request = $this->getRequest();
            if (null !== $request) {
                $module = $this->getRequest()->getModuleName();
            }
            if (empty($module)) {
                $module = $this->getDispatcher()->getDefaultModule();
            }
        }

        $controllerDir = $this->getControllerDirectory($module);

        if ((null === $controllerDir) || !is_string($controllerDir)) {
            return null;
        }

        return dirname($controllerDir);
    }

    /**
     * Set the directory name within a module containing controllers
     *
     * @param  string $name
     * @return $this
     */
    public function setModuleControllerDirectoryName($name = 'controllers')
    {
        $this->_moduleControllerDirectoryName = (string) $name;

        return $this;
    }

    /**
     * Return the directory name within a module containing controllers
     *
     * @return string
     */
    public function getModuleControllerDirectoryName()
    {
        return $this->_moduleControllerDirectoryName;
    }

    /**
     * Set the default controller (unformatted string)
     *
     * @param string $controller
     * @return $this
     */
    public function setDefaultControllerName($controller)
    {
        $dispatcher = $this->getDispatcher();
        $dispatcher->setDefaultControllerName($controller);
        return $this;
    }

    /**
     * Retrieve the default controller (unformatted string)
     *
     * @return string
     */
    public function getDefaultControllerName()
    {
        return $this->getDispatcher()->getDefaultControllerName();
    }

    /**
     * Set the default action (unformatted string)
     *
     * @param string $action
     * @return $this
     */
    public function setDefaultAction($action)
    {
        $dispatcher = $this->getDispatcher();
        $dispatcher->setDefaultAction($action);
        return $this;
    }

    /**
     * Retrieve the default action (unformatted string)
     *
     * @return string
     */
    public function getDefaultAction()
    {
        return $this->getDispatcher()->getDefaultAction();
    }

    /**
     * Set the default module name
     *
     * @param string $module
     * @return $this
     */
    public function setDefaultModule($module)
    {
        $dispatcher = $this->getDispatcher();
        $dispatcher->setDefaultModule($module);
        return $this;
    }

    /**
     * Retrieve the default module
     *
     * @return string
     */
    public function getDefaultModule()
    {
        return $this->getDispatcher()->getDefaultModule();
    }

    /**
     * Set request class/object
     *
     * Set the request object.  The request holds the request environment.
     *
     * If a class name is provided, it will instantiate it
     *
     * @param string|Zend_Controller_Request_Abstract $request
     * @throws Zend_Controller_Exception if invalid request class
     * @return $this
     */
    public function setRequest($request)
    {
        if (is_string($request)) {
            if (!class_exists($request)) {
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($request);
            }
            $request = new $request();
        }
        if (!$request instanceof Zend_Controller_Request_Abstract) {
            require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Exception('Invalid request class');
        }

        $this->_request = $request;

        return $this;
    }

    /**
     * Return the request object.
     *
     * @return null|Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Set router class/object
     *
     * Set the router object.  The router is responsible for mapping
     * the request to a controller and action.
     *
     * If a class name is provided, instantiates router with any parameters
     * registered via {@link setParam()} or {@link setParams()}.
     *
     * @param string|Zend_Controller_Router_Interface $router
     * @throws Zend_Controller_Exception if invalid router class
     * @return $this
     */
    public function setRouter($router)
    {
        if (is_string($router)) {
            if (!class_exists($router)) {
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($router);
            }
            $router = new $router();
        }

        if (!$router instanceof Zend_Controller_Router_Interface) {
            require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Exception('Invalid router class');
        }

        $router->setFrontController($this);
        $this->_router = $router;

        return $this;
    }

    /**
     * Return the router object.
     *
     * Instantiates a Zend_Controller_Router_Rewrite object if no router currently set.
     *
     * @return Zend_Controller_Router_Interface
     */
    public function getRouter()
    {
        if (null == $this->_router) {
            require_once 'Zend/Controller/Router/Rewrite.php';
            $this->setRouter(new Zend_Controller_Router_Rewrite());
        }

        return $this->_router;
    }

    /**
     * Set the base URL used for requests
     *
     * Use to set the base URL segment of the REQUEST_URI to use when
     * determining PATH_INFO, etc. Examples:
     * - /admin
     * - /myapp
     * - /subdir/index.php
     *
     * Note that the URL should not include the full URI. Do not use:
     * - http://example.com/admin
     * - http://example.com/myapp
     * - http://example.com/subdir/index.php
     *
     * If a null value is passed, this can be used as well for autodiscovery (default).
     *
     * @param string $base
     * @return $this
     * @throws Zend_Controller_Exception for non-string $base
     */
    public function setBaseUrl($base = null)
    {
        if (!is_string($base) && (null !== $base)) {
            require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Exception('Rewrite base must be a string');
        }

        $this->_baseUrl = $base;

        if ((null !== ($request = $this->getRequest())) && (method_exists($request, 'setBaseUrl'))) {
            $request->setBaseUrl($base);
        }

        return $this;
    }

    /**
     * Retrieve the currently set base URL
     *
     * @return string|null
     */
    public function getBaseUrl()
    {
        $request = $this->getRequest();
        if ((null !== $request) && method_exists($request, 'getBaseUrl')) {
            return $request->getBaseUrl();
        }

        return $this->_baseUrl;
    }

    /**
     * Set the dispatcher object.  The dispatcher is responsible for
     * taking a Zend_Controller_Dispatcher_Token object, instantiating the controller, and
     * call the action method of the controller.
     *
     * @param Zend_Controller_Dispatcher_Interface $dispatcher
     * @return $this
     */
    public function setDispatcher(Zend_Controller_Dispatcher_Interface $dispatcher)
    {
        $this->_dispatcher = $dispatcher;
        return $this;
    }

    /**
     * Return the dispatcher object.
     *
     * @return Zend_Controller_Dispatcher_Interface
     */
    public function getDispatcher()
    {
        /**
         * Instantiate the default dispatcher if one was not set.
         */
        if (!$this->_dispatcher instanceof Zend_Controller_Dispatcher_Interface) {
            require_once 'Zend/Controller/Dispatcher/Standard.php';
            $this->_dispatcher = new Zend_Controller_Dispatcher_Standard();
        }
        return $this->_dispatcher;
    }

    /**
     * Set response class/object
     *
     * Set the response object.  The response is a container for action
     * responses and headers. Usage is optional.
     *
     * If a class name is provided, instantiates a response object.
     *
     * @param string|Zend_Controller_Response_Abstract $response
     * @throws Zend_Controller_Exception if invalid response class
     * @return $this
     */
    public function setResponse($response)
    {
        if (is_string($response)) {
            if (!class_exists($response)) {
                require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($response);
            }
            $response = new $response();
        }
        if (!$response instanceof Zend_Controller_Response_Abstract) {
            require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Exception('Invalid response class');
        }

        $this->_response = $response;

        return $this;
    }

    /**
     * Return the response object.
     *
     * @return null|Zend_Controller_Response_Abstract
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Add or modify a parameter to use when instantiating an action controller
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setParam($name, $value)
    {
        $name = (string) $name;
        $this->_invokeParams[$name] = $value;
        return $this;
    }

    /**
     * Set parameters to pass to action controller constructors
     *
     * @param array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->_invokeParams = array_merge($this->_invokeParams, $params);
        return $this;
    }

    /**
     * Retrieve a single parameter from the controller parameter stack
     *
     * @param string $name
     * @return mixed
     */
    public function getParam($name)
    {
        if(isset($this->_invokeParams[$name])) {
            return $this->_invokeParams[$name];
        }

        return null;
    }

    /**
     * Retrieve action controller instantiation parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_invokeParams;
    }

    /**
     * Clear the controller parameter stack
     *
     * By default, clears all parameters. If a parameter name is given, clears
     * only that parameter; if an array of parameter names is provided, clears
     * each.
     *
     * @param null|string|array $name single key or array of keys for params to clear
     * @return $this
     */
    public function clearParams($name = null)
    {
        if (null === $name) {
            $this->_invokeParams = [];
        } elseif (is_string($name) && isset($this->_invokeParams[$name])) {
            unset($this->_invokeParams[$name]);
        } elseif (is_array($name)) {
            foreach ($name as $key) {
                if (is_string($key) && isset($this->_invokeParams[$key])) {
                    unset($this->_invokeParams[$key]);
                }
            }
        }

        return $this;
    }

    /**
     * Register a plugin.
     *
     * @param  Zend_Controller_Plugin_Abstract $plugin
     * @param  int $stackIndex Optional; stack index for plugin
     * @return $this
     */
    public function registerPlugin(Zend_Controller_Plugin_Abstract $plugin, $stackIndex = null)
    {
        $this->_plugins->registerPlugin($plugin, $stackIndex);
        return $this;
    }

    /**
     * Unregister a plugin.
     *
     * @param  string|Zend_Controller_Plugin_Abstract $plugin Plugin class or object to unregister
     * @return $this
     */
    public function unregisterPlugin($plugin)
    {
        $this->_plugins->unregisterPlugin($plugin);
        return $this;
    }

    /**
     * Is a particular plugin registered?
     *
     * @param  string $class
     * @return bool
     */
    public function hasPlugin($class)
    {
        return $this->_plugins->hasPlugin($class);
    }

    /**
     * Retrieve a plugin or plugins by class
     *
     * @param  string $class
     * @return false|Zend_Controller_Plugin_Abstract|array
     */
    public function getPlugin($class)
    {
        return $this->_plugins->getPlugin($class);
    }

    /**
     * Retrieve all plugins
     *
     * @return array
     */
    public function getPlugins()
    {
        return $this->_plugins->getPlugins();
    }

    /**
     * Set the throwExceptions flag and retrieve current status
     *
     * Set whether exceptions encounted in the dispatch loop should be thrown
     * or caught and trapped in the response object.
     *
     * Default behaviour is to trap them in the response object; call this
     * method to have them thrown.
     *
     * Passing no value will return the current value of the flag; passing a
     * boolean true or false value will set the flag and return the current
     * object instance.
     *
     * @param boolean $flag Defaults to null (return flag state)
     * @return boolean|Zend_Controller_Front Used as a setter, returns object; as a getter, returns boolean
     */
    public function throwExceptions($flag = null)
    {
        if ($flag !== null) {
            $this->_throwExceptions = (bool) $flag;
            return $this;
        }

        return $this->_throwExceptions;
    }

    /**
     * Set whether {@link dispatch()} should return the response without first
     * rendering output. By default, output is rendered and dispatch() returns
     * nothing.
     *
     * @param boolean $flag
     * @return boolean|Zend_Controller_Front Used as a setter, returns object; as a getter, returns boolean
     */
    public function returnResponse($flag = null)
    {
        if (true === $flag) {
            $this->_returnResponse = true;
            return $this;
        } elseif (false === $flag) {
            $this->_returnResponse = false;
            return $this;
        }

        return $this->_returnResponse;
    }

    /**
     * Dispatch an HTTP request to a controller/action.
     *
     * @param Zend_Controller_Request_Abstract|null $request
     * @param Zend_Controller_Response_Abstract|null $response
     * @return void|Zend_Controller_Response_Abstract Returns response object if returnResponse() is true
     */
    public function dispatch(?Zend_Controller_Request_Abstract $request = null, ?Zend_Controller_Response_Abstract $response = null)
    {
        if (!$this->getParam('noErrorHandler') && !$this->_plugins->hasPlugin('Zend_Controller_Plugin_ErrorHandler')) {
            // Register with stack index of 100
            require_once 'Zend/Controller/Plugin/ErrorHandler.php';
            $this->_plugins->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(), 100);
        }

        if (!$this->getParam('noViewRenderer') && !Zend_Controller_Action_HelperBroker::hasHelper('viewRenderer')) {
            require_once 'Zend/Controller/Action/Helper/ViewRenderer.php';
            Zend_Controller_Action_HelperBroker::getStack()->offsetSet(-80, new Zend_Controller_Action_Helper_ViewRenderer());
        }

        /**
         * Instantiate default request object (HTTP version) if none provided
         */
        if ($request !== null) {
            $this->setRequest($request);
        } elseif ((null === $request) && (null === ($request = $this->getRequest()))) {
            require_once 'Zend/Controller/Request/Http.php';
            $request = new Zend_Controller_Request_Http();
            $this->setRequest($request);
        }

        /**
         * Set base URL of request object, if available
         */
        if (is_callable([$this->_request, 'setBaseUrl'])) {
            if (null !== $this->_baseUrl) {
                $this->_request->setBaseUrl($this->_baseUrl);
            }
        }

        /**
         * Instantiate default response object (HTTP version) if none provided
         */
        if ($response !== null) {
            $this->setResponse($response);
        } elseif ((null === $this->_response) && (null === ($this->_response = $this->getResponse()))) {
            require_once 'Zend/Controller/Response/Http.php';
            $response = new Zend_Controller_Response_Http();
            $this->setResponse($response);
        }

        /**
         * Register request and response objects with plugin broker
         */
        $this->_plugins
             ->setRequest($this->_request)
             ->setResponse($this->_response);

        /**
         * Initialize router
         */
        $router = $this->getRouter();
        $router->setParams($this->getParams());

        /**
         * Initialize dispatcher
         */
        $dispatcher = $this->getDispatcher();
        $dispatcher->setParams($this->getParams())
                   ->setResponse($this->_response);

        // Begin dispatch
        try {
            /**
             * Route request to controller/action, if a router is provided
             */

            /**
            * Notify plugins of router startup
            */
            $this->_plugins->routeStartup($this->_request);

            try {
                $router->route($this->_request);
            }  catch (Throwable $e) {
                if ($this->throwExceptions()) {
                    throw $e;
                }

                $this->_response->setException($e);
            }

            /**
            * Notify plugins of router completion
            */
            $this->_plugins->routeShutdown($this->_request);

            /**
             * Notify plugins of dispatch loop startup
             */
            $this->_plugins->dispatchLoopStartup($this->_request);

            /**
             *  Attempt to dispatch the controller/action. If the $this->_request
             *  indicates that it needs to be dispatched, move to the next
             *  action in the request.
             */
            do {
                $this->_request->setDispatched(true);

                /**
                 * Notify plugins of dispatch startup
                 */
                $this->_plugins->preDispatch($this->_request);

                /**
                 * Skip requested action if preDispatch() has reset it
                 */
                if (!$this->_request->isDispatched()) {
                    continue;
                }

                /**
                 * Dispatch request
                 */
                try {
                    $dispatcher->dispatch($this->_request, $this->_response);
                } catch (Throwable $e) {
                    if ($this->throwExceptions()) {
                        throw $e;
                    }
                    $this->_response->setException($e);
                }

                /**
                 * Notify plugins of dispatch completion
                 */
                $this->_plugins->postDispatch($this->_request);
            } while (!$this->_request->isDispatched());
        } catch (Throwable $e) {
            if ($this->throwExceptions()) {
                throw $e;
            }

            $this->_response->setException($e);
        }

        /**
         * Notify plugins of dispatch loop completion
         */
        try {
            $this->_plugins->dispatchLoopShutdown();
        } catch (Throwable $e) {
            if ($this->throwExceptions()) {
                throw $e;
            }

            $this->_response->setException($e);
        }

        if ($this->returnResponse()) {
            return $this->_response;
        }

        $this->_response->sendResponse();
    }
}
