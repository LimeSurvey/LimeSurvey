<?php

namespace LimeSurvey\Helpers\Update;

class Update_142 extends DatabaseUpdateBase
{
    public function up()
    {
        upgradeQuestionAttributes142();
        $this->db->createCommand()->alterColumn('{{surveys}}', 'expires', "datetime");
        $this->db->createCommand()->alterColumn('{{surveys}}', 'startdate', "datetime");
        $this->db->createCommand()->update('{{question_attributes}}', array('value' => 0), "value='false'");
        $this->db->createCommand()->update('{{question_attributes}}', array('value' => 1), "value='true'");
    }
}
