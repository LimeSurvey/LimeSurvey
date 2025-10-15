<?php

namespace LimeSurvey\Helpers\Update;

class Update_638 extends DatabaseUpdateBase
{
    public function up()
    {
        $db = \Yii::app()->db;
        $columnNames = $db->schema->getTable('{{surveys}}')->columnNames;

        if (!in_array('lastmodified', $columnNames, true)) {
            // Step 1: Add column allowing NULL.
            addColumn('{{surveys}}', 'lastmodified', 'datetime NULL');

            // Step 2: Fill it with data using the current UTC time as default.
            $db->createCommand(
                'UPDATE {{surveys}} SET lastmodified = :lastmodified WHERE lastmodified IS NULL'
            )->bindValue(':lastmodified', gmdate('Y-m-d H:i:s'))->execute();

            // Step 3: Set column to NOT NULL.
            alterColumn('{{surveys}}', 'lastmodified', 'datetime NOT NULL');
        }
    }
}
