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
                    'entity' => 'string(15) not null',
                    'entity_id' => 'integer not null',
                    'title' => 'string not null', // varchar(255) in postgres
                    'message' => 'text not null',
                    'status' => "string(15) not null default 'new' ",
                    'importance' => 'integer not null default 1',
                    'display_class' => "string(31) default 'default'",
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
