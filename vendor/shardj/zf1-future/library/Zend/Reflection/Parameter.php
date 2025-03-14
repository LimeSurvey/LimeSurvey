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
 * @package    Zend_Reflection
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Reflection
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Reflection_Parameter extends ReflectionParameter
{
    /**
     * @var bool
     */
    protected $_isFromMethod = false;

    /**
     * Get declaring class reflection object
     *
     * @param  string $reflectionClass Reflection class to use
     * @return Zend_Reflection_Class
     */
    public function getDeclaringClass($reflectionClass = 'Zend_Reflection_Class'): ?\ReflectionClass
    {
        $phpReflection  = parent::getDeclaringClass();
        $zendReflection = new $reflectionClass($phpReflection->getName());
        if (!$zendReflection instanceof Zend_Reflection_Class) {
            require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('Invalid reflection class provided; must extend Zend_Reflection_Class');
        }
        unset($phpReflection);
        return $zendReflection;
    }

    /**
     * Get class reflection object
     *
     * @param  string $reflectionClass Reflection class to use
     * @return Zend_Reflection_Class
     */
    public function getClass($reflectionClass = 'Zend_Reflection_Class'): ?\ReflectionClass
    {
        if (PHP_VERSION_ID < 80000) {
            $phpReflection  = parent::getClass();
            if ($phpReflection == null) {
                return null;
            }
            $phpReflectionClassName = $phpReflection->getName();
        } else {
            if (!parent::hasType()) {
                return null;
            }
            $phpReflectionClassName = parent::getType();
        }

        $zendReflection = new $reflectionClass($phpReflectionClassName);
        if (!$zendReflection instanceof Zend_Reflection_Class) {
            require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('Invalid reflection class provided; must extend Zend_Reflection_Class');
        }
        unset($phpReflection);
        return $zendReflection;
    }

    /**
     * Get declaring function reflection object
     *
     * @param  string $reflectionClass Reflection class to use
     * @return Zend_Reflection_Function|Zend_Reflection_Method
     */
    public function getDeclaringFunction($reflectionClass = null): \ReflectionFunctionAbstract
    {
        $phpReflection = parent::getDeclaringFunction();
        if ($phpReflection instanceof ReflectionMethod) {
            $baseClass = 'Zend_Reflection_Method';
            if (null === $reflectionClass) {
                $reflectionClass = $baseClass;
            }
            $zendReflection = new $reflectionClass($this->getDeclaringClass()->getName(), $phpReflection->getName());
        } else {
            $baseClass = 'Zend_Reflection_Function';
            if (null === $reflectionClass) {
                $reflectionClass = $baseClass;
            }
            $zendReflection = new $reflectionClass($phpReflection->getName());
        }
        if (!$zendReflection instanceof $baseClass) {
            require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('Invalid reflection class provided; must extend ' . $baseClass);
        }
        unset($phpReflection);
        return $zendReflection;
    }

    /**
     * Get parameter type
     *
     * @return string
     */
    public function getType(): ?\ReflectionType
    {
        try {
            if ($docblock = $this->getDeclaringFunction()->getDocblock()) {
                $params = $docblock->getTags('param');

                if (isset($params[$this->getPosition()])) {
                    return $params[$this->getPosition()]->getType();
                }

            }
        } catch (Zend_Reflection_Exception $e) {
            if (PHP_VERSION_ID >= 80000) {
                return parent::getType();
            } else {
                throw $e;
            }
        }

        return null;
    }
}
