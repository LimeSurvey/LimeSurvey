<?php

namespace LimeSurvey\Helpers\Update;

class Update_429 extends DatabaseUpdateBase
{
    public function run()
    {
            $oTransaction = $oDB->beginTransaction();
            extendDatafields429($oDB); // Do it again for people already using 4.x before this was introduced
            $oDB->createCommand()->update(
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