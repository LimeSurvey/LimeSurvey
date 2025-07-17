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
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Regex extends Zend_Validate_Abstract
{
    public const INVALID   = 'regexInvalid';
    public const NOT_MATCH = 'regexNotMatch';
    public const ERROROUS  = 'regexErrorous';

    /**
     * @var array
     */
    protected $_messageTemplates = [
        self::INVALID   => "Invalid type given. String, integer or float expected",
        self::NOT_MATCH => "'%value%' does not match against pattern '%pattern%'",
        self::ERROROUS  => "There was an internal error while using the pattern '%pattern%'",
    ];

    /**
     * @var array
     */
    protected $_messageVariables = [
        'pattern' => '_pattern'
    ];

    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected $_pattern;

    /**
     * Sets validator options
     *
     * @param  string|array|Zend_Config $pattern
     * @throws Zend_Validate_Exception On missing 'pattern' parameter
     */
    public function __construct($pattern)
    {
        if ($pattern instanceof Zend_Config) {
            $pattern = $pattern->toArray();
        }

        if (is_array($pattern)) {
            if (array_key_exists('pattern', $pattern)) {
                $pattern = $pattern['pattern'];
            } else {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("Missing option 'pattern'");
            }
        }

        $this->setPattern($pattern);
    }

    /**
     * Returns the pattern option
     *
     * @return string
     */
    public function getPattern()
    {
        return $this->_pattern;
    }

    /**
     * Sets the pattern option
     *
     * @param  string $pattern
     * @throws Zend_Validate_Exception if there is a fatal error in pattern matching
     * @return $this
     */
    public function setPattern($pattern)
    {
        $this->_pattern = (string) $pattern;
        $status         = @preg_match($this->_pattern, "Test");

        if (false === $status) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Internal error while using the pattern '$this->_pattern'");
        }

        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value matches against the pattern option
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);

        $status = @preg_match($this->_pattern, $value);
        if (false === $status) {
            $this->_error(self::ERROROUS);
            return false;
        }

        if (!$status) {
            $this->_error(self::NOT_MATCH);
            return false;
        }

        return true;
    }
}
