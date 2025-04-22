<?php

namespace LimeSurvey\Helpers\Update;

class Update_630 extends DatabaseUpdateBase
{
    public function up()
    {
            addColumn('{{surveys}}', 'othersettings', 'mediumtext');
            addColumn('{{surveys_groupsettings}}', 'othersettings', 'mediumtext');
    }
}
