<?php

namespace LimeSurvey\Helpers\Update;

class Update_638 extends DatabaseUpdateBase
{
    public function up()
    {
        $columnNames = \Yii::app()->db->schema->getTable('{{surveys}}')->columnNames;
        if (!in_array('lastmodified', $columnNames)) {
            addColumn('{{surveys}}', 'lastmodified', "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP");
        }
    }
}