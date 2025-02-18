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
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * Class for Database record validation
 *
 * @category   Zend
 * @package    Zend_Validate
 * @uses       Zend_Validate_Abstract
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Validate_Db_Abstract extends Zend_Validate_Abstract
{
    /**
     * Error constants
     */
    public const ERROR_NO_RECORD_FOUND = 'noRecordFound';
    public const ERROR_RECORD_FOUND    = 'recordFound';

    /**
     * @var array Message templates
     */
    protected $_messageTemplates = [
        self::ERROR_NO_RECORD_FOUND => "No record matching '%value%' was found",
        self::ERROR_RECORD_FOUND    => "A record matching '%value%' was found",
    ];

    /**
     * @var string
     */
    protected $_schema = null;

    /**
     * @var string
     */
    protected $_table = '';

    /**
     * @var string
     */
    protected $_field = '';

    /**
     * @var mixed
     */
    protected $_exclude = null;

    /**
     * Database adapter to use. If null isValid() will use Zend_Db::getInstance instead
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_adapter = null;

    /**
     * Select object to use. can be set, or will be auto-generated
     * @var Zend_Db_Select
     */
    protected $_select;

    /**
     * Provides basic configuration for use with Zend_Validate_Db Validators
     * Setting $exclude allows a single record to be excluded from matching.
     * Exclude can either be a String containing a where clause, or an array with `field` and `value` keys
     * to define the where clause added to the sql.
     * A database adapter may optionally be supplied to avoid using the registered default adapter.
     *
     * The following option keys are supported:
     * 'table'   => The database table to validate against
     * 'schema'  => The schema keys
     * 'field'   => The field to check for a match
     * 'exclude' => An optional where clause or field/value pair to exclude from the query
     * 'adapter' => An optional database adapter to use
     *
     * @param array|Zend_Config $options Options to use for this validator
     * @throws Zend_Validate_Exception
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Db_Select) {
            $this->setSelect($options);
            return;
        }
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (func_num_args() > 1) {
            $options       = func_get_args();
            $temp['table'] = array_shift($options);
            $temp['field'] = array_shift($options);
            if (!empty($options)) {
                $temp['exclude'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['adapter'] = array_shift($options);
            }

            $options = $temp;
        }

        if (!array_key_exists('table', $options) && !array_key_exists('schema', $options)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Table or Schema option missing!');
        }

        if (!array_key_exists('field', $options)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Field option missing!');
        }

        if (array_key_exists('adapter', $options)) {
            $this->setAdapter($options['adapter']);
        }

        if (array_key_exists('exclude', $options)) {
            $this->setExclude($options['exclude']);
        }

        $this->setField($options['field']);
        if (array_key_exists('table', $options)) {
            $this->setTable($options['table']);
        }

        if (array_key_exists('schema', $options)) {
            $this->setSchema($options['schema']);
        }
    }

    /**
     * Returns the set adapter
     *
     * @return Zend_Db_Adapter_Abstract
     * @throws Zend_Validate_Exception
     */
    public function getAdapter()
    {
        /**
         * Check for an adapter being defined. if not, fetch the default adapter.
         */
        if ($this->_adapter === null) {
            $this->_adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
            if (null === $this->_adapter) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception('No database adapter present');
            }
        }
        return $this->_adapter;
    }

    /**
     * Sets a new database adapter
     *
     * @param  Zend_Db_Adapter_Abstract $adapter
     * @throws Zend_Validate_Exception
     * @return Zend_Validate_Db_Abstract
     */
    public function setAdapter($adapter)
    {
        if (!($adapter instanceof Zend_Db_Adapter_Abstract)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Adapter option must be a database adapter!');
        }

        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * Returns the set exclude clause
     *
     * @return string|array
     */
    public function getExclude()
    {
        return $this->_exclude;
    }

    /**
     * Sets a new exclude clause
     *
     * @param string|array $exclude
     * @return Zend_Validate_Db_Abstract
     */
    public function setExclude($exclude)
    {
        $this->_exclude = $exclude;
        return $this;
    }

    /**
     * Returns the set field
     *
     * @return string
     */
    public function getField()
    {
        return $this->_field;
    }

    /**
     * Sets a new field
     *
     * @param string $field
     * @return Zend_Validate_Db_Abstract
     */
    public function setField($field)
    {
        $this->_field = (string) $field;
        return $this;
    }

    /**
     * Returns the set table
     *
     * @return string
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * Sets a new table
     *
     * @param string $table
     * @return Zend_Validate_Db_Abstract
     */
    public function setTable($table)
    {
        $this->_table = (string) $table;
        return $this;
    }

    /**
     * Returns the set schema
     *
     * @return string
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Sets a new schema
     *
     * @param string $schema
     * @return Zend_Validate_Db_Abstract
     */
    public function setSchema($schema)
    {
        $this->_schema = $schema;
        return $this;
    }

    /**
     * Sets the select object to be used by the validator
     *
     * @param Zend_Db_Select $select
     * @throws Zend_Validate_Exception
     * @return Zend_Validate_Db_Abstract
     */
    public function setSelect($select)
    {
        if (!$select instanceof Zend_Db_Select) {
            throw new Zend_Validate_Exception('Select option must be a valid ' .
                                              'Zend_Db_Select object');
        }
        $this->_select = $select;
        return $this;
    }

    /**
     * Gets the select object to be used by the validator.
     * If no select object was supplied to the constructor,
     * then it will auto-generate one from the given table,
     * schema, field, and adapter options.
     *
     * @return Zend_Db_Select The Select object which will be used
     */
    public function getSelect()
    {
        if (null === $this->_select) {
            $db = $this->getAdapter();
            /**
             * Build select object
             */
            $select = new Zend_Db_Select($db);
            $select->from($this->_table, [$this->_field], $this->_schema);
            if ($db->supportsParameters('named')) {
                $select->where($db->quoteIdentifier($this->_field, true).' = :value'); // named
            } else {
                $select->where($db->quoteIdentifier($this->_field, true).' = ?'); // positional
            }
            if ($this->_exclude !== null) {
                if (is_array($this->_exclude)) {
                    $select->where(
                          $db->quoteIdentifier($this->_exclude['field'], true) .
                            ' != ?', $this->_exclude['value']
                    );
                } else {
                    $select->where($this->_exclude);
                }
            }
            $select->limit(1);
            $this->_select = $select;
        }
        return $this->_select;
    }

    /**
     * Run query and returns matches, or null if no matches are found.
     *
     * @param  String $value
     * @return Array when matches are found.
     */
    protected function _query($value)
    {
        $select = $this->getSelect();

        // Run query
        return $select->getAdapter()->fetchRow(
            $select,
            ['value' => $value], // this should work whether db supports positional or named params
            Zend_Db::FETCH_ASSOC
        );
    }
}
