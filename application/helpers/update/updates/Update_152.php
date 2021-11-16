<?php

namespace LimeSurvey\Helpers\Update;

class Update_152 extends DatabaseUpdateBase
{
    public function run()
    {
            $oDB->createCommand()->createIndex('question_attributes_idx3', '{{question_attributes}}', 'attribute');
    }
}
