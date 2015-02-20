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
    * Executes an SQL file
    *
    * @param string $fileName
    * @param string $prefix
    */
    public function executeFile($fileName, $prefix)
    {
        if (!is_readable($fileName)) {
            throw new \Exception("SQL file is not readable.");
        }
        $sql = strtr(file_get_contents($fileName), ['prefix_' => $prefix]);
        $this->createCommand($sql)->execute();
        return true;
    }
}
?>