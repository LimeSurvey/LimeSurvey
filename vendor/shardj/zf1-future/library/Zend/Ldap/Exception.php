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
 * @package    Zend_Ldap
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Exception
 */
require_once 'Zend/Exception.php';

/**
 * @category   Zend
 * @package    Zend_Ldap
 * @uses       Zend_Exception
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Exception extends Zend_Exception
{
    public const LDAP_SUCCESS                        = 0x00;
    public const LDAP_OPERATIONS_ERROR               = 0x01;
    public const LDAP_PROTOCOL_ERROR                 = 0x02;
    public const LDAP_TIMELIMIT_EXCEEDED             = 0x03;
    public const LDAP_SIZELIMIT_EXCEEDED             = 0x04;
    public const LDAP_COMPARE_FALSE                  = 0x05;
    public const LDAP_COMPARE_TRUE                   = 0x06;
    public const LDAP_AUTH_METHOD_NOT_SUPPORTED      = 0x07;
    public const LDAP_STRONG_AUTH_REQUIRED           = 0x08;
    public const LDAP_PARTIAL_RESULTS                = 0x09;
    public const LDAP_REFERRAL                       = 0x0a;
    public const LDAP_ADMINLIMIT_EXCEEDED            = 0x0b;
    public const LDAP_UNAVAILABLE_CRITICAL_EXTENSION = 0x0c;
    public const LDAP_CONFIDENTIALITY_REQUIRED       = 0x0d;
    public const LDAP_SASL_BIND_IN_PROGRESS          = 0x0e;
    public const LDAP_NO_SUCH_ATTRIBUTE              = 0x10;
    public const LDAP_UNDEFINED_TYPE                 = 0x11;
    public const LDAP_INAPPROPRIATE_MATCHING         = 0x12;
    public const LDAP_CONSTRAINT_VIOLATION           = 0x13;
    public const LDAP_TYPE_OR_VALUE_EXISTS           = 0x14;
    public const LDAP_INVALID_SYNTAX                 = 0x15;
    public const LDAP_NO_SUCH_OBJECT                 = 0x20;
    public const LDAP_ALIAS_PROBLEM                  = 0x21;
    public const LDAP_INVALID_DN_SYNTAX              = 0x22;
    public const LDAP_IS_LEAF                        = 0x23;
    public const LDAP_ALIAS_DEREF_PROBLEM            = 0x24;
    public const LDAP_PROXY_AUTHZ_FAILURE            = 0x2F;
    public const LDAP_INAPPROPRIATE_AUTH             = 0x30;
    public const LDAP_INVALID_CREDENTIALS            = 0x31;
    public const LDAP_INSUFFICIENT_ACCESS            = 0x32;
    public const LDAP_BUSY                           = 0x33;
    public const LDAP_UNAVAILABLE                    = 0x34;
    public const LDAP_UNWILLING_TO_PERFORM           = 0x35;
    public const LDAP_LOOP_DETECT                    = 0x36;
    public const LDAP_NAMING_VIOLATION               = 0x40;
    public const LDAP_OBJECT_CLASS_VIOLATION         = 0x41;
    public const LDAP_NOT_ALLOWED_ON_NONLEAF         = 0x42;
    public const LDAP_NOT_ALLOWED_ON_RDN             = 0x43;
    public const LDAP_ALREADY_EXISTS                 = 0x44;
    public const LDAP_NO_OBJECT_CLASS_MODS           = 0x45;
    public const LDAP_RESULTS_TOO_LARGE              = 0x46;
    public const LDAP_AFFECTS_MULTIPLE_DSAS          = 0x47;
    public const LDAP_OTHER                          = 0x50;
    public const LDAP_SERVER_DOWN                    = 0x51;
    public const LDAP_LOCAL_ERROR                    = 0x52;
    public const LDAP_ENCODING_ERROR                 = 0x53;
    public const LDAP_DECODING_ERROR                 = 0x54;
    public const LDAP_TIMEOUT                        = 0x55;
    public const LDAP_AUTH_UNKNOWN                   = 0x56;
    public const LDAP_FILTER_ERROR                   = 0x57;
    public const LDAP_USER_CANCELLED                 = 0x58;
    public const LDAP_PARAM_ERROR                    = 0x59;
    public const LDAP_NO_MEMORY                      = 0x5a;
    public const LDAP_CONNECT_ERROR                  = 0x5b;
    public const LDAP_NOT_SUPPORTED                  = 0x5c;
    public const LDAP_CONTROL_NOT_FOUND              = 0x5d;
    public const LDAP_NO_RESULTS_RETURNED            = 0x5e;
    public const LDAP_MORE_RESULTS_TO_RETURN         = 0x5f;
    public const LDAP_CLIENT_LOOP                    = 0x60;
    public const LDAP_REFERRAL_LIMIT_EXCEEDED        = 0x61;
    public const LDAP_CUP_RESOURCES_EXHAUSTED        = 0x71;
    public const LDAP_CUP_SECURITY_VIOLATION         = 0x72;
    public const LDAP_CUP_INVALID_DATA               = 0x73;
    public const LDAP_CUP_UNSUPPORTED_SCHEME         = 0x74;
    public const LDAP_CUP_RELOAD_REQUIRED            = 0x75;
    public const LDAP_CANCELLED                      = 0x76;
    public const LDAP_NO_SUCH_OPERATION              = 0x77;
    public const LDAP_TOO_LATE                       = 0x78;
    public const LDAP_CANNOT_CANCEL                  = 0x79;
    public const LDAP_ASSERTION_FAILED               = 0x7A;
    public const LDAP_SYNC_REFRESH_REQUIRED          = 0x1000;
    public const LDAP_X_SYNC_REFRESH_REQUIRED        = 0x4100;
    public const LDAP_X_NO_OPERATION                 = 0x410e;
    public const LDAP_X_ASSERTION_FAILED             = 0x410f;
    public const LDAP_X_NO_REFERRALS_FOUND           = 0x4110;
    public const LDAP_X_CANNOT_CHAIN                 = 0x4111;

    /* internal error code constants */

    public const LDAP_X_DOMAIN_MISMATCH              = 0x7001;
    public const LDAP_X_EXTENSION_NOT_LOADED         = 0x7002;

    /**
     * @param Zend_Ldap $ldap A Zend_Ldap object
     * @param string    $str  An informtive exception message
     * @param int       $code An LDAP error code
     */
    public function __construct(?Zend_Ldap $ldap = null, $str = null, $code = 0)
    {
        $errorMessages = [];
        $message = '';

        if ($ldap !== null) {
            $oldCode = $code;
            $message = $ldap->getLastError($code, $errorMessages) . ': ';

            if ($code === 0) {
                $message = '';
                $code = $oldCode;
            }
        }

        if (empty($message) && $code > 0) {
            $message = '0x' . dechex($code) . ': ';
        }

        if (!empty($str)) {
            $message .= $str;
        } else {
            $message .= 'no exception message';
        }

        parent::__construct($message, $code);
    }


    /**
     * @deprecated not necessary any more - will be removed
     * @param Zend_Ldap $ldap A Zend_Ldap object
     * @return int The current error code for the resource
     */
    public static function getLdapCode(?Zend_Ldap $ldap = null)
    {
        if ($ldap !== null) {
            return $ldap->getLastErrorCode();
        }
        return 0;
    }

    /**
     * @deprecated will be removed
     * @return int The current error code for this exception
     */
    public function getErrorCode()
    {
        return $this->getCode();
    }
}
