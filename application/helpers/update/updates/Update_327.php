<?php

namespace LimeSurvey\Helpers\Update;

class Update_327 extends DatabaseUpdateBase
{
    public function run()
    {
            upgrade327($this->db);
    }
}
