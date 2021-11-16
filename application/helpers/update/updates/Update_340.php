            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 340), "stg_name='DBVersion'");
            $oTransaction->commit();
