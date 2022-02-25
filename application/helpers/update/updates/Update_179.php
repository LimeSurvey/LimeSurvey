<?php

namespace LimeSurvey\Helpers\Update;

class Update_179 extends DatabaseUpdateBase
{
    public function up()
    {
            upgradeSurveys177(); // Needs to be run again to make sure
            upgradeTokenTables179();
            \alterColumn('{{participants}}', 'email', "string(254)", false);
            \alterColumn('{{participants}}', 'firstname', "string(150)", false);
            \alterColumn('{{participants}}', 'lastname', "string(150)", false);
    }
}
