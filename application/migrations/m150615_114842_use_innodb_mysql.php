<?php
namespace ls\migrations;
use \CDbMigration;

class m150615_114842_use_innodb_mysql extends CDbMigration
{
	/**
     * Describe the migration in this PHPDOC.
     * Implement it below.
     * @return boolean True if migration was a success.
     */
    public function safeUp()
	{
        $schema = App()->db->schema;
        if ($schema instanceof \MysqlSchema) {

            $prefix = App()->db->tablePrefix;
            foreach(App()->db->schema->tableNames as $tableName) {
                // Check if we "own" this table.
                if (strncmp($prefix, $tableName, strlen($prefix)) === 0) {
                    // Check if it is not a response or token table.
                    if (!preg_match("/^{$prefix}(survey_|token_).*\\d+/", $tableName)) {
                        if (!$schema->alterEngine($tableName, $schema::ENGINE_INNODB)) {
                            echo "Failed to convert table.\n";
                            return false;
                        }
                    }
                }

            }
        }
        return true;
	}
    
    /**
     * @return boolean True if migration was a success.
     */

	public function safeDown()
	{
		echo "m150615_114842_use_innodb_mysql does not support migration down.\\n";
		return false;
        
	}

}