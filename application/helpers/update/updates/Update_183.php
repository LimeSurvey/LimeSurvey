<?php

namespace LimeSurvey\Helpers\Update;

class Update_183 extends DatabaseUpdateBase
{
    public function run()
    {
            upgradeSurveyTables183();
    }
}
