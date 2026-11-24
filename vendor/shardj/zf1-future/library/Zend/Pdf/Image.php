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
 * @package    Zend_Pdf
 * @subpackage Images
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * Abstract factory class which vends {@link Zend_Pdf_Resource_Image} objects.
 *
 * This class is also the home for image-related constants because the name of
 * the true base class ({@link Zend_Pdf_Resource_Image}) is not intuitive for the
 * end user.
 *
 * @package    Zend_Pdf
 * @subpackage Images
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_Image
{
  /**** Class Constants ****/


  /* Image Types */

    public const TYPE_UNKNOWN = 0;
    public const TYPE_JPEG = 1;
    public const TYPE_PNG = 2;
    public const TYPE_TIFF = 3;

  /* TIFF Constants */

    public const TIFF_FIELD_TYPE_BYTE=1;
    public const TIFF_FIELD_TYPE_ASCII=2;
    public const TIFF_FIELD_TYPE_SHORT=3;
    public const TIFF_FIELD_TYPE_LONG=4;
    public const TIFF_FIELD_TYPE_RATIONAL=5;

    public const TIFF_TAG_IMAGE_WIDTH=256;
    public const TIFF_TAG_IMAGE_LENGTH=257; //Height
    public const TIFF_TAG_BITS_PER_SAMPLE=258;
    public const TIFF_TAG_COMPRESSION=259;
    public const TIFF_TAG_PHOTOMETRIC_INTERPRETATION=262;
    public const TIFF_TAG_STRIP_OFFSETS=273;
    public const TIFF_TAG_SAMPLES_PER_PIXEL=277;
    public const TIFF_TAG_STRIP_BYTE_COUNTS=279;

    public const TIFF_COMPRESSION_UNCOMPRESSED = 1;
    public const TIFF_COMPRESSION_CCITT1D = 2;
    public const TIFF_COMPRESSION_GROUP_3_FAX = 3;
    public const TIFF_COMPRESSION_GROUP_4_FAX  = 4;
    public const TIFF_COMPRESSION_LZW = 5;
    public const TIFF_COMPRESSION_JPEG = 6;
    public const TIFF_COMPRESSION_FLATE = 8;
    public const TIFF_COMPRESSION_FLATE_OBSOLETE_CODE = 32946;
    public const TIFF_COMPRESSION_PACKBITS = 32773;

    public const TIFF_PHOTOMETRIC_INTERPRETATION_WHITE_IS_ZERO=0;
    public const TIFF_PHOTOMETRIC_INTERPRETATION_BLACK_IS_ZERO=1;
    public const TIFF_PHOTOMETRIC_INTERPRETATION_RGB=2;
    public const TIFF_PHOTOMETRIC_INTERPRETATION_RGB_INDEXED=3;
    public const TIFF_PHOTOMETRIC_INTERPRETATION_CMYK=5;
    public const TIFF_PHOTOMETRIC_INTERPRETATION_YCBCR=6;
    public const TIFF_PHOTOMETRIC_INTERPRETATION_CIELAB=8;

  /* PNG Constants */

    public const PNG_COMPRESSION_DEFAULT_STRATEGY = 0;
    public const PNG_COMPRESSION_FILTERED = 1;
    public const PNG_COMPRESSION_HUFFMAN_ONLY = 2;
    public const PNG_COMPRESSION_RLE = 3;

    public const PNG_FILTER_NONE = 0;
    public const PNG_FILTER_SUB = 1;
    public const PNG_FILTER_UP = 2;
    public const PNG_FILTER_AVERAGE = 3;
    public const PNG_FILTER_PAETH = 4;

    public const PNG_INTERLACING_DISABLED = 0;
    public const PNG_INTERLACING_ENABLED = 1;

    public const PNG_CHANNEL_GRAY = 0;
    public const PNG_CHANNEL_RGB = 2;
    public const PNG_CHANNEL_INDEXED = 3;
    public const PNG_CHANNEL_GRAY_ALPHA = 4;
    public const PNG_CHANNEL_RGB_ALPHA = 6;

  /**** Public Interface ****/


  /* Factory Methods */

    /**
     * Returns a {@link Zend_Pdf_Resource_Image} object by file path.
     *
     * @param string $filePath Full path to the image file.
     * @return Zend_Pdf_Resource_Image
     * @throws Zend_Pdf_Exception
     */
    public static function imageWithPath($filePath)
    {
        require_once 'Zend/Pdf/Resource/ImageFactory.php';
        return Zend_Pdf_Resource_ImageFactory::factory($filePath);
    }



    /**** Internal Methods ****/


    /* Image Extraction Methods */

    /**
     * Attempts to extract a JPEG Image from the data source.
     *
     * @param Zend_Pdf_FileParserDataSource $dataSource
     * @return Zend_Pdf_Resource_Image_Jpeg May also return null if
     *   the data source does not appear to contain valid image data.
     * @throws Zend_Pdf_Exception
     */
    protected static function _extractJpegImage($dataSource)
    {
        require_once 'Zend/Pdf/Exception.php';
        throw new Zend_Pdf_Exception('Jpeg image fileparser is not implemented. Old styly implementation has to be used.');
    }

    /**
     * Attempts to extract a PNG Image from the data source.
     *
     * @param Zend_Pdf_FileParserDataSource $dataSource
     * @return Zend_Pdf_Resource_Image_Png May also return null if
     *   the data source does not appear to contain valid image data.
     */
    protected static function _extractPngImage($dataSource)
    {
        require_once 'Zend/Pdf/FileParser/Image/Png.php';
        $imageParser = new Zend_Pdf_FileParser_Image_Png($dataSource);
        require_once 'Zend/Pdf/Resource/Image/Png.php';
        $image = new Zend_Pdf_Resource_Image_Png($imageParser);
        unset($imageParser);

        return $image;
    }

    /**
     * Attempts to extract a TIFF Image from the data source.
     *
     * @param Zend_Pdf_FileParserDataSource $dataSource
     * @return Zend_Pdf_Resource_Image_Tiff May also return null if
     *   the data source does not appear to contain valid image data.
     * @throws Zend_Pdf_Exception
     */
    protected static function _extractTiffImage($dataSource)
    {
        require_once 'Zend/Pdf/Exception.php';
        throw new Zend_Pdf_Exception('Tiff image fileparser is not implemented. Old styly implementation has to be used.');
    }
}
