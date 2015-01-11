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
    }

    public function createTable($table, $columns, $options = null) {
        $result = parent::createTable($table, $columns, $options);
        $result .= ' ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
        return $result;
    }
    
}