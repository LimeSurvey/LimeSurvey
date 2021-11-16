<?php

namespace LimeSurvey\Helpers\Update;

class Update_140 extends DatabaseUpdateBase
{
    public function up()
    {
            addColumn('{{surveys}}', 'emailresponseto', 'text');
    }
}
