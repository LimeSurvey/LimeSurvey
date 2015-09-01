<?php

trait SmartColumnTypeTrait {
    /**
    * Adds support for replacing default arguments.
    * @param string $type
    * @return string
    */
    public function getColumnType($type)
    {
        if (isset(Yii::app()->db->schema->columnTypes[$type]))
        { // Direct : get it
            $sResult=Yii::app()->db->schema->columnTypes[$type];
        }
        elseif (preg_match('/^([a-zA-Z ]+)\((.+?)\)(.*)$/', $type, $matches)) 
        { // With params : some test to do
            $baseType = $this->getColumnType($matches[1]);
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
            $sResult = $this->getColumnType($type);
        }
        return $sResult;
    }
