<?php

namespace LimeSurvey\Helpers\Update;

class Update_306 extends DatabaseUpdateBase
{
    public function up()
    {
            createSurveyGroupTables306($this->db);
    }
}
