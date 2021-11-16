            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                [
                    'permission' => 'surveylocale',
                ],
                'name=\'emailtemplates\''
            );
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 347], "stg_name='DBVersion'");
            $oTransaction->commit();
