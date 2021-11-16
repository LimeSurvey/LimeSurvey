<?php

namespace LimeSurvey\Helpers\Update;

class Update_404 extends DatabaseUpdateBase
{
    public function run()
    {
            createSurveysGroupSettingsTable($oDB);
    }
}
