<?php

namespace LimeSurvey\Helpers\Update;

class Update_139 extends DatabaseUpdateBase
{
    public function run()
    {
            upgradeSurveyTables139();
    }
}