<?php

namespace LimeSurvey\Helpers\Update;

class Update_410 extends DatabaseUpdateBase
{
    public function up()
    {
            $this->db->createCommand()->addColumn('{{question_l10ns}}', 'script', " text NULL DEFAULT NULL");
    }
}
