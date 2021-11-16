<?php

namespace LimeSurvey\Helpers\Update;

class Update_341 extends DatabaseUpdateBase
{
    public function run()
    {

            $oDB->createCommand()->truncateTable('{{tutorials}}');
            $oDB->createCommand()->truncateTable('{{tutorial_entries}}');
            $oDB->createCommand()->truncateTable('{{tutorial_entry_relation}}');


    }
}