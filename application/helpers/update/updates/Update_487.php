<?php

namespace LimeSurvey\Helpers\Update;

class Update_487 extends DatabaseUpdateBase
{
    public function up()
    {
        // Adjust permissions for "Survey Participants" menu entry
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            [
                'permission' => 'tokens',
                'permission_grade' => 'read'
            ],
            "name='participants'"
        );
    }
}
