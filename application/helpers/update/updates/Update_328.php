<?php

namespace LimeSurvey\Helpers\Update;

class Update_328 extends DatabaseUpdateBase
{
    public function run()
    {
            upgrade328($oDB);
    }
}
