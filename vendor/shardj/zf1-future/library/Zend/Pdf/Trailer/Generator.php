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


/** Zend_Pdf_Trailer */
require_once 'Zend/Pdf/Trailer.php';

/**
 * PDF file trailer generator (used for just created PDF)
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Trailer_Generator extends Zend_Pdf_Trailer
{
    /**
     * Object constructor
     *
     * @param Zend_Pdf_Element_Dictionary $dict
     */
    public function __construct(Zend_Pdf_Element_Dictionary $dict)
    {
        parent::__construct($dict);
    }

    /**
     * Get length of source PDF
     *
     * @return int
     */
    public function getPDFLength()
    {
        require_once 'Zend/Pdf.php';
        return strlen(Zend_Pdf::PDF_HEADER);
    }

    /**
     * Get PDF String
     *
     * @return string
     */
    public function getPDFString()
    {
        require_once 'Zend/Pdf.php';
        return Zend_Pdf::PDF_HEADER;
    }

    /**
     * Get header of free objects list
     * Returns object number of last free object
     *
     * @return integer
     */
    public function getLastFreeObject()
    {
        return 0;
    }
}
