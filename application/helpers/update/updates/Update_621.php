<?php

namespace LimeSurvey\Helpers\Update;

use LimeSurvey\Helpers\Update\DatabaseUpdateBase;

class Update_621 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $table = \Yii::app()->db->schema->getTable('{{users}}');
        if (isset($table->columns['user_status']) && $table->columns['user_status']->dbType != 'integer') {
            \alterColumn('{{users}}', 'user_status', "integer", false, 1);
        }
    }
}
