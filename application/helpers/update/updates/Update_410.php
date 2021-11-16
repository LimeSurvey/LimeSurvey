<?php

namespace LimeSurvey\Helpers\Update;

class Update_410 extends DatabaseUpdateBase
{
    public function run()
    {
            $oDB->createCommand()->addColumn('{{question_l10ns}}', 'script', " text NULL default NULL");
    }
}