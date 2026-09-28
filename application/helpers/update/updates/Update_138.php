<?php

namespace LimeSurvey\Helpers\Update;

class Update_138 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {
            \alterColumn('{{quota_members}}', 'code', "string(11)");
    }
}
