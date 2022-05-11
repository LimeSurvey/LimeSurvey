<?php

namespace LimeSurvey\Helpers\Update;

class Update_343 extends DatabaseUpdateBase
{
    public function up()
    {
            \alterColumn('{{answers}}', 'assessment_value', 'integer', false, '0');
    }
}
