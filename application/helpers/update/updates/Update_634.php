<?php

namespace LimeSurvey\Helpers\Update;

class Update_631 extends DatabaseUpdateBase
{
    public function up()
    {
        $other_settings_inherit = [
            'question_code_prefix' => 'I',
            'subquestion_code_prefix' => 'I',
            'answer_code_prefix' => 'I'
        ];
        $other_settings_inherit_json = json_encode($other_settings_inherit);
        $this->db->createCommand()->update(
            '{{surveys}}',
            ['othersettings' => $other_settings_inherit_json],
            'othersettings IS NULL'
        );
    }
}
