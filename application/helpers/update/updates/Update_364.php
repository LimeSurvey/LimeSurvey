<?php

namespace LimeSurvey\Helpers\Update;

class Update_364 extends DatabaseUpdateBase
{
    public function run()
    {
            extendDatafields364($oDB);
    }
}