            // Update 'Theme Options' Entry (Side Menu Link) in Survey Menu Entries.
            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                array(
                    'menu_link' => 'themeOptions/updateSurvey',
                    'data' => '{"render": {"link": { "pjaxed": true, "data": {"sid": ["survey","sid"], "gsid":["survey","gsid"]}}}}'
                ),
                "name='theme_options'"
            );

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 432), "stg_name='DBVersion'");
            $oTransaction->commit();
