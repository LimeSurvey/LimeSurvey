<?php

namespace LimeSurvey\Helpers\Update;

class Update_429 extends DatabaseUpdateBase
{
    public function up()
    {
        extendDatafields429($this->db); // Do it again for people already using 4.x before this was introduced
        $this->db->createCommand()->update(
            '{{surveymenu_entries}}',
            array(
                'menu_link' => '',
                'action' => 'updatesurveylocalesettings',
                'template' => 'editLocalSettings_main_view',
                'partial' => '/admin/survey/subview/accordion/_resources_panel',
                'getdatamethod' => 'tabResourceManagement'
            ),
            "name='resources'"
        );
    }
}
