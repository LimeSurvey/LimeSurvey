            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update(
                "{{surveymenu_entries}}",
                [
                    'name' => "listSurveyGroups",
                    'title' => gT('Group list', 'unescaped'),
                    'menu_title' => gT('Group list', 'unescaped'),
                    'menu_description' => gT('List question groups', 'unescaped'),
                ],
                'name=\'listQuestionGroups\''
            );

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 420), "stg_name='DBVersion'");
            $oTransaction->commit();
