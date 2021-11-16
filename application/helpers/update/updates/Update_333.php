<?php

namespace LimeSurvey\Helpers\Update;

class Update_333 extends DatabaseUpdateBase
{
    public function run()
    {
            upgrade333($oDB);
    }
}