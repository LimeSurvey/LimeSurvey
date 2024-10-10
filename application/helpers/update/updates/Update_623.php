<?php

namespace LimeSurvey\Helpers\Update;

use LimeSurvey\Helpers\Update\DatabaseUpdateBase;

class Update_623 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        if (\Yii::app()->db->driverName == 'pgsql') {
            $table = \Yii::app()->db->schema->getTable('{{users}}');
            if (isset($table->columns['user_status']) && $table->columns['user_status']->dbType != 'integer') {
                $this->db->createCommand("ALTER TABLE {{users}} ALTER COLUMN user_status DROP DEFAULT;")->execute();
                $this->db->createCommand("ALTER TABLE {{users}} ALTER COLUMN user_status TYPE INT USING CASE WHEN user_status=TRUE THEN 1 ELSE 0 END;")->execute();
                $this->db->createCommand("ALTER TABLE {{users}} ALTER COLUMN user_status SET DEFAULT 1;")->execute();
            }
        }
        if (in_array(\Yii::app()->db->driverName, array('mssql', 'sqlsrv', 'dblib'))) {
            $this->db->createCommand("UPDATE {{users}} SET user_status=1")->execute();
        }
    }
}
