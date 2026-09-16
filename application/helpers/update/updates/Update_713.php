<?php

namespace LimeSurvey\Helpers\Update;


class Update_713 extends DatabaseUpdateBase
{
    public function up()
    {
        addColumn('{{surveys}}', 'code', 'string');
    }
}
