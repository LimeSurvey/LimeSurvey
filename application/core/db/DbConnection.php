<?php

class DbConnection extends \CDbConnection
{
    public function __construct($dsn = '', $username = '', $password = '') {
        parent::__construct($dsn, $username, $password);
        $this->driverMap = array_merge($this->driverMap, array(
            'mysql' => 'MysqlSchema',
            'mysqli' => 'MysqlSchema',
            'mssql' => 'MssqlSchema',
            'dblib' => 'MssqlSchema',
            'sqlsrv' => 'MssqlSchema',
            'pgsql' => 'PgsqlSchema'
        ));
    }

    /**
     * Quotes a string value for use in a query.
     * @param string $str string to be quoted
     * @param integer $quoteParam Parameter for PDO::quote function.
     * @return string the properly quoted string
     * @see http://www.php.net/manual/en/function.PDO-quote.php
     */
    public function quoteValueExtended($str, $quoteParam)
    {
        if(is_int($str) || is_float($str))
            return $str;

        $this->setActive(true);
        if(($value=$this->getPdoInstance()->quote($str, $quoteParam))!==false)
            return $value;
        else  // the driver doesn't support quote (e.g. oci)
            return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
    }
}
?>