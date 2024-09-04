<?php

namespace LimeSurvey\Helpers\Update;

class Update_402 extends DatabaseUpdateBase
{
    public function up()
    {

            // Plugin type is either "core", "user" or "upload" (different folder locations).
            $this->db->createCommand()->addColumn('{{plugins}}', 'plugin_type', "string(6) DEFAULT 'user'");

            $this->db->createCommand()->update('{{settings_global}}', array('stg_value' => 402), "stg_name='DBVersion'");
    }
}
