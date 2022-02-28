<?php

namespace LimeSurvey\Helpers\Update;

class Update_152 extends DatabaseUpdateBase
{
    public function up()
    {
            $this->db->createCommand()->createIndex('question_attributes_idx3', '{{question_attributes}}', 'attribute');
    }
}
