<?php
namespace ls\migrations;
use \CDbMigration;
class m150602_153442_refactor_participant_attributes extends CDbMigration
{
	/**
     * Describe the migration in this PHPDOC.
     * Implement it below.
     * @return boolean True if migration was a success.
     */
    public function safeUp()
	{
        $table = \ls\models\ParticipantAttributeName::model()->tableName();
        $this->renameColumn($table, 'attribute_type', 'type');
        $this->renameColumn($table, 'defaultname', 'name');
        $this->renameColumn($table, 'visible', 'visible_old');
        $this->addColumn($table, 'visible', 'bool');

        $this->update($table, [
            'visible' => 0
        ], 'visible_old = :false', [
            ':false' => 'FALSE'
        ]);
        $this->update($table, [
            'visible' => 1
        ], 'visible_old = :true', [
            ':true' => 'TRUE'
        ]);
        $this->dropColumn($table, 'visible_old');
        // Remove auto increment from column.
        $this->alterColumn($table, 'attribute_id', 'int');
        $this->dropPrimaryKey($table . '_pkey', $table);
        $this->addColumn($table, 'id', 'pk');
        $this->update($table, [
            'id' => new \CDbExpression('attribute_id')
        ]);
        $this->dropColumn($table, 'attribute_id');

        return true;
	}
    
    /**
     * @return boolean True if migration was a success.
     */

	public function safeDown()
	{
        echo "m150602_153442_refactor_participant_attributes does not support migration down.\\n";
		return false;
        
	}

}