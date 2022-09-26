<?php

namespace LimeSurvey\Helpers\Update;

class Update_493 extends DatabaseUpdateBase
{
    public function up()
    {
        $this->db->createCommand()->addColumn('{{surveys_languagesettings}}', 'surveyls_alias', 'string(100)');
    }
}
