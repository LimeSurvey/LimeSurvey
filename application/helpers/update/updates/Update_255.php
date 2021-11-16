<?php

namespace LimeSurvey\Helpers\Update;

class Update_255 extends DatabaseUpdateBase
{
    public function run()
    {
            upgradeSurveyTables255();
            // Update DBVersion
    }
}
