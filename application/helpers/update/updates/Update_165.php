<?php

namespace LimeSurvey\Helpers\Update;

class Update_165 extends DatabaseUpdateBase
{
    public function up()
    {
            $this->db->createCommand()->createTable(
                '{{plugins}}',
                array(
                    'id' => 'pk',
                    'name' => 'string NOT NULL',
                    'active' => 'boolean'
                )
            );
            $this->db->createCommand()->createTable(
                '{{plugin_settings}}',
                array(
                    'id' => 'pk',
                    'plugin_id' => 'integer NOT NULL',
                    'model' => 'string',
                    'model_id' => 'integer',
                    'key' => 'string',
                    'value' => 'text'
                )
            );
            \alterColumn('{{surveys_languagesettings}}', 'surveyls_url', "text");
    }
}
