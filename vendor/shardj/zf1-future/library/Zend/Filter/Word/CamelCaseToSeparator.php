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
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Filter_PregReplace
 */
require_once 'Zend/Filter/Word/Separator/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Word_CamelCaseToSeparator extends Zend_Filter_Word_Separator_Abstract
{

    public function filter($value)
    {
        if (self::isUnicodeSupportEnabled()) {
            parent::setMatchPattern(['#(?<=(?:\p{Lu}))(\p{Lu}\p{Ll})#','#(?<=(?:\p{Ll}|\p{Nd}))(\p{Lu})#']);
            parent::setReplacement([$this->_separator . '\1', $this->_separator . '\1']);
        } else {
            parent::setMatchPattern(['#(?<=(?:[A-Z]))([A-Z]+)([A-Z][A-z])#', '#(?<=(?:[a-z0-9]))([A-Z])#']);
            parent::setReplacement(['\1' . $this->_separator . '\2', $this->_separator . '\1']);
        }

        return parent::filter($value);
    }

}
