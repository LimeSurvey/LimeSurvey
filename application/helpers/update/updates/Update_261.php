<?php

namespace LimeSurvey\Helpers\Update;

class Update_261 extends DatabaseUpdateBase
{
    public function up()
    {
            /*
            * The hash value of a notification is used to calculate uniqueness.
            * @since 2016-08-10
            * @author Olle Haerstedt
            */
            addColumn('{{notifications}}', 'hash', 'string(64)');
            $this->db->createCommand()->createIndex('{{notif_hash_index}}', '{{notifications}}', 'hash', false);
    }
}
