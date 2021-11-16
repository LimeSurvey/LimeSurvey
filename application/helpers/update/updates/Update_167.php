<?php

namespace LimeSurvey\Helpers\Update;

class Update_167 extends DatabaseUpdateBase
{
    public function up()
    {
            addColumn('{{surveys_languagesettings}}', 'attachments', 'text');
            addColumn('{{users}}', 'created', 'datetime');
            addColumn('{{users}}', 'modified', 'datetime');
    }
}
