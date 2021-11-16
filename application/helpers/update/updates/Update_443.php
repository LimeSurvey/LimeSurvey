<?php

namespace LimeSurvey\Helpers\Update;

class Update_443 extends DatabaseUpdateBase
{
    public function run()
    {
            $oDB->createCommand()->renameColumn('{{users}}', 'lastLogin', 'last_login');
    }
}
