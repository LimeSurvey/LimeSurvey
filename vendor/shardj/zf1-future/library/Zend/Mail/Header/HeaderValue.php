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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @category   Zend
 * @package    Zend_Mail
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
final class Zend_Mail_Header_HeaderValue
{
    /**
     * No public constructor.
     */
    private function __construct()
    {
    }

    /**
     * Filter the header value according to RFC 2822
     *
     * @see    http://www.rfc-base.org/txt/rfc-2822.txt (section 2.2)
     * @param  string $value
     * @return string
     */
    public static function filter($value)
    {
        $result = '';
        $tot    = strlen($value);

        // Filter for CR and LF characters, leaving CRLF + WSP sequences for
        // Long Header Fields (section 2.2.3 of RFC 2822)
        for ($i = 0; $i < $tot; $i += 1) {
            $ord = ord($value[$i]);

            if (($ord < 32 || $ord > 126)
                && $ord !== 13
            ) {
                continue;
            }

            if ($ord === 13) {
                if ($i + 2 >= $tot) {
                    continue;
                }

                $lf = ord($value[$i + 1]);
                $sp = ord($value[$i + 2]);

                if ($lf !== 10 || $sp !== 32) {
                    continue;
                }

                $result .= "\r\n ";
                $i += 2;
                continue;
            }

            $result .= $value[$i];
        }

        return $result;
    }

    /**
     * Determine if the header value contains any invalid characters.
     *
     * @see    rfc-6532
     * @param string $value
     * @return bool
     */
    public static function isValid($value)
    {
        $tot = strlen($value);
        $i = 0;

        while ($i < $tot) {
            $ord = ord($value[$i]);

            // Check for control characters (CR and LF) as per RFC 6532
            if ($ord === 13) { // Carriage Return (CR)
                if ($i + 1 >= $tot || ord($value[$i + 1]) !== 10) { // Must be followed by Line Feed (LF)
                    return false;
                }
                $i += 2; // Skip the CRLF sequence
                continue;
            } elseif ($ord === 10) { // Standalone Line Feed (LF) is not allowed
                return false;
            }

            // Validate that the character is a valid UTF-8 character
            if (!self::isUtf8Character($value, $i, $tot)) {
                return false;
            }
        }

        return true;
    }

    private static function isUtf8Character($value, &$i, $tot)
    {
        $byte = ord($value[$i]);

        // ASCII character (1 byte): 0x00 - 0x7F
        if ($byte >= 0x00 && $byte <= 0x7F) {
            $i++;
            return true;
        }

        // Determine the number of bytes in the UTF-8 character
        $numBytes = 0;

        if ($byte >= 0xC2 && $byte <= 0xDF) { // 2-byte sequence
            $numBytes = 2;
        } elseif ($byte >= 0xE0 && $byte <= 0xEF) { // 3-byte sequence
            $numBytes = 3;
        } elseif ($byte >= 0xF0 && $byte <= 0xF4) { // 4-byte sequence
            $numBytes = 4;
        } else {
            return false; // Invalid byte for UTF-8
        }

        // Validate the next numBytes - 1 bytes (must be of the format 10xxxxxx)
        for ($j = 1; $j < $numBytes; $j++) {
            if ($i + $j >= $tot || (ord($value[$i + $j]) & 0xC0) !== 0x80) {
                return false; // Invalid UTF-8 sequence
            }
        }

        $i += $numBytes; // Move the pointer forward by the number of bytes
        return true;
    }

    /**
     * Assert that the header value is valid.
     *
     * Raises an exception if invalid.
     *
     * @param string $value
     * @throws Exception\RuntimeException
     * @return void
     */
    public static function assertValid($value)
    {
        if (! self::isValid($value)) {
            require_once 'Zend/Mail/Exception.php';
            throw new Zend_Mail_Exception('Invalid header value detected');
        }
    }
}
