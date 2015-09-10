<?php

class MssqlSchema extends CMssqlSchema
{
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
        if (preg_match('/^([[:alpha:]]+)\s*(\(.+?\))(.*)$/', $type, $matches)) {
            $baseType = parent::getColumnType($matches[1] . ' ' . $matches[3]);
            $param = $matches[2];
            $result = preg_replace('/\(.+?\)/', $param, $baseType, 1);
        } else {
            $result = parent::getColumnType($type);
        }
        /**
         * @date 2015-5-11
         * A bug occurs with DBLIB when specifying neither of NULL and NOT NULL.
         * So if resulting type doesn't contain NULL then add it.
         */        
        if (stripos($result, 'NULL') === false) {
            $result .= ' NULL';
        }
        return $result;
    }
}