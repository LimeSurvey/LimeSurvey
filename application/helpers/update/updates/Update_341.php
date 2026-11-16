<?php

namespace LimeSurvey\Helpers\Update;

class Update_341 extends DatabaseUpdateBase
{
    #[\Override]
    public function up()
    {

            $this->db->createCommand()->truncateTable('{{tutorials}}');
            $this->db->createCommand()->truncateTable('{{tutorial_entries}}');
            $this->db->createCommand()->truncateTable('{{tutorial_entry_relation}}');
    }
}
