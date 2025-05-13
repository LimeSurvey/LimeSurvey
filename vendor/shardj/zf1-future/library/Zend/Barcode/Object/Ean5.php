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
 * @package    Zend_Barcode
 * @subpackage Object
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Barcode_Object_Ean13
 */
require_once 'Zend/Barcode/Object/Ean13.php';

/**
 * @see Zend_Validate_Barcode
 */
require_once 'Zend/Validate/Barcode.php';

/**
 * Class for generate Ean5 barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode_Object_Ean5 extends Zend_Barcode_Object_Ean13
{

    protected $_parities = [
        0 => ['B','B','A','A','A'],
        1 => ['B','A','B','A','A'],
        2 => ['B','A','A','B','A'],
        3 => ['B','A','A','A','B'],
        4 => ['A','B','B','A','A'],
        5 => ['A','A','B','B','A'],
        6 => ['A','A','A','B','B'],
        7 => ['A','B','A','B','A'],
        8 => ['A','B','A','A','B'],
        9 => ['A','A','B','A','B']
    ];

    /**
     * Default options for Ean5 barcode
     * @return void
     */
    protected function _getDefaultOptions()
    {
        $this->_barcodeLength = 5;
    }

    /**
     * Width of the barcode (in pixels)
     * @return integer
     */
    protected function _calculateBarcodeWidth()
    {
        $quietZone       = $this->getQuietZone();
        $startCharacter  = (5 * $this->_barThinWidth) * $this->_factor;
        $middleCharacter = (2 * $this->_barThinWidth) * $this->_factor;
        $encodedData     = (7 * $this->_barThinWidth) * $this->_factor;
        return $quietZone + $startCharacter + ($this->_barcodeLength - 1) * $middleCharacter + $this->_barcodeLength * $encodedData + $quietZone;
    }

    /**
     * Prepare array to draw barcode
     * @return array
     */
    protected function _prepareBarcode()
    {
        $barcodeTable = [];

        // Start character (01011)
        $barcodeTable[] = [0 , $this->_barThinWidth , 0 , 1];
        $barcodeTable[] = [1 , $this->_barThinWidth , 0 , 1];
        $barcodeTable[] = [0 , $this->_barThinWidth , 0 , 1];
        $barcodeTable[] = [1 , $this->_barThinWidth , 0 , 1];
        $barcodeTable[] = [1 , $this->_barThinWidth , 0 , 1];

        $firstCharacter = true;
        $textTable = str_split($this->getText());

        // Characters
        for ($i = 0; $i < $this->_barcodeLength; $i++) {
            if ($firstCharacter) {
                $firstCharacter = false;
            } else {
                // Intermediate character (01)
                $barcodeTable[] = [0 , $this->_barThinWidth , 0 , 1];
                $barcodeTable[] = [1 , $this->_barThinWidth , 0 , 1];
            }
            $bars = str_split($this->_codingMap[$this->_getParity($i)][$textTable[$i]]);
            foreach ($bars as $b) {
                $barcodeTable[] = [$b , $this->_barThinWidth , 0 , 1];
            }
        }

        return $barcodeTable;
    }

    /**
     * Get barcode checksum
     *
     * @param  string $text
     * @return int
     */
    public function getChecksum($text)
    {
        $this->_checkText($text);
        $checksum = 0;

        for ($i = 0 ; $i < $this->_barcodeLength; $i ++) {
            $checksum += (int)$text[$i] * ($i % 2 ? 9 : 3);
        }

        return ($checksum % 10);
    }

    protected function _getParity($i)
    {
        $checksum = $this->getChecksum($this->getText());
        return $this->_parities[$checksum][$i];
    }

    /**
     * Retrieve text to encode
     * @return string
     */
    public function getText()
    {
        return $this->_addLeadingZeros($this->_text);
    }
}
