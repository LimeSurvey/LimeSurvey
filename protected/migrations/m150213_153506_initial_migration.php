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
        /**
         * @todo Re-Enable this check to prevent migrations happening with unknown database state.
         */
        if (false && 300 != SettingGlobal::get('DBVersion', 0)) {

            echo "Migrations require database version 300.\n";
            return false;
        }
        
//        echo "Not implemented yet.\n";
//        return false;
	}

	public function safeDown()
	{
        return true;
	}
	
}