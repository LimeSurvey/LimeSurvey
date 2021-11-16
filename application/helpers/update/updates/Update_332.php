            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 332), "stg_name='DBVersion'");
            $oTransaction->commit();
