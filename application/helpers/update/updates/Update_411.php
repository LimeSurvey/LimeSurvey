<?php

namespace LimeSurvey\Helpers\Update;

class Update_411 extends DatabaseUpdateBase
{
    public function run()
    {
            $oDB->createCommand()->addColumn('{{plugins}}', 'priority', "int NOT NULL default 0");
    }
}
