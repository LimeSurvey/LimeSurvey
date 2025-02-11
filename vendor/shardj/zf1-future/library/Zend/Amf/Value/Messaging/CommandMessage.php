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

/**
 * @see Zend_Amf_Value_Messaging_AsyncMessage
 */
require_once 'Zend/Amf/Value/Messaging/AsyncMessage.php';

/**
 * A message that represents an infrastructure command passed between
 * client and server. Subscribe/unsubscribe operations result in
 * CommandMessage transmissions, as do polling operations.
 *
 * Corresponds to flex.messaging.messages.CommandMessage
 *
 * Note: THESE VALUES MUST BE THE SAME ON CLIENT AND SERVER
 *
 * @package    Zend_Amf
 * @subpackage Value
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Amf_Value_Messaging_CommandMessage extends Zend_Amf_Value_Messaging_AsyncMessage
{
    /**
     *  This operation is used to subscribe to a remote destination.
     */
    public const SUBSCRIBE_OPERATION = 0;

    /**
     * This operation is used to unsubscribe from a remote destination.
     */
    public const UNSUSBSCRIBE_OPERATION = 1;

    /**
     * This operation is used to poll a remote destination for pending,
     * undelivered messages.
     */
    public const POLL_OPERATION = 2;

    /**
     * This operation is used by a remote destination to sync missed or cached messages
     * back to a client as a result of a client issued poll command.
     */
    public const CLIENT_SYNC_OPERATION = 4;

    /**
     * This operation is used to test connectivity over the current channel to
     * the remote endpoint.
     */
    public const CLIENT_PING_OPERATION = 5;

    /**
     * This operation is used to request a list of failover endpoint URIs
     * for the remote destination based on cluster membership.
     */
    public const CLUSTER_REQUEST_OPERATION = 7;

    /**
     * This operation is used to send credentials to the endpoint so that
     * the user can be logged in over the current channel.
     * The credentials need to be Base64 encoded and stored in the <code>body</code>
     * of the message.
     */
    public const LOGIN_OPERATION = 8;

    /**
     * This operation is used to log the user out of the current channel, and
     * will invalidate the server session if the channel is HTTP based.
     */
    public const LOGOUT_OPERATION = 9;

    /**
     * This operation is used to indicate that the client's subscription to a
     * remote destination has been invalidated.
     */
    public const SESSION_INVALIDATE_OPERATION = 10;

    /**
     * This operation is used by the MultiTopicConsumer to subscribe/unsubscribe
     * from multiple subtopics/selectors in the same message.
     */
    public const MULTI_SUBSCRIBE_OPERATION = 11;

    /**
     * This operation is used to indicate that a channel has disconnected
     */
    public const DISCONNECT_OPERATION = 12;

    /**
     * This is the default operation for new CommandMessage instances.
     */
    public const UNKNOWN_OPERATION = 10000;

    /**
     * The operation to execute for messages of this type
     * @var int
     */
    public $operation = self::UNKNOWN_OPERATION;
}
