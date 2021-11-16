
            $oDB->createCommand()->update(
                '{{template_configuration}}',
                array('packages_to_load' => '["pjax"]'),
                "templates_name='default' OR templates_name='material'"
            );

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 315), "stg_name='DBVersion'");
            $oTransaction->commit();
