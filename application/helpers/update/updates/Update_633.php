<?php

namespace LimeSurvey\Helpers\Update;

class Update_633 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                'menu_link' => 'admin/statistics/sa/simpleStatistics/',
            ],
            'name=\'statistics\''
        );
    }
}
