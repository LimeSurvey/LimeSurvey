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
 * @category   Akrabat
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @copyright  Copyright (c) 2010 Rob Allen (rob@akrabat.com)
 * @version    $Id: $
 */


/**
 * @see Zend_Db_Adapter_Pdo_Abstract
 */
require_once 'Zend/Db/Adapter/Pdo/Abstract.php';


/**
 * Class for connecting to Microsoft SQL Server databases and performing common operations.
 *
 * @category   Akrabat
 * @package    Zend_Db
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @copyright  Copyright (c) 2010 Rob Allen (rob@akrabat.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Adapter_Pdo_Sqlsrv extends Zend_Db_Adapter_Pdo_Abstract
{
    /**
     * PDO type.
     *
     * @var string
     */
    protected $_pdoType = 'sqlsrv';

    /**
     * Keys are UPPERCASE SQL datatypes or the constants
     * Zend_Db::INT_TYPE, Zend_Db::BIGINT_TYPE, or Zend_Db::FLOAT_TYPE.
     *
     * Values are:
     * 0 = 32-bit integer
     * 1 = 64-bit integer
     * 2 = float or decimal
     *
     * @var array Associative array of datatypes to values 0, 1, or 2.
     */
    protected $_numericDataTypes = [
        Zend_Db::INT_TYPE    => Zend_Db::INT_TYPE,
        Zend_Db::BIGINT_TYPE => Zend_Db::BIGINT_TYPE,
        Zend_Db::FLOAT_TYPE  => Zend_Db::FLOAT_TYPE,
        'INT'                => Zend_Db::INT_TYPE,
        'SMALLINT'           => Zend_Db::INT_TYPE,
        'TINYINT'            => Zend_Db::INT_TYPE,
        'BIGINT'             => Zend_Db::BIGINT_TYPE,
        'DECIMAL'            => Zend_Db::FLOAT_TYPE,
        'FLOAT'              => Zend_Db::FLOAT_TYPE,
        'MONEY'              => Zend_Db::FLOAT_TYPE,
        'NUMERIC'            => Zend_Db::FLOAT_TYPE,
        'REAL'               => Zend_Db::FLOAT_TYPE,
        'SMALLMONEY'         => Zend_Db::FLOAT_TYPE
    ];

    /**
     * Creates a PDO DSN for the adapter from $this->_config settings.
     *
     * @return string
     */
    protected function _dsn()
    {
        // baseline of DSN parts
        $dsn = $this->_config;

        if (isset($dsn['name'])) {
            $dsn = $this->_pdoType . ':' . $dsn['name'];
        } else {

            if(isset($dsn['dbname'])) {
                $dsn['Database'] = $dsn['dbname'];
                unset($dsn['dbname']);
            }

            if(isset($dsn['host'])) {
                $dsn['Server'] = $dsn['host'];
                unset($dsn['host']);
            }

            unset($dsn['username']);
            unset($dsn['password']);
            unset($dsn['options']);
            unset($dsn['charset']);
            unset($dsn['persistent']);
            unset($dsn['driver_options']);
            if (isset($dsn['ReturnDatesAsStrings'])) {
                // common sqlsrv setting but not supported by pdo_sqlsrv
                unset($dsn['ReturnDatesAsStrings']);
            }


            foreach ($dsn as $key => $val) {
                $dsn[$key] = "$key=$val";
            }

            $dsn = $this->_pdoType . ':' . implode(';', $dsn);
        }

        return $dsn;
    }

    /**
     * @return void
     */
    protected function _connect()
    {
        if ($this->_connection) {
            return;
        }
        parent::_connect();
        $this->_connection->exec('SET QUOTED_IDENTIFIER ON');
    }

    /**
     * Set the transaction isoltion level.
     *
     * @param integer|null $level A fetch mode from SQLSRV_TXN_*.
     * @return true
     * @throws Zend_Db_Adapter_Sqlsrv_Exception
     */
    public function setTransactionIsolationLevel($level = null)
    {
        $this->_connect();
        $sql = null;

        // Default transaction level in sql server
        if ($level === null)
        {
            $level = SQLSRV_TXN_READ_COMMITTED;
        }

        switch ($level) {
            case SQLSRV_TXN_READ_UNCOMMITTED:
                $sql = "READ UNCOMMITTED";
                break;
            case SQLSRV_TXN_READ_COMMITTED:
                $sql = "READ COMMITTED";
                break;
            case SQLSRV_TXN_REPEATABLE_READ:
                $sql = "REPEATABLE READ";
                break;
            case SQLSRV_TXN_SNAPSHOT:
                $sql = "SNAPSHOT";
                break;
            case SQLSRV_TXN_SERIALIZABLE:
                $sql = "SERIALIZABLE";
                break;
            default:
                require_once 'Zend/Db/Adapter/Exception.php';
                throw new Zend_Db_Adapter_Exception("Invalid transaction isolation level mode '$level' specified");
        }

        if (!$this->_connection->exec("SET TRANSACTION ISOLATION LEVEL $sql;")) {
            require_once 'Zend/Db/Adapter/Exception.php';
            throw new Zend_Db_Adapter_Exception("Transaction cannot be changed to '$level'");
        }

        return true;
    }


    /**
     * Returns a list of the tables in the database.
     *
     * @return array
     */
    public function listTables()
    {
        $sql = "SELECT name FROM sysobjects WHERE type = 'U' ORDER BY name";
        return $this->fetchCol($sql);
    }

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * PRIMARY_AUTO     => integer; position of auto-generated column in primary key
     *
     * @todo Discover column primary key position.
     * @todo Discover integer unsigned property.
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName, $schemaName = null)
    {
        if ($schemaName != null) {
            if (strpos($schemaName, '.') !== false) {
                $result = explode('.', $schemaName);
                $schemaName = $result[1];
            }
        }
        /**
         * Discover metadata information about this table.
         */
        $sql = "exec sp_columns @table_name = " . $this->quoteIdentifier($tableName, true);
        if ($schemaName != null) {
            $sql .= ", @table_owner = " . $this->quoteIdentifier($schemaName, true);
        }

        $stmt = $this->query($sql);
        $result = $stmt->fetchAll(Zend_Db::FETCH_NUM);

        $table_name  = 2;
        $column_name = 3;
        $type_name   = 5;
        $precision   = 6;
        $length      = 7;
        $scale       = 8;
        $nullable    = 10;
        $column_def  = 12;
        $column_position = 16;

        /**
         * Discover primary key column(s) for this table.
         */
        $sql = "exec sp_pkeys @table_name = " . $this->quoteIdentifier($tableName, true);
        if ($schemaName != null) {
            $sql .= ", @table_owner = " . $this->quoteIdentifier($schemaName, true);
        }

        $stmt = $this->query($sql);
        $primaryKeysResult = $stmt->fetchAll(Zend_Db::FETCH_NUM);
        $primaryKeyColumn = [];
        $pkey_column_name = 3;
        $pkey_key_seq = 4;
        foreach ($primaryKeysResult as $pkeysRow) {
            $primaryKeyColumn[$pkeysRow[$pkey_column_name]] = $pkeysRow[$pkey_key_seq];
        }

        $desc = [];
        $p = 1;
        foreach ($result as $key => $row) {
            $identity = false;
            $words = explode(' ', $row[$type_name], 2);
            if (isset($words[0])) {
                $type = $words[0];
                if (isset($words[1])) {
                    $identity = (bool) preg_match('/identity/', $words[1]);
                }
            }

            $isPrimary = array_key_exists($row[$column_name], $primaryKeyColumn);
            if ($isPrimary) {
                $primaryPosition = $primaryKeyColumn[$row[$column_name]];
            } else {
                $primaryPosition = null;
            }

            $desc[$this->foldCase($row[$column_name])] = [
                'SCHEMA_NAME'      => null, // @todo
                'TABLE_NAME'       => $this->foldCase($row[$table_name]),
                'COLUMN_NAME'      => $this->foldCase($row[$column_name]),
                'COLUMN_POSITION'  => (int) $row[$column_position],
                'DATA_TYPE'        => $type,
                'DEFAULT'          => $row[$column_def],
                'NULLABLE'         => (bool) $row[$nullable],
                'LENGTH'           => $row[$length],
                'SCALE'            => $row[$scale],
                'PRECISION'        => $row[$precision],
                'UNSIGNED'         => null, // @todo
                'PRIMARY'          => $isPrimary,
                'PRIMARY_POSITION' => $primaryPosition,
                'IDENTITY'         => $identity
            ];
        }
        return $desc;
    }

    /**
     * Adds an adapter-specific LIMIT clause to the SELECT statement.
     *
     * @param string $sql
     * @param integer $count
     * @param integer $offset OPTIONAL
     * @return string
     * @throws Zend_Db_Adapter_Exceptions
     */
     public function limit($sql, $count, $offset = 0)
     {
        $count = (int)$count;
        if ($count <= 0) {
            require_once 'Zend/Db/Adapter/Exception.php';
            throw new Zend_Db_Adapter_Exception("LIMIT argument count=$count is not valid");
        }

        $offset = (int)$offset;
        if ($offset < 0) {
            /** @see Zend_Db_Adapter_Exception */
            require_once 'Zend/Db/Adapter/Exception.php';
            throw new Zend_Db_Adapter_Exception("LIMIT argument offset=$offset is not valid");
        }

        if ($offset === 0) {
            $sql = preg_replace('/^SELECT\s/i', 'SELECT TOP ' . $count . ' ', $sql);
        } else {
            $orderby = stristr($sql, 'ORDER BY');
            if ($orderby !== false) {
                $sort  = (stripos($orderby, ' desc') !== false) ? 'desc' : 'asc';
                $order = str_ireplace('ORDER BY', '', $orderby);
                $order = trim(preg_replace('/\bASC\b|\bDESC\b/i', '', $order));
            }

            $sql = preg_replace('/^SELECT\s/i', 'SELECT TOP ' . ($count+$offset) . ' ', $sql);

            $sql = 'SELECT * FROM (SELECT TOP ' . $count . ' * FROM (' . $sql . ') AS inner_tbl';
            if ($orderby !== false) {
                $innerOrder = preg_replace('/\".*\".\"(.*)\"/i', '"inner_tbl"."$1"', $order);
                $sql .= ' ORDER BY ' . $innerOrder . ' ';
                $sql .= (stripos($sort, 'asc') !== false) ? 'DESC' : 'ASC';
            }
            $sql .= ') AS outer_tbl';
            if ($orderby !== false) {
                $outerOrder = preg_replace('/\".*\".\"(.*)\"/i', '"outer_tbl"."$1"', $order);
                $sql .= ' ORDER BY ' . $outerOrder . ' ' . $sort;
            }
        }

        return $sql;
    }

    /**
     * Retrieve server version in PHP style
     * Pdo_Mssql doesn't support getAttribute(PDO::ATTR_SERVER_VERSION)
     * @return string
     */
    public function getServerVersion()
    {
        try {
            $stmt = $this->query("SELECT SERVERPROPERTY('productversion')");
            $result = $stmt->fetchAll(Zend_Db::FETCH_NUM);
            if (count($result)) {
                return $result[0][0];
            }
            return null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
