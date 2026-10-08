<?php

namespace LimeSurvey\Helpers\Update;

class Update_327 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            upgrade327($this->db);
    }
}
