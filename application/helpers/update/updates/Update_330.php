<?php

namespace LimeSurvey\Helpers\Update;

class Update_330 extends DatabaseUpdateBase
{
    public function up()
    {
            upgrade330($this->db);
    }
}
