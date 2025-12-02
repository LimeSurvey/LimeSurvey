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
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Json_Server_Smd
 */
require_once 'Zend/Json/Server/Smd.php';

/**
 * Create Service Mapping Description for a method
 *
 * @package    Zend_Json
 * @subpackage Server
 * @version    $Id$
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Json_Server_Smd_Service
{
    /**#@+
     * Service metadata
     * @var string
     */
    protected $_envelope  = Zend_Json_Server_Smd::ENV_JSONRPC_1;
    protected $_name;
    protected $_return;
    protected $_target;
    protected $_transport = 'POST';
    /**#@-*/

    /**
     * Allowed envelope types
     * @var array
     */
    protected $_envelopeTypes = [
        Zend_Json_Server_Smd::ENV_JSONRPC_1,
        Zend_Json_Server_Smd::ENV_JSONRPC_2,
    ];

    /**
     * Regex for names
     * @var string
     */
    protected $_nameRegex = '/^[a-z][a-z0-9._]+$/i';

    /**
     * Parameter option types
     * @var array
     */
    protected $_paramOptionTypes = [
        'name'        => 'is_string',
        'optional'    => 'is_bool',
        'default'     => null,
        'description' => 'is_string',
    ];

    /**
     * Service params
     * @var array
     */
    protected $_params = [];

    /**
     * Mapping of parameter types to JSON-RPC types
     * @var array
     */
    protected $_paramMap = [
        'any'     => 'any',
        'arr'     => 'array',
        'array'   => 'array',
        'assoc'   => 'object',
        'bool'    => 'boolean',
        'boolean' => 'boolean',
        'dbl'     => 'float',
        'double'  => 'float',
        'false'   => 'boolean',
        'float'   => 'float',
        'hash'    => 'object',
        'integer' => 'integer',
        'int'     => 'integer',
        'mixed'   => 'any',
        'nil'     => 'null',
        'null'    => 'null',
        'object'  => 'object',
        'string'  => 'string',
        'str'     => 'string',
        'struct'  => 'object',
        'true'    => 'boolean',
        'void'    => 'null',
    ];

    /**
     * Allowed transport types
     * @var array
     */
    protected $_transportTypes = [
        'POST',
    ];

    /**
     * Constructor
     *
     * @param  string|array $spec
     * @return void
     * @throws Zend_Json_Server_Exception if no name provided
     */
    public function __construct($spec)
    {
        if (is_string($spec)) {
            $this->setName($spec);
        } elseif (is_array($spec)) {
            $this->setOptions($spec);
        }

        if (null == $this->getName()) {
            require_once 'Zend/Json/Server/Exception.php';
            throw new Zend_Json_Server_Exception('SMD service description requires a name; none provided');
        }
    }

    /**
     * Set object state
     *
     * @param  array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            if ('options' == strtolower($key)) {
                continue;
            }
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set service name
     *
     * @param  string $name
     * @return $this
     * @throws Zend_Json_Server_Exception
     */
    public function setName($name)
    {
        $name = (string) $name;
        if (!preg_match($this->_nameRegex, $name)) {
            require_once 'Zend/Json/Server/Exception.php';
            throw new Zend_Json_Server_Exception(sprintf('Invalid name "%s" provided for service; must follow PHP method naming conventions', $name));
        }
        $this->_name = $name;
        return $this;
    }

    /**
     * Retrieve name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set Transport
     *
     * Currently limited to POST
     *
     * @param  string $transport
     * @return $this
     */
    public function setTransport($transport)
    {
        if (!in_array($transport, $this->_transportTypes)) {
            require_once 'Zend/Json/Server/Exception.php';
            throw new Zend_Json_Server_Exception(sprintf('Invalid transport "%s"; please select one of (%s)', $transport, implode(', ', $this->_transportTypes)));
        }

        $this->_transport = $transport;
        return $this;
    }

    /**
     * Get transport
     *
     * @return string
     */
    public function getTransport()
    {
        return $this->_transport;
    }

    /**
     * Set service target
     *
     * @param  string $target
     * @return $this
     */
    public function setTarget($target)
    {
        $this->_target = (string) $target;
        return $this;
    }

    /**
     * Get service target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->_target;
    }

    /**
     * Set envelope type
     *
     * @param  string $envelopeType
     * @return $this
     */
    public function setEnvelope($envelopeType)
    {
        if (!in_array($envelopeType, $this->_envelopeTypes)) {
            require_once 'Zend/Json/Server/Exception.php';
            throw new Zend_Json_Server_Exception(sprintf('Invalid envelope type "%s"; please specify one of (%s)', $envelopeType, implode(', ', $this->_envelopeTypes)));
        }

        $this->_envelope = $envelopeType;
        return $this;
    }

    /**
     * Get envelope type
     *
     * @return string
     */
    public function getEnvelope()
    {
        return $this->_envelope;
    }

    /**
     * Add a parameter to the service
     *
     * @param  string|array $type
     * @param  array $options
     * @param  int|null $order
     * @return $this
     */
    public function addParam($type, array $options = [], $order = null)
    {
        if (is_string($type)) {
            $type = $this->_validateParamType($type);
        } elseif (is_array($type)) {
            foreach ($type as $key => $paramType) {
                $type[$key] = $this->_validateParamType($paramType);
            }
        } else {
            require_once 'Zend/Json/Server/Exception.php';
            throw new Zend_Json_Server_Exception('Invalid param type provided');
        }

        $paramOptions = [
            'type' => $type,
        ];
        foreach ($options as $key => $value) {
            if (in_array($key, array_keys($this->_paramOptionTypes))) {
                if (null !== ($callback = $this->_paramOptionTypes[$key])) {
                    if (!$callback($value)) {
                        continue;
                    }
                }
                $paramOptions[$key] = $value;
            }
        }

        $this->_params[] = [
            'param' => $paramOptions,
            'order' => $order,
        ];

        return $this;
    }

    /**
     * Add params
     *
     * Each param should be an array, and should include the key 'type'.
     *
     * @param  array $params
     * @return $this
     */
    public function addParams(array $params)
    {
        ksort($params);
        foreach ($params as $options) {
            if (!is_array($options)) {
                continue;
            }
            if (!array_key_exists('type', $options)) {
                continue;
            }
            $type  = $options['type'];
            $order = (array_key_exists('order', $options)) ? $options['order'] : null;
            $this->addParam($type, $options, $order);
        }
        return $this;
    }

    /**
     * Overwrite all parameters
     *
     * @param  array $params
     * @return $this
     */
    public function setParams(array $params)
    {
        $this->_params = [];
        return $this->addParams($params);
    }

    /**
     * Get all parameters
     *
     * Returns all params in specified order.
     *
     * @return array
     */
    public function getParams()
    {
        $params = [];
        $index  = 0;
        foreach ($this->_params as $param) {
            if (null === $param['order']) {
                if (array_search($index, array_keys($params), true)) {
                    ++$index;
                }
                $params[$index] = $param['param'];
                ++$index;
            } else {
                $params[$param['order']] = $param['param'];
            }
        }
        ksort($params);
        return $params;
    }

    /**
     * Set return type
     *
     * @param  string|array $type
     * @return $this
     */
    public function setReturn($type)
    {
        if (is_string($type)) {
            $type = $this->_validateParamType($type, true);
        } elseif (is_array($type)) {
            foreach ($type as $key => $returnType) {
                $type[$key] = $this->_validateParamType($returnType, true);
            }
        } else {
            require_once 'Zend/Json/Server/Exception.php';
            throw new Zend_Json_Server_Exception('Invalid param type provided ("' . gettype($type) .'")');
        }
        $this->_return = $type;
        return $this;
    }

    /**
     * Get return type
     *
     * @return string|array
     */
    public function getReturn()
    {
        return $this->_return;
    }

    /**
     * Cast service description to array
     *
     * @return array
     */
    public function toArray()
    {
        $name       = $this->getName();
        $envelope   = $this->getEnvelope();
        $target     = $this->getTarget();
        $transport  = $this->getTransport();
        $parameters = $this->getParams();
        $returns    = $this->getReturn();

        if (empty($target)) {
            return compact('envelope', 'transport', 'parameters', 'returns');
        }

        return $paramInfo = compact('envelope', 'target', 'transport', 'parameters', 'returns');
    }

    /**
     * Return JSON encoding of service
     *
     * @return string
     */
    public function toJson()
    {
        $service = [$this->getName() => $this->toArray()];

        require_once 'Zend/Json.php';
        return Zend_Json::encode($service);
    }

    /**
     * Cast to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Validate parameter type
     *
     * @param  string $type
     * @return true
     * @throws Zend_Json_Server_Exception
     */
    protected function _validateParamType($type, $isReturn = false)
    {
        if (!is_string($type)) {
            require_once 'Zend/Json/Server/Exception.php';
            throw new Zend_Json_Server_Exception('Invalid param type provided ("' . $type .'")');
        }

        if (!array_key_exists($type, $this->_paramMap)) {
            $type = 'object';
        }

        $paramType = $this->_paramMap[$type];
        if (!$isReturn && ('null' == $paramType)) {
            require_once 'Zend/Json/Server/Exception.php';
            throw new Zend_Json_Server_Exception('Invalid param type provided ("' . $type . '")');
        }

        return $paramType;
    }
}
