<?php

namespace LimeSurvey\Helpers\Update;

class Update_333 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            upgrade333($this->db);
    }
}
