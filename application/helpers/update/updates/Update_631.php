<?php

namespace LimeSurvey\Helpers\Update;

class Update_631 extends DatabaseUpdateBase
{
    public function up()
    {
        addColumn('{{surveys}}', 'othersettings', 'mediumtext');
        addColumn('{{surveys_groupsettings}}', 'othersettings', 'mediumtext');

        $question_code_prefix = App()->getConfig("question_code_prefix");
        $subquestion_code_prefix = App()->getConfig("subquestion_code_prefix");
        $answer_code_prefix = App()->getConfig("answer_code_prefix");

        $other_settings = [
            'question_code_prefix' => $question_code_prefix,
            'subquestion_code_prefix' => $subquestion_code_prefix,
            'answer_code_prefix' => $answer_code_prefix
        ];

        $other_settings_json = json_encode($other_settings);

        $this->db->createCommand()->update(
            '{{surveys_groupsettings}}',
            ['othersettings' => $other_settings_json],
            'gsid = 0'
        );
        $other_settings_inherit = [
            'question_code_prefix' => 'I',
            'subquestion_code_prefix' => 'I',
            'answer_code_prefix' => 'I'
        ];
        $other_settings_inherit_json = json_encode($other_settings_inherit);
        $this->db->createCommand()->update(
            '{{surveys_groupsettings}}',
            ['othersettings' => $other_settings_inherit_json],
            'gsid != 0 AND othersettings IS NULL'
        );
    }
}
