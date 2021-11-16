<?php

namespace LimeSurvey\Helpers\Update;

class Update_256 extends DatabaseUpdateBase
{
    public function run()
    {
            upgradeTokenTables256();
            alterColumn('{{participants}}', 'email', "text", false);
    }
}