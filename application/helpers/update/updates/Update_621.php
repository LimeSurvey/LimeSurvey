<?php

namespace LimeSurvey\Helpers\Update;

class Update_622 extends DatabaseUpdateBase
{
    public function up()
    {
            // Add new tokens setting
            addColumn('{{surveys}}', 'showdatapolicybutton', "string(1) NOT NULL default 'N'");
            addColumn('{{surveys}}', 'showlegalnoticebutton', "string(1) NOT NULL default 'N'");
    }
}
