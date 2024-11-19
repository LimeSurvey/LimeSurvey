<?php

namespace LimeSurvey\Helpers\Update;

class Update_494 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->db->createCommand()->addColumn('{{surveys_languagesettings}}', 'surveyls_alias', 'string(100)');
    }
}
