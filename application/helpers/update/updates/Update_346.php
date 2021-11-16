<?php

namespace LimeSurvey\Helpers\Update;

class Update_346 extends DatabaseUpdateBase
{
    public function run()
    {
            createSurveyMenuTable($oDB);
            $oDB->createCommand()->truncateTable('{{tutorials}}');
            $oDB->createCommand()->truncateTable('{{tutorial_entries}}');
            $oDB->createCommand()->truncateTable('{{tutorial_entry_relation}}');
    }
}
