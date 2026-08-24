<?php

namespace LimeSurvey\Helpers\Update;


class Update_711 extends DatabaseUpdateBase
{
    public function up()
    {
        addColumn('{{surveys}}', 'internaltitle', 'string');
    }
}
