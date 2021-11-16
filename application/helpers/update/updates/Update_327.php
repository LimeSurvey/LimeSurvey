<?php

namespace LimeSurvey\Helpers\Update;

class Update_327 extends DatabaseUpdateBase
{
    public function up()
    {
            upgrade327($this->db);
    }
}
