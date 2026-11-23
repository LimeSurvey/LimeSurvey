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
 * @subpackage Statement
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @see Zend_Db_Statement
 */
require_once 'Zend/Db/Statement.php';


/**
 * Extends for Mysqli
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Statement
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Statement_Mysqli extends Zend_Db_Statement
{

    /**
     * Column names.
     *
     * @var array
     */
    protected $_keys;

    /**
     * Fetched result values.
     *
     * @var array
     */
    protected $_values;

    /**
     * Result set metadata, false for a statement without a result set,
     * null before the first execution.
     *
     * @var mysqli_result|false|null
     */
    protected $_meta = null;

    /**
     * @param  string $sql
     * @return void
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    public function _prepare($sql)
    {
        $mysqli = $this->_adapter->getConnection();

        try {
            $this->_stmt = $mysqli->prepare($sql);
        } catch (mysqli_sql_exception $e) {
            // PHP 8.1+ throws by default (MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT)
            require_once 'Zend/Db/Statement/Mysqli/Exception.php';
            throw new Zend_Db_Statement_Mysqli_Exception("Mysqli prepare error: " . $e->getMessage(), $e->getCode(), $e);
        }

        if ($this->_stmt === false || $mysqli->errno) {
            /**
             * @see Zend_Db_Statement_Mysqli_Exception
             */
            require_once 'Zend/Db/Statement/Mysqli/Exception.php';
            throw new Zend_Db_Statement_Mysqli_Exception("Mysqli prepare error: " . $mysqli->error, $mysqli->errno);
        }
    }

    /**
     * Binds a parameter to the specified variable name.
     *
     * @param mixed $parameter Name the parameter, either integer or string.
     * @param mixed $variable  Reference to PHP variable containing the value.
     * @param mixed $type      OPTIONAL Datatype of SQL parameter.
     * @param mixed $length    OPTIONAL Length of SQL parameter.
     * @param mixed $options   OPTIONAL Other options.
     * @return bool
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    protected function _bindParam($parameter, &$variable, $type = null, $length = null, $options = null)
    {
        return true;
    }

    /**
     * Closes the cursor and the statement.
     *
     * @return bool
     */
    public function close()
    {
        if ($this->_stmt) {
            $r = $this->_stmt->close();
            $this->_stmt = null;
            return $r;
        }
        return false;
    }

    /**
     * Closes the cursor, allowing the statement to be executed again.
     *
     * @return bool
     */
    public function closeCursor()
    {
        if ($stmt = $this->_stmt) {
            $mysqli = $this->_adapter->getConnection();
            while ($mysqli->more_results()) {
                $mysqli->next_result();
            }
            $this->_stmt->free_result();
            return $this->_stmt->reset();
        }
        return false;
    }

    /**
     * Returns the number of columns in the result set.
     * Returns null if the statement has no result set metadata.
     *
     * @return int The number of columns.
     */
    public function columnCount()
    {
        if (isset($this->_meta) && $this->_meta) {
            return $this->_meta->field_count;
        }
        return 0;
    }

    /**
     * Returns metadata for a column in a result set, in the format of
     * PDOStatement::getColumnMeta() of pdo_mysql: native_type, flags, table,
     * name, len, precision and pdo_type. The pdo_type is the type of the
     * values fetched: integer columns come as integers from mysqli.
     *
     * @param int $column Zero-based index of the column.
     * @return array|false False when the statement has no result set or no such column.
     */
    public function getColumnMeta($column)
    {
        $column = (int) $column;
        if (!$this->_meta || $column < 0 || $column >= $this->_meta->field_count) {
            return false;
        }

        $field = $this->_meta->fetch_field_direct($column);
        if (!$field) {
            return false;
        }

        $meta = [];
        $nativeTypes = [
            MYSQLI_TYPE_STRING      => 'STRING',
            MYSQLI_TYPE_VAR_STRING  => 'VAR_STRING',
            MYSQLI_TYPE_TINY        => 'TINY',
            MYSQLI_TYPE_BIT         => 'BIT',
            MYSQLI_TYPE_SHORT       => 'SHORT',
            MYSQLI_TYPE_LONG        => 'LONG',
            MYSQLI_TYPE_LONGLONG    => 'LONGLONG',
            MYSQLI_TYPE_INT24       => 'INT24',
            MYSQLI_TYPE_FLOAT       => 'FLOAT',
            MYSQLI_TYPE_DOUBLE      => 'DOUBLE',
            MYSQLI_TYPE_DECIMAL     => 'DECIMAL',
            MYSQLI_TYPE_NEWDECIMAL  => 'NEWDECIMAL',
            MYSQLI_TYPE_GEOMETRY    => 'GEOMETRY',
            MYSQLI_TYPE_TIMESTAMP   => 'TIMESTAMP',
            MYSQLI_TYPE_YEAR        => 'YEAR',
            MYSQLI_TYPE_SET         => 'SET',
            MYSQLI_TYPE_ENUM        => 'ENUM',
            MYSQLI_TYPE_DATE        => 'DATE',
            MYSQLI_TYPE_NEWDATE     => 'NEWDATE',
            MYSQLI_TYPE_TIME        => 'TIME',
            MYSQLI_TYPE_DATETIME    => 'DATETIME',
            MYSQLI_TYPE_TINY_BLOB   => 'TINY_BLOB',
            MYSQLI_TYPE_MEDIUM_BLOB => 'MEDIUM_BLOB',
            MYSQLI_TYPE_LONG_BLOB   => 'LONG_BLOB',
            MYSQLI_TYPE_BLOB        => 'BLOB',
            MYSQLI_TYPE_NULL        => 'NULL',
        ];
        // pdo_mysql leaves the key out for the types it has no name for
        if (isset($nativeTypes[$field->type])) {
            $meta['native_type'] = $nativeTypes[$field->type];
        }

        $flags = [];
        $flagNames = [
            MYSQLI_NOT_NULL_FLAG     => 'not_null',
            MYSQLI_PRI_KEY_FLAG      => 'primary_key',
            MYSQLI_MULTIPLE_KEY_FLAG => 'multiple_key',
            MYSQLI_UNIQUE_KEY_FLAG   => 'unique_key',
            MYSQLI_BLOB_FLAG         => 'blob',
        ];
        foreach ($flagNames as $flag => $name) {
            if ($field->flags & $flag) {
                $flags[] = $name;
            }
        }
        $meta['flags'] = $flags;

        $meta['table']     = $field->table;
        $meta['name']      = $field->name;
        $meta['len']       = $field->length;
        $meta['precision'] = $field->decimals;

        $integerTypes = [
            MYSQLI_TYPE_BIT, MYSQLI_TYPE_YEAR, MYSQLI_TYPE_TINY, MYSQLI_TYPE_SHORT,
            MYSQLI_TYPE_INT24, MYSQLI_TYPE_LONG,
        ];
        if (PHP_INT_SIZE == 8) {
            $integerTypes[] = MYSQLI_TYPE_LONGLONG;
        }
        $meta['pdo_type'] = in_array($field->type, $integerTypes, true) ? Zend_Db::PARAM_INT : Zend_Db::PARAM_STR;

        return $meta;
    }

    /**
     * Retrieves the error code, if any, associated with the last operation on
     * the statement handle.
     *
     * @return string error code.
     */
    public function errorCode()
    {
        if (!$this->_stmt) {
            return false;
        }
        return substr($this->_stmt->sqlstate, 0, 5);
    }

    /**
     * Retrieves an array of error information, if any, associated with the
     * last operation on the statement handle.
     *
     * @return array
     */
    public function errorInfo()
    {
        if (!$this->_stmt) {
            return false;
        }
        return [
            substr($this->_stmt->sqlstate, 0, 5),
            $this->_stmt->errno,
            $this->_stmt->error,
        ];
    }

    /**
     * Executes a prepared statement.
     *
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    public function _execute(?array $params = null)
    {
        if (!$this->_stmt) {
            return false;
        }

        // if no params were given as an argument to execute(),
        // then default to the _bindParam array
        if ($params === null) {
            $params = $this->_bindParam;
        }
        try {
            return $this->_executeStatement($params);
        } catch (mysqli_sql_exception $e) {
            // PHP 8.1+ throws by default (MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT)
            require_once 'Zend/Db/Statement/Mysqli/Exception.php';
            throw new Zend_Db_Statement_Mysqli_Exception("Mysqli statement execute error : " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Binds the parameters, executes the statement and binds the result.
     *
     * @param array|null $params Values to bind to parameter placeholders.
     * @return bool
     * @throws Zend_Db_Statement_Mysqli_Exception
     * @throws mysqli_sql_exception
     */
    protected function _executeStatement($params)
    {
        // send $params as input parameters to the statement
        if ($params) {
            array_unshift($params, str_repeat('s', count($params)));
            $stmtParams = [];
            foreach ($params as $k => &$value) {
                $stmtParams[$k] = &$value;
            }
            call_user_func_array(
                [$this->_stmt, 'bind_param'],
                array_values($stmtParams)
                );
        }

        // execute the statement
        $retval = $this->_stmt->execute();
        if ($retval === false) {
            /**
             * @see Zend_Db_Statement_Mysqli_Exception
             */
            require_once 'Zend/Db/Statement/Mysqli/Exception.php';
            throw new Zend_Db_Statement_Mysqli_Exception("Mysqli statement execute error : " . $this->_stmt->error, $this->_stmt->errno);
        }


        // retain metadata
        if ($this->_meta === null) {
            $this->_meta = $this->_stmt->result_metadata();
            if ($this->_stmt->errno) {
                /**
                 * @see Zend_Db_Statement_Mysqli_Exception
                 */
                require_once 'Zend/Db/Statement/Mysqli/Exception.php';
                throw new Zend_Db_Statement_Mysqli_Exception("Mysqli statement metadata error: " . $this->_stmt->error, $this->_stmt->errno);
            }
        }

        // statements that have no result set do not return metadata
        if ($this->_meta !== false) {

            // get the column names that will result
            $this->_keys = [];
            foreach ($this->_meta->fetch_fields() as $col) {
                $this->_keys[] = $this->_adapter->foldCase($col->name);
            }

            // set up a binding space for result variables
            $this->_values = array_fill(0, count($this->_keys), null);

            // set up references to the result binding space.
            // just passing $this->_values in the call_user_func_array()
            // below won't work, you need references.
            $refs = [];
            foreach ($this->_values as $i => &$f) {
                $refs[$i] = &$f;
            }

            $this->_stmt->store_result();
            // bind to the result variables
            call_user_func_array(
                [$this->_stmt, 'bind_result'],
                $this->_values
            );
        }
        return $retval;
    }


    /**
     * Fetches a row from the result set.
     *
     * @param int $style  OPTIONAL Fetch mode for this fetch operation.
     * @param int $cursor OPTIONAL Absolute, relative, or other.
     * @param int $offset OPTIONAL Number for absolute or relative cursors.
     * @return array|bool|object Array, object, or scalar depending on fetch mode.
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    public function fetch($style = null, $cursor = null, $offset = null)
    {
        if (!$this->_stmt) {
            return false;
        }
        // fetch the next result
        $retval = $this->_stmt->fetch();
        switch ($retval) {
            case null: // end of data
            case false: // error occurred
                $this->_stmt->reset();
                return false;
            default:
                // fallthrough
        }

        // make sure we have a fetch mode
        if ($style === null) {
            $style = $this->_fetchMode;
        }

        // dereference the result values, otherwise things like fetchAll()
        // return the same values for every entry (because of the reference).
        $values = [];
        foreach ($this->_values as $key => $val) {
            $values[] = $val;
        }

        $row = false;
        switch ($style) {
            case Zend_Db::FETCH_NUM:
                $row = $values;
                break;
            case Zend_Db::FETCH_ASSOC:
                $row = array_combine($this->_keys, $values);
                break;
            case Zend_Db::FETCH_BOTH:
                $assoc = array_combine($this->_keys, $values);
                $row = array_merge($values, $assoc);
                break;
            case Zend_Db::FETCH_OBJ:
                $row = (object) array_combine($this->_keys, $values);
                break;
            case Zend_Db::FETCH_BOUND:
                $assoc = array_combine($this->_keys, $values);
                $row = array_merge($values, $assoc);
                return $this->_fetchBound($row);
                break;
            default:
                /**
                 * @see Zend_Db_Statement_Mysqli_Exception
                 */
                require_once 'Zend/Db/Statement/Mysqli/Exception.php';
                throw new Zend_Db_Statement_Mysqli_Exception("Invalid fetch mode '$style' specified");
                break;
        }
        return $row;
    }

    /**
     * Retrieves the next rowset (result set) for a SQL statement that has
     * multiple result sets.  An example is a stored procedure that returns
     * the results of multiple queries.
     *
     * @return bool
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    public function nextRowset()
    {
        /**
         * @see Zend_Db_Statement_Mysqli_Exception
         */
        require_once 'Zend/Db/Statement/Mysqli/Exception.php';
        throw new Zend_Db_Statement_Mysqli_Exception(__FUNCTION__.'() is not implemented');
    }

    /**
     * Returns the number of rows affected by the execution of the
     * last INSERT, DELETE, or UPDATE statement executed by this
     * statement object.
     *
     * @return int     The number of rows affected.
     */
    public function rowCount()
    {
        if (!$this->_adapter) {
            return false;
        }
        $mysqli = $this->_adapter->getConnection();
        return $mysqli->affected_rows;
    }

}
