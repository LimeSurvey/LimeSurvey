<?php

namespace LimeSurvey\Helpers\Update;

class Update_331 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            upgrade331($this->db);
    }
}
