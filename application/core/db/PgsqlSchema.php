<?php

class PgsqlSchema extends CPgsqlSchema
{
    use SmartColumnTypeTrait;
    
    public function __construct($conn) {
        parent::__construct($conn);
        /**
         * Auto increment.
         */
        $this->columnTypes['autoincrement'] = 'serial';
        
        $this->columnTypes['longbinary'] = 'bytea';
    }
    
    public function createDatabase($name) {
        try {
            $this->connection->createCommand("CREATE DATABASE \"$name\" ENCODING 'UTF8'")->execute();
        } catch (Exception $e) {
            return false;
        }
        return true;        
    }
}