<?php

namespace SPSS;

use SPSS\Sav\Record\Variable;

class Utils
{
    /**
     * SPSS represents a date as the number of seconds since the epoch, midnight, Oct. 14, 1582.
     *
     * @param $timestamp
     * @param string $format
     *
     * @return false|string
     */
    public static function formatDate($timestamp, $format = 'Y M d')
    {
        return date($format, strtotime('1582-10-14 00:00:00') + $timestamp);
    }

    /**
     * Rounds X up to the next multiple of Y.
     *
     * @param int $x
     * @param int $y
     *
     * @return int
     */
    public static function roundUp($x, $y)
    {
        return ceil($x / $y) * $y;
    }

    /**
     * Rounds X down to the prev multiple of Y.
     *
     * @param int $x
     * @param int $y
     *
     * @return int
     */
    public static function roundDown($x, $y)
    {
        return floor($x / $y) * $y;
    }

    /**
     * Convert bytes to string.
     *
     * @param  array  $bytes
     *
     * @return string
     */
    public static function bytesToString(array $bytes)
    {
        $str = '';
        foreach ($bytes as $byte) {
            $str .= \chr($byte);
        }

        return $str;
    }

    /**
     * Convert double to string.
     *
     * @param float $num
     *
     * @return string
     */
    public static function doubleToString($num)
    {
        return self::bytesToString(unpack('C8', pack('d', $num)));
    }

    /**
     * @param string $str
     *
     * @return float
     */
    public static function stringToDouble($str)
    {
        // if (strlen($str) < 8) {
        //     throw new Exception('String must be a 8 length');
        // }

        return unpack('d', pack('A8', $str))[1];
    }

    /**
     * @param  array  $bytes
     * @param  bool  $unsigned
     *
     * @return int
     */
    public static function bytesToInt(array $bytes, $unsigned = true)
    {
        $bytes = array_reverse($bytes);
        $value = 0;
        foreach ($bytes as $i => $b) {
            $value |= $b << $i * 8;
        }

        return $unsigned ? $value : self::unsignedToSigned($value, \count($bytes) * 8);
    }

    /**
     * @param int $int
     * @param int $size
     *
     * @return array
     */
    public static function intToBytes($int, $size = 32)
    {
        $size  = self::roundUp($size, 8);
        $bytes = [];
        for ($i = 0; $i < $size; $i += 8) {
            $bytes[] = 0xFF & $int >> $i;
        }

        return array_reverse($bytes);
    }

    /**
     * @param int $value
     * @param int $size
     *
     * @return string
     */
    public static function unsignedToSigned($value, $size = 32)
    {
        $size = self::roundUp($size, 8);
        if (bccomp($value, bcpow(2, $size - 1)) >= 0) {
            $value = bcsub($value, bcpow(2, $size));
        }

        return $value;
    }

    /**
     * @param int $value
     * @param int $size
     *
     * @return string
     */
    public static function signedToUnsigned($value, $size = 32)
    {
        return $value + bcpow(2, $size);
    }

    /**
     * Returns the number of bytes of uncompressed case data used for writing a variable of the given WIDTH to a system file.
     * All required space is included, including trailing padding and internal padding.
     *
     * @param int $width
     *
     * @return int
     */
    public static function widthToBytes($width)
    {
        // assert($width >= 0);

        if (0 === $width) {
            $bytes = 8;
        } elseif (Variable::isVeryLong($width) === 0) {
            $bytes = $width;
        } else {
            $chunks    = $width / Variable::EFFECTIVE_VLS_CHUNK;
            $remainder = $width % Variable::EFFECTIVE_VLS_CHUNK;
            $bytes     = floor($chunks) * Variable::REAL_VLS_CHUNK + $remainder;
        }

        return self::roundUp($bytes, 8);
    }

    /**
     * Returns the number of 8-byte units (octs) used to write data for a variable of the given WIDTH.
     *
     * @param int $width
     *
     * @return int
     */
    public static function widthToOcts($width)
    {
        $result = 0;
        foreach (self::getSegments($width) as $segmentWidth) {
            $result += ceil($segmentWidth / 8);
        }

        return (int) max(1, $result);
    }

    /**
     * Returns the number of "segments" used for writing case data for a variable of the given WIDTH.
     * A segment is a physical variable in the system file that represents some piece of a logical variable.
     * Only very long string variables have more than one segment.
     *
     * @param int $width
     *
     * @return int
     */
    public static function widthToSegments($width)
    {
        return Variable::isVeryLong($width) !== 0 ? ceil($width / Variable::EFFECTIVE_VLS_CHUNK) : 1;
    }

    /**
     * @param $width
     *
     * @return \Generator
     */
    public static function getSegments($width)
    {
        $count = self::widthToSegments($width);
        for ($i = 1; $i < $count; $i++) {
            yield 255;
        }
        yield $width - ($count - 1) * Variable::EFFECTIVE_VLS_CHUNK;
    }

    /**
     * Returns the width to allocate to the given SEGMENT within a variable of the given WIDTH.
     * A segment is a physical variable in the system file that represents some piece of a logical variable.
     *
     * @param int $width
     * @param int $segment
     *
     * @return int
     */
    public static function segmentAllocWidth($width, $segment = 0)
    {
        $segmentCount = self::widthToSegments($width);
        // assert($segment < $segmentCount);

        if (Variable::isVeryLong($width) === 0) {
            return $width;
        }

        return $segment < $segmentCount - 1 ? Variable::REAL_VLS_CHUNK : $width - $segment * Variable::EFFECTIVE_VLS_CHUNK;
    }

    /**
     * Returns the number of bytes to allocate to the given SEGMENT within a variable of the given width.
     * This is the same as.
     *
     * @param mixed $width
     * @param int   $segment
     *
     * @return int
     *
     * @see segmentAllocWidth, except that a numeric value takes up 8 bytes despite having a width of 0.
     */
    public static function segmentAllocBytes($width, $segment)
    {
        \assert($segment < self::widthToSegments($width));

        return 0 === $width ? 8 : self::roundUp(self::segmentAllocWidth($width, $segment), 8);
    }

    /**
     * @param mixed $values
     */
    public static function is_countable($values)
    {
        # is_countable (PHP 7 >= 7.3.0, PHP 8)
        if (version_compare(PHP_VERSION, "7.3") < 0) {
            return (is_array($values) || is_object($values) || is_iterable($values) || $values instanceof Countable);
        } else {
            return \is_countable($values);
        }
    }
}
