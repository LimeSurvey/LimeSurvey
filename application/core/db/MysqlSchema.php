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
        if (isset($this->columnTypes[$type]))
        { // Direct : get it
            $sResult=$this->columnTypes[$type];
        }
        elseif (preg_match('/^([a-zA-Z ]+)\((.+?)\)(.*)$/', $type, $matches)) 
        { // With params : some test to do
            $baseType = parent::getColumnType($matches[1]);
            if(preg_match('/^([a-zA-Z ]+)\((.+?)\)(.*)$/', $baseType, $baseMatches))
            { // Replace the default Yii param
                $sResult=preg_replace('/\(.+\)/', "(".$matches[2].")",parent::getColumnType($matches[1]." ".$matches[3]));
            }
            else
            { // Get the base type and join
                $sResult=join(" ",array($baseType,"(".$matches[2].")",$matches[3]));
            }
        }
        else
        {
            $sResult = parent::getColumnType($type);
        }
        return $sResult;
    }
}
