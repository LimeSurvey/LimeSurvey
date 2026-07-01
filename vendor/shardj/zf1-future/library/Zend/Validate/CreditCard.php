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
class Zend_Validate_CreditCard extends Zend_Validate_Abstract
{
    /**
     * Detected CCI list
     *
     * @var string
     */
    public const ALL              = 'All';
    public const AMERICAN_EXPRESS = 'American_Express';
    public const UNIONPAY         = 'Unionpay';
    public const DINERS_CLUB      = 'Diners_Club';
    public const DINERS_CLUB_US   = 'Diners_Club_US';
    public const DISCOVER         = 'Discover';
    public const JCB              = 'JCB';
    public const LASER            = 'Laser';
    public const MAESTRO          = 'Maestro';
    public const MASTERCARD       = 'Mastercard';
    public const SOLO             = 'Solo';
    public const VISA             = 'Visa';

    public const CHECKSUM       = 'creditcardChecksum';
    public const CONTENT        = 'creditcardContent';
    public const INVALID        = 'creditcardInvalid';
    public const LENGTH         = 'creditcardLength';
    public const PREFIX         = 'creditcardPrefix';
    public const SERVICE        = 'creditcardService';
    public const SERVICEFAILURE = 'creditcardServiceFailure';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [
        self::CHECKSUM       => "'%value%' seems to contain an invalid checksum",
        self::CONTENT        => "'%value%' must contain only digits",
        self::INVALID        => "Invalid type given. String expected",
        self::LENGTH         => "'%value%' contains an invalid amount of digits",
        self::PREFIX         => "'%value%' is not from an allowed institute",
        self::SERVICE        => "'%value%' seems to be an invalid creditcard number",
        self::SERVICEFAILURE => "An exception has been raised while validating '%value%'",
    ];

    /**
     * List of allowed CCV lengths
     *
     * @var array
     */
    protected $_cardLength = [
        self::AMERICAN_EXPRESS => [15],
        self::DINERS_CLUB      => [14],
        self::DINERS_CLUB_US   => [16],
        self::DISCOVER         => [16],
        self::JCB              => [16],
        self::LASER            => [16, 17, 18, 19],
        self::MAESTRO          => [12, 13, 14, 15, 16, 17, 18, 19],
        self::MASTERCARD       => [16],
        self::SOLO             => [16, 18, 19],
        self::UNIONPAY         => [16, 17, 18, 19],
        self::VISA             => [16],
    ];

    /**
     * List of accepted CCV provider tags
     *
     * @var array
     */
    protected $_cardType = [
        self::AMERICAN_EXPRESS => ['34', '37'],
        self::DINERS_CLUB      => ['300', '301', '302', '303', '304', '305', '36'],
        self::DINERS_CLUB_US   => ['54', '55'],
        self::DISCOVER         => ['6011', '622126', '622127', '622128', '622129', '62213',
                                        '62214', '62215', '62216', '62217', '62218', '62219',
                                        '6222', '6223', '6224', '6225', '6226', '6227', '6228',
                                        '62290', '62291', '622920', '622921', '622922', '622923',
                                        '622924', '622925', '644', '645', '646', '647', '648',
                                        '649', '65'],
        self::JCB              => ['3528', '3529', '353', '354', '355', '356', '357', '358'],
        self::LASER            => ['6304', '6706', '6771', '6709'],
        self::MAESTRO          => ['5018', '5020', '5038', '6304', '6759', '6761', '6763'],
        self::MASTERCARD       => ['51', '52', '53', '54', '55', '2221', '2222', '2223',
	                                '2224', '2225', '2226', '2227', '2228', '2229', '223',
					'224', '225', '226', '227', '228', '229', '23', '24',
					'25', '26', '271', '2720'],
        self::SOLO             => ['6334', '6767'],
        self::UNIONPAY         => ['622126', '622127', '622128', '622129', '62213', '62214',
                                        '62215', '62216', '62217', '62218', '62219', '6222', '6223',
                                        '6224', '6225', '6226', '6227', '6228', '62290', '62291',
                                        '622920', '622921', '622922', '622923', '622924', '622925'],
        self::VISA             => ['4'],
    ];

    /**
     * CCIs which are accepted by validation
     *
     * @var array
     */
    protected $_type = [];

    /**
     * Service callback for additional validation
     *
     * @var callback
     */
    protected $_service;

    /**
     * Constructor
     *
     * @param string|array|Zend_Config $options OPTIONAL Type of CCI to allow
     */
    public function __construct($options = [])
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (!is_array($options)) {
            $options = func_get_args();
            $temp['type'] = array_shift($options);
            if (!empty($options)) {
                $temp['service'] = array_shift($options);
            }

            $options = $temp;
        }

        if (!array_key_exists('type', $options)) {
            $options['type'] = self::ALL;
        }

        $this->setType($options['type']);
        if (array_key_exists('service', $options)) {
            $this->setService($options['service']);
        }
    }

    /**
     * Returns a list of accepted CCIs
     *
     * @return array
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Sets CCIs which are accepted by validation
     *
     * @param string|array $type Type to allow for validation
     * @return $this
     */
    public function setType($type)
    {
        $this->_type = [];
        return $this->addType($type);
    }

    /**
     * Adds a CCI to be accepted by validation
     *
     * @param string|array $type Type to allow for validation
     * @return $this
     */
    public function addType($type)
    {
        if (is_string($type)) {
            $type = [$type];
        }

        foreach($type as $typ) {
            if (defined('self::' . strtoupper($typ)) && !in_array($typ, $this->_type)) {
                $this->_type[] = $typ;
            }

            if (($typ == self::ALL)) {
                $this->_type = array_keys($this->_cardLength);
            }
        }

        return $this;
    }

    /**
     * Returns the actual set service
     *
     * @return callback
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * Sets a new callback for service validation
     *
     * @param mixed $service
     * @throws Zend_Validate_Exception
     * @return $this
     */
    public function setService($service)
    {
        if (!is_callable($service)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Invalid callback given');
        }

        $this->_service = $service;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value follows the Luhn algorithm (mod-10 checksum)
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        if (!is_string($value)) {
            $this->_error(self::INVALID, $value);
            return false;
        }

        if (!ctype_digit($value)) {
            $this->_error(self::CONTENT, $value);
            return false;
        }

        $length = strlen($value);
        $types  = $this->getType();
        $foundp = false;
        $foundl = false;
        foreach ($types as $type) {
            foreach ($this->_cardType[$type] as $prefix) {
                if (substr($value, 0, strlen($prefix)) == $prefix) {
                    $foundp = true;
                    if (in_array($length, $this->_cardLength[$type])) {
                        $foundl = true;
                        break 2;
                    }
                }
            }
        }

        if ($foundp == false){
            $this->_error(self::PREFIX, $value);
            return false;
        }

        if ($foundl == false) {
            $this->_error(self::LENGTH, $value);
            return false;
        }

        $sum    = 0;
        $weight = 2;

        for ($i = $length - 2; $i >= 0; $i--) {
            $digit = $weight * $value[$i];
            $sum += floor($digit / 10) + $digit % 10;
            $weight = $weight % 2 + 1;
        }

        if ((10 - $sum % 10) % 10 != $value[$length - 1]) {
            $this->_error(self::CHECKSUM, $value);
            return false;
        }

        if (!empty($this->_service)) {
            try {
                require_once 'Zend/Validate/Callback.php';
                $callback = new Zend_Validate_Callback($this->_service);
                $callback->setOptions($this->_type);
                if (!$callback->isValid($value)) {
                    $this->_error(self::SERVICE, $value);
                    return false;
                }
            } catch (Zend_Exception $e) {
                $this->_error(self::SERVICEFAILURE, $value);
                return false;
            }
        }

        return true;
    }
}
