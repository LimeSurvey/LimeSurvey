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
 * @package    Zend_Mime
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Support class for MultiPart Mime Messages
 *
 * @category   Zend
 * @package    Zend_Mime
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Mime
{
    public const TYPE_OCTETSTREAM         = 'application/octet-stream';
    public const TYPE_TEXT                = 'text/plain';
    public const TYPE_HTML                = 'text/html';
    public const ENCODING_7BIT            = '7bit';
    public const ENCODING_8BIT            = '8bit';
    public const ENCODING_QUOTEDPRINTABLE = 'quoted-printable';
    public const ENCODING_BASE64          = 'base64';
    public const DISPOSITION_ATTACHMENT   = 'attachment';
    public const DISPOSITION_INLINE       = 'inline';
    public const LINELENGTH               = 72;
    public const LINEEND                  = "\n";
    public const MULTIPART_ALTERNATIVE    = 'multipart/alternative';
    public const MULTIPART_MIXED          = 'multipart/mixed';
    public const MULTIPART_RELATED        = 'multipart/related';

    /**
     * Boundary
     *
     * @var null|string
     */
    protected $_boundary;

    /**
     * @var int
     */
    protected static $makeUnique = 0;

    /**
     * Lookup-Tables for QuotedPrintable
     *
     * @var array
     */
    public static $qpKeys = [
        "\x00",
        "\x01",
        "\x02",
        "\x03",
        "\x04",
        "\x05",
        "\x06",
        "\x07",
        "\x08",
        "\x09",
        "\x0A",
        "\x0B",
        "\x0C",
        "\x0D",
        "\x0E",
        "\x0F",
        "\x10",
        "\x11",
        "\x12",
        "\x13",
        "\x14",
        "\x15",
        "\x16",
        "\x17",
        "\x18",
        "\x19",
        "\x1A",
        "\x1B",
        "\x1C",
        "\x1D",
        "\x1E",
        "\x1F",
        "\x7F",
        "\x80",
        "\x81",
        "\x82",
        "\x83",
        "\x84",
        "\x85",
        "\x86",
        "\x87",
        "\x88",
        "\x89",
        "\x8A",
        "\x8B",
        "\x8C",
        "\x8D",
        "\x8E",
        "\x8F",
        "\x90",
        "\x91",
        "\x92",
        "\x93",
        "\x94",
        "\x95",
        "\x96",
        "\x97",
        "\x98",
        "\x99",
        "\x9A",
        "\x9B",
        "\x9C",
        "\x9D",
        "\x9E",
        "\x9F",
        "\xA0",
        "\xA1",
        "\xA2",
        "\xA3",
        "\xA4",
        "\xA5",
        "\xA6",
        "\xA7",
        "\xA8",
        "\xA9",
        "\xAA",
        "\xAB",
        "\xAC",
        "\xAD",
        "\xAE",
        "\xAF",
        "\xB0",
        "\xB1",
        "\xB2",
        "\xB3",
        "\xB4",
        "\xB5",
        "\xB6",
        "\xB7",
        "\xB8",
        "\xB9",
        "\xBA",
        "\xBB",
        "\xBC",
        "\xBD",
        "\xBE",
        "\xBF",
        "\xC0",
        "\xC1",
        "\xC2",
        "\xC3",
        "\xC4",
        "\xC5",
        "\xC6",
        "\xC7",
        "\xC8",
        "\xC9",
        "\xCA",
        "\xCB",
        "\xCC",
        "\xCD",
        "\xCE",
        "\xCF",
        "\xD0",
        "\xD1",
        "\xD2",
        "\xD3",
        "\xD4",
        "\xD5",
        "\xD6",
        "\xD7",
        "\xD8",
        "\xD9",
        "\xDA",
        "\xDB",
        "\xDC",
        "\xDD",
        "\xDE",
        "\xDF",
        "\xE0",
        "\xE1",
        "\xE2",
        "\xE3",
        "\xE4",
        "\xE5",
        "\xE6",
        "\xE7",
        "\xE8",
        "\xE9",
        "\xEA",
        "\xEB",
        "\xEC",
        "\xED",
        "\xEE",
        "\xEF",
        "\xF0",
        "\xF1",
        "\xF2",
        "\xF3",
        "\xF4",
        "\xF5",
        "\xF6",
        "\xF7",
        "\xF8",
        "\xF9",
        "\xFA",
        "\xFB",
        "\xFC",
        "\xFD",
        "\xFE",
        "\xFF"
    ];

    /**
     * @var array
     */
    public static $qpReplaceValues = [
        "=00",
        "=01",
        "=02",
        "=03",
        "=04",
        "=05",
        "=06",
        "=07",
        "=08",
        "=09",
        "=0A",
        "=0B",
        "=0C",
        "=0D",
        "=0E",
        "=0F",
        "=10",
        "=11",
        "=12",
        "=13",
        "=14",
        "=15",
        "=16",
        "=17",
        "=18",
        "=19",
        "=1A",
        "=1B",
        "=1C",
        "=1D",
        "=1E",
        "=1F",
        "=7F",
        "=80",
        "=81",
        "=82",
        "=83",
        "=84",
        "=85",
        "=86",
        "=87",
        "=88",
        "=89",
        "=8A",
        "=8B",
        "=8C",
        "=8D",
        "=8E",
        "=8F",
        "=90",
        "=91",
        "=92",
        "=93",
        "=94",
        "=95",
        "=96",
        "=97",
        "=98",
        "=99",
        "=9A",
        "=9B",
        "=9C",
        "=9D",
        "=9E",
        "=9F",
        "=A0",
        "=A1",
        "=A2",
        "=A3",
        "=A4",
        "=A5",
        "=A6",
        "=A7",
        "=A8",
        "=A9",
        "=AA",
        "=AB",
        "=AC",
        "=AD",
        "=AE",
        "=AF",
        "=B0",
        "=B1",
        "=B2",
        "=B3",
        "=B4",
        "=B5",
        "=B6",
        "=B7",
        "=B8",
        "=B9",
        "=BA",
        "=BB",
        "=BC",
        "=BD",
        "=BE",
        "=BF",
        "=C0",
        "=C1",
        "=C2",
        "=C3",
        "=C4",
        "=C5",
        "=C6",
        "=C7",
        "=C8",
        "=C9",
        "=CA",
        "=CB",
        "=CC",
        "=CD",
        "=CE",
        "=CF",
        "=D0",
        "=D1",
        "=D2",
        "=D3",
        "=D4",
        "=D5",
        "=D6",
        "=D7",
        "=D8",
        "=D9",
        "=DA",
        "=DB",
        "=DC",
        "=DD",
        "=DE",
        "=DF",
        "=E0",
        "=E1",
        "=E2",
        "=E3",
        "=E4",
        "=E5",
        "=E6",
        "=E7",
        "=E8",
        "=E9",
        "=EA",
        "=EB",
        "=EC",
        "=ED",
        "=EE",
        "=EF",
        "=F0",
        "=F1",
        "=F2",
        "=F3",
        "=F4",
        "=F5",
        "=F6",
        "=F7",
        "=F8",
        "=F9",
        "=FA",
        "=FB",
        "=FC",
        "=FD",
        "=FE",
        "=FF"
    ];

    /**
     * @var string
     */
    public static $qpKeysString =
        "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\x7F\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8A\x8B\x8C\x8D\x8E\x8F\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9A\x9B\x9C\x9D\x9E\x9F\xA0\xA1\xA2\xA3\xA4\xA5\xA6\xA7\xA8\xA9\xAA\xAB\xAC\xAD\xAE\xAF\xB0\xB1\xB2\xB3\xB4\xB5\xB6\xB7\xB8\xB9\xBA\xBB\xBC\xBD\xBE\xBF\xC0\xC1\xC2\xC3\xC4\xC5\xC6\xC7\xC8\xC9\xCA\xCB\xCC\xCD\xCE\xCF\xD0\xD1\xD2\xD3\xD4\xD5\xD6\xD7\xD8\xD9\xDA\xDB\xDC\xDD\xDE\xDF\xE0\xE1\xE2\xE3\xE4\xE5\xE6\xE7\xE8\xE9\xEA\xEB\xEC\xED\xEE\xEF\xF0\xF1\xF2\xF3\xF4\xF5\xF6\xF7\xF8\xF9\xFA\xFB\xFC\xFD\xFE\xFF";

    /**
     * Check if the given string is "printable"
     *
     * Checks that a string contains no unprintable characters. If this returns
     * false, encode the string for secure delivery.
     *
     * @param string $str
     * @return boolean
     */
    public static function isPrintable($str)
    {
        return (strcspn($str, self::$qpKeysString) == strlen($str));
    }

    /**
     * Encode a given string with the QUOTED_PRINTABLE mechanism and wrap the lines.
     *
     * @param  string $str
     * @param  int    $lineLength Line length; defaults to {@link LINELENGTH}
     * @param  string $lineEnd    Line end; defaults to {@link LINEEND}
     * @return string
     */
    public static function encodeQuotedPrintable(
        $str,
        $lineLength = self::LINELENGTH,
        $lineEnd = self::LINEEND
    )
    {
        $out = '';
        $str = self::_encodeQuotedPrintable($str);

        // Split encoded text into separate lines
        while (strlen($str) > 0) {
            $ptr = strlen($str);
            if ($ptr > $lineLength) {
                $ptr = $lineLength;
            }

            // Ensure we are not splitting across an encoded character
            $pos = strrpos(substr($str, 0, $ptr), '=');
            if ($pos !== false && $pos >= $ptr - 2) {
                $ptr = $pos;
            }

            // Check if there is a space at the end of the line and rewind
            if ($ptr > 0 && $str[$ptr - 1] == ' ') {
                --$ptr;
            }

            // Add string and continue
            $out .= substr($str, 0, $ptr) . '=' . $lineEnd;
            $str = substr($str, $ptr);
        }

        $out = rtrim($out, $lineEnd);
        $out = rtrim($out, '=');

        return $out;
    }

    /**
     * Converts a string into quoted printable format.
     *
     * @param  string $str
     * @return string
     */
    private static function _encodeQuotedPrintable($str)
    {
        $str = str_replace(
            array_merge(['='], self::$qpKeys),
            array_merge(['=3D'], self::$qpReplaceValues),
            $str);

        return rtrim($str);
    }

    /**
     * Encode a given string with the QUOTED_PRINTABLE mechanism for Mail Headers.
     *
     * Mail headers depend on an extended quoted printable algorithm otherwise
     * a range of bugs can occur.
     *
     * @param  string $str
     * @param  string $charset
     * @param  int    $lineLength Line length; defaults to {@link LINELENGTH}
     * @param  string $lineEnd    Line end; defaults to {@link LINEEND}
     * @return string
     */
    public static function encodeQuotedPrintableHeader(
        $str, $charset, $lineLength = self::LINELENGTH, $lineEnd = self::LINEEND
    )
    {
        // Reduce line-length by the length of the required delimiter, charsets and encoding
        $prefix     = sprintf('=?%s?Q?', $charset);
        $lineLength = $lineLength - strlen($prefix) - 3;

        $str = self::_encodeQuotedPrintable($str);

        // Mail-Header required chars have to be encoded also:
        $str = str_replace(
            ['?', ' ', '_', ','], ['=3F', '=20', '=5F', '=2C'], $str
        );

        // initialize first line, we need it anyways
        $lines = [0 => ""];

        // Split encoded text into separate lines
        $tmp = "";
        while (strlen($str) > 0) {
            $currentLine = max(count($lines) - 1, 0);
            $token       = self::getNextQuotedPrintableToken($str);
            $str         = substr($str, strlen($token));

            $tmp .= $token;
            if ($token == '=20') {
                // only if we have a single char token or space, we can append the
                // tempstring it to the current line or start a new line if necessary.
                if (strlen($lines[$currentLine] . $tmp) > $lineLength) {
                    $lines[$currentLine + 1] = $tmp;
                } else {
                    $lines[$currentLine] .= $tmp;
                }
                $tmp = "";
            }

            // don't forget to append the rest to the last line
            if (strlen($str) === 0) {
                $lines[$currentLine] .= $tmp;
            }
        }

        // assemble the lines together by pre- and appending delimiters, charset, encoding.
        for ($i = 0; $i < count($lines); $i++) {
            $lines[$i] = " " . $prefix . $lines[$i] . "?=";
        }

        $str = trim(implode($lineEnd, $lines));

        return $str;
    }

    /**
     * Retrieves the first token from a quoted printable string.
     *
     * @param  string $str
     * @return string
     */
    private static function getNextQuotedPrintableToken($str)
    {
        if (substr($str, 0, 1) == "=") {
            $token = substr($str, 0, 3);
        } else {
            $token = substr($str, 0, 1);
        }

        return $token;
    }

    /**
     * Encode a given string in mail header compatible base64 encoding.
     *
     * @param  string $str
     * @param  string $charset
     * @param  int    $lineLength Line length; defaults to {@link LINELENGTH}
     * @param  string $lineEnd    Line end; defaults to {@link LINEEND}
     * @return string
     */
    public static function encodeBase64Header(
        $str, $charset, $lineLength = self::LINELENGTH, $lineEnd = self::LINEEND
    )
    {
        $prefix          = '=?' . $charset . '?B?';
        $suffix          = '?=';
        $remainingLength = $lineLength - strlen($prefix) - strlen($suffix);

        $encodedValue = self::encodeBase64($str, $remainingLength, $lineEnd);
        $encodedValue = str_replace(
            $lineEnd, $suffix . $lineEnd . ' ' . $prefix, $encodedValue
        );
        $encodedValue = $prefix . $encodedValue . $suffix;

        return $encodedValue;
    }

    /**
     * Encode a given string in base64 encoding and break lines
     * according to the maximum linelength.
     *
     * @param  string $str
     * @param  int    $lineLength Line length; defaults to {@link LINELENGTH}
     * @param  string $lineEnd    Line end; defaults to {@link LINEEND}
     * @return string
     */
    public static function encodeBase64(
        $str, $lineLength = self::LINELENGTH, $lineEnd = self::LINEEND
    )
    {
        return rtrim(chunk_split(base64_encode($str), $lineLength, $lineEnd));
    }

    /**
     * Constructor
     *
     * @param null|string $boundary
     */
    public function __construct($boundary = null)
    {
        // This string needs to be somewhat unique
        if ($boundary === null) {
            $this->_boundary = '=_' . md5(microtime(1) . self::$makeUnique++);
        } else {
            $this->_boundary = $boundary;
        }
    }

    /**
     * Encode the given string with the given encoding.
     *
     * @param string $str
     * @param string $encoding
     * @param string $EOL Line end; defaults to {@link Zend_Mime::LINEEND}
     * @return string
     */
    public static function encode($str, $encoding, $EOL = self::LINEEND)
    {
        switch ($encoding) {
            case self::ENCODING_BASE64:
                return self::encodeBase64($str, self::LINELENGTH, $EOL);

            case self::ENCODING_QUOTEDPRINTABLE:
                return self::encodeQuotedPrintable($str, self::LINELENGTH, $EOL);

            default:
                /**
                 * @todo 7Bit and 8Bit is currently handled the same way.
                 */
                return $str;
        }
    }

    /**
     * Return a MIME boundary
     *
     * @access public
     * @return string
     */
    public function boundary()
    {
        return $this->_boundary;
    }

    /**
     * Return a MIME boundary line
     *
     * @param  string $EOL Line end; defaults to {@link LINEEND}
     * @return string
     */
    public function boundaryLine($EOL = self::LINEEND)
    {
        return $EOL . '--' . $this->_boundary . $EOL;
    }

    /**
     * Return MIME ending
     *
     * @param  string $EOL Line end; defaults to {@link LINEEND}
     * @return string
     */
    public function mimeEnd($EOL = self::LINEEND)
    {
        return $EOL . '--' . $this->_boundary . '--' . $EOL;
    }
}
