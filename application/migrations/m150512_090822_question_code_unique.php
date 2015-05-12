<?php
namespace ls\migrations;
use \CDbMigration;
class m150512_090822_question_code_unique extends CDbMigration
{
	/**
     * Describe the migration in this PHPDOC.
     * Implement it below.
     * @return boolean True if migration was a success.
     */
    public function safeUp()
	{
        $this->createIndex('unique_question_codes', '{{questions}}', ['sid', 'title', 'parent_qid'], true);
        return false;
	}
    
    /**
     * @return boolean True if migration was a success.
     */

	public function safeDown()
	{
		echo "m150512_090822_question_code_unique does not support migration down.\\n";
		return false;
        
	}

}