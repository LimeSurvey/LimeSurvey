<?php

namespace LimeSurvey\Helpers\Update;

class Update_304 extends DatabaseUpdateBase
{
    public function up()
    {
            upgradeTemplateTables304($this->db);
    }
}
