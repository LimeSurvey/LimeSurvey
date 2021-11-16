            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 336), "stg_name='DBVersion'");
            $oTransaction->commit();
