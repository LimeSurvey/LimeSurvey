<?php

namespace LimeSurvey\Helpers\Update;

class Update_311 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            addColumn('{{surveys_groups}}', 'template', "string(128) DEFAULT 'default'");
    }
}
