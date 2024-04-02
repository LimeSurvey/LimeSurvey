<?php

namespace LimeSurvey\Helpers\Update;

class Update_430 extends DatabaseUpdateBase
{
    public function up()
    {
        $result = $this->db->createCommand('SELECT name FROM {{plugins}} WHERE name = ' . $this->db->quoteValue('ComfortUpdateChecker'))->queryAll();
        if (empty($result)) {
            $this->db->createCommand()->insert(
                "{{plugins}}",
                [
                    'name' => 'ComfortUpdateChecker',
                    'plugin_type' => 'core',
                    'active' => 1,
                    'version' => '1.0.0',
                    'load_error' => 0,
                    'load_error_message' => null
                ]
            );
        }
    }
}
