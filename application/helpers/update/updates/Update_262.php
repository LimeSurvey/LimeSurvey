<?php

namespace LimeSurvey\Helpers\Update;

class Update_262 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            \alterColumn('{{settings_global}}', 'stg_value', "text", false);
    }
}
