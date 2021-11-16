<?php

namespace LimeSurvey\Helpers\Update;

class Update_440 extends DatabaseUpdateBase
{
    public function run()
    {
            $oDB->createCommand()->createIndex('sess_expire', '{{sessions}}', 'expire');
    }
}