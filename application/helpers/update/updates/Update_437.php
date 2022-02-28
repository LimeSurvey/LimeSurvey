<?php

namespace LimeSurvey\Helpers\Update;

class Update_437 extends DatabaseUpdateBase
{
    public function up()
    {
            //refactore controller assessment (surveymenu_entry link changes to new controller rout)
            $this->db->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'menu_link' => 'assessment/index',
                ),
                "name='assessments'"
            );
    }
}
