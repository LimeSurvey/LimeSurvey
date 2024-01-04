<?php

namespace LimeSurvey\Helpers\Update;

class Update_622 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->addColumn('{{surveys_languagesettings}}', 'surveyls_legal_notice', 'text');
    }
}
