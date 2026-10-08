<?php

namespace LimeSurvey\Helpers\Update;

class Update_404 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            createSurveysGroupSettingsTable($this->db);
    }
}
