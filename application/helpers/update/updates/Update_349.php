<?php

namespace LimeSurvey\Helpers\Update;

class Update_349 extends DatabaseUpdateBase
{
    public function up()
    {
            dropColumn('{{users}}', 'one_time_pw');
            addColumn('{{users}}', 'one_time_pw', 'text');
    }
}
