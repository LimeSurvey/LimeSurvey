            $oDB->createCommand()->insert(
                "{{plugins}}",
                [
                    'name' => 'PasswordRequirement',
                    'plugin_type' => 'core',
                    'active' => 1,
                    'version' => '1.0.0',
                    'load_error' => 0,
                    'load_error_message' => null
                ]
            );

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 418), "stg_name='DBVersion'");
            $oTransaction->commit();

            SurveymenuEntries::reorderMenu(2);
