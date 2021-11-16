<?php

namespace LimeSurvey\Helpers\Update;

class Update_138 extends DatabaseUpdateBase
{
    public function run()
    {
            alterColumn('{{quota_members}}', 'code', "string(11)");
    }
}