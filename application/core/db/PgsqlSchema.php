<?php

class PgsqlSchema extends CPgsqlSchema
{
    use SmartColumnTypeTrait;
    
    public function __construct($conn) {
        parent::__construct($conn);
        /**
         * Auto increment.
         */
        $this->columnTypes['autoincrement'] = 'serial';
    }
    
}