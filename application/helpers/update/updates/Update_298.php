<?php

namespace LimeSurvey\Helpers\Update;

class Update_298 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            upgradeTemplateTables298($this->db);
    }
}
