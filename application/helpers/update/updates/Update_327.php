            $oTransaction = $oDB->beginTransaction();
            upgrade327($oDB);
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 327), "stg_name='DBVersion'");
            $oTransaction->commit();
