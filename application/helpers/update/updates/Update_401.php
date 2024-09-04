<?php

namespace LimeSurvey\Helpers\Update;

class Update_401 extends DatabaseUpdateBase
{
    public function up()
    {

            $this->db->createCommand()->addColumn('{{plugins}}', 'load_error', 'int DEFAULT 0');
            $this->db->createCommand()->addColumn('{{plugins}}', 'load_error_message', 'text');

            $this->db->createCommand()->update('{{settings_global}}', array('stg_value' => 401), "stg_name='DBVersion'");
    }
}
