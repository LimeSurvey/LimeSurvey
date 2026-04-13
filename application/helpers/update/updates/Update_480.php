<?php

namespace LimeSurvey\Helpers\Update;

class Update_480 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->createTable(
            '{{source_message}}',
            [
                'id' => "pk",
                'category' => "string(35)",
                'message' => "text",
            ],
            $this->options
        );
        $this->db->createCommand()->createTable(
            '{{message}}',
            [
                'id' => "integer NOT NULL",
                'language' => "string(50) NOT NULL DEFAULT ''",
                'translation' => "text",
            ],
            $this->options
        );
        $this->db->createCommand()->addPrimaryKey(
            '{{message_pk}}',
            '{{message}}',
            ['id', 'language']
        );
    }
}
