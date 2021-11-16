
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 309), "stg_name='DBVersion'");
            $oTransaction->commit();
