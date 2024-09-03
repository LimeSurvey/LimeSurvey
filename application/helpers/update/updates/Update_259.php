<?php

namespace LimeSurvey\Helpers\Update;

class Update_259 extends DatabaseUpdateBase
{
    public function up()
    {
            $this->db->createCommand()->createTable(
                '{{notifications}}',
                array(
                    'id' => 'pk',
                    'entity' => 'string(15) NOT NULL',
                    'entity_id' => 'integer NOT NULL',
                    'title' => 'string NOT NULL', // varchar(255) in postgres
                    'message' => 'text NOT NULL',
                    'status' => "string(15) NOT NULL DEFAULT 'new' ",
                    'importance' => 'integer NOT NULL DEFAULT 1',
                    'display_class' => "string(31) DEFAULT 'default'",
                    'created' => 'datetime',
                    'first_read' => 'datetime'
                )
            );
            $this->db->createCommand()->createIndex(
                '{{notif_index}}',
                '{{notifications}}',
                'entity, entity_id, status',
                false
            );
    }
}
