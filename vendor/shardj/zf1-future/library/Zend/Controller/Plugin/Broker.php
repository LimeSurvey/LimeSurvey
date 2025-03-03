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
 * @subpackage Plugins
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Controller_Plugin_Abstract */
require_once 'Zend/Controller/Plugin/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Plugin_Broker extends Zend_Controller_Plugin_Abstract
{
    /**
     * Array of instance of objects extending Zend_Controller_Plugin_Abstract
     *
     * @var array
     */
    protected $_plugins = [];

    /**
     * Register a plugin.
     *
     * @param Zend_Controller_Plugin_Abstract $plugin
     * @param int $stackIndex
     * @return $this
     * @throws Zend_Controller_Exception
     */
    public function registerPlugin(Zend_Controller_Plugin_Abstract $plugin, $stackIndex = null)
    {
        if (false !== array_search($plugin, $this->_plugins, true)) {
            require_once 'Zend/Controller/Exception.php';
            throw new Zend_Controller_Exception('Plugin already registered');
        }

        $stackIndex = (int) $stackIndex;

        if ($stackIndex) {
            if (isset($this->_plugins[$stackIndex])) {
                require_once 'Zend/Controller/Exception.php';
                throw new Zend_Controller_Exception('Plugin with stackIndex "' . $stackIndex . '" already registered');
            }
            $this->_plugins[$stackIndex] = $plugin;
        } else {
            $stackIndex = count($this->_plugins);
            while (isset($this->_plugins[$stackIndex])) {
                ++$stackIndex;
            }
            $this->_plugins[$stackIndex] = $plugin;
        }

        $request = $this->getRequest();
        if ($request) {
            $this->_plugins[$stackIndex]->setRequest($request);
        }
        $response = $this->getResponse();
        if ($response) {
            $this->_plugins[$stackIndex]->setResponse($response);
        }

        ksort($this->_plugins);

        return $this;
    }

    /**
     * Unregister a plugin.
     *
     * @param string|Zend_Controller_Plugin_Abstract $plugin Plugin object or class name
     * @return $this
     * @throws Zend_Controller_Exception
     */
    public function unregisterPlugin($plugin)
    {
        if ($plugin instanceof Zend_Controller_Plugin_Abstract) {
            // Given a plugin object, find it in the array
            $key = array_search($plugin, $this->_plugins, true);
            if (false === $key) {
                require_once 'Zend/Controller/Exception.php';
                throw new Zend_Controller_Exception('Plugin never registered.');
            }
            unset($this->_plugins[$key]);
        } elseif (is_string($plugin)) {
            // Given a plugin class, find all plugins of that class and unset them
            foreach ($this->_plugins as $key => $_plugin) {
                $type = get_class($_plugin);
                if ($plugin == $type) {
                    unset($this->_plugins[$key]);
                }
            }
        }
        return $this;
    }

    /**
     * Is a plugin of a particular class registered?
     *
     * @param  string $class
     * @return bool
     */
    public function hasPlugin($class)
    {
        foreach ($this->_plugins as $plugin) {
            $type = get_class($plugin);
            if ($class == $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve a plugin or plugins by class
     *
     * @param  string $class Class name of plugin(s) desired
     * @return false|Zend_Controller_Plugin_Abstract|array Returns false if none found, plugin if only one found, and array of plugins if multiple plugins of same class found
     */
    public function getPlugin($class)
    {
        $found = [];
        foreach ($this->_plugins as $plugin) {
            $type = get_class($plugin);
            if ($class == $type) {
                $found[] = $plugin;
            }
        }

        switch (count($found)) {
            case 0:
                return false;
            case 1:
                return $found[0];
            default:
                return $found;
        }
    }

    /**
     * Retrieve all plugins
     *
     * @return array
     */
    public function getPlugins()
    {
        return $this->_plugins;
    }

    /**
     * Set request object, and register with each plugin
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return $this
     */
    public function setRequest(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;

        foreach ($this->_plugins as $plugin) {
            $plugin->setRequest($request);
        }

        return $this;
    }

    /**
     * Get request object
     *
     * @return Zend_Controller_Request_Abstract $request
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Set response object
     *
     * @param Zend_Controller_Response_Abstract $response
     * @return $this
     */
    public function setResponse(Zend_Controller_Response_Abstract $response)
    {
        $this->_response = $response;

        foreach ($this->_plugins as $plugin) {
            $plugin->setResponse($response);
        }

        return $this;
    }

    /**
     * Get response object
     *
     * @return Zend_Controller_Response_Abstract $response
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Called before Zend_Controller_Front begins evaluating the
     * request against its routes.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     * @throws Zend_Controller_Exception
     */
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        foreach ($this->_plugins as $plugin) {
            try {
                $plugin->routeStartup($request);
            } catch (\Throwable $e) {
                if (Zend_Controller_Front::getInstance()->throwExceptions()) {
                    throw new Zend_Controller_Exception($e->getMessage() . $e->getTraceAsString(), $e->getCode(), $e);
                }

                $this->getResponse()->setException($e);
            }
        }
    }

    /**
     * Called before Zend_Controller_Front exits its iterations over
     * the route set.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     * @throws Zend_Controller_Exception
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        foreach ($this->_plugins as $plugin) {
            try {
                $plugin->routeShutdown($request);
            } catch (\Throwable $e) {
                if (Zend_Controller_Front::getInstance()->throwExceptions()) {
                    throw new Zend_Controller_Exception($e->getMessage() . $e->getTraceAsString(), $e->getCode(), $e);
                }

                $this->getResponse()->setException($e);
            }
        }
    }

    /**
     * Called before Zend_Controller_Front enters its dispatch loop.
     *
     * During the dispatch loop, Zend_Controller_Front keeps a
     * Zend_Controller_Request_Abstract object, and uses
     * Zend_Controller_Dispatcher to dispatch the
     * Zend_Controller_Request_Abstract object to controllers/actions.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     * @throws Zend_Controller_Exception
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        foreach ($this->_plugins as $plugin) {
            try {
                $plugin->dispatchLoopStartup($request);
            } catch (\Throwable $e) {
                if (Zend_Controller_Front::getInstance()->throwExceptions()) {
                    throw new Zend_Controller_Exception($e->getMessage() . $e->getTraceAsString(), $e->getCode(), $e);
                }

                $this->getResponse()->setException($e);
            }
        }
    }

    /**
     * Called before an action is dispatched by Zend_Controller_Dispatcher.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     * @throws Zend_Controller_Exception
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        foreach ($this->_plugins as $plugin) {
            try {
                $plugin->preDispatch($request);
            } catch (\Throwable $e) {
                if (Zend_Controller_Front::getInstance()->throwExceptions()) {
                    throw new Zend_Controller_Exception($e->getMessage() . $e->getTraceAsString(), $e->getCode(), $e);
                }

                $this->getResponse()->setException($e);
                // skip rendering of normal dispatch give the error handler a try
                $this->getRequest()->setDispatched(false);
            }
        }
    }

    /**
     * Called after an action is dispatched by Zend_Controller_Dispatcher.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     * @throws Zend_Controller_Exception
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        foreach ($this->_plugins as $plugin) {
            try {
                $plugin->postDispatch($request);
            } catch (\Throwable $e) {
                if (Zend_Controller_Front::getInstance()->throwExceptions()) {
                    throw new Zend_Controller_Exception($e->getMessage() . $e->getTraceAsString(), $e->getCode(), $e);
                }

                $this->getResponse()->setException($e);
            }
        }
    }

    /**
     * Called before Zend_Controller_Front exits its dispatch loop.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return void
     * @throws Zend_Controller_Exception
     */
    public function dispatchLoopShutdown()
    {
       foreach ($this->_plugins as $plugin) {
           try {
                $plugin->dispatchLoopShutdown();
            } catch (\Throwable $e) {
                if (Zend_Controller_Front::getInstance()->throwExceptions()) {
                    throw new Zend_Controller_Exception($e->getMessage() . $e->getTraceAsString(), $e->getCode(), $e);
                }

                $this->getResponse()->setException($e);
            }
       }
    }
}
