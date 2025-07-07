<?php

namespace LimeSurvey\Helpers\Update;

class Update_632 extends DatabaseUpdateBase
{
    public function up()
    {

        //updating the default value for datestamp
        //surveys_groupsettings datestamp should be 'Y'
        \alterColumn('{{surveys_groupsettings}}', 'datestamp', 'string(1)', false, 'Y');
        \alterColumn('{{surveys}}', 'datestamp', 'string(1)', false, 'Y');
    }
}
