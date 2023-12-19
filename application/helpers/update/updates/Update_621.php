<?php

namespace LimeSurvey\Helpers\Update;

class Update_621 extends DatabaseUpdateBase
{
    public function up()
    {
            addColumn('{{surveys}}', 'showdatapolicybutton', "string(1) NOT NULL default 'N'");
            addColumn('{{surveys}}', 'showlegalnoticebutton', "string(1) NOT NULL default 'N'");
    }
}
