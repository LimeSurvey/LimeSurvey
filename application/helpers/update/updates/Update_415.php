            $oTransaction = $oDB->beginTransaction();

            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                [
                    "menu_link" => "admin/filemanager",
                    "action" => '',
                    "template" => '',
                    "partial" => '',
                    "classes" => '',
                    "data" => '{"render": { "link": {"data": {"surveyid": ["survey","sid"]}}}}',
                ],
                'name=:name',
                [':name' => 'resources']
            );
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 415), "stg_name='DBVersion'");
            $oTransaction->commit();
