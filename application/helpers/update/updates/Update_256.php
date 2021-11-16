            upgradeTokenTables256();
            alterColumn('{{participants}}', 'email', "text", false);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 256), "stg_name='DBVersion'");
            $oTransaction->commit();
