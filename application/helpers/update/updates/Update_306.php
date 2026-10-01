<?php

namespace LimeSurvey\Helpers\Update;

class Update_306 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            createSurveyGroupTables306($this->db);
    }
}
