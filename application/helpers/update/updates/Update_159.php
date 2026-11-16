<?php

namespace LimeSurvey\Helpers\Update;

class Update_159 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            \alterColumn('{{failed_login_attempts}}', 'ip', "string(40)", false);
    }
}
