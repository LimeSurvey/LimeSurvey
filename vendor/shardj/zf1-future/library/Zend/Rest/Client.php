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
 * @package    Zend_Rest
 * @subpackage Client
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Zend_Service_Abstract */
require_once 'Zend/Service/Abstract.php';

/** Zend_Rest_Client_Result */
require_once 'Zend/Rest/Client/Result.php';

/** Zend_Uri */
require_once 'Zend/Uri.php';

/**
 * @category   Zend
 * @package    Zend_Rest
 * @subpackage Client
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Rest_Client extends Zend_Service_Abstract
{
    /**
     * Data for the query
     * @var array
     */
    protected $_data = [];

     /**
     * Zend_Uri of this web service
     * @var Zend_Uri_Http
     */
    protected $_uri = null;

    /**
     * Flag indicating the Zend_Http_Client is fresh and needs no reset.
     * Must be set explicitly if you want to keep preset parameters.
     * @var bool true if you do not want a reset. Default false.
     */
    protected $_noReset = false;

    /**
     * Constructor
     *
     * @param string|Zend_Uri_Http $uri URI for the web service
     * @return void
     */
    public function __construct($uri = null)
    {
        if (!empty($uri)) {
            $this->setUri($uri);
        }
    }

    /**
     * Set the URI to use in the request
     *
     * @param string|Zend_Uri_Http $uri URI for the web service
     * @return Zend_Rest_Client
     */
    public function setUri($uri)
    {
        if ($uri instanceof Zend_Uri_Http) {
            $this->_uri = $uri;
        } else {
            $this->_uri = Zend_Uri::factory($uri);
        }

        return $this;
    }

    /**
     * Retrieve the current request URI object
     *
     * @return Zend_Uri_Http
     */
    public function getUri()
    {
        return $this->_uri;
    }

    /**
     * Call a remote REST web service URI and return the Zend_Http_Response object
     *
     * @param  string $path            The path to append to the URI
     * @throws Zend_Rest_Client_Exception
     * @return void
     */
    private function _prepareRest($path)
    {
        // Get the URI object and configure it
        if (!$this->_uri instanceof Zend_Uri_Http) {
            require_once 'Zend/Rest/Client/Exception.php';
            throw new Zend_Rest_Client_Exception('URI object must be set before performing call');
        }

        $uri = $this->_uri->getUri();

        if ($path[0] != '/' && $uri[strlen($uri)-1] != '/') {
            $path = '/' . $path;
        }

        $this->_uri->setPath($path);

        /**
         * Get the HTTP client and configure it for the endpoint URI.  Do this each time
         * because the Zend_Http_Client instance is shared among all Zend_Service_Abstract subclasses.
         */
        if ($this->_noReset) {
            // if $_noReset we do not want to reset on this request,
            // but we do on any subsequent request
            $this->_noReset = false;
        } else {
            self::getHttpClient()->resetParameters();
        }

        self::getHttpClient()->setUri($this->_uri);
    }

    /**
     * Tells Zend_Rest_Client not to reset all parameters on it's
     * Zend_Http_Client. If you want no reset, this must be called explicitly
     * before every request for which you do not want to reset the parameters.
     * Parameters will accumulate between requests, but as soon as you do not
     * call this function prior to any request, all preset parameters will be reset
     * as by default.
     * @param boolean $bool
     */
    public function setNoReset($bool = true)
    {
        $this->_noReset = $bool;
    }

    /**
     * Performs an HTTP GET request to the $path.
     *
     * @param string $path
     * @param array  $query Array of GET parameters
     * @throws Zend_Http_Client_Exception
     * @return Zend_Http_Response
     */
    public function restGet($path, ?array $query = null)
    {
        $this->_prepareRest($path);
        $client = self::getHttpClient();
        $client->setParameterGet($query);
        return $client->request('GET');
    }

    /**
     * Perform a POST or PUT
     *
     * Performs a POST or PUT request. Any data provided is set in the HTTP
     * client. String data is pushed in as raw POST data; array or object data
     * is pushed in as POST parameters.
     *
     * @param mixed $method
     * @param mixed $data
     * @return Zend_Http_Response
     */
    protected function _performPost($method, $data = null)
    {
        $client = self::getHttpClient();
        if (is_string($data)) {
            $client->setRawData($data);
        } elseif (is_array($data) || is_object($data)) {
            $client->setParameterPost((array) $data);
        }
        return $client->request($method);
    }

    /**
     * Performs an HTTP POST request to $path.
     *
     * @param string $path
     * @param mixed $data Raw data to send
     * @throws Zend_Http_Client_Exception
     * @return Zend_Http_Response
     */
    public function restPost($path, $data = null)
    {
        $this->_prepareRest($path);
        return $this->_performPost('POST', $data);
    }

    /**
     * Performs an HTTP PUT request to $path.
     *
     * @param string $path
     * @param mixed $data Raw data to send in request
     * @throws Zend_Http_Client_Exception
     * @return Zend_Http_Response
     */
    public function restPut($path, $data = null)
    {
        $this->_prepareRest($path);
        return $this->_performPost('PUT', $data);
    }

    /**
     * Performs an HTTP DELETE request to $path.
     *
     * @param string $path
     * @throws Zend_Http_Client_Exception
     * @return Zend_Http_Response
     */
    public function restDelete($path, $data = null)
    {
        $this->_prepareRest($path);
        return $this->_performPost('DELETE', $data);
    }

    /**
     * Method call overload
     *
     * Allows calling REST actions as object methods; however, you must
     * follow-up by chaining the request with a request to an HTTP request
     * method (post, get, delete, put):
     * <code>
     * $response = $rest->sayHello('Foo', 'Manchu')->get();
     * </code>
     *
     * Or use them together, but in sequential calls:
     * <code>
     * $rest->sayHello('Foo', 'Manchu');
     * $response = $rest->get();
     * </code>
     *
     * @param string $method Method name
     * @param array $args Method args
     * @return Zend_Rest_Client_Result|Zend_Rest_Client Zend_Rest_Client if using
     * a remote method, Zend_Rest_Client_Result if using an HTTP request method
     */
    public function __call($method, $args)
    {
        $methods = ['post', 'get', 'delete', 'put'];

        if (in_array(strtolower($method), $methods)) {
            if (!isset($args[0])) {
                $args[0] = $this->_uri->getPath();
            }
            $this->_data['rest'] = 1;
            $data = array_slice($args, 1) + $this->_data;
            $response = $this->{'rest' . $method}($args[0], $data);
            $this->_data = [];//Initializes for next Rest method.
            return new Zend_Rest_Client_Result($response->getBody());
        }

        // More than one arg means it's definitely a Zend_Rest_Server
        if (count($args) === 1) {
            // Uses first called function name as method name
            if (!isset($this->_data['method'])) {
                $this->_data['method'] = $method;
                $this->_data['arg1']  = $args[0];
            }
            $this->_data[$method]  = $args[0];
        } else {
            $this->_data['method'] = $method;

            if (count($args) > 0) {
                foreach ($args as $key => $arg) {
                    $key = 'arg' . $key;
                    $this->_data[$key] = $arg;
                }
            }
        }

        return $this;
    }
}
