<?php

namespace LimeSurvey\Helpers\Update;

class Update_402 extends DatabaseUpdateBase
{
    public function run()
    {

            // Plugin type is either "core", "user" or "upload" (different folder locations).
            $oDB->createCommand()->addColumn('{{plugins}}', 'plugin_type', "string(6) default 'user'");

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 402), "stg_name='DBVersion'");
    }
}