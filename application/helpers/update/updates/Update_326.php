<?php

namespace LimeSurvey\Helpers\Update;

class Update_326 extends DatabaseUpdateBase
{
    public function run()
    {
            $this->db->createCommand()->alterColumn('{{surveys}}', 'datecreated', 'datetime');
    }
}
