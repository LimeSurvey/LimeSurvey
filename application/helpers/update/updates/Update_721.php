<?php

namespace LimeSurvey\Helpers\Update;

class Update_721 extends DatabaseUpdateBase
{
    /**
     * Add a new column archive_alias to table archived_table_settings
     */
    #[\Override]
    public function up()
    {
        $columnNames = \Yii::app()->db->schema->getTable('{{archived_table_settings}}')->columnNames;

        if (!in_array('archive_alias', $columnNames, true)) {
            addColumn('{{archived_table_settings}}', 'archive_alias', "string(255) DEFAULT ''");
        }
    }
}
