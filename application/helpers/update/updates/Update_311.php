<?php

namespace LimeSurvey\Helpers\Update;

class Update_311 extends DatabaseUpdateBase
{
    public function run()
    {
            addColumn('{{surveys_groups}}', 'template', "string(128) DEFAULT 'default'");
    }
}