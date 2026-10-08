<?php

namespace LimeSurvey\Helpers\Update;

class Update_256 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            upgradeTokenTables256();
            \alterColumn('{{participants}}', 'email', "text", false);
    }
}
