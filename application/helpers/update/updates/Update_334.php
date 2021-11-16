<?php

namespace LimeSurvey\Helpers\Update;

class Update_334 extends DatabaseUpdateBase
{
    public function run()
    {
            $this->db->createCommand()->addColumn('{{tutorials}}', 'title', 'string(192)');
            $this->db->createCommand()->addColumn('{{tutorials}}', 'icon', 'string(64)');
    }
}
