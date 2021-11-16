<?php

namespace LimeSurvey\Helpers\Update;

class Update_330 extends DatabaseUpdateBase
{
    public function run()
    {
            upgrade330($oDB);
    }
}