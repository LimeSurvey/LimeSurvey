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
 * @package    Zend_XmlRpc
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Fault.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * Zend_XmlRpc_Fault
 */
require_once 'Zend/XmlRpc/Fault.php';


/**
 * XMLRPC Server Faults
 *
 * Encapsulates an exception for use as an XMLRPC fault response. Valid
 * exception classes that may be used for generating the fault code and fault
 * string can be attached using {@link attachFaultException()}; all others use a
 * generic '404 Unknown error' response.
 *
 * You may also attach fault observers, which would allow you to monitor
 * particular fault cases; this is done via {@link attachObserver()}. Observers
 * need only implement a static 'observe' method.
 *
 * To allow method chaining, you may use the {@link getInstance()} factory
 * to instantiate a Zend_XmlRpc_Server_Fault.
 *
 * @category   Zend
 * @package    Zend_XmlRpc
 * @subpackage Server
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_XmlRpc_Server_Fault extends Zend_XmlRpc_Fault
{
    /**
     * @var Exception
     */
    protected $_exception;

    /**
     * @var array Array of exception classes that may define xmlrpc faults
     */
    protected static $_faultExceptionClasses = array('Zend_XmlRpc_Server_Exception' => true);

    /**
     * @var array Array of fault observers
     */
    protected static $_observers = array();

    /**
     * Constructor
     *
     * @param Exception $e
     * @return Zend_XmlRpc_Server_Fault
     */
    public function __construct(Exception $e)
    {
        $this->_exception = $e;
        $code             = 404;
        $message          = 'Unknown error';

        foreach (array_keys(self::$_faultExceptionClasses) as $class) {
            if ($e instanceof $class) {
                $code    = $e->getCode();
                $message = $e->getMessage();
                break;
            }
        }

        parent::__construct($code, $message);

        // Notify exception observers, if present
        if (!empty(self::$_observers)) {
            foreach (array_keys(self::$_observers) as $observer) {
                call_user_func(array($observer, 'observe'), $this);
            }
        }
    }

    /**
     * Return Zend_XmlRpc_Server_Fault instance
     *
     * @param Exception $e
     * @return Zend_XmlRpc_Server_Fault
     */
    public static function getInstance(Exception $e)
    {
        return new self($e);
    }

    /**
     * Attach valid exceptions that can be used to define xmlrpc faults
     *
     * @param string|array $classes Class name or array of class names
     * @return void
     */
    public static function attachFaultException($classes)
    {
        if (!is_array($classes)) {
            $classes = (array) $classes;
        }

        foreach ($classes as $class) {
            if (is_string($class) && class_exists($class)) {
                self::$_faultExceptionClasses[$class] = true;
            }
        }
    }

    /**
     * Detach fault exception classes
     *
     * @param string|array $classes Class name or array of class names
     * @return void
     */
    public static function detachFaultException($classes)
    {
        if (!is_array($classes)) {
            $classes = (array) $classes;
        }

        foreach ($classes as $class) {
            if (is_string($class) && isset(self::$_faultExceptionClasses[$class])) {
                unset(self::$_faultExceptionClasses[$class]);
            }
        }
    }

    /**
     * Attach an observer class
     *
     * Allows observation of xmlrpc server faults, thus allowing logging or mail
     * notification of fault responses on the xmlrpc server.
     *
     * Expects a valid class name; that class must have a public static method
     * 'observe' that accepts an exception as its sole argument.
     *
     * @param string $class
     * @return boolean
     */
    public static function attachObserver($class)
    {
        if (
            !is_string($class)
            || !class_exists($class)
            || !is_callable(array($class, 'observe'))
        ) {
            return false;
        }

        if (!isset(self::$_observers[$class])) {
            self::$_observers[$class] = true;
        }

        return true;
    }

    /**
     * Detach an observer
     *
     * @param string $class
     * @return boolean
     */
    public static function detachObserver($class)
    {
        if (!isset(self::$_observers[$class])) {
            return false;
        }

        unset(self::$_observers[$class]);
        return true;
    }

    /**
     * Retrieve the exception
     *
     * @access public
     * @return Exception
     */
    public function getException()
    {
        return $this->_exception;
    }
}
