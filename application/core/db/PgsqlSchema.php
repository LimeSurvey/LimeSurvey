<?php

class PgsqlSchema extends CPgsqlSchema
{
    public function __construct($conn) {
        parent::__construct($conn);
        /**
         * Auto increment.
         */
        $this->columnTypes['autoincrement'] = 'serial';
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