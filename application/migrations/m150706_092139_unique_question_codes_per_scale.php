<?php
namespace ls\migrations;
use \CDbMigration;
class m150706_092139_unique_question_codes_per_scale extends CDbMigration
{
	/**
     * Describe the migration in this PHPDOC.
     * Implement it below.
     * @return boolean True if migration was a success.
     */
    public function safeUp()
	{
        $this->dropIndex('unique_question_codes', '{{questions}}');
        $this->createIndex('unique_question_codes', '{{questions}}', ['sid', 'title', 'parent_qid', 'scale_id'], true);
        return true;
	}
    
    /**
     * @return boolean True if migration was a success.
     */

	public function safeDown()
	{
        echo "m150706_092139_unique_question_codes_per_scale does not support migration down.\\n";
		return false;
        
	}

}