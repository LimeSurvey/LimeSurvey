<?php

class MssqlSchema extends CMssqlSchema
{
    use SmartColumnTypeTrait;
    public function __construct($conn) {
        parent::__construct($conn);
        /**
         * Recommended practice.
         */
        $this->columnTypes['text'] = 'nvarchar(max)';
        /**
         * DbLib bugs if no explicit NOT NULL is specified.
         */
        $this->columnTypes['pk'] = 'int IDENTITY PRIMARY KEY NOT NULL';
        /**
         * Varchar cannot store unicode, nvarchar can.
         */
        $this->columnTypes['string'] = 'nvarchar(255)';
        /**
         * Auto increment.
         */
        $this->columnTypes['autoincrement'] = 'integer NOT NULL IDENTITY (1,1)';
        
        $this->columnTypes['longbinary'] = 'varbinary(max)';
    }

    public function getColumnType($type)
	{
        $result = $this->parseType($type, function($type) { return parent::getColumnType($type); });
        /**
         * @date 2015-5-11
         * Bug occurs with DBLIB when specifying neither of NULL and NOT NULL.
         * So if resulting type doesn't contain NULL then add it.
         */
        if (stripos($result, 'NULL') === false) {
            $result .= ' NULL';
        }
        return $result;
    }
    
    public function getDatabases() {
        return $this->dbConnection->createCommand('EXEC sp_databases')->queryColumn(['DATABASE_NAME']);
    }
    
    public function createDatabase($name)
    {
        $this->connection->createCommand("CREATE DATABASE [$name]")->execute();
        return true;
    }

    public function tableExists($name)
    {
        try {

            App()->db->createCommand("SELECT 1 FROM {{%$name}}")->execute();
            return true;
        } catch (\CDbException $e) {
            return false;
        }
    }
}