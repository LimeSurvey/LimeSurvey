<?php

namespace LimeSurvey\Helpers\Update;

class Update_346 extends DatabaseUpdateBase
{
    public function run()
    {
            createSurveyMenuTable($this->db);
            $this->db->createCommand()->truncateTable('{{tutorials}}');
            $this->db->createCommand()->truncateTable('{{tutorial_entries}}');
            $this->db->createCommand()->truncateTable('{{tutorial_entry_relation}}');
    }
}
