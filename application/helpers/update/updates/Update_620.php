<?php

namespace LimeSurvey\Helpers\Update;

class Update_620 extends DatabaseUpdateBase
{
    public function up()
    {
            // Add new tokens setting
            addColumn('{{surveys}}', 'showquestioncode', "string(1) NOT NULL default 'N'");
            addColumn('{{surveys}}', 'cookieconsent', "string(1) NOT NULL default 'N'");
            addColumn('{{surveys}}', 'footerbranding', "string(1) NOT NULL default 'N'");
    }
}
