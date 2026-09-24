<?php

namespace LimeSurvey\Helpers\Update;

class Update_139 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            upgradeSurveyTables139();
    }
}
