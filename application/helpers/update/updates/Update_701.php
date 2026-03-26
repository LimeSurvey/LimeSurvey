<?php

namespace LimeSurvey\Helpers\Update;

class Update_701 extends DatabaseUpdateBase
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
                'priority' => 0,
            ]
        );

        try {
            if (class_exists("\\LimeSurveyProfessional")) {
                App()->db->createCommand()->update(
                    '{{plugins}}',
                    ['priority' => 1],
                    'name = :name',
                    [':name' => 'LimeSurveyProfessional']
                );
            }
        } catch (\Exception $e) {
            // LimeSurveyProfessional is not available - that's fine for community edition
        }
    }
}
