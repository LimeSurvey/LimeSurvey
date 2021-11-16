<?php

namespace LimeSurvey\Helpers\Update;

class Update_343 extends DatabaseUpdateBase
{
    public function run()
    {
            alterColumn('{{answers}}', 'assessment_value', 'integer', false, '0');
    }
}