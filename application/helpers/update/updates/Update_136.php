<?php

namespace LimeSurvey\Helpers\Update;

class Update_136 extends DatabaseUpdateBase
{
    public function up()
    {
            addColumn('{{quota}}', 'autoload_url', "integer NOT NULL DEFAULT 0");
            // Create quota table
            $aFields = array(
                'quotals_id' => 'pk',
                'quotals_quota_id' => 'integer NOT NULL DEFAULT 0',
                'quotals_language' => "string(45) NOT NULL DEFAULT 'en'",
                'quotals_name' => 'string',
                'quotals_message' => 'text NOT NULL',
                'quotals_url' => 'string',
                'quotals_urldescrip' => 'string',
            );
            $this->db->createCommand()->createTable('{{quota_languagesettings}}', $aFields);
    }
}
