            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                [
                    'permission' => 'tokens',
                ],
                'name=\'participants\''
            );
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 360], "stg_name='DBVersion'");
            $oTransaction->commit();
