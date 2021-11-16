<?php

namespace LimeSurvey\Helpers\Update;

class Update_328 extends DatabaseUpdateBase
{
    public function up()
    {
            upgrade328($this->db);
    }
}
