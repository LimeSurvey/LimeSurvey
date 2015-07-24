<?php

trait SmartColumnTypeTrait {
    /**
    * Adds support for replacing default arguments.
    * @param type $type
    */
    public function getColumnType($type)
    {
        $sResult=$type;
        if (isset($this->columnTypes[$type])) {
            $sResult=$this->columnTypes[$type];
        } elseif (preg_match('/^(\w+)\((.+?)\)(.*)$/', $type, $matches)) {
            if (isset($this->columnTypes[$matches[1]])) {
                $sResult=preg_replace('/\(.+\)/', '(' . $matches[2] . ')', $this->columnTypes[$matches[1]]) . $matches[3];
            }
        } elseif (preg_match('/^(\w+)\s+/', $type, $matches)) {
            if (isset($this->columnTypes[$matches[1]])) {
                $sResult=preg_replace('/^\w+/', $this->columnTypes[$matches[1]], $type);
            }
        }
        return $sResult;	}
}