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
 * @package    Zend_Paginator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Paginator_ScrollingStyle_Interface
 */
require_once 'Zend/Paginator/ScrollingStyle/Interface.php';

/**
 * A Yahoo! Search-like scrolling style.  The cursor will advance to
 * the middle of the range, then remain there until the user reaches
 * the end of the page set, at which point it will continue on to
 * the end of the range and the last page in the set.
 *
 * @link       http://search.yahoo.com/search?p=Zend+Framework
 * @category   Zend
 * @package    Zend_Paginator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Paginator_ScrollingStyle_Sliding implements Zend_Paginator_ScrollingStyle_Interface
{
    /**
     * Returns an array of "local" pages given a page number and range.
     *
     * @param  Zend_Paginator $paginator
     * @param  integer $pageRange (Optional) Page range
     * @return array
     */
    public function getPages(Zend_Paginator $paginator, $pageRange = null)
    {
        if ($pageRange === null) {
            $pageRange = $paginator->getPageRange();
        }

        $pageNumber = $paginator->getCurrentPageNumber();
        $pageCount  = count($paginator);

        if ($pageRange > $pageCount) {
            $pageRange = $pageCount;
        }

        $delta = ceil($pageRange / 2);

        if ($pageNumber - $delta > $pageCount - $pageRange) {
            $lowerBound = $pageCount - $pageRange + 1;
            $upperBound = $pageCount;
        } else {
            if ($pageNumber - $delta < 0) {
                $delta = $pageNumber;
            }

            $offset     = $pageNumber - $delta;
            $lowerBound = $offset + 1;
            $upperBound = $offset + $pageRange;
        }

        return $paginator->getPagesInRange($lowerBound, $upperBound);
    }
}
