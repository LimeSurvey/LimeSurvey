<?php

namespace LimeSurvey\Helpers\Update;

class Update_621 extends DatabaseUpdateBase
{
    public function up()
    {
        addColumn('{{surveys}}', 'showquestioncode', "string(1) NOT NULL default 'N'");
        addColumn('{{surveys}}', 'cookieconsent', "string(1) NOT NULL default 'N'");
        addColumn('{{surveys}}', 'footerbranding', "string(1) NOT NULL default 'N'");
    }
}
