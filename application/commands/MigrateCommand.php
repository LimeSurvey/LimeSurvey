<?php
namespace ls\cli;
class MigrateCommand extends \MigrateCommand {
    public $migrationTable;
    
    public function __construct($name, $runner) {
        $this->migrationTable = \Yii::app()->db->tablePrefix . 'migration';
        parent::__construct($name, $runner);
    }
}

