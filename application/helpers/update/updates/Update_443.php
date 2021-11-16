<?php

namespace LimeSurvey\Helpers\Update;

class Update_443 extends DatabaseUpdateBase
{
    public function up()
    {
            $this->db->createCommand()->renameColumn('{{users}}', 'lastLogin', 'last_login');
    }
}
