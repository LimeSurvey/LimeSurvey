<?php

class MysqlSchema extends CMysqlSchema
{
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
    /**
    * Adds support for replacing default arguments.
    * @param type $type
    */
    public function getColumnType($type)
    {
        if (preg_match('/^([[:alpha:]]+)\s*(\(.+?\))(.*)$/', $type, $matches)) {
            $baseType = parent::getColumnType($matches[1] . ' ' . $matches[3]);
            $param = $matches[2];
            $result = preg_replace('/\(.+?\)/', $param, $baseType, 1);
        } else {
            $result = parent::getColumnType($type);
        }
        return $result;
    }    
}