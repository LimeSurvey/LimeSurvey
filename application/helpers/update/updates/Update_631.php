<?php

namespace LimeSurvey\Helpers\Update;

class Update_631 extends DatabaseUpdateBase
{
    public function up()
    {
        addColumn('{{archived_table_settings}}', 'archive_alias', "string(255) DEFAULT ''");
    }
}
