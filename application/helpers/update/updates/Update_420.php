<?php

namespace LimeSurvey\Helpers\Update;

class Update_420 extends DatabaseUpdateBase
{
    public function run()
    {
            $this->db->createCommand()->update(
                "{{surveymenu_entries}}",
                [
                    'name' => "listSurveyGroups",
                    'title' => gT('Group list', 'unescaped'),
                    'menu_title' => gT('Group list', 'unescaped'),
                    'menu_description' => gT('List question groups', 'unescaped'),
                ],
                'name=\'listQuestionGroups\''
            );
    }
}
