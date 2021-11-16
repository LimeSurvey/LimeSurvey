            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 310), "stg_name='DBVersion'");
            $oTransaction->commit();
