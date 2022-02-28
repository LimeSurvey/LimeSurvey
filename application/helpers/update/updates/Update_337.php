<?php

namespace LimeSurvey\Helpers\Update;

class Update_337 extends DatabaseUpdateBase
{
    public function up()
    {
            resetTutorials337($this->db);
    }
}
