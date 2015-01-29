<?php

class MysqlSchema extends CMysqlSchema
{
    use SmartColumnTypeTrait;
    public function __construct($conn) {
        parent::__construct($conn);
        /**
         * Auto increment.
         */
        $this->columnTypes['autoincrement'] = 'int(11) NOT NULL AUTO_INCREMENT';
        
        $this->columnTypes['longbinary'] = 'longblob';
    }

    public function createTable($table, $columns, $options = null) {
        $result = parent::createTable($table, $columns, $options);
        $result .= ' ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
        return $result;
    }
    
    public function getDatabases() {
        return $this->dbConnection->createCommand('SHOW DATABASES')->queryColumn(['Database']);
    }
    
    public function createDatabase($name) {
        try {
            $this->connection->createCommand("CREATE DATABASE `$name` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci")->execute();
        } catch (Exception $e) {
            return false;
        }
        return true;        
    }
}