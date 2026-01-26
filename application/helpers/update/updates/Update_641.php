<?php

namespace LimeSurvey\Helpers\Update;

class Update_641 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->insert(
            "{{plugins}}",
            [
                'name' => 'ReactEditor',
                'plugin_type' => 'core',
                'active' => 1,
                'version' => '1.0.0',
                'load_error' => 0,
                'load_error_message' => null,
                'priority' => 1,
            ]
        );
    }
}
