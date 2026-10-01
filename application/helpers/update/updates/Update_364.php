<?php

namespace LimeSurvey\Helpers\Update;

class Update_364 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            extendDatafields364($this->db);
    }
}
