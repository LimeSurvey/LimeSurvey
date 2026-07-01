<?php

namespace LimeSurvey\Helpers\Update;

class Update_161 extends DatabaseUpdateBase
{
    public function up()
    {
            addColumn('{{survey_links}}', 'date_invited', 'datetime NULL DEFAULT NULL');
            addColumn('{{survey_links}}', 'date_completed', 'datetime NULL DEFAULT NULL');
    }
}
