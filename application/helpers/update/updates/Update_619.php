<?php

namespace LimeSurvey\Helpers\Update;

use LimeSurvey\Helpers\Update\DatabaseUpdateBase;

class Update_619 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()->addColumn('{{users}}', 'user_status', 'integer DEFAULT 1');
        $this->db->createCommand("UPDATE {{users}} SET user_status=1")->execute();
    }
}
