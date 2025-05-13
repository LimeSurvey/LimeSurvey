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
 * @package    Zend_Db
 * @subpackage Table
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Table
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Db_Table_Rowset_Abstract implements SeekableIterator, Countable, ArrayAccess
{
    /**
     * The original data for each row.
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Zend_Db_Table_Abstract object.
     *
     * @var Zend_Db_Table_Abstract
     */
    protected $_table;

    /**
     * Connected is true if we have a reference to a live
     * Zend_Db_Table_Abstract object.
     * This is false after the Rowset has been deserialized.
     *
     * @var boolean
     */
    protected $_connected = true;

    /**
     * Zend_Db_Table_Abstract class name.
     *
     * @var string
     */
    protected $_tableClass;

    /**
     * Zend_Db_Table_Row_Abstract class name.
     *
     * @var string
     */
    protected $_rowClass = 'Zend_Db_Table_Row';

    /**
     * Iterator pointer.
     *
     * @var integer
     */
    protected $_pointer = 0;

    /**
     * How many data rows there are.
     *
     * @var integer
     */
    protected $_count;

    /**
     * Collection of instantiated Zend_Db_Table_Row objects.
     *
     * @var array
     */
    protected $_rows = [];

    /**
     * @var boolean
     */
    protected $_stored = false;

    /**
     * @var boolean
     */
    protected $_readOnly = false;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (isset($config['table'])) {
            $this->_table      = $config['table'];
            $this->_tableClass = get_class($this->_table);
        }
        if (isset($config['rowClass'])) {
            $this->_rowClass   = $config['rowClass'];
        }
        if (!class_exists($this->_rowClass)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($this->_rowClass);
        }
        if (isset($config['data'])) {
            $this->_data       = $config['data'];
        }
        if (isset($config['readOnly'])) {
            $this->_readOnly   = $config['readOnly'];
        }
        if (isset($config['stored'])) {
            $this->_stored     = $config['stored'];
        }

        // set the count of rows
        $this->_count = count($this->_data);

        $this->init();
    }

    /**
     * Store data, class names, and state in serialized object
     *
     * @return array
     */
    public function __sleep()
    {
        return ['_data', '_tableClass', '_rowClass', '_pointer', '_count', '_rows', '_stored',
                     '_readOnly'];
    }

    /**
     * Setup to do on wakeup.
     * A de-serialized Rowset should not be assumed to have access to a live
     * database connection, so set _connected = false.
     *
     * @return void
     */
    public function __wakeup()
    {
        $this->_connected = false;
    }

    /**
     * Initialize object
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * Return the connected state of the rowset.
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->_connected;
    }

    /**
     * Returns the table object, or null if this is disconnected rowset
     *
     * @return Zend_Db_Table_Abstract
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * Set the table object, to re-establish a live connection
     * to the database for a Rowset that has been de-serialized.
     *
     * @param Zend_Db_Table_Abstract $table
     * @return boolean
     * @throws Zend_Db_Table_Row_Exception
     */
    public function setTable(Zend_Db_Table_Abstract $table)
    {
        $this->_table = $table;
        $this->_connected = false;
        // @todo This works only if we have iterated through
        // the result set once to instantiate the rows.
        foreach ($this as $row) {
            $connected = $row->setTable($table);
            if ($connected == true) {
                $this->_connected = true;
            }
        }
        $this->rewind();
        return $this->_connected;
    }

    /**
     * Query the class name of the Table object for which this
     * Rowset was created.
     *
     * @return string
     */
    public function getTableClass()
    {
        return $this->_tableClass;
    }

    /**
     * Rewind the Iterator to the first element.
     * Similar to the reset() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind(): void
    {
        $this->_pointer = 0;
    }

    /**
     * Return the current element.
     * Similar to the current() function for arrays in PHP
     * Required by interface Iterator.
     *
     * @return Zend_Db_Table_Row_Abstract|null current element from the collection
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        if ($this->valid() === false) {
            return null;
        }

        // return the row object
        return $this->_loadAndReturnRow($this->_pointer);
    }

    /**
     * Return the identifying key of the current element.
     * Similar to the key() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function key(): int
    {
        return $this->_pointer;
    }

    /**
     * Move forward to next element.
     * Similar to the next() function for arrays in PHP.
     * Required by interface Iterator.
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function next(): void
    {
        ++$this->_pointer;
    }

    /**
     * Check if there is a current element after calls to rewind() or next().
     * Used to check if we've iterated to the end of the collection.
     * Required by interface Iterator.
     *
     * @return bool False if there's nothing more to iterate over
     */
    #[\ReturnTypeWillChange]
    public function valid(): bool
    {
        return $this->_pointer >= 0 && $this->_pointer < $this->_count;
    }

    /**
     * Returns the number of elements in the collection.
     *
     * Implements Countable::count()
     *
     * @return int
     */
    #[\ReturnTypeWillChange]
    public function count(): int
    {
        return $this->_count;
    }

    /**
     * Take the Iterator to position $position
     * Required by interface SeekableIterator.
     *
     * @param int $position the position to seek to
     * @return void
     * @throws Zend_Db_Table_Rowset_Exception
     */
    public function seek($position): void
    {
        $position = (int) $position;
        if ($position < 0 || $position >= $this->_count) {
            require_once 'Zend/Db/Table/Rowset/Exception.php';
            throw new Zend_Db_Table_Rowset_Exception("Illegal index $position");
        }
        $this->_pointer = $position;
    }

    /**
     * Check if an offset exists
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return boolean
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool
    {
        return isset($this->_data[(int) $offset]);
    }

    /**
     * Get the row for the given offset
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @return Zend_Db_Table_Row_Abstract
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        $offset = (int) $offset;
        if ($offset < 0 || $offset >= $this->_count) {
            require_once 'Zend/Db/Table/Rowset/Exception.php';
            throw new Zend_Db_Table_Rowset_Exception("Illegal index $offset");
        }
        $this->_pointer = $offset;

        return $this->current();
    }

    /**
     * Does nothing
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     * @param mixed $value
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void
    {
    }

    /**
     * Does nothing
     * Required by the ArrayAccess implementation
     *
     * @param string $offset
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void
    {
    }

    /**
     * Returns a Zend_Db_Table_Row from a known position into the Iterator
     *
     * @param int $position the position of the row expected
     * @param bool $seek wether or not seek the iterator to that position after
     * @return Zend_Db_Table_Row
     * @throws Zend_Db_Table_Rowset_Exception
     */
    public function getRow($position, $seek = false)
    {
        try {
            $row = $this->_loadAndReturnRow($position);
        } catch (Zend_Db_Table_Rowset_Exception $e) {
            require_once 'Zend/Db/Table/Rowset/Exception.php';
            throw new Zend_Db_Table_Rowset_Exception('No row could be found at position ' . (int) $position, 0, $e);
        }

        if ($seek == true) {
            $this->seek($position);
        }

        return $row;
    }

    /**
     * Returns all data as an array.
     *
     * Updates the $_data property with current row object values.
     *
     * @return array
     */
    public function toArray()
    {
        // @todo This works only if we have iterated through
        // the result set once to instantiate the rows.
        foreach ($this->_rows as $i => $row) {
            $this->_data[$i] = $row->toArray();
        }
        return $this->_data;
    }

    protected function _loadAndReturnRow($position)
    {
        if (!isset($this->_data[$position])) {
            require_once 'Zend/Db/Table/Rowset/Exception.php';
            throw new Zend_Db_Table_Rowset_Exception("Data for provided position does not exist");
        }

        // do we already have a row object for this position?
        if (empty($this->_rows[$position])) {
            $this->_rows[$position] = new $this->_rowClass(
                [
                    'table'    => $this->_table,
                    'data'     => $this->_data[$position],
                    'stored'   => $this->_stored,
                    'readOnly' => $this->_readOnly
                ]
            );

            if ( $this->_table instanceof Zend_Db_Table_Abstract ) {
                $info = $this->_table->info();

                if ( $this->_rows[$position] instanceof Zend_Db_Table_Row_Abstract ) {
                    if ($info['cols'] == array_keys($this->_data[$position])) {
                        $this->_rows[$position]->setTable($this->getTable());
                    }
                }
            } else {
                $this->_rows[$position]->setTable(null);
            }
        }

        // return the row object
        return $this->_rows[$position];
    }

}
