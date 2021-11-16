<?php

namespace LimeSurvey\Helpers\Update;

class Update_349 extends DatabaseUpdateBase
{
    public function run()
    {
            dropColumn('{{users}}', 'one_time_pw');
            addColumn('{{users}}', 'one_time_pw', 'text');
    }
}
