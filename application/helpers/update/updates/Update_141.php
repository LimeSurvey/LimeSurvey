<?php

namespace LimeSurvey\Helpers\Update;

class Update_141 extends DatabaseUpdateBase
{
    public function up()
    {
            addColumn('{{surveys}}', 'tokenlength', 'integer NOT NULL DEFAULT 15');
    }
}
