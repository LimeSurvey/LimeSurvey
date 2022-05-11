<?php

namespace LimeSurvey\Helpers\Update;

class Update_347 extends DatabaseUpdateBase
{
    public function up()
    {
            $this->db->createCommand()->update(
                '{{surveymenu_entries}}',
                [
                    'permission' => 'surveylocale',
                ],
                'name=\'emailtemplates\''
            );
    }
}
