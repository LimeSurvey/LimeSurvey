
            //$oDB->createCommand()->renameColumn('{{template_configuration}}', 'templates_name', 'template_name');

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 316), "stg_name='DBVersion'");
            $oTransaction->commit();
