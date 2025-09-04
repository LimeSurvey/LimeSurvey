<?php

namespace LimeSurvey\Helpers\Update;

class Update_637 extends DatabaseUpdateBase
{
    public function up()
    {
        dropColumn('{{surveys}}', 'othersettings');
        dropColumn('{{surveys_groupsettings}}', 'othersettings');
    }
}
