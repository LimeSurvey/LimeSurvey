<?php
namespace ls\migrations;
use \CDbMigration;
class m150322_132458_add_series_to_survey extends CDbMigration
{
	/**
     * Describe the migration in this PHPDOC.
     * Implement it below.
     * @return boolean True if migration was a success.
     */
    public function safeUp()
	{
        $this->addColumn('{{surveys}}', 'use_series', 'boolean DEFAULT FALSE NOT NULL');
	}
    
    /**
     * @return boolean True if migration was a success.
     */

	public function safeDown()
	{
        $this->dropColumn('{{surveys}}', 'use_series');
        
	}

}