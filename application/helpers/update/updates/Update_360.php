<?php

namespace LimeSurvey\Helpers\Update;

class Update_360 extends DatabaseUpdateBase
{
    public function up()
    {
            $this->db->createCommand()->update(
                '{{surveymenu_entries}}',
                [
                    'permission' => 'tokens',
                ],
                'name=\'participants\''
            );
    }
}
