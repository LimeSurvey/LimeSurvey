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
    * @param string $type
    * @return string
    */
    public function getColumnType($type)
    {
        if(isset(Yii::app()->db->schema->columnTypes[$type]))
        { // Do it only if is not find directly
            $result = parent::getColumnType($type);
        }
        elseif (preg_match('/^([[:alpha:]]+)\s*(\(.+?\))(.*)$/', $type, $matches))
        {
            $baseType = parent::getColumnType($matches[1] . ' ' . $matches[3]);
            $param = $matches[2];
            if(preg_match('/^([[:alpha:]]+)\s*(\(.+?\))(.*)$/', $baseType))
            { // If Yii type have default params, replace it
                $result = preg_replace('/\(.+?\)/', $param, $baseType, 1);
            }
            else
            { // Else join the yii type and the param ( decimal don't have params, other ?)
                $result = $baseType.$param;
            }
        }
        else
        {
            $result = parent::getColumnType($type);
        }
        return $result;
    }
}
