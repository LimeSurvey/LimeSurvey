<?php

namespace LimeSurvey\Helpers\Update;

class Update_357 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            //// IKI
            $this->db->createCommand()->renameColumn('{{surveys_groups}}', 'owner_uid', 'owner_id');
    }
}
