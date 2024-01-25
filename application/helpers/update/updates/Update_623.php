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
            $this->db->createCommand("ALTER TABLE {{users}} ALTER COLUMN user_status DROP DEFAULT;")->execute();
            $this->db->createCommand("ALTER TABLE {{users}} ALTER COLUMN user_status TYPE INT USING CASE WHEN user_status=TRUE THEN 1 ELSE 0 END;")->execute();
            $this->db->createCommand("ALTER TABLE {{users}} ALTER COLUMN user_status SET DEFAULT 1;")->execute();
        }
    }
}
