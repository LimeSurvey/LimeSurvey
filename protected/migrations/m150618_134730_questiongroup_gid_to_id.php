<?php
namespace ls\migrations;
use \CDbMigration;
class m150618_134730_questiongroup_gid_to_id extends CDbMigration
{
	/**
     * Describe the migration in this PHPDOC.
     * Implement it below.
     * @return boolean True if migration was a success.
     */
    public function safeUp()
	{
        $this->renameColumn('{{groups}}', 'gid', 'id');
        return true;
	}
    
    /**
     * @return boolean True if migration was a success.
     */

	public function safeDown()
	{
		echo "m150618_134730_questiongroup_gid_to_id does not support migration down.\\n";
		return false;
        
	}

}