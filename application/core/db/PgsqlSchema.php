<?php

class PgsqlSchema extends CPgsqlSchema
{

    public function __construct($conn) {
        parent::__construct($conn);
        /**
        * Auto increment.
        */
        $this->columnTypes['autoincrement'] = 'serial';

        $this->columnTypes['longbinary'] = 'bytea';
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