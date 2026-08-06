<?php

namespace LimeSurvey\Helpers\Update;


class Update_710 extends DatabaseUpdateBase
{
    public function up()
    {
        addColumn('{{surveys}}', 'projecttitle', 'string');
    }
}
