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
 * @package    Zend_Amf
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Zend_Amf_Value_Messaging_AbstractMessage */
require_once 'Zend/Amf/Value/Messaging/AbstractMessage.php';

/**
 * This type of message contains information necessary to perform
 * point-to-point or publish-subscribe messaging.
 *
 * @package    Zend_Amf
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Value_Messaging_AsyncMessage extends Zend_Amf_Value_Messaging_AbstractMessage
{
    /**
     * The message id to be responded to.
     * @var String
     */
    public $correlationId;
}
