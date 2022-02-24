<?php

namespace LimeSurvey\Helpers\Update;

class Update_326 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->alterColumn('{{surveys}}', 'datecreated', 'datetime');
    }
}
