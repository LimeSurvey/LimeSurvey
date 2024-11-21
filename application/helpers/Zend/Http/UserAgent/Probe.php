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
 * @package    Zend_Http
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Http/UserAgent/AbstractDevice.php';

/**
 * Probe browser type matcher
 *
 * @category   Zend
 * @package    Zend_Http
 * @subpackage UserAgent
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Http_UserAgent_Probe extends Zend_Http_UserAgent_AbstractDevice
{
    /**
     * User Agent Signatures
     *
     * @var array
     */
    protected static $_uaSignatures = array(
        'witbe',
        'netvigie',
    );

    /**
     * Comparison of the UserAgent chain and User Agent signatures
     *
     * @param string $userAgent User Agent chain
     * @param  array $server $_SERVER like param
     * @return bool
     */
    public static function match($userAgent, $server)
    {
        return self::_matchAgentAgainstSignatures($userAgent, self::$_uaSignatures);
    }


    /**
     * Gives the current browser type
     *
     * @return string
     */
    public function getType()
    {
        return 'probe';
    }

    /**
     * Look for features
     *
     * @return string
     */
    protected function _defineFeatures()
    {
        $this->setFeature('images', false, 'product_capability');
        $this->setFeature('iframes', false, 'product_capability');
        $this->setFeature('frames', false, 'product_capability');
        $this->setFeature('javascript', false, 'product_capability');
        return parent::_defineFeatures();
    }
}
