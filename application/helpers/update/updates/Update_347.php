<?php

namespace LimeSurvey\Helpers\Update;

class Update_347 extends DatabaseUpdateBase
{
    public function run()
    {
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                [
                    'permission' => 'surveylocale',
                ],
                'name=\'emailtemplates\''
            );
    }
}
