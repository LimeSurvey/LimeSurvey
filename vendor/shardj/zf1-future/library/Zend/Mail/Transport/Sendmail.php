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
 * @subpackage Transport
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @see Zend_Mail_Transport_Abstract
 */
require_once 'Zend/Mail/Transport/Abstract.php';


/**
 * Class for sending eMails via the PHP internal mail() function
 *
 * @category   Zend
 * @package    Zend_Mail
 * @subpackage Transport
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Mail_Transport_Sendmail extends Zend_Mail_Transport_Abstract
{
    /**
     * Subject
     * @var string
     * @access public
     */
    public $subject = null;


    /**
     * Config options for sendmail parameters
     *
     * @var string
     */
    public $parameters;

    /**
     * EOL character string
     * @var string
     * @access public
     */
    public $EOL = "\r\n";

    /**
     * error information
     * @var string
     */
    protected $_errstr;

    /**
     * Constructor.
     *
     * @param  string|array|Zend_Config $parameters OPTIONAL (Default: null)
     * @return void
     */
    public function __construct($parameters = null)
    {
        if ($parameters instanceof Zend_Config) {
            $parameters = $parameters->toArray();
        }

        if (is_array($parameters)) {
            $parameters = implode(' ', $parameters);
        }

        $this->parameters = $parameters;
    }


    /**
     * Send mail using PHP native mail()
     *
     * @access public
     * @return void
     * @throws Zend_Mail_Transport_Exception if parameters is set
     *         but not a string
     * @throws Zend_Mail_Transport_Exception on mail() failure
     */
    public function _sendMail()
    {
        $recipients = $this->recipients;
        $subject = $this->_mail->getSubject();
        $body = $this->body;
        $header = $this->header;
        $isWindowsOs = strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';
        if (PHP_VERSION_ID < 80000 && !$isWindowsOs) {
            $recipients = str_replace("\r\n", "\n", $recipients);
            $subject = str_replace("\r\n", "\n", $subject);
            $body = str_replace("\r\n", "\n", $body);
            $header = str_replace("\r\n", "\n", $header);
        }

        if ($this->parameters === null) {
            set_error_handler([$this, '_handleMailErrors']);
            $result = mail(
                $recipients,
                $subject,
                $body,
                $header);
            restore_error_handler();
        } else {
            if(!is_string($this->parameters)) {
                /**
                 * @see Zend_Mail_Transport_Exception
                 *
                 * Exception is thrown here because
                 * $parameters is a public property
                 */
                require_once 'Zend/Mail/Transport/Exception.php';
                throw new Zend_Mail_Transport_Exception(
                    'Parameters were set but are not a string'
                );
            }

            $fromEmailHeader = str_replace(' ', '', $this->parameters);
            if (PHP_VERSION_ID < 80000 && !$isWindowsOs) {
                $fromEmailHeader = str_replace("\r\n", "\n", $fromEmailHeader);
            }
            // Sanitize the From header
            // https://github.com/Shardj/zf1-future/issues/326
            
            if ( empty($fromEmailHeader) === TRUE ) { // nothing to worry about
                goto processMail;
            }
            
            // now we use 2 different approaches, based ond the usage context            
            if( substr( $fromEmailHeader, 0, 2 ) === '-f' ) {
                
                if(substr_count($fromEmailHeader, '"') >2) { // we are considering just usage of double-quotes
                    throw new Zend_Mail_Transport_Exception('Potential code injection in From header');
                }
  
            } else { // full email validation
                
                if( Zend_Validate::is($fromEmailHeader, 'EmailAddress') === FALSE ) {
                    throw new Zend_Mail_Transport_Exception('Potential code injection in From header');
                }                
            }            
            
            processMail:
            
                set_error_handler([$this, '_handleMailErrors']);
                $result = mail(
                    $recipients,
                    $subject,
                    $body,
                    $header,
                    $fromEmailHeader);
                restore_error_handler();

        }

        if ($this->_errstr !== null || !$result) {
            /**
             * @see Zend_Mail_Transport_Exception
             */
            require_once 'Zend/Mail/Transport/Exception.php';
            throw new Zend_Mail_Transport_Exception('Unable to send mail. ' . $this->_errstr);
        }
    }


    /**
     * Format and fix headers
     *
     * mail() uses its $to and $subject arguments to set the To: and Subject:
     * headers, respectively. This method strips those out as a sanity check to
     * prevent duplicate header entries.
     *
     * @access  protected
     * @param   array $headers
     * @return  void
     * @throws  Zend_Mail_Transport_Exception
     */
    protected function _prepareHeaders($headers)
    {
        if (!$this->_mail) {
            /**
             * @see Zend_Mail_Transport_Exception
             */
            require_once 'Zend/Mail/Transport/Exception.php';
            throw new Zend_Mail_Transport_Exception('_prepareHeaders requires a registered Zend_Mail object');
        }

        // mail() uses its $to parameter to set the To: header, and the $subject
        // parameter to set the Subject: header. We need to strip them out.
        if (0 === strpos(PHP_OS, 'WIN')) {
            // If the current recipients list is empty, throw an error
            if (empty($this->recipients)) {
                /**
                 * @see Zend_Mail_Transport_Exception
                 */
                require_once 'Zend/Mail/Transport/Exception.php';
                throw new Zend_Mail_Transport_Exception('Missing To addresses');
            }
        } else {
            // All others, simply grab the recipients and unset the To: header
            if (!isset($headers['To'])) {
                /**
                 * @see Zend_Mail_Transport_Exception
                 */
                require_once 'Zend/Mail/Transport/Exception.php';
                throw new Zend_Mail_Transport_Exception('Missing To header');
            }

            unset($headers['To']['append']);
            $this->recipients = implode(',', $headers['To']);
        }

        // Remove recipient header
        unset($headers['To']);

        // Remove subject header, if present
        if (isset($headers['Subject'])) {
            unset($headers['Subject']);
        }

        // Prepare headers
        parent::_prepareHeaders($headers);

        // Fix issue with empty blank line ontop when using Sendmail Trnasport
        $this->header = rtrim($this->header);
    }

    /**
     * Temporary error handler for PHP native mail().
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param string $errline
     * @param array  $errcontext
     * @return true
     */
    public function _handleMailErrors($errno, $errstr, $errfile = null, $errline = null, ?array $errcontext = null)
    {
        $this->_errstr = $errstr;
        return true;
    }

}
