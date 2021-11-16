<?php

namespace LimeSurvey\Helpers\Update;

class Update_154 extends DatabaseUpdateBase
{
    public function run()
    {
            $this->db->createCommand()->addColumn('{{groups}}', 'grelevance', "text");
    }
}
