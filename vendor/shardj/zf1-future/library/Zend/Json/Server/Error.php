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
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Json
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Json_Server_Error
{
    public const ERROR_PARSE           = -32768;
    public const ERROR_INVALID_REQUEST = -32600;
    public const ERROR_INVALID_METHOD  = -32601;
    public const ERROR_INVALID_PARAMS  = -32602;
    public const ERROR_INTERNAL        = -32603;
    public const ERROR_OTHER           = -32000;

    /**
     * Allowed error codes
     * @var array
     */
    protected $_allowedCodes = [
        self::ERROR_PARSE,
        self::ERROR_INVALID_REQUEST,
        self::ERROR_INVALID_METHOD,
        self::ERROR_INVALID_PARAMS,
        self::ERROR_INTERNAL,
        self::ERROR_OTHER,
    ];

    /**
     * Current code
     * @var int
     */
    protected $_code = -32000;

    /**
     * Error data
     * @var mixed
     */
    protected $_data;

    /**
     * Error message
     * @var string
     */
    protected $_message;

    /**
     * Constructor
     *
     * @param  string $message
     * @param  int $code
     * @param  mixed $data
     * @return void
     */
    public function __construct($message = null, $code = -32000, $data = null)
    {
        $this->setMessage($message)
             ->setCode($code)
             ->setData($data);
    }

    /**
     * Set error code
     *
     * @param  int $code
     * @return $this
     */
    public function setCode($code)
    {
        if (!is_scalar($code)) {
            return $this;
        }

        $code = (int) $code;
        if (in_array($code, $this->_allowedCodes)) {
            $this->_code = $code;
        } elseif (in_array($code, range(-32099, -32000))) {
            $this->_code = $code;
        }

        return $this;
    }

    /**
     * Get error code
     *
     * @return int|null
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Set error message
     *
     * @param  string $message
     * @return $this
     */
    public function setMessage($message)
    {
        if (!is_scalar($message)) {
            return $this;
        }

        $this->_message = (string) $message;
        return $this;
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Set error data
     *
     * @param  mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * Get error data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Cast error to array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'code'    => $this->getCode(),
            'message' => $this->getMessage(),
            'data'    => $this->getData(),
        ];
    }

    /**
     * Cast error to JSON
     *
     * @return string
     */
    public function toJson()
    {
        require_once 'Zend/Json.php';
        return Zend_Json::encode($this->toArray());
    }

    /**
     * Cast to string (JSON)
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}

