<?php

namespace LimeSurvey\Helpers\Update;

class Update_348 extends DatabaseUpdateBase
{
    public function up()
    {
            $this->db->createCommand()->addColumn('{{surveys_languagesettings}}', 'surveyls_policy_notice', 'text');
            $this->db->createCommand()->addColumn('{{surveys_languagesettings}}', 'surveyls_policy_error', 'text');
            $this->db->createCommand()->addColumn(
                '{{surveys_languagesettings}}',
                'surveyls_policy_notice_label',
                'string(192)'
            );
            $this->db->createCommand()->addColumn('{{surveys}}', 'showsurveypolicynotice', 'integer DEFAULT 0');
    }
}
