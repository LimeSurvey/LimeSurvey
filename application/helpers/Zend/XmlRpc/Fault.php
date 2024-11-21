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
 * @package    Zend_XmlRpc
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Fault.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * Zend_XmlRpc_Value
 */
require_once 'Zend/XmlRpc/Value.php';

/**
 * XMLRPC Faults
 *
 * Container for XMLRPC faults, containing both a code and a message;
 * additionally, has methods for determining if an XML response is an XMLRPC
 * fault, as well as generating the XML for an XMLRPC fault response.
 *
 * To allow method chaining, you may only use the {@link getInstance()} factory
 * to instantiate a Zend_XmlRpc_Server_Fault.
 *
 * @category   Zend
 * @package    Zend_XmlRpc
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_XmlRpc_Fault
{
    /**
     * Fault code
     * @var int
     */
    protected $_code;

    /**
     * Fault character encoding
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * Fault message
     * @var string
     */
    protected $_message;

    /**
     * Internal fault codes => messages
     * @var array
     */
    protected $_internal = array(
        404 => 'Unknown Error',

        // 610 - 619 reflection errors
        610 => 'Invalid method class',
        611 => 'Unable to attach function or callback; not callable',
        612 => 'Unable to load array; not an array',
        613 => 'One or more method records are corrupt or otherwise unusable',

        // 620 - 629 dispatch errors
        620 => 'Method does not exist',
        621 => 'Error instantiating class to invoke method',
        622 => 'Method missing implementation',
        623 => 'Calling parameters do not match signature',

        // 630 - 639 request errors
        630 => 'Unable to read request',
        631 => 'Failed to parse request',
        632 => 'Invalid request, no method passed; request must contain a \'methodName\' tag',
        633 => 'Param must contain a value',
        634 => 'Invalid method name',
        635 => 'Invalid XML provided to request',
        636 => 'Error creating xmlrpc value',

        // 640 - 649 system.* errors
        640 => 'Method does not exist',

        // 650 - 659 response errors
        650 => 'Invalid XML provided for response',
        651 => 'Failed to parse response',
        652 => 'Invalid response',
        653 => 'Invalid XMLRPC value in response',
    );

    /**
     * Constructor
     *
     * @return Zend_XmlRpc_Fault
     */
    public function __construct($code = 404, $message = '')
    {
        $this->setCode($code);
        $code = $this->getCode();

        if (empty($message) && isset($this->_internal[$code])) {
            $message = $this->_internal[$code];
        } elseif (empty($message)) {
            $message = 'Unknown error';
        }
        $this->setMessage($message);
    }

    /**
     * Set the fault code
     *
     * @param int $code
     * @return Zend_XmlRpc_Fault
     */
    public function setCode($code)
    {
        $this->_code = (int) $code;
        return $this;
    }

    /**
     * Return fault code
     *
     * @return int
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * Retrieve fault message
     *
     * @param string
     * @return Zend_XmlRpc_Fault
     */
    public function setMessage($message)
    {
        $this->_message = (string) $message;
        return $this;
    }

    /**
     * Retrieve fault message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * Set encoding to use in fault response
     *
     * @param string $encoding
     * @return Zend_XmlRpc_Fault
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
        Zend_XmlRpc_Value::setEncoding($encoding);
        return $this;
    }

    /**
     * Retrieve current fault encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Load an XMLRPC fault from XML
     *
     * @param string $fault
     * @return boolean Returns true if successfully loaded fault response, false
     * if response was not a fault response
     * @throws Zend_XmlRpc_Exception if no or faulty XML provided, or if fault
     * response does not contain either code or message
     */
    public function loadXml($fault)
    {
        if (!is_string($fault)) {
            require_once 'Zend/XmlRpc/Exception.php';
            throw new Zend_XmlRpc_Exception('Invalid XML provided to fault');
        }

        try {
            $xml = @new SimpleXMLElement($fault);
        } catch (Exception $e) {
            // Not valid XML
            require_once 'Zend/XmlRpc/Exception.php';
            throw new Zend_XmlRpc_Exception('Failed to parse XML fault: ' . $e->getMessage(), 500, $e);
        }

        // Check for fault
        if (!$xml->fault) {
            // Not a fault
            return false;
        }

        if (!$xml->fault->value->struct) {
            // not a proper fault
            require_once 'Zend/XmlRpc/Exception.php';
            throw new Zend_XmlRpc_Exception('Invalid fault structure', 500);
        }

        $structXml = $xml->fault->value->asXML();
        $struct    = Zend_XmlRpc_Value::getXmlRpcValue($structXml, Zend_XmlRpc_Value::XML_STRING);
        $struct    = $struct->getValue();

        if (isset($struct['faultCode'])) {
            $code = $struct['faultCode'];
        }
        if (isset($struct['faultString'])) {
            $message = $struct['faultString'];
        }

        if (empty($code) && empty($message)) {
            require_once 'Zend/XmlRpc/Exception.php';
            throw new Zend_XmlRpc_Exception('Fault code and string required');
        }

        if (empty($code)) {
            $code = '404';
        }

        if (empty($message)) {
            if (isset($this->_internal[$code])) {
                $message = $this->_internal[$code];
            } else {
                $message = 'Unknown Error';
            }
        }

        $this->setCode($code);
        $this->setMessage($message);

        return true;
    }

    /**
     * Determine if an XML response is an XMLRPC fault
     *
     * @param string $xml
     * @return boolean
     */
    public static function isFault($xml)
    {
        $fault = new self();
        require_once 'Zend/XmlRpc/Exception.php';
        try {
            $isFault = $fault->loadXml($xml);
        } catch (Zend_XmlRpc_Exception $e) {
            $isFault = false;
        }

        return $isFault;
    }

    /**
     * Serialize fault to XML
     *
     * @return string
     */
    public function saveXml()
    {
        // Create fault value
        $faultStruct = array(
            'faultCode'   => $this->getCode(),
            'faultString' => $this->getMessage()
        );
        $value = Zend_XmlRpc_Value::getXmlRpcValue($faultStruct);

        $generator = Zend_XmlRpc_Value::getGenerator();
        $generator->openElement('methodResponse')
                    ->openElement('fault');
        $value->generateXml();
        $generator->closeElement('fault')
                    ->closeElement('methodResponse');

        return $generator->flush();
    }

    /**
     * Return XML fault response
     *
     * @return string
     */
    public function __toString()
    {
        return $this->saveXML();
    }
}
