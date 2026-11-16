<?php

namespace LimeSurvey\Helpers\Update;

class Update_183 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            upgradeSurveyTables183();
    }
}
