<?php

namespace LimeSurvey\Helpers\Update;

class Update_333 extends DatabaseUpdateBase
{
    public function up()
    {
            upgrade333($this->db);
    }
}
