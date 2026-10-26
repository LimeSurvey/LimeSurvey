<?php

namespace LimeSurvey\Helpers\Update;

class Update_343 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            \alterColumn('{{answers}}', 'assessment_value', 'integer', false, '0');
    }
}
