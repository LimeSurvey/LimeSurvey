<?php

namespace LimeSurvey\Helpers\Update;

class Update_253 extends DatabaseUpdateBase
{
    public function run()
    {
            upgradeSurveyTables253();

            // Update DBVersion
    }
}
