<?php
namespace ls\migrations;
use \CDbMigration;
class m150501_082838_create_translation_table extends CDbMigration
{
	/**
     * Describe the migration in this PHPDOC.
     * Implement it below.
     * @return boolean True if migration was a success.
     */
    public function safeUp()
	{
        \ls\models\Translation::createTable();
        return true;
	}
    
    /**
     * @return boolean True if migration was a success.
     */

	public function safeDown()
	{
        echo "m150501_082838_create_translation_table does not support migration down.\\n";
		return false;
        
	}

}