<?php

namespace LimeSurvey\Helpers\Update;

class Update_326 extends DatabaseUpdateBase
{
    public function run()
    {
            $oDB->createCommand()->alterColumn('{{surveys}}', 'datecreated', 'datetime');
    }
}