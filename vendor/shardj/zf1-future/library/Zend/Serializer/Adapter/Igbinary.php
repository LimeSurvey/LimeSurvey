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
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** @see Zend_Serializer_Adapter_AdapterAbstract */
require_once 'Zend/Serializer/Adapter/AdapterAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Serializer
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Serializer_Adapter_Igbinary extends Zend_Serializer_Adapter_AdapterAbstract
{
    /**
     * @var string Serialized null value
     */
    private static $_serializedNull = null;

    /**
     * Constructor
     *
     * @param  array|Zend_Config $opts
     * @return void
     * @throws Zend_Serializer_Exception If igbinary extension is not present
     */
    public function __construct($opts = [])
    {
        if (!extension_loaded('igbinary')) {
            require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception('PHP extension "igbinary" is required for this adapter');
        }

        parent::__construct($opts);

        if (self::$_serializedNull === null) {
            self::$_serializedNull = igbinary_serialize(null);
        }
    }

    /**
     * Serialize PHP value to igbinary
     *
     * @param  mixed $value
     * @param  array $opts
     * @return string
     * @throws Zend_Serializer_Exception on igbinary error
     */
    public function serialize($value, array $opts = [])
    {
        $error = null;
        $ret = $this->_call('igbinary_serialize', $value, $error);
        if ($ret === false) {
            require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception($error ?? 'Serialization with igbinary failed');
        }
        return $ret;
    }

    /**
     * Deserialize igbinary string to PHP value
     *
     * @param  string $serialized
     * @param  array $opts
     * @return mixed
     * @throws Zend_Serializer_Exception on igbinary error
     */
    public function unserialize($serialized, array $opts = [])
    {
        $error = null;
        $ret = $this->_call('igbinary_unserialize', $serialized, $error);
        if ($ret === null && $serialized !== self::$_serializedNull) {
            require_once 'Zend/Serializer/Exception.php';
            throw new Zend_Serializer_Exception($error ?? 'Unserialization with igbinary failed');
        }
        return $ret;
    }

    /**
     * Calls an igbinary function and records its warning instead of emitting it
     *
     * @param  string      $function
     * @param  mixed       $value
     * @param  string|null $error the message of the last warning, null without one
     * @return mixed
     */
    protected function _call($function, $value, &$error)
    {
        set_error_handler(function ($errno, $errstr) use (&$error) {
            $error = $errstr;
            return true;
        }, E_WARNING | E_NOTICE);
        try {
            return $function($value);
        } finally {
            restore_error_handler();
        }
    }
}
