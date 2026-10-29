<?php

namespace LimeSurvey\Helpers\Update;

class Update_304 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            upgradeTemplateTables304($this->db);
    }
}
