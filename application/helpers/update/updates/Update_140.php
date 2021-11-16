<?php

namespace LimeSurvey\Helpers\Update;

class Update_140 extends DatabaseUpdateBase
{
    public function run()
    {
            addColumn('{{surveys}}', 'emailresponseto', 'text');
    }
}
