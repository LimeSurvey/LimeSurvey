<?php

namespace LimeSurvey\Helpers\Update;

class Update_147 extends DatabaseUpdateBase
{
    public function run()
    {
            addColumn('{{users}}', 'templateeditormode', "string(7) NOT NULL default 'default'");
            addColumn('{{users}}', 'questionselectormode', "string(7) NOT NULL default 'default'");
    }
}