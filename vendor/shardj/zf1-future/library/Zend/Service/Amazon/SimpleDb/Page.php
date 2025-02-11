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
 * @package    Zend_Service_Amazon
 * @subpackage SimpleDb
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Service_Amazon_Exception
 */
require_once 'Zend/Service/Amazon/Exception.php';

/**
 * The Custom Exception class that allows you to have access to the AWS Error Code.
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage SimpleDb
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_SimpleDb_Page
{
    /**
     * Page data
     *
     * @var string
     */
    protected $_data;

    /**
     * Token identifying page
     *
     * @var string|null
     */
    protected $_token;

    /**
     * Constructor
     *
     * @param string      $data
     * @param string|null $token
     */
    public function __construct($data, $token = null)
    {
        $this->setData($data);
        $this->setToken($token);
    }

    /**
     * Set page data
     *
     * @param string $data
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    /**
     * Retrieve page data
     *
     * @return string
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Set token
     *
     * @param string|null $token
     */
    public function setToken($token)
    {
        $this->_token = (trim($token) === '') ? null : $token;
    }

    /**
     * Retrieve token
     *
     * @return string|null
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * Determine whether this is the last page of data
     *
     * @return bool
     */
    public function isLast()
    {
        return (null === $this->_token);
    }

    /**
     * Cast to string
     *
     * @return string
     */
    public function __toString()
    {
        return "Page with token: " . $this->_token
             . "\n and data: " . $this->_data;
    }
}
