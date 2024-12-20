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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Zend_Pdf_Filter_Compression */
require_once 'Zend/Pdf/Filter/Compression.php';

/**
 * Flate stream filter
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Filter_Compression_Flate extends Zend_Pdf_Filter_Compression
{
    /**
     * Encode data
     *
     * @param string $data
     * @param array $params
     * @return string
     * @throws Zend_Pdf_Exception
     */
    public static function encode($data, $params = null)
    {
        if ($params != null) {
            $data = self::_applyEncodeParams($data, $params);
        }

        if (extension_loaded('zlib')) {

            if (($output = @gzcompress($data)) === false) {
                $err = error_get_last();
                $phpErrormsg = $err['message'];
                require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception($phpErrormsg);
            }
        } else {
            require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Not implemented yet. You have to use zlib extension.');
        }

        return $output;
    }

    /**
     * Decode data
     *
     * @param string $data
     * @param array $params
     * @return string
     * @throws Zend_Pdf_Exception
     */
    public static function decode($data, $params = null)
    {
        if (extension_loaded('zlib')) {
            if (($output = @gzuncompress($data)) === false) {
                $err = error_get_last();
                $phpErrormsg = $err['message'];
                require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception($phpErrormsg);
            }
        } else {
            require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Not implemented yet');
        }

        if ($params !== null) {
            return self::_applyDecodeParams($output, $params);
        } else {
            return $output;
        }
    }
}
