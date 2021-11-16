<?php

namespace LimeSurvey\Helpers\Update;

class Update_291 extends DatabaseUpdateBase
{
    public function run()
    {

            addColumn('{{plugins}}', 'version', 'string(32)');

    }
}