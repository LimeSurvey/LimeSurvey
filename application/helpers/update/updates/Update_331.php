            upgrade331($oDB);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 331), "stg_name='DBVersion'");
            $oTransaction->commit();
