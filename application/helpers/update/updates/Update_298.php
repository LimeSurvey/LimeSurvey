<?php

namespace LimeSurvey\Helpers\Update;

class Update_298 extends DatabaseUpdateBase
{
    public function up()
    {
            upgradeTemplateTables298($this->db);
    }
}
