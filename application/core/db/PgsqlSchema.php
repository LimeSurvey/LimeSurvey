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
        $this->columnTypes['decimal'] = 'numeric (10,0)'; // Same default than MySql (not used)
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