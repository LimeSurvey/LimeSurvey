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

        // Check if LimeSurveyProfessional plugin exists in database instead of checking class
        $command = $this->db->createCommand()
            ->select('id')
            ->from('{{plugins}}')
            ->where('name = :name', [':name' => 'LimeSurveyProfessional']);

        if ($command->queryRow()) {
            $this->db->createCommand()->update(
                '{{plugins}}',
                ['priority' => 1],
                'name = :name',
                [':name' => 'LimeSurveyProfessional']
            );
        }
    }
}
