<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Fix organizer link : icon and survey activated
 * @package LimeSurvey\Helpers\Update
 */
class Update_611 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                "title" => "Overview question & groups",
                "menu_title" => "Overview question & groups",
                "menu_description" => "Overview question and groups",
            ],
            'name=:name',
            [':name' => 'listQuestions']
        );

        $this->db->createCommand()->delete(
            '{{surveymenu_entries}}',
            'name=:name',
            [':name' => 'listQuestionGroups']
        );
        $this->db->createCommand()->delete(
            '{{surveymenu_entries}}',
            'name=:name',
            [':name' => 'reorder']
        );
    }
}
