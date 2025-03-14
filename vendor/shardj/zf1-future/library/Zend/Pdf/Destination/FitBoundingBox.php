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
 * @subpackage Destination
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Internally used classes */
require_once 'Zend/Pdf/Element/Array.php';
require_once 'Zend/Pdf/Element/Name.php';
require_once 'Zend/Pdf/Element/Numeric.php';


/** Zend_Pdf_Destination_Explicit */
require_once 'Zend/Pdf/Destination/Explicit.php';

/**
 * Zend_Pdf_Destination_FitBoundingBox explicit detination
 *
 * Destination array: [page /FitB]
 *
 * (PDF 1.1) Display the page designated by page, with its contents magnified
 * just enough to fit its bounding box entirely within the window both horizontally
 * and vertically. If the required horizontal and vertical magnification
 * factors are different, use the smaller of the two, centering the bounding box
 * within the window in the other dimension.
 *
 * @package    Zend_Pdf
 * @subpackage Destination
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Destination_FitBoundingBox extends Zend_Pdf_Destination_Explicit
{
    /**
     * Create destination object
     *
     * @param Zend_Pdf_Page|integer $page  Page object or page number
     * @return Zend_Pdf_Destination_FitBoundingBox
     * @throws Zend_Pdf_Exception
     */
    public static function create($page)
    {
        $destinationArray = new Zend_Pdf_Element_Array();

        if ($page instanceof Zend_Pdf_Page) {
            $destinationArray->items[] = $page->getPageDictionary();
        } else if (is_integer($page)) {
            $destinationArray->items[] = new Zend_Pdf_Element_Numeric($page);
        } else {
            require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Page entry must be a Zend_Pdf_Page object or a page number.');
        }

        $destinationArray->items[] = new Zend_Pdf_Element_Name('FitB');

        return new Zend_Pdf_Destination_FitBoundingBox($destinationArray);
    }
}
