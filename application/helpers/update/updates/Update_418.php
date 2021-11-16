<?php

namespace LimeSurvey\Helpers\Update;

class Update_418 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->insert(
            "{{plugins}}",
            [
                'name' => 'PasswordRequirement',
                'plugin_type' => 'core',
                'active' => 1,
                'version' => '1.0.0',
                'load_error' => 0,
                'load_error_message' => null
            ]
        );
    }
}
