<?php

namespace LimeSurvey\Helpers\Update;

class Update_477 extends DatabaseUpdateBase
{
    public function up()
    {

            // refactored controller ResponsesController (surveymenu_entry link changes to new controller rout)
            $this->db->createCommand()->update(
                '{{surveymenu_entries}}',
                [
                    'menu_link' => 'responses/browse',
                    'data'      => '{"render": {"isActive": true, "link": {"data": {"surveyId": ["survey", "sid"]}}}}'
                ],
                "name='responses'"
            );
    }
}
