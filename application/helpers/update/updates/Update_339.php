
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 339), "stg_name='DBVersion'");
            $oTransaction->commit();
