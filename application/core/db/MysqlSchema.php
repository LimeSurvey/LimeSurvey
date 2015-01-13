<?php

class MysqlSchema extends CMysqlSchema
{
    public function __construct($conn) {
        parent::__construct($conn);
        /**
         * Auto increment.
         */
        $this->columnTypes['autoincrement'] = 'int(11) NOT NULL AUTO_INCREMENT';
    }

    public function createTable($table, $columns, $options = null) {
        $result = parent::createTable($table, $columns, $options);
        $result .= ' ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
        return $result;
    }
    
    public function getColumnType($type)
	{
        if (preg_match('/([[:alpha:]]+)\s*(\(.+?\)).*/', $type, $matches)) {
            $baseType = parent::getColumnType($type);
            $param = $matches[2];
            $result = preg_replace('/\(.+?\)/', $param, $baseType, 1);
        } else {
            $result = parent::getColumnType($type);
        }
        return $result;		
	}
    
}