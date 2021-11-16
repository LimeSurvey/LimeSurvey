<?php

namespace LimeSurvey\Helpers\Update;

class Update_446 extends DatabaseUpdateBase
{
    public function up()
    {
            // archived_table_settings
            $this->db->createCommand()->createTable(
                '{{archived_table_settings}}',
                [
                    'id' => "pk",
                    'survey_id' => "int NOT NULL",
                    'user_id' => "int NOT NULL",
                    'tbl_name' => "string(255) NOT NULL",
                    'tbl_type' => "string(10) NOT NULL",
                    'created' => "datetime NOT NULL",
                    'properties' => "text NOT NULL",
                ],
                $this->options
            );
            upgradeArchivedTableSettings446();

            $this->db->createCommand()->update('{{settings_global}}', array('stg_value' => 446), "stg_name='DBVersion'");
    }
}
