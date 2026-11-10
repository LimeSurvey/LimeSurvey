<?php

declare(strict_types=1);

namespace PhpOffice\WMF\Reader;

use PhpOffice\WMF\Reader\EMF\GD as EMFReader;

/**
 * Detects the type of an image (WMF, EMF, EMF+) from its content
 *
 * @see https://learn.microsoft.com/en-us/openspecs/windows_protocols/ms-wmf/
 * @see https://learn.microsoft.com/en-us/openspecs/windows_protocols/ms-emf/
 * @see https://learn.microsoft.com/en-us/openspecs/windows_protocols/ms-emfplus/
 */
class Detector
{
    public const TYPE_WMF = 'wmf';
    public const TYPE_EMF = 'emf';
    public const TYPE_EMFPLUS = 'emf+';
    public const TYPE_UNKNOWN = 'unknown';

    /**
     * Key of the placeable WMF header
     */
    protected const WMF_PLACEABLE_KEY = 0x9AC6CDD7;
    /**
     * Signature of the EMF header (" EMF")
     */
    protected const EMF_SIGNATURE = 0x464D4520;
    /**
     * Identifier of the EMR_COMMENT_EMFPLUS records ("EMF+")
     */
    protected const EMFPLUS_IDENTIFIER = 0x2B464D45;

    protected const EMFPLUS_HEADER = 0x4001;

    /**
     * Returns the type of an image : TYPE_WMF, TYPE_EMF, TYPE_EMFPLUS or TYPE_UNKNOWN
     *
     * EMF+ files are EMF files containing EMF+ records : EMF+ "dual" files can be read as EMF files
     */
    public static function detect(string $content): string
    {
        if (self::isWMF($content)) {
            return self::TYPE_WMF;
        }
        if (self::isEMFPlus($content)) {
            return self::TYPE_EMFPLUS;
        }
        if (self::isEMF($content)) {
            return self::TYPE_EMF;
        }

        return self::TYPE_UNKNOWN;
    }

    /**
     * Returns the type of an image file : TYPE_WMF, TYPE_EMF, TYPE_EMFPLUS or TYPE_UNKNOWN
     *
     * Only the headers of the file are read
     */
    public static function detectFile(string $filename): string
    {
        if (!is_file($filename) || !is_readable($filename)) {
            return self::TYPE_UNKNOWN;
        }

        $content = (string) file_get_contents($filename, false, null, 0, 88);
        if (self::isEMF($content)) {
            // The EMF+ header is in the record following the EMF header
            list(, $headerSize) = unpack('V', (string) substr($content, 4, 4));
            $content = (string) file_get_contents($filename, false, null, 0, min((int) $headerSize, 65536) + 28);
        }

        return self::detect($content);
    }

    /**
     * Returns if the content is a WMF file (placeable or not)
     */
    public static function isWMF(string $content): bool
    {
        return self::isPlaceableWMF($content) || self::isStandardWMF($content);
    }

    /**
     * Returns if the content is a placeable WMF file (with a META_PLACEABLE header)
     */
    public static function isPlaceableWMF(string $content): bool
    {
        if (strlen($content) < 22 + 18) {
            return false;
        }
        list(, $key) = unpack('V', (string) substr($content, 0, 4));

        return $key == self::WMF_PLACEABLE_KEY && self::isStandardWMF((string) substr($content, 22));
    }

    /**
     * Returns if the content starts with a META_HEADER record
     */
    protected static function isStandardWMF(string $content): bool
    {
        if (strlen($content) < 18) {
            return false;
        }
        $header = unpack('vtype/vheaderSize/vversion', (string) substr($content, 0, 6));

        // Type : MEMORYMETAFILE or DISKMETAFILE
        // Header size : 9 words
        // Version : METAVERSION100 or METAVERSION300
        return in_array($header['type'], [1, 2])
            && $header['headerSize'] == 9
            && in_array($header['version'], [0x0100, 0x0300]);
    }

    /**
     * Returns if the content is a EMF file (including EMF+ files)
     */
    public static function isEMF(string $content): bool
    {
        if (strlen($content) < 88) {
            return false;
        }
        list(, $type, $size) = unpack('V2', (string) substr($content, 0, 8));
        list(, $signature) = unpack('V', (string) substr($content, 40, 4));

        return $type == EMFReader::EMR_HEADER && $size >= 88 && $signature == self::EMF_SIGNATURE;
    }

    /**
     * Returns if the content is a EMF+ file : a EMF file whose first record after the header is a EMF+ header
     */
    public static function isEMFPlus(string $content): bool
    {
        if (!self::isEMF($content)) {
            return false;
        }
        list(, $headerSize) = unpack('V', (string) substr($content, 4, 4));
        $record = (string) substr($content, $headerSize, 28);
        if (strlen($record) < 28) {
            return false;
        }
        $comment = unpack('Vtype/Vsize/VdataSize/Videntifier/vplusType', $record);

        return $comment['type'] == EMFReader::EMR_GDICOMMENT
            && $comment['identifier'] == self::EMFPLUS_IDENTIFIER
            && $comment['plusType'] == self::EMFPLUS_HEADER;
    }
}
