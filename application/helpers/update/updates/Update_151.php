<?php

namespace LimeSurvey\Helpers\Update;

class Update_151 extends DatabaseUpdateBase
{
    public function run()
    {
            addColumn('{{groups}}', 'randomization_group', "string(20) NOT NULL default ''");
    }
}