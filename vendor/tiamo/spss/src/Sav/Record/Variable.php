<?php

namespace SPSS\Sav\Record;

use SPSS\Buffer;
use SPSS\Sav\Record;
use SPSS\Utils;

class Variable extends Record
{
    const TYPE = 2;

    /**
     * Number of bytes really stored in each segment of a very long string variable.
     */
    const REAL_VLS_CHUNK = 255;

    /**
     * Number of bytes per segment by which the amount of space for very long string variables is allocated.
     */
    const EFFECTIVE_VLS_CHUNK = 252;

    /**
     * Set to 0 for a numeric variable.
     * For a short string variable or the first part of a long string variable, this is set to the width of the string.
     * For the second and subsequent parts of a long string variable, set to -1, and the remaining fields in the structure are ignored.
     *
     * @var int variable width
     */
    public $width;

    /**
     * The real record position of the variable inside the file.
     */
    public $realPosition;

    /**
     * If the variable has no missing values, set to 0.
     * If the variable has one, two, or three discrete missing values, set to 1, 2, or 3, respectively.
     * If the variable has a range for missing variables, set to -2;
     * if the variable has a range for missing variables plus a single discrete value, set to -3.
     * A long string variable always has the value 0 here.
     * A separate record indicates missing values for long string variables.
     *
     * @var int
     *
     * @see \SPSS\Sav\Record\Info\LongStringMissingValues
     */
    public $missingValuesFormat = 0;

    /**
     * Print format for this variable.
     * [0, format, width, decimals].
     *
     * @var array
     */
    public $print = [0, 0, 0, 0];

    /**
     * Write format for this variable.
     * [0, format, width, decimals].
     *
     * @var array
     */
    public $write = [0, 0, 0, 0];

    /**
     * The variable name must begin with a capital letter or the at-sign (‘@’).
     * Subsequent characters may also be digits, octothorpes (‘#’), dollar signs (‘$’), underscores (‘_’), or full stops (‘.’).
     * The variable name is padded on the right with spaces.
     *
     * @var string variable name
     */
    public $name;

    /**
     * It has length label_len, rounded up to the nearest multiple of 32 bits.
     * The first label_len characters are the variable’s variable label.
     *
     * @var string
     */
    public $label;

    /**
     * It has the same number of 8-byte elements as the absolute value of $missingValuesFormat.
     * Each element is interpreted as a number for numeric variables (with HIGHEST and LOWEST indicated as described in the chapter introduction).
     * For string variables of width less than 8 bytes, elements are right-padded with spaces;
     * for string variables wider than 8 bytes,
     * only the first 8 bytes of each missing value are specified, with the remainder implicitly all spaces.
     * For discrete missing values, each element represents one missing value.
     * When a range is present, the first element denotes the minimum value in the range,
     * and the second element denotes the maximum value in the range.
     * When a range plus a value are present, the third element denotes the additional discrete missing value.
     *
     * @var array
     */
    public $missingValues = [];

    /**
     * Returns true if WIDTH is a very long string width, false otherwise.
     *
     * @param int $width
     *
     * @return int
     */
    public static function isVeryLong($width)
    {
        return $width > self::REAL_VLS_CHUNK;
    }

    public function read(Buffer $buffer)
    {
        $this->width               = $buffer->readInt();
        $hasLabel                  = $buffer->readInt();
        $this->missingValuesFormat = $buffer->readInt();
        $this->print               = Utils::intToBytes($buffer->readInt());
        $this->write               = Utils::intToBytes($buffer->readInt());
        $this->name                = rtrim($buffer->readString(8));
        if ($hasLabel !== 0) {
            $labelLength = $buffer->readInt();
            $this->label = $buffer->readString($labelLength, 4);
        }
        if (0 !== $this->missingValuesFormat) {
            for ($i = 0, $iMax = abs($this->missingValuesFormat); $i < $iMax; $i++) {
                $this->missingValues[] = $buffer->readDouble();
            }
        }
    }

    public function write(Buffer $buffer)
    {
        $seg0width = Utils::segmentAllocWidth($this->width, 0);
        $hasLabel  = !empty($this->label);

        $buffer->writeInt(self::TYPE);
        $buffer->writeInt($seg0width);
        $buffer->writeInt($hasLabel ? 1 : 0);
        $buffer->writeInt($this->missingValuesFormat);
        $buffer->writeInt(Utils::bytesToInt($this->print));
        $buffer->writeInt(Utils::bytesToInt($this->write));
        $buffer->writeString($this->name, 8);

        if ($hasLabel) {
            // Maxlength is 255 bytes, since we write utf8 a char can be multiple bytes
            $labelLength      = min(mb_strlen($this->label), 255);
            $label            = mb_substr($this->label, 0, $labelLength);
            $labelLengthBytes = mb_strlen($label, '8bit');
            while ($labelLengthBytes > 255) {
                // Strip one char, can be multiple bytes
                $label            = mb_substr($label, 0, -1);
                $labelLengthBytes = mb_strlen($label, '8bit');
            }
            $buffer->writeInt($labelLengthBytes);
            $buffer->writeString($label, Utils::roundUp($labelLengthBytes, 4));
        }

        // TODO: test
        if ($this->missingValuesFormat !== 0) {
            foreach ($this->missingValues as $val) {
                if (0 === $this->width) {
                    $buffer->writeDouble($val);
                } else {
                    $buffer->writeString($val, 8);
                }
            }
        }

        // We need an empty record
        $this->writeBlank($buffer, $seg0width);

        // Write additional segments for very long string variables.
        if (self::isVeryLong($this->width) !== 0) {
            $segmentCount = Utils::widthToSegments($this->width);
            for ($i = 1; $i < $segmentCount; $i++) {
                $segmentWidth = Utils::segmentAllocWidth($this->width, $i);
                $format       = Utils::bytesToInt([0, 1, max($segmentWidth, 1), 0]);
                $buffer->writeInt(self::TYPE);
                $buffer->writeInt($segmentWidth);
                $buffer->writeInt($hasLabel); // No variable label
                $buffer->writeInt(0); // No missing values
                $buffer->writeInt($format); // Print format
                $buffer->writeInt($format); // Write format
                $buffer->writeString($this->getSegmentName($i - 1), 8);
                if ($hasLabel) {
                    $buffer->writeInt($labelLengthBytes);
                    $buffer->writeString($label, Utils::roundUp($labelLengthBytes, 4));
                }

                $this->writeBlank($buffer, $segmentWidth);
            }
        }
    }

    /**
     * @param  Buffer  $buffer
     * @param  int  $width
     */
    public function writeBlank(Buffer $buffer, $width)
    {
        // assert(self::widthToSegments($width) == 1);

        for ($i = 8; $i < $width; $i += 8) {
            $buffer->writeInt(self::TYPE);
            $buffer->writeInt(-1);
            $buffer->writeInt(0);
            $buffer->writeInt(0);
            $buffer->writeInt(0x011d01);
            $buffer->writeInt(0x011d01);
            $buffer->write('        ');
        }
    }

    /**
     * @param int $seg
     *
     * @return string
     */
    public function getSegmentName($seg = 0)
    {
        // TODO: refactory
        $name = $this->name;
        $name = mb_substr($name, 0, 6);
        $name .= $seg;

        return mb_strtoupper($name);
    }
}
