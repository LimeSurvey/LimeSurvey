<?php

namespace LimeSurvey\Helpers\Update;

class Update_306 extends DatabaseUpdateBase
{
    public function run()
    {
            createSurveyGroupTables306($oDB);
    }
}
