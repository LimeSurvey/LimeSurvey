<?php

namespace LimeSurvey\Helpers\Update;

class Update_253 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            upgradeSurveyTables253();

            // Update DBVersion
    }
}
