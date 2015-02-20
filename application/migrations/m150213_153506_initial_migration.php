<?php
namespace ls\migrations;
use \CDbMigration;
use SettingGlobal;

class m150213_153506_initial_migration extends CDbMigration
{


	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        // Check current database version.
        if (300 != SettingGlobal::get('DBVersion', 0)) {
            echo "Migrations require database version 300.\n";
            return false;
        }
        
        echo "Not implemented yet.\n";
        return false;
	}

	public function safeDown()
	{
        echo "m150213_153506_initial_migration does not support migration down.\n";
		return false;
	}
	
}