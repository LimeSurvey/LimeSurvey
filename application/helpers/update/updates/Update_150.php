<?php

namespace LimeSurvey\Helpers\Update;

class Update_150 extends DatabaseUpdateBase
{
    public function run()
    {
            addColumn('{{questions}}', 'relevance', 'text');
    }
}