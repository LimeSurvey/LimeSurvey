<?php

abstract class Zend_Db_Schema_AbstractChange
{
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * @var string
     */
    protected $_tablePrefix;

    /**
     * Build and initialize the object
     *
     * @param Zend_Db_Adapter_Abstract $db          Database adabpter to use
     * @param string                   $tablePrefix Prefix for any table names
     */
    public function __construct(Zend_Db_Adapter_Abstract $db, $tablePrefix = '')
    {
        $this->_db = $db;
        $this->_tablePrefix = $tablePrefix;
    }

    /**
     * Changes to be applied in this change
     *
     * @return null
     */
    abstract function up();

    /**
     * Rollback changes made in up()
     *
     * @return null
     */
    abstract function down();

}