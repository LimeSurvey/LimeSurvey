<?php

namespace LimeSurvey\Helpers\Update;

use TemplateConfiguration;

class Update_713 extends DatabaseUpdateBase
{
    /**
     * Add a new column welcome_image to table surveys
     */
    public function up()
    {
        $db = \Yii::app()->db;
        $columnNames = $db->schema->getTable('{{surveys}}')->columnNames;

        if (!in_array('welcome_image', $columnNames, true)) {
            addColumn('{{surveys}}', 'welcome_image', 'mediumtext NULL');
        }
    }
}
