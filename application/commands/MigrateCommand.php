<?php
namespace ls\cli;
class MigrateCommand extends \MigrateCommand {
    public $migrationTable = "{{migration}}";
    
    public function getTemplate() {
        return file_get_contents($this->migrationPath . '/template');
    }
    
    protected function instantiateMigration($class)
	{
        $fullClass = "\\ls\\migrations\\$class";
        $migration=new $fullClass;
		$migration->setDbConnection($this->getDbConnection());
		return $migration;
	}
}

