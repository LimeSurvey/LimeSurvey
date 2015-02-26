<?php
/**
 * Application component that manages Yii migrations.
 * Most of the protected functions have been copies from CMigrateCommand,
 * where needed adaptations have been made to use them from a WebApplication.
 */
class MigrationManager extends \CApplicationComponent
{
    const BASE_MIGRATION='m000000_000000_base';
    public $migrationTable = 'tbl_migration';
    /**
     *
     * @var string[] The paths to folders containing migrations.
     */
    public $migrationPaths = [__DIR__ . '/../migrations'];
    
    protected function getNewMigrations()
	{
		$applied = [];
		foreach($this->migrationHistory as $version => $time)
			$applied[substr($version,1,13)] = true;

		$migrations = [];
        foreach ($this->migrationPaths as $migrationPath) {
            $handle = opendir($migrationPath);
            while(($file=readdir($handle))!==false)
            {
                if($file==='.' || $file==='..')
                    continue;
                $path = "$migrationPath/$file";
                if(preg_match('/^(m(\d{6}_\d{6})_.*?)\.php$/',$file,$matches) && is_file($path) && !isset($applied[$matches[2]]))
                    $migrations[]=$matches[1];
            }
            closedir($handle);
        }
		sort($migrations);
		return $migrations;
	}
    
    protected function getMigrationHistory($limit = -1)
	{
		$db = App()->db;
		if($db->schema->getTable($this->migrationTable,true)===null)
		{
			$this->createMigrationHistoryTable();
		}
		return CHtml::listData($db->createCommand()
			->select('version, apply_time')
			->from($this->migrationTable)
			->order('version DESC')
			->limit($limit)
			->queryAll(), 'version', 'apply_time');
	}

	protected function createMigrationHistoryTable()
	{
		$db=$this->getDbConnection();
        echo '<pre>';
		echo 'Creating migration history table "'.$this->migrationTable.'"...';
        $db->createCommand()->createTable($this->migrationTable,array(
			'version'=>'varchar(180) NOT NULL PRIMARY KEY',
			'apply_time'=>'integer',
		));
		$db->createCommand()->insert($this->migrationTable,array(
			'version'=>self::BASE_MIGRATION,
			'apply_time'=>time(),
		));
		echo "done.\n";
        echo '</pre>';
	}
    
    public function migrateUp($class)
	{
		if ( $class === self::BASE_MIGRATION) {
			return;
        }
        ob_end_flush();
        echo '<pre>';
		
        echo "*** applying $class\n";
        
        $start = microtime(true);
		$migration = $this->instantiateMigration($class);
		if($migration->up()!== false){
			App()->db->createCommand()->insert($this->migrationTable, [
				'version' => $class,
				'apply_time' => time(),
			]);
            
			$time = microtime(true) - $start;
			echo "*** applied $class (time: ".sprintf("%.3f",$time)."s)\n\n";
            echo '</pre>';
		} else {
			$time = microtime(true) - $start;
            echo "*** failed to apply $class (time: ".sprintf("%.3f",$time)."s)\n\n";
            echo '</pre>';
			return false;
		}
        
	}
    
    protected function instantiateMigration($class)
	{
        // If classname is unqualified we prefix it.
        if (strpos($class, '\\') === false) {
            $class = "\\ls\\migrations\\" . $class;
        }
		$migration=new $class;
		$migration->setDbConnection(App()->db);
		return $migration;
	}
}