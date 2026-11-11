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
 * @package    Zend_Mail
 * @subpackage Protocol
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Mail_Protocol_Smtp
 */
require_once 'Zend/Mail/Protocol/Smtp.php';

/**
 * Performs XOAUTH2 authentication (OAuth 2.0 bearer token) with an SMTP server,
 * as used by Gmail and Microsoft 365
 *
 * The access token has to be obtained and refreshed by the application; it is
 * passed in as $config['accessToken'] together with the mailbox in
 * $config['username'].
 *
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage Protocol
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Mail_Protocol_Smtp_Auth_Xoauth2 extends Zend_Mail_Protocol_Smtp
{
    /**
     * @var string
     */
    protected $_username;

    /**
     * @var string
     */
    protected $_accessToken;

    /**
     * @param  string $host   (Default: 127.0.0.1)
     * @param  int    $port   (Default: null)
     * @param  array  $config Auth-specific parameters: username, accessToken
     */
    public function __construct($host = '127.0.0.1', $port = null, $config = null)
    {
        if (is_array($config)) {
            if (isset($config['username'])) {
                $this->_username = $config['username'];
            }
            if (isset($config['accessToken'])) {
                $this->_accessToken = $config['accessToken'];
            }
        }

        parent::__construct($host, $port, $config);
    }

    /**
     * Builds the SASL XOAUTH2 initial client response
     *
     * @param  string $username
     * @param  string $accessToken
     * @return string base64 encoded
     */
    public static function encodeCredentials($username, $accessToken)
    {
        return base64_encode('user=' . $username . "\x01auth=Bearer " . $accessToken . "\x01\x01");
    }

    /**
     * Performs the XOAUTH2 authentication
     *
     * @throws Zend_Mail_Protocol_Exception
     * @return void
     */
    public function auth()
    {
        // Ensure AUTH has not already been initiated.
        parent::auth();

        if ($this->_username === null || $this->_accessToken === null) {
            /**
             * @see Zend_Mail_Protocol_Exception
             */
            require_once 'Zend/Mail/Protocol/Exception.php';
            throw new Zend_Mail_Protocol_Exception('XOAUTH2 authentication needs the username and accessToken config');
        }

        $this->_send('AUTH XOAUTH2 ' . self::encodeCredentials($this->_username, $this->_accessToken));
        $message  = $this->_expect([235, 334]);
        $lastLine = (string) end($this->_response);

        if (strpos($lastLine, '334') === 0) {
            // The server rejected the token and sent its reason (base64 encoded JSON) in a
            // continuation; an empty line asks for the final 535 reply, which carries the error.
            $reason = base64_decode(trim($message), true);
            try {
                $this->_send('');
                $this->_expect([235]);
            } catch (Zend_Mail_Protocol_Exception $e) {
                require_once 'Zend/Mail/Protocol/Exception.php';
                throw new Zend_Mail_Protocol_Exception(
                    $e->getMessage() . ($reason !== false && $reason !== '' ? ' (' . $reason . ')' : ''),
                    $e->getCode(),
                    $e
                );
            }
        }

        $this->_auth = true;
    }
}
