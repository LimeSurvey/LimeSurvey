<?php

namespace LimeSurvey\Helpers\Update;

class Update_403 extends DatabaseUpdateBase
{
    public function up()
    {
            upgradeTokenTables402('utf8mb4_bin');
            upgradeSurveyTables402('utf8mb4_bin');
    }
}
