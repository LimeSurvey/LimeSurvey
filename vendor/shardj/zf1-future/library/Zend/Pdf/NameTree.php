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
 * @subpackage Actions
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Internally used classes */
require_once 'Zend/Pdf/Element.php';


/**
 * PDF name tree representation class
 *
 * @todo implement lazy resource loading so resources will be really loaded at access time
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_NameTree implements ArrayAccess, Iterator, Countable
{
    /**
     * Elements
     * Array of name => object tree entries
     *
     * @var array
     */
    protected $_items = [];

    /**
     * Object constructor
     *
     * @param Zend_Pdf_Element $rootDictionary root of name dictionary
     */
    public function __construct(Zend_Pdf_Element $rootDictionary)
    {
        if ($rootDictionary->getType() != Zend_Pdf_Element::TYPE_DICTIONARY) {
            require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Name tree root must be a dictionary.');
        }

        $intermediateNodes = [];
        $leafNodes         = [];
        if ($rootDictionary->Kids !== null) {
            $intermediateNodes[] = $rootDictionary;
        } else {
            $leafNodes[] = $rootDictionary;
        }

        while (count($intermediateNodes) !== 0) {
            $newIntermediateNodes = [];
            foreach ($intermediateNodes as $node) {
                foreach ($node->Kids->items as $childNode) {
                    if ($childNode->Kids !== null) {
                        $newIntermediateNodes[] = $childNode;
                    } else {
                        $leafNodes[] = $childNode;
                    }
                }
            }
            $intermediateNodes = $newIntermediateNodes;
        }

        foreach ($leafNodes as $leafNode) {
            $destinationsCount = count($leafNode->Names->items)/2;
            for ($count = 0; $count < $destinationsCount; $count++) {
                $this->_items[$leafNode->Names->items[$count*2]->value] = $leafNode->Names->items[$count*2 + 1];
            }
        }
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->_items);
    }


    #[\ReturnTypeWillChange]
    public function next()
    {
        return next($this->_items);
    }


    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->_items);
    }


    public function valid(): bool
    {
        return current($this->_items)!==false;
    }


    public function rewind(): void
    {
        reset($this->_items);
    }


    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->_items);
    }


    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->_items[$offset];
    }


    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            $this->_items[]        = $value;
        } else {
            $this->_items[$offset] = $value;
        }
    }


    public function offsetUnset($offset): void
    {
        unset($this->_items[$offset]);
    }


    public function clear()
    {
        $this->_items = [];
    }

    public function count(): int
    {
        return count($this->_items);
    }
}
