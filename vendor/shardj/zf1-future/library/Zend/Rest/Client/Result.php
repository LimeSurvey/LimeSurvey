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

require_once 'Zend/Xml/Security.php';

/**
 * @category   Zend
 * @package    Zend_Rest
 * @subpackage Client
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Rest_Client_Result implements IteratorAggregate {
    /**
     * @var SimpleXMLElement
     */
    protected $_sxml;

    /**
     * error information
     * @var string
     */
    protected $_errstr;

    /**
     * Constructor
     *
     * @param string $data XML Result
     * @return void
     */
    public function __construct($data)
    {
        set_error_handler([$this, 'handleXmlErrors']);
        $this->_sxml = Zend_Xml_Security::scan($data);
        restore_error_handler();
        if($this->_sxml === false) {
            if ($this->_errstr === null) {
                $message = "An error occured while parsing the REST response with simplexml.";
            } else {
                $message = "REST Response Error: " . $this->_errstr;
                $this->_errstr = null;
            }
            require_once "Zend/Rest/Client/Result/Exception.php";
            throw new Zend_Rest_Client_Result_Exception($message);
        }
    }

    /**
     * Temporary error handler for parsing REST responses.
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @param array  $errcontext
     * @return true
     */
    public function handleXmlErrors($errno, $errstr, $errfile = null, $errline = null, ?array $errcontext = null)
    {
        $this->_errstr = $errstr;
        return true;
    }

    /**
     * Casts a SimpleXMLElement to its appropriate PHP value
     *
     * @param SimpleXMLElement $value
     * @return string|null
     */
    public function toValue(SimpleXMLElement $value)
    {
        $node = dom_import_simplexml($value);
        return $node->nodeValue;
    }

    /**
     * Get Property Overload
     *
     * @param string $name
     * @return null|SimpleXMLElement|array Null if not found, SimpleXMLElement if only one value found, array of Zend_Rest_Client_Result objects otherwise
     */
    public function __get($name)
    {
        if (isset($this->_sxml->{$name})) {
            return $this->_sxml->{$name};
        }

        $result = $this->_sxml->xpath("//$name");
        $count  = count($result);

        if ($count === 0) {
            return null;
        }

        if ($count === 1) {
            return $result[0];
        }

        return $result;
    }

    /**
     * Cast properties to PHP values
     *
     * For arrays, loops through each element and casts to a value as well.
     *
     * @param string $method
     * @param array $args
     * @return array|string|null
     */
    public function __call($method, $args)
    {
        if (null !== ($value = $this->__get($method))) {
            if (!is_array($value)) {
                return $this->toValue($value);
            } else {
                $return = [];
                foreach ($value as $element) {
                    $return[] = $this->toValue($element);
                }
                return $return;
            }
        }

        return null;
    }


    /**
     * Isset Overload
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        if (isset($this->_sxml->{$name})) {
            return true;
        }

        $result = $this->_sxml->xpath("//$name");

        if (sizeof($result) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Implement IteratorAggregate::getIterator()
     *
     * @return bool|DomDocument|SimpleXMLElement|null
     */
    #[\ReturnTypeWillChange]
    public function getIterator(): \Traversable
    {
        return $this->_sxml;
    }

    /**
     * Get Request Status
     *
     * @return boolean
     */
    public function getStatus()
    {
        $status = $this->_sxml->xpath('//status/text()');
        if ( !isset($status[0]) ) return false;

        $status = strtolower($status[0]);

        if (ctype_alpha($status) && $status == 'success') {
            return true;
        } elseif (ctype_alpha($status) && $status != 'success') {
            return false;
        } else {
            return (bool) $status;
        }
    }

    public function isError()
    {
        $status = $this->getStatus();
        if ($status) {
            return false;
        } else {
            return true;
        }
    }

    public function isSuccess()
    {
        $status = $this->getStatus();
        if ($status) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * toString overload
     *
     * Be sure to only call this when the result is a single value!
     *
     * @return string
     */
    public function __toString()
    {
        if (!$this->getStatus()) {
            $message = $this->_sxml->xpath('//message');
            return (string) $message[0];
        } else {
            $result = $this->_sxml->xpath('//response');
            if (sizeof($result) > 1) {
                return (string) "An error occured.";
            } else {
                return (string) $result[0];
            }
        }
    }
}
