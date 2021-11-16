            upgradeTokens176();
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 176), "stg_name='DBVersion'");
            $oTransaction->commit();
