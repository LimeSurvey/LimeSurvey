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
 * @package    Zend_Ldap
 * @subpackage Node
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Ldap_Node
 */
require_once 'Zend/Ldap/Node.php';

/**
 * Zend_Ldap_Node_ChildrenIterator provides an iterator to a collection of children nodes.
 *
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage Node
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_Node_ChildrenIterator implements Iterator, Countable, RecursiveIterator, ArrayAccess
{
    /**
     * An array of Zend_Ldap_Node objects
     *
     * @var array
     */
    private $_data;

    /**
     * Constructor.
     *
     * @param  array $data
     * @return void
     */
    public function __construct(array $data)
    {
        $this->_data = $data;
    }

    /**
     * Returns the number of child nodes.
     * Implements Countable
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->_data);
    }

    /**
     * Return the current child.
     * Implements Iterator
     *
     * @return Zend_Ldap_Node
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->_data);
    }

    /**
     * Return the child'd RDN.
     * Implements Iterator
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->_data);
    }

    /**
     * Move forward to next child.
     * Implements Iterator
     */
    public function next(): void
    {
        next($this->_data);
    }

    /**
     * Rewind the Iterator to the first child.
     * Implements Iterator
     */
    public function rewind(): void
    {
        reset($this->_data);
    }

    /**
     * Check if there is a current child
     * after calls to rewind() or next().
     * Implements Iterator
     *
     * @return boolean
     */
    public function valid(): bool
    {
        return (current($this->_data)!==false);
    }

    /**
     * Checks if current node has children.
     * Returns whether the current element has children.
     *
     * @return boolean
     */
    public function hasChildren(): bool
    {
        if ($this->current() instanceof Zend_Ldap_Node) {
            return $this->current()->hasChildren();
        } else {
            return false;
        }
    }

    /**
     * Returns the children for the current node.
     *
     * @return Zend_Ldap_Node_ChildrenIterator
     */
    public function getChildren(): ?\RecursiveIterator
    {
        if ($this->current() instanceof Zend_Ldap_Node) {
            return $this->current()->getChildren();
        } else {
            return null;
        }
    }

    /**
     * Returns a child with a given RDN.
     * Implements ArrayAccess.
     *
     * @param  string $rdn
     * @return Zend_Ldap_node
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($rdn)
    {
        if ($this->offsetExists($rdn)) {
            return $this->_data[$rdn];
        } else {
            return null;
        }
    }

    /**
     * Checks whether a given rdn exists.
     * Implements ArrayAccess.
     *
     * @param  string $rdn
     * @return boolean
     */
    public function offsetExists($rdn): bool
    {
        return (array_key_exists($rdn, $this->_data));
    }

    /**
     * Does nothing.
     * Implements ArrayAccess.
     *
     * @param  string $name
     * @return null
     */
    public function offsetUnset($name): void { }

    /**
     * Does nothing.
     * Implements ArrayAccess.
     *
     * @param  string $name
     * @param  mixed $value
     * @return null
     */
    public function offsetSet($name, $value): void { }

    /**
     * Get all children as an array
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];
        foreach ($this as $rdn => $node) {
            $data[$rdn] = $node;
        }
        return $data;
    }
}
