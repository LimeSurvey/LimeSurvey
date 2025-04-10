<?php

namespace LimeSurvey\Helpers\Update;

class Update_630 extends DatabaseUpdateBase
{
    public function up()
    {
            // Add new question code setting
            addColumn('{{surveys}}', 'othersettings', 'mediumtext');
    }
}
