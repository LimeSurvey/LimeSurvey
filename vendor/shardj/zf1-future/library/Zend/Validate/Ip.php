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
class Zend_Validate_Ip extends Zend_Validate_Abstract
{
    public const INVALID        = 'ipInvalid';
    public const NOT_IP_ADDRESS = 'notIpAddress';

    /**
     * @var array
     */
    protected $_messageTemplates = [
        self::INVALID        => "Invalid type given. String expected",
        self::NOT_IP_ADDRESS => "'%value%' does not appear to be a valid IP address",
    ];

    /**
     * internal options
     *
     * @var array
     */
    protected $_options = [
        'allowipv6' => true,
        'allowipv4' => true
    ];

    /**
     * Sets validator options
     *
     * @param array $options OPTIONAL Options to set, see the manual for all available options
     */
    public function __construct($options = [])
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            $temp['allowipv6'] = array_shift($options);
            if (!empty($options)) {
                $temp['allowipv4'] = array_shift($options);
            }

            $options = $temp;
        }

        $options += $this->_options;
        $this->setOptions($options);
    }

    /**
     * Returns all set options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Sets the options for this validator
     *
     * @param array $options
     * @throws Zend_Validate_Exception
     * @return Zend_Validate_Ip
     */
    public function setOptions($options)
    {
        if (array_key_exists('allowipv6', $options)) {
            $this->_options['allowipv6'] = (boolean) $options['allowipv6'];
        }

        if (array_key_exists('allowipv4', $options)) {
            $this->_options['allowipv4'] = (boolean) $options['allowipv4'];
        }

        if (!$this->_options['allowipv4'] && !$this->_options['allowipv6']) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Nothing to validate. Check your options');
        }

        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is a valid IP address
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        if (!is_string($value)) {
            $this->_error(self::INVALID);
            return false;
        }

        $this->_setValue($value);
        if (($this->_options['allowipv4'] && !$this->_options['allowipv6'] && !$this->_validateIPv4($value)) ||
            (!$this->_options['allowipv4'] && $this->_options['allowipv6'] && !$this->_validateIPv6($value)) ||
            ($this->_options['allowipv4'] && $this->_options['allowipv6'] && !$this->_validateIPv4($value) && !$this->_validateIPv6($value))) {
            $this->_error(self::NOT_IP_ADDRESS);
            return false;
        }

        return true;
    }

    /**
     * Validates an IPv4 address
     *
     * @param string $value
     * @return bool
     */
    protected function _validateIPv4($value) {
        $ip2long = ip2long($value);
        if($ip2long === false) {
            return false;
        }

        return $value == long2ip($ip2long);
    }

    /**
     * Validates an IPv6 address
     *
     * @param  string $value Value to check against
     * @return boolean True when $value is a valid ipv6 address
     *                 False otherwise
     */
    protected function _validateIPv6($value) {
        if (strlen($value) < 3) {
            return $value == '::';
        }

        if (strpos($value, '.')) {
            $lastcolon = strrpos($value, ':');
            if (!($lastcolon && $this->_validateIPv4(substr($value, $lastcolon + 1)))) {
                return false;
            }

            $value = substr($value, 0, $lastcolon) . ':0:0';
        }

        if (strpos($value, '::') === false) {
            return preg_match('/\A(?:[a-f0-9]{1,4}:){7}[a-f0-9]{1,4}\z/i', $value);
        }

        $colonCount = substr_count($value, ':');

        if ($colonCount < 8) {
            return preg_match('/\A(?::|(?:[a-f0-9]{1,4}:)+):(?:(?:[a-f0-9]{1,4}:)*[a-f0-9]{1,4})?\z/i', $value);
        }

        // special case with ending or starting double colon
        if ($colonCount === 8) {
            return preg_match('/\A(?:::)?(?:[a-f0-9]{1,4}:){6}[a-f0-9]{1,4}(?:::)?\z/i', $value);
        }

        return false;
    }
}
