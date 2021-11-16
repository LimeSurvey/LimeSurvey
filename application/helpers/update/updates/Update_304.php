<?php

namespace LimeSurvey\Helpers\Update;

class Update_304 extends DatabaseUpdateBase
{
    public function run()
    {
            upgradeTemplateTables304($oDB);
    }
}
