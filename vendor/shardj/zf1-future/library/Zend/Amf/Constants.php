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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * The following constants are used throughout serialization and
 * deserialization to detect the AMF marker and encoding types.
 *
 * @package    Zend_Amf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
final class Zend_Amf_Constants
{
    public const AMF0_NUMBER            = 0x00;
    public const AMF0_BOOLEAN           = 0x01;
    public const AMF0_STRING            = 0x02;
    public const AMF0_OBJECT            = 0x03;
    public const AMF0_MOVIECLIP         = 0x04;
    public const AMF0_NULL              = 0x05;
    public const AMF0_UNDEFINED         = 0x06;
    public const AMF0_REFERENCE         = 0x07;
    public const AMF0_MIXEDARRAY        = 0x08;
    public const AMF0_OBJECTTERM        = 0x09;
    public const AMF0_ARRAY             = 0x0a;
    public const AMF0_DATE              = 0x0b;
    public const AMF0_LONGSTRING        = 0x0c;
    public const AMF0_UNSUPPORTED       = 0x0e;
    public const AMF0_XML               = 0x0f;
    public const AMF0_TYPEDOBJECT       = 0x10;
    public const AMF0_AMF3              = 0x11;
    public const AMF0_OBJECT_ENCODING   = 0x00;

    public const AMF3_UNDEFINED         = 0x00;
    public const AMF3_NULL              = 0x01;
    public const AMF3_BOOLEAN_FALSE     = 0x02;
    public const AMF3_BOOLEAN_TRUE      = 0x03;
    public const AMF3_INTEGER           = 0x04;
    public const AMF3_NUMBER            = 0x05;
    public const AMF3_STRING            = 0x06;
    public const AMF3_XML               = 0x07;
    public const AMF3_DATE              = 0x08;
    public const AMF3_ARRAY             = 0x09;
    public const AMF3_OBJECT            = 0x0A;
    public const AMF3_XMLSTRING         = 0x0B;
    public const AMF3_BYTEARRAY         = 0x0C;
    public const AMF3_OBJECT_ENCODING   = 0x03;

    // Object encodings for AMF3 object types
    public const ET_PROPLIST            = 0x00;
    public const ET_EXTERNAL            = 0x01;
    public const ET_DYNAMIC             = 0x02;
    public const ET_PROXY               = 0x03;

    public const FMS_OBJECT_ENCODING    = 0x01;

    /**
     * Special content length value that indicates "unknown" content length
     * per AMF Specification
     */
    public const UNKNOWN_CONTENT_LENGTH = -1;
    public const URL_APPEND_HEADER      = 'AppendToGatewayUrl';
    public const RESULT_METHOD          = '/onResult';
    public const STATUS_METHOD          = '/onStatus';
    public const CREDENTIALS_HEADER     = 'Credentials';
    public const PERSISTENT_HEADER      = 'RequestPersistentHeader';
    public const DESCRIBE_HEADER        = 'DescribeService';

    public const GUEST_ROLE             = 'anonymous';
}
