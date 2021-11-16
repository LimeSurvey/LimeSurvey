<?php

namespace LimeSurvey\Helpers\Update;

class Update_141 extends DatabaseUpdateBase
{
    public function run()
    {
            addColumn('{{surveys}}', 'tokenlength', 'integer NOT NULL default 15');
    }
}
