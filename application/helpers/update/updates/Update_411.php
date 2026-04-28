<?php

namespace LimeSurvey\Helpers\Update;

class Update_411 extends DatabaseUpdateBase
{
    public function up()
    {
            $this->db->createCommand()->addColumn('{{plugins}}', 'priority', "int NOT NULL DEFAULT 0");
    }
}
