<?php

class DbConnection extends \CDbConnection
{
    public function __construct($dsn = '', $username = '', $password = '') {
        parent::__construct($dsn, $username, $password);
        $this->driverMap = array_merge($this->driverMap, array(
            'mysql' => MysqlSchema::class,
            'mysqli' => MysqlSchema::class,
            'mssql' => MssqlSchema::class,
            'dblib' => MssqlSchema::class,
            'sqlsrv' => MssqlSchema::class,
            'pgsql' => PgsqlSchema::class
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
        $this->pdoInstance->exec($sql);
        return true;
    }
}
