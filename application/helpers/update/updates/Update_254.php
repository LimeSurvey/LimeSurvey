<?php

namespace LimeSurvey\Helpers\Update;

class Update_254 extends DatabaseUpdateBase
{
    public function up()
    {
            upgradeSurveyTables254();
            // Update DBVersion
    }
}
