            // Update vanilla config
            $oDB->createCommand()->update(
                '{{template_configuration}}',
                [
                    'files_css' => '{"add":["css/base.css","css/theme.css","css/custom.css","css/noTablesOnMobile.css"]}',
                ],
                "template_name = 'vanilla' AND files_css != 'inherit'"
            );
            // Update bootswatch config
            $oDB->createCommand()->update(
                '{{template_configuration}}',
                [
                    'files_css' => '{"add":["css/base.css","css/theme.css","css/custom.css","css/noTablesOnMobile.css"]}',
                ],
                "template_name = 'bootswatch' AND files_css != 'inherit'"
            );
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 428), "stg_name='DBVersion'");
            $oTransaction->commit();
