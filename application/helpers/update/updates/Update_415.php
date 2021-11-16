
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
