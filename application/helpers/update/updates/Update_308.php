            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 308), "stg_name='DBVersion'");
            $oTransaction->commit();
