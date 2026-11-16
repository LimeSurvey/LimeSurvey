<?php

namespace LimeSurvey\Helpers\Update;

class Update_255 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            upgradeSurveyTables255();
            // Update DBVersion
    }
}
