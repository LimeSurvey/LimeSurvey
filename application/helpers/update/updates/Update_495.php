<?php

namespace LimeSurvey\Helpers\Update;

class Update_495 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->addColumn('{{users}}', 'expires', 'datetime');
    }
}
