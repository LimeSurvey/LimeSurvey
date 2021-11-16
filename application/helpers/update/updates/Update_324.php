
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 324), "stg_name='DBVersion'");
            $oTransaction->commit();
