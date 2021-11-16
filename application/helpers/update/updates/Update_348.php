<?php

namespace LimeSurvey\Helpers\Update;

class Update_348 extends DatabaseUpdateBase
{
    public function run()
    {
            $oDB->createCommand()->addColumn('{{surveys_languagesettings}}', 'surveyls_policy_notice', 'text');
            $oDB->createCommand()->addColumn('{{surveys_languagesettings}}', 'surveyls_policy_error', 'text');
            $oDB->createCommand()->addColumn(
                '{{surveys_languagesettings}}',
                'surveyls_policy_notice_label',
                'string(192)'
            );
            $oDB->createCommand()->addColumn('{{surveys}}', 'showsurveypolicynotice', 'integer DEFAULT 0');
    }
}
