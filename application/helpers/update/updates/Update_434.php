            $defaultSetting = LsDefaultDataSets::getDefaultUserAdministrationSettings();

            $oDB->createCommand()->delete('{{settings_global}}', 'stg_name=:name', [':name' => 'sendadmincreationemail']);
            $oDB->createCommand()->delete('{{settings_global}}', 'stg_name=:name', [':name' => 'admincreationemailsubject']);
            $oDB->createCommand()->delete('{{settings_global}}', 'stg_name=:name', [':name' => 'admincreationemailtemplate']);

            $oDB->createCommand()->insert(
                '{{settings_global}}',
                [
                    "stg_name" => 'sendadmincreationemail',
                    "stg_value" => $defaultSetting['sendadmincreationemail'],
                ]
            );

            $oDB->createCommand()->insert(
                '{{settings_global}}',
                [
                    "stg_name" => 'admincreationemailsubject',
                    "stg_value" => $defaultSetting['admincreationemailsubject'],
                ]
            );

            $oDB->createCommand()->insert(
                '{{settings_global}}',
                [
                    "stg_name" => 'admincreationemailtemplate',
                    "stg_value" => $defaultSetting['admincreationemailtemplate'],
                ]
            );

