<?php

namespace LimeSurvey\Helpers\Update;

class Update_181 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
        upgradeTokenTables181('utf8_bin');
        upgradeSurveyTables181('utf8_bin');
    }
}
