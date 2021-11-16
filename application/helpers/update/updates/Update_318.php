
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 318), "stg_name='DBVersion'");
            $oTransaction->commit();
