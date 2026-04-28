<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Fix organizer link : icon and survey activated
 * @package LimeSurvey\Helpers\Update
 */
class Update_606 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()->update(
            "{{surveymenu_entries}}",
            [
                'data' => '{"render": {"link": {"data": {"surveyid": ["survey", "sid"]}}}}',
                'menu_icon' => 'reorder',
                'menu_icon_type' => 'fontawesome'
            ],
            "name='reorder'"
        );
    }
}
