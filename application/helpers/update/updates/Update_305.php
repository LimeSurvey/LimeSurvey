            $oTransaction->commit();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 305), "stg_name='DBVersion'");
