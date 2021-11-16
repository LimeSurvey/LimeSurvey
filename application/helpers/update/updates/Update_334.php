<?php

namespace LimeSurvey\Helpers\Update;

class Update_334 extends DatabaseUpdateBase
{
    public function run()
    {
            $oDB->createCommand()->addColumn('{{tutorials}}', 'title', 'string(192)');
            $oDB->createCommand()->addColumn('{{tutorials}}', 'icon', 'string(64)');
    }
}
