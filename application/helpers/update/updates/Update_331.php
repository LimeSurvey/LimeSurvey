<?php

namespace LimeSurvey\Helpers\Update;

class Update_331 extends DatabaseUpdateBase
{
    public function run()
    {
            upgrade331($this->db);
    }
}
