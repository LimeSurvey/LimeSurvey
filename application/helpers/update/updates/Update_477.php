
            // refactored controller ResponsesController (surveymenu_entry link changes to new controller rout)
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                [
                    'menu_link' => 'responses/browse',
                    'data'      => '{"render": {"isActive": true, "link": {"data": {"surveyId": ["survey", "sid"]}}}}'
                ],
                "name='responses'"
            );
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 477], "stg_name='DBVersion'");
            $oTransaction->commit();
