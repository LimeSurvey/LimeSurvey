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
        switch (\Yii::app()->db->driverName) {
            case 'pgsql':
                $this->db->createCommand("ALTER TABLE {{users}} ALTER COLUMN user_status DROP DEFAULT;")->execute();
                $this->db->createCommand("ALTER TABLE {{users}} ALTER COLUMN user_status TYPE INT USING CASE WHEN user_status=TRUE THEN 1 ELSE 0 END;")->execute();
                $this->db->createCommand("ALTER TABLE {{users}} ALTER COLUMN user_status SET DEFAULT 1;")->execute();
                break;
            default:
                \alterColumn('{{users}}', 'user_status', "integer", false, 1);
        }
    }
}
