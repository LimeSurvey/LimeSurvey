            upgrade330($oDB);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 330), "stg_name='DBVersion'");
            $oTransaction->commit();
