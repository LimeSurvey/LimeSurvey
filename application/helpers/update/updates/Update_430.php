<?php

namespace LimeSurvey\Helpers\Update;

class Update_430 extends DatabaseUpdateBase
{
    public function up()
    {
        $plugin = $this->db->createCommand()
            ->select('*')
            ->from('{{plugins}}')
            ->where("name = :name", [':name' => 'ComfortUpdateChecker'])
            ->queryRow();
        if (empty($plugin)) {
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
