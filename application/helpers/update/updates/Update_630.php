<?php

namespace LimeSurvey\Helpers\Update;

class Update_630 extends DatabaseUpdateBase
{
    public function up()
    {
            // Add new question code setting
            addColumn('{{surveys}}', 'othersettings', 'mediumtext');
            $this->db->createCommand()->update('{{surveys}}', array('othersettings' => '{"question_code_prefix":"Q","subquestion_code_prefix":"SQ","answer_code_prefix":"A"}'));
    }
}
