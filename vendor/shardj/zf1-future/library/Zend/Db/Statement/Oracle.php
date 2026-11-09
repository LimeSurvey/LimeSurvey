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
 * Extends for Oracle.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Statement
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Statement_Oracle extends Zend_Db_Statement
{

    /**
     * Column names.
     */
    protected $_keys;

    /**
     * Fetched result values.
     */
    protected $_values;

    /**
     * Check if LOB field are returned as string
     * instead of OCILob objects (OCI-Lob before PHP 8)
     *
     * @var boolean
     */
    protected $_lobAsString = false;

    /**
     * Activate/deactivate return of LOB as string
     *
     * @param string $lob_as_string
     * @return $this
     */
    public function setLobAsString($lob_as_string)
    {
        $this->_lobAsString = (bool) $lob_as_string;
        return $this;
    }

    /**
     * Return whether or not LOB are returned as string
     *
     * @return boolean
     */
    public function getLobAsString()
    {
        return $this->_lobAsString;
    }

    /**
     * Prepares statement handle
     *
     * @param string $sql
     * @return void
     * @throws Zend_Db_Statement_Oracle_Exception
     */
    protected function _prepare($sql)
    {
        $connection = $this->_adapter->getConnection();
        $this->_stmt = @oci_parse($connection, $sql);
        if (!$this->_stmt) {
            /**
             * @see Zend_Db_Statement_Oracle_Exception
             */
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception(oci_error($connection));
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
     * @throws Zend_Db_Statement_Exception
     */
    protected function _bindParam($parameter, &$variable, $type = null, $length = null, $options = null)
    {
        // default value
        if ($type === NULL) {
            $type = SQLT_CHR;
        }

        // default value
        if ($length === NULL) {
            $length = -1;
        }

        $retval = @oci_bind_by_name($this->_stmt, $parameter, $variable, $length, $type);
        if ($retval === false) {
            /**
             * @see Zend_Db_Adapter_Oracle_Exception
             */
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception(oci_error($this->_stmt));
        }

        return true;
    }

    /**
     * Closes the cursor, allowing the statement to be executed again.
     *
     * @return bool
     */
    public function closeCursor()
    {
        if (!$this->_stmt) {
            return false;
        }

        oci_free_statement($this->_stmt);
        $this->_stmt = false;
        return true;
    }

    /**
     * Returns the number of columns in the result set.
     * Returns null if the statement has no result set metadata.
     *
     * @return int The number of columns.
     */
    public function columnCount()
    {
        if (!$this->_stmt) {
            return false;
        }

        return oci_num_fields($this->_stmt);
    }

    /**
     * Returns metadata for a column in a result set, in the format of
     * PDOStatement::getColumnMeta() of pdo_oci as far as oci8 reports it:
     * native_type, name, len, precision, scale and pdo_type. oci8 reports
     * neither the table nor the flags of a column, and the native_type of a
     * type it has no name for is the Oracle type code. The pdo_type is the
     * type of the values fetched: LOB columns come as OCILob objects, or as
     * strings with the lob_as_string option.
     *
     * @param int $column Zero-based index of the column.
     * @return array|false False when the statement has no result set or no such column.
     */
    public function getColumnMeta($column)
    {
        $column = (int) $column;
        if (!$this->_stmt || $column < 0 || $column >= oci_num_fields($this->_stmt)) {
            return false;
        }

        $field = $column + 1;
        $nativeType = oci_field_type($this->_stmt, $field);
        $isLob = in_array($nativeType, ['BLOB', 'CLOB', 'BFILE'], true);

        return [
            'native_type' => $nativeType,
            'name'        => $this->_adapter->foldCase(oci_field_name($this->_stmt, $field)),
            'len'         => oci_field_size($this->_stmt, $field),
            'precision'   => oci_field_precision($this->_stmt, $field),
            'scale'       => oci_field_scale($this->_stmt, $field),
            'pdo_type'    => $isLob && !$this->getLobAsString() ? Zend_Db::PARAM_LOB : Zend_Db::PARAM_STR,
        ];
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

        $error = oci_error($this->_stmt);

        if (!$error) {
            return false;
        }

        return $error['code'];
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

        $error = oci_error($this->_stmt);
        if (!$error) {
            return false;
        }

        if (isset($error['sqltext'])) {
            return [
                $error['code'],
                $error['message'],
                $error['offset'],
                $error['sqltext'],
            ];
        } else {
            return [
                $error['code'],
                $error['message'],
            ];
        }
    }


    /**
     * Executes a prepared statement.
     *
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function _execute(?array $params = null)
    {
        $connection = $this->_adapter->getConnection();

        if (!$this->_stmt) {
            return false;
        }

        if ($params !== null) {
            if (!is_array($params)) {
                $params = [$params];
            }
            $error = false;
            foreach (array_keys($params) as $name) {
                if (!$this->bindParam($name, $params[$name], null, -1)) {
                    $error = true;
                    break;
                }
            }
            if ($error) {
                /**
                 * @see Zend_Db_Adapter_Oracle_Exception
                 */
                require_once 'Zend/Db/Statement/Oracle/Exception.php';
                throw new Zend_Db_Statement_Oracle_Exception(oci_error($this->_stmt));
            }
        }

        $retval = @oci_execute($this->_stmt, $this->_adapter->_getExecuteMode());
        if ($retval === false) {
            /**
             * @see Zend_Db_Adapter_Oracle_Exception
             */
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception(oci_error($this->_stmt));
        }

        $this->_keys = [];
        if ($field_num = oci_num_fields($this->_stmt)) {
            for ($i = 1; $i <= $field_num; $i++) {
                $name = oci_field_name($this->_stmt, $i);
                $this->_keys[] = $name;
            }
        }

        $this->_values = [];
        if ($this->_keys) {
            $this->_values = array_fill(0, count($this->_keys), null);
        }

        return $retval;
    }

    /**
     * Fetches a row from the result set.
     *
     * @param int $style  OPTIONAL Fetch mode for this fetch operation.
     * @param int $cursor OPTIONAL Absolute, relative, or other.
     * @param int $offset OPTIONAL Number for absolute or relative cursors.
     * @return mixed Array, object, or scalar depending on fetch mode.
     * @throws Zend_Db_Statement_Exception
     */
    public function fetch($style = null, $cursor = null, $offset = null)
    {
        if (!$this->_stmt) {
            return false;
        }

        if ($style === null) {
            $style = $this->_fetchMode;
        }

        $lob_as_string = $this->getLobAsString() ? OCI_RETURN_LOBS : 0;

        switch ($style) {
            case Zend_Db::FETCH_NUM:
                $row = oci_fetch_array($this->_stmt, OCI_NUM | OCI_RETURN_NULLS | $lob_as_string);
                break;
            case Zend_Db::FETCH_ASSOC:
                $row = oci_fetch_array($this->_stmt, OCI_ASSOC | OCI_RETURN_NULLS | $lob_as_string);
                break;
            case Zend_Db::FETCH_BOTH:
                $row = oci_fetch_array($this->_stmt, OCI_BOTH | OCI_RETURN_NULLS | $lob_as_string);
                break;
            case Zend_Db::FETCH_OBJ:
                $row = oci_fetch_object($this->_stmt);
                break;
            case Zend_Db::FETCH_BOUND:
                $row = oci_fetch_array($this->_stmt, OCI_BOTH | OCI_RETURN_NULLS | $lob_as_string);
                if ($row !== false) {
                    return $this->_fetchBound($row);
                }
                break;
            default:
                /**
                 * @see Zend_Db_Adapter_Oracle_Exception
                 */
                require_once 'Zend/Db/Statement/Oracle/Exception.php';
                throw new Zend_Db_Statement_Oracle_Exception(
                    [
                        'code'    => 'HYC00',
                        'message' => "Invalid fetch mode '$style' specified"
                    ]
                );
                break;
        }

        if (! $row && $error = oci_error($this->_stmt)) {
            /**
             * @see Zend_Db_Adapter_Oracle_Exception
             */
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception($error);
        }

        if (is_array($row) && array_key_exists('zend_db_rownum', $row)) {
            unset($row['zend_db_rownum']);
        }

        return $this->_foldCase($row);
    }

    /**
     * Returns an array containing all of the result set rows.
     *
     * @param int $style OPTIONAL Fetch mode.
     * @param int $col   OPTIONAL Column number, if fetch mode is by column.
     * @return array Collection of rows, each in a format by the fetch mode.
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchAll($style = null, $col = 0)
    {
        if (!$this->_stmt) {
            return false;
        }

        // make sure we have a fetch mode
        if ($style === null) {
            $style = $this->_fetchMode;
        }

        // oci_fetch_all() has no mode with both numeric and named keys
        if ($style == Zend_Db::FETCH_BOTH) {
            $result = [];
            while (($row = $this->fetch(Zend_Db::FETCH_BOTH)) !== false) {
                $result[] = $row;
            }
            return $result;
        }

        $flags = OCI_FETCHSTATEMENT_BY_ROW;

        switch ($style) {
            case Zend_Db::FETCH_NUM:
                $flags |= OCI_NUM;
                break;
            case Zend_Db::FETCH_ASSOC:
                $flags |= OCI_ASSOC;
                break;
            case Zend_Db::FETCH_OBJ:
                break;
            case Zend_Db::FETCH_COLUMN:
                $flags = $flags &~ OCI_FETCHSTATEMENT_BY_ROW;
                $flags |= OCI_FETCHSTATEMENT_BY_COLUMN;
                $flags |= OCI_NUM;
                break;
            default:
                /**
                 * @see Zend_Db_Adapter_Oracle_Exception
                 */
                require_once 'Zend/Db/Statement/Oracle/Exception.php';
                throw new Zend_Db_Statement_Oracle_Exception(
                    [
                        'code'    => 'HYC00',
                        'message' => "Invalid fetch mode '$style' specified"
                    ]
                );
                break;
        }

        $result = [];
        if ($flags != OCI_FETCHSTATEMENT_BY_ROW) { /* not Zend_Db::FETCH_OBJ */
            if (! ($rows = oci_fetch_all($this->_stmt, $result, 0, -1, $flags) )) {
                if ($error = oci_error($this->_stmt)) {
                    /**
                     * @see Zend_Db_Adapter_Oracle_Exception
                     */
                    require_once 'Zend/Db/Statement/Oracle/Exception.php';
                    throw new Zend_Db_Statement_Oracle_Exception($error);
                }
                if (!$rows) {
                    return [];
                }
            }
            if ($style == Zend_Db::FETCH_COLUMN) {
                $result = $result[$col];
            }
            foreach ($result as &$row) {
                if (is_array($row) && array_key_exists('zend_db_rownum', $row)) {
                    unset($row['zend_db_rownum']);
                }
            }
        } else {
            while (($row = oci_fetch_object($this->_stmt)) !== false) {
                $result [] = $row;
            }
            if ($error = oci_error($this->_stmt)) {
                /**
                 * @see Zend_Db_Adapter_Oracle_Exception
                 */
                require_once 'Zend/Db/Statement/Oracle/Exception.php';
                throw new Zend_Db_Statement_Oracle_Exception($error);
            }
        }

        if ($style != Zend_Db::FETCH_COLUMN) {
            $result = array_map([$this, '_foldCase'], $result);
        }

        return $result;
    }

    /**
     * Folds the column names of a row by the CASE_FOLDING option of the adapter,
     * which oci8 does not do itself.
     *
     * @param mixed $row Array, object, or false
     * @return mixed
     */
    protected function _foldCase($row)
    {
        if ($this->_adapter->foldCase('Zend') === 'Zend') {
            return $row;
        }

        if (is_array($row)) {
            $folded = [];
            foreach ($row as $key => $value) {
                $folded[is_string($key) ? $this->_adapter->foldCase($key) : $key] = $value;
            }
            return $folded;
        }

        if (is_object($row)) {
            $folded = new stdClass();
            foreach (get_object_vars($row) as $key => $value) {
                $folded->{$this->_adapter->foldCase($key)} = $value;
            }
            return $folded;
        }

        return $row;
    }


    /**
     * Returns a single column from the next row of a result set.
     *
     * @param int $col OPTIONAL Position of the column to fetch.
     * @return string
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchColumn($col = 0)
    {
        if (!$this->_stmt) {
            return false;
        }

        if (!oci_fetch($this->_stmt)) {
            // if no error, there is simply no record
            if (!$error = oci_error($this->_stmt)) {
                return false;
            }
            /**
             * @see Zend_Db_Adapter_Oracle_Exception
             */
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception($error);
        }

        $data = oci_result($this->_stmt, $col+1); //1-based
        if ($data === false) {
            /**
             * @see Zend_Db_Adapter_Oracle_Exception
             */
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception(oci_error($this->_stmt));
        }

        if ($this->getLobAsString()) {
            // the LOB class is OCILob since PHP 8 and OCI-Lob before
            $type = class_exists('OCILob', false) ? 'OCILob' : 'OCI-Lob';
            if ($data instanceof $type) {
                $data = $data->read($data->size());
            }
        }

        return $data;
    }

    /**
     * Fetches the next row and returns it as an object.
     *
     * @param string $class  OPTIONAL Name of the class to create.
     * @param array  $config OPTIONAL Constructor arguments for the class.
     * @return false|object One object instance of the specified class.
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchObject($class = 'stdClass', array $config = [])
    {
        if (!$this->_stmt) {
            return false;
        }

        $obj = oci_fetch_object($this->_stmt);

        if ($error = oci_error($this->_stmt)) {
            /**
             * @see Zend_Db_Adapter_Oracle_Exception
             */
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception($error);
        }

        /* @todo XXX handle parameters */

        return $obj;
    }

    /**
     * Retrieves the next rowset (result set) for a SQL statement that has
     * multiple result sets.  An example is a stored procedure that returns
     * the results of multiple queries.
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function nextRowset()
    {
        /**
         * @see Zend_Db_Statement_Oracle_Exception
         */
        require_once 'Zend/Db/Statement/Oracle/Exception.php';
        throw new Zend_Db_Statement_Oracle_Exception(
            [
                'code'    => 'HYC00',
                'message' => 'Optional feature not implemented'
            ]
        );
    }

    /**
     * Returns the number of rows affected by the execution of the
     * last INSERT, DELETE, or UPDATE statement executed by this
     * statement object.
     *
     * @return int     The number of rows affected.
     * @throws Zend_Db_Statement_Exception
     */
    public function rowCount()
    {
        if (!$this->_stmt) {
            return false;
        }

        $num_rows = oci_num_rows($this->_stmt);

        if ($num_rows === false) {
            /**
             * @see Zend_Db_Adapter_Oracle_Exception
             */
            require_once 'Zend/Db/Statement/Oracle/Exception.php';
            throw new Zend_Db_Statement_Oracle_Exception(oci_error($this->_stmt));
        }

        return $num_rows;
    }

}
