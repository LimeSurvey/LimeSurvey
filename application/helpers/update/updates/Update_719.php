<?php

namespace LimeSurvey\Helpers\Update;

class Update_719 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
        $db = \Yii::app()->db;
        $columnNames = $db->schema->getTable('{{surveys}}')->columnNames;

        if (!in_array('code', $columnNames, true)) {
            addColumn('{{surveys}}', 'code', 'string(255) NULL');
        }
    }
}
