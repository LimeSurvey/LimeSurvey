<?php

namespace LimeSurvey\Helpers\Update;

class Update_307 extends DatabaseUpdateBase
{
    public function up()
    {
        if (tableExists('{settings_user}')) {
            $this->db->createCommand()->dropTable('{{settings_user}}');
        }
            $this->db->createCommand()->createTable(
                '{{settings_user}}',
                array(
                    'id' => 'pk',
                    'uid' => 'integer NOT NULL',
                    'entity' => 'string(15)',
                    'entity_id' => 'string(31)',
                    'stg_name' => 'string(63) NOT NULL',
                    'stg_value' => 'text',

                )
            );
    }
}
