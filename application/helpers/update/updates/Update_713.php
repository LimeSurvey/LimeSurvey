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
            switch ($db->driverName) {
                case 'mysql':
                case 'mysqli':
                    addColumn('{{surveys}}', 'welcome_image', 'mediumtext NULL');
                    break;
                case 'pgsql':
                case 'mssql':
                case 'sqlsrv':
                case 'dblib':
                default:
                    addColumn('{{surveys}}', 'welcome_image', 'text NULL');
                    break;
            }
        }
    }
}
