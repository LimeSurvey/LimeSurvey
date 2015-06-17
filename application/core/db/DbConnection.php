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
}
?>