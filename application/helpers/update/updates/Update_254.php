<?php

namespace LimeSurvey\Helpers\Update;

class Update_254 extends DatabaseUpdateBase
{
    public function run()
    {
            upgradeSurveyTables254();
            // Update DBVersion
    }
}