
            addColumn('{{plugins}}', 'version', 'string(32)');

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 291), "stg_name='DBVersion'");
            $oTransaction->commit();
