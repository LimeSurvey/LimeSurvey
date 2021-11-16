            $oDB->createCommand()->addColumn('{{surveys_languagesettings}}', 'surveyls_policy_notice', 'text');
            $oDB->createCommand()->addColumn('{{surveys_languagesettings}}', 'surveyls_policy_error', 'text');
            $oDB->createCommand()->addColumn(
                '{{surveys_languagesettings}}',
                'surveyls_policy_notice_label',
                'string(192)'
            );
            $oDB->createCommand()->addColumn('{{surveys}}', 'showsurveypolicynotice', 'integer DEFAULT 0');

            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 348], "stg_name='DBVersion'");
            $oTransaction->commit();
