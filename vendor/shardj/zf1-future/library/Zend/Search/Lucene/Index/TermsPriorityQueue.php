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
 * @package    Zend_Search_Lucene
 * @subpackage Index
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Search_Lucene_PriorityQueue */
require_once 'Zend/Search/Lucene/PriorityQueue.php';

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Index
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Search_Lucene_Index_TermsPriorityQueue extends Zend_Search_Lucene_PriorityQueue
{
    /**
     * Compare elements
     *
     * Returns true, if $termsStream1 is "less" than $termsStream2; else otherwise
     *
     * @param mixed $termsStream1
     * @param mixed $termsStream2
     * @return boolean
     */
    protected function _less($termsStream1, $termsStream2)
    {
        return strcmp($termsStream1->currentTerm()->key(), $termsStream2->currentTerm()->key()) < 0;
    }

}
