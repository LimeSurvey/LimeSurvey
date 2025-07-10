<?php

namespace LimeSurvey\Helpers\Update;

class Update_637 extends DatabaseUpdateBase
{
    public function up()
    {
        addColumn('{{archived_table_settings}}', 'archive_alias', "string(255) DEFAULT ''");
    }
}
